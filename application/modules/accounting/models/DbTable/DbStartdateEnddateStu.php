<?php
class Accounting_Model_DbTable_DbStartdateEnddateStu extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_startdate_enddate';
	
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    
	public function getStartDateEndDate($search){
		$db= $this->getAdapter();
		$sql="SELECT se.id,
			     (SELECT CONCAT(s.stu_enname,'(',s.stu_code,')')AS `name` FROM rms_student AS s WHERE s.stu_id=se.stu_id LIMIT 1) AS stu_name,
			     (SELECT  name_en  FROM rms_view WHERE  rms_view.key_code = se.term AND TYPE=6 AND STATUS=1 LIMIT 1)AS term,
			      DATE_FORMAT(se.start_date, '%d-%m-%Y'),DATE_FORMAT(se.end_date, '%d-%m-%Y'),se.note,se.create_date,
			      (SELECT first_name FROM `rms_users` WHERE id=se.user_id LIMIT 1) AS user_name 
			    FROM  rms_startdate_enddate_stu AS se
			    WHERE 1";
		$where = "";
    	if(!empty($search['search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['search']));
    		$s_where[]= " note LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(($search['stu_id']>0)){
    		$where.= " AND se.stu_id = ".$search['stu_id'];
    	}
    	if(($search['term_id']>0)){
    		$where.= " AND se.term = ".$search['term_id'];
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
							'stu_id'	=>$data['stu_id_'.$i],
							'term'		=>$data['term_'.$i],
							'start_date'=>$data['startdate_'.$i],
							'end_date'	=>$data['enddate_'.$i],
							'note'		=>$data['remark_'.$i],
							'create_date'=>date("Y-m-d"),
							'status'	=>1,
							'user_id'	=>$this->getUserId(),
						);
					$this->_name='rms_startdate_enddate_stu';	
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
			$this->_name="rms_startdate_enddate_stu";
			$where = " status=1 ";
			$this->delete($where);
			
			if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'stu_id'	=>$data['stu_id_'.$i],
							'term'		=>$data['term_'.$i],
							'start_date'=>$data['startdate_'.$i],
							'end_date'	=>$data['enddate_'.$i],
							'note'		=>$data['remark_'.$i],
							'create_date'=>date("Y-m-d"),
							'status'	=>1,
							'user_id'=>$this->getUserId(),
						);
					$this->_name='rms_startdate_enddate_stu';	
					$this->insert($arr);
				}
			}
    	}catch(Exception $e){
    		echo $e->getMessage();
    	}
	}
	
	function getAllStartDateEndDate(){
		$db = $this->getAdapter();
		$sql=" select * from rms_startdate_enddate_stu";
		return $db->fetchAll($sql);
	}
	
	function getAllpaymentTerm(){
		$db = $this->getAdapter();
		$SQL="select key_code as id , name_en as name from rms_view where type=6 and status=1 ";
		return $db->fetchAll($SQL);
	}
	
	//select all Gerneral old student
	function getAllGerneralOldStudent(){
		$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission();
		$sql="SELECT s.stu_id As id,s.stu_code As stu_code,CONCAT(s.stu_enname,'(',s.stu_code,')')AS `name` FROM rms_student AS s
		WHERE s.status=1 and s.is_subspend=0  $branch_id  ORDER BY stu_type DESC ";
		$rows=$db->fetchAll($sql);
		//array_unshift($rows,array('id' => '-1',"name"=>"Add New"));
		array_unshift($rows,array('id' => '',"name"=>"Select Student", ));
		$options = '';
		if(!empty($rows))foreach($rows as $value){
			$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
		}
		return $options;
	}
	
	function getAllStudent(){
		$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission();
		$sql="SELECT s.stu_id As id,s.stu_code As stu_code,CONCAT(s.stu_enname,'(',s.stu_code,')')AS `name` FROM rms_student AS s
		WHERE s.status=1 and s.is_subspend=0  $branch_id  ORDER BY stu_type DESC ";
		return $db->fetchAll($sql);
	}
}



