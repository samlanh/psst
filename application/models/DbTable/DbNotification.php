<?php

class Application_Model_DbTable_DbNotification extends Zend_Db_Table_Abstract
{
    // set name value
	public function setName($name){
		$this->_name=$name;
	}
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	
	function getStuDocumentAlert(){
		$db = $this->getAdapter();
		$day = 5;
		$end_date = date('Y-m-d',strtotime(" +$day day"));
		$sql ="
			SELECT s.branch_id,
			(SELECT CONCAT(b.branch_nameen) FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_name,
			(SELECT b.photo FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_logo,
			s.stu_code,s.stu_khname,s.stu_enname,s.last_name,
			s.photo,
			s.sex,
			s.tel,
			sd.*
			FROM `rms_student_document` AS sd,`rms_student` AS s
			WHERE s.stu_id = sd.stu_id
			AND sd.is_receive=0
		";
		$where ='';
		$to_date = (empty($end_date))? '1': " sd.date_end <= '".$end_date." 23:59:59'";
		$where.= " AND ".$to_date;
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission("s.branch_id");
		$order=" ORDER BY sd.date_end DESC, sd.stu_id ASC";
		$limit=" LIMIT 20";
		return $db->fetchAll($sql.$where.$order.$limit);
	}
	function getTeachDocumentAlert(){
		$db = $this->getAdapter();
		$day = 5;
		$end_date = date('Y-m-d',strtotime(" +$day day"));
		$sql =" SELECT s.branch_id,
		(SELECT CONCAT(b.branch_nameen) FROM rms_branch AS b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_name,
		(SELECT b.photo FROM rms_branch AS b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_logo,
		CASE
    	WHEN  s.sex = 1 THEN '".$this->tr->translate("MALE")."'
    	WHEN s.sex = 2 THEN '".$this->tr->translate("FEMALE")."'
    	END AS sex,
		(SELECT name_kh FROM rms_view WHERE rms_view.type=24 AND rms_view.key_code=s.teacher_type) AS teacher_type,
		(SELECT name_kh FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=s.nationality) AS nationality,
		(SELECT name_kh FROM rms_view WHERE rms_view.type=3 AND rms_view.key_code=s.degree) AS degree,
		s.teacher_code,s.teacher_name_kh,s.tel,
		s.email,s.photo,
		sd.*
		FROM `rms_teacher_document` AS sd, `rms_teacher` AS s
		WHERE s.id = sd.stu_id
		AND sd.is_receive=0
		";
		$where ='';
		$to_date = (empty($end_date))? '1': " sd.date_end <= '".$end_date." 23:59:59'";
		$where.= " AND ".$to_date;
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission("s.branch_id");
		
		$order=" ORDER BY sd.date_end DESC, sd.stu_id ASC";
		$limit=" LIMIT 20";
		return $db->fetchAll($sql.$where.$order.$limit);
	}
	function getStuProductAlert($new=null){
		$db = $this->getAdapter();
		$day = 5;
		$end_date = date('Y-m-d',strtotime(" +$day day"));
		$sql ="
			SELECT 
			(SELECT CONCAT(b.branch_nameen) FROM rms_branch AS b WHERE b.br_id=sp.branch_id LIMIT 1) AS branch_name,
			(SELECT b.photo FROM rms_branch AS b WHERE b.br_id=sp.branch_id LIMIT 1) AS branch_logo,
			sp.student_id as stu_id,
			(SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = sp.student_id LIMIT 1) AS student_name,
			(SELECT s.stu_enname FROM `rms_student` AS s WHERE s.stu_id = sp.student_id LIMIT 1) AS stu_enname,
			(SELECT s.last_name FROM `rms_student` AS s WHERE s.stu_id = sp.student_id LIMIT 1) AS last_name,
			(SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id = sp.student_id LIMIT 1) AS stu_code,
			(SELECT s.photo FROM `rms_student` AS s WHERE s.stu_id = sp.student_id LIMIT 1) AS photo,
			(SELECT s.tel FROM `rms_student` AS s WHERE s.stu_id = sp.student_id LIMIT 1) AS tel,
			(SELECT ie.title FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) AS items_name,
			(SELECT ie.images FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) AS pro_images,
			(SELECT ie.items_type FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) AS items_type,
			spd.*,
			sp.branch_id,
			sp.receipt_number,
			sp.create_date AS payment_date,
			(SELECT ctd.remide_date FROM `rms_cutstock_detail` AS ctd WHERE ctd.student_paymentdetail_id=spd.id ORDER BY ctd.remide_date DESC LIMIT 1 ) AS remide_date
			FROM `rms_student_payment` AS sp,
			`rms_student_paymentdetail` AS spd
			WHERE spd.payment_id = sp.id
			AND (SELECT ie.items_type FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) =3
			AND is_void=0
			AND qty_balance >0
					";
		$where ='';
		$order="";
		if (!empty($new)){
// 			$where = " AND spd.id NOT IN (SELECT ctd.student_paymentdetail_id FROM `rms_cutstock_detail` AS ctd ) ";
			$where = " AND spd.qty_balance = spd.qty ";
			$order = " ORDER BY sp.id DESC";
		}else{
			$to_date = (empty($end_date))? '1': " (SELECT ctd.remide_date FROM `rms_cutstock_detail` AS ctd WHERE ctd.student_paymentdetail_id=spd.id ORDER BY ctd.remide_date DESC LIMIT 1 ) <= '".$end_date." 23:59:59'";
			$where.= " AND ".$to_date;
			$order=" ORDER BY (SELECT ctd.remide_date FROM `rms_cutstock_detail` AS ctd WHERE ctd.student_paymentdetail_id=spd.id ORDER BY ctd.remide_date DESC LIMIT 1 ) DESC ";
			
		}
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission("sp.branch_id");
		$limit=" LIMIT 10";
		return $db->fetchAll($sql.$where.$order.$limit);
	}
	
	function getStudentNotYetGroup(){
		$db = $this->getAdapter();
		$sql="SELECT
			(SELECT CONCAT(b.branch_nameen) FROM rms_branch AS b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_name,
			(SELECT b.photo FROM rms_branch AS b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_logo,
			(SELECT b.school_namekh FROM rms_branch AS b WHERE b.br_id=s.branch_id LIMIT 1) AS school_namekh,
			(SELECT b.school_nameen FROM rms_branch AS b WHERE b.br_id=s.branch_id LIMIT 1) AS school_nameen,
			(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=s.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade_title,
			   (SELECT rms_items.title FROM rms_items WHERE rms_items.id=s.degree AND rms_items.type=1 LIMIT 1) AS degree_title,	 
			s.* FROM 
			`rms_student` AS s
			WHERE s.customer_type =1
			AND s.status=1
			AND s.is_subspend=0
			AND s.is_setgroup =0 ";//(s.group_id=0 OR s.group_id='')
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("s.branch_id");
		$limit=" LIMIT 10";
		return $db->fetchAll($sql.$limit);
	}
	
	function getCrmNextContactNoti(){
		$db = $this->getAdapter();
		$day = 5;
		$end_date = date('Y-m-d',strtotime(" +$day day"));
		$sql ="SELECT 
			crm.branch_id,
			(SELECT CONCAT(b.branch_nameen) FROM rms_branch AS b WHERE b.br_id=crm.branch_id LIMIT 1) AS branch_name,
			(SELECT b.photo FROM rms_branch AS b WHERE b.br_id=crm.branch_id LIMIT 1) AS branch_logo,
			crm.kh_name,
			crm.first_name,
			crm.last_name,
			crm.tel,
			crm.reason,
			crh.* FROM `rms_crm_history_contact` AS crh,`rms_crm` AS crm
			WHERE crm.id = crh.crm_id
			AND crh.proccess = 1
			";
		$to_date = (empty($end_date))? '1': " crh.next_contact <= '".$end_date." 23:59:59'";
		$sql.= " AND ".$to_date;
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("crm.branch_id");
		
		$sql.=" GROUP BY crh.crm_id
			ORDER BY crh.next_contact DESC ,crh.id DESC";
		$limit=" LIMIT 10";
		return $db->fetchAll($sql.$limit);
	}
}
?>