<?php
include_once('config.php');
@session_start();

require_once("zbapi.php");
if ($_SESSION['zbAcc']==''){
	$host = $_SERVER['HTTP_HOST'];
	header("Location: http://{$host}/login.php");
	exit;
} 
if ($_SESSION['zbAcc']!='')$zbapi=new zbapi($_SESSION['zbAcc'],$_SESSION['zbPwd']);
$zbapi->loadDevInfo();
$onlineQty=0;
$leftQty=0; 

foreach($zbapi->devinfo as $v){
	if ($v['Lat']){
		$DefLat=$v['Lat'];
		$DefLon=$v['Lon'];
	} 
	if ($v['State'])$onlineQty++;else $leftQty++;
}
apc_store('zb_devinfo'.$_SESSION['zbAcc'],$zbapi->devinfo,600);
$total=count($zbapi->devinfo);
$p0=intval(ceil($total/10));
$p1=intval(ceil($onlineQty/10));
$p2=intval(ceil($leftQty/10));

$JSStr="var pageTotal=new Array();var pagePos=new Array();pagePos[0]=1;pagePos[1]=1;pagePos[2]=1;pagePos[3]=1;pageTotal[0]=$p0; pageTotal[1]=$p1;pageTotal[2]=$p2;pageTotal[3]=$p0;var panelPos=0;var curPanel=0;var termPage=1;var userPage=1;";
?><!DOCTYPE html>

<html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="resources/css/base.css" type="text/css" media="screen"/>
<link rel="stylesheet" href="resources/js/jquery-ui-1.12.1/jquery-ui.min.css" type="text/css" media="screen" />
<link rel="stylesheet" href="resources/js/jquery.showLoading-master/css/showLoading.css" type="text/css" media="screen" />
<script src="resources/js/jquery-1.11.1.min.js"></script>
<script src="resources/js/jquery-ui-1.12.1/jquery-ui.min.js"></script>
<script src="resources/js/jquery.showLoading-master/js/jquery.showLoading.min.js"></script>
<script src="resources/js/zhuobi.js"></script>
<script src="resources/js/common.js"></script>
<link rel="stylesheet" href="http://cache.amap.com/lbs/static/main1119.css"/>
    <script src="http://cache.amap.com/lbs/static/es5.min.js"></script>
    <script src="http://webapi.amap.com/maps?v=1.3&key=829110813b52c5982a834b2fb606622a&plugin=AMap.PolyEditor,AMap.CircleEditor,AMap.Geocoder"></script>
    <script type="text/javascript" src="http://cache.amap.com/lbs/static/addToolbar.js"></script>
	
<title></title>
</head>
   <body>
    <div class="top">
           <div class="left">
              <img src="resources/images/logo_2x.png" width="130" style="margin-top:-10px;"  alt="" />
              <span>卓比汽车在线</span>
           </div>
           <div class="center">
              <ul>
                <li class="currli" id="idWatchbu">轨迹监控</li>
                <li>设备管理</li>
				<li id="idUserbu">用户管理</li>
				<li>通知信息</li>
				<?php if ($_SESSION['zbRole']=='0' && false){?><li id="idSetupbu">系统设置</li><?php }?>
              </ul>
           </div>
          <a class="right" href="login.php?act=exit" style="cursor:pointer">
              退出(<?php echo $_SESSION['zbAcc'];?>)
           </a>
    </div>
<div class="con">
        
    
     <div class="con1">

        <div class="map" id="idMap"></div><!-- 地图 -->
        <div class="leftDiv">
        	<h2><div style="position: absolute;font-size:16px;left:15px;top:1px;">轨迹监控管理</div>
               <div id="dj" onclick="if ($('#dj').css('background-image').indexOf('closex.png')>5) $('#dj').css('background-image','url(resources/images/openx.png)');else $('#dj').css('background-image','url(resources/images/closex.png)'); " style="background-image:url(resources/images/closex.png);background-repeat: no-repeat; background-size: 12px 7px;width:40px;height:40px;position: absolute;top:20px;right:-5px;"></div>
            </h2>
            <div class="leftmolud" style="padding:0px;">
                <ul class="ul1" >
                    <li onclick="$('#idPoint').css('left','25%');setPage(panelPos);$('#idDIVTrack').css('display','none');clearMap();" class="blue">实时监控</li>
                    <li onclick="$('#idPoint').css('left','75%');setPage(3);curPanel=3;$('#idDIVTrack').css('display','');clearMap();">轨迹查询</li>
                </ul>
				<div id="idPoint" style="position: absolute;width: 16px;height: 9px;top:71px;background: url(resources/images/pointer.png);left: 25%;z-index:999;margin-left: -8px;transition: all .2s ease-in-out;"></div>
                <div class="ul2">
                    <li>
                        <div class="searchDiv" >
                            <input type="text" class="left" id="idKey_1" placeholder="请输入关键字" style="font-size:14px;padding:6px;width:250px;"/>
                            <img src="resources/images/search.png" alt="" width="16" class="right" onclick="search_1()"/>
                        </div> 
                        <div>
                            <ul class="mo">
                                <li onclick="setPage(0);panelPos=0;curPanel=0;" class="blueli" id="idTotal_0">全部(<?php if (!$zbapi->devinfo) echo '0'; else  echo count($zbapi->devinfo);?>)</li>
                                <li onclick="setPage(1);panelPos=1;curPanel=1;" id="idTotal_1">在线(<?php echo $onlineQty;?>)</li>
                                <li onclick="setPage(2);panelPos=2;curPanel=2;" id="idTotal_2">离线(<?php echo $leftQty;?>)</li>
                            </ul>
                        </div> 
                        <div class="detail">
                            <ul >
                                <li id="idPanel_0">
								<?php $i=0; foreach($zbapi->devinfo as $v){
									$i++;
									if ($i>10) break;
									if (!$v['Num']) {
										$v['Num'] = '未知';
									}
									if ($v['State']){
										if ($v['State']==2) {
											$Stat='休眠';
										} else {
											$Stat='在线';
										}
										echo "<p onclick=\"locateTerm('$v[IMEI]',$v[State]);\"><img src='resources/images/caron.png'  style='width:16px;padding:0px 5px;' alt='' class='left'><span class='left'>$v[IMEI]</span><span class='left'  style='padding-left:10px;'>$v[Num]</span><span class='right'>$Stat</span></p>";
									} 
									else {
										 echo "<p onclick=\"locateTerm('$v[IMEI]',$v[State]);\"><img src='resources/images/caroff.png'  style='width:16px;padding:3px 12px 0px 9px;' alt='' class='left'><span class='left'>$v[IMEI]</span><span class='left'  style='padding-left:10px;'>$v[Num]</span><span class='right'>离线</span></p>";
									}  
								}?>
                                    
                                </li>
                                <li class="none" id="idPanel_1">
								
								<?php $i=0; foreach($zbapi->devinfo as $v){
									
									if ($v['State']){
										$i++;
										if ($i>10) break;
										if (!$v['Num']) {
											$v['Num'] = '未知';
										}
										if ($v['State']==2) $Stat='休眠';
										else $Stat='在线';
										echo "<p onclick=\"locateTerm('$v[IMEI]',$v[State]);\"><img src='resources/images/caron.png'  style='width:16px;padding:0px 5px;' alt='' class='left'><span class='left'>$v[IMEI]</span><span class='left'  style='padding-left:10px;'>$v[Num]</span><span class='right'>$Stat</span></p>";
									} 
									 
								}?>
							
                                </li>
                                <li class="none" id="idPanel_2">
                                   <?php $i=0; foreach($zbapi->devinfo as $v){
									
									if (!$v['State']) {
										$i++;
										if ($i>10) break;
										
										if (!$v['Num']) {
											$v['Num'] = '未知';
										}
										 echo "<p onclick=\"locateTerm('$v[IMEI]',$v[State]);\"><img src='resources/images/caroff.png'  style='width:16px;padding:3px 12px 0px 9px;' alt='' class='left'><span class='left'>$v[IMEI]</span><span class='left'  style='padding-left:10px;'>$v[Num]</span><span class='right'>离线</span></p>";
									}  
								}?>
                                </li>
                            </ul>
                        </div> 
                    </li>
                    <li class="none">
                        <div class="riqi" style="margin-top:-5px">
                            <input type="text" id="idTrackDate" class="input1" style="margin:-2px 0px 0px -2px" size=10 maxlength=10 value="<?php echo date('Y-m-d',time());?>" onfocus="HS_setDate(this)" value="请选择日期">
                        </div>
                       <div class="searchDiv">
                            <input type="text" class="left"  id="idKey_2"  placeholder="请输入关键字" style="font-size:14px;padding:6px;width:250px;"/>
                            <img src="resources/images/search.png" alt="" width="16" class="right" onclick="search_2();"/>
                        </div>
                        <div class="detail" id="idPanel_3">
						<?php $i=0; foreach($zbapi->devinfo as $v){
									$i++;
									if ($i>10) break;
									if (!$v['Num']) {
										$v['Num'] = '未知';
									}
									if ($v['State']){
										if ($v['State']==2) $Stat='休眠';
										else $Stat='在线';
										echo "<p onclick=\"track('$v[IMEI]');\"><img src='resources/images/caron.png'  style='width:16px;padding:3px 12px 0px 9px;' alt='' class='left'><span class='left'>$v[IMEI]</span><span class='left'  style='padding-left:10px;'>$v[Num]</span><span class='right'>$Stat</span></p>";
									} 
									else {
										 echo "<p onclick=\"track('$v[IMEI]');\"><img src='resources/images/caroff.png'  style='width:16px;padding:3px 12px 0px 9px;' alt='' class='left'><span class='left'>$v[IMEI]</span><span class='left'  style='padding-left:10px;'>$v[Num]</span><span class='right'>离线</span></p>";
									}  
								}?>
                             
                        </div>
                    </li>
                </div>
                <!-- 分页 -->
				<div style="position: absolute;bottom:50px;border-top: 1px solid #e1e1e1;width:100%"></div>
            <div class="fenye">
                <div style="float:left;padding-top:0px;"><input type="text" style=" font-weight:bold;width:50px;text-align:right;line-height:23px;height:23px;padding-right:3px;" id="idPagePos" value="1"/> /<span id="idPageTotal"><?php echo $p0;?></span>页</div>
				<div class="bordersp" onclick="gotoPage($('#idPagePos').val(),-1);" style="margin:0px 5px;padding-top:3px;float:left;font-size:12px;height:12px;"> GO </div>
				<span class="bordersp" onclick="gotoPage(pagePos[curPanel]-1,-1);" id="idPageFirst"style="float:left;width:20px;background-image:url(resources/images/lastpageoff.png"></span>
				<span class="bordersp" onclick="gotoPage(pagePos[curPanel]+1,-1);" id="idPageNext" style="margin-left:-1px;float:left;width:20px;background-image: url(resources/images/nextpageon.png"></span>
            </div>
        </div>
     </div>
    <!-- 计时条 -->
<div class="hour" id="idDIVTrack" style="display:none;">

         <img style="margin:5px 0px 0px 5px;" id="idPlay" onclick="playTrack(1);" src="resources/images/play.png" height="40" width="40" alt="">
     <div style=" height: 20px; width: 20px; position: absolute; cursor: pointer; background-position: center center; background-repeat: no-repeat; z-index: 999; margin-top: -3px; left: 790px; top: 15px; font-size:12px;color:#444;" onclick="playTrack(2);">2X</div> 
     <div style=" height: 20px; width: 20px; position: absolute; cursor: pointer; background-position: center center; background-repeat: no-repeat; z-index: 999; margin-top: -3px; left: 815px; top: 15px; font-size:12px;color:#444;" onclick="playTrack(4);">4X</div> 
     <div style=" height: 20px; width: 20px; position: absolute; cursor: pointer; background-position: center center; background-repeat: no-repeat; z-index: 999; margin-top: -3px; left: 840px; top: 15px; font-size:12px;color:#444;" onclick="playTrack(8)">8X</div> 
	<div style="float:left;position:absolute;top:15px;left:60px;">
	<div id="idTrackPos" style="background-image: url(resources/images/trackpos.png);height: 20px;width: 20px;position: absolute;cursor: pointer;background-position: center;background-repeat: no-repeat;z-index:999;margin-top:-3px;"></div>
    <ul style="height:10px;float:left;margin-bottom:5px;">
        <li style="width:720px"><table cellpadding="0" cellspacing="0" class="track" id="idtrackTab"><tr>
		<?php for($i=0;$i<720;$i++) echo '<td onclick="t(',$i,');"></td>';?></tr></table></li>
       
    </ul>
    <p style="margin-left:-2px;width:770px;">
		<span>0</span>
        <span>1</span>
        <span>2</span>
        <span>3</span>
        <span>4</span>
        <span>5</span>
        <span>6</span>
        <span>7</span>
        <span>8</span>
        <span>9</span>
        <span>10</span>
        <span>11</span>
        <span>12</span>
        <span>13</span>
        <span>14</span>
        <span>15</span>
        <span>16</span>
        <span>17</span>
        <span>18</span>
        <span>19</span>
        <span>20</span>
        <span>20</span>
        <span>21</span>
        <span>22</span>
        <span>23</span>
       <span>24</span>
    </p>
	</div>
