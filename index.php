<?php
require_once dirname(__FILE__) . "/lib.inc/config.php";
require_once dirname(__FILE__) . "/lib.inc/vendor/autoload.php";

use \Firebase\JWT\JWT;

class UserAgent {
	const SERVER_HTTP_USER_AGENT = 'HTTP_USER_AGENT';

	public static function get_user_browser()
	{
		$fullUserBrowser = (!empty($_SERVER[self::SERVER_HTTP_USER_AGENT]) ?
			$_SERVER[self::SERVER_HTTP_USER_AGENT] : getenv(self::SERVER_HTTP_USER_AGENT));
		$userBrowser = explode(')', $fullUserBrowser);
		$userBrowser = $userBrowser[count($userBrowser) - 1];

		if (self::isInternetExplorer($userBrowser, $fullUserBrowser)) {
			return 'Internet-Explorer';
		} else if (self::isEdge($userBrowser, $fullUserBrowser)) {
			return 'Microsoft-Edge';
		} else if (self::isGoogleChrome($userBrowser)) {
			return 'Google-Chrome';
		} else if (self::isFirefox($userBrowser)) {
			return 'Mozilla-Firefox';
		} else if (self::isSafari($userBrowser, $fullUserBrowser)) {
			return 'Safari';
		} else if (self::isOperaMini($userBrowser, $fullUserBrowser)) {
			return 'Opera-Mini';
		} else if (self::isOpera($userBrowser)) {
			return 'Opera';
		}
		return false;
	}

	public static function isInternetExplorer($userBrowser, $fullUserBrowser)
	{
		return (empty($userBrowser) || $userBrowser === ' ' || strpos($userBrowser, 'like Gecko') === 1) && strpos($fullUserBrowser, 'Windows') !== false;
	}
	public static function isEdge($userBrowser, $fullUserBrowser)
	{
		return (strpos($userBrowser, 'Edge/') !== false || strpos($userBrowser, 'Edg/') !== false) && strpos($fullUserBrowser, 'Windows') !== false;
	}
	public static function isGoogleChrome($userBrowser)
	{
		return strpos($userBrowser, 'Chrome/') === 1 || strpos($userBrowser, 'CriOS/') === 1;
	}

	public static function isFirefox($userBrowser)
	{
		return strpos($userBrowser, 'Firefox/') !== false || strpos($userBrowser, 'FxiOS/') !== false;
	}

	public static function isSafari($userBrowser, $fullUserBrowser)
	{
		return strpos($userBrowser, 'Safari/') !== false && strpos($fullUserBrowser, 'Mac') !== false;
	}

	public static function isOperaMini($userBrowser, $fullUserBrowser)
	{
		return strpos($userBrowser, 'OPR/') !== false && strpos($fullUserBrowser, 'Opera Mini') !== false;
	}
	public static function isOpera($userBrowser)
	{
		return strpos($userBrowser, 'OPR/') !== false;
	}
}

class UniversalSimulator
{
	const EVAL_PHP_END = '{[EVAL_PHP_END]}';
	const EVAL_PHP_BEGIN = '{[EVAL_PHP_BEGIN]}';
	const PREFIX_INPUT = '$INPUT.';
	const PREFIX_REQUEST = '$REQUEST.';
	const PHP_INPUT = 'php://input';
	const HEADER_CONTENT_LENGTH = 'Content-length: ';
	const HEADER_CONTENT_TYPE = 'Content-type: ';
	const SUBFIX_CONTENT_TYPE_URL_ENCODE = '/x-www-form-urlencoded';
	const SUBFIX_CONTENT_TYPE_SUBFIX_JSON = '/json';

	
	public static function fix_document_root($document_root)
	{
		if ($document_root == null) {
			$document_root = dirname(__FILE__);
		}
		return $document_root;
	}

	public static function fix_carriage_return($data)
	{
		$data = str_replace("\n", "\r\n", $data);
		$data = str_replace("\r\r\n", "\r\n", $data);
		$data = str_replace("\r", "\r\n", $data);
		$data = str_replace("\r\n\n", "\r\n", $data);
		return $data;
	}

	public static function get_array_line($lines)
	{
		$i = 0;
		$nl = false;
		$j = 0;
		// If line ended with \, do not explode it as array
		foreach ($lines as $line) {
			if (\StringProcessor::endsWith($line, "\\")) {
				$nl = true;
			} else {
				$nl = false;
			}
			if (!isset($array[$i])) {
				$array[$i] = "";
				$j = 0;
			}
			if ($nl) {
				$line = substr($line, 0, strlen($line) - 1) . "\\";
			}
			$array[$i] .= $line;
			if ($j > 0) {
				$array[$i] .= EOL;
			}
			if (!$nl) {
				$i++;
			}
			$j++;
		}
		return $array;
	}

		
	public static function fix_array_key($request_data)
	{
		$fixed_data = array();
		foreach ($request_data as $key => $val) {
			$key2 = trim(preg_replace("/[^A-Za-z0-9_]/", '_', $key)); //NOSONAR
			$fixed_data[$key2] = $val;
		}
		return $fixed_data;
	}

	public static function fix_header_key($request_headers)
	{
		$headers = array();
		foreach ($request_headers as $key => $val) {
			$key2 = trim(strtoupper(str_replace("-", "_", $key)));
			$headers[$key2] = $val;
		}
		return $headers;
	}

		
	public static function get_request_headers()
	{
		return getallheaders();
	}

	

	public static function get_context_path()
	{
		$url = $_SERVER['REQUEST_URI'];
		return parse_url($url, PHP_URL_PATH);
	}

	public static function get_url()
	{
		return $_SERVER['REQUEST_URI'];
	}

	public static function send_response_header($headers)
	{
		$arr = explode("\r\n", $headers);
		foreach ($arr as $header) {
			$header = trim($header, " \t\r\n");
			if (stripos($header, ":") > 0) {
				header($header);
			}
		}
	}

	public static function drop_content_length($headers)
	{
		$arr = explode("\r\n", $headers);
		$result = array();
		foreach ($arr as $header) {
			if (stripos(trim($header), 'content-length:') !== 0) {
				$result[] = $header;
			}
		}
		return implode("\r\n", $result);
	}

	public static function parse_response_header($raw_headers)
	{
		$hdr = array();
		if (isset($raw_headers) && !empty($raw_headers)) {
			$arr = explode("\r\n", $raw_headers);
			foreach ($arr as $header) {
				$header = trim($header, " \t\r\n");
				if (stripos($header, ":") > 0) {
					$row = explode(":", $header, 2);
					$key = trim(strtoupper(str_replace("-", "_", $row[0])));
					$hdr[] = array($key => trim($row[1]));
				}
			}
		}
		return $hdr;
	}

	public static function contains_key($headers, $key)
	{
		if (isset($header) && is_array($headers)) {
			foreach ($headers as $val) {
				if (isset($val[$key])) {
					return true;
				}
			}
		} else {
			return false;
		}
	}

