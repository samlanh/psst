<?php
class Registrar_Model_DbTable_DbChangeProduct extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_change_product';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	}
	
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->branch_id;
	}
	
	function addChangeProduct($data){
		$db = $this->getAdapter();
		
		$credit_memo_id = 0;
		$this->_name="rms_creditmemo";
		if($data['credit_memo']>0){

		////////////////////////////////// select if this student reamin credit_memo ///////////////////////////////////////	
			$sql = "SELECT * FROM rms_creditmemo WHERE student_id = ".$data['stu_id']." AND type=0 and status=1 ORDER BY id DESC LIMIT 1" ;
			$result = $db->fetchRow($sql);

			if(!empty($result)){ // if have , sum and update 
				$total_credit_memo = $result['total_amountafter'] + $data['credit_memo'];
				$array = array(
						'total_amountafter'=>$total_credit_memo,
						);
				$where = " id = ".$result['id'];
				$this->update($array, $where);
				
				$credit_memo_id = $result['id'];
				
			}else{ // insert new
				$arr = array(
						'branch_id'			=>$this->getBranchId(),
						'student_id'		=>$data['stu_id'],
						'total_amount'		=>$data['credit_memo'],
						'total_amountafter'	=>$data['credit_memo'],
						'user_id'			=>$this->getUserId(),
						'note'				=>"from change product",
						'date'				=>date('Y-m-d'),
				);
				$credit_memo_id = $this->insert($arr);
			}
		}
		
		$db = new Registrar_Model_DbTable_DbRegister();
		$receipt = $db->getRecieptNo();
		
		$this->_name="rms_change_product";
		$arr = array(
				'receipt_no'	=>$receipt,
				'stu_id'		=>$data['stu_id'],
				'total_payment'	=>$data['total_payment'],
				'credit_memo_id'=>$credit_memo_id,
				'credit_memo'	=>$data['credit_memo'],
				'user_id'		=>$this->getUserId(),
				'create_date'	=>date('Y-m-d'),
		);
		$change_id = $this->insert($arr);
		
		
		
		$ids = explode(',', $data['identity']);
		foreach ($ids as $j){
			$arr = array(
					'change_id'		=>$change_id,
					'service_id_old'=>$data['product_old_'.$j],// service_id in tbl_program_name
					'old_pro_id'	=>$data['old_pro_id_'.$j], // pro_id in tbl_product
					'old_pro_type'	=>$data['old_pro_type_'.$j], // pro_type ==> 0=normal , 1=set product
					
					'old_pro_price'	=>$data['price_old_'.$j],
					'old_qty'		=>$data['qty_old_'.$j],
					'old_total'		=>$data['total_old_'.$j],
					
					
					'service_id_new'=>$data['product_new_'.$j],
					'new_pro_id'	=>$data['new_pro_id_'.$j],
					'new_pro_type'	=>$data['new_pro_type_'.$j], // pro_type ==> 0=normal , 1=set product
					
					'new_pro_price'	=>$data['price_new_'.$j],
					'new_qty'		=>$data['qty_new_'.$j],
					'new_total'		=>$data['total_new_'.$j],
					
					'total_different'=>$data['total_diff_'.$j],
					'note'			=>$data['remark'.$j]
				   );
		   $this->_name='rms_change_product_detail';
		   $this->insert($arr);
		   
		   
		   $this->updateReturnStock($data['old_pro_id_'.$j],$data['old_pro_type_'.$j],$data['qty_old_'.$j]);
		   
		   $this->updateCutStock($data['new_pro_id_'.$j],$data['new_pro_type_'.$j],$data['qty_new_'.$j]);
		   
		}
		
 	 }
 	 
 	 function updateReturnStock($pro_id_old,$pro_type_old,$qty_old){
 	 	$db = $this->getAdapter();
 	 	$this->_name='rms_product_location';
 	 	
 	 	if($pro_type_old==0){ // product not set
 	 		$sql = " select pro_qty from rms_product_location where pro_id = $pro_id_old ";
 	 		$qty_in_stock = $db->fetchOne($sql);
 	 		
 	 		$total_qty = $qty_in_stock + $qty_old;
 	 		
 	 		$array = array(
 	 				'pro_qty'=>$total_qty,
 	 				);
 	 		$where = " pro_id = $pro_id_old ";
 	 		$this->update($array, $where);
 	 		
 	 	}else{ // product set
 	 		
 	 		$sql = " select subpro_id,qty from rms_product_setdetail where pro_id = $pro_id_old ";
 	 		$result = $db->fetchAll($sql);
 	 		if(!empty($result)){
 	 			foreach ($result as $sub_pro){
 	 				$sql = " select pro_qty from rms_product_location where pro_id = ".$sub_pro['subpro_id'];
 	 				$qty_in_stock = $db->fetchOne($sql);
 	 				
 	 				$total_qty = $qty_in_stock + ($qty_old * $sub_pro['qty']);
 	 				
 	 				$array = array(
 	 						'pro_qty'=>$total_qty,
 	 				);
 	 				$where = " pro_id = ".$sub_pro['subpro_id'];
 	 				$this->update($array, $where);
 	 			}
 	 		}
 	 	}
 	 }
 	 
 	 function updateCutStock($pro_id_new,$pro_type_new,$qty_new){
 	 	$db = $this->getAdapter();
 	 	$this->_name='rms_product_location';
 	 	
 	 	if($pro_type_new==0){ // product not set
 	 		$sql = " select pro_qty from rms_product_location where pro_id = $pro_id_new ";
 	 		$qty_in_stock = $db->fetchOne($sql);
 	 		 
 	 		$total_qty = $qty_in_stock - $qty_new;
 	 		 
 	 		$array = array(
 	 				'pro_qty'=>$total_qty,
 	 		);
 	 		$where = " pro_id = $pro_id_new ";
 	 		$this->update($array, $where);
 	 		 
 	 	}else{ // product set
 	 		$sql = " select subpro_id,qty from rms_product_setdetail where pro_id = $pro_id_new ";
 	 		$result = $db->fetchAll($sql);
 	 		if(!empty($result)){
 	 			foreach ($result as $sub_pro){
 	 				$sql = " select pro_qty from rms_product_location where pro_id = ".$sub_pro['subpro_id'];
 	 				$qty_in_stock = $db->fetchOne($sql);
 	 				
 	 				$total_qty = $qty_in_stock - ($qty_new * $sub_pro['qty']);
 	 				
 	 				$array = array(
 	 						'pro_qty'=>$total_qty,
 	 				);
 	 				$where = " pro_id = ".$sub_pro['subpro_id'];
 	 				$this->update($array, $where);
 	 			}
 	 		}
 	 	}
 	 	
 	 }
 	 

	 function editChangeProduct($data,$id){
	 	$db = $this->getAdapter();
	 	try{
	 		//print_r($data);exit();
		 	if($data['status']==0){
		 		$this->_name="rms_change_product";
		 		$arr = array(
		 				'status'=>$data['status'],
		 				);
		 		$where=" id = $id ";
		 		$this->update($arr, $where);
		 		
		 		$this->_name="rms_creditmemo";
		 		if($data['credit_memo_id']>0){
		 			
		 			$sql="select * from rms_creditmemo where id = ".$data['credit_memo_id']." and type=0 and status=1 ";
		 			$result = $db->fetchRow($sql);
		 			
		 			$total_credit_memo = $result['total_amountafter'] - $data['credit_memo'];
		 			
		 			if($total_credit_memo>0){
		 				$array = array(
		 						'total_amountafter'=>$total_credit_memo,
		 						);
		 				$where=" id = ".$data['credit_memo_id'];
		 				$this->update($array, $where);
		 			}else{
		 				$ar = array(
		 						'status'=>0,
		 						'type'=>1,
		 				);
		 				$where=" id = ".$data['credit_memo_id'];
		 				$this->update($arr, $where);
		 			}
		 		}
		 		
		 		$ids = explode(',', $data['identity']);
		 		foreach ($ids as $j){
		 			 
		 			$this->updateCutStock($data['old_pro_id_'.$j],$data['old_pro_type_'.$j],$data['qty_old_'.$j]);
		 			 
		 			$this->updateReturnStock($data['new_pro_id_'.$j],$data['new_pro_type_'.$j],$data['qty_new_'.$j]);
		 		}
		 	}else{
		 		return 0;
		 	}
	 	}catch (Exception $e){
	 		echo $e->getMessage();
	 	}
			
	}
	
	function getAllChangeProductById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_change_product where id=$id ";
		return $db->fetchRow($sql);
	}
	
	function getAllChangeProductDetailById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_change_product_detail WHERE change_id=$id ";
		return $db->fetchAll($sql);
	}
	
	function getAllChangeProduct($search=null){
		$db = $this->getAdapter();
		
		$from_date =(empty($search['start_date']))? '1': " cp.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " cp.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " and ".$from_date." AND ".$to_date;
	
		$sql="select
					cp.id,
					receipt_no,					
					(CASE WHEN s.stu_khname IS NULL THEN s.stu_enname ELSE s.stu_khname END) AS name,					
					total_payment,
					credit_memo,
					cp.create_date,
					(select first_name from rms_users where rms_users.id=cp.user_id) as user_id,
					(select name_en from rms_view where type=10 and key_code=cp.is_void) as status
				from
					rms_change_product cp,
					rms_student as s
				where 
					cp.stu_id=s.stu_id
		";
	
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " receipt_no LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}

	function getAllProduct(){
		$db = $this->getAdapter();
		$sql=" select service_id as id,title as name, pro_type from rms_program_name where status=1 and type=1";
		return $db->fetchAll($sql);
	}
	
	function getAllStuCode(){
		$db = $this->getAdapter();
		$sql=" select stu_id as id,stu_code from rms_student where status=1 and is_subspend=0";
		return $db->fetchAll($sql);
	}
	
	function getAllStuName(){
		$db = $this->getAdapter();
		$sql=" select stu_id as id,CONCAT(stu_enname,'-',stu_khname) as name from rms_student where status=1 and is_subspend=0";
		return $db->fetchAll($sql);
	}

	function getProductPrice($pro_id){
		$db = $this->getAdapter();
		$sql=" select price,pro_type,ser_cate_id as pro_id from rms_program_name where service_id=$pro_id";
		return $db->fetchRow($sql);
	}

}