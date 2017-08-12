<?php

class Accounting_Model_DbTable_DbProduct extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_product';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    	 
    }
    function getAllProduct($search=null){
    	$db = $this->getAdapter();
    	$sql=" SELECT p.id,p.pro_code,
			 (SELECT CONCAT(branch_nameen) FROM rms_branch WHERE rms_branch.br_id=pl.brand_id) AS branch_name,
				p.pro_name,(SELECT cat.name_kh FROM rms_pro_category AS cat WHERE cat.id=p.cat_id) As cat_name,
				(select name_en from rms_view where type=11 and key_code=pro_type) as pro_type,
			    p.pro_price, 
				pl.pro_qty,p.date,p.status
				FROM rms_product AS p,rms_product_location AS pl
				WHERE p.id=pl.pro_id and sale_set = ".$search['sale_set'];
    	
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
    	if(!empty($search['category_id'])){
    		$where.=" AND p.cat_id=".$search['category_id'];
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
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	//exit();
    	try{
	    		$_arr = array(
	    				'pro_name'	=>$_data['product_name'],
	    				'pro_code'	=>$_data['product_code'],
	    				'cat_id'	=>$_data['category_id'],
	    				'pro_price'	=>$_data['pro_price'],
	    				'pro_des'	=>$_data['descript'],
	    				'pro_type'	=>$_data['pro_type'],
	    				'status'	=>$_data['status'],
	    				'date'		=>date("Y-m-d"),
	    				'user_id'	=>$this->getUserId()
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
		    				'type'			=>1, // type=1 => product 
		    				'pro_type'		=>0, // product not set
	    				);
	    		$this->insert($array);
	    		
    			$db->commit();
		   	}catch (Exception $e){
		   		$db->rollBack();
		   		echo $e->getMessage();
		   	}
    }
    
    function checkProductExist($pro_id){
    	$db = $this->getAdapter();
    	$sql = "select * from rms_program_name where ser_cate_id=$pro_id and type=1";
    	return $db->fetchRow($sql);
    }
    
	public function updateProduct($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
	    		$_arr = array(
	    				'pro_name'	=>$_data['product_name'],
	    				'pro_code'	=>$_data['product_code'],
	    				'cat_id'	=>$_data['category_id'],
	    				'pro_price'	=>$_data['pro_price'],
	    				'pro_des'	=>$_data['descript'],
	    				'pro_type'	=>$_data['pro_type'],
	    				'status'	=>$_data['status'],
	    				//'date'		=>date("Y-m-d"),
	    				'user_id'	=>$this->getUserId()
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
		    				'type'			=>1, // type=1 => product 
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
	    					'type'			=>1, // type=1 => product 
	    					'pro_type'		=>$_data['pro_type'], // 1=cut stock , 2=cut stock later
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
    	$rows=$db->fetchAll($sql);
        $options = '';
        if(!empty($rows))foreach($rows as $value){
        	$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
        }
        return $options;
    }
    function getAllProductOption(){
    	$db=$this->getAdapter();
    	$sql="SELECT id, pro_name FROM rms_product WHERE status=1 AND pro_name!='' AND sale_set=0  ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('br_id');
    	$sql.=" ORDER BY id DESC ";
    	$rows=$db->fetchAll($sql);
    	$options = '';
    	if(!empty($rows))foreach($rows as $value){
    		$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['pro_name'], ENT_QUOTES).'</option>';
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
    function getProductCategory(){ //if type=1 category , if type=2 measure 
    	$db=$this->getAdapter();
    	$sql="SELECT id,name_kh as name FROM rms_pro_category WHERE `status`=1 AND name_kh!=''";
    	return $db->fetchAll($sql);
    }
    /////////////Category 
    function getAllCategory($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    			id,
    			name_kh,
    			`date`,
    			(select name_en from rms_view where type=1 and key_code=status) as status
    		  FROM rms_pro_category  WHERE status=1 ";
    	$where=" ";
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " name_kh LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}	  
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND status=".$search['status_search'];
    	}  		
    		    	
    	$order=" ORDER BY id DESC";
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
    		$db->rollBack();
    		echo $e->getMessage();
    	}
    }
    function getGategoryById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT id,name_kh,name_en,type_id,`date`,`status` FROM rms_pro_category WHERE id=$id limit 1";
    	return $db->fetchRow($sql);
    }
    function getProductName(){
    	$db=$this->getAdapter();
    	$sql="SELECT p.id As id,CONCAT(p.pro_name) AS name FROM rms_product AS p,rms_product_location AS pl 
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

    function AddProCate($data){
    	$this->_name="rms_pro_category";
    	$array = array(
    				'name_kh'=>$data['title'],
	    			'date'=>date('Y-m-d'),
	    			'user_id'=>$this->getUserId(),
    			);
    	return $this->insert($array);
    }
    /*pro duct set group */
    function getAllProductSetGroup($search=null){
    	$db = $this->getAdapter();
    	$sql=" SELECT p.id,p.pro_code,
    	p.pro_name,(SELECT cat.name_kh FROM rms_pro_category AS cat WHERE cat.id=p.cat_id LIMIT 1) As cat_name,
    	p.pro_price,p.date,p.status
    	FROM rms_product AS p
    	WHERE sale_set = ".$search['sale_set'];
    	 
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
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND p.status=".$search['status_search'];
    	}
//     	$dbp = new Application_Model_DbTable_DbGlobal();
//     	$where.=$dbp->getAccessPermission('brand_id');
    	$order=" ORDER BY id DESC";
//     	echo $sql.$where.$order;exit();
    	return $db->fetchAll($sql.$where.$order);
    }
    public function addProductSetGroup($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$_arr = array(
    				'pro_name'	=>$_data['product_name'],
    				'pro_code'	=>$_data['product_code'],
    				'cat_id'	=>$_data['category_id'],
    				'pro_price'	=>$_data['pro_price'],
    				'pro_des'	=>$_data['descript'],
    				'pro_type'	=>2,
    				'sale_set'	=>1,
    				'status'	=>$_data['status'],
    				'date'		=>date("Y-m-d"),
    				'user_id'	=>$this->getUserId()
    		);
    		$pro_id = $this->insert($_arr);
    		
    		$this->_name='rms_product_setdetail';
    		$ids = explode(',', $_data['identity']);
    		$one_price=$_data['pro_price'];
    		foreach ($ids as $i){
    			$_arr = array(
    					'pro_id'=>$pro_id,
    					'subpro_id'=>$_data['pro_id'.$i],
    					'qty'=>$_data['qty_'.$i],
    					'remark'=>$_data['note_'.$i],
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
    				'pro_type'		=>2,
    		);
    		$this->insert($array);
    		$db->commit();    		
    	}catch (Exception $e){
    		$db->rollBack();
    		echo $e->getMessage();exit();
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function getProDetailById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM rms_product_setdetail WHERE pro_id=$id";
    	return $db->fetchAll($sql);
    }  
    public function updateProductSetDetail($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$_arr = array(
    				'pro_name'	=>$_data['product_name'],
    				'pro_code'	=>$_data['product_code'],
    				'cat_id'	=>$_data['category_id'],
    				'pro_price'	=>$_data['pro_price'],
    				'pro_des'	=>$_data['descript'],
    				'pro_type'	=>2,
    				'sale_set'	=>1,
    				'status'	=>$_data['status'],
    				'date'		=>date("Y-m-d"),
    				'user_id'	=>$this->getUserId()
    		);
    		$where=" id=".$_data['id'];
    		$this->update($_arr, $where);
    		
    		$one_price=$_data['pro_price'];
    		$this->_name='rms_product_setdetail';
    		$where=" pro_id= ".$_data['id'];
    		$this->delete($where);
    		
    		$ids = explode(',', $_data['identity']);
    		foreach ($ids as $i){
    			$_arr = array(
    					    'pro_id'=>$_data['id'],
    						'subpro_id'=>$_data['pro_id'.$i],
    						'qty'=>$_data['qty_'.$i],
    						'remark'=>$_data['note_'.$i],
    					);
    			$this->insert($_arr);
    		}
    		 
    			$this->_name='rms_program_name';
    			$array = array(
    					//'ser_cate_id'	=>$pro_id,
    					'title'			=>$_data['product_name'],
    					'description'	=>$_data['descript'],
    					'price'			=>$_data['pro_price'],
    					'status'		=>$_data['status'],
    					'user_id'		=>$this->getUserId(),
    					'type'			=>1, 
    					'pro_type'		=>2,
    			);
    			$where = " ser_cate_id=".$_data['id'];
    			$this->update($array, $where);
    		    $db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    	}
    }
}