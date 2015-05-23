<?php
/*
 * 管理端的事务操作
 * 用于渲染管理页面、系统设置更改等等
 * 不含文件系统修改
 */
class AdminAction extends ActionUtil implements Filter{
	/**
	 * 进入管理端前的初始化操作 
	 */
	public function admin(){
		$xml = new XMLUtil("1.0", "UTF-8");
		$root = $xml->loadGlobalAndGetRootElement();
		$listHTML = $xml->generatelistHTMLformSubElements($root);
		$contentHTML = $xml->generateContentHTMLfromSubElements($root);
		$pathnavyHTML = '<li id="path_0" class="active">'.@$root->getAttribute("name").' <span class="divider">/</span></li>';
		//将生成的片段转交到admin视图
		$this->tpl(array(
				'listHTML'=>$listHTML, 
				'contentHTML'=>$contentHTML, 
				'pathnavyHTML'=>$pathnavyHTML
		));
	}
	/**
	 * 管理端登录逻辑
	 */
	public function adminLogin(){
		//实验代码：
// 		FileUtil::writeAttrToVerifyFile(APPROOT.ADMINISTRATION_VERIFY_FILE_PATH, 'admin=>admin|:|admin');	//admin=>管理员|:|密码
// 		if(FileUtil::hasMatchedAttrInVerifyFile(APPROOT.ADMINISTRATION_VERIFY_FILE_PATH, 'admin=>admin|:|admin'))
// 			echo "已匹配管理员身份";

		//校验管理员身份
		if(FileUtil::hasMatchedAttrInVerifyFile(APPROOT.ADMINISTRATION_VERIFY_FILE_PATH, 'admin=>'.$this->admin.'|:|'.$this->pwd)){
			$_SESSION['admin'] = array('admin'=>$this->admin, 'pwd'=>$this->pwd);
// 			$_SERVER['REQUEST_URI'] = implode('=', array_splice(explode('=', $_SERVER['REQUEST_URI']), count(explode('=', $_SERVER['REQUEST_URI']))-1, 1)).'=admin';
			$this->admin();	//登录成功，进入管理顶级视图
		}else{
			echo "非管理员身份！<br>";	//这里使用AJAX弹窗提示用户
		}
		exit;
	}
	/**
	 * 退出管理视图
	 */
	public function adminLogout(){
		session_unset();
		header('Location: ./?'.rand(0, 32767).'=admin');
	}
	
	/* 
	 * Admin模块过滤器
	 * 1、从会话中取得URL信息
	 * 2、验证身份
	 */
	public function doFilter() {
// 		FileUtil::writeAttrToVerifyFile(APPROOT.ADMINISTRATION_VERIFY_FILE_PATH, 'admin=>admin|:|admin');die;	//测试用：写入管理员账户信息到校验文件
		$this->urlInfo = @$_SESSION['urlInfo'];
		//以下6行代码：接受URL参数admin和pwd到本模块属性值
		$params = array( 'admin', 'pwd' );
		$correct_params = Util_::rectifyExpectationNamesToCorrectUrlParamNames($params) ;
		$i = 0;
		foreach ( Util_::getParamValuesFromNames( $correct_params ) as $value ){
			$this -> $params[ $i ++ ] = $value ;
		}
		//根据会话和URL参数决定是否需要登录
		if(! isset($_SESSION['admin'])){
			if( ! Util_::urlParamNotNullAndNotEmptyString( $correct_params ) ){
				$tip = Util_::urlParamIsNullAtAll( $correct_params ) ? '尚未登录！' : '登录所需信息不足，请重新登录';  
				ActionUtil::setTplArgs(array('message' => $tip ));
				require_once OTHER_TEMPLATE_DIR.'admin/login.php';	//跳转到登录页
				die;
			}else{
				$this->adminLogin();	//参数信息充足，可以进入登录校验
			}
		}else {	
			//虽然已登录，但想切换到另一个管理员
			Util_ :: urlParamNotNullAndNotEmptyString ( $correct_params ) and $this -> adminLogin ( );
		}
		//echo "如果此处被执行，说明管理员处于在线状态，且希望继续保持这个状态。<br>";
	}
	
	private $urlInfo = null;	//URL参数信息
	private $admin;	//管理员账号
	private $pwd;	//管理员密码
}