</div>


    </div>
    <div class="con1 none" id="idTermPanel" >
     <div class="panelTop" ><div class="searchDiv" style="width:200px;float:left;margin-right:20px;">
       <input type="text" class="left" placeholder="请输入关键字" id="idTermKey" style="font-size:14px;padding:6px 0px 6px 0px;margin:0px;width:180px;"/>
        <img src="resources/images/search.png" alt="" width="16" class="right" onclick="serachTerm();" />
     </div><div style="float:left;margin:3px 10px 0px 0px;">当前分组：<select id="idSelectGroup" onchange="serachTerm();" style="width:150px;height:27px;background-color:#f5f5f5;border: 1px solid #aad;"><option selected="selected" value="0">全部</option><?php 
	 $ret=json_decode($zbapi->req(157,'{"Account":"'.$zbapi->account.'"}'),true);
	 if ($ret['Code']=='0')
		 foreach ($ret['Groups'] as $v ) if ($v)
			 echo "<option value=\"$v[GroupId]\">$v[GroupName]</option>";
	 ?></select><?php if ($_SESSION['zbRole']=='0') {?> <button class="button" onclick="editGroup(0);">添加分组</button><button class="button" onclick="editGroup(1);">修改当前分组</button><button class="button" onclick="deleteGroup();">删除当前分组</button><button class="button" onclick="editGroupVar();">当前分组参数设置</button><?php }?></div></div> 
     <div class="tDiv" id="idTermList">
         <table border="1" cellspacing="0" cellpadding="0" >
          <tr>
            <?php if ($_SESSION['zbRole']=='0') {?> <th><input onclick="selAllTerm(this)"  type="checkbox"/>全选</th><?php }?>
            <th>设备编号</th>
			
            <th>车牌号 </th>
			<th>设备号码</th>
			<th>监听号码</th>
			<th>周期定位</th>
			
            <th>最后位置</th>
            <th>最后定位时间</th>
			<th>ACC状态</th>
            <th>开通时间</th>
            <th>电量</th>
			<?php if ($_SESSION['zbRole']=='0') echo '<th>操作</th>'; ?>
          </tr>
		  <?php $i=0;
		 // echo $zbapi->req(144,'{"Account":"'.$zbapi->account.'"}');
		  //print_r (json_decode($zbapi->req(144,'{"Account":"'.$zbapi->account.'"}')));
		  foreach($zbapi->devinfo as $v){
				$i++;
				if ($i>10) break;
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
				if ($_SESSION['zbRole']=='0') echo "<td><input type='checkbox' id='termIds' name='termIds' type='checkbox' value='$v[IMEI]'/></td>";
				echo "<td>$v[IMEI]</td><td>$v[Num]</td><td>$v[DevPhone]</td><td>$book</td><td>$LoopDes</td><td>$v[Addr]</td><td>$v[Time]</td><td>$Stat</td><td>$v[EnableTime]</td><td>$v[Power]%</td>";
				
				
				if ($_SESSION['zbRole']=='0') echo "<td><a href='javascript:' style='margin-right:5px;' onclick='OpenSwithCtrl(\"$v[IMEI]\", \"$v[ReplayState]\");'>[继电器控制]</a><a href='javascript:' style='margin-right:5px;' onclick='reSetIMEIFac(\"$v[IMEI]\");'>[恢复出厂设置]</a><a href='javascript:' style='margin-right:5px;' onclick='editSafeArea(\"$v[IMEI]\", \"$v[Lon]\", \"$v[Lat]\");'>[安全区域]</a><a href='javascript:' style='margin-right:5px;' onclick='startIMEIWatch(\"$v[IMEI]\");'>[监听]</a><a href='javascript:' style='margin-right:5px;' onclick='editTerm(\"$v[IMEI]\",\"$v[Num]\");'>[修改]</a> <a href='javascript:' onclick='delIMEITerm(\"$v[IMEI]\")'>[删除]</a> </td>";
				echo "</tr>";
				}?>
        </table>
		<div style="width:100%;height:35px;"><div class="pager"><?php if ($_SESSION['zbRole']=='0'){?><a href='#' onclick='newTerm();'>添加设备</a><a href='#' onclick='importTerm();'>导入设备</a><a href='#' onclick='delSelTerm();' style='margin-right:30px;'>删除选中项</a><?php } require_once 'page.php'; pageft($total,10,1,0,1,10,'','termGotoPage'); echo $pagenav;?></div></div>	
     </div>
     <!-- 蒙版 -->
<div class="blackDiv" style="font-size:13px;display:none;" id="idSafeArea" onclick="$('#idSafeArea').css('display','none');">	 
<div class="whiteDiv" style="z-index:9999;width:90%;left:4%;top:100px;" onclick="event.cancelBubble = true;" >
        <div class="inDiv">
            <label for="name">名称:</label>
            <input type="text" id="idSafeAreaName"  />
        </div> 
<div class="inDiv">
            <label for="name">类别:</label>
            <label for="male">圆形围栏</label>
            <input type="radio" name="idSafeType" id="idSafeType_0" onclick="safeAreaType=0;$('#idSafeAreaCity').css('display','none');"  />&nbsp&nbsp
            <label for="female">地市级围栏</label>
            <input type="radio" name="idSafeType" id="idSafeType_1" onclick="safeAreaType=1;$('#idSafeAreaCity').css('display','');safeMap.clearMap();" /><input type="text" id="idSafeAreaCity"  disabled=disabled style="background:#e0e0e0;display:none;"/> <span style='color:green'>点击地图选择围栏、城市</span>
        </div> 		
	<div style="width:100%;height:480px;" id="idSafeMap"></div>
        <div class="inDiv" style=" border-bottom: 1px solid #e1e1e1;">
           
        </div>  
        <div class="inDiv" >
            
        </div>  
        <div class="btn_f">
            <button class="save right"  onclick="savesafeArea();">保存</button>
			
            
			<button class="save right" style="margin-right:5px;" onclick="$('#idSafeArea').css('display','none');">取消</button>
			<button class="save right" style="margin-right:5px;width:150px;" onclick="closeSafeArea();">关闭安全区域</button>
        </div>   
</div>
</div>	 

<div class="blackDiv" style="font-size:13px;display:none;" id="idSwithCtrl" onclick="$('#idSwithCtrl').css('display','none');">	 
<div class="whiteDiv"  onclick="event.cancelBubble = true;" >
        <div class="inDiv">
            <label for="name">继电器密码:</label>
            <input type="text" id="idSwithCtrlPwd"  />
        </div> 
	
	
        <div class="inDiv" style=" border-bottom: 1px solid #e1e1e1;">
           
        </div>  
        <div class="inDiv" >
            
        </div>  
        <div class="btn_f">
            
			
            
			<button id="switchCtrlBtn0" class="save right" style="margin-right:5px;" onclick="SaveSwithCtrl(1);">恢复油电</button>
			<button id="switchCtrlBtn1" class="save right" style="margin-right:5px;" onclick="SaveSwithCtrl(0);">断油电</button>
        </div>  
		<div class="inDiv" >
            
        </div> 
		<div class="inDiv" >
            
        </div> 
		<div class="inDiv" >
            
        </div> 
		<div class="inDiv">
            <label for="name"><b>密码设置</b></label>
           
        </div> 
		
		<div class="inDiv">
            <label for="name">继电器新密码:</label>
            <input type="text" id="idSwithCtrlNewPwd"  />
        </div> 
		<div class="inDiv" style=" border-bottom: 1px solid #e1e1e1;">
            
        </div> 
		<div class="btn_f">
			
            <button class="save right"  onclick="SaveSwithCtrlPwd();">保存</button>
			<button class="save right" style="margin-right:5px;" onclick="$('#idSwithCtrl').css('display','none');">取消</button>
            
		</div>  
		
</div>
</div>
<div class="blackDiv" style="font-size:13px;display:none;" id="idTermEdit" onclick="$('#idTermEdit').css('display','none');">	 
<div class="whiteDiv" style="z-index:9999;width:50%;left:25%;" onclick="event.cancelBubble = true;" >
<div class="inDiv">
            <label for="name">所属分组:</label>
			<select name="idTermGroup" id="idTermGroup" style="width:155px;color:#666;font-size:13px;">
                
            </select>
            
        </div> 
		
        <div class="inDiv" id="idTermIMEIPanel">
            <label for="name">设备编号:</label>
            <input type="text" id="idTermIMEI" maxlength="15" onkeydown="isNumber1(event,-1);"  style="ime-mode:disabled" 
onkeyup="if (this.value.match(/[^0-9]/g))this.value=this.value.replace(/[^0-9]/g,'');" onpaste="return false" 
 /><span style="color:red">*</span>
        </div> 
		<div class="inDiv" id="idTermNumPanel">
            <label for="name">车牌号码:</label>
            <input type="text" id="idTermNum"  maxlength="8" onkeyup="if (this.value.match(/[^0-9a-zA-Z\u4e00-\u9fa5]/g))this.value=this.value.replace(/[^0-9a-zA-Z\u4e00-\u9fa5]/g,'');" onpaste="return false" /> 
        </div> 		
		
		<div class="inDiv" id="idTermPhonePanel">
            <label for="name">设备号码:</label>
            <input type="text" id="idTermPhone"  maxlength="11" onkeydown="isNumber1(event,-1);"  style="ime-mode:disabled" 
onkeyup="if (this.value.match(/[^0-9]/g))this.value=this.value.replace(/[^0-9]/g,'');" onpaste="return false" />
        </div> 		
		
		<div class="inDiv">
            <label for="name">监听号码:</label>
            <input type="text" id="idTermBook" onkeydown="isNumber1(event,188);" maxlength="36" style="ime-mode:disabled" 
onkeyup="if (this.value.match(/[^0-9,]/g))this.value=this.value.replace(/[^0-9,]/g,'');" onpaste="return false" /><span style="color:green">每个号码以（，）号结尾，如13888888888,13899999999,</span>
        </div> 		
	
	<div class="inDiv">
            <label for="name">优先定位:</label>
			<select name="idPreLocate" id="idPreLocate" style="width:155px;color:#666;font-size:13px;">
                <option value="0" selected="selected">基站定位</option>
                <option value="1">GPS定位</option>  
				<option value="2">WIFI定位</option>
            </select>
            
        </div> 	
		<div class="inDiv">
            <label for="name">定位间隔:</label>
            <input type="number" id="idLocIntval" maxlength="4" onkeydown="isNumber1(event,-1);" style="ime-mode:disabled" 
onkeyup="if (this.value.match(/[^0-9,]/g))this.value=this.value.replace(/[^0-9,]/g,'');" onpaste="return false"/><span style="color:green">(单位 秒) 默认为 60 秒 </span>
        </div> 
		<div class="inDiv">
            <label for="name">周期定位:</label>
             <label for="male">按月</label>
            <input type="radio" name="idLoopType" id="idLoopType_1" onclick="setLoopType(1);" value="1" checked=checked/>&nbsp&nbsp
            <label for="female">按周</label>
            <input type="radio" name="idLoopType" id="idLoopType_2" onclick="setLoopType(2);" value="2"/>&nbsp&nbsp
			<label for="female">按天</label>
            <input type="radio" name="idLoopType" id="idLoopType_3" onclick="setLoopType(3);" value="3"/>&nbsp&nbsp
			<label for="female">取消</label>
            <input type="radio" name="idLoopType" id="idLoopType_0" onclick="setLoopType(0);;" value="0"/>
			<span style="color:green">如果按月,周期数值=1,代表每月1号,如果按周,周期数值=1 周一,如果按天,周期数值=10:00 每天 10点定位</span>
			
        </div> 
		<div class="inDiv">
            <label for="name">周期数值:</label>
            <input type="text" id="idLoopValue" maxlength="2" onkeydown="isNumber1(event,-1);"  style="ime-mode:disabled" 
onkeyup="if (this.value.match(/[^0-9]/g))this.value=this.value.replace(/[^0-9]/g,'');" onpaste="return false" /><span id="idLoopValueUnit">1-31号</span> <input type="text" id="idLoopValue_2" maxlength="2" onkeydown="isNumber1(event,-1);"  style="width:60px;ime-mode:disabled" 
onkeyup="if (this.value.match(/[^0-9]/g))this.value=this.value.replace(/[^0-9]/g,'');" onpaste="return false" /><span id="idLoopValueUnit_2">分钟</span><span style="color:green">周期定位数值</span>
        </div> 
		<div class="inDiv">
            <label for="name">休眠间隔:</label>
            <input type="number" id="idSleepIntval"  maxlength="3" onkeydown="isNumber1(event,-1);"  style="ime-mode:disabled" 
onkeyup="if (this.value.match(/[^0-9,]/g))this.value=this.value.replace(/[^0-9,]/g,'');" onpaste="return false"/><span style="color:green">（单位 分钟）设备静止多长时间进入休眠，默认 为 20 分钟</span>
        </div> 
		<div class="inDiv">
            <label for="name">报警开关:</label>
            <label for="female">打开</label>
            <input type="radio" name="idAlarmStat" id="idAlarmStat_1" onclick="TermAlarmStat=1;" value="1"/>&nbsp&nbsp
			  <label for="male">关闭</label>
            <input type="radio" name="idAlarmStat" id="idAlarmStat_0" onclick="TermAlarmStat=0;" value="0" checked=checked/>&nbsp&nbsp
           
			
        </div> 
		<div class="inDiv">
            <label for="name">上报间隔:</label>
            <input type="number" id="idReportIntval"  maxlength="3" onkeydown="isNumber1(event,-1);"  style="ime-mode:disabled" 
onkeyup="if (this.value.match(/[^0-9,]/g))this.value=this.value.replace(/[^0-9,]/g,'');" onpaste="return false"/><span style="color:green">（单位 分钟）设备定位上报间隔 ,默认 5 分钟  </span>
        </div> 
        <div class="inDiv" style=" border-bottom: 1px solid #e1e1e1;">
           
        </div>  
        <div class="inDiv" >
            
        </div>  
        <div class="btn_f">
            
            <button class="save right" onclick="if (editTermStatus==0)saveNewTerm(); else saveEditTerm(editTermStatus==1);">保存</button>
			<button class="save right" style="margin-right:5px;" onclick="$('#idTermEdit').css('display','none');">取消</button>
        </div>   
</div>
</div>

<div class="blackDiv" style="font-size:13px;display:none;" id="idGroupVarEdit" onclick="$('#idGroupVarEdit').css('display','none');">	 
<div class="whiteDiv" style="z-index:9999;width:50%;left:25%;" onclick="event.cancelBubble = true;" >
<div class="inDiv">
            <label for="name">分组名称:</label>
			<input type="text" id="idGroupNameVar"  disabled="disabled"/>
			
                
            </select>
            
        </div> 
		
		<div style="display:none" class="inDiv">
            <label for="name">监听号码:</label>
            <input type="text" id="idTermBookVar" onkeydown="isNumber1(event,188);"  style="ime-mode:disabled" 
onkeyup="if (this.value.match(/[^0-9,]/g))this.value=this.value.replace(/[^0-9,]/g,'');" onpaste="return false"/><span style="color:green">空为不修改，多个号码用(;)分号分割，如13888888888;13899999999</span>
        </div> 		
	
	<div class="inDiv">
            <label for="name">优先定位:</label>
			<select name="idPreLocateVar" id="idPreLocateVar" style="width:155px;color:#666;font-size:13px;">
			<option value="-1" selected="selected">不修改</option>
                <option value="0" >基站定位</option>
                <option value="1">GPS定位</option>  
				<option value="2">WIFI定位</option>
            </select>
            
        </div> 	
		<div class="inDiv">
            <label for="name">定位间隔:</label>
            <input type="number" id="idLocIntvalVar" maxlength="4" value="-1" onkeydown="isNumber1(event,189);"  style="ime-mode:disabled;" 
