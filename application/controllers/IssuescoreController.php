<?php

class IssuescoreController extends Zend_Controller_Action
{

	const REDIRECT_URL = '/home';
	
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');  
    }
	public function indexAction()
	{
		$this->_helper->layout()->disableLayout();
		$id=0;
		
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search = array(
				'txtsearch' => "",
				'study_type'=>0
				);
		}
		$search['group_id']=$id;
		$this->view->search = $search;
		
		$db = new Application_Model_DbTable_DbExternal();
		$row = $db->getStudentListByGroup($search);
		$this->view->row = $row;
		
		$rs = $db->getGroupDetailByID($id);
		$this->view->rr = $rs;
		
	}
    public function addAction()
	{
		$this->_helper->layout()->disableLayout();
		$key = new Application_Model_DbTable_DbKeycode();
		$dbset=$key->getKeyCodeMiniInv(TRUE);
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			if($dbset['scoreresulttye']==1){
				$db = new Issue_Model_DbTable_DbScore();//by subject
			}else{
				$db = new Issue_Model_DbTable_DbScoreaverage();//by average 
			}
			try {
				if(isset($_data['save_new'])){
					$rs =  $db->addStudentScore($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/score/add");
				}else {
					$rs =  $db->addStudentScore($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/score");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$row = $dbExternal->getGroupDetailByID($id);
		$this->view->row = $row;
		if(empty($row)){
			$this->_redirect("/external/group");
		}
		
		$db_global=new Application_Model_DbTable_DbGlobal();
	
		$db = new Issue_Model_DbTable_DbScore();
		$this->view-> month = $db->getAllMonth();
		
	}
}





