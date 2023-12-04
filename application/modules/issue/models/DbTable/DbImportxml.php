<?php

class Issue_Model_DbTable_DbImportxml extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_group_schedule';
	public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }

	public function getSubjectId($title=null,$strId,$shortcut=null){
		
		$db = $this->getAdapter();
		$sql=" SELECT id FROM `rms_subject` WHERE 1 ";
		
		if(!empty($title)){
			$sublang = !empty(strpos($title,'(EN)'))?2:1;
			$title = str_replace('(EN)','', $title);
			$title = str_replace('(KH)','', $title);
			
			$sql.=" AND subject_titlekh = '".$title."' OR  subject_titleen='".$title."'";
			$sql.=" AND subject_lang= $sublang AND type_subject=1";
		}
		if(!empty($shortcut)){
			$sql.=" AND shortcut='".$shortcut."'";
		}

		$subject_id =  $db->fetchOne($sql);
        
		if(empty($subject_id)){
			echo $title;
			exit();
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
	public function getTeacherId($title,$gender,$strId,$shortcut=null){
	
		$db = $this->getAdapter();
		
			$sql="SELECT id FROM `rms_teacher` WHERE 1";
			if(!empty($title)){
				$sql.=" AND teacher_name_kh = '".$title."' OR teacher_name_en = '".$title."'"; 
			}
			elseif(!empty($shortcut)){
				$sql.=" AND teacher_code = '".$shortcut."'";
			}
			
			$teacherId =  $db->fetchOne($sql);
			$dbg = new Application_Model_DbTable_DbGlobal();
				
			if(empty($teacherId)){
				$sex= ($gender=='M')?1:2;
				echo $title;
				exit();
				$code = $dbg->getTeacherCode(1);
				$_arr=array(
						'branch_id' 		 => 1,
						'teacher_name_en'	 => $title,
						'teacher_name_kh'	 => $title,
						'teacher_code'		 => $code,
						'sex'				 => $sex,
						'nation' 			 => 1,
						'create_date' 		 => date("Y-m-d"),
						'user_id'	  		 => $this->getUserId(),
				);
				$this->_name = "rms_teacher";
				$teacherId =  $this->insert($_arr);
			}else{
				$this->_name='rms_teacher';
				$array = array(
						'strId' => $strId,
				);
				$where = "id =".$teacherId;
				$this->update($array,$where);
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
			exit();
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
	public function getSubjectIdbyStrId($strId){
		$db = $this->getAdapter();
		$sql=" SELECT id FROM `rms_subject` WHERE strId = '".$strId."'" ;
		return $db->fetchOne($sql);
	}
	function getTeacherIdbyStrId($strId){
		$db = $this->getAdapter();
		$sql=" SELECT id FROM `rms_teacher` WHERE strId = '".$strId."'" ;
		return $db->fetchOne($sql);
	}
	function getGroupIdbyStrId($strId){
		$db = $this->getAdapter();
		$sql=" SELECT id FROM `rms_group` WHERE strId = '".$strId."'" ;
		return $db->fetchOne($sql);
	}
	function getPeriodData($index){
		$rs = array(
				1=>array('fromhr'=>'7.30','tohr'=>'8.20'),
				2=>array('fromhr'=>'8.40','tohr'=>'9.30'),
				3=>array('fromhr'=>'9.40','tohr'=>'10.30'),
				4=>array('fromhr'=>'10.40','tohr'=>'11.30'),
				5=>array('fromhr'=>'13.10','tohr'=>'14.00'),
				6=>array('fromhr'=>'14.10','tohr'=>'15.00'),
				7=>array('fromhr'=>'15.10','tohr'=>'16.00')
				);
		return $rs[$index];
	}
	function addcard($lessionId,$period,$day){
			$db = $this->getAdapter();
			$rs = $this->getPeriodData($period);
					$_arr=array(
						'lessionstrId' 		 => $lessionId,
						'periodstrId'	 => $period,
						'fromhr'		=>$rs['fromhr'],
						'tohr'		=>$rs['tohr'],
						'daystrId'	 =>$this->dayofWeek($day),
				);
				$this->_name = "rms_cards";
				$this->insert($_arr);
	}
	

	
	function dayofWeek($str){
		$days = array(
				'10000'=>1,//mon
				'01000'=>2,
				'00100'=>3,
				'00010'=>4,
				'00001'=>5,//fri
				);
		return $days[$str];
	}
	
	function getCardList($strId){
		$db = $this->getAdapter();
		$sql="SELECT
			periodstrId,
			fromhr,
			tohr,
			daystrId
		FROM `rms_cards`
			WHERE lessionstrId='".$strId."'";
		$sql.=" ORDER BY daystrId ASC,fromhr ASC";
		return $db->fetchAll($sql);
	}
	
	function updateExistingSchedule($lessionId,$data,$groupId){
		$db = $this->getAdapter();
		$results = $this->getCardList($lessionId);
		foreach($results as $rs){
			$this->_name="rms_group_reschedule";
			$where = "year_id=7 AND group_id=".$groupId." AND day_id=".$rs['daystrId']." AND from_hour=".$rs['fromhr']." AND to_hour=".$rs['tohr'];
			 $this->update($data, $where);
// 			exit();
		}
	}
}   

