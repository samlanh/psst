<?php class Home_Model_DbTable_DbCRM extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_student_test';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
    function getAllCRM($search = ''){
    	$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql="SELECT c.id,
			(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = c.branch_id LIMIT 1) AS branch_name,
			c.kh_name,c.first_name,c.last_name,
			CASE    
				WHEN  c.sex = 1 THEN '".$tr->translate("MALE")."'
				WHEN  c.sex = 2 THEN '".$tr->translate("FEMALE")."'
				END AS sex,
			c.tel,
			CASE    
				WHEN  c.ask_for = 1 THEN '".$tr->translate("KHMER_KNOWLEDGE")."'
				WHEN  c.ask_for = 2 THEN '".$tr->translate("ENGLISH")."'
				WHEN  c.ask_for = 3 THEN '".$tr->translate("UNIVERSITY")."'
				WHEN  c.ask_for = 4 THEN '".$tr->translate("OTHER")."'
				END AS ask_for,
			c.create_date,
			CASE    
				WHEN  c.crm_status = 0 THEN '".$tr->translate("DROPPED")."'
				WHEN  c.crm_status = 1 THEN '".$tr->translate("PROCCESSING")."'
				WHEN  c.crm_status = 2 THEN '".$tr->translate("WAITING_TEST")."'
				WHEN  c.crm_status = 3 THEN '".$tr->translate("COMPLETED")."'
				END AS crm_status,
			(SELECT COUNT(cr.id) FROM `rms_crm_history_contact` AS cr WHERE cr.crm_id = c.id LIMIT 1) AS amountContact, 
			(SELECT CONCAT(first_name) FROM rms_users WHERE c.user_id=id LIMIT 1 ) AS userby
			 FROM `rms_crm` AS c
    		WHERE 1
    	";
    	$where = ' ';
    	$from_date =(empty($search['start_date']))? '1': " c.create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " c.create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    	$where.= " AND  ".$from_date." AND ".$to_date;
    	if(!empty($search['advance_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
    		$s_where[] = " c.kh_name LIKE '%{$s_search}%'";
    		$s_where[] = " c.first_name LIKE '%{$s_search}%'";
    		$s_where[] = " c.last_name LIKE '%{$s_search}%'";
    		$s_where[] = " c.tel LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['branch_search'])){
    		$where.= " AND c.branch_id = ".$db->quote($search['branch_search']);
    	}
    	if(!empty($search['askfor_search'])){
    		$where.= " AND c.ask_for = ".$db->quote($search['askfor_search']);
    	}
    	if($search['status_search']>-1){
    		$where.= " AND c.crm_status = ".$db->quote($search['status_search']);
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('c.branch_id');
    	$where.=" ORDER BY c.id DESC";
    	return $db->fetchAll($sql.$where);
    }
    function checkPrevConcern($value){
    	$db = $this->getAdapter();
    	$sql="SELECT v.key_code FROM `rms_view` AS v WHERE v.name_kh = '$value' AND v.type=22  LIMIT 1";
    	return $db->fetchOne($sql);
    }
    function getPrevTilteByKeyCode($value){
    	$db = $this->getAdapter();
    	$sql="SELECT v.name_kh  FROM `rms_view` AS v WHERE v.key_code = $value AND v.type=22  LIMIT 1";
    	return $db->fetchOne($sql);
    }
	public function AddCRM($_data){
		$_db= $this->getAdapter();
		try{
			$_dbgb = new Application_Model_DbTable_DbGlobal();
			$prev = "";
			if (!empty($_data['prev_concern'])){
				$epl = explode(",", $_data['prev_concern']);
				foreach ($epl as $ss){
					$key = $this->checkPrevConcern($ss);
					if (empty($key)){
						$key_code = $_dbgb->getLastKeycodeByType(22);
						$_arrview=array(
								'name_en'	  => $ss,
								'name_kh' => $ss,
								'key_code'=> $key_code,
								'type'=>22,
								'status'=> 1,
						);
						$this->_name="rms_view";
						$key = $this->insert($_arrview);
					}
					if (empty($prev)){$prev=$key;}else{$prev=$prev.",".$key;}
				}
			}
			
			$_arr=array(
					'branch_id'	  => $_data['branch_id'],
					'kh_name' => $_data['kh_name'],
					'first_name'=> $_data['first_name'],
					'last_name'=> $_data['last_name'],
					'sex'=> $_data['sex'],
					'ask_for'=> $_data['ask_for'],
					'prev_concern'=> $prev,
					'know_by'=> $_data['know_by'],
					'tel'=> $_data['tel'],
					'old_school'=> $_data['old_school'],
					'reason'=> $_data['reason'],
					'note'=> $_data['note'],
					'crm_status'=> $_data['crm_status'],
					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'user_id'	  => $this->getUserId()
			);
			$this->_name="rms_crm";
			$id =  $this->insert($_arr);
			
			if (!empty($_data['identity'])){
				$ids = explode(",", $_data['identity']);
				foreach ($ids as $i){
					$array = array(
							'branch_id'	  => $_data['branch_id'],
							'crm_id'	  => $id,
							'customer_type'=>3,
							'stu_khname'=> $_data['kh_name_'.$i],
							'stu_enname'=> $_data['first_name_'.$i],
							'last_name'=> $_data['last_name_'.$i],
							'sex'=> $_data['gender_'.$i],
							'degree'=> $_data['degree_'.$i],//May Not User
							'grade'=> $_data['grade_'.$i],//May Not User
							'crm_degree'=> $_data['degree_'.$i],
							'crm_grade'=> $_data['grade_'.$i],
							'age'=> $_data['age_'.$i],
							'user_id'	  => $this->getUserId(),
							
							'guardian_khname' => $_data['kh_name'],
							'guardian_first_name'=> $_data['first_name'],
							'guardian_enname'=> $_data['last_name'],
							'guardian_tel'=> $_data['tel'],
							'from_school'=> $_data['old_school'],
							'know_by'=> $_data['know_by'],
							);
					$this->_name="rms_student";
					$this->insert($array);
				}
			}
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
				
			$_dbgb = new Application_Model_DbTable_DbGlobal();
			$prev = "";
			if (!empty($_data['prev_concern'])){
				$epl = explode(",", $_data['prev_concern']);
				foreach ($epl as $ss){
					$key = $this->checkPrevConcern($ss);
					if (empty($key)){
						$key_code = $_dbgb->getLastKeycodeByType(22);
						$_arrview=array(
								'name_en'	  => $ss,
								'name_kh' => $ss,
								'key_code'=> $key_code,
								'type'=>22,
								'status'=> 1,
						);
						$this->_name="rms_view";
						$key = $this->insert($_arrview);
					}
					if (empty($prev)){$prev=$key;}else{$prev=$prev.",".$key;}
				}
			}
			
			$_arr=array(
					'branch_id'	  => $_data['branch_id'],
					'kh_name' => $_data['kh_name'],
					'first_name'=> $_data['first_name'],
					'last_name'=> $_data['last_name'],
					'sex'=> $_data['sex'],
					'ask_for'=> $_data['ask_for'],
					'prev_concern'=> $prev,
					'know_by'=> $_data['know_by'],
					'tel'=> $_data['tel'],
					'old_school'=> $_data['old_school'],
					'reason'=> $_data['reason'],
					'note'=> $_data['note'],
					'crm_status'=> $_data['crm_status'],
					'modify_date' => date("Y-m-d H:i:s"),
					'user_id'	  => $this->getUserId()
			);
			$id = $_data['id'];
			$where="id=$id";
			$this->_name="rms_crm";
			$this->update($_arr, $where);
			
			$detailId="";
			$ids = explode(",", $_data['identity']);
			if (!empty($_data['identity'])){
				foreach ($ids as $k){
					if (empty($detailId)){
						if (!empty($_data['detailid'.$k])){
							$detailId = $_data['detailid'.$k];
						}
					}else{
						if (!empty($_data['detailid'.$k])){
							$detailId= $detailId.",".$_data['detailid'.$k];
						}
					}
				}
			}
			
			$this->_name="rms_student";
			$where="crm_id = ".$id;
			if (!empty($detailId)){
				$where.=" AND stu_id NOT IN ($detailId) ";
			}
			$this->delete($where);
				
			if (!empty($_data['identity'])){
				$ids = explode(",", $_data['identity']);
				foreach ($ids as $i){
					if (!empty($_data['detailid'.$i])){
						$array = array(
							'branch_id'	  => $_data['branch_id'],
							'crm_id'	  => $id,
							'customer_type'=>3,
							'stu_khname'=> $_data['kh_name_'.$i],
							'stu_enname'=> $_data['first_name_'.$i],
							'last_name'=> $_data['last_name_'.$i],
							'sex'=> $_data['gender_'.$i],
// 							'degree'=> $_data['degree_'.$i],//May Not User
// 							'grade'=> $_data['grade_'.$i],//May Not User
							'crm_degree'=> $_data['degree_'.$i],
							'crm_grade'=> $_data['grade_'.$i],
							'age'=> $_data['age_'.$i],
							'user_id'	  => $this->getUserId(),
							'guardian_khname' => $_data['kh_name'],
							'guardian_first_name'=> $_data['first_name'],
							'guardian_enname'=> $_data['last_name'],
							'guardian_tel'=> $_data['tel'],
							'from_school'=> $_data['old_school'],
							'know_by'=> $_data['know_by'],
							);
						$this->_name="rms_student";
						$where = " stu_id =".$_data['detailid'.$i];
						$this->update($array, $where);
					}else{
						$array = array(
							'branch_id'	  => $_data['branch_id'],
							'crm_id'	  => $id,
							'customer_type'=>3,
							'stu_khname'=> $_data['kh_name_'.$i],
							'stu_enname'=> $_data['first_name_'.$i],
							'last_name'=> $_data['last_name_'.$i],
							'sex'=> $_data['gender_'.$i],
							'degree'=> $_data['degree_'.$i],//May Not User
							'grade'=> $_data['grade_'.$i],//May Not User
							'crm_degree'=> $_data['degree_'.$i],
							'crm_grade'=> $_data['grade_'.$i],
							'age'=> $_data['age_'.$i],
							'user_id'	  => $this->getUserId(),
							'guardian_khname' => $_data['kh_name'],
							'guardian_first_name'=> $_data['first_name'],
							'guardian_enname'=> $_data['last_name'],
							'guardian_tel'=> $_data['tel'],
							'from_school'=> $_data['old_school'],
							'know_by'=> $_data['know_by'],
								
							);
						$this->_name="rms_student";
						$this->insert($array);
					}
				}
			}
			
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
	public function getCRMById($id){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="SELECT st.*,
				CASE    
				WHEN  st.ask_for = 1 THEN '".$tr->translate("KHMER_KNOWLEDGE")."'
				WHEN  st.ask_for = 2 THEN '".$tr->translate("ENGLISH")."'
				WHEN  st.ask_for = 3 THEN '".$tr->translate("UNIVERSITY")."'
				WHEN  st.ask_for = 4 THEN '".$tr->translate("OTHER")."'
				END AS ask_for_title
		FROM `rms_crm` AS st WHERE st.id = $id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('st.branch_id');
		return $db->fetchRow($sql);
	}
	
	public function getCRMDetailById($id){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="SELECT st.*,CASE    
				WHEN  st.sex = 1 THEN '".$tr->translate("MALE")."'
				WHEN  st.sex = 2 THEN '".$tr->translate("FEMALE")."'
				END AS sextitle,
				(SELECT i.title FROM `rms_items` AS i WHERE i.id = st.crm_degree AND i.type=1 LIMIT 1) AS degree_title,
				(SELECT idd.title FROM `rms_itemsdetail` AS idd WHERE idd.id = st.crm_grade AND idd.items_type=1 LIMIT 1) AS grade_title
		FROM `rms_student` AS st WHERE st.crm_id = $id  ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('st.branch_id');
		return $db->fetchAll($sql);
	}
	
// 	public function getCRMById($id){
// 		$db = $this->getAdapter();
// 		$sql="SELECT st.*	FROM `rms_student_test` AS st WHERE st.id = $id AND st.is_makestudenttest = 0 ";
// 		$dbp = new Application_Model_DbTable_DbGlobal();
// 		$sql.=$dbp->getAccessPermission('st.branch_id');
// 		return $db->fetchRow($sql);
// 	}
	
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
			$this->_name = "rms_crm";
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
		$sql="SELECT st.*,(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = st.branch_id LIMIT 1) AS branch_name 
		FROM `rms_student` AS st WHERE st.customer_type = 3 ";
		return $db->fetchAll($sql);
	}
}
