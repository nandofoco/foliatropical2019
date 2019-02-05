--Relatorio Financeiro

DECLARE @vendas TABLE (VE_COD INT, VE_TIPO INT, VE_ESTOQUE INT, VE_SETOR INT, VE_DIA INT, VE_FILA VARCHAR(255), VE_VAGAS INT, VE_TIPO_ESPECIFICO VARCHAR(255));

INSERT INTO @vendas (VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO)
SELECT VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO FROM [foliatropical2014].[dbo].[vendas] WHERE VE_BLOCK=0 AND D_E_L_E_T_=0;

DECLARE @loja TABLE (LO_COD INT, LO_ANTIFRAUDE_STATUS VARCHAR(255), LO_ANTIFRAUDE_SCORE FLOAT, LO_CARTAO_NOME VARCHAR(255), LO_CARTAO_BANDEIRA VARCHAR(50), LO_CARTAO VARCHAR(50), LO_PARCELAS INT, LO_ORIGEM VARCHAR(50), LO_CLIENTE INT, LO_PARCEIRO INT, LO_VENDEDOR INT, LO_DATA_COMPRA DATETIME, LO_VALOR_TOTAL FLOAT, LO_VALOR_PARCIAL FLOAT, LO_VALOR_ITEM FLOAT, LO_VALOR_ITEM_TABELA FLOAT, LO_VALOR_INGRESSOS FLOAT, LO_VALOR_ADICIONAIS FLOAT, LO_VALOR_TRANSFER FLOAT, LO_VALOR_DELIVERY FLOAT, LO_VALOR_DESCONTO FLOAT, LO_VALOR_OVER_INTERNO FLOAT, LO_VALOR_OVER_EXTERNO FLOAT, LO_COMISSAO INT, LO_COMISSAO_RETIDA TINYINT, LO_COMISSAO_PAGA TINYINT, LO_PAGO TINYINT, LO_DESCONTO_FOLIA TINYINT, LO_DESCONTO_FRISA TINYINT, LO_TID VARCHAR(255), LO_FORMA_PAGAMENTO INT, LI_COD INT, LI_INGRESSO INT, QTDE INT, ITENS INT);


INSERT INTO @loja (LO_COD, LO_ANTIFRAUDE_STATUS, LO_ANTIFRAUDE_SCORE, LO_CARTAO_NOME, LO_CARTAO_BANDEIRA, LO_CARTAO, LO_PARCELAS, LO_ORIGEM, LO_CLIENTE, LO_PARCEIRO, LO_VENDEDOR, LO_DATA_COMPRA, LO_VALOR_TOTAL, LO_VALOR_PARCIAL, LO_VALOR_ITEM, LO_VALOR_ITEM_TABELA, LO_VALOR_INGRESSOS, LO_VALOR_ADICIONAIS, LO_VALOR_TRANSFER, LO_VALOR_DELIVERY, LO_VALOR_DESCONTO, LO_VALOR_OVER_INTERNO, LO_VALOR_OVER_EXTERNO, LO_COMISSAO, LO_COMISSAO_RETIDA, LO_COMISSAO_PAGA, LO_PAGO, LO_DESCONTO_FOLIA, LO_DESCONTO_FRISA, LO_TID, LO_FORMA_PAGAMENTO, LI_COD, LI_INGRESSO, QTDE, ITENS)
SELECT l.LO_COD, MAX(l.LO_ANTIFRAUDE_STATUS), MAX(l.LO_ANTIFRAUDE_SCORE), l.LO_CARTAO_NOME, l.LO_CARTAO_BANDEIRA, l.LO_CARTAO, l.LO_PARCELAS, MAX(l.LO_ORIGEM), l.LO_CLIENTE, MAX(l.LO_PARCEIRO), MAX(l.LO_VENDEDOR),MAX(l.LO_DATA_COMPRA), MAX(l.LO_VALOR_TOTAL), MAX(l.LO_VALOR_PARCIAL), li.LI_VALOR, li.LI_VALOR_TABELA /*(li.LI_VALOR + li.LI_DESCONTO - li.LI_OVER_INTERNO - li.LI_OVER_EXTERNO)*/, MAX(l.LO_VALOR_INGRESSOS), li.LI_VALOR_ADICIONAIS, li.LI_VALOR_TRANSFER, MAX(l.LO_VALOR_DELIVERY), li.LI_DESCONTO, li.LI_OVER_INTERNO, li.LI_OVER_EXTERNO, MAX(l.LO_COMISSAO), MAX(l.LO_COMISSAO_RETIDA), MAX(l.LO_COMISSAO_PAGA), MAX(l.LO_PAGO), MAX(l.LO_DESCONTO_FOLIA), MAX(l.LO_DESCONTO_FRISA), MAX(l.LO_TID), MAX(l.LO_FORMA_PAGAMENTO), li.LI_COD, li.LI_INGRESSO, COUNT (li.LI_COD), MAX(l.LO_NUM_ITENS)
FROM [foliatropical2014].[dbo].[loja] l, [foliatropical2014].[dbo].[loja_itens] li WHERE l.LO_EVENTO=%evento% AND l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 /*vendedor*/
GROUP BY li.LI_COD, li.LI_INGRESSO, l.LO_COD, l.LO_CARTAO_NOME, l.LO_CARTAO_BANDEIRA, l.LO_CARTAO, l.LO_PARCELAS, l.LO_CLIENTE, li.LI_VALOR, li.LI_VALOR_TABELA, li.LI_VALOR_TRANSFER, li.LI_VALOR_ADICIONAIS, li.LI_DESCONTO, li.LI_OVER_INTERNO, li.LI_OVER_EXTERNO;


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
		WHERE l.LO_EVENTO=%evento% AND l.LO_PARCEIRO=54 AND l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 /*AND d.ED_DATA IN('2015-02-15', '2015-02-16')*/ AND t.TI_TAG='lounge'
		GROUP BY l.LO_COD
	) S
) S WHERE DESCONTO > 0


