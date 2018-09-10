<?php class Home_Model_DbTable_DbCRM extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_student_test';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
    function getAllCRM($search = ''){
    	$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql = " SELECT 
				st.id,
				(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = st.branch_id LIMIT 1) AS branch_name,
				st.kh_name,st.first_name,st.en_name,
				CASE    
				WHEN  st.sex = 1 THEN '".$tr->translate("MALE")."'
				WHEN  st.sex = 2 THEN '".$tr->translate("FEMALE")."'
				END AS sex,
				st.old_school,
				st.parent_name,
				st.parent_tel,
				CASE    
				WHEN  st.crm_status = 0 THEN '".$tr->translate("DROPPED")."'
				WHEN  st.crm_status = 1 THEN '".$tr->translate("PROCCESSING")."'
				WHEN  st.crm_status = 2 THEN '".$tr->translate("WAITING_TEST")."'
				WHEN  st.crm_status = 3 THEN '".$tr->translate("COMPLETED")."'
				END AS crm_status,
				st.create_date,
				(SELECT COUNT(c.id) FROM `rms_crm_history_contact` AS c WHERE c.crm_id = st.id LIMIT 1) AS amountContact, 
				(SELECT CONCAT(first_name) FROM rms_users WHERE st.crm_by=id LIMIT 1 ) AS crm_by
				FROM `rms_student_test` AS st WHERE st.type=0 ";
    	$where = ' ';
    	$from_date =(empty($search['start_date']))? '1': " st.create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " st.create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    	$where.= " AND  ".$from_date." AND ".$to_date;
    	if(!empty($search['advance_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
    		$s_where[] = " st.kh_name LIKE '%{$s_search}%'";
    		$s_where[] = " st.first_name LIKE '%{$s_search}%'";
    		$s_where[] = " st.en_name LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status_search']>-1){
    		$where.= " AND st.crm_status = ".$db->quote($search['status_search']);
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('st.branch_id');
    	$where.=" ORDER BY st.id DESC";
    	return $db->fetchAll($sql.$where);
    }
    
	public function AddCRM($_data){
		$_db= $this->getAdapter();
		try{
			
			$_arr=array(
					'branch_id'	  => $_data['branch_id'],
					'kh_name' => $_data['kh_name'],
					'first_name'=> $_data['first_name'],
					'en_name'=> $_data['en_name'],
					'sex'=> $_data['sex'],
					'old_school'=> $_data['old_school'],
					'reason'=> $_data['reason'],
					'parent_name'=> $_data['reference_name'],
					'parent_tel'=> $_data['parent_tel'],
					'crm_status'=> $_data['crm_status'],
					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'crm_by'	  => $this->getUserId()
			);
			$id =  $this->insert($_arr);
			return $id;	
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
	
	public function updateCrm($_data){
		$_db= $this->getAdapter();
		try{
				
			$_arr=array(
					'branch_id'	  => $_data['branch_id'],
					'kh_name' => $_data['kh_name'],
					'first_name'=> $_data['first_name'],
					'en_name'=> $_data['en_name'],
					'sex'=> $_data['sex'],
					'old_school'=> $_data['old_school'],
					'reason'=> $_data['reason'],
					'parent_name'=> $_data['reference_name'],
					'parent_tel'=> $_data['parent_tel'],
					'crm_status'=> $_data['crm_status'],
// 					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'crm_by'	  => $this->getUserId()
			);
			$id = $_data['id'];
			$where="id=$id";
			$this->update($_arr, $where);
			
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
	public function getCRMById($id){
		$db = $this->getAdapter();
		$sql="SELECT st.*	FROM `rms_student_test` AS st WHERE st.id = $id AND st.is_makestudenttest = 0 ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('st.branch_id');
		return $db->fetchRow($sql);
	}
	
	public function AllHistoryContact($crm_id){
		$db = $this->getAdapter();
		$sql="SELECT c.*,
			(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE c.user_contact=id LIMIT 1 ) AS user_contact_name
		FROM `rms_crm_history_contact` AS c WHERE crm_id = $crm_id ORDER BY c.id DESC";
		return $db->fetchAll($sql);
	}
	public function addCrmContactHistory($_data){
		$_db= $this->getAdapter();
		try{
	
			$_arr=array(
					'crm_id'	  => $_data['id'],
					'contact_date' => $_data['contact_date'],
					'feedback'=> $_data['feedback'],
					'proccess'=> $_data['proccess'],
					'next_contact'=> $_data['next_contact'],
					'user_contact'=> $_data['user_contact'],
					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'user_id'	  => $this->getUserId()
			);
			$this->_name = "rms_crm_history_contact";
			$id = $this->insert($_arr);

			//update CRM
			$_arr=array(
					'crm_status'=> $_data['proccess']
			);
			$this->_name = "rms_student_test";
			$where="id=".$_data['id'];
			$this->update($_arr, $where);
			
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
	
	function getAllCompleteCRM(){
		$db = $this->getAdapter();
		$sql="SELECT st.*,(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = st.branch_id LIMIT 1) AS branch_name FROM `rms_student_test` AS st WHERE st.is_makestudenttest = 0 AND st.crm_status = 2";
		return $db->fetchAll($sql);
	}
}
