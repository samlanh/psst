<?php

class Allreport_Model_DbTable_DbRptSummaryStock extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_product';
    
    public function getAllProduct($search){
    	$db = $this->getAdapter();
    	
    	$from_date =(empty($search['start_date']))? '1': $search['start_date']." 00:00:00";
    	$to_date = (empty($search['end_date']))? '1': $search['end_date']." 23:59:59";
    	
    	$sql = "SELECT 
					  pl.brand_id,
					  p.*,
					  (SELECT b.branch_namekh FROM rms_branch AS b WHERE b.br_id = pl.brand_id LIMIT 1) AS branch_namekh,
					  (SELECT b.branch_nameen FROM rms_branch AS b WHERE b.br_id = pl.brand_id LIMIT 1) AS branch_nameen,

					  (SELECT 
						    SUM(pd.qty) 
						  FROM
						    rms_supproduct_detail AS pd,
						    rms_supplier_product AS pu 
						  WHERE pu.id = pd.supproduct_id 
						    AND pd.pro_id = p.id 
						    AND pu.date >= '2018-03-21 00:00:00' 
						    AND pu.date <= '2018-03-21 23:59:59' 
						    AND pu.status = 1 
						  GROUP BY pd.pro_id 
						  LIMIT 1
					   ) AS purchaseQty,
					  
					  (SELECT 
						    SUM(sal.qty) 
						  FROM
						    rms_saledetail AS sal,
						    rms_student_payment AS sp,
						    rms_program_name AS pn 
						  WHERE pn.service_id = sal.pro_id 
						    AND sp.id = sal.payment_id 
						    AND pn.ser_cate_id = p.id 
						    AND sp.create_date >= '2018-03-21 00:00:00' 
						    AND sp.create_date <= '2018-03-21 23:59:59' 
						    AND sp.is_void = 0 
						  GROUP BY sal.pro_id
					  	LIMIT 1
					  ) AS stockOut,
					  
					  pl.pro_qty 
				from 
					rms_product as p,
					rms_product_location as pl
				where 
					p.id=pl.pro_id	
					and p.status=1	
    		";
    	
    	$where=' ';
    	$order=" order by pl.brand_id ASC,p.id ASC";
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " pro_code LIKE '%{$s_search}%'";
    		$s_where[] = " pro_name LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if(!empty($search['branch_id'])){
    		$where .=' AND pl.brand_id = '.$search['branch_id'];
    	}
    	if(!empty($search['pro_type'])){
    		$where .=' AND p.cat_id = '.$search['pro_type'];
    	}
    	if(!empty($search['pro_name'])){
    		$where .=' AND p.id = '.$search['pro_name'];
    	}
    	
    	//echo $sql.$where.$order;exit();
    	return $db->fetchAll($sql.$where.$order);
    }
   
    function getAllProductName(){
    	$db = $this->getAdapter();
    	$sql="select id , CONCAT(pro_code,' => ',pro_name) as name from rms_product where status=1";
    	return $db->fetchAll($sql);
    }
    function getAllProductType(){
    	$db = $this->getAdapter();
    	$sql="select id , name_kh as name from rms_pro_category where status=1";
    	return $db->fetchAll($sql);
    }
    
}