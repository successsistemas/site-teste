<?php require_once('../padrao_restrito.php'); ?>
<?php require_once('../../Connections/conexao.php'); ?>
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

// usuarios
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("SELECT * FROM usuarios WHERE usuario = %s", GetSQLValueString($colname_usuario, "text"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
// fim - usuarios

// geral_tipo_praca
$colname_praca = "-1";
if (isset($_GET['IdPraca'])) {
  $colname_praca = $_GET['IdPraca'];
}
mysql_select_db($database_conexao, $conexao);
$query_praca = sprintf("SELECT * FROM geral_tipo_praca WHERE IdPraca = %s", GetSQLValueString($colname_praca, "int"));
$praca = mysql_query($query_praca, $conexao) or die(mysql_error());
$row_praca = mysql_fetch_assoc($praca);
$totalRows_praca = mysql_num_rows($praca);
// fim - geral_tipo_praca

// executores (praças)
mysql_select_db($database_conexao, $conexao);
$query_executores = "SELECT * FROM da03 ORDER BY nome3 ASC";
$executores = mysql_query($query_executores, $conexao) or die(mysql_error());
$row_executores = mysql_fetch_assoc($executores);
$totalRows_executores = mysql_num_rows($executores);
// fim - executores (praças)

// insert - praca/executor
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "executores")) {

		mysql_select_db($database_conexao, $conexao);

		// se existe checkbox marcado
		if(isset( $_POST["codigo"] )) {

                $sql_delete_comandos = "";
		        // Faz loop pelos códigos passados via checkbox - só os selecionados
                foreach($_POST["codigo"] as $codigo) {

						// verifica os selecionados, um a um...
                        $sql_select = sprintf("SELECT * FROM geral_tipo_praca_executor WHERE IdPraca = %s and IdExecutor = %s", 
								GetSQLValueString($row_praca['IdPraca'], "int"),
								GetSQLValueString($codigo, "text"));
                        $Result_select = mysql_query($sql_select, $conexao) or die(mysql_error());
                        $row_sql_select = mysql_fetch_assoc($Result_select);
                        $totalRows_sql_select = mysql_num_rows($Result_select);
						
						// se atual ainda não está na tabela, então insere....
						if ($totalRows_sql_select == 0){
	        	                $sql_insert = sprintf("INSERT INTO geral_tipo_praca_executor (IdPraca, praca, IdExecutor) VALUES (%s,%s,%s)",
															GetSQLValueString($row_praca['IdPraca'], "int"),
															GetSQLValueString($row_praca['praca'], "text"),
															GetSQLValueString($codigo, "text"));
    		                    $Result_insert = mysql_query($sql_insert, $conexao) or die(mysql_error());
                        }
						// adiciona o atual para ser uma execção ao deletar
                        $sql_delete_comandos .= "IdExecutor <> '".$codigo. "' and ";
						mysql_free_result($Result_select);

                }
				// - fim loop

				$sql_delete = "DELETE FROM geral_tipo_praca_executor WHERE IdPraca = '".$row_praca['IdPraca']."' and ( ".$sql_delete_comandos;
				$sql_delete = substr($sql_delete, 0, -4)." )";
				$Result_delete = mysql_query($sql_delete, $conexao) or die(mysql_error());
		// - fim - se existe checkbox marcado

		// se NAO existe checkbox marcado
        } else {

                $sql = "DELETE FROM geral_tipo_praca_executor WHERE IdPraca = '".$row_praca['IdPraca']."'";
				$Result = mysql_query($sql, $conexao) or die(mysql_error());
        
        }
		// fim - se NAO ...

		// redireciona
		$insertGoTo = "listar_executor.php";
		if (isset($_SERVER['QUERY_STRING'])) {
			$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
			$insertGoTo .= $_SERVER['QUERY_STRING'];
		}
		header(sprintf("Location: %s", $insertGoTo));
		// fim - redireciona
}
// fim -  insert - praca/executor
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="content-language" content="pt-BR" />
<meta name="language" content="pt-BR" />
<meta name="robots" content="noindex,nofollow" />
<meta name="author" content="Juliano Martins - (34) 8805 3396" />
<meta name="copyright" content=" Copyright © Success Sistemas - Todos os direitos reservados." />
<title>Área do Parceiro - Success Sistemas</title>
<link href="../../css/guia_painel.css" rel="stylesheet" type="text/css">
<script src="../../js/jquery.js"></script>
</head>
<body>

<div class="cabecalho"><? require_once('../padrao_cabecalho.php'); ?></div>

<!-- corpo -->
<div class="corpo">
	<div class="texto"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
            
                <td class="padrao_esquerda"><? require_once('../padrao_esquerda.php'); ?></td>
                                
                <td class="padrao_centro">                
                
                <!-- titulo -->
                <div class="titulo">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="left">Tipo de Praça - Executores</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; <a href="listar.php">Tipo de Praça</a> &gt;&gt; Executores</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                
Praça: <strong><?php echo $row_praca['praca']; ?> - <?php echo $row_praca['estado']; ?></strong>
<br><br>

<form action="<?php echo $editFormAction; ?>" method="post">
<input type="hidden" id="IdPraca" value="<?php echo $row_praca['IdPraca']; ?>" />
<input type="hidden" name="MM_insert" value="executores" />

<input name="Salvar" type="submit" value="Salvar" />
<br><br>


<?php do { ?>

<?
$colname_geral_tipo_praca_executor = "-1";
if (isset($row_executores['codigo3'])) {
  $colname_geral_tipo_praca_executor = $row_executores['codigo3'];
}
$colname2_geral_tipo_praca_executor = "-1";
if (isset($_GET['IdPraca'])) {
  $colname2_geral_tipo_praca_executor = $_GET['IdPraca'];
}
mysql_select_db($database_conexao, $conexao);
$query_geral_tipo_praca_executor = sprintf("SELECT * FROM geral_tipo_praca_executor WHERE IdExecutor = %s and IdPraca = %s", GetSQLValueString($colname_geral_tipo_praca_executor, "text"),GetSQLValueString($colname2_geral_tipo_praca_executor, "int"));
$geral_tipo_praca_executor = mysql_query($query_geral_tipo_praca_executor, $conexao) or die(mysql_error());
$row_geral_tipo_praca_executor = mysql_fetch_assoc($geral_tipo_praca_executor);
$totalRows_geral_tipo_praca_executor = mysql_num_rows($geral_tipo_praca_executor);
?>

	<div style="text-align:left; padding-bottom: 10px; <? if($totalRows_geral_tipo_praca_executor > 0) { ?>color:#FF0000;<? } ?>">
	<input type="checkbox" name="codigo[]" value="<?php echo $row_executores['codigo3']; ?>" <? if($totalRows_geral_tipo_praca_executor > 0) { echo "checked=\"checked\""; } ?> />

		<?php echo $row_executores['codigo3']; ?> - <?php echo $row_executores['nome3']; ?>
	
	</div>

<? mysql_free_result($geral_tipo_praca_executor); ?>
<?php } while ($row_executores = mysql_fetch_assoc($executores)); ?>
</form>
                
                </div>
                
                 
                </td>
                
            </tr>
        </table>
  	</div>
</div>
<!-- fim - corpo -->

<div class="rodape"><? require_once('../padrao_rodape.php'); ?></div>

</body>
</html>
<?php 
mysql_free_result($usuario); 
mysql_free_result($praca);
mysql_free_result($executores);
?>
