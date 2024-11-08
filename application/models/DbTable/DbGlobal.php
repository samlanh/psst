<?php

class Application_Model_DbTable_DbGlobal extends Zend_Db_Table_Abstract
{
	// set name value
	public function setName($name)
	{
		$this->_name = $name;
	}
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	function currentlang()
	{
		$session_lang = new Zend_Session_Namespace('lang');
		$lang = $session_lang->lang_id;
		if (empty($session_lang->lang_id)) {
			$lang = 1;
		}
		return $lang;
	}
	public function getBranchDisplay()
	{
		$branch_display_setting=Setting_Model_DbTable_DbGeneral::geValueByKeyName('branch_display_setting');
		$lang = $this->currentlang();
		$string = "branch_namekh";
		if($branch_display_setting==1){
			if($lang==1){// khmer
				$string = "branch_namekh";
			}else{ // English
				$string = "branch_nameen";
			}
		}elseif($branch_display_setting==2){
			$string = "abbreviations";
		}elseif($branch_display_setting==3){
			$string = "branch_code";
		}	
		return $string;
	}
	
	public function getViewLabelDisplay($viewFor = "full")
	{
		$dbSett = new Setting_Model_DbTable_DbGeneral();
		$labelViewSetting=1;
		if($viewFor=="shortcut"){
			$labelViewSetting=2;
		}
		
		$lang = $this->currentlang();
		if($labelViewSetting==2){
			$string = "shortcut";
		}else{
			$string = "name_en";
			if($lang==1){// khmer
				$string = "name_kh";
			}
		}	
		return $string;
	}
	
	public function getGradingSystem($degreeId, $template = 1)
	{//$template1=psis,2=ahs
		if ($template == 1) {
			if ($degreeId == 1) {
				$rs = array(
					array(
						'criteria' => $this->tr->translate("CLASS PARTICIPATION"),
						'percent' => "15%",
						'percentage' => "90-100%",
						'grade' => "A",
						'interpretation' => "ល្អណាស់ Very Good",
						'achievment' => "ប្រសើរជាងគេ Outstanding",
					),
					array(
						'criteria' => $this->tr->translate("HOMEWORK AND ASSIGNMENT"),
						'percent' => "15%",
						'percentage' => "80-89%",
						'grade' => "B",
						'interpretation' => "ល្អ Good",
						'achievment' => "គួរជាទីពេញចិត្ត Satisfactory",
					),
					array(
						'criteria' => $this->tr->translate("QUIZZES"),
						'percent' => "15%",
						'percentage' => "70-79%",
						'grade' => "C",
						'interpretation' => "ល្អបង្គួរ Fair",
						'achievment' => "ត្រូវខំប្រឹងតទៅទៀត Needs Improvement",
					),
					array(
						'criteria' => $this->tr->translate("MONTHLY TEST"),
						'percent' => $this->tr->translate("55%"),
						'percentage' => "60-69%",
						'grade' => "D",
						'interpretation' => "មធ្យម Average",
						'achievment' => "មិនទាន់ពេញចិត្ត Unsatisfactory",
					),
					array(
						'criteria' => '',
						'percent' => "",
						'percentage' => "50-59%",
						'grade' => "E",
						'interpretation' => "ខ្សោយ Poor",
						'achievment' => "មិនអនុវត្តកើត Not Applicable",
					),
					array(
						'criteria' => "",
						'percent' => "",
						'percentage' => "0-49%",
						'grade' => "F",
						'interpretation' => "ធ្លាក់ Failed",
						'achievment' => "មិនអនុវត្តកើត Not Applicable",
					),
				);

			} else {
				$rs = array(
					array(
						'criteria' => $this->tr->translate("Attendance"),
						'percent' => "5%",
						'percentage' => "90-100%",
						'grade' => "A",
						'interpretation' => "ល្អណាស់ Very Good",
						'achievment' => "ប្រសើរជាងគេ Outstanding",
					),
					array(
						'criteria' => $this->tr->translate("Discipline"),
						'percent' => "10%",
						'percentage' => "80-89%",
						'grade' => "B",
						'interpretation' => "ល្អ Good",
						'achievment' => "គួរជាទីពេញចិត្ត Satisfactory",
					),
					array(
						'criteria' => $this->tr->translate("Book Check/Oral Question"),
						'percent' => "5%",
						'percentage' => "70-79%",
						'grade' => "C",
						'interpretation' => "ល្អបង្គួរ Fair",
						'achievment' => "ត្រូវខំប្រឹងតទៅទៀត Needs Improvement",
					),
					array(
						'criteria' => $this->tr->translate("Homework"),
						'percent' => $this->tr->translate("15%"),
						'percentage' => "60-69%",
						'grade' => "D",
						'interpretation' => "មធ្យម Average",
						'achievment' => "មិនទាន់ពេញចិត្ត Unsatisfactory",
					),
					array(
						'criteria' => $this->tr->translate("Quiz"),
						'percent' => "15%",
						'percentage' => "50-59%",
						'grade' => "E",
						'interpretation' => "ខ្សោយ Poor",
						'achievment' => "មិនអនុវត្តកើត Not Applicable",
					),
					array(
						'criteria' => $this->tr->translate("Monthly Exam"),
						'percent' => "50%",
						'percentage' => "0-49%",
						'grade' => "F",
						'interpretation' => "ធ្លាក់ Failed",
						'achievment' => "មិនអនុវត្តកើត Not Applicable",
					),
				);
			}
		}
		if ($template == 2) {//ahs
			$rs = array(
				array(
					'criteria' => "ការចូលរួម / Class Participation",
					'percent' => "5%",
					'percentage' => "90-100%",
					'grade' => "A",
					'interpretation' => "ល្អប្រសើរ / Excellence",
					'achievment' => "ប្រសើរជាងគេ Outstanding",
				),
				array(
					'criteria' => "តេស្តខ្លី / Quiz",
					'percent' => "5%",
					'percentage' => "80-89.99%",
					'grade' => "B",
					'interpretation' => "ល្អណាស់ / Very Good",
					'achievment' => "គួរជាទីពេញចិត្ត Satisfactory",
				),
				array(
					'criteria' => "វត្តមាន / Attendance",
					'percent' => "10%",
					'percentage' => "70-79.99%",
					'grade' => "C",
					'interpretation' => "ល្អបង្គួរ / Fair Good",
					'achievment' => "ត្រូវខំប្រឹងតទៅទៀត Needs Improvement",
				),
				array(
					'criteria' => "អាកប្បកិរិយា / Behavior",
					'percent' => $this->tr->translate("10%"),
					'percentage' => "60-69.99%",
					'grade' => "D",
					'interpretation' => "ល្អ / Good",
					'achievment' => "មិនទាន់ពេញចិត្ត Unsatisfactory",
				),
				array(
					'criteria' => "កិច្ចការផ្ទះ / Homework",
					'percent' => "20%",
					'percentage' => "50-59.99%",
					'grade' => "E",
					'interpretation' => "មធ្យម /Average",
					'achievment' => "មិនអនុវត្តកើត Not Applicable",
				),
				array(
					'criteria' => "ប្រឡងប្រចាំខែ / Monthly Test",
					'percent' => "50%",
					'percentage' => "0-49.99%",
					'grade' => "F",
					'interpretation' => "ធ្លាក់ / Failed",
					'achievment' => "មិនអនុវត្តកើត Not Applicable",
				),
			);
		}

		return $rs;
	}
	public function getGradingSystemEng($degreeId, $template = 1)
	{//$template1=psis,2=ahs
		if ($template == 1) {
			if ($degreeId == 1) {
				$rs = array(
					array(
						'criteria' => $this->tr->translate("CLASS PARTICIPATION"),
						'percent' => "15%",
						'percentage' => "90-100%",
						'grade' => "A",
						'interpretation' => "Very Good",
						'achievment' => "Outstanding",
					),
					array(
						'criteria' => $this->tr->translate("HOMEWORK AND ASSIGNMENT"),
						'percent' => "15%",
						'percentage' => "80-89%",
						'grade' => "B",
						'interpretation' => "Good",
						'achievment' => "Satisfactory",
					),
					array(
						'criteria' => $this->tr->translate("QUIZZES"),
						'percent' => "15%",
						'percentage' => "70-79%",
						'grade' => "C",
						'interpretation' => "Fair",
						'achievment' => "Needs Improvement",
					),
					array(
						'criteria' => $this->tr->translate("MONTHLY TEST"),
						'percent' => $this->tr->translate("55%"),
						'percentage' => "60-69%",
						'grade' => "D",
						'interpretation' => "Average",
						'achievment' => "Unsatisfactory",
					),
					array(
						'criteria' => '',
						'percent' => "",
						'percentage' => "50-59%",
						'grade' => "E",
						'interpretation' => "Poor",
						'achievment' => "Not Applicable",
					),
					array(
						'criteria' => "",
						'percent' => "",
						'percentage' => "0-49%",
						'grade' => "F",
						'interpretation' => "ធ្លាក់ Failed",
						'achievment' => "Not Applicable",
					),
				);

			} else {
				$rs = array(
					array(
						'criteria' => $this->tr->translate("Attendance"),
						'percent' => "5%",
						'percentage' => "90-100%",
						'grade' => "A",
						'interpretation' => "Very Good",
						'achievment' => "Outstanding",
					),
					array(
						'criteria' => $this->tr->translate("Discipline"),
						'percent' => "10%",
						'percentage' => "80-89%",
						'grade' => "B",
						'interpretation' => "Good",
						'achievment' => "Satisfactory",
					),
					array(
						'criteria' => $this->tr->translate("Book Check/Oral Question"),
						'percent' => "5%",
						'percentage' => "70-79%",
						'grade' => "C",
						'interpretation' => "Fair",
						'achievment' => "Needs Improvement",
					),
					array(
						'criteria' => $this->tr->translate("Homework"),
						'percent' => $this->tr->translate("15%"),
						'percentage' => "60-69%",
						'grade' => "D",
						'interpretation' => "Average",
						'achievment' => "Unsatisfactory",
					),
					array(
						'criteria' => $this->tr->translate("Quiz"),
						'percent' => "15%",
						'percentage' => "50-59%",
						'grade' => "E",
						'interpretation' => "Poor",
						'achievment' => "Not Applicable",
					),
					array(
						'criteria' => $this->tr->translate("Monthly Exam"),
						'percent' => "50%",
						'percentage' => "0-49%",
						'grade' => "F",
						'interpretation' => "Failed",
						'achievment' => "Not Applicable",
					),
				);
			}
		}
		return $rs;
	}
	public function getGlobalDb($sql)
	{
		$db = $this->getAdapter();
		$row = $db->fetchAll($sql);
		if (!$row)
			return NULL;
		return $row;
	}

	public function getGlobalDbRow($sql)
	{
		$db = $this->getAdapter();
		$row = $db->fetchRow($sql);
		if (!$row)
			return NULL;
		return $row;
	}
	public function getGlobalDbOne($sql)
	{
		$db = $this->getAdapter();
		$result = $db->fetchOne($sql);
		return $result;
	}

	public function isRecordExist($conditions, $tbl_name)
	{
		$db = $this->getAdapter();
		$sql = "SELECT * FROM " . $tbl_name . " WHERE " . $conditions . " LIMIT 1";
		$row = count($db->fetchRow($sql));
		if (!$row)
			return NULL;
		return $row;
	}
	public function getAllDay($all = 0)
	{
		defined('STUDY_DAY_SETTING') || define('STUDY_DAY_SETTING', Setting_Model_DbTable_DbGeneral::geValueByKeyName('studyday_schedule'));

		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if ($lang == 1) {// khmer
			$label = "name_kh";
		} else { // English
			$label = "name_en";
		}
		$sql = "SELECT key_code as id ,$label as name FROM rms_view where type = 18 ";
		if ($all == 0) {
			$sql .= " and key_code != 7 ";
		}
		if (STUDY_DAY_SETTING == 1) {
			$sql .= " and key_code != 7 ";
		} else if (STUDY_DAY_SETTING == 2) {
			$sql .= " and key_code != 7 and key_code != 6 ";
		}
		return $db->fetchAll($sql);
	}
	public function getDaySchedule($branch, $year, $group)
	{
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if ($lang == 1) {// khmer
			$label = "name_kh";
		} else { // English
			$label = "name_en";
		}
		$sql = "SELECT 
    				v.key_code as id,
    				v.$label as name 
    			FROM 
    				rms_view as v,
    				rms_group_reschedule as gs 
    			where 
    				v.key_code = gs.day_id 
    				and v.type = 18 
    				and gs.branch_id = $branch
    				and gs.group_id = $group
    				and gs.year_id = $year
    			group by
    				gs.day_id
    			order by
    				gs.day_id ASC ";
		return $db->fetchAll($sql);
	}

	public static function getResultWarning()
	{
		return array('err' => 1, 'msg' => 'មិន​ទាន់​មាន​ទន្និន័យ​នូវ​ឡើយ​ទេ!');
	}
	public function getUserId()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	public function caseStatusShowImage($status = "status")
	{
		$base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
		$imgnone = '<img src="' . $base_url . '/images/icon/cross.png"/>';
		$imgtick = '<img src="' . $base_url . '/images/icon/apply2.png"/>';
		$string = ", CASE
		   	WHEN  $status = 1 THEN '$imgtick'
		   	WHEN  $status = 0 THEN '$imgnone'
		   	END AS status ";
		return $string;
	}
	function getAllUserGlobal($branchId = null)
	{
		$db = $this->getAdapter();

		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		$branch_list = $session_user->branch_list;
		$level = $session_user->level;

		$sql = "SELECT
			u.id,
			CONCAT(u.last_name,' ',u.first_name) AS name,
			u.branch_id,
			u.branch_list
			 FROM `rms_users` AS u WHERE u.active=1
			AND u.is_system =0 ";

		if ($level != 1) {
			$bra = explode(",", $branch_list);
			if (!empty($bra)) {
				$s_where = array();
				foreach ($bra as $ss) {
					$s_where[] = "(FIND_IN_SET('" . $ss . "', u.branch_list) ) ";
				}
				$sql .= ' AND ( ' . implode(' OR ', $s_where) . ')';
			}
		}

		if (!empty($branchId)) {
			$bra = explode(",", $branchId);
			if (!empty($bra)) {
				$s_where = array();
				foreach ($bra as $ss) {
					$s_where[] = "(FIND_IN_SET('" . $ss . "', u.branch_list) ) ";
				}
				$sql .= ' AND ( ' . implode(' OR ', $s_where) . ')';
			}
		}

		$row = $db->fetchAll($sql);

		/*
						   $result =array();
						   if ($level!=1){
							   $bra = explode(",", $branch_list);
							   if (!empty($bra)){
								   $array = array();
								   foreach ($bra as $ss) {
									   $array[$ss] = $ss;
								   }
								   if (!empty($row)) foreach ($row as $key => $rs){
									   $exp = explode(",", $rs['branch_list']);
									   foreach ($exp as $ss){
										   if (in_array($ss, $array)) {
											   $result[$key] = $rs;
											   break;
										   }
									   }
								   }
							   }
						   }
						   if (!empty($result)){
							   $row = $result;
						   }
						   $result =array();
						   if (!empty($branchId)){
							   $bra = explode(",", $branchId);
							   if (!empty($bra)){
								   $array = array();
								   foreach ($bra as $ss) {
									   $array[$ss] = $ss;
								   }
								   if (!empty($row)) foreach ($row as $key => $rs){
									   $exp = explode(",", $rs['branch_list']);
									   foreach ($exp as $ss){
										   if (in_array($ss, $array)) {
											   $result[$key] = $rs;
											   break;
										   }
									   }
								   }
							   }
						   }
						   if (!empty($result)){
							   $row = $result;
						   }
					   */
		return $row;
	}
	function getUserListbyBranch($data)
	{
		$db = $this->getAdapter();

		$sql = "SELECT
			   	u.id,
			   	CONCAT(u.last_name,' ',u.first_name) AS name
			   	FROM `rms_users` AS u WHERE u.active=1
			   	AND u.is_system =0 ";
		if (!empty($data['branchId'])) {
			$sql .= ' AND u.branch_list= ' . $data['branchId'];
		}
		if (!empty($data['branch_filter'])) {
			$sql .= ' AND u.branch_id= ' . $data['branch_filter'];
		}
		return $db->fetchAll($sql);
	}

	public function getUserInfo()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		$session_teacher = new Zend_Session_Namespace('authteacher');
		if (!empty($session_user->user_id)) {
			$userName = $session_user->user_name;
			$GetUserId = $session_user->user_id;
			$level = $session_user->level;
			$location_id = $session_user->branch_id;
			$branch_list = $session_user->branch_list;
			$schoolOption = $session_user->schoolOption;
			$info = array("user_name" => $userName, "user_id" => $GetUserId, "level" => $level, "branch_id" => $location_id, "branch_list" => $branch_list, "schoolOption" => $schoolOption);
			return $info;
		} elseif (!empty($session_teacher->teacher_id)) {
			$userName = $session_teacher->teacher_name;
			$teacherId = $session_teacher->teacher_id;
			$location_id = $session_teacher->branch_id;
			$branch_list = $session_teacher->branch_list;
			$schoolOption = $session_teacher->schoolOption;
			$info = array("user_name" => $userName, "user_id" => $teacherId, "level" => null, "branch_list" => $branch_list, "schoolOption" => $schoolOption);
			return $info;
		}
	}

	public function getAllSession()
	{
		$db = $this->getAdapter();
		$sql = "SELECT key_code as id,name_en as name FROM rms_view WHERE type=4 AND status=1 ";
		return $db->fetchAll($sql);
	}

	public function getProvince()
	{
		$db = $this->getAdapter();
		$lang = $this->currentlang();
		$field = 'province_en_name';
		if ($lang == 1) {
			$field = 'province_kh_name';
		}
		$sql = "SELECT $field as province_en_name,province_id FROM rms_province WHERE status=1 AND province_en_name!='' ";
		return $db->fetchAll($sql);
	}

	public function getOccupation()
	{
		$db = $this->getAdapter();
		$currentlang = $this->currentlang();
		$occuTitle = 'occu_enname';
		if ($currentlang == 1) {
			$occuTitle = 'occu_name';
		}
		$sql = "SELECT occupation_id as id, " . $occuTitle . " as name FROM rms_occupation WHERE status=1 AND occu_name!='' 
	   	ORDER BY " . $occuTitle . " ASC ";
		return $db->fetchAll($sql);
	}

	public function getAllLangLevel()
	{
		$db = $this->getAdapter();
		$sql = "SELECT id as id, title as name FROM rms_degree_language WHERE status=1 AND title!='' ORDER BY rms_degree_language.id ASC ";
		return $db->fetchAll($sql);
	}

	public function getAllNation()
	{
		$db = $this->getAdapter();
		$lang = $this->currentlang();
		$field = 'name_en';
		if ($lang == 1) {
			$field = 'name_kh';
		}
		$sql = "SELECT key_code as id, $field AS name FROM rms_view WHERE rms_view.type=21 AND name_kh!='' ORDER BY rms_view.id ASC";
		return $db->fetchAll($sql);
	}

	public function getAllKnowBy()
	{
		$db = $this->getAdapter();
		$sql = "SELECT id as id, title as name FROM rms_know_by WHERE status=1 AND title!='' ORDER BY rms_know_by.id ASC ";
		return $db->fetchAll($sql);
	}

	public function getAllDocumentType()
	{
		$db = $this->getAdapter();
		$sql = "SELECT name as id, name FROM rms_document_type WHERE types=1 AND status=1 AND name!='' ORDER BY rms_document_type.id ASC ";
		return $db->fetchAll($sql);
	}

	public function getAllDocteacherType()
	{
		$db = $this->getAdapter();
		$sql = " SELECT name as id, name FROM rms_document_type WHERE types=2 AND status=1 AND name!='' ORDER BY rms_document_type.id ASC ";
		return $db->fetchAll($sql);
	}

	public function getAllFecultyNamess($type)
	{
		return $this->getAllFecultyName();
	}
	public function getAllDegreeName()
	{
		$db = $this->getAdapter();
		return $this->getAllItems(1, null);
	}

	public function getAllFecultyName()
	{
		$db = $this->getAdapter();
		return $this->getAllItems(1, null);
	}

	function getAllgroupStudyNotPass($action = null)
	{
		$db = $this->getAdapter();
		$sql = "SELECT `g`.`id`, CONCAT(`g`.`group_code`,' ',
   	(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=f.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation) ) AS name
   	FROM `rms_group` AS `g` WHERE g.status =1";
		$where = '';
		if (!empty($action)) {
			$where = " AND (g.is_pass=0 || g.id = $action)";
		} else {
			$where = " AND g.is_pass=0 ";
		}
		return $db->fetchAll($sql . $where);
	}

	public function getAllServiceItemsName($status = 1, $type = null)
	{
		$db = $this->getAdapter();
		return $this->getAllItems(null);
	}

	function getAllDept($search, $start, $limit)
	{
		$db = $this->getAdapter();
		$sql = $this->_buildQuery($search) . " LIMIT " . $start . ", " . $limit;
		if ($limit == 'All') {
			$sql = $this->_buildQuery($search);
		}
		return $db->fetchAll($sql);
	}


	public function getGlobalResultList($sql, $sql_count)
	{
		$db = $this->getAdapter();
		$rows = $db->fetchAll($sql);
		$_count = count($db->fetchAll($sql_count));
		return array(0 => $rows, 1 => $_count);
		//get all result by param 0 ,get count record by param1
	}

	public function getAllDegree($id = null)
	{
		$rs = array(
			0 => $this->tr->translate("SELECT_DEGREE"),
			1 => $this->tr->translate("ASSOCIATE"),
			2 => $this->tr->translate("BACHELOR"),
			3 => $this->tr->translate('MASTER'),
			4 => $this->tr->translate('DOCTORATE'),
			5 => $this->tr->translate('English Program'),
			6 => $this->tr->translate('Computer Course'),
			7 => $this->tr->translate('Other')
		);
		if ($id == null)
			return $rs;
		return $rs[$id];
	}

	public static function getAllStatus($id = null)
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$rs = array(
			1 => $tr->translate("ACTIVE"),
			0 => $tr->translate("DEACTIVE")
		);
		if ($id == null)
			return $rs;
		return $rs[$id];
	}
	public function AllStatus($id = null)
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$rs = array(
			1 => $tr->translate("ACTIVE"),
			0 => $tr->translate("DEACTIVE")
		);
		if ($id == null)
			return $rs;
		return $rs[$id];
	}

	public function AllStatusRe($id = null)
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$rs = array(
			0 => $tr->translate("PLEASE_SELECT_STATUS"),
			1 => $tr->translate("RELATIVE"),
			2 => $tr->translate("FRIEND"),
			3 => $tr->translate("BUSINESS_PARTNER"),
			4 => $tr->translate("OTHER")
		);
		if ($id == null)
			return $rs;
		return $rs[$id];
	}
	public static function getAllDegreeById($id = null)
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$rs = array(
			1 => $tr->translate("Grade School"),
			2 => $tr->translate("Pramary School"),
			3 => $tr->translate('Other'),
		);
		if ($id == null)
			return $rs;
		return $rs[$id];
	}
	public function getAllPaymentTerm($id = null, $hidemonth = null)
	{
		if ($hidemonth != null) {
			$opt_term = array(
				2 => $this->tr->translate('TERM'),
				3 => $this->tr->translate('SEMESTER'),
				4 => $this->tr->translate('YEAR'),
			);
			return $opt_term;
		}
		$opt_term = array(
			1 => $this->tr->translate('MONTHLY'),
			2 => $this->tr->translate('TERM'),
			3 => $this->tr->translate('SEMESTER'),
			4 => $this->tr->translate('YEAR'),
			5 => $this->tr->translate('ONE_PAYMENT_ONLY'),
		);
		if ($id == null) {
			return $opt_term;
		} else {
			return $opt_term[$id];
		}
	}
	public function getAllServicePayment($id = null)
	{
		$opt_term = array(
			1 => $this->tr->translate('RIEL'),
			2 => $this->tr->translate('PRICE1'),
			3 => $this->tr->translate('PRICE2')
		);
		if ($id == null)
			return $opt_term;
		else
			return $opt_term[$id];
	}
	public function getAllGEPPrgramPayment($id = null)
	{
		$opt_term = array(
			1 => $this->tr->translate('FEE'),
			2 => $this->tr->translate('2TERM'),
			3 => $this->tr->translate('3TERM')
		);
		if ($id == null)
			return $opt_term;
		else
			return $opt_term[$id];
	}

	public static function getAllMention($id = null)
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$opt_rank = array(
			1 => $tr->translate('A'),
			2 => $tr->translate('B'),
			3 => $tr->translate('C'),
			4 => $tr->translate('D'),
			5 => $tr->translate('E'),
		);
		if ($id == null)
			return $opt_rank;
		else
			return $opt_rank[$id];
	}

	public function getTutionFeebyCondition($data)
	{
		$db = $this->getAdapter();
		//for bachelor
		$degree = $data['degree'];
		$metion = $data['metion'];
		$batch = $data['batch'];
		$faculty_id = $data['faculty_id'];
		$payment_type = $data['payment_term'];
		if ($degree == 2) {
			$sql = " SELECT tuition_fee FROM `rms_tuitionfee` AS f,`rms_tuitionfee_detail` AS fd
   		WHERE f.fee_id = fd.fee_id AND metion = $metion AND  degree =$degree AND
   		batch = $batch AND faculty_id = $faculty_id AND `payment_type`=$payment_type LIMIT 1";
		} else {
			$sql = "SELECT tuition_fee FROM `rms_tuitionfee` AS f,`rms_tuitionfee_detail` AS fd
   		WHERE f.fee_id = fd.fee_id AND metion = $faculty_id AND  degree =$degree AND
   		batch = $batch AND `payment_type`=$payment_type";
		}
		return $db->fetchOne($sql);
	}

	function getTeacherCode($branch_id = 0)
	{
		$db = $this->getAdapter();

		$pre = $this->getPrefixCode($branch_id);

		$sql = " SELECT COUNT(id) AS number FROM `rms_teacher` where branch_id = $branch_id LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);

		$new_acc_no = (int) $acc_no + 1;
		$acc_no = strlen((int) $acc_no + 1);

		for ($i = $acc_no; $i < 5; $i++) {
			$pre .= '0';
		}
		return $pre . $new_acc_no;
	}

	function getPrefixCode($branch_id)
	{
		$db = $this->getAdapter();
		$sql = " SELECT prefix FROM `rms_branch` WHERE br_id = $branch_id LIMIT 1 ";
		return $db->fetchOne($sql);
	}

	function getAllProvince($opt = null, $option = null)
	{
		$db = $this->getAdapter();
		$lang = $this->currentlang();
		$field = 'province_en_name';
		if ($lang == 1) {
			$field = 'province_kh_name';
		}
		$sql = "SELECT province_id as id,$field AS name FROM rms_province WHERE status=1 ";
		$rows = $db->fetchAll($sql);
		if ($opt == null) {
			return $rows;
		} else {
			if ($option != null) {
				$opt_province = array(-1 => "Please Select Location");
			} else {
				$opt_province = array();
			}
			if (!empty($rows))
				foreach ($rows as $row)
					$opt_province[$row['id']] = $row['province_name'];
			return $opt_province;
		}
	}

	public function getAllDistrict()
	{
		$this->_name = 'ln_district';
		$lang = $this->currentlang();
		$field = 'district_name';
		if ($lang == 1) {
			$field = 'district_namekh';
		}
		$sql = " SELECT dis_id,pro_id,$field AS district_name FROM $this->_name WHERE status=1 AND district_name!='' ";
		$db = $this->getAdapter();
		return $db->fetchAll($sql);
	}

	//    public function getAllsubject(){//not use yet
