<?php
require_once __DIR__.'/dueros.php';
$chars = md5(uniqid(mt_rand(), true));
$uuid  = substr($chars,0,8) . '-';
$uuid .= substr($chars,8,4) . '-';
$uuid .= substr($chars,12,4) . '-';
$uuid .= substr($chars,16,4) . '-';
$uuid .= substr($chars,20,12);

$poststr = file_get_contents("php://input");
$obj = json_decode($poststr);
$messageId = $uuid;
switch($obj->header->namespace){
	case 'DuerOS.ConnectedHome.Discovery':
		$header = array(
			"namespace"           =>    "DuerOS.ConnectedHome.Discovery",
			"name"                       =>    "DiscoverAppliancesResponse",
			"messageId "            =>    $messageId,
			"payloadVersion"  =>    "1"
		);
		//$header = json_encode($tmp);
		$payload = array(
			"discoveredAppliances"  =>  array(
				array(
					"actions"  =>  array("turnOn", "turnOff"),
					"applianceTypes"  => array("LIGHT"),
					"additionalApplianceDetails"  =>  array(),
					"applianceId"  =>  "light.sonoff",
					"friendlyDescription"  =>  "LightDeviceId",
					"friendlyName"  =>  "卧室的灯",
					"isReachable"  =>  true,
					"manufacturerName"  =>  "Nodemcu",
					"modelName"  =>  "fancyLight",
					"version"  =>  "1.0"
				),
				array(
					"actions"  =>  array("turnOn", "turnOff"),
					"applianceTypes"  => array("HUMIDIFIER"),
					"additionalApplianceDetails"  =>  array(),
					"applianceId"  =>  "switch.pump",
					"friendlyDescription" =>  "浇水",
					"friendlyName"  =>  "植物浇水器",
					"isReachable"  =>  true,
					"manufacturerName"  =>  "broadlink",
					"modelName"  =>  "植物浇水器",
					"version"  =>  "1.0"
				),
				array(
					"actions"  =>  array("turnOn", "turnOff"),
					"applianceTypes"  => array("LIGHT"),
					"additionalApplianceDetails"  =>  array(),
					"applianceId"  =>  "switch.light",
					"friendlyDescription" =>  "补光",
					"friendlyName"  =>  "植物补光器",
					"isReachable"  =>  true,
					"manufacturerName"  =>  "broadlink",
					"modelName"  =>  "植物补光器",
					"version"  =>  "1.0"
				),
				array(
					"actions"  =>  array("turnOn", "turnOff"),
					"applianceTypes"  => array("HEATER"),
					"additionalApplianceDetails"  =>  array(),
					"applianceId"  =>  "switch.temperature",
					"friendlyDescription" =>  "温度",
					"friendlyName"  =>  "植物调温器",
					"isReachable"  =>  true,
					"manufacturerName"  =>  "broadlink",
					"modelName"  =>  "植物调温器",
					"version"  =>  "1.0"
				),
				array(
					"actions"  =>  array("getTemperatureReading"),
					"applianceTypes"  => array("AIR_MONITOR"),
					"additionalApplianceDetails"  =>  array(),
					"applianceId"  =>  "sensor.plant_temperature",
					"friendlyDescription" =>  "温度",
					"friendlyName"  =>  "花花草草温度",
					"isReachable"  =>  true,
					"manufacturerName"  =>  "miflora",
					"modelName"  =>  "花花草草温度",
					"version"  =>  "1.0"
				),
				array(
					"actions"  =>  array("getHumidity"),
					"applianceTypes"  => array("AIR_MONITOR"),
					"additionalApplianceDetails"  =>  array(),
					"applianceId"  =>  "sensor.plant_moisture",
					"friendlyDescription" =>  "湿度",
					"friendlyName"  =>  "花花草草湿度",
					"isReachable"  =>  true,
					"manufacturerName"  =>  "miflora",
					"modelName"  =>  "花花草草湿度",
					"version"  =>  "1.0"
				),
				array(
					"actions"  =>  array("getWaterQuality"),
					"applianceTypes"  => array("AIR_MONITOR"),
					"additionalApplianceDetails"  =>  array(),
					"applianceId"  =>  "sensor.plant_conductivity",
					"friendlyDescription" =>  "电导率",
					"friendlyName"  =>  "花花草草电导率",
					"isReachable"  =>  true,
					"manufacturerName"  =>  "miflora",
					"modelName"  =>  "花花草草电导率",
					"version"  =>  "1.0"
				),
				array(
					"actions"  =>  array("getRunningStatus"),
					"applianceTypes"  => array("AIR_MONITOR"),
					"additionalApplianceDetails"  =>  array(),
					"applianceId"  =>  "sensor.plant_battery",
					"friendlyDescription" =>  "电量",
					"friendlyName"  =>  "花花草草电量",
					"isReachable"  =>  true,
					"manufacturerName"  =>  "miflora",
					"modelName"  =>  "花花草草电量",
					"version"  =>  "1.0"
				)
			)
		);
		//$payload = json_encode($payload);
		$resultStr = json_encode(array("header" => $header, "payload" => $payload));
		break;
	case 'DuerOS.ConnectedHome.Control':
		$result = Device_control($obj);
		if($result->result == "True"){
			$header = array(
				"namespace"           =>    "DuerOS.ConnectedHome.Control",
				"name"                       =>    $result->name,
				"messageId "            =>    $messageId,
				"payloadVersion"  =>    "1"
			);
			$payload = array();
			$resultStr = json_encode(array("header" => $header, "payload" => $payload));
		}
		break;
	case 'DuerOS.ConnectedHome.Query':
		$result = Device_status($obj);
		$name = substr( $obj->header->name, 0, -7);
		$response_name = $name.'Response';
		if($result->entity_id === $obj->payload->appliance->applianceId){
			$header = array(
				"namespace"       =>  "DuerOS.ConnectedHome.Query",
				"name"            =>  $response_name,
				"messageId "      =>  $messageId,
				"payloadVersion"  =>  "1"
			);
			if($obj->header->name === "GetAirQualityIndexRequest"){
				$payload = array(
					"AQI" => array("value" => $result->state)
				);
			}elseif($obj->header->name === "GetAirPM25Request"){
				$payload = array(
					"PM25" => array("value" => $result->state)
				);
			}elseif($obj->header->name === "GetCO2QuantityRequest"){
				$payload = array(
					"ppm" => array("value" => $result->state)
				);
			}elseif($obj->header->name === "GetHumidityRequest"){
				$payload = array(
					"humidity" => array("value" => $result->state)
				);
			}elseif($obj->header->name === "GetTemperatureReadingRequest"){
				$payload = array(
					"temperatureReading" => array("value" => $result->state, "scale" => "CELSIUS")
				);
			}elseif($obj->header->name === "GetTargetTemperatureRequest"){
				$payload = array(
					"targetTemperature" => array("value" => $result->state, "scale" => "CELSIUS"),
					"temperatureMode"   => array("value" => $result->state, "friendlyName" => $result->mode)
				);
			}elseif($obj->header->name === "GetRunningTimeRequest"){
				$payload = array(
					"totalTimeInSeconds" => array("value" => $result->state)
				);
			}elseif($obj->header->name === "GetTimeLeftRequest"){
				$payload = array(
					"timeLeftInSeconds" => array("value" => $result->state)
				);
			}elseif($obj->header->name === "GetRunningStatusRequest"){
				$payload = array(
					"runningState" => array("value" => $result->state)
				);
			}elseif($obj->header->name === "GetElectricityCapacityRequest"){
				$payload = array(
					"attributes" => array("name" => "electricityCapacity", "value" => $result->state, "scale" => "%", "timestampOfSample" => time(), "uncertaintyInMilliseconds" => 1000)
				);
			}else{
				$payload = array(
						"value"   =>  $result->value,
						"scale"   =>  $result->scale
					);
			}
			$resultStr = json_encode(array("header" => $header, "payload" => $payload));
		}else{
			$resultStr = $result;
		}
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
