<?php
class Accounting_Model_DbTable_DbTransfercredit extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_transfer_credit';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	}
	function getAllTransfer($search=null){
		$db = $this->getAdapter();
		//$session_user=new Zend_Session_Namespace('authstu');
		$sql="SELECT 
				c.id,
				(SELECT branch_nameen FROM `rms_branch` WHERE rms_branch.br_id = c.branch_id LIMIT 1) AS branch_name,				
				(SELECT stu_code FROM `rms_student` WHERE rms_student.stu_id = c.student_id LIMIT 1) AS stu_id, 
				(SELECT CONCAT(stu_khname,'-',stu_enname) AS student_name FROM `rms_student` WHERE rms_student.stu_id = c.student_id LIMIT 1) AS stu_idname,
				(SELECT s.stu_code FROM `rms_student` WHERE rms_student.stu_id = c.stu_idto LIMIT 1) AS stu_idto,
				(SELECT CONCAT(stu_khname,'-',stu_enname) AS student_name FROM `rms_student` WHERE rms_student.stu_id = c.stu_name LIMIT 1) AS stu_name,
				 total_amount,
				 prob,
				 problem,
				(SELECT first_name FROM `rms_users` WHERE id=c.user_id LIMIT 1) AS user_name,
				c.status 
			  FROM 
				rms_transfer_credit c,
				rms_student AS s
			  WHERE
				  s.stu_id = c.stu_idto
			";
 		$where="";
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " (SELECT branch_nameen FROM `rms_branch` WHERE rms_branch.br_id = c.branch_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT stu_code FROM `rms_student` WHERE rms_student.stu_id = c.student_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " CONCAT(stu_khname,'-',stu_enname) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT s.stu_code FROM `rms_student` WHERE rms_student.stu_id = c.stu_idto LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT CONCAT(stu_khname,'-',stu_enname) AS student_name FROM `rms_student` WHERE rms_student.stu_id = c.stu_name LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.=' AND c.branch_id='.$search['branch_id'];
		}
		if($search['status']>-1){
			$where.= " AND c.status  = ".$search['status'];
		}
		$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function transfercreditMemo($data){
		$db = $this->getAdapter();
		//print_r($data); exit();
		try{
			$sql="SELECT id FROM rms_transfer_credit WHERE branch_id =".$data['branch_id'];
			$sql.=" AND student_id='".$data['student_id']."'";
 		//	$sql.=" AND stu_name='".$data['stu_name']."'";
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
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
				
				'stu_idto'=>$data['stu_idto'],
				'stu_name'=>$data['stu_name'],
				'start_date'=>$data['start_date'],
				'end_dates'=>$data['end_dates'],
				'problem'=>$data['problem'],
				'Descriptions'=>$data['Descriptions'],
				'status'=>$data['status'],
				'user_id'=>$this->getUserId(),);
		$this->_name='rms_transfer_credit';
		$this->insert($arr);
		
		$arr = array(
				'branch_id'		=>$data['branch_id'],
				'student_id'	=>$data['stu_idto'],
				'total_amount'	=>0,
				'total_amountafter'=>0,
				'note'			=>$data['Description'],
				'prob'			=>$data['prob'],
				'type'			=>1,
				'date'			=>$data['Date'],
				'end_date'		=>$data['end_date'],
				'status'		=>$data['status'],
				'user_id'		=>$this->getUserId(),);
		$this->_name='rms_creditmemo';
		$this->insert($arr);
		}catch (Exception $e){
			$db->rollBack();
			echo $e->getMessage();exit();
		}
	}
	function getTransferbyid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_transfer_credit where id=$id ";
		return $db->fetchRow($sql);
	}
}