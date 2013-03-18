<?php

// why doesn't this function exist?
if(!function_exists('mb_str_replace')) {
	function mb_str_replace($search, $replace, $subject){
		return preg_replace('@'.preg_quote($search).'@u',$replace,$subject);
	}
}
if (!function_exists('mb_str_split')) {
    function mb_str_split($str, $length = 1)
    {
        preg_match_all('/.{1,' . $length . '}/us', $str, $matches);
        return $matches[0];
    }
}

class StaticString {
	/* static methods wrapping multibyte */

	/**
	 * Wrapper for substr
	 */
	public static function substr ($string, $start, $length = null) {
		if(String::$multibyte) {
			return new String(mb_substr($string, $start, $length, String::$multibyte_encoding));
		}
		return new String(substr($string, $start, $length));
	}

	/**
	 * Equivelent of Javascript's String.substring
	 * @link http://www.w3schools.com/jsref/jsref_substring.asp
	 */
	public static function substring ($string, $start, $end) {
		if(empty($length)) {
			return self::substr($string, $start);
		}
		return self::substr($string, $end - $start);
	}

	public function charAt ($str, $point) {
		return self::substr($str, $point, 1);
	}

	public function charCodeAt ($str, $point) {
		return ord(self::substr($str, $point, 1));
	}

	public static function concat () {
		$args = func_get_args();
		$r = "";
		foreach($args as $arg) {
			$r .= (string)$arg;
		}
		return $arg;
	}

	public static function fromCharCode ($code) {
		return chr($code);
	}

	public static function indexOf ($haystack, $needle, $offset = 0) {
		if(String::$multibyte) {
			return mb_strpos($haystack, $needle, $offset, String::$multibyte_encoding);
		}
		return strpos($haystack, $needle, $offset);
	}

	public static function lastIndexOf ($haystack, $needle, $offset = 0) {
		if(String::$multibyte) {
			return mb_strrpos($haystack, $needle, $offset, String::$multibyte_encoding);
		}
		return strrpos($haystack, $needle, $offset);
	}

	public static function match ($haystack, $regex) {
		preg_match_all($regex, $haystack, $matches, PREG_PATTERN_ORDER);
		return new Arr($matches[0]);
	}

	public static function replace ($haystack, $needle, $replace, $regex = false) {
		if($regex) {
			$r = preg_replace($needle, $replace, $haystack);
		}
		else {
			if(String::$multibyte) {
				$r = mb_str_replace($needle, $replace, $haystack);
			}
			else {
				$r = str_replace($needle, $replace, $haystack);
			}
		}
		return new String($r);
	}

	public static function strlen ($string) {
		if(String::$multibyte) {
			return mb_strlen($string, String::$multibyte_encoding);
		}
		return strlen($string);
	}

	public static function slice ($string, $start, $end = null) {
		return self::substring($string, $start, $end);
	}

	public static function toLowerCase ($string) {
		if(String::$multibyte) {
			return new String(mb_strtolower($string, String::$multibyte_encoding));
		}
		return new String(strtolower($string));
	}

	public static function toUpperCase ($string) {
		if(String::$multibyte) {
			return new String(mb_strtoupper($string, String::$multibyte_encoding));
		}
		return new STring(strtoupper($string));
	}

	public static function split ($string, $at = '') {
		if(empty($at)) {
			if(String::$multibyte) {
				return new Arr(mb_str_split($string));
			}
			return new Arr(str_split($string));
		}
		return new Arr(explode($at, $string));
	}

	/* end static wrapper methods */
}
class String implements ArrayAccess {
	public static $multibyte_encoding = null;
	public static $multibyte = false;

	private $value;
	private static $checked = false;

	/* magic methods */
	public function __construct ($string) {
		if(!self::$checked) {
			self::$multibyte = extension_loaded('mbstring');
		}
		if(is_null(self::$multibyte_encoding)) {
			if(self::$multibyte) {
				self::$multibyte_encoding = mb_internal_encoding();
			}
		}
		$this->value = (string)$string;
	}
	
	public function __toString () {
		return $this->value;
	}

	/* end magic methods */
	
	/* ArrayAccess Methods */
	
