<?php
include('db_login.php');
include('classes/class_vam_mailer.php');

$db = new mysqli($db_host , $db_username , $db_password , $db_database);
if ($db->connect_errno > 0) {
	die('Unable to connect to database [' . $db->connect_error . ']');
}
$sql="SELECT * FROM reputation_system";
	if (!$result = $db->query($sql)) {
		die('There was an error running the query [' . $db->error . ']');
	}
	while($row=$result->fetch_assoc()) {
		$daysinactivate=$row["days_to_inactivate"];
		$percent_f=$row["percent_flights"];
		$percent_v=$row["percent_visits"];
	}

//Calculate how many reputation should down in case of inactivity
$rep_down_flight=$percent_f/$daysinactivate;
$rep_down_visit=$percent_v/$daysinactivate;
$hoy=date('Y-m-d');
echo 'Baja '.$rep_down_flight.' por cada dia sin volar y baja '.$rep_down_visit.' por cada dia sin logear.<p>Hoy: '.$hoy.'.<p><p><p>';

//Collect data from tables
$sql1="SELECT g.gvauser_id,g.callsign,g.name,g.surname,date_format(g.register_date,'%Y-%m-%d') as register_date,init_date,final_date,g.reputation,g.last_forum,date_format(last_visit_date,'%Y-%m-%d') as last_visit_date,date_format(max_date,'%Y-%m-%d') as max_date,d.date_i_period,d.date_f_period,datediff(d.date_f_period,d.date_i_period)+1 as days_period FROM gvausers g left outer join (SELECT pilot,MAX(date) AS max_date FROM v_total_data_flight group by pilot) t on (g.gvauser_id=t.pilot) left outer join (SELECT init_date,final_date,id_user FROM periods where validated=1 and '$hoy' BETWEEN init_date and final_date) p on (g.gvauser_id=p.id_user) left outer join (SELECT id_user,max(init_date) as date_i_period,max(final_date) as date_f_period FROM periods where validated=1) d on (g.gvauser_id=d.id_user) where g.gvauser_id!=23 and g.user_type_id=2";
	if (!$result1 = $db->query($sql1)) {
		die('There was an error running the query [' . $db->error . ']');
	}
	while($row1=$result1->fetch_assoc()) {
		$gvauser_id=$row1["gvauser_id"];
		$callsign=$row1["callsign"];
		$name=$row1["name"];
		$surname=$row1["surname"];
		$register_date=$row1["register_date"];
		$segundos=strtotime($hoy) - strtotime($register_date);
		$dif_register_date=intval($segundos/60/60/24);


		$last_visit_date = (is_null($row1["last_visit_date"])) ? $register_date : $row1["last_visit_date"] ;
		$segundos=strtotime($hoy) - strtotime($last_visit_date);
		$dif_last_visit_date=intval($segundos/60/60/24);

		$last_forum = (is_null($row1["last_forum"])) ? $register_date : $row1["last_forum"] ;
		$segundos=strtotime($hoy) - strtotime($last_forum);
		$dif_last_forum=intval($segundos/60/60/24);


		$max_date = (is_null($row1["max_date"])) ? $register_date : $row1["max_date"] ;
		$segundos=strtotime($hoy) - strtotime($max_date);
		$dif_max_date=intval($segundos/60/60/24);


		$rep=$row1["reputation"];
		$idate=$row1["init_date"];
		$fdate=$row1["final_date"];

		$sql4="SELECT max(date_change) as checkdate,value_to_date FROM reputation_changes WHERE user_afected='$gvauser_id' and description LIKE 'Inactivity%' ";
			if (!$result4 = $db->query($sql4)) {
			die('There was an error running the query [' . $db->error . ']');
			}
			while($row4=$result4->fetch_assoc()) {
			$checked = (is_null($row4["checkdate"])) ? date("Y-m-d",mktime(-168)) : $row4["checkdate"] ;
			}

		$segundos=strtotime($hoy) - strtotime($checked);
		$dif_check=intval($segundos/60/60/24);

		echo '<p><hr><b>Piloto: </b>'.$name.' '.$surname.'<p><b>Registro: </b>'.$register_date.' ('.$dif_register_date.') <b>Visita: </b>'.$last_visit_date.' ('.$dif_last_visit_date.') <b>Foro: </b>'.$last_forum.' ('.$dif_last_forum.') <b>Vuelo: </b>'.$max_date.' ('.$dif_max_date.') <b>Rep: </b>'.$rep.' <p><b>Init Date: </b>'.$idate.' <b>Final Date: </b>'.$fdate.' <b>Comprobado: </b>'.$checked.' ('.$dif_check.')<p>';


	//IF is on holidays or have been checked since 6 days, dont do anything
	if((!is_null($idate)&&!is_null($fdate))||$dif_check<=6) {
		echo '<p>- Vacaciones o comprobacion hace menos de 6 dias.';
	//IF is not on holidays and dont have been checked since 6 days
	}else{
		//Check if there are any holidays between today and last check
		$sql6="SELECT days FROM periods where id_user='$gvauser_id' and (init_date between '$checked' and '$hoy') and (final_date between '$checked' and '$hoy')";
			if (!$result6 = $db->query($sql6)) {
				die('There was an error running the query [' . $db->error . ']');
			}
			while($row6=$result6->fetch_assoc()) {
				echo 'Dias de vacaciones: '.$row6["days"].'<p>';
				$dif_last_visit_date=$dif_last_visit_date-$row6["days"];
				$dif_max_date=$dif_max_date-$row6["days"];
			}

		//Calculate reputation down about days without flights
		if($dif_max_date>=6) {
			$dif_flights=($dif_max_date>$dif_check) ? $dif_check : $dif_max_date ;
			$var_rep_f=round($rep_down_flight*$dif_flights);
			echo '<p>- Lleva sin volar mas de 6 dias ('.$dif_flights.' dias).<p>- Bajamos rep por vuelos: '.$var_rep_f;
		}else{
			$var_rep_f='0';
		}

		//Calculate reputation down about days without login
		if ($dif_last_visit_date>=6) {
			$dif_login=($dif_last_visit_date>$dif_check) ? $dif_check : $dif_last_visit_date ;
			$var_rep_v=round($rep_down_visit*$dif_login);
			echo '<p>- Lleva sin logear mas de 6 dias ('.$dif_login.' dias).<p>- Bajamos rep por login: '.$var_rep_v;
		}else{
			$var_rep_v='0';
		}
	}

	$var_rep=$var_rep_f+$var_rep_v;
	$rep_result=$rep-$var_rep;
	$rep_result=($rep_result<=5) ? 5 : $rep_result ;
	echo '<p><b>Reputacion Resultante: </b>'.round($rep_result).' (-'.round($var_rep).')';
	if($var_rep>0) {
		//Insert reputation change in tables and update reputation to user
		$description='Inactivity (Flights ('.$dif_max_date.'): '.$var_rep_f.' Login ('.$dif_last_visit_date.'): '.$var_rep_v.')';
		$sql2="INSERT INTO reputation_changes (user_afected,user_staff,date_change,value_changed,value_to_date,description) VALUES ('$gvauser_id','23','$hoy','$var_rep','$rep_result','$description')";
		if (!$result2 = $db->query($sql2)) {
			die('There was an error running the query [' . $db->error . ']');
		}
		$sql3="UPDATE gvausers SET reputation='$rep_result' WHERE gvauser_id='$gvauser_id'";
		if (!$result3 = $db->query($sql3)) {
			die('There was an error running the query [' . $db->error . ']');
		}
	}

	if($rep<=5&&$rep_result<=5) {
		echo '<p><b>Este piloto tiene una inactividad continuada</b>';

		$sql5="SELECT max(warning_date) as date FROM `pilot_warning` WHERE gvauser_id=9'$gvauser_id'";
		if (!$result5 = $db->query($sql5)) {
			die('There was an error running the query [' . $db->error . ']');
		}
		while($row6=$result6->fetch_assoc()) {
			$date_warning=$row5["date"];
			$segundos=strtotime($hoy) - strtotime($date_warning);
			$dif_warning=intval($segundos/60/60/24);
		}
		// Send mail to the pilot only if last mail 7 days ago
		if($dif_warning>=7) {
		$mail = new vam_mailer();
		$mail->mail_staff_warning_pilot($gvauser_id,$callsign,$name,$surname,$dif_register_date,$dif_max_date,$dif_last_visit_date);
	}
	}
	}//Fin while datos piloto
$db->close();
