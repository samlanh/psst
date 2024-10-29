<?php

class Allreport_Model_DbTable_DbSuspendService extends Zend_Db_Table_Abstract
{

	public function  getStudetnSuspendServiceDetail($search=null){
		$db = $this->getAdapter();
		try{
			$dbp = new Application_Model_DbTable_DbGlobal();
			$branch= $dbp->getBranchDisplay();
			$lang = $dbp->currentlang();
			
			$label = "name_en";
			$service = "title_en";
			if($lang==1){// khmer
				$label = "name_kh";
				$service = "title";
			}
			$sql ="SELECT 
			
					ss.`id` AS id
					,ss.`student_id` AS studentId
					,ss.`create_date`
					,(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id = ss.branch_id LIMIT 1) AS branchName
					,s.`stu_code` AS stuCode
					,s.`stu_khname` AS stuNameKh
					,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stuNameEn
					,(SELECT it.$service FROM rms_items AS it WHERE it.id = itd.items_id LIMIT 1) AS category
					,itd.`title` AS service_name
					,itd.`title` AS serviceName
					,(SELECT $label FROM rms_view AS v WHERE v.type=1 and v.key_code = ss.status LIMIT 1) as status
			   		,(SELECT CONCAT(u.first_name) FROM rms_users AS u WHERE u.id = ss.user_id LIMIT 1) as user
			   		,ssd.reason
			   		,ssd.stopDate
	   			FROM 
	   				 (`rms_suspendservice` AS ss JOIN rms_suspendservicedetail AS ssd ON ss.`id` = ssd.`suspendservice_id`)
					LEFT JOIN `rms_itemsdetail` AS itd ON itd.`id` = ssd.`spd_id`
					JOIN rms_student AS s ON s.`stu_id` = ss.`student_id`
	   			WHERE 
	   				1
	   		";
			
			$order = ' ORDER BY ss.id DESC,ssd.`spd_id` ASC ';
			
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
		   		$where.=" AND itd.items_id=".$search['category'];
				$arrCon = array(
					"categoryId" => $search['category'],
				);
				$condiction = $dbp->getChildItems($arrCon);
				if (!empty($condiction)){
					$where.=" AND itd.items_id IN ($condiction)";
				}else{
					$where.=" AND itd.items_id=".$search['item'];
				}
		   	}
			if(!empty($search['adv_search'])){
		   		$s_where = array();
		   		$s_search = addslashes(trim($search['adv_search']));
		   		$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
		   		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
		   		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
		   		$s_where[] = " s.last_name LIKE '%{$s_search}%'";
		   		$s_where[] = " itd.`title` LIKE '%{$s_search}%'";
		   		$s_where[] = " itd.`title_en` LIKE '%{$s_search}%'";
		   		$where .=' AND ( '.implode(' OR ',$s_where).')';
		   	}
			$where.=$dbp->getAccessPermission('ss.branch_id');
			return $db->fetchAll($sql.$where.$order);
		}catch (Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
}