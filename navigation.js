/* Au chargement...  */
// on regarde les cookies pour savoir si l'utilisateur n'est pas déjà authentifié !
if(getCookie('PHPSESSID') == "")
{
    afficherFormulaireConnexion();
}
else
{
    if(getCookie('statut') == 0)
        afficherListeDesEvals();
    if(getCookie('statut') == 1)
        afficherEval(getCookie('ideval'));
}
// S'il est déjà authentifié
  // s'il est PROFS
  // On regarde les cookies pour savoir 


function setCookie(cname, cvalue, exminutes)
{
    const d = new Date();
    d.setTime(d.getTime() + (exminutes*60*1000));
    let expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + "path=/";
}

function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }



document.getElementById("menu").addEventListener("click", nav_rendre_visible);

function nav_rendre_visible(event) {
    console.debug(event.type);
    console.debug(event.target);
    document.getElementById("nav").style.left = "0vh";
}

document.getElementById("div_connexion").addEventListener("click", nav_rendre_invisible);
function nav_rendre_invisible(event){
    //document.getElementById("nav").style.display = "none";
    document.getElementById("nav").style.left = "-20vh";

    afficherFormulaireConnexion();
}


function afficherFormulaireConnexion() {
fetch("form_connexion.html")
    .then(function (response) {  // La réponse est déclenchée dès que l'on reçoit l'entête HTTP. Il faut alors indiquer quel genre de données on va recevoir
        if(response.ok)
            return response.text();  // on indique ici le type de la réponse attendue (.text()  .json() ...)
        throw new Error("Erreur du statut");
    })
    .then(function(formulairehtml) {  // on récupère la réponse
        document.getElementById("conversation").innerHTML = formulairehtml;
        document.getElementById("button_valider_connexion").addEventListener("click", envoyerFormulaireConnexion);
    })
    .catch(function(error) {
        console.log(error);
    });
}


function envoyerFormulaireConnexion(event) {
//console.debug("mot de passe : " + CryptoJS.SHA512(document.getElementById("input_motdepasse").value));
let jsontext = '{"formulaire":"form_connexion","identifiant":"' + document.getElementById("input_identifiant").value + '", "motdepasse":"' +
CryptoJS.SHA512(document.getElementById("input_motdepasse").value) + '"}';
// tab['identifiant'] = document.getElementById("input_identifiant").value;
// tab['motdepasse'] = CryptoJS.SHA512(document.getElementById("input_motdepasse").value);
// let jsontext = JSON.stringify(tab);
//console.debug("JSON : ");
//console.debug(jsontext);
fetch("rest.php" , {
        method: "POST", // *GET, POST, PUT, DELETE, etc.
        cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
        headers: {
        "Content-Type": "application/json",
        },
        body: jsontext, // body data type must match "Content-Type" header
    })
    .then(function(response) {
        if(response.ok)
            return response.json();
        throw new Error("STATUS ERROR : " + response.status);
    })
    .then(function(jsondata) {
        console.debug(jsondata);
        //let json = JSON.parse(jsondata);
        if(jsondata.form_connexion == "reussie")
        {
            //console.debug("identifiant : " + jsondata.identifiant);
            //console.debug("groupe : " + jsondata.groupe);
            document.getElementById("p_resultat_connexion").innerHTML = "";
            // Création des cookies de la session
            //setCookie("identifiant", jsondata.identifiant, 30);
            //setCookie("groupe", jsondata.groupe, 30);

            afficherListeDesEvals();
        }
        else
        {
            document.getElementById("p_resultat_connexion").innerHTML = "Echec de l'authentification.";
        }
    })
    .catch(function(error) {
        console.debug("ERREUR TRAITEMENT FORMULAIRE CONNEXION : " + error);
    });
}

/*  Formulaire d'une nouvelle eval live  */
function afficherFormulaireNouvelleEval() {
    fetch("form_nouvelle_eval.html")
        .then(function (response) {  // La réponse est déclenchée dès que l'on reçoit l'entête HTTP. Il faut alors indiquer quel genre de données on va recevoir
            if(response.ok)
                return response.text();  // on indique ici le type de la réponse attendue (.text()  .json() ...)
            throw new Error("Erreur du statut");
        })
        .then(function(formulairehtml) {  // on récupère la réponse
            document.getElementById("conversation").innerHTML = formulairehtml;
            document.getElementById("button_valider_connexion").addEventListener("click", envoyerFormulaireConnexion);
        })
        .catch(function(error) {
            console.log(error);
        });
}

