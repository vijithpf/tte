<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

if (!defined(FORMIDABLE_ELEMENT_YEAR_SELECT))
	define(FORMIDABLE_ELEMENT_YEAR_SELECT, date("Y")+10);
				
Loader::model('formidable/element', 'formidable');

class FormidableElementDate extends FormidableElement {
	
	public $element_text = 'Date Field';
	public $element_type = 'date';
	public $element_group = 'Special Elements';	
		
	public $properties = array(
		'label' => true,
		'label_hide' => true,
		'default' => array(
			'type' => 'input',
			'note' => '',
			'mask' => '99/99/9999'
		),
		'required' => true,
		'appearance' => '',
		'format' => array(
			'formats' => '',
			'note' => ''
		),
		'tooltip' => true,
		'advanced' => array(
			'note' => ''
		),
		'handling' => true
	);
	
	public $advanced = 1;
	public $advanced_value = 'changeYear:true, yearRange:"1900:c+20", changeMonth:true';		
	
	private $_formats = array('j' => 'd',
							  'd' => 'dd',											
							  'z' => 'o',									
							  'z' => 'oo',										
							  'D' => 'D',										
							  'l' => 'DD',										
							  'n' => 'm',										
							  'm' => 'mm',										
							  'F' => 'MM',
							  'M' => 'M',
							  'y' => 'y',
							  'Y' => 'yy');
	
	public $dependency = array(
		'has_value_change' => false
	);
	
	function __construct($elementID = 0) 
	{				
		parent::__construct($elementID);
		
		$this->properties['default']['note'] = array(
			t('Default date format: mm/dd/yyyy')
		);
		
		$this->properties['appearance'] = array(
			'picker' => t('Create element with datepicker'),			
			'select' => t('Selectboxes for years, months, days'),
			'input' => t('Create masked input textfield (only dd, mm and yyyy input)')
		);
			
		$this->properties['format']['formats'] = array(
			'mm/dd/yyyy' => 'mm/dd/yyyy',
			'mm/dd/yy' => 'mm/dd/yy',
			'dd/mm/yyyy' => 'dd/mm/yyyy',
			'dd/mm/yy' => 'dd/mm/yy',
			'dd-mm-yyyy' => 'dd-mm-yyyy',
			'dd-mm-yy' => 'dd-mm-yy',
			'yyyy/mm/dd' => 'yyyy/mm/dd',
			'yy/mm/dd' => 'yy/mm/dd',
			'DD MM d yy' => 'DD MM d yy',		
			'other' => t('Other format: ')
		);
		
		$this->properties['format']['note'] = array(
			'd - '.t('day of month (no leading zero)'),
			'dd - '.t('day of month (two digit)'),
			'o - '.t('day of the year (no leading zeros)'),
			'oo - '.t('day of the year (three digit)'),
			'D - '.t('day name short'),
			'DD - '.t('day name long'),
			'm - '.t('month of year (no leading zero)'),
			'mm - '.t('month of year (two digit)'),
			'M - '.t('month name short'),
			'MM - '.t('month name long'),
			'y - '.t('year (two digit)'),
			'yy - '.t('year (four digit)'),
			t('More information about dateformat: ').'<a href="http://docs.jquery.com/UI/Datepicker/formatDate" target="_blank">'.t('click here').'</a>'
		);
		
		$this->properties['advanced']['note'] = array(
			t('Manage some advanced options of the datepicker'),
			t('Comma seperate options'),
			t('Example: changeYear:true, yearRange:"c-10:c+10"'),
			t('Possible options: ').'<a href="http://jqueryui.com/demos/datepicker/" target="_blank">'.t('click here').'</a>'
		);
		
		
	}
	
