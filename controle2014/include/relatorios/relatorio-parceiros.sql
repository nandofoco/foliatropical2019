SELECT 
p.CODPARC,
p.NOMEPARC,
p.RAZAOSOCIAL,
p.IDENTINSCESTAD,
p.EMAIL,
p.TELEFONE,
p.CGC_CPF,
p.TIPPESSOA,
p.CEP,
p.CODEND,
p.NUMEND,
p.COMPLEMENTO,
p.CODBAI,
p.CODCID,
p.VENDEDOR,
p.CODBCO,
p.CODAGE,
p.CODCTABCO,
p.DTCAD,
p.DTALTER,
p.BLOQUEAR,
p.AD_COMISSAO,
p.TIPO,
p.DESCONTO,
c.CODCID,
c.NOMECID,
c.UF,
u.CODUF,
u.UF,
e.CODEND,
e.NOMEEND,
b.CODBAI,
b.NOMEBAI,
p.CUPOM 

FROM 
[parceiros].[dbo].[TGFPAR] p,
[parceiros].[dbo].[TSICID] c,
[parceiros].[dbo].[TSIUFS] u,
[parceiros].[dbo].[TSIEND] e,
[parceiros].[dbo].[TSIBAI] b 

WHERE

p.CODCID=c.CODCID
AND c.UF=u.CODUF
AND p.CODEND=e.CODEND
AND p.CODBAI=b.CODBAI
AND p.VENDEDOR='S';
