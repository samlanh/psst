<?php class Global_Model_DbTable_DbDegree extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_dept';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	
	function getAllDegree($search = ''){
		$db = $this->getAdapter();
		$sql = " SELECT 
					 dept_id,
					 en_name,
					 shortcut,
					 modify_date,
			         (SELECT CONCAT(first_name) FROM rms_users WHERE user_id=id LIMIT 1 ) AS user_name,
			         is_active as status
		         FROM 
					 rms_dept 
				 WHERE en_name!='' ";
		$orderby = " ORDER BY dept_id DESC ";
		if(empty($search)){
			return $db->fetchAll($sql.$orderby);
		}
		$where = ' ';
		if(!empty($search['title'])){
			$s_where = array();
	    		$s_search = addslashes(trim($search['title']));
		 		$s_where[] = " en_name LIKE '%{$s_search}%'";
	    		$s_where[] = " kh_name LIKE '%{$s_search}%'";
	    		$s_where[] = " shortcut LIKE '%{$s_search}%'";
	    		$sql .=' AND ( '.implode(' OR ',$s_where).')';	
			}
	    		
		if($search['status']>-1){
			$where.= " AND is_active = ".$db->quote($search['status']);
		}
		return $db->fetchAll($sql.$where.$orderby);
	}
	
	public function AddDegree($_data){
		$_db= $this->getAdapter();
		try{
			$sql="SELECT dept_id FROM rms_dept WHERE max_average =".$_data['max_average'];
			$sql.=" AND en_name='".$_data['en_name']."'";
			$rs = $_db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
			$_arr=array(
					'en_name'	  => $_data['en_name'],
					'max_average' => $_data['max_average'],
					'pass_average'=> $_data['pass_average'],
					'shortcut'    => $_data['shortcut'],
					'modify_date' => new Zend_Date(),
					'user_id'	  => $this->getUserId()
			);
			$id =  $this->insert($_arr);
				
			if(!empty($_data['identity'])){
				$this->_name='rms_dept_subject_detail';
				$ids = explode(',', $_data['identity']);
				foreach ($ids as $i){
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
		}catch(exception $e){
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
	public function UpdateDegree($_data){
		$_arr=array(
				'en_name'	  => $_data['en_name'],
				'max_average' => $_data['max_average'],
				'pass_average'=> $_data['pass_average'],
				'shortcut'    => $_data['shortcut'],
				'is_active'   => $_data['status'],
				'user_id'	  => $this->getUserId()
		);
		$where = $this->getAdapter()->quoteInto("dept_id=?",$_data["dept_id"]);
		$this->update($_arr, $where);
	
		$this->_name='rms_dept_subject_detail';
		$where = 'dept_id = '.$_data['dept_id'];
		$this->delete($where);
	
		if(!empty($_data['identity'])){
			$this->_name='rms_dept_subject_detail';
			$ids = explode(',', $_data['identity']);
			foreach ($ids as $i){
				$arr = array(
						'dept_id'		=>$_data['dept_id'],
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
	public function getDeptSubjectById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_dept_subject_detail WHERE dept_id = ".$db->quote($id);
		$row=$db->fetchAll($sql);
		return $row;
	}
	public function getDeptById($dept_id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `rms_dept` WHERE `dept_id` = $dept_id ";
		return $db->fetchRow($sql);
	}
	public function AddDegreeajax($_data){
			$_arr=array(
					'en_name'	  => $_data['fac_enname'],
					'shortcut'    => $_data['shortcut_fac'],
					'modify_date' => new Zend_Date(),
					'is_active'   => 1,
					'user_id'	  => $this->getUserId()
			);
			return $this->insert($_arr);
	}	
}