	public function generate() 
	{				
		$form = Loader::helper('form');
		
		// Generate selectboxes
		if ($this->appearance == 'select')
		{							
			if (!empty($this->value))
				$day = date("d", strtotime($this->value));
			
			$day_selector = '<select name="'.$this->handle.'_day" id="'.$this->handle.'_day" class="'.(string)$this->attributes['class'].' day">';
			
			$selected = '';
			if ($day == sprintf('%02d', $i))
				$selected = 'selected';
			$day_selector .= '<option value=""' . $selected . '></option>';
			
			for ($i = 1; $i <= 31; $i++) 
			{
				$selected = '';
				if ($day == sprintf('%02d', $i))
					$selected = 'selected';
					
				$day_selector .= '<option value="' . sprintf('%02d', $i) . '"' . $selected . '>' . sprintf('%02d', $i) . '</option>';
			}
			$day_selector .= '</select>';
			
			if (!empty($this->value))
				$month = date("m", strtotime($this->value));			
			
			$month_selector = '<select name="'.$this->handle.'_month" id="'.$this->handle.'_month" class="'.(string)$this->attributes['class'].' month">';
			
			$selected = '';
			if ($month == sprintf('%02d', $i))
				$selected = 'selected';
			$month_selector .= '<option value=""' . $selected . '></option>';
			
			for ($i = 1; $i <= 12; $i++) 
			{
				$selected = '';
				if ($month == sprintf('%02d', $i)) 
					$selected = 'selected';

				$month_selector .= '<option value="' . sprintf('%02d', $i) . '"' . $selected . '>' . sprintf('%02d', $i) . '</option>';
			}
			$month_selector .= '</select>';			
			
			if (!empty($this->value))
				$year = date("Y", strtotime($this->value));
			
			$year_selector = '<select name="'.$this->handle.'_year" id="'.$this->handle.'_year" class="'.(string)$this->attributes['class'].' year">';
			
			$selected = '';
			if ($year == sprintf('%02d', $i))
				$selected = 'selected';
			$year_selector .= '<option value=""' . $selected . '></option>';
							 
			for ($i = FORMIDABLE_ELEMENT_YEAR_SELECT; $i >= 1970; $i--) 
			{
				$selected = '';
				if ($year == sprintf('%02d', $i)) 
					$selected = 'selected';

				$year_selector .= '<option value="' . sprintf('%02d', $i) . '"' . $selected . '>' . sprintf('%02d', $i) . '</option>';
			}
			$year_selector .= '</select>';						
													
			$format = preg_replace(array('/d/', '/m/', '/y/'), array(' {qq} ', ' {ww} ', ' {ee} '), $this->format());
			$_date = preg_replace(array('/{qq}/', '/{ww}/', '/{ee}/'), array($day_selector, $month_selector, $year_selector), $format);			
			//$_date .= $form->hidden($this->handle, $this->value);				
		}
		
		// Generate inputfield
		if ($this->appearance == 'input')
		{								
			$this->attributes['placeholder'] = preg_replace(array('/d/', '/m/', '/y/'), array('dd', 'mm', 'yyyy'), $this->format());			
			$_date = $form->text($this->handle, !empty($this->value)?date($this->format_translate(), strtotime($this->value)):'', $this->attributes);
			
			$_mask = preg_replace(array('/d/', '/m/', '/y/'), array('99', '99', '9999'), $this->format());
			$this->addJavascript("if ($.fn.mask) { $('#".$this->handle."').mask('".$_mask."') }");
		}
		
		// Generate datepicker
		if ($this->appearance == 'picker')
		{
			if (strpos($this->attributes['class'], 'datepicker') === false)
				$this->attributes['class'] .= ' datepicker';	
					
			$_date  = $form->text($this->handle.'_date', !empty($this->value)?$this->value_translate():'', $this->attributes);
			$_date .= $form->hidden($this->handle, !empty($this->value)?date('Y-m-d', strtotime($this->value)):'');
			
			$_options = 'changeYear: true, showAnim: \'fadeIn\'';
			if (intval($this->advanced) == 1)
				$_options = preg_replace('/"/', '\'', $this->advanced_value);
			
			$_picker_format = preg_replace('/yyyy/', 'yy', $this->format(false));			
			$this->addJavascript("$('#".$this->handle."_date').datepicker({ altField:'#".$this->handle."', altFormat:'yy-mm-dd', dateFormat:'".$_picker_format."',".$_options."});");
		}
						
		$this->setAttribute('input', $_date);	
	}
	
