<?php

class Issuesetting_Model_DbTable_DbTimeList extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_timeseting';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllTimeList($search=null){
    	$db = $this->getAdapter();
    	$sql = " SELECT
			en.id,en.title,en.value
			 ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->caseStatusShowImage("en.status");
    	$sql.=" FROM `rms_timeseting` AS en
			 WHERE 1  ";
    	
    	$order=" order by en.id DESC";
    	$where = '';
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[]= " en.title LIKE '%{$s_search}%'";
    		$s_where[]= " en.title_en LIKE '%{$s_search}%'";
    		$s_where[]= " en.value LIKE '%{$s_search}%'";
    		$s_where[]= " en.note LIKE '%{$s_search}%'";
    		$where .= ' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND en.status = ".$search['status'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    public function addTimeList($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$_arr = array(
	    		'title'=>$_data['title'],
    			'title_en'=>$_data['title'],
    			'value'=>$_data['value'],
	    		'note'=>$_data['note'],
    			'status'=>1,
	    		'modify_date'=>date("Y-m-d H:i:s"),
	    		'user_id'=>$this->getUserId(),
	    	);
    		$this->_name='rms_timeseting';
    		if (!empty($_data['id'])){
				$status=empty($_data['status'])?0:1;
    			$_arr['status']=$status;
    			$where = "id=".$_data['id'];
    			$this->update($_arr, $where);
    		}else{
    			$_arr['create_date']=date("Y-m-d H:i:s");
    			$this->insert($_arr);
    		}
    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function getTimeListByID($id){
    	$db =$this->getAdapter();
    	$sql="SELECT en.* FROM `rms_timeseting` AS en WHERE en.id=$id LIMIT 1";
    	return $db->fetchRow($sql);
    }
	function getAllTimeListActive(){
    	$db =$this->getAdapter();
    	$sql="SELECT DISTINCT en.value AS id,en.title AS `name` FROM `rms_timeseting` AS en WHERE en.status=1 ORDER BY en.value ASC";
    	return $db->fetchAll($sql);
    }
}