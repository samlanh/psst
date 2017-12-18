<?php

class Mobileapp_Model_DbTable_DbBanner extends Zend_Db_Table_Abstract
{

    protected $_name = 'mobile_slide';

    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }


   public function getAll(){
        $db=$this->getAdapter();
        $sql="SELECT *  FROM `mobile_slide` where status = 1"; 
        $sql.=" ORDER BY id DESC";
        return $db->fetchAll($sql);
    }
 

 public function getAllBanner($search=null){
        $db=$this->getAdapter();
        $sql="SELECT p.`id`,p.`title`,p.image,p.`status`,
            (SELECT u.first_name FROM `rms_users` AS u WHERE u.id = p.`user_id` LIMIT 1) AS user_name
             FROM `mobile_slide` AS p WHERE 1";
        if(!empty($search['adv_search'])){
            $s_where = array();
            $s_search = addslashes(trim($search['adv_search']));
            $s_where[] = " p.`title` LIKE '%{$s_search}%'";
            $sql.=' AND ('.implode(' OR ',$s_where).')';
        }
        if ($search['status']>0){
            $sql.=" AND p.`status`=".$search['status'];
        }
        $sql.=" ORDER BY id DESC";
       
        return $db->fetchAll($sql);
    }
 


    public function addBanner($data='')
    {
      //  print_r($data);exit();
       $dbg = new Application_Model_DbTable_DbGlobal();
       $db = $this->getAdapter();
        $db->beginTransaction();
        try{
              $image_name='';
            $part= PUBLIC_PATH.'/images/banner/';
             if (!empty($_FILES['photo']['name'])){
                        $ss =   explode(".", $_FILES['photo']['name']);
                        $new_image_name = date("Y").date("m").date("d").time().".".end($ss);
                        $image_name = $dbg->resizeImase($_FILES['photo'], $part,$new_image_name);
                    }else{
                        $image_name = "";
                        if(!empty($data['id'])){  
                        $image_name = $data['old_photo'];
                        }
                    }
            $_arr=array(
                    'title' => $data['title'],
                    'image' => $image_name,
                    'link' => $data['link'],
                    'description' => $data['description'],
                    'option_link' => $data['option_link'],
                    'status' => $data['status'],
                    'create_date'=>date("Y-m-d H:i:s"),
                    'modify_date'=>date("Y-m-d H:i:s"),
                    'user_id' => $this->getUserId(),
            );
            $this->_name="mobile_slide";

        if(!empty($data['id'])){  
            // var_dump($_arr);exit();                            
            $where = 'id='.$data['id'];          
            $this->update($_arr, $where);                     
        }else{
             $this->insert($_arr);
        }           
            $db->commit();
        }catch(exception $e){
            Application_Form_FrmMessage::message("Application Error");
            Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
            $db->rollBack();
        }
    }   

    public function getBannerById($id){
        $db=$this->getAdapter();
        $sql="SELECT *  FROM `mobile_slide` WHERE id = ".$db->quote($id);
        $sql.=" LIMIT 1 ";
        $row=$db->fetchRow($sql);
        return $row;
    }

}

