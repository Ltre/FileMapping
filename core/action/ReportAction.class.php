<?php
/**
 * 这里的方法一般在执行完成后【不】要加 die语句
 *
 */

class ReportAction extends ActionUtil{
	public function global_setupfile_rollback_because_DOMNodeHasError(){
		echo "在保存GLOBAL文件过程中发生了错误，原因是DOM节点有严重问题，现在已经通过备用GLOBAL文件修复。文件系统状态已还原到本次操作之前。<br/>";
	}
}