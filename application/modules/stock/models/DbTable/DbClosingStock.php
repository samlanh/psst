<?php

class Stock_Model_DbTable_DbClosingStock extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_closing';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	
    function getAllClosingStock($search){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
  
    	$sql="SELECT 
					cl.id,
					(SELECT branch_namekh FROM `rms_branch` WHERE br_id=cl.branchId LIMIT 1) AS projectName,
					cl.closingDate,
					cl.note,
					(SELECT create_date FROM `rms_adjuststock` WHERE rms_adjuststock.id= cl.adjustId LIMIT 1) AS adjustDate,
					(SELECT first_name FROM rms_users WHERE id=cl.userId LIMIT 1 ) AS user_name
				FROM `rms_closing` AS cl WHERE 1";

		$where="";
		$from_date =(empty($search['start_date']))? '1': " cl.closingDate >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " cl.closingDate <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;

		if(!empty($search['title'])){
    		$s_where=array();
    		$s_search = str_replace(' ', '', addslashes(trim($search['title'])));
    		$s_where[]= " REPLACE((SELECT branch_namekh FROM `rms_branch` WHERE br_id=cl.branchId LIMIT 1),' ','') LIKE '%{$s_search}%'";
    		$s_where[]= " REPLACE(note,' ','') LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	
    	if($search['branch_id']>0 and !empty($search['branch_id'])){
    		$where.= " AND cl.branchId = ".$search['branch_id'];
    	}
    	if(!empty($search['adjustDate'])){
    		$where.= " AND cl.adjustId = ".$search['adjustDate'];
    	}
		
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$where.= $dbg->getAccessPermission('so.projectId');
    	$order=' ORDER BY cl.id DESC  ';
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where.$order);
    }
	

    function updatePreviousClosingEntry($branchId,$endDate){
    	$sql="SELECT id FROM $this->_name WHERE branchId=".$branchId." ORDER BY closingDate DESC LIMIT 1";
    	$db = $this->getAdapter();
    	$closedId = $db->fetchOne($sql);
    	
    	if(!empty($closedId)){
    		$arr = array(
    				'toDate'=>$endDate
    			);
    		$where='id='.$closedId;
    		$this->update($arr, $where);
    	}
    }
   
	
    function addClosingStock($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$this->updatePreviousClosingEntry($data['branch_id'], $data['date']);
    		
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		$arr = array(
    				'branchId'=>$data['branch_id'],
    				'adjustId'=>$data['adjust_date'],
    				'closingDate'=>$data['date'],
    				'note'=>$data['note'],
    				'userId'=>$this->getUserId(),
    				'createDate'=>date('Y-m-d'),
    			);
    		$closeId = $this->insert($arr);
    		
    		
    		$arr = array(
    				'is_closed'=>1
    			);
    		$this->_name='rms_adjuststock';
    		$where='id='.$data['adjust_date'];
    		$this->update($arr, $where);
    		
    		$param = array(
    			'branch_id'=>$data['branch_id'],
    			//'isCountStock'=>1
    		);
    		
    		$results = $dbs->getProductLocationbyBranch($param);
    		
    		if(!empty($results)){
    			foreach($results as $result){
    				$arr = array(
    					'closingId'=>$closeId,
    					'branchId'=>$data['branch_id'],
    					'proId'=>$result['id'],
    					'qtyBegining'=>$result['currentQty'],
    					'costing'=>$result['costing'],
    				);
    				$this->_name='rms_closing_detail';
    				$id = $this->insert($arr);
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/stock/stockclosing/add",2);
    	}
    }

	/*
    function upateAdjustStock($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$row = $this->getDataRow($data['id']);
    		$projectId = $row['projectId'];
    		
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		$arr = array(
    				'isApproved'=>1,
    				'approvedBy'=>$this->getUserId(),
    				'approvedDate'=>date('Y-m-d'),
    			);
    		$where = 'id='.$data['id'];
    		$this->_name='st_adjust_stock';
    		$this->update($arr, $where);
    
    		
    		$results = $this->getDataAllRow($data['id']);
    		if(!empty($results)){
    			foreach($results as $result){
    				$arr = array(
    					'qty'=>$result['exactQty'],
    				);
    					
    				$this->_name='st_product_location';
    				$where = 'projectId='.$projectId." AND proId=".$result['proId'];
    				$this->update($arr, $where);
    				
    				$param = array(
    					'branch_id'=>$projectId,
    					'productId'=>$result['proId'],
    				);
    				
					$qtyDifferent = $result['exactQty']- $result['currentQty'];
   					$dbs->addProductHistoryQty($projectId,$result['proId'],7,$qtyDifferent,$result['id']);//movement'
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/stockinout/adjuststock/add",2);
    	}
    }
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	$this->_name='st_adjust_stock';
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$approved = $tr->translate("APPROVED");
    	$reject =  $tr->translate("REJECTED");
    	
    	$sql=" SELECT 
    		id,
    		projectId,
    		isApproved,
			(SELECT project_name FROM `ln_project` WHERE br_id=projectId LIMIT 1) AS projectName,
			(SELECT first_name FROM rms_users WHERE id=userId LIMIT 1 ) AS user_name,
			note,
    		DATE_FORMAT(adjustDate,'%d-%m-%Y') AS adjustDate,
	    	CASE WHEN isApproved=1 THEN '$approved'
	    		ELSE '$reject'
	    	END AS status,
		   (SELECT first_name FROM rms_users WHERE id=approvedBy LIMIT 1) approvedBy
		    	
    	FROM $this->_name WHERE id=".$recordId;
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$sql.= $dbg->getAccessPermission('projectId');
    	
    	$sql.=" LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getDataAllRow($recordId){
    	$db = $this->getAdapter();
    	$this->_name='st_adjust_detail';
    	$sql=" SELECT 
    				ad.id,
    				 ad.currentQty,
    				 ad.exactQty,
    				 ad.note,
    				 ad.proId,
    				 (SELECT `proCode` FROM `st_product` WHERE st_product.`proId`=ad.proId LIMIT 1) AS proCode,
					 (SELECT `proName` FROM `st_product` WHERE st_product.`proId`=ad.proId LIMIT 1) AS proName,
					 (SELECT measureLabel FROM st_product p WHERE p.proId=ad.proId LIMIT 1) measureLabel
    		FROM $this->_name as ad WHERE ad.adjustId=".$recordId." ";
    	return $db->fetchAll($sql);
    }
    function getAllAdjusted($data){
    	$db = $this->getAdapter();
    	$this->_name='st_adjust_stock';
    	
    	$sql=" SELECT 
    		id,
    		DATE_FORMAT(adjustDate,'%d-%m-%Y') AS name
    	FROM $this->_name WHERE 1 ";
    	
    	if(isset($data['isApproved'])){
    		$sql.=" AND isApproved=".$data['isApproved'];
    	}
    	if(isset($data['branch_id'])){
    		$sql.=" AND projectId=".$data['branch_id'];
    	}
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$sql.= $dbg->getAccessPermission('projectId');
    	
    	return $db->fetchAll($sql);
    }
    
    function getAllClosingDate($search){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	
    	$sql="SELECT
		    	cl.id,
		    	CONCAT(DATE_FORMAT(cl.closingDate,'%d-%m-%Y'),'/',DATE_FORMAT(cl.toDate,'%d-%m-%Y')) AS name
    		FROM `st_closing` cl WHERE 1 ";
    	
    	$where='';
    	if($search['branch_id']>-1){
    		$where.= " AND cl.projectId = ".$search['branch_id'];
    	}
    	 
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$where.= $dbg->getAccessPermission('cl.projectId');
    	 
    	$order=' ORDER BY cl.id DESC  ';
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where.$order);
    }
	*/
}