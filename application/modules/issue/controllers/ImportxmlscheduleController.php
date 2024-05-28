<?php
class Issue_ImportxmlscheduleController extends Zend_Controller_Action {
	
	const REDIRECT_URL_ADD ='/items/measure/add';
	const REDIRECT_URL_CLOSE ='/items/measure/index';
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			// include  PUBLIC_PATH.'/Classes/PHPExcel/IOFactory.php';
			$db=new Issue_Model_DbTable_DbImportxml();
			if($this->getRequest()->isPost()){
				// $adapter = new Zend_File_Transfer_Adapter_Http();
				// $adapter->receive();
				// $file = $adapter->getFileInfo();
				// $inputFileName = $file['file_xml']['tmp_name'];
				
 				// try {
				// 	$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
				// } catch(Exception $e) {
				// 	die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
				// }
				// $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
				$data = $this->getRequest()->getPost();
				$db->uploadXMLFile($data);
				Application_Form_FrmMessage::Sucessfull("Import Successfully","/issue/importxmlschedule");
			}else{

			}
			
			$frm = new Issue_Form_FrmSchedule();
			$frm->FrmAddSchedule(null);
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_items = $frm;
			
		}catch (Exception $e){
			
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
	}
	function importxmlAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbImportxml();
			$result = $db->importxmlSubject($data);
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
	

	function addAction(){
		$this->_redirect("/issue/importxmlschedule");
	}
	public function editAction(){
		$this->_redirect("/issue/importxmlschedule");
	}
	
	
}

