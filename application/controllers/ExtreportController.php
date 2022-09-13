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
	
	function rptScoreListAction(){ //ពិន្ទុសរុបតាមមុខ
		$this->_helper->layout()->disableLayout();
    	$id=$this->getRequest()->getParam("id");
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    		$result = $db->getStundetScoreResult($search,null,1);
    		$this->view->studentgroup = $result;
    	}
    	else{
    		$row = $db->getScoreExamByID($id);
    		$search = array(
    				'group' => $row['group_id'],
    				'study_year'=> $row['for_academic_year'],
    				'exam_type'=> $row['exam_type'],
    				'branch_id'=>$row['branch_id'],
    				'for_month'=>$row['for_month'],
    				'for_semester'=>$row['for_semester'],
    				'grade'=> '',
    				'degree'=>'',
    				'session'=> '',
    		);
    		$result = $db->getStundetScoreResult($search,$id,1);
    		$this->view->studentgroup = $result;
    	}
    	$this->view->search=$search;
    	
    	$this->view->g_all_name=$db->getAllgroupStudyNotPass();
    	$this->view->month = $db->getAllMonth();
    	
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    	
    	$frm = new Application_Form_FrmGlobal();
    	$branch_id = empty($result[0]['branch_id'])?1:$result[0]['branch_id'];
    	$this->view->header = $frm->getHeaderReceipt($branch_id);
    	$this->view->headerScore = $frm->getHeaderReportScore($branch_id);
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$this->view->branchInfo = $db->getBranchInfo($branch_id);
    	
    }
	
	function rptScoreDetailAction(){//តាមមុខវិជ្ជាលម្អិត
	
		$this->_helper->layout()->disableLayout();
    	$id=$this->getRequest()->getParam("id");
    	$db = new Allreport_Model_DbTable_DbRptStudentScore();
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    		$this->view->studentgroup = $db->getStundetScoreDetailGroup($search,null,1);
    	}
    	else{
    		$row = $db->getScoreExamByID($id);
    		$search = array(
    				'group' => $row['group_id'],
    				'study_year'=> $row['for_academic_year'],
    				'exam_type'=> $row['exam_type'],
    				'branch_id'=>$row['branch_id'],
    				'for_month'=>$row['for_month'],
    				'for_semester'=>$row['for_semester'],
    				'grade'=> '',
    				'degree'=>'',
    				'session'=> '',
    		);
    		$this->view->studentgroup = $db->getStundetScoreDetailGroup($search,$id,1);
    	}
    	
    	$this->view->search=$search;
//     	$this->view->g_all_name=$db->getAllgroupStudyNotPass();
    	
    	$this->view->month = $db->getAllMonth();
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    }
}





