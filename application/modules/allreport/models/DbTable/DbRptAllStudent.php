<?php

class Allreport_Model_DbTable_DbRptAllStudent extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student';
    public function getAllStudent($search){//for card list
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$grade = "rms_itemsdetail.title";
    		$degree = "rms_items.title";
    		$branch = "branch_namekh";
    		
    		$village_name = "village_namekh";
    		$commune_name = "commune_namekh";
    		$district_name = "district_namekh";
    		$province = "province_kh_name";
    	}else{ // English
    		$label = "name_en";
    		$grade = "rms_itemsdetail.title_en";
    		$degree = "rms_items.title_en";
    		$branch = "branch_nameen";
    		
    		$village_name = "village_name";
    		$commune_name = "commune_name";
    		$district_name = "district_name";
    		$province = "province_en_name";
    	}
    	$sql ="SELECT 
    				*,
	    	      (SELECT $branch FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
	    	       CONCAT(stu_khname,' - ',stu_enname,' ',last_name) as name,
	    	       stu_khname,
	    	       last_name,
	    	       stu_enname,
	    	      (SELECT $label FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality,
	    		  (SELECT $label FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation,
	    		   degree as dept,
	    		   (SELECT g.group_code FROM `rms_group` AS g WHERE g.id=s.group_id LIMIT 1 ) AS group_name,
	    		   (SELECT CONCAT(from_academic,'-',to_academic) from rms_tuitionfee where rms_tuitionfee.id=s.academic_year LIMIT 1) as academic_year,
	    		   (SELECT from_academic from rms_tuitionfee where rms_tuitionfee.id=s.academic_year LIMIT 1) as start_year,
	    		   (SELECT to_academic from rms_tuitionfee where rms_tuitionfee.id=s.academic_year LIMIT 1) as end_year,
	    		   (SELECT end_date from rms_tuitionfee where rms_tuitionfee.id=s.academic_year LIMIT 1) as end_date,
	    		   (SELECT $label from rms_view where rms_view.type=4 and rms_view.key_code=s.session LIMIT 1)AS session,
	    		   
	    		   (SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=s.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
				   (SELECT $degree FROM rms_items WHERE rms_items.id=s.degree AND rms_items.type=1 LIMIT 1) AS degree,
				   (SELECT rms_items.schoolOption FROM rms_items WHERE rms_items.id=s.degree AND rms_items.type=1 LIMIT 1) AS schoolOption,
			
	    		   (SELECT $label from rms_view where type=5 and key_code=is_subspend LIMIT 1) as status,
	    		   (SELECT v.$village_name FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name,
			    	(SELECT c.$commune_name FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_name,
			    	(SELECT d.$district_name FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_name,
	    		   (SELECT $province from rms_province where rms_province.province_id = s.province_id LIMIT 1)AS province,
	    		   	   	
	    		   (SELECT $label from rms_view where rms_view.type=2 and rms_view.key_code=s.sex LIMIT 1) AS sex,
	    		   photo,
	    		   (SELECT occu_name FROM rms_occupation WHERE occupation_id=s.father_job LIMIT 1) fath_job,
				 (SELECT occu_name FROM rms_occupation WHERE occupation_id=s.mother_job LIMIT 1) moth_job,
				 (SELECT occu_name FROM rms_occupation WHERE occupation_id=s.guardian_job LIMIT 1) guard_job
    		   	FROM 
    				rms_student as s
    			WHERE 
    				status=1 
    				AND customer_type=1 
    		";
    	
    	$where=' ';

    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	$from_date =(empty($search['start_date']))? '1': "s.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "s.create_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;    	
    	$order=" ORDER BY s.stu_id,s.degree,s.grade,s.academic_year DESC";
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[]=" stu_code LIKE '%{$s_search}%'";
    		$s_where[]=" stu_khname LIKE '%{$s_search}%'";
    		$s_where[]=" stu_enname LIKE '%{$s_search}%'";
    		$s_where[]=" tel LIKE '%{$s_search}%'";
    		$s_where[]=" father_phone LIKE '%{$s_search}%'";
    		$s_where[]=" mother_phone LIKE '%{$s_search}%'";
    		$s_where[]=" guardian_tel LIKE '%{$s_search}%'";
    		$s_where[]=" father_enname LIKE '%{$s_search}%'";
    		$s_where[]=" mother_enname LIKE '%{$s_search}%'";
    		$s_where[]=" guardian_enname LIKE '%{$s_search}%'";
    		$s_where[]=" remark LIKE '%{$s_search}%'";
    		$s_where[]=" home_num LIKE '%{$s_search}%'";
    		$s_where[]=" street_num LIKE '%{$s_search}%'";
    		$s_where[]=" village_name LIKE '%{$s_search}%'";
    		$s_where[]=" commune_name LIKE '%{$s_search}%'";
    		$s_where[]=" district_name LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND academic_year='.$search['study_year'];
    	}
    	if(!empty($search['group'])){
    		$where.=' AND group_id='.$search['group'];
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND degree='.$search['degree'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=' AND grade='.$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND session='.$search['session'];
    	}
    	if(!empty($search['stu_type']) AND $search['stu_type']>-1){
    		$where.=' AND is_stu_new = '.$search['stu_type'];
    	}
//     	$dbp = new Application_Model_DbTable_DbGlobal();
//     	$where.=$dbp->getAccessPermission();
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getAllStudentpro($search){
    	$db = $this->getAdapter();
    	$sql ='SELECT *,
    	(SELECT branch_namekh FROM `rms_branch` WHERE br_id=rms_student.branch_id LIMIT 1) AS branch_name,
    	CONCAT(stu_khname) as name_kh,
    	CONCAT(stu_enname," ",last_name) as name_en,
    	(SELECT name_en FROM rms_view where type=21 and key_code=nationality LIMIT 1) AS nationality,
    	(SELECT name_en FROM rms_view where type=21 and key_code=nation LIMIT 1) AS nation,
    	 
    	tel,email,stu_code,home_num,street_num,tel,
    	(SELECT occu_name FROM rms_occupation WHERE occupation_id=father_job LIMIT 1) AS fa_job,
		(SELECT occu_name FROM rms_occupation WHERE occupation_id=mother_job LIMIT 1) AS mo_job,
		(SELECT occu_name FROM rms_occupation WHERE occupation_id=guardian_job LIMIT 1) AS gu_job,
    	is_subspend,
    	degree as dept,
    	(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=rms_student.group_id LIMIT 1 ) AS group_name,
    	(SELECT CONCAT(from_academic,"-",to_academic) from rms_tuitionfee where rms_tuitionfee.id=academic_year LIMIT 1) as academic_year,
    	(SELECT from_academic from rms_tuitionfee where rms_tuitionfee.id=academic_year LIMIT 1) as start_year,
    	(SELECT to_academic from rms_tuitionfee where rms_tuitionfee.id=academic_year LIMIT 1) as end_year,
    	(SELECT end_date from rms_tuitionfee where rms_tuitionfee.id=academic_year LIMIT 1) as end_date,
    	(SELECT name_en from rms_view where rms_view.type=4 and rms_view.key_code=rms_student.session LIMIT 1)AS session,
    		
    	(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=rms_student.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
    	(SELECT rms_items.title FROM rms_items WHERE rms_items.id=rms_student.degree AND rms_items.type=1 LIMIT 1) AS degree,
    	(SELECT rms_items.schoolOption FROM rms_items WHERE rms_items.id=rms_student.degree AND rms_items.type=1 LIMIT 1) AS schoolOption,
    	(SELECT room_name FROM `rms_room` AS r WHERE r.room_id = room LIMIT 1) AS room,
    	(SELECT name_kh from rms_view where type=5 and key_code=is_subspend LIMIT 1) as status,
    	(SELECT v.village_name FROM `ln_village` AS v WHERE v.vill_id = rms_student.village_name LIMIT 1) AS village_name,
    	(SELECT c.commune_name FROM `ln_commune` AS c WHERE c.com_id = rms_student.commune_name LIMIT 1) AS commune_name,
    	(SELECT d.district_name FROM `ln_district` AS d WHERE d.dis_id = rms_student.district_name LIMIT 1) AS district_name,
    	(SELECT province_en_name from rms_province where rms_province.province_id = rms_student.province_id LIMIT 1)AS province,
    	(SELECT v.village_namekh FROM `ln_village` AS v WHERE v.vill_id = rms_student.village_name LIMIT 1) AS village_namekh,
    	(SELECT c.commune_namekh FROM `ln_commune` AS c WHERE c.com_id = rms_student.commune_name LIMIT 1) AS commune_namekh,
    	(SELECT d.district_namekh FROM `ln_district` AS d WHERE d.dis_id = rms_student.district_name LIMIT 1) AS district_namekh,
    	(SELECT rms_province.province_kh_name from rms_province where rms_province.province_id = rms_student.province_id LIMIT 1)AS province_kh_name
    	FROM rms_student';
    	$where=' WHERE status=1 AND customer_type=1 AND is_subspend=0';
    
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	$from_date =(empty($search['start_date']))? '1': "rms_student.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "rms_student.create_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	$order=" ORDER BY stu_id,degree,grade,academic_year DESC";
    	//     	if(empty($search)){
    	//     		return $db->fetchAll($sql.$order);
    	//     	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[]=" stu_khname LIKE '%{$s_search}%'";
    		$s_where[]=" stu_enname LIKE '%{$s_search}%'";
    		$s_where[]=" tel LIKE '%{$s_search}%'";
    		$s_where[]=" father_phone LIKE '%{$s_search}%'";
    		$s_where[]=" mother_phone LIKE '%{$s_search}%'";
    		$s_where[]=" guardian_tel LIKE '%{$s_search}%'";
    		$s_where[]=" father_enname LIKE '%{$s_search}%'";
    		$s_where[]=" mother_enname LIKE '%{$s_search}%'";
    		$s_where[]=" guardian_enname LIKE '%{$s_search}%'";
    		$s_where[]=" remark LIKE '%{$s_search}%'";
    		$s_where[]=" home_num LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND academic_year='.$search['study_year'];
    	}
    	if(!empty($search['group'])){
    		$where.=' AND group_id='.$search['group'];
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND degree='.$search['degree'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=' AND grade='.$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND session='.$search['session'];
    	}
//     	$dbp = new Application_Model_DbTable_DbGlobal();
//     	$where.=$dbp->getAccessPermission();
    	return $db->fetchAll($sql.$where.$order);
    }
	public function getAllStudentGroupbyBranchAndSchoolOption($search){
    	$db = $this->getAdapter();
    	$sql ='SELECT branch_id,    		   
		(SELECT rms_items.title FROM rms_items WHERE rms_items.id=rms_student.degree AND rms_items.type=1 LIMIT 1) AS degree,
		(SELECT rms_items.schoolOption FROM rms_items WHERE rms_items.id=rms_student.degree AND rms_items.type=1 LIMIT 1) AS schoolOption
		FROM rms_student ';
    	$where=' WHERE status=1 AND customer_type=1 ';
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	   	
    	$order=" GROUP BY branch_id,(SELECT rms_items.schoolOption FROM rms_items WHERE rms_items.id=rms_student.degree AND rms_items.type=1 LIMIT 1)  
		ORDER BY stu_id,degree,grade,academic_year DESC";
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	if(empty($search)){
    		return $db->fetchAll($sql.$where.$order);
    	}
    	$from_date =(empty($search['start_date']))? '1': "rms_student.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "rms_student.create_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[]=" stu_code LIKE '%{$s_search}%'";
    		$s_where[]=" stu_khname LIKE '%{$s_search}%'";
    		$s_where[]=" stu_enname LIKE '%{$s_search}%'";
    		$s_where[]=" tel LIKE '%{$s_search}%'";
    		$s_where[]=" father_phone LIKE '%{$s_search}%'";
    		$s_where[]=" mother_phone LIKE '%{$s_search}%'";
    		$s_where[]=" guardian_tel LIKE '%{$s_search}%'";
    		$s_where[]=" father_enname LIKE '%{$s_search}%'";
    		$s_where[]=" mother_enname LIKE '%{$s_search}%'";
    		$s_where[]=" guardian_enname LIKE '%{$s_search}%'";
    		$s_where[]=" remark LIKE '%{$s_search}%'";
    		$s_where[]=" home_num LIKE '%{$s_search}%'";
    		$s_where[]=" street_num LIKE '%{$s_search}%'";
    		$s_where[]=" village_name LIKE '%{$s_search}%'";
    		$s_where[]=" commune_name LIKE '%{$s_search}%'";
    		$s_where[]=" district_name LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND academic_year='.$search['study_year'];
    	}
    	if(!empty($search['group'])){
    		$where.=' AND group_id='.$search['group'];
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND degree='.$search['degree'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=' AND grade='.$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND session='.$search['session'];
    	}
    	if(!empty($search['stu_type'])){
    		$where.=' AND is_stu_new = '.$search['stu_type'];
    	}
    	
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getAmountStudent($year=null){//count to dashboard
    	$db = $this->getAdapter();
    	$sql ='SELECT COUNT(stu_id) FROM rms_student ';
    	$where=' WHERE status=1 AND customer_type=1 AND is_subspend=0';
    	if (!empty($year)){
    		$where.=" AND DATE_FORMAT(create_date, '%Y') <='$year'";
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	return $db->fetchOne($sql.$where);
    }
    public function getAmountNewStudent(){//count to dashboard
    	$db = $this->getAdapter();
    	$sql ='SELECT COUNT(stu_id) FROM rms_student';
    	$where=' WHERE status=1 AND is_stu_new=1 AND customer_type=1 AND is_subspend=0';
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	return $db->fetchOne($sql.$where);
    }   
    public function getAmountDropStudent(){//count to dashboard
    	$db = $this->getAdapter();
    	$sql ='SELECT COUNT(stu_id) FROM rms_student ';
    	$where=' WHERE status=1 AND is_subspend!=0 AND customer_type=1';
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	return $db->fetchOne($sql.$where);
    }
    public function getAmountStudentTest(){//count to dashboard
    	$db = $this->getAdapter();
    	$sql ='SELECT COUNT(stu_id) FROM `rms_student` ';
    	$where=" WHERE status=1 AND (stu_khname!='' OR stu_enname!='') AND customer_type=4 ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	return $db->fetchOne($sql.$where);
    }
    public function getAmountStudentTestRegistered(){//count to dashboard
    	$db = $this->getAdapter();
    	$sql ="SELECT COUNT(str.id)
			FROM `rms_student` AS st,
			`rms_student_test_result` AS str
			WHERE str.is_registered=1
			AND st.is_studenttest =1
			AND str.stu_test_id = st.stu_id
			AND
			st.status=1
		";
    	$where=" AND (st.stu_khname!='' OR st.stu_enname!='')";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	return $db->fetchOne($sql.$where);
    } 
    public function getAmountStudentUpdateresult(){//count to dashboard
    	$db = $this->getAdapter();
    	$sql ="SELECT COUNT(str.id)
			FROM `rms_student` AS st,
			`rms_student_test_result` AS str
			WHERE str.updated_result=1
			AND st.is_studenttest =1
			AND str.stu_test_id = st.stu_id
			AND
			st.status=1
		";
    	$where=" AND (st.stu_khname!='' OR st.stu_enname!='')";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	return $db->fetchOne($sql.$where);
    }
    
    public function getAllStudentSelectedBG($stu_id){
    	$db = $this->getAdapter();
    	
    	$sql ='SELECT 
    				branch_id,
    	(SELECT rms_items.title FROM rms_items WHERE rms_items.id=rms_student.degree AND rms_items.type=1 LIMIT 1) AS degree,
    	(SELECT rms_items.schoolOption FROM rms_items WHERE rms_items.id=rms_student.degree AND rms_items.type=1 LIMIT 1) AS schoolOption
    			from 
    				rms_student 
    			where 
    				stu_id ='.$stu_id;
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('rms_student.group_id');
    	$sql.= $dbp->getSchoolOptionAccess('(SELECT i.schoolOption FROM `rms_items` AS i WHERE i.type=1 AND i.id = rms_student.degree )');
    	return $db->fetchAll($sql);
    }
    public function getAllStudentSelected($stu_id){
    	$db = $this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$grade = "rms_itemsdetail.title";
    		$degree = "rms_items.title";
    		$branch = "branch_namekh";
    	
    		$village_name = "village_namekh";
    		$commune_name = "commune_namekh";
    		$district_name = "district_namekh";
    		$province = "province_kh_name";
    	}else{ // English
    		$label = "name_en";
    		$grade = "rms_itemsdetail.title_en";
    		$degree = "rms_items.title_en";
    		$branch = "branch_nameen";
    	
    		$village_name = "village_name";
    		$commune_name = "commune_name";
    		$district_name = "district_name";
    		$province = "province_en_name";
    	}
    	
    	$sql ="SELECT
    	*,
    	(SELECT branch_namekh FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
    	CONCAT(stu_khname,' - ',stu_enname,' ',last_name) as name,
    	stu_khname,last_name,stu_enname as en_name,
    	CONCAT(stu_enname,' ',last_name) AS stu_enname,
    	(SELECT name_kh FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality,
    	(SELECT name_kh FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation,
    	(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=s.group_id LIMIT 1 ) AS group_name,
    	degree as dept,
    	(SELECT CONCAT(from_academic,'-',to_academic) from rms_tuitionfee where rms_tuitionfee.id=s.academic_year limit 1) as academic_year,
    	(SELECT from_academic from rms_tuitionfee where rms_tuitionfee.id=s.academic_year limit 1) as start_year,
    	(SELECT to_academic from rms_tuitionfee where rms_tuitionfee.id=s.academic_year limit 1) as end_year,
    	(SELECT end_date from rms_tuitionfee where rms_tuitionfee.id=s.academic_year limit 1) as end_date,
    	(SELECT name_en from rms_view where rms_view.type=4 and rms_view.key_code=s.session limit 1) AS session,
    	(SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=s.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
    	(SELECT $degree FROM rms_items WHERE rms_items.id=s.degree AND rms_items.type=1 LIMIT 1) AS degree,
    	(SELECT name_en from rms_view where type=5 and key_code=s.is_subspend LIMIT 1) as status,
    	(SELECT v.$village_name FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name,
    	(SELECT c.$commune_name FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_name,
    	(SELECT d.$district_name FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_name,
    	(SELECT $province from rms_province where rms_province.province_id = s.province_id limit 1)AS province,
    	(SELECT $label from rms_view where rms_view.type=2 and rms_view.key_code=s.sex limit 1)AS sex,
    	(SELECT rms_items.schoolOption FROM rms_items WHERE rms_items.id=s.degree AND rms_items.type=1 LIMIT 1) AS schoolOption,
    	 photo,
    		   (SELECT occu_name FROM rms_occupation WHERE occupation_id=s.father_job LIMIT 1) fath_job,
			 (SELECT occu_name FROM rms_occupation WHERE occupation_id=s.mother_job LIMIT 1) moth_job,
			 (SELECT occu_name FROM rms_occupation WHERE occupation_id=s.guardian_job LIMIT 1) guard_job
    	from
    	rms_student as s
    	where
    	stu_id =".$stu_id;
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('s.group_id');
    	$sql.= $dbp->getSchoolOptionAccess('(SELECT i.schoolOption FROM `rms_items` AS i WHERE i.type=1 AND i.id = s.degree )');
    	return $db->fetchAll($sql);
    }
    public function getAllAmountStudent($search){
    	$db = $this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$grade = "rms_itemsdetail.title";
    		$degree = "rms_items.title";
    	}else{ // English
    		$label = "name_en";
    		$grade = "rms_itemsdetail.title_en";
    		$degree = "rms_items.title_en";
    	}
    	
    	$sql ="SELECT stu_id,
			    	(SELECT branch_namekh FROM `rms_branch` WHERE br_id=rms_student.branch_id LIMIT 1) AS branch_name,
			    	CONCAT(last_name,' ',stu_enname) AS name,
			    	stu_khname,
			    	is_stu_new,
			    	rms_student.sex as sex_key,
			    	(SELECT $label FROM rms_view where type=21 and key_code=nationality LIMIT 1) AS nationality,
	       			(SELECT $label FROM rms_view where type=21 and key_code=nation LIMIT 1) AS nation,
			    	tel,email,stu_code,home_num,street_num,dob,
			    	is_subspend,
			    	(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') from rms_tuitionfee where rms_tuitionfee.id=academic_year LIMIT 1) as academic_year,
			    	(SELECT $label from rms_view where rms_view.type=4 and rms_view.key_code=rms_student.session limit 1)AS session,
			    	(SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=rms_student.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
					(SELECT $degree FROM rms_items WHERE rms_items.id=rms_student.degree AND rms_items.type=1 LIMIT 1) AS degree,
					rms_student.degree as degree_id,
			    	(SELECT $label from rms_view where type=5 and key_code=is_subspend LIMIT 1) as status,    
			    	(SELECT v.village_name FROM `ln_village` AS v WHERE v.vill_id = rms_student.village_name LIMIT 1) AS village_name,
			    	(SELECT c.commune_name FROM `ln_commune` AS c WHERE c.com_id = rms_student.commune_name LIMIT 1) AS commune_name,
			    	(SELECT d.district_name FROM `ln_district` AS d WHERE d.dis_id = rms_student.district_name LIMIT 1) AS district_name,
			    	(SELECT province_en_name from rms_province where rms_province.province_id = rms_student.province_id limit 1)AS province,
			    	(SELECT $label from rms_view where rms_view.type=2 and rms_view.key_code=rms_student.sex limit 1)AS sex,
			    	(SELECT room_name FROM `rms_room` AS r WHERE r.room_id = room LIMIT 1) AS room
	    		FROM 
    				rms_student 
    			WHERE 
    				status=1 
    				AND customer_type=1
    		";
    	
    	$where=' ';
    	 
    	$from_date =(empty($search['start_date']))? '1': "rms_student.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "rms_student.create_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	$where.= $dbp->getSchoolOptionAccess('(SELECT i.schoolOption FROM `rms_items` AS i WHERE i.type=1 AND i.id = rms_student.degree )');
    	
    	$order="  order by academic_year DESC,degree ASC,grade DESC,session ASC,stu_khname ASC ";
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " last_name LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND academic_year='.$search['study_year'];
    	}
    	if(!empty($search['group'])){
    		$where.=' AND rms_student.group_id='.$search['group'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=' AND grade='.$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND session='.$search['session'];
    	}if($search['degree']>0){
    		$where.=' AND degree='.$search['degree'];
    	}
    	if(($search['branch_id'])>0){
    		$where.=' AND branch_id='.$search['branch_id'];
    	}
    	if($search['grade_all']>0){
    		$where.=' AND grade='.$search['grade_all'];
    	}
    	if($search['study_type']!=''){
    		if($search['study_type']==0){
    			$where.=' AND is_subspend='.$search['study_type'];
    		}else{
    			$where.=' AND is_subspend!=0';
    		}
    	}
//     	$dbp = new Application_Model_DbTable_DbGlobal();
//     	$where.=$dbp->getAccessPermission();
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getAllStudentgep($search){
    	$db = $this->getAdapter();
    	$to_date = (empty($search['end_date']))? '1': " spd.validate >= '".$search['end_date']." 23:59:59'";
    	$sql ="SELECT stu_id,
    	(SELECT branch_namekh FROM `rms_branch` WHERE br_id=rms_student.branch_id LIMIT 1) AS branch_name,
    	CONCAT(stu_enname) AS name,
    	stu_khname,
    	is_stu_new,
    	remark,
    	(SELECT name_en FROM rms_view where type=21 and key_code=nationality LIMIT 1) AS nationality,
       (SELECT name_en FROM rms_view where type=21 and key_code=nation LIMIT 1) AS nation,
    	tel,email,stu_code,home_num,street_num,
    	(SELECT v.village_name FROM `ln_village` AS v WHERE v.vill_id = rms_student.village_name LIMIT 1) AS village_name,
    	(SELECT c.commune_name FROM `ln_commune` AS c WHERE c.com_id = rms_student.commune_name LIMIT 1) AS commune_name,
    	(SELECT d.district_name FROM `ln_district` AS d WHERE d.dis_id = rms_student.district_name LIMIT 1) AS district_name,
    	is_subspend,
    	(select CONCAT(from_academic,'-',to_academic,'(',generation,')') from rms_tuitionfee where rms_tuitionfee.id=academic_year) as academic_year,
    	(select name_en from rms_view where rms_view.type=4 and rms_view.key_code=rms_student.session limit 1)AS session,
    	
    	(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=rms_student.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
		(SELECT rms_items.title FROM rms_items WHERE rms_items.id=rms_student.degree AND rms_items.type=1 LIMIT 1) AS degree,
				
    	(select name_en from rms_view where type=5 and key_code=is_subspend) as status,
    	(select province_en_name from rms_province where rms_province.province_id = rms_student.province_id limit 1)AS province,
    	(select name_en from rms_view where rms_view.type=2 and rms_view.key_code=rms_student.sex limit 1)AS sex,
    	(SELECT r.room_name FROM `rms_room` AS r where r.room_id=rms_student.room LIMIT 1 ) AS room_name,
    	(SELECT sp.`create_date` FROM  `rms_student_paymentdetail` AS spd,`rms_student_payment` AS sp
    		WHERE 
    		 rms_student.stu_id = sp.student_id
    		AND sp.id=spd.`payment_id` 
    		AND sp.is_void!=1
    		AND spd.`is_start` = 1 
    		AND spd.service_id=4
    		AND $to_date ORDER BY spd.`validate` DESC LIMIT 1) AS paid_date
    	
    	FROM rms_student ";
    	$where=' WHERE status=1 AND degree IN(5,6,7,8) ';
    
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	 
    	$order="  order by academic_year DESC,degree ASC,grade DESC,session ASC,stu_id DESC";
    	if(empty($search)){
    		return $db->fetchAll($sql.$where.$order);
    	}
    	//     	$from_date =(empty($search['start_date']))? '1': "rms_student.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "rms_student.create_date <= '".$search['end_date']." 23:59:59'";
    	$where .=" AND ".$to_date;
    	
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " CONCAT(stu_enname,stu_khname) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT name_en from rms_view where rms_view.type=4 and rms_view.key_code=rms_student.session limit 1) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND academic_year='.$search['study_year'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=' AND grade='.$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND session='.$search['session'];
    	}if($search['degree']>0){
    		$where.=' AND degree='.$search['degree'];
    	}
    	if(($search['branch_id'])>0){
    		$where.=' AND branch_id='.$search['branch_id'];
    	}
    	if($search['grade_all']>0){
    		$where.=' AND grade='.$search['grade_all'];
    	}
    	if($search['study_type']!=''){
    		if($search['study_type']==0){
    			$where.=' AND is_subspend='.$search['study_type'];
    		}else{
    			$where.=' AND is_subspend!=0';
    		}
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getStudentStatistic($search){
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
    	$sql ="SELECT 
					 s.stu_id,
					(SELECT $branch FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name,	
					(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=s.academic_year LIMIT 1) AS academic_year_name,
					(SELECT $label FROM rms_view WHERE rms_view.type=4 AND rms_view.key_code=s.session LIMIT 1) AS `session_name`,
					(SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=s.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade_name,
					(SELECT $degree FROM rms_items WHERE rms_items.id=s.degree AND rms_items.type=1 LIMIT 1) AS degree_name,
					s.academic_year,
					s.degree,
					s.grade,
					s.session,
					s.is_stu_new,
					s.`is_subspend`,
					(SELECT $label FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality,
    				(SELECT $label FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation
				FROM 
					rms_student AS s 
				WHERE 
				 	s.status=1 AND s.customer_type=1 ";
    	$group_by = " ";
		$order_by = " ORDER BY s.branch_id DESC,s.`academic_year` ASC,s.`degree` ASC,s.`grade` ASC,s.`session` ASC";	
    	$where=' ';
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	if(!empty($search['study_year'])){
    		$where.=' AND s.academic_year='.$search['study_year'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=' AND s.grade='.$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND s.session='.$search['session'];
    	}if(!empty($search['degree'])){
    		$where.=' AND s.degree='.$search['degree'];
    	}
    	if(($search['branch_id'])>0){
    		$where.=' AND s.branch_id='.$search['branch_id'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("s.branch_id");
    	
    	return $db->fetchAll($sql.$where.$group_by.$order_by);
    }
    public function getAllStudyHistory($search){
    	$db = $this->getAdapter();
	    	$sql = 'SELECT 
					  h.`stu_id`,s.`stu_code`,is_subspend,
					  CONCAT(s.`stu_enname`," ",s.`last_name`) AS stu_enname,
					  s.`stu_khname`,s.`sex`,s.`group_id`,
						(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = s.`group_id` LIMIT 1) AS group_code,
					  h.is_finished,h.finished_date,
					  (SELECT branch_nameen FROM `rms_branch` WHERE br_id=h.branch_id LIMIT 1) AS branch_name,
					  (SELECT CONCAT(from_academic,"-",to_academic,"(",generation,")") FROM rms_tuitionfee WHERE rms_tuitionfee.id=h.academic_year LIMIT 1) AS academic_year,
					  (SELECT name_en FROM rms_view WHERE rms_view.type=4 AND rms_view.key_code=h.session LIMIT 1)AS session,
					  
					  (SELECT name_en FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality,
    				  (SELECT name_en FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation,
					  (SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=h.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
					  (SELECT rms_items.title FROM rms_items WHERE rms_items.id=h.degree AND rms_items.type=1 LIMIT 1) AS degree,
					  
					  (SELECT teacher_name_en FROM rms_teacher AS t WHERE t.id = h.teacher_id ) AS teacher
					FROM
					  `rms_student` AS s,
					  `rms_study_history` AS h 
					WHERE s.`stu_id` = h.`stu_id` 
	    		   ';
	    	$where="";
	    	
	    	$dbp = new Application_Model_DbTable_DbGlobal();
	    	$where.=$dbp->getAccessPermission('h.branch_id');
	    	
	    	$order="  order by s.`stu_id`,h.degree,h.grade,h.academic_year DESC";
	    	if(empty($search)){
	    		return $db->fetchAll($sql.$where.$order);
	    	}
	    	$from_date =(empty($search['start_date']))? '1': "h.create_date >= '".$search['start_date']." 00:00:00'";
	    	$to_date = (empty($search['end_date']))? '1': "h.create_date <= '".$search['end_date']." 23:59:59'";
	    	$where .= " AND ".$from_date." AND ".$to_date;
	    	
	    	if(!empty($search['title'])){
	    		$s_where = array();
	    		$s_search = addslashes(trim($search['title']));
	    		$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
	    		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
	    		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
	    		$s_where[] = " (SELECT g.group_code FROM `rms_group` AS g WHERE g.id = s.`group_id` LIMIT 1) LIKE '%{$s_search}%'";
	    		$s_where[] = " CONCAT(stu_enname,stu_enname) LIKE '%{$s_search}%'";
	    		$where .=' AND ( '.implode(' OR ',$s_where).')';
	    	}
	    	if(!empty($search['study_year'])){
	    		$where.=' AND h.academic_year='.$search['study_year'];
	    	}
	    	if(!empty($search['degree'])){
	    		$where.=' AND h.degree='.$search['degree'];
	    	}
	    	if(!empty($search['branch_id'])){
	    		$where.=' AND h.branch_id='.$search['branch_id'];
	    	}
	    	if(!empty($search['grade_all'])){
	    		$where.=' AND h.grade='.$search['grade_all'];
	    	}
	    	if(!empty($search['session'])){
	    		$where.=' AND h.session='.$search['session'];
	    	}
	    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getAllStudentID(){
    	$db = $this->getAdapter();
    	$sql="select stu_id as id,stu_code from rms_student ";
    	return $db->fetchAll($sql);
    }
    function getAllStudentName(){
    	$db = $this->getAdapter();
    	$sql="select stu_id as id,stu_enname as name from rms_student ";
    	return $db->fetchAll($sql);
    }
    public function getAllStudentDetail($search){
    	$db = $this->getAdapter();
    	$sql ='SELECT stu_id,CONCAT(stu_khname," - ",stu_enname," ",last_name)AS name,
    	(SELECT branch_nameen FROM `rms_branch` WHERE br_id=rms_student.branch_id LIMIT 1) AS branch_name,
    	(SELECT name_en FROM rms_view where type=21 and key_code=nationality LIMIT 1) AS nationality,
    	(SELECT name_en FROM rms_view where type=21 and key_code=nation LIMIT 1) AS nation,
    	tel,email,stu_code,home_num,street_num,
    	CONCAT(father_enname," - ",father_khname)AS father_name,father_nation,father_phone,
    	CONCAT(mother_enname," - ",mother_khname)AS mother_name,mother_nation,mother_phone,
    	CONCAT(guardian_enname," - ",guardian_khname)AS guardian_name,guardian_nation,guardian_document,guardian_tel,guardian_email,
    	(SELECT name_en from rms_view where type=5 and key_code=is_subspend LIMIT 1) as status,
    	(SELECT occu_enname FROM rms_occupation where rms_occupation.occupation_id=rms_student.father_job LIMIT 1)AS father_job,
    	(SELECT occu_enname FROM rms_occupation where rms_occupation.occupation_id=rms_student.mother_job LIMIT 1)AS mother_job,
    	(SELECT occu_enname FROM rms_occupation where rms_occupation.occupation_id=rms_student.guardian_job LIMIT 1)AS guardian_job,
    	(SELECT name_en from rms_view where rms_view.type=4 and rms_view.key_code=rms_student.session LIMIT 1)AS session,
    	(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=rms_student.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
		(SELECT rms_items.title FROM rms_items WHERE rms_items.id=rms_student.degree AND rms_items.type=1 LIMIT 1) AS degree,
		(SELECT v.village_name FROM `ln_village` AS v WHERE v.vill_id = rms_student.village_name LIMIT 1) AS village_name,
    	(SELECT c.commune_name FROM `ln_commune` AS c WHERE c.com_id = rms_student.commune_name LIMIT 1) AS commune_name,
    	(SELECT d.district_name FROM `ln_district` AS d WHERE d.dis_id = rms_student.district_name LIMIT 1) AS district_name,
    	(SELECT province_en_name from rms_province where rms_province.province_id = rms_student.province_id LIMIT 1)AS province,
    	(SELECT name_en FROM rms_view where rms_view.type=2 and rms_view.key_code=rms_student.sex LIMIT 1)AS sex
    	FROM rms_student ';
    	$where=' WHERE customer_type=1 ';    	 
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("branch_id");
    	$where.= $dbp->getSchoolOptionAccess('(SELECT i.schoolOption FROM `rms_items` AS i WHERE i.type=1 AND i.id = rms_student.degree )');
    	$order=" order by stu_id DESC";
    	 
    	if(empty($search)){
    		return $db->fetchAll($sql.$where.$order);
    	}
    	$from_date =(empty($search['start_date']))? '1': "rms_student.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "rms_student.create_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " CONCAT(stu_enname,stu_khname) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT rms_items.title FROM rms_items WHERE rms_items.id=rms_student.degree AND rms_items.type=1 LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=rms_student.grade AND rms_itemsdetail.items_type=1 LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (select name_en from rms_view where rms_view.type=4 and rms_view.key_code=rms_student.session limit 1) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND academic_year='.$search['study_year'];
    	}
    	if(!empty($search['grade'])){
    		$where.=' AND grade='.$search['grade_bac'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND session='.$search['session'];
    	}
    	if(!empty($search['group'])){
    		$where.=' AND group_id='.$search['group'];
    	}
    	
    	
    	return $db->fetchAll($sql.$where.$order);
    }
   function getStudentAttendance($search){
    	$db = $this->getAdapter();
    	$sql="SELECT 
		    	 	(SELECT b.branch_namekh FROM `rms_branch` AS b WHERE b.br_id=s.`branch_id` LIMIT 1) AS branch_name,
			    	s.`stu_id`,
			    	s.`stu_code`,
			    	s.`stu_khname`,
			    	s.`stu_enname`,
			    	s.`sex`,
			    	(SELECT sgh.group_id FROM `rms_group_detail_student` AS sgh WHERE sgh.stu_id = s.`stu_id` ORDER BY sgh.gd_id DESC LIMIT 1) as group_id,
			    	(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = (SELECT sgh.group_id FROM `rms_group_detail_student` AS sgh WHERE sgh.stu_id = s.`stu_id` ORDER BY sgh.gd_id DESC LIMIT 1) LIMIT 1) AS group_code, 
			    	(SELECT rms_items.title FROM rms_items WHERE rms_items.id=s.`degree` AND rms_items.type=1 LIMIT 1) AS degree,
			    	s.`degree` as degree_id,
					(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=s.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
					
					(SELECT `r`.`room_name`	FROM `rms_room` `r` WHERE `r`.`room_id` = s.`room`LIMIT 1) AS room,
			    	s.`group` 
			    FROM 
			    	`rms_student` AS s 
			    WHERE 
			    	s.`status`=1 
		    ";
    	
    	$where='';
    	
 		if(!empty($search['group'])){
    		$where.= " AND (SELECT sgh.group_id FROM `rms_student_group_history` AS sgh WHERE sgh.stu_id = s.`stu_id` ORDER BY sgh.id DESC LIMIT 1) =".$search['group'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND s.academic_year =".$search['study_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND s.`degree`=".$search['degree'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=" AND s.`grade`=".$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND s.session=".$search['session'];
    	}
    	$order=" ORDER BY degree_id,s.`group`,s.`stu_id` DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
    function CountAttendence($stu_id,$att_status,$fromdate,$todate,$group_id){
    	$db = $this->getAdapter();
    	$sql="SELECT COUNT(sad.`id`) AS count_att FROM `rms_student_attendence_detail` AS sad WHERE sad.`stu_id` =$stu_id AND sad.`attendence_status`=$att_status AND (SELECT sa.status FROM `rms_student_attendence` AS sa WHERE sa.id = sad.`attendence_id` LIMIT 1)=1 
    	AND (SELECT sa.group_id FROM `rms_student_attendence` AS sa WHERE sa.id = sad.`attendence_id` LIMIT 1)=$group_id
    	";
    	$from_date =" (SELECT sa.date_attendence FROM `rms_student_attendence` AS sa WHERE sa.id = sad.`attendence_id` LIMIT 1) >=  '".date("Y-m-d",strtotime($fromdate))." 00:00:00'";
    	$to_date = "(SELECT sa.date_attendence FROM `rms_student_attendence` AS sa WHERE sa.id = sad.`attendence_id` LIMIT 1)  <= '".date("Y-m-d",strtotime($todate))." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	return $db->fetchOne($sql.$where);
    }
    
    function getStudentAttendence($search){
    	$db = $this->getAdapter();
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
    	$sql=" SELECT 
					g.id as group_id,
					g.`group_code`,
					g.`branch_id`,
					(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year,
					(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
					(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
		
					(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
					`g`.`semester` AS `semester`,
					(SELECT`rms_view`.$label FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
					gsd.`stu_id`,
					st.`stu_code`,st.`stu_enname`,st.`stu_khname`,st.`last_name`,st.`sex`
				FROM 
					`rms_group_detail_student` AS gsd,
					`rms_group` AS g,
					`rms_student` AS st,
					rms_student_attendence AS sta
				WHERE 
					sta.type=1
					AND gsd.status=1
					AND gsd.type=1
					AND sta.type=1
	    			AND g.`id` = gsd.`group_id`
				 	AND sta.group_id = g.id 
				 	AND st.`stu_id` = gsd.`stu_id` 
				 	AND sta.status=1 
				 	AND g.is_pass!=1 
				 	AND st.customer_type=1
    		";
    	
    	$from_date =(empty($search['start_date']))? '1': "sta.date_attendence >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "sta.date_attendence <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;

    	if(!empty($search['group'])){
    		$where.= " AND g.id =".$search['group'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND g.academic_year =".$search['study_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND `g`.`degree` =".$search['degree'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=" AND `g`.`grade`=".$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session`=".$search['session'];
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('`g`.`branch_id`');
    	
    	$order =" GROUP BY sta.group_id,gsd.stu_id 
    		ORDER BY `g`.`degree`,`g`.`grade`,g.group_code ASC ,g.id DESC,st.stu_khname ASC ";
    	
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getStatusAttendence($stu_id,$date_att,$group,$subject=null){
    	$db = $this->getAdapter();
    	$sql='SELECT
		sat.`group_id`,satd.`attendence_status`,sat.`date_attendence`
		FROM `rms_student_attendence` AS sat,
		`rms_student_attendence_detail` AS satd 
		WHERE sat.`id`= satd.`attendence_id`
		AND sat.type=1
		AND satd.`stu_id`='.$stu_id.' AND sat.`date_attendence`="'.$date_att.'" AND sat.`group_id`='.$group;
    	$where='';
    	if (!empty($subject)){ // high school student
    		$where.=" AND sat.`subject_id`=".$subject;
    	}
		return $db->fetchRow($sql.$where.' LIMIT 1');
    }
    function checkDateAttendence($date_att,$group,$subject=null){
    	$db = $this->getAdapter();
    	$sql="SELECT sat.`id` FROM `rms_student_attendence` AS sat 
			WHERE sat.`date_attendence`='$date_att' AND sat.`group_id`=$group";
    	$where='';
    	if (!empty($subject)){// high school student
    		$where.=" AND sat.`subject_id`=".$subject;
    	}
//     	echo $sql.$where.' LIMIT 1';
    	return $db->fetchRow($sql.$where.' LIMIT 1');
    }
    
    
	function getStudentMistake($search){
    	$db = $this->getAdapter();
    	$sql="SELECT 
					g.id as group_id,
					g.`group_code`,
					(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation LIMIT 1) AS academic_year, 
					(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
					(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 LIMIT 1) AS grade,
					(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`, 
					`g`.`semester` AS `semester`,
					(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
					 sdd.`stu_id`, st.`stu_code`, 
					 st.`stu_enname`,
					 st.last_name,
					 st.`stu_khname`,
					 st.`sex` 
				FROM 
					 `rms_group` AS g, `rms_student` AS st, 
					  rms_student_attendence AS sd, 
					 `rms_student_attendence_detail` AS sdd 
				WHERE 
					 (sd.type=2 OR sdd.`attendence_status` IN (4,5)) 
					 AND sd.`id` = sdd.`attendence_id` 
					 AND sd.group_id = g.id AND sd.status=1 
					 AND st.`stu_id` = sdd.`stu_id` ";
    	
    	$from_date =(empty($search['start_date']))? '1': "sd.`date_attendence` >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "sd.`date_attendence` <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " st.stu_khname LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if(!empty($search['group'])){
    		$where.= " AND g.id =".$search['group'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND `g`.`branch_id`=".$search['branch_id'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND g.academic_year =".$search['study_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND `g`.`degree` =".$search['degree'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=" AND `g`.`grade`=".$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session`=".$search['session'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("g.branch_id");
    	
    	$order =" GROUP BY g.id,sdd.`stu_id` ORDER BY `g`.`degree`,`g`.`grade`,g.group_code ASC ,g.id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function checkDateMistake($mistake_date,$group,$subject=null){
    	$db = $this->getAdapter();
    	$sql="SELECT 
    				sd.`id` 
    			FROM 
    				`rms_student_discipline` AS sd
    			WHERE 
    				sd.`mistake_date`= $mistake_date  
    				AND sd.`group_id`=$group
    		";
    	$where='';
    	    	echo $sql.$where.' LIMIT 1';//exit();
    	return $db->fetchRow($sql.$where.' LIMIT 1');
    }
    
    function getStatusMistake($stu_id,$date_att,$group){//old
    	$db = $this->getAdapter();
//     	$sql="SELECT
// 			    	sd.`group_id`,
// 			    	sdd.`mistake_type`,
// 			    	sdd.description,
// 			    	sd.`mistake_date`
// 			    FROM 
// 			    	`rms_student_discipline` AS sd,
// 			    	`rms_student_discipline_detail` AS sdd
// 			    WHERE 
// 			    	sd.`id` = sdd.`discipline_id`
// 			    	AND sdd.`stu_id` = $stu_id 
//     				AND sd.`mistake_date` = '".$date_att."' 
//     				AND sd.`group_id` = $group
//     		";
    	$sql="SELECT
	    	sd.`group_id`,
	    	sd.`type`,
	    	sdd.`attendence_status` as mistake_type,
	    	sdd.description,
	    	sd.`date_attendence` as mistake_date
	    	FROM
	    	`rms_student_attendence` AS sd,
	    	`rms_student_attendence_detail` AS sdd
	    	WHERE
	    	(sd.type=2 OR sdd.`attendence_status` IN (4,5))
	    	AND sd.`id` = sdd.`attendence_id`
	    	AND sdd.`stu_id` = $stu_id
	    	AND sd.`date_attendence` = '".$date_att."'
	    	AND sd.`group_id` = $group
    	";
    	
    	$where='';
    	return $db->fetchRow($sql.$where.' LIMIT 1');
    }
    
    function getStatusMistakeByStudent($stu_id,$group,$start_date,$end_date){
    	$db = $this->getAdapter();
    	$sql="SELECT
    	sd.`group_id`,
    	sd.`type`,
    	sdd.`attendence_status` as mistake_type,
    	sdd.description,
    	sd.`date_attendence` as mistake_date,
    	sd.for_session
    	FROM
    	`rms_student_attendence` AS sd,
    	`rms_student_attendence_detail` AS sdd
    	WHERE
    	(sd.type=2 OR sdd.`attendence_status` IN (4,5))
    	AND sd.`id` = sdd.`attendence_id`
    	AND sdd.`stu_id` = $stu_id
    	AND sd.`group_id` = $group ";
    	$from_date =(empty($start_date))? '1': " sd.`date_attendence` >= '".$start_date." 00:00:00'";
    	$to_date = (empty($end_date))? '1': " sd.`date_attendence` <= '".$end_date." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	return $db->fetchAll($sql.$where);
    }
    function getTotalStatusMistake($stu_id,$date_att,$group){
    	$db = $this->getAdapter();
// old    	$sql="SELECT
// 			    	sd.`group_id`,
// 			    	sdd.`mistake_type`,
// 			    	sdd.description,
// 			    	sd.`mistake_date`
// 			    FROM 
// 			    	`rms_student_discipline` AS sd,
// 			    	`rms_student_discipline_detail` AS sdd
// 			    WHERE 
// 			    	sd.`id` = sdd.`discipline_id`
// 			    	AND sdd.`stu_id` = $stu_id 
//     				AND sd.`mistake_date` = '".$date_att."' 
//     				AND sd.`group_id` = $group
//     		";
    	
//     	$where='';
// //     	echo $sql.$where.' LIMIT 1';//exit();
//     	return $db->fetchRow($sql.$where.' LIMIT 1');
// 		$sql="SELECT
// 			    	sd.`group_id`,
// 			    	sdd.`mistake_type`,
// 			    	sdd.description,
// 			    	sd.`mistake_date`,
// 			    	sdd.`stu_id`,
// 			    	COUNT(sdd.`mistake_type`) AS count_mistack			    	
// 			    FROM 
// 			    	`rms_student_discipline` AS sd,
// 			    	`rms_student_discipline_detail` AS sdd
// 			    WHERE 
// 			    	sd.`id` = sdd.`discipline_id`
// 			    	AND sdd.`stu_id` = $stu_id 
//     				AND sd.`group_id` = $group
//     			GROUP BY mistake_type
// 			";
    	$sql="SELECT
	    	sd.`group_id`,
	    	sdd.`attendence_status` as mistake_type,
	    	sdd.description,
	    	sd.`date_attendence` as mistake_date,
	    	sdd.`stu_id`,
	    	COUNT(sdd.`attendence_status`) AS count_mistack
	    	FROM
	    	`rms_student_attendence` AS sd,
	    	`rms_student_attendence_detail` AS sdd
	    	WHERE
	    	sd.`type` =2
	    	AND sd.`id` = sdd.`attendence_id`
	    	AND sdd.`stu_id` = $stu_id
	    	AND sd.`group_id` = $group
	    	GROUP BY attendence_status
    	";
		return $db->fetchAll($sql);
    }
    function getAttendenceFoul($group_id,$stu_id){//Ã¡Å¾â‚¬Ã¡Å¸â€ Ã¡Å¾Â Ã¡Å¾Â»Ã¡Å¾Å¸ Ã¡Å¾ËœÃ¡Å¾â‚¬Ã¡Å¾â„¢Ã¡Å¾ÂºÃ¡Å¾ï¿½ Ã¡Å¾â€œÃ¡Å¾Â·Ã¡Å¾â€žÃ¡Å¾â€¦Ã¡Å¸ï¿½Ã¡Å¾â€°Ã¡Å¾ËœÃ¡Å¾Â»Ã¡Å¾â€œ
    	$db = $this->getAdapter();
    	$sql="SELECT sade.*,sta.`date_attendence`,sta.`group_id`,COUNT(sade.`attendence_status`) AS count_foul_att
    	FROM rms_student_attendence_detail AS sade,
    	`rms_student_attendence` AS sta
    	WHERE sta.`id` = sade.`attendence_id`
    	AND sade.`stu_id`=$stu_id AND sta.`group_id`=$group_id AND sade.`attendence_status` IN (4,5) LIMIT 1
			";
    	$where="";
    	return $db->fetchRow($sql.$where);
    }
    function getStudentAttendenceHighschool($search){
    	$db = $this->getAdapter();
    	$sql="
	    	SELECT
	    	g.id AS group_id,
	    	g.`group_code`,
	    	gsj.`subject_id`,
			(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = gsj.`subject_id` LIMIT 1) AS subject_name,
	    	(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year,
	    	(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
	    	(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
		
	    	(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
	    	`g`.`semester` AS `semester`,
	    	(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
	    	AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
	    	gsd.`stu_id`,
	    	st.`stu_code`,st.`stu_enname`,st.`stu_khname`,st.`sex`
	    	FROM `rms_group_detail_student` AS gsd,
	    	`rms_group` AS g,
	    	`rms_student` AS st,`rms_group_subject_detail` AS gsj
	    	WHERE g.`id` = gsd.`group_id` AND st.`stu_id` = gsd.`stu_id`
			AND gsj.`group_id` = g.`id`    	
	    	 AND `g`.`degree` =2
    	";
    	//     	$from_date =" (SELECT sa.date_attendence FROM `rms_student_attendence` AS sa WHERE sa.id = sad.`attendence_id` LIMIT 1) >=  '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    	//     	$to_date = "(SELECT sa.date_attendence FROM `rms_student_attendence` AS sa WHERE sa.id = sad.`attendence_id` LIMIT 1)  <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    	$where='';
    	//     	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['group'])){
    		$where.= " AND g.id =".$search['group'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND g.academic_year =".$search['study_year'];
    	}
    	if(!empty($search['grade_highschool'])){
    		$where.=" AND `g`.`grade`=".$search['grade_highschool'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session`=".$search['session'];
    	}
    	$order ="  ORDER BY `g`.`degree`,g.id,gsj.`subject_id` DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getGroupHighschoolSearch(){
    	$db=$this->getAdapter();
    	$sql="SELECT g.`id` as id,g.`group_code` AS `name` 
    	FROM `rms_group` AS g WHERE g.`status`=1 AND g.`degree`=2";
    	return $db->fetchAll($sql);
    }
    function getSubjectByGroup($group_id){
    	$db=$this->getAdapter();
    	$sql="SELECT 	
		gsjd.`subject_id` AS id,	
		(SELECT CONCAT(sj.subject_titleen,'-',sj.subject_titlekh) FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS `name` 
		FROM rms_group_subject_detail AS gsjd WHERE gsjd.group_id = ".$group_id;
    	$rs = $db->fetchAll($sql);
    	return $rs;
    }
    
    function getStuDocumentNotEnough($search){
    	$db = $this->getAdapter();
    	$to_date = $search['end_date'];
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$lang = $dbp->currentlang();
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
    	$sql ="SELECT 
    				s.branch_id,
			    	(SELECT $branch FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_name,
			    	(SELECT b.photo FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_logo,
			    	s.stu_code,
			    	s.stu_khname,
			    	s.stu_enname,
			    	s.last_name,
			    	s.photo,
			    	s.sex,
			    	s.tel,
			    	(SELECT $label FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality,
       				(SELECT $label FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation,
			    	(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=s.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) AS academic_year,
			    	(SELECT $degree FROM `rms_items` WHERE (`rms_items`.`id`=`s`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
			    	(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`s`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
			    	sd.*
    			FROM 
    				`rms_student_document` AS sd,
    				`rms_student` AS s
    			WHERE 
    				s.stu_id = sd.stu_id
    				AND sd.is_receive=0
    		";
    	$where ='';
    	
    	$to_date = (empty($to_date))? '1': " sd.date_end <= '".$to_date." 23:59:59'";
    	$where.= " AND ".$to_date;
    	
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]=" stu_code LIKE '%{$s_search}%'";
    		$s_where[]=" stu_code LIKE '%{$s_search}%'";
    		$s_where[]=" stu_khname LIKE '%{$s_search}%'";
    		$s_where[]=" stu_enname LIKE '%{$s_search}%'";
    		$s_where[]=" tel LIKE '%{$s_search}%'";
    		$s_where[]=" father_phone LIKE '%{$s_search}%'";
    		$s_where[]=" mother_phone LIKE '%{$s_search}%'";
    		$s_where[]=" guardian_tel LIKE '%{$s_search}%'";
    		$s_where[]=" father_enname LIKE '%{$s_search}%'";
    		$s_where[]=" mother_enname LIKE '%{$s_search}%'";
    		$s_where[]=" guardian_enname LIKE '%{$s_search}%'";
    		$s_where[]=" remark LIKE '%{$s_search}%'";
    		$s_where[]=" home_num LIKE '%{$s_search}%'";
    		$s_where[]=" street_num LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND s.academic_year='.$search['study_year'];
    	}
    	if(!empty($search['group'])){
    		$where.=' AND s.group_id='.$search['group'];
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND s.degree='.$search['degree'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND s.branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=' AND s.grade='.$search['grade_all'];
    	}
    	
    	$where.=$dbp->getAccessPermission("s.branch_id");
    	$order=" ORDER BY sd.date_end DESC, sd.stu_id ASC";
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getStudentDropInfo($drop_id){
    	$db = $this->getAdapter();
    	$sql="SELECT 
			(SELECT branch_nameen FROM `rms_branch` WHERE rms_branch.br_id = sd.branch_id LIMIT 1) AS branch_name,
			(SELECT school_namekh FROM `rms_branch` WHERE rms_branch.br_id = sd.branch_id LIMIT 1) AS school_namekh,
			(SELECT school_nameen FROM `rms_branch` WHERE rms_branch.br_id = sd.branch_id LIMIT 1) AS school_nameen,
			(SELECT photo FROM `rms_branch` WHERE rms_branch.br_id = sd.branch_id LIMIT 1) AS branch_photo,
			sd.*,
			s.stu_khname,
			s.stu_enname,
			s.last_name,
			s.sex,
			s.tel,
			s.home_num,
			s.street_num,
			(SELECT v.village_namekh FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_namekh,
			(SELECT c.commune_namekh FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_namekh,
			(SELECT d.district_namekh FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_namekh,
			(SELECT province_kh_name FROM rms_province WHERE province_id=s.province_id LIMIT 1) AS province_kh_name,
			
			(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
		(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) lIMIT 1) AS grade,
		`g`.`amount_month`,
		(SELECT`rms_view`.`name_en`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
		AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
		(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`)) AS `room_name`
		
			FROM `rms_student_drop` AS sd,
			`rms_student` AS s,
			`rms_group` AS g
			WHERE 
    	g.id = sd.group AND
    	s.stu_id = sd.stu_id AND sd.id = $drop_id LIMIT 1";
    	return $db->fetchRow($sql);
    }
    
    public function getAllStudentNotYetGroup($search){
    	$db = $this->getAdapter();
    	$sql ='SELECT 
    	(SELECT branch_namekh FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
    	CONCAT(stu_khname," - ",stu_enname," ",last_name) as name,
    	s.*,
    	(SELECT name_en FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality,
    	(SELECT name_en FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation,
    	(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=s.group_id LIMIT 1 ) AS group_name,
    	(SELECT CONCAT(from_academic,"-",to_academic) from rms_tuitionfee where rms_tuitionfee.id=academic_year LIMIT 1) as academic_year,
    	(SELECT from_academic from rms_tuitionfee where rms_tuitionfee.id=s.academic_year LIMIT 1) as start_year,
    	(SELECT to_academic from rms_tuitionfee where rms_tuitionfee.id=s.academic_year LIMIT 1) as end_year,
    	(SELECT end_date from rms_tuitionfee where rms_tuitionfee.id=s.academic_year LIMIT 1) as end_date,
    	(SELECT name_en from rms_view where rms_view.type=4 and rms_view.key_code=s.session LIMIT 1)AS session,
    		
    	(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=s.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
    	(SELECT rms_items.title FROM rms_items WHERE rms_items.id=s.degree AND rms_items.type=1 LIMIT 1) AS degree,
    
    	(SELECT name_kh from rms_view where type=5 and key_code=s.is_subspend LIMIT 1) as status,
    	(SELECT v.village_name FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name,
    	(SELECT c.commune_name FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_name,
    	(SELECT d.district_name FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_name,
    	(SELECT province_en_name from rms_province where rms_province.province_id = s.province_id LIMIT 1)AS province,
    	(SELECT name_en from rms_view where rms_view.type=2 and rms_view.key_code=s.sex LIMIT 1) AS sextitle
    	
    	FROM rms_student as s ';
    	$where=' WHERE s.status=1 AND s.customer_type=1 AND s.is_setgroup =0';
    
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
//     	$from_date =(empty($search['start_date']))? '1': "s.create_date >= '".$search['start_date']." 00:00:00'";
//     	$to_date = (empty($search['end_date']))? '1': "s.create_date <= '".$search['end_date']." 23:59:59'";
//     	$where .= " AND ".$from_date." AND ".$to_date;
    	$order=" ORDER BY stu_id,degree,grade,academic_year DESC";
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
    		$s_where[]=" s.stu_khname LIKE '%{$s_search}%'";
    		$s_where[]=" s.stu_enname LIKE '%{$s_search}%'";
    		$s_where[]=" s.last_name LIKE '%{$s_search}%'";
    		$s_where[]=" s.tel LIKE '%{$s_search}%'";
    		$s_where[]=" s.father_phone LIKE '%{$s_search}%'";
    		$s_where[]=" s.mother_phone LIKE '%{$s_search}%'";
    		$s_where[]=" s.guardian_tel LIKE '%{$s_search}%'";
    		$s_where[]=" s.father_enname LIKE '%{$s_search}%'";
    		$s_where[]=" s.mother_enname LIKE '%{$s_search}%'";
    		$s_where[]=" s.guardian_enname LIKE '%{$s_search}%'";
    		$s_where[]=" s.remark LIKE '%{$s_search}%'";
    		$s_where[]=" s.home_num LIKE '%{$s_search}%'";
    		$s_where[]=" s.street_num LIKE '%{$s_search}%'";
    		$s_where[]=" s.village_name LIKE '%{$s_search}%'";
    		$s_where[]=" s.commune_name LIKE '%{$s_search}%'";
    		$s_where[]=" s.district_name LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND s.academic_year='.$search['study_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND s.degree='.$search['degree'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND s.branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['grade'])){
    		$where.=' AND s.grade='.$search['grade'];
    	}
//     	if(!empty($search['session'])){
//     		$where.=' AND s.session='.$search['session'];
//     	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getGroupBYStudentGrade($search){
    	$db = $this->getAdapter();
    	$sql="SELECT 
			g.branch_id,
			(SELECT branch_namekh FROM `rms_branch` WHERE br_id=g.branch_id LIMIT 1) AS branch_name,
			g.academic_year,
			(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=g.academic_year LIMIT 1) AS academic_year_name,
			g.session,
			(SELECT name_kh FROM rms_view WHERE rms_view.type=4 AND rms_view.key_code=g.session LIMIT 1) AS `session_name`, 
			g.grade,
			(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=g.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade_name, 
			g.degree,
			(SELECT rms_items.title FROM rms_items WHERE rms_items.id=g.degree AND rms_items.type=1 LIMIT 1) AS degree_name,
			(SELECT from_academic FROM rms_tuitionfee WHERE rms_tuitionfee.id=g.academic_year LIMIT 1) AS from_academic,
			(SELECT to_academic FROM rms_tuitionfee WHERE rms_tuitionfee.id=g.academic_year LIMIT 1) AS to_academic,
			(SELECT generation FROM rms_tuitionfee WHERE rms_tuitionfee.id=g.academic_year LIMIT 1) AS generation,
			COUNT(gds.stu_id) AS total_stu
			FROM `rms_group_detail_student` AS gds,
			`rms_group` AS g
			WHERE g.id = gds.group_id
			
			";
    	$where=' ';
    	if(($search['branch_id'])>0){
    		$where.=' AND g.branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND g.academic_year='.$search['study_year'];
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND g.degree='.$search['degree'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=' AND g.grade='.$search['grade_all'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND g.session='.$search['session'];
    	}
    	if (!empty($search['allacademicyear'])){
    		$acad = explode("-", $search['allacademicyear']);
    		if (!empty($acad)){
    			$from_year=$acad[0];
    			$to_year=$acad[1];
    			if (!empty($from_year)){
    			$where.=" AND (SELECT from_academic FROM rms_tuitionfee WHERE rms_tuitionfee.id=g.academic_year ) = '$from_year'";
    			}
    			if (!empty($from_year)){
    				$where.=" AND (SELECT to_academic FROM rms_tuitionfee WHERE rms_tuitionfee.id=g.academic_year ) = '$to_year'";
    			}
    		}
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("g.branch_id");
    	$where.= $dbp->getSchoolOptionAccess('(SELECT i.schoolOption FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` )');
    	$group_by = " GROUP BY g.branch_id,g.`academic_year`,
			g.`degree`,g.`grade`,g.`session`";
    	$order_by = " ORDER BY g.branch_id DESC,g.`academic_year` ASC,
			g.`degree` ASC,g.`grade` ASC,g.`session` ASC ";
    	return $db->fetchAll($sql.$where.$group_by.$order_by);
    }
    function getCountStuBYtype($branch_id,$academic_year,$degree,$grade,$session,$status_study=0,$is_new=null){
    	$db = $this->getAdapter();
    	$sql="SELECT 
			COUNT(gds.stu_id) AS total_stu
			FROM `rms_group_detail_student` AS gds,
			`rms_group` AS g
			WHERE g.id = gds.group_id
			AND g.branch_id =$branch_id
			AND g.academic_year=$academic_year
			AND g.degree = $degree
			AND g.grade = $grade
			AND g.session = $session
			";
    	if (!empty($is_new)){
    		$sql.=" AND gds.is_newstudent = 1";
    	}else{
    		if ($status_study!=0){
    			$sql.=" AND gds.stop_type != 0";
    		}else{
    			$sql.=" AND gds.stop_type = 0";
    		}
    	}
    	return $db->fetchOne($sql);
    }
    //for rpt-student-static
    function getAllYearTuitionfee(){
    	$db = $this->getAdapter();
    	$sql="SELECT CONCAT(t.from_academic,'-',t.to_academic) AS academicyear FROM `rms_tuitionfee` AS t
			GROUP BY t.from_academic,t.to_academic 
			ORDER BY t.from_academic DESC
		";
    	return $db->fetchAll($sql);
    }
    
//     public function getStudentInfo($id){
//     	$db = $this->getAdapter();
    
//     	$_db = new Application_Model_DbTable_DbGlobal();
//     	$lang = $_db->currentlang();
    
//     	if($lang==1){// khmer
//     		$label = "name_kh";
//     		$village_name = "village_namekh";
//     		$commune_name = "commune_namekh";
//     		$district_name = "district_namekh";
//     		$province = "province_kh_name";
//     	}else{ // English
//     		$label = "name_en";
//     		$village_name = "village_name";
//     		$commune_name = "commune_name";
//     		$district_name = "district_name";
//     		$province = "province_en_name";
//     	}
    
//     	$sql = "SELECT *,
//     	(SELECT CONCAT(f.from_academic,'-',f.to_academic) FROM rms_tuitionfee AS f WHERE f.id=(SELECT g.academic_year FROM `rms_group` AS g WHERE g.id=s.group_id LIMIT 1 ) AND f.`status`=1 GROUP BY f.from_academic,f.to_academic,f.generation)  AS academic_year,
//     	(SELECT sgh.group_id FROM rms_group_detail_student AS sgh WHERE sgh.stu_id = s.`stu_id` ORDER BY sgh.gd_id DESC LIMIT 1) as group_id,
//     	(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=s.group_id LIMIT 1 ) AS group_name,
//     	(SELECT rms_items.schoolOption FROM rms_items WHERE rms_items.id=s.degree AND rms_items.type=1 LIMIT 1) AS schoolOption,
//     	(SELECT name_kh FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality_title,
//     	(SELECT name_kh FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation_title,
//     	(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.father_job LIMIT 1) fath_job,
//     	(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.mother_job LIMIT 1) moth_job,
//     	(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.guardian_job LIMIT 1) guard_job,
    		
//     	(SELECT v.$village_name FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_title,
//     	(SELECT c.$commune_name FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_title,
//     	(SELECT d.$district_name FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_title,
//     	(SELECT $province FROM rms_province WHERE province_id=s.province_id LIMIT 1) AS province_title,
//     	(SELECT $label from rms_view where rms_view.type=2 and rms_view.key_code=s.sex LIMIT 1) AS sex
    	
//     	FROM rms_student as s
//     	WHERE s.stu_id =".$id."
//     	AND s.customer_type=1";
//     	$dbp = new Application_Model_DbTable_DbGlobal();
//     	$sql.=$dbp->getAccessPermission();
//     	return $db->fetchRow($sql);
//     }
    
    function getStudenCetificate($search){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$currentLang = $dbp->currentlang();
    	$colunmname='name_en';
    	$stu_name="CONCAT(st.last_name,' ',st.stu_enname)";
    	$dept="c.dept_eng";
    	$program="c.program_en";
    	if ($currentLang==1){
    		$colunmname='name_kh';
    		$stu_name="st.stu_khname";
    		$dept="c.dept_kh";
    		$program="c.program_kh";
    	}
    	
    	$sql="SELECT cd.*,
				(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = c.branch_id LIMIT 1) AS branch_name,
				(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = c.group_id LIMIT 1) AS group_code,
				$dept AS dept_kh,
				$program AS program_kh,
				c.from_date,
				c.to_date,
				c.issue_date,
				st.stu_enname,
				st.last_name,
				st.stu_khname,
				st.stu_code,
				$stu_name AS stu_name,
				(SELECT $colunmname FROM rms_view WHERE rms_view.type=2 and rms_view.key_code=st.sex LIMIT 1) as sex
			FROM 
				`rms_issuecertificate_detail` AS cd,
				rms_student AS st,
				`rms_issuecertificate` AS c
			WHERE 
				c.id = cd.certificate_id
				AND st.stu_id = cd.stu_id
			";
    	$where="";
    	
    	$from_date =(empty($search['start_date']))? '1': "c.issue_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "c.issue_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
    	
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " c.dept_kh LIKE '%{$s_search}%'";
    		$s_where[]=" c.dept_en LIKE '%{$s_search}%'";
    		$s_where[]=" c.program_kh LIKE '%{$s_search}%'";
    		$s_where[]=" c.program_en LIKE '%{$s_search}%'";
    		$s_where[]=" st.stu_enname LIKE '%{$s_search}%'";
    		$s_where[]=" st.last_name LIKE '%{$s_search}%'";
    		$s_where[]=" st.stu_khname LIKE '%{$s_search}%'";
    		$s_where[]=" st.stu_code LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['study_year'])){
    		$where.=' AND (SELECT g.academic_year FROM `rms_group` AS g WHERE g.id = c.group_id LIMIT 1) ='.$search['study_year'];
    	}
    	if(!empty($search['group'])){
    		$where.=' AND c.group_id='.$search['group'];
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND (SELECT g.degree FROM `rms_group` AS g WHERE g.id = c.group_id LIMIT 1) ='.$search['degree'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=' AND (SELECT g.grade FROM `rms_group` AS g WHERE g.id = c.group_id LIMIT 1) ='.$search['grade_all'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=' AND c.branch_id='.$search['branch_id'];
    	}
    	
    	$where.=$dbp->getAccessPermission("c.branch_id");
    	$order=" ORDER BY c.id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getStudenCetificateById($id){
    	$db = $this->getAdapter();
    	$sql="SELECT cd.*,
			    	(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = c.branch_id LIMIT 1 ) AS branch_name,
			    	(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = c.group_id LIMIT 1 ) AS group_code,
			    	c.dept_kh,
			    	c.dept_eng,
			    	c.program_kh,
			    	c.program_en,
			    	c.from_date,
			    	c.to_date,
			    	c.issue_date,
			    	st.stu_enname,
			    	st.last_name,
			    	st.stu_khname,
			    	st.stu_code,
			    	st.dob,
			    	st.photo,
			    	CONCAT(st.last_name,' ',st.stu_enname) AS stu_name,
			    	(SELECT name_en FROM rms_view WHERE rms_view.type=2 and rms_view.key_code=st.sex LIMIT 1 ) as sex,
			    	(SELECT name_kh FROM rms_view WHERE rms_view.type=2 and rms_view.key_code=st.sex LIMIT 1 ) as sexkh
	    	FROM
	    		`rms_issuecertificate_detail` AS cd,
	    		rms_student AS st,
	    		`rms_issuecertificate` AS c
	    	WHERE
	    	c.id = cd.certificate_id
	    	AND st.stu_id = cd.stu_id AND cd.id=$id ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission("c.branch_id");
    	$sql." LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
    
    function getStudenLetterofpraise($search){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$currentLang = $dbp->currentlang();
    	$colunmname='name_en';
    	$stu_name="CONCAT(st.last_name,' ',st.stu_enname)";
    	if ($currentLang==1){
    		$colunmname='name_kh';
    		$stu_name="st.stu_khname";
    	}
    	 
    	$sql="SELECT cd.*,
		    	(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = c.branch_id LIMIT 1) AS branch_name,
		    	(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = c.group_id LIMIT 1) AS group_code,
		    	c.issue_date,
		    	c.academic_year,
		    	c.grade,
		    	st.stu_enname,
		    	st.last_name,
		    	st.stu_khname,
		    	st.stu_code,
		    	$stu_name AS stu_name,
		    	(SELECT $colunmname FROM rms_view WHERE rms_view.type=2 and rms_view.key_code=st.sex LIMIT 1) as sex
	    	FROM
		    	`rms_issue_letterpraise_detail` AS cd,
		    	rms_student AS st,
		    	`rms_issue_letterpraise` AS c
	    	WHERE
		    	c.id = cd.letterpraise_id
		    	AND st.stu_id = cd.stu_id";
    	
    	$where="";
    	 
    	$from_date =(empty($search['start_date']))? '1': "c.issue_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "c.issue_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	 
    	 
    	if(!empty($search['title'])){
    	$s_where = array();
    	$s_search = addslashes(trim($search['title']));
    	$s_where[]=" c.academic_year LIKE '%{$s_search}%'";
    	$s_where[]=" c.grade LIKE '%{$s_search}%'";
    	$s_where[] = " (SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = c.branch_id LIMIT 1) LIKE '%{$s_search}%'";
    	$s_where[]=" (SELECT g.group_code FROM `rms_group` AS g WHERE g.id = c.group_id LIMIT 1) LIKE '%{$s_search}%'";
    	$s_where[]=" st.stu_enname LIKE '%{$s_search}%'";
    	$s_where[]=" st.last_name LIKE '%{$s_search}%'";
    	$s_where[]=" st.stu_khname LIKE '%{$s_search}%'";
    	$s_where[]=" st.stu_code LIKE '%{$s_search}%'";
    	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['study_year'])){
    	$where.=' AND (SELECT g.academic_year FROM `rms_group` AS g WHERE g.id = c.group_id LIMIT 1) ='.$search['study_year'];
    	}
    	if(!empty($search['group'])){
    			$where.=' AND c.group_id='.$search['group'];
    	}
    	if(!empty($search['degree'])){
	    	$where.=' AND (SELECT g.degree FROM `rms_group` AS g WHERE g.id = c.group_id LIMIT 1) ='.$search['degree'];
	    }
	    if(!empty($search['grade_all'])){
	    	$where.=' AND (SELECT g.grade FROM `rms_group` AS g WHERE g.id = c.group_id LIMIT 1) ='.$search['grade_all'];
	    }
	    if(!empty($search['branch_id'])){
	   		 $where.=' AND c.branch_id='.$search['branch_id'];
	    }
     
	    $where.=$dbp->getAccessPermission("c.branch_id");
	    $order=" ORDER BY c.id DESC";
	    return $db->fetchAll($sql.$where.$order);
    }
    
    function getStudenLetterofpraiseById($id){
    	$db = $this->getAdapter();
    	
   		 $dbp = new Application_Model_DbTable_DbGlobal();
    	$currentLang = $dbp->currentlang();
    	$colunmname='name_en';
    	$stu_name="CONCAT(st.last_name,' ',st.stu_enname)";
    	if ($currentLang==1){
    		$colunmname='name_kh';
    		$stu_name="st.stu_khname";
    	}
    	$sql="SELECT cd.*,
    	c.academic_year,
    	c.grade,
    	c.issue_date,
    	(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = c.branch_id LIMIT 1) AS branch_name,
    	(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = c.group_id LIMIT 1) AS group_code,
    	st.stu_enname,
    	st.last_name,
    	st.stu_khname,
    	st.stu_code,
    	st.dob,
    	st.photo,
    	$stu_name AS stu_name,
    	(SELECT $colunmname FROM rms_view WHERE rms_view.type=2 and rms_view.key_code=st.sex LIMIT 1) as sex
    	FROM
    	`rms_issue_letterpraise_detail` AS cd,
    	rms_student AS st,
    	`rms_issue_letterpraise` AS c
    	WHERE
    	c.id = cd.letterpraise_id
    	AND st.stu_id = cd.stu_id AND cd.id=$id  ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission("c.branch_id");
    	$sql." LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
}