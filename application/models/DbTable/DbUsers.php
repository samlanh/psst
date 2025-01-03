<?php

class Application_Model_DbTable_DbUsers extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_users';
    
	//function get user info from database
	public function getUserInfo($user_id)
	{		
		if (!empty($user_id)){	
			$select=$this->select();
			$select->from($this,array('user_type', 'last_name' ,'first_name','branch_id','branch_list','schoolOption',
			'(SELECT isSuperUser FROM `rms_acl_user_type` WHERE user_type_id=rms_users.user_type) as isSuperUser'))
			->where('id=?',$user_id);

			$row=$this->fetchRow($select);		
			if(!$row) return NULL;
			return $row;
		}else {
			return null;
		}
	}
	function getUserInformationById($user_id){
		$db = $this->getAdapter();
		$sql="
		SELECT 
			s.*
			,s.user_type AS level
			,(SELECT ut.user_type FROM `rms_acl_user_type` AS ut WHERE ut.user_type_id = s.user_type LIMIT 1) AS user_typetitle 
			,(SELECT ut.user_type FROM `rms_acl_user_type` AS ut WHERE ut.user_type_id = s.user_type LIMIT 1) AS userTypeTitle 
			,(SELECT ut.dashboardType FROM `rms_acl_user_type` AS ut WHERE ut.user_type_id = s.user_type LIMIT 1) AS dashboardType 
		FROM `rms_users` AS s
		WHERE s.id = $user_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	//function get user id from database
	public function getUserID($user_name)
	{		
		$select=$this->select();
			$select->from($this,'id')
			->where('user_name=?',$user_name);
		$row=$this->fetchRow($select);		
		if(!$row) return NULL;
		return $row['id'];
	}
	
	//get max user
	public function getMaxUser()
	{	
		$db = $this->getAdapter();
		$sql = "SELECT count(*) AS max_user FROM `rms_users`";	
		$row = $db->fetchRow($sql);	
		return ($row['max_user'] + 1);
	}
    
    /** 
     * To validate the user name 
     * and password is valids or not
     * @param <string> $username
     * @param <string> $password
     */
    public function userAuthenticate($username,$password)
	{
        
		$db_adapter = Application_Model_DbTable_DbUsers::getDefaultAdapter(); 
        $auth_adapter = new Zend_Auth_Adapter_DbTable($db_adapter);
              
        $auth_adapter->setTableName($this->_name) // table where users are stored
                     ->setIdentityColumn('user_name') // field name of user in the table
                     ->setCredentialColumn('password') // field name of password in the table
                     ->setCredentialTreatment('MD5(?) AND active=1'); // optional if password has been hashed
 		
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
	
	private function _buildQuery($search = ''){
		$sql = "SELECT 
						CONCAT( u.`last_name` , ' ', u.`first_name` ) AS name, 
						u.`user_name` , 
						u.`user_type`, 
						u.`id`,
						u.`active`
				FROM `rms_users` AS u
						
				WHERE 1 ";
		$orderby = " ORDER BY u.user_type DESC";
		if(empty($search)){
			return $sql;//.$orderby;
		}
		$where = '';
		
		if ($search['active'] >= 0){
			$where = 'AND u.`active` = '.$search['active'];			
		}
		
		if (!empty($search['txtsearch'])){
			$fields = array('u.last_name', 'u.first_name', 'u.user_name');	
			$s_where = array();	
	        foreach($fields as $field)
	    	{
	    		$s_where[] = $field." LIKE '%{$search['txtsearch']}%'";
	    	}	    	
	        $where .= ' AND ('.implode(' OR ',$s_where).')';
		}		
		
		if ($search['user_type'] >= 0 ){
			$where .= ' AND u.`user_type` = '. $search['user_type'];
		}
				
		return $sql.$where.$orderby;
	}
	
	function getUserList($search=null){
		$db = $this->getAdapter();
		$sql = "SELECT
		u.`id`,
		u.`last_name` ,
		u.`first_name` AS name,
		u.`user_name` ,
		u.`branch_list` ,
		(SELECT user_type FROM `rms_acl_user_type` WHERE user_type_id=u.user_type LIMIT 1) aS users_type,
		u.user_type as typeid,
		u.`active` as status,
		u.`branch_list` 
		FROM `rms_users` AS u
		WHERE u.is_system =0 ";
		$orderby = " ORDER BY u.user_type DESC";
		
		$where = '';
		$_db = new Application_Model_DbTable_DbGlobal();
// 		$where.= $_db->getAccessPermission('branch_id');
		
		if(empty($search)){
			return $sql.$where.$orderby;
		}
		
		if (!empty($search['adv_search'])){
			$fields = array('u.last_name', 'u.first_name', 'u.user_name');
			$s_where = array();
			foreach($fields as $field)
			{
				$s_where[] = $field." LIKE '%{$search['adv_search']}%'";
			}
			$where .= ' AND ('.implode(' OR ',$s_where).')';
		}
		if (!empty($search['branch_id'])){
			$where = 'AND u.`branch_id` = '.$search['branch_id'];
		}
		if ($search['status'] > -1 AND $search['status']!=''){
			$where = 'AND u.`active` = '.$search['status'];
		}
		
		if ($search['user_type'] > -1 AND $search['user_type']!=''){
			$where .= ' AND u.`user_type` = '. $search['user_type'];
		}
		
		$row = $db->fetchAll($sql.$where.$orderby);
		
		//loop display only branch are available in current user
		$userInfo = $_db->getUserInfo();
		if($userInfo['level']!=1){
			$branchList = empty($userInfo['branch_list'])?"":$userInfo['branch_list'];
			$bra = explode(",", $branchList);
			$resutl = array();
			if (!empty($bra)){
				$array = array();
				foreach ($bra as $ss) {
					$array[$ss] = $ss;
				}
				if (!empty($row)) foreach ($row as $key => $rs){
					$exp = explode(",", $rs['branch_list']);
					foreach ($exp as $ss){
						if (in_array($ss, $array)) {
							$resutl[$key] = $rs;
							break;
						}
					}
				}
			}
			return $resutl;
		}else{
			return $row;
		}
		
		
	}
	
	function getUserListBy($search, $start, $limit){        
		$db = $this->getAdapter();		
		$sql = $this->_buildQuery($search)." LIMIT ".$start.", ".$limit;
		if ($limit == 'All') {
			$sql = $this->_buildQuery($search);
		}		
		return $db->fetchAll($sql);
	}
	
	function getUserListTotal($search=''){        
		$db = $this->getAdapter();
		$sql = $this->_buildQuery();
		if(!empty($search)){
			$sql = $this->_buildQuery($search);
		}
		$_result = $db->fetchAll($sql); 
		return $_result;
	}
	
	function getUserEdit($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
					u.`first_name` 
					,u.`last_name`
					,u.`user_name`
					,u.`user_type` 
					,u.`active`
					,u.branch_id
					,u.branch_list
					,u.`id`
					,u.schoolOption 
					,u.degreeList 
					
				FROM `rms_users` AS u
				WHERE u.id = ".$id;	
		
		return $db->fetchRow($sql);
	}
	
	function getUserView($id){
		$db = $this->getAdapter();
		$sql = "SELECT `first_name`, `last_name`, `user_name`, `user_type`, u.`active`
				FROM `rms_users` AS u					  
				WHERE u.id = ".$id;	
		
		return $db->fetchRow($sql);
	}
	
	function getUserListSelect($orderby = ""){
		$db = $this->getAdapter();
		$sql = "SELECT CONCAT(`last_name`,' ',`first_name`) AS name, `id`
				FROM `rms_users`";	
		if(!empty($orderby)){
			$sql .= " ". $orderby;
		}
		
		return $db->fetchAll($sql);
	}
	
	//function get user info from database
	public function getUserInfoByfetchAll($user_id)
	{
		$select=$this->select();
		$select->from($this,array('id', 'CONCAT(`last_name`," ",`first_name`) AS name'))
		->where('id=?',$user_id);
		$row=$this->fetchAll($select);		
		return $row;
	}
	
	function insertUser($data){
		$db = $this->getAdapter();
		try{
				$branchList="";
				if (!empty($data['selector'])){
// 					foreach ($data['selector'] as $rs){
// 						if (empty($branchList)){
// 							$branchList = $rs;
// 						}else { $branchList = $branchList.",".$rs;
// 						}
// 					}
					$branchList = implode(',', $data['selector']);
				}
				$sql="SELECT id FROM rms_users WHERE user_name ='".$data['user_name']."'";
				$rs = $db->fetchOne($sql);
				if(!empty($rs)){
					return -1;
				}
			$schooloption= implode(',', $data['schooloptoncheck']);
			$degreeSelector= implode(',', $data['degreeSelector']);
			$_user_data = array(
		    	'last_name'=>$data['last_name'],
				'first_name'=>$data['first_name'],
				'user_name'=>$data['user_name'],
				'password'=> MD5($data['password']),
				'user_type'=> $data['user_type'],
				'branch_list'=>$branchList,
				'schoolOption'=>$schooloption,
				'degreeList'=>$degreeSelector,
				'active'=> 1			
		    ); 
			return $this->insert($_user_data);
		}catch (Exception $e){
			Application_Form_FrmMessage::message($this->tr->translate("INSERT_SUCCSS"));
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function updateUser($data){	
		$db = $this->getAdapter();
		$branchList="";
		if (!empty($data['selector'])){
// 			foreach ($data['selector'] as $rs){
// 				if (empty($branchList)){
// 					$branchList = $rs;
// 				}else { $branchList = $branchList.",".$rs;
// 				}
// 			}
			$branchList = implode(',', $data['selector']);
		}
		
		$schooloption= implode(',', $data['schooloptoncheck']);
		$degreeSelector= implode(',', $data['degreeSelector']);
		$status = empty($data['active'])?0:1;
		$_user_data=array(
	    	'last_name'=>$data['last_name'],
			'first_name'=>$data['first_name'],
			'user_name'=>$data['user_name'],
// 			'password'=> MD5($data['password']),
			'user_type'=> $data['user_type'],
// 			'branch_id'=>$data['branch_id'],
			'branch_list'=>$branchList,
			'schoolOption'=>$schooloption,
			'degreeList'=>$degreeSelector,
			'active'=> $status		
	    );    	   
		if (!empty($data['check_change'])){
			$_user_data['password']= md5($data['password']);
		}
		$where=$this->getAdapter()->quoteInto('id=?', $data['id']); 
    	   
		return  $this->update($_user_data,$where);
	}
	
	function changePassword($newpwd, $id){
		$_user_data=array(
			'password'=> MD5($newpwd)		
	    );    	   
		
		$where=$this->getAdapter()->quoteInto('id=?', $id); 
    	   
		return  $this->update($_user_data,$where);
	}

	/**
	 * To get all acl of a user type
	 * @param string $user_type_id
	 */
// 	public function getArrAcl($user_type_id){
// 		$db = $this->getAdapter();
// 		$sql = "SELECT aa.module, aa.controller, aa.action FROM rms_acl_user_access AS ua  INNER JOIN rms_acl_acl AS aa ON (ua.acl_id=aa.acl_id) WHERE ua.user_type_id='".$user_type_id."'";
// 		$rows = $db->fetchAll($sql);
// 		return $rows;
// 	}
	public function getArrAcl($user_type_id){
		$db = $this->getAdapter();
		$sql = "SELECT aa.module, aa.controller, aa.action,aa.label FROM rms_acl_user_access AS ua  INNER JOIN rms_acl_acl AS aa
		ON (ua.acl_id=aa.acl_id) WHERE ua.user_type_id='".$user_type_id."'
		GROUP BY  aa.module ,aa.controller,aa.action
		ORDER BY aa.module ,aa.rank ASC,aa.is_menu ASC  ";
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	public function getArrAclReport($user_type_id,$str_controller='allreport'){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$user_type_id = $session_user->level;
		$sql = "SELECT aa.label,aa.module, aa.controller, aa.action 
			FROM rms_acl_user_access AS ua  
			INNER JOIN rms_acl_acl AS aa
		ON (ua.acl_id=aa.acl_id) 
			WHERE aa.status=1 AND ua.user_type_id='".$user_type_id."'
				AND aa.module='allreport' 
				AND aa.controller ='".$str_controller."'
			GROUP BY  aa.module ,aa.controller,aa.action
		ORDER BY aa.module ,aa.controller ASC , aa.rank ASC ";	
		return $db->fetchAll($sql);
	}
	function getAccessUrl($module,$controller,$action){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$user_typeid = $session_user->level;
		$db = $this->getAdapter();
		$sql = "SELECT aa.module, aa.controller, aa.action 
					FROM rms_acl_user_access AS ua  
					INNER JOIN rms_acl_acl AS aa
				ON (ua.acl_id=aa.acl_id) 
					WHERE ua.user_type_id='".$user_typeid."' 
						AND aa.module='".$module."' 
						AND aa.controller='".$controller."' 
						AND aa.action='".$action."' limit 1";
		
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	
	//For Student Placement Test
	public function userAuthenticateStudentTest($username,$password)
	{
		$db_adapter = Application_Model_DbTable_DbUsers::getDefaultAdapter();
		$auth_adapter = new Zend_Auth_Adapter_DbTable($db_adapter);
		$auth_adapter->setTableName('rms_student') // table where users are stored
		->setIdentityColumn('serial') // field name of user in the table
		->setCredentialColumn('password') // field name of password in the table
		->setCredentialTreatment('MD5(?) AND status=1 AND customer_type=4'); // optional if password has been hashed
			
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
	
	//function get user info from database
	public function getStudentInfo($username,$password)
	{		
		$db = $this->getAdapter();
		if (!empty($username)){	
			$sql=" SELECT s.* FROM rms_student AS s WHERE 1 ";
			$sql.= " AND ".$db->quoteInto('s.serial=?', $username);
			$sql.= " AND ".$db->quoteInto('s.password=?', md5($password));
			$row=$db->fetchRow($sql);
			if(!$row) return NULL;
			return $row;
			
		}else {
			return null;
		}
	}
}