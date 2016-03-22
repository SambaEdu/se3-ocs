###############################################################################
## OCSINVENTORY-NG 
## Copyleft Pascal DANEK 2008
## Web : http://www.ocsinventory-ng.org
##
## This code is open source and may be copied and modified as long as the source
## code is always made freely available.
## Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
################################################################################
package Apache::Ocsinventory::Server::Inventory::Update::Hardware;

use strict;

require Exporter;

our @ISA = qw /Exporter/;

our @EXPORT = qw / _hardware /; 

use Apache::Ocsinventory::Server::Inventory::Cache;
use Apache::Ocsinventory::Server::Constants;
use Apache::Ocsinventory::Server::System qw / :server /;

sub _hardware{
  my $sectionMeta = shift;
  my $result = $Apache::Ocsinventory::CURRENT_CONTEXT{'XML_ENTRY'};
  my $base = $result->{CONTENT}->{HARDWARE};
  my $ua = $Apache::Ocsinventory::CURRENT_CONTEXT{'USER_AGENT'};
  my $deviceId = $Apache::Ocsinventory::CURRENT_CONTEXT{'DATABASE_ID'};
  my $dbh = $Apache::Ocsinventory::CURRENT_CONTEXT{'DBI_HANDLE'};
  # We replace all data but quality and fidelity. The last come becomes the last date.
  my $userid = '';	
  $userid = "USERID=".$dbh->quote($base->{USERID})."," if( $base->{USERID}!~/(system|localsystem)/i );

  my $ipAddress = &_get_default_iface();

  $dbh->do("UPDATE hardware SET USERAGENT=".$dbh->quote($ua).", 
	LASTDATE=".((defined($base->{LASTDATE})&&($base->{LASTDATE} ne "1970-01-01"))?$dbh->quote($base->{LASTDATE}):"NOW()").", 
	LASTCOME=NOW(),
	CHECKSUM=(".(defined($base->{CHECKSUM})?$base->{CHECKSUM}:CHECKSUM_MAX_VALUE)."|CHECKSUM|1),
	NAME=".$dbh->quote($base->{NAME}).", 
	WORKGROUP=".$dbh->quote($base->{WORKGROUP}).",
	USERDOMAIN=".$dbh->quote($base->{USERDOMAIN}).",
	OSNAME=".$dbh->quote($base->{OSNAME}).",
	OSVERSION=".$dbh->quote($base->{OSVERSION}).",
	OSCOMMENTS=".$dbh->quote($base->{OSCOMMENTS}).",
	PROCESSORT=".$dbh->quote($base->{PROCESSORT}).", 
	PROCESSORS=".(defined($base->{PROCESSORS})?$base->{PROCESSORS}:0).", 
	PROCESSORN=".(defined($base->{PROCESSORN})?$base->{PROCESSORN}:0).", 
	MEMORY=".(defined($base->{MEMORY})?$base->{MEMORY}:0).",
	SWAP=".(defined($base->{SWAP})?$base->{SWAP}:0).",
	IPADDR=".$dbh->quote($ipAddress).",
	DNS=".$dbh->quote($base->{DNS}).",
	DEFAULTGATEWAY=".$dbh->quote($base->{DEFAULTGATEWAY}).",
	ETIME=NULL,
	$userid
	TYPE=".(defined($base->{TYPE})?$base->{TYPE}:0).",
	DESCRIPTION=".$dbh->quote($base->{DESCRIPTION}).",
	WINCOMPANY=".$dbh->quote($base->{WINCOMPANY}).",
	WINOWNER=".$dbh->quote($base->{WINOWNER}).",
	WINPRODID=".$dbh->quote($base->{WINPRODID}).",
	WINPRODKEY=".$dbh->quote($base->{WINPRODKEY}).",
	IPSRC=".$dbh->quote($Apache::Ocsinventory::CURRENT_CONTEXT{IPADDRESS}).",
	UUID=".$dbh->quote($base->{UUID}).",
	ARCH=".$dbh->quote($base->{ARCH})."
	 WHERE ID=".$deviceId)
  or return(1);

  #We feed cache tables associated to hardware fields
  if ($ENV{OCS_OPT_INVENTORY_CACHE_ENABLED}) {
    my $cache_values =[];

    for (keys %{ $sectionMeta->{field_cached}} ) {
      #Feeding array for cache values
      $cache_values->[ $sectionMeta->{field_cached}->{$_} ] = $base->{$_};
    }
    &_cache( 'add', 'hardware', $sectionMeta, $cache_values );
  }

  $dbh->commit unless $ENV{'OCS_OPT_INVENTORY_TRANSACTION'};
  0;
}

sub _get_default_iface{
  return undef if !defined $Apache::Ocsinventory::CURRENT_CONTEXT{XML_ENTRY}->{CONTENT}->{NETWORKS};
  my $networks = $Apache::Ocsinventory::CURRENT_CONTEXT{XML_ENTRY}->{CONTENT}->{NETWORKS};
  for( @$networks ){
    if( $_->{IPADDRESS} eq $Apache::Ocsinventory::CURRENT_CONTEXT{IPADDRESS}){
      return $_->{IPADDRESS};
    }
  }
  return $Apache::Ocsinventory::CURRENT_CONTEXT{XML_ENTRY}->{CONTENT}->{HARDWARE}->{IPADDR};
}
1;
