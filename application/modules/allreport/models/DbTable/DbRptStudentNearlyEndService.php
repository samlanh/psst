<?php

class Allreport_Model_DbTable_DbRptStudentNearlyEndService extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_tuitionfee';
    function getAllStudentNearlyEndService($search){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('sp.branch_id');
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$data=$key->getKeyCodeMiniInv(TRUE);
    	
    	if (!empty($data['payment_day_alert'])){
    		$alert = $data['payment_day_alert'];
    		$search['end_date'] = date('Y-m-d',strtotime($search['end_date']."+$alert day"));
    	}
    	$sql="SELECT 
    		(SELECT branch_namekh FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
				 s.stu_code AS code,
				 s.stu_enname AS name,
				 s.last_name AS last_name,
				 s.tel,
				 (SELECT name_en from rms_view where rms_view.type=2 and key_code=s.sex LIMIT 1) AS sex,
				 (SELECT name_en from rms_view where type=4 and key_code =s.session LIMIT 1) as session,
				 (SELECT rms_itemsdetail.title from rms_itemsdetail where rms_itemsdetail.id = s.grade LIMIT 1) as grade,
				 (SELECT g.group_code FROM `rms_group` AS g WHERE g.id=s.group_id LIMIT 1 ) AS group_name,
				 sp.`receipt_number` AS receipt,
				 spd.`start_date` AS start,
				 spd.`validate` AS end,
				 sp.create_date ,
				 item.title AS service_name,
				 (SELECT title FROM `rms_items` WHERE rms_items.id=item.items_id LIMIT 1 ) AS category_name
			FROM
				  `rms_student_paymentdetail` AS spd,
				  `rms_student_payment` AS sp,
				  rms_student as s,
				  rms_itemsdetail AS item
			WHERE 
				  spd.`is_start` = 1 
				  AND sp.id=spd.`payment_id`
				  AND spd.itemdetail_id=item.`id`
    			  AND sp.is_void!=1  $branch_id
    			  and s.stu_id = sp.student_id
    			  and spd.is_suspend = 0 
    			  AND spd.is_onepayment =0 ";
    	$sql.=" AND s.is_subspend=0 ";
     	$order=" ORDER by item.items_id ASC ";
     	$where=" ";
     	$to_date = (empty($search['end_date']))? '1': "spd.validate <= '".$search['end_date']." 23:59:59'";
     	
     	$where .= " AND ".$to_date;
     	if(!empty($search['service'])){
     		$where .=" and item.id=".$search['service'];
     	}
     	if(($search['service_type']>0)){
     		$where.= " AND item.items_type = ".$search['service_type'];
     	}
     	if(($search['grade_all']>0)){
     		$where.= " AND s.grade = ".$search['grade_all'];
     	}
     	if(($search['group']>0)){
     		$where.= " AND s.group_id = ".$search['group'];
     	}
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " s.last_name LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}    		
    	return $db->fetchAll($sql.$where.$order);
    }
} 