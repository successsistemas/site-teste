<?php require('restrito.php'); ?>
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

// agenda
$where = "-1";
if (isset($_POST['where'])) {
	$where = stripslashes($_POST["where"]); // stripslashes: serve para tirar a "barra invertida" que o servidor insere no POST como segurança
}

mysql_select_db($database_conexao, $conexao);
$query_agenda = "
SELECT 

agenda.id_agenda, agenda.data, agenda.data_inicio, agenda.id_usuario_responsavel, agenda.descricao, 

suporte.id AS suporte_id,
suporte.empresa AS suporte_empresa, 
suporte.data_suporte AS suporte_data_suporte, 

prospeccao.id AS prospeccao_id,
prospeccao.nome_razao_social AS prospeccao_nome_razao_social, 
prospeccao.data_prospeccao AS prospeccao_data_prospeccao, 

venda.id AS venda_id,  
venda.empresa AS venda_empresa, 
venda.data_venda AS venda_data_venda

FROM agenda 

LEFT JOIN suporte ON (suporte.id = agenda.id_suporte)
LEFT JOIN prospeccao ON (prospeccao.id = agenda.id_prospeccao)
LEFT JOIN venda ON (venda.id = agenda.id_venda_treinamento or venda.id = agenda.id_venda_implantacao)

WHERE $where and (agenda.status = 'a' or agenda.status = 'g')
ORDER BY agenda.data ASC";
$agenda = mysql_query($query_agenda, $conexao) or die(mysql_error());
$row_agenda = mysql_fetch_assoc($agenda);
$totalRows_agenda = mysql_num_rows($agenda);
// fim - agenda

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
<style>
td {
	font-family:"Courier New", Courier, monospace;
	font-size: 12px;
	text-align:left;
}
.linha1 {
	background-color: #EEEEEE;
}
.linha2 {
	background-color: #FFFFFF;
}
.div_td {
	padding-bottom: 2px;
	padding-top: 2px;
	padding-left: 15px;
	padding-right: 5px;
}
.hr_linha {
	border-width: 0;
	height: 0;
	border-top: 1px solid #CCC;;
	margin-top: 5;
	margin-bottom: 5;
}
</style>
</head>

<body>

<!-- titulo -->
<div style="border: 1px solid #CCC; padding: 5px; margin-bottom: 10px; font-family: Verdana, Geneva, sans-serif;">

	<div style="text-align: center; font-weight: bold; font-size: 14px;">
	    Agenda Geral (<? echo $totalRows_agenda; ?>)
    </div>
      
</div>
<!-- fim - titulo -->