// 	   	$db = $this->getAdapter();

	// 	   	$lang = $this->currentlang();
// 	   	$field = 'subject_titleen';
// 	   	if ($lang==1){
// 	   		$field = 'subject_titlekh';
// 	   	}
// 	   	$sql = "SELECT id ,$field AS  name
// 	   	FROM `rms_subject` WHERE status=1 AND (subject_titleen!='' OR subject_titlekh!='')";
// 	   	return $db->fetchAll($sql);
//    }


	public function getAllGroup()
	{
		$db = $this->getAdapter();
		$sql = " 
			SELECT 
				g.id 
				,g.group_code As name 
			FROM 
			`rms_group` AS g
			WHERE g.status=1 AND g.group_code != '' 
			";
		$sql .= $this->getAccessPermission('g.branch_id');
		$sql .= $this->getDegreePermission('g.degree');
		$sql .= " ORDER BY g.degree,g.grade,g.group_code ASC,`g`.`id` DESC ";
		return $db->fetchAll($sql);
	}

	public function getAllTeacherSubject()
	{
		$db = $this->getAdapter();
		$branch_id = $this->getAccessPermission();
		$sql = "SELECT  
   			id ,
   			CONCAT(teacher_name_en) AS name 
   			FROM `rms_teacher`
   	        WHERE status = 1 AND active_type=0
   	$branch_id  Order BY id DESC";
		return $db->fetchAll($sql);
	}
	function getRoom()
	{
		$db = $this->getAdapter();
		$sql = "SELECT room_id,room_name FROM rms_room ";
		return $db->fetchAll($sql . 'ORDER  BY room_id DESC');
	}
	public function getAllRoom($branch_id = null)
	{
		$db = $this->getAdapter();
		$sql = " SELECT room_id AS id ,room_name As name FROM `rms_room` WHERE is_active=1 AND room_name!='' ";
		if (!empty($branch_id)) {
			$sql .= " AND branch_id =$branch_id";
		}
		$sql .= $this->getAccessPermission();
		$sql .= " ORDER BY room_name ASC ";
		return $db->fetchAll($sql);
	}
	public function getAllDegreeMent()
	{
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if ($lang == 1) {// khmer
			$label = "name_kh";
		} else { // English
			$label = "name_en";
		}
		$db = $this->getAdapter();
		$sql = " SELECT key_code AS id, $label AS name FROM rms_view WHERE STATUS=1 AND TYPE=3 AND name_kh!='' ORDER BY rms_view.key_code ASC ";
		return $db->fetchAll($sql);
	}
	function getSession()
	{
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if ($lang == 1) {// khmer
			$label = "name_kh";
		} else { // English
			$label = "name_en";
		}
		$sql = "SELECT key_code AS id,key_code,$label as name,$label AS view_name 
   		FROM rms_view WHERE `type`=4 AND `status`=1";
		return $db->fetchAll($sql);
	}
	function getAllBranchName()
	{
		return $this->getAllBranch();
	}

	function getAllDiscount($option = null)
	{
		$db = $this->getAdapter();
		$sql = "  SELECT disco_id AS id,dis_name AS name FROM `rms_discount` 
	   		WHERE status=1 AND dis_name!='' ";
		$rows = $db->fetchAll($sql);
		if ($option != null) {
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$options = array(0 => $tr->translate("CHOOSE"));
			if (!empty($rows))
				foreach ($rows as $row) {
					$options[$row['id']] = $row['name'];
				}
			return $options;
		} else {
			return $rows;
		}
	}
	function getAllDepartment()
	{
		$db = $this->getAdapter();
		$currentLang = $this->currentlang();
		$colunmName = 'depart_nameen';
		if ($currentLang == 1) {
			$colunmName = 'depart_namekh';
		}
		$sql = "  SELECT depart_id AS id,$colunmName AS name FROM `rms_department` WHERE STATUS=1 AND depart_namekh!=''  ";
		return $db->fetchAll($sql);
	}




	function getAllBranch()
	{
		$db = $this->getAdapter();
		$branch_id = $this->getAccessPermission('br_id');
		$branchLabel = $this->getBranchDisplay();
		$sql = "
			SELECT 
				br_id as id
				, $branchLabel as name 
			FROM rms_branch WHERE branch_nameen!='' 
			AND  status=1  $branch_id 
		";
		return $db->fetchAll($sql);
	}
	public function getAccessPermission($branch_str = 'branch_id')
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		$branch_list = $session_user->branch_list;
		$result = "";
		if (!empty($branch_list)) {
			$level = $session_user->level;
			if ($level == 1) {
				$result .= "";
			} else {
				$result .= " AND $branch_str IN ($branch_list)";
			}
		}
		return $result;

		$session_teacher = new Zend_Session_Namespace('authteacher');
		$branch_id = $session_teacher->branch_id;
		if (!empty($branch_id)) {
			$result = " AND $branch_str =" . $branch_id;
			return $result;
		}
	}

	function getAccessBranchForProduct($branch_str = 'branch_id')
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		$level = $session_user->level;
		$branch_list = $session_user->branch_list;
		if ($level == 1) {
			return;
		}

		$user = $this->getUserInfo();
		$branchOptionlist = $branch_list;
		$slist = explode(",", $branchOptionlist);
		$sql = "";
		$s_where = array();
		foreach ($slist as $option_id) {
			$s_where[] = " $option_id IN ($branch_str)";
		}
		$sql .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		return $sql;
	}

	function getSchoolOptionAccess($schooloption_coloum = "schoolOption")
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		$level = $session_user->level;
		if ($level == 1) {
			return;
		}

		$user = $this->getUserInfo();
		$schooloptionlist = $user['schoolOption'];
		$slist = explode(",", $schooloptionlist);
		$sql = "";
		$s_where = array();
		foreach ($slist as $option_id) {
			// 	   		$s_where[] = " $schooloption_coloum IN ($option)";
			//$s_where[] = " $option_id IN ($schooloption_coloum)";
			$s_where[] = "(FIND_IN_SET('" . $option_id . "', " . $schooloption_coloum . ") )";
		}
		$sql .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		return $sql;
	}

	public function getDegreePermission($degreeStr = 'degree')
	{

		$user = $this->getUserProfile();
		$level = $user['user_type'];
		$stringCondiction = "";
		if ($level != 1) {
			if (!empty($user['degreeList'])) {
				$degreeList = $user['degreeList'];
				$stringCondiction = " AND $degreeStr IN ($degreeList)";
			}

		}
		return $stringCondiction;
	}

	public function getUserAccessPermission($user_id = 'user_id')
	{
		$user = $this->getUserId();
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		$level = $session_user->level;
		if ($level == 1) {
			$result = "";
			return $result;
		} else {
			$result = " AND $user_id =" . $user;
			return $result;
		}
	}

	function getUserType()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->level;
	}

	function getViewById($type, $is_opt = null, $is_stringopt = null)
	{
		$db = $this->getAdapter();
		$lang = $this->currentlang();
		if ($lang == 1) {// khmer
			$label = "name_kh";
		} else { // English
			$label = "name_en";
		}
		$sql = "SELECT key_code AS id,$label AS name,key_code,$label AS view_name FROM rms_view WHERE `type`=$type AND `status`=1 order by key_code ASC ";
		$rows = $db->fetchAll($sql);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$options = array(-1 => $tr->translate("PAYMENT_METHOD"));
		if ($is_opt != null) {
			if (!empty($rows))
				foreach ($rows as $row) {
					$options[$row['key_code']] = $row['view_name'];
				}
		} elseif (!empty($is_stringopt)) {
			$options = '';
			if (!empty($rows))
				foreach ($rows as $value) {
					$options .= '<option value="' . $value['id'] . '" >' . htmlspecialchars($value['name'], ENT_QUOTES) . '</option>';
				}
			return $options;
		} else {
			return $rows;
		}
		return $options;
	}
	function getViewByType($type, $is_opt = null,$condictionArr = array())
	{
		$db = $this->getAdapter();

		$lang = $this->currentlang();
		$field = 'name_en';
		if ($lang == 1) {
			$field = 'name_kh';
		}

		$sql = "SELECT key_code as id ,$field AS name FROM rms_view WHERE `type`=$type AND `status`=1 ORDER BY key_code ASC ";//ORDER BY name_kh ASC
		$rows = $db->fetchAll($sql);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$options = array("" => $tr->translate("CHOOSE"));
		if ($is_opt != null) {
			$condictionArr["addonsItem"] = empty($condictionArr["addonsItem"]) ? "" : $condictionArr["addonsItem"];
			if($condictionArr["addonsItem"]=="addNEw"){
				$options["-1"] = $tr->translate("ADD_NEW");
			}
			if (!empty($rows))
				foreach ($rows as $row) {
					$options[$row['id']] = $row['name'];
				}
		} else {
			return $rows;
		}
		return $options;
	}
	function getExchangeRate()
	{
		$db = $this->getAdapter();
		$sql = "select reil from rms_exchange_rate";
		return $db->fetchOne($sql);
	}
	function getDegree()
	{
		return $this->getAllItems();
	}
	function getAllYear($type = 1, $is_completed = 0)
	{
		$db = $this->getAdapter();
		return $this->getAllAcademicYear();

	}
	function getAllYearByBranch($data)
	{//for fee id only
		$db = $this->getAdapter();
		$branch_id = $this->getAccessPermission();

		$lang = $this->currentlang();
		if ($lang == 1) {// khmer
			$label = "title_kh";
		} else { // English
			$label = "title_eng";
		}
		$titleQuery = "
		CONCAT(
			(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=academic_year LIMIT 1), 
		   	( SELECT $label FROM rms_studytype AS st WHERE st.id =rms_tuitionfee.term_study LIMIT 1 )
		   	,'(',generation,')'
			)
		";
		if (!empty($data['shortTitle'])) {
			$titleQuery = "
			CONCAT(
				COALESCE(( SELECT $label FROM rms_studytype AS st WHERE st.id =rms_tuitionfee.term_study LIMIT 1 ),'')
						,'(',generation,')'
			)
			";
		}

		$sql = "SELECT id,
		   	$titleQuery AS name,
		   	CONCAT_WS(',',(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=academic_year LIMIT 1),generation) AS years,
			generation as feeTitle,
			(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=academic_year LIMIT 1) academicYearTitle,
			( SELECT $label FROM rms_studytype AS st WHERE st.id =rms_tuitionfee.term_study LIMIT 1 ) AS termStudy

	   	FROM rms_tuitionfee WHERE 
	   	 type=1 AND `status`=1
	   	$branch_id ";

		if (!empty($data['branch_id'])) {
			$sql .= " AND branch_id=" . $data['branch_id'];
		}
		if (!empty($data['academicYear'])) {
			$sql .= " AND academic_year =" . $data['academicYear'];
		}
		if (isset($data['isFinished'])) {
			$sql .= " AND is_finished=" . $data['isFinished'];
		}

		if (!empty($data['schoolOption'])) {
			$sql .= " AND school_option = " . $data['schoolOption'];
		}
		if (!empty($data['degree'])) {
			$dbdeg = new Global_Model_DbTable_DbItems();
			$type = 1;
			$row = $dbdeg->getDegreeById($data['degree'], $type);
			if (!empty($row['schoolOption'])) {
				$sql .= " AND school_option IN (" . $row['schoolOption'] . ") ";
			}
		}

		$user = $this->getUserInfo();
		$level = $user['level'];
		if ($level != 1) {
			$sql .= ' AND school_option IN (' . $user['schoolOption'] . ')';
		}

		$sql .= " GROUP BY academic_year, term_study, generation ";
		$order = ' ORDER BY id DESC';
		$result = $db->fetchAll($sql.$order);
		if (!empty($data['option'])) {
			$options = '';
			if (!empty($result))
				foreach ($result as $value) {
					$param = array(
						'branch_id'=>empty($data['branch_id'])?:$data['branch_id'],
						//'academic_year'=>$value['id'],
						'feeId'=>$value['id'],
						'degree'=>empty($data['degree'])?'':$data['degree'],
						'serviceType'=>1
					);
					$resultPeriodJson =  htmlspecialchars(json_encode($this->getAllStudyPeriod($param)));
					$options .= '<option data-datajson="'.$resultPeriodJson.'"  data-academic-year-title="' . $value['academicYearTitle'] . '" data-fee-title="' . $value['feeTitle'] . '" data-term-study="' . $value['termStudy'] . '" value="' . $value['id'] . '" >' . htmlspecialchars($value['name']) . '</option>';
				}
			return $options;
		}
		return $result;
	}

	function getAllGrade()
	{
		$param = array(
			'itemsType' => 1,
		);
		return $this->getAllItemDetail($param);
	}
	public function getExpenseIncome($type)
	{
		$db = $this->getAdapter();
		$branch_id = $this->getAccessPermission();
		$sql = "SELECT id ,account_name as name FROM `rms_account_name` WHERE status=1 AND account_name!=''
   			AND account_type = " . $type;
		return $db->fetchAll($sql);
	}
	/*blog get student*/
	function getAllListStudent($data)
	{
		$db = $this->getAdapter();
		$branch_id = $this->getAccessPermission();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();

		$sql = " SELECT 
   		s.stu_id AS stu_id,
	   	stu_code,
	   	s.stu_id AS id,
	   	CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,''),'-',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS name
	   	FROM rms_student AS s ";

		if (!empty($data['joinGroup'])) {
			$sql .= ', rms_group_detail_student as gds ';
			$where = " WHERE gds.stu_id = s.stu_id";
			if (!empty($data['groupId'])) {
				$where .= " AND gds.group_id=" . $data['groupId'];
			}
			if (!empty($data['degree'])) {
				$where .= " AND gds.degree=" . $data['degree'];
			}
		} else {
			$where = " WHERE 1";
		}
		if (!empty($data['itemType'])) {
			$where .= " AND gds.itemType=" . $data['itemType'];
		}
		if (!empty($data['activStudent'])) {
			$where .= " AND (gds.stop_type=0) ";
			//OR gds.stop_type=3 OR gds.stop_type=4
		}
		if (isset($data['isCurrent'])) {
			$where .= " AND gds.is_current=" . $data['isCurrent'];
		}
		if (isset($data['isMaingrad'])) {
			$where .= " AND gds.is_maingrade=" . $data['isMaingrad'];
		}
		$branchStr = 'branchId';
		if (!empty($data['branch_id'])) {
			$branchStr = 'branch_id';
		}
		if (!empty($data['branchId'])) {
			$where .= " AND s.branch_id=" . $data[$branchStr];
		}
		if (!empty($data['branchId'])) {
			$where .= " AND s.branch_id=" . $data['branchId'];
		}

		if (!empty($data['customerType'])) {
			$where .= " AND s.customer_type=" . $data['customerType'];
		}
		$where .= " AND (s.stu_enname!='' OR s.stu_khname!='') ";

		/*	WHERE 
							
							AND gds.stu_id = s.stu_id 
							AND gds.is_current=1 AND gds.is_maingrade=1
							AND (gds.stop_type=0 OR gds.stop_type=3 OR gds.stop_type=4)
							AND (stu_enname!='' OR s.stu_khname!='')
							AND s.status=1  AND customer_type=1 ";*/


		$group = " GROUP BY s.stu_id ORDER BY stu_code ASC, stu_khname ASC ";
	
		$rows = $db->fetchAll($sql . $where . $group);
		if (!empty($data['opt'])) {
			$options = array(0 => $tr->translate("CHOOSE"));
			if (!empty($rows))
				foreach ($rows as $row) {
					$lable = $row['stu_code'];
					if ($data['type'] == 2) {
						$lable = $row['name'];
					}
					$options[$row['id']] = $lable;
				}
			return $options;
		} else {
			return $rows;
		}
	}
	function getAllListStudentName($opt = null, $type = 2, $branchid = null)
	{
		$db = $this->getAdapter();
		$branch_id = $this->getAccessPermission();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = " SELECT s.stu_id AS id,s.stu_id AS stu_id,
					   	stu_code,
					   	CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,''),'-',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS name
					   	FROM rms_student AS s
					   	WHERE
					   	(stu_enname!='' OR s.stu_khname!='')
	   				";
		if ($branchid != null) {
			$sql .= " AND branch_id=" . $branchid;
		}
		$sql .= " ORDER BY stu_khname ASC";
		$rows = $db->fetchAll($sql);
		if ($opt != null) {
			$options = array(0 => $tr->translate("CHOOSE"));
			if (!empty($rows))
				foreach ($rows as $row) {
					$lable = $row['stu_code'];
					if ($type == 2) {
						$lable = $row['name'];
					}
					$options[$row['id']] = $lable;
				}
			return $options;
		} else {
			return $rows;
		}
	}

	function getAllCustomer($opt = null, $branchid = null)
	{
		$db = $this->getAdapter();
		$branch_id = $this->getAccessPermission();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = " SELECT s.stu_id AS id,
   		s.stu_id AS stu_id,
	   	stu_code,
	   	CONCAT(COALESCE(s.stu_khname,''),'-',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS name
	   	FROM rms_student AS s
	   	WHERE
	   	s.stu_khname!=''
	   	AND s.status=1  AND customer_type=2 ";

		$sql .= " ORDER BY stu_khname ASC";
		$rows = $db->fetchAll($sql);
		if ($opt != null) {
			$options = array(0 => $tr->translate("CHOOSE"));
			if (!empty($rows))
				foreach ($rows as $row) {
					$lable = $row['name'];
					$options[$row['id']] = $lable;
				}
			return $options;
		} else {
			return $rows;
		}
	}
	function getAllStuCode()
	{
		$db = $this->getAdapter();
		$branch_id = $this->getAccessPermission();
		$sql = "SELECT 
			   	s.stu_id As id,
			   	s.stu_id As stu_id,
			   	s.stu_code As stu_code,
			   	s.stu_code AS name,
			   	(CASE WHEN s.stu_khname IS NULL THEN s.stu_enname ELSE s.stu_khname END) AS stu_name
			   	FROM rms_student AS s
   			WHERE s.status=1 AND customer_type=1 $branch_id  ORDER BY stu_code DESC ";
		return $db->fetchAll($sql);
	}

	function getStudentinfoGlobalById($stu_id)
	{
		$db = $this->getAdapter();
		$currentLang = $this->currentlang();
		$colunmname = 'title_en';
		$field = 'name_en';
		if ($currentLang == 1) {
			$colunmname = 'title';
			$field = 'name_kh';
		}

		$sql = "
			SELECT 
				s.*,
				fam.fatherNameKh AS father_khname 
				,fam.fatherName AS father_enname  
				,fam.fatherNation AS father_nation
				,fam.fatherPhone AS father_phone
				,fam.fatherPhoto AS fatherPhoto
				
				,fam.motherNameKh AS mother_khname 
				,fam.motherName AS mother_enname  
				,fam.motherNation AS mother_nation  
				,fam.motherPhone AS mother_phone  
				,fam.motherPhoto AS motherPhoto  
				
				,fam.guardianNameKh AS guardian_khname 
				,fam.guardianName AS guardian_enname 
				,fam.guardianNation AS guardian_nation 
				,fam.guardianPhone AS guardian_tel
				,fam.guardianPhoto AS guardianPhoto
				
				,DATE_FORMAT(s.dob,'%d-%m-%Y') AS dob,
	   			sgd.grade,
	   			sgd.degree,
	   			sgd.is_newstudent AS is_stu_new,
			   	(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=sgd.grade LIMIT 1) as grade_label,
				(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.id=sgd.degree LIMIT 1) as degree_label,
		   		(SELECT name_kh FROM `rms_view` WHERE type=3 AND key_code=s.calture LIMIT 1) as degree_culture,	
				(SELECT name_kh FROM `rms_view` WHERE type=40 AND key_code=s.studentType LIMIT 1) as studentType,	
				(SELECT p.`title`  FROM `rms_parttime_list` AS p WHERE p.`id` = sgd.`session` LIMIT 1) as parttime_label,		   		
		   		(SELECT SUM(cr.total_amountafter) FROM rms_creditmemo cr WHERE cr.status=1 AND cr.student_id = $stu_id and cr.total_amountafter>0 GROUP BY cr.student_id LIMIT 1 ) AS total_amountafter,
		   		(SELECT id FROM rms_creditmemo WHERE status AND student_id = $stu_id AND total_amountafter>0 GROUP BY student_id LIMIT 1 ) AS credit_memo_id,
		   		(SELECT $field from rms_view where type=5 and key_code=sgd.stop_type AND sgd.is_maingrade=1 AND sgd.is_current=1 LIMIT 1) as status_student,
		   		sgd.academic_year,
		   		sgd.is_newstudent,
		   		(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id =sgd.academic_year LIMIT 1) AS academic_year_label,
		   		(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id =s.academicYearEnroll LIMIT 1) AS academicYearEnroll,
		   		sgd.feeId AS fee_id,
				COALESCE(sgd.group_id,0) AS groupId,
				(SELECT group_code FROM `rms_group` WHERE rms_group.id=sgd.group_id AND sgd.is_maingrade=1 AND sgd.is_current=1 LIMIT 1) AS group_name
				
				
			
	   		FROM 
	   			rms_student AS s LEFT JOIN rms_group_detail_student AS sgd ON s.stu_id=sgd.stu_id
				LEFT JOIN rms_family AS fam ON fam.id = s.familyId
			WHERE 
				sgd.itemType=1
   				AND s.stu_id=$stu_id 
	   			AND sgd.is_current=1 
				AND sgd.is_pass=0
				AND sgd.is_maingrade=1
	   	LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getStudentTestinfoById($stu_id)
	{//for student with result
		$db = $this->getAdapter();
		$currentLang = $this->currentlang();
		$colunmname = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'title';
		}
		$sql = "
		SELECT 
			s.*,
			fam.fatherNameKh AS father_khname 
			,fam.fatherName AS father_enname  
			,fam.fatherNation AS father_nation
			,fam.fatherPhone AS father_phone
			
			,fam.motherNameKh AS mother_khname 
			,fam.motherName AS mother_enname  
			,fam.motherNation AS mother_nation  
			,fam.motherPhone AS mother_phone  
			
			,fam.guardianNameKh AS guardian_khname 
			,fam.guardianName AS guardian_enname 
			,fam.guardianNation AS guardian_nation 
			,fam.guardianPhone AS guardian_tel
			
			,'N/A' as group_name,
			(SELECT name_kh FROM `rms_view` WHERE type=3 AND key_code=s.calture LIMIT 1) as degree_culture,
			(SELECT total_amountafter FROM rms_creditmemo WHERE student_id = $stu_id and total_amountafter>0 ) AS total_amountafter,
			(SELECT id FROM rms_creditmemo WHERE student_id = $stu_id and total_amountafter>0 ) AS credit_memo_id,  	
			(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=t.grade_result LIMIT 1) as grade_label,
			(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.id=t.degree_result LIMIT 1) as degree_label,
			(SELECT p.`title`  FROM `rms_parttime_list` AS p WHERE p.`id` = (SELECT sd.`session` FROM `rms_group_detail_student` AS sd WHERE sd.`stu_id` =s.stu_id AND sd.`test_restult_id`= t.id  LIMIT 1 ) LIMIT 1) as parttime_label,
			t.degree_result AS degree,t.grade_result AS grade,t.session_result AS session,
			'N/A' AS status_student,
			t.id,
			(SELECT name_kh FROM `rms_view` WHERE type=4 AND key_code=t.session_result LIMIT 1) as session_label,
			'N/A' AS room_label,
			'1' AS is_newstudent,
			(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id =s.academicYearEnroll LIMIT 1) AS academicYearEnroll
		
			
		   		
	   	FROM 
			rms_student as s JOIN rms_student_test_result As t ON t.stu_test_id=s.stu_id 
			LEFT JOIN rms_family AS fam ON fam.id = s.familyId
	   	WHERE 
		   	1 
		    AND t.is_current=1 AND updated_result=1
		   	AND s.stu_id=$stu_id LIMIT 1 ";
		return $db->fetchRow($sql);

	}

	function getCustomerinfoById($stu_id)
	{//for student with result
		$db = $this->getAdapter();
		$currentLang = $this->currentlang();
		$colunmname = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'title';
		}
		$sql = "SELECT s.*,
	   	'N/A' as group_name,
	   	'N/A' as degree_culture,
	   	 0 AS total_amountafter,
	   	 0 AS credit_memo_id,
	   	'N/A' as grade_label,
	   	'N/A' as degree_label,
	   	'N/A' AS degree,
	   	'N/A' AS grade,
	   	'N/A' AS session,
	   	'N/A' AS status_student,
	   	'N/A' as session_label,
	   	'1' AS is_newstudent,
	   	'N/A' AS room_label,
		'N/A' AS academicYearEnroll
   	FROM rms_student as s
   	WHERE
	    s.customer_type=2
	   	AND s.stu_id=$stu_id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	/*tested student*/
	function getAllstudentTest($data)
	{//get all
		$db = $this->getAdapter();

		$sql = "SELECT stu_id as id,CONCAT(COALESCE(serial,'-'),COALESCE(stu_khname,''),' [',last_name,' ',stu_enname,']') AS name
	   			FROM rms_student,
	   			rms_student_test_result AS result
	   		WHERE
		   		stu_id = result.stu_test_id 
		   		AND result.updated_result=1 
		   		AND (stu_khname!='' OR stu_enname!='') 
	   			AND status=1  
	   			AND customer_type=4 "; //AND customer_type=4
		$sql .= " AND is_studenttest=1 ";


		if (!empty($data['branch_id'])) {
			$sql .= " AND rms_student.branch_id = " . $data['branch_id'];
		}
		if (!empty($data['issueResult'])) {
			$sql .= " AND result.updated_result=1";
		}

		$sql .= " GROUP BY result.stu_test_id ORDER BY rms_student.stu_id DESC";

		return $db->fetchAll($sql);
	}
	function getAllstudentTestForFrmRestult($branch = null, $result = 0)
	{//get all
		$db = $this->getAdapter();
		$branch_id = $this->getAccessPermission();
		$sql = "SELECT stu_id as id,CONCAT(COALESCE(serial,'-'),COALESCE(stu_khname,''),' [',last_name,' ',stu_enname,']') AS name
   		FROM rms_student
   		WHERE
   		(stu_khname!='' OR stu_enname!='') AND status=1  $branch_id  "; //AND customer_type=4
		$sql .= " AND is_studenttest=1 ";
		if (!empty($branch)) {
			$sql .= " AND branch_id = $branch";
		}
		$sql .= " GROUP BY stu_id ORDER BY stu_id DESC";

		return $db->fetchAll($sql);
	}
	/*crm student*/
	function getAllCrmstudent($branch = null, $type)
	{//get all
		$db = $this->getAdapter();
		$branch_id = $this->getAccessPermission();
		$sql = "SELECT stu_id as id,CONCAT(COALESCE(stu_khname,''),' [',COALESCE(stu_enname,''),' ',COALESCE(last_name,''),']') AS name
   	FROM rms_student
   	WHERE (stu_khname!='' OR stu_enname!='') AND status=1 AND customer_type=3 $branch_id  ";
		if (!empty($branch)) {
			$sql .= " AND branch_id = $branch ";
		}
		$sql .= " ORDER BY stu_id DESC";
		return $db->fetchAll($sql);
	}

	function getDeduct()
	{
		$db = $this->getAdapter();
		$sql = " SELECT value FROM `ln_system_setting` WHERE id=19 ";
		return $db->fetchOne($sql);
	}
	public function getExpenseRecieptNo()
	{
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$sql = "SELECT id FROM ln_expense WHERE 1 ORDER BY id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no = (int) $acc_no + 1;
		$acc_no = strlen((int) $acc_no + 1);
		$pre = 0;
		for ($i = $acc_no; $i < 6; $i++) {
			$pre .= '0';
		}
		return $pre . $new_acc_no;
	}

	function getAllGeneration($opt = null, $option = null)
	{
		$db = $this->getAdapter();
		$sql = "SELECT DISTINCT(generation) AS generation FROM `rms_tuitionfee` WHERE generation!=''ORDER BY id DESC ";
		$rows = $db->fetchAll($sql);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		if ($opt == null) {
			return $rows;
		} else {
			if ($option != null) {
				$opt_gen = array(-1 => $tr->translate("SELECT_TYPE"));
			} else {
				$opt_gen = array();
			}
			if (!empty($rows))
				foreach ($rows as $row)
					$opt_gen[$row['generation']] = $row['generation'];
			return $opt_gen;
		}
	}
	/**notification alert*/
	//    function gettokenbystudentid($student_id){
//    	$sql="SELECT token FROM `mobile_mobile_token` WHERE stu_id=$student_id";
//    	return $this->getAdapter()->fetchAll($sql);
//    }
	function getTokenbyGroupId($groupId)
	{ //get android token by groupid
		$db = $this->getAdapter();
		$sql_android = "SELECT mb.`token`
				FROM `rms_group_detail_student` AS sd
					,`mobile_mobile_token` AS mb
				WHERE 
					sd.itemType=1 AND
					sd.`group_id` = " . $groupId . "
					AND sd.`stu_id` = mb.`stu_id`
					AND stop_type=0
					AND mb.device_type=1 ";
		return $db->fetchCol($sql_android);
	}
	function getTokenbyStudentId($studentId)
	{ //get android token by groupid
		$db = $this->getAdapter();
		$sql_android = "SELECT t.`token` FROM `mobile_mobile_token` AS t WHERE device_type=1 AND t.`stu_id` IN(" . $studentId . ") LIMIT 1 ";
		return $db->fetchCol($sql_android);
	}
	function getTokenbyDegreeId($degreeId)
	{ //get android token by groupid
		$db = $this->getAdapter();
		$sql_android = "SELECT 
        				mb.`token`
					  FROM 
		        		`rms_group_detail_student` AS sd
		        		,`mobile_mobile_token` AS mb
		        			WHERE 
							sd.itemType=1 AND
							sd.`degree` = " . $degreeId . "
		        		AND sd.`stu_id` = mb.`stu_id`
		        		AND stop_type=0
		        		AND sd.is_subspend=0
		        		AND mb.device_type=1 ";
		return $db->fetchCol($sql_android);
	}

	function setTitleTonotification($title_id)
	{
		$db = $this->getAdapter();
		$title_label = array(
			1 => 'lbl_smspaymentpaid',
			2 => 'lbl_smsatt',
			3 => 'lbl_smsmistake',
			4 => 'lbl_smsscore',
			5 => 'lbl_smsnews',
			6 => 'lbl_smsnotification',
			7 => 'lbl_paymentnotification',
			8 => 'lbl_replyfeedback',
		);
		$sql = "SELECT keyValue FROM `moble_label` 
   		WHERE keyName='" . $title_label[$title_id] . "'";
		return $db->fetchOne($sql);
	}
	function pushNotification($student = null, $groupId = null, $urlType, $titleId = null, $textTitle = null)
	{//$urlType 1payment,2score,3att,4discipline,5news
		return 11;//close notification
		if (AUTO_PUSH_NOTIFICATION == 0) {
			return false;
		}
		$db = $this->getAdapter();

		if ($textTitle != null) {
			$title = $textTitle;
		} else {
			$title = $this->setTitleTonotification($titleId);
		}



		$content = array(
			"en" => $title,
		);

		$data_notify = array(
			"title" => $title,
			"urlType" => $urlType
		);

		if (!empty($groupId)) {//select by gorup
			$androidToken = $this->getTokenbyGroupId($groupId);

			$fields = array(
				'app_id' => APP_ID,
				'include_player_ids' => $androidToken,
				'data' => $data_notify,
				'contents' => $content
			);

		} else if (!empty($student)) {//by student id
			$androidToken = $this->getTokenbyStudentId($student);
			$fields = array(
				'app_id' => APP_ID,
				'include_player_ids' => $androidToken,
				'data' => $data_notify,
				'contents' => $content
			);
			//    			$this->pushSendNotification($androidToken, $iosToken, $title_id);
		} else {//all 
			$fields = array(
				'app_id' => APP_ID,
				'included_segments' => array('Active Users'),
				'data' => $data_notify,
				'contents' => $content,
				'headings' => $content,
				//'subtitle'=>array('en'=>$data['descriptionKhmer']),
				//'template_id'=>'a13d6177-c4fd-4ba0-addf-07d9557b0460'
			);
		}

		//    		print_r($androidToken);exit();
		$fields = json_encode($fields);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt(
			$ch,
			CURLOPT_HTTPHEADER,
			array(
				'Content-Type: application/json; charset=utf-8',
				'Authorization: Basic OGY3MGQ2M2EtMmQ3OS00MjZhLTk2MjYtYjYzMzExYTg5YWRm'
			)
		);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);

	}

	//    function pushSendNotification($studentToken,$title_id,$body='',$data=''){//$stu_id//google
