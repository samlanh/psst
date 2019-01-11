<?php

class Allreport_Model_DbTable_DbRptGroup extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_group';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('authstu');
//     	return $session_user->user_id;
    	 
//     }
	function submitDateList($data){
		$db=$this->getAdapter();
		$this->_name='rms_student';
		if(!empty($data['identity'])){
			$ids = explode(',', $data['identity']);
			foreach ($ids as $i){
				$arr = array(
// 						'dob'=>$data['dob_'.$i]
						'stu_code'=>$data['student_'.$i]
						);
				
				$where=" stu_id = ".$data['stu_id'.$i];
				$this->update($arr, $where);
			}
		}
	} 
    public function getAllGroup($search){
    	$db = $this->getAdapter();
    	$sql = "SELECT `g`.`id`,`g`.`group_code` AS `group_code`,`g`.`semester` AS `semester`,
    	
    	(select CONCAT(from_academic,'-',to_academic,' (',generation,')') from rms_tuitionfee where rms_tuitionfee.id=g.academic_year) as academic_year,
    	
		(SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) AS degree,
		(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) lIMIT 1) AS grade,`g`.`amount_month`,
		
		(SELECT`rms_view`.`name_en`	FROM `rms_view`	WHERE ((`rms_view`.`type` = 4)
		AND (`rms_view`.`key_code` = `g`.`session`))LIMIT 1) AS `session`,
		(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`)) AS `room_name`,
		`g`.`start_date`,`g`.`expired_date`,`g`.`note`,
		(SELECT `rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 1)
		AND (`rms_view`.`key_code` = `g`.`status`)) LIMIT 1) AS `status`
		FROM `rms_group` as `g`  ";	
    	
    	$where= " where 1";
    	$order=" order by id DESC";
   		if(empty($search)){
	   		return $db->fetchAll($sql.$order);
	   	}
	   	
	   	if(!empty($search['title'])){
	   		$s_where = array();
	   		$s_search = addslashes(trim($search['title']));
		   		$s_where[] = " group_code LIKE '%{$s_search}%'";
		   		$s_where[] = " (SELECT rms_room.room_name FROM rms_room	WHERE (rms_room.room_id = g.room_id)) LIKE '%{$s_search}%'";
				$s_where[] = " (SELECT rms_view.name_en	FROM rms_view WHERE ((rms_view.type = 4)
								AND (rms_view.key_code = g.session))LIMIT 1) LIKE '%{$s_search}%'";
		   		//$s_where[] = " (select CONCAT(from_academic,'-',to_academic)) LIKE '%{$s_search}%'";
	    		$s_where[] = " (SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) lIMIT 1) LIKE '%{$s_search}%'";
	   			$s_where[] = " (SELECT rms_items.title FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1) LIKE '%{$s_search}%'";
	   		$where .=' AND ( '.implode(' OR ',$s_where).')';
	   	}
	   	if(!empty($search['study_year'])){
	   		$where.=' AND g.academic_year='.$search['study_year'];
	   	}
	   	if(!empty($search['grade'])){
	   		$where.=' AND g.grade='.$search['grade'];
	   	}
	   	if(!empty($search['session'])){
	   		$where.=' AND g.session='.$search['session'];
	   	}
	   	
    	return $db->fetchAll($sql.$where.$order);
    	 
    }
   public function getStudentGroup($id,$search,$type){
   	$session_lang=new Zend_Session_Namespace('lang');
	$lang_id=$session_lang->lang_id;
		$gender_str = 'name_en';
	if($lang_id==1){//for kh
		$gender_str = 'name_kh';
	}
   	$db = $this->getAdapter();
		$sql="SELECT
					 g.gd_id,
					 `g`.`group_id` AS `group_id`,
					 `g`.`stu_id`   AS `stu_id`,
				  	 `s`.`stu_code` AS `stu_code`,
				     `s`.`stu_khname` AS `kh_name`,
				     `s`.`stu_enname` AS `en_name`,
				     `s`.`last_name` AS `last_name`,
				     `s`.`address` AS `address`,
				     s.pob,
				     `s`.`tel` AS `tel`,
				     `s`.`sex` AS `gender`,
				     `s`.`dob` AS `dob`,
				     s.father_enname AS father_name,
				     (SELECT name_kh FROM rms_view where type=21 and key_code=`s`.`nationality` LIMIT 1) AS nationality,
    				(SELECT name_kh FROM rms_view where type=21 and key_code=`s`.`nation` LIMIT 1) AS nation,
					 (SELECT occu_name FROM `rms_occupation` WHERE occupation_id = s.father_job LIMIT 1) AS father_job,
					 s.mother_enname AS mother_name,
					 (SELECT occu_name FROM `rms_occupation` WHERE occupation_id = s.mother_job LIMIT 1) AS mother_job,
				    (SELECT
				        `rms_view`.$gender_str
				      FROM `rms_view`
				      WHERE ((`rms_view`.`type` = 2)
				             AND (`rms_view`.`key_code` = `s`.`sex`)) LIMIT 1) AS `sex`,
				  `g`.`status`   AS `status`,
				  s.home_num,
				  s.street_num,
				  (SELECT v.village_name FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_name,
			    	(SELECT c.commune_name FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_name,
			    	(SELECT d.district_name FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_name,
			    	(SELECT province_en_name from rms_province where rms_province.province_id = s.province_id LIMIT 1)AS province,
			    	(SELECT v.village_namekh FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_namekh,
			    	(SELECT c.commune_namekh FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_namekh,
			    	(SELECT d.district_namekh FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_namekh,
			    	(SELECT rms_province.province_kh_name from rms_province where rms_province.province_id = s.province_id LIMIT 1)AS province_kh_name
				FROM 
					`rms_group_detail_student` AS g,
					rms_student as s,
					`rms_group` AS gr
				WHERE 
					gr.id = g.group_id
					AND g.stu_id = s.stu_id
		   			AND `g`.`status` = 1 ";
			if (!empty($id)){
				$sql.=' AND g.group_id='.$id;
			}
			if($type == 0){
				$sql.=' and g.type=1 ';
			}  
			$order= ' ORDER BY s.stu_khname ASC,s.stu_enname ASC ';
		   	if(empty($search)){
		   		return $db->fetchAll($sql.$order);
		   	}
		   	if(!empty($search['txtsearch'])){
		   		$s_where = array();
		   		$s_search = addslashes(trim($search['txtsearch']));
			   		$s_where[] = " stu_enname LIKE '%{$s_search}%'";
			   		$s_where[] = " stu_khname LIKE '%{$s_search}%'";
		   			$s_where[] = " stu_code LIKE '%{$s_search}%'";
		   		$sql .=' AND ( '.implode(' OR ',$s_where).')';
		   	}
		   	if(!empty($search['branch_id'])){
		   		$sql.=' AND gr.branch_id = '.$search['branch_id'];
		   	}
		   	if(!empty($search['study_year'])){
		   		$sql.=' AND gr.academic_year = '.$search['study_year'];
		   	}
		   	if(!empty($search['group'])){
		   		$sql.=' AND gr.id = '.$search['group'];
		   	}
		   	if(!empty($search['study_type']) AND $search['study_type']==1){
		   		$sql.=' AND g.stop_type = 0';
		   	}
		   	if(!empty($search['study_type']) AND $search['study_type']!=1){
		   		$sql.=' AND g.stop_type != 0 ';
		   	}
		 return $db->fetchAll($sql.$order);
	}
	public function getGroupDetail($search){
	   	$db = $this->getAdapter();
	   	$sql = 'SELECT
				   	`g`.`id`,
				   	(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) AS branch_name,
				   	`g`.`group_code`    AS `group_code`,
				   	(SELECT CONCAT(from_academic," - ",to_academic,"(",generation,")") FROM rms_tuitionfee WHERE rms_tuitionfee.id=g.academic_year LIMIT 1) AS academic,
				   	`g`.`semester` AS `semester`,
				   	(SELECT rms_items.title FROM `rms_items`	WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1)  LIMIT 1) as degree,
				   	(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as grade,
				   	
		
				   	(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`)) LIMIT 1) AS `session`,
				   	(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)LIMIT 1) AS `room_name`,
				   	 g.amount_month,
				   	`g`.`start_date`,
				   	`g`.`expired_date`,
				   	`g`.`note`,
				   	(SELECT `rms_view`.`name_kh` FROM `rms_view` WHERE `rms_view`.`type` = 9 AND `rms_view`.`key_code` = `g`.`is_pass` LIMIT 1) AS `status`,
				   	(SELECT COUNT(DISTINCT  sg.`stu_id`) FROM `rms_group_detail_student` AS sg,rms_student AS s  
	   					WHERE sg.`group_id`=`g`.`id` AND s.stu_id =sg.`stu_id` AND s.status=1 AND type=1 LIMIT 1) AS Num_Student,
	   				(SELECT COUNT(DISTINCT  sg.`stu_id`) FROM `rms_group_detail_student` AS sg,rms_student as s 
	   					WHERE sg.`group_id`=`g`.`id` AND s.stu_id =sg.`stu_id` AND s.is_subspend!=0 AND type=1 LIMIT 1) AS student_drop
				FROM 
	   				`rms_group` `g`
	   			WHERE 
	   				 group_code != "" ';
	   	
	   	$where=" ";
	   	
	   	if(!empty($search['title'])){
	   		$s_where = array();
	   		$s_search = addslashes(trim($search['title']));
	   		$s_where[] = " `g`.`group_code` LIKE '%{$s_search}%'";
	   		$s_where[] = " 	`g`.`semester` LIKE '%{$s_search}%'";
	   		$s_where[] = "  (SELECT	name_en FROM rms_view WHERE rms_view.type = 4 AND rms_view.key_code = g.session LIMIT 1) LIKE '%{$s_search}%'";
	   		$s_where[] = "  (SELECT	name_en FROM rms_view WHERE rms_view.type = 9 AND rms_view.key_code = g.is_pass LIMIT 1) LIKE '%{$s_search}%'";
	   		$sql .=' AND ( '.implode(' OR ',$s_where).')';
	   	}
	   	if(!empty($search['branch_id'])){
	   		$where.=' AND g.branch_id='.$search['branch_id'];
	   	}
	   	if(!empty($search['study_year'])){
	   		$where.=' AND g.academic_year='.$search['study_year'];
	   	}
	   	if(!empty($search['teacher'])){
	   		$where.=' AND g.teacher_id='.$search['teacher'];
	   	}
	   	if(!empty($search['grade'])){
	   		$where.=' AND g.grade='.$search['grade'];
	   	}
	   	if($search['room']>0){
	   		$where.=' AND `g`.`room_id`='.$search['room'];
	   	}
	   	if($search['degree']>0){
	   		$where.=' AND `g`.`degree`='.$search['degree'];
	   	}
	   	if(!empty($search['session'])){
	   		$where.=' AND g.session='.$search['session'];
	   	}
	   	if(!empty($search['group'])){
	   		$where.=' AND g.id='.$search['group'];
	   	}
	   	if($search['study_status']>=0){
	   		$where.=' AND g.is_pass='.$search['study_status'];
	   	}
	   	
	   	$order = ' ORDER BY `g`.`is_pass` ASC ,`g`.`group_code` ASC ';
	   	return $db->fetchAll($sql.$where.$order);
	}
   
	public function getGroupDetailByID($id){
	   	$db = $this->getAdapter();
	   	$sql = 'SELECT
				   	`g`.`id`,
				   	`g`.`group_code`    AS `group_code`,
				   	(SELECT CONCAT(from_academic," - ",to_academic,"(",generation,")") FROM rms_tuitionfee WHERE rms_tuitionfee.id=g.academic_year LIMIT 1) AS academic,
				   	`g`.`semester` AS `semester`,
				   	(SELECT rms_items.title FROM `rms_items`	WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1)  LIMIT 1) as degree,
				   	(SELECT rms_itemsdetail.title FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as grade,
				   	
				   	
				   	(SELECT	`rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`)) LIMIT 1) AS `session`,
				   	(SELECT `r`.`room_name` FROM `rms_room` `r` WHERE (`r`.`room_id` = `g`.`room_id`)) AS `room_name`,
				   	`g`.`start_date`,
				   	`g`.`expired_date`,
				   	`g`.`note`,
				   	(SELECT `rms_view`.`name_en` FROM `rms_view` WHERE ((`rms_view`.`type` = 1) AND (`rms_view`.`key_code` = `g`.`status`)) LIMIT 1) AS `status`,
				   	(SELECT COUNT(`stu_id`) FROM `rms_group_detail_student` WHERE `group_id`=`g`.`id`)AS Num_Student
			   	FROM 
		   			`rms_group` `g` 
		   		WHERE 
		   			`g`.`id`='.$id;
	   	return $db->fetchRow($sql);
	}
	
	function getAllTeacherByGroup($group_id){
		$db = $this->getAdapter();
		$sql=" SELECT 
					t.id,t.`teacher_name_kh` AS name
				FROM
					rms_group_subject_detail AS gsd,
					rms_teacher AS t
				WHERE 
					gsd.teacher = t.id
					AND t.teacher_name_kh!=''
					AND gsd.group_id =  $group_id	
		
			";
		return $db->fetchAll($sql);
	}
	
	function getAllSubjectByGroup($group_id){
		$db = $this->getAdapter();
		$sql=" SELECT
					s.id,
					CONCAT(s.`subject_titlekh`,'-',s.`subject_titleen`) AS name
				FROM
					rms_group_subject_detail AS gsd,
					rms_subject AS s
				WHERE
					gsd.subject_id = s.id
					AND gsd.group_id =  $group_id
			";
		return $db->fetchAll($sql);
	}
	function UpdateAmountStudent($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$ids = explode(",", $data['identity']);
			$iddetail ="";
			if (!empty($ids)) foreach ($ids as $id){
				if (empty($iddetail)){
					if (!empty($data['stu_id'.$id])){
						$iddetail=$data['stu_id'.$id];
					}
				}else{
					if (!empty($data['stu_id'.$id])){
						$iddetail=$iddetail.",".$data['stu_id'.$id];
					}
				}
			}
			$this->_name="rms_group_detail_student";
			$where1=" group_id=".$data['group_id'];
			if (!empty($iddetail)){
				$where1.=" AND gd_id NOT IN (".$iddetail.")";
			}
			$this->delete($where1);
			$db->commit();
		}catch(exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	
	
       
}