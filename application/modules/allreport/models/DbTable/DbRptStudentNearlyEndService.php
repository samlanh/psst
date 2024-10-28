<?php

class Allreport_Model_DbTable_DbRptStudentNearlyEndService extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_tuitionfee';
    /* function getAllStudentNearlyEndService($search){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('sp.branch_id');
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$data=$key->getKeyCodeMiniInv(TRUE);
    	
    	if (!empty($data['payment_day_alert'])){
    		$alert = $data['payment_day_alert'];
    		$search['end_date'] = date('Y-m-d',strtotime($search['end_date']."+$alert day"));
    	}

    	$lang = $_db->currentlang();
		$branch= $_db->getBranchDisplay();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$item_detail = "item.title";
    		$item = "title";
    	}else{ // English
    		$label = "name_en";
    		$item_detail = "item.title_en";
    		$item = "title_en";
    	}
    	
    	$sql="SELECT 
				 spd.`id` AS payment_id_detail,
    		     (SELECT $branch FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
				 s.stu_code AS stu_code,
				 s.stu_khname AS stu_khname,
				 s.stu_enname AS first_name,
				 s.last_name AS last_name,
				 s.tel,
				 (SELECT group_code FROM `rms_group` g WHERE g.id=sp.group_id limit 1) AS group_name,
				 (SELECT $label from rms_view where rms_view.type=2 and key_code=s.sex LIMIT 1) AS sex,
				 sp.`receipt_number` AS receipt,
				 spd.`payment_id` AS payment_id,
				 spd.`start_date` AS start_date,
				 spd.`validate` AS end_date,
				 sp.create_date ,
				 (SELECT $item FROM `rms_items` WHERE rms_items.id=(SELECT item.items_id FROM `rms_itemsdetail` AS item WHERE item.id=spd.itemdetail_id LIMIT 1) LIMIT 1 ) AS category_name,
				 (SELECT $item_detail FROM `rms_itemsdetail` AS item WHERE item.id=spd.itemdetail_id LIMIT 1) AS service_name
			FROM
				   rms_student as s,
				   rms_group_detail_student as gd,
				  `rms_student_paymentdetail` AS spd,
				  `rms_student_payment` AS sp
			WHERE
					gd.itemType=1 
					AND s.stu_id=gd.stu_id
					AND gd.is_current=1
					AND gd.is_maingrade=1
					AND gd.stop_type=0 
					AND  spd.`is_start` = 1 
				  AND sp.id=spd.`payment_id`
    			  AND sp.is_void!=1  $branch_id
    			  and s.stu_id = sp.student_id
    			  and spd.is_suspend = 0 
    			  AND spd.is_onepayment =0 ";
    	$sql.=" ";
     	$where=" ";
     	$to_date = (empty($search['end_date']))? '1': "spd.validate <= '".$search['end_date']." 23:59:59'";
     	
     	$where .= " AND ".$to_date;

		 if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " s.last_name LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}  

     	if($search['item']>0){
//      		$where .=" and spd.itemdetail_id=".$search['item'];
//      		$where .=" and (SELECT $item FROM `rms_items` WHERE rms_items.id=".$search['service'].")";
     	}

     	if(!empty($search['service'])){
     		$where .=" AND spd.itemdetail_id=".$search['service'];
     	}
 
     	if(($search['branch_id']>0)){
     		$where.= " AND sp.branch_id = ".$search['branch_id'];
     	}
		
     	if(!empty($search['study_year'])){
			$where.= " AND gd.academic_year = ".$search['study_year'];
		}
    	$order=" ORDER by spd.itemdetail_id ASC ";
		// echo $sql.$where.$order; exit();
    	return $db->fetchAll($sql.$where.$order);
    }*/

	function getAllStudentNearlyEndTuition($search){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('s.`branchId`');
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$data=$key->getKeyCodeMiniInv(TRUE);
    	
    	if (!empty($data['payment_day_alert'])){
    		$alert = $data['payment_day_alert'];
    		$search['end_date'] = date('Y-m-d',strtotime($search['end_date']."+$alert day"));
    	}

    	$lang = $_db->currentlang();
		$branch= $_db->getBranchDisplay();
		
		$label = "name_en";
		$item = "title_en";
		$itemDetail = "gradeTitleEn";
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$itemDetail = "gradeTitle";
    		$item = "title";
    	}
    	$sql="SELECT 
		
				vl.* 
				,(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id=s.`branchId` LIMIT 1) AS branchName
				
				
				,COALESCE(NULLIF(s.stopType,''),0) AS stopType 
				,s.`stuNameEn`
				,s.`stuNameKh`
				,s.`stuCode`
				,s.`branchId`
				,s.`tel`
				,s.`groupCode`
				,s.`academicYear`
				,(SELECT CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) FROM `rms_academicyear` AS ac WHERE ac.id =s.academicYear LIMIT 1) AS academicYearTitle
 
				,(SELECT v.$label FROM rms_view AS v where v.type=2 AND v.key_code=s.sex LIMIT 1) AS sexTitle
				,(SELECT $item FROM `rms_items` WHERE rms_items.id= item.items_id LIMIT 1 ) AS categoryName
				,vl.$itemDetail AS serviceTitle
				
			FROM `v_last_record_paid_tuition` AS vl
				JOIN `v_stu_study_info` AS s ON s.`studentId` = vl.`studetnId` AND s.`grade` = vl.`grade` 
				LEFT JOIN `rms_itemsdetail` AS item ON item.id=vl.grade
			WHERE 1
				AND vl.`endDate` IS NOT NULL
				AND COALESCE(NULLIF(s.stopType,''),0) = 0
				$branch_id
			";
    	$sql.=" ";
     	$where=" ";
     	$to_date = (empty($search['end_date']))? '1': "vl.endDate <= '".$search['end_date']." 23:59:59'";
     	
     	$where .= " AND ".$to_date;

		 if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " vl.receiptNo LIKE '%{$s_search}%'";
    		$s_where[] = " s.`stuNameEn` LIKE '%{$s_search}%'";
    		$s_where[] = " s.`stuNameKh` LIKE '%{$s_search}%'";
    		$s_where[] = " s.stuCode LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}  

     	if($search['item']>0){
			//$where .=" AND item.items_id=".$search['item'];
			$arrCon = array(
				"categoryId" => $search['item'],
			);
			$condiction = $_db->getChildItems($arrCon);
			if (!empty($condiction)){
				$where.=" AND item.items_id IN ($condiction)";
			}else{
				$where.=" AND item.items_id=".$search['item'];
			}
     	}

     	if(!empty($search['service'])){
     		$where .=" AND vl.grade=".$search['service'];
     	}
 
     	if(($search['branch_id']>0)){
     		$where.= " AND s.branchId = ".$search['branch_id'];
     	}
     	if(!empty($search['study_year'])){
			$where.= " AND s.academicYear = ".$search['study_year'];
		}
		if(!empty($search['degree'])){
			$where.= " AND s.degree = ".$search['degree'];
		}
		if(!empty($search['grade_all'])){
			$where.= " AND s.grade = ".$search['grade_all'];
		}
    	$order=" ORDER by vl.grade ASC,s.`stuCode` ASC ";
    	//$order.=" LIMIT 100 ";
		
		//echo $sql.$where.$order;exit();
    	return $db->fetchAll($sql.$where.$order);
    }
	
	function getAllStudentNearlyEndService($search){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('s.`branchId`');
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$data=$key->getKeyCodeMiniInv(TRUE);
    	
    	if (!empty($data['payment_day_alert'])){
    		$alert = $data['payment_day_alert'];
    		$search['end_date'] = date('Y-m-d',strtotime($search['end_date']."+$alert day"));
    	}

    	$lang = $_db->currentlang();
		$branch= $_db->getBranchDisplay();
		
		$label = "name_en";
		$item = "title_en";
		$itemDetail = "gradeTitleEn";
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$itemDetail = "gradeTitle";
    		$item = "title";
    	}
    	$sql="SELECT 
		
				vl.* 
				,(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id=s.`branchId` LIMIT 1) AS branchName
				
				
				,COALESCE(NULLIF(s.stopType,''),0) AS stopType 
				,s.`stuNameEn`
				,s.`stuNameKh`
				,s.`stuCode`
				,s.`branchId`
				,s.`tel`
				,s.`groupCode`
				,s.`academicYear`
				,(SELECT CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) FROM `rms_academicyear` AS ac WHERE ac.id =s.academicYear LIMIT 1) AS academicYearTitle
 
				,(SELECT v.$label FROM rms_view AS v where v.type=2 AND v.key_code=s.sex LIMIT 1) AS sexTitle
				,(SELECT $item FROM `rms_items` WHERE rms_items.id= item.items_id LIMIT 1 ) AS categoryName
				,vl.$itemDetail AS serviceTitle
				,COALESCE((SELECT sspd.id FROM  `rms_suspendservicedetail` AS sspd WHERE sspd.`studentId`=s.`studentId` AND sspd.`spd_id`=vl.`grade` AND DATE_FORMAT(sspd.`stopDate`,'%\Y-%\m-%\d') >= DATE_FORMAT(vl.`paymentDate`,'%\Y-%\m-%\d') LIMIT 1),0) AS stoptServiceStatus
				
			FROM `v_last_record_paid_service` AS vl
				JOIN `v_stu_study_info` AS s ON s.`studentId` = vl.`studetnId` 
				LEFT JOIN `rms_itemsdetail` AS item ON item.id=vl.grade
			WHERE 1
				AND vl.`endDate` IS NOT NULL
				AND COALESCE(NULLIF(s.stopType,''),0) = 0
				$branch_id
			";
    	$sql.=" AND COALESCE((SELECT sspd.id FROM  `rms_suspendservicedetail` AS sspd WHERE sspd.`studentId`=s.`studentId` AND sspd.`spd_id`=vl.`grade` AND DATE_FORMAT(sspd.`stopDate`,'%\Y-%\m-%\d') >= DATE_FORMAT(vl.`paymentDate`,'%\Y-%\m-%\d') LIMIT 1),0) = 0 ";
     	$where=" ";
     	$to_date = (empty($search['end_date']))? '1': "vl.endDate <= '".$search['end_date']." 23:59:59'";
     	
     	$where .= " AND ".$to_date;

		 if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " vl.receiptNo LIKE '%{$s_search}%'";
    		$s_where[] = " s.`stuNameEn` LIKE '%{$s_search}%'";
    		$s_where[] = " s.`stuNameKh` LIKE '%{$s_search}%'";
    		$s_where[] = " s.stuCode LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}  

     	if($search['item']>0){
			//$where .=" AND item.items_id=".$search['item'];
			$arrCon = array(
				"categoryId" => $search['item'],
			);
			$condiction = $_db->getChildItems($arrCon);
			if (!empty($condiction)){
				$where.=" AND item.items_id IN ($condiction)";
			}else{
				$where.=" AND item.items_id=".$search['item'];
			}
     	}

     	if(!empty($search['service'])){
     		$where .=" AND vl.grade=".$search['service'];
     	}
 
     	if(($search['branch_id']>0)){
     		$where.= " AND s.branchId = ".$search['branch_id'];
     	}
     	if(!empty($search['study_year'])){
			$where.= " AND s.academicYear = ".$search['study_year'];
		}
		if(!empty($search['degree'])){
			$where.= " AND s.degree = ".$search['degree'];
			
		}
		if(!empty($search['grade_all'])){
			$where.= " AND s.grade = ".$search['grade_all'];
		}
    	$order=" ORDER by vl.grade ASC,s.`stuCode` ASC ";
    	//$order.=" LIMIT 100 ";
		
    	return $db->fetchAll($sql.$where.$order);
    }

	function getStudentGroupDetailInfoByItems($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT
				gs.*
			FROM
				`rms_group_detail_student` AS gs,
				rms_student as st
			WHERE 	st.stu_id=gs.stu_id 
				AND gs.`stop_type` =0
				AND gs.`is_current` =1
			";
		$studentId = empty($data['studentId']) ? 0 : $data['studentId'];
		$gradeId = empty($data['gradeId']) ? 0 : $data['gradeId'];
		$sql .= " AND gs.`stu_id` = " . $studentId;
		$sql .= " AND gs.`grade` = " . $gradeId;
		$sql .= " LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function updateValidate($data){
		$db = $this->getAdapter();//ស្ពានភ្ជាប់ទៅកាន់Data Base
		$db->beginTransaction();//ទប់ស្កាត់មើលការErrore , មានErrore វាមិនអោយចូល
			try{
				
				$payment_id = $data['id'];
				$this->_name='rms_student_paymentdetail';
					$arra=array(
							'start_date'=>$data['start_date'],
							'validate'=>$data['validate'],
					);
				$where = " id = ".$payment_id;
				$this->update($arra, $where);
				
				$rw = $this->getStudentGroupDetailInfoByItems($data);
				if(!empty($rw)){
					$this->_name='rms_group_detail_student';
					$arra=array(
							'startDate'=>$data['start_date'],
							'endDate'=>$data['validate'],
					);
					$where = " gd_id = ".$rw["gd_id"];
					$this->update($arra, $where);
				}
				
				$db->commit();
				return 1;
			}catch (Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				$db->rollBack();
			}
	}
} 