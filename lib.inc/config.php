<?php

/**
 * JWT configuration
 */
$appConfig = new \stdClass();

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
$useRelativePath = @getenv("USE_RELATIVE_PATH");
$wildcardURLToRequest = @getenv("WILDCARD_URL_TO_REQUEST");
$asyncEnable = @getenv("ASYNC_ENABLE");
$userAgent = @getenv("USER_AGENT");
$timeZone = @getenv("SERVER_TIME_ZONE");


$useRelativePathDefault = "false";
$asyncEnableDefault = "true";
$wildcardURLToRequestDefault = "false";
$userAgentDefault = "Universal REST Simulator";
$defaultTimeZone = "Asia/Jakarta";

if(empty($configDir))
{
    /**
     * Use default value
     */
	$configDir = dirname(dirname(__FILE__))."/config";
    putenv("CONFIG_DIR=" . $configDir);
}
if(empty($useRelativePath))
{
    /**
     * Use default value
     */
    $useRelativePath = $useRelativePathDefault;
    putenv("USE_RELATIVE_PATH=$useRelativePath");
}
if(empty($asyncEnable))
{
    /**
     * Use default value
     */
    $asyncEnable = $asyncEnableDefault;
    putenv("ASYNC_ENABLE=$asyncEnable");
}
if(empty($wildcardURLToRequest))
{
    /**
     * Use default value
     */
    $wildcardURLToRequest = $wildcardURLToRequestDefault;
    putenv("WILDCARD_URL_TO_REQUEST=$wildcardURLToRequest");
}
if(empty($userAgent))
{
    /**
     * Use default value
     */
    $userAgent = $userAgentDefault;
    putenv("USER_AGENT=$userAgent");
}
if(empty($timeZone))
{
    $timeZone = $defaultTimeZone;
    putenv("SERVER_TIME_ZONE=$defaultTimeZone");
}

define("CONFIG_DIR", $configDir);
define("USE_RELATIVE_PATH", stripos($useRelativePath, "true") !== false);
define("WILDCARD_URL_TO_REQUEST", stripos($wildcardURLToRequest, "true") !== false);
define("ASYNC_ENABLE", stripos($asyncEnable, "true") !== false);
define("SPACE_TRIMMER", " \t\r\n ");
define("EOL", "{[EOL]}");
define("USER_AGENT", $userAgent);

date_default_timezone_set($timeZone);