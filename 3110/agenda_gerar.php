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

// seleciona os EXECUTORES que o usuário atual tem acesso E montagem da query --------------------------------------------
$query_usuarios_geral_tipo_praca_executor = sprintf(" 
SELECT usuarios.praca, geral_tipo_praca_executor.praca, usuarios.IdUsuario, geral_tipo_praca_executor.IdExecutor
FROM usuarios 
INNER JOIN geral_tipo_praca_executor ON  usuarios.praca = geral_tipo_praca_executor.praca 
WHERE usuarios.IdUsuario = ".$row_usuario['IdUsuario']."
");
$usuarios_geral_tipo_praca_executor = mysql_query($query_usuarios_geral_tipo_praca_executor, $conexao) or die(mysql_error());	
$sql_clientes_vendedor17 = ""; 

		// lista os EXECUTORES - em cada EXECUTOR com acesso, é adicionado seu codigo a uma string
		while ($row_usuarios_geral_tipo_praca_executor = mysql_fetch_assoc($usuarios_geral_tipo_praca_executor)){
			$sql_clientes_vendedor17 .= "vendedor17 = '".$row_usuarios_geral_tipo_praca_executor['IdExecutor']."' or ";
		}
		// fim - lista os EXECUTORES - em cada EXECUTOR com acesso, é adicionado seu codigo a uma string

$sql_clientes_vendedor17 = substr($sql_clientes_vendedor17, 0, -4);
// fim - seleciona os EXECUTORES que o usuário atual tem acesso E montagem da query --------------------------------------

// cliente_antigo_listar
$where_cliente_antigo_listar = "1=1";

if ($row_usuario['controle_prospeccao']=="Y" or $row_usuario['praca']=="MATRIZ"){

} else {
	$where_cliente_antigo_listar .= " and $sql_clientes_vendedor17";
}

mysql_select_db($database_conexao, $conexao);
$query_cliente_antigo_listar = sprintf("
SELECT 
da37.codigo17, da37.cliente17, 
da01.nome1 
FROM da37 
INNER JOIN da01 ON da37.cliente17 = da01.codigo1 
WHERE $where_cliente_antigo_listar and da01.sr_deleted <> 'T' and da37.sr_deleted <> 'T' 
ORDER BY da01.nome1 ASC", 
GetSQLValueString($row_usuario['praca'], "text"));
$cliente_antigo_listar = mysql_query($query_cliente_antigo_listar, $conexao) or die(mysql_error());
$row_cliente_antigo_listar = mysql_fetch_assoc($cliente_antigo_listar);
$totalRows_cliente_antigo_listar = mysql_num_rows($cliente_antigo_listar);
// fim - cliente_antigo_listar

// agenda_responsavel para selectbox
$where_agenda_responsavel = "status = '1'";

if ($row_usuario['controle_prospeccao']=="Y" or $row_usuario['praca']=="MATRIZ"){

} else {
	$where_agenda_responsavel .= " and praca = '".$row_usuario['praca']."' ";
}

mysql_select_db($database_conexao, $conexao);
$query_agenda_responsavel = "SELECT IdUsuario, nome FROM usuarios WHERE $where_agenda_responsavel ";
$query_agenda_responsavel .= " ORDER BY nome ASC";
$agenda_responsavel = mysql_query($query_agenda_responsavel, $conexao) or die(mysql_error());
$row_agenda_responsavel = mysql_fetch_assoc($agenda_responsavel);
$totalRows_agenda_responsavel = mysql_num_rows($agenda_responsavel);
// fim - agenda_responsavel para selectbox

// tipo ramo_atividade
mysql_select_db($database_conexao, $conexao);
$query_geral_tipo_ramo_atividade = "SELECT * FROM geral_tipo_ramo_atividade ORDER BY titulo ASC";
$geral_tipo_ramo_atividade = mysql_query($query_geral_tipo_ramo_atividade, $conexao) or die(mysql_error());
$row_geral_tipo_ramo_atividade = mysql_fetch_assoc($geral_tipo_ramo_atividade);
$totalRows_geral_tipo_ramo_atividade = mysql_num_rows($geral_tipo_ramo_atividade);
// fim - tipo ramo_atividade

// insert
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "prospeccao")) {
	
	// usuario_responsavel		
	$colname_usuario_responsavel = "-1";
	if (isset($_POST['agenda_responsavel'])) {
		$colname_usuario_responsavel = $_POST['agenda_responsavel'];
	}
	mysql_select_db($database_conexao, $conexao);
	$query_usuario_responsavel = sprintf("SELECT IdUsuario, nome FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_usuario_responsavel, "int"));
	$usuario_responsavel = mysql_query($query_usuario_responsavel, $conexao) or die(mysql_error());
	$row_usuario_responsavel = mysql_fetch_assoc($usuario_responsavel);
	$totalRows_usuario_responsavel = mysql_num_rows($usuario_responsavel);
	// fim - usuario_responsavel

	// converter entrada de data em portugues para ingles
	if ( isset($_POST['data_agendamento_inicio']) and $_POST['data_agendamento_inicio'] != "" ) {
		$data_agendamento_inicio_data_inicio = substr($_POST['data_agendamento_inicio'],0,10);
		$data_agendamento_inicio_hora = substr($_POST['data_agendamento_inicio'],10,9);
		$_POST['data_agendamento_inicio'] = implode("-",array_reverse(explode("-",$data_agendamento_inicio_data_inicio))).$data_agendamento_inicio_hora;
	} else {
		$_POST['data_agendamento_inicio'] = "0000-00-00 00:00:00";
	}
	
	
	if ( isset($_POST['data_agendamento']) and $_POST['data_agendamento'] != "" ) {
		$data_agendamento_data = substr($_POST['data_agendamento'],0,10);
		$data_agendamento_hora = substr($_POST['data_agendamento'],10,9);
		$_POST['data_agendamento'] = implode("-",array_reverse(explode("-",$data_agendamento_data))).$data_agendamento_hora;
	} else {
		$_POST['data_agendamento'] = "0000-00-00 00:00:00";
	}
	// fim - converter entrada de data em portugues para ingles - fim
		
	// insert agenda
	$insertSQL_prospeccao_agenda = sprintf("
	INSERT INTO agenda (id_usuario_responsavel, data_inicio, data, data_criacao, status, descricao) 
	VALUES (%s, %s, %s, %s, %s, %s)",
	GetSQLValueString($row_usuario['IdUsuario'], "int"),
	GetSQLValueString($_POST['data_agendamento_inicio'], "date"),
	GetSQLValueString($_POST['data_agendamento'], "date"),
	GetSQLValueString(date("Y-m-d H:i:s"), "date"),
	GetSQLValueString("a", "text"), 
	GetSQLValueString($_POST['observacao'], "text"));
	
	mysql_select_db($database_conexao, $conexao);
	$Result_prospeccao_agenda = mysql_query($insertSQL_prospeccao_agenda, $conexao) or die(mysql_error());
	// fim - insert agenda
	
	// redireciona
	$insertGoTo = "agenda.php?padrao=sim";
	echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $insertGoTo);
	// fim - redireciona
	exit;
	
}
// fim - insert
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link rel="stylesheet" href="css/suporte.css" type="text/css" media="screen" />


