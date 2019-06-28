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
	public function addProgramName($data=null,$type=null){
		$_title = new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));
		
		$_db = new Application_Model_DbTable_DbGlobal();
		if(!empty($type)){
			$rows = $_db->getServiceType(2);
		}else{
			$rows = $_db->getServiceType(1);
		}
		
		//array_unshift($rows,array('id' => '-1',"title"=>$this->tr->translate("ADD")) );
		$opt = "";
		if(!empty($rows))foreach($rows AS $row) $opt[$row['id']]=$row['title'];
		$_service_name = new Zend_Dojo_Form_Element_FilteringSelect("type");
		$_service_name->setMultiOptions($opt);
		$_service_name->setAttribs(array(
				'dojoType'=>$this->filter,
				'required'=>'true',
				'class'=>'fullside',));
		
		$_desc = new Zend_Dojo_Form_Element_Textarea('desc');
		$_desc->setAttribs(array('dojoType'=>$this->tarea,'class'=>'fullside','style'=>'width:96%;min-height:50px;'	));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_program');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				2=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		if (!empty($data)){
			$_title->setValue($data['title']);
			$_desc->setValue($data['desc']);
		}
		$this->addElements(array($_service_name,$_title,$_desc,$_status));
		return $this;
	}
	public function addProServiceCategory($data=null){
		$_title = new Zend_Dojo_Form_Element_ValidationTextBox('servicetype_title');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside','required'=>'true'));
		
		$_tem_desc = new Zend_Dojo_Form_Element_TextBox('item_desc');
		$_tem_desc->setAttribs(array('dojoType'=>$this->text,'required'=>'true','class'=>'fullside',));
	
		$_status = new Zend_Dojo_Form_Element_FilteringSelect('sertype_status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$_type = new Zend_Dojo_Form_Element_FilteringSelect('ser_type');
		$_status_type = array(
				1=>$this->tr->translate("SERVICE"),
				2=>$this->tr->translate("PROGRAM"));
		$_type->setMultiOptions($_status_type);
		
		$_type->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		
		$_id = new Zend_Form_Element_Hidden('id');
		$_id->setAttribs(array('dojoType'=>$this->text));
		
		if($data !=null){
			$_id->setValue($data['id']);
			$_title->setValue($data['title']);
			$_tem_desc->setValue($data['item_desc']);
			$_status->setValue($data['status']);
			$_type->setValue($data['type']);
			
		}
		$this->addElements(array($_title,$_tem_desc,$_status,$_type,$_id));
	
		return $this;
	
	}
	public function frmPopupDistrict(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$frm = new Global_Form_FrmDistrict();
		$frm = $frm->FrmAddDistrict();
		Application_Model_Decorator::removeAllDecorator($frm);
		$str='<div class="dijitHidden">
				<div data-dojo-type="dijit.Dialog"  id="frm_district" >
				<form id="form_district" >';
		$str.='<table style="margin: 0 auto; width: 100%;" cellspacing="7">
				<tr>
				<td>District Name English</td>
				<td>'.$frm->getElement('pop_district_name').'</td>
				</tr>
				<tr>
					<td>Province Name English</td>
					<td>'.$frm->getElement('pop_district_namekh').'</td>
				</tr>
				<tr>
					<td>District Name Khmer</td>
					<td>'.$frm->getElement('province_names').'</td>
				</tr>
						
				<tr>
					<td colspan="2" align="center">
					<input type="button" value="Save" label="Save" dojoType="dijit.form.Button"
					iconClass="dijitEditorIcon dijitEditorIconSave" onclick="addNewDistrict();"/>
					</td>
				</tr>
			</table>';
		$str.='</form></div>
		</div>';
		return $str;
	}
	public function frmPopupCommune(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str='<div class="dijitHidden">
		<div data-dojo-type="dijit.Dialog"  id="frm_commune" >
		<form id="form_commune" >';
		$str.='<table style="margin: 0 auto; width: 100%;" cellspacing="7">
		<tr>
		<td>Commune Name EN</td>
		<td>'.'<input dojoType="dijit.form.ValidationTextBox" required="true" class="fullside" id="commune_nameen" name="commune_nameen" value="" type="text">'.'</td>
		</tr>
		<tr>
		<td>Commune KH</td>
		<td>'.'<input dojoType="dijit.form.ValidationTextBox" required="true" class="fullside" id="commune_namekh" name="commune_namekh" value="" type="text">'.'</td>
		</tr>
		<tr>
		<td></td>
		<td>'.'<input dojoType="dijit.form.TextBox" required="true" class="fullside" id="district_nameen" name="district_nameen" value="" type="hidden">'.'</td>
		</tr>
		<tr>
		<td colspan="2" align="center">
		<input type="button" value="Save" label="Save" dojoType="dijit.form.Button"
		iconClass="dijitEditorIcon dijitEditorIconSave" onclick="addNewCommune();"/>
		</td>
		</tr>
		</table>';
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
		
		$session_user=new Zend_Session_Namespace('authstu');
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
								<td class="one ">&nbsp;'.$tr->translate("CHEQE_NO").'&nbsp;</td>
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
}