<?php
$images_path = 'images';


//Delete old files
$images = scandir($images_path, SCANDIR_SORT_DESCENDING);
$now = time();
$i = 0;
foreach($images as $image) {
	$file = $images_path.'/'.$image;
	if(is_file($file) && $file != '.htaccess') {
		//Keep the first one
		if($i) {
			if($now - filemtime($file) >= 60 * 60 * 24 * 8) //8 days
				unlink($file);
		}
		$i++;
	}
}

if(count($images))
	echo '<img src="'.$images_path.'/'.$images[0].'" alt="Webcam" /><span class="timestamp">'.str_replace('.jpg', '', $images[0]).'</span>';
?>