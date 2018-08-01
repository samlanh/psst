<?php

class Allreport_Model_DbTable_DbRptStudentPaymentLate extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('authstu');
//     	return $session_user->user_id;
    	 
//     }
    function getAllStudentPaymentLate($search){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('sp.branch_id');
    	$sql="SELECT 
				  sp.`receipt_number` AS receipt,
				  s.stu_id AS code,
				  s.stu_enname AS name,
				  s.tel,
				  (select name_en from rms_view where rms_view.type=2 and key_code=s.sex) AS sex,
				  (SELECT g.group_code FROM `rms_group` AS g WHERE g.id=s.group_id LIMIT 1 ) AS group_name,
				  pn.`title` service,
				  spd.`start_date` as start,
				  spd.`validate` as end,
				  spd.`type`,
				  spd.id AS payment_id,
				  spd.is_onepayment,
				   sp.create_date,
				  (select major_enname from rms_major where major_id = s.grade LIMIT 1) as grade,
				  (select name_en from rms_view where type=4 and key_code =s.session LIMIT 1) as session,
				  (SELECT title FROM rms_program_type WHERE rms_program_type.id=p.ser_cate_id AND p.type=2 LIMIT 1) service_type
				  
				FROM
				  `rms_student_paymentdetail` AS spd,
				  `rms_student_payment` AS sp,
				  `rms_program_name` AS pn,
				  rms_student as s,
				  rms_program_name AS p
				WHERE 
				  spd.`is_start` = 1 
				  AND sp.id=spd.`payment_id`
				  AND p.service_id=spd.service_id 
				  AND spd.`service_id`=pn.`service_id`
    			  $branch_id	
    			  and sp.is_void=0
    			  and sp.student_id=s.stu_id
    			  and sp.is_suspend = 0 
    			  AND spd.is_onepayment =0 ";
    	
     	$order=" ORDER by spd.type ASC ";
     	$to_date = (empty($search['end_date']))? '1': "spd.validate <= '".$search['end_date']." 23:59:59'";
     	$where = " AND ".$to_date;
     	
     	if(!empty($search['service'])){
     		$where .= " and spd.service_id = ".$search['service'];
     	}
     	if(($search['grade_all']>0)){
     		$where.= " AND s.grade = ".$search['grade_all'];
     	}
     	if(($search['session']>0)){
     		$where.= " AND s.session = ".$search['session'];
     	}
     	if(($search['group']>0)){
     		$where.= " AND s.group_id = ".$search['group'];
     	}
     	if(($search['service_type']>0)){
     		$where.= " AND p.ser_cate_id = ".$search['service_type'];
     	}
     	if(($search['stu_name']>0)){
     		$where.= " AND s.stu_id = ".$search['stu_name'];
     	}
     	if(($search['stu_code']>0)){
     		$where.= " AND s.stu_id = ".$search['stu_code'];
     	}
     	
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
    		$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
    		$s_where[] = " (select title from rms_program_name where rms_program_name.service_id=spd.service_id LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " spd.comment LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
       return $db->fetchAll($sql.$where.$order);
    }
    function submitlatePayment($data){
    	$ids = explode(',', $data['identity']);
    	$key = 1;
    	$this->_name='rms_student_paymentdetail';
    	foreach ($ids as $i){
    		$arr = array(
    				'is_onepayment'=>$data['onepayment_'.$i],
    				'start_date'=>$data['create_date'.$i],
    				'validate'=>$data['end_date'.$i],
    			);
    		$where="id = ".$data['payment_id'.$i];
    		$this->update($arr, $where);
    	}
    }
    
}
   
    
   