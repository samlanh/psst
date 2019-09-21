<?php

class Issue_Model_DbTable_DbSchedule extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_group';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllScheduleGroup($search){
    	$db = $this->getAdapter();
    	try{
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		
    		$sql="
    		SELECT gsch.id,
			(SELECT branch_nameen FROM `rms_branch` WHERE br_id=gsch.branch_id LIMIT 1) AS branch_name,	
			(SELECT CONCAT(rms_tuitionfee.from_academic,'-',rms_tuitionfee.to_academic,'(',rms_tuitionfee.generation,')') 
			 FROM rms_tuitionfee WHERE rms_tuitionfee.status=1 AND rms_tuitionfee.is_finished=0 AND rms_tuitionfee.id=gsch.academic_year LIMIT 1) AS years,
			 (SELECT group_code FROM rms_group WHERE rms_group.id= gsch.group_id LIMIT 1) AS group_code,
			 (SELECT st.title FROM `rms_schedulesetting` AS st WHERE st.id = gsch.schedule_setting LIMIT 1) AS sch_setting,
			 DATE_FORMAT(gsch.create_date,'%d-%m-%Y') AS create_date, (SELECT first_name FROM rms_users WHERE rms_users.id = gsch.user_id) AS user_name
			FROM 
				`rms_group_schedule` AS gsch WHERE 1
    		";
    		
    		$where =' ';
    		$order =  ' ORDER BY `gsch`.`id` DESC ' ;
    		if(!empty($search['title'])){
    			$s_where = array();
    			$s_search = addslashes(trim($search['title']));
    			$s_where[] = " gsch.`note` LIKE '%{$s_search}%'";
    			$s_where[] = " (SELECT group_code FROM rms_group WHERE rms_group.id= gsch.group_id LIMIT 1) LIKE '%{$s_search}%'";
    			$s_where[] = " (SELECT branch_nameen FROM `rms_branch` WHERE br_id=gsch.branch_id LIMIT 1) LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		if(!empty($search['branch_id'])){
    			$where.=' AND gsch.branch_id='.$search['branch_id'];
    		}
    		if(!empty($search['study_year'])){
    			$where.=' AND gsch.year_id='.$search['study_year'];
    		}
    		if(!empty($search['group'])){
    			$where.=' AND gsch.group_id='.$search['group'];
    		}
    		$where.=$dbp->getAccessPermission('gsch.branch_id');
    		return $db->fetchAll($sql.$where.$order);
    		
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function addScheduleGroup($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		
    		$model = new Application_Model_DbTable_DbGlobal();
    		$allDay = $model->getAllDay(1);
    		
    		$_arr = array(
    				'branch_id'=>$_data['branch_id'],
    				'academic_year'=>$_data['academic_year'],
    				'group_id'=>$_data['group_code'],
    				'schedule_setting'=>$_data['schedule_setting'],
    				'note'=>$_data['note'],
    				'status'=>1,
    				'create_date'=>date("Y-m-d H:i:s"),
    				'modify_date'=>date("Y-m-d H:i:s"),
    				'user_id'=>$this->getUserId(),
    		);
    		$this->_name='rms_group_schedule';
    		$id = $this->insert($_arr);
    		
    		if(!empty($_data['identity1'])){
    			$ids = explode(',', $_data['identity1']);
    			foreach ($ids as $i){
    				$arr = array(
    						'main_schedule_id'		=>$id,
    						'branch_id'		=>$_data['branch_id'],
    						'group_id'		=>$_data['group_code'],
    						'year_id'		=>$_data['academic_year'],
    						
    						'schedule_setting_id'	=>$_data['settingdetail_'.$i],
    						'from_hour'		=>$_data['from_hour_'.$i],
    						'to_hour'		=>$_data['to_hour_'.$i],
    						
    						'create_date'	=>date("Y-m-d H:i:s"),
    						'status'		=>1,
    						'user_id'		=>$this->getUserId(),
    						
    				);
    				if (!empty($allDay)) foreach ($allDay as $day){
    					$arr['study_type'] =  $_data['type_'.$i."_".$day['id']];
    					if ( $_data['type_'.$i."_".$day['id']] ==1){
		    				$arr['day_id'] = $day['id'];
		    				$arr['subject_id'] =  $_data['subject_'.$i."_".$day['id']];
		    				$arr['techer_id'] =  $_data['teacher_'.$i."_".$day['id']];
    					}else{
    						$arr['day_id'] = $day['id'];
    						$arr['subject_id'] =  "";
    						$arr['techer_id'] =  "";
    					}
	    				$this->_name='rms_group_reschedule';
	    				if($_data['type_'.$i."_".$day['id']]==1){ // 1=study , 2=no study
	    					$this->insert($arr);
	    				}
    				}
    			}
    		}
    		$db->commit();
	   	}catch (Exception $e){
	   		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		$db->rollBack();
	   	}
    }
    function updateScheduleGroup($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    
    		$model = new Application_Model_DbTable_DbGlobal();
    		$allDay = $model->getAllDay(1);
    
    		$_arr = array(
    				'branch_id'=>$_data['branch_id'],
    				'academic_year'=>$_data['academic_year'],
    				'group_id'=>$_data['group_code'],
    				'schedule_setting'=>$_data['schedule_setting'],
    				'note'=>$_data['note'],
    				'status'=>1,
    				'modify_date'=>date("Y-m-d H:i:s"),
    				'user_id'=>$this->getUserId(),
    		);
    		$this->_name='rms_group_schedule';
    		$id = $_data['id'];
    		$where=" id = ".$id;
    		$this->update($_arr, $where);
    
    		$this->_name="rms_group_reschedule";
    		$where = " main_schedule_id=$id ";
    		$this->delete($where);
    		
    		if(!empty($_data['identity1'])){
    			$ids = explode(',', $_data['identity1']);
    			foreach ($ids as $i){
    				$arr = array(
    						'main_schedule_id'		=>$id,
    						'branch_id'		=>$_data['branch_id'],
    						'group_id'		=>$_data['group_code'],
    						'year_id'		=>$_data['academic_year'],
    
    						'schedule_setting_id'	=>$_data['settingdetail_'.$i],
    						'from_hour'		=>$_data['from_hour_'.$i],
    						'to_hour'		=>$_data['to_hour_'.$i],
    
    						'create_date'	=>date("Y-m-d H:i:s"),
    						'status'		=>1,
    						'user_id'		=>$this->getUserId(),
    
    				);
    				if (!empty($allDay)) foreach ($allDay as $day){
    					$arr['study_type'] =  $_data['type_'.$i."_".$day['id']];
    					if ( $_data['type_'.$i."_".$day['id']] ==1){
    						$arr['day_id'] = $day['id'];
    						$arr['subject_id'] =  $_data['subject_'.$i."_".$day['id']];
    						$arr['techer_id'] =  $_data['teacher_'.$i."_".$day['id']];
    					}else{
    						$arr['day_id'] = $day['id'];
    						$arr['subject_id'] =  "";
    						$arr['techer_id'] =  "";
    					}
    					$this->_name='rms_group_reschedule';
    					
    					if($_data['type_'.$i."_".$day['id']]==1){ // 1=study , 2=no study
	    					$this->insert($arr);
	    				}
    				}
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    function getScheduleGroupById($id){
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM rms_group_schedule WHERE id= $id LIMIT 1";
    	return $db->fetchRow($sql);
    }
    
    function checking($data){
    	
    	$group_id = empty($data['group_id'])?0:$data['group_id'];
    	$db = $this->getAdapter();
    	$sql="SELECT gs.id FROM rms_group_schedule AS gs
			WHERE gs.group_id=$group_id ";
    	if (!empty($data['id'])){
    		$sql.=" AND gs.id != ".$data['id'];
    	}
    	$row = $db->fetchOne($sql);
    	if (!empty($row)){
    		return 1;
    	}
    	return 0;
    }
}

