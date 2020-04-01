<?php

class Allreport_Model_DbTable_DbRptStudentChangeGroup extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student_change_group';
    public function getAllStudentChangeGroup($search){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
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
    	$sql = "SELECT 
    				scg.stu_id,
    				(SELECT CONCAT(stu_khname,' - ',last_name,' ',stu_enname) FROM `rms_student` WHERE `rms_student`.`stu_id`=scg.`stu_id` limit 1) AS name,
			    	(SELECT stu_code FROM `rms_student` WHERE `rms_student`.`stu_id`=scg.`stu_id` limit 1) AS stu_code,
					(SELECT $label FROM `rms_view` WHERE `rms_view`.`type`=2 and `rms_view`.`key_code`=(SELECT sex FROM `rms_student` WHERE `rms_student`.`stu_id`=scg.`stu_id` limit 1) limit 1)AS sex,
					(SELECT group_code from rms_group WHERE rms_group.id=scg.from_group limit 1) AS code,
					
					(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id =(SELECT academic_year FROM rms_group WHERE rms_group.id=scg.from_group limit 1 ) LIMIT 1) AS academic,
					
					(SELECT semester FROM rms_group WHERE rms_group.id=scg.from_group limit 1 ) AS semester,
					(SELECT $label FROM rms_view WHERE rms_view.type=4 and rms_view.key_code=(SELECT session FROM rms_group WHERE scg.from_group=rms_group.id LIMIT 1) LIMIT 1) AS session,
					(SELECT $grade FROM rms_itemsdetail WHERE `rms_itemsdetail`.`items_type`=1 AND rms_itemsdetail.id=(SELECT grade from rms_group WHERE scg.from_group=rms_group.id LIMIT 1) limit 1) AS grade,
					(SELECT room_name FROM rms_room WHERE rms_room.room_id=(select room_id from rms_group WHERE scg.from_group=rms_group.id) LIMIT 1) AS room_name,
					(SELECT start_date FROM rms_group WHERE rms_group.id=scg.from_group limit 1) AS start_date,
					
					(SELECT expired_date FROM rms_group WHERE rms_group.id=scg.from_group limit 1) AS expired_date,
					(SELECT group_code FROM rms_group WHERE rms_group.id=scg.to_group limit 1) AS to_code,
					
					(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1)AS to_academic,
					(SELECT semester FROM rms_group WHERE rms_group.id=scg.to_group limit 1) AS to_semester,
					(SELECT $label FROM rms_view WHERE rms_view.type=4 and rms_view.key_code=g.id limit 1) AS to_session,
					(SELECT $grade FROM rms_itemsdetail WHERE `rms_itemsdetail`.`items_type`=1 AND rms_itemsdetail.id=g.grade limit 1) AS to_grade,
					(SELECT room_name FROM rms_room WHERE rms_room.room_id=g.room_id limit 1) AS to_room_name,
				 	 scg.moving_date,
				 	 scg.note,
					(SELECT $label FROM `rms_view` WHERE `rms_view`.`type`=6 AND `rms_view`.`key_code`=scg.`status`)AS status
		 		FROM 
    				`rms_student_change_group` AS scg,
    				rms_group  AS g
    			WHERE 
    				scg.to_group=g.id ";
    	
    	$where=' ';
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("g.branch_id");
    	$order=" order by scg.id DESC";
    	if(empty($search)){
    		return $db->fetchAll($sql.$where.$order);
    	}
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " (SELECT stu_code FROM `rms_student` WHERE `rms_student`.`stu_id`=scg.`stu_id` limit 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT CONCAT(stu_khname,' - ',stu_enname) FROM `rms_student` WHERE `rms_student`.`stu_id`=scg.`stu_id` limit 1) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND g.branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=' AND g.academic_year='.$search['academic_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND g.degree='.$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.=' AND g.grade='.$search['grade'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND g.session='.$search['session'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('g.branch_id');
    	return $db->fetchAll($sql.$where.$order);
    }    
}