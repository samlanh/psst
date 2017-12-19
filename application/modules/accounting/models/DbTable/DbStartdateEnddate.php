<?php
class Accounting_Model_DbTable_DbStartdateEnddate extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_startdate_enddate';
	
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    
	public function getStartDateEndDate($search){
		$db= $this->getAdapter();
		$sql="SELECT 
					id,
					start_date,
					end_date,
					note,
					create_date,
					(SELECT first_name FROM `rms_users` WHERE id=user_id LIMIT 1) as user_name 
				FROM 
					rms_startdate_enddate
				WHERE 
					1
			";
		$where = "";
    	if(!empty($search['search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['search']));
    		$s_where[]= " note LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
		$order=" ORDER BY id DESC";
		return $db->fetchAll($sql.$where.$order);
	}
	
	
    public function addStartdateEnddate($data){
    	 
    	$db= $this->getAdapter();
    	try{
    		if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'start_date'=>$data['startdate_'.$i],
							'end_date'=>$data['enddate_'.$i],
							'note'=>$data['remark_'.$i],
							'create_date'=>date("Y-m-d"),
							'user_id'=>$this->getUserId(),
						);
					$this->_name='rms_startdate_enddate';	
					$this->insert($arr);
				}
    		}
		    	
    	}catch(Exception $e){
    		echo $e->getMessage();
    	}
    }
	public function editStartdateEnddate($data,$id){
		$db= $this->getAdapter();
		try{
			$this->_name="rms_startdate_enddate";
			$where = " status = 1 ";
			$this->delete($where);
			
			if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'start_date'=>$data['startdate_'.$i],
							'end_date'=>$data['enddate_'.$i],
							'note'=>$data['remark_'.$i],
							'create_date'=>date("Y-m-d"),
							'user_id'=>$this->getUserId(),
						);
					$this->_name='rms_startdate_enddate';	
					$this->insert($arr);
				}
			}
    	}catch(Exception $e){
    		echo $e->getMessage();
    	}
	}
	
	function getAllStartDateEndDate(){
		$db = $this->getAdapter();
		$sql=" select * from rms_startdate_enddate ";
		return $db->fetchAll($sql);
	}
	
}



