<?php 

    session_start();

    include_once("extraction_uri.php");

    include_once("./websockets/client_unix.php");



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

    $tab_uri = extraction_uri("rest.php/");
    //var_dump($tab_uri);
    // Gestion de l'authentification
    if(!isset($_COOKIE["PHPSESSID"]))
    {
        // Gestion de l'authentification par l'envoie d'un fichier json en POST
        if($_SERVER ['REQUEST_METHOD'] == "POST")
        {
            $json = file_get_contents('php://input');
            //echo $json;
            $data = json_decode($json, true);
            // SI ON RECOIT LE FORMULAIRE DE CONNEXION
            if($data["formulaire"] == "form_connexion")
            {
                $stmt = $pdo->prepare("SELECT utilisateur.identifiant AS identifiant, groupe.nom AS nom_groupe, utilisateur.idutilisateur AS idutilisateur FROM utilisateur INNER JOIN groupe ON groupe.idgroupe = utilisateur.idgroupe WHERE identifiant=? AND motdepasse=?");
                $stmt->bindParam(1,$data["identifiant"]);
                $stmt->bindParam(2,$data["motdepasse"]);
                $stmt->execute();
                $tab = $stmt->fetchAll();
                if(count($tab) != 1 )
                {
                    echo '{"form_connexion":"echec"}';
                    die();
                }
                else
                {
                    //var_dump($tab[0]["identifiant"]);
                    echo '{"form_connexion":"reussie", "identifiant":"' . $tab[0]["identifiant"] . '", "groupe":"' . $tab[0]["nom_groupe"] . '"}';
                    $_SESSION["identifiant"] = $tab[0]["identifiant"];
                    $_SESSION["idutilisateur"] = $tab[0]["idutilisateur"];
                    $_SESSION["nom_groupe"] = $tab[0]["nom_groupe"];
                    setcookie("statut","0",time()+60*15,"/");
                    die();
                }
            }
        }
        // Sinon
        echo '{"erreur" : "Vous n\'êtes pas autorisé"}';
        session_destroy();
        unset($_COOKIE["PHPSESSID"]);
        setcookie("PHPSESSID",'',-1,'/');
        setcookie("statut","1",time()-60,"/");               // 15 minutes
        setcookie("ideval",$tab_uri[1],time()-60,"/");          // 15 minutes
        die();
    }

    // Si j'ai un cookie PHPSESSID mais que je n'ai pas idutilisateur dans $_SESSION 
    if(isset($_COOKIE["PHPSESSID"]) && !isset($_SESSION["idutilisateur"]))
    {
        echo '{"erreur" : "La session a expirée, veuillez vous reconnecter"}';
        session_destroy();
        unset($_COOKIE["PHPSESSID"]);
        setcookie("PHPSESSID",'',-1,'/');
        die();
    }


    switch ($_SERVER ['REQUEST_METHOD'])
    {
        case "GET" :
            /**************************************
             *  rest.php/eval
             * ********************************** */
            if((sizeof($tab_uri) == 1) && ($tab_uri[0] == "eval"))
            {
                $stmt = $pdo->prepare("SELECT * FROM eval");
                $stmt->execute();
                $tab = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($tab as $key => $eval)
                {
                    if($eval["statut"] == 1 && $eval["ws_port"] == 0 && $eval["pid"] == 0)
                    {
                        $tab[$key]["action"] = "Start";  // Boutton Démarrer dans les actions
                    }
                    else if($eval["statut"] == 1 && $eval["ws_port"] != 0 && $eval["pid"] != 0)
                    {
                        $tab[$key]["action"] = "Stop";  // Boutton Démarrer dans les actions
                    }
                }
                //var_dump($tab);
                echo json_encode($tab);
                die();
            }
            /**************************************
             *  rest.php/eval/X/start
             * ********************************** */
            else if((sizeof($tab_uri) == 3) && ($tab_uri[0] == "eval") && ($tab_uri[2] == "start"))
            {
                if($_SESSION["nom_groupe"] == "PROFS")
                {
                    //$retour = system("nohup php /home/gaetan/public_html/EvalLive/websockets/websock_evallive.php 192.168.56.101 8000 ".$tab_uri[1]." > nohup.out & > /dev/null");
                    $retour = lancementWebsocketServerPourEvalLive($tab_uri[1], $err);
                    if($retour == 0)
                    {
                        echo '{"websocket" : "Le serveur websocket est démarré : '.$retour.'"}';
                        setcookie("statut","1",time()+60*15,"/");               // 15 minutes
                        setcookie("ideval",$tab_uri[1],time()+60*15,"/");          // 15 minutes
                    }
                    else
                    {
                        echo '{"erreur":"Le serveur websocket n\'est pas démarré. Erreur n° '.$retour.' : '.$err.'"}';
                        setcookie("statut","0",time()+60*15,"/");
                    }
                }
                die();
            }
            /**************************************
             *  rest.php/eval/X/stop
             * ********************************** */
            else if((sizeof($tab_uri) == 3) && ($tab_uri[0] == "eval") && ($tab_uri[2] == "stop"))
            {
                $retour = arretWebsocketServerPourEvalLive($tab_uri[1], $err);
                echo '{"websocket" : "Le serveur websocket est bien arrêté"}';
                setcookie("statut","0",time()+60*15,"/");
            }
             /**************************************
             *  rest.php/eval/X/
             * ********************************** */
            else if((sizeof($tab_uri) == 2) && ($tab_uri[0] == "eval"))
            {
                $stmt = $pdo->prepare("SELECT idquestion, question, type, note FROM question WHERE ideval=?");
                $stmt->bindParam(1,$tab_uri[1]);
                $stmt->execute();
                $tab = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($tab as $key => $element)
                {
                    if($element["type"] == "QCM")
                    {
                        $stmt2 = $pdo->prepare("SELECT proposition FROM choix WHERE idquestion=?");
                        $stmt2->bindParam(1,$element["idquestion"]);
                        $stmt2->execute();
                        $tab2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                        $tab[$key]["choix"] = $tab2;
                    }
                }
                echo json_encode($tab);
                setcookie("statut","1",time()+60*15,"/");               // 15 minutes
                setcookie("ideval",$tab_uri[1],time()+60*15,"/");          // 15 minutes
            }
            /**************************************
             *  rest.php/session
             * ********************************** */
            else if((sizeof($tab_uri) == 1) && ($tab_uri[0] == "session"))
            {
                echo json_encode($_SESSION);
                die();
            }
            break;

        case "POST" :
            $json = file_get_contents('php://input');
            //echo $json;
            $data = json_decode($json, true);
            // SI ON RECOIT LE FORMULAIRE DE CONNEXION
            if($data["formulaire"] == "form_connexion")
            {
                $stmt = $pdo->prepare("SELECT utilisateur.identifiant AS identifiant, groupe.nom AS nom_groupe, utilisateur.idutilisateur AS idutilisateur FROM utilisateur INNER JOIN groupe ON groupe.idgroupe = utilisateur.idgroupe WHERE identifiant=? AND motdepasse=?");
                $stmt->bindParam(1,$data["identifiant"]);
                $stmt->bindParam(2,$data["motdepasse"]);
                $stmt->execute();
                $tab = $stmt->fetchAll();
                if(count($tab) != 1 )
                {
                    echo '{"form_connexion":"echec"}';
                }
                else
                {
                    //var_dump($tab[0]["identifiant"]);
                    echo '{"form_connexion":"reussie", "identifiant":"' . $tab[0]["identifiant"] . '", "groupe":"' . $tab[0]["nom_groupe"] . '"}';
                    $_SESSION["identifiant"] = $tab[0]["identifiant"];
                    $_SESSION["idutilisateur"] = $tab[0]["idutilisateur"];
                    $_SESSION["nom_groupe"] = $tab[0]["nom_groupe"];
                    setcookie("statut","0",time()+60*15,"/"); // 10 minutes
                }
            }
        
            break;
        default : break;
    }


?>