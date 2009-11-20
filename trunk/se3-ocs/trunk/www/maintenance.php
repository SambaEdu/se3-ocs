<?
/* $Id$ */


// Sandrine Dangreville matice creteil 2003
// Philippe Chadefaux 
/* =============================================
   Projet SE3 : Inventaire de machines
   inventaire/maintenance.php
   Matice acadï¿½ie de Creteil
   Distribuï¿½selon les termes de la licence GPL
   ============================================= */


include "entete.inc.php";
include "ldap.inc.php";
include "ihm.inc.php";
include "fonc_inventaire.php";
include "dbconfig.inc.php";


// Verifie les droits
if ((is_admin("computers_is_admin",$login)=="Y") or (is_admin("parc_can_view",$login)=="Y") or (is_admin("parc_can_manage",$login)=="Y") or (is_admin("inventaire_can_read",$login)=="Y") or (is_admin("maintenance_can_write",$login)=="Y")) {

	//aide
	$_SESSION["pageaide"]="L%27inventaire#Maintenance";
} else {
	echo "Vous n'avez pas les droits pour accèder à cette page";
	exit;
}	

//*****************cas des parcs délégués***********************************/
if ((is_admin("computers_is_admin",$login)=="N") and ((is_admin("parc_can_view",$login)=="Y") or (is_admin("parc_can_manage",$login)=="Y"))) { 
	echo "<h3>".gettext("Votre délégation a été prise en compte pour l'affichage de cette page.")."</h3>"; $acces_restreint=1;

	$list_delegate=list_parc_delegate($login);
	echo "<ul>";
	foreach ($list_delegate as $info_parc_delegate) {
		echo "<li>$info_parc_delegate</li>";
	}
	echo "</ul>";
}


/************************* Declaration des variables ***********************************/

foreach ($_GET as $cle=>$val) {
        $$cle = $val;
}
if (!$mpenc){ $mpenc="all";}
if (!$interval) { $interval='14';}
$datedujour=date("Y-m-d");
// if (isset($action)) { $action="affiche";}

$dbnameinvent="ocsweb";


$authlink_invent=@mysql_connect($_SESSION["SERVEUR_SQL"],$_SESSION["COMPTE_BASE"],$_SESSION["PSWD_BASE"]);
@mysql_select_db($dbnameinvent) or die("Impossible de se connecter &#224; la base $dbnameinvent.");


/************************* Ajout maintenance *****************************************************/
/* if  (($mpenc != "all") and ($action!="moi") and ($action!="all") and ($action!="change")  and ($action!="ajout")) {
	$jour=date("Y-m-d G:i:s");  
     	if ($mpenc != "all") {
        	echo "<P><h1>".gettext("Maintenance de la machine")." $mpenc</h1></P>\n";
    

       		// Si il est admin il peut voir le reste
       		if ((is_admin("computers_is_admin",$login)=="Y") or (is_admin("parc_can_manage",$login)=="Y")or (is_admin("parc_can_view",$login)=="Y") or (is_admin("inventaire_can_read",$login)=="Y")) {
       }
    }
    
}   
*/    

