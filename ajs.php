<?php
include_once('config.php');
@session_start();
if ($_SESSION['zbAcc']==''){
	$host = $_SERVER['HTTP_HOST'];
	header("Location: http://{$host}/login.php");
	exit;
} 
$act=intval($_REQUEST['act']);
switch ($act){
case 9001:
	$skip=intval($_REQUEST['page'])*10-10;
	$key=trim($_REQUEST['key']);
	if (apc_exists('zb_devinfo'.$_SESSION['zbAcc']))
		$devinfo=apc_fetch('zb_devinfo'.$_SESSION['zbAcc']);
	else {
		require_once("zbapi.php");
		$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
		$zbapi->login();
		$zbapi->loadDevInfo();
		$devinfo=$zbapi->devinfo;
		apc_store('zb_devinfo'.$_SESSION['zbAcc'],$zbapi->devinfo,600);
	}
	
	
	switch (intval($_REQUEST['panel'])){
		case 0:
		case 3:
			//$devinfo=apc_fetch('zb_devinfo'.$_SESSION['zbAcc']);
			$i=0; 
			
			foreach($devinfo as $v){
				if (!$key || strpos('a'.$v['IMEI'],$key) ||  strpos('a'.$v['Num'],$key)){
					if ($skip==0){
						$i++;
						if ($i>10) break;
						if (!$v['Num']) {
							$v['Num'] = '未知';
						}
						if ($v['State']){
							if ($v['State']==2) $Stat='休眠';
							else $Stat='在线';
							if (intval($_REQUEST['panel'])==3)
								echo "<p onclick=\"track('$v[IMEI]');\"><img  src='resources/images/caron.png'  style='width:16px;padding:0px 5px;' alt='' class='left'><span class='left'>$v[IMEI]</span><span class='left' style='padding-left:10px;'>$v[Num]</span><span class='right'>$Stat</span></p>";
							else 
							echo "<p onclick=\"locateTerm('$v[IMEI]',$v[State]);\"><img  src='resources/images/caron.png'  style='width:16px;padding:0px 5px;' alt='' class='left'><span class='left'>$v[IMEI]</span><span class='left' style='padding-left:10px;'>$v[Num]</span><span class='right'>$Stat</span></p>";
						} 
						else {
							if (intval($_REQUEST['panel'])==3)
								 echo "<p onclick=\"track('$v[IMEI]');\"><img src='resources/images/caroff.png'  style='width:16px;padding:3px 12px 0px 9px;' alt='' class='left'><span class='left'>$v[IMEI]</span><span class='left' style='padding-left:10px;'>$v[Num]</span><span class='right'>离线</span></p>";
							else 
							 echo "<p onclick=\"locateTerm('$v[IMEI]',$v[State]);\"><img src='resources/images/caroff.png'  style='width:16px;padding:3px 12px 0px 9px;' alt='' class='left'><span class='left'>$v[IMEI]</span><span class='left' style='padding-left:10px;'>$v[Num]</span><span class='right'>离线</span></p>";
						}	
					} else $skip--;
				}
				  
			}
		break;
		
		case 1:
			//$devinfo=apc_fetch('zb_devinfo'.$_SESSION['zbAcc']);
			$i=0; 
			
			foreach($devinfo as $v){
				if (!$key || strpos('a'.$v['IMEI'],$key) ||  strpos('a'.$v['Num'],$key)){
					if ($skip==0){
						if ($v['State']){
							$i++;
							if ($i>10) break;
							if (!$v['Num']) {
								$v['Num'] = '未知';
							}
							if ($v['State']==2) $Stat='休眠';
							else $Stat='在线';
							echo "<p onclick=\"locateTerm('$v[IMEI]',$v[State]);\"><img src='resources/images/caron.png'  style='width:16px;padding:0px 5px;' alt='' class='left'><span class='left'>$v[IMEI]</span><span class='left' style='padding-left:10px;'>$v[Num]</span><span class='right'>$Stat</span></p>";
						} 	
					} else $skip--;
				}
				  
			}
		break;
		case 2:
			//$devinfo=apc_fetch('zb_devinfo'.$_SESSION['zbAcc']);
			$i=0; 
			
			foreach($devinfo as $v){
				if (!$key || strpos('a'.$v['IMEI'],$key) ||  strpos('a'.$v['Num'],$key)){
					if ($skip==0){
						if (!$v['State']){
							$i++;
							if ($i>10) break;
							if (!$v['Num']) {
								$v['Num'] = '未知';
							}
							 echo "<p><img src='resources/images/caroff.png'  style='width:16px;padding:3px 12px 0px 9px;' alt='' class='left'><span class='left'>$v[IMEI]</span><span class='left' style='padding-left:10px;'>$v[Num]</span><span class='right'>离线</span></p>";
						} 		
					} else $skip--;
				}
			}
		break;
	
	}
	
 break;
 
case 9002:
	if (apc_exists('zb_devinfo'.$_SESSION['zbAcc']))
		$devinfo=apc_fetch('zb_devinfo'.$_SESSION['zbAcc']);
	else {
		require_once("zbapi.php");
		$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
		$zbapi->login();
		$zbapi->loadDevInfo();
		$devinfo=$zbapi->devinfo;
		apc_store('zb_devinfo'.$_SESSION['zbAcc'],$zbapi->devinfo,600);
	}
	
echo '<table border="1" cellspacing="0" cellpadding="0" ><tr>';
	if ($_SESSION['zbRole']=='0') echo '<th><input onclick="selAllTerm(this)"  type="checkbox" />全选</th>';
	echo '<th>设备编号</th><th>ACC状态</th><th>车牌号 </th><th>设备号码</th><th>监听号码</th><th>周期定位</th><th>最后位置</th><th>最后定位时间</th><th>电量</th>';
	if ($_SESSION['zbRole']=='0') echo '<th>操作</th>';
	echo '</tr>';
	$i=0;
	require_once 'page.php'; 
	//global $page;
	$page=intval($_REQUEST['page']);
	$skip=$page*10-10;
	if ($skip<0) $skip=0; 
	foreach($devinfo as $v){
		if ($skip==0){
			$i++;
			if ($i>10) break;
			$book=str_replace("[",'',$v['PhoneBook']);//implode(';',$v['PhoneBook']);$Txt=str_replace("；",';',$_REQUEST['Book']);
			$book=str_replace("]",'',$book);
			$book=str_replace('"','',$book);
			$book=str_replace(';',',',$book);
			$book=str_replace(',,',',',$book);
			
			if ($v['AccType']==2) $Stat='';
			elseif ($v['AccType']==0) $Stat='关闭';
			else  $Stat='打开';
			if ($v['LoopType']==0) $LoopDes='取消';
			else if ($v['LoopType']==1) $LoopDes="按月 $v[LoopValue]号";
			else if ($v['LoopType']==2){
				if ($v['LoopValue']==7) $LoopDes="按周 周日";
				else $LoopDes="按周 周$v[LoopValue]";
			} else $LoopDes="按天 $v[LoopValue]";
			
			echo "<tr>";
			if ($_SESSION['zbRole']=='0') echo "<td><input  id='termIds' name='termIds' type='checkbox' value='$v[IMEI]'/></td><td>$v[IMEI]</td>";
			echo "<td>$Stat</td><td>$v[Num]</td><td>$v[DevPhone]</td><td>$book</td><td>$LoopDes</td><td>$v[Addr]</td><td>$v[Time]</td><td>$v[Power]%</td>";
				if ($_SESSION['zbRole']=='0') echo "<td><a href='javascript:' style='margin-right:5px;' onclick='OpenSwithCtrl(\"$v[IMEI]\", \"$v[ReplayState]\");'>[继电器控制]</a><a href='javascript:' style='margin-right:5px;' onclick='reSetIMEIFac(\"$v[IMEI]\");'>[恢复出厂设置]</a><a href='javascript:' style='margin-right:5px;' onclick='editSafeArea(\"$v[IMEI]\");'>[安全区域]</a><a href='javascript:' style='margin-right:5px;' onclick='startIMEIWatch(\"$v[IMEI]\");'>[监听]</a><a href='javascript:' style='margin-right:5px;' onclick='editTerm((\"$v[IMEI]\",\"$v[Num]\"))'>[修改]</a> <a href='javascript:' onclick='delIMEITerm(\"$v[IMEI]\")'>[删除]</a> </td>";
				echo "</tr>";
			} else $skip--;
		}
    echo '</table><div style="width:100%;height:30px;"><div class="pager">';
	if ($_SESSION['zbRole']=='0') echo '<a href="#" onclick="newTerm();">添加设备</a><a href="#" onclick="importTerm();">导入设备</a><a href="#" onclick="delSelTerm();" style="margin-right:30px;">删除选中项</a>';
	
	pageft(count($devinfo),10,1,0,1,10,'','termGotoPage');
	echo $pagenav,'</div></div>';	
break;
case 9003:
	$key=trim($_REQUEST['Key']);
	$Group=intval($_REQUEST['Group']);
	
	if (apc_exists('zb_devinfo'.$_SESSION['zbAcc']))
		$devinfo=apc_fetch('zb_devinfo'.$_SESSION['zbAcc']);
	else {
		require_once("zbapi.php");
		$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
		$zbapi->login();
		$zbapi->loadDevInfo();
		$devinfo=$zbapi->devinfo;
		apc_store('zb_devinfo'.$_SESSION['zbAcc'],$zbapi->devinfo,600);
	}
	
	
	echo '<table border="1" cellspacing="0" cellpadding="0" ><tr>';
	if ($_SESSION['zbRole']=='0') echo '<th><input onclick="selAllTerm(this)"  type="checkbox" />全选</th>';
	echo '<th>设备编号</th><th>车牌号 </th><th>设备号码</th><th>监听号码</th><th>周期定位</th><th>最后位置</th><th>最后定位时间</th><th>ACC状态</th><th>开通时间</th><th>电量</th>';
	if ($_SESSION['zbRole']=='0') echo '<th>操作</th>';
	echo '</tr>';
	$i=0;
	require_once 'page.php'; 
	//global $page;
	$page=intval($_REQUEST['page']);
	$skip=$page*10-10;
	$Total=0;
	if ($skip<0) $skip=0; 
	foreach($devinfo as $v){
		if (($Group==0 || $Group==$v['GroupId'])&& (!$key || strpos('a'.$v['IMEI'],$key) ||  strpos('a'.$v['Num'],$key))){
			$Total++;
			if ($skip==0){
				$i++;
				if ($i<11){ 
					$book=str_replace("[",'',$v['PhoneBook']);//implode(';',$v['PhoneBook']);$Txt=str_replace("；",';',$_REQUEST['Book']);
					$book=str_replace("]",'',$book);
					$book=str_replace('"','',$book);
					$book=str_replace(';',',',$book);
					$book=str_replace(',,',',',$book);
					if ($v['AccType']==2) $Stat='空';
					elseif ($v['AccType']==0) $Stat='关闭';
					else  $Stat='打开';
			
					if ($v['LoopType']==0) $LoopDes='取消';
					else if ($v['LoopType']==1) $LoopDes="按月 $v[LoopValue]号";
					else if ($v['LoopType']==2){
						if ($v['LoopValue']==7) $LoopDes="按周 周日";
						else $LoopDes="按周 周$v[LoopValue]";
					} else $LoopDes="按天 $v[LoopValue]";
				
					
					echo "<tr>";
					if ($_SESSION['zbRole']=='0') echo "<td><input  id='termIds' name='termIds' type='checkbox' value='$v[IMEI]'/></td>";
					echo "<td>$v[IMEI]</td><td>$v[Num]</td><td>$v[DevPhone]</td><td>$book</td><td>$LoopDes</td><td>$v[Addr]</td><td>$v[Time]</td><td>$Stat</td><td>$v[EnableTime]</td><td>$v[Power]%</td>";
					if ($_SESSION['zbRole']=='0') echo "<td><a href='javascript:' style='margin-right:5px;' onclick='OpenSwithCtrl(\"$v[IMEI]\");'>[继电器控制]</a><a href='javascript:' style='margin-right:5px;' onclick='reSetIMEIFac(\"$v[IMEI]\");'>[恢复出厂设置]</a><a href='javascript:' style='margin-right:5px;' onclick='editSafeArea(\"$v[IMEI]\");'>[安全区域]</a><a href='javascript:' style='margin-right:5px;' onclick='startIMEIWatch(\"$v[IMEI]\");'>[监听]</a><a href='javascript:' style='margin-right:5px;' onclick='editTerm(\"$v[IMEI]\",\"$v[Num]\")'>[修改]</a> <a href='javascript:' onclick='delIMEITerm(\"$v[IMEI]\")'>[删除]</a> </td>";
					echo "</tr>";
					}
			} else $skip--;
		}
		}
    echo '</table><div style="width:100%;height:30px;"><div class="pager">';
	if ($_SESSION['zbRole']=='0') echo '<a href="#" onclick="newTerm()">添加设备</a><a href="#" onclick="importTerm();">导入设备</a><a href="#" onclick="delSelTerm();" style="margin-right:30px;">删除选中项</a>';
	
	pageft($Total,10,1,0,1,10,'','termGotoPage');
	echo $pagenav,'</div></div>';	
break;  
case 9004:

	$Total=0;
	$Online=0;
	
	if (apc_exists('zb_devinfo'.$_SESSION['zbAcc']))
		$devinfo=apc_fetch('zb_devinfo'.$_SESSION['zbAcc']);
	else {
		require_once("zbapi.php");
		$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
		$zbapi->login();
		$zbapi->loadDevInfo();
		$devinfo=$zbapi->devinfo;
		apc_store('zb_devinfo'.$_SESSION['zbAcc'],$zbapi->devinfo,600);
	}
	
	$key=trim($_REQUEST['key']);
	
	foreach($devinfo as $v){
		if (!$key || strpos('a'.$v['IMEI'],$key) ||  strpos('a'.$v['Num'],$key)){
			$Total++;
			if ($v['State']) $Online++;
		}
		  
	}

	echo "$Total,$Online";
break;
case 9005:

	$Total=0;
	$Online=0;
	if (apc_exists('zb_devinfo'.$_SESSION['zbAcc']))
		$devinfo=apc_fetch('zb_devinfo'.$_SESSION['zbAcc']);
	else {
		require_once("zbapi.php");
		$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
		$zbapi->login();
		$zbapi->loadDevInfo();
		$devinfo=$zbapi->devinfo;
		apc_store('zb_devinfo'.$_SESSION['zbAcc'],$zbapi->devinfo,600);
	}
	
	
	$key=trim($_REQUEST['key']);
	
	foreach($devinfo as $v){
		if (!$key || strpos('a'.$v['IMEI'],$key) ||  strpos('a'.$v['Num'],$key)){
			$Total++;
			if ($v['State']) $Online++;
		}
		  
	}
	
	echo "$Total";
break;

case 9006:
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	//$userinfo=apc_fetch('zb_userinfo'.$_SESSION['zbAcc']);
	$userinfo = $zbapi->loadUserInfo();
	echo '<table border="1" cellspacing="0" cellpadding="0" ><tr>';
	if ($_SESSION['zbRole']=='0') echo '<th><input onclick="selAllTerm(this)"  type="checkbox"/>全选</th>';
	echo '<th>帐号</th><th>用户类别</th><th>用户名称</th><th>手机号码</th><th>Email</th>';
	if ($_SESSION['zbRole']=='0') echo '<th>操作</th>';
	echo '</tr>';
	$i=0;
	require_once 'page.php'; 
	$key=trim($_REQUEST['key']);
	//global $page;
	$total=0;
	$page=intval($_REQUEST['page']);
	$skip=$page*10-10;
	if ($skip<0) $skip=0; 
	foreach($userinfo as $v){
		if (!$key || strpos('a'.$v['Name'],$key) ||  strpos('a'.$v['Phone'],$key)){
			$total++;
			if ($skip==0){
				$i++;
				if ($i<11) {
					if ($v['Role'])$usertype='普通用户';
					else $usertype='管理员'; 
					if (($v['Phone']===$_SESSION['zbAcc'] || $v['Account']===$_SESSION['zbAcc'])){
						$Choosdis='disabled="disabled"';
						$me='<b style="color:#666">(我)</b>';
					}
					else{
						$me='';
						$Choosdis='';
					} 
					echo "<tr>";
					if ($_SESSION['zbRole']=='0')  echo "<td><input type='checkbox' $Choosdis id='userIds' name='userIds'  value='$v[Phone]'/></td>"; 
					echo "<td>$v[Account]$me ";
					 echo "</td><td>$usertype</td><td>$v[Name]</td><td>$v[Phone]</td><td>$v[Email]</td>";
					if ($_SESSION['zbRole']=='0'){
						
						echo "<td><a href='javascript:' style='margin-right:5px;' onclick='editUser(\"$v[Account]\",\"$v[Name]\",\"$v[Phone]\",\"$v[Email]\",$v[Role])'>[修改]</a> "; if ($v['Phone']!=$_SESSION['zbAcc'] && $v['Account']!=$_SESSION['zbAcc']) echo"<a href='javascript:' onclick='delUserName(\"$v[Phone]\")'>[删除]</a> ";
						if (($v['Name']==$_SESSION['zbUsername'])&&($v['Phone']==$_SESSION['zbPhone'] || $v['Account']==$_SESSION['zbAcc'])) {
							echo "<a href='javascript:' style='margin-right:5px;' onclick='changeUserPer(\"$v[Phone]\")'>[转移权限]</a>";
						}
					   	echo "</td>";
					}
					echo "</tr>";
				}
				} else $skip--;
		}
		}
    echo '</table><div style="width:100%;height:35px;"><div class="pager">';
	if ($_SESSION['zbRole']=='0') echo '<a href="#" onclick=\'newUser("");\'>添加用户</a><a href="#" onclick=\'delSelUser();\' style="margin-right:30px;">删除选中用户</a>';
	
	
	pageft($total,10,1,0,1,10,'','userGotoPage');
	echo $pagenav,'</div></div>';	
break;
case 9007:
	$userAcc=$_REQUEST['Acc'];
	$userName=$_REQUEST['Name'];
	$userPwd=$_REQUEST['Pwd'];
	$userPhone=$_REQUEST['Phone'];
	$userMail=$_REQUEST['Mail'];
	$userType=$_REQUEST['Type'];

	//echo '{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}';exit;
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	
	$ret=json_decode($zbapi->req(133,'{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}'),true);
	//$ret=json_decode($zbapi->req(9,'{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","Phone":"'.$userPhone.'","Role":'.$userType.'}'),true);
	if ($ret['Code']=='0') {
	$userinfo=$zbapi->loadUserInfo();
	apc_store('zb_userinfo'.$_SESSION['zbAcc'],$userinfo);	
	echo '添加用户成功！';
	}
	else echo errstr($ret['Code']);
break;
case 9008:
	$Phone=$_REQUEST['Phone'];
	
	//echo '{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}';exit;
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$ret=json_decode($zbapi->req(8,'{"Account":"'.$_SESSION['zbAcc'].'","Phone":"'.$Phone.'"}'),true);
	//$ret=json_decode($zbapi->req(133,'{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}'),true);
	if ($ret['Code']=='0'){
		$userinfo=$zbapi->loadUserInfo();
		apc_store('zb_userinfo'.$_SESSION['zbAcc'],$userinfo);	
		echo '删除用户成功！';
	} 
	else echo errstr($ret['Code']);
break;
case 9009:
	$Phone=$_REQUEST['Phone'];
	
	//echo '{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}';exit;
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$ret=json_decode($zbapi->req(16,'{"Account":"'.$_SESSION['zbAcc'].'","Phone":"'.$Phone.'"}'),true);
	if ($ret['Code']=='0'){
		$userinfo=$zbapi->loadUserInfo();
		apc_store('zb_userinfo'.$_SESSION['zbAcc'],$userinfo);	
		$_SESSION['zbRole']=1;
		echo '转移权限成功！';
	} 
	else echo errstr($ret['Code']);
break;

case 9010:
	$IMEI=trim($_REQUEST['IMEI']);
	$TrackDate=$_REQUEST['TrackDate'];
	$curD=strtotime($TrackDate);
	$yy=date('Y',$curD);
	$mm=date('m',$curD);
	$dd=date('d',$curD);
	//echo '{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}';exit;
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	$times='';
	$addrs='';
	$disp='';
	$bakt=-1;
	$ret=json_decode($zbapi->req(17,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Year":'.$yy.',"Mon":'.$mm.',"Day":'.$dd.'}'),true);//$zbapi->req(17,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Year":'.$yy.',"Mon":'.$mm.',"Day":'.$dd.'}'),true);
	//echo $ret;exit;//print_r($ret);json_decode
	if ($ret['Code']=='0'){
		foreach ($ret['PosData'] as $v){
			$curD=strtotime($v['Time']);
			$t=intval((date('H',$curD)*60+date('i',$curD))/2);
			if ($bakt!=$t){
				$bakt=$t;
				if (!$times){
					$times=$t;
					$addrs=$v['Lon'].','.$v['Lat'];
					$disp=$v['Time'].chr(2).$v['Addr'];
				} else {
					$times.=','.$t;
					$addrs.=','.$v['Lon'].','.$v['Lat'];
					$disp.=chr(2).$v['Time'].chr(2).$v['Addr'];
				}	
			}
			
		}
	 if ($times)	
		echo chr(1).'ok'.chr(1).$times.chr(1).$addrs.chr(1).$disp;
	else echo '没有当天的车辆轨迹数据.';
	} 
		//echo json_decode($ret['PosData']);//$jsonStr;//json_encode($ret['PosData']);
	else echo errstr($ret['Code']);
	
break;

case 9011:
	$Phones=explode(',',$_REQUEST['Phone']);
	$Txt=str_replace("；",';',$_REQUEST['Book']);
	$Txt=str_replace("，",';',$Txt);
	$Txt=str_replace(",",';',$Txt);
	$Books=explode(';',$Txt);
	$Bookstr='';
	foreach ($Books as $v){
		if ($Bookstr) $Bookstr=$Bookstr.',"'.$v.'"';
		else $Bookstr=$Bookstr.'"'.$v.'"';
	}
	$Bookstr='['.$Bookstr.']';		
	//echo '{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}';exit;
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$q=0;
	foreach ($Phones as $p) if ($p){
		$ret=json_decode($zbapi->req(8,'{"Account":"'.$_SESSION['zbAcc'].'","Phone":"'.$p.'"}'),true);
	//$ret=json_decode($zbapi->req(133,'{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}'),true);
	if ($ret['Code']=='0') $q++;
	}
	//$ret=json_decode($zbapi->req(8,'{"Account":"'.$_SESSION['zbAcc'].'","Phone":"'.$Phone.'"}'),true);
	//$ret=json_decode($zbapi->req(133,'{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}'),true);
	
	$userinfo=$zbapi->loadUserInfo();
	apc_store('zb_userinfo'.$_SESSION['zbAcc'],$userinfo);	
	echo "成功删除[ $q ]个用户！";
	
break;
case 9012:
	$IMEI=$_REQUEST['IMEI'];
	$Num=$_REQUEST['Num'];
	$GroupID=$_REQUEST['GroupID'];
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	$Txt=str_replace("；",',',$_REQUEST['Book']);
	$Txt=str_replace("，",',',$Txt);
	$Txt=str_replace(";",',',$Txt);
	$Books=explode(',',$Txt);
	$Bookstr='';
	foreach ($Books as $v){
		if ($Bookstr) $Bookstr=$Bookstr.',"'.$v.',"';
		else $Bookstr=$Bookstr.'"'.$v.',"';
	}
	$Bookstr='['.$Bookstr.']';		//$ret=json_decode($zbapi->req(9,'{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}'),true);
	$ret=json_decode($zbapi->req(23,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Number":"'.$Num.'","Phone":"'.$_REQUEST['Phone'].'"}'),true);
	if ($ret['Code']=='0') {
		
		//$zbapi->req(131,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Phone":"'.$_REQUEST['Phone'].'"}');
		$zbapi->req(130,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Value":'.intval($_REQUEST['PreLocate']).'}');
		$zbapi->req(129,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Value":'.intval($_REQUEST['SleepIntval']).'}');
		$zbapi->req(128,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Value":'.intval($_REQUEST['LocIntval']).'}');
		$zbapi->req(138,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Type":'.intval($_REQUEST['LoopType']).',"Value":"'.$_REQUEST['LoopValue'].'"}');
		$zbapi->req(148,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","State":'.intval($_REQUEST['AlarmStat']).'}');
		$zbapi->req(150,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Value":'.intval($_REQUEST['ReportIntval']).'}');
		$zbapi->req(33,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","PhoneBook":'.$Bookstr.'}') ;
		if ($GroupID)  $zbapi->req(154,'{"Account":"'.$_SESSION['zbAcc'].'","GroupId":"'.$GroupID.'","Devices":[{"IMEI":"'.$IMEI.'","Number":"'.$Num.'","Phone":"'.$_REQUEST['Phone'].'"}]}') ;
		$zbapi->loadDevInfo();
		apc_store('zb_devinfo'.$_SESSION['zbAcc'],$zbapi->devinfo,600);	
		
		echo '添加设备成功！';
	}

	else echo errstr($ret['Code']);
break;
case 9013:
	$IMEI=$_REQUEST['IMEI'];
	
	//echo '{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}';exit;
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	//echo $zbapi->req(141,'{"Account":"'.$_SESSION['zbAcc'].'","Devices":[{"IMEI":"'.$IMEI.'"]}');exit;
	$ret=json_decode($zbapi->req(141,'{"Account":"'.$_SESSION['zbAcc'].'","Devices":["'.$IMEI.'"]}'),true);
	//$ret=json_decode($zbapi->req(133,'{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}'),true);
	if ($ret['Code']=='0'){
		$devinfo=apc_fetch('zb_devinfo'.$_SESSION['zbAcc']);
		if ($devinfo){
			for ($i=0;$i<count($devinfo);$i++) if ($devinfo[$i]['IMEI']===$IMEI){
				unset($devinfo[$i]);
				apc_store('zb_devinfo'.$_SESSION['zbAcc'],$devinfo,600);
				break;
			}
			
			
				
		}
		
		
		
			
		
		echo '删除设备成功！';
	} 
	else echo errstr($ret['Code']);
break;
case 9014:
	$IMEI=trim($_REQUEST['IMEI']);
	$Num=trim($_REQUEST['Num']);
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	$GroupID=$_REQUEST['GroupID'];
	$Txt=str_replace("；",',',trim($_REQUEST['Book']));
	$Txt=str_replace("，",',',$Txt);
	$Txt=str_replace(";",',',$Txt);
	$Books=explode(',',$Txt);
	$Bookstr='';
	foreach ($Books as $v) if ($v){
		if ($Bookstr) $Bookstr=$Bookstr.',"'.trim($v).',"';
		else $Bookstr=$Bookstr.'"'.trim($v).',"';
	}
	$Bookstr='['.$Bookstr.']';	//$ret=json_decode($zbapi->req(9,'{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}'),true);
	$ret=json_decode($zbapi->req(5,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Number":"'.$Num.'"}'),true);
	if ($ret['Code']=='0') {
		
		
		$zbapi->req(131,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Phone":"'.trim($_REQUEST['Phone']).'"}');
		$zbapi->req(130,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Value":'.intval($_REQUEST['PreLocate']).'}');
		$zbapi->req(129,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Value":'.intval($_REQUEST['SleepIntval']).'}');
		$zbapi->req(128,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Value":'.intval($_REQUEST['LocIntval']).'}');
		//echo $_REQUEST['LoopType'],'   ',$_REQUEST['LoopValue'],'  '   , 
		$zbapi->req(138,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Type":'.intval($_REQUEST['LoopType']).',"Value":"'.$_REQUEST['LoopValue'].'"}');
		$zbapi->req(148,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","State":'.intval($_REQUEST['AlarmStat']).'}');
		$zbapi->req(150,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Value":'.intval($_REQUEST['ReportIntval']).'}');
		$zbapi->req(33,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","PhoneBook":'.$Bookstr.'}') ;
		if ($GroupID)  $zbapi->req(154,'{"Account":"'.$_SESSION['zbAcc'].'","GroupId":"'.$GroupID.'","Devices":[{"IMEI":"'.$IMEI.'","Number":"'.$Num.'","Phone":"'.$_REQUEST['Phone'].'"}]}') ;
		$zbapi->loadDevInfo();
		apc_store('zb_devinfo'.$_SESSION['zbAcc'],$zbapi->devinfo,600);	
		
		 
		echo '修改设备成功！';
	}

	else echo errstr($ret['Code']);
break;
case 9015:
	$Txt=str_replace("\r",'',$_REQUEST['Txt']);
	$Txt=str_replace("，",',',$Txt);
	
	$line=explode("\n",$Txt);
	$GroupID=intval($_REQUEST['GroupID']);
	
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	$q=0;
	$err='';
	$Post='';
	foreach($line as $r){
		
		$v=explode(",",$r);
	//	$Post.='{"IMEI":"'.trim($v[0]).'","Number":"'.trim($v[1]).'","Phone":"'.trim($v[2]).'"}';
	//	$ret=json_decode($zbapi->req(140,'{"Account":"'.$_SESSION['zbAcc'].'","Devices":['.$Post.']}'),true);
		$IMEI=trim($v[0]);
		$IMEI=str_replace(chr(8),'',$IMEI);
		$IMEI=str_replace(chr(9),'',$IMEI);
		$IMEI=str_replace(chr(10),'',$IMEI);
		$IMEI=str_replace(chr(13),'',$IMEI);
		if ($IMEI){
			$ret=json_decode($zbapi->req(23,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Number":"'.trim($v[1]).'","Phone":"'.trim($v[2]).'"}'),true);
			if ($ret['Code']=='0') {
				if ($v[3])
				{
					
					$Bookstr='';
					for ($i=3;$i<11;$i++){
						
						
						$Phs=trim($v[$i]);
						if ($Phs) {
							if ($Bookstr) $Bookstr=$Bookstr.',"'.$Phs.',"';
							else $Bookstr=$Bookstr.'"'.$Phs.',"';
							}
					}
					
					$Bookstr='['.$Bookstr.']';	
					//echo '{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","PhoneBook":'.$Bookstr.'}';
					$zbapi->req(33,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","PhoneBook":'.$Bookstr.'}') ;
					
				}
				if ($GroupID)  $zbapi->req(154,'{"Account":"'.$_SESSION['zbAcc'].'","GroupId":"'.$GroupID.'","Devices":[{"IMEI":"'.$IMEI.'","Number":"'.trim($v[1]).'","Phone":"'.trim($v[2]).'"}]}') ;
				$q++;
			} else $err.="[$IMEI] ".errstr($ret['Code'])."\r\n";
				
		}	
		
	}
	//$ret=json_decode($zbapi->req(9,'{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}'),true);
	//if ($ret['Code']===0){
		
	//} 
	
	$zbapi->loadDevInfo();
	apc_store('zb_devinfo'.$_SESSION['zbAcc'],$zbapi->devinfo,600);	
	if ($err) $err="\r\n".$err;
	
	echo "成功导入[ $q ]台设备！$err ";
	

	
break;

case 9016:
	$alarminfo=apc_fetch('zb_alarmInfo'.$_SESSION['zbAcc']);
	echo '<table border="1" cellspacing="0" cellpadding="0" ><tr><th>序号</th><th>设备编号</th><th>车牌号</th><th>通知时间</th><th>通知内容</th></tr>';
	$i=0;
	require_once 'page.php'; 
	$key=trim($_REQUEST['key']);
	//global $page;
	$total=0;
	$page=intval($_REQUEST['page']);
	$skip=$page*10-10;
	if ($skip<0) $skip=0; 

	foreach($alarminfo as $v){
		if (!$key || strpos('a'.$v['IMEI'],$key) ||  strpos('a'.$v['Number'],$key) ||  strpos('a'.$v['Time'],$key)){
			$total++;
			if ($skip==0){
				$i++;
				if ($i<11) {
					if ($v['Type']==1)$usertype='振动报警';
				else if ($v['Type']==2)$usertype='超速报警';
				else if ($v['Type']==3)$usertype='非法开门报警';
				else if ($v['Type']==4)$usertype='非法启动报警';
				else if ($v['Type']==5)$usertype='围栏报警(出)';
				else if ($v['Type']==6)$usertype='围栏报警(进)';
				else if ($v['Type']==7)$usertype='设备脱落报警（光感脱落）';
				else if ($v['Type']==8)$usertype='设备低电量报警（达到即将关机的阈值）';
				
				
				
		
				echo "<tr><td>$v[AlarmId]</td><td>$v[IMEI]</td><td>$v[Number]</td><td>$v[Time]</td><td>$usertype</td></tr>";
				}
			} else $skip--;
		}
		}
    echo '</table><div style="width:100%;height:35px;"><div class="pager">';
	
	
	pageft($total,10,1,0,1,10,'','alarmGotoPage');
	echo $pagenav,'</div></div>';	
break;
case 9017:
	$IMEI=$_REQUEST['IMEI'];
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	$ret=json_decode($zbapi->req(19,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'"}'),true);
	if ($ret['Code']=='0'){
		if ($ret['Type']=='0') echo chr(1),$ret['Type'],chr(1), $ret['Name'],chr(1),$ret['Fence']['Radius'],chr(1),$ret['Fence']['Lon'],chr(1),$ret['Fence']['Lat'],chr(1);
		else echo chr(1),'1',chr(1), $ret['Name'],chr(1),$ret['Fence']['City'],chr(1);
	} 
	else  echo chr(1),'1',chr(1),'',chr(1),'',chr(1); 
	
break;
case 9018:
	$IMEI=$_REQUEST['IMEI'];
	$Type=$_REQUEST['Type'];
	$Name=$_REQUEST['Name'];
	$City=$_REQUEST['City'];
	$Radius=intval($_REQUEST['Radius']);
	$Lng=$_REQUEST['Lng'];
	$Lat=$_REQUEST['Lat'];
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	if ($Type==1) $post='{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Type":'.$Type.',"Name":"'.$Name.'","Fence":{"City":"'.$City.'"}}';
	else $post='{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'","Type":'.$Type.',"Name":"'.$Name.'","Fence":{"Radius":'.$Radius.',"Lon":"'.$Lng.'","Lat":"'.$Lat.'"}}';
	
	$ret=json_decode($zbapi->req(20,$post),true);
	if ($ret['Code']=='0'){
	echo "安全区域设置成功.";
	}
	else echo errstr($ret['Code']);
break;

case 9019:
	
	$IMEI=explode(',',$_REQUEST['IMEI']);
	//$devinfo=apc_fetch('zb_devinfo'.$_SESSION['zbAcc']);
	//echo '{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}';exit;
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	//echo $zbapi->req(141,'{"Account":"'.$_SESSION['zbAcc'].'","Devices":[{"IMEI":"'.$IMEI.'"]}');exit;
	$q=0;
	foreach ($IMEI as $v) if ($v){
		$ret=json_decode($zbapi->req(141,'{"Account":"'.$_SESSION['zbAcc'].'","Devices":["'.$v.'"]}'),true);
		if ($ret['Code']=='0'){
			$q++;
			
		} 
	}
	
	//$ret=json_decode($zbapi->req(133,'{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}'),true);
	if ($q){
		$zbapi->loadDevInfo();
		$devinfo=$zbapi->devinfo;
		apc_store('zb_devinfo'.$_SESSION['zbAcc'],$zbapi->devinfo,600);
	}
	
	
		
		
		
		
	echo '成功删除[ '.$q.' ]台设备！';
	
break;

case 9020:
	$IMEI=$_REQUEST['IMEI'];
	//echo $IMEI;exit;
	//echo '{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}';exit;
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	
	//echo $zbapi->req(3,'{"Devices":["'.$IMEI.'"]}');exit;
	$ret=json_decode($zbapi->req(3,'{"Devices":["'.$IMEI.'"]}'),true);
	if ($ret['Code']=='0'){
		foreach($ret['DevInfo'] as $v);
		echo chr(1),'0',chr(1),$v['State'],chr(1),$v['Addr'],chr(1),$v['Time'],chr(1),$v['Lat'],chr(1),$v['Lon'],chr(1),$v['Power'],chr(1),$v['AccType'],chr(1);
		
		$tmp=apc_fetch('zb_devinfo'.$_SESSION['zbAcc']);
	
		if ($tmp)
			for ($i=0;$i<count($tmp);$i++)if($tmp[$i]['IMEI']==$IMEI){
				$tmp[$i]['State']=$v['State'];
				apc_store('zb_devinfo'.$_SESSION['zbAcc'],$tmp,600);
				break;
			}
			
	}
	else echo chr(1),'1',chr(1),errstr($ret['Code']);
break;

case 9021:
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	
	//echo $zbapi->req(3,'{"Devices":["'.$IMEI.'"]}');exit;
	$ret=json_decode($zbapi->req(21,'{"Account":"'.$_SESSION['zbAcc'].'"}'),true);
	if ($ret['Code']=='0'){
		echo '恢复出厂设置成功！';
	}
	else echo errstr($ret['Code']);
break;


case 9022:
    $Type=intval($_REQUEST['Type']);
	$Value=$_REQUEST['Value'];
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	
	//echo $zbapi->req(3,'{"Devices":["'.$IMEI.'"]}');exit;
	$ret=json_decode($zbapi->req(18,'{"Account":"'.$_SESSION['zbAcc'].'","Type":'.$Type.',"Value":"'.$Value.'"}'),true);
	if ($ret['Code']=='0'){
		echo '周期定位设置成功！';
	}
	else echo errstr($ret['Code']);
break;



case 9023:
    $IMEI=$_REQUEST['IMEI'];
	
	if (apc_exists('zb_devinfo'.$_SESSION['zbAcc']))
		$devinfo=apc_fetch('zb_devinfo'.$_SESSION['zbAcc']);
	else {
		require_once("zbapi.php");
		$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
		$zbapi->login();
		$zbapi->loadDevInfo();
		$devinfo=$zbapi->devinfo;
		apc_store('zb_devinfo'.$_SESSION['zbAcc'],$zbapi->devinfo,600);
	}
	
	
	foreach($devinfo as $v){
		if ($v['IMEI']==$IMEI){
			$book=str_replace("[",'',$v['PhoneBook']);//implode(';',$v['PhoneBook']);$Txt=str_replace("；",';',$_REQUEST['Book']);
			$book=str_replace("]",'',$book);
			$book=str_replace('"','',$book);
			$book=str_replace(';',',',$book);
			$book=str_replace(' ','',$book);
			
			echo chr(1),$v['DevPhone'],chr(1),$v['PreLocate'],chr(1),$v['LocIntval'],chr(1),$v['LoopType'],chr(1),$v['LoopValue'],chr(1),$v['SleepIntval'],chr(1),$book,chr(1),$v['ReportIntval'],chr(1),$v['AlarmState'],chr(1),$v['GroupId'];
			
			
			exit;
			//echo chr(1),$v[''];
		}
		  
	}
	
break;
case 9024:

	$userAcc=$_REQUEST['Acc'];
	$userName=$_REQUEST['Name'];
	$userPwd=$_REQUEST['Pwd'];
	$userPhone=$_REQUEST['Phone'];
	$userMail=$_REQUEST['Mail'];
	$userType=$_REQUEST['Type'];
	//echo '{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}';exit;
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	if ($userPwd) $SetInfo='{"Name":"'.$userName.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Pwd":"'.$userPwd.'"}';
	else $SetInfo='{"Name":"'.$userName.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'"}';
	$ret=json_decode($zbapi->req(132,'{"Account":"'.$_SESSION['zbAcc'].'","Pwd":"'.md5($_SESSION['zbAcc'].$_SESSION['zbPwd']).'","SetAccount":"'.$userAcc.'","SetInfo":'.$SetInfo.'}'),true);
	//$ret=json_decode($zbapi->req(9,'{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","Phone":"'.$userPhone.'","Role":'.$userType.'}'),true);
	if ($ret['Code']=='0') {
	$userinfo=$zbapi->loadUserInfo(true);
	apc_store('zb_userinfo'.$_SESSION['zbAcc'],$userinfo);	
	echo '修改用户信息成功！';
	}
	else echo errstr($ret['Code']);
break;	

case 9025:
	require_once("zbapi.php");
	 $IMEI=$_REQUEST['IMEI'];
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	
	//echo $zbapi->req(3,'{"Devices":["'.$IMEI.'"]}');exit;
	$ret=json_decode($zbapi->req(139,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'"}'),true);
	if ($ret['Code']=='0'){
		$zbapi->loadDevInfo();
		apc_store('zb_devinfo'.$_SESSION['zbAcc'],$zbapi->devinfo,600);
	
	
	
		echo '恢复出厂设置成功！';
	}
	else echo errstr($ret['Code']);
break;

case 9026:
	require_once("zbapi.php");
	 $IMEI=$_REQUEST['IMEI'];
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	
	//echo $zbapi->req(3,'{"Devices":["'.$IMEI.'"]}');exit;
	$ret=json_decode($zbapi->req(149,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'"}'),true);
	if ($ret['Code']=='0'){
		echo '监听设置成功！';
	}
	else echo errstr($ret['Code']);
break;

  
case 9027:

	require_once("zbapi.php");
	 $IMEI=$_REQUEST['IMEI'];
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	
	//echo $zbapi->req(3,'{"Devices":["'.$IMEI.'"]}');exit;
	$ret=json_decode($zbapi->req(32,'{"Account":"'.$_SESSION['zbAcc'].'","IMEI":"'.$IMEI.'"}'),true);
	if ($ret['Code']=='0'){
		echo '关闭安全区域成功！';
	}
	else echo errstr($ret['Code']);
break;

case 9028:

	require_once("zbapi.php");
	 $Group=$_REQUEST['Group'];
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	
	//echo $zbapi->req(3,'{"Devices":["'.$IMEI.'"]}');exit;
	$ret=json_decode($zbapi->req(159,'{"Account":"'.$_SESSION['zbAcc'].'","GroupName":"'.$Group.'"}'),true);
	if ($ret['Code']=='0'){
		echo chr(1),'0',chr(1), '<option selected="selected" value="0">全部</option>';
		 
	 $ret=json_decode($zbapi->req(157,'{"Account":"'.$_SESSION['zbAcc'].'"}'),true);
	 if ($ret['Code']=='0'){
		 foreach ($ret['Groups'] as $v ) if ($v)
			 echo "<option value=\"$v[GroupId]\">$v[GroupName]</option>";
	 } else echo chr(1),'1',chr(1),errstr($ret['Code']);
		 
	}
	else echo chr(1),'1',chr(1),errstr($ret['Code']);
break;


case 9029:

	require_once("zbapi.php");
	$ID=$_REQUEST['ID'];
	 $Group=$_REQUEST['Group'];
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	
	//echo $zbapi->req(3,'{"Devices":["'.$IMEI.'"]}');exit;
	$ret=json_decode($zbapi->req(153,'{"Account":"'.$_SESSION['zbAcc'].'","GroupId":"'.$ID.'","GroupName":"'.$Group.'"}'),true);
	if ($ret['Code']=='0'){
		echo chr(1),'0',chr(1), '<option selected="selected" value="0">全部</option>';
		 
	 $ret=json_decode($zbapi->req(157,'{"Account":"'.$_SESSION['zbAcc'].'"}'),true);
	 if ($ret['Code']=='0'){
		 foreach ($ret['Groups'] as $v ) if ($v)
			 echo "<option value=\"$v[GroupId]\">$v[GroupName]</option>";
	 } else echo chr(1),'1',chr(1),errstr($ret['Code']);
		 
	}
	else echo chr(1),'1',chr(1),errstr($ret['Code']);
break;

case 9030:

	require_once("zbapi.php");
	$GroupId=intval($_REQUEST['GroupId']); 
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	
	//echo $zbapi->req(3,'{"Devices":["'.$IMEI.'"]}');exit;
	$ret=json_decode($zbapi->req(155,'{"Account":"'.$_SESSION['zbAcc'].'","GroupId":"'.$GroupId.'"}'),true);
	if ($ret['Code']=='0'){
		if ($GroupId>0 && apc_exists('zb_devinfo'.$_SESSION['zbAcc'])){
			$devinfo=apc_fetch('zb_devinfo'.$_SESSION['zbAcc']);
			for ($i=count($devinfo);$i>=0;$i--){
				if ($devinfo[$i]['GroupId']==$GroupId){
					unset($devinfo[$i]);
				} 
			}
			apc_store('zb_devinfo'.$_SESSION['zbAcc'],$devinfo,600);			
		}
		
		echo "删除分组成功";
	}
	else errstr($ret['Code']);
break;


case 9031:

	require_once("zbapi.php");
	$GroupId=$_REQUEST['GroupId']; 
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	
	//echo $zbapi->req(3,'{"Devices":["'.$IMEI.'"]}');exit;
	$ret=json_decode($zbapi->req(155,'{"Account":"'.$_SESSION['zbAcc'].'","GroupId":"'.$GroupId.'"}'),true);
	if ($ret['Code']=='0'){
		$zbapi->loadDevInfo();
		apc_store('zb_devinfo'.$_SESSION['zbAcc'],$zbapi->devinfo,600);
		echo "删除分组成功.";
	}
	else errstr($ret['Code']);
break;

case 9032:
	$ID=$_REQUEST['GroupId'];
	$post='';
	$PreLocate=intval($_REQUEST['PreLocate']);
	if ($PreLocate>=0) $post=',"PreLocation":'.$PreLocate;
	if (intval($_REQUEST['LocIntval'])>=0) $post.=',"LocInterval":'.intval($_REQUEST['LocIntval']);
	if (intval($_REQUEST['LoopType'])>=0){
		$post.=',"LoopLocType":'.intval($_REQUEST['LoopType']);
		if ($_REQUEST['LoopValue']) $post.=',"LoopLocValue":"'.$_REQUEST['LoopValue'].'"';
	} 
	
	if (intval($_REQUEST['SleepIntval'])>=0) $post.=',"SleepValue":'.intval($_REQUEST['SleepIntval']);
	if (intval($_REQUEST['AlarmStat'])>=0) $post.=',"AlarmState":'.intval($_REQUEST['AlarmStat']);
	if (intval($_REQUEST['ReportIntval'])>=0) $post.=',"ReportVal":'.intval($_REQUEST['ReportIntval']);

	
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
	$GroupID=$_REQUEST['GroupID'];
	$Txt=str_replace("；",';',$_REQUEST['Book']);
	$Txt=str_replace("，",';',$Txt);
	$Txt=str_replace(",",';',$Txt);
	$Books=explode(';',$Txt);
	$Bookstr='';
	foreach ($Books as $v){
		if ($Bookstr) $Bookstr=$Bookstr.',"'.$v.'"';
		else $Bookstr=$Bookstr.'"'.$v.'"';
	}
	$Bookstr='['.$Bookstr.']';	
//echo '{"Account":"'.$_SESSION['zbAcc'].'","GroupId":"'.$ID.'"'.$post.'}';	//$ret=json_decode($zbapi->req(9,'{"Account":"'.$_SESSION['zbAcc'].'","Name":"'.$userName.'","SetAccount":"'.$userAcc.'","SetPwd":"'.$userPwd.'","Phone":"'.$userPhone.'","Email":"'.$userMail.'","Role":'.$userType.'}'),true);
	$ret=json_decode($zbapi->req(156,'{"Account":"'.$_SESSION['zbAcc'].'","GroupId":"'.$ID.'"'.$post.'}'),true);
	if ($ret['Code']=='0') {
		
		$zbapi->loadDevInfo();
		apc_store('zb_devinfo'.$_SESSION['zbAcc'],$zbapi->devinfo);	
		echo '修改分组参数成功！';
	}
	else echo errstr($ret['Code']);
break;
case 9033:
	$IMEI=$_REQUEST['IMEI'];
	$Pwd=$_REQUEST['Pwd'];
	$Stat=intval($_REQUEST['Stat']);
		
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
//	echo '{"Account":"'.$_SESSION['zbAcc'].'","Pwd":"'.md5($_SESSION['zbAcc'].$_SESSION['zbPwd']).'","IMEI":"'.$IMEI.'","DevPwd":"'.md5($_SESSION['zbAcc'].$Pwd).'","Switch":'.$Stat.'}';
	$ret=json_decode($zbapi->req(161,'{"Account":"'.$_SESSION['zbAcc'].'","Pwd":"'.md5($_SESSION['zbAcc'].$_SESSION['zbPwd']).'","IMEI":"'.$IMEI.'","DevPwd":"'.md5($_SESSION['zbAcc'].$Pwd).'","Switch":'.$Stat.'}'),true);
	if ($ret['Code']=='0') {
		
		if ($Stat) echo '恢复油电成功！';
		else echo '断油电成功！';
	}
	else echo errstr($ret['Code']);
break;

case 9034:
	$Pwd=$_REQUEST['Pwd'];
	require_once("zbapi.php");
	$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
	$zbapi->login();
//	echo '{"Account":"'.$_SESSION['zbAcc'].'","Pwd":"'.md5($_SESSION['zbAcc'].$_SESSION['zbPwd']).'","IMEI":"'.$IMEI.'","DevPwd":"'.md5($_SESSION['zbAcc'].$Pwd).'","Switch":'.$Stat.'}';
	$ret=json_decode($zbapi->req(160,'{"Account":"'.$_SESSION['zbAcc'].'","Pwd":"'.md5($_SESSION['zbAcc'].$_SESSION['zbPwd']).'","DevPwd":"'.$Pwd.'"}'),true);
	if ($ret['Code']=='0') {
		echo '设置继电器密码 成功！';
	}
	else echo errstr($ret['Code']);
break;	
}



?>
