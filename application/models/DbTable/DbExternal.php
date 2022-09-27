<?php

class Application_Model_DbTable_DbExternal extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_teacher';
    
	public function userAuthenticateTeacher($username,$password)
	{
		$db_adapter = Application_Model_DbTable_DbUsers::getDefaultAdapter();
		$auth_adapter = new Zend_Auth_Adapter_DbTable($db_adapter);
		$auth_adapter->setTableName('rms_teacher') // table where users are stored
		->setIdentityColumn('user_name') // field name of user in the table
		->setCredentialColumn('password') // field name of password in the table
		->setCredentialTreatment('MD5(?) AND status=1 '); // optional if password has been hashed
			
		$auth_adapter->setIdentity($username); // set value of username field
		$auth_adapter->setCredential($password);// set value of password field
		//instantiate Zend_Auth class
		$auth = Zend_Auth::getInstance();
	
		$result = $auth->authenticate($auth_adapter);
		if($result->isValid()){
			return true;
		}else{
			return false;
		}
	}
	
	public function getTeacherInfo($username,$password)
	{		
		$db = $this->getAdapter();
		if (!empty($username)){	
			$sql=" SELECT s.* FROM rms_teacher AS s WHERE 1 ";
			$sql.= " AND ".$db->quoteInto('s.user_name=?', $username);
			$sql.= " AND ".$db->quoteInto('s.password=?', md5($password));
			$row=$db->fetchRow($sql);
			if(!$row) return NULL;
			return $row;
			
		}else {
			return null;
		}
	}
	
	public function getCurrentTeacherInfo()
	{		
		$db = $this->getAdapter();
		$sql=" SELECT s.* FROM rms_teacher AS s WHERE 1 ";
		$sql.= " AND ".$db->quoteInto('s.id=?', $this->getUserExternalId());
		$row=$db->fetchRow($sql);
		if(!$row) return NULL;
		return $row;
	}
	
	public static function getUserExternalId(){
		$sessionUserExternal=new Zend_Session_Namespace("externalAuth");
		$userId = $sessionUserExternal->userId;
		return $userId;
	}
	
	function changePassword($newpwd){
		$_user_data=array(
			'password'=> MD5($newpwd)		
	    );    	   
		$currentTeacher = $this->getUserExternalId();
		$where=$this->getAdapter()->quoteInto('id=?', $currentTeacher); 
    	$this->_name='rms_teacher';   
		return  $this->update($_user_data,$where);
	}
	
	function coutingClassByUser($arrCondiction=array()){
		$db = $this->getAdapter();
		$sql="
			SELECT COUNT(DISTINCT(gsjb.group_id))  
			";
		$sql.=" FROM `rms_group_subject_detail` AS gsjb,
					`rms_group` AS g 
				WHERE 
					g.id = gsjb.group_id 
					AND g.is_use=1
					  ";//
		
		$sql.=' AND gsjb.teacher='.$this->getUserExternalId();
		
		
		if(!empty($arrCondiction['classTypeFilter'])){
			if($arrCondiction['classTypeFilter']==1){
				//Completed Class
				$sql.=" AND g.is_pass =4 ";
			}elseif($arrCondiction['classTypeFilter']==2){
				//Active Class
				$sql.=" AND g.is_pass=2 ";
			}
		}
		
		return $db->fetchOne($sql);
	}
	
	function getAllClassByUser($search=array()){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label="name_en";
		$branch = "branch_nameen";
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
			$branch = "branch_namekh";
		}
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$currentTeacher = $this->getUserExternalId();
		$sql="
			SELECT 
				g.*
				,(SELECT $branch FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) AS branchName
				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear	
				,(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) AS degree
				,(SELECT id.$colunmname FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) AS grade
				,(SELECT`rms_view`.$label FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`
				,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `roomName`
				,CASE
					WHEN g.teacher_id = $currentTeacher THEN '".$tr->translate("MAINTEACHER")."' 
				END AS mainTeacher
				,(SELECT te.teacher_name_kh FROM rms_teacher AS te WHERE te.id = g.teacher_id LIMIT 1 ) as mainTeaccher
				,(SELECT te.teacher_name_kh FROM rms_teacher AS te WHERE te.id = gsjb.teacher LIMIT 1 ) as subjectTeaccher
				,(SELECT $label from rms_view where type=9 and key_code=g.is_pass) as groupStatus
				,(SELECT COUNT(gds.gd_id)  FROM `rms_group_detail_student` AS gds WHERE gds.group_id = g.id AND gds.is_maingrade=1 ) AS amountStudent
			
		";
		$sql.=" FROM 
					`rms_group_subject_detail` AS gsjb,
					`rms_group` AS g ";
		$where =' WHERE 
					g.id = gsjb.group_id AND g.is_use=1
					 ';
		$where.=' AND gsjb.teacher='.$currentTeacher;
		
		
		
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]	="	`g`.`group_code` LIKE '%{$s_search}%'";
			$s_where[]	="	(SELECT b.branch_namekh FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]	="	(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]	=" `g`.`semester` LIKE '%{$s_search}%'";
			
			$s_where[]	="	(SELECT i.title FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]	="	(SELECT i.title_en FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) LIKE '%{$s_search}%'";
			
			$s_where[] 	="	(SELECT id.title FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] 	="	(SELECT id.title_en FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) LIKE '%{$s_search}%'";
			
			$s_where[] 	="	(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`)) LIKE '%{$s_search}%'";
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['academic_year'])){
			$where.=' AND g.academic_year='.$search['academic_year'];
		}
		if(!empty($search['degree'])){
			$where.=' AND `g`.`degree`='.$search['degree'];
		}
		if(!empty($search['grade'])){
			$where.=' AND g.grade='.$search['grade'];
		}
		if(!empty($search['is_pass']) AND $search['is_pass']>-1){
			$where.=' AND g.is_pass='.$search['is_pass'];
		}
		$where.=' GROUP BY gsjb.group_id ';
		$order =  ' ORDER BY `g`.`id` DESC ' ;
		
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
				   	`g`.*
				   	
				   	,(SELECT CONCAT(b.branch_nameen) FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branch_name
				   	,(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS school_nameen
					,(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branch_logo
				   	
				   	,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academic
				   	,`g`.`degree` AS degree_id
				   	,`g`.`grade` AS gradeId
				   	,(SELECT $degree FROM `rms_items`	WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1)  LIMIT 1) as degree
				   	,(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as grade
				   	,(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`)) LIMIT 1) AS `session`
				   	,(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)) AS `room_name`
				   	,(SELECT t.teacher_name_en FROM `rms_teacher` AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacher_name_en
					,(SELECT t.teacher_name_kh FROM `rms_teacher` AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacher_name_kh
				   	,(SELECT `rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 1) AND (`rms_view`.`key_code` = `g`.`status`)) LIMIT 1) AS `status`
				   	,(SELECT grading.title FROM `rms_scoreengsetting` AS grading WHERE grading.schoolOption=1 AND grading.id=g.gradingId LIMIT 1)AS gradingSystem
				   	,(SELECT COUNT(`stu_id`) FROM `rms_group_detail_student` WHERE itemType=1 AND `group_id`=`g`.`id`)AS Num_Student
			   	FROM 
		   			`rms_group` `g` 
		   		WHERE 
		   			`g`.`id`=".$id." ";
	   	
	   	$dbp = new Application_Model_DbTable_DbGlobal();
	   	$sql.=$dbp->getAccessPermission("g.branch_id");
	   	$sql.="  LIMIT 1 ";
	   	
	   	return $db->fetchRow($sql);
	}
	
	function getAllSubjectByGroup($data){
		$db=$this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='subject_titleen';
		if ($currentLang==1){
			$colunmname='subject_titlekh';
		}
		
		$groupId = empty($data['groupId'])?0:$data['groupId'];
		$examType = empty($data['examType'])?0:$data['examType'];
		
		$sql="
		SELECT 
			gsjd.subject_id AS id
			,(SELECT sj.$colunmname FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS name
		FROM 
			rms_group_subject_detail AS gsjd ,
			rms_group as g
		WHERE 
			g.id = gsjd.group_id
			and gsjd.group_id = ".$groupId;
		$sql.=' AND gsjd.teacher='.$this->getUserExternalId();
		if($examType==1){//for month
			$sql.=" AND gsjd.amount_subject >0 ";
		}else{//for semester
			$sql.=" AND gsjd.amount_subject_sem >0 ";
		}
		
		$sql.=" ORDER BY (SELECT sj.$colunmname FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) ASC";
		return $db->fetchAll($sql);
	}
	function getSubjectGroupInfo($data){
		$db=$this->getAdapter();
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='subject_titleen';
		if ($currentLang==1){
			$colunmname='subject_titlekh';
		}
		
		$groupId = empty($data['groupId'])?0:$data['groupId'];
		$examType = empty($data['examType'])?0:$data['examType'];
		$subjectId = empty($data['subjectId'])?0:$data['subjectId'];
		$sql="
			SELECT 
				gsjd.*
				,g.amount_subject AS amountSubjectDivide
				,gsjd.max_score AS maxSubjectscore
				,(SELECT sj.parent FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS parent
				
				,(SELECT sj.is_parent FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS isParent
				,(SELECT sj.shortcut FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS shortcut
				
				,(gsjd.amount_subject) amtsubjectMonth
				,(gsjd.amount_subject_sem) amtsubjectSemester
				,(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS subjectTitleKh
				,(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS subjectTitleEn
				,(SELECT sj.$colunmname FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS name
			FROM 
		 		rms_group_subject_detail AS gsjd ,
		 		rms_group as g
			WHERE 
				g.id = gsjd.group_id
				and gsjd.group_id = ".$groupId;
			$sql.=" AND gsjd.subject_id =".$subjectId;
			
			$sql.=' ORDER BY gsjd.id ASC LIMIT 1';
			return $db->fetchRow($sql);
	 	    
	}
	
	public function getStudentListByGroup($search){
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
					 g.gd_id
					,gr.branch_id AS branchId
					,(SELECT CONCAT(b.branch_nameen) FROM rms_branch as b WHERE b.br_id=`gr`.branch_id LIMIT 1) AS branch_name
					,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gr.academic_year LIMIT 1) AS academic_yeartitle
					,(SELECT b.photo FROM rms_branch as b WHERE b.br_id=`gr`.branch_id LIMIT 1) AS branch_logo
					,g.`group_id` AS `group_id`
					,g.`stu_id`   AS `stu_id`
				  	,s.`stu_code` AS `stu_code`
				    ,s.`stu_khname` AS `kh_name`
				    ,s.`stu_enname` AS `en_name`
				    ,s.`last_name` AS `last_name`
				    ,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS fullName
				    ,`s`.`address` AS `address`
				    ,s.pob
				    ,`s`.`tel` AS `tel`
				    ,`s`.`sex` AS `gender`
				    ,DATE_FORMAT(`s`.`dob`,'%d-%m-%Y') AS `dob`
				    ,s.father_enname AS father_name
				    ,(SELECT name_kh FROM rms_view where type=21 and key_code=`s`.`nationality` LIMIT 1) AS nationality
    				,(SELECT name_kh FROM rms_view where type=21 and key_code=`s`.`nation` LIMIT 1) AS nation
					,(SELECT occu_name FROM `rms_occupation` WHERE occupation_id = s.father_job LIMIT 1) AS father_job
					,s.mother_enname AS mother_name
					,(SELECT occu_name FROM `rms_occupation` WHERE occupation_id = s.mother_job LIMIT 1) AS mother_job
				    ,(SELECT
				        `rms_view`.$gender_str
				      FROM `rms_view`
				      WHERE ((`rms_view`.`type` = 2)
				             AND (`rms_view`.`key_code` = `s`.`sex`)) LIMIT 1) AS `sex`
				  ,g.`status`   AS `status`
				  ,g.`is_current`   AS `is_current`
				  ,g.`is_pass`   AS `is_pass`
				  ,g.`is_maingrade`   AS `is_maingrade`
				  ,s.home_num
				  ,s.street_num
				    ,(SELECT v.$str_village FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name
			    	,(SELECT c.$str_commune FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_name
			    	,(SELECT d.$str_district FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_name
			    	,(SELECT $str_province FROM rms_province WHERE rms_province.province_id = s.province_id LIMIT 1) AS province
					
				,(SELECT te.signature from rms_teacher AS te WHERE te.id = gr.teacher_id LIMIT 1 ) AS mainTeacherSigature
				,(SELECT te.teacher_name_kh from rms_teacher AS te WHERE te.id = gr.teacher_id LIMIT 1 ) AS mainTeaccherNameKh
				,(SELECT te.teacher_name_en from rms_teacher AS te WHERE te.id = gr.teacher_id LIMIT 1 ) AS mainTeaccherNameEng
				
			FROM 
				`rms_group_detail_student` AS g,
				 rms_student as s,
				`rms_group` AS gr
			WHERE 
				g.itemType=1 
				AND gr.id = g.group_id
				AND g.stu_id = s.stu_id
				AND `g`.`status` = 1 ";
			$groupId = empty($search['group_id'])?0:$search['group_id'];
			if (!empty($groupId)){
				$sql.=' AND g.group_id='.$groupId;
			}
			$sql.=' AND gr.teacher_id='.$this->getUserExternalId();
			
			$stuOrderBy = empty($search['stuOrderBy'])?0:$search['stuOrderBy'];
			
			$order= ' ORDER BY s.stu_khname ASC ';
			if ($stuOrderBy==1){
				$order= " ORDER BY CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) ASC ";
			}
			return $db->fetchAll($sql.$order);
	}
	
	
	function getGradingSystemDetail($data){
		$db = $this->getAdapter();
		$gradingId = empty($data['gradingId'])?0:$data['gradingId'];
		$subjectId = empty($data['subjectId'])?0:$data['subjectId'];
		
		$sql="
			SELECT 
				s.*
				,(SELECT es.title FROM `rms_exametypeeng` AS es WHERE es.id = s.exam_typeid LIMIT 1) AS criterialTitle 
				,(SELECT es.title_en FROM `rms_exametypeeng` AS es WHERE es.id = s.exam_typeid LIMIT 1) AS criterialTitleEng 
			FROM `rms_scoreengsettingdetail` AS s 
			WHERE s.score_setting_id=$gradingId 
			AND s.subjectId =$subjectId
		";
		$row = $db->fetchRow($sql);
		
		$sql="
			SELECT 
				s.*
				,(SELECT es.title FROM `rms_exametypeeng` AS es WHERE es.id = s.exam_typeid LIMIT 1) AS criterialTitle 
				,(SELECT es.title_en FROM `rms_exametypeeng` AS es WHERE es.id = s.exam_typeid LIMIT 1) AS criterialTitleEng 
			FROM `rms_scoreengsettingdetail` AS s 
			WHERE s.score_setting_id=$gradingId 
			AND s.subjectId =0
		";
		if(!empty($row)){
			$sql.=" AND s.exam_typeid !=".$row['exam_typeid'];
		}
		$db = $this->getAdapter();
		$rRow = $db->fetchAll($sql);
   	
		if(!empty($row)){
			array_unshift($rRow, $row);
		}
		asort($rRow);
		return $rRow;
	}
	
	function getClassSubjectScoreById($gradingId){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label = 'name_en';
		$branch = "branch_nameen";
		$month = "month_en";
		$subjectTitle='subject_titleen';
		if ($currentLang==1){
			$colunmname='title';
			$label = 'name_kh';
			$branch = "branch_namekh";
			$month = "month_kh";
			$subjectTitle='subject_titlekh';
		}
		$sql="SELECT 
				grd.*
				,(SELECT br.$branch FROM `rms_branch` AS br WHERE br.br_id=grd.branchId LIMIT 1) As branchName
				,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =grd.examType LIMIT 1) as examTypeTitle
				,CASE
					WHEN grd.examType = 2 THEN grd.forSemester
				ELSE (SELECT $month FROM `rms_month` WHERE id=grd.forMonth  LIMIT 1) 
				END AS forMonthTitle
				,g.group_code AS  groupCode
				,g.gradingId AS  gradingId
				,g.academic_year AS  academicYearId
				,g.grade AS  gradeId
				,g.degree AS  degreeId
				
				,(SELECT te.signature from rms_teacher AS te WHERE te.id = g.teacher_id LIMIT 1 ) AS mainTeacherSigature
				,(SELECT te.teacher_name_kh from rms_teacher AS te WHERE te.id = g.teacher_id LIMIT 1 ) AS mainTeaccherNameKh
				,(SELECT te.teacher_name_en from rms_teacher AS te WHERE te.id = g.teacher_id LIMIT 1 ) AS mainTeaccherNameEng
				
				,(SELECT gsjd.max_score FROM rms_group_subject_detail AS gsjd WHERE g.id = gsjd.group_id AND gsjd.subject_id =grd.subjectId LIMIT 1 ) AS maxSubjectscore
				
				,(SELECT te.signature from rms_teacher AS te WHERE te.id = grd.teacherId LIMIT 1 ) AS teacherSigature
				,(SELECT te.teacher_name_kh from rms_teacher AS te WHERE te.id = grd.teacherId LIMIT 1 ) AS teaccherNameKh
				,(SELECT te.teacher_name_en from rms_teacher AS te WHERE te.id = grd.teacherId LIMIT 1 ) AS teaccherNameEng
				
				,(SELECT sj.$subjectTitle FROM `rms_subject` AS sj WHERE sj.id = grd.subjectId LIMIT 1) AS subjectTitle
				,(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = grd.subjectId LIMIT 1) AS subjectTitleKh
				,(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = grd.subjectId LIMIT 1) AS subjectTitleEng
				,(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = grd.subjectId LIMIT 1) AS subjectTitleEng
				,(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear
				,(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degreeTitle
				,(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
				,(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS sessionTitle
				,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName
		";
		
		$sql.=" FROM rms_grading AS grd,
					rms_group AS g 
			WHERE grd.groupId=g.id  AND grd.inputOption=2 ";
		
		$where ='';
		$where.=' AND grd.teacherId='.$this->getUserExternalId();
		$where.=' AND grd.id='.$gradingId;
		$where.=' LIMIT 1 ';

		
		return $db->fetchRow($sql.$where);
	}
	
	
	function getStudentByGroup($data=array()){
		$db=$this->getAdapter();
		
		$groupId = empty($data['groupId'])?0:$data['groupId'];
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$studentName="CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,''))";
		$studentEnName=$studentName;
		
		if ($currentLang==1){
			$studentName='s.stu_khname';
		}
		
		$sql="
				SELECT
					sgh.`stu_id`
					,sgh.`stu_id` AS studentId
					,(SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS stuCode
					,(SELECT ".$studentName." FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS stu_name
					,(SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS stuKhName
					,(SELECT ".$studentEnName." FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS stuEnName
					,(SELECT s.sex FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS sex
				FROM 
					`rms_group_detail_student` AS sgh
				WHERE 
					sgh.itemType=1 
					and sgh.`group_id` = ".$groupId; //AND sgh.stop_type=0
		$order=" ORDER BY (SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) ASC ";
		if(!empty($data['sortStundent'])){
			if($data['sortStundent']==1){
				$order=" ORDER BY (SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) ASC ";
			}else if($data['sortStundent']==2){
				$order=" ORDER BY (SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) ASC ";
			}else if($data['sortStundent']==3){
				$order=" ORDER BY (SELECT ".$studentEnName." FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) ASC ";
			}
		}	
		return $db->fetchAll($sql.$order);
	}
	
	function getScoreByCriterial($gradingId,$studentId,$criteriaId){
		$db=$this->getAdapter();
		$sql="
			SELECT 
				grd.*
			FROM 
				`rms_grading_detail` AS grd
			WHERE 
				grd.gradingId =$gradingId
				AND grd.studentId =$studentId
				AND grd.criteriaId =$criteriaId
		";
		//$sql.=" LIMIT 1 ";
		return $db->fetchAll($sql);
	}
	function getAverageAndRankBySubjectOfCriterial($gradingId,$studentId){
		$db=$this->getAdapter();
		$sql="
			SELECT 
				grt.*,
				FIND_IN_SET( 
					grt.totalAverage, 
						(    
							SELECT 
								GROUP_CONCAT( dd.totalAverage ORDER BY dd.totalAverage DESC ) 
							FROM rms_grading_total AS dd 
							WHERE  dd.`gradingId`=$gradingId
						)
					) AS rank
			FROM 
				`rms_grading_total` AS grt
			WHERE 
				grt.gradingId =$gradingId
				AND grt.studentId =$studentId
		";
		$sql.=" LIMIT 1 ";
		
		return $db->fetchRow($sql);
	}
	
	function getTeachingSchedule($search=array()){
		$db=$this->getAdapter();
		
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
		$day = empty($search['day'])?0:$search['day'];
		$sql="
		SELECT 
			(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleKh
			,(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleEng
			,(SELECT te.teacher_name_kh FROM rms_teacher AS te WHERE te.id = schDetail.techer_id LIMIT 1 ) AS teaccherNameKh
			,(SELECT te.teacher_name_en FROM rms_teacher AS te WHERE te.id = schDetail.techer_id LIMIT 1 ) AS teaccherNameEng
			,(SELECT name_kh FROM rms_view WHERE rms_view.key_code=schDetail.day_id AND rms_view.type=18 LIMIT 1)AS daysKh
			,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.from_hour LIMIT 1) AS fromHourTitle
			,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.to_hour LIMIT 1) AS toHourTitle
			
			,(SELECT CONCAT(b.branch_nameen) FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchName
			,(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS schoolNameen
			,(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchLogo
			
			,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
			,`g`.`group_code` AS groupCode
			,`g`.`degree` AS degree_id
			,`g`.`grade` AS gradeId
			,(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)) AS `roomName`
			,(SELECT $degree FROM `rms_items`	WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1)  LIMIT 1) as degreeTitle
			,(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as gradeTitle
					
			,schDetail.*
		FROM 
			rms_group_reschedule AS schDetail
			,rms_group_schedule AS sch
			,rms_group AS g
		WHERE 
			sch.id =schDetail.main_schedule_id
			AND g.id =sch.group_id
			AND g.is_use =1
			AND g.is_pass =2
		";
		$sql.=" AND schDetail.techer_id=".$this->getUserExternalId();
		if(!empty($day)){
			$sql.=" AND schDetail.day_id=".$day;
		}
		
		$sql.=" ORDER BY schDetail.day_id ASC ,schDetail.from_hour ASC ";
		return $db->fetchAll($sql);
	}
	
	 function getCommentByDegree($degree){
		$db=$this->getAdapter();
		$sql="SELECT
					dc.comment_id AS id,
					c.comment AS name
				FROM
					rms_degree_comment as dc,
					rms_comment as c
				WHERE
					dc.comment_id = c.id
					and dc.degree_id = $degree
			";
		return $db->fetchAll($sql);
	}
	function getClassAssessmentById($assessmentID){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label = 'name_en';
		$branch = "branch_nameen";
		$month = "month_en";
		$subjectTitle='subject_titleen';
		if ($currentLang==1){
			$colunmname='title';
			$label = 'name_kh';
			$branch = "branch_namekh";
			$month = "month_kh";
			$subjectTitle='subject_titlekh';
		}
		$sql="SELECT 
				grd.*
				,(SELECT br.$branch FROM `rms_branch` AS br WHERE br.br_id=grd.branchId LIMIT 1) As branchName
				,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =grd.forType LIMIT 1) as forTypeTitle
				,CASE
					WHEN grd.forType = 2 THEN grd.forSemester
				ELSE (SELECT $month FROM `rms_month` WHERE id=grd.forMonth  LIMIT 1) 
				END AS forMonthTitle
				,g.group_code AS  groupCode
				,g.academic_year AS  academicYearId
				,g.grade AS  gradeId
				,g.degree AS  degreeId
				
				,(SELECT te.signature from rms_teacher AS te WHERE te.id = g.teacher_id LIMIT 1 ) AS mainTeacherSigature
				,(SELECT te.teacher_name_kh from rms_teacher AS te WHERE te.id = g.teacher_id LIMIT 1 ) AS mainTeaccherNameKh
				,(SELECT te.teacher_name_en from rms_teacher AS te WHERE te.id = g.teacher_id LIMIT 1 ) AS mainTeaccherNameEng
				
				,(SELECT te.signature from rms_teacher AS te WHERE te.id = grd.teacherId LIMIT 1 ) AS teacherSigature
				,(SELECT te.teacher_name_kh from rms_teacher AS te WHERE te.id = grd.teacherId LIMIT 1 ) AS teaccherNameKh
				,(SELECT te.teacher_name_en from rms_teacher AS te WHERE te.id = grd.teacherId LIMIT 1 ) AS teaccherNameEng
				
				
				,(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear
				,(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degreeTitle
				,(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
				,(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS sessionTitle
				,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName
		";
		
		$sql.=" FROM rms_studentassessment AS grd,
					rms_group AS g 
			WHERE grd.groupId=g.id  AND grd.inputOption=2 ";
		
		$where ='';
		$where.=' AND grd.teacherId='.$this->getUserExternalId();
		$where.=' AND grd.id='.$assessmentID;
		$where.=' LIMIT 1 ';

		
		return $db->fetchRow($sql.$where);
	}
	
	function getDays($type=1){
		$db = $this->getAdapter();
		$sql="SELECT 
			v.key_code AS id 
			,v.name_kh AS daysKh 
			,v.name_en AS daysEng 
		";
		$sql.=" FROM rms_view AS v ";
		$sql.=" WHERE 
				1 
				AND v.type=18
			";
		if($type==1){//Weekly
			$sql.=' AND v.key_code NOT IN (7) ';
		}else if($type==2){//Full Weekly
			$sql.=' AND v.key_code NOT IN (6,7) ';
		}else if($type==3){//Weekend
			$sql.=' AND v.key_code IN (6,7) ';
		}
		return $db->fetchAll($sql);
	}
	function getTimeTeachingByTeacher(){
		$db = $this->getAdapter();
		$sql="
		SELECT 
			schDetail.from_hour
			,schDetail.to_hour 
			,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.from_hour LIMIT 1) AS fromHourTitle
			,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.to_hour LIMIT 1) AS toHourTitle
			,CONCAT ((SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.from_hour LIMIT 1),' - ',(SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.to_hour LIMIT 1)) timeTitle
		FROM  
			rms_group_reschedule AS schDetail
			,rms_group_schedule AS sch
			,rms_group AS g
		WHERE 
			sch.id =schDetail.main_schedule_id
			AND g.id =sch.group_id 
		";
		$sql.=' AND schDetail.techer_id ='.$this->getUserExternalId();
		$sql.=" GROUP BY CONCAT (schDetail.from_hour,schDetail.to_hour) ";
		return $db->fetchAll($sql);
	}
	
	function getScheduleInfoDetail($data=array()){
		$db = $this->getAdapter();
		$fromHour = empty($data['fromHour'])?0:$data['fromHour'];
		$toHour = empty($data['toHour'])?0:$data['toHour'];
		$dayID = empty($data['dayID'])?0:$data['dayID'];
		
		
		
		$sql="
			SELECT 
			g.id
			,(SELECT CONCAT(b.branch_nameen) FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchName
			,(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS schoolNameen
			,(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchLogo
			
			,(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleKh
			,(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleEng
			,(SELECT te.teacher_name_kh FROM rms_teacher AS te WHERE te.id = schDetail.techer_id LIMIT 1 ) AS teaccherNameKh
			,(SELECT te.teacher_name_en FROM rms_teacher AS te WHERE te.id = schDetail.techer_id LIMIT 1 ) AS teaccherNameEng
			
			,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
			,`g`.`group_code` AS groupCode
			,`g`.`degree` AS degree_id
			,`g`.`grade` AS gradeId
			,(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)) AS `roomName`
			,(SELECT rms_items.title FROM `rms_items`	WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1)  LIMIT 1) as degreeTitle
			,(SELECT rms_items.title_en FROM `rms_items`	WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1)  LIMIT 1) as degreeTitleEng
			,(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as gradeTitle
			,(SELECT rms_itemsdetail.title_en FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as gradeTitleEng
			,schDetail.*
		";
		
		$sql.=" ";
		$sql.=" 
			FROM  
				rms_group_reschedule AS schDetail
				,rms_group_schedule AS sch
				,rms_group AS g
		";
		$sql.=" WHERE 
					sch.id =schDetail.main_schedule_id
					AND g.id =sch.group_id 
					AND g.status =1
					AND g.is_use =1
					AND g.is_pass =2
					AND schDetail.day_id =$dayID
					AND schDetail.from_hour ='$fromHour'
					AND schDetail.to_hour ='$toHour' ";
		$sql.=' AND schDetail.techer_id ='.$this->getUserExternalId();
		$sql.=" LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	
}