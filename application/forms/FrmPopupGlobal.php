<?php

class Application_Form_FrmPopupGlobal extends Zend_Dojo_Form
{
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_num;
	protected $text;
	protected $tarea;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	
	public function frmPopupDistrict(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$frm = new Global_Form_FrmDistrict();
		$frm = $frm->FrmAddDistrict();
		Application_Model_Decorator::removeAllDecorator($frm);
		$str='<div class="dijitHidden">
				<div style="width:500px;" data-dojo-type="dijit.Dialog" id="frm_district" data-dojo-props="title:'."'".$tr->translate("ADD_DISTRICT")."'".'" >
					<form id="form_district" >';
						$str.='
						<div class="card-box">
							<div class="card-blogform">
								<div class="card-body"> 
									<div class="row"> 
										<div class="col-md-12 col-sm-12 col-xs-12"> 
											<div class="d-flex"> 
												<div class="settings-main-icon ">
													<i class="fa fa-map-marker"></i>
												</div> 
												<div class="col-md-10 col-sm-10 col-xs-12"> 
													<p class="tx-20 font-weight-semibold d-flex ">'.$tr->translate("DISTRICT").'</p>
												</div> 
											</div>
						';
						$str.='
								<div class="form-group">
								   <label class="control-label col-md-5 col-sm-5 col-xs-12" >'.$tr->translate("PROVINCE_NAME").' :
								   </label>
								   <div class="col-md-7 col-sm-7 col-xs-12">
										'.$frm->getElement('province_names').'
								   </div>
								</div>
								<div class="form-group">
								   <label class="control-label col-md-5 col-sm-5 col-xs-12" >'.$tr->translate("DISTRICT_KH").' :
								   </label>
								   <div class="col-md-7 col-sm-7 col-xs-12">
										'.$frm->getElement('pop_district_namekh').'
								   </div>
								</div>
								<div class="form-group">
								   <label class="control-label col-md-5 col-sm-5 col-xs-12" >'.$tr->translate("DISTRICT_EN").' :
								   </label>
								   <div class="col-md-7 col-sm-7 col-xs-12">
										'.$frm->getElement('pop_district_name').'
								   </div>
								</div>
						';
						$str.='
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="card-box">
				<div class="col-md-12 col-sm-12 col-xs-12 border-top mt-20 ptb-10 text-center">
					<input type="button" class="button-class button-primary" iconClass="glyphicon glyphicon-floppy-disk" value="Save" label="'.$tr->translate("SAVE").'" dojoType="dijit.form.Button" onclick="addNewDistrict();"/>
				</div>
			</div>	
						';
				
		
		$str.='</form></div>
		</div>';
		return $str;
	}
	public function frmPopupCommune(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str='<div class="dijitHidden">
				<div style="width:500px;" data-dojo-type="dijit.Dialog" id="frm_commune" data-dojo-props="title:'."'".$tr->translate("ADD_COMMUNE")."'".'">
					<form id="form_commune" >';
					$str.='
						<div class="card-box">
							<div class="card-blogform">
								<div class="card-body"> 
									<div class="row"> 
										<div class="col-md-12 col-sm-12 col-xs-12"> 
											<div class="d-flex"> 
												<div class="settings-main-icon ">
													<i class="fa fa-map-marker"></i>
												</div> 
												<div class="col-md-10 col-sm-10 col-xs-12"> 
													<p class="tx-20 font-weight-semibold d-flex ">'.$tr->translate("COMMUNE").'</p>
												</div> 
											</div>
					';
					$str.='
								
								<div class="form-group">
								   <label class="control-label col-md-5 col-sm-5 col-xs-12" >'.$tr->translate("COMMUNE_NAME_KH").' :
								   </label>
								   <div class="col-md-7 col-sm-7 col-xs-12">
										<input dojoType="dijit.form.TextBox" required="true" class="fullside" id="district_nameen" name="district_nameen" value="" type="hidden">
										<input dojoType="dijit.form.ValidationTextBox" required="true" class="fullside" id="commune_namekh" name="commune_namekh" value="" type="text">
								   </div>
								</div>
								<div class="form-group">
								   <label class="control-label col-md-5 col-sm-5 col-xs-12" >'.$tr->translate("COMMUNE_NAME").' :
								   </label>
								   <div class="col-md-7 col-sm-7 col-xs-12">
										<input dojoType="dijit.form.ValidationTextBox" required="true" class="fullside" id="commune_nameen" name="commune_nameen" value="" type="text">
								   </div>
								</div>
						';
					$str.='
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="card-box">
				<div class="col-md-12 col-sm-12 col-xs-12 border-top mt-20 ptb-10 text-center">
					<input type="button" class="button-class button-primary" iconClass="glyphicon glyphicon-floppy-disk" value="Save" label="'.$tr->translate("SAVE").'" dojoType="dijit.form.Button" onclick="addNewCommune();"/>
				</div>
			</div>	
			';
		
		$str.='</form></div>
		</div>';
		return $str;
	}
	public function frmPopupVillage(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str='<div class="dijitHidden">
		<div data-dojo-type="dijit.Dialog"  id="frm_village" >
		<form id="form_village" dojoType="dijit.form.Form" method="post" enctype="application/x-www-form-urlencoded">
		<script type="dojo/method" event="onSubmit">
		if(this.validate()) {
		return true;
	} else {
	return false;
	}
	</script>
	';
		$str.='<table style="margin: 0 auto; width: 95%;" cellspacing="10">
		<tr>
		<td>'.$tr->translate("VILLAGE_KH").'</td>
		<td>'.'<input dojoType="dijit.form.ValidationTextBox" required="true" missingMessage="Invalid Module!" class="fullside" id="village_namekh" name="village_namekh" value="" type="text">'.'</td>
		</tr>
		<tr>
		<td>'.$tr->translate("VILLAGE_NAME").'</td>
		<td>'.'<input dojoType="dijit.form.ValidationTextBox" required="true" missingMessage="Invalid Module!" class="fullside" id="village_name" name="village_name" value="" type="text">'.'</td>
		</tr>
		<tr>
		<td>'.'<input dojoType="dijit.form.TextBox" class="fullside" id="province_name" name="province_name" value="" type="hidden">
		<input dojoType="dijit.form.TextBox" id="district_name" name="district_name" value="" type="hidden">
		'.'</td>
		<td>'.'<input dojoType="dijit.form.TextBox" id="commune_name" name="commune_name" value="" type="hidden">'.'</td>
		</tr>
		<tr>
		<td colspan="2" align="center">
		<input type="reset" value="សំអាត" label='.$tr->translate('CLEAR').' dojoType="dijit.form.Button" iconClass="dijitIconClear"/>
		<input type="button" value="save_close" name="save_close" label="'. $tr->translate('SAVE').'" dojoType="dijit.form.Button"
		iconClass="dijitEditorIcon dijitEditorIconSave" Onclick="addVillage();"  />
		</td>
		</tr>
		</table>';
		$str.='</form></div>
		</div>';
		return $str;
	}	
	
