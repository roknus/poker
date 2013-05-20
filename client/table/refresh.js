/**
 * Fonction qui va mettre a jour toute les informations ainsi qu'interpreter les requêtes reçu depuis le serveur de table
 */

function refresh(){
    $.ajax({
	url : "/~flucia/client/table/actualiser_joueurs.php",
	complete : function(xhr,result){
	    if(result != "success") return; 
	    var response = xhr.responseText;
	    if(res != 'RAS')
	    { 
		/**
		 * Si le serveur a reçu des informations du serveur de table on les récupère dans un tableau res
		 * Puis on parcours les differentes requêtes.
		 * @var res tableau contenant les differentes requetes, il est initialisé grâce à la tokenisation de response
		 * @var i compteur avec lequel on parcours res
		 */
		var res = response.split('&'); 
		var i = 0;
		while(res[i])
		{
		    if(res[i] == 'InfoT')
		    {
			/**
			 * Le serveur nous informe de l'état actuel de la table, cette information est reçu lors de la connection à la
			 * table pour initialiser les varibles.
			 * @var pot donne la valeur actuelle du pot
			 * @var dealer donne la place actuelle du dealer
			 * @var placePB donne la place actuelle de la petite blind
			 * @var placeGB donne la place actuelle de la grosse blind
			 * @var mise_min donne la valeur de la mise minimum
			 * @var suivant donne la place du suivant à jouer
			 */
			pot = parseFloat(res[i+1]);
			$('.pot').html(pot);
			dealer = parseInt(res[i+2]);
			placePB = parseInt(res[i+3]);
			placeGB = parseInt(res[i+4]);
			mise_min = parseFloat(res[i+5]);
			suivant = parseInt(res[i+6]);
			
			i = i+7; // On deplace le curseur pour la boucle qui suit							    
		    } 
		    while(res[i] == 'NewJo')
		    {
			/**
			 * Informations relative à un joueurs présent sur la table
			 * @var place siège qu'occupe le joueur
			 * @var res[i+2] Nom du joueur
			 * @var res[i+3] Nombre de jetons que le joueurs possède pour la partie en cour
			 * @var res[i+4] Mise du joueur pour le tour actuel
			 * @var res[i+5] Etat dans lequel se trouve le joueur ( t = le joueur est coucher, f = le joueur est encore dans la manche en cour )
			 */
			place = parseInt(res[i+1]);
			joueurs[place][0] = res[i+2];
			joueurs[place][1] = parseFloat(res[i+3]);
			joueurs[place][2] = parseFloat(res[i+4]); 
			joueurs[place][3] = res[i+5];
			
			ajout_message_systeme(joueurs[place][0]+" vient de s'asseoir à la place n°"+(place+1)+".");
			
			if(joueurs[place][0] == mon_pseudo)
			{
			    /**
			     * Si le nouveau joueur est le client on lui efface le panel de droite ou il a sélectionner ses jetons
			     */
			    ma_place = place;
			    $(".select").empty();						  
			}
			if(joueurs[place][3] == 't')
			{
			    /**
			     * Si le joueur est coucher on l'affiche dans sa mise
			     */
			    joueurs[place][2] = 'COUCHER';
			}
			/**
			 * Créer la case du joueur avec son pseudo, ses jetons et sa mise à la place qu'il a choisit
			 */
			$('#player'+place).html('<table><tr><td id="player_status_bar"><span id="player_status"></span></td><td id="timer_box"><span id="timer"></span></td></tr><tr><td colspan="2"><strong><span class="pseudo">'+joueurs[place][0]+' : </span></strong><span id="jetons">'+joueurs[place][1]+'</span><img src="icone_money.png" heigth="12" width="12"/></td></tr><tr><td colspan="2"><span id="mise">'+joueurs[place][2]+'</span></td></tr><tr><td colspan="2" id="cartes_gagnant"></td></tr></table>');
			
			/**
			 * Met les icones dans les barres de status du dealer, petite blind et grosse blind
			 */
			if( dealer == place)
			{
			    $('<img src="couronne.png" height="20" width="35" />').appendTo('#player'+dealer+' #player_status');
			}
			if( placePB == place)
			{
			    $('<img src="pb.png" height="20" width="30" />').appendTo('#player'+placePB+' #player_status');	    
			}
			if( placeGB == place)
			{
			    $('<img src="gb.png" height="20" width="30" />').appendTo('#player'+placeGB+' #player_status');
			}
			nb_joueurs = nb_joueurs +1;
			
			if(nb_joueurs >= 2)
			{
			    ajout_message_systeme("La partie va commencer");
			}
			if(place == ma_place || nb_joueurs >= nb_j_max)
			{
			    /**
			     * Dans le cas ou le joueur s'asseoit ou que quelqu'un viens de s'asseoir et que la table 
			     * viens d'atteindre son nombre de joueurs maximum, on vide les cases "Choissez votre siège".
			     */
			    for(var j=0;j<10;j++)
			    {
				if( joueurs[j][0] == "" )
				{
				    $("#player"+j).empty();
				}
			    }
			}
			i= i+6;
		    }
		    if(res[i] == "Miser")
		    {
			/**
			 * Informations que quelqu'un viens de miser, se coucher, etc...
			 * Remet à zero le timer du dernier joueur à miser
			 * @var place place du joueur ayant misé
			 * @var mise valeur de la mise ( 0 = parole, -1 = coucher, autre = valeur de la mise )
			 */
			
			clearInterval(interval);
			$("#player"+suivant+" #timer").html("");
			$("#player"+suivant+" #timer_box").css("background-color","rgb(30,45,45)");
			time = 20;

			var place = parseInt(res[i+1]);
			var mise = parseFloat(res[i+2]);							  
			suivant = parseInt(res[i+3]);						
			
			if(mise == 0)
			{
			    ajout_message_systeme(joueurs[place][0]+" a dit parole.");
			    $('#player'+place+' #mise').html('PAROLE');			      
			}
			else if(mise == -1)
			{
			    ajout_message_systeme(joueurs[place][0]+" s'est couché.");
			    $('#player'+place+' #mise').html('COUCHER');			      
			}
			else
			{
			    joueurs[place][2] = Math.round((joueurs[place][2]+mise)*100)/100;
			    joueurs[place][1] = Math.round((joueurs[place][1]-mise)*100)/100;
			    
			    if(joueurs[place][1] == 0)
			    {
				/**
				 * Si le joueur a misé tout ses jetons (tapis) on l'affiche
				 * Si le tapis du joueur est supérieure à la quantité de jetons que possède le client
				 * on fixe sa valeur pour suivre à son propre tapis ( le tapis d'un adversaire supérieure à nos jetons ).
				 */
				ajout_message_systeme(joueurs[place][0]+" est tapis.");
				$('#player'+place+' #mise').html("TAPIS");
				if(mise > mise_suivre)
				{
				    mise_suivre = joueurs[place][2];
				}
			    }
			    else
			    {
				/**
				 * Si le joueur à suivi ou relancé, on l'affiche et on met à jour la mise suivre du client
				 */
				if(joueurs[place][2] == mise_suivre)
				{							  
				    ajout_message_systeme(joueurs[place][0]+" a suivi.");
				}
				else
				{							  
				    ajout_message_systeme(joueurs[place][0]+" a relancé de "+(Math.round((joueurs[place][2]-mise_suivre)*100)/100)+".");
				}
				$('#player'+place+' #mise').html(joueurs[place][2]);
				mise_suivre = joueurs[place][2];
			    }
			    /**
			     * Met le pot à jour
			     */
			    $('#player'+place+' #jetons').html(joueurs[place][1]);
			    pot = Math.round((pot + mise)*100)/100;
			    $('.pot').html(pot);
			}   		
			/**
			 * Place le timer sur le prochain à jouer
			 */
			$("#player"+suivant+" #timer").html(time);
			interval = setInterval(function(interval){
			    $("#player"+suivant+" #timer").html(time-1);
			    time= time-1;
			    if(time == 5)
			    {
				$("#player"+suivant+" #timer_box").css("background-color","rgb(70,45,45)");
			    }
			    if(time == 4)
			    {
				$("#player"+suivant+" #timer_box").css("background-color","rgb(110,35,35)");
			    }
			    if(time == 3)
			    {
				$("#player"+suivant+" #timer_box").css("background-color","rgb(150,25,25)");
			    }
			    if(time == 2)
			    {
				$("#player"+suivant+" #timer_box").css("background-color","rgb(200,15,15)");
			    }
			    if(time == 1)
			    {
				$("#player"+suivant+" #timer_box").css("background-color","rgb(240,5,5)");
			    }
			    if(time == 0)
			    {
				if(ma_place == suivant)
				{
				    absent();							    
				}
				clearInterval(interval);
				if(suivant != -1)
				{
				    $("#player"+suivant+" #timer").html("");
				    time = 21;
				}
			    }
			},1000);
			
			i = i+4;
		    }
		    if(res[i] == "Tchat")
		    {
			/**
			 * Informe d'un nouveau message de chat puis l'incorpore dans la fenêtre de chat
			 * @var pseudo nom de celui qui à envoyé le message
			 * @var message contient le message de chat
			 */
			var pseudo = res[i+1];
			var message = res[i+2];					   
			
			$('<span class="message_chat"><strong>['+pseudo+']</strong></span><span class="message_chat"> : '+message+'<br/></span>').appendTo('#chat_box');
			var scroll = $("#chat_box").scrollTop();
			$("#chat_box").scrollTop(scroll +100);
			
			i = i+3;
		    }
		    if(res[i] == "Deale")
		    {
			/**
			 * Le serveur nous informe du début d'une manche elle va s'éffectuer en deux temps :
			 *    - Met l'interface du client à zero pour le début d'une nouvelle manche ( mise des joueurs à zero, le pot a zero, enlever les cartes du board et du client, etc ...)
			 *    - Met les informations des joueurs ( status des dealer, petite blind, place la petite blind, grosse blinde, etc ... )
			 * @var dealer place du dealer pour la nouvelle manche
			 * @var placePB place de la petite blind pour la nouvelle manche
			 * @var misePB mise de la petite blind pour le debut de la manche ( important dans le cas ou le joueur aurai moins de jetons que la mise minimum )
			 * @var placeGb place de la grosse blind pour la nouvelle manche
			 * @var miseGB mise de la grosse blind pour le début de la manche ( important dans le meme cas que la petite blind )
			 * @var suivant place du premier à jouer dans la nouvelle manche
			 */
			for(var k=0;k<10;k++)
			{
			    /**
			     * Enleve les cartes miniature du gagnant de la dernière manche
			     */
			    $('#player'+k+' #cartes_gagnant').empty();
			}
			/**
			 * Remet le timer du dernier joueur à avoir miser à zero
			 */
			clearInterval(interval);
			$("#player"+suivant+" #timer").html("");
			time = 20;
			
			$('#player'+dealer+' #player_status').html('');
			$('#player'+placeGB+' #player_status').html('');
			$('#player'+placePB+' #player_status').html('');
			dealer = res[i+1];
			placePB = res[i+2];
			misePB = parseFloat(res[i+3]);
			placeGB = res[i+4];
			miseGB = parseFloat(res[i+5]);						
			suivant = res[i+6];
			
			ajout_message_systeme("La partie débute ! Bonne chance à tous !");
			
			$('<img src="couronne.png" height="20" width="35" />').appendTo('#player'+dealer+' #player_status');
			$('<img src="pb.png" height="20" width="30" />').appendTo('#player'+placePB+' #player_status');    
			$('<img src="gb.png" height="20" width="30" />').appendTo('#player'+placeGB+' #player_status');
			
			for(var j=0;j<10;j++)
			{
			    joueurs[j][2] = 0;							      
			    $('#player'+j+' #mise').html(joueurs[j][2]);
			}
			
			joueurs[placeGB][2] = miseGB;
			joueurs[placeGB][1] = (joueurs[placeGB][1] - miseGB);
			joueurs[placePB][2] = misePB;
			joueurs[placePB][1] = (joueurs[placePB][1] - misePB);
			$('#player'+placePB+' #mise').html((Math.round(joueurs[placePB][2]*100))/100);
			$('#player'+placePB+' #jetons').html((Math.round(joueurs[placePB][1]*100))/100);
			$('#player'+placeGB+' #mise').html((Math.round(joueurs[placeGB][2]*100))/100);
			$('#player'+placeGB+' #jetons').html((Math.round(joueurs[placeGB][1]*100))/100);
			
			$('#board1').attr('src','cards/def.png');
			$('#board2').attr('src','cards/def.png');
			$('#board3').attr('src','cards/def.png');
			$('#board4').attr('src','cards/def.png');
			$('#board5').attr('src','cards/def.png');
			board = 1;
			
			$("#carte1").attr('src','cards/def.png');
			$("#carte2").attr('src','cards/def.png');
			$(".pot").html("0");
			
			pot = misePB + miseGB;
			$('.pot').html(pot);
			
			mise_suivre = joueurs[placeGB][2];
			
			$("#player"+suivant+" #timer").html(time);
			interval = setInterval(function(interval){
			    $("#player"+suivant+" #timer").html(time-1);
			    time= time-1;
			    if(time == 5)
			    {
				$("#player"+suivant+" #timer_box").css("background-color","rgb(70,45,45)");
			    }
			    if(time == 4)
			    {
				$("#player"+suivant+" #timer_box").css("background-color","rgb(110,35,35)");
			    }
			    if(time == 3)
			    {
				$("#player"+suivant+" #timer_box").css("background-color","rgb(150,25,25)");
			    }
			    if(time == 2)
			    {
				$("#player"+suivant+" #timer_box").css("background-color","rgb(200,15,15)");
			    }
			    if(time == 1)
			    {
				$("#player"+suivant+" #timer_box").css("background-color","rgb(240,5,5)");
			    }
			    if(time == 0)
			    {
				if(ma_place == suivant)
				{
				    absent();							    
				}
				clearInterval(interval);
				$("#player"+suivant+" #timer").html("");
				time = 21;
			    }
			},1000);
			
			i = i+7;							    
		    }
		    if(res[i] == "Carte")
		    {
			/**
			 * Informations relative au cartes d'un joueur :
			 *   - Si elles appartienent au client on les lui affiche et on lui affiche l'interface joueur pour miser, se coucher, etc...
			 *   - Sinon elles appartienent à un autre joueur, ce cas là se produira dans le cas d'une fin de manche on affiche alors les cartes d'un joueur encore en jeu à la fin de la manche
			 * @var place place du joueur à qui appartient les cartes
			 * @var carte_1 première carte
			 * @var carte_2 seconde carte
			 */
			var place = res[i+1];
			var carte_1 = res[i+2];
			var carte_2 = res[i+3];

			if(ma_place == place)
			{							
			    $('.select').empty();
			    $('.select').html('<table><tr><td><img onclick="javascript:miser(0)" src="button_parole.png" width="73" height="35" style="cursor:pointer;"/></td><td><img src="button_suivre.png" style="cursor:pointer;" width="73" height="35" onclick="javascript:miser(1)"/></td><td><img src="button_relancer.png" style="cursor:pointer;" width="73" height="35" onclick="javascript:miser(2)"/></td><td><img src="button_coucher.png" style="cursor:pointer;" width="73" height="35" onclick="javascript:miser(-1)"/></td></tr></table><div id="relance_div" style="display:none"><div id="slider"></div><div id="valeur_relance"></div></div>');					       
			    
			    for(var j=0;j<10;j++)
			    {
				/**
				 * On enleve au client la possibilité s'asseoir
				 */
				if($('#player'+j).html() == '<strong>Choisissez votre siège.</strong>')
				{
				    $('#player'+j).html('');
				}
			    }													
			    carte1 = carte_1;
			    $("#carte1").attr('src','cards/'+carte1+'.png');
			    carte2 = carte_2;
			    $("#carte2").attr('src','cards/'+carte2+'.png');
			}
			else
			{
			    $('#player'+place+' #cartes_gagnant').append('<img src="mini_cards/'+carte_1+'.png" width="20" height="30"/>');
			    $('#player'+place+' #cartes_gagnant').append('<img src="mini_cards/'+carte_2+'.png" width="20" height="30"/>');
			}
			i = i+4;
		    }
		    if(res[i] == "Milie")
		    {		
			/**
			 * Information indiquant qu'une nouvelle carte viens d'être tiré et placé sur le board
			 * @var res[i+1] valeur de la carte qui viens d'être tirée
			 * @var suivant place du suivant à jouer
			 */
			clearInterval(interval);
			$("#player"+suivant+" #timer").html("");
			time = 20;				   
									  
			$('#board'+board).attr('src','cards/'+res[i+1]+'.png');
			board = board+1;
			suivant = res[i+2];
			
			$("#player"+suivant+" #timer").html(time);
			interval = setInterval(function(interval){
			    $("#player"+suivant+" #timer").html(time-1);
			    time= time-1;
			    if(time == 5)
			    {
				$("#player"+suivant+" #timer_box").css("background-color","rgb(70,45,45)");
			    }
			    if(time == 4)
			    {
				$("#player"+suivant+" #timer_box").css("background-color","rgb(110,35,35)");
			    }
			    if(time == 3)
			    {
				$("#player"+suivant+" #timer_box").css("background-color","rgb(150,25,25)");
			    }
			    if(time == 2)
			    {
				$("#player"+suivant+" #timer_box").css("background-color","rgb(200,15,15)");
			    }
			    if(time == 1)
			    {
				$("#player"+suivant+" #timer_box").css("background-color","rgb(240,5,5)");
			    }
			    if(time == 0)
			    {
				if(ma_place == suivant)
				{
				    absent();							    
				}
				clearInterval(interval);
				$("#player"+suivant+" #timer").html("");
				time = 21;
			    }
			},1000);
			
			for(var j=0;j<10;j++)
			{
			    /**
			     * Milie nous indique également la fin d'un tour, on remet donc la mise des joueurs à zero
			     */
			    joueurs[j][2] = 0.0;
			    $('#player'+j+' #mise').html("0");
			}
			mise_suivre = 0.0;
			
			i = i+2;
		    }
		    if(res[i] == "Gagna")
		    {			
			/**
			 * Information concernant le ou les gagnants d'une manche, affiche les cinq cartes avec lesquelles le joueur à gagné
			 * Met les jetons du gagnant à jours et sa mise à zero
			 * @var place_gagnant place d'un des gagnant de la manche
			 * @var new_jetons la nouvelle quantité de jetons du gagnant
			 * @var carte1 permière carte de la meilleure main du gagnant
			 * @var carte2 seconde carte de la meilleure main du gagnant
			 * @var carte3 troisième carte de la meilleure main du gagnant
			 * @var carte4 quatrième carte de la meilleure main du gagnant
			 * @var carte5 cinquième carte de la meilleure main du gagnant
			 */

			clearInterval(interval);
			var place_gagnant = res[i+1];
			var new_jetons = parseFloat(res[i+2]);
			var carte1 = res[i+3];
			var carte2 = res[i+4];
			var carte3 = res[i+5];
			var carte4 = res[i+6];
			var carte5 = res[i+7];
			
			ajout_message_systeme(joueurs[place_gagnant][0]+" a récuperé "+Math.round((new_jetons - joueurs[place_gagnant][1])*100)/100+" du pot.");
			
			joueurs[place_gagnant][1] = new_jetons;
			
			$('#player'+place_gagnant+' #jetons').html(joueurs[place_gagnant][1]);
			$('#player'+place_gagnant+' #mise').html("");
			
			var j;
			if(res[i+3] != -1)
			{
			    for(j=3;j<8;j++)
			    {
				$('#player'+place_gagnant+' #cartes_gagnant').append('<img src="mini_cards/'+res[i+j]+'.png" width="20" height="30"/>');
			    }
			}
			
			i = i+8;
		    }
		    if(res[i] == "Perdu")
		    {	
			/**
			 * Information relative a un joueur qui n'a pas gagné une manche mais à récupéré des jetons du pot 
			 * Exemple : le joueur 1 est tapis, le joueur 2 n'a pas assez de jetons pour suivr le tapis du joueur 1 
			 * Il met donc tout ses jetons et se met donc tapis. Résultat le joueur 2 remporte la manche 
			 * Cependant comme il n'a pas put suivre le tapis du joueur 1, le joueur 1 récupère la différence 
			 * Entre son tapis et le tapis du joueur 2.
			 */
			clearInterval(interval);
			var place_perdant = res[i+1];
			var new_jetons = parseFloat(res[i+2]);
			
			ajout_message_systeme(joueurs[place_perdant][0]+" a récuperé "+Math.round((new_jetons - joueurs[place_perdant][1])*100)/100+" du pot.");
			
			joueurs[place_perdant][1] = new_jetons;
			$('#player'+place_perdant+' #jetons').html(joueurs[place_perdant][1]);
			
			i = i+8;						  
		    }
		    if(res[i] == "JQuit")
		    {
			/**
			 * Information qu'un joueur viens de quitter la table ou est devenu spectateur, on remet son emplacement à vide puis 
			 * dans le cas ou la table serai pleine, on donne au spectateur la possibilité de s'asseoir
			 * @var joueur_quit place du joueur qui viens de partir
			 */

			var joueur_quit = res[i+1];
			
			ajout_message_systeme(joueurs[joueur_quit][0]+" est devenu spectateur.");
			
			$('#player'+joueur_quit).empty();
			joueurs[joueur_quit][0] = "";
			joueurs[joueur_quit][1] = 0.0;
			joueurs[joueur_quit][2] = 0.0;
			joueurs[joueur_quit][3] = "";
			if(ma_place == joueur_quit)
			{
			    $("#carte1").attr('src','cards/def.png');
			    $("#carte2").attr('src','cards/def.png');
			    $('.select').empty();
			}
			if(ma_place == -1)
			{	
			    var j;
			    for(j=0;j<10;j++)
			    {
				if($("#player"+j).html() == "")
				{
				    $("#player"+j).html('<div id="select_seat" onclick="javascript:choixPlace('+j+')"><strong>Choisissez votre siège</strong></div>');
				}
			    }
			}
			else if(joueur_quit == ma_place )
			{
			    var j;
			    ma_place = -1;
			    for(j=0;j<10;j++)
			    {
				if(joueurs[j][0] == "")
				{
				    $("#player"+j).html('<div id="select_seat" onclick="javascript:choixPlace('+j+')"><strong>Choisissez votre siège</strong></div>');
				    $(".select").empty();
				}
			    }
			}						
			
			i = i+1;
		    }
		    if(res[i] == "Absen")
		    {
			/**
			 * Information relative à un joueur qui est devenu absent, si c'est le client qui est absent on lui affiche un panneau avec la possibilité de revenir sur la table
			 */
			var place = res[i+1];
			
			ajout_message_systeme(joueurs[place][0]+" est absent.");
						
			if(place == ma_place)
			{
			    $('<div id="absent"><div>Vous n\'avez pas joué dans le temps qui vous était imparti</div><input type="button" value="Revenir sur la table" onclick="javascript:retour_en_jeu();"/></div><div id="absent_background"></div>').appendTo("html");
			}
			
			i = i+2;
			
		    }
		    else
		    {
			i = i+1;
		    }
		}
	    }	
	}
    });
}