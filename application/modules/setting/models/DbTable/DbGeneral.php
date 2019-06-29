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
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getAllSchoolOption(){
		$db = $this->getAdapter();
		$sql="SELECT * FROM `rms_schooloption`";
		return $db->fetchAll($sql);
	}
}

