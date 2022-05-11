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

// relatorio_tipo
$relatorio_tipo = "venda";
if( (isset($_POST["relatorio_tipo"])) && ($_POST['relatorio_tipo'] =="venda") ) {
	$relatorio_tipo = "venda";
} else if( (isset($_POST["relatorio_tipo"])) && ($_POST['relatorio_tipo'] =="venda_agenda_treinamento") ) {
	$relatorio_tipo = "venda_agenda_treinamento";
} else if( (isset($_POST["relatorio_tipo"])) && ($_POST['relatorio_tipo'] =="venda_agenda_implantacao") ) {
	$relatorio_tipo = "venda_agenda_implantacao";
}
// fim - relatorio_tipo


$where = "-1";
if (isset($_POST['where'])) {
	$where = stripslashes($_POST["where"]); // stripslashes: serve para tirar a "barra invertida" que o servidor insere no POST como segurança
}

$where_agenda_treinamento = "-1";
if (isset($_POST['where_agenda_treinamento'])) {
	$where_agenda_treinamento = stripslashes($_POST["where_agenda_treinamento"]); // stripslashes: tira a "barra invertida" que o servidor insere no POST como segurança
}

$where_agenda_implantacao = "-1";
if (isset($_POST['where_agenda_implantacao'])) {
	$where_agenda_implantacao = stripslashes($_POST["where_agenda_implantacao"]); // stripslashes: tira a "barra invertida" que o servidor insere no POST como segurança
}

mysql_select_db($database_conexao, $conexao);

// venda
if($relatorio_tipo=="venda"){

	$query_venda = "
	SELECT 
	venda.id, venda.status, venda.situacao, venda.data_venda, venda.id_usuario_responsavel, 
	venda.empresa, venda.quantidade_agendado_treinamento, venda.quantidade_agendado_implantacao, venda.status_flag, 
	venda.id_prospeccao, venda.contrato, venda.praca, venda.data_inicio, venda.data_fim, venda.observacao, 
	(SELECT nome FROM usuarios WHERE venda.id_usuario_responsavel = usuarios.IdUsuario) AS usuario_responsavel, 
	
	'' AS venda_agenda_treinamento_id_agenda, 
	'' AS venda_agenda_treinamento_data, 
	'' AS venda_agenda_treinamento_data_inicio, 
	'' AS venda_agenda_treinamento_descricao, 
	'' AS venda_agenda_treinamento_status
	
	FROM venda 
	WHERE $where 
	ORDER BY venda.id ASC";
	
} else if($relatorio_tipo=="venda_agenda_treinamento"){

	$query_venda = "
	SELECT 
	venda.id, venda.status, venda.situacao, venda.data_venda, venda.usuario_responsavel, 
	venda.empresa, venda.quantidade_agendado_treinamento, venda.quantidade_agendado_implantacao, venda.status_flag, 
	venda.id_prospeccao, venda.contrato, venda.praca, venda.data_inicio, venda.data_fim, venda.observacao, 
	
	agenda.data AS venda_agenda_treinamento_data, 
	agenda.data_inicio AS venda_agenda_treinamento_data_inicio, 
	agenda.descricao AS venda_agenda_treinamento_descricao, 
	agenda.status AS venda_agenda_treinamento_status
	
	FROM agenda 
	LEFT JOIN venda ON agenda.id_venda_treinamento = venda.id
	WHERE $where and agenda.id_venda_treinamento IS NOT NULL and $where_agenda_treinamento
	ORDER BY agenda.data ASC";

} else if($relatorio_tipo=="venda_agenda_implantacao"){

	$query_venda = "
	SELECT 
	venda.id, venda.status, venda.situacao, venda.data_venda, venda.usuario_responsavel, 
	venda.empresa, venda.quantidade_agendado_treinamento, venda.quantidade_agendado_implantacao, venda.status_flag, 
	venda.id_prospeccao, venda.contrato, venda.praca, venda.data_inicio, venda.data_fim, venda.observacao, 
	
	agenda.data AS venda_agenda_implantacao_data, 
	agenda.data_inicio AS venda_agenda_implantacao_data_inicio, 
	agenda.descricao AS venda_agenda_implantacao_descricao, 
	agenda.status AS venda_agenda_implantacao_status
	
	FROM agenda 
	LEFT JOIN venda ON agenda.id_venda_implantacao = venda.id
	WHERE $where and agenda.id_venda_implantacao IS NOT NULL and $where_agenda_implantacao
	ORDER BY agenda.data ASC";

}

$venda = mysql_query($query_venda, $conexao) or die(mysql_error());
$row_venda = mysql_fetch_assoc($venda);
$totalRows_venda = mysql_num_rows($venda);
// fim - venda


