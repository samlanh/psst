<?php
class Issue_Model_DbTable_DbScheduleSetting extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_schedulesetting';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllScheuleSetting($search = ''){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql = " SELECT 
				s.id,
				(SELECT CONCAT(branch_nameen) FROM rms_branch WHERE br_id=s.branch_id LIMIT 1) AS branch_name,
				s.title,s.note,s.create_date
    		";
    	$sql.=$dbp->caseStatusShowImage("s.status");
    	$sql.=" FROM `rms_schedulesetting` AS s
				WHERE 1 ";
    	$orderby = " ORDER BY s.id DESC";
    	$where = ' ';
    	$from_date =(empty($search['start_date']))? '1': "s.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "s.create_date <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['advance_search'])){
   			 $s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
    					$s_where[] = " s.title LIKE '%{$s_search}%'";
    					$s_where[] = " s.note LIKE '%{$s_search}%'";
    					$sql .=' AND ( '.implode(' OR ',$s_where).')';
   	 	}
   	 	if(!empty($search['branch_search'])){
   	 		$where.= " AND s.branch_id = ".$db->quote($search['branch_search']);
   	 	}
    	if($search['status_search']>-1){
    		$where.= " AND s.status = ".$db->quote($search['status_search']);
    	}
    	$where.=$dbp->getAccessPermission('s.branch_id');
    	return $db->fetchAll($sql.$where.$orderby);
    }
	public function addScheduleSetting($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$_arr = array(
					'branch_id'=>$_data['branch_id'],
					'title'=>$_data['title'],
					'note'=>$_data['note'],
					'status'=>1,
					'create_date'=>date("Y-m-d H:i:s"),
					'modify_date'=>date("Y-m-d H:i:s"),
					'user_id'=>$this->getUserId(),
			);
			$this->_name='rms_schedulesetting';
			$id = $this->insert($_arr);
			
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				if(!empty($ids))foreach ($ids as $i){
					$arr=array(
							'setting_id'=>$id,
							'from_hour'=>$_data['from_hour'.$i],
							'to_hour'=>$_data['to_hour'.$i],
							'note'=>$_data['note_'.$i],
					);
					$this->_name='rms_schedulesetting_detail';
					$this->insert($arr);
				}
			}
		  $db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
   }
   
   function getScheduleSettingById($id){
   		$db = $this->getAdapter();
   		$sql="SELECT s.* FROM `rms_schedulesetting` AS s WHERE s.id=$id";
   		
   		$dbp = new Application_Model_DbTable_DbGlobal();
   		$sql.=$dbp->getAccessPermission('s.branch_id');
   		return $db->fetchRow($sql);
   }
   function getScheduleSettingDetail($id){
   	$db = $this->getAdapter();
   	$sql="SELECT s.* FROM `rms_schedulesetting_detail` AS s WHERE s.setting_id=$id";
   	return $db->fetchAll($sql);
   }
   public function editScheduleSetting($_data){
   	$db = $this->getAdapter();
   	$db->beginTransaction();
   	try{
   		$_arr = array(
   				'branch_id'=>$_data['branch_id'],
   				'title'=>$_data['title'],
   				'note'=>$_data['note'],
   				'status'=>$_data['status'],
   				'modify_date'=>date("Y-m-d H:i:s"),
   				'user_id'=>$this->getUserId(),
   		);
   		$this->_name='rms_schedulesetting';
   		$where=' id='.$_data['id'];
   		$this->update($_arr, $where);
   		$id = $_data['id'];
   		
   		$detailId="";
   		$ids = explode(",", $_data['identity']);
   		if (!empty($_data['identity'])){
   			foreach ($ids as $k){
   				if (empty($detailId)){
   					if (!empty($_data['detailid'.$k])){
   						$detailId = $_data['detailid'.$k];
   					}
   				}else{
   					if (!empty($_data['detailid'.$k])){
   						$detailId= $detailId.",".$_data['detailid'.$k];
   					}
   				}
   			}
   		}
   		$this->_name="rms_schedulesetting_detail";
   		$where="setting_id = ".$id;
   		if (!empty($detailId)){
   			$where.=" AND id NOT IN ($detailId) ";
   		}
   		$this->delete($where);
   		
   		if(!empty($_data['identity'])){
   			$ids = explode(',', $_data['identity']);
   			if(!empty($ids))foreach ($ids as $i){
   				if (!empty($_data['detailid'.$i])){
   					$arr=array(
   							'setting_id'=>$id,
   							'from_hour'=>$_data['from_hour'.$i],
   							'to_hour'=>$_data['to_hour'.$i],
   							'note'=>$_data['note_'.$i],
   					);
   					$this->_name='rms_schedulesetting_detail';
   					$where = " id =".$_data['detailid'.$i];
					$this->update($arr, $where);
   				}else{
	   				$arr=array(
	   						'setting_id'=>$id,
	   						'from_hour'=>$_data['from_hour'.$i],
	   						'to_hour'=>$_data['to_hour'.$i],
	   						'note'=>$_data['note_'.$i],
	   				);
	   				$this->_name='rms_schedulesetting_detail';
	   				$this->insert($arr);
   				}
   			}
   		}
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
   	
   	$_db = new Accounting_Model_DbTable_DbFee();
   	$row = $_db->getFeeById($academic_year);
   	$schoolOption = empty($row['school_option'])?null:$row['school_option'];
   	
   	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$model = new Application_Model_DbTable_DbGlobal();
   	$allDay = $model->getAllDay(1);
   	$allSubject = $model->getAllSubjectName($schoolOption);
   	$allTeacher = $model->getAllTeahcerName($branch_id);
   	 
   	 
   	$scheduleSetting = $this->getScheduleSettingDetail($setting_id);
   	 
   	$str='
	   	<thead  id="head-title">
		   	<tr class="head-td">
		   		<th >'.$tr->translate("TIME").'</th>';
			   	if (!empty($allDay)) foreach ($allDay as $day){
			   		$str.='<th >'. $day['name'].'</th>';
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
   		<tr class="record_schedule '.$classrow.'" id="row_'.($index).'">
	   		<td align="center" class="nowrap">
		   		<span>
			   		<i class="fa fa-clock-o" aria-hidden="true"></i> '.$rs['from_hour']." - ".$rs['to_hour'].'
			   		<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="settingdetail_'.$index.'" name="settingdetail_'.$index.'" value="'.$rs['id'].'" />
		   			<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="from_hour_'.$index.'" name="from_hour_'.$index.'" value="'.$rs['from_hour'].'" />
		   			<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="to_hour_'.$index.'" name="to_hour_'.$index.'" value="'.$rs['to_hour'].'" />
		   		
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
   			$str.='<td align="center">
			   			<div class="dayColunm">';
			   			$str.='
				   			<select onChange="disableColume('."'".$keyindex."'".')" dojoType="dijit.form.FilteringSelect" class="fullside" name="type_'.$keyindex.'" id="type_'.$keyindex.'" >
					   			<option value="1">'.$tr->translate("STUDY").'</option>
					   			<option value="2" '.$selected.'>'.$tr->translate("NO_STUDY").'</option>
				   			</select>
			   			';
			   				
			   			$str.='
			   			<select '.$readOnly.' dojoType="dijit.form.FilteringSelect" class="fullside" name="subject_'.$keyindex.'" id="subject_'.$keyindex.'" >';
			   			if (!empty($allSubject)) foreach ($allSubject as $subject){
			   				$str.='<option value="'.$subject['id'].'">'.$subject['name'].'</option>';
			   			}
			   			$str.='</select>
			   			';
			   				
		   			$str.='
		   				<select '.$readOnly.' dojoType="dijit.form.FilteringSelect" class="fullside" name="teacher_'.$keyindex.'" id="teacher_'.$keyindex.'" >';
		   			if (!empty($allTeacher)) foreach ($allTeacher as $teacher){
		   				$str.='<option value="'.$teacher['id'].'">'.$teacher['name'].'</option>';
		   			}
		   			$str.='</select>
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
}