<?php

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
		}
		if($nl)
		{
			$line = substr($line, 0, strlen($line) - 1)."\\";
		}
		$array[$i] .= $line;
		if(!$nl)
		{
			$i++;
		}
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
function parse_input($config, $request_headers, $request_data)
{
	$headers = fix_header_key($request_headers);
	
	// Parsing input
	$rule = $config['PARSING_RULE'];
	$rule = str_replace("\\", "\r\n", $rule);
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
	return $res;
}

function process_transaction($parsed, $request)
{
	$content_type = $parsed['RESPONSE_TYPE'];
	$transaction_rule = $parsed['TRANSACTION_RULE'];
	$transaction_rule = str_replace('\\$', "\r\n$", $transaction_rule);
	$arr = explode('ENDIF', $transaction_rule);
	$return_data = array();
	foreach($arr as $idx=>$data)
	{
		if(stripos($data, "THEN") > 0)
		{
			$arr2 = explode("THEN", $data);
			$rcondition = str_replace("\\", "\r\n", $arr2[0]);
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
			if(stripos($rcondition, 'IF') === 0)
			{
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
					$str = preg_replace('/\s\s+/', ' ', $str);
					$arr5 = explode(" ", $str);
					foreach($arr5 as $idx3=>$word)
					{
						if(stripos($word, '$INPUT.') === 0)
						{
							$var = substr($word, strlen('$INPUT.'));
							$result = str_replace($word, $request[$var], $result);
						}
						
					}
					$result = replace_date($result);
					$result = ltrim($result, " \t\r\n ");
					$arr6 = explode("=", $result, 2);
					$variable = $arr6[0];
					$value = $arr6[1];
					$value = str_replace("\\", "\r\n", $value);
					if(stripos($variable, '$') === 0)
					{
						$variable = substr($variable, 1);
					}
					
					$return_data[$variable] = $value;	
				}
				break;
			}
		}
	}
	return $return_data;
}

function eval_date($args)
{
	$quote = substr_count($args,"'");
	if($quote == 2)
	{
		// no time zone
		$args = str_replace('$', '', $args);
		return eval("return $args;");
	}
	else
	{
		$args = trim(str_replace(array('$DATE', '(', ')'), ' ', $args));
		$arr = explode(',', $args);
		$fmt = str_replace("'", "", trim($arr[0]));
		$tz = str_replace("'", "", trim($arr[1]));
		
		if(stripos($tz, 'UTC') !== false)
		{
			$tz1 = (str_replace("UTC", "", $tz) * 1);			
			return date($fmt, time(0) + ($tz1 * 3600));			
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
		do{
			$start = stripos($string, '$DATE');
			$stop = stripos($string, ")");
			if($stop !== false)
			{
				$src = substr($string, $start, $stop + 1 - $start);
				$date = eval_date($src);
				$string = str_replace($src, $date, $string);
				break;
			}
		}
		while(stripos($string, '$DATE') !== false);
	}
	return $string;
}

function get_request_body($parsed, $url)
{
	if($parsed['METHOD'] == 'GET')
	{
		$query = parse_url($url, PHP_URL_QUERY);
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
			$request_data = json_decode(json_encode(new SimpleXMLElement($input_buffer)), true);
			$request_data = fix_array_key($request_data);
		}
	}
	return $request_data;
}

function get_config_file($dir, $context_path)
{
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if ('.' === $file) continue;
			if ('..' === $file) continue;
			$filepath = rtrim($dir, "/")."/".$file;	
			$prsd = parse_config($filepath);
			if($prsd['PATH'] == $context_path)
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
	$context_path = parse_url($url, PHP_URL_PATH);
	return $context_path;
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

	// Get request body
	$request_data = get_request_body($parsed, $url);

	// Parse request
	$request = parse_input($parsed, $request_headers, $request_data);

	// Process the transaction
	$output = process_transaction($parsed, $request);

	// Finally, send response to client
	if(!empty($output))
	{
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
			send_response_header($output['HEADER']);
		}
		if(isset($parsed['RESPONSE_TYPE']))
		{
			$content_type = $parsed['RESPONSE_TYPE'];
			header("Content-type: $content_type");
		}
		if(isset($output['OUTPUT']))
		{
			$response = @$output['OUTPUT'];
			header("Content-length: ".strlen($response));
			echo $response;
		}
	}
}

?>
