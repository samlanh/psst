<?php

class Allreport_Model_DbTable_DbTeacher extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_teacher';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
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
						$image_name = "profile_lecturer".date("Y").date("m").date("d").time().".".end($ss);
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
							'staff_type'  	 	 => $_data['staff_type'],
					        'tel'  				 => $_data['phone'],
							'note' 				 => $_data['note'],
							
							'department' 		 => $_data['department'],
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
						'staff_type'  	 	 => $_data['staff_type'],
				        'tel'  				 => $_data['phone'],
						'note' 				 => $_data['note'],
						
						'department' 		 => $_data['department'],
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
						$image_name = "profile_lecturer".date("Y").date("m").date("d").time().".".end($ss);
						$tmp = $_FILES['photo']['tmp_name'];
						if(move_uploaded_file($tmp, $part.$image_name)){
							$_arr['photo']=$image_name;
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
	}
	public function getTeacherById($id){
		$db = $this->getAdapter();
		
		$dbg = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbg->currentlang();
		$colunmName='depart_nameen';
		if ($currentLang==1){
			$colunmName='depart_namekh';
		}
		
		$sql = "
		SELECT t.*,
			(SELECT dept.depart_nameen FROM rms_department AS dept WHERE dept.depart_id=t.department LIMIT 1) AS dept_name,
			(SELECT dept.$colunmName FROM rms_department AS dept WHERE dept.depart_id=t.department LIMIT 1) AS deptName
		FROM rms_teacher AS t WHERE t.id =$id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.= $dbp->getAccessPermission('t.branch_id');
		$sql.=" LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
	function getAllDegree(){
		$db=$this->getAdapter();
		$sql="SELECT id,name_kh AS name FROM rms_view WHERE rms_view.type=3 AND name_kh!='' and status=1";
		return $db->fetchAll($sql);
	}
	
	public function getTeacherDocumentById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_teacher_document as s WHERE s.stu_id =".$id;
		return $db->fetchAll($sql);
	}
	
	function getAllDepartment(){
		$db = $this->getAdapter();
		$sql = " SELECT depart_id AS id,depart_namekh AS name FROM `rms_department` WHERE STATUS=1 AND depart_namekh!='' ";
		return $db->fetchAll($sql);
	}
	
	function getAllTeacher($search){
		$db = $this->getAdapter();
		
		$dbg = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbg->currentlang();
		
		$colunmName='depart_nameen';
		$label="name_en";
		if ($currentLang==1){
			$colunmName='depart_namekh';
			$label='name_kh';
		}
		
		$sql = '
			SELECT g.id, 
				(SELECT CONCAT(b.branch_nameen) FROM rms_branch AS b WHERE b.br_id=g.branch_id LIMIT 1) AS branch_name,
				g.teacher_code, 
				g.teacher_name_kh,
				(SELECT v.'.$label.' FROM rms_view AS v WHERE v.type=2 	AND v.key_code=g.sex LIMIT 1) AS sex,
				(SELECT v.'.$label.' FROM rms_view AS v WHERE v.type=26 AND v.key_code=g.staff_type LIMIT 1) AS staff_type,
				(SELECT v.'.$label.' FROM rms_view AS v WHERE v.type=21 AND v.key_code=g.nationality LIMIT 1) AS nationality, 
				(SELECT v.'.$label.' FROM rms_view AS v WHERE v.type=3 	AND v.key_code=g.degree LIMIT 1) AS degree,
				(SELECT v.'.$label.' FROM rms_view AS v WHERE v.type=24 AND v.key_code=g.teacher_type LIMIT 1) AS teacher_type,
				(SELECT rms_items.schoolOption FROM rms_items WHERE rms_items.id=g.degree AND rms_items.type=1 LIMIT 1) AS schoolOption,
				(SELECT dept.depart_nameen FROM rms_department AS dept WHERE dept.depart_id=g.department LIMIT 1) AS dept_name,
				(SELECT dept.'.$colunmName.' FROM rms_department AS dept WHERE dept.depart_id=g.department LIMIT 1) AS deptName,
				g.position_add,
				g.tel,
				g.photo,
				g.email,
				g.note,
				(SELECT v.'.$label.' FROM rms_view AS v WHERE v.key_code=g.status AND v.type=1 LIMIT 1) AS `status`
			FROM rms_teacher AS g WHERE 1';
		
		$where='';
		if(!empty($search['title'])){
		    $s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " (SELECT CONCAT(branch_nameen) FROM rms_branch WHERE br_id=branch_id LIMIT 1) LIKE '%{$s_search}%'";
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
		if(!empty($search['staff_type'])){
			$where.=' AND staff_type='.$search['staff_type'];
		}
		if($search['status']>-1){
			$where.=' AND status='.$search['status'];
		}
		if($search['teacher_type']>-1){
			$where.=' AND teacher_type='.$search['teacher_type'];
		}
		$order_by=" ORDER BY id DESC";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.= $dbp->getAccessPermission('g.branch_id');		
		
		return $db->fetchAll($sql.$where.$order_by);
	}
	
	function getTeachDocumentAlert($search){
		$db = $this->getAdapter();
		
		$dbg = new Application_Model_DbTable_DbGlobal();
    	$currentlang = $dbg->currentlang();
		
		$label="name_en";
		if ($currentLang==1){
			$label='name_kh';
		}
		
		$sql =" 
		SELECT s.branch_id,
			(SELECT CONCAT(b.branch_nameen) FROM rms_branch AS b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_name,
			(SELECT v.$label FROM rms_view v WHERE v.type=2 AND v.key_code=s.sex LIMIT 1) AS sex,
			(SELECT v.$label FROM rms_view v WHERE v.type=24 AND v.key_code=s.teacher_type LIMIT 1) AS teacher_type, 
			(SELECT v.$label FROM rms_view v WHERE v.type=21 AND v.key_code=s.nationality LIMIT 1) AS nationality, 
			(SELECT v.$label FROM rms_view v WHERE v.type=3 AND v.key_code=s.degree LIMIT 1) AS degree,
			s.teacher_code,s.teacher_name_kh,s.tel,
			s.email,
			sd.*
		FROM `rms_teacher_document` AS sd, `rms_teacher` AS s
		WHERE s.id = sd.stu_id
			AND sd.is_receive=0
		";
		$where ='';
		$to_date = (empty($search['end_date']))? '1': " sd.date_end <= '".$search['end_date']." 23:59:59'";
		$where.= " AND ".$to_date;
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission("s.branch_id");
		
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
		$order=" ORDER BY sd.date_end DESC, sd.stu_id ASC";
		return $db->fetchAll($sql.$where.$order);
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