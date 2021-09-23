<?php

class Setting_Model_DbTable_DbEntrance extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_entrance_exit';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;  	 
    }
	public function addEntrance($_data){
		$db = $this->getAdapter();
		try{
	  		
	  		$arr = array(
	  				'titleKh'	=> $_data['titleKh'],
	  				'titleEn'	=> $_data['titleEn'],
	  				'note'	=> $_data['note'],
	  				'modify_date' 	=> date('Y-m-d H:i:s'),
					'user_id'		=> $this->getUserId()
	  		);
			
			$partAudio= PUBLIC_PATH.'/images/frontFile/audio/';
			if (!file_exists($partAudio)) {
				mkdir($partAudio, 0777, true);
			}
			$audiofileName = $_FILES['audiofile']['name'];
			if (!empty($audiofileName)){
				$tem =explode(".", $audiofileName);
				$newAudiofileName= "audioEntranceNExit_".date("Y").date("m").date("d").time().".".end($tem);
				$tmp = $_FILES['audiofile']['tmp_name'];
				if(move_uploaded_file($tmp, $partAudio.$newAudiofileName)){
					$arr['soundFile']=$newAudiofileName;
					$arr['soundFileKh']=$newAudiofileName;
				}
			}
				
			if(!empty($_data['id'])){
				$arr['status']=$_data['status'];
				$where=$this->getAdapter()->quoteInto("id=?", $_data["id"]);
				$this->update($arr,$where);
			}else{
				$arr['create_date']=date('Y-m-d H:i:s');
				$arr['status']=1;
				$this->insert($arr);
			}
			
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	public function getEntranceById($id){
		$db = $this->getAdapter();
		$sql = " SELECT * FROM rms_entrance_exit WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}

	function getAllEntrance($search){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  id,
				  titleKh,
				  titleEn,
				  create_date,
				  (SELECT  CONCAT(first_name) FROM rms_users WHERE id=user_id )AS user_name
				";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM `rms_entrance_exit`  WHERE 1 ";
		
		$where = '';
		$order = ' ORDER BY id DESC ';
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " titleKh LIKE '%{$s_search}%'";
			$s_where[] = " titleEn LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.=' AND status='.$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);
	}	
}