<?php
class Registrar_Model_DbTable_DbStudentTest extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_test';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->branch_id;
	}
	
	function addStudentTest($data){
		$array = array(
					'branch_id'	=>$this->getBranchId(),
					'kh_name'	=>$data['kh_name'],
					'en_name'	=>$data['en_name'],
					'sex'		=>$data['sex'],
					'dob'		=>$data['dob'],
					'phone'		=>$data['phone'],
					'old_school'=>$data['old_school'],
					'old_grade'	=>$data['old_grade'],
				);
		$this->insert($array);
 	}
 	
	function updateStudentTest($data,$id){
		$array = array(
					'branch_id'	=>$this->getBranchId(),
					'kh_name'	=>$data['kh_name'],
					'en_name'	=>$data['en_name'],
					'sex'		=>$data['sex'],
					'dob'		=>$data['dob'],
					'phone'		=>$data['phone'],
					'old_school'=>$data['old_school'],
					'old_grade'	=>$data['old_grade'],
					'status'	=>$data['status'],
				);
		$where=" id = $id";
		$this->update($array, $where);
	}
	
	function getStudentTestById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_student_test where id=$id ";
		return $db->fetchRow($sql);
	}
	
	function getAllStudentTest($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace('auth');
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
		
		$where = " and ".$from_date." AND ".$to_date;
		
		$sql="  SELECT 
					id,
					kh_name,
					en_name,
					(select name_kh from rms_view where type=2 and key_code=sex) as sex,
					dob,
					phone,
					old_school,
					old_grade
				FROM 
					rms_student_test
				where
					status=1
					and register=0
			";
		
		if (!empty($search['txtsearch'])){
				$s_where = array();
				$s_search = trim(addslashes($search['txtsearch']));
				$s_where[] = " kh_name LIKE '%{$s_search}%'";
				$s_where[] = " en_name LIKE '%{$s_search}%'";
				$s_where[] = " old_school LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
		}
	       
		$order=" order by id desc ";
// 		echo $sql.$where.$order;exit();
		return $db->fetchAll($sql.$where.$order);
	}

	
	
	
	
	
}







