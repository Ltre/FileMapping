<?php
/**
 * 将形如 http://localhsot:8080/FileManager/admin/?xxx=xxx&xxx=xxx...&xxx=xxx
 * 	重定向到 http://localhsot:8080/FileManager/admin.php?xxx=xxx&xxx=xxx...&xxx=xxx
 * 如果仅输入 http://localhsot:8080/FileManager/admin 将会重定向到 http://localhsot:8080/FileManager/?xxxxx=admin
 */
defined('APPROOT') or define('APPROOT','');	//添加路径纠错支持
foreach (explode('/', $_SERVER['REQUEST_URI']) as $param);
if($param=='')
	$param = '?a=admin';
header("Location: ../$param");


