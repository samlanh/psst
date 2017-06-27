<?php

class Allreport_Model_DbTable_DbRptFee extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    function getAllTuitionFee($search){
    	$db=$this->getAdapter();
    	
    	$_db=new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql = "SELECT id,CONCAT(from_academic,' - ',to_academic) AS academic,note,generation,
    			(select branch_namekh from rms_branch where br_id = branch_id) as branch_name,
    		    (select name_en from `rms_view` where `rms_view`.`type`=7 and `rms_view`.`key_code`=`rms_tuitionfee`.`time`)AS time,
    			create_date ,status FROM `rms_tuitionfee`  WHERE 1  $branch_id  ";
    	$where= '';
    	$order=" ORDER BY id DESC ";
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	
    	if(!empty($search['year'])){
    		$where.=" AND id = ".$search['year'] ;
    	}
    	
    	if(!empty($search['branch_id'])){
    		$where.=" AND branch_id = ".$search['branch_id'] ;
    	}
    	
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$abc = $s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " CONCAT(from_academic,'-',to_academic) LIKE '%{$s_search}%'";
    		$s_where[] = " rms_tuitionfee.generation LIKE '%{$s_search}%'";
    		$s_where[] = " rms_tuitionfee.from_academic LIKE '%{$s_search}%'";
    		$s_where[] = " rms_tuitionfee.to_academic LIKE '%{$s_search}%'";
    		//$s_where[] = " (SELECT major_enname FROM rms_major WHERE rms_major.major_id = (select class_id from rms_tuitionfee_detail where rms_tuitionfee_detail.fee_id = rms_tuitionfee.id  limit 1)limit 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (select name_en from rms_view where rms_view.type=7 and rms_view.key_code=rms_tuitionfee.time) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}

    	return $db->fetchAll($sql.$where.$order);
    }
    function getFeebyOther($fee_id,$grade_search){
    	//print_r($fee_id);exit();
    	$db = $this->getAdapter();
    	$sql = "select *,
			    	(SELECT CONCAT(major_enname) FROM `rms_major` WHERE major_id=rms_tuitionfee_detail.class_id) as class,
			    	(select name_en from rms_view where type=4 and key_code=session) as session
			    	from rms_tuitionfee_detail where fee_id = $fee_id ";
    	
    	$where = ' ';
    	$order = ' ORDER BY id';
    	
    	if(!empty($grade_search)){
    		$where.=" AND class_id = ".$grade_search;
    	}
    	//echo $sql.$where.$order;
    	$result = $db->fetchAll($sql.$where.$order);
    	
    	if(!empty($result)){
    		return $result;
    	}
    	
    }
    
    function getAllYearFee(){
    	$db=$this->getAdapter();
    	$_db=new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	$sql=" select id, CONCAT(from_academic,'-',to_academic,'(',generation,')') as year,(select name_en from rms_view where type=7 and key_code=time) as time from rms_tuitionfee where 1  $branch_id  ";
    	return $db->fetchAll($sql);
    }
    
    
}
   
    
   