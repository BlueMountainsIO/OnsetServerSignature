<?php 
/**
 *
 * OGP - Open Game Protocol - PHP-Class
 *
 * This project is designed to query any gameserver supporting the
 * Open Game Protocol. The main class is very easy to use, because
 * it only needs the serveraddress and the serverport supplied
 * to query.
 *
 * Supports OGP up to {@link http://www.open-game-protocol.org/spec/ogp_spec_v0.94.htm v0.94}
 *
 * Copyright (c) 2004 Marcel Rath
 *
 * contact: zorck@zorck.net<br>
 * web: {@link http://www.zorck.net/}
 *
 * Visit: {@link http://www.open-game-protocol.org/ www.open-game-protocol.org}
 * for more Information on the protocol itself.
 *
 *
 * Released 12.08.2004
 *
 * @link http://www.zorck.net/serverinfo/ogp/sources/0.3a/ogp.class.phps View Filesource
 * @link http://www.zorck.net/serverinfo/ogp/downloads/ Download Project
 *
 * @version v0.4a
 * @author Marcel Rath <zorck@zorck.net>
 * @copyright 2004 Marcel Rath
 * @package phpOGPQuery
 * @link http://www.open-game-protocol.org/ Open Game Protocol Documentation
 * @filesource
 *
*/
 
/**
* The main class, that does all the Serverquery and Parsing stuff.
*
* This is a hopefully well documented and
* easy to use OGP Query Class.
* For those people who don't know what OGP is,
* visit www.open-game-protocol.org
*
* @access public
* @package phpOGPQuery
* @author Marcel Rath <zorck@zorck.net>
* @copyright 2004 Marcel Rath
* @link http://www.open-game-protocol.org/ Open Game Protocol Documentation
*
*/
class OGP
{
    /**
    * OGP::$addr
    *
    * Stores the address to query
    *
    * @access public
    * @var string
    *
    */
    var $addr = "";
    /**
    * OGP::$port
    *
    * Stores the port to query
    *
    * @access public
    * @var integer
    *
    */
    var $port = 0;
    /**
    * OGP::$timeout
    *
    * The query timeout (total time)
    *
    * @access public
    * @var integer
    *
    */
    var $timeout = 0;
    /**
    * OGP::$error
    *
    * Stores the last Errormessage
    *
    * @access protected
    * @var string
    *
    */
    var $error = "";
    /**
    * OGP::$queryres
    *
    * Stores the last received Serverdata
    *
    * @access protected
    * @var string
    *
    */
    var $queryres = "";
    /**
    * OGP::$result_packets
    *
    * A Counter for incomming packets
    * Is resetted each time a new query is processed
    *
    * @access protected
    * @var integer
    *
    */
    var $result_packets = 0;
    /**
    * OGP::$total_result_packets
    *
    * A Counter for the total number of incomming packets
    *
    * @access protected
    * @var integer
    *
    */
    var $total_result_packets = 0;
    /**
    * OGP::$pakets
    *
    * Stores the received packets of the last query
    *
    * @access protected
    * @var mixed
    *
    */
    var $pakets = array();
    /**
    * OGP::$timeout_sock
    *
    * Value of the socket timeout in seconds
    *
    * To Specify a value smaller than one second, use
    * $timeout_sock instead
    *
    * @see $timeout_sock
    * @access public
    * @var integer
    *
    */
    var $timeout_sock = 3;
    /**
    * OGP::$timeout_sock_m
    *
    * Value of the socket timeout in microseconds
    * (one microseconds is 1 / 1 000 000 seconds)
    *
    * @see $timeout_sock
    * @access public
    * @var integer
    *
    */
    var $timeout_sock_m = 0;

    /**
    * OGP::$ChallengeNumber
    *
    * Stores the last parsed ChallengeNumber
    *
    * @access protected
    * @var mixed
    *
    */
    var $ChallengeNumber = -1;
    /**
    * OGP::$RequestID
    *
    * Stores the last parsed RequestID
    *
    * @access protected
    * @var mixed
    *
    */
    var $RequestID = -1;

    /**
    * OGP::$SERVERINFO
    *
    * All vars depending on SERVERINFO
    * are stored in this array
    *
    * @access public
    * @var mixed
    *
    */
    var $SERVERINFO = array();
    /**
    * OGP::$RULELIST
    *
    * All vars depending on RULELIST
    * are stored in this array
    *
    * @access public
    * @var mixed
    *
    */
    var $RULELIST = array();
    /**
    * OGP::$TEAMLIST
    *
    * All vars depending on TEAMLIST
    * are stored in this array
    *
    * @access public
    * @var mixed
    *
    */
    var $TEAMLIST = array();
    /**
    * OGP::$PLAYERLIST
    *
    * All vars depending on PLAYERLIST
    * are stored in this array
    *
    * @access public
    * @var mixed
    *
    */
    var $PLAYERLIST = array();
    /**
    * OGP::$ADDONLIST
    *
    * All vars depending on ADDONLIST
    * are stored in this array
    *
    * @access public
    * @var mixed
    *
    */
    var $ADDONLIST = array();
    /**
    * OGP::$LIMITLIST
    *
    * All vars depending on LIMITLIST
    * are stored in this array
    *
    * @access public
    * @var mixed
    *
    */
    var $LIMITLIST = array();

    /**
    * OGP::OGP()
    *
    * the Contructor of the class
    *
    * Just sets the address, port and the default timeout
    *
    * @param string $addr
    * @param integer $port
    * @param integer $timeout
    */
    function OGP($addr, $port, $timeout = 100)
    {
        $this->addr = $addr;
        $this->port = $port;
        $this->timeout = $timeout;
    }


	/**
    * OGP::timenow()
    *
    * Returns the exact current time
    * Used to calculate the exact difference between to times
    *
    * @access protected
    * @return float The exact current time
    *
    */
    function timenow()
    {
		return doubleval(preg_replace('/^0\.([0-9]*) ([0-9]*)$/', '\\2.\\1', microtime()));
	}
	
    /**
    * OGP::serverQuery()
    *
    * Does the sending and receiving of the data
    *
    * I sends the command stored in $command to the specified server
    * that is listening on the specified port (using the UDP Protocol)
    * After that it waits for at least one incomming Packets and analyses
    * if it is a valid OGP Packet.
    *
    * If it is valid, the method waits for more packets (if bSplit is
    * specified in the OGP Header) and stops waiting if that many packets
    * as specified in SplitPacketNo were received or if there is a timeout.
    *
    * If it is <b>not</b> valid, it is descarded and the function returns false
    *
    * Finally it generates one string out of the received packets (stripping
    * the headers). This string is stored in $OGP::queryres.
    *
    * @access protected
    *
    * @param string $command The command to send to the server
    * @param string $addr The address of the OGP server
    * @param integer $port The port of the OGP server
    * @param integer $waittime The read timeout
    * @return bool True on success, false on failure
    *
    */
    function serverQuery($command, $addr, $port, $waittime)
    {
        if (!$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))
        {
            $this->error = "Could not connect!";
            return false;
        }
        
        if (socket_bind($socket, '54.38.153.141') !== TRUE)
        {
            socket_close($socket);
            $this->error = "Could not connect 2!";
            return false;
        }

