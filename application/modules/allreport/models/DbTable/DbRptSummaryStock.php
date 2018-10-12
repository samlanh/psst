<?php

class Allreport_Model_DbTable_DbRptSummaryStock extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_product';
    
    public function getAllProduct($search){
    	$db = $this->getAdapter();
    	
    	$from_date =(empty($search['start_date']))? '1': $search['start_date']." 00:00:00";
    	$to_date = (empty($search['end_date']))? '1': $search['end_date']." 23:59:59";
    	
    	$sql="SELECT d.*,
			(SELECT b.branch_namekh FROM rms_branch AS b WHERE b.br_id = pl.brand_id LIMIT 1) AS branch_namekh,
			(SELECT b.branch_nameen FROM rms_branch AS b WHERE b.br_id = pl.brand_id LIMIT 1) AS branch_nameen,
			(SELECT 
			    SUM(pd.qty) 
			  FROM
			    rms_purchase_detail AS pd,
			    rms_purchase AS pu 
			  WHERE pu.id = pd.supproduct_id 
			    AND pd.pro_id = d.id 
			    AND pu.branch_id = pl.`brand_id`
			    AND pu.date >= '$from_date' 
			    AND pu.date <= '$to_date' 
			    AND pu.status = 1 
			  GROUP BY pd.pro_id
			  LIMIT 1
			) AS purchaseQty,
			(SELECT 
		    	SUM(qty_receive) 
		  	FROM
			    rms_request_order AS req,
			    rms_request_orderdetail AS req_d 
		  	WHERE 
		  		req.id = req_d.request_id 
		  		and req_d.pro_id = d.id
		  		and req_d.branch_id = pl.brand_id
		  		AND req.request_date >= '$from_date' 
			    AND req.request_date <= '$to_date'
			    and req.status=1
		  	LIMIT 1) AS request,
			pl.pro_qty
			FROM `rms_itemsdetail` AS d,
			rms_product_location AS pl
			 WHERE 
			d.id = pl.pro_id
			AND 
			 d.items_type=3
			AND d.status=1";
    	
//     	$sql = "SELECT 
// 					  pl.brand_id,
// 					  p.*,
// 					  (SELECT b.branch_namekh FROM rms_branch AS b WHERE b.br_id = pl.brand_id LIMIT 1) AS branch_namekh,
// 					  (SELECT b.branch_nameen FROM rms_branch AS b WHERE b.br_id = pl.brand_id LIMIT 1) AS branch_nameen,
// 					  (SELECT 
// 						    SUM(pd.qty) 
// 						  FROM
// 						    rms_purchase_detail AS pd,
// 						    rms_purchase AS pu 
// 						  WHERE pu.id = pd.supproduct_id 
// 						    AND pd.pro_id = p.id 
// 						    AND pu.branch_id = pl.`brand_id`
// 						    AND pu.date >= '$from_date' 
// 						    AND pu.date <= '$to_date' 
// 						    AND pu.status = 1 
// 						  GROUP BY pd.pro_id
// 						  LIMIT 1
// 					   ) AS purchaseQty,
					  
// 					  (SELECT 
// 						    SUM(sal.qty) 
// 						  FROM
// 						    rms_saledetail AS sal,
// 						    rms_student_payment AS sp,
// 						    rms_program_name AS pn 
// 						  WHERE 
// 						  	pn.service_id = sal.pro_id 
// 						    AND sp.id = sal.payment_id 
// 						    AND pn.ser_cate_id = p.id 
// 						    and sp.branch_id = pl.brand_id
// 						    AND sp.create_date >= '$from_date' 
// 						    AND sp.create_date <= '$to_date' 
// 						    AND sp.is_void = 0 
// 						  GROUP BY sal.pro_id
// 					  	LIMIT 1
// 					  ) AS stockOut,
					  
// 					  (SELECT 
// 					    	SUM(qty_receive) 
// 					  	FROM
// 						    rms_request_order AS req,
// 						    rms_request_orderdetail AS req_d 
// 					  	WHERE 
// 					  		req.id = req_d.request_id 
// 					  		and req_d.pro_id = p.id
// 					  		and req_d.branch_id = pl.brand_id
// 					  		AND req.request_date >= '$from_date' 
// 						    AND req.request_date <= '$to_date'
// 						    and req.status=1
// 					  	LIMIT 1) AS request,
// 					  pl.pro_qty 
// 				from 
// 					rms_product as p,
// 					rms_product_location as pl
// 				where 
// 					p.id=pl.pro_id	
// 					and p.status=1	
//     		";
    	
    	$where=' ';
    	$group_by = " ";
    	$order=" ORDER BY pl.brand_id ASC,d.id ASC";
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " d.title LIKE '%{$s_search}%'";
    		$s_where[] = " d.code LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if(!empty($search['branch_id'])){
    		$where .=' AND pl.brand_id = '.$search['branch_id'];
    	}
    	if(!empty($search['pro_type'])){
    		$where .=' AND d.items_id = '.$search['pro_type'];
    	}
    	if(!empty($search['pro_name'])){
    		$where .=' AND d.id = '.$search['pro_name'];
    	}
//     	echo $sql.$where.$group_by.$order;//exit();
    	return $db->fetchAll($sql.$where.$group_by.$order);
    }
   
    function getAllProductName(){
    	$db = $this->getAdapter();
    	$sql="select id , CONCAT(code,' => ',title) as name FROM rms_itemsdetail WHERE status=1 AND items_type=3";
    	return $db->fetchAll($sql);
    }
    function getAllProductType(){
    	$db = $this->getAdapter();
    	$sql="select id , title as name FROM rms_items WHERE status=1 AND type=3";
    	return $db->fetchAll($sql);
    }
    
}