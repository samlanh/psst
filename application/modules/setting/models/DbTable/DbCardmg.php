<?php

class Setting_Model_DbTable_DbCardmg extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_cardbackground'; 
	 public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
    
    function addCardMg($_data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
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
    			$image_name = "cardbackgroun_".date("Y").date("m").date("d").time().".".end($ss);
    			$tmp = $_FILES['photo']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$image_name)){
    				$photo = $image_name;
    			}
    			else
    				$string = "Image Upload failed";
    		}
    		$sql="SELECT id FROM rms_cardbackground WHERE 1";
    		$sql.=" AND title='".$_data['title']."' AND branch_id=".$_data['branch_id'];
    		if (!empty($_data['schoolOption'])){
    			$sql." AND schoolOption =".$_data['schoolOption'];
    		}
    		if (!empty($_data['card_type'])){
    			$sql." AND card_type =".$_data['card_type'];
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
			$this->_name ="rms_cardbackground";
			$whereother=" branch_id=".$_data['branch_id']." AND schoolOption=".$_data['schoolOption']." AND card_type=".$_data['card_type'];
			$this->update($_arrother, $whereother);
			
	    	$_arr = array(
	    			'branch_id'	    =>$_data['branch_id'],
	    			'title' =>$_data['title'],
	    			'background' =>$photo,
	    			'card_type'	=>$_data['card_type'],
	    			'card_prefix'	=>$_data['card_prefix'],
	    			'colorcode'	=>$_data['colorcode'],
	    			'schoolOption'		=>$_data['schoolOption'],
	    			'issue'	=>$_data['issue'],
					'valid'	=>$_data['valid'],
					'note'	=>$_data['note'],
	    			'display_by'	=>$_data['display_by'],
	    			'default'	=>1,
	    			'status'	=>1,
					'modify_date'	=>date("Y-m-d H:i:s"),
					'create_date'	=>date("Y-m-d H:i:s"),
	    			'user_id'	=>$this->getUserId(),	
					
					'stunameleft'	=>$_data['name_left'],
					'stunametop'	=>$_data['name_top'],
					'studentcodeleft'	=>$_data['studentcode_left'],
					'studentcodetop'	=>$_data['studentcode_top'],
					'photo_left'	=>$_data['photo_left'],
					'photo_top'	=>$_data['photo_top'],
					'groupleft'	=>$_data['group_left'],
					'grouptop'	=>$_data['group_top'],
					'qrcodeleft'	=>$_data['code_left'],
					'qrcodetop'	=>$_data['code_top'],
					
	    			);
	    	
	    	$dept = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($dept)){
	    			$dept = $rs;
	    		}else{ $dept = $dept.",".$rs;
	    		}
	    	}
	    	$_arr['degree'] = $dept;
	    	
	    	
	    	$frontimage_name="";
	    	$frontname = $_FILES['front_background']['name'];
	    	if (!empty($frontname)){
	    		$ss = 	explode(".", $frontname);
	    		$frontimage_name = "frontbackgroun_".date("Y").date("m").date("d").time().".".end($ss);
	    		$tmp = $_FILES['front_background']['tmp_name'];
	    		if(move_uploaded_file($tmp, $part.$frontimage_name)){
	    			$_arr['front_background']=$frontimage_name;
	    		}
	    	}
	    	
	    	$this->_name ="rms_cardbackground";
	    	$this->insert($_arr);//insert data
	    	
	    	$_db->commit();
	    	}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    		$_db->rollBack();
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
    	
			$sql="SELECT id FROM rms_cardbackground WHERE 1";
    		$sql.=" AND title='".$_data['title']."' AND branch_id=".$_data['branch_id']." AND id!=".$id;
    		if (!empty($_data['schoolOption'])){
    			$sql." AND schoolOption =".$_data['schoolOption'];
    		}
    		if (!empty($_data['card_type'])){
    			$sql." AND card_type =".$_data['card_type'];
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
				$this->_name ="rms_cardbackground";
				$whereother="id=".$id." AND branch_id=".$_data['branch_id']." AND schoolOption=".$_data['schoolOption']." AND card_type=".$_data['card_type'];
				$this->update($_arrother, $whereother);
				
				$default=1;
			}
			$_arr = array(
				'branch_id'	    =>$_data['branch_id'],
				'title' =>$_data['title'],
				'card_type'	=>$_data['card_type'],
				'card_prefix'	=>$_data['card_prefix'],
				'colorcode'	=>$_data['colorcode'],
				'schoolOption'		=>$_data['schoolOption'],
				'issue'	=>$_data['issue'],
				'valid'	=>$_data['valid'],
				'note'	=>$_data['note'],
				'display_by'	=>$_data['display_by'],
				'default'	=>$default,
				'status'	=>1,
				'modify_date'	=>date("Y-m-d H:i:s"),
				'user_id'	=>$this->getUserId(),	
				
				'stunameleft'	=>$_data['name_left'],
				'stunametop'	=>$_data['name_top'],
				'studentcodeleft'	=>$_data['studentcode_left'],
				'studentcodetop'	=>$_data['studentcode_top'],
				'photo_left'	=>$_data['photo_left'],
				'photo_top'	=>$_data['photo_top'],
				'groupleft'	=>$_data['group_left'],
				'grouptop'	=>$_data['group_top'],
				'qrcodeleft'	=>$_data['code_left'],
				'qrcodetop'	=>$_data['code_top'],
			);
			
			$dept = "";
			if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
				if (empty($dept)){
					$dept = $rs;
				}else{ $dept = $dept.",".$rs;
				}
			}
			$_arr['degree'] = $dept;
			
    	
			if (!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = "cardbackgroun_".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['photo']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					$_arr['background']=$image_name;
				}
			}
			
			$frontimage_name="";
			$frontname = $_FILES['front_background']['name'];
			if (!empty($frontname)){
				$ss = 	explode(".", $frontname);
				$frontimage_name = "frontbackgroun_".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['front_background']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$frontimage_name)){
					$_arr['front_background']=$frontimage_name;
				}
			}
			
			$this->_name ="rms_cardbackground";
			$where=$this->getAdapter()->quoteInto("id=?", $id);
			$this->update($_arr, $where);
			
			$_db->commit();
    	}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$_db->rollBack();
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
    	CASE    
				WHEN  b.card_type = 1 THEN '".$tr->translate("STUDENT")."'
				WHEN  b.card_type = 2 THEN '".$tr->translate("TEACHER")."'
				WHEN  b.card_type = 3 THEN '".$tr->translate("STAFF")."'
				END AS card_type,
				b.valid,
    	b.note  ";
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->caseStatusShowImage("b.status");
    	$sql.=" FROM rms_cardbackground AS b ";
    	
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
    	$order=' ORDER BY b.id DESC';
    	$where.= $dbp->getAccessPermission('b.branch_id');
  		 return $db->fetchAll($sql.$where.$order);
   }
      
	function getCardmgById($id){
 		
	 	$dbp = new Application_Model_DbTable_DbGlobal();
	 	$sql_string = $dbp->caseStatusShowImage("status");
 	
    	$db = $this->getAdapter();
    	$sql = "SELECT * $sql_string FROM
    	$this->_name ";
    	$where = " WHERE `id`= $id";  
    	
   		return $db->fetchRow($sql.$where);
    }
	
	/*
	function getStudentInfo($studentCode,$fileNameAndPart,$fileName){
		$_db= $this->getAdapter();
		$sql="SELECT 
				s.`stu_code`
				FROM `rms_student` AS s 
				WHERE s.`stu_code` = '$studentCode'
		";
		$rs = $_db->fetchOne($sql);
		if(empty($rs)){
			$file=$fileNameAndPart;
			if(file_exists($file)){
				unlink($file);
			}
		}else{
			$_arrother = array(				
				'photo'	=>$fileName	
			);
			$this->_name ="rms_student";
			$whereother=" stu_code='$studentCode'";
			$this->update($_arrother, $whereother);
		}
	}
	*/
	
    
}  
	  

