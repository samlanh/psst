<?php

class Allreport_Model_DbTable_DbRptLecturer extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_teacher';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('authstu');
//     	return $session_user->user_id;
    	 
//     }
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
    	
//     	if(empty($search)){
//     		return $db->fetchAll($sql.$order);
//     	}
    	
// //     	if(!empty($search['txtsearch'])){
// //     		$where.=" AND g.group_code LIKE '%".$search['txtsearch']."%'";
// //     	}

//     	$searchs = $search['txtsearch'];
//     	if($search['searchby']==0){
//     		$where.='';
//     	}
//     	if($search['searchby']==1){
//     		$where.= " AND teacher_code  LIKE  '%".$searchs."%'";
//     	}
//     	if($search['searchby']==2){
//     		$where.= " AND CONCAT(teacher_name_en,teacher_name_kh) LIKE '%".$searchs."%'" ;
//     	}
//     	if($search['searchby']==3){
//     		$where.= " AND (select name_en from rms_view where rms_view.type=3 and rms_view.key_code=rms_teacher.degree limit 1)  LIKE '%".$searchs."%'" ;
//     	}
    	
    	return $db->fetchAll($sql.$where.$order);
    	 
    }
	public function getAmountStudentByTeacher($search){
		$db = $this->getAdapter();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission('t.branch_id');
		
		$sql="
			  SELECT 
				  b.`branch_nameen`,
				  CONCAT(a.`from_academic`,' - ',a.`to_academic`) AS academic,
				  a.`generation`,
				  a.`time`,
				  d.`en_name` AS degree_name,
				  m.`major_enname` AS grade_name,
				  t.`teacher_name_en`,
				  s.`stu_type`,
				  (SELECT v.name_en FROM `rms_view` AS v WHERE v.key_code = t.`sex` AND v.type = 2) AS `gender`,
				  (SELECT v.name_en FROM `rms_view` AS v WHERE v.key_code = s.`session` AND v.type = 4) AS `session`,
				  (SELECT v.name_en FROM `rms_view` AS v WHERE v.key_code = s.`sex` AND v.type = 2) AS `stu_gender`,
				  s.`stu_id`,
				  s.`stu_enname`,
				  s.`stu_khname`,
				  s.`stu_code`,
				  s.`teacher_id` 
				FROM
				  `rms_branch` AS b,
				  `rms_tuitionfee` AS a,
				  `rms_dept` AS d,
				  `rms_major` AS m,
				  `rms_teacher` AS t,
				  `rms_student` AS s 
				WHERE s.`branch_id` = b.`br_id` 
				  AND s.`academic_year` = a.`id` 
				  AND s.`degree` = d.`dept_id` 
				  AND s.`grade` = m.`major_id` 
				  AND d.`dept_id` = m.`dept_id` 
				  AND s.`teacher_id` = t.`id` 
				  AND s.`branch_id` = t.`branch_id`
				  $branch_id 
			";
			$where ='';
			if($search['degree']>-1){
				$where .=' AND s.`degree`='.$search['degree'];
			}
			if($search['grade']>-1){
				$where .=' AND s.`grade` ='.$search['grade'];
			}
			if($search['academic']>-1){
				$where .=' AND s.`academic_year` ='.$search['academic'];
			}
			if(!empty($search['branch_id'])){
				$where .=' AND s.`branch_id` ='.$search['branch_id'];
			}
			if(!empty($search['session'])){
				$where .=' AND s.`session` ='.$search['session'];
			}
			if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " d.`en_name` LIKE '%{$s_search}%'";
    		$s_where[] = " t.`teacher_name_en` LIKE '%{$s_search}%'";
    		$s_where[] = "  s.`stu_enname` LIKE '%{$s_search}%'";
			$s_where[] = "  s.`stu_code` LIKE '%{$s_search}%'";
			$s_where[] = "  a.`generation` LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
	$order =" ORDER BY 	s.`stu_code`,d.`en_name`,m.`major_enname` ASC";
		return $db->fetchAll($sql.$where);
	}
   public function getAllDegree(){
	   $db = $this->getAdapter();
	   $sql="SELECT d.`dept_id` AS id, d.`en_name` AS `name` FROM `rms_dept` AS d WHERE d.`is_active`=1";
	   return $db->fetchAll($sql);
   }
    public function getAllGrade(){
	   $db = $this->getAdapter();
	   $sql="SELECT m.`major_id` AS id, m.`major_enname` AS `name` FROM `rms_major` AS m WHERE m.`is_active` =1";
	   return $db->fetchAll($sql);
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