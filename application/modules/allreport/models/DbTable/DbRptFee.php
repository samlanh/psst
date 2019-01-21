<?php

class Allreport_Model_DbTable_DbRptFee extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    function getAllTuitionFee($search,$type=1){
    	$db=$this->getAdapter();
    	
    	$_db=new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$grade = "rms_itemsdetail.title";
    		$degree = "rms_items.title";
    		$branch = "branch_namekh";
    	}else{ // English
    		$label = "name_en";
    		$grade = "rms_itemsdetail.title_en";
    		$degree = "rms_items.title_en";
    		$branch = "branch_nameen";
    	}
    	
    	$sql = "SELECT id,CONCAT(from_academic,' - ',to_academic) AS academic,note,generation,
    			(select $branch from rms_branch where br_id = branch_id) as branch_name,
    		    (select $label from `rms_view` where `rms_view`.`type`=7 and `rms_view`.`key_code`=`rms_tuitionfee`.`time`)AS time,
    		    (SELECT $label FROM `rms_view` WHERE `rms_view`.`type`=12 AND `rms_view`.`key_code`=`rms_tuitionfee`.`is_finished`)AS is_process,
    			create_date ,status FROM `rms_tuitionfee`  WHERE 1  $branch_id  ";
    	$sql.=" AND type= $type ";
    	$where= ' ';
    	$order=" ORDER BY id DESC ";
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	
    	if(!empty($search['study_year'])){
    		$where.=" AND id = ".$search['study_year'] ;
    	}
    	
    	if(!empty($search['branch_id'])){
    		$where.=" AND branch_id = ".$search['branch_id'] ;
    	}
    	
    	if($search['finished_status']>-1){
    		$where.=" AND `rms_tuitionfee`.`is_finished` = ".$search['finished_status'] ;
    	}
    	
    	if(!empty($search['generation']) AND $search['generation']!=-1){
    		$where.=" AND generation = '".$search['generation']."'" ;
    	}
    	
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " CONCAT(from_academic,'-',to_academic) LIKE '%{$s_search}%'";
    		$s_where[] = " rms_tuitionfee.generation LIKE '%{$s_search}%'";
    		$s_where[] = " rms_tuitionfee.from_academic LIKE '%{$s_search}%'";
    		$s_where[] = " rms_tuitionfee.to_academic LIKE '%{$s_search}%'";
    		$s_where[] = " (select name_en from rms_view where rms_view.type=7 and rms_view.key_code=rms_tuitionfee.time LIMIT 1) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    function getFeebyOther($fee_id,$grade_search,$degree_id){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$grade = "i.title";
    		$degree = "rms_items.title";
    		$branch = "b.branch_namekh";
    	}else{ // English
    		$label = "name_en";
    		$grade = "i.title_en";
    		$degree = "rms_items.title_en";
    		$branch = "b.branch_nameen";
    	}
    	$sql = "select tf.*,
			    	$grade as class,
			    	(SELECT $degree FROM `rms_items` WHERE rms_items.id=i.items_id LIMIT 1) as degree
			    	FROM 
			    		rms_tuitionfee_detail as tf,
			    		rms_itemsdetail as i 
			    	WHERE 
    					i.id=tf.class_id 
    					AND tf.fee_id = $fee_id 
    		";
    	
    	$where = ' ';
    	$order = ' ORDER BY tf.id ASC';
    	
    	if($degree_id>0){
    		$where.=" AND i.items_id = ".$degree_id;
    	}
    	if($grade_search>0){
    		$where.=" AND tf.class_id = ".$grade_search;
    	}

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
   
    
   