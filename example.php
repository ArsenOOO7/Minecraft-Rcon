<?php

require_once(__DIR__."/Rcon.php");

$ip = "example.com";
$port = 19132;
$password = "RconPassword";

$rcon = new Rcon($ip, $port, $password);
$rcon->sendCommand("say Hello World!");

echo $rcon->getLastResponse();
