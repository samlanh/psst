<?php

class Global_Model_DbTable_DbOccupation extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_occupation';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;  	 
    }
	public function addNewOccupation($_data){
		$db = $this->getAdapter();
		try{
			$title = trim($_data['occu_name']);
			$titleEn = empty($_data['occu_enname'])?$title:$_data['occu_enname'];
			$titleEn = trim($titleEn);
			
			$sql="SELECT occupation_id FROM rms_occupation WHERE 1";
			$sql.=" AND occu_name='".$title."'";
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}			
		$_arr=array(
				'occu_name'	  => $title,
				'occu_enname' => $titleEn,
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
	public function addNewOccupationPopup($_data){
		$titleEn = empty($_data['occu_enname'])?$_data['occu_name']:$_data['occu_enname'];
		$titleEn = trim($titleEn);
		$_arr=array(
			'occu_name'	  => $_data['occu_name'],
			'occu_enname' => $titleEn,
			'create_date' => Zend_Date::now(),
			'status'   => $_data['status_j'],
			'user_id'	  => $this->getUserId()
		);
		return  $this->insert($_arr);
	}
	public function getOccupationById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_occupation WHERE occupation_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function updateOccupation($_data){
		$status = empty($_data['status'])?0:1;
		$titleEn = empty($_data['occu_enname'])?$_data['occu_name']:$_data['occu_enname'];
		$titleEn = trim($titleEn);
		$_arr=array(
			'occu_name'	  	=> $_data['occu_name'],
			'occu_enname'	=> $titleEn,
			'create_date' 	=> Zend_Date::now(),
			'status'   		=> $status,
			'user_id'	  	=> $this->getUserId()
		);
		$where=$this->getAdapter()->quoteInto("occupation_id=?", $_data["id"]);
		$this->update($_arr, $where);
	}
	function getAllOccupation($search){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql = " SELECT 
					occupation_id AS id,
					occu_name,occu_enname,
					create_date,
					(SELECT  CONCAT(first_name) FROM rms_users WHERE id=user_id )AS user_name";
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM rms_occupation ";
		
		$order = ' ORDER BY id DESC '; 
		$where = ' WHERE occu_name!="" ';
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " occu_name LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.=' AND status='.$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);
	}	
	public function addOccupation($_data){//ajax
		$_arr=array(
			'occu_name'	  => $_data['occu_name'],
			'create_date' => Zend_Date::now(),
			'status'   => 1,
			'user_id'	  => $this->getUserId()
		);
		return  $this->insert($_arr);
	}
}