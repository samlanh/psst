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
			,i.$columnItem AS title
			,'' AS listSubId
		FROM `rms_items` AS i 
		WHERE i.`is_parent` =1
			ORDER BY i.`type` ASC,i.`ordering` ASC,i.$columnItem ASC
		";
		$rs = $db->fetchAll($sql);
		if(!empty($rs)) foreach($rs as $key => $cate){
			$subList = $this->getMulitpleSubCategory($cate["id"]);
			$rs[$key]["listSubId"] = empty($subList) ? "" : $subList;
			
		}
		return $rs;
	}
	
	function getCurrentPayemntDetailByParent($data){
		$db=$this->getAdapter();
		$paymentId = empty($data["paymentId"]) ? 0 : $data["paymentId"];
		$subList = empty($data["listSubId"]) ? 0 : $data["listSubId"];
		$sql="
			SELECT 
			spmtd.`service_type`
			,spmtd.`itemdetail_id`
			,itd.title AS itemsName
			,COALESCE(itd.`items_id`,0) AS categoryId
			,SUM(spmtd.`fee`) AS totalFee
			,SUM(spmtd.`subtotal`) AS gSubTotalFee
			,MAX(spmtd.`discount_percent`) AS totalDiscountPercent
			,SUM(spmtd.`discount_amount`) AS totalDiscountAmt
			,SUM(spmtd.`total_discount`) AS gTotalDiscount
			,SUM(spmtd.`extra_fee`) AS totalExtraFee
			,spmtd.`extra_fee`
			,SUM(spmtd.`totalpayment`) AS gTotalPament
			,(SELECT v.name_kh FROM rms_view  AS v WHERE v.type=6 AND v.key_code=spmtd.payment_term LIMIT 1) AS payTerm

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
			if(!empty($rs["listSubId"])){
				$array = array(
					"paymentId" =>$paymentId
					,"listSubId" =>$rs["listSubId"]
				);
				$result = $this->getCurrentPayemntDetailByParent($array);
				if(!empty($result)){
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
			$branch_id = $dbGb->getAccessPermission('spmt.branch_id');
			$branch = $dbGb->getBranchDisplay();
			
			$labelFull = $dbGb->getViewLabelDisplay();
			$labelShort = $dbGb->getViewLabelDisplay("shortcut");
			
			$currentLang = $dbGb->currentlang();
			$columnItem = "title_en";
			if($currentLang==1){// khmer
				$columnItem = "title";
			}
			
			$db=$this->getAdapter();
			$fromDate =(empty($search['start_date']))? '1': " DATE_FORMAT(spmt.`create_date`, '%Y-%m-%d %H:%i:%s') >= '".$search['start_date']." 00:00:00'";
			$toDate = (empty($search['end_date']))? '1': " DATE_FORMAT(spmt.`create_date`, '%Y-%m-%d %H:%i:%s') <= '".$search['end_date']." 23:59:59'";
			$sql=" SELECT
						(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id = spmt.branch_id LIMIT 1) AS branchName
						,s.`stu_code` AS studentCode
						,s.`stu_khname` AS studentNameKh
						,CONCAT(s.`stu_enname`,' ',s.`last_name`) AS studentNameEn
						,(SELECT v.$labelFull FROM `rms_view` AS v WHERE v.type =8 AND v.key_code = spmt.`payment_method` LIMIT 1) AS paymentMethodTitle
						,COALESCE((SELECT b.`bank_name` FROM `rms_bank` AS b WHERE b.id = spmt.`bank_id` LIMIT 1),'N/A') AS bankName
						,fam.`laonNumber`
						,fam.`street`
						,fam.`houseNo`
						,(SELECT v.$labelShort FROM `rms_view` AS v WHERE v.type =41 AND v.key_code = fam.`familyType` LIMIT 1) AS familyTypeTitle
						,g.`group_code` AS groupCode
						,(SELECT i.$columnItem FROM `rms_items` AS i WHERE i.type=1 AND i.id = spmt.degree LIMIT 1) AS degreeTitle
						,(SELECT itd.$columnItem FROM `rms_itemsdetail` AS itd WHERE itd.`items_type`=1 AND itd.id = spmt.grade LIMIT 1) AS gradeTitle
						,(SELECT u.first_name FROM rms_users AS u WHERE u.id = spmt.user_id LIMIT 1) AS byUserName
						,spmt.id AS paymentId
						,spmt.*
				  FROM
						`rms_student_payment` AS spmt 
						JOIN (`rms_student` AS s LEFT JOIN `rms_family` AS fam ON fam.`id` = s.`familyId`) ON s.`stu_id` = spmt.`student_id`
						LEFT JOIN  `rms_group` AS g ON g.id =  spmt.`group_id` 
				  WHERE 1
						
						$branch_id ";
	
			$sql.= " AND ".$fromDate." AND ".$toDate;
	
			if(!empty($search['adv_search'])){
				$s_where=array();
				$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
				$s_where[] = " REPLACE(s.`stu_code`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(s.`stu_khname`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(s.`stu_enname`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(s.`last_name`,' ','') LIKE '%{$s_search}%'";
				$s_where[]=	 " REPLACE(CONCAT(s.last_name,s.stu_enname),' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(fam.`laonNumber`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(fam.`street`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(fam.`houseNo`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(g.`group_code`,' ','') LIKE '%{$s_search}%'";
				$s_where[] = " REPLACE(spmt.receipt_number,' ','') LIKE '%{$s_search}%'";
				$where.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(!empty($search['branch_id'])){
				$sql.= " AND spmt.branch_id = ".$search['branch_id'];
			}
			if(!empty($search['userId'])){
					$sql.= " AND spmt.user_id = ".$search['userId'];
			}
			if(!empty($search['degree'])){
				$sql.= " AND spmt.degree = ".$search['degree'];
			}
			if(!empty($search['grade_all'])){
				$sql.= " AND spmt.grade = ".$search['grade_all'];
			}
			if(!empty($search['stu_name'])){
				$sql.= " AND spmt.student_id = ".$search['stu_name'];
			}
	
			$order=" ORDER BY spmt.`payment_method` DESC,spmt.`bank_id` ASC,spmt.id DESC ";
			if($search['receipt_order']==0){
				$order=" ORDER BY spmt.`payment_method` DESC,spmt.`bank_id` ASC,spmt.id ASC ";
			}
			return $db->fetchAll($sql.$order);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
	}
    
}
   
    
   