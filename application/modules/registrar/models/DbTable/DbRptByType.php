<?php

class Registrar_Model_DbTable_DbRptByType extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student_paymentdetail';
    
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    public function getBranchId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->branch_id;
    }
	function getIncomebyCategory($search){
		$db=$this->getAdapter();
		try{
	    	$_db = new Application_Model_DbTable_DbGlobal();
	    	$branch_id = $_db->getAccessPermission('s.branch_id');
	    	$user_level = $_db->getUserAccessPermission('s.user_id');
        	$from_date =(empty($search['start_date']))? '1': "s.create_date >= '".$search['start_date']." 00:00:00'";
	    	$to_date = (empty($search['end_date']))? '1': "s.create_date <= '".$search['end_date']." 23:59:59'";
	    	
	    	$sql=" SELECT 
				    i.title AS category_name,
				    i.type,
				    SUM(s.penalty) AS total_penalty,
				    SUM(s.credit_memo) AS credit_memo,
				    SUM(sp.paidamount) AS total_paidamount
				FROM 
					rms_items AS i,
					rms_itemsdetail AS d,
					rms_student_payment AS s,
					rms_student_paymentdetail AS sp
					
				WHERE i.id = d.items_id
					AND s.id = sp.payment_id
					AND d.id = sp.itemdetail_id ";
	    	
	    	$where = " AND ".$from_date." AND ".$to_date;
	    	
	    	if(!empty($search['branch_id']) AND $search['branch_id']>0){
	    		$where.=" AND s.branch_id = ".$search['branch_id'] ;
	    	}
	    	$where.=" GROUP BY i.id ";
	    	return $db->fetchAll($sql.$where);
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function getTotalPenalty($search){
	}
	function getIncomeDiscount($search){
		$db=$this->getAdapter();
// 			$_db = new Application_Model_DbTable_DbGlobal();
// 			$branch_id = $_db->getAccessPermission('s.branch_id');
// 			$user_level = $_db->getUserAccessPermission('s.user_id');
			$from_date =(empty($search['start_date']))? '1': "p.create_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': "p.create_date <= '".$search['end_date']." 23:59:59'";
			$sql="SELECT 
				(SELECT title FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=itemdetail_id LIMIT 1) AS item_name,
				SUM(sp.subtotal) subtotal,
				SUM(sp.extra_fee) extra_fee,
				SUM(sp.paidamount) paidamount,
				SUM(sp.discount_amount) discount_amount,
				SUM(sp.discount_percent) discount_percent
				
				FROM `rms_student_payment` AS p,
				     `rms_student_paymentdetail` AS sp
				WHERE p.id=sp.payment_id
				      AND p.status=1
				      AND p.is_void=0
				      AND sp.service_type=1  ";
	
			$where = " AND ".$from_date." AND ".$to_date;
	
			if(!empty($search['branch_id']) AND $search['branch_id']>0){
				$where.=" AND s.branch_id = ".$search['branch_id'] ;
			}
			$where.=" GROUP BY 
				p.branch_id,
				sp.discount_type,
				sp.itemdetail_id";
			return $db->fetchAll($sql.$where);
		
	}
}