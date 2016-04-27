<?php

require_once('config.inc.php');
$target_path = 'images/'.date('Y-m-d H:i:s').'.jpg';

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
