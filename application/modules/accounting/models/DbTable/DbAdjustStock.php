<?php

class Accounting_Model_DbTable_DbAdjustStock extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_request_order';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    	 
    }
    function getAllRequest($search=null){
    	$db = $this->getAdapter();
    	$sql="SELECT id,request_no,request_name,purpose,request_date,
		       (SELECT SUM(rd.qty_request) FROM rms_request_orderdetail AS rd WHERE rd.request_id=rms_request_order.id)AS total_qty,
		       (SELECT name_en FROM rms_view WHERE key_code=rms_request_order.status AND rms_view.type=1 LIMIT 1) AS `status`,
			   (SELECT first_name FROM rms_users WHERE id=rms_request_order.user_id LIMIT 1) AS user_name
			   FROM rms_request_order WHERE 1";
    	$where="";
    	$from_date =(empty($search['start_date']))? '1': " request_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " request_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search = str_replace(' ', '', addslashes(trim($search['title'])));
    		$s_where[]= " REPLACE(request_no,' ','') LIKE '%{$s_search}%'";
    		$s_where[]="  REPLACE(request_name,' ','') LIKE '%{$s_search}%'";
    		$s_where[]= " REPLACE(purpose,' ','') LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND status=".$search['status_search'];
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	$order=" ORDER BY id DESC";
    	//echo $where;
    	return $db->fetchAll($sql.$where.$order);
    }

    public function getProQtyByLocation($branch_id,$pro_id){
    	$db=$this->getAdapter();
    	$sql=" SELECT pl.id,pl.pro_id,pl.pro_qty  FROM rms_product_location AS pl,rms_product AS p
				 WHERE pl.pro_id=$pro_id AND pl.brand_id=$branch_id
				 AND   p.id=pl.pro_id ";
    	$row = $db->fetchRow($sql);
    	if(empty($row)){
    		$session_user=new Zend_Session_Namespace('authstu');
    		$userName=$session_user->user_name;
    		$GetUserId= $session_user->user_id;
    		$array = array(
    				'pro_id'	=>$pro_id,
    				'brand_id'	=>$branch_id,
    				'pro_qty'	=>0,
    				'date'		=>	date("Y-m-d"),
    				'status'	=>	1,
    				"user_id"   =>  $GetUserId,
    		);
    		$this->_name="rms_product_location";
    		$this->insert($array);
    		$sql=" SELECT pl.pro_id,pl.pro_qty  FROM rms_product_location AS pl,rms_product AS p
    		WHERE pl.pro_id=$pro_id AND pl.brand_id=$branch_id
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
			$arr=array(
					"adjust_no"    	=> 	$data["adjust_no"],
					"request_name"  => 	$data["request_name"],
					"note"     		=> 	$data["note"],
					"request_date"  => 	date("Y-m-d"),
					"create_date"   => 	date("Y-m-d"),
					"user_id"       => 	$this->getUserId(),
					"status"        => 	$data['status'],
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
							'create_date'	=>  date("Y-m-d"),
							'remark'  		=> 	$data['note_'.$i],
							'last_usermod'	=> 	$this->getUserId(),
							'status'		=> 	$data['status'],
					);
					$this->_name='rms_adjuststock_detail';
					$this->insert($data_item);
					$rows=$this->getProQtyByLocation($data['branch_id_'.$i], $data['product_name_'.$i]); 
					if($rows){
							$datatostock= array(
									'pro_qty' 	=>$data['request_qty'.$i],
									'date'		=> date("Y-m-d"),
									'user_id'	=> $this->getUserId()
							);
							$this->_name="rms_product_location";
							$where=" id = ".$rows['id'];
							$this->update($datatostock, $where);
					}else{
						
					}
				 }
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
    
 	function updateRequest($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$rows=$this->getRequestDetail($data['id']);
			if(!empty($rows)){
				foreach ($rows as $row){
					$qty=$this->getProQtyByLocation($row['branch_id'], $row['pro_id']); 
					//print_r($qty);exit();
					if($qty){
						$datat= array(
								'pro_qty' 	=> $qty["pro_qty"]+$row['qty_request'],
								'date'		=> date("Y-m-d"),
								'user_id'	=> $this->getUserId()
						);
						$this->_name="rms_product_location";
						$where=" id = ".$qty['id'];
						$this->update($datat, $where);
					}else{
					
					}
				}
			}
			$arr=array(
					"request_no"    => 	$data["request_no"],
					"request_name"  => 	$data["request_name"],
					"purpose"     	=> 	$data["purpose"],
					"request_date"  => 	date("Y-m-d",strtotime($data['request_date'])),
					"create_date"   => 	date("Y-m-d"),
					"user_id"       => 	$this->getUserId(),
					"status"        => 	$data['status'],
			);
			$this->_name="rms_request_order";
			$where="id=".$data['id'];
			$this->update($arr, $where);
			unset($arr);
			$this->_name='rms_request_orderdetail';
			$where=" request_id=".$data['id'];
			$this->delete($where);
			
			if($data['identity']!=""){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$data_item= array(
							'request_id'	=>  $data['id'],
							'branch_id'		=> 	$data['branch_id_'.$i],
							'pro_id'		=>  $data['product_name_'.$i],
							'qty_curr'		=> 	$data['curr_qty'.$i],
							'qty_request'	=>  $data['request_qty'.$i],
							'pro_type'		=> 2,//type product cut stock later
							'create_date'	=>  	date("Y-m-d"),
							'remark'  		=> 	$data['note_'.$i],
							'user_id'		=> 	$this->getUserId(),
							'status'		=> 	$data['status'],
					);
					$this->_name='rms_request_orderdetail';
					$this->insert($data_item);
					$rows=$this->getProQtyByLocation($data['branch_id_'.$i], $data['product_name_'.$i], $data['request_qty'.$i]); 
					if($rows){
							$datatostock= array(
									'pro_qty' 	=> $rows["pro_qty"]-$data['request_qty'.$i],
									'date'		=> date("Y-m-d"),
									'user_id'	=> $this->getUserId()
							);
							$this->_name="rms_product_location";
							$where=" id = ".$rows['id'];
							$this->update($datatostock, $where);
					}else{
						
					}
				 }
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	
	function getRequestById($id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_request_order WHERE id=$id";
		return $db->fetchRow($sql);
	}
	
	function getRequestDetail($id){
		$db=$this->getAdapter();
		$sql="SELECT branch_id,(SELECT p.pro_name FROM rms_product AS p WHERE p.id=pro_id ) AS pro_name,
			    pro_id,qty_curr,qty_request,remark FROM rms_request_orderdetail 
				WHERE request_id=$id";
		return $db->fetchAll($sql);
	}
	
    function getProductNames(){
    	$db=$this->getAdapter();
    	$sql="SELECT p.id,pl.brand_id,p.pro_name AS `name` FROM rms_product AS p,rms_product_location AS pl
 				WHERE p.id=pl.pro_id AND p.status=1  ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('brand_id');
    	$sql.=" GROUP BY p.id ORDER BY id DESC ";
        $rows=$db->fetchAll($sql);
        
        array_unshift($rows,array('id' => '',"name"=>"Please select product name"));
        $options = '';
        if(!empty($rows))foreach($rows as $value){
        	$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
        }
        return $options;
    }
    
    function getProductName(){
    	$db=$this->getAdapter();
    	$sql="SELECT p.id,pl.brand_id,p.pro_name AS `name` FROM rms_product AS p,rms_product_location AS pl
    	WHERE p.id=pl.pro_id AND p.status=1  ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('brand_id');
    	$sql.=" GROUP BY p.id ORDER BY id DESC ";
    	return $db->fetchAll($sql);
    }
    
    function getProducCutStockLater(){
    	$db=$this->getAdapter();
    	$sql="SELECT p.id,pl.brand_id,p.pro_name AS `name` FROM rms_product AS p,rms_product_location AS pl
		    	WHERE p.id=pl.pro_id AND p.status=1
		    	AND p.pro_type=2";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('brand_id');
    	$sql.=" GROUP BY p.id ORDER BY id DESC ";
    	return $db->fetchAll($sql);
    }

    function getAjustCode(){
    	$db = $this->getAdapter();
    	$sql="SELECT id FROM rms_adjuststock WHERE STATUS=1 ORDER BY id DESC LIMIT 1";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$pre='RQ-';
    	for($i = $acc_no;$i<4;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
     
    function getProductById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM rms_product WHERE id=$id";
    	return $db->fetchRow($sql);
    }
    
    function getAllBranch(){
    	$db = $this->getAdapter();
    	$sql="select br_id as id, CONCAT(branch_nameen) as name from rms_branch where status=1 ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('br_id');
    	return $db->fetchAll($sql);
    }
    
    public function ajaxAddProduct($data){
    	$db = $this->getAdapter();
    	$session_user=new Zend_Session_Namespace('authstu');
    	$userName=$session_user->user_name;
    	$GetUserId= $session_user->user_id;
    	$_arr = array(
    			'pro_name'	=>$data['product_name'],
    			'pro_code'	=>$data['product_code'],
    			'cat_id'	=>$data['category_id'],
    			'pro_price'	=>$data['pro_price'],
    			'pro_des'	=>$data['descript'],
    			'pro_type'	=>$data['pro_type'],
    			'status'	=>$data['p_status'],
    			'date'		=>date("Y-m-d"),
    			'user_id'	=>$this->getUserId()
    	);
    	$this->_name = "rms_product";
    	$pro_id = $this->insert($_arr);
    	$_arr = array(
    			'pro_id'=>$pro_id,
    			'brand_id'=>$data['location_id'],
    			'pro_qty'=>0,
    			'total_amount'=>0,
    			'note'=>'',
    	);
    	$this->_name='rms_product_location';
    	$this->insert($_arr);
    	$array = array(
    			'ser_cate_id'	=>$pro_id,
    			'title'			=>$data['product_name'],
    			'description'	=>$data['descript'],
    			'price'			=>$data['pro_price'],
    			'status'		=>1,
    			'create_date'	=>date("Y-m-d H:i:s"),
    			'user_id'		=>$this->getUserId(),
    			'type'			=>1, // type=1 => product
    			'pro_type'		=>$data['pro_type'], // 1=cut stock , 2=cut stock later
    	);
    	$this->_name='rms_program_name';
    	$this->insert($array);
    	return $pro_id;
    }
    
    function getProductQty($location,$pro_id){
    	$db=$this->getAdapter();
    	$sql="SELECT pl.pro_qty FROM rms_product AS p,rms_product_location AS pl
		  WHERE p.id=pl.pro_id
		  AND pl.pro_id=$pro_id 
		  AND pl.brand_id=$location";
    	return $db->fetchOne($sql);
    }
    
    function getAllProductBybranch($branch_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT p.id,pl.brand_id,p.pro_name AS `name` FROM rms_product AS p,rms_product_location AS pl
		    	WHERE p.id=pl.pro_id AND p.status=1
		    	AND p.pro_type=2 AND pl.brand_id=".$branch_id;
    	$order=' ORDER BY p.id DESC';
    	return $db->fetchAll($sql.$order);
    }
}