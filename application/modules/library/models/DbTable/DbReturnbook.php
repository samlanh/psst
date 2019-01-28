<?php

class Library_Model_DbTable_DbReturnbook extends Zend_Db_Table_Abstract
{
 	protected $_name = 'rms_book';
 	protected $tr;
 	public function init()
 	{
 		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
 	}
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;
    }
    
    function getAllReturnBook($search=null){
    	$db=$this->getAdapter();
    	$sql=" SELECT 
    				ret.id,
    				ret.return_no,
			     	ret.return_date,
			     	ret.note,
					(SELECT first_name FROM rms_users WHERE id=ret.user_id LIMIT 1) AS user_name,
					(SELECT name_en FROM rms_view WHERE key_code=ret.status LIMIT 1) AS `status`
				FROM 
					rms_bookreturn AS ret
				WHERE 
					1
    		";
    	
    	$where = '';
    	
    	$from_date =(empty($search['start_date']))? '1': " ret.return_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " ret.return_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  ret.return_no LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	
    	if($search["status_search"]>-1){
    	    $where.=' AND b.status='.$search["status_search"];
    	}
    	
    	if($search["student_name"]>0){
    		$where.=' AND bor.stu_id='.$search["student_name"];
    	}
    	if(!empty($search["is_type_bor"])){
    		$where.=' AND bor.borrow_type='.$search["is_type_bor"];
    	}
    	
    	$order=" ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
 
	public function addReturnBook($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$arr=array(
				"return_no"     => 	$data["borrow_id"],
				"return_date"   => 	date("Y-m-d",strtotime($data['return_date'])),
				"note"          => 	$data['notes'],
				"user_id"       => 	$this->getUserId(),
			);
			$this->_name="rms_bookreturn";
			$return_id = $this->insert($arr); 

			if($data['identity']!=""){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{ 
					$data_item= array(
						'return_id'		=> $return_id,
						'book_id'		=> $data['book_id'.$i],
						'borr_detail_id'=> $data['borrow_detail_id'.$i],
					);
					$this->_name='rms_bookreturndetails';
					$this->insert($data_item);
					
					$borr_item= array(
						'is_return'	=> 1,
					);
					$this->_name='rms_borrowdetails';
					$where=" book_id = ".$data['book_id'.$i]." and id = ".$data['borrow_detail_id'.$i];
					$this->update($borr_item, $where);
					
					$book = array(
							'is_borrow'	=> 0,
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
	
	public function updateReturnBook($data){
		//print_r($data['stu_id']);exit();
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			
			$id=$data['id'];
			$row_item=$this->getItemDetail($id);
			if(!empty($row_item)){
				foreach ($row_item As $rs_item){
					$row=$this->getBookQty($rs_item['book_id']);
					if($row){
						$datatostock   = array(
								'qty_after' =>  $row["qty_after"]-$rs_item['borr_qty'],
								'date'		=>	date("Y-m-d"),
						);
// 						$db->getProfiler()->setEnabled(true);
						$this->_name="rms_book";
						$where=" id = ".$row['id'];
						$this->update($datatostock, $where);
					}
					
					$borr_qty=$this->getQtyBorrow($rs_item['borrow_id'],$rs_item['book_id']);
					if($borr_qty){
						$arr    = array(
								'borr_qty' =>  $borr_qty["borr_qty"]+$rs_item['borr_qty'],
								'date'		=>	date("Y-m-d"),
						);
						$this->_name="rms_borrowdetails";
						$where=" id = ".$borr_qty['id'];
						$this->update($arr, $where);
					}
				}
			}
			
			$this->_name='rms_bookreturndetails';
			$where=" return_id=".$id;
			$this->delete($where);
			
			$stu_id=$data['stu_id'];
			$arr_return=array(
					"return_no"     => 	$data["return_no"],
					"phone"     	=> 	$data["phone"],
					"stu_id"        => 	$data["stu_id"],
					"return_date"   => 	date("Y-m-d",strtotime($data['return_date'])),
					"note"          => 	$data['notes'],
					"user_id"       => 	$this->getUserId(),
					"status"        => 	$data['status'],
			);
			$this->_name="rms_bookreturn";
			$where=" id=".$data['id'];
			$this->update($arr_return, $where);
			 
			
			if($data['record_row']!=""){
				$ids=explode(',',$data['record_row']);
				//print_r($ids);exit();
				foreach ($ids as $i)
				{ 
					$is_comp=0;
					$total_borr=$data['oldbor_qty_'.$i]+$data['oldqty_return_'.$i];
					if($total_borr <= $data['return_qty_'.$i]){
						$is_comp=1;
					}
					$data_item= array(
							'return_id'	=>  $data['id'],
							'book_id'	=> 	$data['book_id_'.$i],
							'borr_qty'	=>  $data['return_qty_'.$i],
							'note'  	=> 	$data['note_'.$i],
							'borr_detail_id'=>$data['oldborrow_id'.$i],
							'user_id'	=> 	$this->getUserId(),
							'is_full'	=> 	$is_comp,
							'status'	=> 	$data['status'],
							
							'delay_qty'	=>  $data['delay_qty_'.$i],
							'date_delay'=>  $data['date_delay_'.$i],
					);
					$this->_name='rms_bookreturndetails';
					$this->insert($data_item);
					
					$borr_qtys=$this->getQtyBorrow($data['stu_id'],$data['book_id_'.$i]);
					if($borr_qtys){
						$data_borrow    = array(
								'is_full'	=> 	$is_comp,
								'borr_qty' =>  $borr_qtys["borr_qty"]-$data['return_qty_'.$i],
								'date'		=>	date("Y-m-d"),
								'return_date'=> date("Y-m-d",strtotime($data['date_delay_'.$i]))
						);
						$this->_name="rms_borrowdetails";
						$where=" id = ".$borr_qtys['id'];
						$this->update($data_borrow, $where);
					}
				
					$rows=$this->getBookQty($data['book_id_'.$i]);
					if($rows){
						$datatostock= array(
								'qty_after' => $rows["qty_after"]+$data['return_qty_'.$i],
								'date'		=>	date("Y-m-d"),
								'user_id'	=>$this->getUserId()
						);
						$this->_name="rms_book";
						$where=" id = ".$rows['id'];
						$this->update($datatostock, $where);
					} 
				}
			}
			//exit();
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	 
	function getReturnBookById($id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_bookreturn WHERE id=$id";
		return $db->fetchRow($sql);
	}
	
	function getReturnDetailById($id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_bookreturndetails WHERE return_id=$id";
		return $db->fetchAll($sql);
	}
	
	function getReturnBookNo(){
		$db=$this->getAdapter();
		$sql="SELECT id FROM rms_bookreturn WHERE 1 ORDER BY id DESC";
		$row=$db->fetchOne($sql);
		if(empty($row)){
			$row=floatVal('1.0'.rand(1,9));
		}
		$fex='';
		if(!empty($row)){
			for($i=0;$i<4;$i++){
				$fex.='0';
			}
		}
		return $fex.$row;
	}
	
	function getBookDetail($book_id=0){
		$db=$this->getAdapter();
		$sql="SELECT 
					bd.id as borrow_detail_id,
					b.*,
					s.stu_khname,
					s.last_name,
					s.stu_enname,
					s.stu_code,
					DATE_FORMAT(b.return_date, '%d-%m-%Y') AS return_date
				FROM 
					rms_borrow as b,
					rms_borrowdetails as bd,
					rms_student as s
				WHERE 
					b.id = bd.borr_id
					and b.stu_id = s.stu_id
					and is_return = 0
					and bd.book_id = $book_id
			";
		return $db->fetchRow($sql);
	}
	
}



