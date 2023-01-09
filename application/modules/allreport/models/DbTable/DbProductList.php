<?php
class Allreport_Model_DbTable_DbProductList extends Zend_Db_Table_Abstract
{
	public  function getStudentInfo($search){
		$_db = $this->getAdapter();
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbGb->currentlang();
		$occuTitle="occu_enname";
		$label = "name_en";
		if ($currentLang==1){
			$occuTitle="occu_name";
			$label = "name_kh";
		}
		
		$sql = "
			SELECT 
				s.*
				,(SELECT name_kh FROM `rms_view` WHERE type=2 AND key_code = s.sex LIMIT 1) as gender
				,(SELECT province_kh_name FROM `rms_province` WHERE `province_id`= s.province_id) as pro
		
				,(SELECT occ.occu_name FROM `rms_occupation` AS occ WHERE occ.occupation_id = s.father_job LIMIT 1)as far_job
				,(SELECT occ.$occuTitle FROM `rms_occupation` AS occ WHERE occ.occupation_id = s.father_job LIMIT 1)as farJobTitle
				,(SELECT occ.occu_name FROM `rms_occupation` AS occ WHERE occ.occupation_id = s.mother_job LIMIT 1)as mom_job
				,(SELECT occ.$occuTitle FROM `rms_occupation` AS occ WHERE occ.occupation_id = s.mother_job LIMIT 1)as momJobTitle
				,(SELECT occ.occu_name FROM `rms_occupation` AS occ WHERE occ.occupation_id = s.guardian_job LIMIT 1)as guar_job
				,(SELECT occ.$occuTitle FROM `rms_occupation` AS occ WHERE occ.occupation_id = s.guardian_job LIMIT 1)as guarJobTitle
		
				,(SELECT v.$label FROM `rms_view` AS v WHERE v.type=1 AND v.key_code = s.status LIMIT 1) as status
				,nationality
		FROM rms_student AS s WHERE s.status = 1";
		
		$sql.='';
		if(empty($search)){
			$_db->fetchAll($sql);
		}
		if(!empty($search['txtsearch']))
		{
			$s_where = array();
			$s_search = trim($search['txtsearch']);
			$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
			$s_where[] = " s.nationality LIKE '%{$s_search}%'";
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
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$level = $_db->getUserType();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$branch = "branch_namekh";
    		$grade = "p.title";
    		$degree = "it.title";
    	}else{ // English
    		$label = "name_en";
    		$branch = "branch_nameen";
    		$grade = "p.title_en";
    		$degree = "it.title_en";
    	}
    	if($level==4){
    		$branch_id = $_db->getAccessPermission("branch_id");
    	}else{
    		$branch_id = "";
    	}
    	
    	$sql="SELECT 
    				p.code AS pro_code,
    				p.images,
    				$grade AS pro_name ,
    				(SELECT $degree FROM `rms_items` AS it WHERE it.id = p.items_id LIMIT 1) AS category_name,
    	            (SELECT $branch FROM rms_branch WHERE rms_branch.br_id=pl.branch_id LIMIT 1) AS brand_name,
    	            pl.branch_id,
    				pl.pro_qty,
    				pl.note,
    				p.price,
    				pl.price AS pro_price, 
    				p.cost,
			        p.create_date AS date,
			        (SELECT v.$label FROM rms_view AS v WHERE v.key_code=p.status AND v.type=1 LIMIT 1) AS `status` 
			  FROM 
			  		`rms_itemsdetail` AS p,
			  		rms_product_location AS pl
			  WHERE 
    				p.id=pl.pro_id 
    				AND p.items_type=3
    				$branch_id ";
    	$where=" ";
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " p.code LIKE '%{$s_search}%'";
    		$s_where[]= " p.title LIKE '%{$s_search}%'";
    		$s_where[]= " p.cost LIKE '%{$s_search}%'";
    		$s_where[]= " p.price LIKE '%{$s_search}%'";
    		$s_where[]= "  pl.pro_qty LIKE '%{$s_search}%'";
    		$s_where[]= "  pl.total_amount LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['location'])){
    		$where.=" AND pl.branch_id=".$search['location'];
    	}
    	if(!empty($search['product'])){
    		$where.=" AND p.id=".$search['product'];
    	}
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND p.status=".$search['status_search'];
    	}
    	if($search['category_id']>0){
    		$where.=" AND p.items_id=".$search['category_id'];
    	}
    	if($search['product_type']>0){
    		$where.=" AND p.product_type=".$search['product_type'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	$order = " ORDER BY pl.branch_id ASC ";
    	if($search['sort_by']==1){
    		$order.=" , p.items_id ASC ";
    	}else if($search['sort_by']==2){
    		$order.=" , $grade ASC ";
    	}
    	
    	return $db->fetchAll($sql.$where.$order);
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



