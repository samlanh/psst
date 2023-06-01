<?php

class Allreport_Model_DbTable_DbRptCar extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_car';
    public function getAllCar($search){
    	$db = $this->getAdapter();
    	$sql = "SELECT carid,carname,drivername,tel,zone,note,status FROM rms_car ";
    	$where=' where 1';
    	$order=" order by id DESC";
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " carid LIKE '%{$s_search}%'";
    		$s_where[] = " carname LIKE '%{$s_search}%'";
    		$s_where[] = " drivername LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
 
    	return $db->fetchAll($sql.$where.$order);
    	 
    }
   
    
    
    
    
    
    
}