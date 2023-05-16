<?php

class Application_Model_DbTable_DbGlobalStock extends Zend_Db_Table_Abstract
{
    // set name value
	public function setName($name){
		$this->_name=$name;
	}
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	function currentlang(){
		$session_lang=new Zend_Session_Namespace('lang');
		$lang = $session_lang->lang_id;
		if (empty($session_lang->lang_id)){
			$lang = 1;
		}
		return $lang;
	}
// 	public function getGlobalDb($sql)
//   	{
//   		$db=$this->getAdapter();
//   		$row=$db->fetchAll($sql);  		
//   		if(!$row) return NULL;
//   		return $row;
//   	}
  	
//   	public function getGlobalDbRow($sql)
//   	{
//   		$db=$this->getAdapter();  		
//   		$row=$db->fetchRow($sql);
//   		if(!$row) return NULL;
//   		return $row;
//   	}
  	
//     public function isRecordExist($conditions,$tbl_name){
// 		$db=$this->getAdapter();		
// 		$sql="SELECT * FROM ".$tbl_name." WHERE ".$conditions." LIMIT 1"; 
// 		$row= count($db->fetchRow($sql));
// 		if(!$row) return NULL;
// 		return $row;	
//     }
	
	function addProductHistoryQty($branch_id,$pro_id,$tranType,$Qty,$tranId=0){//done
		$this->_name='rms_product_history';
		$arr = array(
				'branchId'=>$branch_id,
				'pro_id'=>$pro_id,
				'transId'=>$tranId,
				'tranType'=>$tranType,//1=+init,2 +receive,3 -usage,4 -sale,5 -transfer out ,5 +receiv tran,7 +- adjust
				'qty'=>$Qty,
				'userId'=>$this->getUserId(),
				'transDate'=>date("Y-m-d"),
		);
		$this->insert($arr);
	}
	function DeleteProductHistoryQty($tranId){//done
		$this->_name='rms_product_story';
		$where= "transId = ".$tranId;
		$this->delete($where);
	}
	function getProductLocationbyBranch($_data=null){//done
		$db=$this->getAdapter();
	
		$branch_id=0;
		if(!empty($_data['branch_id'])){
			$branch_id = $_data['branch_id'];
		}
		$sql="
		SELECT
			p.id,
			CONCAT(COALESCE(p.code,''),' ',COALESCE(p.title,'')) AS `name`,
			p.title,
			l.pro_qty AS currentQty,
			l.costing,
			(SELECT branch_namekh FROM `rms_branch` where br_id=l.branch_id LIMIT 1) as projectName
		";
	
		$sql.=" FROM
			`rms_itemsdetail` AS p,
			rms_product_location AS l
		WHERE p.status=1
			  AND p.items_type=3
			   AND p.id=l.pro_id ";
			
		if(!empty($branch_id)){
			$sql.=" AND l.branch_id=".$branch_id;
		}
	
		if(!empty($_data['productId'])){
			$sql.=" AND p.id= ".$_data['productId'];
		}
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	function getProductInfoByLocation($_data=null){//done
	
		$db=$this->getAdapter();
	
		$branch_id=0;
		if(!empty($_data['branch_id'])){
			$branch_id = $_data['branch_id'];
		}
		$sql="
		SELECT
				p.id,
				CONCAT(COALESCE(p.code,''),' ',COALESCE(p.title,'')) AS `name`,
				p.title,
				p.is_productseat,
				p.is_onepayment,
				p.product_type,
				(SELECT pl.pro_qty FROM rms_product_location AS pl WHERE pl.branch_id=p.id AND pl.branch_id= $branch_id LIMIT 1) AS currentQty,
				(SELECT pl.costing FROM rms_product_location AS pl WHERE pl.pro_id=p.id AND pl.branch_id= $branch_id LIMIT 1) AS currentPrice,
				p.measure AS measureTitle ";
			$sql.=" FROM
			`rms_itemsdetail` AS p ";
				
			if(!empty($branch_id)){
				$sql.=" ,rms_product_location AS l";
				$sql.=" WHERE p.status=1
				AND p.id=l.pro_id ";
				$sql.=" AND l.branch_id=".$branch_id;
			}else{
				$sql.=" WHERE p.status=1 ";
			}
			if(!empty($_data['categoryId'])){
				$sql.=" AND p.items_id= ".$_data['categoryId'];
			}
			if(!empty($_data['productId'])){
				$sql.=" AND p.id= ".$_data['productId'];
			}
				$sql.=" ORDER BY p.id DESC LIMIT 1";
			return $db->fetchRow($sql);
	
	}
	function updateStockbyBranchAndProductId($data){//done
		$resultStock = $this->getProductInfoByLocation($data);
		if(!empty($resultStock)){
			if($resultStock['is_productseat']==0){//for product sale type
					
				$currentStock = $resultStock['currentQty'];
				$currentPrice = $resultStock['currentPrice'];
				$newQty = $data['EntyQty'];
				$newPrice = $data['EntyPrice'];
				$totalQty = $currentStock+$newQty;
				$costing = (($currentStock*$currentPrice)+($newQty*$newPrice))/$totalQty;
	
	
				$arr = array(
						'branch_id'=>$data['branch_id'],
						'productId'=>$data['productId'],
						'costing'=>$currentPrice,
						'date'=>date('Y-m-d')
				);
	
				$this->_name='rms_product_costing';
				$this->insert($arr);
	
				$arr = array(
						'pro_qty'=>$totalQty,
						'costing'=>$costing
				);
	
				$this->_name='rms_product_location';
				$where = 'branch_id='.$data['branch_id']." AND pro_id=".$data['productId'];
				$this->update($arr, $where);
			}else{//for product set
				$param =array(
						'pro_id'=>$data['productId']
						);
				$proSets = $this->getProductasSet($param);
				if(!empty($proSets)){
					foreach($proSets as $rs){
						$data['pro_id'] = $rs['subpro_id'];
						$data['EntyQty'] = $data['EntyQty']*$rs['qty'];
						$this->updateStockbyBranchAndProductId($data);
					}
				}
			}
		}
	}
	function updateProductLocation($data){
		$resultStock = $this->getProductInfoByLocation($data);
		if(!empty($resultStock)){
			if($resultStock['is_productseat']==0){
				$currentStock = $resultStock['currentQty'];
				$newQty = $data['EntyQty'];
				$totalQty = $currentStock+$newQty;
					
				$arr = array(
						'pro_qty'=>$totalQty,
				);
					
				$this->_name='rms_product_location';
				$where = 'branch_id='.$data['branch_id']." AND pro_id=".$data['productId'];
				$this->update($arr, $where);
			}else{
				$param =array(
						'pro_id'=>$data['productId']
				);
				$proSets = $this->getProductasSet($param);
				if(!empty($proSets)){
					foreach($proSets as $rs){
						$data['pro_id'] = $rs['subpro_id'];
						$data['EntyQty'] = $data['EntyQty']*$rs['qty'];
						$this->updateProductLocation($data);
					}
				}
			}
		}else{//new stock
			$arr = array(
					'branch_id'=>$data['branch_id'],
					'pro_qty'=>$data['EntyQty'],
					'pro_id'=>$data['productId'],
					'costing'=>empty($data['costing'])?0:$data['costing'],
			);
				
			$this->_name='rms_product_location';
			$this->insert($arr);
		}
	}
	function getProductasSet($data){
		$sql="SELECT 
				pro_id,
				subpro_id,
				qty,
			FROM
				rms_product_setdetail
			WHERE 1 ";
		if(!empty($data['pro_id'])){
			$sql.=" AND pro_id=".$data['pro_id'];
		}
		$db = $this->getAdapter();
		return $db->fetchAll($sql);
	}
    
}
?>