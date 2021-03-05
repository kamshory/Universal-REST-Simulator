<?php

require dirname(__FILE__)."/lib.inc/config.php";
require dirname(__FILE__)."/lib.inc/vendor/autoload.php";
use \Firebase\JWT\JWT;


error_reporting(E_ALL);
// Functions
function parse_config($context_path, $document_root = null)
{
	if($document_root == null)
	{
		$document_root = dirname(__FILE__);
	}
	$config = $document_root."/".$context_path;
	$file_content = file_get_contents($config);
	// Fixing new line
	// Some operating system may have different style
	$file_content = str_replace("\n", "\r\n", $file_content);
	$file_content = str_replace("\r\r\n", "\r\n", $file_content);
	$file_content = str_replace("\r", "\r\n", $file_content);
	$file_content = str_replace("\r\n\n", "\r\n", $file_content);
	
	$lines = explode("\r\n", $file_content);
	$array = array();
	$i = 0;
	$nl = false;
	$j = 0;

	// If line ended with \, do not explode it as array
	foreach($lines as $idx=>$line)
	{
		if(endsWith($line, "\\"))
		{
			$nl = true;
		}
		else
		{
			$nl = false;
		}
		if(!isset($array[$i]))
		{
			$array[$i] = "";
			$j = 0;
		}
		if($nl)
		{
			$line = substr($line, 0, strlen($line) - 1)."\\";
		}
		$array[$i] .= $line;
		if($j > 0)
		{
			$array[$i] .= "{[EOL]}";
		}
		if(!$nl)
		{
			$i++;
		}
		$j++;
	}
	// Parse raw file to raw configuration with it properties
	$parsed = array();
	foreach($array as $idx=>$content)
	{
		if(stripos($content, "=") > 0)
		{
			$arr = explode("=", trim($content), 2);
			$parsed[trim($arr[0])] = trim($arr[1]);
		}
	}
	return $parsed;
}

function fix_array_key($request_data)
{
	$fixed_data = array();
	foreach($request_data as $key=>$val)
	{
		$key2 = trim(preg_replace("/[^A-Za-z0-9_]/", '_', $key));
		$fixed_data[$key2] = $val;
	}
	return $fixed_data;
}

function fix_header_key($request_headers)
{
	$headers = array();
	foreach($request_headers as $key=>$val)
	{
		$key2 = trim(strtoupper(str_replace("-", "_", $key)));
		$headers[$key2] = $val;
	}
	return $headers;
}

