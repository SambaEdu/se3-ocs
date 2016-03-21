<?php
// UPDATED FOR SE3 BY LAURENT JOLY 19-03-2016

require("../includes/dbconfig.inc.php");
define("DB_NAME", "ocsweb");
define("SERVER_READ",$_SESSION["SERVEUR_SQL"]);
define("SERVER_WRITE",$_SESSION["SERVEUR_SQL"]);
define("COMPTE_BASE",$_SESSION["COMPTE_BASE"]);
define("PSWD_BASE",$_SESSION["PSWD_BASE"]);

?>