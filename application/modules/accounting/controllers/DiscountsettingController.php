<?php
class Accounting_DiscountSettingController extends Zend_Controller_Action
{
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL') || define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}

	public function indexAction()
	{
		try {
			if ($this->getRequest()->isPost()) {
				$search = $this->getRequest()->getPost();
			} else {
				$search = array(
					'title' => '',
					'academic_year' => '0',
					'branch' => '',
					'studentId' => '',
					'discountId' => '',
					'discountFor' => '0',
					'discountPeriod' => '0',
					'status_search' => -1
				);
			}
			$db = new Accounting_Model_DbTable_DbDiscountSetting();
			$rs_rows = $db->getAllDiscountset($search);

			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH", "ACADEMIC_YEAR", "TITLE", "DISCOUNT_FOR_TYPE", "DISCOUNT_OPTION", "COURSE_SERVICE_PRODUCT", "DISCOUNT_TYPE", "DIS_MAX", "DISCOUNT_PERIOD", "BY_USER", "CREATE_DATE", "STATUS");
			$link = array(
				'module' => 'accounting',
				'controller' => 'discountsetting',
				'action' => 'edit',
			);
			$this->view->list = $list->getCheckList(
				0,
				$collumns,
				$rs_rows,
				array(
					'branch' => $link,
					'academicYear' => $link,
					'discountTitle' => $link,
					'discountForText' => $link,
					'studentName' => $link,
					'discountForOption' => $link
				)
			);
		} catch (Exception $e) {
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}

		$this->view->adv_search = $search;
		$frm = new Global_Form_FrmSearchMajor();
		$frms = $frm->FrmsearchDiscount();
		Application_Model_Decorator::removeAllDecorator($frms);
		$this->view->form_search = $frms;

		$model = new Application_Model_DbTable_DbGlobal();
		$disc = $model->getAllDiscount();
		$this->view->discount = $disc;
	}
	public function addAction()
	{
		if ($this->getRequest()->isPost()) {
			$_data = $this->getRequest()->getPost();
			try {
				$sms = "INSERT_SUCCESS";
				$_dbmodel = new Accounting_Model_DbTable_DbDiscountSetting();
				$_discount = $_dbmodel->addNewDiscountset($_data);
				if ($_discount == -1) {
					$sms = "RECORD_EXIST";
				}
				if (isset($_data['save_close'])) {
					Application_Form_FrmMessage::Sucessfull($sms, "/accounting/discountsetting");
				} else {
					Application_Form_FrmMessage::Sucessfull($sms, "/accounting/discountsetting/add");
				}
				Application_Form_FrmMessage::message($sms);

			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$model = new Application_Model_DbTable_DbGlobal();
		$disc = $model->getAllDiscount();
		array_unshift($disc, array('id' => -1, 'name' => $this->tr->translate("ADD_NEW")));
		$this->view->discount = $disc;

		$this->view->itemType = $model->getAllItems();

		$tsub = new Accounting_Form_FrmDiscount();
		$frm_discount = $tsub->FrmDiscountsetting();
		Application_Model_Decorator::removeAllDecorator($frm_discount);
		$this->view->frm_discount = $frm_discount;

		$this->view->rsdegree = $model->getAllDegreeName();
	}
	public function editAction()
	{
		$id = $this->getRequest()->getParam("id");
		$id = empty($id) ? 0 : $id;
		if ($this->getRequest()->isPost()) {
			$_data = $this->getRequest()->getPost();
			$_data['id'] = $id;
			try {
				$data = $this->getRequest()->getPost();
				$db = new Accounting_Model_DbTable_DbDiscountSetting();
				$db->updateDiscountset($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/accounting/discountsetting/index");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db = new Accounting_Model_DbTable_DbDiscountSetting();
		$rows = $db->getDiscountsetById($id);
		if (empty($rows)) {
			Application_Form_FrmMessage::Sucessfull("No Record", "/accounting/discountsetting");
			exit();
		}
		$this->view->rs = $rows;

		$model = new Application_Model_DbTable_DbGlobal();
		$dis = $model->getAllDiscount();
		$this->view->discount = $dis;
		$this->view->rsdegree = $model->getAllDegreeName();

		$this->view->itemType = $model->getAllItems();

		$tsub = new Accounting_Form_FrmDiscount();
		$frm_discount = $tsub->FrmDiscountsetting($rows);
		Application_Model_Decorator::removeAllDecorator($frm_discount);
		$this->view->frm_discount = $frm_discount;
	}
	function addcompositionAction()
	{
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbDiscountSetting();
			$id = $db->addDiscounttionset($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	function adddiscountAction()
	{
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbDiscountSetting();
			$gty = $db->addNewDiscountPopup($data);
			print_r(Zend_Json::encode($gty));
			exit();
		}
	}

}

