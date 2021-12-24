<?php
require_once dirname(dirname(__FILE__))."/lib.inc/config.php";

// Functions
function fix_document_root($document_root)
{
	if($document_root == null)
	{
		$document_root = dirname(__FILE__);
	}
	return $document_root;
}
function parse_config($context_path, $document_root = null)
{
	if($document_root !== null)
	{
		$document_root = fix_document_root($document_root);
		$config = $document_root."/".$context_path;
	}
	else
	{
		$config = $context_path;
	}

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
	$document_root = (USE_RELATIVE_PATH)?dirname(dirname(__FILE__)):null;

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
			$filepath = rtrim($dir, "/")."/".$file;	
			$filepathFileManager = $filepath;
			if(!USE_RELATIVE_PATH)
			{
				$filepathFileManager = ltrim(substr($filepathFileManager, strlen($dir)), "/\\");
			}
			$prsd = parse_config($filepath, $document_root);
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
				'FILE'=>$filepathFileManager
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


error_reporting(0);

$config_dir = CONFIG_DIR;


	
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
	.filter-area{
		padding:4px 0;
	}
	input[type="text"]{
		width: 100%;
		box-sizing: border-box;
		padding: 6px 10px;
		color: #444444;
		background-color: #FFFFFF;
		border: 1px solid #999999;
		transition: all ease-in-out 0.2s;
	}
	input[type="text"]:focus, input[type="text"]:focus-visible, input[type="text"]:focus-within{
		outline: none;
		border: 1px solid #3583e8;
	}
	</style>
	<script src="../filemanager/js/jquery/jquery.min.js"></script>
	<script>
	function filterFile(obj)
	{
		var name = obj.val();
		var table = obj.closest('.data-block').find('table');
		name = name.toLowerCase();
		if(name=="")
		{
			table.find('tbody tr').css({'display':''});
		}
		else
		{
			table.find('tbody tr').css({'display':'none'});
			table.find('tbody tr[data-file*="'+name+'"]').css({'display':''});
			table.find('tbody tr[data-path*="'+name+'"]').css({'display':''});
		}
	}
	$(document).ready(function(e){
		$(document).on('change keyup', '.filter-area input[type="text"]', function(e2){
			filterFile($(this))
		});
	});
	</script>
</head>
<body>

<h3>All Config</h3>
<div class="data-block">
<div class="filter-area">
	<input type="text" name="main" id="main" placeholder="Type here to filter...">
</div>
<table width="100%" border="1" class="config-table-main">
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
		<tr data-file="<?php echo htmlspecialchars(strtolower(basename($row['FILE'])));?>" data-path="<?php echo htmlspecialchars(strtolower($row['PATH']));?>">
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
</div>

<h3>Duplicated Config</h3>
<div class="data-block">
<div class="filter-area">
	<input type="text" name="duplicated" id="duplicated" placeholder="Type here to filter...">
</div>
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
		<tr data-file="<?php echo htmlspecialchars(strtolower(basename($row['FILE'])));?>" data-path="<?php echo htmlspecialchars(strtolower($row['PATH']));?>">
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
</div>
<p>Go to <a href="../filemanager/">File Manager</a></p>
</body>
</html>
