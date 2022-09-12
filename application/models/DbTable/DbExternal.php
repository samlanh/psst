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
		//$from_date =(empty($search['start_date']))? '1': "g.date >= '".$search['start_date']." 00:00:00'";
		//$to_date = (empty($search['end_date']))? '1': "g.date <= '".$search['end_date']." 23:59:59'";
		//$where.= " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['adv_search'])){
			//$s_where = array();
			//$s_search = addslashes(trim($search['adv_search']));
			//$s_where[] = " `g`.`group_code` LIKE '%{$s_search}%'";
			//$s_where[]="(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) LIKE '%{$s_search}%'";
			//$s_where[] = " `g`.`semester` LIKE '%{$s_search}%'";
			//$s_where[] = " (SELECT id.title FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) LIKE '%{$s_search}%'";
			//$s_where[]=" (SELECT i.title FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) LIKE '%{$s_search}%'";
			//$s_where[] = " (SELECT id.title_en FROM `rms_itemsdetail` AS id WHERE id.id = `g`.`grade` LIMIT 1) LIKE '%{$s_search}%'";
			//$s_where[]=" (SELECT i.title_en FROM `rms_items` AS i WHERE i.type=1 AND i.id = `g`.`degree` LIMIT 1) LIKE '%{$s_search}%'";
			//$s_where[] = " (SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`)) LIKE '%{$s_search}%'";
			//$s_where[] = " (SELECT`rms_view`.`name_en`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
			//AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) LIKE '%{$s_search}%'";
			//$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['academic_year'])){
			//$where.=' AND g.academic_year='.$search['academic_year'];
		}
		$order =  ' ORDER BY `g`.`id` DESC ' ;
		return $db->fetchAll($sql.$where.$order);
	}
}