	public function receiptOtherIncome(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$key = new Application_Model_DbTable_DbKeycode();
		$data=$key->getKeyCodeMiniInv(TRUE);
		
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$last_name=$session_user->last_name;
		$username = $session_user->first_name;
		$user_id = $session_user->user_id;
		$usertype="";
		$str='
			<style>
				.h{ margin-top: -3px;margin-bottom: 3px;}
				.tab_row{ margin-top: -8px;}
				.bold{
					font-weight:bold;
				}
				.h1{ margin-top: -6px;}
				.values{ min-height: 25px; padding: 2px 5px;display: block; font-family: '."'Times New Roman'".','."'Khmer OS Battambang'".';}
						
				.fonteng{font-size:16px;}
				.one{white-space:nowrap;font-size:16px;}
				.border{border:1px solid #000; min-width:220px}
				.heght-row {
				    height: 38px;
				}
			</style>
			<table width="100%"  class="print" cellspacing="0"  cellpadding="0" style=" font-family: '."'.Times New Roman.'".','."'Khmer OS Battambang'".' !important; font-size:11px !important; margin-top: -14px;white-space:nowrap;">
				<tr height="90px;">
					<td id="header_receipt" align="center" valign="top">
					</td>
				</tr>
				<tr height="40px;">
					<td colspan="10"  style="" align="center" valign="top">
						<table width="100%" style="font-size: 10px;margin-top: -15px;">
							<tr>
								<td width="30%"></td>
								<td align="center" width="40%" style="font-size: 16px; line-height: 20px;">
									<div style="font-family:'."'Times New Roman'".','."'Khmer OS Muol Light'".';font-size: 16px;">បង្កាន់ដៃបង់ប្រាក់</div>
									<strong>OFFICIAL RECEIPT</strong>
								</td>
								<td width="30%" valign="center" align="center">
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="10" valign="top">
						<table class="defaulheight" width="100%" border="0" style="font-family: '."'.Times New Roman.'".','."'Khmer OS Battambang'".';font-size:12px; white-space:nowrap;margin-top:-5px;line-height: 20px;">
							<tr class="heght-row">
								<td class="one ">&nbsp;'.$tr->translate("RECEIPT_NO").'&nbsp;</td>
								<td class="border values one">&nbsp;<span id="receipt_no"></span>&nbsp;</td>
								<td class="one " align="left">&nbsp;'.$tr->translate("FOR_DATE").'&nbsp;</td>
								<td class="border values one">&nbsp;<span id="lbl_for_date"></span>&nbsp;</td>
							</tr>
							
							<tr class="heght-row">
								<td class="one ">&nbsp;'.$tr->translate("INCOME_TITLE").'&nbsp;</td>
								<td class="border values one">&nbsp;<span id="lbl_title_income"></span>&nbsp;</td>
								<td class="one " align="left">&nbsp;'.$tr->translate("INCOME_CATEGORY").'&nbsp;</td>
								<td class="border values one">&nbsp;<span id="lbl_cate_income"></span>&nbsp;</td>
							</tr>
							
							<tr class="heght-row">
								<td class="one ">&nbsp;'.$tr->translate("PAYMENT_METHOD").'&nbsp;</td>
								<td class="border values one">&nbsp;<span id="lbl_payment_method"></span>&nbsp;</td>
								<td class="one " align="left">&nbsp;'.$tr->translate("TOTAL_INCOME").'&nbsp;</td>
								<td class="border values one">&nbsp;<span id="lbl_total_amount"></span>&nbsp;</td>
							</tr>
							
							<tr class="heght-row">
								<td class="one ">&nbsp;'.$tr->translate("NOTE_NUMBER").'&nbsp;</td>
								<td class="border values one">&nbsp;<span id="lbl_cheqe_no"></span>&nbsp;</td>
								<td class="one " align="left">&nbsp;'.$tr->translate("NOTE").'&nbsp;</td>
								<td class="border values one">&nbsp;<span id="lbl_description"></span>&nbsp;</td>
							</tr>
							
							<tr style="font-size: 15px; font-family:'."'.Times New Roman.'".','."'Khmer OS Battambang'".';">
								<td colspan="2" align="center" >អ្នកប្រគល់</td>
								<td colspan="2" align="center">អ្នកទទួល</td>
							</tr>
							<tr style="font-size: 15px;">
								<td colspan="4" align="center">&nbsp;</td>
							</tr>
							
							<tr style="font-size: 15px;">
								<td colspan="2" align="center"></td>
								<td colspan="2" align="center" style="line-height: 13px;">
									<h4 id="user_sign" style="font-weight:bold; font-family: Arial Black;color:#000; font-size: 13px;font-family:'."'.Times New Roman.'".','."'Khmer OS Battambang'".';"> 
								        '.$last_name." ".$username.$usertype.'
					        		</h4>
					        		<div style="font-size: 12px;margin-top:-10px;" id="time_footer">
					        			'.date("F j, Y, g:i a").'
					        		</div>
					        	</td>
							</tr>
							
						</table>
					</td>
				</tr>
			</table>
		';
		return $str;
	}
	
	
	function printByFormatForTeacher(){
		
		$dbExternal = new Application_Model_DbTable_DbExternal();
		$teacherInfo =$dbExternal->getCurrentTeacherInfo();
		$teacherNameKh = empty($teacherInfo['teacher_name_kh'])?"":$teacherInfo['teacher_name_kh'];
		$teacherNameEn = empty($teacherInfo['teacher_name_en'])?"":$teacherInfo['teacher_name_en'];
	
		$teachName = $teacherNameKh;
		if(empty($teachName)){
			$teachName = $teacherNameEn;
		}else{
			if(!empty($teacherNameEn)){
				$teachName = $teachName." / ".$teacherNameEn;
			}
		}
	
		$string='
				<ul class="printInfo">
					<li>Print Date / Time : '.date("d/m/Y"." H:i:s").'</li>
					<li>Print By : '.$teachName.'</li>
				<ul>
		';
		return $string;
	}
}