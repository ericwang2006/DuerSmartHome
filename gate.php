<?php
require_once __DIR__.'/dueros.php';
require_once __DIR__.'/hass.php';
//设备发现
function Hdiscovery($d, $devices){
	return $d->discovery();
}

//设备控制
function Hcontrol($o, $d){
	//echo "a";
	$result = $d->control();
	$name = substr( $o->header->name, 0, -7);
	$response_name = $name.'Confirmation';
	$header = array(
		"namespace"           =>    "DuerOS.ConnectedHome.Control",
		"name"                       =>    $response_name,
		"messageId "            =>    $d->getMessageID(),
		"payloadVersion"  =>    "1"
	);
	if($result !== false){
		switch($o->header->name){
			case 'TurnOnRequest':
				$payload = array("attributes" => "");
				break;
			case 'TimingTurnOnRequest':
				$payload = array("attributes" => "");
				break;
			case 'TurnOffRequest':
				$payload = array("attributes" => "");
				break;
			case 'TimingTurnOffRequest':
				$payload = array("attributes" => "");
				break;
			case 'PauseRequest':
				$payload = array("attributes" => "");
				break;
			case 'ContinueRequest':
				$payload = array("attributes" => "");
				break;
			case 'SetBrightnessPercentageRequest':
				$payload = array("previousState" => array("brightness" => array("value" => "null")), "brightness" => array("value" => $obj->payload->brightness->value), "attributes" => "");
				break;
			case 'SetColorRequest':
				$payload = array("achievedState" => array("color" => $d->object2array($o->payload->color)), "attributes" => "");
				break;
			//more
			default:
				$payload = array();
				break;
		}
	}else{
		return 'hahaha';
	}
	return json_encode(array("header" => $header, "payload" => $payload));
}

//获得dueros的请求
$poststr = file_get_contents("php://input");
$obj = json_decode($poststr);
$duer = new dueros($obj, DEVICES, URL, PASS);
switch($obj->header->namespace){
	case 'DuerOS.ConnectedHome.Discovery':
		$resultStr = Hdiscovery($duer, DEVICES);
		break;
	case 'DuerOS.ConnectedHome.Control':
		//echo "lala";
		$resultStr = Hcontrol($obj, $duer);
		break;
	case 'DuerOS.ConnectedHome.Query':
		$resultStr = Hstatus();
		break;
	default:
		$resultStr='Nothing return,there is an error~!!';
}
error_log('-------');
error_log('----get-request---');
error_log($poststr);
error_log('----reseponse---');
error_log($resultStr);
echo($resultStr);
