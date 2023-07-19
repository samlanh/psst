<?php

class Registrar_Model_DbTable_DbPayment extends Zend_Db_Table_Abstract
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
	
	public function getListStudentForPayment($search){
		$_db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label="name_en";
		$branch = "branch_nameen";
		$field = 'name_en';
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
			$field = 'name_kh';
			$branch = "branch_namekh";
		}
		$sql ="SELECT  
					s.*,
					CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stu_name,
					(SELECT $label FROM `rms_view` WHERE type=2 AND key_code = s.sex LIMIT 1) AS sexTitle,
					CASE
						WHEN primary_phone = 1 THEN s.tel
						WHEN primary_phone = 2 THEN s.father_phone
						WHEN primary_phone = 3 THEN s.mother_phone
						ELSE s.guardian_tel
					END as tel,
					ds.stop_type AS is_subspend,
					CASE
						WHEN s.customer_type = 1 THEN '".$tr->translate("Existing Student")."'
						WHEN s.customer_type = 2 THEN '".$tr->translate("Cutomer")."'
						WHEN s.customer_type = 3 THEN '".$tr->translate("CRM")."'
						WHEN s.customer_type = 4 THEN '".$tr->translate("Student Test")."'
					END as typeStudent,
		
					(SELECT $field from rms_view where type=5 and key_code=ds.stop_type LIMIT 1) as status_student,
					(SELECT group_code FROM `rms_group` WHERE rms_group.id=ds.group_id AND ds.is_maingrade=1 LIMIT 1) AS group_name,
					(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = ds.degree AND i.type=1 AND ds.is_maingrade=1 LIMIT 1) AS degree,
					(SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = ds.grade AND idd.items_type=1 AND ds.is_maingrade=1 LIMIT 1) AS grade,
					ds.group_id,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=ds.academic_year LIMIT 1) AS academic_year
				FROM rms_student AS s,
					rms_group_detail_student AS ds
				  WHERE  
				   ds.itemType=1
				   AND ds.is_maingrade=1 
				   AND ds.is_current=1 
				   AND s.stu_id=ds.stu_id 
				   AND s.status = 1 
				 ";
		$where = " ";
		$orderby = " ORDER BY s.stu_khname ASC ";
		
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[]=" REPLACE(stu_code,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(stu_khname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(stu_enname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(last_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" CONCAT(last_name,stu_enname) LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(tel,' ','')  			LIKE '%{$s_search}%'";
			
			$s_where[]=" REPLACE(father_phone,' ','')  	LIKE '%{$s_search}%'";
			
			$s_where[]=" REPLACE(mother_phone,' ','')  	LIKE '%{$s_search}%'";
			
			$s_where[]=" REPLACE(guardian_tel,' ','')  	LIKE '%{$s_search}%'";
			
			
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.=" AND s.branch_id=".$search['branch_id'];
		}
		if(!empty($search['study_year'])){
			$where.=" AND ds.academic_year=".$search['study_year'];
		}
		if(!empty($search['group'])){
			$where.=" AND ds.group_id=".$search['group'];
		}
		if(!empty($search['degree'])){
			$where.=" AND ds.degree=".$search['degree'];
		}
		if(!empty($search['grade_all'])){
			$where.=" AND ds.grade=".$search['grade_all'];
		}
		if(!empty($search['session'])){
			$where.=" AND ds.session=".$search['session'];
		}
		if($search['customer_type']>-1){
			$where.=" AND s.customer_type=".$search['customer_type'];
		}
		if($search['study_status']>=0){
			$where.=' AND (SELECT rms_group.is_pass FROM `rms_group` WHERE rms_group.id=ds.group_id AND ds.is_maingrade=1 LIMIT 1) ='.$search['study_status'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission("s.branch_id");
		$row = $_db->fetchAll($sql.$where.$orderby);
		
		$rsCus = $this->getListCustomerForPayment($search);
		if(!empty($rsCus)){
			$row = array_merge($row, $rsCus);
			//array_unshift($row, $row);
		}
		
		return $row;
	}
	
	public function getListCustomerForPayment($search){
		$_db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label="name_en";
		$branch = "branch_nameen";
		$field = 'name_en';
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
			$field = 'name_kh';
			$branch = "branch_namekh";
		}
		$sql ="SELECT  
					s.*,
					CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stu_name,
					(SELECT $label FROM `rms_view` WHERE type=2 AND key_code = s.sex LIMIT 1) AS sexTitle,
					s.tel,
					'' AS status_student,
					CASE
						WHEN s.customer_type = 1 THEN '".$tr->translate("Existing Student")."'
						WHEN s.customer_type = 2 THEN '".$tr->translate("Cutomer")."'
						WHEN s.customer_type = 3 THEN '".$tr->translate("CRM")."'
						WHEN s.customer_type = 4 THEN '".$tr->translate("Student Test")."'
					END as typeStudent
				FROM rms_student AS s
				WHERE  
				    s.status = 1 
				   AND s.customer_type=2
				 ";
		$where = " ";
		$orderby = " ORDER BY s.stu_khname ASC ";
		
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[]=" REPLACE(stu_code,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(stu_khname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(stu_enname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(last_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" CONCAT(last_name,stu_enname) LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(tel,' ','')  			LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(father_phone,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(mother_phone,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(guardian_tel,' ','')  	LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['customer_type']>-1){
			$where.=" AND s.customer_type=".$search['customer_type'];
		}
		return $_db->fetchAll($sql.$where.$orderby);
	}
	
	public function getStudentForPaymentById($stuId){
		$_db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label="name_en";
		$branch = "branch_nameen";
		$field = 'name_en';
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
			$field = 'name_kh';
			$branch = "branch_namekh";
		}
		$sql ="SELECT  
					s.*,
					CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stu_name,
					(SELECT $label FROM `rms_view` WHERE type=2 AND key_code = s.sex LIMIT 1) AS sexTitle,
					CASE
						WHEN primary_phone = 1 THEN s.tel
						WHEN primary_phone = 2 THEN s.father_phone
						WHEN primary_phone = 3 THEN s.mother_phone
						ELSE s.guardian_tel
					END as parentTel,
					ds.stop_type AS is_subspend,
					CASE
						WHEN s.customer_type = 1 THEN s.stu_code
						WHEN s.customer_type = 2 THEN s.serial
						WHEN s.customer_type = 3 THEN s.serial
						WHEN s.customer_type = 4 THEN s.serial
					END as stuCodeInfo,
					CASE
						WHEN s.customer_type = 1 THEN '".$tr->translate("Existing Student")."'
						WHEN s.customer_type = 2 THEN '".$tr->translate("Cutomer")."'
						WHEN s.customer_type = 3 THEN '".$tr->translate("CRM")."'
						WHEN s.customer_type = 4 THEN '".$tr->translate("Student Test")."'
					END as typeStudent,
		ds.feeId AS currentFeeId,
					(SELECT $field from rms_view where type=5 and key_code=ds.stop_type LIMIT 1) as status_student,
					(SELECT group_code FROM `rms_group` WHERE rms_group.id=ds.group_id AND ds.is_maingrade=1 LIMIT 1) AS group_name,
					(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.id = ds.degree AND i.type=1 AND ds.is_maingrade=1 LIMIT 1) AS degree,
					(SELECT idd.$colunmname FROM `rms_itemsdetail` AS idd WHERE idd.id = ds.grade AND idd.items_type=1 AND ds.is_maingrade=1 LIMIT 1) AS grade,
					
					ds.group_id,
					ds.degree AS degree_id,
					ds.grade AS grade_id,
					
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=ds.academic_year LIMIT 1) AS academic_year,
					(SELECT sb.id FROM `rms_student_balance` AS sb WHERE ds.gd_id = sb.group_detail_id AND s.stu_id = sb.stu_id AND sb.status=1 AND sb.is_balance=1 LIMIT 1  ) AS studentBalanceId,
					(SELECT sb.group_detail_id FROM `rms_student_balance` AS sb WHERE ds.gd_id = sb.group_detail_id AND s.stu_id = sb.stu_id AND  sb.status=1 AND sb.is_balance=1 LIMIT 1  ) AS groupDetailId,
					
					COALESCE((SELECT id FROM rms_creditmemo WHERE student_id = s.stu_id AND total_amountafter>0 LIMIT 1),0) AS creditMemoId,
					COALESCE((SELECT total_amountafter FROM rms_creditmemo WHERE student_id = s.stu_id AND total_amountafter>0 LIMIT 1),0) AS totalAmountAfter,
					COALESCE((SELECT SUM(sp.balance_due) FROM rms_student_payment AS sp WHERE sp.student_id=s.stu_id LIMIT 1 ),0) AS studentBalancePayment
				  
					
				FROM rms_student AS s,
					rms_group_detail_student AS ds
				  WHERE  
				   ds.itemType=1
				   AND ds.is_maingrade=1 
				   AND ds.is_current=1 
				   AND s.stu_id=ds.stu_id 
				   AND s.status = 1 
				   AND s.stu_id = $stuId
				 ";
		$where = " LIMIT 1";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission("s.branch_id");
		return $_db->fetchRow($sql.$where);
		
	}
	public function getCustomerForPaymentById($stuId){
		$_db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label="name_en";
		$branch = "branch_nameen";
		$field = 'name_en';
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
			$field = 'name_kh';
			$branch = "branch_namekh";
		}
		$sql ="SELECT  
					s.*,
					CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stu_name,
					(SELECT $label FROM `rms_view` WHERE type=2 AND key_code = s.sex LIMIT 1) AS sexTitle,
					CASE
						WHEN primary_phone = 1 THEN s.tel
						WHEN primary_phone = 2 THEN s.father_phone
						WHEN primary_phone = 3 THEN s.mother_phone
						ELSE s.guardian_tel
					END as parentTel,
					'0' AS is_subspend,
					CASE
						WHEN s.customer_type = 1 THEN s.stu_code
						WHEN s.customer_type = 2 THEN s.serial
						WHEN s.customer_type = 3 THEN s.serial
						WHEN s.customer_type = 4 THEN s.serial
					END as stuCodeInfo,
					CASE
						WHEN s.customer_type = 1 THEN '".$tr->translate("Existing Student")."'
						WHEN s.customer_type = 2 THEN '".$tr->translate("Cutomer")."'
						WHEN s.customer_type = 3 THEN '".$tr->translate("CRM")."'
						WHEN s.customer_type = 4 THEN '".$tr->translate("Student Test")."'
					END as typeStudent,
		
					'0' AS currentFeeId,
					'N/A' as status_student,
					'N/A' AS group_name,
					'N/A' AS degree,
					'N/A' AS grade,
					
					'0' AS group_id,
					'0' AS degree_id,
					'0' AS grade_id,
					
					'N/A' AS academic_year,
					'0' AS studentBalanceId,
					'0' AS groupDetailId,
					
					COALESCE((SELECT id FROM rms_creditmemo WHERE student_id = s.stu_id AND total_amountafter>0 LIMIT 1),0) AS creditMemoId,
					COALESCE((SELECT total_amountafter FROM rms_creditmemo WHERE student_id = s.stu_id AND total_amountafter>0 LIMIT 1),0) AS totalAmountAfter,
					COALESCE((SELECT SUM(sp.balance_due) FROM rms_student_payment AS sp WHERE sp.student_id=s.stu_id LIMIT 1 ),0) AS studentBalancePayment
				  
					
				FROM rms_student AS s
				  WHERE  
				    s.customer_type = 2
				   AND s.status = 1 
				   AND s.stu_id = $stuId
				 ";
		$where = " LIMIT 1";
		return $_db->fetchRow($sql.$where);
		
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

    
	function addRegister($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$paid_date = date("Y-m-d H:i:s");
		
		
		
		//$receipt_number = $this->getRecieptNo($data['branch_id']);
		
		$DbRegister = new Registrar_Model_DbTable_DbRegister();
		$stu_id = $data['old_stu'];
		$receipt_number = $DbRegister->getRecieptNo($data['branch_id']);
		
			try{
				$gdb = new  Application_Model_DbTable_DbGlobal();
				$customer_type=1;
				if($data['student_type']==1){//existing student
					$rs_stu = $gdb->getStudentinfoGlobalById($stu_id);
					
					if($rs_stu['is_setstudentid']==0 AND !empty($data['student_code'])){
						$arr = array(
							'stu_code'=>$data['student_code'],
							'is_setstudentid'=>1,
						);
						$this->_name='rms_student';
						$where="stu_id = ".$stu_id;
						$this->update($arr, $where);
					}
					
				}elseif($data['student_type']==2){//testing student
					$rs_stu = $gdb->getStudentTestinfoById($stu_id);
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
						$this->insert($_arr);
					}
					
				}elseif($data['student_type']==3){//from crm
					$rs_stu = $gdb->getStudentinfoGlobalById($stu_id);
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
				}elseif($data['student_type']==4){//សិស្សនៅមិនទាន់ទូទាត់ ថ្នាក់សិក្សាចាស់
					
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
					'paystudent_type'=> $rs_stu['is_stu_new'],
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
					$this->_name="rms_student_paymentdetail";
					$studentpaymentid = $this->insert($_arr);

			
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
				$rs_stu = $gdb->getStudentinfoGlobalById($stu_id);
				$rs_stu['receipt_number'] = $receipt_number;
				return $rs_stu;
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();//
			
		}
	}
	
	
	
	
}
