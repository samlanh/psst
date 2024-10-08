<?php

class Stock_Model_DbTable_DbAdjustStock extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_request_order';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    	 
    }
    function getAllAdjustStock($search=null){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$branch = $dbp->getBranchDisplay();
    	$sql="SELECT id,
    			(SELECT b.$branch FROM `rms_branch` AS b  WHERE b.br_id = branch_id LIMIT 1) AS branch_name,
    			adjust_no,request_name,note,request_date,
    		   (SELECT SUM(rd.qty_after) FROM rms_adjuststock_detail AS rd WHERE rd.adjuststock_id=rms_adjuststock.id LIMIT 1)AS total_qty,
			   (SELECT first_name FROM rms_users WHERE id=rms_adjuststock.user_id LIMIT 1) AS user_name
    		 ";
    	$sql.=$dbp->caseStatusShowImage("status");
    	$sql.=" FROM rms_adjuststock WHERE 1 ";
    	
    	$where="";
    	$from_date =(empty($search['start_date']))? '1': " request_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " request_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search = str_replace(' ', '', addslashes(trim($search['title'])));
    		$s_where[]= " REPLACE(adjust_no,' ','') LIKE '%{$s_search}%'";
    		$s_where[]="  REPLACE(request_name,' ','') LIKE '%{$s_search}%'";
    		$s_where[]= " REPLACE(note,' ','') LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND status=".$search['status_search'];
    	}
    	if($search['branch_id']>0 and !empty($search['branch_id'])){
    		$where.=" AND branch_id=".$search['branch_id'];
    	}
    	
    	$sql.=$dbp->getAccessPermission('branch_id');
    	$order=" ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }

	function getAllProductLocattion($search=null){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$branch = $dbp->getBranchDisplay();
    	$sql="SELECT id, 
	    	(SELECT b.$branch FROM `rms_branch` AS b  WHERE b.br_id = branch_id LIMIT 1) AS branch_name,
			(SELECT t.code FROM `rms_itemsdetail` AS t  WHERE t.id = pro_id LIMIT 1) AS product_code,
	    	(SELECT t.title FROM `rms_itemsdetail` AS t  WHERE t.id = pro_id LIMIT 1) AS product_name,
    		pro_qty,costing,price,price_set,

			CASE    
			WHEN  (SELECT t.product_type FROM `rms_itemsdetail` AS t  WHERE t.id = pro_id LIMIT 1)  = 1 THEN '".$tr->translate("PRODUCT_FOR_SELL")."'
			WHEN  (SELECT t.product_type FROM `rms_itemsdetail` AS t  WHERE t.id = pro_id LIMIT 1)  = 2 THEN '".$tr->translate("OFFICE_MATERIAL")."'
			END AS product_type,

    		date,
			(SELECT (first_name) FROM rms_users WHERE user_id=id LIMIT 1 ) AS byuser,
    		status ";
    	$sql.=$dbp->caseStatusShowImage("status");
    	$sql.="  FROM `rms_product_location` WHERE pro_id IS NOT NULL ";
    	
    	$where="";
    	$from_date =(empty($search['start_date']))? '1': "date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search = str_replace(' ', '', addslashes(trim($search['title'])));
    		$s_where[]="(SELECT t.id FROM `rms_itemsdetail` AS t  WHERE t.id = pro_id AND t.title LIKE '%{$s_search}%')";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND status=".$search['status_search'];
    	}
    	if($search['branch_id']>0 and !empty($search['branch_id'])){
    		$where.=" AND branch_id=".$search['branch_id'];
    	}
		if($search['product_type']>0 and !empty($search['product_type'])){
    		$where.=" AND (SELECT t.product_type FROM `rms_itemsdetail` AS t WHERE t.id = pro_id LIMIT 1) =".$search['product_type'];
    	}
    	$sql.=$dbp->getAccessPermission('branch_id');
    	$order=" ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }

    public function getProQtyByLocation($branch_id,$pro_id){
    	$db=$this->getAdapter();
    	$sql=" SELECT pl.id,pl.pro_id,pl.pro_qty  
    		   FROM 
    		   		rms_product_location AS pl,
    		   		rms_itemsdetail AS p
			   WHERE pl.pro_id=$pro_id AND pl.branch_id=$branch_id
				 AND   p.id=pl.pro_id ";
    	$row = $db->fetchRow($sql);
    	if(empty($row)){
    		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    		$userName=$session_user->user_name;
    		$GetUserId= $session_user->user_id;
    		$array = array(
    				'pro_id'	=>$pro_id,
    				'branch_id'	=>$branch_id,
    				'pro_qty'	=>0,
    				'date'		=>	date("Y-m-d"),
    				'status'	=>	1,
    				"user_id"   =>  $GetUserId,
    		);
    		$this->_name="rms_product_location";
    		$this->insert($array);
    		$sql=" SELECT pl.pro_id,pl.pro_qty  FROM rms_product_location AS pl,rms_itemsdetail AS p
    		WHERE pl.pro_id=$pro_id AND pl.branch_id=$branch_id
    		AND   p.id=pl.pro_id ";
    		return $row = $db->fetchRow($sql);
    	}else{
    		return $row;
    	}
    }
    
    public function addAdjustStock($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$itemsCode = $this->getAjustCode($data["branch"]);
			$arr=array(
					"branch_id"    	=> 	$data["branch"],
					"adjust_no"    	=> 	$itemsCode,
					"request_name"  => 	$data["request_name"],
					"note"     		=> 	$data["note"],
					"request_date"  => 	date("Y-m-d"),
					"create_date"   => 	date("Y-m-d H:i:s"),
					"user_id"       => 	$this->getUserId(),
					"status"        => 1,
			);
			$this->_name="rms_adjuststock";
			$adjuststock_id = $this->insert($arr); 
			unset($arr);

			if($data['identity']!=""){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$data_item= array(
							'adjuststock_id'=>  $adjuststock_id,
							'branch_id'		=> 	$data['branch_id_'.$i],
							'pro_id'		=>  $data['product_name_'.$i],
							'qty_befor'		=> 	$data['curr_qty'.$i],
							'qty_after'		=>  $data['request_qty'.$i],
							'difference'	=> 	$data['spacing_qty'.$i],
							'create_date'	=>  date("Y-m-d H:i:s"),
							'remark'  		=> 	$data['note_'.$i],
							'last_usermod'	=> 	$this->getUserId(),
							'status'		=> 	1,
					);
					$this->_name='rms_adjuststock_detail';
					$this->insert($data_item);
					$rows=$this->getProQtyByLocation($data['branch_id_'.$i], $data['product_name_'.$i]); 
					if($rows){
						$datatostock= array(
							'pro_qty' 	=>$data['request_qty'.$i],
							'date'		=> date("Y-m-d H:i:s"),
							'user_id'	=> $this->getUserId()
						);
						$this->_name="rms_product_location";
						$where=" id = ".$rows['id'];
						$this->update($datatostock, $where);
					}
				 }
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
			Application_Form_FrmMessage::message('INSERT_FAIL');
		}
	}
    
 	function updateAdjustStock($data){
 		$db = $this->getAdapter();
 		$db->beginTransaction();
 		try{
 			//sum qty to current qty in stock
 			$oldrs = $this->getAdjustStockById($data['id']);
 			if($oldrs['status']==1){
	 			$rows=$this->getAdjustStockDetail($data['id']);
	 			if(!empty($rows))foreach ($rows AS $row){
	 				$qty=$this->getProQtyByLocation($row['branch_id'], $row['pro_id']);
	 				if($qty){
	 					$curr= array(
	 						'pro_qty' 	=>$qty['pro_qty']+$row['difference'],
	 						'date'		=> date("Y-m-d H:i:s"),
	 						'user_id'	=> $this->getUserId()
	 					);
	 					$this->_name="rms_product_location";
	 					$where=" id = ".$qty['id'];
	 					$this->update($curr, $where);
	 				}
	 			}
 			}
 			$arr=array(
 				"adjust_no"    	=> 	$data["adjust_no"],
 				"request_name"  => 	$data["request_name"],
 				"note"     		=> 	$data["note"],
 				"request_date"  => 	date("Y-m-d"),
 				"create_date"   => 	date("Y-m-d H:i:s"),
 				"user_id"       => 	$this->getUserId(),
 				"status"        => 	$data['status'],
 			);
 			$this->_name="rms_adjuststock";
 			$where="id=".$data['id'];
 			$this->update($arr, $where);
 			unset($arr);
 			
 			$this->_name='rms_adjuststock_detail';
 			$where="adjuststock_id=".$data['id'];
 			$this->delete($where);
 			
 			if($data['identity']!=""){
 				$ids=explode(',',$data['identity']);
 				foreach ($ids as $i)
 				{
 					$data_item= array(
 							'adjuststock_id'=>  $data['id'],
 							'branch_id'		=> 	$data['branch_id_'.$i],
 							'pro_id'		=>  $data['product_name_'.$i],
 							'qty_befor'		=> 	$data['curr_qty'.$i],
 							'qty_after'		=>  $data['request_qty'.$i],
 							'difference'	=> 	$data['spacing_qty'.$i],
 							'create_date'	=>  date("Y-m-d H:i:s"),
 							'remark'  		=> 	$data['note_'.$i],
 							'last_usermod'	=> 	$this->getUserId(),
 							'status'		=> 	$data['status'],
 					);
 					$this->_name='rms_adjuststock_detail';
 					$this->insert($data_item);
 					
 					if($data['status']==1){
	 					$rows=$this->getProQtyByLocation($data['branch_id_'.$i], $data['product_name_'.$i]);
	 					if($rows){
	 						$datatostock= array(
	 								'pro_qty' 	=>$data['request_qty'.$i],
	 								'date'		=> date("Y-m-d H:i:s"),
	 								'user_id'	=> $this->getUserId()
	 						);
	 						$this->_name="rms_product_location";
	 						$where=" id = ".$rows['id'];
	 						$this->update($datatostock, $where);
	 					}
 					}
 				}
 			}
 			$db->commit();
 		}catch(Exception $e){
 			$db->rollBack();
 			Application_Form_FrmMessage::message('INSERT_FAIL');
 			$err =$e->getMessage();
 			Application_Model_DbTable_DbUserLog::writeMessageError($err);
 		}
 	}
	function getAdjustStockById($id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_adjuststock WHERE id=$id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
		return $db->fetchRow($sql);
	}
	
	function getAdjustStockDetail($id){
		$db=$this->getAdapter();
		$sql="SELECT ad.branch_id,(SELECT p.title FROM rms_itemsdetail AS p WHERE p.id=ad.pro_id ) AS pro_name,
			    ad.pro_id,ad.qty_befor,ad.qty_after,ad.difference,ad.remark FROM rms_adjuststock_detail  AS ad
				WHERE ad.adjuststock_id=$id";
		return $db->fetchAll($sql);
	}
	
    function getProductNames(){
    	$db=$this->getAdapter();
    	$sql="SELECT p.id,pl.branch_id,p.title AS `name` FROM rms_itemsdetail AS p,rms_product_location AS pl
 				WHERE p.id=pl.pro_id AND p.status=1  ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	$sql.=" GROUP BY p.id ORDER BY id DESC ";
        $rows=$db->fetchAll($sql);
        
        array_unshift($rows,array('id' => '',"name"=>"Please select product name"));
        $options = '';
        if(!empty($rows))foreach($rows as $value){
        	$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
        }
        return $options;
    }
    


    function getAjustCode($branch_id=null){
    	$db = $this->getAdapter();
    	$sql="SELECT id FROM rms_adjuststock WHERE STATUS=1 ";
    	$pre="";
    	if (!empty($branch_id)){
    		$sql.=" AND branch_id=".$branch_id;
    		$_dbgb = new Application_Model_DbTable_DbGlobal();
    		$pre.= $_dbgb->getPrefixCode($branch_id);//by branch
    	}
    	$sql.=" ORDER BY id DESC LIMIT 1";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$pre.='RQ-';
    	for($i = $acc_no;$i<4;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
     
   
    
    function getAllBranch(){
    	$db = $this->getAdapter();
    	$sql="select br_id as id, CONCAT(branch_nameen) as name from rms_branch where status=1 ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('br_id');
    	return $db->fetchAll($sql);
    }
    
   
    
    function getProductQty($location,$pro_id){
    	$db=$this->getAdapter();
    	$sql="SELECT pl.pro_qty FROM rms_product AS p,rms_product_location AS pl
		  WHERE p.id=pl.pro_id
		  AND pl.pro_id=$pro_id 
		  AND pl.branch_id=$location";
    	return $db->fetchOne($sql);
    }
    
    function getAllProductBybranch($branch_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT p.id,pl.branch_id,p.pro_name AS `name` FROM rms_product AS p,rms_product_location AS pl
		    	WHERE p.id=pl.pro_id AND p.status=1
		    	AND p.pro_type=2 AND pl.branch_id=".$branch_id;
    	$order=' ORDER BY p.id DESC';
    	return $db->fetchAll($sql.$order);
    }

	function getAllAdjusted($data){
    	$db = $this->getAdapter();
    	$this->_name='rms_adjuststock';
    	
    	$sql=" SELECT 
    		id,
    		DATE_FORMAT(create_date,'%d-%m-%Y') AS name
    	FROM $this->_name WHERE 1 ";
    	
    	if(isset($data['is_approved'])){
    		$sql.=" AND is_approved=".$data['is_approved'];
    	}
		
    	if(isset($data['branch_id'])){
    		$sql.=" AND branch_id=".$data['branch_id'];
    	}
	
    	if(isset($data['is_closed'])){
    		$sql.=" AND is_closed=".$data['is_closed'];
    	}
		
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	
    	return $db->fetchAll($sql);
    }
}