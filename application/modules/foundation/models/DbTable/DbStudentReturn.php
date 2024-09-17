<?php

class Foundation_Model_DbTable_DbStudentReturn extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_return';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	public function getAllStudentDropReturn($search){
		$_db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		
		$branch = $dbp->getBranchDisplay();
		
		$colunmname='title_en';
		$label="name_en";
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
		}
		
		$sql = "SELECT  sdr.id,
				(SELECT rms_branch.$branch FROM `rms_branch` WHERE rms_branch.br_id = sdr.branchId LIMIT 1) AS branch_name,			
				(SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id=sdr.stuId LIMIT 1) AS stu_code,
				(SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id=sdr.stuId LIMIT 1) AS student_kh,
				(SELECT CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s WHERE s.stu_id=sdr.stuId LIMIT 1) AS student_name,
				(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = sdr.academicYear LIMIT 1) AS academic,
				
				(SELECT rms_items.$colunmname FROM `rms_items` WHERE `id`=sdr.degree AND type=1 LIMIT 1) AS degree,
				(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=sdr.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
				(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=sdr.group LIMIT 1 ) AS group_name,
				
				(SELECT rms_items.$colunmname FROM `rms_items` WHERE `id`=sdr.degreeReturn AND type=1 LIMIT 1) AS degreeReturn,
				(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=sdr.gradeReturn AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeReturn,
				(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=sdr.groupReturn LIMIT 1 ) AS groupReturn,
				sdr.returnDate,
				(SELECT first_name FROM `rms_users` WHERE id=sdr.userId LIMIT 1) AS user_name
			";
		$sql.=$dbp->caseStatusShowImage("sdr.status");
		$sql.=" FROM `rms_student_return` AS sdr WHERE 1 ";
		$where = "";
		$from_date =(empty($search['start_date']))? '1': " sdr.returnDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sdr.returnDate <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
		$order_by=" order by sdr.id DESC";
		
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[] = " REPLACE((SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id=sdr.stuId LIMIT 1),' ','')LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE((SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id=sdr.stuId LIMIT 1),' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE((SELECT CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s WHERE s.stu_id=sdr.stuId LIMIT 1),' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE((SELECT g.group_code FROM `rms_group` AS g WHERE g.id=sdr.group LIMIT 1 ),' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE((SELECT g.group_code FROM `rms_group` AS g WHERE g.id=sdr.groupReturn LIMIT 1 ),' ','') LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.=" AND sdr.branchId = ".$search['branch_id'];
		}
		if(!empty($search['academic_year'])){
			$where.=" AND sdr.academicYear = ".$search['academic_year'];
		}
		if(!empty($search['degree'])){
			$where.=" AND sdr.degree=".$search['degree'];
		}
		if(!empty($search['grade'])){
			$where.=" AND sdr.grade=".$search['grade'];
		}
		
		$where.=$dbp->getAccessPermission('sdr.branchId');
		$where.=$dbp->getDegreePermission('sdr.degree');
		return $_db->fetchAll($sql.$where.$order_by);
	}
	
	public function getStudentDropReturnById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student_return WHERE id =".$id;
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		$sql.=$dbp->getDegreePermission('degree');
		return $db->fetchRow($sql);
	}
	function addStudentDropReturn($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{	
			$_dbgb = new Application_Model_DbTable_DbGlobal();
			$return_type = $_data['return_type'];
			$oldGroupId = $_data['groupId'];
			$newGroupId = $_data['group'];

			$stu_id = $_data['stu_id'];
			$newDegreeId = $_data['degree'];
			$newGradeId = $_data['grade'];
			$feeId = $_data['feeId'];
			
			$academic_year=0;
			$dbGroup = new Foundation_Model_DbTable_DbGroup();
			$group_info = $dbGroup->getGroupById($newGroupId);
			if(!empty($group_info)){
				$academic_year = $group_info['academic_year'];
			}
			$_arr= array(
						'branchId'		=> $_data['branch_id'],
						'dropId'		=>$_data['drop_id'],
						'stuId'			=>$stu_id,
						'group'			=>$oldGroupId,
						'degree'		=>$_data['degreeId'],
						'grade'			=>$_data['gradeId'],
						
						'academicYear'	=> $academic_year,
						'degreeReturn'	=> $newDegreeId,
						'gradeReturn'	=> $newGradeId,
						'groupReturn'	=> $newGroupId,
						'returnType' 	=>$_data['return_type'],
						'returnDate'	=> $_data['return_date'],
						'note'	 		=> $_data['note'],						
						
						'userId'		=> $this->getUserId(),
						'createDate'	=> date('Y-m-d H:i:s'),
						'modifyDate'	=> date('Y-m-d H:i:s'),
				);
				$this->_name="rms_student_return";
				$this->insert($_arr);

				$_arrStuDropRecord=array(
						'isReturn'	=>1,
				);
				$this->_name="rms_student_drop";
				$whereStuDropRecord="id = ".$_data['drop_id'];
				$this->update($_arrStuDropRecord,$whereStuDropRecord);

				if($return_type ==1){
					/*Copy Student to crm */
				
					$stuInfo = $this->getStudentById($stu_id);
					$_arr=array(
						'branch_id'	  	 => $stuInfo['branch_id'],
						'kh_name' 	   	 => $stuInfo['stu_khname'],
						'first_name'   	 => $stuInfo['stu_khname'],
						'last_name'    	 => $stuInfo['last_name'],
						'sex'         	 => $stuInfo['sex'],
						'know_by'		 => $stuInfo['know_by'],
						'tel'            => $stuInfo['tel'],
						'current_address'=> $stuInfo['address'],
						'note'           => 'From Student Return',
						'crm_status'     => 1,
						'create_date'    => date("Y-m-d H:i:s"),
						'modify_date'    => date("Y-m-d H:i:s"),
						'user_id'	     => $this->getUserId()
					);
					$this->_name="rms_crm";
					$crmId =  $this->insert($_arr);

					$stuToken = $_dbgb->getStudentToken();
					$array = array(
							'branch_id'	 	 => $stuInfo['branch_id'],
							'crm_id'	 	 => $crmId,
							'customer_type'	 =>3,
							'stu_khname'	 => $stuInfo['stu_khname'],
							'stu_enname'     => $stuInfo['stu_enname'],
							'last_name'		 => $stuInfo['last_name'],
							'sex'			 => $stuInfo['sex'],
							'tel'		     => $stuInfo['tel'],
							'crm_degree'     => $newDegreeId,
							'crm_grade'      => $newGradeId,
							'age'            => $stuInfo['age'],
							'nationality'    => $stuInfo['nationality'],
							'nation'         => $stuInfo['nation'],
							'dob'            => $stuInfo['dob'],
							'pob'            => $stuInfo['pob'],
							'email'          => $stuInfo['email'],
							'address'        => $stuInfo['address'],
							'home_num'       => $stuInfo['home_num'],
							'street_num'     => $stuInfo['street_num'],
							'village_name'   => $stuInfo['village_name'],
							'commune_name'   => $stuInfo['commune_name'],
							'province_id'    => $stuInfo['province_id'],
							'father_enname'  => $stuInfo['father_enname'],
							'father_khname'  => $stuInfo['father_khname'],
							'father_dob'     => $stuInfo['father_dob'],
							'father_nation'  => $stuInfo['father_nation'],
							'father_job'     => $stuInfo['father_job'],
							'father_phone'   => $stuInfo['father_phone'],
							'mother_khname'  => $stuInfo['mother_khname'],
							'mother_enname'  => $stuInfo['mother_enname'],
							'mother_nation'  => $stuInfo['mother_nation'],
							'mother_phone'   => $stuInfo['mother_phone'],
							'guardian_first_name' => $stuInfo['guardian_first_name'],
							'guardian_enname'=> $stuInfo['guardian_enname'],
							'guardian_khname'=> $stuInfo['guardian_khname'],
							'guardian_dob'   => $stuInfo['guardian_dob'],
							'guardian_nation'=> $stuInfo['guardian_nation'],
							'guardian_tel'   => $stuInfo['guardian_tel'],
							'street'         => $stuInfo['street'],
							'comm_id'        => $stuInfo['comm_id'],
							'vill_id'        => $stuInfo['vill_id'],
							'dis_id'         => $stuInfo['dis_id'],
							'pro_id'         => $stuInfo['pro_id'],
							'photo'          => $stuInfo['photo'],
							'father_photo'   => $stuInfo['father_photo'],
							'mother_photo'   => $stuInfo['mother_photo'],
							'guardian_photo' => $stuInfo['guardian_photo'],
							'from_school'    => $stuInfo['from_school'],
							'know_by'        => $stuInfo['know_by'],
							'studentToken'   =>$stuToken,
							'create_date'    => date("Y-m-d H:i:s"),
							'modify_date'    => date("Y-m-d H:i:s"),
							'user_id'	     => $this->getUserId()
							);
					$this->_name="rms_student";
					$newStuId = $this->insert($array);

					/* insert student to rms_group_detail_student */
					$school_option = $_dbgb->getSchoolOptionbyDegree($newDegreeId);
					$_arr = array(
						'branch_id'			=>$_data['branch_id'],
						'stu_id'			=>$newStuId,
						'is_newstudent'		=>1,
						'status'			=>1,
						'group_id'			=>0,
						'degree'			=>$newDegreeId,
						'grade'				=>$newGradeId,
						'school_option'		=>$school_option,
						'is_current'		=>1,
						'is_setgroup'		=>0,
						'is_maingrade'		=>1,
						'create_date'		=>date("Y-m-d H:i:s"),
						'modify_date'		=>date("Y-m-d H:i:s"),
						'user_id'			=>$this->getUserId(),
						'itemType'			=>1,
						'entryFrom'			=>1,
					);
					$this->_name="rms_group_detail_student";
					$this->insert($_arr);

				}else{
					if($oldGroupId!=$newGroupId){
					
						$_arrOldGroupDetail = array(
								'is_current'		=>0,
								'modify_date'		=>date("Y-m-d H:i:s"),
								'user_id'			=>$this->getUserId(),
						);
						$this->_name="rms_group_detail_student";
						$whereOldGroupDetail=" stu_id = ".$stu_id." AND itemType=1 AND is_current=1 ";
						// if(!empty($oldGroupId)){
						// 	$whereOldGroupDetail.=" AND group_id=$oldGroupId ";
						// }
						$this->update($_arrOldGroupDetail,$whereOldGroupDetail);
						$isSetgroup=0;
						(!empty($newGroupId))?$isSetgroup=1:($isSetgroup=0);
						$_arrNewGroupDetail = array(
								'stu_id'			=>$stu_id,
								'status'			=>1,
								'group_id'			=>$newGroupId,
								'degree'			=>$newDegreeId,
								'grade'				=>$newGradeId,
								'academic_year'		=>$academic_year,
								'is_current'		=>1,
								'feeId'				=>$feeId,
								'is_setgroup'		=>$isSetgroup,
								'is_maingrade'		=>1,
								'note'				=>"New Group Detail From Student Return",
								'create_date'		=>date("Y-m-d H:i:s"),
								'modify_date'		=>date("Y-m-d H:i:s"),
								'user_id'			=>$this->getUserId(),
						);
						$this->_name="rms_group_detail_student";
						$this->insert($_arrNewGroupDetail);
							
					}else{
						$_arrOldGroupDetail = array(
								'stop_type'		=>0,
								'note'			=>"Update StopType Group Detail From Student Return",
								'modify_date'		=>date("Y-m-d H:i:s"),
								'user_id'			=>$this->getUserId(),
						);
						$this->_name="rms_group_detail_student";
						$whereOldGroupDetail="stu_id = ".$stu_id." AND itemType=1 AND is_current=1 ";
						if(!empty($oldGroupId)){
							$whereOldGroupDetail.=" AND group_id=$oldGroupId ";
						}
						$this->update($_arrOldGroupDetail,$whereOldGroupDetail);
					}
				}
			$_db->commit();
			return true;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
			exit();
		}
	}
	
	function updateStudentDropReturn($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{	
		
			$id=$_data['id'];
			$_arr= array(
					'returnDate'		 => $_data['return_date'],
					'note'	 => $_data['note'],						
					'userId'	 => $this->getUserId(),
					'modifyDate'=> date('Y-m-d H:i:s'),
					'status' 	=>$_data['status']
			);
			$this->_name="rms_student_return";
			$whereArr="id =".$id;
			$this->update($_arr,$whereArr);
			
			if($_data['status']==0){
				
				// $record = $this->getStudentDropReturnById($id);
				// $dropId = $record['dropId'];

				$dropId = $_data['drop_id'];
				$_arrStuDropRecord=array(
						'isReturn'	=>0,
				);
				$whereStuDropRecord=$this->getAdapter()->quoteInto("id=?", $dropId);
				$this->_name="rms_student_drop";
				$this->update($_arrStuDropRecord,$whereStuDropRecord);
				
				$oldGroupId = $_data['groupId'];
			
				$stu_id = $_data['stu_id'];
				$newGroupId = $_data['group'];
				
				
				$dbStuDrop= new Foundation_Model_DbTable_DbStudentDrop();
				$rowDrop = $dbStuDrop->getStudentDropById($dropId);
				$stopType = $rowDrop['type'];
				if($oldGroupId!=$newGroupId){
					
					$this->_name = 'rms_group_detail_student';
					$whereDeleteNewStudy="stu_id = ".$stu_id." AND is_current=1 ";
					if(!empty($newGroupId)){
						$whereDeleteNewStudy.=" AND group_id=$newGroupId ";
					}
					$this->delete($whereDeleteNewStudy);
					
					$_arrOldGroupDetail = array(
							'is_current'		=>1,
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
					);
					$this->_name="rms_group_detail_student";
					$whereOldGroupDetail="stu_id = ".$stu_id." AND itemType=1 AND is_current=0 ";
					if(!empty($oldGroupId)){
						$whereOldGroupDetail.=" AND group_id=$oldGroupId ";
					}
					$this->update($_arrOldGroupDetail,$whereOldGroupDetail);
				}else{
					$_arrOldGroupDetail = array(
							'stop_type'		=>$stopType,
							'note'			=>"Update StopType Group Detail From Student Return",
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
					);
					$this->_name="rms_group_detail_student";
					$whereOldGroupDetail="stu_id = ".$stu_id." AND itemType=1 AND is_current=1 ";
					if(!empty($oldGroupId)){
						$whereOldGroupDetail.=" AND group_id=$oldGroupId ";
					}
					$this->update($_arrOldGroupDetail,$whereOldGroupDetail);
				}
			}
		
			$_db->commit();
			return true;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function getAllStudentDrop($_data){
		$db = $this->getAdapter();
		
		$branch_id = empty($_data['branch_id'])?0:$_data['branch_id'];
		$sql="
		SELECT 
			dr.id,
			(SELECT CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,''),'-',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s WHERE s.stu_id=dr.stu_id LIMIT 1) AS `name` 
		FROM `rms_student_drop` AS dr 
		WHERE dr.status=1
			AND dr.isReturn=0
			AND dr.branch_id=$branch_id
		";
		if(!empty($_data['dropIdSelected'])){
			$sql.=" OR dr.id=".$_data['dropIdSelected'];
		}
		return $db->fetchAll($sql);
	}
	
	function getStudentDropInfo($_data){
		$db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label="name_en";
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
		}
		
		$drop_id = empty($_data['drop_id'])?0:$_data['drop_id'];
		$sql="
		SELECT 
			dr.*,
			(SELECT its.$colunmname FROM `rms_items` AS its WHERE its.id=dr.degree AND its.type=1 LIMIT 1) AS degreeTitle,
			(SELECT CONCAT(itsd.$colunmname) FROM `rms_itemsdetail` AS itsd WHERE itsd.id=dr.grade AND itsd.items_type=1 LIMIT 1) AS gradeTitle,
			(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = dr.group LIMIT 1) AS groupCode,
			(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = dr.academic_year LIMIT 1) AS academicYearTitle,
			(SELECT v.$label FROM `rms_view` AS v WHERE v.type=5 AND key_code = dr.type LIMIT 1) AS typeTitle,
			(SELECT CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,''),'-',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s WHERE s.stu_id=dr.stu_id LIMIT 1) AS `name` 
		FROM `rms_student_drop` AS dr 
		WHERE dr.status=1
			AND dr.id=$drop_id 
			LIMIT 1
		";
		return $db->fetchRow($sql);
	}
	function getStudentById($id){
		$db = $this->getAdapter();
		$sql="SELECT * FROM `rms_student` WHERE stu_id = $id ";
		return $db->fetchRow($sql);
	}
}