<?php
class FilterUtil {
	/**
	 * 在XxxxAction模块或方法调用之前，执行绑定的过滤器（如果有）
	 * 参数：Action名称，如'Test'；方法名，如'test'.
	 * 该方法仅供ActionUtil::invokeAction()、XxxxAction__call()、ActionUtil::__call()调用，以实现调用前先过滤的功能。
	 */
	public static function doFilterIfHasIt($action, $method){
		//获取XxxxAction通过实现Filter而绑定的所有过滤器方法
		$filters = get_class_methods($action.'Action');
		if(! $filters)
			return;
		//第一步、执行内置过滤器方法(绑定在XxxxAction模块的默认过滤器)，如果有
		if($filters && in_array('doFilter', $filters))
			ActionUtil::action($action)->doFilter();
		//获取XxxxAction通过定义XxxxFilter而绑定的所有过滤器方法
		$filters = get_class_methods($action.'Filter');
		if(! $filters)
			return;
		//第二步、再执行绑定在Action模块的后续过滤器链，如果有
		$i = 0;
		while( $filters && ++$i<=count($filters) && in_array('doFilter_'.$i, $filters) )
			eval('ActionUtil::action($action)->doFilter_'.$i.'();');
		//第三步、执行Action方法绑定的单过滤器（与方法名同名），如果有
		if( in_array($method, $filters) )
			FilterUtil::filter($action)->$method();
		//第四步、再执行绑定在Action方法的后续过滤器链，如果有
		$i = 0;
		while( $filters && ++$i<=count($filters) && in_array($method.'_'.$i, $filters) )
			eval('FilterUtil::filter($action)->'.$method.'_'.$i.'();');
	}
	/**
	 * 【该方法暂时用不到】
	 * 获取类实现的接口方法
	 * 参数：类名
	 * 返回方法名数组
	 */
	public static function getInterfaceMethodsIfHasTheir($action){
		$m = array();
		$r = new ReflectionClass($action);
		foreach ($r->getInterfaceNames() as $interface){
			foreach (get_class_methods($interface) as $method){
				if(!in_array($method, $m))
					array_push($m, $method);
			}
		}
		return $m;
	}
	/**
	 * 获得一个过滤器的实例
	 * 参数：过滤器名，如取TestFilter中的'Test'
	 * 返回XxxxFilter实例
	 */
	public static function filter($filter){
		$execute = '$f = new '.$filter.'Filter();';
		eval($execute);
		return $f;
	}
	
}






/**
 * 内置的过滤器接口，该接口仅提供一个过滤方法。
 * 如果某个过程仅需一个过滤器，则可以直接实现该接口
 * 一般地，这个过滤方法专门用来绑定XxxxAction对象。
 * 凡是调用时需要通过XxxxAction对象完成的过程，只要绑定了该过滤器，就能够产生访问XxxxAction的第一关卡。
 * 使用内置过滤器的方法：直接implements Filter，然后实现doFilter()即可。
 * Otherwise:
 * 如果XxxxAction内的方法也需要过滤器，就需要定义一个过滤器群组，格式为：class XxxxFilter extends FilterUtil{...}
 * 由于不是所有的Action方法都需要过滤器，因此，需要添加过滤器的方法，一律要用protected修饰。
 * 相应地，应该声明对应的过滤器方法，
 * 格式为：public function xxxx(){...}; //“xxxx”部分等于被需过滤的方法名，如 public function index(){...};
 * Otherwise:
 * 要为同一个过程绑定多个过滤器，则需要在过滤器方法名后添加“_数字”，数字从1开始
 * 	对于Action[对象]，格式如：public function doFilter_1(){...};	//由于还有doFilter()，因此doFilter_1()是第二个过滤器。
 * 	对于Action[方法]，格式如public function index_1(){...}，表示index()方法的第一个过滤器
 */
interface Filter {
	function doFilter();
}

