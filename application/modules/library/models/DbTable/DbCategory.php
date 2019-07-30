<?php

class Library_Model_DbTable_DbCategory extends Zend_Db_Table_Abstract
{
 	protected $_name = 'rms_bcategory';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
 
    public function getAllCategoryVandy($parent = 0, $spacing ='', $cate_tree_array = '',$status='',$search){
    	$db=$this->getAdapter();
    	if (!is_array($cate_tree_array)){$cate_tree_array = array();}
    	$sql="SELECT 
    				c.`id`,
			    	c.name,
			    	c.`parent_id`,
			    	c.remark,
			    	(SELECT first_name FROM rms_users WHERE rms_users.id=c.user_id LIMIT 1) AS user_name,
			    	(SELECT name_en FROM rms_view WHERE key_code=c.status LIMIT 1) AS `status` 
		    	FROM 
    				`rms_bcategory` AS c 
    			WHERE 
    				1
    				AND c.`parent_id` = $parent
    		";
    	
    	
        if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['title']));
    		$s_where[] = " c.`name` LIKE '%{$s_search}%'";
    		$sql .=' AND ('.implode(' OR ',$s_where).')';
    	} 
    	
    	if ($search["status_search"]>-1){
    		$sql.=" AND c.`status`='".$search["status_search"]."'";
    	}
    	if ($search["parent"]>0){
    		$sql.=" AND c.`id`='".$search["parent"]."'";
    	}
    	
