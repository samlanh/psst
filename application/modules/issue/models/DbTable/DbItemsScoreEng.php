<?php

class Issue_Model_DbTable_DbItemsScoreEng extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_exametypeeng';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    function getAllItesmScoreEn($search=null){
    	$db = $this->getAdapter();
    	$sql = " SELECT
			en.id,en.title,en.title_en,en.note
			 ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->caseStatusShowImage("en.status");
    	$sql.=" FROM `rms_exametypeeng` AS en
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
    		$s_where[]= " en.note LIKE '%{$s_search}%'";
    		$where .= ' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND en.status = ".$search['status'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    public function addItemsScoreEng($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$_arr = array(
	    		'title'=>$_data['title'],
    			'title_en'=>$_data['title_en'],
	    		'note'=>$_data['note'],
    			'status'=>1,
	    		'modify_date'=>date("Y-m-d H:i:s"),
	    		'user_id'=>$this->getUserId(),
	    	);
    		$this->_name='rms_exametypeeng';
    		if (!empty($_data['id'])){
    			$_arr['status']=$_data['status'];
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
    function getItemsEnByID($id){
    	$db =$this->getAdapter();
    	$sql="SELECT en.* FROM `rms_exametypeeng` AS en WHERE en.id=$id LIMIT 1";
    	return $db->fetchRow($sql);
    }
}