<?php

class Issue_Model_DbTable_DbCertification extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_issuecertificate';
    public function getUserId(){
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	return $dbp->getUserId();
    }
    function getAllIssueCertification($search){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql = "SELECT c.id,
			(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = c.branch_id LIMIT 1) AS branch_name,
			(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = c.group_id LIMIT 1) AS group_code,
			c.dept_kh,
			c.from_date,
			c.to_date,
			c.issue_date 
    	";
    	
    	$sql.=$dbp->caseStatusShowImage("c.status");
    	$sql.=" FROM `rms_issuecertificate` AS c WHERE 1 ";
    	$where ='';
    	if(!empty($search['adv_search'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['adv_search']));
	    	$s_where[] = " c.dept_kh LIKE '%{$s_search}%'";
	    	$s_where[] = " c.dept_eng LIKE '%{$s_search}%'";
	    	$s_where[] = " c.program_kh LIKE '%{$s_search}%'";
	    	$s_where[] = " c.program_en LIKE '%{$s_search}%'";
	    	$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
	    if(!empty($search['branch_id'])){
	    	$where.=' AND c.branch_id='.$search['branch_id'];
	    }
	    if(!empty($search['group'])){
	    	$where.=' AND c.group_id='.$search['group'];
	    }
	    if($search['status_search']>-1){
	    	$where.=' AND c.status='.$search['status_search'];
	    }
	    $where.=$dbp->getAccessPermission('c.branch_id');
	    $order =  ' ORDER BY c.`id` DESC ' ;
	    return $db->fetchAll($sql.$where.$order);
    }
    
    function checkGroupIssueCertificate($_data){
    	$db= $this->getAdapter();
    	$sql="SELECT c.*
			FROM `rms_issuecertificate` AS c 
			 WHERE  c.branch_id=".$_data['branch_id']." AND c.group_id=".$_data['group']." LIMIT 1";
    	return $db->fetchRow($sql);
    }
	public function addIssueCertificate($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			$_arr= array(
					'branch_id'		=>$_data['branch_id'],
					'group_id'		=>$_data['group'],
					'dept_kh'		=>$_data['dept_kh'],
					'dept_eng'		=>$_data['dept_eng'],
// 					'program_kh'		=>$_data['program_kh'],
// 					'program_en'		=>$_data['program_en'],
					'from_date'		=>$_data['from_date'],
					'to_date'		=>$_data['to_date'],
					'issue_date'		=>$_data['issue_date'],
					'note'		=>$_data['note'],
					'create_date'		=>date("Y-m-d H:i:s"),
					'modify_date'		=>date("Y-m-d H:i:s"),
					'status'		=>1,
					'user_id'		=>$this->getUserId(),
					);
			$this->_name='rms_issuecertificate';
			$id = $this->insert($_arr);
			
			$all_stu_id = $_data['public-methods'];
			foreach ($all_stu_id as $stu_id){
				$arr = array(
						'certificate_id'	=>$id,
						'group_id'	=>$_data['group'],
						'stu_id'	=>$stu_id,
						'date'		=>date("Y-m-d H:i:s"),
						'user_id'	=>$this->getUserId(),
				);
				$this->_name='rms_issuecertificate_detail';
				$this->insert($arr);
			}
			
			$_db->commit();
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
		}
	}
	
	public function updateIssueCertificate($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			$_arr= array(
					'branch_id'		=>$_data['branch_id'],
					'group_id'		=>$_data['group'],
					'dept_kh'		=>$_data['dept_kh'],
					'dept_eng'		=>$_data['dept_eng'],
// 					'program_kh'		=>$_data['program_kh'],
// 					'program_en'		=>$_data['program_en'],
					'from_date'		=>$_data['from_date'],
					'to_date'		=>$_data['to_date'],
					'issue_date'		=>$_data['issue_date'],
					'note'		=>$_data['note'],
					'modify_date'		=>date("Y-m-d H:i:s"),
					'status'		=>$_data['status'],
					'user_id'		=>$this->getUserId(),
			);
			$this->_name='rms_issuecertificate';
			$id = $_data['id'];
			$where=" id =".$id;
			$this->update($_arr, $where);
			
			
			$where1 = " certificate_id = $id ";
			$this->_name='rms_issuecertificate_detail';
			$this->delete($where1);
			 
			$all_stu_id = $_data['public-methods'];
			foreach ($all_stu_id as $stu_id){
				$arr = array(
						'certificate_id'	=>$id,
						'group_id'	=>$_data['group'],
						'stu_id'	=>$stu_id,
						'date'		=>date("Y-m-d H:i:s"),
						'user_id'	=>$this->getUserId(),
				);
				$this->_name='rms_issuecertificate_detail';
				$this->insert($arr);
			}
				
			$_db->commit();
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
		}
	}
	
	function getIssueCertificationById($id){
		$db = $this->getAdapter();
		$sql="SELECT c.* FROM `rms_issuecertificate` AS c WHERE c.id = $id";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('c.branch_id');
		$sql." LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getIssueCetifStudent($id){
		$db = $this->getAdapter();
		$sql="SELECT c.*
			FROM `rms_issuecertificate_detail` AS c
			 WHERE 
			 c.certificate_id = $id";
		return $db->fetchAll($sql);
	}
	
}