<?php
include_once dirname(__FILE__)."/functions.php";
include_once dirname(__FILE__)."/auth.php";
include dirname(__FILE__)."/conf.php";
if($cfg->authentification_needed && !$userlogin)
{
	include_once dirname(__FILE__)."/tool-login-form.php";
	exit();
}
if(@$_GET['option'] == 'ajax-load')
{
	$cnt = "";
	$path = kh_filter_input(INPUT_GET, 'filepath');
	$filepath = path_decode($path, $cfg->rootdir);
	if(file_exists($filepath))
	{
		$cnt = file_get_contents($filepath);
		echo $cnt;
	}
}
else
{
	$cnt = "";
	$path = kh_filter_input(INPUT_GET, 'filepath');
	$filepath = path_decode($path, $cfg->rootdir);
	if(file_exists($filepath))
	{
		$cnt = file_get_contents($filepath);
	}
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Universal REST Simulator Code Editor</title>
<script type="text/javascript" src="js/jquery/jquery.min.js"></script>
<link rel="stylesheet" href="style/file-type.css">
<link rel="stylesheet" href="cm/lib/codemirror.css">
<link rel="stylesheet" href="style/code-editor.css">
<script src="cm/lib/codemirror.js"></script>
<script src="cm/addon/mode/loadmode.js"></script>
<script src="cm/mode/meta.js"></script>
<script src="js/code-editor.js"></script>
</head>
<body>
<div>
<article>
<form method="post" enctype="multipart/form-data" action="" onsubmit="return false;">
<div class="file">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><input type="text" name="filename" id="filename" value="<?php echo path_encode($filepath, $cfg->rootdir);?>" autocomplete="off" placeholder="File" required></td>
    <td width="60" style="padding-left:4px;"><input type="button" name="open" id="open" value="Open"></td>
    <td width="60" style="padding-left:4px;"><input type="button" name="save" id="save" value="Save"></td>
    <td width="70" style="padding-left:4px;"><input type="button" name="tutorial" id="tutorial" value="Tutorial" onclick="window.open('../tutorial/')"></td>
  </tr>
</table>
</div>
<div class="code">
<textarea id="code" name="code"><?php echo htmlspecialchars($cnt);?></textarea>
</div>
</form>
</article>
</div>
<div class="alert">
	<div class="alert-title">Save File</div>
	<div class="alert-content">
	</div>
	<div class="alert-button">
		<button>Close</button>
	</div>
</div>
</body>
</html>
<?php
}
?>