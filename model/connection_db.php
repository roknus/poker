<?php
	function connect_db(){
	
		try
		{
<<<<<<< HEAD
			$db = new PDO('mysql:host=venus;dbname=flucia','flucia', 'flucia');
=======
			$db = new PDO('mysql:host=localhost;dbname=pokerl3','root', 'azerty');
>>>>>>> 8fce1a301af4a3a49741dadcaa8313c7e30a1170
		}
		catch (Exception $e)
		{
				die('Erreur : ' . $e->getMessage());
		}
		
		return $db;
	}