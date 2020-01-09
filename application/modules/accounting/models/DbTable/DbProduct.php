<?php

class Accounting_Model_DbTable_DbProduct extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_product';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    	 
    }
    function getAllProduct($search=null){
    	$db = $this->getAdapter();
    	$sql=" SELECT p.id,p.pro_code,
			 (SELECT CONCAT(branch_nameen) FROM rms_branch WHERE rms_branch.br_id=pl.brand_id) AS branch_name,
				p.pro_name,(SELECT cat.name_kh FROM rms_pro_category AS cat WHERE cat.id=p.cat_id) As cat_name,
				(select name_en from rms_view where type=11 and key_code=pro_type) as pro_type,
			    p.cost, 
			    p.pro_price, 
				pl.pro_qty,p.date,p.status
				FROM rms_product AS p,rms_product_location AS pl
				WHERE p.id=pl.pro_id and sale_set = ".$search['sale_set'];
    	
    	$where="";
    	$from_date =(empty($search['start_date']))? '1': " p.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " p.date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " p.pro_code LIKE '%{$s_search}%'";
    		$s_where[]=" p.pro_name LIKE '%{$s_search}%'";
    		$s_where[]= " p.pro_size LIKE '%{$s_search}%'";
    		$s_where[]= " p.pro_size LIKE '%{$s_search}%'";
    		$s_where[]= " p.pro_price LIKE '%{$s_search}%'";
    		$s_where[]= " pl.pro_qty LIKE '%{$s_search}%'";
    		$s_where[]= " pl.total_amount LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['location'])){
    		$where.=" AND pl.brand_id=".$search['location'];
    	}
    	if(!empty($search['category_id'])){
    		$where.=" AND p.cat_id=".$search['category_id'];
    	}
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND p.status=".$search['status_search'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('brand_id');
    	$order=" ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
  
    function getBrandLocation(){
    	$db=$this->getAdapter();
    	$sql="SELECT br_id AS id,CONCAT(branch_nameen) AS `name` FROM rms_branch WHERE STATUS=1  ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('br_id');
    	$sql.=" ORDER BY br_id DESC";
    	$rows=$db->fetchAll($sql);
        $options = '';
        if(!empty($rows))foreach($rows as $value){
        	$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
        }
        return $options;
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
    	$sql="SELECT id,pro_id,brand_id,pro_qty,total_amount,note FROM rms_product_location WHERE pro_id=$id";
    	return $db->fetchAll($sql);
    }
    function getProductCategory(){ //if type=1 category , if type=2 measure 
		$_dbg = new Application_Model_DbTable_DbGlobal();
		return $_dbg->getAllItems(3);
		
    }
    /////////////Category 
    function getAllCategory($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    			id,
    			name_kh,
    			`date`,
    			status
    		  FROM rms_pro_category  WHERE 1 ";
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
    public function addCategory($_data){
    	//print_r($_data);exit();
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$_arr = array(
    				'name_kh'=>$_data['name_kh'],
    				'date'=>date("Y-m-d"),
    				'status'=>$_data['status'],
    				'user_id'=>$this->getUserId()
    		);
    		$this->_name='rms_pro_category';
    		if(!empty($_data['id'])){
    			$where=" id=".$_data['id'];
    			$this->update($_arr, $where);
    		}else{
    			$this->insert($_arr);
    		}
    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		echo $e->getMessage();
    	}
    }
    function getGategoryById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT id,name_kh,name_en,type_id,`date`,`status` FROM rms_pro_category WHERE id=$id limit 1";
    	return $db->fetchRow($sql);
    }
    function getProductName(){
    	$db=$this->getAdapter();
    	$sql="SELECT p.id As id,CONCAT(p.pro_name) AS name FROM rms_product AS p,rms_product_location AS pl 
     			  WHERE p.id=pl.pro_id AND p.status=1 ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('brand_id');
    	$sql.="  ORDER BY id DESC";
    	return $db->fetchAll($sql);
    }
    function getLocation(){
    	$db=$this->getAdapter();
    	$sql="SELECT br_id as id,branch_nameen AS `name` FROM rms_branch WHERE `status`=1";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('br_id');
    	return $db->fetchAll($sql);
    }

    function AddProCate($data){
    	$this->_name="rms_pro_category";
    	$array = array(
    				'name_kh'=>$data['title'],
	    			'date'=>date('Y-m-d'),
	    			'user_id'=>$this->getUserId(),
    			);
    	return $this->insert($array);
    }
 /*pro duct set group */
    function getAllProductSetGroup($search=null){
    	$db = $this->getAdapter();
    	$sql=" SELECT p.id,p.pro_code,
    	p.pro_name,(SELECT cat.name_kh FROM rms_pro_category AS cat WHERE cat.id=p.cat_id LIMIT 1) As cat_name,
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
    		$s_where[]= " p.pro_code LIKE '%{$s_search}%'";
    		$s_where[]=" p.pro_name LIKE '%{$s_search}%'";
    		$s_where[]= " p.pro_size LIKE '%{$s_search}%'";
    		$s_where[]= " p.pro_size LIKE '%{$s_search}%'";
    		$s_where[]= " p.pro_price LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND p.status=".$search['status_search'];
    	}
//     	$dbp = new Application_Model_DbTable_DbGlobal();
//     	$where.=$dbp->getAccessPermission('brand_id');
    	$order=" ORDER BY id DESC";
//     	echo $sql.$where.$order;exit();
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getProDetailById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM rms_product_setdetail WHERE pro_id=$id";
    	return $db->fetchAll($sql);
    }  
}