<?php
require_once('config.inc.php');

/*
CREATE TABLE IF NOT EXISTS `rasp-temp` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `date_add` datetime NOT NULL,
  `data1` decimal(6,3) NULL,
  `data2` decimal(6,3) NULL,
  `data3` decimal(6,3) NULL,
  `data4` decimal(6,3) NULL,
  `data5` decimal(6,3) NULL,
  `data6` decimal(6,3) NULL,
  `data7` decimal(6,3) NULL,
  `data8` decimal(6,3) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
*/

if($_POST['token'] == $token && $_POST['data']) {
	$date = date('Y-m-d H:i:s');
	$temps = [];
	if(is_array($_POST['data']))
		$temps = $_POST['data'];
	else
		$temps[] =  $_POST['data'];
		
	$cols = [];
	for($i = 1; $i <= count($temps); $i++)
		$cols[] = '`data'.$i.'`';
		
		
	$query = 'INSERT INTO `'._DB_TABLE_.'` (`date_add`, '.implode(', ', $cols).') VALUES("'.$date.'", '.implode(', ', $temps).')';
	//echo $query.' ';
	$mysql = new MySQL();
	if($mysql->Execute($query)) {
	    echo "Success";
	}
	else
	{ 
		echo "Error ".$mysql->getMsgError()." ";
		print_r($_POST['data']);
	}
} else {
	echo "No data to save";
}

?>
