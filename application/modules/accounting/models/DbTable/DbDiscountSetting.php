<?php
class Accounting_Model_DbTable_DbDiscountSetting extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_dis_setting';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;  	 
    }
	public function addNewDiscountset($_data){
		$db = $this->getAdapter();
  		try{
			$sql="SELECT discount_id FROM rms_dis_setting WHERE disname_id =".$_data['disname_id'];
			$sql.=" AND dis_max='".$_data['dis_max']."'";
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}			
		$_arr=array(
				'branch_id'   => $_data['branch_id'],
				'disname_id'  => $_data['disname_id'],
				'dis_max'	  => $_data['dis_max'],
				'start_date'  => $_data['start_date'],
				'end_date'	  => $_data['end_date'],
				'create_date' => Zend_Date::now(),
				'status'  	  => 1,
				'user_id'	  => $this->getUserId()
		);
		$this->insert($_arr);
		}catch (Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message("INSERT_FAIL");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}	
	public function addNewDiscountPopup($_data){
		$_arr=array(
				'dis_name' => $_data['dis_name'],
				'create_date' => Zend_Date::now(),
				'status'   => $_data['status_j'],
				'user_id'	  => $this->getUserId()
		);
		$this->_name="rms_discount";
		return  $this->insert($_arr);
	}
	public function getDiscountsetById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_dis_setting WHERE discount_id = ".$db->quote($id);
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function updateDiscountset($_data){
		$_arr=array(
				'branch_id'   => $_data['branch_id'],
				'disname_id'  => $_data['disname_id'],
				'dis_max'	  => $_data['dis_max'],
				'start_date'  => $_data['start_date'],
				'end_date'	  => $_data['end_date'],
				'create_date' => Zend_Date::now(),
				'status'  	  => $_data['status'],
				'user_id'	  => $this->getUserId()
		);
		$where=$this->getAdapter()->quoteInto("discount_id=?", $_data["id"]);
		$this->update($_arr, $where);
	}
	function getAllDiscountset($search){
		
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql = " SELECT 
					g.discount_id AS id,
					(SELECT branch_nameen FROM `rms_branch` WHERE br_id=g.branch_id LIMIT 1)AS branch,
					(SELECT dis_name AS NAME FROM `rms_discount` WHERE disco_id=g.disname_id LIMIT 1) AS disc_name,
					g.dis_max,
					g.start_date,
					g.end_date,
					(SELECT  CONCAT(first_name) FROM rms_users WHERE id=g.user_id LIMIT 1 )AS user_name
					";
		$sql.=$dbp->caseStatusShowImage("g.status");
		$sql.=" FROM rms_dis_setting AS g ";
		
		$order = ' ORDER BY id DESC '; 
		$where = ' WHERE disname_id!="" ';
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " (SELECT dis_name AS name FROM `rms_discount` WHERE disco_id=disname_id ) LIKE '%{$s_search}%'";
			$s_where[] = " dis_max LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.=' AND g.branch_id='.$search['branch_id'];
		}
		if($search['status']>-1){
			$where.=' AND status='.$search['status'];
		}
		
		$where.=$dbp->getAccessPermission('g.branch_id');
		return $db->fetchAll($sql.$where.$order);
	}	
	public function addDiscounttionset($_data){//ajax
		$_arr=array(
			'dis_name' => $_data['dis_name'],
			'create_date' => Zend_Date::now(),
			'status'   => 1,
			'user_id'	  => $this->getUserId()
		);
		return  $this->insert($_arr);
	}
}