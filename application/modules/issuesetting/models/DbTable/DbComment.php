<?php

class Issuesetting_Model_DbTable_DbComment extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_comment';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;  	 
    }
	public function addComment($_data){
		$db = $this->getAdapter();
		try{
			$comment = trim($_data['comment']);
			$sql="SELECT id FROM rms_comment WHERE comment='".$comment."'";
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}			
			$_arr=array(
					'comment'	  => $comment,
					'commentType'	  =>$_data['commentType'],
					'create_date' => date("Y-m-d H:i:s"),
					'user_id'	  => $this->getUserId()
			);
			$this->insert($_arr);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("INSERT_FAIL");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getCommentById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_comment WHERE id = $id LIMIT 1";
		return $db->fetchRow($sql);
	}
	public function updateComment($data,$id){
		$status = empty($data['status'])?0:1;
		$_arr=array(
			'comment'	  	=> $data['comment'],
			'commentType'	  =>$data['commentType'],
			'status'   		=> $status,
			'user_id'	  	=> $this->getUserId()
		);
		$where=$this->getAdapter()->quoteInto("id=?", $id);
		$this->update($_arr, $where);
	}
	function getAllComment($search){
		$dbp = new Application_Model_DbTable_DbGlobal();
		$lang = $dbp->currentlang();
    	$name = 'name_kh';
    	if ($lang==1){
    		$name = 'name_kh';
    	}elseif($lang==2){
			$name = 'name_en';
		}

		$db = $this->getAdapter();
		$sql = " SELECT 
					id,
					comment,
					(SELECT $name FROM rms_view WHERE type= 36 AND key_code=commentType )AS commentType,
					create_date,
					(SELECT CONCAT(first_name) FROM rms_users WHERE id=user_id )AS user_name
			";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM `rms_comment` AS c WHERE comment != '' ";
		$order = ' ORDER BY id DESC '; 
		$where = ' ';
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['advance_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['advance_search']));
			$s_where[] = " comment LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status_search']>-1){
			$where.=' AND status='.$search['status_search'];
		}
		return $db->fetchAll($sql.$where.$order);
	}	
}