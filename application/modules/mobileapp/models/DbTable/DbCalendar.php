<?php
class Mobileapp_Model_DbTable_DbCalendar extends Zend_Db_Table_Abstract
{
	protected $_name = 'mobile_calendar';

	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	}
	function getAllCalendar($search){
		$db=$this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': "mba.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "mba.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;	
		$sql="SELECT mba.id,mba.title,mba.amount_day,mba.start_date,mba.end_date,mba.active as status FROM $this->_name AS mba WHERE 1";
		if($search['search_status']>-1){
			$where.= " AND mba.active = ".$search['search_status'];
		}
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=$search['adv_search'];
			$s_where[]= " mba.title LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		$order = " ORDER BY mba.id DESC";
		return $db->fetchAll($sql.$where.$order);
	}

	public function getById($id)
	{
		$db=$this->getAdapter();
        $sql="SELECT *  FROM ".$this->_name." WHERE id = ".$db->quote($id);
        $sql.=" ORDER BY id ASC LIMIT 1 ";
        $row=$db->fetchRow($sql);
        return $row;
	}


	function add($data)
	{
		if(!empty($data['note'])){
				$des =  $data['note'];
			}else{
				$des = '';
			}
	      	$db = $this->getAdapter();
	        $db->beginTransaction();
	        try{
	            $dept = "";
	           if (!empty($data['selector'])) foreach ( $data['selector'] as $rs){
	                if (empty($dept)){ $dept = $rs;}else{ $dept = $dept.",".$rs;}
	            }
				if(!empty($data['id'])){
				  $where = 'id='.$data['id'];
				  $this->delete($where);
				}		  
					   
	            if($data['amount_day']>1){
	            	$date_next=$data['start_date'];
	            	for($i=1;$i<=$data['amount_day'];$i++){
						if($i>1){
							$d = new DateTime($date_next);
							$str_next = '+1 day';
							
							$d->modify($str_next);
							$date_next =  $d->format( 'Y-m-d' );
							$data['start_date']=$date_next;
						}
	            
			            $_arr=array(
							'title' => $data['holiday_name'],
							'start_date' => $data['start_date'],
							'end_date' => $data['end_date'],
							'amount_day' => $data['amount_day'],					
							'description' => $des,                  
							'active' => $data['status'],//use instead status
							'date'=> $data['start_date'],
							'modify_date'=>date("Y-m-d H:i:s"),
							'user_id'=>$this->getUserId(),
							'status' => 1,		
							'dept' => $dept,					
			            );
			        
						$_arr['create_date']=date("Y-m-d H:i:s");
			            $this->insert($_arr);
			        
	        	}
	        }
	        $db->commit();
	     }catch(exception $e){
	            Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	            $db->rollBack();
	    }
	}
}