<?php

class Issue_Model_DbTable_DbLetterofpraise extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_issue_letterpraise';
    public function getUserId(){
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	return $dbp->getUserId();
    }
    function getAllIssueCertification($search){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
		$branch = $dbp->getBranchDisplay();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql = "
			SELECT 
				c.id
				,(SELECT b.$branch FROM `rms_branch` AS b  WHERE b.br_id = c.branch_id LIMIT 1) AS branch_name
				,(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = c.group_id LIMIT 1) AS group_code
				,c.academic_year
				,c.grade
				,c.issue_date
				,CASE
					WHEN  c.for_type = 1 THEN '". $tr->translate("KHMER")."'
					WHEN  c.for_type = 2 THEN '". $tr->translate("ENGLISH")."'
				END AS for_type
				,(SELECT first_name FROM rms_users WHERE rms_users.id = c.user_id LIMIT 1) AS user
    	";
    	$sql.=$dbp->caseStatusShowImage("c.status");
    	$sql.=" FROM `rms_issue_letterpraise` AS c WHERE 1 ";
    	$where ='';
    	if(!empty($search['adv_search'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['adv_search']));
	    	$s_where[] = " c.academic_year LIKE '%{$s_search}%'";
	    	$s_where[] = " c.grade LIKE '%{$s_search}%'";
	    	$s_where[] = " (SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = c.branch_id LIMIT 1) LIKE '%{$s_search}%'";
	    	$s_where[] = " (SELECT g.group_code FROM `rms_group` AS g WHERE g.id = c.group_id LIMIT 1) LIKE '%{$s_search}%'";
	    	$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
	    if(!empty($search['branch_id'])){
	    	$where.=' AND c.branch_id='.$search['branch_id'];
	    }
	    if(!empty($search['group'])){
	    	$where.=' AND c.group_id='.$search['group'];
	    }
	    if(!empty($search['language_type'])){
	    	$where.=' AND c.for_type='.$search['language_type'];
	    }
	    if($search['status']>-1){
	    	$where.=' AND c.status='.$search['status'];
	    }
	    $where.=$dbp->getAccessPermission('c.branch_id');
	    $order =  ' ORDER BY c.`id` DESC ' ;
	    return $db->fetchAll($sql.$where.$order);
    }
    
    function checkGroupIssueLetterpraise($_data){
    	$db= $this->getAdapter();
    	$sql="SELECT c.*
			FROM `rms_issue_letterpraise` AS c 
			 WHERE  c.branch_id=".$_data['branch_id']." AND c.group_id=".$_data['group']." LIMIT 1";
    	return $db->fetchRow($sql);
    }
	public function addIssueLetterpraise($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			$_arr= array(
					'branch_id'		=>$_data['branch_id'],
					'group_id'		=>$_data['group'],
					'academic_year'	=>$_data['academic_year'],
					'grade'			=>$_data['grade'],
					'issue_date'	=>$_data['issue_date'],
					'for_type'		=>$_data['for_type'],
					'note'			=>$_data['note'],
					
					'teacherName'			=>$_data['teacherName'],
					'positionTeacher'		=>$_data['positionTeacher'],
					'principleName'			=>$_data['principleName'],
					'positionPrinciple'		=>$_data['positionPrinciple'],
					
					'create_date'		=>date("Y-m-d H:i:s"),
					'modify_date'		=>date("Y-m-d H:i:s"),
					'status'		=>1,
					'user_id'		=>$this->getUserId(),
					);
			$this->_name='rms_issue_letterpraise';
			$id = $this->insert($_arr);

		 if (!empty($_data['selector'])) foreach ($_data['selector'] as $k){
				$arr = array(
						'letterpraise_id'	=>$id,
						'group_id'	=>$_data['group'],
						'stu_id'	=>$_data['stu_id'.$k],
						'rank'	=>$_data['rank_'.$k],
						'letterpraiseCode'	=>$_data['letterpraiseCode_'.$k],
						'note'	=>$_data['note_'.$k],
						'date'		=>date("Y-m-d H:i:s"),
						'user_id'	=>$this->getUserId(),
				);
				$this->_name='rms_issue_letterpraise_detail';
				$this->insert($arr);
			}
			
			$_db->commit();
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
		}
	}
	
	public function updateIssueLetterpraise($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			$_arr= array(
					'branch_id'			=>$_data['branch_id'],
					'group_id'			=>$_data['group'],
					'academic_year'		=>$_data['academic_year'],
					'grade'				=>$_data['grade'],
					'issue_date'		=>$_data['issue_date'],
					'for_type'			=>$_data['for_type'],
					'note'				=>$_data['note'],
					
					'teacherName'			=>$_data['teacherName'],
					'positionTeacher'		=>$_data['positionTeacher'],
					'principleName'			=>$_data['principleName'],
					'positionPrinciple'		=>$_data['positionPrinciple'],
					
					'modify_date'		=>date("Y-m-d H:i:s"),
					'status'			=>$_data['status'],
					'user_id'			=>$this->getUserId(),
			);
			$this->_name='rms_issue_letterpraise';
			$id = $_data['id'];
			$where=" id =".$id;
			$this->update($_arr, $where);
			
			$where1 = " letterpraise_id = $id ";
			$this->_name='rms_issue_letterpraise_detail';
			$this->delete($where1);
			 
		 	if (!empty($_data['selector'])) foreach ($_data['selector'] as $k){
				$arr = array(
						'letterpraise_id'	=>$id,
						'group_id'	=>$_data['group'],
						'stu_id'	=>$_data['stu_id'.$k],
						'rank'	=>$_data['rank_'.$k],
						'letterpraiseCode'	=>$_data['letterpraiseCode_'.$k],
						'note'	=>$_data['note_'.$k],
						'date'		=>date("Y-m-d H:i:s"),
						'user_id'	=>$this->getUserId(),
				);
				$this->_name='rms_issue_letterpraise_detail';
				$this->insert($arr);
			}
			$_db->commit();
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
		}
	}
	
	function getIssueLetterofpraiseById($id){
		$db = $this->getAdapter();
		$sql="SELECT c.* FROM `rms_issue_letterpraise` AS c WHERE c.id = $id";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('c.branch_id');
		$sql." LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getIssueLetterofpraiseStudent($id){
		$db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='name_en';
		$stu_name="CONCAT(st.last_name,' ',st.stu_enname)";
		if ($currentLang==1){
			$colunmname='name_kh';
			$stu_name="st.stu_khname";
		}
		
		$sql="SELECT cd.*,
			st.stu_enname,
			st.last_name,
			st.stu_khname,
			st.stu_code,
			st.dob,
			$stu_name AS stu_name,
			(SELECT $colunmname FROM rms_view WHERE rms_view.type=2 AND rms_view.key_code=st.sex LIMIT 1) AS sex
			FROM `rms_issue_letterpraise_detail` AS cd,
			rms_student AS st
			WHERE 
			st.stu_id = cd.stu_id 
			AND
			 cd.letterpraise_id = $id";
		return $db->fetchAll($sql);
	}
	
}