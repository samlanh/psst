<?php

class Global_Model_DbTable_DbBranch extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_branch';   
    function addbranch($_data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	//print_r($_FILES['photo']); exit();
    	try{
    		$part= PUBLIC_PATH.'/images/';
    		$name = $_FILES['photo']['name'];
    		$size = $_FILES['photo']['size'];
    		if (!file_exists($part)) {
    			mkdir($part, 0777, true);
    		}
    		$photo='';
    		$dbg = new Application_Model_DbTable_DbGlobal();
    		if (!empty($name)){
    			$tem =explode(".", $name);
    			$new_image_name = "branch".date("Y").date("m").date("d").time().".".end($tem);
    			$photo = $dbg->resizeImase($_FILES['photo'], $part,$new_image_name);
    			//$arr['photo']=$photo;
    		}
    		$sql="SELECT br_id FROM rms_branch WHERE parent =".$_data['main_branch_id'];
    		$sql.=" AND branch_nameen='".$_data['branch_nameen']."'";
    		$sql.=" AND prefix='".$_data['prefix_code']."'";
    		$rs = $_db->fetchOne($sql);
    		if(!empty($rs)){
    			return -1;
    		}
    		//	echo $photo; exit();
	    	$_arr = array(
	    			'parent'	    =>$_data['main_branch_id'],
	    			'branch_nameen' =>$_data['branch_nameen'],
	    			'prefix'		=>$_data['prefix_code'],
	    			'br_address'	=>$_data['br_address'],
	    			'branch_code'	=>$_data['branch_code'],
	    			'branch_tel'	=>$_data['branch_tel'],
	    			'fax'		    =>$_data['fax'],
	    			'email'		    =>$_data['email'],
	    			'website'		=>$_data['website'],
	    			'other'			=>$_data['branch_note'],
	    			'status'		=>$_data['branch_status'],	    			
	    			'displayby'		=>2,
	    			'photo'   	    => $photo,
	    			);
	    	$this->insert($_arr);//insert data
	    	
	    	$_db->commit();
	    	}catch(Exception $e){
	    		$_db->rollBack();
	    		echo $e->getMessage(); exit();
	    	}
    }
    public function updateBranch($_data,$id){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	//print_r($_data); exit();
    	try{
    		$part= PUBLIC_PATH.'/images/';
    		$name = $_FILES['photo']['name'];
    		$size = $_FILES['photo']['size'];
    		if (!file_exists($part)) {
    			mkdir($part, 0777, true);
    		}
    		$photo='';
    		$dbg = new Application_Model_DbTable_DbGlobal();
    		if (!empty($name)){
    			$tem =explode(".", $name);
    			$new_image_name = "branch".date("Y").date("m").date("d").time().".".end($tem);
    			$photo = $dbg->resizeImase($_FILES['photo'], $part,$new_image_name);
    			$_arr['photo']=$photo;
    		}
    		//	echo $photo; exit();
    	$_arr = array(
    			'parent'		=>$_data['main_branch_id'],
    			'branch_nameen'	=>$_data['branch_nameen'],
    			'prefix'      	=>$_data['prefix_code'],
    			'br_address'	=>$_data['br_address'],
    			'branch_code'	=>$_data['branch_code'],
    			'branch_tel'	=>$_data['branch_tel'],
    			'fax'			=>$_data['fax'],
    			'email'		    =>$_data['email'],
    			'website'		=>$_data['website'],
    			'other'			=>$_data['branch_note'],
    			'status'		=>$_data['branch_status'],
    			'displayby'		=>2,
    			'photo'   	    => $photo,
    			);
    	$where=$this->getAdapter()->quoteInto("br_id=?", $id);
    	$this->update($_arr, $where);
    	
    	$_db->commit();
    	}catch(Exception $e){
    		$_db->rollBack();
    		echo $e->getMessage(); exit();
    	}
    }
   	
    function getAllBranch($search){
    	$db = $this->getAdapter();
    	$sql = "SELECT b.br_id,b.branch_nameen,
		    	(SELECT bs.branch_nameen FROM rms_branch as bs WHERE bs.br_id =b.parent LIMIT 1) as parent_name,
		    	b.prefix,b.branch_code,b.br_address,b.branch_tel,b.fax,
    			b.other,b.status FROM rms_branch AS b  ";
    	$where = ' WHERE  b.branch_nameen !="" ';   	
//     	(SELECT name_en FROM `rms_view` AS v WHERE v.`type` = 4 AND v.key_code = b.displayby)AS displayby,
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
   //echo $sql.$where;
   return $db->fetchAll($sql.$where.$order);
   }
      
 function getBranchById($id){
 		
    	$db = $this->getAdapter();
    	$sql = "SELECT br_id,parent,prefix,branch_namekh,branch_nameen,br_address,branch_code,branch_tel,fax,email,website,photo,displayby,other,status FROM
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
	
	function addajaxs($_data){
		//return '0000';
    	$_arr = array(
				'branch_namekh'=>$_data['branch_nameen'],
				'branch_nameen'=>$_data['branch_nameen'],
				'fax'=>$_data['fax'], 
				'br_address'=>$_data['br_address'],
    			);
    	return $this->insert($_arr);//insert data
    }
}  
	  

