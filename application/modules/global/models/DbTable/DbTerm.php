<?php
class Global_Model_DbTable_DbTerm extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_startdate_enddate';
	
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    
	public function getAllTerm($search){
		$db= $this->getAdapter();
		$sql="SELECT 
					id,
					(SELECT CONCAT(branch_nameen) FROM rms_branch WHERE br_id=branch_id LIMIT 1) AS branch_name,
					title,
					(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = academic_year LIMIT 1) AS academic_year,
					start_date,
					end_date,
					note,
					create_date,
					(SELECT first_name FROM `rms_users` WHERE id=user_id LIMIT 1) as user_name 
				 ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM 
					rms_startdate_enddate
				WHERE 
					1 ";
		
		$where = "";
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['adv_search']));
    		$s_where[]= " branch_id LIKE '%{$s_search}%'";
    		$s_where[]= " title LIKE '%{$s_search}%'";
    		$s_where[]= " note LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND branch_id= ".$search['branch_id'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=" AND academic_year= ".$search['academic_year'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('branch_id');
		$order=" ORDER BY id DESC";
		return $db->fetchAll($sql.$where.$order);
	}
	public function addTermStudy($data){
    	$db= $this->getAdapter();
    	try{
    		if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'branch_id'		=>$data['branch_id'],
							'academic_year'	=>$data['academic_year'],
							'degreeId'		=>$data['degree'],
							'title'			=>$data['title_'.$i],
							'periodId'		=>$data['term_'.$i],
							'start_date'	=>$data['startdate_'.$i],
							'end_date'		=>$data['enddate_'.$i],
							'note'			=>$data['remark_'.$i],
							'create_date'	=>date("Y-m-d"),
							'user_id'		=>$this->getUserId(),
						);
					$this->_name='rms_startdate_enddate';	
					$this->insert($arr);
				}
    		}
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	
	public function editTermbyID($data){
		$db= $this->getAdapter();
		try{
			$arr = array(
					'branch_id'=>$data['branch_id'],
					'academic_year'=>$data['academic_year'],
					'degreeId'=>$data['academic_year'],
					'periodId'=>$data['academic_year'],
					'title'=>$data['title'],
					'start_date'=>$data['start_date'],
					'end_date'=>$data['end_date'],
					'note'=>$data['note'],
					'status'=>$data['status'],
					'user_id'=>$this->getUserId(),
				);
			$this->_name='rms_startdate_enddate';	
			$where=" id = ".$data['id'];
			$this->update($arr, $where);
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	function getTermById($id=null){
		$db = $this->getAdapter();
		$sql=" select * from rms_startdate_enddate WHERE id = $id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		$sql.=" LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getTermStudyInterm($branch,$year=null,$option=1){
		$dbp = new Application_Model_DbTable_DbGlobal();
		
		$param = array(
				'branch_id'=>$branch,
				'study_year'=>$year,
				'option'=>$option,
		);
		
		return $dbp->getAllStudyPeriod($branch,$year,$option);
	}
}



