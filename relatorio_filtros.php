<div class="filtro_geral">

<div class="filtro_geral_titulo" id="cabecalho_filtros" style="cursor: pointer">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Filtros - Geral
		</td>
        
		<td style="text-align: right">
		<div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
		</td>
	</tr>
</table>
</div>

<div style="border: 1px solid #c5dbec; margin-bottom: 5px;" id="corpo_filtros">
<form name="form_filtro" id="form_filtro" action="relatorio.php" method="get">

		<input type="hidden" id="tela" name="tela" value="digital" />

        <? if($relatorio_id_grupo!=NULL){ ?>
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
            
              <td style="text-align:left">
                <span class="label_solicitacao">Grupo: </span>
                <br />
                <input id="relatorio_id_grupo" name="relatorio_id_grupo" type="hidden" value="<? echo @$_GET["relatorio_id_grupo"]; ?>" />
                
                <select id="relatorio_id_grupo_subgrupo" name="relatorio_id_grupo_subgrupo" style="width: 300px;">
                <option value="" <?php if (!(strcmp("", isset($_GET['relatorio_id_grupo_subgrupo'])))) {echo "selected=\"selected\"";} ?>>Escolha...</option>
                <?php
                do {  
                ?>
                <option value="<?php echo $row_filtro_relatorio_grupo_subgrupo['id']?>" 
				<?php if ($row_filtro_relatorio_grupo_subgrupo['id'] == @$_GET['relatorio_id_grupo_subgrupo']) {echo "selected=\"selected\"";} ?>>
				<?php echo utf8_encode($row_filtro_relatorio_grupo_subgrupo['titulo']); ?>
                </option>
                <?php
                } while ($row_filtro_relatorio_grupo_subgrupo = mysql_fetch_assoc($filtro_relatorio_grupo_subgrupo));
                $rows = mysql_num_rows($filtro_relatorio_grupo_subgrupo);
                if($rows > 0) {
                mysql_data_seek($filtro_relatorio_grupo_subgrupo, 0);
                $row_filtro_relatorio_grupo_subgrupo = mysql_fetch_assoc($filtro_relatorio_grupo_subgrupo);
                }
                ?>
                </select>
                </td>
                
              <td style="text-align:right">
                <span class="label_solicitacao">Relatório: </span>
                <br />
                
                <select id="relatorio_id" name="relatorio_id" style="width: 300px;">
                	<option value="">Escolha um subgrupo primeiro ... </option>
                </select>
                </td>
                
            </tr>
        </table>
        </div>
        <? } ?>
                
        <div class="div_filtros">
        <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
        <td style="text-align:left">
			<!-- filtro_geral_data_criacao/filtro_geral_data_criacao_fim -->
            <? if($relatorio_id_grupo > 0){ ?>
                <span class="label_solicitacao">Data: </span>
                <br>
                <table cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td>
                    <input name="filtro_geral_data_criacao" id="filtro_geral_data_criacao" type="text" value="<? 
                    if ( isset($_GET['filtro_geral_data_criacao']) ){ echo $_GET['filtro_geral_data_criacao']; }
                    ?>" style="width: 130px;" />
                    </td>
                
                    <td width="35" style="text-align: center;">
                    à
                    </td>
                    
                    <td>
                    <input name="filtro_geral_data_criacao_fim" id="filtro_geral_data_criacao_fim" type="text" value="<? 
                    if ( isset($_GET['filtro_geral_data_criacao_fim']) ){ echo $_GET['filtro_geral_data_criacao_fim']; }
                    ?>"  style="width: 130px;" />
                    </td>
                </tr>
                </table>
            <? } else { ?>
                <span class="label_solicitacao">Mês/Ano processamento: </span>
                <br>
                <table cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td>
                    <select id="filtro_geral_data_mes_ano" name="filtro_geral_data_mes_ano" style="width: 150px;">
						<? for ($i = 0; $i <= 17; $i++) { ?>
                        	<? $mes_ano = strftime('%m-%Y', strtotime('-'.$i.' Month')); ?>
                        	<option value="<? echo $mes_ano; ?>"><? echo $mes_ano; ?></option>
                        <? } ?>
                    </select>
                    <input name="filtro_geral_data_criacao" id="filtro_geral_data_criacao" type="hidden" value="<? 
                    if ( isset($_GET['filtro_geral_data_criacao']) ){ echo $_GET['filtro_geral_data_criacao']; }
                    ?>" />
                    <input name="filtro_geral_data_criacao_fim" id="filtro_geral_data_criacao_fim" type="hidden" value="<? 
                    if ( isset($_GET['filtro_geral_data_criacao_fim']) ){ echo $_GET['filtro_geral_data_criacao_fim']; }
                    ?>" />
                    </td>
                  </tr>
                </table>
            <? } ?>
            <!-- fim - filtro_geral_data_criacao/filtro_geral_data_criacao_fim -->
        </td>
        
        <td style="text-align:right" class="div_filtros_suportes_corpo_td">
        	<!-- filtro_geral_praca -->
            <span class="label_solicitacao">Praça: </span>
            <br />
            <? if($acesso == 1){ ?>
            <select name="filtro_geral_praca" id="filtro_geral_praca" style="width: 300px;">
            <option value=""
            <?php if (!(strcmp("", isset($_GET['filtro_geral_praca'])))) {echo "selected=\"selected\"";} ?>
            >
            Escolha ...
            </option>
            <?php do {  ?>
            <option value="<?php echo $row_filtro_geral_praca['praca']?>"
            <?php if ( (isset($_GET['filtro_geral_praca'])) and (!(strcmp($row_filtro_geral_praca['praca'], $_GET['filtro_geral_praca']))) ) {echo "selected=\"selected\"";} ?>
            >
            <?php echo $row_filtro_geral_praca['praca']?>
            </option>
            <?php
            } while ($row_filtro_geral_praca = mysql_fetch_assoc($filtro_geral_praca));
            $rows = mysql_num_rows($filtro_geral_praca);
            if($rows > 0) {
            mysql_data_seek($filtro_geral_praca, 0);
            $row_filtro_geral_praca = mysql_fetch_assoc($filtro_geral_praca);
            }
            ?>
            </select>
            <? } else { ?>
            <input type="hidden" name="filtro_geral_praca" id="filtro_geral_praca" value="<? echo $row_usuario['praca']; ?>" />
            <strong><? echo $row_usuario['praca']; ?></strong>
            <? } ?>
            <!-- fim - filtro_geral_praca -->
        </td>
        </tr>
        </table>
        </div>
        
        <? if($row_relatorio['filtro_geral_cliente'] == 1 or $row_relatorio['filtro_geral_usuario'] == 1){ ?>
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td style="text-align: left">
                <span class="label_solicitacao">Cliente: </span>
                <br />
                <select name="filtro_geral_cliente" id="filtro_geral_cliente" style="width: 300px;">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_geral_cliente'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_geral_cliente['codigo1']; ?>"
                <?php if ( (isset($_GET['filtro_geral_cliente'])) and (!(strcmp($row_filtro_geral_cliente['codigo1'], $_GET['filtro_geral_cliente']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo utf8_encode($row_filtro_geral_cliente['nome1']); ?>
                </option>
                <?php
                } while ($row_filtro_geral_cliente = mysql_fetch_assoc($filtro_geral_cliente));
                $rows = mysql_num_rows($filtro_geral_cliente);
                if($rows > 0) {
                mysql_data_seek($filtro_geral_cliente, 0);
                $row_filtro_geral_cliente = mysql_fetch_assoc($filtro_geral_cliente);
                }
                ?>
                </select>
				</td>
        
              	<td style="text-align:right" width="300px">
                <span class="label_solicitacao">Usuário: </span>
                <br />
                <select name="filtro_geral_usuario" id="filtro_geral_usuario" style="width: 300px;">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_geral_usuario'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_geral_usuario['IdUsuario']; ?>"
                <?php if ( (isset($_GET['filtro_geral_usuario'])) and (!(strcmp($row_filtro_geral_usuario['IdUsuario'], $_GET['filtro_geral_usuario']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo utf8_encode($row_filtro_geral_usuario['nome']); ?>
                </option>
                <?php
                } while ($row_filtro_geral_usuario = mysql_fetch_assoc($filtro_geral_usuario));
                $rows = mysql_num_rows($filtro_geral_usuario);
                if($rows > 0) {
                mysql_data_seek($filtro_geral_usuario, 0);
                $row_filtro_geral_usuario = mysql_fetch_assoc($filtro_geral_usuario);
                }
                ?>
                </select>
                </td>

                <? if($row_relatorio['filtro_geral_usuario_area'] == 1){ ?>
                <td style="text-align:right" width="120px">
                <span class="label_solicitacao">Área: </span>
                <br />
                <select name="filtro_geral_usuario_area" id="filtro_geral_usuario_area" style="width: 100px;">
                <option value="" <?php if (!(strcmp("", isset($_GET['filtro_geral_usuario_area'])))) {echo "selected=\"selected\"";} ?>>...</option>
                <option value="a" <?php if ( (isset($_GET['filtro_geral_usuario_area'])) and (!(strcmp("a", $_GET['filtro_geral_usuario_area']))) ) {echo "selected=\"selected\"";} ?>>Administrativo</option>
                <option value="o" <?php if ( (isset($_GET['filtro_geral_usuario_area'])) and (!(strcmp("o", $_GET['filtro_geral_usuario_area']))) ) {echo "selected=\"selected\"";} ?>>Operacional</option>
                </select>
                </td>
                <? } ?>
                
            </tr>
        </table>
        </div>
        <? } ?>
        
        <? if($row_relatorio['filtro_suporte_situacao'] == 1 and $relatorio_id_grupo > 0){ ?>
		<div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td style="text-align: left">
                <fieldset style="border: 0px;">
                <span class="label_solicitacao">Situação:</span>
                <input  name="filtro_suporte_situacao[]" type="checkbox" value="criada"
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_suporte_situacao'])){
                        foreach($_GET['filtro_suporte_situacao'] as $filtro_suporte_situacao){
                            if($filtro_suporte_situacao=="criada"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />criada
                
                <input name="filtro_suporte_situacao[]" type="checkbox" value="analisada" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_suporte_situacao'])){
                        foreach($_GET['filtro_suporte_situacao'] as $filtro_suporte_situacao){
                            if($filtro_suporte_situacao=="analisada"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />analisada
    
                <input name="filtro_suporte_situacao[]" type="checkbox" value="em execução" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_suporte_situacao'])){
                        foreach($_GET['filtro_suporte_situacao'] as $filtro_suporte_situacao){
                            if($filtro_suporte_situacao=="em execução"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />em execução
                
                <input name="filtro_suporte_situacao[]" type="checkbox" value="em validação" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_suporte_situacao'])){
                        foreach($_GET['filtro_suporte_situacao'] as $filtro_suporte_situacao){
                            if($filtro_suporte_situacao=="em validação"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />em validação
                
                <input name="filtro_suporte_situacao[]" type="checkbox" value="solicitado suporte" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_suporte_situacao'])){
                        foreach($_GET['filtro_suporte_situacao'] as $filtro_suporte_situacao){
                            if($filtro_suporte_situacao=="solicitado suporte"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />solicitado suporte

                <input name="filtro_suporte_situacao[]" type="checkbox" value="solicitado visita" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_suporte_situacao'])){
                        foreach($_GET['filtro_suporte_situacao'] as $filtro_suporte_situacao){
                            if($filtro_suporte_situacao=="solicitado visita"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />solicitado visita
                
                <input name="filtro_suporte_situacao[]" type="checkbox" value="encaminhado para solicitação" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_suporte_situacao'])){
                        foreach($_GET['filtro_suporte_situacao'] as $filtro_suporte_situacao){
                            if($filtro_suporte_situacao=="encaminhado para solicitação"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />encaminhado para solicitação
                
                
                                
                <input name="filtro_suporte_situacao[]" type="checkbox" value="solucionada" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_suporte_situacao'])){
                        foreach($_GET['filtro_suporte_situacao'] as $filtro_suporte_situacao){
                            if($filtro_suporte_situacao=="solucionada"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />solucionada
                
                <input name="filtro_suporte_situacao[]" type="checkbox" value="cancelada" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_suporte_situacao'])){
                        foreach($_GET['filtro_suporte_situacao'] as $filtro_suporte_situacao){
                            if($filtro_suporte_situacao=="cancelada"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />cancelada
                
                <input type="checkbox" id="checkall_filtro_suporte_situacao"  name="checkall_filtro_suporte_situacao" />Marcar todos
                </fieldset>
				</td>
        
            </tr>
        </table>
        </div>
        <? } ?>

        <? if($row_relatorio['filtro_suporte_anexo'] == 1 and $relatorio_id_grupo > 0){ ?>
		<div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                
                <td style="text-align: left">
                <span class="label_solicitacao">Anexos:</span>
                <select name="filtro_suporte_anexo">
                    <option value="">...</option>
                    <option value="n" <?php if ((isset($_GET['filtro_suporte_anexo'])) and (!(strcmp("n", $_GET['filtro_suporte_anexo'])))) {echo "selected=\"selected\"";} ?>>Não</option>
                    <option value="s" <?php if ((isset($_GET['filtro_suporte_anexo'])) and (!(strcmp("s", $_GET['filtro_suporte_anexo'])))) {echo "selected=\"selected\"";} ?>>Sim</option>
                </select>
                </td>
    
            </tr>
        </table>
        </div>
        <? } ?>
        
        <? if(($row_relatorio['filtro_suporte_solicitante'] == 1 or $row_relatorio['filtro_suporte_atendente'] == 1) and $relatorio_id_grupo > 0){ ?>
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td style="text-align: left">
                <span class="label_solicitacao">Solicitante: </span>
                <br />
                <select name="filtro_suporte_solicitante" id="filtro_suporte_solicitante" style="width: 300px;">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_suporte_solicitante'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_suporte_solicitante['IdUsuario']; ?>"
                <?php if ( (isset($_GET['filtro_suporte_solicitante'])) and (!(strcmp($row_filtro_suporte_solicitante['IdUsuario'], $_GET['filtro_suporte_solicitante']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo utf8_encode($row_filtro_suporte_solicitante['nome']); ?>
                </option>
                <?php
                } while ($row_filtro_suporte_solicitante = mysql_fetch_assoc($filtro_suporte_solicitante));
                $rows = mysql_num_rows($filtro_suporte_solicitante);
                if($rows > 0) {
                mysql_data_seek($filtro_suporte_solicitante, 0);
                $row_filtro_suporte_solicitante = mysql_fetch_assoc($filtro_suporte_solicitante);
                }
                ?>
                </select>
				</td>
        
              	<td style="text-align:right" width="300px">
                <span class="label_solicitacao">Atendente: </span>
                <br />
                <select name="filtro_suporte_atendente" id="filtro_suporte_atendente" style="width: 300px;">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_suporte_atendente'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_suporte_atendente['IdUsuario']; ?>"
                <?php if ( (isset($_GET['filtro_suporte_atendente'])) and (!(strcmp($row_filtro_suporte_atendente['IdUsuario'], $_GET['filtro_suporte_atendente']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo utf8_encode($row_filtro_suporte_atendente['nome']); ?>
                </option>
                <?php
                } while ($row_filtro_suporte_atendente = mysql_fetch_assoc($filtro_suporte_atendente));
                $rows = mysql_num_rows($filtro_suporte_atendente);
                if($rows > 0) {
                mysql_data_seek($filtro_suporte_atendente, 0);
                $row_filtro_suporte_atendente = mysql_fetch_assoc($filtro_suporte_atendente);
                }
                ?>
                </select>
                </td>
            </tr>
        </table>
        </div>
        <? } ?>
        
        <? if($row_relatorio['filtro_suporte_tipo_atendimento'] == 1 and $relatorio_id_grupo > 0){ ?>
		<div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td style="text-align: left">
                <fieldset style="border: 0px;">
                <span class="label_solicitacao">Tipo de atendimento:</span>
                
                <? do { ?>
                
                <input  name="filtro_suporte_tipo_atendimento[]" type="checkbox" value="<? echo $row_filtro_suporte_tipo_atendimento['descricao']; ?>"
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_suporte_tipo_atendimento'])){
                        foreach($_GET['filtro_suporte_tipo_atendimento'] as $filtro_suporte_tipo_atendimento_atual){
                            if($filtro_suporte_tipo_atendimento_atual == $row_filtro_suporte_tipo_atendimento['descricao']){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                /><? echo $row_filtro_suporte_tipo_atendimento['descricao']; ?>
                
                <? } while ($row_filtro_suporte_tipo_atendimento = mysql_fetch_assoc($filtro_suporte_tipo_atendimento)); ?>
                               
                <input type="checkbox" id="checkall_filtro_suporte_tipo_atendimento"  name="checkall_filtro_suporte_tipo_atendimento" />Marcar todos
                </fieldset>
				</td>
        
            </tr>
        </table>
        </div>
        <? } ?>
        
        <? if($row_relatorio['filtro_suporte_tipo_recomendacao'] == 1 and $relatorio_id_grupo > 0){ ?>
		<div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td style="text-align: left">
                <fieldset style="border: 0px;">
                <span class="label_solicitacao">Recomendação:</span>
                
                <? do { ?>
                
                <input  name="filtro_suporte_tipo_recomendacao[]" type="checkbox" value="<? echo $row_filtro_suporte_tipo_recomendacao['titulo']; ?>"
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_suporte_tipo_recomendacao'])){
                        foreach($_GET['filtro_suporte_tipo_recomendacao'] as $filtro_suporte_tipo_recomendacao_atual){
                            if($filtro_suporte_tipo_recomendacao_atual == $row_filtro_suporte_tipo_recomendacao['titulo']){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                /><? echo $row_filtro_suporte_tipo_recomendacao['titulo']; ?>
                
                <? } while ($row_filtro_suporte_tipo_recomendacao = mysql_fetch_assoc($filtro_suporte_tipo_recomendacao)); ?>
                               
                <input type="checkbox" id="checkall_filtro_suporte_tipo_recomendacao"  name="checkall_filtro_suporte_tipo_recomendacao" />Marcar todos
                </fieldset>
				</td>
        
            </tr>
        </table>
        </div>
        <? } ?>

        <? if($row_relatorio['filtro_suporte_tipo_visita'] == 1 and $relatorio_id_grupo > 0){ ?>
		<div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td style="text-align: left">
                <fieldset style="border: 0px;">
                <span class="label_solicitacao">Tipo de visita:</span>
             
                <input name="filtro_suporte_tipo_visita[]" type="checkbox" value="1" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_suporte_tipo_visita'])){
                        foreach($_GET['filtro_suporte_tipo_visita'] as $filtro_suporte_tipo_visita){
                            if($filtro_suporte_tipo_visita=="1"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />Nenhum
                
                <input name="filtro_suporte_tipo_visita[]" type="checkbox" value="2" 
                    <?
                    // verificar se foi selecionada

                    if(isset($_GET['filtro_suporte_tipo_visita'])){
                        foreach($_GET['filtro_suporte_tipo_visita'] as $filtro_suporte_tipo_visita){
                            if($filtro_suporte_tipo_visita=="2"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />Sem Limite
                
                <input name="filtro_suporte_tipo_visita[]" type="checkbox" value="3" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_suporte_tipo_visita'])){
                        foreach($_GET['filtro_suporte_tipo_visita'] as $filtro_suporte_tipo_visita){
                            if($filtro_suporte_tipo_visita=="3"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />Mensal

                <input name="filtro_suporte_tipo_visita[]" type="checkbox" value="4" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_suporte_tipo_visita'])){
                        foreach($_GET['filtro_suporte_tipo_visita'] as $filtro_suporte_tipo_visita){
                            if($filtro_suporte_tipo_visita=="4"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />Trimestral

                <input name="filtro_suporte_tipo_visita[]" type="checkbox" value="5" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_suporte_tipo_visita'])){
                        foreach($_GET['filtro_suporte_tipo_visita'] as $filtro_suporte_tipo_visita){
                            if($filtro_suporte_tipo_visita=="5"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />Sem visita        
                               
                <input type="checkbox" id="checkall_filtro_suporte_tipo_visita"  name="checkall_filtro_suporte_tipo_visita" />Marcar todos
                </fieldset>
				</td>
        
            </tr>
        </table>
        </div>
        <? } ?>
         
     
        <? if(($row_relatorio['filtro_solicitacao_solicitante'] == 1 or $row_relatorio['filtro_solicitacao_executante'] == 1) and $relatorio_id_grupo > 0){ ?>
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td style="text-align: left">
                <span class="label_solicitacao">Solicitante: </span>
                <br />
                <select name="filtro_solicitacao_solicitante" id="filtro_solicitacao_solicitante" style="width: 300px;">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_solicitacao_solicitante'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_solicitacao_solicitante['IdUsuario']; ?>"
                <?php if ( (isset($_GET['filtro_solicitacao_solicitante'])) and (!(strcmp($row_filtro_solicitacao_solicitante['IdUsuario'], $_GET['filtro_solicitacao_solicitante']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo utf8_encode($row_filtro_solicitacao_solicitante['nome']); ?>
                </option>
                <?php
                } while ($row_filtro_solicitacao_solicitante = mysql_fetch_assoc($filtro_solicitacao_solicitante));
                $rows = mysql_num_rows($filtro_solicitacao_solicitante);
                if($rows > 0) {
                mysql_data_seek($filtro_solicitacao_solicitante, 0);
                $row_filtro_solicitacao_solicitante = mysql_fetch_assoc($filtro_solicitacao_solicitante);
                }
                ?>
                </select>
				</td>
        
              	<td style="text-align:right" width="300px">
                <span class="label_solicitacao">Executante: </span>
                <br />
                <select name="filtro_solicitacao_executante" id="filtro_solicitacao_executante" style="width: 300px;">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_solicitacao_executante'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_solicitacao_executante['IdUsuario']; ?>"
                <?php if ( (isset($_GET['filtro_solicitacao_executante'])) and (!(strcmp($row_filtro_solicitacao_executante['IdUsuario'], $_GET['filtro_solicitacao_executante']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo utf8_encode($row_filtro_solicitacao_executante['nome']); ?>
                </option>
                <?php
                } while ($row_filtro_solicitacao_executante = mysql_fetch_assoc($filtro_solicitacao_executante));
                $rows = mysql_num_rows($filtro_solicitacao_executante);
                if($rows > 0) {
                mysql_data_seek($filtro_solicitacao_executante, 0);
                $row_filtro_solicitacao_executante = mysql_fetch_assoc($filtro_solicitacao_executante);
                }
                ?>
                </select>
                </td>
            </tr>
        </table>
        </div>
        <? } ?>
        
        <? if(($row_relatorio['filtro_solicitacao_operador'] == 1 or $row_relatorio['filtro_solicitacao_testador'] == 1) and $relatorio_id_grupo > 0){ ?>
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td style="text-align: left">
                <span class="label_solicitacao">Operador: </span>
                <br />
                <select name="filtro_solicitacao_operador" id="filtro_solicitacao_operador" style="width: 300px;">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_solicitacao_operador'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_solicitacao_operador['IdUsuario']; ?>"
                <?php if ( (isset($_GET['filtro_solicitacao_operador'])) and (!(strcmp($row_filtro_solicitacao_operador['IdUsuario'], $_GET['filtro_solicitacao_operador']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo utf8_encode($row_filtro_solicitacao_operador['nome']); ?>
                </option>
                <?php
                } while ($row_filtro_solicitacao_operador = mysql_fetch_assoc($filtro_solicitacao_operador));
                $rows = mysql_num_rows($filtro_solicitacao_operador);
                if($rows > 0) {
                mysql_data_seek($filtro_solicitacao_operador, 0);
                $row_filtro_solicitacao_operador = mysql_fetch_assoc($filtro_solicitacao_operador);
                }
                ?>
                </select>
				</td>
        
              	<td style="text-align:right" width="300px">
                <span class="label_solicitacao">Testador: </span>
                <br />
                <select name="filtro_solicitacao_testador" id="filtro_solicitacao_testador" style="width: 300px;">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_solicitacao_testador'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_solicitacao_testador['IdUsuario']; ?>"
                <?php if ( (isset($_GET['filtro_solicitacao_testador'])) and (!(strcmp($row_filtro_solicitacao_testador['IdUsuario'], $_GET['filtro_solicitacao_testador']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo utf8_encode($row_filtro_solicitacao_testador['nome']); ?>
                </option>
                <?php
                } while ($row_filtro_solicitacao_testador = mysql_fetch_assoc($filtro_solicitacao_testador));
                $rows = mysql_num_rows($filtro_solicitacao_testador);
                if($rows > 0) {
                mysql_data_seek($filtro_solicitacao_testador, 0);
                $row_filtro_solicitacao_testador = mysql_fetch_assoc($filtro_solicitacao_testador);
                }
                ?>
                </select>
                </td>
            </tr>
        </table>
        </div>
        <? } ?>
        
        <? if(($row_relatorio['filtro_solicitacao_tipo'] == 1 or $row_relatorio['filtro_solicitacao_desmembrada'] == 1) and $relatorio_id_grupo > 0){ ?>
		<div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <? if($row_relatorio['filtro_solicitacao_tipo'] == 1 and $relatorio_id_grupo > 0){ ?>
                    <td style="text-align: left">

                        <fieldset style="border: 0px;">
                        <span class="label_solicitacao">Tipo de solicitação:</span>
                        
                        <? do { ?>
                        
                        <input  name="filtro_solicitacao_tipo[]" type="checkbox" value="<? echo $row_filtro_solicitacao_tipo['titulo']; ?>"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['filtro_solicitacao_tipo'])){
                                foreach($_GET['filtro_solicitacao_tipo'] as $filtro_solicitacao_tipo_atual){
                                    if($filtro_solicitacao_tipo_atual == $row_filtro_solicitacao_tipo['titulo']){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        /><? echo $row_filtro_solicitacao_tipo['titulo']; ?>
                        
                        <? } while ($row_filtro_solicitacao_tipo = mysql_fetch_assoc($filtro_solicitacao_tipo)); ?>
                                    
                        <input type="checkbox" id="checkall_filtro_solicitacao_tipo"  name="checkall_filtro_solicitacao_tipo" />Marcar todos
                        </fieldset>

                    </td>
                <? } ?>
                
                <? if($row_relatorio['filtro_solicitacao_desmembrada'] == 1 and $relatorio_id_grupo > 0){ ?>
                    <td style="text-align: right">
                    <span class="label_solicitacao">Desmembrada:</span>
                    <select name="filtro_solicitacao_desmembrada">
                        <option value="">...</option>
                        <option value="n" <?php if ((isset($_GET['filtro_solicitacao_desmembrada'])) and (!(strcmp("n", $_GET['filtro_solicitacao_desmembrada'])))) {echo "selected=\"selected\"";} ?>>Não</option>
                        <option value="s" <?php if ((isset($_GET['filtro_solicitacao_desmembrada'])) and (!(strcmp("s", $_GET['filtro_solicitacao_desmembrada'])))) {echo "selected=\"selected\"";} ?>>Sim</option>
                    </select>
                    </td>
                <? } ?>
        
            </tr>
        </table>
        </div>
        <? } ?>
        
        <? if($row_relatorio['filtro_solicitacao_status'] == 1 and $relatorio_id_grupo > 0){ ?>
		<div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left" valign="top">
                <fieldset>
                    <span class="label_solicitacao">Status:</span>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="250" valign="top">

                        
                        <input  name="filtro_solicitacao_status[]" type="checkbox" class="checkbox" value="pendente solicitante"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['filtro_solicitacao_status'])){
                                foreach($_GET["filtro_solicitacao_status"] as $filtro_solicitacao_status){
                                    if($filtro_solicitacao_status=="pendente solicitante"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />pendente solicitante 
                        
                        <br>
                                                
                        <input  name="filtro_solicitacao_status[]" type="checkbox" class="checkbox" value="pendente operador"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['filtro_solicitacao_status'])){
                                foreach($_GET["filtro_solicitacao_status"] as $filtro_solicitacao_status){
                                    if($filtro_solicitacao_status=="pendente operador"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />pendente operador
                        
                        <br>
                        
                        <input  name="filtro_solicitacao_status[]" type="checkbox" class="checkbox" value="pendente executante"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['filtro_solicitacao_status'])){
                                foreach($_GET["filtro_solicitacao_status"] as $filtro_solicitacao_status){
                                    if($filtro_solicitacao_status=="pendente executante"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />pendente executante
                    
                    <br>
                                            
                        <input  name="filtro_solicitacao_status[]" type="checkbox" class="checkbox" value="pendente testador"
                        <?
                        // verificar se foi selecionada
                        if(isset($_GET['filtro_solicitacao_status'])){
                            foreach($_GET["filtro_solicitacao_status"] as $filtro_solicitacao_status){
                                if($filtro_solicitacao_status=="pendente testador"){
                                    echo "checked=\"checked\"";
                                }
                            }
                        }
                        // verificar se foi selecionada
                        ?>
                    />pendente testador
                        
                        </td>
                        
                        <td width="250" valign="top">                     
   
                        <input  name="filtro_solicitacao_status[]" type="checkbox" class="checkbox" value="encaminhada para solicitante"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['filtro_solicitacao_status'])){
                                foreach($_GET["filtro_solicitacao_status"] as $filtro_solicitacao_status){
                                    if($filtro_solicitacao_status=="encaminhada para solicitante"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />encaminhada para solicitante 
                        
                        <br>
                                                
                        <input  name="filtro_solicitacao_status[]" type="checkbox" class="checkbox" value="encaminhada para operador"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['filtro_solicitacao_status'])){
                                foreach($_GET["filtro_solicitacao_status"] as $filtro_solicitacao_status){
                                    if($filtro_solicitacao_status=="encaminhada para operador"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />encaminhada para operador
                        
                        <br>
                        
                        <input  name="filtro_solicitacao_status[]" type="checkbox" class="checkbox" value="encaminhada para executante"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['filtro_solicitacao_status'])){
                                foreach($_GET["filtro_solicitacao_status"] as $filtro_solicitacao_status){
                                    if($filtro_solicitacao_status=="encaminhada para executante"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />encaminhada para executante
                        
                        <br>
                        
                        <input  name="filtro_solicitacao_status[]" type="checkbox" class="checkbox" value="encaminhada para testador"
                        <?
                        // verificar se foi selecionada
                        if(isset($_GET['filtro_solicitacao_status'])){
                            foreach($_GET["filtro_solicitacao_status"] as $filtro_solicitacao_status){
                                if($filtro_solicitacao_status=="encaminhada para testador"){
                                    echo "checked=\"checked\"";
                                }
                            }
                        }
                        // verificar se foi selecionada
                        ?>
                    />encaminhada para testador
                        
                        </td>
                        
                        <td width="250" valign="top">
                                        
                        <input  name="filtro_solicitacao_status[]" type="checkbox" class="checkbox" value="encaminhada para analista"
                        <?
                        // verificar se foi selecionada
                        if(isset($_GET['filtro_solicitacao_status'])){
                            foreach($_GET["filtro_solicitacao_status"] as $filtro_solicitacao_status){
                                if($filtro_solicitacao_status=="encaminhada para analista"){
                                    echo "checked=\"checked\"";
                                }
                            }
                        }
                        // verificar se foi selecionada
                        ?>
                    />encaminhada para analista
                    
                    <br>
                    
                    <input  name="filtro_solicitacao_status[]" type="checkbox" class="checkbox" value="devolvida para solicitante"
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_solicitacao_status'])){
                        foreach($_GET["filtro_solicitacao_status"] as $filtro_solicitacao_status){
                            if($filtro_solicitacao_status=="devolvida para solicitante"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />devolvida para solicitante
                
                <br>
               
                        <input  name="filtro_solicitacao_status[]" type="checkbox" class="checkbox" value="devolvida para operador"
                        <?
                        // verificar se foi selecionada
                        if(isset($_GET['filtro_solicitacao_status'])){
                            foreach($_GET["filtro_solicitacao_status"] as $filtro_solicitacao_status){
                                if($filtro_solicitacao_status=="devolvida para operador"){
                                    echo "checked=\"checked\"";
                                }
                            }
                        }
                        // verificar se foi selecionada
                        ?>
                    />devolvida para operador
                    
                    <br>
                    
                        <input  name="filtro_solicitacao_status[]" type="checkbox" class="checkbox" value="devolvida para executante"
                        <?
                        // verificar se foi selecionada
                        if(isset($_GET['filtro_solicitacao_status'])){
                            foreach($_GET["filtro_solicitacao_status"] as $filtro_solicitacao_status){
                                if($filtro_solicitacao_status=="devolvida para executante"){
                                    echo "checked=\"checked\"";
                                }
                            }
                        }
                        // verificar se foi selecionada
                        ?>
                    />devolvida para executante
                    
                    </td>
                    <td valign="top">
                    
                        <input  name="filtro_solicitacao_status[]" type="checkbox" class="checkbox" value="devolvida para testador"
                        <?
                        // verificar se foi selecionada
                        if(isset($_GET['filtro_solicitacao_status'])){
                            foreach($_GET["filtro_solicitacao_status"] as $filtro_solicitacao_status){
                                if($filtro_solicitacao_status=="devolvida para testador"){
                                    echo "checked=\"checked\"";
                                }
                            }
                        }
                        // verificar se foi selecionada
                        ?>
                    />devolvida para testador
                    
                    <br>  
                    
                        
                        <input type="checkbox" class="checkbox" id="checkall_filtro_solicitacao_status"  name="checkall_filtro_solicitacao_status" />Marcar todos
                        
                        </td>
                    </tr>
                    </table>
				</fieldset>
                </td>
                
          </tr>
        </table>
        </div>
        <? } ?>


        <? if($row_relatorio['filtro_prospeccao_usuario_responsavel'] == 1 and $relatorio_id_grupo > 0){ ?>
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
        
              	<td style="text-align:left">
                <span class="label_solicitacao">Usuário responsável: </span>
                <br />
                <select name="filtro_prospeccao_usuario_responsavel" id="filtro_prospeccao_usuario_responsavel" style="width: 300px;">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_prospeccao_usuario_responsavel'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_prospeccao_usuario_responsavel['IdUsuario']; ?>"
                <?php if ( (isset($_GET['filtro_prospeccao_usuario_responsavel'])) and (!(strcmp($row_filtro_prospeccao_usuario_responsavel['IdUsuario'], $_GET['filtro_prospeccao_usuario_responsavel']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo utf8_encode($row_filtro_prospeccao_usuario_responsavel['nome']); ?>
                </option>
                <?php
                } while ($row_filtro_prospeccao_usuario_responsavel = mysql_fetch_assoc($filtro_prospeccao_usuario_responsavel));
                $rows = mysql_num_rows($filtro_prospeccao_usuario_responsavel);
                if($rows > 0) {
                mysql_data_seek($filtro_prospeccao_usuario_responsavel, 0);
                $row_filtro_prospeccao_usuario_responsavel = mysql_fetch_assoc($filtro_prospeccao_usuario_responsavel);
                }
                ?>
                </select>
                </td>
            </tr>
        </table>
        </div>
        <? } ?>
        
        <? if(($row_relatorio['filtro_prospeccao_tipo_cliente'] == 1 or $row_relatorio['filtro_prospeccao_ativo_passivo'] == 1) and $relatorio_id_grupo > 0){ ?>
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td style="text-align: left">
                <span class="label_solicitacao">Tipo de cliente: </span>
                <br />
                <select name="filtro_prospeccao_tipo_cliente" id="filtro_prospeccao_tipo_cliente" style="width: 200px;">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_prospeccao_tipo_cliente'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <option value="<?php echo "a"; ?>"<?php if ( (isset($_GET['filtro_prospeccao_tipo_cliente'])) and (!(strcmp("a", $_GET['filtro_prospeccao_tipo_cliente']))) ) {echo "selected=\"selected\"";} ?>> Antigo</option>
                <option value="<?php echo "n"; ?>"<?php if ( (isset($_GET['filtro_prospeccao_tipo_cliente'])) and (!(strcmp("n", $_GET['filtro_prospeccao_tipo_cliente']))) ) {echo "selected=\"selected\"";} ?>> Novo</option>
                </select>
				</td>
        
              	<td style="text-align:right" width="300px">
                <span class="label_solicitacao">Prospect: </span>
                <br />
                <select name="filtro_prospeccao_ativo_passivo" id="filtro_prospeccao_ativo_passivo" style="width: 200px;">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_prospeccao_ativo_passivo'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <option value="<?php echo "a"; ?>"<?php if ( (isset($_GET['filtro_prospeccao_ativo_passivo'])) and (!(strcmp("a", $_GET['filtro_prospeccao_ativo_passivo']))) ) {echo "selected=\"selected\"";} ?>> Ativo</option>
                <option value="<?php echo "p"; ?>"<?php if ( (isset($_GET['filtro_prospeccao_ativo_passivo'])) and (!(strcmp("p", $_GET['filtro_prospeccao_ativo_passivo']))) ) {echo "selected=\"selected\"";} ?>> Passivo</option>
                </select>
                </td>
            </tr>
        </table>
        </div>
        <? } ?>
        
        <? if($row_relatorio['filtro_prospeccao_situacao'] == 1 and $relatorio_id_grupo > 0){ ?>
		<div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td style="text-align: left">
                <fieldset style="border: 0px;">
                <span class="label_solicitacao">Situação:</span>
                
                <input name="filtro_prospeccao_situacao[]" type="checkbox" value="analisada" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_prospeccao_situacao'])){
                        foreach($_GET['filtro_prospeccao_situacao'] as $filtro_prospeccao_situacao){
                            if($filtro_prospeccao_situacao=="analisada"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />analisada
    
                <input name="filtro_prospeccao_situacao[]" type="checkbox" value="em negociação" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_prospeccao_situacao'])){
                        foreach($_GET['filtro_prospeccao_situacao'] as $filtro_prospeccao_situacao){
                            if($filtro_prospeccao_situacao=="em negociação"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />em negociação
                
                <input name="filtro_prospeccao_situacao[]" type="checkbox" value="solicitado agendamento" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_prospeccao_situacao'])){
                        foreach($_GET['filtro_prospeccao_situacao'] as $filtro_prospeccao_situacao){
                            if($filtro_prospeccao_situacao=="solicitado agendamento"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />solicitado agendamento
                
                <input name="filtro_prospeccao_situacao[]" type="checkbox" value="venda realizada" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_prospeccao_situacao'])){
                        foreach($_GET['filtro_prospeccao_situacao'] as $filtro_prospeccao_situacao){
                            if($filtro_prospeccao_situacao=="venda realizada"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />venda realizada

                <input name="filtro_prospeccao_situacao[]" type="checkbox" value="venda perdida" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_prospeccao_situacao'])){
                        foreach($_GET['filtro_prospeccao_situacao'] as $filtro_prospeccao_situacao){
                            if($filtro_prospeccao_situacao=="venda perdida"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />venda perdida
                
                <input name="filtro_prospeccao_situacao[]" type="checkbox" value="cancelada" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_prospeccao_situacao'])){
                        foreach($_GET['filtro_prospeccao_situacao'] as $filtro_prospeccao_situacao){
                            if($filtro_prospeccao_situacao=="cancelada"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />cancelada

                <input type="checkbox" id="checkall_filtro_prospeccao_situacao"  name="checkall_filtro_prospeccao_situacao" />Marcar todos
                </fieldset>
				</td>
        
            </tr>
        </table>
        </div>
        <? } ?>
        
        <? if($row_relatorio['filtro_prospeccao_status'] == 1 and $relatorio_id_grupo > 0){ ?>
		<div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left" valign="top">
                <fieldset>
                    <span class="label_solicitacao">Status:</span>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="250" valign="top">

                        
                        <input  name="filtro_prospeccao_status[]" type="checkbox" class="checkbox" value="aguardando retorno do cliente"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['filtro_prospeccao_status'])){
                                foreach($_GET["filtro_prospeccao_status"] as $filtro_prospeccao_status){
                                    if($filtro_prospeccao_status=="aguardando retorno do cliente"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />
                        aguardando retorno do cliente <br>
                                                
                        <input  name="filtro_prospeccao_status[]" type="checkbox" class="checkbox" value="aguardando atendente"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['filtro_prospeccao_status'])){
                                foreach($_GET["filtro_prospeccao_status"] as $filtro_prospeccao_status){
                                    if($filtro_prospeccao_status=="aguardando atendente"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />aguardando atendente
                        
                        <br>
                        
                        <input  name="filtro_prospeccao_status[]" type="checkbox" class="checkbox" value="aguardando agendamento"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['filtro_prospeccao_status'])){
                                foreach($_GET["filtro_prospeccao_status"] as $filtro_prospeccao_status){
                                    if($filtro_prospeccao_status=="aguardando agendamento"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />aguardando agendamento
                        
                        </td>
                        
                        <td valign="top">                     
                    
                        <input  name="filtro_prospeccao_status[]" type="checkbox" class="checkbox" value="encaminhada para usuario responsavel"
                        <?
                        // verificar se foi selecionada
                        if(isset($_GET['filtro_prospeccao_status'])){
                            foreach($_GET["filtro_prospeccao_status"] as $filtro_prospeccao_status){
                                if($filtro_prospeccao_status=="encaminhada para usuario responsavel"){
                                    echo "checked=\"checked\"";
                                }
                            }
                        }
                        // verificar se foi selecionada
                        ?>
                    />encaminhada para usuario responsavel
                    
                    <br>
                    
                        <input  name="filtro_prospeccao_status[]" type="checkbox" class="checkbox" value="pendente usuario responsavel"
                        <?
                        // verificar se foi selecionada
                        if(isset($_GET['filtro_prospeccao_status'])){
                            foreach($_GET["filtro_prospeccao_status"] as $filtro_prospeccao_status){
                                if($filtro_prospeccao_status=="pendente usuario responsavel"){
                                    echo "checked=\"checked\"";
                                }
                            }
                        }
                        // verificar se foi selecionada
                        ?>
                    />pendente usuario responsavel
                    
                    <br>
                        
                        <input type="checkbox" class="checkbox" id="checkall_filtro_prospeccao_status"  name="checkall_filtro_prospeccao_status" />Marcar todos
                        
                        </td>
                    </tr>
                    </table>
				</fieldset>
                </td>
                
          </tr>
        </table>
        </div>
        <? } ?>
        
        <? if($row_relatorio['filtro_prospeccao_baixa_perda_motivo'] == 1 and $relatorio_id_grupo > 0){ ?>
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td style="text-align: left">
                <span class="label_solicitacao">Motivo: </span>
                <br />
                <select name="filtro_prospeccao_baixa_perda_motivo" id="filtro_prospeccao_baixa_perda_motivo" style="width: 200px;">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_prospeccao_baixa_perda_motivo'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <option value="<?php echo "concorrência"; ?>"<?php if ( (isset($_GET['filtro_prospeccao_baixa_perda_motivo'])) and (!(strcmp("concorrência", $_GET['filtro_prospeccao_baixa_perda_motivo']))) ) {echo "selected=\"selected\"";} ?>> concorrência</option>
                <option value="<?php echo "falta de recurso"; ?>"<?php if ( (isset($_GET['filtro_prospeccao_baixa_perda_motivo'])) and (!(strcmp("falta de recurso", $_GET['filtro_prospeccao_baixa_perda_motivo']))) ) {echo "selected=\"selected\"";} ?>> falta de recurso</option>
                </select>
				</td>
            </tr>
        </table>
        </div>
        <? } ?>
        


        <? if($row_relatorio['filtro_venda_usuario_responsavel'] == 1 and $relatorio_id_grupo > 0){ ?>
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
        
              	<td style="text-align:left">
                <span class="label_solicitacao">Usuário responsável: </span>
                <br />
                <select name="filtro_venda_usuario_responsavel" id="filtro_venda_usuario_responsavel" style="width: 300px;">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_venda_usuario_responsavel'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_venda_usuario_responsavel['IdUsuario']; ?>"
                <?php if ( (isset($_GET['filtro_venda_usuario_responsavel'])) and (!(strcmp($row_filtro_venda_usuario_responsavel['IdUsuario'], $_GET['filtro_venda_usuario_responsavel']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo utf8_encode($row_filtro_venda_usuario_responsavel['nome']); ?>
                </option>
                <?php
                } while ($row_filtro_venda_usuario_responsavel = mysql_fetch_assoc($filtro_venda_usuario_responsavel));
                $rows = mysql_num_rows($filtro_venda_usuario_responsavel);
                if($rows > 0) {
                mysql_data_seek($filtro_venda_usuario_responsavel, 0);
                $row_filtro_venda_usuario_responsavel = mysql_fetch_assoc($filtro_venda_usuario_responsavel);
                }
                ?>
                </select>
                </td>
            </tr>
        </table>
        </div>
        <? } ?>
        
        <? if($row_relatorio['filtro_venda_situacao'] == 1 and $relatorio_id_grupo > 0){ ?>
		<div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td style="text-align: left">
                <fieldset style="border: 0px;">
                <span class="label_solicitacao">Situação:</span>
                
                <input name="filtro_venda_situacao[]" type="checkbox" value="analisada" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_venda_situacao'])){
                        foreach($_GET['filtro_venda_situacao'] as $filtro_venda_situacao){
                            if($filtro_venda_situacao=="analisada"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />analisada
    
                <input name="filtro_venda_situacao[]" type="checkbox" value="em execução" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_venda_situacao'])){
                        foreach($_GET['filtro_venda_situacao'] as $filtro_venda_situacao){
                            if($filtro_venda_situacao=="em execução"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />em execução

                <input name="filtro_venda_situacao[]" type="checkbox" value="solucionada" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_venda_situacao'])){
                        foreach($_GET['filtro_venda_situacao'] as $filtro_venda_situacao){
                            if($filtro_venda_situacao=="solucionada"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />solucionada
                
                <input name="filtro_venda_situacao[]" type="checkbox" value="cancelada" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_venda_situacao'])){
                        foreach($_GET['filtro_venda_situacao'] as $filtro_venda_situacao){
                            if($filtro_venda_situacao=="cancelada"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />cancelada

                <input type="checkbox" id="checkall_filtro_venda_situacao"  name="checkall_filtro_venda_situacao" />Marcar todos
                </fieldset>
				</td>
        
            </tr>
        </table>
        </div>
        <? } ?>
        
        <? if($row_relatorio['filtro_venda_modulos'] == 1 and $relatorio_id_grupo > 0){ ?>
		<div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td style="text-align: left">
                <fieldset style="border: 0px;">
                <span class="label_solicitacao">Módulos:</span>
                
                <? do { ?>
                
                <input  name="filtro_venda_modulos[]" type="checkbox" value="<? echo $row_filtro_venda_modulos['descricao']; ?>"
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['filtro_venda_modulos'])){
                        foreach($_GET['filtro_venda_modulos'] as $filtro_venda_modulos_atual){
                            if($filtro_venda_modulos_atual == $row_filtro_venda_modulos['descricao']){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                /><? echo $row_filtro_venda_modulos['descricao']; ?>
                
                <? } while ($row_filtro_venda_modulos = mysql_fetch_assoc($filtro_venda_modulos)); ?>
                               
                <input type="checkbox" id="checkall_filtro_venda_modulos"  name="checkall_filtro_venda_modulos" />Marcar todos
                </fieldset>
				</td>
        
            </tr>
        </table>
        </div>
        <? } ?>
        
        <? if($row_relatorio['filtro_venda_tipo_cliente'] == 1 and $relatorio_id_grupo > 0){ ?>
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td style="text-align: left">
                <span class="label_solicitacao">Tipo de cliente: </span>
                <br />
                <select name="filtro_venda_tipo_cliente" id="filtro_venda_tipo_cliente" style="width: 200px;">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_venda_tipo_cliente'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <option value="<?php echo "a"; ?>"<?php if ( (isset($_GET['filtro_venda_tipo_cliente'])) and (!(strcmp("a", $_GET['filtro_venda_tipo_cliente']))) ) {echo "selected=\"selected\"";} ?>> Antigo</option>
                <option value="<?php echo "n"; ?>"<?php if ( (isset($_GET['filtro_venda_tipo_cliente'])) and (!(strcmp("n", $_GET['filtro_venda_tipo_cliente']))) ) {echo "selected=\"selected\"";} ?>> Novo</option>
                </select>
				</td>
            </tr>
        </table>
        </div>
        <? } ?>
        
        <? if($row_relatorio['filtro_venda_usuario_responsavel'] == 1 and $relatorio_id_grupo > 0){ ?>
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
        
              	<td style="text-align:left">
                <span class="label_solicitacao">Usuário responsável: </span>
                <br />
                <select name="filtro_venda_usuario_responsavel" id="filtro_venda_usuario_responsavel" style="width: 300px;">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_venda_usuario_responsavel'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_venda_usuario_responsavel['IdUsuario']; ?>"
                <?php if ( (isset($_GET['filtro_venda_usuario_responsavel'])) and (!(strcmp($row_filtro_venda_usuario_responsavel['IdUsuario'], $_GET['filtro_venda_usuario_responsavel']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo utf8_encode($row_filtro_venda_usuario_responsavel['nome']); ?>
                </option>
                <?php
                } while ($row_filtro_venda_usuario_responsavel = mysql_fetch_assoc($filtro_venda_usuario_responsavel));
                $rows = mysql_num_rows($filtro_venda_usuario_responsavel);
                if($rows > 0) {
                mysql_data_seek($filtro_venda_usuario_responsavel, 0);
                $row_filtro_venda_usuario_responsavel = mysql_fetch_assoc($filtro_venda_usuario_responsavel);
                }
                ?>
                </select>
                </td>
            </tr>
        </table>
        </div>
        <? } ?>
        
        <? if($row_relatorio['filtro_administrativo_atraso'] == 1 and $relatorio_id_grupo > 0){ ?>
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
        
              	<td style="text-align:left">
                <span class="label_solicitacao">Tempo vencido (dias): </span>
                <br />
                <select name="filtro_administrativo_atraso" id="filtro_administrativo_atraso" style="width: 200px;">
                <option value="1"<?php if ( (!(strcmp("", isset($_GET['filtro_administrativo_atraso'])))) or ((isset($_GET['filtro_administrativo_atraso'])) and (!(strcmp(1, $_GET['filtro_administrativo_atraso'])))) ) {echo "selected=\"selected\"";} ?>>Todos vencidos</option>
                <option value="15"<?php if ( (isset($_GET['filtro_administrativo_atraso'])) and (!(strcmp(15, $_GET['filtro_administrativo_atraso']))) ) {echo "selected=\"selected\"";} ?>>Acima de 15</option>
                <option value="30"<?php if ( (isset($_GET['filtro_administrativo_atraso'])) and (!(strcmp(30, $_GET['filtro_administrativo_atraso']))) ) {echo "selected=\"selected\"";} ?>>Acima de 30</option>
                <option value="60"<?php if ( (isset($_GET['filtro_administrativo_atraso'])) and (!(strcmp(60, $_GET['filtro_administrativo_atraso']))) ) {echo "selected=\"selected\"";} ?>>Acima de 60</option>
                <option value="90"<?php if ( (isset($_GET['filtro_administrativo_atraso'])) and (!(strcmp(90, $_GET['filtro_administrativo_atraso']))) ) {echo "selected=\"selected\"";} ?>>Acima de 90</option>
                <option value="120"<?php if ( (isset($_GET['filtro_administrativo_atraso'])) and (!(strcmp(120, $_GET['filtro_administrativo_atraso']))) ) {echo "selected=\"selected\"";} ?>>Acima de 120</option>
                <option value="150"<?php if ( (isset($_GET['filtro_administrativo_atraso'])) and (!(strcmp(150, $_GET['filtro_administrativo_atraso']))) ) {echo "selected=\"selected\"";} ?>>Acima de 150</option>
                <option value="180"<?php if ( (isset($_GET['filtro_administrativo_atraso'])) and (!(strcmp(180, $_GET['filtro_administrativo_atraso']))) ) {echo "selected=\"selected\"";} ?>>Acima de 180</option>
                <option value="210"<?php if ( (isset($_GET['filtro_administrativo_atraso'])) and (!(strcmp(210, $_GET['filtro_administrativo_atraso']))) ) {echo "selected=\"selected\"";} ?>>Acima de 210</option>
                <option value="240"<?php if ( (isset($_GET['filtro_administrativo_atraso'])) and (!(strcmp(240, $_GET['filtro_administrativo_atraso']))) ) {echo "selected=\"selected\"";} ?>>Acima de 240</option>
                <option value="270"<?php if ( (isset($_GET['filtro_administrativo_atraso'])) and (!(strcmp(270, $_GET['filtro_administrativo_atraso']))) ) {echo "selected=\"selected\"";} ?>>Acima de 270</option>
                <option value="300"<?php if ( (isset($_GET['filtro_administrativo_atraso'])) and (!(strcmp(300, $_GET['filtro_administrativo_atraso']))) ) {echo "selected=\"selected\"";} ?>>Acima de 300</option>
                </select>
                </td>
            </tr>
        </table>
        </div>
        <? } ?>

		<!-- botões -->
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left">
                <input name="Gerar relatório" type="submit" value="Gerar relatório" class="botao_geral2" style="width: 130px" />
                <input onclick="clear_form_elements(this.form)" type="button" value="Limpar campos" class="botao_geral2" style="width: 130px" />
				</td>
            </tr>
        </table>
        </div>  
		<!-- fim - botões -->
        
</form>
</div>

</div>