function afficherListeDesEvals() {
    fetch("rest.php/eval" , {
        method: "GET", // *GET, POST, PUT, DELETE, etc.
        cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
    })
    .then(function(response) {
        if(response.ok)
            return response.json();
        throw new Error("STATUS ERROR : " + response.status);
    })
    .then(function(jsondata) {
        console.debug(jsondata);
        if(jsondata.erreur) 
        { 
            alert(jsondata.erreur);
            afficherFormulaireConnexion();
        }
        console.debug(jsondata.length);
        var html = "<table><tr><th>Titre</th><th>Groupe</th><th>Date</th><th>Type</th><th>Statut</th><th>ws_port</th><th>Action</th></tr>";
        for(let i=0 ; i<jsondata.length ; i++)
        {
            html += "<tr><td>"  + jsondata[i].titre + "</td><td>" 
                                + jsondata[i].idgroupe + "</td><td>"
                                + jsondata[i].date + "</td><td>"
                                + jsondata[i].type + "</td><td>"
                                + jsondata[i].statut + "</td><td>"
                                + jsondata[i].ws_port + "</td>";
            if(typeof jsondata[i].action != "undefined")
            {
                console.debug("Le bouton action n'est pas indéfini ! ");
                console.debug("indice : "+ i);
                console.debug(jsondata[i].action);
                if(jsondata[i].action == "Start")
                { 
                    console.debug("DEMARRER!!!");                   
                    html += "<td><button id=\""+jsondata[i].ideval+"\" onclick=\"demarrerEval("+jsondata[i].ideval+");\">D&eacute;marrer</button></td>";
                }
                else if(jsondata[i].action == "Stop")
                {
                    console.debug("STOP!!!");
                    html += "<td><button id=\""+jsondata[i].ideval+"\" onclick=\"arreterEval("+jsondata[i].ideval+");\">Stop</button></td>";
                }
            }
            else
            {
                html += "<td></td>";
            }
            html += "<td></tr>";

        }
        html += "</table>";
        document.getElementById("conversation").innerHTML = html;
    })
}   

function demarrerEval(ideval)
{
    console.debug("Démarrage de l'éval");
    //console.debug(event.target.id);
    //var ideval = event.target.id;

    fetch("rest.php/eval/"+ideval+"/start" , {
        method: "GET", // *GET, POST, PUT, DELETE, etc.
        cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
    })
    .then(function(response) {
        if(response.ok)
            return response.json();
        throw new Error("STATUS ERROR : " + response.status);
    })
    .then(function(jsondata) {
        console.debug(jsondata);
        // Vérifier que ça a marché

        //console.debug(jsondata.websocket);
        //console.log("Executed now");
        // On efface le contenu de la section principale
        document.getElementById("conversation").innerHTML = "";
        
        // Après un délai de 200 ms, on réffiche l'état des evals
        setTimeout(function(){
            console.log("Executed after 1 second");
            //afficherListeDesEvals();
            afficherEval(ideval);
        }, 200);
    })

}

function arreterEval(ideval)
{
    console.debug("Arret de l'éval");
    //console.debug(event.target.id);
    //var ideval = event.target.id;

    fetch("rest.php/eval/"+ideval+"/stop" , {
        method: "GET", // *GET, POST, PUT, DELETE, etc.
        cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
    })
    .then(function(response) {
        if(response.ok)
            return response.json();
        throw new Error("STATUS ERROR : " + response.status);
    })
    .then(function(jsondata) {
        console.debug(jsondata);
        // Vérifier que ça a marché
        //console.debug(jsondata.websocket);
        //console.log("Executed now");

        // On efface le contenu de la section principale
        document.getElementById("conversation").innerHTML = "";
        
        // Après un délai de 200 millisecondes (le temps du démarrage du serveur websocket), on affiche l'état des évals
        setTimeout(function(){
            console.log("Exécuté après 200 millisecondes");
            afficherListeDesEvals();
        }, 200);

        
        // rafraichir la liste des eval -> port devrait apparaître + pid
    })

}


function afficherEval(ideval)
{
    console.debug("Affichage de l'eval " + ideval);
    fetch("rest.php/eval/"+ideval , {
        method: "GET", // *GET, POST, PUT, DELETE, etc.
        cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
    })
    .then(function(response) {
        if(response.ok)
            return response.json();
        throw new Error("STATUS ERROR : " + response.status);
    })
    .then(function(jsondata) {
        console.debug(jsondata);
        // On crée les formulaires pour chaque question

        let html = '<button id="'+ideval+'" onclick="arreterEval('+ideval+');">Stop</button>';
        html += '<div id="E'+ideval+'" class="eval" name="'+ideval+'">';
        for(let i=0 ; i<jsondata.length ; i++)
        {
            html += '<form id="Q'+jsondata[i].idquestion+'" class="question" name="'+jsondata[i].idquestion+'">';
            html += '<p>Q'+(i+1)+') '+jsondata[i].question+' /'+jsondata[i].note+'</p>';
            if(jsondata[i].type == "LIBRE")
            {
                html += '<label><input id="R'+(i+1)+'" type="text" /></label>';
            }
            else if(jsondata[i].type == "QCM")
            {
                for(let j=0; j<jsondata[i].choix.length ; j++)
                {
                    html += '<label><input type="checkbox" id="R'+(i+1)+'C'+(j+1)+'">'+jsondata[i].choix[j].proposition+'</label>';
                }
            }
            html += '</form>';
        }
        html += '</div>';
        document.getElementById("conversation").innerHTML = html;
    })
}

