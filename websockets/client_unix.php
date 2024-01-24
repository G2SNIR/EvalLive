<?php
// Code récupéré sur : https://www.php.net/manual/en/function.socket-create.php
// CLIENT SOCKET DGRAM UNIX DOMAIN

// TEST de la fonction
//lancementWebsocketServerPourEvalLive(1, $err);

// Cette fonction renvoie un nombre négatif en cas d'erreur, 0 si tout est ok.
function lancementWebsocketServerPourEvalLive($numEvalLive, &$erreur)
{
    $erreur = "";

    if (!extension_loaded('sockets')) 
    {
        $erreur = 'The sockets extension is not loaded.';
        return -1;
    }
    // create unix udp socket
    $socket = socket_create(AF_INET, SOCK_DGRAM, 0);
    if (!$socket)
    {
        $erreur = 'Unable to create AF_UNIX socket';
        return -2;
    }

    // same socket will be later used in recv_from
    // no binding is required if you wish only send and never receive

    // use socket to send data
    if (!socket_set_nonblock($socket))
    {
        $erreur = 'Unable to set nonblocking mode for socket';
        return -5;
    }
    // server side socket filename is known apriori
    $msg = "START ".$numEvalLive;
    $len = strlen($msg);
    // at this point 'server' process must be running and bound to receive from serv.sock
    $bytes_sent = socket_sendto($socket, $msg, $len, 0, "127.0.0.1", 7999);
    if ($bytes_sent == -1)
    {
        $erreur = 'An error occured while sending to the socket';
        return -6;
    }
    else if ($bytes_sent != $len)
    {
        $erreur = $bytes_sent . ' bytes have been sent instead of the ' . $len . ' bytes expected. Erreur : '.socket_last_error($socket).' : '. socket_strerror(socket_last_error($socket));
        return -7;
    }

    // use socket to receive data
    if (!socket_set_block($socket))
    {
        //die('Unable to set blocking mode for socket');
        return -10;
    }
    $buf = '';
    $from_ip = '';
    $from_port = 0;
    // will block to wait server response
    $bytes_received = socket_recvfrom($socket, $buf, 65536, 0, $from_ip, $from_port);
    if ($bytes_received == -1)
    {
        //die('An error occured while receiving from the socket');
        return -11;
    }
    if($buf != "OK")
    {
        return -12;
    }
    //echo "Received $buf from $from_ip:$from_port\n";

    // close socket and delete own .sock file
    socket_close($socket);
    return 0;
}


function arretWebsocketServerPourEvalLive($numEvalLive, &$erreur)
{
    $erreur = "";

    if (!extension_loaded('sockets')) 
    {
        $erreur = 'The sockets extension is not loaded.';
        return -1;
    }
    // create unix udp socket
    $socket = socket_create(AF_INET, SOCK_DGRAM, 0);
    if (!$socket)
    {
        $erreur = 'Unable to create AF_UNIX socket';
        return -2;
    }

    // same socket will be later used in recv_from
    // no binding is required if you wish only send and never receive

    // use socket to send data
    if (!socket_set_nonblock($socket))
    {
        $erreur = 'Unable to set nonblocking mode for socket';
        return -5;
    }
    // server side socket filename is known apriori
    $msg = "STOP ".$numEvalLive;
    $len = strlen($msg);
    // at this point 'server' process must be running and bound to receive from serv.sock
    $bytes_sent = socket_sendto($socket, $msg, $len, 0, "127.0.0.1", 7999);
    if ($bytes_sent == -1)
    {
        $erreur = 'An error occured while sending to the socket';
        return -6;
    }
    else if ($bytes_sent != $len)
    {
        $erreur = $bytes_sent . ' bytes have been sent instead of the ' . $len . ' bytes expected. Erreur : '.socket_last_error($socket).' : '. socket_strerror(socket_last_error($socket));
        return -7;
    }

    // use socket to receive data
    if (!socket_set_block($socket))
    {
        //die('Unable to set blocking mode for socket');
        return -10;
    }
    $buf = '';
    $from_ip = '';
    $from_port = 0;
    // will block to wait server response
    $bytes_received = socket_recvfrom($socket, $buf, 65536, 0, $from_ip, $from_port);
    if ($bytes_received == -1)
    {
        //die('An error occured while receiving from the socket');
        return -11;
    }
    if($buf != "OK")
    {
        return -12;
    }
    //echo "Received $buf from $from_ip:$from_port\n";

    // close socket and delete own .sock file
    socket_close($socket);
    return 0;
}


?>