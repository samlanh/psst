<?php

class Foundation_Model_DbTable_DbStudentBalance extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rms_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	
	}
	public function getAllStudentBalance($search){
		$_db = $this->getAdapter();
	
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label="name_en";
		$branch = "branch_nameen";
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
			$branch = "branch_namekh";
		}
	
		$from_date =(empty($search['start_date']))? '1': "sb.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "sb.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql = "
			SELECT 
				sb.id,
				(SELECT $branch FROM rms_branch WHERE br_id=sb.branch_id LIMIT 1) AS branch_name,
				s.stu_code,
				s.stu_khname,
				CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stu_name,
				(SELECT $label FROM `rms_view` WHERE type=2 AND key_code = s.sex LIMIT 1) AS sex,
				
				(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=sb.academic_year LIMIT 1) AS academicTitle,
				(SELECT group_code FROM `rms_group` WHERE rms_group.id=sb.group_id LIMIT 1) AS group_name,
				(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.type=1 AND i.id = sb.`degree` LIMIT 1) AS degreeTitle,
				(SELECT id.$colunmname FROM `rms_itemsdetail` AS id WHERE id.id = sb.`grade` LIMIT 1) AS gradeTitle,
				CASE
					WHEN sb.is_balance = 1 THEN '".$tr->translate("BALANCES")."'
					ELSE '".$tr->translate("UNBALANCES")."'
				END as is_balance
					
			";
		$sql.=$dbp->caseStatusShowImage("sb.status");
		$sql.="
				FROM
					`rms_student_balance` AS sb ,
					`rms_student` AS s
				WHERE 
					s.stu_id = sb.stu_id 
			";
		$orderby = " ORDER BY sb.id DESC ";
	
			if(!empty($search['adv_search'])){
					$s_where = array();
					$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
					$s_where[]=" REPLACE(s.stu_code,' ','')   	LIKE '%{$s_search}%'";
					$s_where[]=" REPLACE(s.stu_khname,' ','')  	LIKE '%{$s_search}%'";
					$s_where[]=" REPLACE(s.stu_enname,' ','')  	LIKE '%{$s_search}%'";
					$s_where[]=" REPLACE(s.last_name,' ','')  	LIKE '%{$s_search}%'";
					$s_where[]=" REPLACE(CONCAT(s.last_name,s.stu_enname),' ','')  	LIKE '%{$s_search}%'";
					$s_where[]=" REPLACE(CONCAT(s.stu_enname,s.last_name),' ','')  	LIKE '%{$s_search}%'";
					$where .=' AND ( '.implode(' OR ',$s_where).')';
			}
			if(!empty($search['group'])){
				$where.=" AND sb.group_id =".$search['group'];
			}
			if(!empty($search['degree'])){
				$where.=" AND sb.degree =".$search['degree'];
			}
			if(!empty($search['grade_all'])){
				$where.=" AND sb.grade =".$search['grade_all'];
			}
			if(!empty($search['branch_id'])){
				$where.=" AND sb.branch_id=".$search['branch_id'];
			}
			if($search['status']>-1){
				$where.=" AND sb.status=".$search['status'];
			}
			$where.=$dbp->getAccessPermission('sb.branch_id');
			return $_db->fetchAll($sql.$where.$orderby);
		}
	public function getAllStudentNotYetPayment($search){
		$_db = $this->getAdapter();
	
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label="name_en";
		$branch = "branch_nameen";
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
			$branch = "branch_namekh";
		}
	
		$where = " ";
		$sql = "SELECT 
					s.stu_id,
					s.branch_id,
					(SELECT $branch FROM rms_branch WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
					s.stu_code,
					s.stu_khname,
					s.stu_enname,
					s.last_name,
					
					CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS stu_name,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=gds.academic_year LIMIT 1) AS academicTitle,
					(SELECT group_code FROM `rms_group` WHERE rms_group.id=gds.group_id LIMIT 1) AS group_name,
					(SELECT $label FROM `rms_view` WHERE type=2 AND key_code = s.sex LIMIT 1) AS sex,
					(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.type=1 AND i.id = gds.`degree` LIMIT 1) AS degreeTitle,
					(SELECT id.$colunmname FROM `rms_itemsdetail` AS id WHERE id.id = gds.`grade` LIMIT 1) AS gradeTitle,
					gds.gd_id,
					gds.degree,
					gds.grade,
					gds.group_id,
					gds.academic_year
				FROM `rms_student` AS s,
				    `rms_group_detail_student` AS gds
				WHERE gds.stu_id = s.stu_id
					AND gds.is_current=1 
					AND gds.is_maingrade=1 
					AND gds.is_setgroup=1 
					AND s.customer_type=1 
					AND s.status=1 
					AND gds.stop_type=0 
					AND (SELECT sb.group_detail_id FROM `rms_student_balance` AS sb WHERE gds.gd_id = sb.group_detail_id AND sb.status=1 LIMIT 1) IS NULL
				AND 
				(SELECT 
				sp.id
				FROM `rms_student_payment` AS sp,
				`rms_student_paymentdetail` AS spd
				WHERE 
				sp.id = spd.payment_id 
				AND spd.service_type =1 AND spd.itemdetail_id = gds.grade AND sp.student_id=s.stu_id ORDER BY sp.id DESC LIMIT 1) IS NULL
		 ";
	
	
		$orderby = " ORDER BY s.stu_id DESC ";

		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[]=" REPLACE(s.stu_code,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.stu_khname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.stu_enname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.last_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(CONCAT(s.last_name,s.stu_enname),' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(CONCAT(s.stu_enname,s.last_name),' ','')  	LIKE '%{$s_search}%'";
			
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['group'])){
			$where.=" AND gds.group_id =".$search['group'];
		}
		if(!empty($search['degree'])){
			$where.=" AND gds.degree =".$search['degree'];
		}
		if(!empty($search['grade_all'])){
			$where.=" AND gds.grade =".$search['grade_all'];
		}
		if(!empty($search['branch_id'])){
			$where.=" AND s.branch_id=".$search['branch_id'];
		}
		$where.=$dbp->getAccessPermission('s.branch_id');
	
		return $_db->fetchAll($sql.$where.$orderby);
	}
	
	function addStudentBalance($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			if (!empty($_data['identity'])){
				$idsss=explode(',', $_data['identity']);
				foreach ($idsss as $k){
					if (!empty($_data['stu_id_'.$k])){
						$arr=array(
									'branch_id'			=>$_data['branch_id_'.$k],
									'group_detail_id'	=>$_data['gd_id_'.$k],
									'stu_id'			=>$_data['stu_id_'.$k],
									'academic_year'		=>$_data['academic_year_'.$k],
									'group_id'			=>$_data['group_id_'.$k],
									'degree'			=>$_data['degree_'.$k],
									'grade'				=>$_data['grade_'.$k],
									'is_balance'		=>1,
									'status'			=>1,
									'user_id'			=>$this->getUserId(),
									'create_date'		=>date('Y-m-d H:i:s'),
									'modify_date'		=>date('Y-m-d H:i:s'),
							);
							$this->_name='rms_student_balance';
							$this->insert($arr);
					}
				}
			}
			return $_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
		
		}
	}
}