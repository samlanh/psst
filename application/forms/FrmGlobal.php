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
	function getLetterHeaderReport($branch_id){
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
	
	function getLeftLogo($branch_id){
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
		$str="
			<style>
				span.space {
					padding:0;
					padding-right: 10px;
					margin:0;
					line-height: inherit;
				}
			</style>
			<table width='100%' style='white-space:nowrap;'>
				<tr>
					<td width='20%' align='left'>
						<img style='width:80%' src=".$logo."><br>
					</td>
					<td width='60%' valign='top'>
						<h2 style='margin: 0; font-weight:normal; font-family: Times New Roman , Khmer OS Muol Light;font-size:11px; color: inherit;padding: 5px 0px 5px 0px;'>".$rs['school_namekh']."</h2>
						<h2 style='white-space:nowrap; font-weight:bold; font-size:10px; margin: 0; font-family: Times New Roman , Khmer OS Muol; color: #inherit;'>".$rs['school_nameen']."</h2>
					</td>
					<td width='20%' >
					</td>
				</tr>
			</table>
		";
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
	function getFormatReceipt(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$last_name=$session_user->last_name;
		$username = $session_user->first_name;
		
		$str="<style>
			.hearder_table{height:20px !important;}
			.defaulheight{line-height:10px !important;}
			.bold{
				font-weight:bold;
			}
			.blogbranchlogo{
					margin:0 auto;position:absolute;top:50px !important;left:100px;
				}
		</style>
		<div id='PrintReceipt' style='width:100%cm !important; padding: 0px;'>
			<style>
				.noted{
				    white-space: pre-wrap;     
					word-wrap: break-word;      
					word-break: break-all;
					white-space: pre;
					font:12px 'Khmer OS Battambang';
					border: 1px solid #000;
                    line-height:20px;
					font-weight: normal !important;
					padding:2px;
				    white-space: normal;
				}
				.blogbranchlogo{
					margin:0 auto;position:absolute;top:10px;left:100px;
				}
				.boxnorefund{
					color: #fff;
    				background: #d42727;
    				border: 2px solid fff;
    				font-size: 11px;
    				padding:10px 2px;
   	 				border-radius: 2px;
    				border: 6px double #fff;
    				font-weight:bold !important;
    				font-family:Times New Roman;	
				}
				#printfooter {
				    position: absolute;
				    bottom: 0;
				    position: fixed;
				    display: block ;
				    width:100%;
				}
				
				@page {
				  /* Chrome sets own margins, we change these printer settings */
				  margin:0.5cm 1cm 0.3cm 1cm; '
				   page-break-before: avoid;
				   /*size: 21cm 14.8cm; */
				}
				   
			</style>
			<table width='100%'  class='print' cellspacing='0'  cellpadding='0' style='font-family:Khmer OS Battambang,Times New Roman !important; font-size:11px !important; margin-top: -5px;white-space:nowrap;'>
				<tr>
					<td align='center' valign='top' colspan='3'>
						<label id='lbl_header'></label>
					</td>
				</tr>
				<tr>
					<td width='30%'>
						<div id='lbl_branchlogo'></div>
						<div class='blogbranchlogo' style='font-family:Khmer OS Muol Light;font-size:12px;'>
							<label id='lb_branchname'></label>
							<div style='line-height:10px;'><label id='lb_branchnameen'></label></div>
						</div>
					</td>
					<td align='center' valign='bottom' width='40%'>
						<div style='font-family:Khmer OS Muol Light;line-height:15px;font-size:12px;position:relative'>បង្កាន់ដៃបង់ប្រាក់</div>
						<div style='font-family:Times New Roman;font-size:12px;font-weight:bold'>Official Receipt</div>
					</td>
					<td width='30%'>&nbsp;</td>
				</tr>
				<tr>
					<td align='center' valign='bottom' colspan='3'>
						<table  width='100%' style='font-size: 11px;line-height:9px !important;'>
							<tr>
								<td width='15%'>Student ID </td>
								<td width='25%'> : &nbsp;<label id='lb_stu_id' class='one bold'></label></td>
								<td width='15%'><div style='font-size: 12px;font-family:Times New Roman;'><u>Receipt N<sup>o</sup></u></div></td>
								<td width='25%'> : &nbsp;<label id='lb_receipt_no'></label></td>
								<td rowspan='4' width='25%'><div style='border:1px solid #000;margin:0 auto;position:absolute;top:35px;width:70px;height:85px;right:0.2cm'><label id='lb_photo'></label></div></td>
							</tr>
							<tr>
								<td>Student Name</td>
								<td colspan='1'> : &nbsp;<label id='lb_name' class='one bold'></label></td>
								<td><div style='font-size: 12px;font-weight: bold;font-family: Times New Roman'>Pay Date</div></td>
								<td> : &nbsp;<label id='lb_date' class='one bold'></label></td>
							</tr>
							<tr>
								<td>Gender </td>
								<td> : &nbsp;<label id='lb_sex' class='one bold'></label></td>
								<td>Print Date</td>
								<td> : &nbsp;".date('d-m-Y g:i A')."</td>
							</tr>
							<tr>
								<td>Tel</td>
								<td> : &nbsp;<label id='lb_phone' class='one bold'></label><label id='lb_session' class='one bold'></label><label id='lb_study_year' class='one bold'></label></td>
								<td>Print By :</td>
								<td> : &nbsp;".$username."</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan='3'><div id='t_amountmoneytype'></div></td>
				</tr>
				<tr>
					<td valign='top' style='font-size:10px;'>Note
						<div style='width:99%;float: left;'>
						 	<div style='font-size:10px;min-height:70px;border:1px solid #000;' id='lbl_note' class='noted' ></div>
						 </div>
					</td>
					<td valign='top' style='font-size:10px;'>
						Say in US Dollars
						<div style='font-size:10px;min-height: 70px;border:1px solid #000;' id='lb_read_khmer' class='noted' ></div>
					</td>
					<td>
						<table width='98%' style='margin-left:4px;marin-top:5px;font-size:12px; white-space:nowrap;line-height:12px;border-collapse:collapse;'>
							<tr>
								<td>Penalty</td>
								<td>: $</td>
								<td align='right'>&nbsp;&nbsp; <label id='lb_fine'></label></td>
							</tr>
							<tr>
								<td>Total Payment</td>
								<td>: $</td>
								<td align='right' style='font-weight: bold;font-family:Times New Roman;'>&nbsp;&nbsp; <label id='lb_total_payment'></label></td>
							</tr>
							<tr>
								<td><div>Credit Memo</div></td>
								<td>: $</td>
								<td align='right'>&nbsp;&nbsp; <label id='lb_credit_memo'></label></td>
							</tr>
							<tr>
								<td><div>Paid Amount</div></td>
								<td>: $</td>
								<td align='right' style='font-weight: bold;font-family:Times New Roman;'>&nbsp;&nbsp; <label id='lb_paid_amount'></label></td>
							</tr>
							<tr>
								<td><div>Balance</div></td>
								<td>: $</td>
								<td align='right'>&nbsp;&nbsp;<label id='lb_balance_due'></label></td>
							</tr>
							<tr>
								<td><div>Payment Method</div></td>
								<td></td>
								<td align='right'>&nbsp;&nbsp;<label id='lb_paymentmethod'></label></td>
							</tr>
							<tr>
								<td><div>Number/Bank</div></td>
								<td></td>
								<td align='right'>&nbsp;&nbsp;<label id='lb_paymentnumber'></label></td>
							</tr>
							<tr>
								<td colspan='3'><div class='boxnorefund'>Non-Refundable / Transferable</div></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td valign='top' colspan='3'>
						<table class='defaulheight' width='100%' border='0' style='font-family: Khmer OS Battambang,Times New Roman;font-size:12px;white-space:nowrap;margin-top:-5px;line-height: 11px;'>
							<tr>
								<td colspan='5'>
									<table width='100%' style='marin-top:5px;font-size:12px; white-space:nowrap;line-height:15px;border-collapse:collapse;'>
										<tr>
											<td align='center'>Cashier</td>
											<td align='center'>Head of Cashier</td>
											<td align='center'>Customer</td>
										</tr>
										<tr>
											<td align='center'>
												<div style='font-size:10px;border-bottom: 1px solid #000;margin-top:50px;'><label id='lb_byuser'></label>";
												  	
												$str.="</div>
												Signature/Name/Date
											</td>
											<td align='center' valign='bottom'>
												<div style='border-bottom: 1px solid #000;width:85%;margin:0 auto;'></div>
												Signature/Name/Date
											</td>
											<td align='center' valign='bottom'>
												<div style='border-bottom: 1px solid #000;width:85%;margin:0 auto;'></div>
												Signature/Name/Date
											</td>
										</tr>
									</table>
								</td>
								<td valign='top'>
								</td>
							</tr>
						</table>
					</td>
				</tr>
		        <tr>
				    <td valign='top' colspan='3'>
					    <div id='printfooter' style='display:block;font-family:khmer os battambang'>
			        		<table width='100%' style='background: #fff;border-top: 2px solid #000;font-family: 'Times New Roman','Khmer OS Battambang'; font-size:8px;line-height: 12px;white-space:nowrap;'> 
								<tr style='text-align:center;white-space:nowrap;line-height: 15px;font-size:10px !important;font-family: 'Times New Roman','Khmer OS Battambang'>
									<td width='100%'> &#9993; <label id='lbl_email' style='width:20%;display:in-line;margin-right:10px;'></label> &#127758 <label id='lbl_website'style='width:20%;display:in-line;margin-right:10px;'></label> &#127963 <label id='lbl_address' style='font-family:'Times New Roman,Khmer OS Battambang !important'></label> </td>
								</tr>
							</table>
			        	</div>
		        	</td>
				</tr>
			</table>
		</div>";
		return $str;
	}
}