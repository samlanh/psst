<?php

class Library_Model_DbTable_DbBook extends Zend_Db_Table_Abstract
{
 	protected $_name = 'rms_book';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    
    function getAllBook($search=null){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    	}else{ // English
    		$label = "name_en";
    	}
    	$sql="SELECT 
    				b.id,
    				b.title,
    				b.author,
    				(SELECT c.name FROM rms_bcategory AS c WHERE c.id=b.cat_id limit 1) AS cat_name,
    			  	(SELECT c.block_name FROM rms_blockbook AS c WHERE c.id=b.block_id limit 1) AS block_name,
    			  	(select count(id) from rms_book_detail as bdt where b.id=bdt.book_id and bdt.status=1 limit 1) as total_book,
			    	(select count(id) from rms_book_detail as bdt where b.id=bdt.book_id and bdt.is_borrow=0 and bdt.is_broken=0 and bdt.status=1 limit 1) as total_available,
			    	(select count(id) from rms_book_detail as bdt where b.id=bdt.book_id and bdt.is_borrow=1 and bdt.is_broken=0 and bdt.status=1 limit 1) as total_borrow,
			    	(select count(id) from rms_book_detail as bdt where b.id=bdt.book_id and bdt.is_broken=1 and bdt.status=1 limit 1) as total_broken,
			        b.date,
			      	(SELECT first_name FROM rms_users WHERE id=b.user_id LIMIT 1) AS user_name,
			      	(select $label from rms_view where type=1 and key_code = b.status) as status
			      	
			      FROM 
			      	rms_book AS b 
			      WHERE 
    				b.title!='' 
    		";
    	
    	$where = '';
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  b.title LIKE '%{$s_search}%'";
    		$s_where[]= " b.author LIKE '%{$s_search}%'";
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
    	