/*****************************************************************************************/
   // zone de saisie de la demande de maintenance
  if ($action == "ajout") {
     if($mpenc=="all") { //si on a bien le nom d'une machine
	$action="";
	$erreur=gettext("Vous devez sélectionner une machine");
     } else {	
     
   	echo "<h1>".gettext("Demande de maintenance : $mpenc")."</H1>";
  	 // On vï¿½ifie si une demande de maintenance n'est pas dï¿½ï¿½ouverte pour cette machine
    	$query="select * from repairs where NAME='$mpenc' AND STATUT='0' LIMIT 20";
    	$resultat=mysql_query($query,$authlink_invent);
    	$ligne=mysql_num_rows($resultat);
    	if ($ligne > "0") {
            	while ($row = mysql_fetch_row($resultat)) {
                	if ($row[11] == "2") {
            			echo "<CENTER>";
            			echo gettext("Une demande pour cette machine est déjà ouverte par")." $row[8]<BR>";
            			echo "$row[13] ".gettext(" l'a mise en ATTENTE le")." $row[5]";
            			if ($row[6] != "") {
                			echo gettext("Motif :")." $row[6]";
            			}
        		} elseif ($row[11] == "3") {
            			echo "<CENTER>";
            			echo gettext("Une demande pour cette machine est déjà ouverte par")." $row[8]<BR>";
            			echo "$row[13] ".gettext("l'a indiqu&#233; comme non r&#233;parable le")." $row[5]";
            			if ($row[6] != "") {
                			echo gettext("Motif :")." $row[6]";
            			}
        		}
    
    
    			if ($row[11] == "0")  {
            			echo "<CENTER><BR>";
            			echo "<IMG SRC=\"../elements/images/critical.png\" ALT=\"Alerte\">";
            			echo gettext(" Une demande pour cette machine est d&#233;j&#224; ouverte le")." $row[3] ".gettext("par")." $row[8]<BR>";
            			echo gettext("avec le motif :")." $row[4]<BR>";
    			}
   			 //on determine quel demande de maintenance correspond a la personne qui consulte la page et on recopie la ligne correspondante
    			//cette ligne sera modifiable seulement si le login correspond, sinon on pourra poser une nouvelle alerte
    			if ($login == "$row[8]") {
				$test_login_ok=$login;
         			$row_login=array();
         			for ($i=0;$i<count($row);$i++) {  array_push($row_login,$row[$i]);}
        		}
        
    		}
    	}
    	
	if ($login == $test_login_ok) {
       		echo "<FORM action=maintenance.php method=\"GET\">";
       		echo "<BR><BR><CENTER><TABLE width=80% border=1>";
       		if ($ligne > "0") {
        		echo "<TR><TD colspan=\"2\" class='menuheader' align=center height=\"30\"> ".aide('Vous ne pouvez pas effectuer plus d\&#039;une demande par machine.Modifier votre derni&#232;re demande','Modifier')." votre demande de maintenance pour $mpenc</TD></TR>";
       		} else {
        		echo "<TR><TD colspan=\"2\" class='menuheader' align=center height=\"30\">".aide('Une demande a &#233;t&#233; faite par une autre personne. Un descriptif court vous indique ci-dessus la nature de sa demande. Ne faites pas de demandes inutiles si vous avez le m&#234;me probl&#232;me.',"Ajouter")." une demande de maintenance</TD></TR>";
       		}
       		if ($ligne > "0") { echo "<TR><TD>".gettext("Date de la premi&#232;re saisie :")." </TD><TD>$row_login[3]&nbsp;</TD></TR>"; }
       
       		echo "<TR><TD>".gettext("Description (Br&#232;ve)")."</TD><TD>\n";
		echo "<TEXTAREA NAME=\"REQDESC\" cols=\"50\" rows=\"2\">$row_login[4]";
       		echo "</TEXTAREA></TD></TR>\n";
       		echo "<TR><TD>".gettext("Priorit&#233;:")." </TD>\n";
		echo "<TD><SELECT NAME=\"PRIORITE\"><OPTION value=\"0\"";
       		if ($row_login[12] == "0") { echo"selected"; }
       		echo">".gettext("Normale")."</OPTION><OPTION value=\"1\"";
       		if ($row_login[12] == "1") { echo"selected"; }
       		echo ">".gettext("Urgent")."</OPTION><OPTION value=\"3\"";
       		if ($row_login[12] == "2") { echo"selected"; }
       		echo ">".gettext("Très urgent")."</OPTION><OPTION value=\"2\"";
		if ($row_login[12] == "3") { echo"selected"; }
		echo">".gettext("Annulé")."</OPTION>";
		echo "</SELECT>";
		echo "</TD></TR>\n";
       		echo "<TR><TD>".gettext("Commentaire (Long si n&#233;cessaire)")."</TD>";
		echo "<TD><TEXTAREA NAME=\"COMMENTS\" cols=\"50\" rows=\"5\">$row_login[10]";
       		echo "</TEXTAREA></TD></TR>\n";
       		echo "</TABLE>";
       		echo "<INPUT TYPE=\"hidden\" NAME=\"action\" VALUE=\"change\">";
       		echo "<INPUT TYPE=\"hidden\" NAME=\"mpenc\" VALUE=\"$mpenc\">";
       		if ($ligne > "0") {
        		echo "<INPUT TYPE=\"hidden\" NAME=\"ID\" VALUE=\"$row_login[0]\">";
       		}    
       		echo "<INPUT TYPE=\"submit\" VALUE=\"Valider\">";
       		echo "</FORM>\n";
   	 } else {
    		//les logins ne correspondent pas , on laisse la possibilit&#233; d'ajouter une demande de maintenance
       		echo "<FORM action=maintenance.php method=\"GET\">";
       		echo "<BR><BR><CENTER><TABLE width=80% border=1>";
       		echo "<TR><TD colspan=\"2\" class='menuheader' align=center height=\"30\">".aide('Une demande a &#233;t&#233; faite par une autre personne. Un descriptif court vous indique ci-dessus la nature de sa demande. Ne faites pas de demandes inutiles si vous avez le m&#234;me probl&#232;me.',"Ajouter")." une demande de maintenance</TD></TR>\n";
       		echo "<TR><TD>".gettext("Description (Br&#232;ve)")."</TD>\n";
		echo "<TD><TEXTAREA NAME=\"REQDESC\" cols=\"50\" rows=\"2\">";
       		echo "</TEXTAREA></TD></TR>\n";
       		echo "<TR><TD>".gettext("Priorit&#233;:")." </TD><TD><SELECT NAME=\"PRIORITE\"><OPTION value=\"0\"";
       		echo ">".gettext("Normale")."</OPTION><OPTION value=\"1\"";
       		echo ">".gettext("Tr&#233;s urgent")."</OPTION><OPTION value=\"2\"";
       		echo ">".gettext("Urgent")."</OPTION></SELECT></TD></TR>";
       		echo "<TR><TD>".gettext("Commentaire (Long si n&#233;cessaire)")."</TD>\n";
		echo "<TD><TEXTAREA NAME=\"COMMENTS\" cols=\"50\" rows=\"5\">";
       		echo "</TEXTAREA></TD></TR>\n";
       		echo "</TABLE>\n";
       		echo "<INPUT TYPE=\"hidden\" NAME=\"action\" VALUE=\"change\">";
       		echo "<INPUT TYPE=\"hidden\" NAME=\"mpenc\" VALUE=\"$mpenc\">";
       		echo "<INPUT TYPE=\"submit\" VALUE=\"".gettext("Valider")."\">";
       		echo "</FORM>\n";
    	}
   include ("pdp.inc.php");
   exit;
  }
}

