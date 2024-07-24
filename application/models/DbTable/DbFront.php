<?php

class Application_Model_DbTable_DbFront extends Zend_Db_Table_Abstract
{
	
	
	public function getStudentByScanning($qr,$check=null){
		
		$curr = new Application_Model_DbTable_DbGlobal();
		$lang= $curr->currentlang();
		$db = $this->getAdapter();
// 		$from_date =(empty($search['start_date']))? '1': "s.create_date >= '".$search['start_date']." 00:00:00'";
// 		$to_date = (empty($search['end_date']))? '1': "s.create_date <= '".$search['end_date']." 23:59:59'";
// 		$where = " AND ".$from_date." AND ".$to_date;
				$field = 'name_en';
				$colunmname='title_en';
				if ($lang==1){
					$field = 'name_kh';
					$colunmname='title';
				}
				$sql ="SELECT  s.*
							,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS fullNameEng,
							CASE
								WHEN s.primary_phone = 1 THEN s.tel
								WHEN s.primary_phone = 2 THEN COALESCE(fam.fatherPhone,'')
								WHEN s.primary_phone = 3 THEN COALESCE(fam.motherPhone,'')
								ELSE COALESCE(fam.guardianPhone,'')
							END as tel,
							ds.stop_type AS is_subspend,
							ds.gd_id AS study_id,
							s.sex as sexcode,
							s.is_vaccined,
							s.is_covidTested,
							photo,
							(SELECT $field from rms_view where type=5 and key_code=ds.stop_type LIMIT 1) as status_student,
							(SELECT sga.audioFile FROM `rms_setting_grade_audio` AS sga WHERE sga.gradeId=ds.grade LIMIT 1) AS gradeAudioFile,
							(SELECT group_code FROM `rms_group` WHERE rms_group.id=ds.group_id AND ds.is_maingrade=1 LIMIT 1) AS group_name,
							ds.group_id,
						    (SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = ds.degree AND i.type=1 AND ds.is_maingrade=1 LIMIT 1) AS degree,
						    (SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = ds.grade AND idd.items_type=1 AND ds.is_maingrade=1 LIMIT 1) AS grade,
						    ds.group_id,
						    (SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=ds.academic_year LIMIT 1) AS academic_year
						FROM 
							rms_student AS s JOIN rms_group_detail_student AS ds ON ds.itemType=1 AND ds.is_maingrade=1  AND ds.is_current=1  AND s.stu_id=ds.stu_id 
							LEFT JOIN rms_family AS fam ON fam.id = s.familyId 
						WHERE  
						   1 
		                   AND s.status = 1 
						AND s.customer_type = 1 ";
		$sql.="  AND s.studentToken = ".$db->quote($qr);
		
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		if (!empty($check)){
			if (!empty($row)){
				return true;
			}else{
				return false;
			}
		}else {
			$phblicpart = PUBLIC_PATH;
			$baseURl = Zend_Controller_Front::getInstance()->getBaseUrl();
			$photo = $baseURl.'/images/no-profile.png';
			if (!empty($row['photo'])){
				if (file_exists($phblicpart."/images/photo/".$row['photo'])){
					$photo = $baseURl.'/images/photo/'.$row['photo'];
				}
			}
			$defaultAudio = $baseURl.'/images/frontFile/audio/no_sound.mp3';
			$audio = $defaultAudio;
			if (!empty($row['audioTitle'])){
				if (file_exists($phblicpart."/images/frontFile/audio/".$row['audioTitle'])){
					$audio = $baseURl.'/images/frontFile/audio/'.$row['audioTitle'];
				}
			}
			$gradeAudioFile = $defaultAudio;
			if (!empty($row['gradeAudioFile'])){
				if (file_exists($phblicpart."/images/frontFile/audio/".$row['gradeAudioFile'])){
					$gradeAudioFile = $baseURl.'/images/frontFile/audio/'.$row['gradeAudioFile'];
				}
			}
			
			$allowAudio = $defaultAudio;
			if (!empty($row['welcomeAudio'])){
				if (file_exists($phblicpart."/images/frontFile/audio/".$row['welcomeAudio'])){
					$denyAudio = $baseURl.'/images/frontFile/audio/'.$row['welcomeAudio'];
				}
			}
			
			$denyAudio = $defaultAudio;
			if (!empty($row['denyGetInAudio'])){
				if (file_exists($phblicpart."/images/frontFile/audio/".$row['denyGetInAudio'])){
					$denyAudio = $baseURl.'/images/frontFile/audio/'.$row['denyGetInAudio'];
				}
			}
			
			$row['fullUrlAudio']=$audio;
			$row['fullUrlGradeAudio']=$gradeAudioFile;
			$row['fullUrlProfile']=$photo;
			$row['statusReturn']=1;
			$row['fullUrlAllowAudio']=$allowAudio;
			$row['fullUrlDenyAudio']=$denyAudio;
			return $row;
		}
	}
	
	public function getAllEntrance(){
		$db = $this->getAdapter();
		$sql = " SELECT * FROM rms_entrance_exit WHERE status = 1 ";
		$row=$db->fetchAll($sql);
		return $row;
	}
	public function getEntranceById($id){
		$db = $this->getAdapter();
		$sql = " SELECT * FROM rms_entrance_exit WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function getAllPlaylistvideo(){
		$db = $this->getAdapter();
		$sql = " SELECT * FROM rms_setting_playlistvideo WHERE status = 1 ";
		$row=$db->fetchAll($sql);
		return $row;
	}
	
	
	public function isertScanning($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$_arr=array(
				'study_id'	  		=> $_data['study_id'],
				'stu_id'			=> $_data['stu_id'],
				'group_id'			=> $_data['group_id'],
				'scan_type'			=> $_data['scan_type'],
				'create_date'	 	=> date('Y-m-d H:i:s'),
				'modify_date' 		=> date('Y-m-d H:i:s'),
				'entrance_id'	  	=> $_data['entrance_id'],
				"audio_played"		=> 0
			);
			$this->_name = "rms_scan_transaction";
			$id =  $this->insert($_arr);
			$db->commit();
			return $id;
				
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error");
			$db->rollBack();
		}
	}	
}
?>