    	$order=" ORDER BY b.id DESC ";
    	//echo $sql.$where;
    	return $db->fetchAll($sql.$where.$order);
    }
 
	public function addBook($data){
		$db = $this->getAdapter();
		$arr = array(
				'title'		=>	$data["book_name"],
				'author'	=>	$data["author_name"],
				'publisher'	=>	$data["publisher"],
				
				'cat_id'	=>	$data["cat_id"],
				'block_id'	=>	$data["block_id"],
				
				'note'		=>	$data["remark"],
				
				'date'		=>	date('Y-m-d'),
				"user_id"   =>  $this->getUserId(),
		);
		$this->_name = "rms_book";
		$book_id = $this->insert($arr);
		
		if(!empty($data["identity"])){
			$ids = explode(",", $data['identity']);
			foreach ($ids as $i){
				$array = array(
					'book_id'	=> $book_id,
					'serial'	=> $data['serial_'.$i],
					'barcode'	=> $data['barcode_'.$i],
					'note'	  	=> $data['note_'.$i],
				);
				$this->_name="rms_book_detail";
				$this->insert($array);
			}
		}
	}
	 
	public function editBook($data,$id){ 
		$db = $this->getAdapter();
		$arr = array(
				'title'		=>	$data["book_name"],
				'author'	=>	$data["author_name"],
				'publisher'	=>	$data["publisher"],
				
				'cat_id'	=>	$data["cat_id"],
				'block_id'	=>	$data["block_id"],
				'note'		=>	$data["remark"],
				
				"user_id"   =>  $this->getUserId(),
		);
		$this->_name = "rms_book";
		$where = " id = $id";
		$this->update($arr, $where);
		
		if(!empty($data["identity"])){
			$identitys = explode(',',$data['identity']);
			$oldId="";
			if (!empty($identitys)){
				foreach ($identitys as $i){
					if(empty($oldId)){
						if (!empty($data['old_'.$i])){
							$oldId = $data['old_'.$i];
						}
					}else{
						if (!empty($data['old_'.$i])){
							$oldId= $oldId.",".$data['old_'.$i];
						}
					}
				}
			}
		}
		
		$this->_name="rms_book_detail";
		$where1 = " book_id = $id";
		if(!empty($oldId)){
			$where1.=" AND id NOT IN ($oldId)";
		}
		$this->delete($where1);
		
		$this->_name="rms_book_detail";
		if(!empty($data["identity"])){
			$ids = explode(",", $data['identity']);
			foreach ($ids as $i){
				if (!empty($data['old_'.$i])){
					$arr = array(
						'book_id'	=>$id,
						'serial'	=> $data['serial_'.$i],
						'barcode'	=> $data['barcode_'.$i],
						'note'	  	=> $data['note_'.$i],
						'status'	=> $data['status_'.$i],
					);
					$where =" id =".$data['old_'.$i];
					$this->update($arr, $where);
				}else{
					$array = array(
						'book_id'	=> $id,
						'serial'	=> $data['serial_'.$i],
						'barcode'	=> $data['barcode_'.$i],
						'note'	  	=> $data['note_'.$i],
						'status'	=> $data['status_'.$i],
					);
					$this->insert($array);
				}
			}
		}
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
		$sql="SELECT count(id) FROM rms_book limit 1";
		$qty=$db->fetchOne($sql);
		$qty_new = $qty+1;
		$lenght = strlen($qty_new);
		
		$prefix='';
		for($i=$lenght;$i<5;$i++){
			$prefix.='0';
		}
		return $prefix.$qty_new;
		
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
	function getBookRowDetailById($id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_book_detail WHERE book_id=$id";
		return $db->fetchAll($sql);
	}
	
	function getTotalBook(){
		$db=$this->getAdapter();
		$sql="SELECT count(id) FROM rms_book_detail WHERE `status`=1 ";
		return $db->fetchOne($sql);
	}
	function getTotalBrokenBook(){
		$db=$this->getAdapter();
		$sql="SELECT count(id) FROM rms_book_detail WHERE `status`=1 and is_broken=1 ";
		return $db->fetchOne($sql);
	}
	
	function getBookNotReturn(){
		$db=$this->getAdapter();
		$sql="SELECT count(id) FROM rms_borrowdetails WHERE is_return=0";
		return $db->fetchOne($sql);
	}

	function getBorrowThisDay(){
		$date=date('Y-m-d');
		$db=$this->getAdapter();
		$sql="SELECT count(bd.id) FROM rms_borrow as b,rms_borrowdetails as bd WHERE b.id=bd.borr_id AND b.borrow_date='$date'";
		return $db->fetchOne($sql);
	}
	
	function getReturnThisDay(){
		$date=date('Y-m-d');
		$db=$this->getAdapter();
		$sql="SELECT count(bd.id) FROM rms_bookreturn as b,rms_bookreturndetails as bd WHERE b.id=bd.return_id AND b.return_date='$date'";
		return $db->fetchOne($sql);
	}
	
	public function getBookQty($book_id){
		$db=$this->getAdapter();
		$sql=" SELECT id,book_no,qty_after,qty FROM rms_book WHERE id=$book_id AND `status`=1 ";
		$row = $db->fetchRow($sql);
		if(empty($row)){
			$session_user=new Zend_Session_Namespace(SYSTEM_SES);
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
		$sql="SELECT count(bd.id) FROM rms_borrow as b,rms_borrowdetails as bd WHERE b.id=bd.borr_id and bd.is_return=0 ";
		$search['end_date']=date("Y-m-d");
		$str_next = '+3 day';
		$search['end_date']=date("Y-m-d", strtotime($search['end_date'].$str_next));
		$to_date = (empty($search['end_date']))? '1': " b.return_date <= '".$search['end_date']." 23:59:59'";
		$sql .= " AND ".$to_date;
		return $db->fetchOne($sql);
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
	
	function getPurchaseToday(){
		$date=date('Y-m-d');
		$db=$this->getAdapter();
		$sql="SELECT count(bd.id) FROM rms_bookpurchase as b,rms_bookpurchasedetails as bd WHERE b.id=bd.purchase_id AND b.date_purchase='$date' and bd.status=1 ";
		return $db->fetchOne($sql);
	}
	
	function getBrokenToday(){
		$date=date('Y-m-d');
		$db=$this->getAdapter();
		$sql="SELECT count(bd.id) FROM rms_bookbroken as b,rms_bookbrokendetails as bd WHERE b.id=bd.broken_id AND b.date_broken='$date'";
		return $db->fetchOne($sql);
	}
	
	
}



