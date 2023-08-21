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
// 	function  EditStudentBus($_data){
// 		$db = $this->getAdapter();
// 	  $db->beginTransaction();
// 	  try{
// 		  $status = empty($_data['status'])?0:1;
// 		  $_arr=array(
// 			  'branchId' 	=> $_data['branch_id'],
// 			  'busCode' 	=> $_data['busCode'],
// 			  'busType' 	=> $_data['busType'],
// 			  'driverId'	=> $_data['driverId'],
// 			  'busPlateNo' 	=> $_data['busPlateNo'],

// 			  'password' 	=>md5($_data['password']),

// 			  'note'   		=> $_data['note'],
// 			  'createDate' 	=> date("Y-m-d"),
// 			  'modifyDate' 	=> date("Y-m-d"),
// 			  'status'   	=>  $status,
// 			  'userId'	 	=> $this->getUserId(),
// 		  );
		
// 		  if (!empty($data['check_change'])){
// 			$_arr['password']= md5($_data['password']);
// 			}
// 			$this->_name="rms_student_bus";
// 			$where=$this->getAdapter()->quoteInto('id=?', $_data['id']); 
// 			$this->update($_arr,$where);
		
// 		  $db->commit();
// 	  }catch(exception $e){
// 		  Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 		  echo $e->getMessage();
// 		  $db->rollBack();
// 	  }
//    }


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
	$sql="SELECT * FROM rms_student_bus_schedule WHERE bus_id=".$data['bus_id']."  ORDER BY time ASC ";

	return $db->fetchAll($sql);
}

}