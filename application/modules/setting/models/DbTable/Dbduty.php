<?php

class Setting_Model_DbTable_Dbduty extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_duty';   
  
    function addDutySetting($_data){
		$dbg = new Application_Model_DbTable_DbGlobal();
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
    		$part= PUBLIC_PATH.'/images/logo/';
    		if (!file_exists($part)) {
    			mkdir($part, 0777, true);
    		}
    		
			$admin_stamp = $_FILES['stamp']['name'];
    		$imgstamp='';
    		if (!empty($admin_stamp)){
    			$ss = 	explode(".", $admin_stamp);
    			$image_name = "duty_stamp".date("Y").date("m").date("d").time().".".end($ss);
    			$tmp = $_FILES['stamp']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$image_name)){
    				$imgstamp = $image_name;
    			}
    			else
    				$string = "Image Upload failed";
    		}

			$admin_signature = $_FILES['signature']['name'];
    		$imgsignature='';
    		if (!empty($admin_signature)){
    			$ss = 	explode(".", $admin_signature);
    			$image_name = "duty_signature".date("Y").date("m").date("d").time().".".end($ss);
    			$tmp = $_FILES['signature']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$image_name)){
    				$imgsignature = $image_name;
    			}
    			else
    				$string = "Image Upload failed";
    		}
    	
	    	$_arr = array(
	    			
	    			'duty_namekh' 	=>$_data['duty_namekh'],
	    			'duty_nameen' 	=>$_data['duty_nameen'],
					'positionkh' 	=>$_data['positionkh'],
	    			'positionen' 	=>$_data['positionen'],
	    			'signature'		=>$imgsignature,
	    			'stamp'			=>$imgstamp,
					'createDate' 	=>date("Y-m-d"),
					'userId' 		=>$dbg->getUserId(),
	    			
	    		);
	    	$this->_name ="rms_duty";
	    	$this->insert($_arr);
	    	
	    	$_db->commit();
    	}catch(Exception $e){
    		Application_Form_FrmMessage::message($this->tr->translate("APPLICATION_ERROR"));
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$_db->rollBack();
    	}
    }
 
    public function updateDutySetting($_data,$id){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
			
    	$part= PUBLIC_PATH.'/images/logo/';
    	if (!file_exists($part)) {
    		mkdir($part, 0777, true);
    	}

    	$dbg = new Application_Model_DbTable_DbGlobal();
		$status = empty($_data['status'])?0:1;
    		
    	$_arr = array(

			'duty_namekh' 	=>$_data['duty_namekh'],
			'duty_nameen' 	=>$_data['duty_nameen'],
			'positionkh' 	=>$_data['positionkh'],
			'positionen' 	=>$_data['positionen'],
			'modifyDate' 	=>date("Y-m-d"),
			'userId' 		=>$dbg->getUserId(),
			'status' 		=>$status,
    			
    		);
    
    	
    	$name = $_FILES['signature']['name'];
    	if (!empty($name)){
    		$ss = 	explode(".", $name);
    		$image_name = "duty_signature".date("Y").date("m").date("d").time().".".end($ss);
    		$tmp = $_FILES['signature']['tmp_name'];
    		if(move_uploaded_file($tmp, $part.$image_name)){
    			$_arr['signature']=$image_name;
    		}
    	}
		if (!empty($name) and file_exists($part . $_data['old_prin_sign'])) { //delelete old file
			unlink($part . $_data['old_prin_sign']);
		}
    	
    	$stamp = $_FILES['stamp']['name'];
    	if (!empty($stamp)){
    		$ss = 	explode(".", $stamp);
    		$image_name = "duty_stamp".date("Y").date("m").date("d").time().".".end($ss);
    		$tmp = $_FILES['stamp']['tmp_name'];
    		if(move_uploaded_file($tmp, $part.$image_name)){
    			$_arr['stamp']=$image_name;
    		}
    	}
		if (!empty($stamp) and file_exists($part . $_data['old_prin_stamp'])) { //delelete old file
			unlink($part . $_data['old_prin_stamp']);
		}
    	$where=$this->getAdapter()->quoteInto("id=?", $id);
    	$this->update($_arr, $where);
    	
    	$_db->commit();
    	}catch(Exception $e){
    		Application_Form_FrmMessage::message($this->tr->translate("APPLICATION_ERROR"));
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$_db->rollBack();
    	}
    }
   	
    function getAllDutySetting($search){
    	$db = $this->getAdapter();
    	$sql = "SELECT d.id,
    			d.duty_namekh,
				d.duty_nameen,
    			d.positionkh,
    			d.positionen,
				d.createDate,
		    	d.status
				 FROM rms_duty AS d ";
    	$where = ' WHERE 1 ';   	
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=trim(addslashes($search['adv_search']));
    		$s_where[]=" d.duty_namekh LIKE '%{$s_search}%'";
    		$s_where[]=" d.duty_nameen LIKE '%{$s_search}%'";
    		$s_where[]=" d.positionkh LIKE '%{$s_search}%'";
    		$s_where[]=" d.positionen LIKE '%{$s_search}%'";
    	
    		$where.=' AND ('.implode(' OR ',$s_where).')';
    	}
		if($search['status']>-1){
			$where.= " AND d.status = ".$search['status'];
		}
    	$order=' ORDER BY d.id DESC';
   return $db->fetchAll($sql.$where.$order);
   }
      
 	function getDutyById($id){
 		
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM
    	$this->_name ";
    	$where = " WHERE `id`= $id" ;  
   		return $db->fetchRow($sql.$where);
    }

    
    // function checkuDuplicatePrefix($data){
    // 	$db = $this->getAdapter();
    // 	$sql="
    // 	SELECT
    // 	* FROM rms_branch AS i
    // 	WHERE i.prefix='".$data['prefix_code']."'
    // 	 ";
    // 	if (!empty($data['id'])){
    // 		$sql.=" AND i.br_id != ".$data['id'];
    // 	}
    // 	$sql.=" LIMIT 1 ";
    // 	$row = $db->fetchRow($sql);
    // 	if (!empty($row)){
    // 		return 1;
    // 	}
    // 	return 0;
    // }
}