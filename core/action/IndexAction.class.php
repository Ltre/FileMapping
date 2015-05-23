<?php
class IndexAction extends ActionUtil{
	//默认的Action方法，进入入口后，如果没有输入任何参数，则执行此处。自定义配置见 DEFAULT_URL_SHELL   @   /core/lib/base/env__.php
	public function index($urlInfo){
		self::tpl(null);	//测试Action模板
	}
	
	//测试【TestAction绑定的过滤器】的代码
	//测试链接：http://server.com/?xxx=demo
	public function demo($urlInfo){
		echo "<br>===============当前进入IndexAction模块的demo( )方法，准备测试...===============<br><br>";
		echo "开始测试TestAction : : test( )的过滤器<br>";
		$this->action('Test')->test($urlInfo);
		echo "<br>===============当前跳出IndexAction模块的demo( )方法，结束测试...===============<br><br>";
	}
}