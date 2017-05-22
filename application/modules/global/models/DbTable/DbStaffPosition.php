<?php

class Global_Model_DbTable_DbStaffPosition extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_staff_position';
    
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    
	public function addNewStaffPosition($_data){
		$_arr=array(
				'title'	  		=> $_data['title'],
				'create_date' 	=> date('Y-m-d'),
				'user_id'	  	=> $this->getUserId()
		);
		return  $this->insert($_arr);
	}
	
	public function getStaffPositionById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_staff_position WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	
	public function updateStaffPosition($data){
		$_arr=array(
				'title'	  		=> $data['title'],
				'status' 		=> $data['status'],
				'user_id'	  	=> $this->getUserId()
		);
		$where= " id = ".$data['id'] ;
		$this->update($_arr, $where);
	}
	
	function getAllStaffPosition($search=null){
		$db = $this->getAdapter();
		$sql = " select id , title , (select name_en from rms_view where type=1 and key_code=status) as status , create_date from rms_staff_position where status =1 ";
		
		$order=" order by id DESC ";
		
		return $db->fetchAll($sql.$order);	
	}	
	
}

