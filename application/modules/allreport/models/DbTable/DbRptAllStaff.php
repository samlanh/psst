<?php

class Allreport_Model_DbTable_DbRptAllStaff extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
//     	return $session_user->user_id;
    	 
//     }
    function getAllTeacherCard($search){
    	$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbg->currentlang();
    	$label="name_en";
    	if($currentlang==1){
    		$label="name_kh";
    	}
    	$sql = "SELECT 
	    			g.*,
			    	(SELECT CONCAT(branch_nameen) FROM rms_branch WHERE br_id=branch_id LIMIT 1) AS branch_name,
			    	CASE
			    	WHEN  g.sex = 1 THEN '".$tr->translate("MALE")."'
			    	WHEN g.sex = 2 THEN '".$tr->translate("FEMALE")."'
			    	END AS sex,
			    	CASE
			    	WHEN  g.staff_type = 1 THEN '".$tr->translate("TEACHER")."'
			    	WHEN g.staff_type = 2 THEN '".$tr->translate("STAFF")."'
			    	END AS staff_type_title,
			    	(SELECT $label FROM rms_view WHERE rms_view.type=24 AND rms_view.key_code=g.teacher_type) AS teacher_type,
			    	(SELECT $label FROM rms_view WHERE rms_view.type=3 AND rms_view.key_code=g.degree) AS degree,
			    	(SELECT $label FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=g.nationality) AS nationality,
			    	(SELECT depart_nameen FROM rms_department WHERE rms_department.depart_id=g.department) AS dept_name
    			FROM 
    				rms_teacher AS g 
    			WHERE 
    				1
    		";
    
    	$where='';
    	if(!empty($search['title'])){
    	$s_where = array();
    	$s_search = addslashes(trim($search['title']));
    	$s_where[] = " (SELECT CONCAT(branch_nameen) FROM rms_branch WHERE br_id=branch_id LIMIT 1) LIKE '%{$s_search}%'";
    			$s_where[] = " teacher_code LIKE '%{$s_search}%'";
    			$s_where[] = " teacher_name_en LIKE '%{$s_search}%'";
    			$s_where[] = " teacher_name_kh LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND degree='.$search['degree'];
    	}
    	if($search['teacher_type']>-1){
			$where.=' AND teacher_type='.$search['teacher_type'];
		}
    	if(!empty($search['nationality'])){
    		$where.=' AND nationality='.$search['nationality'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['staff_type'])){
    		$where.=' AND staff_type='.$search['staff_type'];
    	}
    	$order_by=" ORDER BY id DESC ";
    
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.= $dbp->getAccessPermission('g.branch_id');
    	//echo $sql.$where.$order_by;//exit();
    	return $db->fetchAll($sql.$where.$order_by);
    }
	function getAllTeacher($search){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$currentlang = $dbg->currentlang();
		$label="name_en";
		if($currentlang==1){
			$label="name_kh";
		}
		$sql = "
			SELECT g.*, 
				(SELECT CONCAT(branch_nameen) FROM rms_branch WHERE br_id=branch_id LIMIT 1) AS branch_name,
				CASE    
				WHEN  g.sex = 1 THEN '".$tr->translate("MALE")."'
				WHEN g.sex = 2 THEN '".$tr->translate("FEMALE")."'
				END AS sex,
				CASE    
				WHEN  g.staff_type = 1 THEN '".$tr->translate("TEACHER")."'
				WHEN g.staff_type = 2 THEN '".$tr->translate("STAFF")."'
				END AS staff_type_title,
				(SELECT $label FROM rms_view WHERE rms_view.type=24 AND rms_view.key_code=g.teacher_type) AS teacher_type,
				(SELECT $label FROM rms_view WHERE rms_view.type=3 AND rms_view.key_code=g.degree) AS degree,
				(SELECT $label FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=g.nationality) AS nationality, 
				(SELECT depart_nameen FROM rms_department WHERE rms_department.depart_id=g.department) AS dept_name,
				CASE
				   	WHEN  g.active_type = 1 THEN '".$tr->translate("STOP")."'
				   	WHEN  g.active_type = 0 THEN '".$tr->translate("ACTIVING")."'
			   	END AS active_type 
				FROM rms_teacher AS g WHERE 1
		";
		
		$where='';
		if(!empty($search['title'])){
		    $s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " (SELECT CONCAT(branch_nameen) FROM rms_branch WHERE br_id=branch_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " teacher_code LIKE '%{$s_search}%'";
			$s_where[] = " teacher_name_en LIKE '%{$s_search}%'";
			$s_where[] = " teacher_name_kh LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['degree'])){
			$where.=' AND degree='.$search['degree'];
		}
		if(!empty($search['department'])){
			$where.=' AND department='.$search['department'];
		}
		if($search['teacher_type']>-1){
			$where.=' AND teacher_type='.$search['teacher_type'];
		}
		if(!empty($search['nationality'])){
			$where.=' AND nationality='.$search['nationality'];
		}
		if(!empty($search['branch_id'])){
			$where.=' AND branch_id='.$search['branch_id'];
		}
		if(!empty($search['staff_type'])){
			$where.=' AND staff_type='.$search['staff_type'];
		}
		if($search['active_type']>-1){
			$where.=' AND active_type='.$search['active_type'];
		}
		$order_by=" ORDER BY id DESC";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.= $dbp->getAccessPermission('g.branch_id');		
		
		return $db->fetchAll($sql.$where.$order_by);
	}
    
    public function getAllStaffSelected($staff_id){
   		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$currentlang = $dbg->currentlang();
		$label="name_en";
		if($currentlang==1){
			$label="name_kh";
		}
		$sql = "
			SELECT g.*, 
				(SELECT CONCAT(branch_nameen) FROM rms_branch WHERE br_id=branch_id LIMIT 1) AS branch_name,
				CASE    
				WHEN  g.sex = 1 THEN '".$tr->translate("MALE")."'
				WHEN g.sex = 2 THEN '".$tr->translate("FEMALE")."'
				END AS sex,
				CASE    
				WHEN  g.staff_type = 1 THEN '".$tr->translate("TEACHER")."'
				WHEN g.staff_type = 2 THEN '".$tr->translate("STAFF")."'
				END AS staff_type_title,
				(SELECT $label FROM rms_view WHERE rms_view.type=24 AND rms_view.key_code=g.teacher_type) AS teacher_type,
				(SELECT $label FROM rms_view WHERE rms_view.type=3 AND rms_view.key_code=g.degree) AS degree,
				(SELECT $label FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=g.nationality) AS nationality, 
				(SELECT depart_nameen FROM rms_department WHERE rms_department.depart_id=g.department) AS dept_name
				FROM rms_teacher AS g WHERE status = 1 and id = $staff_id
		";
    	return $db->fetchAll($sql);
    }
    public function getAllStaffSelectedGroupBy($staff_id){
    	$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbg->currentlang();
    	$label="name_en";
    	if($currentlang==1){
    		$label="name_kh";
    	}
    	$sql = "
    	SELECT g.*,
    	(SELECT CONCAT(branch_nameen) FROM rms_branch WHERE br_id=branch_id LIMIT 1) AS branch_name,
    	CASE
    	WHEN  g.sex = 1 THEN '".$tr->translate("MALE")."'
    	WHEN g.sex = 2 THEN '".$tr->translate("FEMALE")."'
    	END AS sex,
    	CASE
    	WHEN  g.staff_type = 1 THEN '".$tr->translate("TEACHER")."'
    	WHEN g.staff_type = 2 THEN '".$tr->translate("STAFF")."'
    	END AS staff_type_title,
    	(SELECT $label FROM rms_view WHERE rms_view.type=24 AND rms_view.key_code=g.teacher_type) AS teacher_type,
    	(SELECT $label FROM rms_view WHERE rms_view.type=3 AND rms_view.key_code=g.degree) AS degree,
    	(SELECT $label FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=g.nationality) AS nationality,
    	(SELECT depart_nameen FROM rms_department WHERE rms_department.depart_id=g.department) AS dept_name
    	FROM rms_teacher AS g WHERE status = 1 and id = $staff_id GROUP BY staff_type
    	";
    	return $db->fetchAll($sql);
    }
    function getTeachDocumentAlert($search){
    	$db = $this->getAdapter();
    	$sql =" SELECT 
    				t.branch_id,
			    	(SELECT CONCAT(b.branch_nameen) FROM rms_branch AS b WHERE b.br_id=t.branch_id LIMIT 1) AS branch_name,
			    	(SELECT name_kh FROM rms_view WHERE rms_view.type=2 AND rms_view.key_code=t.sex LIMIT 1) AS sex,
			    	(SELECT name_kh FROM rms_view WHERE rms_view.type=24 AND rms_view.key_code=t.teacher_type LIMIT 1) AS teacher_type,
			    	(SELECT name_kh FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=t.nationality LIMIT 1) AS nationality,
			    	(SELECT name_kh FROM rms_view WHERE rms_view.type=3 AND rms_view.key_code=t.degree LIMIT 1) AS degree,
			    	t.teacher_code,
			    	t.teacher_name_kh,
			    	t.tel,
			    	t.email,
			    	sd.*
    			FROM 
    				`rms_teacher_document` AS sd,
    				`rms_teacher` AS t
    			WHERE 
    				t.id = sd.stu_id
    				AND sd.is_receive=0 ";
    	$where ='';
    	$to_date = (empty($search['end_date']))? '1': " sd.date_end <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$to_date;
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("t.branch_id");
    
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " teacher_code LIKE '%{$s_search}%'";
    		$s_where[] = " teacher_name_en LIKE '%{$s_search}%'";
    		$s_where[] = " teacher_name_kh LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND degree='.$search['degree'];
    	}
    	if(!empty($search['department'])){
    		$where.=' AND department='.$search['department'];
    	}
    	if($search['teacher_type']>-1){
    		$where.=' AND teacher_type='.$search['teacher_type'];
    	}
    	if(!empty($search['staff_type'])){
    		$where.=' AND staff_type='.$search['staff_type'];
    	}
    	if(!empty($search['nationality'])){
    		$where.=' AND nationality='.$search['nationality'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND branch_id='.$search['branch_id'];
    	}
    	$order=" ORDER BY sd.date_end DESC, sd.stu_id ASC";
    	return $db->fetchAll($sql.$where.$order);
    }
}



