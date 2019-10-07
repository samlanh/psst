<?php

class Application_Model_DbTable_DbPlacementTest extends Zend_Db_Table_Abstract
{
    // set name value
	public function setName($name){
		$this->_name=$name;
	}
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function getStudnetId(){
		$session_user=new Zend_Session_Namespace(SUTUDENT_SESSION);
		return $session_user->student_id;
	}
	function getPlacementSetting($id){
		$db = $this->getAdapter();
		$sql="SELECT s.*,(SELECT t.title FROM `rms_test_type` AS t WHERE t.id=s.test_type LIMIT 1 ) AS test_type_title FROM `rms_placementtest_setting` AS s WHERE s.id =$id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getPlacementSettingDetail($id){
		$db = $this->getAdapter();
		$sql="SELECT sd.*,s.title AS section_title
			FROM  `rms_placementtest_setting_detail` AS sd,
			`rms_section` AS s
			 WHERE
				s.id = sd.section_id 
			  AND sd.setting_id = $id";
		return $db->fetchAll($sql);
	}
	function getStartPlacementTest($id){
		$db = $this->getAdapter();
		$sql="SELECT s.* FROM `rms_placement_test` AS s WHERE s.id =$id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function startPlacementTest($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$session_user=new Zend_Session_Namespace(SUTUDENT_SESSION);
			$branch_id = empty($session_user->branch_id)?1:$session_user->branch_id;
			
			$total_point = $this->getTotalScoreExam($_data['id']);
			$arr = array(
					'branch_id'	  			=> $branch_id,
					'student_id'	  			=> $this->getStudnetId(),
					'placement_setting_id'	 	=> $_data['id'],
					'duration'	  				=> $_data['duration'],
					'total_point'	  			=> $total_point,
					'result_score'	  			=> 0,
					'start' 					=> date("Y-m-d H:i:s"),
					'create_date' 				=> date("Y-m-d H:i:s"),
					'modify_date' 				=> date("Y-m-d H:i:s"),
					'status'	  				=> 1
					);
			$this->_name = 'rms_placement_test';
			$id = $this->insert($arr);
			$db->commit();
			return $id;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	function completePlacementTest($_placement_id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$result_score = $this->getTotalScoreResult($_placement_id);
			$arr = array(
					'result_score'	  			=> $result_score,
					'stop' 					=> date("Y-m-d H:i:s"),
					'modify_date' 				=> date("Y-m-d H:i:s"),
			);
			$this->_name = 'rms_placement_test';
			$where = " id = ".$_placement_id;
			$this->update($arr, $where);
			
			$db->commit();
			return $_placement_id;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	function getTotalScoreResult($_placement_id){
		$db = $this->getAdapter();
		$sql=" SELECT SUM(p.point) FROM `rms_placement_test_detail` AS p WHERE p.placemnet_id=$_placement_id ";
		return $db->fetchOne($sql);
	}
	function getTotalScoreExam($test_setting_id){
		$db = $this->getAdapter();
		$sql="SELECT SUM(qd.point)
		FROM `rms_question` AS q,
			 `rms_section` AS s,
			 rms_question_detail AS qd
		WHERE s.id = q.section_id AND qd.question_id = q.id
		";
		
		$sql.=" AND (s.id IN (SELECT  st.section_id FROM `rms_placementtest_setting_detail` AS st
			WHERE st.setting_id =$test_setting_id) OR s.parent IN (SELECT  st.section_id FROM `rms_placementtest_setting_detail` AS st
			WHERE st.setting_id =$test_setting_id))";
		
		 
		$sql.="ORDER BY
		(SELECT sp.ordering FROM `rms_section` AS sp WHERE sp.id = s.parent LIMIT 1 ) ASC,
		s.ordering ASC,
		q.ordering ASC";
		return $db->fetchOne($sql);
	}
	function enterPlacementTestAnswer($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$id= 0 ;
			$row = $this->checkQuestionDetial($_data);
			if (!empty($row)){
				$question_type = $_data['question_type'];
				$answer_key = $row['answer_key'];
				$point = $row['point'];
				if ($question_type==1){
					$answer_key = $_data['answer_key'];
					
					$answer = strtolower($row['answer_key']);
					$keyinput = strtolower($_data['answer_key']);
					if ($answer==$keyinput){
						$point = $row['point'];
					}else{
						$point = 0;
						$spp = str_split($row['answer_key']);
						foreach ($spp as $ss){
							if ($keyinput == $ss){
								$point = $row['point'];
								break;
							}
						}
					}
				}else if ($question_type==4 || $question_type==5 ||  $question_type==7 || $question_type==8 || $question_type==9 || $question_type==10 || $question_type==14 || $question_type==15){
					$answer_key = $_data['answer_key'];
					
					$answer = strtolower($row['answer_key']);
					$keyinput = strtolower($_data['answer_key']);
					if ($answer==$keyinput){
						$point = $row['point'];
					}else{
						$point = 0;
					}
				}
				
				$check = $this->checkPlacementTestDetial($_data);
				
				$arr = array(
						'placemnet_id'	 	=> $_data['placemnet_id'],
						'question_id'	  	=> $_data['question_id'],
						'answer_id'	  		=> $_data['answer_id'],
						'answer_key'	  	=> $answer_key,
						'point'	  			=>$point,
						'modify_date' 				=> date("Y-m-d H:i:s"),
				);
				$this->_name = 'rms_placement_test_detail';
				if (!empty($check)){
					$id = $check['id'];
					$where = " id = ".$id;
					$this->update($arr, $where);
				}else{
					$arr['create_date'] = date("Y-m-d H:i:s");
					$id = $this->insert($arr);
				}
				$db->commit();
			}
			return $id;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	function checkQuestionDetial($_data){
		$db = $this->getAdapter();
		$question_id = empty($_data['question_id'])?0:$_data['question_id']; 
		$answer_id = empty($_data['answer_id'])?0:$_data['answer_id'];
		$sql=" SELECT qd.* FROM `rms_question_detail` AS qd WHERE qd.question_id = $question_id AND qd.id = $answer_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function checkPlacementTestDetial($_data){
		$db = $this->getAdapter();
		$question_type = $_data['question_type'];
		$placemnet_id = empty($_data['placemnet_id'])?0:$_data['placemnet_id'];
		$question_id = empty($_data['question_id'])?0:$_data['question_id'];
		$answer_id = empty($_data['answer_id'])?0:$_data['answer_id'];
		$sql=" SELECT qd.* FROM `rms_placement_test_detail` AS qd WHERE qd.placemnet_id = $placemnet_id AND qd.question_id = $question_id ";
		if ($question_type!=2 && $question_type!=3 && $question_type!=12 && $question_type!=13){
		$sql.=" AND qd.answer_id = $answer_id ";
		}
		$sql.=" LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getPlacementTestAnswerRow($placemnet_id,$question_id,$answer_id){
		$db = $this->getAdapter();
		$sql="SELECT pd.* FROM `rms_placement_test_detail` AS pd WHERE pd.placemnet_id = $placemnet_id AND pd.question_id = $question_id AND pd.answer_id =$answer_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getExamHistoryStudent(){
		$db = $this->getAdapter();
		$student_id = $this->getStudnetId();
		$sql=" 
			SELECT 
			ps.title,
			(SELECT t.title FROM `rms_test_type` AS t WHERE t.id = ps.test_type LIMIT 1) AS test_type_title,
			pt.* FROM `rms_placement_test` AS pt,
			`rms_placementtest_setting` AS ps
			WHERE 
			ps.id = pt.placement_setting_id AND
			pt.student_id = $student_id
		";
		$sql.=" ORDER BY pt.id DESC";
		return $db->fetchAll($sql);
	}
	
	function getPlacementTestbyId($id){
		$db = $this->getAdapter();
		$student_id = $this->getStudnetId();
		$sql="SELECT pt.* FROM rms_placement_test AS pt WHERE pt.id = $id ";
		$sql.=" AND pt.student_id = $student_id";
		$sql.="  LIMIT 1 ";
		return $db->fetchRow($sql);
	}
}
?>