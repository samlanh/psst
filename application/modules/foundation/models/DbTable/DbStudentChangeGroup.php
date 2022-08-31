<?php
class Foundation_Model_DbTable_DbStudentChangeGroup extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_change_group';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}	
	public function getAllStudentID(){
		$_db = $this->getAdapter();
		$sql = "SELECT st.stu_id as id,st.stu_code FROM `rms_student` as st,rms_group_detail_student as gds where gds.is_pass=0 and gds.stu_id=st.stu_id and st.status=1 group by gds.stu_id";
		return $_db->fetchAll($sql);		
	}
	
	public function getAllStudentName(){
		$_db = $this->getAdapter();
		$sql = "SELECT st.stu_id as id,		
		(CASE WHEN st.stu_khname IS NULL THEN st.stu_enname ELSE st.stu_khname END) AS name
		FROM 
			`rms_student` as st,
			rms_group_detail_student as gds
		 WHERE  gds.is_pass=0 
		and gds.stu_id=st.stu_id 
		and gds.is_setgroup=1 
		
		and st.status=1 
		group by gds.stu_id";
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
		
		$sql = "
		SELECT scg.id,
			(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = scg.branch_id LIMIT 1) AS branch_name,
			(SELECT stu_code FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` limit 1) AS code,
			(SELECT stu_khname FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` limit 1) AS kh_name,
			(SELECT last_name FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` limit 1) AS last_name,
			(SELECT stu_enname FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` limit 1) AS en_name,
			(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=2 and `rms_view`.`key_code`=(SELECT sex FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id` limit 1) limit 1)AS sex,
			
			(SELECT group_code from rms_group where rms_group.id = scg.from_group limit 1)AS from_group,
			(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = (select academic_year from rms_group where rms_group.id = scg.from_group limit 1) LIMIT 1) AS from_academic,
			(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=(select grade from rms_group where rms_group.id = scg.from_group LIMIT 1)) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as from_grade,
			(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = (select session from rms_group where rms_group.id = scg.from_group limit 1))) LIMIT 1) AS `from_session`,
			
			g.group_code AS to_group,
			(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS to_academic,
			(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=g.grade) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as to_grade,
			(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = g.session )) LIMIT 1) AS `to_session`,
			moving_date,scg.note 
		
			
		";
		$sql.=$dbgb->caseStatusShowImage("scg.status");
		$sql.=" FROM 
			`rms_student_change_group` AS scg,
			rms_student AS st,
			rms_group_detail_student AS gds,
			rms_group AS g
		WHERE 
			scg.study_id=gds.gd_id
			AND scg.to_group=g.id 
			AND scg.stu_id=st.stu_id 
			AND gds.stop_type=0
		";
		$order_by=" order by id DESC";
		$where=' ';
		
		if(empty($search)){
			return $_db->fetchAll($sql.$order_by);
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " (SELECT stu_code FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id`) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT stu_khname FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id`) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT stu_enname FROM `rms_student` WHERE `rms_student`.`stu_id`=`scg`.`stu_id`) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT group_code from rms_group where g.id = scg.from_group) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT group_code from rms_group where g.id = scg.to_group) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['academic_year'])){
			$where.=" AND g.academic_year like ".$search['academic_year'];
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
		if(!empty($search['branch_id'])){
			$where.=" AND scg.branch_id=".$search['branch_id'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('scg.branch_id');
		return $_db->fetchAll($sql.$where.$order_by);
	}
	public function getAllStudentChangeGroupById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student_change_group WHERE id =".$id;
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		
		return $db->fetchRow($sql);
	}
	public function getDegreeAndGradeToGroup($group){
		$db = $this->getAdapter();
		$sql = "SELECT academic_year,degree,grade,session,room_id FROM rms_group WHERE id =".$group;
		return $db->fetchRow($sql);
	}
	public function addStudentChangeGroup($_data){
			$_db= $this->getAdapter();
			$_db->beginTransaction();
			try{	
				$dbstu = new Foundation_Model_DbTable_DbStudent();
				$stuInfo = $dbstu->getStudentStudyInfo($_data['studentid']);
				if (!empty($stuInfo)){
					$stu_id = $stuInfo['stu_id'];
					$_arr= array(
							'branch_id'=>$_data['branch_id'],
							'study_id'	=>$_data['studentid'],
							'stu_id'	=>$stu_id,
							'from_group'=>$_data['from_group'],
							'to_group'	=>$_data['to_group'],
							'moving_date'=>$_data['moving_date'],
							'note'		=>$_data['note'],
							'user_id'	=>$this->getUserId(),
							'status'	=>1,
							'create_date'=> date('Y-m-d H:i:s'),
							'modify_date'=> date('Y-m-d H:i:s'),
					);
					$this->_name='rms_student_change_group';
					$id = $this->insert($_arr);
					
					$this->_name='rms_group_detail_student';
					$arr= array(
							'group_id'=>$_data['to_group'],
							'old_group'	=>$_data['from_group'],
					);
					$where="gd_id=".$_data['studentid'];
					$this->update($arr, $where);
					
					$_db->commit();
					return true;
				}
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				$_db->rollBack();
			}
	}
	public function updateStudentChangeGroup($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{	
			if($_data['status']==1){
				$dbstu = new Foundation_Model_DbTable_DbStudent();
				$stuInfo = $dbstu->getStudentStudyInfo($_data['studentid']);
				if (!empty($stuInfo)){
					$stu_id = $stuInfo['stu_id'];
					$_arr=array(
							'branch_id'=>$_data['branch_id'],
							'user_id'	=>$this->getUserId(),
							'from_group'=>$_data['from_group'],
							'to_group'	=>$_data['to_group'],
							'moving_date'=>$_data['moving_date'],
							'note'		=>$_data['note'],
							'status'	=>$_data['status'],
							'modify_date'=> date('Y-m-d H:i:s'),
					);
					$where=" id = ".$_data['id'];
					$this->update($_arr, $where);
					
					$this->_name='rms_group_detail_student';
					$arr= array(
							'group_id'	=>$_data['to_group'],
							'old_group'	=>$_data['from_group'],
					);
					$where="gd_id=".$_data['studentid'];
					$this->update($arr, $where);
				}
			}else{
				$_arr=array(
						'user_id'	=>$this->getUserId(),
						'status'	=>$_data['status'],
				);
				$where=" id = ".$_data['id'];
				$this->update($_arr, $where);
				
				
				// update back to old group
				$this->_name='rms_group_detail_student';
				$arr= array(
						'group_id'	=>$_data['from_group'],
						'old_group'	=>null,
				);
				$where="gd_id=".$_data['studentid'];
				$this->update($arr, $where);
			}
			return $_db->commit();
			
		}catch(Exception $e){
			$_db->rollBack();
			echo $e->getMessage();
		}
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
}

