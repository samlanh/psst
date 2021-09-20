<?php

class Application_Model_DbTable_DbFront extends Zend_Db_Table_Abstract
{
	
	
	public function getStudentByScanning($qr,$check=null){
		
		$curr = new Application_Model_DbTable_DbGlobal();
		$lang= $curr->currentlang();
		$db = $this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': "s.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "s.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
				$field = 'name_en';
				$colunmname='title_en';
				if ($lang==1){
					$field = 'name_kh';
					$colunmname='title';
				}
				$sql ="SELECT  s.*,
							CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS fullNameEng,
							CASE
								WHEN primary_phone = 1 THEN s.tel
								WHEN primary_phone = 2 THEN s.father_phone
								WHEN primary_phone = 3 THEN s.mother_phone
								ELSE s.guardian_tel
							END as tel,
							ds.stop_type AS is_subspend,
							s.sex as sexcode,
							photo,
							(SELECT $field from rms_view where type=5 and key_code=ds.stop_type LIMIT 1) as status_student,
							(SELECT group_code FROM `rms_group` WHERE rms_group.id=ds.group_id AND ds.is_maingrade=1 LIMIT 1) AS group_name,
						    (SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = ds.degree AND i.type=1 AND ds.is_maingrade=1 LIMIT 1) AS degree,
						    (SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = ds.grade AND idd.items_type=1 AND ds.is_maingrade=1 LIMIT 1) AS grade,
						    ds.group_id,
						    (SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=ds.academic_year LIMIT 1) AS academic_year
						FROM rms_student AS s,
							rms_group_detail_student AS ds
						  WHERE  
						   ds.is_maingrade=1 
						   AND ds.is_current=1 
						   AND s.stu_id=ds.stu_id 
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
			$audio = $baseURl.'/images/frontFile/audio/no_sound.mp3';
			if (!empty($row['audioTitle'])){
				if (file_exists($phblicpart."/images/frontFile/audio/".$row['audioTitle'])){
					$audio = $baseURl.'/images/frontFile/audio/'.$row['audioTitle'];
				}
			}
			$row['fullUrlAudio']=$audio;
			$row['fullUrlProfile']=$photo;
			$row['statusReturn']=1;
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
	
}
?>