        if (socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec'=> $this->timeout_sock, 'usec'=> $this->timeout_sock_m)) !== TRUE)
        {
            socket_close($socket);
            $this->error = "Could not connect 3!";
            return false;
        }

        /*if (!$socket = fsockopen("udp://".$addr,$port, $errno, $errstr, 2))
        {
            $this->error = "Could not connect!";
            return false;
        }*/
        /*if (@socket_set_timeout($socket, $this->timeout_sock, $this->timeout_sock_m) !== TRUE)
        {
            $this->error = "Could not connect 2!";
            return false;
        }*/

        $bytes_sent = 0;
        if (!($bytes_sent = socket_sendto($socket, $command, strlen($command), 0, $addr, $port) > 0))
        {
            socket_close($socket);
            $this->error = "Could not query!";
            return false;
        }

        /*if(!fwrite($socket,$command,strlen($command)))
        {
            $this->error = "Could not query!";
            return false;
        }*/

        $this->pakets = array();
        $this->result_packets = 0;
        $this->queryres = "";
        do
        {

            $starttime = $this->timenow();
            $serverdata = "";
            /*do
            {
                $serverdata .= fgetc($socket);
                //$serverdatalen++;
                $socketstatus = socket_get_status($socket);
                if ($this->timenow() > ($starttime+$waittime))
                {
                    fclose($socket);
                    $this->error = "Connection timed out";
                    return false;
                }
           }
           while ($socketstatus["unread_bytes"] && !feof($socket));*/
           if (!(socket_recv($socket, $serverdata, 2048, 0) > 0))
           {
               socket_close($socket);
               $this->error = "Remote system unresponsive!";
               return false;
           }

           $this->result_packets++;
           $this->total_result_packets++;

           $req = "\xFF\xFF\xFF\xFFOGP\x00";
           if(substr($serverdata,0,strlen($req)) != $req)
           {
               socket_close($socket);
               $this->error = "Not a OGP Server Response (1)";
               return false;
           }


           $flags_check = substr($serverdata, 10, 1);
           $flags = $this->getVarBitArray($flags_check);

           if($flags[0][3] != 1) //bSplit
           {
               if($this->result_packets == 1)
               {
                   $this->pakets[0] = $serverdata;
               }
               break;
           }
           else
           {
               $HeaderSize = substr($serverdata, 8, 1);
               $HeaderSize = $this->parseUint($HeaderSize);

               $serverheader = substr($serverdata, 8, $HeaderSize);

               $SplitPacketCount = substr($serverheader, -2,1);
               $SplitPacketNo = substr($serverheader, -1,1);

               $SplitPacketCount = $this->parseUint($SplitPacketCount);
               $SplitPacketNo = $this->parseUint($SplitPacketNo);

               $this->pakets[$SplitPacketNo] = $serverdata;

               if($this->result_packets >= $SplitPacketCount)
               {
                   break;
               }
           }

        }
        while(true);


        socket_close($socket);
        $this->ping = round(($this->timenow() - $starttime) * 100);


        for($i=0;$i<sizeof($this->pakets);$i++)
        {
            $this_headersize = substr($this->pakets[$i],8,1);
            $this_headersize = $this->parseUint($this_headersize);

            if($i > 0)
            {
                $this->queryres .= substr($this->pakets[$i], $this_headersize + 8);
            }
            else
            {
                $this->queryres .= $this->pakets[$i];
            }

        }
        return true;
    }

    /**
    * OGP::getChallengeNumber()
    *
    * This method requests a new ChallengeNumber from the Server,
    * by sending an Invalid Query where no ChallengeNumber is specified.
    *
    * The received ChallengeNumber is stored in OGP::ChallengeNumber
    *
    * @access protected
    * @return bool True on success, false on failure
    *
    */
    function getChallengeNumber()
    {
        $command = "\xFF\xFF\xFF\xFFOGP\x00";  //header
        $Type = "\x01"; //query
        $HeadFlags = "00000000";
        $HeadFlags = bindec($HeadFlags);
        $command2 = $Type.chr($HeadFlags);

        $command = $command.chr(strlen($command2)+1).$command2;


        if(!$this->serverQuery($command, $this->addr, $this->port, $this->timeout))
        {
            return false;
        }

        $result = $this->queryres;

        $req = "\xFF\xFF\xFF\xFFOGP\x00";
        if(substr($result,0,strlen($req)) != $req)
        {
            $this->error = "Not a OGP Server Response (2)";
            return false;
        }

        $result2 = substr($result, 8);
        $size = ord($this->getUint($result2,8));


        $type = $this->getUint($result2,8);
        if($type != "\xFF") //must be an error
        {
            $this->error = "Unexpected value (1)";
            return false;
        }

        $HeadFlags = $this->getVarBitArray($result2);
        if($HeadFlags[0][0] != "1") //must be 1, because we're waiting for an answer
                                    //packet
        {
            $this->error = "Unexpected value (2)";
            return false;
        }

        if($HeadFlags[0][1] != "1") //must be 1, because we want a Challengenumber!
        {
            $this->error = "Unexpected value (3)";
            return false;
        }

        $this->ChallengeNumber = $this->getUint($result2,32);

        return true;
    }