<script type="text/javascript" src="js/jquery.js"></script>

<script type="text/javascript" src="js/jquery.maskedinput-1.2.2.min.js"></script> 
<script type="text/javascript" src="js/jquery.alphanumeric.pack.js"></script> 
<script type="text/javascript" src="js/jquery.rsv.js"></script> 

<link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen" />
<script type="text/javascript" src="js/thickbox.js"></script>

<link type="text/css" href="css/jquery-ui.css" rel="stylesheet" />	
<link type="text/css" href="css/jquery-ui-timepicker-addon.css" rel="stylesheet" />	

<script type="text/javascript" src="js/jquery-ui.js"></script>
<script type="text/javascript" src="js/jquery-ui-timepicker-addon.js"></script>

<script type="text/javascript" src="js/date.format.js"></script>

<script type="text/javascript" src="js/funcoes_data.js"></script>

<script type="text/javascript" src="js/funcao_js_valida_cpf_cnpj.js"></script>

<style>
/* calendário */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
/* fim - calendário */
</style>
<script type="text/javascript">
function myOnComplete(){return true;}

// função customizada (data_inicial_final_menor)
function data_inicial_final_menor()
{
	var data_inicio = document.getElementById("data_agendamento_inicio").value;
	var data_fim = document.getElementById("data_agendamento").value;	
	var is_valid = false;

	if(data_inicio != "" && data_fim != ""){
		var quebraDI=data_inicio.split("-");
		var diaDI = quebraDI[0];
		var mesDI = quebraDI[1];
		var anoDI = quebraDI[2].substr(0,4);
		var time_inicial = quebraDI[2].substr(5,8);
		var quebraTimeDI=time_inicial.split(":");
		var horaDI = quebraTimeDI[0];
		var minutoDI = quebraTimeDI[1];
		var segundoDI = quebraTimeDI[2];
		var date1 = anoDI+"-"+mesDI+"-"+diaDI+" "+horaDI+":"+minutoDI+":"+segundoDI;
		
		var quebraDF=data_fim.split("-");
		var diaDF = quebraDF[0];
		var mesDF = quebraDF[1];
		var anoDF = quebraDF[2].substr(0,4);
		var time_final = quebraDF[2].substr(5,8);
		var quebraTimeDF=time_final.split(":");
		var horaDF = quebraTimeDF[0];
		var minutoDF = quebraTimeDF[1];
		var segundoDF = quebraTimeDF[2];
		var date2 = anoDF+"-"+mesDF+"-"+diaDF+" "+horaDF+":"+minutoDF+":"+segundoDF;	
		
		if (date1 < date2){
			is_valid = true;
		}
		
		if (!is_valid)
		{
			var field = document.getElementById("data_agendamento_inicio");
			return [[field, "Data inicial maior que data final."]];
		}		
	}

	return true;
}
// fim - função customizada (data_inicial_final_menor)

