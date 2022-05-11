<?php require_once('restrito.php'); ?>
<?php require_once('Connections/conexao.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

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
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
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
	$colname_prospeccao = $_GET["id_prospeccao"];
}
mysql_select_db($database_conexao, $conexao);
$query_prospeccao = sprintf("
							SELECT * 
							FROM prospeccao 
							WHERE id = %s
							ORDER BY id ASC", GetSQLValueString($colname_prospeccao, "int"));
$prospeccao = mysql_query($query_prospeccao, $conexao) or die(mysql_error());
$row_prospeccao = mysql_fetch_assoc($prospeccao);
$totalRows_prospeccao = mysql_num_rows($prospeccao);
// fim - prospeccao

// prospeccao_pergunta
mysql_select_db($database_conexao, $conexao);
$query_prospeccao_pergunta = "SELECT * FROM prospeccao_pergunta ORDER BY IdProspeccaoPergunta ASC";
$prospeccao_pergunta = mysql_query($query_prospeccao_pergunta, $conexao) or die(mysql_error());
$row_prospeccao_pergunta = mysql_fetch_assoc($prospeccao_pergunta);
$totalRows_prospeccao_pergunta = mysql_num_rows($prospeccao_pergunta);
// fim - prospeccao_pergunta

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Questionário (<?php echo $row_prospeccao['id']; ?>)</title>
<link rel="stylesheet" href="css/suporte.css" type="text/css" media="screen" />
<script type="text/javascript" src="js/jquery.js"></script>
</head>
<style>
td {
	font-size: 12px;
	text-align:left;
}
</style>
<body>

<div style="border: 1px solid #CCC; padding: 5px; margin-bottom: 10px; font-family: Verdana, Geneva, sans-serif;">
	<div style="text-align: center; font-weight: bold; font-size: 14px;">
	    Prospecção n° <?php echo $row_prospeccao['id']; ?>
	</div>
</div>


<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
        <td style="text-align:left">
		<span class="label_solicitacao">Tipo de prospect: </span>
        <?php if($row_prospeccao['ativo_passivo']=="a"){echo "ativo";} if($row_prospeccao['ativo_passivo']=="p"){echo "passivo";} ?>
        </td>
        
        <td style="text-align: right" width="350">        
		<span class="label_solicitacao">Indicado por: </span>
        <?php echo $row_prospeccao['indicado_por']; ?>
        </td>
	</tr>
</table>
</div>


<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
        <td style="text-align:left">
        <span class="label_solicitacao">Cliente: </span>
        <?php echo $row_prospeccao['nome_razao_social']; ?>
        
        <br>
        
        <span class="label_solicitacao">Fantasia: </span>
        <?php echo $row_prospeccao['fantasia']; ?> 
        
        |        
        
        <span class="label_solicitacao">Praça: </span>
        <?php echo $row_prospeccao['praca']; ?>
        </td>
        
        <td style="text-align: right" width="350">
        <span class="label_solicitacao">CNPJ: </span>
        <?php echo $row_prospeccao['cpf_cnpj']; ?> 
        
        <br>
        
        <span class="label_solicitacao">Insc. Est.: </span>
        <?php echo $row_prospeccao['rg_inscricao']; ?>
        </td>
	</tr>
</table>
</div>

<br>

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
    
		<td style="text-align:left; vertical-align: top">
		<span class="label_solicitacao">CEP: </span>
		<?php echo $row_prospeccao['cep']; ?> | 
        
		<span class="label_solicitacao">Logradouro: </span>
		<?php echo $row_prospeccao['endereco']; ?> <?php echo $row_prospeccao['endereco_numero']; ?> <?php echo $row_prospeccao['endereco_complemento']; ?>
        
        <br>
		<span class="label_solicitacao">Bairro: </span>
		<?php echo $row_prospeccao['bairro']; ?> | 
        
		<span class="label_solicitacao">Cidade/UF: </span>
		<?php echo $row_prospeccao['cidade']; ?>/<?php echo $row_prospeccao['uf']; ?>

		</td>
        
        <td style="text-align: right">
		<span class="label_solicitacao">Telefone: </span>
		<?php echo $row_prospeccao['telefone']; ?>
        
        | 
		
        <span class="label_solicitacao">Celular: </span>
		<?php echo $row_prospeccao['celular']; ?> 
        
        <br>       
		<span class="label_solicitacao">Data/hora criação: </span>
        <? echo date('d-m-Y  H:i:s', strtotime($row_prospeccao['data_criacao'])); ?><br>
        </td>
        
	</tr>
</table>
</div>

<br>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>

		<td style="text-align:left">
		<span class="label_solicitacao">Responsável por TI: </span>
        <?php echo $row_prospeccao['responsavel_por_ti']; ?>        
        </td>
        
        <td style="text-align: right">
		<span class="label_solicitacao">Ramo de Atividade: </span>
        <?php echo $row_prospeccao['ramo_de_atividade']; ?>
		</td>
        
	</tr>
</table>
</div>


<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>

		<td style="text-align:left">
        <span class="label_solicitacao">Enquadramento Fiscal: </span>
        <?php if($row_prospeccao['enquadramento_fiscal']==""){ ?>   
	        <?php echo $row_prospeccao['enquadramento_fiscal_outro']; ?>
        <? } else { ?>
            <?php echo $row_prospeccao['enquadramento_fiscal']; ?>
		<? } ?>
        </td>
        
        <td style="text-align: right">
		<span class="label_solicitacao">Informações Fiscais: </span>
        Exige NFE: <?php if($row_prospeccao['exige_nfe']=="0"){echo "não";} if($row_prospeccao['exige_nfe']=="1"){echo "sim";} ?> | 
        Exige Cupom Fiscal: <?php if($row_prospeccao['exige_cupom_fiscal']=="0"){echo "não";} if($row_prospeccao['exige_cupom_fiscal']=="1"){echo "sim";} ?> | 
        Outras: <?php echo $row_prospeccao['exige_outro']; ?>
		</td>
        
	</tr>
</table>
</div>

<br>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>

		<td style="text-align:left">
		<span class="label_solicitacao">Contador: </span>
        <?php echo $row_prospeccao['contador']; ?>
        
        <br>
        
		<span class="label_solicitacao">Telefone (cont.): </span>
        <?php echo $row_prospeccao['contador_telefone']; ?> |
        
		<span class="label_solicitacao">E-mail (cont.): </span>
        <?php echo $row_prospeccao['contador_email']; ?>
        </td>
        
        <td style="text-align: right">
		<span class="label_solicitacao">Situação: </span>
        <?php echo $row_prospeccao['situacao']; ?> | 
		<span class="label_solicitacao">Status: </span>
        <?php echo $row_prospeccao['status']; ?>
		</td>
        
	</tr>
</table>
</div>

<hr style="border: 1px solid #CCC;">

<!-- questionario -->
<div>
<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align: left">
        <? if($totalRows_prospeccao_pergunta > 0){ ?>
        <? $contador_pergunta = 0; ?>
        
        <?php do { ?>
            <? $contador_pergunta = $contador_pergunta + 1; ?>
            
            <div style="margin-top: 10px; margin-bottom: 10px; text-align: justify">
            
            	<!-- pergunta -->
                <div style="font-weight: bold; padding-bottom: 5px; padding-top: 5px;">
                <? echo $contador_pergunta; ?>) 
                	<span style="color: #000;"><?php echo $row_prospeccao_pergunta['descricao']; ?></span>
                </div>
                <!-- fim - pergunta -->
                
                
                <!-- resposta -->
                
					<?
                    // prospeccao_resposta
                    $colname_prospeccao_resposta = "-1";
                    if (isset($row_prospeccao_pergunta['IdProspeccaoPergunta'])) {
                        $colname_prospeccao_resposta = $row_prospeccao_pergunta['IdProspeccaoPergunta'];
                    }
                    mysql_select_db($database_conexao, $conexao);
                    $query_prospeccao_resposta = sprintf("
                                                         SELECT * 
                                                         FROM prospeccao_resposta 
                                                         WHERE IdProspeccaoPergunta = %s 
                                                         ORDER BY IdProspeccaoResposta ASC", 
                                                         GetSQLValueString($colname_prospeccao_resposta, "text"));
                    $prospeccao_resposta = mysql_query($query_prospeccao_resposta, $conexao) or die(mysql_error());
                    $row_prospeccao_resposta = mysql_fetch_assoc($prospeccao_resposta);
                    $totalRows_prospeccao_resposta = mysql_num_rows($prospeccao_resposta);
                    // fim - prospeccao_resposta             
                    ?>
    
                    <!-- questão fechada -->
                    <? if($totalRows_prospeccao_resposta > "0"){ ?>
                    <div style="padding: 5px;">
                    
                    <?php do { ?>
                    
                        <?
                        // prospeccao_participacao
                        mysql_select_db($database_conexao, $conexao);
                        $query_prospeccao_participacao = sprintf("
                        SELECT * 
                        FROM prospeccao_participacao 
                        WHERE 
                        id_prospeccao = %s and
                        IdProspeccaoPergunta = %s and
                        IdProspeccaoResposta = %s ", 
                        GetSQLValueString($row_prospeccao['id'], "int"), 
                        GetSQLValueString($row_prospeccao_pergunta['IdProspeccaoPergunta'], "int"),
                        GetSQLValueString($row_prospeccao_resposta['IdProspeccaoResposta'], "int"));
                        $prospeccao_participacao = mysql_query($query_prospeccao_participacao, $conexao) or die(mysql_error());
                        $row_prospeccao_participacao = mysql_fetch_assoc($prospeccao_participacao);
                        $totalRows_prospeccao_participacao = mysql_num_rows($prospeccao_participacao);
                        // fim - prospeccao_participacao
                        ?>
    
                        <?php if (!(strcmp($row_prospeccao_resposta['IdProspeccaoResposta'], $row_prospeccao_participacao['IdProspeccaoResposta']))) {?>
                            <div style="padding-bottom: 5px; padding-top: 5px; color: #090;">
                            (X) <?php echo $row_prospeccao_resposta['descricao']; ?>
                            </div>
                        <? } else { ?>
                            <div style="padding-bottom: 5px; padding-top: 5px;">
                            (&nbsp;&nbsp;) <?php echo $row_prospeccao_resposta['descricao']; ?>
                            </div>
                        <? } ?>
    
                        <? mysql_free_result($prospeccao_participacao); ?>
    
                    <?php } while ($row_prospeccao_resposta = mysql_fetch_assoc($prospeccao_resposta)); ?>
                    
                    </div>
                    <? } ?>                
                    <!-- fim - questão fechada -->

					<? mysql_free_result($prospeccao_resposta); ?>
    
                    <!-- questão aberta  -->
                    <?php if($row_prospeccao_pergunta['campo_texto'] =="s"){ ?>
					<div style="padding: 5px;">
                    
                        <?php if($row_prospeccao_pergunta['campo_texto_label'] !=""){ ?>
                            <div style="margin-top: 10px; margin-bottom: 5px; font-weight: bold;">
                                <?php echo $row_prospeccao_pergunta['campo_texto_label']; ?>
                            </div>
                        <? } ?>
    
                        <?
                        // prospeccao_participacao
                        mysql_select_db($database_conexao, $conexao);
                        $query_prospeccao_participacao = sprintf("
                        SELECT campo_texto 
                        FROM prospeccao_participacao 
                        WHERE 
                        id_prospeccao = %s and
                        IdProspeccaoPergunta = %s 
                        GROUP BY IdProspeccaoPergunta", 
                        GetSQLValueString($row_prospeccao['id'], "int"), 
                        GetSQLValueString($row_prospeccao_pergunta['IdProspeccaoPergunta'], "int"));
                        $prospeccao_participacao = mysql_query($query_prospeccao_participacao, $conexao) or die(mysql_error());
                        $row_prospeccao_participacao = mysql_fetch_assoc($prospeccao_participacao);
                        $totalRows_prospeccao_participacao = mysql_num_rows($prospeccao_participacao);
                        // fim - prospeccao_participacao
                        ?>
                                            
                        <div>
                            <? echo $row_prospeccao_participacao['campo_texto']; ?>
                        </div>
                        
                        <? mysql_free_result($prospeccao_participacao); ?>
					
                    </div>
                    <? } ?>    
                    <!-- fim - questão aberta -->
                                   
                <!-- fim - resposta -->
                
            </div>
            
            <hr style="border: 1px solid #CCC;">
            
        <?php } while ($row_prospeccao_pergunta = mysql_fetch_assoc($prospeccao_pergunta)); ?>
        
        <? } else { ?>
        Nenhuma pergunta disponível.
        <? } ?>
		</td>
	</tr>
</table>
</div>
<!-- fim - questionario -->

</body>
</html>
<?
mysql_free_result($usuario);
mysql_free_result($prospeccao);
mysql_free_result($prospeccao_pergunta);
?>