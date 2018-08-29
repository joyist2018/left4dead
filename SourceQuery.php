<?php
class SourceQuery {

	public function __construct($ip, $port=27015) {
		$this->ip = $ip;
		$this->port = $port;
	}

	protected function query($query) {
		$fp = fsockopen('udp://' . $this->ip, $this->port, $errno, $errstr, 2);
		if (!$fp) {
			trigger_error('服务器没有响应', E_USER_NOTICE);
			return false;
		} else {
			fwrite($fp, $query);
			stream_set_timeout($fp, 2);
			$result = '';
			do {
				$result .= fread ($fp, 1);
				$fpstatus = socket_get_status($fp);
			} while ($fpstatus['unread_bytes']);
			fclose($fp);
			return $result;
		}
	}

	protected function getChallenge() {
		$challenge = $this->query("\xFF\xFF\xFF\xFFU\xFF\xFF\xFF\xFF");
		return substr($challenge, 5);
	}
	
	// Hex 签名 Dec http://fr2.php.net/manual/en/function.hexdec.php#97172
	private function hexdecs($hex){
	    $dec = hexdec($hex);
	    $max = pow(2, 4 * (strlen($hex) + (strlen($hex) % 2)));
	    $_dec = $max - $dec;
	    return $dec > $_dec ? -$_dec : $dec;
	}
	
	public function getInfos() {
		$infos = $this->query("\xFF\xFF\xFF\xFFTSource Engine Query\x00");

		//  确定使用的协议
		$protocol = hexdec(substr(bin2hex($infos), 8, 2));
			
		if($protocol == 109) return $this->getInfos1($infos);
		else if($protocol == 73) return $this->getInfos2($infos);
		
		trigger_error('未知的服务器类型', E_USER_NOTICE);
		return false;
	}
	
	protected function getInfos1($infos) {
		// 拆分信息
		$infos = chunk_split(substr(bin2hex($infos), 10), 2, '\\');
		@list($serveur['ip'], $serveur['name'], $serveur['map'], $serveur['mod'], $serveur['modname'], $serveur['params']) = explode('\\00', $infos);
		
		// 拆分参数
		$serveur['params'] = substr($serveur['params'],0,18);
		
		$serveur['params'] = chunk_split(str_replace('\\', '', $serveur['params']), 2, ' ');
		list($params['players'], $params['places'], $params['protocol'], $params['dedie'], $params['os'], $params['pass']) = explode(' ', $serveur['params']);
		$params = array(
			'id'		=>	0, // 不支持
			'bots'		=>	0, // 不支持
			'ip'		=>	$this->ip,
			'port'		=>	$this->port,
			'players'	=>	hexdec($params['players']),
			'places'	=>	hexdec($params['places']),
			'protocol'	=>	hexdec($params['protocol']),
			'dedie'		=>	chr(hexdec($params['dedie'])),
			'os'		=>	chr(hexdec($params['os'])),
			'pass'		=>	hexdec($params['pass'])
		);
		unset($serveur['ip']);
		unset($serveur['params']);
		
		$serveur = array_map(function($item){
			return pack("H*", str_replace('\\', '', $item));
		}, $serveur);
		
		$infos = ($params + $serveur);
		return $infos;
	}
	
	protected function getInfos2($infos) {
		// 拆分信息
		$infos = chunk_split(substr(bin2hex($infos), 12), 2, '\\');
		@list($serveur['name'], $serveur['map'], $serveur['mod'], $serveur['modname'], $serveur['params']) = explode('\\00', $infos, 5);
		
		// 拆分参数
		$serveur['params'] = substr($serveur['params'], 0);
		
		$serveur['params'] = chunk_split(str_replace('\\', '', $serveur['params']), 2, ' ');
		list($params['id1'], $params['id2'], $params['players'], $params['places'], $params['bots'], $params['dedie'], $params['os'], $params['pass']) = explode(' ', $serveur['params']);
		$params=array(
			'id'		=>  hexdec($params['id2'] . $params['id1']),
			'ip'		=>	$this->ip,
			'port'		=>	$this->port,
			'players'	=>	hexdec($params['players']),
			'places'	=>	hexdec($params['places']),
			'bots'		=>	hexdec($params['bots']),
			'protocol'	=>	73,
			'dedie'		=>	chr(hexdec($params['dedie'])),
			'os'		=>	chr(hexdec($params['os'])),
			'pass'		=>	hexdec($params['pass'])
		);
		unset($serveur['params']);
		
		$serveur = array_map(function($item){
			return pack("H*", str_replace('\\', '', $item));
		}, $serveur);
		
		$infos = ($serveur + $params);	
		return $infos;
	}
	
	public function getPlayers() {
		$challenge = $this->getChallenge();
	
		$infos = $this->query("\xFF\xFF\xFF\xFFU" . $challenge);
		
		$infos = chunk_split(substr(bin2hex($infos), 12), 2, '\\');
		
		$infos = explode('\\', $infos);
		
		$players = array();
		for ($i = 0; isset($infos[$i + 1]); $i = $j + 9) {
			
			// 玩家名
			$name = '';
			for ($j = $i + 1; isset($infos[$j]) && $infos[$j] != '00'; $j++) $name .= chr(hexdec($infos[$j]));
			
			if (!isset($infos[$j + 8])) break;
			
			// 游戏时间
			eval('$time="\x'.trim(chunk_split($infos[$j + 5] . $infos[$j + 6] . $infos[$j + 7] . $infos[$j + 8], 2,"\x"), "\x") . '";');
			list(,$time) = unpack('f', $time);
			
			// 得分
			$score = ltrim($infos[$j + 4] . $infos[$j + 3] . $infos[$j + 2] . $infos[$j + 1], '0');
			
			$players[] = array(
				'</br>id'	=>	hexdec($infos[$i]),
				'名字'	=>	$name,
				'得分'	=>	empty($score)? 0 : $this->hexdecs($score),
				'时间'	=>	$time
			);
		}
		return $players;
	}
}
?>
