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
				$pho_name = '';
			}
			$array = array(
						'branch_id'	=>$this->getBranchId(),
						'stu_code'	=>$data['stu_code'],
						'kh_name'	=>$data['kh_name'],
						'en_name'	=>$data['en_name'],
						'sex'		=>$data['sex'],
						'nationality'=>$data['nationality'],
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
						'photo'				=>$pho_name,

						'old_school'=>$data['old_school'],
						'old_grade'	=>$data['old_grade'],
						'degree'	=>$data['degree'],
						'grade'		=>$data['grade'],
					
						'emergency_name'		=>$data['emergency_name'],
						'relationship_to_student'=>$data['relationship_to_student'],
						'emergency_tel'			=>$data['emergency_tel'],
						'emergency_address'		=>$data['emergency_address'],
						//'educational_background'=>$data['edu_background'],
					
						'degree_result'	=>$data['degree_result'],
						'grade_result'	=>$data['grade_result'],
						'session_result'=>$data['session'],
						'time_result'	=>$data['time'],
					    //'date_result'   =>$data['date_result'],
					    'term_test'		=>$data['term_test'],
					
						'note'		=>$data['note'],
						'serial'	=>$data['serial'],
						'user_id'	=>$this->getUserId(),
						'test_date'	=>$data['test_date'],
					
						'updated_result'=>$updated_result,
					
						'create_date'=>date('Y-m-d'),
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
					
		}catch (Exception $e){
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
						'branch_id'	=>$this->getBranchId(),
						'stu_code'	=>$data['stu_code'],
						'kh_name'	=>$data['kh_name'],
						'en_name'	=>$data['en_name'],
						'sex'		=>$data['sex'],
						'nationality'=>$data['nationality'],
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
						'photo'				=>$pho_name,
					
						'old_school'=>$data['old_school'],
						'old_grade'	=>$data['old_grade'],
						'degree'	=>$data['degree'],
						'grade'		=>$data['grade'],
					
						'emergency_name'		=>$data['emergency_name'],
						'relationship_to_student'=>$data['relationship_to_student'],
						'emergency_tel'			=>$data['emergency_tel'],
						'emergency_address'		=>$data['emergency_address'],
						//'educational_background'=>$data['edu_background'],
					
						'degree_result'	=>$data['degree_result'],
						'grade_result'	=>$data['grade_result'],
						'session_result'=>$data['session'],
						'time_result'	=>$data['time'],
						//'date_result'   =>$data['date_result'],
						'term_test'		=>$data['term_test'],
					
						'note'		=>$data['note'],
						'serial'	=>$data['serial'],
						'user_id'	=>$this->getUserId(),
						'test_date'	=>$data['test_date'],
						'updated_result'=>$updated_result,
					
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
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
		
		$where = " AND ".$from_date." AND ".$to_date;
		$sql="  SELECT 
					id,
					serial,
					stu_code,
					kh_name,
					en_name,
					(SELECT name_kh from rms_view WHERE type=2 and key_code=sex LIMIT 1) as sex,
					phone,
					test_date,
					(SELECT en_name from rms_dept WHERE dept_id=degree_result LIMIT 1) as degree,
					(SELECT major_enname from rms_major where major_id=grade_result LIMIT 1) as grade,
					term_test,
					note,
					(SELECT first_name FROM `rms_users` WHERE id=rms_student_test.user_id LIMIT 1) aS user_name,
					(select name_en from rms_view WHERE type=14 and key_code=updated_result LIMIT 1) as result_status,
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
	
}