<table width="100%" border="0" cellpadding="0" cellspacing="0">

	<!-- cabeçalho -->
    <tr>
	<? $contador_relatorio_campos = 0; ?>
	<? foreach($_POST["relatorio_campos"] as $relatorio_campos){ // lista os campos selecionados para o relatório ?>
    		<? $contador_relatorio_campos = $contador_relatorio_campos + 1; // contador de campos selecionados  ?>
			<td>
			<div class="div_td">
			<?
            if($relatorio_campos=="id"){echo "Número";}
            if($relatorio_campos=="data_modulo"){echo "Emissão";}
            if($relatorio_campos=="data_inicio"){echo "Início";}
            if($relatorio_campos=="data"){echo "Fim";}
            if($relatorio_campos=="id_usuario_responsavel"){echo "Responsável";}
			if($relatorio_campos=="empresa"){echo "Empresa";}
            if($relatorio_campos=="descricao"){echo "Descrição";}
            ?>
			</div>
			</td>
	<? } // fim - lista os campos selecionados para o relatório  ?>       
    </tr>

    <tr>
      	<td colspan="<? echo $contador_relatorio_campos; ?>">
		<div style=" padding:2px; margin-top: 5px; margin-bottom: 5px;; background-color:#CCCCCC; ">
		</div>
		</td>
    </tr>
	<!-- fim - cabeçalho -->

	<!-- registros -->
	<? $contador = 0; ?>
    <?php do { ?>
			<? $contador = $contador+1; ?>
            <tr class="<? if (($contador % 2)==0){echo "linha1";}else{echo "linha2";} ?>">
        
            <? $contador_relatorio_campos = 0;
            foreach($_POST["relatorio_campos"] as $relatorio_campos){ // lista os campos selecionados para o relatório 
                    $contador_relatorio_campos = $contador_relatorio_campos + 1; // contador de campos selecionados ?>
        
                    <td>
					<div class="div_td">
                    <?
					$campo = "";
					
					// id
					if($relatorio_campos=="id"){
						if($row_agenda['suporte_id']!=NULL){
							$campo = $row_agenda['suporte_id'];
						} if($row_agenda['prospeccao_id']!=NULL){
							$campo = $row_agenda['prospeccao_id'];
						} else if($row_agenda['venda_data_venda']!=NULL){
							$campo = $row_agenda['venda_id'];
						}
					}
					// fim - id

					// data_modulo
					if($relatorio_campos=="data_modulo"){
						if($row_agenda['suporte_id']!=NULL){
							$campo = $row_agenda['suporte_data_suporte'];
						} else if($row_agenda['prospeccao_id']!=NULL){
							$campo = $row_agenda['prospeccao_data_prospeccao'];
						} else if($row_agenda['venda_data_venda']!=NULL){
							$campo = $row_agenda['venda_data_venda'];
						}
						$campo = date('d-m-Y H:i', strtotime($campo));
					}
					// fim - data_modulo
					
					// data_inicio
					if($relatorio_campos=="data_inicio"){
						$campo = $row_agenda['data_inicio'];
						$campo = date('d-m-Y H:i', strtotime($campo));
					}
					// fim - data_inicio
					
					// data
					if($relatorio_campos=="data"){
						$campo = $row_agenda['data'];
						$campo = date('d-m-Y H:i', strtotime($campo));
					}
					// fim - data
					
					// id_usuario_responsavel
					if($relatorio_campos=="id_usuario_responsavel"){
						$campo = $row_agenda['id_usuario_responsavel'];
						
						// busca usuario_responsavel selecionado
						mysql_select_db($database_conexao, $conexao);
						$query_usuario_responsavel_selecionado = sprintf("
																		 SELECT nome 
																		 FROM usuarios 
																		 WHERE IdUsuario = %s", 
																		 GetSQLValueString($campo, "int"));
						$usuario_responsavel_selecionado = mysql_query($query_usuario_responsavel_selecionado, $conexao) or die(mysql_error());
						$row_usuario_responsavel_selecionado = mysql_fetch_assoc($usuario_responsavel_selecionado);
						$totalRows_usuario_responsavel_selecionado = mysql_num_rows($usuario_responsavel_selecionado);
						$campo = $row_usuario_responsavel_selecionado['nome'];
						mysql_free_result($usuario_responsavel_selecionado);
						// fim - busca usuario_responsavel selecionado
					}
					// fim - id_usuario_responsavel

					// empresa
					if($relatorio_campos=="empresa"){
						if($row_agenda['suporte_id']!=NULL){
							$campo = $row_agenda['suporte_empresa'];
						} else if($row_agenda['prospeccao_id']!=NULL){
							$campo = $row_agenda['prospeccao_nome_razao_social'];
						} else if($row_agenda['venda_data_venda']!=NULL){
							$campo = $row_agenda['venda_empresa'];
						}
					}
					// fim - empresa
					
					// descricao
					if($relatorio_campos=="descricao"){
						$campo = $row_agenda['descricao'];
					}
					// fim - descricao

					echo $campo;
					?>
					</div>
					</td>
        
            <? } // fim - lista os campos selecionados para o relatório  ?>  
            </tr>
	<?php } while ($row_agenda = mysql_fetch_assoc($agenda)); ?>
	<!-- fim - registros -->
    
</table>
</body>
</html>
<?
mysql_free_result($usuario);
mysql_free_result($agenda);
mysql_close($conexao);
?>