<?php
class Issuesetting_Model_DbTable_DbScoreEntrySetting extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_score_entry_setting';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllScoreEntrySetting($search = ''){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql = " SELECT s.id,
		(SELECT CONCAT(branch_nameen) FROM rms_branch WHERE br_id=s.branchId LIMIT 1) AS branch_name,
		s.title,s.description,s.fromDate,s.`endDate`, s.createDate ";
    	$sql.=$dbp->caseStatusShowImage("s.status");
    	$sql.=" FROM `rms_score_entry_setting` AS s WHERE 1 ";
    	$orderby = "  ORDER BY s.id DESC";
    	$where = ' ';
    	$from_date =(empty($search['start_date']))? '1': "s.createDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "s.createDate <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['advance_search'])){
   			 $s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
    					$s_where[] = " s.title LIKE '%{$s_search}%'";
    					$s_where[] = " s.description LIKE '%{$s_search}%'";
    					$sql .=' AND ( '.implode(' OR ',$s_where).')';
   	 	}
   	 	if(!empty($search['branch_search'])){
   	 		$where.= " AND s.branchId = ".$db->quote($search['branch_search']);
   	 	}
    	if($search['status_search']>-1){
    		$where.= " AND s.status = ".$db->quote($search['status_search']);
    	}
    	$where.=$dbp->getAccessPermission('s.branchId');
    	return $db->fetchAll($sql.$where.$orderby);
    }
	public function addScoreEntrySetting($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$dept = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($dept)){
	    			$dept = $rs;
	    		}else{ $dept = $dept.",".$rs;
	    		}
	    	}
			$_arr = array(
					'branchId'	 =>$_data['branch_id'],
					'degreeId'	 =>$dept,
					'title'		 =>$_data['title'],
					'description'=>$_data['description'],
					'fromDate'	 =>$_data['from_date'],
					'endDate'	 =>$_data['end_date'],
					'createDate' =>date("Y-m-d H:i:s"),
					'modifyDate' =>date("Y-m-d H:i:s"),
					'status'	 =>1,
			);
			$this->_name='rms_score_entry_setting';
			$this->insert($_arr);
		  $db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
   }
   
   function getScoreEntrySettingById($id){
   		$db = $this->getAdapter();
   		$sql="SELECT s.* FROM `rms_score_entry_setting` AS s WHERE s.id=$id";
   		
   		$dbp = new Application_Model_DbTable_DbGlobal();
   		$sql.=$dbp->getAccessPermission('s.branchId');
   		return $db->fetchRow($sql);
   }
 
   public function editScoreEntrySetting($_data){
   	$db = $this->getAdapter();
   	$db->beginTransaction();
   	try{
		$dept = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($dept)){
	    			$dept = $rs;
	    		}else{ $dept = $dept.",".$rs;
	    		}
	    	}
		$status=empty($_data['status'])?0:1;
   		$_arr = array(
			'branchId'	 =>$_data['branch_id'],
			'degreeId'	 =>$dept,
			'title'		 =>$_data['title'],
			'description'=>$_data['description'],
			'fromDate'	 =>$_data['from_date'],
			'endDate'	 =>$_data['end_date'],
			'createDate' =>date("Y-m-d H:i:s"),
			'modifyDate' =>date("Y-m-d H:i:s"),
			'status'	 =>$status,
   		);
   		$this->_name='rms_score_entry_setting';
   		$where=' id='.$_data['id'];
   		$this->update($_arr, $where);
   		$db->commit();
   	}catch (Exception $e){
   		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
   		$db->rollBack();
   	}
   }
   
   function getScheduleSettingByBranch($branch_id){
   		$db = $this->getAdapter();
   		$sql="SELECT st.id, st.title AS `name`  FROM rms_schedulesetting AS st WHERE st.status=1";
   		$sql.=" AND st.branch_id = $branch_id ";
   		return $db->fetchAll($sql);
   }
   
   function getScheduleDetialBySchedule($_data){
   	 
   	$setting_id = empty($_data['schedule_setting'])?0:$_data['schedule_setting'];
   	$branch_id = empty($_data['branch_id'])?0:$_data['branch_id'];
   	$academic_year = empty($_data['academic_year'])?0:$_data['academic_year'];
   	$index = empty($_data['keyrow'])?1:$_data['keyrow'];
   	$groupId = empty($_data['groupId'])?0:$_data['groupId'];
   	
   	$_db = new Accounting_Model_DbTable_DbFee();
   	//$row = $_db->getFeeById($academic_year);
   	$schoolOption = '1,2,3';//empty($row['school_option'])?null:$row['school_option'];
   	
   	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$model = new Application_Model_DbTable_DbGlobal();
   	$allDay = $model->getAllDay(1);
	$arraySubject = array(
			"schoolOption"=>$schoolOption,
			"groupId"=>$groupId,
		);
   	$allSubject = $model->getAllSubjectName($arraySubject);
	$arrayFilter = array(
		"branch_id"=>$branch_id,
		"groupId"=>$groupId,
	);
   	$allTeacher = $model->getAllTeahcerName($arrayFilter);
   	 
   	 
   	$scheduleSetting = $this->getScheduleSettingDetail($setting_id);
   	 
   	$str='
	   	<thead  id="head-title">
		   	<tr class="head-td" align="center">
		   		<th scope="col" >'.$tr->translate("TIME").'</th>';
			   	if (!empty($allDay)) foreach ($allDay as $day){
			   		$str.='<th scope="col" >'. $day['name'].'</th>';
			   	}
		   	$str.='</tr>
	   	</thead>
	   	';
   	$str.='<tbody id="table_row">';
   	$identity="";
   	if (!empty($scheduleSetting)) foreach ($scheduleSetting as $key => $rs) {
   		$index = $index+1;
   		if (empty($identity)){
   			$identity = $index;
   		}else{ $identity = $identity.",".$index;
   		}
   		$classrow="";
   		if (($key+1)%2==1){
   			$classrow = "odd";
   		}
   		$str.='
   		<tr class="rowData record_schedule '.$classrow.'" id="row_'.($index).'">
	   		<td data-label="'.$tr->translate("TIME").'" align="center" class="nowrap">
		   		<span>
			   		<i class="fa fa-clock-o" aria-hidden="true"></i> '.$rs['fromHourTitle']." - ".$rs['toHourTitle'].'
			   		<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="settingdetail_'.$index.'" name="settingdetail_'.$index.'" value="'.$rs['id'].'" />
		   			<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="from_hour_'.$index.'" name="from_hour_'.$index.'" value="'.$rs['from_hour'].'" />
		   			<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="to_hour_'.$index.'" name="to_hour_'.$index.'" value="'.$rs['to_hour'].'" />
		   			<div class="custom-control custom-checkbox ">
						<input type="checkbox" class="checkboxAttendance custom-control-input" onchange="CheckAll('.$index.','.count($allDay).');"  name="breaktime'.$index.'" id="breaktime'.$index.'">
						<label class="custom-control-label" for="breaktime'.$index.'" id="lblbreaktime'.$index.'">'.$tr->translate("NO_STUDY").'</label>
					</div>
		   		</span>
	   		</td>
   		';
   		if (!empty($allDay)) foreach ($allDay as $day){
   			$selected="";
   			$readOnly="";
   			if ($day['id']==7){
   				$selected = 'selected="selected"';
   				$readOnly = 'readOnly="readOnly"';
   			}
   			$keyindex = $index."_".$day['id'];
   			$str.='<td data-label="'.$day['name'].'" align="center">
			   			<div class="dayColunm">';
			   			$str.='
					<div class="form-group marginAdjust">
						<div class="col-md-12 col-sm-12 col-xs-12">
				   			<select onChange="disableColume('."'".$keyindex."'".')" dojoType="dijit.form.FilteringSelect" class="fullside" name="type_'.$keyindex.'" id="type_'.$keyindex.'" autoComplete="false" queryExpr="*${0}*">
					   			<option value="1">'.$tr->translate("STUDY").'</option>
					   			<option value="2" '.$selected.'>'.$tr->translate("NO_STUDY").'</option>
				   			</select>
						</div>
					</div>
			   		';
			   				
			   			$str.='
							<div class="form-group marginAdjust">
								<div class="col-md-12 col-sm-12 col-xs-12">
								<select '.$readOnly.' dojoType="dijit.form.FilteringSelect" class="fullside" name="subject_'.$keyindex.'" placeHolder="'.$tr->translate("SELECT_SUBJECT").'" id="subject_'.$keyindex.'" autoComplete="false" queryExpr="*${0}*">';
								$str.='<option value=""></option>';
								if (!empty($allSubject)) foreach ($allSubject as $subject){
									$str.='<option value="'.$subject['id'].'">'.$subject['name'].'</option>';
								}
								$str.='</select>
								</div>
							</div>
			   			';
			   				
		   			$str.='
					<div class="form-group marginAdjust">
						<div class="col-md-12 col-sm-12 col-xs-12">
							<select '.$readOnly.' dojoType="dijit.form.FilteringSelect" class="fullside" name="teacher_'.$keyindex.'" placeHolder="'.$tr->translate("SELECT_TEACHER").'" id="teacher_'.$keyindex.'" autoComplete="false" queryExpr="*${0}*">';
							$str.='<option value=""></option>';
							if (!empty($allTeacher)) foreach ($allTeacher as $teacher){
								$str.='<option value="'.$teacher['id'].'">'.$teacher['name'].'</option>';
							}
							$str.='</select>
						</div>
					</div>
		   			';
   			$str.='</div>
   			</td>';
   		}
   		$str.='</tr>';
   	}
   	$str.='</tbody>';
   
   	$arr = array(
   			'string' => $str,
   			'identity' => $identity,
   			'keyrow' =>$index,
   	);
   	return $arr;
   }
   function getScheduleDetialByScheduleEdit($_data){
   	 
   	$setting_id = empty($_data['schedule_setting'])?0:$_data['schedule_setting'];
   	$branch_id = empty($_data['branch_id'])?0:$_data['branch_id'];
   	$academic_year = empty($_data['academic_year'])?0:$_data['academic_year'];
   	$index = empty($_data['keyrow'])?1:$_data['keyrow'];
   	$mainId = empty($_data['id'])?1:$_data['id'];
	$groupId = empty($_data['groupId'])?0:$_data['groupId'];
   
   	$_db = new Accounting_Model_DbTable_DbFee();
   	$schoolOption = '1,2,3';
   
   	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$model = new Application_Model_DbTable_DbGlobal();
   	$allDay = $model->getAllDay(1);
	$arraySubject = array(
			"schoolOption"=>$schoolOption,
			"groupId"=>$groupId,
		);
   	$allSubject = $model->getAllSubjectName($arraySubject);
	$arrayFilter = array(
				"branch_id"=>$branch_id,
				"groupId"=>$groupId,
			);
   	$allTeacher = $model->getAllTeahcerName($arrayFilter);
   	 
   	 
   	$scheduleSetting = $this->getScheduleSettingDetail($setting_id);
   	 
   	$str='
   	<thead  id="head-title">
	<tr class="head-td" align="center">
   		<th scope="col" >'.$tr->translate("TIME").'</th>';
   	if (!empty($allDay)) foreach ($allDay as $day){
   		$str.='<th scope="col" >'. $day['name'].'</th>';
   	}
   	$str.='</tr>
   	</thead>
   	';
   	$str.='<tbody id="table_row">';
   	$identity="";
   	if (!empty($scheduleSetting)) foreach ($scheduleSetting as $key => $rs) {
   		$index = $index+1;
   		if (empty($identity)){
   			$identity = $index;
   		}else{ $identity = $identity.",".$index;
   		}
   		$classrow="";
   		if (($key+1)%2==1){
   			$classrow = "odd";
   		}
   		$str.='
   		<tr class="rowData record_schedule '.$classrow.'" id="row_'.($index).'">
   		<td data-label="'.$tr->translate("TIME").'" align="center" class="nowrap">
	   		<span>
		   		<i class="fa fa-clock-o" aria-hidden="true"></i> '.$rs['fromHourTitle']." - ".$rs['toHourTitle'].'
		   		<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="settingdetail_'.$index.'" name="settingdetail_'.$index.'" value="'.$rs['id'].'" />
		   		<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="from_hour_'.$index.'" name="from_hour_'.$index.'" value="'.$rs['from_hour'].'" />
		   		<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="to_hour_'.$index.'" name="to_hour_'.$index.'" value="'.$rs['to_hour'].'" />
	   		 
	   		</span>
   		</td>
   		';
   		if (!empty($allDay)) foreach ($allDay as $day){
   			$selected="";
   			$readOnly="";
   			
   			$scheduleDay = $this->getScheduleDetail($mainId, $rs['id'], $day['id']);
   			$subjectSchedule="";
   			$teacherSchedule="";
   			$idScheduleDetail="";
   			if (!empty($scheduleDay)){
   				if ($scheduleDay['study_type']==2){
   					$selected = 'selected="selected"';
   					$readOnly = 'readOnly="readOnly"';
   				}
   				$subjectSchedule=$scheduleDay['subject_id'];
   				$teacherSchedule=$scheduleDay['techer_id'];
   				$idScheduleDetail=$scheduleDay['id'];
   			}else{
	   			//if ($day['id']==7){
	   				$selected = 'selected="selected"';
	   				$readOnly = 'readOnly="readOnly"';
	   			//}
   			}
   			$keyindex = $index."_".$day['id'];
   			$str.='<td data-label="'.$day['name'].'" align="center">
			   			<div class="dayColunm">';
			   			$str.='
						<div class="form-group marginAdjust">
							 <div class="col-md-12 col-sm-12 col-xs-12">
								<select onChange="disableColume('."'".$keyindex."'".')" dojoType="dijit.form.FilteringSelect" class="fullside" name="type_'.$keyindex.'" id="type_'.$keyindex.'" autoComplete="false" queryExpr="*${0}*">
									<option value="1">'.$tr->translate("STUDY").'</option>
									<option value="2" '.$selected.'>'.$tr->translate("NO_STUDY").'</option>
								</select>
								<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="schedule_detail'.$keyindex.'" name="schedule_detail'.$keyindex.'" value="'.$idScheduleDetail.'" />
							</div>
						</div>
			   			';
   
   			$str.='
			<div class="form-group marginAdjust">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<select '.$readOnly.' dojoType="dijit.form.FilteringSelect" class="fullside" placeHolder="'.$tr->translate("SELECT_SUBJECT").'" name="subject_'.$keyindex.'" id="subject_'.$keyindex.'" autoComplete="false" queryExpr="*${0}*">';
						$str.='<option value=""></option>';
						if (!empty($allSubject)) foreach ($allSubject as $subject){
							$selected="";
							if($subjectSchedule==$subject['id']){ $selected = 'selected="selected"'; }
							$str.='<option '.$selected.' value="'.$subject['id'].'">'.$subject['name'].'</option>';
						}
				$str.='</select>
				</div>
			</div>
   			';
   
   			$str.='
			<div class="form-group marginAdjust">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<select '.$readOnly.' dojoType="dijit.form.FilteringSelect" class="fullside" placeHolder="'.$tr->translate("SELECT_TEACHER").'" name="teacher_'.$keyindex.'" id="teacher_'.$keyindex.'" autoComplete="false" queryExpr="*${0}*">';
					$str.='<option value=""></option>';
					if (!empty($allTeacher)) foreach ($allTeacher as $teacher){
						$selected="";
						if($teacherSchedule==$teacher['id']){ $selected = 'selected="selected"';}
						$str.='<option '.$selected.' value="'.$teacher['id'].'">'.$teacher['name'].'</option>';
					}
					$str.='</select>
				</div>
			</div>
   			';
   			$str.='</div>
   			</td>';
   		}
   		$str.='</tr>';
   	}
   	$str.='</tbody>';
   	 
   	$arr = array(
   			'string' => $str,
   			'identity' => $identity,
   			'keyrow' =>$index,
   	);
   	return $arr;
   }
   
   function getScheduleDetail($main_schedule_id,$schedule_setting_id,$day_id){
   	$db = $this->getAdapter();
   	$sql="SELECT d.*
		FROM `rms_group_reschedule` AS d 
		WHERE d.main_schedule_id=$main_schedule_id
		AND d.day_id =$day_id
		AND d.schedule_setting_id =$schedule_setting_id";
   	return $db->fetchRow($sql);
   }
   
   
   function getCheckScheduleSettingInSchedule($id){
   	$db = $this->getAdapter();
   	$sql="SELECT s.* FROM `rms_group_schedule` AS s WHERE s.schedule_setting=$id LIMIT 1";
   	return $db->fetchRow($sql);
   }
}