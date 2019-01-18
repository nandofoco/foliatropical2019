<?

	define('PGINCLUDE', 'true');

	//Verificamos o dominio
	include("checkwww.php");

	//Banco de dados
	include("../conn/conn.php");

	// Checar usuario logado
	include("checklogado.php");

	//Incluir funções básicas
	include("funcoes.php");

	//Incluir função para url amigável
	include("toascii.php");


	header('Content-Type: text/html; charset=utf-8'); 
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
	header("Expires: Fri, 1 Jan 2010 08:00:00 GMT"); // Date in the past 

	$sucesso = false;

	//-------------------------------------//

	$acao = format($_GET['a']);

	if ($acao == 'excluir') 
	{
		$cod = $_GET['cod'];
		$item = $_GET['i'];
		unset($_SESSION['pagamento-multiplo'][$cod][$item]);
		
		$sucesso = true;
	} else {
		$cod = (int) $_POST['cod'];
	}

	//-------------------------------------//

	$valor = number_format(str_replace(",", ".", str_replace(".", "",format($_POST['valor']))), 2, ".", "");
	$forma = (int) $_POST['forma'];

	$sql_formas_pagamento = sqlsrv_query($conexao, "SELECT FP_COD, FP_NOME FROM formas_pagamento WHERE FP_COD NOT IN (5,10) AND D_E_L_E_T_=0 ORDER BY FP_NOME ASC", $conexao_params, $conexao_options);
	
	if(sqlsrv_num_rows($sql_formas_pagamento))
	{
		while ($ar_formas_pagamento = sqlsrv_fetch_array($sql_formas_pagamento)) 
		{		
			$formas_pagamento_cod = $ar_formas_pagamento['FP_COD'];
			$formas_pagamento_nome = $ar_formas_pagamento['FP_NOME'];				
			$formas_pagamento[$formas_pagamento_cod] = $formas_pagamento_nome;
		}
	}

	if(is_numeric($cod) && !empty($valor) && !empty($forma)) 
	{

		//Conta o item
		$i = count($_SESSION['pagamento-multiplo'][$cod]);

		//Se não existe, adicionamos o item à sessão
		$_SESSION['pagamento-multiplo'][$cod][$i]['valor'] = $valor;
		$_SESSION['pagamento-multiplo'][$cod][$i]['forma'] = $forma;
		$_SESSION['pagamento-multiplo'][$cod][$i]['sessao'] = true;

		//ksort($_SESSION['pagamento-multiplo'][$cod]);

		$sucesso = true;
	}

	//criar array de itens
	$ingressos_html = '';

	foreach ($_SESSION['pagamento-multiplo'][$cod] as $key => $compra) 
	{
		$item = $compra['cod'];
		$pago = $compra['pago'] ? 'pago' : '';
		$acao = $compra['pago'] ? 'bloquear' : 'confirmar';
		$disabled = $compra['bd'] ? '' : 'disabled';
		$sessao = $compra['sessao'] ? ' class="sessao"' : '';

		//$ingressos_html .= '<li id="linha'.$compra['cod'].'">';
		$ingressos_html .= '<li id="linha'.$key.'">';
		
		$ingressos_html .= '<strong>'.utf8_encode($formas_pagamento[$compra['forma']]).'</strong> R$ '.number_format($compra['valor'], 2, ',','.');
		
		if(!$pago) 
		{				
			$ingressos_html .= '<a href="'.SITE.'include/multiplo-adicionar.php?cod='.$cod.'&i='.$key.'&a=excluir" class="remover rm_multiplo">&times;</a>';

			if($compra['bd']) $ingressos_html .= '<a href="'.$link.'" class="confirmar '.$class.' '.$disabled.'" title="Confirmar pagamento">'.$label.'</a>';
		} 
		else 
		{
			if($compra['bd']) $ingressos_html .= '<a href="'.$link.'" class="pago" title="Cancelar pagamento"></a>';
		}

		$ingressos_html .= '<input type="hidden" name="multiplo['.$compra['forma'].']" value="'.$compra['valor'].'" '.$sessao.' />';
	}

	//Exibir resposta
	echo json_encode(array('sucesso' => $sucesso, 'itens'=> $ingressos_html));

	//Fechar conexoes
	include("../conn/close.php");

	exit();
