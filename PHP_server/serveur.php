<?php
/*=======================================================================
 Nom : serveur.php              Auteur : Lucia Florent
 Date creation : 21/02/2013     Derniere mise a jour : 15/04/2013
 Projet : Poker en ligne L3 S6
 ------------------------------------------------------------------------
 Specification:
 Ce fichier contient ...
 ======================================================================*/ 

require_once("class_client.php");

$C_SERVER_HOSTNAME = '192.168.0.12';
$C_SERVER_PORT = '21345';
$LOCAL_MACHINE_HOSTNAME = 'roknus';
$LOCAL_MACHINE_PORT = '12349';

//Creer un socket public pour toutes requetes en provenance d'un client
if(false === $socket_public = socket_create(AF_INET,SOCK_STREAM,SOL_TCP)){
  echo "Erreur creation socket";
  exit(1);
 }

//Bind le socket avec un n° de port

if(false === socket_bind($socket_public,$LOCAL_MACHINE_HOSTNAME,$LOCAL_MACHINE_PORT)){
  echo "Erreur bind du socket\n";
  exit(1);
 }

//Creation de la liste d'attente
if(false === socket_listen($socket_public,50)){
  echo "Erreur creation file d'attente\n";
  exit(1);
 }

$sockets = array($socket_public);//Tableau contenant le socket public et les sockets de chaque client connecte au serveur C++
$clients = array();//Tableau des instances de clients
while(true){

        $read = $sockets;//Descripteur en lecture du select
	$write = NULL;//Descripteur en ecriture du select
	$except = NULL;//Descripeur des exception du select

	//Si il n'y a aucun changement sur les sockets on passe a l'iteration suivante (sinon on met a jour le tableau des sockets)
	if(socket_select($read,$write,$except,0) < 1){
		continue;
	}

	//Si il y a un changement sur le socket public ( coté client ) on l'accepte et on recupere le nom d'utilisateur du client
	if(in_array($socket_public,$read)){
  
		$new_client_socket = socket_accept($socket_public);
		$mess = socket_read($new_client_socket,255);
		
		if(!strstr($mess,'refresh') AND !strstr($mess,'rafraichir_menu_principal')){ // A enlever !
		  echo "\nMess = ".$mess."\n";
		}

		$username = strtok($mess,'&');
       
		//Si on ne possede pas de socket pour ce client on lui en creer un socket que l'on connecte au serveur C++
		if(!(isset($clients[$username]))){
		        $new_socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
			if(false === socket_connect($new_socket,$C_SERVER_HOSTNAME,$C_SERVER_PORT)){
			  echo "Impossible de joindre le serveur principal\n";
			  socket_write($new_client_socket,"Disconnected");
			  socket_close($new_client_socket);
			  continue;
			}			
			socket_write($new_socket,$mess);
			$infoclient = socket_read($new_socket,2047);

			//Si la connection du client est accepté par le serveur C++ alors on lui créer une instance de classe client
			if($infoclient != false){
				$sockets[$username] = $new_socket;
				$password = strtok('&');
				$new_client = new Client(strtok($infoclient,'&'),$password,$new_socket,strtok('&'),strtok('&'),strtok('&'),strtok('&'),strtok('&'),strtok('&'),strtok('&'),strtok('&'),strtok('&'),strtok('&'));
				$clients[$username] = $new_client;
				echo "Connexion de ".$username." établie avec le serveur principal\n";
				socket_write($new_client_socket,"connection_accepted");
				socket_write($sockets[$username],"RafrT");
				socket_close($new_client_socket);
			}	
			//Sinon si le serveur C++ ferme le circuit virtuel on renvoie une erreur de connection au client
			else{
				echo "Connexion de ".$username." refusé avec le serveur principal\n";			  
				socket_close($new_socket);
				socket_write($new_client_socket,"connection_denied");
				socket_close($new_client_socket);
				} 
		}
		//Dans le cas ou l'on possede deja une instance de classe pour ce client
		else{
			$requete = strtok('&');
			while($requete != ""){
		    
			        if($requete == $clients[$username]->getpassword()){//Si c'est le mot de passe du client on le reconnecte au menu principal ( par exemple si il ferme la fenetre de jeu via la croix et qu'il reouvre une session de jeu, on ne peut pas recuperer le signal de fermeture )

				  socket_close($sockets[$username]);
				  $sockets[$username] = $new_socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
				  $clients[$username]->setsocket($new_socket);
				  socket_connect($new_socket,$C_SERVER_HOSTNAME,$C_SERVER_PORT);
				  socket_write($new_socket,$username.'&'.$clients[$username]->getpassword().'&');

				  socket_write($new_client_socket,"connection_accepted");

				  echo 'Connexion de '.$username.'... Client déja connecté... Reconnexion au serveur principal';
				}
				else{//Sinon on ecupere la requete du client et on l'applique
					switch($requete){
					        case "rafraichir_menu_principal" :
						{	
						        socket_write($new_client_socket,$clients[$username]->gettable_info());
							$clients[$username]->settable_info("");
						        socket_write($sockets[$username],"RafrT");							
							break;
						}
						case "connect_table":
						{
						        $num_port = strtok('&');
							socket_close($sockets[$username]);
							echo 'Deconnexion de '.$username.' au serveur principal ... Connexion à la table '.$num_port." ...\n";
							$sockets[$username] = $new_socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
							$clients[$username]->setsocket($new_socket);
							socket_connect($new_socket,$C_SERVER_HOSTNAME,$num_port);
							socket_write($new_socket,$username);
							echo "Connexion à la table !\n";
							break;
						}
						case "jouer":
						{
							$place = strtok('&');
							$jeton = strtok('&');
							$jeton *= 100;							
							socket_write($clients[$username]->getsocket(),"Jouer&".$place."&".$jeton."&");
							echo "Choix du siege !\n";
							break;
						}
						case "refresh":
						{
						        socket_write($new_client_socket,$clients[$username]->refresh());
							break;
						}
				             	case "miser":
						{
						        $mise = strtok('&');	
							$mise *= 100;						     
							socket_write($clients[$username]->getsocket(),'Miser&'.$mise.'&');
							break;
						}
					        case "Tchat":
						{						   		      
							$message = $username.'&'.strtok('&');
							str_replace("{esp}","&",$message);
							echo "Tchat&".$message."\n";							;
							socket_write($clients[$username]->getsocket(),"Tchat&".$message.'&');
							break;
						}
					        case "Absen":
						{
						        socket_write($sockets[$username],"Absen&");
							break;
						}
					        case "Retou":
						{
						        socket_write($sockets[$username],"Retou&");
							break;
						}
					        case "QuitT":
						{
						        socket_write($sockets[$username],"QuitT&");
							break;
						}
					        case "quit_table":
					        {
						        socket_close($sockets[$username]);
							$sockets[$username] = $new_socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
							$clients[$username]->setsocket($new_socket);
							socket_connect($new_socket,$C_SERVER_HOSTNAME,$C_SERVER_PORT);
							socket_write($new_socket,$username.'&'.$clients[$username]->getpassword().'&');
							break;
						}
					}
				}
				$requete = strtok('&');
			}
		}
	}	
	else{
		foreach($sockets as $username=>$socket){
			if(in_array($socket,$read)){
				$mess = socket_read($socket,2047);
		
				//Si le descripteur retourne une erreur on cherche sa position dans le tableau de sockets puis on l'enleve
				if($mess == false){
					echo "Fermeture de circuit virtuel ".socket_last_error($socket);
					if($username = array_search($socket,$sockets) !== false){
						unset($sockets[$username]);
					}
					socket_close($socket);
				}
				else{	
					$requete = strtok($mess,'&');	
					if($requete != "TInfoa"){ // A enlever
					  echo "Nouveau message recu du serveur c++ sur le socket ".$username." : \"".$mess."\"\n";
					}
					while(trim($requete) != ""){
					  switch(trim($requete)){
					                case "TInfo":
					                {
							        $nom_table = strtok('&');
								$num_port = strtok('&');
								$nbJ = strtok('&');
								$nbJMax = strtok('&');
								$miseMin = strtok('&');
								$miseMin /= 100;
								$style_jeu = strtok('&');
								$clients[$username]->add_table_info("TInfo&".$nom_table.'&'.$num_port.'&'.$nbJ.'&'.$nbJMax.'&'.$miseMin.'&'.$style_jeu.'&');
								break;
					                }
					                case "InfoT":
							{
								$pot = strtok('&');
								$pot /= 100;
								$dealer = strtok('&');
								$placePB = strtok('&');
								$placeGB = strtok('&');
								$miseMin = strtok('&');	
								$miseMin /= 100;
								$next = strtok('&');
								$clients[$username]->setinfo_table('InfoT&'.$pot.'&'.$dealer.'&'.$placePB.'&'.$placeGB.'&'.$miseMin.'&'.$next.'&');		
								echo "add info table\n";
								break;
							}
							case "NewJo":
							{
								$place = strtok('&');
								$pseudo = strtok('&');
								$jetons = strtok('&');
								$jetons /= 100;
								$mise = strtok('&');
								$mise /= 100;
								$etat = strtok('&');
								$clients[$username]->add_info_joueurs('NewJo&'.$place.'&'.$pseudo.'&'.$jetons.'&'.$mise.'&'.$etat.'&');
								echo "add info joueurs\n";
								break;
							}
						        case "Deale":
							{
							        $placeD = strtok('&');
								$placePB = strtok('&');
								$misePB = strtok('&');
								$misePB /= 100;
								$placeGB = strtok('&');
								$miseGB = strtok('&');
								$miseGB /= 100;
								$next = strtok('&');						      
								$clients[$username]->setdealer('Deale&'.$placeD.'&'.$placePB.'&'.$misePB.'&'.$placeGB.'&'.$miseGB.'&'.$next.'&');
								echo "add dealer\n";
								break;
							}
							case "Carte":
							{
						         	$place = strtok('&');
								$valeur1 = strtok('&');			      
								$valeur2 = strtok('&');
								$clients[$username]->setcartes('Carte&'.$place.'&'.$valeur1.'&'.$valeur2.'&');
								echo "add cartes\n";
								break;
							}
					                case "Miser":
					                {
					                        $place = strtok('&');
								$mise = strtok('&');
								$mise /= 100;
								$suivant = strtok('&');
								$clients[$username]->add_mise('Miser&'.$place.'&'.$mise.'&'.$suivant.'&');
								break;
							}
					                case "Tchat":
							{
								$pseudo = strtok('&');
								$message = strtok('&');
								$clients[$username]->add_message_chat('Tchat&'.$pseudo.'&'.$message.'&');
								break;
							}
					                case "Milie":
							{
							        $valeur = strtok('&');
								$suivant = strtok('&');
								$clients[$username]->add_carte_board('Milie&'.$valeur.'&'.$suivant.'&');
								break;
							}
					                case "Absen":
							{
							       $place = strtok('&');
							       $clients[$username]->add_absent('Absen&'.$place.'&');
							       break;
							}
					                case "Retou":
							{
							       $place = strtok('&');
							       break;
							}
				                        case "Gagna":
					                {
							        $place = strtok('&');
							        $jetons = strtok('&');
							        $jetons /= 100;
								$c1 = strtok('&');
								$c2 = strtok('&');
								$c3 = strtok('&');
								$c4 = strtok('&');
								$c5 = strtok('&'); 
						         	$clients[$username]->add_gagnant('Gagna&'.$place.'&'.$jetons.'&'.$c1.'&'.$c2.'&'.$c3.'&'.$c4.'&'.$c5.'&');
							        break;
							}
					                case "Perdu":
							{
							        $place = strtok('&');
								$jetons = strtok('&');
								$jetons /= 100;
						         	$clients[$username]->add_perdant('Perdu&'.$place.'&'.$jetons.'&');
								break;
							}
					                case "JQuit" :
					                {
					                        $place = strtok('&');
						         	$clients[$username]->add_joueurQuit('JQuit&'.$place.'&');
								break;
							}
					                default:
							{
								$requete = "";
								break;
							}
						}
						$requete = strtok('&');						
					}
				}
			}
		}
	}
}
foreach($sockets as $socket_destroy){
  socket_close($socket_destroy);
}
 
