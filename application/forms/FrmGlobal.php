<?php

class Application_Form_FrmGlobal{
	public function getReceiptFooter(){
		$_dbmodel = new Application_Model_DbTable_DbKeycode();
		$keycode=$_dbmodel->getKeyCodeMiniInv(TRUE);
			$str="";
			$str.="<tr bgcolor='6D5CDD'>";
				$str.='<td colspan="4" style="text-align: center; color:#fff;background:#6D5CDD;">';
				$brachs = explode('/',$keycode['footer_branch']);
					$str.='<ul style="list-style-type: none;float:left; text-align: left;padding-left:10px;">';
						foreach ($brachs AS $key =>$branch){
							$str.="<li> $branch;</li>";
						}
					$str.='</ul>';
					$phones = explode('/',$keycode['foot_phone']);
					$str.='<ul style="list-style-type: none;float:left;text-align: left;padding-left:10px;">';
						foreach ($phones AS $key =>$phone)
							$str.="<li> $phone </li>";
					$str.='</ul>';
					$contacts= explode('/',$keycode['f_email_website']);
					$str.='<ul style="list-style-type: none;float:left;text-align: left;padding-left:10px;">';
						foreach ($contacts AS $key =>$contact){
							$str.="<li> $contact </li>";
						}
					$str.='</ul>';
				$str.='</td>';
			$str.='</tr>';						
				return $str;
	}
	
	public function getHeaderReceipt($branch_id=null){
		$key = new Application_Model_DbTable_DbKeycode();
		$setting = $key->getKeyCodeMiniInv(TRUE);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str="";
		if($branch_id==null){
			$img = 'logo.png';
			$school_khname = $tr->translate('SCHOOL_NAME');
			$school_name = $tr->translate('CUSTOMER_BRANCH_EN');
			$address = $tr->translate('CUSTOMER_ADDRESS');
			$tel = $tr->translate('CUSTOMER_TEL');
			$email =  $tr->translate('CUSTOMER_EMAIL');
			$website = $tr->translate('CUSTOMER_WEBSITE');
		}else{
			$db = new Application_Model_DbTable_DbGlobal();
			$rs = $db->getBranchInfo($branch_id);
			if(!empty($rs)){
				$img = $rs['photo'];
				$school_khname = $rs['school_namekh'];
				$school_name = $rs['school_nameen'];
				
				$address = $rs['br_address'];
				$tel = $rs['branch_tel'];
				$email = $rs['email'];
				$website = $rs['website'];
			}
		}
		if($setting['show_header_receipt']==1){
			$str="<table width='100%' style='white-space:nowrap;'>
				<tr>
					<td width='17%' valign='top'>
						<img style='max-width: 98%;max-height:90px;' src=".Zend_Controller_Front::getInstance()->getBaseUrl().'/images/'.$img.">
					</td>
					<td width='43%' valign='top' style='font-size:11px;line-height: 18px;font-family: Khmer OS Battambang;' >
						<div style='font-size:18px;margin-top: 10px;font-family:Khmer OS Muol Light'>".$school_khname."</div>
						<div style='font-size:18px;font-family:Times New Roman'>".$school_name."</div>
						<div style='line-height: 16px;margin-top: 2px;'>".$address."</div>
					</td>
					<td width='40%' valign='top' style='font-size:11px;line-height: 18px;font-family: Khmer OS Battambang;' >
						<div style='line-height: 16px;'>&nbsp;</div>
						<div style='line-height: 16px;'>".$tel."</div>
						<div style='line-height: 16px;'>".$email."</div>
						<div style='line-height: 16px;'>".$website."</div>
					</td>
				</tr>
			</table>";
		}
		return $str;
	}
}