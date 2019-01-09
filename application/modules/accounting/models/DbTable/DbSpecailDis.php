<?php class Accounting_Model_DbTable_DbSpecailDis extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_specail_discount';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
	
	function getAllSpecailDis($search = '',$type=null){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$from_date =(empty($search['start_date']))? '1': "d.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "d.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql = " SELECT d.id,
		d.request_name,
		d.phone,
		d.stu_name,
		(SELECT so.dis_name FROM rms_discount AS so WHERE so.disco_id = d.dis_type LIMIT 1) AS discount_type,
		CASE    
				WHEN  d.duration_type = 1 THEN CONCAT(d.duration_type,' ".$tr->translate("MONTHLY")."') 
				WHEN  d.duration_type = 2 THEN CONCAT(d.duration_type,' ".$tr->translate("QUARTER")."')
				WHEN  d.duration_type = 3 THEN CONCAT(d.duration_type,' ".$tr->translate("SEMESTER")."')
				WHEN  d.duration_type = 4 THEN CONCAT(d.duration_type,' ".$tr->translate("YEAR")."')
				END AS duration_type,
		expired_date,
		(SELECT name_kh FROM rms_view WHERE TYPE=11 AND key_code =d.status LIMIT 1) AS status,notes,
		(SELECT CONCAT(first_name) FROM rms_users WHERE d.user_id=id LIMIT 1 ) AS user_name
		FROM `rms_specail_discount` AS d WHERE 1 ";
		$orderby = " ORDER BY d.dis_type ASC, d.id DESC ";
		if(!empty($search['advance_search'])){
			$s_where = array();
	    		$s_search = addslashes(trim($search['advance_search']));
		 		$s_where[] = " d.request_name LIKE '%{$s_search}%'";
	    		$s_where[] = " d.phone LIKE '%{$s_search}%'";
	    		$s_where[] = " d.stu_name LIKE '%{$s_search}%'";
	    		$sql .=' AND ( '.implode(' OR ',$s_where).')';	
		}
		if(!empty($search['dis_type'])){
			$where.= " AND d.dis_type  = ".$db->quote($search['dis_type']);
		}
		if(!empty($search['status_type'])){
			$where.= " AND d.status = ".$db->quote($search['status_type']);
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
	public function AddSpecailDis($_data){
		try{
			$_arr=array(
					'request_name'	=> $_data['request_name'],
					'phone' 		=> $_data['phone'],
					'stu_name'		=> $_data['stu_name'],
					'dis_type'		=> $_data['dis_type'],
					'duration'		=> $_data['duration'],
					'duration_type'		=> $_data['duration_type'],
					'notes'			=> $_data['notes'],
					'expired_date'	=> $_data['expired_date'],
					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'status'		=> $_data['status'],
					'user_id'	  	=> $this->getUserId()
			);
			$id = $this->insert($_arr);
			$part= PUBLIC_PATH.'/images/document/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			if (!empty($_data['identity'])){
				$identity = $_data['identity'];
				$ids = explode(',', $identity);
				$image_name="";
				$photo="";
				foreach ($ids as $i){
					$name = $_FILES['attachment'.$i]['name'];
					if (!empty($name)){
						$ss = 	explode(".", $name);
						$image_name = "document_".date("Y").date("m").date("d").time().$i.".".end($ss);
						$tmp = $_FILES['attachment'.$i]['tmp_name'];
						if(move_uploaded_file($tmp, $part.$image_name)){
							$photo = $image_name;
							$arr = array(
									'discount_id'=>$id,
									'fileName'=>$photo,
									);
							$this->_name = "rms_specail_discount_document";
							$this->insert($arr);
						}
						else
							$string = "Image Upload failed";
						//     				}
					}
				}
			}
			}catch(Exception $e){
	    		//$_db->rollBack();
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
		//print_r($_data); exit();
	}
	
	
	public function UpdateSpecailDis($_data){
		try{
			$_arr=array(
					'request_name'	=> $_data['request_name'],
					'phone' 		=> $_data['phone'],
					'stu_name'		=> $_data['stu_name'],
					'dis_type'		=> $_data['dis_type'],
					'duration'		=> $_data['duration'],
					'duration_type'		=> $_data['duration_type'],
					'notes'			=> $_data['notes'],
					'expired_date'	=> $_data['expired_date'],
// 					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'status'		=> $_data['status'],
					'user_id'	  	=> $this->getUserId()
			);
			$where = " id = ".$_data['id'];
			$id = $_data['id'];
			$this->update($_arr, $where);
			$part= PUBLIC_PATH.'/images/document/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}

			if (!empty($_data['identity'])){
				$identity = $_data['identity'];
				$ids = explode(',', $identity);
				$detailId="";
				foreach ($ids as $i){
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
				$this->_name = "rms_specail_discount_document";
				$where1 =" discount_id=".$id;
				if (!empty($detailId)){
					$where1.=" AND id NOT IN ($detailId) ";
				}
				$this->delete($where1);
				 
				$image_name="";
				$photo="";
				 
				foreach ($ids as $i){
					if (!empty($_data['detailid'.$i])){
						$name = $_FILES['attachment'.$i]['name'];
						if (!empty($name)){
							$ss = 	explode(".", $name);
							$image_name = "document_".date("Y").date("m").date("d").time().$i.".".end($ss);
							$tmp = $_FILES['attachment'.$i]['tmp_name'];
							if(move_uploaded_file($tmp, $part.$image_name)){
								$photo = $image_name;
								$arr = array(
										'discount_id'=>$id,
										'fileName'=>$photo,
								);
								$this->_name = "rms_specail_discount_document";
								$where=" id=".$_data['detailid'.$i];
								$this->update($arr, $where);
							}
							else
								$string = "Image Upload failed";
							//     				}
						}
					}else{
						$name = $_FILES['attachment'.$i]['name'];
						if (!empty($name)){
							$ss = 	explode(".", $name);
							$image_name = "document_".date("Y").date("m").date("d").time().$i.".".end($ss);
							$tmp = $_FILES['attachment'.$i]['tmp_name'];
							if(move_uploaded_file($tmp, $part.$image_name)){
								$photo = $image_name;
								$arr = array(
										'discount_id'=>$id,
										'fileName'=>$photo,
								);
								$this->_name = "rms_specail_discount_document";
								$this->insert($arr);
							}
							else
								$string = "Image Upload failed";
							//     				}
						}
					}
				}
			}else{
				$this->_name = "rms_specail_discount_document";
				$where1 =" discount_id=".$id;
				$this->delete($where1);
			}
			
		}catch(Exception $e){
			//$_db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getSpecailDis($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_specail_discount WHERE id = ".$db->quote($id)." LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
	
	public function getSpecailDisDetail($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `rms_specail_discount_document` WHERE discount_id = ".$db->quote($id);
		$row=$db->fetchAll($sql);
		return $row;
	}
}
