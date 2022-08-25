<?php

class Foundation_Model_DbTable_DbStudenttranDrop extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_trandrop';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	
	public function getStudentDropById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student_drop WHERE id =".$id;
		return $db->fetchRow($sql);
	}
	public function addStudentDrop($_data){
		$_db= $this->getAdapter();
		try{	
			$sql="SELECT id FROM rms_student_trandrop WHERE academic_year =".$_data['academic_year'];
			$sql.=" AND name_kh='".$_data['name_kh']."'";
			$sql.=" AND calture='".$_data['calture']."'";
			$rs = $_db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
			$_arr= array(
					'stu_code'		=>$_data['student_id'],
					'name_kh'		=>$_data['name_kh'],
				//	'group'			=>$_data['group'],
					'sex'			=>$_data['sex'],
					'academic_year'	=>$_data['academic_year'],
					'calture'		=>$_data['calture'],
					'session'		=>$_data['session'],
					'degree'		=>$_data['degree'],
				//	'grade'			=>$_data['grade'],
					'room'			=>$_data['room'],
					'stu_stop'		=>$_data['stu_stop'],
					'reason'		=>$_data['reason'],
					'date_stop'		=>$_data['date_stop'],
					'status'		=>$_data['status'],
					'user_id'		=>$this->getUserId(),
					);
			$id = $this->insert($_arr);
		}catch(Exception $e){
			$_db->rollBack();
		}
	} 
	
}



