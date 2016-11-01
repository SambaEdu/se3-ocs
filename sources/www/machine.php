<?php

/********************************************SPECIFIC SE3********************************************/
// Ajout Se3 pour l'authentification 
require_once("../includes/config.inc.php");
require_once("includes/functions.inc.php");
$login =isauth();

// chemin pour ocsreports
$pathocs=$path_to_wwwse3."/ocsreports";
chdir($pathocs);

$tableau="";
$titre="";

// Si pas se3_is_admin
if ((ldap_get_right("se3_is_admin",$login)=="Y") || (ldap_get_right("computers_is_admin",$login)=="Y") || (ldap_get_right("inventaire_can_read",$login)=="Y") || (ldap_get_right("parc_can_manage",$login)=="Y"))
{

	if (isset($_GET['systemid']))
	{
		$systemid = $_GET['systemid']+0;
	}
	elseif (isset($_POST['systemid']))
	{
		$systemid = $_POST['systemid']+0;
	}
	else
		$systemid=0;

	if ($systemid == 0)
	{
		echo "Num&eacute;ro d'identification de la machine erron&eacute;e";
		die();
	}

	require("../includes/dbconfig.inc.php");

	$conn = mysqli_connect($_SESSION["SERVEUR_SQL"], $_SESSION["COMPTE_BASE"], $_SESSION["PSWD_BASE"], 'ocsweb');

	$query = mysqli_prepare($conn, "SELECT NAME, WORKGROUP, IPADDR, USERID, MEMORY, SWAP, OSNAME, ARCH, OSVERSION, OSCOMMENTS, WINCOMPANY, WINOWNER, WINPRODID, WINPRODKEY, USERAGENT, LASTCOME, LASTDATE FROM hardware WHERE ID = ?");
	mysqli_stmt_bind_param($query,"i", $systemid);
	mysqli_stmt_execute($query);
	mysqli_stmt_bind_result($query,$res_name,$res_workgroup,$res_ipaddr,$res_userid,$res_memory,$res_swap,$res_osname,$res_arch,$res_osversion,$res_oscomments,$res_wincompany,$res_winowner,$res_winprodid,$res_winprodkey,$res_useragent,$res_lastcome,$res_lastdate);
	mysqli_stmt_fetch($query);
	mysqli_stmt_close($query);
	
	$query2 = mysqli_prepare($conn, "SELECT TYPE, SPEED, CORES, LOGICAL_CPUS, CURRENT_ADDRESS_WIDTH FROM cpus WHERE HARDWARE_ID = ?");
	mysqli_stmt_bind_param($query2,"i", $systemid);
	mysqli_stmt_execute($query2);
	mysqli_stmt_bind_result($query2,$res_cputype,$res_cpuspeed,$res_cpucores,$res_cpulogical,$res_cpuaddress);
	mysqli_stmt_fetch($query2);
	mysqli_stmt_close($query2);
	
	$query3 = mysqli_prepare($conn, "SELECT SSN, SMODEL, SMANUFACTURER FROM bios WHERE HARDWARE_ID = ?");
	mysqli_stmt_bind_param($query3,"i", $systemid);
	mysqli_stmt_execute($query3);
	mysqli_stmt_bind_result($query3,$res_ssn,$res_smodel,$res_smarque);
	mysqli_stmt_fetch($query3);
	mysqli_stmt_close($query3);
	
	$query4 = mysqli_prepare($conn, "SELECT MACADDR FROM networks WHERE HARDWARE_ID = ? AND IPADDRESS = '".$res_ipaddr."'");
	mysqli_stmt_bind_param($query4,"i", $systemid);
	mysqli_stmt_execute($query4);
	mysqli_stmt_bind_result($query4,$res_macaddr);
	mysqli_stmt_fetch($query4);
	mysqli_stmt_close($query4);
	
	mysqli_close($conn);
	
	$titre .= "<title>Rapport d'inventaire du poste $res_name</title>";
	
	$tableau .= "<table width=100%>";
	$tableau .= "<tr><th colspan='2'><center>Rapport d'inventaire du poste $res_name</center></th></tr>";
	$tableau .= "<tr><td colspan='2'>&nbsp;</td></tr>";
	$tableau .= "<tr><th colspan='2'>Mat&eacute;riel</th></tr>";
	$tableau .= "<tr><td>Mod&egrave;le</td><td>$res_smarque $res_smodel</td></tr>";
	$tableau .= "<tr><td>Num&eacute;ro de s&eacute;rie</td><td>$res_ssn</td></tr>";
	$tableau .= "<tr><td>Processeur</td><td>$res_cputype</td></tr>";
	$tableau .= "<tr><td>Fr&eacute;quence du processeur</td><td>$res_cpuspeed MHz</td></tr>";
	$tableau .= "<tr><td>Nombre de coeurs physiques (logiques)</td><td>$res_cpucores ($res_cpulogical)</td></tr>";
	$tableau .= "<tr><td>M&eacute;moire Vive</td><td>$res_memory Mo</td></tr>";
	$tableau .= "<tr><td>Espace du Swap</td><td>$res_swap Mo</td></tr>";
	$tableau .= "<tr><td colspan='2'>&nbsp;</td></tr>";
	$tableau .= "<tr><th colspan='2'>Syst&egrave;me d'exploitation</th></tr>";
	$tableau .= "<tr><td>Nom du syst&egrave;me</td><td>$res_osname</td></tr>";
	$tableau .= "<tr><td>Service Pack</td><td>$res_oscomments</td></tr>";
	$tableau .= "<tr><td>Version du syst&egrave;me</td><td>$res_osversion</td></tr>";
	$tableau .= "<tr><td>Architecture</td><td>$res_cpuaddress bits</td></tr>";
	$tableau .= "<tr><td>Utilisateur Windows</td><td>$res_winowner</td></tr>";
	$tableau .= "<tr><td>Licence Windows</td><td>$res_winprodid</td></tr>";
	$tableau .= "<tr><td>Cl&eacute; Windows</td><td>$res_winprodkey</td></tr>";
	$tableau .= "<tr><td colspan='2'>&nbsp;</td></tr>";
	$tableau .= "<tr><th colspan='2'>R&eacute;seau</th></tr>";
	$tableau .= "<tr><td>Adresse IP</td><td>$res_ipaddr</td></tr>";
	$tableau .= "<tr><td>Adresse MAC</td><td>$res_macaddr</td></tr>";
	$tableau .= "<tr><td>Domaine</td><td>$res_workgroup</td></tr>";
	$tableau .= "<tr><td>Utilisateur</td><td>$res_userid</td></tr>";
	$tableau .= "<tr><td colspan='2'>&nbsp;</td></tr>";
	$tableau .= "<tr><th colspan='2'>Agent</th></tr>";
	$tableau .= "<tr><td>Type agent</td><td>$res_useragent</td></tr>";
	$tableau .= "<tr><td>Dernier inventaire</td><td>$res_lastdate</td></tr>";
	$tableau .= "<tr><td>Dernier contact</td><td>$res_lastcome</td></tr>";
	$tableau .= "<tr><td colspan='2'>&nbsp;</td></tr>";
	$tableau .= "<tr><td colspan='2'><center><a onClick='print()'>Imprimer la page</a></center></td></tr>";
	$tableau .= "</table>";


}
else
{
	$titre .= "<title>Connexion interdite!</title>";
	$tableau .= "Connexion interdite!";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
	<STYLE type="text/css">
	body{
		background: url(/elements/images/fond_SE3.png) ghostwhite bottom right no-repeat fixed;
	}
	</STYLE>

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<LINK REL='StyleSheet' TYPE='text/css' HREF='css/bootstrap.min.css'>
	<LINK REL='StyleSheet' TYPE='text/css' HREF='css/bootstrap-theme.min.css'>
	<LINK REL='StyleSheet' TYPE='text/css' HREF='css/bootstrap-custom.css'>
	<LINK REL='StyleSheet' TYPE='text/css' HREF='css/dataTables-custom.css'>
	<LINK REL='StyleSheet' TYPE='text/css' HREF='css/dataTables.bootstrap.css'>
	<LINK REL='StyleSheet' TYPE='text/css' HREF='css/ocsreports.css'>
	<LINK REL='StyleSheet' TYPE='text/css' HREF='css/header.css'>
	<LINK REL='StyleSheet' TYPE='text/css' HREF='css/computer_details.css'>
	<LINK REL='StyleSheet' TYPE='text/css' HREF='css/forms.css'>
<?php
	echo $titre;
?>
</head>
<body>
<?php
	echo $tableau;
?>
</body>
</html>