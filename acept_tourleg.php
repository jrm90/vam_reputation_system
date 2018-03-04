<?php
	if ($_SESSION["access_flight_validator"] == '1')
	{
		$hoy=date('Y-m-d');

		$tour_pilot_id = $_GET['tour_pilot_id'];
		include('db_login.php');
		$db = new mysqli($db_host , $db_username , $db_password , $db_database);
		if ($db->connect_errno > 0) {
			die('Unable to connect to database [' . $db->connect_error . ']');
		}
		$sql = "UPDATE tour_pilots set status=1 where tour_pilot_id=$tour_pilot_id";
		if (!$result = $db->query($sql)) {
			die('There was an error running the query [' . $db->error . ']');
		}
		// GET TOUR_ID
		$sql = "select * from tour_pilots where tour_pilot_id=$tour_pilot_id";
		if (!$result = $db->query($sql)) {
			die('There was an error running the query [' . $db->error . ']');
		}
		while ($row = $result->fetch_assoc()) {
			$tour_id = $row["tour_id"];
			$gvauser_id = $row["gvauser_id"];
		}
		// GET TOUR NUM LEGS
		$sql = "select count(*) as cnt from tour_legs where tour_id=$tour_id";
		if (!$result = $db->query($sql)) {
			die('There was an error running the query [' . $db->error . ']');
		}
		while ($row = $result->fetch_assoc()) {
			$num_legs = $row["cnt"];
		}
		// GET LEGS VALIDATED
		$sql = "select count(*) as cnt from tour_pilots where tour_id=$tour_id and gvauser_id=$gvauser_id and status=1";
		if (!$result = $db->query($sql)) {
			die('There was an error running the query [' . $db->error . ']');
		}
		while ($row = $result->fetch_assoc()) {
			$legsvalidated = $row["cnt"];
		}
		if ($num_legs == $legsvalidated)
		{
			$sql = "insert tour_finished (gvauser_id,tour_id,finish_date) values ($gvauser_id,$tour_id,curdate())";
			if (!$result = $db->query($sql)) {
				die('There was an error running the query [' . $db->error . ']');
			}

		//Sube el ranting de piloto
		$sql = "SELECT gu.reputation,gu.gvauser_id,rs.reputation_tour FROM gvausers gu INNER JOIN reputation_system rs WHERE gvauser_id=$gvauser_id";
			if (!$result = $db->query($sql)) {
				die('There was an error running the query [' . $db->error . ']');
			}
			while($row=$result->fetch_assoc()) {
				$reputationuser=$row["reputation"];
				$usertype=$row["user_type_id"];
				$reputation_tour=$row["reputation_tour"];
			}
			$sql = "SELECT tour_name FROM tours WHERE tour_id=$tour_id";
			if (!$result = $db->query($sql)) {
				die('There was an error running the query [' . $db->error . ']');
			}
			while($row=$result->fetch_assoc()) {
				$tour_name=$row["tour_name"];
			}

		$new_reputation=$reputationuser+$reputation_tour;
			if($new_reputation>=0&&$new_reputation<=100&&$usertype==2) {
			$sql = "UPDATE gvausers SET reputation=$new_reputation WHERE gvauser_id=$gvauser_id";
				if (!$result = $db->query($sql)) {
					die('There was an error running the query [' . $db->error . ']');
				}
				$descriptionreputation = 'Finalizacion del tour: '.$tour_name;
			$sql2 = "INSERT INTO reputation_changes (user_afected, user_staff, value_changed, value_to_date, description,date_change) VALUES ('$gvauser_id', '1', '$reputation_tour', '$new_reputation', '$descriptionreputation','$hoy');";
				if (!$result2 = $db->query($sql2)) {
					die('There was an error running the query [' . $db->error . ']');
				}
			}
		}
		echo '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=./index_vam_op.php?page=validate_flights">';
	}
	else
	{
		include("./notgranted.php");
	}
?>
