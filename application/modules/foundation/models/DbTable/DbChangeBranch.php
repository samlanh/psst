<?php
class Foundation_Model_DbTable_DbChangeBranch extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_change_group';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}	
	function getAllBranch($br_id=null){
		$db = $this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$branch_id = $dbgb->getAccessPermission('br_id');
		$sql="select br_id as id, CONCAT(branch_nameen) as name from rms_branch WHERE branch_nameen!='' AND  status=1  $branch_id ";
		if (!empty($br_id)){
			$sql.=" AND br_id != $br_id ";
		}
		return $db->fetchAll($sql);
	}
	public function getAllStudentID(){
		$_db = $this->getAdapter();
		$sql = "SELECT st.stu_id as id,st.stu_code FROM `rms_student` as st,rms_group_detail_student as gds where gds.type=1 and gds.is_pass=0 and gds.stu_id=st.stu_id and is_setgroup=1 and st.is_subspend=0 and st.status=1 group by gds.stu_id";
		//$orderby = " ORDER BY stu_code ";
		return $_db->fetchAll($sql);		
	}
	
	public function getAllStudentName(){
		$_db = $this->getAdapter();
		$sql = "SELECT st.stu_id as id,		
		(CASE WHEN st.stu_khname IS NULL THEN st.stu_enname ELSE st.stu_khname END) AS name
		FROM 
			`rms_student` as st,
			rms_group_detail_student as gds
		 WHERE gds.type=1 
		and gds.is_pass=0 
		and gds.stu_id=st.stu_id 
		and is_setgroup=1 
		and st.is_subspend=0 
		and st.status=1 
		group by gds.stu_id";
		//$orderby = " ORDER BY stu_code ";
		return $_db->fetchAll($sql);
	}
	
	public function getAllGroup(){
		$db = $this->getAdapter();
		$sql = "SELECT group_code,id FROM `rms_group` where status = 1 and is_pass IN (0,2) ";
// 		$orderby = " ORDER BY stu_code ";
		return $db->fetchAll($sql);
	}
	
	public function selectAllStudentChangeGroup($search){
		$_db = $this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$sql = "SELECT 
		scg.id,
		(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = scg.branch_id LIMIT 1) AS branch_name,
		(SELECT stu_code FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` limit 1) AS code,
		(SELECT stu_khname FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` limit 1) AS kh_name,
		(SELECT CONCAT(COALESCE(stu_enname,'-'),' ',COALESCE(last_name,'') ) FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` limit 1) AS last_name,
		
		(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=2 and `rms_view`.`key_code`=(SELECT sex FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` limit 1) limit 1)AS sex,
		(SELECT group_code from rms_group where rms_group.id = scg.from_group limit 1)AS from_group,
		(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=(select academic_year from rms_group where rms_group.id = scg.from_group limit 1)) AS from_academic,
		(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=(select grade from rms_group where rms_group.id = scg.from_group LIMIT 1)) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as from_grade,
		(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = (select session from rms_group where rms_group.id = scg.from_group limit 1))) LIMIT 1) AS `from_session`,
		
		(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = scg.to_branch LIMIT 1) AS to_branch_name,
		group_code AS to_group,
		(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=rms_group.academic_year limit 1) AS to_academic,
		(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=rms_group.grade) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as to_grade,
		(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = rms_group.session )) LIMIT 1) AS `to_session`,
			moving_date
		";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("scg.status");
		$sql.=" FROM `rms_student_change_branch` as scg,
			rms_student as st,rms_group where scg.to_group=rms_group.id and scg.stu_id=st.stu_id and st.is_subspend=0 ";
		
		$order_by=" order by id DESC";
		$where=' ';
		$from_date =(empty($search['start_date']))? '1': "scg.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "scg.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(empty($search)){
			return $_db->fetchAll($sql.$order_by);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " (SELECT stu_code FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id`) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT stu_khname FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id`) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT stu_enname FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id`) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT group_code from rms_group where rms_group.id = scg.from_group) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT group_code from rms_group where rms_group.id = scg.to_group) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['study_year'])){
			$where.=" AND rms_group.academic_year like ".$search['study_year'];
		}
		if(!empty($search['grade'])){
			$where.=" AND rms_group.grade=".$search['grade'];
		}
		if(!empty($search['session'])){
			$where.=" AND rms_group.session=".$search['session'];
		}
		if(!empty($search['branch_id'])){
			$where.=" AND scg.branch_id=".$search['branch_id'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('scg.branch_id');
		return $_db->fetchAll($sql.$where.$order_by);
	}
	public function getStudentChangeBranchById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student_change_branch WHERE id =".$id." AND status = 1 ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		return $db->fetchRow($sql);
	}
	public function getDegreeAndGradeToGroup($group){
		$db = $this->getAdapter();
		$sql = "SELECT academic_year,degree,grade,session,room_id FROM rms_group WHERE id =".$group;
		return $db->fetchRow($sql);
	}
	public function addStudentChangeBranch($_data){
			$_db= $this->getAdapter();
			$_db->beginTransaction();
			
			try{	
				$stu_id=$_data['studentid'];
				$_arr= array(
						'branch_id'=>$_data['branch_id'],
						'stu_id'	=>$_data['studentid'],
						'from_group'=>$_data['from_group'],
						
						'to_branch'=>$_data['to_branch'],
						'to_group'	=>$_data['to_group'],
						'moving_date'=>$_data['moving_date'],
						
						'reason'		=>$_data['reason'],
						'note'		=>$_data['note'],
						'user_id'	=>$this->getUserId(),
						'status'	=>1,
						'create_date' => date("Y-m-d H:i:s"),
						'modify_date' => date("Y-m-d H:i:s")
					);
				$this->_name='rms_student_change_branch';
				$id = $this->insert($_arr);
				
				
				if (!empty($_data['to_group'])){
					$this->_name='rms_group_detail_student';
					$arr= array(
							'stu_id'	=>$stu_id,
							'group_id'=>$_data['to_group'],
							'old_group'	=>$_data['from_group'],
							'status'	=>1,
							'date'		=>date('Y-m-d')
					);
					$this->insert($arr);
				}
// 				$this->_name='rms_group_detail_student';
// 				$arr= array(
// 						'group_id'=>$_data['to_group'],
// 						'old_group'	=>$_data['from_group'],
// 				);
// 				$where="stu_id=".$stu_id." and is_pass=0 and group_id=".$_data['from_group'];
// 				$this->update($arr, $where);
				
				$this->_name='rms_group';
				$arra = array(
						'is_pass'	=> 2,
						);
				$where = " id = ".$_data['to_group'];
				$this->update($arra, $where);
				
				
				$this->_name='rms_student';
				$test = $this->getDegreeAndGradeToGroup($_data['to_group']);
				
// 				if($test['degree']==1 || $test['degree']==2){
// 					$stu_type=1;    //  kid - 6
// 				}else if($test['degree']==3){
// 					$stu_type=2;    // 7-12
// 				}else{
// 					$stu_type=3;// eng and other subject
// 				}
				$array = array(
							'branch_id'		=>$_data['to_branch'],
							'academic_year'	=>$test['academic_year'],
							'degree'		=>$test['degree'],
							'grade'			=>$test['grade'],
							'session'		=>$test['session'],
							'room'			=>$test['room_id'],
// 							'stu_type'		=>$stu_type,
							'group_id'		=>$_data['to_group'],
						);
				$where = " stu_id=".$_data['studentid'];
				$this->update($array, $where);
				return $_db->commit();
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				$_db->rollBack();
				
			}
	}
	
	
	function getAllGrade($grade_id){
		$db = $this->getAdapter();
		$sql = "SELECT major_id As id,major_enname As name FROM rms_major WHERE dept_id=".$grade_id;
		$order=' ORDER BY id DESC';
		return $db->fetchAll($sql.$order);
	}
	function getStudentChangeGroup1ById($id){
		$db = new Application_Model_DbTable_DbGlobal();
		return $db->getStudentGroupInfoById($id);
	}
	function getStudentInfoById($stu_id){
		$db = $this->getAdapter();
		$sql = "SELECT 	
		(CASE WHEN st.stu_khname IS NULL THEN st.stu_enname ELSE st.stu_khname END) AS name,
		 st.`sex`,gds.`group_id` FROM `rms_student` AS st,rms_group_detail_student AS gds WHERE gds.is_pass=0 and  st.stu_id=$stu_id AND st.stu_id=gds.stu_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	
	public function revertChangeBranch($id){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			
			$row = $this->getStudentChangeBranchById($id);
			if (!empty($row)){
				
				$_arr= array(
						'user_id'	=>$this->getUserId(),
						'status'	=>0,
						'modify_date' => date("Y-m-d H:i:s")
				);
				$this->_name='rms_student_change_branch';
				$where = " id = ".$id;
				$id = $this->update($_arr, $where);
				
				
				$this->_name='rms_group_detail_student';
				$where="stu_id=".$row['stu_id']." and group_id=".$row['to_group'];
				$this->delete($where);
				
// 				$this->_name='rms_group';
// 				$arra = array(
// 						'is_pass'	=> 2,
// 				);
// 				$where = " id = ".$row['to_group'];
// 				$this->update($arra, $where);

				$this->_name='rms_student';
				$test = $this->getDegreeAndGradeToGroup($row['from_group']);
				$array = array(
						'branch_id'		=>$row['branch_id'],
						'academic_year'	=>$test['academic_year'],
						'degree'		=>$test['degree'],
						'grade'			=>$test['grade'],
						'session'		=>$test['session'],
						'room'			=>$test['room_id'],
						// 							'stu_type'		=>$stu_type,
						'group_id'		=>$row['from_group'],
				);
				$where = " stu_id=".$row['stu_id'];
				$this->update($array, $where);
			}
	
			
	
			
	
	
			
			return $_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
	
		}
	}
}

