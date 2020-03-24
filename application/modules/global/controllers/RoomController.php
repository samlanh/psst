
<?php
class Global_RoomController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
				$_data=$this->getRequest()->getPost();
				$search = array(
						'title'		 => $_data['title'],
						'branch_id'  => $_data['branch_id'],
						'status'	 => $_data['status_search']);
			}
			else{
				$search = array(
						'title' => '',
						'branch_id' => '',
						'status' => -1);
			}
			$db = new Global_Model_DbTable_DbRoom();
			$rs_rows= $db->getAllRooms($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","ROOM_NAME","FLOOR","MAX_STUDENT","MODIFY_DATE","BY_USER","STATUS");
			$link=array(
					'module'=>'global','controller'=>'room','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('room_name'=>$link,'branch'=>$link,'floor'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm_branch = new Global_Form_FrmSearchMajor();
		$frm =$frm_branch->searchRoom();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
	}
   function addAction(){
	   	if($this->getRequest()->isPost()){
	   		$_data = $this->getRequest()->getPost();
	   		try {
	   			$sms = "INSERT_SUCCESS";
	   			$_dbmodel = new Global_Model_DbTable_DbRoom();
	   			$_major_id = $_dbmodel->addNewRoom($_data);
	   			if($_major_id==-1){
	   				$sms = "RECORD_EXIST";
	   			}
	   			if(isset($_data['save_close'])){
	   				Application_Form_FrmMessage::Sucessfull($sms,"/global/room/index");
	   			}else{
	   				Application_Form_FrmMessage::Sucessfull($sms,"/global/room/add");
	   			}
	   
	   		}catch (Exception $e) {
	   			Application_Form_FrmMessage::message("INSERT_FAIL");
	   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		}   
	   	}
	   	$classname=new Global_Form_FrmAddClass();
	   	$frm_classname=$classname->FrmAddClass();
	   	Application_Model_Decorator::removeAllDecorator($frm_classname);
	   	$this->view->frm_classname = $frm_classname;
   }
   public function editAction()
   {
	   	$id=$this->getRequest()->getParam("id");	   
	   	if($this->getRequest()->isPost())
	   	{
	   		try{
		   		$data = $this->getRequest()->getPost();
		   		$data["id"]=$id;
		   		$db = new Global_Model_DbTable_DbRoom();
		   		$db->updateRoom($data);
		   		Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/global/room/index");
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message("EDIT_FAIL");
	   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		}
	   	}
	   	$db = new Global_Model_DbTable_DbRoom();
	   	$row = $db->getRoomById($id);
	   	if (empty($row)){
	   		Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/global/room/");
	   		exit();
	   	}
	   	$obj=new Global_Form_FrmAddClass();
	   	$frm_room=$obj->FrmAddClass($row);
	   	$this->view->update_room=$frm_room;
	   	Application_Model_Decorator::removeAllDecorator($frm_room);
   }
   function addroomAction(){//ajax
	   	if($this->getRequest()->isPost()){
   			$_data = $this->getRequest()->getPost();
   			$_dbmodel = new Global_Model_DbTable_DbRoom();
   			$roomid = $_dbmodel->addAjaxRoom($_data);
   			print_r(Zend_Json::encode($roomid));
   			exit();
	   	}
   }
   
   //new create on 24-3-2020
   function getroomAction(){
   	if($this->getRequest()->isPost()){
   		$data=$this->getRequest()->getPost();
   		$model = new Application_Model_DbTable_DbGlobal();
   		$room = $model->getAllRoom($data['branch_id']);
   		if (empty($data['has_addnew'])){
   			array_unshift($room, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
   		}
   		array_unshift($room, array ( 'id' => 0,'name' =>$this->tr->translate("SELECT_ROOM")));
   		print_r(Zend_Json::encode($room));
   		exit();
   	}
   }
}