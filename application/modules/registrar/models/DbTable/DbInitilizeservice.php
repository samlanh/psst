<?php
class Registrar_Model_DbTable_DbInitilizeservice extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	
// 	function getAllCustomer($search=null){
// 		$db = $this->getAdapter();
		
// 		$dbp = new Application_Model_DbTable_DbGlobal();
// 		$currentLang = $dbp->currentlang();
// 		$colunmname='title_en';
// 		$label="name_en";
// 		$branch = "branch_nameen";
// 		if ($currentLang==1){
// 			$colunmname='title';
// 			$label="name_kh";
// 			$branch = "branch_namekh";
// 		}
// 		;
// 		$from_date =(empty($search['start_date']))? '1': " s.create_date >= '".$search['start_date']." 00:00:00'";
// 		$to_date = (empty($search['end_date']))? '1': " s.create_date <= '".$search['end_date']." 23:59:59'";
// 		$where = " AND ".$from_date." AND ".$to_date;
	
// 		$sql=" SELECT s.stu_id as id,
// 				(CASE WHEN s.stu_khname IS NULL OR s.stu_khname='' THEN s.stu_enname ELSE s.stu_khname END) AS name,
// 				(SELECT $label FROM `rms_view` WHERE type=2 AND key_code = s.sex LIMIT 1) AS sex,
// 				s.tel,
// 				s.email,
// 				(SELECT first_name FROM rms_users WHERE s.user_id=rms_users.id LIMIT 1 ) AS user_name
// 				 ";
// 		$sql.=$dbp->caseStatusShowImage("s.status");
// 		$sql.="	FROM $this->_name AS s 
// 			WHERE s.customer_type=2  ";
// 		if (!empty($search['adv_search'])){
// 			$s_where = array();
// 			$s_search = trim(addslashes($search['adv_search']));
// 			$s_where[]=" REPLACE(stu_code,' ','')   	LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(stu_khname,' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(stu_enname,' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(last_name,' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(CONCAT(last_name,stu_enname),' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(CONCAT(stu_enname,last_name),' ','')  	LIKE '%{$s_search}%'";
// 			$s_where[]=" REPLACE(tel,' ','') LIKE '%{$s_search}%'";
			
// 			$where .=' AND ('.implode(' OR ',$s_where).')';
// 		}
// 		if($search['status']>-1){
// 			$where.= " AND s.status = ".$search['status'];
// 		}
// 		$order=" ORDER BY s.stu_id DESC ";
// 		return $db->fetchAll($sql.$where.$order);
// 	}
	function addInitilizeService($data){
		$_db = $this->getAdapter();
		try{
				$this->_name='rms_group_detail_student';
				$ids = explode(',', $data['identity']);
				$dbg = new Application_Model_DbTable_DbGlobal();
				if(!empty($ids))foreach ($ids as $i){
					$param = array(
						'Id'=>$data['itemId_'.$i]
					);
					$resultRow = $dbg->getItemDetailRow($param);
					
					$result = $dbg->getFeeStudyinfoById($data['study_year']);
					$year = empty($result)?'':$result['id'];
					
					$_arr= array(
							'branch_id'		=> $data['branch_id'],
							'stu_id'		=> $data['studentId'],
							'itemType'		=> $resultRow['items_type'],
							
							'feeId'			=> $data['study_year'],
							'balance'		=> $data['balance_'.$i],
							
							'degree'		=> $resultRow['items_id'],
							'grade'			=> $data['itemId_'.$i],
							
							'startDate'		=> empty($data['balance_'.$i])?'':$data['date_start_'.$i],
							'endDate'		=> empty($data['balance_'.$i])?'':$data['end_date_'.$i],
							'is_maingrade'	=> ($resultRow['items_type']==1)?1:'',
							'school_option'	=> $resultRow['schoolOption'],
							'is_current'	=> 1,
							'stop_type'		=> 0,
							'status'		=> 1,
							'is_newstudent'	=> 1,
							'academic_year'	=> $year,
							'note'			=> $data['remark'.$i],
							'create_date'	=> date("Y-m-d H:i:s"),
							'date'			=> date("Y-m-d"),
							'user_id'		=> $this->getUserId(),
					);
					$id = $this->insert($_arr);
				}
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAILE");
		}
	}
	
// 	public function getCustomerById($id){
// 		$db = $this->getAdapter();
// 		$_db = new Application_Model_DbTable_DbGlobal();
// 		$lang = $_db->currentlang();
// 		$sql = "SELECT *
// 				FROM rms_student as s
// 				WHERE s.stu_id =".$id." 
// 				AND s.customer_type=2";
// 		return $db->fetchRow($sql);
// 	}

	

}