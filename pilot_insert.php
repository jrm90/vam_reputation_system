<?php
	$name = $_POST['name'];
	$surname = $_POST['surname'];
	$email = $_POST['email'];
	$ivao = $_POST['ivao'];
	$vatsim = $_POST['vatsim'];
	$city = $_POST['city'];
	$hub_id = $_POST['hub'];
	$pass = $_POST['password'];
	$country = $_POST['country'];
	$birthday = $_POST['birthdate'];
	$notes = $_POST['notes'];
	$vatsim = $_POST['vatsim'];
	$language = $_POST['language'];
	$captcha = $_POST['captcha'];
	$captchahidden = $_POST['captchahidden'];
	if ($captcha!= $captchahidden)
	{
	?>
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading"><?php echo REGISTER_WRONG; ?></div>
				<div class="alert alert-danger" role="alert"><?php echo REGISTER_CAPTCHA_MSG; ?></div>
			<div>
		<div>
	<div>
	<?php
	}
	else
	{
		$db = new mysqli($db_host , $db_username , $db_password , $db_database);
		$db->set_charset("utf8");
		if ($db->connect_errno > 0) {
			die('Unable to connect to database [' . $db->connect_error . ']');
		}
		$sql = "SELECT * from gvausers WHERE email='$_POST[email]' ";
		if (!$result = $db->query($sql)) {
			die('There was an error running the query [' . $db->error . ']');
		}
		$existentuser = $result->num_rows;
		if ($existentuser > 0)
		{
?>
			<div class="row">
				<div class="col-md-12">
					<div class="panel panel-default">
						<div class="panel-heading"><?php echo REGISTER_WRONG; ?></div>
						<div class="alert alert-danger" role="alert"><?php echo REGISTER_WRONG_MSG; ?></div>
					<div>
				<div>
			<div>
<?php
		}
		else
		{
			if ($_POST["password"])
			{
				$encryptpassword = md5($pass);
				echo '<br>';
				$sql1 = "insert into gvausers (register_date,activation,name,surname,callsign,email,password,ivaovid,hub_id,country,city,reg_comments,birth_date,vatsimid,language)
                    values (now(),0,'$name','$surname','_NEW_','$email','$encryptpassword','$ivao','$hub_id','$country','$city','$notes','$birthday','$vatsim','$language');";
				if (!$result = $db->query($sql1)) {
					die('There was an error running the query [' . $db->error . ']');
				}
				$sql4="SELECT max(gvauser_id) as id FROM gvausers";
				if (!$result4=$db->query($sql4)) {
					die('There was an error running the query ['.$db->error.']');
				}
				while($row4=$result4->fetch_assoc()) {
					$id=$row4["id"];
				}
				$sql5="INSERT INTO reputation_changes (user_afected,user_staff,date_change,value_changed,value_to_date,description) VALUES ('$id','23',now(),'50','50','New pilot')";
				if (!$result5=$db->query($sql5)) {
					die('There was an error running the query ['.$db->error.']');
				}

			}
			// Send mail to the pilot
			$mail = new vam_mailer();
			$mail->mail_register_compose($email);
?>
			<div class="row">
				<div class="col-md-12">
					<div class="panel panel-default">
						<div class="panel-heading"><?php echo REGISTER_SUCCESSFUL; ?></div>
						<div class="alert alert-success" role="alert"><?php echo REGISTER_SUCCESSFUL_MSG; ?></div>
					<div>
				<div>
			<div>
<?php
		}
		$db->close();
	}
?>
