<?php
include_once('config.php');
require_once("zbapi.php");
$regok=0;
if ($_POST){
	if ($_REQUEST['Account']=='') $JS= "alert('帐号不能为空！');";
	else if ($_REQUEST['password']=='') $JS= "alert('密码不能为空！');";
	else if (strlen($_REQUEST['password'])<6) $JS= "alert('密码最少6位！');";
	else if ($_REQUEST['Name']=='') $JS= "alert('用户名不能为空！');";
	else if ($_REQUEST['Phone']=='') $JS= "alert('手机号不能为空！');";
	else if ($_REQUEST['Email']=='') $JS= "alert('Email不能为空！');";
	else {

		$zbapi=new zbapi($Account,$password);
		$ret=json_decode($zbapi->req(142,'{"Account":"'.$_REQUEST['Account'].'","Pwd":"'.$_REQUEST['password'].'","Name":"'.$_REQUEST['Name'].'","Phone":"'.$_REQUEST['Phone'].'","Email":"'.$_REQUEST['Email'].'"}'),true);
		if ($ret['Code']=='0'){
			 $regok=1;
			 
		} else $JS= "alert('".errstr($ret['Code'])."');";
	}
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta charset="UTF-8">
<title>卓比汽车在线</title>
<link rel="stylesheet" href="resources/css/style.css" type="text/css" media="screen" />
<script type="text/javascript" src="resources/js/jquery-1.11.1.min.js"></script>
<script src="resources/js/common.js"></script>
<script type="text/javascript" src="resources/js/zhuobi.js"></script>
</head>
  
<body>
<form name="form1" id="form1" method="post" >
	<div class="all_top">
		<div class="logoDiv">
			<div class="con_in">
				<img src="resources/images/logo.jpg" alt="" class="logo"/>
			</div>
		</div>
	  <div class="con_in" id="idmain" <?php if ($regok) echo 'style="display:none"'; ?>>
	    <div class="passwordDIV passwordDIV1">
	    	<span id="close" onclick="window.document.location.href='login.php?act=exit';">
	    		X
	    	</span>
	    	<div class="info">
	    		账号<span class="red" style="margin:0 10px 0 5px;">※</span>
	    		<input type="text" id="Account" name="Account" maxlength="30" value="<?php echo $_REQUEST['Account']?>"  
				   onkeyup="if (this.value.match(/[^0-9a-zA-Z:@\-_]/g))this.value=this.value.replace(/[^0-9a-zA-Z:@\-_]/g,'');"  onafterpaste="if (this.value.match(/[^0-9a-zA-Z:@\-_]/g))this.value=this.value.replace(/[^0-9a-zA-Z:@\-_]/g,'');"/>
	    	</div>
	    	<div class="info">
	    		密码<span class="red" style="margin:0 10px 0 5px;">※</span>
	    		<input type="password" id="password" name="password" maxlength="64" value="<?php echo $_REQUEST['password']?>" />
	    	</div>
	    	<div class="info">
	    		名称<span class="red" style="margin:0 10px 0 5px;">※</span>
	    		<input type="text" id="name" name="Name" maxlength="50" value="<?php echo $_REQUEST['Name']?>" />
	    	</div>
	    		<div class="info">
	    		邮箱<span class="red" style="margin:0 10px 0 5px;">※</span>
	    		<input type="text"   id="Email" name="Email" maxlength="200" value="<?php echo $_REQUEST['Email']?>" />
	    	</div>
	    		<div class="info">
	    		手机号<span class="red" >※</span>
	    		<input type="text" id="Phone" Name="Phone" maxlength="15" value="<?php echo $_REQUEST['Phone']?>" onkeydown="isNumber1(event);"  style="ime-mode:disabled" 
onkeyup="if (this.value.match(/[^0-9]/g))this.value=this.value.replace(/[^0-9]/g,'');" onpaste="return false"/>
	    	</div>
	    	<button class="passbtn" type="submit">提交</button>
	    </div>  
	  </div>
	  </form>
	  <div class="con_in" id="idpromt"  <?php if (!$regok) echo 'style="display:none"'; ?>>
	    <div class="passwordDIV">
	    	<span id="close" onclick="window.document.location.href='login.php?act=exit';">
	    		X
	    	</span>
	    	<img src="resources/images/img_03.jpg"/>
	    	<button type="button" class="loginbtn loginbtn2" onclick="window.document.location.href='login.php?Account=<?php echo $_REQUEST['Account']?>&password=<?php echo $_REQUEST['password'];?>';">注册成功</button>
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
	
</body>
</html>
<script type="text/javascript">
function isNumber1(evt)    
{    
     var iKeyCode = window.event?evt.keyCode:evt.which;    

  
         if((iKeyCode>=48) && (iKeyCode<=57) || (iKeyCode>=96) && (iKeyCode<=105) || (iKeyCode>=37) && (iKeyCode<=40) || iKeyCode===8|| iKeyCode===9 || iKeyCode==46 || iKeyCode==16 ) 
        {     
    }    
    else    
    {    
            if (window.event) //IE    
            {    
                event.returnValue = false;    
            }    
            else //Firefox    
            {    
    
                evt.preventDefault();    
            }    
        }    
}
$("#Account").focus();

$("#form1").submit(function(event){

	var mail = phone = '';
	mail = $("#Email").val();
	phone = $("#Phone").val();

	if (mail == '' || !checkMail(mail)) {
		alert('Email 格式不正确!');
		event.preventDefault();
	}

	if (phone == '' || !checkPhone(phone)) {
		alert("电话号码不正确!");
		event.preventDefault();
	}
});
$(function(){
	  var H = $(window).height();
	  var logoDiv = $('.logoDiv').height();
	  var footer = $('.footer').height();
    $('.all_top').css('height',H-logoDiv-footer);
    if(H<843){
    	$('.loginDiv').css('margin','1% 0 0 0');
    	$('.passwordDIV').css('margin','2% auto');
    }
	window.setTimeout(function(){
		<?php echo $JS;?> },50);
})
 <?php if ($regok) echo ' window.setTimeout(function(){window.document.location.href="login.php?Account='.$_REQUEST['Account'].'&password='.$_REQUEST['password'].'";},5000);' ?> 
</script>
