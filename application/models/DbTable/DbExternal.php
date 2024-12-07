<?php

class Application_Model_DbTable_DbExternal extends Zend_Db_Table_Abstract
{

	protected $_name = 'rms_teacher';

	public function checkSessionTeacherExpireBeforeSubmit()
	{
		$teacherId = $this->getUserExternalId();
		$teacherId = empty($teacherId) ? 0 : $teacherId;
		if (empty($teacherId)) {
			return false;
		} else {
			return true;
		}
	}
	function reloadPageTecherExpireSession()
	{
		$url = "/external";
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$msg = $tr->translate("Session Expire");
		echo '<script language="javascript">
		alert("' . $msg . '");		
		window.location = "' . Zend_Controller_Front::getInstance()->getBaseUrl() . $url . '";
		</script>';
	}
	public function userAuthenticateTeacher($username, $password)
	{
		$db_adapter = Application_Model_DbTable_DbUsers::getDefaultAdapter();
		$auth_adapter = new Zend_Auth_Adapter_DbTable($db_adapter);
		$auth_adapter->setTableName('rms_teacher') // table where users are stored
			->setIdentityColumn('user_name') // field name of user in the table
			->setCredentialColumn('password') // field name of password in the table
			->setCredentialTreatment('MD5(?) AND status=1 '); // optional if password has been hashed

		$auth_adapter->setIdentity($username); // set value of username field
		$auth_adapter->setCredential($password); // set value of password field
		//instantiate Zend_Auth class
		$auth = Zend_Auth::getInstance();

		$result = $auth->authenticate($auth_adapter);
		if ($result->isValid()) {
			return true;
		} else {
			return false;
		}
	}

	public function getTeacherInfo($username, $password)
	{
		$db = $this->getAdapter();
		if (!empty($username)) {
			$sql = " SELECT s.* FROM rms_teacher AS s WHERE 1 ";
			$sql .= " AND " . $db->quoteInto('s.user_name=?', $username);
			$sql .= " AND " . $db->quoteInto('s.password=?', md5($password));
			$row = $db->fetchRow($sql);
			if (!$row) return NULL;
			return $row;
		} else {
			return null;
		}
	}

	public function getCurrentTeacherInfo()
	{
		$db = $this->getAdapter();
		$sql = " SELECT 
				gsd.group_id,
				g.academic_year AS currentAcademic,
				s.* 
			FROM rms_teacher AS s 
					LEFT JOIN `rms_group_subject_detail` AS gsd 
						INNER JOIN  `rms_group`  AS g
						ON  gsd.group_id = g.id
					ON s.id = gsd.teacher
			WHERE 1 ";
		$sql .= " AND " . $db->quoteInto('s.id=?', $this->getUserExternalId());
		$sql .= " ORDER BY g.academic_year DESC ";
		$sql .= " LIMIT 1 ";
		$row = $db->fetchRow($sql);
		if (!$row) return NULL;
		return $row;
	}

	public static function getUserExternalId()
	{
		$zendRequest = new Zend_Controller_Request_Http();
		$userId = $zendRequest->getCookie(TEACHER_AUTH . 'userId');
		$userId = empty($userId) ? 0 : $userId;
		return $userId;
	}

	function changePassword($newpwd)
	{
		$_user_data = array(
			'password' => MD5($newpwd)
		);
		$currentTeacher = $this->getUserExternalId();
		$where = $this->getAdapter()->quoteInto('id=?', $currentTeacher);
		$this->_name = 'rms_teacher';
		return  $this->update($_user_data, $where);
	}

	function getAllMonth()
	{
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if ($lang == 1) { // khmer
			$month = "month_kh";
		} else { // English
			$month = "month_en";
		}
		$sql = "SELECT 
				id 
				,month_kh AS month_kh 
				,month_en AS month_en 
				,$month AS name 
			FROM rms_month 
			WHERE status=1 ";
		return $db->fetchAll($sql);
	}

	function coutingClassByUser($arrCondiction = array())
	{
		$db = $this->getAdapter();
		$sql = "
			SELECT COUNT(DISTINCT(gsjb.group_id))  
			";
		$sql .= " FROM `rms_group_subject_detail` AS gsjb,
					`rms_group` AS g 
				WHERE 
					g.id = gsjb.group_id 
					AND g.is_use=1
					  "; //

		$sql .= ' AND gsjb.teacher=' . $this->getUserExternalId();


		if (!empty($arrCondiction['classTypeFilter'])) {
			if ($arrCondiction['classTypeFilter'] == 1) {
				//Completed Class
				$sql .= " AND g.is_pass NOT IN (0,2) ";
			} elseif ($arrCondiction['classTypeFilter'] == 2) {
				//Active Class
				$sql .= " AND g.is_pass=2 ";
			}
		}

		return $db->fetchOne($sql);
	}

	function getAllClassByUser($search = array())
	{
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname = 'title_en';
		$label = "name_en";
		$branch = "branch_nameen";
		$langSubject = 'subject_titleen';
		if ($currentLang == 1) {
			$colunmname = 'title';
			$label = "name_kh";
			$branch = "branch_namekh";
			$langSubject = 'subject_titlekh';
		}
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$currentTeacher = $this->getUserExternalId();
		$sql = "
			SELECT 
				g.*
				,g.degree as degreeId
				,gsjb.subject_id as subjectId
				,gsjb.teacher as teacherId
				,(SELECT $branch FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) AS branchName
				,(SELECT b.branch_namekh FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) AS branchNameKh
				,(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) AS branchNameEn

				,(SELECT $langSubject FROM `rms_subject` AS sj WHERE sj.id = gsjb.subject_id LIMIT 1) AS subjectName

				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear	
				,(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) AS degree
				,(SELECT i.title FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) AS degreeTitle
				,(SELECT i.title_en FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) AS degreeTitleEng
				,(SELECT id.$colunmname FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) AS grade
				,(SELECT id.title FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) AS gradeTitle
				,(SELECT id.title_en FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) AS gradeTitleEng
				,(SELECT`rms_view`.$label FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`
				,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `roomName`
				,CASE
					WHEN g.teacher_id = $currentTeacher THEN '" . $tr->translate("MAINTEACHER") . "' 
				END AS mainTeacher
				,(SELECT te.teacher_name_kh FROM rms_teacher AS te WHERE te.id = g.teacher_id LIMIT 1 ) as mainTeaccher
				,(SELECT $label from rms_view where type=9 and key_code=g.is_pass) as groupStatus
				,(SELECT COUNT(gds.gd_id)  FROM `rms_group_detail_student` AS gds WHERE gds.group_id = g.id AND gds.is_maingrade=1 ) AS amountStudent
			
		";
		$sql .= " FROM 
					`rms_group_subject_detail` AS gsjb,
					`rms_group` AS g ";
		$where = ' WHERE 
					g.id = gsjb.group_id AND g.is_use=1
					 ';
		$where .= ' AND gsjb.teacher=' . $currentTeacher;

		if (!empty($search['adv_search'])) {
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]	= "	`g`.`group_code` LIKE '%{$s_search}%'";
			$s_where[]	= "	(SELECT b.branch_namekh FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]	= "	(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]	= " `g`.`semester` LIKE '%{$s_search}%'";

			$s_where[]	= "	(SELECT i.title FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]	= "	(SELECT i.title_en FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) LIKE '%{$s_search}%'";

			$s_where[] 	= "	(SELECT id.title FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] 	= "	(SELECT id.title_en FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) LIKE '%{$s_search}%'";

			$s_where[] 	= "	(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`)) LIKE '%{$s_search}%'";

