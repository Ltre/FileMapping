<?php
/*
 * 与加密有关的工具类
 */
class CryptUtil {
	/**
	 * 简单加密1：先反转后插缝扩容（缩写：SDAR）
	 */
	public static function simpleDilatationAndReversal( $str ){
		$str = strrev( $str );
		$len = strlen($str);
		$raw = str_split($str);
		$dilatation = array();
		$i = 0;
		for( ; $i < 2 * $len ; $i ++ ){
			if( 0 == $i % 2 )
				$element = $raw[ $i / 2 ];
			else
				$element = $raw[ ( 2 * $len - 1 - $i ) / 2 ];
			array_push($dilatation, chr( ord( $element ) + 2 * $len - $i ) );
		}
		$str = implode('', $dilatation);
		return strrev( $str );
	}
	
	/**
	 * 简单解密1：解密 SDAR
	 */
	public static function de_simpleDilatationAndReversal( $str ){
		$str = strrev( $str );
		$raw = str_split( $str );
		$shrink = array();
		$len = count( $raw );
		$i = 0;
		for( ; $i < $len ; $i ++ ) {
			if( 0 == $i % 2 )
				array_push ( $shrink, chr ( ord( $raw[ $i ] ) - ( $len - $i ) ) ) ;
		}
		return strrev( implode('', $shrink) ) ;
	}
}