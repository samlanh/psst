<?php
class Accounting_Model_DbTable_DbProgram extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_program_name';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
    public function addprogram($_data){
    	$db = $this->getAdapter();
    		$_arr = array(
    				'title'=>$_data['add_title'],
    				'type'=>1,
    				'ser_cate_id'=>$_data['title'],
    				'description'=>$_data['desc'],
    				'create_date'=>Zend_Date::now(),
    				'status'=>$_data['status'],
    				'user_id'=>$this->getUserId(),
    		);
    		return ($this->insert($_arr));
    	
    } 
    public function serviceExist($service_name,$_type){
    	$db = $this->getAdapter();
    	$sql = "SELECT service_id FROM `rms_program_name` WHERE title= '".$service_name."' AND ser_cate_id=$_type";
    	return $db->fetchRow($sql);
    }
    public function updateprogram($_data){
    	$_arr=array(
	    			'title'=>$_data['add_title'],
	    			'ser_cate_id'=>$_data['title'],
    				'description'=>$_data['desc'],
    				'create_date'=>Zend_Date::now(),
    				'status'=>$_data['status'],
    				'user_id'=>$this->getUserId());
    	$where=$this->getAdapter()->quoteInto("service_id=?", $_data["id"]);
    	$this->update($_arr, $where);
    }
    public function getProgramById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM rms_program_name WHERE service_id = ".$id;
    	return $db->fetchRow($sql);
    }	
    public function getAllProgramNames($search=''){
    	$db = $this->getAdapter();
    	$where='';
    	
    	$sql = "SELECT service_id AS id,p.title
    	,(SELECT title FROM `rms_program_type` WHERE id=ser_cate_id LIMIT 1) AS cate_name
    	,`desc`,p.`status`,p.`create_date`
    	,(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE p.user_id=id ) AS user_name
    	FROM `rms_program_name` AS p Where type=1 ";
    	$order=" ORDER BY p.title";
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$where.=" AND p.title LIKE '%".$search['txtsearch']."%'";
    	}
    	/*if($search['type']>-1){
    	 $where.= " AND type = ".$search['type'];
    	}*/
    	if($search['status']>-1){
    		$where.= " AND p.status = '".$search['status']."'";
    	}
    	if($search['status']>-1){
    		//$where.= " AND status = '".$search['status']."'";
    	}
    	return $db->fetchAll($sql.$where.$order);
    }	
}



