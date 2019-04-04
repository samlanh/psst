<?php

class Library_Model_DbTable_DbBlock extends Zend_Db_Table_Abstract
{
 	protected $_name = 'rms_blockbook';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    
    function getAllBlock($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    				id,
    				block_name,
    				remark,
    				(SELECT first_name FROM rms_users WHERE id=rms_blockbook.user_id LIMIT 1) AS user_name,
			      	(SELECT name_en FROM rms_view WHERE key_code=rms_blockbook.status LIMIT 1) AS `status`
				FROM 
    				rms_blockbook 
    			WHERE 
    				block_name!='' 
    		";
    	$where = '';
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]= " block_name LIKE '%{$s_search}%'";
    		$s_where[]= " remark LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search["status_search"]>-1){
    	    $where.=' AND `status`='.$search["status_search"];
    	}
    	$order=" ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
 
	public function addBlock($data){
			$db = $this->getAdapter();
			$arr = array(
					'block_name'=>	$data["block_name"],
					'remark'	=>	$data["note"],
					'date'		=>	date('Y-m-d'),
					'status'	=>	$data["status"],
					"user_id"   =>  $this->getUserId(),
			);
			$this->_name = "rms_blockbook";
			$this->insert($arr);
	}
	
	public function updateBlock($data){
		$db = $this->getAdapter();
		$arr = array(
				'block_name'=>	$data["block_name"],
				'remark'	=>	$data["note"],
				'date'		=>	date('Y-m-d'),
				'status'	=>	$data["status"],
				"user_id"   =>  $this->getUserId(),
		);
		$where=" id=".$data['id'];
		$this->_name = "rms_blockbook";
		$this->update($arr, $where);
	}
	
	public function getBlockByid($id){
		$db = $this->getAdapter();
		 $sql=" SELECT * FROM rms_blockbook WHERE id=".$id;
		 return $db->fetchRow($sql);
	}

}



