<?php

class Api_Model_DbTable_DbApi extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getDailyReport($search=null){
    	$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$colunmname='title_en';
			$lbView="name_en";
			$branch = "branch_nameen";
			$schoolName = "school_nameen";
			
			if ($currentLang==1){
				$colunmname='title';
				$lbView="name_kh";
				$branch = "branch_namekh";
				$schoolName = "school_namekh";
				 
			}
			
    		$from_date =(empty($search['start_date']))? '1': "sp.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "sp.create_date <= '".$search['end_date']." 23:59:59'";
    		$sql=" 
    		SELECT
	    		s.stu_id,
	    		sp.id,
	    		sp.receipt_number,
	    		s.stu_code,
	    		s.stu_khname,
	    		s.stu_enname,
	    		(SELECT $colunmname FROM `rms_items` WHERE rms_items.id=sp.degree LIMIT 1 ) AS degreeTitle,
	    		(SELECT $colunmname FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=sp.grade LIMIT 1) AS gradeTile,
	    		(SELECT $lbView FROM rms_view WHERE rms_view.type = 4 AND key_code=sp.session LIMIT 1) AS sessionTitle,
	    		
	    		DATE_FORMAT(sp.create_date, '%d-%m-%Y') AS  createDate,
	    		sp.is_void,
	    		(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE `status`=1 AND id=sp.academic_year LIMIT 1) AS year,
	    		(SELECT first_name FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS user_id,
	    		(SELECT $lbView FROM rms_view WHERE type=10 AND key_code=sp.is_void LIMIT 1) AS voidStatus,
	    		
	    		FORMAT(sp.grand_total,2)  AS totalPayment,
	    		FORMAT(sp.grand_total,2)  AS grandTotal,
	    		FORMAT(sp.credit_memo,2)  AS creditMemo,
	    		FORMAT(sp.penalty,2)  AS penalty,
	    		FORMAT(sp.paid_amount,2)  AS paidAmount,
	    		FORMAT(sp.balance_due,2)  AS balanceDue,
	    		
	    		sp.note,
	    		sp.is_closed,
	    		sp.payment_method,
	    		(SELECT first_name FROM rms_users WHERE rms_users.id = sp.void_by LIMIT 1) AS void_by
    		FROM
	    		rms_student AS s,
	    		rms_student_payment AS sp
    		WHERE
    		s.stu_id = sp.student_id ";
	    	if (!empty($search['stu_id'])){
	    		$sql.=" AND s.stu_id = ".$search['stu_id'];
	    	}
    		$where = " AND ".$from_date." AND ".$to_date;
    		if(!empty($search['adv_search'])){
    			$s_where=array();
    			$s_search= addslashes(trim($search['adv_search']));
    			$s_where[]= " s.stu_code LIKE '%{$s_search}%'";
    			$s_where[]=" sp.receipt_number LIKE '%{$s_search}%'";
    			$s_where[]= " s.stu_khname LIKE '%{$s_search}%'";
    			$s_where[]= " s.stu_enname LIKE '%{$s_search}%'";
    			$s_where[]= " s.last_name LIKE '%{$s_search}%'";
    			$s_where[]= " s.grade LIKE '%{$s_search}%'";
    			$where.=' AND ('.implode(' OR ', $s_where).')';
    		}
    		if(!empty($search['branch_id'])){
    			$where.= " AND sp.branch_id = ".$search['branch_id'];
    		}
    		if(!empty($search['user'])){
    			$where.= " AND sp.user_id = ".$search['user'];
    		}
    		if(!empty($search['degree'])){
    			$where.= " AND s.degree = ".$search['degree'];
    		}
    		if(!empty($search['grade_all'])){
    			$where.= " AND s.grade = ".$search['grade_all'];
    		}
    		if(!empty($search['session'])){
    			$where.= " AND s.session = ".$search['session'];
    		}
    		if(!empty($search['stu_name'])){
    			$where.= " AND sp.student_id = ".$search['stu_name'];
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
    
}