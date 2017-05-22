<?php

class Global_Model_DbTable_DbStudentScore extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_subject';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
	
    public function getAllSubjectParent(){
    	$db = $this->getAdapter();
    	$sql = "SELECT id,subject_titleen FROM rms_subject WHERE is_parent=1";
    	return $db->fetchAll($sql);
    }
    
    public function getAllSubjectParentByID($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM rms_subject WHERE id=".$id;
    	return $db->fetchRow($sql);
    }
    
	public function addNewSubjectExam($_data){
		$_arr=array(
				'parent' 			=> $_data['parent'],
				'subject_titlekh' 	=> $_data['subject_kh'],
				'subject_titleen' 	=> $_data['subject_en'],
				'date' 				=> date("Y-m-d"),
				'status'   			=> $_data['status'],
				'is_parent'   		=> $_data['par'],
				'access_type'   	=> $_data['access_type'],
				'user_id'	  		=> $this->getUserId()
		);
		return  $this->insert($_arr);
	}
	public function updateSubjectExam($_data,$id){
		$_arr=array(
				'parent' 			=> $_data['parent'],
				'subject_titlekh' 	=> $_data['subject_kh'],
				'subject_titleen' 	=> $_data['subject_en'],
				'date' 				=> date("Y-m-d"),
				'status'   			=> $_data['status'],
				'is_parent'   		=> $_data['par'],
				'access_type'   	=> $_data['access_type'],
				'user_id'	  		=> $this->getUserId()
		);
		$where=$this->getAdapter()->quoteInto("id=?", $id);
		$this->update($_arr, $where);
   }
	public function getSubexamById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_subject WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
	function getParentName(){
		$db=$this->getAdapter();
		$sql="SELECT id,CONCAT(subject_titleen,'-',subject_titlekh) AS parent FROM rms_subject
			      WHERE parent=0 AND is_parent=1 AND `status`=1 ";
		$order=" ORDER BY id DESC ";
		return  $db->fetchAll($sql.$order);
	}
	function getSujectById($data){
		$db=$this->getAdapter();
		$sql="SELECT id,CONCAT(subject_titleen,'-',subject_titlekh) AS name FROM rms_subject
		       WHERE  is_parent='' AND `status`=1 AND parent =".$data;
		$order=" ORDER BY id DESC ";
		return  $db->fetchAll($sql.$order);
	}
	function getStudyYears(){
		$db=$this->getAdapter();
		$sql="SELECT id,CONCAT(from_academic,'-',to_academic) AS name FROM rms_group WHERE `status`=1";
		$order=" ORDER BY id DESC";
		return $db->fetchAll($sql.$order);
	}
	function getGroupAll(){
		$db=$this->getAdapter();
		$sql="SELECT id,group_code AS `name` FROM rms_group WHERE `status`=1";
		$order=" ORDER BY id DESC";
		return $db->fetchAll($sql.$order);
	}
	function getStudent($data){
		$db=$this->getAdapter();
		$sql="SELECT s.stu_id,s.stu_code,CONCAT(s.stu_enname,' - ',s.stu_khname) AS stu_khname,s.sex,(SELECT CONCAT(major_enname,' - ',major_khname ) AS major_enname
		    FROM rms_major WHERE rms_major.major_id=s.grade) AS grade,s.grade As grade_id
	    	FROM rms_student AS s,rms_group_detail_student AS g  WHERE s.stu_id=g.stu_id AND g.group_id=$data";
		$order=" ORDER BY stu_code ASC";
		return $db->fetchAll($sql.$order);
	}
}

