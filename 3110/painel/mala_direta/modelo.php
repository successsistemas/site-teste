<?php 
require_once('../../Connections/conexao.php');

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

require_once('../funcao.php');

// rsmala_direta
$colname_rsmala_direta = "-1";
if (isset($_POST['IdMalaDireta'])) {
  $colname_rsmala_direta = $_POST['IdMalaDireta'];
}
mysql_select_db($database_conexao, $conexao);
$query_rsmala_direta = sprintf("
SELECT 
mala_direta.titulo, mala_direta.texto 
FROM mala_direta 
WHERE mala_direta.IdMalaDireta = %s", 
GetSQLValueString($colname_rsmala_direta, "int"));
$rsmala_direta = mysql_query($query_rsmala_direta, $conexao) or die(mysql_error());
$row_rsmala_direta = mysql_fetch_assoc($rsmala_direta);
$totalRows_rsmala_direta = mysql_num_rows($rsmala_direta);
// fim - rsmala_direta
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
<!-- saved from url=(0106)mhtml:file://C:\Users\Juliano\AppData\Local\Microsoft\Windows\INetCache\Content.Outlook\0ANT6RB9\email.mht -->
<HTML xmlns='http://www.w3.org/1999/xhtml'>
<HEAD>
<META content='IE=7.0000' 
http-equiv='X-UA-Compatible'>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<!-- <META content='text/html; charset=iso-8859-1' http-equiv=Content-Type> -->
<META name=viewport 
content=width=device-width,initial-scale=1.0,maximum-scale=1.,user-scalable=no>
<STYLE type=text/css media=screen>
BODY {
	PADDING-BOTTOM: 0px;
	-MS-TEXT-SIZE-ADJUST: none;
	PADDING-TOP: 0px;
	PADDING-LEFT: 0px;
	MARGIN: 0px;
	PADDING-RIGHT: 0px;
	-webkit-text-size-adjust: none
}
P {
	PADDING-BOTTOM: 0px;
	-MS-TEXT-SIZE-ADJUST: none;
	PADDING-TOP: 0px;
	PADDING-LEFT: 0px;
	MARGIN: 0px;
	PADDING-RIGHT: 0px;
	-webkit-text-size-adjust: none
}
IMG {
	TEXT-DECORATION: none;
	OUTLINE-STYLE: none;
	LINE-HEIGHT: 100%;
	-MS-INTERPOLATION-MODE: bicubic
}
A IMG {
	BORDER-TOP-STYLE: none;
	BORDER-BOTTOM-STYLE: none;
	BORDER-RIGHT-STYLE: none;
	BORDER-LEFT-STYLE: none
}
A {
	COLOR: #02bfba
}
A:link {
	COLOR: #02bfba
}
TABLE TD {
	BORDER-COLLAPSE: collapse
}
SUP {
	FONT-SIZE: 11px !important;
	POSITION: relative;
	LINE-HEIGHT: 7px !important;
	TOP: 4px
}
.mobile_link A[href^='tel'] {
	CURSOR: default;
	COLOR: #a9a9a9 !important;
	pointer-events: auto
}
.mobile_link A[href^='sms'] {
	CURSOR: default;
	COLOR: #a9a9a9 !important;
	pointer-events: auto
}
.no-detect A {
	CURSOR: default;
	TEXT-DECORATION: none;
	COLOR: #666;
	pointer-events: auto
}
.no-detect-local A {
	COLOR: #a9a9a9
}
SPAN {
	BORDER-BOTTOM-STYLE: none;
COLOR:
}
SPAN:hover {
	BACKGROUND-COLOR: transparent
}

