<?php
class Mobileapp_Model_DbTable_DbFeedBack extends Zend_Db_Table_Abstract
{
	protected $_name = 'mobile_message';

	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authstu');
		return $session_user->user_id;
	}

	function getAllFeedback($search){
		$db=$this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': "mm.date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "mm.date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;	
		$sql="SELECT mm.stu_id,mm.`message`,
			(SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id = mm.`stu_id` LIMIT 1) AS stu_name,
			mm.`date`,mm.`read`,mm.`status`
			 FROM `mobile_message` AS mm WHERE mm.`reply_id`=0";
		if($search['search_status']>-1){
			$where.= " AND mm.status = ".$search['search_status'];
		}
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=$search['adv_search'];
			$s_where[]= " mm.message LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		$order = " ORDER BY mm.id DESC";
		return $db->fetchAll($sql.$where.$order);
	}
	public function getStuIdById($search=null,$stu_id)
	{
		$db=$this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': "mm.date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "mm.date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
        $sql="SELECT *  FROM ".$this->_name." WHERE stu_id = ".$db->quote($stu_id);
        $where.=" ORDER BY id ";
        $row=$db->fetchAll($sql.$where);
        return $row;
	}
	function updateMessage($id){
		$db=$this->getAdapter();
		$_arr = array(
				'read'=>"read",
		);
		$unread = "unread";
		$where = " stu_id=".$id." AND `read`='$unread'";
		$this->_name ="mobile_message";
		$this->update($_arr, $where);
	}
	function AddFeedback($data){
		$db=$this->getAdapter();
		$_arr = array(
				'message'=>$data['message'],
				'stu_id'=>$data['stu_id'],
				'reply_id'=>$data['stu_id'],
				'date'=>date("Y-m-d H:i:s"),
		);
		$id= $this->insert($_arr);
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		$string = '
				<div class="chat">
		                  <div class="chat-avatar">
		                    <a class="avatar" data-toggle="tooltip" href="#" data-placement="right" title="" data-original-title="">
		                      <img src="'.$baseurl."/images/no-profile-admin.png".'" alt="avatar">
		                    </a>
		                  </div>
		                  <div class="chat-body">
		                    <div class="chat-content">
		                     <p>'.$data['message'].'</p>
		                    </div>
		                  </div>
		                </div>
		';
		return $string;
	}
	
	

}