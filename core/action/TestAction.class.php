<?php
/**
 * 该Action被同时绑定模块过滤器和操作过滤器，作为测试对象
 */
class TestAction extends ActionUtil implements Filter{
	
	//要使test()添加的过滤器方法有效，其访问控制须设为protected。绑定的过滤器可以有：TestFilter::test(),TestFilter::test_*()
	protected function test($urlInfo){
		echo "过滤器执行完毕，{ 现在进入TestAction : : test( )的方法体 }<br>";
		echo '/readme.txt的字数约有：'.intval(filesize(APPROOT.'readme.txt')/(floatval(13)/3)).'<br>';
		require_once OTHER_TEMPLATE_DIR.'othertest.php';//测试普通模板
		echo '{ TestAction : : test( )的方法体执行结束 }<br>';
	}
	
	public function echoXMLtest(){
		header ( "Content-type: text/html;charset=utf-8" );
		$xml = new XMLUtil("1.0","UTF-8");
		$root = $xml->loadGlobalAndGetRootElement();
		$xml->ergodicElementAndGrandchildren($root);
	}
	
	public function unsetsession(){
		session_unset();
		echo "所有会话已销毁<br>";
	}
	
	public function dlfiletest(){
		header("Content-type: text/html;charset=utf-8");
		$src = iconv('utf-8', 'gb2312', 'Y:\存档区\视频\番集连载【Y盘扩展】\丧女\【2013-7】丧女【夏雪字幕】\1.flv');
		$display_name = '萨嘎.flv';
		$file = fopen($src, "rb");
		$file_size = filesize($src);//文件大小
		header("Content-type: application/octet-stream");
		header("Accept-Range : byte");
		header("Accept-Length: $file_size");
		header("Content-Disposition: attachment; filename=\"" . urlencode($display_name)."\"");
		readfile($src);
		fclose($file);
	}
	
	//实现系统内置的模块单过滤器 Filter::doFilter()。如果进入TestAction模块前仅需一个过滤器，则仅实现Filter接口即可。
	public function doFilter() {
		echo substr(__CLASS__, 0, -6)."Action模块绑定的内置过滤器执行...<br>";
	}
}



