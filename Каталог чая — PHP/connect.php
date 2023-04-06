<?php
	error_reporting(0);
	
	require_once('../../wp-config.php'); // Подключаем Вордпрессовский файл с паролями
	
	$custom_DB_link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	if (!$custom_DB_link) {
    	die('<p style="color:red">'.mysqli_connect_errno().' - '.mysqli_connect_error().'</p>');
	}
	mysqli_query($custom_DB_link, "SET NAMES utf8");
?>