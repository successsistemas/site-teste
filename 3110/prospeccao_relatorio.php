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
$relatorio_tipo = "prospeccao";
if( (isset($_POST["relatorio_tipo"])) && ($_POST['relatorio_tipo'] =="prospeccao") ) {
	$relatorio_tipo = "prospeccao";
} else {	
	$relatorio_tipo = "prospeccao_agenda";
}
// fim - relatorio_tipo

$where = "-1";
if (isset($_POST['where'])) {
	$where = stripslashes($_POST["where"]); // stripslashes: serve para tirar a "barra invertida" que o servidor insere no POST como segurança
}

$where_agenda = "-1";
if (isset($_POST['where_agenda'])) {
	$where_agenda = stripslashes($_POST["where_agenda"]); // stripslashes: serve para tirar a "barra invertida" que o servidor insere no POST como segurança
}

mysql_select_db($database_conexao, $conexao);


// prospeccao
if($relatorio_tipo=="prospeccao"){	

	$query_prospeccao = "
	SELECT 
	prospeccao.id, prospeccao.nome_razao_social, prospeccao.pessoa, prospeccao.fantasia, prospeccao.cep, prospeccao.endereco, prospeccao.bairro, prospeccao.cidade, prospeccao.uf, prospeccao.praca, prospeccao.telefone, prospeccao.celular, prospeccao.cpf_cnpj, prospeccao.rg_inscricao, prospeccao.observacao, prospeccao.data_prospeccao, prospeccao.data_prospeccao_fim, prospeccao.ativo_passivo, prospeccao.indicado_por, prospeccao.responsavel_por_ti, prospeccao.enquadramento_fiscal, prospeccao.ramo_de_atividade, prospeccao.contador, prospeccao.exige_nfe, prospeccao.exige_cupom_fiscal, prospeccao.exige_outro, prospeccao.status, prospeccao.situacao, prospeccao.id_usuario_responsavel, prospeccao.sistema_possui, prospeccao.id_concorrente, prospeccao.sistema_nivel_satisfacao, prospeccao.nivel_interesse, prospeccao.baixa_perda_motivo, prospeccao.id_contador, 
	prospeccao_concorrente.nome AS prospeccao_concorrente_nome, prospeccao_concorrente.migracao, prospeccao_concorrente.migracao_tipo, 
	prospeccao_contador.razao AS prospeccao_contador_razao, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = prospeccao.id_usuario_responsavel) as usuario_responsavel, 
	
	'' AS prospeccao_agenda_id_agenda, 
	'' AS prospeccao_agenda_data, 
	'' AS prospeccao_agenda_data_inicio, 
	'' AS prospeccao_agenda_descricao, 
	'' AS prospeccao_agenda_status
	
	FROM prospeccao 
	LEFT JOIN prospeccao_concorrente ON prospeccao_concorrente.id = prospeccao.id_concorrente 
	LEFT JOIN prospeccao_contador ON prospeccao_contador.id = prospeccao.id_contador  
	WHERE $where
	ORDER BY prospeccao.id ASC";
	
} else {

	$query_prospeccao = "
	SELECT 
	prospeccao.id, prospeccao.nome_razao_social, prospeccao.pessoa, prospeccao.fantasia, prospeccao.cep, prospeccao.endereco, prospeccao.bairro, prospeccao.cidade, prospeccao.uf, prospeccao.praca, prospeccao.telefone, prospeccao.celular, prospeccao.cpf_cnpj, prospeccao.rg_inscricao, prospeccao.observacao, prospeccao.data_prospeccao, prospeccao.data_prospeccao_fim, prospeccao.ativo_passivo, prospeccao.indicado_por, prospeccao.responsavel_por_ti, prospeccao.enquadramento_fiscal, prospeccao.ramo_de_atividade, prospeccao.contador, prospeccao.exige_nfe, prospeccao.exige_cupom_fiscal, prospeccao.exige_outro, prospeccao.status, prospeccao.situacao, prospeccao.id_usuario_responsavel, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = prospeccao.id_usuario_responsavel) as usuario_responsavel, 
	
	agenda.id_agenda AS prospeccao_agenda_id_agenda, 
	agenda.data AS prospeccao_agenda_data, 
	agenda.data_inicio AS prospeccao_agenda_data_inicio, 
	agenda.descricao AS prospeccao_agenda_descricao, 
	agenda.status AS prospeccao_agenda_status
	
	FROM agenda
	LEFT JOIN prospeccao ON agenda.id_prospeccao = prospeccao.id
	WHERE $where and $where_agenda
	ORDER BY prospeccao.id ASC";

}

$prospeccao = mysql_query($query_prospeccao, $conexao) or die(mysql_error());
$row_prospeccao = mysql_fetch_assoc($prospeccao);
$totalRows_prospeccao = mysql_num_rows($prospeccao);
// fim - prospeccao


