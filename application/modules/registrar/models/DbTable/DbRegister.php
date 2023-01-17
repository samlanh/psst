<?php

class Registrar_Model_DbTable_DbRegister extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    	 
    }
    
    public function getBranchId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
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
				$customer_type=1;
				if($data['student_type']==1){//existing student
					$rs_stu = $gdb->getStudentinfoById($stu_id);
					
					if($rs_stu['is_setstudentid']==0 AND !empty($data['student_code'])){
						$branch_id = $data['branch_id'];
						$stu_no = $gdb->getnewStudentId($branch_id,0);
						
						$arr = array(
							'stu_code'=>$stu_no,
							'is_setstudentid'=>1,
						);
						$this->_name='rms_student';
						$where="stu_id = ".$stu_id;
						$this->update($arr, $where);
					}
					
				}elseif($data['student_type']==2){//testing student
					$rs_stu = $gdb->getStudentTestinfoById($stu_id);
					
					if(!empty($data['auto_test'])){
						$arr = array(
							'is_registered'=>1,
						);
						$this->_name='rms_student_test_result';
						$where="id = ".$rs_stu['id'];
						$this->update($arr, $where);
						
						$dbg = new Application_Model_DbTable_DbGlobal();
						$degreeStudent = empty($rs_stu['degree'])?0:$rs_stu['degree'];
						$stu_code = $dbg->getnewStudentId($data['branch_id'],$degreeStudent);
						
						$settingNewStuID = NEW_STU_ID_FROM_TEST;
						if ($settingNewStuID==1){
							$stu_code=empty($data['student_code'])?$stu_code:$data['student_code'];
						}
						
						$data['degreeStudent'] =$degreeStudent;//For Insert To Tale Count ID
						$dbg->updateAmountStudetByDegree($data);//For Insert To Tale Count ID
						
						$arr = array(
							'customer_type' =>1,
							'stu_code'=>$stu_code,
	// 						'academic_year'=>$data['study_year'],
							'create_date'=>date("Y-m-d H:i:s")
						);
						$this->_name='rms_student';
						$where="stu_id = ".$data['old_stu'];
						$this->update($arr, $where);
						
						// new setup fee_id for student from tested
						if (!empty($data['study_year'])){
							$_dbfee = new Accounting_Model_DbTable_DbFee();
							$feeID = empty($data['study_year'])?0:$data['study_year'];
							$rowfee = $_dbfee->getFeeById($feeID);
							if(empty($rowfee)){
								$academicYear=0;
							}else{
								$academicYear = empty($rowfee['academic_year'])?0:$rowfee['academic_year'];
							}
							
							$_arr= array(
								'branch_id'		=>$data['branch_id'],
								'user_id'		=>$this->getUserId(),
								'student_id'	=>$data['old_stu'],
								'fee_id'		=>$feeID,
								'academic_year'	=>$academicYear,
								'note'			=>'',
								'is_current'	=>1,
								'is_new'		=>1,
								'status'		=>1,
								'create_date'	=>date("Y-m-d H:i:s"),
								'modify_date'	=>date("Y-m-d H:i:s"),
							);
							$this->_name="rms_student_fee_history";
							$this->insert($_arr);//check more.
						}
						if(!empty($data['group_id'])){
							
							$group_id = $data['group_id'];
							$is_setgroup = 1;
							$dbGroup = new Foundation_Model_DbTable_DbGroup();
							$group_info = $dbGroup->getGroupById($group_id);
							if($group_info['degree_id']==$data['degree_id'] AND $group_info['grade']==$data['grade']){
								$array = array(
									'group_id'=>$group_id
								);
								$where =" group_id=0 AND stu_id=".$data['old_stu'];
								$this->_name="rms_group_detail_student";
								$this->update($array, $where);
							}else{
								$_arr = array(
										'stu_id'			=>$data['old_stu'],
										'is_newstudent'		=>1,
										'status'			=>1,
										'group_id'			=>$group_id,
										'degree'			=>$data['degree_id'],
										'grade'				=>$data['grade_id'],
										'is_current'		=>1,
										'is_setgroup'		=>$is_setgroup,
										'is_maingrade'		=>1,
										'date'				=>date("Y-m-d"),
										'create_date'		=>date("Y-m-d H:i:s"),
										'modify_date'		=>date("Y-m-d H:i:s"),
										'user_id'			=>$this->getUserId(),
								);
								$this->_name="rms_group_detail_student";
								$this->insert($_arr);
							}
						}
					}
					
				}elseif($data['student_type']==3){//from crm
					
					$rs_stu = $gdb->getStudentinfoById($stu_id);
					$_dbgb = new Application_Model_DbTable_DbGlobal();
					if(!empty($data['auto_test'])){
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
					
				}elseif($data['student_type']==4){//សិស្សនៅមិនទាន់ទូទាត់ ថ្នាក់សិក្សាចាស់
					//$rs_stu = $gdb->getStudentBalanceInfoById($stu_id);
					$arrStuBalance = array(
						'is_balance' =>0,
						'modify_date'=>date("Y-m-d H:i:s"),
					);
					$this->_name='rms_student_balance';
					$whereStuBalance="id = ".$data['studentBalanceId'];
					$this->update($arrStuBalance, $whereStuBalance);
				}
			
				$data['credit_memo'] = empty($data['credit_memo'])?0:$data['credit_memo'];
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
					'paystudent_type'=> $rs_stu['is_stu_new'],//
					'group_id'		=> $data['group_id'],
					'degree'		=> $rs_stu['degree'],
					'grade'			=> $rs_stu['grade'],
					'degree_culture'=> $rs_stu['calture'],
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
				
				$key = new Application_Model_DbTable_DbKeycode();
				$keydata=$key->getKeyCodeMiniInv(TRUE);
				$condictionSale = empty($keydata['sale_cut_stock'])?0:$keydata['sale_cut_stock'];//0=Transfer Cut Stock Direct,1=Transfer  Cut Stock with Receive
				
				$cut_id="";
				$totalQty=0;
				if ($condictionSale!=1){
					$dbstock = new Stock_Model_DbTable_DbCutStock();
					$itemsCode = $dbstock->getCutStockode($data['branch_id']);
					$_arr=array(
							'branch_id'	   => $data['branch_id'],
							'serailno'	   => $itemsCode,
							'student_id'   => $data['old_stu'],
							'balance'      => 0,
// 							'total_received'=>$data['qty_'.$i],
							'total_qty_due' => 0,
							'received_date' => $paid_date,
							'create_date'   => date("Y-m-d H:i:s"),
							'modify_date'	=> date("Y-m-d H:i:s"),
							'status'        => 1,
							'note'			=>'Direct Stock From Payment',
							'user_id'       => $this->getUserId(),
					);
					$this->_name ='rms_cutstock';
					$cut_id =  $this->insert($_arr);
				}
				
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
							'feeId'			=>$data['academic_year_'.$i],
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
							'totalpayment'	=>$data['total_amount'.$i],
							'paidamount'	=>$data['paid_amount'.$i],
							'is_onepayment'	=>$data['onepayment_'.$i],
							'start_date'	=>$data['date_start_'.$i],
							'validate'		=>$data['validate_'.$i],
							'is_start'		=>1,
							'note'			=>$data['remark'.$i],
							'is_parent'     =>$spd_id,
						);
					$this->_name="rms_student_paymentdetail";
					$studentpaymentid = $this->insert($_arr);
					
					$this->_name='rms_group_detail_student';
					
					$arr = array(
						'feeId'=>$data['academic_year_'.$i],
						'startDate'=>$data['date_start_'.$i],
						'endDate'=>$data['validate_'.$i],
					);
					
					$balance = $data['total_amount'.$i]-$data['paid_amount'.$i];
					if($balance>0){
						$arr['balance']=$balance;
						$arr['isoldBalance']=1;
					}
					elseif($balance==0 AND $data['isoldBalance'.$i]==1){
						$arr['balance']=0;
						$arr['isoldBalance']=0;
					}
					$where = "stu_id=".$data['old_stu']." AND grade=".$data['item_id'.$i];
					$this->update($arr, $where);
					

			////////////////////////////////////////// if product type => insert to sale_detail //////////////////////////////	
					if($rs_item['items_type']==3){ // product
						if($rs_item['is_productseat']==1){ // product set
							$sql="SELECT 
										set.pro_id as product_set_id,
										set.subpro_id as pro_id,
										set.qty as set_qty,
										idt.cost,
										lo.price
									FROM 
										rms_itemsdetail as idt,
										rms_product_setdetail as `set`,
										rms_product_location as lo
									WHERE
										idt.id = set.subpro_id
										and set.subpro_id = lo.pro_id
										and set.pro_id = ".$rs_item['id']."
										and lo.branch_id = ".$data['branch_id'];
							$sql.=" GROUP BY set.subpro_id ORDER BY set.id ASC ";
							$result = $db->fetchAll($sql);
							if(!empty($result)){
								foreach ($result as $row){
									$totalQty = $totalQty+($row['set_qty'] * $data['qty_'.$i]);//count QtyReceive
									$qty_after = $row['set_qty'] * $data['qty_'.$i];
									if ($condictionSale!=1){
										$qty_after=0;
									}
									$arr_sale = array(
											'payment_id'		=>$paymentid,
											'is_product_set'	=>1,
											'product_set_id'	=>$row['product_set_id'],
											'pro_id'			=>$row['pro_id'],
											'qty'				=>$row['set_qty'] * $data['qty_'.$i], // (qty of set detail) * (qty buy)
											'qty_after'			=>$qty_after,
											'cost'				=>$row['cost'],
											'price'				=>$row['price'],
											'user_id'			=>$this->getUserId(),
										);
									$this->_name="rms_saledetail";
									$sale_detailid = $this->insert($arr_sale);
									
									if ($condictionSale!=1){
										$arrs = array(
												'cutstock_id'=>$cut_id,
												'student_paymentdetail_id'=>$sale_detailid,
												'product_id'=>$row['pro_id'],
												'due_amount'=>0,
												'qty_receive'=>$row['set_qty'] * $data['qty_'.$i],
												'remain'=>0,
												'remide_date'=>'',
										);
										$this->_name ='rms_cutstock_detail';
										$this->insert($arrs);
										$dbpu = new Stock_Model_DbTable_DbPurchase();
										$dbpu->updateStock($row['pro_id'],$data['branch_id'],-($row['set_qty'] * $data['qty_'.$i]));
									}
								}
							}
						}else{ // product normal
							$totalQty = $totalQty+$data['qty_'.$i];//count QtyReceive
							$qty_after = $data['qty_'.$i];
							if ($condictionSale!=1){
								$qty_after=0;
							}
							$arr_sale = array(
									'payment_id'		=>$paymentid,
									'is_product_set'	=>0,
									'product_set_id'	=>$data['item_id'.$i],
									'pro_id'			=>$data['item_id'.$i],
									'qty'				=>$data['qty_'.$i],
									'qty_after'			=>$qty_after,
									'cost'				=>$rs_item['cost'],
									'price'				=>$data['price_'.$i],
									'user_id'			=>$this->getUserId(),
								);
							$this->_name="rms_saledetail";
							$sale_detailid= $this->insert($arr_sale);
							
							if ($condictionSale!=1){
								$arrs = array(
										'cutstock_id'=>$cut_id,
										'student_paymentdetail_id'=>$sale_detailid,
										'product_id'=>$data['item_id'.$i],
										'due_amount'=>0,
										'qty_receive'=>$data['qty_'.$i],
										'remain'=>0,
										'remide_date'=>'',
								);
								$this->_name ='rms_cutstock_detail';
								$this->insert($arrs);
								$dbpu = new Stock_Model_DbTable_DbPurchase();
								$dbpu->updateStock($data['item_id'.$i],$data['branch_id'],-$data['qty_'.$i]);
							}
						}
					}
				}
				
				if ($condictionSale!=1){
					$dbstock = new Stock_Model_DbTable_DbCutStock();
					$itemsCode = $dbstock->getCutStockode($data['branch_id']);
					$_arr=array(
						'total_received'=>$totalQty,
					);
					$this->_name ='rms_cutstock';
					$where = "id = ".$cut_id;
					$this->update($_arr, $where);
				}
				$db->commit();
