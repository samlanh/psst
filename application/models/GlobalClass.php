<?php

class Application_Model_GlobalClass  extends Zend_Db_Table_Abstract
{
   public static function getInvoiceNo(){	
		//return strtoupper(uniqid());
		$sub=substr(uniqid(rand(10,1000),false),rand(0,10),5);
		$date= new Zend_Date();
		$head="W".$date->get('YY-MM-d/ss');
		return $head.$sub;
   }
   public function getOptonsHtml($sql, $display, $value){
   	$db = $this->getAdapter();
   	$option = '<option value="">--- Select ---</option>';
   	foreach($db->fetchAll($sql) as $r){
   			
   		$option .= '<option value="'.$r[$value].'">'.htmlspecialchars($r[$display], ENT_QUOTES).'</option>';
   	}
   	return $option;
   }
   public function getYesNoOption(){
   	//Select Public for report
   	$myopt = '<option value="">---Select----</option>';
   	$myopt .= '<option value="Yes">Yes</option>';
   	$myopt .= '<option value="No">No</option>';
   	return $myopt;
   }	
   public function getImgAttachStatus($rows,$base_url, $case=''){
		if($rows){			
			$imgattach='<img src="'.$base_url.'/images/icon/attachment.png"/>';
			$imgnone='<img src="'.$base_url.'/images/icon/no-attachment.png"/>';
			if($case !== ''){
				$imgattach='<img src="'.$base_url.'/images/icon/attachment.png"/>';
				$imgnone='<img src="'.$base_url.'/images/icon/no-attachment.png"/>';
			}
			 
			foreach ($rows as $i =>$row){
				if(is_dir('docs/case_note_id_'.$row['note_id'])){
					$rows[$i]['note_id'] = $imgattach;
				}
				else{
					$rows[$i]['note_id'] = $imgnone;
				}
			}
			 
		}		
		return $rows;
	}
	/**
	 * add element "delete" to $rows
	 * @param array $rows
	 * @param string $url_delete
	 * @param string $base_url
	 * @return array $rows
	 */
	public static function getImgDelete($rows,$url_delete,$base_url){
		foreach($rows as $key=>$row){
			$url = $url_delete.$row["id"];
			$row['delete'] = '<a href="'.$url.'"><img src="'.BASE_URL.'/images/icon/cross.png"/></a>';
			$rows[$key] = $row;
		}
		return $rows;
	}
	
	/**
	 * Get Day name With multiple Languages
	 * @param string $key
	 * @var $key ('mo', 'tu', 'we', 'th', 'fr', 'sa', 'su')
	 */
	public function getDayName($key = ''){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$day_name = array(
							'su' => $tr->translate('SU'),
							'mo' => $tr->translate('MO'),
							'tu' => $tr->translate('TU'),
							'we' => $tr->translate('WE'),
							'th' => $tr->translate('TH'),
							'fr' => $tr->translate('FR'),
							'sa' => $tr->translate('SA')							
						 );
		if(empty($key)){
			return $day_name;
		}
		return  $day_name[$key];
	}
	
