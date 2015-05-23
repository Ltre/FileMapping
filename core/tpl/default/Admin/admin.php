<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>管理端顶级视图</title>

<script type="text/javascript" src="<?php printf(APPROOT.JQUERY_LIB_PATH); ?>"></script>
<script type="text/javascript" src="res/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="res/js/util.js"></script>
<script type="text/javascript" src="res/js/Admin-admin.js"></script>
<link href="res/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="res/bootstrap/js/bootstrap-popover.js"></script>
<link href="res/css/Admin-admin.css" rel="stylesheet" type="text/css" />

</head>
<body bgcolor="#f9f9f9">
<div id="container">
	<div id="menus">
          <div class="btn-group">
            <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
              管理员
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="#">查看</a></li>
              <li><a href="#">修改</a></li>
              <li><a href="#">添加</a></li>
              <li><a href="#">销户</a></li>
              <li class="divider"></li>
              <li><a href="./?h65k4h654i6h5u6=adminLogout">退出</a></li>
            </ul>
          </div><!-- 管理员 -->
          <div class="btn-group">
            <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
              系统限定
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="#">全局速度</a></li>
              <li><a href="#">单线速度</a></li>
              <li><a href="#">全局线程数</a></li>
              <li><a href="#">单链线程数</a></li>
              <li><a href="#">IP限定</a></li>
              <li class="divider"></li>
              <li><a href="#">启用限定</a></li>
            </ul>
          </div><!-- 系统限定 -->
          <div class="btn-group">
            <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
              导入
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="#">从全局文件系统配置文件(.global)</a></li>
              <li><a href="#">从局部文件系统配置文件(.part)</a></li>
              <li><a href="#">从管理端校验文件(.verify)</a></li>
            </ul>
          </div><!-- 导入 -->
          <div class="btn-group">
            <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
              导出
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="#">导出全局文件系统配置文件(.global)</a></li>
              <li><a href="#">导出管理端校验文件(.verify)</a></li>
            </ul>
          </div><!-- 导出 -->
		  <input type="text" width="600px" height="10px" />
          <button id="copy_link" class="btn btn-info" type="button">复制链接</button>
          <button id="open_link" class="btn btn-success" type="button" onclick="window.open(document.getElementById('menus').getElementsByTagName('input').item(0).value);">打开链接</button>
    </div><!-- #menus -->
    <div id="url">
		<ul class="breadcrumb">
<!--  			<li class="active">根目录 <span class="divider">/</span></li>
  			<li>第一层的名称 <span class="divider">/</span></li>
  			<li>第二层的名称 <span class="divider">/</span></li>
  			<li>第三层的名称 <span class="divider">/</span></li>
  			<li>第四的名称层 <span class="divider">/</span></li>
  			<li>第五层的名称</li>-->
            <?php 
            $args = ActionUtil::getTplArgs();
            printf($args['pathnavyHTML']);
            ?>
		</ul><!-- . breadcrumb -->
    </div><!-- #url -->
    <div id="content">
    	<div id="left">
            <br />
            &nbsp;&nbsp;<a class="btn btn-info" onclick="click_root_button()" onContextMenu="alert('禁止右键！');return false;">根目录</a>
            <?php
			//显示根目录下第一层的所有项目
			printf($args['listHTML']);
			?>
        </div><!-- #left -->
        <div id="right">
            <div class="navbar" style="position: static;">
              <div class="navbar-inner">
                <div class="container">
                  <a class="btn btn-navbar" data-toggle="collapse" data-target=".navbar-inverse-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                  </a>
                  <div class="nav-collapse collapse navbar-inverse-collapse">
                    <ul class="nav">
                      <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">新建 <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                          <li><a href="#filefile" data-toggle="modal"> 从文件新建 </a></li>
                          <li><a href="#linkfile" data-toggle="modal"> 从链接新建 </a></li>
                          <li class="divider"></li>
                          <li><a href="#dirdir" data-toggle="modal"> 从目录新建 </a></li>
                          <li><a href="#empdir" data-toggle="modal"> 虚拟目录 </a></li>
                        </ul>
                      </li>
                    </ul>
                    <form class="navbar-search pull-right" action="">
                      <input type="text" class="search-query span2" placeholder="Search">
                    </form>
                    <ul class="nav pull-right">
                      <li class="divider-vertical"></li>
                      <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">筛选 <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                          <li><a href="#">按文件</a></li>
                          <li><a href="#">按目录</a></li>
                          <li class="divider"></li>
                          <li class="active"><a href="#">全&nbsp;&nbsp;&nbsp;部</a></li>
                        </ul>
                      </li>
                    </ul>
                  </div><!-- /.nav-collapse -->
                </div>
              </div><!-- /navbar-inner -->
            </div><!-- /navbar -->
            <div id="right_content" onclick="click_blank_in_right_content(this)" onContextMenu="return context_blank_in_right_content(this)">
                <?php 
                printf($args['contentHTML']);
                ?>
            </div>
        </div><!-- #right -->
    </div><!-- #content -->
    <div id="session"></div><!-- #session -->
    <div id="status"></div><!-- #status -->
