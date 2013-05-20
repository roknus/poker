<?php
session_start(); 
require_once('../../model/connection_db.php');
function get_money(){
  $db = connect_db();
  $request = $db->prepare('SELECT * FROM infoclients WHERE username = :login;');
  $request->execute(array(
			  "login"=>$_SESSION["username"]
			  ));
  $data = $request->fetch();
  $money = $data["money"];
  $money /= 100;
  $_SESSION["money"] = $money;
  return $money;
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
		<script src="/~flucia/jquery-ui-1.10.2.custom/development-bundle/ui/jquery.ui.core.js"></script>
		<script src="/~flucia/jquery-ui-1.10.2.custom/development-bundle/ui/jquery.ui.widget.js"></script>
		<script src="/~flucia/jquery-ui-1.10.2.custom/development-bundle/ui/jquery.ui.mouse.js"></script>
		<script src="/~flucia/jquery-ui-1.10.2.custom/development-bundle/ui/jquery.ui.slider.js"></script>

		<script type="text/javascript">
		<!--
                        function rejoindreTable(num_port,nb_j_max,nom_table,style_jeu){
                             $(location).attr('href',"/~flucia/client/table/index.php?num_port="+num_port+"&nb_j_max="+nb_j_max+"&nom_table="+nom_table+"&style="+style_jeu);	    
			}

			function rafraichir(){
  				 $.ajax({
					 url : "/~flucia/client/menu_principal/refresh_table.php",
					 complete : function(xhr,result){
					 	  if(result != "success") return;
						  var response = xhr.responseText;
						  $("#tables_list").empty();
						  $(response).appendTo("#tables_list");
						  
						  var tr = $("#tables_list").find("tr");
						  $(tr).each(function(index){
							if(index % 2 == 0){
		       				  	   $(tr).eq(index).css("background-color","rgb(30,35,35)");
		     				  	}
		   				  });
      					}
   				});
			}
		//-->
		</script>
	</head>
    	<body>
		<script type="text/javascript">
		<!--
		$(document).ready(function(){
					setInterval(function(){
						rafraichir();						       
					},
					700);
			});
		//-->
		</script>

        	<div class="page">
			<div class="wrapper">
				<div id="menu_principal">                                        
					<ul id="menu_bar">
						<li class="menu_pseudo"><?php echo $_SESSION["username"] ?></li>
		                                <li><?php echo get_money(); ?><Li>
					</ul>
					<div id="tables_list"></div>
				</div>
			</div>
        	</div>
    	</body>
</html>
