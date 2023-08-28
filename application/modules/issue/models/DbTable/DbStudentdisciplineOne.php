<?php

class Issue_Model_DbTable_DbStudentdisciplineOne extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_attendence';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    
    function getAllDiscipline($search=null){
    	$db=$this->getAdapter();
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$currentLang = $dbp->currentlang();
    	$colunmname='title_en';
    	$label="name_en";
    	if ($currentLang==1){
    		$colunmname='title';
    		$label="name_kh";
    	}
    	
    	$sql="SELECT 
    				sad.`id`,
			    	(SELECT branch_nameen FROM `rms_branch` WHERE rms_branch.br_id = sa.branch_id LIMIT 1) AS branch_name,
			    	(SELECT stu_code from rms_student as s where s.stu_id = sad.stu_id limit 1) as stu_code,
			    	(SELECT stu_khname from rms_student as s where s.stu_id = sad.stu_id limit 1) as stu_name,
			    	g.group_code AS group_name,
			    	(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS academic_year,
			    	(SELECT rms_items.$colunmname FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
			    	(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 ) AS grade,
			    	(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS room,
			    	(SELECT`rms_view`.`name_kh`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS session,
	    			sa.`date_attendence`,
	    			(SELECT $label from rms_view as v where v.type=1 and key_code = sa.status LIMIT 1) as status
    			FROM 
    				`rms_student_attendence` AS sa,
    				rms_student_attendence_detail as sad,
    				rms_group as g 
    			WHERE 
    				sa.id = sad.attendence_id
    				AND sad.type = 2
    				AND sa.type = 2	
    				AND g.id=sa.group_id ";
    	$where = ' ';
    	$from_date =(empty($search['start_date']))? '1': " sa.date_attendence >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sa.date_attendence <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['branch_id'])){
    		$where.= " AND sa.`branch_id` =".$search['branch_id'];
    	}
    	if(!empty($search['group_name'])){
    		$where.= " AND sa.`group_id` =".$search['group_name'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND g.academic_year =".$search['study_year'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND g.grade =".$search['grade'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session` =".$search['session'];
    	}
    	if(!empty($search['room'])){
    		$where.=" AND `g`.`room_id` =".$search['room'];
    	}
    	$where.=$dbp->getAccessPermission('sa.`branch_id`');
    	$order=" ORDER BY id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
	public function addDisciplineOne($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		
		$branch = $_data['branch_id'];
		$group = $_data['group'];
		$date = $_data['discipline_date'];
		$for_semester = $_data['for_semester'];
		$sql="select id from rms_student_attendence where branch_id = $branch and group_id = $group and for_semester = $for_semester and date_attendence = '$date' and type=2 limit 1";
		$id = $db->fetchOne($sql);
		
		try{
			if(empty($id)){
				$_arr = array(
						'branch_id'		=>$_data['branch_id'],
						'group_id'		=>$_data['group'],
						'date_attendence'=>date("Y-m-d",strtotime($_data['discipline_date'])),
						'date_create'	=>date("Y-m-d"),
						'modify_date'	=>date("Y-m-d"),			
						'for_semester'	=>$_data['for_semester'],
						'note'			=>$_data['note'],
						'status'		=>1,
						'user_id'		=>$this->getUserId(),
						'type'			=>2, // mistake
				);
				$id=$this->insert($_arr);
			}
			
			$dbpush = new  Application_Model_DbTable_DbGlobal();
		//	$dbpush->getTokenUser($_data['stu_code'],null, 3);
			$arr = array(
					'attendence_id'	=>$id,
					'stu_id'		=>$_data['stu_code'],
					'attendence_status'=>$_data['mistake'],
					'description'	=>$_data['comment'],
					'type'			=>2, //from one student
			);
			$this->_name ='rms_student_attendence_detail';
			$this->insert($arr);
			
		  	$db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
   }
   public function updateStudentAttendenceOne($_data,$id){
		
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$_arr = array(
					'branch_id'		=>$_data['branch_id'],
					'group_id'		=>$_data['group'],
					'date_attendence'=>date("Y-m-d",strtotime($_data['discipline_date'])),
					'modify_date'	=>date("Y-m-d"),
					'for_semester'	=> $_data['for_semester'],
					'note'			=>$_data['note'],
					//'status'		=>$_data['status'],
					'user_id'		=>$this->getUserId(),
					'type'			=>2, //for displince
			);
			$where="id=".$_data['att_id'];
			$db->getProfiler()->setEnabled(true);
			$this->update($_arr, $where);
			
			$this->_name ='rms_student_attendence_detail';
			if ($_data['status']==1){
				$arr = array(
						'attendence_id'	=>$_data['att_id'],
						'stu_id'		=>$_data['stu_code'],
						'attendence_status'=>$_data['mistake'],
						'description'	=>$_data['comment'],
				);
				$where = " id = $id ";
   				$this->update($arr, $where);
			}else{
				$where = " id = $id ";
				$this->delete($where);
			}
		  	$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
   }
	function getAttendencetByIDDiscipline($id){
		$db=$this->getAdapter();
		$sql="SELECT 
					sad.*,
					sa.*
				FROM 
					`rms_student_attendence` sa ,
					rms_student_attendence_detail as sad 
				WHERE  
					sa.id = sad.attendence_id
					and sad.`id` = $id
					AND sa.type=2
					and sad.type=2 
			";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('sa.branch_id');
		return $db->fetchRow($sql);
	}
}