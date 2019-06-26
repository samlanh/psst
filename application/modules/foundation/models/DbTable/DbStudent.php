<?php

class Foundation_Model_DbTable_DbStudent extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rms_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
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
		(SELECT `group_id` FROM `rms_group_detail_student` WHERE rms_group_detail_student.`stu_id`=s.`stu_id` AND is_pass=0 LIMIT 1 )
		 AS current_groupid
		 FROM `rms_student` AS s  WHERE s.group_id>0
		 AND s.group_id !=(SELECT `group_id` FROM `rms_group_detail_student` WHERE rms_group_detail_student.`stu_id`=s.`stu_id` AND is_pass=0 LIMIT 1)";
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
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$from_date =(empty($search['start_date']))? '1': "s.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "s.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
				$sql = "SELECT  s.stu_id,
				(SELECT branch_nameen FROM rms_branch WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
				s.stu_code,
				s.stu_khname,
				CONCAT(s.last_name,' ',s.stu_enname),
				(SELECT name_kh FROM `rms_view` WHERE type=2 AND key_code = s.sex LIMIT 1) AS sex,
				tel ,
				(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=s.academic_year LIMIT 1) AS academic,
				(SELECT rms_items.$colunmname FROM `rms_items` WHERE `id`=s.degree AND type=1 LIMIT 1) AS degree,
				(SELECT CONCAT(rms_itemsdetail.$colunmname) FROM `rms_itemsdetail` WHERE `id`=s.grade AND items_type=1 LIMIT 1) AS grade,
				(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `s`.`session`)) LIMIT 1) AS `session`,
				(select room_name from rms_room where room_id=s.room LIMIT 1) as room,
				(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=s.group_id LIMIT 1 ) AS group_name,
				(SELECT name_kh from rms_view where type=5 and key_code=s.is_subspend LIMIT 1) as status_student
				 ";//AND s.is_subspend=0
				
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
			
