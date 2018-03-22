<?php
//ob_start("ob_gzhandler");
error_reporting(0);
session_start();

/* Conexão com o banco de dados */
define('DB_SERVER', 'us-cdbr-iron-east-05.cleardb.net');
define('DB_USERNAME', 'bfde2033c4398c');
define('DB_PASSWORD', '0b2028f0');
define('DB_DATABASE', 'heroku_df2ed41f0745c08');
define("BASE_URL", "https://slimrestfulapi.herokuapp.com/");
define("SITE_KEY", 'yourSecretKey');


function getDB() 
{
	$dbhost=DB_SERVER;
	$dbuser=DB_USERNAME;
	$dbpass=DB_PASSWORD;
	$dbname=DB_DATABASE;
	$dbConnection = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbConnection->exec("set names utf8");
	$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbConnection;
}
/* DATABASE CONFIGURATION END */

/* retorna o token */
function apiToken($session_uid)
{
	$key=md5(SITE_KEY.$session_uid);
	return hash('sha256', $key);
}

?>
