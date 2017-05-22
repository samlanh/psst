<?php

class Allreport_Model_DbTable_DbRptStudentDrop extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student_drop';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('auth');
//     	return $session_user->user_id;
    	 
//     }
    public function getAllStudentDrop($search){
    	$db = $this->getAdapter();
    	$sql = "SELECT st.stu_code as stu_id, CONCAT(st.stu_khname,' - ',st.stu_enname) as name,
    	(select CONCAT(from_academic,'-',to_academic,'(',generation,')') from rms_tuitionfee where rms_tuitionfee.id=st.academic_year) as academic_year,
    	(select name_en from rms_view where rms_view.type=4 and rms_view.key_code=st.session limit 1)AS session,
    	(select major_enname from rms_major where rms_major.major_id=st.grade limit 1)AS grade,
		(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=2 and `rms_view`.`key_code`=st.sex )AS sex,
		(SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=stdp.`type`) as type,stdp.note,stdp.date,
		(select name_kh from `rms_view` where `rms_view`.`type`=6 and `rms_view`.`key_code`=`stdp`.`status`)AS status
		 from rms_student_drop as stdp,rms_student as st where stdp.stu_id=st.stu_id and stdp.status=1 ";

    	$where=' ';
    	$order=" order by id DESC";
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	
    	if(empty($search)){
    		return $db->fetchAll($sql);
    	}
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " st.stu_code LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT CONCAT(stu_khname,' - ',stu_enname) FROM `rms_student` WHERE `rms_student`.`stu_id`=`stdp`.`stu_id`) LIKE '%{$s_search}%'";
    		$s_where[] = "  (SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=`stdp`.`type`) LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if(!empty($search['study_year'])){
    		$where.=' AND academic_year='.$search['study_year'];
    	}
    	if(!empty($search['grade_bac'])){
    		$where.=' AND grade='.$search['grade_bac'];
    	}
    	if(!empty($search['session'])){
    		$where.=' AND session='.$search['session'];
    	}
    	
//     	$searchs=$search['txtsearch'];
    	
//     	if($search['searchby']==0){
//     		$where.='';
//     	}
//     	if($search['searchby']==1){
//     		$where.=" AND stu_id  LIKE  '%".$searchs."%' ";
//     	}
//     	if($search['searchby']==2){
//     		$where.=" AND (SELECT CONCAT(stu_khname,' - ',stu_enname) FROM `rms_student` WHERE `rms_student`.`stu_id`=`rms_student_drop`.`stu_id`) LIKE '%".$searchs."%'";
//     	}
//     	if($search['searchby']==3){
//     		$where.=" AND (SELECT name_kh FROM `rms_view` WHERE `rms_view`.`type`=5 and `rms_view`.`key_code`=`rms_student_drop`.`type`) LIKE '%".$searchs."%'";
//     	}
    	
    	return $db->fetchAll($sql.$where.$order);
    	 
    }
   
    
    
    
    
    
    
    
}