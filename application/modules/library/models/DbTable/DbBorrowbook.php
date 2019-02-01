<?php

class Library_Model_DbTable_DbBorrowbook extends Zend_Db_Table_Abstract
{
 	protected $_name = 'rms_book';
 	protected $tr;
 	public function init()
 	{
 		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
 	}
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    
    function getAllBorrow($search=null){
    	$db=$this->getAdapter();
    	$sql=" SELECT 
    				b.id,
    				b.borrow_no,
    				(SELECT v.name_kh FROM rms_view  AS v WHERE v.key_code=b.borrow_type AND v.type=13) AS `type`,
    				CASE
						WHEN borrow_type = 1 THEN (select stu_code from rms_student as s where b.stu_id = s.stu_id)
						ELSE b.card_id
					END,
					CASE
						WHEN borrow_type = 1 THEN (select stu_khname from rms_student as s where b.stu_id = s.stu_id)
						ELSE b.name
					END,
    				b.phone,
		        	DATE_FORMAT(b.borrow_date,'%d/%b/%Y'),
		        	DATE_FORMAT(b.return_date,'%d/%b/%Y'),
		        	b.note,
		       		(SELECT first_name FROM rms_users WHERE id=b.user_id LIMIT 1) AS user_name,
			   		(SELECT name_en FROM rms_view WHERE key_code=b.status LIMIT 1) AS `status`
		       	FROM 
		       		rms_borrow AS b
		       	WHERE  
		       		1
		    ";
    	
    	$where = '';
    	
    	$from_date =(empty($search['start_date']))? '1': " b.borrow_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " b.borrow_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  b.borrow_no LIKE '%{$s_search}%'";
    		$s_where[]="  b.card_id LIKE '%{$s_search}%'";
    		$s_where[]="  b.name LIKE '%{$s_search}%'";
    		$s_where[]="  b.phone LIKE '%{$s_search}%'";
    		$s_where[]= "(SELECT stu_code FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[]= "(SELECT stu_enname FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=b.stu_id LIMIT 1) LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	
    	if($search["status_search"]>-1){
    	    $where.=' AND b.status='.$search["status_search"];
    	}
    	
    	if($search["student_name"]>0){
    		$where.=' AND b.stu_id='.$search["student_name"];
    	}
    	
    	if(!empty($search["is_type_bor"])){
    		$where.=' AND b.borrow_type='.$search["is_type_bor"];
    	}
    	
    	$order=" GROUP BY b.borrow_no ORDER BY b.id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
 
	public function addBorrowBook($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$arr=array(
					"borrow_no"     => 	$data["borrow_id"],
					"stu_id"        => 	$data["stu_id"],
					"borrow_date"   => 	date("Y-m-d",strtotime($data['borrow_date'])),
					"return_date"   => 	date("Y-m-d",strtotime($data['return_date'])),
					"amount_day"	=> 	$data['amountday'],
					"note"          => 	$data['note'],
					"phone"         => 	$data['phone'],
					"user_id"       => 	$this->getUserId(),
					"card_id"     	=> 	$data["card_id"],
					"name"     		=> 	$data["name"],
					"borrow_type"   => 	$data["type"],
			);
			$this->_name="rms_borrow";
			$borr_id = $this->insert($arr); 
            
			if(!empty($data['identity'])){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$data_item= array(
							'borr_id'	=>  $borr_id,
							'book_id'	=> 	$data['book_id'.$i],
					);
					$this->_name='rms_borrowdetails';
					$this->insert($data_item);
					
					// update rms_book_detail
					$datatostock= array(
						'is_borrow' => 1,
					);
					$this->_name="rms_book_detail";
					$where=" id = ".$data['book_id'.$i];
					$this->update($datatostock, $where);
				 }
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();
		}
	}
	 
