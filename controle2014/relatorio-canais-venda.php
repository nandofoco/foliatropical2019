<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];

$relatorio_sql = "DECLARE @vendas TABLE (VE_COD INT, VE_TIPO INT, VE_ESTOQUE INT, VE_SETOR INT, VE_DIA INT, VE_FILA VARCHAR(255), VE_VAGAS INT, VE_TIPO_ESPECIFICO VARCHAR(255));

INSERT INTO @vendas (VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO)
SELECT VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO FROM [foliatropical2014].[dbo].[vendas] WHERE VE_BLOCK=0 AND D_E_L_E_T_=0;

DECLARE @loja TABLE (LO_COD INT, LO_ORIGEM VARCHAR(50), LO_CLIENTE INT, LO_PARCEIRO INT, LO_VENDEDOR INT, LO_DATA_COMPRA DATETIME, LO_VALOR_TOTAL FLOAT, LO_VALOR_PARCIAL FLOAT, LO_VALOR_ITEM FLOAT, LO_VALOR_ITEM_TABELA FLOAT, LO_VALOR_INGRESSOS FLOAT, LO_VALOR_ADICIONAIS FLOAT, LO_VALOR_TRANSFER FLOAT, LO_VALOR_DELIVERY FLOAT, LO_VALOR_DESCONTO FLOAT, LO_VALOR_OVER_INTERNO FLOAT, LO_VALOR_OVER_EXTERNO FLOAT, LO_COMISSAO INT, LO_COMISSAO_RETIDA TINYINT, LO_COMISSAO_PAGA TINYINT, LO_PAGO TINYINT, LO_DESCONTO_FOLIA TINYINT, LO_DESCONTO_FRISA TINYINT, LO_TID VARCHAR(255), LO_FORMA_PAGAMENTO INT, LI_INGRESSO INT, QTDE INT, ITENS INT);


INSERT INTO @loja (LO_COD, LO_ORIGEM, LO_CLIENTE, LO_PARCEIRO, LO_VENDEDOR, LO_DATA_COMPRA, LO_VALOR_TOTAL, LO_VALOR_PARCIAL, LO_VALOR_ITEM, LO_VALOR_ITEM_TABELA, LO_VALOR_INGRESSOS, LO_VALOR_ADICIONAIS, LO_VALOR_TRANSFER, LO_VALOR_DELIVERY, LO_VALOR_DESCONTO, LO_VALOR_OVER_INTERNO, LO_VALOR_OVER_EXTERNO, LO_COMISSAO, LO_COMISSAO_RETIDA, LO_COMISSAO_PAGA, LO_PAGO, LO_DESCONTO_FOLIA, LO_DESCONTO_FRISA, LO_TID, LO_FORMA_PAGAMENTO, LI_INGRESSO, QTDE, ITENS)
SELECT l.LO_COD, MAX(l.LO_ORIGEM), l.LO_CLIENTE, MAX(l.LO_PARCEIRO), MAX(l.LO_VENDEDOR),MAX(l.LO_DATA_COMPRA), MAX(l.LO_VALOR_TOTAL), MAX(l.LO_VALOR_PARCIAL), li.LI_VALOR, li.LI_VALOR_TABELA /*(li.LI_VALOR + li.LI_DESCONTO - li.LI_OVER_INTERNO - li.LI_OVER_EXTERNO)*/, MAX(l.LO_VALOR_INGRESSOS), li.LI_VALOR_ADICIONAIS, li.LI_VALOR_TRANSFER, MAX(l.LO_VALOR_DELIVERY), li.LI_DESCONTO, li.LI_OVER_INTERNO, li.LI_OVER_EXTERNO, MAX(l.LO_COMISSAO), MAX(l.LO_COMISSAO_RETIDA), MAX(l.LO_COMISSAO_PAGA), MAX(l.LO_PAGO), MAX(l.LO_DESCONTO_FOLIA), MAX(l.LO_DESCONTO_FRISA), MAX(l.LO_TID), MAX(l.LO_FORMA_PAGAMENTO), li.LI_INGRESSO, COUNT (li.LI_COD), MAX(l.LO_NUM_ITENS)
FROM [foliatropical2014].[dbo].[loja] l, [foliatropical2014].[dbo].[loja_itens] li WHERE l.LO_EVENTO=$evento AND l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 /*vendedor*/
GROUP BY li.LI_INGRESSO, l.LO_COD, l.LO_CLIENTE, li.LI_VALOR, li.LI_VALOR_TABELA, li.LI_VALOR_TRANSFER, li.LI_VALOR_ADICIONAIS, li.LI_DESCONTO, li.LI_OVER_INTERNO, li.LI_OVER_EXTERNO;


