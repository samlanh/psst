<?php

class Allreport_Model_DbTable_DbRptServiceCharge extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_servicefee';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
//     	return $session_user->user_id;
    	 
//     }
    function getAllServiceFee($search){
    	$db=$this->getAdapter();
    	
    	$_db=new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission();
    	
    	$sql = "SELECT sf.id,CONCAT(from_academic,'-',to_academic,'(',generation,')') AS academic,
    		    sf.create_date,sf.status FROM `rms_servicefee` as sf,rms_tuitionfee as tf  WHERE tf.id=sf.academic_year  $branch_id  ";
    	$order=" ORDER BY sf.id DESC ";
    	$where = '';
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	
    	if(!empty($search['year'])){
    		$where.=" AND tf.id = ".$search['year'] ;
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND branch_id = ".$search['branch_id'] ;
    	}
    	
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " tf.from_academic LIKE '%{$s_search}%'";
    		$s_where[] = " tf.to_academic LIKE '%{$s_search}%'";
    		$s_where[] = " tf.generation LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
//     	if(empty($search)){
//     		return $db->fetchAll($sql.$order);
//     	}
//     	$searchs = $search['txtsearch'];
//     	if($search['searchby']==0){
//     		$where.="";
//     	}
//     	if($search['searchby']==1){
//     		$where.= " AND rms_servicefee.generation LIKE '%".$searchs."%' ";
//     	}
//     	if($search['searchby']==2){
//     		$where.=" AND (select title from rms_program_name where rms_program_name.service_id=(select service_id from rms_servicefee_detail where rms_servicefee_detail.service_feeid=rms_servicefee.id limit 1)) LIKE '%".$searchs."%'";
//     	}
//     	echo $sql.$where;
    	return $db->fetchAll($sql.$where.$order);
    	
    }    
    function getServiceFeebyId($service_id,$service_type,$serid){
    	$db = $this->getAdapter();
    	$sql = "SELECT sd.id,sd.service_id,sd.price_fee,sd.payment_term,sd.remark,
    			p.title AS service_name ,
    			(SELECT pt.title FROM `rms_program_type` AS pt WHERE pt.id=p.ser_cate_id LIMIT 1) as ser_type
    			FROM `rms_servicefee_detail` as sd,rms_program_name p 
    			WHERE p.service_id=sd.service_id AND sd.service_feeid=".$service_id;
    	if($service_type>0){
    		$sql.=" AND p.ser_cate_id = ".$service_type;
    	}
    	if($serid>0){
    		$sql.=" AND sd.service_id = ".$serid;
    	}
    	$sql.=" ORDER BY sd.service_id ";
    	return $db->fetchAll($sql);
    }
}