@media Unknown {
TD[class='main_cta'] {
	WIDTH: 100% !important;
	PADDING-BOTTOM: 0px;
	PADDING-TOP: 0px;
	PADDING-LEFT: 10px;
	PADDING-RIGHT: 10px
}
TABLE[class='main_cta'] {
	WIDTH: 100% !important;
	PADDING-BOTTOM: 0px;
	PADDING-TOP: 0px;
	PADDING-LEFT: 10px;
	PADDING-RIGHT: 10px
}
TABLE[class='header-wrp'] {
	WIDTH: 100% !important
}
A[class='banner-img'] IMG {
	HEIGHT: auto !important;
	WIDTH: 100% !important;
	PADDING-BOTTOM: 0px;
	PADDING-TOP: 0px;
	PADDING-LEFT: 0px;
	PADDING-RIGHT: 0px
}
A[class='banner-img'] {
	HEIGHT: auto !important;
	WIDTH: 100% !important;
	PADDING-BOTTOM: 0px;
	PADDING-TOP: 0px;
	PADDING-LEFT: 0px;
	PADDING-RIGHT: 0px
}
TABLE[class='header-hd'] {
	WIDTH: 0px !important
}
TD[class='header-hd'] {
	WIDTH: 0px !important
}
H1[class='title'] {
	FONT-SIZE: 26px !important
}
TD[class='td-hd'] {
	BACKGROUND: #fff
}
IMG[class='logo-success'] {
	HEIGHT: auto !important;
	WIDTH: 150px !important
}
TD[class='td-logo'] {
	HEIGHT: auto !important;
	WIDTH: 150px !important
}
IMG[class='ico-social'] {
	MARGIN-RIGHT: 15px !important
}
}
</STYLE>
<META name=GENERATOR content='MSHTML 11.00.9600.18212'>
</HEAD>
<BODY 
style='FONT-SIZE: 12px; FONT-FAMILY: Arial, Helvetica, sans-serif; BACKGROUND: #fff'>
<TABLE style='BACKGROUND: #002E5D' cellSpacing=0 cellPadding=0 width='100%' 
border=0 valign='top'>
	<TBODY>
		<TR>
			<TD class=wrap style='BACKGROUND: #002E5D' vAlign=top width='100%' 
    align=center><BR>
				<TABLE class=main_cta cellSpacing=0 cellPadding=0 width=700>
					<TBODY>
						<TR>
							<TD width=10></TD>
							<TD class=main_cta style='PADDING-BOTTOM: 40px; PADDING-TOP: 15px'><TABLE cellSpacing=0 cellPadding=0 width='100%' border=0>
									<TBODY>
										<TR>
											<TD class=td-logo width=210 align=left>
				  <IMG class=logo-success 
                  style='BORDER-LEFT-WIDTH: 0px; FONT-SIZE: 20px; FONT-FAMILY: Arial, sans-serif; BORDER-RIGHT-WIDTH: 0px; BORDER-BOTTOM-WIDTH: 0px; COLOR: #fff; DISPLAY: block; BORDER-TOP-WIDTH: 0px' 
                   src='http://success.inf.br/email/logo.png' width=180 
                  ></TD>
											<TD align=right><IMG 
                  style='BORDER-LEFT-WIDTH: 0px; HEIGHT: 18px; BORDER-RIGHT-WIDTH: 0px; WIDTH: 16px; VERTICAL-ALIGN: middle; BORDER-BOTTOM-WIDTH: 0px; BORDER-TOP-WIDTH: 0px' 
                  alt=icone-fone src='http://success.inf.br/email/ico-fone.png'> <SPAN 
                  style='FONT-SIZE: 11px; FONT-FAMILY: Arial, sans-serif; COLOR: #fff'><STRONG 
                  style='FONT-SIZE: 13px; TEXT-DECORATION: none !important; FONT-FAMILY: Arial, sans-serif'>(38) 3672-4999</STRONG> </SPAN>&nbsp;&nbsp; <SPAN 
                  style='FONT-SIZE: 11px; FONT-FAMILY: Arial, sans-serif; COLOR: #fff'><STRONG 
                  style='FONT-SIZE: 13px; TEXT-DECORATION: none !important; FONT-FAMILY: Arial, sans-serif'>(38) 9.8825-2024</STRONG> </SPAN>
				  							</TD>
										</TR>
									</TBODY>
								</TABLE></TD>
							<TD width=10></TD>
						</TR>
					</TBODY>
				</TABLE>
				<TABLE class=main_cta 
      style='BACKGROUND: #3D9BE9; -webkit-border-top-left-radius: 5px; -webkit-border-top-right-radius: 5px; -moz-border-radius-topleft: 5px; -moz-border-radius-topright: 5px; border-top-left-radius: 5px; border-top-right-radius: 5px' 
      cellSpacing=0 cellPadding=0 width=700>
					<TBODY>
						<TR>
							<TD style='BACKGROUND: #281b4b' width=2></TD>
							<TD width=40></TD>
							<TD class=main_cta><TABLE cellSpacing=0 cellPadding=0 width='100%' border=0>
									<TBODY>
										<TR>
											<TD class=header-lf height=104 align=center><SPAN class=title 
                  style='MARGIN-BOTTOM: 35px; FONT-SIZE: 26px; FONT-FAMILY: Arial, sans-serif; FONT-WEIGHT: bold; COLOR: #fff; TEXT-ALIGN: center; MARGIN-TOP: 38px; DISPLAY: block; LETTER-SPACING: 0px'><? echo $row_rsmala_direta['titulo']; ?></SPAN></TD>
										</TR>
									</TBODY>
								</TABLE></TD>
							<TD width=40></TD>
							<TD style='BACKGROUND: #281b4b' width=2></TD>
						</TR>
					</TBODY>
				</TABLE>
				<TABLE class=main_cta style='BACKGROUND: #ffffff' cellSpacing=0 
      cellPadding=0 width=700>
					<TBODY>
						<TR>
							<TD style='BACKGROUND: #281b4b' width=2></TD>
							<TD width=40></TD>
							<TD class=main_cta 
          style='PADDING-BOTTOM: 10px; PADDING-TOP: 20px'></TD>
							<TD width=40></TD>
							<TD style='BACKGROUND: #281b4b' 
  width=2></TD>
						</TR>
					</TBODY>
				</TABLE></TD>
		</TR>
	</TBODY>
