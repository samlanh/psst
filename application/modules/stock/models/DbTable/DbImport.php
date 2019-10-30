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
    public function updateItemsByImport($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$db_items = new Global_Model_DbTable_DbItems();
    	for($i=1; $i<=$count; $i++){
    		
    		$title = empty($data[$i]['C'])?null:$data[$i]['C'];
    		
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
    		$_arr=array(
    				'items_id'		=> $items_id,
    				'items_type'	=> 3,
    				'code'			=> $data[$i]['B'],
    				'title'	 	 	=> $title,
    				'title_en'		=> $title,
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
    		$id =  $this->insert($_arr);
    	}
    }
}   

