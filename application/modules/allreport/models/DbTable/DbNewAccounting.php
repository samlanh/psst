<?php
class Allreport_Model_DbTable_DbNewAccounting extends Zend_Db_Table_Abstract
{

	protected $_name = 'rms_student';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	
	function getAllPaymentStatistic($search=null){
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch= $_db->getBranchDisplay();

		$lang = $_db->currentlang();
	   	if($lang==1){// khmer
	   		$label = "name_kh";
	   		$grade = "it.title";

	   	}else{ // English
	   		$label = "name_en";
	   		$grade = "it.title_en";
	   	}
			$viewName = " v_studenttutionfeepaid";

		$condiontion = " AND itemType=1 AND dg.is_maingrade=1 ";
		if($search['reportType']==2){
			$viewName = " v_studentlunchfeepaid";
			$condiontion = " AND dg.itemType=2 AND dg.grade=361 ";
		 }elseif($search['reportType']==3){
			$viewName = " v_studentnapfeepaid";
			$condiontion = " AND dg.itemType=2 AND dg.grade=360 ";
		 }
//(SELECT $branch FROM `rms_branch` WHERE rms_branch.br_id = s.branch_id LIMIT 1) AS branch_name,
// (SELECT $label FROM `rms_view` WHERE type=40 and key_code=s.studentType LIMIT 1) AS studentType,
//(SELECT REPLACE($grade,'Grade','') FROM `rms_itemsdetail` it WHERE (`it`.`id`=`dg`.`grade`) AND (`it`.`items_type`=1) LIMIT 1) as grade,
		$sql = "SELECT 
				s.stu_id,
				s.stu_code,
				stu_khname AS student_name,
				CASE
					WHEN primary_phone=1 THEN s.tel
					WHEN primary_phone=2 THEN (SELECT f.fatherPhone FROM rms_family f WHERE f.id=s.familyId LIMIT 1)
					WHEN primary_phone=3 THEN (SELECT f.motherPhone FROM rms_family f WHERE f.id=s.familyId LIMIT 1)
					WHEN primary_phone=4 THEN (SELECT f.guardianPhone FROM rms_family f WHERE f.id=s.familyId LIMIT 1)
				END tel,
				dg.stop_type AS stopType,
				DATE_FORMAT(s.create_date,'%d/%m/%Y') AS registrationDate,
				(SELECT COALESCE(NULLIF(it.`shortcut`,''),$grade) FROM `rms_itemsdetail` it WHERE (`it`.`id`=`dg`.`grade`) AND (`it`.`items_type`=1) LIMIT 1) as grade,
				
				dg.grade as gradeId,
				dg.feeId,
				vpm.paymentList,
				vpm.payment_term,
				vpm.stpaymentType,
				vpm.discountCode,
				vpm.discountValue,
				(SELECT SUBSTRING_INDEX(installmentOrdering,',',1) AS installmentOrdering FROM `rms_startdate_enddate` WHERE id=vpm.termPaidStartId) as startTerm
			  FROM 
				 rms_student AS s
				INNER JOIN `rms_group_detail_student` AS dg
				ON s.stu_id = dg.stu_id $condiontion
				LEFT JOIN $viewName AS vpm
				ON s.stu_id=vpm.studentId 

			  WHERE
			   	s.status=1
				AND dg.is_maingrade=1
				AND s.customer_type=1
				";
		$where = "";
	
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
			$s_where[] = " stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " stu_enname LIKE '%{$s_search}%'";
			$s_where[] = " tel LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.= " AND s.branch_id = ".$search['branch_id'];
		}
		if(!empty($search['academic_year'])){
			$where.= " AND dg.academic_year = ".$search['academic_year'];
			$where.= " AND vpm.academicYearId = ".$search['academic_year'];
		}
		if(!empty($search['degree'])){
			$where.= " AND dg.degree = ".$search['degree'];
		}
		if(!empty($search['grade']) AND $search['grade']>0){
			$where.= " AND dg.grade = ".$search['grade'];
		}
		
		$from_date =(empty($search['start_date']))? '1': "s.create_date >= '".$search['start_date']." 00:00:00'";
	    $to_date = (empty($search['end_date']))? '1': "s.create_date <= '".$search['end_date']." 23:59:59'";
		$where .= " AND ".$from_date." AND ".$to_date;

		if($search['active_type']>-1){
			
			if ($search['active_type'] == 0) {
				$where.= " AND dg.stop_type=0 AND dg.is_current=1";
			} else {
				$where.= " AND dg.stop_type!=0";
			}
		}
		
		if(!empty($search['studentType'])){
			$where.= " AND s.studentType = ".$search['studentType'];
		}
		if(!empty($search['pay_term'])){
			$where.= " AND vpm.payment_term=".$search['pay_term'];
		}
		if(!empty($search['payment_date'])){
			$where.= " AND FIND_IN_SET ('".$search['payment_date']."',vpm.PaidDateList)";
		}
		
