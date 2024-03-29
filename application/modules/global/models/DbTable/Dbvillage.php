<?php
class Global_Model_DbTable_DbVillage extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_village';
    public function getUserId(){
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	return $dbg->getUserId();
    }
    
	public function addVillage($_data){
		$_arr=array(
			'commune_id'	=> $_data['commune_name'],
			'village_name'	=> $_data['village_name'],
			'village_namekh'=> $_data['village_namekh'],
			'modify_date' 	=> Zend_Date::now(),
			'user_id'	  	=> $this->getUserId()
		);
		if(!empty($_data['id'])){
			$status = empty($_data['status'])?0:1;
			$_arr['status']=$status;
			$where = 'vill_id = '.$_data['id'];
			return  $this->update($_arr, $where);
		}else{
			$_arr['status']=1;
			return  $this->insert($_arr);
		}
	}

	function addVillageByAjax($_data){
		$db = $this->getAdapter();
		$_arr=array(
			'commune_id'	  => $_data['commune_name'],
			'village_name'	  => $_data['village_name'],
			'village_namekh'	  => $_data['village_namekh'],
			'status'	  => $_data['status'],
			'modify_date' => Zend_Date::now(),
			'user_id'	  => $this->getUserId()
		);
		return  $this->insert($_arr);
	}
	
	public function getVillageById($id){
		$db = $this->getAdapter();
		$sql=" SELECT v.vill_id,v.code,v.commune_id,v.village_name,v.village_namekh,v.displayby,v.modify_date,
					v.status,v.user_id,d.dis_id,d.pro_id FROM 
			   `ln_village` AS v,ln_commune AS c,ln_district AS d
			   WHERE v.commune_id=c.com_id AND v.vill_id AND c.district_id=d.dis_id AND
			  v.vill_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	function getAllVillage($search=null){
		$db = $this->getAdapter();
// 		$sql =" CALL st_getAllVillage('',1) ";
		$sql =" SELECT
			v.vill_id,v.village_namekh,v.village_name,
			(SELECT commune_namekh FROM ln_commune WHERE v.commune_id=com_id LIMIT 1) AS commune_name,
			d.district_namekh,p.province_kh_name,
			v.modify_date,v.status, 
			(SELECT first_name FROM rms_users WHERE id=v.user_id LIMIT 1) AS user_name
			FROM ln_village AS v,`ln_commune` AS c, `ln_district` AS d , `rms_province` AS p
			WHERE v.commune_id = c.com_id AND c.district_id = d.dis_id AND d.pro_id = p.province_id ";
		$where = '';
        if($search['province_name']>0){
        	$where.= " AND p.province_id = ".$search['province_name'];
        }
        if(!empty($search['district_name'])){
        	$where.= " AND d.dis_id = ".$search['district_name'];
        }
        if($search['commune_name']>0){
        	$where.= " AND c.com_id = ".$search['commune_name'];
        }
        
		if($search['search_status']>-1){
			$where.= " AND v.status = ".$search['search_status'];
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = $search['adv_search'];
			$s_where[] = " v.village_name LIKE '%{$s_search}%'";
			$s_where[]=" v.village_namekh LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$order= ' ORDER BY v.vill_id DESC ';
		return $db->fetchAll($sql.$where.$order);	
	}

    public function getAllvillagebyCommune($village_id){
		$db = $this->getAdapter();
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$lang = $_dbgb->currentlang();
		$field = 'village_name';
		if ($lang==1){
			$field = 'village_namekh';
		}
		$sql = "SELECT vill_id AS id,$field AS name FROM $this->_name WHERE village_name!='' AND status=1 AND commune_id=".$db->quote($village_id);
		$rows=$db->fetchAll($sql);
		return $rows;
	}	
}
