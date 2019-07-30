<?php
class Registrar_Model_DbTable_DbCateIncome extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_cate_income_expense';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->branch_id;
	}
	
	function getAllCateIncome($search=null,$parent = 0, $spacing = '', $cate_tree_array = ''){
		$db = $this->getAdapter();
		$sql="select
					id,
					category_name as name,
					parent,
					account_code,
					create_date,
					(select name_en from rms_view where type=1 and key_code = rms_cate_income_expense.status) as status,
					(select first_name from rms_users where rms_users.id = user_id) as user
				from
					rms_cate_income_expense
				where
					parent = $parent
			";
		$order = " ORDER BY id desc ";
		$where = '';
	
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = $search['adv_search'];
			$s_where[]=" category_name LIKE '%{$s_search}%'";
			$s_where[]=" account_code LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		$rows = $db->fetchAll($sql.$where.$order);
		if (!is_array($cate_tree_array)){
			$cate_tree_array = array();
		}
		if (count($rows) > 0) {
			foreach ($rows as $row){
				$cate_tree_array[] = array("id" => $row['id'],"parent" => $row['parent'], "name" => $spacing . $row['name'],"account_code" => $row['account_code'],"create_date" => $row['create_date'],"user" => $row['user'],"status" => $row['status']);
				$cate_tree_array = $this->getAllCateIncome($search,$row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		return $cate_tree_array;
	}
	
	function addCateIncome($data){
		$_db= $this->getAdapter();
		try{
			$sql="SELECT id FROM rms_cate_income_expense WHERE category_name ='".$data['title']."'";
			$rs = $_db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
			$array = array(
				'category_name'	=>$data['title'],
				'parent'		=>$data['parent'],
				'account_code'	=>$data['acc_code'],
				'user_id'		=>$this->getUserId(),
				'create_date'	=>date('Y-m-d'),
			);
			$this->insert($array);
		}catch(Exception $e){
			echo $e->getMessage();
		}
 	 }
 	 
	 function updateCateIncome($data){
	 	try{
			$arr = array(
					'category_name'	=>$data['title'],
					'parent'		=>$data['parent'],
					'account_code'	=>$data['acc_code'],
					'status'		=>$data['status'],
					'user_id'		=>$this->getUserId(),
				);
			$where=" id = ".$data['id'];
			$this->update($arr, $where);
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}
	
	function getCateIncomeById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_cate_income_expense where id=$id ";
		return $db->fetchRow($sql);
	}
	
	public function getParentCateIncome($cate_id='',$parent = 0, $spacing = '', $cate_tree_array = ''){
		$db=$this->getAdapter();
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();
		$sql = " SELECT id , category_name as name from rms_cate_income_expense where status=1 AND `parent` = $parent ";
		if (!empty($cate_id)){
			$sql.=" AND id != $cate_id";
		}
		$query = $db->fetchAll($sql);
		$rowCount = count($query);
	
		$id='';
		if ($rowCount > 0) {
			foreach ($query as $row){
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getParentCateIncome($cate_id,$row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		return $cate_tree_array;
	}
	
	
}



