<?
// função update venda/descrição
function funcao_venda_update($id, $dados_venda, $dados_venda_descricao){
	require('Connections/conexao.php');
	mysql_select_db($database_conexao, $conexao);
	//----------------------------------------------------------------------------------------------------------------------------------------------//
	if ((count($dados_venda) > 0 and count($dados_venda_descricao) > 0)){ // atualiza solic. e insere desc.
	//----------------------------------------------------------------------------------------------------------------------------------------------//
		// update venda			
				// lista os campos que estão sendo passados e monta o WHERE
				$where_venda = "";
				foreach ($dados_venda as $campo => $retorno) {
	
						// parametros
						switch ($campo){
								case "id_prospeccao": $retorno = GetSQLValueString($retorno, "int"); break;
								case "data_venda": $retorno = GetSQLValueString($retorno, "text"); break;
								case "data_inicio": $retorno = GetSQLValueString($retorno, "text"); break;
								case "data_fim": $retorno = GetSQLValueString($retorno, "text"); break;
								case "data_contrato": $retorno = GetSQLValueString($retorno, "text"); break;
								case "titulo": $retorno = GetSQLValueString($retorno, "text"); break;
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
								case "solicita_venda": $retorno = GetSQLValueString($retorno, "text"); break;
								case "solicita_visita": $retorno = GetSQLValueString($retorno, "text"); break;
								case "id_formulario": $retorno = GetSQLValueString($retorno, "int"); break;
								case "final_parecer": $retorno = GetSQLValueString($retorno, "text"); break;
								case "final_situacao": $retorno = GetSQLValueString($retorno, "date"); break;
								case "final_status": $retorno = GetSQLValueString($retorno, "text"); break;
								case "final_solicita_venda": $retorno = GetSQLValueString($retorno, "text"); break;
								case "final_solicita_visita": $retorno = GetSQLValueString($retorno, "text"); break;
								case "estorno": $retorno = GetSQLValueString($retorno, "text"); break;
								case "estorno_justificativa": $retorno = GetSQLValueString($retorno, "text"); break;
								case "acao": $retorno = GetSQLValueString($retorno, "text"); break;
								case "ordem_servico": $retorno = GetSQLValueString($retorno, "text"); break;
								case "data_solicita_venda": $retorno = GetSQLValueString($retorno, "text"); break;
								case "data_solicita_venda_aceita_recusa": $retorno = GetSQLValueString($retorno, "text"); break;
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
								case "treinamento_tempo": $retorno = GetSQLValueString($retorno, "date"); break;
								case "quantidade_agendado_treinamento": $retorno = GetSQLValueString($retorno, "text"); break;
								case "quantidade_agendado_implantacao": $retorno = GetSQLValueString($retorno, "text"); break;
								case "data_atualizacao_contrato": $retorno = GetSQLValueString($retorno, "text"); break;
								case "espelho": $retorno = GetSQLValueString($retorno, "text"); break;
								case "ordem_servico": $retorno = GetSQLValueString($retorno, "text"); break;
								case "dilacao_prazo_proposto": $retorno = GetSQLValueString($retorno, "text"); break;
								case "dilacao_prazo": $retorno = GetSQLValueString($retorno, "text"); break;
								case "dilacao_prazo_quantidade": $retorno = GetSQLValueString($retorno, "text"); break;
								case "dilacao_motivo": $retorno = GetSQLValueString($retorno, "text"); break;
								case "dilacao_id_atual": $retorno = GetSQLValueString($retorno, "text"); break;
								case "conclusao_implantacao_treinamento": $retorno = GetSQLValueString($retorno, "text"); break;
								case "conclusao_implantacao_treinamento_data": $retorno = GetSQLValueString($retorno, "text"); break;
								case "impressao_venda_formulario_avaliacao_implantacao": $retorno = GetSQLValueString($retorno, "text"); break;
								case "impressao_venda_formulario_avaliacao_implantacao_data": $retorno = GetSQLValueString($retorno, "text"); break;
								case "encerramento_automatico": $retorno = GetSQLValueString($retorno, "text"); break;
								case "encerramento_automatico_data": $retorno = GetSQLValueString($retorno, "text"); break;
								case "interacao": $retorno = GetSQLValueString($retorno, "int"); break;
								case "validacao_venda_data": $retorno = GetSQLValueString($retorno, "date"); break;
								case "validacao_venda_IdUsuario": $retorno = GetSQLValueString($retorno, "int"); break;	

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
						
						// mostra nome do campo e valor passado
						$where_venda .= "$campo = $retorno, ";			
				}
				$where_venda = substr($where_venda, 0, -2); // tira a última virgula
				// fim - lista os campos que estão sendo passados e monta o WHERE

				$updateSuporte = "UPDATE venda SET $where_venda WHERE id =".GetSQLValueString($id, "int");
				$ResultSuporte = mysql_query($updateSuporte, $conexao) or die(mysql_error());
			
				// limpa os campos
				$where_venda = "";
				$dados_venda = array();
				$campo = "";
				$retorno = "";
				$dados_venda="";
				$updateSuporte = "";
				$ResultSuporte = "";
		// fim - update venda

	}
	//----------------------------------------------------------------------------------------------------------------------------------------------//
		// insert descrição
				// lista os campos que estão sendo passados
				$campos_venda = "";
				foreach ($dados_venda_descricao as $campo => $retorno) {
						$campos_venda .= "$campo, ";			
				}
				$campos_venda = substr($campos_venda, 0, -2);
				// fim - lista os campos que estão sendo passados
				
				// limpa os campos			
				$campo = "";
				$retorno = "";
						
				// lista os valores que estão sendo passados
				$valores_venda = "";
				foreach ($dados_venda_descricao as $campo => $retorno) {
					
						// parametros			
						switch ($campo){
							case "id_venda": $retorno = GetSQLValueString($retorno, "int"); break;
							case "descricao": $retorno = GetSQLValueString(nl2br($retorno), "text"); break; // nl2br = coloca <br> no textarea.
							case "data": $retorno = GetSQLValueString($retorno, "date"); break;
							case "tipo_postagem": $retorno = GetSQLValueString($retorno, "text"); break;
							case "id_usuario_responsavel": $retorno = GetSQLValueString($retorno, "int"); break;
							case "questionado": $retorno = GetSQLValueString($retorno, "text"); break;
						}
						// parametros
						
						$valores_venda .= "$retorno, ";
				}
				$valores_venda = substr($valores_venda, 0, -2);
				// fim - lista os valores que estão sendo passados
				
				$insertSuporteDescricao  = "INSERT INTO venda_descricoes ($campos_venda) VALUES ($valores_venda)";
				$ResultSuporteDescricao = mysql_query($insertSuporteDescricao, $conexao) or die(mysql_error());			
				
				// limpa os campos
				$campos_venda = "";
				$valores_venda = "";
				$dados_venda_descricao = array();
				$campo = "";
				$retorno = "";
				$insertSuporteDescricao = "";
				$ResultSuporteDescricao = "";
		// fim - insert descrição	
	//----------------------------------------------------------------------------------------------------------------------------------------------//
}
// fim - função update venda/descrição

// funcao_venda_interacao
function funcao_venda_interacao($id_venda, $interacao_atual){

	require('Connections/conexao.php');
	

	mysql_select_db($database_conexao, $conexao);
	$query_consulta = "SELECT interacao FROM venda WHERE id = '$id_venda'";
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
// fim - funcao_venda_interacao
?>