function parse_input($config, $request_headers, $request_data, $context_path, $query)
{
	$headers = fix_header_key($request_headers);
	
	parse_str($query, $get_data);
	
	// Parsing input
	$rule = $config['PARSING_RULE'];
	$rule = trim(str_replace("\\{[EOL]}", "\r\n", $rule), " \\ ");
	if(endsWith($rule, '{[EOL]}'))
	{
		$rule = substr($rule, 0, strlen($rule) - strlen('{[EOL]}'));
	}
	
	$arr = explode("\r\n", $rule);
	$res = array();
	foreach($arr as $idx=>$line)
	{
		if(stripos($line, "=") > 0)
		{
			$arr2 = explode("=", $line, 2);			
			
			// Parse from headers
			if(stripos(trim($arr2[0]), '$INPUT.') === 0 && stripos(trim($arr2[1]), '$HEADER.') === 0)
			{
				$key = trim(substr(trim($arr2[0]), strlen('$INPUT.')));
				$value = trim($headers[$key]);
				$res[$key] = isset($value)?$value:'';
			}
			
			// Get UUID
			if(stripos(trim($arr2[0]), '$INPUT.') === 0 && trim($arr2[1]) == '$SYSTEM.UUID')
			{
				$key = trim(substr(trim($arr2[0]), strlen('$INPUT.')));
				$res[$key] = uniqid();
			}
			
			// Parse from authorization
			if(isset($headers['AUTHORIZATION']))
			{
				$authorization = trim($headers['AUTHORIZATION']);
				if(stripos($authorization, 'Basic ') === 0)
				{
					$auths = base64_decode(substr($authorization, 6));
					if(stripos($auths, ":") !== false)
					{
						$up = explode(":", $auths, 2);
						if(stripos(trim($arr2[0]), '$INPUT.') === 0 && stripos(trim($arr2[1]), '$AUTHORIZATION_BASIC.') === 0)
						{
							$key1 = trim(substr(trim($arr2[0]), strlen('$INPUT.')));
							$key2 = trim(substr(trim($arr2[1]), strlen('$AUTHORIZATION_BASIC.')));
							if($key2 == 'USERNAME')
							{
								$value = trim($up[0]);
								$res[$key1] = isset($value)?$value:'';
							}
							if($key2 == 'PASSWORD')
							{
								$value = trim($up[1]);
								$res[$key1] = isset($value)?$value:'';
							}
							
						}
					}
				}
			}

			// Parse from GET (also applied on POST and PUT)
			if(stripos(trim($arr2[0]), '$INPUT.') === 0 && stripos(trim($arr2[1]), '$GET.') === 0)
			{
				
				$key = trim(substr(trim($arr2[0]), strlen('$INPUT.')));
				$value = trim(substr(trim($arr2[1]), strlen('$GET.')));
				$res[$key] = isset($get_data[$value])?$get_data[$value]:'';
			}	
			// Parse from POST
			if(stripos(trim($arr2[0]), '$INPUT.') === 0 && stripos(trim($arr2[1]), '$POST.') === 0 && $config['METHOD'] == 'POST')
			{
				
				$key = trim(substr(trim($arr2[0]), strlen('$INPUT.')));
				$value = trim(substr(trim($arr2[1]), strlen('$POST.')));
				$res[$key] = isset($request_data[$value])?$request_data[$value]:'';
			}	
			// Parse from PUT
			if(stripos(trim($arr2[0]), '$INPUT.') === 0 && stripos(trim($arr2[1]), '$PUT.') === 0 && $config['METHOD'] == 'PUT')
			{
				
				$key = trim(substr(trim($arr2[0]), strlen('$INPUT.')));
				$value = trim(substr(trim($arr2[1]), strlen('$POST.')));
				$res[$key] = isset($request_data[$value])?$request_data[$value]:'';
			}	
			
			// Parse from request
			if(stripos(trim($arr2[0]), '$INPUT.') === 0 && stripos(trim($arr2[1]), '$REQUEST.') === 0)
			{
				if(stripos($config['REQUEST_TYPE'], '/x-www-form-urlencoded') !== false)
				{
					$key = trim(substr(trim($arr2[0]), strlen('$INPUT.')));
					$value = trim(substr(trim($arr2[1]), strlen('$REQUEST.')));
					$res[$key] = isset($request_data[$value])?$request_data[$value]:'';
				}
				else if(stripos($config['REQUEST_TYPE'], '/json') !== false 
					|| stripos($config['REQUEST_TYPE'], '/xml') !== false)
				{
					$obj = json_decode(json_encode($request_data));
					$key = trim(substr(trim($arr2[0]), strlen('$INPUT.')));
					$tst = trim(substr(trim($arr2[1]), strlen('$REQUEST.')));
					$attr = str_replace(".", "->", $tst);					
					$value = eval('return @$obj->'.$attr.';');					
					$res[$key] = isset($value)?$value:'';
				}
			}
		}
	}
	// Parse URL
	if(stripos($config['PATH'], '{[') !== false && stripos($config['PATH'], ']}') !== false)
	{
		$start = stripos($config['PATH'], '{[');
		$end = stripos($config['PATH'], ']}');
		
		if($start === false || $end === false)
		{
			$base_path = $config['PATH'];
		}
		else
		{
			$base_path = substr($config['PATH'], 0, $start);
			$wildcard_data = trim(substr($context_path, $start), "/");
			$wildcard_variable = trim(substr($config['PATH'], $start), "/");
			$arr1 = explode("/", $wildcard_variable);
			$arr2 = explode("/", $wildcard_data);
			foreach($arr1 as $key=>$val)
			{
				if(startsWith($val, '{[') && endsWith($val, ']}'))
				{
					$par = trim(str_replace(array('{[', ']}'), '', $val));
					$res[$par] = $arr2[$key];
				}		
			}
		}	
	}
	
	return $res;
}

function is_valid_jwt($token_sent)
{
	global $appConfig;
	$secret_key = $appConfig->jwtSecret;
	try 
	{
		$decoded = JWT::decode($token_sent, $secret_key, array('HS256'));
		return true;
	}
	catch(Exception $e)
	{
		return false;
	}
}

