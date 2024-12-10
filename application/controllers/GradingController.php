<?php

class GradingController extends Zend_Controller_Action
{

	const REDIRECT_URL = '/external';
	
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');  
    }
	public function indexAction()
	{
		$this->_helper->layout()->disableLayout();
		$id=0;
		
		$dbExternal = new Application_Model_DbTable_DbExternal();
		
		$teacherInfo = $dbExternal->getCurrentTeacherInfo();
		$currentAcademic = empty($teacherInfo['currentAcademic'])?0:$teacherInfo['currentAcademic'];

		$groupId=$this->getRequest()->getParam("id");
		$groupId = empty($groupId)?0:$groupId;
		$this->view->groupId = $groupId;

		$criteriaId=$this->getRequest()->getParam("criteriaId");
		$criteriaId = empty($criteriaId)?'':$criteriaId;
		$this->view->criteriaId = $criteriaId;

		$subjectId=$this->getRequest()->getParam("subjectId");
		$subjectId = empty($subjectId)?'':$subjectId;
		$this->view->subjectId = $subjectId;

		$settingEntryId=$this->getRequest()->getParam("settingEntryId");
		$this->view->settingEntryId = $settingEntryId;

		$examType = 0;
		$forMonth = 0;
		$forSemester = 0;
        if(!empty($settingEntryId)){ 
			$dbScoreSetting = new Issuesetting_Model_DbTable_DbScoreEntrySetting();
    		$rs = $dbScoreSetting->getScoreEntrySettingById($settingEntryId);
			if(!empty($rs)){
				$examType = $rs["examType"];
				$forMonth = $rs["forMonth"];
				$forSemester = $rs["forSemester"];
			}
			$this->view->settingEntryRow= $rs;
		}
		
		
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
			$search['externalAuth']=1;
		}
		else{
			
			$search = array(
				'adv_search'=>'',
				'externalAuth'=>1,//for teacher access
				'academic_year'=> $currentAcademic,
				'exam_type'=>$examType,
				'for_semester'=>$forSemester ,
				'for_month'=>$forMonth,
				'degree'=>0,
				'grade'=> 0,
				'group'=> $groupId ,
				'subjectId'=> $subjectId ,
				'criteriaId'=>$criteriaId,
				'start_date'=> '',
				'end_date'=>date('Y-m-d')
			);
		}
		$this->view->search = $search;
		$db = new Application_Model_DbTable_DbGradingScore();
		$row = $db->getAllGradingScore($search);
		$this->view->row = $row;
		$form=new Application_Form_FrmSearchGlobal();
		$forms=$form->FrmSearch();
		Application_Model_Decorator::removeAllDecorator($forms);
		$this->view->form_search=$form;
		
	}
    public function addAction()
	{
		$this->_helper->layout()->disableLayout();
		$key = new Application_Model_DbTable_DbKeycode();
		$dbset=$key->getKeyCodeMiniInv(TRUE);
		$db = new Application_Model_DbTable_DbGradingScore();
		$dbg = new Application_Model_DbTable_DbExternal();

		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			
			$checkTeachSesion=  $dbg->checkSessionTeacherExpireBeforeSubmit();
			if(empty($checkTeachSesion)){
				$dbg->reloadPageTecherExpireSession();
				exit();
			}

			try {
				$rs = $db->addScoreGradingByClass($_data);
				
				/*
				$dbPush = new Api_Model_DbTable_DbPushNotification();
				$gradingTmpId = empty($rs) ? 0 : $rs;
				$notify = array(
					"typeNotify" => "criteriaStudentScore",
					"optNotification" => 3,
					"notificationId" => $gradingTmpId,
					"studentId" => 3,
				);
				
				$stTmpScore = $dbPush->getGradingTmpStudentList($gradingTmpId);
				if(!empty($stTmpScore)) foreach($stTmpScore as $st){
					$notify["studentId"] = empty($st["studentId"]) ? 0 : $st["studentId"];
					$title = $st["criteriaTitleKh"]." នៃមុខវិជ្ជា ".$st["subjectTitleKh"]." ថ្នាក់ ".$st["groupCode"];
					$title = $title." - ".$st["criteriaTitle"]." Of ".$st["subjectTitle"]." Class ".$st["groupCode"];
					$subTitle = "ពិន្ទូទទូលបាន ".$st["totalGrading"]." ពិន្ទុ លើ".$st["criteriaTitleKh"]." នៃមុខវិជ្ជា ".$st["subjectTitleKh"]."។";
					$subTitle = $subTitle." Score ".$st["totalGrading"]." Pt(s) for ".$st["criteriaTitle"]." Of ".$st["subjectTitle"].".";
					
					$notify["title"] = $title;
					$notify["subTitle"] = $subTitle;
					$dbPush->pushNotificationAPI($notify);	
				}
				*/
				
				if(isset($_data['save_new'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/grading/add");
				}else {
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/grading/index");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;

		$criteriaId=$this->getRequest()->getParam("criteriaId");
		$criteriaId = empty($criteriaId)?0:$criteriaId;
		$this->view->criteriaId = $criteriaId;

		$subjectId=$this->getRequest()->getParam("subjectId");
		$subjectId = empty($subjectId)?'':$subjectId;
		$this->view->subjectId = $subjectId;

		$settingEntryId=$this->getRequest()->getParam("settingEntryId");
		$this->view->settingEntryId = $settingEntryId;
		if(!empty($settingEntryId)){
			$dbScoreSetting = new Issuesetting_Model_DbTable_DbScoreEntrySetting();
			$rs = $dbScoreSetting->getScoreEntrySettingById($settingEntryId);
			$this->view->settingEntryRow= $rs;
		}
		
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$row = $dbExternal->getGroupDetailByIDExternal($id,1);
		
		if(empty($settingEntryId)){
			$this->_redirect("/external/group");
		}
		
		$this->view->row = $row;
		if(empty($row)){
			$this->_redirect("/external/group");
		}
		$arrayChecking = array(
				'settingEntryId'=>$settingEntryId,
				'groupId'=>$id,
				'subjectId'=>$subjectId
				);
		$checking = $dbExternal->checkingExaminationSubject($arrayChecking);
		if(!empty($checking)){
			Application_Form_FrmMessage::Sucessfull("Issued Examination Ready","/external/group");
		}
		
		$this->view-> month = $dbExternal->getAllMonth();
	
		$gradingId = $row['gradingId'];
		$array = array(
				'gradingId'=>$gradingId,
				'subjectId'=>$subjectId
				);
		$result = $dbExternal->getGradingCriteriaItems($array);

		$this->view->criteria = $result;

	}
	public function editAction()
	{
		$this->_helper->layout()->disableLayout();
		$key = new Application_Model_DbTable_DbKeycode();
		$dbg = new Application_Model_DbTable_DbGlobal();
		$dbset=$key->getKeyCodeMiniInv(TRUE);
		$db = new Application_Model_DbTable_DbGradingScore();
		$dbexnternal = new Application_Model_DbTable_DbExternal();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();

			$checkTeachSesion=  $dbexnternal->checkSessionTeacherExpireBeforeSubmit();
			if(empty($checkTeachSesion)){
				$dbexnternal->reloadPageTecherExpireSession();
				exit();
			}

			try{
				$rs = $db->UpdateScoreGradingByClass($_data);
				
				$dbPush = new Api_Model_DbTable_DbPushNotification();
				$gradingTmpId = empty($_data['recordId']) ? 0 : $_data['recordId'];
				$notify = array(
					"typeNotify" => "criteriaStudentScore",
					"optNotification" => 3,
					"notificationId" => $gradingTmpId,
					"studentId" => 3,
				);
				
				$stTmpScore = $dbPush->getGradingTmpStudentList($gradingTmpId);
				if(!empty($stTmpScore)) foreach($stTmpScore as $st){
					$notify["studentId"] = empty($st["studentId"]) ? 0 : $st["studentId"];
					$title = $st["criteriaTitleKh"]." នៃមុខវិជ្ជា ".$st["subjectTitleKh"]." ថ្នាក់ ".$st["groupCode"];
					$title = $title." - ".$st["criteriaTitle"]." Of ".$st["subjectTitle"]." Class ".$st["groupCode"];
					$subTitle = "ពិន្ទូទទូលបាន ".$st["totalGrading"]." ពិន្ទុ លើ".$st["criteriaTitleKh"]." នៃមុខវិជ្ជា ".$st["subjectTitleKh"]."។";
					$subTitle = $subTitle." Score ".$st["totalGrading"]." Pt(s) for ".$st["criteriaTitle"]." Of ".$st["subjectTitle"].".";
					
					$notify["title"] = $title;
					$notify["subTitle"] = $subTitle;
					$dbPush->pushNotificationAPI($notify);	
				}
				
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/grading/index");				
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$gradingRowId=$this->getRequest()->getParam("gradingRowId");
		$gradingRowId = empty($gradingRowId)?0:$gradingRowId;
		
		$resultRecord = $db->getGradingScoreById($gradingRowId,$fullControlID=NULL);
		if(empty($resultRecord)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/grading/index");
		}
	
		$rscoreType = $dbg->checkScoreType($resultRecord);
		if(!empty($rscoreType)){
			if($rscoreType['isLock']==1){
				Application_Form_FrmMessage::Sucessfull("Can not Edit, Already Used !","/grading/index");
			}elseif($resultRecord['criteriaType'] != 2 ){
				Application_Form_FrmMessage::Sucessfull("Can not Edit, Already Used !","/grading/index");
			}
			
		}

		$this->view->resultRecord = $resultRecord;
		$this->view->gradingRowId = $gradingRowId;
		
		
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
	
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$row = $dbExternal->getGroupDetailByIDExternal($id,1);
	
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/dashboard");
			exit();
		}
	
		$this->view->row = $row;
		if(empty($row)){
			$this->_redirect("/external/group");
		}
		$this->view-> month = $dbExternal->getAllMonth();
	
		$degreeId = $row['degree_id'];
		$result = $dbg->checkEntryScoreSetting($degreeId);
		if(empty($result)){
			Application_Form_FrmMessage::Sucessfull("NO_PERMISSION_TO_ENTRY","/grading/index");
		}
		$gradingId = $row['gradingId'];
		$array = array(
				'gradingId'=>$gradingId
		);
		$result = $dbExternal->getGradingCriteriaItems($array);
		$this->view->criteria = $result;
	}
	function getStudentsingleengryAction(){//single entry by criteria
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGradingScore();
			$data['sortStundent']=empty($data['sortStundent'])?0:$data['sortStundent'];
			$rs=$db->getStudentForGradingScore($data);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	

}