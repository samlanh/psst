<?php

class Foundation_Model_DbTable_DbGraduatedStudent extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rms_graduated_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	}
	
	public function getfromGroup(){
		$db = $this->getAdapter();
		$sql = "SELECT group_code,id FROM `rms_group` where status = 1 and  group_code!=''";
		return $db->fetchAll($sql);
	}
	public function gettoGroup(){
		$db = $this->getAdapter();
		$sql = "SELECT group_code,id FROM `rms_group` where status = 1 and is_pass IN (0,2) AND group_code!=''";
		return $db->fetchAll($sql);
	}
	
	public function getAllStudentGraduated($search){
		$_db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql = "SELECT 
					gs.id,
					(SELECT branch_namekh FROM `rms_branch` WHERE br_id=g.branch_id LIMIT 1) AS branch_name,
					g.group_code,
					(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=g.academic_year limit 1) AS academic,
					(SELECT rms_itemsdetail.title from rms_itemsdetail where rms_itemsdetail.`id`=g.grade AND rms_itemsdetail.items_type=1 limit 1) as grade,
					(SELECT name_en from rms_view where rms_view.type=4 and rms_view.key_code = g.session limit 1 ) as session,
					(SELECT name_en from rms_view where type=5 and key_code = gs.type LIMIT 1) as type,
					gs.note,
					gs.create_date,
					(select first_name from rms_users where id = gs.user_id) as user
				 ";
		$sql.=$dbp->caseStatusShowImage("gs.status");
		$sql.=" FROM 
					rms_graduated_student as gs,
					rms_group as g
				WHERE 
					g.id=gs.group_id ";
		$order_by = " order by id DESC";
		$where=" ";
		$where.=$dbp->getAccessPermission('g.branch_id');
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
		if(!empty($search['branch_id'])){
			$where.=" AND g.branch_id=".$search['branch_id'];
		}
		if(!empty($search['study_year'])){
			$where.=" AND g.academic_year=".$search['study_year'];
		}
		if(!empty($search['grade'])){
			$where.=" AND g.grade=".$search['grade'];
		}
		if(!empty($search['session'])){
			$where.=" AND g.session=".$search['session'];
		}
		if(!empty($search['group'])){
			$where.=" AND g.id=".$search['group'];
		}
		return $_db->fetchAll($sql.$where.$order_by);
	}
	public function getAllDropById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_graduated_student WHERE id =".$id;
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		return $db->fetchRow($sql);
	}
	public function getCondition($data){
		$db=$this->getAdapter();
		$sql="select * from rms_group_student_change_group where rms_group_student_change_group.from_group=".$data['from_group']." and rms_group_student_change_group.to_group=".$data['to_group'];
		return $db->fetchRow($sql);
	}
	public function addGraduatedStudent($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{	
			$identity = empty($_data['selector'])?null:implode(',', $_data['selector']);
			$_arr= array(
				'user_id'		=>$this->getUserId(),
				'branch_id'		=>$_data['branch_id'],
				'group_id'		=>$_data['from_group'],
				'type'			=>$_data['type'],
				'note'			=>$_data['note'],
				'status'		=>1,
				'array_checkbox'=>$identity,
				'create_date'	=>date("Y-m-d"),
			);
			$id = $this->insert($_arr);
				
			if (!empty($_data['selector'])){
				foreach ($_data['selector'] as $rs){
					$stu=array(
						'stop_type'		=>3,// graduated
					);
					$where=" stu_id=".$rs;
					$this->_name='rms_group_detail_student';
					$this->update($stu, $where);
					
					$array=array(
						'is_subspend'=>3,
					);
					$where = " stu_id=".$rs;
					$this->_name = 'rms_student';
					$this->update($array, $where);
				}
			}
			
			$this->_name = 'rms_group';
			$group=array(
				'is_use'	=>1,//used
				'is_pass'	=>1,//finish
			);
			$where=" id=".$_data['from_group'];
			$this->update($group, $where);
			return $_db->commit();
		}catch(Exception $e){
			$_db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}	
	function getGroupDetail($group_id){
		$db = $this->getAdapter();
		$sql="select academic_year,grade,session,degree,room_id from rms_group where rms_group.id=".$group_id;
		return $db->fetchRow($sql);
	}
	function getAllStudentOldGroup($from_group){
		$db = $this->getAdapter();
		$sql="select gd_id,stu_id from rms_group_detail_student where group_id=$from_group and is_pass=0  ";
		return $db->fetchAll($sql);
	}	
	public function updateGraduateStudent($_data){
		$_db= $this->getAdapter();
 		$_db->beginTransaction();
		try{	
			$id = $_data['id'];
			/////////////////////// Update student to study in old_group in group_detail_student  //////////////////////////////////
			$this->_name='rms_group_detail_student';
			$StudentOldGroup = $this->getAllStudentOldGroup($_data['from_group']);
			if(!empty($StudentOldGroup)){
				foreach($StudentOldGroup as $result){
					$arra=array(
						'stop_type'		=>0,// active
					);
					$where=" gd_id=".$result['gd_id'];
					$this->update($arra, $where);
				}
			}
			$this->_name='rms_student';
			if(!empty($StudentOldGroup)){
				foreach($StudentOldGroup as $result){
					$arra=array(
							'is_subspend'=>0,
					);
					$where=" stu_id = ".$result['stu_id'];
					$this->update($arra, $where);
				}
			}
			$this->_name = 'rms_group';
			$group=array(
					'is_use'	=>1, // true
					'is_pass'	=>2, // studying
			);
			$where=" id=".$_data['from_group'];
			$this->update($group, $where);
			
			if($_data['status']==1){
				$identity = empty($_data['selector'])?null:implode(',', $_data['selector']);
				$_arr=array(
						'user_id'		=>$this->getUserId(),
						'branch_id'		=>$_data['branch_id'],
						'group_id'		=>$_data['from_group'],
						'type'			=>$_data['type'],
						'note'			=>$_data['note'],
						'status'		=>$_data['status'],
						'array_checkbox'=>$identity,
				);
				$this->_name='rms_graduated_student';
				$where=" id = ".$_data['id'];
				$this->update($_arr, $where);				
				
				
				if (!empty($_data['selector'])){
					foreach ($_data['selector'] as $rs){
						$stu=array(
								'stop_type'	=> 3,// graduated
						);
						$where=" stu_id=".$rs;
						$this->_name='rms_group_detail_student';
						$this->update($stu, $where);
				
						$array=array(
								'is_subspend'=>3,
						);
						$where = " stu_id=".$rs;
						$this->_name = 'rms_student';
						$this->update($array, $where);
					}
				}
				$this->_name = 'rms_group';
				$group=array(
						'is_use'	=>1,
						'is_pass'	=>2,
					);
				$where=" id=".$_data['from_group'];
				$this->update($group, $where);
								
			}else{  //////// status == 0 => deactive    ===> so update all student to old info
				$_arr=array(
						'user_id'=>$this->getUserId(),
						'status'=>$_data['status']
				);
				$where=" id = ".$id;
				$this->_name = 'rms_graduated_student';
				$this->update($_arr, $where);
			}			
			return $_db->commit();
		}catch(Exception $e){
			$_db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}	
	function getAllStudentFromGroup($from_group){
		$db=$this->getAdapter();
		$sql="SELECT 
					gds.stu_id as stu_id,
					st.stu_enname,
					st.last_name,
					st.stu_khname,
					st.stu_code,
			 		(select name_en from rms_view where rms_view.type=2 and rms_view.key_code=st.sex) as sex
			 FROM 
					rms_group_detail_student as gds,
					rms_student as st 
			 WHERE 
					gds.stu_id=st.stu_id 
					and st.is_subspend = 0 
					and gds.type=1 
					and is_pass=0
					and gds.group_id=$from_group ";
		return $db->fetchAll($sql);
	}
	function getAllStudentFromGroupUpdate($from_group){
		$db=$this->getAdapter();
		$sql="select 
					gds.stu_id as stu_id,
					st.stu_enname,
					st.stu_khname,
					st.stu_code,
			 		(select name_en from rms_view where rms_view.type=2 and rms_view.key_code=st.sex) as sex
			 from 
					rms_group_detail_student as gds,
					rms_student as st 
			 where 
					gds.stu_id=st.stu_id 
					and gds.is_pass=0
					and gds.group_id=$from_group
			";
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
		$sql = "SELECT stu_id  FROM rms_group_detail_student as gds WHERE gds.group_id=$id and gds.is_pass=0 and gds.type=2 and gds.stop_type=2";
		return $db->fetchAll($sql);
	}
	
	function getGradeByDegree($degree){
		$db = $this->getAdapter();
		$sql = "SELECT major_id as id,major_enname as name FROM rms_major WHERE dept_id = $degree";
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
// 					'amount_month' 	=> $_data['amountmonth'],
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
				'amount_month' 	=> $data['amountmonth'],
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
	
	public function getDropType(){
		$db=$this->getAdapter();
		$sql="SELECT key_code as id, name_kh as name from rms_view where type=5 and status=1 ";
		return $db->fetchAll($sql);
	}
	
}

