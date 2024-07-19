<?php

class Global_Model_DbTable_DbStuentType extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_view';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;  	 
    }
	public function addStudentType($_data){
		$db = $this->getAdapter();
		try{
	 
			$dbg= New Application_Model_DbTable_DbGlobal;
			$keyCode = $dbg->getLastKeycodeByType(40);

	  		$arr = array(
	  				'name_kh'	    => $_data['name_kh'],
	  				'name_en'	    => $_data['name_en'],
					'type'		    => 40,
					'status'		=> 1,
	  				'key_code'		=> $keyCode,
				
	  		);
			$this->insert($arr);
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getStudentTypeById($id){
		$db = $this->getAdapter();
		$sql = " SELECT * FROM rms_view WHERE type=40 AND key_code = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function updateStudentType($_data){
		$db = $this->getAdapter();
		$status = empty($_data['status'])?0:1;
		$_arr=array(
				'name_kh'	    => $_data['name_kh'],
				'name_en'	    => $_data['name_en'],
  				'status'	    => $status,
				
		);
		$where=$this->getAdapter()->quoteInto(" type=40 AND key_code=?", $_data["id"]);
		$this->update($_arr,$where);
	}
	function getAllStudentType($search){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  key_code AS id,
				  name_kh,
				  name_en
				";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM `rms_view`  WHERE type =40  ";
		
		$where = '';
		$order = ' ORDER BY key_code DESC ';
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " name_kh LIKE '%{$s_search}%'";
			$s_where[] = " name_en LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1 AND $search['status']!=''){
			$where.=' AND status='.$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);
	}	
}