DECLARE @folia TABLE (LO_COD INT, DESCONTO FLOAT);
INSERT INTO @folia (LO_COD, DESCONTO)
SELECT COD, DESCONTO FROM (
	SELECT
	COD,
	CASE 
		WHEN (DATA <= '2016-01-20' AND PRIMEIRA > 0 AND SEGUNDA > 0) THEN 20 
		WHEN (DATA <= '2016-01-20' AND PRIMEIRA > 0 AND TERCEIRA > 0) THEN 21 
		WHEN (DATA <= '2016-01-20' AND SEGUNDA > 0 AND TERCEIRA > 0) THEN 21 
		WHEN (DATA <= '2016-01-20' AND PRIMEIRA > 0 AND SEGUNDA > 0 AND TERCEIRA > 0) THEN 26.5 

		WHEN (DATA > '2016-01-20' AND PRIMEIRA > 0 AND SEGUNDA > 0) THEN 10
		WHEN (DATA > '2016-01-20' AND PRIMEIRA > 0 AND TERCEIRA > 0) THEN 10
		WHEN (DATA > '2016-01-20' AND SEGUNDA > 0 AND TERCEIRA > 0) THEN 10
		WHEN (DATA > '2016-01-20' AND PRIMEIRA > 0 AND SEGUNDA > 0 AND TERCEIRA > 0) THEN 10

		ELSE 0 
	END AS DESCONTO
	FROM (
	
		SELECT
		l.LO_COD AS COD,
		MAX(l.LO_DATA_COMPRA) AS DATA,
		SUM(CASE WHEN d.ED_DATA='2015-02-15' OR d.ED_DATA='2016-02-07' THEN 1 ELSE 0 END) AS PRIMEIRA,
		SUM(CASE WHEN d.ED_DATA='2015-02-16' OR d.ED_DATA='2016-02-08' THEN 1 ELSE 0 END) AS SEGUNDA,
		SUM(CASE WHEN d.ED_DATA='2015-02-21' OR d.ED_DATA='2016-02-13' THEN 1 ELSE 0 END) AS TERCEIRA

		FROM [foliatropical2014].[dbo].[loja] l, [foliatropical2014].[dbo].[loja_itens] li 
		LEFT JOIN @vendas v ON v.VE_COD = li.LI_INGRESSO
		LEFT JOIN [foliatropical2014].[dbo].[tipos] t ON t.TI_COD=v.VE_TIPO
		LEFT JOIN [foliatropical2014].[dbo].[eventos_dias] d ON d.ED_COD=v.VE_DIA
		WHERE l.LO_EVENTO=$evento AND l.LO_PARCEIRO=54 AND l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 /*AND d.ED_DATA IN('2015-02-15', '2015-02-16')*/ AND t.TI_TAG='lounge'
		GROUP BY l.LO_COD
	) S
) S WHERE DESCONTO > 0

DECLARE @clientes TABLE (CODPARC INT, NOMEPARC VARCHAR(255), EMAIL VARCHAR(255), CGC_CPF VARCHAR(255));

INSERT INTO @clientes (CODPARC, NOMEPARC, EMAIL, CGC_CPF)
SELECT CODPARC, NOMEPARC, EMAIL, CGC_CPF FROM [parceiros].[dbo].[TGFPAR] WHERE CLIENTE='S';

DECLARE @parceiros TABLE (CODPARC INT, NOMEPARC VARCHAR(255), TIPO VARCHAR(20));

