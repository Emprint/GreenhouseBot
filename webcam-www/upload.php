<?php

require_once('config.inc.php');
$target_path = 'images/'.date('Y-m-d H:i:s').'.jpg';

//mail('julien@emprint.fr', 'Debug', 'POST: '.print_r($_POST, true)."\n\nGET: ".print_r($_GET, true)."\n\nFILES: ".print_r($_FILES, true));

if($_FILES['raspifile'] && $_POST['token'] == $token) {
	if(copy($_FILES['raspifile']['tmp_name'], $target_path)) {
		chmod($target_path, 0664);
	    echo "File uploaded";
	}
	else
	{ 
		echo "Error";
		print_r($_FILES);
	}
} else {
	echo "No file to upload";
}

?>