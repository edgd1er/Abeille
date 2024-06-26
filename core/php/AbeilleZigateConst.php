
<?php
    /*
     * Zigate specific constants
     */

    define('ZIGATE_CMD', 0); // Cmd for/from Zigate. Not transmitted over the air.

    /* Zigate messages */
    $zgMessages = array(
        "0002" => array(
            "name" => "Set zigate mode",
            "type" => ZIGATE_CMD,
        ),
        "0008" => array(
            "name" => "Set heartBeat enable/disable",
            "type" => ZIGATE_CMD,
        ),
        "0009" => array(
            "name" => "Get network status",
            "type" => ZIGATE_CMD,
        ),
        "0010" => array(
            "name" => "Get version",
            "type" => ZIGATE_CMD,
        ),
        "0011" => array(
            "name" => "Reset",
            "type" => ZIGATE_CMD,
        ),
        "0012" => array(
            "name" => "Erase persistent datas",
            "type" => ZIGATE_CMD,
        ),
        "0013" => array(
            "name" => "ZLO/ZLL 'Factory New' Reset",
            "type" => ZIGATE_CMD,
        ),
        "0014" => array(
            "name" => "Permit join",
            "type" => ZIGATE_CMD,
        ),
        "0015" => array(
            "name" => "Get devices list",
            "type" => ZIGATE_CMD,
        ),
        "0016" => array(
            "name" => "Set time server",
            "type" => ZIGATE_CMD,
        ),
        "0017" => array(
            "name" => "Get time server",
            "type" => ZIGATE_CMD,
        ),
        "0018" => array(
            "name" => "Set LED",
            "type" => ZIGATE_CMD,
        ),
        "0019" => array(
            "name" => "Set certification",
            "type" => ZIGATE_CMD,
        ),
        "0020" => array(
            "name" => "Set ext PAN id",
            "type" => ZIGATE_CMD,
        ),
        "0021" => array(
            "name" => "Set channel mask",
            "type" => ZIGATE_CMD,
        ),
        "0022" => array(
            "name" => "Set security state+key",
            "type" => ZIGATE_CMD,
        ),
        "0023" => array(
            "name" => "Set device type",
            "type" => ZIGATE_CMD,
        ),
        "0024" => array(
            "name" => "Start network",
            "type" => ZIGATE_CMD,
        ),
        "0025" => array(
            "name" => "Start network scan",
            "type" => ZIGATE_CMD,
        ),
        "0027" => array(
            "name" => "Enable Permissions Controlled Joins",
            "type" => ZIGATE_CMD,
        ),
        "0030" => array(
            "name" => "Bind 0030",
        ),
        "0040" => array(
            "name" => "Network addr request",
        ),
        "004E" => array(
            "name" => "Management LQI request",
        ),
        "0051" => array(
            "name" => "Free PDM internal address map table",
            "type" => ZIGATE_CMD,
        ),
        "0052" => array(
            "name" => "Get PDM child table size",
            "type" => ZIGATE_CMD,
        ),
        "0062" => array(
            "name" => "Get groups membership",
        ),
        "0070" => array(
            "name" => "Identify send",
        ),
        "00FA" => array(
            "name" => "Windows covering",
        ),
        "0100" => array(
            "name" => "Read attribute",
        ),
        "0110" => array(
            "name" => "?",
        ),
        "0120" => array(
            "name" => "Configure reporting",
        ),

        "0200" => array(
            "name" => "Save record request",
        ),
        "0201" => array(
            "name" => "Load record request",
        ),
        "0202" => array(
            "name" => "Delete all records",
        ),
        "0300" => array(
            "name" => "Host PDM available request", // Unused by Abeille
        ),
        "0302" => array(
            "name" => "PDM loaded", // Unused by Abeille
        ),

        "0530" => array(
            "name" => "?",
        ),
        "0806" => array(
            "name" => "Set TX power",
            "type" => ZIGATE_CMD,
        ),
        "0807" => array(
            "name" => "Get TX power",
            "type" => ZIGATE_CMD,
        ),
        "8208" => array(
            "name" => "PDM ?",
            "type" => ZIGATE_CMD,
        ),
        "8300" => array(
            "name" => "PDM ?",
            "type" => ZIGATE_CMD,
        ),


        "8001" => array(
            "name" => "Log message",
        ),
        "8002" => array(
            "name" => "Data indication",
        ),
        "8006" => array(
            "name" => "Non “Factory new” Restart",
        ),
        "8007" => array(
            "name" => "“Factory New” Restart",
        ),
        "8008" => array(
            "name" => "HeartBeat", // Zigate v2 >= 3.20: Unused by Abeille
        ),
        "8009" => array(
            "name" => "Network state response",
        ),
        "8017" => array(
            "name" => "Get Time Server Response",
        ),
        "8024" => array(
            "name" => "Network joined/formed",
        ),
        "8028" => array(
            "name" => "Authenticate response",
        ),
        "802B" => array(
            "name" => "User descriptor notify",
        ),
        "802C" => array(
            "name" => "User descriptor response",
        ),
        "8030" => array(
            "name" => "Bind response", // Unused by Abeille
        ),
        "8031" => array(
            "name" => "Unbind response", // Unused by Abeille
        ),
        "8034" => array(
            "name" => "Complex descriptor response", // Unused by Abeille
        ),
        "8035" => array(
            "name" => "PDM event code",
        ),
        "8040" => array(
            "name" => "Network address response", // Unused by Abeille
        ),
        "8041" => array(
            "name" => "IEEE address response", // Unused by Abeille
        ),
        "8042" => array(
            "name" => "Node descriptor response", // Unused by Abeille
        ),
        "8043" => array(
            "name" => "Simple descriptor response", // Unused by Abeille
        ),
        "8044" => array(
            "name" => "Power descriptor response", // Unused by Abeille
        ),
        "8045" => array(
            "name" => "Active endpoints response", // Unused by Abeille
        ),
        "8046" => array(
            "name" => "Match descriptor response", // Unused by Abeille
        ),
        "8047" => array(
            "name" => "Management leave response", // Unused by Abeille
        ),
        "8048" => array(
            "name" => "Leave indication",
        ),
        "804A" => array(
            "name" => "Network management update notify", // Unused by Abeille
        ),
        "804B" => array(
            "name" => "System server discovery response",
        ),
        "804E" => array(
            "name" => "Management LQI response", // Unused by Abeille
        ),
        "8060" => array(
            "name" => "Add group response", // Unused by Abeille
        ),
        "8061" => array(
            "name" => "View group response",
        ),
        "8062" => array(
            "name" => "Get group membership response", // Unused by Abeille
        ),
        "8063" => array(
            "name" => "Remove group response", // Unused by Abeille
        ),
        "80A0" => array(
            "name" => "Scene View", // Unused by Abeille
        ),
        "80A1" => array(
            "name" => "Add scene response", // Unused by Abeille
        ),
        "80A2" => array(
            "name" => "Remove scene response", // Unused by Abeille
        ),
        "80A3" => array(
            "name" => "SRemove All Scene", // Unused by Abeille
        ),
        "80A4" => array(
            "name" => "Store Scene Response", // Unused by Abeille
        ),
        "80A6" => array(
            "name" => "?",
        ),
        "80A7" => array(
            "name" => "?",
        ),
        "8100" => array(
            "name" => "Read individual attribute response", // Unused by Abeille
        ),
        "8101" => array(
            "name" => "Default response", // Unused by Abeille
        ),
        "8102" => array(
            "name" => "Attribute report", // Unused by Abeille
        ),
        "8110" => array(
            "name" => "Write attribute response", // Unused by Abeille
        ),
        "8120" => array(
            "name" => "Configure reporting response", // Unused by Abeille
        ),
        "8122" => array(
            "name" => "Read reporting config response", // Unused by Abeille
        ),
        "8139" => array(
            "name" => "Attribute discovery individual response",
        ),
        "8140" => array(
            "name" => "Discover attributes response", // Same as 8139 ??
        ),
        "8141" => array(
            "name" => "Discover attributes extended response", // Unused by Abeille
        ),
        "8150" => array(
            "name" => "Discover commands received individual response", // Unused by Abeille
        ),
        "8151" => array(
            "name" => "Discover commands received individual response completed", // Unused by Abeille
        ),
        "8160" => array(
            "name" => "Discover commands generated individual response", // Unused by Abeille
        ),
        "8161" => array(
            "name" => "Discover commands generated individual response completed", // Unused by Abeille
        ),
        "8200" => array(
            "name" => "Save record request response",
        ),
        "8201" => array(
            "name" => "Load record response",
        ),
        "8300" => array(
            "name" => "Host PDM available response",
        ),
        "8401" => array(
            "name" => "Zone status change notification", // Unused by Abeille
        ),
        "8501" => array(
            "name" => "OTA block request", // Unused by Abeille
        ),
        "8503" => array(
            "name" => "OTA upgrade end request", // Unused by Abeille
        ),
        "8531" => array(
            "name" => "Complex descriptor response",
        ),

        // Abeille's FW (ABxx-yyyy) specific messages
        "AB01" => array(
            "name" => "PDM dump response",
        ),
        "AB03" => array(
            "name" => "PDM restore response",
        ),
    );

    /* Returns Zigate message (array) based on given '$msgType' or [] if unknown */
    function zgGetMsg($msgType) {
        $msgType = strtoupper($msgType);
        global $zgMessages;

        if (array_key_exists($msgType, $zgMessages))
            return $zgMessages[$msgType];
        return [];
    }

    /* Returns 'true' is message is for Zigate only (not transmitted over the air) */
    function zgIsZigateOnly($msgType) {
        $msgType = strtoupper($msgType);
        global $zgMessages;

        if (array_key_exists($msgType, $zgMessages))
            $zgMsg = $zgMessages[$msgType];
        else
            $zgMsg = [];
        // WARNING: To be revisited if another type than ZIGATE_CMD is added
        return (isset($zgMsg['type']) ? true : false);
    }

    /* Returns Zigate message name based on given '$msgType' */
    function zgGetMsgName($msgType) {
        $msgType = strtoupper($msgType);

        // /* Type and name of zigate messages */
        // $zgMessages = array(
        //     "0200" => "Save record request",
        //     "0201" => "Load record request",
        //     "0202" => "Delete all records",
        //     "0300" => "Host PDM available request",
        //     "0302" => "PDM loaded",

        //     "8001" => "Log message",
        //     "8002" => "Data indication",
        //     "8006" => "Non “Factory new” Restart",
        //     "8007" => "“Factory New” Restart",
        //     "8009" => "Network state response",
        //     "8028" => "Authenticate response",
        //     "802B" => "User descriptor notify",
        //     "802C" => "User descriptor response",
        //     "8031" => "Unbind response",
        //     "8034" => "Complex descriptor response",
        //     "8035" => "PDM event code",
        //     "8042" => "Node descriptor response",
        //     "8044" => "Power descriptor response",
        //     "8046" => "Match descriptor response",
        //     "8047" => "Management leave response",
        //     "804B" => "System server discovery response",
        //     "804E" => "Management LQI response",
        //     "8061" => "View group response",
        //     "80A1" => "Add scene response",
        //     "80A2" => "Remove scene response",
        //     "8110" => "Write attribute response",
        //     "8139" => "Attribute discovery individual response",
        //     "8140" => "Configure reporting response",
        //     "8141" => "Discover attributes extended response",
        //     "8150" => "Discover commands received individual response",
        //     "8151" => "Discover commands received individual response completed",
        //     "8200" => "Save record request response",
        //     "8201" => "Load record response",
        //     "8300" => "Host PDM available response",
        //     "8401" => "Zone status change notification",
        //     "8501" => "OTA block request",
        //     "8503" => "OTA upgrade end request",
        //     "8531" => "Complex descriptor response",
        // );

        global $zgMessages;
        if (array_key_exists($msgType, $zgMessages))
            return $zgMessages[$msgType]['name'];
        return "Unknown-".$msgType;
    }

    /* Returns Zigate PDM event desc based on given '$code' */
    function zgGetPDMEvent($code) {
        $code = strtoupper($code);

        /* PDM event codes & desc.
           Returned by command 0x8035 */
       $zgPDMEvents = array(
            "00" => "WEAR_COUNT_TRIGGER_VALUE_REACHED",
            "01" => "DESCRIPTOR_SAVE_FAILED",
            "02" => "PDM_NOT_ENOUGH_SPACE",
            "03" => "LARGEST_RECORD_FULL_SAVE_NO_LONGER_POSSIBLE",
            "04" => "SEGMENT_DATA_CHECKSUM_FAIL",
            "05" => "SEGMENT_SAVE_OK",
            "06" => "EEPROM_SEGMENT_HEADER_REPAIRED",
            "07" => "SYSTEM_INTERNAL_BUFFER_WEAR_COUNT_SWAP",
            "08" => "SYSTEM_DUPLICATE_FILE_SEGMENT_DETECTED",
            "09" => "SYSTEM_ERROR",
            "0A" => "SEGMENT_PREWRITE",
            "0B" => "SEGMENT_POSTWRITE",
            "0C" => "SEQUENCE_DUPLICATE_DETECTED",
            "0D" => "SEQUENCE_VERIFY_FAIL",
            "0E" => "PDM_SMART_SAVE",
            "0F" => "PDM_FULL_SAVE"
        );

        if (array_key_exists($code, $zgPDMEvents))
            return $zgPDMEvents[$code];
        return "Unknown-".$code;
    }

    /* Returns zigate 8000 cmd status based on given '$status' value.
       Tcharp38: Still unclear how to decode such statuses. */
    function zgGet8000Status($status) {
        $status = strtoupper($status);

        $statusesTable = array (
            "00" => "Success",
            "01" => "Incorrect parameters",
            "02" => "Unhandled command",
            "03" => "Command failed",
            "04" => "Busy", // Node is carrying out a lengthy operation and is currently unable to handle the incoming command
            "05" => "Stack already started", // No new configuration accepted
            "14" => "No buffer or msg too big", // E_ZCL_ERR_ZBUFFER_FAIL
            "15" => "ZPS_EVENT_ERROR", // Indicates that an error has occurred on the local node. The nature of the error is reported through the structure ZPS_tsAfErrorEvent - see Section 7.2.2.17. JN-UG-3113 v1.5 -> En gros pas de place pour traiter le message
        );

        if (array_key_exists($status, $statusesTable))
            return $statusesTable[$status];
        return "Unknown-".$status;
    }

    // /* Returns a string corresponding to 804E bitmap info, based on given '$bitMap' value.
    //    Example: "RxONWhenIdle/Parent/PermitJoinON/Coordinator" */
    // function zgGet804EBitMap($bitMap) {
    //     $bitMap = hexdec($bitMap);
    //     $desc = "";

    //     // Bit map of attributes Described below: uint8_t
    //     //    bit 0-1 Device Type (0-Coordinator 1-Router 2-End Device)
    //     //    bit 2-3 Permit Join status (1- On 0-Off)
    //     //    bit 4-5 Relationship (0-Parent 1-Child 2-Sibling)
    //     //    bit 6-7 Rx On When Idle status (1-On 0-Off)
    //     $rx = ($bitMap >> 6) & 0x3;
    //     switch ($rx) {
    //     case 0: $desc .= "RxOFFWhenIdle"; break;
    //     case 1: $desc .= "RxONWhenIdle"; break;
    //     default; $desc .= "?"; break;
    //     }

    //     $rel = ($bitMap >> 4) & 0x3;
    //     switch ($rel) {
    //     case 0: $desc .= "/Parent"; break;
    //     case 1: $desc .= "/Child"; break;
    //     case 2: $desc .= "/Sibling"; break;
    //     default; $desc .= "/?"; break;
    //     }

    //     $pj = ($bitMap >> 2) & 0x3;
    //     switch ($pj) {
    //     case 0: $desc .= "/PermitJoinON"; break;
    //     case 1: $desc .= "/PermitJoinOFF"; break;
    //     default; $desc .= "/?"; break;
    //     }

    //     $dt = ($bitMap >> 0) & 0x3;
    //     switch ($dt) {
    //     case 0: $desc .= "/Coordinator"; break;
    //     case 1: $desc .= "/Router"; break;
    //     case 2; $desc .= "/EndDevice"; break;
    //     default; $desc .= "/?"; break;
    //     }

    //     return $desc;
    // }
?>
