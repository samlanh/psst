<?php

class Global_Model_DbTable_DbDepart extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_department';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;  	 
    }
	public function addNewDepartment($_data){
		$db = $this->getAdapter();
		//print_r($_data); exit();
		try{
  		$sql="SELECT depart_id FROM rms_department WHERE status =".$_data['status'];
  		$sql.=" AND depart_namekh='".$_data['depart_namekh']."'";
  		$rs = $db->fetchOne($sql);
  		if(!empty($rs)){
  			return -1;
  		}
  		$arr = array(
  				'depart_namekh'	=>$_data['depart_namekh'],
  				'depart_nameen'	=>$_data['depart_nameen'],
  				'create_date' 	=> Zend_Date::now(),
  				'status'		=>$_data['status'],
				'user_id'		=> $this->getUserId()
  		);
		$this->insert($arr);
		}catch (Exception $e){
			$db->rollBack();
			echo $e->getMessage();exit();
		}
	}
	public function getDepartmentById($id){
		$db = $this->getAdapter();
		$sql = " SELECT * FROM rms_department WHERE depart_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function updateDepartment($_data){
		$db = $this->getAdapter();
		$_arr=array(
				'depart_namekh'	=>$_data['depart_namekh'],
  				'depart_nameen'	=>$_data['depart_nameen'],
				'create_date' => Zend_Date::now(),
  				'status'	=>$_data['status'],
				'user_id'	  => $this->getUserId()
		);
		$where=$this->getAdapter()->quoteInto("depart_id=?", $_data["id"]);
		$this->update($_arr,$where);
		//print_r($_data); exit();
	}
	function getAllDepartment($search){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  depart_id AS id,
				  depart_namekh,
				  depart_nameen,
				  create_date,
				  status,
				  (SELECT  CONCAT(first_name) FROM rms_users WHERE id=user_id )AS user_name
				  FROM `rms_department` where 1";
		$where = '';
		$order = ' ORDER BY id DESC ';
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " depart_namekh LIKE '%{$s_search}%'";
			$s_where[] = " depart_nameen LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.=' AND status='.$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);
	}	
}