DECLARE @clientes TABLE (CODPARC INT, NOMEPARC VARCHAR(255), EMAIL VARCHAR(255), CGC_CPF VARCHAR(255), SEXO VARCHAR(5), FAX VARCHAR(20), TELEFONE VARCHAR(20), DDD VARCHAR(20), DDD_CELULAR VARCHAR(20), DDI VARCHAR(20), DDI_CELULAR VARCHAR(20), ORIGEM VARCHAR(40));

INSERT INTO @clientes (CODPARC, NOMEPARC, EMAIL, CGC_CPF, SEXO, FAX, TELEFONE, DDD, DDD_CELULAR, DDI, DDI_CELULAR, ORIGEM)
SELECT CODPARC, NOMEPARC, EMAIL, CGC_CPF, SEXO, FAX, TELEFONE, DDD, DDD_CELULAR, DDI, DDI_CELULAR, ORIGEM FROM [parceiros].[dbo].[TGFPAR] WHERE CLIENTE='S';

DECLARE @parceiros TABLE (CODPARC INT, NOMEPARC VARCHAR(255), TIPO VARCHAR(20));

INSERT INTO @parceiros (CODPARC, NOMEPARC, TIPO)
SELECT CODPARC, NOMEPARC, TIPO FROM [parceiros].[dbo].[TGFPAR] WHERE VENDEDOR='S';

DECLARE @enderecos TABLE (CE_CLIENTE INT, CE_CIDADE VARCHAR(255), CE_ESTADO VARCHAR(255), CE_PAIS VARCHAR(255));

INSERT INTO @enderecos (CE_CLIENTE, CE_ESTADO, CE_CIDADE, CE_PAIS)
SELECT CE_CLIENTE, CE_ESTADO, CE_CIDADE, CE_PAIS FROM [parceiros].[dbo].[clientes_enderecos]
WHERE CE_COD IN
(
	SELECT MAX(CE_COD)
	FROM [parceiros].[dbo].[clientes_enderecos]
	WHERE CE_BLOCK=0 AND D_E_L_E_T_=0
	GROUP BY CE_CLIENTE
)

SELECT

