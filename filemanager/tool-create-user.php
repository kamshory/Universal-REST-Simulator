<?php
if(!isset($securityKey))
{
    $securityKey = sha1(mt_rand(11111, 99999999));
}
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Universal REST Simulator</title>
<link rel="shortcut icon" href="../static/images/16x16.png" type="image/jpeg" />
<link rel="stylesheet" type="text/css" href="style/login.css" />
</head>
<body>
<div class="all">
<div class="box">
	<div class="box-inner">
        <div class="box-title">Universal REST Simulator</div>
        <div class="box-form">
        <form id="form1" name="form1" method="post" action="create-user.php">
        <div class="label">Username</div>
        <div class="field">
        <input type="text" name="username" id="username" class="input-text-login" autocomplete="off" />
        </div>
        <div class="clear"></div>
        <div class="label">Password</div>
        <div class="field">
        <input type="password" name="password" id="password" class="input-text-login" autocomplete="off" />
        </div>
        <div class="clear"></div>
        <div class="field">
        <div class="button-area"><input type="hidden" name="ref" id="ref" value="<?php echo htmlspecialchars(strip_tags($_SERVER['REQUEST_URI']));?>" />
        <input type="submit" name="login" id="login" value="Create User" class="login-button" />
        <input type="hidden" name="sec" value="<?php echo $securityKey;?>"> 
        </div>
        <div class="clear"></div>
        </div>
        </form>
         </div>
    </div>
</div>
<div class="footer">
 &copy; <a href="https://www.github.com/kamshory">Kamshory</a> 2010-<?php echo date('Y');?>. All rights reserved.</div>
</div>
</body>
</html>
