<?php
class Placement_Model_DbTable_DbSection extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_section';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->branch_id;
	}
	function getAllSection($search=null,$parent = 0, $spacing = '', $cate_tree_array = ''){
		 
		$dbp = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$lang = $dbp->currentlang();
		$db = $this->getAdapter();
		$sql = " SELECT
		en.id,
		en.parent,
		en.title,(SELECT tt.title FROM rms_test_type AS tt WHERE tt.id=en.test_type LIMIT 1 ) AS test_type,
		en.create_date
		";
		$sql.=$dbp->caseStatusShowImage("en.status");
		$sql.=" FROM `rms_section` AS en
		WHERE 1  ";
		 
		$order=" ORDER BY en.id DESC";
		$from_date =(empty($search['start_date']))? '1': " en.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " en.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$where.= "  AND en.parent = $parent ";
		
		if(!empty($search['adv_search'])){
		$s_where = array();
		$s_search = addslashes(trim($search['adv_search']));
				$s_where[]= " en.title LIKE '%{$s_search}%'";
				$s_where[]= " en.instruction LIKE '%{$s_search}%'";
				$s_where[]= " en.note LIKE '%{$s_search}%'";
				$where .= ' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['status'])){
			$where.= " AND en.status = ".$search['status'];
		}
		if(!empty($search['test_type'])){
			$where.= " AND en.test_type = ".$search['test_type'];
		}
		$rows = $db->fetchAll($sql.$where.$order);
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();
		if (count($rows) > 0) {
			foreach ($rows as $row){
				$cate_tree_array[] = array("id" => $row['id'], "title" => $spacing . $row['title'],"test_type" => $row['test_type'],"create_date" => $row['create_date'],"status" => $row['status']);
				$cate_tree_array = $this->getAllSection($search,$row['id'], $spacing . ' - ', $cate_tree_array);
			}
			
		}
		return $cate_tree_array;
	}
	public function addSection($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$_arr = array(
					'parent'=>$_data['parent'],
					'title'=>$_data['title'],
					'test_type'=>$_data['test_type'],
					'ordering'=>$_data['ordering'],
					'instruction'=>$_data['instruction'],
					'note'=>$_data['note'],
					'article'=>$_data['article'],
					'modify_date'=>date("Y-m-d H:i:s"),
					'user_id'=>$this->getUserId(),
			);
			$this->_name='rms_section';
			if (!empty($_data['id'])){
				$_arr['status']=$_data['status'];
				$where = "id=".$_data['id'];
				$this->update($_arr, $where);
			}else{
				$_arr['status']=1;
				$_arr['create_date']=date("Y-m-d H:i:s");
				$this->insert($_arr);
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getSectionById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_section WHERE id = $id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
}