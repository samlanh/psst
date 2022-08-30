<?php

class Accounting_Model_DbTable_DbProductset extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_product';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    	 
    }


    function getAllProductOption(){
    	$db=$this->getAdapter();
    	$sql="SELECT id, pro_name FROM rms_product WHERE status=1 AND pro_name!='' AND sale_set=0  ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('br_id');
    	$sql.=" ORDER BY id DESC ";
    	$rows=$db->fetchAll($sql);
    	$options = '';
    	if(!empty($rows))foreach($rows as $value){
    		$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['pro_name'], ENT_QUOTES).'</option>';
    	}
    	return $options;
    }
    function getProCode(){
    	$db = $this->getAdapter();
    	$sql="SELECT id FROM rms_product WHERE STATUS=1 ORDER BY id DESC LIMIT 1";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$pre='P-';
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    function getProductById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM rms_product WHERE id=$id";
    	return $db->fetchRow($sql);
    }
    function getProLocationById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT id,pro_id,branch_id,pro_qty,total_amount,note FROM rms_product_location WHERE pro_id=$id";
    	return $db->fetchAll($sql);
    }
    function getProductCategory(){ //if type=1 category , if type=2 measure 
    	$db=$this->getAdapter();
    	$sql="SELECT id,name_kh as name FROM rms_pro_category WHERE `status`=1 ";
    	return $db->fetchAll($sql);
    }
//     /////////////Category 
    function getAllCategory($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    			id,
    			name_kh,
    			`date`,
    			(select name_en from rms_view where type=1 and key_code=status) as status
    		  FROM rms_pro_category  WHERE status=1 ";
    	$where=" ";
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " name_kh LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}	  
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND status=".$search['status_search'];
    	}  		
    		    	
    	$order=" ORDER BY id DESC";
    	//echo $sql.$where;
    	return $db->fetchAll($sql.$where.$order);
    }

//     }
    function getLocation(){
    	$db=$this->getAdapter();
    	$sql="SELECT br_id as id,branch_nameen AS `name` FROM rms_branch WHERE `status`=1";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('br_id');
    	return $db->fetchAll($sql);
    }

    /*pro duct set group */
    function getAllProductSetGroup($search=null){
    	$db = $this->getAdapter();
    	$sql=" SELECT p.id,p.pro_code,
	    	p.pro_name,
	    	(SELECT cat.name_kh FROM rms_pro_category AS cat WHERE cat.id=p.cat_id LIMIT 1) As cat_name,
	    	p.pro_price,p.date,p.status
	    	FROM rms_product AS p
	    	WHERE sale_set = ".$search['sale_set'];
    	 
    	$where="";
    	$from_date =(empty($search['start_date']))? '1': " p.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " p.date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]=" p.pro_code LIKE '%{$s_search}%'";
    		$s_where[]=" p.pro_name LIKE '%{$s_search}%'";
    		$s_where[]=" p.pro_size LIKE '%{$s_search}%'";
    		$s_where[]=" p.pro_size LIKE '%{$s_search}%'";
    		$s_where[]=" p.pro_price LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND p.status=".$search['status_search'];
    	}
//     	$dbp = new Application_Model_DbTable_DbGlobal();
//     	$where.=$dbp->getAccessPermission('branch_id');
    	$order=" ORDER BY id DESC";
//     	echo $sql.$where.$order;exit();
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getProDetailById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT *,
    	(SELECT p.pro_price FROM `rms_product` AS p WHERE p.id = rms_product_setdetail.subpro_id LIMIT 1) AS pro_price
    		FROM rms_product_setdetail 
    	WHERE pro_id=$id";
    	return $db->fetchAll($sql);
    }  
}