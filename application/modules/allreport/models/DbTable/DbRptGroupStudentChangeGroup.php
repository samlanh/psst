<?php

class Allreport_Model_DbTable_DbRptGroupStudentChangeGroup extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    	 
    }
   

    
//     function getAllSession(){
//     	$db=$this->getAdapter();
//     	$sql="select key_code,name_en from rms_view where type=4";
//     	return $db->fetchAll($sql);
//     }
    
    function getAllStu($search){
    	$db= $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$grade = "rms_itemsdetail.title";
    		$degree = "rms_items.title";
    		$branch = "b.branch_namekh";
    	}else{ // English
    		$label = "name_en";
    		$grade = "rms_itemsdetail.title_en";
    		$degree = "rms_items.title_en";
    		$branch = "b.branch_nameen";
    	}
    	$sql="SELECT 
				  gds.`stu_id`,
				  gscg.`from_group`,				  
				  st.stu_code,				 
				  st.stu_khname,
				  st.last_name,
				  st.stu_enname,
				  (SELECT $label FROM rms_view WHERE rms_view.`type`=2 AND rms_view.`key_code`=st.sex Limit 1) AS sex,
				  (SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = (SELECT rms_group.academic_year FROM rms_group WHERE rms_group.id=gscg.`from_group` LIMIT 1) LIMIT 1) AS academic_year,
				  (SELECT $grade from rms_itemsdetail WHERE `rms_itemsdetail`.`items_type`=1 AND rms_itemsdetail.id=(SELECT rms_group.grade FROM rms_group WHERE rms_group.id=gscg.`from_group`) LIMIT 1) AS grade,
				  (select $label from rms_view where rms_view.type=4 and key_code=(SELECT rms_group.session FROM rms_group WHERE rms_group.id=gscg.`from_group`) LIMIT 1) AS session,
				  (SELECT group_code from rms_group WHERE rms_group.id=gscg.from_group limit 1) AS from_group_code,
				  
				  gscg.`to_group` ,
				  (SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS to_academic_year,
				  (SELECT $grade from rms_itemsdetail WHERE `rms_itemsdetail`.`items_type`=1 AND rms_itemsdetail.id=g.grade limit 1) AS to_grade,
		
				  (select $label from rms_view where rms_view.type=4 and key_code=g.session Limit 1) AS to_session,
				  (select $label from rms_view where type=17 and key_code=gscg.change_type Limit 1) as change_type,
				  g.group_code as to_group_code
				FROM
				  `rms_group_detail_student` AS gds,
				  `rms_group_student_change_group` AS gscg,
				   rms_group as g,
				   rms_student as st
				WHERE 
					gds.itemType=1 
					AND gds.`group_id` = gscg.`to_group` 
					and gscg.change_type=1
				  	AND gds.`old_group` = gscg.`from_group`
				  	and gscg.to_group=g.id
				  	and gds.stu_id=st.stu_id   
    	";
    	
    	$order=" ORDER BY gscg.`id` ASC";
    	
    	$where  = '';
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("st.branch_id");
    	$where.=$dbp->getDegreePermission("gds.degree");
    	
   		 if(empty($search)){
    		return $db->fetchAll($sql.$where.$order);
    	}
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " (SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=g.grade AND rms_itemsdetail.items_type=1 LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (select name_en from rms_view where rms_view.type=4 and rms_view.key_code=g.session) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND st.branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=' AND g.academic_year='.$search['academic_year'];
    	}
    	if(!empty($search['grade_bac'])){
    		$where.=' AND g.grade='.$search['grade_bac'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND g.session='.$search['session'];
    	}
    	if(!empty($search['change_type'])){
    		$where.=' AND gscg.change_type='.$search['change_type'];
    	}
    	if(!empty($search['changegroup_id'])){
    		$where.=' AND gscg.id='.$search['changegroup_id'];
    	}
    	
    	$row = $db->fetchAll($sql.$where.$order);
    	if($row){
    		return $row;
    	}
    }
    
    
    public function getChangeType(){
    	$db=$this->getAdapter();
    	$sql="SELECT key_code as id, name_kh as name from rms_view where type=17 and status=1 ";
    	return $db->fetchAll($sql);
    }
    public function getAllChangeGroup($type){//1=ប្តូរក្រុម , 2=ឡើងថ្នាក់
    	$db=$this->getAdapter();
    	$sql="SELECT 
    				id, 
    				(select group_code from rms_group as g where g.id = from_group) as from_group,
    				(select group_code from rms_group as g where g.id = to_group) as to_group
    			from 
    				rms_group_student_change_group 
    			where 
    				change_type=$type
    				and status=1 
    		";
    	return $db->fetchAll($sql);
    }
    
}

