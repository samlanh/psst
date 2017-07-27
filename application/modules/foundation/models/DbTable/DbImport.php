<?php

class Foundation_Model_DbTable_DbImport extends Zend_Db_Table_Abstract
{

    protected $_name = 'ldc_product';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
    public function updateItemsByImport($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	print_r($data);exit();
    	for($i=2; $i<=$count; $i++){
    		$arr = array(
    				'branch_id'=>$data[$i]['B'],
    				'stu_id'=>$data[$i]['C'],
    				'degree'=>$data[$i]['D'],
    				'status'=>1,
    		);
    		$this->_name='rms_student';    		
    		$id = $this->insert($arr);
    		
    		$arr = array(
    				'branch_id'=>1,
    				'stu_id'=>$id,
    				'degree'=>1,
    				'status'=>1,
    		);
    		$this->_name='rms_student_id';
    		$id = $this->insert($arr);
    		
    		
//     		$price = array(
//     				1=>$data[$i]['E'],
//     				2=>$data[$i]['F'],
//     				3=>$data[$i]['G'],
//     				4=>$data[$i]['H'],
//     				5=>$data[$i]['I'],
//     				6=>$data[$i]['J'],
//     				7=>$data[$i]['K'],
//     				8=>$data[$i]['L'],
//     				9=>$data[$i]['M'],
//     				10=>$data[$i]['N'],
//     		);
    		 
    		
    	}
    	
    }
}   