    /**
    * OGP::getStatus()
    *
    * Does the "Default query v1" as specified on www.online-game-protocol.org
    *
    * It generates the OGP Header and the OGP Query,
    * then calls OGP::serverQuery(); to get the answer packets.
    * After that it parses the result packets, and fills the arrays:
    *  - {@link OGP::$SERVERINFO}
    *  - {@link OGP::$TEAMLIST}
    *  - {@link OGP::$PLAYERLIST}
    *  - {@link OGP::$RULELIST}
    *  - {@link OGP::$ADDONLIST}
    *  - {@link OGP::$LIMITLIST}
    * (the arrays are only filled with the values that are supported by the server)
    *
    * @access public
    * @see OGP::serverQuery()
    * @return bool True on success, false on failure
    *
    */
    function getStatus()
    {
        if($this->ChallengeNumber == -1)
        {
            if(!$this->getChallengeNumber())
            {
                return false;
            }
        }

        if($this->ChallengeNumber == -1)
        {
            $this->error = "Could not get Challenge Number!";
            return false;
        }

        //HEADER BEGIN

        $command = "\xFF\xFF\xFF\xFFOGP\x00";
        $Type = "\x01"; //query
		$HeadFlags[0][1] = 1;
		$HeadFlags[0][4] = 1;
        $HeadFlags_send = $this->getCharsbyBinary($this->VarBitArray_toString($HeadFlags));
                   /*
                   Bit 0.0: bAnswer = 0
                   Bit 0.1: bChallengeNumber
                   Bit 0.2: bRequestID
                   Bit 0.3: bSplit
                   Bit 0.4: (bPassword)

                   The server ignores the query if bAnswer is set.

                   The password field is reserved for future.
                   It is needed for ogp rcon protocol and to
                   request sensitiv information about some
                   player via default protocol (e.g. IP address)
                   */

        $command2 = $Type.$HeadFlags_send.$this->ChallengeNumber;

        $command = $command.chr(strlen($command2)+1).$command2;

        //HEADER ENDE

        //QUERY BEGIN

        $RequestFlags[0][0] = 1;
        $RequestFlags[0][1] = 0;
        $RequestFlags[0][2] = 0;
        $RequestFlags[0][3] = 0;
        $RequestFlags[0][4] = 0;
        $RequestFlags[0][5] = 0;
        $RequestFlags[1][0] = 0;
        $RequestFlags_send = $this->getCharsbyBinary($this->VarBitArray_toString($RequestFlags));
                      /*
                      Bit 0.0: bServerInfo
                      Bit 0.1: bTeamList
                      Bit 0.2: bPlayerList
                      Bit 0.3: bRuleList
                      Bit 0.4: bAddOnList
                      Bit 0.5: bLimitList

                      Bit 1.0: bColoredNames
                      */
        if($RequestFlags[0][0] == 1)
        {
            $ServerInfoFields[0][0] = 1;
            $ServerInfoFields[0][1] = 1;
            $ServerInfoFields[0][2] = 1;
            $ServerInfoFields[0][3] = 1;

            $ServerInfoFields[1][0] = 1;
            $ServerInfoFields[1][1] = 1;
            $ServerInfoFields[1][2] = 1;
            $ServerInfoFields[1][3] = 1;
            $ServerInfoFields[1][4] = 0;

            $ServerInfoFields[2][0] = 1;
            $ServerInfoFields[2][1] = 1;
            $ServerInfoFields[2][2] = 0;
            $ServerInfoFields[2][3] = 0;
        }
        else
        {
            $ServerInfoFields[0][0] = 0;
        }

        $ServerInfoFields_send = $this->getCharsbyBinary($this->VarBitArray_toString($ServerInfoFields));
                          /*
                           - Depends: bServerInfo
                           Bit 0.0: bGameName
                           Bit 0.1: bServerFlags
                           Bit 0.2: bHostName
                           Bit 0.3: bConnectPort

                           Bit 1.0: bMod
                           Bit 1.1: bGameType
                           Bit 1.2: bGameMode
                           Bit 1.3: bMap
                           Bit 1.4: bNextMap

                           Bit 2.0: bPlayerCount
                           Bit 2.1: bSlotMax
                           Bit 2.2: bBotCount
                           Bit 2.3: bReservedSlots
                          */
        if($ServerInfoFields[1][0] == 1) //ServerInfoFields.bMod
        {
            $ModFields[0][0] = 1;
            $ModFields[0][1] = 1;
            $ModFields[0][2] = 1;
            $ModFields[0][3] = 1;
            $ModFields[0][4] = 1;
        }
        else
        {
            $ModFields[0][0] = 0;
        }
        
        $ModFields_send = $this->getCharsbyBinary($this->VarBitArray_toString($ModFields));
                          /*
                           - Depends: ServerInfoFields.bMod
                            Bit 0.0: bModIdentifier
                            Bit 0.1: bModSize
                            Bit 0.2: bModVersion
                            Bit 0.3: bModURL
                            Bit 0.4: bModAuthor
                          */
                          
        if($ServerInfoFields[1][3] == 1) //ServerInfoFields.bMap
        {
            $MapFields[0][0] = 1;
            $MapFields[0][1] = 1;
            $MapFields[0][2] = 1;
            $MapFields[0][3] = 1;
            $MapFields[0][4] = 1;
            $MapFields[0][5] = 1;
        }
        else
        {
            $MapFields[0][0] = 0;
        }

        $MapFields_send = $this->getCharsbyBinary($this->VarBitArray_toString($MapFields));
                          /*
                           - Depends: ServerInfoFields.bMap
                             Bit 0.0: bMapFileName
                             Bit 0.1: bMapFileSize
                             Bit 0.2: bMapFileMD5
                             Bit 0.3: bMapVersion
                             Bit 0.4: bMapURL
                             Bit 0.5: bMapAuthor
                          */
        if($RequestFlags[0][1] == 1)
        {
            $TeamFields[0][0] = 1;
            $TeamFields[0][1] = 1;
            $TeamFields[0][2] = 1;
            $TeamFields[0][3] = 1;
            $TeamFields[0][4] = 1;
            $TeamFields[0][5] = 1;
        }
        else
        {
            $TeamFields[0][0] = 0;
        }
        $TeamFields_send = $this->getCharsbyBinary($this->VarBitArray_toString($TeamFields));
                    /*
                     - Depends: bTeamList
                    Bit 0.0: bTeamName
                    Bit 0.1: bTeamScore
                    Bit 0.2: bTeamAveragePing
                    Bit 0.3: bTeamAverageLoss
                    Bit 0.4: bTeamPlayerCount
                    Bit 0.5: bTeamColor
                    */
        if($RequestFlags[0][2] == 1)
        {
            $PlayerFields[0][0] = 1;
            $PlayerFields[0][1] = 1;
            $PlayerFields[0][2] = 1;
            $PlayerFields[0][3] = 1;
            $PlayerFields[0][4] = 1;
            $PlayerFields[0][5] = 1;

            $PlayerFields[1][0] = 1;
            $PlayerFields[1][1] = 1;
            $PlayerFields[1][2] = 1;
            $PlayerFields[1][3] = 1;
            $PlayerFields[1][4] = 1;
            $PlayerFields[1][5] = 1;

            $PlayerFields[2][0] = 1;
            $PlayerFields[2][1] = 1;
            $PlayerFields[2][2] = 1;
            $PlayerFields[2][3] = 1;
            $PlayerFields[2][4] = 1;
            $PlayerFields[2][5] = 1;

            $PlayerFields[3][0] = 1;
        }
        else
        {
            $PlayerFields[0][0] = 0;
        }
        $PlayerFields_send = $this->getCharsbyBinary($this->VarBitArray_toString($PlayerFields));
                      /*
                       - Depends: bPlayerList
                      This field indicates which player information will be returned

                      Bit 0.0: bPlayerFlags
                      Bit 0.1: bPlayerSlot
                      Bit 0.2: bPlayerName
                      Bit 0.3: bPlayerTeam
                      Bit 0.4: bPlayerClass
                      Bit 0.5: bPlayerRace

                      Bit 1.0: bPlayerScore
                      Bit 1.1: bPlayerFrags
                      Bit 1.2: bPlayerKills
                      Bit 1.3: bPlayerDeath
                      Bit 1.4: bPlayerSuicides
                      Bit 1.5: bPlayerTeamKills

                      Bit 2.0: bPlayerID
                      Bit 2.1: bPlayerGlobalID
                      Bit 2.2: bPlayerPing
                      Bit 2.3: bPlayerLoss
                      Bit 2.4: bPlayerModel
                      Bit 2.5: bPlayerTime

                      Bit 3.0: bPlayerAddress
                      */
        if($RequestFlags[0][4] == 1)
        {
            $AddOnFields[0][0] = 1;
            $AddOnFields[0][1] = 1;
            $AddOnFields[0][2] = 1;
            $AddOnFields[0][3] = 1;
        }
        else
        {
            $AddOnFields[0][0] = 0;
        }
        $AddOnFields_send = $this->getCharsbyBinary($this->VarBitArray_toString($AddOnFields));
                     /*
                      - Depends: bAddOnList
                     Bit 0.0: bAddOnFlags
                     Bit 0.1: bAddOnShortName
                     Bit 0.2: bAddOnLongName
                     Bit 0.3: bAddOnVersion
                     */

        //QUERY ENDE

        $query = $RequestFlags_send.$ServerInfoFields_send.$ModFields_send.$MapFields_send.$TeamFields_send.
                   $PlayerFields_send.$AddOnFields_send;

        $command .= $query;

        if(!$this->serverQuery($command, $this->addr, $this->port, $this->timeout))
        {
            return false;
        }

        $result = $this->queryres;

        $req = "\xFF\xFF\xFF\xFFOGP\x00";
        if(substr($result,0,strlen($req)) != $req)
        {
            $this->error = "Not a OGP Server Response (2)";
            return false;
        }

        $result2 = substr($result, 8);
        $HeadSize = ord($this->getUint($result2,8));
        $Type = $this->getUint($result2,8);
        if($Type == "\xFF") //Error?
        {
            $err_message = substr($result, $HeadSize + 8);
            $err_id = $this->getUint($err_message,8);
            $this->error = "Server says Error: '".$this->getErrorbyID($err_id )."' (1)";
            return false;
        }
        elseif($Type != "\x01") //must be a default query v1
        {
            $this->error = "Unexpected value (4)";
            return false;
        }

        $HeadFlags = $this->getVarBitArray($result2);
        if($HeadFlags[0][0] != 1) //must be an Answer Packet
        {
            $this->error = "Unexpected value (5)";
            return false;
        }

        if($HeadFlags[0][1] == 1) //bChallengeNumber isset
        {
            $this->ChallengeNumber = $this->getInt($result2, 32);
        }
        if($HeadFlags[0][2] == 1) //bRequestID isset
        {
            $this->RequestID = $this->getInt($result2, 32);
        }
        if($HeadFlags[0][3] == 1) //bSplit isset
        {
            $SplitPacketCount = $this->getInt($result2, 8);
            $SplitPacketNo = $this->getInt($result2, 8);
        }

        ///HEADER ENDE!

        $GameID = $this->getUint($result2, 16);
        $get_RequestFlags = $this->getVarBitArray($result2);

        if($get_RequestFlags[0][0] == 1) //bServerInfo
        {
            $get_ServerInfoFields = $this->getVarBitArray($result2);
            if($get_ServerInfoFields[0][0] == 1) //bGameName
            {
                $this->SERVERINFO['GameName'] = $this->getSzString($result2);
            }
            if($get_ServerInfoFields[0][1] == 1) //bServerFlags
            {
                $this->SERVERINFO['ServerFlags'] = $this->getVarBitArray($result2);
                $this->parseServerFlags();
            }
            if($get_ServerInfoFields[0][2] == 1) //bHostName
            {
                $this->SERVERINFO['HostName'] = $this->getSzString($result2);
                /* PHP Notice:  Undefined offset:
                if($get_RequestFlags[1][0] == 1) //bColoredNames
                {
                    $this->SERVERINFO['HostNameColor'] = $this->getStringColorInfo($result2);
                }*/
            }

            if($get_ServerInfoFields[0][3] == 1) //bConnectPort
            {
                $this->SERVERINFO['ConnectPort'] = $this->parseUint($this->getUint($result2, 16));
            }

            if($get_ServerInfoFields[1][0] == 1) //bMod
            {
                $this->SERVERINFO['MODINFO']['ModName'] = $this->getSzString($result2);
                if(!empty($this->SERVERINFO['MODINFO']['ModName']))
                {
                    $get_ModFields = $this->getVarBitArray($result2);
                    if($get_ModFields [0][0] == 1) //bModIdentifier
                    {
                        $this->SERVERINFO['MODINFO']['ModIdentifier'] = $this->getSzString($result2);
                        if(empty($this->SERVERINFO['MODINFO']['ModIdentifier']))
                        {
                            $this->SERVERINFO['MODINFO']['ModIdentifier'] = $this->SERVERINFO['MODINFO']['ModName'];
                        }
                    }
                    if($get_ModFields [0][1] == 1) //bModSize
                    {
                        $this->SERVERINFO['MODINFO']['ModSize'] = $this->parseUint($this->getUint($result2, 32));
                    }
                    if($get_ModFields [0][2] == 1) //bModVersion
                    {
                        $this->SERVERINFO['MODINFO']['ModVersion'] = $this->getSzString($result2);
                    }
                    if($get_ModFields [0][3] == 1) //bModURL
                    {
                        $this->SERVERINFO['MODINFO']['ModURL'] = $this->getSzString($result2);
                    }
                    if($get_ModFields [0][4] == 1) //bModAuthor
                    {
                        $this->SERVERINFO['MODINFO']['ModAuthor'] = $this->getSzString($result2);
                    }
                }
            }
            if($get_ServerInfoFields[1][1] == 1) //bGameType
            {
                $this->SERVERINFO['GameType'] = $this->getSzString($result2);
            }
            if($get_ServerInfoFields[1][2] == 1) //bGameMode
            {
                $this->SERVERINFO['GameMode'] = $this->getSzString($result2);
            }
            if($get_ServerInfoFields[1][3] == 1) //bMap
            {
                $get_MapFields = $this->getVarBitArray($result2);
                $this->SERVERINFO['MAPINFO']['MapName'] = $this->getSzString($result2);
                if($get_MapFields[0][0] == 1) //bMapFileName
                {
                    $this->SERVERINFO['MAPINFO']['MapFileName'] = $this->getSzString($result2);
                }
                if($get_MapFields[0][1] == 1) //bMapFileSize
                {
                    $this->SERVERINFO['MAPINFO']['MapFileSize'] = $this->parseUint($this->getUint($result2, 32));
                }
                if($get_MapFields[0][2] == 1) //bMapFileMD5
                {
                    $this->SERVERINFO['MAPINFO']['MapFileMD5'] = "";
                    for($md5c=0;$md5c<16;$md5c++)
                    {
                        $this->SERVERINFO['MAPINFO']['MapFileMD5'] .= chr($this->parseUint($this->getUint($result2, 8)));
                    }
                }
                if($get_MapFields[0][3] == 1) //bMapVersion
                {
                    $this->SERVERINFO['MAPINFO']['MapVersion'] = $this->getSzString($result2);
                }
                if($get_MapFields[0][4] == 1) //bMapURL
                {
                    $this->SERVERINFO['MAPINFO']['MapURL'] = $this->getSzString($result2);
                }
                if($get_MapFields[0][5] == 1) //bMapAuthor
                {
                    $this->SERVERINFO['MAPINFO']['MapAuthor'] = $this->getSzString($result2);
                }
            }
            if($get_ServerInfoFields[1][3] == 1 && $get_ServerInfoFields[1][4] == 1) //bMap && bNextMap
            {
                $this->SERVERINFO['NEXTMAPINFO']['MapName'] = $this->getSzString($result2);
                if($get_MapFields[0][0] == 1) //bMapFileName
                {
                    $this->SERVERINFO['NEXTMAPINFO']['MapFileName'] = $this->getSzString($result2);
                }
                if($get_MapFields[0][1] == 1) //bMapFileSize
                {
                    $this->SERVERINFO['NEXTMAPINFO']['MapFileSize'] = $this->parseUint($this->getUint($result2, 32));
                }
                if($get_MapFields[0][2] == 1) //bMapFileMD5
                {
                    $this->SERVERINFO['NEXTMAPINFO']['MapFileMD5'] = "";
                    for($md5c=0;$md5c<16;$md5c++)
                    {
                        $this->SERVERINFO['NEXTMAPINFO']['MapFileMD5'] .= chr($this->parseUint($this->getUint($result2, 8)));
                    }
                }
                if($get_MapFields[0][3] == 1) //bMapVersion
                {
                    $this->SERVERINFO['NEXTMAPINFO']['MapVersion'] = $this->getSzString($result2);
                }
                if($get_MapFields[0][4] == 1) //bMapURL
                {
                    $this->SERVERINFO['NEXTMAPINFO']['MapURL'] = $this->getSzString($result2);
                }
                if($get_MapFields[0][5] == 1) //bMapAuthor
                {
                    $this->SERVERINFO['NEXTMAPINFO']['MapAuthor'] = $this->getSzString($result2);
                }
            }

            if($get_ServerInfoFields[2][0] == 1) //bPlayerCount
            {
                $this->SERVERINFO['PlayerCount'] = $this->parseUint($this->getVarUint($result2));
            }
            if($get_ServerInfoFields[2][1] == 1) //bSlotMax
            {
                $this->SERVERINFO['SlotMax'] = $this->parseUint($this->getVarUint($result2));
            }
            if($get_ServerInfoFields[2][2] == 1) //bBotCount
            {
                $this->SERVERINFO['BotCount'] = $this->parseUint($this->getVarUint($result2));
            }
            if($get_ServerInfoFields[2][3] == 1) //bReservedSlots
            {
                $this->SERVERINFO['ReservedSlots'] = $this->parseUint($this->getVarUint($result2));
            }
        }

        if($get_RequestFlags[0][1] == 1) //bTeamList
        {
            $TeamCount = $this->parseUint($this->getVarUint($result2));
            if($TeamCount > 0)
            {
                $get_TeamFields = $this->getVarBitArray($result2);
            }

            for($t=0;$t<$TeamCount;$t++)
            {
                $this_team_entry = array();

                if($get_TeamFields[0][0] == 1) //bTeamName
                {
                    $this_team_entry['TeamName'] = $this->getSzString($result2);
                    if($get_RequestFlags[1][0] == 1) //bColoredNames
                    {
                        $this_team_entry['TeamNameColor'] = $this->getStringColorInfo($result2);
                    }
                }
                if($get_TeamFields[0][1] == 1) //bTeamScore
                {
                    $this_team_entry['TeamScore'] = $this->parseInt($this->getVarSint($result2));
                }
                if($get_TeamFields[0][2] == 1) //bTeamAveragePing
                {
                    $this_team_entry['TeamAveragePing'] = $this->parseUint($this->getUint($result2, 16));
                }
                if($get_TeamFields[0][3] == 1) //bTeamAverageLoss
                {
                    $this_team_entry['TeamAverageLoss'] = $this->parseUint($this->getUint($result2, 16));
                }
                if($get_TeamFields[0][4] == 1) //bTeamPlayerCount
                {
                    $this_team_entry['TeamPlayerCount'] = $this->parseUint($this->getVarUint($result2));
                }
                if($get_TeamFields[0][5] == 1) //bTeamColor
                {
                    $this_team_entry['TeamColor'] = $this->parseUint($this->getUint($result2, 16));
                }

                array_push($this->TEAMLIST, $this_team_entry);
            }

        }

        if($get_RequestFlags[0][2] == 1) //bPlayerList
        {
            $PlayerCount = $this->parseUint($this->getVarUint($result2));
            if($PlayerCount > 0)
            {
                $get_PlayerFields = $this->getVarBitArray($result2);
            }

            for($p=0;$p<$PlayerCount;$p++)
            {
                $this_player_entry = array();

                if($get_PlayerFields[0][0] == 1) //bPlayerFlags
                {
                    $this_player_entry['PlayerFlags'] = $this->parsePlayerFlags($this->getVarBitArray($result2));
                }
                if($get_PlayerFields[0][1] == 1) //bPlayerSlot
                {
                    $this_player_entry['PlayerSlot'] = $this->parseUint($this->getVarUint($result2));
                }
                if($get_PlayerFields[0][2] == 1) //bPlayerName
                {
                    $this_player_entry['PlayerName'] = $this->getSzString($result2);
                    if($get_RequestFlags[1][0] == 1) //bColoredNames
                    {
                        $this_player_entry['PlayerNameColor'] = $this->getStringColorInfo($result2);
                    }
                }
                if($get_PlayerFields[0][3] == 1) //bPlayerTeam
                {
                    $this_player_entry['PlayerTeamNo'] = $this->parseInt($this->getVarSint($result2));
                }
                if($get_PlayerFields[0][4] == 1) //bPlayerClass
                {
                    $this_player_entry['PlayerClass'] = $this->getSzString($result2);
                }
                if($get_PlayerFields[0][5] == 1) //bPlayerRace
                {
                    $this_player_entry['PlayerRace'] = $this->getSzString($result2);
                }

                if($get_PlayerFields[1][0] == 1) //bPlayerScore
                {
                    $this_player_entry['PlayerScore'] = $this->parseInt($this->getVarSint($result2));
                }
                if($get_PlayerFields[1][1] == 1) //bPlayerFrags
                {
                    $this_player_entry['PlayerFrags'] = $this->parseInt($this->getVarSint($result2));
                }
                if($get_PlayerFields[1][2] == 1) //bPlayerKills
                {
                    $this_player_entry['PlayerKills'] = $this->parseUint($this->getVarUint($result2));
                }
                if($get_PlayerFields[1][3] == 1) //bPlayerDeath
                {
                    $this_player_entry['PlayerDeath'] = $this->parseUint($this->getVarUint($result2));
                }
                if($get_PlayerFields[1][4] == 1) //bPlayerSuicides
                {
                    $this_player_entry['PlayerSuicides'] = $this->parseUint($this->getVarUint($result2));
                }
                if($get_PlayerFields[1][5] == 1) //bPlayerTeamKills
                {
                    $this_player_entry['PlayerTeamKills'] = $this->parseUint($this->getVarUint($result2));
                }

                if($get_PlayerFields[2][0] == 1) //bPlayerID
                {
                    $this_player_entry['PlayerID'] = $this->parseUint($this->getUint($result2, 32));
                }
                if($get_PlayerFields[2][1] == 1) //bPlayerGlobalID
                {
                    $this_player_entry['PlayerGlobalID'] = $this->getSzString($result2);
                }
                if($get_PlayerFields[2][2] == 1) //bPlayerPing
                {
                    $this_player_entry['PlayerPing'] = $this->parseUint($this->getUint($result2, 16));
                }
                if($get_PlayerFields[2][3] == 1) //bPlayerLoss
                {
                    $this_player_entry['PlayerLoss'] = $this->parseUint($this->getUint($result2, 16));
                }
                if($get_PlayerFields[2][4] == 1) //bPlayerModel
                {
                    $this_player_entry['PlayerModel'] = $this->getSzString($result2);
                }
                if($get_PlayerFields[2][5] == 1) //bPlayerTime
                {
                    $this_player_entry['PlayerTime'] = $this->parseUint($this->getUint($result2, 16));
                }

                if($get_PlayerFields[3][0] == 1) //bPlayerAddress
                {
                    //$this_player_entry['PlayerAddress'] = $this->parseIP($this->getUint($result2, 32));
                    $PlayerAddressLen = $this->parseUint($this->getVarUint($result2));
                    //FIXME!
                    $PlayerAddress = "";
                    for($padrc=0;$padrc<$PlayerAddressLen;$padrc++)
                    {
                        $PlayerAddress .= $this->getUint($result2, 8);
                    }
                }

                array_push($this->PLAYERLIST, $this_player_entry);
            }
        }


        if($get_RequestFlags[0][3] == 1) //bRuleList
        {
            $RuleCount = $this->parseUint($this->getVarUint($result2));

            for($r=0;$r<$RuleCount;$r++)
            {
                $this->RULELIST[$this->getSzString($result2)] = $this->getSzString($result2);
            }
        }

        if($get_RequestFlags[0][4] == 1) //bAddOnList
        {
            $AddOnCount = $this->parseUint($this->getVarUint($result2));
            if($AddOnCount > 0)
            {
                $get_AddOnFields = $this->getVarBitArray($result2);
            }

            for($p=0;$p<$AddOnCount;$p++)
            {
                $this_addon_entry = array();

                if($get_AddOnFields[0][0] == 1) //bAddOnFlags
                {
                    $this_addon_entry['AddOnFlags'] = $this->parseAddOnFlags($this->getVarBitArray($result2));
                }
                if($get_AddOnFields[0][1] == 1) //bAddOnShortName
                {
                    $this_addon_entry['AddOnShortName'] = $this->getSzString($result2);
                }
                if($get_AddOnFields[0][2] == 1) //bAddOnLongName
                {
                    $this_addon_entry['AddOnLongName'] = $this->getSzString($result2);
                }
                if($get_AddOnFields[0][3] == 1) //bAddOnVersion
                {
                    $this_addon_entry['AddOnVersion'] = $this->getSzString($result2);
                }

                array_push($this->ADDONLIST, $this_addon_entry);
            }
        }

        if($get_RequestFlags[0][5] == 1) //bLimitList
        {
            $LimitCount = $this->parseUint($this->getVarUint($result2));

            for($p=0;$p<$LimitCount;$p++)
            {
                $this_limit_entry = array();

                $this_limit_entry['LimitType'] = $this->getVarBitArray($result2);
                $bLimitLeft = $this_limit_entry['LimitType'][0][0]; //bLimitLeft

                $LimitType = bindec(strrev($this_limit_entry['LimitType'][0][1].
                           $this_limit_entry['LimitType'][0][2].
                           $this_limit_entry['LimitType'][0][3].
                           $this_limit_entry['LimitType'][0][4]));

                //$LimitType = $this->parseUint($LimitType);
                $this_limit_entry['LimitType'] = $this->parseLimitType($LimitType);

                $this_limit_entry['Limit'] = $this->parseUint($this->getVarUint($result2));
                if($bLimitLeft == 1) //bLimitLeft
                {
                    $this_limit_entry['Left'] = $this->parseUint($this->getVarUint($result2));
                }

                array_push($this->LIMITLIST, $this_limit_entry);
            }
        }

        return true;

    }

