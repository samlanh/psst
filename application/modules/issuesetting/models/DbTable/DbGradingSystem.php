<?php
class Issuesetting_Model_DbTable_DbGradingSystem extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_scoreengsetting';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAlGrandingSystem($search = '',$items_type=null){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql = " SELECT 
				s.id,
				(SELECT CONCAT(branch_nameen) FROM rms_branch WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
				s.title,s.note,s.create_date
    		";
    	$sql.=$dbp->caseStatusShowImage("s.status");
    	$sql.=" FROM `rms_scoreengsetting` AS s
				WHERE 1 AND s.schoolOption=1 ";
    	$orderby = " ORDER BY s.id DESC";
    	$where = ' ';
    	$from_date =(empty($search['start_date']))? '1': "s.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "s.create_date <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['advance_search'])){
   			 $s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
    					$s_where[] = " s.title LIKE '%{$s_search}%'";
    					$s_where[] = " s.note LIKE '%{$s_search}%'";
    					$sql .=' AND ( '.implode(' OR ',$s_where).')';
   	 	}
   	 	if(!empty($search['branch_search'])){
   	 		$where.= " AND s.branch_id = ".$db->quote($search['branch_search']);
   	 	}
    	if($search['status_search']>-1){
    		$where.= " AND s.status = ".$db->quote($search['status_search']);
    	}
    	$where.=$dbp->getAccessPermission('s.branch_id');
    	return $db->fetchAll($sql.$where.$orderby);
    }
	public function addGrandingSystem($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			
			
			$_arr = array(
					'branch_id'				=>$_data['branch_id'],
					'title'					=>$_data['title'],
					'note'					=>$_data['note'],
					'status'				=>1,
					
					'create_date'			=>date("Y-m-d H:i:s"),
					'modify_date'			=>date("Y-m-d H:i:s"),
					'user_id'				=>$this->getUserId(),
					'schoolOption'			=>$_data['schoolOption'],
			);
			$this->_name='rms_scoreengsetting';
			$id = $this->insert($_arr);
			
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				if(!empty($ids))foreach ($ids as $i){
					$forExamType = empty($_data['forExamType'.$i])?1:2;
					$arr=array(
							'score_setting_id'		=>$id,
							'exam_typeid'			=>$_data['examtype_name_'.$i],
							'pecentage_score'		=>$_data['percentage'.$i],
							
							'timeInput'				=>$_data['timeInput'.$i],
							'subjectId'				=>$_data['subjectId'.$i],
							'subCriterialTitleKh'	=>$_data['subCriterialTitleKh'.$i],
							'subCriterialTitleEng'	=>$_data['subCriterialTitleEng'.$i],
							
							'note'					=>$_data['note_'.$i],
							'forExamType'			=>$forExamType,
					);
					$this->_name='rms_scoreengsettingdetail';
					$this->insert($arr);
				}
			}
		  $db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
			
		}
   }
   function getGradingSystemById($id){
   		$db = $this->getAdapter();
   		$sql="SELECT s.* FROM `rms_scoreengsetting` AS s WHERE s.id=$id AND s.schoolOption=1 ";
   		
   		$dbp = new Application_Model_DbTable_DbGlobal();
   		$sql.=$dbp->getAccessPermission('s.branch_id');
   		return $db->fetchRow($sql);
   }
   function getGradingSystemDetail($id){
   	$db = $this->getAdapter();
   	$sql="SELECT s.*,(SELECT es.title FROM `rms_exametypeeng` AS es WHERE es.id = s.exam_typeid LIMIT 1) AS exam_typetitle FROM `rms_scoreengsettingdetail` AS s WHERE s.score_setting_id=$id";
   	return $db->fetchAll($sql);
   }
   public function editGrandingSystem($_data){
   	$db = $this->getAdapter();
   	$db->beginTransaction();
   	try{
		$status = empty($_data['status'])?0:1;
   		$_arr = array(
   				'branch_id'				=>$_data['branch_id'],
   				'title'					=>$_data['title'],
   				'note'					=>$_data['note'],
   				'status'				=>$status,
   				'modify_date'			=>date("Y-m-d H:i:s"),
   				'user_id'				=>$this->getUserId(),
				
				'schoolOption'			=>$_data['schoolOption'],
   		);
   		$this->_name='rms_scoreengsetting';
   		$where=' id='.$_data['id'];
   		$this->update($_arr, $where);
   		$id = $_data['id'];
   		
   		$detailId="";
   		$ids = explode(",", $_data['identity']);
   		if (!empty($_data['identity'])){
   			foreach ($ids as $k){
   				if (empty($detailId)){
   					if (!empty($_data['detailid'.$k])){
   						$detailId = $_data['detailid'.$k];
   					}
   				}else{
   					if (!empty($_data['detailid'.$k])){
   						$detailId= $detailId.",".$_data['detailid'.$k];
   					}
   				}
   			}
   		}
   		$this->_name="rms_scoreengsettingdetail";
   		$where="score_setting_id = ".$id;
   		if (!empty($detailId)){
   			$where.=" AND id NOT IN ($detailId) ";
   		}
   		$this->delete($where);
   		
   		if(!empty($_data['identity'])){
   			$ids = explode(',', $_data['identity']);
   			if(!empty($ids))foreach ($ids as $i){
				$forExamType = empty($_data['forExamType'.$i])?1:2;
   				if (!empty($_data['detailid'.$i])){
   					$arr=array(
							'score_setting_id'		=>$id,
							'exam_typeid'			=>$_data['examtype_name_'.$i],
							'pecentage_score'		=>$_data['percentage'.$i],
							
							'timeInput'				=>$_data['timeInput'.$i],
							'subjectId'				=>$_data['subjectId'.$i],
							'subCriterialTitleKh'	=>$_data['subCriterialTitleKh'.$i],
							'subCriterialTitleEng'	=>$_data['subCriterialTitleEng'.$i],
							
							'note'					=>$_data['note_'.$i],
							'forExamType'			=>$forExamType,
					);
   					$this->_name='rms_scoreengsettingdetail';
   					$where = " id =".$_data['detailid'.$i];
					$this->update($arr, $where);
   				}else{
	   				$arr=array(
							'score_setting_id'		=>$id,
							'exam_typeid'			=>$_data['examtype_name_'.$i],
							'pecentage_score'		=>$_data['percentage'.$i],
							
							'timeInput'				=>$_data['timeInput'.$i],
							'subjectId'				=>$_data['subjectId'.$i],
							'subCriterialTitleKh'	=>$_data['subCriterialTitleKh'.$i],
							'subCriterialTitleEng'	=>$_data['subCriterialTitleEng'.$i],
							
							'note'					=>$_data['note_'.$i],
							'forExamType'			=>$forExamType,
					);
	   				$this->_name='rms_scoreengsettingdetail';
	   				$this->insert($arr);
   				}
   			}
   		}
   		$db->commit();
   	}catch (Exception $e){
   		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
   		$db->rollBack();
   	}
   }
   
   function checkingIsInUse($gradingIdSettingID){
	   $db = $this->getAdapter();
	   $sql="SELECT grd.* ";
	   $sql.="
			FROM `rms_grading` AS grd ,
				`rms_group` AS g
	   ";
	   $sql.=" WHERE grd.groupId = g.id ";
	   $sql.=" AND g.gradingId = $gradingIdSettingID ";
	   $sql.=" ORDER BY grd.id DESC ";
	   $sql.=" LIMIT 1 ";
	   return $db->fetchRow($sql);
   }
}