	/** offsetExists ( mixed $index )
		*
		* Similar to array_key_exists
		*/
	public function offsetExists ($index) {
		return !empty($this->value[$index]);
	}
	
	/* offsetGet ( mixed $index ) 
	 *
	 * Retrieves an array value
	 */
	public function offsetGet ($index) {
		return StaticString::substr($this->value, $index, 1)->toString();
	}
	
	/* offsetSet ( mixed $index, mixed $val ) 
	 *
	 * Sets an array value
	 */
	public function offsetSet ($index, $val) {
		$this->value = StaticString::substring($this->value, 0, $index) . $val . StaticString::substring($this->value, $index+1, StaticString::strlen($this->value));
	}
	
	/* offsetUnset ( mixed $index ) 
	 *
	 * Removes an array value
	 */
	public function offsetUnset ($index) {
		$this->value = StaticString::substr($this->value, 0, $index) . StaticString::substr($this->value, $index+1);
	}

	public static function create ($obj) {
		if($obj instanceof String) return new String($obj);
		return new String($obj);
	}
	
	/* public methods */
	public function substr ($start, $length) {
		return StaticString::substr($this->value, $start, $length);
	}

	public function substring ($start, $end) {
		return StaticString::substring($this->value, $start, $end);
	}

	public function charAt ($point) {
		return StaticString::substr($this->value, $point, 1);
	}

	public function charCodeAt ($point) {
		return ord(StaticString::substr($this->value, $point, 1));
	}

	public function indexOf ($needle, $offset) {
		return StaticString::indexOf($this->value, $needle, $offset);
	}

	public function lastIndexOf ($needle) {
		return StaticString::lastIndexOf($this->value, $needle);
	}

	public function match ($regex) {
		return StaticString::match($this->value, $regex);
	}

	public function replace ($search, $replace, $regex = false) {
		return StaticString::replace($this->value, $search, $replace, $regex);
	}

	public function first () {
		return StaticString::substr($this->value, 0, 1);
	}

	public function last () {
		return StaticString::substr($this->value, -1, 1);
	}

	public function search ($search, $offset = null) {
		return $this->indexOf($search, $offset);
	}

	public function slice ($start, $end = null) {
		return StaticString::slice($this->value, $start, $end);
	}

	public function toLowerCase () {
		return StaticString::toLowerCase($this->value);
	}

	public function toUpperCase () {
		return StaticString::toUpperCase($this->value);
	}

	public function toUpper () {
		return $this->toUpperCase();
	}

	public function toLower () {
		return $this->toLowerCase();
	}

	public function split ($at = '') {
		return StaticString::split($this->value, $at);
	}

	public function trim ($charlist = null) {
		return new String(trim($this->value, $charlist));
	}

	public function ltrim ($charlist = null) {
		return new String(ltrim($this->value, $charlist));
	}

	public function rtrim ($charlist = null) {
		return new String(rtrim($this->value, $charlist));
	}

	public function toString () {
		return $this->__toString();
	}
}
class Arr extends ArrayObject {
	private static $ret_obj = true;

	public function add () {
		$val = 0;
		foreach($this as $vals) {
			$val += $vals;
		}
		return $val;
	}

	public function get ($i) {
		$val = $this->offsetGet($i);
		if(is_array($val)) {
			return new self($val);
		}
		if(is_string($val) && self::$ret_obj) {
			return new String($val);
		}
		return $val;
	}

	public function each ($callback) {
		foreach($this as $key => $val) {
			call_user_func_array($callback, array(
				$val, $key, $this
			));
		}
		return $this;
	}

	public function set ($i, $v) {
		$this->offsetSet($i, $v);
		return $this;
	}

	public function push ($value) {
		$this[] = $value;
		return $this;
	}

	public function join ($paste = '') {
		return implode($paste, $this->getArrayCopy());
	}

	public function sort () {
		$this->asort();
		return $this;
	}

	public function toArray () {
		return $this->getArrayCopy();
	}

	public function natsort () {
		parent::natsort();
		return $this;
	}

	public function rsort () {
		parent::uasort('Arr::sort_alg');
		return $this;
	}

	public static function sort_alg ($a,$b) {
	    if ($a == $b) {
			return 0;
		}
		return ($a < $b) ? 1 : -1;
	}
}

