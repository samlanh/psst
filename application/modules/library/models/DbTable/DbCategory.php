<?php

class Library_Model_DbTable_DbCategory extends Zend_Db_Table_Abstract
{
 	protected $_name = 'rms_bcategory';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
 
    public function getAllCategoryVandy($parent = 0, $spacing ='', $cate_tree_array = '',$status='',$search){
    	$db=$this->getAdapter();
    	if (!is_array($cate_tree_array))
    		$cate_tree_array = array();
    	$sql="SELECT c.`id`,
    	c.name,
    	c.`parent_id`,(SELECT v.`name_en` FROM rms_view AS v WHERE v.`type`=1  AND C.`status`=v.`key_code` LIMIT 1) AS status ,
    	c.remark,(SELECT first_name FROM rms_users WHERE rms_users.id=c.user_id LIMIT 1) AS user_name  
    	FROM `rms_bcategory` AS c WHERE  1 ";
    	 
        if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['title']));
    		$s_where[] = " c.`name` LIKE '%{$s_search}%'";
    		$sql .=' AND ('.implode(' OR ',$s_where).')';
    	} 
    	if (@$search["status"]!=''){
    		$sql.=" AND c.`status`='".$search["status"]."'";
    	}
    	if (@$search["parent"]>0){
    		$sql.=" AND c.`id`='".$search["parent"]."'";
    	}else{
    		//$sql.=" AND c.`parent_id`=$parent";
    	}
    	if (@$search["name"]!=''){
    		$sql.=" AND c.`name`='".$search["name"]."'";
    	}else{
    		//$sql.=" AND c.`parent_id`=$parent";
    	}
    	if(@$search["parent"]=='' and @$search["name"]=='' and @$search["status"]==''){
    		$sql.=" AND c.`parent_id`=$parent";
    	}
    	 
    	$sql.=" ORDER BY c.id ASC";
    	$query = $db->fetchAll($sql);
    	$stmt = $db->query($sql);
    	$rowCount = count($query);
    	$id='';
    	if ($rowCount > 0) {
    		foreach ($query as $row){
    			$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name'],"status" => $row['status'],"remark" => $row['remark'],"user_name" => $row['user_name'],$status);
    			$cate_tree_array = $this->getAllCategoryVandy($id=$row['id'], $spacing. '  - ', $cate_tree_array,$status,$search='');
    		}
    	}
    	//echo $sql;
    	return $cate_tree_array;
    }
	public function add($data){
			$db = $this->getAdapter();
			$session_user=new Zend_Session_Namespace('auth');
		    $userName=$session_user->user_name;
		    $GetUserId= $session_user->user_id;
			$arr = array(
					'name'			=>	$data["cat_name"],
					'parent_id'		=>	$data["parent"],
					'date'			=>	new Zend_Date(),
					'status'		=>	$data["status"],
					'remark'		=>	$data["note"],
					"user_id"       =>  $GetUserId,
			);
			$this->_name = "rms_bcategory";
			$this->insert($arr);
	}
	
	public function ajaxAddCategory($data){
		//return  $data;
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$arr = array(
				'name'			=>	$data["cat_name"],
				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["note"],
				"user_id"       =>  $GetUserId,
		);
		$this->_name = "rms_bcategory";
		return $this->insert($arr);
	}
	 
	public function edit($data){
		//print_r($data);exit();
				$db = $this->getAdapter();
				$session_user=new Zend_Session_Namespace('auth');
			    $userName=$session_user->user_name;
			    $GetUserId= $session_user->user_id;
				$arr = array(
						'name'			=>	$data["cat_name"],
						'parent_id'		=>	$data["parent"],
						'date'			=>	new Zend_Date(),
						'status'		=>	$data["status"],
						'remark'		=>	$data["note"],
						"user_id"       =>  $GetUserId,
				);
				$this->_name = "rms_bcategory";
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
		$sql=" SELECT bd.id as borrow_id,b.borrow_no,bd.borr_qty,(SELECT book_no FROM rms_book WHERE rms_book.id=bd.book_id  LIMIT 1) AS book_no,
		        (SELECT title FROM rms_book WHERE rms_book.id=bd.book_id  LIMIT 1) AS book_name,b.return_date,bd.book_id
				FROM rms_borrow AS b,rms_borrowdetails AS bd 
				WHERE b.id=bd.borr_id
				AND b.stu_id=$stu_id
				AND bd.is_full=0
       		    AND b.is_completed=0 ";
		return $db->fetchAll($sql);
	}
	
	function getReturnBookDetail($id){
		$db=$this->getAdapter();
		$sql=" SELECT bd.id AS return_id,b.return_no,bd.borr_qty,(SELECT book_no FROM rms_book WHERE rms_book.id=bd.book_id  LIMIT 1) AS book_no,
		(SELECT title FROM rms_book WHERE rms_book.id=bd.book_id  LIMIT 1) AS book_name,b.return_date,bd.book_id,
		(SELECT rms_borrowdetails.borr_qty FROM rms_borrowdetails WHERE rms_borrowdetails.id=bd.borr_detail_id LIMIT 1) AS oldbor_qty ,
		(SELECT rms_borrowdetails.id FROM rms_borrowdetails WHERE rms_borrowdetails.id=bd.borr_detail_id LIMIT 1) AS borrow_id 
		FROM rms_bookreturn AS b,rms_bookreturndetails AS bd
		WHERE b.id=bd.return_id
		AND bd.return_id=$id
		AND b.is_completed=0
		";
		return $db->fetchAll($sql);
	}
	
	
	
}