onkeyup="if (this.value.match(/[^0-9\-]/g))this.value=this.value.replace(/[^0-9\-]/g,'');" onpaste="return false" onblur=" this.value=this.value.replace(/[^0-9\-]/g,'');"/><span style="color:green">-1不修改,(单位 秒) 默认为 60 秒 </span>
        </div> 
		<div class="inDiv">
            <label for="name">周期定位:</label>
			 <label for="male">不修改</label>
            <input type="radio" name="idLoopTypeVar" id="idLoopTypeVar_9" onclick="setLoopTypeVar(9);" value="-1" checked=checked/>&nbsp&nbsp
			
             <label for="male">按月</label>
            <input type="radio" name="idLoopTypeVar" id="idLoopTypeVar_1" onclick="setLoopTypeVar(1);" value="1" checked=checked/>&nbsp&nbsp
            <label for="female">按周</label>
            <input type="radio" name="idLoopTypeVar" id="idLoopTypeVar_2" onclick="setLoopTypeVar(2);" value="2"/>&nbsp&nbsp
			<label for="female">按天</label>
            <input type="radio" name="idLoopTypeVar" id="idLoopTypeVar_3" onclick="setLoopTypeVar(3);" value="3"/>&nbsp&nbsp
			<label for="female">取消</label>
            <input type="radio" name="idLoopTypeVar" id="idLoopTypeVar_0" onclick="setLoopTypeVar(0);" value="0"/>
			<span style="color:green">如果按月,周期数值=1,代表每月1号,如果按周,周期数值=1 周一,如果按天,周期数值=10:00 每天 10点定位</span>
			
        </div> 
		<div class="inDiv">
            <label for="name">周期数值:</label>
            <input type="text" id="idLoopValueVar" maxlength="2" onkeydown="isNumber1(event,-1);"  style="ime-mode:disabled" 
				onkeyup="if (this.value.match(/[^0-9]/g))this.value=this.value.replace(/[^0-9]/g,'');" onpaste="return false" /><span id="idLoopValueUnitVar">1-31号</span> <input type="text" id="idLoopValue_2Var" maxlength="2" onkeydown="isNumber1(event,-1);"  style="width:60px;ime-mode:disabled;display:none" 
				onkeyup="if (this.value.match(/[^0-9]/g))this.value=this.value.replace(/[^0-9]/g,'');" onpaste="return false" /><span id="idLoopValueUnit_2Var" style="display:none">分钟</span><span style="color:green">周期定位数值</span>
        </div> 
		<div class="inDiv">
            <label for="name">休眠间隔:</label>
            <input type="number" id="idSleepIntvalVar"  maxlength="3" value="-1" onkeydown="isNumber1(event,189);"  style="ime-mode:disabled" 
onkeyup="if (this.value.match(/[^0-9,]/g))this.value=this.value.replace(/[^0-9/-]/g,'');" onpaste="return false"/><span style="color:green">-1不修改,（单位 分钟）设备静止多长时间进入休眠，默认 为 20 分钟</span>
        </div> 
		<div class="inDiv">
            <label for="name">报警开关:</label>
            <label for="female">不修改</label>
            <input type="radio" name="idAlarmStatVar" id="idAlarmStatVar_9" onclick="TermAlarmStatVar=-1;" value="-1"/>&nbsp&nbsp
			<label for="female">打开</label>
            <input type="radio" name="idAlarmStatVar" id="idAlarmStatVar_1" onclick="TermAlarmStatVar=1;" value="1"/>&nbsp&nbsp
			  <label for="male">关闭</label>
            <input type="radio" name="idAlarmStatVar" id="idAlarmStatVar_0" onclick="TermAlarmStatVar=0;" value="0" checked=checked/>&nbsp&nbsp
           
			
        </div> 
		<div class="inDiv">
            <label for="name">上报间隔:</label>
            <input type="number" id="idReportIntvalVar"  maxlength="3" value="-1" onkeydown="isNumber1(event,189);"  style="ime-mode:disabled" 
onkeyup="if (this.value.match(/[^0-9,]/g))this.value=this.value.replace(/[^0-9/-]/g,'');" onpaste="return false"/><span style="color:green">-1不修改,（单位 分钟）设备定位上报间隔 ,默认 5 分钟  </span>
        </div> 
        <div class="inDiv" style=" border-bottom: 1px solid #e1e1e1;">
           
        </div>  
        <div class="inDiv" >
            
        </div>  
        <div class="btn_f">
            
            <button class="save right" onclick="saveGroupVar();">保存</button>
			<button class="save right" style="margin-right:5px;" onclick="$('#idGroupVarEdit').css('display','none');">取消</button>
        </div>   
</div>
</div>

<div class="blackDiv" style="font-size:13px;display:none;" id="idTermImport" onclick="$('#idTermImport').css('display','none');">	 
<div class="whiteDiv" style="z-index:9999;width:40%;left:30%;height:440px;" onclick="event.cancelBubble = true;" >
	<div class="inDiv">
            <label for="name">所属分组:</label>
			<select name="idTermGroupImpt" id="idTermGroupImpt" style="width:155px;color:#666;font-size:13px;">
                
            </select>
            
        </div> 
        <div class="inDiv">
		    导入设备
            <textarea id="idImporttxt" style="width:100%;height:300px;border: 1px solid #e1e1e1;"></textarea>
        </div> 
	
        
		<div class="inDiv" ></div>
        <div class="inDiv" >
          <span style="color:green"> 一行一台设备，导入格式设备编号，车牌号码，设备号码，监听号码</span>
        </div>  
		<div class="inDiv" style=" border-bottom: 1px solid #e1e1e1;"></div>  
        <div class="btn_f">
            
            <button class="save right" onclick="saveImportTerm();">保存</button>
			<button class="save right" style="margin-right:5px;" onclick="$('#idTermImport').css('display','none');">取消</button>
        </div>   
</div>
</div>


<div class="blackDiv" style="font-size:13px;display:none;" id="idEditGroup" onclick="$('#idEditGroup').css('display','none');">	 
<div class="whiteDiv" style="z-index:9999;" onclick="event.cancelBubble = true;" >
		 <div class="inDiv"></div>
        <div class="inDiv">
            <label for="name">分组名称:</label>
            <input type="text" id="idGroupName" maxlength="30" />
        </div> 
	
        
		<div class="inDiv" ></div>
         
		<div class="inDiv" style=" border-bottom: 1px solid #e1e1e1;"></div>  
        <div class="btn_f">
            
            <button class="save right" onclick="saveGroupInfo();">保存</button>
			<button class="save right" style="margin-right:5px;" onclick="$('#idEditGroup').css('display','none');">取消</button>
        </div>   
</div>
</div>

    </div>
	
	
	<div class="con1 none" id="idUserPanel">
	<div class="panelTop" >
     <div class="searchDiv" style="float:left;width:350px;">
        <input type="text" class="left" placeholder="请输入关键字" id="idUserKey" style="font-size:14px;padding:6px;margin:0px;width:310px;"/>
        <img src="resources/images/search.png" alt="" width="16" class="right" onclick="serachUser();" />
     </div> </div>
     <div class="tDiv" id="idUserList">
         <table border="1" cellspacing="0" cellpadding="0" >
          <tr>
           <?php if ($_SESSION['zbRole']=='0') {?> <th><input onclick="selAllUser(this)"  type='checkbox' />全选</th><?php }?>
			<th>帐号</th>
			<th>用户类别</th>
            <th>用户名称</th>
            <th>手机号码 </th>
			<th>Email</th>
			 <?php if ($_SESSION['zbRole']=='0') {?><th>操作</th><?php }?>
          </tr>
		  <?php 
		 
		  $userinfo=$zbapi->loadUserInfo();
		  apc_store('zb_userinfo'.$_SESSION['zbAcc'],$userinfo);
		  $i=0;
		  $me='';
		 // print_r($userinfo);
		  foreach($userinfo as $v){
				$i++;
				if ($i>10) break;
				
				if ($v['Role'])$usertype='普通用户';
				else $usertype='管理员'; 
				if (($v['Name']===$_SESSION['zbUsername'])&&($v['Phone']===$_SESSION['zbPhone'] || $v['Account']===$_SESSION['zbAcc'])) {
					$Choosdis='disabled="disabled"';
					$me='<b style="color:#666">(我)</b>';
				}
				else{
					$me='';
					$Choosdis='';
				} 
				echo "<tr>";
				if ($_SESSION['zbRole']=='0') echo "<td><input id='userIds' name='userIds' $Choosdis type='checkbox' value='$v[Phone]'/>";
				echo "</td><td>$v[Account]$me ";
				echo "</td><td>$usertype</td><td>$v[Name]</td><td>$v[Phone]</td><td>$v[Email]</td>";
				if ($_SESSION['zbRole']=='0'){
					
					echo "<td><a href='javascript:' style='margin-right:5px;' onclick='editUser(\"$v[Account]\",\"$v[Name]\",\"$v[Phone]\",\"$v[Email]\",$v[Role]);'>[修改]</a> ";  
					if ($v['Phone']!=$_SESSION['zbAcc'] && $v['Account']!=$_SESSION['zbAcc']) {
						echo "<a href='javascript:' onclick='delUserName(\"$v[Phone]\")'>[删除]</a>";
					}
				}
				if ($_SESSION['zbRole']=='0') {
					if (($v['Name']==$_SESSION['zbUsername'])&&($v['Phone']==$_SESSION['zbPhone'] || $v['Account']==$_SESSION['zbAcc'])) {
						echo"<a href='javascript:' style='margin-right:5px;' onclick='changeUserPer(\"$v[Phone]\")'>[转移权限]</a>";
					}
				}
				echo " </td>";
				echo "</tr>";
				}?>
        </table>
		<div style="width:100%;height:35px;"><div class="pager"><?php if ($_SESSION['zbRole']=='0'){?><a href='#' onclick='newUser();'>添加用户</a><a href='#' onclick='delSelUser();' style='margin-right:30px;'>删除选中用户</a><?php }?><?php  pageft(count($userinfo),10,1,0,1,10,'','userGotoPage'); echo $pagenav;?></div></div>	
     </div>
     <!-- 蒙版 -->
<div class="blackDiv" style="font-size:13px;display:none;" id="idUserEdit" onclick="$('#idUserEdit').css('display','none');">	 
<div class="whiteDiv" style="z-index:9999" onclick="event.cancelBubble = true;" >
<div class="inDiv">
            <label for="name">用户帐号:</label>
            <input type="text" id="idUserAcc" maxlength="30" 
				   onkeyup="if (this.value.match(/[^0-9a-zA-Z:@\-_]/g))this.value=this.value.replace(/[^0-9a-zA-Z:@\-_]/g,'');"  onpaste="return false" /><span style="color:red">*</span>
        </div> 
		<div class="inDiv">
            <label for="name">登录密码:</label>
            <input type="text" id="idUserPwd" maxlength="64" /><span id="idPwdspan_1" style="color:red">*</span><span id="idPwdspan_2" style="color:green">空为不修改密码</span>
        </div> 
		<div id="idUserTypeDIV"class="inDiv">
            <label for="name">用户类别: </label>
            <select name="province" id="idUserType" style="width:155px;color:#666;font-size:13px;">
                <option value="1" selected="selected">普通用户</option>
                <option value="0">管理员</option>

            </select>
        </div>	
        <div class="inDiv">
            <label for="name">用户名称:</label>
            <input type="text" id="idUserName"  maxlength="50" /><span style="color:red">*</span>
        </div> 
<div class="inDiv">
            <label for="name">手机号码:</label>
            <input type="text" id="idUserPhone" maxlength="11" onkeydown="isNumber1(event,-1);"  style="ime-mode:disabled" 
onkeyup="if (this.value.match(/[^0-9]/g))this.value=this.value.replace(/[^0-9]/g,'');" onpaste="return false"/><span style="color:red">*</span>
        </div> 		
<div class="inDiv">
            <label for="email" style="margin-right:20px;">Email:</label>
            <!--input type="text" id="idUserMail"  maxlength="200" onkeyup="if (this.value.match(/[^0-9a-zA-Z:@\-_]/g))this.value=this.value.replace(/[^0-9a-zA-Z:@\-_]/g,'');"  onafterpaste="if (this.value.match(/[^0-9a-zA-Z:@\-_]/g))this.value=this.value.replace(/[^0-9a-zA-Z:@\-_]/g,'');"/><span style="color:red">*</span-->
            <input type="text" id="idUserMail"  maxlength="200" /><span style="color:red">*</span>
        </div> 	
	
		
        <div class="inDiv" style=" border-bottom: 1px solid #e1e1e1;">
           
        </div>  
        <div class="inDiv" >
            
        </div>  
        <div class="btn_f">
            
            <button class="save right" onclick="saveNewUser();">保存</button>
			<button class="save right" style="margin-right:5px;" onclick="$('#idUserEdit').css('display','none');">取消</button>
        </div>   
</div>
</div>
    </div>
	
	
	<div class="con1 none">
	<div class="panelTop" >
     <div class="searchDiv" style="float:left;width:350px;">
    
        <input type="text" class="left" placeholder="请输入关键字" id="idAlarmKey" style="font-size:14px;padding:6px;margin:0px;width:310px;"/>
        <img src="resources/images/search.png" alt="" width="16" class="right" onclick="alarmGotoPage(1);" />
     </div> </div>
     <div class="tDiv" id="idAlarmList">
         <table border="1" cellspacing="0" cellpadding="0" >
          <tr>
          
            <th>序号</th>
            <th>设备编号 </th>
            <th>车牌号</th>
			<th>通知时间</th>
			<th>通知内容</th>
          </tr>
		  <?php 
		  $alarmInfo=$zbapi->loadAlarmInfo();
		  apc_store('zb_alarmInfo'.$_SESSION['zbAcc'],$alarmInfo);
		  $i=0;
		  $usertype='';
		  foreach($alarmInfo as $v){
				$i++;
				if ($i>10) break;
				
				if ($v['Type']==1)$usertype='振动报警';
				else if ($v['Type']==2)$usertype='超速报警';
				else if ($v['Type']==3)$usertype='非法开门报警';
				else if ($v['Type']==4)$usertype='非法启动报警';
				else if ($v['Type']==5)$usertype='围栏报警(出)';
				else if ($v['Type']==6)$usertype='围栏报警(进)';
				else if ($v['Type']==7)$usertype='设备脱落报警（光感脱落）';
				else if ($v['Type']==8)$usertype='设备低电量报警（达到即将关机的阈值）';
				
		
				echo "<tr><td>$v[AlarmId]</td><td>$v[IMEI]</td><td>$v[Number]</td><td>$v[Time]</td><td>$usertype</td></tr>";
				}?>
        </table>
		<div style="width:100%;height:35px;"><div class="pager"><?php  pageft(count($alarmInfo),10,1,0,1,10,'','alarmGotoPage'); echo $pagenav;?></div></div>	
     </div>

    </div>
	