	public static function has_eval($lines)
	{
		$begin = false;
		$end = false;
		foreach ($lines as $line) {
			if (trim($line) == self::EVAL_PHP_BEGIN) {
				$begin = true;
				break;
			}
		}
		foreach ($lines as $line) {
			if (trim($line) == self::EVAL_PHP_END) {
				$end = true;
				break;
			}
		}
		return $begin && $end;
	}


		
	public static function is_valid_jwt($token_sent)
	{
		global $appConfig;
		$secret_key = $appConfig->jwtSecret;
		try {
			$decoded = JWT::decode($token_sent, $secret_key, array('HS256')); //NOSONAR
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	public static function is_valid_quoted($fm1)
	{
		return (\StringProcessor::startsWith($fm1, "'") && \StringProcessor::endsWith($fm1, "'")) 
		|| (\StringProcessor::startsWith($fm1, '"') && \StringProcessor::endsWith($fm1, '"'));
	}

	public static function validate_token($string, $token_sent)
	{
		if (stripos($string, '$ISVALIDTOKEN') !== false) {
			do {
				$total_length = strlen($string);
				$start = stripos($string, '$string');
				$p1 = \StringProcessor::find_bracket_position($string, $start);
				$formula = substr($string, $start, $p1);
				$fm1 = trim($formula, SPACE_TRIMMER);
				$fm1 = substr($fm1, 6, strlen($fm1) - 7);
				$fm1 = trim($fm1, SPACE_TRIMMER);
				if (self::is_valid_quoted($fm1)) {
					$fm1 = substr($fm1, 1, strlen($fm1) - 2);
				}
				if (self::is_valid_jwt($token_sent)) {
					$result = "true";
				} else {
					$result = "false";
				}
				$string = str_ireplace($formula, $result, $string);
				if ($start + $p1 >= $total_length) {
					break;
				}
			} while (stripos($string, '$ISVALIDTOKEN') !== false);
		}
		return $string;
	}

	public static function generate_token()
	{
		global $appConfig;
		$id = $appConfig->jwtUserID; //NOSONAR
		$firstname = $appConfig->jwtUserFirstName; //NOSONAR
		$lastname = $appConfig->jwtUserLastName; //NOSONAR
		$email = $appConfig->jwtUserEmail; //NOSONAR

		$lifetime = $appConfig->jwtNotValidAfter;

		$secret_key = $appConfig->jwtSecret;
		$issuer_claim = $appConfig->jwtIssuer; // this can be the servername
		$audience_claim = $appConfig->jwtAudience;
		$issuedat_claim = time(); // issued at
		$notbefore_claim = $issuedat_claim + $appConfig->jwtNotValidBefore; //not before in seconds
		$expire_claim = $issuedat_claim + $lifetime; // expire time in seconds
		$token = array(
			"iss" => $issuer_claim,
			"aud" => $audience_claim,
			"iat" => $issuedat_claim,
			"nbf" => $notbefore_claim,
			"exp" => $expire_claim,
			"data" => array()
		);

		$jwt = JWT::encode($token, $secret_key);
		return array(
			"JWT" => $jwt,
			"EXPIRE_AT" => $expire_claim,
			"EXPIRE_IN" => $lifetime
		);
	}


}

class StringProcessor{

	const SYSTEM_UUID = '$SYSTEM.UUID';
	const FUNCTION_DATE = '$DATE';
	const FUNCTION_CALC = '$CALC';
	const FUNCTION_NUMBERFORMAT = '$NUMBERFORMAT';
	const FUNCTION_UPPERCASE = '$UPPERCASE';
	const FUNCTION_LOWERCASE = '$LOWERCASE';
	const FUNCTION_SUBSTRING = '$SUBSTRING';
	const FUNCTION_RANDOM = '$RANDOM';


	public static function startsWith($haystack, $needle)
	{
		$length = strlen($needle);
		return substr($haystack, 0, $length) === $needle;
	}

	public static function endsWith($haystack, $needle)
	{
		$length = strlen($needle);
		if (!$length) {
			return true;
		}
		return substr($haystack, -$length) === $needle;
	}
	public static function eval_date($args)
	{
		$args = trim($args, SPACE_TRIMMER);
		if (self::startsWith($args, 'date(')) {
			$args = substr($args, 5, strlen($args) - 6);
		}
		$result = parse_params($args);
		if (count($result) == 1) {
			return self::date_without_tz($result[0]);
		} else if (count($result) == 2) {
			return self::date_with_tz($result[0], $result[1]);
		}
	}

	public static function replace_date($string)
	{
		if (stripos($string, self::FUNCTION_DATE) !== false) {
			do {
				$total_length = strlen($string);
				$start = stripos($string, self::FUNCTION_DATE);
				$p1 = self::find_bracket_position($string, $start);
				$formula = substr($string, $start, $p1);
				$fm1 = trim($formula, SPACE_TRIMMER);
				$fm1 = substr($fm1, 6, strlen($fm1) - 7);
				$fm1 = trim($fm1, SPACE_TRIMMER);
				$result = self::eval_date('date(' . $fm1 . ')');

				$string = str_ireplace($formula, $result, $string);
				if ($start + $p1 >= $total_length) {
					break;
				}
			} while (stripos($string, self::FUNCTION_DATE) !== false);
		}
		return $string;
	}

	public static function replace_number_format($string)
	{
		if (stripos($string, self::FUNCTION_NUMBERFORMAT) !== false) {
			do {
				$total_length = strlen($string);
				$start = stripos($string, self::FUNCTION_NUMBERFORMAT);
				$p1 = self::find_bracket_position($string, $start);
				$formula = substr($string, $start, $p1);
				$fm1 = trim($formula, SPACE_TRIMMER);
				$fm1 = substr($fm1, 13, strlen($fm1) - 7);
				$fm1 = trim($fm1, SPACE_TRIMMER);
				$fm1 = ltrim($fm1, '(');
				$fm1 = rtrim($fm1, ')');
				$result = "";
				eval('$result = number_format(' . $fm1 . ');');
				$string = str_ireplace($formula, $result, $string);
				if ($start + $p1 >= $total_length) {
					break;
				}
			} while (stripos($string, self::FUNCTION_NUMBERFORMAT) !== false);
		}
		return $string;
	}

	public static function replace_substring($string)
	{
		if (stripos($string, self::FUNCTION_SUBSTRING) !== false) {
			do {
				$total_length = strlen($string);
				$start = stripos($string, self::FUNCTION_SUBSTRING);
				$p1 = self::find_bracket_position($string, $start);
				$formula = substr($string, $start, $p1);
				$fm1 = trim($formula, SPACE_TRIMMER);
				$fm1 = substr($fm1, 10, strlen($fm1) - 7);
				$fm1 = trim($fm1, SPACE_TRIMMER);
				$fm1 = ltrim($fm1, '(');
				$fm1 = rtrim($fm1, ')');
				$result = "";
				eval('$result = substr(' . $fm1 . ');');
				$string = str_ireplace($formula, $result, $string);
				if ($start + $p1 >= $total_length) {
					break;
				}
			} while (stripos($string, self::FUNCTION_SUBSTRING) !== false);
		}
		return $string;
	}

	public static function replace_uppercase($string)
	{
		if (stripos($string, self::FUNCTION_UPPERCASE) !== false) {
			do {
				$total_length = strlen($string);
				$start = stripos($string, self::FUNCTION_UPPERCASE);
				$p1 = self::find_bracket_position($string, $start);
				$formula = substr($string, $start, $p1);
				$fm1 = trim($formula, SPACE_TRIMMER);
				$fm1 = substr($fm1, 10, strlen($fm1) - 7);
				$fm1 = trim($fm1, SPACE_TRIMMER);
				$fm1 = ltrim($fm1, '(');
				$fm1 = rtrim($fm1, ')');
				$result = "";
				eval('$result = strtoupper(' . $fm1 . ');');
				$string = str_ireplace($formula, $result, $string);
				if ($start + $p1 >= $total_length) {
					break;
				}
			} while (stripos($string, self::FUNCTION_UPPERCASE) !== false);
		}
		return $string;
	}

	public static function replace_lowercase($string)
	{
		if (stripos($string, self::FUNCTION_LOWERCASE) !== false) {
			do {
				$total_length = strlen($string);
				$start = stripos($string, self::FUNCTION_LOWERCASE);
				$p1 = self::find_bracket_position($string, $start);
				$formula = substr($string, $start, $p1);
				$fm1 = trim($formula, SPACE_TRIMMER);
				$fm1 = substr($fm1, 10, strlen($fm1) - 7);
				$fm1 = trim($fm1, SPACE_TRIMMER);
				$fm1 = ltrim($fm1, '(');
				$fm1 = rtrim($fm1, ')');
				$result = "";
				eval('$result = strtolower(' . $fm1 . ');');
				$string = str_ireplace($formula, $result, $string);
				if ($start + $p1 >= $total_length) {
					break;
				}
			} while (stripos($string, self::FUNCTION_LOWERCASE) !== false);
		}
		return $string;
	}

	public static function find_bracket_position($string, $start)
	{
		$p1 = 0;
		$rem = 0;
		$found = false;
		do {
			$f1 = substr($string, $start + $p1, 1);
			$f2 = substr($string, $start + $p1, 1);
			if ($f1 == "(") {
				$rem++;
				$found = true;
			}
			if ($f2 == ")") {
				$rem--;
				$found = true;
			}
			$p1++;
		} while ($rem > 0 || !$found);
		return $p1;
	}

	public static function replace_calc($string)
	{
		if (stripos($string, self::FUNCTION_CALC) !== false) {
			do {
				$total_length = strlen($string);
				$start = stripos($string, self::FUNCTION_CALC);
				$p1 = self::find_bracket_position($string, $start);

				$formula = substr($string, $start, $p1);
				$fm1 = trim($formula, SPACE_TRIMMER);
				$fm1 = substr($fm1, 6, strlen($fm1) - 7);
				$fm1 = trim($fm1, SPACE_TRIMMER);
				if ((self::startsWith($fm1, "'") && self::endsWith($fm1, "'")) || (self::startsWith($fm1, '"') && self::endsWith($fm1, '"'))) {
					$fm1 = substr($fm1, 1, strlen($fm1) - 2);
				}
				$result = eval("return $fm1;");
				$string = str_ireplace($formula, $result, $string);
				if ($start + $p1 >= $total_length) {
					break;
				}
			} while (stripos($string, self::FUNCTION_CALC) !== false);
		}
		return $string;
	}

	public static function replace_uuid($string)
	{
		if (stripos($string, self::SYSTEM_UUID) !== false) {
			do {
				$formula = self::SYSTEM_UUID;
				$result = uniqid();
				$string = self::str_replace_first($formula, $result, $string);
			} while (stripos($string, self::SYSTEM_UUID) !== false);
		}
		return $string;
	}

	public static function replace_random($string)
	{
		if (stripos($string, self::FUNCTION_RANDOM) !== false) {
			do {
				$total_length = strlen($string);
				$start = stripos($string, self::FUNCTION_RANDOM);
				$p1 = self::find_bracket_position($string, $start);
				$formula = substr($string, $start, $p1);
				$fm1 = trim($formula, SPACE_TRIMMER);
				$fm1 = substr($fm1, 8, strlen($fm1) - 9);
				$fm1 = trim($fm1, SPACE_TRIMMER);
				if (stripos($fm1, ",") !== false) {
					$arr = explode(",", $fm1);
					$arr[0] = preg_replace("/[^0-9]/", "", $arr[0]);//NOSONAR
					$arr[1] = preg_replace("/[^0-9]/", "", $arr[1]);//NOSONAR
					$min = $arr[0] * 1;
					$max = $arr[1] * 1;
					if ($min < $max) {
						$result = mt_rand($min, $max);
					} else {
						$result = mt_rand($max, $min);
					}
				} else {
					$result = mt_rand();
				}

				$string = self::str_replace_first($formula, $result, $string);
				if ($start + $p1 >= $total_length) {
					break;
				}
			} while (stripos($string, self::FUNCTION_RANDOM) !== false);
		}
		return $string;
	}

	public static function str_replace_first($search, $replace, $subject)
	{
		$search = '/' . preg_quote($search, '/') . '/';
		return preg_replace($search, $replace, $subject, 1);
	}

	public static function get_query($url)
	{
		return parse_url($url, PHP_URL_QUERY);
	}

		
	public static function date_without_tz($args1)
	{
		return date($args1);
	}

	public static function date_with_tz($fmt, $tz)
	{
		$offset = 0;
		if (stripos($tz, 'UTC') !== false) {
			$tz1 = (str_replace("UTC", "", $tz) * 1);
			$tz2 = date('Z') / 3600;
			$offset = $tz1 - $tz2;
			$time = time();
			$tzTime = $time + ($offset * 3600);
			$ret = date($fmt, $tzTime);
		} else if (stripos($tz, 'GMT') !== false) {
			$tz1 = (str_replace("GMT", "", $tz) * 1);
			$tz2 = date('Z') / 3600;
			$offset = $tz1 - $tz2;
			$time = time();
			$tzTime = $time + ($offset * 3600);
			$ret = date($fmt, $tzTime);
		} else {
			date_default_timezone_set($tz);
			$ret = date($fmt);
		}
		return $ret;
	}

}

error_reporting(0);

// Functions




function get_config($document_root, $context_path)
{
	$config = "";
	if ($document_root !== null) {
		$document_root = \UniversalSimulator::fix_document_root($document_root);
		$config = $document_root . "/" . $context_path;
	} else {
		$config = $context_path;
	}
	return $config;
}



function parse_config($context_path, $document_root = null)
{
	$config = get_config($document_root, $context_path);

	$file_content = file_get_contents($config);

	// Fixing new line
	// Some operating system may have different style
	$file_content = \UniversalSimulator::fix_carriage_return($file_content);

	$lines = explode("\r\n", $file_content);
	$array = array();

	$array = \UniversalSimulator::get_array_line($lines);

	// Parse raw file to raw configuration with it properties
	$parsed = array();
	if (\UniversalSimulator::has_eval($lines)) {
		$parsed = parse_lines_native($array, $file_content);
	} else {
		$parsed = parse_lines($array);
	}
	return $parsed;
}

function parse_lines_native($array, $file_content)
{
	$parsed = array();
	foreach ($array as $content) {
		if (stripos($content, "=") > 0) {
			$arr = explode("=", trim($content), 2);
			$key = trim($arr[0]);
			if ($key == 'METHOD' || $key == 'PATH') {
				$parsed[$key] = trim($arr[1]);
			}
		}
	}
	$code2eval = get_php_code($file_content);
	$parsed['PHP_CODE'] = $code2eval;
	return $parsed;
}
function parse_lines($array)
{
	$parsed = array();
	foreach ($array as $content) {
		if (stripos($content, "=") > 0) {
			$arr = explode("=", trim($content), 2);
			$parsed[trim($arr[0])] = trim($arr[1]);
		}
	}
	return $parsed;
}
function get_php_code($file_content)
{
	$php_codes = array();
	$file_content = "\r\n" . $file_content . "\r\n";
	$arr1 = explode(\UniversalSimulator::EVAL_PHP_END, $file_content);

	foreach ($arr1 as $code1) {
		if (stripos($code1, \UniversalSimulator::EVAL_PHP_BEGIN) !== false) {
			$arr2 = explode(\UniversalSimulator::EVAL_PHP_BEGIN, $code1, 2);
			$php_codes[] = trim($arr2[1], "\r\n\t");
		}
	}
	return $php_codes;
}



function parse_input($config, $request_headers, $request_data, $context_path, $query) //NOSONAR
{
	$headers = \UniversalSimulator::fix_header_key($request_headers);
	parse_str($query, $get_data);
	// Parsing input
	$rule = $config['PARSING_RULE'];
	$rule = str_replace("\\", "\\" . EOL, $rule);
	$rule = trim(str_replace("\\" . EOL, "\r\n", $rule), " \\ ");
	if (\StringProcessor::endsWith($rule, EOL)) {
		$rule = substr($rule, 0, strlen($rule) - strlen(EOL));
	}
	$arr = explode("\r\n", $rule);
	$res = array();

	$url_data = array();
	// Parse URL
	if (stripos($config['PATH'], '{[') !== false && stripos($config['PATH'], ']}') !== false) {
		$start = stripos($config['PATH'], '{[');
		$end = stripos($config['PATH'], ']}');

		if ($start === false || $end === false) {
			$base_path = $config['PATH']; //NOSONAR
		} else {
			$base_path = substr($config['PATH'], 0, $start); //NOSONAR
			$wildcard_data = trim(substr($context_path, $start), "/");
			$wildcard_variable = trim(substr($config['PATH'], $start), "/");
			$arr1 = explode("/", $wildcard_variable);
			$arr2 = explode("/", $wildcard_data);
			foreach ($arr1 as $key => $val) {
				if (\StringProcessor::startsWith($val, '{[') && \StringProcessor::endsWith($val, ']}')) {
					$par = trim(str_replace(array('{[', ']}'), '', $val));
					$url_data[$par] = $arr2[$key];
				}
			}
		}
	}
	foreach ($arr as $line) {
		if (\StringProcessor::startsWith($line, EOL)) {
			$line = substr($line, strlen(EOL));
		}
		if (\StringProcessor::endsWith($line, EOL)) {
			$line = substr($line, 0, strlen($line) - strlen(EOL));
		}
		if (stripos($line, "=") > 0) {
			$arr2 = explode("=", $line, 2);
			// Parse from headers
			if (stripos(trim($arr2[0]), \UniversalSimulator::PREFIX_INPUT) === 0 && stripos(trim($arr2[1]), '$HEADER.') === 0) {
				$key = trim(substr(trim($arr2[0]), strlen(\UniversalSimulator::PREFIX_INPUT)));
				$key2 = trim(substr(trim($arr2[1]), strlen('$HEADER.')));
				$value = trim($headers[$key2]);
				$res[$key] = isset($value) ? $value : '';
			}
			if (stripos(trim($arr2[0]), \UniversalSimulator::PREFIX_INPUT) === 0 && stripos(trim($arr2[1]), '$URL.') === 0) {
				$key = trim(substr(trim($arr2[0]), strlen(\UniversalSimulator::PREFIX_INPUT)));
				$key2 = trim(substr(trim($arr2[1]), strlen('$URL.')));
				$value = trim($url_data[$key2]);
				$res[$key] = isset($value) ? $value : '';
			}

			// Get Random
			if (stripos(trim($arr2[0]), \UniversalSimulator::PREFIX_INPUT) === 0 && stripos(trim($arr2[1]), '$SYSTEM.RANDOM') === 0) {
				$key = trim(substr(trim($arr2[0]), strlen(\UniversalSimulator::PREFIX_INPUT)));
				$val = trim($arr2[1]);
				$val =  trim(preg_replace("/[^0-9,]/", "", $val));
				if (empty($val)) {
					$res[$key] = mt_rand();
				} else {
					if (stripos($val, ",") !== false) {
						$arr = explode(",", $val);
						$min = $arr[0] * 1;
						$max = $arr[1] * 1;
						if ($min < $max) {
							$randomVal = mt_rand($min, $max);
						} else {
							$randomVal = mt_rand($max, $min);
						}
						$res[$key] = $randomVal;
					} else {
						$max = abs($val * 1);
						$res[$key] = mt_rand(0, $max);
					}
				}
			}
			if (stripos(trim($arr2[0]), \UniversalSimulator::PREFIX_INPUT) === 0 && stripos(trim($arr2[1]), '$JSON.REQUEST') === 0) {
				$key = trim(substr(trim($arr2[0]), strlen(\UniversalSimulator::PREFIX_INPUT)));
				$val = trim($arr2[1]);
				$val = substr($val, strlen('$JSON.REQUEST'));
				if (\StringProcessor::startsWith($val, '(') && \StringProcessor::endsWith($val, ')')) {
					$val = substr($val, 1, strlen($val) - 2);
				}
				$input = file_get_contents(\UniversalSimulator::PHP_INPUT);
				if (empty($val)) {
					try {
						$obj = json_decode($input, true);
						$res[$key] = $obj;
					} catch (Exception $e) {
						$res[$key] = 'null';
					}
				} else {
					$params = parse_params($val);
					if (count($params) == 1) {
						$params[1] = 'false';
					}
					try {
						if (empty($params[0])) {
							$obj = json_decode($input, true);
							$value = $obj;
							if (strtolower($params[1]) == 'true' || $params[1] == '1') {
								$res[$key] = json_encode(json_encode(isset($value) ? $value : 'null'));
							} else {
								$res[$key] = json_encode(isset($value) ? $value : 'null');
							}
						} else if (stripos($params[0], '[') !== false && stripos($params[0], ']') !== false) {
							// parse as associated array
							$obj = json_decode($input, true);
							$attr = $params[0];
							if (!\StringProcessor::startsWith($attr, '[')) {
								$arr1 = explode("[", $attr, 2);
								$attr = '[' . $arr1[0] . ']' . $arr1[1];
							}
							$value = eval('return @$obj' . $attr . ';');

							if (strtolower($params[1]) == 'true' || $params[1] == '1') {
								$res[$key] = json_encode(isset($value) ? $value : 'null');
							} else {
								$res[$key] = isset($value) ? $value : 'null';
							}
						} else {
							$obj = json_decode($input);
							$attr = str_replace(".", "->", $params[0]);
							$value = eval('return @$obj->' . $attr . ';');
							if (strtolower($params[1]) == 'true' || $params[1] == '1') {
								$res[$key] = json_encode(json_encode(isset($value) ? $value : 'null'));
							} else {
								$res[$key] = json_encode(isset($value) ? $value : 'null');
							}
						}
					} catch (Exception $e) {
						$res[$key] = 'null';
					}
				}
			}
			// Get UUID
			if (stripos(trim($arr2[0]), \UniversalSimulator::PREFIX_INPUT) === 0 && stripos(trim($arr2[1]), \StringProcessor::SYSTEM_UUID) === 0) {
				$key = trim(substr(trim($arr2[0]), strlen(\UniversalSimulator::PREFIX_INPUT)));
				$res[$key] = uniqid();
			}
			// Parse from authorization
			if (isset($headers['AUTHORIZATION'])) {
				$authorization = trim($headers['AUTHORIZATION']);
				if (stripos($authorization, 'Basic ') === 0) {
					$auths = base64_decode(substr($authorization, 6));
					if (stripos($auths, ":") !== false) {
						$up = explode(":", $auths, 2);
						if (stripos(trim($arr2[0]), \UniversalSimulator::PREFIX_INPUT) === 0 && stripos(trim($arr2[1]), '$AUTHORIZATION_BASIC.') === 0) {
							$key1 = trim(substr(trim($arr2[0]), strlen(\UniversalSimulator::PREFIX_INPUT)));
							$key2 = trim(substr(trim($arr2[1]), strlen('$AUTHORIZATION_BASIC.')));
							if ($key2 == 'USERNAME') {
								$value = trim($up[0]);
								$res[$key1] = isset($value) ? $value : '';
							}
							if ($key2 == 'PASSWORD') {
								$value = trim($up[1]);
								$res[$key1] = isset($value) ? $value : '';
							}
						}
					}
				}
			}
			// Parse from GET (also applied on POST and PUT)
			if (stripos(trim($arr2[0]), \UniversalSimulator::PREFIX_INPUT) === 0 && stripos(trim($arr2[1]), '$GET.') === 0) {
				$key = trim(substr(trim($arr2[0]), strlen(\UniversalSimulator::PREFIX_INPUT)));
				$value = trim(substr(trim($arr2[1]), strlen('$GET.')));
				$res[$key] = isset($get_data[$value]) ? $get_data[$value] : '';
			}
			if (stripos(trim($arr2[0]), \UniversalSimulator::PREFIX_INPUT) === 0 && stripos(trim($arr2[1]), '$GET[') === 0) {
				$key = trim(substr(trim($arr2[0]), strlen(\UniversalSimulator::PREFIX_INPUT)));
				$value = trim(substr(trim($arr2[1]), strlen('$GET')));
				$val = eval('return isset($get_data' . $value . ')?($get_data' . $value . '):\'\';'); //NOSONAR
				$res[$key] = $val;
			}
			// Parse from POST
			if (stripos(trim($arr2[0]), \UniversalSimulator::PREFIX_INPUT) === 0 && stripos(trim($arr2[1]), '$POST.') === 0 && $config['METHOD'] == 'POST') {
				$key = trim(substr(trim($arr2[0]), strlen(\UniversalSimulator::PREFIX_INPUT)));
				$value = trim(substr(trim($arr2[1]), strlen('$POST.')));
				$res[$key] = isset($request_data[$value]) ? $request_data[$value] : '';
			}
			if (stripos(trim($arr2[0]), \UniversalSimulator::PREFIX_INPUT) === 0 && stripos(trim($arr2[1]), '$POST[') === 0) {
				$key = trim(substr(trim($arr2[0]), strlen(\UniversalSimulator::PREFIX_INPUT)));
				$value = trim(substr(trim($arr2[1]), strlen('$POST')));
				$val = eval('return isset($get_data' . $value . ')?($get_data' . $value . '):\'\';');
				$res[$key] = $val;
			}
			// Parse from PUT
			if (stripos(trim($arr2[0]), \UniversalSimulator::PREFIX_INPUT) === 0 && stripos(trim($arr2[1]), '$PUT.') === 0 && $config['METHOD'] == 'PUT') {
				$key = trim(substr(trim($arr2[0]), strlen(\UniversalSimulator::PREFIX_INPUT)));
				$value = trim(substr(trim($arr2[1]), strlen('$PUT.')));
				$res[$key] = isset($request_data[$value]) ? $request_data[$value] : '';
			}
			if (stripos(trim($arr2[0]), \UniversalSimulator::PREFIX_INPUT) === 0 && stripos(trim($arr2[1]), '$PUT[') === 0) {
				$key = trim(substr(trim($arr2[0]), strlen(\UniversalSimulator::PREFIX_INPUT)));
				$value = trim(substr(trim($arr2[1]), strlen('$PUT')));
				$val = eval('return isset($get_data' . $value . ')?($get_data' . $value . '):\'\';');
				$res[$key] = $val;
			}
			// Parse from request
			if (stripos(trim($arr2[0]), \UniversalSimulator::PREFIX_INPUT) === 0 && stripos(trim($arr2[1]), \UniversalSimulator::PREFIX_REQUEST) === 0) {
				if (stripos($config['REQUEST_TYPE'], \UniversalSimulator::SUBFIX_CONTENT_TYPE_URL_ENCODE) !== false) {
					$key = trim(substr(trim($arr2[0]), strlen(\UniversalSimulator::PREFIX_INPUT)));
					$value = trim(substr(trim($arr2[1]), strlen(\UniversalSimulator::PREFIX_REQUEST)));
					$res[$key] = isset($request_data[$value]) ? $request_data[$value] : '';
				} else if (
					stripos($config['REQUEST_TYPE'], \UniversalSimulator::SUBFIX_CONTENT_TYPE_SUBFIX_JSON) !== false
					|| stripos($config['REQUEST_TYPE'], '/xml') !== false
					|| (stripos($config['REQUEST_TYPE'], 'soap') !== false
						&& stripos($config['REQUEST_TYPE'], 'xml') !== false)
				) {
					$obj = json_decode(json_encode($request_data));
					$key = trim(substr(trim($arr2[0]), strlen(\UniversalSimulator::PREFIX_INPUT)));
					$tst = trim(substr(trim($arr2[1]), strlen(\UniversalSimulator::PREFIX_REQUEST)));
					$attr = str_replace(".", "->", $tst);
					$value = eval('return @$obj->' . $attr . ';');
					$res[$key] = isset($value) ? $value : '';
				}
				if (WILDCARD_URL_TO_REQUEST) {
					$tst = trim(substr(trim($arr2[1]), strlen(\UniversalSimulator::PREFIX_REQUEST)));
					if (!empty($tst) && isset($url_data[$tst])) {
						$key = trim(substr(trim($arr2[0]), strlen(\UniversalSimulator::PREFIX_INPUT)));
						$res[$key] = $url_data[$tst];
					}
				}
			}
			if (stripos(trim($arr2[0]), \UniversalSimulator::PREFIX_INPUT) === 0 && stripos(trim($arr2[1]), '$REQUEST[') === 0) {
				if (stripos($config['REQUEST_TYPE'], \UniversalSimulator::SUBFIX_CONTENT_TYPE_URL_ENCODE) !== false) {
					$key = trim(substr(trim($arr2[0]), strlen(\UniversalSimulator::PREFIX_INPUT)));
					$value = trim(substr(trim($arr2[1]), strlen('$REQUEST')));
					$val = eval('return isset($request_data' . $value . ')?($request_data' . $value . '):\'\';');
					$res[$key] = $val;
				} else if (stripos($config['REQUEST_TYPE'], \UniversalSimulator::SUBFIX_CONTENT_TYPE_SUBFIX_JSON) !== false || stripos($config['REQUEST_TYPE'], '/xml') !== false) {
					$obj = $request_data;
					$key = trim(substr(trim($arr2[0]), strlen(\UniversalSimulator::PREFIX_INPUT)));
					$tst = trim(substr(trim($arr2[1]), strlen('$REQUEST')));
					$value = eval('return @$obj' . $tst . ';');
					$res[$key] = isset($value) ? $value : '';
				}
			}
		}
	}
	return $res;
}

function process_transaction($parsed, $request) //NOSONAR
{
	$token_sent = get_token();
	$content_type = $parsed['RESPONSE_TYPE']; //NOSONAR
	$transaction_rule = $parsed['TRANSACTION_RULE'];
	$transaction_rule = trim($transaction_rule, "\\");
	$transaction_rule = str_replace("\\{[EOL]}$" . "OUTPUT.", "\\{[EOL]}\r\n$" . "OUTPUT.", $transaction_rule);
	if (\StringProcessor::endsWith($transaction_rule, EOL)) {
		$transaction_rule = substr($transaction_rule, 0, strlen($transaction_rule) - strlen(EOL));
	}
	$arr = explode('{[ENDIF]}', $transaction_rule);
	$return_data = array();
	$token_generated = null;
	foreach ($arr as $data) {
		if (stripos($data, "{[THEN]}") > 0) {
			$arr2 = explode("{[THEN]}", $data);
			$rcondition = $arr2[0];
			$rcondition = str_replace("\\{[EOL]}", "\r\n", $rcondition);
			$rcondition = \UniversalSimulator::validate_token($rcondition, $token_sent);
			$rline = $arr2[1];

			// Evaluate condition
			$str = preg_replace('/[^a-z0-9\.\$_]/i', ' ', $rcondition);
			$str = preg_replace('/\s\s+/', ' ', $str);
			$arr5 = explode(" ", $str);
			foreach ($arr5 as $word) {
				if (stripos($word, \UniversalSimulator::PREFIX_INPUT) === 0) {
					$var = substr($word, strlen(\UniversalSimulator::PREFIX_INPUT));
					$rcondition = str_replace($word, '$request[\'' . $var . '\']', $rcondition);
				}
			}
			$rcondition = trim($rcondition, SPACE_TRIMMER);
			if (stripos($rcondition, '{[IF]}') === 0) {
				$rcondition = str_ireplace('{[IF]}', 'if', $rcondition);
				$rcondition = trim(substr($rcondition, 2));
			}

			$test = eval("return " . $rcondition . ";");
			if ($test) {
				$arr3 = explode("\r\n", $rline);
				foreach ($arr3 as $result) {
					// Parse result
					$str = preg_replace('/[^a-z0-9\.\$_]/i', ' ', $result);
					$str = str_replace(\UniversalSimulator::PREFIX_INPUT, ' $INPUT.', $str);
					$str = preg_replace('/\s\s+/', ' ', $str);
					$arr5 = explode(" ", $str);
					foreach ($arr5 as $word) {
						if (stripos($word, \UniversalSimulator::PREFIX_INPUT) === 0) {
							$var = substr($word, strlen(\UniversalSimulator::PREFIX_INPUT));
							$result = str_replace($word, $request[$var], $result);
						}
						if (stripos($word, '$TOKEN.') === 0) {
							if ($token_generated === null) {
								$token_generated = \UniversalSimulator::generate_token();
							}
							$var = substr($word, strlen('$TOKEN.'));
							$result = str_replace($word, $token_generated[$var], $result);
						}
					}
					$result = trim(str_replace("\\{[EOL]}", "\r\n", $result), " \\\r\n ");
					$result = \StringProcessor::replace_date($result);
					$result = \StringProcessor::replace_number_format($result);
					$result = \StringProcessor::replace_substring($result);
					$result = \StringProcessor::replace_uppercase($result);
					$result = \StringProcessor::replace_lowercase($result);
					$result = \StringProcessor::replace_calc($result);
					$result = \StringProcessor::replace_random($result);
					$result = \StringProcessor::replace_uuid($result);
					$result = ltrim($result, SPACE_TRIMMER);
					$arr6 = explode("=", $result, 2);
					$variable = $arr6[0];
					$value = @$arr6[1];

					if (stripos($variable, '$OUTPUT.') === 0) {
						$variable = substr($variable, 8);
					}
					$variable = trim($variable);
					if (strlen($variable) > 0) {
						$value = str_ireplace('{[|]}', '', $value);
						$return_data[$variable] = $value;
					}
				}
				break;
			}
		}
	}
	return $return_data;
}

function parse_params($args) //NOSONAR
{
	$args = trim($args, SPACE_TRIMMER);
	$parts = preg_split("/(?:'[^']*'|)\K\s*(,\s*|$)/", $args);
	$result = array_filter($parts, function ($value) {
		return ($value !== '');
	});
	if (empty($result) && !empty($args)) {
		$args = trim($args);
		if (stripos($args, ',') === false && \StringProcessor::startsWith($args, "'") && \StringProcessor::endsWith($args, "'")) {
			return array($args);
		}
	}
	if (count($result) == 1) {
		// no time zone	
		if (\StringProcessor::startsWith($result[0], "'")) {
			$result[0] = substr($result[0], 1);
		}
		if (\StringProcessor::endsWith($result[0], "'")) {
			$result[0] = substr($result[0], 0, strlen($result[0]) - 1);
		}
		return array($result[0]);
	} else if (count($result) == 2) {
		if (\StringProcessor::startsWith($result[0], "'")) {
			$result[0] = substr($result[0], 1);
		}
		if (\StringProcessor::endsWith($result[0], "'")) {
			$result[0] = substr($result[0], 0, strlen($result[0]) - 1);
		}
		if (\StringProcessor::startsWith($result[1], "'")) {
			$result[1] = substr($result[1], 1);
		}
		if (\StringProcessor::endsWith($result[1], "'")) {
			$result[1] = substr($result[1], 0, strlen($result[1]) - 1);
		}
		$result[0] = str_replace("'", "", trim($result[0]));
		$result[1] = str_replace("'", "", trim($result[1]));
		return array($result[0], $result[1]);
	}
}


function get_request_body($parsed, $url)
{
	if ($parsed['METHOD'] == 'GET') {
		$query = \StringProcessor::get_query($url);
		error_log("HTTP Request     : \r\n" . $query); //NOSONAR
		parse_str($query, $request_data);
	} else if ($parsed['METHOD'] == 'POST' || $parsed['METHOD'] == 'PUT') {
		if (stripos($parsed['REQUEST_TYPE'], \UniversalSimulator::SUBFIX_CONTENT_TYPE_URL_ENCODE) !== false) {
			$query = file_get_contents(\UniversalSimulator::PHP_INPUT);
			error_log("HTTP Request     : \r\n" . $query);
			parse_str($query, $request_data);
		} else if (stripos($parsed['REQUEST_TYPE'], \UniversalSimulator::SUBFIX_CONTENT_TYPE_SUBFIX_JSON) !== false) {
			$input_buffer = file_get_contents(\UniversalSimulator::PHP_INPUT);
			error_log("HTTP Request     : \r\n" . $input_buffer);
			$request_data = json_decode($input_buffer, true);
			$request_data = \UniversalSimulator::fix_array_key($request_data);
		} else if (stripos($parsed['REQUEST_TYPE'], '/xml') !== false) {
			$input_buffer = file_get_contents(\UniversalSimulator::PHP_INPUT);
			error_log("HTTP Request     : \r\n" . $input_buffer);
			$xml = simplexml_load_string($input_buffer);
			$request_data = json_decode(json_encode($xml), true);
			$request_data = \UniversalSimulator::fix_array_key($request_data);
		} else if (stripos($parsed['REQUEST_TYPE'], 'soap') !== false && stripos($parsed['REQUEST_TYPE'], 'xml') !== false) {
			$input_buffer = file_get_contents(\UniversalSimulator::PHP_INPUT);
			error_log("HTTP Request     : \r\n" . $input_buffer);
			$input_buffer = str_ireplace(array('<soap:', '</soap:'), array('<', '</'), $input_buffer);
			$xml = simplexml_load_string($input_buffer);
			$request_data = json_decode(json_encode($xml), true);
			$request_data = \UniversalSimulator::fix_array_key($request_data);
		}
	}
	return $request_data;
}

function parse_match_url($config_path, $request_path)
{
	// Parsing
	$arr1 = explode(']}', $config_path);
	$arr2 = array();
	$params = array();
	foreach ($arr1 as $key => $val) {
		$pos = stripos($val, '{[', 0);
		if ($pos !== false) {
			$arr1[$key] = substr($val, 0, $pos);
			$params[] = substr($val, $pos + 2);
		}
	}
	$values = array();
	if (count($arr1) > 1) {
		if ($arr1[count($arr1) - 1] != '') {
			$j = 0;
			$cur = 0;
			$start = 0;
			$end = 0;
			$lastpost = 0;
			for ($i = 0; $i < count($arr1) - 1; $i++) {
				$curval = $arr1[$i];
				$nextval = $arr1[$i + 1];
				$start = $cur + strlen($curval);
				$end = stripos($request_path, $nextval, $start);
				$arr2[] = substr($request_path, $lastpost, $start - $lastpost);
				$lastpost = $end;
				$values[$params[$j]] = substr($request_path, $start, $end - $start);
				$cur = $end;
				$j++;
			}
			$arr2[] = substr($request_path, $lastpost);
		} else {
			$arr2[0] = substr($request_path, 0, strlen($arr1[0]));
			$arr2[1] = "";
			$values[$params[0]] = substr($request_path, strlen($arr1[0]));
		}
	}
	$arr1 = remove_slash($arr1);
	$arr2 = remove_slash($arr2);
	return array(
		'config_path' => $config_path,
		'request_path' => $request_path,
		'config_path_list' => $arr1,
		'request_path_list' => $arr2,
		'param_list' => $params,
		'param_values' => $values
	);
}

function remove_slash($arr)
{
	$arr1 = $arr;
	foreach ($arr as $idx => $val) {
		if ($idx > 0 && $val == "/") {
			unset($arr1[$idx]);
		}
	}
	return $arr1;
}

function is_match_path($config_path, $request_path)
{
	$match = false;
	$start = stripos($config_path, '{[');
	if ($start === false) {
		if (\StringProcessor::endsWith($config_path, "/")) {
			$match = stripos($request_path, $config_path) === 0;
		} else {
			$match = stripos(rtrim($config_path, "/") . "/", rtrim($request_path, "/") . "/") === 0;
		}
	} else {
		$match = is_match_path_wildcard($config_path, $request_path);
	}
	return $match;
}

function is_match_path_wildcard($config_path, $request_path)
{
	$match = parse_match_url($config_path, $request_path);
	$arr1 = $match['config_path_list'];
	$arr2 =	$match['request_path_list'];
	$oke = false;
	if (count($arr1) <= count($arr2)) {
		$oke = true;
		foreach ($arr1 as $k => $v) {
			if ($arr2[$k] != $v) {
				$oke = false;
				break;
			}
		}
	}
	return $oke;
}

function get_config_file($dir, $context_path)
{
	$document_root = (USE_RELATIVE_PATH) ? dirname(__FILE__) : null;
	$parsed = null;
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if ('.' === $file || '..' === $file) {
				continue;
			}
			$filepath = rtrim($dir, "/") . "/" . $file;
			$prsd = parse_config($filepath, $document_root);
			if (is_match_path($prsd['PATH'], $context_path) && $prsd['METHOD'] == $_SERVER["REQUEST_METHOD"]) {
				$parsed = $prsd;
				break;
			}
		}
		closedir($handle);
	}
	return $parsed;
}

