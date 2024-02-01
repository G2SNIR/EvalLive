<?php


function extraction_uri($texteATrouver) 
{
    //echo "Bonjour";
    //echo '{"uri": "'.$_SERVER["REQUEST_URI"].'"}';
    // Récupération de tout ce qui se trouve après rest.php
    //$texteATrouver = "rest.php/";
    //$texteATrouver = "test_extraction_uri.php/";
    $tabChemin = array();
    $depart = strpos($_SERVER["REQUEST_URI"], $texteATrouver);
    // On teste si le mot recherché a été trouvé
    if($depart != false) 
    {
        if($depart+strlen($texteATrouver) < strlen($_SERVER["REQUEST_URI"]) )
        {
            $chemin = substr($_SERVER["REQUEST_URI"], $depart+strlen($texteATrouver));
            $tabChemin = explode('/',$chemin);
            //var_dump($tabChemin);
        }
    }
    return $tabChemin;
}

// TEST de la fonction 
//$tab_uri = extraction_uri("test_extraction_uri.php/");
//var_dump($tab_uri);



?>