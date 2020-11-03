<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementTime extends FormidableElement {
	
	public $element_text = 'Time Field';
	public $element_type = 'time';
	public $element_group = 'Special Elements';	
		
	public $properties = array(
		'label' => true,
		'label_hide' => true,
		'default' => array(
			'type' => 'input',
			'note' => '',
			'mask' => '99:99:99'
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
	
	public $dependency = array(
		'has_value_change' => true
	);
	
	private $hours24 = true;

	function __construct($elementID = 0) 
	{				
		parent::__construct($elementID);
		
		$this->properties['default']['note'] = array(
			t('Default time format (24 hours): HH:mm:ss')
		);
		
		$this->properties['appearance'] = array(
			'slider' => t('Create element with timeslider'),
			'select' => t('Selectboxes for hours, minutes, etc.'),
			'input' => t('Create masked input textfield (only 24-hours notation)')
		);
			
		$this->properties['format']['formats'] = array(
			'hh:mm TT' => 'hh:mm AM/PM',
			'hh:mm:ss TT' => 'hh:mm:ss AM/PM',
			'hh:mm tt' => 'hh:mm am/pm',
			'hh:mm:ss tt' => 'hh:mm:ss am/pm',
			'hh:mm' => 'hh:mm',									     
			'hh:mm:ss' => 'hh:mm:ss',
			'other' => t('Other format: ')
		);
		
		$this->properties['format']['note'] = array(
			'h - '.t('hour (no leading zero)'),
			'hh - '.t('hour (two digit)'),
			'm - '.t('minutes (no leading zero)'),
			'mm - '.t('minutes (two digit)'),
			's - '.t('seconds (no leading zero)'),
			'ss - '.t('seconds (two digit)'),
			'tt - '.t('am or pm for AM/PM'),
			'TT - '.t('AM or PM for AM/PM'),
			t('More information about timeformat: ').'<a href="http://trentrichardson.com/examples/timepicker/" target="_blank">'.t('click here').'</a>'
		);
		
		$this->properties['advanced']['note'] = array(
			t('Manage some advanced options of the timepicker'),
			t('Comma seperate options'),
			t('Example: hourMin: 8, hourMax: 16'),
			t('Possible options: ').'<a href="http://trentrichardson.com/examples/timepicker/" target="_blank">'.t('click here').'</a>'
		);
	}
	
	public function generate() 
	{				
		$form = Loader::helper('form');
		
		$this->hours24();

		// Generate selectboxes
		if ($this->appearance == 'select')
		{							
			$hour = false;
			if (!empty($this->value))
				$hour = date($this->hours24?"H":"h", strtotime($this->value));
						
			$hour_selector = '<select name="'.$this->handle.'_hour" id="'.$this->handle.'_hour" class="'.(string)$this->attributes['class'].' hour">';
			
			$selected = '';
			if ($hour == '')
				$selected = 'selected';
			$hour_selector .= '<option value=""' . $selected . '></option>';
			
			$j = $this->hours24?0:1;
			$hours = $this->hours24?23:12;						
			for ($i=$j; $i<=$hours; $i++) 
			{
				$selected = '';
				if (sprintf('%02d', $hour) == sprintf('%02d', $i) && $hour != '')
					$selected = 'selected';
					
				$h = $this->hours24?sprintf('%02d', $i):$i;
					
				$hour_selector .= '<option value="' . $h . '"' . $selected . '>' . sprintf('%02d', $i) . '</option>';
			}
			$hour_selector .= '</select>';
			
			$minute = false;
			if (!empty($this->value))
				$minute = date("i", strtotime($this->value));			
			
			$minute_selector = '<select name="'.$this->handle.'_minute" id="'.$this->handle.'_minute" class="'.(string)$this->attributes['class'].' minute">';
			$selected = '';
			if ($minute == '')
				$selected = 'selected';
			$minute_selector .= '<option value=""' . $selected . '></option>';
			
			for ($i = 0; $i <= 59; $i++) 
			{
				$selected = '';
				if ($minute == sprintf('%02d', $i) && $minute != '') 
					$selected = 'selected';

				$minute_selector .= '<option value="' . sprintf('%02d', $i) . '"' . $selected . '>' . sprintf('%02d', $i) . '</option>';
			}
			$minute_selector .= '</select>';			
			
			$second = false;
			if (!empty($this->value))
				$second = date("s", strtotime($this->value));
			
			$second_selector = '<select name="'.$this->handle.'_second" id="'.$this->handle.'_second" class="'.(string)$this->attributes['class'].' second">';
			$selected = '';
			if ($second == '')
				$selected = 'selected';
			$second_selector .= '<option value=""' . $selected . '></option>';
			
			for ($i = 0; $i <= 59; $i++) 
			{
				$selected = '';
				if ($second == sprintf('%02d', $i) && $second != '') 
					$selected = 'selected';

				$second_selector .= '<option value="' . sprintf('%02d', $i) . '"' . $selected . '>' . sprintf('%02d', $i) . '</option>';
			}
			$second_selector .= '</select>';
			
			
			if ($this->value != '')
				$ampm = date("A", strtotime($this->value));
			
			$ampm_selector = '<select name="'.$this->handle.'_ampm" id="'.$this->handle.'_ampm" class="'.(string)$this->attributes['class'].' ampm">';
			$selected = '';
			if ($ampm == '') 
				$selected = 'selected';
			$ampm_selector .= '<option value=""' . $selected . '></option>';
			
			$ampm_selector .= '<option value="AM"';
			$selected = '';
			if ($ampm == 'AM') 
				$ampm_selector .= 'selected';
			$ampm_selector .= '>AM</option>';
			$ampm_selector .= '<option value="PM"';
			$selected = '';
			if ($ampm == 'PM') 
				$ampm_selector .= 'selected';
			$ampm_selector .= '>PM</option>';
			$ampm_selector .= '</select>';	
							
													
			$format = preg_replace(array('/hh/', '/mm/', '/ss/', '/tt/', '/TT/'), array(' {qq} ', ' {ww} ', ' {ee} ', ' {rr} ', ' {rr} '), $this->format());
			$_time = preg_replace(array('/{qq}/', '/{ww}/', '/{ee}/', '/{rr}/'), array($hour_selector, $minute_selector, $second_selector, $ampm_selector), $format);				
		}
		
		// Generate inputfield
		if ($this->appearance == 'input')
		{								
			$this->attributes['placeholder'] = preg_replace(array('/h/', '/i/', '/m/', '/s/', '/t/'), array('hh', 'mm', 'mm', 'ss', 'am/pm'), $this->format());	
			$_time = $form->text($this->handle, !empty($this->value)?$this->result:'', $this->attributes);
			
			$_mask = preg_replace(array('/h/', '/i/', '/m/', '/s/', '/t/'), array('99', '99', '99', '99', 'aa'), $this->format());
			$this->addJavascript("if ($.fn.mask) { $('#".$this->handle."').mask('".$_mask."') }");
		}
		
		// Generate datepicker
		if ($this->appearance == 'slider')
		{
			if (strpos($this->attributes['class'], 'timeslider') === false)
				$this->attributes['class'] .= ' timeslider';	
					
			$_time = $form->text($this->handle, !empty($this->value)?$this->result:'', $this->attributes);
			
			if (!$this->hours24)			
				$_options[] ='ampm: true';	
			if (strpos($this->format(), 's') !== false)
				$_options[] ='showSecond: true';			
			$_options = @implode(',', $_options);
			
			if (intval($this->advanced) == 1)
				$_options = preg_replace('/"/', '\'', $this->advanced_value);
									
			$this->addJavascript("$('#".$this->handle."').timepicker({ timeFormat: '".$this->format(false)."', ".$_options." });");
		}
						
		$this->setAttribute('input', $_time);	
	}
	
	public function validate() {
		
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidator($this->elementID, $this->label, $this->dependency_validation);
		
		if ($this->required)
		{
			if ($this->appearance != 'select')
				$validator->required($this->request($this->handle));
			else
			{
				$time = @implode(':', array_filter(array($this->request($this->handle.'_hour'), $this->request($this->handle.'_minute'), $this->request($this->handle.'_second'))));
				$time = @implode(' ', array_filter(array($time, $this->request($this->handle.'_ampm'))));

				if (strtolower(date($this->format_translate(), strtotime($time))) != strtolower($time))
					$validator->add(t('Field "%s" is an invalid time'));		
			}
		}
		return $validator->getList();
	}
	
	public function post() 
	{	
		// Tweak for selector	
		if ($this->appearance != 'select')
			parent::post();	
		else
		{		
			$_value['value'] = '';
					
			$time = @implode(':', array_filter(array($this->request($this->handle.'_hour'), $this->request($this->handle.'_minute'), $this->request($this->handle.'_second'))));
			$time = @implode(' ', array_filter(array($time, $this->request($this->handle.'_ampm'))));
			if (!empty($time))
				$_value['value'] = $time;
			
			$this->value($_value);	
		}
	}
			
	public function value($value = array()) {		
				
		if (!empty($value))
			$_value = is_array($value)?$value['value']:(string)$value;
		
		if (!empty($_value)) 
		{
			if ($this->appearance == 'input' || $this->appearance == 'slider')	
				$_value = date('H:i:s', strtotime($_value));
		}
					
		parent::value(array('value' => $_value));			
	}
	
	public function result($value = array(), $seperator = ', ') {		
		
		$_value = $this->value;
		if (!empty($value))
			$_value = $value['value'];
		
		if (!empty($_value))	
			$_value = date($this->format_translate(), strtotime($_value));
							
		parent::result(array('value' => $_value));
	}
	
	private function format($preg_replace = true)
	{
		$_format = $this->format;
		if ($_format == 'other')
			$_format = $this->format_other;
		
		if ($preg_replace)	
			return preg_replace('/(\w\1+)/', '$1', $_format);
			
		return $_format;	
	}
	
	private function format_translate() 
	{			
		$this->hours24();
		
		$replace = array('h', 'g', 'i', 'i', 's', 's', 'A', 'a');	
		if ($this->hours24)
			$replace = array('H', 'G', 'i', 'i', 's', 's', 'A', 'a');
			
		$pattern = array('/hh/', '/h/', '/mm/', '/m/', '/ss/', '/s/', '/TT/', '/tt/');

		return preg_replace($pattern, $replace, $this->format());
	}

	private function hours24()
	{
		if (strpos(strtolower($this->format(false)), 't') !== false)
			$this->hours24 = false;
	}
}