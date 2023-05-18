<?php

class RsvAcl_Model_DbTable_DbBranch extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_branch';   
    function getCheckHasBranch(){
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM `rms_branch` AS b WHERE b.status=1 LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function addbranch($_data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
    		$part= PUBLIC_PATH.'/images/logo/';
    		$name = $_FILES['photo']['name'];
    		$size = $_FILES['photo']['size'];
    		if (!file_exists($part)) {
    			mkdir($part, 0777, true);
    		}
    		$photo='';
			$photo= empty($_data['oldLogo'])?'':$_data['oldLogo'];
    		$dbg = new Application_Model_DbTable_DbGlobal();
    		if (!empty($name)){
    			$ss = 	explode(".", $name);
    			$image_name = "branch_".date("Y").date("m").date("d").time().".".end($ss);
    			$tmp = $_FILES['photo']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$image_name)){
    				$photo = $image_name;
    			}
    			else
    				$string = "Image Upload failed";
    		}
    		
    		$principalsign = $_FILES['principalsign']['name'];
    		$imgsignature='';
			$imgsignature= empty($_data['oldSignature'])?'':$_data['oldSignature'];
    		if(!empty($principalsign)){
    			$ss = 	explode(".", $principalsign);
    			$image_name = "signature_".date("Y").date("m").date("d").time().".".end($ss);
    			$tmp = $_FILES['principalsign']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$image_name)){
    				$imgsignature = $image_name;
    			}
    			else
    				$string = "Image Upload failed";
    		}
    		
    		$stamp = $_FILES['stamp']['name'];
    		$imgstamp='';
			$imgstamp= empty($_data['oldStamp'])?'':$_data['oldStamp'];
    		if (!empty($stamp)){
    			$ss = 	explode(".", $stamp);
    			$image_name = "stamp".date("Y").date("m").date("d").time().".".end($ss);
    			$tmp = $_FILES['stamp']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$image_name)){
    				$imgstamp = $image_name;
    			}
    			else
    				$string = "Image Upload failed";
    		}
    		
    		$sql="SELECT br_id FROM rms_branch WHERE 1 ";
    		$sql.=" AND branch_nameen='".$_data['branch_nameen']."'";
    		if (!empty($_data['main_branch_id'])){
    			$sql." AND parent =".$_data['main_branch_id'];
    		}
    		$rs = $_db->fetchOne($sql);
    		if(!empty($rs)){
    			return -1;
    		}
    		
    		$schooloption="";
    		if (!empty($_data['selector'])){
	    		foreach ($_data['selector'] as $rs){
	    			if (empty($schooloption)){
	    				$schooloption = $rs;
	    			}else { $schooloption = $schooloption.",".$rs;
	    			}
	    		}
    		}
			$title = trim($_data['branch_namekh']);
			$titleEn = empty($_data['branch_nameen'])?$title:$_data['branch_nameen'];
			$titleEn = trim($titleEn);
	    	$_arr = array(
	    			'parent'	    =>$_data['main_branch_id'],
	    			'school_namekh' =>$_data['school_namekh'],
	    			'school_nameen' =>$_data['school_nameen'],
	    			'branch_nameen' =>$titleEn,
	    			'branch_namekh' =>$title,
	    			'prefix'		=>$_data['prefix_code'],
	    			'br_address'	=>$_data['br_address'],
	    			'branch_code'	=>$_data['branch_code'],
	    			'branch_tel'	=>$_data['branch_tel'],
	    			'branch_tel1'	=>$_data['branch_tel1'],
	    			'fax'		    =>$_data['fax'],
	    			'email'		    =>$_data['email'],
	    			'website'		=>$_data['website'],
	    			'other'			=>$_data['branch_note'],
	    			'status'		=>1,	    			
	    			'displayby'		=>2,
	    			'photo'   	    => $photo,
	    			'schooloptionlist'	=>$schooloption,
	    			'color'			=>$_data['color'],
	    			'card_type'		=>$_data['card_type'],
	    			'centereys'		=>$_data['centereys'],
	    			'officeeys'		=>$_data['officeeys'],
	    			'workat'		=>$_data['workat'],
	    			'principal'		=>$_data['principal'],
	    			'signature'		=>$imgsignature,
	    			'stamp'			=>$imgstamp,
	    			
	    		);
	    	$this->_name ="rms_branch";
	    	$this->insert($_arr);
	    	
	    	$_db->commit();
    	}catch(Exception $e){
    		Application_Form_FrmMessage::message($this->tr->translate("APPLICATION_ERROR"));
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$_db->rollBack();
    	}
    }
    function getAllSchoolOption(){
    	$db = $this->getAdapter();
    	$this->_name = "rms_schooloption";
    	$sql="SELECT s.id, s.title AS name FROM $this->_name AS s WHERE s.status = 1";
    	return $db->fetchAll($sql);
    }
    public function updateBranch($_data,$id){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
    		$part= PUBLIC_PATH.'/images/logo/';
    		$name = $_FILES['photo']['name'];
    		if (!file_exists($part)) {
    			mkdir($part, 0777, true);
    		}
    		$photo='';
    		$dbg = new Application_Model_DbTable_DbGlobal();
    		
    		$schooloption="";
    		if (!empty($_data['selector'])){
    			foreach ($_data['selector'] as $rs){
    				if (empty($schooloption)){
    					$schooloption = $rs;
    				}else { $schooloption = $schooloption.",".$rs;
    				}
    			}
    		}
			$status = empty($_data['status'])?0:1;
    		
    	$_arr = array(
    			'parent'		=>$_data['main_branch_id'],
    			'school_namekh' =>$_data['school_namekh'],
    			'school_nameen' =>$_data['school_nameen'],
    			'branch_nameen'	=>$_data['branch_nameen'],
    			'branch_namekh'	=>$_data['branch_namekh'],
    			'prefix'      	=>$_data['prefix_code'],
    			'br_address'	=>$_data['br_address'],
    			'branch_code'	=>$_data['branch_code'],
    			'branch_tel'	=>$_data['branch_tel'],
    			'branch_tel1'	=>$_data['branch_tel1'],
    			'fax'			=>$_data['fax'],
    			'email'		    =>$_data['email'],
    			'website'		=>$_data['website'],
    			'other'			=>$_data['branch_note'],
    			'status'		=>$status,
    			'displayby'		=>2,
    			'schooloptionlist'=>$schooloption,
    			'color'			=>$_data['color'],
    			'card_type'		=>$_data['card_type'],
    			'centereys'		=>$_data['centereys'],
    			'officeeys'		=>$_data['officeeys'],
    			'workat'		=>$_data['workat'],
    			'principal'		=>$_data['principal'],
    			
    		);
    	if (!empty($name)){
    		$ss = 	explode(".", $name);
    		$image_name = "branch_".date("Y").date("m").date("d").time().".".end($ss);
    		$tmp = $_FILES['photo']['tmp_name'];
    		if(move_uploaded_file($tmp, $part.$image_name)){
    			$_arr['photo']=$image_name;
    		}
    	}
    	
    	$name = $_FILES['principalsign']['name'];
    	if (!empty($name)){
    		$ss = 	explode(".", $name);
    		$image_name = "signature_".date("Y").date("m").date("d").time().".".end($ss);
    		$tmp = $_FILES['principalsign']['tmp_name'];
    		if(move_uploaded_file($tmp, $part.$image_name)){
    			$_arr['signature']=$image_name;
    		}
    	}
    	
    	$name = $_FILES['stamp']['name'];
    	if (!empty($name)){
    		$ss = 	explode(".", $name);
    		$image_name = "stamp_".date("Y").date("m").date("d").time().".".end($ss);
    		$tmp = $_FILES['stamp']['tmp_name'];
    		if(move_uploaded_file($tmp, $part.$image_name)){
    			$_arr['stamp']=$image_name;
    		}
    	}
    	
    	$where=$this->getAdapter()->quoteInto("br_id=?", $id);
    	$this->update($_arr, $where);
    	
    	$_db->commit();
    	}catch(Exception $e){
    		Application_Form_FrmMessage::message($this->tr->translate("APPLICATION_ERROR"));
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$_db->rollBack();
    	}
    }
   	
    function getAllBranch($search){
    	$db = $this->getAdapter();
    	$sql = "SELECT b.br_id,
    			b.school_namekh,
    			b.school_nameen,
    			b.branch_namekh,
				b.branch_nameen,
		    	(SELECT bs.branch_nameen FROM rms_branch as bs WHERE bs.br_id =b.parent LIMIT 1) as parent_name,
		    	b.prefix,b.branch_code,b.br_address,b.branch_tel,b.branch_tel1,b.fax,
    			b.other,b.status FROM rms_branch AS b  ";
    	$where = ' WHERE  b.branch_nameen !="" ';   	
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=trim(addslashes($search['adv_search']));
    		$s_where[]=" b.prefix LIKE '%{$s_search}%'";
    		$s_where[]=" b.branch_namekh LIKE '%{$s_search}%'";
    		$s_where[]=" b.branch_nameen LIKE '%{$s_search}%'";
    		$s_where[]=" b.br_address LIKE '%{$s_search}%'";
    		$s_where[]=" b.branch_code LIKE '%{$s_search}%'";
    		$s_where[]=" b.branch_tel LIKE '%{$s_search}%'";
    		$s_where[]=" b.fax LIKE '%{$s_search}%'";
    		$s_where[]=" b.other LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ',$s_where).')';
    	}
		if($search['status']>-1){
			$where.= " AND b.status = ".$search['status'];
		}
    	$order=' ORDER BY b.br_id DESC';
   return $db->fetchAll($sql.$where.$order);
   }
      
 	function getBranchById($id){
 		
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM
    	$this->_name ";
    	$where = " WHERE `br_id`= $id" ;  
   		return $db->fetchRow($sql.$where);
    }
    public static function getBranchCode(){
    	$db = new Application_Model_DbTable_DbGlobal();
    	$sql = "SELECT COUNT(br_id) AS amount FROM `rms_branch`";
    	$acc_no= $db->getGlobalDbRow($sql);
    	$acc_no=$acc_no['amount'];
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$pre = "";
    	for($i = $acc_no;$i<3;$i++){
    		$pre.='0';
    	}
    	return "C-".$pre.$new_acc_no;
    }
	
	
    function getAllBranchCount(){
    	$db = $this->getAdapter();
    	$this->_name = "rms_branch";
    	$sql="SELECT COUNT(s.br_id) as cou FROM $this->_name AS s ";
    	return $db->fetchOne($sql);
    }
    
    function checkuDuplicatePrefix($data){
    	$db = $this->getAdapter();
    	$sql="
    	SELECT
    	* FROM rms_branch AS i
    	WHERE i.prefix='".$data['prefix_code']."'
    	 ";
    	if (!empty($data['id'])){
    		$sql.=" AND i.br_id != ".$data['id'];
    	}
    	$sql.=" LIMIT 1 ";
    	$row = $db->fetchRow($sql);
    	if (!empty($row)){
    		return 1;
    	}
    	return 0;
    }
}