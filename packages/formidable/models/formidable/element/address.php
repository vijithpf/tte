<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementAddress extends FormidableElement {
	
	public $element_text = 'Address Field(s)';
	public $element_type = 'address';
	public $element_group = 'Pre-Defined Elements';	
		
	public $properties = array(
		'label' => true,
		'label_hide' => true,
		'required' => true,
		'tooltip' => true,
		'format' => array(
			'formats' => '',
			'note' => ''
		),
		'handling' => false
	);
	
	public $dependency = array(
		'has_value_change' => false
	);
	
	function __construct($elementID = 0) 
	{				
		parent::__construct($elementID);
		
		$this->properties['format']['formats'] = array(
			'{address_1}{n}{address_2}{n}{city}{n}{state}{n}{country}{n}{postal_code}' => t('address_1 {n} address_2 {n} city {n} province {n} country {n} postal_code'),			
			'{address_1}{n}{address_2}{n}{city}{n}{province}{n}{country}{n}{postal_code}' => t('address_1 {n} address_2 {n} city {n} province {n} country {n} postal_code'),
			'{address_1}{n}{address_2}{n}{city}{n}{county}{n}{country}{n}{zipcode}' => t('address_1 {n} address_2 {n} city {n} county {n} country {n} zipcode'),
			
			'{address_1}{n}{address_2}{n}{city}{n}{state}{n}{postal_code}{n}{country}' => t('address_1 {n} address_2 {n} city {n} state {n} postal_code {n} country'),
			'{address_1}{n}{address_2}{n}{city}{n}{province}{n}{postal_code}{n}{country}' => t('address_1 {n} address_2 {n} city {n} province {n} postal_code {n} country'),
			'{address_1}{n}{address_2}{n}{city}{n}{county}{n}{zipcode}{n}{country}' => t('address_1 {n} address_2 {n} city {n} county {n} zipcode {n} country'),			
			
			'{address_1}{n}{address_2}{n}{city}{n}{country}{n}{state}{n}{postal_code}' => t('address_1 {n} address_2 {n} city {n} country {n} state {n} postal_code'),
			'{address_1}{n}{address_2}{n}{city}{n}{country}{n}{province}{n}{postal_code}' => t('address_1 {n} address_2 {n} city {n} country {n} province {n} postal_code'),
			'{address_1}{n}{address_2}{n}{city}{n}{country}{n}{county}{n}{zipcode}' => t('address_1 {n} address_2 {n} city {n} country {n} county {n} zipcode'),
			
			'{address_1} {address_2} {postal_code} {city}, {state}{n}{country}' => t('address_1 address_2 {n} postal_code city, state {n} country'),
			'{address_1} {address_2} {postal_code} {city}, {province}{n}{country}' => t('address_1 address_2 {n} postal_code city, province {n} country'),
			'{address_1} {address_2} {zipcode} {city}, {county}{n}{country}' => t('address_1 address_2 {n} zipcode city, county {n} country'),
			
			'{address_1} {postal_code} {city}, {state}{n}{country}' => t('address_1 {n} postal_code city, state {n} country'),
			'{address_1} {postal_code} {city}, {province}{n}{country}' => t('address_1 {n} postal_code city, province {n} country'),
			'{address_1} {zipcode} {city}, {county}{n}{country}' => t('address_1 {n} zipcode city, county {n} country'),
			'{street} {number}{n}{zipcode} {city}, {state}{n}{country}' => t('street, number {n} zipcode city, state {n} country'),
			'{street} {number}{n}{zipcode} {city}, {province}{n}{country}' => t('street, number {n} zipcode city, province {n} country'),
			'{street} {number}{n}{zipcode} {city}, {county}{n}{country}' => t('street, number {n} zipcode city, county {n} country'),
			'{street} {number}{n}{zipcode} {city}{n}{country}' => t('street, number {n} zipcode city{n} country'),
			'other' => t('Other format: ')
		);
		$this->properties['format']['note'] = array(
			'{street} - '.t('Street'),
			'{number} - '.t('Number'),
			'{address_1} - '.t('Address 1'),
			'{address_2} - '.t('Address 2'),
			'{city} - '.t('City'),
			'{state} - '.t('State'),
			'{province} - '.t('Province'),			
			'{county} - '.t('County'),
			'{country} - '.t('Country'),
			'{postal_code} - '.t('Postal Code'),
			'{zipcode} - '.t('Zipcode'),
			'{n} - '.t('Break / New line'),
			t('You can also use specialchars like ,.!;: etc...')
		);	
	}
	
	public function generate() 
	{				
		$form = Loader::helper('form');
		
		$_attributes = $this->attributes;		
		$_attributes['class'] .= ' address_1';
		$_attributes['placeholder'] = t('Address 1');		
		$_address_1 = $form->text($this->handle.'[address_1]', isset($this->value['address_1'])?$this->value['address_1']:'', $_attributes);
		
		$_attributes = $this->attributes;		
		$_attributes['class'] .= ' address_2';
		$_attributes['placeholder'] = t('Address 2');		
		$_address_2 = $form->text($this->handle.'[address_2]', isset($this->value['address_2'])?$this->value['address_2']:'', $_attributes);

		$_attributes = $this->attributes;		
		$_attributes['class'] .= ' city';
		$_attributes['placeholder'] = t('City');		
		$_city = $form->text($this->handle.'[city]', isset($this->value['city'])?$this->value['city']:'', $_attributes);
		
		$_attributes = $this->attributes;		
		$_attributes['class'] .= ' county county_input';
		$_attributes['placeholder'] = t('County');
		$_attributes['ccm-attribute-address-field-name'] = $this->handle.'[province]';		
		$_county = $form->text($this->handle.'[province]', isset($this->value['province'])?$this->value['province']:'', $_attributes);
		
		$_attributes = $this->attributes;		
		$_attributes['class'] .= ' county county_select';
		$_attributes['placeholder'] = t('County');
		$_attributes['ccm-attribute-address-field-name'] = $this->handle.'[province]';		
		$_county .= $form->select($this->handle.'[province]', array(), isset($this->value['province'])?$this->value['province']:'', $_attributes);
		
		$_attributes = $this->attributes;		
		$_attributes['class'] .= ' province province_input';
		$_attributes['placeholder'] = t('Province');
		$_attributes['ccm-attribute-address-field-name'] = $this->handle.'[province]';		
		$_province = $form->text($this->handle.'[province]', isset($this->value['province'])?$this->value['province']:'', $_attributes);
		
		$_attributes = $this->attributes;		
		$_attributes['class'] .= ' province province_select';
		$_attributes['ccm-attribute-address-field-name'] = $this->handle.'[province]';		
		$_province .= $form->select($this->handle.'[province]', array(), isset($this->value['province'])?$this->value['province']:'', $_attributes);
		
		$_attributes = $this->attributes;		
		$_attributes['class'] .= ' state state_input';
		$_attributes['placeholder'] = t('State');
		$_attributes['ccm-attribute-address-field-name'] = $this->handle.'[province]';		
		$_state = $form->text($this->handle.'[province]', isset($this->value['province'])?$this->value['province']:'', $_attributes);
		
		$_attributes = $this->attributes;		
		$_attributes['class'] .= ' state state_select';
		$_attributes['placeholder'] = t('State');
		$_attributes['ccm-attribute-address-field-name'] = $this->handle.'[province]';		
		$_state .= $form->select($this->handle.'[province]', array(), isset($this->value['province'])?$this->value['province']:'', $_attributes);

		$_attributes = $this->attributes;		
		$_attributes['class'] .= ' country country_select';
		$_country = $form->select($this->handle.'[country]', $this->get_countries(), isset($this->value['country'])?$this->value['country']:'', $_attributes);
		
		$_attributes = $this->attributes;		
		$_attributes['class'] .= ' postal_code';
		$_attributes['placeholder'] = t('Postal Code');		
		$_postal_code = $form->text($this->handle.'[zipcode]', isset($this->value['zipcode'])?$this->value['zipcode']:'', $_attributes);
		
		$_attributes = $this->attributes;		
		$_attributes['class'] .= ' zipcode';
		$_attributes['placeholder'] = t('Zipcode');		
		$_zipcode = $form->text($this->handle.'[zipcode]', isset($this->value['zipcode'])?$this->value['zipcode']:'', $_attributes);
		
		$_attributes = $this->attributes;		
		$_attributes['class'] .= ' street';
		$_attributes['placeholder'] = t('Street');		
		$_street = $form->text($this->handle.'[street]', isset($this->value['street'])?$this->value['street']:'', $_attributes);
		
		$_attributes = $this->attributes;		
		$_attributes['class'] .= ' number';
		$_attributes['placeholder'] = t('Number');		
		$_number = $form->text($this->handle.'[number]', isset($this->value['number'])?$this->value['number']:'', $_attributes);
				
		$find = array('/{n}/', '/[,.:;!?]/', '/{address_1}/', '/{address_2}/', '/{city}/', '/{country}/', '/{zipcode}/', '/{postal_code}/', '/{province}/', '/{county}/', '/{state}/', '/{street}/', '/{number}/');
		$replace = array('<br />', '', $_address_1, $_address_2, $_city, $_country, $_zipcode, $_postal_code, $_province, $_county, $_state, $_street, $_number);
		
		$this->setAttribute('input', preg_replace($find, $replace, $this->get_format()));
		
		$this->addJavascript($this->load_provinces_js(), false);
		$this->addJavascript('if (typeof ccmFormidableAddressSetupStateProvinceSelector == \'function\') { ccmFormidableAddressSetupStateProvinceSelector(\''.$this->handle.'\'); }');			
	}
	
	public function validate() {
		
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidator($this->elementID, $this->label, $this->dependency_validation);
		
		if ($this->required) {
			
			$_format = $this->get_format();
				
			$_value = $this->request($this->handle);
				
			if (preg_match('/{address_1}/', $_format))
				$validator->required($_value['address_1']);
			
			if (preg_match('/{city}/', $_format))
				$validator->required($_value['city']);
			
			if (preg_match('/{province}/', $_format))
				$validator->required($_value['province']);
			
			if (preg_match('/{county}/', $_format))
				$validator->required($_value['province']);
			
			if (preg_match('/{state}/', $_format))
				$validator->required($_value['province']);
						
			if (preg_match('/{country}/', $_format))
				$validator->required($_value['country']);
			
			if (preg_match('/{postal_code}/', $_format))
				$validator->required($_value['zipcode']);
				
			if (preg_match('/{zipcode}/', $_format))
				$validator->required($_value['zipcode']);
				
			if (preg_match('/{street}/', $_format))
				$validator->required($_value['street']);
				
			if (preg_match('/{number}/', $_format))
				$validator->required($_value['number']);		
		}
		return $validator->getList();
	}
	
	public function result($value = array(),  $seperator = ', ') {			
		
		$_value = $this->value;
		if (!empty($value))
			$_value = $value['value'];
		
		$_val = array_filter((array)$_value);
		if (!empty($_val)) {
						
			$find = array('/{n}/', '/([,.:;!?])/', '/{address_1}/', '/{address_2}/', '/{city}/', '/{country}/', '/{zipcode}/', '/{postal_code}/', '/{province}/', '/{county}/', '/{state}/', '/{street}/', '/{number}/');
			$replace = array(', ', 
							 '$1', 
							 isset($_value['address_1'])?$_value['address_1']:'', 
							 isset($_value['address_2'])?$_value['address_2']:'',  
							 isset($_value['city'])?$_value['city']:'',  
							 isset($_value['country'])?$this->get_country_name($_value['country']):'', 
							 isset($_value['zipcode'])?$_value['zipcode']:'',  
							 isset($_value['zipcode'])?$_value['zipcode']:'',  
							 isset($_value['province'])?$this->get_province_name($_value['province'], $_value['country']):'',
							 isset($_value['province'])?$this->get_province_name($_value['province'], $_value['country']):'',
							 isset($_value['province'])?$this->get_province_name($_value['province'], $_value['country']):'',
							 isset($_value['street'])?$_value['street']:'', 
							 isset($_value['number'])?$_value['number']:'');
								 
			$_result = preg_replace($find, $replace, $this->get_format());
		}
		$this->setAttribute('result', $_result);
	}
	
	private function get_format() {
		$_format = strtolower($this->format);
		if ($_format == 'other')
			$_format = strtolower($this->format_other);
		
		return $_format;
	}
	
	private function get_countries() {
		$lc = Loader::helper('lists/countries');
		$countries = $lc->getCountries();
		asort($countries, SORT_LOCALE_STRING);
		return array_merge(array('' => t('Choose country')), $countries);
	}
	
	private function get_country_name($country) {
		$lc = Loader::helper('lists/countries');
		return $lc->getCountryName($country);
	}
	
	private function get_province_name($province, $country) {
		$h = Loader::helper('lists/states_provinces');
		$val = $h->getStateProvinceName($province, $country);
		if ($val == '') 
			return $province;
	
		return $val;
	}
	
	private function load_provinces_js() {
		$h = Loader::helper('lists/states_provinces');
		$return .= "var ccmFormidableAddressStatesTextList = '";
		$all = $h->getAll();
		foreach($all as $country => $countries) {
			foreach($countries as $value => $text) {
				$return .= addslashes($country) . ':' . addslashes($value) . ':' . addslashes($text) . "|";
			}
		}
		$return .= "';";
		return $return;
	}
	
}