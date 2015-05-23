<?php
class XMLUtil extends DOMDocument {
	// 非零时，eogodicInnerOfElement()将处于递归状态
	private $flag_ergodic = 0;
	// XML数据文本存储点
	private $xml_str = '';
	// VFS全局配置文件保存点【便于切换保存：主GLOBAL/备用GLOBAL】
	private $global_file = VFS_GLOBAL_SETUPFILE_PATH;
	/**
	 * 创建可以含有属性的元素节点
	 * 参数：由元素名称、属性MAP组成的数组。
	 * 如下：
	 * array(
	 * 		'element' => '元素名',
	 * 		'attributes' => array (
	 * 			'属性名1' => '属性值1',
	 * 			'属性名2' => '属性值2',
	 * 					... ...
	 * 			'属性名n' => '属性值n'
	 * 		)
	 * )
	 * 返回：DOMElement
	 * 创建失败返回null.
	 */
	public function createElementWithAttribute($elementInfo){
		$element = $this->createElement($elementInfo['element']);
		foreach ($elementInfo['attributes'] as $name=>$value){
			if(! Util_::check_utf8($value))
				return null;	//属性值非UTF-8【检测的第一道关卡】
			if(! strcasecmp('utf-8', mb_detect_encoding($value, array('UTF-8'))))
				$element->setAttribute($name, $value);
			else
				return null;	//如果属性值非UTF-8编码【检测的第二道关卡】
		}
		return $element;
	}
	
	/**
	 * 判断节点是文件还是目录
	 * 参数：元素名
	 * 返回：1——文件；2——目录
	 */
	public function elementIsFileOrDir( $n ){
		if(!strcasecmp(ROOT_ELEMENT_NAME_IN_GLOBAL_FILE, $n) || !strcasecmp(DIR_ELEMENT_NAME_IN_GLOBAL_FILE, $n))
			return 2;
		else if(!strcasecmp(FILE_ELEMENT_NAME_IN_GLOBAL_FILE, $n))
			return 1;
	}
	
	/**
	 * 添加元素节点，要求name属性不能与当前目录下其他元素的相同
	 * 参数：DOMNode
	 * 注：暂时用不到，后续功能再根据需要添加
	 */
	public function appendElementInDistinctName($domNode){
	
	}
	
	
	// 输出某标签内的内容，不包含该标签的本身和属性
	private function ergodicInnerOfElement($domNode) {
		foreach ( $domNode->childNodes as $index => $node ) {
			if (XML_ELEMENT_NODE == $node->nodeType) {
				for($i=0;$i<=$this->flag_ergodic;$i++)	//标签缩进
					Util_::echo8spaces ();
				echo "&lt;" . $node->nodeName . "&nbsp;";
				$this->ergodicAttributesOfElement ( $node ); // 遍历属性
				if ($this->hasElements ( $node ) == 1) {
					$this->flag_ergodic ++;
					echo "&gt;<br/>";
					$this->ergodicInnerOfElement ( $node );
					$this->flag_ergodic --;
					for($i=0;$i<=$this->flag_ergodic;$i++)	//标签缩进
						Util_::echo8spaces ();
					echo "&lt;/" . $node->nodeName . "&gt;<br/>";
				} else {
					echo "/&gt;<br/>";
				}
			}
		}
	}
	// 输出某个标签的所有属性
	private function ergodicAttributesOfElement($domNode) {
		foreach ( $domNode->attributes as $attrName => $attrNode ) {
			echo "$attrName=\"" . $attrNode->value . "\"&nbsp;";
		}
	}
	
	/**
	 * 检测是否含有标签节点
	 * 返回：1——有；0——无。
	 */
	public function hasElements($domNode) {
		$flag = 0;
		foreach ( $domNode->childNodes as $node ) {
			if (XML_ELEMENT_NODE == $node->nodeType) {
				$flag = 1;
				break;
			}
		}
		return $flag;
	}
	/**
	 * 输出某标签本身及其全部子孙标签。
	 * 参数要求：DOMNode类型
	 */
	public function ergodicElementAndGrandchildren($domNode) {
		echo "&lt;" . $domNode->nodeName . "&nbsp;";
		// 本标签属性
		$this->ergodicAttributesOfElement ( $domNode );
		echo "&gt;<br/>";
		// 遍历本标签内部的节点
		$this->ergodicInnerOfElement ( $domNode );
		echo "&lt;/" . $domNode->nodeName . "&gt;<br/>";
	}
	
