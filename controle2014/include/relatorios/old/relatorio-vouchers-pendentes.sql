--Relatorio de Vouchers Entregues

DECLARE @loja TABLE (LO_COD INT, LO_CLIENTE INT, LO_PARCEIRO INT, LO_DATA_COMPRA DATETIME, LO_VALOR_TOTAL FLOAT, LO_PAGO TINYINT, LO_TID VARCHAR(255));

INSERT INTO @loja (LO_COD, LO_CLIENTE, LO_PARCEIRO, LO_DATA_COMPRA, LO_VALOR_TOTAL, LO_PAGO, LO_TID)
SELECT LO_COD, LO_CLIENTE, LO_PARCEIRO, LO_DATA_COMPRA, LO_VALOR_TOTAL, LO_PAGO, LO_TID
FROM [foliatropical].[dbo].[loja] WHERE LO_EVENTO=1 AND LO_ENVIADO=0 AND D_E_L_E_T_=0;


DECLARE @clientes TABLE (CODPARC INT, NOMEPARC VARCHAR(255), EMAIL VARCHAR(255), CGC_CPF VARCHAR(255));

INSERT INTO @clientes (CODPARC, NOMEPARC, EMAIL, CGC_CPF)
SELECT CODPARC, NOMEPARC, EMAIL, CGC_CPF FROM [SANKHYA_PROD].[sankhya].[TGFPAR] WHERE CLIENTE='S';


DECLARE @parceiros TABLE (CODPARC INT, NOMEPARC VARCHAR(255));

INSERT INTO @parceiros (CODPARC, NOMEPARC)
SELECT CODPARC, NOMEPARC FROM [SANKHYA_PROD].[sankhya].[TGFPAR] WHERE VENDEDOR='S';


SELECT VOUCHER, PARCEIRO, CLIENTE, EMAIL, CPF_CNPJ, DATA_COMPRA, TID, PAGO FROM (
	SELECT 
	l.LO_COD AS VOUCHER,
	p.NOMEPARC AS PARCEIRO,
	c.NOMEPARC AS CLIENTE,
	c.EMAIL AS EMAIL,
	c.CGC_CPF AS CPF_CNPJ,
	(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA_COMPRA,
	l.LO_VALOR_TOTAL AS TOTAL,
	l.LO_TID AS TID,
	CASE WHEN l.LO_PAGO=1 THEN 'PAGO' ELSE '' END AS PAGO,
	l.LO_DATA_COMPRA

	FROM @loja l
	LEFT JOIN @clientes c ON c.CODPARC = l.LO_CLIENTE
	LEFT JOIN @parceiros p ON p.CODPARC = l.LO_PARCEIRO
) S ORDER BY LO_DATA_COMPRA ASC, PARCEIRO ASC