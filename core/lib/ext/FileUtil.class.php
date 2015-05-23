<?php

/**
 * 这里代码暂时用不到，先留着
 * @author worm
 *
 */

class FileUtil {
	/**
	 * 取得文件去除前缀路径的名称（基本名称）
	 * 不使用系统自带的basename($f)的理由：不能处理中文名的文件
	 */
	public static function getFileBasename($filename){
		return array_pop(explode(FILESYSTEM_SEPARATE, $filename));
	}
	
	/**
	 * 将键值对写入verify文件
	 * 格式：键名=>键值
	 * 参数要求：verify文件路径，键值对。
	 */
	public static function writeAttrToVerifyFile ($verifypath, $attr){
		$fp = fopen($verifypath, 'ab+');
// 		fwrite($fp, sha1($attr)."\r\n");
		fwrite($fp, CryptUtil::simpleDilatationAndReversal( $attr ) . "\r\n\r\n\r\n");
		fclose($fp);
	}
	
	/**
	 * 从verify文件寻找匹配的键值对。
	 * 如果有匹配的，则返回true，否则返回false
	 * 参数：verify文件路径，目标键值对（键=>值）
	 */	
	public static function hasMatchedAttrInVerifyFile ($verifypath, $attr){
		if( ! file_exists($verifypath) ){
			@ErrorAction::cannot_find_verify_file($verifypath);
			die;
		}
		$fp = fopen($verifypath, 'rb+');
		while (! feof($fp)){
			if( Util_::isSpaceString ( $line=fgets($fp) ) )
				continue;
// 			if(! strcasecmp(trim($line, "\r\n"), sha1($attr))){
// 				return true;
			if(! strcasecmp(CryptUtil::de_simpleDilatationAndReversal(trim($line, "\r\n")), $attr )){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @正使用中
	 * 下载超大文件
	 * @param string $sourceFile 源文件路径名，如E:\abc.txt
	 * @param string $outFile 输出文件名，如abc.txt。可空。
	 */
	public static function downSuperFile($sourceFile, $outFile = null){
	
		$outFile = $outFile ? $outFile : basename($sourceFile); //下载保存到客户端的文件名
		$file_extension = strtolower(substr(strrchr($sourceFile, "."), 1)); //获取文件扩展名
		//if (!preg_match("/[tmp|txt|rar|pdf|doc]/", $file_extension))exit ("非法资源下载");
		//检测文件是否存在
		if (!is_file($sourceFile)) {
			die("<b>文件不存在!</b>");
		}
		$len = filesize($sourceFile); //获取文件大小
		$filename = basename($sourceFile); //获取文件名字
		$outFile_extension = strtolower(substr(strrchr($outFile, "."), 1)); //获取文件扩展名
		//根据扩展名 指出输出浏览器格式
		switch ($outFile_extension) {
			case "exe" :
				$ctype = "application/octet-stream";
				break;
			case "zip" :
				$ctype = "application/zip";
				break;
			case "mp3" :
				$ctype = "audio/mpeg";
				break;
			case "mpg" :
				$ctype = "video/mpeg";
				break;
			case "avi" :
				$ctype = "video/x-msvideo";
				break;
			default :
				$ctype = "application/force-download";
		}
		//Begin writing headers
		header("Cache-Control:");
		header("Cache-Control: public");
		 
		//设置输出浏览器格式
		header("Content-Type: $ctype");
		header("Content-Disposition: attachment; filename=" . $outFile);
		header("Accept-Ranges: bytes");
		$size = filesize($sourceFile);
		//如果有$_SERVER['HTTP_RANGE']参数
		$range = null;
		if (isset ($_SERVER['HTTP_RANGE'])) {
			/*Range头域 　　Range头域可以请求实体的一个或者多个子范围。
			 例如，
			表示头500个字节：bytes=0-499
			表示第二个500字节：bytes=500-999
			表示最后500个字节：bytes=-500
			表示500字节以后的范围：bytes=500- 　　
			第一个和最后一个字节：bytes=0-0,-1 　　
			同时指定几个范围：bytes=500-600,601-999 　　
			但是服务器可以忽略此请求头，如果无条件GET包含Range请求头，响应会以状态码206（PartialContent）返回而不是以200 （OK）。
			*/
			// 断点后再次连接 $_SERVER['HTTP_RANGE'] 的值 bytes=4390912-
			list ($a, $range) = explode("=", $_SERVER['HTTP_RANGE']);
			//if yes, download missing part
			str_replace($range, "-", $range); //这句干什么的呢。。。。
			$size2 = $size -1; //文件总字节数
			$new_length = $size2 - $range; //获取下次下载的长度
			header("HTTP/1.1 206 Partial Content");
			header("Content-Length: $new_length"); //输入总长
	    header("Content-Range: bytes $range$size2/$size"); //Content-Range: bytes 4908618-4988927/4988928   95%的时候
		} else {
		//第一次连接
		$size2 = $size -1;
		header("Content-Range: bytes 0-$size2/$size"); //Content-Range: bytes 0-4988927/4988928
		header("Content-Length: " . $size); //输出总长
		}
		//打开文件
		$fp = fopen("$sourceFile", "rb");
		//设置指针位置
		fseek($fp, $range);
		//虚幻输出
		while (!feof($fp)) {
			//设置文件最长执行时间
			set_time_limit(0);
			print (fread($fp, 1024 * 8)); //输出文件
			flush(); //输出缓冲
			ob_flush();
		}
		fclose($fp);
		exit ();
		
	}	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//==============================以下属于测试代码，并未被正式使用========================//
	
	
	/**
	 * 用php下载大文件
	 * 200M以上的文件，需加set_time_limit(0)
	 * 参数：$url-文件路径，$filename-文件基本名称
	 */
	public static function download($url, $filename){
		// 获得文件大小, 防止超过2G的文件, 用sprintf来读
		$filesize = sprintf("%u", filesize($url));
		if (!$filesize)
			return;
		header("Content-type:application/octet-stream\n"); //application/octet-stream
		header("Content-type:unknown/unknown;");
		header("Content-disposition: attachment; filename=\"".urlencode($filename)."\"");
		header('Content-transfer-encoding: binary');
// 			die($filesize);
		// 当有偏移量的时候，采用206的断点续传头
		if (@$range = getenv('HTTP_RANGE')){
			$range = explode('=', $range);
			$range = $range[1];
			header("HTTP/1.1 206 Partial Content");
			header("Date: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Last-Modified: ".gmdate("D, d M Y H:i:s", filemtime($url))." GMT");
			header("Accept-Ranges: bytes");
			header("Content-Length:".($filesize - $range));
			header("Content-Range: bytes ".$range.($filesize-1)."/".$filesize);
			header("Connection: close"."\n\n");
		}else{
			header("Content-Length:".$filesize."\n\n");
			$range = 0;
		}
// 		self::loadFile($url);
	}
	private static function loadFile($filename, $retbytes = true) {
		$buffer = '';
		$cnt =0;
		$handle = fopen($filename, 'rb');
		if ($handle === false) {
			return false;
		}
		while (!feof($handle)) {
			$buffer = fread($handle, 1024*1024);
			echo $buffer;
			ob_flush();
			flush();
			if ($retbytes) {
				$cnt += strlen($buffer);
			}
		}
		$status = fclose($handle);
		if ($retbytes && $status) {
			return $cnt; // return num. bytes delivered like readfile() does.
		}
		return $status;
	}
	
	//测试六：
	public static function downloadLargeFile($src, $display_name){
		$sourceFile = $src;
		$outFile = "用户订单.xls"; //下载保存到客户端的文件名
		$file_extension = strtolower(substr(strrchr($sourceFile, "."), 1)); //获取文件扩展名
		//echo $sourceFile;
		if (!ereg("[tmp|txt|rar|pdf|doc]", $file_extension))
			exit ("非法资源下载");
		
		//检测文件是否存在
		if (!is_file($sourceFile)) { die("<b>404 File not found!</b>"); }
		
		$len = filesize($sourceFile); //获取文件大小
		$filename = $display_name; //获取文件名字
		$outFile_extension = strtolower(substr(strrchr($outFile, "."), 1)); //获取文件扩展名
		//根据扩展名 指出输出浏览器格式
		switch ($outFile_extension) {
			case "exe" :
				$ctype = "application/octet-stream"; break;
			case "zip" :
		
				$ctype = "application/zip"; break;
			case "mp3" :
		
				$ctype = "audio/mpeg"; break;
			case "mpg" :
		
				$ctype = "video/mpeg"; break;
			case "avi" :
		
				$ctype = "video/x-msvideo"; break;
			default :
		
// 				$ctype = "application/force-download"; 
				$ctype = "application/octet-stream";
		}
		//Begin writing headers
		header("Cache-Control:");
		header("Cache-Control: public");
		
		//设置输出浏览器格式
		header("Content-Type: $ctype");
		
		header("Content-Disposition: attachment; filename=\"" . urlencode($display_name) ."\"");
		header("Accept-Ranges: bytes");
		$size = filesize($sourceFile);
		
		//如果有$_SERVER['HTTP_RANGE']参数
		if (isset ($_SERVER['HTTP_RANGE'])) {
			/*Range头域 Range头域可以请求实体的一个或者多个子范围。 例如，
		
			表示头500个字节：bytes=0-499 表示第二个500字节：bytes=500-999
		
			百度文库客户端，免财富值下载文档1/2表示最后500个字节：bytes=-500
		
			表示500字节以后的范围：bytes=500- 第一个和最后一个字节：bytes=0-0,-1
		
			同时指定几个范围：bytes=500-600,601-999
		
			但是可以忽略此请求头，如果无条件GET包含Range请求头，响应会以状态码206（PartialContent）返回而不是以200 （OK）。
			*/
		
			// 断点后再次连接 $_SERVER['HTTP_RANGE'] 的值bytes=4390912-
			list ($a, $range) = explode("=", $_SERVER['HTTP_RANGE']);
		
			//if yes, download missing part
			str_replace($range, "-", $range); //这句干什么的呢。。。。
			$size2 = $size -1; //文件总字节数
			$new_length = $size2 - $range; //获取下次下载的长度
			header("HTTP/1.1 206 Partial Content");
		
			header("Content-Length: $new_length"); //输入总长
			header("Content-Range: bytes $range$size2/$size"); //Content-Range: bytes 4908618-4988927/4988928 95%的时候
		} else {
		
			//第一次连接 $size2 = $size -1;
		
			header("Content-Range: bytes 0-$size2/$size"); //Content-Range: bytes 0-4988927/4988928
			header("Content-Length: " . $size); //输出总长
		}
		//打开文件
		$fp = fopen("$sourceFile", "rb");
		//设置指针位置
		fseek($fp, $range);
		//虚幻输出
		while (!feof($fp)) {
		
			//设置文件最长执行时间
			set_time_limit(0);
		
			print (fread($fp, 1024 * 8)); //输出文件
			flush(); //输出缓冲
			ob_flush();
		}
		fclose($fp);
		exit ();
	}
	
	// 测试五：服务器文件路径，下载文件名字(默认为服务器文件名)，是否许可用户下载方式(默认可选)，速度限制(默认自动)，文件类型(默认所有)
	public static function downFile($fileName, $fancyName = '', $forceDownload = true, $speedLimit = 0, $contentType = '') {
		if (!is_readable($fileName))
		{
			header("HTTP/1.1 404 Not Found");
			return false;
		}
	
		$fileStat = stat($fileName);
		$lastModified = $fileStat['mtime'];
			
		$md5 = md5($fileStat['mtime'] .'='. $fileStat['ino'] .'='. $fileStat['size']);
		$etag = '"' . $md5 . '-' . crc32($md5) . '"';
			
		header('Last-Modified: ' . gmdate("D, d M Y H:i:s", $lastModified) . ' GMT');
		header("ETag: $etag");
			
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModified)
		{
			header("HTTP/1.1 304 Not Modified");
			return true;
		}
	
		if (isset($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) < $lastModified)
		{
			header("HTTP/1.1 304 Not Modified");
			return true;
		}
			
		if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag)
		{
			header("HTTP/1.1 304 Not Modified");
			return true;
		}
	
		if ($fancyName == '')
		{
			$fancyName = basename($fileName);
		}
			
		if ($contentType == '')
		{
			$contentType = 'application/octet-stream';
		}
	
		$fileSize = $fileStat['size'];
	
		$contentLength = $fileSize;
		$isPartial = false;
	
		if (isset($_SERVER['HTTP_RANGE']))
		{
			if (preg_match('/^bytes=(d*)-(d*)$/', $_SERVER['HTTP_RANGE'], $matches))
			{
				$startPos = $matches[1];
				$endPos = $matches[2];
	
				if ($startPos == '' && $endPos == '')
				{
					return false;
				}
					
				if ($startPos == '')
				{
					$startPos = $fileSize - $endPos;
					$endPos = $fileSize - 1;
				}
				else if ($endPos == '')
				{
					$endPos = $fileSize - 1;
				}
					
				$startPos = $startPos < 0 ? 0 : $startPos;
				$endPos = $endPos > $fileSize - 1 ? $fileSize - 1 : $endPos;
					
				$length = $endPos - $startPos + 1;
					
				if ($length < 0)
				{
					return false;
				}
	
				$contentLength = $length;
				$isPartial = true;
			}
		}
	
		// send headers
		if ($isPartial)
		{
			header('HTTP/1.1 206 Partial Content');
			header("Content-Range: bytes $startPos-$endPos/$fileSize");
	
		}
		else
		{
			header("HTTP/1.1 200 OK");
			$startPos = 0;
			$endPos = $contentLength - 1;
		}
	
		header('Pragma: cache');
		header('Cache-Control: public, must-revalidate, max-age=0');
		header('Accept-Ranges: bytes');
		header('Content-type: ' . $contentType);
		header('Content-Length: ' . $contentLength);
	
		if ($forceDownload)
		{
			header('Content-Disposition: attachment; filename="' . rawurlencode($fancyName). '"');
		}
	
		header("Content-Transfer-Encoding: binary");
			
		$bufferSize = 2048;
	
		if ($speedLimit != 0)
		{
			$packetTime = floor($bufferSize * 1000000 / $speedLimit);
		}
	
		$bytesSent = 0;
		$fp = fopen($fileName, "rb");
		fseek($fp, $startPos);
		while ($bytesSent < $contentLength && !feof($fp) && connection_status() == 0 )
		{
			if ($speedLimit != 0)
			{
				list($usec, $sec) = explode(" ", microtime());
				$outputTimeStart = ((float)$usec + (float)$sec);
			}
				
			$readBufferSize = $contentLength - $bytesSent < $bufferSize ? $contentLength - $bytesSent : $bufferSize;
			$buffer = fread($fp, $readBufferSize);
	
			echo $buffer;
				
			ob_flush();
			flush();
	
			$bytesSent += $readBufferSize;
	
			if ($speedLimit != 0)
			{
				list($usec, $sec) = explode(" ", microtime());
				$outputTimeEnd = ((float)$usec + (float)$sec);
					
				$useTime = ((float) $outputTimeEnd - (float) $outputTimeStart) * 1000000;
				$sleepTime = round($packetTime - $useTime);
				if ($sleepTime > 0)
				{
					usleep($sleepTime);
				}
			}
		}
		return true;
	}
	
}
?>