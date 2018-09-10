<?php
class Registrar_Model_DbTable_DbStudentTest extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_test';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	}
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->branch_id;
	}
	function addStudentTest($data){
		try{
			
// 			$updated_result = 0;
// 			if(!empty($data['degree_result']) && !empty($data['grade_result']) ){
// 				$updated_result = 1;
// 			}
			
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
			
			$array = array(
						'branch_id'	=>$data['branch_id'],
						'stu_code'	=>$data['stu_code'],
						'kh_name'	=>$data['kh_name'],
						'first_name'	=>$data['first_name'],
						'en_name'	=>$data['en_name'],
						'sex'		=>$data['sex'],
						'nationality'=>$data['nationality'],
						'nation'=>$data['nation'],
						'dob'		=>$data['dob'],
						'pob'		=>$data['pob'],
						'phone'		=>$data['phone'],
						'email'		=>$data['email'],
						'address'	=>$data['address'],
						'student_status'	=>$data['student_status'],
						'if_employed_where'	=>$data['if_employed_where'],
						'position'			=>$data['position'],
						'parent_name'		=>$data['parent_name'],
						'parent_tel'		=>$data['parent_tel'],
						'photo'				=>$photo,

						'old_school'=>$data['old_school'],
						'old_grade'	=>$data['old_grade'],
					
						'emergency_name'		=>$data['emergency_name'],
						'relationship_to_student'=>$data['relationship_to_student'],
						'emergency_tel'			=>$data['emergency_tel'],
						'emergency_address'		=>$data['emergency_address'],
						'serial'	=>$data['serial'],
						'user_id'	=>$this->getUserId(),
						'is_makestudenttest'	=>1,
						'type'	=>1,
						'create_datetest' => date("Y-m-d H:i:s"),
						'modify_datetest' => date("Y-m-d H:i:s")
					);
					$stutest_id=$this->insert($array);
					if(!empty($data['identity'])){
						$ids = explode(',', $data['identity']);
						foreach ($ids as $i){
							$arr = array(
									'stutest_id'	=>$stutest_id,
									'school_name'	=>$data['school_name'.$i],
									'level'			=>$data['level'.$i],
									'year'			=>$data['year'.$i],
									'major'			=>$data['major'.$i],
									'note'			=>$data['remark_'.$i],
									'creat_date'	=>date("Y-m-d"),
									'status'		=>1,
									'user_id'		=>$this->getUserId(),
							);
							$this->_name='rms_student_testdetail';
							$this->insert($arr);
						}
					}
					
					$arrayScore = array(
							'stu_test_id'	=>$stutest_id,
							'degree'	=>$data['degree'],
							'grade'	=>$data['grade'],
							'test_date'		=>$data['test_date'],
							'note'		=>$data['note'],
							'create_date' => date("Y-m-d H:i:s"),
							'modify_date' => date("Y-m-d H:i:s"),
							'user_id'	=>$this->getUserId(),
					);
						
					if (!empty($data['score']) AND !empty($data['degree_result']) AND !empty($data['grade_result'])){
					
						$arrayScore['score']=$data['score'];
						$arrayScore['degree_result']=$data['degree_result'];
						$arrayScore['grade_result']=$data['grade_result'];
						$arrayScore['result_date']=empty($data['result_date'])?date("Y-m-d"):$data['result_date'];
						$arrayScore['updated_result']=1;
					}
					$this->_name="rms_student_test_result";
					$this->insert($arrayScore);
					
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();exit();
		}
		
 	}
 	
	function updateStudentTest($data,$id){
		$db=$this->getAdapter();
		try{
			$updated_result = 0;
			if(!empty($data['degree_result']) && !empty($data['grade_result']) ){
				$updated_result = 1;
			}
			
			$adapter = new Zend_File_Transfer_Adapter_Http();
			$part = PUBLIC_PATH.'/images';
			$adapter->setDestination($part);
			$adapter->receive();
			$photo = $adapter->getFileInfo();
			
			if(!empty($photo['photo']['name'])){
				$pho_name = $photo['photo']['name'];
			}else{
				$pho_name = $data['old_photo'];
			}
			$array = array(
						'branch_id'	=>$data['branch_id'],
						'stu_code'	=>$data['stu_code'],
						'kh_name'	=>$data['kh_name'],
						'first_name'	=>$data['first_name'],
						'en_name'	=>$data['en_name'],
						'sex'		=>$data['sex'],
						'nationality'=>$data['nationality'],
						'nation'=>$data['nation'],
						'dob'		=>$data['dob'],
						'pob'		=>$data['pob'],
						'phone'		=>$data['phone'],
						'email'		=>$data['email'],
						'address'	=>$data['address'],
						'student_status'	=>$data['student_status'],
						'if_employed_where'	=>$data['if_employed_where'],
						'position'			=>$data['position'],
						'parent_name'		=>$data['parent_name'],
						'parent_tel'		=>$data['parent_tel'],
						'photo'				=>$photo,

						'old_school'=>$data['old_school'],
						'old_grade'	=>$data['old_grade'],
					
						'emergency_name'		=>$data['emergency_name'],
						'relationship_to_student'=>$data['relationship_to_student'],
						'emergency_tel'			=>$data['emergency_tel'],
						'emergency_address'		=>$data['emergency_address'],
						'serial'	=>$data['serial'],
						'status'	=>$data['status'],
						'user_id'	=>$this->getUserId(),
						'is_makestudenttest'	=>1,
						'type'	=>1,
// 						'create_datetest' => date("Y-m-d H:i:s"),
						'modify_datetest' => date("Y-m-d H:i:s")
					);
			$where="id = $id";
			$this->update($array, $where);
			$sql = "DELETE FROM rms_student_testdetail WHERE stutest_id=".$id;
			$db->query($sql);
			
			if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'stutest_id'	=>$id,
							'school_name'	=>$data['school_name'.$i],
							'level'			=>$data['level'.$i],
							'year'			=>$data['year'.$i],
							'major'			=>$data['major'.$i],
							'note'			=>$data['remark_'.$i],
							'creat_date'	=>date("Y-m-d"),
							'status'		=>1,
							'user_id'		=>$this->getUserId(),
					);
					$this->_name='rms_student_testdetail';
					$this->insert($arr);
				}
			}
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
	}
	
	function getStudentTestById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_student_test where id=$id ";
		return $db->fetchRow($sql);
	}	
	
	function getStudentTestDetail($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_student_testdetail WHERE stutest_id=$id";
		return $db->fetchAll($sql);
	}
	
	function getAllStudentTest($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace('authstu');
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$print=$tr->translate("PRINT_PROFILE");
		$from_date =(empty($search['start_date']))? '1': " create_datetest >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_datetest <= '".$search['end_date']." 23:59:59'";
		
		$where = " AND ".$from_date." AND ".$to_date;
		$sql="  SELECT 
					id,
					(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = branch_id LIMIT 1) AS branch_name,
					serial,
					stu_code,
					kh_name,
					en_name,
					(SELECT name_kh from rms_view WHERE type=2 and key_code=sex LIMIT 1) as sex,
					(SELECT name_kh from rms_view WHERE type=21 and key_code=nationality LIMIT 1) as nationality,
					phone,
					dob,
					old_school,
					parent_name,
					parent_tel,
					(SELECT first_name FROM `rms_users` WHERE id=rms_student_test.user_id LIMIT 1) aS user_name,
					'$print'
				FROM 
					rms_student_test
				WHERE
					status=1 AND is_makestudenttest=1";
		
		
		if (!empty($search['txtsearch'])){
			$s_where = array();
			$s_search = trim(addslashes($search['txtsearch']));
			$s_where[] = " serial LIKE '%{$s_search}%'";
			$s_where[] = " kh_name LIKE '%{$s_search}%'";
			$s_where[] = " en_name LIKE '%{$s_search}%'";
			$s_where[] = " old_school LIKE '%{$s_search}%'";
			$s_where[] = " old_grade LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}    
		if(!empty($search['branch_search'])){
		$where .= " AND branch_id = ".$search['branch_search'];
		}
		if(!empty($search['degree'])){
			$where .= " and degree = ".$search['degree'];
		}
		if(!empty($search['term_test'])){
			$where .= " AND term_test ='".$search['term_test']."'";
		}
		if(!empty($search['result_status'])){
			$where .= " and updated_result = ".$search['result_status'];
		}
		$order=" order by id desc ";

		return $db->fetchAll($sql.$where.$order);
	}	
	
	function getStudentTestProfileById($id){
		$db = $this->getAdapter();
		$sql=" SELECT 
					*,
					(select en_name from rms_dept where dept_id = degree_result) as degree_name,
					(select major_enname from rms_major where major_id = grade_result) as grade_name,
					(select name_en from rms_view where type=4 and key_code = session_result) as session_name,
					(select name_en from rms_view where type=2 and key_code=sex) as sex,
					(select name_en from rms_view where type=16 and key_code=student_status) as student_status
				FROM 
					rms_student_test 
				where 
					id=$id ";
		return $db->fetchRow($sql);
	}
	
	
	function createStudentTestFromCrm($data){
		$db=$this->getAdapter();
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
			
			$array = array(
					'branch_id'	=>$data['branch_id'],
					'stu_code'	=>$data['stu_code'],
					'kh_name'	=>$data['kh_name'],
					'first_name'	=>$data['first_name'],
					'en_name'	=>$data['en_name'],
					'sex'		=>$data['sex'],
					'nationality'=>$data['nationality'],
					'nation'=>$data['nation'],
					'dob'		=>$data['dob'],
					'pob'		=>$data['pob'],
					'phone'		=>$data['phone'],
					'email'		=>$data['email'],
					'address'	=>$data['address'],
					'student_status'	=>$data['student_status'],
					'if_employed_where'	=>$data['if_employed_where'],
					'position'			=>$data['position'],
					'parent_name'		=>$data['parent_name'],
					'parent_tel'		=>$data['parent_tel'],
					'photo'				=>$photo,
					'old_school'=>$data['old_school'],
					'old_grade'	=>$data['old_grade'],
					
					'emergency_name'		=>$data['emergency_name'],
					'relationship_to_student'=>$data['relationship_to_student'],
					'emergency_tel'			=>$data['emergency_tel'],
					'emergency_address'		=>$data['emergency_address'],
						
// 					'note'		=>$data['note'],
					'serial'	=>$data['serial'],
					'user_id'	=>$this->getUserId(),
					'is_makestudenttest'	=>1,
					'create_datetest' => date("Y-m-d H:i:s"),
					'modify_datetest' => date("Y-m-d H:i:s")
						
			);
			
			$id = $data['id'];
			$where="id = $id";
			$this->update($array, $where);
			
			if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'stutest_id'	=>$id,
							'school_name'	=>$data['school_name'.$i],
							'level'			=>$data['level'.$i],
							'year'			=>$data['year'.$i],
							'major'			=>$data['major'.$i],
							'note'			=>$data['remark_'.$i],
							'creat_date'	=>date("Y-m-d"),
							'status'		=>1,
							'user_id'		=>$this->getUserId(),
					);
					$this->_name='rms_student_testdetail';
					$this->insert($arr);
				}
			}
			
			return $id;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
	}
	
	function getAllStudentByBranchTested($branch){
		$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission();
		$sql="SELECT id,CONCAT(en_name,'-',kh_name) AS name 
			FROM rms_student_test 
		WHERE (en_name!='' OR kh_name!='') AND is_makestudenttest=1 AND status=1 and register=0 $branch_id ";
		$sql.=" AND branch_id = $branch ";
		$sql.=" ORDER BY id DESC ";
		return $db->fetchAll($sql);
	}
	
	function insertTestExam($data){
		$db=$this->getAdapter();
		try{
			$array = array(
					'stu_test_id'	=>$data['stu_test_id'],
					'degree'	=>$data['degree'],
					'grade'	=>$data['grade'],
					'test_date'		=>$data['test_date'],
					'note'		=>$data['note'],
					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'user_id'	=>$this->getUserId(),
			);
			
			if (!empty($data['score']) AND !empty($data['degree_result']) AND !empty($data['grade_result'])){
				
				$array['score']=$data['score'];
				$array['degree_result']=$data['degree_result'];
				$array['grade_result']=$data['grade_result'];
				$array['result_date']=empty($data['result_date'])?date("Y-m-d"):$data['result_date'];
				$array['updated_result']=1;
			}
			$this->_name="rms_student_test_result";
			$id = $this->insert($array);
			
			return $id;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}
	}
	
	function getAllTestResult($stu_id){
		$db = $this->getAdapter();
		$sql="SELECT 
			str.*,
			(SELECT i.title FROM `rms_items` AS i WHERE i.id = str.degree AND i.type=1 LIMIT 1) AS degree_title,
			(SELECT idd.title FROM `rms_itemsdetail` AS idd WHERE idd.id = str.grade AND idd.items_type=1 LIMIT 1) AS grade_title,
			(SELECT i.title FROM `rms_items` AS i WHERE i.id = str.degree_result AND i.type=1 LIMIT 1) AS degree_result_title,
			(SELECT idd.title FROM `rms_itemsdetail` AS idd WHERE idd.id = str.grade_result AND idd.items_type=1 LIMIT 1) AS grade_result_title
			FROM
			`rms_student_test_result` AS str
			WHERE 
			str.stu_test_id = $stu_id ORDER BY str.id DESC ";
		
		return $db->fetchAll($sql);
	}
}