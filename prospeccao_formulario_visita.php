<? session_start(); ?>
<?php require_once('restrito.php'); ?>
<?php require_once('Connections/conexao.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

require_once('parametros.php');
require_once('funcao_consulta_modulo_array.php');

// usuário logado via SESSION
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("SELECT * FROM usuarios WHERE usuario = %s", GetSQLValueString($colname_usuario, "text"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
// fim - usuário logado via SESSION

// prospeccao
$colname_prospeccao = "-1";
if (isset($_GET['id_prospeccao'])) {
  $colname_prospeccao = $_GET['id_prospeccao'];
}
mysql_select_db($database_conexao, $conexao);
$query_prospeccao = sprintf("
SELECT prospeccao.*, 
(SELECT nome FROM usuarios WHERE prospeccao.id_usuario_responsavel = usuarios.IdUsuario) AS usuario_responsavel
FROM prospeccao 
WHERE prospeccao.id = %s", 
GetSQLValueString($colname_prospeccao, "int"));
$prospeccao = mysql_query($query_prospeccao, $conexao) or die(mysql_error());
$row_prospeccao = mysql_fetch_assoc($prospeccao);
$totalRows_prospeccao = mysql_num_rows($prospeccao);
// fim - prospeccao

// agenda
$colname_agenda = "-1";
if (isset($_GET['id_agenda'])) {
  $colname_agenda = $_GET['id_agenda'];
}
mysql_select_db($database_conexao, $conexao);
$query_agenda = sprintf("
SELECT agenda.* 
FROM agenda 
WHERE agenda.id_agenda = %s", 
GetSQLValueString($colname_agenda, "int"));
$agenda = mysql_query($query_agenda, $conexao) or die(mysql_error());
$row_agenda = mysql_fetch_assoc($agenda);
$totalRows_agenda = mysql_num_rows($agenda);
// fim - agenda

// prospeccao_formulario
$colname_prospeccao_formulario = "-1";
if (isset($_GET['id_agenda'])) {
  $colname_prospeccao_formulario = $_GET['id_agenda'];
}
mysql_select_db($database_conexao, $conexao);
$query_prospeccao_formulario = sprintf("
SELECT prospeccao_formulario.* 
FROM prospeccao_formulario 
WHERE prospeccao_formulario.id_agenda = %s", 
GetSQLValueString($colname_prospeccao_formulario, "int"));
$prospeccao_formulario = mysql_query($query_prospeccao_formulario, $conexao) or die(mysql_error());
$row_prospeccao_formulario = mysql_fetch_assoc($prospeccao_formulario);
$totalRows_prospeccao_formulario = mysql_num_rows($prospeccao_formulario);
// fim - prospeccao_formulario

// prospeccao_contato
$colname_prospeccao_contato = "-1";
if (isset($_GET['id_prospeccao'])) {
  $colname_prospeccao_contato = $_GET['id_prospeccao'];
}
mysql_select_db($database_conexao, $conexao);
$query_prospeccao_contato = sprintf("
SELECT prospeccao_contato.* 
FROM prospeccao_contato 
WHERE prospeccao_contato.id_prospeccao = %s 
ORDER BY prospeccao_contato.id DESC 
LIMIT 3", 
GetSQLValueString($colname_prospeccao_contato, "int"));
$prospeccao_contato = mysql_query($query_prospeccao_contato, $conexao) or die(mysql_error());
$row_prospeccao_contato = mysql_fetch_assoc($prospeccao_contato);
$totalRows_prospeccao_contato = mysql_num_rows($prospeccao_contato);
// fim - prospeccao_contato
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
<style>
body {
	font-family: Verdana, Geneva, sans-serif;
	font-size: 10px;
}
table.bordasimples {
	border-collapse: collapse;
	font-size: 10px;

}table.bordatransparente {
	border-collapse: inherit;
	font-size: 10px;
}
table.bordasimples tr td {
	border:1px solid #000;
	font-family: Verdana, Geneva, sans-serif;
	padding-left: 3px;
	padding-right: 3px;
	padding-top: 1px;
	padding-bottom: 1px;
	vertical-align: top;
	line-height: 1;
}
.titulo_formulario {
	font-family: Verdana, Geneva, sans-serif;
	font-size: 12px;
	padding: 3px;
}
.caixa_texto{
	margin-top: 5px; 
	margin-bottom: 5px; 
	text-align:justify; 
	font-size: 8px;
	line-height: 0.9;
}
.caixa_observacao{
	padding-top: 2px;
	text-align:justify; 
	font-size: 8px;
	line-height: 1;
}
.titulo_grupo{
	margin-top: 20px;
	margin-bottom: 5px;
	font-size: 10px;
	font-weight: bold;
}
</style>
</head>

<body>

<table width="100%" height="20" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="titulo_formulario" style="font-weight: bold">
      FORMULÁRIO DE VISITA (Prospecção nº <? echo $row_prospeccao['id']; ?> / Formulário nº <? echo $row_prospeccao_formulario['IdFormulario']; ?>)
    </td>
  </tr>
</table>

<div class="titulo_grupo">Dados do Cliente:</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>   
        <td width="100%">

			<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
			
				<td width="50%">
				Nome/Razão Social:
				<div><?php echo utf8_encode($row_prospeccao['nome_razao_social']); ?></div>
				</td>
				
				<td width="50%">
				Nome Fantasia:
				<div><?php echo $row_prospeccao['fantasia']; ?></div>
				</td>
			
			</tr>
			</table>
	
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>   
			   
				<td width="100%">
				Endereço: 
				<br>
				<?php echo $row_prospeccao['endereco']; ?> <?php echo $row_prospeccao['endereco_numero']; ?> <?php echo $row_prospeccao['endereco_complemento']; ?>
				</td> 
					
			</tr>
			</table>
			
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>   
			   
				<td width="33%">
				Telefone: 
				<br>
				<?php echo $row_prospeccao['telefone']; ?>
				</td> 
				
				<td width="34%">
				Celular: 
				<br>
				<?php echo $row_prospeccao['celular']; ?>
				</td> 
				
				<td width="33%">
				E-mail: 
				<br>
				<?php echo $row_prospeccao['email']; ?>
				</td> 
					
			</tr>
			</table>
			
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>   
			   
				<td width="33%">
				Responsável pela Empresa: 
				<br>
				<?php echo $row_prospeccao['empresa_responsavel']; ?>
				</td> 
				
				<td width="34%">
				Responsável por TI: 
				<br>
				<?php echo $row_prospeccao['responsavel_por_ti']; ?>
				</td> 
				
				<td width="33%">
				Contato: 
				<br>
				<?php echo $row_prospeccao['empresa_contato']; ?>
				</td> 
					
			</tr>
			</table>

    </td>
</tr>
</table>


<div class="titulo_grupo">Dados Contábeis/Fiscais:</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>   
	<td width="100%">

		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td width="50%">
				Contabilidade:
				<div><?php echo $row_prospeccao['contador']; ?></div>
				</td>
				
				<td width="25%">
				Telefone (cont):
				<div><?php echo $row_prospeccao['contador_telefone']; ?></div>
				</td>
				
				<td width="25%">
				E-mail (cont):
				<div><?php echo $row_prospeccao['contador_email']; ?></div>
				</td>
			</tr>
		</table>
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td width="50%">
				Enquadramento Fiscal:
				<div>
				<?php if($row_prospeccao['enquadramento_fiscal']==""){ ?>   
					<?php echo $row_prospeccao['enquadramento_fiscal_outro']; ?>
				<? } else { ?>
					<?php echo $row_prospeccao['enquadramento_fiscal']; ?>
				<? } ?>
				</div>
				</td>
				
				<td width="50%">
				Informações Fiscais:
				<div>
				Exige NFE: <?php if($row_prospeccao['exige_nfe']=="0"){echo "não";} if($row_prospeccao['exige_nfe']=="1"){echo "sim";} ?> | 
				Exige Cupom Fiscal: <?php if($row_prospeccao['exige_cupom_fiscal']=="0"){echo "não";} if($row_prospeccao['exige_cupom_fiscal']=="1"){echo "sim";} ?> | 
				Outras: <?php echo $row_prospeccao['exige_outro']; ?>
				</div>
				</td>
			</tr>
		</table>
	
	</td>
</tr>
</table>

<div class="titulo_grupo">Dados Comerciais:</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>   
	<td width="100%">

		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td width="33%">
				Representante Comercial:
				<div><? echo $row_prospeccao['usuario_responsavel']; ?></div>
				</td>
				
				<td width="34%">
				Tipo de Prospect:
				<div><?php if($row_prospeccao['ativo_passivo']=="a"){echo "ativo";} if($row_prospeccao['ativo_passivo']=="p"){echo "passivo";} ?></div>
				</td>
				
				<td width="33%">
				Ramo de Atividade:
				<div><?php echo $row_prospeccao['ramo_de_atividade']; ?></div>
				</td>
			</tr>
		</table>
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td width="33%">
				Indicação:
				<div>
				<?php 
				if($row_prospeccao['indicacao'] == "co"){echo "Contador";}
				else if($row_prospeccao['indicacao'] == "cl"){echo "Cliente";}
				else if($row_prospeccao['indicacao'] == "fu"){echo "Funcionário";}
				else if($row_prospeccao['indicacao'] == "cs"){echo "Colaborador Success";}
				else if($row_prospeccao['indicacao'] == "te"){echo "Terceiros";}
				?>
				</div>
				</td>
				
				<td width="34%">
				Indicador:
				<div>
				<?php 
				if($row_prospeccao['indicador'] == "co"){echo "Contador";}
				else if($row_prospeccao['indicador'] == "cl"){echo "Cliente";}
				else if($row_prospeccao['indicador'] == "fu"){echo "Funcionário";}
				else if($row_prospeccao['indicador'] == "cs"){echo "Colaborador Success";}
				else if($row_prospeccao['indicador'] == "te"){echo "Terceiros";}
				?>
				</div>
				</td>
				
				<td width="33%">
				Nome do Indicador:
				<div><?php echo $row_prospeccao['indicado_por']; ?></div>
				</td>
			</tr>
		</table>

		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td width="50%">
				Necessidades/Interesses do cliente:
				<div><?php echo funcao_consulta_modulo_array($row_prospeccao['necessidades']); ?></div>
				</td>
				
				<td width="50%">
				Nível de Interesse:
				<div>
				<?php 
				if($row_prospeccao['nivel_interesse'] == "a"){echo "Alto";}
				else if($row_prospeccao['nivel_interesse'] == "m"){echo "Médio";}
				else if($row_prospeccao['nivel_interesse'] == "b"){echo "Baixo";}
				else if($row_prospeccao['nivel_interesse'] == "n"){echo "Nenhum";}
				?>
				</div>
				</td>
			</tr>
		</table>
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td width="33%">
				Valor da Proposta/Orçamento:
				<div><?php echo $row_prospeccao['proposta_valor']; ?> </div>
				</td>
				
				<td width="34%">
				Recursos da Proposta/Orçamento:
				<div><?php echo $row_prospeccao['proposta_recursos']; ?></div>
				</td>
				
				<td width="33%">
				Validade da Proposta/Orçamento:
				<div><?php echo $row_prospeccao['proposta_validade']; ?></div>
				</td>
			</tr>
		</table>
		
	</td>
</tr>
</table>

<div class="titulo_grupo">Dados da Concorrência:</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>   
	<td width="100%">

		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td width="33%">
				Possui Sistema:
				<div>
				<?php 
				if($row_prospeccao['sistema_possui']=="s"){echo "Sim";} 
				else if($row_prospeccao['sistema_possui']=="n"){echo "Não";}
				?> 
				</div>
				</td>
				
				<td width="34%">
				Nome:
				<div>...</div>
				</td>
				
				<td width="33%">
				Empresa Representante:
				<div>...</div>
				</td>
			</tr>
		</table>
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td width="33%">
				Nível de utilização:
				<div>
				<?php 
				if($row_prospeccao['sistema_nivel_utilizacao'] == "a"){echo "Alto";}
				else if($row_prospeccao['sistema_nivel_utilizacao'] == "m"){echo "Médio";}
				else if($row_prospeccao['sistema_nivel_utilizacao'] == "b"){echo "Baixo";}
				else if($row_prospeccao['sistema_nivel_utilizacao'] == "n"){echo "Não implantado";}
				?>
				</div>
				</td>
				
				<td width="34%">
				Nível de satisfação:
				<div>
				<?php 
				if($row_prospeccao['sistema_nivel_satisfacao'] == "a"){echo "Alto";}
				else if($row_prospeccao['sistema_nivel_satisfacao'] == "m"){echo "Médio";}
				else if($row_prospeccao['sistema_nivel_satisfacao'] == "b"){echo "Baixo";}
				else if($row_prospeccao['sistema_nivel_satisfacao'] == "i"){echo "Insatisfeito";}
				?>
				</div>
				</td>
				
				<td width="33%">
				Motivo de satisfação/insatisfação:
				<div><?php echo $row_prospeccao['sistema_nivel_motivo']; ?></div>
				</td>
			</tr>
		</table>
	
	</td>
</tr>
</table>

<div class="titulo_grupo">Dados da Prospecção:</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>   
	<td width="100%">

		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td width="25%">
				Data/hora criação:
				<div><? echo date('d-m-Y  H:i:s', strtotime($row_prospeccao['data_prospeccao'])); ?></div>
				</td>
				
				<td width="25%">
				Última visita:
				<div>...</div>
				</td>
				
				<td width="25%">
				Situação:
				<div><?php echo $row_prospeccao['situacao']; ?></div>
				</td>
				
				
				<td width="25%">
				Status:
				<div><?php echo $row_prospeccao['status']; ?></div>
				</td>
			</tr>
		</table>
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td width="100%">
				3 Últimos contatos:
				<? if($totalRows_prospeccao_contato > 0){ ?>
					<? do { ?>
					<div><? echo date('d-m-Y H:i', strtotime($row_prospeccao_contato['data'])); ?> - <? echo $row_prospeccao_contato['responsavel']; ?> - <? echo $row_prospeccao_contato['descricao']; ?></div>
					<?php } while ($row_prospeccao_contato = mysql_fetch_assoc($prospeccao_contato)); ?>
				<? } ?>
				</td>
			</tr>
		</table>
	
	</td>
</tr>
</table>

<div class="titulo_grupo">Dados da Visita:</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>   
	<td width="100%">

		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td width="20%">
				Agendamento:
				<div>...</div>
				</td>
				
				<td width="20%">
				Contato:
				<div>...</div>
				</td>
				
				<td width="20%">
				Data:
				<div><? echo date('d-m-Y', strtotime($row_agenda['data_inicio'])); ?></div>
				</td>
				
				<td width="20%">
				Hora inicial:
				<div><? echo date('H:i', strtotime($row_agenda['data_inicio'])); ?></div>
				</td>
				
				<td width="20%">
				Hora final:
				<div><? echo date('H:i', strtotime($row_agenda['data'])); ?></div>
				</td>
			</tr>
		</table>
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td width="100%">
				Descrição da visita:
				<div><?php echo $row_agenda['descricao']; ?></div>
				</td>
			</tr>
		</table>
	
	</td>
</tr>
</table>

<div class="caixa_texto" style="margin-top: 10px; margin-bottom: 40px;">
A Success Sistemas possui mais 11 anos na área de desenvolvimento de softwares comerciais, com mais de 500 cópias comercializadas em mais de 20 cidades de Minas Gerais, Distrito Federal e Goiás, tendo representantes em Unai, Brasilândia, João Pinheiro, Vazante, Cristalina e Araxá. Os nossos softwares estão com o TEF homologado e autorizado pelo DICAT.
</div>

<table cellspacing=0 cellpadding=0 width='100%' class="bordatransparente">
  <tr>
  
    <td width="50%" align="center" valign="top">
    <br>
    ______________________________________________
    <br>
    Visto do Cliente
    </td>
    
    <td width="50%" align="center" valign="top">
    <br>
    ______________________________________________
    <br>
    Success Sistemas & Inf. Ltda
    </td>
    
  </tr>
</table>

</body>
</html>
<?php
mysql_free_result($usuario);
mysql_free_result($prospeccao);
mysql_free_result($agenda);
mysql_free_result($prospeccao_formulario);
mysql_free_result($prospeccao_contato);
?>