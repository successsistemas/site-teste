<?
// funcao_solicitacao_update
function funcao_solicitacao_update($id, $dados_solicitacao, $dados_solicitacao_descricao){

	$funcao_solicitacao_update_retorno = NULL;
	
	// conexão
	require('Connections/conexao.php');
	mysql_select_db($database_conexao, $conexao);

	//region - solicitacao ------------------------------------------------------
	if (count($dados_solicitacao) > 0){

		$where_solicitacao = "";
		foreach ($dados_solicitacao as $campo => $retorno) { // lista os campos que estão sendo passados e monta o WHERE

				//if($retorno==""){$retorno='';}
				// parametros
				switch ($campo){
					case "id": $retorno = GetSQLValueString($retorno, "int"); break;
					case "titulo": $retorno = GetSQLValueString($retorno, "text"); break;
					case "dt_solicitacao": $retorno = GetSQLValueString($retorno, "date"); break;
					case "empresa": $retorno = GetSQLValueString($retorno, "text"); break;
					case "contrato": $retorno = GetSQLValueString($retorno, "text"); break;
					case "id_usuario_responsavel": $retorno = GetSQLValueString($retorno, "int"); break;
					case "id_programa": $retorno = GetSQLValueString($retorno, "int"); break;
					case "programa": $retorno = GetSQLValueString($retorno, "text"); break;
					case "id_subprograma": $retorno = GetSQLValueString($retorno, "int"); break;
					case "subprograma": $retorno = GetSQLValueString($retorno, "text"); break;
					case "campo": $retorno = GetSQLValueString($retorno, "text"); break;
					case "versao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "programa_executavel": $retorno = GetSQLValueString($retorno, "text"); break;
					case "data_executavel": $retorno = GetSQLValueString($retorno, "date"); break;
					case "hora_executavel": $retorno = GetSQLValueString($retorno, "text"); break;
					case "medida_tomada": $retorno = GetSQLValueString(nl2br($retorno), "text"); break;
					case "online": $retorno = GetSQLValueString($retorno, "text"); break;
					case "prioridade": $retorno = GetSQLValueString($retorno, "text"); break;
					case "prioridade_justificativa": $retorno = GetSQLValueString($retorno, "text"); break;
					case "h_des": $retorno = GetSQLValueString($retorno, "text"); break;
					case "tipo_bd": $retorno = GetSQLValueString($retorno, "text"); break;
					case "tipo_bd2": $retorno = GetSQLValueString($retorno, "text"); break;
					case "geral_tipo_versao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "geral_tipo_distribuicao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "geral_tipo_ecf": $retorno = GetSQLValueString($retorno, "text"); break;
					case "geral_tipo_ecf2": $retorno = GetSQLValueString($retorno, "text"); break;
					case "praca": $retorno = GetSQLValueString($retorno, "text"); break;
					case "protocolo_suporte": $retorno = GetSQLValueString($retorno, "int"); break;
					case "codigo_empresa": $retorno = GetSQLValueString($retorno, "text"); break;
					case "tipo": $retorno = GetSQLValueString($retorno, "text"); break;
					case "situacao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "dt_recebimento": $retorno = GetSQLValueString($retorno, "date"); break;
					case "id_operador": $retorno = GetSQLValueString($retorno, "int"); break;
					case "nome_operador": $retorno = GetSQLValueString($retorno, "text"); break;
					case "previsao_analise": $retorno = GetSQLValueString($retorno, "date"); break;
					case "dt_aprovacao_reprovacao": $retorno = GetSQLValueString($retorno, "date"); break;
					case "observacao_aprovacao_reprovacao": $retorno = GetSQLValueString(nl2br($retorno), "text"); break;
					case "previsao_solucao": $retorno = GetSQLValueString($retorno, "date"); break;
					case "nome_executante": $retorno = GetSQLValueString($retorno, "text"); break;
					case "dt_conclusao": $retorno = GetSQLValueString($retorno, "date"); break;
					case "previsao_testes": $retorno = GetSQLValueString($retorno, "date"); break;
					case "nome_testador": $retorno = GetSQLValueString($retorno, "text"); break;
					case "dt_conclusao_testes": $retorno = GetSQLValueString($retorno, "date"); break;
					case "observacao_testes": $retorno = GetSQLValueString(nl2br($retorno), "text"); break;
					case "previsao_validacao": $retorno = GetSQLValueString($retorno, "date"); break;
					case "dt_validacao": $retorno = GetSQLValueString($retorno, "date"); break;
					case "observacao_validacao": $retorno = GetSQLValueString(nl2br($retorno), "text"); break;
					case "dt_final": $retorno = GetSQLValueString($retorno, "date"); break;
					case "status": $retorno = GetSQLValueString($retorno, "text"); break;
					case "previsao_geral": $retorno = GetSQLValueString($retorno, "date"); break;
					case "solicitante_leu": $retorno = GetSQLValueString($retorno, "date"); break;
					case "operador_leu": $retorno = GetSQLValueString($retorno, "date"); break;
					case "executante_leu": $retorno = GetSQLValueString($retorno, "date"); break;
					case "testador_leu": $retorno = GetSQLValueString($retorno, "date"); break;
					case "observacao_final": $retorno = GetSQLValueString(nl2br($retorno), "text"); break;
					case "previsao_proposta": $retorno = GetSQLValueString($retorno, "date"); break;
					case "previsao_proposta_ja_alterada": $retorno = GetSQLValueString($retorno, "text"); break;
					case "nome_analista_orcamento": $retorno = GetSQLValueString($retorno, "text"); break;
					case "previsao_analise_orcamento": $retorno = GetSQLValueString($retorno, "date"); break;
					case "dt_orcamento": $retorno = GetSQLValueString($retorno, "date"); break;
					case "prazo_desenvolvimento_orcamento": $retorno = GetSQLValueString($retorno, "int"); break;
					case "orcamento": $retorno = GetSQLValueString($retorno, "text"); break;
					case "analista_orcamento_leu": $retorno = GetSQLValueString($retorno, "date"); break;
					case "encaminhamento_data": $retorno = GetSQLValueString($retorno, "date"); break;
					case "previsao_analise_inicio": $retorno = GetSQLValueString($retorno, "date"); break;
					case "previsao_analise_orcamento_inicio": $retorno = GetSQLValueString($retorno, "date"); break;
					case "previsao_solucao_inicio": $retorno = GetSQLValueString($retorno, "date"); break;
					case "previsao_testes_inicio": $retorno = GetSQLValueString($retorno, "date"); break;
					case "previsao_validacao_inicio": $retorno = GetSQLValueString($retorno, "date"); break;
					case "previsao_geral_inicio": $retorno = GetSQLValueString($retorno, "date"); break;
					case "previsao_proposta_inicio": $retorno = GetSQLValueString($retorno, "date"); break;
					case "encaminhamento_data_inicio": $retorno = GetSQLValueString($retorno, "date"); break;
					case "status_questionamento": $retorno = GetSQLValueString($retorno, "text"); break;
					case "status_devolucao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "orcamento_os": $retorno = GetSQLValueString($retorno, "text"); break;
					case "status_recusa": $retorno = GetSQLValueString($retorno, "text"); break;
					case "implementacao_mensagem_sim_nao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "implementacao_nao_justificativa": $retorno = GetSQLValueString(nl2br($retorno), "text"); break;
					case "previsao_retorno_orcamento_inicio": $retorno = GetSQLValueString($retorno, "date"); break;
					case "previsao_retorno_orcamento": $retorno = GetSQLValueString($retorno, "date"); break;
					case "acao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "deixar_sugestao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "encerramento_automatico": $retorno = GetSQLValueString($retorno, "text"); break;
					case "encerramento_automatico_data": $retorno = GetSQLValueString($retorno, "text"); break;
					case "interacao": $retorno = GetSQLValueString($retorno, "int"); break;
					case "id_executante": $retorno = GetSQLValueString($retorno, "int"); break;
					case "id_testador": $retorno = GetSQLValueString($retorno, "int"); break;
					case "id_analista_orcamento": $retorno = GetSQLValueString($retorno, "int"); break;
					case "id_encaminhamento": $retorno = GetSQLValueString($retorno, "int"); break;
					case "id_usuario_aprovacao_reprovacao": $retorno = GetSQLValueString($retorno, "int"); break;
					case "auto_email_status": $retorno = GetSQLValueString($retorno, "text"); break;
					case "auto_email_data": $retorno = GetSQLValueString($retorno, "text"); break;
					case "auto_email_solicitacao_descricao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "numero_revisao_svn_estavel": $retorno = GetSQLValueString($retorno, "text"); break;
					case "numero_revisao_svn_desenvolvimento": $retorno = GetSQLValueString($retorno, "text"); break;
					case "devolucao_id_usuario": $retorno = GetSQLValueString($retorno, "text"); break;
					case "devolucao_motivo": $retorno = GetSQLValueString($retorno, "text"); break;
					case "devolucao_data": $retorno = GetSQLValueString($retorno, "text"); break;
				}
				// parametros
				
				// mostra nome do campo e valor passado
				$where_solicitacao .= "$campo = $retorno, ";

		}
		
		$where_solicitacao = substr($where_solicitacao, 0, -2); // tira a última virgula
		// fim - lista os campos que estão sendo passados e monta o WHERE
		$updateSolicitacao = "UPDATE solicitacao SET $where_solicitacao WHERE id =".GetSQLValueString($id, "int");
		$ResultSolicitacao = mysql_query($updateSolicitacao, $conexao) or die(mysql_error());

		//region - limpa os campos
		$where_solicitacao = "";
		$dados_solicitacao = array();
		$campo = "";
		$retorno = "";
		$dados_solicitacao="";
		$updateSolicitacao = "";
		$ResultSolicitacao = "";
		//endregion - fim - limpa os campos

		$solicitacao_ultimo_id = mysql_insert_id($conexao);

		$funcao_solicitacao_update_retorno['solicitacao'] = array('acao' => 'update', 'id'=> $id);

	}
	//endregion - solicitacao ------------------------------------------------------	

	//region - solicitacao_descricoes ------------------------------------------------------
	if (count($dados_solicitacao_descricao) > 0){

		//region - lista os campos que estão sendo passados
		$campos_solicitacao = "";
		foreach ($dados_solicitacao_descricao as $campo => $retorno) {
				$campos_solicitacao .= "$campo, ";			
		}
		$campos_solicitacao = substr($campos_solicitacao, 0, -2);
		//endregion - fim - lista os campos que estão sendo passados
		
		// limpa os campos			
		$campo = "";
		$retorno = "";
				
		//region - lista os valores que estão sendo passados
		$valores_solicitacao = "";
		foreach ($dados_solicitacao_descricao as $campo => $retorno) {
			
				// parametros			
				switch ($campo){
					
					case "id_solicitacao": $retorno = GetSQLValueString($retorno, "int"); break;
					case "descricao": $retorno = GetSQLValueString(nl2br($retorno), "text"); break;
					case "usuario_responsavel": $retorno = GetSQLValueString($retorno, "text"); break;
					case "data": $retorno = GetSQLValueString($retorno, "date"); break;
					case "orcamento": $retorno = GetSQLValueString($retorno, "text"); break;
					case "h_des": $retorno = GetSQLValueString($retorno, "text"); break;
					case "previsao_entrega": $retorno = GetSQLValueString($retorno, "text"); break;
					case "tipo_postagem": $retorno = GetSQLValueString($retorno, "text"); break;
					case "questionamento_pos_solucao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "id_usuario_responsavel": $retorno = GetSQLValueString($retorno, "int"); break;
					case "executante": $retorno = GetSQLValueString($retorno, "text"); break;
					case "questionado": $retorno = GetSQLValueString($retorno, "text"); break;
					case "id_arquivo": $retorno = GetSQLValueString($retorno, "int"); break;
					case "data_edicao": $retorno = GetSQLValueString($retorno, "text"); break;
					case "solicitacao_auto_email_dias": $retorno = GetSQLValueString($retorno, "int"); break;

					case "conclusao_execucao": $retorno = GetSQLValueString($retorno, "int"); break;
					case "conclusao_teste": $retorno = GetSQLValueString($retorno, "int"); break;

				}
				// parametros
				
				$valores_solicitacao .= "$retorno, ";
		}
		$valores_solicitacao = substr($valores_solicitacao, 0, -2);
		//endregion - fim - lista os valores que estão sendo passados
			
		$insertSolicitacaoDescricao  = "INSERT INTO solicitacao_descricoes ($campos_solicitacao) VALUES ($valores_solicitacao)";
		$ResultSolicitacaoDescricao = mysql_query($insertSolicitacaoDescricao, $conexao) or die(mysql_error());			
		
		//region - limpa os campos
		$campos_solicitacao = "";
		$valores_solicitacao = "";
		$dados_solicitacao_descricao = array();
		$campo = "";
		$retorno = "";
		$dados_solicitacao="";
		$dados_solicitacao_descricao="";
		$insertSolicitacaoDescricao = "";
		$ResultSolicitacaoDescricao = "";
		//endregion - fim - limpa os campos

		$solicitacao_descricoes_ultimo_id = mysql_insert_id($conexao);

		$funcao_solicitacao_update_retorno['solicitacao_descricoes'] = array('acao' => 'insert', 'id'=> $solicitacao_descricoes_ultimo_id);

	}
	//endregion - fim - solicitacao_descricoes ------------------------------------------------------

	return $funcao_solicitacao_update_retorno;

}
// fim - funcao_solicitacao_update

// funcao_solicitacao_interacao
function funcao_solicitacao_interacao($id_solicitacao, $interacao_atual){

	require('Connections/conexao.php');
	

	mysql_select_db($database_conexao, $conexao);
	$query_consulta = "SELECT interacao FROM solicitacao WHERE id = '$id_solicitacao'";
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
// fim - funcao_solicitacao_interacao
?>