$(document).ready(function() {
						   		
	// pega o primeiro campo habilitado
	setTimeout(function() {$('#form :input:visible:enabled:first').focus();}, 100);
	// fim - pega o primeiro campo habilitado

	// calendário -------------------------------------------------------------
	var data_agendamento_inicio = $('#data_agendamento_inicio');
	
	data_agendamento_inicio.datetimepicker({
							   
		showOn: "button",
		buttonImage: "css/images/calendar.gif",
		buttonImageOnly: true,									
		showSecond: false,
		minDateTime: new Date(<?php echo time() * 1000 ?>),
		inline: true,
		dateFormat: 'dd-mm-yy',
		timeFormat: 'HH:mm',
		dayNames: [
		'Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'
		],
		dayNamesMin: [
		'D','S','T','Q','Q','S','S','D'
		],
		dayNamesShort: [
		'Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'
		],
		monthNames: [
		'Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro',
		'Outubro','Novembro','Dezembro'
		],
		monthNamesShort: [
		'Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set',
		'Out','Nov','Dez'
		],
		nextText: 'Próximo',
		prevText: 'Anterior',
		closeText:"Fechar",
		currentText: "Agora",
		timeOnlyTitle: 'Escolha a hora',
		timeText: 'Horário',
		hourText: 'Hora',
		minuteText: 'Minuto',
		secondText: 'Segundo',
		beforeShow: function (selectedDateTime){
			
			$('#data_agendamento').val('');
			$('#agendamento_tempo').val('');
			
		},
		onChangeMonthYear: function(selectedDateTime) {
			$('#data_agendamento').val('');
			$('#agendamento_tempo').val('');
		},
		onClose: function(selectedDateTime){

			if(selectedDateTime=="  -  -       :  " || selectedDateTime==""){
				$('#data_agendamento').val('');
				$('#agendamento_tempo').val('');
			}
			
		},
		onSelect: function (selectedDateTime){
			$('#data_agendamento').val('');
			$('#agendamento_tempo').val('');
		}
		
	});
	// fim - calendario -------------------------------------------------------
	
	// agendamento_tempo
	$("select[name=agendamento_tempo]").change(function(){
														
		var agendamento_tempo = $(this).val();

		var data_inicio = $("#data_agendamento_inicio").val();
		var quebraDI=data_inicio.split("-");
		var diaDI = quebraDI[0];
		var mesDI = quebraDI[1];
		var anoDI = quebraDI[2].substr(0,4);
		var time_inicial = quebraDI[2].substr(5,8);
		var quebraTimeDI=time_inicial.split(":");
		var horaDI = quebraTimeDI[0];
		var minutoDI = quebraTimeDI[1];
		
		// current date
		var date = new Date(anoDI, mesDI-1, diaDI, horaDI, minutoDI, 0);
		
		// future date
		var new_date = new Date (date);
		
		var minutes = parseInt($("#agendamento_tempo").val());
		
		// Add the minutes to current date to arrive at the new date
		new_date.setMinutes ( date.getMinutes() + minutes );

		date1 = new_date.format('dd-mm-yyyy HH:MM'); // date.format.js
		
		$("#data_agendamento").val(date1);
		
	});
	// fim - agendamento_tempo
	
	// campos obrigatórios - coloca o asterisco*	
	$("label > #req").hide();
	
	<? if($cliente_novo_antigo=="a"){ ?>
		$("label[id=label_agenda_responsavel] > #req").show();
		$("label[id=label_cliente_antigo] > #req").show();
		$("label[id=label_ativo_passivo] > #req").show();
		
		$("label[id=label_responsavel_por_ti] > #req").show();
		$("label[id=label_ramo_de_atividade] > #req").show();
		$("label[id=label_observacao] > #req").show();
	<? } ?>

	<? if($cliente_novo_antigo=="n"){ ?>
		$("label[id=label_agenda_responsavel] > #req").show();
		$("label[id=label_ativo_passivo] > #req").show();
		
		$("label[id=label_titulo] > #req").show();
		
		$("label[id=label_cep] > #req").show();
		$("label[id=label_endereco] > #req").show();
		$("label[id=label_endereco_numero] > #req").show();
		$("label[id=label_bairro] > #req").show();
		$("label[id=label_cidade] > #req").show();
		$("label[id=label_uf] > #req").show();
		
		$("label[id=label_telefone] > #req").show();
		
		$("label[id=label_responsavel_por_ti] > #req").show();
		$("label[id=label_ramo_de_atividade] > #req").show();
		$("label[id=label_observacao] > #req").show();
	<? } ?>
	// fim - campos obrigatórios - coloca o asterisco*
	
    // Click no botão Botão: Salvar ---------------------------------------------------------------
	$('#button').click(function() {

        // consulta automática - agenda
        if($("input[name=data_agendamento_inicio]").val() != '' && $("input[name=data_agendamento]").val() != ''  && $("select[name=agenda_responsavel]").val() != '') {
            
            // post
            $.post("agenda_consulta.php", {
                   data_inicio: $("input[name=data_agendamento_inicio]").val(), 
                   data_fim: $("input[name=data_agendamento]").val(),
                   id_usuario_responsavel: $("select[name=agenda_responsavel]").val(),
                   id_agenda: 0
                   }, function(data) {

                        if(data == 0){
                            $('#form').submit();
                        }
                        if(data == 1){
                            alert("Data informada inválida. Existem agendamentos para o usuário atual durante período informado.");
                            $('#data_agendamento').val('');
                            $('#agendamento_tempo').val('');
                            return false;
                        }

                   }
            );
            // fim - post
        
        } else {
            
            $('#form').submit();
            
        }
        // fim - consulta automática - agenda

	});
	// fim - Click no botão Botão: Salvar ---------------------------------------------------------
	
	// validação
	var rules = [];	
	
	<? if($cliente_novo_antigo=="a"){ ?>
		rules.push("required,agenda_responsavel,Informe o responsável pela prospecção.");
		rules.push("required,cliente_antigo,Selecione o cliente.");
		rules.push("required,ativo_passivo,Informe o tipo de prospecção.");
		
		rules.push("required,responsavel_por_ti,Informe o responsável por TI.");
		rules.push("required,ramo_de_atividade,Informe o ramo de atividade.");
		rules.push("function, data_inicial_final_menor");
		rules.push("length>=10,observacao,Informe a observação com no mínimo 10 caracteres.");
	<? } ?>

	<? if($cliente_novo_antigo=="n"){ ?>
		rules.push("required,agenda_responsavel,Informe o responsável pela prospecção.");
		rules.push("required,ativo_passivo,Informe o tipo de prospecção.");
		
		rules.push("required,pessoa,Informe o tipo de pessoa (física/jurídica).");
		rules.push("length>1,titulo,Informe o nome/razão social.");
		
		rules.push("length>=8,cep,Informe o cep.");
		rules.push("length>=5,endereco,Informe o endereço (mínimo 5 caracteres).");
		rules.push("length>1,endereco_numero,Informe o número do endereço.");
		rules.push("length>1,bairro,Informe o bairro.");
		rules.push("length>1,cidade,Informe a cidade.");
		rules.push("length>1,uf,Informe o estado.");
		
		rules.push("length>=10,telefone,Informe o telefone com DDD (mínimo 10 caracteres).");
		
		rules.push("required,responsavel_por_ti,Informe o responsável por TI.");
		rules.push("required,ramo_de_atividade,Informe o ramo de atividade.");
		
		rules.push("function, data_inicial_final_menor");
		
		rules.push("length>=10,observacao,Informe a observação com no mínimo 10 caracteres.");
	<? } ?>

	$("#form").RSV({
			onCompleteHandler: myOnComplete,
			rules: rules
	});			
	// fim - validação
	
	// mascara
	$('#cep').mask('99999-999',{placeholder:" "});
	$('#data_agendamento_inicio').mask('99-99-9999 99:99',{placeholder:" "});
	
	$("input[name=cpf_cnpj]").numeric();
	$("input[name=rg_inscricao]").alphanumeric();
	// mascara - fim
	
	// tab/enter													 
	textboxes = $("input[type='text']:visible:enabled, input[type='submit']:visible:enabled, input[type='radio']:visible:enabled, select:visible:enabled, textarea:visible:enabled");	
	$(textboxes).keypress (checkForEnter);
	function checkForEnter (event) {
		
		if (event.keyCode == 9 || event.keyCode == 13) {
			
			// corrige problema - quando a agenda está aberta, o tab/enter fica com o FOCUS na hora_inicio
			if ( $("#TB_window").length ) { // verifica se o tb_show está sendo exibido
				setTimeout(function() {$('#observacao').focus();}, 100);
				event.preventDefault();
			} else {
				// ação do tab/enter
				currentBoxNumber = textboxes.index(this);	
				if (textboxes[currentBoxNumber + 1] != null) {
					nextBox = textboxes[currentBoxNumber + 1]
					setTimeout(function() {nextBox.focus();}, 100);
					event.preventDefault();
				}
				// fim - ação do tab/enter				
			}
			// fim - corrige problema
		}
	}
	// fim - tab/enter
	
    // abrir agenda
	$('#ver_agenda').click(function() {		
		id_usuario_envolvido = $("select[id='agenda_responsavel']").val();
		data_agendamento_inicio = $('#data_agendamento_inicio').val(); // pega a data inicial
		
		tb_show("Agenda","agenda_popup.php?id_usuario_atual="+id_usuario_envolvido+"&data_atual="+data_agendamento_inicio+"&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true","");		
		return false;
	});
	// fim - abrir agenda
	
	// cliente_antigo
	$("select[name='cliente_antigo']").change(function () { // ao mudar o valor do select

		var cliente_antigo_atual = $(this).val(); // lê o valor selecionado		
		$.post("prospeccao_consulta_cliente.php", 
			  {id:cliente_antigo_atual},
			  function(valor){

				  if( valor.pessoa1 == "F"){
					  $('input:radio[id="pessoa"]:nth(0)').attr('checked', true);
				  }
				  if( valor.pessoa1 == "J"){
					  $('input:radio[id="pessoa"]:nth(1)').attr('checked', true);
				  }
				  
				  $('#titulo').val(valor.nome1); 
				  $('#fantasia').val(valor.fantasia1);
				  $('#cpf_cnpj').val(valor.cgc1);
				  $('#rg_inscricao').val(valor.insc1);
				  $('#cep').val(valor.cep1);
				  $('#endereco').val(valor.endereco1);
				  $('#bairro').val(valor.bairro1);
				  $('#cidade').val(valor.cidade1);
				  $('#estado').val(valor.uf1);
				  $('#telefone').val(valor.telefone1);
				  $('#celular').val(valor.celular1);

			  }, "json"
		);

	});
	// fim - cliente_antigo
	
});
</script>