function validate_token($string, $token_sent)
{
	if(stripos($string, '$ISVALIDTOKEN') !== false)
	{
		do
		{
			$total_length = strlen($string);
			$start = stripos($string, '$string');
			$p1 = 0;
			$rem = 0;
			$found = false;
			do
			{
				$f1 = substr($string, $start+$p1, 1);
				$f2 = substr($string, $start+$p1, 1);
				if($f1 == "(")
				{
					$rem++;
					$found = true;
				}
				if($f2 == ")")
				{
					$rem--;
					$found = true;
				}
				$p1++;
			}
			while($rem > 0 || !$found); 
			$formula = substr($string, $start, $p1);
			$fm1 = trim($formula, " \r\n\t ");
			$fm1 = substr($fm1, 6, strlen($fm1)-7);
			$fm1 = trim($fm1, " \r\n\t ");
			if((startsWith($fm1, "'") && endsWith($fm1, "'")) || (startsWith($fm1, '"') && endsWith($fm1, '"')))
			{
				$fm1 = substr($fm1, 1, strlen($fm1)-2);
			}
			
			if(is_valid_jwt($token_sent))
			{
				$result = "true";	
			}
			else
			{
				$result = "false";	
			}
			$string = str_ireplace($formula, $result, $string);
			if($start + $p1 >= $total_length)
			{
				break;
			}
		}
		while(stripos($string, '$ISVALIDTOKEN') !== false);
	}
	return $string;
}

function generate_token()
{
	global $appConfig;
	$id = $appConfig->jwtUserID;
	$firstname = $appConfig->jwtUserFirstName;
	$lastname = $appConfig->jwtUserLastName;
	$email = $appConfig->jwtUserEmail;


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
		"data" => array(
	));


	$jwt = JWT::encode($token, $secret_key);
	return array(
		"JWT"=>$jwt,
		"EXPIRE_AT"=>$expire_claim,
		"EXPIRE_IN"=>$lifetime
	);
}