<?php if ($_SESSION['zbRole']=='0'){?>
	<div class="con1 none" id="idSetupPanel">
     
     <div class="tDiv" id="idSysSet" style="height:70%;margin-top:50px;padding:30px 10px 10px 10px;">
	
	 <div style="margin:10px 0px 0px 120px;">
	 <div style="font-size:16px;font-weight:bold;height:30px;">周期定位设置</div>
	 
	
		<div class="inDiv">
            <label for="name">周期定位类型:</label>
			 
           
             <label for="male">按月</label>
            <input type="radio" name="id_ZJType" id="id_ZJType_1" onclick="setZJType(1);" value="1" checked=checked/>&nbsp&nbsp
            <label for="female">按周</label>
            <input type="radio" name="id_ZJType" id="id_ZJType_2" onclick="setZJType(2);" value="2"/>&nbsp&nbsp
			<label for="female">按天</label>
            <input type="radio" name="id_ZJType" id="id_ZJType_3" onclick="setZJType(3);" value="3"/>&nbsp&nbsp
			<label for="female">取消</label>
            <input type="radio" name="id_ZJType" id="id_ZJType_4" onclick="setZJType(0);" value="0"/>
			<span style="color:green">如果按月,周期数值=1,代表每月1号,如果按周,周期数值=1 周一,如果按天,周期数值=10:00 每天 10点定位</span>
			
        </div> 
		<div class="inDiv">
            <label for="name">周期定位数值:</label>
            <input type="text" id="idLoopValueVar_a" maxlength="2" onkeydown="isNumber1(event,-1);"  style="ime-mode:disabled" 
				onkeyup="if (this.value.match(/[^0-9]/g))this.value=this.value.replace(/[^0-9]/g,'');" onpaste="return false" /><span id="idLoopValueUnitVar_a">1-31号</span> <input type="text" id="idLoopValue_2Var_a" maxlength="2" onkeydown="isNumber1(event,-1);"  style="width:60px;ime-mode:disabled;display:none" 
				onkeyup="if (this.value.match(/[^0-9]/g))this.value=this.value.replace(/[^0-9]/g,'');" onpaste="return false" /><span id="idLoopValueUnit_2Var_a" style="display:none">分钟</span><span style="color:green"></span>
        </div>
		
	
		<button onclick="reSetZJLocate()" style="background-color: #2398ff;font-size:16px;padding:0px 5px;color:white;width:160px;height:30px;margin:10px 0px 0px 0px;">保存周期定位设置</button>
	 </div>
     <button onclick="reSetFac()" style="background-color: #2398ff;font-size:16px;padding:0px 5px;color:white;width:160px;height:30px;margin:50px 0px 0px 120px;">恢复出厂设置</button>    
     </div>

    </div>	
<?php }?>	
</div>    

<div id="dialog-message" title="请重新登陆系统" style="display:none;">
  <p style="font-size:12px;">由于您长时间不登录，系统已自动关闭，请重新登录！ </p>
</div>

<div id="dialog-form" title="请选择接受权限的用户" style="display:none;">
 
  <form>
    <fieldset>
    <select name="speed" id="speed">
      <option selected="selected">请选择用户</option>
<?php
		foreach ($userinfo as $user) {
			if ($user['Account']== $_SESSION['zbAcc'] || $user['Role'] == 0) {
				continue;
			}

			echo "<option value='{$user['Phone']}'>{$user['Name']} ({$user['Account']})</option>";
		}
	?>
    </select>
      <!-- Allow form submission with keyboard without duplicating the dialog button -->
      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
    </fieldset>
  </form>
</div>

    </body>
</html>
<script type="text/javascript">
<?php echo $JSStr;?>
	var editGroupStat=0;
	var TermAlarmStat=1;
	var TermLoopType=1;
	var TermAlarmStatVar=-1;
	var TermLoopTypeVar=1;
	var ZJSetupType=1;
	var lastTrackTime=0;
	var editTermStatus=0;
	var editUserStatus=0;
	var safeAreaType=0;
	var mapEditor={};
	var mFlag=false;
	var divX,bakX;
	var isplay=0;
	var TrackPos=0;
	var defaultIMEI='';
	var marker=0;
	var circle=0;
	var lineArr = [];
	var timeArr = [];
	var AddrTimes = [];
	
	var groups = <?php echo json_encode($zbapi->groupinfo);?>;
	var arrPosArr =  new Array([720]);
	var map = new AMap.Map('idMap', {
        resizeEnable: true,
        zoom:11,
        <?php if ($DefLon && $DefLat) echo "center: [$DefLon,$DefLat]";?>
    });
	
	var safeMap = new AMap.Map('idSafeMap', {
        resizeEnable: true,
        zoom:15,
        <?php if ($DefLon && $DefLat) echo "center: [$DefLon,$DefLat]";?>
    });

function SaveSwithCtrlPwd(){

	
	var Pwd=$("#idSwithCtrlNewPwd").val();
	if (Pwd==""){
		alert("请输入继电器新密码.");
		return 0;
	}
	
	
	$.post("ajs.php",{act:9034,Pwd:Pwd},
	function(s){if (s.indexOf("成功")>2){$("#idSwithCtrl").css("display","NONE");}alert(s);});
	
}	
	
function SaveSwithCtrl(Stat){
	var Pwd=$("#idSwithCtrlPwd").val();
	if (Pwd==""){
		alert("请输入继电器密码.");
		return 0;
	}
	if (Stat == 0) {
		if (!confirm("确定对车辆进行断油电")) {
			return;
		}
	} else {
		if (!confirm("确定对车辆进行恢复油电")) {
			return;
		}
	}
	$.post("ajs.php",{act:9033,IMEI:defaultIMEI,Pwd:Pwd,Stat:Stat}, function(s){
		if (s.indexOf("成功")>2){
			$("#idSwithCtrl").css("display","NONE");
		}
		alert(s);
	});
	
}	
function OpenSwithCtrl(IMEI, replyStatus){
	defaultIMEI=IMEI;
	$("#idSwithCtrlNewPwd").val("");
	$("#idSwithCtrlPwd").val("");
	$("#idSwithCtrl").css("display","");
	//$("#switchCtrlBtn"+replyStatus).css("display", "");
}
function selAllUser(a){
	$("[name='userIds']").each(function(){ if (!$(this).prop("disabled"))$(this).prop('checked',a.checked);});
}	
function selAllTerm(a){
	$("[name='termIds']").each(function(){ if (!$(this).prop("disabled"))$(this).prop('checked',a.checked);});
}	
		
function setLoopType(LoopType){
	TermLoopType=parseInt(LoopType);
	
	if (LoopType==0){
		$("#idLoopValueUnit").css("display","none");
		$("#idLoopValue").css("width","150px");
		$("#idLoopValue_2").css("display","none");
		$("#idLoopValue").prop("disabled","disabled");
		
	}else if (LoopType==1){
		$("#idLoopValue").removeProp("disabled");
		$("#idLoopValueUnit").css("display","");	
		$("#idLoopValue").css("width","150px");
		$("#idLoopValueUnit").html("1-31号");
		$("#idLoopValueUnit_2").css("display","none");
		$("#idLoopValue_2").css("display","none");
	}else if (LoopType==2){
		$("#idLoopValue").removeProp("disabled");
		$("#idLoopValueUnit").css("display","");
		$("#idLoopValue").css("width","150px");
		$("#idLoopValueUnit").html("周1-7");
		$("#idLoopValueUnit_2").css("display","none");
		$("#idLoopValue_2").css("display","none");	
	}else if (LoopType==3){
		$("#idLoopValue").removeProp("disabled");
		$("#idLoopValueUnit").css("display","");
		$("#idLoopValue").css("width","66px");
		$("#idLoopValueUnit").html("点");
		$("#idLoopValueUnit_2").css("display","");
		$("#idLoopValue_2").css("display","");
		}	
	
}

function setZJType(LoopType){
	ZJSetupType=parseInt(LoopType);
	
	if (LoopType==0 || LoopType==9){
		$("#idLoopValueUnitVar_a").css("display","none");
		$("#idLoopValueVar_a").css("width","150px");
		$("#idLoopValue_2Var_a").css("display","none");
		$("#idLoopValueUnit_2Var_a").css("display","none");
	}else if (LoopType==1){
		$("#idLoopValueUnitVar_a").css("display","");	
		$("#idLoopValueVar_a").css("width","150px");
		$("#idLoopValueUnitVar_a").html("1-31号");
		$("#idLoopValueUnit_2Var_a").css("display","none");
		$("#idLoopValue_2Var_a").css("display","none");
	}else if (LoopType==2){
		$("#idLoopValueUnitVar_a").css("display","");
		$("#idLoopValueVar_a").css("width","150px");
		$("#idLoopValueUnitVar_a").html("周1-7");
		$("#idLoopValueUnit_2Var_a").css("display","none");
		$("#idLoopValue_2Var_a").css("display","none");	
	}else if (LoopType==3){
		$("#idLoopValueUnitVar_a").css("display","");
		$("#idLoopValueVar_a").css("width","66px");
		$("#idLoopValueUnitVar_a").html("点");
		$("#idLoopValueUnit_2Var_a").css("display","");
		$("#idLoopValue_2Var_a").css("display","");
		}	
	
}

	
function setLoopTypeVar(LoopType){
	TermLoopTypeVar=parseInt(LoopType);
	
	if (LoopType==0 || LoopType==9){
		$("#idLoopValueUnitVar").css("display","none");
		$("#idLoopValueVar").css("width","150px");
		$("#idLoopValue_2Var").css("display","none");
		$("#idLoopValueUnit_2Var").css("display","none");
	}else if (LoopType==1){
		$("#idLoopValueUnitVar").css("display","");	
		$("#idLoopValueVar").css("width","150px");
		$("#idLoopValueUnitVar").html("1-31号");
		$("#idLoopValueUnit_2Var").css("display","none");
		$("#idLoopValue_2Var").css("display","none");
	}else if (LoopType==2){
		$("#idLoopValueUnitVar").css("display","");
		$("#idLoopValueVar").css("width","150px");
		$("#idLoopValueUnitVar").html("周1-7");
		$("#idLoopValueUnit_2Var").css("display","none");
		$("#idLoopValue_2Var").css("display","none");	
	}else if (LoopType==3){
		$("#idLoopValueUnitVar").css("display","");
		$("#idLoopValueVar").css("width","66px");
		$("#idLoopValueUnitVar").html("点");
		$("#idLoopValueUnit_2Var").css("display","");
		$("#idLoopValue_2Var").css("display","");
		}	
	
}
function editGroupVar(){
	var SelId=$("#idSelectGroup").val();
	if (SelId<1){
		alert("请先选择要设置参数的分组.");
		return 0;
	}

	var newGroupValue = '';
	$("#idTermEdit").showLoading();
	$.post("ajs.php",{ act:9040,groupId:SelId,}, function(data) {
		data = data.split("\n");
		var rets = [];
		for( var i=0; i < data.length; i++) {
			if (data[i] == "") continue;
			rets.push(data[i]);
		}
		newGroupValue = eval("("+rets.join("") + ")");
		$("#idTermEdit").hideLoading();

		var oldValue = newGroupValue;
		$('#idGroupNameVar').val($("#idSelectGroup").find("option:selected").text());
		TermAlarmStatVar=-1;
		TermLoopTypeVar=-1;
		$('#idTermBookVar').val('');
		$('#idPreLocateVar').val(oldValue['PreLocate']);
		$('#idLocIntvalVar').val(oldValue['LocIntval']);

		$('#idLoopValueVar').val(oldValue['LoopValue']);
		$('#idLoopTypeVar_'+oldValue['LoopType']).prop('checked','checked');
		$('#idAlarmStatVar_'+oldValue['AlarmState']).prop('checked','checked');
		$('#idReportIntvalVar').val(oldValue['ReportIntval']);
		$('#idSleepIntvalVar').val(oldValue['SleepIntval']);
		$('#idGroupVarEdit').css('display','');
		$("#idTermBookVar").focus();	
	});
}	
function saveGroupVar(){
	var SelId=$("#idSelectGroup").val();
	var Book=$('#idTermBookVar').val();
	var PreLocate=$('#idPreLocateVar').val();
	var LoopValueVar=$('#idLoopValueVar').val();
	var LocIntval=$('#idLocIntvalVar').val();
	var ReportIntval=$('#idReportIntvalVar').val();
	var SleepIntval=$('#idSleepIntvalVar').val();
	if (TermLoopTypeVar==0) LoopValueVar=0;
	
	
	if (TermLoopTypeVar==1 && (LoopValueVar<1 || LoopValueVar>31)){
		alert("周期定位数值错误.请输入有效的周期定位数值.");
		$('#idLoopValue').focus();
		return 0;
	}
	if (TermLoopTypeVar==2 && (LoopValueVar<1 || LoopValueVar>7)){
		alert("周期定位数值错误.请输入有效的周期定位数值.");
		$('#idLoopValueVar').focus();
		return 0;
	}
	if (TermLoopTypeVar==3){
		
		if (LoopValueVar >23){
			alert("周期定位数值错误.请输入有效的周期定位数值.");
		$('#idLoopValueVar').focus();
		return 0;
		}
		tt=$('#idLoopValue_2Var').val();
		if (tt >59){
			alert("周期定位数值错误.请输入有效的周期定位数值.");
		$('#idLoopValueVar').focus();
		return 0;
		}
		if (tt=="") tt="00";
		if (LoopValue=="") LoopValue="00";
		LoopValueVar=LoopValueVar+':'+tt;
	}
	

	$.post("ajs.php",{act:9032,GroupId:SelId,Book:Book,PreLocate:PreLocate,LoopType:TermLoopTypeVar,LoopValue:LoopValueVar,LocIntval:LocIntval,ReportIntval:ReportIntval,SleepIntval:SleepIntval,AlarmStat:TermAlarmStatVar},
	function(s){
		if (s.indexOf("成功")>2){
			$('#idGroupVarEdit').css('display','none');
			termGotoPage(termPage);
			var bak=curPanel;
			curPanel=0;
			search_1();
			curPanel=bak;
			search_2();
			}
			alert(s);
			});
}

