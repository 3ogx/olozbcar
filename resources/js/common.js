function checkMail(value) {
	var pattern = /^([\.a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/;
	if (!pattern.test(value)) {
		return false;
	}
	return true;
}

function checkPhone(number) {
	if (number.length != 11 ) {
		return;
	}

	var pattern = /((\d{11})|^((\d{7,8})|(\d{4}|\d{3})-(\d{7,8})|(\d{4}|\d{3})-(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1})|(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1}))$)/
	if (!pattern.test(number)) {
		return false;
	}

	return true;
}

function checkImei(imei) {
	var pattern = /[\w\d]{1,25}/;
	if (!pattern.test(imei)) {
		return false;
	}
	return true;
}

function checkPlate(number) {
	
	if (number.length != 7 && number.length != 8) {
		return false;
	}
	return true;
	
	var pattern = /[\u4e00-\u9fa5]{1}[\d\w]{6}$/
	if (!pattern.test(number)) {
		return false;
	}
	return true;
}


//自动关闭提示框
function Alert(str, autoclose) {
	var _msgBox = document.getElementById("alertmsgDiv");
	if (_msgBox != null && _msgBox != 'undefined') {
		document.body.removeChild(document.getElementById("alertmsgDiv"));
	}
    var msgw,msgh,bordercolor;
    msgw=350;//提示窗口的宽度
    msgh=120;//提示窗口的高度
    titleheight=25 //提示窗口标题高度
    bordercolor="#336699";//提示窗口的边框颜色
    titlecolor="#99CCFF";//提示窗口的标题颜色
    var sWidth,sHeight;
    //获取当前窗口尺寸
    sWidth = document.body.offsetWidth;
    sHeight = document.body.scrollHeight;
//    //背景div
    var bgObj=document.createElement("div");
    bgObj.setAttribute('id','alertbgDiv');
    bgObj.style.position="absolute";
    bgObj.style.top="0";
    bgObj.style.background="#E8E8E8";
    bgObj.style.filter="progid:DXImageTransform.Microsoft.Alpha(style=3,opacity=25,finishOpacity=75";
    bgObj.style.opacity="0.6";
    bgObj.style.left="0";
    bgObj.style.width = sWidth + "px";
    bgObj.style.height = sHeight + "px";
    bgObj.style.zIndex = "10000";
    document.body.appendChild(bgObj);
    //创建提示窗口的div
    var msgObj = document.createElement("div")
    msgObj.setAttribute("id","alertmsgDiv");
    msgObj.setAttribute("align","center");
    msgObj.style.background="white";
    msgObj.style.border="1px solid " + bordercolor;
    msgObj.style.position = "absolute";
    msgObj.style.left = "50%";
    msgObj.style.font="12px/1.6em Verdana, Geneva, Arial, Helvetica, sans-serif";
    //窗口距离左侧和顶端的距离
    msgObj.style.marginLeft = "-225px";
    //窗口被卷去的高+（屏幕可用工作区高/2）-150
    msgObj.style.top = document.body.scrollTop+(window.screen.availHeight/2)-150 +"px";
	// msgObj.style.top = document.body.clientHeight+(window.screen.availHeight/2)-150 +"px";
    msgObj.style.width = msgw + "px";
    msgObj.style.height = msgh + "px";
    msgObj.style.textAlign = "center";
    msgObj.style.lineHeight ="25px";
    msgObj.style.zIndex = "10001";
    document.body.appendChild(msgObj);
    //提示信息标题
    var title=document.createElement("h4");
    title.setAttribute("id","alertmsgTitle");
    title.setAttribute("align","left");
    title.style.margin="0";
    title.style.padding="3px";
    title.style.background = bordercolor;
    title.style.filter="progid:DXImageTransform.Microsoft.Alpha(startX=20, startY=20, finishX=100, finishY=100,style=1,opacity=75,finishOpacity=100);";
    title.style.opacity="0.75";
    title.style.border="1px solid " + bordercolor;
    title.style.height="18px";
    title.style.font="12px Verdana, Geneva, Arial, Helvetica, sans-serif";
    title.style.color="white";
    title.innerHTML="提示信息";
    document.getElementById("alertmsgDiv").appendChild(title);
    //提示信息
    var txt = document.createElement("p");
    txt.setAttribute("id","msgTxt");
    txt.style.margin="16px 0";
    txt.innerHTML = str;
    document.getElementById("alertmsgDiv").appendChild(txt);

	var btn = document.createElement("button");
	btn.setAttribute("id", "msgBtn");
	btn.setAttribute("onclick", "closewin();");
	btn.innerHTML = '确定';
	btn.style.font="12px Verdana, Geneva, Arial, Helvetica, sans-serif";
	btn.style.padding="3px 7px";
	btn.style.border="1px solid "+bordercolor;
    document.getElementById("alertmsgDiv").appendChild(btn);
	if (autoclose) {
		//设置关闭时间
		window.setTimeout("closewin()",2000);
	}
}
function closewin() {
    document.body.removeChild(document.getElementById("alertbgDiv"));
	if (document.getElementById("alertmsgDiv") != null ) {
		document.getElementById("alertmsgDiv").removeChild(document.getElementById("alertmsgTitle"));
		document.body.removeChild(document.getElementById("alertmsgDiv"));
	}
}

jQuery(function($) {
	var _ajax = $.ajax;
	$.ajax = function(opt) {
		var _success = opt && opt.success || function(a, b){};
		var _opt = $.extend(opt, {  
            success:function(data, textStatus){  
                // 如果后台将请求重定向到了登录页，则data里面存放的就是登录页的源码，这里需要找到data是登录页的证据(标记)  
                if(data.indexOf('autologin') != -1) {  
					$( "#dialog-message" ).dialog({
						modal: true,
						buttons: {
							"ok": function() {
								window.location.href= "login.php";  
							}
						},
						close:function() {
							window.location.href= "login.php";  
						}
					});
                    return;  
                }  
                _success(data, textStatus);    
            }    
        }); 
		_ajax(_opt);
	}
});