/****************************** Affiche le rï¿½ultat ********************************************/
// Tout voir et permet de définir si la machine est réparé
    
if ($action == "detail") {

	$query="select * from repairs where NAME='$mpenc' AND ID='$ID' LIMIT 1";
    	$resultat=mysql_query($query,$authlink_invent);
    	$ligne=mysql_num_rows($resultat);
        $row = mysql_fetch_array($resultat);

	echo "<H1>".gettext("Maintenance de la machine")." $mpenc</H1>";
   	echo "<CENTER><TABLE width=80% border=1>";
    	echo "<TR><TD colspan=\"2\" class='menuheader' align=center height=\"30\">".gettext("Fiche de maintenance")."</TD></TR>\n";
    	echo "<TR><TD>".gettext("Description (Courte)")."</TD><TD>$row[4]</TD></TR>\n";
    	echo "<TR><TD>".gettext("Date de la demande :")." </TD><TD>$row[3]</TD></TR>\n";
    	echo "<TR><TD>".gettext("Auteur :")."</TD><TD> $row[8]</TD></TR>\n";
    	echo "<TR><TD>".gettext("Priorit&#233;:")."</TD>\n";
     	if ($row[11] == "1") {
                $ETAT=gettext("R&#233;par&#233;");
                $COULEUR="#E0EEEE";
        } elseif ($row[11] == "2") {
                $ETAT=("En attente");
                $COULEUR="#00FF66";
        } elseif ($row[11] == "3") {
                $ETAT=gettext("Non r&#233;parable");
                $COULEUR="#E0EEEE";
        } else {
                if ($row[12] == "2") {
                    $COULEUR="#FF7D40";
                    $ETAT=gettext("Urgent");
                } elseif ($row[12] == "1") {
                    $COULEUR="#EE2C2C";
                    $ETAT=gettext("Tr&#233;s urgent");
                } elseif ($row[12] == "0") {
                    $COULEUR="#FFD700";
                    $ETAT=gettext("Normal");
                }
         }
         $FOND="#E0EEEE";

         echo "<TD bgcolor=\"$COULEUR\">$ETAT</TD></TR>";
        if ($row[10]!= "") {
        	echo "<TR><TD>".gettext("Commentaire (Long si n&#233;cessaire)")."</TD><TD>$row[10]</TD></TR>\n";
    	}
    	echo "<FORM action=maintenance.php method=\"GET\">";

    	if   ((is_admin("computers_is_admin",$login)=="Y") or (($acces_restreint) and in_parc_delegate($login,$mpenc))) {
    		echo "<TR><TD colspan=\"2\" height=\"30\" align=center class='menuheader'>".gettext("D&#233;pannage")."</TD></TR>\n";
    		// On vï¿½ifie si cette machine est dans l'inventaire
    		$query="select * from hardware where NAME LIKE '$mpenc%' LIMIT 1";
    		$resultat=mysql_query($query,$authlink_invent);
    		$ligne=mysql_num_rows($resultat);
    		if ($ligne == "0") {
       		echo "<TR><TD align=center><IMG SRC=\"../elements/images/critical.png\" ALT=\"Warning\"></TD><TD>".gettext("Attention : Cette machine n'est pas dans l'inventaire")."</TD></TR>\n";
    	}   
    	echo "<TR><TD>".gettext("Etat :")." </TD><TD><SELECT NAME=\"STATUT\"><OPTION value=\"0\">".gettext("Aucune")."</OPTION><OPTION value=\"1\"";
    	if($row[11]=="1") { echo "selected";}
    	echo ">R&#233;gl&#233;</OPTION><OPTION value=\"2\"";
    	if($row[11]=="2") { echo "selected";}
    	echo">".gettext("En attente")."</OPTION><OPTION value=\"3\"";
    	if($row[11]=="3") { echo "selected";}
    	echo">".gettext("Non r&#233;parable")."</OPTION></SELECT></TD></TR>\n";
    	if ($row[11] != "0") {
        	echo "<TR><TD>".gettext("Intervenant :")."</TD><TD>$row[13]</TD></TR>\n";
        	echo "<TR><TD>".gettext("Date d'intervention :")."</TD><TD>$row[5]</TD></TR>\n";
    	}   
    	echo "<TR><TD>".gettext("Description r&#233;paration")."</TD><TD><TEXTAREA NAME=\"ACTIONDESC\" cols=\"50\" rows=\"2\">";
    	echo "$row[6]</TEXTAREA></TD></TR>\n";
    	echo "</TABLE>\n";

    	echo "<INPUT TYPE=\"hidden\" NAME=\"ID\" VALUE=\"$ID\">";
    	echo "<INPUT TYPE=\"hidden\" NAME=\"action\" VALUE=\"del\">";
    	echo "<INPUT TYPE=\"hidden\" NAME=\"mpenc\" VALUE=\"$mpenc\">";
    	echo "<INPUT TYPE=\"submit\" VALUE=\"Valider\">";
    	echo "</FORM>\n";
     }
include ("pdp.inc.php");    
exit;
}


