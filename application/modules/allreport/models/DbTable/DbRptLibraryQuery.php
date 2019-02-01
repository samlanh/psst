<?php

class Allreport_Model_DbTable_DbRptLibraryQuery extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student_payment';
    
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    
    public function getStudentPayments($search){
    	$db = $this->getAdapter();
    	$where=' ';
    	$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql=" SELECT * FROM v_getstudentpayment WHERE 1 ";
    	$order=" ORDER BY id DESC , receipt_number DESC ";
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " receipt_number LIKE '%{$s_search}%'";
    		$s_where[] = " stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " kh_name LIKE '%{$s_search}%'";
    		$s_where[] = " en_name LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getReturnBookLate($search=null){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('sp.branch_id');
    	$sql="SELECT bd.id,
    	b.borrow_no,(SELECT book_no FROM rms_book WHERE rms_book.id=bd.book_id LIMIT 1) AS bookno,
    	(SELECT title FROM rms_book WHERE rms_book.id=bd.book_id LIMIT 1) AS bookname,
    	(SELECT stu_code FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) AS stu_code,
    	(SELECT stu_enname FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) AS stu_name,
    	(SELECT name_en FROM rms_view WHERE rms_view.type=2 AND key_code=(SELECT sex FROM rms_student WHERE rms_student.stu_id=b.stu_id LIMIT 1))AS sex,
    	(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.items_type = 1 AND rms_itemsdetail.id = (SELECT grade FROM rms_student WHERE rms_student.stu_id=b.stu_id LIMIT 1)) AS grade,
    	
    	b.borrow_date,b.return_date,bd.borr_qty
    	FROM rms_borrow AS b,rms_borrowdetails AS bd
    	WHERE b.id=bd.borr_id
    	AND bd.is_full=0
    	AND b.is_completed=0";
    	$where = '';
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  b.borrow_no LIKE '%{$s_search}%'";
    		$s_where[]="  bd.borr_qty LIKE '%{$s_search}%'";
    		$s_where[]= "(SELECT stu_code FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[]= "(SELECT stu_enname FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    		
    	if($search["status_search"]>-1){
    		$where.=' AND b.status='.$search["status_search"];
    	}
    
    	if($search["cood_book"]>0){
    		$where.=' AND bd.book_id='.$search["cood_book"];
    	}
    
    	$order=" ORDER BY b.id DESC ";
    	$str_next = '+1 week';
    	$search['end_date']=date("Y-m-d", strtotime($search['end_date'].$str_next));
    	$to_date = (empty($search['end_date']))? '1': " b.return_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$to_date;
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getAllBookList($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    				b.id,
    				b.title,
    				bd.serial,
    				bd.barcode,
    				bd.is_borrow,
    				bd.is_broken,
					bd.note,	    				
    				b.author,
    				b.publisher,
    		  		(SELECT bb.block_name FROM rms_blockbook AS bb WHERE bb.id=b.block_id LIMIT 1) AS  block_name,
      				(SELECT c.name FROM rms_bcategory AS c WHERE c.id=b.cat_id ) AS  cat_name,
      				b.date,
		      		(SELECT CONCAT(rms_users.first_name,' ',last_name) FROM rms_users WHERE rms_users.id=b.user_id) AS user_name,
		      		b.user_id,
		      		(SELECT rms_view.name_en FROM rms_view WHERE rms_view.key_code=b.status AND rms_view.type=1 LIMIT 1 ) AS `status` 
		      	FROM 
    				rms_book AS b,
    				rms_book_detail as bd 
    			WHERE 
    				b.id = bd.book_id
    				and b.status=1
    		";
    	$order = " order by b.id ASC , bd.id ASC ";
    	$where = '';
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  b.title LIKE '%{$s_search}%'";
    		$s_where[]="  b.author LIKE '%{$s_search}%'";
    		$s_where[]="  b.publisher LIKE '%{$s_search}%'";
    		$s_where[]="  bd.serial LIKE '%{$s_search}%'";
    		$s_where[]="  bd.barcode LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search["cood_book"]>0){
    		$where.=' AND b.id='.$search["cood_book"];
    	}
    	if($search["block_id"]>0){
    		$where.=' AND b.block_id='.$search["block_id"];
    	}									
    	if($search["is_borrow"]>-1){
    		$where.=' AND bd.is_borrow='.$search["is_borrow"];
    	}
    	if($search["is_broken"]>-1){
    		$where.=' AND bd.is_broken='.$search["is_broken"];
    	}
    	$db_cat=new Library_Model_DbTable_DbCategory();
    	if($search["parent"]>0){
    		$where.=' AND b.cat_id IN ('.$db_cat->getAllCategoryUnlimit($search["parent"]).')';
    	} 
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getBorrowDetail($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    				bd.id,
    				b.borrow_no,
    				b.stu_id,
    				b.phone,
    				b.borrow_date,
    				b.return_date,
     				(SELECT stu_code FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) AS stu_code,
     				(SELECT stu_khname FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) AS stu_name, 
     				b.card_id,
     				b.name,
     				b.borrow_type,
     				bd.is_return,
     				bdt.serial,
     				bdt.barcode,
     				(SELECT name_kh FROM rms_view WHERE rms_view.key_code=b.borrow_type AND rms_view.type=13 LIMIT 1) AS `type`,
     				(SELECT sex FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) AS sex,        
     				(SELECT `name` FROM rms_bcategory WHERE rms_bcategory.id=(SELECT cat_id FROM rms_book WHERE rms_book.id=bd.book_id) LIMIT 1) AS cat_name,
     				(SELECT title FROM rms_book WHERE rms_book.id=bdt.book_id LIMIT 1)AS book_name,
     				(SELECT CONCAT(first_name,' ',last_name) FROM rms_users WHERE rms_users.id=b.user_id LIMIT 1) AS user_name
		      	FROM 
		      		rms_borrow AS b,
		      		rms_borrowdetails AS bd,
		      		rms_book_detail as bdt
		       	WHERE 
    				b.id=bd.borr_id 
    				and bd.book_id = bdt.id
    		";
    	
    	$where = '';
    	$from_date =(empty($search['start_date']))? '1': "b.borrow_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "b.borrow_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  b.borrow_no LIKE '%{$s_search}%'";
    		$s_where[]="  b.card_id LIKE '%{$s_search}%'";
    		$s_where[]="  b.name LIKE '%{$s_search}%'";
    		$s_where[]=" (SELECT stu_code FROM rms_student WHERE rms_student.stu_id=b.stu_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[]=" (SELECT stu_khname FROM rms_student WHERE rms_student.stu_id=b.stu_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[]=" (SELECT book_no FROM rms_book WHERE rms_book.id=bd.book_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[]=" (SELECT title FROM rms_book WHERE rms_book.id=bd.book_id LIMIT 1) LIKE '%{$s_search}%'";
     		$s_where[]="  bd.borr_qty LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	
    	$db_cat=new Library_Model_DbTable_DbCategory();
    	if($search["parent"]>0){
    		$where.=' AND (SELECT cat_id FROM rms_book WHERE rms_book.id=bd.book_id)  IN ('.$db_cat->getAllCategoryUnlimit($search["parent"]).')';
    	}
    	
    	if($search["is_return"]>-1){
    		$where.=' AND bd.is_return='.$search["is_return"];
    	}
    	
    	if($search["student_name"]>0){
    		$where.=' AND b.stu_id='.$search["student_name"];
    	}

    	if($search["is_type_bor"]>0){
    		$where.=' AND b.borrow_type='.$search["is_type_bor"];
    	}
    	
    	if($search['cood_book']>0){
    		$where.=' AND bd.book_id='.$search["cood_book"];
    	}
    	
    	$order=" ORDER BY b.id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getReturnBookDetail($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    				b.id,
    				a.return_no,
    				a.return_date,
          	 		c.card_id,
          	 		c.name,
          	 		c.borrow_type,
          	 		(SELECT name_kh FROM rms_view WHERE rms_view.key_code=c.borrow_type AND rms_view.type=13 LIMIT 1) AS `type`,
          	 		(SELECT stu_code FROM rms_student WHERE rms_student.stu_id=c.stu_id LIMIT 1) as stu_code,
          	 		(SELECT stu_khname FROM rms_student WHERE rms_student.stu_id=c.stu_id LIMIT 1) as stu_name,
          	 		e.serial,
          	 		e.barcode,
          	 		
	    			(SELECT `name` FROM rms_bcategory WHERE rms_bcategory.id=(SELECT cat_id FROM rms_book WHERE rms_book.id=e.book_id) LIMIT 1) AS cat_name,
	    			(SELECT title FROM rms_book WHERE rms_book.id=e.book_id LIMIT 1)AS book_name,
	    			(SELECT CONCAT(first_name,' ',last_name) FROM rms_users WHERE rms_users.id=a.user_id LIMIT 1) AS user_name
	    		FROM 
	    			rms_bookreturn AS a,
	    			rms_bookreturndetails AS b,
	    			rms_borrow as c,
	    			rms_borrowdetails AS d,
	    			rms_book_detail as e
	    		WHERE 
	    			a.id=b.return_id
	    			and b.borr_detail_id = d.id
	    			and c.id = d.borr_id
	    			AND b.book_id=e.id
    		";
    	 
    	$where = '';
    	$from_date =(empty($search['start_date']))? '1': "a.return_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "a.return_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	 
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  a.return_no LIKE '%{$s_search}%'";
    		$s_where[]="  c.card_id LIKE '%{$s_search}%'";
    		$s_where[]="  c.name LIKE '%{$s_search}%'";
    		$s_where[]=" (SELECT stu_code FROM rms_student WHERE rms_student.stu_id=c.stu_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[]=" (SELECT stu_khname FROM rms_student WHERE rms_student.stu_id=c.stu_id LIMIT 1) LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}

    	if($search["student_name"]>0){
    		$where.=' AND c.stu_id='.$search["student_name"];
    	}
    	
    	if($search["is_type_bor"]>0){
    		$where.=' AND c.borrow_type='.$search["is_type_bor"];
    	}
    	
    	if($search['cood_book']>0){
    		$where.=' AND (SELECT id FROM rms_book WHERE rms_book.id=e.book_id LIMIT 1)='.$search["cood_book"];
    	}
    	
    	$db_cat=new Library_Model_DbTable_DbCategory();
    	if($search["parent"]>0){
    		$where.=' AND (SELECT cat_id FROM rms_book WHERE rms_book.id=e.book_id)  IN ('.$db_cat->getAllCategoryUnlimit($search["parent"]).')';
    	}
    	$order=" ORDER BY b.id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getBorrowDetailByWeek($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT bd.id,b.borrow_no,b.stu_id,b.borrow_date,b.return_date,b.amount_week,
    	b.card_id,b.name,(SELECT name_kh FROM rms_view WHERE rms_view.key_code=b.borrow_type AND rms_view.type=13 LIMIT 1) AS `type`,
    	(SELECT sex FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) AS sex,
    	(SELECT `name` FROM rms_bcategory WHERE rms_bcategory.id=(SELECT cat_id FROM rms_book WHERE rms_book.id=bd.book_id) LIMIT 1) AS cat_name,
    	(SELECT book_no FROM rms_book WHERE rms_book.id=bd.book_id LIMIT 1)AS book_no,
    	(SELECT title FROM rms_book WHERE rms_book.id=bd.book_id LIMIT 1)AS book_name,
    	(SELECT CONCAT(first_name,' ',last_name) FROM rms_users WHERE rms_users.id=bd.user_id LIMIT 1) AS user_name,
    	bd.borr_qty,bd.is_full
    	FROM rms_borrow AS b,rms_borrowdetails AS bd
    	WHERE b.id=bd.borr_id ";
    	 
    	$where = '';
//     	$from_date =(empty($search['start_date']))? '1': "b.borrow_date >= '".$search['start_date']." 00:00:00'";
//     	$to_date = (empty($search['end_date']))? '1': "b.borrow_date <= '".$search['end_date']." 23:59:59'";
//     	$where = " AND ".$from_date." AND ".$to_date;
    	 
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  b.borrow_no LIKE '%{$s_search}%'";
    		$s_where[]="  b.card_id LIKE '%{$s_search}%'";
    		$s_where[]="  b.name LIKE '%{$s_search}%'";
//     		$s_where[]=" (SELECT stu_code FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) LIKE '%{$s_search}%'";
//     		$s_where[]=" (SELECT stu_enname FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[]=" (SELECT book_no FROM rms_book WHERE rms_book.id=bd.book_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[]=" (SELECT title FROM rms_book WHERE rms_book.id=bd.book_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[]="  bd.borr_qty LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	 
    	$db_cat=new Library_Model_DbTable_DbCategory();
    	if($search["parent"]>0){
    		$where.=' AND (SELECT cat_id FROM rms_book WHERE rms_book.id=bd.book_id)  IN ('.$db_cat->getAllCategoryUnlimit($search["parent"]).')';
    	}
    	 
    	if($search["is_full"]>-1){
    		$where.=' AND bd.is_full='.$search["is_full"];
    	}
    	
    	if($search["student_name"]>0){
    		$where.=' AND b.stu_id='.$search["student_name"];
    	}
    	
    	if($search["is_type_bor"]>0){
    		$where.=' AND b.borrow_type='.$search["is_type_bor"];
    	}
    	
    	if($search["cood_book"]>0){
    		$where.=' AND bd.book_id='.$search["cood_book"];
    	}
    	 
    	$order=" ORDER BY b.id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getBookUnavailable($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT b.id,b.book_no,b.title,b.author,b.publisher,
    	(SELECT c.name FROM rms_bcategory AS c WHERE c.id=b.cat_id ) AS  cat_name,
    	b.qty AS qty_curr,b.qty_after,b.unit_price,b.date,
    	(SELECT CONCAT(rms_users.first_name,' ',last_name) FROM rms_users WHERE rms_users.id=b.user_id) AS user_name,b.user_id,
    	(SELECT rms_view.name_en FROM rms_view WHERE rms_view.key_code=b.status AND rms_view.type=1 LIMIT 1 ) AS `status`
    	FROM rms_book AS b WHERE b.status=1 AND b.qty_after=0 ";
    	 
    	$where = '';
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  b.book_no LIKE '%{$s_search}%'";
    		$s_where[]="  b.title LIKE '%{$s_search}%'";
    		$s_where[]="  b.author LIKE '%{$s_search}%'";
    		$s_where[]="  b.publisher LIKE '%{$s_search}%'";
    		$s_where[]="  b.qty LIKE '%{$s_search}%'";
    		$s_where[]="  b.qty_after LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	 
    	if($search["cood_book"]>0){
    		$where.=' AND b.id='.$search["cood_book"];
    	}
    	 
    	$db_cat=new Library_Model_DbTable_DbCategory();
    	if($search["parent"]>0){
    		$where.=' AND b.cat_id IN ('.$db_cat->getAllCategoryUnlimit($search["parent"]).')';
    	}
    	//echo $sql.$where;
    	return $db->fetchAll($sql.$where);
    }
    
    function getPurchaseDetail($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    				bd.id,
    				b.purchase_no,
    				b.date_purchase,
			    	(SELECT `name` FROM rms_bcategory WHERE rms_bcategory.id=rms_book.cat_id LIMIT 1) AS cat_name,
			    	(SELECT `block_name` FROM rms_blockbook WHERE rms_blockbook.id=rms_book.block_id LIMIT 1) AS block_name,
			    	rms_book.title AS book_name,
			    	(SELECT CONCAT(first_name,' ',last_name) FROM rms_users WHERE rms_users.id=b.user_id LIMIT 1) AS user_name,
			    	bdt.serial,
			    	bdt.barcode
    			FROM 
    				rms_bookpurchase AS b,
    				rms_bookpurchasedetails AS bd,
    				rms_book_detail as bdt,
    				rms_book
    			WHERE 
    				b.id=bd.purchase_id
    				and bdt.id=bd.book_id 
    				and rms_book.id = bdt.book_id
    		";
    	 
    	$where = '';
    	$from_date =(empty($search['start_date']))? '1': "b.date_purchase >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "b.date_purchase <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	 
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  b.purchase_no LIKE '%{$s_search}%'";
    		$s_where[]="  rms_book.title LIKE '%{$s_search}%'";
    		$s_where[]="  bdt.serial LIKE '%{$s_search}%'";
    		$s_where[]="  bdt.barcode LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	 
    	$db_cat=new Library_Model_DbTable_DbCategory();
    	if($search["parent"]>0){
    		$where.=' AND rms_book.cat_id IN ('.$db_cat->getAllCategoryUnlimit($search["parent"]).')';
    	}
    	 
    	if($search["block_id"]>0){
    		$where.=' AND rms_book.block_id ='.$search["block_id"];
    	}
    	 
    	if($search["cood_book"]>0){
    		$where.=' AND rms_book.id='.$search["cood_book"];
    	}
    	 
    	$order=" ORDER BY b.id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getBrokenDetail($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    				brd.id,
    				br.broke_no,
    				br.date_broken,
			    	(SELECT `name` FROM rms_bcategory WHERE rms_bcategory.id=b.cat_id LIMIT 1) AS cat_name,
			    	(SELECT `block_name` FROM rms_blockbook WHERE rms_blockbook.id=b.block_id LIMIT 1) AS block_name,
			    	b.title AS book_name,
			    	bdt.serial,
			    	bdt.barcode,
			    	(SELECT CONCAT(first_name,' ',last_name) FROM rms_users WHERE rms_users.id=br.user_id LIMIT 1) AS user_name
		    	FROM 
		    		rms_bookbroken AS br,
		    		rms_bookbrokendetails AS brd,
		    		rms_book_detail as bdt,
		    		rms_book as b
    			WHERE 
    				br.id=brd.broken_id 
    				and brd.book_id = bdt.id
    				and b.id = bdt.book_id
    		";
    
    	$where = '';
    	$from_date =(empty($search['start_date']))? '1': "br.date_broken >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "br.date_broken <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  br.broke_no LIKE '%{$s_search}%'";
    		$s_where[]="  b.title LIKE '%{$s_search}%'";
    		$s_where[]="  bdt.serial LIKE '%{$s_search}%'";
    		$s_where[]="  bdt.barcode LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    
    	$db_cat=new Library_Model_DbTable_DbCategory();
    	if($search["parent"]>0){
    		$where.=' AND b.cat_id IN ('.$db_cat->getAllCategoryUnlimit($search["parent"]).')';
    	}
    
    	if($search["cood_book"]>0){
    		$where.=' AND b.id='.$search["cood_book"];
    	}
    
    	if($search["block_id"]>0){
    		$where.=' AND b.block_id ='.$search["block_id"];
    	}
    
    	$order=" ORDER BY b.id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getBookQtykAll($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT SUM(qty_after) AS qty_after FROM rms_book WHERE `status`=1";
    	return $db->fetchRow($sql);
    }
    
    function getBorrowQtykAll($search=null){
    	$db=$this->getAdapter();
    	$sql=" SELECT SUM(bd.borr_qty) As borr_qty FROM rms_borrow AS b,rms_borrowdetails AS bd
                WHERE b.id=bd.borr_id";
    	$where = '';
    	$from_date =(empty($search['start_date']))? '1': "b.borrow_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "b.borrow_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	return $db->fetchRow($sql.$where);
    }
   
    function getReturnQtykAll($search=null){
    	$db=$this->getAdapter();
    	$sql=" SELECT SUM(bd.borr_qty) As borr_qty FROM rms_bookreturn AS b,rms_bookreturndetails AS bd
      		   WHERE b.id=bd.return_id";
    	$where = '';
    	$from_date =(empty($search['start_date']))? '1': "b.return_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "b.return_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	return $db->fetchRow($sql.$where);
    }
    
    function getPurhcaseQtykAll($search=null){
    	$db=$this->getAdapter();
    	$sql=" SELECT SUM(bd.borr_qty) As borr_qty FROM rms_bookpurchase AS b,rms_bookpurchasedetails AS bd
      			 WHERE b.id=bd.purchase_id";
    	$where = '';
    	$from_date =(empty($search['start_date']))? '1': "b.date_order >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "b.date_order <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	return $db->fetchRow($sql.$where);
    }
    
    function getBrokenQtykAll($search=null){
    	$db=$this->getAdapter();
    	$sql=" SELECT SUM(bd.borr_qty) As borr_qty FROM rms_bookbroken AS b,rms_bookbrokendetails AS bd
      			 WHERE b.id=bd.broken_id";
    	$where = '';
    	$from_date =(empty($search['start_date']))? '1': "b.date_broken >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "b.date_broken <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	return $db->fetchRow($sql.$where);
    }
}
   
    
   