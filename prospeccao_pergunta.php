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

// id_prospeccao -----------
$id_prospeccao = "-1";
if (isset($_GET['id_prospeccao'])) {
  $id_prospeccao = $_GET['id_prospeccao'];
}
// fim - id_prospeccao -----------

// indice -------------------
$indice = "-1";
if (isset($_GET['indice'])) {
  $indice = $_GET['indice'];
  $indice = $indice -1;
}
// fim - indice -------------

if($id_prospeccao == "-1" or $indice == "-1"){
	echo "O questionario ou a pergunta não existe.";
	exit;
}

// pergunta
mysql_select_db($database_conexao, $conexao);
$query_prospeccao_pergunta = sprintf("
SELECT * 
FROM prospeccao_pergunta 
LIMIT %s, 1
", GetSQLValueString($indice, "int")); // LIMIT $indice, 1 indice, registros por página
$prospeccao_pergunta = mysql_query($query_prospeccao_pergunta, $conexao) or die(mysql_error());
$row_prospeccao_pergunta = mysql_fetch_assoc($prospeccao_pergunta);
$totalRows_prospeccao_pergunta = mysql_num_rows($prospeccao_pergunta);
// fim - pergunta

// pergunta (navegação)
mysql_select_db($database_conexao, $conexao);
$query_prospeccao_pergunta_navegacao = "
SELECT IdProspeccaoPergunta 
FROM prospeccao_pergunta 
ORDER BY IdProspeccaoPergunta ASC";
$prospeccao_pergunta_navegacao = mysql_query($query_prospeccao_pergunta_navegacao, $conexao) or die(mysql_error());
$row_prospeccao_pergunta_navegacao = mysql_fetch_assoc($prospeccao_pergunta_navegacao);
$totalRows_prospeccao_pergunta_navegacao = mysql_num_rows($prospeccao_pergunta_navegacao);
// fim - pergunta (navegação)

// resposta
mysql_select_db($database_conexao, $conexao);
$query_prospeccao_resposta = sprintf("
									   SELECT * 
									   FROM prospeccao_resposta 
									   WHERE IdProspeccaoPergunta = %s 
									   ORDER BY IdProspeccaoResposta ASC", 
									   GetSQLValueString($row_prospeccao_pergunta['IdProspeccaoPergunta'], "int"));
$prospeccao_resposta = mysql_query($query_prospeccao_resposta, $conexao) or die(mysql_error());
$row_prospeccao_resposta = mysql_fetch_assoc($prospeccao_resposta);
$totalRows_prospeccao_resposta = mysql_num_rows($prospeccao_resposta);
// fim - resposta

// insert/update prospeccao_participacao
if ( (isset($_POST["acao"])) && ($_POST["acao"] == "responder") ) {
		
	// nenhuma alternativa selecionada
	if(!count(@$_POST['opcao']) ){
		$opcao_atual = array('');
	}
	// fim - nenhuma alternativa selecionada
	
	// pelo menos uma alternativa
	else {
		$opcao_atual = @$_POST['opcao'];		
	}
	// fim - pelo menos uma alternativa
	
	$linha = "";
	
	// opcao (array)
	foreach ($opcao_atual as $valor_atual) {
		
		// prospeccao_participacao_atual
		mysql_select_db($database_conexao, $conexao);
		$query_prospeccao_participacao_atual = sprintf("
		SELECT descricao
		FROM prospeccao_resposta 
		WHERE IdProspeccaoResposta = %s", 
		GetSQLValueString($valor_atual, "int"));
		$prospeccao_participacao_atual = mysql_query($query_prospeccao_participacao_atual, $conexao) or die(mysql_error());
		$row_prospeccao_participacao_atual = mysql_fetch_assoc($prospeccao_participacao_atual);
		$totalRows_prospeccao_participacao_atual = mysql_num_rows($prospeccao_participacao_atual);
		// fim - prospeccao_participacao_atual
					
		// linha
		$linha .= "(";
		$linha .= sprintf("%s", GetSQLValueString($row_prospeccao['id'], "int"));
		$linha .= ", ";
		$linha .= sprintf("%s", GetSQLValueString($row_prospeccao_pergunta['IdProspeccaoPergunta'], "int"));
		$linha .= ", ";
		$linha .= sprintf("%s", GetSQLValueString(@$valor_atual, "int"));
		$linha .= ", ";
		$linha .= sprintf("%s", GetSQLValueString($row_prospeccao_participacao_atual['descricao'], "text"));
		$linha .= ", ";
		$linha .= sprintf("%s", GetSQLValueString(@$_POST['campo_texto'], "text"));			
		$linha .= ", ";
		$linha .= sprintf("%s", GetSQLValueString(date('Y-m-d H:i:s'), "date"));
		$linha .= "), ";
		// fim - linha
		
		mysql_free_result($prospeccao_participacao_atual);
		
	}
	// fim - opcao (array)
	
	$linha = substr($linha, 0, -2);
	
	// delete
	$deleteSQL = sprintf("
						 DELETE FROM prospeccao_participacao 
						 WHERE id_prospeccao=%s and IdProspeccaoPergunta=%s", 
						 GetSQLValueString($row_prospeccao['id'], "int"),
						 GetSQLValueString($row_prospeccao_pergunta['IdProspeccaoPergunta'], "int"));
	
	mysql_select_db($database_conexao, $conexao);
	$Result_delete = mysql_query($deleteSQL, $conexao) or die(mysql_error());
	// fim - delete

	// insert
	$insertSQL = "INSERT INTO prospeccao_participacao (id_prospeccao, IdProspeccaoPergunta, IdProspeccaoResposta, descricao, campo_texto, data) VALUES $linha";	
	mysql_select_db($database_conexao, $conexao);
	$Result_insert = mysql_query($insertSQL, $conexao) or die(mysql_error());
	// fim - insert
		
	// redireciona
	$updateGoTo = "prospeccao_pergunta.php";
	if (isset($_SERVER['QUERY_STRING'])) {
	$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
	$updateGoTo .= $_SERVER['QUERY_STRING'];
	}
	echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $updateGoTo);
	// fim - redireciona
	
	exit;
	
}
// fim - insert/update prospeccao_participacao