	public function validate() {
		
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidator($this->elementID, $this->label, $this->dependency_validation);		
		
		if ($this->required)
		{
			if ($this->appearance == 'picker') {
				$validator->required($this->request($this->handle));				
				if (!$this->parse_date_from_format('Y-m-d', $this->request($this->handle)))
					$validator->add(t('Field "%s" is an invalid date'));
			} elseif ($this->appearance == 'select') {
				$date = array($this->request($this->handle.'_month'), $this->request($this->handle.'_day'), $this->request($this->handle.'_year'));
				$validator->required(@implode('-', $date));			

				if (!checkdate($date[0], $date[1], $date[2]))
					$validator->add(t('Field "%s" is an invalid date'));	
			} else {
				$validator->required($this->request($this->handle));				
				if (!$this->parse_date_from_format($this->format_translate(), $this->request($this->handle)))
					$validator->add(t('Field "%s" is an invalid date'));
			}
		}
		return $validator->getList();
	}
	
	public function post() 
	{	
		// Tweak for datepicker: still a value, even when there is no date...	
		$_value['value'] = '';
		
		if ($this->appearance == 'select')
		{
			$time = @implode('/', array_filter(array($this->request($this->handle.'_month'), $this->request($this->handle.'_day'), $this->request($this->handle.'_year'))));
			if (!empty($time))
				$_value['value'] = $time;
			
			$this->value($_value);	
		}
		else
			parent::post();	
	}
		
	public function value($value = array()) {	
		
		if (!empty($value))
			$_value = $value['value'];
							
		if (!empty($_value)) 
		{
			if ($this->appearance == 'input')	
				$_date = $this->parse_date_from_format($this->format_translate(), $_value);
		}
					
		if (is_array($_date))
			$_value = @implode('-', array($_date['year'], $_date['month'], $_date['day']));
						
		parent::value(array('value' => $_value));			
	}
	
	public function result($value = array(), $seperator = ', ') {		
				
		$_value = $this->value;
		if (!empty($value))
			$_value = $value['value'];
				
		if (empty($_value)) {
			parent::result();
			return;					
		}

		// Generate selector result
		if ($this->appearance == 'select') 
		{
			$_selected = array(date("d", strtotime($_value)), date("m", strtotime($_value)), date("Y", strtotime($_value)));
			$_value = preg_replace(array('/d/', '/m/', '/y/'), $_selected, $this->format());
		}
		
		// Generate input result
		if ($this->appearance == 'input')
		{							
			$_date = $this->parse_date_from_format('Y-m-d', $_value);
			if (is_array($_date)) {
				$_value = date($this->format_translate(), strtotime($_date['year'].'-'.$_date['month'].'-'.$_date['day']));
				//$_value = preg_replace(array('/Y/', '/m/', '/d/'), array($_date['year'], $_date['month'], $_date['day']), $this->format());				
			}
		}
		
		// Generate datepicker result
		if ($this->appearance == 'picker') 
			$_value = !empty($_value)?$this->value_translate($_value):'';
				
		parent::result(!empty($_value)?array('value' => $_value):'');
	}
	
	private function format($preg_replace = true)
	{
		$_format = $this->format;
		if ($_format == 'other')
			$_format = $this->format_other;
		
		if ($preg_replace)	
			return preg_replace('/(\w)\1+/', '$1', strtolower($_format));
			
		return $_format;	
	}
	
	private function format_translate() 
	{		
		$_format = preg_replace('/yyyy/', 'yy', $this->format(false));

		if (sizeof($this->_formats) > 0) 
			foreach (array_values($this->_formats) as $cf)
				$pattern[] = '/\b'.$cf.'\b/';
	
		return preg_replace($pattern, array_keys($this->_formats), $_format);
	}

