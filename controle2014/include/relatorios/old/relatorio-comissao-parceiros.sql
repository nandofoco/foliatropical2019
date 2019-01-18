--Relatorio de Comissão de Parceiros

DECLARE @loja TABLE (LO_COD INT, LO_PARCEIRO INT, LO_DATA_COMPRA DATETIME, LO_VALOR_TOTAL FLOAT, LO_VALOR_INGRESSOS FLOAT, LO_VALOR_ADICIONAIS FLOAT, LO_PAGO TINYINT);

INSERT INTO @loja (LO_COD, LO_PARCEIRO, LO_DATA_COMPRA, LO_VALOR_TOTAL, LO_VALOR_INGRESSOS, LO_VALOR_ADICIONAIS, LO_PAGO)
SELECT LO_COD, LO_PARCEIRO, LO_DATA_COMPRA, LO_VALOR_TOTAL, LO_VALOR_INGRESSOS, LO_VALOR_ADICIONAIS, LO_PAGO
FROM [foliatropical].[dbo].[loja] WHERE LO_EVENTO=1 AND LO_PARCEIRO>0 AND D_E_L_E_T_=0;


DECLARE @vendas TABLE (VE_COD INT, VE_TIPO INT, VE_ESTOQUE INT, VE_SETOR INT, VE_DIA INT, VE_FILA VARCHAR(255), VE_VAGAS INT, VE_TIPO_ESPECIFICO VARCHAR(255));

INSERT INTO @vendas (VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO)
SELECT VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO FROM [foliatropical].[dbo].[vendas] WHERE VE_BLOCK=0 AND D_E_L_E_T_=0;


DECLARE @loja_itens_grupo TABLE (LI_INGRESSO INT, LI_COMPRA INT, LI_VALOR_TABELA FLOAT, LI_VALOR FLOAT, QTDE INT);
INSERT INTO @loja_itens_grupo (LI_INGRESSO, LI_COMPRA, LI_VALOR_TABELA, LI_VALOR, QTDE)
SELECT
	LI_INGRESSO,
	LI_COMPRA,
	SUM(LI_VALOR_TABELA) AS LI_VALOR_TABELA,
	SUM(LI_VALOR) AS LI_VALOR,
	COUNT (LI_COD) AS QTDE
	FROM [foliatropical].[dbo].[loja_itens]
	WHERE D_E_L_E_T_=0
GROUP BY LI_INGRESSO, LI_COMPRA;

DECLARE @loja_itens TABLE (LI_COMPRA INT, LI_VALOR_TABELA FLOAT, LI_VALOR FLOAT);
INSERT INTO @loja_itens (LI_COMPRA, LI_VALOR_TABELA, LI_VALOR)
SELECT 
l.LI_COMPRA AS LI_COMPRA,
CASE WHEN v.VE_TIPO_ESPECIFICO='fechado' AND v.VE_VAGAS>0 THEN l.LI_VALOR_TABELA/l.QTDE ELSE l.LI_VALOR_TABELA END AS LI_VALOR_TABELA,
CASE WHEN v.VE_TIPO_ESPECIFICO='fechado' AND v.VE_VAGAS>0 THEN l.LI_VALOR/l.QTDE ELSE l.LI_VALOR END AS LI_VALOR
FROM @loja_itens_grupo l
LEFT JOIN @vendas v ON v.VE_COD = l.LI_INGRESSO;

DECLARE @itens TABLE (LI_COMPRA INT, LI_VALOR_TABELA FLOAT, LI_VALOR FLOAT);
INSERT INTO @itens (LI_COMPRA, LI_VALOR_TABELA, LI_VALOR)
SELECT 
LI_COMPRA,
SUM(LI_VALOR_TABELA),
SUM(LI_VALOR)
FROM @loja_itens
GROUP BY LI_COMPRA;


DECLARE @parceiros TABLE (CODPARC INT, NOMEPARC VARCHAR(255), EMAIL VARCHAR(80), CGC_CPF VARCHAR(14), AD_COMISSAO FLOAT);

INSERT INTO @parceiros (CODPARC, NOMEPARC, EMAIL, CGC_CPF, AD_COMISSAO)
SELECT CODPARC, NOMEPARC, EMAIL, CGC_CPF, AD_COMISSAO FROM [SANKHYA_PROD].[sankhya].[TGFPAR] WHERE VENDEDOR='S';

SELECT * FROM (
	SELECT 
	l.LO_COD AS VOUCHER,
	c.NOMEPARC AS PARCEIRO,
	c.EMAIL AS EMAIL,
	c.CGC_CPF AS CPF_CNPJ,
	l.LO_VALOR_TOTAL AS TOTAL,
	l.LO_VALOR_INGRESSOS AS INGRESSOS,
	l.LO_VALOR_ADICIONAIS AS ADICIONAIS,
	li.LI_VALOR AS VALOR_COBRADO,
	li.LI_VALOR_TABELA AS VALOR_TABELA,
	--(l.LO_VALOR_INGRESSOS * (c.AD_COMISSAO / 100)) AS VALOR_COMISSAO,
	c.AD_COMISSAO AS COMISSAO,
	(CASE WHEN li.LI_VALOR_TABELA<l.LO_VALOR_INGRESSOS THEN li.LI_VALOR_TABELA ELSE l.LO_VALOR_INGRESSOS END * (c.AD_COMISSAO / 100)) AS VALOR_COMISSAO,
	(li.LI_VALOR - li.LI_VALOR_TABELA) AS VALOR_OVER,
	(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA_COMPRA,
	CASE WHEN l.LO_PAGO=1 THEN 'PAGO' ELSE '' END AS PAGO
	
	FROM @loja l
	LEFT JOIN @parceiros c ON c.CODPARC = l.LO_PARCEIRO
	LEFT JOIN @itens li ON li.LI_COMPRA = l.LO_COD
) S ORDER BY PARCEIRO ASC


