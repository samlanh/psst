<?php

class RsvAcl_Model_DbTable_DbPickupCard extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_pickupcard'; 
	 public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
    
    function addPickupCard($_data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	//print_r($_FILES['photo']); exit();
    	try{
    		$part= PUBLIC_PATH.'/images/card/';
    		$name = $_FILES['photo']['name'];
    		$size = $_FILES['photo']['size'];
    		if (!file_exists($part)) {
    			mkdir($part, 0777, true);
    		}
    		$photo='';
    		$dbg = new Application_Model_DbTable_DbGlobal();
    		if (!empty($name)){
    			$ss = 	explode(".", $name);
    			$image_name = "pickupcard_".date("Y").date("m").date("d").time().".".end($ss);
    			$tmp = $_FILES['photo']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$image_name)){
    				$photo = $image_name;
    			}
    			else
    				$string = "Image Upload failed";
    		}
    		$sql="SELECT id FROM rms_pickupcard WHERE 1";
    		$sql.=" AND title='".$_data['title']."' AND branch_id=".$_data['branch_id'];
    		if (!empty($_data['schoolOption'])){
    			$sql." AND schoolOption =".$_data['schoolOption'];
    		}
    		$rs = $_db->fetchOne($sql);
    		if(!empty($rs)){
    			return -1;
    		}
    		
			$_arrother = array(				
				'default'	=>0,
				'modify_date'	=>date("Y-m-d H:i:s"),
				'user_id'	=>$this->getUserId(),		
			);
			$this->_name ="rms_pickupcard";
			$whereother=" branch_id=".$_data['branch_id']." AND schoolOption=".$_data['schoolOption'];
			$this->update($_arrother, $whereother);
			
	    	$_arr = array(
	    			'branch_id'	    =>$_data['branch_id'],
	    			'title' =>$_data['title'],
	    			'background' =>$photo,
	    			'schoolOption'		=>$_data['schoolOption'],
	    			'display_by'	=>$_data['display_by'],
					'note'	=>$_data['note'],
	    			'default'	=>1,
	    			'status'	=>1,
					'modify_date'	=>date("Y-m-d H:i:s"),
					'create_date'	=>date("Y-m-d H:i:s"),
	    			'user_id'	=>$this->getUserId(),					
	    			);
	    	$this->_name ="rms_pickupcard";
	    	$this->insert($_arr);//insert data
	    	
	    	$_db->commit();
	    	}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    		$_db->rollBack();
	    		echo $e->getMessage(); exit();
	    	}
    }

    public function updateCardMG($_data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
			$id = $_data['id'];
    		$part= PUBLIC_PATH.'/images/card/';
    		$name = $_FILES['photo']['name'];
    		$size = $_FILES['photo']['size'];
    		if (!file_exists($part)) {
    			mkdir($part, 0777, true);
    		}
    		$photo='';
    	
			$sql="SELECT id FROM rms_pickupcard WHERE 1";
    		$sql.=" AND title='".$_data['title']."' AND branch_id=".$_data['branch_id']." AND id!=".$id;
    		if (!empty($_data['schoolOption'])){
    			$sql." AND schoolOption =".$_data['schoolOption'];
    		}
    		$rs = $_db->fetchOne($sql);
    		if(!empty($rs)){
    			return -1;
    		}
			$default=0;
			if(!empty($_data['setdefault'])){
				$_arrother = array(				
					'default'	=>0,
					'modify_date'	=>date("Y-m-d H:i:s"),
					'user_id'	=>$this->getUserId(),		
				);
				$this->_name ="rms_pickupcard";
				$whereother="id!=".$id." AND branch_id=".$_data['branch_id']." AND schoolOption=".$_data['schoolOption'];
				$this->update($_arrother, $whereother);
				
				$default=1;
			}
			$_arr = array(
				'branch_id'	    =>$_data['branch_id'],
				'title' =>$_data['title'],
				'schoolOption'		=>$_data['schoolOption'],
				'display_by'	=>$_data['display_by'],
				'note'	=>$_data['note'],
				'default'	=>$default,
				'status'	=>1,
				'modify_date'	=>date("Y-m-d H:i:s"),
				'user_id'	=>$this->getUserId(),		
			);
    	
			if (!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = "pickupcard_".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['photo']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					$_arr['background']=$image_name;
				}
			}
			$this->_name ="rms_pickupcard";
			$where=$this->getAdapter()->quoteInto("id=?", $id);
			$this->update($_arr, $where);
			
			$_db->commit();
    	}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$_db->rollBack();
    		echo $e->getMessage(); exit();
    	}
    }
   	
    function getAllBranch($search){
    	$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$check = '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
    	$uncheck = '<i class="fa fa-square-o" aria-hidden="true"></i>';
    	$sql = "SELECT b.id,
    	CASE    
				WHEN  b.default = 1 THEN '$check'
				WHEN  b.default = 0 THEN '$uncheck'
				END AS student_statustitle,
    	b.title,
    	(SELECT bs.branch_nameen FROM rms_branch as bs WHERE bs.br_id =b.branch_id LIMIT 1) as branch_name,
    	(SELECT sp.title FROM `rms_schooloption` AS sp WHERE sp.id = b.schoolOption LIMIT 1) AS schoolOption,
    	b.note   ";
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->caseStatusShowImage("b.status");
    	$sql.=" FROM rms_pickupcard AS b ";
    	
    	$where = ' WHERE  b.title !="" ';   	
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=trim(addslashes($search['adv_search']));
    		$s_where[]=" b.title LIKE '%{$s_search}%'";
    		$s_where[]=" b.note LIKE '%{$s_search}%'";
    		
    		$where.=' AND ('.implode(' OR ',$s_where).')';
    	}
		if($search['status']>-1){
			$where.= " AND b.status = ".$search['status'];
		}
		
		$where.= $dbp->getAccessPermission('b.branch_id');
		
    	$order=' ORDER BY b.id DESC';
   		return $db->fetchAll($sql.$where.$order);
   }
      
 function getCardmgById($id){
 		
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM
    	$this->_name ";
    	$where = " WHERE `id`= $id";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->caseStatusShowImage("status");
    	
   		return $db->fetchRow($sql.$where);
    }
    
}  
	  

