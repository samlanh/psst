<?php

class Registrar_Model_DbTable_DbStudentTestPayment extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student_test';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    	 
    }
    
    public function getBranchId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->branch_id;
    }
    
	function addRegister($data){
		$db = $this->getAdapter();//ស្ពានភ្ជាប់ទៅកាន់Data Base
// 		$db->beginTransaction();//ទប់ស្កាត់មើលការErrore , មានErrore វាមិនអោយចូល
// 		$stu_code = $this->getNewAccountNumber($data['dept']);
		$receipt_number = $data['receipt_no']; //$this->getRecieptNo();
		
		$arr = array(
			'receipt_no'=>$receipt_number,
			'price'	 	=>$data['price'],
			'total_price'	 	=>$data['price'],
			'is_paid'	=>1,
			'paid_date'	=>$data['paid_date'],
		);
		
		$where = ' id = '.$data['stu_test'];
		$this->update($arr, $where);
		
	}
	
	function updateRegister($data,$id){
		$db = $this->getAdapter();//ស្ពានភ្ជាប់ទៅកាន់Data Base
		$receipt_number = $data['receipt_no']; //$this->getRecieptNo();
		if(empty($data['is_void'])){
			$arr = array(
					'receipt_no'=>$receipt_number,
					'price'	 	=>$data['price'],
					'total_price'	 	=>$data['price'],
					'is_paid'	=>1,
					'paid_date'	=>$data['paid_date'],
			);
		
			$where = " id = $id ";
			$this->update($arr, $where);
		}else{
			$arr = array(
					'receipt_no'=>null,
					'price'	 	=>null,
					'is_paid'	=>0,
					'paid_date'	=>null,
			);
			
			$where = " id = $id ";
			$this->update($arr, $where);
		}
	
	}
	
    function getAllStudentTestPayment($search=null){
    	$_db  = new Application_Model_DbTable_DbGlobal();
    	$user = $_db->getUserAccessPermission('sp.user_id');
    	$branch_id = $_db->getAccessPermission('sp.branch_id');
    	
    	$db=$this->getAdapter();
    	$sql=" select 
    				id,
    				serial,
    				receipt_no,
    				CONCAT(kh_name,'-',en_name) as name,
    				sex,
    				phone,
    				(select en_name from rms_dept where dept_id = st.degree) as degree_name,
    				
    				price,
    				paid_date,
    				(select first_name from rms_users where id = st.user_id) as user
    				
    			from
    				rms_student_test as st
    			where 
    				status = 1
    				and is_paid = 1
    				$user 
    				$branch_id 
    		";
    	
    	$where=" ";
    	$from_date =(empty($search['start_date']))? '1': " st.paid_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " st.paid_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['adv_search']));
    		$s_where[]= " serial LIKE '%{$s_search}%'";
    		$s_where[]=" receipt_no LIKE '%{$s_search}%'";
    		$s_where[]= " kh_name LIKE '%{$s_search}%'";
    		$s_where[]= " en_name LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	
    	if(($search['branch_id']>0)){
    		$where.= " AND st.branch_id = ".$search['branch_id'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND st.degree=".$search['degree'];
    	}
    	if(!empty($search['user'])){
    		$where.=" AND st.user_id=".$search['user'];
    	}
    	$order=" ORDER BY st.id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }

    public function getRecieptNo(){
    	$db = $this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	$branch_id="";
    	
    	$sql="SELECT count(id)  FROM rms_student_payment where 1 $branch_id LIMIT 1 ";
    	
    	$payment_no = $db->fetchOne($sql);
    	
    	$sql1="SELECT count(id)  FROM ln_income where 1 $branch_id LIMIT 1 ";
    	$income_no = $db->fetchOne($sql1);
    	
    	//$sql2="SELECT count(id)  FROM rms_student_test where 1 $branch_id LIMIT 1 ";
    	//$stu_test_no = $db->fetchOne($sql2); 

    	$sql3="SELECT count(id)  FROM rms_change_product where 1 LIMIT 1 ";
    	$change_product_no = $db->fetchOne($sql3);
    	
    	$new_acc_no= (int)$payment_no + (int)$income_no + (int)$change_product_no + 1;
    	
    	$acc_length = strlen((int)$new_acc_no+1);
    	$pre=0;
    	for($i = $acc_length;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }

    function getAllDegree(){
    	$db=$this->getAdapter();
    	$sql="SELECT dept_id AS id,en_name AS `name` FROM rms_dept WHERE is_active=1 AND en_name!='' ";
    	return $db->fetchAll($sql);
    }
    
    public function getNewStudent($newid,$stu_type){
    	$db = $this->getAdapter();
    	$sql="  SELECT COUNT(stu_id)  FROM rms_student WHERE stu_type IN (1,3)";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$new_acc_no=100+$new_acc_no;
    	$pre='';
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<=5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }

	function getAllStudentTested($type){
		$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission();
		
		$is_paid = " ";
		if($type==0){
			$is_paid = " and is_paid=0";
		}
		$sql="select id,CONCAT(en_name,'-',kh_name)as name from rms_student_test where en_name!='' AND status=1 and register=1 $is_paid $branch_id  ORDER BY id DESC ";
		return $db->fetchAll($sql);
	}
	
	
	function getStudentTestInfo($stu_test_id){
		$db=$this->getAdapter();
		$sql="select * from rms_student_test where id = $stu_test_id ";
		return $db->fetchRow($sql);
	}
	
	function getStudentTestPaymentById($id){
		$db=$this->getAdapter();
		$sql="select * from rms_student_test where id = $id ";
		return $db->fetchRow($sql);
	}
	
}
