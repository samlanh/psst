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

	function getAllStudentNearlyEndService($search){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('vSt.`branchId`');
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$data=$key->getKeyCodeMiniInv(TRUE);
    	
		if (!empty($search['periodDay'])){
			$periodDay = empty($search['periodDay']) ? 0 : $search['periodDay'];
			$search['end_date'] = date('Y-m-d',strtotime("+$periodDay day"));
			
    	}else{
			$search['end_date'] = empty($search['end_date']) ? date('Y-m-d') : $search['end_date'];
			/*
			if (!empty($data['payment_day_alert'])){
				$alert = $data['payment_day_alert'];
				$search['end_date'] = date('Y-m-d',strtotime($search['end_date']."+$alert day"));
			}
			*/
		}
    	
    	$lang = $_db->currentlang();
		$branch= $_db->getBranchDisplay();
		
		$label = "name_en";
		$item = "degreeTitleEn";
		$itemDetail = "gradeTitleEn";
    	if($lang==1){// khmer
    		$label = "name_kh";
			$item = "degreeTitle";
    		$itemDetail = "gradeTitle";
    		
    	}
    	$sql="SELECT 
				vSt.`branchId` AS branchId
				,(SELECT b.$branch FROM `rms_branch` AS b WHERE b.br_id=vSt.`branchId` LIMIT 1) AS branchName
				,vSt.`itemType`
				,vSt.`studentId` AS studentId
				,vSt.`stuCode` AS stuCode
				,vSt.`stuNameKh` AS stuNameKh
				,vSt.`stuNameEn` AS stuNameEn
				,vSt.sex
				,vSt.`tel`
				,vSt.`isMainGrade`
				,vSt.`isCurrent`
				,COALESCE(NULLIF(vSt.`stopType`,''),0) AS stopType
				,vSt.`academicYear` AS academicYear
				,vSt.`degree`
				,vSt.`grade`

				,CASE WHEN (NULLIF(vSt.`endDate`,'') IS NOT NULL OR vSt.`endDate` !='0000-00-00')
							THEN vSt.`startDate`
						WHEN (NULLIF(v.`endDate`,'') IS NOT NULL OR v.`endDate` !='0000-00-00')
							THEN v.`startDate`
					ELSE 1 END AS startDate
				,CASE WHEN (NULLIF(vSt.`endDate`,'') IS NOT NULL OR vSt.`endDate` !='0000-00-00')
							THEN vSt.`endDate`
						WHEN (NULLIF(v.`endDate`,'') IS NOT NULL OR v.`endDate` !='0000-00-00')
							THEN v.`endDate`
					ELSE '' END AS endDate
					
				,v.`receiptNo`
				,v.paidamount
				,(SELECT title FROM `rms_startdate_enddate` WHERE id=v.paymentTerm LIMIT 1) AS paymentTerm
				,v.academicFeeTermId
				,v.`feeId`
				,v.`academicYear` AS paymentAcademicYear
				,v.`creadDate` AS paymentDate
				,v.`startDate` AS pmtStartDate
				,v.`endDate` AS pmtEndDate
				,COALESCE(v.`paymentId`,0) AS paymentId 
				,v.`groupId` AS pmtGroupId
				,v.`grade` AS pmtGrade
				,v.`degree` AS pmtDegree
				,v.`pmtDetailId` AS pmtDetailId
				,CASE 
						WHEN vSt.`itemType` = 1 
							THEN (SELECT g.group_code FROM `rms_group` AS g WHERE g.id = vSt.`groupId` LIMIT 1)
						ELSE (SELECT g.group_code FROM `rms_group` AS g WHERE g.id = v.`groupId` LIMIT 1)
					END AS groupCode
				,CASE 
						WHEN vSt.`itemType` = 1 
							THEN (SELECT CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) FROM `rms_academicyear` AS ac WHERE ac.id =vSt.`academicYear` LIMIT 1)
						ELSE (SELECT CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) FROM `rms_academicyear` AS ac WHERE ac.id =v.`academicYear` LIMIT 1)
					END AS academicYearTitle
				,vSt.$item AS categoryName
				,vSt.$itemDetail AS serviceTitle
				
				
			FROM v_stu_record_info AS vSt
				LEFT JOIN v_stu_item_pmtinfo v ON v.paymentId = (
					SELECT paymentId
						FROM v_stu_item_pmtinfo fa 
						WHERE fa.itemDetailId = vSt.grade AND fa.studentId = vSt.`studentId` AND  fa.itemDetailId = vSt.`grade`
						ORDER BY `paymentId` DESC
					LIMIT 1
				) AND v.`studentId` = vSt.`studentId` AND v.itemDetailId = vSt.`grade`
				LEFT JOIN `rms_items` AS i ON i.`id` = vSt.`degree`
				LEFT JOIN `rms_itemsdetail` AS idd ON idd.`id` =  vSt.`grade`
			WHERE vSt.`stopType` NOT IN (1,2)
				AND vSt.`isOnepayment` = 0
				AND COALESCE(v.`paymentId`,0) !=0
				AND  
					CASE WHEN vSt.`itemType` = 1 
							THEN vSt.`isMaingrade` = 1 AND (NULLIF(vSt.`endDate`,'') IS NOT NULL AND vSt.`endDate` !='0000-00-00')
						 WHEN vSt.`itemType` != 1 
							THEN  (NULLIF(vSt.`endDate`,'') IS NOT NULL AND vSt.`endDate` !='0000-00-00')
							
				ELSE 1 END
			
				
			";
		$toDate = (empty($search['end_date']))? '1': "vSt.`endDate` <= '".$search['end_date']." 23:59:59'";
		$toDatePmt = (empty($search['end_date']))? '1': "v.`endDate` <= '".$search['end_date']." 23:59:59'";
    	$sql.=" 
		AND  
					CASE WHEN (NULLIF(vSt.`endDate`,'') IS NOT NULL OR vSt.`endDate` !='0000-00-00')
							THEN $toDate
						WHEN (NULLIF(v.`endDate`,'') IS NOT NULL OR v.`endDate` !='0000-00-00')
							THEN $toDatePmt
					ELSE 1 END
				
				$branch_id
		";
     	$where=" ";
		/*
			,COALESCE(NULLIF(vSt.`degreeShortcut`,''),vSt.$item) AS categoryName
			,COALESCE(NULLIF(vSt.`gradeShortcut`,''),vSt.$itemDetail) AS serviceTitle
		*/
				
     	//$to_date = (empty($search['end_date']))? '1': "vl.endDate <= '".$search['end_date']." 23:59:59'";
     	
     	//$where .= " AND ".$to_date;

		 if(!empty($search['adv_search'])){
    		$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[]=" REPLACE(vSt.stuCode,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(vSt.stuNameEn,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(vSt.stuNameKh,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(v.receiptNo,' ','')   	LIKE '%{$s_search}%'";
			
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}  

     	if($search['item']>0){
			//$where .=" AND item.items_id=".$search['item'];
			$arrCon = array(
				"categoryId" => $search['item'],
			);
			$condiction = $_db->getChildItems($arrCon);
			if (!empty($condiction)){
				$where.=" AND vSt.degree IN ($condiction)";
			}else{
				$where.=" AND vSt.degree=".$search['item'];
			}
     	}
		if(!empty($search['service_type'])){
     		$where .=" AND vSt.`itemType` =".$search['service_type'];
     	}
     	if(!empty($search['service'])){
     		$where .=" AND vSt.grade=".$search['service'];
     	}
 
     	if(($search['branch_id']>0)){
     		$where.= " AND vSt.branchId = ".$search['branch_id'];
     	}
     	if(!empty($search['study_year'])){
			//$where.= " AND gd.`academic_year` = ".$search['study_year'];
			$where.="
				AND CASE 
						WHEN vSt.`itemType` = 1 
							THEN vSt.`academicYear` = ".$search['study_year']."
						ELSE v.`academicYear` = ".$search['study_year']."	 
					END
			";
			//$where.= " AND gd.`academic_year` = ".$search['study_year'];
		}
		/*if(!empty($search['degree'])){
			$where.= " AND gd.degree = ".$search['degree'];
		}
		if(!empty($search['grade_all'])){
			$where.= " AND gd.grade = ".$search['grade_all'];
		}*/
    	$order=" ORDER BY vSt.`degree` ASC, vSt.`grade` ASC, vSt.`stuCode` ASC ";
		$nearlyPaymetySort = empty($search['nearlyPaymetySort']) ? 1 : $search['nearlyPaymetySort'];
		if($nearlyPaymetySort==1){
			$order=" ORDER BY vSt.`stuCode` ASC , vSt.`degree` ASC , vSt.`grade` ASC ";
		}
    	//$order.=" LIMIT 10 ";
		
		// echo $sql.$where.$order;exit();
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