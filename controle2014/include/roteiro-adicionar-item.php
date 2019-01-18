<?
session_start();

header('Content-Type: text/html; charset=utf-8'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Expires: Fri, 1 Jan 2010 08:00:00 GMT"); // Date in the past 

$sucesso = false;

$setor = $_POST['setor'];

echo json_encode(array("sucesso"=>$sucesso, "lista"=>$lista));

?>