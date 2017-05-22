<?php
class Allreport_Model_DbTable_DbProductList extends Zend_Db_Table_Abstract
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
     
    function getStudentById($score_id){
    	$db=$this->getAdapter();
    	$sql="SELECT id,attd_id,student_id,(SELECT CONCAT(stu_enname,'-',stu_khname) FROM rms_student AS st WHERE st.stu_id=atd.student_id) AS stu_name,
				   student_code,
				   sex
				FROM rms_attendent_detail AS atd
				WHERE attd_id=$score_id  ORDER BY atd.student_id DESC ";
    	return $db->fetchAll($sql);
    }
    function getProductLocation($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT p.pro_code,CONCAT(p.pro_name,' ',pro_size) AS pro_name ,
    	             (SELECT name_kh FROM `rms_pro_category` WHERE id = p.cat_id limit 1) as category_name,
    	             (SELECT branch_namekh FROM rms_branch WHERE rms_branch.br_id=pl.brand_id limit 1) AS brand_name,pl.brand_id,
    				 pl.pro_qty,p.pro_price,pl.total_amount,
			         p.date,(SELECT name_kh FROM rms_view WHERE rms_view.key_code=p.status AND rms_view.type=1 limit 1) AS `status` 
			        FROM rms_product AS p,rms_product_location AS pl
			       WHERE p.id=pl.pro_id ";
    	$where="";
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
    function getProductsByLocId($loc_id){
    	$db=$this->getAdapter();
    	$sql="SELECT p.pro_code,p.pro_name,
				       pl.pro_qty,p.pro_price,pl.total_amount,p.date,
				       (SELECT name_kh FROM rms_view WHERE rms_view.key_code=p.status AND rms_view.type=1) AS `status`
				FROM rms_product AS p,rms_product_location AS pl
				WHERE p.id=pl.pro_id AND pl.pro_id=$loc_id";
    	return $db->fetchAll($sql);
    }
    function getLocationNameById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT CONCAT(branch_nameen,'-',branch_namekh) AS NAME FROM rms_branch WHERE br_id=$id";
    	return $db->fetchRow($sql);
    }
    
}