		$where.=$_db->getAccessPermission('s.branch_id');
		
		$order=" order by s.stu_id DESC ";
		$db = $this->getAdapter();
		// echo $sql.$where.$order;
		// exit();
		
		$resultStudent =  $db->fetchAll($sql.$where.$order);
	
		if (!empty($resultStudent)) {
			foreach ($resultStudent as $key=> $result) {
				$dataPayment = json_decode($result['paymentList'],true);
				$extraColumns =  $this->ExtraColumns($dataPayment,$result['startTerm']);
				$resultStudent[$key]['paymentList'] = $extraColumns;// $this->getStudentPaymentStatistic($param);
			}
		}
		
		$paymentStatus = $search['paymentstatus']>-1?$search['paymentstatus']:-1; // or Finance etc.
		$filterTerm = ($search['termList']>0)?$search['termList']:0;

		if($paymentStatus>-1 AND $filterTerm>0){
			$resultStudent = array_filter($resultStudent, function ($var) use ($paymentStatus,$filterTerm) {
				return ($var['paymentList']['term'.$filterTerm] == $paymentStatus);
			});
		}
		// print_r($resultStudent);
		// exit();
		return $resultStudent;
	}
	function ExtraColumns($dataPayment,$startTerm)
	{
		//print_r($startTerm);
		$arrExtra = array(
			'stpaymentType'=>'',
			'discountCode'=>'',
			'term1' => 0,
			'term2' => 0,
			'term3' => 0,
			'term4' => 0,

			'periodDate1' => '',
			'periodDate2' => '',
			'periodDate3' => '',
			'periodDate4' => '',

			'payment1'=>'',
			'payment2'=>'',
			'payment3'=>'',
			'payment4'=>'',
		);
	
		if (!empty($dataPayment)) {
			$startTerm = !empty($startTerm)?$startTerm:0;

			
			foreach($dataPayment as $key=> $resultPayment){
				if ($startTerm > 4) {
					break;
				}
				
				if($resultPayment['payment_term']==4){//year
					$arrExtra['term1'] = 1;
					$arrExtra['term2'] = 1;
					$arrExtra['term3'] = 1;
					$arrExtra['term4'] = 1;
					$arrExtra['periodDate1'] = $resultPayment['paidDate'];
					$arrExtra['payment1'] = $resultPayment['totalpayment'];
					break;
				}
				if($resultPayment['payment_term']==3){//semestere
					
					if ($startTerm == 1) {  // semester 1
						$arrExtra['term1'] = 1;
						$arrExtra['term2'] = 1;
						$arrExtra['periodDate1'] = $resultPayment['paidDate'];
						$arrExtra['payment1'] = $resultPayment['totalpayment'];
						$startTerm = 2;//next loop will +1 continue to bottom step
						
					}elseif($startTerm == 3){//semester 2
						
						$arrExtra['term1'] = 1;//just update
						$arrExtra['term2'] = 1;//just update

						$arrExtra['term3'] = 1;
						$arrExtra['term4'] = 1;
						
						$arrExtra['periodDate3'] = $resultPayment['paidDate'];
						$arrExtra['payment3'] = $resultPayment['totalpayment'];
					}
				}
				if($resultPayment['payment_term']==2){//term1
					if ($startTerm == 1) {
						$arrExtra['term1'] = 1;
						$arrExtra['periodDate1'] = $resultPayment['paidDate'];
						$arrExtra['payment1'] = $resultPayment['totalpayment'];
					}elseif($startTerm == 2){//term2
						$arrExtra['term1'] = 1;//just update
						$arrExtra['term2'] = 1;
						$arrExtra['periodDate2'] = $resultPayment['paidDate'];
						$arrExtra['payment2'] = $resultPayment['totalpayment'];
				}elseif($startTerm == 3){//term3
						$arrExtra['term1'] = 1;//just update
						$arrExtra['term2'] = 1;//just update
						$arrExtra['term3'] = 1;
						$arrExtra['periodDate3'] =  $resultPayment['paidDate'];
						$arrExtra['payment3'] = $resultPayment['totalpayment'];
				}elseif($startTerm == 4){//term4
					    $arrExtra['term1'] = 1;//just update
						$arrExtra['term2'] = 1;//just update
						$arrExtra['term3'] = 1;//just update
						$arrExtra['term4'] = 1;
						$arrExtra['periodDate4'] = $resultPayment['paidDate'];
						$arrExtra['payment4'] = $resultPayment['totalpayment'];
					}
				}
				$startTerm=$startTerm+1;
				
			}
		} 
		return $arrExtra;
	}
} 
    
   