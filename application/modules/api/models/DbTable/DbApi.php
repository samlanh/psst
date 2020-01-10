<?php

class Api_Model_DbTable_DbApi extends Zend_Db_Table_Abstract
{
	function getStudentLogin($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
	
			$sql ="
			SELECT
				s.*
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
			if ($currentLang==1){
				$colunmname='title';
				$lbView="name_kh";
				$branch = "branch_namekh";
				$schoolName = "school_namekh";
		   
				$province = "province_kh_name";
				$commune = "commune_namekh";
				$district = "district_namekh";
			}
	
			$sql ="SELECT
			s.*,
			(SELECT b.$branch FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branchName,
			(SELECT b.photo FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branchLogo,
			(SELECT b.branch_tel FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branchTel,
			(SELECT b.branch_tel1 FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branchTel1,
			(SELECT b.br_address FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS brAddress,
			(SELECT b.$schoolName FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS schoolName,
			(SELECT b.website FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS website,
			(SELECT b.email FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS email,
			 
			CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS name_englsih,
			(select $lbView from rms_view where type=2 and key_code=s.sex LIMIT 1) as genderTitle,
			 
			(SELECT $province FROM rms_province AS p WHERE p.province_id=s.province_id LIMIT 1) AS provinceTitle,
			(SELECT $commune FROM ln_commune AS p WHERE p.com_id=s.commune_name LIMIT 1) AS communeTitle,
			(SELECT $district FROM ln_district AS p WHERE p.dis_id=s.district_name LIMIT 1) AS districtTitle,
			 
			(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.father_job LIMIT 1) AS fatherOccupation,
			(SELECT occu_name FROM rms_occupation WHERE occupation_id=s.mother_job LIMIT 1) AS motherOccupation,
			 
			(SELECT rms_items.$colunmname FROM rms_items WHERE rms_items.id=g.degree AND rms_items.type=1 LIMIT 1)AS degreeTitle,
			 
			(SELECT rms_itemsdetail.$colunmname FROM rms_itemsdetail WHERE rms_itemsdetail.id=g.grade AND rms_itemsdetail.items_type=1 LIMIT 1)AS gradeTitle,
			CONCAT(t.from_academic,' - ',t.to_academic) as academic_year,
			t.generation
			FROM
			rms_student as s,
			rms_group as g,
			rms_group_detail_student as gds,
			rms_tuitionfee as t
			WHERE
			s.stu_id = gds.stu_id
			and g.id=gds.group_id
			and g.academic_year=t.id
			and s.stu_id=$stu_id";
			$row = $_db->fetchRow($sql);
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
	    		DATE_FORMAT(sp.create_date, '%d-%m-%Y %H:%i') AS  createDate,
	    		sp.is_void,
	    		(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE `status`=1 AND id=sp.academic_year LIMIT 1) AS year,
	    		(SELECT $lbView FROM rms_view WHERE type=10 AND key_code=sp.is_void LIMIT 1) AS voidStatus,
	    		
	    		FORMAT(sp.grand_total,2)  AS totalPayment,
	    		FORMAT(sp.credit_memo,2)  AS creditMemo,
	    		FORMAT(sp.penalty,2)  AS penalty,
	    		FORMAT(sp.paid_amount,2)  AS paidAmount,
	    		FORMAT(sp.balance_due,2)  AS balanceDue,
	    		(SELECT $lbView FROM rms_view WHERE type=8 AND key_code=sp.payment_method LIMIT 1) AS paymentMethod,
	    		(SELECT rms_users.first_name FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS byUser
    		FROM
	    		rms_student_payment AS sp
    		WHERE sp.student_id = ".$search['stu_id'];
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
    
    public function getPaymentDetail($payment_id,$currentLang=1){
    	$db = $this->getAdapter();
		$db->beginTransaction();
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
		    	FORMAT(spd.discount_amount,2)  AS discountAmount,
		    	FORMAT(spd.discount_percent,2)  AS discountPercent,
		    	
		    	DATE_FORMAT(spd.start_date, '%d-%m-%Y %H:%i') AS  startDate,
		    	DATE_FORMAT(spd.validate, '%d-%m-%Y %H:%i') AS  validate,
		    	(SELECT dis_name FROM `rms_discount` WHERE disco_id=spd.discount_type LIMIT 1) AS discount_type,
		    	spd.service_type,
		    	spd.note,
		    	(SELECT $grade FROM `rms_itemsdetail` WHERE id=spd.itemdetail_id LIMIT 1) AS serviceTitle,
		    	(SELECT items_type FROM `rms_itemsdetail` WHERE id=spd.itemdetail_id LIMIT 1) AS items_type,
		    	(SELECT $label FROM `rms_view` WHERE  `type`=6 AND key_code= spd.payment_term LIMIT 1) AS payment_term,
		    	(SELECT $grade FROM rms_itemsdetail WHERE rms_itemsdetail.id=sp.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
		    	(SELECT first_name FROM `rms_users` WHERE `rms_users`.id = sp.user_id LIMIT 1) AS byUser
	    	FROM
		    	rms_student_payment as sp,
		    	rms_student_paymentdetail AS spd ";
		    	$sql.='WHERE sp.id=spd.payment_id
		    	AND spd.payment_id = '.$payment_id;
		    	
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
    
    
}