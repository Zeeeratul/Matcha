<?php

session_start();

function dbConnect()
{
	$DB_DSN  = 'mysql:dbname=matcha;host=127.0.0.1';
	$DB_USER = 'root';
	$DB_PASSWORD = 'CTSJkVNyKqaSqM23';	
	try 
	{
	    $DB = new PDO($DB_DSN, $DB_USER, $DB_PASSWORD);
	    $DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 	    return $DB;
	} 
	catch (Exception $e)
	{
	    die('Error : ' . $e->getMessage());
	}
}
	

	$DB = dbConnect();
	$id_user = $_SESSION['uid'];	

	$stmt = $DB->prepare('SELECT COUNT(*) FROM historical WHERE id_user = ? AND bool = 0');
	$stmt->execute([$id_user]); 
	$count = $stmt->fetchColumn();
	if ($count > 0)
	    echo json_encode('1');
	else
	   	echo json_encode('0');



?>