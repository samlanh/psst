<?php class Home_Model_DbTable_DbDashboard extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_student_test';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
    public function getSpecailDiscount($search){
    	$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$date=date("Y-m-d");
    	$sql="SELECT d.*,
			(SELECT so.dis_name FROM rms_discount AS so WHERE so.disco_id = d.dis_type LIMIT 1) AS discount_type_title,
			(SELECT name_kh FROM rms_view WHERE TYPE=11 AND key_code =d.status) AS STATUS,
			(SELECT CONCAT(first_name) FROM rms_users WHERE d.user_id=id LIMIT 1 ) AS user_name,
			CASE    
				WHEN  d.duration_type = 1 THEN '".$tr->translate("MONTHLY")."'
				WHEN  d.duration_type = 2 THEN '".$tr->translate("QUARTER")."'
				WHEN  d.duration_type = 3 THEN '".$tr->translate("SEMESTER")."'
				WHEN  d.duration_type = 4 THEN '".$tr->translate("YEAR")."'
				END AS duration_type_title,
			CASE    
				WHEN  d.status = 1 THEN '".$tr->translate("RELATIVE")."'
				WHEN  d.status = 2 THEN '".$tr->translate("FRIEND")."'
				WHEN  d.status = 3 THEN '".$tr->translate("BUSINESS_PARTNER")."'
				WHEN  d.status = 4 THEN '".$tr->translate("OTHER")."'
				END AS status_type
			FROM `rms_specail_discount` AS d WHERE 1
			AND d.expired_date >='$date'";
    	$orderby = " ORDER BY d.id DESC ";
    	$where="";
    	if(!empty($search['advance_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
    		$s_where[] = " d.request_name LIKE '%{$s_search}%'";
    		$s_where[] = " d.phone LIKE '%{$s_search}%'";
    		$s_where[] = " d.stu_name LIKE '%{$s_search}%'";
    		$sql .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['dis_type'])){
    		$where.= " AND d.dis_type  = ".$db->quote($search['dis_type']);
    	}
    	if(!empty($search['status_type'])){
    		$where.= " AND d.status = ".$db->quote($search['status_type']);
    	}
    	return $db->fetchAll($sql.$where.$orderby);
    }
    function countStudentDrop($droptype=null){
    	$db = $this->getAdapter();
    	$sql="SELECT COUNT(sd.id) FROM `rms_student_drop` AS sd
			WHERE sd.status =1
			";
    	if (!empty($droptype)){
    		$sql.=" AND sd.type=$droptype";
    	}
    	return $db->fetchOne($sql);
    }
    function getStudentDropNew(){
    	$db = $this->getAdapter();
    	
    	$dbgb = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbgb->currentlang();
    	$title="v.name_en";
    	if ($currentlang==1){
    		$title="v.name_kh";
    	}
    	$sql="SELECT d.*,
			(SELECT branch_nameen FROM `rms_branch` WHERE rms_branch.br_id = d.branch_id LIMIT 1) AS branch_name,
			(SELECT photo FROM `rms_branch` WHERE rms_branch.br_id = d.branch_id LIMIT 1) AS branch_photo,			
			s.stu_code,
			s.stu_khname,
			s.stu_enname,
			s.last_name,
			s.tel,
			s.sex,
			s.photo,
			(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=d.academic_year LIMIT 1) AS academic,
			(SELECT `title` FROM `rms_items` WHERE `id`=d.degree AND TYPE=1 LIMIT 1) AS degree,
			(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=d.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
			(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=d.group LIMIT 1 ) AS group_name,
			(SELECT $title AS `name` FROM rms_view AS v WHERE v.type=5 AND v.key_code=d.type LIMIT 1) AS type_drop
			FROM `rms_student_drop` AS d,
			`rms_student` AS s
			WHERE s.stu_id = d.stu_id and d.status=1 
			AND d.id NOT IN ((SELECT rr.notification_id FROM `rms_read_unread_notif` AS rr WHERE rr.notif_type=1))
			";
    	$sql.=$dbgb->getAccessPermission('d.branch_id');
    	$order =" ORDER BY d.id DESC";
    	return $db->fetchAll($sql.$order);
    }
    function getSettingDiscountNearlyExpire(){
    	$db = $this->getAdapter();
    	$date=date("Y-m-d",strtotime("+1 month"));
    	$dbgb = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbgb->currentlang();
    	$title="v.name_en";
    	if ($currentlang==1){
    		$title="v.name_kh";
    	}
    	$sql="SELECT 
			(SELECT branch_nameen FROM `rms_branch` WHERE br_id=g.branch_id)AS branch,
			(SELECT dis_name AS NAME FROM `rms_discount` WHERE disco_id=g.disname_id )AS disc_name,
			g.*,
			(SELECT  CONCAT(first_name) FROM rms_users WHERE id=g.user_id )AS user_name,
			(SELECT $title FROM rms_view as v WHERE v.type=1 AND v.key_code =g.status) AS `status` 
			FROM 
			rms_dis_setting AS g
			WHERE g.status=1
			AND g.end_date <='$date'";
    	$sql.=$dbgb->getAccessPermission('g.branch_id');
    	$order =" ORDER BY g.discount_id DESC";
    	return $db->fetchAll($sql.$order);
    }
}
