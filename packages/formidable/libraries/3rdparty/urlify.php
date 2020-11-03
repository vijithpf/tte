<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

/**
 * A PHP port of URLify.js from the Django project
 * (https://github.com/django/django/blob/master/django/contrib/admin/static/admin/js/urlify.js).
 * Handles symbols from Latin languages, Greek, Turkish, Russian, Ukrainian,
 * Czech, Polish, and Latvian. Symbols it cannot transliterate
 * it will simply omit.
 *
 * Usage:
 *
 *     echo URLify::filter (' J\'�tudie le fran�ais ');
 *     // "jetudie-le-francais"
 *     
 *     echo URLify::filter ('Lo siento, no hablo espa�ol.');
 *     // "lo-siento-no-hablo-espanol"
 */
class URLify {
	public static $maps = array (
		'de' => array ( /* German */
			'�' => 'Ae', '�' => 'Oe', '�' => 'Ue', '�' => 'ae', '�' => 'oe', '�' => 'ue', '�' => 'ss',
			'?' => 'SS'
		),
		'latin' => array (
			'�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A','A' => 'A', '�' => 'AE', '�' =>
			'C', '�' => 'E', '�' => 'E', '�' => 'E', '�' => 'E', '�' => 'I', '�' => 'I', '�' => 'I',
			'�' => 'I', '�' => 'D', '�' => 'N', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O', '�' =>
			'O', 'O' => 'O', '�' => 'O','?' => 'S','?' => 'T', '�' => 'U', '�' => 'U', '�' => 'U', '�' => 'U', 'U' => 'U',
			'�' => 'Y', '�' => 'TH', '�' => 'ss', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a', '�' =>
			'a', '�' => 'a', 'a' => 'a', '�' => 'ae', '�' => 'c', '�' => 'e', '�' => 'e', '�' => 'e', '�' => 'e',
			'�' => 'i', '�' => 'i', '�' => 'i', '�' => 'i', '�' => 'd', '�' => 'n', '�' => 'o', '�' =>
			'o', '�' => 'o', '�' => 'o', '�' => 'o', 'o' => 'o', '�' => 'o', '?' => 's', '?' => 't', '�' => 'u', '�' => 'u',
			'�' => 'u', '�' => 'u', 'u' => 'u', '�' => 'y', '�' => 'th', '�' => 'y'
		),
		'latin_symbols' => array (
			'�' => '(c)'
		),
		'el' => array ( /* Greek */
			'a' => 'a', '�' => 'b', '?' => 'g', 'd' => 'd', 'e' => 'e', '?' => 'z', '?' => 'h', '?' => '8',
			'?' => 'i', '?' => 'k', '?' => 'l', '�' => 'm', '?' => 'n', '?' => '3', '?' => 'o', 'p' => 'p',
			'?' => 'r', 's' => 's', 't' => 't', '?' => 'y', 'f' => 'f', '?' => 'x', '?' => 'ps', '?' => 'w',
			'?' => 'a', '?' => 'e', '?' => 'i', '?' => 'o', '?' => 'y', '?' => 'h', '?' => 'w', '?' => 's',
			'?' => 'i', '?' => 'y', '?' => 'y', '?' => 'i',
			'?' => 'A', '?' => 'B', 'G' => 'G', '?' => 'D', '?' => 'E', '?' => 'Z', '?' => 'H', 'T' => '8',
			'?' => 'I', '?' => 'K', '?' => 'L', '?' => 'M', '?' => 'N', '?' => '3', '?' => 'O', '?' => 'P',
			'?' => 'R', 'S' => 'S', '?' => 'T', '?' => 'Y', 'F' => 'F', '?' => 'X', '?' => 'PS', 'O' => 'W',
			'?' => 'A', '?' => 'E', '?' => 'I', '?' => 'O', '?' => 'Y', '?' => 'H', '?' => 'W', '?' => 'I',
			'?' => 'Y'
		),
		'tr' => array ( /* Turkish */
			's' => 's', 'S' => 'S', 'i' => 'i', 'I' => 'I', '�' => 'c', '�' => 'C', '�' => 'u', '�' => 'U',
			'�' => 'o', '�' => 'O', 'g' => 'g', 'G' => 'G'
		),
		'ru' => array ( /* Russian */
			'?' => 'a', '?' => 'b', '?' => 'v', '?' => 'g', '?' => 'd', '?' => 'e', '?' => 'yo', '?' => 'zh',
			'?' => 'z', '?' => 'i', '?' => 'j', '?' => 'k', '?' => 'l', '?' => 'm', '?' => 'n', '?' => 'o',
			'?' => 'p', '?' => 'r', '?' => 's', '?' => 't', '?' => 'u', '?' => 'f', '?' => 'h', '?' => 'c',
			'?' => 'ch', '?' => 'sh', '?' => 'sh', '?' => '', '?' => 'y', '?' => '', '?' => 'e', '?' => 'yu',
			'?' => 'ya',
			'?' => 'A', '?' => 'B', '?' => 'V', '?' => 'G', '?' => 'D', '?' => 'E', '?' => 'Yo', '?' => 'Zh',
			'?' => 'Z', '?' => 'I', '?' => 'J', '?' => 'K', '?' => 'L', '?' => 'M', '?' => 'N', '?' => 'O',
			'?' => 'P', '?' => 'R', '?' => 'S', '?' => 'T', '?' => 'U', '?' => 'F', '?' => 'H', '?' => 'C',
			'?' => 'Ch', '?' => 'Sh', '?' => 'Sh', '?' => '', '?' => 'Y', '?' => '', '?' => 'E', '?' => 'Yu',
			'?' => 'Ya'
		),
		'uk' => array ( /* Ukrainian */
			'?' => 'Ye', '?' => 'I', '?' => 'Yi', '?' => 'G', '?' => 'ye', '?' => 'i', '?' => 'yi', '?' => 'g'
		),
		'cs' => array ( /* Czech */
			'c' => 'c', 'd' => 'd', 'e' => 'e', 'n' => 'n', 'r' => 'r', '�' => 's', 't' => 't', 'u' => 'u',
			'�' => 'z', 'C' => 'C', 'D' => 'D', 'E' => 'E', 'N' => 'N', 'R' => 'R', '�' => 'S', 'T' => 'T',
			'U' => 'U', '�' => 'Z'
		),
		'pl' => array ( /* Polish */
			'a' => 'a', 'c' => 'c', 'e' => 'e', 'l' => 'l', 'n' => 'n', '�' => 'o', 's' => 's', 'z' => 'z',
			'z' => 'z', 'A' => 'A', 'C' => 'C', 'E' => 'e', 'L' => 'L', 'N' => 'N', '�' => 'O', 'S' => 'S',
			'Z' => 'Z', 'Z' => 'Z'
		),
		'ro' => array ( /* Romanian */
			'a' => 'a', '�' => 'a', '�' => 'i', '?' => 's', '?' => 't'
		),
		'lv' => array ( /* Latvian */
			'a' => 'a', 'c' => 'c', 'e' => 'e', 'g' => 'g', 'i' => 'i', 'k' => 'k', 'l' => 'l', 'n' => 'n',
			'�' => 's', 'u' => 'u', '�' => 'z', 'A' => 'A', 'C' => 'C', 'E' => 'E', 'G' => 'G', 'I' => 'i',
			'K' => 'k', 'L' => 'L', 'N' => 'N', '�' => 'S', 'U' => 'u', '�' => 'Z'
		),
		'lt' => array ( /* Lithuanian */
			'a' => 'a', 'c' => 'c', 'e' => 'e', 'e' => 'e', 'i' => 'i', '�' => 's', 'u' => 'u', 'u' => 'u', '�' => 'z',
			'A' => 'A', 'C' => 'C', 'E' => 'E', 'E' => 'E', 'I' => 'I', '�' => 'S', 'U' => 'U', 'U' => 'U', '�' => 'Z'
		)
	);