	//根据修正的id串定位节点，返回DOMNode类型【测试通过】。要求id参数串每项都不能为零。
	private function findElementById($domNode, $id_array){
		if(count($id_array)==0)
			return $domNode;
		$id = $id_array[0];//取第一个id
		$id_array = array_slice($id_array, 1);//删除第一个id
		foreach ( $domNode->childNodes as $index=>$node){
			if( XML_ELEMENT_NODE == $node->nodeType ){
				if($id == $node->attributes->getNamedItem("id")->nodeValue){
					return $this->findElementById($node, $id_array);
				}
			}
		}
		return null;	//如果此处被执行，则说明没有对应的节点
	}
	
	
	/**
	 * 根据修正的id串【不含除了第一项以外的0项】，在某节点下（一般是根元素）定位节点
	 * 参数要求：id数组，DOMNode
	 * 存在则返回DOMNode，否则返回null
	 */
	public function getElementByIdArray($id_array, $domNode){
		if(count($id_array)==0 || 1==count($id_array)&&0==$id_array[0])
			return $domNode;
		$id = $id_array[0];//取第一个id项
		$id_array = array_slice($id_array, 1);//删除第一个id
		foreach ( $domNode->childNodes as $index=>$node){
			if( XML_ELEMENT_NODE == $node->nodeType ){
				if($id == $node->attributes->getNamedItem("id")->nodeValue){
					return $this->getElementByIdArray($id_array, $node);
				}
			}
		}
		return null;	//如果此处被执行，则说明没有对应的节点
	}
	
	/**
	 * 获取某标签下的具有最大id值的子标签
	 * 参数：DOMNode
	 * 返回：最大id值和对应节点的数组（顺便把当前目录下的所有元素name值带回）。
	 * 如果返回的id为0，则说明目录是空的
	 * 如果不返回DOMNode而是返回数字 -1 ，则说明该标签下的某个子标签有问题。
	 */
	public function getSubElementByMaxId( $domNode ){
		$id = 0;
		$dest_node = null;
		$names_of_all_elements = array();
		foreach ( $domNode->childNodes as $index=>$node){
			if( XML_ELEMENT_NODE == $node->nodeType ){
// 				die;//调试
				if(null == $node->attributes->getNamedItem("name")){
					$node->setAttribute("name", NAME_VALUE_PREFIX_OF_NODE_ATTR.date('Y-m-d_H：i：s'));
				}
				if(null == $node->attributes->getNamedItem("id")){
					return -1;
				}
				$names_of_all_elements[] = $node->attributes->getNamedItem("name")->nodeValue;
				$id = intval($node->attributes->getNamedItem("id")->nodeValue);
				$dest_node = $node;
			}
		}
		return array('id'=>$id, 'node'=>$dest_node, 'names'=>$names_of_all_elements);
	}
	
	/**
	 * 获取父元素下所有子元素（并非子孙元素）。
	 * 参数：父元素的DOMNode
	 * 返回：子元素的详细信息数组，详见返回值array结构。
	 */
	public function getSubElementsByParentNode( $domNode ){
		$elements = array();
		foreach ($domNode->childNodes as $index=>$node){
			if(XML_ELEMENT_NODE == $node->nodeType){
				$elements[] = $this->getInfoFromSingleElement($node);
			}
		}
		return $elements;
	}
	
	/**
	 * 获取单个元素的信息到数组
	 * 参数：DOMNode
	 * 返回：信息数组
	 */
	public function getInfoFromSingleElement( $node ){
		if(null == $node){
			ActionUtil::action('Error')->no_such_node();
			die;
		}
		$nodeName = $node->nodeName;	//标签名
		$id = $node->getAttribute("id");	//id属性
		$name = $node->getAttribute("name");	//name属性
		$type = $node->getAttribute("type");	//type属性
		$src = null;	//src属性
		$items = null;	//items属性
		if(2 == $this->elementIsFileOrDir($nodeName)){
			$items = $node->getAttribute("items");
			if(0 == strcasecmp('real', $type))
				$src = $node->getAttribute("src");
		}else if(1 == $this->elementIsFileOrDir($nodeName)){
			$src = $node->getAttribute("src");
		}
		return array(
			'nodeName'	=>	$nodeName,
			'id'		=>	$id,
			'name'		=>	$name,
			'type'		=>	$type,
			'src'		=>	$src,
			'items'		=>	$items,
		);
	}
	
