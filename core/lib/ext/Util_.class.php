<?php
/*
 * 存放一些未分类的实方法
 *
 */
class Util_ {
	/**
	 * 输出8个空格，一般用于XML缩进输出
	 */
	public static function echo8spaces(){
		for($i=0;$i<8;$i++)
			echo "&nbsp;";
	}
	
	/**
	 * 判断字符串中是否含有特殊字符。
	 * 参数要求：字符串
	 * 返回：true 有；false 无
	 * 特殊字符有：“\”、“/”、“:”、“*”、“?”、“双引号”、“<”、“>”、“|”、“&”
	 */
	public static function hasSpecialSymbol($str){
		foreach (array('\\','/',':','*','?',chr(ord("\"")),'<','>','|', chr(ord('&'))) as $ch){
			if(false !== strpos($str, $ch))
				return true;
		}
		return false;
	}
	
	/**
	 * 该方法将被弃用
	 * In order to check if a string is encoded correctly in utf-8, 
	 * I suggest the following function, 
	 * that implements the RFC3629 better than mb_check_encoding()
	 * 经测试：不可用
	 */
	public static function check_utf8($str) {
		$len = strlen($str);
		for($i = 0; $i < $len; $i++){
			$c = ord($str[$i]);
			if ($c > 128) {
				if (($c > 247)) return false;
				elseif ($c > 239) $bytes = 4;
				elseif ($c > 223) $bytes = 3;
				elseif ($c > 191) $bytes = 2;
				else return false;
				if (($i + $bytes) > $len) return false;
				while ($bytes > 1) {
					$i++;
					$b = ord($str[$i]);
					if ($b < 128 || $b > 191) return false;
					$bytes--;
				}
			}
		}
		return true;
	} // end of check_utf8
	
	/**
	 * 验证指定的$_REQUEST的所有参数  是否都  非空且非空串
	 * 参数：参数名组成的数组
	 * 返回：true——是；false——否。
	 */
	public static function urlParamNotNullAndNotEmptyString($paramNames){
		foreach ($paramNames as $n){
			if( @$_REQUEST[$n] === null || ! strcasecmp('', @$_REQUEST[$n]) )
				return false;
		}
		return true;
	}
	/**
	 * 验证指定的$_REQUEST的参数  是否含有  非null的参数
	 * 参数：参数名组成的数组
	 * 返回：true——含有；false——不含。
	 */
	public static function urlParamHasNotnull( $pn ){
		foreach ($pn as $n){
			if(@$_REQUEST[$n] !== null)
				return true;
		}
		return false;
	}
	
	/**
	 * 验证指定的$_REQUEST的参数是否全部为  null
	 * 参数：参数名组成的数组
	 * 返回：true——是；false——否。
	 */
	public static function urlParamIsNullAtAll( $pn ){
		foreach ($pn as $n){
			if(@$_REQUEST[$n] !== null)
				return false;
		}
		return true;
	}
	
	/**
	 * 根据URL参数名的数组批量获取参数值
	 * 参数：参数名构成的数组
	 * 返回：参数值构成的数组，顺序与参数名对应
	 */
	public static function getParamValuesFromNames( $pn ){
		$vs = array();
		foreach ($pn as $n)
			array_push($vs, @$_REQUEST[$n]);
		return $vs;
	}
	
	/**
	 * 将期望的URL参数名称矫正到正确的大小写
	 * 参数：期望的参数名构成的数组
	 * 返回：矫正大小写后的参数名构成的数组
	 */
	public static function rectifyExpectationNamesToCorrectUrlParamNames( $exps ){
		$i = 0;
		foreach ( $_REQUEST as $r => $v ){
			if( ! strcasecmp($r, @$exps[ $i ++ ] ) )
				$exps[$i - 1] = $r ;
		}
		return $exps;
	}
	
	/**
	 * 判断字符串是不是空字符
	 */
	public static function isSpaceString( $str ){
		$s = '';
		$len = strlen($str);
		$i = 0;
		while ($i <= $len){
			$i ++;
			if(0 == strcasecmp($s, $str))
				return true;	//是
			$s .= ' ';
		}
		return false;	//不是
	}
}