/********************* valide la reparation ******************************/
if ($action == "del") {
	if  ((is_admin("computers_is_admin",$login)=="N") and (is_admin("parc_can_manage",$login)=="N") and (is_admin("parc_can_view",$login)=="N") and (is_admin("inventaire_can_read",$login)=="N")) { echo gettext("Vous n'avez pas le droit d'effectuer cette action ( droits insuffisants )"); exit;}
	if (($acces_restreint) and (!in_parc_delegate($login,$mpenc)))  { echo gettext("Vous n'avez pas le droit d'effectuer cette action ( pas de droits sur les parcs de cette machine)");  exit;  }
   	if ($STATUT != "0") {
     		// On cloture la demande

    		$query = "UPDATE `repairs` SET `ACTIONDATE`='$jour', `ACTIONDESC`='$ACTIONDESC', `STATUT`='$STATUT', `ADMIN`='$login' WHERE `ID`='$ID';";
  		//  echo $query;
    		// On logs l'action
    		$resultat=mysql_query($query,$authlink_invent);
    		if ($STATUT == "1") { $OK="MaintD"; }
    		if ($STATUT == "2") { $OK="MaintR"; }
    		if ($STATUT == "3") { $OK="MaintE"; }
    		// On expï¿½ie un mail si cela est prï¿½u
    		require_once ("config.inc.php");

        	$auth = @mysql_connect($dbhost,$dbuser,$dbpass);
        	@mysql_select_db($dbname,$auth) or die("Impossible de se connecter &#224; la base $dbname.");
    		$query="select  MAIL, ACTIVE from alertes where VARIABLE='close_maintenance' LIMIT 1";
    		$resultat=mysql_query($query,$auth);
        	$row = mysql_fetch_array($resultat);
    		if ($row[1] == "1") {
        		alerte_mail($row[0],"[SE3] Intervention éffectuée","Intervention éffectuée par $login (statut : Fermeture de la demande ACTION : $ACTIONDESC)");
        
   		 }
   	} 
$action="affiche";
}

