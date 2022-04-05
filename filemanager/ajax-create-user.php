<?php
include_once dirname(__FILE__)."/htpasswd.php";

if(isset($_POST['username']) && isset($_POST['password']))
{
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    // Encrypt password
    $encrypted_password = HTPasswd::crypt_apr1_md5($password);
            
    // Print line to be added to .htpasswd file
    echo $username . ':' . $encrypted_password;
}

?>