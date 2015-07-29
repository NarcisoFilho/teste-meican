<?php

namespace app\modules\topology\models;

class NSIParser {

	private $topology = array();
	private $errors = array();
	private $xml;
	private $xpath;

	//function __construct($discoveryUrl, $certName, $certPass){
	//	$this->url = $discoveryUrl;
	//	$this->cert_password = $certPass;
	//	$this->cert_file = realpath(__DIR__."/../../../certificates/".$certName);
	//}

	function loadXml($input) {
		$this->xml = new \DOMDocument();
		$this->xml->loadXML($input);
		$this->xpath = new \DOMXpath($this->xml);

		$this->parseNotification();
		$this->parseNets();
		$this->parseProviderData();
	}

	function loadFile($url) {
		$ch = curl_init();

		$options = array(
				CURLOPT_RETURNTRANSFER => true,
				//CURLOPT_HEADER         => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_SSL_VERIFYPEER => false,

				CURLOPT_USERAGENT => 'Meican',
				//CURLOPT_VERBOSE        => true,
				CURLOPT_URL => $url,
				//CURLOPT_SSLCERT => $this->cert_file,
				//CURLOPT_SSLCERTPASSWD => $this->cert_password,
		);

		curl_setopt_array($ch , $options);

		$output = curl_exec($ch);
		curl_close($ch);
		//	echo $output;
		$this->loadXml($output);
	}
	
	function getData() {
		return $this->topology;
	}

	function getErrors() {
		return $this->errors;
	}
	
	function addProviderData($domainName, $nsa, $type, $name, $lat, $lng) {
		$nsa = str_replace("urn:ogf:network:","",$nsa);
		$this->topology["domains"][$domainName]["nsa"][$nsa]['name'] = $name;
		$this->topology["domains"][$domainName]["nsa"][$nsa]["type"] = $type;
		$this->topology["domains"][$domainName]["nsa"][$nsa]["lat"] = $lat;
		$this->topology["domains"][$domainName]["nsa"][$nsa]["lng"] = $lng;
	}
	
	function addProviderService($domainName, $nsa, $service) {
		$nsa = str_replace("urn:ogf:network:","",$nsa);
		$this->topology["domains"][$domainName]["nsa"][$nsa]["services"][$service['url']] = $service['type'];
	}

	function addPort($netId, $netName, $biPortId, $biportName, $portId, $portType, $vlan, $alias) {
		$netUrn = str_replace("urn:ogf:network:","",$netId);
		$portUrn = str_replace("urn:ogf:network:","",$portId);
		$biPortUrn = str_replace("urn:ogf:network:","",$biPortId);
		$aliasUrn = str_replace("urn:ogf:network:","",$alias);
		
		$id = explode(":", $netId);
		//         0   1     2         3        4    5
		//	      urn:ogf:network:cipo.rnp.br:2014::POA

		$domainName = $id[3];

		$devicePort = str_replace($netId.":", "", $biPortId);
		if (strpos($devicePort,'urn') !== false) {
		    $this->errors["Unknown URN"][$devicePort] = null;
		    return;
		}
		
		$devicePortArray = explode(":", $devicePort);
		if (count($devicePortArray) > 1) {
			$deviceName = $devicePortArray[0];
			$devicePortArray[0] = "";
			$devicePortArray = implode(":", $devicePortArray);
			$portName = substr($devicePortArray, 1);
		} else {
			$deviceName = "";
			$portName = implode(":", $devicePortArray);
		}

		if (!isset($this->topology["domains"][
				$domainName]["nets"][$netUrn]["devices"][
						$deviceName]["biports"][$biPortUrn])) {
			$this->topology["domains"][
					$domainName]["nets"][$netUrn]["name"] = $netName;
			$this->topology["domains"][
					$domainName]["nets"][$netUrn]["devices"][
							$deviceName]["biports"][$biPortUrn] = array();
					if (!$biportName) $biportName = $portName;
			$this->topology["domains"][
					$domainName]["nets"][$netUrn]["devices"][
							$deviceName]["biports"][$biPortUrn]["port"] = $biportName;
		} 
		
		$devicePort = str_replace($netId.":", "", $portId);
		$devicePortArray = explode(":", $devicePort);
		if (count($devicePortArray) > 1) {
			$devicePortArray[0] = "";
			$devicePortArray = implode(":", $devicePortArray);
			$portName = substr($devicePortArray, 1);
		} else {
			$portName = implode(":", $devicePortArray);
		}
		
		$this->topology["domains"][
				$domainName]["nets"][$netUrn]["devices"][$deviceName]["biports"][
						$biPortUrn]["uniports"][$portUrn]['port'] = $portName;
		$this->topology["domains"][
				$domainName]["nets"][$netUrn]["devices"][$deviceName]["biports"][
						$biPortUrn]["uniports"][$portUrn]['type'] = $portType;
		$this->topology["domains"][
				$domainName]["nets"][$netUrn]["devices"][$deviceName]["biports"][
						$biPortUrn]["uniports"][$portUrn]['vlan'] = $vlan;
				if ($aliasUrn) 
		$this->topology["domains"][
				$domainName]["nets"][$netUrn]["devices"][$deviceName]["biports"][
						$biPortUrn]["uniports"][$portUrn]['aliasUrn'] = $aliasUrn;
	}
	
