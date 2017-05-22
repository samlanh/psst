<?php

class Registrar_Model_DbTable_DbStudentServicePayment extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student_payment';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    
    function getStudentPaymentStart($studentid,$service_id){
    	$db = $this->getAdapter();
    	$sql="select spd.id from rms_student_payment AS sp,rms_student_paymentdetail AS spd where
    		 sp.id=spd.payment_id and is_start=1 and service_id= $service_id and sp.student_id=$studentid limit 1 ";
//     	echo $sql;exit();
    	return $db->fetchOne($sql);
    }
    
    function getStudentExist($receipt,$studentid){
    	$db = $this->getAdapter();
    	$sql ="SELECT * FROM rms_student_payment WHERE student_id='".$studentid."' AND receipt_number= $receipt";
    	return $db->fetchRow($sql);
    }
    function setServiceToFinish($student_id,$service_id,$self_id){
    	$db = $this->getAdapter();
    	$sql=" select spd.id from rms_student_payment as sp,rms_student_paymentdetail as spd 
    			where spd.is_start = 1 and sp.id=spd.payment_id and sp.student_id = ".$student_id." and spd.service_id = ".$service_id." and spd.payment_id != ".$self_id;
    	//echo $sql;exit();
    	return $db->fetchOne($sql);
    }
    
	function addStudentServicePayment($data){
		//print_r($data);exit();
		$db = $this->getAdapter();//ស្ពានភ្ជាប់ទៅកាន់Data Base
		$db->beginTransaction();//ទប់ស្កាត់មើលការErrore , មានErrore វាមិនអោយចូល
		$receipt = new Registrar_Model_DbTable_DbRegister();
		$receipt_no = $receipt->getRecieptNo();
		
		//get id service ដែលយើងបង់ ដើម្បី update វាទៅ Finish រួចចាំ insert new service and new validate
		$finish = $this->setServiceToFinish($data['studentid'], $data['service'],0);
		if(!empty($finish)){
			$this->_name = "rms_student_paymentdetail";
			$array=array(
					'is_start' => 0,
					);
			$where = ' id='.$finish;
			$this->update($array, $where);
		}
		
		// សិក្សាពេល User ច្រលំចុច submit 2 ដង​​ អោយវាចូលតែ1		
		$rs = $this->getStudentExist($data['reciept_no'],$data['studentid']);
		if(!empty($rs)){
			return -1;
		}
		
		// សិក្សាពេល សិស្សជំពាក់ អោយវាទៅ update record នោះទៅជាបង់រួច
		if(!empty($data['balance_id'])){
			$this->_name = 'rms_student_paymentdetail';
			$ar = array(
					'is_complete'=>1,
					'comment'	 =>'បង់រួច',
					);
			$where = ' id = '.$data['balance_id'];
			$this->update($ar, $where);
		}
		$this->_name = 'rms_student_payment';
		try{
			$arr=array(
					'student_id'		=>$data['studentid'],
					'receipt_number'	=>$receipt_no,
					'year'				=>$data['study_year'],
					'tuition_fee'		=>$data['service_fee'],
					'discount_percent'	=>$data['discount'],
					'total_payment'		=>$data['total_payment'],
					'receive_amount'	=>$data['paid_amount'],
					'paid_amount'		=>$data['paid_amount'],
					'total'				=>$data['paid_amount'],
					'return_amount'		=>$data['return'],
					'balance_due'		=>$data['balance'],
					//'amount_in_khmer'	=>$data['char_price'],
					'note'				=>$data['other'],
					'time'				=>$data['time'],
					'payfor_type'		=>3 ,
					'create_date'		=>date("Y-m-d H:i:s"),
					'user_id'			=>$this->getUserId(),
				);
				$id = $this->insert($arr);
				
				// សិក្សា​ថា​តើមាន balance រឺអត់
				$this->_name='rms_student_paymentdetail';
				$balance = $data['total_payment'] - $data['paid_amount'];
				if($balance>0){
					$is_complete = 0;
					$comment = 'មិនទាន់បង់';
				}else{
					$is_complete = 1;
					$comment = 'បង់រួច';
				}
				
				$array = array(
						'payment_id'	=>$id,
						'type'			=>3,
						'service_id'	=>$data['service'],
						'payment_term'	=>$data['term'],
						'fee'			=>$data['service_fee'],
						'qty'			=>$data['qty'],
						'discount_percent'=>$data['discount'],
						'subtotal'		=>$data['total_payment'],
						'paidamount'	=>$data['paid_amount'],
						'balance'		=>$data['balance'],
						'start_date'	=>$data['start_date'],
						'validate'		=>$data['end_date'],
						'references'	=>'from service payment',
						'note'			=>$data['other'],
						'user_id'		=>$this->getUserId(),
						'is_complete'	=>$is_complete,
						'comment'		=>$comment,
						'is_parent'		=>$finish,
						);
			$this->insert($array);
	    	$db->commit();
		}catch (Exception $e){
			echo $e->getMessage();
			$db->rollBack();//អោយវាវិលត្រលប់ទៅដើមវីញពេលណាវាជួបErrore
		}
	}
	
	function updateStudentServicePayment($data){
		$db = $this->getAdapter();//ស្ពានភ្ជាប់ទៅកាន់Data Base
		$db->beginTransaction();//ទប់ស្កាត់មើលការErrore , មានErrore វាមិនអោយចូល
		
		// update service មុនទៅជាប្រើប្រាស់វិញសិន
		if(!empty($data['is_parent'])){
			$this->_name = "rms_student_paymentdetail";
			$arr = array(
					'is_start' => 1,
					);
			$where =" id = ".$data['is_parent'];
			$this->update($arr, $where);
		}
		
		//get id service ដែលយើងបង់ ដើម្បី update វាទៅ Finish រួចចាំ insert new service and new validate
		$finish = $this->setServiceToFinish($data['studentid'], $data['service'] , $data['id']);
		if(!empty($finish)){
			$this->_name = "rms_student_paymentdetail";
			$array=array(
					'is_start' => 0,
			);
			$where = ' id = '.$finish;
			$this->update($array, $where);
		}
		$this->_name = 'rms_student_payment';
		try{
			$arr=array(
				'student_id'		=>$data['studentid'],
				'receipt_number'	=>$data['reciept_no'],
				'year'				=>$data['study_year'],
				'tuition_fee'		=>$data['service_fee'],
				'discount_percent'	=>$data['discount'],
				'total_payment'		=>$data['total_payment'],
				'receive_amount'	=>$data['paid_amount'],
				'paid_amount'		=>$data['paid_amount'],
				'total'				=>$data['paid_amount'],
				'return_amount'		=>$data['return'],
				'balance_due'		=>$data['balance'],
				//'amount_in_khmer'	=>$data['char_price'],
				'note'				=>$data['other'],
				'time'				=>$data['time'],
				'payfor_type'		=>3 ,
				//'create_date'		=>date("Y-m-d H:i:s"),
				//'user_id'			=>$this->getUserId(),
			);
			$where =$this->getAdapter()->quoteInto("id=?", $data['id']);
			$this->update($arr,$where);
			  
			$this->_name='rms_student_paymentdetail';
			$balance = $data['total_payment'] - $data['paid_amount'];
			if($balance>0){
				$is_complete = 0;
				$comment = 'មិនទាន់បង់';
			}else{
				$is_complete = 1;
				$comment = 'បង់រួច';
			}
			$array = array(
					'type'			=>3,
					'service_id'	=>$data['service'],
					'payment_term'	=>$data['term'],
					'fee'			=>$data['service_fee'],
					'qty'			=>$data['qty'],
					'discount_percent'=>$data['discount'],
					'subtotal'		=>$data['total_payment'],
					'paidamount'	=>$data['paid_amount'],
					'balance'		=>$data['balance'],
					'start_date'	=>$data['start_date'],
					'validate'		=>$data['end_date'],
					'references'	=>'from service payment',
					'note'			=>$data['other'],
					'user_id'		=>$this->getUserId(),
					'is_complete'	=>$is_complete,
					'comment'		=>$comment,
					'is_parent'		=>$finish,
			);
			$where = ' payment_id = '.$data['id'];
			$this->update($array, $where);
    		$db->commit();
    		return true;
		}catch (Exception $e){
			echo $e->getMessage();
			$db->rollBack();//អោយវាវិលត្រលប់ទៅដើមវីញពេលណាវាជួបErrore
		}
	}
		
	function getServicePaymentDetail($id) {
		$db = $this->getAdapter();
		$sql="select * from rms_student_payment AS sp,rms_student_paymentdetail AS spd where
		sp.id=spd.payment_id and sp.id=$id";
		return $db->fetchAll($sql);
	}
		
    function getAllStudenTServicePayment($search){
    	$user=$this->getUserId();
    	$db=$this->getAdapter();
    	$sql="select sp.id,
			(select stu_code from rms_student where rms_student.stu_id=sp.student_id limit 1)AS code,
	    	(select CONCAT(stu_khname,' - ',stu_enname) from rms_student where rms_student.stu_id=sp.student_id limit 1)AS name,
	    	(select name_kh from rms_view where rms_view.type=2 and rms_view.key_code=(select sex from rms_student where rms_student.stu_id=sp.student_id limit 1) limit 1)AS sex,
	    	receipt_number,
	    	tuition_fee,sp.discount_percent,total_payment,receive_amount,balance_due,return_amount,create_date,
	    	(select CONCAT(last_name,' ',first_name) from rms_users where rms_users.id=sp.user_id) AS user
	    	from rms_student_payment as sp where 1 and
	    	(select type from rms_student_paymentdetail where rms_student_paymentdetail.payment_id=sp.id limit 1)=3 ";
    	
    	$from_date =(empty($search['start_date']))? '1': " sp.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sp.create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
		
    	$order=" ORDER BY id DESC ";
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " (select CONCAT(from_academic,'-',to_academic) from rms_servicefee where rms_servicefee.id=sp.year limit 1) LIKE '%{$s_search}%'";
    		$s_where[] = " receipt_number LIKE '%{$s_search}%'";
    		$s_where[] = " (select stu_code from rms_student where rms_student.stu_id=sp.student_id) LIKE '%{$s_search}%'";
    		$s_where[] = " (select CONCAT(stu_khname,stu_enname) from rms_student where rms_student.stu_id=sp.student_id) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['year'])){
    		$where.=" AND sp.year=".$search['year'];
    	}
    	if(!empty($search['user'])){
    		$where.=" AND sp.user_id=".$search['user'];
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    function getStudentServicePaymentByID($id){
    	$db=$this->getAdapter();
    	$sql="select * from rms_student_payment where id=".$id;
    	return $db->fetchRow($sql);
    }
    
    function getStudentServicePaymentDetailByID($id){
    	$db=$this->getAdapter();
    	$sql="select * from rms_student_paymentdetail where payment_id=".$id;
    	return $db->fetchRow($sql);
    }
    
    function getAllPaymentTerm($id){
    	$db=$this->getAdapter();
    	$sql="select * from rms_student_paymentdetail where payment_id=".$id;
    	return $db->fetchAll($sql);
    }
    
    
    
    function getAllGrade($grade_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT major_id As id,major_enname As name FROM rms_major WHERE dept_id=".$grade_id;
    	$order=' ORDER BY id DESC';
    	return $db->fetchAll($sql.$order);
    }
    function getPaymentTerm($generat,$payment_term,$grade){
    	$db = $this->getAdapter();
    	$sql="SELECT td.tuition_fee FROM rms_tuitionfee_detail AS td,`rms_tuitionfee` AS tu
    	WHERE tu.id= td.fee_id AND td.class_id=$grade AND td.payment_term=$payment_term AND tu.generation=$generat LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getPaymentGep($study_year,$levele,$payment_term){
    	$db = $this->getAdapter();
    	$sql="SELECT id,tuition_fee FROM rms_tuitionfee_detail WHERE fee_id=$study_year AND class_id=$levele AND payment_term=$payment_term LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getAllYears(){
    	$db = $this->getAdapter();
    	$sql = "SELECT id,CONCAT(from_academic,'-',to_academic) AS years FROM rms_tuitionfee WHERE `status`=1";
    	$order=' ORDER BY id DESC';
    	return $db->fetchAll($sql.$order);
    }
    function getAllYearsProgramFee(){
    	$db = $this->getAdapter();
    	$sql = "SELECT id,CONCAT(start_year,'-',end_year) AS years FROM mrs_program_fee WHERE `status`=1";
    	$order=' ORDER BY id DESC';
    	return $db->fetchAll($sql.$order);
    }
    public function getNewAccountNumber($type){
    	$db = $this->getAdapter();
    	$sql="  SELECT stu_id  FROM rms_student ORDER BY  stu_id DESC LIMIT 1 ";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	//echo $new_acc_no;exit();
    	$acc_no= strlen((int)$acc_no+1);
    	if($type==1){
    		$pre='K';
    	}
    	else if($type==2){
    		$pre='P';
    	}
    	else if($type==3){
    		$pre='S';
    	}else {
    		$pre='H';
    	}
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    public function getRecieptNo(){
    	$db = $this->getAdapter();
    	$sql="SELECT id  FROM rms_student_payment ORDER BY  id DESC LIMIT 1 ";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$pre=0;
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    
    public function getAllStudentCode(){
    	$db = $this->getAdapter();
    	$sql="SELECT stu_id as id,stu_code as code  FROM rms_student ORDER BY  stu_code DESC ";
    	return $db->fetchAll($sql);
    	
    }
    public function getAllStudentName(){
    	$db = $this->getAdapter();
    	$sql="SELECT stu_id as id,CONCAT(stu_khname,' - ',stu_enname) as name  FROM rms_student ORDER BY  stu_code DESC ";
    	return $db->fetchAll($sql);
    	 
    }
    public function getAllpriceByServiceTerm($studentid,$serviceid,$termid,$year){
    	$db=$this->getAdapter();
    	$sql="select spd.id,spd.validate,spd.start_date,balance from rms_student_paymentdetail as spd,rms_student_payment as sp where sp.id=spd.payment_id and spd.service_id=$serviceid and sp.student_id=$studentid and is_complete=0 limit 1";                               
    	$row=$db->fetchRow($sql);
    	if($row['balance']>0){
    		//$row['balance']='លុយជំពាក់ពីមុន';
    		return $row;
    	}
    	else{
    		$sql="select price_fee from rms_servicefee_detail where  rms_servicefee_detail.service_id=$serviceid and rms_servicefee_detail.payment_term=$termid and service_feeid=$year limit 1";
    		return $db->fetchRow($sql);
    	}
    }
    
    public function getAllpriceByServiceTermEdit($serviceid,$termid,$year){
    	$db=$this->getAdapter();
    	$sql="select price_fee from rms_servicefee_detail where  rms_servicefee_detail.service_id=$serviceid and rms_servicefee_detail.payment_term=$termid and service_feeid=$year limit 1";
    	return $db->fetchRow($sql);
    }
    
    public function getAllStudentInfo($studentid){
    	$db=$this->getAdapter();
    	$sql="select stu_enname,stu_khname,sex,
    		(select CONCAT(major_enname,' - ',major_khname) from rms_major where major_id=grade) as grade , 
    		(select name_en from rms_view where type=4 and key_code = session) as session from rms_student where stu_id=$studentid limit 1";
    	return $db->fetchRow($sql);
    }
    
    public function getStudentBalance($studentid,$serviceid,$termid){
    	$db=$this->getAdapter();
    	$sql="select stu_enname,stu_khname,sex from rms_student where stu_id=$studentid limit 1";
    	return $db->fetchRow($sql);
    }
    
    public function getAllService(){
    	$db=$this->getAdapter();
    	$sql="SELECT service_id as id , title as name  FROM rms_program_name WHERE type=2 and status=1";
    	return $db->fetchAll($sql);
    }
    public function getAllServiceType(){
    	$db=$this->getAdapter();
    	$sql="SELECT id , title as name  FROM rms_program_type WHERE status=1";
    	return $db->fetchAll($sql);
    }
    
    function getStudentID($acacemic,$type){
    	$db=$this->getAdapter();
    	if($type==1){
    		$sql="SELECT stu_id As id,stu_code As name  FROM rms_student  WHERE academic_year=$acacemic";
    	}else{
    		$sql="SELECT stu_id As id,CONCAT(stu_khname,' - ',stu_khname) As name  FROM rms_student  WHERE academic_year=$acacemic";
    	}
    	return $db->fetchAll($sql);
    }
    function getYearService(){
    	$db=$this->getAdapter();
    	$sql="SELECT id,CONCAT(from_academic,'-',to_academic,' (',generation,')') AS years FROM rms_servicefee 
							WHERE `status`= 1 GROUP BY from_academic,to_academic,generation ORDER BY id DESC";
    	return $db->fetchAll($sql);
    }
    function addService($data){
    	$this->_name="rms_program_name";
    	$db = $this->getAdapter();
    	$arr = array(
    			'ser_cate_id'=>$data['service_type'] ,
    			'title'=>$data['service_name'] ,
    			'description'=>$data['description'] ,
    			'status'=>$data['status_popup'] ,
    			'user_id'=>$this->getUserId() ,
    			'type'=>2 ,
    			'price'=>0 ,
    			'create_date'=>Zend_Date::now(),
    			);
    	return $this->insert($arr);
    }
}





