<?php
class Teacherapi_IndexController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/teacherapi/index';
	
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
    	$_dbAction = new Teacherapi_Model_DbTable_DbActions();
    	$GetData = $this->getRequest()->getParams();
		
		$session_lang=new Zend_Session_Namespace('lang');
		$session_lang->lang_id=empty($GetData['currentLang'])?1:$GetData['currentLang'];
			
    	if ($_SERVER['REQUEST_METHOD'] == "GET"){
    		if($GetData['url']=="profile"){
    			$_dbAction->profileAction($GetData);
    		}else if ($GetData['url']=="teachingSchedule"){
    			$_dbAction->getTeachingScheduleAction($GetData);
			}else if ($GetData['url']=="issueScoreList"){
    			$_dbAction->getIssueScoreListAction($GetData);
				
			}else if ($GetData['url']=="scoreForIssue"){
    			$_dbAction->getScoreAvailableForIssueAction($GetData);
			}else if ($GetData['url']=="subjectCriteriaForIssue"){
    			$_dbAction->getSubjectCriteriaOfClassAction($GetData);
			}else if ($GetData['url']=="studentLists"){
    			$_dbAction->getStudentListOfClassAction($GetData);
			}else if ($GetData['url']=="criteriaScoreLists"){
    			$_dbAction->getCriteriaScoreListAction($GetData);
			}else if ($GetData['url']=="infoForMonthlyScore"){
    			$_dbAction->getInfoForMonthlyScoreAction($GetData);
			
			}else if ($GetData['url']=="groupMonthlyScoreDetail"){
    			$_dbAction->getGroupMonthlyScoreDetailAction($GetData);
				
			}else if ($GetData['url']=="optionAcademicYear"){
				$GetData['getControlType'] = "academicYear";
    			$_dbAction->getFormOptionSelectAction($GetData);
			}else if ($GetData['url']=="optionGroup"){
				$GetData['getControlType'] = "teachingGroup";
    			$_dbAction->getFormOptionSelectAction($GetData);
			}else if ($GetData['url']=="optionDegree"){
				$GetData['getControlType'] = "teachingDegree";
    			$_dbAction->getFormOptionSelectAction($GetData);
			}else if ($GetData['url']=="optionSubject"){
				$GetData['getControlType'] = "teachingSubject";
    			$_dbAction->getFormOptionSelectAction($GetData);
			}else if ($GetData['url']=="optionRating"){
				$GetData['getControlType'] = "ratingOption";
    			$_dbAction->getFormOptionSelectAction($GetData);
				
			}else if ($GetData['url']=="countingClass"){
    			$_dbAction->getCountingClassAction($GetData);
			}else if ($GetData['url']=="teachingClassList"){
    			$_dbAction->getTeachingClassListAction($GetData);
			}else if ($GetData['url']=="scoreResultClass"){
    			$_dbAction->getScoreResultOfClassAction($GetData);
				
			}else if ($GetData['url']=="classForEvaluation"){
    			$_dbAction->getClassAvailableForEvaluationAction($GetData);
			}else if ($GetData['url']=="evaluationComment"){
    			$_dbAction->getEvaluationCommentByDegreeAction($GetData);
			}else if ($GetData['url']=="assessmentList"){
    			$_dbAction->getAssessmentListOfClassAction($GetData);
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
				}else if ($GetData['url']=="submitIssueCriteriaScore"){
    				$_dbAction->submitCriteriaScoreAction($postData);
				}else if ($GetData['url']=="submitEditCriteriaScore"){
    				$_dbAction->submitEditCriteriaScoreAction($postData);
				}else if ($GetData['url']=="submitMonthlyScore"){
    				$_dbAction->submitMonthlyScoreAction($postData);
				}else if ($GetData['url']=="submitEvaluationStudent"){
    				$_dbAction->submitEvaluationStudentAction($postData);
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
}