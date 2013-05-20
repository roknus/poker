/**
* Connecte le client à la table identifié par le numero de port donnée en URL
*/
function connexionClient(){
    var data = {num_port : num_port};
    $.ajax({
	url : "/~flucia/client/connection/connection_table.php",
	data : data,
	complete : function(xhr,result){
	    if(result != "success") return;
	    var response = xhr.responseText;
	}
    });
}

/**
 * Déconnecte le client de la table courante puis va le connecté au menu principal
 */
function fermetureClient(){
    $.ajax({
	url : "/~flucia/client/connection/fermeture_client.php",
	complete : function(xhr, result){
	    if(result != "success") return;
	    var response = xhr.responseText;
	}
    });
}

/**
 * Propose au client de s'asseoir à un siege et lui demande le nombre de jetons qu'il désire 
 * jouer dans le cas d'un cash game
 */
function choixPlace(place){
    $('.select').empty();
    if(style_jeu == 0){
	$('<div id="select_money"><span><strong>Choisissez les jetons que vous désirez jouer</strong></span><br/><div id="cave"></div><div><input type="number" id="valeur_cave" size="10"><input type="button" value="Envoyer" onclick="javascript:ajaxChoixPlace('+place+')" /></div></div>').appendTo('.select');
	$('#cave').slider({
	    min : mise_min,
	    max : mon_argent,
	    value : mise_min,
	    step : 0.01,
	    slide : function (event){
		$("#valeur_cave").html($(this).slider("value"));
	    },
	    stop : function (event){
		$("#valeur_cave").val($(this).slider("value"));
	    }
	});
    }
    else if(style_jeu == 1){
	$('<div id="select_money"><span><strong>Voulez-vous choisir cette place et jouer '+mise_min+' jetons?</strong></span><br/><button onclick="javascript:ajaxChoixPlace('+place+')">S\'asseoir</button><button onclick="$(\'.select\').empty()">Annuler</buton></div>').appendTo('.select');
    }
}
/**
 * Previens le serveur de table que le joueur ne participera pas à la prochaine manche
 */
function quitterTableNext(){
    $.ajax({
	url : "/~flucia/client/table/quitte_table_next.php",
	complete : function(xhr,result){
	    if(result != "success") return;
	    var response = xhr.responseText;
	}
    });
}

/**
 * Previens le serveur de table que le joueur n'a pas joueur dans le temps qu'il lui été impartit pour miser
 */
function absent(){
    $.ajax({
	url : "/~flucia/client/table/absent.php",
	complete : function(xhr,result){
	    if(result != "success") return;
	    var response = xhr.responseText;
	}
    });
}

/**
 * Previens le serveur de table que le joueur n'est plus considéré comme absent
 */
function retour_en_jeu(){
    $.ajax({
	url : "/~flucia/client/table/retour_en_jeu.php",
	complete : function(xhr,result){
	    if(result != "success") return;
	    var response = xhr.responseText;
	    $('#absent').remove();
	    $('#absent_background').remove();
	}
    });
}

/**
 * Envoie un message de chat au seveur de table
 */
function envoyer_message(message){     
    message = message.replace(/&/g,"{esp}");
    var data = { chat : message };
    $.ajax({
	url : "/~flucia/client/table/chat.php",
	data : data,
	complete : function(xhr,result){
	    if(result != "success") return;
	    var response = xhr.responseText;
	    $("#chat_input").val("");
	}
    });
}

/**
 * Envoie la place et le nombre de jetons que le client désire joueur au serveur de table, le nombre de jetons sera égale
 * à la cave minimum pour un style de jeu sit & go
 */
function ajaxChoixPlace(place){
    if(style_jeu == 0)
    {
	var cave = $("#valeur_cave").val();
    }
    else if(style_jeu == 1)
    {
	var cave = mise_min;
    }
    var data = { place : place, cave : cave };
    $.ajax({
	url : "/~flucia/client/table/choix_place.php",
	data : data,
	complete : function(xhr, result){
	    if(result != "success") return; 
	    var response = xhr.responseText;
	    ma_place = place;
	    var i;
	    for(i=0;i<10;i++){
		if($('#player'+i).html() == '<strong>Choisissez votre siège.</strong>'){
		    $('#player'+i).html('');
		}
	    }
	}
    });		       
}	

function miser(mise){
    $("#relance_div").slideUp();
    if(mise == 0){
	ajax_mise(mise);
    }
    else if(mise == 1){
	var sui = Math.round((mise_suivre - joueurs[ma_place][2])*100)/100;
	if(sui < 0){
	    ajax_mise(joueurs[ma_place][1]);
	}
	else{
	    ajax_mise(sui);
	}
    }
    else if(mise == 2){
	var min;
	min = (mise_suivre*2 - joueurs[ma_place][2]);
	if(min < mise_min){
	    if(joueurs[ma_place][1] < mise_min){
		min = joueurs[ma_place][1];
	    }
	    else{
		min = mise_min;
	    }
	}
	
	if($("#relance_div").css('display') == "none"){
	    $('#valeur_relance').html(min);
	    $("#slider").slider({
		min : min,
		max : joueurs[ma_place][1],
		value : min,
		step : 0.01,
		slide : function (event){
		    $("#valeur_relance").html($(this).slider("value"));
		},
		stop : function (event){
		    $("#valeur_relance").html($(this).slider("value"));
		}
	    });
	    $("#relance_div").slideDown();
	    $("#valeur_relance").html($(this).slider("value"));	
	}
	else{
	    ajax_mise($("#slider").slider("value"));
	}
    }
    else if(mise == -1){
	ajax_mise(mise);
    }
}

function ajax_mise(mise){
    mise = mise;
    var data = { miser : mise};
    $.ajax({
	url : "/~flucia/client/table/miser.php",
	data : data,
	complete : function(xhr,result){
	    if(result != "success") return; 
	    var response = xhr.responseText;
	}
    });
}

function ajout_message_systeme(message){
    $('<span class="message_systeme"><strong>[Système]</strong></span><span class="message_chat_systeme"> : '+message+'<br/></span>').appendTo('#chat_box');
    var scroll = $("#chat_box").scrollTop();
    $("#chat_box").scrollTop(scroll +100);
}
