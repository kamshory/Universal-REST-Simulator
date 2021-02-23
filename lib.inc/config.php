<?php

$appConfig = new StdClass();

$appConfig->jwtSecret = "D^&t*&Te65yjLKHiu^&67tT^FYZ";
$appConfig->jwtIssuer = "Your Company";
$appConfig->jwtAudience = "Your Client";
$appConfig->jwtNotValidBefore = 0;
$appConfig->jwtNotValidAfter = 3600;

$appConfig->jwtUserID = 1;
$appConfig->jwtUserFirstName = "Client First Name";
$appConfig->jwtUserLastName = "Client Last Name";
$appConfig->jwtUserEmail = "email@domail.tld";

?>