<?php
	class Database {
		// logging information
		private $secret_filename = ""; // path to file containing databse username and password 
		private $username;
		private $password;

		// connection information
		private $connection_string = "labs";
		private $encoding = "EE8ISO8859P2";

		public function __construct() {
			// setup logging information
			$secret_file = fopen(__DIR__ . $this->secret_filename, "r") or die("Unable to open file!");
			$this->username = trim(fgets($secret_file));
			$this->password = trim(fgets($secret_file));
			fclose($secret_file);
		}

		private function get_computers_data($connection, $room_id, $room_color) {
			$statement = oci_parse($connection, "SELECT * FROM {$this->username}.computers LEFT JOIN users ON user_ref = login WHERE room_ref = {$room_id} ORDER BY \"index\"");
            oci_execute($statement, OCI_NO_AUTO_COMMIT);

            $data = array();
			while ($row = oci_fetch_array($statement, OCI_BOTH)) {
				$computer_data = array(
					"name" => "{$room_color}{$row['index']}",
					"state" => $row["STATE"]
				);


                if (!oci_field_is_null ($statement, "USER_REF")) {
                	$computer_data["user"] = array(
                		'login' => $row["LOGIN"],
                		'name' => $row['NAME'],
                		'surname' => $row['SURNAME'] 
                	);
                } else {
                	$computer_data["user"] = NULL;
                }

                array_push($data, $computer_data);
            }

            return $data;
		}

		private function get_rooms_data($connection) {
			$statement = oci_parse($connection, "SELECT * FROM {$this->username}.rooms");
			oci_execute($statement, OCI_NO_AUTO_COMMIT);

			$data = array();
			while ($row = oci_fetch_array($statement, OCI_BOTH)) {
				$room_id = $row["ROOM_ID"];
				$room_color = $row["COLOR"];

				$data[$room_id] = array(
					"color" => $room_color,
					"computers" => $this->get_computers_data($connection, $room_id, $room_color)
				);
			}

			return $data;
		}

		private function get_meta_data($connection) {
			$statement = oci_parse($connection, "SELECT to_char(update_time, 'DD.MM.YYYY HH24:MI') as update_time FROM updates");
            oci_execute($statement, OCI_NO_AUTO_COMMIT);
            
            $row = oci_fetch_array($statement, OCI_BOTH);
            $data = array(
				"updateTime" => $row['UPDATE_TIME']
			);

			return $data;
		}

		public function get_data() {
			$connection = oci_connect($this->username, $this->password, $this->connection_string, $this->encoding) or die("Unable to connect to database!");

			$data = array(
				"meta" => $this->get_meta_data($connection),
				"rooms" => $this->get_rooms_data($connection)
			);

			oci_commit($connection);

			return $data;
		}					
	}

	function search_data($data, $parameters) {
		if (count($parameters) > 0 && array_key_exists($parameters[0], $data)) {
			if (count($parameters) > 1) {
				$data = $data[$parameters[0]];
				array_shift($parameters);
				return search_data($data, $parameters);
			} else {
				return $data[$parameters[0]];
			}
		} else {
			return null;
		}
	}
?>