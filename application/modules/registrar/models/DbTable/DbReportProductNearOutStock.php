<?php

class Registrar_Model_DbTable_DbReportProductNearOutStock extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_product_location';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    
    public function getBranchId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->branch_id;
    }
    
    
	function getProductLocation($search=null){
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$level = $_db->getUserType();
		
		if($level==4){
			$branch_id = $_db->getAccessPermission("branch_id");
		}else{
			$branch_id = "";
		}
		
    	$db=$this->getAdapter();
    	$sql="SELECT 
				p.code AS pro_code,
				CONCAT(p.title) AS pro_name ,
				(SELECT it.title FROM `rms_items` AS it WHERE it.id = p.items_id AND it.type=3 LIMIT 1) AS category_name,
				(SELECT branch_namekh FROM rms_branch WHERE rms_branch.br_id=pl.branch_id LIMIT 1) AS brand_name,
				pl.branch_id,
				pl.pro_qty,
				pl.price AS pro_price,
				p.price,
				p.create_date AS DATE,
				(SELECT name_kh FROM rms_view WHERE rms_view.key_code=p.status AND rms_view.type=1 LIMIT 1) AS `status` 
			  FROM 
					rms_itemsdetail AS p,
					rms_product_location AS pl
			  WHERE 
				p.id=pl.pro_id
    				$branch_id
    		";

    	$where=" ";
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " p.code LIKE '%{$s_search}%'";
    		$s_where[]= " p.title LIKE '%{$s_search}%'";
    		$s_where[]= " pl.price LIKE '%{$s_search}%'";
    		$s_where[]= " pl.pro_qty LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['location'])){
    		$where.=" AND pl.branch_id=".$search['location'];
    	}

    	if($search['category_id']>0){
    		$where.=" AND p.items_id=".$search['category_id'];
    	}
    	if($search['product']>0){
    		$where.=" AND p.id=".$search['product'];
    	}
    	if($search['product_type']>0){
    		$where.=" AND p.product_type=".$search['product_type'];
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	$where.=" ORDER BY pl.branch_id DESC";
    	return $db->fetchAll($sql.$where);
    }
	    
}