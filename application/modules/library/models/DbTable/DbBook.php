<?php

class Library_Model_DbTable_DbBook extends Zend_Db_Table_Abstract
{
 	protected $_name = 'rms_book';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    
    function getAllBook($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT b.id,b.book_no,b.title,b.author,b.serial_no,
    			  (SELECT c.block_name FROM rms_blockbook AS c WHERE c.id=b.block_id) AS block_name,
			      (SELECT c.name FROM rms_bcategory AS c WHERE c.id=b.cat_id) AS cat_name,
			        b.qty_after,b.unit_price,b.date,
			      (SELECT first_name FROM rms_users WHERE id=b.user_id LIMIT 1) AS user_name,
			      (SELECT name_en FROM rms_view WHERE key_code=b.status LIMIT 1) AS `status`
			      FROM rms_book AS b 
			      WHERE b.status=1 ";
    	$where = '';
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]= " b.book_no LIKE '%{$s_search}%'";
    		$s_where[]="  b.title LIKE '%{$s_search}%'";
    		$s_where[]= " b.author LIKE '%{$s_search}%'";
    		$s_where[]= " b.serial_no LIKE '%{$s_search}%'";
    		
    		$s_where[]="  b.qty_after LIKE '%{$s_search}%'";
    		$s_where[]= " b.unit_price LIKE '%{$s_search}%'";
    		$s_where[]= " b.serial_no LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
        $db_cat=new Library_Model_DbTable_DbCategory();
    	if($search["parent"]>0){
    		$where.=' AND b.cat_id IN ('.$db_cat->getAllCategoryUnlimit($search["parent"]).')';
    	}
    	
    	if($search["block_id"] > 0){
    		$where.=' AND b.block_id='.$search['block_id'];
    	}
    	
    	if($search["status_search"]>-1){
    	    $where.=' AND b.`status`='.$search["status_search"];
    	}
    	
    	$order=" ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
 
	public function addBook($data){
			$db = $this->getAdapter();
			$session_user=new Zend_Session_Namespace('authstu');
		    $userName=$session_user->user_name;
		    $GetUserId= $session_user->user_id;
		    
// 		    $adapter = new Zend_File_Transfer_Adapter_Http();
// 		    $part = PUBLIC_PATH.'/images';
// 		    $adapter->setDestination($part);
// 		    $adapter->receive();
// 		    $photo = $adapter->getFileInfo();
		    	
// 		    if(!empty($photo['photo']['name'])){
// 		    	$pho_name = $photo['photo']['name'];
// 		    }else{
// 		    	$pho_name = '';
// 		    }
		    
			$arr = array(
					'book_no'	=>	$data["book_id"],
					'title'		=>	$data["book_name"],
					'author'	=>	$data["author_name"],
					'serial_no'	=>	$data["serial_no"],
					'cat_id'	=>	$data["parent_id"],
					'block_id'	=>	$data["block_id"],
					//'photo'		=>	$pho_name,
					'publisher'	=>	$data["publisher"],
					'qty'		=>	$data["qty"],
					'qty_after'	=>	$data["qty"],
					'unit_price'=>	$data["unit_price"],
					'date'		=>	date('Y-m-d'),
					'status'	=>	$data["statuss"],
					'note'		=>	$data["remark"],
					"user_id"   =>  $GetUserId,
			);
			$this->_name = "rms_book";
			$this->insert($arr);
	}
	 
	public function editBook($data){ 
			$db = $this->getAdapter();
			$session_user=new Zend_Session_Namespace('authstu');
		    $userName=$session_user->user_name;
		    $GetUserId= $session_user->user_id;
		    
// 		    $adapter = new Zend_File_Transfer_Adapter_Http();
// 		    $part = PUBLIC_PATH.'/images';
// 		    $adapter->setDestination($part);
// 		    $adapter->receive();
// 		    $photo = $adapter->getFileInfo();
		    	
// 		    if(!empty($photo['photo']['name'])){
// 		    	$pho_name = $photo['photo']['name'];
// 		    }else{
// 		    	$pho_name = $data['old_photo'];
// 		    }
		    
			$arr = array(
					'book_no'	=>	$data["book_id"],
					'title'		=>	$data["book_name"],
					'author'	=>	$data["author_name"],
					'serial_no'	=>	$data["serial_no"],
					'cat_id'	=>	$data["parent_id"],
					//'photo'		=>	$pho_name,
					'publisher'	=>	$data["publisher"],
					'block_id'	=>	$data["block_id"],
					'qty'		=>	$data["qty"],
					'qty_after'	=>	$data["qty"],
					'unit_price'=>	$data["unit_price"],
					'date'		=>	date('Y-m-d'),
					'status'	=>	$data["statuss"],
					'note'		=>	$data["remark"],
					"user_id"   =>  $GetUserId,
			);
			$this->_name = "rms_book";
			$where=" id=".$data['id'];
			$this->update($arr, $where);
	}
	
