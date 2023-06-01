<?php

class Allreport_Model_DbTable_DbSuspendService extends Zend_Db_Table_Abstract
{

	public function  getStudetnSuspendServiceDetail($search=null){
		$db = $this->getAdapter();
		try{
			$dbp = new Application_Model_DbTable_DbGlobal();
			$lang = $dbp->currentlang();
			if($lang==1){// khmer
				$label = "name_kh";
				$service = "title";
				$branch = "branch_namekh";
			}else{ // English
				$label = "name_en";
				$branch = "branch_nameen";
				$service = "title_en";
			}
			$sql ="SELECT 
	   				ss.id,
	   				ss.student_id,
	   				(SELECT $branch from rms_branch where br_id = ss.branch_id LIMIT 1) as branch,
			  	 	s.stu_code AS code,
			   		s.stu_khname as kh_name,
			   		CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS en_name,
			   		ss.create_date,
			   		(SELECT CONCAT(first_name) from rms_users where rms_users.id = ss.user_id) as user,
			   		(select $label from rms_view as v where v.type=1 and v.key_code = ss.status) as status,
			   		(select $service from rms_items as it where it.id = idt.items_id) as category,
			   		$service as service_name,
			   		ssd.reason
	   			FROM 
	   				rms_suspendservice as ss,
	   				rms_suspendservicedetail as ssd,
	   				rms_student_paymentdetail as spd,
	   				rms_student as s,
	   				rms_itemsdetail as idt
	   			where 
	   				s.stu_id = ss.student_id
	   				and ss.id = ssd.suspendservice_id
	   				and ssd.spd_id = spd.id
	   				and spd.itemdetail_id = idt.id
	   		";
			
			$order = ' ORDER BY ss.id DESC,spd.itemdetail_id ASC ';
			
			$from_date =(empty($search['start_date']))? '1': " ss.create_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " ss.create_date <= '".$search['end_date']." 23:59:59'";
			$where = " AND ".$from_date." AND ".$to_date;
			
			if(!empty($search['branch_id'])){
		   		$where.=" AND ss.branch_id=".$search['branch_id'];
		   	}
		   	if(!empty($search['stu_name'])){
		   		$where.=" AND ss.student_id=".$search['stu_name'];
		   	}
		   	if(!empty($search['category'])){
		   		$where.=" AND idt.items_id=".$search['category'];
		   	}
			if(!empty($search['adv_search'])){
		   		$s_where = array();
		   		$s_search = addslashes(trim($search['adv_search']));
		   		$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
		   		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
		   		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
		   		$where .=' AND ( '.implode(' OR ',$s_where).')';
		   	}
			return $db->fetchAll($sql.$where.$order);
		}catch (Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
}