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
    				stu_id,
    				(SELECT CONCAT(stu_khname,' - ',last_name,' ',stu_enname) FROM `rms_student` WHERE `rms_student`.`stu_id`=`rms_student_change_group`.`stu_id` limit 1) AS name,
			    	(SELECT stu_code FROM `rms_student` WHERE `rms_student`.`stu_id`=`rms_student_change_group`.`stu_id` limit 1) AS stu_code,
					(SELECT $label FROM `rms_view` WHERE `rms_view`.`type`=2 and `rms_view`.`key_code`=(SELECT sex FROM `rms_student` WHERE `rms_student`.`stu_id`=`rms_student_change_group`.`stu_id` limit 1) limit 1)AS sex,
					(SELECT group_code from rms_group where rms_group.id=rms_student_change_group.from_group limit 1) AS code,
					(SELECT CONCAT(from_academic,'-',to_academic,' (',generation,')') from rms_tuitionfee where rms_tuitionfee.id=(select academic_year from rms_group where rms_group.id=rms_student_change_group.from_group LIMIT 1) limit 1) as academic,
					(SELECT semester from rms_group where rms_group.id=rms_student_change_group.from_group limit 1 ) AS semester,
					(SELECT $label from rms_view where rms_view.type=4 and rms_view.key_code=(select session from rms_group where rms_student_change_group.from_group=rms_group.id LIMIT 1) limit 1) AS session,
					(SELECT $grade from rms_itemsdetail WHERE `rms_itemsdetail`.`items_type`=1 AND rms_itemsdetail.id=(select grade from rms_group where rms_student_change_group.from_group=rms_group.id LIMIT 1) limit 1) AS grade,
					(SELECT room_name from rms_room where rms_room.room_id=(select room_id from rms_group where rms_student_change_group.from_group=rms_group.id) limit 1) AS room_name,
					(SELECT start_date from rms_group where rms_group.id=rms_student_change_group.from_group limit 1) AS start_date,
					
					(SELECT expired_date from rms_group where rms_group.id=rms_student_change_group.from_group limit 1) AS expired_date,
					(SELECT group_code from rms_group where rms_group.id=rms_student_change_group.to_group limit 1) AS to_code,
					(SELECT CONCAT(from_academic,'-',to_academic,' (',generation,')') from rms_tuitionfee where rms_tuitionfee.id=(select academic_year from rms_group where rms_group.id=rms_student_change_group.to_group LIMIT 1) LIMIT 1) as to_academic,
					(SELECT semester from rms_group where rms_group.id=rms_student_change_group.to_group limit 1) AS to_semester,
					(SELECT $label from rms_view where rms_view.type=4 and rms_view.key_code=(select session from rms_group where rms_student_change_group.to_group=rms_group.id LIMIT 1) limit 1) AS to_session,
					(SELECT $grade from rms_itemsdetail WHERE `rms_itemsdetail`.`items_type`=1 AND rms_itemsdetail.id=(select grade from rms_group where rms_student_change_group.to_group=rms_group.id LIMIT 1) limit 1) AS to_grade,
					(SELECT room_name from rms_room where rms_room.room_id=(select room_id from rms_group where rms_student_change_group.to_group=rms_group.id LIMIT 1) limit 1) AS to_room_name,
				 	 moving_date,
				 	 rms_student_change_group.note,
					(SELECT $label from `rms_view` where `rms_view`.`type`=6 and `rms_view`.`key_code`=`rms_student_change_group`.`status`)AS status
		 		FROM 
    				`rms_student_change_group`,
    				rms_group 
    			WHERE 
    				rms_student_change_group.to_group=rms_group.id ";
    	
    	$where=' ';
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("rms_group.branch_id");
    	$order=" order by rms_student_change_group.id DESC";
    	if(empty($search)){
    		return $db->fetchAll($sql.$where.$order);
    	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " (SELECT stu_code FROM `rms_student` WHERE `rms_student`.`stu_id`=`rms_student_change_group`.`stu_id` limit 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT CONCAT(stu_khname,' - ',stu_enname) FROM `rms_student` WHERE `rms_student`.`stu_id`=`rms_student_change_group`.`stu_id` limit 1) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND rms_group.academic_year='.$search['study_year'];
    	}
    	if(!empty($search['grade_bac'])){
    		$where.=' AND rms_group.grade='.$search['grade_bac'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND rms_group.session='.$search['session'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND rms_group.branch_id='.$search['branch_id'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('rms_group.branch_id');
    	return $db->fetchAll($sql.$where.$order);
    }    
}