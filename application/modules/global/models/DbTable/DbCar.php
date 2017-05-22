<?php

class Global_Model_DbTable_DbCar extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_car';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
    
    function addcar($data){
    	$arr=array(
    			'carid'=>$data['Car_ID'],
    			'carname'=>$data['Car_Name'],
    			'drivername'=>$data['Driver_Name'],
    			'tel'=>$data['Tel'],
    			'zone'=>$data['Zone'],
    			'note'=>$data['Note'],
    			'userid'=>$this->getUserId(),
    			'status'=>$data['status']
    			);  	
    	$this->insert($arr);   	
    }
    function getAllCars($search){
    	$db = $this->getAdapter();
    	$sql = " SELECT id,carid,carname,drivername,tel,zone,note
    	FROM rms_car WHERE 1 ";
    	$order=" order by id DESC";
    	$where = ' ';
	    if(empty($search)){
	    		return $db->fetchAll($sql.$order);
	    }
	    if(!empty($search['title'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['title']));
		 	$s_where[] = " carid LIKE '%{$s_search}%'";
	    	$s_where[] = " carname LIKE '%{$s_search}%'";
	    	$s_where[] = " drivername LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
	    }
    	return $db->fetchAll($sql.$where.$order);
    }
    
    public function getCarById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM rms_car WHERE id = ".$id;
    	$sql.=" LIMIT 1";
    	$row=$db->fetchRow($sql);
    	return $row;
    }
    public function updateCar($data){
    	$_arr=array(
    			'carid'=>$data['Car_ID'],
    			'carname'=>$data['Car_Name'],
    			'drivername'=>$data['Driver_Name'],
    			'tel'=>$data['Tel'],
    			'zone'=>$data['Zone'],
    			'note'=>$data['Note'],
    			'status'=>$data['status']
    	);
    	$where=$this->getAdapter()->quoteInto("id=?", $data['id']);
    	$this->update($_arr, $where);
    }
    
    
}
	

