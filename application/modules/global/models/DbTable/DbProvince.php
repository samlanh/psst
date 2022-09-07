<?php

class Global_Model_DbTable_DbProvince extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_province';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    	 
    }
    public function addNewProvince($_data){
    	$_arr=array(
    			'province_en_name'	  => $_data['en_province'],
    			'province_kh_name'	  => $_data['kh_province'],
    			'modify_date' => Zend_Date::now(),
    			'status'   => 1,
    			'user_id'	  => $this->getUserId()
    	);
    	return  $this->insert($_arr);
    }
	public function getProvinceById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_province WHERE province_id = ".$id;
		$sql.=" LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
    public function updateProvince($_data,$id){
		$status = empty($_data['status'])?0:1;
    	$_arr=array(
    			'province_en_name'	  => $_data['en_province'],
    			'province_kh_name'	  => $_data['kh_province'],
    			'modify_date' 		=> Zend_Date::now(),
    			'status'   			=> $status,
    			'user_id'	  	=> $this->getUserId()
    	);
    	$where=$this->getAdapter()->quoteInto("province_id=?", $id);
    	$this->update($_arr, $where);
    }
    function getAllProvince($search=null){
    	$db = $this->getAdapter();
    	$sql = " SELECT province_id AS id,province_en_name,province_kh_name,modify_date,
    	(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE id=user_id )AS user_name,
    	status
    	FROM rms_province
    	WHERE 1 ";
    	$order=" order by id DESC";
    	$where = '';
    	if(!empty($search['title'])){
    		$where.=" AND ( province_en_name LIKE '%".$search['title']."%' OR province_kh_name LIKE '%".$search['title']."%') ";
    	}
    	if($search['status']>-1){
    		$where.= " AND status = ".$db->quote($search['status']);
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
}

