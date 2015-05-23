jQuery(document).ready(function($) {
    var logout = $("#menus>div:eq(0)>ul:eq(0)>li:eq(5)");
	logout.click(function(evt){
		//evt.preventDefault();
		//$.post('./', {u89jr43ufr498utedvc4:'adminLogout'});
		//location.href = "./?" + _getRandomString(0) + "=admin";
		//window.open('./?' + _getRandomString(0) + '=admin', '_self');
	});
	
/*	//对<li class="closed"><button>展开目录
	click_closed_li = function (e) {
		var id = $(this).attr("id");
		var parent = $(this).parent("#li_"+id);
		alert($(this).html());
		alert(id);
		alert(parent.html());
		$.post('./' ,{xxx:id}, function(data){
			alert(data);
			var innerHTML = parent.html()+data;
			alert(innerHTML);
			parent.append(data);
		});	//$(this).next("ul").children("li.closed").children("button").click( click_closed_li );	//为子节点添加监听器
    }
	//$("li.closed>button").click( click_closed_li );
	//$("button[class~=closed]").click( click_closed_li );
*/
	
});

/*********************全局变量***********************/

var $selected_id = "UNDEFINED";	//右键时被选中的图标id值
var $parent_id = 0;	//记录所进目录的id

/*********************分割线***********************/

//加载目录中的内容到#right_content区域
function loadContentFromDirToRightContent( id ){
	$.post(
		'./', 
		{xxx:"accessDirToContentHTML|"+id}, 
		function(data){
		var rc = document.getElementById("right_content");
		rc.innerHTML = data;
		}
	);
}
//根据id打开文件下载链接
function openFileFromId( id ){
	window.open('./?'+_getRandomString(0)+'='+id);
}
//在视图中，根据id定位到对应的目录【代码有误，暂时无法达到目的】
function orientateDirFromId( id ){
	root = document.getElementById("ulroot");
	$.post('./', {xxx:0}, function(data){
		root.outerHTML = data;
	});
	ids = id.split('-');
	len = ids.length;
	alert("ids.length："+ids.length);
	for(i=0;i<=len;i++){
		id = '';
		for(j=0;j<=i;j++){var $v="";
			if(0==j)
				id+=ids[j];
			else
				id+="-"+ids[j];
		}
		li = document.getElementById("li_"+id);
		liclass = li.getAttribute("class");
		btn = document.getElementById(id);
		alert(id+"====="+liclass);

		li.setAttribute("class", "opened");
		$.post('./', {xxx:id}, function(data){
			alert(i+"的data："+data);
			var innerr = li.innerHTML + data;
			li.innerHTML = innerr;
		});
		loadContentFromDirToRightContent(id);
		showTheHrefWhereChoosing(id);
		updatePathNavy(id);
		obj.setAttribute("onclick", "click_opened_li(this)");

		alert("循环完毕："+i);
	}
}

/*********************分割线***********************/

//在顶部input域 显示所选文件或目录的链接
function showTheHrefWhereChoosing( id ){
	var $url = window.location.href;
	found = $url.lastIndexOf('?');
	//alert($url.slice(0, found));
	url_pre = $url.slice(0, found);
	document.getElementById("menus").getElementsByTagName("input").item(0).value = url_pre + "?" + _getRandomString(0) + "=" + id;
}
//更新#url区域的路径导航
function updatePathNavy( id ){
	$.post(
	'./', 
	{xxx:"updatePathNavyInAdminViewFormIdArray|"+id},
	function(data){
		document.getElementById("url").getElementsByTagName("ul").item(0).innerHTML = data;
	});
}

//点击路径导航
function click_path_navy(obj){
	id = obj.getAttribute("id").slice(5);
	loadContentFromDirToRightContent( id );
	updatePathNavy( id );
	document.getElementById("current_dir_id").value = id;
	$parent_id = id;	//记录所进入的目录的id
}

/*********************分割线***********************/
//点击“根目录”按钮
function click_root_button(){
	loadContentFromDirToRightContent(0);
	showTheHrefWhereChoosing(0);
	updatePathNavy(0);
	$parent_id = 0;
}
//在树形索引单击闭合的目录
function click_closed_li(obj){
	var id = obj.getAttribute("id");
	var parent = document.getElementById("li_"+id);
	parent.setAttribute("class", "opened");
	$.post('./', {xxx:id}, function(data){
		var innerr = parent.innerHTML + data;
		parent.innerHTML = innerr;
	});
	loadContentFromDirToRightContent(id);
	showTheHrefWhereChoosing(id);
	updatePathNavy(id);
	obj.setAttribute("onclick", "click_opened_li(this)");
	document.getElementById("current_dir_id").value = id;
	$parent_id = id;	//记录所进目录的id
}
//在树形索引单击展开的目录
function click_opened_li(obj){
	var id = obj.getAttribute("id");
	var parent = document.getElementById("li_"+id);
	parent.setAttribute("class", "closed");
	obj.setAttribute("onclick", "click_closed_li(this)");
	var btn = obj.outerHTML;
	parent.innerHTML = btn;
	loadContentFromDirToRightContent(id);
	showTheHrefWhereChoosing(id);
	updatePathNavy(id);
	document.getElementById("current_dir_id").value = id;
	$parent_id = id;	//记录所进目录的id
}
//在树形索引单击空目录（没有带加号和减号的），仅在右侧打开空的内容
function click_empty_li(obj){
	var id = obj.getAttribute("id");
	loadContentFromDirToRightContent(id);
	showTheHrefWhereChoosing(id);
	updatePathNavy(id);
	document.getElementById("current_dir_id").value = id;
	$parent_id = id;	//记录所进目录的id
}
//在树形索引单击文件，仅显示链接，不产生下载
function click_file_li(obj){
	var id = obj.getAttribute("id");
	showTheHrefWhereChoosing(id);
}