	private function value_translate($value = '')
	{		
		$_format = $this->format_translate();
		
		$_value = $this->value;
		if (!empty($value))
			$_value = $value;
		
		if (preg_match('/dayNames:\[(.*?)\]/', $this->advanced_value, $ret) && strstr($_format, 'l'))
		{
			$days = explode(",", str_replace(array('"', '\''), '', $ret[1]));
			foreach ($days as $i => $day)
				$adv_value[date('l', mktime(0, 0, 0, 8, $i, 2011))] = trim($day);
		}
		
		if (preg_match('/dayNamesMin:\[(.*?)\]/', $this->advanced_value, $ret) && strstr($_format, 'D'))
		{
			$days = explode(",", str_replace(array('"', '\''), '', $ret[1]));
			foreach ($days as $i => $day)
				$adv_value[date('D', mktime(0, 0, 0, 8, $i, 2011))] = trim($day);
		}				
		
		if (preg_match('/monthNames:\[(.*?)\]/', $this->advanced_value, $ret) && strstr($_format, 'F'))
		{
			$months = explode(",", str_replace(array('"', '\''), '', $ret[1]));
			foreach ($months as $i => $month)
				$adv_value[date('F', mktime(0, 0, 0, $i+1, 1, 2011))] = trim($month);
		}
		
		if (preg_match('/monthNamesMin:\[(.*?)\]/', $this->advanced_value, $ret) && strstr($_format, 'M'))
		{
			$months = explode(",", str_replace(array('"', '\''), '', $ret[1]));
			foreach ($months as $i => $month)
				$adv_value[date('M', mktime(0, 0, 0, $i+1, 1, 2011))] = trim($month);
		}
		
		if (sizeof(array_keys((array)$adv_value)) > 0)
			foreach (array_keys($adv_value) as $cf)
				$pattern[] = '/\b'.$cf.'\b/';			
					
		return @preg_replace((array)$pattern, (array)array_values($adv_value), date($_format, strtotime($_value)));
	}
	
	private function parse_date_from_format($format, $date)
	{	
		if (empty($date))
			return '';

		$keys = array(
			'Y' => array('year', '\d{4}'),              //Année sur 4 chiffres
			'y' => array('year', '\d{2}'),              //Année sur 2 chiffres
			'm' => array('month', '\d{2}'),             //Mois au format numérique, avec zéros initiaux
			'n' => array('month', '\d{1,2}'),           //Mois sans les zéros initiaux
			'M' => array('month', '[A-Z][a-z]{3}'),     //Mois, en trois lettres, en anglais
			'F' => array('month', '[A-Z][a-z]{2,8}'),   //Mois, textuel, version longue; en anglais, comme January ou December
			'd' => array('day', '\d{2}'),               //Jour du mois, sur deux chiffres (avec un zéro initial)
			'j' => array('day', '\d{1,2}'),             //Jour du mois sans les zéros initiaux
			'D' => array('day', '[A-Z][a-z]{2}'),       //Jour de la semaine, en trois lettres (et en anglais)
			'l' => array('day', '[A-Z][a-z]{6,9}'),     //Jour de la semaine, textuel, version longue, en anglais
		);

		// convert format string to regex
		$regex = '';
		$chars = str_split($format);
		foreach ( $chars as $n => $char ) {
			$lastChar = isset($chars[$n-1]) ? $chars[$n-1] : '';
			$skipCurrent = '\\' == $lastChar;
			if ( !$skipCurrent && isset($keys[$char]) ) {
				$regex .= '(?P<'.$keys[$char][0].'>'.$keys[$char][1].')';
			}
			else if ( '\\' == $char ) {
				$regex .= $char;
			}
			else {
				$regex .= preg_quote($char);
			}
		}
							
		$dt = array();
		if (!preg_match('#^'.$regex.'$#', $date, $dt))
			return false;
						
		foreach ($dt as $k => $v )
			if (is_int($k))
				unset($dt[$k]);
		
		if (!isset($dt['year']))
			$dt['year'] = date('Y');

		if (!isset($dt['month']))
			$dt['month'] = date('m');

		if (!isset($dt['day']))
			$dt['day'] = date('d');

		if (!checkdate($dt['month'], $dt['day'], $dt['year']))
			return false;				
		
		return $dt;
	}
}