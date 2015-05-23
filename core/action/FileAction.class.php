<?php
class FileAction extends ActionUtil{
	/**
	 * 从本地文件创建文件：filefile-本地文件路径-父目录id
	 */
	//http://localhost:8080/Filemanager/?id=filefile|E%3A%5Cdemo%5Cdemo.txt|4-5-6-7-8
	protected function filefile( $urlInfo ){
// 		echo "进入filefile()<br>";
// 		var_dump(func_get_args());
// 		echo "已出filefile()<br>";
		$display_name = $urlInfo['params'][1];
		$realpath = $urlInfo['params'][2];
		$parentid = $urlInfo['ids'];
		/*
		 * 开始业务代码
		 */
		if(! $this->root){echo "加载全局配置文件失败，无法获取节点<br>@ErrorAction";die;}
		//先判断父节点id存在否，并进一步判断是目录(root和dir)还是文件(file)。节点不存在或为文件时就跳转ERROR
		$parent_node = $this->xml->getElementByIdArray($parentid, $this->root);
		if(is_null( $parent_node )){
			ActionUtil::action('Error')->no_such_node();
			echo "@ErrorAction";
			die;
		}else if(false === array_search($parent_node->nodeName, array(ROOT_ELEMENT_NAME_IN_GLOBAL_FILE, DIR_ELEMENT_NAME_IN_GLOBAL_FILE))){
			ActionUtil::action('Error')->node_not_dir();
			echo "@ErrorAction";
			die;
		}
		//检索父目录下的项目概况，确定新目录的id值，顺便带回父目录下所有节点的name属性值
		$max_node = $this->xml->getSubElementByMaxId($parent_node);
		if(-1 == $max_node){
			@ErrorAction::certain_subElement_has_error($parentid);
			echo "@ErrorAction";
			die;
		}
		//验证显示名称：不能含有以下半角字符“\”、“/”、“:”、“*”、“?”、“双引号”、“<”、“>”、“|”、“&”。也要求客户端对“&”进行拦截
		if(Util_::hasSpecialSymbol($display_name)){
			@ErrorAction::hasSpecialSymbol();
			echo "@ErrorAction";
			die;
		}
		//检测父目录下是否存在同名的文件或目录
		if(in_array($display_name, $max_node['names'])){
			@ErrorAction::already_exist_the_sameFileOrDirNameAttribute_in_current();
			echo "@ErrorAction";
			return;
		}
		//创建新文件节点
		$new_file_info = array(
			'element'	=>	FILE_ELEMENT_NAME_IN_GLOBAL_FILE,
			'attributes'=>	array(
				'id'	=>	$max_node['id']+1,
				'name'	=>	$display_name,
				'type'	=>	'file',
				'src'	=>	$realpath,
			),
		);
		$new_file = $this->xml->createElementWithAttribute($new_file_info);
		if(is_null($new_file)){
			ActionUtil::action('Error')->some_attr_value_without_utf8_in_xml();
			echo "ErrorAction";
			die;
		}
		//将新文件插入到父目录中，保存到.global
		$parent_node->appendChild($new_file);
		$this->xml->saveGlobalFileWithFormat();
		//返回更新的结果，如果成功，客户端则更新对应目录。如果失败，则提示，并放弃客户端更新。
		$max_node = $this->xml->getSubElementByMaxId($parent_node);//再次取得id最大的节点，以便通知客户端进行局部更新
		array_push($parentid, $max_node['id']);	//生成新目录的id串
		//AJAX回执代码：如果内容含有“@ErrorAction”，则说明内容有错误，客户端应该通过查找“@ErrorAction”以检查是否出错。
		$newid = implode('-', $parentid);
		printf($newid);
	}
	/**
	 * 从链接创建文件：linkfile-新文件名-链接-父目录id
	 */
	//http://localhost:8080/Filemanager/?id=linkfile|4%E7%8B%97%E5%90%88%E4%BD%93.jpg|http://127.0.0.1/BBS/Img/tmp/1.jpg|4-5-6-7-8
	public function linkfile($name, $link, $parentid){
		echo "进入linkfile()<br>";
		var_dump(func_get_args());
		echo "已出linkfile()<br>";
	}
	/**
	 * 修改文件名：filename-新文件名-id值
	 */
	//http://localhost:8080/Filemanager/?id=filename|4%E7%8B%97%E5%90%88%E4%BD%93.jpg|4-5-6-7-8
	public function filename($name, $id){
		echo "进入filename()<br>";
		var_dump(func_get_args());
		echo "已出filename()<br>";
	}
	/**
	 * 删除文件【仅从虚拟目录删除】：rmfile-id值
	 */
	//http://localhost:8080/Filemanager/?id=rmfile|4-5-6-7-8
	public function rmfile($id){
		echo "进入rmfile()<br>";
		var_dump(func_get_args());
		echo "已出rmfile()<br>";
	}
	/**
	 * 移动文件：mvfile-目标目录id-本体id
	 */
	//http://localhost:8080/Filemanager/?id=mvfile|1-2-3|4-5-6-7-8
	public function mvfile($destid, $id){
		echo "进入mvfile()<br>";
		var_dump(func_get_args());
		echo "已出mvfile()<br>";
	}
	
	
	/*
	 * 本模块过滤器
	* URL参数状态为3时，才可进入本模块
	*/
	public function doFilter() {
		$urlInfo = $_SESSION['urlInfo'];
		if(3 != $urlInfo['status']){
			@ErrorAction::url_param_doesnt_fit_the_shell(" DirAction ");
			echo "@ErrorAction";
			die;
		}
	}
	public function __construct(){
		$this->xml = new XMLUtil("1.0", "UTF-8");
		$this->root = $this->xml->loadGlobalAndGetRootElement();
	}
	//加载整个GLOBAL配置文件的DOM对象
	protected $xml;
	//GLOBAL配置文件加载后得到的根元素DOMNode
	protected $root;
}



