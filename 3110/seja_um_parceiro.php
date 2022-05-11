<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script src="js/jquery.js"></script>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Success Sistemas</title>
<link href="css/guia.css" rel="stylesheet" type="text/css">
</head>

<body>
<?php include('topo.php'); ?>
<table class="tabela_geral" cellpadding="0" cellspacing="0">

<tr>
	<td class="tabela_geral_acima"></td>
</tr>

<tr>
	<td class="tabela_geral_centro">

<!-- menu/conteúdo - início -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td class="menu"><?php include('menu.php'); ?></td>
<td class="conteudo">
    
<!-- conteúdo da página - início -->
<div class="conteudo_div">

  	<!-- caminho/voltar/título -->

    <div class="voltar"><a href="javascript:history.go(-1);">Voltar</a></div>
    <div class="caminho">
		<a href="index.php">Página Inicial</a> >> <strong>Seja um parceiro</strong></div>
        
    <div class="titulos">Seja um parceiro</div>
  	<p align="justify">
  	  <!-- caminho/voltar/título -  fim -->
  	  
  	  
  	  Se você tem experiência em treinamento ou suporte de informática, na sua região tem internet, possui um número significante de empresas na área comercial (Supermercados, Acougues, Loja de Ropas, Material de Construção, Ferragens e ramos semelhantes), tem disponibilidade para viajar, entre em contato com a Success Sistemas e faça o treinamento para suporte aos Sistemas Success. Rentabilidade garantida e com incentivos para iniciantes, confira.</p>
  	<p align="justify">Utilize o formulário abaixo ou envie um e-mail para: <a href="mailto:success@success.inf.br"><strong>success@success.inf.br</strong></a>.</p>
  	
<form id="seja_um_parceiro" name="seja_um_parceiro" method="post" action="seja_um_parceiro_enviado.php">
  	  Nome:<br />
  	      <input name="nome" type="text" id="nome" size="50" />
  	      <br />
  	      <br />
  	    E-mail:<br />
  	    <input name="email" type="text" id="email" size="50" />
        <br />
  	    <br />
  	    Cidade:<br />
  	    <input name="cidade" type="text" id="cidade" size="30" />
  	    <br />
  	    <br />
  	    Estado:<br />
  	    <input name="estado" type="text" id="estado" size="30" />
  	    <br />
  	    <br />
  	  Telefone de contato:<br />
  	  <input name="telefone" type="text" id="telefone" size="30" />
  	  <br />
  	  <br />
  	  Outras informações:<br />
  	  <textarea name="outras" id="outras" cols="45" rows="5"></textarea>
  	  <br />
  	  <br />
  	  <input type="submit" name="button" id="button" value="Enviar" />
</form>
  	</div>
<!-- conteúdo da página - fim --></td>
</tr>
</table>
<!-- menu/conteúdo - fim -->

	</td>
</tr>

<tr>
	<td class="tabela_geral_abaixo"></td>
</tr>

</table>
<?php include('creditos.php'); ?>
</body>

</html>