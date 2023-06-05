<?php

class Accounting_Model_DbTable_DbFee extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_tuitionfee';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllTuitionFee($search=null){
    	$db=$this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
    	$session_lang=new Zend_Session_Namespace('lang');
    	$lang = $session_lang->lang_id;
    	$field = 'name_en';
    	$str = 'title_eng';
    	if ($lang==1){
    		$field = 'name_kh';
    		$str = 'title_kh';
    	}
    	
    	$sql = "SELECT t.id,
	    	(SELECT CONCAT(branch_nameen) from rms_branch WHERE br_id =t.branch_id LIMIT 1) AS branch,
	    	(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=t.academic_year LIMIT 1) as academic,
	    	(SELECT $str FROM rms_studytype WHERE rms_studytype.id =t.term_study  LIMIT 1) AS study_type,
	    	CASE is_multi_study
			    WHEN 1 THEN '".$tr->translate("MULTY_PROGRAM")."'
			     WHEN 0 THEN '".$tr->translate("ONE_PROGRAM_ONLY")."'
			END is_multistudy,
	    	t.generation,
	    	(SELECT title FROM `rms_schooloption` WHERE rms_schooloption.id=t.school_option LIMIT 1) as school_option,
	    		t.create_date,
	    	(SELECT $field from rms_view where type=12 and key_code=t.is_finished) as is_finished,
	    	(SELECT CONCAT(first_name) from rms_users where rms_users.id = t.user_id) as user ";
    	
    	$sql.=$dbp->caseStatusShowImage("t.status");
    	$sql.=" FROM `rms_tuitionfee` AS t
    		WHERE t.type=1	";
    	
    	$where =" ";
    	 
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " t.generation LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['academic_year'])){
    		$where.=" AND t.academic_year=".$search['academic_year'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND t.branch_id=".$search['branch_id'];
    	} 
    	if($search['type_study']>0){
    		$where.=" AND t.term_study=".$search['type_study'];
    	}   	 
    	if($search['is_finished_search']!=""){
    		$where.=" AND t.is_finished=".$search['is_finished_search'];
    	}
    	if($search['school_option']>0){
    		$where.=" AND t.school_option=".$search['school_option'];
    	}
    	if($search['status']>-1 AND $search['status']!=''){
    		$where.=" AND t.status=".$search['status'];
    	}
    	
    	$where.=$dbp->getAccessPermission();
    	$order=" GROUP BY t.branch_id,t.academic_year,t.term_study,generation ORDER BY t.id DESC ";
    	
    	return $db->fetchAll($sql.$where.$order);
    }
    function getCondition($_data){
    	$db = $this->getAdapter();
    	$find="SELECT id FROM rms_tuitionfee WHERE 
    		academic_year =".$_data['from_academic']." AND branch_id = ".$_data['branch_id']." AND term_study = ".$_data['type_study']." AND generation ='".$_data['generation']."'";
    	$result = $db->fetchOne($find);
    	if(empty($result)){
    		return 0;
    	}else{
    		return 1;
    	}
    }
	public function addTuitionFee($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		
	    		$_arr = array(
	    			'academic_year'=>$_data['from_academic'],
 	    			'term_study'=>$_data['type_study'],
 	    			'is_multi_study'=>$_data['ismulty_study'],
	    			'generation'=>$_data['generation'],
	    			'school_option'=>$_data['school_option'],
	    			'note'=>$_data['note'],
	    			'type'=>1,//Tuition Fee
	    			'branch_id'=>$_data['branch_id'],
	    			'create_date'=>date("Y-m-d"),
	    			'user_id'=>$this->getUserId()
	    		);
	    		$fee_id = $this->insert($_arr);
    		
	    		$this->_name='rms_tuitionfee_detail';
	    		$ids = explode(',', $_data['identity']);
	    		$id_term =explode(',', $_data['iden_term']);
	    		foreach ($ids as $i){
	    			foreach ($id_term as $j){
	    				$_arr = array(
	    						'fee_id'=>$fee_id,
	    						'class_id'=>$_data['class_'.$i],
	    						'payment_term'=>$j,
	    						'tuition_fee'=>$_data['fee'.$i.'_'.$j],
	    						'remark'=>$_data['remark'.$i]
	    				);
	    				$this->insert($_arr);
	    			}
	    		}
				$id_st_term = explode(',', $_data['identity_term']);
				foreach ($id_st_term as $t){
						$arr = array(
								'branch_id'		=>$_data['branch_id'],
								'academic_year'	=>$_data['from_academic'],
								'title'			=>$_data['title_'.$t],
								'start_date'	=>$_data['startdate_'.$t],
								'end_date'		=>$_data['enddate_'.$t],
								'note'			=>$_data['remark_'.$t],
								'create_date'	=>date("Y-m-d"),
								'user_id'		=>$this->getUserId(),
							);
						$this->_name='rms_startdate_enddate';	
						$this->insert($arr);
				}
    	    $db->commit();
    	    return true;
    		
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		return false;
    	}
    }
    public function getFeeById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM rms_tuitionfee WHERE id = ".$id;
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission();
    	$sql.=" LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
    public function getFeeDetailById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT *
    	FROM rms_tuitionfee_detail WHERE fee_id = ".$id ." ORDER BY id";
    	return $db->fetchAll($sql);
    
    }
    public function updateTuitionFee($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$_arr = array(
    				'academic_year'	=>$_data['from_academic'],
    				'term_study'=>$_data['type_study'],
 	    			'is_multi_study'=>$_data['ismulty_study'],
    				'generation'	=>$_data['generation'],
    				'school_option'=>$_data['school_option'],
    				'note'			=>$_data['note'],
    				'status'		=>$_data['status'],
    				'type'=>1,//Tuition Fee
    				'is_finished'	=>$_data['is_finished'],
    				'branch_id'		=>$_data['branch_id'],
    				'user_id'		=>$this->getUserId()
    		);
    		$where=$this->getAdapter()->quoteInto("id=?", $_data['id']);
    		$this->update($_arr, $where);
    		if($_data['is_finished']==1){
    			$db->commit();
    			return true;
    		}
    		if($_data['is_finished']==0){
    			$this->_name='rms_tuitionfee_detail';
    			$where = "fee_id = ".$_data['id'];
    			$this->delete($where);
    			$ids = explode(',', $_data['identity']);
    			$id_term =explode(',', $_data['iden_term']);
    			foreach ($ids as $i){
    				foreach ($id_term as $j){
    					$_arr = array(
    							'fee_id'=>$_data['id'],
    							'class_id'=>$_data['class_'.$i],
    							'payment_term'=>$j,
    							'tuition_fee'=>$_data['fee'.$i.'_'.$j],
    							'remark'=>$_data['remark'.$i],
    					);
    					$this->insert($_arr);
    				}
    			}
    		}
    		$db->commit();
    		return true;
    	}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    function getAceYear(){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	return $_db->getAllYear(2);
    }
    //for get Grade By School Option
    function getAllGradeStudySchoolOption($option=1,$schoolOption){
    	$db = $this->getAdapter();
    	$_db  = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$grade = "i.title";
    		$degree = "it.title";
    	}else{ // English
    		$grade = "i.title_en";
    		$degree = "it.title_en";
    	}
    	$sql="SELECT i.id,
    	CONCAT($grade,' (',(SELECT $degree FROM `rms_items` AS it WHERE it.id = i.items_id LIMIT 1),')') AS name
    	FROM `rms_itemsdetail` AS i
    	WHERE i.status =1 ";
    	if($option!=null){
    		$sql.=" AND i.items_type=".$option;
    	}
    	$sql.=" AND i.schoolOption=$schoolOption";
    	$sql.=" ORDER BY i.items_id ASC, i.ordering ASC";
    	return $db->fetchAll($sql);
    }
    public function getAllGradeStudyOption($type=1,$schooloption){
    	$rows = $this->getAllGradeStudySchoolOption($type,$schooloption);
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	array_unshift($rows, array('id'=>-1,'name'=>$tr->translate("PLEASE_SELECT")));
    	$options = '';
    	if(!empty($rows))foreach($rows as $value){
    		$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
    	}
    	return $options;
    }
}