INSERT INTO @parceiros (CODPARC, NOMEPARC, TIPO)
SELECT CODPARC, NOMEPARC, TIPO FROM [parceiros].[dbo].[TGFPAR] WHERE VENDEDOR='S';


DECLARE @relatorio TABLE (PARCEIRO VARCHAR(255), TIPO VARCHAR(50), FOLIA FLOAT, SUPER FLOAT, TOTAL FLOAT);

INSERT INTO @relatorio (PARCEIRO, TIPO, FOLIA, SUPER, TOTAL)
SELECT

PARCEIRO,
MAX(PARCEIRO_TIPO) AS PARCEIRO_TIPO,
SUM(CASE WHEN TIPO = 'lounge' THEN LUCRO ELSE 0 END) AS FOLIA,
SUM(CASE WHEN TIPO = 'super' THEN LUCRO ELSE 0 END) AS SUPER,
SUM(LUCRO) AS TOTAL

FROM (

	SELECT

	VENDEDOR_INTERNO,
	CASE 
		WHEN PARCEIRO = 'agencia' THEN 'Agencia'
		WHEN PARCEIRO = 'hotel' THEN 'Hotel'
		WHEN PARCEIRO = 'freelancer' THEN 'Freelancer'
		WHEN PARCEIRO = 'ticketeria' THEN 'Ticketeria'
		WHEN PARCEIRO = 'operadora' THEN 'Operadora'
		WHEN PARCEIRO = 'parceiros' THEN 'Parceiros'
		WHEN PARCEIRO = 'pacifica' THEN 'Grupo Pacifica'
		WHEN PARCEIRO = 'freela' THEN 'Grupo Freela'
		WHEN PARCEIRO = 'site' THEN 'Site'
	END AS PARCEIRO,

	CASE 
		WHEN PARCEIRO = 'agencia' THEN 'Externas'
		WHEN PARCEIRO = 'hotel' THEN 'Externas'
		WHEN PARCEIRO = 'freelancer' THEN 'Externas'
		WHEN PARCEIRO = 'ticketeria' THEN 'Externas'
		WHEN PARCEIRO = 'operadora' THEN 'Externas'
		WHEN PARCEIRO = 'parceiros' THEN 'Externas'

		WHEN PARCEIRO = 'pacifica' THEN 'Internas'
		WHEN PARCEIRO = 'freela' THEN 'Internas'
		WHEN PARCEIRO = 'site' THEN 'Internas'
	END AS PARCEIRO_TIPO,

	PARCEIRO_PACIFICA,
	PARCEIRO_NOME,
	TIPO,
	ORIGEM,
	ISNULL((VALOR_TOTAL_CALCULO_COMISSAO - VALOR_COMISSAO), 0) AS LUCRO

	FROM (

		SELECT
		
		VENDEDOR_INTERNO,
		PARCEIRO,
		PARCEIRO_PACIFICA,
		PARCEIRO_NOME,
		TIPO,
		ORIGEM,

		(PRECO_AUTORIZADO * QTDE) AS VALOR_TOTAL_CALCULO_COMISSAO,
		(PRECO_PRATICADO * QTDE) AS PRECO_TOTAL_PRATICADO,
		((PRECO_PRATICADO * QTDE) + ADICIONAIS) AS VALOR_TOTAL_VENDA,
		(OVER_EXTERNO * QTDE) AS OVER_EXTERNO_TOTAL,
		((PRECO_AUTORIZADO * QTDE) * COMISSAO / 100) AS VALOR_COMISSAO

		FROM (
			SELECT
			*,		
			(VALOR_TABELA - VALOR_DESCONTO + OVER_INTERNO) AS PRECO_AUTORIZADO,
			(VALOR_TABELA - VALOR_DESCONTO + OVER_INTERNO + OVER_EXTERNO) AS PRECO_PRATICADO,

			CASE 
				WHEN PARCEIRO_PACIFICA = 0 AND PARCEIRO_TIPO = 'agencia' THEN 'agencia'
				WHEN PARCEIRO_PACIFICA = 0 AND PARCEIRO_TIPO = 'hotel' THEN 'hotel'
				WHEN PARCEIRO_PACIFICA = 0 AND PARCEIRO_TIPO = 'freelancer' THEN 'freelancer'
				WHEN PARCEIRO_PACIFICA = 0 AND PARCEIRO_TIPO = 'ticketeria' THEN 'ticketeria'
				WHEN PARCEIRO_PACIFICA = 0 AND PARCEIRO_TIPO = 'operadora' THEN 'operadora'
				WHEN PARCEIRO_PACIFICA = 0 AND PARCEIRO_TIPO = 'parceiros' THEN 'parceiros'
				WHEN PARCEIRO_PACIFICA = 0 AND PARCEIRO_TIPO IS NULL AND PARCEIRO_NOME IS NOT NULL THEN 'parceiros'
				WHEN PARCEIRO_PACIFICA = 1 AND PARCEIRO_TIPO IS NULL AND VENDEDOR_INTERNO = 0 AND ORIGEM <> 'site' THEN 'pacifica'
				WHEN PARCEIRO_PACIFICA = 1 AND PARCEIRO_TIPO IS NULL AND VENDEDOR_INTERNO = 1 THEN 'freela'
				WHEN PARCEIRO_PACIFICA = 1 AND PARCEIRO_TIPO IS NULL AND VENDEDOR_INTERNO = 0 AND ORIGEM = 'site' THEN 'site'
			END AS PARCEIRO


			FROM (
				
				SELECT 
				l.LO_COD AS VOUCHER,
				CASE WHEN u.US_GRUPO = 'VIN' THEN 1 ELSE 0 END AS VENDEDOR_INTERNO,
				CASE WHEN l.LO_PARCEIRO = 54 THEN 1 ELSE 0 END AS PARCEIRO_PACIFICA,
				c.NOMEPARC AS CLIENTE,
				c.EMAIL AS CLIENTE_EMAIL,
				p.NOMEPARC AS PARCEIRO_NOME,
				p.TIPO AS PARCEIRO_TIPO,
				l.LO_ORIGEM AS ORIGEM,
				l.LO_VALOR_ITEM_TABELA AS VALOR_TABELA,
				l.LO_VALOR_OVER_INTERNO AS OVER_INTERNO,
				l.LO_VALOR_OVER_EXTERNO AS OVER_EXTERNO,
				l.QTDE AS QTDE,
				l.ITENS AS ITENS,
				l.LO_VALOR_ADICIONAIS AS ADICIONAIS,
				l.LO_VALOR_TRANSFER AS TRANSFER_UNITARIO,
				l.LO_VALOR_DELIVERY AS DELIVERY_TOTAL,
				l.LO_COMISSAO AS COMISSAO,
				l.LO_VALOR_ITEM AS VALOR_ITEM,
				t.TI_TAG AS TIPO,

				CASE 
					WHEN ((l.LO_DATA_COMPRA < '2015-10-15') OR (l.LO_DESCONTO_FOLIA = 1)) AND (fo.DESCONTO > 0) THEN l.LO_VALOR_DESCONTO + (((CASE WHEN (l.LO_COD<=2639) THEN 10 ELSE fo.DESCONTO END / 100.00) * ((l.LO_VALOR_ITEM_TABELA - l.LO_VALOR_DESCONTO + l.LO_VALOR_OVER_INTERNO + l.LO_VALOR_OVER_EXTERNO) * l.QTDE)) / l.QTDE)
					WHEN cp.CP_COD IS NOT NULL AND cp.CP_TIPO=1 THEN l.LO_VALOR_DESCONTO + (((cp.CP_DESCONTO / 100.00) * ((l.LO_VALOR_ITEM_TABELA - l.LO_VALOR_DESCONTO + l.LO_VALOR_OVER_INTERNO + l.LO_VALOR_OVER_EXTERNO) * l.QTDE)) / l.QTDE) 
					WHEN cp.CP_COD IS NOT NULL AND cp.CP_TIPO=2 THEN l.LO_VALOR_DESCONTO + (cp.CP_DESCONTO / l.QTDE)
					ELSE l.LO_VALOR_DESCONTO 
				END AS VALOR_DESCONTO,

				CASE 
					WHEN ((l.LO_DATA_COMPRA < '2015-10-15') OR (l.LO_DESCONTO_FOLIA = 1)) AND (fo.DESCONTO > 0) THEN CONCAT('Combo Folia ', FLOOR(CASE WHEN (l.LO_COD<=2639) THEN 10 ELSE fo.DESCONTO END), '% de desconto')
					WHEN cp.CP_COD IS NOT NULL AND cp.CP_TIPO=1 THEN CONCAT('Cupom ', FLOOR(cp.CP_DESCONTO), '% de desconto')
					WHEN cp.CP_COD IS NOT NULL AND cp.CP_TIPO=2 THEN CONCAT('Cumpom R$ ', cp.CP_DESCONTO, ' de desconto')
					ELSE '' 
				END AS TIPO_DESCONTO

				FROM @loja l
				LEFT JOIN @vendas v ON v.VE_COD = l.LI_INGRESSO
				LEFT JOIN @clientes c ON c.CODPARC = l.LO_CLIENTE
				LEFT JOIN @parceiros p ON p.CODPARC = l.LO_PARCEIRO
				LEFT JOIN @folia fo ON fo.LO_COD = l.LO_COD
				LEFT JOIN [foliatropical2014].[dbo].[usuarios] u ON l.LO_VENDEDOR=u.US_COD
				LEFT JOIN [foliatropical2014].[dbo].[tipos] t ON t.TI_COD=v.VE_TIPO
				LEFT JOIN [foliatropical2014].[dbo].[eventos_dias] d ON d.ED_COD=v.VE_DIA
				LEFT JOIN [foliatropical2014].[dbo].[eventos_setores] s ON s.ES_COD=v.VE_SETOR
				LEFT JOIN [foliatropical2014].[dbo].[formas_pagamento] f ON f.FP_COD=l.LO_FORMA_PAGAMENTO
				LEFT JOIN [foliatropical2014].[dbo].[cupom] cp ON cp.CP_COMPRA=l.LO_COD AND cp.CP_UTILIZADO=1 AND cp.CP_BLOCK=0 AND cp.D_E_L_E_T_=0

				WHERE d.D_E_L_E_T_=0 AND t.D_E_L_E_T_=0 AND s.D_E_L_E_T_=0 AND t.TI_TAG IN ('lounge', 'super')
			) S
		) S 
	) S
) S GROUP BY PARCEIRO;


