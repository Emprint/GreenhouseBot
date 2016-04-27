<?php
require_once('config.inc.php');
$query = 'SELECT * FROM `'._DB_TABLE_.'` ORDER BY `date_add` ASC';
$mysql = new MySQL();

function JSdate($in,$type){
    if($type=='date'){
        //Dates are patterned 'yyyy-MM-dd'
        preg_match('/(\d{4})-(\d{2})-(\d{2})/', $in, $match);
    } elseif($type=='datetime'){
        //Datetimes are patterned 'yyyy-MM-dd hh:mm:ss'
        preg_match('/(\d{4})-(\d{2})-(\d{2})\s(\d{2}):(\d{2}):(\d{2})/', $in, $match);
    }
     
    $year = (int) $match[1];
    $month = (int) $match[2] - 1; // Month conversion between indexes
    $day = (int) $match[3];
     
    if ($type=='date'){
        return "Date($year, $month, $day)";
    } elseif ($type=='datetime'){
        $hours = (int) $match[4];
        $minutes = (int) $match[5];
        $seconds = (int) $match[6];
        return "Date($year, $month, $day, $hours, $minutes, $seconds)";    
    }
}

function getData($result) {
	return $result == '' ? 'undefined' : $result;
}

$data = array("Extérieur" => array(), "Intérieur" => array(), "Boîtier" => array(), "Humidité" => array());
if($results = $mysql->ExecuteS($query)) {
	foreach($results as $result) {
		$data['Extérieur'][] = 	'{x: new '.JSdate($result['date_add'], 'datetime').', y: '.getData($result['data1']).'}';
		$data['Intérieur'][] = 	'{x: new '.JSdate($result['date_add'], 'datetime').', y: '.getData($result['data2']).'}';
		$data['Boîtier'][] = 	'{x: new '.JSdate($result['date_add'], 'datetime').', y: '.getData($result['data3']).'}';
		$data['Humidité'][] = 		'{x: new '.JSdate($result['date_add'], 'datetime').', y: '.getData($result['data4']).'}';
	}
	
	//print_r($data);
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Greenhouse's Cam</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
		<script src="canvasjs-1.8.0/jquery.canvasjs.min.js"></script>
		<style>
			BODY { padding: 0; margin: 0; font-family: Helvetica, Arial, sans-serif; font-size: 11px; }
			H3 { padding-left: 5px; }
			P { padding: 5px; margin: 0; }
			IMG { display: block; max-width: 100%; }
			.timestamp { display: block; position: absolute; color: white; top: 0; left: 0;  padding: 3px; text-shadow: 0px 0px 8px rgba(0, 0, 0, 1); }
			button { padding: 3px 4px 2px !important; }
		</style>
		<script>
		$(function () {
			CanvasJS.addCultureInfo("fr",
                {
                    decimalSeparator: ",",
                    digitGroupSeparator: ",",
                    days: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"],
                    shortDays: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"],
                    months: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "September", "Octobre", "Novembre", "Décembre"],
                    shortMonths: ["Jan", "Fév", "Mar", "Avr", "Mai", "Juin", "Juil", "Aoû", "Sep", "Oct", "Nov", "Déc"]
               });
               
			$("#temperatures").CanvasJSChart({ //Pass chart options
				theme: "theme1",
				culture: "fr",
				axisX: {
					//labelFormatter: function (e) { return CanvasJS.formatDate( e.value, "DD MMM"); },
					valueFormatString: "DD MMM",
					labelFontFamily: "Helvetica, Arial",
					labelFontWeight: "normal",
				},
				axisY: {
					labelFontFamily: "Helvetica, Arial",
					labelFontWeight: "normal",
					title: "Température",
					titleFontFamily: "Helvetica, Arial",
					titleFontWeight: 'normal',
					titleFontSize: 10,
					includeZero: false,
      			},
      			axisY2: {
        			title: "Humidité",
					titleFontFamily: "Helvetica, Arial",
					titleFontWeight: 'normal',
					titleFontSize: 10,
					includeZero: false,
				},
				legend: {
					fontFamily: "Helvetica, Arial",
				},
				toolTip: {
					shared: true,
					borderThickness: 0,
					cornerRadius: 0,
					fontStyle: 'normal',
					contentFormatter: function (e) {
						var content = "<strong>" + CanvasJS.formatDate(e.entries[0].dataPoint.x, "DD MMMM HH:mm", "fr") + "</strong><br />";
						for (var i = 0; i < e.entries.length; i++) {
							content +=  e.entries[i].dataSeries.name + " : " + (typeof e.entries[i].dataPoint.y != "undefined" ? e.entries[i].dataPoint.y + (i < 3 ? "°C" : "%") : "N/A");
							content += "<br/>";
						}
						return content;
					}
				},
				zoomEnabled: true,
				panEnabled: true, 
				data: [
					<?php
						$colors = array('Extérieur' => 'blue', 'Intérieur' => 'lime', 'Boîtier' => 'grey', 'Humidité' => 'cyan');
						foreach($data as $label => $values) {
							echo '{
								name: "'.$label.'",
        						showInLegend: true,
								markerType: "none",
								type: "line", //change it to column, spline, line, pie, etc
								dataPoints: ['.implode(', ', $values).'],
								axisYType: "'.($label == 'Humidité' ? 'secondary' : 'primary').'",'.(array_key_exists($label, $colors) ? 'color: "'.$colors[$label].'",' : '').'
							},';
						}
					?>
				]
			});
		});
	</script>
	</head>
	<body>
		<div class="image">
			<?php
				require_once('last-image.php');
			?>
		</div>
		<p>Cette page se rafraîchit automatiquement toutes les 3 minutes. La température est mise à jour toute les 30 minutes.</p>
		
		<div id="temperatures" style="width:95%; height:300px;"></div>
		
		<h3>Archives</h3>
		<ul>
		<?php
		$videos_path = 'videos';
		$videos = scandir($videos_path, SCANDIR_SORT_DESCENDING);
		foreach($videos as $video) {
			$vfile = $videos_path.'/'.$video;
			if(is_file($vfile) && $vfile != '.htaccess') {
				echo '<li><a href="'.$vfile.'" target="_blank">'.$video.'</a></li>';
			}
		}
		?>
		</ul>
		<script>
			$(document).ready(function() {
				 initTimeout();
			});
			
			function lastImage() {
				$('.image').load('last-image.php?t=' + new Date().getTime(), function() { initTimeout(); });
			}
			
			function initTimeout() {
				setTimeout(lastImage, 300000);
			}
		</script>
	</body>
</html>