	/**
	 * Get all Hour per day
	 * @param int $key
	 * @return multitype:string |Ambigous <string>
	 * @var $key = [0-23]
	 */
	public function getHours($key = ''){
		// 		$hours='';
		// 		$time = 7;
	
		// 		echo $hours;exit();
		$hours ='<option value="07.00">07:00 AM </option>'.
				'<option value="07.15">07:15 AM </option>'.
				'<option value="07.30">07:30 AM </option>'.
				'<option value="08.00">08:00 AM </option>'.
				'<option value="08.15">08:15 AM </option>'.
				'<option value="08:20">08:20 AM </option>'.
				'<option value="08.30">08:30 AM </option>'.
				'<option value="08.40">08.40 AM </option>'.
				'<option value="09.00">09:00 AM </option>'.
				'<option value="09.15">09:15 AM </option>'.
				'<option value="09.30">09:30 AM </option>'.
				'<option value="09.40">09:40 AM </option>'.
				'<option value="10.00">10:00 AM </option>'.
				'<option value="10.15">10:15 AM </option>'.
				'<option value="10.20">10:20 AM </option>'.
				'<option value="10.30">10:30 AM </option>'.
				'<option value="10.40">10:40 AM </option>'.
				'<option value="11.00">11:00 AM </option>'.
				'<option value="11.15">11:15 AM </option>'.
				'<option value="11.30">11:30 AM </option>'.
				'<option value="12.00">12:00 AM </option>'.
				'<option value="12.30">12:30 AM </option>'.
				'<option value="13.00">01:00 PM </option>'.
				'<option value="13.30">01:30 PM </option>'.
				'<option value="13.50">01:50 PM </option>'.
				'<option value="14.00">02:00 PM </option>'.
				'<option value="14.30">02:30 PM </option>'.
				'<option value="14.50">02:50 PM </option>'.
				'<option value="15.00">03:00 PM </option>'.
				'<option value="15.10">03:10 PM </option>'.
				'<option value="15.30">03:30 PM </option>'.
				'<option value="15.45">03:45 PM </option>'.
				'<option value="16.00">04:00 PM </option>'.
				'<option value="16.30">04:30 PM </option>'.
				'<option value="17.00">05:00 PM </option>'.
				'<option value="17.30">05:30 PM </option>'.
				'<option value="18.00">06:00 PM </option>'.
				'<option value="18.30">06:30 PM </option>'.
				'<option value="19.00">07:00 PM </option>'.
				'<option value="19.30">07:30 PM </option>'.
				'<option value="20.00">08:00 PM </option>'.
				'<option value="20.15">08:15 PM </option>'.
				'<option value="20.30">08:30 PM </option>'.
				'<option value="21.00">09:00 PM </option>'.
				'<option value="21:30">09:30 PM </option>';
	
		return  $hours;
	}
	
	/**
	 * Generate Age for child
	 */

	public function getSelectBoxOptions($options){
		$sl_opt = "";
		foreach($options as $key=>$opt){
			$sl_opt .= "<option value='".$key."'>".$opt."</option>";
		}
		return $sl_opt;
	}	
	
	/**
	 * get phone number in format
	 * @param string $str
	 * @return string
	 */
	public static function getPhoneNumber($str)
	{
		$str = str_replace(" ", "", $str);
		$firt = substr($str, 0,3);
		$second = substr($str, 3, strlen($str)-3);
		$phone = $firt." ".$second;
		return $phone;
	}
	