//     	$sql.=" ORDER BY c.id ASC";
//     	return $db->fetchAll($sql);
    	 
    	$sql.=" ORDER BY c.id ASC";
    	
    	//echo $sql."<br>";
    	
    	$query = $db->fetchAll($sql);
    	$rowCount = count($query);
    	$id='';
    	$is_parent=0;
    	if ($rowCount > 0) {
    		foreach ($query as $row){
    			$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name'],"status" => $row['status'],"remark" => $row['remark'],"user_name" => $row['user_name']);
    			$cate_tree_array = $this->getAllCategoryVandy($id=$row['id'], $spacing. '  - ', $cate_tree_array,$status,$search);
    		}
    	}
    	return $cate_tree_array;
    }
	public function add($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["cat_name"],
				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["note"],
				"user_id"       =>  $this->getUserId(),
		);
		$this->_name = "rms_bcategory";
		$this->insert($arr);
	}
	public function edit($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["cat_name"],
				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["note"],
				"user_id"       =>  $this->getUserId(),
		);
		$this->_name = "rms_bcategory";
		$where=" id=".$data['id'];
		$this->update($arr, $where);
	}
	
	public function ajaxAddCategory($data){
		$arr = array(
				'name'			=>	$data["cat_name"],
				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'remark'		=>	$data["note_cate"],
				"user_id"       =>  $this->getUserId(),
		);
		$this->_name = "rms_bcategory";
		return $this->insert($arr);
	}
	
	public function ajaxAddBlock($data){
		$arr = array(
				'block_name'	=>	$data["block_name"],
				'date'			=>	new Zend_Date(),
				'remark'		=>	$data["b_note"],
				"user_id"       =>  $this->getUserId(),
		);
		$this->_name = "rms_blockbook";
		return $this->insert($arr);
	}
	
	public function ajaxAddBook($data){
		$arr = array(
			'title'		=>	$data["book_name"],
			'author'	=>	$data["author_name"],
			'cat_id'	=>	$data["cat_id"],
			'block_id'	=>	$data["block_id"],
			'publisher'	=>	$data["publisher"],
			'note'		=>	$data["note"],
			'date'		=>	date('Y-m-d'),
			"user_id"   =>  $this->getUserId(),
		);
		$this->_name = "rms_book";
		return $this->insert($arr);
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
		$sql="SELECT id,`name`,parent_id,remark,`status` FROM rms_bcategory  WHERE id=$id ";
		return $db->fetchRow($sql);
	}
	
	//get all category
	function getAllCategoryUnlimit($category=0){
		$db=$this->getAdapter();
		$r='';
		$rs='';
		$sql="SELECT c.`id`,c.name,c.`parent_id` FROM `rms_bcategory` AS c WHERE  c.`parent_id`=$category ";
		$query = $db->fetchAll($sql);
		$rowCount = count($query);
		if($rowCount>0){
			if(!empty($query))foreach ($query as $rs){
				$r=$rs['id'].",".$category;
				$rs=$this->subCat($rs['id']);
			}
		}else{
			$r=$category;
		}
		return   $r."".$rs;
	}
	
	function subCat($id)
	{
		$db=$this->getAdapter();
		$sql="SELECT c.`id`,c.name,c.`parent_id` FROM `rms_bcategory` AS c WHERE  c.`parent_id`=$id ";
		$query = $db->fetchAll($sql);
		$rowCount = count($query);
		$r='';
		$rs='';
		if($rowCount>0){
			if(!empty($query))foreach ($query as $rs){
				$r=",".$rs['id'];
				$rs=$this->subCat($rs['id']);
			}
		}
		return $r."".$rs;
	}
	
	function getBookQty($book_id){
		$db=$this->getAdapter();
		$sql="SELECT qty_after FROM rms_book WHERE id=$book_id";
		return $db->fetchOne($sql);
	}
	
	function getReturnBook($stu_id){
		$db=$this->getAdapter();
		$sql="SELECT bd.id AS borrow_id,b.phone,b.borrow_no,bd.borr_qty,(SELECT book_no FROM rms_book WHERE rms_book.id=bd.book_id  LIMIT 1) AS book_no,
		        (SELECT title FROM rms_book WHERE rms_book.id=bd.book_id  LIMIT 1) AS book_name,bd.return_date,bd.book_id,
		        (SELECT v.name_en FROM rms_view AS v WHERE v.key_code=b.borrow_type AND  v.type=13 LIMIT 1) AS borrow_type
				FROM rms_borrow AS b,rms_borrowdetails AS bd 
				WHERE b.id=bd.borr_id
				AND b.id=$stu_id
				AND bd.is_full=0
       		    AND b.is_completed=0 ";
		return $db->fetchAll($sql);
	}
	
	function getReturnBookDetail($id){
		$db=$this->getAdapter();
		$sql=" SELECT bd.id AS return_id,b.return_no,bd.borr_qty,(SELECT book_no FROM rms_book WHERE rms_book.id=bd.book_id  LIMIT 1) AS book_no,
		(SELECT title FROM rms_book WHERE rms_book.id=bd.book_id  LIMIT 1) AS book_name,bd.date_delay,bd.delay_qty,bd.book_id,b.return_date,
		(SELECT rms_borrowdetails.borr_qty FROM rms_borrowdetails WHERE rms_borrowdetails.id=bd.borr_detail_id LIMIT 1) AS oldbor_qty ,
		(SELECT rms_borrowdetails.id FROM rms_borrowdetails WHERE rms_borrowdetails.id=bd.borr_detail_id LIMIT 1) AS borrow_id,
		(SELECT v.name_en FROM rms_view AS v WHERE v.key_code=bor.borrow_type AND  v.type=13 LIMIT 1) AS borrow_type 
		FROM rms_bookreturn AS b,rms_bookreturndetails AS bd,rms_borrow AS bor
		WHERE b.id=bd.return_id
		AND bd.return_id=$id
		AND b.is_completed=0
		AND bor.id=b.borrow_id
		";
		return $db->fetchAll($sql);
	}
	
	function getAllBlockName(){
		$db=$this->getAdapter();
		$sql=" SELECT id,block_name AS `name` FROM rms_blockbook WHERE `status`=1 AND block_name!='' ORDER BY id DESC ";
		return $db->fetchAll($sql);
	}
	
	function getAllBookOpt(){
		$db=$this->getAdapter();
		$sql="SELECT id,CONCAT(title) AS name FROM rms_book WHERE STATUS=1";
		$order=" ORDER BY id DESC";
		return $db->fetchAll($sql.$order);
	}
	
	function getAllStudentOpt(){
		$db=$this->getAdapter();
		$sql="SELECT s.stu_id AS stu_id,CONCAT(s.stu_enname) AS name 
				FROM rms_student AS s,rms_borrow AS b,rms_borrowdetails AS bd
					WHERE s.status=1 
					AND s.is_subspend=0 
					AND b.stu_id=s.stu_id
					AND b.id=bd.borr_id ";
		$order=" GROUP BY b.stu_id ORDER BY stu_type DESC ";
		$sqlconver=utf8_decode($sql);
		return $db->fetchAll($sqlconver.$order);
	}
	
}







