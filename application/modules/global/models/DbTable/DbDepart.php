<?php

class Global_Model_DbTable_DbDepart extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_discount';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;  	 
    }
	public function addNewDepartment($_data){
	try{
  		$db = $this->getAdapter();
  		$key_code = $this->getLastKeycodeByType(21);
//   		$sql="SELECT id FROM rms_view WHERE status =".$data['status_na'];
//   		$sql.=" AND name_kh='".$data['name_kh']."'";
//   		$rs = $db->fetchOne($sql);
//   		if(!empty($rs)){
//   			return -1;
//   		}
  		$arr = array(
  				'name_en'	=>$_data['title_en'],
  				'name_kh'	=>$_data['title_kh'],
  				'status'	=>$_data['status_na'],
  				'key_code'	=>$key_code,
  				'displayby'	=>1,
  				'type'=>25,
  				 
  		);
  		$this->_name="rms_view";
  		return $this->insert($arr);
  	}catch (Exception $e){
  		echo '<script>alert('."$e".');</script>';
  	}
	}
	function getLastKeycodeByType($type){
		$sql = "SELECT key_code FROM `rms_view` WHERE type=$type ORDER BY key_code DESC LIMIT 1 ";
		$db =$this->getAdapter();
		$number = $db->fetchOne($sql);
		return $number+1;
	}
	
	public function addNewOccupationPopup($_data){
		$_arr=array(
				'dis_name' => $_data['dis_name'],
				'create_date' => Zend_Date::now(),
				'status'   => $_data['status_j'],
				'user_id'	  => $this->getUserId()
		);
		return  $this->insert($_arr);
	}
	
	
	public function getDiscountById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_discount WHERE disco_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function updateDiscount($_data){
		$_arr=array(
				'dis_name' => $_data['dis_name'],
				'create_date' => Zend_Date::now(),
				'status'   => $_data['status'],
				'user_id'	  => $this->getUserId()
		);
		$where=$this->getAdapter()->quoteInto("disco_id=?", $_data["id"]);
		$this->update($_arr, $where);
	}
	function getAllDepartment($search){
		$db = $this->getAdapter();
		$sql = " SELECT 
					id,
					CONCAT (name_kh) AS name_english,
					CONCAT (name_en) AS name_khmer,					
					status
				FROM 
					rms_view
				WHERE 
					name_en!='' AND TYPE=25";
		$where = ' AND name_en!="" ';
		$order = ' ORDER BY id DESC ';
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " dis_name LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.=' AND status='.$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);
	}	
	public function addDiscounttion($_data){//ajax
		$_arr=array(
				'dis_name' => $_data['dis_name'],
				'create_date' => Zend_Date::now(),
				'status'   => 1,
				'user_id'	  => $this->getUserId()
		);
		return  $this->insert($_arr);
	}
}

