<?php

class Accounting_Model_DbTable_DbTransferstock extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_transferstock';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    function getAllTransfer($search=null){  
    	$db=$this->getAdapter();
    	$sql = "SELECT s.id,s.transfer_no,s.transfer_date,
    	(SELECT branch_nameen FROM `rms_branch` WHERE br_id=s.from_location LIMIT 1) as fromlocation,
    	(SELECT branch_nameen FROM `rms_branch` WHERE br_id=s.to_location LIMIT 1) as tolocation,
    	s.note,
    	(SELECT first_name FROM `rms_users` WHERE id=s.user_id LIMIT 1) as user_name,
    	(SELECT name_en FROM `rms_view` WHERE TYPE=1 AND key_code=s.status ) as status
    	FROM `rms_transferstock` AS s WHERE 1 ";
    	$from_date =(empty($search['start_date']))? '1': " s.transfer_date>= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " s.transfer_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
	    if(!empty($search['title'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['title']));
	    	$s_where[] = " s.transfer_no LIKE '%{$s_search}%'";
	    	$s_where[] = " s.note LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
	    }
    	$order=" ORDER BY s.id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getTransferById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM rms_transferstock WHERE id=".$id;
    	return $db->fetchRow($sql);
    }
    function getTransferByIdDetail($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT *,
		(SELECT pro_name FROM `rms_product` WHERE id=rms_transferdetail.pro_id LIMIT 1) as pro_name 
    	FROM rms_transferdetail WHERE transferid=".$id;
    	return $db->fetchAll($sql);
    }
    public function addTransferStock($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
	    		$_arr = array(
	    				'transfer_no'=>$_data['transfer_no'],
	    				'transfer_date'=>$_data['date'],
	    				'from_location'=>$_data['from_location'],
	    				'to_location'=>$_data['to_location'],
	    				'note'=>$_data['remark'],
	    				'user_id'=>$this->getUserId(),
	    				'status'=>1,
	    				);
	    		$tranid= $this->insert($_arr);
	    		
	    		
	    		$ids = explode(',', $_data['identity']);
	    		foreach ($ids as $i){
    				$_arr = array(
    						'transferid'=>$tranid,
    						'pro_id'=>$_data['pro_id_'.$i],
    						'qty'=>$_data['qty_'.$i],
    						'note'=>$_data['remark_'.$i],);
    				$this->_name='rms_transferdetail';
    				$this->insert($_arr);
    				
    				$sql="select * from rms_product_location where pro_id=".$_data['pro_id_'.$i]." AND brand_id = ".$_data['from_location'];
    				$qty_stock = $db->fetchRow($sql);
    				$this->_name="rms_product_location";
    				if(!empty($qty_stock)){
    					$qty = $qty_stock['pro_qty'] - $_data['qty_'.$i];
    					$array = array(
    							'pro_qty'=>$qty,
    					);
    					$where = " id = ".$qty_stock['id'];
    					$this->update($array, $where);
    				}
    				//to location
    				$sql="select * from rms_product_location where pro_id=".$_data['pro_id_'.$i]." AND brand_id = ".$_data['to_location'];
    				$qty_stock = $db->fetchRow($sql);
    				if(!empty($qty_stock)){
    					$qty = $qty_stock['pro_qty'] + $_data['qty_'.$i];
    					$array = array(
    							'pro_qty'=>$qty,
    					);
    					$where = " id = ".$qty_stock['id'];
    					$this->update($array, $where);
    				}
	    		}
	    	    $db->commit();
	    	    return true;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		return false;
    	}
    }
	public function updateTransferStock($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
	    		$_arr = array(
	    				'transfer_no'=>$_data['transfer_no'],
	    				'transfer_date'=>$_data['date'],
	    				'from_location'=>$_data['from_location'],
	    				'to_location'=>$_data['to_location'],
	    				'note'=>$_data['remark'],
	    				'user_id'=>$this->getUserId(),
	    				'status'=>1,
	    				);
	    		$where="id = ".$_data['id'];
	    		 $this->update($_arr, $where);
	    		 
	    		$this->_name='rms_transferdetail';
	    		$where = "transferid = ".$_data['id']; 
	    		$this->delete($where);
	    		
	    		$ids = explode(',', $_data['identity']);
	    		foreach ($ids as $i){
    				$_arr = array(
    						'transferid'=>$_data['id'],
    						'pro_id'=>$_data['pro_id_'.$i],
    						'qty'=>$_data['qty_'.$i],
    						'note'=>$_data['remark_'.$i],);
    				$this->insert($_arr);
    				//from location
    				$sql="select * from rms_product_location where pro_id=".$_data['pro_id_'.$i]." AND brand_id = ".$_data['from_location'];
    				$qty_stock = $db->fetchRow($sql);
    				$this->_name="rms_product_location";
    				if(!empty($qty_stock)){
    					$qty = $qty_stock['pro_qty'] - $_data['qty_'.$i];
    					$array = array(
    							'pro_qty'=>$qty,
    					);
    					$where = " id = ".$qty_stock['id'];
    					$this->update($array, $where);
    				}
    				//to location
    				$sql="select * from rms_product_location where pro_id=".$_data['pro_id_'.$i]." AND brand_id = ".$_data['to_location'];
    				$qty_stock = $db->fetchRow($sql);
    				if(!empty($qty_stock)){
    					$qty = $qty_stock['pro_qty'] + $_data['qty_'.$i];
    					$array = array(
    							'pro_qty'=>$qty,
    					);
    					$where = " id = ".$qty_stock['id'];
    					$this->update($array, $where);
    				}
	    		}
	    	    $db->commit();
	    	    return true;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		return false;
    	}
    }
}