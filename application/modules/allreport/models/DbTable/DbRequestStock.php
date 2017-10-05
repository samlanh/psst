<?php
class Allreport_Model_DbTable_DbRequestStock extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_request_order';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	
	}
	
	function getAllRequestProductDetail($search=null){
		$db = $this->getAdapter();
		$sql=" SELECT  re.request_no,re.request_name,re.purpose,re.request_date,
	           (SELECT b.branch_nameen FROM rms_branch AS b WHERE b.br_id=red.branch_id LIMIT 1)AS branch_name,
	           (SELECT p.pro_name FROM rms_product AS p WHERE p.id=red.pro_id LIMIT 1)AS pro_name,
	            red.qty_request AS qty,
	           (SELECT name_en FROM rms_view WHERE key_code=re.status AND rms_view.type=1 LIMIT 1) AS `status`,
			   (SELECT first_name FROM rms_users WHERE id=re.user_id LIMIT 1) AS user_name
			FROM rms_request_order AS re,rms_request_orderdetail AS red 
			WHERE re.id=red.request_id";
		$where="";
		$from_date =(empty($search['start_date']))? '1': " re.request_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " re.request_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['title'])){
			$s_where=array();
			$s_search = str_replace(' ', '', addslashes(trim($search['title'])));
			$s_where[]= " REPLACE(re.request_no,' ','') LIKE '%{$s_search}%'";
			$s_where[]="  REPLACE(re.request_name,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(re.purpose,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(red.qty_request,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE((SELECT p.pro_name FROM rms_product AS p WHERE p.id=red.pro_id LIMIT 1),' ','') LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($search['status_search']==1 OR $search['status_search']==0){
			$where.=" AND re.status=".$search['status_search'];
		}
		if($search['branch_id']){
			$where.=" AND red.branch_id=".$search['branch_id'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		$order=" ORDER BY re.id DESC";
		//echo $sql;
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getAllAdjustStockDetail($search=null){
		$db = $this->getAdapter();
		$sql="SELECT  ad.adjust_no,ad.request_name,ad.note,ad.request_date,ad.create_date,
		        (SELECT b.branch_nameen FROM rms_branch AS b WHERE b.br_id=adj.branch_id LIMIT 1)AS branch_name,
		        (SELECT p.pro_name FROM rms_product AS p WHERE p.id=adj.pro_id LIMIT 1)AS pro_name,
		         adj.qty_befor,adj.qty_after,adj.difference,
		        (SELECT name_en FROM rms_view WHERE key_code=ad.status AND rms_view.type=1 LIMIT 1) AS `status`,
				(SELECT first_name FROM rms_users WHERE id=ad.user_id LIMIT 1) AS user_name
		
				FROM rms_adjuststock AS ad,rms_adjuststock_detail AS adj 
				WHERE ad.id=adj.adjuststock_id";
		$where="";
				$from_date =(empty($search['start_date']))? '1': " ad.request_date >= '".$search['start_date']." 00:00:00'";
				$to_date = (empty($search['end_date']))? '1': " ad.request_date <= '".$search['end_date']." 23:59:59'";
				$where = " AND ".$from_date." AND ".$to_date;
				if(!empty($search['title'])){
					$s_where=array();
					$s_search = str_replace(' ', '', addslashes(trim($search['title'])));
					$s_where[]= " REPLACE(ad.adjust_no,' ','') LIKE '%{$s_search}%'";
					$s_where[]="  REPLACE(ad.request_name,' ','') LIKE '%{$s_search}%'";
					$s_where[]= " REPLACE(ad.note,' ','') LIKE '%{$s_search}%'";
					$s_where[]= " REPLACE((SELECT p.pro_name FROM rms_product AS p WHERE p.id=adj.pro_id LIMIT 1),' ','') LIKE '%{$s_search}%'";
					$where.=' AND ('.implode(' OR ', $s_where).')';
				}
				
				if($search['status_search']==1 OR $search['status_search']==0){
					$where.=" AND ad.status=".$search['status_search'];
				}
				
				if($search['branch_id']){
					$where.=" AND adj.branch_id=".$search['branch_id'];
				}
			
				$dbp = new Application_Model_DbTable_DbGlobal();
				$sql.=$dbp->getAccessPermission('branch_id');
		$order=" ORDER BY ad.id DESC";
		//echo $where;
		return $db->fetchAll($sql.$where.$order);
	}
    
}



