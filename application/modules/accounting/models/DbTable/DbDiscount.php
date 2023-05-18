<?php

class Accounting_Model_DbTable_DbDiscount extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_discount';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;  	 
    }
	public function addNewDiscount($_data){
		$db = $this->getAdapter();
		try{
			$sql="SELECT disco_id FROM rms_discount WHERE status =".$_data['status'];
			$sql.=" AND dis_name='".$_data['dis_name']."'";
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}			
		$_arr=array(
				'dis_name'	  => $_data['dis_name'],
				'create_date' => Zend_Date::now(),
				'status'  	  => $_data['status'],
				'user_id'	  => $this->getUserId()
		);
		$this->insert($_arr);
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	public function addNewOccupationPopup($_data){
		$_arr=array(
				'dis_name' => $_data['dis_name'],
				'create_date' => Zend_Date::now(),
				'status'   => $_data['status_j'],
				'user_id'	  => $this->getUserId()
		);
		return  $this->insert($_arr);
	}
	
	
	public function getDiscountById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_discount WHERE disco_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function updateDiscount($_data){
		$_arr=array(
				'dis_name' => $_data['dis_name'],
				'create_date' => Zend_Date::now(),
				'status'   => $_data['status'],
				'user_id'	  => $this->getUserId()
		);
		$where=$this->getAdapter()->quoteInto("disco_id=?", $_data["id"]);
		$this->update($_arr, $where);
	}
	function getAllDiscount($search){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql = " SELECT 
					disco_id AS id,
					dis_name,
					create_date,
				   (SELECT  CONCAT(first_name) FROM rms_users WHERE id=user_id )AS user_name
			";
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM rms_discount ";
		
		$order = ' ORDER BY id DESC '; 
		$where = ' WHERE dis_name!="" ';
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " dis_name LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.=' AND status='.$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);
	}	
	public function addDiscounttion($_data){//ajax
		$_arr=array(
				'dis_name' => $_data['dis_name'],
				'create_date' => Zend_Date::now(),
				'status'   => 1,
				'user_id'	  => $this->getUserId()
		);
		return  $this->insert($_arr);
	}
}

