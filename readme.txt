
													ActionInvoker调度框架简介及使用入门

一、框架结构：

	1、概览图
			|index.php
			|path__.php
			|
			|
			|core	|action	|IndexAction.class.php
			|		|		|TestAction.class.php
			|		|
			|		|
			|		|filter	|TestFilter.class.php
			|		|       
			|		|
			|		|lib	|base			|__include__.php
			|		|		|				|path__.php
			|		|		|				|env__.php
			|		|		|				|action__.php
项目名		|		|		|				|url__.php
			|		|		|				|init__.php
			|		|		|				
			|		|		|
			|		|		|ext			|其它自添加的库...
			|		|
			|		|
			|		|tpl	|default		|Action模板存放点
			|		|		|				
			|		|		|
			|		|		|other			|普通模板存放点
			|		|		
			|		|
			|		|setting|各种格式的
			|				|配置文件，
			|				|文件内容格式自定，
			|				|解析配置文件过程
			|				|也需要自己编写
			|
			|
			|res	|img
					|css
					|js
					|其它资源文件

	2、说明

		0)概念、名词解释：
			a、本框架的核心是“URL参数————Action调度”。
			b、入口：又称入口文件，是http请求后服务器首先调用的脚本，一般认为 /index.php就是入口。
				一般入口文件所在目录要配备一个path__.php文件，关于path__.php的作用及编写方法，见第一、2、 8) 条。
			c、Action方法：在XxxxAction.class.php中定义的类方法，用static限定，调用形式：XxxxAction::shell();。
				模块：就是XxxxAction
				操作：就是XxxxAction中的类方法。
				默认模块：输入链接[ http://server.com/ItemName ]后被访问的模块。
				默认指令：输入链接[ http://server.com/ItemName ]后被访问的Action方法的名称。关于自定义默认指令，见 env__.php的DEFAULT_URL_SHELL
			d、指令（亦称URL SHELL、URL指令、shell）来源于链接[ http://server.com/ItemName/?xxx=param ]的param部分。
			e、param部分称为“URL全参数”，本项目要求的合法param的格式是：
						[指令] | [ 后续参数(集) = { [参数1|参数2|...|参数n-1] [ | id串={id1-id2-...-idn}] } ]
						例如：假设存在指令“dirdir”，不存其它指令“abc”
							dirdir|abcde|1-2-3-4-5		合法
							dirdir|abcde				合法
							dirdir|1-2-3-4-5			合法
							dirdir|1-2--4-5					非法
							dirdir						合法
							1-2-3-4						合法
							dirdir|							非法
							|dirdir							非法
							|dirdir|						非法
							dirdir|abcde|					非法
							|								非法
							||								非法
							|||								非法
							||||.....						非法
							abc								非法
							abc|abcd 						非法
							abc|1-2-3-4-5					非法
							abc|							非法
							|abc							非法
							|abc|							非法
							更多情况不一一列举，请依次类推。
			
					从以上的列举可以看出，指令必定存在于“URL全参数”的第一个单词。
			f、系统如果从URL解析出指令[URL SHELL]，那么就会继续寻找有没有对应的Action方法。
			g、如果一个指令能够成功调用某个Action方法，则存在与这个指令名称相同的Action方法。
					如：index指令对应index()方法。至于index()方法属于哪个Action，那就要看action__.php文件中怎么配置了。
			h、URL参数状态：其实就是对链接中param部分的种类划分。值：0 ~ 5。
				 * 0、空（长度0）
				 * 1、只有指令（长度1）
				 * 2、只有id串（长度1）
				 * 3、全满含id尾：有指令有作为id串的尾部
				 * 4、全满无id尾：有指令没有作为id串的尾部。也包括如“dirdir|fdsfdsfd|real|1-0-1”，最后一个参数可以认为已经不是id串了。
				 * 5、非法：
				 * 		长度为1时，非指令非id串；
				 * 		长度>1时，第一项非指令;
				 * 		长度等于1时id串去除第一个后还含有“零”项；
				 * 		“xxx|xxx|xxx|xxx”URL含有空串参数，即是否含有“||”或“空串|”或“|空串”；
			i、URL参数信息：通过UrlUtil::analyseUrlParam() @ url__.php获得。
				其数据格式：
				 * 		array (
				 * 			'status'=>	0~5 ,	//URL参数状态
				 * 			'shell'		=>	'URL命令/null',
				 * 			'params'	=>	array (
				 * 								被“|”分隔的参数集合
				 * 							),
				 * 			'ids'	=>	array(被“-”分割的id数组，不以“零”项开头，且不包含“零”项)
				 * 		);
		
		1)/core/action/ 目录用于放置业务相关的[模块——>操作]文件
			其中的IndexAction是默认的模块，内部的方法index()对应的指令是index.
			Action方法的传入参数：可以注意到，预配置的Action方法中，有名为$urlInfo的参数。
					该参数就是从URL中解析得到的参数信息，其参数状态有：0,1,3,4.最常见的参数状态是4。
					如何提取传入参数：从1开始，依次是 $urlInfo['params'][1]、$urlInfo['params'][2]、$urlInfo['params'][3]、...
					提取到的参数对应于：URL指令|param1|param2|param3|...
			将Action方法注册为URL指令：通过注册指令，就可以通过链接方式调用Action方法。
					本框架预注册的指令有：test,index,demo，对应于TestAction::test(),IndexAction::index(),IndexAction::demo()。
					注册方法：见ActionUtil::numOfShellArgs() @ aciton__.php及其注释部分。
			Action方法的调用代码：
				a、在某个模块中调用内部方法，可使用$this->方法名()。但是这种普通的调用方法将无法执行所绑定的过滤器。
					【关于过滤器，见第 一、2、5)条】
					如果要在调用方法前执行过滤器，则可使用：parent::__call($method, $vars)。
					其中，$method是方法名，$vars是传入的参数表构成的数组。
					例如：parent::__call('test', array($urlInfo));
				b、在模块外部调用模块方法：
					一般的代码是：$a = new XxxxAction(); $a -> 方法名();
					本框架推荐的代码是：ActionUtil::action('Action名称,如Index')->方法名();
					如果调用代码所处的位置也是在模块中，则调用代码还可写成：$this->action('Action名')->方法();或self::action('Action名')->方法();
			重新定义默认的模块及指令，见 DEFAULT_URL_SHELL @ env__.php 和 ActionUtil::numOfShellArgs() @ action__.php。
			另外的TestAction是示例模块，其方法test()已在ActionUtil::numOfShellArgs()中注册。
			
		2)/core/lib/ 目录存放基础库base和其他库
			基础库/core/lib/base/如下：
					环境配置文件					env__.php，
					过滤器支持文件				filter__.php，
					模块配置文件					action__.php，
					URL解析支持文件				url__.php，
					路径纠正支持文件				path__.php，
					系统初始化文件				init__.php，
					一次性包含常用库的文件		__include__.php
				a、env__.php配置常量和一些系统的惯例，如时区设定
				b、filter__.php提供过滤器支持。关于过滤器的说明和使用方法，见 【/core/filter/ 目录的说明】。
				c、action__.php配置所有Action的方法所对应的参数，以及配置如何调度Actions。详见源码注释。
				d、url__.php配置URL静态化、URL重写。从URL中分析参数信息等等。详见源码注释。
				e、init__.php初始化系统：
					=>先解析URL参数	=>尝试常规调度(Action) =>常规调度失败则尝试根据URL参数寻找模板文件并输出 =>匹配不到模板文件，则按404处理。
				f、__include__.php用于包含整个项目常用的文件。
					任何文件只要在其同目录下含有文件__path.php,并且已包含之，
					那么在包含这个文件之后，本文件所包含的文件就能被包含了。
			扩展库/core/lib/ext/：
				默认情况下，开发者应该把自行导入的库放置到此处。
				如果想把自己的库放到别处，则需要到env__.php配置USER_LIB_DIRS常量，具体配置方法见其注释。
		
		3)/core/setting/ 目录：存放各种格式的配置文件，其文件内容格式任意，但是需要自己编写解析配置文件的方法。
			作者已经有了一些类型配置文件的解析方法了，但是尚未整合到该框架中，请期待后续版本。
		
		4)/core/tpl/ 目录：存放模板输出文件的目录。
			    模板文件定义：用于输出到客户端的文件
			    模板的分类：按照用途，可以分为Action模板、普通模板。具体用途详见后续说明。
			    模板输出的底层实现方式：使用php的require_once语句。
			    模板参数支持：
			    	由XxxxAction的基类ActionUtil::getTplArgs() 提供带索引数组形式的参数，以供模板文件内脚本使用。
			    	由setTplArgs($tpl_args)方法设置参数，如果没什么参数可提供的，则可不写参数。
			a、Action模板：
				Ⅰ、特点：Action方法附属的模板文件，与方法同名，并由该Action方法独享。
				Ⅱ、命名规则：文件名等于方法名，后缀限定为.php。
				Ⅲ、存放位置：默认为/core/tpl/default/。如需自定义，见ACTION_TEMPLATE_DIR @ /core/lib/base/env__.php
						具体操作：先在core/tpl/default/新建名为Action名称的文件夹，
								   再在这个文件夹中新建与Action方法同名的模板文件，后缀名限定为.php。
								 如：IndexAction::index()的模板文件路径时：/core/tpl/default/Index/index.php
				Ⅳ、如何访问？不能通过链接直接访问，只能通过ActionUtil类方法tpl($tpl_args)实现间接访问。
					XxxxAction可以通过继承ActionUtil来得到tpl()方法的使用权。
				Ⅴ、参数支持：由基类ActionUtil::$tpl_args 提供带索引数组形式的参数，以供模板文件内脚本使用。
				Ⅵ、输出结果：如果找不到相应的模板文件，则不会输出任何内容，但tpl()方法会返回false作为标志。
							成功则返回true。开发者可以根据tpl()的返回值来判断是否输出成功。
				Ⅶ、用途：给业务方法配备专用的输出模板。
			b、普通模板：
				Ⅰ、特点：与Action模板不同，不从属于Action方法，可以在任何地方被调用。
				Ⅱ、命名规则：1、不含“-”；2、必须以.php为后缀名。
				Ⅲ、存放位置：默认为/core/tpl/other/。如需自定义，见 OTHER_TEMPLATE_DIR @ /core/lib/base/env__.php
					  可以要再细分目录存放模板文件，如果想通过链接访问到再深一层的文件，则需要用“-”代替“/”。
					 如：http://server.com/ItemName/?xxx=a-b-c 将默认访问到core/tpl/a/b/c.php。
				Ⅳ、如何访问？
					链接访问法：http://server.com/ItemName/?xxx=模板文件参数。
					代码调用法：require_once OTHER_TEMPLATE_DIR.'模板文件'。
				Ⅴ、标准的模板文件参数格式：a-b-c-...-z。
				Ⅵ、使用（“\”、“/”）混淆模板文件参数以保护路径。
	 				对于【'r_^-fds \  \--/gh'】之类的文件名参数，本系统可以将之解析为【r_^/fds/gh】
	 				（原参数中，有“-”和“--”，但是结果就有两条“/”。至于原参数中的“\\”和“/”，将被清除）。
	 				继续对【'r_^-fds \  \--/gh'】进行URL编码，可以得到【r_%5E-fds%20%5C%20%20%5C--%2Fgh】。
				Ⅶ、设置模板参数：
					如果使用【代码调用法】：则可以在调用之前，先执行XxxxAction::setTplArgs($tpl_args)以向模板文件提供参数。
					如果使用【链接访问法】：则无法设置参数。链接访问法通常用于不需要参数的页面。
				Ⅷ、输出结果：如果找不到模板，则按404处理。
				Ⅸ、用途：如果多个业务需要共享一个页面，则可以使用普通模板。
		
		5)/core/filter/ 目录：存放XxxxFilter.class.php。这个目录涉及到过滤器。
			a、过滤器：执行某过程之前需要执行的操作。
			b、内置过滤器：框架内置的 【模块单过滤器】，其实就是一个接口Filter，内设方法doFilter()，用于在进入Action模块前的过滤。
			c、单过滤器：如果某个过程仅需要一个过滤器，那么这个过滤器就构成了单过滤器。
			d、过滤器链：如果某个过程需要多个过滤器，那么这些过滤器就构成了过滤器链。
			e、过滤器分类：
				在本框架中，过滤器按过滤类型分为两种：Action模块过滤器、Action操作[方法]过滤器；
				再按过滤层次还可分为：单过滤器、过滤器链。
			f、过滤器群组：存储多种过程各自需要的单过滤器或过滤器链的单位。
				一般地，将属于同一个XxxxAction的所有模块过滤器和方法过滤器  归并到  同一个过滤器群组中。
				自然而然，这个存储单位将使用XxxxFilter.class.php存储。
				只要定义了XxxxFilter或XxxxAction实现了Filter接口，那么XxxxAction将自动绑定过滤器。
			g、使用过滤器详解：
				 * 1、如果仅需要————单个Action模块过滤器，就不需要定义高级过滤器 XxxxFilter，仅需实现Filter接口。
				 * 	格式如：
				 * 			class XxxxAction implements Filter{
				 *	 			public function doFilter(){
				 * 					//实现系统内置的模块单过滤器
				 * 				}
				 * 			}
				 * 2、如果需要在进入Action模块之前设置多个过滤器，那么这里就可以用到XxxxFilter了。
				 * 	过滤器群组XxxxFilter的定义格式：
				 * 		class XxxxFilter {
				 * 			public function doFilter_1(){
				 * 				//模块过滤器1
				 * 			}
				 * 			public function doFilter_2(){
				 * 				//模块过滤器2
				 * 			}
				 * 				... ...
				 * 		}
				 * 	  执行过滤器的顺序将是：doFilter_1(), doFilter_2(), ...
				 * 	 如果XxxxAction还实现了Filter接口的doFilter()方法，则会先执行doFilter()，再执行doFilter_1(), doFilter_2(), ...
				 *   建议：如果是定义多个模块过滤器，就不要使用框架内置的【模块单过滤器Filter】，以确保代码的可读性。
				 * 3、如果Action模块中的操作[方法]也需要过滤器，则可以在XxxxFilter()定义专门过滤Action操作的方法，方法名与Action方法同名
				 * 	格式如：
				 * 		class XxxxFilter {
				 * 			public function Aaaa(){
				 * 				//XxxxAction::Aaaa()方法对应的过滤器
				 * 			}
				 * 		}
				 *     如果XxxxAction::Aaaa()方法需要多个过滤器，则可以这样定义：
				 * 		class XxxxFilter {
				 * 			public function Aaaa_1(){
				 * 				//XxxxAction::Aaaa()方法对应的过滤器
				 * 			}
				 * 			public function Aaaa_2(){
				 * 				//XxxxAction::Aaaa()方法对应的过滤器
				 * 			}
				 * 			public function Aaaa_3(){
				 * 				//XxxxAction::Aaaa()方法对应的过滤器
				 * 			}
				 * 				... ...
				 * 		}
				 * 		执行过滤器的顺序将是：Aaaa_1(), Aaaa_2(), Aaaa_3(), ...
				 * 		如果Aaaa()过滤方法也存在，则会先执行Aaaa()，在执行Aaaa_1(), Aaaa_2(), Aaaa_3(), ...
				 * 	      建议：如果是定义多个方法过滤器，就不要再使用与Action方法同名的过滤方法，以确保代码的可读性。
				 */
			h、重新定制过滤器目录：见 FILTER_DIR @ /core/lib/base/env__.php
			
		6)/res/ 目录：存放js,css,img等资源文件。
			常规使用方法：可以自行定义常量以方便导入。如定义js目录为JS_DIR，在页面中使用<?=APPROOT.JS_DIR.'具体JS' ?>完成调用。
					对于一些频繁调用的文件，如jquery.js，则可进一步定义常量JQUERY_PATH为APPROOT.'res/js/jquery.js'。
		
		7)/index.php	一般会作为项目的入口。
			脚本固定执行流程如下：
				添加路径纠错支持
				包含框架
				完毕。
			代码：
				----------------------------------------------------------------------------------------------------------------------
				<?php
				/**
				 * 项目默认入口
				 */
				require_once 'path__.php';	//路径纠错支持
				require_once APPROOT.'core/lib/base/__include__.php';	//一次性包含常用库和所有Action
				/*
				 * 以上两行代码已经完成了框架的初始化，后面也不用添加什么代码了，写了也没有用，执行不到的。
				 * 要写业务处理，就定义XxxxAction；
				 * 要给业务加上过滤器，就直接让XxxxAction实现Filter接口或定义过滤器群组XxxxFilter
				 * 要显示页面，使用模板输出。
				 */
				?>
				----------------------------------------------------------------------------------------------------------------------
			如果需要定制其它入口文件，则新入口文件内的代码也和/index.php的一样，
			并要根据入口文件的目录层次，为其配备一个合适的path__.php文件，详见第一、2、8）条。
			
		8) path__.php  路径纠正支持文件
			a、作用：该文件中的APPROOT常量可以解决文件多重包含时产生的路径错乱问题。
			b、编写方法：
					<?php
					defined('APPROOT') or define('APPROOT','路径值');
					?>
			c、什么是路径值（APPROOT）？
				路径值指的是 项目根目录相对于path__.php的目录，如（''、'../'、'../../'等等）。
				如：
					/path__.php位于项目根目录，应配置的值为  './';
					/core/path__.php位于第一层目录，所以配置的值为 '../';
					/core/lib/path__.php位于第二层目录，所以配置的值为 '../../';
					/core/lib/base/path__.php位于第三层目录，所以配置的值为 '../../../';
			d、何时需要用到path__.php？
				凡是需要包含其它文件的脚本，都需要包含path__.php。【require_once 'path__.php';】
				那么，入口文件就要用到path__.php。
				
		9) APPROOT 常量
			a、其含义见第 8) 条。
			b、使用APPROOT包含文件的格式【require_once APPROOT.'路径串';】。
				要求路径串从项目根目录为起点，如：
				require_once APPROOT.'core/lib/base/__include__.php';

