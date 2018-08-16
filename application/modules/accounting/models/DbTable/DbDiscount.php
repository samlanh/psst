<?php

class Global_Model_DbTable_DbDiscount extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_document';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;  	 
    }
	public function addNewDiscount($_data){
		$db = $this->getAdapter();
		//print_r($_data); exit();
		try{
			$sql="SELECT document_id FROM rms_document WHERE status =".$_data['status'];
			$sql.=" AND doc_name='".$_data['doc_name']."'";
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}			
		$_arr=array(
				'doc_name'	  => $_data['doc_name'],
				'create_date' => Zend_Date::now(),
				'status'  	  => $_data['status'],
				'user_id'	  => $this->getUserId()
		);
		$this->insert($_arr);
		}catch (Exception $e){
			$db->rollBack();
			echo $e->getMessage();exit();
		}
	}
	
	public function addNewOccupationPopup($_data){
		$_arr=array(
				'doc_name' => $_data['doc_name'],
				'create_date' => Zend_Date::now(),
				'status'   => $_data['status_j'],
				'user_id'	  => $this->getUserId()
		);
		return  $this->insert($_arr);
	}
	
	
	public function getDocumentById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_document WHERE document_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function updateDiscount($_data){
		$_arr=array(
				'doc_name' => $_data['doc_name'],
				'create_date' => Zend_Date::now(),
				'status'   => $_data['status'],
				'user_id'	  => $this->getUserId()
		);
		$where=$this->getAdapter()->quoteInto("document_id=?", $_data["id"]);
		$this->update($_arr, $where);
	}
	function getAllDiscount($search){
		$db = $this->getAdapter();
		$sql = " SELECT 
					document_id AS id,
					doc_name,
					create_date,
				   (SELECT  CONCAT(first_name) FROM rms_users WHERE id=user_id )AS user_name,
					status
				FROM 
					rms_document ";
		
		$order = ' ORDER BY id DESC '; 
		$where = ' WHERE doc_name!="" ';
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " doc_name LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.=' AND status='.$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);
	}	
	public function addDocumenttion($_data){//ajax
		$_arr=array(
				'doc_name' => $_data['doc_name'],
				'create_date' => Zend_Date::now(),
				'status'   => 1,
				'user_id'	  => $this->getUserId()
		);
		return  $this->insert($_arr);
	}
}

