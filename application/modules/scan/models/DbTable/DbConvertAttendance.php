<?php

class Scan_Model_DbTable_DbConvertAttendance extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_student_attendence';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllScanAttendence($search=null){
    	$db=$this->getAdapter();
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
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql="SELECT g.`id`,
			    	(SELECT $branch FROM `rms_branch` WHERE rms_branch.br_id = g.branch_id LIMIT 1) AS branch_name,
			    	g.group_code AS group_name,
			    	(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) AS academic_id,
			    	(SELECT rms_items.$colunmname FROM `rms_items` WHERE (`rms_items`.`id`=`g`.`degree`) AND (`rms_items`.`type`=1) LIMIT 1)  AS degree,
			    	(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=`g`.`grade`) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1 ) AS grade,
			    	(SELECT `r`.`room_name`	FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS room,
			    	(SELECT`rms_view`.$label FROM `rms_view` WHERE ((`rms_view`.`type` = 4) AND (`rms_view`.`key_code` = `g`.`session`)) LIMIT 1) AS session,
			    	st.`create_date`,
			    	CASE
					   	WHEN  st.is_converted = 0 THEN '".$tr->translate("UNCONVERT")."'
					   	WHEN  st.is_converted = 1 THEN '".$tr->translate("CONVERTED")."'
			   	END AS is_converted ,
    				(SELECT first_name FROM rms_users WHERE st.conver_userid=rms_users.id LIMIT 1 ) AS user_name ";
    	//$sql.=$dbp->caseStatusShowImage("sa.`status`");
    	//(SELECT first_name FROM rms_users WHERE st.conver_userid=rms_users.id LIMIT 1 ) AS user_name
    	$sql.=" FROM 
    		`rms_scan_transaction` AS st,
    		rms_group as g ";
    	$where =' WHERE g.id=st.group_id ';
    	$from_date =(empty($search['start_date']))? '1': " st.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " st.create_date <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['branch_id'])){
    		$where.= " AND g.`branch_id` =".$search['branch_id'];
    	}
    	if(!empty($search['group'])){
    		$where.= " AND st.`group_id` =".$search['group'];
    	}
    	if(!empty($search['study_year'])){
    		$where.=" AND  g.academic_year =".$search['study_year'];
    	}
    	if(!empty($search['grade'])){
    		$where.=" AND g.grade =".$search['grade'];
    	}
    	if(!empty($search['session'])){
    		$where.=" AND `g`.`session` =".$search['session'];
    	}
   		if(!empty($search['room'])){
			$where.=" AND `g`.`room_id` =".$search['room'];
		}
    	$order=" GROUP BY g.id ,st.scan_type ORDER BY st.id DESC ";
    	$where.=$dbp->getAccessPermission('g.branch_id');
    	return $db->fetchAll($sql.$where.$order);
    }
    function getScanAttendencebyGroup($group_id){
    	$db = $this->getAdapter();
    	$sql="SELECT 
    		st.*,
    		DATE_FORMAT(st.create_date,'%d/%m/%Y') AS date_attendence,
    		g.id,
    		g.branch_id
    	FROM `rms_scan_transaction` AS st,
    		rms_group as g 
    	WHERE 
    	g.id=st.group_id
    	AND g.id=$group_id
    	 GROUP BY g.id ,st.scan_type LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
	public function ConvertScantoAttendece($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$branch = $_data['branch_id'];
			$group = $_data['group'];
			$date = $_data['attendence_date'];
			$for_semester = $_data['for_semester'];
			$session = $_data['session_type'];
			$sql="SELECT id FROM rms_student_attendence WHERE 
				branch_id = $branch and group_id = $group 
				and for_semester = $for_semester 
				and for_session = $session 
				and date_attendence = '$date' 
				and type=1 limit 1";
			$id = $db->fetchOne($sql);
			
			if(empty($id)){
				$_arr = array(
					'branch_id'		=>$_data['branch_id'],
					'group_id'		=>$_data['group'],
					'date_attendence'=>date("Y-m-d",strtotime($_data['attendence_date'])),
					'date_create'	=>date("Y-m-d"),
					'modify_date'	=>date("Y-m-d"),
					'subject_id'	=>$_data['subject'],
					'for_semester'	=> $_data['for_semester'],
					'note'			=>$_data['note'],
					'status'		=>1,
					'user_id'		=>$this->getUserId(),
					'for_session'	=>$_data['session_type'],
					'type'			=>1, //for attendence
				);
				$id=$this->insert($_arr);
				
// 				$dbpush = new Application_Model_DbTable_DbGlobal();
// 				$dbpush->pushNotification(null,$_data['group'],2,2);
				
			}
			$stu_come='';$stu_absent='';
			$dbpush = new Application_Model_DbTable_DbGlobal();
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				if(!empty($ids))foreach ($ids as $i){
					
					$arr = array(
						'attendence_id'	=>$id,
						'stu_id'		=>$_data['student_id'.$i],
						'attendence_status'=>$_data['attedence'.$i],
						'description'	=>$_data['comment'.$i],
						'tran_scanid'	=>$_data['transacan_id'.$i],
							
					);
					$this->_name ='rms_student_attendence_detail';
					$this->insert($arr);
					
					if(!empty($_data['transacan_id'.$i])){
						$this->_name ='rms_scan_transaction';
						$where = 'id='.$_data['transacan_id'.$i];
						$data = array(
								'is_converted'=>1,
								'conver_userid'=>$this->getUserId()
								);
						$this->update($data, $where);
					}
					
					
					if($_data['attedence'.$i]!=1){//ក្រៅពីមក sent all
						if(empty($stu_absent)){
							$stu_absent=$_data['student_id'.$i];
						}else{
							$stu_absent=$stu_absent.','.$_data['student_id'.$i];
						}
					}
					else{
						if(empty($stu_come)){
							$stu_come=$_data['student_id'.$i];
						}else{
							$stu_come=$stu_come.','.$_data['student_id'.$i];
						}
						
					}
				}
				
// 				$dbpush->pushNotification($stu_absent,null,3,2);//absent
// 				$dbpush->pushNotification($stu_come,null,3,2);//come
			}
		  $db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("INSERT_FAIL");
			$db->rollBack();
		}
   }

}