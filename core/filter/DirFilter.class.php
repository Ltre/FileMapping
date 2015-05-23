<?php
class DirFilter{
	private $urlInfo;
	public function __construct(){
		$this->urlInfo = $_SESSION['urlInfo'];
	}
	private function prevent(){
		echo "@ErrorAction";
		die;
	}
	//type值为real或virtual时，才可以接受参数
	public function dirdir(){
		if( ! in_array($this->urlInfo['params'][3], array('real', 'virtual'))){
			@ErrorAction::url_param_doesnt_fit_the_shell("Dir->dirdir()");
			$this->prevent();
		}
	}
	//type值为virtual时，才可以接受参数
	public function empdir(){
		if( strcasecmp($this->urlInfo['params'][3], 'virtual') ){
			@ErrorAction::url_param_doesnt_fit_the_shell("Dir->dirdir()");
			$this->prevent();
		}
	}
	//要求接收到的urlInfo['ids']!=null
	public function rmdir(){
		if(null == $this->urlInfo['ids']){
			echo "请求错误，无id值<br>";
			$this->prevent();
		}
	}
}