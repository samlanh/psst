<?php
class Global_GroupController extends Zend_Controller_Action {
	const REDIRECT_URL = '/global/group';
	public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	function addRoomAction(){
		if($this->getRequest()->isPost()){
			try{
				$data=$this->getRequest()->getPost();
				$db = new Global_Model_DbTable_DbGroup();
				$room = $db->addNewRoom($data);
				print_r(Zend_Json::encode($room));
				exit();
			}catch (Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}
// 	function getsubjectbydegreeAction(){
// 		if($this->getRequest()->isPost()){
// 			$data=$this->getRequest()->getPost();
// 			$db = new Global_Model_DbTable_DbGroup();
// 			$group = $db->getDeptSubjectById($data['dept_id']);
// 			print_r(Zend_Json::encode($group));
// 			exit();
// 		}
// 	}
    function getgroupAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Foundation_Model_DbTable_DbStudent();
    		$group = $db->getAllgroup();
    		$degree = $db->getAllFecultyName();    		
    		array_unshift($group, array ('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
    		
    		$model = new Application_Model_DbTable_DbGlobal();
    		$room = $model->getAllRoom();
    		array_unshift($room, array ( 'id' => 0,'name' => $this->tr->translate("SELECT_ROOM")));
    		
    		$result = array('group'=>$group,'degree'=>$degree,'room'=>$room);
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    }
//     function addTeacherPopupAction(){
//     	if($this->getRequest()->isPost()){
//     		try{
//     			$data = $this->getRequest()->getPost();
//     			$db = new Global_Model_DbTable_DbGroup();
//     			$teacher = $db->addTeacherAjax($data);
//     			print_r(Zend_Json::encode($teacher));
//     			exit();
//     		}catch(Exception $e){
//     			Application_Form_FrmMessage::message("INSERT_FAIL");
//     			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
//     		}
//     	}
//     }
    function getteacherAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		
    		$_db = new Accounting_Model_DbTable_DbFee();
    		$row = $_db->getFeeById($data['academic_year']);
    		$schoolOption = $row['school_option'];
    		
    		if (!empty($schoolOption)){
	    		$db = new Global_Model_DbTable_DbGroup();
	    		$teacher = $db->getAllTeacher($schoolOption);
	    		
	    		array_unshift($teacher, array ('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
	    		array_unshift($teacher, array ('id' => 0, 'name' => $this->tr->translate("PLEASE_SELECT")));
	    		print_r(Zend_Json::encode($teacher));
	    		exit();
    		}
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
				$subject = $db->getAllSubjectName($schoolOption);
				array_unshift($subject, array ('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
				array_unshift($subject, array ('id' => 0, 'name' => $this->tr->translate("PLEASE_SELECT")));
				print_r(Zend_Json::encode($subject));
    			exit();
    		}
    	}
    }
    function getroomAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$model = new Application_Model_DbTable_DbGlobal();
    		$room = $model->getAllRoom();
    		array_unshift($room, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    		array_unshift($room, array ( 'id' => 0,'name' =>$this->tr->translate("SELECT_ROOM")));
    		print_r(Zend_Json::encode($room));
    		exit();
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
    /*function getgroupbyacademicAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
    		$group = $db->getAllGroupByAcademic($data['academic_year']);
    		if (empty($data['noaddnew'])){
    			array_unshift($group, array ('id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    		}
    		array_unshift($group, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_GROUP")));
    		print_r(Zend_Json::encode($group));
    		exit();
    	}
    }*/
    function getacademicAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
    		$group = $db->getAllYearByBranch($data['branch_id']);
    		array_unshift($group, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_ACADEMIC_YEAR")));
    		print_r(Zend_Json::encode($group));
    		exit();
    	}
    }
    function getdegreeAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$_db = new Accounting_Model_DbTable_DbFee();
    		$row = $_db->getFeeById($data['academic_year']);
    		$schoolOption = $row['school_option'];
    		$schoolOption = null;
    		$db = new Application_Model_DbTable_DbGlobal();
    		$group = $db->getAllItems(1,null,$schoolOption);
    		array_unshift($group, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_DEGREE")));
    		print_r(Zend_Json::encode($group));
    		exit();
    	}
    }
}