	public function editBorrowBook($data,$id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
		    $row_item=$this->getItemDetail($id);
		    if(!empty($row_item)){
		    	foreach ($row_item As $rs_item){
	    			$arr = array(
	    				'is_borrow'	=>	0,
	    			);
	    			$this->_name="rms_book_detail";
	    			$where=" id = ".$rs_item['book_id'];
	    			$this->update($arr, $where);
		    	}
		    }
             
			$arr=array(
					"borrow_no"     => 	$data["borrow_id"],
					"stu_id"        => 	$data["stu_id"],
					"borrow_date"   => 	date("Y-m-d",strtotime($data['borrow_date'])),
					"return_date"   => 	date("Y-m-d",strtotime($data['return_date'])),
					"amount_day"	=> 	$data['amountday'],
					"note"          => 	$data['note'],
					"phone"         => 	$data['phone'],
					"user_id"       => 	$this->getUserId(),
					"card_id"     	=> 	$data["card_id"],
					"name"     		=> 	$data["name"],
					"borrow_type"   => 	$data["type"],
			);
			$this->_name="rms_borrow";
			$where = " id = $id";
		    $this->update($arr, $where); 
			 
			$this->_name="rms_borrowdetails";
			$where=" borr_id = $id " ;
			$this->delete($where);

			if(!empty($data['identity'])){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$data_item= array(
							'borr_id'	=>  $id,
							'book_id'	=> 	$data['book_id'.$i],
					);
					$this->_name='rms_borrowdetails';
					$this->insert($data_item);
					
					// update rms_book_detail
					$datatostock= array(
						'is_borrow' => 1,
					);
					$this->_name="rms_book_detail";
					$where=" id = ".$data['book_id'.$i];
					$this->update($datatostock, $where);
				 }
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			echo $e->getMessage();
		}
	}
	
	public function getItemDetail($id){
		$db=$this->getAdapter();
		$sql = "SELECT * FROM rms_borrowdetails WHERE borr_id=$id";
		return $db->fetchAll($sql);
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
		$sql="SELECT id,`name` FROM rms_bcategory WHERE  `status` = 1";
		return $db->fetchAll($sql);		
	}
	
	function getBorrowById($id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_borrow WHERE id=$id";
		return $db->fetchRow($sql);
	}
	
	function getBorrowDetailById($id){
		$db=$this->getAdapter();
		$sql="SELECT brd.*,bd.serial,bd.barcode FROM rms_borrowdetails as brd , rms_book_detail as bd WHERE bd.id = brd.book_id and brd.borr_id = $id ";
		return $db->fetchAll($sql);
	}
	
	function getAllStudentId($type){//type = 1 =>student id , 2=student name 
		$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission();
		if($type==1){
			$sql="SELECT s.stu_id As stu_id,s.stu_code As stu_code FROM rms_student AS s
			WHERE s.status=1 AND s.stu_enname!=''
			and s.is_subspend=0  $branch_id  ORDER BY stu_type DESC ";
		}else {
			$sql="SELECT s.stu_id As stu_id,CONCAT(s.stu_khname) as name FROM rms_student AS s
			WHERE s.status=1 AND s.stu_enname!=''
			and s.is_subspend=0  $branch_id  ORDER BY stu_type DESC ";
		}
		return $db->fetchAll($sql);
	}
	
	function getBookTitle(){
		$db=$this->getAdapter();
		$sql="SELECT id,CONCAT(title) AS name FROM rms_book WHERE `status`=1 AND title!=''";
		$rows=$db->fetchAll($sql);
		array_unshift($rows,array('id' => '-1',"name"=>$this->tr->translate("SELECT_TITLE")));
		$options = '';
		if(!empty($rows))foreach($rows as $value){
			$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
		}
		return $options;
	}
	
	function getBookDetail($book_id=0,$type=1){
		$db=$this->getAdapter();
		$sql="SELECT id,CONCAT(serial,' - ',barcode) as name FROM rms_book_detail WHERE is_broken=0  ";
		if($book_id>0){
			$sql.=" and book_id = $book_id ";
		}
		if($type==1){ // borrow
			$sql.=" and is_borrow=0 ";
		}else{  // return
			$sql.=" and is_borrow=1 ";
		}
		return $db->fetchAll($sql);
	}
	
	
	function getBookTitlePurchase(){
		$db=$this->getAdapter();
		$sql="SELECT id,CONCAT(title) AS name FROM rms_book WHERE `status`=1 AND title!=''";
		$rows=$db->fetchAll($sql);
		array_unshift($rows,array('id' => '-1',"name"=>$this->tr->translate("ADD_NEW")));
		array_unshift($rows,array('id' => '',"name"=>$this->tr->translate("SELECT_TITLE")));
		$options = '';
		if(!empty($rows))foreach($rows as $value){
			$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
		}
		return $options;
	}
	
	function getBorrowNo(){
		$db=$this->getAdapter();
		$sql="SELECT id FROM rms_borrow WHERE 1 ORDER BY id DESC";
		$row=$db->fetchOne($sql);
		if(empty($row)){
			$row=floatVal('1.0'.rand(1,9));
		}
		$fex='';
		if(!empty($row)){
			for($i=0;$i<4;$i++){
				$fex.='0';
			}
		}
		return $fex.$row;
	}
	
	public function getBookQty($book_id){
		$db=$this->getAdapter();
		$sql=" SELECT id,book_no,qty_after FROM rms_book WHERE id=$book_id AND `status`=1 ";
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
			$sql=" SELECT id,book_no,qty_after FROM rms_book WHERE id=$book_id AND `status`=1 ";
			return $row = $db->fetchRow($sql);
		}else{
	
			return $row;
		}
	}
	
}



