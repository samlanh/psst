<?php
class Test_Model_DbTable_DbTerm extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_test_term';
	
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    
	public function getAllTerm($search){
		$db= $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
    	$lang = $dbp->currentlang();
    	if($lang==1){// khmer
    		
    		$branch = "branch_namekh";
    	}else{ // English
    		$branch = "branch_nameen";
    	}
		$sql="SELECT 
					id,
					(SELECT $branch FROM rms_branch WHERE br_id= branch_id LIMIT 1) AS branch_name,
					note AS title,
					start_date,
					end_date,
					create_date,
					(SELECT first_name FROM `rms_users` WHERE id=user_id LIMIT 1) as user_name 
				 ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM 
					rms_test_term
				WHERE 
					1 ";
		
		$where = "";
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
    		$s_where[]= " REPLACE(note,' ','') LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
		if(!empty($search['branch_id'])){
    		$where.=" AND branch_id=".$search['branch_id'];
    	}
		$order=" ORDER BY id DESC";
		return $db->fetchAll($sql.$where.$order);
	}
	public function addTermStudy($data){
    	$db= $this->getAdapter();
    	try{
    		$arr = array(
					'branch_id'		=>$data['branch_id'],
					'start_date'	=>$data['start_date'],
					'end_date'		=>$data['end_date'],
					'note'			=>$data['title'],
					'create_date'	=>date("Y-m-d"),
    				'status'		=>1,
					'user_id'		=>$this->getUserId(),
				);
			$this->_name='rms_test_term';	
			$this->insert($arr);
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	
	public function editTermbyID($data){
		$db= $this->getAdapter();
		try{
			$arr = array(
					'branch_id'		=>$data['branch_id'],
					'start_date'	=>$data['start_date'],
					'end_date'		=>$data['end_date'],
					'note'			=>$data['title'],
					'status'=>$data['status'],
					'user_id'=>$this->getUserId(),
				);
			$this->_name='rms_test_term';	
			$where=" id = ".$data['id'];
			$this->update($arr, $where);
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	function getTermById($id=null){
		$db = $this->getAdapter();
		$sql=" select * from rms_test_term WHERE id = $id  ";
		$sql.="LIMIT 1";
		return $db->fetchRow($sql);
	}
}