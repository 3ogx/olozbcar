<?php
include_once('config.php');
@session_start();
header("Server: xxxx");
if ($_COOKIE['autologin']==1 && !$_POST){
	$_REQUEST['Account']=$_COOKIE['Account'];
	$_REQUEST['password']=$_COOKIE['password'];
	$_REQUEST['autologin']=$_COOKIE['autologin'];
	$_REQUEST['savepwd']=$_COOKIE['savepwd'];
	$_SESSION['LQ_1']=0;	
	
}  
if ($_REQUEST['act']=='exit'){
	$_SESSION['zbAcc']='';
	$_SESSION['zbPwd']='';
	$_REQUEST['Account']=$_COOKIE['Account'];
	$_REQUEST['password']=$_COOKIE['password'];
	$_REQUEST['autologin']=$_COOKIE['autologin'];
	$_REQUEST['savepwd']=$_COOKIE['savepwd'];
}else if(strlen($_REQUEST['Account'])>0){	
	$_SESSION['LQ_1']=$_SESSION['LQ_1']+1;
	require_once("zbapi.php");
	if(($_SESSION['LQ_1']>55)&&($_REQUEST['code']!=$_SESSION['randcode'])) { 
		$JS="alert('\u9a8c\u8bc1\u7801\u9519\u8bef\uff01');window.document.location.href='login.php';";
	} else {	
		$Account=$_REQUEST['Account'];
		$password=$_REQUEST['password'];
		$zbapi=new zbapi($Account,$password);
		if ($zbapi->login()!=0){

			$JS= "alert('\u5e10\u53f7\u6216\u5bc6\u7801\u9519\u8bef\uff01');";	 
		} else{
			$_SESSION['zbAcc']=$zbapi->uniqAcc;
			$_SESSION['zbPwd']=$password;
			$_SESSION['zbPhone']=$zbapi->phone;
			$_SESSION['zbUsername']=$zbapi->username;
			$_SESSION['zbRole']=$zbapi->role;
			if ($_REQUEST['savepwd']){
				setcookie("Account",$Account,time()+3600*24*365);
				setcookie("password",$password,time()+3600*24*365);
				setcookie("autologin",$_REQUEST['autologin'],time()+3600*24*365);
				setcookie("savepwd",1,time()+3600*24*365);

			} else{
				setcookie("Account",'',time()+3600*24*365);
				setcookie("password",'',time()+3600*24*365);
				setcookie("autologin",’‘,time()+3600*24*365);
				setcookie("savepwd",0,time()+3600*24*365);
			}  
			echo  "<script>window.document.location.href='index.php';</script>"; 
			exit();
		} 

	}
}else {
	$_REQUEST['Account']=$_COOKIE['Account'];
	$_REQUEST['password']=$_COOKIE['password'];
	$_REQUEST['autologin']=$_COOKIE['autologin'];
	$_REQUEST['savepwd']=$_COOKIE['savepwd'];
}?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta charset="UTF-8">
<title>卓比汽车在线</title>
<link rel="stylesheet" href="resources/css/style.css" type="text/css" media="screen" />
<script type="text/javascript" src="resources/js/jquery-1.11.1.min.js"></script>
</head>
  
<body>
 <form name="form1" id="form1" method="post" action="login.php">
	<div class="all_top">
		<div class="logoDiv">
			<div class="con_in">
				<img src="resources/images/logo.jpg" alt="" class="logo"/>
			</div>
		</div>
	  <div class="con_in">
	  	<div class="right loginDiv">
	  		
	  		  <input type="text" placeholder="账号" class="register" style="margin-top:30px;"  id="Account" name="Account" value="<?php  if ($_REQUEST['Account']) echo $_REQUEST['Account'];?>"/>
	  		  <span id="pwddiv"><input type="password" placeholder="密码" class="register"  id="password" name="password" value="<?php if($_REQUEST['password']) echo $_REQUEST['password'];?>"/></span>
	  		  <div class="remember">
	  		  	<div class="left">
	  		  		<input type="checkbox" name="savepwd" id="savepwd" value="1" <?php if ($_REQUEST['savepwd']==1) echo 'checked="checked"';?>/>
	  		  		记住密码
	  		  	</div>
	  		  	<div class="right">
	  		  		<input type="checkbox" name="autologin" id="autologin" value="1"  <?php if ($_REQUEST['autologin']==1) echo 'checked="checked"';?>/>
	  		  		自动登录
	  		  	</div>
	  		  </div>
	  	    <button class="loginbtn" type="submit"></button>
	  	    <div class="lastdiv">
	  	    		<a href="register.php">注册</a>
	  	    		<a href="repwd.php">忘记密码</a>
	  	    		<a href="javascript:;" style="text-align: right;">我要体验</a>
	  	    </div>
	  	</div>
	  </div>
	</div> 
	<div class="footer">
		 <div class="code">
	    	<ul>
	    		<li>
	    			<img src="resources/images/er1.jpg" class="left"/>
	    			<div class="appDIV">
	    				 手机APP
	    			</div>
	    		</li>
	    		<li>
	    			<img src="resources/images/er2.jpg" class="left"/>
	    			<div class="appDIV">
	    				 微信公众平台
	    			</div>
	    		</li>
	    	</ul>
	    </div>

		<div class="con_in">
			<img src="resources/images/logo2_03.jpg"/>
		</div>
	</div>
	</form>
