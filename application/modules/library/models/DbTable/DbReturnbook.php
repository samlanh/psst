<?php

class Library_Model_DbTable_DbReturnbook extends Zend_Db_Table_Abstract
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
    
    function getAllReturnBook($search=null){
    	$db=$this->getAdapter();
    	$sql=" SELECT id,return_no,
		       (SELECT stu_code FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=rms_bookreturn.stu_id LIMIT 1) AS stu_code,
		       (SELECT stu_enname FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=rms_bookreturn.stu_id LIMIT 1) AS stu_name,
		        return_date,note,
		       (SELECT first_name FROM rms_users WHERE id=rms_bookreturn.user_id LIMIT 1) AS user_name,
			   (SELECT name_en FROM rms_view WHERE key_code=rms_bookreturn.status LIMIT 1) AS `status`
		       FROM rms_bookreturn 
		       WHERE  1";
    	$where = '';
    	
    	$from_date =(empty($search['start_date']))? '1': " return_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " return_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  return_no LIKE '%{$s_search}%'";
    		$s_where[]= "(SELECT stu_code FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=rms_bookreturn.stu_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[]= "(SELECT stu_enname FROM rms_student WHERE rms_student.is_subspend=0 AND rms_student.stu_id=rms_bookreturn.stu_id LIMIT 1) LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	
    	if($search["status_search"]>-1){
    	    $where.=' AND `status`='.$search["status_search"];
    	}
    	
    	if($search["stu_name"]>0){
    		$where.=' AND stu_id='.$search["stu_name"];
    	}
    	
    	$order=" ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
 
	public function addReturnBook($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('authstu');
		    $userName=$session_user->user_name;
		    $GetUserId= $session_user->user_id;
             
			$arr=array(
					"return_no"     => 	$data["borrow_id"],
					"phone"     	=> 	$data["phone"],
					"stu_id"        => 	$data["stu_id"],
					"return_date"   => 	date("Y-m-d",strtotime($data['return_date'])),
					"note"          => 	$data['notes'],
					"is_completed"  => 	0,
					"user_id"       => 	$GetUserId,
					"status"        => 	$data['status'],
			);
			$this->_name="rms_bookreturn";
			$borr_id = $this->insert($arr); 
			unset($info_purchase_order);

			if($data['record_row']!=""){
				$ids=explode(',',$data['record_row']);
				foreach ($ids as $i)
				{ 
					$is_comp=0;
				    if($data['borrow_qty_'.$i] <= $data['return_qty_'.$i]){
				    	$is_comp=1;
				    }
					$data_item= array(
							'return_id'	=>  $borr_id,
							'book_id'	=> 	$data['book_id_'.$i],
							'borr_qty'	=>  $data['return_qty_'.$i],
							'note'  	=> 	$data['note_'.$i],
							'borr_detail_id'=>$data['borrow_id_'.$i],
							'user_id'	=> 	$GetUserId,
							'is_full'	=> 	$is_comp,
							'status'	=> 	$data['status'],
					);
					$this->_name='rms_bookreturndetails';
					$this->insert($data_item);
					
					$borr_item= array(
							'is_full'	=> 	$is_comp,
							'borr_qty'	=>  $data['borrow_qty_'.$i]-$data['return_qty_'.$i],
					);
					$this->_name='rms_borrowdetails';
					$where=" id=".$data['borrow_id_'.$i];
					$this->update($borr_item, $where);
					
					$rows=$this->getBookQty($data['book_id_'.$i]); 
					if($rows){
							$datatostock= array(
									'qty_after' => $rows["qty_after"]+$data['return_qty_'.$i],
									'date'		=>	date("Y-m-d"),
									'user_id'	=>$GetUserId
							);
							$this->_name="rms_book";
							$where=" id = ".$rows['id'];
							$this->update($datatostock, $where);
					}else{
						
					}
				 }
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	
	public function updateReturnBook($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('authstu');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			
			$id=$data['id'];
			$row_item=$this->getItemDetail($id);
			if(!empty($row_item)){
				foreach ($row_item As $rs_item){
					$row=$this->getBookQty($rs_item['book_id']);
					if($row){
						$datatostock   = array(
								'qty_after' =>  $row["qty_after"]-$rs_item['borr_qty'],
								'date'		=>	date("Y-m-d"),
						);
// 						$db->getProfiler()->setEnabled(true);
						$this->_name="rms_book";
						$where=" id = ".$row['id'];
						$this->update($datatostock, $where);
// 						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
// 						Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
// 						$db->getProfiler()->setEnabled(false);
					}
					
					$borr_qty=$this->getQtyBorrow($rs_item['stu_id'],$rs_item['book_id']);
					if($borr_qty){
						$arr    = array(
								'borr_qty' =>  $borr_qty["borr_qty"]+$rs_item['borr_qty'],
								'date'		=>	date("Y-m-d"),
						);
						$this->_name="rms_borrowdetails";
						$where=" id = ".$borr_qty['id'];
						$this->update($arr, $where);
					}
				}
			}
			
			$this->_name='rms_bookreturndetails';
			$where=" return_id=".$id;
			$this->delete($where);
			
			$stu_id=$data['stu_id'];
			$arr_return=array(
					"return_no"     => 	$data["return_no"],
					"phone"     	=> 	$data["phone"],
					"stu_id"        => 	$data["stu_id"],
					"return_date"   => 	date("Y-m-d",strtotime($data['return_date'])),
					"note"          => 	$data['notes'],
					"is_completed"  => 	0,
					"user_id"       => 	$GetUserId,
					"status"        => 	$data['status'],
			);
			$this->_name="rms_bookreturn";
			$where=" id=".$data['id'];
			$this->update($arr_return, $where);
			 
			
			if($data['record_row']!=""){
				$ids=explode(',',$data['record_row']);
				//print_r($ids);exit();
				foreach ($ids as $i)
				{ 
					$is_comp=0;
					$total_borr=$data['oldbor_qty_'.$i]+$data['oldqty_return_'.$i];
					if($total_borr <= $data['return_qty_'.$i]){
						$is_comp=1;
					}
					$data_item= array(
							'return_id'	=>  $data['id'],
							'book_id'	=> 	$data['book_id_'.$i],
							'borr_qty'	=>  $data['return_qty_'.$i],
							'note'  	=> 	$data['note_'.$i],
							'borr_detail_id'=>$data['oldborrow_id'.$i],
							'user_id'	=> 	$GetUserId,
							'is_full'	=> 	$is_comp,
							'status'	=> 	$data['status'],
					);
					$this->_name='rms_bookreturndetails';
					$this->insert($data_item);
					
					$borr_qtys=$this->getQtyBorrow($data['stu_id'],$data['book_id_'.$i]);
					if($borr_qtys){
						$data_borrow    = array(
								'is_full'	=> 	$is_comp,
								'borr_qty' =>  $borr_qtys["borr_qty"]-$data['return_qty_'.$i],
								'date'		=>	date("Y-m-d"),
						);
						$this->_name="rms_borrowdetails";
						$where=" id = ".$borr_qtys['id'];
						$this->update($data_borrow, $where);
					}
				
					$rows=$this->getBookQty($data['book_id_'.$i]);
					if($rows){
						$datatostock= array(
								'qty_after' => $rows["qty_after"]+$data['return_qty_'.$i],
								'date'		=>	date("Y-m-d"),
								'user_id'	=>$GetUserId
						);
						$this->_name="rms_book";
						$where=" id = ".$rows['id'];
						$this->update($datatostock, $where);
					} 
				}
			}
			//exit();
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	 
	public function editReturnBook($data){}
	
	public function getItemDetail($id){
		$db=$this->getAdapter();
		$sql = "SELECT bd.id,b.stu_id,bd.borr_qty,bd.book_id FROM rms_bookreturn AS b,rms_bookreturndetails AS bd 
		        WHERE  bd.return_id=$id
		        AND b.id=bd.return_id";
		$rows=$db->fetchAll($sql);
		return $rows;
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
	
	function getReturnBookById($id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_bookreturn WHERE id=$id";
		return $db->fetchRow($sql);
	}
	
	function getReturnDetailById($id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_bookreturndetails WHERE return_id=$id";
		return $db->fetchAll($sql);
	}
	
	function getAllStudentId($type){//type = 1 =>student id , 2=student name 
		$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission();
		if($type==1){
			$sql="SELECT s.stu_id AS stu_id,s.stu_code AS stu_code FROM rms_student AS s,rms_borrow AS b,rms_borrowdetails AS bd
				WHERE s.stu_id=b.stu_id 
				AND s.stu_enname!=''
				AND b.id=bd.borr_id
				AND s.status=1 
				AND s.is_subspend=0
				AND bd.is_full=0 $branch_id
			   GROUP BY s.stu_id  ORDER BY stu_type DESC ";
		}else {
			$sql="SELECT s.stu_id AS stu_id,CONCAT(s.stu_enname) as name FROM rms_student AS s,rms_borrow AS b,rms_borrowdetails AS bd
				WHERE s.stu_id=b.stu_id 
				AND s.stu_enname!=''
				AND b.id=bd.borr_id
				AND s.status=1 
				AND s.is_subspend=0
				AND bd.is_full=0 $branch_id
			    GROUP BY s.stu_id ORDER BY stu_type DESC ";
		}
		return $db->fetchAll($sql);
	}
	
	function getBookTitle(){
		$db=$this->getAdapter();
		$sql="SELECT id,CONCAT(book_no,'-',title) AS name FROM rms_book WHERE `status`=1";
		$rows=$db->fetchAll($sql);
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
	
	function getQtyBorrow($stu_id,$book_id){
		$db=$this->getAdapter();
		$sql=" SELECT bd.borr_qty,bd.id,bd.book_id
		      FROM rms_borrow AS b,rms_borrowdetails AS bd
		      WHERE b.id=bd.borr_id
		      AND b.stu_id = $stu_id
		      AND bd.book_id=$book_id";
		$row=$db->fetchRow($sql);
		if(empty($row)){
		   
		}
		return $row;
	}
	
	function getReturnBookNo(){
		$db=$this->getAdapter();
		$sql="SELECT id FROM rms_bookreturn WHERE 1 ORDER BY id DESC";
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
	
}