/************************ Valide le demande de maintenance ****************************************************/
if ($action == "change") {
	$query="select * from repairs where NAME='$mpenc' AND STATUT='0' AND ACCOUNT='$login' ";
    	$resultat=mysql_query($query,$authlink_invent);
 	$jour=date("Y-m-d G:i:s");  
        
    	// On teste il y a une demande de maintenance ouverte pour cette machine. On n'en ouvre qu'une par machine
    	//pas d'accord, on doit pouvoir en ouvrir plusieurs pas machines, mais en ayant connaissance ces autres pour ne pas mettre de doublons
    	$ligne=mysql_num_rows($resultat);
    	if($ligne == "0") {
            
        	// On ajoute dans la table repairs
        	$query2 = "INSERT INTO repairs (ID,DEVICEID,NAME,REQDATE,REQDESC,ACTIONDATE,ACTIONDESC,WARANTY,ACCOUNT,PRICE,COMMENTS,STATUT,PRIORITE,ADMIN) VALUES ('NULL','$mpenc','$mpenc','$jour','$REQDESC','$ACTIONDATE','$ACTIONDESC','$WARANTY','$login','$PRICE','$COMMENTS','$STATUT','$PRIORITE','')"; 
        	$result2 = mysql_query($query2,$authlink_invent);
        
        	// Expï¿½ie un mail aux membres de computers_is_admin
        	require_once ("config.inc.php");
                 
        	$auth = @mysql_connect($dbhost,$dbuser,$dbpass);
        	@mysql_select_db($dbname,$auth) or die("Impossible de se connecter &#224; la base $dbname.");
        	$query="select  MAIL, ACTIVE from alertes where VARIABLE='new_maintenance' LIMIT 1";
        	$resultat=mysql_query($query,$auth);
            	$row = mysql_fetch_array($resultat);
        	if ($row[1] == "1") {
            		alerte_mail($row[0],"[SE3] Ouverture de maintenance","Demande de maintenance sur la machine $mpenc par $login avec la priorité $PRIORITE au motif $REQDESC");
        	}
    	} else  {
        	// une demande de maintenance est deja ouverte, on la modifie
		if($PRIORITE=="3") {
			$STATUT="1";
    			$query = "UPDATE `repairs` SET `ACTIONDATE`='$jour', `ACTIONDESC`='$ACTIONDESC', `STATUT`='$STATUT', `ADMIN`='$login', COMMENTS='$COMMENTS', PRIORITE='$PRIORITE' WHERE `ID`='$ID';";
		} else {	
        		$query = "UPDATE repairs SET REQDESC='$REQDESC', COMMENTS='$COMMENTS', PRIORITE='$PRIORITE' WHERE ID=$ID";
		}
		
        	$resultat=mysql_query($query,$authlink_invent);

        	// Expï¿½ie un mail aux membres de computers_is_admin
        	include "config.inc.php";
        	$auth = @mysql_connect($dbhost,$dbuser,$dbpass);
        	@mysql_select_db($dbname,$auth) or die("Impossible de se connecter &#224; la base $dbname.");
                 
        	$query="select  MAIL, ACTIVE from alertes where VARIABLE='change_maintenance' LIMIT 1";
		$resultat=mysql_query($query,$auth);
        	if ($resultat) {    
			$row = mysql_fetch_array($resultat);
        		if ($row[1] == "1") {
            			alerte_mail($row[0],"[SE3] Modification de maintenance","Modification de la demande de maintenance sur la machine $mpenc par $login avec la priorité $PRIORITE au motif $REQDESC");
        		}
		}
	}
    
    	$action = "moi";

}

