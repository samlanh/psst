<?php
class Home_SearchstudentinfoController extends Zend_Controller_Action {
	
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
		try{
			$param = $this->getRequest()->getParams();
			if(isset($param['search'])){
// 			if(!empty($param['adv_search'])){
				$search=$param;
			}
			else{
				$search = array(
						'adv_search' => '',
						'study_year'=> '',
						'grade_all'=> '',
						'session'=> '',
						'time'=> '',
						'group'=>'',
						'degree'=> '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
			$this->view->adv_search=$search;
			$db_student= new Home_Model_DbTable_DbStudent();
			$rs_rows = $db_student->getAllStudent($search);
			$this->view->rowdata = $rs_rows;
			$this->view->list ="";
			
			$paginator = Zend_Paginator::factory($rs_rows);
			$paginator->setDefaultItemCountPerPage(30);
			$allItems = $paginator->getTotalItemCount();
			$countPages= $paginator->count();
			$p = Zend_Controller_Front::getInstance()->getRequest()->getParam('pages');
			 
			if(isset($p))
			{
				$paginator->setCurrentPageNumber($p);
			} else $paginator->setCurrentPageNumber(1);
			
			$currentPage = $paginator->getCurrentPageNumber();
			 
			$this->view->row  = $paginator;
			$this->view->countItems = $allItems;
			$this->view->countPages = $countPages;
			$this->view->currentPage = $currentPage;
			
			if($currentPage == $countPages)
			{
				$this->view->nextPage = $countPages;
				$this->view->previousPage = $currentPage-1;
			}
			else if($currentPage == 1)
			{
				$this->view->nextPage = $currentPage+1;
				$this->view->previousPage = 1;
			}
			else {
				$this->view->nextPage = $currentPage+1;
				$this->view->previousPage = $currentPage-1;
			}
			
// 			$list = new Application_Form_Frmtable();
// 			$collumns = array("BRANCH_NAME","STUDENT_ID","STUDENT_NAME","SEX","PHONE","ACADEMIC_YEAR","GROUP","DEGREE","GRADE","SESSION","ROOM_NAME","STATUS");
// 			$link=array(
// 					'module'=>'home','controller'=>'searchstudentinfo','action'=>'student-detail',
// 			);
// 			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_name'=>$link,'stu_code'=>$link,'name'=>$link,'stu_khname'=>$link,'grade'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	
	public function studentDetailAction(){
		$db= new Home_Model_DbTable_DbStudent();
		$dbgb= new Application_Model_DbTable_DbGlobal();
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'branch_id'=>'',
						'payment_by'=>-1,
						'grade_all' =>-1,
						'user'=>-1,
						'adv_search' => '',
						'study_year'=> '',
						'grade'=> '',
						'session'=> '',
						'time'=> '',
						'degree'=> '',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
			}
		    $id= $this->getRequest()->getParam('id');
			$this->view->adv_search=$search;
			$student = $db->getStudentById($id);
			$this->view->rs =$student;
			$this->view->document =$db->getStudentDocumentById($id);
			
			$rs=$this->view->row = $db->getStudentPaymentDetail($id);
			$this->view->service=$db->getStudentServiceUsing($id,$search, 1);
			
			$re=$this->view->re_row=$db->getRescheduleByGroupId($id);
			$this->view->student_mistake = $db->getStudentMistake($id);
			$this->view->student_attendance = $db->getStudentAttendence($id);
			
			$this->view->study_history = $db->getStudyHistoryByStudent($id);
			
			$droplink= $this->getRequest()->getParam('droplink');
			$drid= $this->getRequest()->getParam('drid');
			if (!empty($droplink)){
				if ($droplink=="true" AND !empty($drid)){
					//read notification student drop
					$dbgb->updateReadNotif(1, $drid);
				}
			}
			$_db = new Application_Model_DbTable_DbGlobal();
			$this->view->day = $_db->getAllDay();
			
			$branch_id = empty($student['branch_id'])?1:$student['branch_id'];
			$frm = new Application_Form_FrmGlobal();
			$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$form=new Registrar_Form_FrmSearchInfor();
		$form->FrmSearchRegister();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
}

