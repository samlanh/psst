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
	function getStuDocumentAlert($condiction=array()){
		$db = $this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$branchCol = $dbgb->getBranchDisplay();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$day = 5;
		$end_date = date('Y-m-d',strtotime(" +$day day"));
		$sql ="
			SELECT 
				s.branch_id
				,(SELECT CONCAT(b.$branchCol) FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_name
				,(SELECT b.photo FROM rms_branch as b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_logo
				,s.stu_code
				,s.stu_khname
				,s.stu_enname
				,s.last_name
				,s.photo
				,s.sex
				,s.tel
				
				,s.stu_code AS studentCode
				,s.stu_khname AS studentNameKh
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS studentNameEng
				,sd.*
			FROM `rms_student_document` AS sd
				,`rms_student` AS s
			WHERE s.stu_id = sd.stu_id
				AND sd.is_receive=0
		";
		$where ='';
		$to_date = (empty($end_date))? '1': " sd.date_end <= '".$end_date." 23:59:59'";
		$where.= " AND ".$to_date;
		
		$where.=$dbgb->getAccessPermission("s.branch_id");
		$order=" ORDER BY sd.date_end DESC, sd.stu_id ASC";
		$limit=" LIMIT 20";
		if(!empty($condiction['limitRecord'])){
			$limit=" LIMIT ".$condiction['limitRecord'];
		}
		return $db->fetchAll($sql.$where.$order.$limit);
	}
	function getTeachDocumentAlert($condiction=array()){
		$db = $this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$branchCol = $dbgb->getBranchDisplay();
		$viewCol = $dbgb->getViewLabelDisplay();
		
		$day = 5;
		$end_date = date('Y-m-d',strtotime(" +$day day"));
		$sql =" 
		SELECT 
			s.branch_id
			,(SELECT CONCAT(b.$branchCol) FROM rms_branch AS b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_name
			,(SELECT b.photo FROM rms_branch AS b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_logo
			
			,(SELECT v.$viewCol FROM rms_view AS v WHERE v.type=2 AND v.key_code=s.sex LIMIT 1) AS sex
			,(SELECT v.$viewCol FROM rms_view AS v WHERE v.type=24 AND v.key_code=s.teacher_type LIMIT 1) AS teacher_type
			
			,s.teacher_code
			,s.teacher_name_kh
			,s.tel
			,s.email
			,s.photo
			,sd.*
		FROM 
			`rms_teacher_document` AS sd, 
			`rms_teacher` AS s
		WHERE s.id = sd.stu_id
			AND sd.is_receive=0
		";
		$where ='';
		$to_date = (empty($end_date))? '1': " sd.date_end <= '".$end_date." 23:59:59'";
		$where.= " AND ".$to_date;
		$where.=$dbgb->getAccessPermission("s.branch_id");
		$order=" ORDER BY sd.date_end DESC, sd.stu_id ASC";
		$limit=" LIMIT 20";
		if(!empty($condiction['limitRecord'])){
			$limit=" LIMIT ".$condiction['limitRecord'];
		}
		return $db->fetchAll($sql.$where.$order.$limit);
	}
	
	function getStuProductAlert($condiction=array()){
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$new = empty($condiction['new']) ? null : $condiction['new'];
		$db = $this->getAdapter();
		$day = 5;
		$end_date = date('Y-m-d',strtotime(" +$day day"));
		$sql ="
			SELECT 
				(SELECT CONCAT(b.$branchCol) FROM rms_branch AS b WHERE b.br_id=sp.branch_id LIMIT 1) AS branch_name
				,(SELECT b.photo FROM rms_branch AS b WHERE b.br_id=sp.branch_id LIMIT 1) AS branch_logo
				,sp.student_id as stu_id
				
				,(SELECT ie.$colunmname FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) AS items_name
				,(SELECT ie.images FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) AS pro_images
				,spd.*
				,sp.branch_id
				,sp.receipt_number
				
				,s.tel AS tel
				,s.stu_code AS studentCode
				,s.stu_khname AS studentNameKh
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS studentNameEng
			
			";
			
			$fromStatment = "
				FROM 
					`rms_student_payment` AS sp  JOIN `rms_student_paymentdetail` AS spd ON spd.payment_id = sp.id 
					LEFT JOIN `rms_student` AS s ON s.stu_id = sp.student_id
				WHERE 1
					AND (SELECT ie.items_type FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) =3
					AND sp.is_void=0
					AND spd.qty_balance >0
			";
		$where ='';
		$order=" ORDER BY sp.id DESC ";
		if (!empty($new)){
			$sql.="
				,'' AS remide_date
			";
// 			$where = " AND spd.id NOT IN (SELECT ctd.student_paymentdetail_id FROM `rms_cutstock_detail` AS ctd ) ";
			$where = " AND spd.qty_balance = spd.qty ";
			$order = " ORDER BY sp.id DESC";
		}else{
			/*$sql.="
				,(SELECT ctd.remide_date FROM `rms_cutstock_detail` AS ctd WHERE ctd.student_paymentdetail_id=spd.id ORDER BY ctd.remide_date DESC LIMIT 1 ) AS remide_date
			";
			$to_date = (empty($end_date))? '1': " (SELECT ctd.remide_date FROM `rms_cutstock_detail` AS ctd WHERE ctd.student_paymentdetail_id=spd.id ORDER BY ctd.remide_date DESC LIMIT 1 ) <= '".$end_date." 23:59:59'";
			$where.= " AND ".$to_date;
			$order=" ORDER BY (SELECT ctd.remide_date FROM `rms_cutstock_detail` AS ctd WHERE ctd.student_paymentdetail_id=spd.id ORDER BY ctd.remide_date DESC LIMIT 1 ) DESC ";
			*/
		}
		
		$sql.=$fromStatment;
		
		$where.=$dbgb->getAccessPermission("sp.branch_id");
		$limit=" LIMIT 10";
		if(!empty($condiction['limitRecord'])){
			$limit=" LIMIT ".$condiction['limitRecord'];
		}
		return $db->fetchAll($sql.$where.$order.$limit);
	}
	
	function getStudentNotYetGroup($condiction=array()){
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$branchCol = $dbgb->getBranchDisplay();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$db = $this->getAdapter();
		$sql="SELECT
				(SELECT CONCAT(b.$branchCol) FROM rms_branch AS b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_name
				,(SELECT b.photo FROM rms_branch AS b WHERE b.br_id=s.branch_id LIMIT 1) AS branch_logo
				,(SELECT rms_itemsdetail.$colunmname FROM rms_itemsdetail WHERE rms_itemsdetail.id=gd.grade LIMIT 1) AS grade_title
			    ,(SELECT rms_items.$colunmname FROM rms_items WHERE rms_items.id=gd.degree AND rms_items.type=1 LIMIT 1) AS degree_title
				,s.* 
				,stu_code AS studentCode
				,s.stu_khname AS studentNameKh
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS studentNameEng
			FROM `rms_student` AS s,
				rms_group_detail_student AS gd
			WHERE 
				gd.itemType=1 AND
				s.customer_type =1
				AND s.status=1
				AND gd.is_current=1
				AND gd.stop_type=0
				AND s.stu_id = gd.stu_id 
				AND gd.is_setgroup=0
				AND gd.group_id=0 ";
		
		$sql.=$dbgb->getAccessPermission("s.branch_id");
		$limit="";
		if(!empty($condiction['limitRecord'])){
			$limit=" LIMIT ".$condiction['limitRecord'];
		}
		return $db->fetchAll($sql.$limit);
	}
	
	function getCrmNextContactNoti($condiction=array()){
		$db = $this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$branchCol = $dbgb->getBranchDisplay();
		
		$day = 5;
		$end_date = date('Y-m-d',strtotime(" +$day day"));
		$sql ="
			SELECT 
				crm.branch_id
				,(SELECT CONCAT(b.$branchCol) FROM rms_branch AS b WHERE b.br_id=crm.branch_id LIMIT 1) AS branch_name
				,(SELECT b.photo FROM rms_branch AS b WHERE b.br_id=crm.branch_id LIMIT 1) AS branch_logo
				,crm.kh_name
				,crm.first_name
				,crm.last_name
				,crm.kh_name AS nameKh
				,CONCAT(COALESCE(crm.last_name,''),' ',COALESCE(crm.first_name,'')) AS nameEng
				,crm.tel
				,crm.reason
				,crh.* 
			FROM `rms_crm_history_contact` AS crh,
				`rms_crm` AS crm
			WHERE crm.id = crh.crm_id
				AND crh.proccess = 1
				AND crm.followup_status=1
			
		";
		$to_date = (empty($end_date))? '1': " crh.next_contact <= '".$end_date." 23:59:59'";
		$sql.= " AND ".$to_date;
		
		$sql.=$dbgb->getAccessPermission("crm.branch_id");
		
		$sql.=" GROUP BY crh.crm_id
			ORDER BY crh.next_contact DESC ,crh.id DESC";
		$limit=" LIMIT 20";
		if(!empty($condiction['limitRecord'])){
			$limit=" LIMIT ".$condiction['limitRecord'];
		}
		return $db->fetchAll($sql.$limit);
	}
	
	function getCreditMemoNearExpired($condiction=array()){
		$db = $this->getAdapter();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbgb->currentlang();
		$branchCol = $dbgb->getBranchDisplay();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$day = 7;
		$end_date = date('Y-m-d',strtotime(" +$day day"));
		$sql ="
		SELECT 
			(SELECT CONCAT(b.$branchCol) FROM rms_branch AS b WHERE b.br_id=cr.branch_id LIMIT 1) AS branch_name
			,(SELECT b.photo FROM rms_branch AS b WHERE b.br_id=cr.branch_id LIMIT 1) AS branch_logo
			,cr.* 
			
			,s.tel AS tel
			,s.stu_code AS stu_code
			,s.stu_code AS studentCode
			,s.stu_khname AS studentNameKh
			,s.last_name AS last_name
			,s.stu_enname AS stu_enname
			,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS studentNameEng
		FROM `rms_creditmemo` AS cr 
			JOIN `rms_student` AS s ON s.stu_id = cr.student_id
		WHERE cr.status=1
		";
		$to_date = (empty($end_date))? '1': " cr.end_date <= '".$end_date." 23:59:59'";
		$sql.= " AND ".$to_date;
		
		$sql.=$dbgb->getAccessPermission("cr.branch_id");
		
		$sql.=" ORDER BY cr.end_date DESC ,cr.id DESC";
		$limit=" LIMIT 20";
		if(!empty($condiction['limitRecord'])){
			$limit=" LIMIT ".$condiction['limitRecord'];
		}
		return $db->fetchAll($sql.$limit);
	}
}
?>