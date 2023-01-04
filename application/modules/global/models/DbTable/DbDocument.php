<?php

class Global_Model_DbTable_DbDocument extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_document_type';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;  	 
    }
	public function addNewDocument($_data){
		$db = $this->getAdapter();
		try{
			$title = trim($_data['name']);
			$titleEn = empty($_data['name_en'])?$title:$_data['name_en'];
			$titleEn = trim($titleEn);
			$sql="SELECT id FROM rms_document_type WHERE status =1 ";
			$sql.=" AND name='".$title."'";
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}			
			$_arr=array(
					'name'	  	  => $title,
					'name_en'	  => $titleEn,
					'create_date' => date("Y-m-d"),
					'types'		  => $_data['types'],
					'status'	  => 1,
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
				'name' 		  => $_data['name'],
				'create_date' => date("Y-m-d"),
				'status'  	  => $_data['status_j'],
				'user_id'	  => $this->getUserId()
		);
		return  $this->insert($_arr);
	}
	public function getDocumentById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_document_type WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function updateDocument($_data){
		$title = trim($_data['name']);
		$titleEn = empty($_data['name_en'])?$title:$_data['name_en'];
		$titleEn = trim($titleEn);
		$status = empty($_data['status'])?0:1;
		$_arr=array(
				'name' 			=> $title,
				'name_en' 		=> $titleEn,
				'create_date' 	=> date("Y-m-d"),
				'status'		=> $status,
				'types'		  	=> $_data['types'],
				'user_id'	  	=> $this->getUserId()
		);
		$where=$this->getAdapter()->quoteInto("id=?", $_data["id"]);
		$this->update($_arr, $where);
	}
	function getAllDocument($search){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = " SELECT 
				    id,
					name,
					name_en,
					create_date,
					CASE    
					WHEN  types = 1 THEN '".$tr->translate("STUDENT_DOCUMENT")."'
					WHEN  types = 2 THEN '".$tr->translate("TEACHER_DOCUMENT")."'
					END types,
				   (SELECT  CONCAT(first_name) FROM rms_users WHERE id=user_id )AS user_name
				";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM `rms_document_type` ";
		$where = ' WHERE name!="" ';
		$order = ' ORDER BY id DESC ';
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = "name LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['type_search']){
			$where.=' AND types='.$search['type_search'];
		}
		if($search['status']>-1){
			$where.=' AND status='.$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);
	}	
	public function addDocumenttion($_data){//ajax
		$_arr=array(
				'name' 		  => $_data['name'],
				'create_date' => date("Y-m-d"),
				'status'      => 1,
				'user_id'	  => $this->getUserId()
		);
		return  $this->insert($_arr);
	}
}