// 			$s_where[]=" (SELECT rms_view.name_en FROM rms_view WHERE rms_view.type = 4 AND rms_view.key_code = s.session LIMIT 1) LIKE '%{$s_search}%'";
// 			$s_where[]=" (SELECT name_kh FROM `rms_view` WHERE type=2 AND key_code = sex  LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['study_year'])){
			$where.=" AND s.academic_year=".$search['study_year'];
		}
		if(!empty($search['group'])){
			$where.=" AND s.group_id=".$search['group'];
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
		if($search['status']>0){
			$where.=" AND s.status=".$search['status'];
		}
		if(!empty($search['branch_id'])){
			$where.=" AND s.branch_id=".$search['branch_id'];
		}
		$where.=$dbp->getAccessPermission('s.branch_id');
		$where.= $dbp->getSchoolOptionAccess('(SELECT i.schoolOption FROM `rms_items` AS i WHERE i.type=1 AND i.id = s.degree )');
		return $_db->fetchAll($sql.$where.$orderby);
	}
	public function getStudentById($id){
		$db = $this->getAdapter();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		
		if($lang==1){// khmer
		
			$village_name = "village_namekh";
			$commune_name = "commune_namekh";
			$district_name = "district_namekh";
			$province = "province_kh_name";
		}else{ // English
		
			$village_name = "village_name";
			$commune_name = "commune_name";
			$district_name = "district_name";
			$province = "province_en_name";
		}
		
	$sql = "SELECT *,
				s.academic_year as stu_academic_year,
				(SELECT CONCAT(f.from_academic,'-',f.to_academic) FROM rms_tuitionfee AS f WHERE f.id=(SELECT g.academic_year FROM `rms_group` AS g WHERE g.id=s.group_id LIMIT 1 ) AND f.`status`=1 GROUP BY f.from_academic,f.to_academic,f.generation)  AS academic_year,
				(SELECT sgh.group_id FROM rms_group_detail_student AS sgh WHERE sgh.stu_id = s.`stu_id` ORDER BY sgh.gd_id DESC LIMIT 1) as group_id,
				(SELECT g.academic_year FROM `rms_group` AS g WHERE g.id=(SELECT sgh.group_id FROM rms_group_detail_student AS sgh WHERE sgh.stu_id = s.`stu_id` ORDER BY sgh.gd_id DESC LIMIT 1)  LIMIT 1 ) as academic_id,
				(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=s.group_id LIMIT 1 ) AS group_name,
				(SELECT rms_items.schoolOption FROM rms_items WHERE rms_items.id=s.degree AND rms_items.type=1 LIMIT 1) AS schoolOption,
				(SELECT name_kh FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality_title,
    			(SELECT name_kh FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation_title,
    			(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.father_job LIMIT 1) fath_job,
				(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.mother_job LIMIT 1) moth_job,
				(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.guardian_job LIMIT 1) guard_job,
				 
				(SELECT v.$village_name FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_title,
		    	(SELECT c.$commune_name FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_title,
		    	(SELECT d.$district_name FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_title,
				(SELECT $province FROM rms_province WHERE province_id=s.province_id LIMIT 1) AS province_title
				
				FROM rms_student as s
				WHERE s.stu_id =".$id." 
				AND s.customer_type=1";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission();
		$sql.= $dbp->getSchoolOptionAccess('(SELECT i.schoolOption FROM `rms_items` AS i WHERE i.type=1 AND i.id = s.degree )');
		return $db->fetchRow($sql);
		echo $sql; exit();
	}
	
	public function getStudentDocumentById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student_document as s WHERE s.stu_id =".$id;
		return $db->fetchAll($sql);
	}
	
	function getStudentExist($name_en,$sex,$grade,$dob,$session){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student WHERE customer_type=1 AND stu_khname="."'$name_en'"." AND sex=".$sex." 
			AND grade=".$grade." AND dob="."'$dob'"." AND session=".$session;                          
		return $db->fetchRow($sql);
	}
	function ifStudentIdExisting($stu_code){
		$db = $this->getAdapter();
		$sql=" SELECT stu_id FROM rms_student WHERE stu_code='".$stu_code."'";
		return $db->fetchOne($sql);
	}
	public function addStudent($_data){
			$_db = $this->getAdapter();
			$_db->beginTransaction();
		
			$id = $this->getStudentExist($_data['name_kh'],$_data['sex'],$_data['grade'],$_data['date_of_birth'],$_data['session']);	
			if(!empty($id)){
				Application_Form_FrmMessage::Sucessfull("STUDENT_EXISTRING","/foundation/register/add");
				return -1;
			}
			$stu_code=$_data['student_id'];//id duplicate is check new
			$existing = $this->ifStudentIdExisting($stu_code);
			if(!empty($existing)){
				$dbg = new Application_Model_DbTable_DbGlobal();
				$stu_code = $dbg->getnewStudentId($_data['branch_id'],$_data['degree']);
			}
			$part= PUBLIC_PATH.'/images/photo/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}	
			$photo = "";
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
				$is_setgroup=0;
				if(!empty($_data['group']) AND $_data['group']!=-1){
					$is_setgroup=1;
				}
				$session_user=new Zend_Session_Namespace('authstu');
				$branch_id = $session_user->branch_id;
				$_arr= array(
						'branch_id'		=>$_data['branch_id'],
						'user_id'		=>$this->getUserId(),
						'stu_khname'	=>$_data['name_kh'],
						'last_name'		=>ucfirst($_data['last_name']),
						'stu_enname'	=>ucfirst($_data['name_en']),
						'sex'			=>$_data['sex'],
						'is_stu_new'	=>$_data['stu_denttype'],
						'nationality'	=>$_data['studen_national'],
						'nation'		=>$_data['nation'],
						'dob'			=>$_data['date_of_birth'],
						'tel'			=>$_data['phone'],
						'pob'			=>$_data['pob'],
						'home_num'		=>$_data['home_note'],
						'street_num'	=>$_data['way_note'],
						'village_name'	=>$_data['village_note'],
						'commune_name'	=>$_data['commun_note'],
						'district_name'	=>$_data['distric_note'],
						'province_id'	=>$_data['student_province'],
						'group_id'		=>$_data['group'],
						'stu_code'		=>$stu_code,
						'academic_year'	=>$_data['academic_year'],
						'degree'		=>$_data['degree'],
						'calture'		=>$_data['calture'],
						'grade'			=>$_data['grade'],
						'room'			=>$_data['room'],
						'session'		=>$_data['session'],
						'father_enname'	=>$_data['fa_name_en'],
						
						'father_dob'	=>$_data['fa_dob'],
						'father_nation'	=>$_data['fa_national'],
						'father_job'	=>$_data['fa_job'],
						'father_phone'	=>$_data['fa_phone'],
						
						'mother_enname'	=>$_data['mom_name_en'],
						'mother_dob'	=>$_data['mo_dob'],
						'mother_nation'	=>$_data['mom_nation'],
						'mother_job'	=>$_data['mo_job'],
						'mother_phone'	=>$_data['mon_phone'],

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
						'is_setgroup'	=> $is_setgroup,
						'status'		=>1,//$_data['status'],
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
						
						'scholarship_id'	=>$_data['discount_type'],
						'scholarship_amt'	=>$_data['scholarship_amount'],
						'scholar_fromdate'	=>$_data['scholarship_fromdate'],
						'scholar_todate'	=>$_data['scholarship_todate'],
						);
				
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
				$id = $this->insert($_arr);
				
				if($_data['group']!=-1 AND $_data['group']!='' AND $_data['group']!=0){
					$this->_name='rms_group_detail_student';
					$arr_group_history = array(
							'stu_id'	=> $id,
							'group_id'	=> $_data['group'],
							'date'		=> date("Y-m-d H:i:s"),
							'status'	=> 1,//$_data['status'],
							'is_newstudent'	=>$_data['stu_denttype'],
							'user_id'	=> $this->getUserId(),
							);
					$this->insert($arr_group_history);
					
					$this->_name = 'rms_group';
					$group=array(
							'is_use'	=> 1,
							'is_pass'	=> 2,
					);
					$where=" id=".$_data['group'];
					$this->update($group, $where);
				}
				
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
				//for update depart m
				$sql="SELECT id_start FROM `rms_items` WHERE id=".$_data['degree']." LIMIT 1";
				$id_start = $_db->fetchOne($sql);
					
				$this->_name="rms_items";
				$arr=array(
						'id_start'=>$id_start+1
				);
				$where="id = ".$_data['degree'];
				$this->update($arr, $where);
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
			$is_setgroup=0;
			if(!empty($_data['group']) AND $_data['group']!=-1){
				$is_setgroup=1;
			}

			$part= PUBLIC_PATH.'/images/photo/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$_arr=array(
 					'branch_id'		=>$_data['branch_id'],
					'user_id'		=>$this->getUserId(),
					'stu_khname'	=>$_data['name_kh'],
					'last_name'		=>ucfirst($_data['last_name']),
					'stu_enname'	=>ucfirst($_data['name_en']),
					'sex'			=>$_data['sex'],
					'is_stu_new'	=>$_data['stu_denttype'],
					'nationality'	=>$_data['studen_national'],
					'nation'		=>$_data['nation'],
					'dob'			=>$_data['date_of_birth'],
					'tel'			=>$_data['phone'],
					'pob'			=>$_data['pob'],
					'home_num'		=>$_data['home_note'],
					'street_num'	=>$_data['way_note'],
					'village_name'	=>$_data['village_note'],
					'commune_name'	=>$_data['commun_note'],
					'district_name'	=>$_data['distric_note'],
					'province_id'	=>$_data['student_province'],
					'group_id'		=>$_data['group'],
					'academic_year'	=>$_data['academic_year'],
					'stu_code'		=>$_data['student_id'],
					'degree'		=>$_data['degree'],
					'grade'			=>$_data['grade'],
					'calture'		=>$_data['calture'],
					'session'		=>$_data['session'],
					'room'			=>$_data['room'],
					
					'father_enname'	=>$_data['fa_name_en'],
					'father_dob'	=>$_data['fa_dob'],
					'father_nation'	=>$_data['fa_national'],					
					'father_job'	=>$_data['fa_job'],					
					'father_phone'	=>$_data['fa_phone'],
					
					'mother_enname'	=>$_data['mom_name_en'],
					'mother_dob'	=>$_data['mo_dob'],
					'mother_nation'	=>$_data['mom_nation'],
					'mother_job'	=>$_data['mo_job'],
					'mother_phone'	=>$_data['mon_phone'],
// 					'photo'  			 => $pho_name,
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
					
					'is_setgroup'	=> $is_setgroup,
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
					
					'scholarship_id'	=>$_data['discount_type'],
					'scholarship_amt'	=>$_data['scholarship_amount'],
					'scholar_fromdate'	=>$_data['scholarship_fromdate'],
					'scholar_todate'	=>$_data['scholarship_todate'],
					);
			
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
			
			$where=$this->getAdapter()->quoteInto("stu_id=?", $_data["id"]);
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$this->update($_arr, $where);
			
			if(!empty($_data['old_group_id'])){
				$this->_name='rms_group_detail_student';
				$arr_group_history= array(
						'status'	=>$_data['status'],
						'is_newstudent'	=>$_data['stu_denttype'],
						'group_id'	=>$_data['group'],
						'user_id'	=>$this->getUserId(),
				);
				$where = " stu_id=".$_data["id"]." AND is_pass=0 and type = 1 ";
				$this->update($arr_group_history, $where);
			}else{
				if($_data['group']>0){
					$this->_name='rms_group_detail_student';
					$arr_group_history= array(
							'stu_id'	=>$_data["id"],
							'group_id'	=>$_data['group'],
							'is_newstudent'	=>$_data['stu_denttype'],
							'date'		=>date("Y-m-d"),
							'status'	=>$_data['status'],
							'user_id'	=>$this->getUserId(),
					);
					$this->insert($arr_group_history);
					
					$this->_name = 'rms_group';
					$group=array(
							'is_use'	=>1,
							'is_pass'	=>2,
					);
					$where=" id=".$_data['group'];
					$this->update($group, $where);
				}
			}
			
			$this->_name = 'rms_student_id';
			$arra=array(
					'degree'	=>$_data['degree'],
			);
			
			$where = " stu_id = ".$_data["id"];
			$this->update($arra, $where);
			
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
			$db->commit();//if not errore it do....
		}catch(Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getStudyHishotryById($id){
		$db = $this->getAdapter();
		$sql="SELECT * FROM rms_study_history WHERE stu_id= ".$id;
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission();
		return $db->fetchRow($sql);
	}
	function getAllGrade($grade_id){
		$db = $this->getAdapter();
		$sql = "SELECT major_id As id,major_enname As name FROM rms_major WHERE dept_id=".$grade_id;
		$order=' ORDER BY id DESC';
		return $db->fetchAll($sql.$order);
	}

	function getStudentInfoById($stu_id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `rms_student` WHERE stu_id=$stu_id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getSearchStudent($search){
		$db=$this->getAdapter();
		$sql="SELECT stu_id ,stu_code,stu_enname,stu_khname,sex,degree,grade,academic_year from rms_student 
			WHERE `status`=1 AND is_setgroup = 0 and is_subspend=0 ";
		
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
		$sql="  SELECT COUNT(stu_id)  FROM rms_student WHERE stu_type IN (1,3)";
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
		
		$sql = "select id,CONCAT(from_academic,'-',to_academic,'(',generation,')')as years,(select name_en from rms_view where type=7 and key_code=time) as time  from  rms_tuitionfee  where status=1 $branch_id  ";
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
		$sql="SELECT *,(SELECT province_kh_name FROM rms_province AS p WHERE p.province_id=s.province_id LIMIT 1) AS province_name,
		(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.father_job LIMIT 1) AS fa_job,
		(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.mother_job LIMIT 1) AS mo_job,
		(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.guardian_job LIMIT 1) AS gu_job,
		(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=s.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade_name,
		(SELECT rms_items.title FROM rms_items WHERE rms_items.id=s.degree AND rms_items.type=1 LIMIT 1) AS degree_name,
		(SELECT rms_items.title FROM rms_items WHERE rms_items.id=s.degree AND rms_items.type=1 LIMIT 1) AS degreeTitle,
		(SELECT name_kh FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=s.nationality) AS nationality,
		(SELECT name_kh FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=s.father_nation) AS father_nation,
		(SELECT name_kh FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=s.mother_nation) AS mother_nation,
		(SELECT name_kh FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=s.guardian_nation) AS guardian_nation, 		
		(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee WHERE rms_tuitionfee.id=s.academic_year LIMIT 1) AS academic_year,
			   (SELECT from_academic FROM rms_tuitionfee WHERE rms_tuitionfee.id=s.academic_year LIMIT 1) AS start_year,
			   (SELECT to_academic FROM rms_tuitionfee WHERE rms_tuitionfee.id=s.academic_year LIMIT 1) AS end_year,
			   (SELECT name_en from rms_view where rms_view.type=4 and rms_view.key_code=s.session LIMIT 1)AS session,
			   (SELECT r.room_name FROM `rms_room` AS r WHERE r.room_id = s.`room` LIMIT 1 )AS room_name
		
	 FROM rms_student AS s WHERE stu_id=$id";
		return $db->fetchRow($sql);
	}
}