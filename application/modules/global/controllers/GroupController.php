<?php
class Global_GroupController extends Zend_Controller_Action {
	const REDIRECT_URL = '/global/group';
	public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}

    function getgroupAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Foundation_Model_DbTable_DbStudent();
			$model = new Application_Model_DbTable_DbGlobal();
    		$group = $db->getAllgroup();
    		$degree =$model->getAllItems(1,null);    		
    		array_unshift($group, array ('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
    		
    		
    		$room = $model->getAllRoom();
    		array_unshift($room, array ( 'id' => 0,'name' => $this->tr->translate("SELECT_ROOM")));
    		
    		$result = array('group'=>$group,'degree'=>$degree,'room'=>$room);
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    }
    
    function getsubjectAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    
    		$_db = new Accounting_Model_DbTable_DbFee();
    		$row = $_db->getFeeById($data['academic_year']);
    		$schoolOption = $row['school_option'];
    
    		if (!empty($schoolOption)){
    			$db = new Application_Model_DbTable_DbGlobal();
				$arrayFilter = array(
					"schoolOption"=>$schoolOption,
				);
				$subject = $db->getAllSubjectName($arrayFilter);
				array_unshift($subject, array ('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
				array_unshift($subject, array ('id' => 0, 'name' => $this->tr->translate("PLEASE_SELECT")));
				print_r(Zend_Json::encode($subject));
    			exit();
    		}
    	}
    }
   
    function getgroupbybranchAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
    		$forfilter=empty($data['forfilter'])?null:$data['forfilter'];
    		$group = $db->getAllGroupByBranch($data['branch_id'],$forfilter);
    		if(!empty($data['noaddnew'])){
    			array_unshift($group, array ('id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    		}
    		array_unshift($group, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_GROUP")));
    		print_r(Zend_Json::encode($group));
    		exit();
    	}
    }
}