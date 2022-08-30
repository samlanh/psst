<?php

class Issue_Model_DbTable_DbMonthlyProgress extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_score';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function countMulitScoretype(){
    	$db = $this->getAdapter();
    	$sql="SELECT id_multiscore FROM `rms_score_eng`
				WHERE type_score=2 
				ORDER BY id DESC LIMIT 1";
    	$row = $db->fetchOne($sql);
    	$row = empty($row)?0:$row;
    	return $row+1;
    }
    public function addMonthlyProgress($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$rssubject = $_data['selector'];
    		if (!empty($rssubject)){
    			$_arr = array(
    					'branch_id'=>$_data['branch_id'],
    					'subjectarea_setting'=>$_data['subjectAreaSetting'],
    					'academic_year'=>$_data['study_year'],
    					'group_id'=>$_data['group'],
    					'student_id'=>$_data['student_id'],
//     					'title'=>$_data['title'],
    					
    					'exam_type'=>$_data['exam_type'],
    					'for_month'=>$_data['for_month'],
    					'for_semester'=>$_data['for_semester'],
    					
    					'note'=>$_data['note'],
    					'for_date'=>$_data['for_date'],
    					'create_date'=>date("Y-m-d H:i:s"),
    					'modify_date'=>date("Y-m-d H:i:s"),
    					'user_id'=>$this->getUserId(),
    					'status'=>1,
    			);
    			$this->_name='rms_monthlyprogress';
    			$id=$this->insert($_arr);
    			
    			foreach ($rssubject as $key=> $subject){
    				if(!empty($_data['identity'])){
    					$ids = explode(',', $_data['identity']);
    					if(!empty($ids))foreach ($ids as $i){
    						$arr=array(
    								'monthly_pro_id'=>$id,
    								'subject_id'=>$subject,
    								'subject_area_id'=> $_data["subject_area_id".$i],
    								'score'=> $_data["score_".$i."_".$subject],
    								'note'=>$_data['note_'.$i],
    								'user_id'=>$this->getUserId(),
    						);
    						$this->_name='rms_monthlyprogress_detail';
    						$this->insert($arr);
    					}
    				}
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    public function editMonthlyProgress($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$rssubject = $_data['selector'];
    		if (!empty($rssubject)){
    			$id = $_data['id'];
    			$_arr = array(
    					'branch_id'=>$_data['branch_id'],
    					'subjectarea_setting'=>$_data['subjectAreaSetting'],
    					'academic_year'=>$_data['study_year'],
    					'group_id'=>$_data['group'],
    					'student_id'=>$_data['student_id'],
//     					'title'=>$_data['title'],
    					
    					'exam_type'=>$_data['exam_type'],
    					'for_month'=>$_data['for_month'],
    					'for_semester'=>$_data['for_semester'],
    					
    					'note'=>$_data['note'],
    					'for_date'=>$_data['for_date'],
    					'modify_date'=>date("Y-m-d H:i:s"),
    					'user_id'=>$this->getUserId(),
    					'status'=>$_data['status'],
    			);
    			$this->_name='rms_monthlyprogress';
    			$where=" id = $id";
    			$this->update($_arr, $where);
    			
    			$this->_name='rms_monthlyprogress_detail';
    			$wheredelete = "monthly_pro_id =".$id;
    			$this->delete($wheredelete);
    			
    			foreach ($rssubject as $key=> $subject){
    				if(!empty($_data['identity'])){
    					$ids = explode(',', $_data['identity']);
    					if(!empty($ids))foreach ($ids as $i){
    						$arr=array(
    								'monthly_pro_id'=>$id,
    								'subject_id'=>$subject,
    								'subject_area_id'=> $_data["subject_area_id".$i],
    								'score'=> $_data["score_".$i."_".$subject],
    								'note'=>$_data['note_'.$i],
    								'user_id'=>$this->getUserId(),
    						);
    						$this->_name='rms_monthlyprogress_detail';
    						$this->insert($arr);
    					}
    				}
    			}
    		}
    		
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		
    	}
    }
    function getScoreEngBYTypeAndId($exam_type,$id_multiscore){
    	$db = $this->getAdapter();
    	$sql="SELECT s.* FROM `rms_score_eng` AS s
			WHERE s.exame_type=$exam_type AND s.id_multiscore=$id_multiscore LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getAllScore($search=null){
    	$db=$this->getAdapter();
    
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
    	$sql="SELECT seng.id AS id,
				(SELECT branch_namekh FROM `rms_branch` WHERE br_id=seng.branch_id LIMIT 1) AS branch_name,
				CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,''),'-',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS name,
				(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =seng.exam_type LIMIT 1) as exam_type,
				seng.for_semester,
				CASE
					WHEN seng.exam_type = 2 THEN ''
				ELSE (SELECT $month FROM `rms_month` WHERE id=seng.for_month  LIMIT 1) 
				END 
				as for_month,
			
				(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee AS f WHERE id=seng.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation LIMIT 1) AS academic_id,
				g.group_code,
				(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degree,
    			(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
    			seng.for_date
				 ";
    	$sql.=$dbp->caseStatusShowImage("seng.status");
    	$sql.=" FROM `rms_monthlyprogress` AS seng,
						rms_group AS g,
						rms_student AS s
				WHERE seng.student_id = s.stu_id  AND  seng.group_id=g.id ";
    	$where ='';
    	$from_date =(empty($search['start_date']))? '1': " seng.for_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " seng.for_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]=" seng.title LIKE '%{$s_search}%'";
    		$s_where[]=" s.stu_code LIKE '%{$s_search}%'";
    		$s_where[]=" s.stu_khname LIKE '%{$s_search}%'";
    		$s_where[]=" s.stu_enname LIKE '%{$s_search}%'";
    		$s_where[]=" s.last_name LIKE '%{$s_search}%'";
    		$s_where[]=" g.group_code LIKE '%{$s_search}%'";
    		$s_where[]=" seng.note LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['degree']>0){
    		$where.= " AND g.degree =".$search['degree'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND seng.academic_year =".$search['study_year'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND `g`.`grade` =".$search['grade'];
    	}
    	if(!empty($search['group'])){
    			$where.=" AND `seng`.`group_id` =".$search['group'];
    	}
    	
    	if(!empty($search['exam_type'])){
    		$where.=" AND seng.exam_type =".$search['exam_type'];
    	}
    	if(!empty($search['for_semester'])){
    		$where.=" AND seng.for_semester =".$search['for_semester'];
    	}
    	if(!empty($search['for_month'])){
    		$where.=" AND seng.for_month =".$search['for_month'];
    	}
    	
    	$where.=$dbp->getAccessPermission('seng.branch_id');
    	$order=" GROUP BY seng.id ORDER BY seng.id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getMonthlyProgressById($score_id){
    	$db=$this->getAdapter();
    	$sql="SELECT s.*,(SELECT g.is_pass FROM `rms_group` AS g WHERE g.id = s.group_id LIMIT 1) AS is_pass FROM `rms_monthlyprogress` AS s
			WHERE s.id= $score_id";
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('s.branch_id');
    	$sql.=" GROUP BY s.id 
			ORDER BY s.id DESC LIMIT 1";
    	return $db->fetchRow($sql);
    }
    
    function getMonthlyProgressBySubjectID($monthltproid,$subjectarea_id,$subject){
    	$db=$this->getAdapter();
    	$sql="SELECT sed.*
			FROM `rms_monthlyprogress_detail` AS sed
			WHERE 
			 sed.subject_area_id = $subjectarea_id
			AND sed.monthly_pro_id =$monthltproid
			AND sed.subject_id =$subject LIMIT 1";
    	return $db->fetchRow($sql);
    }
    
	function getSubjectAreaSettingByBranch($branch_id){
		$db = $this->getAdapter();
		$sql="SELECT sc.id,sc.title AS `name` FROM `rms_subjectarea_setting` AS sc
			WHERE sc.status=1 AND sc.branch_id = $branch_id";
		return $db->fetchAll($sql);
	}
	function getScoreSettingDetail($id){
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
	
		$db = $this->getAdapter();
		$sql="SELECT
		s.*,seng.$colunmname AS `name`
		FROM
		`rms_subjectarea_setting_detail` AS s,
		`rms_subjectarea` AS seng
		WHERE seng.id = s.subject_area_id
		AND setting_id=$id";
		return $db->fetchAll($sql);
	}
	
	
	function getGroupInforByID($group_id,$string=null){
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		$db = $this->getAdapter();
		$sql ="SELECT g.*,
				(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=g.academic_year LIMIT 1) AS academic,
				(SELECT rms_items.$colunmname FROM `rms_items` WHERE `id`=g.degree AND type=1 LIMIT 1) AS degreetitle,
				(SELECT CONCAT(rms_itemsdetail.$colunmname) FROM `rms_itemsdetail` WHERE `id`=g.grade AND items_type=1 LIMIT 1) AS gradetitle
		FROM `rms_group` AS g WHERE g.`id`=$group_id LIMIT 1";
		$row =  $db->fetchRow($sql);
		if (empty($string)){
			return $row;
		}else{
			$string="";
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			if (!empty($row)){
				$string='<div class="form-group" style=" padding: 10px; border: solid 2px #02014a;   border-radius: 1px;">
				<ul>
				<li><span class="lbl-tt">'.$tr->translate("STUDY_YEAR").'</span>: '.$row['academic'].'</li>
				<li><span class="lbl-tt">'.$tr->translate("DEGREE").'</span>: '.$row['degreetitle'].'</li>
				<li><span class="lbl-tt">'.$tr->translate("DEGREE").'</span>: '.$row['gradetitle'].'</li>
				</ul>
				</div>';
			}
			return $string;
		}
	}
	
	
	function checkuDuplicate($data){
		$db = $this->getAdapter();
		$sql="
		SELECT
		* FROM rms_monthlyprogress AS i
		WHERE i.student_id='".$data['student_id']."'
		AND i.group_id='".$data['group']."'
		";
		if (!empty($data['exam_type'])){
			$sql.=" AND i.exam_type = ".$data['exam_type'];
			$sql.=" AND i.for_semester = ".$data['for_semester'];
			if ($data['exam_type']==1){
				$sql.=" AND i.for_month = ".$data['for_month'];
			}
		}
		if (!empty($data['id'])){
			$sql.=" AND i.id != ".$data['id'];
		}
		$sql.=" LIMIT 1 ";
		$row = $db->fetchRow($sql);
		if (!empty($row)){
			return 1;
		}
		return 0;
	}
}