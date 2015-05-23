<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>管理登录页</title>
<link rel="stylesheet" href="res/bootstrap/css/bootstrap.min.css" />
<link rel="stylesheet" href="res/bootstrap/css/bootstrap-responsive.min.css" />
<script type="text/javascript" src="res/bootstrap/js/bootstrap.min.js"></script>
<style type="text/css">
form {
	height: 285px;
	width: 350px;
	margin-top: 50px;
	margin-right: auto;
	margin-bottom: 50px;
	margin-left: auto;
	border: 3px ridge #99F;
	text-align:center;
}
</style>
</head>
<body>
	<form method="post">
    	<h5><?php var_dump(ActionUtil::getTplArgs()) ?></h5><hr />
		账号：<input name="admin" type="text" /><hr/>
		密码：<input name="pwd" type="text" /><hr/>
		<input class="btn btn-inverse" align="right" type="submit" value="登录" />
		<input type="hidden" name="<?php printf(urlencode(rand(1, 32767)))?>" value="<?php printf(urlencode('adminLogin'))?>" />
	</form>
	<img src="http://localhost:8080/FileMapping/?xxx=2" />
</body>
</html>