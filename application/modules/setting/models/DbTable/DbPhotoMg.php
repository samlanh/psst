<?php

class Setting_Model_DbTable_DbPhotoMg extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_pickupcard'; 
	 public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
    
    function addPhotoMg($_data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
			
			$recordCode = empty($_data['recordCode']) ? "" : $_data['recordCode']; 
			$part = PUBLIC_PATH . '/images/photo/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$imageName = "";
			$base64Image = $_data['baseFileUpload'];
			if(!empty($base64Image)){
				$fileExtension="jpg";
				$extension = explode('/', mime_content_type($base64Image))[1];
				if(!empty($extension)){
					$fileExtension=$extension;
				}
				$stringInfo = "student";
				if($_data["recordType"]==2){
					$stringInfo = "father";
				}else if($_data["recordType"]==3){
					$stringInfo = "mother";
				}else if($_data["recordType"]==4){
					$stringInfo = "guardian";
				}else if($_data["recordType"]==5){
					$stringInfo = "teacher";
				}else if($_data["recordType"]==6){
					$stringInfo = "staff";
				}
				
				$imageName = $stringInfo.$recordCode."-".time() . ".".$fileExtension;
				$outputFile = $part.$imageName;
				$fileHandle = fopen($outputFile,"wb");
				
				$base64Image = trim($base64Image);
				$base64Image = str_replace('data:image/png;base64,', '', $base64Image);
				$base64Image = str_replace('data:image/jpg;base64,', '', $base64Image);
				$base64Image = str_replace('data:image/jpeg;base64,', '', $base64Image);
				$base64Image = str_replace('data:image/gif;base64,', '', $base64Image);
				$base64Image = str_replace(' ', '+', $base64Image);
	
				fwrite($fileHandle,base64_decode($base64Image));
				fclose($fileHandle);
			}
			if(!empty($imageName)){
				$recordId = empty($_data["recordId"]) ? 0 : $_data["recordId"];
				if($_data["recordType"]==1){
					$_arr= array(
							'photo'			=>$imageName,
							'modify_date'	=>date("Y-m-d H:i:s"),
							'user_id'		=>$this->getUserId(),
					);
					$this->_name = "rms_student";
					$where = ' stu_id = '.$recordId;
					$this->update($_arr, $where);	
				}else if($_data["recordType"]==2 || $_data["recordType"]==3 || $_data["recordType"]==4){
					$_arr= array(
							'modifyDate'	=>date("Y-m-d H:i:s"),
							'userId'		=>$this->getUserId(),
					);
					if($_data["recordType"]==2){
						$_arr['fatherPhoto'] = $imageName;
					}else if($_data["recordType"]==3){
						$_arr['motherPhoto'] = $imageName;
					}else if($_data["recordType"]==4){
						$_arr['guardianPhoto'] = $imageName;
					}
					$this->_name = "rms_family";
					$where = ' id = '.$recordId;
					$this->update($_arr, $where);	
				}else if($_data["recordType"]==5 || $_data["recordType"]==6 ){
					$_arr= array(
							'photo'			=>$imageName,
							'user_id'		=>$this->getUserId(),
					);
					$this->_name = "rms_teacher";
					$where = ' id = '.$recordId;
					$this->update($_arr, $where);	
				}
			}
	    	
	    	$_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
		}
    }
	
	
	function getAllListRecord($data){
		
		$dbGbNew = new Application_Model_DbTable_DbGlobalUp();
		$recordType = empty($data["recordType"]) ? 1 : $data["recordType"];
		$result ="";
		if($recordType==1){
			$result = $dbGbNew->getAllStudentsList($data);
		}else if($recordType==2 || $recordType==3 || $recordType==4){
			$result = $dbGbNew->getAllFamilyList($data);
		}else if($recordType==5){
			$data["staffType"] =1; 
			$result = $dbGbNew->getAllStaffsAndTeachersList($data);
		}else if($recordType==6){
			$data["staffType"] =2; 
			$result = $dbGbNew->getAllStaffsAndTeachersList($data);
		}
		
		return $result;
		
	}

    
    
}  
	  

