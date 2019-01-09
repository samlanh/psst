<?php

class Accounting_Model_DbTable_DbFee extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_tuitionfee';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    function getAllTuitionFee($search=null){
    	$db=$this->getAdapter();
    	$sql = "SELECT t.id,
    	(select CONCAT(branch_nameen) from rms_branch where br_id =t.branch_id LIMIT 1) as branch,
    	CONCAT(t.from_academic,' - ',t.to_academic) AS academic, t.generation,
    	(SELECT title FROM `rms_schooloption` WHERE rms_schooloption.id=t.school_option LIMIT 1) as school_option,
    	t.create_date,
    	(select name_en from rms_view where type=12 and key_code=t.is_finished) as is_finished,
    	t.status,
    	(select CONCAT(first_name) from rms_users where rms_users.id = t.user_id) as user
    	FROM `rms_tuitionfee` AS t
    	WHERE t.type=1	";
    	$where =" ";
    	 
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " CONCAT(from_academic,'-',to_academic) LIKE '%{$s_search}%'";
    		$s_where[] = " t.generation LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['academic_year'])){
    		$where.=" AND t.id=".$search['academic_year'];
    	}
    	 
    	if(!empty($search['branch_id'])){
    		$where.=" AND t.branch_id=".$search['branch_id'];
    	}
    	 
    	if($search['is_finished_search']!=""){
    		$where.=" AND t.is_finished=".$search['is_finished_search'];
    	}
    	if($search['school_option']>0){
    		$where.=" AND t.school_option=".$search['school_option'];
    	}
    	 
    	if($search['status']>-1){
    		$where.=" AND t.status=".$search['status'];
    	}
    	 
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission();
    	 
    	$order=" GROUP BY t.branch_id,t.from_academic,t.to_academic,t.generation,t.time ORDER BY t.id DESC  ";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getCondition($_data){
    	$db = $this->getAdapter();
    	$find="select id from rms_tuitionfee where from_academic=".$_data['from_academic']." and to_academic=".$_data['to_academic']."
    	and generation='".$_data['generation']."'  AND branch_id = ".$_data['branch_id'];
    	 
    	return $db->fetchOne($find);
    }
	public function addTuitionFee($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	$fee_id = $this->getCondition($_data);
    	try{
    		if(!empty($fee_id)){
    		}else{
	    		$_arr = array(
	    				'from_academic'=>$_data['from_academic'],
	    				'to_academic'=>$_data['to_academic'],
	    				'generation'=>$_data['generation'],
	    				'school_option'=>$_data['school_option'],
	    				'note'=>$_data['note'],
	    				'type'=>1,//Tuition Fee
	    				'branch_id'=>$_data['branch_id'],
	    				'create_date'=>date("Y-m-d"),
	    				'user_id'=>$this->getUserId()
	    				);
	    		$fee_id = $this->insert($_arr);
    		}
	    		$this->_name='rms_tuitionfee_detail';
	    		$ids = explode(',', $_data['identity']);
	    		$id_term =explode(',', $_data['iden_term']);
	    		foreach ($ids as $i){
	    			foreach ($id_term as $j){
	    				$_arr = array(
	    						'fee_id'=>$fee_id,
	    						'class_id'=>$_data['class_'.$i],
	    						//'session'=>$_data['session_'.$i],
	    						'payment_term'=>$j,
	    						'tuition_fee'=>$_data['fee'.$i.'_'.$j],
	    						'remark'=>$_data['remark'.$i]
	    				);
	    				$this->insert($_arr);
	    			}
	    		}
    	    $db->commit();
    	    return true;
    		
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
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
    				'from_academic'	=>$_data['from_academic'],
    				'to_academic'	=>$_data['to_academic'],
    				'generation'	=>$_data['generation'],
    				'school_option'=>$_data['school_option'],
    				'note'			=>$_data['note'],
    				'status'		=>$_data['status'],
    				'type'=>1,//Tuition Fee
    				'is_finished'	=>$_data['is_finished'],
    				//'time'			=>$_data['time'],
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
    							//'session'=>$_data['session_'.$i],
    							'payment_term'=>$j,
    							'tuition_fee'=>$_data['fee'.$i.'_'.$j],
    							'remark'=>$_data['remark'.$i],
    							//'status'=>$_data['status_'.$i]
    					);
    					$this->insert($_arr);
    				}
    			}
    		}
    		$db->commit();
    		return true;
    	}catch (Exception $e){
    		$db->rollBack();
    		echo $e->getMessage();
    	}
    }
    
    function getAceYear(){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	return $_db->getAllYear(2);
//     	$branch_id = $_db->getAccessPermission();
//     	$sql="SELECT id,CONCAT(from_academic,'-',to_academic,'(',generation,')') AS `name`
//     	FROM rms_tuitionfee WHERE `status`=1 $branch_id  group by from_academic,to_academic,generation,time ";
//     	$oder=" ORDER BY id DESC ";
//     	return $db->fetchAll($sql.$oder);
    }
    //for get Grade By School Option
    function getAllGradeStudySchoolOption($option=1,$schoolOption){
    	$db = $this->getAdapter();
    	$sql="SELECT i.id,
    	CONCAT(i.title,' (',(SELECT it.title FROM `rms_items` AS it WHERE it.id = i.items_id LIMIT 1),')') AS name
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