// 				return $receipt_number;
				$rs_stu = $gdb->getStudentinfoById($stu_id);
				$rs_stu['receipt_number'] = $receipt_number;
				return $rs_stu;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	
	
	function getParentIDToUpdateBack($payment_id){
		$db=$this->getAdapter();
		$sql="select is_parent from rms_student_paymentdetail where payment_id = $payment_id";
		return $db->fetchAll($sql);	
	}
		
	
	function updatePaymentInfoBack($payment_id,$type){
		$db = $this->getAdapter();
		$sql="select * from rms_student_paymentdetail where payment_id = $payment_id ";
		$id_old_record = $db->fetchAll($sql);
		if(!empty($id_old_record)){
			foreach ($id_old_record as $result){
				if($result['is_parent']>0 && $result['service_type'] != 4){
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
				$stu_id = empty($rsold['stu_id'])?0:$rsold['stu_id'];
				$lastPaymentRecord = $this->getLastStudentPaymentRecord($stu_id);
				$lastPayId = empty($lastPaymentRecord['id'])?0:$lastPaymentRecord['id'];
				$voidOldreceipt = 0;
				if ($lastPayId!=$payment_id){//void old receipt 
					$voidOldreceipt=1;
				}
				
				if($rsold['data_from']!=1 AND $voidOldreceipt==0){ // not student study payments
					
					if($rsold['data_from']==2){
						
						$arr = array(
							'is_registered'=>0,
						);
						$this->_name='rms_student_test_result';
						$where="stu_test_id = ".$data['old_stu']." AND degree_result=".$rsold['degree_id']." AND grade_result=".$rsold['grade'];
						$this->update($arr, $where);//reverse to tested student
						
						$arr = array(
							'customer_type'=>4 //reverse to tested student
						);
						
					}elseif($rsold['data_from']==3){
						
						$arr = array(
							'customer_type' =>3, //reverse to crm
							'is_studenttest' =>0,
						);
						
					}
					
					$this->_name='rms_student';
					$where='stu_id = '.$data['old_stu'];
					$this->update($arr, $where);
				}
				// update payment and validate of service and tuition fee info back ,  and update stock back to origin
				if($rsold['is_void']==0 AND $voidOldreceipt==0){
						$this->updatePaymentInfoBack($payment_id,1);   // 1 is pay for both service and tuition fee
				}
				
				$this->_name='rms_student_payment';
				$arra=array(
					'is_void'=>$data['void'],
					'void_by'=>$this->getUserId(),
					'void_note'=>$data['void_note'],
				);
				$where = " id = ".$payment_id;
				$this->update($arra, $where);
			
				if(!empty($data['credit_memo_id']) AND $rsold['is_void']==0){//check again because it old code
					$this->updateCreditMemoBack($data);
				}				
				
				$key = new Application_Model_DbTable_DbKeycode();
				$keydata=$key->getKeyCodeMiniInv(TRUE);
				$condictionSale = empty($keydata['sale_cut_stock'])?0:$keydata['sale_cut_stock'];//0=Transfer Cut Stock Direct,1=Transfer  Cut Stock with Receive
				if ($condictionSale!=1){
					$sql="SELECT sd.* FROM `rms_saledetail` AS sd WHERE sd.payment_id =$payment_id";
					$saleDetail = $db->fetchAll($sql);
					if (!empty($saleDetail)) foreach ($saleDetail as $rs){
						//Qurey Cut Stock Detail
						$sql = "SELECT cd.* FROM `rms_cutstock_detail` AS cd WHERE cd.`student_paymentdetail_id` =".$rs['id'];
						$cutDetail = $db->fetchAll($sql);
						$qtyReceive = 0;
						if (!empty($cutDetail)) foreach ($cutDetail as $cut){
							$qtyReceive = $qtyReceive+$cut['qty_receive'];
							//Void All This Payment Cut Stock
							$_arr=array(
				    			'status'	      => 0,
				    			'user_id'  =>$this->getUserId(),
				    			'modify_date'	  => date("Y-m-d H:i:s"),
					    	);
					    	$this->_name ='rms_cutstock';
					    	$where = ' id = '.$cut['cutstock_id'];
					    	$this->update($_arr, $where);
						}
						//Update Sale Detial back
						$_arr=array(
								'qty_after'	      => ($rs['qty_after']+$qtyReceive),
						);
						$this->_name ='rms_saledetail';
						$where = ' id = '.$rs['id'];
						$this->update($_arr, $where);
						
						$dbpu = new Stock_Model_DbTable_DbPurchase();
						$dbpu->updateStock($rs['pro_id'],$data['branch_id'],+$qtyReceive);
					}
				}
				
// 				$where = " payment_id = $payment_id";
// 				$this->_name='rms_saledetail';
// 				$this->delete($where);

				$db->commit();
				return 0;
			}catch (Exception $e){
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				$db->rollBack();
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
    	
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$branch = "branch_namekh";
    		$grade = "rms_itemsdetail.title";
    		$degree = "rms_items.title";
    	}else{ // English
    		$label = "name_en";
    		$branch = "branch_nameen";
    		$grade = "rms_itemsdetail.title_en";
    		$degree = "rms_items.title_en";
    	}
		
    	$sql=" SELECT 
    				sp.id,
    				(SELECT $branch FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
    				sp.receipt_number,
	    			(CASE WHEN sp.data_from=3 THEN s.serial ELSE s.stu_code END) AS stu_code,
	    			(CASE WHEN s.stu_khname IS NULL OR s.stu_khname='' THEN s.stu_enname ELSE s.stu_khname END) AS stu_khname,
	    			(SELECT $label FROM `rms_view` WHERE type=2 AND key_code = s.sex LIMIT 1) AS sex,
	    			(SELECT CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=sp.academic_year) AS YEAR,
	 		        sp.penalty,sp.grand_total,sp.credit_memo,sp.paid_amount,sp.balance_due,
					(SELECT $label FROM `rms_view` WHERE type=8 AND key_code=payment_method LIMIT 1) AS payment_method,
					number,sp.create_date,
	 		       (SELECT first_name FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS user,
	 		       (SELECT $label FROM rms_view WHERE TYPE=10 AND key_code = sp.is_void LIMIT 1) AS void,
	 		       (SELECT first_name FROM rms_users WHERE rms_users.id = sp.void_by LIMIT 1) AS void_by
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
	    		$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
	    		
	    		$s_where[]= " REPLACE(stu_code,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(serial,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(receipt_number,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(stu_khname,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(stu_enname,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(last_name,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]=	" REPLACE(CONCAT(last_name,stu_enname),' ','') LIKE '%{$s_search}%'";
	    		$where.=' AND ('.implode(' OR ', $s_where).')';
	    	}
	    	if(($search['branch_id']>0)){
	    		$where.= " AND sp.branch_id = ".$search['branch_id'];
	    	}
	    	
	    	if(!empty($search['study_year'])){
	    		$where.=" AND sp.academic_year=".$search['study_year'];
	    	}
// 	    	if(!empty($search['session'])){
// 	    		$where.=" AND sp.session=".$search['session'];
// 	    	}
// 	    	if(!empty($search['degree'])){
// 	    		$where.=" AND sp.degree=".$search['degree'];
// 	    	}
// 	    	if(!empty($search['grade_all'])){
// 	    		$where.=" AND sp.grade=".$search['grade_all'];
// 	    	}
	    	if(!empty($search['user'])){
	    		$where.=" AND sp.user_id=".$search['user'];
	    	}
	    	$order=" ORDER BY sp.id DESC";
    	return $db->fetchAll($sql.$where.$order);
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
//     	$sql = "SELECT id,CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') AS years,(select name_en from rms_view where type=7 and key_code=time) as time FROM rms_tuitionfee
//     	        WHERE `status`=1 GROUP BY from_academic,to_academic,generation,time ";
//     	$order=' ORDER BY id DESC';
//     	return $db->fetchAll($sql.$order);
//     }
    
    function getAllYears($type=1){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	return $_db->getAllYear();
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
    	
    	$new_acc_no= (int)$payment_no + (int)$income_no +  1;
    	//$new_acc_no = $new_acc_no-506;//for psis
    	
    	$acc_length = strlen((int)$new_acc_no+1);
    	$pre=0;
    	for($i = $acc_length;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    
    //select Gep old student by name
    function getGepOldStudent($stu_id){
    	$db=$this->getAdapter();
    	$sql="SELECT stu_id,stu_enname,stu_khname,sex,`session` As ses,degree,grade FROM rms_student 
    	       WHERE  stu_id=$stu_id LIMIT 1";
    	return $db->fetchRow($sql);
    }
    //select all Gerneral old student
    function getAllGerneralOldStudent(){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	return $_db->getAllStuCode();

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
		
		$param = array(
			'itemsType'=>2
		);
    	return $db->getAllItemDetail($param);
    }  
    
    function getAllDegree(){
    	$db=$this->getAdapter();
    	$_dbg = new Application_Model_DbTable_DbGlobal();
    	return $_dbg->getAllItems(1,null);
    }

    
    function getAllDegreeBac($type){
    	$db=new Application_Model_DbTable_DbGlobal();
    	return $db->getAllItems($type,null);
    }
   
   
    
    
    function getAllUser(){
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql="SELECT id,CONCAT(last_name,' - ',first_name) as name FROM rms_users WHERE active=1 $branch_id order by id desc ";
    	return $db->fetchAll($sql);
    }
    
    public function getNewStudent($newid,$stu_type){
    	$db = $this->getAdapter();
    	$sql="  SELECT COUNT(stu_id)  FROM rms_student WHERE 1 ";
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
   
    
    public function getAllService(){
    	$db = $this->getAdapter();
    }
    
    function getAllProductName(){
    	$db = $this->getAdapter();
    	$sql="SELECT id,pro_name FROM `rms_product` WHERE STATUS=1 AND pro_name!='' ORDER BY pro_name";
    	return $db->fetchAll($sql);
    } 
    
    
    public function getAllTermbyItemdetail($branch_id,$year,$item_id){
    		$db=$this->getAdapter();
    		$sql="SELECT items_type FROM `rms_itemsdetail` WHERE id=$item_id LIMIT 1";
    		$rs_pro=$db->fetchRow($sql);
    		$item_type = $rs_pro['items_type'];
    		
    		$session_lang=new Zend_Session_Namespace('lang');
    		$lang = $session_lang->lang_id;
    		
    		if($lang==1){// khmer
    			$label = "name_kh";
    		}else{ // English
    			$label = "name_en";
    		}
    		
    		$sql="SELECT
    				distinct(tfd.`payment_term`) AS id,
    				(SELECT $label FROM rms_view WHERE `type`=6 AND key_code =tfd.`payment_term` AND `status`=1 LIMIT 1) as name
    			FROM
    				rms_tuitionfee AS tf,
    				rms_tuitionfee_detail AS tfd
    			WHERE
    				tf.id = tfd.fee_id
    				AND tf.status=1
    				AND tfd.tuition_fee>0
    				AND tfd.class_id = $item_id
    				AND tf.branch_id = $branch_id ";
    		if($item_type==1 AND !empty($year)){//school fee
    			$sql.=" AND tf.type =1 AND tf.id = $year ";
    		}
    		$rows = $db->fetchAll($sql);
    		$options = '';
    		if(!empty($rows))foreach($rows as $value){
    			$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name']).'</option>';
    		}
    		
    		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    		if($item_type==3){
    			$options .= '<option value="1" >'.$tr->translate('MONTHLY').'</option>';
    		}
    		$options .= '<option value="5" >'.$tr->translate('OTHER').'</option>';
    		return $options;
    		
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
	    		
				sp.degree as degree_id,
	    		(SELECT rms_items.title FROM rms_items WHERE rms_items.id=sp.degree AND rms_items.type=1 LIMIT 1) AS degree,
	    		(SELECT sgh.group_id FROM `rms_group_detail_student` AS sgh WHERE sgh.itemType=1 AND sgh.stu_id = sp.`student_id` ORDER BY sgh.gd_id DESC LIMIT 1) as group_id,
	    		(SELECT first_name from rms_users as u where u.id=sp.user_id  LIMIT 1) as first_name,
	    		(SELECT last_name from rms_users as u where u.id=sp.user_id  LIMIT 1) as last_name
    		FROM
    		  	rms_student_payment as sp,
    		  	rms_student as s
    		WHERE 
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
    			WHERE
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
	
	
    function getPaymentEdit($payment_id){
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
		$_db  = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if($lang==1){// khmer
			$label = "name_kh";
			$branch = "branch_namekh";
			$grade = "item.title";
			$degree = "rms_items.title";
		}else{ // English
			$label = "name_en";
			$branch = "branch_nameen";
			$grade = "item.title_en";
			$degree = "rms_items.title_en";
		}
		$sql="SELECT 
    			  spd.id,
    			  spd.payment_id,
				  spd.fee,
				  spd.qty,
				  spd.subtotal,
				  spd.extra_fee,
				  spd.discount_percent,
				  spd.discount_amount,
				  spd.paidamount,	
				  sp.balance_due as balance,	
				  (SELECT dis_name FROM `rms_discount` WHERE disco_id=spd.discount_type LIMIT 1) AS discount_type,
				  spd.paidamount,
				  spd.note,
				  spd.is_onepayment,
				  DATE_FORMAT(spd.start_date, '%d-%m-%Y') AS start_date ,
				  DATE_FORMAT(spd.validate, '%d-%m-%Y') AS validate ,
				  sp.receipt_number,
				  DATE_FORMAT(sp.create_date, '%d-%m-%Y') AS create_date ,
				  sp.is_void,
				  $grade AS item_name,
				  (SELECT $degree FROM rms_items WHERE item.items_type LIMIT 1) AS category,
				  (SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS user_name,
				  (SELECT $label FROM rms_view  WHERE rms_view.type=6 AND key_code=spd.payment_term LIMIT 1) AS payment_term,
				  (SELECT $label FROM rms_view WHERE TYPE=10 AND key_code=sp.is_void LIMIT 1) AS void_status                  
			FROM 
    				rms_student_payment AS sp,
    				rms_student_paymentdetail AS spd,
    				rms_student AS s,
    				rms_itemsdetail AS item
    			WHERE 
					item.id=spd.itemdetail_id
    				AND s.stu_id = sp.student_id
    				AND sp.id=spd.payment_id 
    				AND sp.student_id = $studentid ORDER BY sp.create_date DESC ";
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
	
	/*function updateBalance($data,$payment_id){
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
	}*/
	
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
	
	function getLastStudentPaymentRecord($stuId){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    			sp.*,
	    		s.stu_enname,
	    		s.stu_khname,
	    		s.sex,
	    		s.stu_code,
	    		s.stu_id,
	    		
				sp.degree as degree_id,
	    		(SELECT rms_items.title FROM rms_items WHERE rms_items.id=sp.degree AND rms_items.type=1 LIMIT 1) AS degree,
	    		(SELECT sgh.group_id FROM `rms_group_detail_student` AS sgh WHERE sgh.itemType=1 AND sgh.stu_id = sp.`student_id` ORDER BY sgh.gd_id DESC LIMIT 1) as group_id,
	    		(SELECT first_name from rms_users as u where u.id=sp.user_id  LIMIT 1) as first_name,
	    		(SELECT last_name from rms_users as u where u.id=sp.user_id  LIMIT 1) as last_name
    		FROM
    		  	rms_student_payment as sp,
    		  	rms_student as s
    		WHERE 
    			s.stu_id = sp.student_id
    			AND sp.student_id=$stuId AND sp.is_closed=0 AND sp.is_void=0 ";
		$sql.=" ORDER BY sp.id DESC ";
		$sql.=" LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
}
