<?php

class Setting_Model_DbTable_DbCertificate extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_certificate_setting'; 
	 public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
    
    function addCertifSetting($_data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
    		$part= PUBLIC_PATH.'/images/card/';
    		$name = $_FILES['photo']['name'];
    		$size = $_FILES['photo']['size'];
    		if (!file_exists($part)) {
    			mkdir($part, 0777, true);
    		}
    		$photo='';
    		$dbg = new Application_Model_DbTable_DbGlobal();
    		if (!empty($name)){
    			$ss = 	explode(".", $name);
    			$image_name = "background_certifcate_".date("Y").date("m").date("d").time().".".end($ss);
    			$tmp = $_FILES['photo']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$image_name)){
    				$photo = $image_name;
    			}
    			else
    				$string = "Image Upload failed";
    		}
			
	    	$_arr = array(
	    			'branch_id'	    =>$_data['branch_id'],
	    			'schoolOption'	=>$_data['schoolOption'],
					'title'	=>$_data['title'],
					'certificate_describe'	=>$_data['certificate_describe'],
	    			'background' 	=>$photo,

	    			'name_left'	=>$_data['name_left'],
					'name_top'	=>$_data['name_top'],

					'gender_left'	=>$_data['gender_left'],
					'gender_top'	=>$_data['gender_top'],

					'date_left'	=>$_data['date_left'],
					'date_top'	=>$_data['date_top'],

					'code_left'	=>$_data['code_left'],
					'code_top'	=>$_data['code_top'],

					'academic_left'	=>$_data['academic_left'],
					'academic_top'	=>$_data['academic_top'],

					'rank_left'	=>$_data['rank_left'],
					'rank_top'	=>$_data['rank_top'],
					
					'grade_left'	=>$_data['grade_left'],
					'grade_top'	=>$_data['grade_top'],

					'describe_left'	=>$_data['describe_left'],
					'describe_top'	=>$_data['describe_top'],

					'month_left'	=>$_data['month_left'],
					'month_top'	=>$_data['month_top'],

					'day_left'	=>$_data['day_left'],
					'day_top'	=>$_data['day_top'],

					'year_left'	=>$_data['year_left'],
					'year_top'	=>$_data['year_top'],
					'default'		=>1,
	    			'status'		=>1,
					'modify_date'	=>date("Y-m-d H:i:s"),
					'create_date'	=>date("Y-m-d H:i:s"),
	    			'user_id'		=>$this->getUserId(),					
	    			);
	    	$this->_name ="rms_certificate_setting";
	    	$this->insert($_arr);//insert data
	    	
	    	$_db->commit();
	    	}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    		$_db->rollBack();
	    	}
    }

    public function updateCertificate($_data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
			$id = $_data['id'];
    		
			$_arr = array(
					'branch_id'	    =>$_data['branch_id'],
					'schoolOption'	=>$_data['schoolOption'],
					'title'	=>$_data['title'],
					'certificate_describe'	=>$_data['certificate_describe'],
	    			'name_left'	=>$_data['name_left'],
					'name_top'	=>$_data['name_top'],

					'gender_left'	=>$_data['gender_left'],
					'gender_top'	=>$_data['gender_top'],

					'date_left'	=>$_data['date_left'],
					'date_top'	=>$_data['date_top'],

					'code_left'	=>$_data['code_left'],
					'code_top'	=>$_data['code_top'],

					'academic_left'	=>$_data['academic_left'],
					'academic_top'	=>$_data['academic_top'],

					'rank_left'	=>$_data['rank_left'],
					'rank_top'	=>$_data['rank_top'],
					
					'grade_left'	=>$_data['grade_left'],
					'grade_top'	=>$_data['grade_top'],

					'describe_left'	=>$_data['describe_left'],
					'describe_top'	=>$_data['describe_top'],

					'month_left'	=>$_data['month_left'],
					'month_top'	=>$_data['month_top'],

					'day_left'	=>$_data['day_left'],
					'day_top'	=>$_data['day_top'],

					'year_left'	=>$_data['year_left'],
					'year_top'	=>$_data['year_top'],

	    			'status'		=>1,
					'modify_date'	=>date("Y-m-d H:i:s"),
					'create_date'	=>date("Y-m-d H:i:s"),
	    			'user_id'		=>$this->getUserId(),		
			);
			
			$part = PUBLIC_PATH . '/images/card/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$photo_name = $_FILES['photo']['name'];
			if (!empty($photo_name)) {
				//unset old file here
				$tem = explode(".", $photo_name);
				$image_name = "background_certifcate_" . date("Y") . date("m") . date("d") . time() . "." . end($tem);
				$tmp = $_FILES['photo']['tmp_name'];
				if (move_uploaded_file($tmp, $part . $image_name)) {
					move_uploaded_file($tmp, $part . $image_name);
					$photo = $image_name;
					$_arr['background'] = $photo;
				}
			}
			if (!empty($photo_name) and file_exists($part . $_data['old_photo'])) { //delelete old file
				unlink($part . $_data['old_photo']);
			}
			
			$this->_name ="rms_certificate_setting";
			$where=$this->getAdapter()->quoteInto("id=?", $id);
			$this->update($_arr, $where);
			
			$_db->commit();
    	}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$_db->rollBack();
    	}
    }
   	
    function getAllCertificate($search){
    	$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$check = '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
    	$uncheck = '<i class="fa fa-square-o" aria-hidden="true"></i>';
    	$sql = "SELECT b.id,
    	-- CASE    
		-- 		WHEN  b.default = 1 THEN '$check'
		-- 		WHEN  b.default = 0 THEN '$uncheck'
		-- 		END AS student_statustitle,
    	b.title,
    	(SELECT bs.branch_nameen FROM rms_branch as bs WHERE bs.br_id =b.branch_id LIMIT 1) as branch_name,
    	(SELECT sp.title FROM `rms_schooloption` AS sp WHERE sp.id = b.schoolOption LIMIT 1) AS schoolOption,
    	b.create_date  ";
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->caseStatusShowImage("b.status");
    	$sql.=" FROM rms_certificate_setting AS b ";
    	
    	$where = ' WHERE  b.title !="" ';   	
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=trim(addslashes($search['adv_search']));
    		$s_where[]=" b.title LIKE '%{$s_search}%'";
    		$s_where[]=" b.note LIKE '%{$s_search}%'";
    		
    		$where.=' AND ('.implode(' OR ',$s_where).')';
    	}
		if($search['status']>-1){
			$where.= " AND b.status = ".$search['status'];
		}
		
		$where.= $dbp->getAccessPermission('b.branch_id');
		
    	$order=' ORDER BY b.id DESC';
   		return $db->fetchAll($sql.$where.$order);
   }
      
 function getCertifById($id){
 	$dbp = new Application_Model_DbTable_DbGlobal();
	 	$sql_str=$dbp->caseStatusShowImage("status");
	 	
    	$db = $this->getAdapter();
    	$sql = "SELECT *
			$sql_str
    	 FROM
    	$this->_name ";
    	$where = " WHERE `id`= $id ";
    	
   		return $db->fetchRow($sql.$where);
    }
    
}  
	  