<title>Nova prospecção</title>
</head>

<body>
<form id="form" name="form" method="POST" action="<?php echo $editFormAction; ?>">

<input type="hidden" name="MM_insert" value="prospeccao" />

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Novo agendamento
		</td>

		<td style="text-align: right">
		&lt;&lt; <a href="agenda.php?padrao=sim&<? echo $agenda_padrao; ?>">Voltar</a> | 
		Usuário logado: <? echo $row_usuario['nome']; ?> |
		<a href="painel/padrao_sair.php">Sair</a>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left" width="365">
		<span class="label_solicitacao"><label id="label_agenda_responsavel">Responsável<span id="req">*</span>: </label></span>
        <select name="agenda_responsavel" id="agenda_responsavel" style="width: 320px;">
        <option value="">Selecione ...</option>
        <?php
        do {  
        ?>
        <option value="<?php echo $row_agenda_responsavel['IdUsuario']?>"
		<?php
		// caso tenha o usuário já definido
		if($row_usuario['IdUsuario'] != ""){
	        if (!(strcmp($row_agenda_responsavel['IdUsuario'], $row_usuario['IdUsuario']))) {echo "selected=\"selected\"";}
		}
		// caso tenha o usuário já definido		
		?>>
		<?php echo utf8_encode($row_agenda_responsavel['nome']); ?>
        </option>
        <?php
        } while ($row_agenda_responsavel = mysql_fetch_assoc($agenda_responsavel));
        $rows = mysql_num_rows($agenda_responsavel);
        if($rows > 0) {
        mysql_data_seek($agenda_responsavel, 0);
        $row_agenda_responsavel = mysql_fetch_assoc($agenda_responsavel);
        }
        ?>
        </select>
        </td>
        
		<td style="text-align: right">
        <span class="label_solicitacao"><label id="label_cliente_antigo">Cliente (<? echo $totalRows_cliente_antigo_listar; ?>):<span id="req">*</span></label></span>
        <br>
        <select name="cliente_antigo" id="cliente_antigo" style="width: 320px;">
        <option value="">Selecione ...</option>
        <?php
        do {  
        ?>
        <option value="<?php echo $row_cliente_antigo_listar['cliente17']?>"><?php echo utf8_encode($row_cliente_antigo_listar['nome1']); ?></option>
        <?php
        } while ($row_cliente_antigo_listar = mysql_fetch_assoc($cliente_antigo_listar));
        $rows = mysql_num_rows($cliente_antigo_listar);
        if($rows > 0) {
        mysql_data_seek($cliente_antigo_listar, 0);
        $row_cliente_antigo_listar = mysql_fetch_assoc($cliente_antigo_listar);
        }
        ?>
        </select>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left" width="945">
		<span class="label_solicitacao"><label id="label_titulo">Título:<span id="req">*</span></label></span>
		<input type="text" name="titulo" id="titulo" style="width: 945px;" value="">
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945" style="margin-top: 5px;">
    <tr>
        <td style="text-align:left">
        <span class="label_solicitacao"><label id="label_anomalia">Anomalia:<span id="req">*</span></label></span>
        <br>
        <textarea name="anomalia" id="anomalia" style="margin-top: 2px; width: 945px; height: 100px;"></textarea>
        </td>    
    </tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>        
		<td style="text-align:left" width="545">
		<span class="label_solicitacao"><label id="label_modulo">Módulo:<span id="req">*</span></label></span>
		<input type="text" name="modulo" id="modulo" style="width: 250px;" value="">
        </td>
        
        <td style="text-align:right">
		<span class="label_solicitacao"><label id="label_tipo">Tipo:<span id="req">*</span></label></span>
        <select name="tipo" id="tipo" style="width: 250px;">
        <option value="">Escolha ...</option>
        <option value=".">.</option>
        </select>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
	<div style="font-weight: bold; margin-bottom: 10px;">Agendamento</div>
    
    <table cellspacing="0" cellpadding="0" width="945">
        <tr>
            <td style="text-align:left" width="290">
            <span class="label_solicitacao"><label id="label_data_agendamento_inicio">Data inicio:<span id="req">*</span></label></span>
            <br>
            <input name="data_agendamento_inicio" type="text" id="data_agendamento_inicio" style="width: 150px;">
            <a href="#" id="ver_agenda" name="ver_agenda" style="color: #03C; font-weight: bold; padding-left: 5px;">Ver agenda</a>
            </td>
            
            <td style="text-align: left" width="200">
            <span class="label_solicitacao"><label id="label_agendamento_tempo">Tempo:<span id="req">*</span></label></span>
            <br>
            <select name="agendamento_tempo" id="agendamento_tempo" style="width: 175px;">
                <option value="">Escolha...</option>
                <option value="<? echo $mm = 15; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
                <option value="<? echo $mm = 30; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
                <option value="<? echo $mm = 45; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
                <option value="<? echo $mm = 60; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
                <option value="<? echo $mm = 120; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
                <option value="<? echo $mm = 180; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
                <option value="<? echo $mm = 240; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
                <option value="<? echo $mm = 300; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
            </select>
    		</td>
            
            <td align="left" width="200">
            <span class="label_solicitacao"><label id="label_data_agendamento">Data fim:<span id="req">*</span></label></span>
            <br>
            <input name="data_agendamento" type="text" id="data_agendamento" readonly="readonly" style="width: 150px;">
            </td>

            <td align="right">
            <span class="label_solicitacao"><label id="label_prioridade">Prioridade:<span id="req">*</span></label></span>
            <br>
            <select name="prioridade" id="prioridade" style="width: 300px;">
                <option value="b">Baixa</option>
                <option value="m">Média</option>
                <option value="a">Alta</option>
            </select>
    		</td>
            
        </tr>
    </table>

        
    <table cellspacing="0" cellpadding="0" width="945" style="margin-top: 5px;">
        <tr>
            <td style="text-align:left">
            <span class="label_solicitacao"><label id="label_observacao">Observação:<span id="req">*</span></label></span>
            <br>
            <textarea name="observacao" id="observacao" style="margin-top: 2px; width: 945px; height: 100px;"></textarea>
            </td>    
        </tr>
    </table>
</div>


<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>

    <td>
    
    <input type="button" name="button" id="button" value="Gravar dados" class="botao_geral" style="width: 150px">
    <input type="reset" name="button" id="button" value="Limpar dados" class="botao_geral" style="width: 150px">
    
	<input type="hidden" name="MM_update" value="prospeccao" />
    <input name="id_prospeccao" type="hidden" value="" />
	</td>

  </tr>
</table>
</div>

</form>

<div class="div_solicitacao_linhas2">*campos com preenchimento obrigatório</div>

</body>
</html>
<?php
mysql_free_result($usuario);
mysql_free_result($geral_tipo_ramo_atividade);
mysql_free_result($agenda_responsavel);
mysql_free_result($cliente_antigo_listar);
mysql_free_result($usuarios_geral_tipo_praca_executor);
?>