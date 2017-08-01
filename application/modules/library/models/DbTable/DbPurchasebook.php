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
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    
    function getAllPurchase($search=null){
    	$db=$this->getAdapter();
    	$sql=" SELECT b.id,b.purchase_no,b.title,b.date_order,
                       SUM(bd.borr_qty),
		       (SELECT CONCAT(u.first_name,' ',u.last_name) FROM rms_users AS u WHERE u.id=b.user_id LIMIT 1) AS user_name,
		       (SELECT v.`name_en` FROM rms_view AS v WHERE v.`type`=1  AND b.user_id=v.`key_code` LIMIT 1) AS `status` 
		       FROM rms_bookpurchase AS b,rms_bookpurchasedetails bd WHERE b.id=bd.purchase_id
		       ";
    	$where = '';
    	$from_date =(empty($search['start_date']))? '1': " b.date_order >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " b.date_order <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  b.purchase_no LIKE '%{$s_search}%'";
    		$s_where[]="  b.title LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search["status_search"]>-1){
    	    $where.=' AND status='.$search["status_search"];
    	}
    	$order=" GROUP BY b.purchase_no ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
 
	public function addPurchaseBook($data){
		//print_r($data);exit();
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('authstu');
		    $userName=$session_user->user_name;
		    $GetUserId= $session_user->user_id;
             
			$arr=array(
					"title"     	=> 	$data["note_p"],
					"purchase_no"   => 	$data["purchase_no"],
					"amount_due"    => 	$data["amount_due"],
					"date_order"    => 	date("Y-m-d",strtotime($data['date_order'])),
					"user_id"       => 	$GetUserId,
					"status"        => 	$data['status_p'],
			);
			$this->_name="rms_bookpurchase";
			$po_id = $this->insert($arr); 
			unset($info_purchase_order);

			if($data['identity']!=""){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$data_item= array(
							'purchase_id'	=>  $po_id,
							'book_id'	=> 	$data['book_id'.$i],
							'borr_qty'	=>  $data['qty_'.$i],
							'cost'		=>  $data['cost_'.$i],
							'amount'	=>  $data['amount_'.$i],
							'note'  	=> 	$data['note_'.$i],
							'user_id'	=> 	$GetUserId,
							'is_full'	=> 	1,
							'status'	=> 	$data['status_p'],
					);
					$this->_name='rms_bookpurchasedetails';
					$this->insert($data_item);
					$db_book=new Library_Model_DbTable_DbBook();
					$rows=$db_book->getBookQty($data['book_id'.$i]); 
					if($rows){
							$datatostock= array(
									'qty_after' => $rows["qty_after"]+$data['qty_'.$i],
									'qty' 		=> $rows["qty"]+$data['qty_'.$i],
									'unit_price'=> $data['cost_'.$i],
									'total_amount'=> $data['amount_'.$i],
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
		$sql = "SELECT * FROM rms_bookpurchasedetails WHERE purchase_id=$id";
		$rows=$db->fetchAll($sql);
		return $rows;
	}
	
	public function editPurchaseDetail($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('authstu');
		    $userName=$session_user->user_name;
		    $GetUserId= $session_user->user_id;
		    
		    $row_item=$this->getItemDetail($data['id']);
		    $db_book=new Library_Model_DbTable_DbBook();
		    if(!empty($row_item)){
		    	foreach ($row_item As $rs_item){
		    		$row=$db_book->getBookQty($rs_item['book_id']);
		    		//print_r($row);exit();
		    		if($row){
		    			$datatostock   = array(
		    					'qty_after' =>  $row["qty_after"]-$rs_item['borr_qty'],
		    					'qty' 		=>  $row["qty"]-$rs_item['borr_qty'],
		    					'date'		=>	date("Y-m-d"),
		    			);
		    			$this->_name="rms_book";
		    			$where=" id = ".$row['id'];
		    			$this->update($datatostock, $where);
		    		}
		    	}
		    }else { }
             
		    $arr=array(
		    		"title"     	=> 	$data["note_p"],
					"purchase_no"   => 	$data["purchase_no"],
					"amount_due"    => 	$data["amount_due"],
					"date_order"    => 	date("Y-m-d",strtotime($data['date_order'])),
					"user_id"       => 	$GetUserId,
					"status"        => 	$data['status_p'],
		    );
		    $this->_name="rms_bookpurchase";
			$where=" id=".$data['id'];
		    $this->update($arr, $where); 
			unset($arr);
			
			$this->_name="rms_bookpurchasedetails";
			$where=" purchase_id=".$data['id'];
			$this->delete($where);

			if($data['identity']!=""){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$data_item= array(
							'purchase_id'	=>  $data['id'],
							'book_id'	=> 	$data['book_id'.$i],
							'borr_qty'	=>  $data['qty_'.$i],
							'cost'		=>  $data['cost_'.$i],
							'amount'	=>  $data['amount_'.$i],
							'note'  	=> 	$data['note_'.$i],
							'user_id'	=> 	$GetUserId,
							'is_full'	=> 	1,
							'status'	=> 	$data['status_p'],
					);
					$this->_name='rms_bookpurchasedetails';
					$this->insert($data_item);
					$rows=$db_book->getBookQty($data['book_id'.$i]); 
					if($rows){
							$datatostock= array(
									'qty_after' => $rows["qty_after"]+$data['qty_'.$i],
									'qty' 		=> $rows["qty"]+$data['qty_'.$i],
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
	
	public function getPurchaseDetailById($id){
		$db=$this->getAdapter();
		$sql = "SELECT * FROM rms_bookpurchasedetails WHERE purchase_id=$id";
		$rows=$db->fetchAll($sql);
		return $rows;
	}
	 
	function getPurchaseById($id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_bookpurchase WHERE id=$id";
		return $db->fetchRow($sql);
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



