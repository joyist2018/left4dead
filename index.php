<?php

require_once 'SourceQuery.php';

$server = new SourceQuery('178.151.158.39', 27015);
$infos  = $server->getInfos();
$infosTwo = $server->getPlayers();
echo 'There is ' . $infos['players'] . ' player(s) on the server "' .$infos['name'] .'". Places: '. $infos['places'].' Pass: '.$infos['pass'];

var_dump($infosTwo);