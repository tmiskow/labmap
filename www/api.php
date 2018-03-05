<?php
	header('Content-Type: application/json; charset=utf-8');
	require_once('data.php');

	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
	error_reporting(E_ALL);

	//and make it work with your exsisting code
	$uri = array_filter(explode("/", $_SERVER["REQUEST_URI"]), "strlen");

	if ($uri[3] == "api") {
		$parameters = array_slice($uri, 3);

		$database = new Database(); 
		$full_data = $database->get_data();

		$data = search_data($full_data, $parameters);

		if ($data) {
			echo json_encode(array(
				"code" => 200,
				"message" => "Success.",
				"data" => $data)
			);
		} else {
			echo json_encode(array(
				"code" => 400,
				"message" => "Bad request.",
				"data" => null)
			);
		}
	} else {
		echo json_encode(array(
				"code" => 404,
				"message" => "Object not found.",
				"data" => null)
		);
	}
?>
