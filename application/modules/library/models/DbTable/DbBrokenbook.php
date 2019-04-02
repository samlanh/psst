<?php

class Library_Model_DbTable_DbBrokenbook extends Zend_Db_Table_Abstract
{
 	protected $_name = 'rms_bookbroken';
 	protected $tr;
 	public function init()
 	{
 		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
 	}
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    
    function getAllBroken($search=null){
    	$db=$this->getAdapter();
    	$sql=" SELECT 
    				b.id,
    				b.broke_no,
    				note,
        		  	DATE_FORMAT(b.date_broken, '%d/%m/%Y'),
        		  	(SELECT first_name FROM rms_users WHERE id=b.user_id LIMIT 1) AS user_name,
				  	(SELECT name_en FROM rms_view WHERE key_code=b.status LIMIT 1) AS `status`
			   	FROM 
			   		rms_bookbroken AS b
				WHERE 
    				1
    		";
    	$where = '';
    	$from_date =(empty($search['start_date']))? '1': " b.date_broken >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " b.date_broken <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  b.broke_no LIKE '%{$s_search}%'";
    		$s_where[]="  b.note LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search["status_search"]>-1){
    	    $where.=' AND b.status='.$search["status_search"];
    	}
    	$order=" ORDER BY b.id ASC";
    	return $db->fetchAll($sql.$where.$order);
    }
 
	public function addBrokenBook($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$arr=array(
				"broke_no"     	=> 	$data["broken_no"],
				"date_broken"   => 	date("Y-m-d",strtotime($data['broken_date'])),
				"note"     		=> 	$data["note"],
				"user_id"       => 	$this->getUserId(),
			);
			$this->_name="rms_bookbroken";
			$broken_id = $this->insert($arr); 

			if(!empty($data['identity'])){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$data_item= array(
						'broken_id'	=>  $broken_id,
						'book_id'	=> 	$data['book_id'.$i],
					);
					$this->_name='rms_bookbrokendetails';
					$this->insert($data_item);
					
					$book = array(
							'is_broken'	=> 1,
					);
					$this->_name='rms_book_detail';
					$where1=" id = ".$data['book_id'.$i];
					$this->update($book, $where1);
				 }
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			echo $e->getMessage();exit();
		}
	}

	public function getItemDetail($id){
		$db=$this->getAdapter();
		$sql = "SELECT * FROM rms_bookbrokendetails WHERE broken_id=$id";
		$rows=$db->fetchAll($sql);
		return $rows;
	}
	
	public function editBrokenBook($data,$id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
		    $row_item=$this->getBrokenDetailById($id);
		    if(!empty($row_item)){
		    	foreach ($row_item As $rs_item){
	    			$datatostock   = array(
    					'is_broken' => 0,
	    			);
	    			$this->_name="rms_book_detail";
	    			$where=" id = ".$rs_item['book_id'];
	    			$this->update($datatostock, $where);
		    	}
		    }
             
		    $arr=array(
	    		"broke_no"     	=> 	$data["broken_no"],
				"date_broken"   => 	date("Y-m-d",strtotime($data['broken_date'])),
				"note"     		=> 	$data["note"],
				"user_id"       => 	$this->getUserId(),
		    );
		    $this->_name="rms_bookbroken";
			$where=" id = $id ";
		    $this->update($arr, $where); 
			
			$this->_name="rms_bookbrokendetails";
			$where1=" broken_id = $id ";
			$this->delete($where1);

			if(!empty($data['identity'])){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$data_item= array(
						'broken_id'	=>  $id,
						'book_id'	=> 	$data['book_id'.$i],
					);
					$this->_name='rms_bookbrokendetails';
					$this->insert($data_item);
					
					$book = array(
							'is_broken'	=> 1,
					);
					$this->_name='rms_book_detail';
					$where2=" id = ".$data['book_id'.$i];
					$this->update($book, $where2);
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
	
	public function getBrokenDetailById($id){
		$db=$this->getAdapter();
		$sql="SELECT 
					bkd.*,
					bd.serial,
					bd.barcode 
				FROM 
					rms_bookbrokendetails as bkd , 
					rms_book_detail as bd 
				WHERE 
					bd.id = bkd.book_id 
					and bkd.broken_id = $id 
			";
		return $db->fetchAll($sql);
	}
	 
	function getBrokenById($id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_bookbroken WHERE id=$id";
		return $db->fetchRow($sql);
	}
	 
	function getBrokenNo(){
		$db=$this->getAdapter();
		$sql="SELECT count(id) FROM rms_bookbroken limit 1";
		$qty=$db->fetchOne($sql);
		
		$qty_new = $qty+1;
		$lenght = strlen($qty_new);
		
		$prefix='';
		for($i=$lenght;$i<5;$i++){
			$prefix.='0';
		}
		return $prefix.$qty_new;
	}
	
}