// filtros utilizados
$campos = "";
$where_campos = explode(";", $campos); // joga os campos em uma Array
$count = count(@$where_campos)-1; // conta o número de campos encontrados (-1 por causa do ponto de virgula)
// fim - filtros utilizados
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
<? // echo $where; echo "<br><br>"; echo $where_agenda_treinamento; echo "<br><br>"; echo $where_agenda_implantacao; ?>

<!-- titulo -->
<div style="border: 1px solid #CCC; padding: 5px; margin-bottom: 10px; font-family: Verdana, Geneva, sans-serif;">

	<div style="text-align: center; font-weight: bold; font-size: 14px;">
		<? if($relatorio_tipo=="venda"){ ?>
	    Relatório geral de Vendas 
        <? } else { ?>
        Relatório geral de Agenda de Vendas 
        <? } ?>
        (<? echo $totalRows_venda; ?>)
    </div>
    
    <!-- filtros -->
	<? if($count > 0){ ?>
    	<hr class="hr_linha" />
    	<div style="text-align: left; font-size: 12px;">
			<? for ($i = 0; $i < $count; $i++) { $campo_atual = $where_campos[$i]; // campos ?>
                            
                <!-- estorno -->                
                <? if($campo_atual=="estorno"){ ?> 
                    Suporte já estornado: SIM
                <? } ?>
                <!-- fim - estorno -->
                
            <? } ?>        
		</div>
    <? } ?>
    <!-- fim - filtros -->
        
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
			if($relatorio_campos=="id_prospeccao"){echo "Prospecção";}
            if($relatorio_campos=="data_venda"){echo "Data/hora criação";}
            if($relatorio_campos=="empresa"){echo "Empresa";}
            if($relatorio_campos=="contrato"){echo "Núm contrato";}
            if($relatorio_campos=="praca"){echo "Praça";}
            if($relatorio_campos=="data_inicio"){echo "Data início";}
            if($relatorio_campos=="data_fim"){echo "Data fim";}
            if($relatorio_campos=="usuario_responsavel"){echo "Responsável";}
            if($relatorio_campos=="status"){echo "Status";}
			if($relatorio_campos=="situacao"){echo "Situação";}
            if($relatorio_campos=="observacao"){echo "Obs.";}
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
        
                    <td >
					<div class="div_td">
					<?
					$campo = $row_venda[$relatorio_campos];

					if($relatorio_campos=="data_venda" and $campo!=NULL){$campo = date('d-m-Y H:i', strtotime($campo));}
					if($relatorio_campos=="data_inicio" and $campo!=NULL){$campo = date('d-m-Y H:i', strtotime($campo));}
					if($relatorio_campos=="data_fim" and $campo!=NULL){$campo = date('d-m-Y H:i', strtotime($campo));}
					if($relatorio_campos=="tipo_venda"){if($campo=="c"){$campo = "cliente";}if($campo=="p"){$campo = "parceiro";}}
					if($relatorio_campos=="inloco"){if($campo=="s"){$campo = "sim";}if($campo=="n"){$campo = "não";}}
					if($relatorio_campos=="empresa"){$campo = utf8_encode($campo);}
					if($relatorio_campos=="cobranca"){if($campo=="s"){$campo = "sim";}if($campo=="n"){$campo = "não";}}
					if($relatorio_campos=="cobranca_recebimento"){if($campo=="s"){$campo = "sim";}if($campo=="n"){$campo = "não";}}
					if($relatorio_campos=="id_formulario"){
						
						// Tipo e Número do form
						if($campo!=""){
							
							// venda_formulario
							mysql_select_db($database_conexao, $conexao);
							$query_venda_formulario = sprintf("
																SELECT tipo_formulario 
																FROM venda_formulario 
																WHERE id_venda = %s", GetSQLValueString($row_venda['id'], "int"));
							$venda_formulario = mysql_query($query_venda_formulario, $conexao) or die(mysql_error());
							$row_venda_formulario = mysql_fetch_assoc($venda_formulario);
							$totalRows_venda_formulario = mysql_num_rows($venda_formulario);
							// fim - venda_formulario
							
							$campo = $campo." - ".$row_venda_formulario['tipo_formulario'];
							
							mysql_free_result($venda_formulario);
							
						} else {
							
							$campo = $campo;
						
						}
						// fim - Tipo e Número do form
						
					}
					
					echo $campo;
					?>
					</div>
					</td>
        
            <? } // fim - lista os campos selecionados para o relatório  ?>  
            </tr>
	<?php } while ($row_venda = mysql_fetch_assoc($venda)); ?>
	<!-- fim - registros -->
    
</table>
</body>
</html>
<?
mysql_free_result($usuario);
mysql_free_result($venda);
mysql_close($conexao);
?>