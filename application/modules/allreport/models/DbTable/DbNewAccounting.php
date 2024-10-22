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

		$sql = "SELECT 
				s.stu_id,
				(SELECT $branch FROM `rms_branch` WHERE rms_branch.br_id = s.branch_id LIMIT 1) AS branch_name,
				s.stu_code,
				CONCAT(stu_khname,'-',stu_enname) AS student_name,
				s.tel,
				(SELECT $label FROM `rms_view` WHERE type=40 and key_code=s.studentType LIMIT 1) AS studentType,
				DATE_FORMAT(s.create_date,'%d/%m/%Y') AS registrationDate,
				(SELECT REPLACE($grade,'Grade','') FROM `rms_itemsdetail` it WHERE (`it`.`id`=`dg`.`grade`) AND (`it`.`items_type`=1) LIMIT 1) as grade,
				dg.grade as gradeId,
				dg.feeId
			  FROM 
				 rms_student AS s
				INNER JOIN `rms_group_detail_student` AS dg
				ON s.stu_id = dg.stu_id  
			  WHERE
			   	s.status=1
				 AND s.customer_type=1
				 AND dg.itemType=1 
				 AND dg.is_maingrade=1
				 AND dg.is_current=1
				 AND dg.stop_type=0";
	
		// $from_date =(empty($search['start_date']))? '1': " $str_date >= '".$search['start_date']." 00:00:00'";
		// $to_date = (empty($search['end_date']))? '1': " $str_date <= '".$search['end_date']." 23:59:59'";

		$where = "";// " AND ".$from_date." AND ".$to_date;
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
			$s_where[] = " stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " stu_enname LIKE '%{$s_search}%'";
			$s_where[] = " tel LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.= " AND s.branch_id = ".$search['branch_id'];
		}
		if(!empty($search['start_date'])){
			$where.= " AND DATE_FORMAT(s.create_date, '%Y-%m-%d') ='".$search['start_date']."'";
		}
		if(!empty($search['grade']) AND $search['grade']>0){
			$where.= " AND dg.grade = ".$search['grade'];
		}
		if(!empty($search['studentType'])){
			$where.= " AND s.studentType = ".$search['studentType'];
		}
		if(!empty($search['pay_term'])){
			$where.= " AND s.stu_id IN (SELECT studentId FROM `v_studenttermpaid` WHERE payment_term=".$search['pay_term'].")";
		}
		if(!empty($search['payment_date'])){
			$where.= " AND s.stu_id IN (SELECT studentId FROM `v_studenttermpaid` WHERE  FIND_IN_SET ('".$search['payment_date']."',PaidDateList))";
		}

		
		$where.=$_db->getAccessPermission('s.branch_id');
		
		$order=" order by s.stu_id DESC ";
		$db = $this->getAdapter();
		$resultStudent =  $db->fetchAll($sql.$where.$order);
		if (!empty($resultStudent)) {
			foreach ($resultStudent as $key=> $result) {
				$param = array(
					'studentId'=>$result['stu_id'],
					'gradeId'=>$result['gradeId'],
					'feeId'=>$result['feeId'],
					'paidDate'=>empty($search['payment_date'])?'':$search['payment_date'],
				);
				$dataPayment = $this->getStudentPaymentStatistic($param);
				$extraColumns =  $this->ExtraColumns($dataPayment);
				$resultStudent[$key]['paymentList'] = $extraColumns;// $this->getStudentPaymentStatistic($param);
			}
		}
		return $resultStudent;
	}
	function ExtraColumns($dataPayment)
	{
		$arrExtra = array(
			'stpaymentType'=>'',
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
			foreach($dataPayment as $key=> $resultPayment){
				$arrExtra['stpaymentType'] = $resultPayment['stpaymentType'];
				if ($key > 3) {
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
				if($resultPayment['payment_term']==3){//semester
					if ($key == 0) {// semester 1
						$arrExtra['term1'] = 1;
						$arrExtra['term2'] = 1;
						$arrExtra['periodDate1'] = $resultPayment['paidDate'];
						$arrExtra['payment1'] = $resultPayment['totalpayment'];
					}elseif($key == 1){//semester 2
						$arrExtra['term3'] = 1;
						$arrExtra['term4'] = 1;
						
						$arrExtra['periodDate3'] = $resultPayment['paidDate'];
						$arrExtra['payment3'] = $resultPayment['totalpayment'];
					}
				}
				if($resultPayment['payment_term']==2){//term1
					if ($key == 0) {
						$arrExtra['term1'] = 1;
						$arrExtra['periodDate1'] = $resultPayment['paidDate'];
						$arrExtra['payment1'] = $resultPayment['totalpayment'];
					}elseif($key == 1){//term2
						$arrExtra['term2'] = 1;
						$arrExtra['periodDate2'] = $resultPayment['paidDate'];
						$arrExtra['payment2'] = $resultPayment['totalpayment'];
				}elseif($key == 2){//term3
						$arrExtra['term3'] = 1;
						$arrExtra['periodDate3'] =  $resultPayment['paidDate'];
						$arrExtra['payment3'] = $resultPayment['totalpayment'];
				}elseif($key == 3){//term4
						$arrExtra['term4'] = 1;
						$arrExtra['periodDate4'] = $resultPayment['paidDate'];
						$arrExtra['payment4'] = $resultPayment['totalpayment'];
					}
				}
			}
		} 
		return $arrExtra;
	}
	function getStudentPaymentStatistic($search=null){
		try{
			$dbGb = new Application_Model_DbTable_DbGlobal();
			$currentLang = $dbGb->currentlang();
			$branch_id = $dbGb->getAccessPermission('sm.branch_id');
			$branch = $dbGb->getBranchDisplay();
			
			$labelFull = $dbGb->getViewLabelDisplay();
			$labelShort = $dbGb->getViewLabelDisplay("shortcut");
			
			$columnItem = 'title_en';
			if ($currentLang == 1) {
				$columnItem = 'title';
			}
			
			$db=$this->getAdapter();
			$fromDate =(empty($search['start_date']))? '1': " DATE_FORMAT(spmt.`create_date`, '%Y-%m-%d %H:%i:%s') >= '".$search['start_date']." 00:00:00'";
			$toDate = (empty($search['end_date']))? '1': " DATE_FORMAT(spmt.`create_date`, '%Y-%m-%d %H:%i:%s') <= '".$search['end_date']." 23:59:59'";
			$sql=" SELECT 
					sm.create_date as paidDate,
					smd.itemdetail_id,
					smd.payment_term,
					(SELECT name_en FROM `rms_view` WHERE type=6 and key_code=smd.payment_term LIMIT 1) AS stpaymentType,
					smd.totalpayment,
					smd.start_date,
					smd.validate,
					smd.academicFeeTermId

					FROM 
						`rms_student_payment` sm
						JOIN `rms_student_paymentdetail` smd
						ON sm.id=smd.payment_id 
					WHERE 
						sm.`status`=1 
						AND sm.is_void=0
						AND smd.service_type=1
						AND payment_term!=5 
						AND payment_term!=6
						$branch_id ";

			if(!empty($search['branch_id'])){
				$sql.= " AND sm.branch_id = ".$search['branch_id'];
			}
			if(!empty($search['studentId'])){
				$sql.= " AND sm.student_id = ".$search['studentId'];
			}
			if(!empty($search['gradeId'])){
				$sql.= " AND smd.itemdetail_id = ".$search['gradeId'];
			}
			if(!empty($search['feeId'])){
				$sql.= " AND smd.feeId = ".$search['feeId'];
			}
			if(!empty($search['paidDate'])){
				$sql.= " AND DATE_FORMAT(sm.create_date, '%Y-%m-%d') = '".$search['paidDate']."'";
			}
			
			$order = " ORDER BY payment_term DESC , sm.create_date ASC ";
			return $db->fetchAll($sql.$order);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
	}
} 
    
   