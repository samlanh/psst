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
	
	public static function getUserExternalId(){
		$sessionUserExternal=new Zend_Session_Namespace("externalAuth");
		$userId = $sessionUserExternal->userId;
		return $userId;
	}
	
	function coutingClassByUser($arrCondiction=array()){
		$db = $this->getAdapter();
		$sql="
			SELECT COUNT(g.id) 
			";
		$sql.=" FROM `rms_group` AS g  ";
		$sql.=" WHERE g.status=1 AND g.is_use=1 AND g.teacher_id=".$this->getUserExternalId();
		
		if(!empty($arrCondiction['classTypeFilter'])){
			if($arrCondiction['classTypeFilter']==1){
				//Completed Class
				$sql.=" AND g.is_pass !=2 ";
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
		
		$sql = "SELECT `g`.*
			,(SELECT $branch FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) AS branchName
			,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear	
			,(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) AS degree
			,(SELECT id.$colunmname FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) AS grade
			,(SELECT`rms_view`.$label FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`
			,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `roomName`
			,(select teacher_name_kh from rms_teacher where rms_teacher.id = g.teacher_id limit 1 ) as teaccher
			,(select $label from rms_view where type=9 and key_code=g.is_pass) as groupStatus
			,(SELECT COUNT(gds.gd_id)  FROM `rms_group_detail_student` AS gds WHERE gds.group_id = g.id AND gds.is_maingrade=1 ) AS amountStudent ";
		
		$sql.=$dbp->caseStatusShowImage("g.status");
		$sql.=" FROM `rms_group` AS `g` ";
		
		$where =' WHERE 1 ';
		$where.=' AND g.teacher_id='.$this->getUserExternalId();
		
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
}