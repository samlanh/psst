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
    	$sql=" SELECT 
    				b.id,
    				b.purchase_no,
    				DATE_FORMAT(b.date_purchase, '%d-%m-%Y') AS date_purchase,
    				b.note,
		       		(SELECT CONCAT(u.first_name,' ',u.last_name) FROM rms_users AS u WHERE u.id=b.user_id LIMIT 1) AS user_name,
		       		(SELECT v.`name_en` FROM rms_view AS v WHERE v.`type`=1  AND b.status=v.`key_code` LIMIT 1) AS `status` 
		       	FROM 
		       		rms_bookpurchase AS b
		       	WHERE 
		       		1
			";
    	
    	$from_date =(empty($search['start_date']))? '1': " b.date_purchase >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " b.date_purchase <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  b.purchase_no LIKE '%{$s_search}%'";
    		$s_where[]="  b.title LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search["status_search"] > -1){
    	    $where.=' AND b.status='.$search["status_search"];
    	}
    	$order=" ORDER BY id ASC";
    	return $db->fetchAll($sql.$where.$order);
    }
 
	public function addPurchaseBook($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$arr=array(
				"purchase_no"   => 	$data["purchase_no"],
				"date_purchase" => 	date("Y-m-d",strtotime($data['date_purchase'])),
				"note"     		=> 	$data["note_p"],
				"user_id"       => 	$this->getUserId(),
			);
			$this->_name="rms_bookpurchase";
			$purchase_id = $this->insert($arr); 

			if(!empty($data['identity'])){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$array = array(
						'book_id'	=> 	$data['book_id'.$i],
						'serial'	=>  $data['serial_'.$i],
						'barcode'	=>  $data['barcode_'.$i],
						'note'  	=> 	$data['note_'.$i],
					);
					$this->_name='rms_book_detail';
					$book_id = $this->insert($array);
					
					$data_item= array(
						'purchase_id'	=>  $purchase_id,
						'book_id'		=> 	$book_id,
						'note'  		=> 	$data['note_'.$i],
					);
					$this->_name='rms_bookpurchasedetails';
					$this->insert($data_item);
				 }
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			echo $e->getMessage();exit();
		}
	}

	public function editPurchaseDetail($data,$id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$arr=array(
					"purchase_no"   => 	$data["purchase_no"],
					"date_purchase" => 	date("Y-m-d",strtotime($data['date_purchase'])),
					"note"     		=> 	$data["note_p"],
					"user_id"       => 	$this->getUserId(),
			);
			$this->_name="rms_bookpurchase";
			$where1=" id = $id ";
			$this->update($arr, $where1);
			
			
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
			
		    $row_detail=$this->getPurchaseDetailById($id);
		    if(!empty($row_detail)){
		    	foreach ($row_detail As $rs_item){
	    			$this->_name = "rms_book_detail";
	    			$where=" id = ".$rs_item['book_id'];
	    			if(!empty($oldId)){
	    				$where.=" and id NOT IN ($oldId) ";
	    			}
	    			$this->delete($where);
		    	}
		    }
             
		    
			
			$this->_name="rms_bookpurchasedetails";
			$where=" purchase_id = $id ";
			$this->delete($where);

			
			if(!empty($data['identity'])){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					if (!empty($data['old_'.$i])){
						$arr = array(
								'book_id'	=> $data['book_id'.$i],
								'serial'	=> $data['serial_'.$i],
								'barcode'	=> $data['barcode_'.$i],
								'note'	  	=> $data['note_'.$i],
						);
						$this->_name='rms_book_detail';
						$where =" id =".$data['old_'.$i];
						$this->update($arr, $where);
						$book_id = $data['old_'.$i];
					}else{
						$array = array(
							'book_id'	=> 	$data['book_id'.$i],
							'serial'	=>  $data['serial_'.$i],
							'barcode'	=>  $data['barcode_'.$i],
							'note'  	=> 	$data['note_'.$i],
						);
						$this->_name='rms_book_detail';
						$book_id = $this->insert($array);
					}
					
					$data_item= array(
						'purchase_id'	=>  $id,
						'book_id'		=> 	$book_id,
						'note'  		=> 	$data['note_'.$i],
					);
					$this->_name='rms_bookpurchasedetails';
					$this->insert($data_item);
				 }
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();exit();
		}
	}
	
	public function getPurchaseDetailById($id){
		$db=$this->getAdapter();
		$sql = "SELECT 
					bpd.*,
					bd.barcode,
					bd.serial,
					b.title,
					b.id as b_id
				FROM 
					rms_bookpurchasedetails as bpd,
					rms_book_detail as bd,
					rms_book as b
				WHERE 
					bpd.book_id = bd.id
					and bd.book_id = b.id
					and purchase_id=$id
			";
		return $db->fetchAll($sql);
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



