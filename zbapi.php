
<?php
  function logger($log_content)
     {
             $max_size = 1000000000;
             $log_filename = "log.xml";
             if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
             file_put_contents($log_filename, date('H:i:s')." ".$log_content."\r\n", FILE_APPEND);
         
     }
function errstr($e){
	switch (intval($e)){	
	case 400: return '请求失败，服务器内部错误!';
	case 401: return '拒绝操作，请求数据不合法!';
	case 402: return '拒绝操作，账号没有登录!';
	case 403: return '登陆失败，多余的登陆请求!';
	case 404: return '登陆失败，账号不存在!';
	case 405: return '登陆失败，密码错误!';
	case 406: return '设备不在线，对设备的操作失败!';
	case 407: return '命令号错误!';
	case 408: return '设备不存在!';
	case 409: return '无效用户!';
	case 410: return '设备已存在!';
	case 411: return '账号已存在!';
	case 412: return '找回密码失败!';
	case 413: return '手机号已存在!';
	default: return "操作失败，错误代码$e!";
}
}

class zbapi
{
	var $uri="tcp://114.215.25.114:7100";
        //var $uri = "127.0.0.1:7100";
	var $username='';
	var $phone='';
	var $account='';
	var $pwd='';
	var $fp=0;
	var $role=-1;
	var $devinfo;
	var $groupinfo;
	var $alarmInfo; 
	var $uniqAcc;
	
	function __construct($acc,$pw)
    {
        $this->account=$acc;
		$this->pwd=$pw;
		//$this->login();
		//$this->loadDevInfo();
		//$this->loadIMEIInfo('9999100100013');
		//$this->loadAlarmInfo('9999100100013',0);
		//echo $this->modifyIMEINum('9999100100013','1A23456');
		
		//echo $this->phone;
    }
	function __destruct(){
		 fclose($this->fp);
	}
	function login(){
		$ret=json_decode($this->req(1,'{"Account":"'.$this->account.'", "Pwd":"'.md5($this->account.$this->pwd).'"}'),true);
		
		if ($ret['Code']=='0'){
		  $this->phone=	$ret['Phone'];
		  $this->username=$ret['Name'];
		  $this->role=$ret['Role'];

		  $contacts = $this->loadUserInfo();
		  foreach ($contacts as $contact) {
			  if ($this->account != $contact['Account'] && $this->account != $contact['Phone']) {
				  continue;
			  }

			  if ($this->account == $contact['Phone'] || $this->account == $contact['Account']) {
				  $this->uniqAcc = $contact['Account'];
			  }
		  }
		 
		  return 0;
		} 
		else {
		  $this->phone='';
		  $this->username='';
		  $this->role=	-1;
		  if ($ret['Code']=='') $ret['Code']='400';
		  return $ret['Code'];	
		}
		
		
	}
	function loadDevInfo(){
		$ret=json_decode($this->req(144,'{"Account":"'.$this->account.'"}'),true);
		if ($ret['Code']=='0'){
			$this->devinfo=$ret['DevInfo'];

			$groups = array();
			foreach ($this->devinfo as $devinfo) {
				$groups[$devinfo['GroupId']]['PreLocate'] = $devinfo['PreLocate'];
				$groups[$devinfo['GroupId']]['LocIntval'] = $devinfo['LocIntval'];
				$groups[$devinfo['GroupId']]['LoopType'] = $devinfo['LoopType'];
				$groups[$devinfo['GroupId']]['LoopValue'] = intval($devinfo['LoopValue']);
				$groups[$devinfo['GroupId']]['SleepIntval'] = $devinfo['SleepIntval'];
				$groups[$devinfo['GroupId']]['AlarmState'] = $devinfo['AlarmState'];
				$groups[$devinfo['GroupId']]['ReportIntval'] = $devinfo['ReportIntval'];
			}
			$this->groupinfo = $groups;
			//for($i=0;$i<count($this->devinfo),$i++) $this->devinfo[$i]=$this->devinfo[$i].'abc';
			//$this->devinfo=array_merge($ret['DevInfo'],$this->devinfo);
		//	print_r($this->devinfo);
		//	foreach ($this->devinfo as $v) echo $v['DevId'],'  : ',$v['IMEI'];
		}
		else $this->devinfo=0;
	
	}
	function loadUserInfo($flag=false){
		if ($flag) {
			$ret=json_decode($this->req(146,'{"Account":"'.$this->uniqAcc.'"}'),true);
		} else {
			$ret=json_decode($this->req(146,'{"Account":"'.$this->account.'"}'),true);
		}
		if ($ret['Code']=='0'){
			for ($i=0;$i<count($ret['Contacts']);$i++){
			   if (($ret['Contacts'][$i]['Phone']===$_SESSION['zbAcc'] || $ret['Contacts'][$i]['Account']===$_SESSION['zbAcc'])){
				   $v=$ret['Contacts'][$i];
				   unset($ret['Contacts'][$i]);
				   array_unshift($ret['Contacts'],$v);
			   }	
			}
			//$r=$ret['Contacts'];
			//for ($i;$i<22;$i++) $r=array_merge($r,$ret['Contacts']);
			//return $r;
			return $ret['Contacts'];
			
		//	print_r($this->devinfo);
		//	foreach ($this->devinfo as $v) echo $v['DevId'],'  : ',$v['IMEI'];
		}
		else return false;
	
	}
	function loadAlarmInfo(){
		$ret=json_decode($this->req(6,'{"Account":"'.$this->account.'"}'),true);
		if ($ret['Code']=='0'){
			for ($i=0;$i<count($ret['AlarmInfo']);$i++) $ret['AlarmInfo'][$i]['AlarmId']=$i+1;
			return $ret['AlarmInfo'];
			
		//	print_r($this->devinfo);
		//	foreach ($this->devinfo as $v) echo $v['DevId'],'  : ',$v['IMEI'];
		}
		else return false;
	
	}
	
	
	
