<?php

class Registrar_Model_DbTable_DbRegister extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    	 
    }
    
    public function getBranchId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->branch_id;
    }
    
    function getStudentPaymentStart($studentid,$service_id,$type=1){
    	$db = $this->getAdapter();
    	$sql="SELECT spd.id 
    			FROM rms_student_payment AS sp,
    			   rms_student_paymentdetail AS spd WHERE
    		sp.id=spd.payment_id AND is_start=1 
    		AND spd.itemdetail_id= $service_id 
    		AND sp.student_id=$studentid 
    		ORDER BY spd.validate DESC LIMIT 1 ";
    	return $db->fetchOne($sql);
    }
    function getStuidExist($stu_code){
    	$db=$this->getAdapter();
    	$sql="SELECT stu_id,stu_code FROM rms_student WHERE stu_code=$stu_code LIMIT 1";
    	return $db->fetchRow($sql);
    }
    
    function updateStock($service_id,$qty_order,$pro_type){ // $pro_type=0 ==> product រាយ  , $pro_type=1 ==> product set 
    	$db = $this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch = $_db->getAccessPermission('brand_id');
    	
    	try{
	    	$sql="SELECT ser_cate_id FROM 
	    		rms_program_name WHERE service_id = $service_id "; // to get product id 
	    	$pro_id =  $db->fetchOne($sql);
	   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	    	if($pro_type==0){ // product រាយ  
	    		
		    	$sql1="select id,pro_qty from rms_product_location where pro_id = $pro_id $branch ";
		    	$qty_in_stock =  $db->fetchRow($sql1);
		    	
		    	$this->_name="rms_product_location";
		    	if(!empty($qty_in_stock)){
			    	$qty = $qty_in_stock['pro_qty'] - $qty_order ;
			    	$array = array(
			    			'pro_qty'=>$qty,
			    			);
			    	$where = " id = ".$qty_in_stock['id'];
			    	$this->update($array, $where);
		    	}else{
		    		$array = array(
		    				'pro_id'=>$pro_id,
		    				'brand_id'=>$this->getBranchId(),
		    				'pro_qty'=>-$qty_order,
		    				);
		    		$this->insert($array);
		    	}
		    	
		///////////////////////////ទំនិញជា Set///////////////////////
		    	
	    	}else{ // product set 
	    		
	    		$query = " select id , subpro_id , qty from rms_product_setdetail where pro_id = $pro_id ";
	    		$result_pro_set = $db->fetchAll($query);
	    		
	    		if(!empty($result_pro_set)){
	    			foreach ($result_pro_set as $set_detail){

	    				$query_qty_in_stock = " select id,pro_qty from rms_product_location where pro_id = ".$set_detail['subpro_id']." $branch ";
	    				$qty_in_stock = $db->fetchRow($query_qty_in_stock);
	    				
	    				if(!empty($qty_in_stock)){
	    					$last_qty = $qty_in_stock['pro_qty'] - ($qty_order * $set_detail['qty']);
	    					
	    					$this->_name = "rms_product_location";
	    					$array = array(
	    							'pro_qty'=>$last_qty,
	    							);
	    					$where = " id = ".$qty_in_stock['id'];
	    					$this->update($array, $where);
	    				}
	    				
	    			}
	    		}
	    	}
    	}catch (Exception $e){
    		echo $e->getMessage();
    	}
    }
    
    function getIdRecordUpdated($stu_id){
    	$db = $this->getAdapter();
    	$sql = "select id from rms_study_history where stu_id = $stu_id and is_finished=0 ";
    	
    }
    function getStudentExist($data){
    	$db = $this->getAdapter();
    	
    	$name = $data['en_name'];
    	$year = $data['study_year'];
    	$grade = $data['grade'];
    	$session = $data['session'];
    	$room = $data['room'];
    	
    	$sql = "select stu_enname from rms_student where stu_enname = '$name' and academic_year = $year and grade = $grade and session = $session and room = $room ";
    	return $db->fetchOne($sql);
    }
    
	function addRegister($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		//$paid_date = $data['paid_date'];
		$paid_date = date("Y-m-d H:i:s");
				
		$stu_id = $data['old_stu'];//$this->getNewAccountNumber($data['dept']);
		$receipt_number = $this->getRecieptNo($data['branch_id']);
			try{
				$gdb = new  Application_Model_DbTable_DbGlobal();
				//$this->_name='rms_student';
				$customer_type=1;
				if($data['student_type']==1){//existing student
					$rs_stu = $gdb->getStudentinfoById($stu_id);
				}elseif($data['student_type']==2){//testing student
					$rs_stu = $gdb->getStudentTestinfoById($stu_id);
					$arr = array(
							'is_registered'=>1,
							);
					$this->_name='rms_student_test_result';
					$where="id = ".$rs_stu['id'];
					$this->update($arr, $where);
					
					$dbg = new Application_Model_DbTable_DbGlobal();
					$stu_code = $dbg->getnewStudentId($data['branch_id'],$rs_stu['degree']);
					
					$arr = array(
						'customer_type' =>1,
						'stu_code'=>$stu_code,
						'academic_year'=>$data['study_year'],
						'create_date'=>date("Y-m-d H:i:s")
					);
					$this->_name='rms_student';
					$where="stu_id = ".$data['old_stu'];
					$this->update($arr, $where);
					
				}elseif($data['student_type']==3){//from crm
					$rs_stu = $gdb->getStudentinfoById($stu_id);
					$_dbgb = new Application_Model_DbTable_DbGlobal();
					$newSerial = $_dbgb->getTestStudentId($data['branch_id']);
					$arr = array(
							'customer_type' =>4,
							'is_studenttest' =>1,
							'serial' => $newSerial,
							'create_date'=>date("Y-m-d"),
							'create_date_stu_test'=>date("Y-m-d"),
					);
					$this->_name='rms_student';
					$where="stu_id = ".$stu_id;
					$this->update($arr, $where);
				}
			
				$cut_credit_memo = $data['grand_total']-$data['credit_memo'];
				if($cut_credit_memo<0){
					$credit_after=abs($cut_credit_memo);
					$cut_credit_memo = $data['grand_total'];
				}else{
					$cut_credit_memo = $data['credit_memo'];
					$credit_after = 0;
				}
				
				$arr = array(
					'balance_due'=>0
					);
				$this->_name='rms_student_payment';
				$where="student_id = ".$stu_id;
				$this->update($arr, $where);//clear old balance
				
				$arr=array(
					'branch_id'		=> $data['branch_id'],
					'revenue_type'  => $data['customer_type'],
					'data_from'		=> $data['student_type'],
					'student_id'	=> $data['old_stu'],
					'receipt_number'=> $receipt_number,
					'penalty'		=> $data['penalty'],
					'grand_total'	=> $data['grand_total'],
					'credit_memo'	=> $cut_credit_memo,
					'memo_id'		=> $data['credit_memo'],
					'paid_amount'	=> $data['paid_amount'],
					'balance_due'	=> $data['balance_due'],
					'amount_in_khmer'=> $data['money_in_khmer'],
					'payment_method'=> $data['payment_method'],
					'number'	    => $data['number'],
					'note'			=> $data['note'],
					'create_date'	=> $paid_date,
					'user_id'		=> $this->getUserId(),
					'academic_year'	=> $data['study_year'],
					'group_id'      => $rs_stu['group_id'],
					'paystudent_type'=> $rs_stu['is_stu_new'],
					'degree'		=> $rs_stu['degree'],
					'grade'			=> $rs_stu['grade'],
					'session'		=> $rs_stu['session'],
					'degree_culture'=> $rs_stu['calture'],
					'room_id'		=> $rs_stu['room'],
				);
				$paymentid = $this->insert($arr);
		
				if($data['student_type']==1 AND $data['customer_type']==1){ // only old_student can have credit_memo
					if(!empty($data['credit_memo_id'])){
						if($data['credit_memo']>0){
							$array=array(
									'total_amountafter'=>$credit_after,
							);
							$this->_name='rms_creditmemo';
							$where = " id = ".$rs_stu['credit_memo_id'];
							$this->update($array, $where);
						}
					}
				}
				
				/*alert ទៅទូរសព្ទដៃអាណាព្យាបាលសិស្ស*/
// 					$dbpush = new  Application_Model_DbTable_DbGlobal();
// 					$dbpush->getTokenUser(null,$id, 1);
				
				$this->_name="rms_student_paymentdetail";
				$ids = explode(',', $data['identity']);
				$dbitem = new Global_Model_DbTable_DbItemsDetail();
				if(!empty($ids))foreach ($ids as $i){
					$spd_id = $this->getStudentPaymentStart($data['old_stu'], $data['item_id'.$i],1);
					$this->_name="rms_student_paymentdetail";
					if(!empty($spd_id)){
						$arr = array(
								'is_start'=>0
						);
						$where=" id = ".$spd_id;
						$this->update($arr,$where);
					}
					
					$rs_item = $dbitem->getItemsDetailById($data['item_id'.$i],null,1);
					$_arr = array(
							'payment_id'	=>$paymentid,
							'service_type'	=>$rs_item['items_type'],
							'itemdetail_id'	=>$data['item_id'.$i],
							'payment_term'	=>$data['term_'.$i],
							'fee'			=>$data['price_'.$i],
							'qty'			=>$data['qty_'.$i],
							'qty_balance'	=>$data['qty_'.$i],
							'subtotal'		=>$data['subtotal_'.$i],
							'extra_fee'		=>$data['extra_fee'.$i],
							'discount_type'	=>$data['discount_type'.$i],
							'discount_percent'=>$data['discount_'.$i],
							'discount_amount'=>$data['discount_amount'.$i],
							'paidamount'	=>$data['total_amount'.$i],
							'is_onepayment'	=>$data['onepayment_'.$i],
							'start_date'	=>$data['date_start_'.$i],
							'validate'		=>$data['validate_'.$i],
							'is_start'		=>1,
							'note'			=>$data['remark'.$i],
							'is_parent'     =>$spd_id,
						);
					$this->insert($_arr);
					
			////////////////////////////////////////// if product type => insert to sale_detail //////////////////////////////	
					if($rs_item['items_type']==3){ // product
						if($rs_item['is_productseat']==1){ // product set
							$sql="select 
										set.pro_id as product_set_id,
										set.subpro_id as pro_id,
										set.qty as set_qty,
										idt.cost,
										lo.price
									from 
										rms_itemsdetail as idt,
										rms_product_setdetail as `set`,
										rms_product_location as lo
									where
										idt.id = set.subpro_id
										and set.subpro_id = lo.pro_id
										and set.pro_id = ".$rs_item['id']."
										and lo.brand_id = ".$data['branch_id'];
							$result = $db->fetchAll($sql);
							if(!empty($result)){foreach ($result as $row){
								$arr_sale = array(
										'payment_id'		=>$paymentid,
										'is_product_set'	=>1,
										'product_set_id'	=>$row['product_set_id'],
										'pro_id'			=>$row['pro_id'],
										'qty'				=>$row['set_qty'] * $data['qty_'.$i], // (qty of set detail) * (qty buy)
										'qty_after'			=>$row['set_qty'] * $data['qty_'.$i],
										'cost'				=>$row['cost'],
										'price'				=>$row['price'],
										'user_id'			=>$this->getUserId(),
									);
								$this->_name="rms_saledetail";
								$this->insert($arr_sale);
							}}
						}else{ // product normal
							$arr_sale = array(
									'payment_id'		=>$paymentid,
									'is_product_set'	=>0,
									'product_set_id'	=>$data['item_id'.$i],
									'pro_id'			=>$data['item_id'.$i],
									'qty'				=>$data['qty_'.$i],
									'qty_after'			=>$data['qty_'.$i],
									'cost'				=>$rs_item['cost'],
									'price'				=>$data['price_'.$i],
									'user_id'			=>$this->getUserId(),
								);
							$this->_name="rms_saledetail";
							$this->insert($arr_sale);
						}
					}
				// បង់លើផលិតផល
				// $this->updateStock($data['service_'.$i],$data['qty_'.$i],$data['product_type_'.$i]);
				}
				$db->commit();
		}catch (Exception $e){
			$db->rollBack();//
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	
	function getParentIDToUpdateBack($payment_id){
		$db=$this->getAdapter();
		$sql="select is_parent from rms_student_paymentdetail where payment_id = $payment_id";
		return $db->fetchAll($sql);	
	}
		
	function updateStockBack($payment_id){
		$db = $this->getAdapter();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission('brand_id');
		
		$sql="select service_id,qty ,type from rms_student_paymentdetail where payment_id = $payment_id and type = 4 ";
		$ser_id = $db->fetchAll($sql);
		
		if(!empty($ser_id)){
			foreach ($ser_id as $i){
				$sql1 = "select ser_cate_id,pro_type from rms_program_name where service_id = ". $i['service_id'];
				$pro_id = $db->fetchRow($sql1);
				if(!empty($pro_id)){
					if($pro_id['pro_type']==0){ // product រាយ
						$sql2 = "select id,pro_qty from rms_product_location where pro_id = ".$pro_id['ser_cate_id']."  $branch_id ";
						$result = $db->fetchRow($sql2);
						if(!empty($result)){
							$qty = $result['pro_qty'] + $i['qty'];
							
							$this->_name="rms_product_location";
							
							$array = array(
									'pro_qty'=>$qty,
									);
							$where = " id = ".$result['id'];
							$this->update($array, $where);
						}
						
					}else{  // product set
						
						$query = "select id , subpro_id , qty from rms_product_setdetail where pro_id = ".$pro_id['ser_cate_id'];
						$result_set = $db->fetchAll($query);
						
						if(!empty($result_set)){
							foreach ($result_set as $pro_in_set){
								$query_qty_in_stock = "select id,pro_qty from rms_product_location where pro_id = ".$pro_in_set['subpro_id']."  $branch_id ";
								$result_qty_in_stock = $db->fetchRow($query_qty_in_stock);
								
								if(!empty($result_qty_in_stock)){
									$last_qty = $result_qty_in_stock['pro_qty'] + ($i['qty'] * $pro_in_set['qty']);
									$this->_name="rms_product_location";
										
									$array = array(
											'pro_qty'=>$last_qty,
									);
									$where = " id = ".$result_qty_in_stock['id'];
									$this->update($array, $where);
								}
								
							}
						}
						
					}
				}
			}
		}
	}
	
	function getParentIdStudentHistory($payment_id){
		$db = $this->getAdapter();
		$sql=" select is_parent from rms_study_history where payment_id = $payment_id ";
		return $db->fetchOne($sql);
	}
	
	function UpdateStudentInfoBack($id_old_record,$payment_id){
		$db = $this->getAdapter();
		$sql="select * from rms_study_history where id = $id_old_record ";
		$stu_info = $db->fetchRow($sql);
		if(!empty($stu_info)){
			$this->_name="rms_student";
			$array = array(
					'stu_type'=>$stu_info['stu_type'],
					'academic_year'=>$stu_info['academic_year'],
					'degree'=>$stu_info['degree'],
					'grade'=>$stu_info['grade'],
					'session'=>$stu_info['session'],
					//'teacher_id'=>$stu_info['teacher_id'],
					'room'=>$stu_info['room'],
					);
			$where = " stu_id = ".$stu_info['stu_id'];
			$this->update($array, $where);
		}
		
		$this->_name="rms_study_history";
		$where = " payment_id = $payment_id ";
		$this->delete($where);
		
	}
	
	function updatePaymentInfoBack($payment_id,$type){
		$db = $this->getAdapter();
		$sql="select * from rms_student_paymentdetail where payment_id = $payment_id ";
		$id_old_record = $db->fetchAll($sql);
		if(!empty($id_old_record)){
			foreach ($id_old_record as $result){
				if($result['is_parent']>0 && $result['type'] != 4){
					$this->_name="rms_student_paymentdetail";
					$array = array(
							'is_start'=>1,
							);
					$where = " id = ".$result['is_parent'];
					$this->update($array, $where);
				}
			}
		}			
	}
	function deleteFromGroup($stu_id,$group_id){
		$db = $this->getAdapter();
		$where = " group_id = $group_id and stu_id = $stu_id";
		$this->_name="rms_group_detail_student";
		$this->delete($where);
	}
	
	function updateCreditMemoBack($data){
		$db = $this->getAdapter();
		$sql=" select total_amountafter from rms_creditmemo where id = ".$data['credit_memo_id'];
		$result = $db->fetchOne($sql);
		
		$credit_memo = $result + $data['credit_memo'];
		
		$array = array(
				'total_amountafter' => $credit_memo,
				'type' => 0,
				);
		$where = " id = ".$data['credit_memo_id'];
		$this->_name='rms_creditmemo';
		$this->update($array, $where);
	}
	
	function updateCustomerPayment($data,$id){
		if($data['void']==1){
			$arr = array(
					'is_void'=>1,
					'void_by'=>$this->getUserId(),
					);
			$where = "id = $id";
			$this->_name="rms_customer_payment";
			$this->update($arr, $where);
		}
	}
	
	function updateRegister($data,$payment_id){
		$db = $this->getAdapter();//ស្ពានភ្ជាប់ទៅកាន់Data Base
		$db->beginTransaction();//ទប់ស្កាត់មើលការErrore , មានErrore វាមិនអោយចូល
		
		if($data['void']==1){  // void
			try{	
				$rsold = $this->getStudentPaymentByID($payment_id);
				if($rsold['data_from']==2){
					
				}
				
				$this->_name='rms_student_payment';
				$arra=array(
					'is_void'=>$data['void'],
					'void_by'=>$this->getUserId(),
					'void_note'=>$data['void_note'],
				);
				$where = " id = ".$payment_id;
				$this->update($arra, $where);
			
				if(!empty($data['credit_memo_id'])){//check again because it old code
					$this->updateCreditMemoBack($data);
				}				
				
				$where = " payment_id = $payment_id";
				$this->_name='rms_saledetail';
				$this->delete($where);
				// update payment and validate of service and tuition fee info back ,  and update stock back to origin	
				$this->updatePaymentInfoBack($payment_id,1);   // 1 is pay for both service and tuition fee
				$db->commit();
				return 0;
			}catch (Exception $e){
				$db->rollBack();
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}					
	}
    function getAllStudentRegister($search=null){
    	$_db  = new Application_Model_DbTable_DbGlobal();
    	$user = $_db->getUserAccessPermission('sp.user_id');
    	$branch_id = $_db->getAccessPermission('sp.branch_id');
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$print=$tr->translate("PRINT_SCHO");
    	$db=$this->getAdapter();
		
    	$sql=" SELECT 
    				sp.id,
    				(SELECT branch_namekh FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
    				sp.receipt_number,
	    			s.stu_code,
	    			(CASE WHEN s.stu_khname IS NULL OR s.stu_khname='' THEN s.stu_enname ELSE s.stu_khname END) AS name,
	    			(SELECT name_en FROM `rms_view` WHERE type=2 AND key_code = s.sex LIMIT 1) AS sex,
	    			(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=sp.academic_year) AS YEAR,
	    	        (SELECT rms_items.title FROM rms_items WHERE rms_items.id=sp.degree AND rms_items.type=1 LIMIT 1) AS degree,
			        (SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=sp.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
	 		        sp.penalty,sp.grand_total,sp.credit_memo,sp.paid_amount,sp.balance_due,
					(SELECT name_en FROM `rms_view` WHERE type=8 AND key_code=payment_method LIMIT 1) AS payment_method,
					number,sp.create_date ,
	 		       (SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS user,
	 		       (SELECT name_en FROM rms_view WHERE TYPE=10 AND key_code = sp.is_void LIMIT 1) AS void,
	 		       (SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = sp.void_by LIMIT 1) AS void_by
 			   FROM 
    				rms_student AS s,
					rms_student_payment AS sp
				WHERE 
					s.stu_id=sp.student_id 
					$user 
					$branch_id ";
    	$from_date =(empty($search['start_date']))? '1': " sp.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sp.create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['adv_search']));
    		$s_where[]= " stu_code LIKE '%{$s_search}%'";
    		$s_where[]=" receipt_number LIKE '%{$s_search}%'";
    		$s_where[]= " stu_khname LIKE '%{$s_search}%'";
    		$s_where[]= " stu_enname LIKE '%{$s_search}%'";
    		$s_where[]= " last_name LIKE '%{$s_search}%'";
    		$s_where[]= " sp.grade LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(($search['branch_id']>0)){
    		$where.= " AND sp.branch_id = ".$search['branch_id'];
    	}
    	if(!empty($search['degree'])){
    		$where.=" AND sp.degree=".$search['degree'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND sp.academic_year=".$search['study_year'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND sp.session=".$search['session'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=" AND sp.grade=".$search['grade_all'];
    	}
    	if(!empty($search['user'])){
    		$where.=" AND sp.user_id=".$search['user'];
    	}
    	$order=" ORDER BY sp.id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
    
//     function getCustomerPayment($search){
//     	$db=$this->getAdapter();
//     	$sql="SELECT
// 			    	*,
// 			    	(select name_en from rms_view where type=2 and key_code=sex LIMIT 1) as sex,
// 			    	(select name_en from rms_view where type=10 and key_code=is_void LIMIT 1) as status,
// 			    	(select first_name from rms_users as u where u.id=user_id LIMIT 1) as user
// 			    FROM
// 			    	rms_customer_payment
// 			    WHERE 1 ";
    	 
//     	$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
//     	$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
//     	$where = " AND ".$from_date." AND ".$to_date;
    	 
//     	if(!empty($search['adv_search'])){
//     		$s_where=array();
//     		$s_search=addslashes(trim($search['adv_search']));
//     		$s_where[]= " name_kh LIKE '%{$s_search}%'";
//     		$s_where[]=" name_en LIKE '%{$s_search}%'";
//     		$s_where[]=" receipt_no LIKE '%{$s_search}%'";
//     		$where.=' AND ('.implode(' OR ', $s_where).')';
//     	}
//     	$order=" ORDER BY id DESC";
//     	return $db->fetchAll($sql.$where.$order);
//     }
    function getRegisterById($id){
    	$db=$this->getAdapter();
    	$sql=" SELECT 
	    			s.stu_id,
	    			s.stu_code,
	    			sp.receipt_number,
	    			sp.branch_id,
	    			s.academic_year,
	    			s.stu_khname,
	    			s.stu_enname,
	    			s.sex,
	    			s.session,
	    			s.degree,
	    			s.grade,
			    	sp.paid_amount,
			    	sp.is_void,sp.create_date,
			    	sp.balance_due,sp.amount_in_khmer,
			    	sp.note,sp.time,
			    	spd.start_date,
			    	spd.validate,spd.is_start
		    	FROM 
		    		rms_student AS s,
		    		rms_student_payment AS sp ,
		    		rms_student_paymentdetail AS spd
		    	WHERE 
    				s.stu_id=sp.student_id 
    				AND sp.id=spd.payment_id 
    				AND sp.id=".$id;
    	$dbl = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbl->getAccessPermission(" sp.branch_id ");
    	return $db->fetchRow($sql);
    }
    function getAllGrade($grade_id,$student_id,$is_stutested){
    	$db = new Application_Model_DbTable_DbGlobal();
    	return $db->getAllGradeStudyByDegree($grade_id,$student_id,$is_stutested);
    }
    function getPaymentTerm($generat,$payment_term,$grade){
//     	$db = $this->getAdapter();
//     	$_db  = new Application_Model_DbTable_DbGlobal();
//     	$branch_id = $_db->getAccessPermission();
//     	$sql="SELECT tfd.id,tfd.tuition_fee FROM rms_tuitionfee AS tf,rms_tuitionfee_detail AS tfd WHERE tf.id = tfd.fee_id
//     	 		AND tfd.fee_id = $generat AND tfd.class_id = $grade AND tfd.payment_term = $payment_term  $branch_id ";
    	 		
//     	return $db->fetchRow($sql);
    }
    function getStudentInfoBalance($studentid){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
				  s.stu_id,
				  s.academic_year,
				  (SELECT id FROM rms_creditmemo WHERE student_id = $studentid AND total_amountafter>0 LIMIT 1) AS id,
				  (SELECT total_amountafter FROM rms_creditmemo WHERE student_id = $studentid AND total_amountafter>0 LIMIT 1) AS total_amountafter,
				  (SELECT SUM(sp.balance_due) FROM rms_student_payment AS sp WHERE sp.student_id=$studentid LIMIT 1 )AS balance
				FROM
				  rms_student AS s
				WHERE s.stu_id=$studentid LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
   
//     function getAllYearsProgramFee(){
//     	$db = $this->getAdapter();
//     	$sql = "SELECT id,CONCAT(from_academic,'-',to_academic,'(',generation,')') AS years,(select name_en from rms_view where type=7 and key_code=time) as time FROM rms_tuitionfee
//     	        WHERE `status`=1 GROUP BY from_academic,to_academic,generation,time ";
//     	$order=' ORDER BY id DESC';
//     	return $db->fetchAll($sql.$order);
//     }
    
    function getAllYears($type=1){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	return $_db->getAllYear();
    }
    
    function getAllYearsServiceFee(){
    	$db = $this->getAdapter();
    	$sql = "SELECT id,CONCAT(from_academic,'-',to_academic) AS years FROM rms_servicefee WHERE `status`=1";
    	$order=' ORDER BY id DESC';
    	return $db->fetchAll($sql.$order);
    }     
    function getPrefixByDegree($degree){
    	$db= $this->getAdapter();
    	$sql=" SELECT shortcut FROM `rms_items` WHERE id=$degree AND type=1  LIMIT 1";
    	return $db->fetchOne($sql);
    }
    public function getNewAccountNumber($branch_id,$degree){
    	$db = new Application_Model_DbTable_DbGlobal();
    	return $db->getnewStudentId($branch_id,$degree);
    }
    function resetReceipt(){
    	$db = $this->getAdapter();
    	$receipt = 0;
    	for($date=6;$date<=21;$date++){
    		$sql="SELECT *  FROM rms_student_payment ";
    		$from_date=" create_date >= '".date("Y-m-".$date)." 00:00:00'";
    		$to_date =" create_date <= '".date("Y-m-".$date)." 23:59:59'";
    		$where = " WHERE ".$from_date." AND ".$to_date." ORDER BY create_date ASC ";
    		$rsp = $db->fetchAll($sql.$where);
    		if(!empty($rsp)){
    			$this->_name="rms_student_payment";
    			foreach($rsp as $rp){
    			$receipt =$receipt+1;
    			$pre=0;
    			$acc_length = strlen((int)$receipt);
    			for($i = $acc_length;$i<5;$i++){
    				$pre.='0';
    			}
    			$arrr = array(
    					'receipt_number'=>$pre.$receipt,
    					'note'=>$rp['note'].'('.$rp['receipt_number'].')'
    					);
    			$where = "id=".$rp['id'];
    			$this->update($arrr, $where);
    		}}
    		
    		$sql="SELECT * FROM rms_student_test ";
    		$from_date =" paid_date >= '".date("Y-m-".$date)." 00:00:00'";
    		$to_date =" paid_date <= '".date("Y-m-".$date)." 23:59:59'";
    		$where = " WHERE ".$from_date." AND ".$to_date." ORDER BY paid_date ASC ";
    		$rst = $db->fetchAll($sql.$where);
    		if(!empty($rst)){
    			$this->_name="rms_student_test";
    			foreach($rst as $rt){
    				$receipt =$receipt+1;
    				$pre=0;
    				$acc_length = strlen((int)$receipt);
    				for($i = $acc_length;$i<5;$i++){    					
    					$pre.='0';
    				}
    				$arr = array(
    						'receipt_no'=>$pre.$receipt,
    						'note'=>$rt['note'].'('.$rt['receipt_no'].")"
    				);
    				$where = "id=".$rt['id'];
    				$this->update($arr, $where);
    			}
    		}
    	}
     }
    public function getRecieptNo($branch=0){
    	$db = $this->getAdapter();
    	if($branch==0){
    		$_db = new Application_Model_DbTable_DbGlobal();
    		$branch_id = $_db->getAccessPermission();
    	}else{
    		$branch_id = " and branch_id = $branch ";
    	}
    	
    	$sql="SELECT count(id)  FROM rms_student_payment where 1 $branch_id LIMIT 1 ";
    	$payment_no = $db->fetchOne($sql);
    	
    	$sql1="SELECT count(id)  FROM ln_income where 1 $branch_id LIMIT 1 ";
    	$income_no = $db->fetchOne($sql1);
    	
//     	$sql2="SELECT count(id)  FROM rms_student_test where total_price>0 AND paid_date>='2018-03-06' and is_paid=1 $branch_id LIMIT 1 ";
//     	$stu_test_no = $db->fetchOne($sql2); 

//     	$sql3="SELECT count(id)  FROM rms_change_product where create_date>='2018-03-06' $branch_id LIMIT 1 ";
//     	$change_product_no = $db->fetchOne($sql3);
    	
//     	$sql4="SELECT count(id)  FROM rms_customer_payment where 1 $branch_id LIMIT 1 ";
//     	$customer_payment = $db->fetchOne($sql4);
    	
//     	$sql5="SELECT count(id)  FROM rms_student_clear_balance where 1 $branch_id LIMIT 1 ";
//     	$clear_balance = $db->fetchOne($sql5);
    	
    	$new_acc_no= (int)$payment_no + (int)$income_no +  1;
    	
    	$acc_length = strlen((int)$new_acc_no+1);
    	$pre=0;
    	for($i = $acc_length;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    //select GEP all old student
    function getAllGepOldStudent(){
    	$db=$this->getAdapter();
    	$sql="SELECT s.stu_id As stu_id,s.stu_code As stu_code FROM rms_student AS s
    	      WHERE s.stu_type=2 AND s.is_subspend=0 and s.status=1 ORDER BY stu_id DESC  ";
    	return $db->fetchAll($sql);
    }
    //select Gep old student by id 
    function getAllGepOldStudentName(){
    	$db=$this->getAdapter();
    	$sql="SELECT s.stu_id As stu_id,			
			(CASE WHEN s.stu_khname IS NULL THEN s.stu_enname ELSE s.stu_khname END) AS name	
		 FROM rms_student AS s
    	WHERE s.stu_type=2 AND s.is_subspend=0 and s.status=1 ORDER BY stu_id DESC ";
    	return $db->fetchAll($sql);
    }
    //select Gep old student by name
    function getGepOldStudent($stu_id){
    	$db=$this->getAdapter();
    	$sql="SELECT stu_id,stu_enname,stu_khname,sex,`session` As ses,degree,grade FROM rms_student 
    	       WHERE  stu_type=2 AND stu_id=$stu_id LIMIT 1";
    	return $db->fetchRow($sql);
    }
    //select all Gerneral old student
    function getAllGerneralOldStudent(){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	return $_db->getAllStuCode();
//     	$branch_id = $_db->getAccessPermission();
//     	$sql="SELECT s.stu_id As id,s.stu_id As stu_id,s.stu_code As stu_code,
//     		s.stu_code AS name,
// 	    	(CASE WHEN s.stu_khname IS NULL THEN s.stu_enname ELSE s.stu_khname END) AS stu_name
// 	    	FROM rms_student AS s
// 	    	WHERE s.status=1 and s.is_subspend=0 AND customer_type=1 $branch_id  ORDER BY stu_type DESC ";
//     	return $db->fetchAll($sql);
    }
    //select general  old student by id
    
    function getAllGerneralOldStudentName(){
    	$db = new Application_Model_DbTable_DbGlobal();
    	return $db->getAllStudent();
    }
    //select general  old student by name
    
    function getStudentById($stu_id){
    	$db = new Application_Model_DbTable_DbGlobal();
    	return $db->getStudentinfoById($stu_id);
    }

//  function getDegree(){
// //     	$db=$this->getAdapter();
// //     	$sql="SELECT dept_id AS id,CONCAT(en_name,'-',kh_name) AS `name` FROM rms_dept  WHERE 1";
// //     	return $db->fetchAll($sql);
//     	$_dbg = new Application_Model_DbTable_DbGlobal();
//     	return $_dbg->getAllItems(1,null);
//     }

    //function add rms_student_detailpayment
    function addStudentPaymentDetail($data,$type,$paymentid,$complete,$comment,$payment_id_ser){
    	$db=$this->getAdapter();
    	    if($type==4){
    	    	$fee = $data["tuitionfee"];
    	    	$subtotal=$data["tuitionfee"]-($data["tuitionfee"]*$data['discount']/100);
    	    	$discount=$data['discount'];
    	    	$paidamount=$data['books'];
    	    	$paidamount=$paidamount-($data["remark"]+$data["addmin_fee"]);
    	    	$balance= $data['total'] - $data['books'];
    	    }elseif($type==5){
    	    	$fee = $data["remark"];
    	    	$subtotal = $data["remark"];
    	    	$paidamount = $data['remark'];
    	    	$discount = 0;
    	    	$balance = 0;
    	    	$comment="បង់រួច";
    	    }elseif($type==6){
    	    	$fee = $data["addmin_fee"];
    	    	$subtotal = $data["addmin_fee"];
    	    	$paidamount=$data['addmin_fee'];
    	    	$discount=0;
    	    	$balance=0;
    	    	$comment="បង់រួច";
    	    }
    		$arr=array(
    				'payment_id'=>$paymentid,
    				'type'=>1,
    				'service_id'=>$type,
    				'payment_term'=>$data['payment_term'],
    				'fee'=>$fee,
    				'qty'=>1,
    				//'subtotal'=>$data['total'],
    				'subtotal'=>$subtotal,
    				'paidamount'=>$paidamount,
    				'balance'=>$balance,
    				'discount_percent'=>$discount,
    				'discount_fix'=>0,
    				'note'=>$data['not'],
    				'start_date'=>$data['start_date'],
    				'validate'=>$data['end_date'],
    				'references'=>'frome registration',
    				'is_parent'		=>$payment_id_ser,
    				'is_complete'	=>$complete,
    				'comment'		=>$comment,
    				'user_id'=>$this->getUserId(),
    		);
    		
    		$this->insert($arr);
    }
    function getGradeAllDept($type){
    	$db = new Application_Model_DbTable_DbGlobal();
    	return $db->getAllGradeStudy($type);
    }  
    function getGradeAll(){
    	$db=$this->getAdapter();
    	$sql="SELECT major_id AS id,major_enname AS `name` FROM rms_major WHERE major_enname!='' AND is_active=1 AND major_enname!='' ";
    	return $db->fetchAll($sql);
    }
    function getAllDegree(){
    	$db=$this->getAdapter();
//     	$sql="SELECT dept_id AS id,en_name AS `name` FROM rms_dept WHERE is_active=1 AND en_name!='' ";
//     	return $db->fetchAll($sql);
    	$_dbg = new Application_Model_DbTable_DbGlobal();
    	return $_dbg->getAllItems(1,null);
    }
//     function getAllDegreeGEP(){
//     	$db=$this->getAdapter();
//     	$sql="SELECT dept_id AS id,en_name AS `name` FROM rms_dept WHERE en_name!=''  AND is_active=1 ";
//     	return $db->fetchAll($sql);
//     }
    
    function getAllDegreeBac($type){
    	$db=new Application_Model_DbTable_DbGlobal();
    	return $db->getAllItems($type,null);
    }
   
    function getGradeAllBac(){
    	$db=$this->getAdapter();
    	$sql="SELECT major_id AS id,major_enname AS `name` FROM rms_major WHERE major_enname!='' AND  is_active=1 ";
    	return $db->fetchAll($sql);
    }
    function getGradeAllKid(){
    	$db=$this->getAdapter();
    	$sql="SELECT major_id AS id,major_enname AS `name` FROM rms_major WHERE major_enname!='' AND dept_id =1  AND is_active=1 ";
    	return $db->fetchAll($sql);
    }
    
    function getAllUser(){
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql="SELECT id,CONCAT(last_name,' - ',first_name) as name FROM rms_users WHERE active=1 $branch_id order by id desc ";
    	return $db->fetchAll($sql);
    }
    
    function getGradeGepAll(){
    	$db=$this->getAdapter();
    	$sql="SELECT major_id AS id,major_enname AS `name` FROM rms_major WHERE major_enname!='' AND is_active=1 ";
    	return $db->fetchAll($sql);
    }
    function getAllGrades(){
    	$db=$this->getAdapter();
    	$sql="SELECT major_id AS id,major_enname AS `name` FROM rms_major WHERE major_enname!='' AND is_active=1";
    	return $db->fetchAll($sql);
    }
    function getServicesAll(){
    	$db=$this->getAdapter();
    	$sql="SELECT service_id AS id,title FROM rms_program_name WHERE `type` = 2 AND title!='' ";
    	return $db->fetchAll($sql);
    }
    public function getNewStudent($newid,$stu_type){
    	$db = $this->getAdapter();
    	$sql="  SELECT COUNT(stu_id)  FROM rms_student WHERE stu_type IN (1,3)";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$new_acc_no=100+$new_acc_no;
    	$pre='';
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<=5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    function getAllGradeGEP($grade_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT CONCAT(major_enname,'-',major_khname) As name,major_id As id FROM rms_major WHERE dept_id=".$grade_id;
    	$order=' ORDER BY id DESC';
    	return $db->fetchAll($sql.$order);
    }
//     function getAllpaymentTerm(){
//     	$db = $this->getAdapter();
//     	$sql="select key_code as id , name_en as name from rms_view where type=6 and status=1 ";
//     	return $db->fetchAll($sql);
//     }
    
//     public function getAllService(){
//     	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
//     	$rows = $this->getAllServiceForOption();
//     	//array_unshift($rows,array('service_id' => '-1',"title"=>$tr->translate("ADD")) );
//     	array_unshift($rows,array('id' => '',"name"=>"Select Grade"));
//     	$options = '';
//     	if(!empty($rows))foreach($rows as $value){
//     		$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
//     	}
//     	return $options;
//     }
    
    public function getAllService(){
    	$db = $this->getAdapter();
//     	$sql = "SELECT
// 				  p.service_id ,
// 				  p.`title`
// 				FROM
// 				  `rms_servicefee_detail` as sfd,
// 				  `rms_servicefee`  as sf,
// 				  `rms_program_name` as p
// 				WHERE `sf`.id = `sfd`.`service_feeid`
// 				  AND p.`service_id`=sfd.`service_id`
// 				  or type=1
// 				GROUP BY service_id ";
//     	return $db->fetchAll($sql);
    }
    
    function getAllProductName(){
    	$db = $this->getAdapter();
    	$sql="SELECT id,pro_name FROM `rms_product` WHERE STATUS=1 AND pro_name!='' ORDER BY pro_name";
    	return $db->fetchAll($sql);
    }
    
    function getServiceType($service_id){
    	$db = $this->getAdapter();
    	$sql="select type,pro_type from rms_program_name where service_id=$service_id";
    	return $db->fetchRow($sql);
    }
    
    public function getServiceFee($year,$item_id,$termid,$student_id,$branch_id){
    	$db=$this->getAdapter();
    	
    	$sql="SELECT items_type,is_productseat FROM `rms_itemsdetail` WHERE id=$item_id LIMIT 1";
    	$rs_pro=$db->fetchRow($sql);
    	$item_type = $rs_pro['items_type'];
    	$is_set = $rs_pro['is_productseat'];
    	if($item_type==1 OR $item_type==2){//grade or service
    		$sql="SELECT 
    					tfd.id,
    					tfd.tuition_fee AS price,
						(SELECT is_onepayment FROM `rms_itemsdetail` WHERE id=$item_id LIMIT 1) as onepayment,
						(SELECT is_productseat FROM `rms_itemsdetail` WHERE id=$item_id LIMIT 1) as is_set,
    					(SELECT items_type FROM `rms_itemsdetail` WHERE id=$item_id LIMIT 1) as items_type,
						(SELECT spd.validate FROM rms_student_payment as sp,rms_student_paymentdetail as spd
							WHERE sp.student_id = $student_id 
								AND spd.itemdetail_id = $item_id 
							 	AND spd.is_start=1 
							 	AND sp.`id`=spd.`payment_id` 
							 	AND sp.is_void=0 
						 	ORDER BY 
						 		spd.validate DESC LIMIT 1) AS validate 
    		 		FROM 
    		 			rms_tuitionfee AS tf,
    		 			rms_tuitionfee_detail AS tfd 
    		  		WHERE 
    		  			tf.id = tfd.fee_id
    					AND tfd.class_id = $item_id 
    					AND tfd.payment_term = $termid 
    			";
    		if($item_type==1){// grade
    			$sql.=" AND tf.type =1 AND tf.id = $year ";
    		}
    		if($item_type==2){//service
    			$sql.=" AND tf.type = 2 ";
    		}
    		$sql.=" AND branch_id = $branch_id LIMIT 1";
    		return $db->fetchRow($sql);
    	}elseif ($item_type==3){//product
    		if($is_set==1){//for set
    			$sql="SELECT
    			 			price,
    						is_onepayment as onepayment,
    						is_productseat as is_set,
    						items_type,
    						(SELECT spd.validate FROM rms_student_payment as sp,rms_student_paymentdetail as spd
    							WHERE sp.student_id = $student_id 
    								AND spd.itemdetail_id = $item_id
    				  				ANd spd.is_start=1 
    				  				AND sp.`id`=spd.`payment_id` 
    				  				AND sp.is_void=0 
    				  			ORDER BY spd.validate DESC LIMIT 1) AS validate
    					FROM 
    						`rms_itemsdetail` 
    					WHERE 
    						id=$item_id LIMIT 1
    				";
    		}else{
    			$sql="SELECT
    		 				price,
    						(SELECT is_onepayment FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=$item_id LIMIT 1) as onepayment,
    						(SELECT is_productseat FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=$item_id LIMIT 1) as is_set,
    						(SELECT items_type FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=$item_id LIMIT 1) as items_type,
    						(SELECT spd.validate FROM rms_student_payment as sp,rms_student_paymentdetail as spd
				    			WHERE sp.student_id = $student_id 
					    			AND spd.itemdetail_id = $item_id
					    			ANd spd.is_start=1 
					    			AND sp.`id`=spd.`payment_id` 
					    			AND sp.is_void=0 
				    			ORDER BY spd.validate DESC LIMIT 1) AS validate
    					FROM 
    						`rms_product_location` 
    					WHERE 
    						pro_id=$item_id 
    						AND brand_id = $branch_id LIMIT 1
    				";
    		}
    		return $db->fetchRow($sql);
    	}
    }
    
    function getProductFee($service_id){
    	$db=$this->getAdapter();
    	$sql="select price,cost from rms_program_name where service_id=$service_id";
    	return $db->fetchRow($sql);
    }
    
    function getStudentPaymentByID($id){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    			 sp.*,
	    		 s.stu_enname,
	    		 s.stu_khname,
	    		 s.sex,
	    		 s.stu_code,
	    		 s.stu_id,
	    		 s.is_stu_new,
	    		 (SELECT rms_items.title FROM rms_items WHERE rms_items.id=s.degree AND rms_items.type=1 LIMIT 1) AS degree,
	    		 
	    		 (SELECT sgh.group_id FROM `rms_group_detail_student` AS sgh WHERE sgh.stu_id = sp.`student_id` ORDER BY sgh.gd_id DESC LIMIT 1) as group_id,
	    		 (select first_name from rms_users as u where u.id=sp.user_id) as first_name,
	    		 (select last_name from rms_users as u where u.id=sp.user_id) as last_name
    		FROM
    		  	rms_student_payment as sp,
    		  	rms_student as s
    		where 
    			s.stu_id = sp.student_id
    			AND sp.id=$id AND is_closed=0 ";
    	return $db->fetchRow($sql);
    }
    
    function getCustomerPaymentByID($id){
    	$db=$this->getAdapter();
    	$sql="SELECT
    				*
    			FROM
			    	rms_customer_payment
    			where
			    	id=$id ";
    	return $db->fetchRow($sql);
    }
    
    function getCustomerPaymentDetailByID($id){
    	$db=$this->getAdapter();
    	$sql="select * from rms_customer_payment_detail where customer_id=$id ";
    	return $db->fetchAll($sql);
    }
    
    function getStudentPaymentDetailServiceByID($id){
    	$db=$this->getAdapter();
    	$sql="SELECT sd.*,
			(SELECT (title) FROM `rms_itemsdetail` WHERE id=itemdetail_id LIMIT 1) as item_name
    	FROM rms_student_paymentdetail AS sd 
    	
    	WHERE sd.payment_id=$id ";
    	return $db->fetchAll($sql);
    }
    
    function getServiceOnlyByID($id){
    	$db=$this->getAdapter();
    	$sql="select * from rms_student_paymentdetail where payment_id=$id ";
    	return $db->fetchAll($sql);
    }
    
    function getProductOnlyByID($id){
    	$db=$this->getAdapter();
    	$sql="select * from rms_student_paymentdetail where payment_id=$id ";
    	return $db->fetchAll($sql);
    }
    
    function getBranchInfo(){
    	$db=$this->getAdapter();
    	$sql=" select * from rms_branch where br_id = ".$this->getBranchId();
    	return $db->fetchRow($sql);
    }
	
	function getAllTeacherByGrade($grade_id,$session){
    	$db = $this->getAdapter();
    	$_db= new Application_Model_DbTable_DbGlobal();
    	$branch_id=$_db->getAccessPermission();
    	$sql = " SELECT t.id,CONCAT(t.teacher_name_en) as name FROM `rms_teacher` AS t,`rms_teacher_subject` AS ts
              WHERE t.id = ts.teacher_id AND ts.subject_id = $grade_id and ts.session=$session $branch_id";
		return $db->fetchAll($sql);
    }
	
    function getTeacherEdit($payment_id){
    	$db = $this->getAdapter();
    	$sql = " select student_id from rms_student_payment where id = $payment_id ";
    	$stu_id = $db->fetchOne($sql);
    	if(!empty($stu_id)){
    		$sql1="select teacher_id from rms_student where stu_id = $stu_id ";
    		return $db->fetchOne($sql1);
    	}
    }
    
    function getAllProvince(){
		$db = $this->getAdapter();
    	$sql = " select province_id as id , province_kh_name as name from ln_province where status=1 ";
    	return $db->fetchAll($sql);
	}
	function getAllDistrict(){
		$db = $this->getAdapter();
		$sql = " select dis_id as id , district_namekh as name from ln_district where status=1 ";
		return $db->fetchAll($sql);
	}
	function getAllCommune(){
		$db = $this->getAdapter();
		$sql = " select com_id as id , commune_namekh as name from ln_commune where status=1 ";
		return $db->fetchAll($sql);
	}
	function getAllVillage(){
		$db = $this->getAdapter();
		$sql = " select vill_id as id , village_namekh as name from ln_village where status=1 ";
		return $db->fetchAll($sql);
	}    
	function getAllStudentTested($branch_id){//get all student test
		$_db = new Application_Model_DbTable_DbGlobal();
		return $_db->getAllstudentTest($branch_id,1);
	}
	function getStudentTestInfo($stu_test_id){
		$_db = new Application_Model_DbTable_DbGlobal();
		return $_db->getStudentinfoById($stu_test_id);
	}
	function getCreditMemoByStuId($stu_id){
		$db=$this->getAdapter();
		$sql="SELECT id, total_amountafter from rms_creditmemo where student_id = $stu_id and total_amountafter>0 ";
		return $db->fetchRow($sql);
	}
	
	function getStartDate($service_id , $stu_id){
		$db = $this->getAdapter();
		$sql = "SELECT spd.validate from rms_student_payment as sp,rms_student_paymentdetail as spd
				where sp.student_id = $stu_id and spd.service_id = $service_id and spd.is_complete=1 and spd.is_start=1 AND sp.`id`=spd.`payment_id` and sp.is_void=0 ";
		$order=' ORDER BY spd.id DESC';
		return $db->fetchOne($sql.$order);
	}
	function getStudentPaymentHistory($studentid){
		$db = $this->getAdapter();
		$sql="SELECT 
    			  spd.id,
				  spd.fee,
				  spd.qty,
				  spd.subtotal,
				  spd.extra_fee,
				  spd.discount_percent,
				  spd.discount_amount,
				  (SELECT dis_name FROM `rms_discount` WHERE disco_id=spd.discount_type LIMIT 1) AS discount_type,
				  spd.paidamount,
				  spd.note,
				  spd.is_onepayment,
				  DATE_FORMAT(spd.start_date, '%d-%m-%Y') AS start_date ,
				  DATE_FORMAT(spd.validate, '%d-%m-%Y') AS validate ,
				  sp.receipt_number,
				  DATE_FORMAT(sp.create_date, '%d-%m-%Y') AS create_date ,
				  sp.is_void,
				  item.title as item_name,
				  (SELECT rms_items.title FROM rms_items WHERE item.items_type LIMIT 1) AS category,
				  (SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS user_name,
				  (SELECT name_kh FROM rms_view  WHERE rms_view.type=6 AND key_code=spd.payment_term LIMIT 1) AS payment_term,
				  (SELECT name_en FROM rms_view WHERE TYPE=10 AND key_code=sp.is_void LIMIT 1) AS void_status                  
			FROM 
    				rms_student_payment AS sp,
    				rms_student_paymentdetail AS spd,
    				rms_student AS s,
    				rms_itemsdetail AS item
    			WHERE 
					item.id=spd.itemdetail_id
    				AND s.stu_id = sp.student_id
    				AND sp.id=spd.payment_id 
    				AND sp.student_id = $studentid ORDER BY create_date DESC,sp.id DESC, item.id ASC ";
			return $db->fetchAll($sql);
	}
	
// 	function getStartDateEndDate($id){
// 		$db = $this->getAdapter();
// 		$sql="select start_date,end_date,note from rms_startdate_enddate where id = $id ";
// 		return $db->fetchRow($sql);
// 	}
	function getStudentPaidExist($student_id,$start_date,$end_date){//សម្រាប់ត្រួតពិនិត្យមើល ថាតើ សិស្សធ្លាប់បង់ប្រាក់ម្តងរឺនៅក្នុងអំឡុង Date នឹង
		$sql="SELECT
			sp.id
			FROM
			rms_student_payment AS sp,
			`rms_student_paymentdetail` spd
			WHERE
			sp.student_id=$student_id
			AND service_id=4
			AND sp.id = spd.payment_id ";
		$start_date = date("Y-m-d",strtotime($start_date));
		$end_date = date("Y-m-d",strtotime($end_date));
		$from_date =(empty($start_date))? '1': "spd.start_date = '".$start_date."'";
		$to_date = (empty($end_date))? '1': "spd.validate = '".$end_date."'";
		$sql.=" AND ".$from_date." AND ".$to_date;
		return $this->getAdapter()->fetchOne($sql);
	}
	
	function updateBalance($data,$payment_id){
		$db = $this->getAdapter();
		$sql="select * from rms_student_payment where id = $payment_id LIMIT 1 ";
		$result = $db->fetchRow($sql);
		if(!empty($result)){
			$array = array(
					"payment_id"	=>$payment_id,
					"stu_id"		=>$data['stu_id'],
					"receipt_no"	=>$this->getRecieptNo($result['branch_id']),
					"total_balance"	=>$data['total_balance'],
					"paid_amount"	=>$data['paid_amount'],
					"balance"		=>$data['balance'],
					"note"			=>$data['note'],
					
					"create_date"	=>date("Y-m-d H:i:s"),
					"user_id"		=>$this->getUserId(),
				);
			$this->_name = "rms_student_clear_balance";
			$this->insert($array);
			
			$arr = array(
					"paid_amount"	=>$result['paid_amount'] + $data['paid_amount'],
					"balance_due"	=>$result['balance_due'] - $data['paid_amount'],
				);
			$where = "id = $payment_id";
			$this->_name = "rms_student_payment";
			$this->update($arr, $where);
		}
	}
	
	function voidStudentClearBalance($id){
		$db = $this->getAdapter();
		$sql="select * from rms_student_clear_balance where id = $id LIMIT 1 ";
		$result = $db->fetchRow($sql);
		if(!empty($result)){
			$array = array(
					"is_void"	=>1,
					"void_by"	=>$this->getUserId(),
				);
			$this->_name = "rms_student_clear_balance";
			$where = " id = $id";
			$this->update($array, $where);
				
			
			$sql1="select * from rms_student_payment where id = ".$result['payment_id']." LIMIT 1 ";
			$row_payment = $db->fetchRow($sql1);
			if(!empty($row_payment)){
				$arr = array(
						"paid_amount"	=>$row_payment['paid_amount'] - $result['paid_amount'],
						"balance_due"	=>$row_payment['balance_due'] + $result['paid_amount'],
				);
				$where = "id = ".$result['payment_id'];
				$this->_name = "rms_student_payment";
				$this->update($arr, $where);
			}
		}
	}
}
