<?php

class Allreport_Model_DbTable_DbRptFee extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllTuitionFee($search,$type=1){
    	$db=$this->getAdapter();
    	$_db=new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	$lang = $_db->currentlang();
		$branch= $_db->getBranchDisplay();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$grade = "rms_itemsdetail.title";
    		$degree = "rms_items.title";
    		$str = 'title_kh';
    	}else{ // English
    		$label = "name_en";
    		$grade = "rms_itemsdetail.title_en";
    		$degree = "rms_items.title_en";
    		$str = 'title_eng';
    	}
    	
    	$sql = "SELECT id,
    			(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1) AS academic,
    			(SELECT $str FROM rms_studytype WHERE rms_studytype.id =rms_tuitionfee.term_study  LIMIT 1) AS study_type,
    			 note,generation,
    			(SELECT $branch from rms_branch where br_id = branch_id LIMIT 1) AS branch_name,
    		    (SELECT $label FROM `rms_view` WHERE `rms_view`.`type`=12 AND `rms_view`.`key_code`=`rms_tuitionfee`.`is_finished` LIMIT 1) AS is_process,
    			create_date ,status FROM `rms_tuitionfee`  WHERE 1  $branch_id  ";
    	$sql.=" AND type= $type ";
    	$where= ' ';
    	$order=" ORDER BY id DESC ";
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['academic_year'])){
    		$where.=" AND academic_year = ".$search['academic_year'] ;
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND branch_id = ".$search['branch_id'] ;
    	}
    	if($search['finished_status']>-1){
    		$where.=" AND `rms_tuitionfee`.`is_finished` = ".$search['finished_status'] ;
    	}
    	if($search['type_study']>-1){
    		$where.=" AND `rms_tuitionfee`.`term_study` = ".$search['type_study'] ;
    	}
    	if(!empty($search['generation']) AND $search['generation']!=-1){
    		$where.=" AND generation = '".$search['generation']."'" ;
    	}
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " rms_tuitionfee.generation LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    function getFeebyOther($fee_id,$grade_search,$degree_id,$search=null){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$grade = "i.title";
    		$degree = "rms_items.title";
    		$branch = "b.branch_namekh";
    	}else{ // English
    		$label = "name_en";
    		$grade = "i.title_en";
    		$degree = "rms_items.title_en";
    		$branch = "b.branch_nameen";
    	}
    	$sql="SELECT tf.*,
			    	$grade as class,
			    	(SELECT $degree FROM `rms_items` WHERE rms_items.id=i.items_id LIMIT 1) as degree
			    	FROM 
			    		rms_tuitionfee_detail as tf,
			    		rms_itemsdetail as i 
			    	WHERE 
    					i.id=tf.class_id 
    					AND tf.fee_id = $fee_id ";
    	$where = ' ';
    	$order = ' ORDER BY i.items_id ASC, tf.id ASC';
    	
    	if($degree_id>0){
    		$where.=" AND i.items_id = ".$degree_id;
    	}
    	if($grade_search>0){
    		$where.=" AND tf.class_id = ".$grade_search;
    	}
    	if($search!=null AND $search['school_option']>0){
    		$where.=" AND i.schoolOption = ".$search['school_option'];
    	}

    	$result = $db->fetchAll($sql.$where.$order);    	
    	if(!empty($result)){
    		return $result;
    	}
    }
	function getAllYearFee(){
	    	$db=$this->getAdapter();
	    	$_db=new Application_Model_DbTable_DbGlobal();
	    	$branch_id = $_db->getAccessPermission();
	    	$sql=" SELECT id, 
	    	 	(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1) AS year
	    	 	FROM rms_tuitionfee WHERE 1  $branch_id  ";
	    	return $db->fetchAll($sql);
	}
	
	function getAllStudentCurrentService($search = array()){
		$_db = $this->getAdapter();
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$lang = $dbGb->currentlang();
		
		$branch = $dbGb->getBranchDisplay();
		$titleCol = "title_en";
		$titleColGrade = "gradeTitleEn";
		$titleColDegree = "degreeTitleEn";
		$label = "name_en";
		if($lang==1){
			$titleCol = "title";
			$titleColGrade = "gradeTitleKh";
			$titleColDegree = "degreeTitleKh";
			$label = "name_kh";
		}
		$sql="SELECT
				gds.`gd_id` AS id
				,gds.`stu_id` AS studentId
				,(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id=vs.branchId LIMIT 1) AS branchName
				,(SELECT CONCAT(aca.fromYear,'-',aca.toYear) FROM rms_academicyear AS aca WHERE aca.id=vs.`academicYear` LIMIT 1) AS academicYearTitle
				,vs.`stuCode`
				,vs.`stuNameKh`
				,vs.`stuNameEn`
				,vs.`tel`
				,vs.`sex`
				,vs.`groupCode`
				,vs.`photo`
				,(SELECT v.$label FROM rms_view AS v where v.type=2 AND v.key_code=vs.sex LIMIT 1) AS sexTitle
				,COALESCE(vs.`degreeShortcut`,vs.$titleColDegree) AS degreeTitle
				,COALESCE(vs.`gradeShortcut`,vs.$titleColGrade) AS gradeTitle
				,vs.`academicYear`
				,gds.`itemType`
				,gds.`gd_id` AS detailId
				,gds.`grade`
				,itd.`title` AS itemTitle
				,gds.`degree`
				,it.`title` AS categoryTitle
				,gds.`stop_type`
				,gds.`startDate`
				,gds.`endDate`
				,COALESCE(gds.`feeId`,0) AS feeId
				,gds.`balance`
				,gds.`discount_type`
				,gds.`discount_amount`
				,gds.note
				,gds.`is_current`
			";
		$sql.="
			FROM (`rms_group_detail_student` AS gds  JOIN `v_stu_study_info` AS vs  ON vs.`studentId` = gds.`stu_id` AND vs.`itemType` =1)
				JOIN `rms_itemsdetail` AS itd ON itd.`id` = gds.`grade` AND itd.`is_onepayment` = 0
				LEFT JOIN `rms_items` AS it ON it.`id` = gds.`degree`
		";
		$sql.="WHERE 1 
			AND gds.`itemType` !=1 
			AND gds.`stop_type` = 0
			AND gds.`is_current` = 1
			AND gds.`endDate` !='0000-00-00'
		";
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=addslashes(trim($search['adv_search']));
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			
			$s_where[]= " REPLACE(vs.`stuCode`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`stuNameKh`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`stuNameEn`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`degreeTitleKh`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`degreeTitleEn`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`gradeTitleEn`,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(vs.`gradeTitleKh`,' ','') LIKE '%{$s_search}%'";
			
			$sql.=' AND ('.implode(' OR ', $s_where).')';
		}
		if(!empty($search['academic_year'])){
    		$sql.= " AND vs.`academicYear` = ".$_db->quote($search['academic_year']);
    	}
		if(!empty($search['studentId'])){
    		$sql.= " AND gds.`stu_id` = ".$_db->quote($search['studentId']);
    	}
		if(!empty($search['branch_id'])){
    		$sql.= " AND vs.`branchId` = ".$_db->quote($search['branch_id']);
    	}
		if(!empty($search['groupId'])){
    		$sql.= " AND vs.`groupId` = ".$_db->quote($search['groupId']);
    	}
		if(!empty($search['degree'])){
			if($search['degree']!=-1){
				$sql.= " AND vs.`degree` = ".$_db->quote($search['degree']);
			}
    	}
		if(!empty($search['grade_all'])){
    		$sql.= " AND vs.`grade` = ".$_db->quote($search['grade_all']);
    	}
		if(!empty($search['item'])){
    		//$sql.= " AND gds.`degree` = ".$_db->quote($search['item']);
			if($search['item']!=-1){
				$arrCon = array(
					"categoryId" => $search['item'],
				);
				$condiction = $dbGb->getChildItems($arrCon);
				if (!empty($condiction)){
					$sql.=" AND gds.`degree` IN ($condiction)";
				}else{
					$sql.=" AND gds.`degree`=".$search['item'];
				}
			}
    	}
		if(!empty($search['service'])){
    		$sql.= " AND gds.`grade` = ".$_db->quote($search['service']);
    	}
		$sql.= $dbGb->getAccessPermission('vs.`branchId`');
		$sql.= $dbGb->getDegreePermission('COALESCE(vs.`degree`,0)');
		$orderby = " ORDER BY gds.`degree` ASC, gds.`grade` ASC, gds.`endDate` ASC,vs.`stuCode` ASC ";
		
		$nearlyPaymetySort = empty($search['nearlyPaymetySort']) ? 1 : $search['nearlyPaymetySort'];
		if($nearlyPaymetySort==1){
			$orderby=" ORDER BY vs.`stuCode` ASC , gds.`degree` ASC , gds.`grade` ASC , gds.`endDate` ASC";
		}
		
		
		return $_db->fetchAll($sql.$orderby);
	}
}
   
    
   