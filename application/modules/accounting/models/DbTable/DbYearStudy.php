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

			$dept = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($dept)){
	    			$dept = $rs;
	    		}else{ $dept = $dept.",".$rs;
	    		}
	    	}

			$isCurrent = empty($_data['isCurrent'])?0:1;

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
					'degreeList'  => $dept,
					'isCurrent'  => $isCurrent,
					'createDate' => date("Y-m-d H:i:s"),
					'modifyDate' => date("Y-m-d H:i:s"),
					'status'  	  => 1,
					'userId'	  => $this->getUserId()
			);
			$id = $this->insert($_arr);
			
			if($isCurrent==1){
				$_arrCurrent=array(
					'isCurrent'  => 0,
					'modifyDate' => date("Y-m-d H:i:s"),
					'userId'	  => $this->getUserId()
				);
				$where=" isCurrent = 1 AND id !=".$id;
				$this->update($_arrCurrent, $where);
			}
			
		}catch (Exception $e){
			$db->rollBack();
		}
	}
	public function updateAcademicYear($_data){
		$db = $this->getAdapter();
		try{

			$dept = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($dept)){
	    			$dept = $rs;
	    		}else{ $dept = $dept.",".$rs;
	    		}
	    	}
			$isCurrent = empty($_data['isCurrent'])?0:1;
			$status = empty($_data['status'])?0:1;

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
					'degreeList'  => $dept,
					'isCurrent'   => $isCurrent,
					'modifyDate'  => date("Y-m-d H:i:s"),
					'status'  	  => $status,
					'userId'	  => $this->getUserId()
			);
			$where=" id = ".$_data['id'];
			$this->update($_arr, $where);
			
			if($isCurrent==1){
				$_arrCurrent=array(
					'isCurrent'  => 0,
					'modifyDate' => date("Y-m-d H:i:s"),
					'userId'	  => $this->getUserId()
				);
				$whereCurrent=" isCurrent = 1 AND id !=".$_data['id'];
				$this->update($_arrCurrent, $whereCurrent);
			}
			
		}catch (Exception $e){
		    Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
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
		$default = ' <span class="glyphicon glyphicon-check"></span>';
		$notDefault = '<span class="glyphicon glyphicon-unchecked"></span>';
		$sql = " SELECT 
					 id,
					fromYear,
					toYear,
					(SELECT GROUP_CONCAT(i.shortcut) FROM rms_items AS i WHERE i.type=1 AND FIND_IN_SET(i.id,degreeList) LIMIT 1) AS degreeList,
					createDate,
				   (SELECT  CONCAT(first_name) FROM rms_users WHERE id=userId )AS user_name,
				    CASE
						WHEN  isCurrent = 1 THEN '$default'
		   				WHEN  isCurrent = 0 THEN '$notDefault'
					END AS isCurrent
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

