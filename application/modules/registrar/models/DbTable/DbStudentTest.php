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
		$array = array(
					'branch_id'	=>$this->getBranchId(),
					'stu_code'	=>$data['stu_code'],
					'kh_name'	=>$data['kh_name'],
					'en_name'	=>$data['en_name'],
					'sex'		=>$data['sex'],
					'dob'		=>$data['dob'],
					'phone'		=>$data['phone'],
					'old_school'=>$data['old_school'],
					'old_grade'	=>$data['old_grade'],
					'degree'	=>$data['degree'],
					'note'		=>$data['note'],
					'serial'	=>$data['serial'],
					'address'	=>$data['address'],
					'user_id'	=>$this->getUserId(),
					'test_date'	=>$data['test_date'],
					'create_date'=>date('Y-m-d'),
				);
		$this->insert($array);
 	}
	function updateStudentTest($data,$id){
		
		$updated_result = 0;
		if(!empty($data['degree_result']) && !empty($data['grade_result']) && !empty($data['session_result'])){
			$updated_result = 1;
		}
		
		$array = array(
					'branch_id'	=>$this->getBranchId(),
					'kh_name'	=>$data['kh_name'],
					'en_name'	=>$data['en_name'],
					'sex'		=>$data['sex'],
					'dob'		=>$data['dob'],
					'phone'		=>$data['phone'],
					'old_school'=>$data['old_school'],
					'old_grade'	=>$data['old_grade'],
					'degree'	=>$data['degree'],
					'note'		=>$data['note'],
					
					'address'	=>$data['address'],
					'user_id'	=>$this->getUserId(),
					'status'	=>$data['status'],
				
					'stu_code'	=>$data['stu_code'],
					'test_date'	=>$data['test_date'],
				
					'degree_result'	=>$data['degree_result'],
					'grade_result'	=>$data['grade_result'],
					'session_result'=>$data['session_result'],
				
					'updated_result'=>$updated_result,
				
				);
		$where="id = $id";
		$this->update($array, $where);
	}
	
	function getStudentTestById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_student_test where id=$id ";
		return $db->fetchRow($sql);
	}	
	function getAllStudentTest($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace('authstu');
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
		
		$where = " and ".$from_date." AND ".$to_date;
		
		$sql="  SELECT 
					id,
					stu_code,
					kh_name,
					en_name,
					(select name_kh from rms_view where type=2 and key_code=sex LIMIT 1) as sex,
					phone,
					serial,
					(select en_name from rms_dept where dept_id=degree LIMIT 1) as degree,
					old_school,
					old_grade,
					note,
					test_date,
					(SELECT first_name FROM `rms_users` WHERE id=rms_student_test.user_id LIMIT 1),
					(select name_en from rms_view where type=15 and key_code=updated_result) as result_status
				FROM 
					rms_student_test
				where
					status=1
					and register=0 ";
		
		if (!empty($search['txtsearch'])){
				$s_where = array();
				$s_search = trim(addslashes($search['txtsearch']));
				$s_where[] = " kh_name LIKE '%{$s_search}%'";
				$s_where[] = " en_name LIKE '%{$s_search}%'";
				$s_where[] = " old_school LIKE '%{$s_search}%'";
				$s_where[] = " old_grade LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
		}      
		if(!empty($search['degree'])){
			$where .= " and degree = ".$search['degree'];
		}
		$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}	
}