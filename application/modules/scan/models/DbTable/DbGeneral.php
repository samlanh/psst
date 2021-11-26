<?php

class Scan_Model_DbTable_DbGeneral extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_setting';
    
    public function geLabelByKeyName($keyName){
    	$db = $this->getAdapter();
    	$sql = " SELECT s.`code`,s.keyName,s.keyValue 
				FROM `rms_setting` AS s
				WHERE s.status=1 
				AND s.`keyName` ='$keyName' LIMIT 1";
    	return $db->fetchRow($sql);
    }
	public function updateWebsitesetting($data){
		try{
			$dbg = new Application_Model_DbTable_DbGlobal();
			
		
			
			
			$partAudio= PUBLIC_PATH.'/images/frontFile/audio/';
			if (!file_exists($partAudio)) {
				mkdir($partAudio, 0777, true);
			}
			$audiofileName = $_FILES['welcomeAudio']['name'];
			if (!empty($audiofileName)){
				$tem =explode(".", $audiofileName);
				$newFileName = "welcomeAudio_".date("Y").date("m").date("d").time().".".end($tem);
				$tmp = $_FILES['welcomeAudio']['tmp_name'];
				if(move_uploaded_file($tmp, $partAudio.$newFileName)){
					
				
					$rows = $this->geLabelByKeyName('welcomeAudio');
					if (empty($rows)){
						$arr = array('keyValue'=>$newFileName,'keyName'=>'welcomeAudio','note'=>"Intro Audio",'user_id'=>$dbg->getUserId());
						$this->_name ="rms_setting";
						$this->insert($arr);
					}else{
						$arr = array('keyValue'=>$newFileName);
						$where=" keyName= 'welcomeAudio'";
						$this->_name ="rms_setting";
						$this->update($arr, $where);
					}
				}
			
			}
			
			$confirmGetInAudioName = $_FILES['confirmGetInAudio']['name'];
			if (!empty($confirmGetInAudioName)){
				$tem =explode(".", $confirmGetInAudioName);
				$newFileName = "confirmGetInAudio".date("Y").date("m").date("d").time().".".end($tem);
				$tmp = $_FILES['confirmGetInAudio']['tmp_name'];
				if(move_uploaded_file($tmp, $partAudio.$newFileName)){
					
				
					$rows = $this->geLabelByKeyName('confirmGetInAudio');
					if (empty($rows)){
						$arr = array('keyValue'=>$newFileName,'keyName'=>'confirmGetInAudio','note'=>"confirm GetIn Audio",'user_id'=>$dbg->getUserId());
						$this->_name ="rms_setting";
						$this->insert($arr);
					}else{
						$arr = array('keyValue'=>$newFileName);
						$where=" keyName= 'confirmGetInAudio'";
						$this->_name ="rms_setting";
						$this->update($arr, $where);
					}
				}
			
			}
			
			$denyGetInAudioName = $_FILES['denyGetInAudio']['name'];
			if (!empty($denyGetInAudioName)){
				$tem =explode(".", $denyGetInAudioName);
				$newFileName = "denyGetInAudio".date("Y").date("m").date("d").time().".".end($tem);
				$tmp = $_FILES['denyGetInAudio']['tmp_name'];
				if(move_uploaded_file($tmp, $partAudio.$newFileName)){
					
				
					$rows = $this->geLabelByKeyName('denyGetInAudio');
					if (empty($rows)){
						$arr = array('keyValue'=>$newFileName,'keyName'=>'denyGetInAudio','note'=>"Deny GetIn Audio",'user_id'=>$dbg->getUserId());
						$this->_name ="rms_setting";
						$this->insert($arr);
					}else{
						$arr = array('keyValue'=>$newFileName);
						$where=" keyName= 'denyGetInAudio'";
						$this->_name ="rms_setting";
						$this->update($arr, $where);
					}
				}
			
			}
			
			
			$confirmGetInAudioName = $_FILES['confirmGetOutAudio']['name'];
			if (!empty($confirmGetInAudioName)){
				$tem =explode(".", $confirmGetInAudioName);
				$newFileName = "confirmGetOutAudio".date("Y").date("m").date("d").time().".".end($tem);
				$tmp = $_FILES['confirmGetOutAudio']['tmp_name'];
				if(move_uploaded_file($tmp, $partAudio.$newFileName)){
					
				
					$rows = $this->geLabelByKeyName('confirmGetOutAudio');
					if (empty($rows)){
						$arr = array('keyValue'=>$newFileName,'keyName'=>'confirmGetOutAudio','note'=>"confirm GetOut Audio",'user_id'=>$dbg->getUserId());
						$this->_name ="rms_setting";
						$this->insert($arr);
					}else{
						$arr = array('keyValue'=>$newFileName);
						$where=" keyName= 'confirmGetOutAudio'";
						$this->_name ="rms_setting";
						$this->update($arr, $where);
					}
				}
			
			}
			
			$denyGetOutAudioName = $_FILES['denyGetOutAudio']['name'];
			if (!empty($denyGetOutAudioName)){
				$tem =explode(".", $denyGetOutAudioName);
				$newFileName = "denyGetOutAudio".date("Y").date("m").date("d").time().".".end($tem);
				$tmp = $_FILES['denyGetOutAudio']['tmp_name'];
				if(move_uploaded_file($tmp, $partAudio.$newFileName)){
					
				
					$rows = $this->geLabelByKeyName('denyGetOutAudio');
					if (empty($rows)){
						$arr = array('keyValue'=>$newFileName,'keyName'=>'denyGetOutAudio','note'=>"Deny GetOut Audio",'user_id'=>$dbg->getUserId());
						$this->_name ="rms_setting";
						$this->insert($arr);
					}else{
						$arr = array('keyValue'=>$newFileName);
						$where=" keyName= 'denyGetOutAudio'";
						$this->_name ="rms_setting";
						$this->update($arr, $where);
					}
				}
			
			}
			
			
			//identity Audio
			$detailidlistAudio = '';
			if(!empty($data['identityAudio'])){
				$ids = explode(',', $data['identityAudio']);
	    		foreach ($ids as $i){
	    			if (empty($detailidlistAudio)){
	    				if (!empty($data['detailidAudio'.$i])){
	    					$detailidlistAudio= $data['detailidAudio'.$i];
	    				}
	    			}else{
	    				if (!empty($data['detailidAudio'.$i])){
	    					$detailidlistAudio = $detailidlistAudio.",".$data['detailidAudio'.$i];
	    				}
	    			}
	    		}
			}
			
			
			$where="";
			if (!empty($detailidlistAudio)){ // check if has old detail id
				$where.=" id NOT IN (".$detailidlistAudio.")";
			}
			$this->_name = 'rms_setting_grade_audio';
			$this->delete($where);
			
			
			if(!empty($data['identityAudio'])){
				$ids = explode(',', $data['identityAudio']);
				foreach ($ids as $i){
					if (!empty($data['detailidAudio'.$i])){
						$gradeId=$data['grade_'.$i];
						$_arr = array(
								'gradeId'			=>$gradeId,
								'modify_date'		=>date("Y-m-d H:i:s"),
								'user_id'			=>$dbg->getUserId(),
						);
						$audiofileRowName = $_FILES['audioFile'.$i]['name'];
						if (!empty($audiofileRowName)){
							$tem =explode(".", $audiofileRowName);
							$newAudiofileRowName = "grade".$gradeId."Audio_".date("Y").date("m").date("d").time().".".end($tem);
							$tmp = $_FILES['audioFile'.$i]['tmp_name'];
							if(move_uploaded_file($tmp, $partAudio.$newAudiofileRowName)){
								$_arr['audioFile']=$newAudiofileRowName;
							}
						
						}
						$this->_name="rms_setting_grade_audio";
						$where=  " id=".$data['detailidAudio'.$i];
						$this->update($_arr, $where);
					}else{
						$gradeId=$data['grade_'.$i];
						$_arr = array(
								'gradeId'			=>$gradeId,
								'create_date'		=>date("Y-m-d H:i:s"),
								'modify_date'		=>date("Y-m-d H:i:s"),
								'user_id'			=>$dbg->getUserId(),
						);
						$audiofileRowName = $_FILES['audioFile'.$i]['name'];
						if (!empty($audiofileRowName)){
							$tem =explode(".", $audiofileRowName);
							$newAudiofileRowName = "grade".$gradeId."Audio_".date("Y").date("m").date("d").time().".".end($tem);
							$tmp = $_FILES['audioFile'.$i]['tmp_name'];
							if(move_uploaded_file($tmp, $partAudio.$newAudiofileRowName)){
								$_arr['audioFile']=$newAudiofileRowName;
							}
						
						}
						$this->_name="rms_setting_grade_audio";
						$this->insert($_arr);
					}
				}
			}
			
			
			//identity PlayList
			$detailidPlayList = '';
			if(!empty($data['identityPlayList'])){
				$ids = explode(',', $data['identityPlayList']);
	    		foreach ($ids as $i){
	    			if (empty($detailidPlayList)){
	    				if (!empty($data['detailidPlayList'.$i])){
	    					$detailidPlayList= $data['detailidPlayList'.$i];
	    				}
	    			}else{
	    				if (!empty($data['detailidPlayList'.$i])){
	    					$detailidPlayList = $detailidPlayList.",".$data['detailidPlayList'.$i];
	    				}
	    			}
	    		}
			}
			$where="";
			if (!empty($detailidPlayList)){ // check if has old detail id
				$where.=" id NOT IN (".$detailidPlayList.")";
			}
			$this->_name = 'rms_setting_playlistvideo';
			$this->delete($where);
			
			
			if(!empty($data['identityPlayList'])){
				$ids = explode(',', $data['identityPlayList']);
				foreach ($ids as $i){
					if (!empty($data['detailidPlayList'.$i])){
						$_arr = array(
								'youtubeLink'		=>$data['youtubeLink'.$i],
								'modify_date'		=>date("Y-m-d H:i:s"),
								'user_id'			=>$dbg->getUserId(),
						);
						$this->_name="rms_setting_playlistvideo";
						$where=  " id=".$data['detailidPlayList'.$i];
						$this->update($_arr, $where);
					}else{
						$_arr = array(
								'youtubeLink'		=>$data['youtubeLink'.$i],
								'create_date'		=>date("Y-m-d H:i:s"),
								'modify_date'		=>date("Y-m-d H:i:s"),
								'user_id'			=>$dbg->getUserId(),
						);
						$this->_name="rms_setting_playlistvideo";
						$this->insert($_arr);
					}
				}
			}
					
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getAllSchoolOption(){
		$db = $this->getAdapter();
		$sql="SELECT * FROM `rms_schooloption`";
		return $db->fetchAll($sql);
	}
	function getAllGradeAudio(){
		$db = $this->getAdapter();
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$lang = $dbg->currentlang();
		$grade = "ie.title_en";
		if($lang==1){// khmer
			$grade = "ie.title";
		}
		
		$sql="SELECT sga.*,(SELECT $grade FROM rms_itemsdetail AS ie WHERE ie.id=sga.gradeId AND ie.items_type=1 LIMIT 1) AS gradeTitle FROM `rms_setting_grade_audio` AS sga WHERE sga.status=1 ";
		return $db->fetchAll($sql);
	}
	
	function getAllPlaylistvideo(){
		$db = $this->getAdapter();
		$sql="SELECT sga.* FROM `rms_setting_playlistvideo` AS sga WHERE sga.status=1 ";
		return $db->fetchAll($sql);
	}
}

