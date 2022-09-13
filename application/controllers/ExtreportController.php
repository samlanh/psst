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
	
	function rptScoreListAction(){ 
		$this->_helper->layout()->disableLayout();
    	$gradingID=$this->getRequest()->getParam("id");
    	$gradingID =empty($gradingID)?0:$gradingID;

		$dbExternal = new Application_Model_DbTable_DbExternal();
		$row = $dbExternal->getClassSubjectScoreById($gradingID);
		$this->view->rs = $row;
		
		$groupId = empty($row['groupId'])?0:$row['groupId'];
		
		$arrFilter = array(
			'groupId'=>$groupId,
		);
		$this->view->students = $dbExternal->getStudentByGroup($arrFilter);
		
    	$gradingId = empty($row['gradingId'])?0:$row['gradingId'];
		$this->view->criterial = $dbExternal->getGradingSystemDetail($gradingId);
		
    	$frm = new Application_Form_FrmGlobal();
    	$branch_id = empty($result[0]['branch_id'])?1:$result[0]['branch_id'];
    	$this->view->header = $frm->getHeaderReceipt($branch_id);
    	$this->view->headerScore = $frm->getHeaderReportScore($branch_id);
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branchInfo = $db->getBranchInfo($branch_id);
    	
    }
	
	
}





