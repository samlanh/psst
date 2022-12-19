<?php
class Stock_StockclosingController extends Zend_Controller_Action {
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$rs_rows=array();
		$db = new Stock_Model_DbTable_DbClosingStock();
		try{
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'title' => '',
    				'branch_id'=>'',
					'adjustDate'=>'',
    				'start_date'=> date('Y-m-d'),
    				'end_date'=>date('Y-m-d'),
				);
			}
			
			$rs_rows= $db->getAllClosingStock($search);
			
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			$this->view->search = $search;
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","CLOSING_DATE","NOTE","ADJUST_DATE","BY_USER");
			$link=array('module'=>'stock','controller'=>'stockclosing','action'=>'index');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('projectName'=>$link,'adjustDate'=>$link,
			'user_name'=>$link,'status'=>$link));
	
			$form=new Accounting_Form_FrmSearchProduct();
			$form=$form->FrmSearchProduct();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
			$model = new Application_Model_DbTable_DbGlobal();
			$branch = $model->getAllBranchName();
			$this->view->branchopt = $branch;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
					$db = new Stock_Model_DbTable_DbClosingStock();
					$row = $db->addClosingStock($_data);
					if(isset($_data['save_close'])){
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stock/stockclosing");
					}else{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stock/stockclosing/add");
					}
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}catch(Exception $e){
					Application_Form_FrmMessage::message("INSERT_FAIL");
					Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				}
			}

			$model = new Application_Model_DbTable_DbGlobal();
			$branch = $model->getAllBranchName();
			$this->view->branchopt = $branch;	
	}

	function getadjustlistAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_com = new Stock_Model_DbTable_DbAdjustStock();
			$result = $db_com->getAllAdjusted($data);	
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();	
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
    
   
}