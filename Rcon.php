<?php

declare(strict_types = 1);

class Rcon{
	
	/** @var Socket */
	private $socket;
	
	/** @var String */
	private $lastResponse;
	/** @var String */
	private $status = 0;
	
	const SUCCESSFUL_LOGIN = 0;
	const FAILED_LOGIN = 1;
	
	const PACKET_AUTH = 3;
	const PACKET_SEND = 2;
	
	
	/**
	 * @param String $ip
	 * @param Integer $port
	 * @param String $rcon
	 *
	 */
	function __construct(string $ip, int $port, string $rcon){
		
		$this->socket = @fsockopen("tcp://".gethostbyname($ip), $port);
		$this->connect($rcon);
		
	}
	
	
	
	/**
	 * @param String $rcon
	 *
	 */
	function connect(string $rcon){
		
		if(!$this->socket)
			throw new RconException("Невозможно подключится к серверу!");
		
		$data = pack("VV", 1, Rcon::PACKET_AUTH).$rcon."\x00\x00";
		$data = pack("V", strlen($data)).$data;
		
		fwrite($this->socket, $data);
		
		$status = fread($this->socket, 1) == 0 ? Rcon::SUCCESSFUL_LOGIN : Rcon::FAILED_LOGIN;
		if($status == 1)
			throw new RconException("Проверьте RCON!");
		
		$this->status = 1;
		
	}
	
	
	
	/**
	 * @param String $command
	 *
	 */
	function sendCommand(string $command){
		
		if($this->status == 0)
			throw new RconException("Отправить команду невозможно, т.к. нет подключения к серверу!");
		
		$data = pack("VV", 1, Rcon::PACKET_SEND).$command."\x00\x00";
		$data = pack("V", strlen($data)).$data;
		
		fwrite($this->socket, $data);		
		$read = $this->readPacket();
		
		$this->lastResponse = $read["bodyCmd"];
		
	}
	
	
	
	/**
	 * @return Array
	 *
	 */
	function readPacket() : array{
		
		$packet = fread($this->socket, 4);
		$packet = unpack("V1size", $packet);
		
		$response = fread($this->socket, $packet["size"]);
		$response = unpack("V1id/V1type/a*bodyCmd", $response);
		
		return $response;
		
	}
	
	
	
	/**
	 * @return String
	 *
	 */
	function getResponse() : string{
		
		return $this->lastResponse;
		
	}
	
	
	
	function disconnect() : void{
		
		fclose($this->socket);
		
	}
}

class RconException extends \Exception{
	
}