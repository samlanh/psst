<?php
class Global_Model_DbTable_DbCurriculumPeriod extends Zend_Db_Table_Abstract
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
	
	public function getAllCurriculumPeriod($search){
		$db= $this->getAdapter();
	
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$month=$tr->translate('MONTHLY');
		$term=$tr->translate('TERM');
		$semster=$tr->translate('SEMESTER');
		$year=$tr->translate('YEAR');

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
				,(SELECT GROUP_CONCAT(i.shortcut) FROM rms_items AS i WHERE i.type=1 AND FIND_IN_SET(i.id,std.degreeId) LIMIT 1) AS degreeList
				,std.start_date
				,std.end_date
				,(SELECT first_name FROM `rms_users` WHERE id=std.user_id LIMIT 1) AS user_name 
	
			FROM  rms_startdate_enddate AS std WHERE 1 AND std.forDepartment=2 ";
			 $where = "";
    	
    	if(!empty($search['branch_id'])){
    		$where.=" AND std.branch_id= ".$search['branch_id'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=" AND std.academic_year= ".$search['academic_year'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('std.branch_id');
		$order="  ORDER BY std.id DESC";
		return $db->fetchAll($sql.$where.$order);
	}
	public function addCurriculumPeriod($data){
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
							'start_date'	=>$data['startdate_'.$i],
							'end_date'		=>$data['enddate_'.$i],
							'note'			=>$data['remark_'.$i],
							'create_date'	=>date("Y-m-d"),
							'user_id'		=>$this->getUserId(),
							
							'forSemester'		=>$data['forSemester'.$i],
							'forDepartment'	=>$data['forDepartment'],
						);
					$this->_name='rms_startdate_enddate';	
					$this->insert($arr);
				}
    		}
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}

	public function editCurriculumPeriod($data){
		$db= $this->getAdapter();
		try{
			$dept = "";
	    	if (!empty($data['selector'])) foreach ( $data['selector'] as $rs){
	    		if (empty($dept)){
	    			$dept = $rs;
	    		}else{ $dept = $dept.",".$rs;
	    		}
	    	}
			$data['forSemester'] = empty($data['forSemester']) ? : $data['forSemester'];
			$arr = array(
				'branch_id'		=>$data['branch_id'],
				'academic_year'	=>$data['academic_year'],
				'degreeId'		=>$dept,
				'title'			=>$data['title'],
				'periodId'		=>$data['term'],
				'start_date'	=>$data['start_date'],
				'end_date'		=>$data['end_date'],
				'note'			=>$data['note'],
				'create_date'	=>date("Y-m-d"),
				'status'		=>$data['status'],
				'user_id'		=>$this->getUserId(),
				'forSemester'	=>$data['forSemester'],
				'forDepartment'	=>$data['forDepartment'],
			);
			$this->_name='rms_startdate_enddate';
			$where =" id =".$data['id'];
			$this->update($arr, $where);

    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	function getCurriculumById($id=null){
		$db = $this->getAdapter();
		$sql=" select * from rms_startdate_enddate WHERE forDepartment=2 AND id = $id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		$sql.=" LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	
}



