<?php
class Global_Model_DbTable_DbTerm extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_startdate_enddate';
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	
	public function getAllTerm($search){
		$db= $this->getAdapter();
	
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$month=$tr->translate('MONTHLY');
		$term=$tr->translate('TERM');
		$semster=$tr->translate('SEMESTER');
		$year=$tr->translate('YEAR');

		$term1=$tr->translate('TERM_1');
		$term2=$tr->translate('TERM_2');
		$term3=$tr->translate('TERM_3');
		$term4=$tr->translate('TERM_4');
		
		$semester1=$tr->translate('SEMSTER_1');
		$semester2=$tr->translate('SEMSTER_2');
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentlang = $dbp->currentlang();
		$branch= $dbp->getBranchDisplay();

		$sql="SELECT 
				std.id
				,(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id=std.branch_id LIMIT 1) AS branch_name
				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = std.academic_year LIMIT 1) AS academic_year
				,std.title
				,CASE
					WHEN std.periodId = 1 THEN '$month'
					WHEN std.periodId = 2 THEN '$term'
					WHEN std.periodId = 3 THEN '$semster'
					WHEN std.periodId = 4 THEN '$year'
				END as Period
				,CASE
					WHEN std.installmentOrdering = '1' THEN '$term1'
					WHEN std.installmentOrdering = '2' THEN '$term2'
					WHEN std.installmentOrdering = '3' THEN '$term3'
					WHEN std.installmentOrdering = '4' THEN '$term4'
					WHEN std.installmentOrdering = '1,2' THEN '$semester1'
					WHEN std.installmentOrdering = '3,4' THEN '$semester2'
					WHEN std.installmentOrdering = '1,2,3,4' THEN '$year'
				END as PeriodType
				,(SELECT GROUP_CONCAT(i.shortcut) FROM rms_items AS i WHERE i.type=1 AND FIND_IN_SET(i.id,std.degreeId) LIMIT 1) AS degreeList
				,std.start_date
				,std.end_date
				,(SELECT first_name FROM `rms_users` WHERE id=std.user_id LIMIT 1) AS user_name 
	
			FROM  rms_startdate_enddate AS std
			WHERE 1 AND std.forDepartment=1 ";
			 $where = "";
    	
    	if(!empty($search['branch_id'])){
    		$where.=" AND std.branch_id= ".$search['branch_id'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=" AND std.academic_year= ".$search['academic_year'];
    	}
		if(!empty($search['degree'])){
			$degreeId = $search['degree'];
			$where.= " AND  FIND_IN_SET($degreeId,std.degreeId) ";
    	}
		if(!empty($search['termOption'])){
    		$where.=" AND std.periodId= ".$search['termOption'];
    	}
    	$where.=$dbp->getAccessPermission('std.branch_id');
		$order="  ORDER BY std.id DESC";
		return $db->fetchAll($sql.$where.$order);
	}
	public function addTermStudy($data){
    	$db= $this->getAdapter();
    	try{

			$degreeSelector= implode(',', $data['degreeSelector']);

    		if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'branch_id'		=>$data['branch_id'],
							'academic_year'	=>$data['academic_year'],
							'degreeId'		=>$degreeSelector,
							'title'			=>$data['title_'.$i],
							'periodId'		=>$data['term_'.$i],
							'installmentOrdering'=>$data['installmentOrdering'.$i],
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
			$dept = "";
	    	if (!empty($data['selector'])) foreach ( $data['selector'] as $rs){
	    		if (empty($dept)){
	    			$dept = $rs;
	    		}else{ $dept = $dept.",".$rs;
	    		}
	    	}
			$arr = array(
				'branch_id'		=>$data['branch_id'],
				'academic_year'	=>$data['academic_year'],
				'degreeId'		=>$dept,
				'title'			=>$data['title'],
				'periodId'		=>$data['term'],
				'installmentOrdering'=>$data['installmentOrdering'],
				'start_date'	=>$data['start_date'],
				'end_date'		=>$data['end_date'],
				'note'			=>$data['note'],
				'create_date'	=>date("Y-m-d"),
				'status'		=>$data['status'],
				'user_id'		=>$this->getUserId(),
			);
			$this->_name='rms_startdate_enddate';
			$where =" id =".$data['id'];
			$this->update($arr, $where);

    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	
	// public function editTermbyID($data){
	// 	$db= $this->getAdapter();
	// 	try{
	// 			$identitys = explode(',',$data['identity']);
	// 			$detailId="";
	// 			if (!empty($identitys)){
	// 				foreach ($identitys as $i){
	// 					if (empty($detailId)){
	// 						if (!empty($data['detailId'.$i])){
	// 							$detailId = $data['detailId'.$i];
	// 						}
	// 					}else{
	// 						if (!empty($data['detailId'.$i])){
	// 							$detailId= $detailId.",".$data['detailId'.$i];
	// 						}
	// 					}
	// 				}
	// 			}
				
	// 			$this->_name='rms_startdate_enddate';
	// 			$whereDl=" degreeId = ".$data['degree']." AND academic_year=".$data['academic_year'];
	// 			if (!empty($detailId)){
	// 				$whereDl.=" AND id NOT IN ($detailId)";
	// 			}
	// 			$this->delete($whereDl);

	// 			if(!empty($data['identity'])){
	// 				$ids = explode(',', $data['identity']);
	// 				foreach ($ids as $i){
						
	// 					if (!empty($data['detailId'.$i])){
	// 						$arr = array(
	// 							'branch_id'		=>$data['branch_id'],
	// 							'academic_year'	=>$data['academic_year'],
	// 							'degreeId'		=>$data['degree'],
	// 							'title'			=>$data['title_'.$i],
	// 							'periodId'		=>$data['term_'.$i],
	// 							'start_date'	=>$data['startdate_'.$i],
	// 							'end_date'		=>$data['enddate_'.$i],
	// 							'note'			=>$data['remark_'.$i],
	// 							'create_date'	=>date("Y-m-d"),
	// 							'user_id'		=>$this->getUserId(),
	// 						);
	// 						$this->_name='rms_startdate_enddate';
	// 						$where =" id =".$data['detailId'.$i];
	// 						$this->update($arr, $where);
	// 					}else{

	// 						$arr = array(
	// 							'branch_id'		=>$data['branch_id'],
	// 							'academic_year'	=>$data['academic_year'],
	// 							'degreeId'		=>$data['degree'],
	// 							'title'			=>$data['title_'.$i],
	// 							'periodId'		=>$data['term_'.$i],
	// 							'start_date'	=>$data['startdate_'.$i],
	// 							'end_date'		=>$data['enddate_'.$i],
	// 							'note'			=>$data['remark_'.$i],
	// 							'create_date'	=>date("Y-m-d"),
	// 							'user_id'		=>$this->getUserId(),
	// 						);
	// 						$this->_name='rms_startdate_enddate';	
	// 						$this->insert($arr);
	// 					}
	// 				}
	// 			}

    // 	}catch(Exception $e){
    // 		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    // 	}
	// }
	function getTermById($id=null){
		$db = $this->getAdapter();
		$sql=" select * from rms_startdate_enddate WHERE forDepartment=1 AND id = $id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		$sql.=" LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	public function getTermDetail($data=null){
		$db= $this->getAdapter();
		$sql="SELECT * FROM rms_startdate_enddate WHERE forDepartment=1 AND academic_year=".$data['academic_year']." AND degreeId=".$data['degreeId']." ORDER BY periodId ASC ";
	
		return $db->fetchAll($sql);
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



