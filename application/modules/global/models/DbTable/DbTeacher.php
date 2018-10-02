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
		$_db->beginTransaction();
			try{
					$part= PUBLIC_PATH.'/images/photo/';
					if (!file_exists($part)) {
						mkdir($part, 0777, true);
					}
					$photo = "";
					$name = $_FILES['photo']['name'];
					if (!empty($name)){
						$ss = 	explode(".", $name);
						$image_name = "profile_".date("Y").date("m").date("d").time().".".end($ss);
						$tmp = $_FILES['photo']['tmp_name'];
						if(move_uploaded_file($tmp, $part.$image_name)){
							$photo = $image_name;
						}
						else
							$string = "Image Upload failed";
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
							'branch_id' 		 => $_data['branch_id'],
							'teacher_code'		 => $_data['code'],
							'teacher_name_kh'	 => $_data['kh_name'],
							'teacher_name_en'	 => $_data['kh_name'],
							'sex'				 => $_data['sex'],
							'dob'				 => $_data['dob'],
							'nationality'  		 => $_data['nationality'],
							'nation'  		     => $_data['nation'],
							'teacher_type'  	 => $_data['teacher_type'],
					        'tel'  				 => $_data['phone'],
							'note' 				 => $_data['note'],
							
							'village_name' 		 => $_data['village_name'],
							'commune_name'  	 => $_data['commune_name'],
							'district_name'  	 => $_data['district_name'],
							'province_id'  	 	 => $_data['province_id'],
							'home_num'  		 => $_data['home_num'],
							'street_num'  		 => $_data['street_num'],
							
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
							'photo'  			 => $photo,
					        'create_date' 		 => date("Y-m-d"),
					        'user_id'	  		 => $this->getUserId(),
						);
						$id = $this->insert($_arr);
						
						$this->_name = 'rms_teacher_document';
						if(!empty($_data['identity'])){
						$ids = explode(',', $_data['identity']);
						foreach ($ids as $i){
								$_arr = array(
										'stu_id'		=>$id,
										'document_type'	=>$_data['document_type_'.$i],
										'date_give'		=>$_data['date_give_'.$i],
										'date_end'		=>$_data['date_end_'.$i],
										'is_receive'	=>$_data['is_receive_'.$i],
										'note'			=>$_data['note_'.$i],
										'type'			=>2,
								);
							$this->insert($_arr);
						}}
						$_db->commit();
			}catch(Exception $e){
	    		$_db->rollBack();
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
	    	//print_r($_data); exit();
	}
	public function updateStaff($_data){
		$_db= $this->getAdapter();		
		$_db->beginTransaction();
		try{	
				$part= PUBLIC_PATH.'/images/photo/';
				if (!file_exists($part)) {
					mkdir($part, 0777, true);
				}
		////////////////////////////////////////////////////////////////////	
				$teacher_code = $this->getTeacherCode();
				$_arr=array(
						'branch_id' 		 => $_data['branch_id'],
						'teacher_code'		 => $_data['code'],
						'teacher_name_kh'	 => $_data['kh_name'],
						'teacher_name_en'	 => $_data['kh_name'],
						'sex'				 => $_data['sex'],
						'dob'				 => $_data['dob'],
						'nationality'  		 => $_data['nationality'],
						'nation'  		     => $_data['nation'],
						'teacher_type'  	 => $_data['teacher_type'],
				        'tel'  				 => $_data['phone'],
						'note' 				 => $_data['note'],
						
						'village_name' 		 => $_data['village_name'],
						'commune_name'  	 => $_data['commune_name'],
						'district_name'  	 => $_data['district_name'],
						'province_id'  	 	 => $_data['province_id'],
						'home_num'  		 => $_data['home_num'],
						'street_num'  		 => $_data['street_num'],
						
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
						'status'   			 => $_data['status'],
						//'photo'  			 => $photo,
				        'create_date' 		 => date("Y-m-d"),
				        'user_id'	  		 => $this->getUserId(),
					);
					$photo = "";
					$name = $_FILES['photo']['name'];
					if (!empty($name)){
						$ss = 	explode(".", $name);
						$image_name = "profile_".date("Y").date("m").date("d").time().".".end($ss);
						$tmp = $_FILES['photo']['tmp_name'];
						if(move_uploaded_file($tmp, $part.$image_name)){
							$array['photo']=$image_name;
						}
					}
					$where=" id = ".$_data['id'];
					$id = $this->update($_arr,$where);
					
					$this->_name = 'rms_teacher_document';
					$where="stu_id = ".$_data["id"];
					$this->delete($where);
					if(!empty($_data['identity'])){
					$ids = explode(',', $_data['identity']);
					foreach ($ids as $i){
							$_arr = array(
									'stu_id'		=>$_data["id"],
									'document_type'	=>$_data['document_type_'.$i],
									'date_give'		=>$_data['date_give_'.$i],
									'date_end'		=>$_data['date_end_'.$i],
									'is_receive'	=>$_data['is_receive_'.$i],
									'note'			=>$_data['note_'.$i],
									'type'			=>2,
							);
						$this->insert($_arr);
					}}
					$_db->commit();
		}catch(Exception $e){
    		$_db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	//print_r($_data); exit();
	}
	public function getTeacherById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_teacher WHERE id =$id ";
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
	public function getTeacherDocumentById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_teacher_document as s WHERE s.stu_id =".$id;
		return $db->fetchAll($sql);
	}
	function getAllTeacher($search){
		$db = $this->getAdapter();
		$sql = 'SELECT g.id, 
				(SELECT CONCAT(branch_nameen) FROM rms_branch WHERE br_id=branch_id LIMIT 1) AS branch_name,
				g.teacher_code, 
				g.teacher_name_kh,
				(SELECT name_kh FROM rms_view WHERE rms_view.type=2 AND rms_view.key_code=g.sex) AS sex,
				(SELECT name_kh FROM rms_view WHERE rms_view.type=24 AND rms_view.key_code=g.teacher_type) AS teacher_type, 
				(SELECT name_kh FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=g.nationality) AS nationality, 
				(SELECT name_kh FROM rms_view WHERE rms_view.type=3 AND rms_view.key_code=g.degree) AS degree,

				g.position_add,
				g.tel,
				g.email,
				g.note,
				(SELECT name_kh FROM rms_view WHERE key_code=g.status AND TYPE=1 LIMIT 1) AS `status`
				FROM rms_teacher AS g WHERE 1';
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
		if(!empty($search['nationality'])){
			$where.=' AND nationality='.$search['nationality'];
		}
		if(!empty($search['branch_id'])){
			$where.=' AND branch_id='.$search['branch_id'];
		}
		if($search['status']>-1){
			$where.=' AND status='.$search['status'];
		}
		return $db->fetchAll($sql.$where.$order_by);
	}
	
	public function getViewById($id){
		$db = $this->getAdapter();
		$sql = "SELECT id, 
				(SELECT p.province_kh_name FROM rms_province AS p WHERE p.code=g.province_id LIMIT 1) AS province_name,	
				(SELECT d.district_namekh FROM ln_district AS d WHERE d.dis_id=g.district_name LIMIT 1) AS dis_name,	
				(SELECT c.commune_namekh FROM ln_commune AS c WHERE c.com_id=g.commune_name LIMIT 1) AS com_name,	
				(SELECT v.village_namekh FROM ln_village AS v WHERE v.vill_id=g.village_name LIMIT 1) AS Village_name,			
				g.teacher_code, 
				g.teacher_name_kh,
				g.home_num,
				g.street_num,
				g.passport_no,
				g.dob,
				g.card_no,
				g.photo,
				g.start_date,
				g.end_date,
				g.agreement,
				g.experiences,
				(SELECT name_kh FROM rms_view WHERE rms_view.type=2 AND rms_view.key_code=g.sex) AS sex,
				(SELECT name_kh FROM rms_view WHERE rms_view.type=24 AND rms_view.key_code=g.teacher_type) AS teacher_type, 
				(SELECT name_kh FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=g.nationality) AS nationality, 
				(SELECT name_kh FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=g.nation) AS nation, 
				(SELECT name_kh FROM rms_view WHERE rms_view.type=3 AND rms_view.key_code=g.degree) AS degree,
				g.position_add,
				g.tel,
				g.email,
				g.note				
				FROM rms_teacher AS g 
				
				WHERE  id=$id";
		$sql.=" LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
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