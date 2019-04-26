<?php class Global_Model_DbTable_DbItems extends Zend_Db_Table_Abstract{
	protected $_name = 'rms_items';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
	
	function getAllItems($search = '',$type=null){
		$db = $this->getAdapter();
		$sql = " SELECT 
					d.id,
					d.title,
					d.title_en,
					d.shortcut,
					d.ordering,
					(SELECT so.title FROM `rms_schooloption` AS so WHERE so.id = d.schoolOption LIMIT 1) AS schoolOption,
					(SELECT CONCAT(first_name) FROM rms_users WHERE d.user_id=id LIMIT 1 ) AS user_name,
					d.create_date,
					d.modify_date
			";
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbgb->caseStatusShowImage("d.status");
		$sql.=" FROM `rms_items` AS d  WHERE 1 ";
		
		$orderby = " ORDER BY d.type ASC, d.ordering DESC,d.id DESC ";
		$where = ' ';
		if(!empty($type)){
			$where.= " AND d.type = ".$db->quote($type);
		}
		if(!empty($search['advance_search'])){
			$s_where = array();
	    		$s_search = addslashes(trim($search['advance_search']));
		 		$s_where[] = " d.title LIKE '%{$s_search}%'";
		 		$s_where[] = " d.title_en LIKE '%{$s_search}%'";
	    		$s_where[] = " d.shortcut LIKE '%{$s_search}%'";
	    		$sql .=' AND ( '.implode(' OR ',$s_where).')';	
		}
		if(!empty($search['schoolOption_search'])){
			$where.= " AND d.schoolOption  = ".$db->quote($search['schoolOption_search']);
		}
		if($search['status_search']>-1){
			$where.= " AND status = ".$db->quote($search['status_search']);
		}
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.= $dbp->getSchoolOptionAccess('d.schoolOption');
		
		return $db->fetchAll($sql.$where.$orderby);
	}
	function getAllItemsOption($search = '',$type=null){
		$db = $this->getAdapter();
		$sql = "SELECT d.id,d.title,d.title_en,
			(SELECT CONCAT(first_name) FROM rms_users WHERE d.user_id=id LIMIT 1 ) AS user_name
				  ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("d.status");
		$sql.=" FROM `rms_items` AS d WHERE 1 ";
		$where = ' ';
		if(!empty($type)){
			$where.= " AND d.type = ".$db->quote($type);
		}
		if(!empty($search['advance_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['advance_search']));
			$s_where[] = " d.title LIKE '%{$s_search}%'";
			$s_where[] = " d.title_en LIKE '%{$s_search}%'";
			$s_where[] = " d.shortcut LIKE '%{$s_search}%'";
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status_search']>-1){
			$where.= " AND status = ".$db->quote($search['status_search']);
		}
		$where.= $dbp->getSchoolOptionAccess('d.schoolOption');
		
		$orderby = " ORDER BY d.type ASC, d.id DESC ";
		return $db->fetchAll($sql.$where.$orderby);
	}
	public function getDegreeById($degreeId,$type=null){
		$db = $this->getAdapter();
		$sql=" SELECT d.* FROM $this->_name AS d WHERE d.`id` = $degreeId ";
		if (!empty($type)){
			$sql.=" AND d.type=$type";
		}

		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.= $dbp->getSchoolOptionAccess('d.schoolOption');
		
		return $db->fetchRow($sql);
	}
	public function AddDegree($_data){
		$_db= $this->getAdapter();
		try{
			$_arr=array(
					'title'	  		=> $_data['title'],
					'title_en'	  	=> $_data['title_en'],
					'shortcut' 		=> $_data['shortcut'],
					'ordering'		=> $_data['ordering'],
					'type'			=> $_data['type'],
					'create_date' 	=> date("Y-m-d H:i:s"),
					'modify_date' 	=> date("Y-m-d H:i:s"),
					'status'		=> 1,
					'user_id'	 	=> $this->getUserId()
			);
			if ($_data['type']==1){
				$_arr['schoolOption'] = $_data['schoolOption'];
				$this->_name = "rms_items";
				$id =  $this->insert($_arr);
				
				if(!empty($_data['identity'])){
					$this->_name='rms_dept_subject_detail';
					$ids = explode(',', $_data['identity']);
					foreach ($ids as $i){
						$arr = array(
								'dept_id'		=> $id,
								'subject_id'	=> $_data['subject_study_'.$i],
								'score_in_class'=> $_data['scoreinclass_'.$i],
								'score_out_class'=> $_data['scoreoutclass_'.$i],
								'score_short'	=> $_data['scoreshort_'.$i],
								'status'    	=> 1,
								'note'   		=> $_data['note_'.$i],
								'date' 			=> date("Y-m-d"),
								'user_id'		=> $this->getUserId()
						);
						$this->insert($arr);
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
				$id =  $this->insert($_arr);
			}
			if($_data['type']==1){
				if(!empty($_data['identity1'])){
					$idss = explode(',', $_data['identity1']);
					foreach ($idss as $j){
						$arr = array(
								'degree_id'		=> $id,
								'comment_id'	=> $_data['comment_'.$j],
								'note'   		=> $_data['remark'.$j],
								'create_date' 	=> date("Y-m-d"),
								'user_id'		=> $this->getUserId()
						);
						$this->_name='rms_degree_comment';
						$this->insert($arr);
					}
				}
			}
			return $id;	
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
		}
	}
	public function UpdateDegree($_data){
		$_db= $this->getAdapter();
		try{
			$_arr=array(
					'title'	  		=> $_data['title'],
					'title_en'	  	=> $_data['title_en'],
					'shortcut' 		=> $_data['shortcut'],
					'type'			=> $_data['type'],
					'ordering'		=> $_data['ordering'],
					'modify_date' 	=> date("Y-m-d H:i:s"),
					'status'		=> $_data['status'],
					'user_id'	  	=> $this->getUserId()
			);
			if ($_data['type']==1){
				$_arr['schoolOption'] = $_data['schoolOption'];
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
									//'status'    	=> $_data['status_'.$i],
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
									//'status'    	=> $_data['status_'.$i],
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
			
		//////////////////////// degree comment ////////////////////////////////////	
		if($_data['type']==1){
				$identitys1 = explode(',',$_data['identity1']);
				$oldId="";
				if(!empty($identitys1)){
					foreach($identitys1 as $j){
						if(empty($oldId)){
							if(!empty($_data['old_id_'.$j])){
								$oldId = $_data['old_id_'.$j];
							}
						}else{
							if(!empty($_data['old_id_'.$j])){
								$oldId= $oldId.",".$_data['old_id_'.$j];
							}
						}
					}
				}
				
				$this->_name='rms_degree_comment';
				$where = 'degree_id = '.$id;
				if(!empty($oldId)){
					$where.=" AND id NOT IN ($oldId) ";
				}
				$this->delete($where);
				
				if(!empty($_data['identity1'])){
					$this->_name='rms_degree_comment';
					$ids1 = explode(',', $_data['identity1']);
					foreach ($ids1 as $k){
						if (!empty($_data['old_id_'.$k])){
							$arr = array(
									'degree_id'		=> $id,
									'comment_id'	=> $_data['comment_'.$k],
									'note'   		=> $_data['remark'.$k],
									'create_date' 	=> date("Y-m-d"),
									'user_id'		=> $this->getUserId()
							);
							$where =" id =".$_data['old_id_'.$k];
							$this->update($arr, $where);
						}else{
							$arr = array(
									'degree_id'		=> $id,
									'comment_id'	=> $_data['comment_'.$k],
									'note'   		=> $_data['remark'.$k],
									'create_date' 	=> date("Y-m-d"),
									'user_id'		=> $this->getUserId()
							);
							$this->insert($arr);
						}
					}
				}							
			}
			$this->updateItemsDetailByItems($_data);
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
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
				
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
		}
	}
	public function getDeptSubjectById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_dept_subject_detail WHERE dept_id = ".$db->quote($id);
		$row=$db->fetchAll($sql);
		return $row;
	}
	public function getDDegreeCommentById($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
					*,
					(select comment from rms_comment where rms_comment.id = comment_id limit 1 ) as comment 
				FROM 
					rms_degree_comment 
				WHERE 
					degree_id = ".$db->quote($id);
		return $db->fetchAll($sql);
	}
	public function getAllComment(){
		$db = $this->getAdapter();
		$sql = "SELECT id,comment FROM rms_comment WHERE status = 1 order by id ASC";
		return $db->fetchAll($sql);
	}
}