    /**
    * OGP::parseLimitType()
    *
    * Returnes the name of the supplied
    * LimitType integer.
    *
    * @access protected
    * @param integer $LimitType A valid LimitType integer
    * @return string The name of the supplied LimitType
    *
    */
    function parseLimitType($LimitType)
    {

        switch($LimitType)
        {
            case 0: return "Time (in seconds)";
            case 1: return "Player Score";
            case 2: return "Round";
            case 3: return "Team Score";

            default: return "Unknown";
        }

    }

    /**
    * OGP::parseAddOnFlags()
    *
    * This method reads the supplied array
    * of the type VarBitArray and returns an
    * array filled with known values
    *
    * @access protected
    * @param VarBitArray $array The AddOnFlags stored in a VarBitArray
    * @return mixed The parsed AddOnFlags, false on failure
    *
    */
    function parseAddOnFlags($array)
    {
        if(!isset($array[0]))
        {
            return false;
        }

        $new_flags = array();
        if($array[0][0] == 1) //bActive
        {
            $new_flags['Active'] = true;
        }
        else
        {
            $new_flags['Active'] = false;
        }

        if($array[0][1] == 1) //bAntiCheatTool
        {
            $new_flags['AntiCheatTool'] = true;
        }
        else
        {
            $new_flags['AntiCheatTool'] = false;
        }

        if($array[0][2] == 1) //bMutator
        {
            $new_flags['Mutator'] = true;
        }
        else
        {
            $new_flags['Mutator'] = false;
        }

        if($array[0][3] == 1) //bAdminTool
        {
            $new_flags['AdminTool'] = true;
        }
        else
        {
            $new_flags['AdminTool'] = false;
        }

        return $new_flags;
    }

