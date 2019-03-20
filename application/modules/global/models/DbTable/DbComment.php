<?php

class Global_Model_DbTable_DbComment extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_comment';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
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
					'create_date' => date("Y-m-d H:i:s"),
					'user_id'	  => $this->getUserId()
			);
			$this->insert($_arr);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("INSERT_FAIL");
			echo $e->getMessage();
		}
	}
	public function getCommentById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_comment WHERE id = $id LIMIT 1";
		return $db->fetchRow($sql);
	}
	public function updateComment($data,$id){
		$_arr=array(
			'comment'	  	=> $data['comment'],
			'status'   		=> $data['status'],
			'user_id'	  	=> $this->getUserId()
		);
		$where=$this->getAdapter()->quoteInto("id=?", $id);
		$this->update($_arr, $where);
	}
	function getAllComment($search){
		$db = $this->getAdapter();
		$sql = " SELECT 
					id,
					comment,
					create_date,
					(SELECT CONCAT(first_name) FROM rms_users WHERE id=user_id )AS user_name,
					status
				FROM 
					rms_comment 
				WHERE 
					comment != '' 
			";
		
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