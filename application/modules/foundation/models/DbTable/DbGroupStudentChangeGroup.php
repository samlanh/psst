<?php

class Foundation_Model_DbTable_DbGroupStudentChangeGroup extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rms_group_student_change_group';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	
	public function getfromGroup(){
		$db = $this->getAdapter();
		$sql = "SELECT g.id,g.`group_code`,
			    COUNT(stu_id) 
			  FROM
			    `rms_group_detail_student` AS gds,
			    `rms_group` AS g 
			  WHERE  gds.type=1 AND gds.group_id = g.id AND g.`degree` IN (1,2,3,4)";
			$request=Zend_Controller_Front::getInstance()->getRequest();
			if($request->getActionName()=='add'){
				$sql.=" AND gds.is_pass=0 ";
			}
			$sql.=" GROUP BY gds.group_id ";
		return $db->fetchAll($sql);
	}
	public function gettoGroup(){
		$db = $this->getAdapter();
		$sql = "SELECT group_code,id FROM `rms_group` where status = 1 and is_pass IN (0,2) ";
		return $db->fetchAll($sql);
	}
	public function selectAllStudentChangeGroup($search){
		$_db = $this->getAdapter();
		$sql = "SELECT rms_group_student_change_group.id,(select group_code from rms_group where rms_group.id=rms_group_student_change_group.from_group) as group_code,
				(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=(select academic_year from rms_group where rms_group.id=rms_group_student_change_group.from_group)) AS academic,
				(select major_enname from rms_major where rms_major.major_id=(select grade from rms_group where rms_group.id=rms_group_student_change_group.from_group) limit 1) as grade,
				(select name_en from rms_view where rms_view.type=4 and rms_view.key_code=(select session from rms_group where rms_group.id=rms_group_student_change_group.from_group) limit 1 ) as session,
				
				group_code as to_group_code,
				(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=rms_group.academic_year) AS to_academic,
				(select major_enname from rms_major where rms_major.major_id=rms_group.grade) as to_grade,
				(select name_en from rms_view where rms_view.type=4 and rms_view.key_code=rms_group.session) as to_session,
				
				moving_date,rms_group_student_change_group.note,`rms_group_student_change_group`.status
				FROM `rms_group_student_change_group`,rms_group where rms_group.id=rms_group_student_change_group.to_group and rms_group.degree IN (1,2,3,4)";
		$order_by=" order by id DESC";
		$where=" ";
		if(empty($search)){
			return $_db->fetchAll($sql.$order_by);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " (select group_code from rms_group where rms_group.id=rms_group_student_change_group.from_group limit 1) LIKE '%{$s_search}%'";
			$s_where[] = " (select group_code from rms_group where rms_group.id=rms_group_student_change_group.to_group limit 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT major_enname FROM rms_major WHERE rms_major.major_id=(select grade from rms_group where rms_group.id=
							rms_group_student_change_group.from_group limit 1)) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT major_enname FROM rms_major WHERE rms_major.major_id=(select grade from rms_group where rms_group.id=
							rms_group_student_change_group.to_group limit 1)) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT name_en FROM rms_view WHERE rms_view.type=4 and key_code=(select session from rms_group where rms_group.id=
							rms_group_student_change_group.to_group limit 1)) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT name_en FROM rms_view WHERE rms_view.type=4 and key_code=(select session from rms_group where rms_group.id=
							rms_group_student_change_group.from_group limit 1)) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		
		if(!empty($search['study_year'])){
			$where.=" AND rms_group.academic_year=".$search['study_year'];
		}
		if(!empty($search['grade_bac'])){
			$where.=" AND rms_group.grade=".$search['grade_bac'];
		}
		if(!empty($search['session'])){
			$where.=" AND rms_group.session=".$search['session'];
		}
		
		return $_db->fetchAll($sql.$where.$order_by);
	}
	public function getAllGroupStudentChangeGroupById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_group_student_change_group WHERE id =".$id;
		return $db->fetchRow($sql);
	}
	
	public function getCondition($data){
		$db=$this->getAdapter();
		$sql="select * from rms_group_student_change_group where rms_group_student_change_group.from_group=".$data['from_group']." and rms_group_student_change_group.to_group=".$data['to_group'];
		return $db->fetchRow($sql);
	}
	
	public function addGroupStudentChangeGroup($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		
			try{	
				$con = $this->getCondition($_data);
				if($con!=''){
					$identity = explode(',', $_data['identity']);
					$array_checkbox=explode(',', $con['array_checkbox']);
					$result = array_merge($array_checkbox,$identity);
					$final_array = implode(",", $result);
					//print_r($final_array);exit();
					
					$arra=array(
						'array_checkbox'	=>	$final_array,
							);
					
					$where = ' from_group='.$_data['from_group'].' and to_group='.$_data['to_group'];
					
					$this->update($arra, $where);
					
				}else{
					$_arr= array(
							'user_id'		=>$this->getUserId(),
							'from_group'	=>$_data['from_group'],
							'to_group'		=>$_data['to_group'],
							'moving_date'	=>$_data['moving_date'],
							'note'			=>$_data['note'],
							'status'		=>$_data['status'],
							'array_checkbox'=>$_data['identity'],
							);
					$id = $this->insert($_arr);
				}
				
				$this->_name='rms_group_detail_student';
					$idsss=explode(',', $_data['identity']);
					foreach ($idsss as $k){
						$stu=array(
								'is_pass'		=>1,
						);
						$where=" stu_id=".$_data['stu_id_'.$k];
						$this->update($stu, $where);
					}
					

				$this->_name = 'rms_student';

					$group_detail = $this->getGroupDetail($_data['to_group']);
					$idss=explode(',', $_data['identity']);
					foreach ($idss as $j){
						
						if($group_detail['degree']==1){
							$stu_type=3;
						}else if($group_detail['degree']== 2 || $group_detail['degree']== 3 || $group_detail['degree']== 4){
							$stu_type=1;
						}else if($group_detail['degree'] > 4){
							$stu_type=2;
						}
						
						$array=array(
								'session'		=>$group_detail['session'],
								'degree'		=>$group_detail['degree'],
								'grade'			=>$group_detail['grade'],
								'academic_year'	=>$group_detail['academic_year'],
								'room'			=>$group_detail['room_id'],
								'stu_type'		=>$stu_type,
								);
						$where = " stu_id=".$_data['stu_id_'.$j];
						$this->update($array, $where);
					}
					
					$this->_name='rms_group_detail_student';
					$ids=explode(',', $_data['identity']);
					foreach ($ids as $i){
						$arr=array(
								'group_id'	=>$_data['to_group'],
								'stu_id'	=>$_data['stu_id_'.$i],
								'user_id'	=>$this->getUserId(),
								'status'	=>1,
								'date'		=>date('Y-m-d'),
								'type'		=>1,
								'old_group'	=>$_data['from_group'],
						);
						$this->insert($arr);
					}
					
				$this->_name = 'rms_group';
					$group=array(
							'is_use'	=>0,
							'is_pass'	=>1,
							);
					$where=" id=".$_data['from_group'];
					$this->update($group, $where);
					
				$this->_name = 'rms_group';
					$group=array(
							'is_use'	=>1,
							'is_pass'	=>0,
					);
					$where=" id=".$_data['to_group'];
					$this->update($group, $where);
				return $_db->commit();
			}catch(Exception $e){
				$_db->rollBack();
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
	}
	
	function getGroupDetail($group_id){
		$db = $this->getAdapter();
		$sql="select academic_year,grade,session,degree,room_id from rms_group where rms_group.id=".$group_id;
		return $db->fetchRow($sql);
		
	}
	function getAllStudentOldGroup($from_group){
		$db = $this->getAdapter();
		$sql="select gd_id from rms_group_detail_student where rms_group_detail_student.group_id=".$from_group;
		return $db->fetchAll($sql);
	}
	
	public function updateStudentChangeGroup($_data,$id){
 		$_db= $this->getAdapter();
 		$_db->beginTransaction();
		try{	
			
			$_arr=array(
						'user_id'=>$this->getUserId(),
						'from_group'=>$_data['from_group'],
						'to_group'=>$_data['to_group'],
						'moving_date'=>$_data['moving_date'],
						'note'=>$_data['note'],
						'array_checkbox'=>$_data['identity'],
						'status'=>$_data['status']
					);
			$where=" id = ".$id;
			$this->update($_arr, $where);
			
			$this->_name='rms_group_detail_student';
			$StudentOldGroup = $this->getAllStudentOldGroup($_data['from_group']);
			if(!empty($StudentOldGroup)){
				foreach($StudentOldGroup as $result){
					$arra=array(
							'is_pass'=>0,
							);
					$where=" gd_id=".$result['gd_id'];
					
					$this->update($arra, $where);
				}
			}
			
			
			$this->_name='rms_group_detail_student';
			$where = "old_group = ".$_data['from_group']." and group_id = ".$_data['old_to_group'];
			$this->delete($where);
			
			$group_detail = $this->getGroupDetail($_data['to_group']);
			if($group_detail['degree']==1){
				$stu_type=3;
			}else if($group_detail['degree']== 2 || $group_detail['degree']== 3 || $group_detail['degree']== 4){
				$stu_type=1;
			}else if($group_detail['degree'] > 4){
				$stu_type=2;
			}
			
			
			
					$idsss=explode(',', $_data['identity']);
					foreach ($idsss as $k){
						if(!empty($_data['checkbox'.$k])){
							
							$this->_name='rms_group_detail_student';
							$is_pass=array(
									'is_pass'	=>1,
							);
							$where = " stu_id=".$_data['stu_id_'.$k];
							$this->update($is_pass, $where);
							
							
							$this->_name='rms_group_detail_student';
							$stu=array(
								'group_id'	=>$_data['to_group'],
								'stu_id'	=>$_data['stu_id_'.$k],
								'user_id'	=>$this->getUserId(),
								'status'	=>1,
								//'date'		=>date('Y-m-d'),
								'type'		=>1,
								'old_group'	=>$_data['from_group'],
							);
							$this->insert($stu);
							
							$this->_name = 'rms_student';
								$array=array(
										'session'		=>$group_detail['session'],
										'degree'		=>$group_detail['degree'],
										'grade'			=>$group_detail['grade'],
										'academic_year'	=>$group_detail['academic_year'],
										'room'			=>$group_detail['room_id'],
										'stu_type'		=>$stu_type,
								);
								$where = " stu_id=".$_data['stu_id_'.$k];
								$this->update($array, $where);
							}
						}

			$this->_name = 'rms_group';
			$group=array(
					'is_use'	=>0,
					'is_pass'	=>2,
					
				);
			$where=" id=".$_data['old_to_group'];
			$this->update($group, $where);

			$this->_name = 'rms_group';
			$group=array(
					'is_use'	=>0,
					'is_pass'	=>1,
				);
			$where=" id=".$_data['from_group'];
			$this->update($group, $where);
							
						
			$this->_name = 'rms_group';
			$group=array(
					'is_use'	=>1,
					'is_pass'	=>0,
					);
			$where=" id=".$_data['to_group'];
			$this->update($group, $where);
							
			return $_db->commit();
		}catch(Exception $e){
			$_db->rollBack();
			echo $e->getMessage();exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function getAllStudentFromGroup($from_group){
		$db=$this->getAdapter();
		$sql="select gds.stu_id as stu_id,st.stu_enname,st.stu_khname,st.stu_code,
			 (select name_en from rms_view where rms_view.type=2 and rms_view.key_code=st.sex) as sex
			 from rms_group_detail_student as gds,rms_student as st where st.is_subspend = 0 and gds.type=1 and is_pass=0 and gds.stu_id=st.stu_id and gds.group_id=$from_group";
		return $db->fetchAll($sql);
	}
	
	function getAllStudentFromGroupUpdate($from_group){
		$db=$this->getAdapter();
		$sql="select gds.stu_id as stu_id,st.stu_enname,st.stu_khname,st.stu_code,
		(select name_en from rms_view where rms_view.type=2 and rms_view.key_code=st.sex) as sex
		from rms_group_detail_student as gds,rms_student as st where st.is_subspend=0 and gds.type=1 and gds.stu_id=st.stu_id and gds.group_id=$from_group";
		return $db->fetchAll($sql);
	}
	
	function getGroupStudentChangeGroup1ById($id,$type){
		$db = $this->getAdapter();
		$sql = "SELECT start_date,expired_date,
		(select CONCAT(from_academic,'-',to_academic,'(',generation,')') from rms_tuitionfee where rms_tuitionfee.id=rms_group.academic_year )AS year ,
		(select major_enname from `rms_major` where `rms_major`.`major_id`=`rms_group`.`grade`)AS grade,
		(select en_name from rms_dept where rms_dept.dept_id=rms_group.degree) as degree,
		(select name_en from `rms_view` where `rms_view`.`type`=4 and `rms_view`.`key_code`=`rms_group`.`session`)AS session
		FROM `rms_group` WHERE  id=$id";
		return $db->fetchRow($sql);
	}
	
	function getAllYears(){
		$db = $this->getAdapter();
		$sql = "SELECT id,CONCAT(from_academic,'-',to_academic,'(',generation,')') AS years FROM rms_tuitionfee WHERE `status`=1 ";
		$order=' ORDER BY id DESC';
		return $db->fetchAll($sql.$order);
	}
	
	function selectStudentPass($id){
		$db = $this->getAdapter();
		$sql = "SELECT stu_id  FROM rms_group_detail_student as gds WHERE gds.old_group=$id";
		//$order=' ORDER BY id DESC';
		return $db->fetchAll($sql);
	}
	
}