function saveGroupInfo(){
	var GroupName=$("#idGroupName").val();
	var existsGroup = [];
	$("#idSelectGroup").find("option").each(function(){
		existsGroup.push($(this).text());
	});

	if (existsGroup.indexOf(GroupName) != '-1') {
		alert("已经存在这个分组！");
		return;
	}
	if (GroupName==""){
		alert("请输入分组名称.");
		return 0;
	}
	var SelId=$("#idSelectGroup").val();
	$.post("ajs.php",{act:9028+editGroupStat,ID:SelId,Group:GroupName},function(s){
			var str=s.split(String.fromCharCode(1));
			if (str[1]=='0'){
				if (str[2].length>10)
					$("#idSelectGroup").html(str[2]);
				if (editGroupStat==0) alert("添加分组成功.");
				else  alert("修改分组成功.");
				$("#idEditGroup").css("display","none");
			}
			else 	
			alert(str[2]);
		});	
}	
function deleteGroup(){
	var SelId=$("#idSelectGroup").val();
	
	if (SelId<1){
		alert("请先选择要删除的分组.");
		return;
	}
	$.post("ajs.php",{act:9030,GroupId:SelId},function(s){
			if (s.indexOf("成功")>3){
				$("#idSelectGroup").find("option:selected").remove();
				serachTerm();
			} 
			alert(s);
		});	
}
function editGroup(Stat){
	editGroupStat=Stat;
	var SelTxt=$("#idSelectGroup").find("option:selected").text();
	var SelId=$("#idSelectGroup").val();
	
	if (SelId<1 && Stat==1){
		alert("请先选择要修改的分组.");
		return;
	}
	if (Stat==1){
		$("#idGroupName").val(SelTxt);	
	}else $("#idGroupName").val("");
	 $("#idEditGroup").css("display","");
	$("#idGroupName").focus();
}	   
function clearMap(){
	return;
	map.clearMap();
	map.clearInfoWindow();
	isplay=0;
	marker=0;
	$("#idTrackPos").css("left","-10px");
	for (var i=0;i<720;i++)
		$("#idtrackTab tr td:nth-child("+i+")").css("background-color","#EEE");
}	
function reSetFac(){
	if (confirm('您确认要恢复出厂设置吗？')){
		$.post("ajs.php",{act:9021},function(s){
			
			alert(s);
		});	
	}
}

function closeSafeArea(){

	$.post("ajs.php",{act:9027,IMEI:defaultIMEI},
		function(s){
			alert(s);
			if (s.indexOf("成功")>2)$('#idSafeArea').css('display','none');
			
		}
		);	
}

function reSetZJLocate(){
	
	var LoopValueVar=$('#idLoopValueVar_a').val();
	
	if (ZJSetupType==0) LoopValueVar=0;
	
	
	if (ZJSetupType==1 && (LoopValueVar<1 || LoopValueVar>31)){
		alert("周期定位数值错误.请输入有效的周期定位数值.");
		$('#idLoopValue_a').focus();
		return 0;
	}
	if (ZJSetupType==2 && (LoopValueVar<1 || LoopValueVar>7)){
		alert("周期定位数值错误.请输入有效的周期定位数值.");
		$('#idLoopValueVar_a').focus();
		return 0;
	}
	if (ZJSetupType==3){
		
		if (LoopValueVar >23){
			alert("周期定位数值错误.请输入有效的周期定位数值.");
		$('#idLoopValueVar_a').focus();
		return 0;
		}
		tt=$('#idLoopValue_2Var_a').val();
		if (tt >59){
			alert("周期定位数值错误.请输入有效的周期定位数值.");
		$('#idLoopValueVar_a').focus();
		return 0;
		}
		if (tt=="") tt="00";
		if (LoopValue=="") LoopValue="00";
		LoopValueVar=LoopValueVar+':'+tt;
	}
	
	
	$.post("ajs.php",{act:9022,Type:ZJSetupType,Value:LoopValueVar},function(s){
		
		alert(s);
	});	
}

function reSetIMEIFac(IMEI){
	if (confirm('您确认要恢复出厂设置吗？')){
		$.post("ajs.php",{act:9025,IMEI:IMEI},function(s){
			if (s.indexOf("成功")>2) termGotoPage(termPage);
			alert(s);
		});	
	}
}
function startIMEIWatch(IMEI){
		$.post("ajs.php",{act:9026,IMEI:IMEI},function(s){
			
			alert(s);
		});	
}
function locateTerm(IMEI,Stat){
	
	$.post("ajs.php",{act:9020,IMEI:IMEI},function(s){
		var str=s.split(String.fromCharCode(1));
		if (str[1]=='0'){
			if (str[2]=='2') str[2]='休眠';
			else if (str[2]=='1') str[2]='在线';
			else str[2]='离线';
			createInfoWindow(IMEI,str[2],str[3],str[4],str[5],str[6],str[7],str[8])
			if (Stat!=str[2]) {
				search_1();
				search_2
			}
		}else alert(str[2]);
	});
}
 function createInfoWindow(IMEI, Stat,Addr,Time,Lon,Lat,Power,AccStat) {
	
	 map.clearInfoWindow();
	 if (Lon==''){
		 Alert('无此车辆定位信息', true);
		 return 0;
	 }
	 var info = document.createElement("div");
	 info.className = "info";
	 closeInfoWindow();
	 if (AccStat=='0') AccStat='关闭';
	 else if (AccStat=='1') AccStat='打开';
	 else AccStat='空';	
	 //可以通过下面的方式修改自定义窗体的宽高
	 //info.style.width = "400px";
	 // 定义顶部标题
	 var top = document.createElement("div");
	 var titleD = document.createElement("div");
	 var closeX = document.createElement("img");
	 top.className = "info-top";
	 titleD.innerHTML = IMEI;
	 closeX.src = "http://webapi.amap.com/images/close2.gif";
	 closeX.onclick = closeInfoWindow;

	 top.appendChild(titleD);
	 top.appendChild(closeX);
	 info.appendChild(top);

	 // 定义中部内容
	 var middle = document.createElement("div");
	 middle.className = "info-middle";
	 middle.style.backgroundColor = 'white';
	 middle.innerHTML = '<div class="inDiv" ><label for="name">状态: </label> <label for="name"> '+Stat+'</label></div>'+
		 '<div class="inDiv" ><label for="name">地址: </label> <label for="name"> '+Addr+'</label></div>'+
		 '<div class="inDiv" ><label for="name">ACC状态: </label> <label for="name"> '+AccStat+'</label></div>'+
		 '<div class="inDiv" ><label for="name">电量: </label> <label for="name"> '+Power+'%</label></div>'+
		 '<div class="inDiv" ><label for="name">时间: </label> <label for="name"> '+Time+'</label></div>';
	 info.appendChild(middle);

	 // 定义底部内容
	 var bottom = document.createElement("div");
	 bottom.className = "info-bottom";
	 bottom.style.position = 'relative';
	 bottom.style.top = '0px';
	 bottom.style.margin = '0 auto';
	 var sharp = document.createElement("img");
	 sharp.src = "http://webapi.amap.com/images/sharp.png";
	 bottom.appendChild(sharp);
	 info.appendChild(bottom);
	 //可以通过下面的方式修改自定义窗体的宽高
	 //info.style.width = "400px";
	 // 定义顶部标题


	 var marker = new AMap.Marker({
		 map: map,
		 position: [Lat,Lon]
	 });	

	 var _click = function(e) {
		 map.clearInfoWindow();
		 var infoWindow = new AMap.InfoWindow({
			 isCustom: true,  //使用自定义窗体
				 autoMove: true,
				 position: [Lat, Lon],
				 content: info,
				 offset: new AMap.Pixel(16,-45)
		 });
		 infoWindow.open(map,e.target.getPosition());
	 }
	 marker.on("click", _click);
	 map.setCenter([Lat,Lon]);
	 setTimeout(function(){
		 map.clearInfoWindow();
		 var infoWindow = new AMap.InfoWindow({
			 isCustom: true,  //使用自定义窗体
				 autoMove: true,
				 position: [Lat, Lon],
				 content: info,
				 offset: new AMap.Pixel(16,-45)
		 });
		 infoWindow.open(map,marker.getPosition());
	 }, 500);
	 return info;
 }
 function closeInfoWindow() {
        map.clearInfoWindow();
		map.clearMap();
		$('#idPlay').attr('src','resources/images/play.png');
    }	
function playTrack(speed){
	if (isplay==0 && marker){
		map.clearInfoWindow();
		isplay=1;
		$('#idPlay').attr('src','resources/images/stop.jpg');
		//doplayTrack(speed);
		demo(speed);
	}else {
		isplay=0;
		clearTimeout(stopTimeout);
		$('#idPlay').attr('src','resources/images/play.png');
	}
}	
function doplayTrack(speed){
	var normal = 1000, speed = 1;
	var _need = normal * (speed/10);
	if (isplay==1 && marker){
		if (TrackPos>719){
			isplay=0;
			$('#idPlay').attr('src','resources/images/play.png');
			TrackPos=timeArr[0];
			//--------------------------------------------------
			// debugger;
			// -------------------------------------------------- 
			t(TrackPos);	
			map.setCenter(lineArr[arrPosArr[TrackPos]]);			
		} else {
			//--------------------------------------------------
			// debugger;
			// -------------------------------------------------- 
			TrackPos++ ;
			for (var i=0;i<60 && TrackPos<720;i++) {
				if(arrPosArr[TrackPos]>=0) {
					TrackPos += speed;
					break; 
				} 
			}
			$("#idTrackPos").css("left",(TrackPos-10)+"px");
			if (arrPosArr[TrackPos]>=0)marker.setPosition(lineArr[arrPosArr[TrackPos]]);
			if (TrackPos % 5 ==0) map.setCenter(lineArr[arrPosArr[TrackPos]]);
			window.setTimeout(doplayTrack,_need);
		}	
	}
	
}

var c = 0;
var currentStatus = 0;
function demo(speed) {
	var normal = 1000;
	currentStatus = 1;
	$(lineArr).each(function(i, data){
		if (c == i) {
			marker.setPosition(data);
			map.setCenter(data);
			$("#idTrackPos").css("left",(i+10)+"px");
		}

		if (c + speed > lineArr.length) {
			if (i == lineArr.length - 1) {
				marker.setPosition(data);
			}
		}
	});
	if (speed > 1) {
		c += speed;
	} else {
		c++;
	}
	if (c < lineArr.length) {
		var args = Array.prototype.slice.call(arguments);
		var self = arguments.callee;
		stopTimeout = window.setTimeout(function() {
			//--------------------------------------------------
			// debugger;
			// -------------------------------------------------- 
			self.apply(null, args)
		}, normal/speed);
	} else {
		c = 0;
		currentStatus = 0;
	}
}


function getAngle(pt1, pt2){  
	return Math.atan2(pt2.lat - pt1.lat, pt2.lng - pt1.lng);  
}

function createIcon(angle) {  
	//从负Y轴方向开始顺时针查找角度  
	var adjAngles = [180,202,225,247,270,292,315,337,0,22,45,67,90,112,135,157];  
	adjAngle = angle;  

	var adjIndex = 0;  
	for (var i = 0; i < 16; i++){  
		if (adjAngle < (- 15 / 16  + i / 8 ) *Math.PI) {  
			adjIndex = i;  
			break;  
		}
	}

	icon = new AMap.Icon({image:"/resources/images/arrow/arrow_" + adjAngles[adjIndex] + ".png", imageSize:new AMap.Size(22,22)});  
	return icon;  
} 

function track(IMEI){

	var time= new Date().getTime();
	if (time-lastTrackTime<5000){
		return 0;
	}

	isplay=0;
	lastTrackTime=time;
	TrackPos=0;
	lineArr.length=0;
	timeArr.length=0;
	for (var i=0;i<720;i++) {
		$("#idtrackTab tr td:nth-child("+i+")").css("background-color","#EEE");
	}
	var TrackDate=$('#idTrackDate').val();

	//server.device.get(IMEI).then(function(results){
	//   console.log(results);
	//});
	$.post("ajs.php",{act:9041,IMEI:IMEI,TrackDate:TrackDate},
		function(s){
			map.clearMap();
			map.clearInfoWindow();
			var str=s.split(String.fromCharCode(1));
			//server.device.add({'IMEI':IMEI, 'data':str, 'date':TrackDate});
			$("#idTrackPos").css("left","-10px"); 
			if (str[1] == "ok"){

				timeArr=str[2].split(',');
				tmpArr=str[3].split(',');
				//AddrTimes=str[4].split(String.fromCharCode(2));
				if (timeArr.length==0){
					lastTrackTime=0;
					alert("当前无轨迹数据.");
					return 0;
				}

				$(eval(str[5])).each(function(i,data){
					lineArr.push([data['Lon'], data['Lat']]);
					AddrTimes.push(data['Time']);
					AddrTimes.push(data['Addr']);
					$("#idtrackTab tr td:nth-child("+i+")").css("background-color","BLUE");
					//timeArr.push(data['Time']);
				});

				var _end_position = eval(str[5])[lineArr.length-1];
				var _start_postion = eval(str[5])[0];
				marker = new AMap.Marker({
					map: map,
					position: [_end_position['Lon'], _end_position['Lat']],
					icon: "/resources/images/endpoint.png",
					offset: new AMap.Pixel(-10, -32),
					autoRotation: true
				});	
				marker = new AMap.Marker({
					map: map,
					position: [tmpArr[0],tmpArr[1]],
					icon: "/resources/images/startpoint.png",
					offset: new AMap.Pixel(-10, -32),
					autoRotation: true
				});	
				marker = new AMap.Marker({
					map: map,
					position: [tmpArr[0],tmpArr[1]],
					icon: "/resources/images/pointB.png",
					offset: new AMap.Pixel(-100, -100),
					autoRotation: true
				});
				window.setTimeout(function(){marker.setOffset(new AMap.Pixel(-31, -31));marker.setIcon("/resources/images/point.png");},1000);	
				TrackPos=timeArr[0];	
				//--------------------------------------------------
				// $("#idTrackPos").css("left",(timeArr[0]-10)+"px");
				// -------------------------------------------------- 
				$("#idTrackPos").css("left","-10px");
				var polyline = new AMap.Polyline({
					map: map,
						path: lineArr,
						strokeColor: "#0020C2",  //线颜色
						strokeOpacity: 1,     //线透明度
						strokeWeight: 5,      //线宽
						strokeStyle: "solid"  //线样式
				});

				var _pointArrs = lineArr;

				for (var i=0; i < _pointArrs.length; i++) {
					if ( _pointArrs[i+1] != undefined) {
						var angle = getAngle(_pointArrs[i], _pointArrs[i+1]);
					}
					var iconImg = createIcon(angle);
					var _marker = new AMap.Marker({
						position: _pointArrs[i],
						icon: iconImg,
						offset: new AMap.Pixel(-5, -9),
						autoRotation:true
					});
					_marker.setMap(map);

					// bind event
					var _click = function(e) {
						var z=500-10*(map.getZoom()-11);
						var idx=-1;
						var best=-1;
						var cur=-1;
						for (var i=0;i<lineArr.length-1;i++){
							cur=e.lnglat.distance(lineArr[i]);
							//if ((cur<best || best==-1) && cur<z){
							if ((cur<best || best==-1) ){

								idx=i;
								best=cur;
							}
						}

						if (idx < 0) return;

						map.clearInfoWindow();
						var infoWindow = new AMap.InfoWindow({
							isCustom: true,  //使用自定义窗体
								autoMove: true,
								position: _pointArrs[i],
								content: trackinfo(idx),
								offset: new AMap.Pixel(16,-45)
						});
						infoWindow.open(map,e.target.getPosition());

					}
					AMap.event.addListener(_marker, 'click', _click);
				}

				//--------------------------------------------------
				// debugger;
				// -------------------------------------------------- 
				for (var i=0;i<720;i++)arrPosArr[i]=-1;
				for (var i=0;i<timeArr.length;i++){
					//$("#idtrackTab tr td:nth-child("+timeArr[i]+")").css("background-color","BLUE");
					arrPosArr[timeArr[i]]=i;
				}
				map.setCenter([tmpArr[0],tmpArr[1]]);

				//map.on('click', function(e) {
				//    var z=500-10*(map.getZoom()-11);
				//    var idx=-1;
				//    var best=-1;
				//    var cur=-1;
				//    for (var i=0;i<lineArr.length-1;i++){
				//        cur=e.lnglat.distance([lineArr[i],lineArr[i+1]]);
				//        if ((cur<best || best==-1) && cur<z){

				//            idx=i;
				//            best=cur;
				//        }
				//    }
				//    if (idx>-1)trackinfo(idx);
				//});
			}else alert(s);
			lastTrackTime=new Date().getTime()-4500;

		});

}	

