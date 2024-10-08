<?php
class Issuesetting_Model_DbTable_DbScoreEntrySetting extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_score_entry_setting';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllScoreEntrySetting($search = ''){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
	
		$monthly=$tr->translate('MONTHLY');
		$semester=$tr->translate('SEMESTER');

    	$sql = " 
			SELECT 
				s.id
				,(SELECT CONCAT(branch_nameen) FROM rms_branch WHERE br_id=s.branchId LIMIT 1) AS branch_name
				,s.title
				,CASE
					WHEN s.examType = 1 THEN '$monthly'
					WHEN s.examType = 2 THEN '$semester'
				END AS examType
				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = s.academicYear LIMIT 1) AS academicYear	
				,s.examFromDate
				,s.`examEndDate`
				,s.fromDate
				,s.`endDate`
				,s.description
				, s.createDate 
		";
    	$sql.=$dbp->caseStatusShowImage("s.status");
    	$sql.=" FROM `rms_score_entry_setting` AS s WHERE 1 ";
    	$orderby = "  ORDER BY s.id DESC";
    	$where = ' ';
    	$from_date =(empty($search['start_date']))? '1': "s.createDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "s.createDate <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['advance_search'])){
   			 $s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
    					$s_where[] = " s.title LIKE '%{$s_search}%'";
    					$s_where[] = " s.description LIKE '%{$s_search}%'";
    					$sql .=' AND ( '.implode(' OR ',$s_where).')';
   	 	}
   	 	if(!empty($search['branch_search'])){
   	 		$where.= " AND s.branchId = ".$db->quote($search['branch_search']);
   	 	}
		if(!empty($search['academicYear'])){
   	 		$where.= " AND s.academicYear = ".$db->quote($search['academicYear']);
   	 	}
    	if($search['status_search']>-1){
    		$where.= " AND s.status = ".$db->quote($search['status_search']);
    	}
    	$where.=$dbp->getAccessPermission('s.branchId');
    	return $db->fetchAll($sql.$where.$orderby);
    }
	public function addScoreEntrySetting($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$dept = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($dept)){
	    			$dept = $rs;
	    		}else{ $dept = $dept.",".$rs;
	    		}
	    	}
			$_arr = array(
					'branchId'	 	=>$_data['branch_id'],
					'degreeId'		=>$dept,
					'title'			 =>$_data['title'],
					'description'	=>$_data['description'],
					'fromDate'		 =>$_data['from_date'],
					'endDate'		 =>$_data['end_date'],
					'examFromDate'	=>$_data['exam_from_date'],
					'examEndDate'	=>$_data['exam_end_date'],
					'createDate' 	=>date("Y-m-d H:i:s"),
					'modifyDate' 	=>date("Y-m-d H:i:s"),
					'status'		 =>1,
					'examType'		=>$_data['examType'],
					'forMonth'		=>$_data['forMonth'],
					'forSemester'	=>$_data['forSemester'],
					'academicYear'	=>empty($_data['academicYear']) ? 0 : $_data['academicYear'],
			);
			$this->_name='rms_score_entry_setting';
			$this->insert($_arr);
		  $db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
   }
   
   function getScoreEntrySettingById($id){
   		$db = $this->getAdapter();
   		$sql="SELECT s.* FROM `rms_score_entry_setting` AS s WHERE s.id=$id";
   		
   		$dbp = new Application_Model_DbTable_DbGlobal();
   		$sql.=$dbp->getAccessPermission('s.branchId');
   		return $db->fetchRow($sql);
   }
 
   public function editScoreEntrySetting($_data){
   	$db = $this->getAdapter();
   	$db->beginTransaction();
   	try{
		$dept = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($dept)){
	    			$dept = $rs;
	    		}else{ $dept = $dept.",".$rs;
	    		}
	    	}
		$status=empty($_data['status'])?0:1;
   		$_arr = array(
			'branchId'	 =>$_data['branch_id'],
			'degreeId'	 =>$dept,
			'title'		 =>$_data['title'],
			'description'=>$_data['description'],
			'fromDate'	 =>$_data['from_date'],
			'endDate'	 =>$_data['end_date'],
			'examFromDate'=>$_data['exam_from_date'],
			'examEndDate'=>$_data['exam_end_date'],
			'createDate' =>date("Y-m-d H:i:s"),
			'modifyDate' =>date("Y-m-d H:i:s"),
			'status'	 =>$status,
			'examType'		=>$_data['examType'],
			'forMonth'		=>$_data['forMonth'],
			'forSemester'	=>$_data['forSemester'],
			'academicYear'	=>empty($_data['academicYear']) ? 0 : $_data['academicYear'],
   		);
   		$this->_name='rms_score_entry_setting';
   		$where=' id='.$_data['id'];
   		$this->update($_arr, $where);
   		$db->commit();
   	}catch (Exception $e){
   		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
   		$db->rollBack();
   	}
   }
}