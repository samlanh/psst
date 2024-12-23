<?php
class Accounting_Model_DbTable_DbTransfercredit extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_transfer_credit';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function getAllTransfer($search=null){
		$db = $this->getAdapter();
		$_db=new Application_Model_DbTable_DbGlobal();
		$branch= $_db->getBranchDisplay();
		$sql="SELECT 
				c.id,
				(SELECT $branch FROM `rms_branch` WHERE rms_branch.br_id = c.branch_id LIMIT 1) AS branch_name,				
				(SELECT stu_code FROM `rms_student` WHERE rms_student.stu_id = c.student_id LIMIT 1) AS stu_id, 
				(SELECT CONCAT(stu_khname,'-',stu_enname) AS student_name FROM `rms_student` WHERE rms_student.stu_id = c.student_id LIMIT 1) AS stu_idname,
				(SELECT CONCAT(stu_code) AS student_name FROM `rms_student` WHERE rms_student.stu_id = c.stu_name LIMIT 1) AS stu_idto,
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
				  s.stu_id = c.stu_name
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
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('c.branch_id');
		
		$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function transfercreditMemo($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
 		try{
			$sql="SELECT id FROM rms_transfer_credit WHERE student_id =".$data['studentId'];
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
			$arr = array(
				'branch_id'		=>$data['branch_id'],
				'student_id'	=>$data['studentId'],
				'total_amount'	=>$data['total_amount'],
				'total_amountafter'=>$data['total_amount'],
				'note'			=>$data['Description'],
				'prob'			=>$data['prob'],
				'type'			=>0,
				'date'			=>$data['Date'],
				'end_date'		=>$data['end_date'],
				'stu_name'		=>$data['toStudentId'],
				'start_date'	=>$data['start_date'],
				'end_dates'		=>$data['end_dates'],
				'problem'		=>$data['problem'],
				'Descriptions'	=>$data['Descriptions'],
				'status'		=>$data['status'],
				'user_id'		=>$this->getUserId(),);
			$this->_name='rms_transfer_credit';
			$this->insert($arr);

			$arr = array(
				'total_amountafter' => 0,
			);
			
			$this->_name='rms_creditmemo';
			$where = "student_id=" . $data['studentId'];
			$this->update($arr,$where);

			$arr = array(
				'branch_id'		=>$data['branch_id'],
				'student_id'	=>$data['toStudentId'],
				'total_amount'	=>$data['total_amount'],
				'total_amountafter'=>$data['total_amount'],
				'note'			=>$data['Description'],
				'prob'			=>$data['prob'],
				'type'			=>1,
				'date'			=>$data['Date'],
				'end_date'		=>$data['end_date'],
				'status'		=>$data['status'],
				'user_id'		=>$this->getUserId(),);
			$this->_name='rms_creditmemo';
			$this->insert($arr);
			$db->commit();
			
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	function updatefercreditMemo($data){
			$arr = array(
					'branch_id'		=>$data['branch_id'],
					'student_id'	=>$data['studentId'],
					'total_amount'	=>$data['total_amount'],
					'total_amountafter'=>$data['total_amount'],
					'note'			=>$data['Description'],
					'prob'			=>$data['prob'],
					'type'			=>0,
					'date'			=>$data['Date'],
					'end_date'		=>$data['end_date'],
					'stu_name'		=>$data['toStudentId'],
					'start_date'	=>$data['start_date'],
					'end_dates'		=>$data['end_dates'],
					'problem'		=>$data['problem'],
					'Descriptions'	=>$data['Descriptions'],
					'status'		=>$data['status'],
					'user_id'		=>$this->getUserId(),);
			$this->_name='rms_transfer_credit';
			$where=" id = ".$data['id'];
			$this->update($arr,$where);
	}
	function getTransferbyid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_transfer_credit where id=$id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		return $db->fetchRow($sql);
	}
}