    /**
    * OGP::parsePlayerFlags()
    *
    * This method reads the supplied array
    * of the type VarBitArray and returns an
    * array filled with known values
    *
    * @access protected
    * @param VarBitArray $array The PlayerFlags stored in a VarBitArray
    * @return mixed The parsed PlayerFlags, false on failure
    *
    */
    function parsePlayerFlags($array)
    {
        if(!isset($array[0]))
        {
            return false;
        }

        $new_flags = array();
        if($array[0][0] == 1) //bAlive
        {
            $new_flags['Alive'] = true;
        }
        else
        {
            $new_flags['Alive'] = false;
        }

        if($array[0][1] == 1) //bDead
        {
            $new_flags['Dead'] = true;
        }
        else
        {
            $new_flags['Dead'] = false;
        }

        if($array[0][2] == 1) //bBot
        {
            $new_flags['Bot'] = true;
        }
        else
        {
            $new_flags['Bot'] = false;
        }

        if($array[1][0] == 1) //bBomp
        {
            $new_flags['Bomp'] = true;
        }

        if($array[1][1] == 1) //bVIP
        {
            $new_flags['VIP'] = true;
        }

        return $new_flags;
    }

    /**
    * OGP::parseServerFlags()
    *
    * This method reads the array OGP::SERVERINFO['ServerFlags']
    * of the type VarBitArray and sets the variable
    * OGP::SERVERINFO['ServerFlags'] with known values
    *
    * @access protected
    * @return bool true on success, false on failure
    *
    */
    function parseServerFlags()
    {
        if(!isset($this->SERVERINFO['ServerFlags'][0]))
        {
            return false;
        }

        $new_flags = array();
        $bType = bindec(strrev($this->SERVERINFO['ServerFlags'][0][0].
               $this->SERVERINFO['ServerFlags'][0][1]));
        switch($bType)
        {
            case 0: $new_flags['bType'] = "Unknown"; break;
            case 1: $new_flags['bType'] = "Listen"; break;
            case 2: $new_flags['bType'] = "Dedicated"; break;
        }
        
        if($this->SERVERINFO['ServerFlags'][0][2] == 1) //bPassword
        {
            $new_flags['Password'] = true;
        }
        else
        {
            $new_flags['Password'] = false;
        }
        
        if($this->SERVERINFO['ServerFlags'][0][3] == 1) //bProxy
        {
            $new_flags['Proxy'] = true;
        }
        else
        {
            $new_flags['Proxy'] = false;
        }

        $OperatingSystem = bindec(strrev($this->SERVERINFO['ServerFlags'][0][4].
                           $this->SERVERINFO['ServerFlags'][0][5].
                           $this->SERVERINFO['ServerFlags'][0][6]));

        switch($OperatingSystem)
        {
            case 0: $new_flags['OperatingSystem'] = "Unknown"; break;
            case 1: $new_flags['OperatingSystem'] = "Windows"; break;
            case 2: $new_flags['OperatingSystem'] = "Linux"; break;
            case 3: $new_flags['OperatingSystem'] = "Macintosh"; break;

            default: $new_flags['OperatingSystem'] = "Unknown (".$OperatingSystem.")";
        }

        $this->SERVERINFO['ServerFlags'] = $new_flags;
        
        return true;
    }