DECLARE @total FLOAT;

SET @total = ISNULL((SELECT SUM(TOTAL) FROM @relatorio), 0);

SELECT

PARCEIRO,
FOLIA_TROPICAL AS 'FOLIA TROPICAL',
SUPER_FOLIA AS 'SUPER FOLIA',
GRAND_TOTAL AS 'GRAND TOTAL',
PERCENTUAL

FROM (

	SELECT * FROM (
		SELECT
		r.PARCEIRO,
		r.TIPO AS 'TIPO',
		r.FOLIA AS 'FOLIA_TROPICAL',
		r.SUPER AS 'SUPER_FOLIA',
		r.TOTAL AS 'GRAND_TOTAL',
		((r.TOTAL / @total) * 100) AS 'PERCENTUAL'

		FROM @relatorio r
	) TODAS
	UNION

	SELECT * FROM (
		SELECT
		r.TIPO AS 'PARCEIRO',
		'X' AS 'TIPO',
		SUM(r.FOLIA) AS 'FOLIA_TROPICAL',
		SUM(r.SUPER) AS 'SUPER_FOLIA',
		SUM(r.TOTAL) AS 'GRAND_TOTAL',
		SUM(((r.TOTAL / @total) * 100)) AS 'PERCENTUAL'

		FROM @relatorio r
		GROUP BY r.TIPO
	) EXINT

	UNION

	SELECT * FROM (
		SELECT
		'Total' AS 'PARCEIRO',
		'Z' AS 'TIPO',
		SUM(r.FOLIA) AS 'FOLIA_TROPICAL',
		SUM(r.SUPER) AS 'SUPER_FOLIA',
		SUM(r.TOTAL) AS 'GRAND_TOTAL',
		SUM(((r.TOTAL / @total) * 100)) AS 'PERCENTUAL'

		FROM @relatorio r
	) TOTAL

) S ORDER BY TIPO ASC";