	function parseNets() {
		$xmlns = "http://schemas.ogf.org/nml/2013/05/base#";
		$tagName = "Topology";
		foreach ($this->xml->getElementsByTagNameNS($xmlns, $tagName) 
				as $netNode) {
			if($netNode->prefix != "") $netNode->prefix .= ":";
			$netId = $netNode->getAttribute('id');
			$netUrn = str_replace("urn:ogf:network:","",$netId);
			$this->xpath->registerNamespace('x', $xmlns);
			$netNameNode = $this->xpath->query(".//x:name", $netNode);
			if ($netNameNode->item(0)) {
				$netName = $netNameNode->item(0)->nodeValue;
			} else {
				$netName = null;
			}
			
			$id = explode(":", $netId);
			//         0   1     2         3        4    5
			//	      urn:ogf:network:cipo.rnp.br:2014::POA
			
			$domainName = $id[3];
			
			$longitudeNode = $this->xpath->query(".//longitude", $netNode);
			$latitudeNode = $this->xpath->query(".//latitude", $netNode);
			$addressNode = $this->xpath->query(".//address", $netNode);
			
			if($longitudeNode->item(0)) {
				$this->topology["domains"][
						$domainName]["nets"][$netUrn]["lat"] = $latitudeNode->item(0)->nodeValue;
				$this->topology["domains"][
						$domainName]["nets"][$netUrn]["lng"] = $longitudeNode->item(0)->nodeValue;
				$this->topology["domains"][
						$domainName]["nets"][$netUrn]["address"] = $addressNode->item(0)->nodeValue;
			}
			
			$this->topology["domains"][
					$domainName]["nets"][$netUrn]["name"] = $netName;
			
			$this->parseBiPorts($netNode, $netId, $netName);
		}
	}
	
	function parseNotification() {
		$xmlns = "http://schemas.ogf.org/nsi/2014/02/discovery/types";
		$tagName = "notifications";
		foreach ($this->xml->getElementsByTagNameNS($xmlns, $tagName)
				as $notNode) {
			$this->topology["local"]["nsa"] = str_replace(
					"urn:ogf:network:","",$notNode->getAttribute('providerId'));
			return;
		}
	}

	function parseBiPorts($netNode, $netId, $netName) {
		$biPortNodes = $this->xpath->query(".//x:BidirectionalPort", $netNode);
		if($biPortNodes) {
			foreach ($biPortNodes as $biPortNode) {
				$biPortId = $biPortNode->getAttribute('id');
				$id = explode(":", $biPortId);

				if ($id[0] !== "urn") {
					$this->errors["Unknown URN"][$biPortId] = null;
					continue;
				}
				
				$biportNameNode = $this->xpath->query(".//x:name", $biPortNode);
				if ($biportNameNode->item(0)) {
					$biportName = $biportNameNode->item(0)->nodeValue;
				} else {
					$biportName = null;
				}

				$this->parseUniPorts($netNode, $biPortNode, $netId, $netName, $biPortId, $biportName);
			}
		}
	}

	function parseUniPorts($netNode, $biPortNode, $netId, $netName, $biPortId, $biportName) {
		$portNodes = $this->xpath->query(".//x:PortGroup", $biPortNode);
		if($portNodes) {
			foreach ($portNodes as $portNode) {
				$portId = $portNode->getAttribute('id');

				$id = explode(":", $portId);
				if ($id[0] !== "urn") {
					$this->errors["Unknown URN"][$portId] = null;
					continue;
				}

				$vlanAndAlias = $this->parseVlanAndAlias($netNode, $portId);
				
				$this->addPort(
						$netId,
						$netName,
						$biPortId,
						$biportName,
						$portId,
						$this->parseUniPortType($netNode, $portId),
						$vlanAndAlias[0],
						$vlanAndAlias[1]);
			}
		}
	}
	
	function parseAlias($portNode) {
		$relationNodes = $this->xpath->query(".//x:Relation", $portNode);
		foreach ($relationNodes as $relationNode) {
			$portNodes = $this->xpath->query(".//x:PortGroup", $relationNode);
			foreach ($portNodes as $portNode) {
				$portId = $portNode->getAttribute('id');
				$id = explode(":", $portId);
				if ($id[0] !== "urn") {
					$this->errors["Unknown URN"][$portId] = null;
					continue;
				}
				return $portId;
			}
		}
		return null;
	}
	
