<?php
class Mobileapp_Model_DbTable_DbStudentBus extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_school_bus';

	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}

	function getAllStudentBus($search){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$lang = $dbp->currentlang();
		
		$from_date =(empty($search['start_date']))? '1': "createDate >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "createDate <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;	
		$sql="SELECT id,
		(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = branchId LIMIT 1) AS branch_name,
		busCode, busPlateNo, busType, createDate, status
		 FROM `rms_school_bus`   WHERE 1";

		 if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=$search['adv_search'];
			$s_where[]= " busCode LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($search['search_status'] > -1){
			$where.= " AND status= ".$search['search_status'];
		}
		$order = " ORDER BY id DESC";
		return $db->fetchAll($sql.$where.$order);
	}
	function getStudentBus($data=null){
		$db=$this->getAdapter();
		$sql="SELECT id,
		CONCAT( COALESCE(busCode,''),'-',COALESCE((SELECT teacher_name_kh FROM `rms_teacher` WHERE id=driverId LIMIT 1),'') ) AS name
		 FROM `rms_school_bus`  WHERE STATUS= 1";
		if(!empty($data['branch_id'])){
			$sql.=" AND branchId=".$data['branch_id'];
		}
		return $db->fetchAll($sql);
	}
	
	public function getById($id)
	{
		$db=$this->getAdapter();
        $sql="SELECT *  FROM ".$this->_name." WHERE id = ".$db->quote($id);
        $sql.=" LIMIT 1 ";
        $row=$db->fetchRow($sql);
        return $row;
	}


	function  addStudentBus($_data){
      	$db = $this->getAdapter();
        $db->beginTransaction();
		$dbg = new Application_Model_DbTable_DbGlobal();
		$code = $dbg->getTeacherCode($_data['branch_id']);
        try{
			$_arr = array(
				'branch_id' 		 => $_data['branch_id'],
				'teacher_code'		 => $code,
				'teacher_name_kh'	 => $_data['driver_name'],
				'teacher_name_en'	 => $_data['driver_name_en'],
				'sex'				 => $_data['sex'],
				'staff_type'  	 	 => 2,
				'user_name' 		 => $_data['user_name'],
				'password' 			 => md5($_data['password']),
				'tel'  				 => $_data['phone'],
				'email' 			 => $_data['email'],
				'address' 			 => $_data['address'],
				'create_date' 		 => date("Y-m-d"),
				'user_id'	  		 => $this->getUserId(),
			
				);
			$this->_name='rms_teacher';
			if(!empty($_data['is_new_driver'])){
				$driver_id=$_data['driverId'];
			}else{
				$driver_id = $this->insert($_arr);
			}

			$_arr=array(
				'branchId' 		=> $_data['branch_id'],
				'busCode' 		=> $_data['busCode'],
				'busType' 		=> $_data['busType'],
				'driverId'		=> $driver_id,
				'busPlateNo' 	=> $_data['busPlateNo'],
				'note'   		=> $_data['note'],
				'createDate' 	=> date("Y-m-d"),
				'modifyDate' 	=> date("Y-m-d"),
				'status'   		=> 1,
				'userId'	 	=> $this->getUserId(),
			);
			$this->_name="rms_school_bus";
			$this->insert($_arr);
            $db->commit();
        }catch(exception $e){
            Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
            $db->rollBack();
        }
 	}
	function  EditStudentBus($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$dbg = new Application_Model_DbTable_DbGlobal();
		$code = $dbg->getTeacherCode($_data['branch_id']);
		try{
			
			if(!empty($_data['is_new_driver'])){
				if (!empty($_data['check_change'])){
					$_arr['password']= md5($_data['password']);
					$this->_name='rms_teacher';
					$where=$this->getAdapter()->quoteInto('id=?', $_data['driverId']); 
					$this->update($_arr,$where);
				}
				$driver_id=$_data['driverId'];
			}else{
				$_arr = array(
					'branch_id' 		 => $_data['branch_id'],
					'teacher_code'		 => $code,
					'teacher_name_kh'	 => $_data['driver_name'],
					'teacher_name_en'	 => $_data['driver_name_en'],
					'sex'				 => $_data['sex'],
					'staff_type'  	 	 => 2,
					'user_name' 		 => $_data['user_name'],
					'password' 			 => md5($_data['password']),
					'tel'  				 => $_data['phone'],
					'email' 			 => $_data['email'],
					'address' 			 => $_data['address'],
					'create_date' 		 => date("Y-m-d"),
					'user_id'	  		 => $this->getUserId(),
			
				);
				$this->_name='rms_teacher';
				$driver_id = $this->insert($_arr);
			}

		  $status = empty($_data['status'])?0:1;
		  $_arr=array(
			  'branchId' 	=> $_data['branch_id'],
			  'busCode' 	=> $_data['busCode'],
			  'busType' 	=> $_data['busType'],
			  'driverId'	=> $driver_id,
			  'busPlateNo' 	=> $_data['busPlateNo'],
			  'note'   		=> $_data['note'],
			  'createDate' 	=> date("Y-m-d"),
			  'modifyDate' 	=> date("Y-m-d"),
			  'status'   	=>  $status,
			  'userId'	 	=> $this->getUserId(),
		  );
			$this->_name="rms_school_bus";
			$where=$this->getAdapter()->quoteInto('id=?', $_data['id']); 
			$this->update($_arr,$where);
		  $db->commit();
	  }catch(exception $e){
		  Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		  echo $e->getMessage();
		  $db->rollBack();
	  }
   }


}