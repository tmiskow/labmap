<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
		<link href="https://fonts.googleapis.com/css?family=PT+Sans+Caption" rel="stylesheet">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<link href="https://use.fontawesome.com/releases/v5.0.4/css/all.css" rel="stylesheet">
		<link rel="stylesheet" href="index.css">
		<title>LabMap</title>

		<?php
			// initialize color dictionary
			$colors = array(
				2041 => "#D50000",
				2042 => "#C51162",
				2043 => "#FF6D00",
				2044 => "#795548",
				2045 => "#FFC107",
				3041 => "#C3B091",
				3042 => "#4CAF50",
				3043 => "#00BCD4",
				3044 => "#2196F3",
				3045 => "#6A1B9A",
			);

			// initialize icon dictionary
			$icons = array(
				"user" => "<i class='material-icons'>person</i>",
				"linux" => "<img src='fonts/linux.svg' alt='linux'>",
				"macos" => "<img src='fonts/apple.svg' alt='linux'>",
				"windows" => "<img src='fonts/windows.svg' alt='linux'>",
			);

			// show errors
			ini_set("display_errors", 1);
			ini_set("display_startup_errors", 1);
			error_reporting(E_ALL);

			// access username and password
			$secret_filename = ""; // path to file containing databse username and password
			$secret_file = fopen(__DIR__.$secret_filename, "r") or die("Unable to open file!");
			$username = trim(fgets($secret_file));
			$password = trim(fgets($secret_file));
			fclose($secret_file);

			// connect to database
			$connection = oci_connect($username, $password, "labs", "EE8ISO8859P2") or die("Unable to connect to database!");
		?>
	</head>
	<body>
		<div id="header">LabMap</div>
		<div id="main">
			<?php
				$room_statement = oci_parse($connection, "SELECT * FROM {$username}.rooms");
				oci_execute($room_statement, OCI_NO_AUTO_COMMIT);

				while ($room_row = oci_fetch_array($room_statement, OCI_BOTH)) {
					$room_id = $room_row['ROOM_ID'];
					$room_color = $room_row['COLOR'];

					$course_statement = oci_parse($connection, "SELECT course_name, to_char(start_time, 'HH24:MI') AS start_time, to_char(end_time, 'HH24:MI') AS end_time, teacher_name, teacher_surname FROM {$username}.full_current_timetable WHERE room_id = {$room_id}");
                    oci_execute($course_statement, OCI_NO_AUTO_COMMIT);


					echo "<div class='card-wrapper'>\n";
					echo "\t<div class='card'>\n";
					echo "\t\t<div class='card-header' style='background-color: {$colors[$room_id]};'>\n";

					echo "\t\t\t<div class='card-header-number'>{$room_id}</div>\n";

					if ($course_row = oci_fetch_array($course_statement, OCI_BOTH)) {
                    	$course_name = $course_row["COURSE_NAME"];
                        $course_start = $course_row["START_TIME"];
                        $course_end = $course_row["END_TIME"];
                        $teacher_name = $course_row["TEACHER_NAME"];
                        $teacher_surname = $course_row["TEACHER_SURNAME"];

                        echo "\t\t\t<div class='card-header-course'>\n";
                        echo "\t\t\t\t<div class='card-header-course-name'>{$course_name}</div>\n";
						echo "\t\t\t\t<div class='card-header-course-time'>{$course_start} - {$course_end}</div>\n";
						echo "\t\t\t</div>\n";
                    }
					echo "\t\t</div>\n";

                    echo "\t\t<div class='card-wrap'>\n";

					$computer_statement = oci_parse($connection, "SELECT * FROM {$username}.computers LEFT JOIN users ON user_ref = login WHERE room_ref = {$room_id} ORDER BY \"index\"");
                    oci_execute($computer_statement, OCI_NO_AUTO_COMMIT);

					while ($computer_row = oci_fetch_array($computer_statement, OCI_BOTH)) {
						$computer_index = $computer_row['index'];
                        $computer_name = "{$room_color}{$computer_index}";
                        $computer_state = $computer_row["STATE"];


                        if (oci_field_is_null ($computer_statement, "USER_REF")) {
                        	$user_id = "";
                        	$user_login = "";
                            $user_name = "";
                            $user_surname = "";
                        } else {
                        	$user_id = $computer_row["USER_REF"];
                        	$user_login = $computer_row['LOGIN'];
                            $user_name = $computer_row['NAME'];
                            $user_surname = $computer_row['SURNAME'];
                        }


                        if ($computer_state != "off") {
                        	echo "\t\t\t<div class='computer-block'>\n";
                        } else {
                        	echo "\t\t\t<div class='computer-block inactive'>\n";
                        }


                        echo "\t\t\t\t<div class='computer-name'>{$computer_name}</div>\n";
                        echo "\t\t\t\t<div class='computer-info'>\n";


                        if (oci_field_is_null ($computer_statement, "USER_REF") && $computer_state != "off") {
                        	echo "\t\t\t\t\t<div class='computer-text'>{$computer_state}</div>\n";
                        } else if ($computer_state != "off") {
                        	echo "\t\t\t\t\t<div class='computer-text'>{$user_name} {$user_surname} ({$user_login})</div>\n";
                        }


                        echo "\t\t\t\t\t<div class='computer-icons'>\n";


                        if (!oci_field_is_null ($computer_statement, "USER_REF")) {
                        	echo "\t\t\t\t\t\t{$icons["user"]}\n";
                        }

                        if ($computer_state != "off") {
                    		$computer_icon = $icons[$computer_state];
                    		echo "\t\t\t\t\t\t{$computer_icon}\n";
                    	}

                        echo "\t\t\t\t\t</div>\n";
                        echo "\t\t\t\t</div>\n";
                        echo "\t\t\t</div>\n";
                    }

					echo "\t\t</div>\n";
					echo "\t</div>\n";
					echo "</div>\n";
				}

				for ($i=0; $i < 4; $i++) {
					echo "<div class='card-wrapper hidden'></div>\n";
				}
			?>
		</div>
			<div id="footer"><div id="footer-right">
				<?php
					$update_time_statement = oci_parse($connection, "SELECT to_char(update_time, 'DD.MM.YYYY HH24:MI') as update_time FROM updates");
	                oci_execute($update_time_statement, OCI_NO_AUTO_COMMIT);
	                $update_time_row = oci_fetch_array($update_time_statement, OCI_BOTH);
	                $update_time = $update_time_row['UPDATE_TIME'];
	                echo "Last update: {$update_time}";
				?>
			</div>
		</div>
	</body>

	<?php
		oci_commit($connection);
	?>
</html>
