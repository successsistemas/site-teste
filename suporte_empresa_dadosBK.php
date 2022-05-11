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

require('suporte_funcao_update.php');
require('parametros.php');

$colname_empresa_dados = "-1";
$colname_empresa_dados = $_POST["codigo17"]; // contrato selecionado
$colname_controle_suporte = $_POST["controle_suporte"];
$colname_suporte_operador_parceiro = $_POST["suporte_operador_parceiro"];


// manutencao dados
mysql_select_db($database_conexao, $conexao);
$query_manutencao_dados = sprintf("
SELECT 
da37.codigo17, da37.cliente17, da37.status17, da37.obs17, da37.tpocont17, da37.visita17, da37.optacuv17, da37.versao17, da37.espmod17, da37.datcont17, versao17, espmod17, executor17, 
geral_tipo_contrato.descricao as tpocont17_descricao,
geral_tipo_visita.descricao as visita17_descricao,
geral_tipo_praca_executor.praca as executor17_praca 

FROM da37 
INNER JOIN geral_tipo_contrato ON da37.tpocont17 = geral_tipo_contrato.IdTipoContrato
INNER JOIN geral_tipo_visita ON da37.visita17 = geral_tipo_visita.IdTipoVisita
INNER JOIN geral_tipo_praca_executor ON da37.executor17 = geral_tipo_praca_executor.IdExecutor

WHERE da37.codigo17 = %s and da37.sr_deleted <> 'T'", 
GetSQLValueString($colname_empresa_dados, "text"));
$manutencao_dados = mysql_query($query_manutencao_dados, $conexao) or die(mysql_error());
$row_manutencao_dados = mysql_fetch_assoc($manutencao_dados);
$totalRows_manutencao_dados = mysql_num_rows($manutencao_dados);
// fim - manutencao dados