    /**
    * OGP::getErrorbyID()
    *
    * Returns the name of a supplied errorid
    *
    * @access public
    * @param integer $id the errorid
    * @return string The errormessage
    *
    */
    function getErrorbyID($id)
    {
        /*
        0 - Banned
        1 - Invalid Type: The query type in header is unkown
        2 - Invalid Value: Any value in header is incorrect
        3 - Invalid Challenge Number: The challenge number is incorrect
        4 - Invalid Query: The query body is incorrect
        */

        $id = $this->parseUint($id);

        switch($id)
        {
            case 0: return "0 - Banned";
            case 1: return "1 - Invalid Type: The query type in header is unkown";
            case 2: return "2 - Invalid Value: Any value in header is incorrect";
            case 3: return "3 - Invalid Challenge Number: The challenge number is incorrect";
            case 4: return "4 - Invalid Query: The query body is incorrect";

            default: return $id." - Unknown";
        }

    }

    /**
    * OGP::parseIP()
    *
    * returns the IP in standart dottet format
    * of the specified UINT32, false on failure.
    *
    * @access protected
    * @param string an integer of type UINT32 (4 bytes string)
    * @return string IP in dotted format, false on failure
    *
    */
    function parseIP($uint)
    {
        if(strlen($uint) < 4)
        {
            echo "<b>Warning:</b> String to short in parseIP();<br>";
            return false;
        }

        return $this->parseUint(substr($uint,3,1)).".".
                 $this->parseUint(substr($uint,2,1)).".".
                 $this->parseUint(substr($uint,1,1)).".".
                 $this->parseUint(substr($uint,0,1));
    }

