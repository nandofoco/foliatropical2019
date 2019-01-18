--Relatorio de Vendas

DECLARE @vendas TABLE (VE_COD INT, VE_TIPO INT, VE_ESTOQUE INT, VE_SETOR INT, VE_DIA INT, VE_FILA VARCHAR(255), VE_VAGAS INT, VE_TIPO_ESPECIFICO VARCHAR(255));

INSERT INTO @vendas (VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO)
SELECT VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO FROM [foliatropical].[dbo].[vendas] WHERE VE_BLOCK=0 AND D_E_L_E_T_=0;


DECLARE @loja TABLE (LO_COD INT, LO_CLIENTE INT, LO_PARCEIRO INT, LO_DATA_COMPRA DATETIME, LO_VALOR_TOTAL FLOAT, LO_PAGO TINYINT, LO_TID VARCHAR(255), LI_INGRESSO INT, QTDE INT);

INSERT INTO @loja (LO_COD, LO_CLIENTE, LO_PARCEIRO, LO_DATA_COMPRA, LO_VALOR_TOTAL, LO_PAGO, LO_TID, LI_INGRESSO, QTDE)
SELECT l.LO_COD, l.LO_CLIENTE, l.LO_PARCEIRO, MAX(l.LO_DATA_COMPRA), MAX(l.LO_VALOR_TOTAL), MAX(l.LO_PAGO), MAX(l.LO_TID), li.LI_INGRESSO, COUNT (li.LI_COD)
FROM [foliatropical].[dbo].[loja] l, [foliatropical].[dbo].[loja_itens] li WHERE l.LO_EVENTO=1 AND l.LO_PARCEIRO >0 AND l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0
GROUP BY li.LI_INGRESSO, l.LO_COD, l.LO_PARCEIRO, l.LO_CLIENTE;


DECLARE @clientes TABLE (CODPARC INT, NOMEPARC VARCHAR(255), EMAIL VARCHAR(255), CGC_CPF VARCHAR(255));

INSERT INTO @clientes (CODPARC, NOMEPARC, EMAIL, CGC_CPF)
SELECT CODPARC, NOMEPARC, EMAIL, CGC_CPF FROM [SANKHYA_PROD].[sankhya].[TGFPAR] WHERE CLIENTE='S';


DECLARE @parceiros TABLE (CODPARC INT, NOMEPARC VARCHAR(255));

INSERT INTO @parceiros (CODPARC, NOMEPARC)
SELECT CODPARC, NOMEPARC FROM [SANKHYA_PROD].[sankhya].[TGFPAR] WHERE VENDEDOR='S';


SELECT VOUCHER, PARCEIRO, CLIENTE, EMAIL, CPF_CNPJ, DATA_COMPRA, QTDE, TID, TIPO, ESPECIFICO, DATA, SETOR, FILA, VAGAS, PAGO FROM (
	SELECT 
	l.LO_COD AS VOUCHER,
	p.NOMEPARC AS PARCEIRO,
	c.NOMEPARC AS CLIENTE,
	c.EMAIL AS EMAIL,
	c.CGC_CPF AS CPF_CNPJ,
	(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA_COMPRA,
	l.LO_VALOR_TOTAL AS TOTAL,
	l.LO_TID AS TID,
	CASE WHEN v.VE_TIPO_ESPECIFICO='fechado' AND v.VE_VAGAS>0 THEN l.QTDE/v.VE_VAGAS ELSE l.QTDE END AS QTDE,
	CASE WHEN t.TI_NOME='Lounge' THEN 'Folia Tropical' ELSE t.TI_NOME END AS TIPO,
	ISNULL(v.VE_TIPO_ESPECIFICO,'') AS ESPECIFICO,
	--REPLACE(d.ED_NOME,'°','o') AS DIA,
	CONVERT(VARCHAR, d.ED_DATA, 103) AS DATA,
	s.ES_NOME AS SETOR,
	ISNULL(v.VE_FILA,'') AS FILA,
	ISNULL(v.VE_VAGAS,'') AS VAGAS,
	CASE WHEN l.LO_PAGO=1 THEN 'PAGO' ELSE '' END AS PAGO,
	d.ED_DATA

	FROM @loja l
	LEFT JOIN @vendas v ON v.VE_COD = l.LI_INGRESSO
	LEFT JOIN @clientes c ON c.CODPARC = l.LO_CLIENTE
	LEFT JOIN @parceiros p ON p.CODPARC = l.LO_PARCEIRO
	LEFT JOIN [foliatropical].[dbo].[tipos] t ON t.TI_COD=v.VE_TIPO
	LEFT JOIN [foliatropical].[dbo].[eventos_dias] d ON d.ED_COD=v.VE_DIA
	LEFT JOIN [foliatropical].[dbo].[eventos_setores] s ON s.ES_COD=v.VE_SETOR
	WHERE d.D_E_L_E_T_=0 AND t.D_E_L_E_T_=0 AND s.D_E_L_E_T_=0	
) S ORDER BY PARCEIRO ASC, TIPO ASC, ED_DATA ASC