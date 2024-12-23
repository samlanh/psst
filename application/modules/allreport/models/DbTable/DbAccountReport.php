<?php

class Allreport_Model_DbTable_DbAccountReport extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_placement_test';
    public function getUserId(){
    	$_db=new Application_Model_DbTable_DbGlobal();
		$userId = $_db->getUserId();
		$userId = empty($userId) ? 0 : $userId;
    	return $userId;
    }
	
	function getMulitpleSubCategory($parent){
		$db=$this->getAdapter();
		$sql="
		SELECT  
			GROUP_CONCAT(id)
		FROM (SELECT cat.* FROM `rms_items` AS cat
			 ORDER BY cat.parent, cat.id) items_sorted,
			(SELECT @iv := '$parent') initialisation
		WHERE FIND_IN_SET(parent, @iv)
			AND LENGTH(@iv := CONCAT(@iv, ',', id))
		";
		$re = $db->fetchOne($sql);
		if(!empty($re)){
			return $re;
		}
		return null;
	}
	function getMainParentOfItems(){
		$db=$this->getAdapter();
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbGb->currentlang();
		$columnItem = "title_en";
		if($currentLang==1){// khmer
			$columnItem = "title";
		}
			
		$sql="
		SELECT 
			i.`id`
			,COALESCE(NULLIF(i.shortcut,''),i.$columnItem) AS title
			,'' AS listSubId
			,'0' AS totalByMethod
			,'0' AS gTotal
		FROM `rms_items` AS i 
		WHERE i.`is_parent` =1
			ORDER BY i.`ordering` ASC,i.$columnItem ASC
		";
		$rs = $db->fetchAll($sql);
		if(!empty($rs)) foreach($rs as $key => $cate){
			$subList = $this->getMulitpleSubCategory($cate["id"]);

			$subListss = empty($subList) ? "" : $subList;
			$subListss = empty($subList) ? $cate["id"] : $subList.",".$cate["id"];
			$rs[$key]["listSubId"] = $subListss;
			
		}
		return $rs;
	}
	
	function getCurrentPayemntDetailByParent($data){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		
		$titleCol = "title_en";
		$labelView = "name_en";
		if ($currentLang == 1) {
			$titleCol = "title";
			$labelView = "name_kh";
		}
		
		$paymentId = empty($data["paymentId"]) ? 0 : $data["paymentId"];
		$subList = empty($data["listSubId"]) ? 0 : $data["listSubId"];
		//--,(SELECT v.$labelView FROM rms_view  AS v WHERE v.type=6 AND v.key_code=spmtd.payment_term LIMIT 1) AS payTerm
		$sql="
			SELECT 
			spmtd.`service_type`
			,spmtd.`itemdetail_id`
			,itd.$titleCol AS itemsName
			,COALESCE(itd.`items_id`,0) AS categoryId
			,SUM(spmtd.`fee`) AS totalFee
			,SUM(spmtd.`subtotal`) AS gSubTotalFee
			,MAX(spmtd.`discount_percent`) AS totalDiscountPercent
			,SUM(spmtd.`discount_amount`) AS totalDiscountAmt
			,SUM(spmtd.`total_discount`) AS gTotalDiscount
			,SUM(spmtd.`extra_fee`) AS totalExtraFee
			,spmtd.`extra_fee`
			,SUM(spmtd.`totalpayment`) AS gTotalPayment
			,(SELECT ss.title FROM `rms_startdate_enddate` ss WHERE ss.id=Max(spmtd.academicFeeTermId) LIMIT 1) AS payTerm


			FROM `rms_student_paymentdetail` AS spmtd 
		LEFT JOIN `rms_itemsdetail` AS itd ON itd.id = spmtd.`itemdetail_id`
		WHERE 1
			AND spmtd.`payment_id` = $paymentId
			AND COALESCE(itd.`items_id`,0) IN ($subList)
		";
		$rs = $db->fetchRow($sql);
		return $rs;
	}
	function getPaymentInfoParent($data){
		$parentCol = empty($data["parentCol"]) ? 0 : $data["parentCol"];
		$paymentId = empty($data["paymentId"]) ? 0 : $data["paymentId"];
		
		if(!empty($parentCol)) foreach($parentCol as $key => $rs){
			$gTotalPayment =0;
			if(!empty($rs["listSubId"])){
				$array = array(
					"paymentId" =>$paymentId
					,"listSubId" =>$rs["listSubId"]
				);
				$result = $this->getCurrentPayemntDetailByParent($array);
				if(!empty($result)){
					$gTotalPayment = empty($result["gTotalPayment"]) ? 0 : $result["gTotalPayment"];
					$parentCol[$key]["paymentInfo"] = $result;
					
				}else{
					$parentCol[$key]["paymentInfo"] = array();
				}
			}else{
				$parentCol[$key]["paymentInfo"] = array();
			}
		}
		return $parentCol;
		
	}
	function getStudentPaymentDaily($search=null){
		try{
			$dbGb = new Application_Model_DbTable_DbGlobal();
			$currentLang = $dbGb->currentlang();
			$branch_id = $dbGb->getAccessPermission('spt.`branchId`');
			$branch = $dbGb->getBranchDisplay();
			
			$labelFull = $dbGb->getViewLabelDisplay();
			$labelShort = $dbGb->getViewLabelDisplay("shortcut");
			
			$columnItem = 'title_en';
			if ($currentLang == 1) {
				$columnItem = 'title';
			}
			
			$db=$this->getAdapter();
			$fromDate =(empty($search['start_date']))? '1': " DATE_FORMAT(spt.`createDate`, '%Y-%m-%d %H:%i:%s') >= '".$search['start_date']." 00:00:00'";
			$toDate = (empty($search['end_date']))? '1': " DATE_FORMAT(spt.`createDate`, '%Y-%m-%d %H:%i:%s') <= '".$search['end_date']." 23:59:59'";
			$sql=" SELECT
						(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id = spt.`branchId` LIMIT 1) AS branchName
						
						,spt.`studentCode` AS studentCode
						,spt.`studentNameKh` AS studentNameKh
						,spt.`studentNameEn`
						
						,(SELECT v.$labelFull FROM `rms_view` AS v WHERE v.type =8 AND v.key_code = spt.`pmtMethod` LIMIT 1) AS paymentMethodTitle
						,COALESCE((SELECT b.`bank_name` FROM `rms_bank` AS b WHERE b.id = spt.`bankId` LIMIT 1),'N/A') AS bankName
						,fam.`laonNumber`
						,fam.`street`
						,fam.`houseNo`
						
						,(SELECT v.$labelShort FROM `rms_view` AS v WHERE v.type =41 AND v.key_code = fam.`familyType` LIMIT 1) AS familyTypeTitle
						,(SELECT COALESCE(i.shortcut,i.$columnItem) FROM `rms_items` AS i WHERE i.type=1 AND i.id = spt.degree LIMIT 1) AS degreeTitle
						,(SELECT COALESCE(itd.shortcut,itd.$columnItem) FROM `rms_itemsdetail` AS itd WHERE itd.`items_type`=1 AND itd.id = spt.grade LIMIT 1) AS gradeTitle
						,(SELECT u.first_name FROM rms_users AS u WHERE u.id = spt.userId LIMIT 1) AS byUserName
						
						,spt.paymentId
						,spt.`receptNo`
						,spt.`createDate`
						,spt.`creditMemo`
						,spt.`grandTotal`
						,spt.`penalty`
						,spt.`paidAmt`
						,spt.`balanceDue`
						,spt.`pmtMethod`
						,spt.`bankId`
						,spt.note
						,spt.`isVoid`
						,spt.`pmtDetailJson`
				  FROM
						`v_stupmt_ft_detail_info` AS spt 
							LEFT JOIN `rms_family` AS fam ON fam.`id` = spt.`familyId`
				  WHERE 1
						
						$branch_id ";
	
			$sql.= " AND ".$fromDate." AND ".$toDate;
	
			if(!empty($search['adv_search'])){
				$s_where=array();
				$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
				$s_where[] = " REPLACE(spt.`studentCode`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(spt.`studentNameKh`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(spt.`studentNameEn`,' ','') LIKE '%{$s_search}%'";
				
				$s_where[] = " REPLACE(fam.`laonNumber`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(fam.`street`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(fam.`houseNo`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(spt.receptNo,' ','') LIKE '%{$s_search}%'";
				$sql.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(!empty($search['branch_id'])){
				$sql.= " AND spt.branchId = ".$search['branch_id'];
			}
			if(!empty($search['userId'])){
					$sql.= " AND spt.userId = ".$search['userId'];
			}
			if(!empty($search['degree'])){
				$sql.= " AND spt.degree = ".$search['degree'];
			}
			if(!empty($search['grade_all'])){
				$sql.= " AND spt.grade = ".$search['grade_all'];
			}
			if(!empty($search['stu_name'])){
				$sql.= " AND spt.studentId = ".$search['stu_name'];
			}
			if(!empty($search['receiptStatus'])){
				if($search['receiptStatus']==1){
					$sql.= " AND spt.isVoid = 0 ";
				}else if($search['receiptStatus']==2){
					$sql.= " AND spt.isVoid = 1 ";
				}
			}
	
			$order=" ORDER BY spt.`pmtMethod` DESC,spt.`bankId` ASC,spt.paymentId DESC ";
			if($search['receipt_order']==0){
				$order=" ORDER BY spt.`pmtMethod` DESC,spt.`bankId` ASC,spt.paymentId ASC ";
			}
			return $db->fetchAll($sql.$order);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
	}
	
	function getIncomeDailyTotal($search=null){
		try{
			$dbGb = new Application_Model_DbTable_DbGlobal();
			$currentLang = $dbGb->currentlang();
			$branch_id = $dbGb->getAccessPermission('spt.`branchId`');
			$branch = $dbGb->getBranchDisplay();
			
			$labelFull = $dbGb->getViewLabelDisplay();
			$labelShort = $dbGb->getViewLabelDisplay("shortcut");
			
			$columnItem = 'title_en';
			if ($currentLang == 1) {
				$columnItem = 'title';
			}
			
			$db=$this->getAdapter();
			$fromDate =(empty($search['start_date']))? '1': " DATE_FORMAT(spt.`createDateFmt`, '%Y-%m-%d %H:%i:%s') >= '".$search['start_date']." 00:00:00'";
			$toDate = (empty($search['end_date']))? '1': " DATE_FORMAT(spt.`createDateFmt`, '%Y-%m-%d %H:%i:%s') <= '".$search['end_date']." 23:59:59'";
			$sql=" SELECT
						(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id = spt.`branchId` LIMIT 1) AS branchName
						
						,(SELECT v.$labelFull FROM `rms_view` AS v WHERE v.type =8 AND v.key_code = spt.`pmtMethod` LIMIT 1) AS paymentMethodTitle
						,COALESCE((SELECT b.`bank_name` FROM `rms_bank` AS b WHERE b.id = spt.`bankId` LIMIT 1),'N/A') AS bankName
						
						,spt.*
				  FROM
						`v_income_daily_total` AS spt 
				  WHERE 1
						
						$branch_id ";
	
			$sql.= " AND ".$fromDate." AND ".$toDate;
	
			if(!empty($search['adv_search'])){
				$s_where=array();
				$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
				$s_where[] = " REPLACE(spt.`branchId`,' ','') LIKE '%{$s_search}%'";
				
				$where.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(!empty($search['branch_id'])){
				$sql.= " AND spt.branchId = ".$search['branch_id'];
			}
	
			$order=" ORDER BY spt.`pmtMethod` DESC,spt.`bankId` ASC";
			
			
			return $db->fetchAll($sql.$order);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
	}
    
}
   
    
   