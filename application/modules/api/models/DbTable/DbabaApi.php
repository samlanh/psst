<?php

class Api_Model_DbTable_DbabaApi extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_banktransaction';
	function getStudentbyStuID($Stuid){
		$db = $this->getAdapter();
		$stuID=trim($Stuid);
		
		try{
			$sql =" SELECT
				s.stu_id AS id,
				CONCAT(COALESCE(s.stu_khname,''),COALESCE(s.stu_enname,''),' ',COALESCE(s.last_name,'')) AS payer
			FROM
				rms_student AS s
			WHERE s.status = 1 AND s.customer_type =1 ";
			$sql.= " AND ".$db->quoteInto('s.stu_code=?', $stuID);
			
			$row = $db->fetchRow($sql);
			
			if(!empty($row)){
				$data=array();
				$data['payer']=$row['payer'];
				$data['amount']='';
				$data['currency']='USD';
				$result = array(
					'status' =>200,
					'message' =>'Success',
					'data' =>$data,
				);
			}else{
				$result = array(
						'status' =>404,
						'message' =>'អត្តលេខសិស្សមិនត្រឹមត្រូវទេ',
				);
			}
			return $result;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return $this->returnServerError();
		}
	}
	function confirmPayment($data){
		$db = $this->getAdapter();
		$stuID=$data['id'];
		$amount=$data['amount'];
		$currency=$data['currency'];
		$bankTranID=$data['bank_transaction_id'];
		$bankChannel=$data['channel'];
		
	
		try{
			
			if(empty($bankTranID) OR empty($stuID) OR empty($amount)){
				return $this->returnBadURL();
			}
			
			$recordExist = $this->getBankTransactionByID($bankTranID);
				
			if($recordExist>0){
				$result = array(
						'status' =>404,
						'message' =>'The bank transaction is existing',
				);
			}else{
				$rsStudent = $this->getStudentByStuCode($stuID);//get student study history
				$feeID=0;$academic=0;$degree=0;$grade=0;
				if(!empty($rsStudent)){
					$feeID = $rsStudent['fee_id'];
					$academic = $rsStudent['academic_year'];
					$degree = $rsStudent['degree'];
					$grade = $rsStudent['grade'];
				}
				$arr= array(
						'stu_id'=>$stuID,
						'amount'=>$amount,
						'bank_transaction_id'=>$bankTranID,
						'currency'=>$currency,
						'date'=>date("Y-m-d H:i:s"),
						'channel'=>'ABA',
						'status'=>1,
						
						'fee_id'=>$feeID,
						'academic_year'=>$academic,
						'degree'=>$degree,
						'grade'=>$grade,
					);
				$id = $this->insert($arr);
				
				$data = array();
				$data['bank_transaction_id']=$bankTranID;
				$data['payment_reference']=$id;
				$data['currency']='USD';
				$result = array(
						'status' =>200,
						'message' =>'Success',
						'data' =>$data,
				);
			}
			return $result;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return $this->returnBadURL();
		}
	}
	
	function confirmPaymentStatus($bankTranID){
		$db = $this->getAdapter();
		try{
			$recordExist = $this->getBankTransactionByID($bankTranID);
	
			if($recordExist>0){
				$data = array();
				$data['bank_transaction_id']=$bankTranID;
				$data['payment_reference']=$recordExist;
				
				$result = array(
						'status' =>200,
						'message' =>'Success',
						'data' =>$data,
				);
			}else{
				$result = array(
						'status' =>400,
						'message' =>'Fail reason',
				);
	
			}
			return $result;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return $this->returnBadURL();
		}
	}
	
	function getStudentByStuCode($stuCode){
		$db= $this->getAdapter();
		$sql="SELECT 				s.stu_id,
	   			sgd.grade,
	   			sgd.degree,
	   			sgd.grade,
	   			sgd.degree,
			   	(SELECT title FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=sgd.grade LIMIT 1) as grade_label,
				(SELECT title FROM `rms_items` WHERE rms_items.id=sgd.degree LIMIT 1) as degree_label, 		
		   		(SELECT tf.academic_year FROM rms_tuitionfee AS tf WHERE tf.id=(SELECT fee_id FROM `rms_student_fee_history` AS sf WHERE sf.student_id=s.stu_id AND sf.is_current=1 LIMIT 1) LIMIT 1) AS academic_year,
		   		(SELECT sf.fee_id FROM `rms_student_fee_history` AS sf WHERE sf.student_id=s.stu_id AND sf.is_current=1 LIMIT 1) AS fee_id
				
	   		FROM 
	   			rms_student AS s
	   			LEFT JOIN rms_group_detail_student AS sgd
	   			ON s.stu_id=sgd.stu_id
   			WHERE 
	   			sgd.is_current=1 AND sgd.is_maingrade=1  ";
		$sql.= " AND ".$db->quoteInto('s.stu_code=?', $stuCode);
		$sql.=" LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getBankTransactionByID($bankTranID){
		$db= $this->getAdapter();
			$sql ="SELECT
				bt.id
			FROM
				rms_banktransaction AS bt
					WHERE bt.status = 1 ";
			$sql.= " AND ".$db->quoteInto('bt.bank_transaction_id=?', $bankTranID);
			return $db->fetchOne($sql);
	}
	
	function returnBadURL(){
		$result = array(
				'status' =>400,
				'message' =>'bad request',
		);
		return $result;
	}
	function returnNoFoundURL(){
		$result = array(
				'status' =>404,
				'message' =>'not found',
		);
		return $result;
	}
	function returnServerError(){
		$result = array(
				'status' =>500,
				'message' =>'internal server error',
		);
		return $result;
	}
	
}