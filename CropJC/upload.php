<?php

include_once("cropjc.php");

$default = array(
		
	"destination_path" => "resources",
	"quality" => 98
);

$cropjc = new cropjc($default);

echo $cropjc->Crop();
