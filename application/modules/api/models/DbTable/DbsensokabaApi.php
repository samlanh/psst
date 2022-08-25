<?php

class Api_Model_DbTable_DbsensokabaApi extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_banktransaction';
	function getStudentbyStuID($Stuid,$tokendReq){
		$db = $this->getAdapter();
		$stuID=trim($Stuid);
		
		try{
			$TokenStr = $this->getStaticTokend(1);
			if($tokendReq!=$TokenStr){
				return $this->returnBadURL();
			}
			$sql =" SELECT
				s.stu_id AS id,
				CONCAT(COALESCE(s.stu_khname,''),' ',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS payer
			FROM
				rms_student AS s,
				rms_group_detail_student AS sgd
			WHERE 
			sgd.mainType=1 
			AND s.status = 1 
			AND s.stu_id=sgd.stu_id
			AND sgd.is_current=1
			AND sgd.is_maingrade=1 ";
			$sql.= " AND (".$db->quoteInto('s.stu_code=?', $stuID);
			$sql.= " OR ".$db->quoteInto('s.serial=?', $stuID);
			$sql.=" ) LIMIT 1";
			
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
					'message' =>'Student record not found',
				);
			}
			return $result;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return $this->returnServerError();
		}
	}
	function getStaticTokend($tokenID){
		$tokendStr = array(
				1=>'DChcSEryAhUNr5eWDKsYABAAGgJ0bA',
				2=>'Z3qBkFmrdvsOuMK_EQsdPE3aJGxoE3',
				3=>'AINFCbYAAAAAYQPPR-vADRaZxg1N2'
			);
		return $tokendStr[$tokenID];
	}
	function confirmPayment($data){
		$db = $this->getAdapter();
		$stuID=empty($data['id'])?'':$data['id'];
		$amount=empty($data['amount'])?'':$data['amount'];
		$currency=empty($data['currency'])?'':$data['currency'];
		$bankTranID=empty($data['bank_transaction_id'])?:$data['bank_transaction_id'];
		$bankChannel=empty($data['channel'])?'':$data['channel'];
		$tokendReq = empty($data['token'])?'':$data['channel'];
		
		try{
			$TokenStr = $this->getStaticTokend(2);
			
			if($tokendReq!=$TokenStr){
				return $this->returnBadURL();
			}
			if(empty($bankTranID) OR empty($stuID) OR empty($amount) OR empty($currency) OR $amount<0){
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
					$stuID = $rsStudent['stu_id'];
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
				
				$this->sendMessagetoTeleagrame($rsStudent);
				
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
			return $this->returnServerError();
		}
	}
	
	function confirmPaymentStatus($bankTranID,$tokendReq){
		$db = $this->getAdapter();
		try{
			$TokenStr = $this->getStaticTokend(3);
			if($tokendReq!=$TokenStr){
				return $this->returnBadURL();
			}
			
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
						'status' =>404,
						'message' =>'Fail reason',
				);
	
			}
			return $result;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return $this->returnServerError();
		}
	}
	
	function getStudentByStuCode($stuCode){
		$db= $this->getAdapter();
		$sql="SELECT 				
				s.stu_id,
				CONCAT(COALESCE(s.stu_khname,''),' ',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS payer,
				s.stu_code,
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
				sgd.mainType=1 
	   			AND sgd.is_current=1 
				AND sgd.is_maingrade=1  ";
		$sql.= " AND ( ".$db->quoteInto('s.stu_code=?', $stuCode);
		$sql.= "  OR ".$db->quoteInto('s.serial=?', $stuCode);
		$sql.=" ) LIMIT 1";
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
	function sendMessagetoTeleagrame($studentInfo) {
		$token='1971785720:AAG46O8Q6venZRO-GfEmVcB5KzU_YMHiQa4';
		$studentInfo=array(
				'tran'=>'10000230303',
				'amt'=>10.5,
				'stu_code'=>'P0012',
				'payer'=>' ម៉ក់ ចន្នី Mork Channy',
				);
		$chatID ='419707100';//419707100,483545634
		$stu_Id =$studentInfo['stu_code'];
		$stu_name=$studentInfo['payer'];
		$amt=$studentInfo['payer'];
		$tranid=$studentInfo['tran'];
		$date =  date("Y-m-d H:i:s");
		
		$data = array(
			    'text' => "Received Payment From ABA
				StuId:$stu_Id
				Student Name:$stu_name
				យោង/Trn:$tranid
				ថ្ងៃប្រតិ./TrnDate:$date
				ទឹកប្រាក់/Amt:$amt");
		
		$ids = explode(',', $chatID);
		
		if(!empty($ids))foreach ($ids as $chatIDuser){
			print_r($this->getUserIdstatus());exit();
			//$data['chat_id']=$chatIDuser;
// 			print_r($data);
			//echo"https://api.telegram.org/bot$token/sendMessage?" . http_build_query($data);
			//echo"<br /><br /><br /><br />";
			  //$result = file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query($data),true);
			//$result = file_get_contents('https://api.telegram.org/bot1971785720:AAG46O8Q6venZRO-GfEmVcB5KzU_YMHiQa4/sendMessage?chat_id=483545634&text=hello');
			 // print_r($result);exit();
		}
		
	}
	function getUserIdstatus(){
		$response = http_get("http://www.example.com/file.xml");
		return $response;
	}
}