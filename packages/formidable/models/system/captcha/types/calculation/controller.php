<?php     defined('C5_EXECUTE') or die(_("Access Denied."));

class CalculationSystemCaptchaTypeController extends SystemCaptchaTypeController {
	
	private $first_int = 0;
	private $second_int = 0;
	
	private $functions = '';
	private $selected_function = 0;
	
	private $fonts = '';
	private $selected_font = '';
	
	private $backgrounds = '';
	private $selected_background = '';
	
	private $expiry_time;
		
	public function __construct() {

		$this->first_int = rand(1,10);
		$this->second_int = rand(1,10);
		
		$this->functions = array('+', '-', '*');
		
		$this->selected_function = array_rand($this->functions, 1);
		
		$this->fonts = array (dirname(__FILE__)."/fonts/BOD_I.TTF", 
							  dirname(__FILE__)."/fonts/CALIST.TTF", 
							  dirname(__FILE__)."/fonts/ARIAL.TTF");	
		
		$this->selected_font = array_rand($this->fonts, 1);
		
		$this->backgrounds = array (dirname(__FILE__)."/img/1.png", 
							 	    dirname(__FILE__)."/img/2.png",  
							  		dirname(__FILE__)."/img/3.png",
									dirname(__FILE__)."/img/4.png");	
		
		$this->selected_background = array_rand($this->backgrounds, 1);	
		
		$this->expiry_time = 900;							
	}
		
	public function getSystemCaptchaTypeName() {
		return t("Calculation Captcha");
	}
			
	public function display() {
		$ci = Loader::helper('concrete/urls');
		
		echo '<div>';
		echo '<img src="'.$ci->getToolsURL('captcha').'?nocache='.time().'" alt="'.t('Captcha Code').'" onclick="this.src = \''.$ci->getToolsURL('captcha').'?nocache='.$time.'\'" class="ccm-captcha-image" />';
		echo '</div>';

	}
	
	public function label() {
		$form = Loader::helper('form');
		echo $form->label('captcha', t('Please solve the equation'));
	}
		
	public function displayCaptchaPicture() {		
		
		$_SESSION['calculation_captcha_code'] = $this->first_int.' '.$this->functions[$this->selected_function].' '.$this->second_int;
		$_SESSION['calculation_captcha_time'] = time();
		
		return $this->generateImage();
	}
	
	public function showInput($args = false)
	{
		$attribs = '';
		if (is_array($args)) {
			foreach($args as $key => $value) {
				$attribs .= $key . '="' . $value . '" ';
			}
		}
		echo '<div><input type="text" name="ccmCaptchaCode" class="ccm-input-captcha" ' . $attribs . ' /></div>';
		echo '<div class="ccm-input-captcha-note">' . t('Please solve the equation') . ' (' . t('click the image to see another equation') . ')</div>';
	}
	 	
	public function check($fieldName = 'ccmCaptchaCode') {
		
		$str = Loader::helper('validation/strings');
		$nbr = Loader::helper('validation/numbers');
					
		$value = $_REQUEST[$fieldName];
		
		if (!$str->notempty($value))
			return false;	
			
		if (!$nbr->integer($value))
			return false;	
			
		if ($this->is_expired($_SESSION['calculation_captcha_time']))
			return false;	
		
		if (intval($value) != $this->solve())
			return false;
			
		return true;
	}
	
	
	private function generateImage() {
				
		$image = imagecreatefrompng($this->backgrounds[$this->selected_background]); 
		
		$white = imagecolorallocate($image, 255, 255, 255); 
		$black = imagecolorallocate($image, 0, 0, 0); 
	
		for ($q=1; $q<=4; $q++)
		{ 
			$size   		= rand (12,18);       		// Which font-size? 
			$angle     		= rand (-25,25);     		// Rotation of the characters 
			
			$top    		= rand (18,27);      		// Top margin
			$top_shade    	= $top - 1;     			// Shadow 
			
			$width          = $width + 28;        		// Width of the characters
			$left_margin    = $width;             		// Left margin 
			$left_shadow 	= $left_margin - 1;    		// Shadow 
			
			switch ($q) 
			{
				case 1: 	$char = $this->first_int;							break;
				case 2: 	$char = $this->functions[$this->selected_function]; break;
				case 3: 	$char = $this->second_int;							break;
				case 4: 	$char = '=';										break;
			}
					
			imagettftext($image, $size, $angle, $left_shadow, $top_shade, $black, $this->fonts[$this->selected_font], $char); 
		} 
		
		ob_start();
		imagepng($image);
		return ob_get_contents();
		ob_end_clean();			
	}
	
	private function solve() {
		
		$result = 0;
		
		list($first_int, $function, $second_int) = @explode(' ', $_SESSION['calculation_captcha_code']);
		switch (trim($function))
		{
			case '-':	$result = $first_int - $second_int;	 break; 
			case '+':	$result = $first_int + $second_int;	 break;
			case '*':	$result = $first_int * $second_int;	 break;	
		}
		return $result;			
	}
	
	private function is_expired($ctime)
	{
		$expired = true;		
		if (!is_numeric($this->expiry_time) || $this->expiry_time < 1) {
			$expired = false;
		} else if (time() - $ctime < $this->expiry_time) {
			$expired = false;
		}		
		return $expired;
	}
}	
