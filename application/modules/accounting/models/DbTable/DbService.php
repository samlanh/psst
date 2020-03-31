<?php
class Accounting_Model_DbTable_DbService extends Zend_Db_Table_Abstract
{

  
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    
    public function addservice($_data){
    	$db = $this->getAdapter();
    		$_arr = array(
    				'title'=>$_data['add_title'],
    				'type'=>2,
    				'ser_cate_id'=>$_data['title'],
    				'description'=>$_data['description'],
    				'create_date'=>Zend_Date::now(),
    				'status'=>$_data['status'],
    				'user_id'=>$this->getUserId(),
    		);
    		return ($this->insert($_arr));
    } 
    public function addServicePopup($_data){
    	$db = $this->getAdapter();
    	$_arr = array(
    			'title'=>$_data['service_name'],
    			'type'=>2,
    			'ser_cate_id'=>$_data['service_type'],
    			'description'=>$_data['description'],
    			'create_date'=>Zend_Date::now(),
    			'status'=>$_data['status_service'],
    			'user_id'=>$this->getUserId(),
    	);
    	return ($this->insert($_arr));
    }
    
   
public function updateservice($_data){
    	$_arr=array(
	    			'title'=>$_data['add_title'],
	    			'ser_cate_id'=>$_data['title'],
    				'description'=>$_data['description'],
    				'status'=>$_data['status'],
    				'user_id'=>$this->getUserId()
    	);
    	$where=$this->getAdapter()->quoteInto("service_id=?", $_data["id"]);
    	$this->update($_arr, $where);
    }
 
public function AddServiceType($_data){
    	try{
    	$this->_name='rms_program_type';
    	$_db = $this->getAdapter();
	    $_arr = array(
    			'code'=>$_data['code'],
    			'title'=>$_data['p_title'],
    			'item_desc'=>$_data['note'],
    			'status'=>$_data['status_p'],
    			'type'=>$_data['type'],
    			'create_date'=> new Zend_Date(),
    			'user_id' => $this->getUserId(),
	    );
    	return $this->insert($_arr);
    }catch(Exception $e){
    	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
   }
}
}