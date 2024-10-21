<?php

class Global_Model_DbTable_DbProgramType extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_program_type';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;  	 
    }
	public function addProgramType($_data){
		$db = $this->getAdapter();
		try{
			$dept = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($dept)){
	    			$dept = $rs;
	    		}else{ $dept = $dept.",".$rs;
	    		}
	    	}
			$_arr=array(
					'title'	  	  => $_data['title'],
					'titleEn'	  => $_data['title_en'],
					'shortcut'	  => $_data['shortcut'],
					'note'	  	  => $_data['note'],
					'isSingleProgram' => $_data['isSingleProgram'],
					'degreeList'  	=> $dept,
					
					'createDate'  => date("Y-m-d H:i:s"),
					'modifyDate'  => date("Y-m-d H:i:s"),
					'status'  	  => 1,
					'userId'	  => $this->getUserId()
			);
			$this->insert($_arr);
		}catch (Exception $e){
			$db->rollBack();
		}
	}
	public function updateProgramType($_data){
		$db = $this->getAdapter();
		try{

			$dept = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($dept)){
	    			$dept = $rs;
	    		}else{ $dept = $dept.",".$rs;
	    		}
	    	}
			$status = empty($_data['status'])?0:1;
			$_arr=array(
					'title'	  	  => $_data['title'],
					'titleEn'	  => $_data['title_en'],
					'shortcut'	  => $_data['shortcut'],
					'note'	  	  => $_data['note'],
					'isSingleProgram' => $_data['isSingleProgram'],
					'degreeList'  => $dept,
					'modifyDate'  => date("Y-m-d H:i:s"),
					'status'  	  => $status,
					'userId'	  => $this->getUserId()
			);
			$where=" id = ".$_data['id'];
			$this->update($_arr, $where);
		}catch (Exception $e){
		    Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	
	public function getProgramTypeId($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_program_type WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	
	function getAllProgramType($search){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();

		$single=$tr->translate('SINGLE_PROGRAM');
		$multi=$tr->translate('MULTIPLE_PROGRAM');

		$sql = " SELECT 
				id,
				title,
				titleEn,
				shortcut,
				CASE
					WHEN  isSingleProgram = 1 THEN '$single'
					WHEN  isSingleProgram = 2 THEN '$multi'
				END AS isSingleProgram,
				(SELECT GROUP_CONCAT(i.shortcut) FROM rms_items AS i WHERE i.type=1 AND FIND_IN_SET(i.id,degreeList) LIMIT 1) AS degreeList,
				createDate,
				(SELECT  CONCAT(first_name) FROM rms_users WHERE id=userId )AS user_name
			";
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM rms_program_type ";
		
		$where = ' WHERE 1 ';
		$order = ' ORDER BY id DESC '; 
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " title LIKE '%{$s_search}%'";
			$s_where[] = " titleEn LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.=' AND status='.$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);
	}	
	
}