	/**
	 * 从一个节点下的子元素生成目录树的某一层的HTML片段
	 * 参数：父节点的DOMNode
	 * 返回：html代码的字符串
	 * 用途：展开目录时用到
	 * 生成的代码依赖于CSS @ /res/css/Admin-admin.css
	 */
	public function generatelistHTMLformSubElements($pnode){
		$elements = $this->getSubElementsByParentNode($pnode);
		$listHTML = '';
		$pre_id = '';//如果父节点不是根元素，则要给出上几层目录经过的id串。
		if( strcasecmp(ROOT_ELEMENT_NAME_IN_GLOBAL_FILE, $pnode->nodeName) )
			$pre_id = implode('-', $_SESSION['urlInfo']['ids']).'-' ; //上层所经目录的所有id
		foreach ($elements as $ele){
			foreach (array('nodeName','id','name','type','items') as $v)
				$$v = $ele[$v];
			$li_class = ' ';
			$btn_class = ' ';
			$onclick = ' ';
			if(! strcasecmp(ROOT_ELEMENT_NAME_IN_GLOBAL_FILE, $nodeName) || ! strcasecmp(DIR_ELEMENT_NAME_IN_GLOBAL_FILE, $nodeName)){
				if(0 == $items){
					$li_class = ' class="hasNoChild" ';
					$onclick = ' onclick="click_empty_li(this)" ';
				}else{
					$li_class = ' class="closed" ';
					$onclick = ' onclick="click_closed_li(this)" ';
				}
				$btn_class = ' class="btn btn-inverse" ';	//$btn_class = ' class="btn btn-inverse closed" ';
			}else if(! strcasecmp(FILE_ELEMENT_NAME_IN_GLOBAL_FILE, $nodeName)){
				$li_class = ' class="isFile" ';
				$onclick = ' onclick="click_file_li(this)" ';
				$btn_class = ' class="btn btn-link" ';
			}
			$listHTML .= '<li id="li_'.$pre_id.$id.'" '.$li_class.'><button id="'.$pre_id.$id.'"'.$btn_class.$onclick.'>'.$name.'</button></li>';
		}
		if( strcasecmp(ROOT_ELEMENT_NAME_IN_GLOBAL_FILE, $pnode->nodeName) )
			$listHTML = '<ul>' . $listHTML ."</ul>";
		else
			$listHTML = '<ul id="ulroot">' . $listHTML ."</ul>";
		return $listHTML;
	}
	
	/**
	 * 从一个节点下的子元素生成管理端右侧文件枚举视图的HTML片段
	 * 参数：父节点的DOMNode
	 * 返回：html代码的字符串
	 * 用途：展开目录时需要将目录内的内容显示在右侧
	 */
	public function generateContentHTMLfromSubElements($pnode){
		$elements = $this->getSubElementsByParentNode($pnode);
		$contentHTML = '';
		$pre_id = '';//如果父节点不是根元素，则要给出上几层目录经过的id串。
		if( strcasecmp(ROOT_ELEMENT_NAME_IN_GLOBAL_FILE, $pnode->nodeName) )
			$pre_id = implode('-', $_SESSION['urlInfo']['ids']).'-' ; //上层所经目录的所有id
		$i = 1 ;
		foreach ($elements as $ele){
			foreach (array('nodeName','id','name','type','items') as $v)
				$$v = $ele[$v];
			//<div id="v_xxx" class="v_xxx" onclick="v_click_xxx"><img class="v_img_xxx" /><br>文件（目录）名</div>
			$v_class = ' ';
			$img_src = ' ';
			$onclick = ' ';
			$onContextMenu = ' ';
			$v_row_prefix = ' ';
			$v_row_suffix = ' ';
			if(1===intval($i%6)){
				/*第一个*/
				$v_row_prefix = '<div class="v_row">';
				$v_class = ' class="v_left" ';
			}else if(0===intval($i%6)){
				/*第六个*/
				$v_class = ' class="v_right" ';
				$v_row_suffix = '</div>';
			}else{
				/*中间四个*/
				$v_class = ' class="v_middle" ';
			}
			/*单个div中的内容：图标和下方文字；div的单击触发器onclick*/
			if(2==$this->elementIsFileOrDir($nodeName)){
				if(0 == $items)
					$img_src = ' src="'.APPROOT.'res/img/emptydir.png" ';	//' class="v_img_emptydir" '
				else
					$img_src = ' src="'.APPROOT.'res/img/fulldir.png" ';	//' class="v_img_fulldir" '
				$onclick = ' onclick="v_click_opendir(this)" ';
				$onContextMenu = ' onContextMenu="return icon_context_in_right_content(this, \'dir\')" ';
			}else if(1==$this->elementIsFileOrDir($nodeName)){
				$img_src = ' src="'.APPROOT.'res/img/file.png" ';	//' class="v_img_file" ';
				$onclick = ' onclick="v_click_openfile(this)" ';	//' onclick="v_click_openfile(this)" '
				$onContextMenu = ' onContextMenu="return icon_context_in_right_content(this, \'file\')" ';
			}
			//拼接单个文件（目录）项的HTML片段
			$contentHTML .= $v_row_prefix . '<div' . $v_class . '><img id="v_' .$pre_id.$id. '"' . $img_src . $onclick . $onContextMenu . '/><br/>'.$name.'</div>' . $v_row_suffix ;
			$i ++;
		}
		return $contentHTML;
	}
	