VOUCHER,
VENDEDOR_INTERNO AS 'VENDEDOR INTERNO',
CLIENTE,
PARCEIRO AS 'CANAL DE VENDA',
CANAL AS 'TIPO DE CANAL DE VENDA',
VALOR_TABELA AS 'VALOR TABELA',
VALOR_DESCONTO AS 'VALOR DESCONTO',
TIPO_DESCONTO AS 'TIPO DESCONTO',
OVER_INTERNO AS 'OVER INTERNO',
PRECO_AUTORIZADO AS 'PRECO AUTORIZADO',
OVER_EXTERNO AS 'OVER EXTERNO',
PRECO_PRATICADO AS 'PRECO PRATICADO',
QTDE,
VALOR_TOTAL_CALCULO_COMISSAO AS 'VALOR TOTAL CALCULO COMISSAO',
PRECO_TOTAL_PRATICADO AS 'PRECO TOTAL PRATICADO',
ADICIONAIS,
TRANSFER,
DELIVERY,
VALOR_TOTAL_VENDA AS 'VALOR TOTAL VENDA',
OVER_EXTERNO_TOTAL AS 'OVER EXTERNO TOTAL',
COMISSAO,
VALOR_ITEM AS 'VALOR ITEM',
VALOR_COMISSAO AS 'VALOR COMISSAO',
--(VALOR_TOTAL_VENDA - OVER_EXTERNO_TOTAL - VALOR_COMISSAO) AS LUCRO,
--(VALOR_TOTAL_CALCULO_COMISSAO - OVER_EXTERNO_TOTAL - VALOR_COMISSAO) AS LUCRO,
TAXA_CARTAO AS 'TAXA CARTAO',
(VALOR_TOTAL_CALCULO_COMISSAO - VALOR_COMISSAO - (CASE WHEN TAXA_CARTAO IS NOT NULL THEN (VALOR_TOTAL_CALCULO_COMISSAO - VALOR_COMISSAO) * (TAXA_CARTAO / 100) ELSE 0 END)) AS LUCRO,
COMISSAO_RETIDA AS 'COMISSAO RETIDA',
COMISSAO_PAGA AS 'COMISSAO PAGA',
--COMISSAO_PENDENTE AS 'COMISSAO PENDENTE',
TIPO,
ESPECIFICO,
DATA,
SETOR,
FILA,
VAGAS,
PAGO,
NOME_LIBERACAO AS 'LIBERADO POR',
FORMA_PAGAMENTO AS 'FORMA PAGAMENTO',
TID,
DATA_COMPRA AS 'DATA COMPRA',
CLIENTE_EMAIL AS 'EMAIL DO CLIENTE',
PAIS_TELEFONE AS 'PAIS TELEFONE',
CELULAR AS 'CELULAR DO CLIENTE',
CIDADE AS 'CIDADE DO CLIENTE',
ESTADO AS 'ESTADO DO CLIENTE',
PAIS AS 'PAIS DO CLIENTE',
BANDEIRA,
NUMERO_CARTAO,
NOME_CARTAO, 
PARCELAS,
ANTIFRAUDE_STATUS AS 'STATUS CLEARSALE',
ANTIFRAUDE_SCORE AS 'RISCO CLEARSALE',
COMENTARIO AS 'COMENTARIO',
COMENTARIO_INTERNO AS 'COMENTARIO INTERNO'

