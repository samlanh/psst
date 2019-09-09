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
	function getLetterHeaderReport($branch_id,$header_type=0){
		//$logo = Zend_Controller_Front::getInstance()->getBaseUrl().'/images/logo.png';
		//$branch_id = empty($branch_id)?1:$branch_id;
		$db = new Application_Model_DbTable_DbGlobal();
		if (empty($branch_id)){
			$optionBranch = $db->getAllBranch();
			if (count($optionBranch)==1){
				if(!empty($optionBranch))foreach($optionBranch AS $row){
					$branch_id = $row['id'];
				}
			}else {
				$branch_id = 1;
			}
			
		}
		$rs = $db->getBranchInfo($branch_id);
		$logo = Zend_Controller_Front::getInstance()->getBaseUrl().'/images/'.$rs['photo'];
		$color = empty($rs['color'])?"":"#".$rs['color'];
		$type_header = HEADER_REPORT_TYPE;
		if($header_type!=0){
			$type_header = $header_type;
		}
		$str="";
		if ($type_header==1){
		$str="<table width='100%'>
				<tr>
					<td width='20%' align='center'>
						<img style='max-height:100px;' src=".$logo."><br>
					</td>
					<td width='80%' valign='top'>
						<div class='schoo-headkh' style='text-align: center;'>
							<h2 style='padding: 0;margin: 0; font-family: Times New Roman , Khmer OS Muol Light;font-size:24px;background: $color;color: #fff;padding: 8px 0px;'>".$rs['school_namekh']."</h2>
						</div>
						<table width='100%' >
							<tr>
								<td width='60%' align='center' valign='top'>
									<h2 style='white-space:nowrap; font-weight:bold; font-size:16px; padding: 0;margin: 0; font-family: Times New Roman , Khmer OS Muol; color: #000;'>".$rs['school_nameen']."</h2>
								</td>
								<td width='40%' align='left' valign='top' style='white-space:nowrap;font-size: 12px;line-height: 14px;font-family: Times New Roman , Khmer OS Battambang;'>
									Contacts: ".$rs['branch_tel']."<br />
									<span style='visibility: hidden;'>Contacts: </span>".$rs['branch_tel1']."
								</td>
							</tr>
						</table>
						<div class='schoo-add' style='text-align: center; font-size: 13px;font-family: Times New Roman , Khmer OS Battambang;'>
							 ".$rs['br_address'];
							if (!empty($rs['email'])){
								$str.=", E-mail: ".$rs['email'];
							}
							if (!empty($rs['website'])){
								$str.=", Website: ".$rs['website'];
							}
							$str.="
						</div>
					</td>
				</tr>
		</table>";
		}else if ($type_header==2){
			$str="
			<style>
				span.space {
					padding:0;
				    padding-right: 10px;
				    margin:0;
				        line-height: inherit;
				}
			</style>
			<table width='100%'>
					<tr>
						<td width='20%' align='center'>
							<img style='max-height:100px;' src=".$logo."><br>
						</td>
						<td width='80%' valign='top'>
							<table width='100%' >
								<tr>
									<td width='60%' align='left' valign='top'>
										<h2 style='padding: 0;margin: 0; font-weight:normal; font-family: Times New Roman , Khmer OS Muol Light;font-size:18px; color: inherit;padding: 8px 0px;'>".$rs['school_namekh']."</h2>
										<h2 style='white-space:nowrap; font-weight:bold; font-size:14px; padding: 0;margin: 0; font-family: Times New Roman , Khmer OS Muol; color: #inherit;'>".$rs['school_nameen']."</h2>
									</td>
									<td width='40%' align='left' valign='top'>
										<ul style='font-size:12px; color:inherit; list-style-type: none; padding: 0; margin: 0; line-height: initial;'>
											<li><span class='space'>&#9742;</span> ".$rs['branch_tel']."</li>";
											if (!empty($rs['email'])){
												$str.="<li><span class='space' style='font-size:15px;' >&#9993;</span> ".$rs['email']."</li>";
											}							
											if (!empty($rs['website'])){
												$str.="<li><span class='space'>&#127758;</span> ".$rs['website']."</li>";
											}
											$str.="<li><span class='space' style='font-size:15px;'>&#127963;</span> ".$rs['br_address']."</li>
										</ul>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					</table>";	
		}else if ($type_header==3){	
			$str="
			<style>
				span.space {
					padding:0;
				    padding-right: 10px;
				    margin:0;
				        line-height: inherit;
				}
			</style>
			<table width='100%'>
				<tr>
					<td width='20%' align='center'>
						<img style='width:100%' src=".$logo."><br>
					</td>
					<td width='80%' valign='top'>
						<h2 style='padding: 0;margin: 0; font-weight:normal; font-family: Times New Roman , Khmer OS Muol Light;font-size:24px; color: inherit;padding: 8px 0px;'>".$rs['school_namekh']."</h2>
						<h2 style='white-space:nowrap; font-weight:bold; font-size:16px; padding: 0;margin: 0; font-family: Times New Roman , Khmer OS Muol; color: #inherit;'>".$rs['school_nameen']."</h2>
					</td>
				</tr>
			</table>";	
		}
		return $str;
	}
	
	function getFooterAccount($spacing=1,$font_size="12px",$font_family="Times New Roman,Khmer OS Muol Light;"){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str="<table width='100%' style='font-size: $font_size;font-family:$font_family'>";
			for($i=1;$i<=$spacing;$i++){
				$str.="<tr><td>&nbsp;</td></tr>";
			}
		$str.="	<tr>
					<td width='25%' align='center'>
						<span>".$tr->translate('APPROVED_BY')."</span>
					</td>
					<td width='50%' align='center'>
						<span>".$tr->translate('VERIFIED_BY')."</span>
					</td>
					<td width='25%' align='center'>
						<span>".$tr->translate('PREPARED_BY')."</span>
					</td>
				</tr>
			</table>";
		return $str;
	}
	
}