			$where .= ' AND (' . implode(' OR ', $s_where) . ')';
		}
		if (!empty($search['academic_year'])) {
			$where .= ' AND g.academic_year=' . $search['academic_year'];
		}
		if (!empty($search['degree'])) {
			$where .= ' AND `g`.`degree`=' . $search['degree'];
		}
		if (!empty($search['grade'])) {
			$where .= ' AND g.grade=' . $search['grade'];
		}
		if (!empty($search['is_pass']) and $search['is_pass'] > -1) {
			$where .= ' AND g.is_pass=' . $search['is_pass'];
		}

		if (!empty($search['classTypeFilter'])) {
			if ($search['classTypeFilter'] == 1) {
				//Completed Class
				$where .= " AND g.is_pass NOT IN (0,2) ";
			} elseif ($search['classTypeFilter'] == 2) {
				//Active Class
				$where .= " AND g.is_pass=2 ";
			}
		}
		if (!empty($search['groupBy'])) {
			$where .= ' GROUP BY gsjb.group_id';
		} else {
			$where .= ' GROUP BY gsjb.group_id,gsjb.subject_id ';
		}
		$order =  ' ORDER BY `g`.`id` DESC ';
		if (!empty($search['limitedRecord'])) {
			$search['limitedRecord'] = empty($search['limitedRecord']) ? 0 : $search['limitedRecord'];
			$order .= ' LIMIT ' . $search['limitedRecord'];
		}
		return $db->fetchAll($sql . $where . $order);
	}



	public function getGroupDetailByIDExternal($id, $getTeacherId = null)
	{
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if ($lang == 1) { // khmer
			$label = "name_kh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
		} else { // English
			$label = "name_en";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
		}
		$sql = "SELECT
				   	`g`.*
				   	,(SELECT CONCAT(b.branch_nameen) FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branch_name
					,(SELECT b.branch_namekh FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) AS branchNameKh
					,(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) AS branchNameEn
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
		   			`g`.`id`=" . $id . " ";

		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql .= $dbp->getAccessPermission("g.branch_id");

		$request = Zend_Controller_Front::getInstance()->getRequest();
		$controllerName = $request->getControllerName();

		if ($controllerName == 'assessment') {
			$sql .= " AND g.teacher_id =" . $dbp->getTeacherUserId();
		}

		if ($getTeacherId != null) {
			$sql .= " AND (SELECT group_id FROM `rms_group_subject_detail` WHERE group_id=$id AND teacher=" . $_db->getTeacherUserId() . " LIMIT 1)";
		}

		$sql .= "  LIMIT 1 ";
		return $db->fetchRow($sql);
	}

	function getAllSubjectByGroupExternal($data)
	{
		$db = $this->getAdapter();

		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname = 'subject_titleen';
		if ($currentLang == 1) {
			$colunmname = 'subject_titlekh';
		}

		$groupId = empty($data['groupId']) ? 0 : $data['groupId'];
		$examType = empty($data['examType']) ? 0 : $data['examType'];

		$sql = "
		SELECT 
			gsjd.subject_id AS id
			,(SELECT CONCAT(sj.$colunmname,CASE WHEN subject_lang =1 THEN '(ខ្មែរ)' WHEN subject_lang =2 THEN '(English)' ELSE '' END) FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS name
		FROM 
			rms_group_subject_detail AS gsjd ,
			rms_group as g
		WHERE 
			g.id = gsjd.group_id
			and gsjd.group_id = " . $groupId;
		$sql .= ' AND gsjd.teacher=' . $this->getUserExternalId();
		if ($examType == 1) { //for month
			$sql .= " AND gsjd.amount_subject >0 ";
		} else { //for semester
			$sql .= " AND gsjd.amount_subject_sem >0 ";
		}

		$sql .= " ORDER BY (SELECT sj.subject_lang FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) ASC ";
		return $db->fetchAll($sql);
	}
	function getSubjectGroupInfoExternal($data)
	{
		$db = $this->getAdapter();
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname = 'subject_titleen';
		if ($currentLang == 1) {
			$colunmname = 'subject_titlekh';
		}

		$groupId = empty($data['groupId']) ? 0 : $data['groupId'];
		$examType = empty($data['examType']) ? 0 : $data['examType'];
		$subjectId = empty($data['subjectId']) ? 0 : $data['subjectId'];
		$strMaxScore = 'max_score';
		if ($examType == 2) {
			$strMaxScore = 'semester_max_score';
		}
		// 		gsjd.*
		// 		,(SELECT sj.parent FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS parent
		// 		,(SELECT sj.is_parent FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS isParent
		// 		,(SELECT sj.shortcut FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS shortcut
		// 		,(gsjd.amount_subject) amtsubjectMonth
		// 		,(gsjd.amount_subject_sem) amtsubjectSemester
		// 		,(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS subjectTitleKh
		// 		,(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS subjectTitleEn
		// 		,(SELECT sj.$colunmname FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS name,
		// 		,g.amount_subject AS amountSubjectDivide

		$sql = "
			SELECT 
				gsjd.$strMaxScore AS maxSubjectscore
			FROM 
		 		rms_group_subject_detail AS gsjd ,
		 		rms_group as g
			WHERE 
				g.id = gsjd.group_id
				and gsjd.group_id = " . $groupId;
		$sql .= " AND gsjd.subject_id =" . $subjectId;

		$sql .= ' ORDER BY gsjd.id ASC LIMIT 1';
		return $db->fetchRow($sql);
	}

	public function getStudentListByGroup($search)
	{
		$session_lang = new Zend_Session_Namespace('lang');
		$lang_id = $session_lang->lang_id;
		$viewColumn = 'name_en';
		$gender_str = 'name_en';
		$str_village = 'village_name';
		$str_commune = 'commune_name';
		$str_district = 'district_name';
		$str_province = 'province_en_name';
		$occuTitle = 'occu_enname';
		if ($lang_id == 1) { //for kh
			$viewColumn = 'name_kh';
			$gender_str = 'name_kh';
			$str_village = 'village_namekh';
			$str_commune = 'commune_namekh';
			$str_district = 'district_namekh';
			$str_province = 'province_kh_name';
			$occuTitle = 'occu_name';
		}
		$db = $this->getAdapter();
		$sql = "
			SELECT
					 g.gd_id
					,gr.branch_id AS branchId
					,(SELECT CONCAT(b.branch_nameen) FROM rms_branch as b WHERE b.br_id=`gr`.branch_id LIMIT 1) AS branch_name
					,(SELECT b.branch_namekh FROM `rms_branch` AS b  WHERE b.br_id = gr.branch_id LIMIT 1) AS branchNameKh
					,(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = gr.branch_id LIMIT 1) AS branchNameEn
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
					
					,fam.fatherNameKh AS father_khname 
					,fam.fatherName AS father_name  
					,fam.fatherNation AS father_nation
					,fam.fatherPhone AS father_phone
					
					,fam.motherNameKh AS mother_khname 
					,fam.motherName AS mother_name  
					,fam.motherPhone AS mother_phone  
					
					,fam.guardianNameKh AS guardian_khname 
					,fam.guardianName AS guardian_enname 
					,fam.guardianPhone AS guardian_tel
					
				    
				    ,(SELECT v." . $viewColumn . " FROM rms_view AS v WHERE v.type=21 and v.key_code=`s`.`nationality` LIMIT 1) AS nationality
    				,(SELECT v." . $viewColumn . " FROM rms_view AS v WHERE v.type=21 and v.key_code=`s`.`nation` LIMIT 1) AS nation
					,(SELECT occ." . $occuTitle . " FROM `rms_occupation` AS occ WHERE occ.occupation_id = fam.fatherJob LIMIT 1) AS father_job
					,(SELECT occ." . $occuTitle . " FROM `rms_occupation` AS occ WHERE occ.occupation_id = fam.motherJob LIMIT 1) AS mother_job
				    
					,(SELECT `rms_view`.$gender_str FROM `rms_view` WHERE ((`rms_view`.`type` = 2) AND (`rms_view`.`key_code` = `s`.`sex`)) LIMIT 1) AS `sex`
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
				rms_student as s JOIN `rms_group_detail_student` AS g ON g.itemType=1 AND g.stu_id = s.stu_id AND `g`.`status` = 1
				LEFT JOIN `rms_group` AS gr ON gr.id = g.group_id
				LEFT JOIN rms_family AS fam ON fam.id = s.familyId
			WHERE 1
				
				 ";
		$groupId = empty($search['group_id']) ? 0 : $search['group_id'];
		if (!empty($groupId)) {
			$sql .= ' AND g.group_id=' . $groupId;
		}
		//$sql.=' AND gr.teacher_id='.$this->getUserExternalId();

		$stuOrderBy = empty($search['stuOrderBy']) ? 0 : $search['stuOrderBy'];

		$order = ' ORDER BY s.stu_khname ASC ';
		if ($stuOrderBy == 1) {
			$order = " ORDER BY CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) ASC ";
		}
		return $db->fetchAll($sql . $order);
	}


	function getGradingCriteriaItems($data)
	{
		$db = $this->getAdapter();
		$gradingId = empty($data['gradingId']) ? 0 : $data['gradingId'];
		$subjectId = empty($data['subjectId']) ? 0 : $data['subjectId'];

		$sql = "
			SELECT 
				s.*
				,(SELECT cri.criteriaType FROM `rms_exametypeeng` cri WHERE cri.id= s.criteriaId LIMIT 1) criteriaType
				,(SELECT es.title FROM `rms_exametypeeng` AS es WHERE es.id = s.criteriaId LIMIT 1) AS criterialTitle 
				,(SELECT es.title_en FROM `rms_exametypeeng` AS es WHERE es.id = s.criteriaId LIMIT 1) AS criterialTitleEng 
			FROM 
				`rms_scoreengsettingdetail` AS s 
			WHERE s.score_setting_id=$gradingId 
			AND s.subjectId =$subjectId
		";
		if (!empty($data['examType'])) {
			$sql .= " AND s.forExamType =" . $data['examType'];
		}
		if (!empty($data['criteriaId'])) {
			$sql .= " AND s.criteriaId =" . $data['criteriaId'];
		}

		$sql .= " AND s.isNotEnteryCri = CASE 
					WHEN COALESCE((SELECT sttDi.isNotEnteryCri FROM `rms_scoreengsettingdetail` AS sttDi WHERE sttDi.score_setting_id =s.score_setting_id 
			AND sttDi.subjectId =  " . $subjectId . " ORDER BY sttDi.isNotEnteryCri DESC LIMIT 1 ),'0') =1 
						THEN '1' 
					ELSE '0'
			END  ";

		$sql .= " ORDER BY criteriaType ASC, s.criteriaId ASC";
		$row = $db->fetchRow($sql);

		$sql = "
			SELECT 
				s.*
				,(SELECT cri.criteriaType FROM `rms_exametypeeng` cri WHERE cri.id= s.criteriaId LIMIT 1) criteriaType
				,(SELECT es.title FROM `rms_exametypeeng` AS es WHERE es.id = s.criteriaId LIMIT 1) AS criterialTitle 
				,(SELECT es.title_en FROM `rms_exametypeeng` AS es WHERE es.id = s.criteriaId LIMIT 1) AS criterialTitleEng 
			FROM `rms_scoreengsettingdetail` AS s 
			WHERE s.score_setting_id=$gradingId 
			AND s.subjectId =0
		";
		if (!empty($row)) {
			$sql .= " AND s.criteriaId !=" . $row['criteriaId'];
		}
		if (!empty($data['examType'])) {
			$sql .= " AND s.forExamType =" . $data['examType'];
		}
		if (!empty($data['criteriaId'])) {
			$sql .= " AND s.criteriaId =" . $data['criteriaId'];
		}

		$sql .= "	AND s.isNotEnteryCri = CASE 
					WHEN COALESCE((SELECT sttDi.isNotEnteryCri FROM `rms_scoreengsettingdetail` AS sttDi WHERE sttDi.score_setting_id =s.score_setting_id 
			AND sttDi.subjectId =  " . $subjectId . " ORDER BY sttDi.isNotEnteryCri DESC LIMIT 1 ),'0') =1 
						THEN '1' 
					ELSE '0'
			END  ";

		$sql .= " ORDER BY criteriaType ASC, s.criteriaId ASC ";
		//	echo 	$sql; exit();
		$rRow = $db->fetchAll($sql);

		if (!empty($row)) {
			array_unshift($rRow, $row);
			asort($rRow);
		}
		return $rRow;
	}

	function getClassSubjectScoreById($gradingId, $fullControlID)
	{
		$db = $this->getAdapter();

		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname = 'title_en';
		$label = 'name_en';
		$branch = "branch_nameen";
		$month = "month_en";
		$subjectTitle = 'subject_titleen';
		if ($currentLang == 1) {
			$colunmname = 'title';
			$label = 'name_kh';
			$branch = "branch_namekh";
			$month = "month_kh";
			$subjectTitle = 'subject_titlekh';
		}
		$criterialList ='
			
			CONCAT(
				"[",
					GROUP_CONCAT(
						CONCAT(
							"{",
							"\"criteriaId\":",
							vs.criteriaId,
							",",
							"\"criteriaType\":",
							vs.criteriaType,
							",",
							"\"criterialTitle\":\"",
							vs.criterialTitle,
							"\",",
							"\"criterialTitleEng\":\"",
							vs.criterialTitleEn,
							"\"",
							"}"
							)
							ORDER BY vs.criteriaType,
							vs.criteriaId ASC
						),
					"]"
				) AS criterialList
		';
		$sql = "SELECT 
				grd.*
				,(SELECT br.$branch FROM `rms_branch` AS br WHERE br.br_id=grd.branchId LIMIT 1) As branchName
				,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameKh
				,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameEn
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
				
				,CASE
					WHEN grd.examType = 2 THEN (SELECT gsjd.semester_max_score FROM rms_group_subject_detail AS gsjd WHERE g.id = gsjd.group_id AND gsjd.subject_id =grd.subjectId LIMIT 1 )
					ELSE (SELECT gsjd.max_score FROM rms_group_subject_detail AS gsjd WHERE g.id = gsjd.group_id AND gsjd.subject_id =grd.subjectId LIMIT 1 )
				END AS maxSubjectscore
				
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
				,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName,
				$criterialList
		";

		$sql .= " FROM  rms_grading AS grd
					LEFT JOIN rms_group AS g ON  grd.groupId = g.id
					LEFT JOIN `vapi_criteria_setting` AS vs  ON  vs.score_setting_id = grd.gradingSettingId
			WHERE  grd.inputOption=2 ";

		$where = '';
		if (empty($fullControlID)) {
			$where .= ' AND grd.teacherId=' . $this->getUserExternalId();
		}
		$where .= ' AND grd.id=' . $gradingId;
		$where .= ' LIMIT 1 ';

		return $db->fetchRow($sql . $where);
	}

	function getSubjectScoreByGroup($data)
	{

		$strSubjectLange = " (SELECT subject_lang FROM `rms_subject` s WHERE
		s.id=sd.subject_id LIMIT 1) ";

		$db = $this->getAdapter();
		$sql = "SELECT
			sd.*, 
			(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = sd.subject_id LIMIT 1) AS sub_name,
			(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = sd.subject_id LIMIT 1) AS sub_name_en,
			(SELECT t.teacher_name_kh FROM `rms_teacher` AS t WHERE t.id = sd.teacher LIMIT 1) AS teacher_name_kh,
			(SELECT t.teacher_name_en FROM `rms_teacher` AS t WHERE t.id = sd.teacher LIMIT 1) AS teacher_name_en,
			$strSubjectLange AS subjectLang,
			(SELECT g.gradingId FROM `rms_group` AS g WHERE g.id = sd.group_id LIMIT 1) AS gradingId
			FROM
			rms_group_subject_detail AS sd   WHERE sd.`group_id` = " . $data['groupId'];
		$sql .= " ORDER  BY $strSubjectLange ";
		$subjectDetail = $db->fetchAll($sql);
		return $subjectDetail;

		// $results = array();
		// if(!empty($subjectDetail)){
		// 	foreach($subjectDetail as $key=>$rs){
		// 		$results[$key]['teacher'] = $rs['teacher'];
		// 		$results[$key]['subjectId'] = $rs['subject_id'];
		// 		$results[$key]['sub_name'] = $rs['sub_name'];
		// 		$results[$key]['sub_name_en'] = $rs['sub_name_en'];
		// 		$results[$key]['teacher_name_kh'] = $rs['teacher_name_kh'];
		// 		$results[$key]['teacher_name_en'] = $rs['teacher_name_en'];
		// 		$results[$key]['subjectLang'] = $rs['subjectLang'];
		// 		$data['subjectId']=$rs['subject_id'];
		// 		$rsGrading=$this->getGradingScoreData($data);
		// 		$results[$key]['gradingScore']=$rsGrading['totalAverage'];
		// 		$results[$key]['gradingId']=$rsGrading['gradingId'];
		// 		$results[$key]['totalCriteria']=$rsGrading['totalCriteria'];
		// 	}
		// }
		// return $results ;

	}


	function getStudentByGroupExternal($data = array())
	{
		$db = $this->getAdapter();

		$groupId = empty($data['groupId']) ? 0 : $data['groupId'];
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$studentName = "CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,''))";
		$studentEnName = $studentName;

		if ($currentLang == 1) {
			$studentName = 's.stu_khname';
		}
		
		$queryStatmentForIssueAttendance="";
		$queryCondictionForIssueAttendance="";
		if (!empty($data['forIssueAttendance'])) {
			
			$date = new DateTime();
			if(!empty($data['attendenceDate'])){
				$date = new DateTime($data['attendenceDate']);
			}
			$attendenceDate =  $date->format("Y-m-d");
			if(!empty($data["attendanceId"])){
				$attendanceId = empty($data["attendanceId"]) ? 0 : $data["attendanceId"];
				$subjectId = empty($data["subjectId"]) ? 0 : $data["subjectId"];
				$fromHour = empty($data["fromHour"]) ? 0 : $data["fromHour"];
				$toHour = empty($data["toHour"]) ? 0 : $data["toHour"];

				$queryStatmentForIssueAttendance=",COALESCE((SELECT attd.id FROM `rms_student_attendence` AS att, `rms_student_attendence_detail` AS attd WHERE att.id = $attendanceId AND att.id = attd.attendence_id  AND sgh.stu_id =attd.stu_id  AND attd.subjectId = $subjectId AND attd.toHour = $toHour  AND attd.fromHour =  $fromHour AND   att.date_attendence = DATE_FORMAT('$attendenceDate', '%Y/%m/%d') LIMIT 1),0) AS detailIdAtt";
				$queryStatmentForIssueAttendance.=",COALESCE((SELECT attd.attendence_status FROM `rms_student_attendence` AS att, `rms_student_attendence_detail` AS attd WHERE att.id = $attendanceId AND att.id = attd.attendence_id  AND sgh.stu_id =attd.stu_id  AND attd.subjectId = $subjectId AND attd.toHour = $toHour  AND attd.fromHour =  $fromHour AND   att.date_attendence = DATE_FORMAT('$attendenceDate', '%Y/%m/%d') LIMIT 1),0) AS attendenceStatus";
				$queryStatmentForIssueAttendance.=",COALESCE((SELECT attd.description FROM `rms_student_attendence` AS att, `rms_student_attendence_detail` AS attd WHERE att.id = $attendanceId AND att.id = attd.attendence_id  AND sgh.stu_id =attd.stu_id  AND attd.subjectId = $subjectId AND attd.toHour = $toHour  AND attd.fromHour =  $fromHour AND   att.date_attendence = DATE_FORMAT('$attendenceDate', '%Y/%m/%d') LIMIT 1),'') AS reason";
				$queryStatmentForIssueAttendance.=",'0' AS permissionRecordId";
			}else if(!empty($data["thisAttendanceId"])){
				$attendanceId = empty($data["thisAttendanceId"]) ? 0 : $data["thisAttendanceId"];
				$queryStatmentForIssueAttendance=",COALESCE((SELECT attd.attendence_status FROM `rms_student_attendence` AS att, `rms_student_attendence_detail` AS attd WHERE att.id = $attendanceId AND att.id = attd.attendence_id  AND sgh.stu_id =attd.stu_id ORDER BY attd.id DESC LIMIT 1),0) AS attendenceStatus";
				$queryStatmentForIssueAttendance.=",COALESCE((SELECT attd.description FROM `rms_student_attendence` AS att, `rms_student_attendence_detail` AS attd WHERE att.id = $attendanceId AND att.id = attd.attendence_id  AND sgh.stu_id =attd.stu_id ORDER BY attd.id DESC LIMIT 1),0) AS reason";
				$queryStatmentForIssueAttendance.=",'0' AS permissionRecordId";
			}else{
				$queryStatmentForIssueAttendance= "
					,COALESCE(attD.attendence_status,'') as attendenceStatus
					,COALESCE(attD.description,'') as reason
					,COALESCE(attD.id,'0') AS permissionRecordId
				";
				$queryCondictionForIssueAttendance= " LEFT JOIN rms_student_attendence_detail AS attD ON attD.type=2 AND sgh.stu_id = attD.stu_id AND attD.attendanceDate ='".$attendenceDate."' ";
			}
			
		}

		$sql = "
				SELECT
					sgh.`stu_id`
					,sgh.`stu_id` AS studentId
					,(SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS stuCode
					,(SELECT " . $studentName . " FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS stu_name
					,(SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS stuKhName
					,(SELECT " . $studentEnName . " FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS stuEnName
					,(SELECT s.sex FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS sex
					,(SELECT s.sex FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) AS gender
					,(SELECT teacherComment FROM `rms_studentassessment_detail` WHERE teacherComment!='' AND studentId=sgh.`stu_id` ORDER BY id DESC LIMIT 1) AS teacherComment
					
				 ";
			$sql .= $queryStatmentForIssueAttendance;
		$sql .= "";
		$sql .= " FROM 
					`rms_group_detail_student` AS sgh";
			$sql .= $queryCondictionForIssueAttendance;
			
		if (!empty($data['forScoreSubject'])) {
			$sql .= "
				LEFT JOIN rms_grading_total AS gradingTotal 
					INNER JOIN `rms_grading` AS grading 
					ON grading.id = gradingTotal.gradingId
				ON gradingTotal.studentId = sgh.`stu_id` 
				AND grading.groupId=sgh.`group_id` 
				AND grading.subjectId=" . $data['subjectId'];
			$sql .= "	AND grading.examType=" . $data['examType'];
			if ($data['examType'] == 1) {
				$sql .= " AND grading.forMonth=" . $data['forMonth'];
			} else {
				$sql .= " AND grading.forSemester=" . $data['forSemester'];
			}
		}
		$sql .= " WHERE 
					sgh.itemType=1 
					AND sgh.stop_type=0
					and sgh.`group_id` =" . $groupId; //
		$order = " ORDER BY (SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) ASC ";
		if (!empty($data['sortStundent'])) {
			if ($data['sortStundent'] == 1) {
				$order = " ORDER BY (SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) ASC ";
			} else if ($data['sortStundent'] == 2) {
				$order = " ORDER BY (SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) ASC ";
			} else if ($data['sortStundent'] == 3) {
				$order = " ORDER BY (SELECT " . $studentEnName . " FROM `rms_student` AS s WHERE s.stu_id = sgh.`stu_id` LIMIT 1) ASC ";
			}
		}
		if (!empty($data['forScoreSubject'])) {
			$order = " ORDER BY gradingTotal.totalAverage DESC";
		}
		return $db->fetchAll($sql . $order);
	}

	function getScoreByCriterial($gradingId, $studentId, $criteriaId)
	{
		$db = $this->getAdapter();
		$sql = "
			SELECT 
				grd.*,
				(SELECT g.dateInput FROM `rms_grading` AS g WHERE grd.`gradingId`=g.id LIMIT 1 ) AS inputDate
			FROM 
				`rms_grading_detail` AS grd
			WHERE 1";
		if (!empty($gradingId)) {
			$sql .= " AND grd.gradingId =" . $gradingId;
		}
		if (!empty($studentId)) {
			$sql .= " AND grd.studentId=" . $studentId;
		}
		if (!empty($criteriaId)) {
			$sql .= " AND grd.criteriaId=" . $criteriaId;
		}
		return $db->fetchAll($sql);
	}
	function getGradingByCriterial($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT
			grd.*, gt.`criteriaId`, gt.dateInput
		FROM
			`rms_grading_detail_tmp` AS grd
			 INNER JOIN `rms_grading_tmp` AS gt ON grd.`gradingId`=gt.`id`
		WHERE 1 ";
		if (!empty($data['studentId'])) {
			$sql .= " AND grd.studentId=" . $data['studentId'];
		}
		if (!empty($data['gradingRowId'])) {
			$sql .= " AND grd.gradingId=" . $data['gradingRowId'];
		}
		if (!empty($data['criteriaId'])) {
			$sql .= " AND gt.`criteriaId`=" . $data['criteriaId'];
		}
		if (!empty($data['subjectId'])) {
			$sql .= " AND gt.`subjectId`=" . $data['subjectId'];
		}
		if (!empty($data['groupId'])) {
			$sql .= " AND gt.`groupId`=" . $data['groupId'];
		}
		if (!empty($data['forMonth'])) {
			$sql .= " AND gt.`forMonth`=" . $data['forMonth'];
		}
		if (!empty($data['examType'])) {
			$sql .= " AND gt.`examType`=" . $data['examType'];
		}
		$order = " ORDER BY gt.dateInput ASC ";
		//echo $sql;
		return $db->fetchAll($sql . $order);
	}

	function getAllGradingByCriterial($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT
			grd.*, gt.`criteriaId`, gt.dateInput
		FROM
			`rms_grading_detail_tmp` AS grd
			 INNER JOIN `rms_grading_tmp` AS gt ON grd.`gradingId`=gt.`id`
		WHERE 1 ";
		if (!empty($data['studentId'])) {
			$sql .= " AND grd.studentId=" . $data['studentId'];
		}
		if (!empty($data['gradingRowId'])) {
			$sql .= " AND grd.gradingId=" . $data['gradingRowId'];
		}
		if (!empty($data['criteriaId'])) {
			$sql .= " AND gt.`criteriaId`=" . $data['criteriaId'];
		}
		if (!empty($data['subjectId'])) {
			$sql .= " AND gt.`subjectId`=" . $data['subjectId'];
		}
		if (!empty($data['groupId'])) {
			$sql .= " AND gt.`groupId`=" . $data['groupId'];
		}
		if (!empty($data['forMonth'])) {
			$sql .= " AND gt.`forMonth`=" . $data['forMonth'];
		}
		if (!empty($data['examType'])) {
			$sql .= " AND gt.`examType`=" . $data['examType'];
		}
		$order = " ORDER BY gt.dateInput ASC ";
		//echo $sql;
		return $db->fetchAll($sql . $order);
	}
	function countInputCriterial($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT id,  dateInput FROM  rms_grading_tmp WHERE groupId= " . $data['groupId'] . " AND subjectId=" . $data['subjectId'] . " AND criteriaId=" . $data['criteriaId'];
		if (!empty($data['examType'])) {
			$sql .= " AND examType =" . $data['examType'];
		}
		if (!empty($data['forMonth'])) {
			$sql .= " AND forMonth =" . $data['forMonth'];
		}
		$order = " ORDER BY dateInput ASC ";
		return $db->fetchAll($sql . $order);
	}

	function countGradingInput($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT id,  dateInput FROM  rms_grading_tmp WHERE groupId= " . $data['groupId'] . " AND subjectId=" . $data['subjectId'] . " AND criteriaId=" . $data['criteriaId'];
		if (!empty($data['examType'])) {
			$sql .= " AND examType =" . $data['examType'];
		}
		if (!empty($data['forMonth'])) {
			$sql .= " AND forMonth =" . $data['forMonth'];
		}
		$order = " ORDER BY dateInput ASC ";
		return $db->fetchAll($sql . $order);
	}
	function getAverageAndRankBySubjectOfCriterial($gradingId, $studentId)
	{
		$db = $this->getAdapter();
		$sql = "
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
		$sql .= " LIMIT 1 ";

		return $db->fetchRow($sql);
	}

	function getTeachingSchedule($search = array())
	{
		$db = $this->getAdapter();

		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();

		$label = "name_en";
		$grade = "rms_itemsdetail.title_en";
		$degree = "rms_items.title_en";
		$branchName = "branch_nameen";
		$subjectTitle = "subject_titleen";

		if ($lang == 1) { // khmer
			$subjectTitle = "subject_titlekh";
			$branchName = "branch_namekh";
			$label = "name_kh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
		}
		$day = empty($search['day']) ? 0 : $search['day'];
		$sql = "
		SELECT 
			(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleKh
			,(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleEng
			,(SELECT sj." . $subjectTitle . " FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitle
			,(SELECT te.teacher_name_kh FROM rms_teacher AS te WHERE te.id = schDetail.techer_id LIMIT 1 ) AS teaccherNameKh
			,(SELECT te.teacher_name_en FROM rms_teacher AS te WHERE te.id = schDetail.techer_id LIMIT 1 ) AS teaccherNameEng
			,(SELECT name_kh FROM rms_view WHERE rms_view.key_code=schDetail.day_id AND rms_view.type=18 LIMIT 1)AS daysKh
			,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.from_hour LIMIT 1) AS fromHourTitle
			,(SELECT t.title FROM rms_timeseting AS t WHERE t.value =schDetail.to_hour LIMIT 1) AS toHourTitle
			
			,(SELECT b." . $branchName . " FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchName
			,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameKh
			,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameEn
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
		$sql .= " AND schDetail.techer_id=" . $this->getUserExternalId();
		if (!empty($day)) {
			$sql .= " AND schDetail.day_id=" . $day;
		}

		$sql .= " ORDER BY schDetail.day_id ASC ,schDetail.from_hour ASC ";
		return $db->fetchAll($sql);
	}

	function getCommentByDegree($degree)
	{
		$db = $this->getAdapter();
		$sql = "SELECT
					dc.comment_id AS id,
					c.comment AS name,
					(SELECT CONCAT(name_kh,' ',name_en) FROM `rms_view` WHERE key_code=c.commentType AND type=36 LIMIT 1) AS commentType
				FROM
					rms_degree_comment as dc,
					rms_comment as c
				WHERE
					dc.comment_id = c.id
					and dc.degree_id = $degree
			ORDER BY commentType DESC , c.id ASC ";
		return $db->fetchAll($sql);
	}
	function getClassAssessmentById($assessmentID, $fullControlID)
	{
		$db = $this->getAdapter();

		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname = 'title_en';
		$label = 'name_en';
		$branch = "branch_nameen";
		$month = "month_en";
		$subjectTitle = 'subject_titleen';
		if ($currentLang == 1) {
			$colunmname = 'title';
			$label = 'name_kh';
			$branch = "branch_namekh";
			$month = "month_kh";
			$subjectTitle = 'subject_titlekh';
		}
		$sql = "SELECT 
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

		$sql .= " FROM rms_studentassessment AS grd,
					rms_group AS g 
			WHERE grd.groupId=g.id  AND grd.inputOption=2 ";

		$where = '';
		if (empty($fullControlID)) {
			$where .= ' AND grd.teacherId=' . $this->getUserExternalId();
		}
		$where .= ' AND grd.id=' . $assessmentID;
		$where .= ' LIMIT 1 ';


		return $db->fetchRow($sql . $where);
	}

	function getDays($type = 1)
	{
		defined('STUDY_DAY_SETTING') || define('STUDY_DAY_SETTING', Setting_Model_DbTable_DbGeneral::geValueByKeyName('studyday_schedule'));
		$db = $this->getAdapter();
		$sql = "SELECT 
			v.key_code AS id 
			,v.name_kh AS daysKh 
			,v.name_en AS daysEng 
		";
		$sql .= " FROM rms_view AS v ";
		$sql .= " WHERE 
				1 
				AND v.type=18
			";
		if (STUDY_DAY_SETTING == 1) { //Weekly
			$sql .= ' AND v.key_code NOT IN (7) ';
		} else if (STUDY_DAY_SETTING == 2) { //Full Weekly
			$sql .= ' AND v.key_code NOT IN (6,7) ';
		} else if (STUDY_DAY_SETTING == 3) { //Weekend
			$sql .= ' AND v.key_code IN (6,7) ';
		}
		return $db->fetchAll($sql);
	}
	function getTimeTeachingByTeacher($data = null)
	{
		$db = $this->getAdapter();
		$getUserExternalId = $this->getUserExternalId();
		$sql = "
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
		if (!empty($data['yearId'])) {
			$sql .= ' AND schDetail.year_id =' . $data['yearId'];
		}
		if (!empty($data['schSettingId'])) {
			$sql .= ' AND sch.schedule_setting =' . $data['schSettingId'];
		} else {
			$sql .= ' AND schDetail.techer_id =' . $getUserExternalId;
		}
		$sql .= " GROUP BY CONCAT (schDetail.from_hour,schDetail.to_hour) ";
		$sql .= " ORDER BY schDetail.from_hour ASC ";
		return $db->fetchAll($sql);
	}

	function getScheduleInfoDetail($data = array())
	{
		$db = $this->getAdapter();
		$fromHour = empty($data['fromHour']) ? 0 : $data['fromHour'];
		$toHour = empty($data['toHour']) ? 0 : $data['toHour'];
		$dayID = empty($data['dayID']) ? 0 : $data['dayID'];
		$techerLogId = $this->getUserExternalId();
		$teacherId = empty($data['teacherId']) ? $techerLogId : $data['teacherId'];

		$sql = "
			SELECT 
			g.id
			,(SELECT CONCAT(b.branch_nameen) FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchName
			,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameKh
			,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameEn
			,(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS schoolNameen
			,(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchLogo
			
			,(SELECT sj.subject_titlekh FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleKh
			,(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjectTitleEng
			,(SELECT sj.subject_lang FROM `rms_subject` AS sj WHERE sj.id = schDetail.subject_id LIMIT 1) AS subjecLang
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

		$sql .= " ";
		$sql .= " 
			FROM  
				rms_group_reschedule AS schDetail
				,rms_group_schedule AS sch
				,rms_group AS g
		";
		$sql .= " WHERE 
					sch.id =schDetail.main_schedule_id
					AND g.id =sch.group_id 
					AND g.status =1
					AND g.is_use =1
					AND g.is_pass =2
					AND schDetail.day_id =$dayID
					AND schDetail.from_hour =$fromHour
					AND schDetail.to_hour =$toHour ";
		$sql .= ' AND schDetail.techer_id =' . $teacherId;
		if (!empty($data['yearId'])) {
			$sql .= ' AND schDetail.year_id =' . $data['yearId'];
		}
		$sql .= " LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getAttScoreSetting($gradingId)
	{

		$db = $this->getAdapter();
		$sql = " SELECT settingScoreAttId FROM `rms_scoreengsetting` WHERE id=" . $gradingId;
		$attSettingId = $db->fetchOne($sql);

		if (!empty($attSettingId)) {
			$sql = "SELECT
					attendanceType,
					scoreDeduct
				FROM `rms_attendance_score_setting_detail`
					WHERE settingId=" . $attSettingId;
			return $db->fetchAll($sql);
		} else {
			return null;
		}
	}
	function getSettingEntryById($Id)
	{

		$db = $this->getAdapter();
		$sql = " SELECT * FROM `rms_score_entry_setting` WHERE id=" . $Id;
		return $db->fetchRow($sql);
	}

	function calculateScoreByAtt($stuId, $data, $attSettingResult)
	{
		$data['settingEntryId'] =  empty($data['settingEntryId']) ? 0 : $data['settingEntryId'];
		$attResult = $this->getSettingEntryById($data['settingEntryId']);

		$sql = "SELECT
			sad.`stu_id`,
			sad.attendence_status,
			COUNT(IF(sad.attendence_status = '2' , sad.attendence_status, NULL)) AS totalA,
			COUNT(IF(sad.attendence_status = '3' , sad.attendence_status, NULL)) AS totalP,
			COUNT(IF(sad.attendence_status = '4' , sad.attendence_status, NULL)) AS totalLate,
			COUNT(IF(sad.attendence_status = '5' , sad.attendence_status, NULL)) AS totalLeave
		FROM
			`rms_student_attendence` AS  sa INNER JOIN 
			`rms_student_attendence_detail` AS sad
			
		ON sa.id=sad.`attendence_id`  WHERE 1 ";

		if (!empty($data['groupId'])) {
			$sql .= " AND sa.`group_id`= " . $data['groupId'];
		}
		if (!empty($data['subjectId'])) {
			$sql .= " AND sad.`subjectId`= " . $data['subjectId'];
		}
		if (!empty($stuId)) {
			$sql .= " AND sad.`stu_id`= " . $stuId;
		}
		$from_date = (empty($attResult['fromDate'])) ? '1' : "sa.date_attendence >= '" . $attResult['fromDate'] . " 00:00:00'";
		$to_date = (empty($attResult['endDate'])) ? '1' : "sa.date_attendence <= '" . $attResult['endDate'] . " 23:59:59'";
		$where = " AND " . $from_date . " AND " . $to_date;

		$groupBy = " GROUP BY sad.attendence_status , sad.stu_id ";
		$result = $this->getAdapter()->fetchRow($sql . $where . $groupBy);

		$reductPercent = 0;
		if (!empty($attSettingResult)) foreach ($attSettingResult as $rs) {
			if ($rs['attendanceType'] == 2 and $result['totalA'] > 0) {
				$reductPercent = $reductPercent + ($result['totalA'] * $rs['scoreDeduct']);
			} elseif ($rs['attendanceType'] == 3 and $result['totalP'] > 0) {
				$reductPercent = $reductPercent + ($result['totalP'] * $rs['scoreDeduct']);
			} elseif ($rs['attendanceType'] == 4 and $result['totalLate'] > 0) {
				$reductPercent = $reductPercent + ($result['totalLate'] * $rs['scoreDeduct']);
			} elseif ($rs['attendanceType'] == 5 and $result['totalLeave'] > 0) {
				$reductPercent = $reductPercent + ($result['totalLeave'] * $rs['scoreDeduct']);
			}
		}
		return $reductPercent;
	}
	function getLatestScoreByGroup($groupId)
	{
		$sql = "SELECT id FROM `rms_score` WHERE group_id=$groupId ORDER BY id DESC limit 1";
		return $this->getAdapter()->fetchOne($sql);
	}
	function getGroupListAndCriterial($search)
	{
		$groupResult = $this->getAllClassByUser($search);
		if (!empty($groupResult)) {
		}
	}
	function getCriterialByGrading($data)
	{

		defined('CRITERIA_LIMIT_SETTING') || define('CRITERIA_LIMIT_SETTING', Setting_Model_DbTable_DbGeneral::geValueByKeyName('criteriaLimitation'));

		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();

		$currentTeacher = $this->getUserExternalId();
		$currentDate =  date('Y-m-d');

		$currentLang = $dbp->currentlang();
		$title = 'title_en';
		if ($currentLang == 1) {
			$title = 'title';
		}

		$gradingId = empty($data['gradingId']) ? 0 : $data['gradingId'];
		$sql = "SELECT 
				s.*,
				s.timeInput AS timeInputSetting,
				COALESCE((SELECT sttDi.isNotEnteryCri FROM `rms_scoreengsettingdetail` AS sttDi WHERE sttDi.score_setting_id =s.score_setting_id AND sttDi.subjectId =  " . $data['subjectId'] . " ORDER BY sttDi.isNotEnteryCri DESC LIMIT 1 ),'0') AS isNotEntryCr
				,(SELECT cri.criteriaType FROM `rms_exametypeeng` cri WHERE cri.id= s.criteriaId LIMIT 1) criteriaType
				,(SELECT es.$title FROM `rms_exametypeeng` AS es WHERE es.id = s.criteriaId LIMIT 1) AS criterialTitle
				,(SELECT COUNT(gd.id) FROM `rms_grading_tmp` AS gd WHERE gd.criteriaId = s.criteriaId AND gd.subjectId= " . $data['subjectId'] . " AND gd.settingEntryId= " . $data['settingEntryId'] . " AND gd.groupId=" . $data['groupId'] . " AND gd.examType=" . $data['examType'] . " LIMIT 1) AS timeInput 
			FROM `rms_scoreengsettingdetail` AS s 
			WHERE s.score_setting_id=$gradingId 
			AND (s.subjectId =0 OR s.subjectId=" . $data['subjectId'] . ")
		";

		if (!empty($data['examType'])) {
			$sql .= ' AND s.forExamType=' . $data['examType'];
		}
		$sql .= " AND (SELECT crit.criteriaType FROM `rms_exametypeeng` AS crit WHERE crit.id = s.criteriaId LIMIT 1)
				 > CASE 
			WHEN COALESCE((SELECT sttDi.isNotEnteryCri FROM `rms_scoreengsettingdetail` AS sttDi WHERE sttDi.score_setting_id =s.score_setting_id 
			AND sttDi.subjectId =  " . $data['subjectId'] . " ORDER BY sttDi.isNotEnteryCri DESC LIMIT 1 ),'0') =1 
						THEN '1' 
						ELSE '0'
					END 
				AND s.isNotEnteryCri = CASE 
					WHEN COALESCE((SELECT sttDi.isNotEnteryCri FROM `rms_scoreengsettingdetail` AS sttDi WHERE sttDi.score_setting_id =s.score_setting_id 
			AND sttDi.subjectId =  " . $data['subjectId'] . " ORDER BY sttDi.isNotEnteryCri DESC LIMIT 1 ),'0') =1 
						THEN '1' 
					ELSE '0'
			END  ";
		$sql .= " ORDER BY criteriaType ASC, s.criteriaId ASC, s.`subjectId` DESC ";
		$db = $this->getAdapter();
		$row = $db->fetchAll($sql);

		if (!empty($row)) {
			$newArray = array();
			$criteriaId = "";
			foreach ($row as $criteria) {
				if (($criteriaId != $criteria["criteriaId"])) {
					array_push($newArray, $criteria);
				}
				$criteriaId = $criteria["criteriaId"];
			}
			$row = $newArray;
		}
		$row = empty($row) ? array() : $row;
		return $row;
	}

	function getAllClassForIssueScore($search)
	{
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();

		$currentTeacher = $this->getUserExternalId();
		$currentDate =  date('Y-m-d');

		$currentLang = $dbp->currentlang();
		$colunmname = 'title_en';
		$label = 'name_en';
		$branch = "branch_nameen";
		$month = "month_en";
		$subjectTitle = 'subject_titleen';
		if ($currentLang == 1) {
			$colunmname = 'title';
			$label = 'name_kh';
			$branch = "branch_namekh";
			$month = "month_kh";
			$subjectTitle = 'subject_titlekh';
		}
		$sql = "SELECT 
			sett.`id`
			,sett.`title`
			,sett.`examType`
			,sett.`fromDate`
			,sett.`endDate`
			,sett.`examFromDate`
			,CASE
				WHEN aTs.endDate  IS NOT NULL THEN aTs.endDate 
				ELSE sett.examEndDate
			END AS examEndDate
			,g.`group_code` 
			,g.`id` as groupId
			,g.`gradingId` as gradingId
			,gsjb.`subject_id` as subjectId
			,(SELECT br.$branch FROM `rms_branch` AS br WHERE br.br_id=sett.branchId LIMIT 1) As branchName
			,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =sett.examType LIMIT 1) as forTypeTitle
			,CASE
				WHEN sett.examType = 2 THEN sett.forSemester
				ELSE (SELECT $month FROM `rms_month` WHERE id=sett.forMonth  LIMIT 1) 
			END AS forMonthTitle
			,(SELECT subj.$subjectTitle FROM `rms_subject` AS subj WHERE subj.id = gsjb.subject_id  LIMIT 1) AS subjectTitle
			,(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear
			,COALESCE(gd.gradingTmpId,'0') AS IsExam
			,COALESCE(gd.isLock,'0') AS isLock
		FROM 
			`rms_group_subject_detail` AS gsjb
			JOIN `rms_group` AS g ON g.id = gsjb.group_id AND g.is_use=1  AND g.is_pass=2
			JOIN `rms_score_entry_setting` AS sett ON FIND_IN_SET(g.degree,sett.degreeId) AND sett.status = 1
			AND CASE  WHEN sett.examType = 1 THEN gsjb.amount_subject >0 ELSE gsjb.amount_subject_sem END  >0
			LEFT JOIN `rms_grading` AS gd ON gd.groupId= g.`id`  AND gd.subjectId = gsjb.subject_id AND gd.settingEntryId= sett.`id`  AND gd.teacherId= gsjb.teacher
			LEFT JOIN `rms_allowed_teacher_score_setting` AS aTs ON aTs.teacherId = gsjb.teacher AND g.id = aTs.group AND FIND_IN_SET(gsjb.subject_id,(aTs.subjectId)) AND aTs.endDate > sett.examEndDate
			";

		$where = " WHERE g.`gradingId` !=0 AND g.is_pass !=3 ";
		$where .= " AND gsjb.teacher =" . $currentTeacher;

		if (!empty($search['adv_search'])) {
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]	= "	`g`.`group_code` LIKE '%{$s_search}%'";
			$s_where[]	= "	(SELECT b.branch_namekh FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]	= "	(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]	= " `g`.`semester` LIKE '%{$s_search}%'";

			$s_where[]	= "	(SELECT i.title FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]	= "	(SELECT i.title_en FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) LIKE '%{$s_search}%'";

			$s_where[] 	= "	(SELECT id.title FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] 	= "	(SELECT id.title_en FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) LIKE '%{$s_search}%'";

			$s_where[] 	= "	(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`)) LIKE '%{$s_search}%'";

			$where .= ' AND (' . implode(' OR ', $s_where) . ')';
		}
		if (!empty($search['academic_year'])) {
			$where .= ' AND g.academic_year=' . $search['academic_year'];
		}
		if (!empty($search['degree'])) {
			$where .= ' AND `g`.`degree`=' . $search['degree'];
		}
		if (!empty($search['grade'])) {
			$where .= ' AND g.grade=' . $search['grade'];
		}
		if (!empty($search['is_pass']) and $search['is_pass'] > -1) {
			$where .= ' AND g.is_pass=' . $search['is_pass'];
		}
		$where .= "	AND sett.fromDate <= '" . $currentDate . "' 
		AND CASE 
			WHEN aTs.endDate IS NOT NULL
			THEN aTs.endDate
			ELSE sett.examEndDate
		END >='" . $currentDate . "'";
		$groupby = "	GROUP BY 
			sett.id,
			gsjb.group_id,
			gsjb.subject_id ";

		$orderby = "	ORDER BY 
			sett.id DESC,
			g.group_code ASC  
			,gsjb.subject_id ASC
		";
		// echo $sql . $where . $groupby . $orderby;
		// exit();
		return $db->fetchAll($sql . $where . $groupby . $orderby);
	}

	function checkTecherEntrySetting($data)
	{
		$currentDate =  date('Y-m-d');
		$criterialType = $data['criterialType'];
		if ($criterialType == 2) {
			$fromDate = "st.examFromDate";
			$endDate = "st.examEndDate";
		} else {
			$fromDate = "st.fromDate";
			$endDate = "st.endDate";
		}
		$db = $this->getAdapter();
		$sql = "SELECT 
			st.id,
			st.fromDate,
			st.endDate,
			st.examFromDate,
			st.examEndDate,
			st.title,
			st.degreeId  ";
		if ($criterialType == 2) {
			$sql .= "
			,att.subjectId,
			CASE 
				WHEN att.endDate IS NOT NULL 
				THEN att.endDate
				ELSE " . $endDate . "
			END AS endDateExam   ";
		}
		$sql .= "	FROM `rms_score_entry_setting` as st ";
		if ($criterialType == 2) {
			$sql .= " LEFT JOIN `rms_allowed_teacher_score_setting` as att ON att.`teacherId`=" . $data['teacherId'] . " AND att.group=" . $data['groupId'] . " AND att.status=1	AND FIND_IN_SET( " . $data['subjectId'] . ",att.`subjectId`) AND att.endDate > " . $endDate;
			$sql .= " WHERE st.status=1 AND '" . $currentDate . "'>=" . $fromDate;
			$sql .= "	AND CASE 
				WHEN att.endDate IS NOT NULL
				THEN att.endDate ELSE " . $endDate . " END >= '" . $currentDate . "' ";
		} else {
			$sql .= " WHERE st.status=1  AND st.fromDate <= '" . $currentDate . "' AND st.endDate >='" . $currentDate . "' ";
		}
		if (!empty($data['degreeId'])) {
			$sql .= " AND  FIND_IN_SET( " . $data['degreeId'] . ", st.degreeId )";
		}
		$sql .= " ORDER BY st.id DESC LIMIT 1 ";
		// if($criterialType==2){
		// 	echo $sql;
		// }
		return $db->fetchRow($sql);
	}
}
