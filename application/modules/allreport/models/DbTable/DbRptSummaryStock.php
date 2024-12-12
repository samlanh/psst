<?php

class Allreport_Model_DbTable_DbRptSummaryStock extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_itemsdetail';
    function getAllRequestProduct($search=null){
    	$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
    	$branch = $dbp->getBranchDisplay();
    	$sql="SELECT
	    	re.*,
	    	(select title from rms_request_for as rf where rf.id = request_for) as request_for,
	    	(select title from rms_for_section as fs where fs.id = for_section) as for_section,
	    	(select $branch from rms_branch where br_id = branch_id) as branch_name,
	    	(select CONCAT(last_name,' ',first_name) from rms_users as u where u.id = re.user_id) as user,
	    	re.status
    	FROM
    	rms_request_order AS re
    	WHERE
    	1";
    	$where="";
    
    	$from_date =(empty($search['start_date']))? '1': " re.request_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " re.request_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search = str_replace(' ', '', addslashes(trim($search['title'])));
    		$s_where[]= " REPLACE(re.request_no,' ','') LIKE '%{$s_search}%'";
    		$s_where[]="  REPLACE(re.request_name,' ','') LIKE '%{$s_search}%'";
    		$s_where[]= " REPLACE(re.purpose,' ','') LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search['request_for']>0){
    		$where.=" AND request_for=".$search['request_for'];
    	}
    	if($search['for_section']>0){
    		$where.=" AND for_section=".$search['for_section'];
    	}
    	if($search['status']>0){
    		$where.=" AND status=".$search['status'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	$order=" ORDER BY re.id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getRequestProductById($id){
    	$db = $this->getAdapter();
    	$sql="SELECT
    	re.*,
    	(select title from rms_request_for as rf where rf.id = request_for) as request_for,
    	(select title from rms_for_section as fs where fs.id = for_section) as for_section,
    	(select branch_namekh from rms_branch where br_id = branch_id) as branch_name,
    	(select first_name from rms_users as u where u.id = re.user_id) as user,
    	(select name_en from rms_view where type=1 and key_code = re.status) as status
    	FROM
    	rms_request_order AS re
    	WHERE
    	re.id = $id
    	";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	return $db->fetchRow($sql);
    }
    
    function getAllRequestProductDetailById($id){
    	$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
    	$branch = $dbp->getBranchDisplay();
    	$sql="SELECT
    	*,
    	(select $branch from rms_branch where br_id = branch_id) as branch_name,
    	(SELECT d.title FROM `rms_itemsdetail` AS d WHERE d.items_type=3 AND d.id = pro_id LIMIT 1) AS pro_name
    	FROM
    	rms_request_orderdetail
    	WHERE
    	request_id = $id
    	";
    	return $db->fetchAll($sql);
    }
    
    
    public function getAllProduct($search){
    	try{
	    	$db = $this->getAdapter();
	    	$_db = new Application_Model_DbTable_DbGlobal();
			
	    	$level = $_db->getUserType();
	    	$lang = $_db->currentlang();
			$branch = $_db->getBranchDisplay();
	    	if($lang==1){// khmer
	    		$label = "name_kh";
	    		$grade = "d.title";
	    		$degree = "it.title";
	    	}else{ // English
	    		$label = "name_en";
	    		$grade = "d.title_en";
	    		$degree = "it.title_en";
	    	}
	    	if($level==4){
	    		$branch_id = $_db->getAccessPermission("branch_id");
	    	}else{
	    		$branch_id = "";
	    	}
	    	$from_date =(empty($search['start_date']))? '1': $search['start_date']." 00:00:00";
	    	$to_date = (empty($search['end_date']))? '1': $search['end_date']." 23:59:59";
	    	$sql="SELECT 
	    			d.*,
	    			$grade as pro_name,
					(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id = pl.branch_id LIMIT 1) AS branch_name,
					(SELECT $degree FROM `rms_items` AS it WHERE it.id = d.items_id LIMIT 1) AS category,
					(SELECT  
							SUM(pd.qty) 
						FROM 
							rms_purchase_detail AS pd,
							rms_purchase AS pu 
						WHERE 
							pu.id = pd.supproduct_id 
						    AND pd.pro_id = d.id 
						    AND pu.branch_id = pl.`branch_id`
						    AND pu.date >= '$from_date' 
						    AND pu.date <= '$to_date' 
						    AND pu.status = 1 
						GROUP BY pd.pro_id
					  	LIMIT 1
					) AS purchaseQty,
					(SELECT SUM(ctd.qty_receive) FROM `rms_cutstock` AS ct,`rms_cutstock_detail` AS ctd WHERE ct.id = ctd.cutstock_id AND ctd.product_id = d.id AND ct.branch_id = pl.branch_id AND ct.received_date >= '$from_date' AND ct.received_date <= '$to_date' AND ct.status=1 LIMIT 1) AS saleqty,
					(SELECT 
					    	SUM(qty_receive) 
					  	FROM
						    rms_request_order AS req,
						    rms_request_orderdetail AS req_d 
					  	WHERE 
					  		req.id = req_d.request_id 
					  		and req_d.pro_id = d.id
					  		and req_d.branch_id = pl.branch_id
					  		AND req.request_date >= '$from_date' 
						    AND req.request_date <= '$to_date'
						    and req.status=1
					  	LIMIT 1) AS request,
				  	(SELECT SUM(difference) FROM rms_adjuststock AS adj, rms_adjuststock_detail AS adj_d WHERE adj.id = adj_d.adjuststock_id AND adj_d.pro_id = d.id AND adj_d.branch_id = pl.branch_id AND adj.request_date >= '$from_date' AND adj.request_date <= '$to_date' AND adj.status=1 LIMIT 1) AS adjustQty, 
				  	(SELECT SUM(qty) FROM `rms_transferstock` AS tra, rms_transferdetail AS tra_d WHERE tra.id = tra_d.transferid AND tra_d.pro_id = d.id AND tra.from_location = pl.branch_id AND tra.transfer_date >= '$from_date' AND tra.transfer_date <= '$to_date' AND tra.status=1 LIMIT 1) AS tran_out, 
					(SELECT SUM(qty) FROM `rms_transferstock` AS tra, rms_transferdetail AS tra_d WHERE tra.id = tra_d.transferid AND tra_d.pro_id = d.id AND tra.to_location = pl.branch_id AND tra.transfer_date >= '$from_date' AND tra.transfer_date <= '$to_date' AND tra.status=1 LIMIT 1) AS tran_in, 
					pl.pro_qty
				FROM 
					`rms_itemsdetail` AS d,
					rms_product_location AS pl
				WHERE 
					d.id = pl.pro_id
					AND d.items_type=3
					AND d.status=1
	    	";
	    	$where=' ';
	    	$group_by = " ";
	    	$order=" ORDER BY pl.branch_id ASC ";
	    	
	    	if($search['sort_by']==1){
	    		$order.=" , d.items_id ASC ";
	    	}else if($search['sort_by']==2){
	    		$order.=" , $grade ASC ";
	    	}
	    	
	    	if(!empty($search['title'])){
	    		$s_where = array();
	    		$s_search = addslashes(trim($search['title']));
	    		$s_where[] = " d.title LIKE '%{$s_search}%'";
	    		$s_where[] = " d.code LIKE '%{$s_search}%'";
	    		$where .=' AND ( '.implode(' OR ',$s_where).')';
	    	}
	    	
	    	if(!empty($search['branch_id'])){
	    		$where .=' AND pl.branch_id = '.$search['branch_id'];
	    	}
	    	if(!empty($search['category_id'])){
	    		$where .=' AND d.items_id = '.$search['category_id'];
	    	}
	    	if(!empty($search['product'])){
	    		$where .=' AND d.id = '.$search['product'];
	    	}
	    	if(!empty($search['product_type'])){
	    		$where .=' AND d.product_type = '.$search['product_type'];
	    	}
	    	$dbp = new Application_Model_DbTable_DbGlobal();
	    	$where.=$dbp->getAccessPermission("pl.branch_id");
	    	//echo  $sql.$where.$order;
	    	return $db->fetchAll($sql.$where.$group_by.$order);
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
	function getSummaryStockReport($search){
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		$branch = $_db->getBranchDisplay();
		if($lang==1){// khmer
			
			$proName = "p.title";
			$category = "it.title";
		}else{ // English
			
			$proName = "p.title_en";
			$category = "it.title_en";
		}

		$sql = "SELECT 
				cl.id as closingId ,
				cl.branchId,
				p.code as proCode,
				$proName as productName,
				(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id = cl.branchId LIMIT 1) AS branchName,
				(SELECT $category FROM `rms_items` AS it WHERE it.id = p.items_id LIMIT 1) AS categoryName,
				cl.openingDate,
				cl.closingDate,
				cl.fromDate,
				cl.toDate,
				cl.adjustId,
				cd.proId,
				cd.qtyBegining,
				cd.qtyClosing,
				(SELECT pro_qty FROM `rms_product_location` AS pl WHERE pl.branch_id = cl.branchId AND pl.pro_id = cd.proId LIMIT 1) AS currentQty,
				cl.isClosed
							
				FROM `rms_closing` AS cl 
				INNER JOIN `rms_closing_detail` AS cd ON cl.id = cd.closingId 
				LEFT JOIN `rms_itemsdetail` AS p ON p.id = cd.proId ";
		$where = ' WHERE 1 ';
		
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " p.title LIKE '%{$s_search}%'";
			$s_where[] = " p.code LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if (!empty($search['closeStockId'])) {
			$where .= " AND cl.id = " . $search['closeStockId'];
		}
		if (!empty($search['branch_id'])) {
			$where .= " AND cl.branchId = " . $search['branch_id'];
		}
		
		if(!empty($search['category_id'])){
			//$where .=' AND p.items_id = '.$search['category_id'];
			$arrCon = array(
				"categoryId" => $search['category_id'],
			);
			$condiction = $_db->getChildItems($arrCon);
			if (!empty($condiction)){
				$where.=" AND p.items_id IN ($condiction)";
			}else{
				$where.=" AND p.items_id=".$search['category_id'];
			}
		}
		if(!empty($search['product'])){
			$where .= " AND cd.proId = " . $search['productId'];
		}
		if(!empty($search['product_type'])){
			$where .=' AND p.product_type = '.$search['product_type'];
		}

		$dbg = new Application_Model_DbTable_DbGlobal();
		$where .= $dbg->getAccessPermission('cl.branchId');
		$order = ' GROUP BY cl.id DESC, cd.proId ORDER BY cl.id DESC, p.items_id,p.id ASC  ';
	//	echo $sql . $where . $order;
		$results = $this->getAdapter()->fetchAll($sql . $where . $order);
		$records = array();
		if (!empty($results)) {
			foreach ($results as $key => $result) {
				$records[$key]['closingId'] = $result['closingId'];
				$records[$key]['branchName'] = $result['branchName'];
				$records[$key]['openingDate'] = $result['openingDate'];
				$records[$key]['closingDate'] = $result['closingDate'];
				$records[$key]['fromDate'] = $result['fromDate'];
				$records[$key]['toDate'] = $result['toDate'];
				$records[$key]['productName'] = $result['productName'];
				$records[$key]['proCode'] = $result['proCode'];

				$records[$key]['categoryName'] = $result['categoryName'];

				$records[$key]['qtyBegining'] = $result['qtyBegining'];
				$records[$key]['qtyClosing'] = $result['qtyClosing'];
				$records[$key]['isClosed'] = $result['isClosed'];
				$records[$key]['currentQty'] = $result['currentQty'];

				$records[$key]['purchaseQty'] = 0;
				$records[$key]['qtyTransferIn'] = 0;

				$records[$key]['saleqty'] = 0;
				$records[$key]['qtyUsage'] = 0;
				$records[$key]['qtyTransferOut'] = 0;

				$records[$key]['qtyAdjust'] = 0;

				$param = array(
					'branchId' => $result['branchId'],
					'proId' => $result['proId'],
					'start_date' => $result['openingDate'],
					'end_date' => empty($result['toDate']) ? date('Y-m-d') : $result['toDate'], //less 1 day
				);

				$purchaseQty = $this->getPurchasebyClosingEntry($param);
				if (!empty($purchaseQty)) {
					$records[$key]['purchaseQty'] = $purchaseQty;
				}

				$qtyTransferIn = $this->getReceiveTransferClosingEntry($param);
				if (!empty($qtyTransferIn)) {
					$records[$key]['qtyTransferIn'] = $qtyTransferIn;
				}

				$qtyTransferOut = $this->getTransferClosingEntry($param);
				if (!empty($qtyTransferOut)) {
					$records[$key]['qtyTransferOut'] = $qtyTransferOut;
				}

				$saleqty = $this->getSaleQtyClosingEntry($param);
				if (!empty($saleqty)) {
					$records[$key]['saleqty'] = $saleqty;
				}
			
				$qtyUsage = $this->getUsageQtyClosingEntry($param);
				if (!empty($qtyUsage)) {
					$records[$key]['qtyUsage'] = $qtyUsage;
				}

				$qtyAdjust = $this->getAdjustQtyClosingEntry($param);
				if (!empty($qtyAdjust)) {
					$records[$key]['qtyAdjust'] = $qtyAdjust;
				}
			}
		}
		///echo $sql . $where . $order; exit();
		return $records;
	}
	function getPurchasebyClosingEntry($data)
	{
		$sql = "SELECT  
					SUM(pd.qty) 
				FROM 
					rms_purchase_detail AS pd,
					rms_purchase AS pu 
				WHERE 
					pu.id = pd.supproduct_id 
					AND pu.status = 1 ";

		if (!empty($data['start_date'])) {
			$from_date = (empty($data['start_date'])) ? '1' : " pu.date >= '" . $data['start_date'] . " 00:00:00'";
			$to_date = (empty($data['end_date'])) ? '1' : " pu.date <='" . $data['end_date'] . " 23:59:59'";
			$sql .= " AND " . $from_date . " AND " . $to_date;
		}
		if (!empty($data['branchId'])) {
			$sql .= " AND pu.branch_id =" . $data['branchId'];
		}
		if (!empty($data['proId'])) {
			$sql .= " AND pd.pro_id  =" . $data['proId'];
		}
		$sql .= " GROUP BY pd.pro_id ";
		return $this->getAdapter()->fetchOne($sql);
	}
	function getSaleQtyClosingEntry($data)
	{ //usage and sale
		$sql = " SELECT 
			SUM(ctd.qty_receive)
		 FROM `rms_cutstock` AS ct,
		 		`rms_cutstock_detail` AS ctd 
				WHERE ct.id = ctd.cutstock_id 
				AND ct.status=1 ";
		if (!empty($data['start_date'])) {
			$from_date = (empty($data['start_date'])) ? '1' : " ct.received_date  >= '" . $data['start_date'] . " 00:00:00'";
			$to_date = (empty($data['end_date'])) ? '1' : " ct.received_date  <= '" . $data['end_date'] . " 00:00:00'";
			$sql .= " AND " . $from_date . " AND " . $to_date;
		}

		if (!empty($data['branchId'])) {
			$sql .= " AND ct.branch_id =" . $data['branchId'];
		}
		if (!empty($data['proId'])) {
			$sql .= " AND ctd.product_id=" . $data['proId'];
		}
		$sql .= " GROUP BY ctd.product_id ";

		return $this->getAdapter()->fetchOne($sql);
	}
	function getUsageQtyClosingEntry($data)
	{ //usage and sale
		$sql = " SELECT 
					SUM(qty_receive) 
				FROM
					rms_request_order AS req,
					rms_request_orderdetail AS req_d 
				WHERE 
					req.id = req_d.request_id 
					AND req.status=1 ";
		if (!empty($data['start_date'])) {
			$from_date = (empty($data['start_date'])) ? '1' : " req.request_date  >= '" . $data['start_date'] . " 00:00:00'";
			$to_date = (empty($data['end_date'])) ? '1' : " req.request_date  <= '" . $data['end_date'] . " 00:00:00'";
			$sql .= " AND " . $from_date . " AND " . $to_date;
		}

		if (!empty($data['branchId'])) {
			$sql .= " AND req_d.branch_id =" . $data['branchId'];
		}
		if (!empty($data['proId'])) {
			$sql .= " AND req_d.pro_id=" . $data['proId'];
		}
		$sql .= " GROUP BY req_d.pro_id ";
		return $this->getAdapter()->fetchOne($sql);
	}

	function getAdjustQtyClosingEntry($data)
	{ //usage and sale
		$sql = " SELECT 
				SUM(difference) 
				FROM rms_adjuststock AS adj,
					rms_adjuststock_detail AS adj_d 
					WHERE adj.id = adj_d.adjuststock_id 
					AND adj.status=1 ";
		if (!empty($data['start_date'])) {
			$from_date = (empty($data['start_date'])) ? '1' : " adj.request_date  >= '" . $data['start_date'] . " 00:00:00'";
			$to_date = (empty($data['end_date'])) ? '1' : " adj.request_date  <= '" . $data['end_date'] . " 00:00:00'";
			$sql .= " AND " . $from_date . " AND " . $to_date;
		}
		if (!empty($data['branchId'])) {
			$sql .= " AND adj_d.branch_id =" . $data['branchId'];
		}
		if (!empty($data['proId'])) {
			$sql .= " AND adj_d.pro_id =" . $data['proId'];
		}
		$sql .= " GROUP BY adj_d.pro_id ";
		return $this->getAdapter()->fetchOne($sql);
	}
	
	function getTransferClosingEntry($data)
	{ //transfer and receive for closing
		$sql = " SELECT
    		SUM(td.qty) AS qty
    	FROM `rms_transferstock` AS t,
			 rms_transferdetail AS td
    	WHERE t.id=td.transferid AND t.is_received=1 ";

		if (!empty($data['start_date'])) {
			$from_date = (empty($data['start_date'])) ? '1' : " t.transfer_date >= '" . $data['start_date'] . " 00:00:00'";
			$to_date = (empty($data['end_date'])) ? '1' : " t.transfer_date <= '" . $data['end_date'] . " 00:00:00'";
			$sql .= " AND " . $from_date . " AND " . $to_date;
		}

		if (!empty($data['branchId'])) {
			$sql .= " AND t.from_location=" . $data['branchId'];
		}
		if (!empty($data['proId'])) {
			$sql .= " AND td.pro_id=" . $data['proId'];
		}
		$sql .= " GROUP BY td.pro_id ";
		return $this->getAdapter()->fetchOne($sql);
	}
	function getReceiveTransferClosingEntry($data)
	{ //transfer and receive for closing
		$sql = " SELECT
    		SUM(td.qty) AS qty
    	FROM `rms_transferstock` AS t,
			 rms_transferdetail AS td
    	WHERE t.id=td.transferid AND t.is_received=1 ";

		if (!empty($data['start_date'])) {
			$from_date = (empty($data['start_date'])) ? '1' : " t.transfer_date >= '" . $data['start_date'] . " 00:00:00'";
			$to_date = (empty($data['end_date'])) ? '1' : " t.transfer_date <= '" . $data['end_date'] . " 00:00:00'";
			$sql .= " AND " . $from_date . " AND " . $to_date;
		}

		if (!empty($data['branchId'])) {
			$sql .= " AND t.to_location=" . $data['branchId'];
		}
		if (!empty($data['proId'])) {
			$sql .= " AND td.pro_id=" . $data['proId'];
		}
		$sql .= " GROUP BY td.pro_id ";
		return $this->getAdapter()->fetchOne($sql);
	}

    public function getAllStudentProduct($search,$new=null){
    	try{
    		$db = $this->getAdapter();
    		
    		$dbp = new Application_Model_DbTable_DbGlobal();
			
    		$currentLang = $dbp->currentlang();
			$branch = $dbp->getBranchDisplay();
    		$stuname=" CONCAT(s.stu_enname,' ',s.last_name)";
    		if ($currentLang==1){
    			$stuname="s.stu_khname";
    		}
    		
    		$from_date =(empty($search['start_date']))? '1': $search['start_date']." 00:00:00";
    		$to_date = $search['end_date'];
    		$sql ="
			SELECT 
			(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id=sp.branch_id LIMIT 1) AS branch_name,
			(SELECT b.photo FROM rms_branch AS b WHERE b.br_id=sp.branch_id LIMIT 1) AS branch_logo,
			sp.student_id as stu_id,
			$stuname AS student_name,
			s.stu_khname ,
			s.stu_enname AS stu_enname,
			s.last_name  AS last_name,
			s.stu_code AS stu_code,
			(SELECT `title` FROM `rms_items` WHERE `id`=s.degree AND type=1 LIMIT 1) AS degree,
			(SELECT CONCAT(`title`) FROM `rms_itemsdetail` WHERE `id`=s.grade AND items_type=1 LIMIT 1) AS grade,
			s.tel AS tel,
			s.photo AS photo,
			(SELECT ie.title FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) AS items_name,
			(SELECT ie.images FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) AS pro_images,
			(SELECT ie.items_type FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) AS items_type,
			spd.*,
			sp.branch_id,
			sp.receipt_number,
			sp.create_date AS payment_date,
			(SELECT ctd.remide_date FROM `rms_cutstock_detail` AS ctd WHERE ctd.student_paymentdetail_id=spd.id ORDER BY ctd.remide_date DESC LIMIT 1 ) AS remide_date
			FROM `rms_student_payment` AS sp,
			`rms_student_paymentdetail` AS spd,
			`rms_student` AS s
			WHERE spd.payment_id = sp.id
			AND s.stu_id = sp.student_id
			AND (SELECT ie.items_type FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) =3
			AND is_void=0
			AND qty_balance >0
					";
    		$where="";
    		if(!empty($search['title'])){
    			$s_where = array();
    			$s_search = addslashes(trim($search['title']));
    			$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
    			$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
    			$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
    			$s_where[] = " s.last_name LIKE '%{$s_search}%'";
    			$s_where[] = " (SELECT ie.title FROM `rms_itemsdetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) LIKE '%{$s_search}%'";
    			$where .=' AND ( '.implode(' OR ',$s_where).')';
    		}
    		if(!empty($search['study_year'])){
    			$where.=' AND s.academic_year='.$search['study_year'];
    		}
    		if(!empty($search['group'])){
    			$where.=' AND s.group_id='.$search['group'];
    		}
    		if(!empty($search['grade_all'])){
    			$where.=' AND s.grade='.$search['grade_all'];
    		}
    		if($search['degree']>0){
    			$where.=' AND s.degree='.$search['degree'];
    		}
    		if(($search['branch_id'])>0){
    			$where.=' AND sp.branch_id='.$search['branch_id'];
    		}
    		$order="";
    		if (!empty($new)){
    			// 			$where = " AND spd.id NOT IN (SELECT ctd.student_paymentdetail_id FROM `rms_cutstock_detail` AS ctd ) ";
    			$where.= " AND spd.qty_balance = spd.qty ";
    			$order = " ORDER BY sp.id DESC";
    		}else{
    			$to_date = (empty($to_date))? '1': " (SELECT ctd.remide_date FROM `rms_cutstock_detail` AS ctd WHERE ctd.student_paymentdetail_id=spd.id ORDER BY ctd.remide_date DESC LIMIT 1 ) <= '".$to_date." 23:59:59'";
    			$where.= " AND ".$to_date;
    			$order=" ORDER BY (SELECT ctd.remide_date FROM `rms_cutstock_detail` AS ctd WHERE ctd.student_paymentdetail_id=spd.id ORDER BY ctd.remide_date DESC LIMIT 1 ) DESC ";
    		
    		}
    		
    		$where.=$dbp->getAccessPermission("sp.branch_id");
    		return $db->fetchAll($sql.$where.$order);
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
	function studentGetProduct($search){
    	$db = $this->getAdapter();
    	try{
    		$dbp = new Application_Model_DbTable_DbGlobal();
			$branch = $dbp->getBranchDisplay();
    		$currentLang = $dbp->currentlang();
    		$stuname=" CONCAT(s.stu_enname,' ',s.last_name)";
    		if ($currentLang==1){
    			$stuname="s.stu_khname";
    		}
    		$sql="SELECT *,
			(SELECT b.$branch FROM `rms_branch` AS b  WHERE b.br_id = p.branch_id LIMIT 1) AS branch_name,
			p.create_date,
			(SELECT s.stu_code FROM `rms_student` AS s  WHERE s.stu_id = p.student_id LIMIT 1) AS student_code,
			(SELECT $stuname FROM `rms_student` AS s  WHERE s.stu_id = p.student_id LIMIT 1) AS student_name,
			(SELECT s.tel FROM `rms_student` AS s  WHERE s.stu_id = p.student_id LIMIT 1) AS tel,
			p.receipt_number,
			(SELECT pd.title FROM `rms_itemsdetail` AS pd  WHERE pd.id = sd.pro_id LIMIT 1) AS product_name,
			sd.qty, 
			sd.qty_after,
			(SELECT CONCAT(first_name,' ',last_name) FROM rms_users AS u WHERE u.id = p.user_id LIMIT 1) AS user_enter
			 	FROM `rms_saledetail` AS sd 
					INNER JOIN `rms_student_payment` AS p WHERE sd.payment_id=p.id
    		";
    		$from_date =(empty($search['start_date']))? '1': " p.create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " p.create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    		$sql.= " AND  ".$from_date." AND ".$to_date;
    		$where="";
    		if(!empty($search['adv_search'])){
    			$s_where=array();
    			$s_search=addslashes(trim($search['adv_search']));
    			$s_where[]= " p.receipt_number LIKE '%{$s_search}%'";
    			$s_where[]= " sd.qty_after LIKE '%{$s_search}%'";
    			$s_where[]= " sd.qty LIKE '%{$s_search}%'";
    		
    			$where.=' AND ('.implode(' OR ', $s_where).')';
    		}
    		if(!empty($search['student_id'])){
    			$where.=" AND p.student_id=".$search['student_id'];
    		}
    		if(!empty($search['status_search'])){
    			$where.=" AND p.status=".$search['status_search'];
    		}
    		if(!empty($search['branch_id'])){
    			$where.=" AND p.branch_id=".$search['branch_id'];
    		}
    		if(!empty($search['status'])){
    			if($search['status']==1){
    				$where.=' AND p.status=0';
    			}else if($search['status']==2){
    				$where.=' AND p.is_closed=1';
    			}
    		}
			if(!empty($search['stock_status'])){
    			if($search['stock_status']==1){
    				$where.=' AND sd.qty_after =0';
    			}else if($search['stock_status']==2){
    				$where.=' AND sd.qty_after>0';
    			}
    		}
    		$where.=$dbp->getAccessPermission('p.branch_id');
    		$order=" ORDER BY p.id DESC";
    		return $db->fetchAll($sql.$where.$order);
    
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function getAllCutStock($search){
    	$db = $this->getAdapter();
    	try{
    		$dbp = new Application_Model_DbTable_DbGlobal();
			$branch = $dbp->getBranchDisplay();
    		$currentLang = $dbp->currentlang();
    		$stuname=" CONCAT(s.stu_enname,' ',s.last_name)";
    		if ($currentLang==1){
    			$stuname="s.stu_khname";
    		}
    		$sql="SELECT  *, c.id AS cutstock_id, c.received_date AS receivedDate,
			(SELECT b.$branch  FROM `rms_branch` AS b  WHERE b.br_id = c.branch_id LIMIT 1) AS branch_name,
			(SELECT p.title FROM `rms_itemsdetail` AS p  WHERE p.id = sd.product_id LIMIT 1) AS product_name,
			
			(SELECT t.qty FROM `rms_saledetail` AS t  WHERE t.id = sd.student_paymentdetail_id LIMIT 1) AS buyQty,
			sd.qty_receive, sd.remain,
			
			(SELECT $stuname FROM `rms_student` AS s  WHERE s.stu_id = c.student_id LIMIT 1) AS student_name,
			(SELECT s.stu_code FROM `rms_student` AS s  WHERE s.stu_id = c.student_id LIMIT 1) AS student_code,
			(SELECT s.tel FROM `rms_student` AS s  WHERE s.stu_id = c.student_id LIMIT 1) AS tel,
			(SELECT sp.receipt_number FROM `rms_student_payment` AS sp  WHERE sp.id = sd.paymentId LIMIT 1) AS receipt_num,
			
			(SELECT CONCAT(first_name,' ',last_name) FROM rms_users AS u WHERE u.id = c.closed_by LIMIT 1) AS user_close,
			(SELECT CONCAT(first_name,' ',last_name) FROM rms_users AS u WHERE u.id = c.user_id LIMIT 1) AS user_enter,
			c.status AS STATUS 
			 FROM `rms_cutstock` AS c 
			INNER JOIN `rms_cutstock_detail` AS sd WHERE c.id = sd.cutstock_id
    		";
    		$from_date =(empty($search['start_date']))? '1': " c.received_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " c.received_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    		$sql.= " AND  ".$from_date." AND ".$to_date;
    		$where="";
    		if(!empty($search['adv_search'])){
    			$s_where=array();
    			$s_search=addslashes(trim($search['adv_search']));
    			$s_where[]= " c.serailno LIKE '%{$s_search}%'";
    			$s_where[]= " sd.qty_receive LIKE '%{$s_search}%'";
    			$s_where[]= " sd.due_amount LIKE '%{$s_search}%'";
    		
    			$where.=' AND ('.implode(' OR ', $s_where).')';
    		}
    		if(!empty($search['student_id'])){
    			$where.=" AND sd.student_id=".$search['student_id'];
    		}
			if(!empty($search['cut_stock_type'])){
    			$where.=" AND c.cut_stock_type=".$search['cut_stock_type'];
    		}
    		if(!empty($search['status_search'])){
    			$where.=" AND c.status=".$search['status_search'];
    		}
    		if(!empty($search['branch_id'])){
    			$where.=" AND c.branch_id=".$search['branch_id'];
    		}
    		if(!empty($search['status'])){
    			if($search['status']==1){
    				$where.=' AND c.status=0';
    			}else if($search['status']==2){
    				$where.=' AND c.is_closed=1';
    			}
    		}
    		$where.=$dbp->getAccessPermission('c.branch_id');
    		$order=" ORDER BY c.id DESC";
    		return $db->fetchAll($sql.$where.$order);
    
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function getAllCutStockForClose($search){
    	$db = $this->getAdapter();
	    	try{
	    		$dbp = new Application_Model_DbTable_DbGlobal();
				$branch = $dbp->getBranchDisplay();
	    		$currentLang = $dbp->currentlang();
	    		$stuname=" CONCAT(s.stu_enname,' ',s.last_name)";
	    		if ($currentLang==1){
	    			$stuname="s.stu_khname";
	    		}
	    		$sql="
	    		SELECT
	    		(SELECT b.$branch FROM `rms_branch` AS b  WHERE b.br_id = pp.branch_id LIMIT 1) AS branch_name,
	    		s.stu_khname,
	    		$stuname AS student_name,
	    		s.stu_enname,
	    		s.last_name,
	    		s.stu_code,
	    		s.tel,
	    		(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gds.academic_year LIMIT 1) AS academic,
	    		(SELECT `title` FROM `rms_items` WHERE `id`=gds.degree AND type=1 LIMIT 1) AS degree,
	    		(SELECT CONCAT(`title`) FROM `rms_itemsdetail` WHERE `id`=gds.grade AND items_type=1 LIMIT 1) AS grade,
	    		pp.*,
	    		(SELECT CONCAT(first_name,' ',last_name) FROM rms_users as u where u.id = pp.closed_by LIMIT 1) as user_close,
	    		(SELECT CONCAT(first_name,' ',last_name) FROM rms_users as u where u.id = pp.user_id LIMIT 1) as user_enter
	    		FROM 
		    		`rms_cutstock` AS pp,
		    		`rms_student` AS s,
		    		rms_group_detail_student AS gds 
	    		WHERE 
				gds.itemType=1 
	    		AND s.stu_id = pp.student_id
	    		AND s.stu_id = gds.stu_id
	    		AND pp.is_closed = 0
	    		AND pp.status = 1
	    		AND gds.is_maingrade=1
	    		AND gds.is_current=1
	    		";
	    		$from_date =(empty($search['start_date']))? '1': " pp.received_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
	    		$to_date = (empty($search['end_date']))? '1': " pp.received_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
	    		$sql.= " AND  ".$from_date." AND ".$to_date;
	    		$where="";
	    		if(!empty($search['adv_search'])){
	    		$s_where=array();
	    				$s_search=addslashes(trim($search['adv_search']));
	    				$s_where[]= " pp.serailno LIKE '%{$s_search}%'";
	    				$s_where[]= " pp.balance LIKE '%{$s_search}%'";
	    		$s_where[]= " pp.total_received LIKE '%{$s_search}%'";
	    		$s_where[]= " pp.total_qty_due LIKE '%{$s_search}%'";
	    
	    		$where.=' AND ('.implode(' OR ', $s_where).')';
	    		}
	    		if(!empty($search['student_id'])){
	    		$where.=" AND pp.student_id=".$search['student_id'];
	    	}
	    	if(!empty($search['status_search'])){
	    	$where.=" AND pp.status=".$search['status_search'];
	    	}
	    	if(!empty($search['branch_search'])){
	    		$where.=" AND pp.branch_id=".$search['branch_search'];
	    	}
	    
	    	$where.=$dbp->getAccessPermission('pp.branch_id');
	    	
	    	$user = $dbp->getUserInfo();
	    	if ($user['level']!=1){
	    		$where.=" AND pp.user_id=".$user['user_id'];
	    	}
	    	
	    	if(!empty($search['user_id'])){
	    		$where.=" AND pp.user_id=".$search['user_id'];
	    	}
	    	$order=" ORDER BY pp.id DESC";
// 	    	echo $sql.$where.$order;exit();
	    	$dbp = new Application_Model_DbTable_DbGlobal();
	    	$where.=$dbp->getAccessPermission('pp.branch_id');
	    	return $db->fetchAll($sql.$where.$order);
	    
	    	}catch(Exception $e){
	    	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    function closingStuProduct($_data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
    		$dbg = new Application_Model_DbTable_DbGlobal();
    		if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
    			if (!empty($rs)){
    				$_arr = array(
    						'is_closed'=> 1,
    						'modify_date'=> date("Y-m-d H:i:s"),
    						'closed_by'=> $dbg->getUserId(),
    				);
    				$this->_name ="rms_cutstock";
    				$where = $_db->quoteInto("id=?", $rs);
    				$this->update($_arr, $where);
    			}
    		}
    
    		$_db->commit();
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$_db->rollBack();
    	}
    }
    
    function getProductSold($search){
    	$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$branch = $dbp->getBranchDisplay();
		$sql="SELECT
		    		(SELECT b.$branch FROM `rms_branch` AS b  WHERE b.br_id = sp.branch_id LIMIT 1) AS branch_name,
			    	(SELECT i.title FROM `rms_items` AS i WHERE i.id = i.items_id LIMIT 1) AS category,
			    	i.title AS items_name,
			    	i.code AS code,
					sum(sd.qty) as qty,
					sum(sd.qty*sd.price) as subtotalsale,
					sum(sd.qty*sd.cost) as subtotalcost
    			FROM 
    				`rms_student_payment` AS sp,
    				rms_saledetail sd,
			    	rms_itemsdetail as i
    			WHERE 
    				sp.id = sd.payment_id
			    	AND i.id = sd.pro_id
			    	AND i.items_type = 3
			    	AND sp.status = 1
			    	AND sp.is_void = 0
    		";

    	
    	$from_date =(empty($search['start_date']))? '1': " sp.create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sp.create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    	$sql.= " AND  ".$from_date." AND ".$to_date;
    	$where="";
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " i.title LIKE '%{$s_search}%'";
    		$s_where[] = " i.code LIKE '%{$s_search}%'";
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$sql .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['category_id'])){
    		//$where.= " AND i.items_id = ".$db->quote($search['category_id']);
			$arrCon = array(
				"categoryId" => $search['category_id'],
			);
			$condiction = $dbp->getChildItems($arrCon);
			if (!empty($condiction)){
				$where.=" AND i.items_id IN ($condiction)";
			}else{
				$where.=" AND i.items_id=".$search['category_id'];
			}
    	}
    	if(!empty($search['product'])){
    		$where.= " AND sd.pro_id= ".$db->quote($search['product']);
    	}
    	if(!empty($search['branch_id'])){
    		$where.= " AND sp.branch_id= ".$db->quote($search['branch_id']);
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('sp.branch_id');
    	$where.=" GROUP BY sp.branch_id, sd.pro_id ";
    	return $db->fetchAll($sql.$where);
    }
    function getAllProductSold($search){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
		$branch = $dbp->getBranchDisplay();
    	$currentLang = $dbp->currentlang();
    	$stuname=" CONCAT(s.stu_enname,' ',s.last_name)";
    	if ($currentLang==1){
    		$stuname="s.stu_khname";
    	}
    	$sql="SELECT
		    		(SELECT b.$branch FROM `rms_branch` AS b  WHERE b.br_id = sp.branch_id LIMIT 1) AS branch_name,
		    		s.stu_khname,
		    		$stuname AS student_name,
		    		s.stu_enname,
		    		s.last_name,
		    		s.stu_code,
		    		s.tel,
			    	sp.receipt_number,
			    	sp.create_date,
			    	(SELECT i.title FROM `rms_items` AS i WHERE i.id = i.items_id LIMIT 1) AS category,
			    	i.title AS items_name,
			    	i.code AS code,
			    	sd.cost , 
			    	sd.qty,
			    	sd.price,
			    	(SELECT CONCAT(first_name,' ',last_name) FROM rms_users as u where u.id = sp.user_id LIMIT 1) as user
    			FROM 
    				`rms_student_payment` AS sp,
    				rms_saledetail sd,
			    	`rms_student` AS s,
			    	rms_itemsdetail as i
    			WHERE 
    				sp.id = sd.payment_id
			    	AND s.stu_id = sp.student_id
			    	AND i.id = sd.pro_id
			    	AND i.items_type = 3
			    	AND sp.status = 1
			    	AND sp.is_void = 0
    		";
    	$from_date =(empty($search['start_date']))? '1': " sp.create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sp.create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    	$sql.= " AND  ".$from_date." AND ".$to_date;
    	$where="";
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " i.title LIKE '%{$s_search}%'";
    		$s_where[] = " i.code LIKE '%{$s_search}%'";
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$sql .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['category_id'])){
    		//$where.= " AND i.items_id = ".$db->quote($search['category_id']);
			$arrCon = array(
				"categoryId" => $search['category_id'],
			);
			$condiction = $dbp->getChildItems($arrCon);
			if (!empty($condiction)){
				$where.=" AND i.items_id IN ($condiction)";
			}else{
				$where.=" AND i.items_id=".$search['category_id'];
			}
    	}
    	if(!empty($search['product'])){
    		$where.= " AND sd.pro_id= ".$db->quote($search['product']);
    	}
    	if(!empty($search['product_type'])){
    		$where.= " AND i.product_type = ".$db->quote($search['product_type']);
    	}
    	if(!empty($search['branch_id'])){
    		$where.= " AND sp.branch_id= ".$db->quote($search['branch_id']);
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('sp.branch_id');
    	return $db->fetchAll($sql.$where);
    }
}