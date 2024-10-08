<?php

class Accounting_Model_DbTable_DbTuitionFee extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllTuitionFee($search=null){  
    	$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$branch = $dbp->getBranchDisplay();
		
    	$sql = "SELECT 
					t.id
					,(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id =t.branch_id LIMIT 1) AS branch
					,CONCAT(t.from_academic,' - ',t.to_academic) AS academic
					,t.generation
					,t.create_date
					,(SELECT v.name_en FROM rms_view AS v WHERE v.type=12 AND v.key_code=t.is_finished LIMIT 1) AS is_finished
					,(SELECT v.name_en FROM rms_view AS v WHERE v.type=1 AND v.key_code=t.status  LIMIT 1) AS `status`
				   ,(SELECT CONCAT(u.first_name) FROM rms_users AS u WHERE u.id = t.user_id LIMIT 1) AS user
				FROM `rms_tuitionfee` AS t
				 WHERE 1	
				";
    	$where =" ";
    	
	    if(!empty($search['txtsearch'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['txtsearch']));
		 	$s_where[] = " CONCAT(t.from_academic,'-',t.to_academic) LIKE '%{$s_search}%'";
	    	$s_where[] = " t.generation LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
	    }
	    if(!empty($search['year'])){
	    	$where.=" AND t.id=".$search['year'];
	    }
	    
	    if(!empty($search['branch_id'])){
	    	$where.=" AND t.branch_id=".$search['branch_id'];
	    }
	    
	    if($search['finished_status']!=""){
	    	$where.=" AND t.is_finished=".$search['finished_status'];
	    }
	    
	    if($search['status_search']>0){
	    	$where.=" AND t.status=".$search['status_search'];
	    }
	    
	    $dbp = new Application_Model_DbTable_DbGlobal();
	    $where.=$dbp->getAccessPermission();
	    
	    $order=" GROUP BY t.branch_id,t.from_academic,t.to_academic,t.generation,t.time ORDER BY t.id DESC  ";
	    
    	return $db->fetchAll($sql.$where.$order);
    }
   
    function getCondition($_data){
    	$db = $this->getAdapter();
    	$find="select id from rms_tuitionfee where from_academic=".$_data['from_year']." and to_academic=".$_data['to_year']." 
    		   and generation='".$_data['generation']."'  AND branch_id = ".$_data['branch'];
    	
    	return $db->fetchOne($find);
    }
  
    public function addTuitionFee($_data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
		
    	$fee_id = $this->getCondition($_data);
    	
    	try{
    		if(!empty($fee_id)){
    			
    		}else{
	    		$_arr = array(
	    				'from_academic'=>$_data['from_year'],
	    				'to_academic'=>$_data['to_year'],
	    				'generation'=>$_data['generation'],
	    				'note'=>$_data['note'],
	    				'branch_id'=>$_data['branch'],
	    				'create_date'=>date("Y-m-d"),
	    				'user_id'=>$this->getUserId()
	    				);
	    		$fee_id = $this->insert($_arr);
    		}
	    		
	    		$this->_name='rms_tuitionfee_detail';
	    		$ids = explode(',', $_data['identity']);
	    		$id_term =explode(',', $_data['iden_term']);
	    		foreach ($ids as $i){
	    			foreach ($id_term as $j){
	    				$_arr = array(
	    						'fee_id'=>$fee_id,
	    						'class_id'=>$_data['class_'.$i],
	    						'payment_term'=>$j,
	    						'tuition_fee'=>$_data['fee'.$i.'_'.$j],
	    						'remark'=>$_data['remark'.$i]
	    				);
	    				$this->insert($_arr);
	    			}
	    		}
	    	    $db->commit();
	    	    return true;
    		
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		return false;
    	}
    }
    public function updateTuitionFee($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    
    		$_arr = array(
    				'from_academic'	=>$_data['from_year'],
    				'to_academic'	=>$_data['to_year'],
    				'generation'	=>$_data['generation'],
    				'note'			=>$_data['note'],
    				'status'		=>$_data['status'],
    				'is_finished'	=>$_data['is_finished'],
    				//'time'			=>$_data['time'],
    				'branch_id'		=>$_data['branch'],
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
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
   
    public function getFeeDetailById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM rms_tuitionfee_detail WHERE fee_id = ".$id ." ORDER BY id";
    	return $db->fetchAll($sql);
    
    }
    
    public function getFeeById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM rms_tuitionfee WHERE id = ".$id;
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission();
    	$sql.=" LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
    
    
    function getSession(){
    	$db=$this->getAdapter();
    	$sql="SELECT key_code AS id,CONCAT(name_en,'-',name_kh) AS `name` FROM rms_view WHERE `type`=4 AND `status`=1 ";
    	return $db->fetchAll($sql);
    }
    function getAceYear(){
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql="SELECT id,CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') AS `name`     
                     FROM rms_tuitionfee WHERE `status`=1 $branch_id  group by from_academic,to_academic,generation,time ";
        $oder=" ORDER BY id DESC ";
    	return $db->fetchAll($sql.$oder);
    }
    
 
    function getAllBranch(){
    	$_db = new Application_Model_DbTable_DbGlobal();
    	return $_db->getAllBranch();
    } 
    
    function getProcessTypeView(){
    	$db = $this->getAdapter();
    	$sql="SELECT key_code AS id , name_en AS `name` FROM rms_view  WHERE `status`=1 AND type=12";
    	return $db->fetchAll($sql);
    }
    
}