websocket = new WebSocket("ws://192.168.56.101:8000/");
websocket.onopen = function (event) {
    console.log("on open");
    //console.log(event.code);
};
websocket.onclose = function(event) {
    console.log("on close");
    console.log(event.code);
};
websocket.onerror = function (event) {
    console.log("on close");
    //console.log(event.code);
};
websocket.onmessage = function(event) {
    var data = JSON.parse(event.data);
    console.log(data);
    if(data.type == "maj")
    {
        document.getElementById("websocket").innerHTML += "<p>"+data.message+"</p>";
    }
};

document.getElementById("input_button_valider").addEventListener("click", Envoyer);
function Envoyer()
{
    let input_text_message = "NQ";
    websocket.send('{"type":"maj", "message":"'+input_text_message+'"}');
}