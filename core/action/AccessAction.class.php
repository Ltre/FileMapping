<?php

/*    只读方式：访问虚拟文件系统的文件或目录    */

class AccessAction extends ActionUtil{
	/**
	 * 通过id串识别出目录、文件
	 * 再作调度
	 */
	//http://localhost:8080/FileMap/?xxx=2
	public function analyseIdArrayToDirOrFile($id_array){
// 		echo "shabi<br>";
// 		var_dump($_SESSION['urlInfo']);
		$xml = new XMLUtil("1.0", "UTF-8");
		$root = $xml->loadGlobalAndGetRootElement();
		$node = $xml->getElementByIdArray($id_array, $root);
		if(null == $node){
			ErrorAction::cannot_find_the_node_in_vfs();
			die;
		}
		if(! strcasecmp($node->nodeName, FILE_ELEMENT_NAME_IN_GLOBAL_FILE) )
			self::accessFile($node);
		else if(! strcasecmp($node->nodeName, DIR_ELEMENT_NAME_IN_GLOBAL_FILE) || !strcasecmp($node->nodeName, ROOT_ELEMENT_NAME_IN_GLOBAL_FILE) )
			self::accessDir($node);
	}
	/**
	 * 该方法用于接收默认指令“access”
	 * 作用同上，只是接收参数的方式不同，并且需要调用以上方法
	 */
	//http://localhost:8080/FileMap/?xxx=access|4-1
	public function access($urlInfo){
		if(3 != $urlInfo['status']){
			ErrorAction::url_param_doesnt_fit_the_shell("Access->access()");
			die;
		}
		self::analyseIdArrayToDirOrFile($urlInfo['ids']);
	}
	/**
	 * 访问文件，产生下载
	 * 要下载大文件，必须在php.ini中设置：
	 * 		post_max_size = xxx
	 * 		memory_limit = xxx
	 * 注意：post_max_size的值超过memory_limit时，将以memory_limit为准
	 * 		下载文件过程中，切忌任何输出。
	 */
	public function accessFile($node){
		$attrs = $node->attributes;
		$type = $attrs->getNamedItem("type")->nodeValue;//文件类型：文件/链接[http/ftp等等]
		$filename = $attrs->getNamedItem("src")->nodeValue;//真实文件路径
		$filename = iconv('utf-8', CHARSET_IN_SERVER_FILESYSTEM, $filename);//路径字串转码，以适合服务器文件系统
		$display_name = $attrs->getNamedItem("name")->nodeValue;//显示名
		//如果“显示名”不存在，则从文件路径串中取
		if(!strcasecmp('', $display_name) || null == $display_name){
			//$display_name = FileUtil::getFileBasename($filename);
			$display_name = basename($filename);
		}
		//根据文件类型（file/link）判断下载方式
		if(! strcasecmp('file', $type)){
			if(! file_exists($filename)){
				ActionUtil::action('Error')->the_find_not_exit_in_filesystem();
				die;
			}
			FileUtil::downSuperFile($filename, $display_name);
			/* $file = fopen($filename, "rb");
			$file_size = filesize($filename);
			header("Content-type: application/octet-stream");
			header("Accept-Range : byte");
			header("Accept-Length: $file_size");
			header("Content-Disposition: attachment; filename=\"" . urlencode($display_name)."\"");
			readfile($filename);
			fclose($file);
			exit; */
		}else if(! strcasecmp('link', $type)){
 			//"暂时不能对外链文件命名下载
			//途径一：受本服务器下载限制，如文件大小
			header("Content-type: application/octet-stream");
			header("Accept-Range : byte");
			header("Content-Disposition: attachment; filename=\"" . urlencode($display_name)."\"");
			readfile($filename);	//貌似readfile仅支持GB2312、GBK之类的字符集。
			//途径二：如果实现了大文件下载，那么也就可以对从外链下载的文件命名了
// 			header("Location: $filename");	//实在解决不了问题，就直接用该语句取代
		}
	}
	/**
	 * 访问目录，返回当前目录遍历信息
	 * 用来输出返回<li>列表
	 */
	public function accessDir($node){
		$xml = new XMLUtil("1.0", "UTF-8");
		$listHTML = $xml->generatelistHTMLformSubElements($node);
		printf($listHTML);	//将使用AJAX接收该HTML片段
	}

