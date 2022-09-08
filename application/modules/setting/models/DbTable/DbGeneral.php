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
			
			$this->_name='rms_setting';
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
			
			$rows = $this->geLabelByKeyName('settingStuID');
			if (empty($rows)){
				$arr = array('keyValue'=>$data['settingStuID'],'keyName'=>'settingStuID','note'=>"couting Student By Setting ",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$data['settingStuID']);
				$where=" keyName= 'settingStuID'";
				$this->update($arr, $where);
			}
			
			$rows = $this->geLabelByKeyName('schooolNameKh');
			if (empty($rows)){
				$arr = array('keyValue'=>$data['schooolNameKh'],'keyName'=>'schooolNameKh','note'=>"ឈ្មោះគ្រឹះស្ថានជាខ្មែរ",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$data['schooolNameKh']);
				$where=" keyName= 'schooolNameKh'";
				$this->update($arr, $where);
			}
			
			$rows = $this->geLabelByKeyName('schooolNameEng');
			if (empty($rows)){
				$arr = array('keyValue'=>$data['schooolNameEng'],'keyName'=>'schooolNameEng','note'=>"ឈ្មោះគ្រឹះស្ថានជាខ្មែរ",'user_id'=>$dbg->getUserId());
				$this->insert($arr);
			}else{
				$arr = array('keyValue'=>$data['schooolNameEng']);
				$where=" keyName= 'schooolNameEng'";
				$this->update($arr, $where);
			}
			
			
			$schoolOption = $this->getAllSchoolOption();
			if (!empty($schoolOption)){
				$this->_name="rms_schooloption";
				foreach ($schoolOption as $rs){
					//if (!empty($data['school_'.$rs['id']])){
						$status=1;
						if (empty($data['school_'.$rs['id']])){
							$status=0;
						}
						$arr = array('status'=>$status,);
						$where=" id= ".$rs['id'];
						$this->update($arr, $where);
					//}
				}
			}
			
			$valid_formats = array("jpg", "png", "gif", "bmp","jpeg","ico");
			$part= PUBLIC_PATH.'/images/logo/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$name = $_FILES['photo']['name'];
			$size = $_FILES['photo']['size'];
			$photo='';
			
			$rows = $this->geLabelByKeyName('logo');
			if (empty($rows)){
				if (!empty($name)){
					$tem =explode(".", $name);
					$image_name = "logo-gn".time().".".end($tem);
					$tmp = $_FILES['photo']['tmp_name'];
					if(move_uploaded_file($tmp, $part.$image_name)){
						$photo = $image_name;
					}
					else
						$string = "Image Upload failed";
					
					$arr = array(
							'keyName'=>'logo',
							'keyValue'=>$photo,
							'note'=>"",
							'user_id'=>$dbg->getUserId()
					);
					$this->_name='rms_setting';
					$this->insert($arr);
				}
			}else{
				if (!empty($name)){
					$tem =explode(".", $name);
					$image_name = time()."logo-gn.".end($tem);
					$tmp = $_FILES['photo']['tmp_name'];
					if(move_uploaded_file($tmp, $part.$image_name)){
						$photo = $image_name;
					}
					else
						$string = "Image Upload failed";
					
					$arr = array(
							'keyValue'=>$photo,
					);
					$where=" keyName= 'logo'";
					$this->_name='rms_setting';
					$this->update($arr, $where);
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

