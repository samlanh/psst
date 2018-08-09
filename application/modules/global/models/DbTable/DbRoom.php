<?php

class Global_Model_DbTable_DbRoom extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_room';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    	 
    }
	public function addNewRoom($_data){
		$_arr=array(
				'branch_id'	  => $_data['branch_id'],
				'floor'	  	  => $_data['floor'],
				'room_name'	  => $_data['classname'],
				'modify_date' => Zend_Date::now(),
				'is_active'   => $_data['status'],
				'user_id'	  => $this->getUserId()
		);
		return  $this->insert($_arr);
	}
	public function addAjaxRoom($_data){
		$_arr=array(
				'branch_id'	  => $_data['branch_id'],
				'floor'	  	  => $_data['floor'],
				'room_name'	  => $_data['classname'],
				'modify_date' => Zend_Date::now(),
				'is_active'   => 1,
				'user_id'	  => $this->getUserId()
		);
		return  $this->insert($_arr);
	}
	public function getRoomById($id, $classroom){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_room WHERE and room_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	
// 	public function getRoomsById($id){
// 		$db = $this->getAdapter();
// 		$sql = "SELECT floor,classname FROM rms_room WHERE room_id =$id
// 		";
// 		$row=$db->fetchOne($sql);
// 		return $row;
// 	}
	
	public function updateRoom($data){
		
		$_arr=array(
				'branch_id'	  => $data['branch_id'],
				'floor'	  	  => $data['floor'],
				'room_name'	  => $data['classname'],
				'modify_date' => Zend_Date::now(),
				'is_active'   => $data['status'],
				'user_id'	  => $this->getUserId()
		);
		$where=$this->getAdapter()->quoteInto("room_id=?", $data["id"]);
		$this->update($_arr,$where);
	}
	function getAllRooms($search=null){
		$db = $this->getAdapter();
		$sql = " SELECT 
					room_id AS id,
					(SELECT CONCAT(branch_nameen) FROM rms_branch WHERE br_id =branch_id LIMIT 1) AS branch,
					floor,
					room_name,
					modify_date,
					(SELECT  CONCAT(first_name) FROM rms_users WHERE id=user_id )AS user_name,
					is_active as status
				FROM 
					rms_room
				WHERE 
					room_name != ''
			 ";
		$order=" order by id DESC ";
		$where = '';
		
		if(!empty($search['title'])){
			$search['title']=addslashes(trim($search['title']));
			$where.=" AND room_name LIKE '%".$search['title']."%'";
		}
		
		if(!empty($search['branch_id'])){
			$where.=" AND branch_id=".$search['branch_id'];
		}
		
		if($search['status']>-1){
			$where.= " AND is_active = ".$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);	
	}	
}

