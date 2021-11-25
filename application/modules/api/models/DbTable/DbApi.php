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
				s.stu_id AS id,
				s.stu_code AS stuCode,
				s.stu_khname AS stuNameKH,
				s.stu_enname AS stuFirstName,
				s.last_name AS stuLastName,
				s.photo
			FROM
				rms_student AS s
			WHERE s.status = 1 AND s.customer_type =1 ";
			$sql.= " AND ".$db->quoteInto('s.stu_code=?', $_data['studentCode']);
			$sql.= " AND ".$db->quoteInto('s.password=?', md5($_data['password']));
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
		$db->beginTransaction();
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
	function getStudentInformation($stu_id=0,$currentLang=1){
		$_db = $this->getAdapter();
		$_db->beginTransaction();
		try{
			$colunmname='title_en';
			$lbView="name_en";
			$branch = "branch_nameen";
			$schoolName = "school_nameen";
	
			$province = "province_en_name";
			$commune = "commune_name";
			$district = "district_name";
			$vill = 'village_name';
			if ($currentLang==1){
				$colunmname='title';
				$lbView="name_kh";
				$branch = "branch_namekh";
				$schoolName = "school_namekh";
		   
				$province = "province_kh_name";
				$commune = "commune_namekh";
				$district = "district_namekh";
				$vill = 'village_namekh';
			}
	//s.stu_khname,
			$sql ="SELECT
						s.stu_id,
						s.stu_code,
						s.stu_khname,
						s.tel,
						CASE
								WHEN primary_phone = 1 THEN s.tel
								WHEN primary_phone = 2 THEN s.father_phone
								WHEN primary_phone = 3 THEN s.mother_phone
								ELSE s.guardian_tel
						END as PrimaryContact,
						COALESCE(DATE_FORMAT(s.dob, '%d-%m-%Y'),'') AS dob,
						s.pob,
						s.home_num,
						s.street_num,
						s.father_enname,
						s.father_phone,
						COALESCE(DATE_FORMAT(s.father_dob, '%d-%m-%Y'),'') AS father_dob,
						s.mother_enname,
						COALESCE(DATE_FORMAT(s.mother_dob, '%d-%m-%Y'),'') AS mother_dob,
						s.mother_phone,
						s.guardian_enname,
						s.guardian_tel,
						COALESCE(DATE_FORMAT(s.guardian_dob, '%d-%m-%Y'),'') AS guardian_dob,
						s.photo,
						gds.group_id,
						CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS name_englsih,
						(SELECT $lbView from rms_view where type=2 and key_code=s.sex LIMIT 1) as genderTitle,
						(SELECT $lbView FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality,
						(SELECT $lbView FROM rms_view where type=21 and key_code=s.father_nation LIMIT 1) AS fatherNation,
		    			(SELECT $lbView FROM rms_view where type=21 and key_code=s.mother_nation LIMIT 1) AS motherNation,
		    			(SELECT $lbView FROM rms_view where type=21 and key_code=s.guardian_nation LIMIT 1) AS guardianNation,
						 
						(SELECT $province FROM rms_province AS p WHERE p.province_id=s.province_id LIMIT 1) AS provinceTitle,
						(SELECT $district FROM ln_district AS p WHERE p.dis_id=s.district_name LIMIT 1) AS districtTitle,
						(SELECT $commune FROM ln_commune AS p WHERE p.com_id=s.commune_name LIMIT 1) AS communeTitle,
						(SELECT $vill FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name,
						 
						(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.father_job LIMIT 1) AS fatherOccupation,
						(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.mother_job LIMIT 1) AS motherOccupation,
						(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.guardian_job LIMIT 1) AS guardian_job,
						(SELECT rms_items.$colunmname FROM rms_items WHERE rms_items.id=g.degree AND rms_items.type=1 LIMIT 1)AS degreeTitle,
						(SELECT rms_itemsdetail.$colunmname FROM rms_itemsdetail WHERE rms_itemsdetail.id=g.grade AND rms_itemsdetail.items_type=1 LIMIT 1)AS gradeTitle,
						(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS academicYearTitle
						
			FROM
				rms_student as s,
				rms_group as g,
				rms_group_detail_student as gds
			WHERE
				s.stu_id = gds.stu_id
				AND s.stu_id=$stu_id ";
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
    function getSchedule($stu_id, $search = array()){
    	$db = $this->getAdapter();
    	try{
    		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    		$dayId = empty($search['dayId'])?1:$search['dayId'];
    		$stuInfo = $this->getStudentInformation($stu_id,$currentLang);
//     		$dayStudy = $this->getDaySchedule($stuInfo,$search,$currentLang);
//     		$timeStudy = $this->getTimeSchelduleByYGS($stuInfo,$search,$currentLang);
    		
    		$arrStudyValue = array();
    		$dayIndex="";
    		$arrStudyValue = $this->getSubjectTeacherByScheduleAndGroup($stuInfo,$dayId ,$currentLang);
//     		if (!empty($dayStudy)){
//     			foreach ($dayStudy as $key => $days){
//     				if (!empty($timeStudy)){
//     					foreach($timeStudy As $keyIndex => $time){
//     						$arrStudyValue[$days['name']][$keyIndex] = $this->getSubjectTeacherByScheduleAndGroup($stuInfo,$time['times'], $days['id'],$currentLang);
// 	    				}
// 	    			}
// 	    		}
//     		}
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
    		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    		$stu_id = empty($search['stu_id'])?1:$search['stu_id'];
    		$stuInfo = $this->getStudentInformation($stu_id,$currentLang);
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
			    	AND s.type_score=1
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
// 	function getSubjectByGroup($stuInfo,$search = array()){
//     	$db=$this->getAdapter();
//     	try{
//     		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
//     		$subjectTitle = "subject_titleen";
//     		if($currentLang==1){// khmer
//     			$subjectTitle = "subject_titlekh";
//     		}
//     		$examType = empty($search['exam_type'])?0:$search['exam_type'];
//     		$groupId = empty($stuInfo['value'][0]['group_id'])?0:$stuInfo['value'][0]['group_id'];
//     		$stuId = empty($stuInfo['value'][0]['stu_id'])?0:$stuInfo['value'][0]['stu_id'];
//     		$sql="
//     		SELECT
// 	    		gsjd.*,
// 	    		g.academic_year AS academicYearId,
// 	    		g.degree AS degreeId,
// 	    		g.grade AS gradeId,
// 	    		g.amount_subject AS amount_subjectdivide,
// 	    		gsjd.max_score AS max_subjectscore,
// 	    		gsjd.score_short as cut_score,
// 	    		(SELECT sj.parent FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS parent,
	    		
// 	    		(SELECT CONCAT(sj.$subjectTitle) FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS subjecTitle,
	    		
// 	    		(SELECT sj.is_parent FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS is_parent,
// 	    		(SELECT sj.shortcut FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS shortcut,
// 	    		(gsjd.amount_subject) amtsubject_month,
// 	    		(gsjd.amount_subject_sem) amtsubject_semester,
// 	    		(SELECT sj.subject_titleen FROM `rms_subject` AS sj WHERE sj.id = gsjd.subject_id LIMIT 1) AS subject_titleen,
// 	    		(SELECT dsd.score_in_class from rms_dept_subject_detail as dsd where dsd.dept_id = g.degree and dsd.subject_id = gsjd.subject_id LIMIT 1) as max_score
//     		FROM
// 	    		rms_group_subject_detail AS gsjd ,
// 	    		rms_group as g
//     		WHERE
//     		g.id = gsjd.group_id
//     		and gsjd.group_id = ".$groupId;
//     		if($examType==1){//for month
//     			$sql.=" AND gsjd.amount_subject > 0 ";
//     		}else{//for semester
//     			$sql.=" AND gsjd.amount_subject_sem > 0 ";
//     		}
//     		$sql.=' ORDER BY gsjd.id ASC ';
//     		return $db->fetchAll($sql);
//     	}catch(Exception $e){
//     		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
//     		$result = array(
//     				'status' =>false,
//     				'value' =>$e->getMessage(),
//     		);
//     		return $result;
//     	}
//     }
// 	public function getScoreBySubject($score_id,$student_id,$subject_id,$currentLang){
//     	$db = $this->getAdapter();
//     	$subjectTitle = "subject_titleen";
//     	if($currentLang==1){// khmer
//     		$subjectTitle = "subject_titlekh";
//     	}
//     	$sql="SELECT
// 	    	sd.`score`,
// 	    	sd.score_cut,
// 	    	sd.`subject_id`,
// 	    	(SELECT CONCAT(sj.$subjectTitle) FROM `rms_subject` AS sj WHERE sj.id = sd.`subject_id` LIMIT 1) AS subjecTitle
// 	    	,sd.amount_subject
// 	    	FROM  `rms_score_detail` AS sd
// 	    	WHERE sd.`score_id`=$score_id AND sd.`student_id`=$student_id  AND sd.`subject_id`=$subject_id ";
//     	return $db->fetchRow($sql);
//     }
//     function getRankSubjectMonthlyExam($group_id,$stu_id,$subject_id,$formonth){
//     	$db = $this->getAdapter();
//     	$sql="
//     	SELECT
// 	    	score,
// 	    	SUM(score) AS total_score,
// 	    	FIND_IN_SET( score, (
// 	    	SELECT GROUP_CONCAT( score
// 	    	ORDER BY score DESC )
// 	    	FROM rms_score_detail AS dd ,rms_score AS ss WHERE
// 	    	ss.`id`=dd.`score_id`
// 	    	AND ss.exam_type=1
// 	    	AND ss.group_id= $group_id
// 	    	AND dd.subject_id=$subject_id
// 	    	AND dd.`is_parent`=1
// 	    	AND ss.for_month=$formonth
// 	    	)
// 	    	) AS rank
//     	FROM
// 	    	`rms_score` AS s,
// 	    	`rms_score_detail` AS sd,
// 	    	`rms_group` AS g
//     	WHERE s.`id`=sd.`score_id`
// 	    	AND g.`id`=s.`group_id`
// 	    	AND sd.`is_parent`=1
// 	    	AND s.status = 1
// 	    	AND s.type_score=1
// 	    	AND s.exam_type=1
// 	    	AND g.id= $group_id
// 	    	AND sd.subject_id= $subject_id
// 	    	AND sd.student_id= $stu_id
// 	    	AND s.for_month=$formonth
//     	ORDER BY s.id DESC
//     	";
//     	return $db->fetchRow($sql);
//     }
    
    function getAttendenceBydate($search = array()){
    	$db = $this->getAdapter();
    	try{
    		$stuId = $search['stu_id'];
    		$LangId = $search['currentLang'];
    		
    		$stuInfo = $this->getStudentInformation($stuId,$LangId);
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
    		$stuId = $search['stu_id'];
    		$LangId = $search['currentLang'];
    		$stuInfo = $this->getStudentInformation($stuId,$LangId);
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
	    	$summary_arr = array('acarYear'=>$academicYear,'className'=>$className,'Minor'=>$minor,"MODERATE"=>$moderate,"MAJOR"=>$major,"OTHER"=>$other,"TOTALAMT"=>$amt);
	    	
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
    		$this->_name = "mobile_mobile_token";
    		
    		$token = $row['mobileToken'];
    		
    		$sql="SELECT id FROM mobile_mobile_token WHERE token='".$token."' LIMIT 1";
    		$rsid = $db->fetchOne($sql);
    		if(!empty($rsid)){
    			$_arr =array(
    					'stu_id' 	=> $row['id'],
    					'date' 		=> date("Y-m-d H:i:s"),
    					'device_type' => $row['deviceType'],
    					'device_model' 		=> "",
    			);
    			$where ='id= '.$rsid;
    			$this->update($_arr, $where);
    		}else{
	    		$sql="SELECT id FROM mobile_mobile_token WHERE stu_id=".$row['id']." AND token='".$token."' LIMIT 1";
	    		$rs = $db->fetchOne($sql);
	    		if(empty($rs)){
	    			$_arr =array(
	    				'stu_id' 	=> $row['id'],
	    				'token' 	=> $token,
	    				'date' 		=> date("Y-m-d H:i:s"),
	    				'device_type' => $row['deviceType'],
	    				'device_model' 	> "",
	    			);
	    			$this->insert($_arr);
	    		}
    		}
    		
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
			    	DATE_FORMAT(act.`publish_date`, '%d-%m-%Y') AS publish_date,
			    	act.image_feature,
			    	(SELECT u.first_name FROM `rms_users` AS u WHERE u.id = act.`user_id` LIMIT 1) AS user_name,
			    	CASE
					   	WHEN  act.`status` = 1 THEN '$base_url'
					  END AS imageUrl
		    	";
		    	$sql.=" FROM `mobile_news_event` AS act WHERE act.`status` =1 ";
		    	$sql_order= "  ORDER BY act.publish_date DESC,act.`id` DESC";
		    	
		    	if (!empty($search['limit'])){
		    		$sql.= "  LIMIT ".$search['limit'];
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
    		
    		$rowabout = $db->fetchAll($sql);
    		
    		$sql=" SELECT
    		l.*,
    		ld.title,ld.description
    		FROM `mobile_location` AS l,
    		`mobile_location_detail` AS ld
    		WHERE l.id=ld.location_id
    		AND ld.lang= $currentLang ";
    		
    		$rowcontact = $db->fetchAll($sql);
    		
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
    	WHERE s.`id`=sd.`score_id`
    	AND vst.stu_id = sm.`student_id`
    	AND vst.stu_id = sd.`student_id`
    	AND vst.stu_id = gds.`stu_id`
    	AND s.group_id = gds.`group_id`
    
    	AND s.`id`=sm.`score_id`
    	AND s.status = 1
    	AND s.type_score=1
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
		    		DATE_FORMAT(act.`publish_date`, '%d-%m-%Y') AS publish_date,
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
    			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
		    	$title="title";
		    	
		    	if ($currentLang==2){
		    		$title="title_en";
		    	}
		    	
				$month = date('m',strtotime($search['mothHoliday']));
				$year_month = date('Y-m',strtotime($search['mothHoliday']));
    			 
    			 $sql="SELECT mc.$title AS holiday_name,
							  DATE_FORMAT(mc.date, '%d') AS holiday_day,
							  DATE_FORMAT(mc.date, '%a') AS holiday_string,
							  DATE_FORMAT(mc.date, '%m') AS holiday_month
					   FROM `mobile_calendar` AS mc 
						WHERE 
							mc.`active` =1 
							AND (( mc.`type_holiday` =1  AND DATE_FORMAT(mc.date, '%m')= ".$month.") 
    			 				OR  (mc.`type_holiday` =2  AND DATE_FORMAT(mc.date, '%Y-%m')='".$year_month."'))";
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
    	function addAppTokenId($_data){
    		$db = $this->getAdapter();
    		try{
    			$this->_name='mobile_mobile_token';
    			$array = array(
    				'token'	=>$_data['token'],
    				'device_type'=>1,
    				'date'	=>date('Y-m-d'),
    			);
    			return $this->insert($array);
    		}catch(Exception $e){
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			return false;
    		}
    	}
    	
    	
    	
    	public function getStundetScoreResult($id=null){ // សម្រាប់លទ្ធផលប្រចាំខែ មិនលម្អិត
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
		    		AND s.type_score=1
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
}