<?php

class Global_Model_DbTable_DbDistrict extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_district';
    public function getUserId(){
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	return $dbg->getUserId();
    }
    
	public function addDistrict($_data){
		$_arr=array(
				'code'	  	  => $_data['code'],
				'pro_id'	  => $_data['province_name'],
				'district_name'=> $_data['district_name'],
				'district_namekh'=> $_data['district_namekh'],
				'status'	  => 1,
				'modify_date' => Zend_Date::now(),
				'user_id'	  => $this->getUserId()
		);
		if(!empty($_data['id'])){
			$status = empty($_data['status'])?0:1;
			$_arr['status']=$status;
			$where = 'dis_id = '.$_data['id'];
			return  $this->update($_arr, $where);
		}else{
			return  $this->insert($_arr);
		}
	}
	public function addDistrictByAjax($_data){
			$_arr=array(
				'pro_id'	  		=> $_data['province_names'],
				'district_name'	  	=> $_data['pop_district_name'],
				'district_namekh'	=> $_data['pop_district_namekh'],
				'status'	  		=> 1,
				'modify_date' 		=> Zend_Date::now(),
				'user_id'	  		=> $this->getUserId()
			);
			return  $this->insert($_arr);
	}
	public function getDistrictById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM $this->_name WHERE dis_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	function getAllDistrict($search=null){
		$db = $this->getAdapter();
		$sql = "SELECT
					dis_id,
					district_namekh,district_name,
				    (SELECT province_kh_name FROM rms_province WHERE province_id=pro_id limit 1) As province_name
					,modify_date,
					status,
				    (SELECT first_name FROM rms_users WHERE id=user_id LIMIT 1) As user_name
		 		FROM $this->_name ";
			$where = ' WHERE 1 ';
		
			if($search['search_status']>-1){
				$where.= " AND status = ".$search['search_status'];
			}
			if(!empty($search['province_name'])){
				$where.=" AND pro_id=".$search['province_name'];
			}
			if(!empty($search['adv_search'])){
				$s_where=array();
				$s_search=addslashes(trim($search['adv_search']));
				$s_where[]=" code LIKE '%{$s_search}%'";
				$s_where[]=" district_name LIKE '%{$s_search}%'";
				$s_where[]=" district_namekh LIKE '%{$s_search}%'";
				$where.=' AND ('.implode('OR',$s_where).')';
			}
			$where.=" ORDER BY dis_id DESC ";
			return $db->fetchAll($sql.$where);	
	}	
	public function getDistrictByIdProvince($pro_id){
		$db = $this->getAdapter();
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$lang = $_dbgb->currentlang();
		$field = 'district_name';
		if ($lang==1){
			$field = 'district_namekh';
		}
		$sql = "SELECT dis_id AS id ,$field AS name FROM $this->_name WHERE status=1 AND pro_id = ".$db->quote($pro_id);
		$rows=$db->fetchAll($sql);
		return $rows;
	}
// 	public function addDistrictByAjax($_data){
// 		$_arr=array(
// 				'pro_id'	  		=> $_data['province_names'],
// 				'district_name'	  	=> $_data['pop_district_name'],
// 				'district_namekh'	=> $_data['pop_district_namekh'],
// 				'status'	  		=> 1,
// 				'modify_date' 		=> Zend_Date::now(),
// 				'user_id'	  		=> $this->getUserId()
// 		);
	
// 		return  $this->insert($_arr);
// 	}
}

