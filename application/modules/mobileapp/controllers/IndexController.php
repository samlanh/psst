<?php

class Mobileapp_IndexController extends Zend_Controller_Action
{
    public function init()
    {    	
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	
	}

    public function indexAction()
    {
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$date = new DateTime();
		$currentDate = $date->format('Y-m-d');
    	$search = array(
						'dateFiltering' => $currentDate,
					);
					
		$db = new Mobileapp_Model_DbTable_DbDashboard();
		$countingDownloaded = $db->getCountingDownloadedDevice($search);
		$this->view->countingDownloaded = $countingDownloaded;
		
		$iosDeviceDownloaded = empty($countingDownloaded["iosDeviceDownloaded"]) ? "00" : sprintf('%02d',$countingDownloaded["iosDeviceDownloaded"]);
		$androidDeviceDownloaded = empty($countingDownloaded["androidDeviceDownloaded"]) ? "00" : sprintf('%02d',$countingDownloaded["androidDeviceDownloaded"]);
		$this->view->summaryDevice = array(
				array("label"=>$tr->translate("IOS"),"value"=>$iosDeviceDownloaded,"color"=>"#02a687"),
				array("label"=>$tr->translate("ANDROID"),"value"=>$androidDeviceDownloaded,"color"=>"#db8806"),
			);
			
		$countingUser = $db->getCountingUserAccount($search);
		$countStudent = empty($countingUser["countStudent"]) ? 0 : $countingUser["countStudent"];
		$countTeacher = empty($countingUser["countTeacher"]) ? 0 : $countingUser["countTeacher"];
		$countSchoolBus = empty($countingUser["countSchoolBus"]) ? 0 : $countingUser["countSchoolBus"];
		$countUnknow = empty($countingUser["countUnknow"]) ? 0 : $countingUser["countUnknow"];
		$totalAccount=$countStudent+$countTeacher+$countSchoolBus+$countUnknow;
		$countingUser["totalAccount"] = $totalAccount;
		
		$this->view->countingUser =$countingUser;
		$this->view->summaryAccount = array(
				array("label"=>$tr->translate("STUDENT"),"value"=>$countStudent,"color"=>"#113a90"),
				array("label"=>$tr->translate("TEACHER"),"value"=>$countTeacher,"color"=>"#f32f2f"),
				array("label"=>$tr->translate("BUS"),"value"=>$countSchoolBus,"color"=>"#42ae5f"),
				array("label"=>$tr->translate("Unknow"),"value"=>$countUnknow,"color"=>"#a9bdc8"),
			);
			
		$search["tokenType"] = 1;
		$deviceRs = $db->getDeviceAndAccountInfo($search);
		
		$this->view->deviceRs =$deviceRs;
		//$rs = $db->updateDeviceInfo($deviceRs);
		
		//$deviceRs = $db->getDeviceAndAccountInfo($search);
		//$this->view->deviceRs =$deviceRs;
			
    }

}