	/**
	 * List of words to remove from URLs.
	 */
	public static $remove_list = array (
		'a', 'an', 'as', 'at', 'before', 'but', 'by', 'for', 'from',
		'is', 'in', 'into', 'like', 'of', 'off', 'on', 'onto', 'per',
		'since', 'than', 'the', 'this', 'that', 'to', 'up', 'via',
		'with'
	);

	/**
	 * The character map.
	 */
	private static $map = array ();

	/**
	 * The character list as a string.
	 */
	private static $chars = '';

	/**
	 * The character list as a regular expression.
	 */
	private static $regex = '';

	/**
	 * The current language
	 */
	private static $language = '';

	/**
	 *  returns the remove list from the concrete5 config or the default from this urlify library
	 *
	 * @return array
	 * @author Ryan Tyler ryan@concrete5.org
	*/
	public function get_removed_list() {
		$remove_list = Config::get('SEO_EXCLUDE_WORDS');
		if(!isset($remove_list)) {
			return self::$remove_list;
		}
		$remove_array = explode(',', $remove_list);
		$remove_array = array_map('trim', $remove_array);
		$remove_array = array_filter($remove_array, 'strlen');
		return $remove_array;
	}


	public function get_original_removed_list() {
		return self::$remove_list;
	}

	/**
	 * Initializes the character map.
	 */
	private static function init ($language = "") {
		if (count (self::$map) > 0 && (($language == "") || ($language == self::$language))) {
			return;
		}

		/* Is a specific map associated with $language ? */
		if (isset(self::$maps[$language]) && is_array(self::$maps[$language])) {
			/* Move this map to end. This means it will have priority over others */
			$m = self::$maps[$language];
			unset(self::$maps[$language]);
			self::$maps[$language] = $m;
			
			/* Reset static vars */
			self::$language = $language;
			self::$map = array();
			self::$chars = '';
		}

		self::$remove_list = self::get_removed_list();

		foreach (self::$maps as $map) {
			foreach ($map as $orig => $conv) {
				self::$map[$orig] = $conv;
				self::$chars .= $orig;
			}
		}

		self::$regex = '/[' . self::$chars . ']/u';
	}

