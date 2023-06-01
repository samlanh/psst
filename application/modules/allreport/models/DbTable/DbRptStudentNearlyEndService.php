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

    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$branch = "branch_namekh";
    		$item_detail = "item.title";
    		$item = "title";
    	}else{ // English
    		$label = "name_en";
    		$branch = "branch_nameen";
    		$item_detail = "item.title_en";
    		$item = "title_en";
    	}
    	
    	$sql="SELECT 
    		     (SELECT branch_namekh FROM `rms_branch` WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
				 s.stu_code AS stu_code,
				 s.stu_khname AS stu_khname,
				 s.stu_enname AS first_name,
				 s.last_name AS last_name,
				 s.tel,
				 (SELECT $label from rms_view where rms_view.type=2 and key_code=s.sex LIMIT 1) AS sex,
				 sp.`receipt_number` AS receipt,
				 spd.`payment_id` AS payment_id,
				 spd.`start_date` AS start_date,
				 spd.`validate` AS end_date,
				 sp.create_date ,
				 (SELECT $item FROM `rms_items` WHERE rms_items.id=(SELECT item.items_id FROM `rms_itemsdetail` AS item WHERE item.id=spd.itemdetail_id LIMIT 1) LIMIT 1 ) AS category_name,
				 (SELECT $item_detail FROM `rms_itemsdetail` AS item WHERE item.id=spd.itemdetail_id LIMIT 1) AS service_name
			FROM
				   rms_student as s,
				   rms_group_detail_student as gd,
				  `rms_student_paymentdetail` AS spd,
				  `rms_student_payment` AS sp
			WHERE
					gd.itemType=1 
					AND s.stu_id=gd.stu_id
					AND gd.is_current=1
					AND gd.is_maingrade=1
					AND gd.stop_type=0 
					AND  spd.`is_start` = 1 
				  AND sp.id=spd.`payment_id`
    			  AND sp.is_void!=1  $branch_id
    			  and s.stu_id = sp.student_id
    			  and spd.is_suspend = 0 
    			  AND spd.is_onepayment =0 ";
    	$sql.=" ";
     	$order=" ORDER by spd.itemdetail_id ASC ";
     	$where=" ";
     	$to_date = (empty($search['end_date']))? '1': "spd.validate <= '".$search['end_date']." 23:59:59'";
     	
     	$where .= " AND ".$to_date;
     	if($search['item']>0){
     		$where .=" and item.items_id=".$search['item'];
     	}
     	if(!empty($search['service'])){
     		$where .=" and item.id=".$search['service'];
     	}
 
     	if(($search['branch_id']>0)){
     		$where.= " AND sp.branch_id = ".$search['branch_id'];
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
//     	echo $sql.$where.$order;exit();	
    	return $db->fetchAll($sql.$where.$order);
    }
} 