	/**
	 * Generate navigation for use global
	 * @author channy
	 * @param $url current of action
	 * @param $frm form for use cover of control 
	 * @param $limit number of limit record
	 * @return $record_count number of record
	 */
		public function getList($url,$frm,$start,$limit,$record_count){
			$page = new Application_Form_FrmNavigation($url, $start, $limit, $record_count);
			$page->init($url, $start, $limit, $record_count);//can wrong $form
			$nevigation = $page->navigationPage();
			$rows_per_page = $page->getRowsPerPage($limit, $frm);
			$result_row = $page->getResultRows();
			$arr = array(
					"nevigation"=>$nevigation,
					"rows_per_page"=>$rows_per_page,
					"result_row"=>$result_row);
			return $arr;
		}
		public function getAllMetionOption(){
			$_db = new Application_Model_DbTable_DbGlobal();
			$rows = $_db->getAllMention();
			$option = '';
			if(!empty($rows))foreach($rows as $key => $value){
				$option .= '<option value="'.$key.'" >'.htmlspecialchars($value, ENT_QUOTES).'</option>';
			}
			return $option;
		}
		public function getAllPayMentTermOption(){
			$_db = new Application_Model_DbTable_DbGlobal();
			$rows = $_db->getAllPaymentTerm();
			$option = '';
			if(!empty($rows))foreach($rows as $key => $value){
				$option .= '<option value="'.$key.'" >'.htmlspecialchars($value, ENT_QUOTES).'</option>';
			}
			return $option;
		}
		public function getAllFacultyOption(){
			$_db = new Application_Model_DbTable_DbGlobal();
			$rows = $_db->getAllMajor();
			array_unshift($rows, array('id'=>-1,'name'=>"select grade"));
			$options = '';
			if(!empty($rows))foreach($rows as $value){
				$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
			}
			return $options;
		}
		public function getAllSession(){
			$db=$this->getAdapter();
			$sql=" SELECT key_code AS id,CONCAT(name_en,'-',name_kh) AS `name` FROM rms_view WHERE `type`=4 AND `status`=1 ";
		    $rows=$db->fetchAll($sql);
		    $options = '';
		    if(!empty($rows))foreach($rows as $value){
		    	$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
		    }
		    return $options;
		}
		public function getAllServiceItemOption($type=null){
			$_db = new Application_Model_DbTable_DbGlobal();
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$rows = $_db->getAllstudentRequest($type);
			array_unshift($rows,array('id' => '-1',"name"=>$tr->translate("ADD_NEW")));
			array_unshift($rows,array('id' => '',"name"=>$tr->translate("SELECT_SERVICE"), ));
			$options = '';
			if(!empty($rows))foreach($rows as $value){
				$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
			}
			return $options;
		}
		public function getImgActive($rows,$base_url, $case='',$type=null){
			if($rows){
				$imgnone='<img src="'.$base_url.'/images/icon/cross.png"/>';
				$imgtick='<img src="'.$base_url.'/images/icon/apply2.png"/>';
				$request=Zend_Controller_Front::getInstance()->getRequest();
				foreach ($rows as $i =>$row){
					if($row['status'] == 1){
						$rows[$i]['status']= $imgtick;
					}
					else{
						$rows[$i]['status'] = $imgnone;
					}
					if($type!=null){
						if($row['type'] == 1){
							$rows[$i]['type']="Service" ;
						}
						else{
							$rows[$i]['type']="Program" ;
						}
					}
				}
			}
			return $rows;
		}
		public function getServiceProgramType($rows,$base_url, $case=''){
			if($rows){
				foreach ($rows as $i =>$row){
					if($row['type'] == 1){
						$rows[$i]['type']="Service" ;
					}
					else{
						$rows[$i]['type']="Program" ;
					}
				}
			}
			return $rows;
		}
		public function getGernder($rows,$base_url, $case=''){
			if($rows){
				foreach ($rows as $i =>$row){
					if($row['sex'] == 1){
						$rows[$i]['sex']="M" ;
					}
					else{
						$rows[$i]['sex']="F" ;
					}
				}
			}
			return $rows;
		}
		public function getGetPayTerm($rows,$base_url, $case=''){
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			if($rows){
				foreach ($rows as $i =>$row){
					if($row['payment_term'] == 2){
						$rows[$i]['payment_term']=$tr->translate(' ត្រីមាស');
					}
					else if($row['payment_term'] == 1){
						$rows[$i]['payment_term']=$tr->translate('MONTHLY');
					}
					else if($row['payment_term'] == 3){
						$rows[$i]['payment_term']=$tr->translate(' ឆមាស');
					}
					else if($row['payment_term'] == 4){
						$rows[$i]['payment_term']=$tr->translate('ឆ្នាំ');
					}
				}
			}
			return $rows;
		}
		public function getSession($rows,$base_url, $case=''){
			$imgnone='<img src="'.$base_url.'/images/icon/cross.png"/>';
			$imgtick='<img src="'.$base_url.'/images/icon/apply2.png"/>';
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			if($rows){
				foreach ($rows as $i =>$row){
					if($row['session_id'] == 1){
						$rows[$i]['session_id']=$tr->translate(' ព្រឹក');
						$rows[$i]['status']= $imgtick;
					}
					else if($row['session_id'] ==2){
						$rows[$i]['session_id']=$tr->translate('រសៀល');
					}
					else if($row['session_id'] == 3){
						$rows[$i]['session_id']=$tr->translate(' ល្ញាច');
					}
					else if($row['session_id'] == 4){
						$rows[$i]['session_id']=$tr->translate('ចុងសបា្តហ៏');
					}
				}
			}
			return $rows;
		}
		public function getsunjectOption(){
			$_db = new Application_Model_DbTable_DbGlobal();
			$rows = $_db->getAllsubject();
			//array_unshift($rows, array('id'=>-1,'subject_name'=>"Add New"));
			$options = '';
			if(!empty($rows))foreach($rows as $value){
				$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['subject_name'], ENT_QUOTES).'</option>';
			}
			$options .= '<option Value="-1">Add New</option>';
			return $options;
		}
		public function getTeachersunjectOption(){
			$_db = new Application_Model_DbTable_DbGlobal();
			$rows = $_db->getAllTeacherSubject();
			$options = '';
			if(!empty($rows))foreach($rows as $value){
				$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['subject_name'].' , '.$value['teacher_name'], ENT_QUOTES).'</option>';
			}
			$options .= '<option Value="-1">Add New</option>';
			return $options;
		}
		public function getAllExpenseIncomeType($type){
			$_db = new Application_Model_DbTable_DbGlobal();
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$rows = $_db->getExpenseIncome($type);
			$options = '';
			$options .= '<option Value="0">'.$tr->translate("SELECT_CATEGORY").'</option>';
			$options .= '<option Value="-1">'.$tr->translate("ADD_NEW").'</option>';
			if(!empty($rows))foreach($rows as $value){
				$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name']).'</option>';
			}
			
			return $options;
		}
		
		public function getAllDays(){
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$hours ='<option value="1">'.$tr->translate('MO').'</option>'.
					'<option value="2">'.$tr->translate('TU').'</option>'.
					'<option value="3">'.$tr->translate('WE').'</option>'.
					'<option value="4">'.$tr->translate('TH').'</option>'.
					'<option value="5">'.$tr->translate('FR').'</option>'.
					'<option value="6">'.$tr->translate('SA').'</option>'.
					'<option value="7">'.$tr->translate('SU').'</option>';
			return  $hours;
		}
		
		public function getHoursStudy(){
			$hours ='<option value="07.00">07:00 AM </option>'.
					'<option value="07.15">07:15 AM </option>'.
					'<option value="07.30">07:30 AM </option>'.
					'<option value="08.00">08:00 AM </option>'.
					'<option value="08.15">08:15 AM </option>'.
					'<option value="08.20">08:20 AM </option>'.
					'<option value="08.30">08:30 AM </option>'.
					'<option value="08.40">08.40 AM </option>'.
					'<option value="09.00">09:00 AM </option>'.
					'<option value="09.15">09:15 AM </option>'.
					'<option value="09.30">09:30 AM </option>'.
					'<option value="09.40">09:40 AM </option>'.
					'<option value="09.50">09:50 AM </option>'.
					'<option value="10.00">10:00 AM </option>'.
					'<option value="10.15">10:15 AM </option>'.
					'<option value="10.20">10:20 AM </option>'.
					'<option value="10.30">10:30 AM </option>'.
					'<option value="10.40">10:40 AM </option>'.
					'<option value="11.00">11:00 AM </option>'.
					'<option value="11.15">11:15 AM </option>'.
					'<option value="11.30">11:30 AM </option>'.
					'<option value="12.00">12:00 AM </option>'.
					'<option value="12.30">12:30 AM </option>'.
					'<option value="13.00">01:00 PM </option>'.
					'<option value="13.30">01:30 PM </option>'.
					'<option value="13.40">01:40 PM </option>'.
					'<option value="13.50">01:50 PM </option>'.
					'<option value="14.00">02:00 PM </option>'.
					'<option value="14.30">02:30 PM </option>'.
					'<option value="14.40">02:40 PM </option>'.
					'<option value="14.50">02:50 PM </option>'.
					'<option value="15.00">03:00 PM </option>'.
					'<option value="15.10">03:10 PM </option>'.
					'<option value="15.30">03:20 PM </option>'.
					'<option value="15.30">03:30 PM </option>'.
					'<option value="15.45">03:45 PM </option>'.
					'<option value="16.00">04:00 PM </option>'.
					'<option value="16.30">04:30 PM </option>'.
					'<option value="17.00">05:00 PM </option>'.
					'<option value="17.30">05:30 PM </option>'.
					'<option value="18.00">06:00 PM </option>'.
					'<option value="18.30">06:30 PM </option>'.
					'<option value="19.00">07:00 PM </option>'.
					'<option value="19.30">07:30 PM </option>'.
					'<option value="20.00">08:00 PM </option>'.
					'<option value="20.15">08:15 PM </option>'.
					'<option value="20.30">08:30 PM </option>'.
					'<option value="21.00">09:00 PM </option>'.
					'<option value="21.30">09:30 PM </option>';
			return  $hours;
	}
}

