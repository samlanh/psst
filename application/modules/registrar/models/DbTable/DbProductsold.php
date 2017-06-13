<?php

class Registrar_Model_DbTable_DbProductsold extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    function getProductSold($search){
    	$db = $this->getAdapter();
    	$sql="SELECT sd.id,
    	 	 (SELECT branch_nameen FROM `rms_branch` WHERE br_id=sp.branch_id LIMIT 1) AS branch_name,
			 (SELECT stu_enname FROM `rms_student` WHERE stu_id=sp.student_id LIMIT 1) AS stu_enname,
			 (SELECT stu_code FROM `rms_student` WHERE stu_id=sp.student_id LIMIT 1) AS stu_code,
			 sp.receipt_number,
			 sd.qty,
			 (SELECT p.pro_name FROM `rms_product` AS p WHERE p.id=sd.pro_id) AS pro_name,
			 sp.note,
			 (SELECT u.first_name FROM `rms_users` as u WHERE sp.user_id =u.id ) as user_name
			  FROM `rms_saledetail` AS sd,rms_student_payment AS sp
			 WHERE sp.id=sd.payment_id ";
    	return $db->fetchAll($sql);
    }	
}



