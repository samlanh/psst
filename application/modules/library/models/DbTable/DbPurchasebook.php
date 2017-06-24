<?php

class Library_Model_DbTable_DbPurchasebook extends Zend_Db_Table_Abstract
{
 	protected $_name = 'rms_bookpurchase';
 	protected $tr;
 	public function init()
 	{
 		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
 	}
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    
    function getAllBroken($search=null){
    	$db=$this->getAdapter();
    	$sql=" SELECT b.id,b.broke_no,
        		   
        		  b.date_broken,SUM(bd.borr_qty) AS qty,b.title,
				  (SELECT name_en FROM rms_view WHERE key_code=b.status LIMIT 1) AS `status`,
				  (SELECT first_name FROM rms_users WHERE id=b.user_id LIMIT 1) AS user_name
			       FROM rms_bookbroken AS b,rms_bookbrokendetails AS bd
			       WHERE  b.status=1
			       AND b.id=bd.broken_id";
    	$where = '';
    	$from_date =(empty($search['start_date']))? '1': " b.date_broken >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " b.date_broken <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  b.title LIKE '%{$s_search}%'";
    		$s_where[]="  b.broke_no LIKE '%{$s_search}%'";
    		$s_where[]="  bd.borr_qty LIKE '%{$s_search}%'";
    		$s_where[]="  (SELECT rms_book.book_no FROM rms_book WHERE rms_book.id=bd.book_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[]="  (SELECT rms_book.title FROM rms_book WHERE rms_book.id=bd.book_id LIMIT 1) LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
//     	$db_cat=new Library_Model_DbTable_DbCategory();
//     	if($search["parent"]>0){
//     		$where.=' AND bd.cat_id IN ('.$db_cat->getAllCategoryUnlimit($search["parent"]).')';
//     	}
    	if(!empty($search["cood_book"])){
    		$where.=' AND bd.book_id='.$search["cood_book"];
    	}
    	if($search["status_search"]>-1){
    	    $where.=' AND b.status='.$search["status_search"];
    	}
    	$order=" GROUP BY b.broke_no ORDER BY b.id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
 
	public function addBrokenBook($data){
		//print_r($data);exit();
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
		    $userName=$session_user->user_name;
		    $GetUserId= $session_user->user_id;
             
			$arr=array(
					"title"     	=> 	$data["note"],
					"broke_no"     	=> 	$data["broken_no"],
					"date_broken"   => 	date("Y-m-d",strtotime($data['broken_date'])),
					"user_id"       => 	$GetUserId,
					"status"        => 	$data['status'],
			);
			$this->_name="rms_bookbroken";
			$broken_id = $this->insert($arr); 
			unset($info_purchase_order);

			if($data['identity']!=""){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$data_item= array(
							'broken_id'	=>  $broken_id,
							'book_id'	=> 	$data['book_id'.$i],
							'borr_qty'	=>  $data['borr_qty'.$i],
							'note'  	=> 	$data['note_'.$i],
							'user_id'	=> 	$GetUserId,
							'is_full'	=> 	1,
							'status'	=> 	$data['status'],
					);
					$this->_name='rms_bookbrokendetails';
					$this->insert($data_item);
					$rows=$this->getBookQty($data['book_id'.$i]); 
					if($rows){
							$datatostock= array(
									'qty_after' => $rows["qty_after"]-$data['borr_qty'.$i],
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

	public function getItemDetail($id){
		$db=$this->getAdapter();
		$sql = "SELECT * FROM rms_bookbrokendetails WHERE broken_id=$id";
		$rows=$db->fetchAll($sql);
		return $rows;
	}
	
	public function editBrokenBook($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
		    $userName=$session_user->user_name;
		    $GetUserId= $session_user->user_id;
		    
		    $row_item=$this->getItemDetail($data['id']);
		    if(!empty($row_item)){
		    	foreach ($row_item As $rs_item){
		    		$row=$this->getBookQty($rs_item['book_id']);
		    		//print_r($row);exit();
		    		if($row){
		    			$datatostock   = array(
		    					'qty_after' =>  $row["qty_after"]+$rs_item['borr_qty'],
		    					'date'		=>	date("Y-m-d"),
		    			);
		    			$this->_name="rms_book";
		    			$where=" id = ".$row['id'];
		    			$this->update($datatostock, $where);
		    		}
		    	}
		    }else { }
             
		    $arr=array(
		    		"title"     	=> 	$data["note"],
		    		"date_broken"   => 	date("Y-m-d",strtotime($data['broken_date'])),
		    		"user_id"       => 	$GetUserId,
		    		"broke_no"     	=> 	$data["broken_no"],
		    		"status"        => 	$data['status'],
		    );
		    $this->_name="rms_bookbroken";
			$where=" id=".$data['id'];
		    $this->update($arr, $where); 
			unset($arr);
			
			$this->_name="rms_bookbrokendetails";
			$where=" broken_id=".$data['id'];
			$this->delete($where);

			if($data['identity']!=""){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$data_item= array(
							'broken_id'	=>  $data['id'],
							'book_id'	=> 	$data['book_id'.$i],
							'borr_qty'	=>  $data['borr_qty'.$i],
							'note'  	=> 	$data['note_'.$i],
							'user_id'	=> 	$GetUserId,
							'is_full'	=> 	1,
							'status'	=> 	$data['status'],
					);
					$this->_name='rms_bookbrokendetails';
					$this->insert($data_item);
					$rows=$this->getBookQty($data['book_id'.$i]); 
					if($rows){
							$datatostock= array(
									'qty_after' => $rows["qty_after"]-$data['borr_qty'.$i],
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
	
	public function getBrokenDetailById($id){
		$db=$this->getAdapter();
		$sql = "SELECT * FROM rms_bookbrokendetails WHERE broken_id=$id";
		$rows=$db->fetchAll($sql);
		return $rows;
	}
	 
	function getBrokenById($id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_bookbroken WHERE id=$id";
		return $db->fetchRow($sql);
	}
	 
	public function getBookQty($book_id){
		$db=$this->getAdapter();
		$sql=" SELECT id,book_no,qty_after FROM rms_book WHERE id=$book_id AND `status`=1 ";
		$row = $db->fetchRow($sql);
		if(empty($row)){
			$session_user=new Zend_Session_Namespace('auth');
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
	
	function getPONo(){
		$db=$this->getAdapter();
		$sql="SELECT id FROM rms_bookpurchase WHERE 1 ORDER BY id DESC";
		$row=$db->fetchOne($sql);
		if(empty($row)){
			$row=floatVal('1.0'.rand(1,9));
		}
		$fex='PO-';
		if(!empty($row)){
			for($i=0;$i<3;$i++){
				$fex.='0';
			}
		}
		return $fex.$row;
	}
	
}



