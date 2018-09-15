<?php
class Registrar_Model_DbTable_DbExpense extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_expense';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	}
	function addExpense($data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			$_arr = array(
					'branch_id'		=>$data['branch_id'],
					'title'			=>$data['title'],
					'total_amount'	=>$data['total_amount'],
					'invoice'		=>$data['invoice'],
					'payment_type'	=>$data['payment_method'],
					'description'	=>$data['Description'],
					'receiver'		=>$data['receiver'],
					'cheque_no'		=>$data['cheque_num'],
					'date'			=>$data['Date'],
					'status'		=>$data['Stutas'],
					'user_id'		=>$this->getUserId(),
					'create_date'	=>date('Y-m-d H:i:s'),
				);
			$expend_id = $this->insert($_arr);
			$ids = explode(',', $data['identity']);
			$this->_name='ln_expense_detail';
			foreach ($ids as $j){
				$arr = array(
						'expense_id'	=>$expend_id,
						'service_id'	=>$data['expense_id'.$j],
						'description'	=>$data['remark'.$j],
						'price_pain'	=>$data['price_pain'.$j],
						'qty_pain'		=>$data['qty_pain'.$j],
						'total_pain'	=>$data['total_pain'.$j],
					);
			   $this->insert($arr);
			}
			$_db->commit();
		}catch(Exception $e){
			$_db->rollBack();
			echo $e->getMessage();exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		//print_r($data); exit();
 }
 function updatExpense($data){
 	$_db= $this->getAdapter();
 	$_db->beginTransaction();
 	try{
	$arr = array(	'branch_id'		=>$data['branch_id'],
					'branch_id'		=>$data['branch_id'],
					'title'			=>$data['title'],
					'total_amount'	=>$data['total_amount'],
					'invoice'		=>$data['invoice'],
					'payment_type'	=>$data['payment_method'],
					'description'	=>$data['Description'],
					'cheque_no'		=>$data['cheque_num'],
					'date'			=>$data['Date'],
					'status'		=>$data['Stutas'],
					'user_id'		=>$this->getUserId(),
					//'create_date'=>date('Y-m-d H:i:s'),
				);
	$where=" id = ".$data['id'];
	$this->update($arr, $where);
	
	$this->_name='ln_expense_detail';
	$where = "expense_id = ".$data['id'];
	$this->delete($where);
	$ids = explode(',', $data['identity']);
	foreach ($ids as $j){
		$arr = array(
				'expense_id'	=>$data['id'],
				'service_id'	=>$data['expense_id'.$j],
				'description'	=>$data['remark'.$j],
				'price_pain'	=>$data['price_pain'.$j],
				'qty_pain'		=>$data['qty_pain'.$j],
				'total_pain'	=>$data['total_pain'.$j],);
		$this->insert($arr);
	}
	$_db->commit();
	}catch(Exception $e){
		$_db->rollBack();
		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	}
	//print_r($data); exit();
}
function getexpensebyid($id){
	$db = $this->getAdapter();
	$sql="SELECT * FROM ln_expense where id=$id ";
	return $db->fetchRow($sql);
}
function getexpenseDetailbyid($id){
	$db = $this->getAdapter();
	$sql="SELECT * FROM ln_expense_detail WHERE expense_id=".$id;
	return $db->fetchAll($sql);
}

function getAllExpense($search=null){
	$db = $this->getAdapter();
	$session_user=new Zend_Session_Namespace('authstu');
	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
	$where = " WHERE ".$from_date." AND ".$to_date;
	
	$sql=" SELECT id,
	(SELECT branch_nameen FROM `rms_branch` WHERE rms_branch.br_id =branch_id LIMIT 1) AS branch_name,
	title,invoice,
	(SELECT name_kh FROM `rms_view` WHERE rms_view.type=8 and rms_view.key_code = payment_type limit 1) AS payment_type,
	total_amount,description,date,
	(SELECT first_name FROM `rms_users` WHERE id=1 LIMIT 1) as user_name,
	status FROM ln_expense ";
	
	if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " title LIKE '%{$s_search}%'";
			$s_where[] = " invoice LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		if($search['payment_type']>-1){
			$where.= " AND payment_type = ".$search['payment_type'];
		}
       $order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
}
function getAllExpenseReport($search=null){
	$db = $this->getAdapter();
	$session_user=new Zend_Session_Namespace('authstu');
	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
	$where = " WHERE ".$from_date." AND ".$to_date;

	$sql=" SELECT id,
	(SELECT branch_namekh FROM `rms_branch` WHERE rms_branch.br_id =branch_id LIMIT 1) AS branch_name,
	account_id,
	(SELECT symbol FROM `ln_currency` WHERE ln_currency.id =curr_type) AS currency_type,invoice,
	curr_type,
	total_amount,disc,date,status FROM $this->_name ";

	if (!empty($search['adv_search'])){
		$s_where = array();
		$s_search = trim(addslashes($search['adv_search']));
		$s_where[] = " account_id LIKE '%{$s_search}%'";
		$s_where[] = " title LIKE '%{$s_search}%'";
		$s_where[] = " total_amount LIKE '%{$s_search}%'";
		$s_where[] = " invoice LIKE '%{$s_search}%'";
		
		$where .=' AND ('.implode(' OR ',$s_where).')';
	}
	if($search['status']>-1){
		$where.= " AND status = ".$search['status'];
	}
	if($search['currency_type']>-1){
		$where.= " AND curr_type = ".$search['currency_type'];
	}
	$order=" order by id desc ";
	return $db->fetchAll($sql.$where.$order);
}

	public function getAllCateExpense($type){
		$db = $this->getAdapter();
		$sql = "SELECT id ,account_name as name FROM `rms_account_name` WHERE status=1 AND account_name!=''
				and account_type = ".$type;
		return $db->fetchAll($sql);
	}


	function addCateExpense($data){
		$this->_name="rms_account_name";
		$arr = array(
				'account_code'=>$data['account_code'],
				'account_name'=>$data['account_name'],
				'account_type'=>5, // expense category
				'status'=>1,
				'user_id'=>$this->getUserId(),
				'date'=>date('Y-m-d'),
		);
		$id = $this->insert($arr);
	
		$db = new Application_Model_GlobalClass();
		$new_arrar_cate_expense = $db->getAllExpenseIncomeType(5);
		
		$result = array(
				'id'=>$id,
				'new_array'=>$new_arrar_cate_expense,
				);
				
		return $result;
		
	}

}