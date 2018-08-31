<?php

class Registrar_Model_DbTable_DbReportProductNearOutStock extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_product_location';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    
    public function getBranchId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->branch_id;
    }
    
    
	function getProductLocation($search=null){
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$level = $_db->getUserType();
		
		if($level==4){
			$branch_id = $_db->getAccessPermission("brand_id");
		}else{
			$branch_id = "";
		}
		
    	$db=$this->getAdapter();
    	$sql="SELECT 
				p.code AS pro_code,
				CONCAT(p.title) AS pro_name ,
				(SELECT it.title FROM `rms_items` AS it WHERE it.id = p.items_id AND it.type=3 LIMIT 1) AS category_name,
			(SELECT branch_namekh FROM rms_branch WHERE rms_branch.br_id=pl.brand_id LIMIT 1) AS brand_name,
			pl.brand_id,
				pl.pro_qty,
				pl.price AS pro_price,
				p.price,
				pl.total_amount,
				p.create_date AS DATE,
				(SELECT name_kh FROM rms_view WHERE rms_view.key_code=p.status AND rms_view.type=1 LIMIT 1) AS `status` 
			  FROM 
					rms_itemsdetail AS p,
					rms_product_location AS pl
			  WHERE 
				p.id=pl.pro_id
    				$branch_id
    		";
//     	echo $sql;
    	$where=" ";
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " p.pro_code LIKE '%{$s_search}%'";
    		$s_where[]= " p.pro_name LIKE '%{$s_search}%'";
    		$s_where[]= " p.pro_price LIKE '%{$s_search}%'";
    		$s_where[]= "  pl.pro_qty LIKE '%{$s_search}%'";
    		$s_where[]= "  pl.total_amount LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['location'])){
    		$where.=" AND pl.brand_id=".$search['location'];
    	}
    	
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND p.status=".$search['status_search'];
    	}
    	if($search['category_id']>0){
    		$where.=" AND p.cat_id=".$search['category_id'];
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('brand_id');
    	$where.=" ORDER BY pl.brand_id DESC";
    	return $db->fetchAll($sql.$where);
    }
	    
}