$sql_relatorio = sqlsrv_query($conexao, $relatorio_sql, $conexao_params, $conexao_options);

?>
<!-- <? echo "<pre>".$relatorio_sql."</pre>"; ?> -->

<section id="conteudo" class="larger">
	<header class="titulo">
		<h1>Relatório <span>Canais de Venda</span></h1>
		<a href="https://ingressos.foliatropical.com.br/controle2014/relatorios/exportar/canal-venda/" target="_blank" class="exportar-relatorio">Exportar Relatório</a>
	</header>
	<section class="secao">
		<table class="lista">
            <thead>
                <tr>
                    <th class="header"><strong>Parceiro</strong></th>
                    <th class="header"><strong>Folia Tropical</strong></th>
                    <th class="header"><strong>Super Folia</strong></th>
                    <th class="header"><strong>Grand Total</strong></th>
                    <th class="header"><strong>Percentual</strong></th>
                </tr>
            </thead>
            <tbody>
                <?
                while(sqlsrv_next_result($sql_relatorio)) {
					$i=1;
					$data = array();
                    while($relatorio = sqlsrv_fetch_array($sql_relatorio)) {

                        $parceiro = utf8_encode($relatorio['PARCEIRO']);
                        $foliatropical = $relatorio['FOLIA TROPICAL'];
                        $superfolia = $relatorio['SUPER FOLIA'];
                        $grandtotal = $relatorio['GRAND TOTAL'];
						$percentual = $relatorio['PERCENTUAL'];

						if($i < 8) {
							$data[$i]['label'] = $parceiro;
							$data[$i]['valor'] = number_format($percentual,1,'.',',');
						}

                        if($i==8) {
                        ?>
                        <tr class="white">
                            <td colspan="5"></td>
                        </tr>
                        <?
                        }
                    ?>
                        <tr>
                            <td class="first"><? echo $parceiro; ?></td>
                            <td>R$ <? echo number_format($foliatropical,2,',','.'); ?></td>
                            <td>R$ <? echo number_format($superfolia,2,',','.'); ?></td>
                            <td>R$ <? echo number_format($grandtotal,2,',','.'); ?></td>
                            <td><? echo number_format($percentual,1,'.',','); ?>%</td>
                        </tr>
                    <?
                        $i++;
                    } 
				} 
				
				$data = json_encode($data);
				?>
            </tbody>
        </table>
    </section>
    <section class="secao bottom">
        <canvas id="myChart" width="600" height="600" style="margin: 0 auto;"></canvas>
        <script>
			var randomScalingFactor = function() {
				return Math.round(Math.random() * 100);
			};

			var data = <? echo $data; ?>;
			var label = [];
			var valor = [];

			for(var i in data) {
				label.push(data[i].label);
				valor.push(data[i].valor);
			}

            var ctx = document.getElementById("myChart").getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'doughnut',
				data: {
					datasets: [{
						data: valor,
						backgroundColor: [
							'rgba(255, 96, 89, 1)',
							'rgba(232, 38, 131, 1)',
							'rgba(139, 193, 99, 1)',
							'rgba(43, 211, 199, 1)',
							'rgba(255, 180, 55, 1)'
						],
						label: 'Canais de Venda'
					}],
					labels: label
				},
				options: {
					tooltips: {
						enabled: true,
						mode: 'single',
						callbacks: {
							label: function(tooltipItem, data) {
								return data['datasets'][0]['data'][tooltipItem['index']]+'%';
							}
						}
					},
					responsive: false,
					legend: {
						position: 'right',
					},
					animation: {
						animateScale: true,
						animateRotate: true
					}
				}
            });
        </script>
    </section>
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>