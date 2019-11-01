<?php

class Stock_Model_DbTable_DbImport extends Zend_Db_Table_Abstract
{

    protected $_name = 'ldc_product';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
    
    function checkItemsTypeId($category){
    	$db = $this->getAdapter();
    	$sql="SELECT d.* FROM rms_items AS d WHERE d.title = '$category' AND d.type=3 LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function checkBranchID($branch_title){
    	$db = $this->getAdapter();
    	$sql="SELECT d.* FROM rms_branch AS d WHERE d.branch_nameen = '$branch_title' LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getProductId($title){
    	$db = $this->getAdapter();
    	$sql="SELECT d.* FROM rms_itemsdetail AS d WHERE d.title = '$title' AND d.items_type=3 LIMIT 1";
    	return $db->fetchRow($sql);
    }
    public function updateItemsByImport($data,$branch_id){
    	$db = $this->getAdapter();
    	$count = count($data);
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$db_items = new Global_Model_DbTable_DbItems();
    	for($i=1; $i<=$count; $i++){
    		
    		$pro_title = empty($data[$i]['C'])?null:$data[$i]['C'];
    		
    		$cate_title = empty($data[$i]['D'])?null:$data[$i]['D'];
    		$cate = $this->checkItemsTypeId($cate_title);
    		$items_id = 0;
    		$schooloption="";
    		if (!empty($cate['id'])){
    			$itemsinfo = $db_items->getDegreeById($cate['id'],3);
    			$items_id = $cate['id'];
    			$schooloption = empty($itemsinfo['schoolOption'])?0:$itemsinfo['schoolOption'];
    		}
    		$product_type = 2;
    		if($data[$i]['F']=="Product For Sell"){
    			$product_type = 1;
    		}
    		
    		$product = $this->getProductId($pro_title);
    		$pro_id = empty($product['id'])?0:$product['id'];
    		if (empty($product)){
	    		$_arr=array(
	    				'items_id'		=> $items_id,
	    				'items_type'	=> 3,
	    				'code'			=> $data[$i]['B'],
	    				'title'	 	 	=> $pro_title,
	    				'title_en'		=> $pro_title,
	    				'note'    		=> $data[$i]['E'],
	    				'product_type' 	=> $product_type,
	    				'is_onepayment' => 1,
	    				'cost'    		=> $data[$i]['G'],
	    				'schoolOption'  => $schooloption,
	    				'create_date' 	=> date("Y-m-d H:i:s"),
	    				'modify_date' 	=> date("Y-m-d H:i:s"),
	    				'status'		=> 1,
	    				'user_id'	 	=> $this->getUserId()
	    		);
	    		$this->_name = "rms_itemsdetail";
	    		$pro_id =  $this->insert($_arr);
    		}
    		
    		if(!empty($data[$i]['H'])){
    			$_arr_prolocation =array(
    				'pro_id'		=> $pro_id,
    				'brand_id'		=>$branch_id,
					'pro_qty'		=>$data[$i]['H'],
					'price'			=>$data[$i]['i'],
					'stock_alert'	=>$data[$i]['j'],
    			);
    			$this->_name = "rms_product_location";
    			$this->insert($_arr_prolocation);
    		}
    	}
    }
}   

