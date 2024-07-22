<?php class Global_Model_DbTable_DbItemsDetail extends Zend_Db_Table_Abstract{
	protected $_name = 'rms_itemsdetail';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
	public function checkServiceCate($id){
		$db = $this->getAdapter();
		$sql="SELECT s.id FROM rms_itemsdetail AS s WHERE s.items_type=2 AND s.items_id=$id ORDER BY s.id DESC LIMIT 1";
		return $db->fetchOne($sql);
	}
	public function checkService($id){
		$db = $this->getAdapter();
		$sql="SELECT s.id FROM rms_tuitionfee_detail AS s WHERE s.class_id=$id ORDER BY s.id DESC LIMIT 1";
		return $db->fetchOne($sql);
	}

	function deleteItemDetail($item_id){

		$this->_name="rms_itemsdetail";
		$where ="id=".$item_id;
		$this->delete($where);
		return $item_id;
	}
	function getAllItemsDetail($search = '',$items_type=null){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$one_payment = $tr->translate('ONE_PAYMENTONLY');
		$validate = $tr->translate('IS_VALIDATE');

		
		$sql = " SELECT 
					ide.id,
					ide.title,
					ide.title_en,
					ide.shortcut,
					(SELECT it.$colunmname FROM `rms_items` AS it WHERE it.id = ide.items_id LIMIT 1) AS degree,
					CASE
						WHEN is_onepayment = 1 THEN '$one_payment'
						WHEN is_onepayment = 0 THEN '$validate'
					END as is_onepayment,
					ide.ordering,
					ide.note,
					ide.create_date,
					ide.modify_date,
					(SELECT CONCAT(first_name) FROM rms_users WHERE ide.user_id=id LIMIT 1 ) AS user_name ";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("ide.status");
		$sql.=" FROM `rms_itemsdetail` AS ide WHERE 1 ";
		
		$orderby = " ORDER BY ide.items_id DESC,ide.ordering DESC, ide.id DESC ";
		$where = ' ';
		if(!empty($items_type)){
			$where.= " AND ide.items_type = ".$db->quote($items_type);
		}
		if(!empty($search['advance_search'])){
				$s_where = array();
	    		$s_search = addslashes(trim($search['advance_search']));
		 		$s_where[] = " ide.title LIKE '%{$s_search}%'";
		 		$s_where[] = " ide.title_en LIKE '%{$s_search}%'";
	    		$s_where[] = " ide.shortcut LIKE '%{$s_search}%'";
	    		$s_where[] = " ide.ordering LIKE '%{$s_search}%'";
	    		$sql .=' AND ( '.implode(' OR ',$s_where).')';	
		}
		if(!empty($search['items_search'])){
			$where.= " AND ide.items_id  = ".$db->quote($search['items_search']);
		}
		if($search['status_search']>-1){
			$where.= " AND ide.status = ".$db->quote($search['status_search']);
		}
		if($search['is_onepayment']>-1){
			$where.= " AND ide.is_onepayment = ".$db->quote($search['is_onepayment']);
		}
		if($search['auto_payment']>-1){
			$where.= " AND ide.is_autopayment = ".$db->quote($search['auto_payment']);
		}
		$where.= $dbp->getSchoolOptionAccess('ide.schoolOption');
		$where.= $dbp->getDegreePermission('ide.items_id');
		return $db->fetchAll($sql.$where.$orderby);
	}
	public function getItemsDetailById($degreeId,$type=null,$is_set=null){
		$db = $this->getAdapter();
		$sql=" SELECT ide.*,
		ide.branch_id as branchSet,
		(SELECT pl.branch_id FROM `rms_product_location` AS pl WHERE ide.id=pl.pro_id LIMIT 1) AS branch_id,
		(SELECT pl.price FROM `rms_product_location` AS pl WHERE ide.id=pl.pro_id LIMIT 1) AS price
		FROM rms_itemsdetail AS ide WHERE ide.`id` = $degreeId ";
		if(!empty($type)){
			$sql.=" AND ide.items_type=$type";
		}
		if(empty($is_set)){
			$sql.=" AND ide.is_productseat=0 ";
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.= $dbp->getSchoolOptionAccess('ide.schoolOption');
		$userSelect = $dbp->getUserProfile();
		if(!empty($userSelect['degreeList'])){
			$degreeList = $userSelect['degreeList'];
			$sql.="
				AND 
					CASE  WHEN ide.items_type != 1 THEN '1' 
					ELSE ide.items_id IN (".$degreeList.") 
					END
				";
		}
		return $db->fetchRow($sql);
	}
	
	public function AddItemsDetail($_data){
		$_db= $this->getAdapter();
		$show = SHOW_IN_GRADE;
		try{
			$db_items = new Global_Model_DbTable_DbItems();
			$schooloption="";
			if (!empty($_data['items_id'])){
				$itemsinfo = $db_items->getDegreeById($_data['items_id'],$_data['items_type']);
				$schooloption = empty($itemsinfo['schoolOption'])?0:$itemsinfo['schoolOption'];
			}
			$sql="SELECT id FROM rms_itemsdetail WHERE items_id =".$_data['items_id'];
			$sql.=" AND items_type='".$_data['items_type']."'";
			$sql.=" AND title='".$_data['title']."'";
			$rs = $_db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
			$_arr=array(
					'items_id'		=> $_data['items_id'],
					'items_type'	=> $_data['items_type'],
					'is_autopayment'=> $_data['auto_payment'],
					'title'	 		=> $_data['title'],
					'title_en'	 	=> $_data['title_en'],
					'shortcut' 		=> $_data['shortcut'],
					'is_onepayment' => $_data['is_onepayment'],
					'note'   		=> $_data['note'],
					'ordering'    	=> $_data['ordering'],
					'schoolOption'  => $schooloption,
					'create_date' 	=> date("Y-m-d H:i:s"),
					'modify_date' 	=> date("Y-m-d H:i:s"),
					'status'		=> 1,
					'user_id'	  	=> $this->getUserId()
			);
			
			if($_data['items_type']==1){
				$_arr['total_score']=$_data['total_score'];
				$_arr['amount_subject']=$_data['amount_subject'];
				$_arr['max_average']=$_data['max_average'];
			}
			
			$this->_name = "rms_itemsdetail";
			$id =  $this->insert($_arr);
			
			
			if($_data['items_type']==1){
				$this->_name='rms_grade_subject_detail';
				if(!empty($_data['identity'])){
					$ids = explode(',', $_data['identity']);
					foreach ($ids as $i){
						$arr = array(
								'grade_id'		=> $id,
								'subject_id'	=> $_data['subject_study_'.$i],
								'max_score'		=>$_data['max_score'.$i],
								'amount_subject' =>$_data['amount_subject'.$i],
								
								'max_score_semester' =>$_data['max_score_semester'.$i],
								'amount_subject_sem' =>$_data['amount_subject_semester'.$i],
								'cut_score'		=>$_data['score_cut_'.$i],
								'subject_order'	=>$_data['subject_order'.$i],
								'date' 			=> date("Y-m-d"),
								'user_id'		=> $this->getUserId(),
								'status' 		=> 1,
						);
						
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
	public function updateItemsDetail($_data){
		$_db= $this->getAdapter();
		$show = SHOW_IN_GRADE;
		try{
			$db_items = new Global_Model_DbTable_DbItems();
			$schooloption="";
			if (!empty($_data['items_id'])){
				$itemsinfo = $db_items->getDegreeById($_data['items_id'],$_data['items_type']);
				$schooloption = empty($itemsinfo['schoolOption'])?0:$itemsinfo['schoolOption'];
			}
			$status = empty($_data['status'])?0:1;
			$_arr=array(
					'items_id'		=> $_data['items_id'],
					'items_type'	=> $_data['items_type'],
					'title'	  		=> $_data['title'],
					'title_en'	 	=> $_data['title_en'],
					'shortcut' 		=> $_data['shortcut'],
					'note'    		=> $_data['note'],
					'ordering'    	=> $_data['ordering'],
					'is_onepayment' => $_data['is_onepayment'],
					'is_autopayment'=> $_data['auto_payment'],
					'schoolOption'  => $schooloption,
					'modify_date' 	=> date("Y-m-d H:i:s"),
					'status'		=> $status,
					'user_id'	  	=> $this->getUserId()
			);
			
			if($_data['items_type']==1){
				$_arr['total_score']=$_data['total_score'];
				$_arr['amount_subject']=$_data['amount_subject'];
				$_arr['max_average']=$_data['max_average'];
			}
			
			$this->_name = "rms_itemsdetail";
			$id = $_data["id"];
			$where = $this->getAdapter()->quoteInto("id=?",$id);
			$this->update($_arr, $where);		

			if($_data['items_type']==1){
				$this->_name='rms_grade_subject_detail';
				$where = 'grade_id = '.$id;
				$this->delete($where);
				
				if(!empty($_data['identity'])){
					$ids = explode(',', $_data['identity']);
					foreach ($ids as $i){
						$arr = array(
								'grade_id'		=> $id,
								'subject_id'	=> $_data['subject_study_'.$i],
								'max_score'=>$_data['max_score'.$i],
								'cut_score'	=>$_data['score_cut_'.$i],
								'amount_subject' =>$_data['amount_subject'.$i],
								'amount_subject_sem' =>$_data['amount_subject_semester'.$i],
								'subject_order'	=>$_data['subject_order'.$i],
								'date' 			=> date("Y-m-d"),
								'user_id'		=> $this->getUserId(),
								'status' 		=> 1,
						);
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
			if(!empty($name)){
					$ss = 	explode(".", $name);
					$image_name = "product_".date("Y").date("m").date("d").time().".".end($ss);
					$tmp = $_FILES['images']['tmp_name'];
					if(move_uploaded_file($tmp, $part.$image_name)){
						$photo = $image_name;
					}
					else{
						$string = "Image Upload failed";
					}
			}
			
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$itemsCode = $dbgb->getItemsDetailCodeByItemsType(3);
			
			$_arr=array(
					'items_id'		=> $_data['items_id'],
					'items_type'	=> $_data['items_type'],
					'code'			=> $itemsCode,
					'title'	 	 	=> $_data['title'],
					'title_en'		=> $_data['title'],
					'note'    		=> $_data['note'],
					'product_type' 	=> $_data['product_type'],
					'isCountStock' 	=> $_data['isCountStock'],
					'is_onepayment' => $_data['is_onepayment'],
					'schoolOption'  => $schooloption,
					'images'   	 	=> $photo,
					'create_date' 	=> date("Y-m-d H:i:s"),
					'modify_date' 	=> date("Y-m-d H:i:s"),
					'status'		=> 1,
					'user_id'	 	=> $this->getUserId()
			);
			$this->_name = "rms_itemsdetail";
			$id =  $this->insert($_arr);

            if($_data['isCountStock']==0){
				$_arr = array(
					'pro_id'     =>$id,
					'branch_id'  =>$_data['branch_search'],
					'pro_qty'	 =>0,
					'price'		 =>$_data['price'],
					'price_set'  =>$_data['price'],
					'note'       =>'Not Count Stock',
				);
				$this->_name='rms_product_location';
				$this->insert($_arr);
			}
				
// 			$this->_name='rms_product_location';
// 			$ids = explode(',', $_data['identity']);
// 			foreach ($ids as $i){
// 				$_arr = array(
// 						'pro_id'=>$id,
// 						'branch_id'=>$_data['brand_name_'.$i],
// 						'pro_qty'=>$_data['qty_'.$i],
// 						'price'=>$_data['price_'.$i],
// 						'stock_alert'=>$_data['qty_alert_'.$i],
// 						'note'=>$_data['note_'.$i],
// 				);
// 				$this->insert($_arr);
// 			}
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
		}
	}

	public function AddInitizeqty($_data){
		$_db= $this->getAdapter();
		try{
			$db_items = new Global_Model_DbTable_DbItems();
			$this->_name='rms_product_location';
			$ids = explode(',', $_data['identity']);
			foreach ($ids as $i){
				$_arr = array(
						'branch_id'=>$_data['branch_id'],
						'pro_id'=>$_data['product_name_'.$i],
						'pro_qty'=>$_data['qty_'.$i],
						'costing'=>$_data['costing_'.$i],
						'price'=>$_data['price_'.$i],
						'price_set'=>$_data['price_set_'.$i],
						'stock_alert'=>$_data['qty_alert_'.$i],
						'note'=>$_data['note_'.$i],
						'date'=>$_data['create_date'],
						'user_id'=>$this->getUserId()
				);
				$this->insert($_arr);
			}
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
		}
	}

	
	public function updateProductPrice($_data){
		$_db= $this->getAdapter();
		try{
			
			//Update Product Location Price

			$this->_name='rms_product_location';
			$ids = explode(',', $_data['identity']);
			foreach ($ids as $i){
				$_arr = array(
						'price'=>$_data['sell_price_'.$i],
						'price_set'=>$_data['price_set_'.$i],
						'stock_alert'=>$_data['qty_alert_'.$i],
						'note'=>$_data['note_'.$i],
						'date' 	=> date("Y-m-d"),
						'user_id'=>$this->getUserId()
				);
			
				$where ="pro_id =".$_data['product_'.$i]." AND branch_id =".$_data['branch_search'];
				$this->update($_arr, $where);
			}

            // update Total Price Of ProductSet

			$this->_name='rms_itemsdetail';
			foreach ($ids as $j){
				$sql="SELECT pro_id FROM rms_product_setdetail WHERE subpro_id =".$_data['product_'.$j];
				$pro_set_detail = $_db->fetchAll($sql);
				if(!empty($pro_set_detail)){
					foreach ($pro_set_detail as $pro_set){

						$sql="SELECT * FROM rms_product_setdetail WHERE subpro_id =".$_data['product_'.$j]." AND pro_id =".$pro_set['pro_id'];
						$pro_price_detail = $_db->fetchRow($sql);

						if(!empty($pro_price_detail)){

							$oldprice  = $pro_price_detail['price']*$pro_price_detail['qty'];
							$newprice  = $_data['price_set_'.$j]*$pro_price_detail['qty'];

							$totalPrice = $newprice - $oldprice;

							$sql1="SELECT  price  FROM `rms_itemsdetail` WHERE items_type = 3 AND is_productseat = 1 AND id  =".$pro_set['pro_id'];
							$old_total_price = $_db->fetchOne($sql1);
                            
							$updateTotalPrice = $old_total_price + $totalPrice;

							$_arr = array(
								'price'=> $updateTotalPrice,
							);
							$where ="id =".$pro_set['pro_id'];
							$this->update($_arr, $where);
						}
					}
				}
               
			}

			 // Update productse Detail Price

			$this->_name='rms_product_setdetail';
			foreach ($ids as $k){
				$_arr = array(
						'price'=>$_data['price_set_'.$k],
				);
				$where ="subpro_id =".$_data['product_'.$k];
				$this->update($_arr, $where);
			}

		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
		}
	}

	function getAllProduct($search = '',$items_type=null){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$lang = $dbgb->currentlang();
		if($lang==1){// khmer
			$grade = "ide.title";
			$degree = "it.title";
		}else{ // English
			$grade = "ide.title_en";
			$degree = "it.title_en";
		}
		$result = $dbgb->getUserInfo();
		$level = $result["level"];
		$branch_id = $result["branch_id"];
		$string="";
		$location="";
		if ($level!=1){
			$string = $dbgb->getAccessPermission('pl.branch_id');
			//$location = $dbgb->getAccessPermission('(SELECT pl.branch_id FROM `rms_product_location` AS pl WHERE pl.pro_id = ide.id LIMIT 1 )');
			$location = $dbgb->getAccessBranchForProduct('(SELECT pl.branch_id FROM `rms_product_location` AS pl WHERE pl.pro_id = ide.id  )');
		}
		
		$sql = " SELECT ide.id,ide.code,$grade,
			(SELECT $degree FROM `rms_items` AS it WHERE it.id = ide.items_id LIMIT 1) AS degree,
			(SELECT SUM(pl.pro_qty) FROM `rms_product_location` AS pl WHERE pl.pro_id = ide.id  $string ) AS totalqty,
			(SELECT pl.price FROM `rms_product_location` AS pl WHERE pl.pro_id = ide.id  $string ) AS price,
			CASE    
			WHEN  ide.isCountStock = 0 THEN '".$tr->translate("NOT_COUNT_STOCK")."'
			WHEN  ide.isCountStock = 1 THEN '".$tr->translate("COUNT_STOCK")."'
			END AS stock_type,
			CASE    
			WHEN  ide.product_type = 1 THEN '".$tr->translate("PRODUCT_FOR_SELL")."'
			WHEN  ide.product_type = 2 THEN '".$tr->translate("OFFICE_MATERIAL")."'
			END AS product_type,
			CASE    
				WHEN  ide.is_onepayment = 0 THEN '".$tr->translate("IS_VALIDATE")."'
				WHEN  ide.is_onepayment = 1 THEN '".$tr->translate("ONE_PAYMENTONLY")."'
			END AS is_onepayment,
			ide.modify_date,
			(SELECT first_name FROM rms_users WHERE ide.user_id=id LIMIT 1 ) AS user_name
			  ";
		$sql.=$dbgb->caseStatusShowImage("ide.status");
		$sql.=" FROM `rms_itemsdetail` AS ide WHERE 1 AND ide.is_productseat = 0 ";
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
			//$where.= " AND ide.items_id  = ".$db->quote($search['items_search']);
			$arrCon = array(
				"categoryId" => $search['items_search'],
				"itemsType" => $items_type,
			);
			$condiction = $dbgb->getChildItems($arrCon);
			if (!empty($condiction)){
				$where.=" AND ide.items_id IN ($condiction)";
			}else{
				$where.=" AND ide.items_id=".$search['items_search'];
			}
		}
		if($search['status_search']>-1 AND $search['status_search']!=''){
			$where.= " AND status = ".$db->quote($search['status_search']);
		}
		if($search['is_onepayment']>-1 AND $search['is_onepayment']!=''){
			$where.= " AND is_onepayment = ".$db->quote($search['is_onepayment']);
		}
		if($search['product_type_search']>-1 AND $search['product_type_search']!=''){
			$where.= " AND ide.product_type = ".$db->quote($search['product_type_search']);
		}
		$orderby = " ORDER BY ide.ordering ASC, ide.id DESC ";
		return $db->fetchAll($sql.$where.$location.$orderby);
	}
	public function checkProductCate($id){
		$db = $this->getAdapter();
		$sql="SELECT s.id FROM rms_itemsdetail AS s WHERE s.items_type=3 AND s.items_id=$id ORDER BY s.id DESC LIMIT 1";
		return $db->fetchOne($sql);
	}
	public function checkProductLocation($id){
		$db = $this->getAdapter();
		$sql="SELECT s.id FROM rms_product_location AS s WHERE  s.pro_id=$id ORDER BY s.id DESC LIMIT 1";
		return $db->fetchOne($sql);
	}
	function deleteProduct($pro_id){

		$this->_name="rms_itemsdetail";
		$where ="id=".$pro_id;
		$this->delete($where);
		return $pro_id;
	}
	
	function getProductLocation($id){
		$db = $this->getAdapter();
		$sql = "
		SELECT pl.*,
		(SELECT s.`branch_namekh` FROM `rms_branch` AS s WHERE pl.`branch_id` = s.`br_id` LIMIT 1  ) AS branch_name
		FROM `rms_product_location` AS pl WHERE pl.pro_id = $id";
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$location = $dbgb->getAccessPermission('pl.`branch_id`');
		return $db->fetchAll($sql.$location);
	}

	function getProductInfoByLocationItem($_data = null){
		$db = $this->getAdapter();
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$lang = $dbgb->currentlang();
		if($lang==1){
			$title = "td.title";
		}elseif($lang==2){
			$title = "td.title_en";
		}
		$sql = "SELECT td.id, $title AS product_name,
			pl.price, pl.price_set, pl.stock_alert, pl.note
			FROM rms_itemsdetail AS td 
			JOIN rms_product_location AS pl ON td.id = pl.pro_id AND pl.branch_id= ".$_data['branch_id'];

		if (!empty($_data['product_id'])) {
			$sql .= " WHERE td.id= " . $_data['product_id'];
		}
		if (!empty($_data['itemsType'])) {
			$sql .= " AND td.items_type= " . $_data['itemsType'];
		}
		
		return $db->fetchRow($sql);
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
					'title_en'=> $_data['title'],
					'note'    => $_data['note'],
					'isCountStock' 	=> $_data['isCountStock'],
					'product_type' => $_data['product_type'],
					'is_onepayment' => $_data['is_onepayment'],
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

			if ($_data['isCountStock']==0){
				$_arr = array(
					'pro_id'     =>$id,
					'branch_id'  =>$_data['branch_search'],
					'price'		 =>$_data['price'],
					'price_set'  =>$_data['price'],
					'note'       =>'Not Count Stock',
				);
				$this->_name='rms_product_location';
				$id =  $_data["id"];
				$where = $_db->quoteInto("pro_id=?", $id);
				$this->update($_arr, $where);
					
				}
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
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
					'branch_id'=> $_data['branch_search'],
					'items_id'=> $_data['items_id'],
					'items_type'=> $_data['items_type'],
					'code'=> $_data['code'],
					'price'=> $_data['price'],
					'title'	  => $_data['title'],
					'title_en'=> $_data['title'],
					'note'    => $_data['note'],
					'is_onepayment' => $_data['is_onepayment'],
					'product_type' => 1,
					'is_productseat' => 1,
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
						'price'=>$_data['sell_price_'.$i],
						'remark'=>$_data['note_'.$i],
					);
					$this->insert($_arrss);
				}
			}
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
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
				    'branch_id'=> $_data['branch_search'],
					'items_id'=> $_data['items_id'],
					'items_type'=> $_data['items_type'],
					'code'=> $_data['code'],
					'price'=> $_data['price'],
					'title'	  => $_data['title'],
					'title_en'=> $_data['title'],
					'note'    => $_data['note'],
					'is_onepayment' => $_data['is_onepayment'],
					'product_type' => 1,
					'is_productseat' => 1,
					'schoolOption'    => $schooloption,
					'modify_date' => date("Y-m-d H:i:s"),
					'status'=> $_data['status'],
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
							'price'=>$_data['sell_price_'.$i],
							'qty'=>$_data['qty_'.$i],
							'remark'=>$_data['note_'.$i],
						);
						$where =" id =".$_data['detailid'.$i];
						$this->update($_arrss, $where);
					}else{
						$_arrss = array(
							'pro_id'=>$id,
							'subpro_id'=>$_data['product_'.$i],
							'price'=>$_data['sell_price_'.$i],
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
		}
	}
	function getAllProductSet($search = '',$items_type=null){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
	
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$lang = $dbgb->currentlang();
		if($lang==1){// khmer
			$grade = "ide.title";
			$degree = "it.title";
		}else{ // English
			$grade = "ide.title_en";
			$degree = "it.title_en";
		}
		$result = $dbgb->getUserInfo();
		$level = $result["level"];
		$branch_id = $result["branch_id"];
		$string="";
		$location="";
		$sql = "SELECT ide.id,ide.code,$grade,
			(SELECT $degree FROM `rms_items` AS it WHERE it.id = ide.items_id LIMIT 1) AS degree,
			ide.price,
			CASE    
				WHEN  ide.is_onepayment = 0 THEN '".$tr->translate("IS_VALIDATE")."'
				WHEN  ide.is_onepayment = 1 THEN '".$tr->translate("ONE_PAYMENTONLY")."'
			END AS is_onepayment,
			ide.modify_date,
			(SELECT CONCAT(first_name) FROM rms_users WHERE ide.user_id=id LIMIT 1 ) AS user_name
			  ";
		$sql.=$dbgb->caseStatusShowImage("ide.status");
		$sql.=" FROM `rms_itemsdetail` AS ide WHERE 1 AND ide.is_productseat = 1 ";
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
			$s_where[] = " ide.code LIKE '%{$s_search}%'";
			$s_where[] = " ide.price LIKE '%{$s_search}%'";
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['items_search'])){
		$where.= " AND ide.items_id  = ".$db->quote($search['items_search']);
		}
		if($search['status_search']>-1 AND $search['status_search']!=''){
			$where.= " AND status = ".$db->quote($search['status_search']);
		}
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
		$_db  = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if($lang==1){// khmer
			$grade = "i.title";
			$degree = "it.title";
		}else{ // English
			$grade = "i.title_en";
			$degree = "it.title_en";
		}
		$sql="SELECT i.id,
		COALESCE($grade,' (',(SELECT $degree FROM `rms_items` AS it WHERE it.id = i.items_id LIMIT 1),')') AS name
		FROM `rms_itemsdetail` AS i
		WHERE i.status =1 AND (i.title_en!='' OR i.title!='') AND i.items_type=3 AND i.is_productseat=0  ";
		
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
			$sql .=' AND i.schoolOption IN ( '.$user['schoolOption'].')';
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
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
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
	function getAllProductInBranch(){
		$db = $this->getAdapter();
		$_db  = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if($lang==1){// khmer
			$pro_name = "i.title";
		}else{ // English
			$pro_name = "i.title_en";
		}
		$sql="SELECT i.id,
			$pro_name  AS name
		FROM `rms_itemsdetail` AS i,
			rms_product_location AS pl
			WHERE i.id=pl.pro_id 
				AND i.status =1 
				AND i.items_type=3  ";
		$sql.=" ORDER BY i.id DESC ";
		return $db->fetchAll($sql);
	}
	
	function getAllProductsNormalByAnyOption($data=array()){
		$db = $this->getAdapter();
		$_db  = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if($lang==1){// khmer
			$grade = "i.title";
			$degree = "it.title";
		}else{ // English
			$grade = "i.title_en";
			$degree = "it.title_en";
		}
		$sql="SELECT i.id,
		CONCAT($grade,' (',(SELECT $degree FROM `rms_items` AS it WHERE it.id = i.items_id LIMIT 1),')') AS name
		FROM `rms_itemsdetail` AS i,
		rms_product_location AS pl
		WHERE i.id=pl.pro_id 
			AND i.status =1 
			AND i.items_type=3 
			AND i.is_productseat=0  ";
		$product_type = empty($data['product_type'])?0:$data['product_type'];
		if(!empty($product_type)){//check type product sale or office
			$sql.= " AND i.product_type = ".$db->quote($product_type);
		}
		$product_type = empty($data['product_type'])?0:$data['product_type'];
		if(!empty($product_type)){//check type product sale or office
			$sql.= " AND i.product_type = ".$db->quote($product_type);
		}
		if(!empty($data['branch_id'])){//check type product sale or office
			$sql.= " AND pl.branch_id = ".$data['branch_id'];
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
			//$sql .=' AND '.$user['schoolOption'].' IN (i.schoolOption)';
			$sql .=' AND i.schoolOption IN ( '.$user['schoolOption'].')';
		}
		$sql.=" GROUP BY pl.pro_id ORDER BY i.items_id ASC, i.ordering ASC";
		return $db->fetchAll($sql);
	}
}