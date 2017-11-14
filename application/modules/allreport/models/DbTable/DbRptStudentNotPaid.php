<?php

class Allreport_Model_DbTable_DbRptStudentNotPaid extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('authstu');
//     	return $session_user->user_id;
    	 
//     }
    function getAllStudentNotPaid($search){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('s.branch_id');
    	$sql="SELECT 
				  s.stu_id,
				  CASE WHEN stu_khname IS NULL THEN stu_enname ELSE stu_khname END AS name,
				  s.`stu_code`,
				  s.`stu_khname`,
				  s.`stu_enname`,
				  (SELECT en_name FROM rms_dept WHERE dept_id = g.`degree`) AS degree,
				  (SELECT major_enname FROM rms_major WHERE major_id = g.grade ) AS grade,
				  (SELECT name_en FROM rms_view WHERE `type` = 4 AND key_code = g.`session`) AS `session`
				FROM
				  rms_student AS s,
				  `rms_group_detail_student` AS gds,
				  `rms_group` AS g
				WHERE 
				  gds.`stu_id`=s.stu_id
				  AND g.id = gds.`group_id`
				  AND gds.`is_pass`=0
				  AND gds.`type`=1
				  AND s.`stu_id` NOT IN (SELECT student_id FROM rms_student_payment AS sp,`rms_student_paymentdetail` AS spd WHERE sp.id=spd.`payment_id` AND spd.`service_id`=4)   
    			  $branch_id
    		";
    	
     	$order=" ORDER by s.stu_khname ASC ";
     	
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
    		
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    		
    	//echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
    
}
   
    
   