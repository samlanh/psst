<?php

class Accounting_Model_DbTable_DbUpdateStudenFee extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rms_group_detail_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	
	}
	function getAllTuitionFee($search=null){
		$db=$this->getAdapter();
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$lang = $dbp->currentlang();
		
		$branch= $dbp->getBranchDisplay();
		$field = 'name_en';
		$str = 'title_eng';
		if ($lang==1){
			$field = 'name_kh';
			$str = 'title_kh';
		}
		 
		$sql = "SELECT t.id,
					(SELECT CONCAT($branch) from rms_branch WHERE br_id =t.branch_id LIMIT 1) AS branch,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=t.academic_year LIMIT 1) as academic,
					(SELECT $str FROM rms_studytype WHERE rms_studytype.id =t.term_study  LIMIT 1) AS study_type,
					CASE is_multi_study
					WHEN 1 THEN '".$tr->translate("MULTY_PROGRAM")."'
					WHEN 0 THEN '".$tr->translate("ONE_PROGRAM_ONLY")."'
					END is_multistudy,
					t.generation,
					(SELECT count(ds.stu_id) FROM rms_group_detail_student AS ds WHERE ds.feeId = t.id AND ds.itemType=1 AND ds.is_current=1  LIMIT 1 ) AS amountStudent,
					(SELECT COUNT(ds.stu_id) FROM rms_group_detail_student AS ds WHERE ds.feeId = t.id AND ds.itemType=1 AND ds.is_current=1 AND ds.stop_type=0  LIMIT 1 ) AS amountUsed,
					(SELECT COUNT(ds.stu_id) FROM rms_group_detail_student AS ds WHERE ds.feeId = t.id AND ds.itemType=1 AND ds.is_current=1 AND ds.stop_type>0  LIMIT 1 ) AS amountStop,
					(SELECT title FROM `rms_schooloption` WHERE rms_schooloption.id=t.school_option LIMIT 1) as school_option,
					t.create_date,
					(SELECT $field from rms_view where type=12 and key_code=t.is_finished) as is_finished,
					(SELECT CONCAT(first_name) from rms_users where rms_users.id = t.user_id) as user ";
		$sql.=$dbp->caseStatusShowImage("t.status");
		$sql.=" FROM `rms_tuitionfee` AS t
				WHERE t.type=1	";
		 
		$where =" ";
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " t.generation LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		$where.=" AND t.is_finished=0 ";
		
		if(!empty($search['academic_year'])){
				$where.=" AND t.id=".$search['academic_year'];
		}
		if(!empty($search['branch_id'])){
			$where.=" AND t.branch_id=".$search['branch_id'];
		}
		if($search['type_study']>0){
			$where.=" AND t.term_study=".$search['type_study'];
		}
		if($search['school_option']>0){
			$where.=" AND t.school_option=".$search['school_option'];
		}
		if($search['status']>-1){
		$where.=" AND t.status=".$search['status'];
		}
		 
		$where.=$dbp->getAccessPermission();
		$order=" GROUP BY t.branch_id,t.academic_year,t.term_study,generation ORDER BY t.id DESC ";
		 
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function addStudentFee($_data){
		$db = $this->getAdapter();
		try{
			if(!empty($_data['academic_year_fee'])){
		
				$feeId = empty($_data['academic_year_fee'])?0:$_data['academic_year_fee'];

				if(!empty($_data['identity'])){
					$ids=explode(',', $_data['identity']);
					foreach ($ids as $k){
						if($_data['type']==1){

							$this->_name = 'rms_group_detail_student';
							$data_gro = array(
									'is_current'=> 1,
									'feeId'=> $feeId,
							);
							$where = 'itemType=1 AND stu_id = '.$_data['stu_id_'.$k]."  AND is_current=1 
								AND COALESCE(feeId,0) =0 AND COALESCE(oldFeeId,0) = 0 ";
							$this->update($data_gro, $where);
							
						}else{
							if(!empty($_data['OldFee'])){
								$this->_name = 'rms_group_detail_student';
								$data_gro = array(
										'is_current'=> 1,
										'feeId'=> $feeId,
								);
								$where = 'itemType=1 AND stu_id = '.$_data['stu_id_'.$k]."  AND is_current=1 
									AND CASE 
									WHEN COALESCE(feeId,0) > 0 THEN feeId
									ELSE oldFeeId END = ".$_data['OldFee'];
							
								$this->update($data_gro, $where);
							}
						}
					
					}
				}
			}
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			echo $e->getMessage(); exit();
		}
	}
	
	function getSearchStudentbyFeeId($search){
		$db=$this->getAdapter();
		$sql="SELECT 
				s.stu_id,
				s.stu_code,
				s.stu_enname,
				s.stu_khname,
				s.last_name,
				CASE
				    WHEN s.sex =1  THEN 'M'
				    WHEN s.sex =2  THEN 'F'
				    ELSE ''
				END AS sex,
				sd.degree,
				sd.grade,
				sd.feeId AS fee_id,
				sd.academic_year,
				(SELECT `title` FROM `rms_items` WHERE `id`=sd.degree AND TYPE=1 LIMIT 1) AS degree_title,
				(SELECT CONCAT(`title`) FROM `rms_itemsdetail` WHERE `id`=sd.grade AND items_type=1 LIMIT 1) AS grade_title,
				COALESCE((SELECT `group_code` FROM `rms_group` WHERE `id`=sd.group_id  LIMIT 1),'') AS groupCode,
				COALESCE((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=s.academicYearEnroll LIMIT 1),'') AS startYear,
				COALESCE((SELECT name_kh FROM `rms_view` WHERE key_code= s.studentType AND TYPE=40  LIMIT 1),'') AS studentType
			  FROM 
			  	rms_student AS s,
			  	`rms_group_detail_student` AS sd
		 	  WHERE 
				sd.itemType=1 AND
				s.stu_id = sd.stu_id
				AND s.`status`=1 
				AND s.customer_type = 1 
				AND sd.stop_type=0
				AND s.stu_id=sd.stu_id
				AND sd.is_current=1 ";
		
		if(!empty($search['schoolFeeOption'])){
			if($search['schoolFeeOption']==1){
				$sql.=" AND COALESCE(sd.feeId,0) =0 AND COALESCE(sd.oldFeeId,0) = 0  ";
			}else{
				if(!empty($search['fromFeeid'])){
					$sql.=" AND CASE 
							WHEN COALESCE(sd.feeId,0) > 0 THEN sd.feeId
							ELSE sd.oldFeeId END = ".$search['fromFeeid'];
				}
			}
		}
	
		if(!empty($search['branch_id'])){
			$sql.=" AND s.branch_id =".$search['branch_id'];
		}
		if(!empty($search['academic_year'])){
			$sql.=" AND sd.academic_year =".$search['academic_year'];
		}
		if(!empty($search['degree'])){
			$sql.=" AND sd.degree =".$search['degree'];
		}
		if(!empty($search['grade'])){
			$sql.=" AND sd.grade =".$search['grade'];
		}
		if(!empty($search['academicYearEnroll'])){
			$sql.=" AND s.academicYearEnroll =".$search['academicYearEnroll'];
		}
		if(!empty($search['studentType'])){
			$sql.=" AND s.studentType =".$search['studentType'];
		}
		$where="";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]=" REPLACE(s.stu_code,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.stu_khname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.stu_enname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.last_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" CONCAT(s.last_name,s.stu_enname) LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.tel,' ','')  			LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		
		$where.=" GROUP BY s.stu_id,sd.degree,sd.grade";
		$where.=" ORDER BY sd.degree,sd.grade,sd.group_id ASC, s.stu_id DESC ";
		
		if(!empty($search['limit'])){
			$where.=" LIMIT ".$search['limit'];
		}
		return $db->fetchAll($sql.$where);
	}
}