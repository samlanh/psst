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
	public function getHeaderReceipt($branch_id=null,$forGenerate=0){
		$key = new Application_Model_DbTable_DbKeycode();
		$setting = $key->getKeyCodeMiniInv(TRUE);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str="";
		
		if ($forGenerate==1){
			$baseUrl =PUBLIC_PATH;
			$styleLogo = "width:120px;";
		}else{
			$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
			$styleLogo = "max-width: 98%;max-height:90px;min-height:50px;";
		}
		$img = 'logo.png';
		
		$logo = $baseUrl.'/images/Logo/Logo.png';
		if(!empty($setting['logo'])){
			if (file_exists(PUBLIC_PATH."/images/logo/".$setting['logo'])){
				$logo = $baseUrl.'/images/logo/'.$setting['logo'];
			}
		}
		
		$school_khname = $tr->translate('SCHOOL_NAME');
		$school_name = $tr->translate('CUSTOMER_BRANCH_EN');
		$address = $tr->translate('CUSTOMER_ADDRESS');
		$tel = $tr->translate('CUSTOMER_TEL');
		$email =  $tr->translate('CUSTOMER_EMAIL');
		$website = $tr->translate('CUSTOMER_WEBSITE');
		if($branch_id==null){
			
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
				
				 if (!empty($rs['photo'])){
					if (file_exists(PUBLIC_PATH."/images/logo/".$rs['photo'])){
						$logo = $baseUrl.'/images/logo/'.$rs['photo'];
					}
				}	
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
					<td width='22%' valign='top'>
						<img style='".$styleLogo."' src=".$logo.">
					</td>
					<td width='48%' valign='top' style='font-size:11px;line-height: 18px;font-family: Khmer OS Battambang;' >
						<div style='font-size:18px;margin-top: 10px;line-height:25px;font-family:Khmer OS Muol Light'>".$school_khname."</div>
						<div style='font-size:18px;font-family:Times New Roman'>".$school_name."</div>
						<div style='font-size:10px;line-height: 16px;margin-top: 2px;max-width:100%;white-space:pre-line;'>".$address."</div>
					</td>
					<td width='30%' valign='top' style='font-size:10px;line-height: 18px;font-family: Khmer OS Battambang;' >
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
		$color = empty($rs['color'])?"#000":"#".$rs['color'];
		$type_header = HEADER_REPORT_TYPE;
		$str="";
		
		
		$email_icon = Zend_Controller_Front::getInstance()->getBaseUrl().'/images/icon/email.png';
		$global_icon = Zend_Controller_Front::getInstance()->getBaseUrl().'/images/icon/global.png';
		$home_icon = Zend_Controller_Front::getInstance()->getBaseUrl().'/images/icon/home.png';
		
		if ($type_header==1){
		$str="
		<style>
				table{
				color:".$color."
				}
		</style>
		<table width='100%'>
				<tr>
					<td width='20%' align='center'>
						<img style='max-height:100px;' src=".$logo."><br>
					</td>
					<td width='80%' valign='top'>
						<div class='schoo-headkh' style='text-align: center;'>
							<h2 style=".'"'."padding: 0;margin: 0; font-family: 'Times New Roman','Khmer OS Muol Light';font-size:24px;background: $color;color: #fff;padding: 8px 0px;".'"'.">".$rs['school_namekh']."</h2>
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
			$str='
				<style>
					table{
					color:".$color."
					}
					span.space {
						padding:0;
						padding-right: 10px;
						margin:0;
							line-height: inherit;
					}
					img.icon-head {
						width: 12px;
						filter: sepia(100%) hue-rotate(190deg) saturate(500%);
					}
					ul.headReport,
					ul.reportTitle{
						margin: 0;
						padding: 0;
						list-style: none;
					}
					ul.headReport li span,
					ul.headReport li{
						line-height: 18px;
						text-align:left; 
						font-size:14px;
						font-family:'.'"Times New Roman"'.','.'"Khmer OS Muol Light"'.';
						
					}
					ul.headReport li.small-text,
					ul.headReport li.small-text span{
						line-height: 14px;
						text-align:center; 
						font-size:11px;
						font-family:'.'"Times New Roman"'.','.'"Khmer OS Battambang"'.';
						
					}
					</style>
			';
			$str.="
			
				
			
			<table class='tableTop' width='100%'>
					<tr>
						<td width='20%' align='center'>
							<img style='max-height:100px;' src=".$logo."><br>
						</td>
						<td width='80%' valign='top'>
							<table width='100%' >
								<tr>
									<td width='60%' align='left' valign='top'>
										<h2 style=".'"'."padding: 0;margin: 0; font-weight:normal; font-family: 'Times New Roman' , 'Khmer OS Muol Light';font-size:18px; color: inherit;padding: 8px 0px;".'"'.">".$rs['school_namekh']."</h2>
										<h2 style='white-space:nowrap; font-weight:bold; font-size:14px; padding: 0;margin: 0; font-family: Times New Roman , Khmer OS Muol; color: #inherit;'>".$rs['school_nameen']."</h2>
									</td>
									<td width='40%' align='left' valign='top'>
										<ul class='headReport'>
											<li><span class='space'>&#9742;</span> ".$rs['branch_tel']."</li>";
											if (!empty($rs['email'])){
												$str.="<li><span class='space'>&#128386;</span> ".$rs['email']."</li>";
											}							
											if (!empty($rs['website'])){
												$str.="<li><span class='space'>🌐</span> ".$rs['website']."</li>";
											}
											$str.="<li><span class='space'>&#127988;</span> ".$rs['br_address']."</li>
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
				table{
					color:".$color."
					}
			</style>
			<table width='100%' class='tableTop'>
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
		}else if ($type_header==4){	
		$str='
		<style>
			tr.line td {
				    display: none !important;
			}
		</style>
		<table width="100%" style="white-space:nowrap;">
			<tbody>
				<tr>
					<td width="25%" valign="top">
						<img style="max-width: 98%;max-height:140px;  margin-top:25px;" src="'.$logo.'">
					</td>
					<td width="35%" valign="top" style="font-size:11px;line-height: 18px;font-family: Khmer OS Battambang;">
					</td>
					<td width="40%" valign="top" style="font-family: '."'Times New Roman'".','."'Khmer OS Muol Light'".';">
						
					</td>
				</tr>
			</tbody>
		</table>
		';
		}
		return $str;
	}
	
	function getLeftLogo($branch_id){
		
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
		$receipt_type = RECEIPT_TYPE;
		if($receipt_type==1){//elt
			$str="<style>
				.hearder_table{height:20px !important;}
				.defaulheight{line-height:10px !important;}
				.bold{
					font-weight:bold;
				}
				.blogbranchlogo{
						margin:0 auto;position:absolute;top:10px !important;left:100px;
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
					table{ border-collapse:collapse; margin:0 auto;
								border-color:#000;font-size:12px; }
					@page {
					  /* Chrome sets own margins, we change these printer settings */
					  margin:0.5cm 1cm 0.3cm 1cm; '
					   page-break-before: avoid;
					   /*size: 21cm 14.8cm; */
					}
					   
				</style>
				<table  width='100%'  class='print' cellspacing='0'  cellpadding='0' style='font-family:Khmer OS Battambang,Times New Roman !important; font-size:11px !important; margin-top: -5px;white-space:nowrap;'>
					<tr>
						<td align='center' valign='top' colspan='3'>
							<label id='lbl_header'></label>
						</td>
					</tr>
					<tr>
						<td width='30%' style='position:relative'>
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
							<table  width='100%' style='font-size: 11px;line-height:12px !important;margin-bottom:10px;'>
								<tr>
									<td width='12%'>Student ID/Test ID </td>
									<td width='18%'> : &nbsp;<label id='lb_stu_id' class='one bold'></label></td>
									<td><div style='font-family: Times New Roman'>Academic Year	</div></td>
									<td> : &nbsp;<label id='lb_academic_year' class='one'>&nbsp;</label>
									<td width='15%'><div style='font-size: 12px;font-family:Times New Roman;'><u>Receipt N<sup>o</sup></u></div></td>
									<td width='15%'> : &nbsp;<label id='lb_receipt_no'></label></td>
									<td  width='15%'><div style='border:1px solid #000;margin:0 auto;position:absolute;top:35px;width:70px;height:85px;right:0.2cm'><label id='lb_photo'></label></div></td>
								</tr>
								<tr>
									<td>Student Name</td>
									<td colspan='1'> : &nbsp;<label id='lb_name' class='one bold'></label></td>
									<td><div style='font-family: Times New Roman'>Session Type</div></td>
									<td> : &nbsp;<label id='lb_sesiontype' class='one'>&nbsp;</label></td>
									<td><div style='font-size: 12px;font-weight: bold;font-family: Times New Roman'>Pay Date</div></td>
									<td> : &nbsp;<label id='lb_date' class='one bold'></label></td>
								</tr>
								<tr>
									<td>Gender </td>
									<td> : &nbsp;<label id='lb_sex' class='one bold'></label></td>
									<td><div style='font-family: Times New Roman'>Grade	</div></td>
									<td style='white-space: nowrap;'> : &nbsp;<label id='lb_grade' class='one'>&nbsp;</label>
									<td>Print Date</td>
									<td> : &nbsp;".date('d-m-Y g:i A')."</td>
								</tr>
								<tr>
									<td>Tel</td>
									<td> : &nbsp;<label id='lb_phone' class='one bold'></label><label id='lb_session' class='one bold'></label><label id='lb_study_year' class='one bold'></label></td>
									<td>Class</td>
									<td> : &nbsp;<label id='lb_group' class='one'>&nbsp;</td>
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
							<table width='98%' style='margin-left:4px;marin-top:5px;font-size:inherit; white-space:nowrap;line-height:12px;border-collapse:collapse;'>
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
									<td><div><strong>Paid Amount</strong></div></td>
									<td>: $</td>
									<td align='right' style='font-weight: bold;font-family:Times New Roman;'>&nbsp;&nbsp;<strong><label id='lb_paid_amount'></label></strong></td>
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
													<div style='font-size:10px;border-bottom: 1px solid #000;margin-top:40px;'><label id='lb_byuser'></label>";
													  	
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
									<tr style='text-align:center;white-space:nowrap;line-height: 15px;font-size:7px !important;font-family: 'Times New Roman','Khmer OS Battambang'>
										<td width='100%'>&#9742; <label id='lbl_branchphone' style='width:20%;display:in-line;'></label> &#9993; <label id='lbl_email' style='width:20%;display:in-line;'></label> &#127758 <label id='lbl_website'style='width:20%;display:in-line;'></label> &#127963 <label id='lbl_address' style='font-family:'Times New Roman,Khmer OS Battambang !important'></label> </td>
									</tr>
								</table>
				        	</div>
			        	</td>
					</tr>
				</table>
				<div class='no_display'>
					<span id='lb_grade'></span>
					<span id='lb_academic_year'></span>
					
					<span id='lbParentName' >&nbsp;</span>
					<span id='lbParentPhone' >&nbsp;</span>
				</div>
			</div>
			";
			$key = new Application_Model_DbTable_DbKeycode();
			$result=$key->getKeyCodeMiniInv(TRUE);
			if($result['receipt_print']>1){
				$str.="<div id='divPrint1'>
				<div style='border:1px dashed #000; vertical-align: middle;margin:10px 0px 10px 0px'></div>
				<div id='printblog2'></div>
				</div>";
			}
		return $str;
		}elseif($receipt_type==2){//newworld
			$str="<style>
			.hearder_table{height:20px !important;}
			.defaulheight{line-height:10px !important;}
			.bold{
			font-weight:bold;
			}
			.blogbranchlogo{
			margin:0 auto;position:absolute;top:10px !important;left:100px;
			}
			</style>
			<div id='PrintReceipt' style='width:100% !important; padding:0px;height:13.7cm;position:relative'>
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
			table{ border-collapse:collapse; margin:0 auto;
								border-color:#000;font-size:12px; }
			#printfooter {
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
			<table width='100%'  class='print' cellspacing='0'  cellpadding='0' style='height:10cm;font-family:Khmer OS Battambang,Times New Roman !important; font-size:11px !important; margin-top: -5px;white-space:nowrap;'>
				<tr>
					<td width='30%' style='position:relative'>
						<div id='lbl_branchlogo'>
						</div>
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
			<td width='15%'>Student ID/Test ID </td>
			<td width='25%'> : &nbsp;<label id='lb_stu_id' class='one bold'></label></td>
			<td width='15%'><div style='font-size: 12px;font-family:Times New Roman;'><u>Receipt N<sup>o</sup></u></div></td>
			<td width='25%'> : &nbsp;<label id='lb_receipt_no'></label></td>
			<td rowspan='4' width='25%'><div style='display:none;border:1px solid #000;margin:0 auto;position:absolute;top:35px;width:70px;height:85px;right:0.2cm'><label id='lb_photo'></label></div></td>
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
			<tr height='50px'>
				<td valign='top' style='font-size:10px;'>Note
					<div style='width:99%;float: left;'>
					<div style='font-size:10px;min-height:70px;border:1px solid #000;' id='lbl_note' class='noted' ></div>
					</div>
				</td>
				<td valign='top' style='font-size:10px;'>
					Say in US Dollars
					<div style='font-size:10px;min-height: 70px;border:1px solid #000;' id='lb_read_khmer' class='noted' ></div>
				</td>
				<td rowspan='2'>
					<table width='98%' style='margin-left:4px;marin-top:5px;font-size:inherit; white-space:nowrap;line-height:12px;border-collapse:collapse;'>
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
							<td><div><strong>Paid Amount</strong></div></td>
							<td>: $</td>
							<td align='right' style='font-weight: bold;font-family:Times New Roman;'>&nbsp;&nbsp; <strong><label id='lb_paid_amount'></label></strong></td>
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
				<td valign='top' colspan='2'>
					<table width='100%' style='font-size:12px; white-space:nowrap;line-height:12px;'>
						<tr>
							<td width='33%' align='center'>Cashier
								<div style='font-size:10px;border-bottom: 1px solid #000;margin-top:40px;'><label id='lb_byuser'></label></div>
								Signature/Name/Date
							</td>
							<td width='33%' align='center'>Head of Cashier
								<div style='border-bottom: 1px solid #000;width:85%;margin-top:40px;'><label>&nbsp;</label></div>
								Signature/Name/Date
							</td>
							<td width='33%' align='center'>Customer
								<div style='border-bottom: 1px solid #000;width:85%;margin:0 auto;margin-top:40px;'>&nbsp;</div>
								Signature/Name/Date
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
			<td valign='top' colspan='3'>
				<table class='defaulheight' width='100%' border='0' style='font-family: Khmer OS Battambang,Times New Roman;font-size:12px;white-space:nowrap;margin-top:-5px;line-height: 11px;'>
			<tr>
			<td colspan='5'>";
			$str.="
					</td>
					<td valign='top'>
					</td>
				</tr>
			</table>
			</td>
			</tr>
			</table>
				<div id='printfooter' style='display:block;font-family:khmer os battambang;position: absolute; bottom:0px;width:100%'>
					<table style='width:100%;margin-top:10px;background: #fff;border-top: 1px solid #000;font-family: 'Times New Roman','Khmer OS Battambang'; font-size:8px;line-height: 12px;white-space:nowrap;'>
						<tr style='text-align:center;white-space:nowrap;line-height: 15px;font-size:7px !important;font-family: 'Times New Roman','Khmer OS Battambang'>
							<td >&#9742; <label id='lbl_branchphone' style='width:20%;display:in-line;'></label> &#9993; <label id='lbl_email' style='width:20%;display:in-line;'></label> &#127758 <label id='lbl_website'style='width:20%;display:in-line;'></label> &#127963 <label id='lbl_address' style='font-family:'Times New Roman,Khmer OS Battambang !important'></label> </td>
						</tr>
					</table>
				</div>
				
				<div class='no_display'>
					<span id='lb_grade'></span>
					<span id='lb_academic_year'></span>
					
					<span id='lbParentName' >&nbsp;</span>
					<span id='lbParentPhone' >&nbsp;</span>
				</div>
				
				
			</div>
			
			";
			$key = new Application_Model_DbTable_DbKeycode();
			$result=$key->getKeyCodeMiniInv(TRUE);
			if($result['receipt_print']>1){
				$str.="<div id='divPrint1'>
						<div style='border:1px dashed #000; vertical-align: middle;margin:10px 0px 10px 0px'></div>
						<div id='printblog2'></div>
					</div>";
			}
			return $str;
		}elseif($receipt_type==3){//psis
			$str="<style>
					.hearder_table{height:20px !important;}
					.defaulheight{line-height:10px !important;}
					.bold{
						font-weight:bold;
					}
					.blogbranchlogo{
						margin:0 auto;position:absolute;top:10px !important;left:100px;
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
							line-height:15px;
							font-weight: normal !important;
							padding:2px;
							white-space: normal;
							width:95%;
						}
						.notedDescription{
							white-space: pre-wrap;
							word-wrap: break-word;
							word-break: break-all;
							white-space: pre;
							font:12px 'Khmer OS Battambang';
							font-size:10px;
							line-height:15px;
							font-weight: normal !important;
							white-space: normal;
							width:200px;
						}
						table{ border-collapse:collapse; margin:0 auto;
								border-color:#000;font-size:10px; }
						.blogbranchlogo{
							margin:0 auto;position:absolute;top:10px;left:100px;
						}
						.boxnorefund{
							color: #fff;
							background: #d42727;
							border: 2px solid fff;
							font-size: 10px;
							padding:10px 2px;
							border-radius: 2px;
							border: 6px double #fff;
							font-weight:bold !important;
							font-family:Times New Roman;
						}
						.hearder_table small{
							display:block;
							line-height:15px;
						}
						.spanBlog{
							display:block !important;
							line-height:12px;
							font-size:10px;
						}
						.print label{
							margin-bottom: 0px !important;
						}
						@page {
							margin:0cm 0.7cm 0cm 0.7cm;
							page-break-before: avoid;
							/*size: A4 landscape;*/
							-webkit-transform: scale(0.5);  /* Chrome, Safari 3.1+  */
							-moz-transform: scale(0.5);  /* Firefox 3.5-15 */
							-ms-transform: scale(0.5);   /* IE 9 */
							-o-transform: scale(0.5);    /* Opera 10.50-12.00 */
							transform: scale(0.5);
						}
						#page {
						   border-collapse: collapse;
						}
						#page td {
						   padding: 0; 
						   margin: 0;
						}
						.no_display{
							display: none;
						}
					</style>
					<table width='100%' class='print' cellspacing='0'  cellpadding='0' style='font-family:Khmer OS Battambang,Times New Roman !important;  white-space:nowrap;'>
						<tr height='90px'>
							<td align='center' valign='top' colspan='5'>
								<label id='lbl_header'></label>
							</td>
						</tr>
						<tr>
							<td width='20%'></td>
							<td width='20%'>&nbsp;</td>
							<td width='20%' align='center' valign='bottom'>
								<div style='font-family:Khmer OS Muol Light;line-height:15px;font-size:11px;position:relative'>បង្កាន់ដៃបង់ប្រាក់</div>
							</td>
							<td width='20%'>លេខបង្កាន់ដៃ/Receipt No
							</td>
							<td width='20%'><strong style='font-size:12px;'><label id='lb_receipt_no'></label></strong>
							</td>
						</tr>
						<tr>
							<td width='20%'><span class='spanBlog'>Print Date:".date('d-m-Y g:iA')."</span></td>
							<td width='20%'><span class='spanBlog'>Print By : ".$username."</span></td>
							<td width='20%' align='center' valign='bottom'>
								<div style='font-family:Times New Roman;font-size:11px;font-weight:bold'>Official Receipt</div>
							</td>
							<td width='20%'>&nbsp;ថ្ងៃបង់ប្រាក់/Pay Date</td>
							<td width='20%'><label id='lb_date' class='one bold'></label></td>
						</tr>
						<tr>
							<td>អត្តលេខ/Student ID,Test ID </td>
							<td> : &nbsp;<label id='lb_stu_id' class='one bold'></label></td>
							<td>ឆ្នាំសិក្សា/Academic Year</td>
							<td> : &nbsp;<label id='lb_academic_year' class='one'>&nbsp;</label></td>
							<td rowspan='5' valign='top'>
								<div style='float:right;border:1px solid #000;width:70px;height:85px;text-align:right'>
									<label id='lb_photo'></label>
								</div>
							</td>
						</tr>
						<tr>
							<td style='vertical-align: top;'>គោត្តនាម-នាម</td>
							<td > : &nbsp;<label id='lb_name' class='one bold' style='display: inline-block; vertical-align: top;' ></label></td>
							<td>ប្រភេទ/Type </td>
							<td> : &nbsp;<label id='lb_sesiontype' class='one'>&nbsp;</label></td>
						</tr>
						<tr>
							<td>surname-Name</td>
							<td> : &nbsp;<label id='lb_namelatin' class='one bold' style='display: inline-block; vertical-align: top;'></label></td>
							<td>ថ្នាក់/Class</td>
							<td style='white-space: nowrap;'> : &nbsp;<label id='lb_grade' class='one'>&nbsp;</label>
							</td>
						</tr>
						<tr>
							<td>ភេទ/Gender </td>
							<td> : &nbsp;<label id='lb_sex' class='one bold'></label></td>
							<td>ថ្នាកទី/Grade/Level​​​</td>
							<td rowspan='2'> : &nbsp;<label id='lb_group' class='one'>&nbsp;</td>
						</tr>
						<tr>
							<td>លេខទូរសព្ទ/Tel</td>
							<td> : &nbsp;<label id='lb_phone' class='one bold'></label><label id='lb_session' class='one bold'></label><label id='lb_study_year' class='one bold'></label></td>
						</tr>
					<tr>
						<td colspan='5'><div id='t_amountmoneytype'></div></td>
					</tr>
					<tr>
						<td rowspan='3' valign='top' style='font-size:10px;'>Note
							<div style='width:99%;float: left;'>
								<div style='font-size:10px;min-height:40px;border:1px solid #000;' id='lbl_note' class='noted' ></div>
							</div>
						</td>
						<td rowspan='3' colspan='2' valign='top' style='font-size:10px;' >
							Say in US Dollars
							<div style='font-size:10px;min-height:40px;border:1px solid #000;' id='lb_read_khmer' class='noted' ></div>
						</td>
						<td>ត្រូវបង់/Total Payment<label id='lb_fine'></label></td>
						<td align='right' style='font-weight: bold;font-family:Times New Roman;'>&nbsp;&nbsp; <label id='lb_total_payment'></label></td>
					</tr>
					<tr>
						<td>ប្រាក់សល់មុន/Credit Memo</td>
						<td align='right'>&nbsp;&nbsp; <label id='lb_credit_memo'></label></td>
					</tr>
					<tr>
						<td><div style='font-weight: bold;font-size:11px;'><strong>បានបង់ Paid Amount</strong>: $</div></td>
						<td align='right' style='font-weight: bold;font-family:Times New Roman;font-size:12px;'>&nbsp;&nbsp; <strong><label id='lb_paid_amount'></label></strong></td>
					</tr>
					<tr>
						<td align='center'>បេឡាករ/Cashier</td>
						<td align='center'>ប្រធានបេឡា/Head of Cashier</td>
						<td align='center'>អតិថិជន/Customer</td>
						<td><div>ជំពាក់/Balance</div></td>
						<td align='right'>&nbsp;&nbsp;<label id='lb_balance_due'></label></td>
					</tr>
					<tr>
						<td colspan='3'></td>
						<td><div>បង់ជា/Payment by</div></td>
						<td align='right'>&nbsp;&nbsp;<label id='lb_paymentmethod'></label></td>
					</tr>
					<tr>
						<td align='center'>
							<div style='font-size:10px;border-bottom: 1px solid #000;margin-top:15px;'><label id='lb_byuser'></label>";
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
						<td valign='top'><div>Cheque No./Bank Name</div></td>
						<td align='right' valign='top'>&nbsp;&nbsp;<label id='lb_paymentnumber'></label></td>
					</tr>
			</table>
				<div class='no_display'>
					<div id='printfooter' style='display:block;font-family:khmer os battambang;position: absolute; bottom:0px;width:100%'>
						<table style='width:100%;margin-top:10px;background: #fff;border-top: 1px solid #000;font-family: 'Times New Roman','Khmer OS Battambang'; font-size:8px;line-height: 12px;white-space:nowrap;'>
							<tr style='text-align:center;white-space:nowrap;line-height: 15px;font-size:7px !important;font-family: 'Times New Roman','Khmer OS Battambang'>
								<td >&#9742; <label id='lbl_branchphone' style='width:20%;display:in-line;'></label> &#9993; <label id='lbl_email' style='width:20%;display:in-line;'></label> &#127758 <label id='lbl_website'style='width:20%;display:in-line;'></label> &#127963 <label id='lbl_address' style='font-family:'Times New Roman,Khmer OS Battambang !important'></label> </td>
							</tr>
						</table>
						<span id='lbParentName' >&nbsp;</span>
						<span id='lbParentPhone' >&nbsp;</span>
						<div id='lbl_branchlogo'></div>
						<div class='blogbranchlogo' style='font-family:Khmer OS Muol Light;font-size:12px;'>
						<label id='lb_branchname'></label>
						<div style='line-height:10px;'><label id='lb_branchnameen'></label></div>
						</div>
					</div>
				</div>
			</div>";
			$key = new Application_Model_DbTable_DbKeycode();
			$result=$key->getKeyCodeMiniInv(TRUE);
			if($result['receipt_print']>1){
				$str.="<div id='divPrint1'>
				<div style='border:1px dashed #000; vertical-align: middle;margin:10px 0px 10px 0px'></div>
				<div id='printblog2'></div>
				</div>";
			}
			return $str;
		}elseif($receipt_type==4){//A4 Receipt
			$str="<style>
					.hearder_table{height:20px !important;}
					.defaulheight{line-height:10px !important;}
					.bold{
						font-weight:bold;
					}
					.blogbranchlogo{
						margin:0 auto;position:absolute;top:10px !important;left:100px;
					}
				</style>
				<div id='PrintReceipt' style='width:100%cm !important; padding: 0px;'>
					<style>
						.noted{
							white-space: pre-wrap;
							word-wrap: break-word;
							word-break: break-all;
							white-space: pre;
							font:12px 'Times New Roman','Khmer OS Battambang';
							border: 1px solid #000;
							line-height:20px;
							font-weight: normal !important;
							padding:2px;
							white-space: normal;
						}
						table{ border-collapse:collapse; margin:0 auto;
								border-color:#000;font-size:10px;font-family:'Times New Roman','Khmer OS Battambang'; }
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
							font-family:'Times New Roman','Khmer OS Battambang';
						}
						@page {
							/* Chrome sets own margins, we change these printer settings */
							margin:0.5cm 1cm 0.3cm 1cm; '
							page-break-before: avoid;
							/*size: 21cm 14.8cm; */
						}
						.no_display{
							display: none;
						}
						div#lbl_branchlogo img {
							max-width: 265px !important;
							max-height: initial !important;
						}
						table.headTable{
							border: solid 1px #000; 
							font-size: 11px;
							line-height:10px !important;
							width:100%;
							border-collapse: collapse;
						}
						
						table.headTable td{
							padding: 2px 4px;
							line-height: 16px;
						}
						table.headTable td.bgHead {
							background: #519fe2;
							line-height: 16px;
							font-weight: 600;
							padding: 6px 4px;
							vertical-align: top;
						}
						
						tr.hearder_table {
							background: #acd8fe;
							font-weight: bold;
							border-top: none;
						}
						tr.hearder_table td {
							padding: 6px 4px;
							text-align: center;
							border-top: none;
							font-size: inherit !important;
						}
						
						table.tableDetail {
							margin-top: -1px !important;
						}
						ul.termCondition {
							padding: 0;
							margin: 0;
							list-style: none;
							font-family:'Times New Roman','Khmer OS Battambang';
							margin-top: 5px;
						}
						li.headUl {
							font-weight: 600;
							text-decoration: underline;
							font-size: 11px;
						}
						
						table.totalFooter {
							font-size: inherit;
							white-space: nowrap;
							line-height: 10px;
							border-collapse: collapse;
							    margin-top: -1px;
						}
						table.totalFooter td {
							padding: 2px;
						}
						table.totalFooter td.titleLb {
							background: #519fe2;
							padding: 4px 2px;
							font-weight: 600;
							line-height: 13px;
							
						}
						
						.footerAddress {
							display: block;
							width: 100%;
							text-align: center;
							background: #519fe2;
							padding: 10px;
							position: absolute;
							bottom: 0;
						}
					</style>
					<table width='100%'  class='print' cellspacing='0'  cellpadding='0' style='height:13.97cm; font-family:'Times New Roman','Khmer OS Battambang' !important;  white-space:nowrap;'>
						<tr>
							<td colspan='3' style='position:relative'>
								<div style='height:100px;'></div>
							</td>
						</tr>
						<tr>
							<td width='30%' style='position:relative'>
								<div class='no_display'>
								<div id='lbl_branchlogo'></div>
									<div class='blogbranchlogo' style='font-family:Khmer OS Muol Light;font-size:12px;'>
									<label id='lb_branchname'></label>
									<div style='line-height:10px;'><label id='lb_branchnameen'></label></div>
									</div>
								</div>
							</td>
							<td align='center' valign='top' width='40%' >
								<div style='font-family:Khmer OS Muol Light;line-height:15px;font-size:12px;position:relative'>វិក្ក័យបត្រ</div>
								<div style='font-family:Times New Roman;font-size:12px;font-weight:bold'>INVOICE</div>
							</td>
							<td width='30%'>&nbsp;</td>
						</tr>
						<tr>
							<td colspan='3' style='position:relative'>
							</td>
						</tr>
						<tr>
						<td align='center' valign='bottom' colspan='3'>
							<table class='headTable' border='1' >
								<tr>
									<td class='bgHead'>អតិថិជន / Customer</td>
									<td class='bgHead'>Students ID</td>
									<td rowspan='2' class='bgHead'>
										កាលបរិច្ឆេទ <br />
										Date:
									</td>
									<td rowspan='2'><label id='lb_date' class='one bold'></label></td>
								</tr>
								<tr>
									<td style='border-bottom: none; border-right: none;'><span id='lb_name' class='one bold' style='display: inline-block; vertical-align: top; line-height: 16px;' ></span></td>
									<td style='border-bottom: none; border-left: none; text-align: center;'><span id='lb_stu_id' class='one bold'></span></td>
								</tr>
								<tr>
									<td style='border-top: none; ' colspan='2'><span id='lb_phone' class='one bold' style='display: inline-block; vertical-align: top; line-height: 13px;' ></span></td>
									<td rowspan='2' class='bgHead'>
										លេខវិក្ក័យបត្រ <br />
										Invoice No.
									</td>
									<td rowspan='2'><span id='lb_receipt_no' class='one bold'></span></td>
								</tr>
								<tr>
									<td class='bgHead' colspan='2'>អ្នកជាអាណាព្យាបាល / Parent's Name</td>
								</tr>
								<tr>
									<td style='border-bottom: none;border-right:none;'>
											<span id='lbFather' style='display: inline-block; vertical-align: top; line-height: 16px;'></span><br />
											<span id='lbFatherTel'></span>
									</td>
									<td style='border-bottom: none;border-left:none;'>
											<span id='lbMother' style='display: inline-block; vertical-align: top; line-height: 16px;'></span><br />
											<span id='lbMotherTel'></span>
									</td>
									<td rowspan='2' class='bgHead'>
										កាលបរិច្ឆេទបោះពុម្ភ<br />
										Print Date:
									</td>
									<td rowspan='2'>
									".date('d-m-Y g:i A')."<br />
									ដោយ / By: <span class='bold'>".$username."</span>
									</td>
								</tr>
								<tr>
									<td style='border-top: none;' colspan='2'><span id='lbParentPhone' style='display: inline-block; vertical-align: top; line-height: 16px;'></span></td>
								</tr>
							</table>
							<div class='no_display'>
								<span id='lb_academic_year' class='one'>&nbsp;</span>
								<span id='lb_grade' class='one'>&nbsp;</span>
								<span id='lb_sesiontype' class='one'>&nbsp;</span>
								<div style='border:1px solid #000;margin:0 auto;position:absolute;top:35px;width:70px;height:85px;right:0.2cm'>
									<span id='lb_photo'></span>
								</div>
								
								<span id='lb_sex' class='one bold'></span>
								<span id='lb_session' class='one bold'></span>
								<span id='lb_study_year' class='one bold'></span>
								<span id='lb_group' class='one'></span>
								
								Note
								<div style='width:99%;float: left;'>
									<div style='font-size:10px;min-height:70px;border:1px solid #000;' id='lbl_note' class='noted' ></div>
								</div>
								
								Say in US Dollars
								<div style='font-size:10px;min-height: 70px;border:1px solid #000;' id='lb_read_khmer' class='noted' ></div>
						
							</div>
							
						</td>
					</tr>
					<tr>
						<td colspan='3'><div id='t_amountmoneytype'></div></td>
					</tr>
					<tr>
						<td colspan='2' valign='top' style='font-size:10px;'>
							<ul class='termCondition'>
								<li class='headUl'>
									គោលការណ៍ និងលក្ខខណ្ឌ / Term of Condition:
								</li>
								<li>
									1). ទឹកប្រាក់ដែលបានបង់ហើយមិនអាចផ្ទេរ ឬដកវិញបានទេ<br />
									All fees are not transferable or refundable.
								</li>
								<li>
									2). ទឹកប្រាក់ដែលបានបង់ហើយមិនអាចផ្ទេរ ឬដកវិញបានទេ<br />
									All fees are not transferable or refundable.
								</li>
							</ul>
						</td>
						
						<td>
							<table class='totalFooter' border='1' width='100%' >
								<tr>
									<td class='titleLb'>
									ពិន័យ:<br />
									Penalty:
									</td>
									<td style='border-right: none;'>$</td>
									<td align='right' style='border-left: none;'>&nbsp;&nbsp; <label id='lb_fine'></label></td>
								</tr>
								<tr>
									<td class='titleLb'>
									តម្លៃសរុប:<br />
									Total Payment:</td>
									<td style='border-right: none;'>$</td>
									<td align='right' style='border-left: none;font-weight: bold;font-family:Times New Roman;'>&nbsp;&nbsp; <label id='lb_total_payment'></label></td>
								</tr>
								<tr>
									<td class='titleLb'>Credit Memo:</td>
									<td style='border-right: none;'>$</td>
									<td align='right' style='border-left: none;'>&nbsp;&nbsp; <label id='lb_credit_memo'></label></td>
								</tr>
								<tr>
									<td class='titleLb'>
										ប្រាក់បានបង់:<br />
										Paid Amount:
									</td>
									<td style='border-right: none;'>$</td>
									<td align='right' style='border-left: none; '>&nbsp;&nbsp; <strong><label id='lb_paid_amount'></label></strong></td>
								</tr>
								<tr>
									<td class='titleLb'>
									នៅខ្វះ:<br />
									Balance:</td>
									<td style='border-right: none;'>$</td>
									<td align='right' style='border-left: none;'>&nbsp;&nbsp;<label id='lb_balance_due'></label></td>
								</tr>
								<tr>
									<td class='titleLb'>
										វិធី​សា​ស្រ្ត​ទូទាត់:<br />
										Payment Method:
									</td>
									<td colspan='2' align='right'>&nbsp;&nbsp;<label id='lb_paymentmethod'></label></td>
								</tr>
								<tr>
									<td class='titleLb'>
										លេខសម្គាល់ / លេខគណនី:<br />
										Number/Bank No.
									</td>
									<td colspan='2' align='right'>&nbsp;&nbsp;<label id='lb_paymentnumber'></label></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td valign='top' colspan='3'>
							<br />
							<table class='defaulheight' width='100%' border='0' style='font-family: Khmer OS Battambang,Times New Roman;font-size:12px;white-space:nowrap;margin-top:50px;line-height: 11px;'>
								<tr>
									<td colspan='5'>
										<table width='100%' style='marin-top:5px;font-size:12px; white-space:nowrap;line-height:15px;border-collapse:collapse;'>
											<tr>
												<td style='width:30%' align='center'></td>
												<td style='width:30%' align='center'></td>
												<td style='width:30%' align='center'></td>
											</tr>
											
											
											<tr>
												<td align='center'>
													<div style='border-bottom: 1px solid #000;margin-top:30px;'>
														<span style='font-size:12x;' >&nbsp;</span>
													</div>
													<span style='line-height: 18px;font-size: 14px;font-weight: 600;margin-top: 5px;display: block;'>ហត្ថលេខាអតិថិជន / Customer's Signature</span>
												</td>
												<td align='center' valign='bottom'>
													
												</td>
												<td align='center' valign='bottom'>
													<div style='border-bottom: 1px solid #000;margin-top:30px;'>
														<span style='font-size:12x; font-weight: 600;'  id='lb_byuser'></span>
													</div>
													<span style='line-height: 18px;font-size: 14px;font-weight: 600;margin-top: 5px;display: block;'>Authorized Signature</span>
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
			</table>
			
				<div class='no_display'>
				<div class='footerAddress'>
				<span id='lbl_address' ></span><br />
				&#9742; <span id='lbl_branchphone' ></span>
			</div>
					<div id='printfooter' style='display:block;font-family:khmer os battambang;position: absolute; bottom:0px;width:100%'>
						<table style='width:100%;margin-top:10px;background: #fff;border-top: 1px solid #000;font-family: 'Times New Roman','Khmer OS Battambang'; font-size:8px;line-height: 12px;white-space:nowrap;'>
							<tr style='text-align:center;white-space:nowrap;line-height: 15px;font-size:7px !important;font-family: 'Times New Roman','Khmer OS Battambang'>
								<td > &#9993; <label id='lbl_email' style='width:20%;display:in-line;'></label> &#127758 <label id='lbl_website'style='width:20%;display:in-line;'></label> &#127963  </td>
							</tr>
						</table>
					</div>
				</div>
			</div>";
			$key = new Application_Model_DbTable_DbKeycode();
			$result=$key->getKeyCodeMiniInv(TRUE);
			if($result['receipt_print']>1){
				$str.="<div id='divPrint1'>
				<div style='border:1px dashed #000; vertical-align: middle;margin:10px 0px 10px 0px'></div>
				<div id='printblog2'></div>
				</div>";
			}
			return $str;
		}
	}
	
	public function getHeaderReportScore($branch_id=null,$forGenerate=0){
		$key = new Application_Model_DbTable_DbKeycode();
		$setting = $key->getKeyCodeMiniInv(TRUE);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str="";
		
		if ($forGenerate==1){
			$baseUrl =PUBLIC_PATH;
			$styleLogo = "width:120px;";
		}else{
			$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
			$styleLogo = "max-width: 98%;max-height:90px;min-height:50px;";
		}
		
		$logo = $baseUrl.'/images/Logo/Logo.png';
		if(!empty($setting['logo'])){
			if (file_exists(PUBLIC_PATH."/images/logo/".$setting['logo'])){
				$logo = $baseUrl.'/images/logo/'.$setting['logo'];
			}
		}
		
		$school_khname = $tr->translate('SCHOOL_NAME');
		$school_name = $tr->translate('CUSTOMER_BRANCH_EN');
		$address = $tr->translate('CUSTOMER_ADDRESS');
		$tel = $tr->translate('CUSTOMER_TEL');
		$email =  $tr->translate('CUSTOMER_EMAIL');
		$website = $tr->translate('CUSTOMER_WEBSITE');
		if($branch_id==null){
			
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
				
				if (!empty($rs['photo'])){
					if (file_exists(PUBLIC_PATH."/images/logo/".$rs['photo'])){
						$logo = $baseUrl.'/images/logo/'.$rs['photo'];
					}
				}
				
				$school_khname = $rs['school_namekh'];
				$school_name = $rs['school_nameen'];
				
				$address = $rs['br_address'];
				$tel = $rs['branch_tel'];
				$email = $rs['email'];
				$website = $rs['website'];
			}
		}
		
	    $imgSing="agreementsign.jpg";
		$str='
		<table width="100%" style="white-space:nowrap;">
			<tbody>
				<tr>
					<td width="35%" valign="top">
						<img style="'.$styleLogo.'" src="'.$logo.'">
					</td>
					<td width="40%" valign="top" style="font-size:11px;line-height: 18px;font-family: Khmer OS Battambang;">
					</td>
					<td width="25%" valign="top" style="font-family: '."'Times New Roman'".','."'Khmer OS Muol Light'".';">
						<ul style="color:#002c7b;list-style: none;padding: 0;text-align: center;line-height: 18px;font-size: 11px;margin-left: 70px;">
							<li>ព្រះរាជាណាចក្រកម្ពុជា</li>
							<li><span style="margin:0;padding:0;font-weight: 600; color: #002c7b; font-size: 10px;">KINGDOM OF CAMBODIA</span></li>
							<li>ជាតិ សាសនា ព្រះមហាក្សត្រ</li>
							<li><span style=" margin:0; padding:0; font-weight: 600; color: #002c7b;font-size: 10px; ">NATION RALIGION KING</span></li>
							<li><img style=" height: 12px; " src="'.$baseUrl.'/images/'.$imgSing.'"></li>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>
		';
		return $str;
	}
	function getFooterPrincipalSigned($branch_id,$group_id){
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Application_Model_DbTable_DbGlobal();
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
// 		$rs = $db->getBranchInfo($branch_id);
// 		print_r($rs);exit();
		
		$sql="SELECT
		   	g.`branch_id`,
		   	b.stamp,
		   	b.signature,
		   	b.principal,
		   	b.workat,
		   	g.`group_code`,
			(SELECT teacher_name_kh from rms_teacher as t where t.id = g.teacher_id LIMIT 1) as teacher,
		   	(SELECT signature from rms_teacher as t where t.id = g.teacher_id LIMIT 1) as teacher_sigature
		   	
   		FROM
		   	`rms_branch` AS b,
		   	`rms_group` AS g
   		WHERE
		   	b.br_id=g.`branch_id`
		   	AND b.br_id= $branch_id
		   	AND g.`id` = ".$group_id;
		$rs = $db->getGlobalDbRow($sql);
		
		$font = 'Khmer OS Muol Light';
		$fontbtb = 'Khmer os battambang';
		
		$str='<table width="100%">
				<tr>
					<td valign="top" width="33%" style="font-family:'.$font.';font-size:14px;text-align: center;">
						បានឃើញ និងឯកភាព<br />
					</td>
					<td width="33%" valign="top" style="font-family:'.$fontbtb.';font-size:14px;text-align: center;">បានពិនិត្យត្រឹមត្រូវ</td>
					<td width="33%" style="white-space: nowrap;font-family:'.$fontbtb.'" valign="top">'.$rs['workat'].$tr->translate("CREATE_WORK_DATE").'</td>
				</tr>
				<tr>
					<td valign="top" style="font-family:'.$font.';font-size:14px;text-align: center;">'.$rs["principal"].'</td>
					<td valign="top" style="font-family:'.$fontbtb.';font-size:14px;text-align: center;">ការិយាល័យសិក្សាធិការ</td>
					<td valign="top" style=" text-align: center;font-family:'.$fontbtb.'">'.$tr->translate("TEACHER_ROOM").'</td>
				</tr>
				<tr>
					<td valign="top"  style="font-family:'.$font.';font-size:14px;text-align: center;">
						<div>
							<img src="'.$baseUrl.'/images/'.$rs['stamp'].'" style="max-height:100px;position:relative;left:100px;" />
						</div>
							<img src="'.$baseUrl.'/images/'.$rs['signature'].'" style="left:100px;bottom:50px;width:200px;position:relative;margin-left:150px;" />
					</td>
					<td></td>
					<td valign="top" style="font-family:'.$font.';font-size:14px;text-align: center;">';
						 if(!empty($rs['teacher_sigature'])){
						$str.='<div><img src="'.$baseUrl.'/images/photo/'.$rs['teacher_sigature'].'" style="height:40px;position:relative;margin-bottom:20px;" /></div>';
						}else{
							$str.='<div style="height: 100px;"></div>';
						} 
						$str.='<span style="font-family:'.$font.';font-size:14px;text-align: center;padding-left:20px;">'.$rs['teacher'].'</span>
					</td>
				</tr>
			</table>';
			return $str;
	}//
	
	
}