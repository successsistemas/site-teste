<?php require_once('restrito.php'); ?>
<?php require_once('Connections/conexao.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
require_once('funcao_converte_caracter.php');

function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      //$theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
	  $theValue = ($theValue != "") ? "'" . funcao_converte_caracter($theValue) . "'" : "NULL";
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8');
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
  $colname_prospeccao = $_GET['id_prospeccao'];
}
mysql_select_db($database_conexao, $conexao);
$query_prospeccao = sprintf("SELECT * FROM prospeccao WHERE id = %s", GetSQLValueString($colname_prospeccao, "int"));
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

<body>

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Prospecção número: <?php echo $row_prospeccao['id']; ?>
		</td>

		<td style="text-align: right">
		&lt;&lt; <a href="prospeccao_editar.php?id_prospeccao=<?php echo $_GET['id_prospeccao']; ?>" target="_top">Voltar</a> 
        <a href="prospeccao_questionario.php?id_prospeccao=<?php echo $_GET['id_prospeccao']; ?>" target="_top">[ Tela Cheia ]</a> 
        </td>
	</tr>
</table>
</div>


<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
		<strong>Questionário</strong> 
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
		Cliente: <?php echo $row_prospeccao['nome_razao_social']; ?>
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
        <span style="font-size: 11px; font-weight: bold; color: #999">
            Clique sobre a pergunta para respondê-la ou 
            <a href="prospeccao_pergunta.php?id_prospeccao=<?php echo $row_prospeccao['id']; ?>&indice=1" target="_self">
            <span style="color: #C00;">clique aqui para responder o questionário em seqüência</span></a>.
        </span>
		</td>
	</tr>
</table>
</div>

<!-- questionario -->
<div class="div_solicitacao_linhas4">
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
                <a href="prospeccao_pergunta.php?id_prospeccao=<?php echo $row_prospeccao['id']; ?>&indice=<?php echo $contador_pergunta; ?>">
                	<span style="color: #000;"><?php echo $row_prospeccao_pergunta['descricao']; ?></span>         
                </a>
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
<?php
mysql_free_result($prospeccao);
mysql_free_result($usuario);
mysql_free_result($prospeccao_pergunta);
?>