<?php

class Global_Model_DbTable_DbTeacher extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_teacher';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
	public function AddNewStaff($_data){
		$_db= $this->getAdapter();		
		try{
		/// add photo ////////////////////////////////////////////////////
			$adapter = new Zend_File_Transfer_Adapter_Http();
			$part = PUBLIC_PATH.'/images';
			$adapter->setDestination($part);
			$adapter->receive();
			$photo = $adapter->getFileInfo();
				
			if(!empty($photo['photo']['name'])){
				$pho_name = $photo['photo']['name'];
			}else{
				$pho_name = '';
			}
		////////////////////////////////////////////////////////////////////////	
			$teacher_code = $this->getTeacherCode();
			$sql="SELECT id FROM rms_teacher WHERE sex =".$_data['sex'];
			$sql.=" AND teacher_name_kh='".$_data['kh_name']."'";
			$sql.=" AND dob='".$_data['dob']."'";
			$rs = $_db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}			
			$_arr=array(
					'teacher_code'		 => $_data['code'],
					'teacher_name_kh'	 => $_data['kh_name'],
					'teacher_name_en'	 => $_data['kh_name'],
					'sex'				 => $_data['sex'],
					'dob'				 => $_data['dob'],
					'nationality'  		 => $_data['nationality'],
			        'tel'  				 => $_data['phone'],
					'address' 			 => $_data['address'],
					'note' 				 => $_data['note'],
					
 					'position_add' 		 => $_data['position_add'],
 					'passport_no' 		 => $_data['passport_no'],
 					'email' 			 => $_data['email'],
  					'degree' 			 => $_data['degree'],
  					'experiences' 		 => $_data['experiences'],
					'card_no' 			 => $_data['card_no'],
 					'start_date' 		 => $_data['start_date'],
 					'end_date' 			 => $_data['end_date'],
  					'agreement' 		 => $_data['agreement'],
					
					'user_name' 		 => $_data['user_name'],
					'password' 			 => md5($_data['password']),
					//'status'   		 => $_data['status'],
					'photo'  			 => $pho_name,
					'branch_id' 		 => 1,
			        'create_date' 		 => date("Y-m-d"),
			        'user_id'	  		 => $this->getUserId(),
				);
			$this->insert($_arr);
			}catch(Exception $e){
	    		$_db->rollBack();
	    		echo $e->getMessage(); exit();
	    	}
		//print_r($_data); exit();
	}
	public function updateStaff($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			/*/////////// add photo ////////////////////////////////////////////////////
// 			$adapter = new Zend_File_Transfer_Adapter_Http();
// 			$part = PUBLIC_PATH.'/images';
// 			$adapter->setDestination($part);
// 			$adapter->receive();
// 			$photo = $adapter->getFileInfo();
// 			if(!empty($photo['photo']['name'])){
// 			$pho_name = $photo['photo']['name'];
// 			}else{
// 			$pho_name = $_data['old_photo'];
// 			}
			*////////////////////////////////////////////////////////////////////////
	
			$_arr=array(
					'teacher_code' 		=> $_data['code'],
					'teacher_name_kh' 	=> $_data['kh_name'],
					'teacher_name_en' 	=> $_data['kh_name'],
					'sex' 				=> $_data['sex'],
					'dob' 				=> $_data['dob'],
					'nationality'  		=> $_data['nationality'],
					'tel'   			=> $_data['phone'],
					'address' 			=> $_data['address'],
					//'branch_id' 		=> $_data['branch_id'],
					//'position' 		=> $_data['staff_position'],
					//'expired_date' 	=> $_data['expired_date'],
					'user_name' 		=> $_data['user_name'],
					'note' 				=> $_data['note'],
					'status'   			=> $_data['status'],
					'create_date' 		=> date("Y-m-d"),
					'user_id'	  		=> $this->getUserId(),
			);
		if(!empty($_data['password'])){
			$_arr['password']=md5($_data['password']);
		}
			$where=$this->getAdapter()->quoteInto("id=?", $_data["id"]);
			$this->update($_arr, $where);
		return $db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getTeacherById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_teacher WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
	function getAllDegree(){
		$db=$this->getAdapter();
		$sql="SELECT id,name_kh AS name FROM rms_view WHERE rms_view.type=3 AND name_kh!='' and status=1";
		return $db->fetchAll($sql);
	}
	public function getallSubjectTeacherById($teacher_id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `rms_teacher_subject` WHERE id= ".$db->quote($teacher_id);
		return $db->fetchAll($sql);;
	}
	
	function getAllTeacher($search){
		$db = $this->getAdapter();
		$sql = 'SELECT id, teacher_code, teacher_name_kh,
				(select name_kh from rms_view where rms_view.type=2 and rms_view.key_code=rms_teacher.sex) AS sex, 
				nationality,
				(SELECT name_kh FROM rms_view WHERE rms_view.type=3 AND rms_view.key_code=degree) AS degree,
				tel,
				email,
				note,
				(select name_en from rms_view where type=1 and key_code =status) as status
				FROM rms_teacher WHERE 1';
		$order_by=" order by id DESC";
		$where = '';
		if(!empty($search['title'])){
		    $s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " teacher_code LIKE '%{$s_search}%'";
			$s_where[] = " teacher_name_en LIKE '%{$s_search}%'";
			$s_where[] = " teacher_name_kh LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['degree'])){
			$where.=' AND degree='.$search['degree'];
		}
		if($search['status']>-1){
			$where.=' AND status='.$search['status'];
		}
		return $db->fetchAll($sql.$where.$order_by);
	}
	public function addNewPosition($data){//ajax
		$this->_name = "rms_staff_position" ;
		$_arr=array(
				'title' 	=> $data['title'],
				'create_date' 		=> date('Y-m-d'),
				'user_id'	=> $this->getUserId(),
			);
		return $this->insert($_arr);
	}
	function getAllBranch(){
		$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission('br_id');
		$sql="select br_id as id,branch_nameen as name from rms_branch where status = 1  $branch_id  ";
		return $db->fetchAll($sql);
	}
	
	function getAllPosition(){
		$db=$this->getAdapter();
		$sql="select id ,title as name from rms_staff_position where status = 1 ";
		return $db->fetchAll($sql);
	}
	
	function getTeacherCode(){
		$db=$this->getAdapter();
		$sql="select count(id) from rms_teacher ";
		$result = $db->fetchOne($sql);
		$code='';
		$new_acc = $result + 1 ;
		$length = strlen((int)$new_acc);
		for($i=$length;$i<5;$i++){
			$code .= "0";
		}
		return $code.$new_acc;
	}
	/*for user teacher account login*/
	public function userAuthenticate($username,$password)
	{
		$this->_name='rms_teacher';		
		$db_adapter = Application_Model_DbTable_DbUsers::getDefaultAdapter();
		$auth_adapter = new Zend_Auth_Adapter_DbTable($db_adapter);
	
		$auth_adapter->setTableName($this->_name) // table where users are stored
		->setIdentityColumn('user_name') // field name of user in the table
		->setCredentialColumn('password') // field name of password in the table
		->setCredentialTreatment('MD5(?) AND status=1'); // optional if password has been hashed
			
		$auth_adapter->setIdentity($username); // set value of username field
		$auth_adapter->setCredential($password);// set value of password field
		$auth = Zend_Auth::getInstance();
		$result = $auth->authenticate($auth_adapter);
		if($result->isValid()){
			return true;
		}else{
			return false;
		}
	}
	public function getTeacherid($user_name)
	{
		$select=$this->select();
		$select->from($this,'id')
		->where('user_name=?',$user_name);
		$row=$this->fetchRow($select);
		if(!$row) return NULL;
		return $row['id'];
	}
	public function getTeacherInfo($user_id)
	{
		$select=$this->select();
		$select->from($this,array('teacher_name_en', 'id','branch_id'))
		->where('id=?',$user_id);
		$row=$this->fetchRow($select);
		if(!$row) return NULL;
		return $row;
	}
	
}