// filtros utilizados
$where_campos = explode(";", @$campos); // joga os campos em uma Array
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
		<? if($relatorio_tipo=="prospeccao"){ ?>
	    Relatório geral de Prospecções 
        <? } else { ?>
        Relatório geral de Agenda de Prospecções 
        <? } ?>
        (<? echo $totalRows_prospeccao; ?>)
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
			<td id="coluna_<? echo $relatorio_campos; ?>">
			<div class="div_td">
			<? 
			if($relatorio_campos=="id"){echo "Número";}
			if($relatorio_campos=="nome_razao_social"){echo "Nome/Razão Social";}
			if($relatorio_campos=="pessoa"){echo "Pessoa";}
			if($relatorio_campos=="fantasia"){echo "Nome Fantasia";}
			if($relatorio_campos=="cep"){echo "CEP";}  
			if($relatorio_campos=="endereco"){echo "Endereço";}
			if($relatorio_campos=="bairro"){echo "Bairro";}
			if($relatorio_campos=="cidade"){echo "Cidade";}
			if($relatorio_campos=="uf"){echo "Estado";}
			if($relatorio_campos=="praca"){echo "Praça";}
			if($relatorio_campos=="telefone"){echo "Telefone";}
			if($relatorio_campos=="celular"){echo "Celular";}
			if($relatorio_campos=="cpf_cnpj"){echo "CPF/CNPJ";}
			if($relatorio_campos=="rg_inscricao"){echo "RG/Inscrição";}
			if($relatorio_campos=="observacao"){echo "Obs.";}
			if($relatorio_campos=="data_prospeccao"){echo "Data de Criação";}
			if($relatorio_campos=="data_prospeccao_fim"){echo "Data Final";}
			if($relatorio_campos=="ativo_passivo"){echo "Tipo de prosppeção";}
			if($relatorio_campos=="indicado_por"){echo "Indicado por";}
			if($relatorio_campos=="responsavel_por_ti"){echo "Responsável por TI";}
			if($relatorio_campos=="enquadramento_fiscal"){echo "Enquadramento Fiscal";}
			if($relatorio_campos=="ramo_de_atividade"){echo "Ramo de Atividade";}
			if($relatorio_campos=="contador"){echo "Contador";}
			if($relatorio_campos=="exige_nfe"){echo "Exige NFE";}
			if($relatorio_campos=="exige_cupom_fiscal"){echo "Exige Cupom";}
			if($relatorio_campos=="exige_outro"){echo "Exige outros";}
			if($relatorio_campos=="situacao"){echo "Situação";}
			if($relatorio_campos=="status"){echo "Status";}
			if($relatorio_campos=="usuario_responsavel"){echo "Responsável";}
			
			if($relatorio_campos=="sistema_possui"){echo "Possui sistema?";}
			if($relatorio_campos=="migracao"){echo "Possuímos migração de dados?";}
			if($relatorio_campos=="migracao_tipo"){echo "Tipo de migração";}
			if($relatorio_campos=="prospeccao_concorrente_nome"){echo "Concorrente";}
			if($relatorio_campos=="sistema_nivel_satisfacao"){echo "Nível de Satisfação";}
			if($relatorio_campos=="nivel_interesse"){echo "Nível de Interesse";}
			if($relatorio_campos=="baixa_perda_motivo"){echo "Motivo da Perda";}
			if($relatorio_campos=="prospeccao_contador_razao"){echo "Contabilidade";}

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
					$campo = $row_prospeccao[$relatorio_campos];

					if($relatorio_campos=="data_prospeccao" and $campo!=NULL){$campo = date('d-m-Y H:i', strtotime($campo));}
					if($relatorio_campos=="data_prospeccao_fim" and $campo!=NULL){$campo = date('d-m-Y H:i', strtotime($campo));}
					if($relatorio_campos=="pessoa"){if($campo=="f"){$campo = "Física";}if($campo=="j"){$campo = "Jurídica";}}
					if($relatorio_campos=="ativo_passivo"){if($campo=="a"){$campo = "Ativo";}if($campo=="p"){$campo = "Passivo";}}
					if($relatorio_campos=="nome_razao_social"){$campo = utf8_encode($campo);}
					
					if($relatorio_campos=="sistema_possui"){
						if($campo=="s"){$campo = "Sim";} 
						else if($campo=="n"){$campo = "Não";}
					}
					
					if($relatorio_campos=="migracao"){
						if($campo=="s"){$campo = "Sim";} 
						else if($campo=="n"){$campo = "Não";}
					}
					
					if($relatorio_campos=="migracao_tipo"){
						if($campo=="c"){$campo = "completa";} 
						else if($campo=="p"){$campo = "Parcial";} 
						else if($campo=="b"){$campo = "Cadastros básicos";}
					}
					
					if($relatorio_campos=="sistema_nivel_satisfacao"){
						if($campo == "a"){$campo = "Alto";}
						else if($campo == "m"){$campo = "Médio";}
						else if($campo == "b"){$campo = "Baixo";}
						else if($campo == "i"){$campo = "Insatisfeito";}
					}
					
					if($relatorio_campos=="nivel_interesse"){
						if($campo == "a"){echo "Alto";}
						else if($campo == "m"){$campo = "Médio";}
						else if($campo == "b"){$campo = "Baixo";}
						else if($campo == "n"){$campo = "Nenhum";}
					}
					
					echo $campo;
					?>
					</div>
					</td>
        
            <? } // fim - lista os campos selecionados para o relatório  ?>  
            </tr>
	<?php } while ($row_prospeccao = mysql_fetch_assoc($prospeccao)); ?>
	<!-- fim - registros -->
    
</table>
</body>
</html>
<?
mysql_free_result($usuario);
mysql_free_result($prospeccao);
mysql_close($conexao);
?>