	/**
	 * Add new characters to the list. `$map` should be a hash.
	 */
	public static function add_chars ($map) {
		if (! is_array ($map)) {
			throw new LogicException ('$map must be an associative array.');
		}
		self::$maps[] = $map;
		self::$map = array ();
		self::$chars = '';
	}

	/**
	 * Append words to the remove list. Accepts either single words
	 * or an array of words.
	 */
	public static function remove_words ($words) {
		$words = is_array ($words) ? $words : array ($words);
		self::$remove_list = array_merge (self::$remove_list, $words);
	}

	/**
	 * Transliterates characters to their ASCII equivalents.
	 * $language specifies a priority for a specific language. 
	 * The latter is useful if languages have different rules for the same character.
	 */
	public static function downcode ($text, $language = "") {
		self::init ($language);

		if (@preg_match_all (self::$regex, $text, $matches)) {
			for ($i = 0; $i < count ($matches[0]); $i++) {
				$char = $matches[0][$i];
				if (isset (self::$map[$char])) {
					$text = str_replace ($char, self::$map[$char], $text);
				}
			}
		}
		return $text;
	}

	/**
	 * Filters a string, e.g., "Petty theft" to "petty-theft"
	 */
	public static function filter ($text, $length = 60, $language = "") {
		$text = self::downcode ($text,$language);

		// remove all these words from the string before urlifying
		$text = preg_replace ('/\b(' . join ('|', self::$remove_list) . ')\b/i', '', $text);

		// if downcode doesn't hit, the char will be stripped here
		$text = preg_replace ('/[^-\w\s]/', '', $text);		// remove unneeded chars
		$text = preg_replace ('/^\s+|\s+$/', '', $text);	// trim leading/trailing spaces
		$text = preg_replace ('/[-\s]+/', '-', $text);		// convert spaces to hyphens
		$text = strtolower ($text);							// convert to lowercase
		return trim (substr ($text, 0, $length), '-');	// trim to first $length chars
	}

	/**
	 * Alias of `URLify::downcode()`.
	 */
	public static function transliterate ($text) {
		return self::downcode ($text);
	}
}