FROM (

	SELECT
	*,
	(TRANSFER_UNITARIO * QTDE) AS TRANSFER,
	(DELIVERY_TOTAL / ITENS) AS DELIVERY,
	(PRECO_AUTORIZADO * QTDE) AS VALOR_TOTAL_CALCULO_COMISSAO,
	(PRECO_PRATICADO * QTDE) AS PRECO_TOTAL_PRATICADO,
	--(PRECO_PRATICADO + ADICIONAIS) AS VALOR_TOTAL_VENDA,
	((PRECO_PRATICADO * QTDE) + ADICIONAIS) AS VALOR_TOTAL_VENDA,
	(OVER_EXTERNO * QTDE) AS OVER_EXTERNO_TOTAL,
	--((VALOR_ITEM - OVER_EXTERNO + OVER_INTERNO) * COMISSAO / 100) AS VALOR_COMISSAO
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
			WHEN PARCEIRO_PACIFICA = 0 AND PARCEIRO_TIPO IS NULL AND PARCEIRO IS NOT NULL THEN 'parceiros'
			WHEN PARCEIRO_PACIFICA = 1 AND PARCEIRO_TIPO IS NULL AND VENDEDOR_INTERNO_BOOL = 0 AND ORIGEM <> 'site' THEN 'pacifica'
			WHEN PARCEIRO_PACIFICA = 1 AND PARCEIRO_TIPO IS NULL AND VENDEDOR_INTERNO_BOOL = 1 THEN 'freela'
			WHEN PARCEIRO_PACIFICA = 1 AND PARCEIRO_TIPO IS NULL AND VENDEDOR_INTERNO_BOOL = 0 AND ORIGEM = 'site' THEN 'site'
		END AS CANAL
		
		FROM (
			
			SELECT 
			l.LO_COD AS VOUCHER,
			l.LO_CARTAO_NOME AS NOME_CARTAO, 
			l.LO_CARTAO_BANDEIRA AS NUMERO_CARTAO, 
			l.LO_CARTAO AS BANDEIRA, 
			l.LO_PARCELAS AS PARCELAS,
			CASE WHEN l.LO_PARCEIRO = 54 THEN 1 ELSE 0 END AS PARCEIRO_PACIFICA,
			CASE WHEN u.US_GRUPO = 'VIN' THEN 1 ELSE 0 END AS VENDEDOR_INTERNO_BOOL,
			u.US_NOME AS VENDEDOR_INTERNO,
			c.NOMEPARC AS CLIENTE,
			c.EMAIL AS CLIENTE_EMAIL,
			p.NOMEPARC AS PARCEIRO,
			p.TIPO AS PARCEIRO_TIPO,
			l.LO_ORIGEM AS ORIGEM,
			l.LO_VALOR_ITEM_TABELA AS VALOR_TABELA,
			--l.LO_VALOR_DESCONTO AS VALOR_DESCONTO,
			l.LO_VALOR_OVER_INTERNO AS OVER_INTERNO,
			--(l.LO_VALOR_ITEM_TABELA - l.LO_VALOR_DESCONTO + l.LO_VALOR_OVER_INTERNO) AS PRECO_AUTORIZADO,
			l.LO_VALOR_OVER_EXTERNO AS OVER_EXTERNO,
			--(l.LO_VALOR_ITEM_TABELA - l.LO_VALOR_DESCONTO + l.LO_VALOR_OVER_INTERNO + l.LO_VALOR_OVER_EXTERNO) AS PRECO_PRATICADO,
			l.QTDE AS QTDE,
			l.ITENS AS ITENS,
			l.LO_VALOR_ADICIONAIS AS ADICIONAIS,
			l.LO_VALOR_TRANSFER AS TRANSFER_UNITARIO,
			l.LO_VALOR_DELIVERY AS DELIVERY_TOTAL,
			l.LO_COMISSAO AS COMISSAO,
			l.LO_VALOR_ITEM AS VALOR_ITEM,
			tx.TX_TAXA AS TAXA_CARTAO,
			log.LG_NOME AS NOME_LIBERACAO,
			
			CASE WHEN t.TI_NOME='Lounge' THEN 'Folia Tropical' ELSE t.TI_NOME END AS TIPO,
			ISNULL(v.VE_TIPO_ESPECIFICO,'') AS ESPECIFICO,
			--REPLACE(d.ED_NOME,'°','o') AS DIA,
			CONVERT(VARCHAR, d.ED_DATA, 103) AS DATA,
			s.ES_NOME AS SETOR,
			ISNULL(v.VE_FILA,'') AS FILA,
			ISNULL(v.VE_VAGAS,'') AS VAGAS,
			CASE WHEN l.LO_PAGO=1 THEN 'PAGO' ELSE '' END AS PAGO,

			CASE WHEN l.LO_COMISSAO_RETIDA=1 THEN 'SIM' ELSE '' END AS COMISSAO_RETIDA,
			CASE WHEN l.LO_COMISSAO_PAGA=1 THEN 'SIM' ELSE '' END AS COMISSAO_PAGA,
			--CASE WHEN l.LO_COMISSAO_PAGA=0 AND l.LO_COMISSAO_RETIDA=0 THEN 'SIM' ELSE '' END AS COMISSAO_PENDENTE,

			f.FP_NOME AS FORMA_PAGAMENTO,
			l.LO_TID AS TID,
			l.LO_DATA_COMPRA AS DATA_COMPRA,
			d.ED_DATA,

			CASE WHEN c.TELEFONE <> '' AND c.TELEFONE IS NOT NULL THEN CONCAT('+', RTRIM(LTRIM(CAST(ptelefone.PAIS_PHONECODE AS CHAR))),' (', RTRIM(LTRIM(CAST(c.DDD AS CHAR))), ') ', RTRIM(LTRIM(CAST(c.TELEFONE AS CHAR)))) ELSE '' END AS TELEFONE,
			RTRIM(LTRIM(CAST(ptelefone.PAIS_NOME AS CHAR))) AS PAIS_TELEFONE,
			CASE WHEN c.FAX <> '' AND c.FAX IS NOT NULL THEN CONCAT('+', RTRIM(LTRIM(CAST(pcelular.PAIS_PHONECODE AS CHAR))),' (', RTRIM(LTRIM(CAST(c.DDD_CELULAR AS CHAR))), ') ', RTRIM(LTRIM(CAST(c.FAX AS CHAR)))) ELSE '' END AS CELULAR,
			enderecos.CE_CIDADE AS CIDADE,
			enderecos.CE_ESTADO AS ESTADO,
			RTRIM(LTRIM(CAST(pe.PAIS_NOME AS CHAR))) AS PAIS,

			CASE
				WHEN l.LO_ANTIFRAUDE_STATUS='APA' THEN 'Aprovação Automática'
				WHEN l.LO_ANTIFRAUDE_STATUS='APM' THEN 'Aprovação Manual'
				WHEN l.LO_ANTIFRAUDE_STATUS='RPM' THEN 'Reprovado Sem Suspeita'
				WHEN l.LO_ANTIFRAUDE_STATUS='AMA' THEN 'Em Análise'
				WHEN l.LO_ANTIFRAUDE_STATUS='ERR' THEN 'Erro' 
				WHEN l.LO_ANTIFRAUDE_STATUS='NVO' THEN 'Não Classificado' 
				WHEN l.LO_ANTIFRAUDE_STATUS='SUS' THEN 'Suspensão Manual' 
				WHEN l.LO_ANTIFRAUDE_STATUS='CAN' THEN 'Cancelado' 
				WHEN l.LO_ANTIFRAUDE_STATUS='FRD' THEN 'Fraude Confirmada' 
				WHEN l.LO_ANTIFRAUDE_STATUS='RPA' THEN 'Reprovação Automática' 
				WHEN l.LO_ANTIFRAUDE_STATUS='RPP' THEN 'Reprovação por Política' 
			END AS ANTIFRAUDE_STATUS,
			
			CASE
				WHEN l.LO_ANTIFRAUDE_SCORE<30 THEN 'Risco Baixo'
				WHEN l.LO_ANTIFRAUDE_SCORE<60 THEN 'Risco Médio'
				WHEN l.LO_ANTIFRAUDE_SCORE<90 THEN 'Risco Alto'
				WHEN l.LO_ANTIFRAUDE_SCORE<100 THEN 'Risco Crítico'
			END AS ANTIFRAUDE_SCORE,

			lc.LC_COMENTARIO AS COMENTARIO,
			lci.LC_COMENTARIO AS COMENTARIO_INTERNO,

			CASE 
				WHEN ((l.LO_DATA_COMPRA < '2015-10-15') OR (l.LO_DESCONTO_FRISA = 1)) AND (t.TI_TAG='frisa' AND (FLOOR(l.QTDE / 6) > 0)) THEN l.LO_VALOR_DESCONTO + (CAST((FLOOR(l.QTDE / 6) * 50 ) AS FLOAT) / CAST(l.QTDE AS FLOAT))
				WHEN ((l.LO_DATA_COMPRA < '2015-10-15') OR (l.LO_DESCONTO_FOLIA = 1)) AND (fo.DESCONTO > 0) THEN l.LO_VALOR_DESCONTO + (((CASE WHEN (l.LO_COD<=2639) THEN 10 ELSE fo.DESCONTO END / 100.00) * ((l.LO_VALOR_ITEM_TABELA - l.LO_VALOR_DESCONTO + l.LO_VALOR_OVER_INTERNO + l.LO_VALOR_OVER_EXTERNO) * l.QTDE)) / l.QTDE)
				WHEN cp.CP_COD IS NOT NULL AND cp.CP_TIPO=1 THEN l.LO_VALOR_DESCONTO + (((cp.CP_DESCONTO / 100.00) * ((l.LO_VALOR_ITEM_TABELA - l.LO_VALOR_DESCONTO + l.LO_VALOR_OVER_INTERNO + l.LO_VALOR_OVER_EXTERNO) * l.QTDE)) / l.QTDE) 
				WHEN cp.CP_COD IS NOT NULL AND cp.CP_TIPO=2 THEN l.LO_VALOR_DESCONTO + (cp.CP_DESCONTO / l.QTDE)
				--WHEN (l.LO_VALOR_ITEM<l.LO_VALOR_ITEM_TABELA AND l.LO_VALOR_DESCONTO=0 AND l.LO_VALOR_OVER_INTERNO=0 AND l.LO_VALOR_OVER_EXTERNO=0) THEN l.LO_VALOR_ITEM_TABELA-l.LO_VALOR_ITEM
				ELSE l.LO_VALOR_DESCONTO 
			END AS VALOR_DESCONTO,

			CASE 
				WHEN ((l.LO_DATA_COMPRA < '2015-10-15') OR (l.LO_DESCONTO_FRISA = 1)) AND (t.TI_TAG='frisa' AND (FLOOR(l.QTDE / 6) > 0)) THEN 'Frisa fechada'
				WHEN ((l.LO_DATA_COMPRA < '2015-10-15') OR (l.LO_DESCONTO_FOLIA = 1)) AND (fo.DESCONTO > 0) THEN CONCAT('Combo Folia ', FLOOR(CASE WHEN (l.LO_COD<=2639) THEN 10 ELSE fo.DESCONTO END), '% de desconto')
				WHEN cp.CP_COD IS NOT NULL AND cp.CP_TIPO=1 THEN CONCAT('Cupom ', FLOOR(cp.CP_DESCONTO), '% de desconto')
				WHEN cp.CP_COD IS NOT NULL AND cp.CP_TIPO=2 THEN CONCAT('Cumpom R$ ', cp.CP_DESCONTO, ' de desconto')
				ELSE '' 
			END AS TIPO_DESCONTO

			FROM @loja l
			LEFT JOIN @vendas v ON v.VE_COD = l.LI_INGRESSO
			LEFT JOIN @parceiros p ON p.CODPARC = l.LO_PARCEIRO
			LEFT JOIN @folia fo ON fo.LO_COD = l.LO_COD
			LEFT JOIN [foliatropical2014].[dbo].[usuarios] u ON l.LO_VENDEDOR=u.US_COD
			LEFT JOIN [foliatropical2014].[dbo].[loja_comentarios] lc ON l.LI_COD=lc.LC_ITEM
			LEFT JOIN [foliatropical2014].[dbo].[loja_comentarios_internos] lci ON l.LI_COD=lci.LC_ITEM
			LEFT JOIN [foliatropical2014].[dbo].[tipos] t ON t.TI_COD=v.VE_TIPO
			LEFT JOIN [foliatropical2014].[dbo].[taxa_cartao] tx
				ON (l.LO_FORMA_PAGAMENTO=1 AND l.LO_CARTAO=tx.TX_CARTAO AND l.LO_PARCELAS >= tx.TX_PARCELAS_INICIO AND l.LO_PARCELAS <= tx.TX_PARCELAS_FIM)
				OR (l.LO_FORMA_PAGAMENTO=6 AND tx.TX_CARTAO='pos')
				OR (l.LO_FORMA_PAGAMENTO=2014 AND tx.TX_CARTAO='pos')
			LEFT JOIN [foliatropical2014].[dbo].[eventos_dias] d ON d.ED_COD=v.VE_DIA
			LEFT JOIN [foliatropical2014].[dbo].[eventos_setores] s ON s.ES_COD=v.VE_SETOR
			LEFT JOIN [foliatropical2014].[dbo].[formas_pagamento] f ON f.FP_COD=l.LO_FORMA_PAGAMENTO
			LEFT JOIN [foliatropical2014].[dbo].[cupom] cp ON cp.CP_COMPRA=l.LO_COD AND cp.CP_UTILIZADO=1 AND cp.CP_BLOCK=0 AND cp.D_E_L_E_T_=0
			LEFT JOIN [foliatropical2014].[dbo].[log] log ON log.LG_VOUCHER=l.LO_COD AND log.LG_ACAO='Pagamento liberado'
			LEFT JOIN @clientes c ON c.CODPARC = l.LO_CLIENTE
			LEFT JOIN [parceiros].[dbo].[pais] ptelefone ON ptelefone.PAIS_SIGLA = c.DDI
			LEFT JOIN [parceiros].[dbo].[pais] pcelular ON pcelular.PAIS_SIGLA = c.DDI_CELULAR
			LEFT JOIN @enderecos enderecos ON enderecos.CE_CLIENTE = l.LO_CLIENTE
			LEFT JOIN [parceiros].[dbo].[pais] pe ON pe.PAIS_SIGLA = enderecos.CE_PAIS
			WHERE d.D_E_L_E_T_=0 AND t.D_E_L_E_T_=0 AND s.D_E_L_E_T_=0
		) S
	) S 
) S ORDER BY TIPO ASC, ED_DATA ASC