<?php
class Accounting_Model_DbTable_DbCreditmemo extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_creditmemo';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function getAllCreditmemo($search=null){
		$db = $this->getAdapter();
		//$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$sql="SELECT 
				c.id,
				(SELECT branch_nameen FROM `rms_branch` WHERE rms_branch.br_id = c.branch_id LIMIT 1) AS branch_name,
				s.stu_code,
				CONCAT(stu_khname,'-',stu_enname) AS student_name,
				total_amount,
				total_amountafter,
				c.date,
				c.end_date,
				c.note,
				(SELECT name_kh FROM rms_view WHERE rms_view.type=23 AND key_code=c.type LIMIT 1) AS paid_transfer,
				(SELECT first_name FROM `rms_users` WHERE id=c.user_id LIMIT 1) AS user_name,
				c.status 
			  FROM 
				rms_creditmemo c, 
				rms_student AS s
			  WHERE
				s.stu_id = c.student_id";
		//$where = ' ';
		$str_date=' c.date ';
		//$str_date=' c.end_date ';
		if(!empty($search['by_date'])==0){
		}else if($search['by_date']==1){//create
			$str_date=' c.date ';
		}else if($search['by_date']==2){//expired
			$str_date=' c.end_date ';
		}
		$from_date =(empty($search['start_date']))? '1': " $str_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " $str_date <= '".$search['end_date']." 23:59:59'";
		
		$where = " AND ".$from_date." AND ".$to_date;
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " (SELECT branch_nameen FROM `rms_branch` WHERE rms_branch.br_id = c.branch_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
			$s_where[] = " stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " stu_enname LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.= " AND c.branch_id = ".$search['branch_id'];
		}
		if($search['paid_transfer']>-1){
			$where.= " AND type = ".$search['paid_transfer'];
		}
		if($search['status']>-1){
			$where.= " AND c.status = ".$search['status'];
		}
		$order=" order by id DESC";
		//echo $sql.$where.$order; exit();
		return $db->fetchAll($sql.$where.$order);
	}
	function addCreditmemo($data){
		$db = $this->getAdapter();
		try{
		$sql="SELECT id FROM rms_creditmemo WHERE branch_id =".$data['branch_id'];
		$sql.=" AND student_id='".$data['student_id']."'";
		$rs = $db->fetchOne($sql);
		if(!empty($rs)){
			return -1;
		}
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
			'status'		=>1,
			'user_id'		=>$this->getUserId(),);
		$this->insert($arr);
		}catch (Exception $e){
			$db->rollBack();
		}
 	 }
	 function updatcreditMemo($data){
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
			'user_id'		=>$this->getUserId(),
		);
		$where=" id = ".$data['id'];
		$this->update($arr, $where);
	}
 function getCreditmemobyid($id){
	$db = $this->getAdapter();
	$sql=" SELECT * FROM rms_creditmemo where id=$id ";
	return $db->fetchRow($sql);
}
}