<?php

class Allreport_Model_DbTable_DbRptGroup extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_group';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
//     	return $session_user->user_id;
    	 
//     }
// 	function submitDateList($data){
// 		$db=$this->getAdapter();
// 		$this->_name='rms_student';
// 		if(!empty($data['identity'])){
// 			$ids = explode(',', $data['identity']);
// 			foreach ($ids as $i){
// 				$arr = array(
// 						'stu_code'=>$data['student_'.$i]
// 						);
				
// 				$where=" stu_id = ".$data['stu_id'.$i];
// 				$this->update($arr, $where);
// 			}
// 		}
// 	} 
    public function getAllGroup($search){
    	$db = $this->getAdapter();
    	$sql = "SELECT `g`.`id`,`g`.`group_code` AS `group_code`,`g`.`semester` AS `semester`,
    	
    	(select CONCAT(from_academic,'-',to_academic,' (',generation,')') from rms_tuitionfee where rms_tuitionfee.id=g.academic_year) as academic_year,
    	
		(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
		(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) lIMIT 1) AS grade,
		
		(SELECT`rms_view`.`name_en`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
		AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
		(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`)) AS `room_name`,
		`g`.`start_date`,`g`.`expired_date`,`g`.`note`,
		(SELECT `rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 1)
		AND (`rms_view`.`key_code` = `g`.`status`)) LIMIT 1) AS `status`
		FROM `rms_group` as `g`  ";	
    	
    	$where= " where 1";
    	$order=" order by id DESC";
   		if(empty($search)){
	   		return $db->fetchAll($sql.$order);
	   	}
	   	
	   	if(!empty($search['title'])){
	   		$s_where = array();
	   		$s_search = addslashes(trim($search['title']));
		   		$s_where[] = " group_code LIKE '%{$s_search}%'";
		   		$s_where[] = " (SELECT rms_room.room_name FROM rms_room	WHERE (rms_room.room_id = g.room_id)) LIKE '%{$s_search}%'";
				$s_where[] = " (SELECT rms_view.name_en	FROM rms_view WHERE ((rms_view.type = 4)
								AND (rms_view.key_code = g.session))LIMIT 1) LIKE '%{$s_search}%'";
		   		//$s_where[] = " (select CONCAT(from_academic,'-',to_academic)) LIKE '%{$s_search}%'";
	    		$s_where[] = " (SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) lIMIT 1) LIKE '%{$s_search}%'";
	   			$s_where[] = " (SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) LIKE '%{$s_search}%'";
	   		$where .=' AND ( '.implode(' OR ',$s_where).')';
	   	}
	   	if(!empty($search['study_year'])){
	   		$where.=' AND g.academic_year='.$search['study_year'];
	   	}
	   	if(!empty($search['grade'])){
	   		$where.=' AND g.grade='.$search['grade'];
	   	}
	   	if(!empty($search['session'])){
	   		$where.=' AND g.session='.$search['session'];
	   	}
	   	
    	return $db->fetchAll($sql.$where.$order);
    	 
    }
   public function getStudentGroup($id,$search,$type){
	   	$session_lang=new Zend_Session_Namespace('lang');
		$lang_id=$session_lang->lang_id;
			$gender_str = 'name_en';
			$str_village='village_name';
			$str_commune='commune_name';
			$str_district='district_name';
			$str_province='province_en_name';
		if($lang_id==1){//for kh
			$gender_str = 'name_kh';
			$str_village='village_namekh';
			$str_commune='commune_namekh';
			$str_district='district_namekh';
			$str_province='province_kh_name';
		}
	   	$db = $this->getAdapter();
	   	$sql="SELECT
					 g.gd_id,
					 (SELECT CONCAT(b.branch_nameen) FROM rms_branch as b WHERE b.br_id=`gr`.branch_id LIMIT 1) AS branch_name,
					 (SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gr.academic_year LIMIT 1) AS academic_yeartitle,
					 (SELECT b.photo FROM rms_branch as b WHERE b.br_id=`gr`.branch_id LIMIT 1) AS branch_logo,
					 `g`.`group_id` AS `group_id`,
					 `g`.`stu_id`   AS `stu_id`,
				  	 `s`.`stu_code` AS `stu_code`,
				     `s`.`stu_khname` AS `kh_name`,
				     `s`.`stu_enname` AS `en_name`,
				     `s`.`last_name` AS `last_name`,
				      CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS fullName,
				     `s`.`address` AS `address`,
				      s.pob,
				     `s`.`tel` AS `tel`,
				     `s`.`sex` AS `gender`,
				     DATE_FORMAT(`s`.`dob`,'%d-%m-%Y') AS `dob`,
				     s.father_enname AS father_name,
				     (SELECT name_kh FROM rms_view where type=21 and key_code=`s`.`nationality` LIMIT 1) AS nationality,
    				(SELECT name_kh FROM rms_view where type=21 and key_code=`s`.`nation` LIMIT 1) AS nation,
					 (SELECT occu_name FROM `rms_occupation` WHERE occupation_id = s.father_job LIMIT 1) AS father_job,
					 s.mother_enname AS mother_name,
					 (SELECT occu_name FROM `rms_occupation` WHERE occupation_id = s.mother_job LIMIT 1) AS mother_job,
				    (SELECT
				        `rms_view`.$gender_str
				      FROM `rms_view`
				      WHERE ((`rms_view`.`type` = 2)
				             AND (`rms_view`.`key_code` = `s`.`sex`)) LIMIT 1) AS `sex`,
					  `g`.`status`   AS `status`,
					  `g`.`is_current`   AS `is_current`,
					  `g`.`is_pass`   AS `is_pass`,
					  `g`.`is_maingrade`   AS `is_maingrade`,
					  s.home_num,
					  s.street_num,
					    (SELECT v.$str_village FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name,
				    	(SELECT c.$str_commune FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_name,
				    	(SELECT d.$str_district FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_name,
				    	(SELECT $str_province FROM rms_province WHERE rms_province.province_id = s.province_id LIMIT 1) AS province,
				    	(SELECT t.teacher_name_kh FROM rms_teacher AS t WHERE t.id = gr.teacher_id LIMIT 1) as teacher
				FROM 
					`rms_group_detail_student` AS g,
					 rms_student as s,
					`rms_group` AS gr
				WHERE 
					g.itemType=1 
					AND gr.id = g.group_id
					AND g.stu_id = s.stu_id
		   			AND `g`.`status` = 1 ";
			
			if(!empty($search['group'])){
				$id= $search['group'] ;
				if (!empty($id)){
					$sql.=' AND g.group_id='.$id;
				}
			}else{
				if (!empty($id)){
					$sql.=' AND g.group_id='.$id;
				}
			}
			
			$search['study_type'] = empty($search['study_type'])?0:$search['study_type'];
			if($search['study_type']>-1){
				if($search['study_type']==1){
					$sql.=' AND (g.stop_type=2 OR g.stop_type= '.$search['study_type'].")";
				}else{
					$sql.=' AND g.stop_type= '.$search['study_type'];
				}
			}  
			
			$stuOrderBy = empty($search['stuOrderBy'])?0:$search['stuOrderBy'];
			
			$order= ' ORDER BY s.stu_khname ASC ';
			if ($stuOrderBy==1){
				$order= " ORDER By  `s`.`stu_code` ASC ";
			}elseif($stuOrderBy==2){
				$order= ' ORDER BY s.stu_khname ASC ';
			}elseif($stuOrderBy==3){
				$order= " ORDER BY CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) ASC ";
			}
			
			$dbp = new Application_Model_DbTable_DbGlobal();
			$sql.=$dbp->getAccessPermission("gr.branch_id");
			
		   	if(empty($search)){
		   		return $db->fetchAll($sql.$order);
		   	}
		   	if(!empty($search['txtsearch'])){
		   		$s_where = array();
		   		$s_search = addslashes(trim($search['txtsearch']));
			   		$s_where[] = " stu_enname LIKE '%{$s_search}%'";
			   		$s_where[] = " stu_khname LIKE '%{$s_search}%'";
		   			$s_where[] = " stu_code LIKE '%{$s_search}%'";
		   		$sql .=' AND ( '.implode(' OR ',$s_where).')';
		   	}
		   	if(!empty($search['branch_id'])){
		   		$sql.=' AND gr.branch_id = '.$search['branch_id'];
		   	}
		   	if(!empty($search['academic_year'])){
		   		$sql.=' AND gr.academic_year = '.$search['academic_year'];
		   	}
		   	if(!empty($search['group'])){
		   		$sql.=' AND gr.id = '.$search['group'];
		   	}
		 return $db->fetchAll($sql.$order);
	}
	public function getGroupDetailReport($search){//using
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
	   	
	   	$sql = "SELECT
				   	`g`.`id`,
				   	(SELECT $branch FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) AS branch_name,
				   	`g`.`group_code` AS `group_code`,
				   	(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academic,
				   	`g`.`semester` AS `semester`,
				   	(SELECT $degree FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1)  LIMIT 1) as degree,
				   	(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as grade,
				   	(SELECT	$label FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`)) LIMIT 1) AS `session`,
				   	(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
				   	(SELECT $label FROM `rms_view` WHERE `rms_view`.`type` = 9 AND `rms_view`.`key_code` = `g`.`is_pass` LIMIT 1) AS `status`,
				   	
				   	(SELECT COUNT(DISTINCT  sg.`stu_id`) FROM `rms_group_detail_student` AS sg,rms_student AS s  
	   					WHERE sg.itemType=1 AND sg.`group_id`=`g`.`id` AND s.stu_id =sg.`stu_id` AND s.status=1 AND sg.is_newstudent=1 LIMIT 1) AS New_Student,
	   					
				   	(SELECT COUNT(DISTINCT  sg.`stu_id`) FROM `rms_group_detail_student` AS sg,rms_student AS s  
	   					WHERE sg.itemType=1 AND sg.`group_id`=`g`.`id` AND s.stu_id =sg.`stu_id` AND s.status=1  LIMIT 1) AS Num_Student,
	   				(SELECT COUNT(DISTINCT  sg.`stu_id`) FROM `rms_group_detail_student` AS sg,rms_student as s 
	   					WHERE sg.itemType=1 AND sg.`group_id`=`g`.`id` AND s.stu_id =sg.`stu_id` AND s.status =1 AND sg.stop_type!=0 LIMIT 1) AS student_drop
				FROM 
	   				`rms_group` `g`
	   			WHERE 
	   				 group_code != '' AND status=1 ";
	   	
	   	$where=" ";
	   	if(!empty($search['adv_search'])){
	   		$s_where = array();
	   		$s_search = addslashes(trim($search['adv_search']));
	   		$s_where[] = " `g`.`group_code` LIKE '%{$s_search}%'";
	   		$s_where[] = " 	`g`.`semester` LIKE '%{$s_search}%'";
	   		$s_where[] = "  (SELECT	name_en FROM rms_view WHERE rms_view.type = 4 AND rms_view.key_code = g.session LIMIT 1) LIKE '%{$s_search}%'";
	   		$s_where[] = "  (SELECT	name_en FROM rms_view WHERE rms_view.type = 9 AND rms_view.key_code = g.is_pass LIMIT 1) LIKE '%{$s_search}%'";
	   		$sql .=' AND ( '.implode(' OR ',$s_where).')';
	   	}
	   	if(!empty($search['branch_id'])){
	   		$where.=' AND g.branch_id='.$search['branch_id'];
	   	}
	   	if(!empty($search['academic_year'])){
	   		$where.=' AND g.academic_year='.$search['academic_year'];
	   	}
	   	if(!empty($search['teacher'])){
	   		$where.=' AND g.teacher_id='.$search['teacher'];
	   	}
	   	if(!empty($search['grade'])){
	   		$where.=' AND g.grade='.$search['grade'];
	   	}
	   	if($search['room']>0){
	   		$where.=' AND `g`.`room_id`='.$search['room'];
	   	}
	   	if($search['degree']>0){
	   		$where.=' AND `g`.`degree`='.$search['degree'];
	   	}
	   	if(!empty($search['school_option'])){
	   		$where.=' AND g.school_option='.$search['school_option'];
	   	}
	   	if(!empty($search['group'])){
	   		$where.=' AND g.id='.$search['group'];
	   	}
	   	if($search['study_status']>=0){
	   		$where.=' AND g.is_pass='.$search['study_status'];
	   	}
	   	$dbp = new Application_Model_DbTable_DbGlobal();
	   	$where.=$dbp->getAccessPermission('g.branch_id');
	   	$where.= $dbp->getSchoolOptionAccess('(SELECT i.schoolOption FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` )');
	   	
	   	$order = ' ORDER BY g.branch_id , g.academic_year DESC ,`g`.`degree`,`g`.`grade`,`g`.`is_pass` ASC ,`g`.`group_code` ASC ';
	   	return $db->fetchAll($sql.$where.$order);
	}
   
	public function getGroupDetailByID($id){
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
	   	$sql = "SELECT
				   	`g`.`id`,
				   	`g`.`branch_id`,
				   	g.academic_year,
				   	(SELECT CONCAT(b.branch_nameen) FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branch_name,
				   	(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS school_nameen,
					(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branch_logo,
				   	`g`.`group_code`    AS `group_code`,
				   	
				   	(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academic,
				   	`g`.`semester` AS `semester`,
				   	`g`.`degree` as degree_id,
				   	(SELECT $degree FROM `rms_items`	WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1)  LIMIT 1) as degree,
				   	(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as grade,
				   	(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`)) LIMIT 1) AS `session`,
				   	(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)) AS `room_name`,
				   	`g`.`start_date`,
				   	`g`.`expired_date`,
				   	`g`.`note`,
				   	`g`.`time`,
				   	(SELECT t.teacher_name_en FROM `rms_teacher` AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacher_name_en,
					(SELECT t.teacher_name_kh FROM `rms_teacher` AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacher_name_kh,
				   	(SELECT `rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 1) AND (`rms_view`.`key_code` = `g`.`status`)) LIMIT 1) AS `status`,
				   	(SELECT COUNT(`stu_id`) FROM `rms_group_detail_student` WHERE itemType=1 AND `group_id`=`g`.`id`)AS Num_Student
			   	FROM 
		   			`rms_group` `g` 
		   		WHERE 
		   			`g`.`id`=".$id." ";
	   	
	   	$dbp = new Application_Model_DbTable_DbGlobal();
	   	$sql.=$dbp->getAccessPermission("g.branch_id");
	   	$sql.="  LIMIT 1 ";
	   	return $db->fetchRow($sql);
	}
	
	function getAllTeacherByGroup($group_id){
		$db = $this->getAdapter();
		$sql=" SELECT 
					t.id,t.`teacher_name_kh` AS name
				FROM
					rms_group_subject_detail AS gsd,
					rms_teacher AS t
				WHERE 
					gsd.teacher = t.id
					AND t.teacher_name_kh!=''
					AND gsd.group_id =  $group_id	
		
			";
		return $db->fetchAll($sql);
	}
	
	function getAllSubjectByGroup($group_id){
		$db = $this->getAdapter();
		$sql=" SELECT
					s.id,
					CONCAT(s.`subject_titlekh`,'-',s.`subject_titleen`) AS name
				FROM
					rms_group_subject_detail AS gsd,
					rms_subject AS s
				WHERE
					gsd.subject_id = s.id
					AND gsd.group_id =  $group_id
			";
		return $db->fetchAll($sql);
	}
	function UpdateAmountStudent($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$ids = explode(",", $data['identity']);
			$iddetail ="";
			if (!empty($ids)) foreach ($ids as $id){
				if (empty($iddetail)){
					if (!empty($data['gd_id'.$id])){
						$iddetail=$data['gd_id'.$id];
					}
				}else{
					if (!empty($data['gd_id'.$id])){
						$iddetail=$iddetail.",".$data['gd_id'.$id];
					}
				}
			}
			$this->_name="rms_group_detail_student";
			$where1=" group_id=".$data['group_id'];
			if(!empty($iddetail)){
				$where1.=" AND gd_id NOT IN (".$iddetail.")";
			}
			$this->delete($where1);
			$db->commit();
		}catch(exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	
	function getScoreSettingIdByGroup($group_id){
		$db = $this->getAdapter();
		$sql="SELECT * FROM `rms_score_eng` AS s WHERE s.group_id = $group_id
			AND s.status=1
			GROUP BY s.score_setting
			ORDER BY s.id DESC 
			LIMIT 1";
		return $db->fetchRow($sql);
	}
	function checkScorePolicyMoreThanOne($group_id){
		$db = $this->getAdapter();
		$sql="SELECT s.score_setting FROM `rms_score_eng` AS s WHERE s.group_id = $group_id 
				AND s.status=1
				GROUP BY s.score_setting
				ORDER BY s.id DESC ";
		return $db->fetchAll($sql);
	}
	function getScoreEngByStuAndType($group_id,$stu_id,$typescore){
		$db = $this->getAdapter();
		$sql="SELECT sed.* FROM `rms_score_eng_detail` AS sed,`rms_score_eng` AS se
			WHERE sed.score_id=se.id
			AND se.group_id=$group_id AND sed.student_id=$stu_id
			AND se.exame_type=$typescore 
			AND se.status=1
			ORDER BY sed.id DESC
		LIMIT 1";
		return $db->fetchRow($sql);
	}
	public function getStudentAddress($search,$type){
		$session_lang=new Zend_Session_Namespace('lang');
		$lang_id=$session_lang->lang_id;
		$gender_str = 'name_en';
		$str_village='village_name';
		$str_commune='commune_name';
		$str_district='district_name';
		$str_province='province_en_name';
		if($lang_id==1){//for kh
			$gender_str = 'name_kh';
			$str_village='village_namekh';
			$str_commune='commune_namekh';
			$str_district='district_namekh';
			$str_province='province_kh_name';
		}
		$db = $this->getAdapter();
		$sql="SELECT
				g.gd_id,
				(SELECT CONCAT(b.branch_nameen) FROM rms_branch as b WHERE b.br_id=`gr`.branch_id LIMIT 1) AS branch_name,
				(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gr.academic_year LIMIT 1) AS academic_yeartitle,
				(SELECT b.photo FROM rms_branch as b WHERE b.br_id=`gr`.branch_id LIMIT 1) AS branch_logo,
				`g`.`group_id` AS `group_id`,
				`g`.`stu_id`   AS `stu_id`,
				`s`.`stu_code` AS `stu_code`,
				`s`.`stu_khname` AS `kh_name`,
				`s`.`stu_enname` AS `en_name`,
				`s`.`last_name` AS `last_name`,
				`s`.`address` AS `address`,
				s.pob,
				`s`.`tel` AS `tel`,
				`s`.`sex` AS `gender`,
				`s`.`dob` AS `dob`,
				s.father_enname AS father_name,
				(SELECT name_kh FROM rms_view where type=21 and key_code=`s`.`nationality` LIMIT 1) AS nationality,
				(SELECT name_kh FROM rms_view where type=21 and key_code=`s`.`nation` LIMIT 1) AS nation,
				(SELECT occu_name FROM `rms_occupation` WHERE occupation_id = s.father_job LIMIT 1) AS father_job,
				s.mother_enname AS mother_name,
				(SELECT occu_name FROM `rms_occupation` WHERE occupation_id = s.mother_job LIMIT 1) AS mother_job,
				(SELECT
				`rms_view`.$gender_str
				FROM `rms_view`
				WHERE ((`rms_view`.`type` = 2)
				AND (`rms_view`.`key_code` = `s`.`sex`)) LIMIT 1) AS `sex`,
				`g`.`status`   AS `status`,
				s.home_num,
				s.street_num,
				(SELECT v.$str_village FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name,
				(SELECT c.$str_commune FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_name,
				(SELECT d.$str_district FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_name,
				(SELECT $str_province FROM rms_province WHERE rms_province.province_id = s.province_id LIMIT 1) AS province,
				
				s.village_name  AS village_text,
				s.commune_name  AS commune_text,
				s.district_name  AS district_text,
				s.province_id AS province_text,
				
				(SELECT t.teacher_name_kh FROM rms_teacher AS t WHERE t.id = gr.teacher_id LIMIT 1) as teacher
				FROM
					`rms_group_detail_student` AS g,
					rms_student as s,
					`rms_group` AS gr
				WHERE
				g.itemType=1 
				AND gr.id = g.group_id
				AND g.stu_id = s.stu_id
				AND `g`.`status` = 1 ";
		
		$sql.=' AND g.stop_type=0  AND g.is_current=1 AND is_maingrade=1 ';
		
		$order= ' ORDER BY s.stu_khname ASC,s.stu_enname ASC ';
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("gr.branch_id");
			
		if(empty($search)){
		return $db->fetchAll($sql.$order);
	}
		if(!empty($search['txtsearch'])){
			$s_where = array();
				$s_search = addslashes(trim($search['txtsearch']));
				$s_where[] = " stu_enname LIKE '%{$s_search}%'";
				$s_where[] = " stu_khname LIKE '%{$s_search}%'";
				$s_where[] = " stu_code LIKE '%{$s_search}%'";
				$sql .=' AND ( '.implode(' OR ',$s_where).')';
				}
				if(!empty($search['branch_id'])){
				$sql.=' AND gr.branch_id = '.$search['branch_id'];
				}
				if(!empty($search['academic_year'])){
				$sql.=' AND gr.academic_year = '.$search['academic_year'];
				}
				if(!empty($search['group'])){
				$sql.=' AND gr.id = '.$search['group'];
		}
		return $db->fetchAll($sql.$order);
	}
       
}