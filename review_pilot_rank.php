<?php
				$hoy=date('Y-m-d');

	$sql = "select * from va_parameters ";
	if (!$result = $db->query($sql)) {
		die('There was an error running the query [' . $db->error . ']');
	}
	while ($row = $result->fetch_assoc()) {
		$no_count_rejected = $row["no_count_rejected"];
	}
	//echo 'Contar no aceptados: '.$no_count_rejected.'<br>';
	$sql1 = "select * from reputation_system";
	if (!$result1 = $db->query($sql1)) {
		die('There was an error running the query [' . $db->error . ']');
	}
	while ($row1 = $result1->fetch_assoc()) {
		$active_reputation = $row1["active_reputation"];
		$auto_update_rank_reputation_up = $row1["auto_update_rank_reputation_up"];
		$auto_update_rank_reputation_down = $row1["auto_update_rank_reputation_down"];
		//echo 'Reputation active: '.$active_reputation.' Rep. Subida: '.$auto_update_rank_reputation_up.' Rep. Bajada: '.$auto_update_rank_reputation_down.'<br><hr>';
	}
	$sql8="SELECT max(rank_id) as maximo, min(rank_id) as minimo FROM ranks";
	if (!$result8 = $db->query($sql8)) {
		die('There was an error running the query [' . $db->error . ']');
	}
	while ($row8 = $result8->fetch_assoc()) {
		$min_rank = $row8["minimo"];
		$max_rank = $row8["maximo"];

	}
	if ($no_count_rejected==1)
	{
		$sql = "select gu.user_type_id,gu.gvauser_id,gu.callsign,gu.name,gu.surname,gu.rank_id,r.rank,(v.gva_hours+gu.transfered_hours) as total_time,gu.reputation from gvausers gu,ranks r, (select 0 + sum(time) as gva_hours, pilot from v_pilot_roster_rejected vv group by pilot) as v where gu.rank_id=r.rank_id and v.pilot = gu.gvauser_id order by callsign asc";
	}else{
		$sql = "select gu.user_type_id,gu.gvauser_id,gu.callsign,gu.name,gu.surname,gu.rank_id,r.rank,(v.gva_hours+gu.transfered_hours) as total_time,gu.reputation from gvausers gu,ranks r, (select 0 + sum(time) as gva_hours, pilot from v_pilot_roster vv group by pilot) as v where gu.rank_id=r.rank_id and v.pilot = gu.gvauser_id order by callsign asc";
	}
	if (!$result = $db->query($sql)) {
		die('There was an error running the query [' . $db->error . ']');
	}
	while ($row = $result->fetch_assoc()) {
		$pilot = $row['gvauser_id'];
		$total_time_current=$row['total_time'];
		$rank_current= $row['rank_id'];
		$rep= $row['reputation'];
		$usertype= $row['user_type_id'];

		$sql2 = "select rank_id,rank from ranks where minimum_hours<='$total_time_current' and maximum_hours>'$total_time_current'";
		if (!$result2 = $db->query($sql2)) {
			die('There was an error running the query [' . $db->error . ']');
		}
		while ($row2 = $result2->fetch_assoc()) {
			$rank_new= $row2['rank_id'];
			$rank_new_name= $row2['rank'];
		}
		//echo '<br><b>Piloto:</b> '.$row["callsign"].'-'.$row["name"].' '.$row["surname"].' <b>Rep:</b> '.$rep.' <b>Horas:</b> '.$total_time_current.' <b>Rango actual:</b> '.$rank_current.' <b>Rango asignado:</b> '.$rank_new;
		//Hasta aqui todo guay, tenemos todos los datos listos para procesar

		//If para reputacion desactivada o staff
		if($active_reputation!=1||$usertype!=2) {
			//echo ' <b>==></b> Aqui sube si no hay reputacion activa o es staff.';
			$sql3 = "update gvausers set rank_id='$rank_new' where gvauser_id='$pilot'";
			if (!$result3 = $db->query($sql3)) {
				die('There was an error running the query [' . $db->error . ']');
			}
		//If-else para reputacion activa y pilotos
		}else{
			//IF si sube de rango con reputacion alta
			if($rank_current<$rank_new&&$rep>$auto_update_rank_reputation_up&&$max_rank!=$rank_current) {
				//echo ' Este tio tiene que subir de rango.';
				if($rank_current<$max_rank) {$rank_current=$rank_current+1;}
				$sql5 = "select rank_id,rank from ranks where rank_id='$rank_current'";
				if (!$result5 = $db->query($sql5)) {
					die('There was an error running the query [' . $db->error . ']');
				}
				while ($row5 = $result5->fetch_assoc()) {
					$rank_new= $row5['rank_id'];
					$rank_new_name= $row5['rank'];
				}
				$sql3 = "update gvausers set rank_id='$rank_new',reputation='50' where gvauser_id=$pilot";
				if (!$result3 = $db->query($sql3)) {
					die('There was an error running the query [' . $db->error . ']');
				}
				$description='Rank up ('.$rank_new_name.')';
				$sql4 = "INSERT INTO reputation_changes (user_afected, user_staff, value_changed, value_to_date, description,date_change) VALUES ('$pilot', '23', '50', '50', '$description','$hoy');";
					if (!$result4 = $db->query($sql4)) {
						die('There was an error running the query [' . $db->error . ']');
					}
			//ELSE-IF si baja de rengo por reputacion baja
			}elseif($rep<=$auto_update_rank_reputation_down&&$min_rank!=$rank_current) {
				//echo ' Este tio tiene que bajar de rango.';
				if($rank_current>$min_rank) {$rank_current=$rank_current-1;}
				$sql5 = "select rank_id,rank from ranks where rank_id='$rank_current'";
				if (!$result5 = $db->query($sql5)) {
					die('There was an error running the query [' . $db->error . ']');
				}
				while ($row5 = $result5->fetch_assoc()) {
					$rank_new= $row5['rank_id'];
					$rank_new_name= $row5['rank'];
				}
				$sql3 = "update gvausers set rank_id='$rank_new',reputation='20' where gvauser_id=$pilot";
				if (!$result3 = $db->query($sql3)) {
					die('There was an error running the query [' . $db->error . ']');
				}
				$description='Rank down ('.$rank_new_name.')';
				$sql4 = "INSERT INTO reputation_changes (user_afected, user_staff, value_changed, value_to_date, description,date_change) VALUES ('$pilot', '23', '20', '20', '$description','$hoy');";
					if (!$result4 = $db->query($sql4)) {
						die('There was an error running the query [' . $db->error . ']');
					}
			}
		}

	//Aqui cierra la llave del while con los datos de los pilotos
	}

			/*$sql3 = "update gvausers set rank_id=$rank_new where gvauser_id=$pilot";
			if (!$result3 = $db->query($sql3)) {
				die('There was an error running the query [' . $db->error . ']');
			}

			UPDATE gvausers SET rank_id=1,reputation=76 WHERE user_type_id=2 
			*/
?>