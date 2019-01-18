<?php

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");


$id_endereco = $_POST['id_endereco'];
$cod = $_POST['cod'];

// $id_endereco = $_GET['id_endereco'];
// $cod = $_GET['cod'];

if ($id_endereco) 
{
	$sql_enderecos =sqlsrv_query($conexao_sankhya, "SELECT
                        *
                    FROM
                        clientes_enderecos
                    WHERE
                        CE_COD=$id_endereco
                        AND CE_BLOCK='0'
                        AND D_E_L_E_T_='0'
                    ORDER BY
                        CE_ULTIMA_ENTREGA DESC",
                $conexao_params, $conexao_options);

    $numRows = sqlsrv_num_rows($sql_enderecos);

    if($numRows > 0) 
    {

        while ($enderecoCliente = sqlsrv_fetch_array($sql_enderecos)) 
        {                        
            $codEndereco = $enderecoCliente['CE_COD'];
            $pais = $enderecoCliente['CE_PAIS'];
            $cep = $enderecoCliente['CE_CEP'];
            $endereco = $enderecoCliente['CE_ENDERECO'];
            $numero = $enderecoCliente['CE_NUMERO'];
            $complemento = $enderecoCliente['CE_COMPLEMENTO'];
            $bairro = $enderecoCliente['CE_BAIRRO'];
            $cidade = $enderecoCliente['CE_CIDADE'];
            $estado = $enderecoCliente['CE_ESTADO'];
            $tipo_endereco = $enderecoCliente['CE_TIPO_ENDERECO'];
            $ponto_referencia = $enderecoCliente['CE_PONTO_REFERENCIA'];
        }
    }
}
else 
{
	
	$pais = format($_POST['pais']);
	//$cep = format($_POST['cep']);
    if (!empty($_POST['cep']))
    { 
        $cep = format($_POST['cep']);
    } 
    else 
    {
        $cep = format($_POST['zipcode']);
    }
	// $zipcode = format($_POST['zipcode']);
	$endereco = format($_POST['endereco']);
	$numero = $_POST['numero'];
	$complemento = format($_POST['complemento']);
	$bairro = format($_POST['bairro']);
	$cidade = format($_POST['cidade']);
	$estado = format($_POST['estado']);
}


$sql_cadastro = sqlsrv_query($conexao, "UPDATE loja SET LO_CLI_CEP='$cep', LO_CLI_ENDERECO='$endereco', LO_CLI_NUMERO='$numero', LO_CLI_COMPLEMENTO='$complemento', LO_CLI_BAIRRO='$bairro', LO_CLI_CIDADE='$cidade', LO_CLI_ESTADO='$estado', LO_CLI_PAIS='$pais' WHERE LO_COD=$cod", $conexao_params, $conexao_options);


// echo "UPDATE loja SET LO_CLI_CEP='$cep', LO_CLI_ENDERECO='$endereco', LO_CLI_NUMERO='$numero', LO_CLI_COMPLEMENTO='$complemento', LO_CLI_BAIRRO='$bairro', LO_CLI_CIDADE='$cidade', LO_CLI_ESTADO='$estado', LO_CLI_PAIS='$pais' WHERE LO_COD=$cod";

if ($sql_cadastro) 
{
	echo true;
}
else
{
	echo false;
}


?>