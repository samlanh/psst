<?php

class Allreport_Model_DbTable_DbRptAcademicYear extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_academicperiod';
    public function getAllAcademic($search){
    	$db = $this->getAdapter();
    	$sql = "select CONCAT(fromyear,' - ',toyear)AS academic,batch,study_start,study_end,duration,note,quarter_start,quarter_end,
    			semester_start,semester_end,yearly_start,yearly_end from rms_academicperiod";	
    	
    	$where=' where 1';
    	$order=" order by id DESC";
    	
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['txtsearch']));
    		$s_where[] = " CONCAT(fromyear,'-',toyear) LIKE '%{$s_search}%'";
    		$s_where[] = " batch LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}

    	return $db->fetchAll($sql.$where.$order);
    	 
    }
   
    
       
}