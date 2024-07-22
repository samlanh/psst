<?php

class Global_Model_DbTable_DbStuentType extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_view';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;  	 
    }
	public function addViewItemsByType($_data){
		$db = $this->getAdapter();
		try{
			$type = empty($_data["type"]) ? 0 : $_data["type"];
			$dbg= New Application_Model_DbTable_DbGlobal;
			$keyCode = $dbg->getLastKeycodeByType($type);

	  		$arr = array(
	  				'name_kh'	    => $_data['name_kh'],
	  				'name_en'	    => $_data['name_en'],
	  				'shortcut'	    => empty($_data['shortcut']) ? "" : $_data['shortcut'],
	  				'note'	    	=> empty($_data['note']) ? "" : $_data['note'],
					'type'		    => $type,
					'status'		=> 1,
	  				'key_code'		=> $keyCode,
				
	  		);
			$this->_name = "rms_view";
			$this->insert($arr);
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getViewItemsByTypeInfo($_data){
		
		$id = empty($_data["id"]) ? 0 : $_data["id"];
		$type = empty($_data["type"]) ? 0 : $_data["type"];
		$db = $this->getAdapter();
		$sql = " SELECT * FROM rms_view WHERE type=".$type." AND key_code = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function updateViewItemsByType($_data){
		$db = $this->getAdapter();
		$status = empty($_data['status'])?0:1;
		$type = empty($_data["type"]) ? 0 : $_data["type"];
		$_arr=array(
				'name_kh'	    => $_data['name_kh'],
				'name_en'	    => $_data['name_en'],
				'shortcut'	    => empty($_data['shortcut']) ? "" : $_data['shortcut'],
				'note'	    	=> empty($_data['note']) ? "" : $_data['note'],
  				'status'	    => $status,
				
		);
		$where=$this->getAdapter()->quoteInto(" type=".$type." AND key_code=?", $_data["id"]);
		$this->_name = "rms_view";
		$this->update($_arr,$where);
	}
	function getAllViewItemsByType($search){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  key_code AS id,
				  shortcut,
				  name_kh,
				  name_en
				";
		$type = empty($search["type"]) ? 0 : $search["type"];
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM `rms_view`  WHERE type =$type  ";
		
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
			$s_where[] = " shortcut LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1 AND $search['status']!=''){
			$where.=' AND status='.$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);
	}
	
	
	public function addPopupView($_data){
		$db = $this->getAdapter();
		try{
	 
			$dbg= New Application_Model_DbTable_DbGlobal;
			$keyCode = $dbg->getLastKeycodeByType($_data['viewType']);
	  		$arr = array(
	  				'name_kh'	    => $_data['popTitleKh'],
	  				'name_en'	    => $_data['popTitleEn'],
	  				'note'	    	=> empty($_data['popNote'])? "" : $_data['popNote'],
	  				'shortcut'	    	=> empty($_data['popShortcut'])? "" : $_data['popShortcut'],
					'type'		    => $_data['viewType'],
					'status'		=> 1,
	  				'key_code'		=> $keyCode,
	  		);
			$this->insert($arr);
			return $keyCode;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
}