if (!function_exists('getallheaders')) {
	function getallheaders()
	{
		$headers = array();
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
				$headers[$key] = $value;
			}
		}
		return $headers;
	}
}

function get_token()
{
	$token_sent = "";
	$headers = \UniversalSimulator::get_request_headers();
	if (isset($headers['Authorization'])) {
		$auth = $headers['Authorization'];
		if (stripos($auth, 'Bearer ') === 0) {
			$token_sent = substr($auth, 7);
		}
	}
	return $token_sent;
}

function send_callback($output) //NOSONAR
{
	$res = "";
	$url = @$output['CALLBACK_URL'];
	$timeout = @$output['CALLBACK_TIMEOUT'] * 0.001;
	if (stripos($url, "://") !== false) {
		$callback_http_version = "1.1";
		$callbackDelay = @$output['CALLBACK_DELAY'] * 1000;
		if ($callbackDelay > 0) {
			usleep($callbackDelay);
		}

		$body = @$output['CALLBACK_BODY'];
		$content_length = strlen($body);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //NOSONAR
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //NOSONAR
		if ($timeout > 0) {
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		}
		$headers = array();
		if (isset($output['CALLBACK_HEADER'])) {
			$header = \UniversalSimulator::fix_carriage_return(trim($output['CALLBACK_HEADER'], SPACE_TRIMMER));
			if (strlen($header) > 2) {
				$headers = explode("\r\n", $header);
			}
			if (stripos($header, "User-agent: ") === false) {
				$headers[] = 'User-agent: ' . USER_AGENT;
			}
		}
		if (strtoupper($output['CALLBACK_METHOD']) == 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1);
			if ($content_length > 0) {
				$headers[] = \UniversalSimulator::HEADER_CONTENT_LENGTH . $content_length;
				if (isset($output['CALLBACK_TYPE'])) {
					$headers[] = \UniversalSimulator::HEADER_CONTENT_TYPE . $output['CALLBACK_TYPE'];
				}
				curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
			}
		} else if (strtoupper($output['CALLBACK_METHOD']) == 'PUT') {
			curl_setopt($ch, CURLOPT_PUT, 1);
			if ($content_length > 0) {
				$headers[] = \UniversalSimulator::HEADER_CONTENT_LENGTH . $content_length;
				if (isset($output['CALLBACK_TYPE'])) {
					$headers[] = \UniversalSimulator::HEADER_CONTENT_TYPE . $output['CALLBACK_TYPE'];
				}
				curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
			}
		}
		if (!empty($headers)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		error_log("Send callback to : " . $url);

		$url_info = parse_url($url);
		$path = $url_info['path'];
		$query = @$url_info['query'];
		if (!empty($query)) {
			$path .= "?" . $query;
		}
		$hostFound = false;
		foreach ($headers as $hdr) {
			if (stripos($hdr, 'Host: ') === 0) {
				$hostFound = true;
				break;
			}
		}
		if (!$hostFound) {
			$target_host = $url_info['host'];
			$port = @$url_info['port'] * 1;
			$scheme = $url_info['scheme'];
			if ($port != 0) {
				if ($scheme == "http" && $port != 80) {
					$target_host .= ":" . $port;
				}
				if ($scheme == "https" && $port != 443) {
					$target_host .= ":" . $port;
				}
			}
			$headers[] = "Host: " . $target_host;
		}
		$debug = $output['CALLBACK_METHOD'] . " " . $path . " HTTP/" . $callback_http_version . "\r\n";
		$debug .= implode("\r\n", $headers) . "\r\n";
		if (!empty($body)) {
			$debug .= "\r\n" . $body;
		}

		error_log("HTTP Request     : \r\n" . $debug);

		$res = curl_exec($ch);
		error_log("Callback sent");
		curl_close($ch);
	}
	return $res;
}