	public function getCategory($parent = 0, $spacing = '', $cate_tree_array = ''){
		$db=$this->getAdapter();
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();
		$sql="SELECT c.`id`,c.`parent_id`,c.name 
		      FROM `rms_bcategory` AS c WHERE c.`status`=1 AND c.`parent_id`=$parent ORDER BY id DESC";
		$query = $db->fetchAll($sql);
		$stmt = $db->query($sql);
		$rowCount = count($query);
		$id='';
		if ($rowCount > 0) {
			foreach ($query as $row){
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getCategory($id=$row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		return $cate_tree_array;
	}
	
	function getCategoryById($id){
		$db=$this->getAdapter();
		$sql="SELECT id,`name`,parent_id,remark,`status` FROM rms_bcategory  WHERE id=$id";
		return $db->fetchRow($sql);
	}
	
	function getBookNo(){
		$db=$this->getAdapter();
		$sql="SELECT id FROM rms_book WHERE 1 ORDER BY id DESC";
		$row=$db->fetchOne($sql);
		$fex='b';
		if(!empty($row)){
			for($i=0;$i<4;$i++){
				$fex.='0';
			}
		}
		return $fex.$row;
		
	}
	
	function getCategoryAll(){
		$db=$this->getAdapter();
		$sql="SELECT id,`name` FROM rms_bcategory WHERE  `status` = 1 AND name!=''";
		return $db->fetchAll($sql);		
	}
	
	function getBlockAll(){
		$db=$this->getAdapter();
		$sql="SELECT id,block_name AS `name` FROM rms_blockbook WHERE `status`=1 AND block_name!=''";
		return $db->fetchAll($sql);
	}
	
	function getBookRowById($id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_book WHERE id=$id";
		return $db->fetchRow($sql);
	}
	
	function getTotalBookEmpty(){
		$db=$this->getAdapter();
		$sql=" SELECT qty_after FROM rms_book WHERE `status`=1 AND qty_after=0";
		return $db->fetchAll($sql);
	}
	
	function getBookNotReturn(){
		$db=$this->getAdapter();
		$sql=" SELECT b.id,bd.borr_qty
		       FROM  rms_borrow AS b,rms_borrowdetails AS bd
		       WHERE b.id=bd.borr_id 
		       AND bd.is_full=0 ";
		return $db->fetchAll($sql);
	}
	
	function getBorrowThisDay(){
		$date=date('Y-m-d');
		$db=$this->getAdapter();
		$sql="SELECT b.id,bd.borr_qty
		       FROM  rms_borrow AS b,rms_borrowdetails AS bd
		       WHERE b.id=bd.borr_id 
		       AND bd.is_full=0 
		       AND bd.date='$date'";
		return $db->fetchAll($sql);
	}
	
	function getReturnThisDay(){
		$date=date('Y-m-d');
		$db=$this->getAdapter();
		$sql="SELECT b.id,bd.borr_qty
		       FROM  rms_bookreturn AS b,rms_bookreturndetails AS bd
		       WHERE b.id=bd.return_id 
		       AND b.return_date='$date'";
		return $db->fetchAll($sql);
	}
	
	public function getBookQty($book_id){
		$db=$this->getAdapter();
		$sql=" SELECT id,book_no,qty_after,qty FROM rms_book WHERE id=$book_id AND `status`=1 ";
		$row = $db->fetchRow($sql);
		if(empty($row)){
			$session_user=new Zend_Session_Namespace('authstu');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$array = array(
					'qty'		=>	0,
					'qty_after'	=>	0,
					'date'		=>	date('Y-m-d'),
					'status'	=>	1,
					"user_id"   =>  $GetUserId,
			);
			$this->_name="rms_book";
			$this->insert($array);
			$sql=" SELECT id,book_no,qty_after,qty FROM rms_book WHERE id=$book_id AND `status`=1 ";
			return $row = $db->fetchRow($sql);
		}else{
	
			return $row;
		}
	}
	
	function getNearDayReturnBookLate($search=null){
		$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission('sp.branch_id');
		$sql="SELECT  SUM(bd.borr_qty) AS borr_qty
		FROM rms_borrow AS b,rms_borrowdetails AS bd
		WHERE b.id=bd.borr_id
		AND bd.is_full=0
		AND b.is_completed=0";
		$where = '';
		$search['end_date']=date("Y-m-d");
		$str_next = '+1 week';
		$search['end_date']=date("Y-m-d", strtotime($search['end_date'].$str_next));
		$to_date = (empty($search['end_date']))? '1': " b.return_date <= '".$search['end_date']." 23:59:59'";
		$where .= " AND ".$to_date;
		return $db->fetchRow($sql.$where);
	}
	
	function getStudentNearDayReturnBook($search=null){
		$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission('sp.branch_id');
		$sql=" SELECT  SUM(b.stu_id) AS stu_id
		FROM rms_borrow AS b,rms_borrowdetails AS bd
		WHERE b.id=bd.borr_id
		AND bd.is_full=0
		AND b.is_completed=0 
		";
		$where = '';
		$search['end_date']=date("Y-m-d");
		$str_next = '+1 week';
		$search['end_date']=date("Y-m-d", strtotime($search['end_date'].$str_next));
		$to_date = (empty($search['end_date']))? '1': " b.return_date <= '".$search['end_date']." 23:59:59'";
		$where .= " AND ".$to_date;
		$group=" GROUP BY b.stu_id";
		return $db->fetchAll($sql.$where.$group);
	}
	
	function getPurchaseDay(){
		$date=date('Y-m-d');
		$db=$this->getAdapter();
		$sql="SELECT b.id,SUM(bd.borr_qty) AS borr_qty
		FROM  rms_bookpurchase AS b,rms_bookpurchasedetails AS bd
		WHERE b.id=bd.purchase_id
		AND b.date_order='$date'";
		return $db->fetchRow($sql);
	}
	
	function getBrokenDay(){
		$date=date('Y-m-d');
		$db=$this->getAdapter();
		$sql="SELECT b.id,SUM(bd.borr_qty) AS borr_qty
		FROM  rms_bookbroken AS b,rms_bookbrokendetails AS bd
		WHERE b.id=bd.broken_id
		AND b.date_broken='$date'";
		return $db->fetchRow($sql);
	}
	
	
}



