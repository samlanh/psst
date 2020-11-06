<?php
class Accounting_Model_DbTable_Dbinvoice extends Zend_Db_Table_Abstract
{
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	public function getinvoice($search){
		$db= $this->getAdapter();
		$sql="SELECT v.id ,
					(SELECT branch_nameen FROM `rms_branch` WHERE br_id=v.branch_id LIMIT 1) AS branch,
					s.stu_code ,
					s.stu_khname,
					CONCAT(s.last_name,' ',s.stu_enname) as en_name,
					(SELECT v.name_en FROM rms_view AS v WHERE v.key_code=s.sex AND v.type=2 LIMIT 1) AS sex,
					DATE_FORMAT(v.invoice_date,'%d-%M-%Y') AS invoice_date,
					v.invoice_num ,
					v.input_date ,
					v.remark ,
					v.totale_amount ,
					(SELECT first_name FROM rms_users WHERE rms_users.id = v.user_id LIMIT 1) AS first_name
				FROM 
					rms_invoice_account  AS v ,
					rms_student AS s 
				WHERE 
				    stu_id = student_name 
					AND s.status=1 
					AND s.customer_type=1 ";
		
    	$from_date =(empty($search['start_date']))? '1': " v.input_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " v.input_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['search']));
    		$s_where[]= " v.branch_id LIKE '%{$s_search}%'";
    		$s_where[]= " s.stu_khname LIKE '%{$s_search}%'";
    		$s_where[]="  v.student_name LIKE '%{$s_search}%'";
    		$s_where[]="  v.last_name LIKE '%{$s_search}%'";
    		$s_where[]= " v.invoice_num LIKE '%{$s_search}%'";
			$s_where[]= " s.stu_code LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND v.branch_id=".$search['branch_id'];
    	}
    	if(!empty($search['student_name'])){
    		$where.=" AND v.student_name=".$search['student_name'];
    	}
		$order=" ORDER BY v.id DESC";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('v.branch_id');
		return $db->fetchAll($sql.$where.$order);
	}
	public function getinvoiceByid($id){
		$db= $this->getAdapter();
		$sql="SELECT v.* ,
			s.stu_khname ,s.stu_enname,s.last_name,s.stu_code,s.sex,s.tel
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
    		$invoice_num= $this->getvCode($data['branch_id']);
	    	$arr = array(
	    			'branch_id'=>$data['branch_id'],
	    			'student_name'=>$data['student_name'],
	    			'student_id'=>$data['student_name'],
					'invoice_date'=>$data['invoice_date'],
	    			'invoice_num'=>$invoice_num,
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
					'month'=>$data['amount_'.$i],
					'term'=>$data['term_'.$i],
					'semester'=>$data['semester_'.$i],
					'year'=>$data['year_'.$i],
					'start_date'=>$data['startdate_'.$i],
					'end_date'=>$data['enddate_'.$i],
					'remark'=>$data['remark_'.$i],
					'is_onepayment'=>$data['onepayment_'.$i],
					'period'=>$data['term_study'.$i],
					);
				$this->_name='rms_invoice_account_detail';	
				$this->insert($arr_s);
			}
		    $db->commit();
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    	}
    }
	public function editinvice($data,$id){
		$db= $this->getAdapter();
		try{
	    	$arr = array(
    			'branch_id'=>$data['branch_id'],
    			'student_name'=>$data['student_name'],
    			'student_id'=>$data['student_name'],
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
					'month'=>$data['amount_'.$i],
					'term'=>$data['term_'.$i],
					'semester'=>$data['semester_'.$i],
					'year'=>$data['year_'.$i],
					'start_date'=>$data['startdate_'.$i],
					'end_date'=>$data['enddate_'.$i],
					'is_onepayment'=>$data['onepayment_'.$i],
					'period'=>$data['term_study'.$i],
					'remark'=>$data['remark_'.$i],
					);
				$this->_name='rms_invoice_account_detail';	
				$this->insert($arr_s);
			}
		    	
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    	}
	}
	public function getvCode($branch_id){
		$db = $this->getAdapter();
		$sql="SELECT count(id) FROM `rms_invoice_account` AS v where branch_id = $branch_id ORDER BY v.`id` DESC LIMIT 1";
		$num = $db->fetchOne($sql);
		$num_lentgh = strlen((int)$num+1);
		$num = (int)$num+1;
		$pre = "0";
		for($i=$num_lentgh;$i<4;$i++){
			$pre.="0";
		}
 		return $pre.$num;
	}
		 
	/*function getAllGradeStudy($option=1,$student_id=null){
	 	$db = $this->getAdapter();
	 	$sql="SELECT i.id,
	 	CONCAT(i.title,' (',(SELECT it.title FROM `rms_items` AS it WHERE it.id = i.items_id LIMIT 1),')') AS name
	 	FROM `rms_itemsdetail` AS i
	 	WHERE i.status =1 ";
	 	if($option!=null){
	 		$sql.=" AND i.items_type=".$option;
	 	}
	 	if($student_id!=null){//new parameter for invoice09-1-019
	 		$sql.=" AND (i.items_type !=1 OR i.id=(SELECT grade FROM `rms_group_detail_student` WHERE status=1 AND is_maingrade=1 AND stop_type=0 AND stu_id= $student_id )) ";
	 	}
	 	$dbbg = new Application_Model_DbTable_DbGlobal();
	 	$branchlist = $dbbg->getAllSchoolOption();
	 	if (!empty($branchlist)){
	 		foreach ($branchlist as $i){
	 			$s_where[] = $i['id']." IN (i.schoolOption)";
	 		}
	 		$sql .=' AND ( '.implode(' OR ',$s_where).')';
	 	}
	 	$user = $dbbg->getUserInfo();
	 	$level = $user['level'];
	 	if ($level!=1){
	 		$sql .=' AND '.$user['schoolOption'].' IN (i.schoolOption)';
	 	}
	 	$sql.=" ORDER BY i.items_id ASC, i.ordering ASC";
	 	return $db->fetchAll($sql);
	}*/
}