<script type="text/javascript" src="resources/js/placeholder.js"></script>
</body>
</html>
	<script>


//function pwdonfocus(){
//        return;
//        if($("#password").val() == '密码'){
//            $("#pwddiv").html('<input type="password" onblur="pwdonblur();" onfocus="pwdonfocus();" class="register"  id="password" name="password" value=""/>');
//        }
//        $("#password")[0].focus();
//    };
//    function pwdonblur(){
//        return;
		
//         if($("#password").val() == ''){
//            $("#pwddiv").html('<input type="text"  class="register" onblur="pwdonblur();" onfocus="pwdonfocus();" id="password" name="password" value="密码"/>');
             
//        }
            
       
//    };

//    $("#password").focus(function(){
//        var _old_value = $(this).val();
//        if (_old_value == this.defaultValue) {
//            $(this).val("");
//        }
//}).blur(function(){
//        var _old_value = $(this).val();
//        if (_old_value == "") {
//            $(this).val(this.defaultValue);
//        }
//});

$("#form1").submit(function(event){
	var _account = $("#Account").val() || '';
	var _pwd = $('#password').val() || '';

	if (_account == '' || _account == '账号' ) {
		alert('请输入账号！');
		$("#Account")[0].focus();
		event.preventDefault();
		return;
	}

	if (_pwd == '' || _pwd == '密码' ) {
		alert('请输入密码！');
		$("#password").val("");
		$("#password")[0].focus();
		event.preventDefault();
		return;
	}

	return;
});

$(function(){
	
	var inp1 = document.getElementById('Account');
    var inp2 = document.getElementById('password');
	
	
    //inp1.onfocus = function(){
    //    if(inp1.value == '账号'){
    //        inp1.value ='';
    //        inp1.style.color = '#000';
    //    }
    //};
    //inp1.onblur = function(){
    //    if(inp1.value == ''){
    //        inp1.value = '账号';
    //        inp1.style.color = '#808080';
    //    }
    //};
    
    
	var H = $(window).height();
	var H = $(window).height();
	var logoDiv = $('.logoDiv').height();
	var footer = $('.footer').height();
	//$('.all_top').css('min-height',H-logoDiv-footer);
    if(H<843){
    	$('.loginDiv').css('margin','1% 0 0 0');
    	$('.passwordDIV').css('margin','2% auto');
    }
	//if(inp1.value == '账号') inp1.style.color = '#808080';
	//else inp1.style.color = '#000';	
	
	if(inp2.value == '密码') {
		
		//$("#pwddiv").html('<input type="text" style="color:#808080" onblur="pwdonblur();" onfocus="pwdonfocus();" class="register"  id="password" name="password" value="密码"/>');
		
	}
	else{
		//var v=inp2.value;
		//$("#pwddiv").html('<input type="password" onblur="pwdonblur();" onfocus="pwdonfocus();" class="register"  id="password" name="password" style="color:#000" value="'+v+'"/>');
		 	
	} 
	window.setTimeout(function(){
		<?php echo $JS;?> },50);
	$('#savepwd').click(function (){if(!this.checked) autologin.checked=false;});	
	$('#autologin').click(function (){if(this.checked) savepwd.checked=true;});	
})
  
</script>
