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
	
	public function getHeaderReceipt(){
		
		$key = new Application_Model_DbTable_DbKeycode();
		$setting = $key->getKeyCodeMiniInv(TRUE);
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str="";
		
		if($setting['show_header_receipt']==1){
			$str="<table width='100%' style='white-space:nowrap;'>
					<tr>
						<td width='17%' valign='top'>
							<img style='width: 70%' src=".Zend_Controller_Front::getInstance()->getBaseUrl().'/images/logo.png'.">
						</td>
						<td width='83%' valign='top' style='font-size:11px;line-height: 18px;font-family: Khmer OS Battambang;' >
							<div style='font-size:22px;margin-top: 10px;'>".$tr->translate('SCHOOL_NAME')."</div>
							<div style='line-height: 18px;'>".$tr->translate('CUSTOMER_ADDRESS')."</div>
							<div style='line-height: 18px;'>".$tr->translate('CUSTOMER_TEL')."</div>
						</td>
					</tr>
				</table>";
		}
		return $str;
	}
}