	/**【该代码有问题，暂时无法解决】
	 * 定位到指定的目录节点，并生成展开列表至该目录的HTML片段
	 * 参数：需要展开的元素节点id串构成的数组
	 * 返回：HTML片段
	 */
	public function orientateDirFromIdAndGenerateListHTML($ids, $flag=0, $raw_id=null, $root=null){
		$innerHTML = '';
		$elements = null;
		$node = null;
		$root or $root = $this->loadGlobalAndGetRootElement();
		if(0 == $flag)
			$node = $root;
		else
			$node = $this->getElementByIdArray($ids, $root);
		$id_save = null;
		if($raw_id != null)
			$id_save = $raw_id;
		else
			$id_save = $ids;
		if($flag < count($id_save))
			$innerHTML = $this->orientateDirFromIdAndGenerateListHTML(array_slice($ids, 0, $flag+1), $flag+1, $id_save, $root);
		$elements = $this->getSubElementsByParentNode($node);
		$listHTML = '';
		$pre_id = '';//如果父节点不是根元素，则要给出上几层目录经过的id串。
		if( strcasecmp(ROOT_ELEMENT_NAME_IN_GLOBAL_FILE, $node->nodeName) )
			$pre_id = implode('-', $_SESSION['urlInfo']['ids']).'-' ; //上层所经目录的所有id
		foreach ($elements as $ele){
			foreach (array('nodeName','id','name','type','items') as $v)
				$$v = $ele[$v];
			$li_class = ' ';
			$btn_class = ' ';
			$onclick = ' ';
			if(! strcasecmp(ROOT_ELEMENT_NAME_IN_GLOBAL_FILE, $nodeName) || ! strcasecmp(DIR_ELEMENT_NAME_IN_GLOBAL_FILE, $nodeName)){
				if(0 == $items){
					$li_class = ' class="hasNoChild" ';
					$onclick = ' onclick="click_empty_li(this)" ';
				}else{
					$li_class = ' class="closed" ';
					$onclick = ' onclick="click_closed_li(this)" ';
				}
				$btn_class = ' class="btn btn-inverse" ';	//$btn_class = ' class="btn btn-inverse closed" ';
			}else if(! strcasecmp(FILE_ELEMENT_NAME_IN_GLOBAL_FILE, $nodeName)){
				$li_class = ' class="isFile" ';
				$onclick = ' onclick="click_file_li(this)" ';
				$btn_class = ' class="btn btn-link" ';
			}
			echo "========$flag=======<br>";
// 			if(count($id_save) != $flag && $ids[$flag] != $id)
// 				$innerHTML = '';
// 			else if(count($id_save) == $flag)
// 				$innerHTML = '';
			$listHTML .= 
				'<li id="li_'.$pre_id.$id.'" '.$li_class.'><button id="'.$pre_id.$id.'"'.$btn_class.$onclick.'>'.$name.'</button>'
				.$innerHTML.
				'</li>';
		}
		if( strcasecmp(ROOT_ELEMENT_NAME_IN_GLOBAL_FILE, $node->nodeName) )
			$listHTML = '<ul>' . $listHTML ."</ul>";
		else
			$listHTML = '<ul id="ulroot">' . $listHTML ."</ul>";
		return $listHTML;
	}
	