/******************************* Affiche la liste de toutes les réparations ************************/
// on affiche
//
if (($action=="change") or ($action=="all") or ($action=="moi") or ($action=="affiche")) {

	if ($deb == "") { $deb = "0"; }
	if ($fin == "") { $fin = "50"; }
	if ($action == "all") {
        	echo "<H1>".gettext("Toutes les demandes de maintenance")."</H1>";
		
    		$query="select * from repairs where STATUT='0' LIMIT 50";
    		$resultat=mysql_query($query,$authlink_invent);
    		$ligne=mysql_num_rows($resultat);
    		if ($ligne == "0") {	
			echo gettext("Pas de machine en attente");
			exit;
		}	
        	if ($acces_restreint) { 
			echo "<H3>";
			echo gettext("Demandes pour vos parcs délégués.");
        		echo "</H3>\n";
		}	

    		if ($etat != "") {
        		$query="select * from repairs WHERE STATUT='$etat' ORDER BY REQDATE  desc LIMIT $deb,$fin ";
    		} else { 
        		$query="select * from repairs ORDER BY REQDATE  desc LIMIT $deb,$fin";
    		}
	} else {
		if ($action == "moi") {
    			echo "<H1>".gettext("Mes demandes de maintenance")."</H1>\n";
    			// permet de voir uniquement les demandes de login
    			$query="select * from repairs where ACCOUNT='$login' $ajout_machine ORDER BY REQDATE  desc LIMIT $deb,$fin";
		} else {

        		echo "<H1>".gettext("Demandes de maintenance de $mpenc")."</H1>";
    			$query="select * from repairs where NAME='$mpenc' ORDER BY REQDATE  desc LIMIT $deb,$fin";
		}
	}

	$result=mysql_query($query,$authlink_invent);
	$ligne=mysql_num_rows($result);
	if($ligne == "0") {
		// Suppression d'une demande de maintenance, cela revient a la fermer
		echo "<BR><BR><CENTER>";
        	echo "<TABLE><TR><TD>".gettext("Aucune demande de maintenance pour cette machine")."</TD></TR>";
        	if ($mpenc != "all") {
        		echo "<TR><TD align=center><A HREF=maintenance.php?mpenc=$mpenc&action=ajout>".gettext("Ajouter une demande de maintenance")."</A></TR></TD></TABLE>\n";
		}
	} else {
        	// Limite l'affichage a 50
       	 	// traite si on en a plus que la taille 50
        	if (($ligne >= "$fin") || ($deb >= "$fin")) {
                	echo "<BR><BR><TABLE width=80%><TR><TD align=left>";
                	if ($deb >= "$fin") {
        	        	$d = $deb - $fin;
                		echo "<CENTER><A HREF=maintenance.php?action=all&mpenc=$mpenc&deb=$d><IMG Style=\"border: 0px solid ;\" SRC=\"../elements/images/left.gif\" ALT=\"Pr&#233;c&#233;dent\"></A>";
        		}

        		$deb = $deb + $fin;
              		echo "</TD><TD align=right>";
                	if ($ligne >= "$fin") { echo "<A HREF=\"maintenance.php?action=all&mpenc=$mpenc&deb=$deb\"><IMG Style=\"border: 0px solid ;\" SRC=\"../elements/images/right.gif\" ALT=\"Suivant\"></A>"; }
                	echo "</TD></TR></TABLE></CENTER>";
        	}
                

        	echo "<TABLE border=\"1\" align=center width=\"80%\">";
        	echo "<TR class='menuheader' align=center>";
        	//affichage de l'id inutile <TD height=\"30\">ID</TD>";
        	echo "<TD height=\"30\">Nom</TD>";
        	echo "<TD height=\"30\"> ".aide('Vous pouvez utiliser des priorites pour vos demandes de maintenance:<br>Urgent<br>Tres urgent<br>Normal','Priorit&#233;')." </TD><TD height=\"30\">Auteur</TD><TD height=\"30\">Date de la demande</TD><TD height=\"30\">Description</TD>";

		if ((is_admin("computers_is_admin",$login)=="Y") or (is_admin("parc_can_view",$login)=="Y") or (is_admin("inventaire_can_read",$login)=="Y") or (is_admin("parc_can_manage",$login)=="Y"))  {
			echo "<TD colspan=\"2\" height=\"30\">";
           		if (($etat != "") and ($action == "all")) {
            			echo "<A HREF=maintenance.php?mpenc=all&action=all>".aide('Voir toutes les demandes de maintenance.',"<IMG  style=\"border: 0px solid ;\" SRC=\"../elements/images/logrotate.png\" ALT=\"All\">")."</A>";
           		}
        		echo "</TD></TR>\n";
        	}
        
        	//debut de la boucle d'affichage
        	while($row = mysql_fetch_array($result)) {
        		if (($action <> "moi") and ($acces_restreint)) { if (!in_parc_delegate($login,$row[2])) { /*echo "vous ne pouvez affichez la machine $row[2]"; */ continue;}}
            		if ($row[11] == "1") {
                		$ETAT=gettext("R&#233;par&#233;");
                		$COULEUR="#E0EEEE";
            		} elseif ($row[11] == "2") {
                		$ETAT=gettext("En attente");
                		$COULEUR="#00FF66";
            		} elseif ($row[11] == "3") {
                		$ETAT=gettext("Non r&#233;parable");
                		$COULEUR="#E0EEEE";
            		} else {
                		if ($row[12] == "2") {
                    			$COULEUR="#FF7D40";
                    			$ETAT=gettext("Urgent");
                		} elseif ($row[12] == "1") {
                    			$COULEUR="#EE2C2C";
                    			$ETAT=gettext("Tr&#233;s urgent");
                		} elseif ($row[12] == "3") {
                    			$COULEUR="#EE2CCC";
                    			$ETAT=gettext("Annulé");
                		} elseif ($row[12] == "0") {
                    			$COULEUR="#FFD700";
                    			$ETAT=gettext("Normal");
                		}
            		}
            		$FOND="#E0EEEE";
            		echo "<TR align=center bgcolor=\"$FOND\">";
            		//affichage de l'id inutile <TD>$row[0]</TD>";
                	echo "<TD><A HREF=maintenance.php?action=affiche&mpenc=$row[2]>$row[2]</A></TD>";
            		echo "<TD bgcolor=\"$COULEUR\">$ETAT</TD><TD>$row[8]</TD><TD>$row[3]</TD><TD>$row[4]</TD>";
            
            		// Seul le computers is admin peut tout voir
            		if ((is_admin("computers_is_admin",$login)=="Y") or (is_admin("parc_can_view",$login)=="Y") or (is_admin("inventaire_can_read",$login)=="Y") or (is_admin("parc_can_manage",$login)=="Y")) {
	        		echo "<TD><A HREF=maintenance.php?mpenc=$row[2]&action=detail&ID=$row[0]>".aide('Affiche les d&#233;tails sur cette demande.',"<IMG  style=\"border: 0px solid ;\" SRC=\"../elements/images/detail.gif\" ALT=\"D&#233;tails\">")."</A></TD>";
                   		if ($row[11] == "0") {
                        		echo"<TD><A HREF=maintenance.php?mpenc=$mpenc&action=all&etat=$row[11]>".aide('Pour voir uniquement les demandes non trait&#233;es',"<IMG  style=\"border: 0px solid ;\" SRC=\"../elements/images/ack.gif\" ALT=\"Demande d'intervention\">")."</A>";
                        		echo"</TD></TR>\n";
               			} elseif($row[11] == "2") {
                        		echo"<TD><A HREF=maintenance.php?mpenc=$mpenc&action=all&etat=$row[11]>".aide('Pour voir uniquement les demandes mises en attente',"<IMG  style=\"border: 0px solid ;\" SRC=\"../elements/images/delay.gif\" ALT=\"En attente\">")."</A>";
                        		echo"</TD></TR>\n";
               			} elseif($row[11] == "1") {
                        		echo"<TD><A HREF=maintenance.php?mpenc=$mpenc&action=all&etat=$row[11]>".aide('Pour voir uniquement les machines r&#233;par&#233;es',"<IMG  style=\"border: 0px solid ;\" SRC=\"../elements/images/noack.gif\" ALT=\"Machine r&#233;par&#233;e\">")."</A>";
                        		echo"</TD></TR>\n";
               			} elseif($row[11] == "3") {
                        		echo"<TD><A HREF=maintenance.php?mpenc=$mpenc&action=all&etat=$row[11]>".aide('Pour voir uniquement les machines non r&#233;parables',"<IMG  style=\"border: 0px solid ;\" SRC=\"../elements/images/stop.gif\" ALT=\"Machine non &#233;parable\">")."</A>";
                        		echo"</TD></TR>\n";
               			}
            		}
            
        	}   
    		echo "</TABLE>\n";
		include ("pdp.inc.php");    
		exit;
	}
}

