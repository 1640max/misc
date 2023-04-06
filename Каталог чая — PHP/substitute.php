<?php
error_reporting(0);

	$substitute = array(
		"FA"	=> "FA",
		"LP"	=> "LP",
		"O"		=> "O",
		"ChG"	=> "ChG",

		"Paket"	=> "Paket",
		"List"	=> "List",

		"Pachka"	=> "Pachka",
		"Jest"	=> "Jest",
		"MY"	=> "MY",

		"Black"	=> "Black",
		"Green"	=> "Green",
		"Red"	=> "Red",

		"Prosto"	=> "Prosto",
		"Paskha"	=> "Paskha",
		"New Year"	=> "New Year",
		"Vosmoe marta"		=> "Vosmoe marta"
	);
	
function SQLFilter(&$elem, $key)
{
	global $substitute;
	$elem = $substitute[$elem];
}
?>