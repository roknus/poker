<?php	
	session_start();
	
	$syntaxeEmail='#^(([a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+\.?)*[a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+)@(([a-z0-9-_]+\.?)*[a-z0-9-_]+)\.[a-z]{2,}$#i';
	if(isset($_POST["username"]) AND 
		isset($_POST["password"]) AND 
		isset($_POST["email"]) AND 
		isset($_POST["day"]) AND 
		isset($_POST["month"]) AND 
		isset($_POST["year"]) AND 
		isset($_POST["gender"]))
	{
		if(strlen($_POST["username"]) > 0 AND strlen($_POST["username"]) <= 32 AND
			strlen($_POST["password"]) > 0 AND strlen($_POST["password"]) <= 32 AND
			strlen($_POST["email"]) > 0 AND strlen($_POST["email"]) <= 50 AND preg_match($syntaxeEmail,$_POST["email"]) AND
			$_POST["day"] > 0 AND $_POST["day"] <= 31 AND
			$_POST["month"] > 0 AND $_POST["month"] <= 12 AND
			$_POST["year"] > 1899 AND $_POST["year"] <= date("Y") AND
			($_POST["gender"] == "Homme" OR $_POST["gender"] == "Femme"))
		{
			
			date_default_timezone_set('Europe/Berlin');
			include_once("../model/connection_db.php");
			include_once("../model/sign_up_model.php");
			require_once("../model/connection_db.php");
			
			$username = htmlspecialchars($_POST["username"]);
			$password = htmlspecialchars($_POST["password"]);
			$email = htmlspecialchars($_POST["email"]);
			$day = htmlspecialchars($_POST["day"]);
			$month = htmlspecialchars($_POST["month"]);
			$year = htmlspecialchars($_POST["year"]);
			$gender = htmlspecialchars($_POST["gender"]);
			
			if(isset($_POST["lastname"]) AND $_POST["lastname"] != ""){$lastname = htmlspecialchars($_POST["lastname"]);}else{$lastname = 'Inconnu';}
			if(isset($_POST["firstname"]) AND $_POST["firstname"] != ""){$firstname = htmlspecialchars($_POST["firstname"]);}else{$firstname = 'Inconnu';}
			if(isset($_POST["address"]) AND $_POST["address"] != ""){$address = htmlspecialchars($_POST["address"]);}else{$address = 'Inconnu';}
			if(isset($_POST["address2"]) AND $_POST["address2"] != ""){$address2 = htmlspecialchars($_POST["address2"]);}else{$address2 = '';}
			if(isset($_POST["city"]) AND $_POST["city"] != ""){$city = htmlspecialchars($_POST["city"]);}else{$city = 'Inconnu';}
			if(isset($_POST["zipcode"]) AND $_POST["zipcode"] != ""){$zipcode = htmlspecialchars($_POST["zipcode"]);}else{$zipcode = 'Inconnu';}
			if(isset($_POST["country"]) AND $_POST["country"] != ""){$country = htmlspecialchars($_POST["country"]);}else{$country = 'Inconnu';}
			
			$password = sha1($password);
			
			$signup = insert_new_client($username,$password,10000,$email,$year,$month,$day,$gender,$firstname,$lastname,$address,$address2,$city,$country,$zipcode);
			
			if($signup){	
				header('Location:/~flucia/');
				echo '<script>alert("Vous �tes inscrit !");</script>';
			}
			else{
				require_once("../view/sign_up_view.php");
				echo '<script>alert("Une erreur s\'est produite.\nVeuillez verifier les champs de saisie");</script>';
			}
		}
		else{
			require_once("../view/sign_up_view.php");
			echo '<script>alert("Une erreur s\'est produite.\nVeuillez verifier les champs de saisie");</script>';
		}
	}
	else{
		require_once("../view/sign_up_view.php");
	}
	