function getMarkerInfo(time, address) {
	map.clearInfoWindow();

	var info = document.createElement("div");
	info.className = "info";

	// 定义顶部标题
	var top = document.createElement("div");
	var titleD = document.createElement("div");
	var closeX = document.createElement("img");
	top.className = "info-top";
	titleD.innerHTML = '时间:'+time;
	closeX.src = "http://webapi.amap.com/images/close2.gif";
	closeX.onclick =function (){map.clearInfoWindow();};

	top.appendChild(titleD);
	top.appendChild(closeX);
	info.appendChild(top);

	// 定义中部内容
	var middle = document.createElement("div");
	middle.className = "info-middle";
	middle.style.backgroundColor = 'white';
	middle.innerHTML = '<div class="inDiv" ><label for="name">地址: </label> <label for="name"> '+address+'</label></div>';
	info.appendChild(middle);

	// 定义底部内容
	var bottom = document.createElement("div");
	bottom.className = "info-bottom";
	bottom.style.position = 'relative';
	bottom.style.top = '0px';
	bottom.style.margin = '0 auto';
	var sharp = document.createElement("img");
	sharp.src = "http://webapi.amap.com/images/sharp.png";
	bottom.appendChild(sharp);
	info.appendChild(bottom);

	return info;
}

function trackinfo(idx) {
	
		map.clearInfoWindow();
    
		var info = document.createElement("div");
        info.className = "info";
		 
        //可以通过下面的方式修改自定义窗体的宽高
        //info.style.width = "400px";
        // 定义顶部标题
        var top = document.createElement("div");
        var titleD = document.createElement("div");
        var closeX = document.createElement("img");
        top.className = "info-top";
        titleD.innerHTML = '时间:'+AddrTimes[idx*2];
        closeX.src = "http://webapi.amap.com/images/close2.gif";
        closeX.onclick =function (){map.clearInfoWindow();};

        top.appendChild(titleD);
        top.appendChild(closeX);
        info.appendChild(top);

        // 定义中部内容
        var middle = document.createElement("div");
        middle.className = "info-middle";
        middle.style.backgroundColor = 'white';
        middle.innerHTML = 
		'<div class="inDiv" ><label for="name">地址: </label> <label for="name"> '+AddrTimes[idx*2+1]+'</label></div>';
        info.appendChild(middle);

        // 定义底部内容
        var bottom = document.createElement("div");
        bottom.className = "info-bottom";
        bottom.style.position = 'relative';
        bottom.style.top = '0px';
        bottom.style.margin = '0 auto';
        var sharp = document.createElement("img");
        sharp.src = "http://webapi.amap.com/images/sharp.png";
        bottom.appendChild(sharp);
        info.appendChild(bottom);
        //可以通过下面的方式修改自定义窗体的宽高
        //info.style.width = "400px";
        // 定义顶部标题
       
       
		var infoWindow = new AMap.InfoWindow({
			isCustom: true,  //使用自定义窗体
			content: info,
			offset: new AMap.Pixel(16,-28)
			});
		
        
		  infoWindow.open(map,lineArr[idx]);
		 // map.setCenter([Lat,Lon]);
        return info;
    }
 
function t(p){
	if (marker){
		TrackPos=p;
		$("#idTrackPos").css("left",(p-10)+"px");
		if (arrPosArr[p]>=0){
			marker.setPosition(lineArr[arrPosArr[p]]);
			
		}
		if (isplay==0)	trackinfo(arrPosArr[p]); 	
	}

	
}
function savesafeArea(){
	var City=$('#idSafeAreaCity').val();
	var Name=$('#idSafeAreaName').val();
	var Lng=0;
	var Lat=0;
	var Radius=0;
	if (safeAreaType==0){
		Radius=mapEditor._circle.getRadius();
		Lng=mapEditor._circle.getCenter().getLng();
		Lat=mapEditor._circle.getCenter().getLat();
		
	}
	$.post("ajs.php",{act:9018,IMEI:defaultIMEI,Type:safeAreaType,Name:Name,City:City,Radius:Radius,Lng:Lng,Lat:Lat},
		function(s){
			alert(s);
			if (s.indexOf("成功")>2)$('#idSafeArea').css('display','none');
		}
		);
	
}
function editSafeArea(IMEI, lon, lat){
	defaultIMEI=IMEI;

	$("#idSafeAreaName").val(defaultIMEI);
	
	$('#idSafeArea').css('display','');
	safeMap.clearMap();
	
	AMap.service('AMap.DistrictSearch',function(){//回调函数
		//实例化DistrictSearch
		districtSearch = new AMap.DistrictSearch();
		//TODO: 使用districtSearch对象调用行政区查询的功能
	})
	
	$.post("ajs.php",{act:9017,IMEI:IMEI},
		function(s){
			
			var str=s.split(String.fromCharCode(1));
			if (str[2] != '') {
				$('#idSafeAreaName').val(str[2]);
			}
			
			if (str[1]!='1'){
				
				$('#idSafeAreaCity').css('display','none');
				
				$('#idSafeType_0').prop('checked','checked');
				safeAreaType=0;
				//attr('checked','checked');
				mapEditor._circle=(function(){
				var circle = new AMap.Circle({
					center: [str[4],str[5]],// 圆心位置
					radius: str[3], //半径
					strokeColor: "#F33", //线颜色
					strokeOpacity: 1, //线透明度
					strokeWeight: 3, //线粗细度
					fillColor: "#ee2200", //填充颜色
					fillOpacity: 0.35//填充透明度
				});
				circle.setMap(safeMap);

				var _locate = [];
				var lnglat = new AMap.LngLat(lon, lat)
				var marker = new AMap.Marker({
					icon: "/resources/images/endpoint.png",
				});
				marker.setPosition(lnglat);
				marker.setMap(safeMap);

				//safeMap.setCenter([lon, lat]);
				safeMap.setFitView();
				
				return circle;
				})();
				
				//
				mapEditor._circleEditor= new AMap.CircleEditor(safeMap, mapEditor._circle);
				mapEditor._circleEditor.open();
				 
			} else{
				
				//AMap.service('AMap.DistrictSearch',function(){//回调函数
					//实例化DistrictSearch
					//districtSearch = new AMap.DistrictSearch();
					//TODO: 使用districtSearch对象调用行政区查询的功能
				//});
				safeAreaType=1;
				$('#idSafeAreaCity').val(str[3]);
				$('#idSafeAreaCity').css('display','');
				$('#idSafeType_1').prop('checked','checked');
				
				//$('#idSafeType_1').click();//attr('checked','checked');				
				safeMap.on('complete', function() {
					if (str[3]!="") {
						safeMap.setCity(str[3]);
				var districtSearch = new AMap.DistrictSearch({extensions:"all"});
				districtSearch.setLevel('city');
				//districtSearch.setSubDistrict(0);
				districtSearch.search(str[3],function(status, result){
					var bounds = result.districtList[0].boundaries;
					var polygon = new AMap.Polygon({
						map: safeMap,
							strokeWeight: 1,
							path: bounds,
							fillOpacity: 0.4,
							fillColor: '#CCF3FF',
							strokeColor: '#CC66CC'
					});
					//safeMap.setFitView();
				});

					}
					
				});
				
				safeAreaType=1;
			
			
			
		}
	});
	
	safeMap.on('click', function(e) {
		if (safeAreaType==0){
			safeMap.clearMap();
			mapEditor._circle=(function(){
			var circle = new AMap.Circle({
				center: e.lnglat,// 圆心位置
				radius: 5000, //半径
				strokeColor: "#F33", //线颜色
				strokeOpacity: 0, //线透明度
				strokeWeight: 4, //线粗细度
				fillColor: "#ee2200", //填充颜色
				fillOpacity: 0.35//填充透明度
			});
			circle.setMap(safeMap);
			
			return circle;
			})();
			
			//window.setTimeout(function (){},500);
			mapEditor._circleEditor= new AMap.CircleEditor(safeMap, mapEditor._circle);
			mapEditor._circleEditor.open();
			
		}	
		var geocoder = new AMap.Geocoder({
		radius: 1000,
		extensions: "all"
			}); 
			
		 geocoder.getAddress([e.lnglat.getLng(),e.lnglat.getLat()],function(status, result) {
		 //alert(status);
		if (status === 'complete' && result.info === 'OK') {
			if (result.regeocode.addressComponent.city!="")$('#idSafeAreaCity').val(result.regeocode.addressComponent.city);
			else if(result.regeocode.addressComponent.province!="") $('#idSafeAreaCity').val(result.regeocode.addressComponent.province);
		}
        });    
		
		
		
		
		});
	safeMap.on('moveend', function(e) {
		//if (safeAreaType==1){
		
			safeMap.getCity(function (data){
			if (data['province'] && typeof data['province'] === 'string') 
				$('#idSafeAreaCity').val(data['city']);
			});	
		//}
			if (map) {
				map.clearInfoWindow();
			}
		
		
		});

} 
//日历控件
function HS_DateAdd(interval,number,date){
    number = parseInt(number);
    if (typeof(date)=="string"){var date = new Date(date.split("-")[0],date.split("-")[1],date.split("-")[2])}
    if (typeof(date)=="object"){var date = date}
    switch(interval){
        case "y":return new Date(date.getFullYear()+number,date.getMonth(),date.getDate()); break;
        case "m":return new Date(date.getFullYear(),date.getMonth()+number,HS_checkDate(date.getFullYear(),date.getMonth()+number,date.getDate())); break;
        case "d":return new Date(date.getFullYear(),date.getMonth(),date.getDate()+number); break;
        case "w":return new Date(date.getFullYear(),date.getMonth(),7*number+date.getDate()); break;
    }
}

function HS_checkDate(year,month,date){
    var enddate = ["31","28","31","30","31","30","31","31","30","31","30","31"];
    var returnDate = "";
    if (year%4==0){enddate[1]="29"}
    if (date>enddate[month]){returnDate = enddate[month]}else{returnDate = date}
    return returnDate;
}

function HS_WeekDay(date){
    var theDate;
    if (typeof(date)=="string"){theDate = new Date(date.split("-")[0],date.split("-")[1],date.split("-")[2]);}
    if (typeof(date)=="object"){theDate = date}
    return theDate.getDay();
}

function HS_calender(){
    var lis = "";
    var style = "";
    /*可以把下面的css剪切出去独立一个css文件*/
    style +="<style type='text/css'>";
    style +=".calender { width:170px; height:160px; font-size:12px; margin-right:14px; background:url(calenderbg.gif) no-repeat right center #fff; border:1px solid #397EAE; padding:1px}";
    style +=".calender ul {list-style-type:none; margin:0; padding:0;}";
    style +=".calender .day { background-color:#EDF5FF; height:20px;}";
    style +=".calender .day li,.calender .date li{ float:left; width:14%; height:20px; line-height:20px; text-align:center}";
    style +=".calender li a { text-decoration:none; font-family:Tahoma; font-size:11px; color:#333}";
    style +=".calender li a:hover { color:#f30; text-decoration:underline}";
    style +=".calender li a.hasArticle {font-weight:bold; color:#f60 !important}";
    style +=".lastMonthDate, .nextMonthDate {color:#bbb;font-size:11px}";
    style +=".selectThisYear a, .selectThisMonth a{text-decoration:none; margin:0 2px; color:#000; font-weight:bold}";
    style +=".calender .LastMonth, .calender .NextMonth{ text-decoration:none; color:#000; font-size:18px; font-weight:bold; line-height:16px;}";
    style +=".calender .LastMonth { float:left;}";
    style +=".calender .NextMonth { float:right;}";
    style +=".calenderBody {clear:both}";
    style +=".calenderTitle {text-align:center;height:20px; line-height:20px; clear:both}";
    style +=".today { background-color:#ffffaa;border:1px solid #f60; padding:2px}";
    style +=".today a { color:#f30; }";
    style +=".calenderBottom {clear:both; border-top:1px solid #ddd; padding: 3px 0; text-align:left}";
    style +=".calenderBottom a {text-decoration:none; margin:2px !important; font-weight:bold; color:#000}";
    style +=".calenderBottom a.closeCalender{float:right}";
    style +=".closeCalenderBox {float:right; border:1px solid #000; background:#fff; font-size:9px; width:11px; height:11px; line-height:11px; text-align:center;overflow:hidden; font-weight:normal !important}";
    style +="</style>";
    var now;
    if (typeof(arguments[0])=="string"){
            selectDate = arguments[0].split("-");
            var year = selectDate[0];
            var month = parseInt(selectDate[1])-1+"";
            var date = selectDate[2];
            now = new Date(year,month,date);
        }
        else if (typeof(arguments[0])=="object"){
            now = arguments[0];
    }
    var lastMonthEndDate = HS_DateAdd("d","-1",now.getFullYear()+"-"+now.getMonth()+"-01").getDate();
    var lastMonthDate = HS_WeekDay(now.getFullYear()+"-"+now.getMonth()+"-01");
    var thisMonthLastDate = HS_DateAdd("d","-1",now.getFullYear()+"-"+(parseInt(now.getMonth())+1).toString()+"-01");
    var thisMonthEndDate = thisMonthLastDate.getDate();
    var thisMonthEndDay = thisMonthLastDate.getDay();
    var todayObj = new Date();
    today = todayObj.getFullYear()+"-"+todayObj.getMonth()+"-"+todayObj.getDate();

    for (i=0; i<lastMonthDate; i++){ // Last Month's Date
        lis = "<li class='lastMonthDate'>"+lastMonthEndDate+"</li>" + lis;
        lastMonthEndDate--;
    }
    for (i=1; i<=thisMonthEndDate; i++){ // Current Month's Date
    if(today == now.getFullYear()+"-"+now.getMonth()+"-"+i){
         var todayString = now.getFullYear()+"-"+(parseInt(now.getMonth())+1).toString()+"-"+i;
         lis += "<li><a href=javascript:void(0) class='today' onclick='_selectThisDay(this)' title='"+now.getFullYear()+"-"+(parseInt(now.getMonth())+1)+"-"+i+"'>"+i+"</a></li>";
    }
    else{
         lis += "<li><a href=javascript:void(0) onclick='_selectThisDay(this)' title='"+now.getFullYear()+"-"+(parseInt(now.getMonth())+1)+"-"+i+"'>"+i+"</a></li>";
    }

    }
    var j=1;
    for (i=thisMonthEndDay; i<6; i++){ // Next Month's Date
        lis += "<li class='nextMonthDate'>"+j+"</li>";
        j++;
    }
    lis += style;
    var CalenderTitle = "<a href='javascript:void(0)' class='NextMonth' onclick=HS_calender(HS_DateAdd('m',1,'"+now.getFullYear()+"-"+now.getMonth()+"-"+now.getDate()+"'),this) title='Next Month'>&raquo;</a>";
    CalenderTitle += "<a href='javascript:void(0)' class='LastMonth' onclick=HS_calender(HS_DateAdd('m',-1,'"+now.getFullYear()+"-"+now.getMonth()+"-"+now.getDate()+"'),this) title='Previous Month'>&laquo;</a>";
    CalenderTitle += "<span class='selectThisYear'><a href='javascript:void(0)' onclick='CalenderselectYear(this)' title='Click here to select other year' >"+now.getFullYear()+"</a></span>年<span class='selectThisMonth'><a href='javascript:void(0)' onclick='CalenderselectMonth(this)' title='Click here to select other month'>"+(parseInt(now.getMonth())+1).toString()+"</a></span>月";
    if (arguments.length>1){
        arguments[1].parentNode.parentNode.getElementsByTagName("ul")[1].innerHTML = lis;
        arguments[1].parentNode.innerHTML = CalenderTitle;
    }else{
        var CalenderBox = style+"<div class='calender'><div class='calenderTitle'>"+CalenderTitle+"</div><div class='calenderBody'><ul class='day'><li>日</li><li>一</li><li>二</li><li>三</li><li>四</li><li>五</li><li>六</li></ul><ul class='date' id='thisMonthDate'>"+lis+"</ul></div><div class='calenderBottom'><a href='javascript:void(0)' class='closeCalender' onclick='closeCalender(this)'>&times;</a><span><span><a href=javascript:void(0) onclick='_selectThisDay(this)' title='"+todayString+"'>今天</a></span></span></div></div>";
        return CalenderBox;
    }
}

