<?php

class Issue_Model_DbTable_DbScoreEng extends Zend_Db_Table_Abstract
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
    public function addStudentScore($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$id_multiscore = $this->countMulitScoretype();
    		$rssubject = $_data['selector'];
    		$examtype='';
    		$id=0;
    		foreach ($rssubject as $key=> $subject){
    			if ($examtype!=$subject){
    				$_arr = array(
	    					'branch_id'=>$_data['branch_id'],
    						'score_setting'=>$_data['scoreSetting'],
	    					'academic_year'=>$_data['study_year'],
	    					'group_id'=>$_data['group'],
	    					'title'=>$_data['title'],
	    					'exame_type'=>$subject,
	    					'note'=>$_data['note'],
	    					'for_date'=>$_data['for_date'],
	    					'create_date'=>date("Y-m-d H:i:s"),
	    					'modify_date'=>date("Y-m-d H:i:s"),
	    					'user_id'=>$this->getUserId(),
    						'status'=>1,
    						'id_multiscore'=>$id_multiscore,
    						'type_score'=>2,//multi score type input
	    			);
	    			$this->_name='rms_score_eng';
	    			$id=$this->insert($_arr);
    			}
    			if(!empty($_data['identity'])){
    				$ids = explode(',', $_data['identity']);
    				if(!empty($ids))foreach ($ids as $i){
    					$arr=array(
    							'score_id'=>$id,
    							'student_id'=>$_data['student_id'.$i],
    							'score'=> $_data["score_".$i."_".$subject],
    							'note'=>$_data['note_'.$i],
    							'user_id'=>$this->getUserId(),
    					);
    					$this->_name='rms_score_eng_detail';
    					$this->insert($arr);
    				}
    			}
    				$examtype = $subject;
    		}
    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    public function editStudentScore($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$id_multiscore = $_data['score_id'];
    		$rssubject = $_data['selector'];
    		$ex_type='';
    		foreach ($rssubject as $key=> $subject){
    			if (empty($ex_type)){
    				$ex_type = $subject;
    			}else{
    				$ex_type = $ex_type.",".$subject;
    			}
    		}
    		
    		$this->_name='rms_score_eng_detail';
    		$wheredelete = "(SELECT rms_score_eng.id_multiscore  FROM `rms_score_eng` WHERE rms_score_eng.id = rms_score_eng_detail.score_id LIMIT 1)=".$id_multiscore;
    		if (!empty($ex_type)){
    			$wheredelete.=" AND (SELECT rms_score_eng.exame_type  FROM `rms_score_eng` WHERE rms_score_eng.id = rms_score_eng_detail.score_id LIMIT 1) NOT IN ($ex_type)";
    		}
    		$this->delete($wheredelete);
    		
    		$this->_name='rms_score_eng';
    		$wheredeleteMain = "id_multiscore=".$id_multiscore;
    		if (!empty($ex_type)){
    			$wheredeleteMain.=" AND exame_type NOT IN ($ex_type)";
    		}
    		$this->delete($wheredeleteMain);
    		
    		$examtype='';
    		$id=0;
    		foreach ($rssubject as $key=> $subject){
    			$oldScoreEng = $this->getScoreEngBYTypeAndId($subject, $id_multiscore);
    			if ($examtype!=$subject){
    				if (!empty($oldScoreEng)){//check has main score and update and delete old
    					$id = $oldScoreEng['id'];
	    				$_arr = array(
	    						'branch_id'=>$_data['branch_id'],
	    						'score_setting'=>$_data['scoreSetting'],
	    						'academic_year'=>$_data['study_year'],
	    						'group_id'=>$_data['group'],
	    						'title'=>$_data['title'],
	    						'exame_type'=>$subject,
	    						'note'=>$_data['note'],
	    						'for_date'=>$_data['for_date'],
	    						'modify_date'=>date("Y-m-d H:i:s"),
	    						'user_id'=>$this->getUserId(),
	    						'status'=>1,
	    						'id_multiscore'=>$id_multiscore,
	    						'type_score'=>2,//multi score type input
	    				);
	    				$this->_name='rms_score_eng';
	    				$where="id=".$id;
	    				$this->update($_arr, $where);
	    				
	    				//delete old score eng
	    				$this->_name='rms_score_eng_detail';
	    				$wherescoredetail = "score_id=".$id;
	    				$this->delete($wherescoredetail);
    				}
    			}
    			if (!empty($oldScoreEng)){//check has main score and insert
	    			if(!empty($_data['identity'])){
	    				$ids = explode(',', $_data['identity']);
	    				if(!empty($ids))foreach ($ids as $i){
	    					$arr=array(
	    							'score_id'=>$id,
	    							'student_id'=>$_data['student_id'.$i],
	    							'score'=> $_data["score_".$i."_".$subject],
	    							'note'=>$_data['note_'.$i],
	    							'user_id'=>$this->getUserId(),
	    					);
	    					$this->_name='rms_score_eng_detail';
	    					$this->insert($arr);
	    				}
	    			}
    			}
    			$examtype = $subject;
    		}
    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
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
    	if ($currentLang==1){
    		$colunmname='title';
    	}
    
    	$sql="SELECT seng.id_multiscore AS id,
				(SELECT branch_namekh FROM `rms_branch` WHERE br_id=seng.branch_id LIMIT 1) AS branch_name,
				seng.title,
				seng.for_date,
				(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS academic_id,
				g.group_code,
				(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degree,
    			(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade
				 ";
    	$sql.=$dbp->caseStatusShowImage("seng.status");
    	$sql.=" FROM `rms_score_eng` AS seng,
						rms_group AS g
				WHERE  seng.group_id=g.id ";
    	$where ='';
    	$from_date =(empty($search['start_date']))? '1': " seng.for_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " seng.for_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]=" seng.title LIKE '%{$s_search}%'";
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
    	$where.=$dbp->getAccessPermission('seng.branch_id');
    	$order=" GROUP BY seng.id_multiscore ORDER BY seng.id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getScoreById($score_id){
    	$db=$this->getAdapter();
    	$sql="SELECT s.*,(SELECT g.is_pass FROM `rms_group` AS g WHERE g.id = s.group_id LIMIT 1) AS is_pass FROM `rms_score_eng` AS s
			WHERE s.id_multiscore=$score_id";
//     	$sql.=" AND (SELECT g.is_pass FROM `rms_group` AS g WHERE g.id = s.group_id LIMIT 1)=2"; // only group studying
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('s.branch_id');
    	$sql.=" GROUP BY s.id_multiscore 
			ORDER BY s.id DESC LIMIT 1";
    	return $db->fetchRow($sql);
    }
    
    function checkExamtypeScore($score_id,$subject){
    	$db = $this->getAdapter();
    	$sql="
			SELECT sed.*,s.id_multiscore,s.exame_type 
			FROM `rms_score_eng_detail` AS sed,
			`rms_score_eng` AS s
			WHERE sed.score_id = s.id
			AND s.id_multiscore =$score_id
			AND s.exame_type =$subject
			GROUP BY s.`exame_type` LIMIT 1";
    	return $db->fetchRow($sql);
    }
    
    function getStudentScoreBySubjectID($score_id,$student_id,$exame_type){
    	$db=$this->getAdapter();
    	$sql="SELECT sed.*,s.id_multiscore,s.exame_type 
			FROM `rms_score_eng_detail` AS sed,
			`rms_score_eng` AS s
			WHERE sed.score_id = s.id
			AND sed.student_id = $student_id
			AND s.id_multiscore =$score_id
			AND s.exame_type =$exame_type LIMIT 1";
    	return $db->fetchRow($sql);
    }
    
	function getScoreSettingByBranch($branch_id){
		$db = $this->getAdapter();
		$sql="SELECT sc.id,sc.title AS `name` FROM `rms_scoreengsetting` AS sc
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
			`rms_scoreengsettingdetail` AS s,
			`rms_exametypeeng` AS seng
			WHERE seng.id = s.exam_typeid
			AND score_setting_id=$id";
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
}