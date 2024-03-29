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
				(SELECT i.title FROM `rms_items` AS i WHERE i.type=1 AND i.id = s.degreeId LIMIT 1) AS degree,
				(SELECT t.title FROM `rms_attendance_score_setting` AS t WHERE t.id = s.settingScoreAttId LIMIT 1) AS settingScoreAttId,
				s.title,s.note,s.create_date
    		";
    	$sql.=$dbp->caseStatusShowImage("s.status");
    	$sql.=" FROM `rms_scoreengsetting` AS s
				WHERE s.schoolOption=1 ";
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
				'degreeId'				=>$_data['degreeId'],
				'title'					=>$_data['title'],
				'note'					=>$_data['note'],
				'status'				=>1,
				'create_date'			=>date("Y-m-d H:i:s"),
				'modify_date'			=>date("Y-m-d H:i:s"),
				'user_id'				=>$this->getUserId(),
				'schoolOption'			=>$_data['schoolOption'],
				'settingScoreAttId'			=>$_data['settingScoreAttId'],
			);
			$this->_name='rms_scoreengsetting';
			$id = $this->insert($_arr);
			
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				$this->_name='rms_scoreengsettingdetail';
				if(!empty($ids))foreach ($ids as $i){
					$forExamType = empty($_data['forExamType'.$i])?1:2;
					if($_data['subjectId'.$i]>0){
						$arr=array(
							'score_setting_id'		=>$id,
							'criteriaId'			=>$_data['examtype_name_'.$i],
							'pecentage_score'		=>$_data['percentage'.$i],
							'timeInput'				=>$_data['timeInput'.$i],
							'subjectId'				=>$_data['subjectId'.$i],
							'note'					=>$_data['note_'.$i],
							'forExamType'			=>$forExamType,
						);
						if(!empty($_data['isNotEnteryCri'.$i])){
							$arr['isNotEnteryCri']=1;
						}
						if(!empty($_data['forMonth'.$i])){// for month
							$arr['subCriterialTitleKh']=$_data['subCriterialTitleKhMonth'.$i];
							$arr['subCriterialTitleEng']=$_data['subCriterialTitleEngMonth'.$i];
							$arr['forExamType']=1;
							$this->insert($arr);
						}
						
						if(!empty($_data['forExamType'.$i])){//for semester
							$arr['subCriterialTitleKh']=$_data['subCriterialTitleKh'.$i];
							$arr['subCriterialTitleEng']=$_data['subCriterialTitleEng'.$i];
							$arr['forExamType']=2;
							$this->insert($arr);
						}
					}else{
						$arr=array(
								'score_setting_id'		=>$id,
								'criteriaId'			=>$_data['examtype_name_'.$i],
								'pecentage_score'		=>$_data['percentage'.$i],
								'timeInput'				=>$_data['timeInput'.$i],
								'subjectId'				=>$_data['subjectId'.$i],
								'subCriterialTitleKh'	=>$_data['subCriterialTitleKh'.$i],
								'subCriterialTitleEng'	=>$_data['subCriterialTitleEng'.$i],
								'note'					=>$_data['note_'.$i],
								'forExamType'			=>$forExamType,
						);
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
   function getGradingSystemById($id){
   		$db = $this->getAdapter();
   		$sql="SELECT s.* FROM `rms_scoreengsetting` AS s WHERE s.id=$id AND s.schoolOption=1 ";
   		
   		$dbp = new Application_Model_DbTable_DbGlobal();
   		$sql.=$dbp->getAccessPermission('s.branch_id');
   		return $db->fetchRow($sql);
   }
   function getGradingDetail($id){
   	$db = $this->getAdapter();
   	$sql="SELECT 
			s.*,
			(SELECT es.title FROM `rms_exametypeeng` AS es WHERE es.id = s.criteriaId LIMIT 1) AS exam_typetitle 
		FROM 
			
			`rms_scoreengsettingdetail` AS s 
   		WHERE s.score_setting_id=$id 
		
		ORDER BY 
			(SELECT es.criteriaType FROM `rms_exametypeeng` AS es WHERE es.id = s.criteriaId LIMIT 1) ASC,
			s.criteriaId ASC,
			s.subjectId ASC,
			s.forExamType ASC  ";
   	
   	$result =  $db->fetchAll($sql);
   	//return $result;
   	$resultOpt = array();
   	if(!empty($result)){
   		$key=0;
   		$criteriaId=0;
   		$subjectId=0;
   		$oldScore=0;
   		$key_old=0;
		
   		foreach($result as $key => $rs){
			
			if($key > 0){
				if($criteriaId == $rs['criteriaId'] AND $subjectId == $rs['subjectId'] AND $oldScore == $rs['pecentage_score']){
					
					
					$resultOpt[$key_old]['forExamTypeSemester']=($rs['forExamType']==2)?2:0;
					
					if($rs['forExamType']==2){
						$resultOpt[$key_old]['subCriterialTitleKhSemester']=$rs['subCriterialTitleKh'];
						$resultOpt[$key_old]['subCriterialTitleEngSemester']=$rs['subCriterialTitleEng'];
					}else{
						$resultOpt[$key_old]['subCriterialTitleKh']=$rs['subCriterialTitleKh'];
						$resultOpt[$key_old]['subCriterialTitleEng']=$rs['subCriterialTitleEng'];
					}
					
						
					
				}else{
					$resultOpt[$key] = $rs;
					$resultOpt[$key]['forExamTypeSemester']=($rs['forExamType']==2)?2:0;
					$resultOpt[$key]['subCriterialTitleKhSemester']="";
					$resultOpt[$key]['subCriterialTitleEngSemester']="";
					if($rs['forExamType']==2){
						$resultOpt[$key]['subCriterialTitleKhSemester']=$rs['subCriterialTitleKh'];
						$resultOpt[$key]['subCriterialTitleEngSemester']=$rs['subCriterialTitleEng'];
					}else{
						$resultOpt[$key]['subCriterialTitleKh']=$rs['subCriterialTitleKh'];
						$resultOpt[$key]['subCriterialTitleEng']=$rs['subCriterialTitleEng'];
					}
					$resultOpt[$key]['forExamType']=$rs['forExamType'];
					
				}
			}else{
				
					$resultOpt[$key] = $rs;
					$resultOpt[$key]['forExamTypeSemester']=($rs['forExamType']==2)?2:0;
					$resultOpt[$key]['subCriterialTitleKhSemester']="";
					$resultOpt[$key]['subCriterialTitleEngSemester']="";
					if($rs['forExamType']==2){
						$resultOpt[$key]['subCriterialTitleKhSemester']=$rs['subCriterialTitleKh'];
						$resultOpt[$key]['subCriterialTitleEngSemester']=$rs['subCriterialTitleEng'];
					}else{
						$resultOpt[$key]['subCriterialTitleKh']=$rs['subCriterialTitleKh'];
						$resultOpt[$key]['subCriterialTitleEng']=$rs['subCriterialTitleEng'];
					}
					$resultOpt[$key]['forExamType']=$rs['forExamType'];
					
			}
			
			$key_old=$key;
			$criteriaId=$rs['criteriaId'];
   			$subjectId=$rs['subjectId'];
   			$oldScore=$rs['pecentage_score'];
			
   		}
   	}
   	return $resultOpt;
   }
   public function editGrandingSystem($_data){
   	$db = $this->getAdapter();
   	$db->beginTransaction();
   	try{
		$status = empty($_data['status'])?0:1;
   		$_arr = array(
   				'branch_id'				=>$_data['branch_id'],
   				'degreeId'				=>$_data['degreeId'],
   				'title'					=>$_data['title'],
   				'note'					=>$_data['note'],
   				'status'				=>$status,
   				'modify_date'			=>date("Y-m-d H:i:s"),
   				'user_id'				=>$this->getUserId(),
				'schoolOption'			=>$_data['schoolOption'],
				'settingScoreAttId'		=>$_data['settingScoreAttId'],
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
   				if(!empty($_data['detailid'.$i])){
   					$arr=array(
							'score_setting_id'		=>$id,
							'criteriaId'			=>$_data['examtype_name_'.$i],
							'pecentage_score'		=>$_data['percentage'.$i],
							'timeInput'				=>$_data['timeInput'.$i],
							'subjectId'				=>$_data['subjectId'.$i],
							'subCriterialTitleKh'	=>$_data['subCriterialTitleKh'.$i],
							'subCriterialTitleEng'	=>$_data['subCriterialTitleEng'.$i],
							'note'					=>$_data['note_'.$i],
							'forExamType'			=>$forExamType,
					);
					if(!empty($_data['isNotEnteryCri'.$i])){
						$arr['isNotEnteryCri']=1;
					}
   					$this->_name='rms_scoreengsettingdetail';
					
					if(!empty($_data['detailid'.$i])){
						if(!empty($_data['forMonth'.$i])){// for month
							$arr['subCriterialTitleKh']=$_data['subCriterialTitleKhMonth'.$i];
							$arr['subCriterialTitleEng']=$_data['subCriterialTitleEngMonth'.$i];
							$arr['forExamType']=1;
							$where = " id =".$_data['detailid'.$i];
							$this->update($arr, $where);
						}
						if(!empty($_data['forExamType'.$i])){//for semester
							$arr['subCriterialTitleKh']=$_data['subCriterialTitleKh'.$i];
							$arr['subCriterialTitleEng']=$_data['subCriterialTitleEng'.$i];
							$arr['forExamType']=2;
							$this->insert($arr);
						}
					}else{
						if(!empty($_data['forMonth'.$i])){// for month
							$arr['subCriterialTitleKh']=$_data['subCriterialTitleKhMonth'.$i];
							$arr['subCriterialTitleEng']=$_data['subCriterialTitleEngMonth'.$i];
							$arr['forExamType']=1;
							$this->insert($arr);
						}
						if(!empty($_data['forExamType'.$i])){//for semester
							$arr['subCriterialTitleKh']=$_data['subCriterialTitleKh'.$i];
							$arr['subCriterialTitleEng']=$_data['subCriterialTitleEng'.$i];
							$arr['forExamType']=2;
							$this->insert($arr);
						}
					}
					
   				}else{
	   				$arr=array(
							'score_setting_id'		=>$id,
							'criteriaId'			=>$_data['examtype_name_'.$i],
							'pecentage_score'		=>$_data['percentage'.$i],
							'timeInput'				=>$_data['timeInput'.$i],
							'subjectId'				=>$_data['subjectId'.$i],
							'subCriterialTitleKh'	=>$_data['subCriterialTitleKh'.$i],
							'subCriterialTitleEng'	=>$_data['subCriterialTitleEng'.$i],
							'note'					=>$_data['note_'.$i],
							'forExamType'			=>$forExamType,
					);
					if(!empty($_data['isNotEnteryCri'.$i])){
						$arr['isNotEnteryCri']=1;
					}
	   				$this->_name='rms_scoreengsettingdetail';
	   				
	   				if(!empty($_data['forExamType'.$i])){//AND $_data['subjectId'.$i]>0
						$arr['subCriterialTitleKh']=$_data['subCriterialTitleKh'.$i];
						$arr['subCriterialTitleEng']=$_data['subCriterialTitleEng'.$i];
	   					$this->insert($arr);
	   				}
	   				if(!empty($_data['forMonth'.$i])){
	   					$arr['subCriterialTitleKh']=$_data['subCriterialTitleKhMonth'.$i];
	   					$arr['subCriterialTitleEng']=$_data['subCriterialTitleEngMonth'.$i];
	   					$arr['forExamType']=1;
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
   
   function checkingIsInUse($gradingIdSettingID){
	   $db = $this->getAdapter();
	   $sql="SELECT grd.* ";
	   $sql.="
			FROM `rms_grading` AS grd
	   ";
	   $sql.=" WHERE 1 ";
	   $sql.=" AND grd.gradingSettingId = $gradingIdSettingID ";
	   $sql.=" ORDER BY grd.id DESC ";
	   $sql.=" LIMIT 1 ";
	   return $db->fetchRow($sql);
   }
}