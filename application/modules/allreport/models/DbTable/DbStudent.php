<?php
class Allreport_Model_DbTable_DbStudent extends Zend_Db_Table_Abstract
{
	public  function getStudentInfoReport($search){
		$_db = $this->getAdapter();
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbGb->currentlang();
		$viewShort = $dbGb->getViewLabelDisplay("shortcut");
		$viewFull = $dbGb->getViewLabelDisplay();
		
		$occuTitle="occu_enname";
		if ($currentLang==1){
			$occuTitle="occu_name";
		}
		
		$sql = "
		SELECT 
			s.stu_id
			,s.stu_enname
			,s.stu_khname
			,(SELECT v.$viewShort FROM `rms_view` AS v WHERE v.type=2 AND v.key_code = s.sex LIMIT 1) as gender
			,s.stu_code
			,s.dob
			,s.remark
			,s.tel
			,s.email
			,(SELECT province_kh_name FROM `rms_province` WHERE `province_id`= s.province_id LIMIT 1) as pro
			
			,fam.fatherNameKh AS father_khname 
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
				
			,(SELECT occ.$occuTitle FROM `rms_occupation` AS occ WHERE occ.`occupation_id` = fam.fatherJob LIMIT 1) AS far_job
			,(SELECT occ.$occuTitle FROM `rms_occupation` AS occ WHERE occ.`occupation_id` = fam.motherJob LIMIT 1) AS mom_job
			,(SELECT occ.$occuTitle FROM `rms_occupation` AS occ WHERE occ.`occupation_id` = fam.guardianJob LIMIT 1) AS guar_job
			
			
			,s.address
			,s.home_num
			,s.street_num
			,s.village_name
			,s.commune_name
			,s.district_name
			,s.remark
			,(SELECT v.$viewFull FROM `rms_view` AS v WHERE v.type=1 AND v.key_code = s.status LIMIT 1) as status
			,s.nationality
		FROM 
			rms_student AS s
			LEFT JOIN rms_family AS fam ON fam.id = s.familyId
		where s.status = 1";
		
		$sql.='';
		if(empty($search)){
			$_db->fetchAll($sql);
		}
		if(!empty($search['txtsearch']))
		{
			$s_where = array();
			$s_search = trim($search['txtsearch']);
			
			$s_where[] = " REPLACE(s.stu_code,' ','')   	LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(s.stu_khname,' ','')   	LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(s.stu_enname,' ','')   	LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(s.last_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[] = " CONCAT(s.last_name,s.stu_enname) LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(s.tel,' ','')  			LIKE '%{$s_search}%'";
			
			
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
		}
		return $_db->fetchAll($sql);
	}
	function getAllCRM($search=array()){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$lang = $dbp->currentlang();
		if($lang==1){// khmer
			$label = "name_kh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
			$branch = "b.branch_namekh";
			$colunmname='title';
		}else{ // English
			$label = "name_en";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
			$branch = "b.branch_nameen";
			$colunmname='title_en';
			
		}
		$sql="SELECT c.*,
			(SELECT $branch FROM `rms_branch` AS b  WHERE b.br_id = c.branch_id LIMIT 1) AS branch_name,
			s.stu_khname AS stuNameKh,
			CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stuNameEn,
			(SELECT $label FROM `rms_view` WHERE type=2 AND key_code = s.sex LIMIT 1) AS stuSex,
			
			(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = ds.degree AND i.type=1 AND ds.is_maingrade=1 LIMIT 1) AS degree,
			(SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = ds.grade AND idd.items_type=1 AND ds.is_maingrade=1 LIMIT 1) AS grade,
			
			(SELECT $label FROM `rms_view` WHERE type=2 AND key_code = c.sex LIMIT 1) AS sexTitle,			
			
			CASE
			WHEN  c.ask_for = 1 THEN '".$tr->translate("KHMER_KNOWLEDGE")."'
			WHEN  c.ask_for = 2 THEN '".$tr->translate("ENGLISH")."'
			WHEN  c.ask_for = 3 THEN '".$tr->translate("UNIVERSITY")."'
			WHEN  c.ask_for = 4 THEN '".$tr->translate("OTHER")."'
			END AS ask_for_title,
			CASE
			WHEN  c.crm_status = 0 THEN '".$tr->translate("CANCEL")."'
			WHEN  c.crm_status = 1 THEN '".$tr->translate("PROGRESSING")."'
			WHEN  c.crm_status = 2 THEN '".$tr->translate("WAITING_COMPLETED")."'
			WHEN  c.crm_status = 3 THEN '".$tr->translate("COMPLETED")."'
			END AS crm_status_title,
			(SELECT k.title FROM `rms_know_by` AS k WHERE k.id = c.know_by LIMIT 1 ) AS know_by_title,
			c.know_by,
			(SELECT COUNT(cr.id) FROM `rms_crm_history_contact` AS cr WHERE cr.crm_id = c.id LIMIT 1) AS amountContact,
			CASE
				WHEN  s.customer_type = 1 THEN '".$tr->translate("STUDENT")."'
				WHEN  s.customer_type = 3 THEN '".$tr->translate("CRM")."'
				WHEN  s.customer_type = 4 THEN '".$tr->translate("TESTED")."'
			END AS customer_type,
			
			DATE_FORMAT(c.create_date, '%Y-%m-%d') AS create_date,
			CASE
			WHEN  c.followup_status = 1 THEN '".$tr->translate("FOLLOW_UP")."'
			WHEN  c.followup_status = 0 THEN '".$tr->translate("STOP_FOLLOW_UP")."'
			END AS followup_status,
			c.prev_concern,
			(SELECT CONCAT(first_name) FROM rms_users WHERE c.user_id=id LIMIT 1 ) AS userby
			FROM 
				rms_student AS s,
				rms_group_detail_student AS ds,
				`rms_crm` AS c
			WHERE 
				ds.itemType=1 
				AND s.crm_id = c.id AND ds.stu_id = s.stu_id
		";
		$where = ' ';
		$from_date =(empty($search['start_date']))? '1': " c.create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " c.create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where.= " AND  ".$from_date." AND ".$to_date;
		if(!empty($search['advance_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['advance_search'])));
			$s_where[] = " REPLACE(c.kh_name,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(c.first_name,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(c.last_name,' ','') LIKE '%{$s_search}%'";
			$s_where[]=	 " REPLACE(CONCAT(c.last_name,c.first_name),' ','') LIKE '%{$s_search}%'";
			$s_where[]=	 " REPLACE(CONCAT(c.first_name,c.last_name),' ','')	LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(c.tel,' ','') LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_search'])){
			$where.= " AND c.branch_id = ".$db->quote($search['branch_search']);
		}
		if(!empty($search['ask_for_search'])){
			$where.= " AND c.ask_for = ".$db->quote($search['ask_for_search']);
		}
		if(!empty($search['know_by_search'])){
			$where.= " AND c.know_by = ".$db->quote($search['know_by_search']);
		}
		if($search['followup_status']>-1){
			$where.= " AND c.followup_status = ".$db->quote($search['followup_status']);
		}
		
		if(!empty($search['degree'])){
			$where.= " AND ds.degree = ".$db->quote($search['degree']);
		}
		if(!empty($search['grade'])){
			$where.= " AND ds.grade = ".$db->quote($search['grade']);
		}
		if($search['status_search']>-1){
			$where.= " AND c.crm_status = ".$db->quote($search['status_search']);
		}
		if($search['crm_process']>-1){
			$where.= " AND s.customer_type = ".$db->quote($search['crm_process']);
		}
		if(!empty($search['school_option'])){
			$where.= " AND ds.school_option = ".$db->quote($search['school_option']);
		}
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('c.branch_id');
		$where.=$dbp->getDegreePermission('ds.degree');
		$where.=" ORDER BY c.id DESC";
		$row = $db->fetchAll($sql.$where);
		$resutl = $row;
		if (!empty($search['prev_concern'])){
			$resutl = array();
			$epl = explode(",", $search['prev_concern']);
			$array = array();
			foreach ($epl as $ss){
				$key = $this->checkPrevConcern($ss,22);
				$array[$key] = $key;
			}
			
			if (!empty($row)) foreach ($row as $key => $rs){
				$exp = explode(",", $rs['prev_concern']);
				foreach ($exp as $ss){
					if (in_array($ss, $array)) {
						$resutl[$key] = $rs;
						break;
					}
				}
			}
		}
		return $resutl;
	}
	function checkPrevConcern($value,$type){
		$db = $this->getAdapter();
		$sql="SELECT v.key_code FROM `rms_view` AS v WHERE v.name_kh = '$value' AND v.type=$type  LIMIT 1";
		return $db->fetchOne($sql);
	}
	function getAllCRMDailyContact($search = ''){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$lang = $dbp->currentlang();
		
		$branch= $dbp->getBranchDisplay();
		$label = "name_en";
		$grade = "rms_itemsdetail.title_en";
		$degree = "rms_items.title_en";
			
		if($lang==1){// khmer
			$label = "name_kh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
		}
		$sql="
			SELECT 
				cc.id,
				cc.crm_id,
				cc.contact_date,
				cc.feedback,
				cc.next_contact,
				cc.user_contact,
				cc.feedback_type,
				(SELECT CONCAT(first_name) FROM rms_users WHERE cc.user_contact=id LIMIT 1 ) AS user_contact,
				(SELECT $branch FROM `rms_branch` AS b  WHERE b.br_id = c.branch_id LIMIT 1) AS branch_name,
				(SELECT CONCAT(first_name) FROM rms_users WHERE c.user_id=rms_users.id LIMIT 1 ) AS userby,
				 (SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE cc.user_contact=rms_users.id LIMIT 1 ) AS user_contact_name,
				 CASE
					WHEN  c.sex = 1 THEN '".$tr->translate("MALE")."'
					WHEN  c.sex = 2 THEN '".$tr->translate("FEMALE")."'
				END AS sexTitle,
				CASE
				WHEN  cc.proccess = 0 THEN '".$tr->translate("CANCEL")."'
				WHEN  cc.proccess = 1 THEN '".$tr->translate("PROGRESSING")."'
				WHEN  cc.proccess = 2 THEN '".$tr->translate("WAITING_COMPLETED")."'
				WHEN  cc.proccess = 3 THEN '".$tr->translate("COMPLETED")."'
				END AS crm_status_title,
				cc.proccess,
				c.kh_name,
				c.first_name,
				c.last_name,
				c.tel 
			 FROM 
			 	`rms_crm` AS c,
				`rms_crm_history_contact` AS cc
			WHERE 
				cc.crm_id = c.id
		";
		
		$where = ' ';
		$from_date =(empty($search['start_date']))? '1': " cc.contact_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " cc.contact_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where.= " AND  ".$from_date." AND ".$to_date;
		if(!empty($search['advance_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['advance_search'])));
			$s_where[] = " REPLACE(c.kh_name,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(c.first_name,' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(c.last_name,' ','') LIKE '%{$s_search}%'";
			$s_where[]=	 " REPLACE(CONCAT(c.last_name,c.first_name),' ','') LIKE '%{$s_search}%'";
			$s_where[]=	 " REPLACE(CONCAT(c.first_name,c.last_name),' ','')	LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(c.tel,' ','') LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_search'])){
			$where.= " AND c.branch_id = ".$db->quote($search['branch_search']);
		}
		if(!empty($search['ask_for_search'])){
			$where.= " AND c.ask_for = ".$db->quote($search['ask_for_search']);
		}
		if(!empty($search['crm_list'])){
			$where.= " AND cc.crm_id = ".$db->quote($search['crm_list']);
		}
		
		if($search['status_search']>-1 AND $search['status_search']!=''){
			$where.= " AND cc.proccess = ".$db->quote($search['status_search']);
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('c.branch_id');
		$where.=" ORDER BY cc.id DESC";
		$row = $db->fetchAll($sql.$where);
		
		$resutl = $row;
		if (!empty($search['feedback_type'])){
			$resutl = array();
			$epl = explode(",", $search['feedback_type']);
			$array = array();
			foreach ($epl as $ss){
				$key = $this->checkPrevConcern($ss,34);
				$array[$key] = $key;
			}
				
			if (!empty($row)) foreach ($row as $key => $rs){
				$exp = explode(",", $rs['feedback_type']);
				foreach ($exp as $ss){
					if (in_array($ss, $array)) {
						$resutl[$key] = $rs;
						break;
					}
				}
			}
		}
		return $resutl;
	}
	
	function getAllStudentTest($search=null){
		try{
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$_db = new Application_Model_DbTable_DbGlobal();
			$branch_id = $_db->getAccessPermission('st.branch_id');
			$lang = $_db->currentlang();
			if($lang==1){// khmer 
				$label = "name_kh";
				$grade = "idd.title";
				$degree = "i.title";
			}else{ // English
				$label = "name_en";
				$grade = "idd.title_en";
				$degree = "i.title_en";
			}
			
			$db=$this->getAdapter();
	
			$from_date =(empty($search['start_date']))? '1': "str.create_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': "str.create_date <= '".$search['end_date']." 23:59:59'";
	
			
			$sql=" SELECT st.*,
					(SELECT $label FROM rms_view WHERE TYPE=2 AND key_code=st.sex LIMIT 1) AS sex,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=str.academic_year LIMIT 1) AS academic,
					";
			
			
				$sql.="(SELECT tm.note FROM `rms_test_term` AS tm WHERE tm.id=str.study_term) AS study_term,";
			
				//$sql.="(SELECT CONCAT(title,' ( ',DATE_FORMAT(start_date, '%d/%m/%Y'),' - ',DATE_FORMAT(end_date, '%d/%m/%Y'),' )') FROM `rms_startdate_enddate` WHERE rms_startdate_enddate.id=str.study_term) AS study_term,";
			
			$sql.="		
					(SELECT $degree FROM `rms_items` AS i WHERE i.id = str.degree AND i.type=1 LIMIT 1) AS degree_title,
					(SELECT $grade FROM `rms_itemsdetail` AS idd WHERE idd.id = str.grade AND idd.items_type=1 LIMIT 1) AS grade_title,
					(SELECT $degree FROM `rms_items` AS i WHERE i.id = str.degree_result AND i.type=1 LIMIT 1) AS degree_result_title,
					(SELECT $grade FROM `rms_itemsdetail` AS idd WHERE idd.id = str.grade_result AND idd.items_type=1 LIMIT 1) AS grade_result_title,
					(SELECT first_name FROM rms_users WHERE rms_users.id = str.user_id LIMIT 1) AS user_id,
					(SELECT $label FROM rms_view WHERE TYPE=15 AND key_code = str.updated_result LIMIT 1) AS result_status,
					(SELECT first_name FROM rms_users WHERE rms_users.id = str.result_by LIMIT 1) AS result_by,
					str.create_date AS create_date_exam,
					str.result_date,
					str.test_date AS test_date_exam,
					str.updated_result AS updated_result_de,
					str.note AS note_result,
					str.is_registered
					,(SELECT ptl.title FROM rms_parttime_list AS ptl WHERE ptl.status=1 AND ptl.id = COALESCE((SELECT gs.`session` FROM `rms_group_detail_student` AS gs WHERE gs.`test_restult_id` = str.`id` LIMIT 1),'0') LIMIT 1 ) AS partTimeTitle
					,CASE 
						WHEN str.resultStatus = 2 THEN '".$tr->translate("Unqualified")."'
						ELSE '".$tr->translate("Qualified")."'
					END AS resultStatusTitle
				FROM 
					`rms_student` AS st,
					`rms_student_test_result` AS str
				WHERE 
					st.is_studenttest = 1
					AND str.stu_test_id = st.stu_id
					AND status=1
					AND st.stu_khname!=''
					$branch_id ";
	
			$where = " AND ".$from_date." AND ".$to_date;
	
			if(!empty($search['adv_search'])){
				$s_where=array();
				$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
				$s_where[]= " REPLACE(st.serial,' ','') LIKE '%{$s_search}%'";
				$s_where[]= " REPLACE(st.stu_code,' ','') LIKE '%{$s_search}%'";
				$s_where[]= " REPLACE(st.stu_khname,' ','') LIKE '%{$s_search}%'";
				$s_where[]= " REPLACE(st.stu_enname,' ','') LIKE '%{$s_search}%'";
				$s_where[]= " REPLACE(st.last_name,' ','') LIKE '%{$s_search}%'";
				$s_where[]=	" REPLACE(CONCAT(st.last_name,st.stu_enname),' ','') LIKE '%{$s_search}%'";
				$s_where[]=	" REPLACE(CONCAT(st.stu_enname,st.last_name),' ','') LIKE '%{$s_search}%'";
				$s_where[]= " REPLACE(st.tel,' ','') LIKE '%{$s_search}%'";
				$where.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(($search['branch_id']>0)){
				$where.= " AND st.branch_id = ".$search['branch_id'];
			}
			if(!empty($search['academic_year'])){
				$where .= " AND str.academic_year = ".$search['academic_year'];
			}
			if(!empty($search['term_test'])){
				$where .= " AND str.study_term = ".$search['term_test'];
			}
			if(!empty($search['user'])){
				$where.= " AND str.user_id = ".$search['user'];
			}
			if(!empty($search['type_exam'])){
				$where .= " AND str.test_type = ".$search['type_exam'];
			}
			if(!empty($search['degree'])){
				$where .= " AND str.degree_result = ".$search['degree'];
			}
			if(!empty($search['student_option_search'])){
				$where .= " AND st.student_option = ".$search['student_option_search'];
			}
			if(!empty($search['province_search'])){
				$where .= " AND st.province_id = ".$search['province_search'];
			}
			if($search['result_status']!=''){
				$where .= " AND str.updated_result = ".$search['result_status'];
			}
			if($search['register_status']!=''){
				$where .= " AND str.is_registered = ".$search['register_status'];
			}
			if(!empty($search['resultStatus'])){
				$where .= " AND str.resultStatus = ".$search['resultStatus'];
			}
			if(!empty($search['partTimeList'])){
				$where .= " AND COALESCE((SELECT gs.`session` FROM `rms_group_detail_student` AS gs WHERE gs.`test_restult_id` = str.`id` LIMIT 1),'0') = ".$search['partTimeList'];
			}
			
			$dbp = new Application_Model_DbTable_DbGlobal();
			$where.=$dbp->getAccessPermission("st.branch_id");
			$sql.= $dbp->getSchoolOptionAccess('str.test_type');
			$sql.= $dbp->getDegreePermission('str.degree_result');
			
			$order=" ORDER By str.create_date DESC,str.updated_result DESC,str.degree_result ASC,str.grade_result ASC ";
			return $db->fetchAll($sql.$where.$order);
		}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
}