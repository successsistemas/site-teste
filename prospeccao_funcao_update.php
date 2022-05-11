<?
// função update prospeccao/descrição
function funcao_prospeccao_update($id, $dados_prospeccao, $dados_prospeccao_descricao){
	require('Connections/conexao.php');
	mysql_select_db($database_conexao, $conexao);
	//----------------------------------------------------------------------------------------------------------------------------------------------//
	if (count($dados_prospeccao) > 0 and count($dados_prospeccao_descricao) > 0){ // atualiza 'prospeccao' e insere 'prospeccao_descricoes'
	//----------------------------------------------------------------------------------------------------------------------------------------------//
		// update prospeccao			
				// lista os campos que estão sendo passados e monta o WHERE
				$where_prospeccao = "";
				foreach ($dados_prospeccao as $campo => $retorno) {
	
						//if($retorno==""){$retorno='';}
						// parametros
						switch ($campo){
							case "id": $retorno = GetSQLValueString($retorno, "text"); break;
							case "data_prospeccao": $retorno = GetSQLValueString($retorno, "text"); break;
							case "data_prospeccao_fim": $retorno = GetSQLValueString($retorno, "text"); break;
							case "praca": $retorno = GetSQLValueString($retorno, "text"); break;
							case "id_usuario_responsavel": $retorno = GetSQLValueString($retorno, "text"); break;
							case "usuario_responsavel_leu": $retorno = GetSQLValueString($retorno, "text"); break;
							case "situacao": $retorno = GetSQLValueString($retorno, "text"); break;
							case "status": $retorno = GetSQLValueString($retorno, "text"); break;
							case "status_flag": $retorno = GetSQLValueString($retorno, "text"); break;
							case "tela": $retorno = GetSQLValueString($retorno, "text"); break;
							case "estorno": $retorno = GetSQLValueString($retorno, "text"); break;
							case "acao": $retorno = GetSQLValueString($retorno, "text"); break;
							case "previsao_geral_inicio": $retorno = GetSQLValueString($retorno, "text"); break;
							case "previsao_geral": $retorno = GetSQLValueString($retorno, "text"); break;
							case "nome_razao_social": $retorno = GetSQLValueString($retorno, "text"); break;
							case "pessoa": $retorno = GetSQLValueString($retorno, "text"); break;
							case "fantasia": $retorno = GetSQLValueString($retorno, "text"); break;
							case "cep": $retorno = GetSQLValueString($retorno, "text"); break;
							case "endereco": $retorno = GetSQLValueString($retorno, "text"); break;
							case "endereco_numero": $retorno = GetSQLValueString($retorno, "text"); break;
							case "endereco_complemento": $retorno = GetSQLValueString($retorno, "text"); break;
							case "bairro": $retorno = GetSQLValueString($retorno, "text"); break;
							case "cidade": $retorno = GetSQLValueString($retorno, "text"); break;
							case "uf": $retorno = GetSQLValueString($retorno, "text"); break;
							case "telefone": $retorno = GetSQLValueString($retorno, "text"); break;
							case "celular": $retorno = GetSQLValueString($retorno, "text"); break;
							case "cpf_cnpj": $retorno = GetSQLValueString($retorno, "text"); break;
							case "rg_inscricao": $retorno = GetSQLValueString($retorno, "text"); break;
							case "rg_orgao_expeditor": $retorno = GetSQLValueString($retorno, "text"); break;
							case "observacao": $retorno = GetSQLValueString(nl2br($retorno), "text"); break;
							case "ativo_passivo": $retorno = GetSQLValueString($retorno, "text"); break;
							case "indicado_por": $retorno = GetSQLValueString($retorno, "text"); break;
							case "responsavel_por_ti": $retorno = GetSQLValueString($retorno, "text"); break;
							case "enquadramento_fiscal": $retorno = GetSQLValueString($retorno, "text"); break;
							case "enquadramento_fiscal_outro": $retorno = GetSQLValueString($retorno, "text"); break;
							case "ramo_de_atividade": $retorno = GetSQLValueString($retorno, "text"); break;
							case "contador": $retorno = GetSQLValueString($retorno, "text"); break;
							case "contador_telefone": $retorno = GetSQLValueString($retorno, "text"); break;
							case "contador_email": $retorno = GetSQLValueString($retorno, "text"); break;
							case "exige_nfe": $retorno = GetSQLValueString($retorno, "text"); break;
							case "exige_cupom_fiscal": $retorno = GetSQLValueString($retorno, "text"); break;
							case "exige_outro": $retorno = GetSQLValueString($retorno, "text"); break;
							case "encaminhamento_id": $retorno = GetSQLValueString($retorno, "text"); break;
							case "encaminhamento_data": $retorno = GetSQLValueString($retorno, "text"); break;
							case "encaminhamento_data_inicio": $retorno = GetSQLValueString($retorno, "text"); break;
							case "status_devolucao": $retorno = GetSQLValueString($retorno, "text"); break;
							case "status_recusa": $retorno = GetSQLValueString($retorno, "text"); break;
							case "final_situacao": $retorno = GetSQLValueString($retorno, "text"); break;
							case "final_status": $retorno = GetSQLValueString($retorno, "text"); break;
							case "baixa_tipo": $retorno = GetSQLValueString($retorno, "text"); break;
							case "baixa_contrato": $retorno = GetSQLValueString($retorno, "text"); break;
							case "baixa_id_venda": $retorno = GetSQLValueString($retorno, "text"); break;
							case "quantidade_agendado": $retorno = GetSQLValueString($retorno, "text"); break;
							case "solicita_agendamento": $retorno = GetSQLValueString($retorno, "text"); break;
							case "baixa_perda_motivo": $retorno = GetSQLValueString($retorno, "text"); break;
							case "baixa_perda_data": $retorno = GetSQLValueString($retorno, "text"); break;
							case "baixa_perda_recurso": $retorno = GetSQLValueString($retorno, "text"); break;
							case "baixa_perda_recurso_solicitacao_existe": $retorno = GetSQLValueString($retorno, "text"); break;
							case "baixa_perda_recurso_solicitacao_verificada": $retorno = GetSQLValueString($retorno, "text"); break;
							case "baixa_perda_recurso_solicitacao_sugestao": $retorno = GetSQLValueString($retorno, "text"); break;
							case "baixa_perda_concorrencia_programa": $retorno = GetSQLValueString($retorno, "text"); break;
							case "baixa_perda_concorrencia_fator": $retorno = GetSQLValueString($retorno, "text"); break;
							case "parecer": $retorno = GetSQLValueString($retorno, "text"); break;
							case "interacao": $retorno = GetSQLValueString($retorno, "int"); break;
							case "nivel_interesse": $retorno = GetSQLValueString($retorno, "text"); break;
							case "proposta_valor": $retorno = GetSQLValueString($retorno, "text"); break;
							case "proposta_recursos": $retorno = GetSQLValueString($retorno, "text"); break;
							case "proposta_validade": $retorno = GetSQLValueString($retorno, "text"); break;
							
							case "sistema_possui": $retorno = GetSQLValueString($retorno, "text"); break;
							case "sistema_nivel_utilizacao": $retorno = GetSQLValueString($retorno, "text"); break;
							case "sistema_nivel_satisfacao": $retorno = GetSQLValueString($retorno, "text"); break;
							case "sistema_nivel_motivo": $retorno = GetSQLValueString($retorno, "text"); break;
							case "sistema_recursos": $retorno = GetSQLValueString($retorno, "text"); break;
							case "sistema_recursos_success_possui": $retorno = GetSQLValueString($retorno, "text"); break;
							case "sistema_recursos_success_nao_possui": $retorno = GetSQLValueString($retorno, "text"); break;

							case "nivel_interesse": $retorno = GetSQLValueString($retorno, "text"); break;
							case "empresa_controle_manual": $retorno = GetSQLValueString($retorno, "text"); break;
							case "necessidades": $retorno = GetSQLValueString($retorno, "text"); break;
							case "podemos_ofertar": $retorno = GetSQLValueString($retorno, "text"); break;

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
						$where_prospeccao .= "$campo = $retorno, ";			
				}
				$where_prospeccao = substr($where_prospeccao, 0, -2); // tira a última virgula
				// fim - lista os campos que estão sendo passados e monta o WHERE
				
				$updateprospeccao = "UPDATE prospeccao SET $where_prospeccao WHERE id =".GetSQLValueString($id, "int");
				$Resultprospeccao = mysql_query($updateprospeccao, $conexao) or die(mysql_error());
				
				// limpa os campos
				$where_prospeccao = "";
				$dados_prospeccao = array();
				$campo = "";
				$retorno = "";
				$dados_prospeccao="";
				$updateprospeccao = "";
				$Resultprospeccao = "";
		// fim - update prospeccao
	}
	//----------------------------------------------------------------------------------------------------------------------------------------------//
		// insert descrição
				// lista os campos que estão sendo passados
				$campos_prospeccao = "";
				foreach ($dados_prospeccao_descricao as $campo => $retorno) {
						$campos_prospeccao .= "$campo, ";			
				}
				$campos_prospeccao = substr($campos_prospeccao, 0, -2);
				// fim - lista os campos que estão sendo passados
				
				// limpa os campos			
				$campo = "";
				$retorno = "";
						
				// lista os valores que estão sendo passados
				$valores_prospeccao = "";
				foreach ($dados_prospeccao_descricao as $campo => $retorno) {
					
						// parametros			
						switch ($campo){
							case "id_prospeccao": $retorno = GetSQLValueString($retorno, "int"); break;
							case "descricao": $retorno = GetSQLValueString(nl2br($retorno), "text"); break; // nl2br = coloca <br> no textarea.
							case "data": $retorno = GetSQLValueString($retorno, "date"); break;
							case "tipo_postagem": $retorno = GetSQLValueString($retorno, "text"); break;
							case "id_usuario_responsavel": $retorno = GetSQLValueString($retorno, "int"); break;
							case "questionado": $retorno = GetSQLValueString($retorno, "text"); break;
						}
						// parametros
						
						$valores_prospeccao .= "$retorno, ";
				}
				$valores_prospeccao = substr($valores_prospeccao, 0, -2);
				// fim - lista os valores que estão sendo passados
				
				
				$insertprospeccaoDescricao  = "INSERT INTO prospeccao_descricoes ($campos_prospeccao) VALUES ($valores_prospeccao)";
				$ResultprospeccaoDescricao = mysql_query($insertprospeccaoDescricao, $conexao) or die(mysql_error());			
				
				// limpa os campos
				$campos_prospeccao = "";
				$valores_prospeccao = "";
				$dados_prospeccao_descricao = array();
				$campo = "";
				$retorno = "";
				$insertprospeccaoDescricao = "";
				$ResultprospeccaoDescricao = "";
		// fim - insert descrição	
	//----------------------------------------------------------------------------------------------------------------------------------------------//
}
// fim - função update prospeccao/descrição

// funcao_prospeccao_interacao
function funcao_prospeccao_interacao($id_prospeccao, $interacao_atual){

	require('Connections/conexao.php');
	

	mysql_select_db($database_conexao, $conexao);
	$query_consulta = "SELECT interacao FROM prospeccao WHERE id = '$id_prospeccao'";
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
// fim - funcao_prospeccao_interacao

?>