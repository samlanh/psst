<?php

class Api_Model_DbTable_DbApi extends Zend_Db_Table_Abstract
{
	function getStudentLogin($_data){
		$db = $this->getAdapter();
// 		$db->beginTransaction();
		$_data['studentCode']=trim($_data['studentCode']);
		$_data['password']=trim($_data['password']);
		try{
			$sql =" SELECT
				s.*,
				s.stu_id AS id,
				s.stu_code AS stuCode,
				s.stu_khname AS stuNameKH,
				s.stu_enname AS stuFirstName,
				s.last_name AS stuLastName,
				s.photo
			FROM
				rms_student AS s,
				`rms_group_detail_student` AS gd
			WHERE s.status = 1 AND s.customer_type =1 
			AND gd.`stu_id` = s.`stu_id` 
			AND gd.`is_maingrade` =	1
			AND gd.`is_current` = 1 
			AND gd.`stop_type` != 1 
			";
			$sql.= " AND ".$db->quoteInto('s.stu_code=?', $_data['studentCode']);
			$sql.= " AND ".$db->quoteInto('s.password=?', md5($_data['password']));
			$sql.=" GROUP BY s.stu_id ";
			$row = $db->fetchRow($sql);
			
			$result = array(
					'status' =>true,
					'value' =>$row,
			);
			return $result;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	function checkChangePassword($_data){
		$db = $this->getAdapter();
		try{
	
			$sql ="
			SELECT
			s.stu_id AS id,
			s.stu_code AS stuCode,
			s.stu_khname AS stuNameKH,
			s.stu_enname AS stuFirstName,
			s.last_name AS stuLastName,
			s.photo
			FROM
			rms_student AS s
			WHERE s.status = 1 AND s.customer_type =1 ";
			$sql.= " AND ".$db->quoteInto('s.stu_id=?', $_data['stu_id']);
			$sql.= " AND ".$db->quoteInto('s.password=?', md5($_data['oldPassword']));
			$row = $db->fetchRow($sql);
			if (empty($row)){
				return false;
			}
			return true;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
		}
	}
	function changePassword($_data){
		$db = $this->getAdapter();
		try{
			$_arr=array(
					'password'	  	=> md5($_data['newPassword']),
			);
			$this->_name = "rms_student";
			$where = $this->getAdapter()->quoteInto("stu_id=?",$_data['stu_id']);
			$this->update($_arr, $where);
			return true;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
		}
	}
	function getStudentInformation($_data){
		$_db = $this->getAdapter();
		try{
			
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$stu_id = empty($_data['stu_id'])?1:$_data['stu_id'];
			$_data['groupId'] = empty($_data['groupId'])?0:$_data['groupId'];
			$colunmname='title_en';
			$lbView="name_en";
			$branch = "branch_nameen";
			$schoolName = "school_nameen";
	
			$province = "province_en_name";
			$commune = "commune_name";
			$district = "district_name";
			$vill = 'village_name';
			$occuTitle='occu_enname';
			if ($currentLang==1){
				$colunmname='title';
				$lbView="name_kh";
				$branch = "branch_namekh";
				$schoolName = "school_namekh";
		   
				$province = "province_kh_name";
				$commune = "commune_namekh";
				$district = "district_namekh";
				$vill = 'village_namekh';
				$occuTitle = 'occu_name';
			}
	//s.stu_khname,
			$sql ="SELECT
						s.*,
						s.stu_id AS studentId,
						CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) as studenLatinName,
						CASE
								WHEN s.primary_phone = 1 THEN s.tel
								WHEN s.primary_phone = 2 THEN COALESCE(fam.fatherPhone,'')
								WHEN s.primary_phone = 3 THEN COALESCE(fam.motherPhone,'')
								ELSE COALESCE(fam.guardianPhone,'')
						END as PrimaryContact,
						COALESCE(DATE_FORMAT(s.dob, '%d-%m-%Y'),'') AS dobFormat,
						
						fam.fatherName AS fatherLatinName,
						fam.fatherNameKh AS fatherKhmerName,
						COALESCE(DATE_FORMAT(fam.fatherDob, '%d-%m-%Y'),'') AS fatherDobFormat,
						
						fam.motherName AS motherLatinName,
						fam.motherNameKh AS motherKhmerName,
						
						COALESCE(DATE_FORMAT(fam.motherDob, '%d-%m-%Y'),'') AS motherDobFormat,
						
						fam.guardianName AS guardianLatinName,
						fam.guardianNameKh AS guardianKhmerName,
						COALESCE(DATE_FORMAT(fam.guardianDob, '%d-%m-%Y'),'') AS guardianDobFormat,
						
						g.branch_id AS branchId,
						gds.degree,
						gds.group_id,
						CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS name_englsih,
						(SELECT $lbView from rms_view where type=2 and key_code=s.sex LIMIT 1) as genderTitle,
						(SELECT $lbView FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality,
						(SELECT $lbView FROM rms_view where type=21 and key_code=fam.fatherNation LIMIT 1) AS fatherNation,
		    			(SELECT $lbView FROM rms_view where type=21 and key_code=fam.motherNation LIMIT 1) AS motherNation,
		    			(SELECT $lbView FROM rms_view where type=21 and key_code=fam.guardianNation LIMIT 1) AS guardianNation,
						 
						(SELECT $province FROM rms_province AS p WHERE p.province_id=s.province_id LIMIT 1) AS provinceTitle,
						(SELECT $district FROM ln_district AS p WHERE p.dis_id=s.district_name LIMIT 1) AS districtTitle,
						(SELECT $commune FROM ln_commune AS p WHERE p.com_id=s.commune_name LIMIT 1) AS communeTitle,
						(SELECT $vill FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name,
						 
						 g.group_code AS groupCode,
						(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=fam.fatherJob LIMIT 1) AS fatherOccupation,
						(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=fam.motherJob LIMIT 1) AS motherOccupation,
						(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=fam.guardianJob LIMIT 1) AS guardian_job,
						(SELECT rms_items.$colunmname FROM rms_items WHERE rms_items.id=g.degree AND rms_items.type=1 LIMIT 1)AS degreeTitle,
						(SELECT rms_itemsdetail.$colunmname FROM rms_itemsdetail WHERE rms_itemsdetail.id=g.grade AND rms_itemsdetail.items_type=1 LIMIT 1)AS gradeTitle,
						(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS academicYearTitle
						,'1' AS isFromStudentTB
						
			FROM
				rms_student AS s 
				JOIN rms_group_detail_student AS gds ON s.stu_id = gds.stu_id
				LEFT JOIN rms_group AS g ON g.id = gds.group_id 
				LEFT JOIN rms_family AS fam ON fam.id = s.familyId
			WHERE
				gds.itemType=1 
				AND s.stu_id=$stu_id ";
			
			if(!empty($_data['groupId'])){ // profileByFilterGroup
				$sql.=" AND gds.group_id = ".$_data['groupId'];
			}else{ // currentProfile
				$sql.=" AND gds.is_maingrade =1
						AND gds.is_current =1 ";
			}
			$sql.=" LIMIT 1";
			$row = $_db->fetchAll($sql);
			$result = array(
					'status' =>true,
					'value' =>$row,
			);
			return $result;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
    function getDailyReport($search=null){
    	$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$lbView="name_en";
			$branch = "branch_nameen";
			$schoolName = "school_nameen";
			
			if ($currentLang==1){
				$lbView="name_kh";
				$branch = "branch_namekh";
				$schoolName = "school_namekh";
			}
			
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    		$sql=" 
    		SELECT
	    		sp.id,
	    		sp.receipt_number,
	    		COALESCE(DATE_FORMAT(sp.create_date, '%d-%m-%Y %H:%i'),'') AS  createDate,
	    		COALESCE((SELECT title FROM `rms_items` WHERE rms_items.id=sp.degree LIMIT 1 ),'N/A') AS degree,
				COALESCE((SELECT title FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=sp.grade LIMIT 1),'N/A') AS grade,
	    		(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee WHERE `status`=1 AND id=sp.academic_year LIMIT 1) AS year,
	    		FORMAT(sp.grand_total,2)  AS totalPayment,
	    		FORMAT(sp.credit_memo,2)  AS creditMemo,
	    		FORMAT(sp.penalty,2)  AS penalty,
	    		FORMAT(sp.paid_amount,2)  AS paidAmount,
	    		FORMAT(sp.balance_due,2)  AS balanceDue,
	    		(SELECT $lbView FROM rms_view WHERE type=8 AND key_code=sp.payment_method LIMIT 1) AS paymentMethod,
	    		(SELECT rms_users.first_name FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS byUser
    		FROM
	    		rms_student_payment AS sp
    		WHERE sp.status=1 AND sp.is_void=0 AND sp.student_id = ".$search['stu_id'];
    		$where = " AND ".$from_date." AND ".$to_date;
    		if(!empty($search['adv_search'])){
    			$s_where=array();
    			$s_search= addslashes(trim($search['adv_search']));
    			$s_where[]=" sp.receipt_number LIKE '%{$s_search}%'";
    			$where.=' AND ('.implode(' OR ', $s_where).')';
    		}
    		$order=" ORDER By sp.id DESC ";
    		$row = $db->fetchAll($sql.$where.$order);
    		
    		$result = array(
    				'status' =>true,
    				'value' =>$row,
    				);
    		return $result;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$result = array(
    				'status' =>false,
    				'value' =>$e->getMessage(),
    				);
    		return $result;
    	}
    }
    
    function getPayment($payment_id,$currentLang=1){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
//     		$lbView="name_en";
//     		$branch = "branch_nameen";
//     		$schoolName = "school_nameen";
    			
//     		if ($currentLang==1){
//     			$lbView="name_kh";
//     			$branch = "branch_namekh";
//     			$schoolName = "school_namekh";
//     		}
//     		$sql="
//     		SELECT
// 	    		sp.id,
// 	    		sp.receipt_number,
// 	    		DATE_FORMAT(sp.create_date, '%d-%m-%Y %H:%i') AS  createDate,
// 	    		sp.is_void,
// 	    		(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee WHERE `status`=1 AND id=sp.academic_year LIMIT 1) AS year,
// 	    		(SELECT $lbView FROM rms_view WHERE type=10 AND key_code=sp.is_void LIMIT 1) AS voidStatus,
	    		 
// 	    		FORMAT(sp.grand_total,2)  AS totalPayment,
// 	    		FORMAT(sp.credit_memo,2)  AS creditMemo,
// 	    		FORMAT(sp.penalty,2)  AS penalty,
// 	    		FORMAT(sp.paid_amount,2)  AS paidAmount,
// 	    		FORMAT(sp.balance_due,2)  AS balanceDue,
// 	    		(SELECT $lbView FROM rms_view WHERE type=8 AND key_code=sp.payment_method LIMIT 1) AS paymentMethod,
// 	    		(SELECT rms_users.first_name FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS byUser
//     		FROM
//     			rms_student_payment AS sp
//     		WHERE sp.id = ".$payment_id;
//     		$where = "";
//     		$row = $db->fetchRow($sql.$where);
    		
    		$rowDetail = $this->getPaymentDetail($payment_id,$currentLang);
//     		$queryArr = array(
//     				'row' =>$row,
//     				'rowDetail' =>$rowDetail,
//     				);
    		$result = array(
    				'status' =>true,
    				'value' =>$rowDetail,
    		);
    		return $result;
    	}catch(Exception $e){
	    	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	$result = array(
	    			'status' =>false,
	    			'value' =>$e->getMessage(),
	    			);
	    			return $result;
	    	}
    	}
    		
    public function getPaymentDetail($payment_id,$currentLang=1){
    	$db = $this->getAdapter();
		try{
	    	$label = "name_en";
	    	$branch = "branch_nameen";
	    	$grade = "rms_itemsdetail.title_en";
	    	$degree = "rms_items.title_en";
	    	if($currentLang==1){// khmer
	    		$label = "name_kh";
	    		$branch = "branch_namekh";
	    		$grade = "rms_itemsdetail.title";
	    		$degree = "rms_items.title";
	    	}
    	$sql=" 
	    	SELECT
		    	spd.id,
		    	spd.payment_id,
		    	spd.is_onepayment,
		    	sp.receipt_number as receipt_number,
		    	FORMAT(sp.grand_total,2)  AS totalPayment,
		    	FORMAT(sp.paid_amount,2)  AS paidAmount,
		    	FORMAT(sp.balance_due,2)  AS balanceDue,
		    	sp.`amount_in_khmer` as amount_in_khmer,
		    	FORMAT(spd.subtotal,2)  AS subTotal,
		    	FORMAT(spd.paidamount,2)  AS paidAmountDetail,
		    	FORMAT(spd.fee,2)  AS fee,
		    	FORMAT(spd.qty,2)  AS qty,
		    	FORMAT(spd.extra_fee,2)  AS extraFee,
		    	COALESCE(FORMAT(spd.discount_amount,2),0)  AS discountAmount,
		    	COALESCE(FORMAT(spd.discount_percent,2),0) AS discountPercent,
		    	
		    	COALESCE(DATE_FORMAT(spd.start_date, '%d-%m-%Y'),'N/A') AS  startDate,
		    	COALESCE(DATE_FORMAT(spd.validate, '%d-%m-%Y'),'N/A') AS  validate,
		    	(SELECT dis_name FROM `rms_discount` WHERE disco_id=spd.discount_type LIMIT 1) AS discount_type,
		    	spd.service_type,
		    	spd.note,
		    	(SELECT $grade FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=spd.itemdetail_id LIMIT 1) AS serviceTitle,
		    	(SELECT $degree FROM rms_items,rms_itemsdetail As itd  WHERE rms_items.id =itd.items_id AND itd.id=spd.itemdetail_id LIMIT 1 ) AS serviceCategory,
		    	(SELECT items_type FROM `rms_itemsdetail` WHERE id=spd.itemdetail_id LIMIT 1) AS items_type,
				 COALESCE((SELECT $label FROM `rms_view` WHERE  `type`=6 AND key_code= spd.payment_term LIMIT 1),'One Payment') payment_term,    	
	
		    	(SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=sp.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
		    	(SELECT first_name FROM `rms_users` WHERE `rms_users`.id = sp.user_id LIMIT 1) AS byUser
	    	FROM
		    	rms_student_payment as sp,
		    	rms_student_paymentdetail AS spd ";
		    	$sql.='WHERE sp.id=spd.payment_id
		    	AND spd.payment_id = '.$payment_id;
		    	
	    	$row = $db->fetchAll($sql);
	    	return $row;
// 	    	$result = array(
// 	    			'status' =>true,
// 	    			'value' =>$row,
// 	    	);
// 	    	return $result;
	    }catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$result = array(
    				'status' =>false,
    				'value' =>$e->getMessage(),
    		);
    		return $result;
	    }
    }
    function getSchedule($search = array()){
    	$db = $this->getAdapter();
    	try{
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$search['stu_id'] = empty($search['stu_id'])?1:$search['stu_id'];
    		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    		$dayId = empty($search['dayId'])?1:$search['dayId'];
    		$stuInfo = $this->getStudentInformation($search);
    		
    		$arrStudyValue = array();
    		$dayIndex="";
    		$arrStudyValue = $this->getSubjectTeacherByScheduleAndGroup($stuInfo,$dayId ,$currentLang);
    		$arrQuery = array(
    				'arrStudyValue' => $arrStudyValue
    				);
    		$result = array(
    				'status' =>true,
    				'value' =>$arrQuery,
    		);
    		return $result;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$result = array(
    				'status' =>false,
    				'value' =>$e->getMessage(),
    		);
    		return $result;
	    }
    }
//     public function getDaySchedule($stuInfo,$search,$currentLang){
//     	$db=$this->getAdapter();
//     	$label = "name_en";
//     	if($currentLang==1){// khmer
//     		$label = "name_kh";
//     	}
//     	$academicYear = empty($stuInfo['value'][0]['academic_year'])?0:$stuInfo['value'][0]['academic_year'];
//     	$groupId = empty($stuInfo['value'][0]['group_id'])?0:$stuInfo['value'][0]['group_id'];
//     	$groupId = empty($search['group_id'])?$groupId:$search['group_id'];
//     	$sql="
//     		SELECT
// 		    	v.key_code as id,
// 		    	v.$label as name
// 	    	FROM
// 		    	rms_view as v,
// 		    	rms_group_reschedule as gs
// 	    	WHERE
// 		    	v.key_code = gs.day_id
// 		    	AND v.type = 18
// 		    	AND gs.group_id = $groupId
		    
// 		    	group by
// 		    	gs.day_id
// 		    	ORDER BY
// 		    	gs.day_id ASC ";
//     	return $db->fetchAll($sql);
//     }
    function getTimeSchelduleByYGS($stuInfo,$search,$currentLang){ /* get Time for show in schedule VD*/
    	$db=$this->getAdapter();
    	$academicYear = empty($stuInfo['value'][0]['academic_year'])?0:$stuInfo['value'][0]['academic_year'];
    	$groupId = empty($stuInfo['value'][0]['group_id'])?0:$stuInfo['value'][0]['group_id'];
    	$groupId = empty($search['group_id'])?$groupId:$search['group_id'];
    	$sql="
    	SELECT gr.from_hour,
    		REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times
    	FROM 
    		rms_group_reschedule AS gr
    	WHERE 
    		 gr.group_id=$groupId
    	GROUP BY REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','')
    	ORDER BY times ASC";
    	//gr.year_id=$academicYear AND
    	$row = $db->fetchAll($sql);
    	return $row;
    }
    function getSubjectTeacherByScheduleAndGroup($stuInfo,$day,$currentLang){
    	$db=$this->getAdapter();
    	$subjecct = "subject_titleen";
    	$teacher = "teacher_name_en";
    	if($currentLang==1){// khmer
    		$subjecct = "subject_titlekh";
    		$teacher = "teacher_name_kh";
    	}
    	$academicYear = empty($stuInfo['value'][0]['academic_year'])?0:$stuInfo['value'][0]['academic_year'];
    	$groupId = empty($stuInfo['value'][0]['group_id'])?0:$stuInfo['value'][0]['group_id'];
    	$sql="
    	SELECT gr.from_hour,
	    	REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS timeValueConcat,
	    	(SELECT s.$subjecct FROM rms_subject AS s WHERE s.id=gr.subject_id LIMIT 1) AS subject_name,
	    	(SELECT t.$teacher FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_name,
	    	(SELECT t.tel FROM rms_teacher AS t WHERE t.id=gr.techer_id LIMIT 1) AS teacher_phone,
			(SELECT t.title FROM rms_timeseting As t WHERE t.value =gr.from_hour LIMIT 1) AS fromHourTitle,
			(SELECT t.title FROM rms_timeseting As t WHERE t.value =gr.to_hour LIMIT 1) AS toHourTitle,
			REPLACE(CONCAT((SELECT t.title FROM rms_timeseting As t WHERE t.value =gr.from_hour LIMIT 1),'-',(SELECT t.title FROM rms_timeseting As t WHERE t.value =gr.to_hour LIMIT 1)),' ','') AS times

    	FROM 
    		rms_group_reschedule AS gr
    	WHERE 
    		 gr.group_id=$groupId
	    	AND gr.`day_id` =$day ";
    	//gr.year_id=$academicYear AND
    	return $db->fetchAll($sql);
    }
    
    function getRatingValuation(){
    	$db = $this->getAdapter();
    	$sql="SELECT r.id,r.rating AS `name` FROM `rms_rating` AS r ";
    	return $db->fetchAll($sql);
    }
    function getStudentEvaluation($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
	    	$sql="
		    	SELECT ed.*,
			    	(SELECT cm.comment FROM `rms_comment` AS cm WHERE cm.id = ed.comment_id LIMIT 1) AS comments,
			    	(SELECT rt.rating FROM `rms_rating` AS rt WHERE rt.id = ed.rating_id LIMIT 1) AS ratingTitle,
			    	e.issue_date,
			    	e.return_date,
			    	DATE_FORMAT(e.issue_date, '%d-%m-%Y %H:%i') AS  issueDate,
			    	DATE_FORMAT(e.return_date, '%d-%m-%Y %H:%i') AS  returnDate,
			    	e.teacher_comment
		    	FROM 
		    		`rms_student_evaluation_detail` AS ed,
		    		`rms_student_evaluation` AS e
		    	WHERE
			    	e.id = ed.evaluation_id
			    	AND e.status=1
	    	";
	    	if (!empty($data['group_id'])){
	    		$sql.=" AND e.group_id=".$data['group_id'];
	    	}
	    	if (!empty($data['stu_id'])){
	    		$sql.=" AND e.student_id=".$data['stu_id'];
	    	}
	    	if (!empty($data['exam_type'])){
	    		$sql.=" AND e.for_type=".$data['exam_type'];
	    		if ($data['exam_type']==1){
	    			if (!empty($data['for_month'])){
	    				$sql.=" AND e.for_month=".$data['for_month'];
	    			}
	    		}else if ($data['exam_type']==2){
	    			if (!empty($data['for_semester'])){
	    				$sql.=" AND e.for_semester=".$data['for_semester'];
	    			}
	    		}
	    	}
	    	$sql.=" ORDER BY e.id DESC";
	    	$row = $db->fetchAll($sql);
	    	$rating = $this->getRatingValuation();
	    	
	    	$row = array(
	    			'rating'=> $rating,
	    			'envaluation'=> $row
	    			);
	    	
	    	$result = array(
	    			'status' =>true,
	    			'value' =>$row,
	    	);
	    	return $result;
	    	
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$result = array(
    				'status' =>false,
    				'value' =>$e->getMessage(),
    		);
    		return $result;
    	}
    }
	function getScoreExame($search = array()){
    	$db = $this->getAdapter();
    	try{
    		$dbgb = new Application_Model_DbTable_DbGlobal();
			
    		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
    		$currentLang = $search['currentLang'];
			
    		$search['stu_id'] = empty($search['stu_id'])?1:$search['stu_id'];
    		$stu_id = $search['stu_id'];
    		$stuInfo = $this->getStudentInformation($search);
			
    		$score_id = empty($search['score_id'])?1:$search['score_id'];
    		$examRow = $this->getExamRow($stuInfo,$search);
    		$rowDetail = $this->getExamRowDetail($examRow, $stu_id, $currentLang);
    		$row = array(
    				'row'=> $examRow,
    				'rowDetail'=> $rowDetail
    		);
    		$result = array(
    				'status' =>true,
    				'value' =>$row,
    		);
    		return $result;
    		
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$result = array(
    				'status' =>false,
    				'value' =>$e->getMessage(),
    		);
    		return $result;
    	}
    }
	function getExamRow($stuInfo,$search = array()){
    	$db = $this->getAdapter();
    	try{
	    	$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
	    	$for_month = "month_en";
	    	if($currentLang==1){// khmer
	    		$for_month = "month_kh";
	    	}
	    	$score_id = empty($search['score_id'])?1:$search['score_id'];
	    	$academicYear = empty($stuInfo['value'][0]['academic_year'])?0:$stuInfo['value'][0]['academic_year'];
	    	$groupId = empty($stuInfo['value'][0]['group_id'])?0:$stuInfo['value'][0]['group_id'];
	    	$groupId = empty($search['group_id'])?$groupId:$search['group_id'];
	    	$stuId = empty($stuInfo['value'][0]['stu_id'])?0:$stuInfo['value'][0]['stu_id'];
	    	$sql="
	    		SELECT
		    	s.`id`,
		    	(SELECT $for_month FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthTitle,
		    	s.exam_type,
		    	s.for_month AS for_month_id,
		    	s.for_semester,
		    	s.reportdate,
		    	s.title_score,
		    	s.max_score,
		    	s.group_id,
		    	(SELECT g.academic_year FROM rms_group as g WHERE g.id =s.group_id LIMIT 1 ) AS academicYearId,
		    	(SELECT g.degree FROM rms_group as g WHERE g.id =s.group_id LIMIT 1 ) AS degreeId,
		    	(SELECT g.grade FROM rms_group as g WHERE g.id =s.group_id LIMIT 1 ) AS gradeId,
		    	sd.`student_id`,
		    	sm.total_score,
		    	sm.total_avg,
		    	FIND_IN_SET( total_avg, 
			    	(
				    	SELECT GROUP_CONCAT( total_avg
				    	ORDER BY total_avg DESC )
				    	FROM rms_score_monthly AS dd ,rms_score AS ss WHERE
				    	ss.`id`=dd.`score_id`
				    	AND ss.group_id= s.`group_id`
				    	AND ss.id=s.`id`
			    	)
		    	) AS rank
		    	
	    	FROM
		    	`rms_score` AS s,
		    	`rms_score_detail` AS sd,
		    	`rms_score_monthly` AS sm
		    	
	    	WHERE 
	    		s.`id`=sd.`score_id`
		    	AND sd.student_id = sm.`student_id`
			    	AND s.`id`=sm.`score_id`
			    	AND s.status = 1
			    	
	    	";
	    	if (!empty($score_id)){
	    		$sql.=" AND s.`id`=".$score_id;
	    	}else{
	    		$sql.=" AND s.`group_id`=".$groupId;
	    		$sql.=" AND sd.`student_id`=".$stuId;
	    		if (!empty($search['exam_type'])){
	    			$sql.=" AND s.exam_type=".$search['exam_type'];
	    			if ($search['exam_type']==1){
	    				if (!empty($search['for_month'])){
	    					$sql.=" AND s.`for_month`=".$search['for_month'];
	    				}
	    			}else if ($search['exam_type']==2){
	    				if (!empty($search['for_semester'])){
	    					$sql.=" AND s.`for_semester`=".$search['for_semester'];
	    				}
	    			}
	    		}
	    	}
	    		
	    	$sql.=" ORDER BY s.id DESC LIMIT 1";
	    	return $db->fetchRow($sql);
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$result = array(
    				'status' =>false,
    				'value' =>$e->getMessage(),
    		);
    		return $result;
    	}
    }
    public function getExamRowDetail($examRow,$student_id,$currentLang){
    	$db = $this->getAdapter();
    	$subjectTitle = "subject_titleen";
    	$mentiontype = 3;
    	if($currentLang==1){// khmer
    		$subjectTitle = "subject_titlekh";
    		$mentiontype = 2;
    	}
    	$score_id = empty($examRow['id'])?0:$examRow['id'];
    	$academicYearId = empty($examRow['academicYearId'])?0:$examRow['academicYearId'];
    	$degreeId = empty($examRow['degreeId'])?0:$examRow['degreeId'];
    	$gradeId = empty($examRow['gradeId'])?0:$examRow['gradeId'];
    	
    	$column="metion_grade";
    	if ($mentiontype==1){//grade A/B/C
    		$column="metion_grade";
    	}else if ($mentiontype==2){// ល្អប្រសើរ/ល្អណាស់/ល្អ
    		$column="metion_in_khmer";
    	}else if ($mentiontype==3){// Excellent/Very  Good/Good
    		$column="mention_in_english";
    	}//,sd.max_score
    	$sql="
    	SELECT
	    	sd.`score`,
	    	FIND_IN_SET( sd.`score`, (
		    	SELECT GROUP_CONCAT( dd.score
		    		ORDER BY dd.score DESC )
		    	FROM 
			    	rms_score_detail AS dd ,
			    	rms_score AS ss 
			    WHERE
			    	ss.`id`=dd.`score_id`
			    	AND ss.`id`= $score_id
			    	AND dd.subject_id=sd.`subject_id`
		    	)
		    ) AS rank,
    		sd.score_cut,
    		sd.`subject_id`,
	    	(SELECT (sj.$subjectTitle) FROM `rms_subject` AS sj WHERE sj.id = sd.`subject_id` LIMIT 1) AS subjecTitle
	    	,sd.amount_subject,
			(SELECT 
				 setd.$column AS mention
					FROM `rms_metionscore_setting_detail` AS setd,
						`rms_metionscore_setting` AS sets
					WHERE sets.id = setd.metion_score_id
						AND sets.academic_year=$academicYearId
						AND sets.degree = $degreeId
						AND sd.`score` < setd.max_score
						ORDER BY setd.max_score ASC
						LIMIT 1
			) AS mention,
			(SELECT 
				 setd.metion_grade AS mention
					FROM `rms_metionscore_setting_detail` AS setd,
						`rms_metionscore_setting` AS sets
					WHERE sets.id = setd.metion_score_id
						AND sets.academic_year=$academicYearId
						AND sets.degree = $degreeId
						AND sd.`score` < setd.max_score
						ORDER BY setd.max_score ASC
						LIMIT 1
			) AS mentionGrade

    	FROM  `rms_score_detail` AS sd
    	WHERE sd.`score_id`=$score_id AND sd.`student_id`=$student_id ";
    	return $db->fetchAll($sql);
    }
    
    function getAttendenceBydate($search = array()){
    	$db = $this->getAdapter();
    	try{
    		$stuId = $search['stu_id'];
    		$LangId = $search['currentLang'];
    		
    		$stuInfo = $this->getStudentInformation($search);
	    	$groupId = empty($stuInfo['value'][0]['group_id'])?0:$stuInfo['value'][0]['group_id'];
	    	$groupId = empty($search['group_id'])?$groupId:$search['group_id'];
	    	
	    	$where_status = " AND att.type =1 AND att.id=sad.attendence_id AND att.group_id=$groupId AND sad.`stu_id`=$stuId AND MONTH(att.date_attendence) = MONTH(sta.date_attendence) GROUP BY MONTH(att.date_attendence) ";
	    	$sql="SELECT
		    	DATE_FORMAT(sta.date_attendence, '%m') AS  dateAttendence,
		    	DATE_FORMAT(sta.date_attendence, '%M') AS  dateLabel,
		    	sta.group_id,
		    	(SELECT COUNT(sad.id) FROM rms_student_attendence AS att, rms_student_attendence_detail AS sad WHERE sad.attendence_status = 1 $where_status  ) AS COME,
		    	(SELECT COUNT(sad.id) FROM rms_student_attendence AS att, rms_student_attendence_detail AS sad WHERE sad.attendence_status = 2 $where_status) AS ABSENT,
		    	(SELECT COUNT(sad.id) FROM rms_student_attendence AS att, rms_student_attendence_detail AS sad WHERE sad.attendence_status = 3 $where_status) AS PERMISSION,
		    	(SELECT COUNT(sad.id) FROM rms_student_attendence AS att, rms_student_attendence_detail AS sad WHERE sad.attendence_status = 4 $where_status) AS LATE,
		    	(SELECT COUNT(sad.id) FROM rms_student_attendence AS att, rms_student_attendence_detail AS sad WHERE sad.attendence_status = 5 $where_status) AS EarlyLeave
   	
	    	FROM rms_student_attendence_detail AS sade,
		    	`rms_student_attendence` AS sta
		    	WHERE sta.`id` = sade.`attendence_id` AND sta.type=1 ";
	    	$where = "";
	    	$where.=" AND sade.`stu_id`=$stuId AND sta.`group_id`=$groupId 
		    	GROUP BY MONTH(sta.date_attendence) ORDER BY sta.`date_attendence` DESC ";
		    
	    	$row =  $db->fetchAll($sql.$where);
	    	
	    	$come = 0;$absent=0;$permission=0;$late=0;$earlyleave=0;$amt = 0;$className='';$academicYear='';
	    	$result = array();
	    	foreach($row as  $index => $rs){
	    		$result[$index]['dateAttendence'] = $rs['dateAttendence'];
	    		$result[$index]['dateLabel'] = $rs['dateLabel'];
	    		$result[$index]['COME'] = empty($rs['COME'])?0:$rs['COME'];
	    		$result[$index]['ABSENT'] = empty($rs['ABSENT'])?0:$rs['ABSENT'];
	    		$result[$index]['PERMISSION'] = empty($rs['PERMISSION'])?0:$rs['PERMISSION'];
	    		$result[$index]['LATE'] = empty($rs['LATE'])?0:$rs['LATE'];
	    		$result[$index]['EarlyLeave'] = empty($rs['EarlyLeave'])?0:$rs['EarlyLeave'];
	    		$result[$index]['TOTAL_ATTRECORD'] = $rs['ABSENT']+$rs['PERMISSION']+$rs['LATE']+$rs['EarlyLeave'];	    		
	    		$result[$index]['group_id'] = $rs['group_id'];
	    		
	    		$come = $come+$rs['COME'];
	    		$absent=$absent+$rs['ABSENT'];
	    		$permission=$permission+$rs['PERMISSION'];
	    		$late=$late+$rs['LATE'];
	    		$earlyleave=$earlyleave+$rs['EarlyLeave'];
	    		
	    	}
	    	$sql=" SELECT 
	    	(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS acarYear,
	    	group_code AS className FROM rms_tuitionfee AS f,rms_group as g WHERE g.id=$groupId AND f.id=g.academic_year LIMIT 1 ";
	    	$rsGroup = $db->fetchRow($sql);
	      
	    	if(!empty($rsGroup)){
	    		$className = $rsGroup['className'];
	    		$academicYear = $rsGroup['acarYear'];
	    	}
	    	$amt = $absent+$permission+$late+$earlyleave;
	    	$summary_arr = array('acarYear'=>$academicYear,'className'=>$className,'COME'=>$come,"ABSENT"=>$absent,"PERMISSION"=>$permission,"LATE"=>$late,"EarlyLeave"=>$earlyleave,"TOTALAMT"=>$amt);
	    	
	    	
	    	$sql="SELECT atnd.title,atnd.description
			    	FROM `mobile_attendencenote` AS atn,
			    		`mobile_attendencenote_detail` AS atnd
			    	WHERE atn.id=atnd.attendance_id
			    		AND atnd.lang=$LangId
	    	ORDER BY atn.ordering ASC ";
	    	$renote = $db->fetchAll($sql);
	    	
	    	
	    	$arr_merch = array('rsDetail'=>$result,'rsSummary'=>$summary_arr,'rsNote'=>$renote);
	    	$result = array(
	    			'status' =>true,
	    			'value' =>$arr_merch,
	    	);
	    	return $result;
	    	
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$result = array(
    				'status' =>false,
    				'value' =>$e->getMessage(),
    		);
    		return $result;
    	}
    }
    function getAttendenceDetail($search = array()){
    	$db = $this->getAdapter();
    	try{
    		$stuId = $search['stu_id'];
    		$currentLang = $search['currentLang'];
    		$currentMonth = $search['currentMonth'];
    		$groupId = $search['groupId'];
    
    		$sql=" SELECT
    					sade.description,
	    			    DATE_FORMAT(sta.date_attendence, '%d-%m-%Y') AS dateAttendence,
    			    CASE
    			    	WHEN  sade.attendence_status = 1 THEN 'C'
    			    	WHEN  sade.attendence_status = 2 THEN 'Absent'
    			    	WHEN  sade.attendence_status = 3 THEN 'Permission'
    			    	WHEN  sade.attendence_status = 4 THEN 'Late'
    			    	WHEN  sade.attendence_status = 5 THEN 'Early Leave'
    			    	END AS attendenceStatusTitle    			    	
    			    FROM rms_student_attendence_detail AS sade,
    			    	`rms_student_attendence` AS sta
    			    	WHERE sta.`id` = sade.`attendence_id` AND sta.type =1 ";
    		
    		    	$where=" AND sade.`stu_id`=$stuId AND sta.`group_id`=$groupId
    		    		AND MONTH(date_attendence)= $currentMonth ORDER BY sta.`date_attendence` DESC ";
    
    		$row =  $db->fetchAll($sql.$where);
    
    		$result = array(
	    		'status' =>true,
	    		'value' =>$row,
    		);
    		return $result;
    
    }catch(Exception $e){
    Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    $result = array(
		    'status' =>false,
		    'value' =>$e->getMessage(),
	    );
	    return $result;
	    }
    }
    function getDisciplineBydate($search = array()){
    	$db = $this->getAdapter();
    	try{
    		
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			$search['stu_id'] = empty($search['stu_id'])?1:$search['stu_id'];
			$stuId = $search['stu_id'];
    		$LangId = $search['currentLang'];
			
    		$stuInfo = $this->getStudentInformation($search);
    		$groupId = empty($stuInfo['value'][0]['group_id'])?0:$stuInfo['value'][0]['group_id'];
    		$groupId = empty($search['group_id'])?$groupId:$search['group_id'];
    
    			$where_status = " AND att.type =2 AND att.id=sad.attendence_id AND att.group_id=$groupId AND sad.`stu_id`=$stuId AND MONTH(att.date_attendence) = MONTH(sta.date_attendence) GROUP BY MONTH(att.date_attendence) ";
	    		$sql="SELECT
		    		DATE_FORMAT(sta.date_attendence, '%m') AS  dateDiscipline,
		    		DATE_FORMAT(sta.date_attendence, '%M') AS  dateLabel,
		    		sta.group_id,
		    		(SELECT COUNT(sad.id) FROM rms_student_attendence AS att, rms_student_attendence_detail AS sad WHERE sad.attendence_status = 1 $where_status) AS Minor,
		    		(SELECT COUNT(sad.id) FROM rms_student_attendence AS att, rms_student_attendence_detail AS sad WHERE sad.attendence_status = 2 $where_status) AS MODERATE,
		    		(SELECT COUNT(sad.id) FROM rms_student_attendence AS att, rms_student_attendence_detail AS sad WHERE sad.attendence_status = 3 $where_status) AS MAJOR,
		    		(SELECT COUNT(sad.id) FROM rms_student_attendence AS att, rms_student_attendence_detail AS sad WHERE sad.attendence_status = 4 $where_status) AS OTHER
	    
	    		FROM rms_student_attendence_detail AS sade,
	    			`rms_student_attendence` AS sta
	    			WHERE sta.`id` = sade.`attendence_id` AND sta.type=2 ";
	    		$where = "";
	    		$where.=" AND sade.`stu_id`=$stuId AND sta.`group_id`=$groupId
	    		GROUP BY MONTH(sta.date_attendence) ORDER BY sta.`date_attendence` DESC ";
	    
	    		$row =  $db->fetchAll($sql.$where);
	    
	    		$minor = 0;$moderate=0;$major = 0;$other=0;$amt = 0;$className='';$academicYear='';
	    		$result = array();
	    		foreach($row as  $index => $rs){
	    			$result[$index]['dateDiscipline'] = $rs['dateDiscipline'];
	    			$result[$index]['dateLabel'] = $rs['dateLabel'];
	    			
	    			$result[$index]['Minor'] = empty($rs['Minor'])?0:$rs['Minor'];
	    			$result[$index]['MODERATE'] = empty($rs['MODERATE'])?0:$rs['MODERATE'];
	    			$result[$index]['MAJOR'] = empty($rs['MAJOR'])?0:$rs['MAJOR'];
	    			$result[$index]['OTHER'] = empty($rs['OTHER'])?0:$rs['OTHER'];
	    			$result[$index]['group_id'] = $rs['group_id'];
	    			$result[$index]['TOTAL_ATTRECORD'] = $rs['Minor']+$rs['MODERATE']+$rs['MAJOR']+$rs['OTHER'];
	    	   
	    			$minor = $minor+$rs['Minor'];
	    			$moderate=$moderate+$rs['MODERATE'];
	    			$major=$major+$rs['MAJOR'];
	    			$other=$other+$rs['OTHER'];
	    	   
	    		}
	    		$sql=" SELECT 
	    			(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS acarYear,
	    			group_code AS className FROM rms_tuitionfee AS f,rms_group as g WHERE g.id=$groupId AND f.id=g.academic_year LIMIT 1 ";
	    		$rsGroup = $db->fetchRow($sql);
	    		 
	    		if(!empty($rsGroup)){
	    		$className = $rsGroup['className'];
	    		$academicYear = $rsGroup['acarYear'];
	    	}
	    	
	    	$amt = $minor+$moderate+$major+$other;
	    	$summary_arr = array('acarYear'=>$academicYear,'className'=>$className,'Minor'=>$minor,"MODERATE"=>$moderate,"GRADE"=>$major,"OTHER"=>$other,"TOTALAMT"=>$amt);
	    	
	    	$sql_note = "SELECT
					    	atnd.title,atnd.description
					    FROM `mobile_disciplinenote` AS atn,
					    	`mobile_disciplinenote_detail` AS atnd
					    WHERE atn.id=atnd.displicipline_id
					    	AND atnd.lang=$LangId
					    	ORDER BY atn.ordering ASC ";
	    	
	    	$result_note = $db->fetchAll($sql_note);
	    	
	    	$arr_merch = array('rsDetail'=>$result,'rsSummary'=>$summary_arr,'rsNote'=>$result_note);
	    	
	    	$result = array(
		    	'status' =>true,
		    	'value' =>$arr_merch,
	    	);
	    	return $result;
    
    	}catch(Exception $e){
	    	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	$result = array(
	    			'status' =>false,
	    			'value' =>$e->getMessage(),
	    			);
	    			return $result;
	    	}
    }
    function getDisciplineDetail($search = array()){
    	$db = $this->getAdapter();
    	try{
    		$stuId = $search['stu_id'];
    		$currentLang = $search['currentLang'];
    		$currentMonth = $search['currentMonth'];
    		$groupId = $search['groupId'];
    
    		$sql=" SELECT
    		sade.description,
    		DATE_FORMAT(sta.date_attendence, '%d-%m-%Y') AS dateAttendence,
    		CASE
    		WHEN  sade.attendence_status = 1 THEN 'Minor'
    		WHEN  sade.attendence_status = 2 THEN 'Moderate'
    		WHEN  sade.attendence_status = 3 THEN 'Major'
    		WHEN  sade.attendence_status = 4 THEN 'Other'
    		END AS attendenceStatusTitle
    		FROM rms_student_attendence_detail AS sade,
    		`rms_student_attendence` AS sta
    		WHERE sta.`id` = sade.`attendence_id` AND sta.type =2 ";
    
    		$where=" AND sade.`stu_id`=$stuId AND sta.`group_id`=$groupId
    		AND MONTH(date_attendence)= $currentMonth ORDER BY sta.`date_attendence` DESC ";
    
    		$row =  $db->fetchAll($sql.$where);
    
    		$result = array(
    			'status' =>true,
    			'value' =>$row,
    		);
    		return $result;
    
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$result = array(
    				'status' =>false,
    				'value' =>$e->getMessage(),
    		);
    		return $result;
    	}
    }
    function generateToken($row){
    	$db = $this->getAdapter();
    	try{
			
			$token = $row['mobileToken'];
    		$this->_name = "mobile_mobile_token";
			if(!empty($row['mobileToken'])){
				$token = $row['mobileToken'];
				$sql="SELECT id FROM mobile_mobile_token WHERE token='".$token."' LIMIT 1";
				$rsid = $db->fetchOne($sql);
				if(!empty($rsid)){
					$row['id'] = empty($row['id']) ? 0 : $row['id'];
					$row['deviceType'] = empty($row['deviceType']) ? 0 : $row['deviceType'];
					$row['tokenType'] = empty($row['tokenType']) ? 0 : $row['tokenType'];
					$_arr =array(
    					'stu_id' 		=> $row['id'],
    					'device_type' 	=> $row['deviceType'],
    					'tokenType' 	=> $row['tokenType'],
					);
					//$where ='id= '.$rsid;
					$where ="token= '".$token."'";
					$this->update($_arr, $where);
				}else{
					$row['id'] = empty($row['id']) ? 0 : $row['id'];
					$row['deviceType'] = empty($row['deviceType']) ? 0 : $row['deviceType'];
					$row['tokenType'] = empty($row['tokenType']) ? 0 : $row['tokenType'];
					$_arr =array(
    					'stu_id' 		=> $row['id'],
    					'device_type' 	=> $row['deviceType'],
    					'tokenType' 	=> $row['tokenType'],
					);
					
					$_arr['date'] = date("Y-m-d H:i:s");
					$this->_name = "mobile_mobile_token";
					$this->insert($_arr);
				}
			}
    		
    		/*
			$token = $row['mobileToken'];
    		$sql="SELECT id FROM mobile_mobile_token WHERE token='".$token."' AND stu_id=0 LIMIT 1";
    		$rsid = $db->fetchOne($sql);
    		if(!empty($rsid)){
				if($row['tokenType']==2){
					$_arr =array(
						'stu_id' 	=> 0,
					);
					$this->_name = "mobile_mobile_token";
					$where=" stu_id =  ".$row['id'];
					$this->update($_arr,$where);
				}
				
    			$_arr =array(
    					'stu_id' 		=> $row['id'],
    					'device_type' 	=> $row['deviceType'],
    					'tokenType' 	=> $row['tokenType'],
    					'device_model' 	=> "",
    			);
    			$where ='id= '.$rsid;
    			$this->update($_arr, $where);
    		}else{
				
	    		$sql="SELECT id FROM mobile_mobile_token WHERE stu_id=".$row['id']." AND token='".$token."' LIMIT 1";
	    		$rs = $db->fetchOne($sql);
	    		if(empty($rs)){
					if($row['tokenType']==2){
						$_arr =array(
							'stu_id' 	=> 0,
						);
						$this->_name = "mobile_mobile_token";
						$where=" stu_id =  ".$row['id'];
						$this->update($_arr,$where);
					}
				
				
					$_arr =array(
	    				'stu_id' 	=> $row['id'],
	    				'token' 	=> $token,
	    				'device_type' => $row['deviceType'],
						'tokenType' => $row['tokenType'],
	    				'device_model' => "",
	    			);
					
					$currentStudentCheck = 0;
					if($row['currentStudentId']>0){
						$sql="SELECT id FROM mobile_mobile_token WHERE stu_id=".$row['currentStudentId']." AND token='".$token."' LIMIT 1";
						$currentStudentCheck = $db->fetchOne($sql);
					}
					if($currentStudentCheck >0){
						$this->_name = "mobile_mobile_token";
						$where=" id = $currentStudentCheck ";
						$this->update($_arr,$where);
					}else{
						$_arr['date'] = date("Y-m-d H:i:s");
						$this->_name = "mobile_mobile_token";
						$this->insert($_arr);
					}
					
	    		}
    		}
			
			*/
    		
    		return $token;
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		return null;
    	}
    }
    
    public function getAllNews($search){
    	$db = $this->getAdapter();
    	try{
	    		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
	    		$base_url = Zend_Controller_Front::getInstance()->getBaseUrl()."/images/";
		    	
		    	$sql="SELECT
			    	act.*,
			    	(SELECT ad.description FROM `mobile_news_event_detail` AS ad WHERE ad.news_id = act.`id` AND ad.lang=$currentLang LIMIT 1) AS description,
			    	(SELECT ad.title FROM `mobile_news_event_detail` AS ad WHERE ad.news_id = act.`id` AND ad.lang=$currentLang LIMIT 1) AS title,
			    	DATE_FORMAT(act.`publish_date`, '%d-%m-%Y') AS publishDateFormat,
			    	act.image_feature,
			    	(SELECT u.first_name FROM `rms_users` AS u WHERE u.id = act.`user_id` LIMIT 1) AS user_name,
			    	CASE
					   	WHEN  act.`status` = 1 THEN '$base_url'
					  END AS imageUrl
					,CASE
						WHEN  re.`is_read` IS NULL THEN 0
						ELSE  re.`is_read`
						END AS isRead
		    	";
		    	$sql.=" FROM `mobile_news_event` AS act  ";
				$stuId = empty($search['studentId'])?0:$search['studentId'];
				$sql.=" LEFT JOIN `mobile_news_event_read` AS re ON act.`id` = re.newsId  AND re.`stuId` =$stuId ";
				
		    	$sql.=" WHERE act.`status` =1 ";
		    	$sql_order= "  ORDER BY act.publish_date DESC,act.`id` DESC";
		    	
		    	if (!empty($search['limit'])){
		    		$sql_order.= "  LIMIT ".$search['limit'];
		    	}
				
				//New Added
				if(!empty($search['LimitStart'])){
					$sql_order.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
				}else if(!empty($search['limitRecord'])){
					$sql_order.=" LIMIT ".$search['limitRecord'];
				}
				if(!empty($search['unreadRecord'])){
					$sql.=" AND  0 = CASE
						WHEN  re.`is_read` IS NULL THEN 0
						ELSE  re.`is_read`
						END  ";
					return $row = $db->fetchAll($sql.$sql_order);
				}
			
		    $row = $db->fetchAll($sql.$sql_order);
		    
		    $sql.=" AND is_feature=2 ";
		    
		    $row_feature = $db->fetchAll($sql.$sql_order);
		     
		    $merch_result = array('feature_news'=>$row_feature,'normal_news'=>$row);
		   
		    $result = array(
		    		'status' =>true,
		    		'value' =>$merch_result,
		    );
		    return $result;
	    }catch(Exception $e){
	    	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	$result = array(
	    			'status' =>false,
	    			'value' =>$e->getMessage(),
	    	);
	    	return $result;
	    }
    }
    public function getMainScore($search){
    	$db = $this->getAdapter();
    	try{
    		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    		$stu_id = empty($search['stu_id'])?1:$search['stu_id'];
    		$base_url = Zend_Controller_Front::getInstance()->getBaseUrl()."/images/";
    		$mentiontype = 3;
    		$columnMonth="month_en";
    		$colunmname='title_en';
    		if ($currentLang==1){
    			$mentiontype = 2;
    			$columnMonth="month_kh";
    			$colunmname='title';
    		}
    		$column="metion_grade";
    		if ($mentiontype==1){//grade A/B/C
    			$column="metion_grade";
    		}else if ($mentiontype==2){// ល្អប្រសើរ/ល្អណាស់/ល្អ
    			$column="metion_in_khmer";
    		}else if ($mentiontype==3){// Excellent/Very  Good/Good
    			$column="mention_in_english";
    		}//,sd.max_score
    		$sql="
    		SELECT
	    		(SELECT $columnMonth FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS for_month,
	    		s.exam_type,
	    		s.title_score,
	    		s.for_semester AS forSemesterId,
	    		s.for_month AS forMonthId,
	    		s.reportdate,
	    		s.max_score AS maxScore,
	    		g.max_average AS maxAverage,
    			FORMAT(sm.total_avg,2) AS totalAverage,
    			sm.total_score AS totalScore,
	    		CASE
	    			WHEN sm.total_avg >= g.max_average/2 THEN 'PASSED'
	    			ELSE 'FAILED'
	    		END AS restultStatus,
    			FIND_IN_SET( sm.total_score, 
			    	(
				    	SELECT GROUP_CONCAT( dd.total_score
				    	ORDER BY dd.total_score DESC )
				    	FROM rms_score_monthly AS dd ,rms_score AS ss WHERE
				    	ss.`id`=dd.`score_id`
				    	AND ss.group_id= s.`group_id`
				    	AND ss.id=s.`id`
			    	)
		    	) AS rank,
		    	
		    	(SELECT 
				 setd.$column AS mention
					FROM `rms_metionscore_setting_detail` AS setd,
						`rms_metionscore_setting` AS sets
					WHERE sets.id = setd.metion_score_id
						AND sets.academic_year=g.academic_year
						AND sets.degree = `g`.`degree`
						AND FORMAT(sm.total_avg,2) < setd.max_score
						ORDER BY setd.max_score ASC
						LIMIT 1
				) AS mention,
				(SELECT 
				 setd.metion_grade AS mention
					FROM `rms_metionscore_setting_detail` AS setd,
						`rms_metionscore_setting` AS sets
					WHERE sets.id = setd.metion_score_id
						AND sets.academic_year=g.academic_year
						AND sets.degree = `g`.`degree`
						AND FORMAT(sm.total_avg,2) < setd.max_score
						ORDER BY setd.max_score ASC
						LIMIT 1
				) AS metionGrade,
			
	    		s.`id`,
	    		g.`group_code` AS groupCode,
	    		g.id AS group_id,
	    		s.for_academic_year AS forAcademicYearId,
	    		
	    		(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear,
	    		
	    		(SELECT rms_items.$colunmname FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degreeTitle,
	    		(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS gradeTitle,
	    		`g`.`semester` AS `semester`,
	    		(SELECT teacher_name_kh FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherName,
	    		(SELECT rms_items.pass_average FROM `rms_items` WHERE rms_items.id=g.degree AND rms_items.type=1 LIMIT 1) AS averagePass
    		FROM
    		`rms_score` AS s,
    		`rms_score_monthly` AS sm,    			
    		`rms_group` AS g
    		WHERE
	    		s.`id`=sm.`score_id`
	    		AND g.`id` = s.`group_id`
	    		AND s.status = 1
	    		AND sm.`student_id` = $stu_id
	    		GROUP BY
	    		s.id,
	    		sm.`student_id`,
	    		sm.score_id,
	    		s.`reportdate`
    			ORDER BY
    			s.id DESC ";
    
    		$row = $db->fetchAll($sql);
	    		$result = array(
	    		'status' =>true,
	    		'value' =>$row,
    		);
    		return $result;
    	}catch(Exception $e){
	    	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		    	$result = array(
		    	'status' =>false,
		    	'value' =>$e->getMessage(),
    		);
    		return $result;
    	}
    }
    public function getAllNotification($search){
    		$db = $this->getAdapter();
    		try{
    			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    			$base_url = Zend_Controller_Front::getInstance()->getBaseUrl()."/images/";
    			$sql=" SELECT
						nf.id,
						nf.opt_notification,
						nf.group,
						nf.student,
						nf.type,
						DATE_FORMAT(nf.`date`,'%d-%m-%Y %H:%i') AS ShowDate,
					    	nfd.title,nfd.description
					    FROM `mobile_notice` AS nf,
					    	`mobile_notice_detail` AS nfd
					    WHERE nf.id=nfd.notification_id
					    	AND nfd.lang= ".$currentLang;
    			
    			$row = $db->fetchAll($sql);
    			$result = array(
    					'status' =>true,
    			'value' =>$row,
    			);
    			return $result;
    		}catch(Exception $e){
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			$result = array(
    				'status' =>false,
    				'value' =>$e->getMessage(),
    			);
    			return $result;
    		}
    }
    public function getAllContact($search){
    	$db = $this->getAdapter();
    	try{
    		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    		
    		$sql=" SELECT 
						ad.title,ad.description
					FROM `mobile_about` AS a,
						`mobile_about_detail` AS ad
					WHERE a.id=ad.abouts_id
						AND ad.lang= $currentLang AND a.status=1 ";
    		if (!empty($search['isForHome'])){
				$sql.=" AND a.isForHome = 1 ";
			}
    		$rowabout = $db->fetchAll($sql);
    		
    		$sql=" SELECT
    		l.*,
    		ld.title,ld.description
    		FROM `mobile_location` AS l,
    		`mobile_location_detail` AS ld
    		WHERE l.id=ld.location_id
    		AND ld.lang= $currentLang ";
    		
    		$rowcontact = $db->fetchRow($sql);
    		
    		$all_result = array('about'=>$rowabout,'contact'=>$rowcontact);
    		
    		$result = array(
    				'status' =>true,
    				'value' =>$all_result,
    		);
    		return $result;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$result = array(
    				'status' =>false,
    				'value' =>$e->getMessage(),
    		);
    		return $result;
    	}
    }
    public function getSingleContact($search){
    	$db = $this->getAdapter();
    	try{
    		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    
    		$sql=" SELECT
	    			l.*,
		    		ld.title,
		    		ld.description
	    		FROM `mobile_location` AS l,
	    			`mobile_location_detail` AS ld
	    		WHERE l.id=ld.location_id
    				AND ld.lang= $currentLang ";
    		$rowcontact = $db->fetchRow($sql);
    		
    		$resultintro = array();
    		$search['keyName'] = "lbl_introduction";
    		$resultintro['lbl_introduction']= $this->getMobileLabel($search);
    		$search['keyName'] = "lbl_introduction_i";
    		$resultintro['lbl_introduction_i']= $this->getMobileLabel($search);
    		$search['keyName'] = "introduction_image";
    		$resultintro['introduction_image']= $this->getMobileLabel($search);
    		
    		$search['keyName'] = "lbl_videointro";
    		$resultintro['lbl_videointro']= $this->getMobileLabel($search);
    		
    		$search['keyName'] = "lbl_howtouse";
    		$resultintro['lbl_howtouse']= $this->getMobileLabel($search);
    
    		$result = array(
    			'status' =>true,
    			'value' =>array('contact'=>$rowcontact,'introduction'=>$resultintro)
    		);
    		
    		return $result;
    	}catch(Exception $e){
    	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	$result = array(
    			'status' =>false,
    			'value' =>$e->getMessage(),
    			);
    			return $result;
    	}
    	}
    public function getAllCategoryLearning($search){
    	$db = $this->getAdapter();
    	try{
    		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    		$sql=" SELECT 
						cat.id,
						cat.created_date,
						catd.title AS category_title
					FROM `mobile_category_video` AS cat,
						`mobile_category_video_detail` AS catd
					WHERE cat.id=catd.news_id
						AND catd.lang=$currentLang AND cat.parent=0";
    		$rows = $db->fetchAll($sql);
    		$cate_result = array();
    		if(!empty($rows)){
    			foreach($rows as $key=> $row){
    				$cate_result[$key]['id']=$row['id'];
    				$cate_result[$key]['created_date']=$row['created_date'];
    				$cate_result[$key]['category_title']=$row['category_title'];
    				
    				
    				$sql=" SELECT
			    				cat.id,
			    				cat.created_date,
			    				cat.parent,
			    				catd.title AS category_title
			    			FROM `mobile_category_video` AS cat,
			    				`mobile_category_video_detail` AS catd
			    			WHERE cat.id=catd.news_id
			    				AND cat.parent = ".$row['id']." AND catd.lang=".$currentLang;
    				$subrows = $db->fetchAll($sql);
    				
    				$sub_cate = array();
    				if(!empty($subrows)){
		    			foreach($subrows as $index=> $subrow){
		    				$sub_cate[$index]['id']=$subrow['id'];
		    				$sub_cate[$index]['created_date']=$subrow['created_date'];
		    				$sub_cate[$index]['category_title']=$subrow['category_title'];
		    				$sub_cate[$index]['parent']=$subrow['parent'];
		    			}
    				}
    				
    				$cate_result[$key]['sub_cate']=$sub_cate;
    			}
    		}
    
    		$result = array(
    				'status' =>true,
    				'value' =>$cate_result,
    		);
    		return $result;
    	}catch(Exception $e){
	    	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	$result = array(
    			'status' =>false,
    			'value' =>$e->getMessage(),
    		);
    		return $result;
    	}
    }
    public function getAllVideoLearning($search){
    	$db = $this->getAdapter();
    	try{
    		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    		$CateId = empty($search['cateId'])?1:$search['cateId'];
    		
    		$sql="SELECT 
				    vi.id,
				    vi.publish_date,
					vid.title,
					vi.video_link 
				  FROM `mobile_video` AS vi,
					 `mobile_video_detail` AS vid
				  WHERE 
					vi.id=vid.news_id
					AND vid.lang=$currentLang
					AND vi.category=$CateId ";
    		$rows = $db->fetchAll($sql);

    			$result = array(
    				'status' =>true,
    				'value' =>$rows,
    			);
    			return $result;
    		}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			$result = array(
    				'status' =>false,
    				'value' =>$e->getMessage(),
    			);
    			return $result;
    	}
    }
    public function getAllSlider($search){
    	$db = $this->getAdapter();
    	try{
    		
    		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    		$sql=" SELECT * FROM `mobile_slideshow` ";
    		$rows = $db->fetchAll($sql);
    		
    
    		$result = array(
    				'status' =>true,
    				'value' =>$rows,
    				);
    				return $result;
    		}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    				$result = array(
    				'status' =>false,
    				'value' =>$e->getMessage(),
    				);
    				return $result;
    	}
    }
//     public function getMainScore($search){
//     	$db = $this->getAdapter();
//     	try{
//     		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
//     		$stu_id = empty($search['stu_id'])?1:$search['stu_id'];
//     		$base_url = Zend_Controller_Front::getInstance()->getBaseUrl()."/images/";
//     		$sql="SELECT 
// 					(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS for_month, 
// 					s.exam_type, 
// 					s.title_score, 
// 					sm.`student_id`,
// 					s.for_semester AS forSemesterId, 
// 					s.for_month AS forMonthId, 
// 					s.reportdate, 
// 					s.max_score AS maxScore, 
// 					g.max_average AS maxAverage,
// 					CASE
// 						WHEN  s.exam_type = 1 THEN FORMAT(sm.total_avg,2)
// 						WHEN  s.exam_type = 2 THEN 
// 							FORMAT(((SELECT FORMAT(AVG(sm2.total_avg),2)
// 							FROM `rms_score_monthly` AS sm2,
// 							  rms_score s2
// 							WHERE 
// 								s2.id = sm2.score_id 
// 								AND s2.for_semester=s.for_semester
// 								AND s2.`group_id`=s.`group_id`
// 								AND s2.exam_type=1
// 								AND sm2.student_id=sm.`student_id`
// 							LIMIT 1)+sm.total_avg)/2,2)
// 					END AS totalAverage,
// 					CASE
// 						WHEN  s.exam_type = 1 THEN FORMAT(sm.total_score,2)
// 						WHEN  s.exam_type = 2 THEN 
// 							FORMAT(((SELECT FORMAT(AVG(sm2.total_avg),2)
// 							FROM `rms_score_monthly` AS sm2,
// 							  rms_score s2
// 							WHERE 
// 								s2.id = sm2.score_id 
// 								AND s2.for_semester=s.for_semester
// 								AND s2.`group_id`=s.`group_id`
// 								AND s2.exam_type=1
// 								AND sm2.student_id=sm.`student_id`
// 							LIMIT 1)+sm.total_avg),2)
// 					END AS totalScore,
// 					CASE
// 						WHEN  s.exam_type = 1 THEN 
// 							CASE
// 								WHEN sm.total_avg >= g.max_average/2 THEN 'PASS'
// 								ELSE 'FAIL'
// 							END
						
// 						WHEN  s.exam_type = 2 THEN 
// 							CASE
// 								WHEN 
// 								FORMAT(((SELECT FORMAT(AVG(sm2.total_avg),2)
// 							FROM `rms_score_monthly` AS sm2,
// 							  rms_score s2
// 							WHERE 
// 								s2.id = sm2.score_id 
// 								AND s2.for_semester=s.for_semester
// 								AND s2.`group_id`=s.`group_id`
// 								AND s2.exam_type=1
// 								AND sm2.student_id=sm.`student_id`
// 							LIMIT 1)+sm.total_avg)/2,2) >= g.max_average/2 THEN 'PASS'
// 								ELSE 'FAIL'
// 							END
							
// 					END AS restultStatus,  
					  
					
// 					s.`id`, 
// 					g.`branch_id`, 
// 					g.`group_code` AS groupCode, 
// 					s.for_academic_year AS forAcademicYearId, 
// 					`g`.`degree` AS degreeId, 
					
// 					(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation LIMIT 1) AS academicYear, 
// 					(SELECT from_academic FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation LIMIT 1) AS startYear, 
// 					(SELECT to_academic FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation LIMIT 1) AS endYear, 
// 					(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degreeTitle, 
// 					(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS gradeTitle, 
// 					`g`.`semester` AS `semester`, 
// 					(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `roomTitle`, 
// 					(SELECT name_kh FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`, 
// 					(SELECT teacher_name_kh FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherName,	
// 					(SELECT SUM(amount_subject) FROM `rms_group_subject_detail` WHERE rms_group_subject_detail.group_id=g.`id` LIMIT 1) AS amountSubject , 
// 					(SELECT SUM(amount_subject_sem) FROM `rms_group_subject_detail` WHERE rms_group_subject_detail.group_id=g.`id` LIMIT 1) AS amountSubjectsem , 
// 					(SELECT rms_items.pass_average FROM `rms_items` WHERE rms_items.id=g.degree AND rms_items.type=1 LIMIT 1) AS averagePass 
// 				FROM 
// 					`rms_score` AS s, 
// 					`rms_score_monthly` AS sm, 
					
// 					`rms_group` AS g 
// 				WHERE 
// 					s.`id`=sm.`score_id` 
					
// 					AND g.`id` = s.`group_id` 	
// 					AND s.status = 1 
// 					AND sm.`student_id` = $stu_id 
// 				GROUP BY 
// 					s.id,
// 					sm.`student_id`,
// 					sm.score_id,
// 					s.`reportdate` 
// 				ORDER BY 
// 					s.id DESC
//     		";
    		
//     		$row = $db->fetchAll($sql);
//     		$result = array(
//     				'status' =>true,
//     		'value' =>$row,
//     		);
//     		return $result;
//     	}catch(Exception $e){
// 	    	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 	    	$result = array(
// 	    			'status' =>false,
// 	    			'value' =>$e->getMessage(),
//     		);
//     		return $result;
//     		}
//     }

    function getExamByExamIdAndStudent($data){
    	$db = $this->getAdapter();
    	$sql="
    	SELECT
	    	s.`id`,
	    	s.branch_id,
	    	(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS for_month,
	    	(SELECT month_en FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS for_monthen,
	    	s.exam_type,
	    	s.for_month AS for_month_id,
	    	s.for_semester,
	    	s.reportdate,
	    	s.title_score,
	    	s.max_score,
	    	sd.`student_id`,
	    	sm.total_score,
	    	sm.total_avg,
	    	FIND_IN_SET( total_avg, 
	    		(
			    	SELECT GROUP_CONCAT( total_avg ORDER BY total_avg DESC )
			    	FROM rms_score_monthly AS dd ,rms_score AS ss WHERE
			    	ss.`id`=dd.`score_id`
			    	AND ss.group_id= s.`group_id`
			    	AND ss.id=s.`id`
	    		)
	    	) AS rank,
	    	(SELECT count(ss.`id`) FROM rms_score_monthly AS dd ,rms_score AS ss WHERE
			    	ss.`id`=dd.`score_id`
			    	AND ss.group_id= s.`group_id`
			    	AND ss.id=s.`id` LIMIT 1) as amountStudent,
	    	vst.*,
	    	(SELECT rms_group.group_code FROM rms_group WHERE rms_group.id=gds.group_id LIMIT 1) AS group_code,
	    	gds.group_id AS group_id,
	    	(SELECT t.`teacher_name_kh` FROM `rms_teacher` t WHERE t.id =(SELECT rms_group.teacher_id FROM rms_group WHERE rms_group.id=gds.group_id LIMIT 1) LIMIT 1) AS teacher,
	    	(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=gds.academic_year LIMIT 1) as academic_year,
	    	(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=gds.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
	    	(SELECT rms_items.title FROM rms_items WHERE rms_items.id=gds.degree AND rms_items.type=1 LIMIT 1) AS degree,
	    	gds.degree AS degree_id,
	    	gds.academic_year AS for_academic_year,
	    	(SELECT br.school_namekh FROM rms_branch AS br WHERE br.br_id = s.branch_id LIMIT 1) AS school_namekh,
	    	(SELECT br.school_nameen FROM rms_branch AS br WHERE br.br_id = s.branch_id LIMIT 1) AS school_nameen,
	    	(SELECT br.photo FROM rms_branch AS br WHERE br.br_id = s.branch_id LIMIT 1) AS photo_branch
    	FROM
    	`rms_score` AS s,
    	`rms_score_detail` AS sd,
    	`rms_score_monthly` AS sm,
    
    	rms_student AS vst,
    	rms_group_detail_student AS gds
    	WHERE 
		gds.itemType=1 
		AND s.`id`=sd.`score_id`
    	AND vst.stu_id = sm.`student_id`
    	AND vst.stu_id = sd.`student_id`
    	AND vst.stu_id = gds.`stu_id`
    	AND s.group_id = gds.`group_id`
    
    	AND s.`id`=sm.`score_id`
    	AND s.status = 1
    	
    	";
    	if (!empty($data['group_id'])){
    		$sql.=" AND gds.`group_id`=".$data['group_id'];
    	}
    	if (!empty($data['stu_id'])){
    		$sql.=" AND vst.`stu_id`=".$data['stu_id'];
    	}
    	if (!empty($data['exam_type'])){
    		$sql.=" AND s.exam_type=".$data['exam_type'];
    
    		if ($data['exam_type']==1){
    			if (!empty($data['for_month'])){
    				$sql.=" AND s.`for_month`=".$data['for_month'];
    			}
    		}else if ($data['exam_type']==2){
    			if (!empty($data['for_semester'])){
    				$sql.=" AND s.`for_semester`=".$data['for_semester'];
    			}
    		}
    	}
    	$sql.=" ORDER BY s.id DESC LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getAverageMonthlyForSemester($group_id,$semester,$stu_id){
    	$db = $this->getAdapter();
    	$sql="
    	SELECT
	    	v.*,
	    	FIND_IN_SET( total_avg, (
	    	SELECT GROUP_CONCAT( total_avg
	    	ORDER BY total_avg DESC )
	    	FROM v_average_semster_monthly_exam AS dd WHERE
	    		
	    	dd.group_id=v.group_id
	    	AND dd.for_semester = v.for_semester
	    	)
	    	) AS rank
    	FROM `v_average_semster_monthly_exam` AS v
	    	WHERE v.group_id=$group_id
	    	AND v.stu_id=$stu_id
	    	AND v.for_semester =$semester
    	";
    	return $db->fetchRow($sql);
    }
    function getAverageSemesterFull($group_id,$semester,$stu_id){
    	$db = $this->getAdapter();
    	$sql="SELECT v.*,
	    	FIND_IN_SET( average_semester_score, (
	    	SELECT GROUP_CONCAT( average_semester_score
	    	ORDER BY average_semester_score DESC )
	    	FROM v_average_semester_full AS dd WHERE
	    	dd.group_id=v.group_id
	    	AND dd.for_semester =v.for_semester
	    	)
	    	) AS rank
	    	FROM `v_average_semester_full` AS v
	    	WHERE v.for_semester = $semester
	    	AND v.group_id =$group_id
	    	AND v.stu_id = $stu_id
	    	LIMIT 1";
    	return $db->fetchRow($sql);
    }
    
    
    
    public function getAllMobileCouse($search){
    	$db = $this->getAdapter();
    	try{
    		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    		$base_url = Zend_Controller_Front::getInstance()->getBaseUrl()."/images/";
    		 
    		$sql="
	    		SELECT
		    		act.*,
		    		(SELECT ad.description FROM `mobile_course_detail` AS ad WHERE ad.course_id = act.`id` AND ad.lang=$currentLang LIMIT 1) AS description,
		    		(SELECT ad.title FROM `mobile_course_detail` AS ad WHERE ad.course_id = act.`id` AND ad.lang=$currentLang LIMIT 1) AS title,
		    		DATE_FORMAT(act.`publish_date`, '%d-%m-%Y') AS publishDateFormat,
		    		act.image_feature,
		    		act.ordering,
		    		(SELECT u.first_name FROM `rms_users` AS u WHERE u.id = act.`user_id` LIMIT 1) AS user_name,
		    		CASE
			    		WHEN  act.`status` = 1 THEN '$base_url'
		    		END AS imageUrl
    		";
    		$sql.=" FROM `mobile_course` AS act WHERE act.`status` =1 ";
    		$sql_order= "  ORDER BY act.ordering ASC";
    		 
    		if (!empty($search['limit'])){
    		$sql.= "  LIMIT ".$search['limit'];
    		}
    		$row = $db->fetchAll($sql.$sql_order);
    		 
    		$result = array(
    		'status' =>true,
    		'value' =>$row,
    		);
    		return $result;
    	}catch(Exception $e){
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    		$result = array(
		    		'status' =>false,
		    		'value' =>$e->getMessage(),
	    			);
    			return $result;
    		}
    	}
    	
    	public function getCalendar($search){
    		$db = $this->getAdapter();
    		try{
    			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    			$base_url = Zend_Controller_Front::getInstance()->getBaseUrl()."/images/";
    			 
    			$sql=" SELECT c.*
	    			,CASE
					   	WHEN $currentLang = 1 THEN c.title
					   	WHEN $currentLang = 2 THEN c.title_en
					END AS titleHoliday
    			FROM `mobile_calendar` AS c
				WHERE c.`active` =1 ";
    			if (!empty($search['type_holiday'])){
    				$sql.=" AND c.`type_holiday` = ".$search['type_holiday'];
    			}
    			if (!empty($search['formatMonth'])){
    				$sql.=" AND DATE_FORMAT(c.date, '%m') = ".date("m",strtotime($search['formatMonth']));
    			}
    			if (!empty($search['formatMonthDay'])){
    				$sql.=" AND DATE_FORMAT(c.date, '%m-%d') = ".date("m-d",strtotime($search['formatMonthDay']));
    			}
    			if (!empty($search['formatMonthDayYear'])){
    				$sql.=" AND DATE_FORMAT(c.date, '%m-%d-%Y') = ".date("m-d-Y",strtotime($search['formatMonthDayYear']));
    			}
    			if (!empty($search['formatMonthYear'])){
    				$sql.=" AND DATE_FORMAT(c.date, '%m-%Y') = ".date("m-Y",strtotime($search['formatMonthDayYear']));
    			}
    			$sql_order= "  ORDER BY c.id ASC ";
    			$row = $db->fetchAll($sql.$sql_order);
    			
    			$degreeIdlist = $search['degree_id'];
    			$slist = explode(",", $degreeIdlist);
    			$result =array();
    			if (!empty($degreeIdlist)){
    				$array = array();
    				foreach ($slist as $ss) {
    					$array[$ss] = $ss;
    				}
    				if (!empty($row)) foreach ($row as $key => $rs){
    					$exp = explode(",", $rs['dept']);
    					foreach ($exp as $ss){
    						if (in_array($ss, $array)) {
    							$result[$key] = $rs;
    							break;
    						}
    					}
    				}
    			}
    			if (!empty($result)){
    				$row = $result;
    			}
    			$result = array(
    					'status' =>true,
    				'value' =>$row,
    			);
    			return $result;
    		}catch(Exception $e){
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    		$result = array(
	    				'status' =>false,
	    				'value' =>$e->getMessage(),
	    			);
    			return $result;
    		}
    	}
    	
    	public function getCalendarHolidayEveryYear($search){
    		$db = $this->getAdapter();
    		try{
    			
				$typeUser = empty($search['typeUser'])?0:$search['typeUser'];
    			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
		    	$title="title";
		    	
		    	if ($currentLang==2){
		    		$title="title_en";
		    	}
				if($typeUser==1){ //student
					$search["stu_id"] = empty($search['userId'])? 0 :$search['userId'];
					$stuInfo = $this->getStudentInformation($search);
					if(!empty($stuInfo['value'][0])){
					}
					$search["degree"] = empty($stuInfo['value'][0]['degree'])?0:$stuInfo['value'][0]['degree'];
				}
		    	
				$month = date('m',strtotime($search['mothHoliday']));
				$year_month = date('Y-m',strtotime($search['mothHoliday']));
    			 
    			 $sql="SELECT mc.$title AS holiday_name,
							  DATE_FORMAT(mc.date, '%d') AS holiday_day,
							  DATE_FORMAT(mc.date, '%a') AS holiday_string,
							  DATE_FORMAT(mc.date, '%m') AS holiday_month
							  ,mc.calendarType
					   FROM `mobile_calendar` AS mc 
						WHERE 
							mc.`active` =1 
							AND (( mc.`type_holiday` =1  AND DATE_FORMAT(mc.date, '%m')= ".$month.") 
    			 				OR  (mc.`type_holiday` =2  AND DATE_FORMAT(mc.date, '%Y-%m')='".$year_month."'))";
    			if(!empty($search["degree"])){
					$sql.=" AND FIND_IN_SET('" . $search["degree"] . "', mc.dept) ";
				}
				$sql.=" ORDER BY DATE_FORMAT(mc.date, '%d') ASC ";
    			 
    			 		$res = $db->fetchAll($sql);
	    				$result = array(
	    					'status' =>true,
	    					'value' =>$res,
	    				);
    				return $result;
    			}catch(Exception $e){
    				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    				$result = array(
	    				'status' =>false,
	    				'value' =>$e->getMessage(),
    				);
    				return $result;
    			}
    	}		
		function checkTokenDevice($token="0"){
			$db = $this->getAdapter();
			$sql=" SELECT t.* FROM mobile_mobile_token AS t ";
			$sql.=" WHERE t.token ='$token' LIMIT 1 ";
			return $db->fetchRow($sql);
		}
		
		function checkTimeSecondBeforeAddToken($token){
			$db = $this->getAdapter();
			$sql ="
				SELECT 
					t.* 
				FROM mobile_mobile_token AS t 
				WHERE t.token ='$token'
				ORDER BY gtmp.`id` DESC LIMIT 1 
			";
			$row = $db->fetchRow($sql);
			
			if(!empty($row)){
				$secondLimit="10";
				$createDate = $row["date"];
				$newCreateDate = new DateTime($createDate);
				$newCreateDate->add(new DateInterval('PT'.$secondLimit.'S')); 
				$createDateNew = $newCreateDate->format('Y-m-d H:i:s');
				
				$todayDate = new DateTime();
				$timeToday = $todayDate->format('Y-m-d H:i:s');
				
				$timeToday = date_create($timeToday);
				$timeCreateDateNew = date_create($createDateNew);
				
				if($timeToday > $timeCreateDateNew){
					return false;
				}
				return true;
			}
			return false;
		}
	
    	function addAppTokenId($_data){
    		$db = $this->getAdapter();
    		try{
				if(!empty($_data['token'])){
					$_data['device_type'] = empty($_data['device_type']) ? 1 : $_data['device_type'];
					$check = $this->checkTokenDevice($_data['token']);
					$this->_name='mobile_mobile_token';
					if(empty($check)){
						$checkingSecond= $this->checkTimeSecondBeforeAddToken($_data['token']);
						if(empty($checkingSecond)){
							$array = array(
								'token'	=>$_data['token'],
								'device_type'=> $_data['device_type'],
								'date'	=>date('Y-m-d H:i:s'),
							);
							return $this->insert($array);
						}
					}
				}
				
    			
    		}catch(Exception $e){
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			return false;
    		}
    	}
    	
    	
    	
    	public function getScoreResult($id=null){ // សម្រាប់លទ្ធផលប្រចាំខែ មិនលម្អិត
    		$db = $this->getAdapter();
    		$_db = new Application_Model_DbTable_DbGlobal();
    		$lang = $_db->currentlang();
    		if($lang==1){// khmer
    			$label = "name_kh";
    			$grade = "rms_itemsdetail.title";
    			$degree = "rms_items.title";
    			$branch = "b.branch_namekh";
    			$month = "month_kh";
    		}else{ // English
    			$label = "name_en";
    			$grade = "rms_itemsdetail.title_en";
    			$degree = "rms_items.title_en";
    			$branch = "b.branch_nameen";
    			$month = "month_en";
    		}
    		$sql="SELECT
		    		s.`id`,
		    		st.`stu_id`,
		    		g.`branch_id`,
		    		(SELECT $branch FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS branch_name,
		    		(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS branch_logo,
		    		s.`group_id`,
		    		g.`group_code`,
		    		s.for_academic_year,
		    		`g`.`degree` as degree_id,
		    		(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation LIMIT 1) AS academic_year,
		    		(SELECT from_academic FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation LIMIT 1) AS start_year,
		    		(SELECT to_academic FROM rms_tuitionfee AS f WHERE f.id=g.academic_year AND `status`=1 GROUP BY from_academic,to_academic,generation LIMIT 1) AS end_year,
		    		(SELECT $degree FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
		    		(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS grade,
		    		 
		    		`g`.`semester` AS `semester`,
		    		(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `room_name`,
		    		(SELECT $label	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
		    		(select teacher_name_kh from rms_teacher as t where t.id = g.teacher_id LIMIT 1) as teacher,
		    		sm.`student_id`,
		    		st.`stu_code`,
		    		st.stu_khname,
		    		st.stu_enname,
		    		st.`sex`,
		    		st.photo,
		    		(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS for_month,
		    		s.exam_type,
		    		s.for_semester,
		    		s.for_month as for_month_id,
		    		s.reportdate,
		    		s.title_score,
		    		s.max_score,
		    		sm.total_score,
		    		sm.total_avg,
		    		g.max_average/2 as pass_avrage,
		    		(SELECT SUM(amount_subject) FROM `rms_group_subject_detail` WHERE rms_group_subject_detail.group_id=g.`id` LIMIT 1) AS amount_subject ,
		    		(SELECT SUM(amount_subject_sem) FROM `rms_group_subject_detail` WHERE rms_group_subject_detail.group_id=g.`id` LIMIT 1) AS amount_subjectsem ,
		    		(SELECT rms_items.pass_average FROM `rms_items` WHERE rms_items.id=g.degree AND  rms_items.type=1 LIMIT 1) as average_pass
		    	FROM
		    		`rms_score` AS s,
		    		`rms_score_monthly` AS sm,
		    		`rms_student` AS st,
		    		`rms_group` AS g
		    	WHERE
		    		st.`stu_id`=sm.`student_id`
		    		AND g.`id` = s.`group_id`
		    		AND s.`id`=sm.`score_id`
		    		AND s.status = 1
		    		
    			";
    	
	    		if (!empty($id)){
		    		$sql.=" AND s.id = $id ";
		    	}
	    		$where='';
    	
		    	$order = "  GROUP BY s.id,sm.`student_id`,sm.score_id,s.`reportdate`
		    	ORDER BY sm.total_avg DESC ,s.for_academic_year,s.for_semester,s.for_month,s.`group_id`,sm.`student_id` ASC	";
    	
    		return $db->fetchAll($sql.$where.$order);
    	}
    	
    	function getMobileLabel($search=array()){
    		$db = $this->getAdapter();
    		try{
	    		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
	    		$keyName = empty($search['keyName'])?1:$search['keyName'];
	    		$title="keyValue";
	    		if ($currentLang==2){
	    			$title="keyValueEn";
	    		}
	    		$sql="SELECT 
		    			ml.code,
		    			ml.keyName,
		    			ml.$title AS title
	    			FROM `moble_label` AS ml
	    			WHERE ml.status=1 AND ml.access_type=0";
	    		if (!empty($keyName)){
	    			$sql.=" AND ml.`keyName` = '$keyName' ";
	    		}
	    		$sql.=" LIMIT 1";
	    		return  $db->fetchRow($sql);
    		}catch(Exception $e){
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			return false;
    		}
    	}
		public function getGradingSystem($search){
    		$db = $this->getAdapter();
    		try{
    			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
		    	$title="title";
		    	
		    	$sql=" SELECT 
						ad.title,
						ad.description
					FROM `mobile_grading_system` AS a,
						`mobile_grading_system_detail` AS ad
					WHERE a.id=ad.grading_id
						AND ad.lang= $currentLang 
						AND a.status=1 ";
				$sql.=" ORDER BY a.id ASC ";
				$row = $db->fetchAll($sql);
			
    			 
				$res = $db->fetchAll($sql);
				$result = array(
					'status' =>true,
					'value' =>$row,
				);
				return $result;
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
				);
				return $result;
			}
    	}
		public function getDisciplinePolicy($search){
    		$db = $this->getAdapter();
    		try{
    			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
		    	$title="title";
		    	
		    	$sql=" SELECT 
						ad.title,
						ad.description
					FROM `mobile_disciplinenote` AS a,
						`mobile_disciplinenote_detail` AS ad
					WHERE a.id=ad.displicipline_id
						AND ad.lang= $currentLang 
						AND a.status=1 ";
				$sql.=" ORDER BY a.ordering ASC ";
				$row = $db->fetchAll($sql);
			
    			 
				$res = $db->fetchAll($sql);
				$result = array(
					'status' =>true,
					'value' =>$row,
				);
				return $result;
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
				);
				return $result;
			}
    	}
		public function getAttendancePolicy($search){
    		$db = $this->getAdapter();
    		try{
    			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
		    	$title="title";
		    	
		    	$sql=" SELECT 
						ad.title,
						ad.description
					FROM `mobile_attendencenote` AS a,
						`mobile_attendencenote_detail` AS ad
					WHERE a.id=ad.attendance_id
						AND ad.lang= $currentLang 
						AND a.status=1 ";
				$sql.=" ORDER BY a.ordering ASC ";
				$row = $db->fetchAll($sql);
			
    			 
				$res = $db->fetchAll($sql);
				$result = array(
					'status' =>true,
					'value' =>$row,
				);
				return $result;
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
				);
				return $result;
			}
    	}
		public function getSchoolBranchList($search){
    		$db = $this->getAdapter();
    		try{
    			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
		    	$title="title";
		    	
		    	$schoolName='school_nameen';
				$branchName='branch_nameen';
				if ($currentLang==1){
					$schoolName='school_namekh';
					$branchName='branch_namekh';
				}
		    	$sql=" 
					SELECT b.*
							,b.$schoolName AS schoolName
							,b.$branchName AS branchName
					FROM `rms_branch` AS b 
					WHERE b.status=1 ";
				$sql.=" ORDER BY b.br_id ASC ";
				$row = $db->fetchAll($sql);
			
    			 
				$res = $db->fetchAll($sql);
				$result = array(
					'status' =>true,
					'value' =>$row,
				);
				return $result;
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
				);
				return $result;
			}
    	}
		public function getStudentEnvaluation($search){
    		$db = $this->getAdapter();
    		try{
    			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    			$studentId = empty($search['studentId'])?0:$search['studentId'];
		    	
		    	$colunmname='title_en';
				$label = 'name_en';
				$teacherName = "teacher_name_en";
				$branch = "branch_nameen";
				$month = "month_en";
				$subjectTitle='subject_titleen';
				if ($currentLang==1){
					$teacherName='teacher_name_kh';
					$colunmname='title';
					$label = 'name_kh';
					$branch = "branch_namekh";
					$month = "month_kh";
					$subjectTitle='subject_titlekh';
				}
				$sql="
					SELECT 
						grd.*
						,(SELECT br.$branch FROM `rms_branch` AS br WHERE br.br_id=grd.branchId LIMIT 1) As branchName
						,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameKh
						,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameEn
						,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =grd.forType LIMIT 1) as examTypeTitle
						,CASE
							WHEN grd.forType = 2 THEN grd.forSemester
							ELSE (SELECT $month FROM `rms_month` WHERE id=grd.forMonth  LIMIT 1) 
						END AS forMonthTitle
						,g.group_code AS  groupCode
						,(SELECT t.$teacherName FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherName
						,(SELECT t.teacher_name_kh FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherNameKh
						,(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teaccherNameEng
						,(SELECT t.signature FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherSigature
						,(SELECT t.tel FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherTel
						,(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear
						,(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degreeTitle
						,(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
						,(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS sessionTitle
						,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName
					";
			
				$sql.=" FROM rms_studentassessment AS grd 
						JOIN rms_group AS g ON grd.groupId=g.id 
						LEFT JOIN rms_studentassessment_detail AS assDe ON grd.id=assDe.assessmentId 
				WHERE   grd.status=1 ";
				$sql.=" AND assDe.studentId = ".$studentId;
				if(!empty($search['academicYear'])){
					$sql.=" AND g.academic_year =".$search['academicYear'];
				}
				if(!empty($search['examType'])){
					$sql.=" AND grd.forType=".$search['examType'];
					if($search['examType']==1){
						if(!empty($search['month'])){
							$sql.=" AND grd.forMonth=".$search['month'];
						}	
					}else{
						if(!empty($search['forSemester'])){
							$sql.=" AND grd.forSemester=".$search['forSemester'];
						}
					}
				}
				
				$order=" GROUP BY grd.id  ORDER BY grd.id DESC ";
				if(!empty($search['LimitStart'])){
					$order.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
				}else if(!empty($search['limitRecord'])){
					$order.=" LIMIT ".$search['limitRecord'];
				}
    			 
				$row = $db->fetchAll($sql.$order);
				$result = array(
					'status' =>true,
					'value' =>$row,
				);
				return $result;
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
				);
				return $result;
			}
    	}
		
		public function getStudentEnvaluationDetail($search){
    		$db = $this->getAdapter();
    		try{
    			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    			$studentId = empty($search['studentId'])?0:$search['studentId'];
		    	
		    	$colunmname='title_en';
				$label = 'name_en';
				$teacherName = "teacher_name_en";
				$branch = "branch_nameen";
				$month = "month_en";
				$subjectTitle='subject_titleen';
				if ($currentLang==1){
					$teacherName='teacher_name_kh';
					$colunmname='title';
					$label = 'name_kh';
					$branch = "branch_namekh";
					$month = "month_kh";
					$subjectTitle='subject_titlekh';
				}
				$sql="SELECT 
						grd.*
						,(SELECT br.$branch FROM `rms_branch` AS br WHERE br.br_id=grd.branchId LIMIT 1) As branchName
						,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameKh
						,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = grd.branchId LIMIT 1) AS branchNameEn
						,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =grd.forType LIMIT 1) as examTypeTitle
						,CASE
							WHEN grd.forType = 2 THEN grd.forSemester
							ELSE (SELECT $month FROM `rms_month` WHERE id=grd.forMonth  LIMIT 1) 
						END AS forMonthTitle
						,g.group_code AS  groupCode
						,(SELECT t.$teacherName FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherName
						,(SELECT t.teacher_name_kh FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherNameKh
						,(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teaccherNameEng
						,(SELECT t.signature FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherSigature
						,(SELECT t.tel FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherTel
						,(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear
						,(SELECT rms_items.$colunmname FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degreeTitle
						,(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
						,(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS sessionTitle
						,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName
						,(SELECT rt.rating FROM rms_rating AS rt WHERE rt.id=assDe.ratingId LIMIT 1) AS ratingTitle
						,(SELECT c.comment FROM rms_comment AS c WHERE c.id = assDe.commentId LIMIT 1) AS commentTitle
				";
				
				$sql.=" FROM rms_studentassessment AS grd 
							JOIN rms_group AS g ON grd.groupId=g.id 
							LEFT JOIN rms_studentassessment_detail AS assDe ON grd.id=assDe.assessmentId 
					WHERE   grd.status=1 ";
			
				
				$sql.=" AND assDe.studentId = ".$studentId;
				if(!empty($search['evaluationId'])){
					$sql.=" AND grd.id =".$search['evaluationId'];
				}
				$row = $db->fetchAll($sql);
				
				$result = array(
					'status' =>true,
					'value' =>$row,
				);
				return $result;
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
				);
				return $result;
			}
    	}
		
		function getGroupOfStudent($studentId){
			$db = $this->getAdapter();
			$sql="SELECT 
					GROUP_CONCAT(gds.group_id) 
				FROM `rms_group_detail_student` AS gds 
				";
			$sql.="
				WHERE 
					 1
			";
			//gds.is_maingrade=1
					//AND gds.is_current=1
			$sql.=" AND gds.stu_id=".$studentId;
			return $db->fetchOne($sql);
		}
		public function getStudentAttendance($search){
    		$db = $this->getAdapter();
    		try{
    			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    			$studentId = empty($search['studentId'])?0:$search['studentId'];
		    	$type = empty($search['type'])?1:$search['type'];
		    	$label = "name_en";
				$branchName = "branch_nameen";
				if($currentLang==1){// khmer
					
					$branchName = "branch_namekh";
					$label = "name_kh";
					
				}
				$groupList = $this->getGroupOfStudent($studentId);
				$groupList = empty($groupList)?0:$groupList;
				
				$sql="
					SELECT
						sat.`group_id`
						,(SELECT b.".$branchName." FROM rms_branch as b WHERE b.br_id=		sat.branch_id LIMIT 1) AS branchName
						,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = sat.branch_id LIMIT 1) AS branchNameKh
						,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = sat.branch_id LIMIT 1) AS branchNameEn
						,g.group_code AS groupCode
						,satd.`attendence_status`
						,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
						,(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)) AS `roomName`
					
						,COUNT(satd.`attendence_status`) AS total
						,sat.`date_attendence`
						,DATE_FORMAT(sat.`date_attendence`,'%Y%m') AS yearMonth
						,satd.description
						

				";
				if($type==1){//attendance
					
					$groupId = empty($search['groupId']) ? 0 : $search['groupId'];
						
					$sql.="
						,'0' AS countNoPermission
						,'0' AS countPermission
						,'0' AS countLate
						,'0' AS countEalyLeave
									";
					
					/*
					$sql.="
						,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat.`group_id` = sat2.`group_id` AND sat2.`type`=1 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=2 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countNoPermission
						,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat.`group_id` = sat2.`group_id` AND sat2.`type`=1 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=3 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countPermission
						,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat.`group_id` = sat2.`group_id` AND sat2.`type`=1 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=4 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countLate
						,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat.`group_id` = sat2.`group_id` AND sat2.`type`=1 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=5 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countEalyLeave
					";
					*/
				}else{//Mistake
					$sql.="
						,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat2.`type`=2 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=1 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countSmallMistake
						,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat2.`type`=2 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=2 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countMediumMistake
						,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat2.`type`=2 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=3 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countBigMistake
						,(SELECT COUNT(satd2.id) FROM `rms_student_attendence_detail` AS satd2,`rms_student_attendence` AS sat2  WHERE sat2.`type`=2 AND sat2.`id`= satd2.`attendence_id` AND satd2.stu_id=$studentId AND satd2.`attendence_status`=4 AND DATE_FORMAT(sat2.`date_attendence`,'%m%Y') = DATE_FORMAT(sat.`date_attendence`,'%m%Y') ) AS countOtherMistake
					";
				}
				
				$sql.="
					FROM
						`rms_student_attendence` AS sat 
						 JOIN `rms_group` AS g ON g.id=sat.group_id
						LEFT JOIN`rms_student_attendence_detail` AS satd ON sat.`id`= satd.`attendence_id`
				";
				$sql.="
					WHERE
						
						 sat.type=".$type."
						AND sat.`status`=1
				";
		
				
				
				$sql.=" AND sat.group_id IN (".$groupList.")";
				
				if(!empty($search['month'])){
					$sql.=" AND DATE_FORMAT(sat.`date_attendence`,'%m') = ".$search['month'];
				}
				if(!empty($search['forSemester'])){
					$sql.=" AND sat.for_semester = ".$search['forSemester'];
				}
				if(!empty($search['academicYear'])){
					$sql.=" AND g.academic_year = ".$search['academicYear'];
				}
				if(!empty($search['groupId'])){
					$sql.=" AND g.id = ".$search['groupId'];
				}
				$sql.=" GROUP BY sat.`group_id`
					,DATE_FORMAT(sat.`date_attendence`,'%Y%m') ";
				$sql.=" ORDER BY DATE_FORMAT(sat.`date_attendence`,'%Y%m') DESC ";
				
				
				if(!empty($search['LimitStart'])){
					$sql.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
				}else if(!empty($search['limitRecord'])){
					$sql.=" LIMIT ".$search['limitRecord'];
				}
				$row = $db->fetchAll($sql);
				if($type==1){
					if(!empty($row)){
						$arrFilter =array(
							"studentId" => $studentId
						);
						foreach($row as $key => $rs){
							
							$arrFilter["yearMonth"] = $rs["yearMonth"];
							$arrFilter["group_id"] = $rs["group_id"];
							$arrFilter["attendence_status"] = 2;
							$countNoPermission = $this->getCountAttendance($arrFilter);
							$row[$key]["countNoPermission"] = $countNoPermission;
							
							$arrFilter["attendence_status"] = 3;
							$countPermission = $this->getCountAttendance($arrFilter);
							$row[$key]["countPermission"] = $countPermission;
							
							$arrFilter["attendence_status"] = 4;
							$countLate = $this->getCountAttendance($arrFilter);
							$row[$key]["countLate"] = $countLate;
							
							$arrFilter["attendence_status"] = 5;
							$countEalyLeave = $this->getCountAttendance($arrFilter);
							$row[$key]["countEalyLeave"] = $countEalyLeave;
							
						}
					}
				}
				
				$result = array(
					'status' =>true,
					'value' =>$row,
				);
				return $result;
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
				);
				return $result;
			}
    	}
		
		function getCountAttendance($_data){
			$db = $this->getAdapter();
			
			$yearMonth = empty($_data["yearMonth"]) ? "202310" : $_data["yearMonth"];
			$studentId = empty($_data["studentId"]) ? "0" : $_data["studentId"];
			$groupId = empty($_data["group_id"]) ? "0" : $_data["group_id"];
			$attendence_status = empty($_data["attendence_status"]) ? "0" : $_data["attendence_status"];
			$sql="
				SELECT 
					satd.attendence_id,
					satd.attendence_status
				FROM 
					`rms_student_attendence` AS sat ,
					`rms_student_attendence_detail` AS satd
						
				WHERE satd.attendence_status = $attendence_status
					AND sat.`id`= satd.`attendence_id`
					AND satd.stu_id = $studentId
					AND sat.group_id = $groupId
					
					
				";
			
			if(!empty($_data["forSemester"])){
				$sql.=" AND sat.`for_semester` = ".$_data["forSemester"];
			}else{
				$sql.=" AND DATE_FORMAT(sat.`date_attendence`,'%Y%m') = '$yearMonth'";
			}
			$sql.="
					GROUP BY satd.attendence_id,satd.attendence_status
					ORDER BY satd.attendence_status DESC
			";
			$rs = $db->fetchAll($sql);
			$restult = "0";
			if(!empty($rs)){
				$restult = "".COUNT($rs)."";
			}
			return $restult;
		}
		
		public function getStudentAttendanceDetail($search){
    		$db = $this->getAdapter();
    		try{
    			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    			$studentId = empty($search['studentId'])?0:$search['studentId'];
    			$type = empty($search['type'])?1:$search['type'];
		    	
		    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
				$label = "name_en";
				$branchName = "branch_nameen";
				$teacherName= "teacher_name_en";
				if($currentLang==1){// khmer
					$teacherName= "teacher_name_kh";
					$branchName = "branch_namekh";
					$label = "name_kh";
					
				}
				$sql="
					SELECT
					sat.`group_id`
					,g.group_code AS groupCode
					,satd.`attendence_status`
					,satd.`type`
					,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
					
					
				";
				if($type==1){//attendance
					$sql.="
						,CASE
							WHEN satd.`attendence_status` = 2 THEN '".$tr->translate("NO_PERMISSION")."'
							WHEN satd.`attendence_status` = 3 THEN '".$tr->translate("PERMISSION")."'
							WHEN satd.`attendence_status` = 4 THEN '".$tr->translate("LATE")."'
							WHEN satd.`attendence_status` = 5 THEN '".$tr->translate("EARLY_LEAVE")."'
						
						END AS attendenceStatusTitle
					";
				}else{//Mistake
					$sql.="
						,CASE
							WHEN satd.`attendence_status` = 1 THEN '".$tr->translate("SMALL_MISTACK")."'
							WHEN satd.`attendence_status` = 2 THEN '".$tr->translate("MEDIUM_MISTACK")."'
							WHEN satd.`attendence_status` = 3 THEN '".$tr->translate("BIG_MISTACK")."'
							WHEN satd.`attendence_status` = 4 THEN '".$tr->translate("OTHER")."'
						
						END AS attendenceStatusTitle
					";
				}
				$sql.="
					,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
					,(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)) AS `roomName`
					,sat.`date_attendence`
					,DATE_FORMAT(sat.`date_attendence`,'%Y%m%d') AS yearMonth
					,satd.description
				";
				$sql.="
					FROM
					`rms_student_attendence` AS sat 
					 JOIN `rms_group` AS g ON g.id=sat.group_id
					LEFT JOIN`rms_student_attendence_detail` AS satd ON sat.`id`= satd.`attendence_id`
						
				";
				$sql.="
					WHERE
						
						 sat.type=".$type."
						AND sat.`status`=1 
				";
				$yearMonth = empty($search['id'])?'':$search['id'];
				$groupID = empty($search['group'])?0:$search['group'];
				
				$sql.=" AND sat.group_id IN (".$groupID.")";
				$sql.=" AND satd.stu_id = $studentId ";
				
				if(!empty($yearMonth)){
					$sql.=" AND DATE_FORMAT(sat.`date_attendence`,'%Y%m') = ".$yearMonth;
				}
				
				$sql.=" GROUP BY sat.`group_id`,sat.`date_attendence`";
				$sql.=" ORDER BY sat.`date_attendence` DESC ";
				if($type==1){
					$sql.=",satd.`attendence_status` DESC";
				}
    			 
				$row = $db->fetchAll($sql);
				$result = array(
					'status' =>true,
					'value' =>$row,
				);
				return $result;
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
				);
				return $result;
			}
    	}
		
		public function getStudentSchedule($search){
    		$db = $this->getAdapter();
    		try{
    			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    			$studentId = empty($search['studentId'])?0:$search['studentId'];
		    	
		    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
				$label = "name_en";
				$grade = "rms_itemsdetail.title_en";
				$degree = "rms_items.title_en";
				$branchName = "branch_nameen";
				$subjectTitle = "subject_titleen";
				$teacherName = "teacher_name_en";
				$timeTitle = "title_en";
				if($currentLang==1){// khmer
					$timeTitle = "title";
					$teacherName = "teacher_name_kh";
					$subjectTitle = "subject_titlekh";
					$branchName = "branch_namekh";
					$label = "name_kh";
					$grade = "rms_itemsdetail.title";
					$degree = "rms_items.title";
				}
				$day = empty($search['day'])?0:$search['day'];
				$sql="
					SELECT 
					
						sj.subject_titlekh AS subjectTitleKh
						,sj.subject_titleen AS subjectTitleEng
						,sj.subject_lang AS subject_lang
						
						,CASE 
							WHEN sj.subject_lang =1 THEN sj.subject_titlekh
							ELSE sj.subject_titleen
						END AS subjectTitle
						,(SELECT te.$teacherName FROM rms_teacher AS te WHERE te.id = schDetail.techer_id LIMIT 1 ) AS teaccherName
						,(SELECT te.teacher_name_kh FROM rms_teacher AS te WHERE te.id = schDetail.techer_id LIMIT 1 ) AS teaccherNameKh
						,(SELECT te.teacher_name_en FROM rms_teacher AS te WHERE te.id = schDetail.techer_id LIMIT 1 ) AS teaccherNameEng
						,(SELECT t.tel FROM rms_teacher AS t WHERE t.id = schDetail.techer_id LIMIT 1) AS teacherTel
						,(SELECT $label FROM rms_view WHERE rms_view.key_code=schDetail.day_id AND rms_view.type=18 LIMIT 1)AS dayTitle
						,(SELECT name_kh FROM rms_view WHERE rms_view.key_code=schDetail.day_id AND rms_view.type=18 LIMIT 1)AS daysKh
						,(SELECT t.$timeTitle FROM rms_timeseting AS t WHERE t.value =schDetail.from_hour LIMIT 1) AS fromHourTitle
						,(SELECT t.$timeTitle FROM rms_timeseting AS t WHERE t.value =schDetail.to_hour LIMIT 1) AS toHourTitle
						
						,(SELECT b.".$branchName." FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchName
						,(SELECT br.branch_namekh FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameKh
						,(SELECT br.branch_nameen FROM `rms_branch` AS br  WHERE br.br_id = g.branch_id LIMIT 1) AS branchNameEn
						,(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS schoolNameen
						,(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.branch_id LIMIT 1) AS branchLogo
						
						,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYear
						,`g`.`group_code` AS groupCode
						,`g`.`degree` AS degree_id
						,`g`.`grade` AS gradeId
						,(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)) AS `roomName`
						,(SELECT $degree FROM `rms_items`	WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1)  LIMIT 1) as degreeTitle
						,(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as gradeTitle
								
						,schDetail.*
					FROM 
						rms_group_reschedule AS schDetail
						JOIN rms_group_schedule AS sch ON sch.id =schDetail.main_schedule_id
						JOIN  rms_group AS g ON g.id =sch.group_id
							LEFT JOIN rms_group_detail_student AS grd ON grd.group_id =sch.group_id
							LEFT JOIN `rms_subject` AS sj ON sj.id = schDetail.subject_id
					WHERE 
						g.is_use =1
						AND g.is_pass =2
						AND grd.is_current =1
						AND grd.is_maingrade =1
					";
					$sql.=" AND grd.stu_id=".$studentId;
					if(!empty($day)){
						$sql.=" AND schDetail.day_id=".$day;
					}
					if(!empty($search['degree'])){
						$sql.=" AND g.degree=".$search['degree'];
					}
					$sql.=" ORDER BY schDetail.day_id ASC ,schDetail.from_hour ASC ";
				$row = $db->fetchAll($sql);
				$result = array(
					'status' =>true,
					'value' =>$row,
				);
				return $result;
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
				);
				return $result;
			}
    	}
		
	public function getStudentScore($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$studentId = empty($search['studentId'])?0:$search['studentId'];
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$label = "name_en";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
			$branch = "b.branch_nameen";
			$month = "month_en";
			$teacherName= "teacher_name_en";
			$mentionGradeTitle= "mention_in_english";
			$titleScore= "title_score_en";
			
			if($currentLang==1){// khmer
				$teacherName = "teacher_name_kh";
				$label = "name_kh";
				$grade = "rms_itemsdetail.title";
				$degree = "rms_items.title";
				$branch = "b.branch_namekh";
				$month = "month_kh";
				$mentionGradeTitle= "metion_in_khmer";
				$titleScore= "title_score";
			}
			$sql="SELECT
					s.*
					,s.$titleScore AS title_score
					,st.`stu_id`
					,g.`branch_id`
					,(SELECT $branch FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS branchName
					,(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS branchLogo
					,(SELECT b.school_namekh FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS schoolNameKh
					,(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS schoolNameEng
				
					,g.`group_code` AS groupCode
					,`g`.`degree` as degreeId
				
					,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYearTitle
					
					,(SELECT $degree FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degreeTitle
					,(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS gradeTitle
			   
					,`g`.`semester` AS `semester`
					,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName
					,(SELECT $label	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`
					,(SELECT t.$teacherName FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherName
					,(SELECT t.teacher_name_kh FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherNameKh
					,(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teaccherNameEng
					,(SELECT t.signature FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherSigature
					,(SELECT t.tel FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherTel
					,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =s.exam_type LIMIT 1) as forTypeTitle
					,CASE
						WHEN s.exam_type = 2 THEN s.for_semester
						ELSE (SELECT $month FROM `rms_month` WHERE id=s.for_month  LIMIT 1) 
					END AS forMonthTitle
					
					,CASE
						WHEN s.exam_type = 2 THEN s.for_semester
					ELSE (SELECT $month FROM `rms_month` WHERE id=s.for_month  LIMIT 1) 
					END AS recordTitle
					,s.date_input AS recordDate
					,'studyScore' AS recordType
					,sm.isRead AS recordIsread
					
					,sm.totalMaxScore AS totalMaxScore
					,sm.total_score AS totalScore
					,sm.total_avg AS totalAvg
					,g.max_average/2 AS passAvrage
					,g.semesterTotalAverage AS semesterMaxAverage
					,g.semesterPercentage AS semesterScal
					
					,sm.totalKhAvg AS totalKhAvg
					,sm.totalEnAvg AS totalEnAvg
					,sm.totalChAvg AS totalChAvg
					
					,sm.OveralAvgKh AS overallAvgKh
					,sm.OveralAvgEng AS overallAvgEng
					,sm.OveralAvgCh AS overallAvgCh
					
					,sm.monthlySemesterAvg AS monthlySemesterAvg
					,sm.overallAssessmentSemester AS overallAssessmentSemester
					
					,(SELECT SUM(amount_subject) FROM `rms_group_subject_detail` WHERE rms_group_subject_detail.group_id=g.`id` LIMIT 1) AS amountSubject
					,(SELECT SUM(amount_subject_sem) FROM `rms_group_subject_detail` WHERE rms_group_subject_detail.group_id=g.`id` LIMIT 1) AS amountSubjectsem
					,(SELECT rms_items.pass_average FROM `rms_items` WHERE rms_items.id=g.degree AND  rms_items.type=1 LIMIT 1) as averagePass
					,CASE 
						WHEN s.exam_type = 2 THEN  
							FIND_IN_SET( 
								sm.overallAssessmentSemester, 
								(
									SELECT GROUP_CONCAT( smSecond.overallAssessmentSemester ORDER BY overallAssessmentSemester DESC )
									FROM rms_score_monthly AS smSecond ,rms_score AS sSecond WHERE
									sSecond.`id`=smSecond.`score_id`
									AND sSecond.group_id= s.`group_id`
									AND sSecond.id=s.`id`
								)
							)
						WHEN s.exam_type = 3 THEN  
							FIND_IN_SET( 
								sm.overallAssessmentSemester, 
								(
									SELECT GROUP_CONCAT( smSecond.overallAssessmentSemester ORDER BY overallAssessmentSemester DESC )
									FROM rms_score_monthly AS smSecond ,rms_score AS sSecond WHERE
									sSecond.`id`=smSecond.`score_id`
									AND sSecond.group_id= s.`group_id`
									AND sSecond.id=s.`id`
								)
							)
						ELSE FIND_IN_SET( 
								sm.total_avg, 
								(
									SELECT GROUP_CONCAT( smSecond.total_avg ORDER BY total_avg DESC )
									FROM rms_score_monthly AS smSecond ,rms_score AS sSecond WHERE
									sSecond.`id`=smSecond.`score_id`
									AND sSecond.group_id= s.`group_id`
									AND sSecond.id=s.`id`
								)
							)
					END AS rank
					,FIND_IN_SET( 
						sm.total_avg, 
						(
							SELECT GROUP_CONCAT( smSecond.total_avg ORDER BY total_avg DESC )
							FROM rms_score_monthly AS smSecond ,rms_score AS sSecond WHERE
							sSecond.`id`=smSecond.`score_id`
							AND sSecond.group_id= s.`group_id`
							AND sSecond.id=s.`id`
						)
					) AS examRanking
					,(SELECT COUNT(gds.gd_id)  FROM `rms_group_detail_student` AS gds WHERE gds.group_id = g.id AND gds.stop_type NOT IN (1,2) AND gds.is_maingrade=1 LIMIT 1) AS amountStudent
					
					,CASE 
						WHEN s.exam_type !=1 THEN  
							(SELECT 
								mstd.$mentionGradeTitle
								FROM `rms_metionscore_setting_detail` AS mstd,
									`rms_metionscore_setting` AS mst 
								WHERE mst.id = mstd.metion_score_id
									AND mst.academic_year=g.academic_year
									AND mst.degree = g.degree 
									AND mst.status = 1 
									AND ((sm.overallAssessmentSemester/COALESCE(g.semesterTotalAverage,10))*100) >= mstd.max_score
								ORDER BY mstd.max_score DESC LIMIT 1
							)
						ELSE (SELECT 
								mstd.$mentionGradeTitle
								FROM `rms_metionscore_setting_detail` AS mstd,
									`rms_metionscore_setting` AS mst 
								WHERE mst.id = mstd.metion_score_id
									AND mst.academic_year=g.academic_year
									AND mst.degree = g.degree 
									AND mst.status = 1 
									AND ((sm.total_score/sm.totalMaxScore)*100) >= mstd.max_score
								ORDER BY mstd.max_score DESC LIMIT 1
							)
					END AS mentionGradeTitle
					,CASE 
						WHEN s.exam_type !=1 THEN  
							(SELECT 
								mstd.metion_grade
								FROM `rms_metionscore_setting_detail` AS mstd,
									`rms_metionscore_setting` AS mst 
								WHERE mst.id = mstd.metion_score_id
									AND mst.academic_year=g.academic_year
									AND mst.degree = g.degree 
									AND mst.status = 1 
									AND ((sm.overallAssessmentSemester/COALESCE(g.semesterTotalAverage,10))*100) >= mstd.max_score
								ORDER BY mstd.max_score DESC LIMIT 1
							)
						ELSE (SELECT 
								mstd.metion_grade
								FROM `rms_metionscore_setting_detail` AS mstd,
									`rms_metionscore_setting` AS mst 
								WHERE mst.id = mstd.metion_score_id
									AND mst.academic_year=g.academic_year
									AND mst.degree = g.degree 
									AND mst.status = 1 
									AND ((sm.total_score/sm.totalMaxScore)*100) >= mstd.max_score
								ORDER BY mstd.max_score DESC LIMIT 1
							)
					END AS mentionGradeTitleLabel
					
			FROM
				`rms_score` AS s,
				`rms_score_monthly` AS sm,
				`rms_student` AS st,
				`rms_group` AS g
			WHERE
				st.`stu_id`=sm.`student_id`
				AND g.`id` = s.`group_id`
				AND s.`id`=sm.`score_id`
				AND s.status = 1 
				AND s.isPublic = 1 
			";
			
			$sql.=" AND st.stu_id=".$studentId;
			
			$where='';
			if(!empty($search['searchBox'])){
				$s_where=array();
				$s_search=addslashes(trim($search['searchBox']));
				$s_search = str_replace(' ', '', addslashes(trim($search['searchBox'])));
				$s_where[]= " REPLACE(g.group_code,' ','') LIKE '%{$s_search}%'";
				$s_where[]= " REPLACE(s.title_score,' ','') LIKE '%{$s_search}%'";
				$s_where[]= " REPLACE(s.max_score,' ','') LIKE '%{$s_search}%'";
				$s_where[]= " REPLACE(s.note,' ','') LIKE '%{$s_search}%'";
				
				$where.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(!empty($search['academicYear'])){
				$where.=" AND g.academic_year=".$search['academicYear'];
			}
			if(!empty($search['examType'])){
				$where.=" AND s.exam_type=".$search['examType'];
				if($search['examType']==1){
					if(!empty($search['month'])){
						$where.=" AND s.for_month=".$search['month'];
					}	
				}
			}
			if(!empty($search['scoreId'])){
				$where.=" AND s.`id`=".$search['scoreId'];
			}
			
			$ordering=" ORDER BY s.date_input DESC,s.id DESC";
			$limit=" ";
			if(!empty($search['LimitStart'])){
				$limit.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
			}else if(!empty($search['limitRecord'])){
				$limit.=" LIMIT ".$search['limitRecord'];
			}
			 
			if(!empty($search['unreadRecord'])){
				if($search['unreadRecord']==1){ //unread
					$sql.=" AND sm.`isRead` =0 ";
				}else if($search['unreadRecord']==2){ //read
					$sql.=" AND sm.`isRead` =1 ";
				}else if($search['unreadRecord']==3){ //all
					$ordering=" ORDER BY sm.`isRead` ASC,s.date_input DESC";
				}
				return  $db->fetchAll($sql.$where.$ordering.$limit);
			}
			
			$row = $db->fetchAll($sql.$where.$ordering.$limit);
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	public function getScoreInformation($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$studentId = empty($search['studentId'])?0:$search['studentId'];
			$scoreId = empty($search['scoreId'])?0:$search['scoreId'];
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$label = "name_en";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
			$branch = "b.branch_nameen";
			$month = "month_en";
			$teacherName= "teacher_name_en";
			$mentionGradeTitle= "mention_in_english";
			$titleScore= "title_score_en";
			if($currentLang==1){// khmer
				$teacherName = "teacher_name_kh";
				$label = "name_kh";
				$grade = "rms_itemsdetail.title";
				$degree = "rms_items.title";
				$branch = "b.branch_namekh";
				$month = "month_kh";
				$mentionGradeTitle= "metion_in_khmer";
				$titleScore= "title_score";
			}
			
			$strSubLang=" (SELECT subject_lang FROM `rms_subject` sub WHERE sub.id=sd.subject_id LIMIT 1) ";
			$sql="SELECT
				s.*
				,s.$titleScore AS title_score
				,g.`branch_id`
				,(SELECT $branch FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS branchName
				,(SELECT b.photo FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS branchLogo
				,(SELECT b.school_namekh FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS schoolNameKh
				,(SELECT b.school_nameen FROM rms_branch as b WHERE b.br_id=g.`branch_id` LIMIT 1) AS schoolNameEng
		   	
				,g.`group_code` AS groupCode
				,`g`.`degree` as degreeId
			
				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) AS academicYearTitle
				
				,(SELECT $degree FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degreeTitle
				,(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS gradeTitle
		   
				,`g`.`semester` AS `semester`
				,(SELECT $label	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`
				,(SELECT t.$teacherName FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherName
				,(SELECT t.teacher_name_kh FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherNameKh
				,(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teaccherNameEng
				,(SELECT t.signature FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherSigature
				,(SELECT t.tel FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherTel
				,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =s.exam_type LIMIT 1) as forTypeTitle
				,CASE
					WHEN s.exam_type = 2 THEN s.for_semester
				ELSE (SELECT $month FROM `rms_month` WHERE id=s.for_month  LIMIT 1) 
				END AS forMonthTitle
				
				,sm.totalMaxScore
				,sm.total_score AS totalScore
				,sm.total_avg AS totalAvg
				,g.max_average/2 AS passAvrage
				
				,g.max_average AS monthlyMaxAverage
				,g.semesterTotalAverage AS semesterMaxAverage
				,g.semesterPercentage AS semesterScal
				
				,sm.totalKhAvg AS totalKhAvg
				,sm.totalEnAvg AS totalEnAvg
				,sm.totalChAvg AS totalChAvg
				
				,sm.OveralAvgKh AS overallAvgKh
				,sm.OveralAvgEng AS overallAvgEng
				,sm.OveralAvgCh AS overallAvgCh
				
				,sm.monthlySemesterAvg AS monthlySemesterAvg
				,sm.overallAssessmentSemester AS overallAssessmentSemester
					
				,(SELECT SUM(amount_subject) FROM `rms_group_subject_detail` WHERE rms_group_subject_detail.group_id=g.`id` LIMIT 1) AS amountSubject
				,(SELECT SUM(amount_subject_sem) FROM `rms_group_subject_detail` WHERE rms_group_subject_detail.group_id=g.`id` LIMIT 1) AS amountSubjectsem
				,(SELECT rms_items.pass_average FROM `rms_items` WHERE rms_items.id=g.degree AND  rms_items.type=1 LIMIT 1) as averagePass
				
				,CASE 
					WHEN s.exam_type != 1 THEN  
						FIND_IN_SET( 
							sm.overallAssessmentSemester, 
							(
								SELECT GROUP_CONCAT( smSecond.overallAssessmentSemester ORDER BY overallAssessmentSemester DESC )
								FROM rms_score_monthly AS smSecond ,rms_score AS sSecond WHERE
								sSecond.`id`=smSecond.`score_id`
								AND sSecond.group_id= s.`group_id`
								AND sSecond.id=s.`id`
							)
						)
					ELSE FIND_IN_SET( 
							sm.total_avg, 
							(
								SELECT GROUP_CONCAT( smSecond.total_avg ORDER BY total_avg DESC )
								FROM rms_score_monthly AS smSecond ,rms_score AS sSecond WHERE
								sSecond.`id`=smSecond.`score_id`
								AND sSecond.group_id= s.`group_id`
								AND sSecond.id=s.`id`
							)
						)
				END AS rank
					
				,FIND_IN_SET( 
					sm.total_avg, 
					(
						SELECT GROUP_CONCAT( smSecond.total_avg ORDER BY total_avg DESC )
						FROM rms_score_monthly AS smSecond ,rms_score AS sSecond WHERE
						sSecond.`id`=smSecond.`score_id`
						AND sSecond.group_id= s.`group_id`
						AND sSecond.id=s.`id`
					)
				) AS examRanking
				
				,COALESCE(FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =1
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =1
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)
				),'0') AS rankingInKhmer
				,COALESCE(FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =2
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =2
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)
				),'0') AS rankingInEnglish
				,COALESCE(FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =3
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =3
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)
				),'0') AS rankingInChinese
					
				,(SELECT COUNT(gds.gd_id)  FROM `rms_group_detail_student` AS gds WHERE gds.group_id = g.id AND gds.is_maingrade=1 AND gds.stop_type NOT IN (1,2) ) AS amountStudent
				,(SELECT 
						mstd.$mentionGradeTitle
						FROM `rms_metionscore_setting_detail` AS mstd,
							`rms_metionscore_setting` AS mst 
						WHERE mst.id = mstd.metion_score_id
							AND mst.academic_year=g.academic_year
							AND mst.degree = g.degree 
							AND mst.status = 1 
							AND ((sm.total_score/sm.totalMaxScore)*100) >= mstd.max_score
						ORDER BY mstd.max_score DESC LIMIT 1
					) AS mentionGradeTitle
					
					,(SELECT 
						mstd.metion_grade
						FROM `rms_metionscore_setting_detail` AS mstd,
							`rms_metionscore_setting` AS mst 
						WHERE mst.id = mstd.metion_score_id
							AND mst.academic_year=g.academic_year
							AND mst.degree = g.degree 
							AND mst.status = 1 
							AND ((sm.total_score/sm.totalMaxScore)*100) >= mstd.max_score
						ORDER BY mstd.max_score DESC LIMIT 1
					) AS mentionGradeTitleLabel
				,FIND_IN_SET( 
					sm.OveralAvgKh, 
					(
						SELECT GROUP_CONCAT( smSecond.OveralAvgKh ORDER BY OveralAvgKh DESC )
						FROM rms_score_monthly AS smSecond ,rms_score AS sSecond WHERE
						sSecond.`id`=smSecond.`score_id`
						AND sSecond.group_id= s.`group_id`
						AND sSecond.id=s.`id`
					)
				) AS overallSemesterKhRank
				,FIND_IN_SET( 
					sm.OveralAvgEng, 
					(
						SELECT GROUP_CONCAT( smSecond.OveralAvgEng ORDER BY OveralAvgEng DESC )
						FROM rms_score_monthly AS smSecond ,rms_score AS sSecond WHERE
						sSecond.`id`=smSecond.`score_id`
						AND sSecond.group_id= s.`group_id`
						AND sSecond.id=s.`id`
					)
				) AS overallSemesterEngRank
				,FIND_IN_SET( 
					sm.OveralAvgCh, 
					(
						SELECT GROUP_CONCAT( smSecond.OveralAvgCh ORDER BY OveralAvgCh DESC )
						FROM rms_score_monthly AS smSecond ,rms_score AS sSecond WHERE
						sSecond.`id`=smSecond.`score_id`
						AND sSecond.group_id= s.`group_id`
						AND sSecond.id=s.`id`
					)
				) AS overallSemesterChRank
				,(SELECT assd.`teacherComment` FROM `rms_studentassessment` AS ass JOIN `rms_studentassessment_detail` AS assD ON ass.id = assd.`assessmentId` WHERE s.id = ass.scoreId AND assd.`studentId`=sm.`student_id` ORDER BY assd.`teacherComment` DESC LIMIT 1) AS teacherComment
			FROM
				rms_score AS s JOIN `rms_score_monthly` AS sm ON s.`id`=sm.`score_id`
				LEFT JOIN `rms_group` AS g ON  g.`id` = s.`group_id`
			WHERE  s.status = 1 ";
				
			$scoreId = empty($search['id'])?0:$search['id'];
			$sql.=" AND sm.student_id = ".$studentId;
			$sql.=" AND s.id = ".$scoreId;
			 
			$row = $db->fetchRow($sql);
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	public function getStudentPaymentHistory($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$studentId = empty($search['studentId'])?0:$search['studentId'];
			
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			
			$label = "name_en";
    		$branch = "branch_nameen";
    		$grade = "rms_itemsdetail.title_en";
    		$degree = "rms_items.title_en";
			if($currentLang==1){
				$label = "name_kh";
				$branch = "branch_namekh";
				$grade = "rms_itemsdetail.title";
				$degree = "rms_items.title";
			}
			
			$sql=" SELECT 
    				sp.*
    				,(SELECT $branch FROM `rms_branch` WHERE br_id=sp.branch_id LIMIT 1) AS branchName
    				,sp.receipt_number AS receiptNo
					,sp.create_date AS paymentDate
					,COALESCE(DATE_FORMAT(sp.create_date, '%Y%m'),'') AS paymentDateYearMonth
					,sp.receipt_number AS recordTitle
					,sp.create_date AS recordDate
					,'payment' AS recordType
					,sp.isRead AS recordIsread
					
	    			,(CASE WHEN sp.data_from=3 THEN s.serial ELSE s.stu_code END) AS stuCode
					,(SELECT g.group_code FROM `rms_group` AS g WHERE g.id=(SELECT ds.group_id FROM rms_group_detail_student AS ds 
					WHERE ds.itemType=1 AND ds.stu_id=s.stu_id AND ds.is_maingrade=1 AND ds.grade=sp.grade AND ds.degree=sp.degree ORDER BY ds.gd_id DESC LIMIT 1) LIMIT 1) AS groupCode
					
	    			,(CASE WHEN s.stu_khname IS NULL OR s.stu_khname='' THEN s.stu_enname ELSE s.stu_khname END) AS stuName
					
	    			,(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=sp.academic_year) AS academicYear
					,(SELECT $label FROM `rms_view` WHERE type=8 AND key_code=sp.payment_method LIMIT 1) AS paymentMethod
					,sp.number AS methodSerialNumber
	 		       ,(SELECT CONCAT(u.last_name,'-',u.first_name) FROM rms_users AS u WHERE u.id = sp.user_id LIMIT 1) AS userName
				   ,(SELECT $label FROM rms_view WHERE TYPE=10 AND key_code = sp.is_void LIMIT 1) AS voidTitle
	 		       
 			   FROM 
    				rms_student AS s,
					rms_student_payment AS sp
				WHERE 
					s.stu_id=sp.student_id 
					AND sp.is_void = 0
					";
    	
	    	$from_date =(empty($search['startDate']))? '1': " sp.create_date >= '".date("Y-m-d",strtotime($search['startDate']))." 00:00:00'";
	    	$to_date = (empty($search['endDate']))? '1': " sp.create_date <= '".date("Y-m-d",strtotime($search['endDate']))." 23:59:59'";
	    	$where = " AND ".$from_date." AND ".$to_date;
			$where.=" AND sp.status = 1 ";
			if(!empty($search['searchBox'])){
	    		$s_where=array();
	    		$s_search=addslashes(trim($search['searchBox']));
	    		$s_search = str_replace(' ', '', addslashes(trim($search['searchBox'])));
	    		$s_where[]= " REPLACE(sp.receipt_number,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(sp.paid_amount,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(sp.balance_due,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(sp.number,' ','') LIKE '%{$s_search}%'";
	    		
	    		$where.=' AND ('.implode(' OR ', $s_where).')';
	    	}
	    	if(!empty($search['academicYear'])){
	    		$where.=" AND sp.academic_year=".$search['academicYear'];
	    	}
			if(!empty($search['paymentMethod'])){
	    		$where.=" AND sp.payment_method=".$search['paymentMethod'];
	    	}
			
			
			$where.=" AND sp.student_id = ".$studentId;
	    	$ordering=" ORDER BY sp.create_date DESC";
			$limit=" ";
			if(!empty($search['LimitStart'])){
				$limit.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
			}else if(!empty($search['limitRecord'])){
	    		$limit.=" LIMIT ".$search['limitRecord'];
	    	}
			if(!empty($search['unreadRecord'])){
				if($search['unreadRecord']==1){ //unread
					$sql.=" AND sp.`isRead` =0 ";
				}else if($search['unreadRecord']==2){ //read
					$sql.=" AND sp.`isRead` =1 ";
				}else if($search['unreadRecord']==3){ //all
					$ordering=" ORDER BY sp.`isRead` ASC,sp.create_date DESC";
				}
				return  $db->fetchAll($sql.$where.$ordering.$limit);
			}
			 
			$row = $db->fetchAll($sql.$where.$ordering.$limit);
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	public function getStudentPaymentInfo($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$studentId = empty($search['studentId'])?0:$search['studentId'];
			$paymentId = empty($search['paymentId'])?0:$search['paymentId'];
			
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			
			$label = "name_en";
			$schooName = "school_nameen";
    		$branch = "branch_nameen";
    		$grade = "rms_itemsdetail.title_en";
    		$degree = "rms_items.title_en";
			if($currentLang==1){// khmer
				$label = "name_kh";
				$schooName = "school_namekh";
				$branch = "branch_namekh";
				$grade = "rms_itemsdetail.title";
				$degree = "rms_items.title";
			}
			
			$sql=" SELECT 
    				sp.*
    				,(SELECT $schooName FROM `rms_branch` WHERE br_id=sp.branch_id LIMIT 1) AS schoolName
    				,(SELECT $branch FROM `rms_branch` WHERE br_id=sp.branch_id LIMIT 1) AS branchName
    				,sp.receipt_number AS receiptNo
					,sp.create_date AS paymentDate
	    			,(CASE WHEN sp.data_from=3 THEN s.serial ELSE s.stu_code END) AS stuCode
	    			,(CASE WHEN s.stu_khname IS NULL OR s.stu_khname='' THEN s.stu_enname ELSE s.stu_khname END) AS stuName
					,(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = sp.group_id LIMIT 1) AS groupCode
	    			,(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=sp.academic_year) AS academicYear
					,(SELECT $label FROM `rms_view` WHERE type=8 AND key_code=sp.payment_method LIMIT 1) AS paymentMethod
					,sp.number AS methodSerialNumber
	 		       ,(SELECT CONCAT(u.last_name,'-',u.first_name) FROM rms_users AS u WHERE u.id = sp.user_id LIMIT 1) AS userName
				   ,(SELECT $label FROM rms_view WHERE TYPE=10 AND key_code = sp.is_void LIMIT 1) AS voidTitle
	 		       
 			   FROM 
    				rms_student AS s,
					rms_student_payment AS sp
				WHERE 
					s.stu_id=sp.student_id 
					";
			$where=" AND sp.status = 1 ";
			$where.=" AND sp.student_id = ".$studentId;
			$where.=" AND sp.id = ".$paymentId;
			$where.=" LIMIT 1 ";
			$row = $db->fetchRow($sql.$where);
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	public function getStudentPaymentDetail($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$studentId = empty($search['studentId'])?0:$search['studentId'];
			$paymentId = empty($search['paymentId'])?0:$search['paymentId'];
			
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			
			$label = "name_en";
			$branch = "branch_nameen";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
			if($currentLang==1){
				$label = "name_kh";
				$branch = "branch_namekh";
				$grade = "rms_itemsdetail.title";
				$degree = "rms_items.title";
			}
			
			$sql=" SELECT 
					spd.*
					,CASE 
						WHEN COALESCE(spd.totalpayment,0) < COALESCE(spd.fee,0) 
						THEN FORMAT(COALESCE(spd.fee,0) - COALESCE(spd.totalpayment,0),2)
						ELSE spd.total_discount
					END AS total_discount
			    	,(SELECT $grade FROM `rms_itemsdetail` WHERE id=spd.itemdetail_id LIMIT 1) AS itemsName
			    	,(SELECT items_type FROM `rms_itemsdetail` WHERE id=spd.itemdetail_id LIMIT 1) AS itemsType
			    	,(SELECT $label FROM `rms_view` WHERE  `type`=6 AND key_code= spd.payment_term LIMIT 1) AS paymentTerm
    			FROM 
			    	rms_student_payment as sp,
			    	rms_student_paymentdetail AS spd ";
			$sql.='WHERE sp.id=spd.payment_id 
				AND spd.payment_id = '.$paymentId;
			
			$row = $db->fetchAll($sql);
			
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	public function getNewsDetail($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$id = empty($search['id'])?0:$search['id'];
			$sql=" SELECT 
						a.*,
						ad.title,
						ad.description
					FROM `mobile_news_event` AS a,
						`mobile_news_event_detail` AS ad
					WHERE a.id=ad.news_id
						AND ad.lang= $currentLang 
						AND a.status=1 ";
			$sql.=" AND a.id=".$id;
    		$row = $db->fetchRow($sql);
			
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	public function getSystemLanguage($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$id = empty($search['id'])?0:$search['id'];
			$sql="SELECT * FROM `ln_language` AS l WHERE l.`status`=1 ORDER BY l.ordering ASC";
    		$row = $db->fetchAll($sql);
			
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	public function getSystemViewType($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$type = empty($search['type'])?0:$search['type'];
			
			$colunmname='name_en';
			if ($currentLang==1){
				$colunmname='name_kh';
			}
			$sql="
				SELECT v.key_code AS id,
				v.$colunmname AS name
				FROM `rms_view` AS v WHERE v.status=1 ";
			$sql.=" AND v.type= ".$type;
			$oder=" ORDER BY v.key_code ASC ";
		
    		$row = $db->fetchAll($sql.$oder);
			
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	public function getMonthOfTheYear($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$colunmname='month_en';
			if ($currentLang==1){
				$colunmname='month_kh';
			}
			$sql="
				SELECT m.id,
				m.$colunmname AS name
				FROM `rms_month` AS m WHERE m.status=1 ";
			$oder=" ORDER BY m.id ASC ";
		
    		$row = $db->fetchAll($sql.$oder);
			
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	public function getSystemAcademicYear($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$studentId = empty($search['studentId'])?0:$search['studentId'];
			$sql="
			SELECT ay.id,
				CONCAT(ay.fromYear,'-',ay.toYear) AS name
			FROM rms_academicyear AS ay WHERE ay.status=1 ";
			$oder=" ORDER BY ay.fromYear DESC ";
		
    		$row = $db->fetchAll($sql.$oder);
			
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	public function getSystemStudyDegree($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$studentId = empty($search['studentId'])?0:$search['studentId'];
			
			$colunmname='title_en';
			if ($currentLang==1){
				$colunmname='title';
			}
			
			$this->_name = "rms_items";
			$sql="SELECT m.id, m.$colunmname AS name FROM $this->_name AS m WHERE m.status=1 ";
			$sql.=" AND m.type=1 ";
			$sql.=' ORDER BY m.schoolOption ASC,m.type DESC,m.ordering DESC, m.title ASC';	
		
    		$row = $db->fetchAll($sql);
			
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	public function getSystemSettingKeycode($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			
			$sql = 'SELECT `keyName`,`keyValue` FROM `rms_setting`';
			$_result = $db->fetchAll($sql);
			
			$_k = array(); 
			foreach ($_result as $key => $k) {
				$_k[$k['keyName']] = $k['keyValue'];
			}
			$row = $_k;
		
			
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	function getUnreadNews($search){
		
		$db = $this->getAdapter();
		try{
			$row = $this->getAllNews($search);
			$counting = count($row);
			$allResult = array('rowData'=>$row,'countingRecord'=>$counting);
			$result = array(
				'status' =>true,
				'value' =>$allResult,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	
	function getUnreadNotification($search){
		
		$db = $this->getAdapter();
		try{
			if($search['unreadRecord']==3){
				
				if(!empty($search['limitRecord'])){
					$limitRecordScore=empty($search['limitRecord'])?null:$search['limitRecord'];
					$limitRecordPayment=empty($search['limitRecord'])?null:$search['limitRecord'];
					
					$limitStartScore=empty($search['LimitStart'])?null:$search['LimitStart'];
					$limitStartPayment=empty($search['LimitStart'])?null:$search['LimitStart'];
				
					
					$search['unreadRecord'] =1;
					$search['limitRecord'] =null;
					$search['LimitStart'] =null;
					$countingUnreadScore = count($this->getStudentScore($search));
					$countingUnreadPayment = count($this->getStudentPaymentHistory($search));	
					
					if(empty($limitStartScore)){
					
						$limitRecordScore=$limitRecordScore+$countingUnreadScore;
						$limitRecordPayment=$limitRecordPayment+$countingUnreadPayment;
					}else{
						$limitStartPayment=$limitStartPayment-$countingUnreadScore;
						
						$limitStartScore=$limitStartScore-$countingUnreadPayment;
					}
					
					$search['unreadRecord'] =3;
					$search['limitRecord'] = $limitRecordScore;
					$search['LimitStart'] = $limitStartScore;
					$row = $this->getStudentScore($search);

					$search['limitRecord'] = $limitRecordPayment;
					$search['LimitStart'] = $limitStartPayment;
					$rRow = $this->getStudentPaymentHistory($search);
				}else{
					$row = $this->getStudentScore($search);
					$rRow = $this->getStudentPaymentHistory($search);
				}
				
				$row = (object) array_merge((array) $rRow, (array) $row);//merg two object array list
				
			}else{
				$row = $this->getStudentScore($search);
				$rRow = $this->getStudentPaymentHistory($search);
				$row = (object) array_merge((array) $rRow, (array) $row);//merg two object array list
				
			}
			
			
		$row = (array) $row;//sort by key Value DESC
		usort($row, function ($a, $b) {return $a['recordDate'] < $b['recordDate'];});

			$counting = count($row);
			$allResult = array('rowData'=>$row,'countingRecord'=>$counting);
			$result = array(
				'status' =>true,
				'value' =>$allResult,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	function checkingReadingReady($_data){
		$db = $this->getAdapter();
		$sql="SELECT nr.id FROM mobile_news_event_read AS nr WHERE nr.stuId=".$_data['studentId']." AND nr.newsId=".$_data['newsId']." LIMIT 1";
		return $db->fetchOne($sql);
	}
	function updateNewsRead($_data){
		$db = $this->getAdapter();
		try{
			if($_data['recordType']=="markAllRead"){//
				
				$row = $this->getAllNews($_data);
				if(!empty($row)) foreach($row as $rs){
					$_data['newsId'] = empty($rs['id'])?0:$rs['id'];
					$checking = $this->checkingReadingReady($_data);
					if(empty($checking)){
						$_arr=array(
							'newsId'	=> $rs['id'],
							'stuId'	  	=> $_data['studentId'],
							'date'	  	=> date("Y-m-d"),
							'is_read'	=> 1,
						);
						$this->_name = "mobile_news_event_read";
						$this->insert($_arr);	
					}
				}
			}else{
				
				$checking = $this->checkingReadingReady($_data);
				
				if(empty($checking)){
					$_arr=array(
						'newsId'	=> $_data['newsId'],
						'stuId'	  	=> $_data['studentId'],
						'date'	  	=> date("Y-m-d"),
						'is_read'	=> 1,
					);
					$this->_name = "mobile_news_event_read";
					$this->insert($_arr);
				}
			}
			
			$unreadRemain = $this->getUnreadNews($_data);
			$countingRecord=0;
			if(!empty($unreadRemain['value']['countingRecord'])){
				$countingRecord=$unreadRemain['value']['countingRecord'];
			}
			$result = array(
					'status' =>true,
					'value' =>$countingRecord,
			);
			return $result;
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	function checkingPaymentReading($_data){
		$db = $this->getAdapter();
		$sql="SELECT nr.id FROM rms_student_payment AS nr WHERE nr.student_id=".$_data['studentId']." AND nr.id=".$_data['recordId']." AND nr.isRead=1 LIMIT 1";
		return $db->fetchOne($sql);
	}
	function checkingScoreReading($_data){
		$db = $this->getAdapter();
		$sql="SELECT nr.id FROM rms_score_monthly AS nr WHERE nr.student_id=".$_data['studentId']." AND nr.score_id=".$_data['recordId']." AND nr.isRead=1 LIMIT 1";
		return $db->fetchOne($sql);
	}
	function updateNotificationRead($_data){
		$db = $this->getAdapter();
		try{
			
			if($_data['recordType']=="markAllRead"){ 
				$row = $this->getStudentPaymentHistory($_data);
				if(!empty($row)){
					foreach($row as $rs){
						$_data['recordId'] = empty($rs['id'])?0:$rs['id'];
						$checking = $this->checkingPaymentReading($_data);
						if(empty($checking)){
							$_arr=array(
								'readDate'	  	=> date("Y-m-d H:i:s"),
								'isRead'	=> 1,
							);
							$this->_name = "rms_student_payment";
							$where ="id=".$rs['id']." AND student_id=".$_data['studentId'];
							$this->update($_arr, $where);
						}
					}
				}
				$rowScore = $this->getStudentScore($_data);
				if(!empty($rowScore)){
					foreach($rowScore as $rs){
						$_data['recordId'] = empty($rs['id'])?0:$rs['id'];
						$checking = $this->checkingScoreReading($_data);
						if(empty($checking)){
							$_arr=array(
								'readDate'	  	=> date("Y-m-d H:i:s"),
								'isRead'	=> 1,
							);
							$this->_name = "rms_score_monthly";
							$where ="score_id=".$rs['id']." AND student_id=".$_data['studentId'];
							$this->update($_arr, $where);
						}
					}
				}
			}else{
				
				if($_data['recordType']=="payment"){
					$checking = $this->checkingPaymentReading($_data);
					if(empty($checking)){
						$_arr=array(
							'readDate'	  	=> date("Y-m-d H:i:s"),
							'isRead'		=> 1,
						);
						$this->_name = "rms_student_payment";
						$where ="id=".$_data['recordId']." AND student_id=".$_data['studentId'];
						$this->update($_arr, $where);
					}
				}else if($_data['recordType']=="studyScore"){
					$checking = $this->checkingScoreReading($_data);
					if(empty($checking)){
						$_arr=array(
							'readDate'	  	=> date("Y-m-d H:i:s"),
							'isRead'		=> 1,
						);
						$this->_name = "rms_score_monthly";
						$where ="score_id=".$_data['recordId']." AND student_id=".$_data['studentId'];
						$this->update($_arr, $where);
					}
				}
			}
			
			$unreadRemain = $this->getUnreadNotification($_data);
			$countingRecord=0;
			if(!empty($unreadRemain['value']['countingRecord'])){
				$countingRecord=$unreadRemain['value']['countingRecord'];
			}
			$result = array(
					'status' =>true,
					'value' =>$countingRecord,
			);
			return $result;
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	public function getAllMobileNotification($search){
    		$db = $this->getAdapter();
    		try{
    			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    			$studentId = empty($search['studentId'])?0:$search['studentId'];
				
    			$mobileToken = empty($search['mobileToken'])?0:$search['mobileToken'];
    			$isCounting = empty($search['isCounting'])?0:$search['isCounting'];
    			$base_url = Zend_Controller_Front::getInstance()->getBaseUrl()."/images/";
				
				$tokenInfo = $this->getTokenInfo($mobileToken);
				$studentIdValue = $studentId;
				
				$whereIsRead = " AND ntf_r.mobileToken = '$mobileToken' ";
				if($studentId>0){
					$studentId = "0,".$studentId;
					$whereIsRead = " AND ntf_r.studentId = '$studentIdValue' ";
				}
    			$sql=" SELECT ntf.id, 
							ntf_d.title,
							CASE 
								WHEN ntf.group >0 THEN ss.stu_code 
								ELSE s.stu_code
							END AS studentCode,
							CASE 
								WHEN ntf.group >0 THEN grp_d.stu_id 
								ELSE ntf.student
							END AS studentId,
							ntf.group,
							ntf.date AS publicDate,
							ntf_d.description,
							ntf.opt_notification,
							ntf.type,
							ntf.actionId,
							COALESCE((SELECT ntf_r.isRead FROM mobile_notify_read AS ntf_r WHERE ntf_r.notification_id = ntf.id ".$whereIsRead." ORDER BY ntf_r.isRead DESC LIMIT 1),0) AS isRead
						FROM `mobile_notice` AS ntf 
							JOIN `mobile_notice_detail` AS ntf_d 
							LEFT JOIN `rms_student` AS s ON s.stu_id = ntf.student 
							LEFT JOIN (`rms_group_detail_student` AS grp_d JOIN `rms_student` AS ss ON ss.stu_id = grp_d.stu_id ) 
								ON grp_d.group_id = ntf.group AND grp_d.group_id !=0
						WHERE ntf_d.notification_id = ntf.id 
							AND ntf_d.lang = ".$currentLang;
				$sql.=" 
					AND CASE 
						WHEN ntf.group >0 THEN grp_d.stu_id 
						ELSE ntf.student
					END IN ($studentId)  ";
				
				if(!empty($isCounting)){
					$dateToken = empty($tokenInfo['date'])?"2023-01-01 00:00:00":$tokenInfo['date'];
					if($studentIdValue==0){
						$sql.=" AND ntf.date >= '$dateToken' ";
					}
					$sql.=" AND COALESCE((SELECT ntf_r.isRead FROM mobile_notify_read AS ntf_r WHERE ntf_r.notification_id = ntf.id ".$whereIsRead." ORDER BY ntf_r.isRead DESC LIMIT 1),0) =0 ";
				}
				
				$sql.=" ORDER BY ntf.date DESC ";
				if(!empty($search['LimitStart'])){
					$sql.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
				}else if(!empty($search['limitRecord'])){
					$sql.=" LIMIT ".$search['limitRecord'];
				}
    	
    			$row = $db->fetchAll($sql);
    			$result = array(
    					'status' =>true,
    			'value' =>$row,
    			);
    			return $result;
    		}catch(Exception $e){
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			$result = array(
    				'status' =>false,
    				'value' =>$e->getMessage(),
    			);
    			return $result;
    		}
    }
	
	public function getTokenInfo($token){
		$db = $this->getAdapter();
		$sql="SELECT t.* FROM mobile_mobile_token AS t WHERE t.token = '$token' ORDER BY t.id ASC LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	public function getNotifyInfoByType($type,$actionId){
		$db = $this->getAdapter();
		$sql="SELECT t.* FROM mobile_notice AS t WHERE t.type = '$type' AND t.actionId = '$actionId' LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function checkIsRead($studentId,$notificationId){
		$db = $this->getAdapter();
		$sql="SELECT 
				ntf_r.* FROM mobile_notify_read AS ntf_r 
			WHERE ntf_r.studentId = $studentId AND ntf_r.notification_id=$notificationId 
			LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function checkIsReadByToken($token,$notificationId){
		$db = $this->getAdapter();
		$sql="SELECT 
				ntf_r.* FROM mobile_notify_read AS ntf_r 
			WHERE ntf_r.mobileToken = '$token' AND ntf_r.notification_id=$notificationId 
			LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	public function setReadNotification($_data){
		$db = $this->getAdapter();
		try{
			$readType 	= empty($_data['readType'])?"0":$_data['readType'];
			$recordType 	= empty($_data['recordType'])?"0":$_data['recordType'];
			$mobileToken 	= empty($_data['mobileToken'])?0:$_data['mobileToken'];
			$studentId 		= empty($_data['studentId'])?0:$_data['studentId'];
			$notificationId = empty($_data['notificationId'])?0:$_data['notificationId'];
			$actionId 		= empty($_data['actionId'])?0:$_data['actionId'];
			
			if($readType=="markAllRead"){ 
				$rowNotify = $this->getAllMobileNotification($_data);
				
				if(!empty($rowNotify['value'])){
					foreach($rowNotify['value'] as $rs){
						$_arr=array(
							'notification_id'	=> $rs['id'],
							'studentId'			=> $studentId,
							'mobileToken'		=> $mobileToken,
							'createDate'	  	=> date("Y-m-d H:i:s"),
							'modifyDate'	  	=> date("Y-m-d H:i:s"),
							'isRead'	=> 1,
						);
						$this->_name = "mobile_notify_read";
						if($studentId>0){
							$check = $this->checkIsRead($studentId,$rs['id']);
							if(empty($check)){
								$this->insert($_arr);
							}
						}else{
							$check = $this->checkIsReadByToken($mobileToken,$rs['id']);
							if(empty($check)){
								$this->insert($_arr);
							}
						}
					}
				}
			}else if($readType=="checkTokenRead"){ // when user login
					$sql="
						SELECT 
							ntf_r.* FROM mobile_notify_read AS ntf_r 
						WHERE ntf_r.mobileToken = '$mobileToken' AND ntf_r.studentId=0
					 ";
					$readyByToken = $db->fetchAll($sql);
					if(!empty($readyByToken)){
						foreach($readyByToken as $read){
							if($studentId>0){
								$check = $this->checkIsRead($studentId,$read['notification_id']);
								if(empty($check)){
									$_arr=array(
										'studentId'			=> $studentId,						
										'modifyDate'	  	=> date("Y-m-d H:i:s"),
									);
									$where = " id = ".$read['id'];
									$this->_name = "mobile_notify_read";
									$this->update($_arr,$where);
								}
							}
						}
					}
			}else{
				if($notificationId>0){ 
					$_arr=array(
						'notification_id'	=> $notificationId,
						'studentId'			=> $studentId,
						'mobileToken'		=> $mobileToken,
						'createDate'	  	=> date("Y-m-d H:i:s"),
						'modifyDate'	  	=> date("Y-m-d H:i:s"),
						'isRead'	=> 1,
					);
					$this->_name = "mobile_notify_read";
					if($studentId>0){
						$check = $this->checkIsRead($studentId,$notificationId);
						if(empty($check)){
							$this->insert($_arr);
						}
					}else{
						$check = $this->checkIsReadByToken($mobileToken,$notificationId);
						if(empty($check)){
							$this->insert($_arr);
						}
					}
				}else{
					$notifyInfo = $this->getNotifyInfoByType($_data['recordType'],$actionId);
					if(!empty($notifyInfo)){
						$notificationId = empty($notifyInfo['id'])?0:$notifyInfo['id'];
						$_arr=array(
							'notification_id'	=> $notificationId,
							'studentId'			=> $studentId,
							'mobileToken'		=> $mobileToken,
							'createDate'	  	=> date("Y-m-d H:i:s"),
							'modifyDate'	  	=> date("Y-m-d H:i:s"),
							'isRead'	=> 1,
						);
						$this->_name = "mobile_notify_read";
						if($studentId>0){
							$check = $this->checkIsRead($studentId,$notificationId);
							if(empty($check)){
								$this->insert($_arr);
							}
						}else{
							$check = $this->checkIsReadByToken($mobileToken,$notificationId);
							if(empty($check)){
								$this->insert($_arr);
							}
						}
					}
				}
			}
			
			$_data['isCounting'] = 1;
			$unreadRemain = $this->getAllMobileNotification($_data);
			$countingRecord=0;
			if(!empty($unreadRemain['value'])){
				$countingRecord=count($unreadRemain['value']);
			}
			$result = array(
					'status' =>true,
					'value' =>$countingRecord,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	function removeAppTokenId($_data){
		$db = $this->getAdapter();
		try{
			$mobileToken 	= empty($_data['mobileToken'])?0:$_data['mobileToken'];
			$studentId 		= empty($_data['studentId'])?0:$_data['studentId'];
			$currentStudentId 		= empty($_data['currentStudentId'])?0:$_data['currentStudentId'];
			$typeRemove 	= empty($_data['typeRemove'])?0:$_data['typeRemove'];
			$deviceType 	= empty($_data['deviceType'])?1:$_data['deviceType'];
			
			if($typeRemove==1){ //Swtiching
				$sql="SELECT id FROM mobile_mobile_token WHERE stu_id=".$studentId." AND token='".$mobileToken."' LIMIT 1";
	    		$rs = $db->fetchOne($sql);
	    		if(empty($rs)){
					$currentStudentCheck = 0;
					if($currentStudentId>0){
						$sql="SELECT id FROM mobile_mobile_token WHERE stu_id=".$currentStudentId." AND token='".$mobileToken."' LIMIT 1";
						$currentStudentCheck = $db->fetchOne($sql);
					}
					$_arr =array(
	    				'stu_id' 	=> $studentId,
	    				'token' 	=> $mobileToken,		
	    				'device_type' => $deviceType,
	    				'tokenType' => "0",
	    			);
					if($currentStudentCheck >0){
						$this->_name = "mobile_mobile_token";
						$where=" id = $currentStudentCheck ";
						$this->update($_arr,$where);
					}else{
						$_arr['date'] = date("Y-m-d H:i:s");
						$this->_name = "mobile_mobile_token";
						$this->insert($_arr);
					}
				}
			}else{ 
				if($studentId>0){
					$where ="stu_id=".$studentId." AND token='$mobileToken' ";
					$this->_name="mobile_mobile_token";
					$this->delete($where);
				}else{
					$currentStudentCheck = 0;
					if($currentStudentId>0){
						$sql="SELECT id FROM mobile_mobile_token WHERE token='".$mobileToken."' ORDER BY id ASC LIMIT 1";
						$currentStudentCheck = $db->fetchOne($sql);
					}
					if($currentStudentId>0){
						$where =" token='$mobileToken' AND id > $currentStudentCheck";
						$this->_name="mobile_mobile_token";
						$this->delete($where);
						
						$_arr =array(
							'stu_id' 	=> 0,
							'token' 	=> $mobileToken,		
							'device_type' => $deviceType,
							'tokenType' => "0",
						);
						$this->_name = "mobile_mobile_token";
						$where=" id = $currentStudentCheck ";
						$this->update($_arr,$where);
						
					}
					
				}
			}
			
			
			$result = array(
					'status' =>true,
					'value' =>$studentId,
			);
			return $result;
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	public function getMobileNotificationDetail($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$notificationId = empty($search['notificationId'])?0:$search['notificationId'];
			$recordType = empty($search['recordType'])?0:$search['recordType'];
			$studentId = empty($search['studentId'])?0:$search['studentId'];
				
			$label = "name_en";
			$grade = "rms_itemsdetail.title_en";
			$degree = "rms_items.title_en";
			$branch = "b.branch_nameen";
			$month = "month_en";
			$teacherName= "teacher_name_en";
			if($currentLang==1){// khmer
				$teacherName = "teacher_name_kh";
				$label = "name_kh";
				$grade = "rms_itemsdetail.title";
				$degree = "rms_items.title";
				$branch = "b.branch_namekh";
				$month = "month_kh";
			}
			$sql = "
				SELECT 
					ntf.*
					,ntf.date AS publishDate
					,ntf_d.title AS announcementTitle
					,ntf_d.description AS announcementDescription
					,g.group_code AS groupCode
					,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = g.academic_year LIMIT 1) as academicYearTitle
					,(SELECT $degree FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degreeTitle
					,(SELECT $grade FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 )AS gradeTitle
					,(SELECT $label	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`
					,(SELECT t.$teacherName FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherName
					,(SELECT t.teacher_name_kh FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherNameKh
					,(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teaccherNameEng
					,(SELECT t.signature FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherSigature
					,(SELECT t.tel FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherTel
			";	
			if($recordType==1){
				$sql.="
					,sp.receipt_number AS receiptNo
					,sp.create_date AS paymentDate
					,COALESCE(sp.grand_total,0) AS grandTotal
					,COALESCE(sp.credit_memo,0) AS creditMemo
					,COALESCE(sp.paid_amount,0) AS paidAmount
					,COALESCE(sp.balance_due,0) AS balanceDue
					,(SELECT v.$label FROM `rms_view` AS v WHERE v.type=8 AND v.key_code=sp.payment_method LIMIT 1) AS paymentMethod
					,(SELECT CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id = sp.user_id LIMIT 1) AS receivedBy
				";
			}else if($recordType==2){
				$sql.=" 
			
					,(SELECT $label FROM `rms_view` WHERE TYPE=19 AND key_code =sc.exam_type LIMIT 1) as forTypeTitle
					,CASE
						WHEN sc.exam_type = 2 THEN sc.for_semester
					ELSE (SELECT $month FROM `rms_month` WHERE id=sc.for_month  LIMIT 1) 
					END AS forMonthTitle
					
					,sm.total_score AS totalScore
					,sm.total_avg AS totalAvg
					,g.max_average/2 AS passAvrage
					,(SELECT SUM(amount_subject) FROM `rms_group_subject_detail` WHERE rms_group_subject_detail.group_id=g.`id` LIMIT 1) AS amountSubject
					,(SELECT SUM(amount_subject_sem) FROM `rms_group_subject_detail` WHERE rms_group_subject_detail.group_id=g.`id` LIMIT 1) AS amountSubjectsem
					,(SELECT rms_items.pass_average FROM `rms_items` WHERE rms_items.id=g.degree AND  rms_items.type=1 LIMIT 1) as averagePass
					,FIND_IN_SET( 
						sm.total_avg, 
						(
							SELECT GROUP_CONCAT( smSecond.total_avg ORDER BY total_avg DESC )
							FROM rms_score_monthly AS smSecond ,rms_score AS sSecond WHERE
							sSecond.`id`=smSecond.`score_id`
							AND sSecond.group_id= g.`id`
							AND sSecond.id=sc.`id`
						)
					) AS rank
					,(SELECT COUNT(gds.gd_id)  FROM `rms_group_detail_student` AS gds WHERE gds.group_id = g.id AND gds.is_maingrade=1 ) AS amountStudent
				";
			}
			$sql.="	FROM `mobile_notice` AS ntf 
					JOIN `mobile_notice_detail` AS ntf_d ON ntf_d.notification_id = ntf.id 
					LEFT JOIN `rms_group` AS g ON g.id = ntf.group
					";
			if($recordType==1){
				$sql.=" 
					LEFT JOIN `rms_student_payment` AS sp ON sp.id = ntf.actionId AND ntf.type = 1 AND sp.student_id = $studentId
				";
			}else if($recordType==2){
				$sql.="
					LEFT JOIN (`rms_score` AS sc JOIN rms_score_monthly AS sm on  sc.`id`=sm.`score_id`) ON sc.id = ntf.actionId AND ntf.type = 2 AND sc.status = 1 AND sm.student_id = $studentId
				";
			}
					
			$sql.="	WHERE  ntf_d.lang = $currentLang 
					AND ntf.id = $notificationId
			";
			$sql.=" LIMIT 1 ";

			$row = $db->fetchRow($sql);
			$row = empty($row) ? null : $row;
			$result = array(
					'status' =>true,
					'value' =>$row,
				);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
    }
	
	function getCheckExistingRegisterStudent($_data){
		$db = $this->getAdapter();
		$_data['phoneNumber']=trim($_data['phoneNumber']);
		$_data['countryCode']=trim($_data['countryCode']);
		$_data['emailAddress']=trim($_data['emailAddress']);
		
		$_data['isCheckDuplicateRegister'] = empty($_data['isCheckDuplicateRegister'])?0:$_data['isCheckDuplicateRegister'];
		try{
			$sql =" SELECT
				s.*
				,s.stu_id AS id
				,s.stu_code AS stuCode
				,s.stu_khname AS stuNameKH
				,s.stu_enname AS stuFirstName
				,s.last_name AS stuLastName
				,s.photo
				,'1' AS isFromStudentTB
			FROM
				rms_student AS s
			WHERE s.status = 1 AND s.customer_type NOT IN (2,3) ";
			
			if($_data['isCheckDuplicateRegister']=="0"){
				$sql.= " AND s.tel= '".$_data['phoneNumber']."'";
				//$sql.= " AND s.countryCode='".$_data['countryCode']."'";
			}else if($_data['isCheckDuplicateRegister']=="1"){
				$sql.= " AND s.email='".$_data['emailAddress']."'";
			}
			
			$sql.=" LIMIT 1 ";
			$row = $db->fetchRow($sql);
			
			if(empty($row)){
				$sql =" SELECT
					pre.*
					,'0' AS isFromStudentTB
				FROM
					rms_mobile_pre_register AS pre
				WHERE pre.status = 1 ";
				
				if($_data['isCheckDuplicateRegister']=="0"){
					$sql.= " AND pre.phoneNumber= '".$_data['phoneNumber']."'";
					$sql.= " AND pre.countryCode='".$_data['countryCode']."'";
				}else if($_data['isCheckDuplicateRegister']=="1"){
					$sql.= " AND pre.email='".$_data['emailAddress']."'";
				}
				$sql.=" LIMIT 1 ";
				$row = $db->fetchRow($sql);
			}
			
			
			$row = empty($row) ? null : $row;
			$result = array(
					'status' =>true,
					'value' =>$row,
			);
			return $result;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	function radomNumber(){
		$digits = 4;
		return str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
	}
	function getPreRegisterInfo($_data){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$studentId = empty($_data['studentId'])?0:$_data['studentId'];
		
			$sql=" SELECT 
						pre.*
						,'0' AS isFromStudentTB 
					FROM  rms_mobile_pre_register AS pre
					WHERE pre.status = 1
				";

			$row = $db->fetchRow($sql);
			$result = array(
					'status' =>true,
					'value' =>$row,
				);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	function submitNewRegister($_data){
    	$db = $this->getAdapter();
		$db->beginTransaction();
    	try{
			
			$format = 'Y-m-d';
			$dateString = $_data['dob'];
			$date = new DateTime($dateString);
			$dateOfBirth = $date->format($format);
			
			$_data['branchId']	= empty($_data['branchId'])?0:$_data['branchId'];
			$_data['note']	= empty($_data['note'])?"":$_data['note'];
		
			$arr = array(
				'firstName' 		=> $_data['firstName'],
				'lastName' 		=> $_data['lastName'],
				'fullKhName' 		=> $_data['fullKhName'],
				'countryISOCode' 	=> $_data['countryISOCode'],
				'countryCode' 			=> $_data['countryCode'],
				
				'phoneNumber' 		=> $_data['phoneNumber'],
				'deviceType' 		=> $_data['deviceType'],
				'deviceToken' 		=> $_data['mobileToken'],
				
				'dob' 				=> $dateOfBirth,
				'gender' 			=> $_data['gender'],
				'degree' 			=> $_data['degree'],
				'createDate' 		=> date("Y-m-d H:i:s"),
				'modifyDate' 		=> date("Y-m-d H:i:s"),
				'status' 			=> 1,
				'isVerifiedAccount' 	=> 1,
				'verifyCode' 			=> $this->radomNumber(),
				'expireDateVerifyCode' 	=> date("Y-m-d H:i:s",strtotime("+1 day")),
			);
			
    		$this->_name='rms_mobile_pre_register';
    		$userId = $this->insert($arr);
    		
			
			$db->commit();
			$result = array(
					'status' =>true,
					'value' =>$userId,
			);
			return $result;
    		
    	}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
    	}
    }
	
	
	function getAllItems($_data){
		$db = $this->getAdapter();
		
		$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		$type = empty($_data['type'])?1:$_data['type'];
		$this->_name = "rms_items";
		$sql="SELECT m.id, CONCAT(m.$colunmname,' (',COALESCE(`m`.`shortcut`,''),')' ) AS name FROM $this->_name AS m WHERE m.status=1 ";
		if (!empty($type)){
			$sql.=" AND m.type=$type";
		}
		$sql .=' ORDER BY m.schoolOption ASC,m.type DESC,m.ordering DESC, m.title ASC';	
		return $db->fetchAll($sql);
	  }
	function getAllGroupStudyByStudent($_data){
		$db = $this->getAdapter();
		$studentId = empty($_data['studentId'])?0:$_data['studentId'];
		$_data['academicYear'] = empty($_data['academicYear'])?0:$_data['academicYear'];
		$sql="SELECT 
				g.id,
				g.group_code AS name
			FROM 
				`rms_group_detail_student` AS gds
				join `rms_group` AS g ON g.id = gds.group_id
			WHERE gds.stu_id = $studentId 
				AND g.status = 1
				AND gds.group_id >0
			";
			if(!empty($_data['academicYear'])){
				$sql.=" AND g.academic_year = ".$_data['academicYear'];	
			}
		
		$sql .=' ORDER BY g.degree DESC,g.id DESC';	
		return $db->fetchAll($sql);
	}
	function getAllCriteriaByStudent($_data){
		$db = $this->getAdapter();
		$studentId = empty($_data['studentId'])?0:$_data['studentId'];
		$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
		$title="title";
		if($currentLang==2){
			$title="title_en";
		}
		$sql="SELECT 
				DISTINCT grdTmp.`criteriaId` AS id
				,cri.$title AS `name`
			FROM 
				`rms_grading_tmp` AS grdTmp 
				JOIN `rms_grading_detail_tmp` AS grdTmpD ON grdTmp.`id` = grdTmpD.`gradingId` 
				JOIN `rms_exametypeeng` AS cri ON cri.id = grdTmp.`criteriaId` 
			WHERE grdTmpD.`studentId` = $studentId 
				
			";
		$sql .=' ORDER BY cri.criteriaType ASC,cri.id ASC ';	
		return $db->fetchAll($sql);
	}
	public function getFormOptionSelect($_data){
		$db = $this->getAdapter();
		try{
			
			
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$getControlType = empty($_data['getControlType'])?"status":$_data['getControlType'];
			$_data['studentId'] = empty($_data['studentId'])?0:$_data['studentId'];
			$_data['branchId'] = empty($_data['branchId'])?0:$_data['branchId'];
			$row=array();
			if($getControlType=="status"){
				$row = array(
					array("id"=>1,"name"=>$currentLang==1 ? "ប្រើប្រាស់" : "Active"),
					array("id"=>2,"name"=>$currentLang==1 ? "មិនប្រើប្រាស់" : "Deactive"),
				);
			}else if($getControlType=="studyDegree"){
				$_data["type"]=1;
				$row = $this->getAllItems($_data);
			}else if($getControlType=="groupStudy"){
				$row = $this->getAllGroupStudyByStudent($_data);
			}else if($getControlType=="requestStatus"){
				$row = array(
					array("id"=>1,"name"=>$currentLang==1 ? "កំពុងរង់ចាំ" : "Pending"),
					array("id"=>2,"name"=>$currentLang==1 ? "បានយល់ព្រម" : "Approved"),
					array("id"=>3,"name"=>$currentLang==1 ? "បានបដិសេធ" : "Rejected"),
				);
			}else if($getControlType=="sessionAttendance"){
				$row = array(
					array("id"=>1,"name"=>$currentLang==1 ? "ព្រឹក" : "Morning"),
					array("id"=>2,"name"=>$currentLang==1 ? "ពេលល្ងាច" : "Evening"),
					array("id"=>3,"name"=>$currentLang==1 ? "ពេញមួយថ្ងៃ" : "Full Day"),
				);
			}else if($getControlType=="criteriaList"){
				$row = $this->getAllCriteriaByStudent($_data);
				if(!empty($_data['withAllOpt'])){
					array_unshift(
						$row
						,array('id' => "0",'name' =>$currentLang==1 ? "ទាំងអស់" : "All") 
					);
				}
			}
			
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	public function getTotalStudentCreditMemo($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$studentId = empty($search['studentId'])?0:$search['studentId'];
			
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			
			$label = "name_en";
			$schooName = "school_nameen";
    		$branch = "branch_nameen";
			if($currentLang==1){// khmer
				$label = "name_kh";
				$schooName = "school_namekh";
				$branch = "branch_namekh";
			}
			
			$sql=" SELECT 
    				credit.*
    				,(SELECT $schooName FROM `rms_branch` WHERE br_id=credit.branch_id LIMIT 1) AS schoolName
    				,(SELECT $branch FROM `rms_branch` WHERE br_id=credit.branch_id LIMIT 1) AS branchName
					,COALESCE(SUM(credit.total_amount),0) AS totalCreditAmount 
					,COALESCE(SUM(credit.total_amountafter),0) totalCreditAmountAfter  
	 		       
 			   FROM 
    				rms_creditmemo AS credit
				WHERE 
					credit.status = 1
					";
			$expireDate =" credit.end_date >= '".date("Y-m-d")." 23:59:59'";
			$sql.= " AND ".$expireDate;
			$sql.=" AND credit.student_id = ".$studentId;
			$sql.= " LIMIT 1 ";
			
			$row = $db->fetchRow($sql);
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	public function getAllStudentCreditMemo($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$studentId = empty($search['studentId'])?0:$search['studentId'];
			
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			
			$label = "name_en";
			$schooName = "school_nameen";
    		$branch = "branch_nameen";
			if($currentLang==1){// khmer
				$label = "name_kh";
				$schooName = "school_namekh";
				$branch = "branch_namekh";
			}
			
			$sql=" SELECT 
    				credit.*
    				,(SELECT $schooName FROM `rms_branch` WHERE br_id=credit.branch_id LIMIT 1) AS schoolName
    				,(SELECT $branch FROM `rms_branch` WHERE br_id=credit.branch_id LIMIT 1) AS branchName
					,credit.total_amount AS totalCreditAmount 
					,credit.total_amountafter AS totalCreditAmountAfter
					,CASE
						WHEN credit.end_date >= '".date("Y-m-d")." 23:59:59' THEN 0
						ELSE 1
					END as isExpired
	 		       
 			   FROM 
    				rms_creditmemo AS credit
				WHERE 
					credit.status = 1
					";
			//$expireDate =" credit.end_date >= '".date("Y-m-d")." 23:59:59'";
			//$sql.= " AND ".$expireDate;
			$sql.=" AND credit.student_id = ".$studentId;

			$ordering=" ORDER BY credit.id DESC";
			$limit=" ";
			if(!empty($search['LimitStart'])){
				$limit.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
			}else if(!empty($search['limitRecord'])){
	    		$limit.=" LIMIT ".$search['limitRecord'];
	    	}
			
			
			$row = $db->fetchAll($sql.$ordering.$limit);
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}	
	
	function getMentionGradeSetting($_data){
		$db = $this->getAdapter();
		try{
			
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$academicYear = empty($_data['academicYear'])?0:$_data['academicYear'];
			$degreeId = empty($_data['degreeId'])?0:$_data['degreeId'];
			
			$sql="
				SELECT 
					sd.*
					,sd.metion_grade AS mentionGradeTitle
					,sd.metion_in_khmer AS mentionGradeTitleKH
					,sd.mention_in_english AS mentionGradeTitleEng
				FROM `rms_metionscore_setting_detail` AS sd,
					`rms_metionscore_setting` AS s
				WHERE s.id = sd.metion_score_id
					AND s.academic_year=$academicYear
					AND s.degree = $degreeId
			";
			$sql.=" ORDER BY sd.max_score DESC ";
			$row = $db->fetchAll($sql);
			
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	function getStudentTranscriptExam($_data){
		$db = $this->getAdapter();
		try{
			
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$studentId = empty($_data['studentId'])?0:$_data['studentId'];
			$scoreId = empty($_data['id'])?0:$_data['id'];
			$_data['scoreId'] = $scoreId;
			
			$dbTrancript = new Allreport_Model_DbTable_DbScoreTranscript();
			$arrStudent = array(
					'studentId'=>$studentId
					);
			$studentInfo =  $dbTrancript->getStudentProfile($arrStudent);
		
			$scoreInfoFromAPI =  $this->getScoreInformation($_data);
			$scoreInfoApi = empty($scoreInfoFromAPI["value"]) ? null : $scoreInfoFromAPI["value"];
			$scoreInfo = $scoreInfoApi;
			
			//$resultArray= array(
					//'scoreId'=>$scoreId,
					//'studentId'=>$studentId
			//);
			//$scoreInfo =  $dbTrancript->getScoreInformation($resultArray);
			$scoreInfo['group_id'] 	= empty($scoreInfo['group_id']) ? 0: $scoreInfo['group_id'];
			$scoreInfo['exam_type'] = empty($scoreInfo['exam_type']) ? 0: $scoreInfo['exam_type'];
			$scoreInfo['for_semester'] = empty($scoreInfo['for_semester']) ? 0: $scoreInfo['for_semester'];
			$scoreInfo['for_month'] = empty($scoreInfo['for_month']) ? 0: $scoreInfo['for_month'];
			
			$resultScoreArr = array(
					'scoreId'=>$scoreId,
					'studentId'=>$studentId,
					'examType'=>$scoreInfo['exam_type']
					);
			$scoreSubjectInfo =  $dbTrancript->getSubjectScoreTranscript($resultScoreArr);
		
			$scoreResultList = array();
			if(!empty($scoreSubjectInfo)){
				foreach ($scoreSubjectInfo as $key=> $result){
					
					$scoreResultList[$key] = array(
							'subject_id'=>$result['subject_id'],
							'gradingTotalId'=>$result['gradingTotalId'],
							'totalAverage'=>$result['totalAverage'],
							'rankingSubject'=>$result['rankingSubject'],
							
							'score_cut'=>$result['score_cut'],
							'sub_name'=>$result['sub_name'],
							'subjectLang'=>$result['subjectLang'],
							'sub_name_en'=>$result['sub_name_en'],
							'maxScore'	=>$result['maxScore'],
							'multiSubject'=>$result['multiSubject'],
							'amount_subject'=>$result['amount_subject'],
							'innerSubject'=>0
						);
					
					$arrSub= array(
						'gradingId'=>$result['gradingTotalId'],
						'subjectId'=>$result['subject_id'],
						'studentId'=>$studentId,
						);
					$resultSubScore = $dbTrancript->getSubScoreList($arrSub);
					if(!empty($resultSubScore)){
						$scoreResultList[$key]['innerSubject']=count($resultSubScore);
						$scoreResultList[$key]['innerSubjectList']=$resultSubScore;
					}
				}
			}
			
			
			$arreValue= array(
				'studentId'=>$studentId,
				'groupId'=>$scoreInfo['group_id'],
				'forType'=>$scoreInfo['exam_type'],
				'forSemester'=>$scoreInfo['for_semester'],
				'forMonth'=>$scoreInfo['for_month'],
				);
			$resultEvalueAtion = $dbTrancript->getStudentAssessmentEvaluation($arreValue);
		
			//$scoreInfoFromAPI =  $this->getScoreInformation($_data);
			//$scoreInfoApi = empty($scoreInfoFromAPI["value"]) ? null : $scoreInfoFromAPI["value"];
			$result= array(
				'studentInfo'=>$studentInfo,
				'scoreInfo'=>$scoreInfoApi,
				'scoreSubjectInfo'=>$scoreResultList,
				'evaluationList'=>$resultEvalueAtion,
			);
				
			
			$returnResult = array(
					'status' =>true,
					'value' =>$result,
			);
			return $returnResult;
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	public function getTotalStudentAttendance($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$studentId = empty($search['studentId'])?0:$search['studentId'];
			$groupId = empty($search['groupId'])?0:$search['groupId'];
			
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			
			$label = "name_en";
			$colunmname = "title_en";
			$schooName = "school_nameen";
    		$branch = "branch_nameen";
			if($currentLang==1){// khmer
				$label = "name_kh";
				$schooName = "school_namekh";
				$branch = "branch_namekh";
				$colunmname='title';
			}
			
			$sql=" SELECT 
    				sat.*
					,(SELECT rms_items.$colunmname FROM rms_items WHERE rms_items.id=g.degree AND rms_items.type=1 LIMIT 1)AS degreeTitle
					,(SELECT rms_itemsdetail.$colunmname FROM rms_itemsdetail WHERE rms_itemsdetail.id=g.grade AND rms_itemsdetail.items_type=1 LIMIT 1)AS gradeTitle
					,(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS academicYearTitle
    				,g.group_code as groupCode
					
					,'0' AS totalComeSemester1
					,'0' AS totalASemester1
					,'0' AS totalPSemester1
					,'0' AS totalLSemester1
					,'0' AS totalELSemester1
					
					,'0' AS totalComeSemester2
					,'0' AS totalASemester2
					,'0' AS totalPSemester2
					,'0' AS totalLSemester2
					,'0' AS totalELSemester2
					
					,'0' AS gTotalCome
					,'0' AS gTotalA
					,'0' AS gTotalP
					,'0' AS gTotalL
					,'0' AS gTotalEL
					
					,sat.`date_attendence`
					,satd.description
	 		       
 			   FROM
					`rms_student_attendence` AS sat 
						JOIN `rms_student_attendence_detail` AS satd ON sat.`id`= satd.`attendence_id`
						LEFT JOIN `rms_group` AS g ON g.id = sat.`group_id`
				WHERE sat.type=1 AND sat.status=1
					";
			$sql.=" AND satd.stu_id = ".$studentId;
			$sql.=" AND sat.`group_id` = ".$groupId;
			
			$sql.= " GROUP BY satd.`stu_id` ";
		
			$rowAttendance = $db->fetchRow($sql);
			if(!empty($rowAttendance)){
				$arrFilter =array(
					"studentId" => $studentId,
					"group_id" => $groupId,
				);
				$arrFilter["forSemester"] = 1;
				$arrFilter["attendence_status"] = 2;
				$totalASemester1 = $this->getCountAttendance($arrFilter);
				$rowAttendance["totalASemester1"] = $totalASemester1;
				
				$arrFilter["attendence_status"] = 3;
				$totalPSemester1 = $this->getCountAttendance($arrFilter);
				$rowAttendance["totalPSemester1"] = $totalPSemester1;
				
				$arrFilter["attendence_status"] = 4;
				$totalLSemester1 = $this->getCountAttendance($arrFilter);
				$rowAttendance["totalLSemester1"] = $totalLSemester1;
				
				$arrFilter["attendence_status"] = 5;
				$totalELSemester1 = $this->getCountAttendance($arrFilter);
				$rowAttendance["totalELSemester1"] = $totalELSemester1;
				
				$arrFilter["forSemester"] = 2;
				$arrFilter["attendence_status"] = 2;
				$totalASemester2 = $this->getCountAttendance($arrFilter);
				$rowAttendance["totalASemester1"] = $totalASemester2;
				
				$arrFilter["attendence_status"] = 3;
				$totalPSemester2 = $this->getCountAttendance($arrFilter);
				$rowAttendance["totalPSemester2"] = $totalPSemester2;
				
				$arrFilter["attendence_status"] = 4;
				$totalLSemester2 = $this->getCountAttendance($arrFilter);
				$rowAttendance["totalLSemester2"] = $totalLSemester2;
				
				$arrFilter["attendence_status"] = 5;
				$totalELSemester2 = $this->getCountAttendance($arrFilter);
				$rowAttendance["totalELSemester2"] = $totalELSemester2;
				
				$rowAttendance["gTotalA"] = "".$totalASemester1+$totalASemester2."";
				$rowAttendance["gTotalP"] = "".$totalPSemester1+$totalPSemester2."";
				$rowAttendance["gTotalL"] = "".$totalLSemester1+$totalLSemester2."";
				$rowAttendance["gTotalEL"] = "".$totalELSemester1+$totalELSemester2."";
			}
			
			
			$sql=" SELECT 
    				sat.*
					,(SELECT rms_items.$colunmname FROM rms_items WHERE rms_items.id=g.degree AND rms_items.type=1 LIMIT 1)AS degreeTitle
					,(SELECT rms_itemsdetail.$colunmname FROM rms_itemsdetail WHERE rms_itemsdetail.id=g.grade AND rms_itemsdetail.items_type=1 LIMIT 1)AS gradeTitle
					,(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS academicYearTitle
    				,g.group_code as groupCode
					
					,COUNT(if(satd.attendence_status = '1' AND sat.for_semester=1, satd.attendence_status, NULL)) AS totalMinorSemester1
					,COUNT(IF(satd.attendence_status = '2' AND sat.for_semester=1, satd.attendence_status, NULL)) AS totalMediumSemester1
					,COUNT(IF(satd.attendence_status = '3' AND sat.for_semester=1, satd.attendence_status, NULL)) AS totalMajorSemester1
					,COUNT(IF(satd.attendence_status = '4' AND sat.for_semester=1, satd.attendence_status, NULL)) AS totalOtherSemester1
					
					,COUNT(IF(satd.attendence_status = '1' AND sat.for_semester=2, satd.attendence_status, NULL)) AS totalMinorSemester2
					,COUNT(IF(satd.attendence_status = '2' AND sat.for_semester=2, satd.attendence_status, NULL)) AS totalMediumSemester2
					,COUNT(IF(satd.attendence_status = '3' AND sat.for_semester=2, satd.attendence_status, NULL)) AS totalMajorSemester2
					,COUNT(IF(satd.attendence_status = '4' AND sat.for_semester=2, satd.attendence_status, NULL)) AS totalOtherSemester2
					
					,COUNT(IF(satd.attendence_status = '1', satd.attendence_status, NULL)) AS gTotalMinor
					,COUNT(IF(satd.attendence_status = '2', satd.attendence_status, NULL)) AS gTotalMedium
					,COUNT(IF(satd.attendence_status = '3', satd.attendence_status, NULL)) AS gTotalMajor
					,COUNT(IF(satd.attendence_status = '4', satd.attendence_status, NULL)) AS gTotalOther
					
					
					,sat.`date_attendence`
					,satd.description
	 		       
 			   FROM
					`rms_student_attendence` AS sat 
						JOIN `rms_student_attendence_detail` AS satd ON sat.`id`= satd.`attendence_id`
						LEFT JOIN `rms_group` AS g ON g.id = sat.`group_id`
				WHERE sat.type=2 AND sat.status=1
					";
			$sql.=" AND satd.stu_id = ".$studentId;
			$sql.=" AND sat.`group_id` = ".$groupId;
			$sql.= " GROUP BY satd.`stu_id` ";
			$rowMistake = $db->fetchRow($sql);
			
			
			$rowAttendance = empty($rowAttendance) ? null : $rowAttendance;
			$rowMistake = empty($rowMistake) ? null : $rowMistake;
			$row = array(
				"attendance" => $rowAttendance,
				"mistake" => $rowMistake,
			);
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	function getStudentRequestPermission($_data){
		$db = $this->getAdapter();
		try{
			
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$studentId = empty($_data['studentId'])?0:$_data['studentId'];
			
			
			$colunmName='title_en';
			$label = 'name_en';
			$teacherName = "teacher_name_en";
			$branch = "branch_nameen";
			
			$sessionTypeI = "Morning";
			$sessionTypeII = "Evening";
			$sessionTypeIII = "Full Day";
			if ($currentLang==1){
				$teacherName='teacher_name_kh';
				$colunmName='title';
				$label = 'name_kh';
				$branch = "branch_namekh";
				
				$sessionTypeI = "ព្រឹក";
				$sessionTypeII = "ពេលល្ងាច";
				$sessionTypeIII = "ពេញមួយថ្ងៃ";
			}
				
			$sql="
				SELECT 
					srq.*
					,b.$branch AS branchName
					,g.group_code AS  groupCode
					,(SELECT t.$teacherName FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherName
					,(SELECT t.teacher_name_kh FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherNameKh
					,(SELECT t.teacher_name_en FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teaccherNameEng
					,(SELECT t.signature FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherSigature
					,(SELECT t.tel FROM rms_teacher AS t WHERE t.id = g.teacher_id LIMIT 1) AS teacherTel
					,(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear
					,(SELECT rms_items.$colunmName FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degreeTitle
					,(SELECT rms_itemsdetail.$colunmName FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
					,(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS sessionTitle
					,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName
					,CASE
								WHEN srq.sessionType = 1 THEN '$sessionTypeI'
								WHEN srq.sessionType = 2 THEN '$sessionTypeII'
								WHEN srq.sessionType = 3 THEN '$sessionTypeIII'
								ELSE 'N/A'
						END as attendanceTypeTitle
					
					
				FROM `rms_student_request_permission` AS srq
					JOIN `rms_branch` AS b ON b.br_id = srq.branchId
					LEFT JOIN `rms_group` AS g ON g.id = srq.groupId
				WHERE srq.status = 1
					AND srq.studentId=$studentId
			";
			
			if(!empty($_data['academicYear'])){
				$sql.=" AND g.academic_year = ".$_data['academicYear'];	
			}
			if(!empty($_data['groupId'])){
				$sql.=" AND g.id = ".$_data['groupId'];	
			}
			if(!empty($_data['degreeId'])){
				$sql.=" AND g.degree = ".$_data['degreeId'];	
			}
			if(!empty($_data['requestStatus'])){
				$requestStatus=0;
				if($_data['requestStatus']==2){
					$requestStatus=1;
				}else if($_data['requestStatus']==3){
					$requestStatus=2;
				}
				$sql.=" AND srq.requestStatus = ".$requestStatus;	
			}
			$sql.=" ORDER BY srq.id DESC ";
			
			$limit=" ";
			if(!empty($search['LimitStart'])){
				$limit.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
			}else if(!empty($search['limitRecord'])){
	    		$limit.=" LIMIT ".$search['limitRecord'];
	    	}
			
			$row = $db->fetchAll($sql.$limit);
			
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	function submitStudentRequestPermission($_data){
    	$db = $this->getAdapter();
		$db->beginTransaction();
    	try{
			
			$format = 'Y-m-d';
			$dateString = $_data['fromDate'];
			$date = new DateTime($dateString);
			$fromDate = $date->format($format);
			
			$toDateString = $_data['toDate'];
			$toDate = new DateTime($toDateString);
			$toDate = $toDate->format($format);
			
			$_data['currentLang'] = empty($_data['currentLang'])?1:$_data['currentLang'];
			$currentLang 	= $_data['currentLang'];
			$studentId		= empty($_data['studentId'])?0:$_data['studentId'];
			$branchId		= empty($_data['branchId'])?0:$_data['branchId'];		
			$groupId		= empty($_data['groupId'])?0:$_data['groupId'];
			$_data['amountDay']		= empty($_data['amountDay'])?0:$_data['amountDay'];
			$_data['phoneNumber']	= empty($_data['phoneNumber'])?0:$_data['phoneNumber'];
			$_data['sessionType']	= empty($_data['sessionType'])?3:$_data['sessionType'];
			$_data['stu_id'] = $studentId;
			
			$studentInfo = $this->getStudentInformation($_data);
			if(!empty($studentInfo['value'][0])){
				$groupId  = empty($studentInfo['value'][0]['group_id'])?0:$studentInfo['value'][0]['group_id'];
				$branchId  = empty($studentInfo['value'][0]['branchId'])?0:$studentInfo['value'][0]['branchId'];
			}
			
	
			$arr = array(
				'branchId' 			=> $branchId,
				'groupId' 			=> $groupId,
				'studentId' 		=> $studentId,
				'amountDay' 		=> $_data['amountDay'],
				'fromDate' 			=> $fromDate,
				'toDate' 			=> $toDate,
				'reason' 			=> $_data['reason'],
				'phoneNumber' 		=> $_data['phoneNumber'],
				'sessionType' 		=> $_data['sessionType'],
				'requestStatus' 	=> 0,
				
				'createDate' 		=> date("Y-m-d H:i:s"),
				'modifyDate' 		=> date("Y-m-d H:i:s"),
				'status' 			=> 1,
				'inputFrom' 		=> $_data['inputFrom'],
			);
    		$this->_name='rms_student_request_permission';
    		$requestId = $this->insert($arr);
    			
			$db->commit();
			$result = array(
					'status' =>true,
					'value' =>$requestId,
			);
			return $result;
    		
    	}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
    	}
    }
	
	function editStudentRequestPermission($_data){
    	$db = $this->getAdapter();
		$db->beginTransaction();
    	try{
			
			$format = 'Y-m-d';
			$dateString = $_data['fromDate'];
			$date = new DateTime($dateString);
			$fromDate = $date->format($format);
			
			$toDateString = $_data['toDate'];
			$toDate = new DateTime($toDateString);
			$toDate = $toDate->format($format);
			
			$currentLang 	= empty($_data['currentLang'])?1:$_data['currentLang'];
			$studentId		= empty($_data['studentId'])?0:$_data['studentId'];
			$_data['amountDay']		= empty($_data['amountDay'])?0:$_data['amountDay'];
			$_data['phoneNumber']	= empty($_data['phoneNumber'])?0:$_data['phoneNumber'];
			$requestId	= empty($_data['recordId'])?0:$_data['recordId'];
			$_data['sessionType']	= empty($_data['sessionType'])?3:$_data['sessionType'];
			
			$arr = array(
				'studentId' 		=> $studentId,
				'amountDay' 		=> $_data['amountDay'],
				'fromDate' 			=> $fromDate,
				'toDate' 			=> $toDate,
				'reason' 			=> $_data['reason'],
				'phoneNumber' 		=> $_data['phoneNumber'],
				'sessionType' 		=> $_data['sessionType'],
				
				'modifyDate' 		=> date("Y-m-d H:i:s"),
				'status' 			=> $_data['status'],
				'inputFrom' 		=> $_data['inputFrom'],
			);
    		$this->_name='rms_student_request_permission';
			$where="id=".$requestId;
    		$this->update($arr, $where);
    			
			$db->commit();
			$result = array(
					'status' =>true,
					'value' =>$requestId,
			);
			return $result;
    		
    	}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
    	}
    }
	
	function getSchoolBusLogin($_data){
		$db = $this->getAdapter();
		$_data['userName']=trim($_data['userName']);
		$_data['password']=trim($_data['password']);
		try{
			$sql =" SELECT
					s.*
					,dri.teacher_name_en AS driverNameEng
					,dri.teacher_name_kh AS driverNameKh
					,dri.tel AS driverPhone
					,dri.user_name
					,dri.password
				FROM
					rms_school_bus AS s
					JOIN `rms_teacher` AS dri ON dri.id = s.driverId
				WHERE s.status = 1 ";
			$sql.= " AND ".$db->quoteInto('dri.user_name=?', $_data['userName']);
			$sql.= " AND ".$db->quoteInto('dri.password=?', md5($_data['password']));
			$row = $db->fetchRow($sql);
			$row = empty($row) ? null : $row;
			$result = array(
					'status' =>true,
					'value' =>$row,
			);
			return $result;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	function onlineOfflineSchoolBus($_data){
    	$db = $this->getAdapter();
		$db->beginTransaction();
    	try{
			
			
			$currentLang 	= empty($_data['currentLang'])?1:$_data['currentLang'];
			$busId		= empty($_data['userId'])?0:$_data['userId'];
			$_data['onlineStatus']		= empty($_data['onlineStatus'])?0:$_data['onlineStatus'];
			
			
			$arr = array(
				'onlineStatus' 		=> $_data['onlineStatus'],
				'modifyDate' 		=> date("Y-m-d H:i:s"),
			);
			
    		$this->_name='rms_school_bus';
			$where="id=".$busId;
    		$this->update($arr, $where);
    			
			$db->commit();
			$result = array(
					'status' =>true,
					'value' =>$busId,
			);
			return $result;
    		
    	}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
    	}
    }
	
	function checkCurrentTokenScoolBusAccount($_data){
		$db = $this->getAdapter();
		$mobileToken 	= empty($_data['mobileToken'])?"":$_data['mobileToken'];
		$tokenType 	= empty($_data['tokenType'])?"1":$_data['tokenType'];
		try{
			$sql =" SELECT
				mt.*
				,mt.stu_id as accountId
			FROM
				mobile_mobile_token AS mt
			WHERE 1 ";
			$sql.= " AND ".$db->quoteInto('mt.tokenType=?', $tokenType);
			$sql.= " AND ".$db->quoteInto('mt.token=?', $mobileToken);
			$row = $db->fetchRow($sql);
			$row = empty($row) ? null : $row;
			$result = array(
					'status' =>true,
					'value' =>$row,
			);
			return $result;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	function getSchoolBusProfile($_data){
		$db = $this->getAdapter();
		try{
			
			$currentLang 	= empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId		= empty($_data['userId'])?0:$_data['userId'];
			
			$branch = "branch_nameen";
			if ($currentLang==1){
				$branch = "branch_namekh";
			}		
			$sql =" 
				SELECT
					bus.*
					,b.$branch AS branchName
					
					,dri.teacher_name_en AS driverNameEng
					,dri.teacher_name_kh AS driverNameKh
					,dri.teacher_name_kh AS driverName
					,dri.tel AS driverPhone
	
				FROM
					rms_school_bus AS bus
					JOIN `rms_teacher` AS dri ON dri.id = bus.driverId
						LEFT JOIN `rms_branch` AS b ON b.br_id = bus.branchId
				WHERE bus.status = 1 and bus.id = $userId ";
		
			$sql.=" LIMIT 1 ";
			$row = $db->fetchRow($sql);
			$row = empty($row) ? array() : $row;
			$result = array(
					'status' =>true,
					'value' =>$row,
			);
			return $result;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	function getSchoolBusForStudent($_data){
		$db = $this->getAdapter();
		try{
			
			$currentLang 	= empty($_data['currentLang'])?1:$_data['currentLang'];
			$studentId		= empty($_data['studentId'])?0:$_data['studentId'];
			$studentId		= empty($studentId)? empty($_data['stu_id'])?0:$_data['stu_id'] :$studentId;
			
			
			$branch = "branch_nameen";
			if ($currentLang==1){
				$branch = "branch_namekh";
			}		
			$sql =" 
			
				SELECT
					bus.*
					,b.$branch AS branchName
					
					,dri.teacher_name_en AS driverNameEng
					,dri.teacher_name_kh AS driverNameKh
					,dri.teacher_name_kh AS driverName
					,dri.tel AS driverPhone
					
				FROM
					`rms_student_bus_schedule` AS busSch 
					
					
						LEFT JOIN (rms_school_bus AS bus LEFT JOIN `rms_teacher` AS dri ON dri.id = bus.driverId) ON bus.id = busSch.bus_id
						LEFT JOIN `rms_branch` AS b ON b.br_id = busSch.branch_id
				WHERE busSch.status = 1 
					AND busSch.student_id = $studentId
				";
				
			$sql.=" GROUP BY busSch.bus_id ";
			$sql.=" ORDER BY busSch.time ASC,busSch.type ASC ";
			$sql.="  LIMIT 1 ";
			$row = $db->fetchRow($sql);
			$row = empty($row) ? null : $row;
			$result = array(
					'status' =>true,
					'value' =>$row,
			);
			return $result;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}

	public function getInstructionArticle($_data){
		$db = $this->getAdapter();
		try{
			
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$keyName = empty($_data['keyName'])?"frontRegister":$_data['keyName'];
			
			$sql =" 
				SELECT
					ins.*
					,insD.title AS title
					,insD.description AS description
					,ins.video_id AS videoId
				FROM
					moble_instruction AS ins
					JOIN `moble_instruction_detail` AS insD ON ins.id = insD.instruction_id
				WHERE ins.status = 1 
					AND ins.keyName='".$keyName."' ";
				
			$sql.=" AND insd.lang = ".$currentLang;
			$sql.=" LIMIT 1 ";
			$row = $db->fetchRow($sql);
			$row = empty($row) ? null : $row;
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	function disableMyAccount($_data){
		$db = $this->getAdapter();
		$studentId = empty($_data['studentId'])?"0":$_data['studentId'];
		try{
			
			$arr = array(
				'isDisbleAccount'  => 1,
				'disableDate' 		 => date("Y-m-d H:i:s"),
				'disableValidDate' 		 => date("Y-m-d H:i:s",strtotime("+30 day")),
			);
			$where = 'stu_id = '. $studentId;
			$this->_name='rms_student';
			$this->update($arr, $where);
			return true;
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
		}
	}
	function enableMyAccount($_data){
		$db = $this->getAdapter();
		$studentId = empty($_data['studentId'])?"0":$_data['studentId'];
		try{
			
			$arr = array(
				'isDisbleAccount'  => 0,
			);
			$where = 'stu_id = '. $studentId;
			$this->_name='rms_student';
			$this->update($arr, $where);
			return true;
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
		}
	}
	
	function getSchoolBusSchedule($_data){
		$db = $this->getAdapter();
		try{
			
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId = empty($_data['userId'])?0:$_data['userId'];
			
			$valueSessionI='Morning';
			$valueSessionII='Afternoon';
			$valueSessionIII='Evening';
			$typeITitle='Take Student to school';
			$typeIITitle='Take Student come back home';
			
			$zoneName='Zone A';
			$colunmName='title_en';
			$label = 'name_en';
			if ($currentLang==1){
				$colunmName='title';
				$label = 'name_kh';
				
				$valueSessionI='ព្រឹក';
				$valueSessionII='រសៀល';
				$valueSessionIII='ល្ងាច';
				
				$typeITitle='ទៅយកសិស្សពីផ្ទះទៅសាលា';
				$typeIITitle='ជូនសិស្សចេញពីសាលាទៅផ្ទះ';
				
				$zoneName='តំបន់ A';
			}
				
			$sql="
				SELECT 
					sbus.*
					,CASE
								WHEN sBus.time = 1 THEN '$valueSessionI'
								WHEN sBus.time = 2 THEN '$valueSessionII'
								WHEN sBus.time = 3 THEN '$valueSessionIII'
								ELSE 'N/A'
						END as forSessionTitle
					,CASE
							WHEN sBus.type = 1 THEN '$typeITitle'
							WHEN sBus.type = 2 THEN '$typeIITitle'
							ELSE 'N/A'
					END AS transportTypeTitle
					
					,'$zoneName' AS zoneName
					,COUNT(sBus.student_id) AS amountStudent
					
				FROM
					rms_student_bus_schedule AS sBus
					
				WHERE sBus.status = 1 
					AND sBus.bus_id=$userId
			";
			
			$sql.=" Group BY sbus.time,sbus.type";	
			$sql.=" ORDER BY sbus.time ASC,sbus.type ASC";	
			
			$row = $db->fetchAll($sql);
			
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	function getAllStudentListForSchoolBus($_data){
		$db = $this->getAdapter();
		try{
			
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$userId = empty($_data['userId'])?0:$_data['userId'];
			
			
			$valueSessionI='Morning';
			$valueSessionII='Afternoon';
			$valueSessionIII='Evening';
			$typeITitle='Take Student to school';
			$typeIITitle='Take Student come back home';
			
			$colunmName='title_en';
			$label = 'name_en';
			if ($currentLang==1){
				$colunmName='title';
				$label = 'name_kh';
				
				$valueSessionI='ព្រឹក';
				$valueSessionII='រសៀល';
				$valueSessionIII='ល្ងាច';
				
				$typeITitle='ទៅយកសិស្សពីផ្ទះទៅសាលា';
				$typeIITitle='ជូនសិស្សចេញពីសាលាទៅផ្ទះ';
			}
			$sql="
				SELECT 
					busSch.*
					,busSch.branch_id AS branchId
					,CASE
								WHEN busSch.time = 1 THEN '$valueSessionI'
								WHEN busSch.time = 2 THEN '$valueSessionII'
								WHEN busSch.time = 3 THEN '$valueSessionIII'
								ELSE 'N/A'
						END as forSessionTitle
					,CASE
							WHEN busSch.type = 1 THEN '$typeITitle'
							WHEN busSch.type = 2 THEN '$typeIITitle'
							ELSE 'N/A'
					END AS transportTypeTitle
					,busSch.student_id AS studentId
					,s.stu_code AS  studentCode
					,s.stu_khname AS  studentKhName
					,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS studentLatinName
					,(SELECT v.$label FROM rms_view AS v where v.type=2 and v.key_code=s.sex LIMIT 1) AS genderTitle
					,g.group_code AS  groupCode
					,busSch.bus_id AS busId
					,busSch.time AS forSession
			";
			$sql.=" FROM `rms_student_bus_schedule` AS busSch 
						LEFT JOIN `rms_school_bus` AS bus ON bus.id =busSch.bus_id 
						LEFT JOIN (`rms_student` AS s JOIN `rms_group_detail_student` AS gds ON  gds.itemType=1 AND gds.stu_id = s.stu_id AND gds.is_current=1 AND gds.is_maingrade=1 ) ON s.stu_id =busSch.student_id
						LEFT JOIN `rms_group` AS g ON g.id =gds.group_id 
					";	
			$sql.=" WHERE busSch.status=1 
					AND busSch.bus_id  =$userId ";	
			$sql.=" ORDER BY busSch.time ASC,busSch.type ASC ";	

			$row = $db->fetchAll($sql);
			
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	
	function getStudentAchievement($_data){
		$db = $this->getAdapter();
		try{
			
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$studentId = empty($_data['studentId'])?0:$_data['studentId'];
			
			
			$colunmName='title_en';
			$label = 'name_en';
			$teacherName = "teacher_name_en";
			$branch = "branch_nameen";
			
			if ($currentLang==1){
				$teacherName='teacher_name_kh';
				$colunmName='title';
				$label = 'name_kh';
				$branch = "branch_namekh";
			}
				
			$sql="
				SELECT 
					ac.*
					,b.$branch AS branchName
					,g.group_code AS  groupCode
					,g.degree AS  degreeId
					,(SELECT CONCAT(acad.fromYear,'-',acad.toYear) FROM rms_academicyear AS acad WHERE acad.id=g.academic_year LIMIT 1) AS academicYear
					,(SELECT rms_items.$colunmName FROM `rms_items` WHERE rms_items.`id`=`g`.`degree` AND rms_items.type=1 LIMIT 1) AS degreeTitle
					,(SELECT rms_itemsdetail.$colunmName FROM `rms_itemsdetail` WHERE rms_itemsdetail.`id`=`g`.`grade` AND rms_itemsdetail.items_type=1 LIMIT 1) AS gradeTitle
					,(SELECT $label FROM rms_view WHERE `type`=4 AND rms_view.key_code= `g`.`session` LIMIT 1) AS sessionTitle
					,(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS roomName
					,s.stu_code AS stuCode
					,s.stu_khname AS stuNameKH
					,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stuNameEng
					,s.photo
					
				FROM 
					`rms_student_achievement` AS ac JOIN rms_student AS s ON s.stu_id = ac.studentId 
					LEFT JOIN `rms_group` AS g ON g.id = ac.groupId
					LEFT JOIN `rms_branch` AS b ON b.br_id = ac.branchId
				WHERE ac.status = 1
					AND ac.studentId=$studentId
			";
			
			if(!empty($_data['academicYear'])){
				$sql.=" AND g.academic_year = ".$_data['academicYear'];	
			}
			if(!empty($_data['groupId'])){
				$sql.=" AND g.id = ".$_data['groupId'];	
			}
			if(!empty($_data['degreeId'])){
				$sql.=" AND g.degree = ".$_data['degreeId'];	
			}
			
			$sql.=" ORDER BY ac.id DESC ";
			
			$limit=" ";
			if(!empty($search['LimitStart'])){
				$limit.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
			}else if(!empty($search['limitRecord'])){
	    		$limit.=" LIMIT ".$search['limitRecord'];
	    	}
			
			$row = $db->fetchAll($sql.$limit);
			
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	function getSpecialFeature($_data){
		$db = $this->getAdapter();
		try{
			$sql="
				SELECT 
					sf.*
				FROM 
					`mobile_special_feature` AS sf
				WHERE sf.status = 1
			";
			$sql.=" ORDER BY sf.ordering ASC ";
			$row = $db->fetchAll($sql);
			
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	public function getStudentCriteriaScore($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$studentId = empty($search['studentId'])?0:$search['studentId'];
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			
			$teacherName= "teacher_name_en";
			$subjectTitle = "subject_titleen";
			$criteriaTitle = "title_en";
			
			if($currentLang==1){// khmer
				$teacherName = "teacher_name_kh";
				$subjectTitle = "subject_titlekh";
				$criteriaTitle = "title";
			}
			$sql="SELECT
					grdTmpD.`studentId`
					,grdTmp.`criteriaId`
					
					,g.`group_code` AS groupCode
					,(SELECT CONCAT(COALESCE(ac.fromYear),'-',COALESCE(ac.toYear)) FROM rms_academicyear AS ac WHERE ac.id=g.academic_year LIMIT 1) AS academicYearTitle
					,(SELECT subj.$subjectTitle FROM `rms_subject` AS subj WHERE subj.id = grdTmp.`subjectId` LIMIT 1) AS subjectTitle
					,(SELECT cri.$criteriaTitle FROM `rms_exametypeeng` AS cri WHERE cri.id = grdTmp.`criteriaId` LIMIT 1) AS criteriaTitle
					
					,grdTmpD.`totalGrading`
					,grdTmpD.`subCriterialTitleEng`
					,grdTmpD.`subCriterialTitleKh`
					,(SELECT sEnT.`title` FROM `rms_score_entry_setting` AS sEnT WHERE sEnT.id = grdTmp.`settingEntryId` LIMIT 1 ) AS entrySettingTitle
					,grdTmp.*
					,(SELECT t.$teacherName  FROM `rms_teacher` AS t WHERE t.id =grdTmp.`teacherId` LIMIT 1) AS teacherName
					
			FROM
				`rms_grading_tmp` AS grdTmp 
				JOIN `rms_grading_detail_tmp` AS grdTmpD ON grdTmp.`id` = grdTmpD.`gradingId`
				LEFT JOIN `rms_group` AS g ON g.id = grdTmp.groupId
			WHERE 1
				AND grdTmpD.`studentId` = $studentId
			";
			
			$where='';
			if(!empty($search['searchBox'])){
				$s_where=array();
				$s_search=addslashes(trim($search['searchBox']));
				$s_search = str_replace(' ', '', addslashes(trim($search['searchBox'])));
				$s_where[]= " REPLACE(g.group_code,' ','') LIKE '%{$s_search}%'";
				$s_where[]= " REPLACE((SELECT sEnT.`title` FROM `rms_score_entry_setting` AS sEnT WHERE sEnT.id = grdTmp.`settingEntryId` LIMIT 1 ),' ','') LIKE '%{$s_search}%'";
				$where.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(!empty($search['academicYear'])){
				$where.=" AND g.academic_year = ".$search['academicYear'];
			}
			if(!empty($search['groupId'])){
				$where.=" AND g.id = ".$search['groupId'];
			}
			if(!empty($search['forSemester'])){
				$where.=" AND grdTmp.forSemester = ".$search['forSemester'];
			}
			if(!empty($search['examType'])){
				$where.=" AND grdTmp.examType=".$search['examType'];
				if($search['examType']==1){
					if(!empty($search['month'])){
						$where.=" AND grdTmp.forMonth=".$search['month'];
					}	
				}
			}
			if(!empty($search['criteriaId'])){
				$where.=" AND grdTmp.`criteriaId` = ".$search['criteriaId'];
			}
			
			$ordering=" ORDER BY grdTmp.createDate DESC, grdTmp.`id` DESC ";
			$limit=" ";
			if(!empty($search['LimitStart'])){
				$limit.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
			}else if(!empty($search['limitRecord'])){
				$limit.=" LIMIT ".$search['limitRecord'];
			}
			$row = $db->fetchAll($sql.$where.$ordering.$limit);
			$result = array(
				'status' =>true,
				'value' =>$row,
			);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
}