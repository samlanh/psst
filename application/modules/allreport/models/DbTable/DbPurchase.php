<?php
class Allreport_Model_DbTable_DbPurchase extends Zend_Db_Table_Abstract
{
	public  function getStudentInfo($search){
		$_db = $this->getAdapter();
		$sql = "SELECT stu_id,stu_enname,stu_khname,
		(SELECT name_kh FROM `rms_view` WHERE type=2 AND key_code = sex) as gender
		,stu_code,dob,remark,tel,(SELECT province_kh_name FROM `rms_province` WHERE `province_id`= rms_student.province_id) as pro,
		father_phone,mother_phone,email,father_khname,father_enname,father_nation,(SELECT `occu_name` FROM `rms_occupation` WHERE `occupation_id` = father_job)as far_job,(SELECT `occu_name` FROM `rms_occupation` WHERE `occupation_id` = mother_job)as mom_job,
		mother_enname,mother_khname,mother_nation,guardian_khname,guardian_nation,guardian_document,guardian_enname,address,home_num,street_num,village_name,commune_name,district_name,
		(SELECT `occu_name` FROM `rms_occupation` WHERE `occupation_id` = guardian_job)as guar_job,guardian_tel,guardian_email,remark,
		(SELECT name_kh FROM `rms_view` WHERE type=1 AND key_code = status) as status,nationality
		FROM rms_student where status = 1";
		
		$sql.='';
		if(empty($search)){
			$_db->fetchAll($sql);
		}
		if(!empty($search['txtsearch']))
		{
			$s_where = array();
			$s_search = trim($search['txtsearch']);
			$s_where[] = " stu_enname LIKE '%{$s_search}%'";
			$s_where[] = " stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " stu_code LIKE '%{$s_search}%'";
			$s_where[] = " nationality LIKE '%{$s_search}%'";
			// 			$s_where[] = " en_name LIKE '%{$s_search}%'";
			// 			$s_where[] = " sex LIKE '%{$s_search}%'";
			//			$s_where[] = " nationality LIKE '%{$s_search}%'";
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
		}
		
		return $_db->fetchAll($sql);
	}
    function getProductsByLocId($loc_id){
    	$db=$this->getAdapter();
    	$sql="SELECT p.pro_code,p.pro_name,
				       pl.pro_qty,p.pro_price,pl.total_amount,p.date,
				       (SELECT name_kh FROM rms_view WHERE rms_view.key_code=p.status AND rms_view.type=1) AS `status`
				FROM rms_product AS p,rms_product_location AS pl
				WHERE p.id=pl.pro_id AND pl.pro_id=$loc_id";
    	return $db->fetchAll($sql);
    }
    function getPurchaseCodeSuplier($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT sp.branch_id,sp.id,sp.supplier_no,s.sup_name,
				      (SELECT branch_namekh FROM rms_branch WHERE rms_branch.br_id=sp.branch_id) AS branch_name,
				       sp.amount_due,sp.date,
				      (SELECT name_kh FROM rms_view WHERE rms_view.key_code=sp.status AND rms_view.type=1) AS `status`
								FROM rms_supplier_product AS sp,rms_supplier AS s 
								WHERE sp.sup_id=s.id AND sp.status=1";
    	
    	$where="";
//     	$from_date =(empty($search['start_date']))? '1': " sp.date >= '".$search['start_date']." 00:00:00'";
//     	$to_date = (empty($search['end_date']))? '1': " sp.date <= '".$search['end_date']." 23:59:59'";
//     	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " sp.supplier_no LIKE '%{$s_search}%'";
    		$s_where[]=" s.sup_name LIKE '%{$s_search}%'";
    		$s_where[]= " sp.amount_due LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['location'])){
    		$where.=" AND sp.branch_id=".$search['location'];
    	}
    	 
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND sp.status=".$search['status_search'];
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	return $db->fetchAll($sql.$where);
    }
    function getPurchaseSupplierById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT pro_id,qty,qty,cost,amount,note,STATUS FROM rms_supproduct_detail WHERE supproduct_id=1";
    	return $db->fetchRow($sql);
    }
    function getPurchaseName($id){
    	$db=$this->getAdapter();
    	$sql="SELECT sp.supplier_no,s.sup_name,
		       (SELECT branch_namekh FROM rms_branch WHERE rms_branch.br_id=sp.branch_id ) AS brand_name 
		       FROM rms_supplier AS s,rms_supplier_product AS sp 
		       WHERE s.id=sp.sup_id AND sp.sup_id=$id LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getPurchaseProductDetail($pro_id,$search=null){
    	$db=$this->getAdapter();
    			$sql=" SELECT  sp.supplier_no,s.sup_name,
			           (SELECT branch_namekh FROM rms_branch WHERE rms_branch.br_id=sp.branch_id ) AS brand_name ,
			           (SELECT pro_name FROM rms_product WHERE rms_product.id=spd.pro_id) AS pro_id, 
			            spd.qty,spd.qty,spd.cost,spd.amount,spd.date,
		       			(SELECT name_kh FROM rms_view WHERE rms_view.key_code=spd.status AND rms_view.type=1) AS `status`
		       				FROM rms_supplier_product AS sp,rms_supproduct_detail AS spd,rms_supplier As s 
		       				WHERE sp.id=spd.supproduct_id AND s.id=sp.sup_id AND spd.supproduct_id=$pro_id";
    			$where="";
    			if(!empty($search['title'])){
    				$s_where=array();
    				$s_search=addslashes(trim($search['title']));
    				$s_where[]= " sp.supplier_no LIKE '%{$s_search}%'";
    				$s_where[]= " s.sup_name LIKE '%{$s_search}%'";
    				$s_where[]=" spd.qty LIKE '%{$s_search}%'";
    				$s_where[]= " spd.cost LIKE '%{$s_search}%'";
    				$s_where[]= " spd.amount LIKE '%{$s_search}%'";
    				$where.=' AND ('.implode(' OR ', $s_where).')';
    			}
    			if(!empty($search['location'])){
    				$where.=" AND sp.branch_id=".$search['location'];
    			}
    			if($search['status_search']==1 OR $search['status_search']==0){
    				$where.=" AND spd.status=".$search['status_search'];
    			}
    			$dbp = new Application_Model_DbTable_DbGlobal();
    			$sql.=$dbp->getAccessPermission('branch_id');
    	return $db->fetchAll($sql.$where);
    }
    function  getAllPurchase($search=null){
    	$db=$this->getAdapter();
    	$sql=" SELECT  sp.id,sp.supplier_no,s.sup_name,
		           (SELECT branch_namekh FROM rms_branch WHERE rms_branch.br_id=sp.branch_id ) AS brand_name ,
		           (SELECT pro_name FROM rms_product WHERE rms_product.id=spd.pro_id) AS pro_id, 
		            spd.qty,spd.qty,spd.cost,spd.amount,spd.date,
		       			(SELECT name_kh FROM rms_view WHERE rms_view.key_code=spd.status AND rms_view.type=1) AS `status`
       				FROM rms_supplier_product AS sp,rms_supproduct_detail AS spd,rms_supplier AS s 
       				WHERE sp.id=spd.supproduct_id  AND s.id=sp.sup_id";
    	
    	$where="";
    	    	$from_date =(empty($search['start_date']))? '1': " spd.date >= '".$search['start_date']." 00:00:00'";
    	     	$to_date = (empty($search['end_date']))? '1': " spd.date <= '".$search['end_date']." 23:59:59'";
    	     	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " sp.supplier_no LIKE '%{$s_search}%'";
    		$s_where[]= " s.sup_name LIKE '%{$s_search}%'";
    		$s_where[]=" spd.qty LIKE '%{$s_search}%'";
    		$s_where[]= " spd.cost LIKE '%{$s_search}%'";
    		$s_where[]= " spd.amount LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['location'])){
    		$where.=" AND sp.branch_id=".$search['location'];
    	}
    	if(!empty($search['product'])){
    		$where.=" AND spd.pro_id=".$search['product'];
    	}
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND spd.status=".$search['status_search'];
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	return $db->fetchAll($sql.$where);
    }
    
}



