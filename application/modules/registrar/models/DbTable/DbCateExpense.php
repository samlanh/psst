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
	
	function getAllCateIncome($search=null,$parent = 0, $spacing = '', $cate_tree_array = ''){
		$db = $this->getAdapter();
		$sql="select
					id,
					account_name as name,
					parent_id,
					account_code,
					date as create_date,
					(select name_en from rms_view where type=1 and key_code = status) as status,
					(select first_name from rms_users where rms_users.id = user_id) as user
				from
					rms_account_name
				where
					parent_id = $parent
			";
		$order = " ORDER BY id desc ";
		$where = '';
	
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = $search['adv_search'];
			$s_where[]=" account_name LIKE '%{$s_search}%'";
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
				$cate_tree_array[] = array("id" => $row['id'],"parent" => $row['parent_id'], "name" => $spacing . $row['name'],"account_code" => $row['account_code'],"create_date" => $row['create_date'],"user" => $row['user'],"status" => $row['status']);
				$cate_tree_array = $this->getAllCateIncome($search,$row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		return $cate_tree_array;
	}
	
	function addCateExpense($data){
		$db= $this->getAdapter();
		try{
			$sql="SELECT id FROM rms_account_name where account_name ='".$data['title']."'";
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
		$array = array(
					'account_name'	=>$data['title'],
					'parent_id'		=>$data['parent'],
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
					'parent_id'		=>$data['parent'],
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
	
	public function getParentCateExpense($cate_id='',$parent = 0, $spacing = '', $cate_tree_array = ''){
		$db=$this->getAdapter();
		if (!is_array($cate_tree_array)){$cate_tree_array = array();}
		$sql = " SELECT id , account_name as name from rms_account_name where status=1 AND `parent_id` = $parent ";
		if (!empty($cate_id)){
			$sql.=" AND id != $cate_id";
		}
		$query = $db->fetchAll($sql);
		$rowCount = count($query);
	
		$id='';
		if ($rowCount > 0) {
			foreach ($query as $row){
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getParentCateExpense($cate_id,$row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		return $cate_tree_array;
	}
	
	
}







