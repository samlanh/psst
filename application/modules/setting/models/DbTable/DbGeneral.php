<?php

class Setting_Model_DbTable_DbGeneral extends Zend_Db_Table_Abstract
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
			
			$arr = array('keyValue'=>$data['branch_tel'],);
			$where=" keyName= 'branch_tel'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['branch_email'],);
			$where=" keyName= 'branch_email'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['branch_add'],);
			$where=" keyName= 'branch_add'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['label_animation'],);
			$where=" keyName= 'label_animation'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['receipt_print'],);
			$where=" keyName= 'receipt_print'";
			$this->update($arr, $where);
			
			$arr = array('keyValue'=>$data['show_header_receipt'],);
			$where=" keyName= 'show_header_receipt'";
			$this->update($arr, $where);
			
			$rows = $this->geLabelByKeyName('payment_day_alert');
			if (empty($rows)){
				$arr = array('keyValue'=>$data['payment_day_alert'],'keyName'=>'payment_day_alert','note'=>"ចំនួនថ្ងៃដែលត្រូវ  Alert ថ្ងៃបង់លុយមុន",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$data['payment_day_alert'],);
				$where=" keyName= 'payment_day_alert'";
				$this->update($arr, $where);
			}
			
			$rows = $this->geLabelByKeyName('trasfer_st_cut');
			if (empty($rows)){
				$arr = array('keyValue'=>$data['trasfer_st_cut'],'keyName'=>'trasfer_st_cut','note'=>"0=Transfer Cut Stock Direct,1=Transfer  Cut Stock with Receive",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$data['trasfer_st_cut'],);
				$where=" keyName= 'trasfer_st_cut'";
				$this->update($arr, $where);
			}
			
			$rows = $this->geLabelByKeyName('sale_cut_stock');
			if (empty($rows)){
				$arr = array('keyValue'=>$data['sale_stock'],'keyName'=>'sale_cut_stock','note'=>"0 cut direct when sale,1 cut stock in cut stock",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$data['sale_stock']);
				$where=" keyName= 'sale_cut_stock'";
				$this->update($arr, $where);
			}
			
			$schoolOption = $this->getAllSchoolOption();
			if (!empty($schoolOption)){
				$this->_name="rms_schooloption";
				foreach ($schoolOption as $rs){
					if (!empty($data['school_'.$rs['id']])){
						$status=1;
						if ($data['school_'.$rs['id']]==2){
							$status=0;
						}
						$arr = array('status'=>$status,);
						$where=" id= ".$rs['id'];
						$this->update($arr, $where);
					}
				}
			}
			
			
			
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
						$audiofileRowName = $_FILES['autionFile'.$i]['name'];
						if (!empty($audiofileRowName)){
							$tem =explode(".", $audiofileRowName);
							$newAudiofileRowName = "grade".$gradeId."Audio_".date("Y").date("m").date("d").time().".".end($tem);
							$tmp = $_FILES['autionFile'.$i]['tmp_name'];
							if(move_uploaded_file($tmp, $partAudio.$newAudiofileRowName)){
								$_arr['autionFile']=$newAudiofileRowName;
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
						$audiofileRowName = $_FILES['autionFile'.$i]['name'];
						if (!empty($audiofileRowName)){
							$tem =explode(".", $audiofileRowName);
							$newAudiofileRowName = "grade".$gradeId."Audio_".date("Y").date("m").date("d").time().".".end($tem);
							$tmp = $_FILES['autionFile'.$i]['tmp_name'];
							if(move_uploaded_file($tmp, $partAudio.$newAudiofileRowName)){
								$_arr['autionFile']=$newAudiofileRowName;
							}
						
						}
						$this->_name="rms_setting_grade_audio";
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
}

