<?php

class Foundation_Model_DbTable_DbStudent extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rms_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	
	}
	function uploadFile($data){
		$part= PUBLIC_PATH.'/images/photo/';
		if (!file_exists($part)) {
			mkdir($part, 0777, true);
		}
	
		$photo = "";
		$name = $_FILES['webcam']['name'];
		if (!empty($name)){
			$ss = 	explode(".", $name);
			$image_name = "profile_".date("Y").date("m").date("d").time().".".end($ss);
			$tmp = $_FILES['webcam']['tmp_name'];
			if(move_uploaded_file($tmp, $part.$image_name)){
				$photo = $image_name;
				return $photo;
			}
			else
				$string = "Image Upload failed";
		}
		return null;
	}
	function updategroupstudent(){
		$db=$this->getAdapter();
		$sql="SELECT 
		s.`stu_id`,
		s.group_id,
		(SELECT `group_id` FROM `rms_group_detail_student` WHERE itemType=1 AND rms_group_detail_student.`stu_id`=s.`stu_id` AND is_pass=0 LIMIT 1 )
		 AS current_groupid
		 FROM `rms_student` AS s  WHERE s.group_id>0
		 AND s.group_id !=(SELECT `group_id` FROM `rms_group_detail_student` WHERE itemType=1 AND rms_group_detail_student.`stu_id`=s.`stu_id` AND is_pass=0 LIMIT 1)";
			$result = $db->fetchAll($sql);
			
			foreach($result as $rs){
				$arr = array(
						'group_id'=>$rs['current_groupid']
						);	
				$where=" stu_id=".$rs['stu_id'];
				$this->update($arr, $where);
			}
	}
	public function getAllStudent($search){
		$_db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label="name_en";
		$branch = "branch_nameen";
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
			$branch = "branch_namekh";
		}
		
		$from_date =(empty($search['start_date']))? '1': "s.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "s.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql = "SELECT  s.stu_id,
				(SELECT $branch FROM rms_branch WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
				s.stu_code,
				s.stu_khname,
				CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stu_name,
				(SELECT $label FROM `rms_view` WHERE type=2 AND key_code = s.sex LIMIT 1) AS sex,
				CASE
					WHEN primary_phone = 1 THEN s.tel
					WHEN primary_phone = 2 THEN s.father_phone
					WHEN primary_phone = 3 THEN s.mother_phone
					ELSE s.guardian_tel
				END as tel,
				(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=(SELECT fee_id FROM `rms_student_fee_history` WHERE student_id=s.stu_id AND is_current=1 LIMIT 1) LIMIT 1) AS academic,
				(SELECT group_code FROM `rms_group` WHERE rms_group.id=(SELECT ds.group_id FROM rms_group_detail_student AS ds 
					WHERE ds.itemType=1 AND ds.stu_id=s.stu_id AND ds.is_maingrade=1 AND ds.is_current=1 LIMIT 1) LIMIT 1) AS group_name,
			
				(SELECT first_name FROM rms_users WHERE s.user_id=rms_users.id LIMIT 1 ) AS user_name ";
				
		$sql.=$dbp->caseStatusShowImage("s.status");
		
		$sql.=" FROM rms_student AS s  WHERE  s.customer_type=1 ";
		$orderby = " ORDER BY stu_id DESC ";

		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[]=" REPLACE(stu_code,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(stu_khname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(stu_enname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(last_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(CONCAT(last_name,stu_enname),' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(CONCAT(stu_enname,last_name),' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" CONCAT(stu_enname,' ',last_name)  	LIKE '%{$s_search}%'";
			$s_where[]=" CONCAT(last_name,' ',stu_enname)  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(tel,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(father_phone,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(mother_phone,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(guardian_tel,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(father_enname,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(mother_enname,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(guardian_enname,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(remark,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(home_num,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(street_num,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(village_name,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(commune_name,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(district_name,' ','') LIKE '%{$s_search}%'";
			
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		

		if(!empty($search['group'])){
			$where.=" AND (SELECT ds.group_id FROM rms_group_detail_student AS ds
				WHERE ds.itemType=1 AND ds.stu_id=s.stu_id AND ds.is_maingrade=1 AND ds.is_current=1 AND ds.group_id =".$search['group']." LIMIT 1) ";
		}
		if(!empty($search['degree'])){
			$where.=" AND (SELECT ds.degree FROM rms_group_detail_student AS ds
				WHERE ds.itemType=1 AND ds.stu_id=s.stu_id AND ds.is_maingrade=1 AND ds.is_current=1 AND ds.degree =".$search['degree']." LIMIT 1) ";
		}
		if(!empty($search['grade_all'])){
			$where.=" AND (SELECT ds.grade FROM rms_group_detail_student AS ds
				WHERE ds.itemType=1 AND ds.stu_id=s.stu_id AND ds.is_maingrade=1 AND ds.is_current=1 AND ds.grade =".$search['grade_all']." LIMIT 1) ";
		}
		if(!empty($search['session'])){
			$where.=" AND (SELECT ds.session FROM rms_group_detail_student AS ds
			WHERE ds.stu_id=s.stu_id AND ds.is_maingrade=1 AND ds.is_current=1 AND ds.session =".$search['session']." LIMIT 1) ";
		}
		if($search['status']>-1){
			$where.=" AND s.status=".$search['status'];
		}
		if(!empty($search['branch_id'])){
			$where.=" AND s.branch_id=".$search['branch_id'];
		}
		$where.=$dbp->getAccessPermission('s.branch_id');
		//$where.= $dbp->getSchoolOptionAccess('(SELECT tf.school_option FROM `rms_student_fee_history` AS sfh,rms_tuitionfee tf WHERE sfh.student_id=s.stu_id AND sfh.fee_id=tf.id AND sfh.is_current=1 )');

		return $_db->fetchAll($sql.$where.$orderby);
	}
	public function getStudentById($id){
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		
		$village_name = "village_name";
		$commune_name = "commune_name";
		$district_name = "district_name";
		$province = "province_en_name";
		$occuTitle='occu_enname';
		$viewTitle = 'name_en';
		if($lang==1){// khmer
			$village_name = "village_namekh";
			$commune_name = "commune_namekh";
			$district_name = "district_namekh";
			$province = "province_kh_name";
			$occuTitle = 'occu_name';
			$viewTitle = 'name_kh';
		}
		
		$sql = "SELECT s.*,
					
					(SELECT $viewTitle FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality_title,
	    			(SELECT $viewTitle FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation_title,
	    			(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=s.father_job LIMIT 1) fath_job,
					(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=s.mother_job LIMIT 1) moth_job,
					(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=s.guardian_job LIMIT 1) guard_job,
					(SELECT v.$village_name FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_title,
			    	(SELECT c.$commune_name FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_title,
			    	(SELECT d.$district_name FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_title,
					(SELECT $province FROM rms_province WHERE province_id=s.province_id LIMIT 1) AS province_title
				FROM rms_student as s
				WHERE s.stu_id =".$id." 
				AND s.customer_type=1";
			$dbp = new Application_Model_DbTable_DbGlobal();
			$sql.=$dbp->getAccessPermission();
// 			$sql.= $dbp->getSchoolOptionAccess('(SELECT tf.school_option FROM `rms_student_fee_history` AS sfh,rms_tuitionfee tf WHERE sfh.student_id=s.stu_id AND sfh.fee_id=tf.id AND sfh.is_current=1 LIMIT 1)');
			///$sql.= $dbp->getSchoolOptionAccess('(SELECT tf.school_option FROM `rms_student_fee_history` AS sfh,rms_tuitionfee tf WHERE sfh.student_id=s.stu_id AND sfh.fee_id=tf.id AND sfh.is_current=1)');
			return $db->fetchRow($sql);
	}
	
	public function getStudentDocumentById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student_document as s WHERE s.stu_id =".$id;
		return $db->fetchAll($sql);
	}
	
	function getStudentExist($_data,$idStu=null){
		$db = $this->getAdapter();
		$name_en = $_data['name_kh'];
		$sex  = $_data['sex'];
		$dob  = $_data['date_of_birth'];
		$stu_code  = $_data['student_id'];
		$sql = "SELECT * FROM rms_student WHERE customer_type=1 AND stu_code="."'$stu_code'"." AND stu_khname="."'$name_en'"." AND sex=".$sex." AND dob="."'$dob'";
// 		"AND grade='".$grade."' AND session='".$session."'";      
		if (!empty($idStu)){
			$sql.=" AND stu_id !=$idStu";
		}                  
		return $db->fetchRow($sql);
	}
	function ifStudentIdExisting($stu_code,$id=null){
		$db = $this->getAdapter();
		$sql=" SELECT stu_id FROM rms_student WHERE stu_code='".$stu_code."'";
		if (!empty($id)){
			$sql.=" AND stu_id !=$id";
		}
		return $db->fetchOne($sql);
	}
	public function addStudent($_data){
			$_db = $this->getAdapter();
			$_db->beginTransaction();
			
			
			$mainRecord = empty($_data['is_main'])?0:$_data['is_main'];
			
			$id = $this->getStudentExist($_data);	
			if(!empty($id)){
				Application_Form_FrmMessage::Sucessfull("STUDENT_EXISTRING","/foundation/register/add");
				return -1;
			}
// 			$stu_code=$_data['student_id'];//id duplicate is check new
// 			$existing = $this->ifStudentIdExisting($stu_code);
// 			if(!empty($existing)){
// 				$dbg = new Application_Model_DbTable_DbGlobal();
// 				$degree_id = empty($_data['degree_'.$mainRecord])?0:$_data['degree_'.$mainRecord];
// 				$stu_code = $dbg->getnewStudentId($_data['branch_id'],$degree_id);
// 			}

			$dbg = new Application_Model_DbTable_DbGlobal();
			$degree_id = empty($_data['degree_'.$mainRecord])?0:$_data['degree_'.$mainRecord];
			$stu_code = $dbg->getnewStudentId($_data['branch_id'],$degree_id);
			
			$part= PUBLIC_PATH.'/images/photo/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}	
			$photo = "";
			if(!empty($_data['old_photo'])){
				$photo = $_data['old_photo'];
			}
			$name = $_FILES['photo']['name'];
			if (!empty($_data['uploaded'])){
				$photo=$_data['uploaded'];
			}else if (!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = "profile_student_".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['photo']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					$photo = $image_name;
				}
				else
					$string = "Image Upload failed";
			}
			
			try{	
				$_data['degreeStudent'] = $degree_id;//For Insert To Tale Count ID
				$dbg->updateAmountStudetByDegree($_data);//For Insert To Tale Count ID
				$stuToken = $dbg->getStudentToken();
				$_arr= array(
						'branch_id'		=>$_data['branch_id'],
						'stu_code'		=>$stu_code,
						'user_id'		=>$this->getUserId(),
						'stu_khname'	=>$_data['name_kh'],
						'last_name'		=>ucfirst($_data['last_name']),
						'stu_enname'	=>ucfirst($_data['name_en']),
						'sex'			=>$_data['sex'],
						
						'nationality'	=>$_data['studen_national'],
						'nation'		=>$_data['nation'],
						'dob'			=>$_data['date_of_birth'],
						'tel'			=>$_data['phone'],
						'primary_phone'	=>$_data['primary_phone'],
						'pob'			=>$_data['pob'],
						'home_num'		=>$_data['home_note'],
						'street_num'	=>$_data['way_note'],
						'village_name'	=>$_data['village_note'],
						'commune_name'	=>$_data['commun_note'],
						'district_name'	=>$_data['distric_note'],
						'province_id'	=>$_data['student_province'],
						
						'father_khname'	=>$_data['father_khname'],
						'father_enname'	=>$_data['fa_name_en'],
						'father_dob'	=>$_data['fa_dob'],
						'father_nation'	=>$_data['fa_national'],
						'father_job'	=>$_data['fa_job'],
						'father_phone'	=>$_data['fa_phone'],
						
						'mother_khname'	=>$_data['mother_khname'],
						'mother_enname'	=>$_data['mom_name_en'],
						'mother_dob'	=>$_data['mo_dob'],
						'mother_nation'	=>$_data['mom_nation'],
						'mother_job'	=>$_data['mo_job'],
						'mother_phone'	=>$_data['mon_phone'],

						'guardian_khname'=>$_data['guardian_khname'],
						'guardian_enname'=>$_data['guardian_name_en'],
						'guardian_dob'	=>$_data['guardian_dob'],
						'guardian_nation'=>$_data['guardian_national'],
						'guardian_job'	=>$_data['gu_job'],
						'guardian_tel'	=>$_data['guardian_phone'],
						
						/////other infomation tab /////
						'lang_level'	=>$_data['lang_level'],
						'from_school'	=>$_data['from_school'],
						'know_by'		=>$_data['know_by'],
						'sponser'		=>$_data['sponser'],
						'sponser_phone'	=>$_data['sponser_phone'],
						//////////////////////////////////////////////				
						'status'		=>1,
						'remark'		=>$_data['remark'],
						'create_date'	=>date("Y-m-d H:i:s"),
						'photo'  			 => $photo,
						'customer_type'			=>1,//Student
						
						'date_bacc'	=>$_data['date_baccexam'],
						'province_bacc'	=>$_data['school_province'],
						'center_bacc'	=>$_data['center_baccexam'],
						'room_bacc'	=>$_data['room_baccexam'],
						'table_bacc'	=>$_data['table_baccexam'],
						'grade_bacc'	=>$_data['grade_baccexam'],
						'score_bacc'	=>$_data['score_baccexam'],
						'certificate_bacc'	=>$_data['certificate_baccexam'],
						
						
						
						
						'studentToken'=>$stuToken
						);
				if (EDUCATION_LEVEL==1){
					$_arr['calture'] = $_data['calture'];
				}
				
				$partAudio= PUBLIC_PATH.'/images/frontFile/audio/';
				if (!file_exists($partAudio)) {
					mkdir($partAudio, 0777, true);
				}
				$audiofileName = $_FILES['audiofile']['name'];
				if (!empty($audiofileName)){
					$tem =explode(".", $audiofileName);
					$newFileName = "audio_".date("Y").date("m").date("d").time().".".end($tem);
					$tmp = $_FILES['audiofile']['tmp_name'];
					if(move_uploaded_file($tmp, $partAudio.$newFileName)){
						$_arr['audioTitle']=$newFileName;
					}
				}
				
				$part= PUBLIC_PATH.'/images/photo/';
				if (!file_exists($part)) {
					mkdir($part, 0777, true);
				}
				$dbg = new Application_Model_DbTable_DbGlobal();
				$name = $_FILES['father_photo']['name'];
				//$size = $_FILES['father_photo']['size'];
				if(!empty($_data['old_father_photo'])){
					$_arr['father_photo'] = $_data['old_father_photo'];
				}
				if (!empty($name)){
					$tem =explode(".", $name);
					$new_image_name = "fatherprofile".date("Y").date("m").date("d").time().".".end($tem);
					$photopj = $dbg->resizeImase($_FILES['father_photo'], $part,$new_image_name);
					$_arr['father_photo']=$photopj;
				}
				$name = $_FILES['mother_photo']['name'];
				//$size = $_FILES['mother_photo']['size'];
				if(!empty($_data['old_mother_photo'])){
					$_arr['mother_photo'] = $_data['old_mother_photo'];
				}
				if (!empty($name)){
					$tem =explode(".", $name);
					$new_image_name = "motherprofile".date("Y").date("m").date("d").time().".".end($tem);
					$photopj = $dbg->resizeImase($_FILES['mother_photo'], $part,$new_image_name);
					$_arr['mother_photo']=$photopj;
				}
				$name = $_FILES['guardian_photo']['name'];
				//$size = $_FILES['guardian_photo']['size'];
				if(!empty($_data['old_guardian_photo'])){
					$_arr['guardian_photo'] = $_data['old_guardian_photo'];
				}
				if (!empty($name)){
					$tem =explode(".", $name);
					$new_image_name = "guardianprofile".date("Y").date("m").date("d").time().".".end($tem);
					$photopj = $dbg->resizeImase($_FILES['guardian_photo'], $part,$new_image_name);
					$_arr['guardian_photo']=$photopj;
				}
				$id = $this->insert($_arr);
				
				$this->_name = 'rms_student_document';
				if(!empty($_data['identity'])){
					$part= PUBLIC_PATH.'/images/document/student/';
					if (!file_exists($part)) {
						mkdir($part, 0777, true);
					}
					$ids = explode(',', $_data['identity']);
					foreach ($ids as $i){
						$_arr = array(
								'stu_id'		=>$id,
								'document_type'	=>$_data['document_type_'.$i],
								'date_give'		=>$_data['date_give_'.$i],
								'date_end'		=>$_data['date_end_'.$i],
								'is_receive'	=>$_data['is_receive_'.$i],
								'note'			=>$_data['note_'.$i],
						);
						$name = $_FILES['attachment'.$i]['name'];
						if (!empty($name)){
							$ss = 	explode(".", $name);
							$image_name = "student_attachment_".date("Y").date("m").date("d").time().$i.".".end($ss);
							$tmp = $_FILES['attachment'.$i]['tmp_name'];
							if(move_uploaded_file($tmp, $part.$image_name)){
								$photo = $image_name;
								$_arr['attachment_file'] = $photo;
							}
						}
						$this->insert($_arr);
					}
				}
				
				$_dbfee = new Accounting_Model_DbTable_DbFee();
				$feeID = empty($_data['academic_year'])?0:$_data['academic_year'];
				$rowfee = $_dbfee->getFeeById($feeID);
				$academicYear = empty($rowfee['academic_year'])?0:$rowfee['academic_year'];
				
				$_arr= array(
						'branch_id'		=>$_data['branch_id'],
						'user_id'		=>$this->getUserId(),
						'student_id'	=>$id,
						'fee_id'		=>$_data['academic_year'],
						'academic_year'		=>$academicYear,
						'note'			=>$_data['remark'],
						'is_current'	=>1,
						'is_new'		=>$_data['stu_denttype'],
						'status'		=>1,
						'create_date'	=>date("Y-m-d H:i:s"),
						'modify_date'	=>date("Y-m-d H:i:s"),
				);
				$this->_name="rms_student_fee_history";
				$this->insert($_arr);
				
				$dbGroup = new Foundation_Model_DbTable_DbGroup();
				if(!empty($_data['identity_study'])){
					$ids = explode(',', $_data['identity_study']);
					foreach ($ids as $i){
						$group_id = empty($_data['group_'.$i])?0:$_data['group_'.$i];
						$is_setgroup = empty($_data['group_'.$i])?0:1;
						$group_info = $dbGroup->getGroupById($group_id);
						
						$isMain = 0;
						if(!empty($_data['is_main']) AND $i==$_data['is_main']){ $isMain =1;}
						$_arr = array(
								'stu_id'			=>$id,
								'is_newstudent'		=>$_data['stu_denttype'],
								'status'			=>1,
								'group_id'			=>$group_id,
								'degree'			=>$_data['degree_'.$i],
								'grade'				=>$_data['grade_'.$i],
								'is_current'		=>1,
								'is_setgroup'		=>$is_setgroup,
								'is_maingrade'		=>$isMain,
								'date'				=>date("Y-m-d"),
								'create_date'		=>date("Y-m-d H:i:s"),
								'modify_date'		=>date("Y-m-d H:i:s"),
								'user_id'			=>$this->getUserId(),
						);
						if (!empty($group_info)){
							$_arr['session'] = $group_info['session'];
							$academic_year = $group_info['academic_year'];
							
						}else{
							$_dbf = new Accounting_Model_DbTable_DbFee();
							$rowfee = $_dbf->getFeeById($_data['academic_year']);
							$academic_year=0;
							if(!empty($rowfee)){
								$academic_year = $rowfee['academic_year'];
							}
						}
						$_arr['academic_year'] = $academic_year;
						$this->_name="rms_group_detail_student";
						$this->insert($_arr);
						
						if($group_id>0){
							$this->_name = 'rms_group';
							$data_gro = array(
									'is_use'=> 1,//ប្រើប្រាស់
									'is_pass'=> 2,//កំពុងសិក្សា
							);
							$whereGroup = 'id = '.$group_id;
							$this->update($data_gro, $whereGroup);
						}
						
					}
				}
				
// 				//for update depart m
// 				$sql="SELECT id_start FROM `rms_items` WHERE id=".$_data['degree']." LIMIT 1";
// 				$id_start = $_db->fetchOne($sql);
// 				$this->_name="rms_items";
// 				$arr=array(
// 						'id_start'=>$id_start+1
// 				);
// 				$where="id = ".$_data['degree'];
// 				$this->update($arr, $where);
				$_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
			Application_Form_FrmMessage::message("INSERT_FAILE");
		}
	}
	public function updateStudent($_data){
		$db = $this->getAdapter();//ស្ពានភ្ជាប់ទៅកាន់Data Base
		$db->beginTransaction();//ទប់ស្កាត់មើលការErrore , មានErrore វាមិនអោយចូល
		try{	
			$part= PUBLIC_PATH.'/images/photo/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$_arr=array(
 					'branch_id'		=>$_data['branch_id'],
 					'stu_code'		=>$_data['student_id'],
					'user_id'		=>$this->getUserId(),
					'stu_khname'	=>$_data['name_kh'],
					'last_name'		=>ucfirst($_data['last_name']),
					'stu_enname'	=>ucfirst($_data['name_en']),
					'sex'			=>$_data['sex'],
					
					'nationality'	=>$_data['studen_national'],
					'nation'		=>$_data['nation'],
					'dob'			=>$_data['date_of_birth'],
					'tel'			=>$_data['phone'],
					'primary_phone'	=>$_data['primary_phone'],
					'pob'			=>$_data['pob'],
					'home_num'		=>$_data['home_note'],
					'street_num'	=>$_data['way_note'],
					'village_name'	=>$_data['village_note'],
					'commune_name'	=>$_data['commun_note'],
					'district_name'	=>$_data['distric_note'],
					'province_id'	=>$_data['student_province'],
					
					'father_khname'	=>$_data['father_khname'],
					'father_enname'	=>$_data['fa_name_en'],
					'father_dob'	=>$_data['fa_dob'],
					'father_nation'	=>$_data['fa_national'],					
					'father_job'	=>$_data['fa_job'],					
					'father_phone'	=>$_data['fa_phone'],
					
					'mother_khname'	=>$_data['mother_khname'],
					'mother_enname'	=>$_data['mom_name_en'],
					'mother_dob'	=>$_data['mo_dob'],
					'mother_nation'	=>$_data['mom_nation'],
					'mother_job'	=>$_data['mo_job'],
					'mother_phone'	=>$_data['mon_phone'],
					
					'guardian_khname'=>$_data['guardian_khname'],
					'guardian_enname'=>$_data['guardian_name_en'],
					'guardian_dob'	=>$_data['guardian_dob'],
					'guardian_nation'=>$_data['guardian_national'],
					'guardian_job'	=>$_data['gu_job'],
					'guardian_tel'	=>$_data['guardian_phone'],
					
					/////other infomation tab /////
					'lang_level'	=>$_data['lang_level'],
					'from_school'	=>$_data['from_school'],
					'know_by'		=>$_data['know_by'],
					'sponser'		=>$_data['sponser'],
					'sponser_phone'	=>$_data['sponser_phone'],
					//////////////////////////////////////////////
					
					'status'		=>$_data['status'],
					'remark'		=>$_data['remark'],
					
					'date_bacc'	=>$_data['date_baccexam'],
					'province_bacc'	=>$_data['school_province'],
					'center_bacc'	=>$_data['center_baccexam'],
					'room_bacc'	=>$_data['room_baccexam'],
					'table_bacc'	=>$_data['table_baccexam'],
					'grade_bacc'	=>$_data['grade_baccexam'],
					'score_bacc'	=>$_data['score_baccexam'],
					'certificate_bacc'	=>$_data['certificate_baccexam'],
					
					
					
					
					);
			if (EDUCATION_LEVEL==1){
				$_arr['calture'] = $_data['calture'];
			}
			$photo = "";
			$name = $_FILES['photo']['name'];
			if (!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = "profile_".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['photo']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					$_arr['photo']=$image_name;
				}
			}
			
			$partAudio= PUBLIC_PATH.'/images/frontFile/audio/';
			if (!file_exists($partAudio)) {
				mkdir($partAudio, 0777, true);
			}
			$audiofileName = $_FILES['audiofile']['name'];
			if (!empty($audiofileName)){
				$tem =explode(".", $audiofileName);
				$newFileName = "audio_".date("Y").date("m").date("d").time().".".end($tem);
				$tmp = $_FILES['audiofile']['tmp_name'];
				if(move_uploaded_file($tmp, $partAudio.$newFileName)){
					$_arr['audioTitle']=$newFileName;
				}
			}
			
			$part= PUBLIC_PATH.'/images/photo/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$dbg = new Application_Model_DbTable_DbGlobal();
			$name = $_FILES['father_photo']['name'];
			$size = $_FILES['father_photo']['size'];
			if (!empty($name)){
				$tem =explode(".", $name);
				$new_image_name = "fatherprofile".date("Y").date("m").date("d").time().".".end($tem);
				$photopj = $dbg->resizeImase($_FILES['father_photo'], $part,$new_image_name);
				$_arr['father_photo']=$photopj;
			}
			$name = $_FILES['mother_photo']['name'];
			$size = $_FILES['mother_photo']['size'];
			if (!empty($name)){
				$tem =explode(".", $name);
				$new_image_name = "motherprofile".date("Y").date("m").date("d").time().".".end($tem);
				$photopj = $dbg->resizeImase($_FILES['mother_photo'], $part,$new_image_name);
				$_arr['mother_photo']=$photopj;
			}
			$name = $_FILES['guardian_photo']['name'];
			$size = $_FILES['guardian_photo']['size'];
			if (!empty($name)){
				$tem =explode(".", $name);
				$new_image_name = "guardianprofile".date("Y").date("m").date("d").time().".".end($tem);
				$photopj = $dbg->resizeImase($_FILES['guardian_photo'], $part,$new_image_name);
				$_arr['guardian_photo']=$photopj;
			}
			
			$stu_id = $_data["id"];
			$where=$this->getAdapter()->quoteInto("stu_id=?", $stu_id);
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$this->update($_arr, $where);
			
			//Student Document Block
			$detailidlist = '';
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
	    		foreach ($ids as $i){
	    			if (empty($detailidlist)){
	    				if (!empty($_data['detailid'.$i])){
	    					$detailidlist= $_data['detailid'.$i];
	    				}
	    			}else{
	    				if (!empty($_data['detailid'.$i])){
	    					$detailidlist = $detailidlist.",".$_data['detailid'.$i];
	    				}
	    			}
	    		}
			}
			
			$this->_name = 'rms_student_document';
			$where="stu_id = ".$_data["id"];
			if (!empty($detailidlist)){ // check if has old payment detail  detail id
				$where.=" AND id NOT IN (".$detailidlist.")";
			}
			$this->delete($where);
			
			if(!empty($_data['identity'])){
				$part= PUBLIC_PATH.'/images/document/student/';
				if (!file_exists($part)) {
					mkdir($part, 0777, true);
				}
				
				$ids = explode(',', $_data['identity']);
				foreach ($ids as $i){
					if (!empty($_data['detailid'.$i])){
						$_arr = array(
								'stu_id'		=>$_data["id"],
								'document_type'	=>$_data['document_type_'.$i],
								'date_give'		=>$_data['date_give_'.$i],
								'date_end'		=>$_data['date_end_'.$i],
								'is_receive'	=>$_data['is_receive_'.$i],
								'note'			=>$_data['note_'.$i]
						);
						$name = $_FILES['attachment'.$i]['name'];
						if (!empty($name)){
							$ss = 	explode(".", $name);
							$image_name = "student_attachment_".date("Y").date("m").date("d").time().$i.".".end($ss);
							$tmp = $_FILES['attachment'.$i]['tmp_name'];
							if(move_uploaded_file($tmp, $part.$image_name)){
								$photo = $image_name;
								$_arr['attachment_file'] = $photo;
							}
						}
						$where=" id=".$_data['detailid'.$i];
						$this->update($_arr, $where);
						
					}else{
						$_arr = array(
								'stu_id'		=>$_data["id"],
								'document_type'	=>$_data['document_type_'.$i],
								'date_give'		=>$_data['date_give_'.$i],
								'date_end'		=>$_data['date_end_'.$i],
								'is_receive'	=>$_data['is_receive_'.$i],
								'note'			=>$_data['note_'.$i]
						);
						$name = $_FILES['attachment'.$i]['name'];
						if (!empty($name)){
							$ss = 	explode(".", $name);
							$image_name = "student_attachment_".date("Y").date("m").date("d").time().$i.".".end($ss);
							$tmp = $_FILES['attachment'.$i]['tmp_name'];
							if(move_uploaded_file($tmp, $part.$image_name)){
								$photo = $image_name;
								$_arr['attachment_file'] = $photo;
							}
						}
						$this->insert($_arr);
					}
					
				}
			}
			
			$_dbfee = new Accounting_Model_DbTable_DbFee();
			$feeID = empty($_data['academic_year'])?0:$_data['academic_year'];
			$rowfee = $_dbfee->getFeeById($feeID);
			$academicYear = empty($rowfee['academic_year'])?0:$rowfee['academic_year'];
			
			$currentFee =  $this->getCurentFeeStudentHistory($stu_id);
			
			$_arr= array(
					'branch_id'		=>$_data['branch_id'],
					'user_id'		=>$this->getUserId(),
					'student_id'	=>$stu_id,
					'fee_id'		=>$_data['academic_year'],
					'academic_year'		=>$academicYear,
					'note'			=>$_data['remark'],
					'is_current'	=>1,
					'is_new'		=>$_data['stu_denttype'],
					'status'		=>1,
					'modify_date'	=>date("Y-m-d H:i:s"),
			);
			$this->_name="rms_student_fee_history";
			if (!empty($currentFee)){
				$where="student_id = ".$stu_id." AND is_current=1";
				$this->update($_arr, $where);
			}else{
				$_arr['create_date']=date("Y-m-d H:i:s");
				$this->insert($_arr);
			}
			
			$dbGroup = new Foundation_Model_DbTable_DbGroup();
			if(!empty($_data['identity_study'])){
				$ids = explode(',', $_data['identity_study']);
				foreach ($ids as $i){
					if (!empty($_data['detailid_study'.$i])){
						
						$group_id = empty($_data['group_'.$i])?0:$_data['group_'.$i];
						$is_setgroup = empty($_data['group_'.$i])?0:1;
						
						$group_info = $dbGroup->getGroupById($group_id);
						
						$isMain = 0;
						if(!empty($_data['is_main']) AND $i==$_data['is_main']){ $isMain =1;}
						
						
						$_arr = array(
								'stu_id'			=>$stu_id,
								'is_newstudent'		=>$_data['stu_denttype'],
								'status'			=>1,
								'group_id'			=>$group_id,
								'degree'			=>$_data['degree_'.$i],
								'grade'				=>$_data['grade_'.$i],
								'is_current'		=>1,
								'is_setgroup'		=>$is_setgroup,
								'is_maingrade'		=>$isMain,
								'date'				=>date("Y-m-d"),
								'create_date'		=>date("Y-m-d H:i:s"),
								'modify_date'		=>date("Y-m-d H:i:s"),
								'user_id'			=>$this->getUserId(),
						);
						
						if (!empty($group_info)){
							$_arr['session'] = $group_info['session'];
							$academic_year = $group_info['academic_year'];
								
						}else{
							$academic_year=0;
							if(!empty($_data['academic_year'])){
								$_dbf = new Accounting_Model_DbTable_DbFee();
								$rowfee = $_dbf->getFeeById($_data['academic_year']);
								if(!empty($rowfee)){
									$academic_year = $rowfee['academic_year'];
								}
							}
						}
						
						$_arr['academic_year'] = $academic_year;
						
						$this->_name="rms_group_detail_student";
						$where=  "stu_id = $stu_id AND gd_id=".$_data['detailid_study'.$i];
						$this->update($_arr, $where);
						
						if($group_id>0){
							$this->_name = 'rms_group';
							$data_gro = array(
									'is_use'=> 1,//ប្រើប្រាស់
									'is_pass'=> 2,//កំពុងសិក្សា
							);
							$whereGroup = 'id = '.$group_id;
							$this->update($data_gro, $whereGroup);
						}
						
					}else{
						
						$group_id = empty($_data['group_'.$i])?0:$_data['group_'.$i];
						
						$group_info = $dbGroup->getGroupById($group_id);
						$is_setgroup = empty($_data['group_'.$i])?0:1;
						$isMain = 0;
						if($i==$_data['is_main']){ $isMain =1;}
						$_arr = array(
								'stu_id'			=>$stu_id,
								'is_newstudent'		=>$_data['stu_denttype'],
								'status'			=>1,
								'group_id'			=>$group_id,
								'degree'			=>$_data['degree_'.$i],
								'grade'				=>$_data['grade_'.$i],
								'is_current'		=>1,
								'is_setgroup'		=>$is_setgroup,
								'is_maingrade'		=>$isMain,
								'date'				=>date("Y-m-d"),
								'create_date'		=>date("Y-m-d H:i:s"),
								'modify_date'		=>date("Y-m-d H:i:s"),
								'user_id'			=>$this->getUserId(),
						);
						
						if (!empty($group_info)){
							$_arr['session'] = $group_info['session'];
							$academic_year = $group_info['academic_year'];
								
						}else{
							$academic_year=0;
							if(!empty($_data['academic_year'])){
								$_dbf = new Accounting_Model_DbTable_DbFee();
								$rowfee = $_dbf->getFeeById($_data['academic_year']);
								if(!empty($rowfee)){
									$academic_year = $rowfee['academic_year'];
								}
							}
						}
						$_arr['academic_year'] = $academic_year;
						
						$this->_name="rms_group_detail_student";
						$this->insert($_arr);
						
						if($group_id>0){
							$this->_name = 'rms_group';
							$data_gro = array(
									'is_use'=> 1,//ប្រើប្រាស់
									'is_pass'=> 2,//កំពុងសិក្សា
							);
							$whereGroup = 'id = '.$group_id;
							$this->update($data_gro, $whereGroup);
						}
					}
				}
			}
			
			$db->commit();//if not errore it do....
		}catch(Exception $e){
			echo $e->getMessage();exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	function getStudyHishotryById($id){
		$db = $this->getAdapter();
		$sql="SELECT * FROM rms_study_history WHERE stu_id = ".$id;
		$dbp = new Application_Model_DbTable_DbGlobal();
		//$sql.=$dbp->getAccessPermission();
		return $db->fetchRow($sql);
	}
	

	function getStudentInfoById($stu_id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `rms_student` WHERE stu_id=$stu_id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getSearchStudent($search){
		$db=$this->getAdapter();
		$sql="SELECT stu_id ,stu_code,stu_enname,stu_khname,sex,degree,grade,academic_year from rms_student 
			WHERE `status`=1 ";
		
		 if(!empty($search['grade'])){
		 	$sql.=" AND grade =".$search['grade'];
		 }
		 if(!empty($search['session'])){
		 	$sql.=" AND session =".$search['session'];
		 }
		 if(!empty($search['academy'])){
		 	$sql.=" AND academic_year =".$search['academy'];
		 }
		return $db->fetchAll($sql);
	}

	public function getNewAccountNumber($newid,$stu_type){
		$db = $this->getAdapter();
		$sql="  SELECT COUNT(stu_id)  FROM rms_student WHERE status=1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$new_acc_no=100+$new_acc_no;
		$pre='';
		$acc_no= strlen((int)$acc_no+1);
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	
	function getAllYear(){
		$db = $this->getAdapter();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission();
		
		$sql = "select id,CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')')as years,(select name_en from rms_view where type=7 and key_code=time) as time  from  rms_tuitionfee  where status=1 $branch_id  ";
		$group = " group by from_academic,to_academic,generation,time ";
		return $db->fetchAll($sql);
	}
	public function getAllFecultyName(){
		$_dbg = new Application_Model_DbTable_DbGlobal();
		return $_dbg->getAllItems(1,null);
	}
	function getProvince(){
		$_db = new Application_Model_DbTable_DbGlobal();
		return $_db->getAllProvince();
	}
	function getAllRoom(){
		$db = $this->getAdapter();
		$sql ="SELECT room_name as name,room_id as id FROM rms_room WHERE is_active=1 ";
		return $db->fetchAll($sql);
		
	}
	function getAllgroup(){
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		return $_dbgb->getAllGroupByBranch();
	}
	function getGroupInforByID($group_id){
		$db = $this->getAdapter();
		$sql ="SELECT * FROM `rms_group` AS g WHERE g.`id`=$group_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getStudentViewDetailById($id){
		$db=$this->getAdapter();
		
		//(SELECT rms_items.title FROM rms_items WHERE rms_items.id=s.degree AND rms_items.type=1 LIMIT 1) AS degreeTitle,
		//(SELECT rms_items.title FROM rms_items WHERE rms_items.id=s.degree AND rms_items.type=1 LIMIT 1) AS degree_name,
		//(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=s.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade_name,
		$sql="SELECT *,(SELECT province_kh_name FROM rms_province AS p WHERE p.province_id=s.province_id LIMIT 1) AS province_name,
		(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.father_job LIMIT 1) AS fa_job,
		(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.mother_job LIMIT 1) AS mo_job,
		(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.guardian_job LIMIT 1) AS gu_job,
		
		(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=(SELECT gds.grade FROM rms_group_detail_student AS gds WHERE gds.itemType=1 AND gds.stu_id=s.stu_id AND gds.is_current=1 AND gds.is_maingrade=1 ORDER BY gds.gd_id DESC LIMIT 1) AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade_name,
		(SELECT rms_items.title FROM rms_items WHERE rms_items.id=(SELECT gds.degree FROM rms_group_detail_student AS gds WHERE gds.itemType=1 AND  gds.stu_id=s.stu_id AND gds.is_current=1 AND gds.is_maingrade=1 ORDER BY gds.gd_id DESC  LIMIT 1) AND rms_items.type=1 LIMIT 1) AS degree_name,
		(SELECT rms_items.title FROM rms_items WHERE rms_items.id=(SELECT gds.degree FROM rms_group_detail_student AS gds WHERE gds.itemType=1 AND  gds.stu_id=s.stu_id AND gds.is_current=1 AND gds.is_maingrade=1 ORDER BY gds.gd_id DESC LIMIT 1) AND rms_items.type=1 LIMIT 1) AS degreeTitle,
		
		(SELECT name_kh FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=s.nationality) AS nationality,
		(SELECT name_kh FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=s.father_nation) AS father_nation,
		(SELECT name_kh FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=s.mother_nation) AS mother_nation,
		(SELECT name_kh FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=s.guardian_nation) AS guardian_nation
		
	 FROM rms_student AS s WHERE s.stu_id=$id";
		//(SELECT name_en from rms_view where rms_view.type=4 and rms_view.key_code=s.session LIMIT 1)AS session,
		//(SELECT from_academic FROM rms_tuitionfee WHERE rms_tuitionfee.id=s.academic_year LIMIT 1) AS start_year,
		//(SELECT to_academic FROM rms_tuitionfee WHERE rms_tuitionfee.id=s.academic_year LIMIT 1) AS end_year,
		//(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee WHERE rms_tuitionfee.id=s.academic_year LIMIT 1) AS academic_year,
		return $db->fetchRow($sql);
	}
	
	function getCurentFeeStudentHistory($student_id){
		$db=$this->getAdapter();
		$sql="SELECT sh.* FROM rms_student_fee_history AS sh WHERE sh.student_id=$student_id AND sh.is_current=1 ORDER BY sh.id DESC LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getCurentStudentStudy($student_id){
		$db=$this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		$sql="SELECT sh.*,
				(SELECT rms_items.$colunmname FROM `rms_items` WHERE `id`=sh.degree AND type=1 LIMIT 1) AS degreeTitle,
				(SELECT CONCAT(rms_itemsdetail.$colunmname) FROM `rms_itemsdetail` WHERE `id`=sh.grade AND items_type=1 LIMIT 1) AS gradeTitle,
				(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = sh.group_id LIMIT 1) AS groupCode
			FROM rms_group_detail_student AS sh 
			WHERE 
				sh.itemType=1 
				AND sh.stu_id=$student_id 
				AND sh.is_current=1 AND sh.is_pass=0";
		$sql.=" ORDER BY sh.gd_id ASC ";
		return $db->fetchAll($sql);
	}
	function getStudentStudyInfo($studyId){
		$db = $this->getAdapter();
		$sql="
			SELECT
				s.*,
				gds.academic_year,
				gds.group_id,
				gds.degree,
				gds.grade,
				gds.session,
				(SELECT g.room_id FROM `rms_group` AS g WHERE g.id = gds.group_id LIMIT 1) AS room
				
			FROM
				rms_student AS s,
				rms_group_detail_student AS gds
			WHERE
				gds.itemType=1 
				AND gds.stu_id = s.stu_id
				AND (stu_enname!='' OR s.stu_khname!='')
				AND s.status=1
				AND s.customer_type=1
				AND gds.gd_id = $studyId
			LIMIT 1
		";
		//AND gds.stop_type=0
		return $db->fetchRow($sql);
	}
	function getAllStudyByStudent($_data){
		$db = $this->getAdapter();
		$stu_id = empty($_data['stu_id'])?0:$_data['stu_id'];
		$branch_id = empty($_data['branch_id'])?0:$_data['branch_id'];
		$sql="
		SELECT
			gds.*,
			gds.academic_year,
			gds.group_id,
			gds.degree,
			gds.grade,
			gds.session,
			(SELECT g.room_id FROM `rms_group` AS g WHERE g.id = gds.group_id LIMIT 1) AS room
		
			FROM
				rms_group_detail_student AS gds
			WHERE
				gds.itemType=1 
				AND gds.is_current =1
				AND gds.stu_id = $stu_id
				AND (SELECT g.branch_id FROM `rms_group` AS g WHERE g.id = gds.group_id LIMIT 1) = $branch_id
		";
		
		return $db->fetchAll($sql);
	}

	
	function getAllStudentBybranch($branch_id){
    	$db = $this->getAdapter();
		
    	$sql = "SELECT 
		stu_id AS id, 
			CONCAT(stu_khname,'-',last_name,' ',stu_enname,'-',stu_code) AS name
		 FROM `rms_student` WHERE branch_id = $branch_id ";
    	return $db->fetchAll($sql);
    }
	function getStdyInfoById($stu_id){
		$db = $this->getAdapter();
		$sql = "SELECT *,	
					(SELECT title FROM rms_items WHERE rms_items.id=gds.degree AND rms_items.type=1 LIMIT 1) AS degree,
					(SELECT title FROM rms_itemsdetail WHERE rms_itemsdetail.id=gds.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
					(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gds.academic_year LIMIT 1) AS academic_year
	
		   FROM 
			rms_student AS s,
			rms_group_detail_student AS gds
		WHERE 
			gds.itemType=1 AND
			s.stu_id = gds.stu_id
			AND s.status=1 
			AND gds.is_current =1
			AND gds.is_maingrade =1
			AND s.customer_type=1 
			AND  s.stu_id=$stu_id ";
		return $db->fetchRow($sql);
	}
}