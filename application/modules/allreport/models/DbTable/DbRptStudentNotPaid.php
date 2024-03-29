<?php

class Allreport_Model_DbTable_DbRptStudentNotPaid extends Zend_Db_Table_Abstract
{
    function getAllStudentNotPaid($search){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('s.branch_id');


    	$sql="SELECT
	    	s.stu_id,
	    	(CASE WHEN stu_khname IS NULL THEN stu_enname ELSE stu_khname END) AS name,
	    	s.tel,
	    	s.`stu_code`,
	    	s.`stu_khname`,
	    	s.`stu_enname`,
	    	s.tel,
	    	(SELECT rms_items.title FROM rms_items WHERE rms_items.type=1 AND rms_items.id = g.`degree`) AS degree,
	    	(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.items_type =1 AND rms_itemsdetail.id = g.grade ) AS grade,
	    	(SELECT name_en FROM rms_view WHERE `type` = 4 AND key_code = g.`session`) AS `session`,
	    	(SELECT group_code FROM `rms_group` WHERE id=gds.group_id LIMIT 1) as group_name
	    	FROM
				rms_student AS s,
				`rms_group_detail_student` AS gds,
				`rms_group` AS g
	    	WHERE
			
			gds.itemType=1 
			AND s.status=1
	    	AND gds.`stu_id`=s.stu_id
	    	AND g.id = gds.`group_id`
	    	AND gds.`is_pass`=0
	    	AND gds.`type`=1 ";
    	$datenow = date("Y-m-d");
	
     	$order=" ORDER by gds.group_id ASC ,s.stu_khname ASC ";
     	$where=" ";
     	if(($search['grade_all']>0)){
     		$where.= " AND g.grade = ".$search['grade_all'];
     	}
     	if(($search['session']>0)){
     		$where.= " AND g.session = ".$search['session'];
     	}
     	if(($search['stu_name']>0)){
     		$where.= " AND s.stu_id = ".$search['stu_name'];
     	}
     	if(($search['stu_code']>0)){
     		$where.= " AND s.stu_id = ".$search['stu_code'];
     	}
     	if(($search['group']>0)){
     		$where.= " AND gds.group_id = ".$search['group'];
     	}
    		
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    		
    	return $db->fetchAll($sql.$where.$order);
    }
    
}
   
    
   