</TABLE>
<TABLE cellSpacing=0 cellPadding=0 width='100%' border=0>
	<TBODY>
		<TR>
			<TD class=wrap style='BACKGROUND: #eee' vAlign=top width='100%' 
align=center><TABLE class=main_cta style='BACKGROUND: #fff' cellSpacing=0 cellPadding=0 
      width=700>
					<TBODY>
						<TR>
							<TD style='BACKGROUND: #e1e1e1' width=2></TD>
							<TD width=40></TD>
							<TD class=main_cta 
          style='PADDING-BOTTOM: 40px; TEXT-ALIGN: center; PADDING-TOP: 15px'><P style='FONT-SIZE: 16px; FONT-FAMILY: Arial, sans-serif; COLOR: #666; PADDING-BOTTOM: 20px; LINE-HEIGHT: 150%'> <p>Paracatu, <? echo data_por_extenso(date('Y-m-d')); ?></p> <BR>
									<BR>
									<BR>
									<BR>
									<SPAN style='FONT-SIZE: 16px'>
									<? echo $row_rsmala_direta['texto']; ?>
									</SPAN></P>
								<BR>
								<BR>
								<P 
            style='FONT-SIZE: 14px; FONT-FAMILY: Arial, Helvetica, sans-serif; COLOR: #666'>Aternciosamente,<BR>
									<BR>
									<STRONG 
            style='FONT-SIZE: 16px; COLOR: #696969; MARGIN-TOP: 5px; DISPLAY: block'>Success Sistemas</STRONG><BR>
									<A 
            style='TEXT-DECORATION: none !important; FONT-WEIGHT: bold; COLOR: #696969' 
            href='http://www.success.inf.br'>www.success.inf.br</A> </P></TD>
							<TD width=40></TD>
							<TD style='BACKGROUND: #e1e1e1' width=2></TD>
						</TR>
					</TBODY>
				</TABLE>
				<TABLE class=main_cta 
      style='BACKGROUND: #ffffff; -webkit-border-bottom-left-radius: 5px; -webkit-border-bottom-right-radius: 5px; -moz-border-radius-bottomleft: 5px; -moz-border-radius-bottomright: 5px; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px' 
      cellSpacing=0 cellPadding=0 width=700>
					<TBODY>
						<TR>
							<TD class=td-hd 
          style='BACKGROUND: #e1e1e1; -webkit-border-bottom-left-radius: 5px; -webkit-border-bottom-right-radius: 5px; -moz-border-radius-bottomleft: 5px; -moz-border-radius-bottomright: 5px; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px' 
          height=2 width='100%'></TD>
						</TR>
					</TBODY>
				</TABLE>
				&nbsp;
	  </TD>
		</TR>
	</TBODY>
</TABLE>
<P class=ampimg 
style='PADDING-BOTTOM: 0px; PADDING-TOP: 0px; PADDING-LEFT: 0px; MARGIN: 0px; DISPLAY: none; LINE-HEIGHT: 0; PADDING-RIGHT: 0px'><IMG alt=''></P>
</BODY>
</HTML>
<? mysql_free_result($rsmala_direta); ?>