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
    
    function getStudentPaymentStart($studentid,$service_id,$type){
    	$db = $this->getAdapter();
    	$sql="select spd.id from rms_student_payment AS sp,rms_student_paymentdetail AS spd where
    	sp.id=spd.payment_id and is_start=1 and service_id= $service_id and sp.student_id=$studentid and spd.type=$type limit 1 ";
    	//echo $sql;exit();
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
	    	$sql="select ser_cate_id from rms_program_name where service_id = $service_id "; // to get product id 
	    	$pro_id =  $db->fetchOne($sql);
	   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	    	
	    	if($pro_type==0){ // product រាយ  
		    	$sql1="select id,pro_qty from rms_product_location where pro_id = $pro_id $branch ";
		    	$qty_in_stock =  $db->fetchRow($sql1);
		    	//print_r($qty_in_stock);exit();
		    	
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
		    	
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		    	
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
		$db = $this->getAdapter();//ស្ពានភ្ជាប់ទៅកាន់Data Base
		$db->beginTransaction();//ទប់ស្កាត់មើលការErrore , មានErrore វាមិនអោយចូល
		$stu_code = $this->getNewAccountNumber($data['dept']);
		$receipt_number =$data['receipt_no']; //$this->getRecieptNo();
		
		try{
			if($data['payment_type']==1){//Tuition Fee and Service
				if($data['student_type']==3){//old
					$this->_name = "rms_student";
					$stu_type='';
					$payfor_type='';
					if($data['dept']==1 || $data['dept']==2){
						$stu_type=1;// kid - Grade6
						$payfor_type=1; // kid  - Grade 12 
					}else if($data['dept']==3){
						$stu_type=2; // Grade7 - Grade 12
						$payfor_type=1; // kid  - Grade 12 
					}else{
						$stu_type=3; // eng 
						$payfor_type=2; // eng and other subject
					}
					// update student information to grade that input	
					$this->_name='rms_student';
					$id=$data['old_studens'];
					$arr = array(
							'academic_year'=>$data['study_year'],
							'degree'	 =>$data['dept'],
							'grade'		 =>$data['grade'],
							'session'	 =>$data['session'],
							'room'		 =>$data['room'],
							'remark'	 =>$data['not'],
							'tel'	 	 =>$data['parent_phone'],
							'stu_type'	 =>$stu_type,
							'is_stu_new' =>0,
							'group_id'	 =>$data['group'],
							);
					$where = ' stu_id = '.$id;
					$this->update($arr, $where);
				}else {
					$stu_type='';
					$payfor_type='';
					if($data['dept']==1 || $data['dept']==2){
						$stu_type=1;// kid - Grade6
						$payfor_type=1; // kid  - Grade 12 
					}else if($data['dept']==3){
						$stu_type=2; // Grade7 - Grade 12
						$payfor_type=1; // kid  - Grade 12 
					}else{
						$stu_type=3; // eng 
						$payfor_type=2; // eng and other subject
					}
					$isset_group = 0;
					if($data['group']>-1){
						$isset_group=1;
					}
				    $arr=array(
							'stu_code'		=>$stu_code,
							'stu_khname'	=>$data['kh_name'],
							'stu_enname'	=>$data['en_name'],
							'sex'			=>$data['sex'],
				    		'tel'			=>$data['parent_phone'],
				    		'dob'			=>$data['dob'],
				    		'academic_year'	=>$data['study_year'],
							'degree'		=>$data['dept'],
							'grade'			=>$data['grade'],
				    		'room'			=>$data['room'],
				    		'session'		=>$data['session'],
				    		'remark'	 	=>$data['not'],
						    'stu_type'		=>$stu_type,
				    		'is_setgroup'	=>$isset_group,
				    		'branch_id'		=>$this->getBranchId(),
				    		'create_date'	=>date('Y-m-d H:i:s'),
							'user_id'		=>$this->getUserId(),
				    		'group_id'	 	=>$data['group'],
						);
				    
			    	$id= $this->insert($arr);
			    	
			    	if($data['group']>-1){
			    		$this->_name='rms_group_detail_student';
			    		$arra = array(
			    				'group_id'	=>$data['group'],
			    				'stu_id'	=>$id,
			    				'user_id'	=>$this->getUserId(),
			    				'status'	=>1,
			    				'date'		=>date('Y-m-d'),
			    		);
			    		$this->insert($arra);
			    	}
				}
				
			// insert to tbl_student_id for count id to generate new student_id
				$this->_name='rms_student_id';
				if($data['student_type']!=3){ 	//	new student
					$arra = array(
							'branch_id'	=>$this->getBranchId(),
							'stu_id'	=>$id,
							'degree'	=>$data['dept'],
					);
					$this->insert($arra);
				}
			//////////////////////////////////////////////////////////////////////	
				$this->_name='rms_student_payment';
				
				if($data['credit_memo_after']>0){
					//$credit_memo_after = $data['credit_memo_after'];
					$cut_credit_memo = $data['credit_memo'] - $data['credit_memo_after'];
				}else{
					//$credit_memo_after = $data['credit_memo'];
					$cut_credit_memo = $data['credit_memo'];
				}
				
				$arr=array(
						'student_id'	=>$id,
						'receipt_number'=>$receipt_number,
						'payment_type'	=> $data['payment_type'],
						'payfor_type'	=>$payfor_type,
						'year'			=>$data['study_year'],
						'degree'		=>$data['dept'],
						'grade'			=>$data['grade'],
						'session'		=>$data['session'],
						'time'			=>$data['study_hour'],
						'room_id'		=>$data['room'],
// 						'payment_term'	=>$data['payment_term'],
// 						'tuition_fee'	=>$data['tuitionfee'],
// 						'discount_percent'=>$data['discount'],
// 						'admin_fee'		=>$data['admin_fee'],
// 						'total'			=>$data['total_payment'],
// 						'total_payment'	=>$data['total_payment'],
// 						'paid_amount'	=>$data['paid_amount'],
// 						'receive_amount'=>$data['paid_amount'],
// 						'balance_due'	=>$data['balance'],
						'scholarship_percent'=>$data['scholarship_percent'],
						'scholarship_amount'=>$data['scholarship_amount'],
						'tution_feeperyear'=>$data['tution_peryear'],
						'total_scholarship'=>$data['total_scholarship'],
						'note'			=>$data['not'],
						'student_type'	=>$data['student_type'],  // 1=tested student , 2=new student , 3=old student
						//'create_date'	=>date('Y-m-d H:i:s'),//check date here 
						'create_date'	=>$data['paid_date'],
						//'amount_in_khmer'=>$data['char_price'],
						'user_id'		=>$this->getUserId(),
						'branch_id'		=>$this->getBranchId(),
						'grand_total'	=>$data['grand_total'],
						'fine'			=>$data['fine'],
						'memo_id'		=>$data['credit_memo_id'],
						'credit_memo'	=>$cut_credit_memo,
						'deduct'		=>$data['deduct'],
						'net_amount'	=>$data['net_amount'],
				);
				$paymentid = $this->insert($arr);
		/////////////// study_history /////////////////////////////////////////////		
				$this->_name='rms_study_history';
				if($data['student_type']!=3){
					$array = array(
							'stu_id'	=> $id,
							'stu_type'	=> $stu_type,
							'stu_code'	=> $stu_code,
							'academic_year'=>$data['study_year'],
							'degree'	=> $data['dept'],
							'grade'		=> $data['grade'],
							'session'	=> $data['session'],
							'room'		=> $data['room'],
							'remark'	=> $data['not'],
							'user_id'	=> $this->getUserId(),
							'branch_id'	=> $this->getBranchId(),
							'payment_id'=> $paymentid,
							'create_date'=>date('Y-m-d H:i:s'),
					);
					$this->insert($array);
				}
	////////////////// rms_creditmemo /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////			
				$this->_name='rms_creditmemo';
				if($data['student_type']==3){ // only old_student can have credit_memo
					if(!empty($data['credit_memo_id'])){
						if($data['credit_memo_after']>0){
							$array=array(
									'total_amountafter'=>$data['credit_memo_after'],
							);
						}else{
							$array=array(
									'total_amountafter'=>0,
									'type'=>1, // បង់រួច
							);
						}
						$where = " id = ".$data['credit_memo_id'];
						$this->update($array, $where);					
					}
				}
				
	/////////////////////// rms_student_test ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////			
				$this->_name='rms_student_test';
				if($data['stu_test']>0){
					$array = array(
							'register'=>1,
							'stu_code'=>$data['stu_id']
							);
					$where= " id = ".$data['stu_test'];
					$this->update($array, $where);
				}
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////			
				//update is_start=0 ដើម្បីអោយដឹងថា Service និង ឈប់ប្រើ រឺ  expired នៅពេលដែលសិស្សចាស់បង់លុយម្តងទៀត រួច store ID record that updated in is_parent of new record
	            $service = 4; // លេខ 4 ជាសេវាកម្មចុះឈ្មោះចូលរៀន
				$type=$payfor_type; // លេខ 1 ជាប្រភេទសិស្សពី kindergarten ដល់ ទី12 , 2 GEP Student
				$expired_record_id = $this->getStudentPaymentStart($id,$service,$type);
				if(empty($expired_record_id)){
					$expired_record_id=0;
				}
				$this->_name='rms_student_paymentdetail';
				$where=" id = $expired_record_id ";
				$arr = array(
						'is_start'=>0
				);
				$this->update($arr,$where);
				
				$complete=1;
				$comment="បង់រួច";
	            if($data){
// 					$service_id=4; // tuition_fee service
// 					$fee = $data["tuitionfee"];
// 					$subtotal=$data["total_payment"] + $data["admin_fee"];
// 					//$subtotal = $subtotal + $data["remark"]+$data["addmin_fee"] ;
// 					$discount=$data['discount'];
// 					$paidamount=$data['paid_amount'] + $data["admin_fee"];
// 					//$balance= $subtotal - $paidamount;
					 $arr=array(
	             			'payment_id'	=>$paymentid,
	             			'type'			=>$payfor_type,
	             			'service_id'	=>4, //  tuition_fee service
	             			'payment_term'	=>$data['payment_term'],
	             			'fee'			=>$data["tuitionfee"],
	             			'qty'			=>1,
	             			'subtotal'		=>$data["tuitionfee"],//$subtotal = fee * qty;
					 		'late_fee'		=>$data["late_fee"],
					 		'discount_percent'=>$data['discount_percent'],//$discount,
					 		'paidamount'	=>$data['total_payment'],//$paidamount,
	             			'balance'		=>0,
	             			'discount_fix'	=>$data['discount_fix'],
	             			'note'			=>$data['not'],
	             			'start_date'	=>$data['start_date'],
	             			'validate'		=>$data['end_date'],
	             			'references'	=>'frome registration',
	             			'is_parent'		=>$expired_record_id, // store record that updated to finished used
	             			'is_complete'	=>$complete,
	             			'comment'		=>$comment,
	             			'user_id'		=>$this->getUserId(),
	             	);
	             	$this->insert($arr);
	             }
	             
// 	             if(!empty($data['identitystock'])){
// 		             $idpro = explode(',', $data['identitystock']);
// 		             foreach ($idpro as $j){
// 		             	$this->_name='rms_saledetail';
// 		             	$arr = array(
// 		             			'payment_id'=>$paymentid,
// 		             			'pro_id'=>$data['produc_id'.$j],
// 		             			'qty'=>$data['pro_qty'.$j],
// 		             			'note'=>$data['remarkpro'.$j],
// 		             			'in_receipt'=>0,
// 		             	);
// 		             	$this->insert($arr);
// 		             }
// 	             }
	             
	             $this->_name="rms_student_paymentdetail";
	             $ids = explode(',', $data['identity']);
	             $disc = 0;
	             $total = 0;
	             foreach ($ids as $i){
	             	$payfor_type=0;
	             	$start_date = '';
	             	$validate='';
	             	if($data['service_type_'.$i]==1){ // product type
	             		$payfor_type = 4; // product payment
	             		$start_date = null; 
	             		$validate = null; // no need validate
						
						$this->updateStock($data['service_'.$i],$data['qty_'.$i],$data['product_type_'.$i]);
						$this->_name='rms_saledetail';
						$arr = array(
								'payment_id'=>$paymentid,
								'pro_id'	=>$data['service_'.$i],
								'qty'		=>$data['qty_'.$i],
								'price'		=>$data['price_'.$i],
								'cost'		=>$data['cost_'.$i],
								'note'		=>$data['remark'.$i],
								'in_receipt'=>1,
								);
					    $this->insert($arr);
						
	             	}else{
	             		$payfor_type = 3; // service payment
	             		$start_date = $data['date_start_'.$i];
	             		$validate = $data['validate_'.$i];
	             	}
	             	// update old record to finish if old student
	             	$spd_id = $this->getStudentPaymentStart($id, $data['service_'.$i],$payfor_type);
	             	$this->_name="rms_student_paymentdetail";
	             	if(!empty($spd_id)){
	             		$where="id = $spd_id";
	             		$arr = array(
	             				'is_start'=>0
	             		);
	             		$this->update($arr,$where);
	             	}else{
	             		$spd_id=0;
	             	}
	             	
	             	$complete=1;
             		$status="បង់រួច";
             		$this->_name="rms_student_paymentdetail";
	             	$_arr = array(
	             			'payment_id'	=>$paymentid,
	             			'service_id'	=>$data['service_'.$i],
	             			'payment_term'	=>$data['term_'.$i],
	             			'fee'			=>$data['price_'.$i],
	             			'qty'			=>$data['qty_'.$i],
	             			'subtotal'		=>$data['subtotal_'.$i],
	             			'late_fee'		=>$data['late_fee_service_'.$i],
	             			'extra_fee'		=>$data['additional_fee_'.$i],
	             			'discount_percent'	=>$data['discount_'.$i],
	             			'discount_fix'	=>0,
	             			'paidamount'	=>$data['paidamount_'.$i],
	             			'balance'		=>0,
	             			'start_date'	=>$start_date,
	             			'validate'		=>$validate,
	             			'note'			=>$data['remark'.$i],
	             			'type'			=>$payfor_type,
	             			'is_parent'		=>$spd_id,
	             			'is_complete'   =>$complete,
	             			'comment'		=>$status,
	             	);
	             	$this->insert($_arr);
	             } 
			} 
// ================================ pay for tuition fee only ============================================================================================
			else if($data['payment_type']==2){
				if($data['student_type']==3){//old
					$this->_name = "rms_student";
					$stu_type='';
					$payfor_type='';
					if($data['dept']==1 || $data['dept']==2){
						$stu_type=1;// kid - 6
						$payfor_type=1; // kid  - Grade 12 
					}else if($data['dept']==3){
						$stu_type=2; // 7 - Grade 12
						$payfor_type=1; // kid  - Grade 12 
					}else{
						$stu_type=3; // eng 
						$payfor_type=2; // eng and other subject
					}
					// update student information to grade that input
					$id=$data['old_studens'];
					$arr = array(
							'session'	=>$data['session'],
							'degree'	=>$data['dept'],
							'grade'		=>$data['grade'],
							'academic_year'=>$data['study_year'],
							'room'		=>$data['room'],
							'stu_type'	=>$stu_type,
							'remark'	=>$data['not'],
							'is_stu_new'=>0,
							'group_id'	=>$data['group'],
					);
					$where = ' stu_id = '.$id;
					$this->update($arr, $where);
				}else {
					$stu_type='';
					$payfor_type='';
					if($data['dept']==1 || $data['dept']==2){
						$stu_type=1;// kid - 6
						$payfor_type=1; // kid  - Grade 12 
					}else if($data['dept']==3){
						$stu_type=2; // 7 - Grade 12
						$payfor_type=1; // kid  - Grade 12 
					}else{
						$stu_type=3; // eng 
						$payfor_type=2; // eng and other subject
					}
					$isset_group = 0;
					if($data['group']>-1){
						$isset_group=1;
					}
					$arr=array(
							'stu_code'		=>$stu_code,
							'stu_khname'	=>$data['kh_name'],
							'stu_enname'	=>$data['en_name'],
							'sex'			=>$data['sex'],
							'tel'			=>$data['parent_phone'],
							'dob'			=>$data['dob'],
							'academic_year'	=>$data['study_year'],
							'degree'		=>$data['dept'],
							'grade'			=>$data['grade'],
							'session'		=>$data['session'],
							'room'			=>$data['room'],
							'is_setgroup'	=>$isset_group,
							'stu_type'		=>$stu_type,
							'remark'		=>$data['not'],
							'branch_id'		=>$this->getBranchId(),
							'create_date'	=>date('Y-m-d H:i:s'),
							'user_id'		=>$this->getUserId(),
							'group_id'		=>$data['group'],
					);
					$id= $this->insert($arr);
					
					if($data['group']>-1){
						$this->_name='rms_group_detail_student';
						$arra = array(
								'group_id'	=>$data['group'],
								'stu_id'	=>$id,
								'user_id'	=>$this->getUserId(),
								'status'	=>1,
								'date'		=>date('Y-m-d'),
						);
						$this->insert($arra);
					}
				}
				
			// insert to tbl_student_id for count id to generate new student_id
				$this->_name='rms_student_id';
				if($data['student_type']==1){ 	//	new student
					$arra = array(
							'branch_id'	=>$this->getBranchId(),
							'stu_id'	=>$id,
							'degree'	=>$data['dept'],
					);
					$this->insert($arra);
				}
			//////////////////////////////////////////////////////////////////////
				$this->_name='rms_student_payment';
				$arr=array(
						'student_id'	=>$id,
						'receipt_number'=>$receipt_number,
						'payment_type'	=> $data['payment_type'],
						'payfor_type'	=>$payfor_type,
						'year'			=>$data['study_year'],
						'degree'		=>$data['dept'],
						'grade'			=>$data['grade'],
						'time'			=>$data['study_hour'],
						'session'		=>$data['session'],
						'room_id'		=>$data['room'],
// 						'payment_term'	=>$data['payment_term'],
// 						'tuition_fee'	=>$data['tuitionfee'],
// 						'discount_percent'=>$data['discount'],
// 						//'other_fee'		=>$data['remark'],
// 						//'admin_fee'		=>$data['addmin_fee'],
// 						'total'			=>$data['total_payment'],
// 						'total_payment'	=>$data['total_payment'],
// 						'paid_amount'	=>$data['paid_amount'],
// 						'receive_amount'=>$data['paid_amount'],
// 						'balance_due'	=>$data['balance'],
						'scholarship_percent'=>$data['scholarship_percent'],
						'scholarship_amount'=>$data['scholarship_amount'],
						'tution_feeperyear'=>$data['tution_peryear'],
						'total_scholarship'=>$data['total_scholarship'],
						'note'			=>$data['not'],
						'student_type'	=>$data['student_type'],
						'create_date'	=>date('Y-m-d H:i:s'),
						//'amount_in_khmer'=>$data['char_price'],
						'user_id'		=>$this->getUserId(),
						'branch_id'		=>$this->getBranchId(),
						
						'grand_total'	=>$data['grand_total'],
						'fine'			=>$data['fine'],
						
						'memo_id'		=>$data['credit_memo_id'],
						'credit_memo'	=>$data['credit_memo'],
						'deduct'		=>$data['deduct'],
						'net_amount'	=>$data['net_amount'],
				);
				$paymentid = $this->insert($arr);
				
		//////////////	rms_study_history ///////////////////////////////////////////////
				$this->_name='rms_study_history';
				if($data['student_type']!=3){
					$array = array(
							'stu_id'	=> $id,
							'stu_type'	=> $stu_type,
							'stu_code'	=> $stu_code,
							'academic_year'=>$data['study_year'],
							'degree'	=> $data['dept'],
							'grade'		=> $data['grade'],
							'session'	=> $data['session'],
							'room'		=> $data['room'],
							'remark'	=> $data['not'],
							'user_id'	=> $this->getUserId(),
							'branch_id'	=> $this->getBranchId(),
							'payment_id'=> $paymentid,
							'create_date'=>date('Y-m-d H:i:s'),
					);
					$this->insert($array);
				}
	////////////////// rms_creditmemo /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				$this->_name='rms_creditmemo';
				if($data['student_type']==3){
					if(!empty($data['credit_memo_id'])){
						if($data['net_amount']>=0){
							$array=array(
									'type'=>1, // បង់រួច
							);
						}else{
							$array=array(
									'total_amountafter'=>abs($data['net_amount']), //
							);
						}
						$where = " id = ".$data['credit_memo_id'];
						$this->update($array, $where);
					}
				}
	/////////////////////// rms_student_test ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////			
				$this->_name='rms_student_test';
				if($data['stu_test']>0){
					$array = array(
							'register'=>1,
							);
					$where= " id = ".$data['stu_test'];
					$this->update($array, $where);
				}
		//////////////////////////////////////////////////////////////////////////////////////		
				$this->_name='rms_student_paymentdetail';
				//update is_start=0 ដើម្បីអោយដឹងថា Service និង ឈប់ប្រើ រឺ  expired នៅពេលដែលសិស្សចាស់បង់លុយម្តងទៀត រួច store ID record that updated in is_parent of new record
				$service = 4; // លេខ 4 ជាសេវាកម្មចុះឈ្មោះចូលរៀន
				$type=$payfor_type; // លេខ 1 ជាប្រភេទសិស្សពី kindergarten ដល់ ទី12 , 2 GEP Student
				$expired_record_id = $this->getStudentPaymentStart($id,$service,$type);
				if(empty($expired_record_id)){
					$expired_record_id=0;
				}
				$where=" id = $expired_record_id ";
				$arr = array(
						'is_start'=>0
				);
				$this->update($arr,$where);
				$complete=1;
				$comment="បង់រួច";
				if($data){
// 					$service_type=4; // tuition_fee service
// 					$fee = $data["tuitionfee"];
// 					$subtotal=$data["total_payment"] + $data["admin_fee"] ;
// 					//$subtotal = $subtotal + $data["remark"]+$data["addmin_fee"] ;
// 					$discount=$data['discount'];
// 					$paidamount=$data['paid_amount'] + $data["admin_fee"] ;
// 					$balance= $subtotal - $paidamount;
					$arr=array(
							'payment_id'	=>$paymentid,
							'type'			=>$payfor_type,
							'service_id'	=>4, // tuition_fee service,
							'payment_term'	=>$data['payment_term'],
	             			'fee'			=>$data["tuitionfee"],
	             			'qty'			=>1,
	             			'subtotal'		=>$data["tuitionfee"],//$subtotal = fee * qty;
					 		'late_fee'		=>$data["late_fee"],
					 		'discount_percent'=>$data['discount_percent'],//$discount,
							'discount_fix'	=>$data['discount_fix'],
					 		'paidamount'	=>$data['total_payment'],//$paidamount,
	             			'balance'		=>0,
							'note'			=>$data['not'],
							'start_date'	=>$data['start_date'],
							'validate'		=>$data['end_date'],
							'references'	=>'from registration',
							'is_parent'		=>$expired_record_id, // store record that updated to already used
							'is_complete'	=>$complete,
							'comment'		=>$comment,
							'user_id'		=>$this->getUserId(),
					);
					$this->insert($arr);
				}
				
			}
// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&			
			else if($data['payment_type']==3){ // pay for service and product only for old studnet
				$this->_name="rms_student_payment";
				$array=array(
						'student_id'	=> $data['old_studens'],		
						'receipt_number'=> $receipt_number,
						'payfor_type'	=> 3, // service and product
						'payment_type'	=> $data['payment_type'],
						'student_type'	=> $data['student_type'],
						
						'grand_total'	=>$data['grand_total'],
						'fine'			=>$data['fine'],
						
						'memo_id'		=>$data['credit_memo_id'],
						'credit_memo'	=>$data['credit_memo'],
						'deduct'		=>$data['deduct'],
						'net_amount'	=>$data['net_amount'],
						
						'scholarship_percent'=>$data['scholarship_percent'],
						'scholarship_amount'=>$data['scholarship_amount'],
						'tution_feeperyear'=>$data['tution_peryear'],
						'total_scholarship'=>$data['total_scholarship'],
						
						'create_date'	=> date('Y-m-d'),
						'user_id'		=>$this->getUserId(),
						'branch_id'=>$this->getBranchId(),
						);
				$paymentid = $this->insert($array);		

				$this->_name="rms_student_paymentdetail";
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$payfor_type=0;
					$start_date = '';
					$validate='';
					if($data['service_type_'.$i]==1){
						$payfor_type = 4; // product payment
						$start_date = null;
						$validate = null; // no need validate
						
						$this->updateStock($data['service_'.$i],$data['qty_'.$i],$data['product_type_'.$i]);
						
						$this->_name='rms_saledetail';
						$arr = array(
								'payment_id'=>$paymentid,
								'pro_id'	=>$data['service_'.$i],
								'qty'		=>$data['qty_'.$i],
								'price'		=>$data['price_'.$i],
								'cost'		=>$data['cost_'.$i],
								'note'		=>$data['remark'.$i],
								'in_receipt'=>1,
						);
						$this->insert($arr);
						
					}else{
						$payfor_type = 3; // service payment
						$start_date = $data['date_start_'.$i];
						$validate = $data['validate_'.$i];
					}
					// update old record to finish if old student
					$this->_name="rms_student_paymentdetail";
					$spd_id = $this->getStudentPaymentStart($data['old_studens'], $data['service_'.$i],$payfor_type);
					if(!empty($spd_id)){
						$where="id = $spd_id";
						$arr = array(
							'is_start'=>0,
							'is_complete'=>1,
							'comment'=>'បង់រួច',
						);
						$this->update($arr,$where);
					}else{
						$spd_id=0;
					}
					$complete=1;
					$status="បង់រួច";
					$this->_name="rms_student_paymentdetail";
					$_arr = array(
							'payment_id'	=>$paymentid,
							'type'			=>$payfor_type,
							'service_id'	=>$data['service_'.$i],
							'payment_term'	=>$data['term_'.$i],
							'fee'			=>$data['price_'.$i],
							'qty'			=>$data['qty_'.$i],
							'subtotal'		=>$data['subtotal_'.$i],
							'late_fee'		=>$data['late_fee_service_'.$i],
							'extra_fee'		=>$data['additional_fee_'.$i],
							'discount_percent' =>$data['discount_'.$i],
							'discount_fix'	=>0,
							
							
							'paidamount'	=>$data['paidamount_'.$i],
							'balance'		=>0,
							'start_date'	=>$start_date,
							'validate'		=>$validate,
							'note'			=>$data['remark'.$i],
							'is_parent'		=>$spd_id,
							'is_complete'   =>$complete,
							'comment'		=>$status,
					);
					$this->insert($_arr);
				}
		////////////////// rms_creditmemo /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				$this->_name='rms_creditmemo';
				if($data['student_type']==3){
					if(!empty($data['credit_memo_id'])){
						if($data['net_amount']>=0){
							$array=array(
									'type'=>1, // បង់រួច
							);
						}else{
							$array=array(
									'total_amountafter'=>abs($data['net_amount']), //
							);
						}
						$where = " id = ".$data['credit_memo_id'];
						$this->update($array, $where);
					}
				}
			}
			$db->commit();//if not errore it do....
			}catch (Exception $e){
				$db->rollBack();//អោយវាវិលត្រលប់ទៅដើមវីញពេលណាវាជួបErrore
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
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
							//echo $qty ; exit();
							
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
		
//	if $type=1 we use fetchAll cuz student pay both or service (service AND tuition fee) more than 1 row, $type=2 fetchRow cuz student pay only tuition fee
		if($type==1){
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
					
					//if($result['type']==4){ // type=4 is product payment
						//$this->updateStockBack($payment_id);
					//}
				}
				$this->updateStockBack($payment_id);
			}
			
		}else{
			$id_old_record = $db->fetchRow($sql);
			
			if(!empty($id_old_record['is_parent'])){
				$this->_name="rms_student_paymentdetail";
				$arr = array(
						'is_start'=>1,
						);
				$where = " id = ".$id_old_record['is_parent'];
				$this->update($arr, $where);
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
	
	
	function updateRegister($data,$payment_id){
		$db = $this->getAdapter();//ស្ពានភ្ជាប់ទៅកាន់Data Base
		$db->beginTransaction();//ទប់ស្កាត់មើលការErrore , មានErrore វាមិនអោយចូល
		//echo $data['void'];exit();
		
		if($data['void']==1){  // void
			
			// if void=1 that mean this record is useless so we only update is_void to 1 no need to update info anymore
			try{	
				$this->_name='rms_student_payment';
				$arra=array(
						'is_void'=>$data['void'],
						);
				$where = " id = ".$payment_id;
				$this->update($arra, $where);
				
				if(!empty($data['credit_memo_id'])){
					$this->updateCreditMemoBack($data);
				}
				
//&&&&&&&&&&&&&&&&&&&&&&&&  if payment_type=1 (pay both tuition_fee and service or product)	$%%$%$%$%$%$%$%$%$%$%$%$%$%$%$%$%$%$			
				if($data['payment_type']==1){
					
				// get id record that we have updated to update it to using again when new record is void
					$parentIdStuHis = $this->getParentIdStudentHistory($payment_id);
				// update old study info to using 	
					if(!empty($parentIdStuHis)){
						
						$this->_name="rms_study_history";
						$array = array(
								'is_finished'=>'0',
								'finished_date'=>null,
								);
						$where = " id = $parentIdStuHis ";
						$this->update($array, $where);
					  //update student info back to old info
						$this->UpdateStudentInfoBack($parentIdStuHis,$payment_id);
					}
					
				// update payment and validate of service and tuition fee info back ,  and update stock back to origin	
					
					$this->updatePaymentInfoBack($payment_id,1);   // 1 is pay for both service and tuition fee
					
					if($data['is_new_stu']==1){  // 1 = is new student 
						$this->deleteFromGroup($data['old_stu_name'],$data['group']);
					}
					
				}else if($data['payment_type']==2){
					
					// get id record that we have updated to update it to using again when new record is void
					$parentIdStuHis = $this->getParentIdStudentHistory($payment_id);
					// update old study info to using
					if(!empty($parentIdStuHis)){
					
						$this->_name="rms_study_history";
						$array = array(
								'is_finished'=>'0',
								'finished_date'=>null,
						);
						$where = " id = $parentIdStuHis ";
						$this->update($array, $where);
					
						$this->UpdateStudentInfoBack($parentIdStuHis,$payment_id);
					}
					
					$this->updatePaymentInfoBack($payment_id,2);   // 2 is pay for only tuition fee 
					
					if($data['is_new_stu']==1){  // 1 = is new student
						$this->deleteFromGroup($data['old_stu_name'],$data['group']);
					}
					
					
				}else if($data['payment_type']==3){
					
					$this->updatePaymentInfoBack($payment_id,1);
				
				}
				
				$db->commit();
				return 0;
			}catch (Exception $e){
				$db->rollBack();
				echo $e->getMessage();exit();
			}
		}else if($data['void']==2){ // delete
					
			if(!empty($data['credit_memo_id'])){
				$this->updateCreditMemoBack($data);
			}		
			
			if($data['payment_type']==1){
				if($data['student_type']==3){ // do when old student 
				// get id record that we have updated to update it to using again when new record is void
					$parentIdStuHis = $this->getParentIdStudentHistory($payment_id);
				// update old study info to using 	
					if(!empty($parentIdStuHis)){
						$this->_name="rms_study_history";
						
						//update student info back to old info
						$this->UpdateStudentInfoBack($parentIdStuHis,$payment_id);
						
						$where = " id = $parentIdStuHis ";
						$this->delete($where);
					}
				}
			// update payment and validate of service and tuition fee info back ,  and update stock back to origin	
				
				$this->updatePaymentInfoBack($payment_id,1);   // 1 is pay for both service and tuition fee
				
				if($data['student_type']!=3){  // 3 = old student 
					$this->deleteFromGroup($data['old_stu_name'],$data['group']);
					$this->deleteStudent($data['old_stu_name']);
				}
					
			}else if($data['payment_type']==2){
				if($data['student_type']==3){ // do when old student 	
					// get id record that we have updated to update it to using again when new record is void
					$parentIdStuHis = $this->getParentIdStudentHistory($payment_id);
					// update old study info to using
					if(!empty($parentIdStuHis)){
						
						$this->_name="rms_study_history";
						$this->UpdateStudentInfoBack($parentIdStuHis,$payment_id);
						$where = " id = $parentIdStuHis ";
						$this->delete($array, $where);
					}
				}
				
				$this->updatePaymentInfoBack($payment_id,2);   // 2 is pay for only tuition fee 
				
				if($data['student_type']!=3){  // 3 = old student 
					$this->deleteFromGroup($data['old_stu_name'],$data['group']);
					$this->deleteStudent($data['old_stu_name']);
				}
				
				
			}else if($data['payment_type']==3){
				
				$this->updatePaymentInfoBack($payment_id,1);
			
			}		
			
			$this->_name='rms_student_payment';
			$where = " id = ".$payment_id;
			$this->delete($where);
			
			$this->_name='rms_student_paymentdetail';
			$where = " payment_id = ".$payment_id;
			$this->delete($where);		
				
			$db->commit();
			return 0;				
		}else{
			return 0;			
		}
		
		
		
		echo 1;exit();
			try{
				
			//update is_void=0 ,if this record already set is_void=1 for last time so we update it to is_void=0	so this record is use
				$this->_name='rms_student_payment';
				$array=array(
						'is_void'=>0,
				);
				$where = " id = $payment_id ";
				$this->update($array, $where);
				
			//update stock to first amount by sum up by old qty we orderded	
				$this->updateStockBack($payment_id);
				
				$this->_name='rms_student_paymentdetail';
				$rows = $this->getParentIDToUpdateBack($payment_id);
				
				if(!empty($rows)){foreach ($rows as $id_update_back){
					$arr = array(
							'is_start' => 1,
							);
					$where = ' id = '.$id_update_back['is_parent'];
					$this->update($arr, $where);
				}}
				
				$where = " payment_id = $payment_id";
				$this->delete($where);

// ###################################### pay for both ####################################################################
				
				if($data['payment_type']==1){
					if($data['student_type']==3){//old
						$this->_name = "rms_student";
						$stu_type='';
						$payfor_type='';
						if($data['dept']==1){
							$stu_type=3;
							$payfor_type=1;  // BacII student
						}else if($data['dept']==2 || $data['dept']==3 || $data['dept']==4){
							$stu_type=1;  
							$payfor_type=1;  // BacII student
						}else{
							$stu_type=2;  
							$payfor_type=2;  // GEP student
						}
						// update student information to grade that input
						$stu_id=$data['old_studens'];
						$arr = array(
								'stu_khname'=>$data['kh_name'],
								'stu_enname'=>$data['en_name'],
								'sex'		=>$data['sex'],
								'tel'		=>$data['parent_phone'],
								'session'	=>$data['session'],
								'degree'	=>$data['dept'],
								'grade'		=>$data['grade'],
								'room'		=>$data['room'],
								'academic_year'=>$data['study_year'],
								'stu_type'	=>$stu_type,
						);
						$where = ' stu_id = '.$stu_id;
						$this->update($arr, $where);
						
						
						$this->_name='rms_study_history';
						
						$array = array(
								'stu_id'	=> $stu_id,
								'stu_type'	=> $stu_type,
								//'stu_code'	=> $data['stu_id'],
								'academic_year'=>$data['study_year'],
								'degree'	=> $data['dept'],
								'grade'		=> $data['grade'],
								'session'	=> $data['session'],
								'user_id'	=> $this->getUserId(),
								//'create_date'=>date('Y-m-d H:i:s'),
						);
						$where = " stu_id = ".$stu_id;
						$this->update($array, $where);
						
					}
					
					
					$this->_name='rms_student_payment';
					$arr=array(
							'student_id'	=>$stu_id,
							//'receipt_number'=>$data['reciept_no'],
							'payment_type'	=> $data['payment_type'],
							'payfor_type'	=>$payfor_type,
							'year'			=>$data['study_year'],
							'degree'		=>$data['dept'],
							'grade'			=>$data['grade'],
							'time'			=>$data['study_hour'],
							'session'		=>$data['session'],
							'room_id'			=>$data['room'],
							'payment_term'	=>$data['payment_term'],
							'tuition_fee'	=>$data['tuitionfee'],
							'discount_percent'=>$data['discount'],
							//'other_fee'		=>$data['remark'],
							//'admin_fee'		=>$data['addmin_fee'],
							'total'			=>$data['total_payment'],
							'total_payment'	=>$data['total_payment'],
							'paid_amount'	=>$data['paid_amount'],
							'receive_amount'=>$data['paid_amount'],
							'balance_due'	=>$data['balance'],
							'note'			=>$data['not'],
							//'student_type'	=>$data['student_type'],  // new or old
							//'create_date'	=>date('Y-m-d H:i:s'),
							//'amount_in_khmer'=>$data['char_price'],
							'user_id'=>$this->getUserId(),
				
							'grand_total'	=>$data['grand_total'],
							'grand_paid_amount'	=>$data['total_received'],
							'grand_balance'	=>$data['total_balance'],
							//'grand_return'	=>$data['total_return'],
				
					);
					
					$where = " id = $payment_id";
					$this->update($arr, $where);
					
					
					$this->_name='rms_student_paymentdetail';
					//update is_start=0 ដើម្បីអោយដឹងថា Service និង ឈប់ប្រើ រឺ  expired នៅពេលដែលសិស្សចាស់បង់លុយម្តងទៀត រួច store ID record that updated in is_parent of new record
					$service = 4; // លេខ 4 ជាសេវាកម្មចុះឈ្មោះចូលរៀន
					$type=$payfor_type; // លេខ 1 ជាប្រភេទសិស្សពី kindergarten ដល់ ទី12 , 2 GEP Student
					$expired_record_id = $this->getStudentPaymentStart($stu_id,$service,$type);
					if(empty($expired_record_id)){
						$expired_record_id=0;
					}
					$where=" id = $expired_record_id ";
					$arr = array(
							'is_start'=>0
					);
					$this->update($arr,$where);
					//update is_complete = 1 ពេលសិស្សមកបង់ថ្លៃដែលបានជំពាក់
					$this->_name='rms_student_paymentdetail';
					if(!empty($data['id_balance_record'])){
						$where="id=".$data['id_balance_record'];
						$arr = array(
								'is_complete'=>1,
								'comment'=>"បង់រួច",
						);
						$this->update($arr,$where);
					}
					$complete=1;
					if($data['balance']>0){
						$complete=0;
						$comment="មិនទាន់បង់";
					}else{
						$complete=1;
						$comment="បង់រួច";
					}
				
					if($data){
						$service_type=4; // tuition_fee service
						$fee = $data["tuitionfee"];
						$subtotal=$data["total_payment"];
						//$subtotal = $subtotal + $data["remark"]+$data["addmin_fee"] ;
						$discount=$data['discount'];
						$paidamount=$data['paid_amount'];
						$balance= $subtotal - $paidamount;
				
						$arr=array(
								'payment_id'=>$payment_id,
								'type'=>$payfor_type,
								'service_id'=>$service_type,
								'payment_term'=>$data['payment_term'],
								'fee'=>$fee,
								'qty'=>1,
								//'subtotal'=>$data['total'],
								'subtotal'=>$subtotal,//$subtotal,
								'paidamount'=>$paidamount,//$paidamount,
								'balance'=>$balance,
								'discount_percent'=>$discount,//$discount,
								'discount_fix'=>0,
								'note'=>$data['not'],
								'start_date'=>$data['start_date'],
								'validate'=>$data['end_date'],
								'references'=>'frome registration',
								'is_parent'		=>$expired_record_id, // store id record that updated to already used
								'is_complete'	=>$complete,
								'comment'		=>$comment,
								'user_id'=>$this->getUserId(),
						);
						$this->insert($arr);
					}
				
				
					$this->_name="rms_student_paymentdetail";
					$ids = explode(',', $data['identity']);
					
					foreach ($ids as $i){
						 
						$payfor_type=0;
						$start_date = '';
						$validate='';
						if($data['service_type_'.$i]==1){ // ជាការបង់ product
							$payfor_type = 4; // product payment
							$start_date = null;
							$validate = null; // no need validate
							
							$this->updateStock($data['service_'.$i],$data['qty_'.$i]);
							
						}else{ // ជាការបង់ Service
							$payfor_type = 3; // service payment
							$start_date = $data['date_start_'.$i];
							$validate = $data['validate_'.$i];
						}
						 
						// update old record to finish if old student
				
						$spd_id = $this->getStudentPaymentStart($stu_id, $data['service_'.$i],$payfor_type);
						if(!empty($spd_id)){
							$where="id = $spd_id";
							$arr = array(
									'is_start'=>0
							);
							$this->update($arr,$where);
						}else{
							$spd_id=0;
						}
						// check balance
						$complete=1;
						if($data['subtotal_'.$i]-$data['paidamount_'.$i]>0){
							$complete=0;
							$status="មិនទាន់បង់";
						}else{
							$complete=1;
							$status="បង់រួច";
						}
						
						$this->_name='rms_student_paymentdetail';
						
						$_arr = array(
								'payment_id'	=>$payment_id,
								'service_id'	=>$data['service_'.$i],
								'payment_term'	=>$data['term_'.$i],
								'fee'			=>$data['price_'.$i],
								'qty'			=>$data['qty_'.$i],
								'discount_percent'	=>$data['discount_'.$i],
								'subtotal'		=>$data['subtotal_'.$i],
								'paidamount'	=>$data['paidamount_'.$i],
								'balance'		=>$data['subtotal_'.$i]-$data['paidamount_'.$i],
								'start_date'	=>$start_date,
								'validate'		=>$validate,
								'note'			=>$data['remark'.$i],
								'type'			=>$payfor_type,
								'is_parent'		=>$spd_id,
								'is_complete'   =>$complete,
								'comment'		=>$status,
								'user_id'		=>$this->getUserId(),
						);
						$this->insert($_arr);
				
					}
				
				}
				
				
// ================================= pay only tuition fee ===========================================================================================
				
				else if($data['payment_type']==2){
				
						
					if($data['student_type']==3){//old
						$this->_name = "rms_student";
						$stu_type='';
						$payfor_type='';
						if($data['dept']==1){
							$stu_type=3;
							$payfor_type=1;  // BacII student
						}else if($data['dept']==2 || $data['dept']==3 || $data['dept']==4){
							$stu_type=1;
							$payfor_type=1;  // BacII student
						}else{
							$stu_type=2;
							$payfor_type=2;  // GEP student
						}
						// update student information to grade that input
						$stu_id=$data['old_studens'];
						$arr = array(
								'stu_khname'=>$data['kh_name'],
								'stu_enname'=>$data['en_name'],
								'sex'		=>$data['sex'],
								'tel'		=>$data['parent_phone'],
								'session'	=>$data['session'],
								'degree'	=>$data['dept'],
								'grade'		=>$data['grade'],
								'room'		=>$data['room'],
								'academic_year'=>$data['study_year'],
								'stu_type'	=>$stu_type,
						);
						$where = ' stu_id = '.$stu_id;
						$this->update($arr, $where);
						
						
						$this->_name='rms_study_history';
						
						$array = array(
								'stu_id'	=> $stu_id,
								'stu_type'	=> $stu_type,
								//'stu_code'	=> $data['stu_id'],
								'academic_year'=>$data['study_year'],
								'degree'	=> $data['dept'],
								'grade'		=> $data['grade'],
								'session'	=> $data['session'],
								'user_id'	=> $this->getUserId(),
								//'create_date'=>date('Y-m-d H:i:s'),
						);
						$where = " stu_id = ".$stu_id;
						$this->update($array, $where);
						
					}
					
					
					$this->_name='rms_student_payment';
					$arr=array(
							'student_id'	=>$stu_id,
							//'receipt_number'=>$data['reciept_no'],
							'payment_type'	=> $data['payment_type'],
							'payfor_type'	=>$payfor_type,
							'year'			=>$data['study_year'],
							'degree'		=>$data['dept'],
							'grade'			=>$data['grade'],
							'time'			=>$data['study_hour'],
							'session'		=>$data['session'],
							'room_id'			=>$data['room'],
							'payment_term'	=>$data['payment_term'],
							'tuition_fee'	=>$data['tuitionfee'],
							'discount_percent'=>$data['discount'],
							//'other_fee'		=>$data['remark'],
							//'admin_fee'		=>$data['addmin_fee'],
							'total'			=>$data['total_payment'],
							'total_payment'	=>$data['total_payment'],
							'paid_amount'	=>$data['paid_amount'],
							'receive_amount'=>$data['paid_amount'],
							'balance_due'	=>$data['balance'],
							'note'			=>$data['not'],
							//'student_type'	=>$data['student_type'],
							//'create_date'	=>date('Y-m-d H:i:s'),
							//'amount_in_khmer'=>$data['char_price'],
							'user_id'=>$this->getUserId(),
				
							'grand_total'	=>$data['grand_total'],
							'grand_paid_amount'	=>$data['total_received'],
							'grand_balance'	=>$data['total_balance'],
							//'grand_return'	=>$data['total_return'],
					);
					$where =  " id = $payment_id " ; 
					$this->update($arr, $where);
					
					$this->_name='rms_student_paymentdetail';
					//update is_start=0 ដើម្បីអោយដឹងថា Service និង ឈប់ប្រើ រឺ  expired នៅពេលដែលសិស្សចាស់បង់លុយម្តងទៀត រួច store ID record that updated in is_parent of new record
					$service = 4; // លេខ 4 ជាសេវាកម្មចុះឈ្មោះចូលរៀន
					$type=$payfor_type; // លេខ 1 ជាប្រភេទសិស្សពី kindergarten ដល់ ទី12 , 2 GEP Student
					$expired_record_id = $this->getStudentPaymentStart($stu_id,$service,$type);
					if(empty($expired_record_id)){
						$expired_record_id=0;
					}
					$where=" id = $expired_record_id ";
					$arr = array(
							'is_start'=>0
					);
					$this->update($arr,$where);
					//update is_complete = 1 ពេលសិស្សមកបង់ថ្លៃដែលបានជំពាក់
					$this->_name='rms_student_paymentdetail';
					if(!empty($data['id_balance_record'])){
						$where="id=".$data['id_balance_record'];
						$arr = array(
								'is_complete'=>1,
								'comment'=>"បង់រួច",
						);
						$this->update($arr,$where);
					}
					$complete=1;
					if($data['balance']>0){
						$complete=0;
						$comment="មិនទាន់បង់";
					}else{
						$complete=1;
						$comment="បង់រួច";
					}
				
					if($data){
						$service_type=4; // tuition_fee service
						$fee = $data["tuitionfee"];
						$subtotal=$data["total_payment"];
						//$subtotal = $subtotal + $data["remark"]+$data["addmin_fee"] ;
						$discount=$data['discount'];
						$paidamount=$data['paid_amount'];
						$balance= $subtotal - $paidamount;
				
						$arr=array(
								'payment_id'=>$payment_id,
								'type'=>$payfor_type,
								'service_id'=>$service_type,
								'payment_term'=>$data['payment_term'],
								'fee'=>$fee,
								'qty'=>1,
								//'subtotal'=>$data['total'],
								'subtotal'=>$subtotal,//$subtotal,
								'paidamount'=>$paidamount,//$paidamount,
								'balance'=>$balance,
								'discount_percent'=>$discount,//$discount,
								'discount_fix'=>0,
								'note'=>$data['not'],
								'start_date'=>$data['start_date'],
								'validate'=>$data['end_date'],
								'references'=>'frome registration',
								'is_parent'		=>$expired_record_id, // store record that updated to already used
								'is_complete'	=>$complete,
								'comment'		=>$comment,
								'user_id'=>$this->getUserId(),
						);
						$this->insert($arr);
							
					}
				
				}
				

// &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& pay for service only &&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&
				
					
				else if($data['payment_type']==3){ // pay for service only for old studnet
				
					$this->_name="rms_student_payment";
				
					$array=array(
							'student_id'	=> $data['old_studens'],
							'payfor_type'	=> 3, // service and product
							'payment_type'	=> $data['payment_type'],
							//'student_type'	=> $data['student_type'],  // new or old
							//'grand_return'	=> $data['total_return'],
							//'create_date'	=> date('Y-m-d'),
							'user_id'		=>$this->getUserId(),
							
							'grand_total'	=> $data['grand_total'],
							'grand_paid_amount'	=> $data['total_received'],
							'grand_balance'	=> $data['total_balance'],
							
					);
					$where = " id = $payment_id ";
					$this->update($array, $where);
				
				
					$this->_name="rms_student_paymentdetail";
				
					$ids = explode(',', $data['identity']);
					foreach ($ids as $i){
				
						$payfor_type=0;
						$start_date = '';
						$validate='';
						if($data['service_type_'.$i]==1){
							$payfor_type = 4; // product payment
							$start_date = null;
							$validate = null; // no need validate
							
							$this->updateStock($data['service_'.$i],$data['qty_'.$i]);
							
						}else{
							$payfor_type = 3; // service payment
							$start_date = $data['date_start_'.$i];
							$validate = $data['validate_'.$i];
						}
				
						// update old record to finish if old student
				
						$spd_id = $this->getStudentPaymentStart($data['old_studens'], $data['service_'.$i],$payfor_type);
						if(!empty($spd_id)){
							$where="id = $spd_id";
							$arr = array(
									'is_start'=>0
							);
							$this->update($arr,$where);
						}else{
							$spd_id=0;
						}
						// check balance
						$complete=1;
						if($data['subtotal_'.$i]-$data['paidamount_'.$i]>0){
							$complete=0;
							$status="មិនទាន់បង់";
						}else{
							$complete=1;
							$status="បង់រួច";
						}
						
						$this->_name='rms_student_paymentdetail';
						
						$_arr = array(
								'payment_id'	=>$payment_id,
								'service_id'	=>$data['service_'.$i],
								'payment_term'	=>$data['term_'.$i],
								'fee'			=>$data['price_'.$i],
								'qty'			=>$data['qty_'.$i],
								'discount_percent'	=>$data['discount_'.$i],
								'subtotal'		=>$data['subtotal_'.$i],
								'paidamount'	=>$data['paidamount_'.$i],
								'balance'		=>$data['subtotal_'.$i]-$data['paidamount_'.$i],
								'start_date'	=>$start_date,
								'validate'		=>$validate,
								'note'			=>$data['remark'.$i],
								'type'			=>$payfor_type,
								'is_parent'		=>$spd_id,
								'is_complete'   =>$complete,
								'comment'		=>$status,
								'user_id'		=>$this->getUserId(),
						);
						$this->insert($_arr);
				
					}
				
				}
				
				
			 //$db->commit();//if not errore it do....
			}catch (Exception $e){
				$db->rollBack();//អោយវាវិលត្រលប់ទៅដើមវីញពេលណាវាជួបErrore
				echo $e->getMessage();exit();
			}
					
	}
    function getAllStudentRegister($search=null){
    	$_db  = new Application_Model_DbTable_DbGlobal();
    	$user = $_db->getUserAccessPermission('sp.user_id');
    	$branch_id = $_db->getAccessPermission('sp.branch_id');
    	
    	$db=$this->getAdapter();
    	$sql=" SELECT sp.id,sp.receipt_number,
    			s.stu_code,
    			CONCAT(s.stu_khname,'-',s.stu_enname) as name,
    			s.sex,
    			(SELECT CONCAT(from_academic,'-',to_academic,'(',generation,')') FROM rms_tuitionfee WHERE rms_tuitionfee.id=sp.year) AS year,
    	        (SELECT en_name FROM rms_dept WHERE dept_id=sp.degree)AS degree,
		       (SELECT CONCAT(major_enname) FROM rms_major WHERE major_id=sp.grade ) AS grade,
		       
 		       sp.grand_total,sp.fine,sp.credit_memo,sp.deduct,sp.net_amount, sp.create_date ,
 		       (select CONCAT(first_name) from rms_users where rms_users.id = sp.user_id) as user,
 		       (select name_en from rms_view where type=10 and key_code = sp.is_void) as void,'បោះ.អាហារូ'
 			   FROM rms_student AS s,rms_student_payment AS sp WHERE  s.stu_id=sp.student_id $user $branch_id ";
    	$where=" ";
    	$from_date =(empty($search['start_date']))? '1': " sp.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sp.create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['adv_search']));
    		$s_where[]= " stu_code LIKE '%{$s_search}%'";
    		$s_where[]=" receipt_number LIKE '%{$s_search}%'";
    		//$s_where[]= " stu_khname LIKE '%{$s_search}%'";
    		$s_where[]= " stu_enname LIKE '%{$s_search}%'";
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
    		$where.=" AND sp.year=".$search['study_year'];
    	}
//     	if(!empty($search['time'])){
//     		$where.=" AND sp.time=".$search['time'];
//     	}
    	if(!empty($search['session'])){
    		$where.=" AND sp.session=".$search['session'];
    	}
    	if(!empty($search['grade_all'])){
    		$where.=" AND sp.grade=".$search['grade_all'];
    	}
    	if(!empty($search['user'])){
    		$where.=" AND sp.user_id=".$search['user'];
    	}
    	//$order=" ORDER By stu_id DESC ";
    	$order=" ORDER BY sp.id DESC";
//     	echo $sql.$where.$order;exit();
    	return $db->fetchAll($sql.$where.$order);
    }
    function getRegisterById($id){
    	$db=$this->getAdapter();
    	$sql=" SELECT s.stu_id,s.stu_code,sp.receipt_number,s.academic_year,s.stu_khname,s.stu_enname,s.sex,s.session,s.degree,s.grade,
		    	sp.payment_term,sp.tuition_fee,sp.discount_percent,sp.other_fee,sp.admin_fee,sp.total,sp.paid_amount,sp.is_void,
		    	sp.balance_due,sp.amount_in_khmer,sp.note,sp.student_type,sp.time,sp.end_hour,spd.start_date,spd.validate,spd.is_start,spd.is_parent
		    	FROM rms_student AS s,rms_student_payment AS sp ,rms_student_paymentdetail AS spd
		    	WHERE s.stu_id=sp.student_id AND sp.id=spd.payment_id AND sp.id=".$id;
    	$dbl = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbl->getAccessPermission(" sp.branch_id ");
    	return $db->fetchRow($sql);
    }
    function getAllGrade($grade_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT major_id As id,CONCAT(major_enname) As name FROM rms_major WHERE is_active=1 and major_enname!='' and dept_id=".$grade_id;
    	$order=' ORDER BY id DESC';
    	return $db->fetchAll($sql.$order);
    }
    function getPaymentTerm($generat,$payment_term,$grade){
    	$db = $this->getAdapter();
    	$_db  = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	$sql="SELECT tfd.id,tfd.tuition_fee FROM rms_tuitionfee AS tf,rms_tuitionfee_detail AS tfd WHERE tf.id = tfd.fee_id
    	 		AND tfd.fee_id = $generat AND tfd.class_id = $grade AND tfd.payment_term = $payment_term  $branch_id ";
    	 		
    	return $db->fetchRow($sql);
    }
    function getBalance($serviceid,$studentid,$type){
    	$db = $this->getAdapter();
//     	$sql="select rms_student_paymentdetail.id,rms_student_paymentdetail.validate,balance AS price_fee
//     	from rms_student_paymentdetail,rms_student_payment where rms_student_payment.id=rms_student_paymentdetail.payment_id
//     	and rms_student_paymentdetail.service_id=$serviceid and rms_student_payment.student_id=$studentid and is_complete=0 limit 1";
    	
    	$sql = "SELECT 
				  spd.id,
				  spd.start_date,
				  spd.validate,
				  spd.balance,
				  sp.year,
				  spd.payment_term,
				  sp.session
				FROM
				  rms_student_paymentdetail AS spd,
				  rms_student_payment AS sp
				WHERE sp.id = spd.payment_id 
				  AND spd.service_id = $serviceid 
				  AND sp.student_id = $studentid 
				  AND is_complete = 0 
				  AND spd.type = $type
    			limit 1
    		";
    	
    	$row=$db->fetchRow($sql);
    	if($row['balance'] > 0){
    	    $row['sms']='លុយជំពាក់ពីមុន';
    		return $row;
    	}else{
    		return $row;
    	}
    }
   
    function getAllYearsProgramFee(){
    	$db = $this->getAdapter();
    	$sql = "SELECT id,CONCAT(from_academic,'-',to_academic,'(',generation,')') AS years,(select name_en from rms_view where type=7 and key_code=time) as time FROM rms_tuitionfee
    	        WHERE `status`=1 GROUP BY from_academic,to_academic,generation,time ";
    	$order=' ORDER BY id DESC';
    	return $db->fetchAll($sql.$order);
    }
    
    function getAllYears(){
    	$db = $this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql = "SELECT 
				  id,
				  CONCAT(from_academic,'-',to_academic,'(',generation,')') AS years
				FROM 
					rms_tuitionfee 
				WHERE 
					`status` = 1 and is_finished=0 $branch_id  
				GROUP BY 
				  	from_academic,
				  	to_academic,
				  	generation
				";
    	$order=' ORDER BY id DESC';
    	
    	//echo $sql;exit();
    	
    	return $db->fetchAll($sql.$order);
    }
    
    function getAllYearsServiceFee(){
    	$db = $this->getAdapter();
    	$sql = "SELECT id,CONCAT(from_academic,'-',to_academic) AS years FROM rms_servicefee WHERE `status`=1";
    	$order=' ORDER BY id DESC';
    	return $db->fetchAll($sql.$order);
    }
//     
    function getPrefixByDegree($degree){
    	$db= $this->getAdapter();
    	$sql=" SELECT shortcut FROM `rms_dept` WHERE dept_id=$degree LIMIT 1";
    	return $db->fetchOne($sql);
    }
    public function getNewAccountNumber($dept_id){
    	$db = $this->getAdapter();
    	$length = '';
    	$pre = '';
    	$sql=" SELECT COUNT(stu_id) FROM rms_student_id WHERE 1  ";    	
//     	if($dept_id==4){//Kindergarten
//     		$sql=" SELECT COUNT(stu_id) FROM rms_student_id WHERE degree IN (4) and status=1 ";
//     		$pre = 'K';
//     	}else if($dept_id==1  || $dept_id==2 || $dept_id==3){
//     		$sql="SELECT COUNT(stu_id) FROM rms_student_id WHERE degree IN (1,2,3) and status=1 ";
//     		$pre = 'G';
//     	}else{
//     		$sql="SELECT COUNT(stu_id) FROM rms_student_id WHERE degree>4 and status=1 ";
//     		$pre = 'GE';
//     	}
    	$pre = $this->getPrefixByDegree($dept_id);
    	$pre.='';
    	
    	
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$length = strlen((int)$new_acc_no);
    	
    	for($i = $length;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    public function getRecieptNo(){
    	$db = $this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	$branch_id="";
    	
    	$sql="SELECT count(id)  FROM rms_student_payment where 1 $branch_id LIMIT 1 ";
    	$payment_no = $db->fetchOne($sql);
    	
    	$sql1="SELECT count(id)  FROM ln_income where 1 $branch_id LIMIT 1 ";
    	$income_no = $db->fetchOne($sql1);
    	
    	$sql2="SELECT count(id)  FROM rms_student_test where 1 and is_paid=1 $branch_id LIMIT 1 ";
    	$stu_test_no = $db->fetchOne($sql2); 

    	$sql3="SELECT count(id)  FROM rms_change_product where 1 LIMIT 1 ";
    	$change_product_no = $db->fetchOne($sql3);
    	
    	$new_acc_no= (int)$payment_no + (int)$income_no + (int)$stu_test_no + (int)$change_product_no + 1;
    	
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
    	$sql="SELECT s.stu_id As stu_id,CONCAT(s.stu_khname,'-',s.stu_enname) As name FROM rms_student AS s
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
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql="SELECT s.stu_id As stu_id,s.stu_code As stu_code FROM rms_student AS s
    	WHERE s.status=1 and s.is_subspend=0  $branch_id  ORDER BY stu_type DESC ";
    	return $db->fetchAll($sql);
    }
    //select general  old student by id
    
    function getAllGerneralOldStudentName(){
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql="SELECT s.stu_id As stu_id,CONCAT(s.stu_enname) as name FROM rms_student AS s
    	WHERE stu_enname!='' AND s.status=1 and s.is_subspend=0  $branch_id  ORDER BY stu_type DESC ";
    	return $db->fetchAll($sql);
    }
    //select general  old student by name
    
    function getGeneralOldStudentById($stu_id){
    	$db=$this->getAdapter();
    	$sql="SELECT s.*,
    		(SELECT scholarship_percent FROM rms_student_payment WHERE student_id=$stu_id AND is_void=0 ORDER BY id DESC LIMIT 1) AS scholarship_percent,
    		(SELECT scholarship_amount FROM rms_student_payment WHERE student_id=$stu_id AND is_void=0 ORDER BY id DESC LIMIT 1) AS scholarship_amount,
    		(SELECT sgh.group_id FROM `rms_group_detail_student` AS sgh WHERE sgh.stu_id = s.stu_id ORDER BY sgh.gd_id DESC LIMIT 1) as group_id
    			 FROM rms_student as s    
    		WHERE s.stu_id=$stu_id LIMIT 1";
    	return $db->fetchRow($sql);
    }
    ///select degree searching 
    function getDegree(){
    	$db=$this->getAdapter();
    	$sql="SELECT dept_id AS id,CONCAT(en_name,'-',kh_name) AS `name` FROM rms_dept  WHERE 1";
    	return $db->fetchAll($sql);
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
    
    function getGradeAllDept(){
    	$db=$this->getAdapter();
    	$sql="SELECT major_id AS id,CONCAT(major_enname ,' (',(select shortcut from rms_dept where rms_dept.dept_id=rms_major.dept_id LIMIT 1),')') AS `name` FROM rms_major where major_enname!='' AND is_active=1 order by dept_id,major_id ";
    	return $db->fetchAll($sql);
    }
    
    function getGradeAll(){
    	$db=$this->getAdapter();
    	$sql="SELECT major_id AS id,major_enname AS `name` FROM rms_major WHERE major_enname!='' AND is_active=1 AND major_enname!='' ";
    	return $db->fetchAll($sql);
    }
    function getAllDegree(){
    	$db=$this->getAdapter();
    	$sql="SELECT dept_id AS id,en_name AS `name` FROM rms_dept WHERE is_active=1 AND en_name!='' ";
    	return $db->fetchAll($sql);
    }
    function getAllDegreeGEP(){
    	$db=$this->getAdapter();
    	$sql="SELECT dept_id AS id,en_name AS `name` FROM rms_dept WHERE en_name!=''  AND is_active=1 ";
    	return $db->fetchAll($sql);
    }
    
    function getAllDegreeBac(){
    	$db=$this->getAdapter();
    	$sql="SELECT dept_id AS id,en_name AS `name` FROM rms_dept WHERE en_name!=''  AND is_active=1 ";
    	return $db->fetchAll($sql);
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
    
    function getAllSession(){
    	$db=$this->getAdapter();
    	$sql="SELECT key_code as id,name_en as name FROM rms_view WHERE `type`=4 AND `status`=1";
    	return $db->fetchAll($sql);
    }
    
    function getAllpaymentTerm(){
    	$db = $this->getAdapter();
    	$sql="select key_code as id , name_en as name from rms_view where type=6 and status=1 ";
    	return $db->fetchAll($sql);
    }
    
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
    	$sql = "SELECT 
				  p.service_id ,
				  p.`title`
				FROM
				  `rms_servicefee_detail` as sfd,
				  `rms_servicefee`  as sf,
				   rms_tuitionfee as tf,
				  `rms_program_name` as p
				WHERE `sf`.id = `sfd`.`service_feeid`
				  and sf.academic_year = tf.id	 
				  AND tf.`branch_id` = ".$this->getBranchId()."
				  AND p.`service_id`=sfd.`service_id`
				  or type=1
				GROUP BY service_id ";
    	//echo $sql;
    	return $db->fetchAll($sql);
    }
    
    function getAllProductName(){
    	$db = $this->getAdapter();
    	$sql="SELECT id,pro_name FROM `rms_product` WHERE STATUS=1 AND pro_name!='' ORDER BY pro_name";
    	return $db->fetchAll($sql);
    }
    
    public function getAllRoom(){
    	$db = $this->getAdapter();
    	$sql = "select room_id as id , room_name as name from rms_room where is_active=1 AND room_name!='' ";
    	return $db->fetchAll($sql);
    }
    
    function getServiceType($service_id){
    	$db = $this->getAdapter();
    	$sql="select type,pro_type from rms_program_name where service_id=$service_id";
    	return $db->fetchRow($sql);
    }
    
    public function getServiceFee($studentid,$serviceid,$termid){
    	$db=$this->getAdapter();
    	
    	$branch = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $branch->getAccessPermission();
    	
    	$sql="select rms_student_paymentdetail.id,rms_student_paymentdetail.validate,rms_student_paymentdetail.start_date,balance AS price_fee from rms_student_paymentdetail,rms_student_payment where rms_student_payment.id=rms_student_paymentdetail.payment_id and rms_student_paymentdetail.service_id=$serviceid and rms_student_payment.student_id=$studentid and is_complete=0 limit 1";
    	$row=$db->fetchRow($sql);
    	if($row['price_fee']>0){
	    	$row['sms']='លុយជំពាក់ពីមុន';
	    	return $row;
    	}
    	else{
	    	$sql="select sfd.price_fee from rms_servicefee_detail as sfd,rms_servicefee as sf where sfd.service_id=$serviceid 
	    		and sfd.payment_term=$termid  limit 1";
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
    		 s.is_stu_new,
    		 (SELECT sgh.group_id FROM `rms_group_detail_student` AS sgh WHERE sgh.stu_id = sp.`student_id` ORDER BY sgh.gd_id DESC LIMIT 1) as group_id
    		FROM
    		  	rms_student_payment as sp,
    		  	rms_student as s
    		where 
    			s.stu_id = sp.student_id
    			AND sp.id=$id ";
    	return $db->fetchRow($sql);
    }
    
    function getStudentPaymentDetailServiceByID($id){
    	$db=$this->getAdapter();
    	$sql="select * from rms_student_paymentdetail where payment_id=$id and type NOT IN(1,2)";
    	return $db->fetchAll($sql);
    }
    
    function getServiceOnlyByID($id){
    	$db=$this->getAdapter();
    	$sql="select * from rms_student_paymentdetail where payment_id=$id and type NOT IN(1,2,4)";
    	return $db->fetchAll($sql);
    }
    
    function getProductOnlyByID($id){
    	$db=$this->getAdapter();
    	$sql="select * from rms_student_paymentdetail where payment_id=$id and type NOT IN(1,2,3)";
    	return $db->fetchAll($sql);
    }
    
    function getStudentPaymentDetailRegisterByID($id){
    	$db=$this->getAdapter();
    	$sql="select * from rms_student_paymentdetail where payment_id=$id and type IN(1,2)";
    	return $db->fetchRow($sql);
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
    
	
	function getAllStudentTested(){
		$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$branch_id = $_db->getAccessPermission();
		$sql="select id,CONCAT(en_name,'-',kh_name)as name from rms_student_test where en_name!='' AND status=1 and register=0 $branch_id  ORDER BY id DESC ";
		return $db->fetchAll($sql);
	}
	
	
	function getStudentTestInfo($stu_test_id){
		$db=$this->getAdapter();
		$sql="select * from rms_student_test where id = $stu_test_id ";
		return $db->fetchRow($sql);
	}
	
	
	function getCreditMemoByStuId($stu_id){
		$db=$this->getAdapter();
		$sql="select id, total_amountafter from rms_creditmemo where student_id = $stu_id and type=0 ";
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
		 
		$sql = "Select 
    			  spd.id,
    			  spd.type,
    			  sp.scholarship_percent,
    			  sp.scholarship_amount,
				  sp.tuition_fee,
				  spd.fee,
				  spd.qty,
				  spd.subtotal,
				  spd.late_fee,
				  spd.extra_fee,
				  spd.discount_percent,
				  spd.discount_fix,
				  spd.paidamount,
				  spd.balance,
				  spd.note,
				  DATE_FORMAT(spd.start_date, '%d-%m-%Y') AS start_date ,
				  DATE_FORMAT(spd.validate, '%d-%m-%Y') AS validate ,
				  spd.is_start,
				  spd.is_parent ,
				  spd.is_complete,
				  sp.receipt_number,
				  DATE_FORMAT(sp.create_date, '%d-%m-%Y') AS create_date ,
				  sp.is_void,
				  s.stu_code,
				  s.stu_khname,
				  s.stu_enname,
				  p.title AS service_name,
				  (SELECT pg.name_kh FROM `rms_pro_category` AS pg WHERE pg.id = (SELECT pp.cat_id FROM `rms_product` AS pp WHERE pp.id = p.ser_cate_id LIMIT 1) LIMIT 1) AS product_category,
				  (SELECT major_enname FROM `rms_major` WHERE major_id=sp.grade LIMIT 1) As major_name,
				  (SELECT CONCAT(first_name) FROM rms_users WHERE rms_users.id = sp.user_id LIMIT 1) AS user,
				  (SELECT name_kh FROM rms_view  WHERE rms_view.type=6 AND key_code=spd.payment_term LIMIT 1) AS payment_term,
				  (select name_en from rms_view where type=10 and key_code=sp.is_void LIMIT 1) as void_status,
				  (select title from rms_program_type where rms_program_type.id=p.ser_cate_id AND p.type=2 LIMIT 1) service_cate                             
    			FROM 
    				rms_student_payment as sp,
    				rms_student_paymentdetail as spd,
    				rms_student as s,
    				rms_program_name as p
    			where 
    				s.stu_id = sp.student_id
    				AND sp.id=spd.payment_id 
    				AND p.service_id=spd.service_id and sp.student_id= $studentid ORDER BY create_date DESC,spd.type ASC";
			return $db->fetchAll($sql);
	}
	
	function getAllStartDateEndDate(){
		$db = $this->getAdapter();
		$sql="select id,start_date,end_date,note,CONCAT(note,'(',start_date,' to ',end_date,')') as name from rms_startdate_enddate ";
		return $db->fetchAll($sql);
	}
	
	function getStartDateEndDate($id){
		$db = $this->getAdapter();
		$sql="select start_date,end_date from rms_startdate_enddate where id = $id ";
		return $db->fetchRow($sql);
	}
	
}
