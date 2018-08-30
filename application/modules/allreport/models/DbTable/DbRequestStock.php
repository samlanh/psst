<?php
class Allreport_Model_DbTable_DbRequestStock extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_request_order';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	
	}
	
	function getAllRequestProduct($search=null){
		$db = $this->getAdapter();
		$sql="SELECT 
					re.*,
					(select title from rms_request_for as rf where rf.id = request_for) as request_for,
    				(select title from rms_for_section as fs where fs.id = for_section) as for_section,
					(select branch_namekh from rms_branch where br_id = branch_id) as branch_name,
					(select first_name from rms_users as u where u.id = re.user_id) as user,
					(select name_en from rms_view where type=1 and key_code = re.status) as status
				FROM 
					rms_request_order AS re
				WHERE 
					1
			";
		
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
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($search['status_search']==1 OR $search['status_search']==0){
			$where.=" AND re.status=".$search['status_search'];
		}
		
		if($search['request_for']>0){
			$where.=" AND request_for=".$search['request_for'];
		}
		if($search['for_section']>0){
			$where.=" AND for_section=".$search['for_section'];
		}
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		$order=" ORDER BY re.id DESC";
		//echo $sql;
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getAllRequestProductDetail($search=null){
		$db = $this->getAdapter();
		$sql="SELECT 
					req_d.*,
					req_d.id,
					(SELECT branch_namekh FROM rms_branch AS b WHERE b.br_id = req_d.`branch_id`) AS branch_name,
					(SELECT title from rms_request_for as rf where rf.id = req.request_for) as request_for,
    				(SELECT title from rms_for_section as fs where fs.id = req.for_section) as for_section,
					(SELECT pro_code FROM `rms_product` AS p WHERE p.id = req_d.`pro_id`) AS pro_code,
					(SELECT pro_name FROM `rms_product` AS p WHERE p.id = req_d.`pro_id`) AS pro_name,
					SUM(req_d.`qty_request`) AS total_request,
					SUM(req_d.`qty_receive`) AS total_receive,
					SUM(req_d.`price`*req_d.`qty_receive`) AS total_price
				FROM
					rms_request_order AS req,
					`rms_request_orderdetail` AS req_d 
				WHERE 
					req.`id` = req_d.`request_id`
					AND req.`status`=1
			";
	
		$where="";
	
		$from_date =(empty($search['start_date']))? '1': " req.request_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " req.request_date <= '".$search['end_date']." 23:59:59'";
		$where .= " AND ".$from_date." AND ".$to_date;
	
		$group = " GROUP BY req_d.branch_id, req_d.pro_id";
		$order = " ORDER BY req_d.branch_id ASC, req_d.`pro_id` ASC";
		
	
		if($search['request_for']>0){
			$where.=" AND request_for=".$search['request_for'];
		}
		if($search['for_section']>0){
			$where.=" AND for_section=".$search['for_section'];
		}
		if($search['branch_id']>0){
			$where.=" AND req_d.branch_id=".$search['branch_id'];
		}
	
		if($search['category_id']>0){
			$where.=" AND (SELECT cat_id FROM `rms_product` AS p WHERE p.id = req_d.`pro_id`) =".$search['category_id'];
		}
		if($search['product']>0){
			$where.=" AND (SELECT id FROM `rms_product` AS p WHERE p.id = req_d.`pro_id`) =".$search['product'];
		}
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('req_d.branch_id');
		
		//echo $sql.$where.$group.$order;
		
		return $db->fetchAll($sql.$where.$group.$order);
	}
	
	
	
	
	function getRequestProductById($id){
		$db = $this->getAdapter();
		$sql="SELECT
					re.*,
					(select title from rms_request_for as rf where rf.id = request_for) as request_for,
    				(select title from rms_for_section as fs where fs.id = for_section) as for_section,
					(select branch_namekh from rms_branch where br_id = branch_id) as branch_name,
					(select first_name from rms_users as u where u.id = re.user_id) as user,
					(select name_en from rms_view where type=1 and key_code = re.status) as status
				FROM
					rms_request_order AS re
				WHERE
					re.id = $id
		";
		return $db->fetchRow($sql);
	}
	
	function getAllRequestProductDetailById($id){
		$db = $this->getAdapter();
		$sql="SELECT
					*,
					(select branch_namekh from rms_branch where br_id = branch_id) as branch_name,
					(select pro_name from rms_product as p where p.id = pro_id) as pro_name
				FROM
					rms_request_orderdetail 
				WHERE
					request_id = $id
			";
		return $db->fetchAll($sql);
	}
	
	
	function getAllAdjustStockDetail($search=null){
		$db = $this->getAdapter();
		$sql="SELECT  ad.adjust_no,ad.request_name,ad.note,ad.request_date,ad.create_date,
		        (SELECT b.branch_nameen FROM rms_branch AS b WHERE b.br_id=adj.branch_id LIMIT 1)AS branch_name,
		       (SELECT it.title FROM `rms_itemsdetail` AS it WHERE it.id=adj.pro_id AND it.items_type=3 LIMIT 1 ) AS pro_name,
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
					$s_where[]= " REPLACE((SELECT it.title FROM `rms_itemsdetail` AS it WHERE it.id=adj.pro_id AND it.items_type=3 LIMIT 1 ),' ','') LIKE '%{$s_search}%'";
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