	/**
	 * 访问目录，返回当前目录遍历信息
	 * 用来输出管理端视图右侧目录内容区的HTML片段
	 * 已注册URL指令：accessDirToContentHTML
	 * 参数要求：$urlInfo['ids']
	 */
	public function accessDirToContentHTML($urlInfo){
		$ids = $urlInfo['ids'];
		if(3 != $urlInfo['status']){
			ErrorAction::url_param_doesnt_fit_the_shell("Access->accessDirToContentHTML()");
			die;
		}
		$xml = new XMLUtil("1.0", "UTF-8");
		$root = $xml->loadGlobalAndGetRootElement();
		$node = $xml->getElementByIdArray($ids, $root);
		if(null == $node){
			//@ErrorAction::cannot_find_the_node_in_vfs();
			die;
		}
		if(strcasecmp($node->nodeName, DIR_ELEMENT_NAME_IN_GLOBAL_FILE) && strcasecmp($node->nodeName, ROOT_ELEMENT_NAME_IN_GLOBAL_FILE))
			die;
		$contentHTML = $xml->generateContentHTMLfromSubElements($node);
		printf($contentHTML);	//将使用AJAX接受该HTML片段
	}
	
	/**
	 * 根据id串生成管理端视图的路径导航，最多5层
	 * 参数：id串
	 * 返回：HTML代码片段
	 * 使用场合：AJAX更新
	 * 备注：已注册指令：updatePathNavyInAdminViewFormIdArray
	 */
	public function updatePathNavyInAdminViewFormIdArray($urlInfo){
		$ids = $urlInfo['ids'];
		$xml = new XMLUtil("1.0", "UTF-8");
		$root = $xml->loadGlobalAndGetRootElement();
		$HTML = '';
		$current_id = null;
		$len = count($ids);
		$i = 0;
		while ($len >= 0 && $i < 6){
			$current_id = array_slice($ids, 0, $len);
			$node = $xml->getElementByIdArray($current_id, $root);
			$info = $xml->getInfoFromSingleElement($node);
			if(1 == count($ids) && 0 == $ids[0]){
				printf('<li id="path_0" class="active">'.$info['name'].' <span class="divider">/</span></li>');
				return;
			}
			if(2 == $xml->elementIsFileOrDir($info['nodeName'])){
				$active_class = ' ';
				$dir_name = ' ';
				$full_id = implode('-', $current_id);
				$onclick = ' ';
				if(count($current_id) == 0)
					$full_id = "0";
				if($len == count($ids))
					$active_class = ' class="active" ';
				else
					$onclick = ' onclick="click_path_navy(this)" ';
				$dir_name = $info['name'];
				$HTML = '<li id="path_'. $full_id . '"' . $active_class . $onclick . '>' . $dir_name . ' <span class="divider">/</span></li>' . $HTML;
			}
			$len --;
			$i ++;
		}
		printf($HTML);
	}
	
	/**
	 * 定位到id串数组指定的目录及诶单，并生成展开列表至该目录的html片段
	 * 参数：$ulrInfo['ids']
	 * 输出：HTML片段，交由AJAX处理
	 * 备注：已注册指令 orientateDirFromIdAndGenerateListHTML 
	 * 【暂时不要使用该方法，因为有问题暂时无法解决】
	 */
	public function orientateDirFromIdAndGenerateListHTML($urlInfo){
		$ids = $urlInfo['ids'];
		if(3 != $urlInfo['status']){
			ActionUtil::action('Error') -> url_param_doesnt_fit_the_shell("Access->orientateDirFromIdAndGenerateListHTML()");
			die;
		}
		$xml = new XMLUtil("1.0", "UTF-8");
		$html = $xml -> orientateDirFromIdAndGenerateListHTML( $ids );
		printf($html);
	}
}




