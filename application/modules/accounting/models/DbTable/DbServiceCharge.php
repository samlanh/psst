<?php

class Accounting_Model_DbTable_DbServiceCharge extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    function getAceYear(){
    	$db=$this->getAdapter();
    	$sql="SELECT id,CONCAT(from_academic,'-',to_academic,'(',generation,')') AS `name`
    	FROM rms_tuitionfee as tf WHERE tf.`status`=1 ";
    	$oder=" ORDER BY id DESC ";
    	return $db->fetchAll($sql.$oder);
    }
    function getAllServiceFee($search){
	    try{		
	    $db=$this->getAdapter();
    	$sql = "SELECT t.id,
	    	(SELECT CONCAT(branch_nameen) from rms_branch where br_id =t.branch_id LIMIT 1) as branch,
	    	CONCAT(t.from_academic,' - ',t.to_academic) AS academic,
	    	t.create_date,
	    	(select name_en from rms_view where type=12 and key_code=t.is_finished LIMIT 1) as is_finished,
	    	(select name_kh from rms_view where type=1 and key_code = t.status) as status,
	    	(SELECT CONCAT(first_name) from rms_users where rms_users.id = t.user_id LIMIT 1) as user
	    	
    	FROM `rms_tuitionfee` AS t
    		WHERE t.type=2 ";
    	$where =" ";
    	 
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " CONCAT(from_academic,'-',to_academic) LIKE '%{$s_search}%'";
    		$s_where[] = " t.generation LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['year'])){
    		$where.=" AND t.id=".$search['year'];
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
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	 
    	$order=" GROUP BY t.branch_id,t.from_academic,t.to_academic,t.generation,t.time ORDER BY t.id DESC  ";
    	return $db->fetchAll($sql.$where.$order);
	    }catch(Exception $e){
	    	Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    }
    }    
    function getCondition($_data){
    	$db = $this->getAdapter();
    	$find="select id from rms_tuitionfee where type=2 AND branch_id='".$_data['branch_id']."' AND from_academic ='".$_data['from_academic']."' AND to_academic='".$_data['to_academic']."'";
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
	    				'from_academic'=>$_data['from_academic'],
	    				'to_academic'=>$_data['to_academic'],
	    				'note'=>$_data['note'],
	    				'type'=>2,
	    				'status'=>$_data['status'],
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
    				'from_academic'	=>$_data['from_academic'],
    				'to_academic'	=>$_data['to_academic'],
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
    							//'status'=>$_data['status_'.$i]
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
    	$sql = "SELECT * FROM rms_tuitionfee WHERE type=2 AND id=$service_id LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getServiceFeebyId($service_id){    	
    	$db = $this->getAdapter();	
    	$sql = "SELECT *
    		FROM rms_tuitionfee_detail WHERE fee_id = ".$service_id." ORDER BY id ";
    	return $db->fetchAll($sql);    	 
    }    
    public function setServiceChargeExist($service_id,$pay_type){
    	$db = $this->getAdapter();
    	$sql = "SELECT servicefee_id,price FROM `rms_servicefee_detail` WHERE service_id=$service_id AND pay_type=$pay_type ";
    	return $db->fetchRow($sql);
    }    
    function getAllBranch(){
    	$db = $this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('br_id');
    	
    	$sql="select br_id as id, CONCAT(branch_nameen) as name from rms_branch where status=1  $branch_id  ";
    	return $db->fetchAll($sql);
    }
    
    function getAllYear(){
    	$db = $this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('branch_id');
    	 
    	$sql="select id, CONCAT(from_academic,'-',to_academic,'(',generation,')') as year from rms_tuitionfee where status=1  $branch_id  ";
    	return $db->fetchAll($sql);
    }
}