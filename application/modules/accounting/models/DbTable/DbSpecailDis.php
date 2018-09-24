<?php class Accounting_Model_DbTable_DbSpecailDis extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_specail_discount';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
	
	function getAllItems($search = '',$type=null){
		$db = $this->getAdapter();
		$sql = " SELECT d.id,d.title,
		(SELECT so.title FROM `rms_schooloption` AS so WHERE so.id = d.schoolOption LIMIT 1) AS schoolOption,
		(SELECT CONCAT(first_name) FROM rms_users WHERE d.user_id=id LIMIT 1 ) AS user_name,
		d.status FROM `rms_items` AS d WHERE 1 ";
		$orderby = " ORDER BY d.type ASC, d.id DESC ";
		$where = ' ';
		if(!empty($type)){
			$where.= " AND d.type = ".$db->quote($type);
		}
		if(!empty($search['advance_search'])){
			$s_where = array();
	    		$s_search = addslashes(trim($search['advance_search']));
		 		$s_where[] = " d.title LIKE '%{$s_search}%'";
	    		$s_where[] = " d.shortcut LIKE '%{$s_search}%'";
	    		$sql .=' AND ( '.implode(' OR ',$s_where).')';	
		}
		if(!empty($search['schoolOption_search'])){
			$where.= " AND d.schoolOption  = ".$db->quote($search['schoolOption_search']);
		}
		if($search['status_search']>-1){
			$where.= " AND status = ".$db->quote($search['status_search']);
		}
		return $db->fetchAll($sql.$where.$orderby);
	}
	function getAllItemsOption($search = '',$type=null){
		$db = $this->getAdapter();
		$sql = " SELECT d.id,d.title,
		(SELECT CONCAT(first_name) FROM rms_users WHERE d.user_id=id LIMIT 1 ) AS user_name,
		d.status FROM `rms_items` AS d WHERE 1 ";
		$orderby = " ORDER BY d.type ASC, d.id DESC ";
		$where = ' ';
		if(!empty($type)){
			$where.= " AND d.type = ".$db->quote($type);
		}
		if(!empty($search['advance_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['advance_search']));
			$s_where[] = " d.title LIKE '%{$s_search}%'";
			$s_where[] = " d.shortcut LIKE '%{$s_search}%'";
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status_search']>-1){
			$where.= " AND status = ".$db->quote($search['status_search']);
		}
		return $db->fetchAll($sql.$where.$orderby);
	}
// 	public function getDegreeById($degreeId,$type=null){
// 		$db = $this->getAdapter();
// 		$sql=" SELECT * FROM $this->_name WHERE `id` = $degreeId ";
// 		if (!empty($type)){
// 			$sql.=" AND type=$type";
// 		}
// 		return $db->fetchRow($sql);
// 	}
	public function AddSpecailDis($_data){
		//$_db= $this->getAdapter();
		try{
			$_arr=array(
					'request_name'	=> $_data['request_name'],
					'phone' 		=> $_data['phone'],
					'stu_name'		=> $_data['stu_name'],
					'dis_type'		=> $_data['dis_type'],
					'duration'		=> $_data['duration'],
					'notes'			=> $_data['notes'],
					'expired_date'	=> $_data['expired_date'],
					'status'		=> $_data['status'],
					'user_id'	  	=> $this->getUserId()
			);
			$id = $this->insert($_arr);
// 			if(!empty($_data['identity'])){
// 				$this->_name='rms_dept_subject_detail';
// 				$ids = explode(',', $_data['identity']);
// 				foreach ($ids as $i){
// 					$arr = array(
// 							'dept_id'		=>$id,
// 							'score_short'	=>$_data['scoreshort_'.$i],
// 							'status'    	=> $_data['status_'.$i],
// 							'note'   		=> $_data['note_'.$i],
// 							'user_id'		=> $this->getUserId()
// 					);
// 					$this->insert($arr);
// 				}
// 			}
				//$_db->commit();
			}catch(Exception $e){
	    		//$_db->rollBack();
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
		//print_r($_data); exit();
	}
	public function UpdateDegree($_data){
		
		$_db= $this->getAdapter();
		try{
				
			$_arr=array(
					'title'	  => $_data['title'],
					'shortcut' => $_data['shortcut'],
					'type'=> $_data['type'],
// 					'schoolOption'    => $_data['schoolOption'],
// 					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'status'=> $_data['status'],
					'user_id'	  => $this->getUserId()
			);
			if ($_data['type']==1){
				$_arr['schoolOption'] = $_data['schoolOption'];
				$_arr['pass_average'] = $_data['pass_average'];
				$_arr['max_average'] = $_data['max_average'];
				
				$this->_name = "rms_items";
				$id = $_data["id"];
				$where = $this->getAdapter()->quoteInto("id=?",$id);
				$this->update($_arr, $where);
				
				
				$identitys = explode(',',$_data['identity']);
				$detailId="";
				if (!empty($identitys)){
					foreach ($identitys as $i){
						if (empty($detailId)){
							if (!empty($_data['detailid'.$i])){
								$detailId = $_data['detailid'.$i];
							}
						}else{
							if (!empty($_data['detailid'.$i])){
								$detailId= $detailId.",".$_data['detailid'.$i];
							}
						}
					}
				}
				
				$this->_name='rms_dept_subject_detail';
				$where = 'dept_id = '.$id;
				if (!empty($detailId)){
					$where.=" AND id NOT IN ($detailId) ";
				}
				$this->delete($where);
				
				if(!empty($_data['identity'])){
					$this->_name='rms_dept_subject_detail';
					$ids = explode(',', $_data['identity']);
					foreach ($ids as $i){
						if (!empty($_data['detailid'.$i])){
							$arr = array(
									'dept_id'		=>$id,
									'subject_id'	=>$_data['subject_study_'.$i],
									'score_in_class'=>$_data['scoreinclass_'.$i],
									'score_out_class'=>$_data['scoreoutclass_'.$i],
									'score_short'	=>$_data['scoreshort_'.$i],
									'status'    	=> $_data['status_'.$i],
									'note'   		=> $_data['note_'.$i],
									'date' 			=> date("Y-m-d"),
									'user_id'		=> $this->getUserId()
							);
							$where =" id =".$_data['detailid'.$i];
							$this->update($arr, $where);
						}else{
							$arr = array(
									'dept_id'		=>$id,
									'subject_id'	=>$_data['subject_study_'.$i],
									'score_in_class'=>$_data['scoreinclass_'.$i],
									'score_out_class'=>$_data['scoreoutclass_'.$i],
									'score_short'	=>$_data['scoreshort_'.$i],
									'status'    	=> $_data['status_'.$i],
									'note'   		=> $_data['note_'.$i],
									'date' 			=> date("Y-m-d"),
									'user_id'		=> $this->getUserId()
							);
							$this->insert($arr);
						}
					}
				}
				
			}else{
				$schooloption="";
				if (!empty($_data['selector'])){
					foreach ($_data['selector'] as $rs){
						if (empty($schooloption)){
							$schooloption = $rs;
						}else { $schooloption = $schooloption.",".$rs;
						}
					}
				}
				$_arr['schoolOption'] = $schooloption;
				
				$this->_name = "rms_items";
				$id = $_data["id"];
				$where = $this->getAdapter()->quoteInto("id=?",$id);
				$this->update($_arr, $where);
			}
			
			
			$this->updateItemsDetailByItems($_data);
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
		
	}
	
	public function addItemsajax($_data,$type=null){
		$_arr=array(
				'title'	  => $_data['fac_enname'],
				'shortcut'    => $_data['shortcut_fac'],
				'modify_date' => date("Y-m-d H:i:s"),
				'status'   => 1,
				'type'   => $type,
				'user_id'	  => $this->getUserId()
		);
		return $this->insert($_arr);
	}
	
	public function updateItemsDetailByItems($_data){
		$_db= $this->getAdapter();
		try{
			
			
			$_arr = array(
					'items_type'=> $_data['type'],
			);
			if ($_data['type']==1){
				$_arr['schoolOption'] = $_data['schoolOption'];
			}else{
				$schooloption="";
				if (!empty($_data['selector'])){
					foreach ($_data['selector'] as $rs){
						if (empty($schooloption)){
							$schooloption = $rs;
						}else { $schooloption = $schooloption.",".$rs;
						}
					}
				}
				$_arr['schoolOption'] = $schooloption;
			}
			
			$this->_name = "rms_itemsdetail";
			$id_items = $_data["id"];
			$where = $this->getAdapter()->quoteInto("items_id=?",$id_items);
			$this->update($_arr, $where);
// 			print_r($_data);exit();
// 			return $id_items;
				
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
	
	public function getDeptSubjectById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_dept_subject_detail WHERE dept_id = ".$db->quote($id);
		$row=$db->fetchAll($sql);
		return $row;
	}
}
