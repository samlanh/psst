<?php
class Accounting_Model_DbTable_DbSuspendservice extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_suspendservice';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }

    function getid($stu_id,$service_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT spd.id from rms_student_paymentdetail as spd,rms_student_payment as sp where sp.student_id=".$stu_id." and sp.id=spd.payment_id
    			and spd.service_id=".$service_id." and is_start=1";
    	return $db->fetchOne($sql);
    }
    
    
   public function addSuspendservice($data){
	   	$db = $this->getAdapter();
	   	$db->beginTransaction();
   		try{
	   		$arr = array(
   				'branch_id'	 => $data['branch_id'],
	   			'student_id' => $data['studentId'],
   				'note'		 => $data['note'],
	   			'create_date'=> date("Y-m-d"),
	   			'user_id'	 => $this->getUserId()
	   		);
	   		$id = $this->insert($arr);
	   		
	   		if($data['identity']!=""){
	   			$ids = explode(',', $data['identity']);
		   		foreach ($ids as $i){
			   		$_arr = array(
			   				'suspendservice_id'	=> $id,
			   				'spd_id'			=> $data['spd_id_'.$i],
			   				'reason'			=> $data['reason_'.$i],
			   			);
			   		$this->_name = 'rms_suspendservicedetail';
			   		$this->insert($_arr);
			   		
			   		$array=array(
		   				'stop_type'=> 1, 
			   		);
			   		$where=" stu_id =".$data['studentId']." AND grade=".$data['spd_id_'.$i];
			   		$this->_name = 'rms_group_detail_student';
			   		$this->update($array, $where);
		   		}
	   		}
		   	$db->commit();
   		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
   			$db->rollBack();
   		}
   }
   
   public function editSuspendService($data,$id){
	   	$db = $this->getAdapter();
	   	$db->beginTransaction();
	   	try{
	   		$arr = array(
	   				'branch_id'		=> $data['branch_id'],
		   			'student_id'	=> $data['studentId'],
	   				'note'			=> $data['note'],
		   			'user_id'		=> $this->getUserId()
	   		);
	   		$where=$this->getAdapter()->quoteInto("id=?", $id);
	   		$this->update($arr, $where);
	   		
	   		$row_detail = $this->getSuspendServiceDetailByID($id);
	   		if(!empty($row_detail)){foreach ($row_detail as $row){
	   			$arra = array(
	   				'is_suspend'	=> 0, // 0 = using
		   			'is_start'		=> 1,
	   			);
	   			$this->_name = 'rms_student_paymentdetail';
	   			$where=" id = ".$row['spd_id'];
	   			$this->update($arra, $where);
	   		}}
	   	
	   		$this->_name = 'rms_suspendservicedetail';
	   		$where = "suspendservice_id = $id ";
	   		$this->delete($where);   		
	   		
	   		if($data['identity']!=""){
	   			$ids = explode(',', $data['identity']);
		   		foreach ($ids as $i){
			   		$_arr = array(
			   				'suspendservice_id'	=> $id,
			   				'spd_id'			=> $data['spd_id_'.$i],
			   				'reason'			=> $data['reason_'.$i],
			   			);
			   		$this->_name = 'rms_suspendservicedetail';
			   		$this->insert($_arr);
			   		
			   		$array=array(
			   				'is_suspend'=> 2, // 2=stop
			   				'is_start'	=> 0,
			   		);
			   		$where=" id = ".$data['spd_id_'.$i];
			   		$this->_name = 'rms_student_paymentdetail';
			   		$this->update($array, $where);
		   		}
	   		}
	   		$db->commit();
	   	}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		$db->rollBack();
	   		
	   	}
   }
   public function getSuspendNo(){
	   	$db = $this->getAdapter();
	   	$sql="SELECT id  FROM rms_suspendservice ORDER BY  id DESC LIMIT 1 ";
	   	$acc_no = $db->fetchOne($sql);
	   	$new_acc_no= (int)$acc_no+1;
	   	$acc_no= strlen((int)$acc_no+1);
	   	$pre=0;
	   	for($i = $acc_no;$i<5;$i++){
	   		$pre.='0';
	   	}
	   	return $pre.$new_acc_no;
   }
   public function getAllGerneralOldStudent(){
	   	$db=$this->getAdapter();
	   	$sql="SELECT s.stu_id As stu_id,s.stu_code As stu_code FROM rms_student AS s,rms_student_payment AS sp
	   	WHERE s.stu_id=sp.student_id AND sp.payfor_type=1";
	   	return $db->fetchAll($sql);
   }
   public function getStudentSuspendService($search){
	   	$db = $this->getAdapter();
	   	$dbp = new Application_Model_DbTable_DbGlobal();
	   	$lang = $dbp->currentlang();
	   	if($lang==1){// khmer
	   		$label = "name_kh";
	   		$branch = "branch_namekh";
	   	}else{ // English
	   		$label = "name_en";
	   		$branch = "branch_nameen";
	   	}
	   	$sql="SELECT 
	   				ss.id,
	   				(SELECT $branch from rms_branch where br_id = ss.branch_id LIMIT 1) as branch,
			  	 	s.stu_code AS code,
			   		s.stu_khname as kh_name,
			   		CONCAT(s.last_name,' ',s.stu_enname) AS en_name,
			   		ss.create_date,
			   		(SELECT CONCAT(first_name) from rms_users where rms_users.id = ss.user_id) as user,
			   		(select $label from rms_view as v where v.type=1 and v.key_code = ss.status) as status
	   			FROM 
	   				rms_suspendservice as ss,
	   				rms_student as s
	   			where 
	   				s.stu_id = ss.student_id
	   		";
	   	
	   	$from_date =(empty($search['start_date']))? '1': " ss.create_date >= '".$search['start_date']." 00:00:00'";
	   	$to_date = (empty($search['end_date']))? '1': " ss.create_date <= '".$search['end_date']." 23:59:59'";
	   	$where = " AND ".$from_date." AND ".$to_date;
	   	
	   	$order=" ORDER BY id DESC";
	   	if(empty($search)){
	   		return $db->fetchAll($sql.$order);
	   	}
	   	if(!empty($search['adv_search'])){
	   		$s_where = array();
	   		$s_search = addslashes(trim($search['adv_search']));
	   		$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
	   		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
	   		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
	   		$where .=' AND ( '.implode(' OR ',$s_where).')';
	   	}
	   	if($search['branch_id']>0){
	   		$where.=" AND ss.branch_id=".$search['branch_id'];
	   	}
	   	if(!empty($search['studentId'])){
	   		$where.=" AND ss.student_id=".$search['studentId'];
	   	}
	   	return $db->fetchAll($sql.$where.$order);
   }
   public function getSuspendServiceByID($id){
	   	$db = $this->getAdapter();
	   	$sql="SELECT * FROM rms_suspendservice WHERE id=".$id;
	   	return $db->fetchRow($sql);
   }
   public function getSuspendServiceDetailByID($id){
	   	$db = $this->getAdapter();
	   	
	   	$dbp = new Application_Model_DbTable_DbGlobal();
	   	$lang = $dbp->currentlang();
	   	if($lang==1){// khmer
	   		$label = "title";
	   	}else{ // English
	   		$label = "title_en";
	   	}
	   	$sql="SELECT 
	   				ssd.*,
	   				(select $label from rms_itemsdetail as idt where idt.id = spd.itemdetail_id) as service_name 
	   			FROM 
	   				rms_suspendservicedetail as ssd,
	   				rms_student_paymentdetail as spd
	   			WHERE 
	   				ssd.spd_id = spd.id
	   				and ssd.suspendservice_id = $id
	   		";
	   	return $db->fetchAll($sql);
   }
   
   public function getAllStudentInfo($studentid){
	   	$db=$this->getAdapter();
	   	$sql="select stu_enname,stu_khname,sex from rms_student where stu_id=$studentid limit 1";
	   	return $db->fetchRow($sql);
   }
   
   public function getAllStudentCode(){
	   	$db = $this->getAdapter();
	   	$sql="SELECT stu_id,stu_code  FROM rms_student ORDER BY  stu_code DESC ";
	   	return $db->fetchAll($sql);
   }
  
   
   function getStudentID($acacemic){
	   	$db=$this->getAdapter();
	   	$sql="SELECT stu_id As id,stu_code As name  FROM rms_student  WHERE academic_year=$acacemic";
	   	return $db->fetchAll($sql);
   }
   
   function getStudentName($acacemic){
	   	$db=$this->getAdapter();
	   	$sql="SELECT stu_id As id,CONCAT(stu_khname,'-',stu_enname) as name  FROM rms_student  WHERE academic_year=$acacemic";
	   	return $db->fetchAll($sql);
   }
   
	function getAllSerivesByIdInsuspend($stu_id){
   		$db = $this->getAdapter();
   		$_db = new Application_Model_DbTable_DbGlobal();
   		$currentLang = $_db->currentlang();
   		$service='title_en';
   		if ($currentLang==1){
   			$service='title';
   		}
   		$sql = "SELECT gd.grade AS id,
			      (SELECT $service FROM rms_itemsdetail AS i WHERE i.id = gd.grade LIMIT 1) AS name
			FROM 
				   rms_group_detail_student AS gd
		    WHERE 
				 gd.stu_id=$stu_id
				 AND gd.itemType!=1
			     AND gd.stop_type=0 ";
   		return $db->fetchAll($sql);
	}
}