$participacao_status = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Questionário (<?php echo $row_prospeccao['id']; ?>)</title>

<link rel="stylesheet" href="css/suporte.css" type="text/css" media="screen" />

<style>
/* erro de validação */
label.error { color: #C00; display: none; font-weight: bold; }	
/* fim - erro de validação */

.paginacao_botao {
	margin-left: 10px; 
	margin-right: 10px; 
	border: 0px; 
	background-color: #5c9ccc; 
	color: #FFF; 
	font-weight: bold; 
	padding: 5px;
}
.paginacao_botao:hover {
	background-color: #dfeffc;
	color: #000;
}
.paginacao_botao_ativo {
	margin-left: 10px; 
	margin-right: 10px; 
	border: 0px; 
	background-color: #000; 
	color: #FFF; 
	font-weight: bold; 
	padding: 5px;
}
</style>

<script type="text/javascript" src="js/jquery.js"></script>

<script src="js/jquery.metadata.js" type="text/javascript"></script>
<script type="text/javascript" src="js/jquery.validate.js"></script>

<script type="text/javascript"> 
function myOnComplete() { return true; }
$.metadata.setType("attr", "validate");

$(document).ready(function() {
						   
	// validação
	$("#form").validate({
		rules: {
			
			'opcao[]': "required",
			
			<? if($row_prospeccao_pergunta['tipo']=="s"){ // nenhuma alternativa ?> 
			campo_texto: "required",
			<? } ?>
			
			acao: "required"
			
		},
		onkeyup: false
	});
	// fim - validação

});
</script>
</head>

<body>

<div class="div_solicitacao_linhas">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Prospecção número: <?php echo $row_prospeccao['id']; ?>
		</td>

		<td style="text-align: right">
		&lt;&lt; <a href="prospeccao_editar.php?id_prospeccao=<?php echo $_GET['id_prospeccao']; ?>" target="_top">Voltar</a>
        <a href="prospeccao_pergunta.php?id_prospeccao=<?php echo $_GET['id_prospeccao']; ?>&indice=<? echo $_GET['indice']; ?>" target="_top">[ Tela Cheia ]</a> 
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

<!-- prospeccao_questionario -->
<? if($indice >= 0 and $indice < $totalRows_prospeccao_pergunta_navegacao){ ?>

<!-- pergunta -->
<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
		Pergunta: <strong><?php echo $row_prospeccao_pergunta['descricao']; ?></strong>
		</td>
	</tr>
</table>
</div>
<!-- fim - pergunta -->

<!-- observacao -->
<?php if($row_prospeccao_pergunta['observacao']!=""){ ?>
<div class="div_solicitacao_linhas4">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
    <tr>
        <td>
        <?php echo $row_prospeccao_pergunta['observacao']; ?>
        </td>
    </tr>
</table>
</div>
<? } ?>
<!-- fim - observacao -->


<!-- resposta -->
<form id="form" name="form" action="prospeccao_pergunta.php?id_prospeccao=<?php echo $_GET['id_prospeccao']; ?>&indice=<?php echo $_GET['indice']; ?>" method="post" enctype="multipart/form-data">

    <input type="hidden" id="acao" name="acao" value="responder" />
    
    <div class="div_solicitacao_linhas4">
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: left">
                
                <!-- alternativas ************************************************************************************************************************************* -->
                <? if($totalRows_prospeccao_resposta > "0"){ ?>
                
                <? if($row_prospeccao_pergunta['tipo']=="r"){ // uma alternativa ?> 
                	<label for="opcao[]" class="error"><div style="margin-bottom: 5px;">Selecione uma das alternativas disponíveis<br></div></label>
				<? } ?>
                
                <? if($row_prospeccao_pergunta['tipo']=="c"){ // várias alternativas ?> 
                	<label for="opcao[]" class="error"><div style="margin-bottom: 5px;">Selecione uma ou mais das alternativas disponíveis<br></div></label>
				<? } ?>
                
                <fieldset style="border: 0px; padding: 0px; margin: 0px;">
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
					
					if($totalRows_prospeccao_participacao>0){
						$participacao_status = 1;
					}
                    // fim - prospeccao_participacao
                    ?>
                                                    
                    <div style="padding-bottom: 5px; padding-top: 5px;">
                    	<? if($row_prospeccao_pergunta['tipo']=="r"){ // uma alternativa ?> 
                            <input type="radio" id="opcao[]" name="opcao[]" value="<?php echo $row_prospeccao_resposta['IdProspeccaoResposta']; ?>" 
                            style="border: 0px; padding: 0px; margin: 0px;" 
							<?php if (!(strcmp($row_prospeccao_resposta['IdProspeccaoResposta'], $row_prospeccao_participacao['IdProspeccaoResposta']))) {echo "checked";} ?>
                            /> 
                        <? } ?>
                        
                        <? if($row_prospeccao_pergunta['tipo']=="c"){ // várias alternativas ?> 
                            <input type="checkbox" id="opcao[]" name="opcao[]" value="<?php echo $row_prospeccao_resposta['IdProspeccaoResposta']; ?>" 
                            style="border: 0px; padding: 0px; margin: 0px;" 
                            <?php if (!(strcmp($row_prospeccao_resposta['IdProspeccaoResposta'], $row_prospeccao_participacao['IdProspeccaoResposta']))) {echo "checked";} ?> /> 
                        <? } ?> 
                                               
                        <?php echo $row_prospeccao_resposta['descricao']; ?>
                    </div>
                    
                    <hr style="border: 1px solid #CCC;" />
                    
                    <? mysql_free_result($prospeccao_participacao); ?>
                    
                <?php } while ($row_prospeccao_resposta = mysql_fetch_assoc($prospeccao_resposta)); ?>
                </fieldset>
                
                <? } ?>                
                <!-- fim - alternativas ******************************************************************************************************************************* -->
    
    
                <!-- campo_texto ************************************************************************************************************************************** -->
                <?php if($row_prospeccao_pergunta['campo_texto'] =="s"){ ?>
                
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
					
					if($totalRows_prospeccao_participacao>0){
						$participacao_status = 1;
					}
                    // fim - prospeccao_participacao
                    ?>
                                        
                    <div style="text-align: center;">
                    	<label class="error" for="campo_texto">Insira algum contéudo no campo para continuar<br></label>
                    	<textarea id="campo_texto" name="campo_texto" style="width: 610px; height: 50px; margin-top: 5px; margin-bottom: 5px; padding: 5px; border: 1px solid #CCC;"><? echo $row_prospeccao_participacao['campo_texto']; ?></textarea>
                    </div>
                    
                    <? mysql_free_result($prospeccao_participacao); ?>
                    
                <? } ?>    
                <!-- fim - campo_texto ******************************************************************************************************************************** -->
    
            </td>
        </tr>
    </table>
    </div>
       
    <div class="div_solicitacao_linhas4">
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td>
            <!-- botão -->
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td align="left" width="200">
				<input type="submit" name="responder" id="responder" value="Responder" class="botao_geral2" style="width: 100px" />
                </td>
                
                <td align="center">
                <? if($participacao_status == "1"){ ?>
                	<span style="font-size: 14px; font-weight: bold; color: #090;">Pergunta respondida!</span>
                <? } ?>
                </td>
                
                <td align="right" width="200">
                <a href="prospeccao_questionario.php?id_prospeccao=<?php echo $_GET['id_prospeccao']; ?>" target="_self" id="botao_geral2" style="float: right;">Listar perguntas</a>
                </td>
                
              </tr>
            </table>
            <!-- fim - botão -->
            </td>
        </tr>
    </table>
    </div>

</form>
<!-- fim - resposta -->


<!-- respostas (navegação) -->
<div class="div_solicitacao_linhas4">
<table width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: center; padding: 10px;">
        <? $contador_pergunta = 0; ?>
		<?php do { ?>
        <? $contador_pergunta = $contador_pergunta + 1; ?>
        	
            <? if($contador_pergunta != $indice+1){ // se não é a pergunta atual ?>
                <a href="prospeccao_pergunta.php?id_prospeccao=<?php echo $row_prospeccao['id']; ?>&indice=<?php echo $contador_pergunta; ?>" style=" text-decoration: none;">
                <span class="paginacao_botao">
                <?php echo $contador_pergunta; ?></span>
                </a>
			<? } else { // se é a pergunta atual ?>
                <span class="paginacao_botao_ativo">
                <?php echo $contador_pergunta; ?></span>            
            <? } ?>
        <?php } while ($row_prospeccao_pergunta_navegacao = mysql_fetch_assoc($prospeccao_pergunta_navegacao)); ?>
        </td>
	</tr>
</table>
</div>
<!-- fim - respostas (navegação) -->

<? } else { ?>

O questionário ou a pergunta não existe.

<? } ?>
<!-- fim - prospeccao_questionario -->

</body>

</html>
<?php
mysql_free_result($prospeccao);
mysql_free_result($usuario);
mysql_free_result($prospeccao_pergunta);
mysql_free_result($prospeccao_pergunta_navegacao);
mysql_free_result($prospeccao_resposta);
?>