<?php
include_once dirname(__FILE__)."/functions.php";
include_once dirname(__FILE__)."/auth.php";
include dirname(__FILE__)."/conf.php";
if($cfg->authentification_needed && !$userlogin)
{
	exit();
}
$request_dir = kh_filter_input(INPUT_GET, 'dir');
$dir = path_decode($request_dir, $cfg->rootdir);

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

function get_conditions($parsed)
{
	if(!isset($parsed['TRANSACTION_RULE']))
	{
		return array();
	}
	$trx = $parsed['TRANSACTION_RULE'];
	$trx = str_replace('\{[EOL]}', "\r\n", $trx);
	$trx = ltrim($trx, "\\");
	$arr = explode("{[ENDIF]}", $trx);
	$res = array();
	foreach($arr as $key=>$val)
	{
		if(stripos($val, "{[THEN]}"))
		{
			$arr2 = explode("{[THEN]}", $val);
			if(stripos($arr2[0], "{[IF]}") !== false)
			{
				$arr2[0] = trim($arr2[0], " \r\n\t ");
				if(stripos($arr2[0], "{[IF]}") === 0)
				{
					$arr2[0] = trim(substr($arr2[0], strlen("{[IF]}")), " \r\n\t ");
				}
			}
			$res[] = array('condition'=>$arr2[0], 'output'=>trim($arr2[1], " \r\n\t "));
		}
	}
	return $res;
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
			$conditions = get_conditions($prsd);
			$count_condition = count($conditions);
			if(!isset($result[$cpath]))
			{
				$result[$cpath] = array();
				$result[$cpath]['DATA'] = array();
			}
			$result[$cpath]['DATA'][] = array(
				'PATH'=>$cpath,
				'METHOD'=>$cmehod,
				'CONDITIONS'=>$count_condition,
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

function count_duplicated($result)
{
	$dup = 0;
	foreach($result as $key=>$val)
	{
		if($result[$key]['DUPLICATED'])
		{
			$dup++;
		}
	}
	return $dup;
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

$parsed = get_config_file($dir);
?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="file-table file-result-table">
    <thead>
        <tr>
            <td width="25%">FILE</td>
            <td width="45%">PATH</td>
            <td width="10%">METHOD</td>
            <td width="10%">CONDITION</td>
            <td width="10%">COUNT</td>
        </tr>
    </thead>
    <tbody>
        <?php
foreach($parsed as $item)
{
foreach($item['DATA'] as $row)
{
?>
        <tr data-file-name-lower="<?php echo htmlspecialchars(strtolower(basename($row['FILE'])));?><?php echo htmlspecialchars(strtolower($row['PATH']));?>"
            data-file-name="<?php echo htmlspecialchars(strtolower(basename($row['FILE'])));?>"
            data-path="<?php echo htmlspecialchars(strtolower($row['PATH']));?>">
            <td><a target="_blank"
                    href="../filemanager/code-editor.php?filepath=<?php echo htmlspecialchars(rtrim($request_dir, "/")."/");?><?php echo urlencode($row['FILE']);?>"><?php echo $row['FILE'];?></a>
            </td>
            <td><?php echo $row['PATH'];?></td>
            <td><?php echo $row['METHOD'];?></td>
            <td><?php echo $row['CONDITIONS'];?></td>
            <td><?php echo $item['LENGTH'];?></td>
        </tr>
        <?php
}
}
?>
    </tbody>
</table>
<?php
if(count_duplicated($parsed))
{
?>

<h3>Duplicated Config</h3>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="file-table file-result-table">
    <thead>
        <tr>
            <td width="25%">FILE</td>
            <td width="45%">PATH</td>
            <td width="10%">METHOD</td>
            <td width="10%">CONDITION</td>
            <td width="10%">COUNT</td>
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
        <tr data-file-name-lower="<?php echo htmlspecialchars(strtolower(basename($row['FILE'])));?><?php echo htmlspecialchars(strtolower($row['PATH']));?>"
            data-path="<?php echo htmlspecialchars(strtolower($row['PATH']));?>">
            <td><a target="_blank"
                    href="../filemanager/code-editor.php?filepath=base%2F<?php echo urlencode($row['FILE']);?>"><?php echo $row['FILE'];?></a>
            </td>
            <td><?php echo $row['PATH'];?></td>
            <td><?php echo $row['METHOD'];?></td>
            <td><?php echo $row['CONDITIONS'];?></td>
            <td><?php echo $item['LENGTH'];?></td>
        </tr>
        <?php
		}
	}
}
		?>
    </tbody>
</table>
<?php
}