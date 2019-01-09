<?php

class Allreport_Model_DbTable_DbRptStudentPaymentLate extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('authstu');
//     	return $session_user->user_id;
    	 
//     }
//     function submitlatePayment($data){
//     	$ids = explode(',', $data['identity']);
//     	$key = 1;
//     	$this->_name='rms_student_paymentdetail';
//     	foreach ($ids as $i){
//     		$arr = array(
//     				'is_onepayment'=>$data['onepayment_'.$i],
//     				'start_date'=>$data['create_date'.$i],
//     				'validate'=>$data['end_date'.$i],
//     			);
//     		$where="id = ".$data['payment_id'.$i];
//     		$this->update($arr, $where);
//     	}
//     }
    
}
   
    
   