//    	$key = new Application_Model_DbTable_DbKeycode();
//    	$dbset=$key->getKeyCodeMiniInv(TRUE);
//    	if($dbset['notification']==0){return 1;}
//    	$url = "https://fcm.googleapis.com/fcm/send";
//    	$serverKey = 'AAAAFUl5wqk:APA91bFktDCO937lkDQ1JnP3fT5xT9YMfdEmBq0GH-QZs-GUGy9YbceyMvLQHNw3LBkgPbV9tZfDmzjti6oaJQyVHzhWrBmvoTdUaNhvD-q5DC3KNunJMVjDRTG3VLPrBVB8c8H9NNb_';

	//    	$title = $this->setTitleTonotification($title_id);

	//    	$data=array('id'=>1,
//    			'title'=>$title,
//    			'body'=>$body,
//    			'type'=>$title_id
//    	);

	//    	$notification = array(
//    			'title' =>$title ,
//    			'text' => $body,
//    			'sound' => 'psis',
//    			'badge' => '1'
//    	);

	//    	$countToken = count($studentToken);
//    		if ($countToken>0){
// 	   			$arrayToSend = array(
// 	   					'registration_ids' => $studentToken,
// 	   					'notification' => $notification,
// 	   					'priority'=>'high',
// 	   					'data' =>$data
// 	   			);
// 	   			$json = json_encode($arrayToSend);
// 	   			$headers = array();
// 	   			$headers[] = 'Content-Type: application/json';
// 	   			$headers[] = 'Authorization: key='. $serverKey;
// 	   			$ch = curl_init();
// 	   			curl_setopt($ch, CURLOPT_URL, $url);
// 	   			curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
// 	   			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
// 	   			curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
// 	   			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
// 	   			//Send the request
// 	   			$response = curl_exec($ch);
// 	   			//Close request
// 	   			if ($response === FALSE) {
// 	   				//die('FCM Send Error: ' . curl_error($ch));
// 	   			}
// 	   			curl_close($ch);
// 	   			//curl_setopt(CURLOPT_RETURNTRANSFER, true);
//    		}
//    }
	function pushSendNotification($android_token, $ios_token, $title_id)
	{
		$title = $this->setTitleTonotification($title_id);
		$content = array(
			"en" => $title
		);
		$fields = array(
			'app_id' => "cf9d5315-4a18-45fc-b0e2-78f7eda3eb1e",
			'include_android_reg_ids' => $android_token,
			'include_ios_tokens' => $ios_token,
			'data' => array(
				"title" => $title,
				"type" => $title_id
			),
			'contents' => $content
		);

		$fields = json_encode($fields);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt(
			$ch,
			CURLOPT_HTTPHEADER,
			array(
				'Content-Type: application/json; charset=utf-8',
				'Authorization: Basic NmQ0ZjE1ZWMtNTJmMC00MTkwLTk3NjktMGFlY2JkOTY4NDEz'
			)
		);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);
	}
	function sendMessageAllDevice($title_id)
	{
		$title = $this->setTitleTonotification($title_id);
		$content = array(
			"en" => $title
		);
		$fields = array(
			'app_id' => "cf9d5315-4a18-45fc-b0e2-78f7eda3eb1e",
			'included_segments' => array(
				'All'
			),
			'data' => array(
				"title" => $title,
				"type" => $title_id
			),
			'contents' => $content
		);

		$fields = json_encode($fields);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt(
			$ch,
			CURLOPT_HTTPHEADER,
			array(
				'Content-Type: application/json; charset=utf-8',
				'Authorization: Basic NmQ0ZjE1ZWMtNTJmMC00MTkwLTk3NjktMGFlY2JkOTY4NDEz'
			)
		);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);

		exit();
	}

	//function for push all device

	function resizeImase($image, $part, $new_imagename = null)
	{
		$photo = $image;
		$temp = explode(".", $photo["name"]);
		$new_name = $temp[0] . end($temp);
		if (!empty($new_imagename)) {
			$new_name = $new_imagename;
		}
		move_uploaded_file($image["tmp_name"], $part . $new_name);

		$uploadimage = $part . $new_name;
		//    $newname=$image["name"];
//    // Load the stamp and the photo to apply the watermark to
		if (end($temp) == 'jpg') {
			$im = imagecreatefromjpeg($uploadimage);
		} else
			if (end($temp) == 'jpeg') {
				$im = imagecreatefromjpeg($uploadimage);
			} else
				if (end($temp) == 'png') {
					$im = imagecreatefrompng($uploadimage);
				} else
					if (end($temp) == 'gif') {
						$im = imagecreatefromgif($uploadimage);
					}

		if ($image['size'] > (1000000 * 5)) {
			// Save the image to file and free memory quality 70%
			imagejpeg($im, $uploadimage, 10);
		} else if ($image['size'] > (1200000)) {
			imagejpeg($im, $uploadimage, 15); //quality 50%
		} else if ($image['size'] > (1000000)) {
			imagejpeg($im, $uploadimage, 20); //quality 50%
		} else if ($image['size'] > 512000) {
			// Save the image to file and free memory quality 80%
			imagejpeg($im, $uploadimage, 60);
		} else if ($image['size'] >= 102400) {
			// Save the image to file and free memory quality 90%
			imagejpeg($im, $uploadimage, 70);
		}else{
			imagejpeg($im, $uploadimage, 70);
		}
		//  imagedestroy($uploadimage);
		return $new_name;
	}

	public function getAllSubjectName($data = array())
	{
		$db = $this->getAdapter();
		$lang = $this->currentlang();

		$field = 'subject_titleen';
		if ($lang == 1) {
			$field = 'subject_titlekh';
		}

		$schooloption = empty($data["schooloption"]) ? null : $data["schooloption"];
		$typesubject = empty($data["typesubject"]) ? 0 : $data["typesubject"];

		$sql = " SELECT subj.id ,
			CONCAT(subj.$field,CASE WHEN subj.subject_lang =1 THEN '(ខ្មែរ)' WHEN subj.subject_lang =2 THEN '(English)' ELSE '' END) AS name,
  			subj.shortcut 
  		  ";

		$sql .= " FROM `rms_subject` AS subj  ";

		$where = "";
		$groupBY = "";
		if (!empty($data["groupId"])) {
			$sql .= " JOIN rms_group_subject_detail AS gsd ON gsd.subject_id = subj.id ";
			$where .= " AND gsd.group_id =" . $data["groupId"];
			if (!empty($data["teacherId"])) {
				$where .= " AND gsd.teacher =" . $data["teacherId"];
			}
			$groupBY = " GROUP BY gsd.subject_id ";
		}

		$sql .= " WHERE subj.status=1 AND subj.subject_titlekh != '' ";


		if ($typesubject == 1) {
			$sql .= ' AND subj.type_subject=1 ';
		}

		$user = $this->getUserInfo();
		$level = $user['level'];

		if ($level != 1) {
			$SchoolOptionarr = $this->getAllSchoolOption();
			if (!empty($SchoolOptionarr)) {
				$s_where = array();
				foreach ($SchoolOptionarr as $i) {
					$s_where[] = $i['id'] . " IN (subj.schoolOption)";
				}
				$sql .= ' AND ( ' . implode(' OR ', $s_where) . ')';
			}

			if (!empty($user['schoolOption'])) {
				$userSchO = explode(",", $user['schoolOption']);
				$s_wheres = array();
				foreach ($userSchO as $schooloptionId) {
					$s_wheres[] = $schooloptionId . " IN (subj.schoolOption)";
				}
				$sql .= ' AND ( ' . implode(' OR ', $s_wheres) . ')';
			}
		}

		if (!empty($schooloption)) {
			$schooloptionParam = explode(",", $schooloption);
			$s_whereee = array();
			foreach ($schooloptionParam as $schooloptionId) {
				$s_whereee[] = $schooloptionId . " IN (schoolOption)";
			}
			$sql .= ' AND ( ' . implode(' OR ', $s_whereee) . ')';
		}
		$sql .= $where;
		$sql .= $groupBY;
		$sql .= " ORDER BY subj.subject_lang ASC, subj.$field ASC ";
		$result = $db->fetchAll($sql);

		if (!empty($data['isOption'])) {

			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$options = '<option value="" >' . $tr->translate("ADD_NEW") . '</option>';
			$options .= '<option value="0" ></option>';
			if (!empty($result))
				foreach ($result as $value) {
					$options .= '<option value="' . $value['id'] . '" >' . htmlspecialchars($value['name'], ENT_QUOTES) . '</option>';
				}
			return $options;
		}
		return $result;
	}

	public function getAllSubjectStudy($schoolOption = null)
	{
		$arrayFilter = array(
			"typesubject" => 1,
			"schoolOption" => $schoolOption,
		);
		return $this->getAllSubjectName($arrayFilter);
	}
	public function getAllTeahcerName($data = array())
	{

		$branch_id = empty($data["branch_id"]) ? null : $data["branch_id"];
		$schooloption = empty($data["schooloption"]) ? null : $data["schooloption"];
		$stuff_type = empty($data["stuff_type"]) ? null : $data["stuff_type"];

		$db = $this->getAdapter();
		$sql = " SELECT 
			t.id ,
			t.teacher_name_kh AS name 
		FROM `rms_teacher` AS t ";
		$where = "";
		$groupBY = "";
		if (!empty($data["groupId"])) {
			$sql .= " JOIN rms_group_subject_detail AS gsd ON gsd.teacher = t.id ";
			$where .= " AND gsd.group_id =" . $data["groupId"];
			$groupBY = " GROUP BY gsd.teacher ";
		}
		$sql .= "	WHERE t.status=1 AND t.teacher_name_kh != '' AND t.active_type=0 ";
		if (!empty($branch_id)) {
			$sql .= " AND t.branch_id = $branch_id";
		}
		if (!empty($data["department"])) {
			$sql .= " AND t.department = " . $data["department"];
		}
		if (!empty($stuff_type)) {
			$sql .= " AND t.staff_type = $stuff_type";
		}
		$sql .= $where;

		$user = $this->getUserInfo();
		$level = $user['level'];
		if ($level != 1) {
			if (!empty($user['schoolOption'])) {
				$userSchO = explode(",", $user['schoolOption']);
				$s_wheres = array();
				foreach ($userSchO as $schooloptionId) {
					$s_wheres[] = $schooloptionId . " IN (t.schoolOption)";
				}
				$sql .= ' AND ( ' . implode(' OR ', $s_wheres) . ')';
			}
		}
		if (!empty($schooloption)) {
			$schooloptionParam = explode(",", $schooloption);
			$s_whereee = array();
			foreach ($schooloptionParam as $schooloptionId) {
				$s_whereee[] = $schooloptionId . " IN (t.schoolOption)";
			}
			$sql .= ' AND ( ' . implode(' OR ', $s_whereee) . ')';
		}
		$sql .= $this->getAccessPermission("t.branch_id");
		$sql .= $groupBY;
		$sql .= " ORDER BY t.id DESC";
		return $db->fetchAll($sql);
	}
	public function getAllTeacherOption($schoolOption = null, $branch_id = null, $addNew = null)
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$arrayFilter = array(
			"branch_id" => $branch_id,
			"schoolOption" => $schoolOption
		);
		$teacher = $this->getAllTeahcerName($arrayFilter);
		if (!empty($addNew)) {
			array_unshift($teacher, array('id' => -1, "name" => $tr->translate("ADD_NEW")));
		}
		$teacher_options = '<option value="0">' . $tr->translate("PLEASE_SELECT") . '</option>';
		if (!empty($teacher))
			foreach ($teacher as $value) {
				$teacher_options .= '<option value="' . $value['id'] . '" >' . htmlspecialchars($value['name'], ENT_QUOTES) . '</option>';
			}
		return $teacher_options;
	}

	public function getAllDayName()
	{
		$db = $this->getAdapter();
		$lang = $this->currentlang();
		$field = 'name_en';
		if ($lang == 1) {
			$field = 'name_kh';
		}
		$sql = " SELECT key_code as id ,$field AS name FROM `rms_view` WHERE status=1 AND name_en!= '' AND TYPE=18";
		return $db->fetchAll($sql);
	}

	function getAllStudyPeriod($data)
	{
		$db = $this->getAdapter();
		$sql = " SELECT id,
			CONCAT(title,' ( ',DATE_FORMAT(start_date, '%d/%m/%Y'),' - ',DATE_FORMAT(end_date, '%d/%m/%Y'),' )') as name, 
			title,
			academic_year AS academicYear,
			periodId,
			degreeId,
			DATE_FORMAT(start_date, '%Y-%m-%d') startDate,
			DATE_FORMAT(end_date, '%Y-%m-%d') endDate
  		FROM rms_startdate_enddate WHERE status=1 AND forDepartment=1 AND degreeId!='' ";
		if (!empty($data['branch_id'])) {
			$sql .= " AND branch_id = " . $data['branch_id'];
		}
		if (!empty($data['academic_year'])) {//correct
			$sql .= " AND academic_year = " . $data['academic_year'];
		}
		if (!empty($data['feeId'])) {//correct
			$result = $this->getFeeStudyinfoByIdGloable($data['feeId']);
			$academicYear = $result['academicYearId'];
			if (!empty($academicYear)){
				$sql .= " AND academic_year = " . $academicYear;
			}
		}
		if (!empty($data['periodId'])) {//correct
			$sql .= " AND periodId = " . $data['periodId'];
		}
		if (!empty($data['degree'])) {//correct
			$degreeId = $data['degree'];
			$sql .= " AND  FIND_IN_SET($degreeId,degreeId) ";
		}
		if (!empty($data['grade'])) {//correct
			if (!empty($data['serviceType']) and $data['serviceType'] == 1) {// SCHOOL FEE ONLY 
				$sql .= " AND FIND_IN_SET((SELECT items_id FROM `rms_itemsdetail` WHERE id=" . $data['grade'] . " ), degreeId) ";
			}
			$lastPaidTuitionInfo = array();
			if(!empty($data['studentId'])){
				$lastPaidTuitionInfo = $this->getStuLastPaidTutionFee($data);
			}
			if(!empty($lastPaidTuitionInfo)){
				if(!empty($lastPaidTuitionInfo["termId"])){
					if($data['grade'] == $lastPaidTuitionInfo["itemdetail_id"]){
						$sql .= " AND id NOT IN (" . $lastPaidTuitionInfo['termId'].")";
					}
				}
			}
		}

		$sql .= " ORDER BY academic_year DESC ";
		$rows = $db->fetchAll($sql);
		//Application_Model_DbTable_DbUserLog::writeMessageError($sql);
		if (empty($data['option'])) {
			return $rows;
		} else {
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$options = " <option value=''>" . $tr->translate("SELECT_TERM") . "</option> ";
			if (!empty($rows)) {
				foreach ($rows as $indexKey => $row) {
					$selected="";
					if($indexKey==0){
						$selected="selected";
					}
					$options .= '<option '.$selected.' data-start-date="'.htmlspecialchars($row['startDate']).'" data-end-date="'.htmlspecialchars($row['endDate']).'" data-title="'.htmlspecialchars($row['title']).'"  value="' . $row['id'] . '" >' . htmlspecialchars($row['name'], ENT_QUOTES) . '</option>';
				}
			}
			return $options;
		}

	}
	function getDiscountOptionList($data)
	{
	
	}
	function getAllTerm()
	{
		$db = $this->getAdapter();
		$sql = "select key_code as id , name_en as name from rms_view where type=6 and status=1 ";
		return $db->fetchAll($sql);
	}
	function getTestStudentId($branch = null)
	{
		$db = $this->getAdapter();

		$typeSerial = STU_SERIAL_TYPE;

		$sql = "SELECT COUNT(stu_id) AS number FROM `rms_student` WHERE  is_studenttest =1 ";//customer_type = 4
		if (!empty($branch)) {
			$sql .= " AND branch_id=$branch";
		}
		$sql .= " ORDER BY stu_id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);

		$new_acc_no = (int) $acc_no + 1;
		$acc_no = strlen((int) $acc_no + 1);
		$pre = "T";
		$branch_prefix = "";
		if (!empty($branch)) {
			$row = $this->getBranchInfo($branch);
			if (!empty($row['prefix'])) {
				$pre .= $row['prefix'];
			}
		}
		if ($typeSerial == 2) {
			$pre = "";
		}

		for ($i = $acc_no; $i < 6; $i++) {
			$pre .= '0';
		}

		$last = '';
		return $pre . $new_acc_no . $last;
	}
	function getallTermtest()
	{
		$db = $this->getAdapter();
		$sql = "select start_date,end_date,note,
  		CONCAT(note,'(',start_date,' to ',end_date,')') as id,
  		CONCAT(note,'(',start_date,' to ',end_date,')') as name
  	 FROM rms_test_term ";
		return $db->fetchAll($sql);
	}

	function addLangLevel($data)
	{
		$array = array(
			"title" => $data['title'],
			"user_id" => $this->getUserId(),
			"modify_date" => date("Y-m-d"),
		);
		$this->_name = "rms_degree_language";
		$langId = $this->checklangLevel($data['title']);
		if (empty($langId)) {
			$id = $this->insert($array);
			$result = array(
				"addNew" => 1,
				"id" => $id,
			);
			return $result;
		} else {
			$result = array(
				"addNew" => 0,
				"id" => $langId,
			);
			return $result;
		}


	}
	function checklangLevel($title)
	{
		$db = $this->getAdapter();
		$sql = "SELECT id FROM `rms_degree_language` WHERE  title = '" . $title . "' limit 1";
		return $db->fetchOne($sql);

	}

	function addNationType($data)
	{
		try {

			$key_code = $this->getLastKeycodeByType(21);

			$arr = array(
				'name_en' => $data['title_en'],
				'name_kh' => $data['title_kh'],
				'status' => $data['status_na'],
				'key_code' => $key_code,
				'displayby' => 1,
				'type' => 21,
			);
			$this->_name = "rms_view";
			$nationId = $this->checkNation($data['title_kh']);
			if (empty($nationId)) {
				$this->insert($arr);
				$result = array(
					"addNew" => 1,
					"id" => $key_code,
				);
			} else {
				$result = array(
					"addNew" => 0,
					"id" => $nationId,
				);
			}
			return $result;
		} catch (Exception $e) {
			echo '<script>alert(' . "$e" . ');</script>';
		}
	}
	function checkNation($title)
	{
		$db = $this->getAdapter();
		$sql = "SELECT key_code FROM `rms_view` WHERE type=21 AND name_kh = '" . $title . "' limit 1";

		return $db->fetchOne($sql);
	}
	function getLastKeycodeByType($type)
	{
		$sql = "SELECT key_code FROM `rms_view` WHERE type=$type ORDER BY id DESC LIMIT 1 ";
		$db = $this->getAdapter();
		$number = $db->fetchOne($sql);
		return $number + 1;
	}

	function addKnowBy($data)
	{
		$array = array(
			"title" => $data['title_know_by'],
			"user_id" => $this->getUserId(),
			"create_date" => date("Y-m-d H:i:s"),
		);
		$this->_name = "rms_know_by";
		$khnow_by = $this->checkKnownBy($data['title_know_by']);
		if (empty($khnow_by)) {
			$id = $this->insert($array);
			$result = array(
				"addNew" => 1,
				"id" => $id,
			);
		} else {
			$result = array(
				"addNew" => 0,
				"id" => $khnow_by,
			);
		}
		return $result;
	}

	function checkKnownBy($title)
	{
		$db = $this->getAdapter();
		$sql = "SELECT id FROM `rms_know_by` WHERE  title = '" . $title . "' limit 1";

		return $db->fetchOne($sql);
	}

	function addDocstudentType($data)
	{
		$array = array(
			"name" => $data['title_doc_type'],
			"types" => 1,
			"user_id" => $this->getUserId(),
			"create_date" => date("Y-m-d H:i:s"),
		);
		$this->_name = "rms_document_type";
		return $this->insert($array);
	}

	function addDoctecherType($data)
	{
		$array = array(
			"name" => $data['title_doc_type'],
			"types" => 2,
			"user_id" => $this->getUserId(),
			"create_date" => date("Y-m-d H:i:s"),
		);
		$this->_name = "rms_document_type";
		return $this->insert($array);
	}
	public function getLaguage()
	{
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `ln_language` AS l WHERE l.`status`=1 ORDER BY l.ordering ASC";
		return $db->fetchAll($sql);
	}

	//New Edition
	function getBranchInfo($branch_id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT b.* FROM `rms_branch` AS b WHERE b.br_id = $branch_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getSchoolOptionListByBranch($branchlist)
	{
		$db = $this->getAdapter();
		$sql = "SELECT  GROUP_CONCAT(DISTINCT schooloptionlist)  FROM rms_branch  WHERE br_id IN($branchlist)";
		$rs = $db->fetchOne($sql);
		$str = implode(',', array_unique(explode(',', $rs)));
		return $str;
	}
	function getAllSchoolOption($branchlist = null, $show_university = 1)
	{
		$db = $this->getAdapter();
		$this->_name = "rms_schooloption";
		$sql = "SELECT s.id, s.title AS name FROM $this->_name AS s WHERE s.status = 1 ";
		$user = $this->getUserInfo();
		$level = $user['level'];
		if ($level != 1) {
			$session_user = new Zend_Session_Namespace(SYSTEM_SES);
			$branchlist = empty($branchlist) ? $session_user->branch_list : $branchlist;
			if ($branchlist != null) {
				$schooloption = $this->getSchoolOptionListByBranch($branchlist);
				$sql .= ' AND s.id IN (' . $schooloption . ')';
			}
			if (!empty($user['schoolOption'])) {
				$sql .= ' AND s.id IN (' . $user['schoolOption'] . ')';
			}
		}
		if ($show_university == 0) {
			$sql .= " and s.id != 3 ";
		}
		return $db->fetchAll($sql);
	}
	function getAllDegreetype()
	{
		$db = $this->getAdapter();
		$this->_name = "rms_itemstype";
		$sql = "SELECT s.id, s.title AS name FROM $this->_name AS s WHERE s.status = 1";
		return $db->fetchAll($sql);
	}

	function getAllItems($type = null, $branchlists = null, $schooloption = null,$parent = 0, $spacing = '', $cate_tree_array = '',$isparent=null,$optionSelect=null)
	{
		$db = $this->getAdapter();
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();

		$currentLang = $this->currentlang();
		$colunmname = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'title';
		}

		$this->_name = "rms_items";
		$sql = "SELECT m.id, m.$colunmname AS name,m.is_parent FROM $this->_name AS m WHERE m.status=1 ";
		$type = empty($type) ? 0 : $type;
		if($type!=1){ // not tuition
			$sql .= " AND m.parent=$parent";
		}else{
			$sql .= " AND COALESCE(m.is_parent,0) = 0 ";
		}
		
		if (!empty($type)) {
			$sql .= " AND m.type=$type";
		}
		
		$user = $this->getUserInfo();
		$level = $user['level'];
		if ($level != 1) {
			$SchoolOptionarr = $this->getAllSchoolOption($branchlists);
			if (!empty($SchoolOptionarr)) {
				$s_where = array();
				foreach ($SchoolOptionarr as $i) {
					$s_where[] = $i['id'] . " IN (m.schoolOption)";
				}
				$sql .= ' AND ( ' . implode(' OR ', $s_where) . ')';
			}

			if (!empty($user['schoolOption'])) {
				$userSchO = explode(",", $user['schoolOption']);
				$s_wheres = array();
				foreach ($userSchO as $schooloptionId) {
					$s_wheres[] = $schooloptionId . " IN (m.schoolOption)";
				}
				$sql .= ' AND ( ' . implode(' OR ', $s_wheres) . ')';
			}

			$userSelect = $this->getUserProfile();
			if (!empty($userSelect['degreeList'])) {
				$degreeList = $userSelect['degreeList'];
				$sql .= "
				AND 
					CASE  WHEN m.`type` !=1 THEN '1' 
					ELSE m.id IN (" . $degreeList . ") 
					END
			";
			}
		}

		if (!empty($schooloption)) {
			$schooloptionParam = explode(",", $schooloption);
			$s_whereee = array();
			foreach ($schooloptionParam as $schooloptionId) {
				$s_whereee[] = $schooloptionId . " IN (m.schoolOption)";
			}
			$sql .= ' AND ( ' . implode(' OR ', $s_whereee) . ')';
		}
		$sql .= ' ORDER BY m.schoolOption ASC,m.type ASC,m.ordering DESC, m.title ASC';
		
		$query = $db->fetchAll($sql);
		if($type!=1){
			$rowCount = count($query);
			$id = '';
			if ($rowCount > 0) {
				foreach ($query as $row) {
					$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
					if(!$isparent!=null){
						$cate_tree_array = $this->getAllItems($type, $branchlists, $schooloption,$row['id'], $spacing . ' - ', $cate_tree_array);
					}
				}
			}
			if ($optionSelect != null) {
				$options = '';
				if (!empty($cate_tree_array))
					foreach ($cate_tree_array as $value) {
						$resultData = $this->getAllItems(null,null,null,$value['id'],'','',null,null);
						$options .= '<option data-jsondata="'.htmlspecialchars(json_encode($resultData)).'" value="' . $value['id'] . '" >' . htmlspecialchars($value['name'], ENT_QUOTES) . '</option>';
					}
				return $options;
			}
			return $cate_tree_array;
		}else{
			if ($optionSelect != null) {
				$options = '';
				if (!empty($query))
					foreach ($query as $value) {
						if(empty($value["is_parent"])){
							$options .= '<option data-jsondata="'.htmlspecialchars(json_encode($resultData)).'" value="' . $value['id'] . '" >' . htmlspecialchars($value['name'], ENT_QUOTES) . '</option>';
						}
					}
				return $options;
			}
			return $query;
		}
		
	}
	function getAllItemDetail($data = null)
	{
		$db = $this->getAdapter();
		$currentLang = $this->currentlang();
		$colunmname = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'title';
		}
		$sql = "
			SELECT i.id,
			  	CONCAT(i.$colunmname,' (',(SELECT it.$colunmname FROM `rms_items` AS it WHERE it.id = i.items_id LIMIT 1),')') AS name
			  	FROM 
			  		`rms_itemsdetail` AS i
			  	WHERE i.status =1 
			";
		if (!empty($data['itemsType'])) {//
			$sql .= " AND i.items_type=" . $data['itemsType'];
		}
		if (!empty($data['itemsTypeList'])) {//
			$sql .= " AND i.items_type IN (" . $data['itemsTypeList'].")";
		}
		if (!empty($data['itemId'])) {//
			//$sql .= " AND i.items_id=" . $data['itemId'];
			$arrCon = array(
				"categoryId" => $data['itemId'],
			);
			$condiction = $this->getChildItems($arrCon);
			if (!empty($condiction)){
				$sql.=" AND i.items_id IN ($condiction)";
			}else{
				$sql.=" AND i.items_id=".$data['itemId'];
			}
		}
		if (!empty($data['parentId'])) {//want to get product in this location
			$sql .= " AND  i.items_id IN (SELECT id FROM `rms_items` WHERE id is NOT NULL AND parent=" . $data['parentId'] . ")";
		}
		if (isset($data['isOnepayment'])) {
			$sql .= " AND i.is_onepayment=" . $data['isOnepayment'];
		}
		if (isset($data['isProductset'])) {
			$sql .= " AND i.is_productseat=" . $data['isProductset'];
		}
		if (!empty($data['proLocation'])) {//want to get product in this location
			//$sql .= " AND  i.id IN (SELECT pro_id FROM `rms_product_location` WHERE pro_id is NOT NULL AND branch_id=" . $data['proLocation'] . ")";
			$sql .= " 
				AND  
					CASE 
						WHEN i.`items_type` =3 THEN 
							i.id IN (SELECT pro_id FROM `rms_product_location` WHERE pro_id is NOT NULL AND branch_id=" . $data['proLocation'] . ")
						ELSE  1 
					End			
				";
		}
		if (!empty($data['includeProLocation'])) {//want to get product in this location
			$sql .= " OR ( i.id IN (SELECT pro_id FROM `rms_product_location` WHERE pro_id is NOT NULL AND branch_id=" . $data['includeProLocation'] . "))";
		}
		if (!empty($data['notLocation'])) {//want to get product in this location
			$sql .= " AND i.id NOT IN (SELECT pro_id FROM `rms_product_location` WHERE pro_id is NOT NULL AND branch_id=" . $data['notLocation'] . ")";
		}

		if (!empty($data['isService'])) {
			$sql .= " OR i.items_type=2 ";
		}
		$branchlist = $this->getAllSchoolOption();
		if (!empty($branchlist)) {
			foreach ($branchlist as $i) {
				$s_where[] = $i['id'] . " IN (i.schoolOption)";
			}
			$sql .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}
		$user = $this->getUserInfo();
		$level = $user['level'];
		if ($level != 1) {
			$sql .= ' AND i.schoolOption  IN (' . $user['schoolOption'] . ')';

			$userSelect = $this->getUserProfile();
			if (!empty($userSelect['degreeList'])) {
				$degreeList = $userSelect['degreeList'];
				$sql .= "
					AND 
						CASE  WHEN i.items_type != 1 THEN '1' 
						ELSE i.items_id IN (" . $degreeList . ") 
						END
					";
			}

		}
		$sql .= " ORDER BY i.ordering ASC , i.items_id ASC ";
		return $db->fetchAll($sql);
	}

	function getItemType($type)
	{
		$db = $this->getAdapter();
		$this->_name = "rms_itemstype";
		$sql = "SELECT * FROM $this->_name AS t WHERE t.id = $type LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getItemsDetailCodeByItemsType($type)
	{
		$db = $this->getAdapter();
		$this->_name = "rms_itemsdetail";
		$sql = "SELECT COUNT(id) AS number FROM $this->_name WHERE items_type =$type  LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$pre = $this->getItemType($type);
		$pre = empty($pre['prefix']) ? "" : $pre['prefix'];
		$new_acc_no = (int) $acc_no + 1;
		$acc_no = strlen((int) $acc_no + 1);
		for ($i = $acc_no; $i < 5; $i++) {
			$pre .= '0';
		}
		return $pre . $new_acc_no;
	}



	function getAllGradeStudyByDegree($data,$category_id = null, $student_id = null, $is_stutested = null, $groupDetailId = null)
	{
		$db = $this->getAdapter();
		$currentLang = $this->currentlang();
		$colunmname = 'i.title_en';
		if ($currentLang == 1) {
			$colunmname = 'i.title';
		}

		$sql = "SELECT i.id,
  		$colunmname AS name
  	FROM `rms_itemsdetail` AS i
  	WHERE i.status = 1 and product_type=1 ";
		// if ($category_id != null and $category_id > 0 and $category_id != '') {
		// 	$sql .= " AND i.items_id=" . $category_id;
		// }
		if (!empty($data['categoryId'])) {
			$sql .= " AND i.items_id=" .$data['categoryId'];
		}
		if (!empty($data['parentcagetoryId'])) {
			$sql .= " AND  i.items_id IN (SELECT id FROM `rms_items` WHERE id is NOT NULL AND parent=".$data['parentcagetoryId']. ")";
		}
		if (!empty($data['studentId'])) {
			$studentId = $data['studentId'];
			// if ($student_id != null and !empty($student_id)) {
			if (empty($data['isTestedStudent'])) {//for normal student
				if (empty($data['groupDetailId'])) {
					$groupDetailId = $data['groupDetailId'];
					$sql .= " AND (i.items_type =2 OR i.id IN (SELECT grade FROM `rms_group_detail_student` WHERE itemType=1 AND status=1 AND is_maingrade=1 AND stop_type=0 AND stu_id= $studentId )) ";
				} else {
					$sql .= " AND (i.items_type =2 OR i.id IN (SELECT grade FROM `rms_group_detail_student` WHERE itemType=1 AND status=1 AND stop_type=0 AND gd_id=$groupDetailId  AND stu_id= $studentId )) ";
				}
			} else {//will check expired of result test later //for tested student
				$sql .= " AND (i.items_type =2 OR i.id IN (SELECT grade_result FROM `rms_student_test_result` WHERE stu_test_id = $studentId GROUP By grade_result ))";
			}
		}
		$user = $this->getUserInfo();
		$level = $user['level'];
		if ($level != 1) {
			if (!empty($user['schoolOption'])) {
				$userSchO = explode(",", $user['schoolOption']);
				$s_wheres = array();
				foreach ($userSchO as $schooloptionId) {
					$s_wheres[] = $schooloptionId . " IN (i.schoolOption) ";
				}
				$sql .= ' AND ( ' . implode(' OR ', $s_wheres) . ')';
			}

			$branchlist = $this->getAllSchoolOption();
			if (!empty($branchlist)) {
				foreach ($branchlist as $i) {
					$s_where[] = $i['id'] . " IN (i.schoolOption)";
				}
				$sql .= ' AND ( ' . implode(' OR ', $s_where) . ')';
			}

			$userSelect = $this->getUserProfile();
			if (!empty($userSelect['degreeList'])) {
				$degreeList = $userSelect['degreeList'];
				$sql .= "
				AND 
					CASE  WHEN i.items_type != 1 THEN '1' 
					ELSE i.items_id IN (" . $degreeList . ") 
					END
				";
			}
		}
		$sql .= " GROUP BY i.id ORDER BY i.items_type ASC, i.ordering ASC , i.items_id DESC ";
		//Application_Model_DbTable_DbUserLog::writeMessageError($sql);
		return $db->fetchAll($sql);
	}
	function getProductbyBranch($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT t.id,title AS name 
  				FROM 
  					`rms_itemsdetail` AS t,
		      		`rms_product_location` as pl
			   WHERE t.items_type=3 AND t.status=1 
  					 AND ( t.is_productseat=1 OR (t.id=pl.pro_id)) ";
		$sql .= $this->getAccessPermission("pl.branch_id");

		if ($data['categoryId'] != null and $data['categoryId'] > 0) {
			//$sql .= ' AND t.items_id=' . $category_id;
			
			$arrCon = array(
				"categoryId" => $data['categoryId'],
				"itemsType" => 3,
			);
			$condiction = $this->getChildItems($arrCon);
			if (!empty($condiction)){
				$sql.=" AND t.items_id IN ($condiction)";
			}else{
				$sql.=" AND t.items_id=".$data['categoryId'];
			}
		}
		if (empty($product_type)) {
			$sql .= " AND t.product_type=1 ";
		}

		if (!empty($data['parentcagetoryId'])) {//want to get product in this location
			$sql .= " AND  t.items_id IN (SELECT id FROM `rms_items` WHERE id is NOT NULL AND parent=" . $data['parentcagetoryId'] . ")";
		}
		$sql .= " GROUP BY t.id ";

		
		return $db->fetchAll($sql);
	}
	public function getAllGradeStudyOption($type = 1)
	{
		$param = array(
			'itemsType' => $type
		);
		$rows = $this->getAllItemDetail($param);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		array_unshift($rows, array('id' => -1, 'name' => $tr->translate("PLEASE_SELECT")));
		$options = '';
		if (!empty($rows))
			foreach ($rows as $value) {
				$options .= '<option value="' . $value['id'] . '" >' . htmlspecialchars($value['name'], ENT_QUOTES) . '</option>';
			}
		return $options;
	}
	function getProcessTypeView()
	{
		$db = $this->getAdapter();
		$lang = $this->currentlang();
		$field = 'name_en';
		if ($lang == 1) {
			$field = 'name_kh';
		}
		$sql = "SELECT key_code AS id , $field AS `name` FROM rms_view  WHERE `status`=1 AND type=12";
		return $db->fetchAll($sql);
	}
	function getAllDiscountName()
	{
		$db = $this->getAdapter();
		$sql = "SELECT disco_id AS id,dis_name FROM `rms_discount` WHERE 
  		status=1 AND dis_name!='' 
  			ORDER BY dis_name ASC ";
		return $db->fetchAll($sql);
	}

	public function getAllNew($limit = null)
	{
		$db = $this->getAdapter();
		$user = $this->getUserInfo();
		$currentLang = $this->currentlang();
		$userid = empty($user['user_id']) ? 0 : $user['user_id'];
		$sql = "SELECT n.*,
	  		(SELECT CONCAT(b.branch_nameen) FROM rms_branch AS b WHERE b.br_id=n.branch_id LIMIT 1) AS branch_name,
	  		(SELECT nd.title FROM `ln_news_detail` AS nd WHERE nd.news_id =n.id AND nd.lang=$currentLang LIMIT 1 ) AS title,
	  		(SELECT nd.description FROM `ln_news_detail` AS nd WHERE nd.news_id =n.id AND nd.lang=$currentLang LIMIT 1 ) AS description,
			(SELECT nr.is_read FROM `ln_news__read` AS nr WHERE nr.new_feed_id = n.id AND nr.cus_id=$userid LIMIT 1) AS is_read
		 FROM `ln_news` AS n
		 	WHERE n.status = 1 ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where = $dbp->getAccessPermission("n.branch_id");
		if (!empty($where)) {
			$where = str_replace('AND', ' AND (', $where);
			$sql .= " $where OR  n.branch_id=0)";
		}

		$sql .= " AND n.publish_date<='" . date('Y-m-d') . "'";

		$sql .= "  ORDER BY 
		 (SELECT nr.is_read FROM `ln_news__read` AS nr WHERE nr.new_feed_id = n.id AND nr.cus_id=$userid LIMIT 1) ASC,
		 n.publish_date DESC, n.created_date DESC ";

		if (!empty($limit)) {
			if (!is_numeric($limit)) {
				$limit = 5;
			}
			$sql .= " LIMIT $limit";
		}
		return $db->fetchAll($sql);
	}

	public function getNewNotreadByUser()
	{
		$db = $this->getAdapter();
		$user = $this->getUserInfo();
		$userid = empty($user['user_id']) ? 0 : $user['user_id'];
		$sql = "SELECT COUNT(n.id) AS notread
		 FROM `ln_news` AS n
		 WHERE n.status = 1
		 AND
		 n.id NOT IN (SELECT nr.new_feed_id FROM `ln_news__read` AS nr WHERE nr.new_feed_id = n.id AND nr.cus_id=$userid )";
		return $db->fetchOne($sql);
	}
	public function getDetailNews($id)
	{
		$db = $this->getAdapter();
		$user = $this->getUserInfo();
		$currentLang = $this->currentlang();
		$userid = empty($user['user_id']) ? 0 : $user['user_id'];
		$sql = "SELECT n.*,
		  	(SELECT nd.title FROM `ln_news_detail` AS nd WHERE nd.news_id =n.id AND nd.lang=$currentLang LIMIT 1 ) AS title,
		  	(SELECT nd.description FROM `ln_news_detail` AS nd WHERE nd.news_id =n.id AND nd.lang=$currentLang LIMIT 1 ) AS description,
		  	(SELECT nr.is_read FROM `ln_news__read` AS nr WHERE nr.new_feed_id = n.id AND nr.cus_id=$userid LIMIT 1) AS is_read
	  	FROM `ln_news` AS n
	  		WHERE n.status = 1 
  				AND n.id = $id ";
		$dbp = new Application_Model_DbTable_DbGlobal();

		$where = $dbp->getAccessPermission("n.branch_id");
		if (!empty($where)) {
			$where = str_replace('AND', ' AND (', $where);
			$sql .= " $where OR  n.branch_id=0)";
		}
		$sql .= " AND n.publish_date<='" . date('Y-m-d') . "'";

		$sql .= "LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getPrefixByDegree($degree)
	{
		$db = $this->getAdapter();
		$sql = " SELECT shortcut FROM `rms_items` WHERE id=$degree LIMIT 1";
		return $db->fetchOne($sql);
	}
	function getnewStudentId($branch_id, $degree)
	{//used global
		$db = $this->getAdapter();

		$canEntry = Setting_Model_DbTable_DbGeneral::geValueByKeyName('entry_stuid');
		if ($canEntry == 1) {//entry by self not need generate for user
			return;
		}

		$prefixOpt = Setting_Model_DbTable_DbGeneral::geValueByKeyName('studentPrefixOpt');
		if ($prefixOpt == 1) {//branch
			$pre = $this->getPrefixCode($branch_id);//by branch
			
			$settingFor = 100; //PSIS CHV
			if($settingFor==100){
				$lastedAca = $this->getLatestAcadmicYear();
				if(!empty($lastedAca)){
					$fromYear = empty($lastedAca["fromYear"]) ? "" : $lastedAca["fromYear"]."-";
					$pre = $fromYear.$pre."-";
				}
			}
		} elseif ($prefixOpt == 2) {
			$pre = $this->getPrefixByDegree($degree);
		} elseif ($prefixOpt == 3) {
			$pre = '';//assign below 
		} else {//by entry
			$pre = Setting_Model_DbTable_DbGeneral::geValueByKeyName('studentIPrefix');
		}

		$idLength = Setting_Model_DbTable_DbGeneral::geValueByKeyName('studentIdLength');

		$option_type = Setting_Model_DbTable_DbGeneral::geValueByKeyName('settingStuID');// count type setting

		if ($option_type == 1) {//auto by branch
			$sql = "SELECT COUNT(stu_id) FROM `rms_student` WHERE status=1 AND customer_type=1 AND branch_id=" . $branch_id;
			$stu_num = $db->fetchOne($sql);
		} elseif ($option_type == 2) {

			//degree option for combine amount 5,6,7,8
			$arrDegreeComine = array(
				5 => 5,//IEAP
				6 => 6,//GESL
				7 => 7,//EHSS
				8 => 8//Pre-English
			);
			$sql = "
				SELECT COUNT(s.stu_id)
				FROM `rms_student` AS s
					JOIN `rms_group_detail_student` AS gds ON gds.stu_id = s.stu_id AND gds.is_maingrade=1
				WHERE s.branch_id=$branch_id 
				AND gds.itemType=1
				AND s.customer_type=1
			";
			if (!empty($arrDegreeComine[$degree])) {
				$s_where = array();
				foreach ($arrDegreeComine as $degree) {
					$s_where[] = " gds.degree = $degree ";
				}
				$sql .= ' AND ( ' . implode(' OR ', $s_where) . ')';
			} else {
				$sql .= " AND gds.degree= $degree ";
			}
			$stu_num = $db->fetchOne($sql);
		} elseif ($option_type == 3) {
			$dbdeg = new Global_Model_DbTable_DbItems();
			$type = 1;
			$row = $dbdeg->getDegreeById($degree, $type);
			$schoolOption = empty($row['schoolOption']) ? 0 : $row['schoolOption'];

			$info = $this->getSchoolOptionInfo($schoolOption);
			if (!empty($info['prefix'])) {
				$pre .= $info['prefix'];
			}
			$sql = "
				SELECT COUNT(s.stu_id)
				FROM `rms_student` AS s
					JOIN `rms_group_detail_student` AS gds ON gds.stu_id = s.stu_id AND gds.is_maingrade=1 
					LEFT JOIN `rms_items` AS deg ON deg.id = gds.degree AND deg.type=1
				WHERE s.branch_id=$branch_id 
				AND gds.itemType=1
				AND s.customer_type=1
			";
			$sql .= " AND deg.schoolOption = $schoolOption ";
			$stu_num = $db->fetchOne($sql);
		}
		$new_acc_no = (int) $stu_num + 1;
		$length = strlen((int) $new_acc_no);
		for ($i = $length; $i < $idLength; $i++) {
			$pre .= '0';
		}
		return $pre . $new_acc_no;
	}
	function getSchoolOptionInfo($id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT sopt.* FROM rms_schooloption AS sopt WHERE sopt.id = $id LIMIT 1";
		return $db->fetchRow($sql);
	}

	function getStudentProfileblog($student_id, $data_from = 1, $customer_type = 1)
	{
		$db = $this->getAdapter();

		if ($customer_type == 1) {//student
			if ($data_from == 1 or $data_from == 3) {//test ,student
				$rs = $this->getStudentinfoGlobalById($student_id);
			} elseif ($data_from == 2) {//test
				$rs = $this->getStudentTestinfoById($student_id);
			}
		} else {//customer//WHERE s.customer_type=2 
			$rs = $this->getCustomerinfoById($student_id);
		}
		$tr = $this->tr;
		$str = '';
		$studyInfo = '';
		$studentCode = array();
		$style = '';
		$link = Zend_Controller_Front::getInstance()->getBaseUrl() . '/home/searchstudentinfo/student-detail/id/' . $student_id;
		/*$student_type=$tr->translate("Old Student");
					   $style="style='color:white'";
					   if($rs["is_stu_new"]==1){
						   $student_type=$tr->translate("New Student");
						   $style="style='color:#99e5fd'";
					   }*/
		$str_parentinfo="";
		if (!empty($rs)) {

			$degree = empty($rs['degree']) ? '' : $rs['degree'];
			$grade = empty($rs['grade']) ? '' : $rs['grade'];
			$branchId = $rs['branch_id'];

			$studentData = array(
				'isSetStudentid' => empty($rs['is_setstudentid']) ? 0 : $rs['is_setstudentid'],
				'groupCode' => $rs['group_name'],
				'grade' => $grade,
				'degree' => $degree,
				'feeId' => empty($rs['fee_id']) ? '' : $rs['fee_id'],
				'groupId' => empty($rs['groupId']) ? 0 : $rs['groupId'],
				'creditAmountAfter' => empty($rs['total_amountafter']) ? '' : $rs['total_amountafter'],
				'creditMemoId' => empty($rs['credit_memo_id']) ? '' : $rs['credit_memo_id'],
			);

			$param = array(
				'branch_id' => $branchId,
				'degree' => $degree,
				'grade' => $grade,
				'noaddnew' => 1,
			);
			//NEW_STU_ID_FROM_TEST==0 should not call group
			$groupResult = $this->getAllGroupName($param);

			$studentCode = $this->getnewStudentId($branchId, $degree);

			$db = new Application_Form_FrmGlobal();
			$resultBranch = $db->getHeaderReceipt($branchId);

			$str = '<div class="col-md-3 col-sm-3 col-xs-12">
  						<div class="form-group">
  							<div class="thumb-xl member-thumb m-b-10 center-block">';
							$photo = Zend_Controller_Front::getInstance()->getBaseUrl() . "/images/no-profile.png";
							if ($rs['sex'] == 2) {
								$photo = Zend_Controller_Front::getInstance()->getBaseUrl() . "/images/no-profile-female.png";
							}
							if (!empty($rs["photo"])) {
								if (file_exists(PUBLIC_PATH . "/images/photo/" . $rs["photo"])) {
									$photo = Zend_Controller_Front::getInstance()->getBaseUrl() . "/images/photo/" . $rs["photo"];
								}
							}

						$str .= '<img src=' . $photo . ' class=" img-thumbnail" alt="profile-image" >';
						if ($rs["sex"] == 1) {
							$str .= '<input type="hidden" id="lbl_gender" value="Male"><i class="fa fa-male member-star text-active" title="verified user"></i>';
						} else {
							$str .= '<input type="hidden" id="lbl_gender" value="Female"><i class="fa fa-female member-star text-deactive" title="verified user"></i>';
						}
						$photo = $rs["photo"];
							$str .= '<input type="hidden" id="lbl_photoname" value="' . $photo . '" />
								</div>
							</div>
						</div>
						<div class="col-md-9 col-sm-9 col-xs-12">
							<div class="form-group">
								<div class="text-center">
									<div class="member-card card-display-reg">
										<p class="text-muted info-list font-13">
											<span class="title-info">' . $tr->translate("STUDENT_CODE") . '</span>: <span id="lbl_stucode" class="inf-value" ><a target="_blank" href="' . $link . '">' . $rs["stu_code"] . '</a></span>
											<span class="title-info">' . $tr->translate("STUDENT_NAMEKHMER") . '</span>: <span id="lbl_namekh" class="inf-value" ><a target="_blank" href="' . $link . '">' . $rs["stu_khname"] . '</a></span>
											<span class="title-info">' . $tr->translate("NAME_ENGLISH") . '</span>: <span id="lbl_nameen" class="inf-value" ><a target="_blank" href="' . $link . '">' . $rs["last_name"] . " " . $rs["stu_enname"] . '</a></span>
											<span class="title-info">' . $tr->translate("PHONE") . '</span>: <span id="lbl_phone" class="inf-value">' . $rs['tel'] . '</span>
											<span class="title-info">' . $tr->translate("Year Enrolled") . '</span>: <span id="lbl_enroll_year" class="inf-value groupinfo">' . $rs['academicYearEnroll'] . '</span>
											<span class="title-info">' . $tr->translate("STUDENT_TYPE") . '</span>: <span id="lbl_student_type" class="inf-value" >' . $rs['studentType'] . '</span>
										</p>
										<span class="title-info hidden">' . $tr->translate("DOB") . '</span><span id="lbl_dob" class="inf-value hidden" >: ' . $rs['dob'] . '</span>
										</div>
									</div>
								</div>
							</div>';

			$str_parentinfo='<p class="text-muted info-list font-13">
								<span class="title-info">' . $tr->translate("FATHER_NAME") . '</span> : <span id="lbl_father" class="inf-value">'.$rs['father_enname'] . '</span>
								<span id="lbl_fathertel" class="inf-value" style="display:none;">' . $rs['father_phone'] . '</span>
								<span id="lbl_mothertel" class="inf-value" style="display:none;">' . $rs['mother_phone'] . '</span>
								<span id="lbl_degreeId" class="inf-value" style="display:none;">' . $rs['degree'] . '</span>
								<span class="title-info">'. $tr->translate("MOTHER_NAME") . '</span> : <span id="lbl_mother" class="inf-value">'. $rs['mother_enname'] . '</span>
								<span class="title-info">'. $tr->translate("PARENT_PHONE") . '</span> : <span id="lbl_parentphone" class="inf-value">' . $rs['guardian_tel'] . '</span>
								<span class="title-info">'. $tr->translate("STATUS") . '</span> : <span id="lbl_culturelevel" class="inf-value red bold" >' . $rs['status_student'] . '</span>
							';
			$str_parentinfo.= '</p>';

			$studyInfo = '<p class="text-muted info-list font-13">
				             <span class="title-info">' . $tr->translate('DEGREE') . '</span> : <span id="lbl_degree" class="inf-value">' . $rs['degree_label'] . '</span>
				             <span class="title-info">' . $tr->translate('GRADE') . '</span> : <span id="lbl_grade" class="inf-value">' . $rs['grade_label'] . '</span>
				             <span class="title-info groupinfo">' . $tr->translate('GROUP') . '</span> : <span id="lbl_group" class="inf-value groupinfo">' . $rs['group_name'] . '</span>
							 <span class="title-info">' . $tr->translate('PART_TIME_LIST') . '</span> : <span id="lbl_part_time" class="inf-value">' . $rs['parttime_label'] . '</span>
					    </p>';
			$strStatus = ($rs['is_newstudent'] == 1) ? 'New Student' : 'Old Student';
			$studentStatus = '<span class="user-badge bg-warning">' . $strStatus . '</span>';

			$param = array(
				'studentId'=>$student_id,
				'degree'=>$degree,
				'fetchAll'=>1
			);

			$strDiscountList = '';
			$rsDiscount = $this->getDiscountListbyStudent($param);
			if (!empty($rsDiscount)) {
				$strDiscountList .= "<ul class='list-unstyled top_profiles scroll-view'>";
				foreach($rsDiscount as $Discount){


					if (!empty($Discount['endDay'])) {
						$strListDay = "<a class='pull-left date'>
											<p class='month'>" . $Discount['endDay'] . "</p>
											<p class='day'>" . $Discount['endYear'] . "</p>
										</a>";
					} else {
						$strListDay = "<a class='pull-left border-green profile_thumb'>
										<i class='fa fa-arrow-circle-up green'></i>
				  						</a>";
					}

					$strDiscountList.= '<li class="media event">
						'.$strListDay.'
						<div class="media-body">
							<a class="title">'.$Discount['discountTitle'].'</a>
							<p><strong class="red">'.$Discount['DisValueType'].'</strong> '.$Discount['discountName'].' ('.$Discount['discountForType'].')</p>
							<p><small>'.$Discount['discountFor'].'</small><small class="blue"> ('.$Discount['discountPeriod'].')</small>
							</p>
						</div>
					</li>';
				}
				$strDiscountList .= "</ul>";
			} else {
				$strDiscountList = "<h2><i class='fa fa-close'></i> ".$tr->translate('No Discount Record')."</h2>";
			}

			$dbRegister = new Registrar_Model_DbTable_DbRegister();
			$param = array(
				'studentId' => $student_id,
				'returnHtml' => 1
			);
			$resultPaymentHistory = $dbRegister->getStudentPaymentHistory($param);
		}

		$result = array(
			'studentInfo' => $str,
			'studyinfo' => $studyInfo,
			'parentinfo' => $str_parentinfo,
			'studentCode' => $studentCode,
			'resultDiscountList'=>$strDiscountList,
			'resultPaymentHistory' => $resultPaymentHistory,
			'studenttypeinfo' => $studentStatus,
			'studentData' => $studentData,
			'groupStore' => $groupResult,
			'branchHeader' => $resultBranch,
		);
		return $result;
	}
	function getCardBackground($branch, $card_type, $schoolOption = null, $degree = null)
	{
		$db = $this->getAdapter();
		$sql = "SELECT c.* FROM `rms_cardbackground` AS c WHERE c.branch_id=$branch 
	  AND c.default=1  AND c.card_type=$card_type 
	  ";
		if (!empty($schoolOption)) {
			$sql .= " AND c.schoolOption IN ($schoolOption) ";
		}

		if (!empty($degree)) {
			$row = $db->fetchAll($sql);
			if (!empty($row)) {
				foreach ($row as $rs) {
					$dept = '';
					if (!empty($rs['degree'])) {
						$dept = explode(",", $rs['degree']);
					}
					$array = array();
					if (!empty($dept)) {
						foreach ($dept as $ss) {
							$array[$ss] = $ss;
						}
					}
					if (in_array($degree, $array)) {
						return $rs;
						break;
					}
				}
			}
			return null;
		} else {
			$sql .= " ORDER BY c.id DESC LIMIT 1";
		}


		return $db->fetchRow($sql);
	}
	function getPickupCardBackground($branch, $schoolOption = null)
	{
		$db = $this->getAdapter();
		$sql = "SELECT c.* FROM `rms_pickupcard` AS c WHERE c.branch_id=$branch
  	AND c.default=1
  	";
		if (!empty($schoolOption)) {
			$sql .= " AND c.schoolOption IN ($schoolOption) ";
		}
		$sql .= " ORDER BY c.id DESC LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getStudentGroupInfoById($id)
	{
		$db = $this->getAdapter();

		$currentLang = $this->currentlang();
		$colunmname = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'title';
		}
		$sql = "SELECT start_date,expired_date,group_code,
		  	(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = rms_group.academic_year LIMIT 1) AS year,
		  	(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = rms_group.academic_year LIMIT 1) AS year_only,
		  	(SELECT rms_items.$colunmname FROM rms_items WHERE rms_items.id=rms_group.degree AND rms_items.type=1 LIMIT 1) AS degree,
		  	(SELECT rms_itemsdetail.$colunmname FROM rms_itemsdetail WHERE rms_itemsdetail.id =`rms_group`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
		  	(SELECT name_en FROM `rms_view` WHERE `rms_view`.`type`=4 AND `rms_view`.`key_code`=`rms_group`.`session` LIMIT 1) AS session,
		  	(SELECT room_name FROM rms_room WHERE room_id = rms_group.room_id LIMIT 1) AS room,
		  	academic_year AS academic_year_id,
		  	degree AS degree_id,
		  	grade AS grade_id,
		  	SESSION AS session_id,
	  		room_id,
		  	(SELECT t.teacher_name_kh FROM rms_teacher AS t WHERE t.id=rms_group.teacher_id LIMIT 1) AS teacherNameKh,
		  	(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id=rms_group.teacher_id LIMIT 1) AS teacherNameEng,
		  	(SELECT rms_items.title FROM rms_items WHERE rms_items.id=rms_group.degree AND rms_items.type=1 LIMIT 1) AS degree_kh,
		  	(SELECT rms_items.title_en FROM rms_items WHERE rms_items.id=rms_group.degree AND rms_items.type=1 LIMIT 1) AS degree_eng,
		  	(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id =`rms_group`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade_kh,
		  	(SELECT rms_itemsdetail.title_en FROM rms_itemsdetail WHERE rms_itemsdetail.id =`rms_group`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade_eng
  		FROM
  			`rms_group` 
  				WHERE id=$id LIMIT 1 ";
		// last 4 row query use for cetifcate only ( added in 06-3-2019)
		return $db->fetchRow($sql);
	}
	function getPuchaseNo($branch_id)
	{//used global
		$db = $this->getAdapter();
		$branch_id = empty($branch_id) ? 0 : $branch_id;

		$db = $this->getAdapter();
		$sql = "SELECT COUNT(id) FROM rms_purchase WHERE branch_id=$branch_id ORDER BY id DESC";
		$stu_num = $db->fetchOne($sql);
		$pre = $this->getPrefixCode($branch_id);//by branch
		$pre .= 'PO-';
		$new_acc_no = (int) $stu_num + 1;
		$length = strlen((int) $new_acc_no);
		for ($i = $length; $i < 4; $i++) {
			$pre .= '0';
		}
		return $pre . $new_acc_no;
	}
	function ifStudentinGroupReady($student_id, $group_id)
	{
		$group_id = empty($group_id) ? 0 : $group_id;
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_group_detail_student WHERE itemType=1 AND stu_id=$student_id AND group_id=$group_id";
		return $db->fetchRow($sql);
	}
	function getAllGroupByBranch($branch_id = null, $forfilterreport = null, $data = array())
	{
		$academic_year = empty($data['academic_year']) ? null : $data['academic_year'];
		$db = $this->getAdapter();
		$sql = "SELECT `g`.`id`, 
	  			CONCAT( g.group_code,' ',(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1)) AS name
	  				FROM `rms_group` AS `g` WHERE g.status=1 ";

		if (!empty($forfilterreport)) {
			$sql .= " AND (g.is_pass=1 OR g.is_pass=2) ";// group studying/completed
		} else {
			$sql .= " AND (g.is_pass=0 OR g.is_pass=2) ";// group studying/not complete
		}
		if (!empty($branch_id)) {
			$sql .= " AND g.branch_id = $branch_id";
		}
		if (!empty($academic_year)) {
			$sql .= " AND g.academic_year = $academic_year";
		}
		$sql .= $this->getAccessPermission('g.branch_id');
		$sql .= $this->getDegreePermission('g.degree');
		$sql .= " ORDER BY g.degree,g.grade,g.group_code ASC,`g`.`id` DESC ";
		return $db->fetchAll($sql);
	}
	function getAllGroupForIssueScore($data)
	{
		$academic_year = empty($data['academic_year']) ? null : $data['academic_year'];
		$db = $this->getAdapter();
		$sql = "SELECT `g`.`id`, 
	  			CONCAT( g.group_code,' ',(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1)) AS name
	  				FROM `rms_group` AS `g` WHERE g.status=1 ";
					
		if (!empty($data['branch_id'])) {
			$sql .= " AND g.branch_id = ".$data['branch_id'];
		}
		if (!empty($data['is_pass'])) {
			$sql .= " AND g.is_pass = ".$data['is_pass'];
		}
		if (!empty($data['academic_year'])) {
			$sql .= " AND g.academic_year = ".$data['academic_year'];
		}
		$sql .= $this->getAccessPermission('g.branch_id');
		$sql .= $this->getDegreePermission('g.degree');
		$sql .= " ORDER BY g.academic_year DESC, g.degree,g.grade,g.group_code ASC ";
		return $db->fetchAll($sql);
	}

	function getNumberInkhmer($number)
	{
		$khmernumber = array("០", "១", "២", "៣", "៤", "៥", "៦", "៧", "៨", "៩");
		$spp = str_split($number);
		$num = "";
		foreach ($spp as $ss) {

			if (!empty($khmernumber[$ss])) {
				$num .= $khmernumber[$ss];
			} else {
				$num .= $ss;
			}
		}
		return $num;
	}
	function getMonthInkhmer($monthNum)
	{
		$monthKH = array(
			"01" => "មករា",
			"02" => "កុម្ភៈ",
			"03" => "មីនា",
			"04" => "មេសា",
			"05" => "ឧសភា",
			"06" => "មិថុនា",
			"07" => "កក្កដា",
			"08" => "សីហា",
			"09" => "កញ្ញា",
			"10" => "តុលា",
			"11" => "វិច្ឆិកា",
			"12" => "ធ្នូ"
		);
		$monthChar = empty($monthKH[$monthNum]) ? "" : $monthKH[$monthNum];
		return $monthChar;
	}
	function calCulateGrade($score, $max_score)
	{
		$score_avg = ($score / $max_score) * 100;
		if ($score_avg < 50) {//0.67
			return 'F';
		} else if ($score_avg < 60) {
			return 'E';
		} else if ($score_avg < 70) {
			return 'D';
		} else if ($score_avg < 80) {
			return 'C';
		} else if ($score_avg < 90) {
			return 'B';
		} else {
			return 'A';
		}
	}
	function calCulateGradeKhmerMention($score, $max_score)
	{
		$score_avg = ($score / $max_score) * 100;
		if ($score_avg < 50) {//0.67
			return 'ខ្សោយ';
		} else if ($score_avg < 60) {
			return 'មធ្យម';
		} else if ($score_avg < 70) {
			return 'ល្អបង្គួរ';
		} else if ($score_avg < 80) {
			return 'ល្អ';
		} else if ($score_avg < 90) {
			return 'ល្អណាស់';
		} else {
			return 'ល្អប្រសើរ';
		}
	}
	function calCulateGradeEnglishMention($score, $max_score)
	{
		$score_avg = ($score / $max_score) * 100;
		if ($score_avg < 50) {//0.67
			return 'Fail';
		} else if ($score_avg < 60) {
			return 'Average';
		} else if ($score_avg < 70) {
			return 'Above Average';
		} else if ($score_avg < 80) {
			return 'Good';
		} else if ($score_avg < 90) {
			return 'Very  Good';
		} else {
			return 'Excellent';
		}
	}
	function getMentionScore($score, $academic, $degree, $mentiontype = 1, $grade = null, $maxScore = 0)
	{
		$db = $this->getAdapter();
		$column = "sd.metion_grade";
		if ($mentiontype == 1) {//grade A/B/C
			$column = "sd.metion_grade";
		} else if ($mentiontype == 2) {// ល្អប្រសើរ/ល្អណាស់/ល្អ
			$column = "sd.metion_in_khmer";
		} else if ($mentiontype == 3) {// Excellent/Very  Good/Good
			$column = "sd.mention_in_english";
		}//,sd.max_score
		$score = empty($score) ? 0 : $score;
		$scoreAverage = $score / $maxScore * 100;

		$sql = "SELECT $column AS mention
			FROM `rms_metionscore_setting_detail` AS sd,
				`rms_metionscore_setting` AS s
			WHERE s.id = sd.metion_score_id
				AND s.academic_year=$academic
				AND degree = $degree
				AND $scoreAverage>=sd.max_score
				ORDER BY sd.max_score DESC
				LIMIT 1";
			// echo $sql;
		return $db->fetchOne($sql);
	}
	function getMentionScoreNew($data)
	{
		$db = $this->getAdapter();
	
		$academic = empty($data['academic_year']) ? 0 : $data['academic_year'];
		$degree = empty($data['degree']) ? 0 : $data['degree'];
		$score = empty($data['score']) ? 0 : $data['score'];
		$maxScore = empty($data['max_score']) ? 0 : $data['max_score'];
		$scoreAverage = $score / $maxScore * 100;
		
		$sql = "SELECT sd.metion_grade,sd.metion_in_khmer,sd.mention_in_english
			FROM `rms_metionscore_setting_detail` AS sd,
				`rms_metionscore_setting` AS s
			WHERE s.id = sd.metion_score_id
				AND s.academic_year=$academic
				AND degree = $degree
				AND $scoreAverage>=sd.max_score ";

			$sql.= " ORDER BY sd.max_score DESC
				LIMIT 1 ";
			// echo $sql;
		return $db->fetchRow($sql);
	}
	function updateReadNotif($type, $notif_id)
	{
		$db = $this->getAdapter();
		$_arr = array(
			'notif_type' => $type,
			'notification_id' => $notif_id,
			'user_id' => $this->getUserId(),
			'is_read' => 1,
			'is_click' => 1,
			'date' => date("Y-m-d H:i:s"),
		);
		$this->_name = 'rms_read_unread_notif';
		$id = $this->insert($_arr);
		return $id;
	}
	function getFeeStudyinfoByIdGloable($fee_id)
	{
		$db = $this->getAdapter();
		$sql = " SELECT
	  	t.id,
	  	t.academic_year AS academicYearId,
	  	(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=t.academic_year LIMIT 1) AS academic_year,
	  	(SELECT title_kh FROM `rms_studytype` WHERE id=t.term_study LIMIT 1) AS session_type
  	FROM
	  	`rms_tuitionfee` as t
	  	WHERE t.id=$fee_id LIMIT  1";
		return $db->fetchRow($sql);
	}
	function getAllYearServiceFeeByBranch($branch_id)
	{
		$db = $this->getAdapter();
		$sql = " SELECT 
  					t.academic_year AS id ,
  					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=t.academic_year LIMIT 1) AS name
  				FROM 
  					`rms_tuitionfee` as t 
  				WHERE 
  					status=1 
  					AND type=2
  					AND branch_id = $branch_id
  				ORDER BY 
  					id DESC ";
		return $db->fetchAll($sql);
	}


	function getExamTypeEngItems()
	{

		$currentLang = $this->currentlang();
		$colunmname = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'title';
		}

		$db = $this->getAdapter();
		$sql = "SELECT ex.id,ex.$colunmname AS `name` 
		FROM `rms_exametypeeng` AS ex
		WHERE ex.status=1";
		return $db->fetchAll($sql);
	}

	function getAllCompleteGroupByBranch($branch_id = null)
	{
		$db = $this->getAdapter();
		$sql = "SELECT `g`.`id`, CONCAT( COALESCE(`g`.`group_code`,''),' ',
  	 COALESCE((SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1),'')
  		
  	 ) AS name
  	FROM `rms_group` AS `g` WHERE g.is_pass=1 ";
		if (!empty($branch_id)) {
			$sql .= " AND g.branch_id = $branch_id";
		}
		$sql .= " ORDER BY `g`.`id` DESC ";
		return $db->fetchAll($sql);
	}
	function getAllPassStudentGroup($group)
	{

		$currentLang = $this->currentlang();
		$colunmname = 'name_en';
		$stu_name = "CONCAT(st.last_name,' ',st.stu_enname)";
		if ($currentLang == 1) {
			$colunmname = 'name_kh';
			$stu_name = "st.stu_khname";
		}

		$db = $this->getAdapter();
		$sql = "
  		SELECT 
	  		gds.stu_id as stu_id,
	  		st.stu_enname,
	  		st.last_name,
	  		st.stu_khname,
	  		st.stu_code,
	  		$stu_name AS stu_name,
	  		(SELECT $colunmname FROM rms_view WHERE rms_view.type=2 and rms_view.key_code=st.sex LIMIT 1) as sex
  		FROM rms_group_detail_student as gds,
  			rms_student as st
  		WHERE 
			gds.itemType=1 AND
			gds.stu_id=st.stu_id
		  	and gds.group_id=$group
		  	and gds.is_pass=1
  	";
		return $db->fetchAll($sql);
	}

	function getRatingValuation()
	{
		$db = $this->getAdapter();
		$sql = "SELECT r.id,r.rating AS `name` FROM `rms_rating` AS r ";
		return $db->fetchAll($sql);
	}


	function getAllStudentByGroup($group_id)
	{
		$db = $this->getAdapter();
		$currentLang = $this->currentlang();
		$coloum = "name_en";
		if ($currentLang == 1) {
			$coloum = "name_kh";
		}
		$sql = "SELECT
  			gds.stu_id as id,
		  	gds.stu_id as stu_id,
		  	st.stu_enname,
		  	st.last_name,
		  	st.stu_khname,
		  	st.stu_code,
		  	CONCAT(st.stu_enname,' - ',st.stu_khname) AS stu_name,
		  	CONCAT(COALESCE(st.stu_code,''),'-',COALESCE(st.stu_khname,''),'-',COALESCE(st.stu_enname,''),' ',COALESCE(st.last_name,'')) AS name,
		  	(select $coloum from rms_view where rms_view.type=2 and rms_view.key_code=st.sex) as sex
	  	FROM
		  	rms_group_detail_student as gds,
		  	rms_student as st
	  	WHERE
			gds.itemType=1 AND
		  	gds.stu_id=st.stu_id
			and is_pass=0
		  	and gds.group_id=$group_id ";
		return $db->fetchAll($sql);
	}

	function getAllStudentByGroupForEdit($group_id)
	{
		$db = $this->getAdapter();
		$currentLang = $this->currentlang();
		$coloum = "name_en";
		if ($currentLang == 1) {
			$coloum = "name_kh";
		}
		$sql = "SELECT
  		gds.stu_id as id,
	  	gds.stu_id as stu_id,
	  	st.stu_enname,
	  	st.stu_khname,
	  	st.stu_code,
	  	st.last_name,
	  	CONCAT(st.stu_enname,' - ',st.stu_khname) AS stu_name,
	  	CONCAT(COALESCE(st.stu_code,''),'-',COALESCE(st.stu_khname,''),'-',COALESCE(st.stu_enname,''),' ',COALESCE(st.last_name,'')) AS name,
	  	(SELECT $coloum FROM rms_view WHERE rms_view.type=2 AND rms_view.key_code=st.sex LIMIT 1) AS sex
  	FROM
	  	rms_group_detail_student as gds,
	  	rms_student as st
  	WHERE
		gds.itemType=1 AND
	  	gds.stu_id=st.stu_id
	  	AND gds.group_id=$group_id 
  		AND gds.stop_type=0 ";
		//	and gds.stop_type=0
		//and gds.is_pass=0
		return $db->fetchAll($sql);
	}
	public function checkSessionExpire()
	{
		$user_id = $this->getUserId();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		if (empty($user_id)) {
			return false;
		} else {
			return true;
		}
	}
	public function checkSessionTeacherExpire()
	{

		$dbExternal = new Application_Model_DbTable_DbExternal();
		$teacherId = $dbExternal->getUserExternalId();
		$userId = $this->getUserId();

		if (empty($teacherId) or empty($userId)) {
			return false;
		} else {
			return true;
		}
	}
	function reloadPageExpireSession()
	{
		$url = "";
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$msg = $tr->translate("Session Expire");
		echo '<script language="javascript">
	  	alert("' . $msg . '");		
	  	window.location = "' . Zend_Controller_Front::getInstance()->getBaseUrl() . $url . '";
	  	</script>';
	}


	function getPh($type = null)
	{
		// Turn on output buffering
		ob_start();
		//Get the ipconfig details using system commond
		system('ipconfig /all');

		// Capture the output into a variable
		$mycom = ob_get_contents();
		// Clean (erase) the output buffer
		ob_clean();

		$findme = "Physical";
		//Search the "Physical" | Find the position of Physical text
		$pmac = strpos($mycom, $findme);
		// Get Physical Address
		$mac = md5(substr($mycom, ($pmac + 36), 17));
		if ($type == 1) {
			return $mac;
		} else {

			//Display Mac Address
			if ($mac != SECRET_KEY) {
				return false;
			}
			return true;
		}

		//If you want you can track the page visitor's mac address and store in database
		//Insert the visitor's mac address to database
		// " INSERT INTO `table_name` (`column_name`) VALUES('".$mac_address."') ";
	}

	function getAllMonth()
	{
		$db = $this->getAdapter();
		$lang = $this->currentlang();
		if ($lang == 1) {// khmer
			$month = "month_kh";
		} else { // English
			$month = "month_en";
		}
		$sql = "SELECT id , $month as name,month_kh from rms_month where status=1 ";
		return $db->fetchAll($sql);
	}
	function getAllCardFormat()
	{
		$db = $this->getAdapter();
		$lang = $this->currentlang();
		if ($lang == 1) {// khmer
			$label = "name_kh";
		} else { // English
			$label = "name_en";
		}
		$sql = "SELECT key_code as id , $label as name from rms_view where status=1 and type=32";
		return $db->fetchAll($sql);
	}
	function getSubjectArea($type = 1)
	{

		$currentLang = $this->currentlang();
		$colunmname = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'title';
		}

		$db = $this->getAdapter();
		$sql = "SELECT ex.id,ex.$colunmname AS `name`
  	FROM `rms_subjectarea` AS ex
  	WHERE ex.status=1 AND ex.type=$type";
		return $db->fetchAll($sql);
	}

	function getAllSetting($_data)
	{
		$db = $this->getAdapter();
		$sql = " SELECT id,title AS name FROM rms_placementtest_setting WHERE 1 ";
		if (!empty($_data['test_type'])) {
			$sql .= " AND test_type = " . $_data['test_type'];
		}
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	function getPlacementTestType()
	{
		$db = $this->getAdapter();
		$sql = "SELECT id AS id ,title AS name FROM rms_test_type WHERE `status`=1 ORDER BY id ASC ";
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	function getAllQuestionType()
	{
		$db = $this->getAdapter();
		$sql = "SELECT id AS id ,title AS name FROM rms_question_type WHERE `status`=1 ORDER BY id ASC ";
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	function getAllSections($_data, $is_parent = null, $parent = 0, $spacing = '', $cate_tree_array = '')
	{
		$db = $this->getAdapter();
		$sql = " SELECT id,title AS name FROM rms_section WHERE 1 ";
		if (!empty($_data['test_type'])) {
			$sql .= " AND test_type = " . $_data['test_type'];
			if (!empty($_data['free_section'])) {
				$parent_section = $this->getPlacementTestSection($_data['test_type']);
				if (!empty($parent_section)) {
					$sql .= " AND id NOT IN ($parent_section) ";
				}
			}
		}
		if (!empty($is_parent)) {
			$sql .= " AND parent = 0 ";
			return $rows = $db->fetchAll($sql);
		} else {
			$sql .= " AND parent = $parent ";
		}
		$rows = $db->fetchAll($sql);
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getAllSections($_data, $is_parent, $row['id'], $spacing . ' - ', $cate_tree_array);
			}

		}
		return $cate_tree_array;
	}
	function getPlacementTestSection($test_type)
	{
		$db = $this->getAdapter();
		$sql = " SELECT GROUP_CONCAT(DISTINCT psd.section_id) AS section_id
			FROM `rms_placement_test` AS pt,
			`rms_placementtest_setting_detail` AS psd
			WHERE psd.setting_id = pt.placement_setting_id ";
		$sql .= " AND (SELECT ps.test_type FROM `rms_placementtest_setting` AS ps WHERE ps.id = psd.setting_id LIMIT 1 ) =$test_type";
		$row = $db->fetchOne($sql);
		return $row;
	}

	function getOptionTrueFalse($hav_empty_opt = null, $is_arr = null)
	{
		if (!empty($is_arr)) {
			$_arr = array(
				array(
					"id" => "true",
					"name" => "True"
				),
				array(
					"id" => "false",
					"name" => "False"
				),
			);
			return $_arr;
		} else {
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$options = '';
			if (!empty($hav_empty_opt)) {
				$options = '<option value="" >' . htmlspecialchars($tr->translate("SELECT_TRUE_FALSE"), ENT_QUOTES) . '</option>';
			}
			$options .= '<option value="true" >' . htmlspecialchars("True", ENT_QUOTES) . '</option>';
			$options .= '<option value="false" >' . htmlspecialchars("False", ENT_QUOTES) . '</option>';
			return $options;
		}
	}

	function getAllQuestionBySettingExam($_data = null)
	{
		$db = $this->getAdapter();
		$sql = "SELECT s.parent,
		(SELECT sp.title FROM `rms_section` AS sp WHERE sp.id = s.parent LIMIT 1 ) AS parent_title,
		(SELECT sp.instruction FROM `rms_section` AS sp WHERE sp.id = s.parent LIMIT 1 ) AS parent_instruction,
		s.title AS section_title,
		s.instruction,
		s.article,
		s.ordering AS section_ordering,
		q.* 
		FROM `rms_question` AS q,
		`rms_section` AS s
		WHERE s.id = q.section_id
		
  		";
		if (!empty($_data['test_setting_id'])) {
			$setting_id = $_data['test_setting_id'];
			$sql .= " AND (s.id IN (SELECT  st.section_id FROM `rms_placementtest_setting_detail` AS st
			WHERE st.setting_id =$setting_id) OR s.parent IN (SELECT  st.section_id FROM `rms_placementtest_setting_detail` AS st
			WHERE st.setting_id =$setting_id))";
		}

		$sql .= "ORDER BY 
		(SELECT sp.ordering FROM `rms_section` AS sp WHERE sp.id = s.parent LIMIT 1 ) ASC,
		s.ordering ASC,
		q.ordering ASC";
		return $db->fetchAll($sql);
	}

	function getQuestionDetailById($question_id)
	{
		$db = $this->getAdapter();
		$sql = " SELECT qd.* FROM `rms_question_detail` AS qd WHERE qd.question_id = $question_id ";
		return $db->fetchAll($sql);
	}
	function getGetAnswerKeyById($section_id, $question_type, $is_opt = null)
	{
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = " SELECT qd.* FROM `rms_question_detail` AS qd,rms_question as q WHERE qd.question_id = q.id AND q.section_id =$section_id AND q.question_type=$question_type  ";
		if ($question_type == 8) {
			$sql .= " AND qd.answer_label = '' ";
		}
		$sql .= " ORDER BY qd.answer_key ASC";
		$row = $db->fetchAll($sql);
		$options = '';
		if (!empty($is_opt)) {
			$options = '<option value="" >' . htmlspecialchars($tr->translate("SELECT_ANSWER"), ENT_QUOTES) . '</option>';
			if (!empty($row))
				foreach ($row as $rs) {
					$options .= '<option value="' . $rs['answer_key'] . '" >' . htmlspecialchars($rs['answer_key'], ENT_QUOTES) . '</option>';
				}
			return $options;
		} else {
			return $row;
		}
	}
	function getGroupSubjectDetail($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT SUM(score_short) AS score_short,
			  		amount_subject AS totalSubjectMonth,
			  		amount_subject_sem AS totalSubjectSemester,
			  		max_score AS maxScoreMonth,
			  		semester_max_score AS maxScoreSemester
  				FROM 
  			`rms_group_subject_detail` WHERE 1";
		if ($data['groupId']) {
			$sql .= " AND group_id=" . $data['groupId'];
		}
		if ($data['subjectId']) {
			$sql .= " AND subject_id =" . $data['subjectId'];
		}
		$sql .= " LIMIT 1";
		return $db->fetchRow($sql);
	}

	function resultScan($student_id)
	{
		$rs = $this->getStudentinfoGlobalById($student_id);
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$photo = $baseUrl . "/images/no-profile.png";
		if (!empty($rs["photo"])) {
			if (file_exists(PUBLIC_PATH . "/images/photo/" . $rs["photo"])) {
				$photo = $baseUrl . "/images/photo/" . $rs["photo"];
			}
		}
		$string = '';
		$string .= '
  	<div class="bg-border-top"></div>
	<div class="bg-border-bottom"></div>
  	<div class="col-md-4 col-sm-4 col-xs-12 blg-profile">
				<div class="profile-img">
					<img class="profile-img" src="' . $photo . '">
				</div>
				<div class="student-name">
					<ul class="info-list">
						<li><span class="value-sheet stu-code">' . $rs["stu_code"] . '</span></li>
						<li><span class="value-sheet">' . $rs["stu_khname"] . '</span></li>
						<li><span class="value-sheet">' . $rs["last_name"] . " " . $rs["stu_enname"] . '</span></li>
					</ul>
				</div>
			</div>';
		$string .= '
  				<div class="col-md-1 col-sm-1 col-xs-12 blg-border-images">
					<img class="border-img" src="' . $baseUrl . '/images/horizontal-line-border.png">
				</div>
  			<div class="col-md-7 col-sm-7 col-xs-12 blg-information">
				<div class="study-info">
					<ul class="study-info-list">
						<li><span class="value-title">ACADEMIC</span>: <span class="value-sheet">2019-2020(N-Full Day)</span></li>
						<li><span class="value-title">DEGREE</span>: <span class="value-sheet">' . $rs['degree_label'] . '</span></li>
						<li><span class="value-title">GRADE</span>: <span class="value-sheet">' . $rs['grade_label'] . '</span></li>
						<li><span class="value-title">CLASS</span>: <span class="value-sheet">' . $rs['group_name'] . '</span></li>
						<li><span class="value-title">ROOM</span>: <span class="value-sheet">' . $rs['room_label'] . '</span></li>
					</ul>
				</div>
			</div>
			<div class="clearfix"></div>
  	';
		return array('string' => $string);
	}
	public function getAllAcademicYear($option = null, $ordering = null)
	{
		$db = $this->getAdapter();
		$sql = "SELECT id,
				CONCAT(fromYear,'-',toYear) as name,
				CONCAT(fromYear,'-',toYear) as years
		  		FROM
		  	rms_academicyear
		  		WHERE status = 1 ";
		$string = " ORDER By toYear DESC";
		if (!empty($ordering)) {
			$string = " ORDER By toYear ASC";
		}
		$sql .= $string;
		$result = $db->fetchAll($sql);
		if ($option != null) {
			$request = Zend_Controller_Front::getInstance()->getRequest();
			if ($request->getActionName() == 'index' or $request->getModuleName() == 'allreport') {
				$options = array(-1 => $this->tr->translate("SELECT_ACADEMIC_YEAR"));
			}
			if (!empty($result))
				foreach ($result as $row) {
					$options[$row['id']] = $row['name'];
				}
			return $options;
		}
		return $result;
	}
	public function getAllTermStudyTitle($option = null)
	{
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $this->currentlang();
		if ($lang == 1) {// khmer
			$label = "title_kh";
		} else { // English
			$label = "title_eng";
		}
		$sql = "SELECT id, $label as name
	  			FROM
	  				rms_studytype
	  					WHERE status = 1 AND $label!='' ";
		$result = $db->fetchAll($sql);

		if ($option != null) {
			$options = array();
			$request = Zend_Controller_Front::getInstance()->getRequest();
			if ($request->getActionName() == 'index' or $request->getModuleName() == 'allreport') {
				$options = array(-1 => $this->tr->translate("SELECT_TYPE"));
			}

			if (!empty($result))
				foreach ($result as $row) {
					$options[$row['id']] = $row['name'];
				}
			return $options;
		}
		return $result;
	}

	public function getAllChangeGroup($type)
	{//1=ប្តូរក្រុម , 2=ឡើងថ្នាក់
		$db = $this->getAdapter();
		$sql = "SELECT
	  	id,
	  	CONCAT(COALESCE((SELECT group_code FROM rms_group as g WHERE g.id = from_group LIMIT 1),''),'-',COALESCE((SELECT group_code FROM rms_group as g WHERE g.id = to_group LIMIT 1),'')) as name,
	  	(SELECT group_code FROM rms_group as g WHERE g.id = from_group LIMIT 1) as from_group,
	  	(SELECT group_code FROM rms_group as g WHERE g.id = to_group LIMIT 1) as to_group
	  	FROM
	  	rms_group_student_change_group
	  	WHERE
	  	change_type=$type
  		and status=1
  	";
		return $db->fetchAll($sql);
	}
	function getAllStudentStudy($opt = null, $data = array())
	{
		$db = $this->getAdapter();

		$branchid = empty($data['branch_id']) ? $data['branch_id'] : null;
		$branch_id = $this->getAccessPermission();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$lang = $this->currentlang();
		$stuName = "COALESCE(s.stu_khname,''),' ',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')";
		$grade = "rms_itemsdetail.title_en";
		if ($lang == 1) {// khmer
//   	$stuName = "COALESCE(s.stu_khname,'')";
			$grade = "rms_itemsdetail.title";
		}

		$string = "
  			gds.gd_id AS id,
	  		CONCAT( COALESCE(s.stu_code,''),'-',$stuName,'-',COALESCE((SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=gds.grade AND rms_itemsdetail.items_type=1 LIMIT 1),'') ) AS name
  	";
		$where = "";
		if (!empty($data['typeQuery'])) {//for get student main
			$string = " s.stu_id  AS id,
  		CONCAT( COALESCE(s.stu_code,''),'-',$stuName ) AS name
  		";
			$where = " AND gds.is_maingrade=1 ";
		}
		$sql = "SELECT 
		  	$string
	  	FROM
		  	rms_student AS s,
		  	rms_group_detail_student AS gds
	  	WHERE
			gds.itemType=1 AND
		  	gds.stu_id = s.stu_id
		  	AND (stu_enname!='' OR s.stu_khname!='')
		  	AND s.status=1
		  	AND s.customer_type=1
		  	AND gds.is_current = 1
		  	AND gds.is_setgroup = 1 ";
		$sql .= $where;
		if ($branchid != null) {
			$sql .= " AND s.branch_id=" . $branchid;
		}
		if (!empty($data['study_id'])) {
			$sql .= "AND (gds.stop_type=0 OR gds.gd_id =" . $data['study_id'] . ")";
		} else {
			if (!empty($data['completedStudent'])) {
				$sql .= "	AND gds.stop_type=3";
			} else {
				$sql .= "	AND gds.stop_type=0";
			}
		}
		if (!empty($data['is_maingrade'])) {
			$sql .= " AND gds.is_maingrade=1";
		}
		if (!empty($data['academic_year'])) {
			$sql .= " AND gds.academic_year=" . $data['academic_year'];
		}
		if (!empty($data['group'])) {
			$sql .= " AND gds.group_id=" . $data['group'];
		}
		if (!empty($data['degree'])) {
			$sql .= " AND gds.degree=" . $data['degree'];
		}
		if (!empty($data['grade'])) {
			$sql .= " AND gds.grade=" . $data['grade'];
		}
		$sql .= " ORDER BY CONCAT( COALESCE(s.stu_code,''),'-',$stuName,COALESCE((SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=gds.grade AND rms_itemsdetail.items_type=1 LIMIT 1),'') ) ASC";
		$rows = $db->fetchAll($sql);
		if ($opt != null) {
			$options = array(0 => $tr->translate("CHOOSE"));
			if (!empty($rows))
				foreach ($rows as $row) {
					$lable = $row['name'];
					$options[$row['id']] = $lable;
				}
			return $options;
		} else {
			return $rows;
		}
	}

	function getAllTestTerm($data = array(), $option = null)
	{

		$db = $this->getAdapter();
		$sql = " SELECT 
  			id,
  			note as name 
  			FROM rms_test_term WHERE status=1 AND note !=''  ";
		if (!empty($data['branch_id'])) {
			$sql .= " AND  branch_id=" . $data['branch_id'];
		}
		$sql .= " ORDER BY id DESC";
		$rows = $db->fetchAll($sql);
		if ($option == null) {
			return $rows;
		} else {
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$options = " <option value=''>" . $tr->translate("SELECT_TERM") . "</option> ";
			if (!empty($rows)) {
				foreach ($rows as $row) {
					$options .= '<option value="' . $row['id'] . '" >' . htmlspecialchars($row['name'], ENT_QUOTES) . '</option>';
				}
			}
			return $options;
		}

	}


	function updateAmountStudetByDegree($data)
	{//used global
		$db = $this->getAdapter();

		$degree = empty($data['degreeStudent']) ? 0 : $data['degreeStudent'];
		$branchId = empty($data['branch_id']) ? 0 : $data['branch_id'];
		$sql = "SELECT amount_student FROM `rms_student_id` WHERE branch_id = $branchId AND  degree = $degree ";
		$num = $db->fetchOne($sql);
		$stu_num = empty($num) ? 0 : $num;
		$newAmouunt = $stu_num + 1;

		$_arr = array(
			'branch_id' => $branchId,
			'amount_student' => $newAmouunt,
		);
		$this->_name = "rms_student_id";
		if ($num != '') {

			$where = "degree = $degree AND branch_id = $branchId ";
			$this->update($_arr, $where);
		} else {
			if ($degree > 0) {
				$_arr['degree'] = $degree;
				$this->insert($_arr);
			}
		}
	}



	function getStudentToken()
	{
		return 'PSIS' . date('YmdHis');
	}
	function getcrmFollowupStatus()
	{
		$_arr = array(
			-1 => $this->tr->translate("ALL"),
			1 => $this->tr->translate("PROGRESSING"),
			2 => $this->tr->translate("WAITING_COMPLETED"),
			3 => $this->tr->translate("COMPLETED"),
			0 => $this->tr->translate("CANCEL")
		);
		return $_arr;
	}
	function crmStatusprocess()
	{
		$_arr = array(
			-1 => $this->tr->translate("SELECT_STUDENT_CRMPROCESS"),
			1 => $this->tr->translate("STUDENT"),
			3 => $this->tr->translate("CRM"),
			4 => $this->tr->translate("TESTED"),
		);
		return $_arr;
	}
	function getSchoolOptionbyDegree($degree_id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT schoolOption FROM rms_items WHERE id=" . $degree_id;
		return $db->fetchOne($sql);
	}
	function getStudentByGroupGlobal($data)
	{//check again
		$db = $this->getAdapter();

		$currentLang = $this->currentlang();
		$colunmname = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'title';
		}

		$today = date('Y-m-d');
		$sql = "SELECT
  				
				st.stu_code AS stu_code,
			  	(CASE WHEN st.stu_khname IS NULL THEN st.stu_enname ELSE stu_khname END) AS stu_name,
			  	st.stu_khname,
			  	st.last_name,
				st.stu_enname,
			  	(SELECT name_en FROM rms_view WHERE rms_view.type=2 AND rms_view.key_code=st.sex LIMIT 1) as gender,
			  	st.sex, 
			  	gs.`stu_id`,
				gs.is_maingrade,
				gs.itemType,
				gs.startDate,
				gs.endDate,
				gs.feeId,
				gs.balance,
				gs.group_id,
				gs.academic_year,
				gs.degree,
				gs.degree AS itemId,
				gs.grade,
				gs.grade AS itemDetailId,
				(SELECT dcs.discountId FROM `rms_dis_setting` dcs WHERE dcs.studentId=st.stu_id AND dcs.endDate >='$today' LIMIT 1) discountType,
				(SELECT dcs.discountValue FROM `rms_dis_setting` dcs WHERE dcs.studentId=st.stu_id endDate >='$today' LIMIT 1) discountValue,
				(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=gs.grade LIMIT 1) as itemDetaillabel,
				(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.id=gs.degree LIMIT 1) as itemLabel,
				(SELECT rms_itemsdetail.is_onepayment FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=gs.grade LIMIT 1) as is_onepayment
		  	FROM
		  		`rms_group_detail_student` AS gs,
		  		rms_student as st
		  	WHERE
		  		st.stu_id=gs.stu_id ";
		if (!empty($data['item_type'])) {
			$sql .= " AND gs.`itemType` = " . $data['item_type'];
		}
		if (!empty($data['group_id'])) {
			$sql .= " AND gs.`group_id` = " . $data['group_id'];
		}
		if (!empty($data['studentId'])) {//required
			$sql .= " AND gs.`stu_id` = " . $data['studentId'];
		}
		if (isset($data['isMaingrade'])) {
			$sql .= " AND gs.`is_maingrade` = " . $data['isMaingrade'];
		}
		if (isset($data['isCurrent'])) {
			$sql .= " AND gs.`is_current` = " . $data['isCurrent'];
		}
		if (!empty($data['degree'])) {
			$sql .= " AND gs.`degree` = " . $data['degree'];
		}
		if (!empty($data['grade'])) {
			$sql .= " AND gs.`grade` = " . $data['grade'];
		}
		if (isset($data['stopType'])) {	//0 = normal,1 stop ,2 suspend,3 = passed,4 graduate
			$sql .= " AND gs.`stop_type` = " . $data['stopType'];
		}
		if (!empty($data['groupId'])) {
			$sql .= " AND gs.`group_id` = " . $data['groupId'];
		}


		$order = " ORDER BY st.stu_khname ASC ";

		if (!empty($data['sortStundent'])) {
			if ($data['sortStundent'] == 1) {
				$order = " ORDER BY st.stu_code  ASC ";
			} else if ($data['sortStundent'] == 2) {
				$order = " ORDER BY st.stu_khname ASC ";
			} else if ($data['sortStundent'] == 3) {
				$studentName = "CONCAT(COALESCE(st.last_name,''),' ',COALESCE(st.stu_enname,''))";
				$order = " ORDER BY $studentName ASC ";
			}
		}

		if (!empty($data['orderitemType'])) {
			$order = " ORDER BY gs.`itemType` ASC ";
		}
		return $db->fetchAll($sql . $order);
	}
	function getItemForPayment($data)
	{//one calling
		$db = $this->getAdapter();
		$currentLang = $this->currentlang();
		$colunmname = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'title';
		}

		$today = date('Y-m-d');

		$sql = "SELECT
			  	gs.itemType,
			  	gs.startDate,
			  	gs.endDate,
			  	gs.feeId,
			  	gs.balance,
			  	gs.academic_year,
			  	gs.grade AS itemDetailId,
			  	(SELECT dcs.discountId FROM `rms_dis_setting` dcs WHERE dcs.studentId=st.stu_id AND endDate >='$today' LIMIT 1) discountType,
			  	(SELECT dcs.discountValue FROM `rms_dis_setting` dcs WHERE dcs.studentId=st.stu_id AND endDate >='$today' LIMIT 1) discountValue,
			  	(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=gs.grade LIMIT 1) as itemDetaillabel,
			  	(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.id=gs.degree LIMIT 1) as itemLabel,
			  	(SELECT rms_itemsdetail.is_onepayment FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=gs.grade LIMIT 1) as is_onepayment
	  	FROM
	  			rms_student as st
	  		LEFT JOIN
		  		`rms_group_detail_student` AS gs
		  	ON st.stu_id=gs.stu_id
		  	
	  	WHERE 1 ";
		if (!empty($data['studentId'])) {
			$sql .= " AND st.`stu_id` = " . $data['studentId'];
		}
		if (isset($data['isMaingrade'])) {
			$sql .= " AND gs.`is_maingrade` = " . $data['isMaingrade'];
		}
		if (isset($data['isCurrent'])) {
			$sql .= " AND gs.`is_current` = " . $data['isCurrent'];
		}
		if (!empty($data['grade'])) {
			$sql .= " AND gs.`grade` = " . $data['grade'];//of product or not in group detail not get data 
		}
		if (isset($data['stopType'])) {	//0 = normal,1 stop ,2 suspend,3 = passed,4 graduate
			$sql .= " AND gs.`stop_type` = " . $data['stopType'];
		}

		$order = " ORDER BY gs.`itemType` ASC ";
		// Application_Model_DbTable_DbUserLog::writeMessageError($sql . $order);
		return $db->fetchAll($sql . $order);
	}
	public function getAllTermbyItemdetail($data)
	{
		//return "<option value='6' >Other</option>";
		try {
			$db = $this->getAdapter();
			$session_lang = new Zend_Session_Namespace('lang');
			$lang = $session_lang->lang_id;

			if ($lang == 1) {// khmer
				$label = "name_kh";
			} else { // English
				$label = "name_en";
			}

			$sql = "SELECT
			  	distinct(tfd.`payment_term`) AS id,
			  	(SELECT $label FROM rms_view WHERE `type`=6 AND key_code =tfd.`payment_term` AND `status`=1 LIMIT 1) as name,
				tf.id as feeId
		  	FROM
			  	rms_tuitionfee AS tf,
			  	rms_tuitionfee_detail AS tfd
		  	WHERE
			  	tf.id = tfd.fee_id
			  	AND tf.status=1
			  	AND tfd.tuition_fee!=0 ";
			if (!empty($data['itemId'])) {
				$sql .= " AND tfd.class_id=" . $data['itemId'];
			}
			if (!empty($data['branchId'])) {
				$sql .= " AND tf.branch_id =" . $data['branchId'];
			}
			if ($data['itemType'] == 1 and !empty($data['academicYear'])) {//school fee
				$sql .= " AND tf.type =1 AND tf.id = " . $data['academicYear'];
			}
			$sql.= " GROUP BY tfd.`payment_term` ";
			$rows = $db->fetchAll($sql);

			$options = "";
			if (!empty($rows)) {
				foreach ($rows as $row) {
					$options .= '<option  value="' . $row['id'] . '" >' . htmlspecialchars($row['name'], ENT_QUOTES) . '</option>';
				}
			}
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			if ($data['itemType'] == 3) {
				$options .= '<option value="5" >' . $tr->translate('ONE_PAYMENTONLY') . '</option>';
			}
			$options .= '<option value="6" >' . $tr->translate('OTHER') . '</option>';
			//$options .= "<option value='6' >Other</option>";
			//Application_Model_DbTable_DbUserLog::writeMessageError($options);
			return $options;
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return "";
		}
	}

	function getServiceForPaymentRecord($data)
	{//for payment only

		$items = array();

		if ($data['studentType'] == 1) {//old student
			$resultItems = array();
			if ($data['isInititilize'] == 1) {
				$resultItems = $this->getItemForPayment($data);//result now year
			}else{
				if(!empty($data['grade'])) {
					$data['Id'] = $data['grade'];
					unset($data['isAutopayment']);
				}
				$resultItems = $this->getItemAllDetail($data);
			}
			
			// $resultItems = array_merge($resultItems, $resultItem2);
		} elseif ($data['studentType'] == 2) {  	//tested
			$resultItems = array();
			if ($data['isInititilize'] == 1) {
				$resultItems = $this->getItemForPayment($data);
			}

			if (!empty($data['grade'])) {
				$data['Id'] = $data['grade'];
			}
			$resultItem2 = $this->getItemAllDetail($data);
			$resultItems = array_merge($resultItems, $resultItem2);
		} else {//corrected
			if (!empty($data['grade'])) {
				$data['Id'] = $data['grade'];
			}
			$resultItems = $this->getItemAllDetail($data);
		}
	
		foreach ($resultItems as $key => $item) {
			$newArray = array(
				'itemType' => $item['itemType'],
				'startDate' => $item['startDate'],
				'endDate' => $item['endDate'],
				'feeId' => empty($item['feeId'])?'':$item['feeId'],
				'balance' => $item['balance'],
				'academic_year' => $item['academic_year'],
				'itemDetailId' => $item['itemDetailId'],
				'discountType' => $item['discountType'],
				'discountValue' => $item['discountValue'],
				'itemDetaillabel' => $item['itemDetaillabel'],
				'is_onepayment' => $item['is_onepayment'],
			);
			
			if($item['itemType']==1 && $item['balance']>0){
				$arrFilter =array(
					'studentId'=>$data['studentId'],
					'itemDetailId' => $item['itemDetailId'],
				); 
				$lastPaidTuition = $this->getStuLastPaidTutionFee($arrFilter);
				if(!empty($lastPaidTuition)){
					$newArray['academicFeeTermId'] = $lastPaidTuition["academicFeeTermId"];
				}
			}else{
				$newArray['academicFeeTermId'] = 0;
			}

			$param = array(
				'branchId' => $data['branch_id'],//not yet pass data
				'itemType' => $item['itemType'],
				'itemId' => $item['itemDetailId'],
				'academicYear' => empty($item['feeId'])?$data['feeId']:$item['feeId'],
			);

			$newArray['termTypeList'] = $this->getAllTermbyItemdetail($param);//month,semester,year...

			$param = array(
				'branch_id' => $data['branch_id'],
				'isFinished'=>$data['isFinished'],
				'option' => 1,
			);
			$academicYearList = $this->getAllYearByBranch($param);
			$newArray['academicYearList'] = $academicYearList;

			$param = array(
				'branch_id' => $data['branch_id'],
				'academic_year' => $item['academic_year'],
				'option'=>1,
			);

			$termStudy = $this->getAllStudyPeriod($param);//វគ្គចូលរៀន
			$newArray['studyPeriodList'] = $termStudy;

			$param = array(
				'studentId'=>$data['studentId'],
				'grade'=>($data['isInititilize']==1)?$item['itemDetailId']:$data['grade'],
				'optionList'=>1,
				'fetchAll'=>1
			);
			$newArray['discountOptionList'] =$this->getDiscountListbyStudent($param);//វគ្គចូលរៀន

			array_push($items, $newArray);
		}

		return $items;
	}

	function getAllGroupName($data = null)
	{

		$db = $this->getAdapter();
		$lang = $this->currentlang();
		$grade = "rms_itemsdetail.title_en";
		if ($lang == 1) {// khmer
			$grade = "rms_itemsdetail.title";
		}
		$sql = "SELECT `g`.`id`,
				  	CONCAT(g.group_code,' ',(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1)) AS name
				  	
				  	FROM `rms_group` AS `g` 
  					
  					WHERE g.status=1 
  						AND group_code!='' ";
		$forfilterreport = empty($data['forfilter']) ? null : $data['forfilter'];
		if (!empty($forfilterreport)) {
			$sql .= " AND (g.is_pass=1 OR g.is_pass=2 OR g.is_pass=3) ";// group studying/completed
		} else {
			$sql .= " AND (g.is_pass=0 OR g.is_pass=2) ";// group studying/not complete
		}

		if (!empty($data['branch_id'])) {
			$sql .= " AND g.branch_id = " . $data['branch_id'];
		}

		if (!empty($data['academic_year'])) {
			$sql .= " AND g.academic_year = " . $data['academic_year'];
		}
		if (!empty($data['teacherId'])) {
			$sql .= " AND g.teacher_id=" . $data['teacherId'];
		}

		if (!empty($data['group_id'])) {
			$sql .= " AND g.id != " . $data['group_id'];
		}
		if (isset($data['isUse'])) {
			$sql .= " AND g.is_use= " . $data['isUse'];
		}

		if (!empty($data['change_type']) and $data['change_type'] == 2) {//ឡើងថ្នាក់
			if (!empty($data['toGrade'])) {
				$sql .= " AND g.grade = " . $data['toGrade'];
			} else {
				$sql .= " AND g.grade != " . $data['grade'];
			}
		} elseif (!empty($data['change_type']) and $data['change_type'] == 3) {//ឆ្លងភូមិសិក្សា
			if (!empty($data['degree'])) {
				$sql .= " AND g.degree != " . $data['degree'];
			}
		} else {
			if (!empty($data['degree'])) {
				$sql .= " AND g.degree = " . $data['degree'];
			}
			if (!empty($data['grade'])) {
				$sql .= " AND g.grade = " . $data['grade'];
			}
		}

		$sql .= $this->getAccessPermission('g.branch_id');
		$sql .= $this->getDegreePermission('g.degree');
		$sql .= $this->getSchoolOptionAccess('(SELECT i.schoolOption FROM `rms_items` AS i WHERE i.type=1 AND i.id = g.degree LIMIT 1)');

		$sql .= " ORDER BY g.degree,g.grade,g.group_code ASC,`g`.`id` DESC ";
		return $db->fetchAll($sql);
	}
	function getAllBank($data = array())
	{
		$sql = "
	  	SELECT
	  	ba.id,
	  	CONCAT(COALESCE(ba.bank_name,'')) AS name
  	";
		$sql .= " FROM `rms_bank` AS ba
	  		WHERE ba.bank_name!='' AND ba.status=1 ";
		$sql .= " ORDER BY ba.bank_name ASC ";

		$db = $this->getAdapter();
		$row = $db->fetchAll($sql);
		return $row;
	}
	function getItemDetailRow($data)
	{
		$sql = "SELECT 
  				i.id,
  				i.items_id,
  				i.items_type,
		  		i.code, i.title,i.title_en,
		  	 	i.ordering,i.shortcut,
		  	  	i.product_type,
		  		i.is_productseat,
  	 			i.schoolOption,
  	 			i.is_autopayment
  			FROM `rms_itemsdetail` i WHERE 
  		i.status=1 ";

		if (!empty($data['Id'])) {
			$sql .= " AND i.id=" . $data['Id'];
		}
		if (!empty($data['itemsId'])) {
			$sql .= " AND i.items_id=" . $data['itemsId'];
		}
		if (!empty($data['itemsType'])) {
			$sql .= " AND i.items_type=" . $data['itemsType'];
		}
		if (!empty($data['productType'])) {
			$sql .= " AND i.product_type=" . $data['productType'];
		}
		if (isset($data['isProductseat'])) {
			$sql .= " AND i.is_productseat=" . $data['isProductseat'];
		}
		if (isset($data['isOnepayment'])) {
			$sql .= " AND i.is_onepayment=" . $data['isOnepayment'];
		}
		return $this->getAdapter()->fetchRow($sql);
	}

	function getItemAllDetail($data)
	{
		$currentLang = $this->currentlang();
		$colunmname = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'title';
		}
		$studentId = !empty($data['studentId']) ? $data['studentId'] : 0;
		$today = date('Y-m-d');

		$sql = "SELECT
  	
  			i.items_type AS itemType,
  			i.id AS itemDetailId,
		  	$colunmname  AS itemDetaillabel,
		  	(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.id=items_id LIMIT 1) as itemLabel,
		  	is_onepayment,
		  	i.items_id as itemId,
		  	(SELECT ds.discountId FROM `rms_dis_setting` ds WHERE studentId=$studentId AND ds.endDate >='$today' LIMIT 1) AS discountType,
		  	(SELECT ds.discountValue FROM `rms_dis_setting` ds WHERE studentId=$studentId AND endDate >='$today' LIMIT 1) AS discountValue,
		  	(SELECT ds.startDate FROM `rms_dis_setting` ds WHERE studentId=$studentId AND ds.endDate >='$today' LIMIT 1) AS startDate,
		  	(SELECT ds.endDate FROM `rms_dis_setting` ds WHERE studentId=$studentId AND ds.endDate >='$today' LIMIT 1) AS endDate,
  			'' AS feeId,
  			0 AS balance,
  			'' AS academic_year
  			
  		FROM `rms_itemsdetail` i WHERE
  			i.status=1 ";

		if (!empty($data['Id'])) {
			$sql .= " AND i.id=" . $data['Id'];
		}
		if(!empty($data['itemsId'])){
			$sql.=" AND i.items_id=".$data['itemsId'];
		}
		if(!empty($data['itemsType'])){
			$sql.=" AND i.items_type=".$data['itemsType'];
		}
		if(!empty($data['productType'])){
			$sql.=" AND i.product_type=".$data['productType'];
		}
		if(isset($data['isProductseat'])){
			$sql.=" AND i.is_productseat=".$data['isProductseat'];
		}
		if (isset($data['isOnepayment'])) {
			$sql .= " AND i.is_onepayment=" . $data['isOnepayment'];
		}
		
		if (isset($data['isAutopayment']) and $data['isInititilize'] == 1) {
			if ($data['isAutopayment'] != '') {
				if($data['studentType']==1){
					//$sql .= " AND FIND_IN_SET(COALESCE((SELECT degree FROM `rms_group_detail_student` WHERE stu_id=$studentId ORDER BY gd_id DESC LIMIT 1),0),degreeOption) ";
					$sql .= " AND FIND_IN_SET(COALESCE((SELECT DISTINCT(grade) FROM `rms_group_detail_student` WHERE stu_id=$studentId ORDER BY gd_id DESC LIMIT 1),0),i.id) ";
				} else {
					$sql .= " AND i.is_autopayment=" . $data['isAutopayment'];
				}
			}
		}
		$sql .= " ORDER BY i.items_type ASC ";
		//Application_Model_DbTable_DbUserLog::writeMessageError($sql);
		return $this->getAdapter()->fetchAll($sql);//fetch all but got only 1 row only .

	}

	public function getAllGradingSetting($data = array())
	{
		$db = $this->getAdapter();
		$branch_id = empty($data['branch_id']) ? 0 : $data['branch_id'];
		$schoolOption = empty($data['schoolOption']) ? 0 : $data['schoolOption'];
		$sql = " SELECT 
					grding.id,
					grding.title As name ";
		$sql .= " FROM `rms_scoreengsetting` AS grding ";
		$sql .= "  WHERE grding.status=1 AND grding.title!='' ";
		if (!empty($branch_id)) {
			$sql .= " AND grding.branch_id =$branch_id ";
		}
		if (!empty($schoolOption)) {
			$sql .= " AND grding.schoolOption =$schoolOption ";
		}
		if (!empty($data['degreeId'])) {
			$sql .= " AND grding.degreeId =" . $data['degreeId'];
		}
		$sql .= $this->getAccessPermission();
		$sql .= " ORDER BY grding.title ASC ";
		return $db->fetchAll($sql);
	}
	function getOneStudentGroupDetailData($data)
	{
		$db = $this->getAdapter();

		$currentLang = $this->currentlang();
		$colunmname = 'title_en';
		if ($currentLang == 1) {
			$colunmname = 'title';
		}

		$sql = "SELECT
	   	st.stu_code AS stu_code,
	   	(CASE WHEN st.stu_khname IS NULL THEN st.stu_enname ELSE stu_khname END) AS stu_name,
	   	st.sex,
	   	gs.`stu_id`,
	   	gs.is_maingrade,
	   	gs.itemType,
	   	gs.startDate,
	   	gs.endDate,
	   	gs.feeId,
	   	gs.balance,
	   	gs.group_id,
	   	gs.academic_year,
	   	gs.degree,
	   	gs.degree AS itemId,
	   	gs.grade,
	   	gs.grade AS itemDetailId,
	   	(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=gs.grade LIMIT 1) as itemDetaillabel,
	   	(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.id=gs.degree LIMIT 1) as itemLabel
   	FROM
	   	`rms_group_detail_student` AS gs,
	   	rms_student as st
   	WHERE
   	st.stu_id=gs.stu_id ";
		if (!empty($data['item_type'])) {
			$sql .= " AND gs.`itemType` = " . $data['item_type'];
		}
		if (!empty($data['group_id'])) {
			$sql .= " AND gs.`group_id` = " . $data['group_id'];
		}
		if (!empty($data['studentId'])) {
			$sql .= " AND gs.`stu_id` = " . $data['studentId'];
		}
		if (isset($data['isMaingrade'])) {
			$sql .= " AND gs.`is_maingrade` = " . $data['isMaingrade'];
		}
		if (isset($data['isCurrent'])) {
			$sql .= " AND gs.`is_current` = " . $data['isCurrent'];
		}
		if (!empty($data['degree'])) {
			$sql .= " AND gs.`degree` = " . $data['degree'];
		}
		if (!empty($data['grade'])) {
			$sql .= " AND gs.`grade` = " . $data['grade'];
		}
		if (isset($data['stopType'])) {	//0 = normal,1 stop ,2 suspend,3 = passed,4 graduate
			$sql .= " AND gs.`stop_type` = " . $data['stopType'];
		}
		if (!empty($data['groupId'])) {
			$sql .= " AND gs.`group_id` = " . $data['groupId'];
		}
		if (!empty($data['branchId'])) {
			$sql .= " AND s.`branch_id` = " . $data['branchId'];
		}
		$sql .= " LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function AddItemToGroupDetailStudent($data)
	{
		try {
			$arr = array(
				'studentId' => $data['studentId'],
				'grade' => $data['grade'],
				'isCurrent' => $data['isCurrent'],
				'isMaingrade' => $data['isMaingrade'],
				'stopType' => $data['stopType']
			);
			$resultDetail = $this->getOneStudentGroupDetailData($arr);

			if (empty($resultDetail)) {
				$academicYear = '';
				if (!empty($data['feeId'])) {
					$result = $this->getFeeStudyinfoByIdGloable($data['feeId']);
					$academicYear = empty($result) ? '' : $result['academicYearId'];
				}

				if (empty($data['schoolOption'])) {
					$data['schoolOption'] = $this->getSchoolOptionbyDegree($data['degree']);
				}

				$_arr = array(
					'branch_id' => $data['branch_id'],
					'stu_id' => $data['studentId'],
					'itemType' => $data['itemType'],
					'grade' => $data['grade'],
					'degree' => $data['degree'],
					'feeId' => $data['feeId'],
					'academic_year' => empty($data['academicYear']) ? $academicYear : $data['academicYear'],
					'startDate' => $data['startDate'],
					'endDate' => $data['endDate'],
					'balance' => $data['balance'],
					'isoldBalance' => ($data['balance'] > 0 ? 1 : 0),
					'discount_type' => $data['discountType'],
					'discount_amount' => $data['discountAmount'],
					'school_option' => $data['schoolOption'],
					'is_maingrade' => $data['isMaingrade'],
					'is_current' => $data['isCurrent'],
					'stop_type' => $data['stopType'],
					'is_setgroup' => empty($data['isSetGroup']) ? 0 : 1,
					'is_newstudent' => $data['isNewStudent'],
					'status' => 1,
					'note' => $data['remark'],
					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'user_id' => $this->getUserId(),
					'entryFrom' => $data['entryFrom'],//1 crm,2 foundation,3change group,4 payment,5 Cashier Init
				);
				if (!empty($data['groupId'])) {
					$_arr['group_id'] = $data['groupId'];
				}
				if (!empty($data['oldGroup'])) {
					$_arr['old_group'] = $data['oldGroup'];
				}
				if (!empty($data['partTimeId'])) {
					$_arr['session'] = $data['partTimeId'];
				}
				if (!empty($data['oldFeeId'])) {
					$_arr['oldFeeId'] = $data['oldFeeId'];
				}
				$this->_name = 'rms_group_detail_student';
				$id = $this->insert($_arr);
			}
		} catch (Exception $e) {
			Application_Form_FrmMessage::message("INSERT_FAIL");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public static function getTeacherUserId()
	{
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$userId = $dbExternal->getUserExternalId();
		$userId = empty($userId) ? 0 : $userId;
		return $userId;
	}
	public function getCertificateSetting($id)
	{
		$db = $this->getAdapter();
		$sql = " SELECT * FROM `rms_certificate_setting` WHERE TYPE=" . $id;
		return $db->fetchRow($sql);
	}
	function checkEntryScoreSetting($data)
	{
		$currentDate = date('Y-m-d');
		$sql = "SELECT 
			id,
	   		fromDate,
	   		endDate,
	   		examFromDate,
	   		examEndDate,
	   		title,
	   		degreeId 
   		FROM `rms_score_entry_setting` ";
		$sql .= " WHERE status=1 AND '" . $currentDate . "'>=fromDate AND '" . $currentDate . "'<=endDate ";
		if (!empty($data['degreeId'])) {
			$sql .= " AND  FIND_IN_SET( " . $data['degreeId'] . ", degreeId )";
		}
		$sql .= " ORDER BY examEndDate DESC  ";
		return $this->getAdapter()->fetchRow($sql);
	}
	function checkTecherEntrySetting($data)
	{
		$currentDate = date('Y-m-d');
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
	function checkScoreType($data)
	{
		$sql = "SELECT * FROM `rms_grading` WHERE status=1  ";
		if (!empty($data['settingEntryId'])) {
			$sql .= " AND settingEntryId =" . $data['settingEntryId'];
		}
		if (!empty($data['groupId'])) {
			$sql .= " AND groupId =" . $data['groupId'];
		}
		if (!empty($data['subjectId'])) {
			$sql .= " AND subjectId =" . $data['subjectId'];
		}
		if (!empty($data['forMonth'])) {
			$sql .= " AND forMonth =" . $data['forMonth'];
		}
		if (!empty($data['teacherId'])) {
			$sql .= " AND teacherId =" . $data['teacherId'];
		}
		$sql .= " ORDER BY id DESC limit 1 ";
		return $this->getAdapter()->fetchRow($sql);
	}

	function getUserProfile()
	{
		$userID = $this->getUserId();
		$userID = empty($userID) ? 0 : $userID;
		$db = $this->getAdapter();
		$sql = "SELECT u.* FROM rms_users AS u WHERE u.id=" . $userID;
		return $db->fetchRow($sql);
	}
	function getAllDegreeOption($_data)
	{
		$db = $this->getAdapter();
		$currentLang = $this->currentlang();
		$columnName = "title";
		if ($currentLang == 2) {
			$columnName = "title_en";
		}
		$this->_name = "rms_items";
		$sql = "
			SELECT 
				deg.`id` AS id
				,CONCAT('(',COALESCE(deg.`shortcut`,''),')',' ',COALESCE(deg." . $columnName . ",'')) AS `name`
			FROM $this->_name AS deg 
			WHERE 
				deg.`status`=1
				AND deg.`type`=1
			
		";
		$sql .= " AND COALESCE(deg.is_parent,0) = 0 ";
		$user = $this->getUserProfile();
		$level = $user['user_type'];
		if (!empty($_data['schooloptoncheck'])) {
			$s_where = array();
			foreach ($_data['schooloptoncheck'] as $ss) {
				$s_where[] = "(FIND_IN_SET('" . $ss . "', deg.schoolOption) ) ";
			}
			$sql .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}

		$sql .= "
			ORDER BY 
				deg.`ordering` ASC
				,deg.id ASC
		";
		return $db->fetchAll($sql);
	}

	function getLatestAcadmicYear()
	{
		$db = $this->getAdapter();
		$sql = "
			SELECT 
				ac.* 
				,CONCAT(COALESCE(ac.`fromYear`,''),'-',COALESCE(ac.`toYear`,'')) AS academicYear
			FROM 
				`rms_academicyear` AS  ac 
			WHERE 
				ac.`status` = 1 
				AND ac.isCurrent = 1
				ORDER BY ac.`id` DESC 
			LIMIT 1
		";
		return $db->fetchRow($sql);
	}

	function getAllPartTimeList($data)
	{
		$db = $this->getAdapter();
		$degreeId = empty($data["degree"]) ? 0 : $data["degree"];
		$branchId = $data["branchId"];
		$branchIdStr = $this->getAccessPermission("pt.`branchId`");

		$currentLang = $this->currentlang();
		$columnName = "titleKh";
		if ($currentLang == 2) {
			$columnName = "title";
		}

		$sql = "	SELECT 
					pt.* 
					,pt.id AS `id`
					,pt.$columnName AS `name`
				FROM `rms_parttime_list` AS pt
				WHERE pt.`status` =1 $branchIdStr   ";
		$sql .= " AND FIND_IN_SET($degreeId,pt.`degreeId`) ";

		if (!empty($branchId)) {
			$sql .= " AND pt.`branchId` =$branchId ";
		}
		$sql .= " ORDER BY pt.`title` ASC ";
		return $db->fetchAll($sql);
	}
	function getDiscountListbyStudent($data)
	{
		if (!empty($data['resultList'])) {
			$sql = "
				SELECT
					ds.discountId AS id,
					(SELECT dis_name FROM `rms_discount` WHERE disco_id =ds.discountId LIMIT 1) name
				FROM
					`rms_dis_setting` as ds WHERE 1 ";
		} else {
			$currentLang = $this->currentlang();
			$colunmname = 'name_en';
			if ($currentLang == 1) {
				$colunmname = 'name_kh';
			}

			$sqlDiscountFor = "(SELECT $colunmname FROM `rms_view` WHERE TYPE=37 AND key_code=ds.discountFor LIMIT 1)";
			$sqlforType = "(SELECT $colunmname FROM `rms_view` WHERE type=38 AND key_code=ds.discountForType LIMIT 1) ";
			$sqlPeriod = "(SELECT $colunmname FROM `rms_view` WHERE type=39 AND key_code=ds.discountPeriod LIMIT 1) ";

			$sql = "
				SELECT
					ds.id,
					ds.discountTitle,
					$sqlDiscountFor AS discountFor,
					disc.studentId,
					disc.grade,
					ds.discountId AS discountTypeId,
					(SELECT
						dis_name
					FROM
						`rms_discount`
					WHERE disco_id = ds.discountId
					LIMIT 1) discountName,

					$sqlforType as discountForType,
					ds.degree,
					ds.DisValueType as DiscountValueType,
					ds.discountValue,
					
					
					CONCAT(ds.discountValue, 
					(CASE WHEN DisValueType=1 THEN '%' WHEN DisValueType=2 THEN '$' END )) AS DisValueType,	
					CONCAT(COALESCE($sqlPeriod),'',COALESCE(DATE_FORMAT(ds.startDate,'%d-%m-%Y'),''),'/',COALESCE(DATE_FORMAT(ds.endDate,'%d-%m-%Y'),'')) AS discountPeriod, 
					
					DATE_FORMAT(ds.endDate,'%d-%b') endDay,
					DATE_FORMAT(ds.endDate,'%y') endYear
				FROM `rms_dis_setting` AS ds
					LEFT JOIN rms_discount_student disc
					ON ds.`id`=disc.`discountGroupId`
				WHERE ds.isCurrent=1 ";
		}
		if (!empty($data['discountPeriod'])) {
			$sql .= " AND ds.discountPeriod=" . $data['discountPeriod'];
		}

		$sqldiscountId = '';
		$sqldiscountCateId = '';
		$sqlStuId = '';
		$sqldegree = '';
		$sqlGrade = '';
		$sqlPeriod = '';
		$sqlPeriod = '';

		if (!empty($data['id'])) {//discount setting id
			$sqldiscountId .= " AND ds.id=" . $data['id'];
		}
		if (!empty($data['discountForType'])) {
			$sqldiscountId.= " AND ds.discountForType=" . $data['discountForType'];
		}
		if (!empty($data['discountId'])) {//discount category id
			$sqldiscountCateId .= " AND ds.discountId=" . $data['discountId'];
		}
		if (!empty($data['studentId'])) {
			$sqlStuId .= " AND disc.studentId=" . $data['studentId'];
		}
		if (!empty($data['degree'])) {
			$sqldegree .= " AND FIND_IN_SET (" . $data['degree'] . ",ds.degree)";
		}
		if (!empty($data['grade'])) {
			$sqlGrade .= " AND FIND_IN_SET((SELECT d.items_id FROM `rms_itemsdetail` d WHERE d.id=".$data['grade']." LIMIT 1),ds.degree)";
		}
		
		
		$condition1 =$sqldiscountId.$sqldegree.$sqlGrade.$sqldiscountCateId;
		$condition2 = " AND disc.isCurrent=1".$sqldiscountId.$sqlStuId.$sqldegree.$sqlGrade.$sqldiscountCateId;
		$sql .= " AND CASE 
					WHEN `discountFor`=3 THEN `discountFor`=3 $condition1 		
					WHEN (`discountFor`=1 OR `discountFor`=2) THEN 1 $condition2
					END ";

		$from_date =" ds.startDate <= '".date("Y-m-d")."'";
		$to_date =" ds.endDate >= '".date("Y-m-d")."'";
		$WherediscountPromotion = " AND ".$from_date." AND ".$to_date;//check discount period first 

		$sql .= " AND CASE 
			WHEN `discountPeriod`=2 THEN `discountPeriod`=2 $WherediscountPromotion 	
			ELSE  1	
		END";
		// Application_Model_DbTable_DbUserLog::writeMessageError($sql);
		if(!empty($data['fetchAll'])){
			$result =  $this->getAdapter()->fetchAll($sql);
			if (!empty($result) AND !empty($data['optionList'])) {
				$options = '';
				$options .= '<option data-discountype="" data-discount-value="" value="" ></option>';
				foreach ($result as $value) {
					$selected =count($result)==1?"selected='selected'":"";
					if (!empty($data['grade'])) {
						if ($data['grade'] ==$value['grade']) {
							$selected = "selected='selected'";
						}
					}
					$options .= '<option '.$selected.'  data-discountype="'.$value['DiscountValueType'].'" data-discount-value="'.$value['discountValue'].'" value="'.$value['id'].'" >' . htmlspecialchars($value['discountTitle'], ENT_QUOTES) . '</option>';
				}
			return $options;
			} else {
				return $result;
			}
		} else {
			return $this->getAdapter()->fetchRow($sql);
		}
	}
	
	function getChildItems($arr = array(),$idetity = null){
		$db = $this->getAdapter();
		$parentId = empty($arr["categoryId"]) ? 0 : $arr["categoryId"];
		$itemsType = empty($arr["itemsType"]) ? 1 : $arr["itemsType"];
		
		$sql=" SELECT 
				i.`id` 
			FROM `rms_items` AS i 
			WHERE i.`parent` = $parentId AND i.`status`=1 ";
		$child = $db->fetchAll($sql);
		if(!empty($child)){
			foreach ($child as $va) {
				if (empty($idetity)){
					$idetity=$parentId.",".$va['id'];
				}else{$idetity=$idetity.",".$va['id'];
				}
				$arr["categoryId"] = empty($va['id']) ? 0 : $va['id'];
				$idetity = $this->getChildItems($arr,$idetity);
			}
		}
		return $idetity;
	}
	
	function getStuLastPaidTutionFee($data){
		$db = $this->getAdapter();
		$studentId = empty($data["studentId"]) ? 0 : $data["studentId"];
		$serviceType = empty($data["serviceType"]) ? 1 : $data["serviceType"];
		$sql="
			SELECT 
				pd.*
				,p.academic_year AS feeId
				,COALESCE((SELECT vTp.termPaidList FROM v_studenttutionfeepaid AS vTp WHERE vTp.`serviceType` = 1 AND vTp.gradeId = pd.`itemdetail_id` AND vTp.`studentId` = p.`student_id` LIMIT 1),0) AS termId
				,COALESCE((SELECT te.`title` FROM `rms_startdate_enddate` AS te WHERE te.id =pd.`academicFeeTermId` LIMIT 1),'') AS termTitle
				,COALESCE((SELECT te.`periodId` FROM `rms_startdate_enddate` AS te WHERE te.id =pd.`academicFeeTermId` LIMIT 1),0) AS periodId
		";
		$sql.=" FROM `rms_student_paymentdetail` AS pd
					JOIN `rms_student_payment` AS p  ON p.`id` = pd.`payment_id`  
			";
		$sql.=" WHERE 
					p.`status` = 1
					AND pd.`service_type` = $serviceType
					AND p.`is_void` = 0
					AND p.`student_id` = $studentId  
				";
		if(!empty($data["paymentId"])){
			$sql.=" AND p.`id` = ".$data["paymentId"];
		}
		if(!empty($data["itemDetailId"])){
			$sql.=" AND pd.`itemdetail_id` = ".$data["itemDetailId"];
		}
		$sql.=" ORDER BY p.id DESC ";
		$sql.=" LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	public function getPaytermTypeList($data=array())
	{
		$ptList = array(
				array(
					'id' => "1",
					'name' => $this->tr->translate("TERM_1"),
					'parent' => 2,
				),
				array(
					'id' => "2",
					'name' => $this->tr->translate("TERM_2"),
					'parent' => 2,
				),
				array(
					'id' => "3",
					'name' => $this->tr->translate("TERM_3"),
					'parent' => 2,
				),
				array(
					'id' => "4",
					'name' => $this->tr->translate("TERM_4"),
					'parent' => 2,
				),
				array(
					'id' => "1,2",
					'name' => $this->tr->translate("SEMSTER_1"),
					'parent' => 3,
				),
				array(
					'id' => "3,4",
					'name' => $this->tr->translate("SEMSTER_2"),
					'parent' => 3,
				),
				array(
					'id' => "1,2,3,4",
					'name' => $this->tr->translate("YEAR"),
					'parent' => 4,
				),
			);
		if (!empty($ptList) AND !empty($data['optionList'])) {
			$options = '';
			$options .= '<option data-parent="" value="0" ></option>';
			foreach ($ptList as $key => $value) {
				$selected = ($key==0)? "selected='selected'":"";
				$options .= '<option '.$selected.'  data-parent="'.$value['parent'].'" value="'.$value['id'].'" >' . htmlspecialchars($value['name'], ENT_QUOTES) . '</option>';
			}
			return $options;
		}else{
			return $ptList;
		}
	}
}
?>