二、使用入门

	1、将ActionInvoker文件夹提取到站点根目录，文件夹名称改为自己的项目名称。
	
	2、在/core/lib/base/env__.php配置自己系统中需要的常量和惯例（如时区设定）。
		需要重点配置的常量是：ENABLE_URL_REWRITE，其它自便。详见注释。
	3、查看默认的Action及操作。
		本项目已经预先在env__.php中定义了常量DEFAULT_URL_SHELL等于“Index-index”，对应IndexAction::index()。
		且在action__.php中的ActionUtil::numOfShellArgs()预配置的Index->index()参数个数为0.
		直接在浏览器测试类似以下链接：
			a、http://server.com/ItemName
			b、http://server.com/ItemName/?xxx=index
		就可以看到欢迎页。
		
	4、如果要添加自己的功能，则可以自行添加模块XxxxAction.class.php到/core/action/中，
		例如 添加文件模块FileAction，并在其中添加[显示目录内容]的方法listfile()。具体步骤如下：
			a、新建/core/action/FileAction.class.php，代码参考IndexAction.class.php
			b、在/core/lib/base/action__.php的ActionUtil::numOfShellArgs()中注册listfile()方法，并指定所需参数个数。
				这里的参数个数 不  是定义function时的参数表的参数个数，而是URL参数信息中count($urlInfo['params'])的值。
				【注：$urlInfo = UrlUtil::analyseUrlParam($_REQUEST);	//见core/lib/base/url__.php】
			c、测试链接：
				如果参数个数为0————http://server.com/ItemName/?xxx=listfile
				如果参数个数大于0————http://server.com/ItemName/?xxx=listfile|后续参数(集)
				
	5、如果需要将自己添加的功能设置为默认功能，只需要配置 DEFAULT_URL_SHELL  @  /core/lib/action__.php
		默认值的格式：Action名称-方法名，如：define('DEFAULT_URL_SHELL','File-listfile');
		注意：所配置的默认功能必须存在。
		
	6、如果需要导入其它库，只需要把库文件放置到/core/lib/ext/ 目录中，可以在其中再设定子目录存放。
		放置完毕后，系统将会自动包含其内所有层次目录的*.php文件。
		如果需要把自定义库文件放置到别处，则需要配置USER_LIB_DIR  @  /core/lib/base/env__.php
	
	7、简单输出页面：
		a、使用Action模板：
			将需要输出的Action页面放到/core/tpl/default/目录（如需改变目录，见ACTION_TEMPLATE_DIR @ /core/lib/base/env__.php）
			本框架已预定义了IndexAction::index()对应的模板，位于/core/tpl/default/Index/index.php。
			并在IndexAction::index()中使用了模板输出方法：self::tpl();
			预配置的模板将输出的内容是 本框架的欢迎页。
		b、使用普通模板：
			将需要输出的自由页面放到/core/tpl/other/目录（如需改变目录，见OTHER_TEMPLATE_DIR @ /core/lib/base/env__.php）
			本框架已预定义了普通模板/core/tpl/other/othertest.php。
			并在TestAction::test()方法中给出了测试代码。也可以通过链接来测试。
			链接访问方法：http://server.com/ItemName/?xxx=模板文件参数。（详见 一、2、(4) 条）			
	
	8、简单使用过滤器：
		本框架通过让TestAction模块实现Filter接口，从而绑定了内置的单模块过滤器Filter::doFilter()。
		通过定义过滤器群组TestFilter，从而使TestAction模块绑定了TestFilter::doFilter_*()过滤器链，
			也使TestAction::test()方法绑定过滤器TestFilter::test(),TestFilter_test_*()过滤器链。
		测试预置过滤器：执行IndexAction::demo	()方法即可。
			测试链接：http://server.com/ItemName/?xxx=demo

			
三、版本变更日志

	2013-7-29
		【
		初版：支持URL分析和Action调度
		】
	
	2013-7-30
		【
		加入自动包含库文件和Action的支持，嵌入使用向导到首页
		】
	
		2013-7-31
		【
		加入模板输出支持、
		
		非法参数和无效模板的404处理，
		
		准备加入过滤器支持（实现Filter接口以拦截进入Action模块的数据流）
		】
	
	2013-8-4
		【
		1、扩充模板输出能力，可以分为Action模板和普通模板两类；
		
		2、对无法访问到的脚本拦截，需要服务器支持URL重写；
		
		3、加入过滤器支持，支持单过滤器和过滤器链，
		
		   可以为模块专门定制一个过滤器群组
		】