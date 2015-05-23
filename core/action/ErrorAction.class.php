<?php
class ErrorAction extends ActionUtil{
	public function number_of_args($len){
		echo "参数个数错误，当前：$len<br>";
	}
	public function id_format(){
		echo "id格式错误<br>";
	}
	public function has_empty_args(){
		echo "含有空串参数，即含有“||”或“空串|”或“|空串”<br>";
	}
	public function no_such_shell(){
		echo "所请求的指令不存在<br>";
	}
	public function no_such_node(){
		echo "虚拟文件系统不存在该节点<br>";
	}
	public function node_already_exist(){
		echo "该节点已存在<br>";
	}
	public function node_not_dir(){
		echo "请求的节点不是目录<br>";
	}
	public function url_param_doesnt_fit_the_shell($action_shell_info){
		echo "参数不合要求，发生于：$action_shell_info<br>";
	}
	public function already_exist_the_sameFileOrDirNameAttribute_in_current(){
		echo "当前目录下已存在同名的文件或目录<br>";
	}
	public function hasSpecialSymbol(){
		echo "不能含有这些特殊字符：[ / \\ : * ? \" &lt; &gt; |]<br/>";
	}
	public function has_no_vfs_global_setupfile(){
		echo "VFS全局配置文件丢失或找不到，请到系统安装日志文件中检查正确的位置，并重新修复<br>";
	}
	public function has_no_vfs_global_backup_setupfile(){
		echo "VFS全局配置的备用文件丢失或找不到，系统可能发生严重错误，请到系统安装日志文件中检查正确的位置，并重新修复<br>";
	}
	public function vfs_global_setup_and_backup_file_error(){
		echo "VFS全局配置文件（主配置文件）及其备用文件都存在问题，请修复主配置文件。备用文件会在主配置文件修复完成后自动修复。<br>";
	}
	public function hasSeriousErrorInSavingXMLBecauseDOMNodehasError_and_backupSetupFileCannotRepairGlobal(){
		echo "在保存GLOBAL文件过程中发生了错误，原因是DOM节点有严重问题，现在已经通过备用GLOBAL文件修复。文件系统状态已还原到本次操作之前。<br/>";
	}
	public function the_find_not_exit_in_filesystem(){
		echo "该文件在真实文件系统中不存在<br>";
	}
	public function cannot_find_the_node_in_vfs(){
		echo "虚拟文件系统中找不到该文件或目录对应的节点<br>";
	}
	public function certain_subElement_has_error($id_array){
		echo "$id_array 对应的标签的某个子标签存在问题<br/>";
	}
	public function some_attr_value_without_utf8_in_xml(){
		echo "出现了非UTF-8编码的XML属性值<br>";
	}
	public function cannot_find_verify_file($verifypath){
		echo "无法找到verify文件：".$verifypath.'<br>';
	}
}