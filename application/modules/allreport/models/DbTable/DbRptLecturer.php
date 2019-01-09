<?php

class Allreport_Model_DbTable_DbRptLecturer extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_teacher';
    public function getAllLecturer($search){
    	$db = $this->getAdapter();
    	$sql = 'select teacher_code,CONCAT(teacher_name_en," - ",teacher_name_kh)AS name,tel,dob,address,email,nationality,
    			(select name_en from rms_view where rms_view.type=3 and rms_view.key_code=rms_teacher.degree limit 1)AS degree,note,
    			(select name_en from rms_view where rms_view.type=2 and rms_view.key_code=rms_teacher.sex limit 1)AS sex,
				id_card_no,pars_id from rms_teacher	';	
    	
    	$where=' where 1 ';
    	$order=" order by id DESC";
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " teacher_code LIKE '%{$s_search}%'";
    		$s_where[] = " CONCAT(teacher_name_en,teacher_name_kh) LIKE '%{$s_search}%'";
    		$s_where[] = " (select name_en from rms_view where rms_view.type=3 and rms_view.key_code=rms_teacher.degree limit 1) LIKE '%{$s_search}%'";
    		//     		$s_where[] = " en_name LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	return $db->fetchAll($sql.$where.$order);
    	 
    }

   public function getAllDegree(){
	   $_dbgb = new Application_Model_DbTable_DbGlobal();
	   return $_dbgb->$_dbgb->getAllItems(1);
   }
    public function getAllGrade(){
	   $_dbgb = new Application_Model_DbTable_DbGlobal();
	   return $_dbgb->getAllGradeStudy(1);
   }
   public function getAcademicyear(){
	   $dbglobal = new Application_Model_DbTable_DbGlobal();
	   $branch_para = "t.branch_id";
	   $branch = $dbglobal->getAccessPermission($branch_para);
	   $db = $this->getAdapter();
	   $sql="SELECT t.`id`,CONCAT(t.`from_academic`,' - ',t.`to_academic`,'(',generation,')') AS `name` FROM `rms_tuitionfee` AS t WHERE t.`status` = 1 ".$branch;
	  
	   return $db->fetchAll($sql);
   } 
       
}