<?php
class Registrar_Model_DbTable_DbCateIncome extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_cate_income_expense';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	}
	
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->branch_id;
	}
	
	function addCateIncome($data){
		$_db= $this->getAdapter();
		try{
			$sql="SELECT id FROM rms_cate_income_expense WHERE category_name ='".$data['title']."'";
			//echo $sql; exit();
			//$sql.=" AND category_name='".$data['title']."'";
			$rs = $_db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
		$array = array(
					'category_name'	=>$data['title'],
					'account_code'	=>$data['acc_code'],
					'user_id'		=>$this->getUserId(),
					'create_date'	=>date('Y-m-d'),
				);
		$this->insert($array);
		$_db->commit();
		}catch(Exception $e){
			$_db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		//print_r($data); exit();
 	 }
 	 
	 function updateCateIncome($data){
		$arr = array(
					'category_name'	=>$data['title'],
					'account_code'	=>$data['acc_code'],
					'status'		=>$data['status'],
					'user_id'		=>$this->getUserId(),
				);
		$where=" id = ".$data['id'];
		$this->update($arr, $where);
	}
	
	function getCateIncomeById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_cate_income_expense where id=$id ";
		return $db->fetchRow($sql);
	}
	
	function getAllCateIncome($search=null){
		$db = $this->getAdapter();
		$sql=" SELECT 
					ci.id,
					ci.category_name,
					ci.account_code,
					(select first_name from rms_users where rms_users.id = ci.user_id) as user,
					create_date,
					ci.status
			FROM 
				rms_cate_income_expense as ci 
			where 1 ";
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