// empresa - dados
mysql_select_db($database_conexao, $conexao);
$query_empresa_dados = sprintf("
SELECT codigo1, nome1, fantasia1, contato1, endereco1, bairro1, cidade1, uf1, telefone1, comercio1, cep1, ultcompra1, atraso1, status1, flag1, tipo1
FROM da01 
WHERE codigo1 = %s and da01.sr_deleted <> 'T'", GetSQLValueString($row_manutencao_dados['cliente17'], "text"));
$empresa_dados = mysql_query($query_empresa_dados, $conexao) or die(mysql_error());
$row_empresa_dados = mysql_fetch_assoc($empresa_dados);
$totalRows_empresa_dados = mysql_num_rows($empresa_dados);
// fim - empresa dados

// $colname_contrato
$colname_contrato = $row_manutencao_dados['codigo17'];
// fim - $colname_contrato

// suporte_formulario_bonus_ultimo
mysql_select_db($database_conexao, $conexao);
$query_suporte_formulario_bonus_ultimo = sprintf("
SELECT suporte_formulario.data 
FROM suporte_formulario 
WHERE suporte_formulario.contrato = %s and suporte_formulario.status_flag = 'a' and suporte_formulario.visita_bonus='s'
ORDER BY suporte_formulario.IdFormulario DESC 
LIMIT 1 
", 
GetSQLValueString($colname_contrato, "text"));
$suporte_formulario_bonus_ultimo = mysql_query($query_suporte_formulario_bonus_ultimo, $conexao) or die(mysql_error());
$row_suporte_formulario_bonus_ultimo = mysql_fetch_assoc($suporte_formulario_bonus_ultimo);
$totalRows_suporte_formulario_bonus_ultimo = mysql_num_rows($suporte_formulario_bonus_ultimo);
// fim - suporte_formulario_bonus_ultimo

// suportes em andamento
mysql_select_db($database_conexao, $conexao);
$query_suportes_em_andamento = sprintf("
SELECT id, data_suporte, status, tipo_suporte, inloco, cobranca 
FROM suporte 
WHERE contrato = %s and situacao <> 'solucionada' and situacao <> 'cancelada'
ORDER BY id ASC", GetSQLValueString($colname_empresa_dados, "text"));
$suportes_em_andamento = mysql_query($query_suportes_em_andamento, $conexao) or die(mysql_error());
$row_suportes_em_andamento = mysql_fetch_assoc($suportes_em_andamento);
$totalRows_suportes_em_andamento = mysql_num_rows($suportes_em_andamento);
// fim - suportes em andamento

// suportes encerrados
mysql_select_db($database_conexao, $conexao);
$query_suportes_encerrados = sprintf("
SELECT id, data_suporte, status, tipo_suporte, inloco, cobranca 
FROM suporte 
WHERE contrato = %s and situacao = 'solucionada' 
ORDER BY id DESC LIMIT 3", GetSQLValueString($colname_empresa_dados, "text"));
$suportes_encerrados = mysql_query($query_suportes_encerrados, $conexao) or die(mysql_error());
$row_suportes_encerrados = mysql_fetch_assoc($suportes_encerrados);
$totalRows_suportes_encerrados = mysql_num_rows($suportes_encerrados);
// fim - suportes encerrados

// função consulta créditos (contrato)
$creditos = funcao_suporte_credito($row_manutencao_dados['codigo17']);
// fim - função consulta créditos (contrato)

// reclamacao_consulta
mysql_select_db($database_conexao, $conexao);
$query_reclamacao_consulta = sprintf("
SELECT id, empresa, situacao, status_flag     
FROM suporte 
WHERE contrato = %s and tipo_suporte = 'r' and 
((status_flag = 'a') or (status_flag = 'f' and DATE_ADD(data_fim,INTERVAL ".$row_parametros['suporte_reclamacao_mensagem_inicial_dias']." DAY) >= now()))
", 
GetSQLValueString($row_manutencao_dados['codigo17'], "text"));
$reclamacao_consulta = mysql_query($query_reclamacao_consulta, $conexao) or die(mysql_error());
$row_reclamacao_consulta = mysql_fetch_assoc($reclamacao_consulta);
$totalRows_reclamacao_consulta = mysql_num_rows($reclamacao_consulta);

$reclamacao_consulta_status = 0;
$reclamacao_consulta_mensagem_aberta = NULL;
$reclamacao_consulta_mensagem_fechada = NULL;
do {

	if($row_reclamacao_consulta['status_flag'] == "f"){
		$reclamacao_consulta_mensagem_fechada .= 'Reclamação: '.$row_reclamacao_consulta['id'].' - Situação: '.$row_reclamacao_consulta['situacao'].'\n';
	} else {
		$reclamacao_consulta_status = 1;
		$reclamacao_consulta_mensagem_aberta .= 'Reclamação: '.$row_reclamacao_consulta['id'].' - Situação: '.$row_reclamacao_consulta['situacao'].'\n';
	}
	
} while ($row_reclamacao_consulta = mysql_fetch_assoc($reclamacao_consulta));

$reclamacao_consulta_mensagem_corpo = NULL;
if($reclamacao_consulta_status == 0){
	$reclamacao_consulta_mensagem = 'ATENÇÃO:\nRECLAMAÇÃO REGISTRADA RECENTEMENTE\nCliente: '.utf8_encode($row_empresa_dados['nome1']).'\n'.$reclamacao_consulta_mensagem_fechada;
	$reclamacao_consulta_mensagem_corpo = 'ATENÇÃO: CLIENTE COM RECLAMAÇÃO REGISTRADA RECENTEMENTE';
} else if($reclamacao_consulta_status == 1){
	$reclamacao_consulta_mensagem = 'ATENÇÃO:\nRECLAMAÇÃO EM ANDAMENTO\nCliente: '.utf8_encode($row_empresa_dados['nome1']).'\n'.$reclamacao_consulta_mensagem_aberta;
	$reclamacao_consulta_mensagem_corpo = 'ATENÇÃO: CLIENTE COM RECLAMAÇÃO EM ANDAMENTO';
}
// fim - reclamacao_consulta
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script type="text/javascript">
$(document).ready(function() {
						   
	// validação
	$("#form_clientes").validate({
		onkeyup: false
	});
	// fim - validação
	
});
</script>
<style>
.linha1{
	border-top: 1px solid #E5E5E5;
	border-bottom: 1px solid #E5E5E5;
	background-color: #F6F6F6;
	margin-top: 3px;
	margin-bottom: 3px;
	padding-top: 3px;
	padding-bottom: 3px;
	padding-left: 5px;
	padding-right: 5px;
}
.linha2{
	border-top: 1px solid #E5E5E5;
	border-bottom: 1px solid #E5E5E5;
	background-color: #FFFFFF;
	margin-top: 3px;
	margin-bottom: 3px;
	padding-top: 3px;
	padding-bottom: 3px;
	padding-left: 5px;
	padding-right: 5px;
}
</style>
<div>
<strong>Dados do cliente selecionado:</strong>

<div class="linha1">
<strong><?php echo utf8_encode($row_empresa_dados['nome1']); ?></strong> |
Fantasia: <?php echo utf8_encode($row_empresa_dados['fantasia1']); ?> |
Fone: <?php echo $row_empresa_dados['telefone1']; ?> | <?php if($row_empresa_dados['comercio1'] > 0){ ?><?php echo $row_empresa_dados['comercio1']; ?> | <? } ?>
Contato: <?php echo $row_empresa_dados['contato1']; ?> | Praça: <? echo $row_manutencao_dados['executor17_praca']; ?>
</div>

<div class="linha1">
<? echo utf8_encode($row_empresa_dados['endereco1']); ?> - <?php echo utf8_encode($row_empresa_dados['bairro1']); ?> - CEP: <?php echo $row_empresa_dados['cep1']; ?> |
<?php echo utf8_encode($row_empresa_dados['cidade1']); ?> - <?php echo $row_empresa_dados['uf1']; ?>
</div>

<? if($totalRows_reclamacao_consulta > 0){ ?>
<div class="linha1" style="color: red;">
<? echo $reclamacao_consulta_mensagem_corpo; ?>
</div>
<? } ?>

<!-- se possui contrato -->
<? if($totalRows_manutencao_dados > 0){ ?>

	<!-- dados -->
    <div class="linha1">
        Contrato: <strong><?php echo $row_manutencao_dados['codigo17']; ?></strong> |     
        Tipo do contrato: <strong><?php echo $row_manutencao_dados['tpocont17_descricao']; ?></strong> | 
        Tipo de visita: <strong><?php echo $row_manutencao_dados['visita17_descricao']; ?></strong> | 
        Optante por acumulo de manutenção: <strong><?php if($row_manutencao_dados['optacuv17']=="N"){echo "Não";} 
													if($row_manutencao_dados['optacuv17']=="S"){echo "Sim";} ?></strong>
        <br>
        Versão: <strong>
        <?php if($row_manutencao_dados['versao17']=="1"){ echo "DOS";} ?>
		<?php if($row_manutencao_dados['versao17']=="2"){ echo "Windows";} ?>
        </strong> | 
        Distribuição: <strong>
        <?php if($row_manutencao_dados['espmod17']=="B"){ echo "Standard";} ?>
		<?php if($row_manutencao_dados['espmod17']=="O"){ echo "Office";} ?>
        </strong>
                
        <br>
        Status manutenção: 
        <strong>
        <?php 
        if($row_manutencao_dados['status17']=="D"){echo "Desbloqueado";}
        if($row_manutencao_dados['status17']=="B"){echo "<font color='red'>Bloqueado</font>";}
        if($row_manutencao_dados['status17']=="C"){echo "Cancelado";}
		if($row_manutencao_dados['status17']=="P"){echo "Pendente";}
		if($row_manutencao_dados['status17']=="S"){echo "Suspenso";}
        ?> | 
        </strong>
        Status manual: 
        <strong>
        <?php 
        if($row_empresa_dados['status1']=="0"){echo "Desbloqueado";} // manual
        if($row_empresa_dados['status1']=="1"){echo "<font color='red'>Bloqueado</font>";} // manual
        ?> | 
        </strong>
        Status automático: 
        <strong>
        <?php 
        if($row_empresa_dados['flag1']=="0"){echo "Desbloqueado";} // autom
        if($row_empresa_dados['flag1']=="1"){echo "<font color='red'>Bloqueado</font>";} // autom
        ?>
        </strong>
        <br>
        Data contrato: <strong><? echo date('d-m-Y', strtotime($row_manutencao_dados['datcont17'])); ?></strong> | 
        Última compra: <strong><? echo date('d-m-Y', strtotime($row_empresa_dados['ultcompra1'])); ?></strong> | 
        Total dias em atraso: <strong><?php echo $row_empresa_dados['atraso1']; ?></strong> | 
        Crédito(s) de visita(s):
		<? echo $creditos; ?> | 
        Direito a visita bônus: 
		<strong>		
		<? if(
		$creditos==0 and 
		($row_manutencao_dados['visita17'] == "3" or $row_manutencao_dados['visita17'] == "4") and 
		$row_empresa_dados['tipo1'] == 'O' and 
		((strtotime(date('Y-m', strtotime($row_suporte_formulario_bonus_ultimo['data'])))) <> (strtotime(date('Y-m')))) // ultimo bonus diferente do mês atual
		){ ?>Sim<? } else { ?>Não<? } ?>
		</strong> 
    </div>

    <div class="linha1">
	    Observações: <?php echo utf8_encode($row_manutencao_dados['obs17']); ?>
    </div>
	<!-- fim - dados -->
    
	<!-- se possui suportes_encerrados -->
	<? if($totalRows_suportes_encerrados > 0){ ?>
    <div class="linha1">
                <em>Suporte(s) encerrado(s) (últimos 3):</em>
                <?php do { ?>
                    <div style="padding-top: 5px; padding-bottom: 5px;">
                    <?php echo $row_suportes_encerrados['id']; ?> | 
                    <? echo date('d-m-Y H:i', strtotime($row_suportes_encerrados['data_suporte'])); ?> | 
                    Para: <?php if($row_suportes_encerrados['tipo_suporte']=='c'){echo "CLI";} if($row_suportes_encerrados['tipo_suporte']=='p'){echo "PAR";} if($row_suportes_encerrados['tipo_suporte']=='r'){echo "REC";} ?> | 
                    In-loco: <?php if($row_suportes_encerrados['inloco']=='s'){echo "Sim";} if($row_suportes_encerrados['inloco']=='n'){echo "Não";} ?> | 
                    <?php if($row_suportes_encerrados['cobranca']=="s"){echo "Cobrança | ";} ?> 
                    <?php echo $row_suportes_encerrados['status']; ?>

                    [ <a href="suporte_editar.php?id_suporte=<?php echo $row_suportes_encerrados['id']; ?>&padrao=sim">acessar</a> ]
                    </div>
                <?php } while ($row_suportes_encerrados = mysql_fetch_assoc($suportes_encerrados)); ?>
    </div>
    <? } ?>
	<!-- fim - se possui suportes_encerrados -->

	<!-- se possui suportes_em_andamento -->
	<? if($totalRows_suportes_em_andamento > 0){ ?>
    <div class="linha1">
                <em>Suporte(s) em andamento:</em>
                <?php do { ?>
                    <div style="padding-top: 5px; padding-bottom: 5px;">
                    <?php echo $row_suportes_em_andamento['id']; ?> | 
                    <? echo date('d-m-Y H:i', strtotime($row_suportes_em_andamento['data_suporte'])); ?> | 
                    Para: <?php if($row_suportes_em_andamento['tipo_suporte']=='c'){echo "CLI";} if($row_suportes_em_andamento['tipo_suporte']=='p'){echo "PAR";} if($row_suportes_em_andamento['tipo_suporte']=='r'){echo "REC";} ?> | 
                    In-loco: <?php if($row_suportes_em_andamento['inloco']=='s'){echo "Sim";} if($row_suportes_em_andamento['inloco']=='n'){echo "Não";} ?> | 
					<?php if($row_suportes_em_andamento['cobranca']=="s"){echo "Cobrança | ";} ?> 
                    <?php echo $row_suportes_em_andamento['status']; ?> 

                    [ <a href="suporte_editar.php?id_suporte=<?php echo $row_suportes_em_andamento['id']; ?>&padrao=sim">acessar</a> ]
                    </div>
                <?php } while ($row_suportes_em_andamento = mysql_fetch_assoc($suportes_em_andamento)); ?>
    </div>
    <? } ?>
	<!-- fim - se possui suportes_em_andamento -->
    
    <!-- mensagens -->
    <? if($row_empresa_dados['status1']=="0" and $row_empresa_dados['flag1']=="0"){ ?>
        
        <? if($creditos < 0){ ?>
        <div class="linha1" style="color:#F00;">
        Somente poderá ser gerado um novo suporte in-loco:sim com o tipo de formulário EXTRA ou TREINAMENTO.
        </div>
        <? } ?>
        
    <? } else { ?>
    
        <div class="linha1" style="color:#F00;">
        Cliente bloqueado, não é possível gerar um novo suporte.
        </div>    
    
    <? } ?>
    <!-- fim - mensagens -->
    
    <!-- suporte aos clientes ------------------------------------- -->
    <table border="0" cellspacing="0" cellpadding="0" style="margin-top: 10px; margin-bottom: 10px;">
    <tr>
    <? if($row_empresa_dados['status1']=="0" and $row_empresa_dados['flag1']=="0"){ ?>
    	
        <td>
        <!-- suporte comum -->
        <form action="suporte_gerar.php" name="form_clientes" id="form_clientes" method="get" class="cmxform">
        <table border="0" cellspacing="0" cellpadding="0">
        <tr>
        <td align="left" valign="top" width="230">
        <input type="submit"  name="botao_inferior" value="Gerar novo suporte ao cliente" class="botao_geral2" style="width:220px;" /> 
        <input type="hidden" name="tipo_suporte" value="c">    
        <input type="hidden" name="cobranca" value="n">
        <input type="hidden" name="cliente" value="<?php echo $row_empresa_dados['codigo1']; ?>">
        <input type="hidden" name="contrato" value="<?php echo $row_manutencao_dados['codigo17']; ?>">
        </td>
        <td>
        <fieldset style="border: 0px solid #F00;">
        Atendimento in-loco: 
        <input name="inloco" type="radio" value="n" validate="required:true" style="border: 0px"> Não 
        
        <? if ($_SESSION['MM_praca'] == $row_manutencao_dados['executor17_praca']){ ?>
        <input name="inloco" type="radio" value="s" validate="required:true" style="border: 0px"> Sim
        <? } ?>
        <label for="inloco" class="error"> - Selecione uma das opções</label>
        </fieldset>
        </td>
        </tr> 
        </table>
        </form>
        <!-- suporte comum -->
        </td>
        
    <? } else { ?>  
 
        <!-- suporte para auxílio cobrança -->
        <? if($creditos > -1){ ?>
        
        	<td>
            <form action="suporte_gerar.php" name="form_clientes" id="form_clientes" method="get" class="cmxform">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
            <td align="left" valign="top" width="230">
            <input type="submit"  name="botao_inferior" value="Gerar novo suporte para auxílio cobrança" class="botao_geral2" style="width:280px;" /> 
            <input type="hidden" name="tipo_suporte" value="c">
            <input type="hidden" name="inloco" value="s">
            <input type="hidden" name="cobranca" value="s">
            <input type="hidden" name="cliente" value="<?php echo $row_empresa_dados['codigo1']; ?>">
            <input type="hidden" name="contrato" value="<?php echo $row_manutencao_dados['codigo17']; ?>">
            </td>
            </tr> 
            </table>
            </form>
            </td>
            
        <? } ?>
        <!-- fim - suporte para auxílio cobrança -->
    
    <? } ?>
    
    <? if($row_empresa_dados['status1']=="0" and ($row_manutencao_dados['status17'] <> "C" and $row_manutencao_dados['status17'] <> "B")){ ?>
    <td>
    <!-- reclamacao -->
    <form action="suporte_gerar.php" name="form_reclamacoes" id="form_reclamacoes" method="get" class="cmxform">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-left: 20px;">
    <tr>
    <td align="left" valign="top" width="230">      
    <input type="submit"  name="botao_inferior" value="Registrar reclamação" class="botao_geral2" style="width:180px;" />
    <input type="hidden" name="tipo_suporte" value="r">    
    <input type="hidden" name="cobranca" value="n">
    <input type="hidden" name="cliente" value="<?php echo $row_empresa_dados['codigo1']; ?>">
    <input type="hidden" name="contrato" value="<?php echo $row_manutencao_dados['codigo17']; ?>">
    <input type="hidden" name="inloco" value="n">        
    </td>
    </tr>
    </table>
    </form>
    <!-- fim - reclamacao -->
    </td>
    <? } ?>
    
    </tr>
    </table>
    <!-- fim - suporte aos clientes ------------------------------- -->
    
    <!-- suporte aos parceiros ------------------------------------ -->
	<? if ($colname_controle_suporte=="Y" or $colname_suporte_operador_parceiro=="Y"){ ?>
    <div class="linha2"></div>
    <form action="suporte_gerar.php" name="form_parceiros" id="form_parceiros" method="get" class="cmxform">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 10px; margin-bottom: 10px;">
    <tr>
    <td align="left" valign="top" width="230">      
    <input type="submit"  name="botao_inferior" value="Gerar novo suporte ao parceiro" class="botao_geral2" style="width:220px;" />
    <input type="hidden" name="tipo_suporte" value="p">    
    <input type="hidden" name="cobranca" value="n">
    <input type="hidden" name="cliente" value="<?php echo $row_empresa_dados['codigo1']; ?>">
    <input type="hidden" name="contrato" value="<?php echo $row_manutencao_dados['codigo17']; ?>">
    <input type="hidden" name="inloco" value="n">        
    </td>
    </tr>
    </table>
    </form>
    <? } ?>
    <!-- fim - suporte aos parceiros ------------------------------ -->
    
    <!-- reclamacao_consulta -->
    <? if($totalRows_reclamacao_consulta > 0){ ?>
        
        <script>
        alert('<? echo $reclamacao_consulta_mensagem; ?>');
        </script>
        
    <? } ?>
    <!-- fim - reclamacao_consulta -->

<? } ?>
<!-- fim - se possui contrato -->
</div>

<?php
mysql_free_result($empresa_dados);
mysql_free_result($manutencao_dados);
mysql_free_result($suporte_formulario_bonus_ultimo);
mysql_free_result($suportes_em_andamento);
mysql_free_result($suportes_encerrados);
mysql_free_result($reclamacao_consulta); 
?>