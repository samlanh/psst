<?php
class Mobileapp_Model_DbTable_DbContact extends Zend_Db_Table_Abstract
{
	protected $_name = 'mobile_contact';

	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}

	function getAllContact($search){
		$db=$this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': "mba.date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "mba.date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;	
		$sql="SELECT mba.id,mba.title,mba.date,mba.active as status FROM $this->_name AS mba WHERE 1";
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
        $sql.=" LIMIT 1 ";
        $row=$db->fetchRow($sql);
        return $row;
	}


	function add($data){
		if(!empty($data['description'])){
			$des =  $data['description'];
		}else{
			$des = '';
		}

      	$db = $this->getAdapter();
        $db->beginTransaction();
        try{
            
            $_arr=array(
                    'title' => $data['title'],                  
                    'description' => $des,                  
                    'active' => 1,//use instead status
                    'date'=>date("Y-m-d H:i:s"),
					'modify_date'=>date("Y-m-d H:i:s"),
					'user_id'=>$this->getUserId(),
					'status' => 1,	
            );
         $this->_name;
        if(!empty($data['id'])){  
            // var_dump($_arr);exit();                            
            $where = 'id='.$data['id'];          
           $this->update($_arr, $where);                     
        }else{
			$_arr['create_date']=date("Y-m-d H:i:s");
            $this->insert($_arr);
        }           
            $db->commit();
        }catch(exception $e){
            Application_Form_FrmMessage::message("Application Error");
            Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
            $db->rollBack();
        }

 }
 public function getContact()
 {
 	$this->_name = "mobile_location";
 	$db=$this->getAdapter();
 	$sql="SELECT *  FROM ".$this->_name." WHERE 1 ";
 	$sql.=" LIMIT 1 ";
 	$row=$db->fetchRow($sql);
 	return $row;
 }
 function updateContactLocation($data){
 	$db = $this->getAdapter();
 	$db->beginTransaction();
 	try{
 		$this->_name = "mobile_location";
 		$arr=array(
//  				'title' => $data['title'],
 				'latitude' => $data['latitude'],
 				'longitude' => $data['longitude'],
//  				'address' => $data['address'],
 				'date'		=>date("Y-m-d H:i:s"),
 				'phone' 	=> $data['phone'],
 				'email' 	=> $data['email'],
 				'website' 	=> $data['website'],
 				'facebook'  => $data['facebook'],
 				'youtube'   => $data['youtube'],
 				'instagram' => $data['instagram'],
				'tiktok'    => $data['tiktok'],
 				'other_social' => $data['other_social'],
 		);
 		
 		$dbglobal = new Application_Model_DbTable_DbGlobal();
 		$lang = $dbglobal->getLaguage();
 		if (!empty($data['id'])){
//  			$arr['status']= $data['status'];
 			$where=" id=".$data['id'];
 			$this->_name="mobile_location";
 			$this->update($arr, $where);
 			$article_id =$data['id'];
 		
 			if(!empty($lang)){
 				$iddetail="";
 				foreach($lang as $row){
 					$title = str_replace(' ','',$row['title']);
 					if (empty($iddetail)){
 						if (!empty($data['iddetail'.$title])){
 							$iddetail=$data['iddetail'.$title];
 						}
 					}
 					else{
 						if (!empty($data['iddetail'.$title])){
 							$iddetail=$iddetail.",".$data['iddetail'.$title];
 						}
 					}
 				}
 				$this->_name="mobile_location_detail";
 				$where1=" location_id=".$data['id'];
 				if (!empty($iddetail)){
 					$where1.=" AND id NOT IN (".$iddetail.")";
 				}
 				$this->delete($where1);
 				 
 				foreach($lang as $row){
 					$title = str_replace(' ','',$row['title']);
 					if (!empty($data['iddetail'.$title])){
 						$arr_article = array(
 								'location_id'=>$article_id,
 								'title'=>$data['title'.$title],
 								'description'=>$data['description'.$title],
 								'lang'=>$row['id'],
 						);
 						$this->_name="mobile_location_detail";
 						$wheredetail=" location_id=".$data['id']." AND id=".$data['iddetail'.$title];
 						$this->update($arr_article,$wheredetail);
 					}else{
 						$arr_article = array(
 								'location_id'=>$article_id,
 								'title'=>$data['title'.$title],
 								'description'=>$data['description'.$title],
 								'lang'=>$row['id'],
 						);
 						$this->_name="mobile_location_detail";
 						$this->insert($arr_article);
 					}
 				}
 			}
 		}else{
 			$this->_name="mobile_location";
 			$arr['create_date']= date("Y-m-d H:i:s");
 			$arr['status']= 1;
 			$article_id = $this->insert($arr);
 		
 			if(!empty($lang)) foreach($lang as $row){
 				$title = str_replace(' ','',$row['title']);
 				$arr_article = array(
 						'location_id'=>$article_id,
 						'title'=>$data['title'.$title],
 						'description'=>$data['description'.$title],
 						'lang'=>$row['id'],
 				);
 				$this->_name="mobile_location_detail";
 				$this->insert($arr_article);
 			}
 		}
 		$db->commit();
 	}catch(exception $e){
 		Application_Form_FrmMessage::message("Application Error");
 		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
 		$db->rollBack();
 	}
 
 }
 function getArticleTitleByLang($article_id,$lang){
 	$db = $this->getAdapter();
 	$sql="SELECT acd.id,acd.`title`,acd.`lang`,acd.description FROM `mobile_location_detail` AS acd WHERE acd.`location_id`=$article_id AND acd.`lang`=$lang";
 	return $db->fetchRow($sql);
 }


}