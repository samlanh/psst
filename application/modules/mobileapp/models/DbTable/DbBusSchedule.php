<?php
class Mobileapp_Model_DbTable_DbBusSchedule extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_bus_schedule';

	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}

	function getAllStudentBus($search){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$lang = $dbp->currentlang();
		
		$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;	
		$sql="SELECT id,
		(SELECT p.branch_nameen FROM `rms_branch` AS p  WHERE p.br_id = branch_id LIMIT 1) AS BranchName,
		(SELECT b.busCode FROM `rms_student_bus` AS b  WHERE b.id = bus_id LIMIT 1) AS SchoolBus,
		(SELECT t.teacher_name_kh FROM `rms_teacher` AS t  WHERE t.id = (SELECT d.driverId FROM `rms_student_bus` AS d  WHERE d.id = bus_id) LIMIT 1) AS Driver,
		(SELECT b.busPlateNo FROM `rms_student_bus` AS b  WHERE b.id = bus_id LIMIT 1) AS PlateNo,
		create_date, status
		 FROM `rms_student_bus_schedule`   WHERE 1";

		//  if(!empty($search['adv_search'])){
		// 	$s_where=array();
		// 	$s_search=$search['adv_search'];
		// 	$s_where[]= " busCode LIKE '%{$s_search}%'";
		// 	$where.=' AND ('.implode(' OR ', $s_where).')';
		// }
		if($search['search_status'] > -1){
			$where.= " AND status= ".$search['search_status'];
		}
		$order = "group by bus_id  ORDER BY id DESC";
		return $db->fetchAll($sql.$where.$order);
	}
	function getStudentBus($data=null){
		$db=$this->getAdapter();
		$sql="SELECT id, busCode as name  FROM `rms_student_bus`  WHERE status= 1 ";
		if(!empty($data['branch_id'])){
			$sql.=" AND branchId=".$data['branch_id'];
		}
		return $db->fetchAll($sql);
	}
	



	function  addBusSchedule($_data){
      	$db = $this->getAdapter();
        $db->beginTransaction();
        try{
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'branch_id'		=>$_data['branch_id'],
							'bus_id'		=>$_data['bus_id_'.$i],
							'student_id'	=>$_data['student_id_'.$i],
							'time'			=>$_data['time_'.$i],
							'type'			=>$_data['type_'.$i],
							'create_date'	=>date('Y-m-d H:i:s'),
							'modify_date'	=>date('Y-m-d H:i:s'),
							'user_id'		=>$this->getUserId(),
						);
					$this->_name="rms_student_bus_schedule";
					$this->insert($arr);
				}
    		}
            $db->commit();
        }catch(exception $e){
            Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
            $db->rollBack();
        }
 	}
	public function editBusSchedule($data){
		$db= $this->getAdapter();
		try{
				$identitys = explode(',',$data['identity']);
				$detailId="";
				if (!empty($identitys)){
					foreach ($identitys as $i){
						if (empty($detailId)){
							if (!empty($data['detailId'.$i])){
								$detailId = $data['detailId'.$i];
							}
						}else{
							if (!empty($data['detailId'.$i])){
								$detailId= $detailId.",".$data['detailId'.$i];
							}
						}
					}
				}
				$this->_name='rms_student_bus_schedule';
				$whereDl=" bus_id = ".$data['schoolBus'];
				if (!empty($detailId)){
					$whereDl.=" AND id NOT IN ($detailId)";
				}
				$this->delete($whereDl);

				if(!empty($data['identity'])){
					$ids = explode(',', $data['identity']);
					foreach ($ids as $i){
						
						if (!empty($data['detailId'.$i])){
							$arr = array(
								// 'branch_id'		=>$data['branch_id'],
								// 'bus_id'		=>$data['bus_id_'.$i],
								// 'student_id'	=>$data['student_id_'.$i],
								'time'			=>$data['time_'.$i],
								'type'			=>$data['type_'.$i],
								'modify_date'	=>date('Y-m-d H:i:s'),
								'user_id'		=>$this->getUserId(),
							);
							$this->_name='rms_student_bus_schedule';
							$where =" id =".$data['detailId'.$i];
							$this->update($arr, $where);
						}else{

							$arr = array(
								'branch_id'		=>$data['branch_id'],
								'bus_id'		=>$data['bus_id_'.$i],
								'student_id'	=>$data['student_id_'.$i],
								'time'			=>$data['time_'.$i],
								'type'			=>$data['type_'.$i],
								'create_date'	=>date('Y-m-d H:i:s'),
								'modify_date'	=>date('Y-m-d H:i:s'),
								'user_id'		=>$this->getUserId(),
							);
							$this->_name='rms_student_bus_schedule';	
							$this->insert($arr);
						}
					}
				}

    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}

public function getById($id)
{
	$db=$this->getAdapter();
	$sql="SELECT *  FROM ".$this->_name." WHERE id = ".$db->quote($id);
	$sql.=" LIMIT 1 ";
	$row=$db->fetchRow($sql);
	return $row;
}
public function getBusScheduleDetail($data=null){
	$db= $this->getAdapter();
	$stuName = "COALESCE(s.stu_code,''),' - ',COALESCE(s.stu_khname,''),' ',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')";
	$sql="SELECT *,
	(SELECT CONCAT($stuName) FROM `rms_student` AS s  WHERE s.stu_id = student_id LIMIT 1) AS StudentName,
	(SELECT b.busCode FROM `rms_student_bus` AS b  WHERE b.id = bus_id LIMIT 1) AS SchoolBus
	FROM rms_student_bus_schedule WHERE bus_id=".$data['bus_id']."  ORDER BY student_id ASC ";
	return $db->fetchAll($sql);
}
	public function getAllSession($id=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$opt_time = array(
				1=>$tr->translate('MORNING'),
				2=>$tr->translate('AFTERNOON'),
				3=>$tr->translate('EVENING'),
		);
		if($id==null){return $opt_time;}
		else {
			return $opt_time[$id]; 
		}
	}
	public function getType($id=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$opt_type = array(
				1=>$tr->translate('GO_TO_SCHOOL'),
				2=>$tr->translate('FROM_SCHOOL'),
		);
		if($id==null){return $opt_type;}
		else {
			return $opt_type[$id]; 
		}
	}
	public function checkStudent($data)
	{
		$db=$this->getAdapter();
		$sql="SELECT student_id FROM ".$this->_name." WHERE bus_id = ".$data['bus_id']." AND student_id=".$data['student_id']." AND time= ".$data['time']." AND  type=".$data['type'];
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}

}