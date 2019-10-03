<?php

class Allreport_Model_DbTable_DbPlacementest extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_placement_test';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllPlacementTest($search){
    	$db=$this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$_db=new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	$lang = $_db->currentlang();
    	$branch = "b.branch_namekh";
    	$stu_name ="s.stu_khname";
    	if($lang==2){// English
    		$branch = "b.branch_nameen";
    		$stu_name = " CONCAT(COALESCE(s.stu_enname,''),' ',COALESCE(s.last_name,'')) ";
    	}
    	$sql = "SELECT 
    		(SELECT $branch FROM `rms_branch` AS b  WHERE b.br_id = pt.branch_id LIMIT 1) AS branch_name,
    		s.stu_khname,
			s.stu_enname,
			s.last_name,
			CONCAT(COALESCE(s.stu_enname,''),' ',COALESCE(s.last_name,'')) AS stu_name_en,
			$stu_name AS stu_name,
			s.sex,
			CASE
			WHEN  s.sex = 1 THEN '".$tr->translate("MALE")."'
			WHEN  s.sex = 2 THEN '".$tr->translate("FEMALE")."'
			END AS sexTitle,
			ps.title,
			(SELECT t.title FROM `rms_test_type` AS t WHERE t.id = ps.test_type LIMIT 1) AS test_type_title,
			pt.* FROM `rms_placement_test` AS pt,
			`rms_placementtest_setting` AS ps,
			`rms_student` AS  s
			WHERE 
			ps.id = pt.placement_setting_id AND s.stu_id = pt.student_id ";
    	$where= ' ';
    	$order=" ORDER BY pt.id DESC ";
    	
    	$from_date =(empty($search['start_date']))? '1': " pt.create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " pt.create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    	$where.= " AND  ".$from_date." AND ".$to_date;
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " s.last_name LIKE '%{$s_search}%'";
    		
    		$s_where[] = " ps.title LIKE '%{$s_search}%'";
    		$s_where[] = " pt.duration LIKE '%{$s_search}%'";
    		$s_where[] = " pt.total_point LIKE '%{$s_search}%'";
    		$s_where[] = " pt.result_score LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT t.title FROM `rms_test_type` AS t WHERE t.id = ps.test_type LIMIT 1) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$where.= " AND pt.branch_id = ".$db->quote($search['branch_id']);
    	}
    	if(!empty($search['test_type'])){
    		$where.= " AND ps.test_type = ".$db->quote($search['test_type']);
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    
}
   
    
   