function send_response($output, $parsed, $async) //NOSONAR
{
	$response = null;
	if (isset($output['BODY'])) {
		$response = @$output['BODY'];
	}
	if ($async) {
		if (function_exists('ignore_user_abort')) {
			ignore_user_abort(true);
		}
		ob_start();

		if ($response != null) {
			echo $response;
		}
	}

	if (isset($output['STATUS'])) {
		$status = trim($output['STATUS']);
		$arr = explode(' ', $status, 2);
		if (count($arr) > 1) {
			header($_SERVER["SERVER_PROTOCOL"] . ' ' . $status);
		} else {
			http_response_code($output['STATUS']);
		}
	}

	if (isset($output['DELAY'])) {
		$delay = @$output['DELAY'] * 1;
		if ($delay > 0) {
			usleep($delay * 1000);
		}
	}

	$send_content_type = false;
	if (isset($output['HEADER'])) {
		$raw_header = $output['HEADER'];
		$response_header = \UniversalSimulator::parse_response_header($raw_header);
		$send_content_type = \UniversalSimulator::contains_key($response_header, "CONTENT_TYPE");
		// drop coontent length
		$raw_header = \UniversalSimulator::drop_content_length($raw_header);
		\UniversalSimulator::send_response_header($raw_header);
	}

	if (!$send_content_type) {
		if (isset($output['TYPE'])) {
			$content_type = $output['TYPE'];
			header(\UniversalSimulator::HEADER_CONTENT_TYPE . trim($content_type));
		} else if (isset($parsed['RESPONSE_TYPE'])) {
			$content_type = $parsed['RESPONSE_TYPE'];
			header(\UniversalSimulator::HEADER_CONTENT_TYPE . trim($content_type));
		}
	}

	if ($response != null) {
		header(\UniversalSimulator::HEADER_CONTENT_LENGTH . strlen($response));
	}

	header("Connection: close");

	if ($async) {
		ob_end_flush();
		ob_flush();
		flush();
		if (function_exists('fastcgi_finish_request')) {
			fastcgi_finish_request();
		}
	} else {
		echo $response;
	}
}