function process_transaction($parsed, $request)
{
	$token_sent = get_token();
	$content_type = $parsed['RESPONSE_TYPE'];
	$transaction_rule = $parsed['TRANSACTION_RULE'];
	$transaction_rule = trim($transaction_rule, "\\");
	$transaction_rule = str_replace("\\{[EOL]}$"."OUTPUT.", "\\{[EOL]}\r\n$"."OUTPUT.", $transaction_rule);
	if(endsWith($transaction_rule, '{[EOL]}'))
	{
		$transaction_rule = substr($transaction_rule, 0, strlen($transaction_rule) - strlen('{[EOL]}'));
	}
	$arr = explode('{[ENDIF]}', $transaction_rule);
	$return_data = array();
	$token_generated = null;
	foreach($arr as $idx=>$data)
	{
		if(stripos($data, "{[THEN]}") > 0)
		{
			$arr2 = explode("{[THEN]}", $data);
			$rcondition = $arr2[0];
			$rcondition = str_replace("\\{[EOL]}", "\r\n", $rcondition);
			$rcondition = validate_token($rcondition, $token_sent);
			$rline = $arr2[1];
			
			// TODO Evaluate condition
			$str = preg_replace( '/[^a-z0-9\.\$_]/i', ' ', $rcondition); 
			$str = preg_replace('/\s\s+/', ' ', $str);
			$arr5 = explode(" ", $str);
			foreach($arr5 as $idx3=>$word)
			{
				if(stripos($word, '$INPUT.') === 0)
				{
					$var = substr($word, strlen('$INPUT.'));
					$rcondition = str_replace($word, '$request[\''.$var.'\']', $rcondition);
				}
				
				
			}
			$rcondition = trim($rcondition, " \t\r\n ");
			if(stripos($rcondition, '{[IF]}') === 0)
			{
				$rcondition = str_ireplace('{[IF]}', 'if', $rcondition);
				$rcondition = trim(substr($rcondition, 2));
			}
			
			$test = eval("return ".$rcondition.";");
			if($test)
			{
				$arr3 = explode("\r\n", $rline);
				foreach($arr3 as $idx2=>$result)
				{
					// TODO Parse result
					$str = preg_replace( '/[^a-z0-9\.\$_]/i', ' ', $result); 
					$str = str_replace('$INPUT.', ' $INPUT.', $str);
					$str = preg_replace('/\s\s+/', ' ', $str);
					$arr5 = explode(" ", $str);
					foreach($arr5 as $idx3=>$word)
					{
						if(stripos($word, '$INPUT.') === 0)
						{
							$var = substr($word, strlen('$INPUT.'));
							$result = str_replace($word, $request[$var], $result);
						}
						if(stripos($word, '$TOKEN.') === 0)
						{
							if($token_generated === null)
							{
								$token_generated = generate_token();
							}
							$var = substr($word, strlen('$TOKEN.'));
							$result = str_replace($word, $token_generated[$var], $result);
						}
						
					}
					$result = trim(str_replace("\\{[EOL]}", "\r\n", $result), " \\\r\n ");
					$result = replace_date($result);
					$result = replace_calc($result);
					$result = ltrim($result, " \t\r\n ");
					$arr6 = explode("=", $result, 2);
					$variable = $arr6[0];
					$value = $arr6[1];
					
					if(stripos($variable, '$OUTPUT.') === 0)
					{
						$variable = substr($variable, 8);
					}
					$variable = trim($variable);
					if(strlen($variable) > 0)
					{
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

function eval_date($args)
{
	$args = trim(substr($args, 5), " \t\r\n ");
	$args = substr($args, 1, strlen($args) - 2);
	$parts = preg_split("/(?:'[^']*'|)\K\s*(,\s*|$)/", $args);
	$result = array_filter($parts, function ($value) {
		return ($value !== '');
	});
	
	if(count($result) == 1)
	{
		// no time zone
		if(startsWith($result[0], "'"))
		{
			$result[0] = substr($result[0], 1);
		}
		if(endsWith($result[0], "'"))
		{
			$result[0] = substr($result[0], 0, strlen($result[0]) - 1);
		}
		return date($result[0]);
	}
	else if(count($result) == 2)
	{
		if(startsWith($result[0], "'"))
		{
			$result[0] = substr($result[0], 1);
		}
		if(endsWith($result[0], "'"))
		{
			$result[0] = substr($result[0], 0, strlen($result[0]) - 1);
		}
		if(startsWith($result[1], "'"))
		{
			$result[1] = substr($result[1], 1);
		}
		if(endsWith($result[1], "'"))
		{
			$result[1] = substr($result[1], 0, strlen($result[1]) - 1);
		}
		$fmt = str_replace("'", "", trim($result[0]));
		$tz = str_replace("'", "", trim($result[1]));
		
		if(stripos($tz, 'UTC') !== false)
		{
			$tz1 = (str_replace("UTC", "", $tz) * 1);	
			$tz2 = date('Z')/3600;		
			return date($fmt, time(0) + (($tz1 - $tz2) * 3600));			
		}
		else
		{
			date_default_timezone_set($tz);
			return date($fmt);			
		}
	}
}

function replace_date($string)
{
	if(stripos($string, '$DATE') !== false)
	{
		do
		{
			$total_length = strlen($string);
			$start = stripos($string, '$DATE');
			$p1 = 0;
			$rem = 0;
			$found = false;
			do
			{
				$f1 = substr($string, $start+$p1, 1);
				$f2 = substr($string, $start+$p1, 1);
				if($f1 == "(")
				{
					$rem++;
					$found = true;
				}
				if($f2 == ")")
				{
					$rem--;
					$found = true;
				}
				$p1++;
			}
			while($rem > 0 || !$found); 
			$formula = substr($string, $start, $p1);
			$fm1 = trim($formula, " \r\n\t ");
			$fm1 = substr($fm1, 6, strlen($fm1)-7);
			$fm1 = trim($fm1, " \r\n\t ");
			$result = eval_date('date('.$fm1.')');
			
			$string = str_ireplace($formula, $result, $string);
			if($start + $p1 >= $total_length)
			{
				break;
			}
		}
		while(stripos($string, '$DATE') !== false);
	}
	return $string;
}
function replace_calc($string)
{
	
	if(stripos($string, '$CALC') !== false)
	{
		do
		{
			$total_length = strlen($string);
			$start = stripos($string, '$CALC');
			$p1 = 0;
			$rem = 0;
			$found = false;
			do
			{
				$f1 = substr($string, $start+$p1, 1);
				$f2 = substr($string, $start+$p1, 1);
				if($f1 == "(")
				{
					$rem++;
					$found = true;
				}
				if($f2 == ")")
				{
					$rem--;
					$found = true;
				}
				$p1++;
			}
			while($rem > 0 || !$found); 
			$formula = substr($string, $start, $p1);
			$fm1 = trim($formula, " \r\n\t ");
			$fm1 = substr($fm1, 6, strlen($fm1)-7);
			$fm1 = trim($fm1, " \r\n\t ");
			if((startsWith($fm1, "'") && endsWith($fm1, "'")) || (startsWith($fm1, '"') && endsWith($fm1, '"')))
			{
				$fm1 = substr($fm1, 1, strlen($fm1)-2);
			}
			$result = eval("return $fm1;");	
			$string = str_ireplace($formula, $result, $string);
			if($start + $p1 >= $total_length)
			{
				break;
			}
		}
		while(stripos($string, '$CALC') !== false);
	}
	return $string;
}

function get_query($url)
{
	return parse_url($url, PHP_URL_QUERY);
}

function get_request_body($parsed, $url)
{
	if($parsed['METHOD'] == 'GET')
	{
		$query = get_query($url);
		parse_str($query, $request_data);
	}
	else if($parsed['METHOD'] == 'POST' || $parsed['METHOD'] == 'PUT')
	{
		if(stripos($parsed['REQUEST_TYPE'], '/x-www-form-urlencoded') !== false)
		{
			$query = file_get_contents("php://input");
			parse_str($query, $request_data);
		}
		else if(stripos($parsed['REQUEST_TYPE'], '/json') !== false)
		{
			$input_buffer = file_get_contents("php://input");
			$request_data = json_decode($input_buffer, true);
			$request_data = fix_array_key($request_data);
		}
		else if(stripos($parsed['REQUEST_TYPE'], '/xml') !== false)
		{
			$input_buffer = file_get_contents("php://input");
			$request_data = json_decode(json_encode(simplexml_load_string($input_buffer)), true);
			$request_data = fix_array_key($request_data);
		}
	}
	return $request_data;
}

function parse_match_url($config_path, $request_path)
{

	// Parsing
	$arr1 = explode(']}', $config_path);
	$params = array();
	foreach($arr1 as $key=>$val)
	{
		$pos = stripos($val, '{[', 0);
		if($pos !== false)
		{
			$arr1[$key] = substr($val, 0, $pos);
			$params[] = substr($val, $pos + 2);
		}
	}
	$values = array();
	if(count($arr1) > 1)
	{
		$j = 0;
		$cur = 0;
		$start = 0;
		$end = 0;
		$lastpost = 0;
		for($i = 0; $i<count($arr1) - 1; $i++)
		{
			$curval = $arr1[$i];
			$nextval = $arr1[$i+1];
			$start = $cur + strlen($curval);
			$end = stripos($request_path, $nextval, $end);
			
			$arr2[] = substr($request_path, $lastpost, $start - $lastpost);
			
			$lastpost = $end;
			
			$values[$params[$j]] = substr($request_path, $start, $end-$start);
			
			$cur = $end;
			$j++;
			
		}
		$arr2[] = substr($request_path, $lastpost);
	}
	return array(
		'config_path_list'=>$arr1,
		'request_path_list'=>$arr1,
		'param_list'=>$params,
		'param_values'=>$values
		);
		
}

function is_match_path($config_path, $request_path)
{
	$match = false;
	$start = stripos($config_path, '{[');
	if($start === false)
	{
		if(endsWith($config_path, "/"))
		{
			$match = stripos($request_path, $config_path) === 0;
		}
		else
		{
			$match = stripos($request_path."/", $config_path."/") === 0;
		}
	}
	else
	{
		/*
		$end = stripos($config_path, ']}');
		if($start === false || $end === false)
		{
			$base_path = $config_path;
		}
		else
		{
			$base_path = substr($config_path, 0, $start);
		}
		$match = stripos($request_path, $base_path) === 0;
		*/
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
	if(count($arr1) <= count($arr2))
	{
		$oke = true;
		foreach($arr1 as $k=>$v)
		{
			if($arr2[$k] != $v)
			{
				$oke = false;
				break;
			}
		}
	}
	return $oke;
	
}
function get_config_file($dir, $context_path)
{
	if ($handle = opendir($dir)) 
	{
		while (false !== ($file = readdir($handle))) 
		{
			if ('.' === $file || '..' === $file)
			{
				continue;
			}
			$filepath = rtrim($dir, "/")."/".$file;	
			$prsd = parse_config($filepath);
			if(is_match_path($prsd['PATH'], $context_path) && $prsd['METHOD'] == $_SERVER["REQUEST_METHOD"])
			{
				$parsed = $prsd;
				break;
			}
		}
		closedir($handle);
	}
	return $parsed;
}

if(!function_exists('getallheaders'))
{
	function getallheaders()
	{
		$headers = array();
		foreach ($_SERVER as $name => $value)
		{
			if (substr($name, 0, 5) == 'HTTP_')
			{
				$key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
				$headers[$key] = $value;
			}
		}
		return $headers;
	}
}
 
function get_request_headers()
{
	return getallheaders();
}

function startsWith( $haystack, $needle ) 
{
     $length = strlen( $needle );
     return substr( $haystack, 0, $length ) === $needle;
}

function endsWith( $haystack, $needle ) 
{
    $length = strlen( $needle );
    if( !$length ) {
        return true;
    }
    return substr( $haystack, -$length ) === $needle;
}

function get_context_path()
{
	$url = $_SERVER['REQUEST_URI'];
	return parse_url($url, PHP_URL_PATH);
}

function get_url()
{
	return $_SERVER['REQUEST_URI'];
}

function send_response_header($headers)
{
	$arr = explode("\r\n", $headers);
	foreach($arr as $header)
	{
		$header = trim($header, " \t\r\n");
		if(stripos($header, ":") > 0)
		{
			header($header);
		}
	}
}
function get_token()
{
	$token_sent = "";
	$headers = get_request_headers();
	if(isset($headers['Authorization']))
	{
		$auth = $headers['Authorization'];
		if(stripos($auth, 'Bearer ') === 0)
		{
			$token_sent = substr($auth, 7);
		}
	}
	return $token_sent;
}

function send_callback($output)
{
	$res = "";
	$url = @$output['CALLBACK_URL'];
	if(stripos($url, "://") !== false)
	{		
		$body = @$output['CALLBACK_BODY'];
		$content_length = strlen($body);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST , 0);
		$headers = array();
		if(isset($output['CALLBACK_HEADER']))
		{
			$header = trim($output['CALLBACK_HEADER'], " \t\r\n ");
			if(strlen($header) > 2)
			{
				$headers = explode("\r\n", $header);				
			}
			if(stripos($header, "User-agent: ") === false)
			{
				$headers[] = 'User-agent: Universal REST Simulator';
			}
		}
		if(strtoupper($output['CALLBACK_METHOD']) == 'POST')
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			if($content_length > 0)
			{
				$headers[] = 'Content-length: '.$content_length;
				if(isset($output['CALLBACK_TYPE']))
				{
					$headers[] = 'Content-type: '.$output['CALLBACK_TYPE'];
				}
				curl_setopt($ch, CURLOPT_POSTFIELDS, $body);  
			}
		}
		else if(strtoupper($output['CALLBACK_METHOD']) == 'PUT')
		{
			curl_setopt($ch, CURLOPT_PUT, 1);
			if($content_length > 0)
			{
				$headers[] = 'Content-length: '.$content_length;
				if(isset($output['CALLBACK_TYPE']))
				{
					$headers[] = 'Content-type: '.$output['CALLBACK_TYPE'];
				}
				curl_setopt($ch, CURLOPT_POSTFIELDS, $body);  
			}
		}
		if(!empty($headers))
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		$res = curl_exec($ch);
		curl_close($ch);
	}
	return $res;
}
// End of functions

error_reporting(0);

$config_dir = "config";


// Get context path
$context_path = get_context_path();

// Get URL
$url = get_url();
	
// Select configuration file
$parsed = get_config_file($config_dir, $context_path);

if(!empty($parsed))
{
	// Get request headers
	$request_headers = get_request_headers();
	
	// Get query
	$query = get_query($url);

	// Get request body
	$request_data = get_request_body($parsed, $url);

	// Parse request
	$request = parse_input($parsed, $request_headers, $request_data, $context_path, $query);
	// Process the transaction
	$output = process_transaction($parsed, $request);

	// Finally, send response to client
	if(!empty($output))
	{
		if(isset($output['CALLBACK_URL']))
		{
			$clbk = send_callback($output);
		}
		if(isset($output['DELAY']))
		{
			$delay = @$output['DELAY'] * 1;			
			if($delay > 0)
			{
				usleep($delay * 1000);
			}
		}
		if(isset($output['HEADER']))
		{
			$clbk = send_response_header($output['HEADER']);
		}
		if(isset($parsed['RESPONSE_TYPE']))
		{
			$content_type = $parsed['RESPONSE_TYPE'];
			header("Content-type: $content_type");
		}
		if(isset($output['BODY']))
		{
			$response = @$output['BODY'];
			header("Content-length: ".strlen($response));
			if(isset($output['STATUS']))
			{
				http_response_code($output['STATUS']);
			}
			echo $response;
		}
	}
}

?>