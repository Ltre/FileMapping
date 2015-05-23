<?php
class DirAction extends ActionUtil implements Filter{
	/**
	 * 从本地目录创建目录：dirdir-显示名-实际目录位置-类型-父目录id
	 */
	//http://localhost:8080/FileMapping/?id=dirdir|abc|E%3A%5Cdemo|real|4-5-6-7-8【WINDOWS】
	//http://localhost:8080/FileMapping/?id=dirdir|def|%2Fhome%2Fadmin%2Fdemo|real|4-5-6-7-8【UNIX/LINUX】
	protected function dirdir($urlInfo){
		$display_name = $urlInfo['params'][1];
		$localdir = $urlInfo['params'][2];
		$type = $urlInfo['params'][3];
		$parentid = $urlInfo['ids'];
		/*
		 * 开始业务代码
		 */
		$xmlutil = new XMLUtil("1.0", "UTF-8");
		//加载global文件并取得根元素节点
		$root = $xmlutil->loadGlobalAndGetRootElement();
		if(! $root){echo "加载全局配置文件失败，无法获取节点<br>";echo "@ErrorAction";die;}
		//先判断父节点id存在否，并进一步判断是目录(root和dir)还是文件(file)。节点不存在或为文件时就跳转ERROR
		$parent_node = $xmlutil -> getElementByIdArray($parentid, $root);
		if(is_null($parent_node)){
			@ErrorAction::no_such_node();
			echo "@ErrorAction";
			die;
		}else if ( false === array_search($parent_node->nodeName, array(ROOT_ELEMENT_NAME_IN_GLOBAL_FILE, DIR_ELEMENT_NAME_IN_GLOBAL_FILE)) ){
			@ErrorAction::node_not_dir();
			echo "@ErrorAction";
			die;
		}
		//检索父目录下的项目概况，确定新目录的id值，顺便带回父目录下所有节点的name属性值
		$max_node = $xmlutil->getSubElementByMaxId($parent_node);
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
		//创建新目录节点
		$new_dir_info = array(
				'element' => DIR_ELEMENT_NAME_IN_GLOBAL_FILE,
				'attributes'=>	array(
					'id'	=>	$max_node['id']+1,	////新id=最大id+1
					'name'	=>	$display_name,	//原：=basename($localdir)
					'type'	=>	$type,
					'src'	=>	$localdir,
					'items'	=>	"0"		//新建的目录下的项目数为零
				),
		);
		$new_dir = $xmlutil->createElementWithAttribute($new_dir_info);
		if(is_null($new_dir)){
			@ErrorAction::some_attr_value_without_utf8_in_xml();
			echo "@ErrorAction";
			die;
		}
		//将新目录插入到父目录中，保存到.global
		$parent_node->appendChild($new_dir);
		//插入完成后，父目录的items属性相应地加一
		$parent_node->setAttribute("items", intval($parent_node->getAttribute("items"))+1 );
		$xmlutil->saveGlobalFileWithFormat();
		//返回更新的结果，如果成功，客户端则更新对应目录。如果失败，则提示，并放弃客户端更新。
		$max_node = $xmlutil->getSubElementByMaxId($parent_node);//再次取得id最大的节点，以便通知客户端进行局部更新
		array_push($parentid, $max_node['id']);	//生成新目录的id串
		//AJAX回执代码：如果内容含有“@ErrorAction”，则说明内容有错误，客户端应该通过查找“@ErrorAction”以检查是否出错。
		$newid = implode('-', $parentid);
		printf($newid);
	}
	
	
	
	
	
	
	
	/**
	 * 创建空的虚拟目录：empdir-新目录名-父目录id
	 */
	//http://localhost:8080/Filemanager/?id=empdir|demo|.nomedia|virtual|%E5%91%B5%E5%91%B5|4-5-6-7-8
	protected function empdir($urlInfo){
		$this->dirdir($urlInfo);
	}
	/**
	 * 修改目录名：mndir-新目录名-id值
	 */
	//http://localhost:8080/Filemanager/?id=mndir|%E5%91%B5%E5%91%B5|4-5-6-7-8
	public function mndir($name, $id){
		echo "进入mndir()<br>";
		var_dump(func_get_args());
		echo "已出mndir()<br>";
	}
	/**
	 * 修改目录类型：mtdir-id值
	 */
	//http://localhost:8080/Filemanager/?id=mtdir|4-5-6-7-8
	public function mtdir($id){
		echo "进入mtdir()<br>";
		var_dump(func_get_args());
		echo "已出mtdir()<br>";
	}
	/**
	 * 删除目录：rmdir-id值
	 */
	//http://localhost:8080/FileMapping/?id=rmdir|24
	protected function rmdir($urlInfo){
		$id_array = $urlInfo['ids'];
		$dest = $this->xml->getElementByIdArray($id_array, $this->root);
		array_pop($id_array);	//转为父目录id
		$parent = $this->xml->getElementByIdArray($id_array, $this->root);
		if(is_null($dest) || is_null($parent)){
			@ErrorAction::no_such_node();
			echo "@ErrorAction";
			die;
		}
		$parent->removeChild($dest);
		$items = $parent->getAttribute("items");
		$items==0 | $items--;
		$parent->setAttribute("items", $items);
		$this->xml->saveGlobalFileWithFormat();
	}
	/**
	 * 修改目录映射：msdir-实际目录位置-id值
	 */
	//http://localhost:8080/Filemanager/?id=msdir|E%3A%5Cdemo%5C|4-5-6-7-8
	public function msdir($localdir, $id){
		echo "进入msdir()<br>";
		var_dump(func_get_args());
		echo "已出msdir()<br>";
	}
	/**
	 * 移动文件夹：mvdir-目标目录id-本体id
	 */
	//http://localhost:8080/Filemanager/?id=mvdir|1-2-3|4-5-6-7-8
	public function mvdir($destid, $id){
		echo "进入mvdir()<br>";
		var_dump(func_get_args());
		echo "已出mvdir()<br>";
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









