<?php

class Global_Model_DbTable_DbCommune extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_commune';
    public function getUserId(){
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	return $dbg->getUserId();
    	 
    }
	public function addCommune($_data,$id=null){
		$_arr=array(
			'code' => $_data['code'],
			'district_id' => $_data['district_name'],
			'commune_namekh'=> $_data['commune_namekh'],
			'commune_name'=> $_data['commune_name'],
			'modify_date' => Zend_Date::now(),
			'user_id'	  => $this->getUserId()
		);
		if(!empty($id)){
			$status = empty($_data['status'])?0:1;
			$_arr['status']=$status;
			$where = 'com_id = '.$id;
			return  $this->update($_arr, $where);
		}else{
			$_arr['status']=1;
			return  $this->insert($_arr);
		}
	}
	public function addCommunebyAJAX($_data,$id=null){
		$_arr=array(
				'district_id' => $_data['district_nameen'],
				'commune_namekh'=> $_data['commune_namekh'],
				'commune_name'=> $_data['commune_nameen'],
				'status'	  => 1,
				'modify_date' => Zend_Date::now(),
				'user_id'	  => $this->getUserId()
		);
			return  $this->insert($_arr);
	}
	
	public function getCommuneById($id){
		$db = $this->getAdapter();
		$sql=" SELECT c.com_id,c.code,c.district_id,c.commune_name,commune_namekh,displayby,c.modify_date,c.status,c.user_id,
				(SELECT pro_id FROM `ln_district` WHERE dis_id =c.district_id LIMIT 1 ) as pro_id
				FROM ln_commune AS c WHERE c.com_id = $id  LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
	function getAllCommune($search=null){
		$db = $this->getAdapter();
		$sql = " 
			SELECT 
				com.com_id
				,com.commune_namekh
				,com.commune_name
				,d.district_namekh AS district_name
				,com.modify_date
				,com.status
				,(SELECT u.first_name FROM rms_users AS u WHERE u.id=com.user_id LIMIT 1) As user_name
			FROM ln_commune AS com 
				LEFT JOIN ln_district AS d ON d.dis_id = com.district_id
			";
		
		$where = ' WHERE 1 ';
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " com.district_name LIKE '%{$s_search}%'";
			$s_where[] = " com.code LIKE '%{$s_search}%'";
			$s_where[] = " com.commune_name LIKE '%{$s_search}%'";
			$s_where[]=" com.commune_namekh LIKE '%{$s_search}%'";
			$where .=' AND '.implode(' OR ',$s_where);
		}
		if(!empty($search['district_name'])){
			$where.=" AND com.district_id=".$search['district_name'];
		}
		if(!empty($search['province_name'])){
			$where.=" AND d.pro_id=".$search['province_name'];
		}
		if($search['search_status']>-1){
			$where.=" AND com.status=".$search['search_status'];
		}
		$order = " ORDER BY com.com_id DESC ";
		
		return $db->fetchAll($sql.$where.$order);	
	}
	public function getCommuneBydistrict($distict_id){
		$db = $this->getAdapter();
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$lang = $_dbgb->currentlang();
		$field = 'commune_name';
		if ($lang==1){
			$field = 'commune_namekh';
		}
		$sql = "SELECT com_id AS id ,$field AS name FROM $this->_name WHERE status=1 AND commune_name!='' AND  $this->_name.district_id=".$db->quote($distict_id); 
		$rows=$db->fetchAll($sql);
		return $rows;
	}	
}