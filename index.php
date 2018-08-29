<?php

require_once 'SourceQuery.php';

$server = new SourceQuery('172.81.237.27', 27025);
$infos  = $server->getInfos();
$infosTwo = $server->getPlayers();


echo '<title>漫步云端战役服务器群motd chdong.top/</title>
<div align="center"></br>
<a href="http://chdong.top/" target="_blank"><img src="http://game.cgcss.com/l4d2_servermessage/logo.png"></a></br>
</br>
<a href="http://steamcommunity.com/groups/4076664" target="_blank">求生之路2 战役服务器群 公布</a>
</br>
</br>当前服务器上有 ' . $infos['players'] . ' 名玩家  </br>
服务器名字: " ' .$infos['name'] .'". </br>
预留槽: '. $infos['places'].' 
</br></br>玩家信息: '.$infos['pass'].'</br> ';
echo '</br></br>玩家信息 '.
var_dump($infosTwo);