// End of functions

$config_dir = CONFIG_DIR;

// Get context path from URL
$context_path = \UniversalSimulator::get_context_path();

// Get URL
$url = \UniversalSimulator::get_url();

// Select matched configuration file
$parsed = get_config_file($config_dir, $context_path);

if ($parsed !== null && !empty($parsed)) {
	if (isset($parsed['PHP_CODE']) && is_array($parsed['PHP_CODE'])) {
		foreach ($parsed['PHP_CODE'] as $php_code) {
			eval($php_code);
		}
	} else {
		// Get request headers
		$request_headers = \UniversalSimulator::get_request_headers();

		// Get query
		$query = \StringProcessor::get_query($url);

		// Get request body
		$request_data = get_request_body($parsed, $url);

		// Parse request
		$request = parse_input($parsed, $request_headers, $request_data, $context_path, $query);

		// Process the transaction
		$output = process_transaction($parsed, $request);

		// Finally, send response to client
		if (!empty($output)) {
			send_response($output, $parsed, ASYNC_ENABLE);

			if (isset($output['CALLBACK_URL'])) {
				$clbk = send_callback($output);
			}
		}
	}
	die(); //a must especially if set_time_limit=0 is used and the task ends
} else {
	http_response_code("404");
	$userBrowser = \UserAgent::get_user_browser();
	if ($userBrowser || !empty($userBrowser)) {
		include_once "404.php";
	} else {
		if ((@$_SERVER['HTTP_X_FORWARDED_PROTO'] != 'https') && (empty($_SERVER['HTTPS']) || @$_SERVER['HTTPS'] === "off")) {
			$scheme = "http";
		} else {
			$scheme = "https";
		}
		$response = '<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Path Not Found</title>
</head>
<body>
	<h1>Path Not Foud</h1>
	<p>No method and path match. Please check path on <a href="/checkpath/">Check Path</a></p>
</body>
</html>';
		header("Content-type: text/html");
		header(\UniversalSimulator::HEADER_CONTENT_LENGTH . strlen($response));
		echo $response;
	}
}
