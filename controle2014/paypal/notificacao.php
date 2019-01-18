<?php


define('PGINCLUDE', 'true');

//Verificamos o dominio
include("../include/checkwww.php");

//Banco de dados
include("../conn/conn.php");

//Incluir funções básicas
include("../include/funcoes.php");

//Incluir função para url amigável
include("../include/toascii.php");

//Incluindo o arquivo que contém a função isIPNValid
require 'isIPNValid.php';
  
//Incluindo o arquivo que contém a função logIPN
require 'logIPN.php';
  
//Incluindo o arquivo que contém a função storeCustomer
require 'storeCustomer.php';
  
//Incluindo o arquivo que contém a função storeTransaction
require 'storeTransaction.php';
  
//Email da conta do vendedor, que será utilizada para verificar o
//destinatário da notificação.

$receiver_email = 'douglas@grupopacifica.com.br';
//$receiver_email = 'seller@paypalsandbox.com';
  
//Informações para conexão com o banco de dados, que utilizaremos
//para gravar o log.
// $mysql = array(
//     'host' => 'localhost',
//     'user' => 'usuário',
//     'pswd' => 'senha',
//     'dbname' => 'code_sample'
// );



//As notificações sempre serão via HTTP POST, então verificamos o método
//utilizado na requisição, antes de fazer qualquer coisa.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Antes de trabalhar com a notificação, precisamos verificar se ela
    //é válida e, se não for, descartar.

        //logando o $_POST em um arquivo


        // $texto = '';
        // foreach ($_POST as $key => $value) {
        //    $texto .= $key.': '.$value.PHP_EOL;
        // }
        // escreveLog('logs/log.txt', $texto);

        


    if (!isIPNValid($_POST)) {
        return;
    }
  
    //Se chegamos até aqui, significa que estamos lidando com uma
    //notificação IPN válida. Agora precisamos verificar se somos o
    //destinatário dessa notificação, verificando o campo receiver_email.


    if ($_POST['receiver_email'] == $receiver_email) {
        //Está tudo correto, somos o destinatário da notificação, vamos
        //gravar um log dessa notificação.
        /*$pdoattrs = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
  
        $pdo = new PDO(sprintf('mysql:host=%s;dbname=%s',
                               $mysql['host'], $mysql['dbname']),
                       $mysql['user'],
                       $mysql['pswd'],
                       $pdoattrs);*/
  
        if (logIPN($_POST)) {
            //Log gravado, podemos seguir com as regras de negócio para
            //essa notificação.
  
            //gravamos dados do cliente
            storeCustomer($_POST);
  
            //gravamos dados da transação
            storeTransaction($conexao, $conexao_params, $conexao_options, $_POST);
        }
   }



}