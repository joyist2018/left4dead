<?php

require_once 'SourceQuery.php';

$server = new SourceQuery('172.81.237.27', 27025);
$infos  = $server->getInfos();
$infosTwo = $server->getPlayers();
echo '服务器上有 ' . $infos['players'] . ' 名玩家  </br>
服务器名字: " ' .$infos['name'] .'". </br>
预留槽: '. $infos['places'].' 
</br></br>玩家信息: '.$infos['pass'].'</br>
</br> ';
echo '</br></br>玩家信息 ' .
var_dump($infosTwo);
