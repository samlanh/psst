<?php
class Accounting_Model_DbTable_Dbinvoice extends Zend_Db_Table_Abstract
{
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
	public function getinvoice($search){
		$db= $this->getAdapter();
		$sql="SELECT v.id ,
					(SELECT branch_nameen FROM `rms_branch` WHERE br_id=v.branch_id)AS branch,
					(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = s.group_id LIMIT 1) AS group_name,
					s.stu_code ,
					s.stu_khname ,
					s.stu_enname ,
					s.last_name ,
					(SELECT v.name_en FROM rms_view AS v WHERE v.key_code=s.sex AND v.type=2) AS sex,
					DATE_FORMAT(v.invoice_date,'%d-%b-%Y') AS invoice_date,
					v.invoice_num ,
					v.input_date ,
					v.remark ,
					v.totale_amount ,
					u.first_name  
				FROM 
					rms_invoice_account  AS v ,
					rms_student AS s ,
					rms_users AS u 
				WHERE 
				    stu_id = student_name 
					AND v.user_id=u.id
					AND s.status=1 
					AND s.customer_type=1";
		
    	$from_date =(empty($search['start_date']))? '1': " v.input_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " v.input_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['search']));
    		$s_where[]= " v.branch_id LIKE '%{$s_search}%'";
    		$s_where[]="  v.student_name LIKE '%{$s_search}%'";
    		$s_where[]= " v.invoice_num LIKE '%{$s_search}%'";
			$s_where[]= " s.stu_code LIKE '%{$s_search}%'";
			$s_where[]= " s.stu_khname LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND v.branch_id=".$search['branch_id'];
    	}
		if(!empty($search['group'])){
    		$where.= " AND s.group_id =".$search['group'];
    	}
    	if(!empty($search['student_name'])){
    		$where.=" AND v.student_name=".$search['student_name'];
    	}
    	if($search['degree']!=""){
    		$where.=" AND s.degree=".$search['degree'];
    	}
    	if($search['grade'] !=""){
    		$where.=" AND s.grade=".$search['grade'];
    	}
		$order=" ORDER BY v.id DESC";
		return $db->fetchAll($sql.$where.$order);
	}
	public function getinvoiceByid($id){
		$db= $this->getAdapter();
		$sql="SELECT v.* ,
			s.stu_khname ,s.stu_enname,s.last_name,s.stu_code,s.sex
			FROM rms_invoice_account  AS v ,
			rms_student AS s WHERE stu_id = student_name and id=".$id." LIMIT 1";
		return $db->fetchrow($sql);
	}
	public function getinvoiceservice($id){
		$db= $this->getAdapter();
		$sql="SELECT v.* ,
		(SELECT p.title FROM rms_itemsdetail AS p WHERE v.service_id = p.id AND p.items_type = v.type LIMIT 1) as title
		FROM 
		rms_invoice_account_detail AS v 
		WHERE vid='".$id."' ";
		return $db->fetchAll($sql);
	}
	function getInvoiceExisting($invoice_num){
		$db = $this->getAdapter();
		$sql="SELECT id FROM  rms_invoice_account WHERE invoice_num ='".$invoice_num."'";
		return $db->fetchOne($sql);
	}
    public function addinviceaccount($data){
    	$db= $this->getAdapter();
    	$db->beginTransaction();
    	try{
    			$rs = $this->getInvoiceExisting($data['invoice_num']);
    			if(!empty($rs)){
    				$dbiv = new Accounting_Model_DbTable_Dbinvoice();
    				$data['invoice_num']= $this->getvCode();
    			}

		    	$arr = array(
		    			'branch_id'=>$data['branch_id'],
		    			'student_name'=>$data['student_name'],
						'invoice_date'=>$data['invoice_date'],
		    			'invoice_num'=>$data['invoice_num'],
						'input_date'=>$data['input_date'],
		    			'remark'=>$data['remark'],
						'totale_amount'=>$data['totle_amount'],
		    			'user_id'=>$this->getUserId(),
		    			);
				$this->_name='rms_invoice_account';		
		    	$_id = $this->insert($arr);
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$arr_s = array(
						'vid'=>$_id,
						'service_id'=>$data['service_'.$i],
						'type'	=>$data['type_'.$i],
						'month'=>$data['amount_'.$i],
						'term'=>$data['term_'.$i],
						'semester'=>$data['semester_'.$i],
						'year'=>$data['year_'.$i],
						'start_date'=>$data['startdate_'.$i],
						'end_date'=>$data['enddate_'.$i],
						'remark'=>$data['remark_'.$i],
						);
					$this->_name='rms_invoice_account_detail';	
					$this->insert($arr_s);
				}
		    	$db->commit();
    	}catch(Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
	public function editinvice($data,$id){
		try{$db= $this->getAdapter();
		    	$arr = array(
		    			'branch_id'=>$data['branch_id'],
		    			'student_name'=>$data['student_name'],
						'invoice_date'=>$data['invoice_date'],
		    			'invoice_num'=>$data['invoice_num'],
						'input_date'=>$data['input_date'],
		    			'remark'=>$data['remark'],
						'totale_amount'=>$data['totle_amount'],
		    			'user_id'=>$this->getUserId(),
		    			);
				$this->_name='rms_invoice_account';	
				$where="id = '".$id."'";	
		    	$this->update($arr,$where);
				
				$db = Zend_Db_Table::getDefaultAdapter();
				$where = $db->quoteInto('vid = ?', $id);
				$db->delete('rms_invoice_account_detail', $where);
				
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$arr_s = array(
						'vid'=>$id,
						'service_id'=>$data['service_'.$i],
						'type'	=>$data['type_'.$i],
						'month'=>$data['amount_'.$i],
						'term'=>$data['term_'.$i],
						'semester'=>$data['semester_'.$i],
						'year'=>$data['year_'.$i],
						'start_date'=>$data['startdate_'.$i],
						'end_date'=>$data['enddate_'.$i],
						'remark'=>$data['remark_'.$i],
						);
					$this->_name='rms_invoice_account_detail';	
					$this->insert($arr_s);
				}
		    	
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	public function getvCode(){
		  $db = $this->getAdapter();
		  $sql="SELECT v.id FROM `rms_invoice_account` AS v  ORDER BY v.`id` DESC LIMIT 1";
		  $num = $db->fetchOne($sql);
		  $num_lentgh = strlen((int)$num+1);
		  $num = (int)$num+1;
		  $pre = "0";
		  for($i=$num_lentgh;$i<4;$i++){
		   $pre.="0";
		  }
		  return $pre.$num;
		 }
}



