function _getRandomString(len){
	len = len || 128;
	var $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
	var maxPos = $chars.length;
	var pwd = '';
	for(i=0;i<len;i++){
		pwd += $chars.charAt(Math.floor(Math.random()*maxPos));
	}
	return pwd;
}

