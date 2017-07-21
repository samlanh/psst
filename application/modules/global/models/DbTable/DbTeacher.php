<?php

class Global_Model_DbTable_DbTeacher extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_teacher';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    	 
    }
	public function AddNewStaff($_data){
		try{

		// 	add photo ////////////////////////////////////////////////////
		
			$adapter = new Zend_File_Transfer_Adapter_Http();
			$part = PUBLIC_PATH.'/images';
			$adapter->setDestination($part);
			$adapter->receive();
			$photo = $adapter->getFileInfo();
				
			if(!empty($photo['photo']['name'])){
				$pho_name = $photo['photo']['name'];
			}else{
				$pho_name = '';
			}
		////////////////////////////////////////////////////////////////////////	
			
			
			$_arr=array(
					
					'teacher_code' => $_data['code'],
					'teacher_name_kh' => $_data['kh_name'],
					'teacher_name_en' => $_data['en_name'],
					'sex' => $_data['sex'],
					'dob' => $_data['dob'],
					'nationality'  => $_data['nationality'],
			        'tel'   => $_data['phone'],
					'address' => $_data['address'],
					
					'branch_id' => $_data['branch_id'],
					'position' => $_data['staff_position'],
					'expired_date' => $_data['expired_date'],
					
					'note' => $_data['note'],
					'status'   => $_data['status'],
					
					'photo'  => $pho_name,
			        'create_date' => Zend_Date::now(),
			        'user_id'	  => $this->getUserId(),
					
				);
			$this->insert($_arr);
		}catch (Exception $e){
		    echo $e->getMessage();
		}
	}
	public function getTeacherById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_teacher WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function getallSubjectTeacherById($teacher_id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `rms_teacher_subject` WHERE id= ".$db->quote($teacher_id);
		return $db->fetchAll($sql);;
	}
	public function updateStaff($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			
			// 	add photo ////////////////////////////////////////////////////
			
			$adapter = new Zend_File_Transfer_Adapter_Http();
			$part = PUBLIC_PATH.'/images';
			$adapter->setDestination($part);
			$adapter->receive();
			$photo = $adapter->getFileInfo();
			
			if(!empty($photo['photo']['name'])){
				$pho_name = $photo['photo']['name'];
			}else{
				$pho_name = $_data['old_photo'];
			}
			////////////////////////////////////////////////////////////////////////
		
			$_arr=array(
					
					'teacher_code' => $_data['code'],
					'teacher_name_kh' => $_data['kh_name'],
					'teacher_name_en' => $_data['en_name'],
					'sex' => $_data['sex'],
					'dob' => $_data['dob'],
					'nationality'  => $_data['nationality'],
			        'tel'   => $_data['phone'],
					'address' => $_data['address'],
					
					'branch_id' => $_data['branch_id'],
					'position' => $_data['staff_position'],
					'expired_date' => $_data['expired_date'],
					
					'note' => $_data['note'],
					'status'   => $_data['status'],
					
					'photo'  => $pho_name,
			        'user_id'	  => $this->getUserId(),
					
				);
			
		$where=$this->getAdapter()->quoteInto("id=?", $_data["id"]);
		
		$this->update($_arr, $where);
		return $db->commit();
		}catch (Exception $e){
// 			echo $e->getMessage();exit();
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function getAllTeacher($search){
		$db = $this->getAdapter();
		$sql = 'SELECT id, teacher_code, teacher_name_kh,teacher_name_en, 
				(select name_kh from rms_view where rms_view.type=2 and rms_view.key_code=rms_teacher.sex)AS sex, 
				tel,
				(select branch_namekh from rms_branch where br_id = branch_id) as branch_name,
				(select title from rms_staff_position where rms_staff_position.id = position) as position,
				note,
				(select name_en from rms_view where type=1 and key_code =status) as status
				FROM rms_teacher WHERE 1';
		$order_by=" order by id DESC";
		$where = '';
		if(!empty($search['title'])){
		    $s_where = array();
			$s_search = addslashes(trim($search['title']));
			
			$s_where[] = " teacher_code LIKE '%{$s_search}%'";
			$s_where[] = " teacher_name_en LIKE '%{$s_search}%'";
			$s_where[] = " teacher_name_kh LIKE '%{$s_search}%'";
			$s_where[] = " (select title from rms_staff_position where rms_staff_position.id = position) LIKE '%{$s_search}%'";
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
			
		}
		return $db->fetchAll($sql.$where.$order_by);
	}
	
	
	public function addNewPosition($data){//ajax
		$this->_name = "rms_staff_position" ;
		$_arr=array(
				'title' 	=> $data['title'],
				'create_date' 		=> date('Y-m-d'),
				'user_id'	=> $this->getUserId(),
			);
		return $this->insert($_arr);
			
	}
	
	
	function getAllBranch(){
		$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission('br_id');
		$sql="select br_id as id,branch_nameen as name from rms_branch where status = 1  $branch_id  ";
		return $db->fetchAll($sql);
	}
	
	function getAllPosition(){
		$db=$this->getAdapter();
		$sql="select id ,title as name from rms_staff_position where status = 1 ";
		return $db->fetchAll($sql);
	}
	
}

