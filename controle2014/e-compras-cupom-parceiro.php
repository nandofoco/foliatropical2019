<?

header('Content-Type: text/html; charset=utf-8');

//Verificamos o dominio
include("include/includes.php");

//conexao Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$cupom = format($_POST['cupom']);
$financeiro = (bool) $_POST['financeiro'];
// $v2 = (isset($_POST['v2'])) ? 'v2/' : '' ;
// $v2 = 'v2/';
$v2 = (isset($_POST['paypal'])) ? 'paypal/' : 'v2/' ;

//-----------------------------------------------------------------------------//

$retorno = $financeiro ? 'financeiro/detalhes/' : 'compras/pagamento/'.$v2;

if(!empty($cod) && !empty($cupom)) {

	//Definir se o cupom é de Parceiro ou de Usuário
	switch (true) {
		case strpos($cupom, 'FOLIA') === 0:

			$sql_parceiro = sqlsrv_query($conexao_sankhya, "SELECT * FROM TGFPAR WHERE CUPOM='$cupom' AND VENDEDOR='S'", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_parceiro) > 0) {

				$parceiro = sqlsrv_fetch_array($sql_parceiro);
				$parceiro_cod = $parceiro['CODPARC'];
				$parceiro_nome = trim(utf8_encode($parceiro['NOMEPARC']));
				$parceiro_cupom = trim(utf8_encode($parceiro['CUPOM']));
				$parceiro_comissao = trim($parceiro['AD_COMISSAO']);
				$parceiro_desconto = trim($parceiro['DESCONTO']);

				$sql_update = sqlsrv_query($conexao, "UPDATE loja SET LO_PARCEIRO='$parceiro_cod', LO_COMISSAO='$parceiro_comissao' WHERE LO_COD='$cod' AND LO_COMISSAO_RETIDA=0 AND LO_COMISSAO_PAGA=0", $conexao_params, $conexao_options);
				
				//Se houver desconto a ser aplicado, criamos um cupom e associamos a compra a ele
				if($parceiro_desconto > 0) {

					$caracteres_aceitos = 'abcdefghijklmnopqrstuvwxyz0123456789';
					$max = strlen($caracteres_aceitos)-1;
					$codigo_aleatorio = null;
					
					for($i=0; $i < 3; $i++) { $codigo_aleatorio .= $caracteres_aceitos{mt_rand(0, $max)}; }	
					
					$cupom_codigo = strtoupper($cupom.$codigo_aleatorio);
					$cupom_valor = $parceiro_desconto;
					$cupom_nome = trim($parceiro['NOMEPARC']).' - Parceria';
					$cupom_validade = date('Y-m-d', strtotime('+1day'));
					$cupom_tipo = 1;

					$sql_insert = sqlsrv_query($conexao, "INSERT INTO cupom (CP_NOME, CP_TIPO, CP_DATA_VALIDADE, CP_DESCONTO, CP_CUPOM) VALUES ('$cupom_nome','$cupom_tipo','$cupom_validade','$cupom_valor','$cupom_codigo')", $conexao_params, $conexao_options);
					$cupom_cod = getLastId();

					if($financeiro) {

						// 1 Porcentagem
						// 2 Valor

						$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 LO_VALOR_INGRESSOS, LO_VALOR_ADICIONAIS, LO_CLIENTE FROM loja WHERE LO_COD='$cod'", $conexao_params, $conexao_options);
						if(sqlsrv_num_rows($sql_loja) > 0) {
							$loja = sqlsrv_fetch_array($sql_loja);
							$loja_valor_total = $loja['LO_VALOR_INGRESSOS'];
							$loja_valor_adicionais = $loja['LO_VALOR_ADICIONAIS'];
							$loja_cliente = $loja['LO_CLIENTE'];
						} else {
							$erro = true;
						}

						switch ($cupom_tipo) {
							case 1:
								$loja_valor_total = $loja_valor_total - (($cupom_valor * $loja_valor_total) / 100);
							break;
							
							case 2:
								if($loja_valor_total >= $cupom_valor) $loja_valor_total = $loja_valor_total - $cupom_valor;
								else $erro = true;
							break;
						}

						if(!$erro) {

							$loja_valor_total = $loja_valor_total + $loja_valor_adicionais;

							$sql_cupom_usado = sqlsrv_query($conexao, "UPDATE TOP (1) cupom SET CP_UTILIZADO=1, CP_COMPRA='$cod', CP_DATA_UTILIZACAO=GETDATE() WHERE CP_COD='$cupom_cod'", $conexao_params, $conexao_options);
							$sql_compra_up = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_VALOR_TOTAL='$loja_valor_total' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);

						}

					} else {
						$_SESSION['compra-cupom']['usuario'] = $loja_cliente;
						$_SESSION['compra-cupom']['cod'] = $cupom_cod;
					}

					$mensagem = 'Você ganhou um desconto em sua compra.';
				} else {
					$mensagem = 'A comissão ao parceiro foi adicionada.';
				}

				$update = true;
			}
			
		break;

		case strpos($cupom, 'USUARIO') === 0:

			$codigo = (int) str_replace('USUARIO','', $cupom);
			if($codigo > 0) {

				$sql_usuarios = sqlsrv_query($conexao, "SELECT * FROM usuarios WHERE D_E_L_E_T_='0' AND US_COD='$codigo'", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_usuarios) > 0) {

					//$sql_update = sqlsrv_query($conexao, "UPDATE loja SET LO_VENDEDOR='$codigo' WHERE LO_COD='$cod' AND (LO_VENDEDOR IS NULL OR LO_VENDEDOR=0)", $conexao_params, $conexao_options);
					$sql_update = sqlsrv_query($conexao, "UPDATE loja SET LO_VENDEDOR='$codigo' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);

					$update = true;
					$mensagem = 'Sua venda foi direcionada para o atendente.';
					
				}				
			}
			

		break;		
		
	}

	if($update) {

		?>
		<script type="text/javascript">
			alert('<? echo $mensagem; ?>');
			location.href='<? echo SITE.$retorno.$cod; ?>/';
		</script>
		<?
	}

	//fechar conexao com o banco
	include("conn/close.php");
	include("conn/close-sankhya.php");

	exit();

}

?>
<script type="text/javascript">
	alert('Ocorreu um erro, tente novamente!');
	history.go(-1);
</script>