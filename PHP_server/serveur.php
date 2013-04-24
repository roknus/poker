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

$C_SERVER_IP = 'localhost';
$C_SERVER_PORT = '21345';
$LOCAL_MACHINE_IP = 'localhost';
$LOCAL_MACHINE_PORT = '12349';

//Creer un socket public pour toutes requetes en provenance d'un client
if(false === $socket_public = socket_create(AF_INET,SOCK_STREAM,SOL_TCP)){
  echo "Erreur creation socket";
  exit(1);
 }

//Bind le socket avec un n° de port
if(false === socket_bind($socket_public,$LOCAL_MACHINE_IP,$LOCAL_MACHINE_PORT)){
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
  
	$read = $sockets;  
	$write = NULL;
	$except = NULL;

	//Si il n'y a aucun changement sur les sockets on passe a l'iteration suivante (sinon on met a jour le tableau des sockets)
	if(socket_select($read,$write,$except,0) < 1){
		continue;
	}

	//Si il y a un changement sur le socket public ( coté client ) on l'accepte et on recupere le nom d'utilisateur du client
	if(in_array($socket_public,$read)){
  
		$new_client_socket = socket_accept($socket_public);
		$mess = socket_read($new_client_socket,255);
		
		if(!strstr($mess,'refresh')){ // A enlever !
		  echo "\nMess = ".$mess."\n";
		}

		$username = strtok($mess,'&');
       
		//Si on ne possede pas de socket pour ce client on lui en creer un socket que l'on connecte au serveur C++
		if(!(isset($clients[$username]))){
		        $new_socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);	      
			socket_connect($new_socket,$C_SERVER_IP,$C_SERVER_PORT);
			socket_write($new_socket,$mess);
			$infoclient = socket_read($new_socket,2047);

			//Si la connection du client est accepté par le serveur C++ alors on lui créer une instance de classe client
			if($infoclient != false){
				$sockets[$username] = $new_socket;
				$password = strtok('&');
				$new_client = new Client(strtok($infoclient,'&'),$password,$new_socket,strtok('&'),strtok('&'),strtok('&'),strtok('&'),strtok('&'),strtok('&'),strtok('&'),strtok('&'),strtok('&'),strtok('&'));

				$clients[$username] = $new_client;
				echo "Client crée\n";
				$table_info = "";
				while(strtok('&') == "TInfo"){
				  $nom_table = strtok('&');
				  $num_port = strtok('&');
				  $nbJ = strtok('&');
				  $nbJMax = strtok('&');
				  $miseMin = strtok('&');
				  $miseMin /= 100;
				  $table_info .= "TInfo&".$nom_table.'&'.$num_port.'&'.$nbJ.'&'.$nbJMax.'&'.$miseMin.'&';
				}
				$clients[$username]->settable_info($table_info);
				socket_write($new_client_socket,$table_info);
				socket_close($new_client_socket);
			}	
			//Sinon on ferme la connection avec le serveur C++ et on renvoie une erreur de connection au client
			else{
				socket_close($new_socket);
				socket_write($new_client_socket,"connection_denied");
				} 
		}
		else{
			$requete = strtok('&');
			while($requete != ""){

				if($requete == $clients[$username]->getpassword()){

				  socket_close($sockets[$username]);
				  $sockets[$username] = $new_socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
				  $clients[$username]->setsocket($new_socket);
				  socket_connect($new_socket,$C_SERVER_IP,$C_SERVER_PORT);
				  socket_write($new_socket,$username.'&'.$clients[$username]->getpassword().'&');

				  socket_write($new_client_socket,$clients[$username]->gettable_info());

				  echo 'Connexion de '.$username.'... Client déja connecté... Reconnexion au menu principal';
				}
				else{
					switch($requete){
						case "connect_table":
						{
						        $num_port = strtok('&');
							socket_close($sockets[$username]);
							echo 'Deconnexion de '.$username.' au serveur principal ... Connexion à la table '.$num_port." ...\n";
							$sockets[$username] = $new_socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
							$clients[$username]->setsocket($new_socket);
							socket_connect($new_socket,$C_SERVER_IP,$num_port);
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
							$infoT = $clients[$username]->getinfo_table();
							$infoJ = $clients[$username]->getinfo_joueurs();
							$infoD = $clients[$username]->getdealer();
							$infoC = $clients[$username]->getcartes();
							$infoM = $clients[$username]->getmise();
							$infoB = $clients[$username]->getboard();
							$infoG = $clients[$username]->getgagnant();
							$infoP = $clients[$username]->getperdant();
							$infoQ = $clients[$username]->getjoueurQuit();
							$infoT .= $infoJ .= $infoM .= $infoD .= $infoC .= $infoB .= $infoG .= $infoP .= $infoQ;
							if($infoT !== ""){
								echo $infoT."\n";
								socket_write($new_client_socket,$infoT);
								$clients[$username]->setinfo_joueurs("");
								$clients[$username]->setinfo_table("");	
								$clients[$username]->setdealer("");
								$clients[$username]->setcartes("");
								$clients[$username]->setmise("");
								$clients[$username]->setboard("");
								$clients[$username]->setgagnant("");
								$clients[$username]->setperdant("");
								$clients[$username]->setjoueurQuit("");
							}
							else{					  
								socket_write($new_client_socket,"RAS");
							}
							break;
						}
				             	case "miser":
						{
						        $mise = strtok('&');	
							$mise *= 100;						     
							socket_write($clients[$username]->getsocket(),'Miser&'.$mise.'&');
							break;
						}
					        case "quit_table":
					        {
						        socket_close($sockets[$username]);
							$sockets[$username] = $new_socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
							$clients[$username]->setsocket($new_socket);
							socket_connect($new_socket,$C_SERVER_IP,$C_SERVER_PORT);
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
				$mess = socket_read($socket,255);
		
				//Si le descripteur retourne une erreur on cherche sa position dans le tableau de sockets puis on l'enleve
				if($mess == false){
					echo "Fermeture de circuit virtuel ".socket_last_error($socket);
					if($username = array_search($socket,$sockets) !== false){
						unset($sockets[$username]);
					}
					socket_close($socket);
				}
				else{				  
					echo "Nouveau message recu du serveur c++ sur le socket ".$username." : \"".$mess."\"\n";
					$requete = strtok($mess,'&');
					while(trim($requete) != ""){
					  switch(trim($requete)){
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
								$placeGB = strtok('&');
								$miseGB = strtok('&');
								$next = strtok('&');						      
								$clients[$username]->setdealer('Deale&'.$placeD.'&'.$placePB.'&'.$placeGB.'&'.$next.'&');
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
					                case "Milie":
							{
							        $valeur = strtok('&');
								$suivant = strtok('&');
								$clients[$username]->add_carte_board('Milie&'.$valeur.'&'.$suivant.'&');
								break;
							}
				                        case "Gagna":
					                {
							        $place = strtok('&');
							        $jetons = strtok('&');
							        $jetons /= 100;
						         	$clients[$username]->add_gagnant('Gagna&'.$place.'&'.$jetons.'&');
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
 
