<?php

class IndexController extends Zend_Controller_Action
{

	const REDIRECT_URL = '/home';
	
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');  
    }

    public function indexAction()
    {
    	
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	$username = $session_user->first_name;
    	$user_id = $session_user->user_id;
    	if (!empty($user_id)){
    		$this->_redirect("/home");
    	}
    	$this->_helper->layout()->disableLayout();
		$form=new Application_Form_FrmLogin();				
		$form->setAction('index');		
		$form->setMethod('post');
		$form->setAttrib('accept-charset', 'utf-8');
		$this->view->form=$form;
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);		
        
		if($this->getRequest()->isPost())		
		{
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$sys = $dbgb->getPh();
			if (!$sys){
// 				$session_user=new Zend_Session_Namespace(SYSTEM_SES);
// 				$session_user->unsetAll();
				Application_Form_FrmMessage::redirectUrl("/");
				exit();
			}
			
			$formdata=$this->getRequest()->getPost();
			if($form->isValid($formdata))
			{
				$session_lang=new Zend_Session_Namespace('lang');
				$session_lang->lang_id=$formdata["lang"];//for creat session
				Application_Form_FrmLanguages::getCurrentlanguage($formdata["lang"]);//for choose lang for when login
				$user_name=$form->getValue('txt_user_name');
				$password=$form->getValue('txt_password');
				$db_user=new Application_Model_DbTable_DbUsers();
				if($db_user->userAuthenticate($user_name,$password)){					
					
					$session_user=new Zend_Session_Namespace(SYSTEM_SES);
					$user_id=$db_user->getUserID($user_name);
					$user_info = $db_user->getUserInfo($user_id);
					
					$arr_acl=$db_user->getArrAcl($user_info['user_type']);
					$a_i = 0;
					$arr_actin = array();
					$arr_module=array();
					for($i=0; $i<count($arr_acl);$i++){
						$arr_module[$i]=$arr_acl[$i]['module'];
					}
					$arr_module=(array_unique($arr_module));
					$arr_actin=(array_unique($arr_actin));
					$arr_module=$this->sortMenu($arr_module);
						
					$session_user->arr_acl = $arr_acl;
					$session_user->arr_module = $arr_module;
					$session_user->arr_actin = $arr_actin;
					
					$session_user->user_id=$user_id;
					$session_user->user_name=$user_name;
					$session_user->pwd=$password;
					$session_user->level= $user_info['user_type'];
					$session_user->last_name= $user_info['last_name'];
					$session_user->first_name= $user_info['first_name'];
					$session_user->branch_id= $user_info['branch_id'];
					$session_user->branch_list= $user_info['branch_list'];
					$session_user->schoolOption= $user_info['schoolOption'];

					$db = new Application_Model_DbTable_DbUsers();
					$creditmemo = $db->getAccessUrl("accounting","creditmemo","index");
					if (empty($creditmemo)){
						$session_user->isnot_creditmemo_acl= 1;
					}
					$lecturer_acl = $db->getAccessUrl("foundation","lecturer","index");
					if (empty($lecturer_acl)){
						$session_user->isnot_lecturer_acl= 1;
					}
					$student_acl = $db->getAccessUrl("foundation","register","index");
					if (empty($student_acl)){
						$session_user->isnot_student_acl= 1;
					}
					$cutstock_acl = $db->getAccessUrl("stock","cutstock","index");
					if (empty($cutstock_acl)){
						$session_user->isnot_cutstock_acl= 1;
					}
					
					$crm_acl = $db->getAccessUrl("home","crm","index");
					if (empty($crm_acl)){
						$session_user->isnot_crm_acl= 1;
					}
					$session_user->timeout= time();
					
					$session_user->lock();
					$log=new Application_Model_DbTable_DbUserLog();
					$log->insertLogin($user_id);
					foreach ($arr_module AS $i => $d){
						$url = self::REDIRECT_URL;
						break;
					}	
					Application_Form_FrmMessage::redirectUrl("/home");	
					exit();										
				}
				else{					
					$this->view->msg = 'ឈ្មោះ​អ្នក​ប្រើ​ប្រាស់ និង ពាក្យ​​សំងាត់ មិន​ត្រឺម​ត្រូវ​ទេ ';
				}	
			}
			else{				
				$this->view->msg = 'លោកអ្នកមិនមានសិទ្ធិប្រើប្រាស់ទេ!';
			}			
		}
		$session_lang=new Zend_Session_Namespace('lang');
		if (empty($session_lang->lang_id)){
			$session_lang->lang_id =1;
		}
		$this->view->rslang = $session_lang->lang_id;
    }
    public function teacherloginAction()
    {
    	$session_user=new Zend_Session_Namespace('authteacher');
    	if (!empty($session_user->teacher_id)){
    		$this->_redirect("/foundation/teacherscore");
    	}
    	$this->_helper->layout()->disableLayout();
    	$form=new Application_Form_FrmLogin();
    	$form->setAction('index');
    	$form->setMethod('post');
    	$form->setAttrib('accept-charset', 'utf-8');
    	$this->view->form=$form;
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    
    	if($this->getRequest()->isPost())
    	{
    		$formdata=$this->getRequest()->getPost();
    			
    		if($form->isValid($formdata))
    		{
    			$session_lang=new Zend_Session_Namespace('lang');
    			$session_lang->lang_id=$formdata["lang"];//for creat session
    			Application_Form_FrmLanguages::getCurrentlanguage($formdata["lang"]);//for choose lang for when login
    			$user_name=$form->getValue('txt_user_name');
    			$password=$form->getValue('txt_password');
    			$db_user=new Global_Model_DbTable_DbTeacher();
    			if($db_user->userAuthenticate($user_name,$password)){
    				
    				$user_id=$db_user->getTeacherid($user_name);
    				$user_info = $db_user->getTeacherInfo($user_id);
    					
    				$session_user->teacher_id=$user_id;
    				$session_user->teacher_name=$user_info['teacher_name_en'];
    				$session_user->branch_id= $user_info['branch_id'];
    				$session_user->branch_list= $user_info['branch_id'];
    				$session_user->schoolOption= $user_info['schoolOption'];
    				$session_user->lock();
//     				$session_user->pwd=$password;
//     				$session_user->level= $user_info['user_type'];
//     				$session_user->last_name= $user_info['last_name'];
//     				$session_user->first_name= $user_info['first_name'];
//     				$session_user->branch_id= $user_info['branch_id'];
//     				$log=new Application_Model_DbTable_DbUserLog();
//     				$log->insertLogin($user_id);
//     				foreach ($arr_module AS $i => $d){
//     					$url = self::REDIRECT_URL;
//     					break;
//     				}
    				Application_Form_FrmMessage::redirectUrl("/issue/teacherscore");
    				exit();
    			}
    			else{
    				$this->view->msg = 'ឈ្មោះ​អ្នក​ប្រើ​ប្រាស់ និង ពាក្យ​​សំងាត់ មិន​ត្រឺម​ត្រូវ​ទេ ';
    			}
    		}
    		else{
    			$this->view->msg = 'លោកអ្នកមិនមានសិទ្ធិប្រើប្រាស់ទេ!';
    		}
    	}
    }
    
    protected function sortMenu($menus){
    	$menus_order = Array ( 'home','test','placement','registrar','foundation','issue','accounting','stock','library','global','mobileapp','allreport','rsvacl','setting');
    	$temp_menu = Array();
    	$menus=array_unique($menus);
    	foreach ($menus_order as $i => $val){
    		foreach ($menus as $k => $v){
    			if($val == $v){
    				$temp_menu[] = $val;
    				unset($menus[$k]);
    				break;
    			}
    		}
    	}
    	return $temp_menu;    	
    }

    public function logoutAction()
    {
        // action body
        if($this->getRequest()->getParam('value')==1){        	
        	$aut=Zend_Auth::getInstance();
        	$aut->clearIdentity();        	
        	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
        	if(!empty($session_user->user_id)){
	        	$log=new Application_Model_DbTable_DbUserLog();
				$log->insertLogout($session_user->user_id);
	        	$session_user->unsetAll();       	
	        	Application_Form_FrmMessage::redirectUrl("/");
	        	exit();
        	}else{
        		$session_teacher=new Zend_Session_Namespace('authteacher');
        		$session_teacher->teacher_id;
        		$session_teacher->unsetAll();
        		Application_Form_FrmMessage::redirectUrl("/index/teacherlogin");
        		exit();
        	}
        } 
    }

    public function changepasswordAction()
    {
        // action body
        if ($this->getRequest()->isPost()){ 
			$session_user=new Zend_Session_Namespace(SYSTEM_SES);    		
    		$pass_data=$this->getRequest()->getPost();
    		if ($pass_data['password'] == $session_user->pwd){
    			    			 
				$db_user = new Application_Model_DbTable_DbUsers();				
				try {
					$db_user->changePassword($pass_data['new_password'], $session_user->user_id);
					$session_user->unlock();	
					$session_user->pwd=$pass_data['new_password'];
					$session_user->lock();
					Application_Form_FrmMessage::Sucessfull('ការផ្លាស់ប្តូរដោយជោគជ័យ', self::REDIRECT_URL);
				} catch (Exception $e) {
					Application_Form_FrmMessage::message('ការផ្លាស់ប្តូរត្រូវបរាជ័យ');
				}				
    		}
    		else{
    			Application_Form_FrmMessage::message('ការផ្លាស់ប្តូរត្រូវបរាជ័យ');
    		}
        }   
    }

    public function errorAction()
    {
        // action body
        
    }
    public function  dashboardAction(){
    	$this->_helper->layout()->disableLayout();
    }
    public static function start(){
    	return ($this->getRequest()->getParam('limit_satrt',0));
    }
    function changelangeAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$session_lang=new Zend_Session_Namespace('lang');
    		$session_lang->lang_id=$data['lange'];
    		Application_Form_FrmLanguages::getCurrentlanguage($data['lange']);
    		print_r(Zend_Json::encode(2));
    		exit();
    	}
    }
    public function barcodeAction(){
    	$loan_code = $this->getRequest()->getParam('codereader');
    	header('Content-type: image/png');
    	$this->_helper->layout()->disableLayout();
    	$barcodeOptions = array('text' => $loan_code,'barHeight' => 11,'drawText' => false);
    	$rendererOptions = array();
    	$renderer = Zend_Barcode::factory('Code128', 'image', $barcodeOptions, $rendererOptions)->render();
    	echo $renderer; exit();
    }
    
    public function loginAction()
    {
    	$session_user=new Zend_Session_Namespace(SUTUDENT_SESSION);
    	$user_id = $session_user->student_id;
    	if (!empty($user_id)){
    		$this->_redirect("/index/home");
    	}
    	
    	$this->_helper->layout()->disableLayout();
    	$form=new Application_Form_FrmLogin();
    	$form->setAction('login');
    	$form->setMethod('post');
    	$form->setAttrib('accept-charset', 'utf-8');
    	$this->view->form=$form;
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    
    	if($this->getRequest()->isPost())
    	{
    		$dbgb = new Application_Model_DbTable_DbGlobal();
    		$sys = $dbgb->getPh();
    		if (!$sys){
    			Application_Form_FrmMessage::redirectUrl("/");
    			exit();
    		}
    		$formdata=$this->getRequest()->getPost();
    		if($form->isValid($formdata))
    		{
    			$session_lang=new Zend_Session_Namespace('lang');
    			$session_lang->lang_id=$formdata["lang"];//for creat session
    			Application_Form_FrmLanguages::getCurrentlanguage($formdata["lang"]);//for choose lang for when login
    			$user_name=$form->getValue('txt_user_name');
    			$password=$form->getValue('txt_password');
    			
    			$db_user=new Application_Model_DbTable_DbUsers();
    			if($db_user->userAuthenticateStudentTest($user_name,$password)){
    				$session_user=new Zend_Session_Namespace(SUTUDENT_SESSION);
    				$studnet_info = $db_user->getStudentInfo($user_name,$password);

    				$session_user->branch_id= $studnet_info['branch_id'];
    				$session_user->student_id= $studnet_info['stu_id'];
    				$session_user->serial=$user_name;
    				$session_user->pwd=$password;
    				$session_user->stu_khname= $studnet_info['stu_khname'];
    				$session_user->stu_enname= $studnet_info['stu_enname'];
    				$session_user->last_name= $studnet_info['last_name'];
    				$session_user->test_type= $studnet_info['test_type'];
    				$session_user->test_setting_id= $studnet_info['test_setting_id'];
    				$session_user->exam_id = 0;
    				
    				Application_Form_FrmMessage::redirectUrl("/index/home");
    				exit();
    			}
    			else{
    				$this->view->msg = 'ឈ្មោះ​អ្នក​ប្រើ​ប្រាស់ និង ពាក្យ​​សំងាត់ មិន​ត្រឺម​ត្រូវ​ទេ ';
    			}
    		}
    		else{
    			$this->view->msg = 'លោកអ្នកមិនមានសិទ្ធិប្រើប្រាស់ទេ!';
    		}
    	}
    	$session_lang=new Zend_Session_Namespace('lang');
    	if (empty($session_lang->lang_id)){
    		$session_lang->lang_id =2;
    	}
    	$this->view->rslang = $session_lang->lang_id;
    }
    public function signoutAction()
    {
    	if($this->getRequest()->getParam('value')==1){
    		$session_user=new Zend_Session_Namespace(SUTUDENT_SESSION);
    		if(!empty($session_user->student_id)){
    			$session_user->unsetAll();
    			Application_Form_FrmMessage::redirectUrl("/index/login");
    			exit();
    		}
    	}
    }
    public function testexamAction()
    {
    	$session_user=new Zend_Session_Namespace(SUTUDENT_SESSION);
    	$test_type = $session_user->test_type;
    	$test_setting_id = $session_user->test_setting_id;
    	$test_setting_id = empty($test_setting_id)?0:$test_setting_id;
    	$exam_id = $session_user->exam_id;
    	$user_id = $session_user->student_id;
    	if (empty($user_id)){
    		$this->_redirect("/index/login");
    	}
    	if (empty($exam_id)){
    		$this->_redirect("/index/home");
    	}
    	
    	$exam_id = empty($exam_id)?0:$exam_id;
    	 
    	$_data = array('test_setting_id'=>$test_setting_id);
    	
    	$_dbgb= new Application_Model_DbTable_DbGlobal();
    	$this->view->question=$_dbgb->getAllQuestionBySettingExam($_data);
    	$this->view->truefalse_opt=$_dbgb->getOptionTrueFalse(1);
    	
    	$_dbpl= new Application_Model_DbTable_DbPlacementTest();
    	$exam = $_dbpl->getStartPlacementTest($exam_id);
    	$this->view->exam = $exam;
    	$this->view->exam_id = $exam_id;
    	$this->view->setting = $_dbpl->getPlacementSetting($test_setting_id);
    	
    	$startTime  = date("Y-m-d H:i:s",strtotime($exam['start'])); //"2019-10-01 15:52:12";
    	$duration = empty($exam['duration'])?1:number_format($exam['duration'],0);
    	$endTiem = date("Y-m-d H:i:s",strtotime("+$duration min $startTime"));
    	
    	if((time()-(60*60*24)) > strtotime($endTiem)){
    		$this->_redirect("/index/complete");
    	}
    	
    	$branch_id = empty($session_user->branch_id)?1:$session_user->branch_id;
    	$frm = new Application_Form_FrmGlobal();
    	$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
    }
    
    public function homeAction()
    {
    	$session_user=new Zend_Session_Namespace(SUTUDENT_SESSION);
    	$test_type = $session_user->test_type;
    	$test_setting_id = $session_user->test_setting_id;
    	$test_setting_id = empty($test_setting_id)?0:$test_setting_id;
    	$user_id = $session_user->student_id;
    	$exam_id = $session_user->exam_id;
    	if (empty($user_id)){
    		$this->_redirect("/index/login");
    	}
    	if (!empty($exam_id)){
    		$this->_redirect("/index/testexam");
    	}
    	$_dbpl= new Application_Model_DbTable_DbPlacementTest();
    	$this->view->setting = $_dbpl->getPlacementSetting($test_setting_id);
    	$this->view->settingDetail = $_dbpl->getPlacementSettingDetail($test_setting_id);
    	
    	$total_point = $_dbpl->getTotalScoreExam($test_setting_id);
    	$this->view->totalPoint = $total_point;
    	
    	$branch_id = empty($session_user->branch_id)?1:$session_user->branch_id;
    	$frm = new Application_Form_FrmGlobal();
    	$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
    }
    function startexamAction(){
    	$session_user=new Zend_Session_Namespace(SUTUDENT_SESSION);
    	$test_type = $session_user->test_type;
    	$test_setting_id = $session_user->test_setting_id;
    	$test_setting_id = empty($test_setting_id)?0:$test_setting_id;
    	$user_id = $session_user->student_id;
    	if (empty($user_id)){
    		$this->_redirect("/index/login");
    	}
    	
    	$_dbpl= new Application_Model_DbTable_DbPlacementTest();
    	$setting = $_dbpl->getPlacementSetting($test_setting_id);
    	$exam = $_dbpl->startPlacementTest($setting);
    	
    	$session_user->unlock();
    	$session_user->exam_id = $exam;
    	$session_user->lock();
    	$this->_redirect("/index/testexam");
    	exit();
    }
    function enteranswerAction(){
    	if($this->getRequest()->isPost()){
    		try{
    			$data = $this->getRequest()->getPost();
    			
    			$session_user=new Zend_Session_Namespace(SUTUDENT_SESSION);
    			$test_type = $session_user->test_type;
    			$test_setting_id = $session_user->test_setting_id;
    			$test_setting_id = empty($test_setting_id)?0:$test_setting_id;
    			$exam_id = $session_user->exam_id;
    			$exam_id = empty($exam_id)?0:$exam_id;
    			
    			$data['placemnet_id'] = $exam_id;
    			$db = new Application_Model_DbTable_DbPlacementTest();
    			$result = $db->enterPlacementTestAnswer($data);
    			print_r(Zend_Json::encode($result));
    			exit();
    		}catch(Exception $e){
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			
    		}
    	}
    }
    function completeAction(){
    	$session_user=new Zend_Session_Namespace(SUTUDENT_SESSION);
    	$test_type = $session_user->test_type;
    	$test_setting_id = $session_user->test_setting_id;
    	$test_setting_id = empty($test_setting_id)?0:$test_setting_id;
    	$exam_id = $session_user->exam_id;
    	$user_id = $session_user->student_id;
    	if (empty($user_id)){
    		$this->_redirect("/index/login");
    	}
    	if (empty($exam_id)){
    		$this->_redirect("/index/home");
    	}
    	
    	$_dbpl= new Application_Model_DbTable_DbPlacementTest();
    	$exam = $_dbpl->completePlacementTest($exam_id);
    	
    	$session_user->unlock();
    	$session_user->exam_id = 0;
    	$session_user->lock();
    	$this->_redirect("/index/review/id/".$exam);
    	exit();
    }
	function examhistoryAction(){
		$session_user=new Zend_Session_Namespace(SUTUDENT_SESSION);
		$test_type = $session_user->test_type;
		$test_setting_id = $session_user->test_setting_id;
		$test_setting_id = empty($test_setting_id)?0:$test_setting_id;
		$exam_id = $session_user->exam_id;
		$user_id = $session_user->student_id;
		if (empty($user_id)){
			$this->_redirect("/index/login");
		}
		
		$_dbpl= new Application_Model_DbTable_DbPlacementTest();
		$row = $_dbpl->getExamHistoryStudent();
		if (empty($row)){
			Application_Form_FrmMessage::messageAlert("No Record","/index/home");
			exit();
		}
		$this->view->row = $row;
		
		$branch_id = empty($session_user->branch_id)?1:$session_user->branch_id;
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
	}
	
	function reviewAction(){
		
		$id = $this->getRequest()->getParam("id");
		
		$session_user=new Zend_Session_Namespace(SUTUDENT_SESSION);
		$user_id = $session_user->student_id;
		if (empty($user_id)){
			$this->_redirect("/index/login");
		}
		if (empty($id)){
			$this->_redirect("/index/home");
		}
		$exam_id = empty($id)?0:$id;
		$this->view->exam_id = $exam_id;
		
		
		 
		$_dbpl= new Application_Model_DbTable_DbPlacementTest();
		$_dbgb= new Application_Model_DbTable_DbGlobal();
		
		$row = $_dbpl->getPlacementTestbyId($exam_id);
		$test_setting_id = empty($row['placement_setting_id'])?0:$row['placement_setting_id'];
		
		$_data = array('test_setting_id'=>$test_setting_id);
		$this->view->question=$_dbgb->getAllQuestionBySettingExam($_data);
		$this->view->truefalse_opt=$_dbgb->getOptionTrueFalse(1);
	
		$this->view->setting = $_dbpl->getPlacementSetting($test_setting_id);
		 
		
		$total_point = $_dbpl->getTotalScoreExam($test_setting_id);
		$this->view->totalPoint = $total_point;
		$this->view->result_score = $_dbpl->getTotalScoreResult($exam_id);
		
		$branch_id = empty($session_user->branch_id)?1:$session_user->branch_id;
		$frm = new Application_Form_FrmGlobal();
		$this->view-> rsheader = $frm->getLetterHeaderReport($branch_id);
	}
	
	public function uiAction()
	{
		 $this->_helper->layout()->disableLayout();
	}
	public function scanningAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$qr_serial = empty($data['students'])?0:$data['students'];
			$dbgb = new Application_Model_DbTable_DbGlobal();
	
			$check = $dbgb->getStudentinfoById($qr_serial);
			if (!empty($check)){
				$string = $dbgb->resultScan($qr_serial);
				print_r(Zend_Json::encode($string));
				exit();
// 				return $string;
			}else{
				$return =0;
			}
			print_r(Zend_Json::encode($return));
			exit();
		}
	}
	
	public function reloadrAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$session_user=new Zend_Session_Namespace(SYSTEM_SES);
			$session_user->timeout= time();
			print_r(Zend_Json::encode($session_user->timeout));
			exit();
		}
	}
	public function sessioncheckAction(){
	
		if($this->getRequest()->isPost()){
			$session_user=new Zend_Session_Namespace(SYSTEM_SES);
			$t = time();
			$t0 = $session_user->timeout;
			$diff = $t - $t0;
			//500 = 5 min
			if ($diff > 1000 || !isset($t0))
			{
				$session_user->unsetAll();
			}
				
			$db_global = new Application_Model_DbTable_DbGlobal();
			$checkses = $db_global->checkSessionExpire();
			if (empty($checkses)){
				echo true; exit();
			}
			echo false; exit();
		}
	}
}





