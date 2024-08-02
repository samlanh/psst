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
    		AND spd.itemdetail_id=$service_id 
    		AND sp.student_id=$studentid 
    		ORDER BY spd.validate DESC LIMIT 1 ";
    	return $db->fetchOne($sql);
    }
    function getStuidExist($stu_code){
    	$db=$this->getAdapter();
    	$sql="SELECT stu_id,stu_code FROM rms_student WHERE stu_code=$stu_code LIMIT 1";
    	return $db->fetchRow($sql);
    }

    function setStudentId($data){
    	$gdb = new  Application_Model_DbTable_DbGlobal();
    	$rs_stu = $gdb->getStudentinfoGlobalById($data['old_stu']);
    	if($rs_stu['is_setstudentid']==0 AND !empty($data['student_code'])){
    		$branch_id = $data['branch_id'];
    		$stu_no = $gdb->getnewStudentId($branch_id,0);
    	
    		$arr = array(
    				'stu_code'=>$stu_no,
    				'is_setstudentid'=>1,
    		);
    		$this->_name='rms_student';
    		$where="stu_id = ".$data['old_stu'];
    		$this->update($arr, $where);
    	}
    }
    function addStudentFromToTesting($data){//from tested student
    	$dbg = new  Application_Model_DbTable_DbGlobal();
    	$rs_stu = $dbg->getStudentTestinfoById($data['old_stu']);
    	if(!empty($data['auto_test'])){
    		$arr = array(
    				'is_registered'=>1,
    		);
    		$this->_name='rms_student_test_result';
    		$where="id = ".$rs_stu['id'];
    		$this->update($arr, $where);
    	
    		$degreeStudent = empty($rs_stu['degree'])?0:$rs_stu['degree'];
    		$stu_code = $dbg->getnewStudentId($data['branch_id'],$degreeStudent);
    	
    		$settingNewStuID = NEW_STU_ID_FROM_TEST;
    		if ($settingNewStuID==1){
    			$stu_code=empty($data['student_code'])?$stu_code:$data['student_code'];
    		}
    	
    		$data['degreeStudent']=$degreeStudent;//For Insert To Tale Count ID
    		$dbg->updateAmountStudetByDegree($data);//For Insert To Tale Count ID
    	
    		$arr = array(
    				'customer_type' =>1,
    				'stu_code'=>$stu_code,
    				'modify_date'=>date("Y-m-d H:i:s")
    		);
    		$this->_name='rms_student';
    		$where="stu_id = ".$data['old_stu'];
    		$this->update($arr, $where);
    	
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
    			}else{//ករណីរើសបង់ថ្លៃកម្មវិធីផ្សេងៗវានឹងបន្ថែម ជួរក្រោម១ទៀត
    				$_arr = array(
    						'stu_id'			=>$data['old_stu'],
    						'itemType'			=>1,
    						'is_newstudent'		=>1,
    						'status'			=>1,
    						'group_id'			=>$group_id,
    						'degree'			=>$data['degree_id'],
    						'grade'				=>$data['grade_id'],
    						'is_current'		=>1,
    						'is_setgroup'		=>$is_setgroup,
    						'is_maingrade'		=>1,
    						'create_date'		=>date("Y-m-d H:i:s"),
    						'modify_date'		=>date("Y-m-d H:i:s"),
    						'user_id'			=>$this->getUserId(),
    						'note'				=>'data from payment then choose group when from tested student'
    				);
    				$this->_name="rms_group_detail_student";
    				$this->insert($_arr);
    			}
    		}
    	}
    }
    function cutStockDetail($data,$i){
    	$db = $this->getAdapter();
    	$condictionSale = $data['conditionCutStock'];
    	$totalQty=0;
    	$totalCosting = 0;
    	if($data['is_productseat']==1){ // product set
    		$sql="SELECT
	    		set.pro_id as product_set_id,
	    		set.subpro_id as pro_id,
	    		set.qty as set_qty,
	    		idt.cost,
	    		lo.price,
	    		lo.price_set,
	    		lo.costing
    		FROM
	    		rms_itemsdetail as idt,
	    		rms_product_setdetail as `set`,
	    		rms_product_location as lo
    		WHERE
	    		idt.id = set.subpro_id
	    		and set.subpro_id = lo.pro_id
	    		and set.pro_id = ".$data['item_id'.$i]."
	    		and lo.branch_id = ".$data['branch_id'];
    		$sql.=" GROUP BY set.subpro_id ORDER BY set.id ASC ";
    		$result = $db->fetchAll($sql);
    		if(!empty($result)){
    			foreach ($result as $row){
    				$qty_after = $row['set_qty'] * $data['qty_'.$i];
    				$totalCosting = $totalCosting+($qty_after*$row['costing']);//for for product
    					
    				if ($condictionSale!=1){
    					$qty_after=0;
    				}
    				$arr_sale = array(
    						'payment_id'		=>$data['paymentId'],
    						'is_product_set'	=>1,
    						'product_set_id'	=>$row['product_set_id'],
    						'pro_id'			=>$row['pro_id'],
    						'qty'				=>$row['set_qty'] * $data['qty_'.$i], // (qty of set detail) * (qty buy)
    						'qty_after'			=>$qty_after,
    						'cost'				=>$row['costing'],
    						'price'				=>$row['price_set'],
    						'user_id'			=>$this->getUserId(),
    				);
    				$this->_name="rms_saledetail";
    				$sale_detailid = $this->insert($arr_sale);
    					
    				if ($condictionSale!=1){
    					$arrs = array(
    							'cutstock_id'=>$data['cutStockId'],
    							'student_paymentdetail_id'=>$sale_detailid,
    							'product_id'=>$row['pro_id'],
    							'due_amount'=>$data['qty_'.$i],
    							'qty_receive'=>$row['set_qty'] * $data['qty_'.$i],
    							'remain'=>0,
    							'remide_date'=>'',
    							'paymentId'=>$data['paymentId'],
    					);
    					$this->_name ='rms_cutstock_detail';
    					$this->insert($arrs);
    					$dbpu = new Stock_Model_DbTable_DbPurchase();
    					$dbpu->updateStock($row['pro_id'],$data['branch_id'],-($row['set_qty'] * $data['qty_'.$i]));
    				}
    			}
    			$this->_name="rms_student_paymentdetail";
    			$arr = array(
    					'productCost'=>$totalCosting
    			);
    			$where ='id='.$data['paymentDetailId'];
    			$this->update($arr, $where);
    		}
    	}else{ // product normal
    			
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		$arr = array(
    				'branch_id'=>$data['branch_id'],
    				'productId'=>$data['item_id'.$i]
    		);
    		$resultItem = $dbs->getProductInfoByLocation($arr);
    		$currentCosting = empty($resultItem['currentPrice'])?0:$resultItem['currentPrice'];
    		
    		$arr = array(
    				'productCost'=>$currentCosting
    		);
    		$where ='id='.$data['paymentDetailId'];
    		$this->_name="rms_student_paymentdetail";
    		$this->update($arr, $where);
    			
    		$qty_after = $data['qty_'.$i];
    		if ($condictionSale!=1){
    			$qty_after=0;
    		}
    		$arr_sale = array(
    				'payment_id'		=>$data['paymentId'],
    				'is_product_set'	=>0,
    				'product_set_id'	=>$data['item_id'.$i],
    				'pro_id'			=>$data['item_id'.$i],
    				'qty'				=>$data['qty_'.$i],
    				'qty_after'			=>$qty_after,
    				'cost'				=>$currentCosting,
    				'price'				=>$data['price_'.$i],
    				'user_id'			=>$this->getUserId(),
    		);
    		$this->_name="rms_saledetail";
    		$sale_detailid= $this->insert($arr_sale);
    			
    		if ($condictionSale!=1){
    			$arrs = array(
    					'cutstock_id'=>$data['cutStockId'],
    					'student_paymentdetail_id'=>$sale_detailid,
    					'product_id'=>$data['item_id'.$i],
    					'due_amount'=>$data['qty_'.$i],
    					'qty_receive'=>$data['qty_'.$i],
    					'remain'=>0,
    					'remide_date'=>'',
						'paymentId'=>$data['paymentId'],
    			);
    			$this->_name ='rms_cutstock_detail';
    			$this->insert($arrs);
    			$dbpu = new Stock_Model_DbTable_DbPurchase();
    			$dbpu->updateStock($data['item_id'.$i],$data['branch_id'],-$data['qty_'.$i]);
    		}
    	}
    }
	function addRegister($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		
		$paid_date = date("Y-m-d ",strtotime($data['paid_date'])).date("H:i:s");

		$gdb = new  Application_Model_DbTable_DbGlobal();
		$stu_id = $data['old_stu'];//$this->getnewStudentId($data['dept']);
		
		$rs_stu = $gdb->getStudentinfoGlobalById($stu_id);
		$receipt_number = $this->getRecieptNo($data['branch_id']);
			try{
				
				$customer_type=1;
				if($data['student_type']==1){//existing student
					$this->setStudentId($data);
				}elseif($data['student_type']==2){//testing student(from tested student)
					$this->addStudentFromToTesting($data);
				}elseif($data['student_type']==3){//from crm
					
					$_dbgb = new Application_Model_DbTable_DbGlobal();
					if(!empty($data['auto_test'])){
						$newSerial = $_dbgb->getTestStudentId($data['branch_id']);
						$arr = array(
							'customer_type' =>4,
							'is_studenttest' =>1,
							'serial' => $newSerial,
							'create_date'=>date("Y-m-d H:i:s"),
							'create_date_stu_test'=>date("Y-m-d H:i:s"),
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
				
				$this->_name='rms_student_payment';
				
				$arr = array(
						'is_current'=>0//update old receipt
				);
				$where="student_id=".$data['old_stu'];
				$this->update($arr,$where);
				
				
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
					'bank_id'		=> $data['bank_name'],
					'number'	    => $data['number'],
					'note'			=> $data['note'],
					'create_date'	=> $paid_date,
					'user_id'		=> $this->getUserId(),
					'academic_year'	=> $data['study_year'],
					'paystudent_type'=>$rs_stu['is_stu_new'],//
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
				
				// $key = new Application_Model_DbTable_DbKeycode();
				// $keydata=$key->getKeyCodeMiniInv(TRUE);
				$condictionSale = Setting_Model_DbTable_DbGeneral::geValueByKeyName('sale_cut_stock');
// 				$condictionSale = empty($stockSetting)?0:$stockSetting;//0=Transfer Cut Stock Direct,1=Transfer  Cut Stock with Receive
				
				$cut_id="";
				$totalQty=0;
				if ($condictionSale!=1){//cut ready 
					$dbstock = new Stock_Model_DbTable_DbCutStock();
					$itemsCode = $dbstock->getCutStockode($data['branch_id']);
					$_arr=array(
							'branch_id'	   => $data['branch_id'],
							'paymentId'	   => $paymentid,
							'serailno'	   => $itemsCode,
							'student_id'   => $data['old_stu'],
							'balance'      => 0,
							'total_qty_due' => 0,
							'received_date' => $paid_date,
							'create_date'   => date("Y-m-d H:i:s"),
							'modify_date'	=> date("Y-m-d H:i:s"),
							'status'        => 1,
							'note'			=>'Cut From Payment',
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
					if(!empty($rs_item)){

						$data['date_start_'.$i]=($data['date_start_'.$i])=='01/01/1970'?'':$data['date_start_'.$i];
						$data['validate_'.$i]=($data['validate_'.$i])=='01/01/1970'?'':$data['validate_'.$i];
						
						$_arr = array(
								'payment_id'	=>$paymentid,
								'feeId'			=>$data['academic_year_'.$i],
								'service_type'	=>$rs_item['items_type'],
								'itemdetail_id'	=>$data['item_id'.$i],
								'payment_term'	=>empty($data['term_'.$i]) ? 0 : $data['term_'.$i],// underfined when submit product
								'fee'			=>$data['price_'.$i],
								'qty'			=>$data['qty_'.$i],
								'qty_balance'	=>$data['qty_'.$i],
								'subtotal'		=>$data['subtotal_'.$i],
								'extra_fee'		=>$data['extra_fee'.$i],
								'discount_type'	=>empty($data['discount_type'.$i]) ? 0 : $data['discount_type'.$i],// underfined when submit product
								'discount_percent'=>$data['discount_'.$i],
								'discount_amount'=>$data['discount_amount'.$i],
								'totalpayment'	=>$data['total_amount'.$i],
								'paidamount'	=>$data['paid_amount'.$i],
								'is_onepayment'	=>($data['term_'.$i]==5)?1:0,//5 = one payment
								'start_date'	=>($data['term_'.$i]==5)?'':$data['date_start_'.$i],
								'validate'		=>($data['term_'.$i]==5)?'':$data['validate_'.$i],
								'is_start'		=>1,
								'note'			=>$data['remark'.$i],
								'is_parent'     =>$spd_id,
								'academicFeeTermId'=>$data["term_study".$i],
							);
						$this->_name="rms_student_paymentdetail";
						$paymentDetailId = $this->insert($_arr);
					}
					
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
						$balance = 0;
						$arr['balance']=$balance;
						$arr['isoldBalance']=0;
					}
					$this->_name='rms_group_detail_student';
					$where = "stu_id=".$data['old_stu']." AND grade=".$data['item_id'.$i];
					$this->update($arr, $where);
					
					if(!empty($rs_item) AND !empty($data['autoNextPay'.$i])){
							$_arr= array(
								'branch_id'		=> $data['branch_id'],
								'studentId'		=> $data['old_stu'],
								'itemType'		=> $rs_item['items_type'],
								'grade'			=> $data['item_id'.$i],
								'degree'		=> $rs_item['items_id'],
								'feeId'			=> $data['academic_year_'.$i],
								'academic_year'	=> '',
								'startDate'		=> $data['date_start_'.$i],
								'endDate'		=> $data['validate_'.$i],
								'discountType'	=>'',
								'discountAmount'=>'',
								'balance'		=> $balance,
								'schoolOption'	=> $rs_item['schoolOption'],
								'isMaingrade'	=> 1,
								'isCurrent'		=> 1,
								'stopType'		=> 0,
								'status'		=> 1,
								'isNewStudent'	=> 1,
								'remark'		=> $data['remark'.$i],
								'create_date'	=> date("Y-m-d H:i:s"),
								'user_id'		=> $this->getUserId(),
								'entryFrom'	=>4,
							);
						$gdb->AddItemToGroupDetailStudent($_arr);//to insert rms_group_detail_student Item
					}
			if($rs_item['items_type']==3){ // product
						$data['is_productseat'] = $rs_item['is_productseat'];
						$data['paymentId'] = $paymentid;
						$data['paymentDetailId'] = $paymentDetailId;
						$data['cutStockId'] = $cut_id;
						$data['costing'] = $rs_item['cost'];
						$data['conditionCutStock'] = $condictionSale;
						$this->cutStockDetail($data,$i);
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

				$stuResult = $gdb->getStudentinfoGlobalById($stu_id);
				$stuResult['receipt_number'] = $receipt_number;
				return $stuResult;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
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
	 		        sp.grand_total,sp.credit_memo,sp.paid_amount,sp.balance_due,
					(SELECT $label FROM `rms_view` WHERE type=8 AND key_code=payment_method LIMIT 1) AS payment_method,
					number,sp.create_date,
	 		       (SELECT first_name FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS user,
	 		       (SELECT $label FROM rms_view WHERE TYPE=10 AND key_code = sp.is_void LIMIT 1) AS void,
					sp.is_void,
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
	    	if(!empty($search['userId'])){
	    		$where.=" AND sp.user_id=".$search['userId'];
	    	}
	    	$order=" ORDER BY sp.id DESC";
    		return $db->fetchAll($sql.$where.$order);
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
		$recieptStart = 0;//psis=-506;
    	$new_acc_no = $new_acc_no+$recieptStart;//for psis
    	
    	$acc_length = strlen((int)$new_acc_no+1);
    	$pre=0;
    	for($i = $acc_length;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
	
	function getStudentPaymentHistory($data){
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
    				AND sp.id=spd.payment_id ";
		if(!empty($data['studentId'])){
			$sql.=" AND sp.student_id =".$data['studentId'];
		}
		$sql.=" ORDER BY sp.create_date DESC ";
		$results =  $db->fetchAll($sql);
		
		if(!empty($data['returnHtml'])){
			$str = '';
			if(!empty($results)){
				$tr = Application_Form_FrmLanguages::getCurrentlanguage();
				$str.='<table class="collape tablesorter" style="border-collapse: collapse; border:1px solid #ccc;white-space: nowrap;">'
						.'<thead><tr  class="head-td" style="background:#ececec;font-size:10px;color:#000"><th class="tdheader">'.$tr->translate("N_O").'</th>'
						.'<th class="tdheader">'.$tr->translate('SERVICE_CATE').'</th>'
						.'<th class="tdheader">'.$tr->translate('SERVICES').'</th>'
						.'<th class="tdheader">'.$tr->translate('PAID_DATE').'</th>'
						.'<th class="tdheader">'.$tr->translate('RECEIPT_NO').'</th>'
						.'<th class="tdheader">'.$tr->translate('PAYMENT_TERM').'</th>'
						.'<th class="tdheader">'.$tr->translate('QTY').'x'.$tr->translate('PRICE').'</th>'
						.'<th class="tdheader">'.$tr->translate('SUBTOTAL').'</th>'
						.'<th class="tdheader">'.$tr->translate("EXTRA_FEE").'/'.$tr->translate("DEDUCT").'</th>'
						.'<th class="tdheader">'.$tr->translate('DISCOUNT_TYPE').'</th>'
						.'<th class="tdheader">'.$tr->translate('DISCOUNT').'</th>'
						.'<th class="tdheader">'.$tr->translate('PAID').'</th>'
						.'<th class="tdheader">'.$tr->translate('BALANCE').'</th>'
						.'<th class="tdheader">'.$tr->translate('VALID_DATE').'</th>'
						.'<th class="tdheader">'.$tr->translate('NOTE').'</th>'
						.'<th class="tdheader">'.$tr->translate('STATUS').'</th>'
						.'<th class="tdheader">'.$tr->translate('USER').'</th>'
						.'<th class="tdheader">'.$tr->translate('DETAIL').'</th>'
				.'</thead>';
				$url = Zend_Controller_Front::getInstance()->getBaseUrl();
				foreach($results as $key=> $result){
					$str.= '<tr style="background-color:none;font-size:11px;text-align:center;" class="hover">';
						$str.= '<td>'.($key+1).'</td>';
						$str.= '<td style="text-align:left;">'.$result['category'].'</td>';
						$str.= '<td style="text-align:left;"><label class="notedDescription">'.$result['item_name'].'</label></td>';
						$str.= '<td>'.$result['create_date'].'</td>';
						$str.= '<td>'.$result['receipt_number'].'</td>';
						$payment_term='';
						if($result['payment_term']!=null){
							$payment_term=$result['payment_term'];
						}
						$str.= '<td style="text-align:left;">'.$payment_term.'</td>';
						$str.= '<td>'.$result['qty'].'x'.$result['fee'].'</td>';
						$str.= '<td>'.$result['subtotal'].'</td>';
						$str.= '<td>'.$result['extra_fee'].'</td>';
						if($result['discount_type']==null || $result['discount_type']){
							$dis_type = "";
						}else{
							$dis_type = $result['discount_type'];
						}
						$str.= '<td>'.$dis_type.'</td>';
						$discount_percent='';
						if($result['discount_percent']>=0){
							$discount_percent=$discount_percent.'/';
						}
						
							$validate ='';
						if($result['is_onepayment']!=1){
							$validate=$result['start_date'].'/'.$result['validate'];
						}
						$str.= '<td>'.$discount_percent.$result['discount_amount'].'</td>';
						$str.= '<td>'.$result['paidamount'].'</td>';
						$str.= '<td>'.$result['balance'].'</td>';
						
						$str.= '<td>'.$validate.'</td>';
						$str.= '<td>'.$result['note'].'</td>';
						$str.= '<td>'.$result['void_status'].'</td>';
						$str.= '<td style="text-align:left;">'.$result['user_name'].'</td>';
						$str.= '<td style="text-align:left;"><a target="_blank" href="'.$url.'/allreport/accounting/rptreceiptdetail/id/'.$result['payment_id'].'">'.$tr->translate('DETAIL').'</a></td>';
					$str.="</tr>";
				}
			}
			return $str;
		}
		return $results;
	}

// 	function updateRegister($data,$payment_id){
// 		$db = $this->getAdapter();//ស្ពានភ្ជាប់ទៅកាន់Data Base
// 		$db->beginTransaction();//ទប់ស្កាត់មើលការErrore , មានErrore វាមិនអោយចូល
	
// 		if($data['void']==1){  // void
// 			try{
// 				$rsold = $this->getStudentPaymentByID($payment_id);
// 				if($rsold['data_from']!=1){ // not student study payments
// 					if($rsold['data_from']==2){
// 						$arr = array(
// 								'is_registered'=>0,
// 						);
// 						$this->_name='rms_student_test_result';
// 						$where="stu_test_id = ".$data['old_stu']." AND degree_result=".$rsold['degree_id']." AND grade_result=".$rsold['grade'];
// 						$this->update($arr, $where);//reverse to tested student
	
// 						$arr = array(
// 								'customer_type'=>4 //reverse to tested student
// 						);
// 					}elseif($rsold['data_from']==3){
// 						$arr = array(
// 								'customer_type' =>3, //reverse to crm
// 								'is_studenttest' =>0,
// 						);
// 					}
// 					$this->_name='rms_student';
// 					$where='stu_id = '.$data['old_stu'];
// 					$this->update($arr, $where);
// 				}
// 				// update payment and validate of service and tuition fee info back ,  and update stock back to origin
// 				if($rsold['is_void']==0){
// 					$this->updatePaymentInfoBack($payment_id,1);   // 1 is pay for both service and tuition fee
// 				}
	
// 				$this->_name='rms_student_payment';
// 				$arra=array(
// 						'is_void'=>$data['void'],
// 						'void_by'=>$this->getUserId(),
// 						'void_note'=>$data['void_note'],
// 				);
// 				$where = " id = ".$payment_id;
// 				$this->update($arra, $where);
					
// 				if(!empty($data['credit_memo_id']) && $rsold['is_void']==0){//check again because it old code
// 					$this->updateCreditMemoBack($data);
// 				}
	
// 				$key = new Application_Model_DbTable_DbKeycode();
// 				$keydata=$key->getKeyCodeMiniInv(TRUE);
// 				$condictionSale = empty($keydata['sale_cut_stock'])?0:$keydata['sale_cut_stock'];//0=Transfer Cut Stock Direct,1=Transfer  Cut Stock with Receive
// 				if ($condictionSale!=1){
// 					$sql="SELECT sd.* FROM `rms_saledetail` AS sd WHERE sd.payment_id =$payment_id";
// 					$saleDetail = $db->fetchAll($sql);
// 					if (!empty($saleDetail)) foreach ($saleDetail as $rs){
// 						//Qurey Cut Stock Detail
// 						$sql = "SELECT cd.* FROM `rms_cutstock_detail` AS cd WHERE cd.`student_paymentdetail_id` =".$rs['id'];
// 						$cutDetail = $db->fetchAll($sql);
// 						$qtyReceive = 0;
// 						if (!empty($cutDetail)) foreach ($cutDetail as $cut){
// 							$qtyReceive = $qtyReceive+$cut['qty_receive'];
// 							//Void All This Payment Cut Stock
// 							$_arr=array(
// 									'status'	      => 0,
// 									'user_id'  =>$this->getUserId(),
// 									'modify_date'	  => date("Y-m-d H:i:s"),
// 							);
// 							$this->_name ='rms_cutstock';
// 							$where = ' id = '.$cut['cutstock_id'];
// 							$this->update($_arr, $where);
// 						}
// 						//Update Sale Detial back
// 						$_arr=array(
// 								'qty_after'	      => ($rs['qty_after']+$qtyReceive),
// 						);
// 						$this->_name ='rms_saledetail';
// 						$where = ' id = '.$rs['id'];
// 						$this->update($_arr, $where);
	
// 						$dbpu = new Stock_Model_DbTable_DbPurchase();
// 						$dbpu->updateStock($rs['pro_id'],$data['branch_id'],+$qtyReceive);
// 					}
// 				}
	
// 				// 				$where = " payment_id = $payment_id";
// 				// 				$this->_name='rms_saledetail';
// 				// 				$this->delete($where);
	
// 				$db->commit();
// 				return 0;
// 			}catch (Exception $e){
// 				Application_Form_FrmMessage::message("UPDATE_FAIL");
// 				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 				$db->rollBack();
// 				exit();
// 			}
// 		}
// 	}
	
	function updateRegister($data){
		$db = $this->getAdapter();//ស្ពានភ្ជាប់ទៅកាន់Data Base
		$db->beginTransaction();//ទប់ស្កាត់មើលការErrore , មានErrore វាមិនអោយចូល
			try{
				
				$payment_id = $data['id'];
				$rsold = $this->getStudentPaymentByID($payment_id);
				if(empty($rsold)){
					return 2;//not permission
				}
				if($rsold['is_void']==1){
					return 3;//ready
				}
				$data['void_note']=$data['reason'];
				$data['branch_id']=$rsold['branch_id'];
				$data['void']=1;
				$data['old_stu']=$rsold['stu_id'];
				$data['credit_memo_id']= $rsold['memo_id'];
				
				if(!empty($rsold)){
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
									'qty_after'	  => ($rs['qty_after']+$qtyReceive),
							);
							$this->_name ='rms_saledetail';
							$where = ' id = '.$rs['id'];
							$this->update($_arr, $where);
		
							$dbpu = new Stock_Model_DbTable_DbPurchase();
							$dbpu->updateStock($rs['pro_id'],$data['branch_id'],+$qtyReceive);
						}
					}
		
					$db->commit();
					return 1;
				}
			}catch (Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				$db->rollBack();
			}
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
		
		$_db  = new Application_Model_DbTable_DbGlobal();
		$sql.= $_db->getUserAccessPermission('sp.branch_id');
		
		return $db->fetchRow($sql);
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
