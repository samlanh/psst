<?php
class Placement_Model_DbTable_DbSetting extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_placementtest_setting';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllPlacementTestSetting($search = '',$items_type=null){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql = " SELECT 
				s.id,
				s.title,
				(SELECT tt.title FROM rms_test_type AS tt WHERE tt.id=s.test_type LIMIT 1 ) AS test_type,
				s.note,s.create_date
    		";
    	$sql.=$dbp->caseStatusShowImage("s.status");
    	$sql.=" FROM `rms_placementtest_setting` AS s
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
    	if(!empty($search['status'])){
    		$where.= " AND s.status = ".$db->quote($search['status']);
    	}
    	if(!empty($search['test_type'])){
    		$where.= " AND s.test_type = ".$db->quote($search['test_type']);
    	}
    	return $db->fetchAll($sql.$where.$orderby);
    }
	public function addPlacementTestSetting($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$_arr = array(
					'test_type'=>$_data['test_type'],
					'title'=>$_data['title'],
					'duration'=>$_data['duration'],
					'note'=>$_data['note'],
					'status'=>1,
					'create_date'=>date("Y-m-d H:i:s"),
					'modify_date'=>date("Y-m-d H:i:s"),
					'user_id'=>$this->getUserId(),
			);
			$this->_name='rms_placementtest_setting';
			$id = $this->insert($_arr);
			
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
				if(!empty($ids))foreach ($ids as $i){
					$arr=array(
							'setting_id'=>$id,
							'section_id'=>$_data['section_id'.$i],
							'note'=>$_data['note_'.$i],
					);
					$this->_name='rms_placementtest_setting_detail';
					$this->insert($arr);
				}
			}
		  $db->commit();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
			
		}
   }
   function getPlacementTestSettingById($id){
   		$db = $this->getAdapter();
   		$sql="SELECT s.* FROM `rms_placementtest_setting` AS s WHERE s.id=$id";
   		return $db->fetchRow($sql);
   }
   function getPlacementTestSettingDetail($id){
   	$db = $this->getAdapter();
   	$sql="SELECT s.*,(SELECT es.title FROM `rms_section` AS es WHERE es.id = s.section_id LIMIT 1) AS section_title FROM `rms_placementtest_setting_detail` AS s WHERE s.setting_id=$id";
   	return $db->fetchAll($sql);
   }
   public function editPlacementTestSetting($_data){
	   	$db = $this->getAdapter();
	   	$db->beginTransaction();
	   	try{
	   		$_arr = array(
	   				'test_type'=>$_data['test_type'],
				'title'=>$_data['title'],
	   				'duration'=>$_data['duration'],
				'note'=>$_data['note'],
				'status'=>$_data['status'],
				'modify_date'=>date("Y-m-d H:i:s"),
				'user_id'=>$this->getUserId(),
	   		);
	   		$this->_name='rms_placementtest_setting';
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
	   		$this->_name="rms_placementtest_setting_detail";
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
								'section_id'=>$_data['section_id'.$i],
								'note'=>$_data['note_'.$i],
						);
	   					$this->_name='rms_placementtest_setting_detail';
	   					$where = " id =".$_data['detailid'.$i];
						$this->update($arr, $where);
	   				}else{
		   				$arr=array(
								'setting_id'=>$id,
								'section_id'=>$_data['section_id'.$i],
								'note'=>$_data['note_'.$i],
						);
		   				$this->_name='rms_placementtest_setting_detail';
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
   
   function checkSettingInUse($placement_setting_id){
   	$db = $this->getAdapter();
	   	$sql=" SELECT pt.* FROM `rms_placement_test` AS pt WHERE pt.placement_setting_id=$placement_setting_id ";
	   	$sql.=" ORDER BY pt.id DESC LIMIT 1 ";
   	$rs = $db->fetchRow($sql);
   	if (!empty($rs)){
   		return 1;
   	}
   	return 0;
   }
}