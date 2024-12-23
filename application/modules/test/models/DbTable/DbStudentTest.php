<?php
class Test_Model_DbTable_DbStudentTest extends Zend_Db_Table_Abstract
{
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->branch_id;
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
	function addStudentTest($data){
		try{
			$part= PUBLIC_PATH.'/images/photo/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$photo = "";
			$name = $_FILES['photo']['name'];
			if (!empty($data['uploaded'])){
				$photo=$data['uploaded'];
			}else if (!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = "profile_".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['photo']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					$photo = $image_name;
				}
				else
					$string = "Image Upload failed";
			}
			$_dbgb = new Application_Model_DbTable_DbGlobal();
			$newSerial = $_dbgb->getTestStudentId($data['branch_id']);
			
			$serialType = STU_SERIAL_TYPE;
			if ($serialType==2){
				$newSerial = $data['serial'];
			}
			
			$stuToken = $_dbgb->getStudentToken();
			$array = array(
						'branch_id'	=>$data['branch_id'],
						'serial'	=>$newSerial,
						'stu_code'	=>$data['stu_code'],
						'stu_khname'	=>$data['kh_name'],
						'stu_enname'	=>$data['first_name'],
						'last_name'	=>$data['en_name'],
						'sex'		=>$data['sex'],
						'nationality'=>$data['nationality'],
						'nation'=>$data['nation'],
						'dob'		=>$data['dob'],
						'pob'		=>$data['pob'],
						'student_status'	=>$data['student_status'],
						'from_school'=>$data['old_school'],
						'old_grade'	=>$data['old_grade'],
						'student_option'	=>$data['student_option'],
						'home_num'	=>$data['home_num'],
						'street_num'	=>$data['street_num'],
						'village_name'	=>$data['village_name'],
						'commune_name'	=>$data['commune_name'],
						'district_name'	=>$data['district_name'],
						'province_id'	=>$data['province_id'],
						'tel'		=>$data['phone'],
						'email'		=>$data['email'],
						
						'familyId'		=>empty($data['familyId']) ? 0 : $data['familyId'],
						//'guardian_khname'		=>$data['parent_name'],
						//'guardian_tel'		=>$data['parent_tel'],
						
						'photo'				=>$photo,
						'emergency_name'		=>$data['emergency_name'],
						'relationship_to_student'=>$data['relationship_to_student'],
						'emergency_tel'			=>$data['emergency_tel'],
						'user_id'	=>$this->getUserId(),
						'customer_type'	=>4,
						'is_studenttest'	=>1,
						'create_date' => date("Y-m-d H:i:s"),
						'create_date_stu_test' => date("Y-m-d H:i:s"),
						'modify_date' => date("Y-m-d H:i:s"),
						'test_type'=>$data['test_type'],
						'test_setting_id'			=>$data['test_setting_id'],
						'studentToken'=>$stuToken
					);
					$this->_name="rms_student";
					$stutest_id=$this->insert($array);
					if(!empty($data['identity'])){
						$ids = explode(',', $data['identity']);
						foreach ($ids as $i){
							$arr = array(
									'stutest_id'	=>$stutest_id,
									'school_name'	=>$data['school_name'.$i],
									'level'			=>$data['level'.$i],
									'year'			=>$data['year'.$i],
									'major'			=>$data['major'.$i],
									'note'			=>$data['remark_'.$i],
									'creat_date'	=>date("Y-m-d"),
									'status'		=>1,
									'user_id'		=>$this->getUserId(),
							);
							$this->_name='rms_student_testdetail';
							$this->insert($arr);
						}
					}
			return $stutest_id;		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAIL");
		}
 	}
	function updateStudentTest($data){
		$db=$this->getAdapter();
		try{
			$part= PUBLIC_PATH.'/images/photo/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			
			$array = array(
					'branch_id'	=>$data['branch_id'],
					'serial'	=>$data['serial'],
					'stu_code'	=>$data['stu_code'],
					'stu_khname'	=>$data['kh_name'],
					'stu_enname'	=>$data['first_name'],
					'last_name'	=>$data['en_name'],
					'sex'		=>$data['sex'],
					'nationality'=>$data['nationality'],
					'nation'=>$data['nation'],
					'dob'		=>$data['dob'],
					'pob'		=>$data['pob'],
					'student_status'	=>$data['student_status'],
					'from_school'=>$data['old_school'],
					'old_grade'	=>$data['old_grade'],
					'student_option'	=>$data['student_option'],
					'home_num'	=>$data['home_num'],
					'street_num'	=>$data['street_num'],
					'village_name'	=>$data['village_name'],
					'commune_name'	=>$data['commune_name'],
					'district_name'	=>$data['district_name'],
					'province_id'	=>$data['province_id'],
					'tel'		=>$data['phone'],
					'email'		=>$data['email'],
					
					'familyId'		=>empty($data['familyId']) ? 0 : $data['familyId'],
					//'guardian_khname'		=>$data['parent_name'],
					//'guardian_tel'		=>$data['parent_tel'],
					'emergency_name'		=>$data['emergency_name'],
					'relationship_to_student'=>$data['relationship_to_student'],
					'emergency_tel'			=>$data['emergency_tel'],
					'user_id'	=>$this->getUserId(),
					'is_studenttest'	=>1,
					'modify_date' => date("Y-m-d H:i:s"),
					'test_type'=>$data['test_type'],
					'test_setting_id'			=>$data['test_setting_id'],
			);
			
			$photo = "";
			$name = $_FILES['photo']['name'];
			if (!empty($data['uploaded'])){
				$array['photo']=$data['uploaded'];
			}else if (!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = "profile_".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['photo']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					$array['photo']=$image_name;
				}
			}
			$id = $data['id'];
			$where=" stu_id = $id";
			$this->_name='rms_student';
			$this->update($array, $where);
			
			$testresult = $this->getAllTestResult($id);
			if (!empty($testresult)) foreach ($testresult as $result){
				if (!empty($data['default_'.$result['id']])){
					$arr_res = array(
						'is_current'=>1,
					);
				}else{
					$arr_res = array(
						'is_current'=>0,
					);
				}
				$this->_name='rms_student_test_result';
				$whereres=" id=".$result['id'];
				$this->update($arr_res, $whereres);
			}
			
			$sql = "DELETE FROM rms_student_testdetail WHERE stutest_id=".$id;
			$db->query($sql);
			
			if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'stutest_id'	=>$id,
							'school_name'	=>$data['school_name'.$i],
							'level'			=>$data['level'.$i],
							'year'			=>$data['year'.$i],
							'major'			=>$data['major'.$i],
							'note'			=>$data['remark_'.$i],
							'creat_date'	=>date("Y-m-d"),
							'status'		=>1,
							'user_id'		=>$this->getUserId(),
					);
					$this->_name='rms_student_testdetail';
					$this->insert($arr);
				}
			}
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAIL");
		}
	}
	function getStudentTestById($id){
		$db = $this->getAdapter();
		$sql=" SELECT 
				s.* 
				,fam.fatherNameKh AS father_khname 
				,fam.fatherName AS father_enname  
				,fam.fatherNation AS father_nation
				,fam.fatherPhone AS father_phone
				
				,fam.motherNameKh AS mother_khname 
				,fam.motherName AS mother_enname  
				,fam.motherNation AS mother_nation  
				,fam.motherPhone AS mother_phone  
				
				,fam.guardianNameKh AS guardian_khname 
				,fam.guardianName AS guardian_enname 
				,fam.guardianNation AS guardian_nation 
				,fam.guardianPhone AS guardian_tel
				
				FROM rms_student AS s
				LEFT JOIN rms_family AS fam ON fam.id = s.familyId
				WHERE s.stu_id=$id AND s.is_studenttest=1 
			"; //AND customer_type =4
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('s.branch_id');
		$sql.=" LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getSchoolOptionbyStudentId($stuId){
		$db = $this->getAdapter();
		$sql="SELECT school_option FROM `rms_group_detail_student` WHERE itemType=1 AND stu_id=$stuId LIMIT 1";
		return $db->fetchOne($sql);
	}	
	
	function getStudentTestDetail($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_student_testdetail WHERE stutest_id=$id";
		return $db->fetchAll($sql);
	}
	
	function getAllStudentTest($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$print=$tr->translate("PRINT_PROFILE");
		$from_date =(empty($search['start_date']))? '1': " s.create_date_stu_test >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.create_date_stu_test <= '".$search['end_date']." 23:59:59'";
		
		$where = " AND ".$from_date." AND ".$to_date;
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		$label = "name_en";
		if($lang==1){// khmer
			$label = "name_kh";
		}	

		$testCondiction = TEST_CONDICTION;
		$branchLabel = $_db->getBranchDisplay();
		$sql="
			SELECT 
				s.stu_id,";
		if ($testCondiction!=2){
			$sql.="(SELECT b.$branchLabel FROM `rms_branch` AS b  WHERE b.br_id = s.branch_id LIMIT 1) AS branch_name,";
		}
		$sql.="	s.serial,
				s.stu_khname,
				s.last_name,
				s.stu_enname,
				(SELECT $label from rms_view WHERE type=2 and key_code=s.sex LIMIT 1) as sex,";
		if ($testCondiction!=2){
			$sql.="(SELECT $label FROM rms_view WHERE TYPE=21 AND key_code=s.nationality LIMIT 1) AS nationality,";
		}
		$sql.="
				s.tel,
				s.dob,";
		if ($testCondiction!=2){
			$sql.="s.from_school,";
		}
		$sql.="
				fam.guardianNameKh,
				fam.guardianPhone,
				(select count(id) from rms_student_test_result where s.stu_id  = rms_student_test_result.stu_test_id and test_type=1) as result_test_fl,
				(select count(id) from rms_student_test_result where s.stu_id  = rms_student_test_result.stu_test_id and test_type=2) as result_test_gn,
				(SELECT first_name FROM `rms_users` WHERE id=s.user_id LIMIT 1) AS user_name,
				'$print'
			 FROM 
				`rms_student` AS s 
				LEFT JOIN rms_family AS fam ON fam.id = s.familyId
			WHERE s.is_studenttest =1 ";
		if (!empty($search['txtsearch'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['txtsearch'])));
			$s_where[] = " REPLACE(s.serial,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(s.stu_khname,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(s.stu_enname,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(s.last_name,' ','')  LIKE '%{$s_search}%'";
			$s_where[]=	 " REPLACE(CONCAT(s.last_name,s.stu_enname),' ','')  LIKE '%{$s_search}%'";
			$s_where[]=	 " REPLACE(CONCAT(s.stu_enname,s.last_name),' ','')  LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(s.tel,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(fam.guardianNameKh,' ','')  LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE(fam.guardianPhone,' ','')  LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}    
		if(!empty($search['branch_search'])){
			$where .= " AND s.branch_id = ".$search['branch_search'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('s.branch_id');
		
		$order=" ORDER BY s.stu_id desc ";
		
		return $db->fetchAll($sql.$where.$order);
	}	
	
	function getStudentTestProfileById($id){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$session_lang=new Zend_Session_Namespace('lang');
		$lang_id=$session_lang->lang_id;

		$str_village='village_name';
		$str_commune='commune_name';
		$str_district='district_name';
		$str_province='province_en_name';
// 		if($lang_id==1){//for kh
// 			$str_village='village_namekh';
// 			$str_commune='commune_namekh';
// 			$str_district='district_namekh';
// 			$str_province='province_kh_name';
// 		}
		
		$sql=" SELECT 
					*,
					(SELECT name_en FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality,
    			(SELECT name_en FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation,
					CASE    
				WHEN  student_status = 1 THEN '".$tr->translate("SINGLE")."'
				WHEN  student_status = 2 THEN '".$tr->translate("MARRIED")."'
				WHEN  student_status = 3 THEN '".$tr->translate("MONK")."'
				END AS student_statustitle,
				CASE    
				WHEN  sex = 1 THEN '".$tr->translate("MALE")."'
				WHEN  sex = 2 THEN '".$tr->translate("FEMALE")."'
				END AS sex_title,
				(SELECT v.$str_village FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name,
			    (SELECT c.$str_commune FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_name,
			    (SELECT d.$str_district FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_name,
			    (SELECT $str_province from rms_province where rms_province.province_id = s.province_id LIMIT 1) AS province
			    
				FROM 
					rms_student As s
				where 
					stu_id=$id AND is_studenttest=1 ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('s.branch_id');
		return $db->fetchRow($sql);
	}
	
	function createStudentTestFromCrm($data){
		$db=$this->getAdapter();
		try{
			$part= PUBLIC_PATH.'/images/photo/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$photo = "";
			$name = $_FILES['photo']['name'];
			if (!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = "profile_".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['photo']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					$photo = $image_name;
				}
				else
					$string = "Image Upload failed";
			}
			
			$array = array(
					'branch_id'	=>$data['branch_id'],
					'stu_code'	=>$data['stu_code'],
					'kh_name'	=>$data['kh_name'],
					'first_name'	=>$data['first_name'],
					'en_name'	=>$data['en_name'],
					'sex'		=>$data['sex'],
					'nationality'=>$data['nationality'],
					'nation'=>$data['nation'],
					'dob'		=>$data['dob'],
					'pob'		=>$data['pob'],
					'phone'		=>$data['phone'],
					'tel'		=>$data['phone'],
					'email'		=>$data['email'],
					'address'	=>$data['address'],
					'student_status'	=>$data['student_status'],
					'if_employed_where'	=>$data['if_employed_where'],
					'position'			=>$data['position'],
					'parent_name'		=>$data['parent_name'],
					'parent_tel'		=>$data['parent_tel'],
					'photo'				=>$photo,
					'old_school'=>$data['old_school'],
					'old_grade'	=>$data['old_grade'],
					
					'emergency_name'		=>$data['emergency_name'],
					'relationship_to_student'=>$data['relationship_to_student'],
					'emergency_tel'			=>$data['emergency_tel'],
					'emergency_address'		=>$data['emergency_address'],
						
// 					'note'		=>$data['note'],
					'serial'	=>$data['serial'],
					'user_id'	=>$this->getUserId(),
					'is_makestudenttest'	=>1,
					'create_datetest' => date("Y-m-d H:i:s"),
					'modify_datetest' => date("Y-m-d H:i:s")
						
			);
			$id = $data['id'];
			$where="id = $id";
			$this->update($array, $where);
			
			if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'stutest_id'	=>$id,
							'school_name'	=>$data['school_name'.$i],
							'level'			=>$data['level'.$i],
							'year'			=>$data['year'.$i],
							'major'			=>$data['major'.$i],
							'note'			=>$data['remark_'.$i],
							'creat_date'	=>date("Y-m-d"),
							'status'		=>1,
							'user_id'		=>$this->getUserId(),
					);
					$this->_name='rms_student_testdetail';
					$this->insert($arr);
				}
			}
			
			return $id;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function insertTestExam($data,$type=null,$test=null){
		$db=$this->getAdapter();
		$db->beginTransaction();
		try{
			$array = array(
				'stu_test_id'	=> $data['stu_test_id'],
				'test_type'		=> $type,//General English
				'academic_year'	=> $data['academic_year'],
				'study_term'    => $data['term_test'],
				'degree'        => $data['degree'],
				'grade'         => $data['grade'],
				'test_date'		=> $data['test_date'],
				'note'		    => $data['note'],
				'modify_date'   => date("Y-m-d H:i:s"),
				'user_id'	    => $this->getUserId(),
				'resultStatus'	=> $data['resultStatus'],
			);
			if (!empty($data['degree_result']) AND !empty($data['grade_result'])){
				$array['score']=$data['score'];
				$array['comment']=$data['comment'];
				$array['degree_result']=$data['degree_result'];
				$array['grade_result']=$data['grade_result'];
				$array['result_date']=empty($data['result_date'])?date("Y-m-d"):$data['result_date'];
				$array['updated_result']=1;
				$array['is_current']=1;
				$array['result_by']=$this->getUserId();
			}else{
				$array['score']=0;
				$array['degree_result']=empty($data['degree_result']) ?0:$data['degree_result'];
				$array['grade_result']=empty($data['grade_result']) ?0:$data['grade_result'];
				$array['is_current']=1;
				$array['updated_result']=0;
			}
			
			
			$data['test_restult_id']=$test;
			if (!empty($data['id'])){
				if($test!=null){
					$_arr = array(
						'stu_id'			=>$data['stu_test_id'],
						'feeId'				=>$data['fee_id'],
						'is_newstudent'		=>1,
						'status'			=>1,
						'group_id'			=>0,
						'academic_year'		=>$data['academic_year'],
						'degree'			=>$data['degree_result'],
						'grade'				=>$data['grade_result'],
						'is_current'		=>1,
						'is_setgroup'		=>0,
						'is_maingrade'		=>1,
						'create_date'		=>date("Y-m-d H:i:s"),
						'modify_date'		=>date("Y-m-d H:i:s"),
						'user_id'			=>$this->getUserId(),
					);
					$check  = $this->checkStudentInGroupDetail($data);
					if (!empty($check)){

						$_arr['session']=$data['part_time_list'];

						$where = "stu_id=".$data['stu_test_id'];
						$where.=" AND test_restult_id = $test ";
						$this->_name="rms_group_detail_student";
						$this->update($_arr, $where);
					}else{
						$_arr['session']=$data['part_time_list'];
						$_arr['branch_id']=$data['branch_id'];
						$_arr['entryFrom']=6;
						$this->_name="rms_group_detail_student";
						$this->insert($_arr);
					}
				}
				
				$id = $data['id'];
				$where = " id = $id ";
				$this->_name="rms_student_test_result";
				$this->update($array, $where);

			}else{
				
				$array['create_date']=date("Y-m-d H:i:s");
				$this->_name="rms_student_test_result";
				$id = $this->insert($array);
				
				$_arr = array(
						'stu_id'			=>$data['stu_test_id'],
						'feeId'				=>$data['fee_id'],
						'academic_year'		=>$data['academic_year'],
						'is_newstudent'		=>1,
						'status'			=>1,
						'group_id'			=>0,
						'degree'			=>empty($data['degree_result'])?$data['degree']:$data['degree_result'],
						'grade'				=>empty($data['grade_result'])?$data['grade']:$data['grade_result'],
						'is_current'		=>1,
						'is_setgroup'		=>0,
						'is_maingrade'		=>1,
						'create_date'		=>date("Y-m-d H:i:s"),
						'modify_date'		=>date("Y-m-d H:i:s"),
						'user_id'			=>$this->getUserId(),
						'test_restult_id'	=>$id,
						'itemType'			=>1,
				);
				
				$arrCheck=array(
						'stu_test_id'	=>$data['stu_test_id'],
						);
				$check  = $this->checkStudentInGroupDetail($arrCheck);
				if (!empty($check)){

					$_arr['session']=$data['part_time_list'];

					$where = "stu_id=".$data['stu_test_id'];
					//$degreeUp = empty($check['degree'])?$data['degree']:$check['degree'];
					//$where.=" AND degree = ".$degreeUp;
					$where.=" AND itemType = 1";
					$this->_name="rms_group_detail_student";
					$this->update($_arr, $where);
				}else{
					$schoolOption = empty($data['schoolOption'])?1:$check['schoolOption'];
					$_arr['session']    =$data['part_time_list'];
					$_arr['school_option'] =$schoolOption;
					$_arr['branch_id']     =$data['branch_id'];
					$_arr['entryFrom']     =6;
					
					$this->_name="rms_group_detail_student";
					$this->insert($_arr);
				}
			}
			if ($type==1){
				if (!empty($data['score']) AND !empty($data['degree_result']) AND !empty($data['grade_result'])){
					$identitys = explode(',',$data['identity']);
					$detailId="";
					if (!empty($identitys)){
						foreach ($identitys as $i){
							if (empty($detailId)){
								if (!empty($data['detailid'.$i])){
									$detailId = $data['detailid'.$i];
								}
							}else{
								if (!empty($data['detailid'.$i])){
									$detailId= $detailId.",".$data['detailid'.$i];
								}
							}
						}
					}
					$this->_name="rms_result_test_subject";
					$where="test_result_id = ".$id;
					if (!empty($detailId)){
						$where.=" AND id NOT IN ($detailId) ";
					}
					$this->delete($where);
					
					if (!empty($data['identity'])){
						$ids = explode(",", $data['identity']);
						foreach ($ids as $i){
							if (!empty($data['detailid'.$i])){
								$arr = array(
									'test_result_id'	=>$id,
									'subjecttest_id'	=>$data['subjecttest_id_'.$i],
									'score'	=>$data['score_'.$i],
									'comment'	=>$data['comment_'.$i],
									'note'	=>$data['note_'.$i],
								);
								$this->_name="rms_result_test_subject";
								$where1=" id = ".$data['detailid'.$i];
								$this->update($arr, $where1);
							}else{
								$arr = array(
									'test_result_id'	=>$id,
									'subjecttest_id'	=>$data['subjecttest_id_'.$i],
									'score'	=>$data['score_'.$i],
									'comment'	=>$data['comment_'.$i],
									'note'	=>$data['note_'.$i],
								);
								$this->_name="rms_result_test_subject";
								$this->insert($arr);
							}
						}
					}
				}
			}
			$db->commit();
			return $id;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	function checkStudentInGroupDetail($_data){
		$db = $this->getAdapter();
		$stu_id = empty($_data['stu_test_id'])?0:$_data['stu_test_id'];
		$sql=" SELECT sd.* FROM rms_group_detail_student AS sd WHERE sd.stu_id = $stu_id  ";

		if(!empty($_data['test_restult_id'])){
			$sql.=" AND sd.test_restult_id= ".$_data['test_restult_id'];
		}
		$sql.=" AND sd.itemType=1 ";
		$sql.=" LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getAllTestResult($stu_id,$type=null){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if($lang==1){// khmer
			$label = "name_kh";
			$grade = "idd.title";
			$degree = "i.title";
		}else{ // English
			$label = "name_en";
			$grade = "idd.title_en";
			$degree = "i.title_en";
		}
		$sql="SELECT 
			str.*,
			CASE    
				WHEN  str.test_type = 1 THEN '".$tr->translate("KHMER_KNOWLEDGE")."'
				WHEN  str.test_type = 2 THEN '".$tr->translate("ENGLISH")."'
				WHEN  str.test_type = 3 THEN '".$tr->translate("UNIVERSITY")."'
				END AS test_type_title,
			CASE    
				WHEN  str.comment = 1 THEN '".$tr->translate("GOOD")."'
				WHEN  str.comment = 2 THEN '".$tr->translate("GOOD_FAIR")."'
				WHEN  str.comment = 3 THEN '".$tr->translate("FAIR")."'
				WHEN  str.comment = 4 THEN '".$tr->translate("WEAK")."'
				END AS comment_title,";
		
			$sql.="(SELECT tm.note FROM `rms_test_term` AS tm WHERE tm.id=str.study_term) AS study_term,";		
		
		$sql.="
			
			(SELECT $degree FROM `rms_items` AS i WHERE i.id = str.degree AND i.type=1 LIMIT 1) AS degree_title,
			(SELECT $grade FROM `rms_itemsdetail` AS idd WHERE idd.id = str.grade AND idd.items_type=1 LIMIT 1) AS grade_title,
			(SELECT $degree FROM `rms_items` AS i WHERE i.id = str.degree_result AND i.type=1 LIMIT 1) AS degree_result_title,
			(SELECT $grade FROM `rms_itemsdetail` AS idd WHERE idd.id = str.grade_result AND idd.items_type=1 LIMIT 1) AS grade_result_title,
			(SELECT first_name FROM rms_users WHERE rms_users.id = str.result_by LIMIT 1) AS result_by
			,(SELECT ptl.title FROM rms_parttime_list AS ptl WHERE ptl.status=1 AND ptl.id = COALESCE((SELECT gs.`session` FROM `rms_group_detail_student` AS gs WHERE gs.`test_restult_id` = str.`id` LIMIT 1),'0') LIMIT 1 ) AS partTimeTitle
		FROM
			`rms_student_test_result` AS str
		WHERE 
			str.stu_test_id = $stu_id ";
		if (!empty($type)){
			$sql.=" AND str.test_type = $type";
		}
		$sql.=" ORDER BY str.id DESC ";
		return $db->fetchAll($sql);
	}
	function getAllStudentTestResult($search = null){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if($lang==1){// khmer
			$name = "s.stu_khname";
			$grade = "idd.title";
			$degree = "i.title";
		}else{ // English
			$name = "CONCAT(s.last_name ,'  ', s.stu_enname) as engName";
			$grade = "idd.title_en";
			$degree = "i.title_en";
		}
		
		$sql="
			SELECT 
				str.id
				, s.serial
				, $name
				, (SELECT sOpt.title FROM rms_schooloption AS sOpt WHERE sOpt.id = str.test_type LIMIT 1 ) AS test_type_title
				,str.test_date
				,(SELECT tm.note FROM `rms_test_term` AS tm WHERE tm.id=str.study_term) AS study_term
				,(SELECT $degree FROM `rms_items` AS i WHERE i.id = str.degree AND i.type=1 LIMIT 1) AS degree_title
				,(SELECT $grade FROM `rms_itemsdetail` AS idd WHERE idd.id = str.grade AND idd.items_type=1 LIMIT 1) AS grade_title
				,str.result_date
				,str.score
				,(SELECT $degree FROM `rms_items` AS i WHERE i.id = str.degree_result AND i.type=1 LIMIT 1) AS degree_result_title
				,(SELECT $grade FROM `rms_itemsdetail` AS idd WHERE idd.id = str.grade_result AND idd.items_type=1 LIMIT 1) AS grade_result_title
				,(SELECT first_name FROM rms_users WHERE rms_users.id = str.result_by LIMIT 1) AS result_by
			FROM
				`rms_student_test_result` AS str
				INNER JOIN `rms_student` AS s ON str.stu_test_id = s.stu_id 
			WHERE str.is_current = 1
			 ";
			$from_date =(empty($search['start_date']))? '1': " str.result_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " str.result_date <= '".$search['end_date']." 23:59:59'";
		
			$where = " AND ".$from_date." AND ".$to_date;
			 if (!empty($search['advance_search'])){
				$s_where = array();
				$s_search = str_replace(' ', '', addslashes(trim($search['advance_search'])));
				$s_where[] = " REPLACE(s.serial,' ','')  LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(s.stu_khname,' ','')  LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(s.stu_enname,' ','')  LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(s.last_name,' ','')  LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}    
			if(!empty($search['branch_search'])){
					$where .= " AND s.branch_id = ".$search['branch_search'];
			}
			if(!empty($search['type_exam_search'])){
				$where .= " AND str.test_type  = ".$search['type_exam_search'];
			}
			if(!empty($search['degree_search'])){
				$where .= " AND str.degree_result = ".$search['degree_search'];
			}
			$dbp = new Application_Model_DbTable_DbGlobal();
			$where.=$dbp->getAccessPermission('s.branch_id');
			$where.=$dbp->getDegreePermission('str.degree');

			$order =" ORDER BY str.id DESC ";
			return $db->fetchAll($sql.$where.$order);
	}
	
	function getTestResultById($id,$type=null,$stu_id=null){
		$db = $this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$sql="SELECT
		str.*,
		(SELECT sd.session  FROM `rms_group_detail_student` AS sd WHERE sd.stu_id = str.stu_test_id AND sd.test_restult_id=str.id LIMIT 1) AS part_time_id,
		(SELECT sd.feeId  FROM `rms_group_detail_student` AS sd WHERE sd.stu_id = str.stu_test_id AND sd.test_restult_id=str.id LIMIT 1) AS feeId,
		(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = str.degree AND i.type=1 LIMIT 1) AS degree_title,
		(SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = str.grade AND idd.items_type=1 LIMIT 1) AS grade_title,
		(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = str.degree_result AND i.type=1 LIMIT 1) AS degree_result_title,
		(SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = str.grade_result AND idd.items_type=1 LIMIT 1) AS grade_result_title
		FROM
			`rms_student_test_result` AS str
		WHERE
		str.id = $id ";
		if (!empty($type)){
			$sql.=" AND test_type=$type ";
		}
		if (!empty($stu_id)){
			$sql.=" AND str.stu_test_id=$stu_id ";
		}
		$sql.=" LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getSubjectScoreByTest($test_id){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$session_lang=new Zend_Session_Namespace('lang');
		$lang = $session_lang->lang_id;
		
		if($lang==1){// khmer
			$label = "name_kh";
		}else{ // English
			$label = "name_en";
		}
		
		$sql="SELECT *,
				(SELECT $label AS view_name FROM rms_view WHERE `key_code`=r.subjecttest_id AND type=31 LIMIT 1) AS subject,
				CASE    
				WHEN  r.comment = 1 THEN '".$tr->translate("GOOD")."'
				WHEN  r.comment = 2 THEN '".$tr->translate("GOOD_FAIR")."'
				WHEN  r.comment = 3 THEN '".$tr->translate("FAIR")."'
				WHEN  r.comment = 4 THEN '".$tr->translate("WEAK")."'
				END AS comment_title
		FROM `rms_result_test_subject` AS r WHERE r.test_result_id=$test_id";
		return $db->fetchAll($sql);
	}

	function getTestPeriod(){
		$db = $this->getAdapter();
		$sql =" SELECT keyValue FROM `rms_setting` WHERE keyname = 'test_period' ";
		return $db->fetchRow($sql);

	}

	function getRowTestResultDate($stu_id,$type=null){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="SELECT 
			str.*
			FROM
			`rms_student_test_result` AS str
			WHERE 
			str.stu_test_id = $stu_id ";
		if (!empty($type)){
			$sql.=" AND str.test_type = $type";
		}
		$sql.=" ORDER BY result_date DESC LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	
}