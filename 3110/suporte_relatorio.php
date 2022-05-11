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

// suporte
$where = "-1";
if (isset($_POST['where'])) {
	$where = stripslashes($_POST["where"]); // stripslashes: serve para tirar a "barra invertida" que o servidor insere no POST como segurança
}

mysql_select_db($database_conexao, $conexao);
$query_suporte = "
				SELECT *, 
				(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_responsavel) as usuario_responsavel, 
				(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_envolvido) as usuario_envolvido
				FROM suporte 
				WHERE $where 
				ORDER BY suporte.id ASC";
$suporte = mysql_query($query_suporte, $conexao) or die(mysql_error());
$row_suporte = mysql_fetch_assoc($suporte);
$totalRows_suporte = mysql_num_rows($suporte);
// fim - suporte

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

<!-- titulo -->
<div style="border: 1px solid #CCC; padding: 5px; margin-bottom: 10px; font-family: Verdana, Geneva, sans-serif;">

	<div style="text-align: center; font-weight: bold; font-size: 14px;">
	    Relatório geral de suportes (<? echo $totalRows_suporte; ?>)
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
    		<? echo $contador_relatorio_campos = $contador_relatorio_campos + 1; // contador de campos selecionados  ?>
			<td>
			<div class="div_td">
			<? 
            if($relatorio_campos=="id"){echo "Número";}
            if($relatorio_campos=="tipo_suporte"){echo "Tipo de suporte";}
            if($relatorio_campos=="inloco"){echo "In-loco";}
            if($relatorio_campos=="titulo"){echo "Título";}
            if($relatorio_campos=="data_suporte"){echo "Data/hora criação";}
            if($relatorio_campos=="empresa"){echo "Empresa";}
            if($relatorio_campos=="contrato"){echo "Núm contrato";}
            if($relatorio_campos=="praca"){echo "Praça";}
            if($relatorio_campos=="data_inicio"){echo "Data início";}
            if($relatorio_campos=="data_fim"){echo "Data fim";}
            if($relatorio_campos=="usuario_envolvido"){echo "Envolvido";}
            if($relatorio_campos=="usuario_responsavel"){echo "Responsável";}
            if($relatorio_campos=="solicitante"){echo "Solicitante";}			
            if($relatorio_campos=="modulo"){echo "Módulo";}
            if($relatorio_campos=="tipo_atendimento"){echo "Tipo de atendimento";}
            if($relatorio_campos=="anomalia"){echo "Anomalia";}
            if($relatorio_campos=="orientacao"){echo "Orientação";}
            if($relatorio_campos=="parecer"){echo "Parecer";}
			if($relatorio_campos=="recomendacao"){echo "Recomendação";}
            if($relatorio_campos=="status"){echo "Status";}
			if($relatorio_campos=="situacao"){echo "Situação";}
            if($relatorio_campos=="observacao"){echo "Obs.";}
            if($relatorio_campos=="id_formulario"){echo "Núm./Tipo Formulário";}
			if($relatorio_campos=="cobranca"){echo "Cobrança";}
			if($relatorio_campos=="cobranca_recebimento"){echo "Recebido (AC)";}
			if($relatorio_campos=="cobranca_recebimento_justificativa"){echo "Justificativa (AC)";}
			if($relatorio_campos=="avaliacao_atendimento"){echo "Avaliação de Atendimento";}
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
					$campo = $row_suporte[$relatorio_campos];

					if($relatorio_campos=="data_suporte"){$campo = date('d-m-Y H:i', strtotime($campo));}
					if($relatorio_campos=="data_inicio"){$campo = date('d-m-Y H:i', strtotime($campo));}
					if($relatorio_campos=="data_fim"){$campo = date('d-m-Y H:i', strtotime($campo));}
					if($relatorio_campos=="tipo_suporte"){if($campo=="c"){$campo = "cliente";}if($campo=="p"){$campo = "parceiro";}}
					if($relatorio_campos=="inloco"){if($campo=="s"){$campo = "sim";}if($campo=="n"){$campo = "não";}}
					if($relatorio_campos=="empresa"){$campo = utf8_encode($campo);}
					if($relatorio_campos=="cobranca"){if($campo=="s"){$campo = "sim";}if($campo=="n"){$campo = "não";}}
					if($relatorio_campos=="cobranca_recebimento"){if($campo=="s"){$campo = "sim";}if($campo=="n"){$campo = "não";}}
					if($relatorio_campos=="id_formulario"){
						
						// Tipo e Número do form
						if($campo!=""){
							
							// suporte_formulario
							mysql_select_db($database_conexao, $conexao);
							$query_suporte_formulario = sprintf("
																SELECT tipo_formulario 
																FROM suporte_formulario 
																WHERE id_suporte = %s", GetSQLValueString($row_suporte['id'], "int"));
							$suporte_formulario = mysql_query($query_suporte_formulario, $conexao) or die(mysql_error());
							$row_suporte_formulario = mysql_fetch_assoc($suporte_formulario);
							$totalRows_suporte_formulario = mysql_num_rows($suporte_formulario);
							// fim - suporte_formulario
							
							$campo = $campo." - ".$row_suporte_formulario['tipo_formulario'];
							
							mysql_free_result($suporte_formulario);
							
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
	<?php } while ($row_suporte = mysql_fetch_assoc($suporte)); ?>
	<!-- fim - registros -->
    
</table>
</body>
</html>
<?
mysql_free_result($usuario);
mysql_free_result($suporte);
mysql_close($conexao);
?>