	function parseVlanAndAlias($netNode, $portId) {
		$relationNodes = $this->xpath->query(".//x:Relation", $netNode);
		foreach ($relationNodes as $relationNode) {
			$portNodes = $this->xpath->query(".//x:PortGroup", $relationNode);
			if($portNodes) {
				foreach ($portNodes as $portNode) {
					$id = $portNode->getAttribute('id');

					$temp = explode(":", $id);
					if ($temp[0] !== "urn") {
						$this->errors["Unknown URN"][$id] = null;
						continue;
					}

					if ($id === $portId) {
						
						$vlanRangeNode = $this->xpath->query(".//x:LabelGroup", $portNode);

						if($vlanRangeNode->item(0)) {
							return [$vlanRangeNode->item(0)->nodeValue, 
									$this->parseAlias($portNode)];
						} else {
							continue;
						}
					}
				}
			}
		}
		return null;
	}
	
	function parseUniPortType($netNode, $portId) {
		$relationNodes = $this->xpath->query(".//x:Relation", $netNode);
		foreach ($relationNodes as $relationNode) {
			$portNodes = $this->xpath->query(".//x:PortGroup", $relationNode);
			if($portNodes) {
				foreach ($portNodes as $portNode) {
					$id = $portNode->getAttribute('id');
	
					$temp = explode(":", $id);
					if ($temp[0] !== "urn") {
						$this->errors["Unknown URN"][$id] = null;
						continue;
					}
	
					if ($id === $portId) {
						if ($relationNode->getAttribute("type") == 
								"http://schemas.ogf.org/nml/2013/05/base#hasInboundPort") {
							return 'IN';
						} elseif ($relationNode->getAttribute("type") == 
								"http://schemas.ogf.org/nml/2013/05/base#hasOutboundPort") {
							return 'OUT';
						}
					}
				}
			}
		}
		return null;
	}

	function parseProviderData() {
		$xmlns = "http://schemas.ogf.org/nsi/2014/02/discovery/nsa";
		$tagName = "nsa";
		foreach ($this->xml->getElementsByTagNameNS($xmlns, $tagName)
				as $nsaNode) {
			$idString = $nsaNode->getAttribute('id');
			$id = explode(":", $idString);
			$domainName = $id[3];
			$nameNode = $this->xpath->query(".//name", $nsaNode);
			$longitudeNode = $this->xpath->query(".//longitude", $nsaNode);
			$latitudeNode = $this->xpath->query(".//latitude", $nsaNode);
			$interfaceNodes = $this->xpath->query(".//interface", $nsaNode);
			$featureNodes = $this->xpath->query(".//feature", $nsaNode);
			$type = null;
			$lat = null;
			$lng = null;
			
			if ($nameNode->item(0)) {
				$name = $nameNode->item(0)->nodeValue;
			} else {
				$name = $domainName;
			}
			
			foreach ($featureNodes as $featureNode) {
				$providerType = $featureNode->getAttribute('type');
				
				if ($type != "AGG" && "vnd.ogf.nsi.cs.v2.role.uPA" == $providerType) {
					$type = "UPA";
				} elseif ("vnd.ogf.nsi.cs.v2.role.aggregator" == $providerType) {
					$type = "AGG";
				} 
			}
			
			foreach ($interfaceNodes as $interfaceNode) {
				$serviceType = $this->xpath->query(".//type", $interfaceNode);
				$serviceUrl = $this->xpath->query(".//href", $interfaceNode);
				
				$service = [];
				if ($serviceType->item(0)) {
					$validService = true;
					switch ($serviceType->item(0)->nodeValue) {
						case "application/vnd.ogf.nsi.cs.v2.provider+soap":
						case "application/vnd.org.ogf.nsi.cs.v2+soap":
							$service["type"] = "NSI_CSP_2_0"; break;
						case "application/vnd.ogf.nsi.topology.v2+xml":
							$service["type"] = "NSI_TD_2_0"; break;						
						case "application/nmwg.topology+xml":
							$service["type"] = "NMWG_TD_1_0"; break;
						case "application/vnd.ogf.nsi.dds.v1+xml":
							$service["type"] = "NSI_DS_1_0"; break;
						default: 
							$service["type"] = $serviceType->item(0)->nodeValue;
							$this->errors["Unknown Service"][$serviceUrl->item(0)->nodeValue] = $serviceType->item(0)->nodeValue;
							$validService = false;
					}
					if ($validService) {
						$service["url"] = trim($serviceUrl->item(0)->nodeValue);
						$this->addProviderService($domainName, $idString, $service);
					}
				}
			}
			
			if($longitudeNode->item(0)) {
				$lat = $latitudeNode->item(0)->nodeValue;
				$lng = $longitudeNode->item(0)->nodeValue;
			}
			
			$this->addProviderData($domainName, $idString, $type, $name, $lat, $lng);
		}
	}
}
	
	?>