<?php

class Accounting_Model_DbTable_DbYearStudy extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_academicyear';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;  	 
    }
	public function addNewAcademicYear($_data){
		$db = $this->getAdapter();
		try{
			$sql="SELECT id FROM rms_academicyear WHERE 1 ";
				$sql.=" AND fromYear='".$_data['fromYear']."'";
				$sql.=" AND toYear='".$_data['toYear']."'";
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}			
			$_arr=array(
					'fromYear'	  => $_data['fromYear'],
					'toYear'	  => $_data['toYear'],
					'createDate' => date("Y-m-d H:i:s"),
					'modifyDate' => date("Y-m-d H:i:s"),
					'status'  	  => 1,
					'userId'	  => $this->getUserId()
			);
			$this->insert($_arr);
		}catch (Exception $e){
			echo $e->getMessage();exit();
			$db->rollBack();
		}
	}
	public function updateAcademicYear($_data){
		$db = $this->getAdapter();
		try{
			$sql="SELECT id FROM rms_academicyear WHERE 1 ";
				$sql.=" AND fromYear='".$_data['fromYear']."'";
				$sql.=" AND toYear='".$_data['toYear']."'";
				$sql.=" AND id !='".$_data['id']."'";
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}			
			$_arr=array(
					'fromYear'	  => $_data['fromYear'],
					'toYear'	  => $_data['toYear'],
					'modifyDate' => date("Y-m-d H:i:s"),
					'status'  	  => $_data['status'],
					'userId'	  => $this->getUserId()
			);
			$where=" id = ".$_data['id'];
			$this->update($_arr, $where);
		}catch (Exception $e){
			echo $e->getMessage();exit();
			$db->rollBack();
		}
	}
	
	public function getAcademicYearById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_academicyear WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	
	function getAllYearStudy($search){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql = " SELECT 
					 id,
					fromYear,
					toYear,
					createDate,
				   (SELECT  CONCAT(first_name) FROM rms_users WHERE id=userId )AS user_name
			";
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM rms_academicyear ";
		
		$order = ' ORDER BY id DESC '; 
		$where = ' WHERE fromYear!="" ';
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " fromYear LIKE '%{$s_search}%'";
			$s_where[] = " toYear LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.=' AND status='.$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);
	}	
	
}

