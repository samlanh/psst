<?php
class Accounting_Model_DbTable_DbCreditmemo extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_creditmemo';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	function getAllCreditmemo($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace('auth');
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
	
		$sql=" SELECT id,
		(SELECT branch_namekh FROM `rms_branch` WHERE rms_branch.br_id =branch_id LIMIT 1) AS branch_name,
		(SELECT stu_code FROM `rms_student` WHERE stu_id =rms_creditmemo.student_id  limit 1 ) as stu_code,
		(SELECT stu_khname FROM `rms_student` WHERE stu_id =rms_creditmemo.student_id  limit 1 ) as student_name,
		total_amount,date,note,
		(SELECT first_name FROM `rms_users` WHERE id=1 LIMIT 1) as user_name,
		status FROM rms_creditmemo ";
	
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
// 			$s_where[] = " title LIKE '%{$s_search}%'";
// 			$s_where[] = " invoice LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
	function addCreditmemo($data){
		$arr = array(
			'branch_id'=>$data['branch_id'],
			'student_id'=>$data['student_id'],
			'total_amount'=>$data['total_amount'],
			'total_amountafter'=>$data['total_amount'],
			'note'=>$data['Description'],
			'type'=>0,
			'date'=>$data['Date'],
			'status'=>$data['status'],
			'user_id'=>$this->getUserId(),);
		$this->insert($arr);
 }
	 function updatcreditMemo($data){
			$arr = array(
				'branch_id'=>$data['branch_id'],
				'student_id'=>$data['student_id'],
				'total_amount'=>$data['total_amount'],
				'total_amountafter'=>$data['total_amount'],
				'note'=>$data['Description'],
				'type'=>0,
				'date'=>$data['Date'],
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