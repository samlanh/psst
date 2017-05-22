<?php

class Application_Model_DbTable_DbDept extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_major';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
	
	private function _buildQuery($search = ''){
		$sql = "SELECT 
					CONCAT(u.first_name,' ',u.last_name) As user_name,dept_id,en_name,shortcut
					,modify_date,user_id,is_active
					FROM `rms_dept` AS d,rms_users AS u				
					WHERE d.user_id=u.id ";
		$orderby = " ORDER BY en_name ";
		if(empty($search)){
			return $sql.$orderby;
		}
		$where = '';
			
		return $sql.$where.$orderby;
	}
	
	function getUserList($start, $limit){
		$db = $this->getAdapter();
		$sql = $this->_buildQuery()." LIMIT ".$start.", ".$limit;
		if ($limit == 'All') {
			$sql = $this->_buildQuery();
		}
		return $db->fetchAll($sql);		
	}
	
	function getUserListBy($search, $start, $limit){        
		$db = $this->getAdapter();		
		$sql = $this->_buildQuery($search)." LIMIT ".$start.", ".$limit;
		if ($limit == 'All') {
			$sql = $this->_buildQuery($search);
		}		
		return $db->fetchAll($sql);
	}
	
	function getUserListTotal($search=''){        
		$db = $this->getAdapter();
		$sql = $this->_buildQuery();
		if(!empty($search)){
			$sql = $this->_buildQuery($search);
		}
		$_result = $db->fetchAll($sql); 
		return count($_result);
	}
	
	public function AddNewMajor($_data){
			$_arr=array(
					'dept_id'	  => $_data['dept_id'],
					'major_enname'  => $_data['major_enname'],
					'major_khname'  => $_data['major_khname'],
					'shortcut'	  => $_data['shortcut'],
					'modify_date' => Zend_Date::now(),
					'is_active'	  => $_data['status'],
					'user_id'	  => $this->getUserId()
			);
			return  $this->insert($_arr);
	}
	public function AddNewDepartment($_data){
		$this->_name='rms_dept';
		if(!empty($_data["dept_id"])){
			$this->UpdateDepartment($_data);
		}else{
			try{
			$_arr=array(
					'en_name'	  => $_data['en_name'],
					//'kh_name'	  => $_data['kh_name'],
					'shortcut'    => $_data['shortcut'],
					'modify_date' => new Zend_Date(),
					'is_active'   => $_data['status'],
					'user_id'	  => $this->getUserId()
			);
			$id =  $this->insert($_arr);
			
			if(!empty($_data['identity'])){
			$this->_name='rms_dept_subject_detail';
			$ids = explode(',', $_data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'dept_id'	=>$id,
							'subject_id'=>$_data['subject_study_'.$i],
							'status'    => $_data['status_'.$i],
							'note'   	=> $_data['note_'.$i],
							'date' 		=> date("Y-m-d"),
							'user_id'	=> $this->getUserId()
					);
					$this->insert($arr);
				}
			}
			return $id;
			}catch(exception $e){
			$err =$e->getMessage();
			Application_Form_FrmMessage::message("Application Error!");
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
	}
	public function UpdateDepartment($_data){
		$this->_name='rms_dept';
		$_arr=array(
				//'en_name'	  => $_data['kh_name'],
				'en_name'	  => $_data['en_name'],
				//'kh_name'	  => $_data['kh_name'],
				'shortcut'    => $_data['shortcut'],
				//'modify_date' => Zend_Date::now(),
				'is_active'   => $_data['status'],
				'user_id'	  => $this->getUserId()
		);
		$where = $this->getAdapter()->quoteInto("dept_id=?",$_data["dept_id"]);
		$this->update($_arr, $where);
		
		$this->_name='rms_dept_subject_detail';
		$where = 'dept_id = '.$_data['dept_id'];
		$this->delete($where);
		
		if(!empty($_data['identity'])){
			$this->_name='rms_dept_subject_detail';
			$ids = explode(',', $_data['identity']);
			foreach ($ids as $i){
				$arr = array(
						'dept_id'	=>$_data['dept_id'],
						'subject_id'=>$_data['subject_study_'.$i],
						'status'    => $_data['status_'.$i],
						'note'   	=> $_data['note_'.$i],
						'date' 		=> date("Y-m-d"),
						'user_id'	=> $this->getUserId()
				);
				$this->insert($arr);
			}
		}
	}
	public function getDeptSubjectById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_dept_subject_detail WHERE dept_id = ".$db->quote($id);
		$row=$db->fetchAll($sql);
		return $row;
	}
}

