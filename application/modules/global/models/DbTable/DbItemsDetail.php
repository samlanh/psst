<?php class Global_Model_DbTable_DbItemsDetail extends Zend_Db_Table_Abstract{
	protected $_name = 'rms_itemsdetail';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
	
	function getAllItemsDetail($search = '',$items_type=null){
		$db = $this->getAdapter();
		$sql = " SELECT ide.id,ide.title,ide.shortcut,ide.ordering,
				(SELECT it.title FROM `rms_items` AS it WHERE it.id = ide.items_id LIMIT 1) AS degree,
				ide.modify_date,
				(SELECT CONCAT(first_name) FROM rms_users WHERE ide.user_id=id LIMIT 1 ) AS user_name,
				ide.status FROM `rms_itemsdetail` AS ide WHERE 1";
		$orderby = " ORDER BY ide.items_id ASC,ide.ordering ASC, ide.id DESC ";
		$where = ' ';
		if(!empty($items_type)){
			$where.= " AND ide.items_type = ".$db->quote($items_type);
		}
		if(!empty($search['advance_search'])){
			$s_where = array();
	    		$s_search = addslashes(trim($search['advance_search']));
		 		$s_where[] = " ide.title LIKE '%{$s_search}%'";
	    		$s_where[] = " ide.shortcut LIKE '%{$s_search}%'";
	    		$s_where[] = " ide.ordering LIKE '%{$s_search}%'";
	    		$sql .=' AND ( '.implode(' OR ',$s_where).')';	
		}
		if(!empty($search['items_search'])){
			$where.= " AND ide.items_id  = ".$db->quote($search['items_search']);
		}
		if($search['status_search']>-1){
			$where.= " AND status = ".$db->quote($search['status_search']);
		}
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.= $dbp->getSchoolOptionAccess('ide.schoolOption');
		
		return $db->fetchAll($sql.$where.$orderby);
	}
	
	public function getItemsDetailById($degreeId,$type=null){
		$db = $this->getAdapter();
		$sql=" SELECT ide.* FROM $this->_name AS ide WHERE ide.`id` = $degreeId ";
		if (!empty($type)){
			$sql.=" AND ide.items_type=$type";
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.= $dbp->getSchoolOptionAccess('ide.schoolOption');
		return $db->fetchRow($sql);
	}
	
	public function AddItemsDetail($_data){
		$_db= $this->getAdapter();
		try{
			$db_items = new Global_Model_DbTable_DbItems();
			$schooloption="";
			if (!empty($_data['items_id'])){
			$itemsinfo = $db_items->getDegreeById($_data['items_id'],$_data['items_type']);
			$schooloption = empty($itemsinfo['schoolOption'])?0:$itemsinfo['schoolOption'];
			}
			$sql="SELECT id FROM rms_itemsdetail WHERE items_id =".$_data['items_id'];
			$sql.=" AND items_type='".$_data['items_type']."'";
			$rs = $_db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
			$_arr=array(
					'items_id'		=> $_data['items_id'],
					'items_type'	=> $_data['items_type'],
					'title'	 		=> $_data['title'],
					'shortcut' 		=> $_data['shortcut'],
					'is_onepayment' => $_data['is_onepayment'],
					'note'   		=> $_data['note'],
					'ordering'    	=> $_data['ordering'],
// 					'is_onepayment' => $_data['is_onepayment'],
					'schoolOption'  => $schooloption,
					'create_date' 	=> date("Y-m-d H:i:s"),
					'modify_date' 	=> date("Y-m-d H:i:s"),
					'status'		=> $_data['status'],
					'user_id'	  	=> $this->getUserId()
			);
			$this->_name = "rms_itemsdetail";
			$id =  $this->insert($_arr);
			return $id;	
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
		print_r($schooloption); exit();
	}
	public function updateItemsDetail($_data){
		$_db= $this->getAdapter();
		try{
			$db_items = new Global_Model_DbTable_DbItems();
			$schooloption="";
			if (!empty($_data['items_id'])){
			$itemsinfo = $db_items->getDegreeById($_data['items_id'],$_data['items_type']);
			$schooloption = empty($itemsinfo['schoolOption'])?0:$itemsinfo['schoolOption'];
			}
			$_arr=array(
					'items_id'		=> $_data['items_id'],
					'items_type'	=> $_data['items_type'],
					'title'	  		=> $_data['title'],
					'shortcut' 		=> $_data['shortcut'],
					'note'    		=> $_data['note'],
					'ordering'    	=> $_data['ordering'],
					'is_onepayment' => $_data['is_onepayment'],
					'schoolOption'  => $schooloption,
// 					'create_date' 	=> date("Y-m-d H:i:s"),
					'modify_date' 	=> date("Y-m-d H:i:s"),
					'status'		=> $_data['status'],
					'user_id'	  	=> $this->getUserId()
			);
			$this->_name = "rms_itemsdetail";
			$id = $_data["id"];
			$where = $this->getAdapter()->quoteInto("id=?",$id);
			$this->update($_arr, $where);
			
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
	
	// For Module Product Stock
	public function AddProduct($_data){
		$_db= $this->getAdapter();
		try{
			$db_items = new Global_Model_DbTable_DbItems();
			$schooloption="";
			if (!empty($_data['items_id'])){
				$itemsinfo = $db_items->getDegreeById($_data['items_id'],$_data['items_type']);
				$schooloption = empty($itemsinfo['schoolOption'])?0:$itemsinfo['schoolOption'];
			}
			
			$part= PUBLIC_PATH.'/images/proimage/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$photo = "";
			$name = $_FILES['images']['name'];
			if (!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = "product_".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['images']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					$photo = $image_name;
				}
				else
					$string = "Image Upload failed";
			}
			
			$_arr=array(
					'items_id'=> $_data['items_id'],
					'items_type'=> $_data['items_type'],
					'code'=> $_data['code'],
					'title'	  => $_data['title'],
					'note'    => $_data['note'],
					'product_type' => $_data['product_type'],
					'is_onepayment' => $_data['is_onepayment'],
					'cost'    => $_data['cost'],
					'schoolOption'    => $schooloption,
					'images'    => $photo,
					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'status'=> 1,
					'user_id'	  => $this->getUserId()
			);
			$this->_name = "rms_itemsdetail";
			$id =  $this->insert($_arr);
			
			$this->_name='rms_product_location';
			$ids = explode(',', $_data['identity']);
			foreach ($ids as $i){
				$_arr = array(
						'pro_id'=>$id,
						'brand_id'=>$_data['brand_name_'.$i],
						'pro_qty'=>$_data['qty_'.$i],
						'price'=>$_data['price_'.$i],
						'stock_alert'=>$_data['qty_alert_'.$i],
						'note'=>$_data['note_'.$i],
				);
				$this->insert($_arr);
			}
			
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
	
	function getAllProduct($search = '',$items_type=null){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$result = $dbgb->getUserInfo();
		$level = $result["level"];
		$branch_id = $result["branch_id"];
		$string="";
		$location="";
		if ($level!==1 AND $branch_id !==1){
// 			$string = " AND $branch_id IN (pl.brand_id)";
			$string = $dbgb->getAccessPermission('pl.brand_id');
// 			$location = " AND $branch_id IN ((SELECT pl.brand_id FROM `rms_product_location` AS pl WHERE pl.pro_id = ide.id )) ";
			$location = $dbgb->getAccessPermission('(SELECT pl.brand_id FROM `rms_product_location` AS pl WHERE pl.pro_id = ide.id LIMIT 1 )');
		}
		
		
		$sql = " SELECT ide.id,ide.code,ide.title,
			(SELECT it.title FROM `rms_items` AS it WHERE it.id = ide.items_id LIMIT 1) AS degree,
			ide.cost,
			(SELECT SUM(pl.pro_qty) FROM `rms_product_location` AS pl WHERE pl.pro_id = ide.id  $string ) AS totalqty,
			CASE    
			WHEN  ide.product_type = 1 THEN '".$tr->translate("PRODUCT_FOR_SELL")."'
			WHEN  ide.product_type = 2 THEN '".$tr->translate("OFFICE_MATERIAL")."'
			END AS product_type,
			ide.modify_date,
			(SELECT CONCAT(first_name) FROM rms_users WHERE ide.user_id=id LIMIT 1 ) AS user_name,
			ide.status FROM `rms_itemsdetail` AS ide WHERE 1 AND ide.is_productseat = 0
			";
		$orderby = " ORDER BY ide.items_id ASC,ide.ordering ASC, ide.id DESC ";
		$where = ' ';
		if(!empty($items_type)){
			$where.= " AND ide.items_type = ".$db->quote($items_type);
		}
		if(!empty($search['advance_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['advance_search']));
			$s_where[] = " ide.title LIKE '%{$s_search}%'";
			$s_where[] = " ide.code LIKE '%{$s_search}%'";
			$s_where[] = " ide.cost LIKE '%{$s_search}%'";
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['items_search'])){
			$where.= " AND ide.items_id  = ".$db->quote($search['items_search']);
		}
		if($search['status_search']>-1){
			$where.= " AND status = ".$db->quote($search['status_search']);
		}
		if($search['product_type_search']>-1){
			$where.= " AND ide.product_type = ".$db->quote($search['product_type_search']);
		}
// 		echo $sql.$where.$location.$orderby;exit();
		return $db->fetchAll($sql.$where.$location.$orderby);
	}
	
	
	function getProductLocation($id){
		$db = $this->getAdapter();
		$sql = "
		SELECT pl.*,
		(SELECT s.`branch_namekh` FROM `rms_branch` AS s WHERE pl.`brand_id` = s.`br_id` LIMIT 1  ) AS branch_name
		FROM `rms_product_location` AS pl WHERE pl.pro_id = $id";
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$location = $dbgb->getAccessPermission('pl.`brand_id`');
		return $db->fetchAll($sql.$location);
	}
	
	
	public function updateProduct($_data){
		$_db= $this->getAdapter();
		try{
			$db_items = new Global_Model_DbTable_DbItems();
			$itemsinfo = $db_items->getDegreeById($_data['items_id'],$_data['items_type']);
			$schooloption = empty($itemsinfo['schoolOption'])?0:$itemsinfo['schoolOption'];
				
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$result = $dbgb->getUserInfo();
			$level = $result["level"];
			$branch_id = $result["branch_id"];
			
			$part= PUBLIC_PATH.'/images/proimage/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$photo = "";
				
			$_arr=array(
					'items_id'=> $_data['items_id'],
					'items_type'=> $_data['items_type'],
					'code'=> $_data['code'],
					'title'	  => $_data['title'],
					'note'    => $_data['note'],
					'product_type' => $_data['product_type'],
					'is_onepayment' => $_data['is_onepayment'],
					'cost'    => $_data['cost'],
					'schoolOption'    => $schooloption,
					'modify_date' => date("Y-m-d H:i:s"),
					'status'=> $_data['status'],
					'user_id'	  => $this->getUserId()
			);
			$this->_name = "rms_itemsdetail";
			$name = $_FILES['images']['name'];
			if (!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = "product_".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['images']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					if (file_exists($part.$_data["old_photo"])) {
						if (!empty($_data["old_photo"])){
							unlink($part.$_data["old_photo"]);//delete old file
						}
					}
					$_arr['images'] = $image_name;
				}
			}
			$id =  $_data["id"];
			$where = $_db->quoteInto("id=?", $id);
			$this->update($_arr, $where);
			

			
			if ($level==1 AND $branch_id==1){ // only main Branch and Admin user
				// For Product Location Section
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
				$this->_name="rms_product_location";
				$where="pro_id = ".$_data["id"];
				if (!empty($detailId)){
					$where.=" AND id NOT IN ($detailId) ";
				}
				$this->delete($where);
			}
			
			if (!empty($_data['identity'])){
				$this->_name='rms_product_location';
				$ids = explode(',', $_data['identity']);
				foreach ($ids as $i){
					if (!empty($_data['detailid'.$i])){
						$_arr = array(
								'pro_id'=>$id,
								'brand_id'=>$_data['brand_name_'.$i],
								'pro_qty'=>$_data['qty_'.$i],
								'price'=>$_data['price_'.$i],
								'stock_alert'=>$_data['qty_alert_'.$i],
								'note'=>$_data['note_'.$i],
						);
						$where =" id =".$_data['detailid'.$i];
						$this->update($_arr, $where);
					}else{
						$_arr = array(
								'pro_id'=>$id,
								'brand_id'=>$_data['brand_name_'.$i],
								'pro_qty'=>$_data['qty_'.$i],
								'price'=>$_data['price_'.$i],
								'stock_alert'=>$_data['qty_alert_'.$i],
								'note'=>$_data['note_'.$i],
						);
						$this->insert($_arr);
					}
				}
			}
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
	
	
	public function addProductSet($_data){
		$_db= $this->getAdapter();
		try{
			$db_items = new Global_Model_DbTable_DbItems();
			$schooloption="";
			if (!empty($_data['items_id'])){
				$itemsinfo = $db_items->getDegreeById($_data['items_id'],$_data['items_type']);
				$schooloption = empty($itemsinfo['schoolOption'])?0:$itemsinfo['schoolOption'];
			}
				
			$_arr=array(
					'items_id'=> $_data['items_id'],
					'items_type'=> $_data['items_type'],
					'code'=> $_data['code'],
					'title'	  => $_data['title'],
					'note'    => $_data['note'],
					'is_onepayment' => $_data['is_onepayment'],
					'product_type' => 1,
					'is_productseat' => 1,
					'price'    => $_data['price'],
					'schoolOption'    => $schooloption,
					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'status'=> 1,
					'user_id'	  => $this->getUserId()
			);
			$this->_name = "rms_itemsdetail";
			$id =  $this->insert($_arr);
			
			$this->_name='rms_product_setdetail';
			$ids = explode(',', $_data['identity']);
			if (!empty($ids)){
				foreach ($ids as $i){
					$_arrss = array(
							'pro_id'=>$id,
							'subpro_id'=>$_data['product_'.$i],
							'qty'=>$_data['qty_'.$i],
							'remark'=>$_data['note_'.$i],
					);
					$this->insert($_arrss);
				}
			}
				
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
	public function updateProductSet($_data){
		$_db= $this->getAdapter();
		try{
			$db_items = new Global_Model_DbTable_DbItems();
			$schooloption="";
			if (!empty($_data['items_id'])){
				$itemsinfo = $db_items->getDegreeById($_data['items_id'],$_data['items_type']);
				$schooloption = empty($itemsinfo['schoolOption'])?0:$itemsinfo['schoolOption'];
			}
	
			$_arr=array(
					'items_id'=> $_data['items_id'],
					'items_type'=> $_data['items_type'],
					'code'=> $_data['code'],
					'title'	  => $_data['title'],
					'note'    => $_data['note'],
					'is_onepayment' => $_data['is_onepayment'],
					'product_type' => 1,
					'is_productseat' => 1,
					'price'    => $_data['price'],
					'schoolOption'    => $schooloption,
// 					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'status'=> 1,
					'user_id'	  => $this->getUserId()
			);
			$this->_name = "rms_itemsdetail";
			$id =  $_data["id"];
			$where = $_db->quoteInto("id=?", $id);
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
			$this->_name='rms_product_setdetail';
			$where="pro_id = ".$_data["id"];
			if (!empty($detailId)){
				$where.=" AND id NOT IN ($detailId) ";
			}
			$this->delete($where);
			
			$this->_name='rms_product_setdetail';
			$ids = explode(',', $_data['identity']);
			if (!empty($ids)){
				foreach ($ids as $i){
					if (!empty($_data['detailid'.$i])){
						$_arrss = array(
								'pro_id'=>$id,
								'subpro_id'=>$_data['product_'.$i],
								'qty'=>$_data['qty_'.$i],
								'remark'=>$_data['note_'.$i],
						);
						$where =" id =".$_data['detailid'.$i];
						$this->update($_arrss, $where);
					}else{
						$_arrss = array(
								'pro_id'=>$id,
								'subpro_id'=>$_data['product_'.$i],
								'qty'=>$_data['qty_'.$i],
								'remark'=>$_data['note_'.$i],
						);
						$this->insert($_arrss);
					}
				}
			}
	
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
	function getAllProductSet($search = '',$items_type=null){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
	
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$result = $dbgb->getUserInfo();
		$level = $result["level"];
		$branch_id = $result["branch_id"];
		$string="";
		$location="";
	
		$sql = " SELECT ide.id,ide.code,ide.title,
			(SELECT it.title FROM `rms_items` AS it WHERE it.id = ide.items_id LIMIT 1) AS degree,
			ide.price,
			ide.modify_date,
			(SELECT CONCAT(first_name) FROM rms_users WHERE ide.user_id=id LIMIT 1 ) AS user_name,
			ide.status FROM `rms_itemsdetail` AS ide WHERE 1 AND ide.is_productseat = 1
		";
		$orderby = " ORDER BY ide.items_id ASC,ide.ordering ASC, ide.id DESC ";
		$where = ' ';
		if(!empty($items_type)){
			$where.= " AND ide.items_type = ".$db->quote($items_type);
		}
		
		$from_date =(empty($search['start_date']))? '1': " ide.modify_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " ide.modify_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['advance_search'])){
		$s_where = array();
		$s_search = addslashes(trim($search['advance_search']));
			$s_where[] = " ide.title LIKE '%{$s_search}%'";
			$s_where[] = " ide.price LIKE '%{$s_search}%'";
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['items_search'])){
		$where.= " AND ide.items_id  = ".$db->quote($search['items_search']);
		}
		if($search['status_search']>-1){
			$where.= " AND status = ".$db->quote($search['status_search']);
		}
// 		if($search['product_type_search']>-1){
// 			$where.= " AND ide.product_type = ".$db->quote($search['product_type_search']);
// 		}
		return $db->fetchAll($sql.$where.$location.$orderby);
	}
	function getProductSetDetailById($id){
		$db=$this->getAdapter();
		$sql="SELECT *,
			(SELECT p.title FROM `rms_itemsdetail` AS p WHERE p.id = rms_product_setdetail.subpro_id LIMIT 1) AS title,
			(SELECT p.cost FROM `rms_itemsdetail` AS p WHERE p.id = rms_product_setdetail.subpro_id LIMIT 1) AS pro_price
			FROM rms_product_setdetail
			WHERE pro_id=$id";
		return $db->fetchAll($sql);
	}
	
	function getAllProductsNormal($product_type=null){
		$db = $this->getAdapter();
		$sql="SELECT i.id,
		CONCAT(i.title,' (',(SELECT it.title FROM `rms_items` AS it WHERE it.id = i.items_id LIMIT 1),')') AS name
		FROM `rms_itemsdetail` AS i
		WHERE i.status =1 AND i.items_type=3 AND i.is_productseat=0  ";
		
		if(!empty($product_type)){//check type product sale or office
			$sql.= " AND i.product_type = ".$db->quote($product_type);
		}
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$branchlist = $dbgb->getAllSchoolOption();
		if (!empty($branchlist)){
			foreach ($branchlist as $i){
				$s_where[] = $i['id']." IN (i.schoolOption)";
			}
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
		}
		$user = $dbgb->getUserInfo();
		$level = $user['level'];
		if ($level!=1){
			$sql .=' AND '.$user['schoolOption'].' IN (i.schoolOption)';
		}
		$sql.=" ORDER BY i.items_id ASC, i.ordering ASC";
		return $db->fetchAll($sql);
	}
	
	//Ajx Function
	public function addDegreeByAjax($data){
		try{
			$this->_name='rms_items';
			$_arr=array(
					'title'	  => $data['fac_enname'],
					'shortcut'    => $data['shortcut_fac'],
					'schoolOption'    => $data['schoolOption'],
					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'type'=> 1,//degree
					'status'=> 1,
					'user_id'	  => $this->getUserId()
			);
			$id =  $this->insert($_arr);
	
			if(!empty($data['identity'])){
				$this->_name='rms_dept_subject_detail';
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'dept_id'	=>$id,
							'subject_id'=>$data['subject_study_'.$i],
							'score_in_class'=>$data['scoreinclass_'.$i],
							'score_out_class'=>$data['scoreoutclass_'.$i],
							'score_short'=>$data['scoreshort_'.$i],
							'note'   	=> $data['note_'.$i],
							'date' 		=> date("Y-m-d"),
							'user_id'	=> $this->getUserId()
					);
					$this->insert($arr);
				}
			}
				
			return $id;
				
		}catch(exception $e){
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
	
	
	public function AddGradeByAjax($_data){//To Items Detail
		$_db= $this->getAdapter();
		try{
			$db_items = new Global_Model_DbTable_DbItems();
			$itemsinfo = $db_items->getDegreeById($_data['degree_popup1'],1);
			$schooloption = empty($itemsinfo['schoolOption'])?0:$itemsinfo['schoolOption'];
			$_arr=array(
					'items_id'=> $_data['degree_popup1'],
					'items_type'=> 1,
					'title'	  => $_data['major_enname'],
					'shortcut' => $_data['shortcut'],
// 					'note'    => $_data['note'],
// 					'ordering'    => $_data['ordering'],
					'schoolOption'    => $schooloption,
					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'status'=> $_data['grade_status'],
					'user_id'	  => $this->getUserId()
			);
			$this->_name = "rms_itemsdetail";
			$id =  $this->insert($_arr);
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
	
	
	
	function CheckProductHasExit($data){
		$db = $this->getAdapter();
		$sql="SELECT ite.* FROM `rms_itemsdetail` AS ite WHERE ite.title='".$data['title']."' AND ite.items_type=3 AND ite.items_id=".$data['category']." ";
		if (!empty($data['id'])){
			$sql.=" AND ite.id != ".$data['id'];
		}
		$sql.=" LIMIT 1";
		$row = $db->fetchRow($sql);
		if (empty($row)) {
			return 1;
		}else{
			return 2;
		}
	}
}
