<?php

class Accounting_Model_DbTable_DbServiceCharge extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAceYear(){
    	$db=$this->getAdapter();
    	$sql="SELECT id,CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') AS `name`
    	FROM rms_tuitionfee as tf WHERE tf.`status`=1 ";
    	$oder=" ORDER BY id DESC ";
    	return $db->fetchAll($sql.$oder);
    }
    function getAllServiceFee($search){
	    try{
	    	
		    $dbp = new Application_Model_DbTable_DbGlobal();
			$currentLang = $dbp->currentlang();
			
			$branch = $dbp->getBranchDisplay();
			$field = 'name_en';
	    	if ($currentLang==1){
	    		$field = 'name_kh';
	    	}
			
		    $db=$this->getAdapter();
	    	$sql = "
				SELECT 
					t.id
					,(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id =t.branch_id LIMIT 1) as branch
					,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM rms_academicyear AS ac WHERE ac.id=t.academic_year LIMIT 1) AS academic
					,t.create_date
					,(SELECT v.$field FROM rms_view AS v WHERE v.type=12 and v.key_code=t.is_finished LIMIT 1) as is_finished
					,(SELECT u.first_name FROM rms_users AS u WHERE u.id = t.user_id LIMIT 1) as user ";
	    	
	    	$sql.=$dbp->caseStatusShowImage("t.status");
	    	$sql.=" FROM `rms_tuitionfee` AS t WHERE t.type=2 ";
	    	
	    	$where =" ";
    	 
	    	if(!empty($search['title'])){
	    		$s_where = array();
	    		$s_search = addslashes(trim($search['title']));
	    		$s_where[] = " t.generation LIKE '%{$s_search}%'";
	    		$where .=' AND ( '.implode(' OR ',$s_where).')';
	    	}
	    	if(!empty($search['year'])){
	    		$where.=" AND t.academic_year=".$search['year'];
	    	}
	    	if(!empty($search['branch_id'])){
	    		$where.=" AND t.branch_id=".$search['branch_id'];
	    	}    	 
	    	if($search['is_finished_search']!=""){
	    		$where.=" AND t.is_finished=".$search['is_finished_search'];
	    	}
	    	if($search['status']>-1){
	    		$where.=" AND t.status=".$search['status'];
	    	}
	    	$where.=$dbp->getAccessPermission();
	    	 
	    	$order=" GROUP BY t.branch_id,t.academic_year,t.generation ORDER BY t.id DESC  ";
	    	return $db->fetchAll($sql.$where.$order);
	    }catch(Exception $e){
	    	Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    }
    }    
    function getCondition($_data){
    	$db = $this->getAdapter();
    	$find="SELECT id from rms_tuitionfee where type=2 AND branch_id='".$_data['branch_id']."' AND academic_year =".$_data['from_academic'];
    	return $db->fetchOne($find);
    }
    
    public function addServiceCharge($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	$service_id = $this->getCondition($_data);
    	try{   	
    		if(!empty($service_id)){
    		}else{
	    		$_arr = array(
    				'branch_id'=>$_data['branch_id'],
    				'academic_year'=>$_data['from_academic'],
    				'note'=>$_data['note'],
    				'type'=>2,
    				'status'=>1,
    				'create_date'=>date('Y-m-d'),
    				'user_id'=>$this->getUserId()
	    		);
	    		$service_id = $this->insert($_arr);
	    		
	    		$this->_name='rms_tuitionfee_detail';
	    		$ids = explode(',', $_data['identity']);
	    		$id_term =explode(',', $_data['iden_term']);
	    		foreach ($ids as $i){
	    			foreach ($id_term as $j){
	    				$_arr = array(
    						'fee_id'=>$service_id,
    						'class_id'=>$_data['class_'.$i],
    						'payment_term'=>$j,
    						'tuition_fee'=>$_data['fee'.$i.'_'.$j],
    						'remark'=>$_data['remark'.$i]
	    				);
	    				$this->insert($_arr);
	    			}
	    		}
    		}
    	    $db->commit();
    	    return true;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		return false;
    	}
    }
    public function updateServiceCharge($_data){
    $db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$_arr = array(
    				'academic_year'	=>$_data['from_academic'],
    				'note'			=>$_data['note'],
    				'status'		=>$_data['status'],
    				'type'=>2,
    				'is_finished'	=>$_data['is_finished'],
    				'branch_id'		=>$_data['branch_id'],
    				'user_id'		=>$this->getUserId()
    		);
    		$where=$this->getAdapter()->quoteInto("id=?", $_data['id']);
    		$this->update($_arr, $where);
    		if($_data['is_finished']==1){
    			$db->commit();
    			return true;
    		}
    		if($_data['is_finished']==0){
    			$this->_name='rms_tuitionfee_detail';
    			$where = "fee_id = ".$_data['id'];
    			$this->delete($where);
    			$ids = explode(',', $_data['identity']);
    			$id_term =explode(',', $_data['iden_term']);
    			foreach ($ids as $i){
    				foreach ($id_term as $j){
    					$_arr = array(
    						'fee_id'=>$_data['id'],
    						'class_id'=>$_data['class_'.$i],
    						'payment_term'=>$j,
    						'tuition_fee'=>$_data['fee'.$i.'_'.$j],
    						'remark'=>$_data['remark'.$i],
    					);
    					$this->insert($_arr);
    				}
    			}
    		}
    		$db->commit();
    		return true;
    	}catch (Exception $e){
    		$db->rollBack();
    		 Application_Form_FrmMessage::message("INSERT_FAIL");
    		 Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    public function getServiceChargeById($service_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM rms_tuitionfee WHERE type=2 AND id=$service_id ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	$sql.=" LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getServiceFeebyId($service_id){    	
    	$db = $this->getAdapter();	
    	$sql = "SELECT *
    		FROM rms_tuitionfee_detail WHERE fee_id = ".$service_id." ORDER BY id ";
    	return $db->fetchAll($sql);    	 
    }    
   
    function getAllBranch(){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
		$branch = $_db->getBranchDisplay();
		
    	$branch_id = $_db->getAccessPermission('br_id');
    	$sql="SELECT br_id as id, CONCAT($branch) as name from rms_branch where status=1  $branch_id  ";
    	return $db->fetchAll($sql);
    }
    function getAllYear(){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('branch_id');
    	$sql="SELECT id, CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') as year from rms_tuitionfee where status=1  $branch_id  ";
    	return $db->fetchAll($sql);
    }
    public function getServiceFeeInServiceCharge($data){
    	$db=$this->getAdapter();
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$item_id = $data['service'];
    	$param = array('Id'=>$item_id);
    	$result = $dbg->getItemDetailRow($param);
    	
    	$item_type = $result['items_type'];
    	$is_set = $result['is_productseat'];
    	if($item_type==1 OR $item_type==2){//grade or service
    		$sql="SELECT
			    		tfd.id,
			    		tfd.tuition_fee AS price,
			    		(SELECT is_onepayment FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=$item_id LIMIT 1) as onepayment,
			    		(SELECT is_productseat FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=$item_id LIMIT 1) as is_set,
			    		(SELECT items_type FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=$item_id LIMIT 1) as items_type,
			    		(SELECT spd.validate FROM rms_student_payment as sp,rms_student_paymentdetail as spd
    						WHERE sp.student_id =".$data['studentid']."
			    				AND spd.itemdetail_id = $item_id
			    				AND spd.is_start=1
			    				AND sp.`id`=spd.`payment_id`
			    				AND sp.is_void=0
		    				ORDER BY spd.validate DESC LIMIT 1) AS validate
    				FROM
	    				rms_tuitionfee AS tf,
	    				rms_tuitionfee_detail AS tfd
    				WHERE
	    				tf.id = tfd.fee_id
	    				AND tf.status=1
	    				AND tfd.class_id =".$item_id;
	    				
    			if(!empty($data['term'])){
    				$sql.=" AND tfd.payment_term = ".$data['term'];
    			}
    				if($item_type==1){// grade
    					$sql.=" AND tf.type =1 ";
    					if(!empty($data['year'])){
    						$sql.=" AND tf.id =".$data['year'];
    					}
    				}else if($item_type==2){//service
    					$sql.=" AND tf.type = 2 ";
						if(!empty($data['year'])){
							
							$_dbFee = new Accounting_Model_DbTable_DbFee();
							$feeInfo = $_dbFee->getFeeById($data['year']);
							if(!empty($feeInfo["academic_year"])){
								$sql.=" AND tf.academic_year =".$feeInfo["academic_year"];
							}
    					}
    				}
    				if(!empty($data['branch_id'])){
    					$sql.=" AND branch_id = ".$data['branch_id'];
    				}
    				$sql.=" LIMIT 1";
    				$resultFee =  $db->fetchRow($sql);
    	}elseif ($item_type==3){//product
    			if($is_set==1){//for set
    				$sql="SELECT
	    						price,
	    						is_onepayment as onepayment,
	    						is_productseat as is_set,
	    						items_type,
	    						(SELECT spd.validate FROM rms_student_payment as sp,
		    						rms_student_paymentdetail as spd
			    						WHERE sp.student_id = ".$data['studentid']."
			    							AND spd.itemdetail_id = $item_id
			    							AND spd.is_start=1
			    							AND sp.`id`=spd.`payment_id`
			    							AND sp.is_void=0
			    							ORDER BY spd.validate DESC LIMIT 1) AS validate
    							FROM
    								`rms_itemsdetail`
    								WHERE
    									id=$item_id LIMIT 1 ";
    			}else{
    					$sql="SELECT
		    						price,
		    						(SELECT is_onepayment FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=$item_id LIMIT 1) as onepayment,
		    						(SELECT is_productseat FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=$item_id LIMIT 1) as is_set,
		    						(SELECT items_type FROM `rms_itemsdetail` WHERE rms_itemsdetail.id=$item_id LIMIT 1) as items_type,
		    						(SELECT spd.validate FROM rms_student_payment as sp,rms_student_paymentdetail as spd
    							WHERE sp.student_id = ".$data['studentid']."
	    							AND spd.itemdetail_id = $item_id
	    							ANd spd.is_start=1
	    							AND sp.`id`=spd.`payment_id`
	    							AND sp.is_void=0
	    							ORDER BY spd.validate DESC LIMIT 1) AS validate
    							FROM
    								`rms_product_location`
    							WHERE
    								pro_id=$item_id
    								AND branch_id = ".$data['branch_id']." LIMIT 1 ";
    				}
    				$resultFee =  $db->fetchRow($sql);
    		}
    		
    		
    		$option = empty($data['option'])?null:$data['option'];
    		$feeId = empty($data['year'])?null:$data['year'];
    			
    		$param = array(
    				'branch_id'=>$data['branch_id'],
    				'feeId'=>$feeId,
    				'grade'	=>$data['service'],//grade
    				'serviceType'=>$data['serviceType'],
    				'periodId'=>$data['term'],
    				'option'=>$option,
    		);
    		
    		$resultPeriod = $dbg->getAllStudyPeriod($param);
    		return array(
    				'resultFee'=>$resultFee,
    				'resultPeriod'=>$resultPeriod,
    				);
    	}
}