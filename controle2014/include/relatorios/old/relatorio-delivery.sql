--Relatorio de Delivery
DECLARE @clientes TABLE (CODPARC INT, NOMEPARC VARCHAR(255), EMAIL VARCHAR(255), TELEFONE VARCHAR(255));

INSERT INTO @clientes (CODPARC, NOMEPARC, EMAIL, TELEFONE)
SELECT CODPARC, NOMEPARC, EMAIL, TELEFONE FROM [SANKHYA_PROD].[sankhya].[TGFPAR] WHERE CLIENTE='S';

DECLARE @adicionais TABLE (COMPRA INT, INCLUSO TINYINT, VALOR FLOAT);

INSERT INTO @adicionais (COMPRA, INCLUSO, VALOR)
SELECT 
LIA_COMPRA AS COMPRA,
LIA_INCLUSO AS INCLUSO,
CASE WHEN LIA_INCLUSO=1 THEN 0.00 ELSE LIA_VALOR END AS VALOR 
FROM [foliatropical].[dbo].[loja_itens_adicionais]
WHERE LIA_ADICIONAL=(SELECT VA_COD FROM [foliatropical].[dbo].[vendas_adicionais] WHERE VA_NOME_INSERCAO='delivery')
AND LIA_BLOCK=0 AND D_E_L_E_T_=0;

SELECT

l.LO_COD AS VOUCHER,
c.NOMEPARC AS CLIENTE,
CASE WHEN a.INCLUSO=1 THEN 'INCLUSO' ELSE '-' END AS INCLUSO,
CASE WHEN a.INCLUSO=1 THEN 0.00 ELSE a.VALOR END AS VALOR,
ISNULL(c.EMAIL, '') AS EMAIL,
ISNULL(c.TELEFONE, '') AS TELEFONE,
CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103) AS DATA,
CASE WHEN l.LO_PAGO=1 THEN 'PAGO' ELSE '' END AS PAGO,
l.LO_CLI_ENDERECO AS ENDERECO,
l.LO_CLI_NUMERO AS NUMERO,
l.LO_CLI_COMPLEMENTO AS COMPLEMENTO,
l.LO_CLI_BAIRRO AS BAIRRO,
l.LO_CLI_CIDADE AS CIDADE,
l.LO_CLI_ESTADO AS ESTADO,
l.LO_CLI_CEP AS CEP
--l.LO_CLI_TELEFONE AS TELEFONE
--CASE WHEN l.LO_ENVIADO=1 THEN 'ENVIADO' ELSE '' END AS ENVIADO

FROM [foliatropical].[dbo].[loja] l
LEFT JOIN @clientes c ON c.CODPARC = l.LO_CLIENTE
LEFT JOIN @adicionais a ON a.COMPRA = l.LO_COD

WHERE l.LO_EVENTO=1 AND l.LO_BLOCK=0 AND l.D_E_L_E_T_=0 AND l.LO_DELIVERY=1
ORDER BY l.LO_COD ASC;