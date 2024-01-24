<?php

function mask($message) {
	$b1 = 0x80 | (0x1 & 0x0f);
	$length = strlen($message);
	
	if ($length <= 125) {
		$header = pack("CC", $b1, $length);
	} elseif ($length > 125 && $length < 65536) {
		$header = pack("CCn", $b1, 126, $length);
	} elseif ($length >= 65536) {
		$header = pack("CCNN", $b1, 127, $length);
	}
	
	return $header.$message;
}

function send_message($message) {
	global $clients;
	
	foreach($clients as $changed_socket) {
		@socket_write($changed_socket, $message, strlen($message));
	}
	
	return true;
}


$protocol = "ws";
$host = "192.168.56.101";
$port = 8000;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
$retour = socket_bind($socket, 0, $port);
if($retour == false) die("WebSocket Server : echec de l'association avec le port 8000 (bind)");
$retour = socket_listen($socket);

echo "WebSocket Server : mise en écoute sur le port 8000\n";

//$clients = array($socket);

// Boucle d'acceptation des clients
//while (true) {
//	$changed = $clients;
//    $null = NULL;

//    socket_select($changed, $null, $null, 0, 10);

//    if (in_array($socket, $changed)) {
        echo "WebSocket Server : Attente d'un nouveau client\n";
        $socket_new = socket_accept($socket);
        echo "WebSocket Server : client accepté\n";
        $handshake = socket_read($socket_new, 1500 );
        echo "WebSocket Server : " . $handshake . "\n";

        $headers = array();
        //$protocol = (stripos($host, "local.") !== false) ? "ws" : "wss";
        $lines = preg_split("/\r\n/", $handshake);
        
        foreach ($lines as $line) {
            $line = chop($line);
            
            if (preg_match("/\A(\S+): (.*)\z/", $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }
        
        $secKey = $headers["Sec-WebSocket-Key"];
        $secAccept = base64_encode(pack("H*", sha1($secKey . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11")));
        
        $upgrade =
            "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: WebSocket\r\n" .
            "Connection: Upgrade\r\n" .
            "WebSocket-Origin: $host\r\n" .
            "WebSocket-Location: $protocol://$host:$port/~gaetan/chat\r\n" .
            "Sec-WebSocket-Version: 13\r\n" .
            "Sec-WebSocket-Accept:$secAccept\r\n\r\n";


        echo "WebSocket Server : Envoi de la réponse\n";
        echo $upgrade;    
        socket_write($socket_new, $upgrade, strlen($upgrade));

        socket_getpeername($socket_new, $ip);
	    $response = mask(json_encode(array("type" => "system", "message" => $ip . " connected")));

        echo "WebSocket Server : " . $response;

        socket_write($socket_new, $response, strlen($response));

	    //send_message($response);



//        $clients[] = $socket_new;
        
//        $header = socket_read($socket_new, 1024);
//        perform_handshaking($header, $socket_new, $host, $port);
    
        // socket_getpeername($socket_new, $ip);
        // $response = mask(json_encode(array("type" => "system", "message" => $ip . " connected")));
        // send_message($response);
        
        // $found_socket = array_search($socket, $changed);
        // unset($changed[$found_socket]);

    // }

    // foreach($changed as $changed_socket) {
        
    // }


// }
socket_close($socket_new);
socket_close($socket);

echo "WebSocket Server : fin du programme";


?>