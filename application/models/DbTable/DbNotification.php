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
		return $db->fetchAll($sql.$where.$order);
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
		return $db->fetchAll($sql.$where.$order);
	}
}
?>