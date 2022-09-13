<?php

class ExtreportController extends Zend_Controller_Action
{

	const REDIRECT_URL = '/home';
	
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');  
    }

    public function rptStudentListAction()
	{
		$this->_helper->layout()->disableLayout();
		$id=$this->getRequest()->getParam("id");
		if(empty($id)){
			$this->_redirect("/external/group");
		}
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
}





