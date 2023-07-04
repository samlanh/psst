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
    	$_db  = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	
    	$strLang="CASE
	    	WHEN subject_lang=1 THEN   '(ខ្មែរ)'
	    	WHEN subject_lang=2 THEN  '(English)'
    	END ";
    	
    	
    	if($lang==1){// khmer
    		$label = "subject_titlekh";
    	}else{ // English
    		$label = "subject_titleen";
    	}
    	
    	if($opt!=null){
    		$sql = "SELECT id,CONCAT($label,$strLang) As name FROM rms_subject WHERE `status`=1 AND subject_titleen!='' ORDER BY subject_lang ASC ";
    		return $db->fetchAll($sql);
    	}else{
	    	$sql = "SELECT id,CONCAT($label,$strLang) AS name FROM rms_subject WHERE  parent=0 AND is_parent=1 AND `status`=1 AND subject_titleen!='' AND parent=0 ORDER BY subject_lang ASC ";
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
					'subject_lang'		=> $_data['subject_lang'],
					'user_id'	  		=> $this->getUserId()
			);
			return  $this->insert($_arr);
		}catch (Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message("INSERT_FAIL");
		}
	}
	public function updateSubjectExam($_data){
		$status = empty($_data['status'])?0:1;
		$_arr=array(
				'parent' 			=> $_data['parent'],
				'subject_titlekh' 	=> $_data['subject_kh'],
				'subject_titleen' 	=> $_data['subject_en'],
				'date' 				=> date("Y-m-d"),
				'status'   			=> $status,
				'is_parent'   		=> $_data['par'],
				'schoolOption'   	=> $_data['schoolOption'],
				'shortcut'			=> $_data['score_percent'],
				'type_subject'		=> $_data['type_subject'],
				'subject_lang'		=> $_data['subject_lang'],
				'user_id'	  		=> $this->getUserId()
		);
		$id = empty($_data['id'])?0:$_data['id'];
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
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sub_khmer=$tr->translate('STUDY_IN_KHMER');
		$sub_english=$tr->translate('STUDY_IN_ENGLISH');
		$db = $this->getAdapter();
		$sql = " SELECT 
					id,
					subject_titlekh,
					subject_titleen,
					shortcut,
					(SELECT subject_titlekh from rms_subject as s where s.id = ide.parent limit 1) as parent,
					
					CASE 
						WHEN subject_lang=1 THEN   '$sub_khmer'
						WHEN subject_lang=2 THEN  '$sub_english'
					END
					AS subtitle,
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
		if($search['status_search']>-1 AND $search['status_search']!=''){
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
	
	function checkuDuplicate($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				* FROM rms_subject AS i
			WHERE i.subject_titlekh='".$data['title']."' AND i.subject_lang = ".$data['subLang']."
		 ";
		if (!empty($data['id'])){
			$sql.=" AND i.id != ".$data['id'];
		}
		$sql.=" LIMIT 1 ";
		$row = $db->fetchRow($sql);
		if (!empty($row)){
			return 1;
		}
		return 0;
	}
}