/************** Page de recherche pour la maintenance **************************************/ 

//if ($action=="choix") {
    
	// pas de machine connue
    	echo "<H1>".gettext("Demande d'intervention")." </H1>";
	if($erreur) { echo "<font color=\"orange\"><I>$erreur</I></font><BR><BR>\n"; }
	
    	$ipaddr = "$REMOTE_ADDR";   
   	$auth = @mysql_connect($dbhost,$dbuser,$dbpass);
   	@mysql_select_db($dbname) or die("Impossible de se connecter &#224; la base $dbname.");
    	$query="select netbios_name from connexions where ip_address='$ipaddr' order by id desc limit 1";
    	$result=mysql_query($query,$auth);

    	if($ligne != "0") {
        	$row = mysql_fetch_array($result);
    	}
    	// Filtrage des noms
    	echo "<H3>".gettext("S&#233;lectionner une machine")."<BR></H3>";
    	echo "<FORM action=\"maintenance.php\" method=\"GET\">\n";
    	echo "<P>".aide('Tri les machines en fonction d\&#039;une partie du nom de la machine',"Lister")." les noms contenant: ";
    	echo "<INPUT TYPE=\"text\" NAME=\"filtrecomp\"\n VALUE=\"$filtrecomp\" SIZE=\"8\">";
        echo "<input type=\"submit\" value=\"Chercher\">\n";
        echo "</FORM>\n";
        // Lecture des membres du parc
        $mp=gof_members($parc,"parcs",1);
        // Creation d'un tableau des  machines
        if ($filtrecomp == '') $filtrel = '*';
        else $filtrel = "*$filtrecomp*";
        $list_machines=search_machines("(&(cn=$filtrel)(objectClass=ipHost))","computers");
        // tri des machines dï¿½a prï¿½entes dans le parc
        $lmloop=0;
        $mpcount=count($mp);
        for ($loop=0; $loop < count($list_machines); $loop++) {
             $loop1=0;
             $mach=$list_machines[$loop]["cn"];
             while (("$mp[$loop1]" != "$mach") && ($loop1 < $mpcount)) $loop1++;
               if ("$mp[$loop1]" != "$mach") $list_new_machines[$lmloop++]=$mach;
         }
         // Affichage menu de sï¿½ection des machines ï¿½ajouter au parc
         if  ( count($list_new_machines)>10) $size=10; else $size=count($list_new_machines);
         if ( count($list_new_machines)>0) {
         	echo "<form action=\"maintenance.php\" method=\"GET\" name=\"ajout_form\">\n";
                echo"<p>".gettext("S&#233;lectionnez une machine &#224; signaler &#224; la maintenance :")."</p>\n";
                echo "<p><select size=\"".$size."\" name=\"mpenc\">\n";
                for ($loop=0; $loop < count($list_new_machines); $loop++) {
                	echo "<option value=\"".$list_new_machines[$loop]."\"";
            		if ($list_new_machines[$loop] == $row[0]) { echo "selected"; }  
            		echo ">".$list_new_machines[$loop]."</option>\n";
        	}
        	echo "</select></p>\n";

		// 
        	if ($ligne != "0") {
            		echo "<font color=\"orange\"><I>".gettext("(La machine pr&#233;selectionn&#233;e est la machine &#224; partir de laquelle vous faites la demande)")."</I></font><BR><BR>\n";
        	} 
                
		// Affiche les boutons
        	echo "<table width=\"40%\">\n";
		echo "<tr><td>\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"ajout\">\n";
		echo "<input type=\"submit\" value=\"Demande de maintenance\">\n";
		echo "</TD><TD>\n";
        	echo "<input type=\"reset\" value=\"".gettext("R&#233;initialiser")."\">\n";
		echo "</td>\n";
        	echo "</form>\n";

        }
//}      

// si il a le droit d'admin sur la maintenance
if ((is_admin("computers_is_admin",$login)=="Y") or (is_admin("parc_can_manage",$login)=="Y") or (is_admin("parc_can_view",$login)=="Y")) {
	echo "<FORM action=\"maintenance.php\" method=\"GET\" name=\"voir_form\">\n";
	echo "<td>";
// 	echo "<input name=\"etat\" type=\"hidden\" value=\"0\">";
	echo "<INPUT name=\"action\" type=\"hidden\" value=\"all\">";
        echo "<input type=\"submit\" value=\"".gettext("Tout voir")."\">\n";
	echo "</td>";
	echo "</form>";
} 
echo "</tr></table>\n";

include ("pdp.inc.php");



?>