	/**
	 * 加载XML并获得根元素节点
	 * 成功返回DOMNode，失败返回null
	 */
	public function loadGlobalAndGetRootElement(){
		if(! file_exists($this->global_file)){
			ErrorAction::has_no_vfs_global_setupfile();
			return null;
		}
		//先从 主GLOBAL文件加载DOM，要避免XML中存在含有“&”的属性值，这个就交给客户端拦截了。
		$this->load(VFS_GLOBAL_SETUPFILE_PATH);
		//按根节点名称寻找TAG
		$elements = $this->getElementsByTagName(ROOT_ELEMENT_NAME_IN_GLOBAL_FILE);
		//如果从主GLOBAL文件加载到的DOM树是空的，则从备用的配置文件加载
		if(0 == $elements->length){
			if(! file_exists(VFS_GLOBAL_BACKUP_SETUPFILE_PATH)){
				ErrorAction::has_no_vfs_global_backup_setupfile();
				return null;
			}
			$this->load(VFS_GLOBAL_BACKUP_SETUPFILE_PATH);
			$elements = $this->getElementsByTagName(ROOT_ELEMENT_NAME_IN_GLOBAL_FILE);
			//如果从备用文件加载到的DOM树也是空的，则说明备用文件也损坏了，就要为系统报错。
			if(0 == $elements->length){
				ErrorAction::vfs_global_setup_and_backup_file_error();
				return null;
			}
			//如果成功加载备用文件得到非空的DOM树，则要自动修复主GLOBAL文件
			$this->saveGlobalFileWithFormat();
			//修复完毕后，要使DOM加载回主GLOBAL文件
			$this->load(VFS_GLOBAL_SETUPFILE_PATH);
		}else{
			//如果成功加载主GLOBAL文件得到非空的DOM树，则要多保存一份到备用文件
			$this->save(VFS_GLOBAL_BACKUP_SETUPFILE_PATH);
		}
		$this->load(VFS_GLOBAL_SETUPFILE_PATH);
		//按根节点名称寻找TAG
		$elements = $this->getElementsByTagName(ROOT_ELEMENT_NAME_IN_GLOBAL_FILE);
		return $elements->item(0);
	}
	
	/**
	 * 保存当前DOM到global文件。
	 * 没有缩进化保存，该方法将被废弃。
	 */
	public function saveGlobalFile(){
		$this->save($this->global_file);
	}
	
	/**
	 * 将当前DOM缩进化保存到Global文件
	 */
	public function saveGlobalFileWithFormat() {
		$this->xml_str = null;
		$domNode = $this->getElementsByTagName(ROOT_ELEMENT_NAME_IN_GLOBAL_FILE)->item(0);
		$this->xml_str .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
		//本标签名
		$this->xml_str .= "<" . $domNode->nodeName . " ";
		// 本标签属性
		$this->ergodicAttributesOfElementToGlobalFile ( $domNode );
		$this->xml_str .= ">\r\n";
		// 遍历本标签内部的节点
		$this->ergodicInnerOfElementToGlobalFile ( $domNode );
		$this->xml_str .= "</" . $domNode->nodeName . ">\r\n";
		//保存到文件
		file_put_contents(VFS_GLOBAL_SETUPFILE_PATH, $this->xml_str);
		//保存操作完成之后，要清空xml文本存储器
		$this->xml_str = '';
	}
	/*
	 *  写入标签的所有属性到字符串
	 */
	private function ergodicAttributesOfElementToGlobalFile($domNode) {
		foreach ( $domNode->attributes as $attrName => $attrNode ) {
			$this->xml_str .= "$attrName=\"" . $attrNode->value . "\" ";
		}
	}
	/*
	 * 写入某标签内的内容到字符串，不包含该标签的本身和属性
	 */
	private function ergodicInnerOfElementToGlobalFile($domNode) {
		foreach ( $domNode->childNodes as $index => $node ) {
			if (XML_ELEMENT_NODE == $node->nodeType) {
				for($i=0;$i<=$this->flag_ergodic;$i++)	//标签缩进
					// 					$this->xml_str .= Util_::echo8spaces ();
					$this->xml_str .= "\t";
				$this->xml_str .= "<" . $node->nodeName . " ";
				$this->ergodicAttributesOfElementToGlobalFile ( $node ); // 遍历属性
				if ($this->hasElements ( $node ) == 1) {
					$this->flag_ergodic ++;
					$this->xml_str .= ">\r\n";
					$this->ergodicInnerOfElementToGlobalFile ( $node );
					$this->flag_ergodic --;
					for($i=0;$i<=$this->flag_ergodic;$i++)	//标签缩进
						// 						$this->xml_str .= Util_::echo8spaces ();
						$this->xml_str .= "\t";
					$this->xml_str .= "</" . $node->nodeName . ">\r\n";
				} else {
					$this->xml_str .= "/>\r\n";
				}
			}
		}
	}
	
	
	
	
	
}