function _selectThisDay(d){
    var boxObj = d.parentNode.parentNode.parentNode.parentNode.parentNode;
    boxObj.targetObj.value = d.title;
    boxObj.parentNode.removeChild(boxObj);
}

function closeCalender(d){
    var boxObj = d.parentNode.parentNode.parentNode;
    boxObj.parentNode.removeChild(boxObj);
}

function CalenderselectYear(obj){
    var opt = "";
    var thisYear = obj.innerHTML;
    for (i=1970; i<=2020; i++){
        if (i==thisYear){
            opt += "<option value="+i+" selected>"+i+"</option>";
        }else{
            opt += "<option value="+i+">"+i+"</option>";
        }
    }
    opt = "<select onblur='selectThisYear(this)' onchange='selectThisYear(this)' style='font-size:11px'>"+opt+"</select>";
    obj.parentNode.innerHTML = opt;
}

function selectThisYear(obj){
    HS_calender(obj.value+"-"+obj.parentNode.parentNode.getElementsByTagName("span")[1].getElementsByTagName("a")[0].innerHTML+"-1",obj.parentNode);
}

function CalenderselectMonth(obj){
    var opt = "";
    var thisMonth = obj.innerHTML;
    for (i=1; i<=12; i++){
        if (i==thisMonth){
            opt += "<option value="+i+" selected>"+i+"</option>";
        }else{
            opt += "<option value="+i+">"+i+"</option>";
        }
    }
    opt = "<select onblur='selectThisMonth(this)' onchange='selectThisMonth(this)' style='font-size:11px'>"+opt+"</select>";
    obj.parentNode.innerHTML = opt;
}

function selectThisMonth(obj){
    HS_calender(obj.parentNode.parentNode.getElementsByTagName("span")[0].getElementsByTagName("a")[0].innerHTML+"-"+obj.value+"-1",obj.parentNode);
}

function HS_setDate(inputObj){
    var calenderObj = document.createElement("span");
    calenderObj.innerHTML = HS_calender(new Date());
    calenderObj.style.position = "absolute";
     calenderObj.style.top = "148px";
     calenderObj.style.left = "72px";
    calenderObj.targetObj = inputObj;
    inputObj.parentNode.insertBefore(calenderObj,inputObj.nextSibling);
}
function setPage(idx){
	$("#idPagePos").val(pagePos[idx]);
	
	$("#idPageTotal").html(pageTotal[idx]);
	if (pagePos[idx]<2) $("#idPageFirst").css("background-image","url(resources/images/lastpageoff.png)");
	else  $("#idPageFirst").css("background-image","url(resources/images/lastpageon.png)");
	if (pagePos[idx]<pageTotal[idx]) $("#idPageNext").css("background-image","url(resources/images/nextpageon.png)");
	else  $("#idPageNext").css("background-image","url(resources/images/nextpageoff.png)");

}
function gotoPage(p,pp){
	if (pp==-1) pp=curPanel;

	if (p>0 && pagePos[pp]!=p && (p<=pageTotal[pp] || (pageTotal[pp]==0 && p==1))){
		var k='';
		if (pp==3) k=$('#idKey_2').val()
		else k=$('#idKey_1').val();	
		$.post("ajs.php",{act:9001,panel:pp,page:p,key:k},
		function(s){$("#idPanel_"+pp).html(s);pagePos[pp]=p;setPage(pp);});
	}
}
function search_1()
{
	var k=$('#idKey_1').val();	
	$.post("ajs.php",{act:9004,panel:curPanel,page:1,key:k},
	function(s){var p=s.indexOf(',');
	pageTotal[0]=s.substring(0,p);pageTotal[1]=s.substring(p+1);
	pageTotal[2]=pageTotal[0]-pageTotal[1];
	$("#idTotal_0").html('全部('+pageTotal[0]+')');
	$("#idTotal_1").html('在线('+pageTotal[1]+')');
	$("#idTotal_2").html('离线('+pageTotal[2]+')');
	pageTotal[0]=Math.ceil(pageTotal[0]/10);
	pageTotal[1]=Math.ceil(pageTotal[1]/10);
	pageTotal[2]=Math.ceil(pageTotal[2]/10);
	pagePos[0]=-1;pagePos[1]=-1;pagePos[2]=-1;
	gotoPage(1,0);
	gotoPage(1,1);
	gotoPage(1,2);
	});	
}
function search_2()
{
	var k=$('#idKey_2').val();	
	$.post("ajs.php",{act:9005,panel:3,page:1,key:k},
	function(s){var p=s.indexOf(',');
	pageTotal[3]=s;
	pageTotal[3]=Math.ceil(pageTotal[3]/10);
	pagePos[3]=-1;
	gotoPage(1,3);});	
}
function serachTerm(){
	var k=$('#idTermKey').val();
	var ID=$('#idSelectGroup').val();
	
	$.post("ajs.php",{act:9003,page:1,Key:k,Group:ID},
	function(s){;$("#idTermList").html(s);termPage=1;});
	
}
function termGotoPage(p){
	var k=$('#idTermKey').val();
	var ID=$('#idSelectGroup').val();
	if (p <= 1) {
		$.post("ajs.php",{act:9003,page:1,Key:k,Group:ID}, function(s){
			$("#idTermList").html(s);
		});
	} else {
		$.post("ajs.php",{act:9003,page:p,Key:k,Group:ID}, function(s){
			$("#idTermList").html(s);
			if ($("#idTermList table").find("tr").length <= 1) {
				if (p-1 < 0) {
					termGotoPage(1);
					termPage=1;
				} else {
					termGotoPage(p-1);
					termPage=p;
				}
			}
		});
	}
	//$.post("ajs.php",{act:9003,page:p,Key:k,Group:ID}, function(s){
	//    $("#idTermList").html(s);
	//    if ($("#idTermList table").find("tr").length <= 1) {
	//        if (p-1 < 0) {
	//            termGotoPage(1);
	//            termPage=1;
	//        } else {
	//            termGotoPage(p-1);
	//            termPage=p;
	//        }
	//    }
	//});
	
}
function importTerm(){
	$('#idTermGroupImpt').html($("#idSelectGroup").html());
	$("#idTermGroupImpt option[value='0']").remove();
	
	
	
	if ($('#idSelectGroup').val()<1)$('#idTermGroupImpt option:first').attr('selected','selected');
	else $('#idTermGroupImpt').val($('#idSelectGroup').val()); 
	$('#idImporttxt').val('');
	$('#idTermImport').css('display','');
	$("#idImporttxt").focus();
	
}
function saveImportTerm(){
	var Txt=$('#idImporttxt').val();
	var GroupID=$('#idTermGroupImpt').val();
	var _terms = Txt.split("\n");
	var postDatas = [];
	for (var i=0; i < _terms.length; i++) {
		var _tmps = _terms[i].split(",");
		if (_tmps.length >= 6) {
		}

		if (!checkImei(_tmps[0])) {
			Alert('IMEI 不正确', false);
			return;
		}

		//if (_tmps[1] != "" && !checkPlate(_tmps[1])) {
			//Alert('车牌号码不正确', false);
			//return;
		//}

		//if (_tmps[2] != "" && !checkPhone(_tmps[2])) {
			//Alert('设置号码不正确', false);
			//return;
		//}

		//if (_tmps[3].trim() != '' && !checkPhone(_tmps[3])) {
			//Alert('监听号码不正确', false);
			//return;
		//}

		//if (_tmps.length > 4) {
			//if (_tmps[4].trim() != '' && !checkPhone(_tmps[4])) {
				//Alert('监听号码不正确', false);
				//return;
			//}

			//if (_tmps[5].trim() != '' && !checkPhone(_tmps[5])) {
				//Alert('监听号码不正确', false);
				//return;
			//}
		//}

		var datas = [];
		for (var j=0; j < _tmps.length; j++) {
			if (j < 6) {
				datas.push(_tmps[j]);
			}
		}
		postDatas.push(datas.join(","));
	}



	$.post("ajs.php",{act:9015,Txt:postDatas.join("\n"),GroupID:GroupID}, function(s){
		$('#idTermImport').css('display','none');
		termGotoPage(termPage);
		var bak=curPanel;
		curPanel=0;
		search_1();
		curPanel=bak;
		search_2();
		alert(s);
	});
}
function delSelTerm(){
var str='';
	$("[name='termIds']:checkbox:checked").each(function(){   
	str+=$(this).val()+",";});
	if (str==""){
		alert("请勾选要删除的设备.");
		return 0;
	}
	if (confirm('您确认要删除选中的设备吗？')){
		$.post("ajs.php",{act:9019,IMEI:str},
		function(s){termGotoPage(termPage);var bak=curPanel;curPanel=0;search_1();curPanel=bak;search_2();alert(s);});
	}
   	
}
function newTerm(){
	editTermStatus=0;
	TermAlarmStat=1;
	TermLoopType=0;
	
	setLoopType(0);
	
	$("#idTermPhonePanel").css("display","");
	$("#idTermIMEIPanel").css("display","");
	$("#idTermNumPanel").css("display","");
	
	$("#idTermGroup").removeProp('disabled');
	
	$('#idTermGroup').html($("#idSelectGroup").html());
	$("#idTermGroup option[value='0']").remove();
	//$("#idTermGroup").prepend("<option value='0'>默认分组</option>");
	$('#idTermGroup option:first').attr('selected','selected');
	
	$('#idTermPhone').val('');
	$('#idTermBook').val('');
	$('#idPreLocate').val('1');
	$('#idLocIntval').val('60');
	
	$('#idLoopValue').val('1');
	$('#idLoopType_0').prop('checked','checked');
	$('#idAlarmStat_0').prop('checked','checked');
	$('#idReportIntval').val('5');
	$('#idSleepIntval').val('20');
	$('#idTermIMEI').val('');
	$('#idTermNum').val('');
	$('#idTermIMEI').css('background','#FFF');
	
	$('#idTermIMEI').removeProp("disabled");
	
	
	$('#idTermEdit').css('display','');
	
	
	$("#idTermIMEI").focus();
	
}
function saveEditTerm(){
	var IMEI=$('#idTermIMEI').val();
	var Num=$('#idTermNum').val();
	var IMEI=$('#idTermIMEI').val();
	var Num=$('#idTermNum').val();
	var Phone=$('#idTermPhone').val();
	var Book=$('#idTermBook').val();
	var PreLocate=$('#idPreLocate').val();
	var LoopValue=$('#idLoopValue').val();
	var LocIntval=$('#idLocIntval').val();
	var ReportIntval=$('#idReportIntval').val();
	var SleepIntval=$('#idSleepIntval').val();
	if (IMEI==""){
		alert("请输入设备编号.");
		$('#idTermIMEI').focus();
		return 0;
	}
	if (Num != "" && !checkPlate(Num)) {
		//Alert("车牌号码不正确", false);
		//return;
	}

	if (Phone != "" && !checkPhone(Phone)) {
		//Alert("设备号码不正确", false);
		//return;
	}

	var books = Book.split(",");
	for (var i=0; i < books.length; i++) {
		if (books[i] == "") {
			continue;
		}

		if (!checkPhone(books[i])) {
			//Alert("监听号码不正确", false);
			//return;
		}
	}


	if (TermLoopType==0) LoopValue=0;
	if (TermLoopType==1 && (LoopValue<1 || LoopValue>31)){
		alert("周期定位数值错误.请输入有效的周期定位数值.");
		$('#idLoopValue').focus();
		return 0;
	}
	if (TermLoopType==2 && (LoopValue<1 || LoopValue>7)){
		alert("周期定位数值错误.请输入有效的周期定位数值.");
		$('#idLoopValue').focus();
		return 0;
	}
	if (TermLoopType==3){
		
		if (LoopValue >23){
			alert("周期定位数值错误.请输入有效的周期定位数值.");
		$('#idLoopValue').focus();
		return 0;
		}
		tt=$('#idLoopValue_2').val();
		if (tt >59){
			alert("周期定位数值错误.请输入有效的周期定位数值.");
		$('#idLoopValue').focus();
		return 0;
		}
		if (tt=="") tt="00";
		if (LoopValue=="") LoopValue="00";
		LoopValue=LoopValue+':'+tt;
	}

	var TermGroupID=$('#idTermGroup').val();	
		
	$("#idTermEdit").showLoading();
	$.post("ajs.php",{act:9014,IMEI:IMEI,Num:Num,Phone:Phone,Book:Book,PreLocate:PreLocate,LoopType:TermLoopType,LoopValue:LoopValue,LocIntval:LocIntval,ReportIntval:ReportIntval,SleepIntval:SleepIntval,AlarmStat:TermAlarmStat,GroupID:TermGroupID},
	function(s){if (s.indexOf("成功")>2){
		$('#idTermEdit').css('display','none');
		$("#idTermEdit").hideLoading();
		if (TermGroupID && false)$("#idSelectGroup").val(TermGroupID);
			serachTerm();
		
		}
		alert(s);
	});
}// termGotoPage(termPage);var bak=curPanel;curPanel=0;search_1();curPanel=bak;search_2();
function saveNewTerm(){
	var IMEI=$('#idTermIMEI').val();
 
	var Num=$('#idTermNum').val();
	var Phone=$('#idTermPhone').val();
	var Book=$('#idTermBook').val();
	var PreLocate=$('#idPreLocate').val();
	var LoopValue=$('#idLoopValue').val();
	var LocIntval=$('#idLocIntval').val();
	var ReportIntval=$('#idReportIntval').val();
	var SleepIntval=$('#idSleepIntval').val();
	
	var TermGroupID=$('#idTermGroup').val();
	if (IMEI==""){
		alert("请输入设备编号.");
		$('#idTermIMEI').focus();
		return 0;
	}

	if (Num != "" && !checkPlate(Num)) {
		//Alert("车牌号码不正确", false);
		//return;
	}

	if (Phone != "" && !checkPhone(Phone)) {
		//Alert("设备号码不正确", false);
		//return;
	}

	var books = Book.split(",");
	for (var i=0; i < books.length; i++) {
		if (books[i] == "") {
			continue;
		}

		if (!checkPhone(books[i])) {
			//Alert("监听号码不正确", false);
			//return;
		}
	}


	if (TermLoopType==0) LoopValue=0;
	if (TermLoopType==1 && (LoopValue<1 || LoopValue>31)){
		alert("周期定位数值错误.请输入有效的周期定位数值.");
		$('#idLoopValue').focus();
		return 0;
	}
	if (TermLoopType==2 && (LoopValue<1 || LoopValue>7)){
		alert("周期定位数值错误.请输入有效的周期定位数值.");
		$('#idLoopValue').focus();
		return 0;
	}
	if (TermLoopType==3){
		
		if (LoopValue >23){
			alert("周期定位数值错误.请输入有效的周期定位数值.");
			$('#idLoopValue').focus();
			return 0;
		}
		var tt=$('#idLoopValue_2').val();
		if (tt >59){
			alert("周期定位数值错误.请输入有效的周期定位数值.");
			$('#idLoopValue').focus();
			return 0;
		}
		if (tt=="") tt="0";
		if (LoopValue=="") LoopValue="0";
		if (tt<'10') tt="0"+tt;
		if (LoopValue<10)LoopValue='0'+LoopValue;
		LoopValue=LoopValue+':'+tt;
	}
	$("#idTermEdit").showLoading();
	$.post("ajs.php",{act:9012,IMEI:IMEI,Num:Num,Phone:Phone,Book:Book,PreLocate:PreLocate,LoopType:TermLoopType,LoopValue:LoopValue,LocIntval:LocIntval,ReportIntval:ReportIntval,SleepIntval:SleepIntval,AlarmStat:TermAlarmStat,GroupID:TermGroupID},function(s){
		if (s.indexOf("成功")>2){
			$('#idTermEdit').css('display','none');
			$("#idTermEdit").hideLoading();
		if (TermGroupID && false)$("#idSelectGroup").val(TermGroupID);
			serachTerm();
		}
		alert(s);});
}
function editTerm(IMEI,Num){
	$("#idTermPhonePanel").css("display","");
	$("#idTermIMEIPanel").css("display","");
	$("#idTermNumPanel").css("display","");
	
	$('#idTermGroup').html($("#idSelectGroup").html());
	$("#idTermGroup option[value='0']").remove();
	$('#idTermGroup option:first').attr('selected','selected');
//	$("#idTermGroup").prepend("<option value='0'>默认分组</option>");
	$("#idTermGroup").removeProp('disabled');
	$('#idTermGroup').val($('#idSelectGroup').val());
	
	$('#idTermPhone').val('');
	$('#idTermBook').val('');
	$('#idPreLocate').val('');
	$('#idLoopValue').val('');
	$('#idLocIntval').val('');
	
	$('#idLoopType_1').prop('checked','checked');
	$('#idAlarmStat_1').prop('checked','checked');
	$('#idReportIntval').val('');
	$('#idSleepIntval').val('');
	
	$.post("ajs.php",{act:9023,IMEI:IMEI},
		function(s){
			//alert(s);
			var str=s.split(String.fromCharCode(1));
			if (str.length>5){
				$('#idTermPhone').val(str[1]);
				$('#idPreLocate').val(str[2]);
				$('#idLocIntval').val(str[3]);
				$('#idLoopType_'+str[4]).prop('checked','checked');
				
				$('#idSleepIntval').val(str[6]);
				if (str[7] != '') {
					var _books = [];
					var _phones = str[7].split(",");
					for (var _phone=0; _phone < _phones.length; _phone++) {
						if (_phones[_phone] == "") { 
							continue;
						}
						_books.push(_phones[_phone]);
						if (_books.length > 3) {
							break;
						}
					}
					if (_books.length > 0) {
						$('#idTermBook').val(_books.join(","));
					} else {
						$('#idTermBook').val("");
					}
				}
				$('#idReportIntval').val(str[8]);
				$('#idAlarmStat_'+str[9]).prop('checked','checked');
				TermAlarmStat=str[9];
				if ($('#idTermGroup').val()<1) $('#idTermGroup').val(str[10]);
				TermLoopType=parseInt(str[4]);
				setLoopType(TermLoopType);
				if (TermLoopType==3){
					var temstr=str[5];
					var ipos=temstr.indexOf(":");
					if (ipos>0){
						$('#idLoopValue').val(temstr.substr(0,ipos));
						$('#idLoopValue_2').val(temstr.substr(ipos+1));
					}else {
						$('#idLoopValue').val("");
						$('#idLoopValue_2').val("");
					}	
				} else $('#idLoopValue').val(str[5]);
				
		
		
		}
	
		});	
	editTermStatus=1;
	$('#idTermIMEI').val(IMEI);
	$('#idTermIMEI').css('background','#EEE');
	$('#idTermIMEI').prop("disabled","disabled");
	$('#idTermNum').val(Num);
	$('#idTermEdit').css('display','');
	$("#idTermNum").focus();
}