    /**
    * OGP::getUint()
    *
    * Reads an unsigned integer of the specified type (valid: 8,16,32)
    * from the specified string $string, returns this value and removes ($length / 8)
    * bytes from $string.
    *
    * @access protected
    * @param string $string Any string, starting with an unsigned integer of the specified length
    * @param integer $length The type of the unsigned integer to read (8,16,32)
    * @return string the read unsigned integer of the specified type, false on failure
    *
    */
    function getUint(&$string, $length=8)
    {
        if(strlen($string) < 1)
        {
            echo "<b>Warning:</b> Empty String in getUint();<br>";
            return false;
        }

        $length = $length / 8;

        $uint = substr($string, 0, $length);
        $string = substr($string, $length);

        return $uint;
    }

    /**
    * OGP::parseUint()
    *
    * Parses the specified unsigned integer to an integer that
    * can be handled by PHP.
    *
    * @access protected
    * @param string $uint The binary unsigned integer to parse
    * @return integer The parsed unsigned integer
    *
    */
    function parseUint($uint)
    {
        if(strlen($uint) == 1)
        {
            $uint = unpack ("Cuint", $uint);
        }
        elseif(strlen($uint) == 2)
        {
            $uint = unpack ("vuint", $uint);
        }
        elseif(strlen($uint) == 4)
        {
            $uint = unpack ("Vuint", $uint);
        }

        return $uint['uint'];
    }

    /**
    * OGP::getInt()
    *
    * Reads a signed integer of the specified type (valid: 8,16,32)
    * from the specified string $string, returns this value and removes ($length / 8)
    * bytes from $string.
    *
    * @access protected
    * @param string $string Any string, starting with a signed integer of the specified length
    * @param integer $length The type of the signed integer to read (8,16,32)
    * @return string the read signed integer of the specified type, false on failure
    *
    */
    function getInt(&$string, $length=8)
    {
        if(strlen($string) < 1)
        {
            echo "<b>Warning:</b> Empty String in getInt();<br>";
            return false;
        }

        $length = $length / 8;

        $int = substr($string, 0, $length);
        $string = substr($string, $length);

        return $int;
    }

    /**
    * OGP::parseInt()
    *
    * Parses the specified signed integer to an integer that
    * can be handled by PHP.
    *
    * @access protected
    * @param string $uint The binary signed integer to parse
    * @return integer The parsed signed integer
    *
    */
    function parseInt($int)
    {
        if(strlen($int) == 1)
        {
            $int = unpack ("cint", $int);
        }
        elseif(strlen($int) == 2)
        {
            $int = unpack ("sint", $int);
        }
        elseif(strlen($int) == 4)
        {
            $int = unpack ("lint", $int);
        }

        return $int['int'];
    }

    /**
    * OGP::getVarBitArray()
    *
    * Reads a VarBitArray from $string,
    * and removes read data from $string.
    *
    * @access protected
    * @param string $string Any string, starting with a VarBitArray
    * @return VarBitArray an array filled with binary data, false on failure
    * @link http://www.open-game-protocol.org/spec/ogp_spec_v0.94.htm#VarBitArray_Type VarBitArray Specification
    *
    */
    function getVarBitArray(&$string)
    {
        if(strlen($string) < 1)
        {
            echo "<b>Warning:</b> Empty String in getVarBitArray();<br>";
            return false;
        }

        $varbitarray = array();
        $i=0;
        while(true)
        {
            $c = substr($string,0,1);
            $string = substr($string,1);

            $bin = decbin(ord($c));
            $bin = str_repeat("0", 8-strlen($bin)).$bin;

            $bin_array = array();
            for($x=7;$x>=0;$x--)
            {
                $b = substr($bin, $x, 1);
                $bin_array[7-$x] = $b;
            }
            $varbitarray[$i] = $bin_array;

            if($bin_array[7] != 1)
            {
                break;
            }
            $i++;
        }

        return $varbitarray;
    }

    /**
    * OGP::VarBitArray_toString()
    *
    * Writes the array of type VarBitArray to a string
    * containing a binary number.
    *
    * @access protected
    * @param VarBitArray The VarBitArray to parse
    * @return string The binary number, false on failure
    *
    */
    function VarBitArray_toString($array)
    {
        if(sizeof($array) < 1)
        {
            echo "<b>Warning:</b> Empty Array in VarBitArray_toString();<br>";
            return false;
        }

        $string = "";
        for($i=0;$i<sizeof($array);$i++)
        {
            if(!isset($array[$i]))
            {
                echo "<b>Warning:</b> Array not valid VarBitArray_toString();<br>";
                return false;
            }

            if($i < sizeof($array)-1)
            {
                $array[$i][7] = 1;
            }

            for($x=7;$x>=0;$x--)
            {
                if(!isset($array[$i][$x]))
                {
                    $array[$i][$x] = 0;
                }
                $string .= $array[$i][$x];
            }
        }

        return $string;
    }

    /**
    * OGP::getCharsbyBinary()
    *
    * Parses a binary number to binary data.
    *
    * @access protected
    * @param string $binary The binary number to parse
    * @return string The parsed binary string, false on failure
    *
    */
    function getCharsbyBinary($binary)
    {
        if(strlen($binary) < 1)
        {
            echo "<b>Warning:</b> Empty String in getCharsbyBinary();<br>";
            return false;
        }

        if(strlen($binary) / 8 != floor(strlen($binary) / 8)
        || strlen($binary) / 8 != ceil(strlen($binary) / 8))
        {
            echo "<b>Warning:</b> String must have length that can be devided by 8 in getCharsbyBinary();<br>";
            return false;
        }

        $string = "";

        $count = strlen($binary) / 8;
        for($i=0;$i<$count;$i++)
        {
            $string .= chr(bindec(substr($binary, $i * 8, 8)));
        }

        return $string;
    }

