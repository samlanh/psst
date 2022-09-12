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
}