	function loadIMEIInfo($IMEI){
		$ret=json_decode($this->req(3,'{"Devices":["'.$IMEI.'"]}'),true);
		if ($ret['Code']=='0'){
			$ret=$ret['DevInfo'][0];
			//print_r($ret);
			for ($i=0;$i<count($this->devinfo);$i++){ if ($this->devinfo[$i]['IMEI']==$ret['IMEI']){$this->devinfo[$i]=$ret;break;}}
		//	foreach ($this->devinfo as $v) echo  '  ID',$v['DevId'],'  : ',$v['IMEI'];
		//	print_r($this->devinfo);
		//	foreach ($this->devinfo as $v) echo $v['DevId'],'  : ',$v['IMEI'];
		}
		//else $this->devinfo=0;
	
	}	
	function loadAlarmInfoEX($IMEI,$MaxId){
	$ret=json_decode($this->req(4,'{"Device":"'.$IMEI.'","AlarmMaxId":'.$MaxId.'}'),true);
		if ($ret['Code']=='0'){
			$this->alarmInfo=$ret['AlarmInfo'];
			for ($i=0;$i<count($this->alarmInfo);$i++) $this->alarmInfo[$i]['AlarmId']=$i+1;
		//	print_r($this->devinfo);
			//foreach ($this->alarmInfo as $v) echo $v['AlarmId'],'  : ',$v['IMEI'];
		}
		else $this->alarmInfo=0;		
	}
	function modifyIMEINum($IMEI,$CatNum){
		$ret=json_decode($this->req(5,'{"IMEI":"'.$IMEI.'","Number":"'.$CatNum.'"}'),true);
		print_r($ret);
		if ($ret['Code']=='0') return 0;
		else return -1;
		 
	}
	function req($cmd,$body){
		$ret='';
		for ($k=0;$k<5;$k++){
			
			
			if (!$this->fp) $this->fp=stream_socket_client($this->uri, $errno, $errstr, 5);
			//stream_set_blocking($this->fp, true);
			//stream_set_timeout($this->fp, 3);
			if(!$this->fp)
			  {
				echo "erreur : $errno - $errstr<br />n";
			  }
			  else
			  {
				$tmp=pack('n',256+$cmd).pack('N',strlen($body)).$body;  
				fwrite($this->fp,$tmp);
				$master=0;
				$read[]=$this->fp;
				$mod_fd = @stream_select($read, $_w = NULL, $_e = NULL, 10);
				if(count($read)){
					$cmda =unpack("n", @fread($this->fp, 2));
					$cmda=$cmda[1];
					$len =unpack("N", @fread($this->fp, 4));
					$len=$len[1];
					$baklen=$len;
					
				//	echo "cmd:$cmd  len:$len" ; 
					while ($len>0){
						$r=@fread($this->fp, $len);
						//if ($r) break;
						$len=$len-strlen($r);	
						$ret=$ret.$r;	
						
					}			
				
				  }
			  }
				//echo $ret;
		if ($ret) break; 
		fclose($this->fp);
		$this->fp=0;
		}
	logger("CMD:$cmd ==>".$body."\r\n".$ret);	
	return $ret;
	}
}
?>
