<?php
	$db = new mysqli($db_host , $db_username , $db_password , $db_database);
	$db->set_charset("utf8");
	if ($db->connect_errno > 0) {
		die('Unable to connect to database [' . $db->connect_error . ']');
	}
	$sql = "SELECT * FROM gvausers where gvauser_id!=22 and user_type_id=2 ORDER BY callsign asc";
	if (!$result = $db->query($sql)) {
		die('There was an error running the query [' . $db->error . ']');
	}
	?>

	<?php
	echo '<table class="table table-hover">';
	echo '<tr><th>' . STATISTICS_CALLSIGN . '</th><th>' . STATISTICS_PILOT . '</th><th>' . REP . '</th><th>' . STATISTICS_REP_VAR . '</th></tr>';
	while ($row = $result->fetch_assoc()) {
		$user_id=$row["gvauser_id"];
		echo "<tr><td>";
		echo $row["callsign"] . '</td><td>';
		echo $row["name"] . ' ' . $row["surname"] . '</td><td>';
		$reputation=$row["reputation"];
		if($reputation<=10) { $color='danger'; 
		}elseif($reputation>10&&$reputation<=25) { $color='warning';
		}elseif($reputation>25&&$reputation<=75) { $color='info';
		}elseif($reputation>75) { $color='success'; }
		echo '<div class="progress"><div class="progress-bar bg-'.$color.' progress-bar-striped progress-bar-animated" role="progressbar" style="width: '.$reputation.'%" aria-valuenow="'.$reputation.'" aria-valuemin="0" aria-valuemax="100">'.$reputation.'%</div></div></td>';
		$sql1 = "SELECT sum(value_changed) as var_rep FROM reputation_changes WHERE user_afected='$user_id'";
		if (!$result1 = $db->query($sql1)) {
			die('There was an error running the query [' . $db->error . ']');
		}
		while ($row1 = $result1->fetch_assoc()) {
				$var_rep=$row1["var_rep"];
		}
		if($var_rep>0) {
			if($var_rep>0&&$var_rep<25) {
			echo '<td><img src="./images/up-arrow-rep.png" class="img-fluid" width=25 height=25> ('.$var_rep.')</td>';
			}else{
			echo '<td><img src="./images/up-arrows-rep.png" class="img-fluid" width=25 height=25> ('.$var_rep.')</td>';
			}
		}elseif($var_rep<0) {
			$var_rep=abs($var_rep);
			if($var_rep>0&&$var_rep<25) {
			echo '<td><img src="./images/down-arrow-rep.png" class="img-fluid" width=25 height=25> (-'.$var_rep.')</td>';
			}else{
			echo '<td><img src="./images/down-arrows-rep.png" class="img-fluid" width=25 height=25> (-'.$var_rep.')</td>';
			}		
		}else{
			echo '<td><img src="./images/equal-rep.png" class="img-fluid" width=25 height=25></td>';
		}
		echo '';
		echo "</tr>";
	}
	echo "</table></br>";
	$db->close();
?>