/*********************分割线***********************/

//在#right_content区域单击目录
function v_click_opendir( obj ){
	var id = obj.getAttribute("id").slice(2);
	loadContentFromDirToRightContent(id);
	showTheHrefWhereChoosing(id);
	updatePathNavy(id);
	document.getElementById("current_dir_id").value = id;
	$parent_id = id;	//记录所进目录的id
}
//在#right_content区域单击文件
function v_click_openfile( obj ){
	var id = obj.getAttribute("id").slice(2);
	openFileFromId(id);
	showTheHrefWhereChoosing(id);
}
//在#right_content区域单击空白处
function click_blank_in_right_content(obj){
	$("#dircontext_in_right_content,#filecontext_in_right_content").hide();
	return false;
}
//在#right_content区域右键
function context_blank_in_right_content(obj){
	return false;
}
//right_content区域的图标右键菜单
function icon_context_in_right_content(obj, type){
	scrLeft = document.getElementById("right_content").scrollLeft;
	scrTop = document.getElementById("right_content").scrollTop;
	halfWidth = 30;
	halfHeight = 30;
	ctxWidth = 239;
	ctxHeight = 170;
	x = obj.offsetLeft + halfWidth - scrLeft;
	y = obj.offsetTop + halfHeight - scrTop;
	x = (x + ctxWidth < document.body.clientWidth) ? x : document.body.clientWidth - ctxWidth;
	y = (y + ctxHeight < document.body.clientHeight) ? y : document.body.clientHeight - ctxHeight;
	context = (type=='dir') ? '#dircontext_in_right_content' : '#filecontext_in_right_content';
	$("#dircontext_in_right_content,#filecontext_in_right_content").hide();
	$(context).css({"left":x,"top":y,"display":"block"});
	$selected_id = obj.id.slice(2);	//记录被选中的id
	return false;
}

/*********************分割线***********************/

function createDir(sh, na, sr, ty, id){
	var shell = sh;
	var name = na;
	var src = sr;
	var type = ty;
	var ids = id;
	if(''==name||''==src)
		return false;
	$.post('./', {xxx:shell+"|"+name+"|"+src+"|"+type+"|"+ids}, function(data){
		//捕获添加目录时产生的错误
		if( -1 != data.indexOf("@ErrorAction") ){
			data = data.slice(0, data.length-12);
			document.getElementById("erroraction_msg").innerHTML = data;
			$("#error_action").modal();
			return false;
		}
		loadContentFromDirToRightContent(id);
		updatePathNavy(id);
	});
}

function click_createRealDir( obj ){
	createDir('dirdir', document.getElementById("dirdir_newname").value, document.getElementById("dirdir_realdir").value, 'real', document.getElementById("current_dir_id").value);
}

function click_createVirtualDir( obj ){
	createDir('empdir', document.getElementById("empdir_newname").value, '.nomedia', 'virtual', document.getElementById("current_dir_id").value);
}

/*********************分割线***********************/

//发送“移除目录”的请求
function rmdir(){
	$.post('./', {xxx:"rmdir|"+$selected_id}, function(data){
		if( -1 != data.indexOf("@ErrorAction") ){
			data = data.slice(0, data.length-12);
			document.getElementById("erroraction_msg").innerHTML = data;
			$("#error_action").modal();
			$("#dircontext_in_right_content,#filecontext_in_right_content").hide();
			return false;
		}
		loadContentFromDirToRightContent($parent_id);
		$("#dircontext_in_right_content,#filecontext_in_right_content").hide();
	});	
}
//点击移除目录
function click_rmdir(){
	$("#are_you_sure_title").html("移除目录");
	$("#are_you_sure span").html("该操作将删除所选目录及其内部所有内容，继续？");
	$("#are_you_sure button.btn.btn-primary").attr("onclick", "return rmdir()");
	$("#are_you_sure").modal();
	//alert("$selected_id="+$selected_id+"\n$parent_id="+$parent_id);
}

/*********************分割线***********************/

//处理“从文件添加文件请求”
function filefile(dn, lp, pid){
	$.post('./', {xxx:'filefile|'+dn+'|'+lp+'|'+pid}, function(data){
		//捕获添加目录时产生的错误
		if( -1 != data.indexOf("@ErrorAction") ){
			data = data.slice(0, data.length-12);
			document.getElementById("erroraction_msg").innerHTML = data;
			$("#error_action").modal();
			return false;
		}
		loadContentFromDirToRightContent(pid);
		updatePathNavy(pid);
	});
}
//点击“从文件添加文件”
function click_filefile(){
	dn = $("#filefile_displayname").val();
	lp = $("#filefile_realdir").val();
	filefile(dn, lp, $parent_id);
}