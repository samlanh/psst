<?php
class Placement_Model_DbTable_DbQuestion extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_section';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->branch_id;
	}
	function getAllQuestion($search=null){
		 
		$dbp = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$lang = $dbp->currentlang();
		$db = $this->getAdapter();
		$sql = " SELECT q.id,
					(SELECT tt.title FROM rms_test_type AS tt WHERE tt.id=q.test_type LIMIT 1 ) AS test_type,
					q.question_title,
					(SELECT s.title FROM `rms_section` AS s WHERE s.id =q.section_id LIMIT 1) AS section,
					(SELECT qt.title FROM `rms_question_type` AS qt WHERE qt.id = q.question_type LIMIT 1) AS question_type,
					q.create_date,
					(SELECT first_name FROM rms_users WHERE rms_users.id = q.user_id) AS user_name
		";
		$sql.=$dbp->caseStatusShowImage("q.status");
		$sql.=" FROM `rms_question` AS q
		WHERE 1  ";
		 
		$order=" ORDER BY q.id DESC";
		$from_date =(empty($search['start_date']))? '1': " q.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " q.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['adv_search'])){
		$s_where = array();
		$s_search = addslashes(trim($search['adv_search']));
				$s_where[]= " q.question_title LIKE '%{$s_search}%'";
				$s_where[]= " (SELECT tt.title FROM rms_test_type AS tt WHERE tt.id=q.test_type LIMIT 1 ) LIKE '%{$s_search}%'";
				$s_where[]= " (SELECT s.title FROM `rms_section` AS s WHERE s.id =q.section_id LIMIT 1) LIKE '%{$s_search}%'";
				$s_where[]= " (SELECT qt.title FROM `rms_question_type` AS qt WHERE qt.id = q.question_type LIMIT 1) LIKE '%{$s_search}%'";
				$s_where[]= " q.note LIKE '%{$s_search}%'";
				$where .= ' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['status'])){
			$where.= " AND q.status = ".$search['status'];
		}
		if(!empty($search['test_type'])){
			$where.= " AND q.test_type = ".$search['test_type'];
		}
		if(!empty($search['section_id'])){
			$where.= " AND q.section_id = ".$search['section_id'];
		}
		if(!empty($search['question_type'])){
			$where.= " AND q.question_type = ".$search['question_type'];
		}
		return $db->fetchAll($sql.$where.$order);
	}
	public function addQuestion($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$question_type = $_data['question_type'];
			$_arr = array(
					'test_type'=>$_data['test_type'],
					'section_id'=>$_data['section_id'],
					'question_title'=>$_data['question_title'],
					'question_type'=>$question_type,
					'ordering'=>$_data['ordering'],
					'note'=>$_data['note'],
					'status'=>1,
					'create_date'=>date("Y-m-d H:i:s"),
					'modify_date'=>date("Y-m-d H:i:s"),
					'user_id'=>$this->getUserId(),
					'is_example'=>empty($_data['is_example'])?0:1,
			);
			$this->_name='rms_question';
			
			$part= PUBLIC_PATH.'/images/placement/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$name = $_FILES['photo']['name'];
			if (!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = "question_".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['photo']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					$_arr['images'] = $image_name;
				}
			}
			$id = $this->insert($_arr);
			if ($question_type==1 || $question_type==7 || $question_type==8 || $question_type==9 || $question_type==10 ){
				if(!empty($_data['identity'])){
					$ids = explode(',', $_data['identity']);
					if(!empty($ids))foreach ($ids as $i){
						$arr=array(
								'question_id'=>$id,
								'answer_label'=>$_data['answer_label'.$i],
								'answer_key'=>$_data['answer_key'.$i],
								'point'=>$_data['point'.$i],
						);
						$this->_name='rms_question_detail';
						$this->insert($arr);
					}
				}
			}else if ($question_type==2 || $question_type==3 || $question_type==12 || $question_type==13){ //Muliple Choice
				if(!empty($_data['identity'])){
					$ids = explode(',', $_data['identity']);
					if(!empty($ids))foreach ($ids as $i){
						$arr=array(
								'question_id'=>$id,
								'answer_label'=>$_data['answer_label'.$i],
								'answer_key'=>$_data['answer_key'.$i],
								'is_correct'=>empty($_data['is_correct'.$i])?0:1,
								'point'=>$_data['point'.$i],
						);
						$this->_name='rms_question_detail';
						$this->insert($arr);
					}
				}
			}else if ($question_type==4){
					if(!empty($_data['identity'])){
						$ids = explode(',', $_data['identity']);
						if(!empty($ids))foreach ($ids as $i){
							$arr= array(
									'question_id'=>$id,
									'answer_key'=>$_data['answer_key'.$i],
									'point'=>$_data['point'.$i],
							);
							$name = $_FILES['photo'.$i]['name'];
							if (!empty($name)){
								$ss = 	explode(".", $name);
								$image_name = "question_row_".$id.$i.date("Y").date("m").date("d").time().".".end($ss);
								$tmp = $_FILES['photo'.$i]['tmp_name'];
								if(move_uploaded_file($tmp, $part.$image_name)){
									$arr['image'] = $image_name;
								}
							} 
							$this->_name='rms_question_detail';
							$this->insert($arr);
						}
					}
			}else if ($question_type==5 || $question_type==14){
					if(!empty($_data['identity'])){
						$ids = explode(',', $_data['identity']);
						if(!empty($ids))foreach ($ids as $i){
							$arr=array(
									'question_id'=>$id,
									'answer_label'=>$_data['answer_label'.$i],
									'answer_key'=>$_data['answer_key'.$i],
									'point'=>$_data['point'.$i],
							);
							$this->_name='rms_question_detail';
							$this->insert($arr);
						}
					}
			}else if ($question_type==6 || $question_type==15){//Matching
				if(!empty($_data['identity'])){
					$ids = explode(',', $_data['identity']);
					if(!empty($ids))foreach ($ids as $i){
						$arr=array(
								'question_id'=>$id,
								'answer_label'=>$_data['answer_label'.$i],
								'column_b'=>$_data['column_b'.$i],
								'answer_key'=>$_data['answer_key'.$i],
								'point'=>$_data['point'.$i],
								'is_example'=>empty($_data['is_example'.$i])?0:1,
						);
						$this->_name='rms_question_detail';
						$this->insert($arr);
					}
				}
			}else if ($question_type==11){
				if(!empty($_data['identity'])){
					$ids = explode(',', $_data['identity']);
					if(!empty($ids))foreach ($ids as $i){
						$arr=array(
								'question_id'=>$id,
								'answer_label'=>$_data['answer_label'.$i],
								'answer_key'=>$_data['answer_key'.$i],
								'point'=>$_data['point'.$i],
								'is_example'=>empty($_data['is_example'.$i])?0:1,
						);
						$this->_name='rms_question_detail';
						$this->insert($arr);
					}
				}
			}else if ($question_type==8){
					
			}
			
			
			$db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	function getQuestionById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_question WHERE id = $id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getQuestionDetailById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_question_detail WHERE question_id = $id ";
		return $db->fetchAll($sql);
	}
	public function editQuestion($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$question_type = $_data['question_type'];
			$_arr = array(
					'test_type'=>$_data['test_type'],
					'section_id'=>$_data['section_id'],
					'question_title'=>$_data['question_title'],
					'question_type'=>$question_type,
					'ordering'=>$_data['ordering'],
					'note'=>$_data['note'],
					'status'=>$_data['status'],
					'modify_date'=>date("Y-m-d H:i:s"),
					'user_id'=>$this->getUserId(),
					'is_example'=>empty($_data['is_example'])?0:1,
			);
			$this->_name='rms_question';
			$part= PUBLIC_PATH.'/images/placement/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$name = $_FILES['photo']['name'];
			if (!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = "question_".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['photo']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					$_arr['images'] = $image_name;
				}
			}
			$id = $_data['id'];
			$where=" id=$id";
			$this->update($_arr, $where);
			
			$this->_name="rms_question_detail";
			$whereDelete="question_id = ".$id;
			$this->delete($whereDelete);
			
			if ($question_type==1 || $question_type==7 || $question_type==8 || $question_type==9 || $question_type==10 ){
				if(!empty($_data['identity'])){
					$ids = explode(',', $_data['identity']);
					if(!empty($ids))foreach ($ids as $i){
						$arr=array(
								'question_id'=>$id,
								'answer_label'=>$_data['answer_label'.$i],
								'answer_key'=>$_data['answer_key'.$i],
								'point'=>$_data['point'.$i],
						);
						$this->_name='rms_question_detail';
						$this->insert($arr);
					}
				}
			}else if ($question_type==2 || $question_type==3 || $question_type==12 || $question_type==13){ //Muliple Choice
				if(!empty($_data['identity'])){
					$ids = explode(',', $_data['identity']);
					if(!empty($ids))foreach ($ids as $i){
						$arr=array(
								'question_id'=>$id,
								'answer_label'=>$_data['answer_label'.$i],
								'answer_key'=>$_data['answer_key'.$i],
								'is_correct'=>empty($_data['is_correct'.$i])?0:1,
								'point'=>$_data['point'.$i],
						);
						$this->_name='rms_question_detail';
						$this->insert($arr);
					}
				}
			}else if ($question_type==4){
				if(!empty($_data['identity'])){
					$ids = explode(',', $_data['identity']);
					if(!empty($ids))foreach ($ids as $i){
						$arr= array(
								'question_id'=>$id,
								'answer_key'=>$_data['answer_key'.$i],
								'point'=>$_data['point'.$i],
						);
						$name = $_FILES['photo'.$i]['name'];
						if (!empty($name)){
							$ss = 	explode(".", $name);
							$image_name = "question_row_".$id.$i.date("Y").date("m").date("d").time().".".end($ss);
							$tmp = $_FILES['photo'.$i]['tmp_name'];
							if(move_uploaded_file($tmp, $part.$image_name)){
								$arr['image'] = $image_name;
							}
						}else{
							if (!empty($_data['old_image'.$i])){
								$arr['image'] = $_data['old_image'.$i];
							}
						}
						$this->_name='rms_question_detail';
						$this->insert($arr);
					}
				}
			}else if ($question_type==5 || $question_type==14){
				if(!empty($_data['identity'])){
					$ids = explode(',', $_data['identity']);
					if(!empty($ids))foreach ($ids as $i){
						$arr=array(
								'question_id'=>$id,
								'answer_label'=>$_data['answer_label'.$i],
								'answer_key'=>$_data['answer_key'.$i],
								'point'=>$_data['point'.$i],
						);
						$this->_name='rms_question_detail';
						$this->insert($arr);
					}
				}
			}else if ($question_type==6 || $question_type==15){//Matching
				if(!empty($_data['identity'])){
					$ids = explode(',', $_data['identity']);
					if(!empty($ids))foreach ($ids as $i){
						$arr=array(
								'question_id'=>$id,
								'answer_label'=>$_data['answer_label'.$i],
								'column_b'=>$_data['column_b'.$i],
								'answer_key'=>$_data['answer_key'.$i],
								'point'=>$_data['point'.$i],
								'is_example'=>empty($_data['is_example'.$i])?0:1,
						);
						$this->_name='rms_question_detail';
						$this->insert($arr);
					}
				}
			}else if ($question_type==11){
				if(!empty($_data['identity'])){
					$ids = explode(',', $_data['identity']);
					if(!empty($ids))foreach ($ids as $i){
						$arr=array(
								'question_id'=>$id,
								'answer_label'=>$_data['answer_label'.$i],
								'answer_key'=>$_data['answer_key'.$i],
								'point'=>$_data['point'.$i],
								'is_example'=>empty($_data['is_example'.$i])?0:1,
						);
						$this->_name='rms_question_detail';
						$this->insert($arr);
					}
				}
			}else if ($question_type==8){
					
			}
				
				
			$db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	
	function chcekSectionInUse($section){
		$db = $this->getAdapter();
		$sql=" SELECT psd.section_id,pt.* FROM `rms_placement_test` AS pt,
				`rms_placementtest_setting_detail` AS psd
				WHERE psd.setting_id = pt.placement_setting_id ";
		
		$condiction = $this->getChildSection($section);
		if (!empty($condiction)){
			$sql.=" AND psd.section_id IN ($condiction)";
		}else{
			$sql.=" AND psd.section_id=".$section;
		}
		$sql.=" LIMIT 1";
		$rs = $db->fetchRow($sql);
	   	if (!empty($rs)){
	   		return 1;
	   	}
	   	return 0;
	}
	function getChildSection($id,$idetity=null){
		$where='';
		$db = $this->getAdapter();
		$sql=" SELECT c.`parent` as id FROM `rms_section` AS c WHERE c.`id` = $id AND c.`status`=1 ";
		$child = $db->fetchAll($sql);
		foreach ($child as $va) {
			if (empty($idetity)){
				if ($va['id']!=0){
					$idetity=$id.",".$va['id'];
				}
			}else{
				if ($va['id']!=0){
					$idetity=$idetity.",".$va['id'];
				}
			}
			$idetity = $this->getChildSection($va['id'],$idetity);
		}
		return $idetity;
	}
}