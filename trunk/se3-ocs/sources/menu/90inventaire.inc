<?
/* $Id: 90inventaire.inc 1485 2006-12-10 09:21:36Z plouf $ */

require_once("lang.inc.php");
bindtextdomain('se3-core',"/var/www/se3/locale");
textdomain ('se3-core');

$liens1=array(array(
              gettext("Inventaire"),"computers_is_admin|maintenance_can_write|parc_can_manage|parc_can_view|inventaire_can_read",1,
              gettext("Inventaire"),"ocsreports/","computers_is_admin|inventaire_can_read|parc_can_manage|parc_can_view",1,
              gettext("Rechercher"),"ocsreports/?multi=1","computers_is_admin|inventaire_can_read|parc_can_manage|parc_can_view",1,
              gettext("Maintenance"),"ocsreports/maintenance.php","computers_is_admin|maintenance_can_write|parc_can_manage|parc_can_view",2,
));
	     $liens=array_merge($liens,$liens1);
?>
