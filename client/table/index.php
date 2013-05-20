<?php
    session_start(); 
        
    if(isset($_GET["num_port"]) AND isset($_GET["nb_j_max"]) AND isset($_GET["nom_table"]) AND isset($_GET["style"])){
      $nb_j_max = $_GET["nb_j_max"];
      $num_port = $_GET["num_port"];
      $nom_table = $_GET["nom_table"];
      $style_jeu = $_GET["style"];
    }

    function player_block($place,$nb_j_max){
      echo '<td><div id="player'.$place.'" style="vertical-align:center;">
      <div id="select_seat" onclick="javascript:choixPlace('.$place.')"><strong>Choisissez votre siège</strong></div>
      </div></td>';   
    }
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
        	<title>Poker en ligne</title>
        	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="/~flucia/style.css" />
		<link rel="stylesheet" href="/~flucia/jquery-ui-1.10.2.custom/development-bundle/themes/ui-darkness/jquery.ui.all.css" />
	
		<script type="text/javascript" src="/~flucia/jquery-1.9.1.min.js"></script>
		<script type="text/javascript" src="/~flucia/jquery-ui-1.10.2.custom/development-bundle/ui/jquery.ui.core.js"></script>
		<script type="text/javascript" src="/~flucia/jquery-ui-1.10.2.custom/development-bundle/ui/jquery.ui.widget.js"></script>
		<script type="text/javascript" src="/~flucia/jquery-ui-1.10.2.custom/development-bundle/ui/jquery.ui.mouse.js"></script>
        	<script type="text/javascript" src="/~flucia/jquery-ui-1.10.2.custom/development-bundle/ui/jquery.ui.slider.js"></script>

		<script type="text/javascript">
		<!--
			// Info relative au joueur
			var carte1 = "def.png";
			var carte2 = "def.png";
			var ma_place = -1;
			var mon_pseudo = "<?php echo $_SESSION["username"]; ?>";
			var mon_argent = <?php echo $_SESSION["money"]; ?>;

			// Info relative à la table
			var pot = 0.0;
			var dealer = -1;
			var placePB = -1;
			var placeGB = -1;
			var mise_min = 0.0;
			var mise_suivre = 0.0;
			var suivant = -1;
			var board = 1;
			var time = 20;
			var interval;
			var nb_joueurs = 0;
			var nb_j_max = <?php echo $nb_j_max; ?>;
			var style_jeu = <?php echo $style_jeu; ?>;
			var num_port = <?php echo $num_port; ?>;
			var nom_table = "<?php echo $nom_table; ?>";

			var joueurs = new Array();

			var i;
			for(i=0;i<10;i++)
			{
				joueurs[i] = new Array("",0.0,0.0,"");
			}
		//-->
		</script>
		
		// Script qui contient l'ensemble des fonction relative à la table
        	<script type="text/javascript" src="/~flucia/client/table/script.js"></script>

		// Script qui contient la fonction de rafraichissement de la table
		<script type="text/javascript" src="/~flucia/client/table/refresh.js"></script>

	</head>
	<body onload="connexionClient()" onunload="fermetureClient();">

		<script type="text/javascript">
		<!--
			$(document).ready(function(){					   
				setInterval(function(){
					refresh();
				},300);
				$("#chat_input").bind('keydown',function(event){
					if(event.keyCode == 13){
						envoyer_message($('#chat_input').val());
					}
				});
			});
		//-->
		</script>

		<div id="top_menu_table">
		     <table>
			<tr>
				<td id="table_name"><?php echo $nom_table; ?></td>
				<td id="stats"><strong><?php echo $_SESSION["username"]; ?></strong>
				</td>
				<td id="quit_table_next_round"><button onclick="javascript:quitterTableNext()">Spectateur à la prochaine manche</button></td>
				<td id="quit_table"><a href="../menu_principal"><img src="close_icone.png" width="40" height="23"/></a></td>
			</tr>
		     </table>
                </div>
		<div id="table_page">
			<div>
				<table>
					<tr>
						<td colspan="3">
							<table class="table">
								<tr>
									<td></td>
									<?php
										for($i=0;$i<4;$i++){
										  player_block($i,$nb_j_max);
										}
									?>
									<td></td>
								</tr>
								<tr>   
									<?php player_block(9,$nb_j_max);?>	
									<td><div class="pot_div"><span>Pot</span><br/><span class="pot"></span></div></td>
									<td colspan="3" class="board">
										<table id="board">
											<tr>						   
												<td>
													<img src="cards/carte.png" height="90" width="60"/>
												</td>
												<td>
													<img id="board1" src="cards/def.png" height="90" width="60"/>
												</td>
												<td>
													<img id="board2" src="cards/def.png" height="90" width="60"/>
												</td>
												<td>
													<img id="board3" src="cards/def.png" height="90" width="60"/>
												</td>
												<td>
													<img id="board4" src="cards/def.png" height="90" width="60"/>
												</td>
												<td>
													<img id="board5" src="cards/def.png" height="90" width="60"/>
												</td>
											</tr>
										</table>
									</td>
									<?php player_block(4,$nb_j_max); ?>
								</tr>	
								<tr>	
									<td></td>
									<?php for($i=8;$i>4;$i--){ player_block($i,$nb_j_max); } ?>	
									<td></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<div class="chat">
                                                            <div id="chat_box"></div>							    
							    <textarea id="chat_input"></textarea>          
							</div>
						</td>
						<td>
							<div class="cards">
								<div id="cards_box">
									<img id="carte1" src="cards/def.png" height="130" width="90"/>
								</div>
								<div style="display:inline-block">
									<img id="carte2" src="cards/def.png" height="130" width="90"/>
								</div>
							</div>
						</td>
						<td>
							<div class="select"></div>				
						</td>			
					</tr>						
				</table>		
			</div>		
		</div>
	</body>
</html>
