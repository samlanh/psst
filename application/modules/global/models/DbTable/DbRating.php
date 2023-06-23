<?php

class Global_Model_DbTable_DbRating extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_rating';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;  	 
    }
	public function addNewRating($_data){
		$db = $this->getAdapter();
		try{
	  		$sql="SELECT id FROM rms_rating where status =1 ";
	  		$sql.=" AND rating='".$_data['rating']."'";
	  		$rs = $db->fetchOne($sql);
	  		if(!empty($rs)){
	  			return -1;
	  		}
	  		$arr = array(
	  				'rating'	=> $_data['rating'],
	  				'createDate' 	=>date("Y-m-d"),
					'modifyDate' 	=>date("Y-m-d"),
	  				'status'		=> 1,
					'userId'		=> $this->getUserId()
	  		);
			$this->insert($arr);
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getRatingById($id){
		$db = $this->getAdapter();
		$sql = " SELECT * FROM rms_rating WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function updateRating($_data){
		$db = $this->getAdapter();
		try{
			$status = empty($_data['status'])?0:1;
			$_arr=array(
					'rating'	=> $_data['rating'],
					'createDate' 	=>date("Y-m-d"),
					'modifyDate' 	=>date("Y-m-d"),
					'status'	    => $status,
					'userId'	    => $this->getUserId()
			);
			$where=$this->getAdapter()->quoteInto("id=?", $_data["id"]);
			$this->update($_arr,$where);
		
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	
	function getAllRating($search){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  id, rating, createDate,
				  (SELECT  CONCAT(first_name) FROM rms_users WHERE id=userId )AS user_name
				";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM `rms_rating`  WHERE 1 ";
		
		$where = '';
		$order = ' ORDER BY id ASC ';
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " rating LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1 AND $search['status']!=''){
			$where.=' AND status='.$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);
	}	
}