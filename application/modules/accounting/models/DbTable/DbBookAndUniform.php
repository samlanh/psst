<?php
class Accounting_Model_DbTable_DbBookAndUniform extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_items';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    public function addservice($_data){
    	$db = $this->getAdapter();
    		$_arr = array(
    				'title'=>$_data['title'],
    				'type'=>1,
    				//'ser_cate_id'=>$_data['title'],
    				'description'=>$_data['desc'],
    				'price'=>$_data['price'],
    				'create_date'=>Zend_Date::now(),
    				'status'=>$_data['status'],
    				'user_id'=>$this->getUserId(),
    		);
    		return ($this->insert($_arr));
    } 
    public function updateservice($_data){
    	$_arr=array(
	    			'title'=>$_data['title'],
    				'description'=>$_data['desc'],
    				'status'=>$_data['status'],
    				'price'=>$_data['price'],
    			);
    	$where=' service_id='.$_data['id'];
    	return $this->update($_arr, $where);
    }
    
}



