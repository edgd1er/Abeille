<?php

    include_once __DIR__.'/AbeilleCmdProcess.class.php';

    class AbeilleCmdPrepare extends AbeilleCmdProcess {
        // function found at https://secure.php.net/manual/fr/function.parse-str.php
        // which translate a X=x&Y=y&Z=z en array X=>x Y=>y Z=>z
        function proper_parse_str($str) {
            # result array
            $arr = array();

            # split on outer delimiter
            $pairs = explode('&', $str);

            # loop through each pair
            foreach ($pairs as $i) {
                # split into name and value
                list($name,$value) = explode('=', $i, 2);

                # if name already exists
                if(isset($arr[$name])) {
                    # stick multiple values into an array
                    if(is_array($arr[$name]) ) {
                        $arr[$name][] = $value;
                    }
                    else {
                        $arr[$name] = array($arr[$name], $value);
                    }
                }
                # otherwise, simply stick it in a scalar
                else {
                    $arr[$name] = $value;
                }
            }

            # return result array
            return $arr;
        }

        /**
         * procmsg()
         *
         * process commands received in the queue to be send to zigate
         * Do the mapping (L2: Level 1) with basic command and send it to processCmd() for processing at cmd level (L1: Level 1)
         *
         * @param message   message in the queue which include the cmd and data to be sent
         *
         * @return none
         *
         */
        function procmsg($message) {

            $this->deamonlog("debug", "  L2 - procmsg(".json_encode($message).")", $this->debug['procmsg']);

            $topic      = $message->topic;
            $msg        = $message->payload;
            $priority   = $message->priority;

            if (sizeof(explode('/', $topic)) != 3) {
                $this->deamonlog("error", "procmsg(): Mauvais format de message reçu.");
                return ;
            }

            list ($type, $address, $action) = explode('/', $topic);

            if (preg_match("(^TempoCmd)", $type)) {
                $this->deamonlog("debug", "  Ajoutons le message a queue Tempo.", $this->debug['procmsg2']);
                $this->addTempoCmdAbeille($topic, $msg, $priority);
                return;
            }

            if (!preg_match("(^Cmd)", $type)) {
                $this->deamonlog('warning', '  Msg Received: Type: {'.$type.'} <> Cmdxxxxx donc ce n est pas pour moi, no action.');
                return;
            }

            $dest = str_replace('Cmd', '',  $type); // Remove 'Cmd' prefix

            $this->deamonlog("debug", '  Msg Received: Topic: {'.$topic.'} => '.$msg, $this->debug['procmsg3']);
            $this->deamonlog("debug", '  (ln: '.__LINE__.') - Type: '.$type.' Address: '.$address.' avec Action: '.$action, $this->debug['procmsg3']);

            $convertOnOff = array(
                "On"      => "01",
                "Off"     => "00",
                "Toggle"  => "02",
            );

            $fields = preg_split("/[=&]+/", $msg);
            if (count($fields) > 1) {
                $parameters = $this->proper_parse_str($msg);
            }

            switch ($action) {

            /*
             * Zigbee ZDO commands
             */

            case "managementNetworkUpdateRequest":
                $Command = array(
                    "managementNetworkUpdateRequest" => "1",
                    "priority" => $priority,
                    "dest" => $dest,
                    "address" => $address,
                );
                break;
            case "getRoutingTable":
            case "Mgmt_Rtg_req":
                $Command = array(
                    "getRoutingTable" => "1",
                    "priority" => $priority,
                    "dest" => $dest,
                    "address" => $address,
                );
                break;
            case "getBindingTable":
                $Command = array(
                    "getBindingTable" => "1",
                    "priority" => $priority,
                    "dest" => $dest,
                    "address" => $address,
                );
                break;
            case "Management_LQI_request": // Mgmt_Lqi_req: OBSOLETE: Use 'getNeighborTable' instead
                $keywords = preg_split("/[=&]+/", $msg);
                $Command = array(
                    "Management_LQI_request" => "1",
                    "priority" => $priority,
                    "dest" => $dest,
                    "address" => $keywords[1],
                    "StartIndex" => $keywords[3],
                );
                break;
            case "getNeighborTable": // Mgmt_Lqi_req
                $keywords = preg_split("/[=&]+/", $msg);
                $Command = array(
                    "getNeighborTable" => "1",
                    "priority" => $priority,
                    "dest" => $dest,
                    "addr" => $address,
                    "startIndex" => $keywords[1]
                );
                break;
            case "IEEE_Address_request":
                $keywords = preg_split("/[=&]+/", $msg);
                $Command = array(
                    "IEEE_Address_request"      => "1",
                    "priority"                  => $priority,
                    "dest"                      => $dest,
                    "address"                   => $address,
                    "shortAddress"              => $keywords[1],
                );
                break;
            case "bind0030":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }
                $Command = array(
                    "bind0030" => "1",
                    "priority" => $priority,
                    "dest" => $dest,
                    "addr" => $parameters['addr'], // IEEE source addr
                    "ep" => $parameters['ep'], // Source EP
                    "clustId" => $parameters['clustId'], // Source cluster
                    "destAddr" => $parameters['destAddr'], // Dest IEEE addr or group addr
                );
                if (isset($parameters['destEp'])) // Not required if group address
                    $Command['destEp'] = $parameters['destEp']; // Dest EP if IEEE addr
                break;
            case "bindShort":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }
                $Command = array(
                                    "bindShort"                => "1",
                                    "priority"                 => $priority,
                                    "dest"                     => $dest,
                                    "address"                  => $address,
                                    "targetExtendedAddress"    => $parameters['targetExtendedAddress'],
                                    "targetEndpoint"           => $parameters['targetEndpoint'],
                                    "clusterID"                => $parameters['ClusterId'],
                                    "destinationAddress"       => $parameters['reportToAddress'],
                                    "destinationEndpoint"      => "01",
                                    );
                break;
            case "BindToGroup":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }
                $Command = array(
                                    "BindToGroup"              => "1",
                                    "priority"                 => $priority,
                                    "dest"                     => $dest,
                                    "address"                  => $address,
                                    "targetExtendedAddress"    => $parameters['targetExtendedAddress'],
                                    "targetEndpoint"           => $parameters['targetEndpoint'],
                                    "clusterID"                => $parameters['clusterID'],
                                    "reportToGroup"            => $parameters['reportToGroup'],
                                    "destinationEndpoint"      => "01",
                                    );
                break;

            /*
             * Zigbee ZCL commands
             */

            case "ReadAttributeRequest": // OBSOLETE: Use "readAttribute' instead
                $keywords = preg_split("/[=&]+/", $msg);
                if (count($keywords) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }
                if (!isset($parameters['Proprio']) ) { $parameters['Proprio'] = "0000"; }
                $Command = array(
                                    "ReadAttributeRequest"     => "1",
                                    "priority"                 => $priority,
                                    "dest"                     => $dest,
                                    "address"                  => $address,
                                    "clusterId"                => $parameters['clusterId'],   // Don't change the speeling here but in the template
                                    "attributeId"              => $parameters['attributeId'],
                                    "EP"                       => $parameters['EP'],
                                    "Proprio"                  => $parameters['Proprio'],
                                    );
                break;

            case "readAttribute":
            case "readAttributeRequest": // OBSOLETE: Use "readAttribute' instead
                    $keywords = preg_split("/[=&]+/", $msg);
                if (count($keywords) > 1) {
                    $params = $this->proper_parse_str($msg);
                }
                if (!isset($params['ep']) || !isset($params['clustId']) || !isset($params['attrId'])) {
                    $this->deamonlog('debug', '  readAttributeRequest ERROR: missing minimal infos');
                    return;
                }
                $Command = array(
                                "readAttributeRequest"      => "1",
                                "priority"                  => $priority,
                                "dest"                      => $dest,
                                "addr"                      => $address,
                                "ep"                        => $params['ep'],
                                "clustId"                   => $params['clustId'],
                                "attrId"                    => $params['attrId']
                                );
                break;

            case "ReadAttributeRequestHue": // OBSOLETE: Use "readAttribute' instead
                $keywords = preg_split("/[=&]+/", $msg);
                $Command = array(
                                    "ReadAttributeRequestHue" => "1",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address" => $address,
                                    "clusterId" => $keywords[1],
                                    "attributeId" => $keywords[3],
                                    );
                break;

            case "ReadAttributeRequestOSRAM": // OBSOLETE: Use "readAttribute' instead
                $keywords = preg_split("/[=&]+/", $msg);
                $Command = array(
                                    "ReadAttributeRequestOSRAM" => "1",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address" => $address,
                                    "clusterId" => $keywords[1],
                                    "attributeId" => $keywords[3],
                                    );
                break;

            case "ReadAttributeRequestMulti":
                $keywords = preg_split("/[=&]+/", $msg);
                if (count($keywords) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }
                if (!isset($parameters['Proprio']) ) { $parameters['Proprio'] = "0000"; }
                $Command = array(
                                    "ReadAttributeRequestMulti"     => "1",
                                    "priority"                 => $priority,
                                    "dest"                     => $dest,
                                    "address"                  => $address,
                                    "EP"                       => $parameters['EP'],
                                    "clusterId"                => $parameters['clusterId'],   // Don't change the speeling here but in the template
                                    "Proprio"                  => $parameters['Proprio'],
                                    "attributeId"              => $parameters['attributeId'],
                                    );
                break;

            case "setReportSpirit": // OBSOLETE: Use 'configureReporting' instead
            case "setReport": // OBSOLETE: Use 'configureReporting' instead
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }

                if (!isset($parameters['targetEndpoint']) )
                    $parameters['targetEndpoint'] = "01";
                if (!isset($parameters['MinInterval']) )
                    $parameters['MinInterval'] = "0000";
                else
                    $parameters['MinInterval'] = str_pad(dechex($parameters['MinInterval']), 4, 0, STR_PAD_LEFT);

                $Command = array(
                                "setReport"         => "1",
                                "priority"          => $priority,
                                "dest"              => $dest,
                                "address"           => $address,
                                "targetEndpoint"    => $parameters['targetEndpoint'],
                                "ClusterId"         => $parameters['ClusterId'],
                                "AttributeId"       => $parameters['AttributeId'],
                                "MinInterval"       => $parameters['MinInterval']
                                );
                if (isset($parameters["AttributeDirection"]))      { $Command['AttributeDirection']    = $parameters['AttributeDirection']; }
                if (isset($parameters["AttributeType"]))           { $Command['AttributeType']         = $parameters['AttributeType']; }
                if (isset($parameters["MaxInterval"]))             { $Command['MaxInterval']           = str_pad(dechex($parameters['MaxInterval']),   4,0,STR_PAD_LEFT); }
                if (isset($parameters["Change"]))                  { $Command['Change']                = str_pad(dechex($parameters['Change']),        2,0,STR_PAD_LEFT); }
                if (isset($parameters["Timeout"]))                 { $Command['Timeout']               = str_pad(dechex($parameters['Timeout']),       4,0,STR_PAD_LEFT); }
                break;

            case "setReportRaw": // OBSOLETE: Use 'configureReporting' instead
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }

                $Command = array(
                                    "setReportRaw"             => "1",
                                    "priority"                 => $priority,
                                    "dest"                     => $dest,
                                    "address"                  => $address,
                                    "targetEndpoint"           => $parameters['targetEndpoint'],
                                    "ClusterId"                => $parameters['ClusterId'],
                                    "AttributeId"              => $parameters['AttributeId'],
                                    );
                if (isset($parameters["AttributeDirection"]))      { $Command['AttributeDirection']    = $parameters['AttributeDirection']; }
                if (isset($parameters["AttributeType"]))           { $Command['AttributeType']         = $parameters['AttributeType']; }
                if (isset($parameters["MinInterval"]))             { $Command['MinInterval']           = str_pad(dechex($parameters['MinInterval']),   4,0,STR_PAD_LEFT); }
                if (isset($parameters["MaxInterval"]))             { $Command['MaxInterval']           = str_pad(dechex($parameters['MaxInterval']),   4,0,STR_PAD_LEFT); }
                if (isset($parameters["Change"]))                  { $Command['Change']                = str_pad(dechex($parameters['Change']),        2,0,STR_PAD_LEFT); }
                if (isset($parameters["Timeout"]))                 { $Command['Timeout']               = str_pad(dechex($parameters['Timeout']),       4,0,STR_PAD_LEFT); }
                break;

            case "configureReporting":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }

                /* Call example:
                    configureReporting&ep=EE&clustId=XXXX&attrType=TT&attrId=YYYY&minInterval=ZZZZ&maxInterval=CCCC
                    'minInterval' & 'maxInterval' are supposed to be decimal numbers. */

                if (!isset($parameters['ep']) )
                    $parameters['ep'] = "01";
                if (!isset($parameters['minInterval']) )
                    $parameters['minInterval'] = "0000";
                else
                    $parameters['minInterval'] = str_pad(dechex($parameters['minInterval']), 4, 0, STR_PAD_LEFT);
                if (!isset($parameters["maxInterval"]) || ($parameters["maxInterval"] == "#MAXINTERVAL#"))
                    $parameters['maxInterval'] = "0000";
                else
                    $parameters["maxInterval"] = str_pad(dechex($parameters['maxInterval']), 4, 0, STR_PAD_LEFT);

                $Command = array(
                    "configureReporting"    => "1",
                    "priority"              => $priority,
                    "dest"                  => $dest,
                    "addr"                  => $address,
                    "ep"                    => $parameters['ep'],
                    "clustId"               => $parameters['clustId'],
                    "attrId"                => $parameters['attrId'],
                    "minInterval"           => $parameters['minInterval'],
                    "maxInterval"           => $parameters['maxInterval']
                );
                if (isset($parameters['attrType']) )
                    $Command["attrType"] = $parameters['attrType']; // Auto-detected if not filled
                break;

            case "readReportingConfig":
                $Command = array(
                    "readReportingConfig"    => "1",
                    "priority"              => $priority,
                    "dest"                  => $dest,
                    "addr"                  => $parameters['addr'],
                    "clustId"               => $parameters['clustId'],
                    "attrId"                => $parameters['attrId'],
                    );
                break;

            // case "discoverCommandsReceived":
            //     $Command = array(
            //                         "discoverCommandsReceived"    => "1",
            //                         "priority"              => $priority,
            //                         "dest"                  => $dest,
            //                         "addr"                  => $address,
            //                         "ep"                    => isset($parameters['ep']) ? $parameters['ep']: null,
            //                         "clustId"               => isset($parameters['clustId']) ? $parameters['clustId']: null,
            //                         "startId"               => isset($parameters['startId']) ? $parameters['startId']: null,
            //                         "max"                   => isset($parameters['max']) ? $parameters['max']: null,
            //                         );
            //     break;

            // case "discoverAttributes":
            //     $fields = preg_split("/[=&]+/", $msg);
            //         if (count($fields) > 1) {
            //             $parameters = $this->proper_parse_str($msg);
            //         }

            //     $Command = array(
            //         "discoverAttributes"    => "1",
            //         "priority"              => $priority,
            //         "dest"                  => $dest,
            //         "addr"                  => $address,
            //         "ep"                    => $parameters['ep'],
            //         "clustId"               => $parameters['clustId'],
            //         "dir"                   => isset($parameters['dir']) ? $parameters['dir'] : "00",
            //         "startAttrId"           => isset($parameters['startAttrId']) ? $parameters['startAttrId'] : "0000",
            //         "maxAttrId"             => isset($parameters['maxAttrId']) ? $parameters['maxAttrId'] : "FF",
            //     );
            //     break;

            // case "discoverAttributesExt": // Discover Attributes Extended: WORK ONGOING
            //     $fields = preg_split("/[=&]+/", $msg);
            //         if (count($fields) > 1) {
            //             $parameters = $this->proper_parse_str($msg);
            //         }

            //     $Command = array(
            //         "discoverAttributesExt"    => "1",
            //         "priority"              => $priority,
            //         "dest"                  => $dest,
            //         "addr"                  => $address,
            //         "ep"                    => $parameters['ep'],
            //         "clustId"               => $parameters['clustId'],
            //         "dir"                   => isset($parameters['dir']) ? $parameters['dir'] : "00",
            //         "startAttrId"           => isset($parameters['startAttrId']) ? $parameters['startAttrId'] : "0000",
            //         "maxAttrId"             => isset($parameters['maxAttrId']) ? $parameters['maxAttrId'] : "FF",
            //     );
            //     break;

            /*
             * Unsorted yet
             */

            case "AnnonceManufacturer":
                if (strlen($msg) == 2) {
                    $Command = array(
                        "ReadAttributeRequest" => "1",
                        "priority" => $priority,
                        "dest" => $dest,
                        "address" => $address,
                        "clusterId" => "0000",
                        "attributeId" => "0004",
                        "EP" => $msg,
                    );
                }
                break;
            case "Annonce":
                if (strlen($msg) == 2) {
                    $Command = array(
                        "ReadAttributeRequest" => "1",
                        "priority" => $priority,
                        "dest" => $dest,
                        "address" => $address,
                        "clusterId" => "0000",
                        "attributeId" => "0005",
                        "EP" => $msg,
                    );
                } else {
                    if ($msg == "Default") {
                        $Command = array(
                            "ReadAttributeRequest" => "1",
                            "priority" => $priority,
                            "dest" => $dest,
                            "address" => $address,
                            "clusterId" => "0000",
                            "attributeId" => "0005",
                            "EP" => "01",
                        );
                    }
                    if ($msg == "Hue") {
                        $Command = array(
                            "ReadAttributeRequestHue" => "1",
                            "priority" => $priority,
                            "dest" => $dest,
                            "address" => $address,
                            "clusterId" => "0000",
                            "attributeId" => "0005",
                            "EP" => "0B",
                        );
                    }
                    if ($msg == "OSRAM") {
                        $Command = array(
                            "ReadAttributeRequestOSRAM" => "1",
                            "priority" => $priority,
                            "dest" => $dest,
                            "address" => $address,
                            "clusterId" => "0000",
                            "attributeId" => "0005",
                            "EP" => "03",
                        );
                    }
                }
                break;
            case "AnnonceProfalux":
                if ($msg == "Default") {
                    $Command = array(
                        "ReadAttributeRequest" => "1",
                        "priority" => $priority,
                        "dest" => $dest,
                        "address" => $address,
                        "clusterId" => "0000",
                        "attributeId" => "0010",
                        "EP" => "03",
                    );
                }
                break;
            case "OnOffRaw":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                    $Command = array(
                        "onoffraw"              => "1",
                        "dest"                  => $dest,
                        "priority"              => $priority,
                        "addressMode"           => "02",
                        "address"               => $address,
                        "destinationEndpoint"   => $parameters['EP'],
                        "action"                => $convertOnOff[$parameters['Action']],
                    );
                }
                break;
            case "OnOff":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                    $Command = array(
                        "onoff"                 => "1",
                        "dest"                  => $dest,
                        "priority"              => $priority,
                        "addressMode"           => "02",
                        "address"               => $address,
                        "destinationEndpoint"   => $parameters['EP'],
                        "action"                => $convertOnOff[$parameters['Action']],
                    );
                }
                else {
                    $Command = array(
                        "onoff"                 => "1",
                        "dest"                  => $dest,
                        "priority"              => $priority,
                        "addressMode"           => "02",
                        "address"               => $address,
                        "destinationEndpoint"   => "01",
                        "action"                => $convertOnOff[$msg],
                    );
                }
                break;
            case "OnOff2":
                $Command = array(
                    "onoff"                 => "1",
                    "dest"                  => $dest,
                    "priority"              => $priority,
                    "addressMode"           => "02",
                    "address"               => $address,
                    "destinationEndpoint"   => "02",
                    "action"                => $convertOnOff[$msg],
                );
                break;
            case "OnOff3":
            case "OnOffOSRAM":
                $Command = array(
                    "onoff"                 => "1",
                    "dest"                  => $dest,
                    "priority"              => $priority,
                    "addressMode"           => "02",
                    "address"               => $address,
                    "destinationEndpoint"   => "03",
                    "action"                => $convertOnOff[$msg],
                );
                break;
            case "OnOff4":
                $Command = array(
                    "onoff"                 => "1",
                    "dest"                  => $dest,
                    "priority"              => $priority,
                    "addressMode"           => "02",
                    "address"               => $address,
                    "destinationEndpoint"   => "04",
                    "action"                => $convertOnOff[$msg],
                );
                break;
            case "OnOffHue":
                $Command = array(
                    "onoff"                 => "1",
                    "dest"                  => $dest,
                    "priority"              => $priority,
                    "addressMode"           => "02",
                    "address"               => $address,
                    "destinationEndpoint"   => "0B",
                    "action"                => $convertOnOff[$msg],
                );
                break;
            case "commandLegrand":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }

                $Command = array(
                    "commandLegrand" => "1",
                    "addressMode" => "02",
                    "priority" => $priority,
                    "dest" => $dest,
                    "address" => $address,
                    "destinationEndpoint" => $parameters['EP'],
                    "Mode" => $parameters['Mode'],
                );
                break;
            case "UpGroup":
                $Command = array(
                    "UpGroup" => "1",
                    "addressMode" => "01",
                    "priority" => $priority,
                    "dest" => $dest,
                    "address" => $address,
                    "destinationEndpoint" => "01", // Set but not send on radio
                    "step" => $msg,
                );
                break;
            case "DownGroup":
                $Command = array(
                    "DownGroup" => "1",
                    "addressMode" => "01",
                    "priority" => $priority,
                    "dest" => $dest,
                    "address" => $address,
                    "destinationEndpoint" => "01", // Set but not send on radio
                    "step" => $msg,
                );
                break;
            case "OnOffGroup":
                if ($msg == "On") {
                    $actionId = "01";
                } elseif ($msg == "Off") {
                    $actionId = "00";
                } elseif ($msg == "Toggle") {
                    $actionId = "02";
                }
                $Command = array(
                    "onoff"                 => "1",
                    "addressMode"           => "01",
                    "priority"              => $priority,
                    "dest"                  => $dest,
                    "address"               => $address,
                    "destinationEndpoint"   => "01", // Set but not send on radio
                    "action"                => $actionId,
                );
                break;
            case "OnOffGroupBroadcast":
                if ($msg == "On") {
                    $actionId = "01";
                } elseif ($msg == "Off") {
                    $actionId = "00";
                } elseif ($msg == "Toggle") {
                    $actionId = "02";
                }
                $Command = array(
                    "onoff"                 => "1",
                    "addressMode"           => "04",
                    "priority"              => $priority,
                    "dest"                  => $dest,
                    "address"               => $address,
                    "destinationEndpoint"   => "01", // Set but not send on radio
                    "action"                => $actionId,
                );
                break;
            case "OnOffGroupTimed":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }

                if ($parameters['action'] == "On") {
                    $actionId = "01";
                } elseif ($parameters['action'] == "Off") {
                    $actionId = "00";
                }

                $Command = array(
                                "OnOffTimed"           => "1",
                                "addressMode"          => "01",
                                "priority"             => $priority,
                                "dest"                 => $dest,
                                "address"              => $address,
                                "destinationEndpoint"  => "01", // Set but not send on radio
                                "action"               => $actionId,
                                "onTime"               => $parameters['onTime'],
                                "offWaitTime"          => $parameters['offWaitTime'],
                            );
                break;
            case "WriteAttributeRequest":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }
                $Command = array(
                                "WriteAttributeRequest" => "1",
                                "priority"          => $priority,
                                "dest"              => $dest,
                                "address"           => $address,

                                "Proprio"           => $parameters['Proprio'],
                                "clusterId"         => $parameters['clusterId'],
                                "attributeId"       => $parameters['attributeId'],
                                "attributeType"     => $parameters['attributeType'],
                                "value"             => $parameters['value'],
                            );
                break;
            case "WriteAttributeRequestVibration":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }
                $Command = array(
                                    "WriteAttributeRequestVibration" => "1",
                                    "priority"         => $priority,
                                    "dest"             => $dest,
                                    "address"          => $address,

                                    "Proprio"          => $parameters['Proprio'],
                                    "clusterId"        => $parameters['clusterId'],
                                    "attributeId"      => $parameters['attributeId'],
                                    "attributeType"    => $parameters['attributeType'],
                                    "value"            => $parameters['value'],
                                    "repeat"           => $parameters['repeat'],

                                    );
                break;
            case "WriteAttributeRequestHostFlag":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }
                $consigne = $parameters['value'];
                $consigneHex = $consigne[4].$consigne[5].$consigne[2].$consigne[3].$consigne[0].$consigne[1];
                $Command = array(
                                    "WriteAttributeRequest" => "1",
                                    "priority"         => $priority,
                                    "dest"             => $dest,
                                    "address"          => $address,

                                    "Proprio"          => $parameters['Proprio'],
                                    "clusterId"        => $parameters['clusterId'],
                                    "attributeId"      => $parameters['attributeId'],
                                    "attributeType"    => $parameters['attributeType'],
                                    "value"            => $consigneHex,
                                    );
                break;
            case "WriteAttributeRequestTemperatureSpiritConsigne":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }
                $consigne = sprintf("%04X", $parameters['value']*100 );
                $consigneHex = $consigne[2].$consigne[3].$consigne[0].$consigne[1];
                $Command = array(
                                    "WriteAttributeRequest"    => "1",
                                    "priority"                 => $priority,
                                    "dest"                     => $dest,
                                    "address"                  => $address,
                                    "Proprio"                  => $parameters['Proprio'],
                                    "clusterId"                => $parameters['clusterId'],
                                    "attributeId"              => $parameters['attributeId'],
                                    "attributeType"            => $parameters['attributeType'],
                                    "value"                    => $consigneHex,

                                    );
                break;
            case "WriteAttributeRequestValveSpiritConsigne":
            case "WriteAttributeRequestTrvSpiritMode":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }
                $consigne = sprintf("%02X", $parameters['value'] );
                $consigneHex = $consigne;
                $Command = array(
                                    "WriteAttributeRequest" => "1",
                                    "priority"         => $priority,
                                    "dest"             => $dest,
                                    "address"          => $address,
                                    "Proprio"          => $parameters['Proprio'],
                                    "clusterId"        => $parameters['clusterId'],
                                    "attributeId"      => $parameters['attributeId'],
                                    "attributeType"    => $parameters['attributeType'],
                                    "value"            => $consigneHex,
                                    );
                break;
            case "WriteAttributeRequestGeneric":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }
                $valuePrepared = $parameters['value'];
                // Example: set Temperature Danfoss Radiator Head
                if ($parameters['attributeType'] = '29' )  {
                    $valuePrepared = sprintf("%04X", $parameters['value']*100 );
                    $valuePrepared = $valuePrepared[2] . $valuePrepared[3] . $valuePrepared[0] . $valuePrepared[1] ;
                }
                if ($parameters['attributeType'] = '30' )  {
                    $valuePrepared = sprintf("%02X", $parameters['value'] );
                }
                $Command = array(
                                    "WriteAttributeRequestGeneric" => "1",
                                    "priority"         => $priority,
                                    "dest"             => $dest,
                                    "address"          => $address,
                                    "EP"               => $parameters['EP'],
                                    "Proprio"          => $parameters['Proprio'],
                                    "clusterId"        => $parameters['clusterId'],
                                    "attributeId"      => $parameters['attributeId'],
                                    "attributeType"    => $parameters['attributeType'],
                                    "value"            => $valuePrepared,
                                    );
                break;
            case "WriteAttributeRequestActivateDimmer":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }
                $Command = array(
                                    "WriteAttributeRequestActivateDimmer" => "1",
                                    "priority"         => $priority,
                                    "dest"             => $dest,
                                    "address"          => $address,
                                    "clusterId"        => $parameters['clusterId'],
                                    "attributeId"      => $parameters['attributeId'],
                                    "attributeType"    => $parameters['attributeType'],
                                    "value"            => $parameters['value'],
                                    );
                break;

            case "setLevel":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                    $Command = array(
                                    "setLevel"             => "1",
                                    "addressMode"          => "02",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address"              => $address,
                                    "destinationEndpoint"  => $parameters['EP'],
                                    "Level"                => intval($parameters['Level'] * 255 / 100),
                                    "duration"             => $parameters['duration'],
                                );
                }
                break;
            case "setLevelRaw":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                    $Command = array(
                                        "setLevel"             => "1",
                                        "addressMode"          => "02",
                                        "priority" => $priority,
                                        "dest" => $dest,
                                        "address"              => $address,
                                        "destinationEndpoint"  => $parameters['EP'],
                                        "Level"                => $parameters['Level'],
                                        "duration"             => $parameters['duration'],
                                        );
                }
                break;
            case "setLevelOSRAM":
                $keywords = preg_split("/[=&]+/", $msg);
                $Command = array(
                                    "setLevel" => "1",
                                    "addressMode" => "02",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address" => $address,
                                    "destinationEndpoint" => "03",
                                    "Level" => intval($keywords[1] * 255 / 100),
                                    "duration" => $keywords[3],
                                    );
                break;
            case "setLevelVolet":
                $eqLogic = eqLogic::byLogicalId($dest."/".$address, "Abeille" );
                $a = $eqLogic->getConfiguration('paramA', 0);
                $b = $eqLogic->getConfiguration('paramB', 1);
                $c = $eqLogic->getConfiguration('paramC', 0);

                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }
                $levelSlider = $parameters['Level'];                // Valeur entre 0 et 100
                $levelSliderPourcent = $levelSlider/100;            // Valeur entre 0 et 1
                $levelPourcent = $a * $levelSliderPourcent * $levelSliderPourcent + $b * $levelSliderPourcent + $c;
                $level = $levelPourcent * 255;
                $level = min(max(round($level), 0), 255);
                $Command = array(
                    "setLevel" => "1",
                    "addressMode" => "02",
                    "priority" => $priority,
                    "dest" => $dest,
                    "address" => $address,
                    "destinationEndpoint" => "01",
                    "Level" => $level,
                    "duration" => $parameters['duration'],
                );
                break;
            case "moveToLiftAndTiltBSO":
                    $fields = preg_split("/[=&]+/", $msg);
                    if (count($fields) > 1) {
                        $parameters = $this->proper_parse_str($msg);
                    }
                    $Command = array(
                                    "moveToLiftAndTiltBSO" => "1",
                                    "addressMode" => "02",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address" => $address,
                                    "destinationEndpoint" => "01",
                                    "lift"        => $parameters['lift'],
                                    "inclinaison" => $parameters['inclinaison'],        // Valeur entre 0 et 90
                                    "duration"    => $parameters['duration'],              // FFFF to have max speed of tilt
                                    );
                break;
            case "setLevelStop":
                $keywords = preg_split("/[=&]+/", $msg);
                $Command = array(
                                    "setLevelStop" => "1",
                                    "addressMode" => "02",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address" => $address,
                                    "sourceEndpoint" => "01",
                                    "destinationEndpoint" => "01",
                                    );
                break;
            case "setLevelStopHue":
                $keywords = preg_split("/[=&]+/", $msg);
                $Command = array(
                                    "setLevelStop" => "1",
                                    "addressMode" => "02",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address" => $address,
                                    "sourceEndpoint" => "01",
                                    "destinationEndpoint" => "0B",
                                    );
                break;
            case "setLevelHue":
                $keywords = preg_split("/[=&]+/", $msg);
                $Command = array(
                                    "setLevel" => "1",
                                    "addressMode" => "02",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address" => $address,
                                    "destinationEndpoint" => "0B",
                                    "Level" => intval($keywords[1] * 255 / 100),
                                    "duration" => $keywords[3],
                                    );
                break;
            case "setLevelGroup":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }
                $Command = array(
                                    "setLevel"             => "1",
                                    "addressMode"          => "01",
                                    "priority"             => $priority,
                                    "dest"                 => $dest,
                                    "address"              => $address,
                                    "destinationEndpoint"  => "01",
                                    "Level"                => intval($parameters['Level'] * 255 / 100),
                                    "duration"             => $parameters['duration'],
                                    );
                break;
            case "setColour":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                    $Command = array(
                                        "setColour"            => "1",
                                        "addressMode"          => "02",
                                        "priority" => $priority,
                                        "dest" => $dest,
                                        "address"              => $address,
                                        "X"                    => $parameters['X'],
                                        "Y"                    => $parameters['Y'],
                                        "destinationEndPoint"  => $parameters['EP'],
                                        "duration"             => $parameters['duration'],
                                        );
                }
                break;
            case "setColourGroup":
                    $fields = preg_split("/[=&]+/", $msg);
                    if (count($fields) > 1) {
                        $parameters = $this->proper_parse_str($msg);
                        $Command = array(
                                            "setColour"            => "1",
                                            "addressMode"          => "01",
                                            "priority"             => $priority,
                                            "dest"                 => $dest,
                                            "address"              => $address,
                                            "X"                    => $parameters['X'],
                                            "Y"                    => $parameters['Y'],
                                            "destinationEndPoint"  => "01", // not needed as group
                                            );
                    }
                    break;
            case "setColourRGB":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                    if (strlen($parameters['color']) == 7) {
                        $parameters['color'] = substr($parameters['color'], 1);
                    }
                }

                // Si message vient de Abeille alors le parametre est: RRVVBB
                // Si le message vient de Homebridge: {"color":"#00FF11"}, j'extrais la partie interessante.
                $rouge = hexdec(substr($parameters['color'],0,2));
                $vert  = hexdec(substr($parameters['color'],2,2));
                $bleu  = hexdec(substr($parameters['color'],4,2));

                $this->publishMosquittoAbeille(queueKeyCmdToAbeille, 'Abeille/'.$address.'/colorRouge', $rouge*100/255      );
                $this->publishMosquittoAbeille(queueKeyCmdToAbeille, 'Abeille/'.$address.'/colorVert',  $vert*100/255       );
                $this->publishMosquittoAbeille(queueKeyCmdToAbeille, 'Abeille/'.$address.'/colorBleu',  $bleu*100/255       );
                $this->publishMosquittoAbeille(queueKeyCmdToAbeille, 'Abeille/'.$address.'/ColourRGB',  $parameters['color']);

                $Command = array(
                                    "setColourRGB" => "1",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address" => $address,
                                    "R" => $rouge,
                                    "G" => $vert,
                                    "B" => $bleu,
                                    "destinationEndPoint" => $parameters['EP'],
                                    );
                break;
            case "setRouge":
                $abeille = Abeille::byLogicalId($dest.'/'.$address,'Abeille');

                $rouge  = $abeille->getCmd('info', 'colorRouge')->execCmd();
                $vert   = $abeille->getCmd('info', 'colorVert')->execCmd();
                $bleu   = $abeille->getCmd('info', 'colorBleu')->execCmd();

                if ($rouge=="" ) { $rouge = 1;   }
                if ($vert=="" )  { $vert = 1;    }
                if ($bleu=="" )  { $bleu = 1;    }

                $this->publishMosquittoAbeille(queueKeyCmdToAbeille, $dest.'/'.$address.'/colorRouge', $msg );

                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }

                $Command = array(
                                    "setColourRGB" => "1",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address" => $address,
                                    "R" => $parameters['color']/100*255,
                                    "G" => $vert/100*255,
                                    "B" => $bleu/100*255,
                                    "destinationEndPoint" => $parameters['EP'],
                                    );
                break;
            case "setVert":
                $abeille = Abeille::byLogicalId($dest.'/'.$address,'Abeille');

                $rouge  = $abeille->getCmd('info', 'colorRouge')->execCmd();
                $vert   = $abeille->getCmd('info', 'colorVert')->execCmd();
                $bleu   = $abeille->getCmd('info', 'colorBleu')->execCmd();

                if ($rouge=="" ) { $rouge = 1;   }
                if ($vert=="" )  { $vert = 1;    }
                if ($bleu=="" )  { $bleu = 1;    }

                $this->publishMosquittoAbeille(queueKeyCmdToAbeille, $dest.'/'.$address.'/colorVert', $msg );

                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }

                $Command = array(
                                    "setColourRGB" => "1",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address" => $address,
                                    "R" => $rouge/100*255,
                                    "G" => $parameters['color']/100*255,
                                    "B" => $bleu/100*255,
                                    "destinationEndPoint" => $parameters['EP'],
                                    );
                break;
            case "setBleu":
                $abeille = Abeille::byLogicalId($dest.'/'.$address,'Abeille');

                $rouge  = $abeille->getCmd('info', 'colorRouge')->execCmd();
                $vert   = $abeille->getCmd('info', 'colorVert')->execCmd();
                $bleu   = $abeille->getCmd('info', 'colorBleu')->execCmd();

                if ($rouge=="" ) { $rouge = 1;   }
                if ($vert=="" )  { $vert = 1;    }
                if ($bleu=="" )  { $bleu = 1;    }

                $this->publishMosquittoAbeile(queueKeyCmdToAbeille, $dest.'/'.$address.'/colorBleu', $msg );

                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }

                $Command = array(
                                    "setColourRGB" => "1",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address" => $address,
                                    "R" => $rouge/100*255,
                                    "G" => $vert/100*255,
                                    "B" => $parameters['color']/100*255,
                                    "destinationEndPoint" => $parameters['EP'],
                                    );
                break;
            case "setColourHue":
                $keywords = preg_split("/[=&]+/", $msg);
                $Command = array(
                                    "setColour" => "1",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address" => $address,
                                    "X" => $keywords[1],
                                    "Y" => $keywords[3],
                                    "destinationEndPoint" => "0B",
                                    );
                break;
            case "setColourOSRAM":
                $keywords = preg_split("/[=&]+/", $msg);
                $Command = array(
                                    "setColour" => "1",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address" => $address,
                                    "X" => $keywords[1],
                                    "Y" => $keywords[3],
                                    "destinationEndPoint" => "03",
                                    );
                break;
            case "setTemperature":
                // T°K   Hex sent  Dec eq
                // 2200	 01C6	   454
                // 2700	 0172	   370
                // 4000	 00FA	   250
                // De ces nombres on calcule l'equation: Y = -0,113333333 * X + 703,3333333
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }
                $Command = array(
                                    "setTemperature"       => "1",
                                    "addressMode"          => "02",
                                    "priority"             => $priority,
                                    "dest"                 => $dest,
                                    "address"              => $address,
                                    "temperature"          => sprintf("%04s", dechex(intval(1000000/$parameters['slider'])) ),
                                    "destinationEndPoint"  => $parameters['EP'],
                                    );
                break;
            case "setTemperatureGroup":
                // T°K   Hex sent  Dec eq
                // 2200     01C6       454
                // 2700     0172       370
                // 4000     00FA       250
                // De ces nombres on calcule l'equation: Y = -0,113333333 * X + 703,3333333
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }
                $Command = array(
                                    "setTemperature"       => "1",
                                    "addressMode"          => "01",
                                    "priority"             => $priority,
                                    "dest"                 => $dest,
                                    "address"              => $address,
                                    "temperature"          => sprintf("%04s", dechex(intval(1000000/$parameters['slider'])) ),
                                    "destinationEndPoint"  => $parameters['EP'],
                                    );
                break;


            case "writeAttributeRequestIAS_WD":
                $fields = preg_split("/[=&]+/", $msg);
                    if (count($fields) > 1) {
                        $parameters = $this->proper_parse_str($msg);
                    }

                $Command = array(
                                    "writeAttributeRequestIAS_WD"     => "1",
                                    "priority"                        => $priority,
                                    "dest"                            => $dest,
                                    "address"                         => $address,
                                    "mode"                            => $parameters['mode'],
                                    "duration"                        => $parameters['duration'],
                );
                break;

            /*
             * Cluster 0001/Identify support
             */

            case "identifySend":
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }
                $Command = array(
                                    "identifySend" => "1",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address" => $address,
                                    "duration" => $parameters['duration'],
                                    "DestinationEndPoint" => $parameters['EP'],
                                    );
                break;
            case "identifySendHue":
                $keywords = preg_split("/[=&]+/", $msg);
                $Command = array(
                                    "identifySend" => "1",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address" => $address,
                                    "duration" => "0010",
                                    "DestinationEndPoint" => "0B",
                                    );
                break;

            /*
             * Cluster 0004/Groups support
             */

            case "getGroupMembership":
                if (!isset($parameters['ep'])) { $parameters['ep'] = "01"; }
                $Command = array(
                    "getGroupMembership"    => "1",
                    "priority"              => $priority,
                    "dest"                  => $dest,
                    "addr"                  => $address,
                    "ep"                    => $parameters['ep'],
                );
                break;

            case "addGroup":
                if (strlen($parameters['DestinationEndPoint'])<2 ) { $parameters['DestinationEndPoint'] = "01"; }
                $Command = array(
                                    "addGroup"                 => "1",
                                    "priority"                 => $priority,
                                    "dest"                     => $dest,
                                    "address"                  => $parameters['address'],
                                    "DestinationEndPoint"      => $parameters['DestinationEndPoint'],
                                    "groupAddress"             => $parameters['groupAddress'],
                                    );
                break;
            // case "addGroup":
            //     $fields = preg_split("/[=&]+/", $msg);
            //     if (count($fields) > 1) {
            //         $parameters = $this->proper_parse_str($msg);
            //     }
            //     if (strlen($parameters['DestinationEndPoint'])<2 ) { $parameters['DestinationEndPoint'] = "01"; }
            //     $Command = array(
            //                         "addGroup"                 => "1",
            //                         "priority"                 => $priority,
            //                         "dest"                     => $dest,
            //                         "address"                  => $address,
            //                         "DestinationEndPoint"      => $parameters['DestinationEndPoint'],
            //                         "groupAddress"             => $parameters['groupAddress'],
            //                         );
            //     break;

            case "removeGroup":
                // if ($parameters['address']=="Ruche" ) { $parameters['address'] = "0000"; }
                if (strlen($parameters['DestinationEndPoint'])<2 ) { $parameters['DestinationEndPoint'] = "01"; }
                $Command = array(
                                    "removeGroup"              => "1",
                                    "priority"                 => $priority,
                                    "dest"                     => $dest,
                                    "address"                  => $parameters['address'],
                                    "DestinationEndPoint"      => $parameters['DestinationEndPoint'],
                                    "groupAddress"             => $parameters['groupAddress'],
                                    );
                break;

            /*
             * Cluster 0005/Scenes support
             */

            case "viewScene":
                if (!isset($parameters['DestinationEndPoint']) ) { $parameters['DestinationEndPoint'] = "01"; }
                $Command = array(
                                    "viewScene"                => "1",
                                    "priority"                 => $priority,
                                    "dest"                     => $dest,
                                    "address"                  => $parameters['address'],
                                    "DestinationEndPoint"      => $parameters['DestinationEndPoint'],
                                    "groupID"                  => $parameters['groupID'],
                                    "sceneID"                  => $parameters['sceneID'],
                                    );
                break;

            case "storeScene":
                if (!isset($parameters['DestinationEndPoint']) ) { $parameters['DestinationEndPoint'] = "01"; }
                $Command = array(
                                    "storeScene"               => "1",
                                    "priority"                 => $priority,
                                    "dest"                     => $dest,
                                    "address"                  => $parameters['address'],
                                    "DestinationEndPoint"      => $parameters['DestinationEndPoint'],
                                    "groupID"                  => $parameters['groupID'],
                                    "sceneID"                  => $parameters['sceneID'],
                                    );
                break;

            case "recallScene":
                if (!isset($parameters['DestinationEndPoint']) ) { $parameters['DestinationEndPoint'] = "01"; }
                $Command = array(
                                    "recallScene"              => "1",
                                    "priority"                 => $priority,
                                    "dest"                     => $dest,
                                    "address"                  => $parameters['address'],
                                    "DestinationEndPoint"      => $parameters['DestinationEndPoint'],
                                    "groupID"                  => $parameters['groupID'],
                                    "sceneID"                  => $parameters['sceneID'],
                                    );
                break;

            case "sceneGroupRecall":
                $this->deamonlog('debug', '  sceneGroupRecall msg: ' . $msg );
                $fields = preg_split("/[=&]+/", $msg);
                if (count($fields) > 1) {
                    $parameters = $this->proper_parse_str($msg);
                }

                $Command = array(
                                    "sceneGroupRecall"         => "1",
                                    "priority"                 => $priority,
                                    "dest"                     => $dest,
                                    "groupID"                  => $parameters['groupID'],
                                    "sceneID"                  =>  $parameters['sceneID'],
                                    );
                break;

            // case "sceneGroupRecall":
            //     $this->deamonlog('debug', '  sceneGroupRecall msg: ' . $msg );
            //     $fields = preg_split("/[=&]+/", $msg);
            //     if (count($fields) > 1) {
            //         $parameters = $this->proper_parse_str($msg);
            //     }

            //     $Command = array(
            //                         "sceneGroupRecall"         => "1",
            //                         "priority"                 => $priority,
            //                         "dest"                     => $dest,
            //                         "groupID"                  => $parameters['groupID'],
            //                         "sceneID"                  =>  $parameters['sceneID'],
            //                         );
            //     break;

            case "addScene":
                if (!isset($parameters['DestinationEndPoint']) ) { $parameters['DestinationEndPoint'] = "01"; }
                $Command = array(
                                    "addScene"                 => "1",
                                    "priority"                 => $priority,
                                    "dest"                     => $dest,
                                    "address"                  => $parameters['address'],
                                    "DestinationEndPoint"      => $parameters['DestinationEndPoint'],
                                    "groupID"                  => $parameters['groupID'],
                                    "sceneID"                  => $parameters['sceneID'],
                                    "sceneName"                => $parameters['sceneName'],
                                    );
                break;

            case "getSceneMembership":
                if (!isset($parameters['DestinationEndPoint']) ) { $parameters['DestinationEndPoint'] = "01"; }
                $Command = array(
                                    "getSceneMembership"       => "1",
                                    "priority"                 => $priority,
                                    "dest"                     => $dest,
                                    "address"                  => $parameters['address'],
                                    "DestinationEndPoint"      => $parameters['DestinationEndPoint'],
                                    "groupID"                  => $parameters['groupID'],
                                    );
                break;

            case "removeSceneAll":
                if (!isset($parameters['DestinationEndPoint']) ) { $parameters['DestinationEndPoint'] = "01"; }
                $Command = array(
                                    "removeSceneAll"           => "1",
                                    "priority"                 => $priority,
                                    "dest"                     => $dest,
                                    "address"                  => $parameters['address'],
                                    "DestinationEndPoint"      => $parameters['DestinationEndPoint'],
                                    "groupID"                  => $parameters['groupID'],
                                    );
                break;

            /*
             * Cluster 0102/Window covering support
             */

            case "WindowsCovering":
                $fields = preg_split("/[=&]+/", $msg);
                    if (count($fields) > 1) {
                        $parameters = $this->proper_parse_str($msg);
                    }

                $Command = array(
                                    "WindowsCovering"          => "1",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address"                  => $address,
                                    "clusterCommand"           => $parameters['clusterCommand'],
                );
                break;

            case "WindowsCoveringLevel":
                $fields = preg_split("/[=&]+/", $msg);
                    if (count($fields) > 1) {
                        $parameters = $this->proper_parse_str($msg);
                    }

                $Command = array(
                                    "WindowsCoveringLevel"     => "1",
                                    "priority"                 => $priority,
                                    "dest"                     => $dest,
                                    "address"                  => $address,
                                    "clusterCommand"           => $parameters['clusterCommand'],
                                    "liftValue"                => sprintf("%02s",dechex($parameters['liftValue'])),
                );
                break;

            case "WindowsCoveringGroup":
                $fields = preg_split("/[=&]+/", $msg);
                    if (count($fields) > 1) {
                        $parameters = $this->proper_parse_str($msg);
                    }

                $Command = array(
                                    "WindowsCoveringGroup"     => "1",
                                    "priority" => $priority,
                                    "dest" => $dest,
                                    "address"                  => $address,
                                    "clusterCommand"           => $parameters['clusterCommand'],
                );
                break;

            default:
                $this->deamonlog("debug", '  No prepare function. Forwarding cmd to AbeilleCmdProcess.');
                // $this->deamonlog("debug", '  msg='.json_encode($msg));

                /* Tcharp38 notes:
                   This part forward directly message to 'AbeilleCmdProcess'.
                   Should be the default to later remove this 'AbeilleCmdPrepare' step.
                   Incoming messages have 2 parts:
                     - Target device (ex: CmdAbeille1/FE70/discoverAttributesExt) => address + command
                     - Command params (ex: ep=01&clustId=0000&start=00)
                   During this conversion process a key point is 'addr'. Device addr is taken if 'addr' is not already
                   part of parameters.
                 */

                $Command =  array(
                    $action => $action, // Tcharp38: Legacy. To be removed at some point.
                    "name" => $action,
                    "priority" => $priority,
                    "dest" => $dest,
                );
                $keywords = preg_split("/[=&]+/", $msg);
                $addrFound = false;
                for($i = 0; $i < count($keywords); $i += 2) {
                    if (($i + 1) < count($keywords))
                        $Command[$keywords[$i]] = $keywords[$i + 1];
                    else
                        $Command[$keywords[$i]] = 1; // Special case. Ex: CmdAbeille1/0000/SetPermit -> Inclusion
                    if ($Command[$keywords[$i]] == "addr")
                        $addrFound = true;
                }
                if ($addrFound == false)
                    $Command['addr'] = $address;

                // if (count($keywords) == 1) {
                //     $Command = array(
                //                     $action => $msg,
                //                     "name" => $action,
                //                     "priority" => $priority,
                //                     "dest" => $dest,
                //                     );
                // } else if (count($keywords) == 2) { // Si une command type get http param1=value1&param2=value2
                //     $Command = array(
                //                     $action => $action,
                //                     "name" => $action,
                //                     "priority" => $priority,
                //                     "dest" => $dest,
                //                     $keywords[0] => $keywords[1],
                //                     );
                // } else if (count($keywords) == 4) {
                //     $Command = array(
                //                     $action => $action,
                //                     "name" => $action,
                //                     "priority" => $priority,
                //                     "dest" => $dest,
                //                     $keywords[0] => $keywords[1],
                //                     $keywords[2] => $keywords[3],
                //                     );
                // } else if (count($keywords) == 6) {
                //     $Command = array(
                //                     $action => $action,
                //                     "name" => $action,
                //                     "priority" => $priority,
                //                     "dest" => $dest,
                //                     $keywords[0] => $keywords[1],
                //                     $keywords[2] => $keywords[3],
                //                     $keywords[4] => $keywords[5],
                //                     );
                // } else if (count($keywords) == 8) {
                //     $Command = array(
                //                     $action => $action,
                //                     "name" => $action,
                //                     "priority" => $priority,
                //                     "dest" => $dest,
                //                     $keywords[0] => $keywords[1],
                //                     $keywords[2] => $keywords[3],
                //                     $keywords[4] => $keywords[5],
                //                     $keywords[6] => $keywords[7],
                //                     );
                // } else if (count($keywords) == 10) {
                //     $Command = array(
                //                     $action => $action,
                //                     "name" => $action,
                //                     "priority" => $priority,
                //                     "dest" => $dest,
                //                     $keywords[0] => $keywords[1],
                //                     $keywords[2] => $keywords[3],
                //                     $keywords[4] => $keywords[5],
                //                     $keywords[6] => $keywords[7],
                //                     $keywords[8] => $keywords[9],
                //                     );
                // } else if (count($keywords) == 12) {
                //     $Command = array(
                //                     $action => $action,
                //                     "name" => $action,
                //                     "priority" => $priority,
                //                     "dest" => $dest,
                //                     $keywords[0] => $keywords[1],
                //                     $keywords[2] => $keywords[3],
                //                     $keywords[4] => $keywords[5],
                //                     $keywords[6] => $keywords[7],
                //                     $keywords[8] => $keywords[9],
                //                     $keywords[10] => $keywords[11],
                //                     );
                // }
                break;
            } // switch action

            if (!isset($Command)) {
                $this->deamonlog('debug', '  WARNING: Unknown command ! (topic='.$topic.')');
            } else {
                // $this->deamonlog('debug', '  L2 - calling processCmd with Command parameters: '.json_encode($Command), $this->debug['procmsg']);
                $this->processCmd($Command);
            }

            return;
        }
    }
?>