    /**
    * OGP::getSzString()
    *
    * Returns a null terminated String from $string,
    * and removes is from $string.
    *
    * @access protected
    * @param string $string The string starting with a null terminated string
    * @return string The result string
    *
    */
    function getSzString(&$string)
    {
        if(strlen($string) < 1)
        {
            echo "<b>Warning:</b> Empty String in getSzString();<br>";
            return false;
        }

        $szstring = substr($string, 0, strpos($string, "\x00"));
        $string = substr($string, strlen($szstring)+1);

        return $szstring;
    }

    /**
    * OGP::getStringColorInfo()
    *
    * Reads a string containing color information
    * from $string
    *
    * @access protected
    * @param string $string Any string starting with a StringColorInfo string
    * @return array the array filled with the color information
    * @link http://www.open-game-protocol.org/spec/ogp_spec_v0.94.htm#StringColorInfo_Type StringColorInfo Specification
    *
    */
    function getStringColorInfo(&$string)
    {
        $ColorSize = $this->getVarUint($string);

        $total_size = 0;
        $ColorSize_p = $this->parseUint($ColorSize_p);
        $Data = array();
        while($total_size < $ColorSize_p)
        {
            $ColorInfoEntry = $this->getStringColorInfoEntry($string);
            $total_size += $ColorInfoEntry['size'];
            array_push($Data, $ColorInfoEntry);
        }

        return $Data;
    }

    /**
    * OGP::getStringColorInfoEntry()
    *
    * Reads a StringColorInfoEntry containing color information
    * from $string.
    *
    * @access protected
    * @param string $string Any string starting with a StringColorInfoEntry string
    * @return array the array filled with the color information
    * @link http://www.open-game-protocol.org/spec/ogp_spec_v0.94.htm#StringColorInfoEntry_Type StringColorInfoEntry Specification
    *
    */
    function getStringColorInfoEntry(&$string)
    {
        if(strlen($string) < 1)
        {
            echo "<b>Warning:</b> Empty String in getStringColorInfo();<br>";
            return false;
        }

        $size_before = strlen($string);

        $DeltaPosition = $this->getVarUint($string);

        $ColorValue = $this->getUint($string, 8);
        if($this->parseInt($ColorValue) >= 0x90 ||
        $this->parseInt($ColorValue) <= 0x9F)
        {
            $ColorValue16 = $this->getUint($string, 16);
        }

        $size_after = strlen($string);
        $size = $size_before - $size_after;

        return array('DeltaPosition' => $DeltaPosition,
                                     'ColorValue' => $ColorValue,
                                     'ColorValue16' => $ColorValue16,
                                     'size' => $size);
    }

    /**
    * OGP::getVarUint()
    *
    * Reads a VarUint from $string
    *
    * @access protected
    * @param string $string Any string starting with a VarUint
    * @return string an unsigned integer in a binary string
    * @link http://www.open-game-protocol.org/spec/ogp_spec_v0.94.htm#VarUINT8-32_Type Specification
    *
    */
    function getVarUint(&$string)
    {
        if(strlen($string) < 1)
        {
            echo "<b>Warning:</b> Empty String in getVarUint();<br>";
            return false;
        }

        $uint = $this->getUint($string, 8);
        $uint2 = $this->parseUint($uint);

        if($uint2 <= 0xFD)
        {
            return $uint;
        }
        
        if($uint2 == 0xFE)
        {
            $uint = $this->getUint($string, 16);
            return $uint;
        }
        
        if($uint2 == 0xFF)
        {
            $uint = $this->getUint($string, 32);
            return $uint;
        }

        echo "<b>Warning:</b> Unknown type in getVarUint();<br>";
        return false;
    }

    /**
    * OGP::getVarSint()
    *
    * Reads a VarUint from $string
    *
    * @access protected
    * @param string $string Any string starting with a VarUint
    * @return string a signed integer in a binary string
    * @link http://www.open-game-protocol.org/spec/ogp_spec_v0.94.htm#VarSINT8-32_Type VarSint Specification
    *
    */
    function getVarSint(&$string)
    {
        if(strlen($string) < 1)
        {
            echo "<b>Warning:</b> Empty String in getVarSint();<br>";
            return false;
        }

        $int = $this->getInt($string, 8);
        $int2 = $this->parseInt($int);
        if($int2 <= 0x7F && -0x7E <= $int2)
        {
            return $int;
        }
        
        if($int2 == -0x80)
        {
            $int = $this->getInt($string, 16);
            return $int;
        }
        
        if($int2 == -0x7F)
        {
            $int = $this->getInt($string, 32);
            return $int;
        }

        echo "<b>Warning:</b> Unknown type in getVarSint();<br>";
        return false;
    }


    /**
    * OGP::color_web_to16bit()
    *
    * Converts a 32-Bit hexadecimal colorstring
    * to a 16-Bit colorstring
    *
    */
    function color_web_to16bit($color)
    {
        $r = hexdec(substr($color,0,2));
        $g = hexdec(substr($color,2,2));
        $b = hexdec(substr($color,4,2));

        $r = round($r / (255 / 31));
        $g = round($g / (255 / 63));
        $b = round($b / (255 / 31));

        $r = substr(decbin($r),-5);
        $g = substr(decbin($g),-6);
        $b = substr(decbin($b),-5);


        $r = str_pad($r, 5, "0", STR_PAD_LEFT);
        $g = str_pad($g, 6, "0", STR_PAD_LEFT);
        $b = str_pad($b, 5, "0", STR_PAD_LEFT);

        $res = bindec($r.$g.$b);
        return $res;
    }

    /**
    * OGP::color_16bit_toweb()
    *
    * Converts a 16-Bit colorstring
    * to a 32-Bit hexadecimal colorstring
    *
    */
    function color_16bit_toweb($color)
    {
        $binary = decbin($color);
        $binary = str_pad($binary, 16, "0", STR_PAD_LEFT);


        $r = bindec(substr($binary, 0,  5));
        $g = bindec(substr($binary, 5,  6));
        $b = bindec(substr($binary, 11, 5));


        $r = round($r * (255 / 31));
        $g = round($g * (255 / 63));
        $b = round($b * (255 / 31));

        $r = str_pad(dechex($r), 2, "0", STR_PAD_LEFT);
        $g = str_pad(dechex($g), 2, "0", STR_PAD_LEFT);
        $b = str_pad(dechex($b), 2, "0", STR_PAD_LEFT);

        return strtoupper($r.$g.$b);
    }

}

if(!function_exists("fragsort"))
{
    /**
    * Document::fragsort()
    *
    * Tool function to sort with
    * {@link http://www.php.net/manual/en/function.uasort.php uasort()}
    *
    * @link http://www.php.net/manual/en/function.uasort.php PHP's asort() Documentation
    *
    */
    function fragsort ($a, $b)
    {
        if ($a["frags"] == $b["frags"])
            return 0;
	
        if ($a["frags"] > $b["frags"])
        {
            return -1;
        }
        else
        {
            return 1;
        }
    }
}
?>

