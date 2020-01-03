<?php
class Mobileapp_Model_DbTable_Dbuseraccount extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rms_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	
	}
	public function getAllStudent($search){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$change_pwd = $tr->translate("CHANGE_PASSWORD");
		$db = $this->getAdapter();
		$_db  = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if($lang==1){// khmer
			$label = "name_kh";
			$branch = "branch_namekh";
			$grade = "rms_itemsdetail.title";
			$degree = "rms_items.title";
		}else{ // English
			$label = "name_en";
			$branch = "branch_nameen";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
		}
		
		$sql = "SELECT  s.stu_id,
		(SELECT branch_namekh FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
		s.stu_code,
		s.stu_khname,
		CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS name,
		(SELECT name_kh FROM `rms_view` WHERE TYPE=2 AND key_code = s.sex LIMIT 1) AS sex,
		tel ,
		(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=s.academic_year LIMIT 1) AS academic,
		
		(SELECT $degree FROM rms_items WHERE rms_items.id=s.degree AND rms_items.type=1 LIMIT 1) AS degree,
		(SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=s.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
		
		(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `s`.`session`)) LIMIT 1) AS `session`,
		(select room_name from rms_room where room_id=s.room LIMIT 1) as room,
		'".$change_pwd."',
		(SELECT name_en FROM `rms_view` WHERE TYPE=1 AND key_code = s.status LIMIT 1) AS status,
		(SELECT COUNT(t.`token`) FROM `mobile_mobile_token` AS t WHERE t.`stu_id` = s.`stu_id` LIMIT 1 ) AS number_mobile
		FROM rms_student AS s  WHERE  s.is_subspend=0 ";
		
		$from_date =(empty($search['start_date']))? '1': "s.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "s.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		$orderby = " ORDER BY stu_id DESC ";
		if(empty($search)){
			return $db->fetchAll($sql.$orderby);
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]=" stu_code LIKE '%{$s_search}%'";
			$s_where[]=" stu_khname LIKE '%{$s_search}%'";
			$s_where[]=" stu_enname LIKE '%{$s_search}%'";
			
			$s_where[]=" REPLACE(CONCAT(last_name,' ',stu_enname),' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" CONCAT(last_name,' ',stu_enname) LIKE '%{$s_search}%'";
			
			$s_where[]=" tel LIKE '%{$s_search}%'";
			$s_where[]=" father_phone LIKE '%{$s_search}%'";
			$s_where[]=" mother_phone LIKE '%{$s_search}%'";
			$s_where[]=" guardian_tel LIKE '%{$s_search}%'";
				
			$s_where[]=" father_enname LIKE '%{$s_search}%'";
			$s_where[]=" mother_enname LIKE '%{$s_search}%'";
			$s_where[]=" guardian_enname LIKE '%{$s_search}%'";
			$s_where[]=" remark LIKE '%{$s_search}%'";
			$s_where[]=" home_num LIKE '%{$s_search}%'";
			$s_where[]=" street_num LIKE '%{$s_search}%'";
			$s_where[]=" village_name LIKE '%{$s_search}%'";
			$s_where[]=" commune_name LIKE '%{$s_search}%'";
			$s_where[]=" district_name LIKE '%{$s_search}%'";
				
			$s_where[]=" (SELECT rms_view.name_en FROM rms_view WHERE rms_view.type = 4 AND rms_view.key_code = s.session) LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT name_kh FROM `rms_view` WHERE type=2 AND key_code = sex) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['study_year'])){
			$where.=" AND s.academic_year=".$search['study_year'];
		}
		if(!empty($search['degree'])){
			$where.=" AND s.degree=".$search['degree'];
		}
		if(!empty($search['grade_all'])){
			$where.=" AND s.grade=".$search['grade_all'];
		}
		if(!empty($search['session'])){
			$where.=" AND s.session=".$search['session'];
		}
		if($search['status'] != ""){
			$where.=" AND s.status=".$search['status'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission();
		//echo $sql.$where.$orderby;
		return $db->fetchAll($sql.$where.$orderby);
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

