<?php
if(!file_exists(dirname(__FILE__)."/.htpasswd"))
{
    $securityKey = sha1($_SERVER['REMOTE_ADDR'].date('U').mt_rand(100000, 9999999));
    $_SESSION['sescu'] = $securityKey;
    include_once dirname(__FILE__)."/tool-create-user.php";
    exit();
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Universal REST Simulator - File Manager</title>
    <link rel="icon" href="data:;base64,iVBORw0KGgo=">
    <link rel="stylesheet" type="text/css" href="style/login.css" />
</head>

<body>
    <div class="all">
        <div class="box">
            <div class="box-inner">
                <div class="box-title">Universal REST Simulator</div>
                <div class="box-form">
                    <form id="form1" name="form1" method="post" action="login.php">
                        <div class="label">Username</div>
                        <div class="field">
                            <input type="text" name="username" id="username" class="input-text-login"
                                autocomplete="off" />
                        </div>
                        <div class="clear"></div>
                        <div class="label">Password</div>
                        <div class="field">
                            <input type="password" name="password" id="password" class="input-text-login"
                                autocomplete="off" />
                        </div>
                        <div class="clear"></div>
                        <div class="field">
                            <div class="button-area"><input type="hidden" name="ref" id="ref"
                                    value="<?php echo htmlspecialchars(strip_tags($_SERVER['REQUEST_URI']));?>" />
                                <input type="submit" name="login" id="login" value="Login" class="login-button" />
                                <a href="generate-user.php" target="_blank">Generate User</a>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="footer">
            &copy; <a href="https://www.github.com/kamshory">Kamshory</a> 2010-<?php echo date('Y');?>. All rights
            reserved.</div>
    </div>
</body>

</html>