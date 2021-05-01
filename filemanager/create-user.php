<?php
include dirname(__FILE__)."/session.php";
include_once dirname(__FILE__)."/conf.php";

include_once dirname(__FILE__)."/functions.php";

if(isset($_POST['username']) && isset($_POST['password']))
{
	// TODO You need to filter this input if you use database to store user credentials
	$uid = trim($_POST['username']);
	$pas = trim($_POST['password']);

    if(!empty($_POST['sec']) && $_POST['sec'] == $_SESSION['sescu'])
    {
        // create new user
        // Password to be used for the user
        $username = $uid;
        $password = $pas;
        
        // Encrypt password
        $encrypted_password = HTPasswd::crypt_apr1_md5($password);
        
        // Print line to be added to .htpasswd file
        file_put_contents('.htpasswd', $username . ':' . $encrypted_password);
        $_SESSION['userid'] = $username;
    }
}

if(!isset($_SESSION['userid']))
{
	include_once dirname(__FILE__)."/tool-login-form.php";
}
else
{
	header("Location: ./");
}
?>
