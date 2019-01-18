<?

//Verificamos o dominio
include(__DIR__."/checkwww.php");

//Banco de dados
include(__DIR__."/../conn/conn.php");
include(__DIR__."/../../conn/conn.php");

include(__DIR__."/../../conn/conn-sankhya.php");
include(__DIR__."/../conn/conn-sankhya.php");

// Checar usuario logado
include(__DIR__."/checklogado.php");

//Incluir funções básicas
include(__DIR__."/funcoes.php");

//Incluir função para url amigável
include(__DIR__."/toascii.php");

?>