function delIMEITerm(IMEI){
	if (confirm('您确认要删除设备吗？')){
		$.post("ajs.php",{act:9013,IMEI:IMEI}, function(s){
			termGotoPage(termPage);
			var bak=curPanel;
			curPanel=0;
			search_1();
			curPanel=bak;
			search_2();
			alert(s);
		});	
	} 
}
function userGotoPage(p){
	var k=$('#idUserKey').val();
	$.post("ajs.php",{act:9006,page:p,key:k}, function(s){
			$("#idUserList").html(s);
			userPage=p;
		});
	
}
function alarmGotoPage(p){
	var k=$('#idAlarmKey').val();
	$.post("ajs.php",{act:9016,page:p,key:k},
	function(s){$("#idAlarmList").html(s);});
	
}

function editUser(Acc,Name,Phone,Mail,Type){
	editUserStatus=1;
	$("#idPwdspan_1").css("display","none");
	$("#idPwdspan_2").css("display","");
	
	
	$('#idTermGroup').html($("#idSelectGroup").html());
	$("#idTermGroup option[value='0']").remove();
	
	$("#idTermGroup").prepend("<option value='0'>默认分组</option>");
	$('#idUserAcc').val(Acc);
	$('#idUserName').val(Name);
	$('#idUserPhone').val(Phone);
	$('#idUserMail').val(Mail);
	$('#idUserPwd').val('');
	$('#idUserType').val(Type);
	$('#idUserAcc').css('background','#EEE');
	$('#idUserAcc').prop("disabled","disabled");
	$('#idUserTypeDIV').css('display','none');
	$('#idUserEdit').css('display','');
	$("#idUserPwd").focus();
	//$.post("ajs.php",{act:9006,page:page},
	//function(s){$("#idUserEdit").html(s);});	
}
function newUser(){
	editUserStatus=0;
		$("#idPwdspan_1").css("display","");
	$("#idPwdspan_2").css("display","none");
	$('#idUserAcc').css('background','#FFF');
	$('#idUserAcc').removeProp("disabled");
	$('#idUserAcc').val('');
	$('#idUserName').val('');
	$('#idUserPwd').val('');
	$('#idUserPhone').val('');
	$('#idUserMail').val('');
	$('#idUserType').val(1);
	$('#idUserTypeDIV').css('display','');
	
	$('#idUserEdit').css('display','');
	$("#idUserAcc").focus();
	//$.post("ajs.php",{act:9006,page:page},
	//function(s){$("#idUserEdit").html(s);});	
}


function delSelUser(){
	var str='';
	$("[name='userIds']:checkbox:checked").each(function(){   
	str+=$(this).val()+",";});
	if (str==""){
		alert("请勾选要删除的用户.");
		return 0;
	}
	if (confirm('您确认要删除选中的用户吗？')){
		$.post("ajs.php",{act:9011,Phone:str},
		function(s){userGotoPage(userPage);alert(s);});
	}
   
}
function delUserName(Phone){
	if (confirm('您确认要删除选定用户吗？')){
	$.post("ajs.php",{act:9008,Phone:Phone},
	function(s){userGotoPage(userPage);alert(s);});	
	}
}
function saveNewUser(){
	var userName=$('#idUserName').val();
	var userPhone=$('#idUserPhone').val();
	var userType=$('#idUserType').val();
	var userAcc=$('#idUserAcc').val();
	var userPwd=$('#idUserPwd').val();
	var userMail=$('#idUserMail').val();
	if (userAcc==""){
		alert('请输入用户帐号.');
		return 0;
	}
	if ((userPwd.length<6 && editUserStatus==0) || (userPwd.length<6 && userPwd.length>0)){
		alert('请输入有效的登录密码，最少6位.');
		return 0;
	}
	if (userName==""){
		alert('请输入用户名称.');
		return 0;
	}
	if (userPhone==""){
		alert('请输入手机号码.');
		return 0;
	}

	if (!checkPhone(userPhone)) {
		alert('手机号码格式不正确.');
		return;
	}

	if (userMail==""){
		alert('请输入Email.');
		return 0;
	}

	if (!checkMail(userMail)) {
		alert('Email 格式不正确.');
		return;
	}
	
	if (editUserStatus==0){
		$.post("ajs.php",{act:9007,Name:userName,Phone:userPhone,Type:userType,Acc:userAcc,Pwd:userPwd,Mail:userMail},
		function(s){if (s.indexOf("成功")>2){userGotoPage(userPage);$('#idUserEdit').css('display','none');}alert(s);});
	}
		
	else {
		$.post("ajs.php",{act:9024,Name:userName,Phone:userPhone,Type:userType,Acc:userAcc,Pwd:userPwd,Mail:userMail}, function(s){
			if (s.indexOf("成功")>2){
				userGotoPage(userPage);
				$('#idUserEdit').css('display','none');
			}
			alert(s);
		});
	}	
	
}
function changeUserPermission(Phone){

	$.post("ajs.php",{act:9009,Phone:Phone}, function(s){
		if (s='转移权限成功！') {
			//$('#idWatchbu').click();
			termGotoPage(termPage);
			//$('#idSetupbu').empty();
			//$('#idUserPanel').empty();
			//$('#idSetupPanel').empty();
			//$('#idUserbu').empty();
			alert(s);
			$("#dialog-form").dialog("close");
			window.location.reload();
		} else {
			$('#idUserEdit').css('display','none');
			userGotoPage(userPage);alert(s);
		}
	});
}
function changeUserPer(phone) {

	var dialog, form;
	dialog = $( "#dialog-form" ).dialog({
      autoOpen: false,
      height: 200,
      width: 350,
      modal: true,
      buttons: {
		  "转移权限": function() {
			  var p = $("#speed").attr("selected", true).val();
			  changeUserPermission(p);
		  },
        "取消" : function() {
          dialog.dialog( "close" );
        }
      },
      close: function() {
        form[ 0 ].reset();
        //allFields.removeClass( "ui-state-error" );
      }
	});
	dialog.dialog("open");
 
    form = dialog.find( "form" ).on( "submit", function( event ) {
      event.preventDefault();
      changeUserPermission(phone);
    });

	$( "#speed" ).selectmenu();
    //$( "#create-user" ).button().on( "click", function() {
    //  dialog.dialog( "open" );
    //});

}
function serachUser(){
	
	var k=$('#idUserKey').val();
	$.post("ajs.php",{act:9006,page:1,key:k},
	function(s){$("#idUserList").html(s);UserPage=1;});
	
}
$(function(){
	$("#idUserKey").focus(function(event){
		$(this).keydown(function(event){
			var _ev = document.all ? window.event : event;
			if (_ev.keyCode == 13) {
				serachUser();
			}
		});
	});
});




$(function(){
	
	$("#idTrackPos").bind("click mousedown", function(e){
	if (event.type == 'click') {
	}else if(event.type == 'mousedown') {
	mFlag=true;
	divX =e.pageX - parseInt($("#idTrackPos").css("left"));
	}
	});
$(document).mousemove(function(e){
	if(mFlag){
		bakX=e.pageX-divX;
		$("#idTrackPos").stop();
		if (bakX>=0 && bakX<710){
		  $("#idTrackPos").css({left:bakX});
		if (arrPosArr[bakX+10]>=0)
			marker.setPosition(lineArr[arrPosArr[bakX+10]]);	  
		}
	}
	}).mouseup(function(){
	mFlag=false;
	});
	setPage(0);
});  



function isNumber1(evt,num1)    
{    
     var iKeyCode = window.event?evt.keyCode:evt.which;    
        if((iKeyCode>=48) && (iKeyCode<=57) || (iKeyCode>=96) && (iKeyCode<=105) || (iKeyCode>=37) && (iKeyCode<=40) || iKeyCode===8|| iKeyCode===9 || iKeyCode==46 || iKeyCode==16 || iKeyCode==num1)    
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
 	
</script>
