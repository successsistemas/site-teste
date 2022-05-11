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

// venda
$colname_venda = "-1";
if (isset($_GET['id_venda'])) {
  $colname_venda = $_GET['id_venda'];
}
mysql_select_db($database_conexao, $conexao);
$query_venda = sprintf("
SELECT venda.*, 
(SELECT nome FROM usuarios WHERE venda.id_usuario_responsavel = usuarios.IdUsuario) AS usuario_responsavel 
FROM venda 
WHERE venda.id = %s", 
GetSQLValueString($colname_venda, "int"));
$venda = mysql_query($query_venda, $conexao) or die(mysql_error());
$row_venda = mysql_fetch_assoc($venda);
$totalRows_venda = mysql_num_rows($venda);
// fim - venda

// $colname_contrato
$colname_contrato = "-1";
if (isset($_GET["contrato"])) {
  $colname_contrato = $_GET["contrato"];
}
// fim - $colname_contrato

// manutencao_dados
mysql_select_db($database_conexao, $conexao);
$query_manutencao_dados = sprintf("
SELECT * FROM da37 WHERE da37.codigo17 = %s and da37.sr_deleted <> 'T'", GetSQLValueString($colname_contrato, "text"));
$manutencao_dados = mysql_query($query_manutencao_dados, $conexao) or die(mysql_error());
$row_manutencao_dados = mysql_fetch_assoc($manutencao_dados);
$totalRows_manutencao_dados = mysql_num_rows($manutencao_dados);
// fim - manutencao_dados

// empresa_dados ---------------------------
if($totalRows_manutencao_dados > 0 and $row_venda['codigo_empresa']!=""){ // contrato existe na tabela 'DA37s9'
	
	mysql_select_db($database_conexao, $conexao);
	$query_empresa_dados = sprintf("
	SELECT nome1, contato1, cgc1, insc1, endereco1, bairro1, cidade1, uf1, telefone1, comercio1, cep1, ultcompra1, atraso1, status1, flag1 
	FROM da01 
	WHERE codigo1 = %s and da01.sr_deleted <> 'T'", 
	GetSQLValueString($row_manutencao_dados['cliente17'], "text"));
	$empresa_dados = mysql_query($query_empresa_dados, $conexao) or die(mysql_error());
	$row_empresa_dados = mysql_fetch_assoc($empresa_dados);
	$totalRows_empresa_dados = mysql_num_rows($empresa_dados);

}else{ // contrato NÃO existe na tabela 'DA37s9'
	
	mysql_select_db($database_conexao, $conexao);
	$query_empresa_dados = sprintf("
	SELECT nome_razao_social AS nome1, cpf_cnpj AS cgc1, rg_inscricao AS insc1, concat(endereco,' - ',endereco_numero) AS endereco1, bairro AS bairro1, cidade AS cidade1, 
	uf AS uf1, telefone telefone1, celular AS comercio1, cep AS cep1, '' AS ultcompra1, '' AS atraso1, '' AS status1, '' AS flag1, '' AS contato1 
	FROM prospeccao 
	WHERE id = %s", 
	GetSQLValueString($row_venda['id_prospeccao'], "text"));
	$empresa_dados = mysql_query($query_empresa_dados, $conexao) or die(mysql_error());
	$row_empresa_dados = mysql_fetch_assoc($empresa_dados);
	$totalRows_empresa_dados = mysql_num_rows($empresa_dados);
	
}
// fim - empresa_dados ---------------------

// treinamento_pergunta
mysql_select_db($database_conexao, $conexao);
$query_treinamento_pergunta =  sprintf("
									   SELECT treinamento_pergunta.permite_lancamentos, treinamento_pergunta.codigo, treinamento_pergunta.descricao  
									   FROM treinamento_pergunta 
									   LEFT JOIN venda_modulos ON treinamento_pergunta.id_modulo = venda_modulos.id_modulo 
									   WHERE venda_modulos.contrato = %s
									   ORDER BY treinamento_pergunta.codigo ASC", 
									   GetSQLValueString($colname_contrato, "text"));
$treinamento_pergunta = mysql_query($query_treinamento_pergunta, $conexao) or die(mysql_error());
$row_treinamento_pergunta = mysql_fetch_assoc($treinamento_pergunta);
$totalRows_treinamento_pergunta = mysql_num_rows($treinamento_pergunta);
// fim - treinamento_pergunta

// implantacao_pergunta
mysql_select_db($database_conexao, $conexao);
$query_implantacao_pergunta =  sprintf("
									   SELECT implantacao_pergunta.permite_lancamentos, implantacao_pergunta.codigo, implantacao_pergunta.descricao  
									   FROM implantacao_pergunta 
									   LEFT JOIN venda_modulos ON implantacao_pergunta.id_modulo = venda_modulos.id_modulo 
									   WHERE venda_modulos.contrato = %s
									   ORDER BY implantacao_pergunta.codigo ASC", 
									   GetSQLValueString($colname_contrato, "text"));
$implantacao_pergunta = mysql_query($query_implantacao_pergunta, $conexao) or die(mysql_error());
$row_implantacao_pergunta = mysql_fetch_assoc($implantacao_pergunta);
$totalRows_implantacao_pergunta = mysql_num_rows($implantacao_pergunta);
// fim - implantacao_pergunta

// venda_modulos
mysql_select_db($database_conexao, $conexao);
$query_venda_modulos = sprintf("SELECT geral_tipo_modulo.descricao AS modulo FROM venda_modulos LEFT JOIN geral_tipo_modulo ON venda_modulos.id_modulo = geral_tipo_modulo.IdTipoModulo WHERE venda_modulos.id_venda = %s", GetSQLValueString($_GET['id_venda'], "int"));
$venda_modulos = mysql_query($query_venda_modulos, $conexao) or die(mysql_error());
$row_venda_modulos = mysql_fetch_assoc($venda_modulos);
$totalRows_venda_modulos = mysql_num_rows($venda_modulos);
// fim - venda_modulos

// venda_agenda_treinamento
mysql_select_db($database_conexao, $conexao);
$query_venda_agenda_treinamento =  sprintf("
									   SELECT agenda.id_usuario_responsavel, agenda.data_inicio, agenda.data, agenda.venda_receptor,
									   usuarios.status AS usuarios_status, usuarios.nome 
									   FROM agenda 
									   INNER JOIN usuarios ON agenda.id_usuario_responsavel = usuarios.IdUsuario 
									   WHERE id_venda_treinamento = %s and (agenda.status = 'a' or agenda.status = 'f')
									   ORDER BY agenda.id_agenda ASC", 
									   GetSQLValueString($row_venda['id'], "int"));
$venda_agenda_treinamento = mysql_query($query_venda_agenda_treinamento, $conexao) or die(mysql_error());
$row_venda_agenda_treinamento = mysql_fetch_assoc($venda_agenda_treinamento);
$totalRows_venda_agenda_treinamento = mysql_num_rows($venda_agenda_treinamento);
// fim - venda_agenda_treinamento

// venda_agenda_implantacao
mysql_select_db($database_conexao, $conexao);
$query_venda_agenda_implantacao =  sprintf("
									   SELECT agenda.id_usuario_responsavel, agenda.data_inicio, agenda.data, agenda.venda_receptor, 
									   usuarios.status AS usuarios_status, usuarios.nome 
									   FROM agenda 
									   INNER JOIN usuarios ON agenda.id_usuario_responsavel = usuarios.IdUsuario 
									   WHERE id_venda_implantacao = %s and (agenda.status = 'a' or agenda.status = 'f')
									   ORDER BY agenda.id_agenda ASC", 
									   GetSQLValueString($row_venda['id'], "int"));
$venda_agenda_implantacao = mysql_query($query_venda_agenda_implantacao, $conexao) or die(mysql_error());
$row_venda_agenda_implantacao = mysql_fetch_assoc($venda_agenda_implantacao);
$totalRows_venda_agenda_implantacao = mysql_num_rows($venda_agenda_implantacao);
// fim - venda_agenda_implantacao

// venda_validade
mysql_select_db($database_conexao, $conexao);
$query_venda_validade =  sprintf("
								 SELECT *
								 FROM venda_validade 
								 WHERE id_venda = %s 
								 ORDER BY venda_validade.id ASC", 
								 GetSQLValueString($row_venda['id'], "int"));
$venda_validade = mysql_query($query_venda_validade, $conexao) or die(mysql_error());
$row_venda_validade = mysql_fetch_assoc($venda_validade);
$totalRows_venda_validade = mysql_num_rows($venda_validade);
// fim - venda_validade

$venda_validade_dias = $row_parametros['venda_validade_dias'] + $row_venda['dilacao_prazo'];
$validade = date('d-m-Y 23:59:59', strtotime("+$venda_validade_dias days",strtotime($row_venda['data_venda'])));
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript">
$(document).ready(function() {
		
	// imprime o 'concluir_implantacao_treinamento' e vai para a tela da 'venda'
	$('#imprimir').click(function() {
		
		print();
		
		<? if($row_venda['situacao']!="criada" and $row_venda['situacao']!="solucionada" and $row_venda['situacao']!="cancelada"){ ?>
			<?php if($row_venda['conclusao_implantacao_treinamento'] == 0){ ?>
			// post
			$.post("venda_editar_concluir_implantacao_treinamento_impressao.php", {
				   id_venda: <?php echo $row_venda['id']; ?>
				   }, function(data) {
	
						window.open('venda_editar.php?id_venda=<?php echo $row_venda['id']; ?>', '_self');
						
				   }
			);
			// fim - post
			<? } ?>
		<? } ?>
	
	});	
	// fim - imprime o 'concluir_implantacao_treinamento' e vai para a tela da 'venda'>

});
</script>
<style>
body {
	font-family: Verdana, Geneva, sans-serif;
	font-size: 10px;
}
table.bordasimples {
	border-collapse: collapse;
	font-size: 10px;
}
table.bordatransparente {
	border-collapse: inherit;
	font-size: 10px;
}
table.bordasimples tr th {
	border:1px solid #000;
	font-family: Verdana, Geneva, sans-serif;
	font-size: 12px;
	padding-left: 3px;
	padding-right: 3px;
	padding-top: 5px;
	padding-bottom: 5px;
	vertical-align: top;
	line-height: 1;
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
	line-height: 1.3;
}
.caixa_observacao{
	padding-top: 2px;
	text-align:justify; 
	font-size: 8px;
	line-height: 1.2;
}
thead { display:table-header-group;margin:0;padding:0;}
tfoot {display: table-footer-group;margin:0;padding:0;}

table.resumo {
	border-collapse: collapse;
	font-size: 10px;
}
table.resumo tr th {
	border:1px solid #000;
	font-family: Verdana, Geneva, sans-serif;
	font-size: 12px;
	padding-left: 3px;
	padding-right: 3px;
	padding-top: 5px;
	padding-bottom: 5px;
	vertical-align: top;
	line-height: 1;
}
table.resumo tr td {
	border: 1px solid #000;
	font-family: Verdana, Geneva, sans-serif;
	padding-left: 5px;
	padding-right: 5px;
	padding-top: 5px;
	padding-bottom: 5px;
	vertical-align: top;
	line-height: 1;
}

    thead { display: table-header-group; }
    tfoot { display: table-footer-group; }
	
.cabecalho_impressao{
	border: 2px solid #999;
	padding: 5px;
	margin-bottom: 5px;
	text-align: center;
	font-weight: bold;
	font-size: 12px;
}
</style>
<link href="css/print.css" rel="stylesheet" type="text/css" media="print" />
</head>

<body>

<? if($row_venda['situacao']!="criada" and $row_venda['situacao']!="solucionada" and $row_venda['situacao']!="cancelada"){ ?>
<div class="cabecalho_impressao">
<a href="#" id="imprimir" style="width: 150px;">Imprimir</a> 
</div>
<? } ?>

<table width="100%" height="20" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="titulo_formulario" style="font-weight: bold">
    CONCLUSÃO DE IMPLANTAÇÃO E TREINAMENTO (Venda nº <? echo $_GET['id_venda']; ?>)
    </td>
  </tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>   
        <td width="50%" align="left">

        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
        <tr>
        
        <td width="50%">
        Cliente:
        <div><?php echo utf8_encode($row_empresa_dados['nome1']); ?></div>
        </td>
        
        <td width="25%">
        CPF / CNPJ:
        <div><?php echo shellDescriptografa($row_empresa_dados['cgc1']); ?></div>
        </td>
        
        <td width="25%">
        ID / INSC. EST.:
        <div><?php echo shellDescriptografa($row_empresa_dados['insc1']); ?></div>
        </td>
        
        </tr>
        </table>
        
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
        <tr>   
           
        <td width="50%">
        Representante Legal: 
        <br>
		<?php echo utf8_encode($row_empresa_dados['contato1']); ?>
        </td> 

        <td width="25%">
        Receptor do treinamento:
        <br>
        <br>
        </td>
                       
        <td width="25%">
        Função:
        <br><br>
        </td>
                
        </tr>
        </table>
        
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
        <tr>      
        
        <td width="25%">
        Instrutor:
        <br>
        <?php echo $row_venda['usuario_responsavel']; ?>
        </td>        
        
        <td width="15%">
        Data do início:
        <br>
        <? if($row_venda_agenda_treinamento['data']!=""){echo date('d-m-Y', strtotime($row_venda_agenda_treinamento['data']));} ?>
        </td>
        
        <td width="35%">
        Horas de treinamento adquiridas:
        <br>
		<? 
		$treinamento_tempo_segundo = $row_venda['treinamento_tempo']*60;

		$tHoras = $treinamento_tempo_segundo / 3600;
		$tMinutos = $treinamento_tempo_segundo % 3600 / 60;
		
		echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
        ?>
        </td>
        
        <td width="25%">
        Valor do serviço:
        <br>
        <? if($row_venda['valor_treinamento']!=""){ ?>
        R$ <? echo number_format($row_venda['valor_treinamento'], 2, ',', '.'); ?>
        <? } else { ?>
        R$ 0,00
        <? } ?>
        </td>
        
        </tr>
        </table>
        
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
        <tr>

        <td width="25%">
        Região: 
        <br>
		<?php echo $row_venda['praca']; ?>
        </td>
        
        <td width="15%">
        Instalação:
        <br>
        <? if($row_venda_agenda_implantacao['data']!=""){echo date('d-m-Y', strtotime($row_venda_agenda_implantacao['data']));} ?>
        </td>
        
        <td width="35%">
        Horas de implantação disponibilizadas:
        <br>
		<? 
		$implantacao_tempo_segundo = $row_venda['implantacao_tempo']*60;

		$tHoras = $implantacao_tempo_segundo / 3600;
		$tMinutos = $implantacao_tempo_segundo % 3600 / 60;
		
		echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
        ?>
        </td>
        
        <td width="25%">
        Validade dos serviços:
        <br>
        <? 
		if($row_venda['data_contrato']!=""){echo date('d-m-Y', strtotime($validade));}
		?>
        </td>
        
        </tr>
        </table>
        
    </td>
</tr>
</table>

<!-- dilacao -->
<? if($totalRows_venda_validade > 0){ ?>
<table width="100%" height="20" border="0" cellspacing="0" cellpadding="0" style="margin-top: 20px; margin-bottom: 10px;">
  <tr>
    <td class="titulo_formulario" style="font-weight: bold; text-align: center;">
      DILAÇÃO DE PRAZO DE VALIDADE DOS SERVIÇOS
    </td>
  </tr>
</table>


<table cellspacing=0 cellpadding=0 width='100%' class="resumo">

<thead>
    <tr>
    	<th width="25%">Data</th>
        <th width="20%">Quantidade de dias estendidos</th>
        <th>Motivo</th>
        <th width="10%">Autorizado</th>
    </tr>
</thead>

<tfoot>
    <tr>
        <td colspan="4" height="10">        
        </td>
    </tr>
</tfoot>
 
<? do{ ?>
<tr>

    <td align="center">
	<? if($row_venda_validade['data_atual']!=""){echo date('d-m-Y H:i', strtotime($row_venda_validade['data_atual']));} ?>
    </td>
    
    <td>
	<? echo $row_venda_validade['prazo']; ?>
    </td>
    
    <td style="text-align:center">
    <? echo $row_venda_validade['motivo']; ?>
    </td>
    
    <td>
    <? if($row_venda_validade['status']==1){echo "Sim";} ?>
    <? if($row_venda_validade['status']==0){echo "Não";} ?>
    </td>
    
  </tr>
<?php } while ($row_venda_validade = mysql_fetch_assoc($venda_validade)); ?>

</table>
<? } ?>
<!-- fim - dilacao -->

<div class="caixa_texto">
<? echo $row_parametros['venda_formulario_resumo_texto1']; ?>
</div>

<!-- implantacao -->
<table width="100%" height="20" border="0" cellspacing="0" cellpadding="0" style="margin-top: 20px; margin-bottom: 10px;">
  <tr>
    <td class="titulo_formulario" style="font-weight: bold; text-align: center;">
      IMPLANTAÇÃO 
     (
		<? 
		$implantacao_tempo_segundo = $row_venda['implantacao_tempo']*60;

		$tHoras = $implantacao_tempo_segundo / 3600;
		$tMinutos = $implantacao_tempo_segundo % 3600 / 60;
		
		echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
        ?>
      )
    </td>
  </tr>
</table>


<table cellspacing=0 cellpadding=0 width='100%' class="resumo">

<thead>
    <tr>
    	<th width="25%">DATA</th>
        <th>RECEPTOR</th>
        <th width="25%">DURAÇÃO</th>
        <th width="25%">INSTRUTOR</th>
    </tr>
</thead>

<tfoot>
    <tr>
        <td colspan="4" height="10">        
        </td>
    </tr>
</tfoot>
 
<? do{ ?>
<tr>

    <td align="center">
	<? if($row_venda_agenda_implantacao['data_inicio']!=""){echo date('d-m-Y H:i', strtotime($row_venda_agenda_implantacao['data_inicio']));} ?>
    </td>
    
    <td>
	<? echo $row_venda_agenda_implantacao['venda_receptor']; ?>
    </td>
    
    <td style="text-align:center">
    <?
    if($row_venda_agenda_implantacao['data_inicio']!="" and $row_venda_agenda_implantacao['data']!=""){
		$implantacao_tempo_segundo = strtotime($row_venda_agenda_implantacao['data'])-strtotime($row_venda_agenda_implantacao['data_inicio']);

		$tHoras = $implantacao_tempo_segundo / 3600;
		$tMinutos = $implantacao_tempo_segundo % 3600 / 60;
		
		echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
	}
	?>
    </td>
    
    <td>
    <? echo $row_venda_agenda_implantacao['nome']; ?>
    </td>
    
  </tr>
<?php } while ($row_venda_agenda_implantacao = mysql_fetch_assoc($venda_agenda_implantacao)); ?>

</table>
<!-- fim - implantacao -->


<div class="caixa_texto">
<? echo $row_parametros['venda_formulario_resumo_texto2']; ?>
</div>

<!-- treinamento -->
<table width="100%" height="20" border="0" cellspacing="0" cellpadding="0" style="margin-top: 20px; margin-bottom: 10px;">
  <tr>
    <td class="titulo_formulario" style="font-weight: bold; text-align: center;">
      TREINAMENTO 
     (
		<? 
		$treinamento_tempo_segundo = $row_venda['treinamento_tempo']*60;

		$tHoras = $treinamento_tempo_segundo / 3600;
		$tMinutos = $treinamento_tempo_segundo % 3600 / 60;
		
		echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
        ?>
      )
    </td>
  </tr>
</table>


<table cellspacing=0 cellpadding=0 width='100%' class="resumo">

<thead>
    <tr>
    	<th width="25%">DATA</th>
        <th>RECEPTOR</th>
        <th width="25%">DURAÇÃO</th>
        <th width="25%">INSTRUTOR</th>
    </tr>
</thead>

<tfoot>
    <tr>
        <td colspan="4" height="10">        
        </td>
    </tr>
</tfoot>
 
<? do{ ?>
<tr>

    <td align="center">
	<? if($row_venda_agenda_treinamento['data_inicio']!=""){echo date('d-m-Y H:i', strtotime($row_venda_agenda_treinamento['data_inicio']));} ?>
    </td>
    
    <td>
	<? echo $row_venda_agenda_treinamento['venda_receptor']; ?>
    </td>
    
    <td style="text-align:center">
    <?
    if($row_venda_agenda_treinamento['data_inicio']!="" and $row_venda_agenda_treinamento['data']!=""){
		$treinamento_tempo_segundo = strtotime($row_venda_agenda_treinamento['data'])-strtotime($row_venda_agenda_treinamento['data_inicio']);
		
		$tHoras = $treinamento_tempo_segundo / 3600;
		$tMinutos = $treinamento_tempo_segundo % 3600 / 60;
		
		echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
	}
	?>
    </td>
    
    <td>
    <? echo $row_venda_agenda_treinamento['nome']; ?>
    </td>
    
  </tr>
<?php } while ($row_venda_agenda_treinamento = mysql_fetch_assoc($venda_agenda_treinamento)); ?>

</table>
<!-- fim - treinamento -->

<div class="caixa_texto">
<? echo $row_parametros['venda_formulario_resumo_texto3']; ?>
</div>

<table cellspacing=0 cellpadding=0 width='100%' class="bordatransparente" style="margin-top: 40px;">
  <tr>
    <td width="50%" align="center" valign="top">
    <br>
    _______________________________________________
    <br>
    Receptor do Treinamento
    </td>
    
    <td width="50%" align="center" valign="top">
    <br>
    _______________________________________________
    <br>
    Instrutor
    </td>
  </tr>
</table>


<table cellspacing=0 cellpadding=0 width='100%' class="bordatransparente" style="margin-top: 40px;">
  <tr>
    <td width="50%" align="center" valign="top">
    <br>
    _______________________________________________
    <br>
    Representante Legal
    </td>
    
    <td width="50%" align="center" valign="top">
    <br>
    _______________________________________________
    <br>
    Success Sistemas & Inf. Ltda
    </td>
  </tr>
</table>


</body>
</html>
<?php
mysql_free_result($usuario);
mysql_free_result($venda);
mysql_free_result($manutencao_dados);
mysql_free_result($empresa_dados);
mysql_free_result($treinamento_pergunta);
mysql_free_result($implantacao_pergunta);
mysql_free_result($venda_modulos);
mysql_free_result($venda_agenda_treinamento);
mysql_free_result($venda_agenda_implantacao);
mysql_free_result($venda_validade);
?>