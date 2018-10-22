<?php
class Registrar_Model_DbTable_DbCateExpense extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_account_name';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	}
	
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->branch_id;
	}
	
	function addCateExpense($data){
		$db= $this->getAdapter();
		try{
			$sql="SELECT id FROM rms_account_name where account_name ='".$data['title']."'";
			//$sql.=" AND account_code='".$$data['acc_code']."'";
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
		$array = array(
					'account_name'	=>$data['title'],
					'account_code'	=>$data['acc_code'],
					'account_type'	=>5,
					'user_id'		=>$this->getUserId(),
					'date'	=>date('Y-m-d'),
				);
		$this->insert($array);
		}catch (Exception $e){
			$db->rollBack();
			echo $e->getMessage();exit();
		}
		//print_r($data); exit();
 	 }
 	 
	 function updateCateExpense($data){
		$arr = array(
					'account_name'	=>$data['title'],
					'account_code'	=>$data['acc_code'],
					'status'		=>$data['status'],
					'user_id'		=>$this->getUserId(),
				);
		$where=" id = ".$data['id'];
		$this->update($arr, $where);
	}
	
	function getCateExpenseById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_account_name where id=$id ";
		return $db->fetchRow($sql);
	}
	
	function getAllCateExpense($search=null){
		$db = $this->getAdapter();
		$sql=" SELECT 
					ac.id,
					ac.account_name,
					ac.account_code,
					(select first_name from rms_users where rms_users.id = ac.user_id) as user,
					date,
					ac.status
				FROM 
					rms_account_name as ac 
				where 
					account_type=5
					and account_name!=''";
		$where = " ";
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " category_name LIKE '%{$s_search}%'";
			$s_where[] = " account_code LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		
        $order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	
	
}







