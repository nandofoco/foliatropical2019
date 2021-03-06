--Relatorio de Pendencias

DECLARE @loja TABLE (LO_COD INT, LO_CLIENTE INT, LO_DATA_COMPRA DATETIME, LO_VALOR_TOTAL FLOAT, LO_VALOR_PARCIAL FLOAT, LO_VALOR_INGRESSOS FLOAT, LO_VALOR_ADICIONAIS FLOAT, LO_VALOR_TRANSFER FLOAT, LO_VALOR_DELIVERY FLOAT, LO_VALOR_DESCONTO FLOAT, LO_VALOR_OVER_INTERNO FLOAT, LO_VALOR_OVER_EXTERNO FLOAT, LO_COMISSAO INT, LO_PAGO TINYINT, LO_FORMA_PAGAMENTO INT, LO_NUM_ITENS INT);

INSERT INTO @loja (LO_COD, LO_CLIENTE, LO_DATA_COMPRA, LO_VALOR_TOTAL, LO_VALOR_PARCIAL, LO_VALOR_INGRESSOS, LO_VALOR_ADICIONAIS, LO_VALOR_TRANSFER, LO_VALOR_DELIVERY, LO_VALOR_DESCONTO, LO_VALOR_OVER_INTERNO, LO_VALOR_OVER_EXTERNO, LO_COMISSAO, LO_PAGO, LO_FORMA_PAGAMENTO, LO_NUM_ITENS)
SELECT LO_COD, LO_CLIENTE, LO_DATA_COMPRA, LO_VALOR_TOTAL, LO_VALOR_PARCIAL, LO_VALOR_INGRESSOS, LO_VALOR_ADICIONAIS, LO_VALOR_TRANSFER, LO_VALOR_DELIVERY, LO_VALOR_DESCONTO, LO_VALOR_OVER_INTERNO, LO_VALOR_OVER_EXTERNO, LO_COMISSAO, LO_PAGO, LO_FORMA_PAGAMENTO, LO_NUM_ITENS
FROM [foliatropical2014].[dbo].[loja] WHERE LO_EVENTO=%evento% AND D_E_L_E_T_=0;


DECLARE @clientes TABLE (CODPARC INT, NOMEPARC VARCHAR(255), EMAIL VARCHAR(255), CGC_CPF VARCHAR(255));

INSERT INTO @clientes (CODPARC, NOMEPARC, EMAIL, CGC_CPF)
SELECT CODPARC, NOMEPARC, EMAIL, CGC_CPF FROM [parceiros].[dbo].[TGFPAR] WHERE CLIENTE='S';


DECLARE @pendencias TABLE (LP_COD INT, LP_COMPRA INT, CL1 VARCHAR(10), CL2 VARCHAR(10), CL3 VARCHAR(10), CL4 VARCHAR(10), CL5 VARCHAR(10), CL6 VARCHAR(10), CL7 VARCHAR(10), CL8 VARCHAR(10));

INSERT INTO @pendencias (LP_COD, LP_COMPRA, CL1, CL2, CL3, CL4, CL5, CL6, CL7, CL8)
SELECT LP_COD, LP_COMPRA, REPLACE([1], '0', 'SIM'), REPLACE([2], '0', 'SIM'), REPLACE([3], '0', 'SIM'), REPLACE([4], '0', 'SIM'), REPLACE([5], '0', 'SIM'), REPLACE([6], '0', 'SIM'), REPLACE([7], '0', 'SIM'), REPLACE([8], '0', 'SIM')
FROM [foliatropical2014].[dbo].[loja_pendencias] PIVOT (SUM(D_E_L_E_T_) FOR LP_PENDENCIA IN ([1],[2],[3],[4],[5],[6],[7],[8])) P ORDER BY 1;


DECLARE @agendar TABLE (LIA_COMPRA INT, QTDE INT);

INSERT INTO @agendar (LIA_COMPRA, QTDE)
SELECT LIA_COMPRA, COUNT(LIA_COD) AS QTDE FROM [foliatropical2014].[dbo].[loja_itens_adicionais]
WHERE LIA_ADICIONAL IN (SELECT VA_COD FROM [foliatropical2014].[dbo].[vendas_adicionais] WHERE (VA_NOME_EXIBICAO='transfer' OR VA_NOME_EXIBICAO='transferinout') AND VA_BLOCK=0 AND D_E_L_E_T_=0) GROUP BY LIA_COMPRA;


DECLARE @agendados TABLE (LI_COMPRA INT, QTDE INT DEFAULT 0);
INSERT INTO @agendados (LI_COMPRA, QTDE)
SELECT l.LI_COMPRA, COUNT(t.TA_ITEM) FROM [foliatropical2014].[dbo].[transportes_agendamento] t, [foliatropical2014].[dbo].[loja_itens] l WHERE l.LI_COD=t.TA_ITEM AND t.D_E_L_E_T_=0 AND l.D_E_L_E_T_=0 GROUP BY l.LI_COMPRA;


SELECT

VOUCHER,
CLIENTE,
ADICIONAIS,
TRANSFER,
DELIVERY,
VALOR,
FORMA_PAGAMENTO AS 'FORMA PAGAMENTO',
DATA_COMPRA AS 'DATA COMPRA',
ITENS,
ISNULL(CL1,'') AS 'ASSINAR SLIP',
ISNULL(CL2,'') AS 'RECEBER SLIP',
ISNULL(CL3,'') AS 'CÓPIA DO CARTÃO',
ISNULL(CL4,'') AS 'CÓPIA DO PASSAPORTE',
ISNULL(CL5,'') AS 'CARTA DE AUTORIZAÇÃO',
ISNULL(CL6,'') AS 'APRESENTAR ID',
ISNULL(CL7,'') AS 'DEFINIR TRANSPORTE',
ISNULL(CL8,'') AS 'KIT',
CASE WHEN((AGENDAR - AGENDADOS) > 0) THEN 'SIM' ELSE '' END AS 'TRANSPORTE PENDENTE'

FROM (

	SELECT 
	l.LO_COD AS VOUCHER,
	c.NOMEPARC AS CLIENTE,
	((l.LO_VALOR_INGRESSOS - l.LO_VALOR_DESCONTO + l.LO_VALOR_OVER_INTERNO) - ((l.LO_VALOR_INGRESSOS - l.LO_VALOR_DESCONTO + l.LO_VALOR_OVER_INTERNO) * l.LO_COMISSAO / 100)) AS VALOR,
	l.LO_NUM_ITENS AS ITENS,
	l.LO_VALOR_ADICIONAIS AS ADICIONAIS,
	l.LO_VALOR_TRANSFER AS TRANSFER,
	f.FP_NOME AS FORMA_PAGAMENTO,
	l.LO_VALOR_DELIVERY AS DELIVERY,
	l.LO_DATA_COMPRA AS DATA_COMPRA,
	p.*,
	ISNULL(a.QTDE, 0) AS AGENDAR,
	ISNULL(ad.QTDE, 0) AS AGENDADOS

	FROM @loja l
	LEFT JOIN @clientes c ON c.CODPARC = l.LO_CLIENTE
	LEFT JOIN @pendencias p ON p.LP_COMPRA=l.LO_COD
	LEFT JOIN @agendar a ON a.LIA_COMPRA=l.LO_COD
	LEFT JOIN @agendados ad ON ad.LI_COMPRA=l.LO_COD
	LEFT JOIN [foliatropical2014].[dbo].[formas_pagamento] f ON f.FP_COD=l.LO_FORMA_PAGAMENTO

) S ORDER BY VOUCHER ASC