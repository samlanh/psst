<?php

class Global_Model_DbTable_DbSubjectExam extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_subject';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	
    public function getAllSubjectParent($opt=null){
    	$db = $this->getAdapter();
    	if($opt!=null){
    		$sql = "SELECT id,subject_titleen As name FROM rms_subject WHERE `status`=1 AND subject_titleen!=''";
    		return $db->fetchAll($sql);
    	}else{
	    	$sql = "SELECT id,subject_titleen FROM rms_subject WHERE  parent=0 AND is_parent=1 AND `status`=1 AND subject_titleen!='' AND parent=0";
	    	return $db->fetchAll($sql);
    	}
    }
    public function getAllSubjectParentByID($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM rms_subject WHERE id=".$id;
    	return $db->fetchRow($sql);
    }
	public function addNewSubjectExam($_data){
		$db = $this->getAdapter();
		try{
			$sql="SELECT id FROM rms_subject WHERE parent =".$_data['parent'];
			$sql.=" AND subject_titlekh='".$_data['subject_kh']."'";
			$sql.=" AND subject_titleen='".$_data['subject_en']."'";
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
			$_arr=array(
					'parent' 			=> $_data['parent'],
					'subject_titlekh' 	=> $_data['subject_kh'],
					'subject_titleen' 	=> $_data['subject_en'],
					'date' 				=> date("Y-m-d"),
					'status'   			=> 1,
					'schoolOption'   	=> $_data['schoolOption'],
					'is_parent'   		=> $_data['par'],
			        'shortcut'			=> $_data['score_percent'],
					'type_subject'		=> $_data['type_subject'],
					'user_id'	  		=> $this->getUserId()
			);
			return  $this->insert($_arr);
		}catch (Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message("INSERT_FAIL");
		}
	}
	public function updateSubjectExam($_data,$id){
		$_arr=array(
				'parent' 			=> $_data['parent'],
				'subject_titlekh' 	=> $_data['subject_kh'],
				'subject_titleen' 	=> $_data['subject_en'],
				'date' 				=> date("Y-m-d"),
				'status'   			=> $_data['status'],
				'is_parent'   		=> $_data['par'],
				'schoolOption'   	=> $_data['schoolOption'],
				'shortcut'			=> $_data['score_percent'],
				'type_subject'		=> $_data['type_subject'],
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
	function getAllSujectName($search=null){
		$db = $this->getAdapter();
		$sql = " SELECT 
					id,
					subject_titlekh,
					subject_titleen,
					shortcut,
					(select subject_titlekh from rms_subject as s where s.id = ide.parent limit 1) as parent,
					(SELECT so.title FROM `rms_schooloption` AS so WHERE so.id = schoolOption LIMIT 1) AS schoolOption,
					date,
					(SELECT first_name FROM rms_users WHERE id=user_id LIMIT 1) as user_name
			 ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM `rms_subject` AS ide WHERE 1 ";
		
		$order=" order by id DESC";
		$where = '';
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
				$s_where[]= " subject_titlekh LIKE '%{$s_search}%'";
				$s_where[]= " subject_titleen LIKE '%{$s_search}%'";
				$s_where[]= " shortcut LIKE '%{$s_search}%'";
			$where .= ' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status_search']>-1){
			$where.= " AND status = ".$search['status_search'];
		}
		if(!empty($search['schoolOption_search'])){
			$where.= " AND schoolOption  = ".$search['schoolOption_search'];
		}
		return $db->fetchAll($sql.$where.$order);
	}
	public function addNewSubjectajax($_data){
		$_arr=array(
				'parent' 			=> $_data['parent'],
				'subject_titlekh' 	=> $_data['subject_kh'],
				'date' 				=> date("Y-m-d"),
				'status'   			=> $_data['status'],
				'is_parent'   		=> $_data['par'],
				'score_percent'   	=> $_data['score_percent'],
				'user_id'	  		=> $this->getUserId(),
				'access_type'=>1,
		);
		$subject_id = $this->insert($_arr);
		$_model = new Global_Model_DbTable_DbGroup();
		$option = $_model->getAllSubjectStudy();
		$return = array('data'=>$subject_id,'option'=>$option);
		return   $return;
	}
	public function addSubjectajax($_data){
		$this->_name="rms_subject";
		$_arr=array(
				'parent' 			=> $_data['parent'],
				'subject_titlekh' 	=> $_data['subject_kh'],
				'subject_titleen' 	=> $_data['subject_en'],
				'date' 				=> date("Y-m-d"),
				'status'   			=> 1,
				'is_parent'   		=> $_data['par'],
				'shortcut'   		=> $_data['shortcut_sub'],
				'user_id'	  		=> $this->getUserId(),
				'access_type'=>1,
		);
		return $this->insert($_arr);
	}
}