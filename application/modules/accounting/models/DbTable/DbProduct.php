<?php

class Accounting_Model_DbTable_DbProduct extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_product';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    function getAllProduct($search=null){
    	$db = $this->getAdapter();
    	$sql=" SELECT p.id,p.pro_code,
			 (SELECT CONCAT(branch_nameen) FROM rms_branch WHERE rms_branch.br_id=pl.brand_id) AS branch_name,
				p.pro_name,(SELECT cat.name_kh FROM rms_pro_category AS cat WHERE cat.id=p.cat_id) As cat_id,
			    p.pro_price, 
				pl.pro_qty,pl.total_amount,p.date,p.status
				FROM rms_product AS p,rms_product_location AS pl
				WHERE p.id=pl.pro_id ";
    	
    	$where="";
    	$from_date =(empty($search['start_date']))? '1': " p.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " p.date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " p.pro_code LIKE '%{$s_search}%'";
    		$s_where[]=" p.pro_name LIKE '%{$s_search}%'";
    		$s_where[]= " p.pro_size LIKE '%{$s_search}%'";
    		$s_where[]= " p.pro_size LIKE '%{$s_search}%'";
    		$s_where[]= " p.pro_price LIKE '%{$s_search}%'";
    		$s_where[]= " pl.pro_qty LIKE '%{$s_search}%'";
    		$s_where[]= " pl.total_amount LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['location'])){
    		$where.=" AND pl.brand_id=".$search['location'];
    	}
    	
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND p.status=".$search['status_search'];
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('brand_id');
    	
    	$order=" ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
    public function addProduct($_data){
    	//print_r($_data);exit();
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
	    		$_arr = array(
	    				'pro_name'=>$_data['product_name'],
	    				'pro_code'=>$_data['product_code'],
	    				'cat_id'=>$_data['category_id'],
	    				'pro_price'=>$_data['pro_price'],
	    				'pro_des'=>$_data['descript'],
	    				'status'=>$_data['status'],
	    				'date'=>date("Y-m-d"),
	    				'user_id'=>$this->getUserId()
	    				);
	    		$pro_id = $this->insert($_arr);
	    		
	    		$this->_name='rms_product_location';
	    		$ids = explode(',', $_data['identity']);
	    		$one_price=$_data['pro_price'];
	    		foreach ($ids as $i){
	    				$_arr = array(
	    						'pro_id'=>$pro_id,
	    						'brand_id'=>$_data['brand_name_'.$i],
	    						'pro_qty'=>$_data['qty_'.$i],
	    						'total_amount'=>$_data['qty_'.$i]*$one_price,
	    						'note'=>$_data['note_'.$i], 
	    				);
	    				$this->insert($_arr);
	    		}
	    		
	    		$this->_name='rms_program_name';
	    		$array = array(
	    				'ser_cate_id'	=>$pro_id,
	    				'title'			=>$_data['product_name'],
	    				'description'	=>$_data['descript'],
	    				'price'			=>$_data['pro_price'],
	    				'status'		=>1,
	    				'create_date'	=>date("Y-m-d H:i:s"),
	    				'user_id'		=>$this->getUserId(),
	    				'type'			=>1,
	    				);
	    		$this->insert($array);
	    		
    			$db->commit();
		   	}catch (Exception $e){
		   		echo $e->getMessage();
		   		$db->rollBack();
		   	}
    }
    
    function checkProductExist($pro_id){
    	$db = $this->getAdapter();
    	$sql = "select * from rms_program_name where ser_cate_id=$pro_id and type=1";
    	return $db->fetchRow($sql);
    }
    
	public function updateProduct($_data){
    	//print_r($_data);exit();
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
	    		$_arr = array(
	    				'pro_name'=>$_data['product_name'],
	    				'pro_code'=>$_data['product_code'],
	    				'cat_id'=>$_data['category_id'],
	    				'pro_price'=>$_data['pro_price'],
	    				'pro_des'=>$_data['descript'],
	    				'status'=>$_data['status'],
	    				'date'=>date("Y-m-d"),
	    				'user_id'=>$this->getUserId()
	    				);
	    		$where=" id=".$_data['id'];
	    		$this->update($_arr, $where);
	    		$one_price=$_data['pro_price'];
	    		$this->_name='rms_product_location';
	    		$where=" pro_id= ".$_data['id'];
	    		$this->delete($where);
	    		$ids = explode(',', $_data['identity']);
	    		foreach ($ids as $i){
	    				$_arr = array(
	    						'pro_id'=>$_data['id'],
	    						'brand_id'=>$_data['brand_name_'.$i],
	    						'pro_qty'=>$_data['qty_'.$i],
	    						'total_amount'=>$_data['qty_'.$i]*$one_price,
	    						'note'=>$_data['note_'.$i], 
	    				);
	    				$this->insert($_arr);
	    		}
	    		
	    		$result = $this->checkProductExist($_data['id']);
	    		if(!empty($result)){
		    		$this->_name='rms_program_name';
		    		$array = array(
		    				//'ser_cate_id'	=>$pro_id,
		    				'title'			=>$_data['product_name'],
		    				'description'	=>$_data['descript'],
		    				'price'			=>$_data['pro_price'],
		    				'status'		=>$_data['status'],
		    				'user_id'		=>$this->getUserId(),
		    				'type'			=>1,
		    		);
		    		$where = " ser_cate_id=".$_data['id'];
		    		$this->update($array, $where);
	    		}else{
	    			$this->_name='rms_program_name';
	    			$array = array(
	    					'ser_cate_id'	=>$_data['id'],
	    					'title'			=>$_data['product_name'],
	    					'description'	=>$_data['descript'],
	    					'price'			=>$_data['pro_price'],
	    					'status'		=>1,
	    					'create_date'	=>date("Y-m-d H:i:s"),
	    					'user_id'		=>$this->getUserId(),
	    					'type'			=>1,
	    			);
	    			$this->insert($array);
	    		}
	    		
    			$db->commit();
		   	}catch (Exception $e){
		   		//echo $e->getMessage();
		   		$db->rollBack();
		   	}
    }
    function getBrandLocation(){
    	$db=$this->getAdapter();
    	$sql="SELECT br_id AS id,CONCAT(branch_nameen) AS `name` FROM rms_branch WHERE STATUS=1  ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('br_id');
    	$sql.=" ORDER BY br_id DESC";
    	//echo $sql;exit();
    	$rows=$db->fetchAll($sql);
        //array_unshift($rows,array('id' => '',"name"=>"Please select brand name"));
        $options = '';
        if(!empty($rows))foreach($rows as $value){
        	$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
        }
        return $options;
    }
    function getProCode(){
    	$db = $this->getAdapter();
    	$sql="SELECT id FROM rms_product WHERE STATUS=1 ORDER BY id DESC LIMIT 1";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$pre='P-';
    	for($i = $acc_no;$i<5;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    function getProductById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM rms_product WHERE id=$id";
    	return $db->fetchRow($sql);
    }
    function getProLocationById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT id,pro_id,brand_id,pro_qty,total_amount,note FROM rms_product_location WHERE pro_id=$id";
    	return $db->fetchAll($sql);
    }
    function getCatAndMeasure($type){ //if type=1 category , if type=2 measure 
    	$db=$this->getAdapter();
    	$sql="SELECT id,name_kh FROM rms_pro_category WHERE `status`=1 AND type_id=$type";
    	return $db->fetchAll($sql);
    }
    /////////////Category and Measure 
    function getAllCategory($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT id,name_kh,name_en,type_id,`date`,`status` FROM rms_pro_category  WHERE 1 ";
    	$where="";
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " name_kh LIKE '%{$s_search}%'";
    		$s_where[]=" name_en LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}	  
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND status=".$search['status_search'];
    	}  		
    	if(!empty($search['category'])){
    		$where.=" AND type_id=".$search['category'];
    	}
    		    	
    	$order=" ORDER BY type_id DESC";
    	//echo $sql.$where;
    	return $db->fetchAll($sql.$where.$order);
    }
    public function addCategory($_data){
    	//print_r($_data);exit();
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$_arr = array(
    				'name_kh'=>$_data['name_kh'],
    				'name_en'=>$_data['name_en'],
    				'type_id'=>$_data['type'],
    				'date'=>date("Y-m-d"),
    				'status'=>$_data['status'],
    				'user_id'=>$this->getUserId()
    		);
    		$this->_name='rms_pro_category';
    		if(!empty($_data['id'])){
    			$where=" id=".$_data['id'];
    			$this->update($_arr, $where);
    		}else{
    			$this->insert($_arr);
    		}
    	$db->commit();
    	}catch (Exception $e){
    		echo $e->getMessage();
    		$db->rollBack();
    	}
    }
    function getGategoryById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT id,name_kh,name_en,type_id,`date`,`status` FROM rms_pro_category WHERE id=$id limit 1";
    	return $db->fetchRow($sql);
    }
    function getProductName(){
    	$db=$this->getAdapter();
    	$sql="SELECT p.id As id,CONCAT(p.pro_name,' ',p.pro_size) AS name FROM rms_product AS p,rms_product_location AS pl 
     			  WHERE p.id=pl.pro_id AND p.status=1 ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('brand_id');
    	$sql.="  ORDER BY id DESC";
    	return $db->fetchAll($sql);
    }
    function getLocation(){
    	$db=$this->getAdapter();
    	$sql="SELECT br_id as id,branch_nameen AS `name` FROM rms_branch WHERE `status`=1";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('br_id');
    	return $db->fetchAll($sql);
    }
    
}