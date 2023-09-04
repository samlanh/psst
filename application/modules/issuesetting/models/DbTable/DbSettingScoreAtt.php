<?php
class Issuesetting_Model_DbTable_DbSettingScoreAtt extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_attendance_score_setting';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllSettingScoreAtt($search=array()){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label="name_en";
		$branch = "branch_nameen";
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
			$branch = "branch_namekh";
		}
		
    	$sql = " SELECT 
				s.id,
				(SELECT $branch FROM `rms_branch` AS b  WHERE b.br_id = s.branchId LIMIT 1) AS branchName,
				(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.type=1 AND i.id = s.degreeId LIMIT 1) AS degree,
				s.title,
				s.note,
				s.createDate
    		";
    	$sql.=$dbp->caseStatusShowImage("s.status");
    	$sql.=" FROM `rms_attendance_score_setting` AS s
				WHERE 1 ";
    	$orderby = " ORDER BY s.id DESC";
    	$where = ' ';
    	$from_date =(empty($search['start_date']))? '1': "s.createDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "s.createDate <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['advance_search'])){
   			 $s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
    					$s_where[] = " s.title LIKE '%{$s_search}%'";
    					$s_where[] = " s.note LIKE '%{$s_search}%'";
    					$sql .=' AND ( '.implode(' OR ',$s_where).')';
   	 	}
   	 	if(!empty($search['branch_id'])){
   	 		$where.= " AND s.branchId = ".$db->quote($search['branch_id']);
   	 	}
		if(!empty($search['degree'])){
   	 		$where.= " AND s.degreeId = ".$db->quote($search['degree']);
   	 	}
    	if($search['status']>-1 ){
    		$where.= " AND s.status = ".$db->quote($search['status']);
    	}
    	$where.=$dbp->getAccessPermission('s.branchId');
    	return $db->fetchAll($sql.$where.$orderby);
    }
	public function addSettingScoreAttendance($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$_arr = array(
					'branchId'		=>$_data['branch_id'],
					'title'			=>$_data['title'],
					'degreeId'		=>$_data['degreeId'],
					'score'			=>$_data['score'],
					'note'			=>$_data['note'],
					'status'		=>1,
					'createDate'	=>date("Y-m-d H:i:s"),
					'modifyDate'	=>date("Y-m-d H:i:s"),
					'userId'		=>$this->getUserId(),
			);
			$this->_name='rms_attendance_score_setting';
			$id = $this->insert($_arr);
			
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				if(!empty($ids))foreach ($ids as $i){
					$arr=array(
							'settingId'			=>$id,
							'attendanceType'	=>$_data['attendanceType'.$i],
							'scoreDeduct'		=>$_data['scoreDeduct'.$i],
							'note'				=>$_data['note_'.$i],
					);
					$this->_name='rms_attendance_score_setting_detail';
					$this->insert($arr);
				}
			}
		  $db->commit();
		  return $id;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
			
		}
   }
   function getSettingScoreAttById($id){
   		$db = $this->getAdapter();
   		$sql="SELECT s.* FROM `rms_attendance_score_setting` AS s WHERE s.id=$id  ";
   		
   		$dbp = new Application_Model_DbTable_DbGlobal();
   		$sql.=$dbp->getAccessPermission('s.branch_id');
   		return $db->fetchRow($sql);
   }
   function getSettingScoreAttDetail($id)
   {
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="SELECT 
				s.*
				,CASE
						WHEN s.attendanceType = 2 THEN '".$tr->translate("ABSENT")."'
						WHEN s.attendanceType = 3 THEN '".$tr->translate("PERMISSION")."'
						WHEN s.attendanceType = 4 THEN '".$tr->translate("LATE")."'
						WHEN s.attendanceType = 5 THEN '".$tr->translate("EARLY_LEAVE")."'
						ELSE 'N/A'
				END as attendanceTypeTitle
			FROM `rms_attendance_score_setting_detail` AS s 
			WHERE s.settingId=$id";
		return $db->fetchAll($sql);
   }
   
   public function editSettingScoreAttendance($_data){
   	$db = $this->getAdapter();
   	$db->beginTransaction();
   	try{
		$status = empty($_data['status'])?0:1;
   		$_arr = array(
   				'branchId'		=>$_data['branch_id'],
				'title'			=>$_data['title'],
				'degreeId'		=>$_data['degreeId'],
				'score'			=>$_data['score'],
				'note'			=>$_data['note'],
   				'status'		=>$status,
				
   				'modifyDate'	=>date("Y-m-d H:i:s"),
				'userId'		=>$this->getUserId(),
   		);
   		$this->_name='rms_attendance_score_setting';
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
   		$this->_name="rms_attendance_score_setting_detail";
   		$where="settingId = ".$id;
   		if (!empty($detailId)){
   			$where.=" AND id NOT IN ($detailId) ";
   		}
   		$this->delete($where);
   		
   		if(!empty($_data['identity'])){
   			$ids = explode(',', $_data['identity']);
   			if(!empty($ids))foreach ($ids as $i){
   				if (!empty($_data['detailid'.$i])){
   					$arr=array(
   							'settingId'			=>$id,
							'attendanceType'	=>$_data['attendanceType'.$i],
							'scoreDeduct'		=>$_data['scoreDeduct'.$i],
							'note'				=>$_data['note_'.$i],
   					);
   					$this->_name='rms_attendance_score_setting_detail';
   					$where = " id =".$_data['detailid'.$i];
					$this->update($arr, $where);
   				}else{
	   				$arr=array(
	   						'settingId'			=>$id,
							'attendanceType'	=>$_data['attendanceType'.$i],
							'scoreDeduct'		=>$_data['scoreDeduct'.$i],
							'note'				=>$_data['note_'.$i],
	   				);
	   				$this->_name='rms_attendance_score_setting_detail';
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
   
   function getSettingScoreAttList($_data){
	  $branchId = empty($_data['branchId']) ? 0 : $_data['branchId'];
	  $degreeId = empty($_data['degreeId']) ? 0 : $_data['degreeId'];
   		$db = $this->getAdapter();
   		$sql="
			SELECT 
				st.id, 
				st.title AS `name`  
			FROM rms_attendance_score_setting AS st 
			WHERE st.status=1";
   		$sql.=" AND st.branchId = $branchId ";
   		$sql.=" AND st.degreeId = $degreeId ";
		if(!empty($_data['settingId'])){
			$sql.=" OR st.id = ".$_data['settingId'];
		}
   		return $db->fetchAll($sql);
   }
}