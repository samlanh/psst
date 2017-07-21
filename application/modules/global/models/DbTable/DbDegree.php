<?php class Global_Model_DbTable_DbDegree extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_dept';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
	
	function getAllDegree($search = ''){
		$db = $this->getAdapter();
		$sql = " SELECT dept_id,en_name,shortcut,modify_date,is_active as status,
		         (SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE user_id=id LIMIT 1 ) AS user_name
		         FROM rms_dept WHERE 1 AND en_name!='' ";
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
		try{
			$_arr=array(
					'en_name'	  => $_data['en_name'],
					'shortcut'    => $_data['shortcut'],
					'modify_date' => new Zend_Date(),
					'is_active'   => $_data['status'],
					'user_id'	  => $this->getUserId()
			);
			$id =  $this->insert($_arr);
				
			if(!empty($_data['identity'])){
				$this->_name='rms_dept_subject_detail';
				$ids = explode(',', $_data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'dept_id'	=>$id,
							'subject_id'=>$_data['subject_study_'.$i],
							'score_in_class'=>$_data['scoreinclass_'.$i],
							'score_out_class'=>$_data['scoreoutclass_'.$i],
							'score_short'=>$_data['scoreshort_'.$i],
							'status'    => $_data['status_'.$i],
							'note'   	=> $_data['note_'.$i],
							'date' 		=> date("Y-m-d"),
							'user_id'	=> $this->getUserId()
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
						'dept_id'	=>$_data['dept_id'],
						'subject_id'=>$_data['subject_study_'.$i],
						'score_in_class'=>$_data['scoreinclass_'.$i],
						'score_out_class'=>$_data['scoreoutclass_'.$i],
						'score_short'=>$_data['scoreshort_'.$i],
						'status'    => $_data['status_'.$i],
						'note'   	=> $_data['note_'.$i],
						'date' 		=> date("Y-m-d"),
						'user_id'	=> $this->getUserId()
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
