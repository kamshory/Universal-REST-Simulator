<?php

/**
 * JWT configuration
 */
$appConfig = new StdClass();

$appConfig->jwtSecret = "Zy&t*&ytwqytqwy72Fe^&67tT^F";
$appConfig->jwtIssuer = "Your Company";
$appConfig->jwtAudience = "Your Company";
$appConfig->jwtNotValidBefore = 0;
$appConfig->jwtNotValidAfter = 3600;
$appConfig->jwtUserID = 1;
$appConfig->jwtUserFirstName = "Client First Name";
$appConfig->jwtUserLastName = "Client Last Name";
$appConfig->jwtUserEmail = "email@domain.tld";

/**
 * Path of configuration files
 */
$configDir = @getenv("CONFIG_DIR");
$useAbsolutePath = @getenv("USE_RELATIVE_PATH");
$wildcardURLToRequest = @getenv("WILDCARD_URL_TO_REQUEST");

if(empty($configDir))
{
    /**
     * Use default value
     */
	$configDir = dirname(dirname(__FILE__))."/config";
    putenv("CONFIG_DIR=" . $configDir);
}
if(empty($useAbsolutePath))
{
    /**
     * Use default value
     */
    $useAbsolutePath = "false";
    putenv("USE_RELATIVE_PATH=$useAbsolutePath");
}
if(empty($wildcardURLToRequest))
{
    /**
     * Use default value
     */
    $wildcardURLToRequest = "false";
    putenv("WILDCARD_URL_TO_REQUEST=$wildcardURLToRequest");
}

define("CONFIG_DIR", $configDir);
define("USE_RELATIVE_PATH", stripos($useAbsolutePath, "true") !== false);
define("WILDCARD_URL_TO_REQUEST", stripos($wildcardURLToRequest, "true") !== false);

?>