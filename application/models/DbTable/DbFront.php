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
	
	public function getScanDocumentByDocId($id){
		$db = $this->getAdapter();
		$this->_name="dt_scan_document";
		$sql=" SELECT sd.*,
		(SELECT dp.title FROM `dt_deptarment` AS dp WHERE dp.id = sd.department_scanner LIMIT 1) department,
		(SELECT dp.code FROM `dt_deptarment` AS dp WHERE dp.id = sd.department_scanner LIMIT 1) departmentCode,
		(SELECT u.full_name FROM `rms_users` AS u WHERE u.id = sd.scan_by LIMIT 1) AS scanBy,
		(SELECT v.name_kh FROM `ln_view` AS v WHERE v.key_code = sd.scan_type AND v.type = 4 LIMIT 1) AS scanType,
		(SELECT v.name_kh FROM `ln_view` AS v WHERE v.key_code = sd.doc_processing AND v.type = 5 LIMIT 1) AS proccess
		 FROM $this->_name AS sd
		 WHERE sd.document_id = ".$db->quote($id)."";
		$sql.=" ORDER BY sd.id DESC";
		$row=$db->fetchAll($sql);
		return $row;
	}
	
	
	public function isertScanDocument($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$checkScan = $this->getScanDocumentByTime($_data['document_id']);
			
			if(empty($checkScan)){
				$arr_old = array(
						"is_active"			=> 0
						);
				$this->_name = "dt_scan_document";
				$where = "document_id = ".$_data['document_id'];
				$this->update($arr_old, $where);
				
				$user = $this->getUserInfo();
				$user_id = empty($user['user_id'])?0:$user['user_id'];
				$_arr=array(
						'document_id'	  	=> $_data['document_id'],
						'scan_by'			=> $user_id,
						'department_scanner'=> $user['department'],
						'comment'	  		=> "",
						'doc_processing'	=> $_data['doc_processing'],
						'scan_type'	 		=> $_data['scan_type'],
						'create_date'	 	=> date('Y-m-d H:i:s'),
						'modify_date' 		=> date('Y-m-d H:i:s'),
						'status'	  		=> 1,
						"is_active"			=> 1
				);
				$this->_name = "dt_scan_document";
				$id =  $this->insert($_arr);
				$db->commit();
			return $id;
			}
		}catch (Exception $e){
			
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error");
			$db->rollBack();
		}
	}
	function getScanDocumentByTime($document_id){
		$db = $this->getAdapter();
		$user = $this->getUserInfo();
				$user_id = empty($user['user_id'])?0:$user['user_id'];
		$date = date("Y-m-d H:i");
		$sql='SELECT DATE_FORMAT(sc.create_date, "%Y-%m-%d %H:%i"),sc.* FROM `dt_scan_document` AS sc WHERE 1
			AND DATE_FORMAT(sc.create_date, "%Y-%m-%d %H:%i") = "'.$date.'" AND sc.document_id ='.$document_id.' AND sc.scan_by = '.$user_id.' LIMIT 1';
		
		$row=$db->fetchRow($sql);
		return $row;
	}
	
}
?>