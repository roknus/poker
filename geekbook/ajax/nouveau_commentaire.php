<?php
	require_once('../model/connection_db.php');
	require_once('../model/add_comment.php');
	date_default_timezone_set('Europe/Berlin');
	
	$comment = $_GET["comment"];
        $id_post = $_GET["id_post"];
        addComment($id_post,$comment);
        echo'<td><img src="default.jpg" height="30" width="30" /></td>
	        <td>'.$comment.'</td>';