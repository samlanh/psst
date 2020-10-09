<?php
class Registrar_Model_DbTable_DbNewStudent extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	
	function getAllNewStudent($search=null){
		$db = $this->getAdapter();
		
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
		;
		$from_date =(empty($search['start_date']))? '1': " s.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
	
		$sql=" SELECT s.stu_id as id,
				(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gds.academic_year LIMIT 1) AS tuitionfee_id,
				(SELECT i.$colunmname FROM `rms_items` AS i WHERE i.type=1 AND i.id = `gds`.`degree` LIMIT 1) AS degree,
				(SELECT id.$colunmname FROM `rms_itemsdetail` AS id WHERE id.id = `gds`.`grade` LIMIT 1) AS grade,
				(CASE WHEN s.stu_khname IS NULL OR s.stu_khname='' THEN s.stu_enname ELSE s.stu_khname END) AS name,
				(SELECT $label FROM `rms_view` WHERE type=2 AND key_code = s.sex LIMIT 1) AS sex,
				s.tel,
				s.email,
				(SELECT first_name FROM rms_users WHERE s.user_id=rms_users.id LIMIT 1 ) AS user_name
				 ";
		$sql.=$dbp->caseStatusShowImage("s.status");
		$sql.="	FROM $this->_name AS s ,
				rms_group_detail_student as gds
			WHERE 
				s.stu_id = gds.stu_id
				AND gds.is_newstudent=1
				AND gds.group_id=0
				AND gds.is_current=1
				AND gds.is_maingrade=1 
				AND s.customer_type=1 
		";
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[]=" REPLACE(stu_code,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(stu_khname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(stu_enname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(last_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(CONCAT(last_name,stu_enname),' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(CONCAT(stu_enname,last_name),' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(tel,' ','') LIKE '%{$s_search}%'";
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND s.status = ".$search['status'];
		}
		$order=" ORDER BY s.stu_id DESC "; 
		return $db->fetchAll($sql.$where.$order);
	}
	function addNewStudent($_data){
		$_db = $this->getAdapter();
		$_db->beginTransaction();
		try{
			
			$dbg = new Application_Model_DbTable_DbGlobal();
			$_dbfee = new Accounting_Model_DbTable_DbFee();
			
				$_arr= array(
					'branch_id'		=>$_data['branch_id'],
					'user_id'		=>$this->getUserId(),
					
					'stu_khname'	=>$_data['stu_khname'],
					'last_name'		=>ucfirst($_data['last_name']),
					'stu_enname'	=>ucfirst($_data['name_en']),
					'sex'			=>$_data['sex'],
					
					'customer_type'	=>1,
					
					
					'tel'			=>$_data['phone'],
					'email'			=>$_data['email'],
					'home_num'		=>$_data['home_note'],
					'street_num'	=>$_data['way_note'],
					'village_name'	=>$_data['village_note'],
					'commune_name'	=>$_data['commun_note'],
					'district_name'	=>$_data['distric_note'],
					'province_id'	=>$_data['student_province'],
					
					
					//////////////////////////////////////////////				
					'remark'		=>$_data['remark'],
					
				);
				if(!empty($_data['id']) ){
// 					$_arr['stu_code']=$_data['student_id'];
					$_arr['status']=$_data['status'];
					$where=$this->getAdapter()->quoteInto("stu_id=?", $_data['id']);
					$this->update($_arr, $where);
					
					$feeID = empty($_data['academic_year'])?0:$_data['academic_year'];
					$rowfee = $_dbfee->getFeeById($feeID);
					$academicYear = empty($rowfee['academic_year'])?0:$rowfee['academic_year'];
						
					$_arrFeeHistory= array(
							'branch_id'		=>$_data['branch_id'],
							'user_id'		=>$this->getUserId(),
							'fee_id'		=>$_data['academic_year'],
							'academic_year'	=>$academicYear,
							'note'			=>$_data['remark'],
							'is_current'	=>1,
							'is_new'		=>1,
							'status'		=>1,
							'modify_date'	=>date("Y-m-d H:i:s"),
					);
					$this->_name="rms_student_fee_history";
					$where=$this->getAdapter()->quoteInto("id=?", $_data['feeHistoryId']);
					$this->update($_arrFeeHistory, $where);
					
					$degree_id = empty($_data['degree'])?0:$_data['degree'];
					$grade_id = empty($_data['grade'])?0:$_data['grade'];
					$_arrGroupDetail = array(
							'is_newstudent'		=>1,
							'status'			=>1,
							'group_id'			=>0,
							'academic_year'		=>$academicYear,
							'degree'			=>$degree_id,
							'grade'				=>$grade_id,
							'is_current'		=>1,
							'is_setgroup'		=>0,
							'is_maingrade'		=>1,
							'date'				=>date("Y-m-d"),
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
					);
					$this->_name="rms_group_detail_student";
					$whereGd=$this->getAdapter()->quoteInto("gd_id=?", $_data['groupDetailId']);
					$this->update($_arrGroupDetail, $whereGd);
					
				}else{
					
					
					$degree_id = empty($_data['degree'])?0:$_data['degree'];
					$grade_id = empty($_data['grade'])?0:$_data['grade'];
					$stu_code = $dbg->getnewStudentId($_data['branch_id'],$degree_id);
					
					$_arr['stu_code']=$stu_code;
					$_arr['status']=1;
					$_arr['create_date']=date("Y-m-d H:i:s");
					$id = $this->insert($_arr);
					
					
					
					$feeID = empty($_data['academic_year'])?0:$_data['academic_year'];
					$rowfee = $_dbfee->getFeeById($feeID);
					$academicYear = empty($rowfee['academic_year'])?0:$rowfee['academic_year'];
					
					$_arrFeeHistory= array(
							'branch_id'		=>$_data['branch_id'],
							'user_id'		=>$this->getUserId(),
							'student_id'	=>$id,
							'fee_id'		=>$_data['academic_year'],
							'academic_year'	=>$academicYear,
							'note'			=>$_data['remark'],
							'is_current'	=>1,
							'is_new'		=>1,
							'status'		=>1,
							'create_date'	=>date("Y-m-d H:i:s"),
							'modify_date'	=>date("Y-m-d H:i:s"),
					);
					$this->_name="rms_student_fee_history";
					$this->insert($_arrFeeHistory);
					
					
					$_arrGroupDetail = array(
							'stu_id'			=>$id,
							'is_newstudent'		=>1,
							'status'			=>1,
							'group_id'			=>0,
							'academic_year'		=>$academicYear,
							'degree'			=>$degree_id,
							'grade'				=>$grade_id,
							'is_current'		=>1,
							'is_setgroup'		=>0,
							'is_maingrade'		=>1,
							'date'				=>date("Y-m-d"),
							'create_date'		=>date("Y-m-d H:i:s"),
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
					);
					$this->_name="rms_group_detail_student";
					$this->insert($_arrGroupDetail);
				}
						
			$_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
			Application_Form_FrmMessage::message("INSERT_FAILE");
		}
	}
	
	public function getNewStudentById($id){
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		$sql = "SELECT s.*,
					(SELECT sh.id FROM rms_student_fee_history AS sh WHERE sh.student_id=s.stu_id AND sh.is_current=1 ORDER BY sh.id DESC LIMIT 1) as feeHistoryId,
					(SELECT sh.fee_id FROM rms_student_fee_history AS sh WHERE sh.student_id=s.stu_id AND sh.is_current=1 ORDER BY sh.id DESC LIMIT 1) as fee_id,
					(SELECT sh.academic_year FROM rms_student_fee_history AS sh WHERE sh.student_id=s.stu_id AND sh.is_current=1 ORDER BY sh.id DESC LIMIT 1) as academicYyear,
					gds.gd_id,
					gds.academic_year,
					gds.degree,
					gds.grade
				FROM rms_student as s,
				rms_group_detail_student as gds
				WHERE 
				s.stu_id = gds.stu_id
				AND gds.is_newstudent=1
				AND gds.group_id=0
				AND gds.is_current=1
				AND gds.is_maingrade=1
				AND	s.stu_id =".$id." 
				AND s.customer_type=1";
		return $db->fetchRow($sql);
	}

	

}