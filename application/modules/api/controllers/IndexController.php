<?php
class Api_IndexController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/api/index';
	
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	$this->_helper->layout()->disableLayout();
    	header('Content-type:application/json;charset=utf-8');
    	
//     	header('Content-Type: application/json');
    	$_dbAction = new Api_Model_DbTable_DbActions();
    	$GetData = $this->getRequest()->getParams();
    	if ($_SERVER['REQUEST_METHOD'] == "GET"){
    		if($GetData['url']=="profile"){
    			$_dbAction->profileAction($GetData);
    		}else if ($GetData['url']=="payment"){
    			$_dbAction->paymentAction($GetData);
    		}else if ($GetData['url']=="paymentDetail"){
    			$_dbAction->paymentDetailAction($GetData);
    		}else if ($GetData['url']=="schedule"){
    			$_dbAction->scheduleAction($GetData);
    		}else if ($GetData['url']=="attendance"){
    			$_dbAction->attendanceAction($GetData);
    		}else if ($GetData['url']=="attendanceDetail"){
    			$_dbAction->attendanceDetailAction($GetData);
    		}else if ($GetData['url']=="discipline"){
    			$_dbAction->disciplineAction($GetData);
    		}else if ($GetData['url']=="disciplineDetail"){
    			$_dbAction->disciplineDetailAction($GetData);
    		}else if ($GetData['url']=="score"){
    			$_dbAction->scorelAction($GetData);
    		}else if ($GetData['url']=="scoredetail"){
    			$_dbAction->scoredetailAction($GetData);
    		}else if ($GetData['url']=="elearning"){
    			$_dbAction->elearningAction($GetData);
    		}else if ($GetData['url']=="slieshow"){
    			$_dbAction->slidshowAction($GetData);
    		}else if ($GetData['url']=="elearningvideo"){
    			$_dbAction->getAllVideoLearningAction($GetData);
    		}else if ($GetData['url']=="evaluation"){
    			$_dbAction->envaluationAction($GetData);
    		}else if ($GetData['url']=="news"){
    			$_dbAction->newsAction($GetData);
    		}else if ($GetData['url']=="notification"){
    			$_dbAction->notificationAction($GetData);
    		}else if ($GetData['url']=="contactus"){
    			$_dbAction->contactUsAction($GetData);
    		}else if ($GetData['url']=="singlecontact"){
    			$_dbAction->SinglecontactAction($GetData);
    		}
    		else if ($GetData['url']=="course"){
    				$_dbAction->coursePostAction($GetData);
    		}else if ($GetData['url']=="calendar"){
    			$_dbAction->calendarAction($GetData);
    		}
    		else{
    			echo Zend_Http_Response::responseCodeAsText(401,true);
    		}
    	}else if ($_SERVER['REQUEST_METHOD'] == "POST"){
    		if($this->getRequest()->isPost()){
    			$postData = $this->getRequest()->getPost();
    			if ($GetData['url']=="auth"){// login
    				$_dbAction->loginAction($postData);
    			}else if ($GetData['url']=="changePassword"){// change password
    				$_dbAction->changePasswordAction($postData);
    			}else if ($GetData['url']=="addtoken"){// change password
    				$_dbAction->addTokenAction($postData);
    			}
    			else{
    				echo Zend_Http_Response::responseCodeAsText(401,true);
    			}
				
			}else{
				echo Zend_Http_Response::responseCodeAsText(405,true);
			}
    	}else{
    		echo Zend_Http_Response::responseCodeAsText(405,true);
    	}
    	exit();
    }
   function testAction(){
	   	$this->_helper->layout()->disableLayout();
	   	$_dbAction = new Api_Model_DbTable_DbActions();
//    		return $_dbAction->profileAction($GetData);
	   	$data['stu_id']='2';
	   	$data['currentLang']='2';
// 	   	$data['']='';
// 	   	$data['']='';
// 	   	$data['']='';
   		print_r(Zend_Json::encode($_dbAction->profileAction($data)));
   		exit();
   }
   function transcriptpdfAction(){
   	$this->_helper->layout()->disableLayout();
   	
   	if ($_SERVER['REQUEST_METHOD'] == "GET"){
   		$stu_id =$this->getRequest()->getParam("stu_id");
   		$stu_id = empty($stu_id)?0:$stu_id;
   		$group_id =$this->getRequest()->getParam("group_id");
   		$group_id = empty($group_id)?0:$group_id;
   		$exam_type =$this->getRequest()->getParam("exam_type");
   		$exam_type = empty($exam_type)?0:$exam_type;
   		$for_semester =$this->getRequest()->getParam("for_semester");
   		$for_semester = empty($for_semester)?0:$for_semester;
   		$for_month =$this->getRequest()->getParam("for_month");
   		$for_month = empty($for_month)?0:$for_month;
   		$data = array(
   				'stu_id'=>$stu_id,
   				'group_id'=>$group_id,
   				'exam_type'=>$exam_type,
   				'for_semester'=>$for_semester,
   				'for_month'=>$for_month,
   		);
   	}
   $dbApi = new Api_Model_DbTable_DbApi();
   	
   	$rs = $dbApi->getExamByExamIdAndStudent($data);
   	$this->view->rs = $rs;
   	if ($rs['exam_type']==2){
   		$monthlysemesterAverage = $dbApi->getAverageMonthlyForSemester($rs['group_id'], $rs['for_semester'], $rs['student_id']);
   		$this->view->monthlySemester = $monthlysemesterAverage;
   		$semesterAverage = $dbApi->getAverageSemesterFull($rs['group_id'], $rs['for_semester'], $rs['student_id']);
   		$this->view->Semester = $semesterAverage;
   	}
   	$db = new Foundation_Model_DbTable_DbScore();
   	$subject =$db->getSubjectByGroup($data['group_id'],null,$data['exam_type']);
   	$this->view->subject = $subject;
   	
   }
   function downloadAction(){
   	
   }
   
   function examscorepdfAction(){
	   	$this->_helper->layout()->disableLayout();
	   	if ($_SERVER['REQUEST_METHOD'] == "GET"){
	   		$id=$this->getRequest()->getParam("id");
	   		$dbApi = new Api_Model_DbTable_DbApi();
	   		 
	   		$result = $dbApi->getStundetScoreResult($id);
	   		$this->view->studentgroup = $result;
	   	}
	   		

   		$db = new Allreport_Model_DbTable_DbRptStudentScore();
	   	$this->view->g_all_name=$db->getAllgroupStudyNotPass();
	   	$this->view->month = $db->getAllMonth();
	   	 
	   	$key = new Application_Model_DbTable_DbKeycode();
	   	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	   	 
	   	$frm = new Application_Form_FrmGlobal();
	   	$branch_id = empty($result[0]['branch_id'])?1:$result[0]['branch_id'];
	   	$this->view->header = $frm->getHeaderReceipt($branch_id,1);
	   	
   }
   
}







