<?
// função update suporte/descrição
function funcao_suporte_update($id, $dados_suporte, $dados_suporte_descricao){
	require('Connections/conexao.php');
	mysql_select_db($database_conexao, $conexao);
	//----------------------------------------------------------------------------------------------------------------------------------------------//
	if ((count($dados_suporte) > 0 and count($dados_suporte_descricao) > 0)){ // atualiza solic. e insere desc.
	//----------------------------------------------------------------------------------------------------------------------------------------------//
			
	// lista os campos que estão sendo passados e monta o WHERE
	$where_suporte = "";
	foreach ($dados_suporte as $campo => $retorno) {

			// parametros
			switch ($campo){
					case "tipo_suporte": $retorno = GetSQLValueString($retorno, "text"); break;
					case "inloco": $retorno = GetSQLValueString($retorno, "text"); break;
					case "data_suporte": $retorno = GetSQLValueString($retorno, "text"); break;
					case "data_suporte_fim": $retorno = GetSQLValueString($retorno, "text"); break;
					case "data_inicio": $retorno = GetSQLValueString($retorno, "text"); break;
					case "data_fim": $retorno = GetSQLValueString($retorno, "text"); break; // data/hora fim
					case "titulo": $retorno = GetSQLValueString($retorno, "text"); break;
					case "titulo_anterior": $retorno = GetSQLValueString($retorno, "text"); break;
					case "empresa": $retorno = GetSQLValueString($retorno, "text"); break;
					case "codigo_empresa": $retorno = GetSQLValueString($retorno, "text"); break;
					case "praca": $retorno = GetSQLValueString($retorno, "text"); break;
					case "contrato": $retorno = GetSQLValueString($retorno, "text"); break;
					case "id_usuario_envolvido": $retorno = GetSQLValueString($retorno, "int"); break;	
					case "id_usuario_responsavel": $retorno = GetSQLValueString($retorno, "int"); break;
					case "solicitante": $retorno = GetSQLValueString($retorno, "text"); break;
					case "modulo": $retorno = GetSQLValueString($retorno, "text"); break;
					case "tipo_atendimento": $retorno = GetSQLValueString($retorno, "text"); break;
					case "anomalia": $retorno = GetSQLValueString(nl2br($retorno), "text"); break;
					case "orientacao": $retorno = GetSQLValueString(nl2br($retorno), "text"); break;
					case "parecer": $retorno = GetSQLValueString($retorno, "text"); break;
					case "status": $retorno = GetSQLValueString($retorno, "text"); break;
					case "status_flag": $retorno = GetSQLValueString($retorno, "text"); break;
					case "observacao": $retorno = GetSQLValueString(nl2br($retorno), "text"); break; // nl2br = coloca <br> no textarea.
					case "tela": $retorno = GetSQLValueString($retorno, "text"); break;
					case "id_solicitacao": $retorno = GetSQLValueString($retorno, "int"); break;
					case "solicita_suporte": $retorno = GetSQLValueString($retorno, "text"); break;
					case "solicita_visita": $retorno = GetSQLValueString($retorno, "text"); break;
					case "id_formulario": $retorno = GetSQLValueString($retorno, "int"); break;
					case "tipo_formulario": $retorno = GetSQLValueString($retorno, "text"); break;
					case "final_parecer": $retorno = GetSQLValueString($retorno, "text"); break;
					case "final_situacao": $retorno = GetSQLValueString($retorno, "date"); break;
					case "final_status": $retorno = GetSQLValueString($retorno, "text"); break;
					case "final_solicita_suporte": $retorno = GetSQLValueString($retorno, "text"); break;
					case "final_solicita_visita": $retorno = GetSQLValueString($retorno, "text"); break;
					case "estorno": $retorno = GetSQLValueString($retorno, "text"); break;
					case "estorno_justificativa": $retorno = GetSQLValueString($retorno, "text"); break;
					case "acao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "ordem_servico": $retorno = GetSQLValueString($retorno, "text"); break;
					case "data_solicita_suporte": $retorno = GetSQLValueString($retorno, "text"); break;
					case "data_solicita_suporte_aceita_recusa": $retorno = GetSQLValueString($retorno, "text"); break;
					case "recomendacao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "cobranca": $retorno = GetSQLValueString($retorno, "text"); break;
					case "cobranca_recebimento": $retorno = GetSQLValueString($retorno, "text"); break;
					case "cobranca_recebimento_justificativa": $retorno = GetSQLValueString($retorno, "text"); break;
					case "cobranca_documento_vinculado": $retorno = GetSQLValueString($retorno, "text"); break;
					case "avaliacao_atendimento": $retorno = GetSQLValueString($retorno, "text"); break;
					case "avaliacao_atendimento_justificativa": $retorno = GetSQLValueString($retorno, "text"); break;
					case "usuario_responsavel_leu": $retorno = GetSQLValueString($retorno, "date"); break;
					case "usuario_envolvido_leu": $retorno = GetSQLValueString($retorno, "date"); break;
					case "situacao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "encaminhamento_id": $retorno = GetSQLValueString($retorno, "text"); break;
					case "encaminhamento_data": $retorno = GetSQLValueString($retorno, "date"); break;
					case "encaminhamento_data_inicio": $retorno = GetSQLValueString($retorno, "text"); break;
					case "solicita_solicitacao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "status_devolucao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "status_recusa": $retorno = GetSQLValueString($retorno, "text"); break;
					case "dt_validacao": $retorno = GetSQLValueString($retorno, "date"); break;
					case "previsao_geral_inicio": $retorno = GetSQLValueString($retorno, "date"); break;
					case "previsao_geral": $retorno = GetSQLValueString($retorno, "date"); break;
					case "reagendamento": $retorno = GetSQLValueString($retorno, "int"); break;
					case "reagendamento_solicitante": $retorno = GetSQLValueString($retorno, "text"); break;
					case "creditar": $retorno = GetSQLValueString($retorno, "text"); break;
					case "credito": $retorno = GetSQLValueString($retorno, "int"); break;
					case "contato": $retorno = GetSQLValueString($retorno, "int"); break;
					case "prioridade": $retorno = GetSQLValueString($retorno, "text"); break;
					case "prioridade_justificativa": $retorno = GetSQLValueString($retorno, "text"); break;
					case "status_questionamento": $retorno = GetSQLValueString($retorno, "text"); break;
					case "encerramento_automatico": $retorno = GetSQLValueString($retorno, "text"); break;
					case "encerramento_automatico_data": $retorno = GetSQLValueString($retorno, "text"); break;
					case "valor": $retorno = GetSQLValueString($retorno, "text"); break;
					case "reclamacao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "reclamacao_questionamento": $retorno = GetSQLValueString($retorno, "text"); break;
					case "reclamacao_percepcao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "reclamacao_data_acordada": $retorno = GetSQLValueString($retorno, "text"); break;
					case "reclamacao_responsavel": $retorno = GetSQLValueString($retorno, "text"); break;
					case "reclamacao_telefone": $retorno = GetSQLValueString($retorno, "text"); break;
					case "reclamacao_vinculo": $retorno = GetSQLValueString($retorno, "text"); break;
					case "reclamacao_solicitacao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "reclamacao_suporte": $retorno = GetSQLValueString($retorno, "text"); break;
					case "reclamacao_prospeccao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "reclamacao_venda": $retorno = GetSQLValueString($retorno, "text"); break;
					case "envolvido_reclamacao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "interacao": $retorno = GetSQLValueString($retorno, "int"); break;
					case "adiantamento_visita": $retorno = GetSQLValueString($retorno, "text"); break;
					case "visita_bonus": $retorno = GetSQLValueString($retorno, "text"); break;
					case "versao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "prazo_anexo_liberar": $retorno = GetSQLValueString($retorno, "text"); break;
					case "solucionado": $retorno = GetSQLValueString($retorno, "text"); break;
					case "solucionado_nao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "anomalia_simulada": $retorno = GetSQLValueString($retorno, "text"); break;
					case "anomalia_simulada_afirmacao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "data_conclusao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "atendimento": $retorno = GetSQLValueString($retorno, "text"); break;
					case "atendimento_cliente": $retorno = GetSQLValueString($retorno, "text"); break;
					case "atendimento_IdUsuario": $retorno = GetSQLValueString($retorno, "text"); break;
					case "atendimento_data": $retorno = GetSQLValueString($retorno, "text"); break;
					case "atendimento_motivo": $retorno = GetSQLValueString($retorno, "text"); break;
					case "atendimento_local": $retorno = GetSQLValueString($retorno, "text"); break;
					case "atendimento_previsao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "atendimento_texto": $retorno = GetSQLValueString($retorno, "text"); break;
					case "atendimento_status": $retorno = GetSQLValueString($retorno, "text"); break;	
			}
			// parametros

			$where_suporte .= "$campo = $retorno, "; // mostra nome do campo e valor passado		
	}
	$where_suporte = substr($where_suporte, 0, -2); // tira a última virgula
	// fim - lista os campos que estão sendo passados e monta o WHERE

	$updateSuporte = "UPDATE suporte SET $where_suporte WHERE id =".GetSQLValueString($id, "int");
	$ResultSuporte = mysql_query($updateSuporte, $conexao) or die(mysql_error());

	// limpa os campos
	$where_suporte = "";
	$dados_suporte = array();
	$campo = "";
	$retorno = "";
	$dados_suporte="";
	$updateSuporte = "";
	$ResultSuporte = "";
	// fim - limpa os campos

	}
	//----------------------------------------------------------------------------------------------------------------------------------------------//
	// insert descrição
	
	// lista os campos que estão sendo passados
	$campos_suporte = "";
	foreach ($dados_suporte_descricao as $campo => $retorno) {
			$campos_suporte .= "$campo, ";			
	}
	$campos_suporte = substr($campos_suporte, 0, -2);
	// fim - lista os campos que estão sendo passados
	
	// limpa os campos			
	$campo = "";
	$retorno = "";
			
	// lista os valores que estão sendo passados
	$valores_suporte = "";
	foreach ($dados_suporte_descricao as $campo => $retorno) {
		
			// parametros			
			switch ($campo){
				case "id_suporte": $retorno = GetSQLValueString($retorno, "int"); break;
				case "descricao": $retorno = GetSQLValueString(nl2br($retorno), "text"); break; // nl2br = coloca <br> no textarea.
				case "data": $retorno = GetSQLValueString($retorno, "date"); break;
				case "tipo_postagem": $retorno = GetSQLValueString($retorno, "text"); break;
				case "id_usuario_responsavel": $retorno = GetSQLValueString($retorno, "int"); break;
				case "questionado": $retorno = GetSQLValueString($retorno, "text"); break;
			}
			// parametros
			
			$valores_suporte .= "$retorno, ";
	}
	$valores_suporte = substr($valores_suporte, 0, -2);
	// fim - lista os valores que estão sendo passados
	
	$insertSuporteDescricao  = "INSERT INTO suporte_descricoes ($campos_suporte) VALUES ($valores_suporte)";
	$ResultSuporteDescricao = mysql_query($insertSuporteDescricao, $conexao) or die(mysql_error());			
	
	// limpa os campos
	$campos_suporte = "";
	$valores_suporte = "";
	$dados_suporte_descricao = array();
	$campo = "";
	$retorno = "";
	$insertSuporteDescricao = "";
	$ResultSuporteDescricao = "";
		
	// fim - insert descrição	
	//----------------------------------------------------------------------------------------------------------------------------------------------//
}
// fim - função update suporte/descrição


