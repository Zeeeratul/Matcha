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


$number_comments = $_POST['number_comments'];
$offset = $_POST['offset'];
$convId = $_POST['convId'];
$id_sender = $_SESSION['uid'];

if (isset($_POST['id_first']))
{
	$id_first = $_POST['id_first'];
	$DB = dbConnect();
	$stmt = $DB->prepare('SELECT message, message_id, send_user_id FROM message WHERE id_conversation = ? AND message_id > ? ORDER BY message_id ASC');
	$stmt->execute([$convId, $id_first]);
	$data = $stmt->fetchAll(PDO::FETCH_ASSOC);	
}
else
{
	$DB = dbConnect();
	$stmt = $DB->prepare('SELECT message, message_id, send_user_id FROM message WHERE id_conversation = ? ORDER BY message_id DESC LIMIT ' . $number_comments . " OFFSET " . $offset);
	$stmt->execute([$convId]);
	$data = $stmt->fetchAll(PDO::FETCH_ASSOC);	
}

$json = array();

foreach ($data as $row) {
	$item = array();

	foreach ($row as $key => $value) {
		$item[$key] = $value;
	}
	array_push($json, $item);
}

echo json_encode($json);

?>