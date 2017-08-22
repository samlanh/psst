<?php

class Allreport_Model_DbTable_DbSuspendService extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_car';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('authstu');
//     	return $session_user->user_id;
    	 
//     }
public function  getStudetnSuspendServiceDetail($search=null){
		try{
			$db = $this->getAdapter();
			$sql = 'SELECT 
			ss.student_id AS stu_id,
			s.`stu_enname` AS en_name,
			s.`stu_khname` AS kh_name,
			s.`stu_code` AS stu_code,
			(SELECT `name_en` FROM `rms_view` WHERE `type`=2 AND `key_code`=s.`sex`) AS sex,
			(SELECT CONCAT(`from_academic`,"-",`to_academic`,"(",generation,")") FROM `rms_tuitionfee` WHERE id =ss.`year`) AS academic,
			ss. suspend_no,
			p.title as service,
			(SELECT title FROM `rms_program_type` WHERE id=p.ser_cate_id LIMIT 1) AS service_type,
			(SELECT `name_en` FROM `rms_view` WHERE `type`=5 AND `key_code`= type_suspend LIMIT 1)AS type_suspend,
			reason,sd.note,
			ss.define_date
			FROM rms_suspendservicedetail AS sd,
			rms_suspendservice AS ss,
			rms_student AS s,
			rms_program_name as p
			WHERE 
			sd.suspendservice_id=ss.id AND
			sd.status=1 
			AND s.stu_id = ss.student_id
			AND p.service_id = sd.service_id ';
			$order = ' ORDER BY sd.id DESC ';
			
			$from_date =(empty($search['start_date']))? '1': " ss.define_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " ss.define_date <= '".$search['end_date']." 23:59:59'";
			$where= " AND ".$from_date." AND ".$to_date;
			
			if($search['service_type']>0){
				$where .=" AND p.ser_cate_id = ".$search['service_type'];
			}
			if($search['service']>0){
				$where .=" AND sd.service_id = ".$search['service'];
			}
// 			if($search['degree']>0){
// 				$where .=" AND sd.service_id = ".$search['degree'];
// 			}
			
// 			if(!empty($search['grade_all'])){
// 				$where .=" AND sd.service_id = ".$search['grade_all'];
// 			}
			
			if(!empty($search['study_year'])){
				$where .=" AND ss.year = ".$search['study_year'];
			}
			if(!empty($search['title'])){
				$s_where = array();
				$s_search = addslashes(trim($search['title']));
				$s_where[] = " ss.suspend_no  LIKE '%{$s_search}%'";
				$s_where[] = " s.`stu_enname` LIKE '%{$s_search}%'";
				$s_where[] = " s.`stu_khname` LIKE '%{$s_search}%'";
				$s_where[] = " s.`stu_code` LIKE '%{$s_search}%'";
				$where .=' AND ( '.implode(' OR ',$s_where).')';
			}
			return $db->fetchAll($sql.$where.$order);
		}catch (Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getStudetnByid($id){
		try{
			$db = $this->getAdapter();
			$sql = 'SELECT id,
			(SELECT `stu_code` FROM `rms_student` WHERE `stu_id`= student_id LIMIT 1) AS student_id,
			(SELECT CONCAT (`from_academic`,"-",`to_academic`) FROM `rms_tuitionfee`WHERE id = year LIMIT 1) as year,
			(SELECT `stu_enname` FROM `rms_student` WHERE `stu_id`= student_id LIMIT 1) AS name_en,
			(SELECT `stu_khname` FROM `rms_student` WHERE `stu_id`= student_id LIMIT 1) AS name_kh,
			(SELECT (SELECT	`name_en` FROM `rms_view` WHERE `type`= 2 AND `key_code`=`sex` LIMIT 1) FROM `rms_student` WHERE `stu_id`= student_id LIMIT 1) AS sex
			FROM rms_suspendservice WHERE id='.$id;
			return $db->fetchRow($sql);
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
    
}