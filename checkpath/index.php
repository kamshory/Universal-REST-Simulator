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


function get_config_file($dir)
{
	$result = array();
	if ($handle = opendir($dir)) 
	{
		while (false !== ($file = readdir($handle))) 
		{
			
			if(@is_dir($dir."/".$file))
			{
				continue;
			}
			if ('.' === $file || '..' === $file)
			{
				continue;
			}
			$filepath = $file;	
			$prsd = parse_config($filepath, dirname(dirname(__FILE__))."/config");
			$cpath = $prsd['PATH'];
			$cmehod = $prsd['METHOD'];
			if(!isset($result[$cpath]))
			{
				$result[$cpath] = array();
				$result[$cpath]['DATA'] = array();
			}
			$result[$cpath]['DATA'][] = array(
				'PATH'=>$cpath,
				'METHOD'=>$cmehod,
				'FILE'=>$filepath
			);
			
		}
		closedir($handle);
	}
	foreach($result as $key=>$val)
	{
		if(count($val['DATA'])>1)
		{
			$result[$key]['DUPLICATED'] = true;
		}
		else
		{
			$result[$key]['DUPLICATED'] = false;
		}
		$result[$key]['LENGTH'] = count($val['DATA']);
	}
	return $result;
}

function endsWith( $haystack, $needle ) 
{
    $length = strlen( $needle );
    if( !$length ) {
        return true;
    }
    return substr( $haystack, -$length ) === $needle;
}



error_reporting(E_ALL);

$config_dir = dirname(dirname(__FILE__))."/config";


	
// Select configuration file
$parsed = get_config_file($config_dir);
//header("Content-type: application/json");
//echo json_encode($parsed, JSON_PRETTY_PRINT);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Config Info</title>
	<style>
	table{
		border-collapse:collapse;
	}
	td{
		padding:5px 12px;
	}
	thead{
		font-weight:bold;
	}
	a{
		text-decoration:none;
		color:#444444;
	}
	</style>
</head>
<body>

<h3>All Config</h3>
<table width="100%" border="1">
<thead>
		<tr>
		<td width="30%">FILE</td>
		<td width="50%">PATH</td>
		<td width="10%">METHOD</td>
		<td with="10%">COUNT</td>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach($parsed as $item)
	{

		foreach($item['DATA'] as $row)
		{
	?>
		<tr>
		<td><a target="_blank" href="../filemanager/code-editor.php?filepath=base%2F<?php echo urlencode($row['FILE']);?>"><?php echo $row['FILE'];?></a></td>
		<td><?php echo $row['PATH'];?></td>
		<td><?php echo $row['METHOD'];?></td>
		<td><?php echo $item['LENGTH'];?></td>
		</tr>
		<?php
		}
	}
		?>
	</tbody>
</table>

<h3>Duplicated Config</h3>
<table width="100%" border="1">
<thead>
		<tr>
		<td width="30%">FILE</td>
		<td width="50%">PATH</td>
		<td width="10%">METHOD</td>
		<td with="10%">COUNT</td>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach($parsed as $item)
	{
		if($item['DUPLICATED'])
		{

		foreach($item['DATA'] as $row)
		{
	?>
		<tr>
		<td><a target="_blank" href="../filemanager/code-editor.php?filepath=base%2F<?php echo urlencode($row['FILE']);?>"><?php echo $row['FILE'];?></a></td>
		<td><?php echo $row['PATH'];?></td>
		<td><?php echo $row['METHOD'];?></td>
		<td><?php echo $item['LENGTH'];?></td>
		</tr>
		<?php
		}
	}
}
		?>
	</tbody>
</table>
</body>
</html>
