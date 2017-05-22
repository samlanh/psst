<?php

class Accounting_Model_DbTable_DbProgramCharge extends Zend_Db_Table_Abstract
{

    protected $_name = 'mrs_program_fee';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
	
    function getAllTuitionFee($search){
    	$db = $this->getAdapter();
    	$sql = "SELECT p.service_id as id,p.`title` AS service_name,
    		p.status,t.title as cate_name FROM `rms_program_name` AS p,
    		`rms_program_type` AS t
    	WHERE t.id=p.ser_cate_id ";
    	$order=" ORDER BY p.title";
    	$where = '';
    	
    	if(empty($search)){
    		 $sql.$order;
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$where.=" AND title LIKE '%".$search['txtsearch']."%'";
    	}
    	if($search['type']>-1){
    		$where.= " AND type = ".$search['type'];
    	}
    	if($search['status']>-1){
    		$where.= " AND status = '".$search['status']."'";
    	}
    	return $db->fetchAll($sql.$where.$order);

    }
    public function addProgramCharge($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$arr = array(
    			'start_year' 	=> $_data['from_year'],
    			'end_year' 	=> $_data['to_year'],
    			'batch' => $_data['generation'],
    			'study_type'=> $_data['type_hour'],
    			'note' => $_data['note'],
    			'status' => $_data['status'],
    			'date'=>$_data['create_date'],
    			'user_id'=>$this->getUserId()
    			);
    		
    		$id =$this->insert($arr);
    		
    		$this->_name='mrs_programfee_detail';
    		$ids =explode(',', $_data['identity']);//main
    		$id_term =explode(',', $_data['iden_term']);//sub
	    		foreach ($ids as $i){
		    					foreach ($id_term as $j){
			    				$_arr= array(
			    						'programfeeid'=>$id,
			    						'subject_id'=>$_data['service_id'.$i],
			    						'pay_type'=>$j,
			    						'total_hour'=>$_data['level'.$i],
			    						'fee'=>$_data['fee'.$i.'_'.$j],
			    						'note'=>$_data['remark'.$i],
			    						'status'=>$_data['status'.$i],
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
    public function updateProgramCharge($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$_arr = array(
    			'start_year' 	=> $_data['from_year'],
    			'end_year' 	=> $_data['to_year'],
    			'batch' => $_data['generation'],
    			'study_type'=> $_data['type_hour'],
    			'note' => $_data['note'],
    			'status' => $_data['status'],
    			'date'=>$_data['create_date'],
    			'user_id'=>$this->getUserId()
    				
    		);
    		$where=$this->getAdapter()->quoteInto("id=?", $_data['id']);
    		$this->update($_arr, $where);
    		
    		$this->_name='mrs_programfee_detail';
    		
    		$where=$this->getAdapter()->quoteInto("programfeeid=?", $_data['id']);
    		$this->delete($where);
    		
    		$ids =explode(',', $_data['identity']);//main
    		$id_term =explode(',', $_data['iden_term']);//sub
	    		foreach ($ids as $i){
	    			foreach ($id_term as $j){
	    					$_db = new Application_Model_DbTable_DbGlobal();
	    						$_arr= array(
	    								'programfeeid'=>$_data['id'],
	    								'subject_id'=>$_data['service_id'.$i],
	    								'pay_type'=>$j,
	    								'total_hour'=>$_data['level'.$i],
	    								'fee'=>$_data['fee'.$i.'_'.$j],
	    								'note'=>$_data['remark'.$i],
	    								'status'=>$_data['status'],
	    						);
	    						$this->insert($_arr);
	    				}
    			}
    		$db->commit();
    		return true;
    	}catch (Exception $e){
    		$db->rollBack();
    		echo $e->getMessage();exit();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		return false;
    	}
    }
    function getProgramFeebyId($program){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM `mrs_programfee_detail` WHERE programfeeid=".$program." ORDER BY subject_id ";
    	return $db->fetchAll($sql);
    	 
    }
    
    public function getProgramPriceExist($service_id,$pay_type){//
    	$db = $this->getAdapter();
    	$sql = "SELECT programfeeid,fee FROM `mrs_programfee_detail` WHERE subject_id=$service_id 
    	AND pay_type=$pay_type LIMIT 1  ";
    	return $db->fetchRow($sql);
    }
    public function getServiceChargeById($service_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM mrs_program_fee WHERE id=$service_id LIMIT 1";
    	
    	return $db->fetchRow($sql);
    
    }
}



