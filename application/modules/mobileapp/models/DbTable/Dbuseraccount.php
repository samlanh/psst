<?php
class Mobileapp_Model_DbTable_Dbuseraccount extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rms_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	
	}
	
public function getStudentById($id){
		$db = $this->getAdapter();
		$sql = "SELECT *,(SELECT sgh.group_id FROM `rms_group_detail_student` AS sgh WHERE sgh.stu_id = s.`stu_id` ORDER BY sgh.gd_id DESC LIMIT 1) as group_id FROM rms_student as s WHERE s.stu_id =".$id;
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission();
		return $db->fetchRow($sql);
	}
	
public function updateStudent($_data){
		$db = $this->getAdapter();//ស្ពានភ្ជាប់ទៅកាន់Data Base
		try{	
			$_arr=array(
					'password'=>md5($_data['new_password']),
					);
			$where=$this->getAdapter()->quoteInto("stu_id=?", $_data["id"]);
			$this->update($_arr, $where);
		}catch(Exception $e){
		}
	}


	/*************update label**************/
	public function getAllLabelList($search){
		$db = $this->getAdapter();
		$sql = " SELECT code ,keyName ,keyValue FROM `moble_label` WHERE status=1 AND access_type=0 ";
		return $db->fetchAll($sql);
	}
	public function getAllSystemSetting(){
		$db = $this->getAdapter();
		$sql = " SELECT keycode ,value FROM `ln_system_setting` ";
		$_result = $db->fetchAll($sql);
		$_k = array();
		foreach ($_result as $key => $k) {
			$_k[$k['keycode']] = $k['value'];
		}
		return $_k;
	}
	public function getLabelVaueById($id){
		$db = $this->getAdapter();
		$sql = "SELECT code,keyName,keyValue FROM `moble_label` WHERE code = $id AND access_type=0";
		return $db->fetchRow($sql);
	}
	public function updateLabel($data){
		$this->_name="moble_label";
		$db = $this->getAdapter();
		$arr = array(
				'keyValue'=>$data['label_name'],
		);
		$where = $db->quoteInto('code=?', $data['id']);
		$this->update($arr, $where);
	
	}
	function updateKeyCode($post, $data){
		$this->_name='ln_system_setting';
		$_key_code_data=array();
		foreach ($post as $key => $val) {
			if($val != $data[$key]){
				$_key_code_data['value'] = $val;
	
				$where=$this->getAdapter()->quoteInto('keycode=?', $key);
				$this->update( $_key_code_data, $where);
				if($key == 'servername'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "servername");
					$this->update( $_key_code_data, $where);
				}else if($key == 'dbuser'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "dbuser");
					$this->update( $_key_code_data, $where);
				}else if($key == 'dbpassword'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "dbpassword");
					$this->update( $_key_code_data, $where);
				}else if($key == 'dbname'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "dbname");
					$this->update( $_key_code_data, $where);
				}else if($key == 'work_saturday'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "work_saturday");
					$this->update( $_key_code_data, $where);
				}else if($key == 'work_sunday'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "work_sunday");
					$this->update( $_key_code_data, $where);
				}
				else if($key == 'adminfee'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "adminfee");
					$this->update( $_key_code_data, $where);
				}else if($key == 'interest_late'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "interest_late");
					$this->update( $_key_code_data, $where);
				}else if($key == 'graice_pariod_late'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "graice_pariod_late");
					$this->update( $_key_code_data, $where);
				}else if($key == 'reschedule_postfix'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "reschedule_postfix");
					$this->update( $_key_code_data, $where);
				}else if($key == 'co_prefix'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "co_prefix");
					$this->update( $_key_code_data, $where);
				}else if($key == 'theme_setting'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "theme_setting");
					$this->update( $_key_code_data, $where);
					$array_theme = array(
							1=>"claro",
							2=>"nihilo",
							3=>"soria",
							4=>"tundra"
					);
					$session_user=new Zend_Session_Namespace(SYSTEM_SES);
					$session_user->theme_style=$array_theme[$val];
						
				}
			}
		}
	}
	
	function getMobileLabel($keyName=""){
		$db = $this->getAdapter();
		$sql="SELECT ml.code ,ml.keyName,ml.keyValue FROM `moble_label` AS ml 
			WHERE ml.status=1 AND ml.access_type=0";
		if (!empty($keyName)){
			$sql.=" AND ml.`keyName` = '$keyName' ";
		}
		$sql.=" LIMIT 1";
		return  $db->fetchRow($sql);
	}
	function updateMobileLabel($data){
		try{
			$this->_name="moble_label";
			$_arr=array(
					'keyValue'=>$data['lbl_smspaymentpaid'],
			);
			$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_smspaymentpaid");
			$this->update($_arr, $where);
			
			$_arr=array(
					'keyValue'=>$data['lbl_smsatt'],
			);
			$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_smsatt");
			$this->update($_arr, $where);
			
			$_arr=array(
					'keyValue'=>$data['lbl_smsmistake'],
			);
			$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_smsmistake");
			$this->update($_arr, $where);
			
			$_arr=array(
					'keyValue'=>$data['lbl_smsscore'],
			);
			$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_smsscore");
			$this->update($_arr, $where);
			
			$_arr=array(
					'keyValue'=>$data['lbl_smsnews'],
			);
			$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_smsnews");
			$this->update($_arr, $where);
			
			$_arr=array(
					'keyValue'=>$data['lbl_smsnotification'],
			);
			$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_smsnotification");
			$this->update($_arr, $where);
			
			$_arr=array(
					'keyValue'=>$data['lbl_paymentnotification'],
			);
			$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_paymentnotification");
			$this->update($_arr, $where);
			
			$_arr=array(
					'keyValue'=>$data['lbl_schoolphone'],
			);
			$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_schoolphone");
			$this->update($_arr, $where);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
}

