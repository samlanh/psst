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
		(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gds.academic_year LIMIT 1) AS academic,
		
		(SELECT $degree FROM rms_items WHERE rms_items.id=gds.degree AND rms_items.type=1 LIMIT 1) AS degree,
	    (SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=gds.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,

	     (SELECT $label from rms_view where rms_view.type=4 and rms_view.key_code=(SELECT g.session FROM rms_group AS g WHERE g.id = gds.group_id LIMIT 1) LIMIT 1)AS session,
	     
		(select room_name from rms_room where room_id=(SELECT g.room_id FROM rms_group AS g WHERE g.id = gds.group_id LIMIT 1) LIMIT 1) as room,
		'".$change_pwd."',
		(SELECT $label FROM `rms_view` WHERE TYPE=1 AND key_code = s.status LIMIT 1) AS status,
		(SELECT COUNT(t.`token`) FROM `mobile_mobile_token` AS t WHERE t.`stu_id` = s.`stu_id` LIMIT 1 ) AS number_mobile
		FROM rms_student AS s,
			rms_group_detail_student AS gds
		WHERE 
			gds.itemType=1
			AND s.stu_id = gds.stu_id
    		AND s.status=1 
    		AND gds.is_maingrade =1
    		AND gds.is_current =1
    		AND s.customer_type=1
		";
		
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
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.=" AND s.branch_id=".$search['branch_id'];
		}
		if(!empty($search['academic_year'])){
			$where.=" AND gds.academic_year=".$search['academic_year'];
		}
		if(!empty($search['degree'])){
			$where.=" AND gds.degree=".$search['degree'];
		}
		if(!empty($search['grade'])){
			$where.=" AND gds.grade=".$search['grade'];
		}
		if(!empty($search['session'])){
			$where.=" AND (SELECT g.session FROM rms_group AS g WHERE g.id = gds.group_id LIMIT 1)=".$search['session'];
		}
		if($search['status']>-1 AND $search['status']!=''){
			$where.=" AND s.status=".$search['status'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission();
		return $db->fetchAll($sql.$where.$orderby);
	}
	public function getStudentById($id){
		$db = $this->getAdapter();
		$sql = "SELECT *,(SELECT sgh.group_id FROM `rms_group_detail_student` AS sgh WHERE sgh.itemType=1 AND sgh.stu_id = s.`stu_id` ORDER BY sgh.gd_id DESC LIMIT 1) as group_id FROM rms_student as s WHERE s.stu_id =".$id;
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
		$sql="SELECT ml.code ,ml.keyName,ml.keyValue,ml.keyValueEn FROM `moble_label` AS ml 
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
					'keyValueEn'=>$data['lbl_smspaymentpaidEn'],
			);
			$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_smspaymentpaid");
			$this->update($_arr, $where);
			
			$_arr=array(
					'keyValue'=>$data['lbl_smsatt'],
					'keyValueEn'=>$data['lbl_smsattEn'],
			);
			$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_smsatt");
			$this->update($_arr, $where);
			
			$_arr=array(
					'keyValue'=>$data['lbl_smsmistake'],
					'keyValueEn'=>$data['lbl_smsmistakeEn'],
			);
			$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_smsmistake");
			$this->update($_arr, $where);
			
			$_arr=array(
					'keyValue'=>$data['lbl_smsscore'],
					'keyValueEn'=>$data['lbl_smsscoreEn'],
			);
			$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_smsscore");
			$this->update($_arr, $where);
			
			$_arr=array(
					'keyValue'=>$data['lbl_smsnews'],
					'keyValueEn'=>$data['lbl_smsnewsEn'],
			);
			$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_smsnews");
			$this->update($_arr, $where);
			
			$_arr=array(
					'keyValue'=>$data['lbl_smsnotification'],
					'keyValueEn'=>$data['lbl_smsnotificationEn'],
			);
			$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_smsnotification");
			$this->update($_arr, $where);
			
			$_arr=array(
					'keyValue'=>$data['lbl_paymentnotification'],
					'keyValueEn'=>$data['lbl_paymentnotificationEn'],
			);
			$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_paymentnotification");
			$this->update($_arr, $where);
			
			$_arr=array(
					'keyValue'=>$data['lbl_schoolphone'],
					'keyValueEn'=>$data['lbl_schoolphoneEn'],
			);
			$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_schoolphone");
			$this->update($_arr, $where);
			
			$rows = $this->geLabelByKeyNamesetting('amountRequestPermission');
			if (empty($rows)){
				$data['amountRequestPermission'] = empty($data['amountRequestPermission']) ? 1 : $data['amountRequestPermission'];
				$arr = array(
						'keyName'=>'amountRequestPermission',
						'keyValue'=>$data['amountRequestPermission'],
						'user_id'=>$this->getUserId(),
				);
				$this->_name='rms_setting';
				$this->insert($arr);
			}else{
				$data['amountRequestPermission'] = empty($data['amountRequestPermission']) ? 1 : $data['amountRequestPermission'];
				$arr = array(
						'keyValue'=>$data['amountRequestPermission'],
				);
				$where=" keyName= 'amountRequestPermission'";
				$this->_name='rms_setting';
				$this->update($arr, $where);
			}


			$valid_formats = array("jpg", "png", "gif", "bmp","jpeg","ico");
			$part= PUBLIC_PATH.'/images/slide/app-utility/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$name = $_FILES['mockupAppImage']['name'];
			$size = $_FILES['mockupAppImage']['size'];
			$photo='';
			
			$rows = $this->geLabelByKeyNamesetting('mockupAppImage');
			if (empty($rows)){
				if (!empty($name)){
					$tem =explode(".", $name);
					$image_name = "mockupAppImage-gn".time().".".end($tem);
					$tmp = $_FILES['mockupAppImage']['tmp_name'];
					if(move_uploaded_file($tmp, $part.$image_name)){
						$photo = $image_name;
					}
					else
						$string = "Image Upload failed";
					
					$arr = array(
							'keyName'=>'mockupAppImage',
							'keyValue'=>$photo,
							'user_id'=>$this->getUserId(),
					);
					$this->_name='rms_setting';
					$this->insert($arr);
				}
			}else{
				if (!empty($name)){
					$tem =explode(".", $name);
					$image_name = time()."mockupAppImage-gn.".end($tem);
					$tmp = $_FILES['mockupAppImage']['tmp_name'];
					if(move_uploaded_file($tmp, $part.$image_name)){
						$photo = $image_name;
					}
					else
						$string = "Image Upload failed";
					
					$arr = array(
							'keyValue'=>$photo,
					);
					$where=" keyName= 'mockupAppImage'";
					$this->_name='rms_setting';
					$this->update($arr, $where);
				}
			}


			$name1 = $_FILES['qrcodeAppLink']['name'];
			$size1 = $_FILES['qrcodeAppLink']['size'];
			$photo1='';
			
			$rows1 = $this->geLabelByKeyNamesetting('qrcodeAppLink');
			if (empty($rows1)){
				if (!empty($name1)){
					$tem =explode(".", $name1);
					$image_name = "qrcodeAppLink-gn".time().".".end($tem);
					$tmp = $_FILES['qrcodeAppLink']['tmp_name'];
					if(move_uploaded_file($tmp, $part.$image_name)){
						$photo1 = $image_name;
					}
					else
						$string = "Image Upload failed";
					
					$arr = array(
							'keyName'=>'qrcodeAppLink',
							'keyValue'=>$photo1,
							'user_id'=>$this->getUserId(),
					);
					$this->_name='rms_setting';
					$this->insert($arr);
				}
			}else{
				if (!empty($name1)){
					$tem =explode(".", $name1);
					$image_name = time()."qrcodeAppLink-gn.".end($tem);
					$tmp = $_FILES['qrcodeAppLink']['tmp_name'];
					if(move_uploaded_file($tmp, $part.$image_name)){
						$photo1 = $image_name;
					}
					else
						$string = "Image Upload failed";
					
					$arr = array(
							'keyValue'=>$photo1,
					);
					$where=" keyName= 'qrcodeAppLink'";
					$this->_name='rms_setting';
					$this->update($arr, $where);
				}
			}

		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function geLabelByKeyNamesetting($keyName){
    	$db = $this->getAdapter();
    	$sql = " SELECT s.`code`,s.keyName,s.keyValue 
				FROM `rms_setting` AS s
				WHERE s.status=1 
				AND s.`keyName` ='$keyName' LIMIT 1";
    	return $db->fetchRow($sql);
    }
	
	
	public function geLabelByKeyName($keyName){
		$db = $this->getAdapter();
		$sql = " SELECT s.*
		FROM `moble_label` AS s
		WHERE s.status=1
		AND s.`keyName` ='$keyName' LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function updateMobileIntroduction($data){
		try{
			
			$this->_name="moble_label";
			$rows = $this->geLabelByKeyName('lbl_introduction');
			
			if (empty($rows)){
				$_arr=array(
						'keyName'=>"lbl_introduction",
						'keyValue'=>$data['lbl_introduction'],
						'keyValueEn'=>$data['lbl_introductionEn'],
						'user_id'=>$this->getUserId(),
						'access_type'=>0,
				);
				$this->insert($_arr);
			}else{
				$_arr=array(
						'keyValue'=>$data['lbl_introduction'],
						'keyValueEn'=>$data['lbl_introductionEn'],
						'user_id'=>$this->getUserId(),
				);
				$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_introduction");
				$this->update($_arr, $where);
			}
			
			$rows = $this->geLabelByKeyName('lbl_introduction_i');
			if (empty($rows)){
				$_arr=array(
						'keyName'=>"lbl_introduction_i",
						'keyValue'=>$data['lbl_introduction_i'],
						'keyValueEn'=>$data['lbl_introduction_iEn'],
						'access_type'=>0,
						'user_id'=>$this->getUserId(),
				);
				$this->insert($_arr);
			}else{
				$_arr=array(
						'keyValue'=>$data['lbl_introduction_i'],
						'keyValueEn'=>$data['lbl_introduction_iEn'],
						'user_id'=>$this->getUserId(),
				);
				$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_introduction_i");
				$this->update($_arr, $where);
			}
			
			
			$rows = $this->geLabelByKeyName('introduction_image');
			if (empty($rows)){
				$part= PUBLIC_PATH.'/images/newsevent/introduction_image/';
				if (!file_exists($part)) {
					mkdir($part, 0777, true);
				}
				
				$_arr=array(
						'keyName'=>"introduction_image",
						'access_type'=>0,
						'user_id'=>$this->getUserId(),
				);
				$name = $_FILES['images']['name'];
				if (!empty($name)){
					$ss = 	explode(".", $name);
					$image_name = "introduction_image_".date("Y").date("m").date("d").time().".".end($ss);
					$tmp = $_FILES['images']['tmp_name'];
					if(move_uploaded_file($tmp, $part.$image_name)){
						$_arr['keyValue'] = $image_name;
						$_arr['keyValueEn'] = $image_name;
					}
				}
				$this->insert($_arr);
			}else{
				$part= PUBLIC_PATH.'/images/newsevent/introduction_image/';
				if (!file_exists($part)) {
					mkdir($part, 0777, true);
				}
				
				$_arr=array(
						'user_id'=>$this->getUserId(),
				);
				$name = $_FILES['images']['name'];
				if (!empty($name)){
					$ss = 	explode(".", $name);
					$image_name = "introduction_image_".date("Y").date("m").date("d").time().".".end($ss);
					$tmp = $_FILES['images']['tmp_name'];
					if(move_uploaded_file($tmp, $part.$image_name)){
						$_arr['keyValue'] = $image_name;
						$_arr['keyValueEn'] = $image_name;
					}
				}
				$where=$this->getAdapter()->quoteInto("keyName=?", "introduction_image");
				$this->update($_arr, $where);
			}
			
			$rows = $this->geLabelByKeyName('lbl_videointro');
				
			if (empty($rows)){
				$_arr=array(
						'keyName'=>"lbl_videointro",
						'keyValue'=>$data['lbl_videointro'],
						'keyValueEn'=>$data['lbl_videointro'],
						'user_id'=>$this->getUserId(),
						'access_type'=>0,
				);
				$this->insert($_arr);
			}else{
				$_arr=array(
						'keyValue'=>$data['lbl_videointro'],
						'keyValueEn'=>$data['lbl_videointro'],
						'user_id'=>$this->getUserId(),
				);
				$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_videointro");
				$this->update($_arr, $where);
			}
			
			$rows = $this->geLabelByKeyName('lbl_howtouse');
				
			if (empty($rows)){
				$_arr=array(
						'keyName'=>"lbl_howtouse",
						'keyValue'=>$data['lbl_howtouse'],
						'keyValueEn'=>$data['lbl_howtouse'],
						'user_id'=>$this->getUserId(),
						'access_type'=>0,
				);
				$this->insert($_arr);
			}else{
				$_arr=array(
						'keyValue'=>$data['lbl_howtouse'],
						'keyValueEn'=>$data['lbl_howtouse'],
						'user_id'=>$this->getUserId(),
				);
				$where=$this->getAdapter()->quoteInto("keyName=?", "lbl_howtouse");
				$this->update($_arr, $where);
			}
				
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
}

