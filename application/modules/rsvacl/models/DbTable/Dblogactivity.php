<?php 

class RsvAcl_Model_DbTable_Dblogactivity extends Zend_Db_Table_Abstract
{
	protected  $_name = "rms_acl_acl";
	
	
	function getAllUserLogActivity($data){
		$sql="
		SELECT 
			id,
			(SELECT first_name FROM `rms_users` WHERE id=user_id LIMIT 1 ) USER_NAME,
			ipaddress,
			DATE_FORMAT(log_date,'%d/%m/%Y :%H:%i:%s') log_date,
			log_type
			FROM 
			`rms_user_log` WHERE 1 ";
		
		$from_date =(empty($data['start_date']))? '1': " log_date >= '".$data['start_date']." 00:00:00'";
		$to_date = (empty($data['end_date']))? '1': " log_date <= '".$data['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		 
		
		if(($data['user']>0)){
			$where.= " AND user_id = ".$data['user'];
		}
		return $this->getAdapter()->fetchAll($sql.$where);
		
	}
	
}

?>