</div><!-- #container -->


<!--------------------超级分割线----------------------->


<!-- #error_action 专用对话框 -->
<div id="error_action" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="erroraction_title" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="erroraction_title">错误信息</h3>
  </div>
  <div class="modal-body">
	<span id="erroraction_msg"></span>
  </div>
  <div class="modal-footer">
  	<button class="btn btn-primary" data-dismiss="modal" aria-hidden="true" style="float:right;">知道了</button>
  </div>
</div><!-- #error_action -->

<!-- 确定操作的询问框 #are_you_sure -->
<div id="are_you_sure" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="are_you_sure_title" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="are_you_sure_title">操作名</h3>
  </div>
  <div class="modal-body">
	<span>你确定？</span>
  </div>
  <div class="modal-footer">
  	<button class="btn btn-primary" data-dismiss="modal" aria-hidden="true" style="float:right;">确定</button>
    <button class="btn" data-dismiss="modal" aria-hidden="true" style="float:right;">取消</button>
  </div>
</div><!-- #are_you_sure -->

<!-- #filefile 从实际文件添加文件 -->
<div id="filefile" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="filefile_title" aria-hidden="true">
	<div class="modal-header">
    	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="filefile_title">从实际文件新建文件</h3>
    </div>
    <div class="modal-body">
    	<form onsubmit="return false">
        	<input type="text" placeholder="显示名称" name="filefile_displayname" id="filefile_displayname"/>
            <input type="text" placeholder="在服务器文件系统对应的路径，如：E:\abc.txt" name="filefile_realdir" id="filefile_realdir"/>
        </form>
    </div>
	<div class="modal-footer">
  		<button class="btn btn-primary" data-dismiss="modal" aria-hidden="true" style="float:right;" onclick="click_filefile()">确定</button>
    	<button class="btn" data-dismiss="modal" aria-hidden="true" style="float:right;">关闭</button>
	</div>
</div><!-- #filefile -->

<!-- #dirdir 从实际目录添加目录 -->
<div id="dirdir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="dirdir_title" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="dirdir_title">从实际路径新建目录</h3>
  </div>
  <div class="modal-body">
    <form onsubmit="return false">
    	<input type="hidden" name="current_dir_id" id="current_dir_id" value="0" /><!-- 当前目录的id串 -->
		<input placeholder="新目录的显示名，如：abc" type="text" name="dirdir_newname" id="dirdir_newname"/><!-- 新目录名 -->
        <input type="text" placeholder="在服务器文件系统对应的路径，如：E:\ABC\" name="dirdir_realdir" id="dirdir_realdir"/><!-- 对应真实目录 --><!-- 如何限定选择目录？ -->   
    </form>
  </div>
  <div class="modal-footer">
  	<button class="btn btn-primary" data-dismiss="modal" aria-hidden="true" style="float:right;" onclick="click_createRealDir()">确定</button>
    <button class="btn" data-dismiss="modal" aria-hidden="true" style="float:right;">关闭</button>
  </div>
</div><!-- #dirdir -->

<!-- #empdir 新建虚拟目录 -->
<div id="empdir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="empdir_title" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="empdir_title">新建虚拟目录</h3>
  </div>
  <div class="modal-body">
    <form onsubmit="return false">
		<input placeholder="新目录的显示名，如：abc" type="text" name="empdir_newname" id="empdir_newname"/><!-- 新目录名 -->  
    </form>
  </div>
  <div class="modal-footer">
  	<button class="btn btn-primary" data-dismiss="modal" aria-hidden="true" style="float:right;" onclick="click_createVirtualDir()">确定</button>
    <button class="btn" data-dismiss="modal" aria-hidden="true" style="float:right;">关闭</button>
  </div>
</div><!-- #empdir -->

<!-- #dircontext_in_right_content区域文件或目录的右键菜单 -->
<ul id="dircontext_in_right_content" class="dropdown-menu">
    <li><a href="#">重命名</a></li>
    <li><a href="#">复制链接</a></li>
    <li><a href="#" onclick="return click_rmdir()">移除目录</a></li>
    <li><a href="#">修改目录映射</a></li>
    <li class="dropdown-submenu">
        <a href="#">更改类型</a>
        <ul class="dropdown-menu">
            <li><a href="#">虚拟目录</a></li>
            <li><a href="#">真实目录</a></li>
        </ul>
    </li>
    <li><a href="#">属性</a></li>
    <li><a onclick="$('#dircontext_in_right_content').hide();" href="#">取消</a></li>
</ul><!-- #dircontext_in_right_content -->


<!-- #filecontext_in_right_content区域文件或目录的右键菜单 -->
<ul id="filecontext_in_right_content" class="dropdown-menu">
    <li><a href="#">重命名</a></li>
    <li><a href="#">复制链接</a></li>
    <li><a href="#">移除文件</a></li>
    <li><a href="#">修改文件映射</a></li>
    <li><a href="#">属性</a></li>
    <li><a onclick="$('#filecontext_in_right_content').hide();" href="#">取消</a></li>
</ul> <!-- #filecontext_in_right_content -->

</body>
</html>