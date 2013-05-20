<?php 
session_start();

/*=======================================================================
 Nom : index.php                Auteur : Lucia Florent
 Date creation : 21/02/2013     Derniere mise a jour : 21/02/2013
 Projet : Poker en ligne L3 S6
 ------------------------------------------------------------------------
 Specification:
 Ce fichier contient ...
 =======================================================================*/ 

$LOCAL_MACHINE = $_SESSION["server_hostname"];
$LOCAL_MACHINE_PORT = 12349;

$socket_public = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
if(socket_connect($socket_public,$LOCAL_MACHINE,$LOCAL_MACHINE_PORT) == false){
  break;
 }

socket_write($socket_public,$_SESSION["username"]."&rafraichir_menu_principal&");
$res = socket_read($socket_public,2047);

$mess = strtok($res,'&');
$tables = '<table><tr>
                     <th id="nom_table_col">Nom Table</th>
                     <th id="nb_j_col">Nombre de joueurs</th>					
           	     <th id="mise_min_col">Mise minimum / Cave S&G</th>
                     <th id="boutton_col"></th>
                   </tr>';
/*while($mess != ""){  
  $tables .= '<tr><td>';
  $nom_table = strtok('&');
  $tables .= $nom_table.'</td>';
  $num_port = strtok('&');
  $tables .= '<td style="text-align:center;">'.strtok('&').' / ';
  $nb_j_max = strtok('&');
  $tables .= $nb_j_max.'</td><td style="text-align:center;">'.number_format(strtok('&'),2,'.',' ').'</td>';  
  $style_jeu = strtok('&');
  $tables .= '<td><button class="button_connect" onclick="javascript:rejoindreTable('.$num_port.','.$nb_j_max.',\''.$nom_table.'\','.$style_jeu.')">Rejoindre</button></td></tr>';
  $mess = strtok('&');
 }
*/
while($mess != "")
{
  $nom_table = strtok('&');
  $num_port = strtok('&');
  $nombre_joueurs = strtok('&');
  $nombre_joueurs_max = strtok('&');
  $mise_min = number_format(strtok('&'),2,'.',' ');
  $style_jeu = strtok('&');
  if($style_jeu == 1)
  {
	$nom_table .= ' (Sit and Go)';
  }
  
  $tables .= '<tr><td>'.$nom_table.'</td>
		<td style="text-align:center;">'.$nombre_joueurs.' / '.$nombre_joueurs_max.'</td>
		<td style="text-align:center;">'.$mise_min.'</td>
		<td><button class="button_connect" onclick="javascript:rejoindreTable('.$num_port.','.$nombre_joueurs_max.',\''.$nom_table.'\','.$style_jeu.')">Rejoindre</button></td></tr>';
  $mess = strtok('&');
}

$tables .= '</table>';
echo $tables;

socket_close($socket_public);