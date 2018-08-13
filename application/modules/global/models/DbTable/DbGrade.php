<?php

class Global_Model_DbTable_DbGrade extends Zend_Db_Table_Abstract
{
 	protected $_name = 'rms_major';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    
	function getAllDept(){        
		$db = $this->getAdapter();
		$sql="select dept_id as id,en_name as name from rms_dept where en_name!='' AND is_active=1 ";
		return $db->fetchAll($sql);
	}	
	 
	public function getAllGrade($search=''){
		$db = $this->getAdapter();
		$sql = " SELECT m.major_id AS id,m.major_enname,m.shortcut,m.ordering, 
        (select d.en_name from rms_dept AS d where m.dept_id=d.dept_id )AS dept_name,
        m.modify_date,
		(select name_en from rms_view where type=1 and key_code=is_active)
        FROM rms_major AS m WHERE 1";
		$order=" order by m.major_id DESC,dept_id, ordering DESC ";
		$where = '';
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = trim($search['txtsearch']);
			$s_where[] = " m.major_enname LIKE '%{$s_search}%'";
			$s_where[] = " m.major_khname LIKE '%{$s_search}%'";
			$s_where[] = " m.ordering LIKE '%{$s_search}%'";
			$s_where[] = " (select d.en_name from rms_dept AS d where m.dept_id=d.dept_id ) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND m.is_active = ".$db->quote($search['status']);
		}
		return $db->fetchAll($sql.$where.$order);
	}	
	public function AddGrade($_data){
		$db = $this->getAdapter();
		try{
			$sql="SELECT major_id FROM rms_major WHERE dept_id =".$_data['dept'];
			$sql.=" AND major_enname='".$_data['major_enname']."'";
			$sql.=" AND ordering='".$_data['ordering']."'";
			$sql.=" AND shortcut='".$_data['shortcut']."'";
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
		$_arr=array(
				'dept_id'	  => $_data['dept'],
				'major_enname'=> $_data['major_enname'],
				'ordering'	  => $_data['ordering'],
				'shortcut'	  => $_data['shortcut'],
				'modify_date' => Zend_Date::now(),
				'is_active'	  => $_data['status'],
				'user_id'	  => $this->getUserId()
		);
		$major_id = $this->insert($_arr);
		}catch(Exception $e){
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();exit();
		}		
// 		if(!empty($_data['identity'])){
// 			$ids = explode(',', $_data['identity']);
//     		foreach ($ids as $i){
//     				$_arr = array(
//     						'subject_id'=>$major_id,
//     						'teacher_id'=>$_data['teacher_'.$i],
//     						'session'=>$_data['session_'.$i],
//     						'note'=>$_data['note_'.$i],
// 							'date'=>date("Y-m-d"),
//     						'user_id'=>$this->getUserId(),
//     				);
// 					$this->_name='rms_teacher_subject';
//     				$this->insert($_arr);
//     		}
// 		}
	}
	public function getMajorById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_major WHERE major_id = ".$db->quote($id);
		return($db->fetchRow($sql));
		
	}
	public function updateGrade($_data,$major_id){

		$_arr=array(
				'dept_id'	  => $_data['dept'],
				'major_enname'  => $_data['major_enname'],
				'ordering'	  => $_data['ordering'],
				'shortcut'	  => $_data['shortcut'],
				'modify_date' => Zend_Date::now(),
				'is_active'	  => $_data['status'],
				'user_id'	  => $this->getUserId()
		);
		$where = " major_id = $major_id ";
		$this->update($_arr, $where);
		
// 		$this->_name='rms_teacher_subject';
// 		$where = "subject_id = $major_id ";
// 		$this->delete($where);
		
// 		if(!empty($_data['identity'])){
// 			$ids = explode(',', $_data['identity']);
//     		foreach ($ids as $i){
//     				$_arr = array(
//     						'subject_id'=>$major_id,
//     						'teacher_id'=>$_data['teacher_'.$i],
//     						'session'	=>$_data['session_'.$i],
//     						'note'		=>$_data['note_'.$i],
// 							'date'		=>date("Y-m-d"),
//     						'user_id'	=>$this->getUserId(),
//     				);
// 					$this->_name='rms_teacher_subject';
//     				$this->insert($_arr);
//     		}
// 		}
	}

	public function addDept($data){
		try{
			$this->_name='rms_dept';
			$_arr=array(
					'en_name'	  => $data['fac_enname'],
					'shortcut'    => $data['shortcut_fac'],
					'modify_date' => date('Y-m-d'),
					'user_id'	  => $this->getUserId()
			);
			$id =  $this->insert($_arr);
				
			if(!empty($data['identity'])){
				$this->_name='rms_dept_subject_detail';
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'dept_id'	=>$id,
							'subject_id'=>$data['subject_study_'.$i],
							'score_in_class'=>$data['scoreinclass_'.$i],
							'score_out_class'=>$data['scoreoutclass_'.$i],
							'score_short'=>$data['scoreshort_'.$i],
							'note'   	=> $data['note_'.$i],
							'date' 		=> date("Y-m-d"),
							'user_id'	=> $this->getUserId()
					);
					$this->insert($arr);
				}
			}
			
			return $id;
			
		}catch(exception $e){
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
		
	public function getTeacher(){
		$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getAllTeacherSubject();
		array_unshift($rows, array ( 'id' => -1,'name' => 'select teacher'));
		$options = '';
		if(!empty($rows))foreach($rows as $value){
			$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
		}
		return $options;
	}
	function getTeacherBySubjectID($id){
		$db = $this->getAdapter();
		$sql = "SELECT  * FROM `rms_teacher_subject`WHERE subject_id=".$id;
		return $db->fetchAll($sql);
	}
	
	function getSession(){
		$db = $this->getAdapter();
		$sql = "select key_code as id,name_en as name from rms_view where type=4 and status=1";
		return $db->fetchAll($sql);
	}
	
	function getNameGradeAll(){
		$db=$this->getAdapter();
		$sql="SELECT m.major_id AS id,CONCAT(m.major_enname,' (',(SELECT d.en_name FROM rms_dept AS d WHERE m.dept_id=d.dept_id ),')') As name, 
		        (SELECT d.en_name FROM rms_dept AS d WHERE m.dept_id=d.dept_id )AS dept_name,
		        m.shortcut,m.modify_date,
				(SELECT name_en FROM rms_view WHERE TYPE=1 AND key_code=is_active)
		        FROM rms_major AS m WHERE 1 AND major_enname!='' ";
		$order=" ORDER BY m.major_id DESC";
		return $db->fetchAll($sql.$order);
	}
	
	
	
}



