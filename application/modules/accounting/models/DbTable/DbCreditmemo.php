<?php
class Accounting_Model_DbTable_DbCreditmemo extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_creditmemo';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	}
	function getAllCreditmemo($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace('authstu');
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
	
		$sql=" SELECT 
				c.id,
				(SELECT branch_nameen FROM `rms_branch` WHERE rms_branch.br_id = c.branch_id LIMIT 1) AS branch_name,
				s.stu_code,
				CONCAT(stu_khname,'-',stu_enname) as student_name,
				total_amount,
				total_amountafter,
				c.date,
				c.end_date,
				(select name_en from rms_view where rms_view.type=15 and key_code=c.type) as paid_status,
				(SELECT first_name FROM `rms_users` WHERE id=c.user_id LIMIT 1) as user_name,
				c.status 
			  FROM 
				rms_creditmemo c,
				rms_student as s
			  Where
				s.stu_id = c.student_id
			";
	
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " (SELECT branch_nameen FROM `rms_branch` WHERE rms_branch.br_id = c.branch_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
			$s_where[] = " stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " stu_enname LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		
		if($search['paid_status'] != ''){
			$where.= " AND type = ".$search['paid_status'];
		}
		
		$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
	function addCreditmemo($data){
		$arr = array(
			'branch_id'		=>$data['branch_id'],
			'student_id'	=>$data['student_id'],
			'total_amount'	=>$data['total_amount'],
			'total_amountafter'=>$data['total_amount'],
			'note'			=>$data['Description'],
			'prob'			=>$data['prob'],
			'type'			=>0,
			'date'			=>$data['Date'],
			'end_date'		=>$data['end_date'],
			'status'		=>$data['status'],
			'user_id'		=>$this->getUserId(),);
		$this->insert($arr);
		//print_r($data); exit();
 }
	 function updatcreditMemo($data){
			$arr = array(
				'branch_id'=>$data['branch_id'],
				'student_id'=>$data['student_id'],
				'total_amount'=>$data['total_amount'],
				'total_amountafter'=>$data['total_amount'],
				'note'=>$data['Description'],
				'prob'=>$data['prob'],
				'type'=>0,
				'date'=>$data['Date'],
				'end_date'=>$data['end_date'],
				'status'=>$data['status'],
				'user_id'=>$this->getUserId(),);
		$where=" id = ".$data['id'];
		$this->update($arr, $where);
	}
 function getCreditmemobyid($id){
	$db = $this->getAdapter();
	$sql=" SELECT * FROM rms_creditmemo where id=$id ";
	return $db->fetchRow($sql);
}
}