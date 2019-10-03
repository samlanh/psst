<?php

class Registrar_Model_DbTable_DbStudentTestPayment extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student_test';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    	 
    }
    
    public function getBranchId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->branch_id;
    }
    
	function addRegister($data){
		$db = $this->getAdapter();//ស្ពានភ្ជាប់ទៅកាន់Data Base
// 		$db->beginTransaction();//ទប់ស្កាត់មើលការErrore , មានErrore វាមិនអោយចូល
// 		$stu_code = $this->getNewAccountNumber($data['dept']);
		//$receipt_number = $data['receipt_no']; //$this->getRecieptNo();
		$_db = new Registrar_Model_DbTable_DbRegister();
		$receipt_number = $_db->getRecieptNo();
		if($data['stu_type']==1){
			$arr = array(
				'kh_name'	 	=>$data['kh_name'],
				'en_name'	 	=>$data['en_name'],
				'receipt_no'	=>$receipt_number,
				'price'	 		=>$data['price'],
				'total_price'	=>$data['price'],
				'is_paid'		=>1,
				'paid_date'		=>$data['paid_date'],
				'note'		 	=>$data['note'],
				'account_userid'=>$this->getUserId()
			);
			$where = ' id = '.$data['stu_test'];
			$this->update($arr, $where);
		}else{
			$array = array(
				'branch_id'	=>$this->getBranchId(),
				'kh_name'	 	=>$data['kh_name'],
				'en_name'	 	=>$data['en_name'],
				'sex'	 		=>$data['sex'],
				'dob'		 	=>$data['dob'],
				'phone'		 	=>$data['parent_phone'],
				'serial'		=>$data['serial'],
				'degree'		=>$data['degree'],
				'note'		 	=>$data['note'],
				'receipt_no'	=>$receipt_number,
				'price'	 		=>$data['price'],
				'total_price'	=>$data['price'],
				'is_paid'		=>1,
				'paid_date'		=>$data['paid_date'],
				'test_date'		=>date('Y-m-d'),
				'create_date'	=>date('Y-m-d'),
				'account_userid'=>$this->getUserId()
			);
			$this->insert($array);
		}
	}
	
	function updateRegister($data,$id){
		$db = $this->getAdapter();//ស្ពានភ្ជាប់ទៅកាន់Data Base
		$receipt_number = $data['receipt_no']; //$this->getRecieptNo();
		if(empty($data['is_void'])){
			$arr = array(
					'receipt_no'=>$receipt_number,
					'price'	 	=>$data['price'],
					'total_price'=>$data['price'],
					'is_paid'	=>1,
					'paid_date'	=>$data['paid_date'],
					'note'		 	=>$data['note'],
					'account_userid'=>$this->getUserId()
			);
		
			$where = " id = $id ";
			$this->update($arr, $where);
		}else{
			$arr = array(
					'receipt_no'=>null,
					'price'	 	=>null,
					'total_price'=>null,
					'is_paid'	=>0,
					'note'		 	=>$data['note'],
					'paid_date'	=>null,
			);
			
			$where = " id = $id ";
			$this->update($arr, $where);
		}
	
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
		$sql="select id,CONCAT(en_name,'-',kh_name)as name from rms_student_test where en_name!='' AND status=1 $is_paid $branch_id  ORDER BY id DESC ";
		
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
