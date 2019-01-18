<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

//arquivos de layout
// include("include/head.php");
// include("include/header.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$cod = (int) $_GET['c'];

$vendedor = ($_SESSION['us-grupo'] == 'VIN') ? true : false;
$usuario = (int) $_SESSION['us-cod'];

// Se o usuário for vendedor interno, ver apenas as suas vendas
if($vendedor) $search_vendedor = " AND l.LO_VENDEDOR='$usuario' ";

//-----------------------------------------------------------------//

//Buscar evento
$sql_evento = sqlsrv_query($conexao, "SELECT TOP 1 EV_NOME FROM eventos WHERE EV_COD='$evento'", $conexao_params, $conexao_options);
if(sqlsrv_num_rows($sql_evento) > 0) {
	$eventoar = sqlsrv_fetch_array($sql_evento);
	$evento_nome = utf8_encode($eventoar['EV_NOME']);
}

//Buscar informações do parceiro
$sql_parceiro = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL FROM TGFPAR WHERE CODPARC='$cod' AND VENDEDOR='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
if(sqlsrv_num_rows($sql_parceiro) > 0) $parceiro_ar = sqlsrv_fetch_array($sql_parceiro);

$parceiro_nome = utf8_encode(trim($parceiro_ar['NOMEPARC']));
$parceiro_telefone = utf8_encode(trim($parceiro_ar['TELEFONE']));
$parceiro_email = utf8_encode(trim($parceiro_ar['EMAIL']));

?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Folia Tropical</title>

<link rel="shortcut icon" href="<? echo SITE; ?>favicon.ico" />
<link rel="stylesheet" type="text/css" href="<? echo SITE; ?>css/print.css"/>

<link rel="stylesheet" type="text/css" href="<? echo SITE; ?>fancybox/jquery.fancybox.css?v=2.1.5" media="screen" />
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:400,600,700"/>

<!--[if IE]>
<script src="<? echo SITE; ?>js/html5.js" type="text/javascript"></script>
<![endif]-->

</head>
<body class="entrega caderno recibo">
	<section id="conteudo">

	<?

	$comissao_total = 0;
	$vouchers = array();

	$sql_loja = sqlsrv_query($conexao, "SELECT l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA, ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA, CONVERT(VARCHAR, l.LO_DEADLINE, 103) AS DATA_DEADLINE, CONVERT(CHAR, l.LO_CLI_DATA_ENTREGA, 103) AS DATA_PARA_ENTREGA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_PARCEIRO='$cod' $search_vendedor", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_loja) > 0) {

		while($loja = sqlsrv_fetch_array($sql_loja)) { 

			$loja_cod = $loja['LO_COD'];
			$loja_cliente = $loja['LO_CLIENTE'];
			$loja_vendedor = $loja['LO_VENDEDOR'];
			$loja_pago = (bool) $loja['LO_PAGO'];
			$loja_delivery = (bool) $loja['LO_DELIVERY'];
			
			$loja_valor_desconto = $loja['LO_VALOR_DESCONTO'];
	      	$loja_valor_over_interno = $loja['LO_VALOR_OVER_INTERNO'];
	      	$loja_valor_over_externo = $loja['LO_VALOR_OVER_EXTERNO'];
	      	$loja_valor_ingressos = $loja['LO_VALOR_INGRESSOS'];
	      	$loja_comissao = $loja['LO_COMISSAO'];

	      	$loja_comissao_valor = (($loja_valor_ingressos - $loja_valor_desconto + $loja_valor_over_interno) * $loja_comissao / 100);
	      	if($loja_comissao_valor > 0) {
	      		$comissao_total += $loja_comissao_valor;

	      		array_push($vouchers, array(
	      			'voucher' => $loja_cod,
	      			'comissao' => $loja_comissao_valor
	      		));
	      	}

	      	$loja_valor_desconto = number_format($loja_valor_desconto, 2, ',','.');
	      	$loja_valor_over_interno = number_format($loja_valor_over_interno, 2, ',','.');
	      	$loja_valor_over_externo = number_format($loja_valor_over_externo, 2, ',','.');

			//Vendedor
			$sql_vendedor = sqlsrv_query($conexao, "SELECT TOP 1 US_NOME FROM usuarios WHERE US_COD='$loja_vendedor'", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_vendedor) > 0) {
				$loja_vendedor_ar = sqlsrv_fetch_array($sql_vendedor);			
				$loja_vendedor_nome = utf8_encode($loja_vendedor_ar['US_NOME']);
			}

			/*//Buscar vendedor externo
			if($loja_concierge > 0) {
				
				$sql_vendedor_externo = sqlsrv_query($conexao, "SELECT TOP 1 VE_NOME FROM vendedor_externo WHERE VE_COD='$loja_concierge'", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_vendedor_externo) > 0) {
					$vendedorexar = sqlsrv_fetch_array($sql_vendedor_externo);
					$vendedor_externo_nome = utf8_encode($vendedorexar['VE_NOME']);
				}

			}*/
		}


	$comissao_total_f = number_format($comissao_total, 2, ',', '.');

	?>
	<header id="topo">
		<img src="<? echo SITE; ?>img/logo-land-tour.png" class="logo" />
		<section class="recibo">
			<p>Rio de Janeiro, <? echo date('d'); ?> de <? echo $meses[date('m')]; ?> de <? echo date('Y'); ?>.</p>
		</section>
	</header>

	<article id="detalhes-recibo">
		<h1>Recibo</h1>
		<h2>R$ <? echo $comissao_total_f; ?></h2>

		<p>Recebi da <strong>Land Tour Passeios Turisticos</strong>, a importância de R$ <? echo $comissao_total_f; ?> (<? echo valorPorExtenso("R$ ".$comissao_total_f, true, false); ?>), referente a comissão sobre vendas <strong>Pacote de <? echo $evento_nome ?></strong>.</p>

		<ul>
			<? foreach ($vouchers as $r) { ?>
			<li>Voucher <? echo $r['voucher']; ?> – R$ <? echo number_format($r['comissao'], 2, ',', '.'); ?></li>
			<? } ?>
		</ul>

		<p class="assinatura"><? echo $parceiro_nome; ?></p>
	</article>
	<?
	}
	?>	
	</section>

	<input type="hidden" id="base-site" value="<? echo SITE; ?>" />
</body>
</html>
<?

//-----------------------------------------------------------------//

// include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>