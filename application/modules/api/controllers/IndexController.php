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
		
		$session_lang=new Zend_Session_Namespace('lang');
		$session_lang->lang_id=empty($GetData['currentLang'])?1:$GetData['currentLang'];
			
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
    		}else if ($GetData['url']=="course"){
    				$_dbAction->coursePostAction($GetData);
    		}else if ($GetData['url']=="calendar"){
    			$_dbAction->calendarAction($GetData);
    		}else if ($GetData['url']=="getholiday"){
    			$_dbAction->holidayEveryYearAction($GetData);
    		}else if ($GetData['url']=="introductionhome"){
    			$_dbAction->introductionHomeAction($GetData);
			
			}else if ($GetData['url']=="monthOfTheYear"){
    			$_dbAction->monthOfTheYearAction($GetData);
			}else if ($GetData['url']=="systemLanguage"){
    			$_dbAction->systemLanguageAction($GetData);
			}else if ($GetData['url']=="systemViewType"){
    			$_dbAction->systemViewTypeAction($GetData);
			}else if ($GetData['url']=="systemSettingKeycode"){
    			$_dbAction->systemSettingKeycodeAction($GetData);
			}else if ($GetData['url']=="systemAcademicYear"){
    			$_dbAction->systemAcademicYearAction($GetData);
			}else if ($GetData['url']=="systemStudyDegree"){
    			$_dbAction->systemStudyDegreeAction($GetData);					
			}else if ($GetData['url']=="gradingSystem"){
    			$_dbAction->gradingSystemAction($GetData);
			}else if ($GetData['url']=="disciplinePolicy"){
    			$_dbAction->disciplinePolicyAction($GetData);
			}else if ($GetData['url']=="attendancePolicy"){
    			$_dbAction->attendancePolicyAction($GetData);
			}else if ($GetData['url']=="branchList"){
    			$_dbAction->schoolBranchListAction($GetData);
			}else if ($GetData['url']=="studentEvaluation"){
    			$_dbAction->studentEvaluationAction($GetData);
			}else if ($GetData['url']=="studentEvaluationDetail"){
    			$_dbAction->studentEvaluationDetailAction($GetData);
			}else if ($GetData['url']=="studentAttendance"){
    			$_dbAction->studentAttendanceAction($GetData);
			}else if ($GetData['url']=="studentAttendanceDetail"){
    			$_dbAction->studentAttendanceDetailAction($GetData);
			}else if ($GetData['url']=="studentSchedule"){
    			$_dbAction->studentScheduleAction($GetData);
			}else if ($GetData['url']=="studentScore"){
    			$_dbAction->studentScoreAction($GetData);
			}else if ($GetData['url']=="studentScoreTranscriptNew"){
    			$_dbAction->studentTranscriptAction($GetData);	
			}else if ($GetData['url']=="mentionScoreSetting"){
    			$_dbAction->metionScoreSettingAction($GetData);
				
				
			}else if ($GetData['url']=="studentPayment"){
    			$_dbAction->studentPaymentAction($GetData);
			}else if ($GetData['url']=="studentPaymentInfo"){
    			$_dbAction->studentPaymentInfoAction($GetData);
			}else if ($GetData['url']=="studentPaymentDetail"){
    			$_dbAction->studentPaymentDetailAction($GetData);
			}else if ($GetData['url']=="newsDetail"){
    			$_dbAction->newsDetailAction($GetData);
			}else if ($GetData['url']=="unread"){
    			$_dbAction->unreadAction($GetData);
				
			}else if ($GetData['url']=="mobileNotify"){ //2023-03-18
    			$_dbAction->mobileNotifyAction($GetData);
			}else if ($GetData['url']=="mobileNotifyDetail"){
    			$_dbAction->mobileNotificationDetailAction($GetData);
			}else if ($GetData['url']=="studentCreditMemo"){
    			$_dbAction->studentCreditMemoAction($GetData);
			}else if ($GetData['url']=="studentCreditMemoTotal"){
    			$_dbAction->studentCreditMemoTotalAction($GetData);
			}else if ($GetData['url']=="summaryAttAndDis"){
    			$_dbAction->studentSummaryAttendanceAndDisciplineAction($GetData);
			}else if ($GetData['url']=="studentRequestPermissionList"){
    			$_dbAction->getStudentRequestPermissionAction($GetData);
			}else if ($GetData['url']=="schoolBusForStudent"){
    			$_dbAction->getSchoolBusForStudentAction($GetData);
			}else if ($GetData['url']=="schoolBusProfile"){
    			$_dbAction->getSchoolBusProfileAction($GetData);
			}else if ($GetData['url']=="checkCurrentTokenAccount"){
    			$_dbAction->checkCurrentTokenAccountAction($GetData);
				
			}else if ($GetData['url']=="instructionArticle"){
    			$_dbAction->getInstructionArticleAction($GetData);
				
			}else if ($GetData['url']=="busStudentList"){
    			$_dbAction->getAllStudentListForSchoolBusAction($GetData);
			}else if ($GetData['url']=="busSchedule"){
    			$_dbAction->getSchoolBusScheduleAction($GetData);
				
			}else if ($GetData['url']=="optionDegreeStudy"){
				$GetData['getControlType'] = "studyDegree";
    			$_dbAction->getFormOptionSelectAction($GetData);
			}else if ($GetData['url']=="optionGroupStudy"){
				$GetData['getControlType'] = "groupStudy";
    			$_dbAction->getFormOptionSelectAction($GetData);
			}else if ($GetData['url']=="optionRequest"){
				$GetData['getControlType'] = "requestStatus";
    			$_dbAction->getFormOptionSelectAction($GetData);
			}else if ($GetData['url']=="optionSessionAttendance"){
				$GetData['getControlType'] = "sessionAttendance";
    			$_dbAction->getFormOptionSelectAction($GetData);
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
				}else if ($GetData['url']=="authWeb"){
    				$_dbAction->loginWebAction($postData);
				}else if ($GetData['url']=="newsRead"){
    				$_dbAction->newsReadAction($postData);
				}else if ($GetData['url']=="notificationRead"){
    				$_dbAction->notificationReadAction($postData);
					
				}else if ($GetData['url']=="setReadNotification"){
    				$_dbAction->setReadNotificationAction($postData);
				}else if ($GetData['url']=="removeTokenApp"){
    				$_dbAction->removeTokenAction($postData);
				}else if ($GetData['url']=="disableStudentAcc"){
					$_dbAction->disableMyAccountAction($postData);
				
				}else if ($GetData['url']=="checkExistingStudent"){
    				$_dbAction->checkExistingStudentAction($postData);
				}else if ($GetData['url']=="registerStudentTest"){
    				$_dbAction->submitNewRegisterAction($postData);
				}else if ($GetData['url']=="studentRequestPermission"){
    				$_dbAction->studentRequestPermissionAction($postData);
				}else if ($GetData['url']=="studentRequestPermissionEdit"){
    				$_dbAction->studentRequestPermissionEditAction($postData);
				}else if ($GetData['url']=="loginSchoolBus"){
    				$_dbAction->loginSchoolBusAction($postData);
				}else if ($GetData['url']=="onlineOfflineSchoolBus"){
    				$_dbAction->onlineOfflineSchoolBusAction($postData);
					
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
//    	print_r($rs);exit();
   	$this->view->rs = $rs;
   	if ($rs['exam_type']==2){
   		$monthlysemesterAverage = $dbApi->getAverageMonthlyForSemester($rs['group_id'], $rs['for_semester'], $rs['student_id']);
   		$this->view->monthlySemester = $monthlysemesterAverage;
   		$semesterAverage = $dbApi->getAverageSemesterFull($rs['group_id'], $rs['for_semester'], $rs['student_id']);
   		$this->view->Semester = $semesterAverage;
   	}
   	$db = new Foundation_Model_DbTable_DbScore();
   	$subject =$db->getSubjectScoreByGroup($data['group_id'],null,$data['exam_type']);
   	$this->view->subject = $subject;
   	
   	$frmgb = new Application_Form_FrmGlobal();
   	$this->view->footterPrincipal = $frmgb->getFooterPrincipalSigned($rs['branch_id'],$group_id);
//    	print_r($frmgb->getFooterPrincipalSigned($rs['branch_id'],$group_id));
//    	exit();
   	
   }
   function downloadAction(){
	   	$this->_helper->layout()->disableLayout();
	   	$scoreId =$this->getRequest()->getParam("scoreId");
	   	$stuId =$this->getRequest()->getParam("studentId");
	   	
	   	$data = array(
	   			'scoreId'=>$scoreId,//use
	   			'studentId'=>$stuId,//use and all above not use
	   	);
	   	$dbscore = new Allreport_Model_DbTable_DbScoreTranscript();
	   	$resultData = $dbscore->getTranscriptExam($data);
	   	$this->view->resultData = $resultData;
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
   
   
   function paymentinfoAction(){
   	
	   	$GetData = $this->getRequest()->getParams();
	   	$Stuid=$GetData["id"];
	   	$_dbAction = new Api_Model_DbTable_DbabaApi();
	   	
	   	if($_SERVER['REQUEST_METHOD'] == "GET" AND !empty($Stuid)){
	   		if(!empty($Stuid)){
	   			$studentInfo = $_dbAction->getStudentbyStuID($Stuid);
	   			print_r(json_encode($studentInfo,JSON_UNESCAPED_UNICODE ));
	   		}
	   	}else{
	   		print_r(json_encode($_dbAction->returnBadURL()));
	   	}
	   	exit();
   }
   
   
   function confirmpaymentAction(){
	   
	   	$GetData = $this->getRequest()->getParams();
	   	$Stuid=$GetData["id"];
	   	$_dbAction = new Api_Model_DbTable_DbabaApi();
	   
		  if($this->getRequest()->isPost()){
		  	$postData = $this->getRequest()->getPost();
		  	$paymentInfo = $_dbAction->confirmPayment($postData);
		  	print_r(json_encode($paymentInfo));
		  	
	   	}else{
	   		print_r(json_encode($_dbAction->returnBadURL()));
	   	}
	   	exit();
   }
   
   function confirmpaymentstatusAction(){
   		$bankTranid = $this->getRequest()->getParam('id');
   		$tokendReq = $this->getRequest()->getParam('token');
   		$_dbAction = new Api_Model_DbTable_DbabaApi();

   		if($_SERVER['REQUEST_METHOD'] == "GET" AND !empty($bankTranid)){
   			
	   		if(!empty($bankTranid)){
	   			$confirm = $_dbAction->confirmPaymentStatus($bankTranid,$tokendReq);
	   			print_r(json_encode($confirm));
	   		}
	   	}else{
	   		print_r(json_encode($_dbAction->returnBadURL()));
	   	}
	   	exit();
   	
   }
   function jspdfAction(){
   	
   }
}