<?php

// Code récupéré sur https://www.php.net/manual/en/function.socket-create.php

// SERVEUR SOCKET DGRAM UNIX DOMAIN
umask(0);
//unlink("/tmp/server.sock");

if (!extension_loaded('sockets')) {
    die('The sockets extension is not loaded.');
}
// create unix udp socket
$socket = socket_create(AF_INET, SOCK_DGRAM, 0);
if (!$socket)
        die('Unable to create AF_UNIX socket');

// same socket will be used in recv_from and send_to
//$server_side_sock = "/tmp/server.sock";
if (!socket_bind($socket, "127.0.0.1", 7999))
        die("Unable to bind to 127.0.0.1:7999");

while(1) // server never exits
{
    // receive query
    if (!socket_set_block($socket))
            die('Unable to set blocking mode for socket');
    $buf = '';
    $from_ip = '';
    $from_port = 0;
    echo "Ready to receive...\n";
    // will block to wait client query
    $bytes_received = socket_recvfrom($socket, $buf, 65536, 0, $from_ip, $from_port);
    if ($bytes_received == -1)
            die('An error occured while receiving from the socket');
    echo "Lancement de l'eval live $buf\n";

    //Extraction des données reçues
    $buf_exploded = explode(' ', $buf);
    if($buf_exploded[0] == "START")
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid == 0) {
            // we are the child
            socket_close($socket);
            exec("php -q websock_evallive.php 192.168.56.101 8000 ".$buf_exploded[1]." > websock.out");
            die();
        } else {
            // we are the parent 
            $rep = "OK"; // process client query here
            // send response
    //        if (!socket_set_nonblock($socket))
    //                die('Unable to set nonblocking mode for socket');
            // client side socket filename is known from client request: $from
            $len = strlen($rep);
            $bytes_sent = socket_sendto($socket, $rep, $len, 0, $from_ip, $from_port);
            if ($bytes_sent == -1)
                    die('An error occured while sending to the socket');
            else if ($bytes_sent != $len)
                    die($bytes_sent . ' bytes have been sent instead of the ' . $len . ' bytes expected');
            echo "Request processed\n";

        }
    }
    else if($buf_exploded[0] == "STOP")
    {
        // On récupère le pid de l'eval en allant dans la BDD
        $db="eval";
        $dbhost="localhost";
        $dbport=3306;
        $dbuser="eval";
        $dbpasswd="eval";
        
        try {
            $pdo = new PDO('mysql:host='.$dbhost.';port='.$dbport.';dbname='.$db.'', $dbuser, $dbpasswd);
        } catch (PDOException $e) {
            print '{"Error": "' . $e->getMessage() . '"}';
            die();
        }
    
    
        $pdo->exec("SET CHARACTER SET utf8");
        $stmt = $pdo->prepare("SELECT pid FROM eval WHERE ideval=?");
        $stmt->bindParam(1,$buf_exploded[1]);
        $stmt->execute();
        $tab = $stmt->fetchAll();

        $pid = $tab[0]["pid"];
        // On arrête le processus
        echo "kill -15 $pid";
        exec("kill -15 $pid");
        $rep = "OK"; // process client query here
            // send response
    //        if (!socket_set_nonblock($socket))
    //                die('Unable to set nonblocking mode for socket');
            // client side socket filename is known from client request: $from
            $len = strlen($rep);
            $bytes_sent = socket_sendto($socket, $rep, $len, 0, $from_ip, $from_port);
            if ($bytes_sent == -1)
                    die('An error occured while sending to the socket');
            else if ($bytes_sent != $len)
                    die($bytes_sent . ' bytes have been sent instead of the ' . $len . ' bytes expected');
            echo "Request processed\n";
    }

}




?>