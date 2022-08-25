<?php

class Foundation_Model_DbTable_DbGroupStudentChangeGroup extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rms_group_student_change_group';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	public function getfromGroup(){
		$db = $this->getAdapter();
		$sql = "SELECT g.id,g.`group_code`,
			    COUNT(stu_id) 
			  FROM
			    `rms_group_detail_student` AS gds,
			    `rms_group` AS g 
			  WHERE  
				gds.mainType=1 
				AND gds.group_id = g.id AND group_code!=''";
			$request=Zend_Controller_Front::getInstance()->getRequest();
			if($request->getActionName()=='add'){
				$sql.=" AND gds.is_pass=2 ";
			}
			$sql.=" GROUP BY gds.group_id ";
		return $db->fetchAll($sql);
	}
	public function gettoGroup(){
		$db = $this->getAdapter();
		$sql = "SELECT group_code,id FROM `rms_group` where status = 1 and is_pass IN (0,2) AND group_code!=''";
		return $db->fetchAll($sql);
	}
	
	public function selectAllStudentChangeGroup($search){
		$_db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label = 'name_en';
		$branch = "branch_nameen";
		$month = "month_en";
		if ($currentLang==1){
			$colunmname='title';
			$label = 'name_kh';
			$branch = "branch_namekh";
			$month = "month_kh";
		}
		$sql = "SELECT 
					gscg.id,
					(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) AS branch_name,
					(select group_code from rms_group where rms_group.id=gscg.from_group LIMIT 1) as group_code,
					(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = (SELECT academic_year FROM rms_group WHERE rms_group.id=gscg.from_group LIMIT 1) LIMIT 1) AS academic,
					(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=(select grade from rms_group where rms_group.id=gscg.from_group)) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as grade,
				
					(select $label from rms_view where rms_view.type=4 and rms_view.key_code=(select session from rms_group where rms_group.id=gscg.from_group) limit 1 ) as session,
				
					(SELECT rms_group.group_code FROM rms_group WHERE rms_group.id=gscg.to_group LIMIT 1) as to_group_code,
					CASE
		   				WHEN  change_type = 3 THEN (SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gscg.academic_year LIMIT 1)
		  				ELSE (SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = (SELECT rms_group.academic_year FROM rms_group WHERE rms_group.id=gscg.to_group LIMIT 1) LIMIT 1)
		   			END  AS to_academic,
		   			CASE
		   				WHEN  change_type = 3 THEN (SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=gscg.grade ) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1)
		  				ELSE (SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=(SELECT rms_group.grade FROM rms_group WHERE rms_group.id=gscg.to_group LIMIT 1) ) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1)
		   			END  AS to_grade,
					(select $label from rms_view where rms_view.type=4 and rms_view.key_code=(SELECT rms_group.session FROM rms_group WHERE rms_group.id=gscg.to_group LIMIT 1)  limit 1) as to_session,
				
					moving_date,
					gscg.note
			";
		$sql.=$dbp->caseStatusShowImage("gscg.status");
		$sql.=" FROM 
					`rms_group_student_change_group` as gscg,
					rms_group as g
				WHERE 
					g.id=gscg.from_group ";
		$order_by=" order by id DESC";
		$where=" ";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " (SELECT group_code from rms_group WHERE rms_group.id=gscg.from_group limit 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT group_code from rms_group WHERE rms_group.id=gscg.to_group limit 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=(select grade from rms_group where rms_group.id=gscg.from_group)) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=g.grade) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.=" AND g.branch_id=".$search['branch_id'];
		}
		if(!empty($search['academic_year'])){
			$where.=" AND g.academic_year=".$search['academic_year'];
		}
		if(!empty($search['degree'])){
			$where.=" AND g.degree=".$search['degree'];
		}
		if(!empty($search['grade'])){
			$where.=" AND g.grade=".$search['grade'];
		}
		if(!empty($search['session'])){
			$where.=" AND g.session=".$search['session'];
		}
		$where.=$dbp->getAccessPermission('g.branch_id');
		return $_db->fetchAll($sql.$where.$order_by);
	}
	public function getAllGroupStudentChangeGroupById($id){
		$db = $this->getAdapter();
		$sql = "SELECT gsc.*,
		(SELECT g.degree FROM rms_group AS g WHERE g.id = gsc.from_group  LIMIT 1) AS from_degree 
		FROM rms_group_student_change_group  AS gsc WHERE gsc.id =".$id;
		return $db->fetchRow($sql);
	}
	
	public function getCondition($data){
		$db=$this->getAdapter();
		$sql="SELECT gsc.* FROM rms_group_student_change_group AS gsc WHERE gsc.from_group=".$data['from_group']." AND gsc.to_group=".$data['to_group'];
		return $db->fetchRow($sql);
	}
	
	public function addGroupStudentChangeGroup($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{	
			
			$_dbFee = new Accounting_Model_DbTable_DbFee();
			$feeId = empty($_data['academic_year'])?0:$_data['academic_year'];
			$rowFeeInfo = $_dbFee->getFeeById($feeId);
			$academicYear = empty($rowFeeInfo['academic_year'])?0:$rowFeeInfo['academic_year'];
			$stopType = 0;
			$isCurrent=0;
			if($_data['change_type']==3){
				$stopType = $_data['change_type'];//ឆ្លងភូមិសិក្សា
				$isCurrent=1;
			}
			$con='';
			if ($stopType!=3){
				$con = $this->getCondition($_data);
			}
			if($con!=''){
				$identity = explode(',', $_data['identity']);
				$array_checkbox=explode(',', $con['array_checkbox']);
				$result = array_merge($array_checkbox,$identity);
				$final_array = implode(",", $result);
				$arra=array(
					'array_checkbox'	=>	$final_array,
						);
				$where = ' from_group='.$_data['from_group'].' and to_group='.$_data['to_group'];
				$this->update($arra, $where);
			}else{
				$_data['to_group'] = empty($_data['to_group'])?0:$_data['to_group'];
				$_arr= array(
					'user_id'		=>$this->getUserId(),
					'branch_id'	=>$_data['branch_id'],
					'from_group'	=>$_data['from_group'],
					'to_group'		=>$_data['to_group'],
					'change_type'	=>$_data['change_type'],
					'moving_date'	=>$_data['moving_date'],
					'note'			=>$_data['note'],
					'status'		=>1,
					'array_checkbox'=>$_data['identity'],
					'fee_id'		=>$feeId,
					'academic_year'	=>$academicYear,
					'degree'		=>$_data['degree'],
					'grade'			=>$_data['grade'],
				);
				$this->_name = "rms_group_student_change_group";
				$id = $this->insert($_arr);
			}
				
			$this->_name='rms_group_detail_student';
			$idsss=explode(',', $_data['identity']);
			
			foreach ($idsss as $k){
				if (!empty($_data['stu_id_'.$k])){
					$stu=array(
						'stop_type'	=>$stopType,
						'is_pass'	=>$_data['change_type'],//corrected
						'is_current'=>$isCurrent,
						'modify_date'=> date("Y-m-d H:i:s")
					);
					$where=" stu_id=".$_data['stu_id_'.$k]." AND group_id=".$_data['from_group'];
					$this->_name='rms_group_detail_student';
					$this->update($stu, $where);
					
					if ($stopType==3){//ឆ្លងភូមិសិក្សា
						$newStuId = '';
						if(!empty($_data['stu_id_'.$k])){
							$newStuId = $this->duplicateStudent($_data['stu_id_'.$k]);
						}
						
						$this->_name = 'rms_student_fee_history';
						$data_gro = array(
							'is_current'=> 0,
						);
						$where = 'student_id = '.$_data['stu_id_'.$k]." AND is_current=1";
						$this->update($data_gro, $where);
						
						if(!empty($feeId)){
							$arr = array(
								'user_id'			=>$this->getUserId(),
								'branch_id'			=>$_data['branch_id'],
								'student_id'		=>$newStuId,//$_data['stu_id_'.$k],
								'status'			=>1,
								'academic_year'		=>$academicYear,
								'fee_id'			=>$feeId,
								'is_current'		=>1,
								'create_date'		=>date("Y-m-d H:i:s"),
								'modify_date'		=>date("Y-m-d H:i:s"),
							);
							$this->_name='rms_student_fee_history';
							$this->insert($arr);
						}
						
						
						$arr=array(
							'stu_id'		=>$newStuId,//$_data['stu_id_'.$k],
							'group_id'		=>0,
							'session'		=>0,
							'degree'		=>$_data['degree'],
							'grade'			=>$_data['grade'],
							'academic_year'	=>$academicYear,
							'user_id'		=>$this->getUserId(),
							'status'		=>1,
							'date'			=>date('Y-m-d'),
							'create_date'	=>date('Y-m-d H:i:s'),
							'modify_date'	=>date('Y-m-d H:i:s'),
							'type'			=>1,
							'old_group'		=>$_data['from_group'],
							'is_setgroup'	=>0,
							'is_current'	=>1,
							'is_maingrade'	=>1,
						);
						$this->_name='rms_group_detail_student';
						$this->insert($arr);
					}
				}
			}
			
			if ($stopType!=3){
				$group_detail = $this->getGroupDetail($_data['to_group']);
				$dbg = new Application_Model_DbTable_DbGlobal();
				$this->_name='rms_group_detail_student';
				$ids=explode(',', $_data['identity']);
				foreach ($ids as $i){
					if (!empty($_data['stu_id_'.$i])){
						$rsOldGroup =$dbg->ifStudentinGroupReady($_data['stu_id_'.$i],$_data['from_group']);
						$is_maingrade = empty($rsOldGroup['is_maingrade'])?0:$rsOldGroup['is_maingrade'];
							
						$rsexist =$dbg->ifStudentinGroupReady($_data['stu_id_'.$i],$_data['to_group']);
						if(empty($rsexist)){
							$arr=array(
									'stu_id'		=>$_data['stu_id_'.$i],
									'group_id'		=>$_data['to_group'],
									'session'		=>$group_detail['session'],
									'degree'		=>$group_detail['degree'],
									'grade'			=>$group_detail['grade'],
									'academic_year'	=>$group_detail['academic_year'],
									'user_id'		=>$this->getUserId(),
									'status'		=>1,
									'date'			=>date('Y-m-d'),
									'create_date'		=>date('Y-m-d H:i:s'),
									'modify_date'		=>date('Y-m-d H:i:s'),
									'type'			=>1,
									'old_group'	=>$_data['from_group'],
									'is_setgroup'		=>1,
									'is_current'		=>1,
									'is_maingrade'		=>$is_maingrade,
							);
							if($_data['change_type']==2){
								$array['is_newstudent']=0;
							}
							$this->insert($arr);
						}
					}
				}
				$this->_name = 'rms_group';
				$group=array(
						'is_use'	=>1,
						'is_pass'	=>2,
				);
				$where=" id=".$_data['to_group'];
				$this->update($group, $where);
			}
			
			$ident = explode(',', $_data['identity']);
			$selected_student = count($ident);
			$all_student = $_data['all_student'];
			
			if($all_student == $selected_student){
				$this->_name = 'rms_group';
				$group=array(
						'is_pass'	=>$_data['change_type'],
						);
				$where=" id=".$_data['from_group'];
				$this->update($group, $where);
			}
			return $_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
		}
	}
	function duplicateStudent($stu_id){
		$db = $this->getAdapter();
		//user_id
		//create_date
		$userId = $this->getUserId();
		$sql="INSERT INTO rms_student(
					branch_id,
					stu_code,
					stu_khname,
					last_name,
					stu_enname,
					sex,
					is_stu_new,
					nationality,
					nation,
					dob,
					tel,
					primary_phone,
					pob,
					home_num,
					street_num,
					village_name,
					commune_name,
					district_name,
					province_id,
					father_enname,
					father_dob,
					father_nation,
					father_job,
					father_phone,
					mother_enname,
					mother_dob,
					mother_nation,
					mother_job,
					mother_phone,
					guardian_enname,
					guardian_dob,
					guardian_nation,
					guardian_job,
					guardian_tel,
					lang_level,
					from_school,
					know_by,
					sponser,
					sponser_phone,
					status,
					remark,
					photo,
					customer_type,
					date_bacc,
					province_bacc,
					center_bacc,
					room_bacc,
					table_bacc,
					grade_bacc,
					score_bacc,
					certificate_bacc,
					
					
					calture,
					father_photo,
					mother_photo,
					guardian_photo,
					create_date,
					is_setstudentid,
					street,
					vill_id,
					comm_id,
					dis_id,
					pro_id,
					audioTitle,
					studentToken,
					is_vaccined,
					is_covidTested,
					dateUpdatedCovidFeature,
					setBy,
					crm_degree,
					crm_grade,
					crm_id,
					email,
					emergency_name,
					emergency_tel,
					father_khname,
					guardian_document,
					guardian_email,
					guardian_first_name,
					guardian_khname,
					is_studenttest,
					
					modify_date,
					mother_khname,
					password,
					relationship_to_student,
					serial,
					
					student_option,
					student_status,
					test_id,
					test_setting_id,
					test_type,
					user_id				
				)
					SELECT
					branch_id,
					stu_code,
					stu_khname,
					last_name,
					stu_enname,
					sex,
					is_stu_new,
					nationality,
					nation,
					dob,
					tel,
					primary_phone,
					pob,
					home_num,
					street_num,
					village_name,
					commune_name,
						district_name,
						province_id,
						father_enname,
						father_dob,
						father_nation,
						father_job,
						father_phone,
						mother_enname,
						mother_dob,
						mother_nation,
						mother_job,
						mother_phone,
						guardian_enname,
						guardian_dob,
						guardian_nation,
						guardian_job,
						guardian_tel,
						lang_level,
						from_school,
						know_by,
						sponser,
						sponser_phone,
						STATUS,
						remark,
						photo,
						customer_type,
						date_bacc,
						province_bacc,
						center_bacc,
						room_bacc,
						table_bacc,
						grade_bacc,
						score_bacc,
						certificate_bacc,
						
						
						calture,
						father_photo,
						mother_photo,
						guardian_photo,
						NOW(),0,street,
						vill_id,
						comm_id,
						dis_id,
						pro_id,
						audioTitle,
						studentToken,
						is_vaccined,
						is_covidTested,
						dateUpdatedCovidFeature,
						setBy,
						crm_degree,
						crm_grade,
						crm_id,
						email,
						emergency_name,
						emergency_tel,
						father_khname,
						guardian_document,
						guardian_email,
						guardian_first_name,
						guardian_khname,
						is_studenttest,
						
						modify_date,
						mother_khname,
						PASSWORD,
						relationship_to_student,
						SERIAL,
						
						student_option,
						student_status,
						test_id,
						test_setting_id,
						test_type,
						$userId
		FROM rms_student WHERE stu_id=$stu_id LIMIT 1";
		 $db->query($sql);
		return $db->lastInsertId();
		
	}
	
	function getGroupDetail($group_id){
		$db = $this->getAdapter();
// 		$sql="select academic_year,grade,session,degree,room_id from rms_group where rms_group.id=".$group_id;
		$sql="select * from rms_group where rms_group.id=".$group_id;
		return $db->fetchRow($sql);
		
	}
	function getAllStudentOldGroup($from_group){
		$db = $this->getAdapter();
		$sql="select gd_id from 
		
			rms_group_detail_student 
			where mainType=1  AND rms_group_detail_student.group_id=".$from_group;
		return $db->fetchAll($sql);
	}
	
	public function updateStudentChangeGroup($_data,$id){
		
		$_db= $this->getAdapter();
 		$_db->beginTransaction();
		try{	
			$_dbFee = new Accounting_Model_DbTable_DbFee();
			$feeId = empty($_data['academic_year'])?0:$_data['academic_year'];
			$rowFeeInfo = $_dbFee->getFeeById($feeId);
			$academicYear = empty($rowFeeInfo['academic_year'])?0:$rowFeeInfo['academic_year'];
			$stopType = 0;
			if ($_data['change_type']==3){
				$stopType = $_data['change_type'];//ឆ្លងភូមិសិក្សា
			}
			
			if($_data['status']==1){
				$_arr=array(
						'user_id'		=>$this->getUserId(),
						'from_group'	=>$_data['from_group'],
						'to_group'		=>$_data['to_group'],
						'change_type'	=>$_data['change_type'],
						'moving_date'	=>$_data['moving_date'],
						'note'			=>$_data['note'],
						'array_checkbox'=>$_data['identity'],
						'status'		=>$_data['status'],
						
						'fee_id'		=>$feeId,
						'academic_year'	=>$academicYear,
						'degree'		=>$_data['degree'],
						'grade'			=>$_data['grade'],
				);
				$where=" id = ".$id;
				$this->update($_arr, $where);
				
				$this->_name='rms_group_detail_student';
				$StudentOldGroup = $this->getAllStudentOldGroup($_data['from_group']);
				if(!empty($StudentOldGroup)){
					foreach($StudentOldGroup as $result){
						$arra=array(
								'stop_type'		=>0,
								'is_pass'		=>0,
								'is_current'	=>1,
								);
						$where=" gd_id=".$result['gd_id'];
						$this->_name='rms_group_detail_student';
						$this->update($arra, $where);
						
						$this->_name = 'rms_student_fee_history';
						$whereStudyFee = 'student_id = '.$result['stu_id']." AND is_current=1 AND academic_year=".$result['academic_year'];
						$this->delete($whereStudyFee);
					}
				}
				$this->_name='rms_group_detail_student';
				$where = "old_group = ".$_data['from_group']." and group_id = ".$_data['old_to_group'];
				if ($stopType==3){
					$where = "old_group = ".$_data['from_group'];
				}
				$this->delete($where);
				
				$group_detail = $this->getGroupDetail($_data['to_group']);
				if(empty($_data['identity'])){
					$_data['identity'] = $_data['old_array_checkbox'];
				}
				
				
				if(!empty($_data['identity'])){
					$idsss=explode(',', $_data['identity']);
					foreach ($idsss as $k){
						if (!empty($_data['stu_id_'.$k])){
							$stu=array(
									'stop_type'		=>$stopType,
									'is_pass'		=>1,
									'is_current'	=>0,
									'modify_date'	=> date("Y-m-d H:i:s")
							);
							$where=" stu_id=".$_data['stu_id_'.$k]." AND group_id=".$_data['from_group'];
							$this->_name='rms_group_detail_student';
							$this->update($stu, $where);
							
							if ($stopType==3){//ឆ្លងភូមិសិក្សា
								$this->_name = 'rms_student_fee_history';
								$data_gro = array(
										'is_current'=> 0,
								);
								
								$where = 'student_id = '.$_data['stu_id_'.$k]." AND is_current=1";
								$this->update($data_gro, $where);
								
								$arr = array(
										'user_id'			=>$this->getUserId(),
										'branch_id'			=>$_data['branch_id'],
										'student_id'		=>$_data['stu_id_'.$k],
										'status'			=>1,
										'academic_year'		=>$academicYear,
										'fee_id'			=>$feeId,
										'is_current'		=>1,
										'create_date'		=>date("Y-m-d H:i:s"),
										'modify_date'		=>date("Y-m-d H:i:s"),
								);
								$this->_name='rms_student_fee_history';
								$feeHistortyId = $this->insert($arr);
								
								$arr=array(
										'stu_id'		=>$_data['stu_id_'.$k],
										'group_id'		=>0,
										'session'		=>0,
										'degree'		=>$_data['degree'],
										'grade'			=>$_data['grade'],
										'academic_year'	=>$academicYear,
								
										'user_id'		=>$this->getUserId(),
										'status'		=>1,
										'date'			=>date('Y-m-d'),
										'create_date'	=>date('Y-m-d H:i:s'),
										'modify_date'	=>date('Y-m-d H:i:s'),
										'type'			=>1,
										'old_group'		=>$_data['from_group'],
										'is_setgroup'	=>1,
										'is_current'	=>1,
										'is_maingrade'	=>1,
								);
								$this->_name='rms_group_detail_student';
								$this->insert($arr);
								
							}
						}
					}
				}
				
				if ($stopType!=3){
					$group_detail = $this->getGroupDetail($_data['to_group']);
					$dbg = new Application_Model_DbTable_DbGlobal();
					$this->_name='rms_group_detail_student';
					$ids=explode(',', $_data['identity']);
					foreach ($ids as $i){
						if (!empty($_data['stu_id_'.$i])){
							$rsOldGroup =$dbg->ifStudentinGroupReady($_data['stu_id_'.$i],$_data['from_group']);
							$is_maingrade = empty($rsOldGroup['is_maingrade'])?0:$rsOldGroup['is_maingrade'];
								
							$rsexist =$dbg->ifStudentinGroupReady($_data['stu_id_'.$i],$_data['to_group']);
							if(empty($rsexist)){
								$arr=array(
										'stu_id'		=>$_data['stu_id_'.$i],
										'group_id'		=>$_data['to_group'],
										'session'		=>$group_detail['session'],
										'degree'		=>$group_detail['degree'],
										'grade'			=>$group_detail['grade'],
										'academic_year'	=>$group_detail['academic_year'],
				
										'user_id'		=>$this->getUserId(),
										'status'		=>1,
										'date'			=>date('Y-m-d'),
										'create_date'		=>date('Y-m-d H:i:s'),
										'modify_date'		=>date('Y-m-d H:i:s'),
										'type'			=>1,
										'old_group'	=>$_data['from_group'],
										'is_setgroup'		=>1,
										'is_current'		=>1,
										'is_maingrade'		=>$is_maingrade,
								);
								if($_data['change_type']==2){
									$array['is_newstudent']=0;
								}
								$this->insert($arr);
							}
						}
					}
					$this->_name = 'rms_group';
					$group=array(
							'is_use'	=>1,
							'is_pass'	=>2,
					);
					$where=" id=".$_data['to_group'];
					$this->update($group, $where);
				}
				
				$this->_name = 'rms_group';
				$group=array(
						'is_use'	=>0,
						'is_pass'	=>2,
					);
				$where=" id=".$_data['old_to_group'];
				$this->update($group, $where);
	
				$ident = explode(',', $_data['identity']);
				$selected_student = count($ident);
				$all_student = $_data['all_student'];
				if($all_student == $selected_student){
					$from_group=array(
						'is_pass' => 1,
					);
				}else{
					$from_group=array(
						'is_pass' => 0,
					);
				}
				$this->_name = 'rms_group';
				$where=" id=".$_data['from_group'];
				$this->update($from_group, $where);
				
				
				
			}else{  //////// status == 0 => deactive    ===> so update all student to old info
				$_arr=array(
						'user_id'=>$this->getUserId(),
						'status'=>$_data['status']
				);
				$where=" id = ".$id;
				$this->update($_arr, $where);
				
			/////////////////////// Update student to study in old_group in group_detail_student  //////////////////////////////////
				$this->_name='rms_group_detail_student';
				$StudentOldGroup = $this->getAllStudentOldGroup($_data['from_group']);
				if(!empty($StudentOldGroup)){
					foreach($StudentOldGroup as $result){
						$arra=array(
								'stop_type'		=>0,
								'is_pass'		=>0,
								'is_current'	=>1,
								'modify_date'	=> date("Y-m-d H:i:s")
						);
						$where=" gd_id=".$result['gd_id'];
						$this->_name='rms_group_detail_student';
						$this->update($arra, $where);
						
						$this->_name = 'rms_student_fee_history';
						$whereStudyFee = 'student_id = '.$result['stu_id']." AND is_current=1 AND academic_year=".$result['academic_year'];
						$this->delete($whereStudyFee);
						
						
						$lasFeeHis=  $this->getLastFeeStudentHistory($result['stu_id']);
						if (!empty($lasFeeHis)){
							$this->_name = 'rms_student_fee_history';
							$data_gro = array(
									'is_current'=> 1,
							);
							$where = 'id = '.$lasFeeHis['id']." AND is_current=0 ";
							$this->update($data_gro, $where);
						}
					}
				}
			//////////////////////// delete record student that added to new group //////////////////////////////////////	
				$this->_name='rms_group_detail_student';
				$where = "old_group = ".$_data['from_group']." and group_id = ".$_data['old_to_group'];
				$this->delete($where);

			//////////////////////// get group_detail_info to update student info back to old group /////////////////////	
				$group_detail = $this->getGroupDetail($_data['from_group']);
				$this->_name = 'rms_group';
				$group=array(
						'is_use'	=>0,
						'is_pass'	=>2,
				);
				$where=" id=".$_data['old_to_group'];
				$this->update($group, $where);
				
				$from_group=array(
					'is_pass' => 0,
				);
				$this->_name = 'rms_group';
				$where=" id=".$_data['from_group'];
				$this->update($from_group, $where);
			}
			return $_db->commit();
			
		}catch(Exception $e){
			$_db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getLastFeeStudentHistory($student_id){
		$db=$this->getAdapter();
		$sql="SELECT sh.* FROM rms_student_fee_history AS sh WHERE sh.student_id=$student_id  ORDER BY sh.id DESC LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getAllStudentFromGroup($from_group){
		$db=$this->getAdapter();
		$sql="SELECT 
				gds.stu_id as stu_id,
				st.stu_enname,
				st.last_name,
				st.stu_khname,
				st.stu_code,
			 	(SELECT name_en FROM rms_view WHERE rms_view.type=2 AND rms_view.key_code=st.sex LIMIT 1) as sex
			FROM rms_group_detail_student as gds,
				rms_student as st 
			WHERE 
				gds.mainType=1 
				AND gds.stop_type = 0 
				AND gds.stu_id=st.stu_id 
				AND gds.group_id=$from_group
				AND gds.is_pass=0 ";
		return $db->fetchAll($sql);
	}
	
	function getAllStudentFromGroupUpdate($from_group){
		$db=$this->getAdapter();
		$sql="select 
					gds.stu_id as stu_id,
					st.stu_enname,
					st.last_name,
					st.stu_khname,
					st.stu_code,
					(select name_en from rms_view where rms_view.type=2 and rms_view.key_code=st.sex) as sex
				from 
					rms_group_detail_student as gds,
					rms_student as st 
				where 
					gds.mainType=1 
					AND gds.stu_id=st.stu_id 
					and gds.group_id=$from_group
			";
		return $db->fetchAll($sql);
	}
	
	function getGroupStudentChangeGroup1ById($id,$type){
		$db = new Application_Model_DbTable_DbGlobal();
		return $db->getStudentGroupInfoById($id);
	}
	
	function getAllYears(){
		$db = $this->getAdapter();
		$sql = "SELECT id,CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') AS years FROM rms_tuitionfee WHERE `status`=1 ";
		$order=' ORDER BY id DESC';
		return $db->fetchAll($sql.$order);
	}
	
	function selectStudentPass($id){
		$db = $this->getAdapter();
		$sql = "SELECT stu_id  FROM rms_group_detail_student as gds WHERE 
			gds.mainType=1 
			AND gds.old_group=$id";
		return $db->fetchAll($sql);
	}

	public function AddNewGroupAjaxold($_data){
		print_r($_data);exit();
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$_arr=array(
					'group_code' 	=> $_data['group_code'],
// 					'room_id' 		=> $_data['room'],
// 					'academic_year' => $_data['academic_year'],
// 					'semester' 		=> $_data['semester'],
// 					'session' 		=> $_data['session'],
// 					'degree' 		=> $_data['degree'],
// 					'grade' 		=> $_data['grade'],
// 					'start_date'	=> $_data['start_date'],
// 					'expired_date'	=> $_data['end_date'],
// 					'date' 			=> date("Y-m-d"),
// 					'status'   		=> $_data['status'],
// 					'note'   		=> $_data['note'],
					'user_id'	 	=> $this->getUserId(),
					'is_use' 		=> 0
			);
			$this->_name='rms_group';
			return $this->insert($_arr);
			return $db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	public function AddNewGroupAjax($data){
		//return  $data;
		$db = $this->getAdapter();
		$_arr=array(
				'group_code' 	=> $data['group_code'],
				'room_id' 		=> $data['room'],
				'academic_year' => $data['academic_year'],
				'semester' 		=> $data['semester'],
				'session' 		=> $data['session_group'],
				'degree' 		=> $data['degree_group'],
				'grade' 		=> $data['grade_group'],
				'start_date'	=> $data['start_date'],
				'expired_date'	=> $data['end_date'],
				'date' 			=> date("Y-m-d"),
				'status'   		=> 1,
				'time'			=> $data['time'],
				'note'   		=> $data['note'],
				'user_id'	 	=> $this->getUserId(),
				'is_use' 		=> 0
		);
		$this->_name='rms_group';
		return $this->insert($_arr);
	}
	
	public function getGroupNewAll(){
		$db=$this->getAdapter();
		$sql="SELECT id,group_code As name FROM `rms_group` WHERE STATUS = 1 AND is_pass IN (0,2) AND group_code!=''";
		return $db->fetchAll($sql);
	}
	
	public function getChangeType(){
		$_db  = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if($lang==1){// khmer
			$label = "name_kh";
		}else{ // English
			$label = "name_en";
		}
		$db=$this->getAdapter();
		$sql="SELECT key_code as id, $label as name from rms_view where type=17 and status=1 ";
		return $db->fetchAll($sql);
	}
	
}