// funcao_suporte_interacao
function funcao_suporte_interacao($id_suporte, $interacao_atual){

	require('Connections/conexao.php');
	

	mysql_select_db($database_conexao, $conexao);
	$query_consulta = "SELECT interacao FROM suporte WHERE id = '$id_suporte'";
	$consulta = mysql_query($query_consulta, $conexao) or die(mysql_error());
	$row_consulta = mysql_fetch_assoc($consulta);
	$totalRows_consulta = mysql_num_rows($consulta);

	if($row_consulta['interacao'] <> $interacao_atual and $interacao_atual <> NULL){
		return 1;
	} else {
		return 0;
	}
	
	mysql_free_result($consulta);

}
// fim - funcao_suporte_interacao


// função para consulta de créditos (contrato)
function funcao_suporte_credito($contrato){
	if(!isset($_SESSION)){session_start();}
	require('Connections/conexao.php');	
	require_once('parametros.php');	
	mysql_select_db($database_conexao, $conexao);
	
	// pega informações do contrato atual
	$query_contrato_dados = sprintf("
	SELECT 
	da37.codigo17, da37.visita17, 
	(
	case 
	when da37.visita17 = 1 or da37.visita17 = 5 then 0
	when da37.visita17 = 2 then 'Sem Limite'
	when da37.visita17 = 3 or da37.visita17 = 4 then (
		(SELECT COUNT(geral_credito.IdCredito) FROM geral_credito WHERE geral_credito.contrato = da37.codigo17 and status = 1 and geral_credito.data_utilizacao IS NULL) - 
		(SELECT COUNT(geral_credito.IdCredito) FROM geral_credito WHERE geral_credito.contrato = da37.codigo17 and status = 1 and geral_credito.adiantamento = 's')
	)
	else 0 end
	) as creditos 
	 
	FROM da37 
	WHERE da37.codigo17 = %s and da37.sr_deleted <> 'T' 
	ORDER BY da37.vendedor17 ASC 
	", 
	GetSQLValueString($contrato, "text"));
	$contrato_dados = mysql_query($query_contrato_dados, $conexao) or die(mysql_error());
	$row_contrato_dados = mysql_fetch_assoc($contrato_dados);
	$totalRows_contrato_dados = mysql_num_rows($contrato_dados);
	// fim - pega informações do contrato atual

	// 1: Nenhum
	// 2: Sem Limite
	// 3: Mensal
	// 4: Trimestral
	// 5: Sem visita

	return $row_contrato_dados['creditos'];
	
	mysql_free_result($contrato_dados);
}
// fim - função para consulta de créditos (contrato)
?>