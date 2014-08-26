<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once("cropjc.php");
//print_r($_POST["dimensions"]);exit;

/*
print_r($_POST);
print_r($_FILES);
exit;
*/

//$image = file_get_contents("imagetest.jpg");

/*
	"xPos" => 0,
	"yPos" => 0,
	"cropH" => 200,
	"cropW" => 200,
*/
$default = array(
		
	"destination_path" => "resources",
	"quality" => 98
);

$cropjc = new cropjc($default);

echo $cropjc->Crop();
