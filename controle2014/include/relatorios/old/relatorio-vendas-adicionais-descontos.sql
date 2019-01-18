--Relatorio de Adicionais e Descontos

DECLARE @loja TABLE (LO_COD INT, LO_PARCEIRO INT, LO_CLIENTE INT, LO_DATA_COMPRA DATETIME, LO_VALOR_TOTAL FLOAT, LO_VALOR_INGRESSOS FLOAT, LO_VALOR_ADICIONAIS FLOAT, LO_VALOR_PARCIAL FLOAT, LO_PAGO TINYINT);

INSERT INTO @loja (LO_COD, LO_PARCEIRO, LO_CLIENTE, LO_DATA_COMPRA, LO_VALOR_TOTAL, LO_VALOR_INGRESSOS, LO_VALOR_ADICIONAIS, LO_VALOR_PARCIAL, LO_PAGO)
SELECT LO_COD, LO_PARCEIRO, LO_CLIENTE, LO_DATA_COMPRA, LO_VALOR_TOTAL, LO_VALOR_INGRESSOS, LO_VALOR_ADICIONAIS, LO_VALOR_PARCIAL, LO_PAGO
FROM [foliatropical].[dbo].[loja] WHERE LO_EVENTO=1 AND D_E_L_E_T_=0;

DECLARE @clientes TABLE (CODPARC INT, NOMEPARC VARCHAR(255), EMAIL VARCHAR(255), CGC_CPF VARCHAR(255));

INSERT INTO @clientes (CODPARC, NOMEPARC, EMAIL, CGC_CPF)
SELECT CODPARC, NOMEPARC, EMAIL, CGC_CPF FROM [SANKHYA_PROD].[sankhya].[TGFPAR] WHERE CLIENTE='S';

DECLARE @parceiros TABLE (CODPARC INT, NOMEPARC VARCHAR(255), EMAIL VARCHAR(80), CGC_CPF VARCHAR(14), AD_COMISSAO FLOAT);

INSERT INTO @parceiros (CODPARC, NOMEPARC, EMAIL, CGC_CPF, AD_COMISSAO)
SELECT CODPARC, NOMEPARC, EMAIL, CGC_CPF, AD_COMISSAO FROM [SANKHYA_PROD].[sankhya].[TGFPAR] WHERE VENDEDOR='S';

SELECT * FROM (
	SELECT 
	l.LO_COD AS VOUCHER,
	c.NOMEPARC AS CLIENTE,
	p.NOMEPARC AS PARCEIRO,
	p.EMAIL AS EMAIL,
	p.CGC_CPF AS CPF_CNPJ,
	l.LO_VALOR_TOTAL AS TOTAL,
	l.LO_VALOR_INGRESSOS AS INGRESSOS,
	l.LO_VALOR_ADICIONAIS AS ADICIONAIS,
	(l.LO_VALOR_PARCIAL - l.LO_VALOR_TOTAL) AS DESCONTOS,
	(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA_COMPRA,
	CASE WHEN l.LO_PAGO=1 THEN 'PAGO' ELSE '' END AS PAGO
	
	FROM @loja l
	LEFT JOIN @clientes c ON c.CODPARC = l.LO_CLIENTE
	LEFT JOIN @parceiros p ON p.CODPARC = l.LO_PARCEIRO
) S ORDER BY PARCEIRO ASC


