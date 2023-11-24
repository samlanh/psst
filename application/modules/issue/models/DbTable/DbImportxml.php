<?php

class Issue_Model_DbTable_DbImportxml extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_group_schedule';
	public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }

	public function getSubjectId($title,$strId){
		$sublang = !empty(strpos($title,'(EN)'))?2:1;
		$title = str_replace('(EN)','', $title);
		$title = str_replace('(KH)','', $title);
		
		$db = $this->getAdapter();
		$sql=" SELECT id FROM `rms_subject` WHERE subject_titlekh = '".$title."' OR  subject_titleen='".$title."'";
		$sql.="  AND subject_lang= $sublang AND type_subject=1";

		$subject_id =  $db->fetchOne($sql);
        
		if(empty($subject_id)){
			echo $title;
		//	exit();
			// $arr = array(
			// 	'subject_titlekh'=>$kh_name,
			// 	'subject_titleen'=>$eng_name,
			// 	'shortcut'		=>$eng_name,
			// 	'schoolOption'	=>1,
			// 	'is_parent'		=>0,
			// 	'subject_lang'	=>$subject_lang,
			// 	'type_subject'	=>$subject_type,
			// 	//'note'		    =>"From Import",
			// 	'date'			=>date("Y-m-d"),
			// 	// 'modify_date'	=>date("Y-m-d H:i:s"),
			// 	'status'		=>1,
			// 	'user_id'		=>$this->getUserId(),
			// );
			// $this->_name='rms_subject';
			// $subject_id = $this->insert($arr); 
		}else{
			$this->_name='rms_subject';
			$array = array(
				'strId' => $strId,
			);
			$where = "id =".$subject_id;

			$this->update($array,$where);
		}
		return $subject_id;
	}

	public function getTeacherId($title,$gender,$strId){
		$db = $this->getAdapter();
		$sex= ($gender=='M')?1:2;
		if(!empty($title)){
			$sql=" SELECT id FROM `rms_teacher` WHERE teacher_name_kh = '".$title."' OR teacher_name_en = '".$title."' ";
			$teacherId =  $db->fetchOne($sql);
			$dbg = new Application_Model_DbTable_DbGlobal();
			$code = $dbg->getTeacherCode(1);
			if(empty($teacherId)){
				 echo $title;
				// 	$_arr=array(
				// 		'branch_id' 		 => 1,
				// 		'teacher_name_en'	 => $title,
				// 		'teacher_name_kh'	 => $title,
				// 		'teacher_code'		 => $code,
				// 		'sex'				 => $sex,
				// 		'nation' 			 => 1,
				// 		'create_date' 		 => date("Y-m-d"),
				// 		'user_id'	  		 => $this->getUserId(),
				// );
				// $this->_name = "rms_teacher";
				// $teacherId =  $this->insert($_arr);
			}else{
				$this->_name='rms_teacher';
				$array = array(
					'strId' => $strId,
				);
				$where = "id =".$teacherId;
				$this->update($array,$where);
			}
		}
		return $teacherId;
	}

	public function getGroupId($title,$strId){
		$db = $this->getAdapter();

	
			$sql=" SELECT id FROM `rms_group` WHERE group_code like '%".$title."%' AND academic_year=7";
			$groupId =  $db->fetchOne($sql);
			$dbg = new Application_Model_DbTable_DbGlobal();
			if(empty($groupId)){
				 echo $title;
				// 	$_arr=array(
				// 		'branch_id' 		 => $data['branch_id'],
				// 		'teacher_name_en'	 => $eng_name,
				// 		'teacher_name_kh'	 => $kh_name,
				// 		'teacher_code'		 => $code,
				// 		'tel'				 => $tel,
				// 		'sex'				 => $sex,
				// 		'nation' 			 => 1,
				// 		'create_date' 		 => date("Y-m-d"),
				// 		'user_id'	  		 => $this->getUserId(),
				// );
				// $this->_name = "rms_teacher";
				// $teacherId =  $this->insert($_arr);
			}else{
				$this->_name='rms_group';
				$array = array(
					'strId' => $strId,
				);
				$where = "id =".$groupId;
				$this->update($array,$where);

			}
		
		return $groupId;
	}
	


	
}   

