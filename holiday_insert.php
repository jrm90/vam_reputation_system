<?php
	/**
	 * @Project: Virtual Airlines Manager (VAM)
	 * @Author: Alejandro Garcia
	 * @Web http://virtualairlinesmanager.net
	 * Copyright (c) 2013 - 2016 Alejandro Garcia
	 * VAM is licenced under the following license:
	 *   Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA 4.0)
	 *   View license.txt in the root, or visit http://creativecommons.org/licenses/by-nc-sa/4.0/
	 */
?>
<?php
	require('check_login.php');
	include('get_pilot_data.php');
	if (is_logged()&&$_SESSION["access_pilot_manager"]==1)
	{
		$db = new mysqli($db_host , $db_username , $db_password , $db_database);
		$db->set_charset("utf8");
		if ($db->connect_errno > 0) {
			die('Unable to connect to database [' . $db->connect_error . ']');
		}
		$i_date = $_POST['init_date'];
		$f_date = $_POST['final_date'];
		$sql = "INSERT INTO periods (init_date,final_date,id_user) VALUES ('$i_date','$f_date','$id')";
		if (!$result = $db->query($sql)) {
			die('There was an error running the query [' . $db->error . ']');
		}
		$sql1 = "UPDATE periods set days=datediff(final_date,init_date)+1";
		if (!$result = $db->query($sql1)) {
			die('There was an error running the query [' . $db->error . ']');
		}

		$db->close();
		echo '<META HTTP-EQUIV="Refresh" CONTENT="0; URL=./index_vam.php?page=pilot_options">';
	}
else
	{
		include("./notgranted.php");
	}
?>