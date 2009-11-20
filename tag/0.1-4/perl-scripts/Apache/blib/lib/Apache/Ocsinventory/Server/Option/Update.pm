###############################################################################
## OCSINVENTORY-NG 
## Copyleft Pascal DANEK 2005
## Web : http://ocsinventory.sourceforge.net
##
## This code is open source and may be copied and modified as long as the source
## code is always made freely available.
## Please refer to the General Public Licence http://www.gnu.org/ or Licence.txt
################################################################################
package Apache::Ocsinventory::Option::Update;

use strict;

BEGIN{
	if($ENV{'OCS_MODPERL_VERSION'} == 1){
		require Apache::Ocsinventory::Server::Modperl1;
		Apache::Ocsinventory::Server::Modperl1->import();
	}elsif($ENV{'OCS_MODPERL_VERSION'} == 2){
		require Apache::Ocsinventory::Server::Modperl2;
		Apache::Ocsinventory::Server::Modperl2->import();
	}
}

require Exporter;

our @ISA = qw /Exporter/;

our @EXPORT = qw //;

BEGIN{
	if($ENV{'OCS_MODPERL_VERSION'} == 1){
		require Apache::Ocsinventory::Server::Modperl1;
		Apache::Ocsinventory::Server::Modperl1->import();
	}elsif($ENV{'OCS_MODPERL_VERSION'} == 2){
		require Apache::Ocsinventory::Server::Modperl2;
		Apache::Ocsinventory::Server::Modperl2->import();
	}
}

use Apache::Ocsinventory::Server::System;
use Apache::Ocsinventory::Server::Communication;
use Apache::Ocsinventory::Server::Constants;

# Initialize option
push @{$Apache::Ocsinventory::OPTIONS_STRUCTURE},{
	'HANDLER_PROLOG_READ' => undef,
	'HANDLER_PROLOG_RESP' => undef,
	'HANDLER_INVENTORY' => undef,
	'REQUEST_NAME' => 'UPDATE',
	'HANDLER_REQUEST' => \&_update_handler,
	'HANDLER_DUPLICATE' => undef,
	'TYPE' =>undef
};

# Default
$Apache::Ocsinventory::{OPTIONS}{'OCS_OPT_UPDATE'} = 0;

# To manage the update request
sub _update_handler{
	my $current_context = shift;
	my $data = $current_context->{'DATA'};
	my $dbh = $current_context->{'DBI_HANDLE'};

	my %resp;
	my @agent;
	my @dmi;
	my @ipdiscover;
	my $Acurrent;
	my $Dcurrent;
	my $Icurrent;
	my $Iversion;
	my $agent;
	my $dmi;
	my $ip;
	my $platform;
	my $Dversion;
	my $Aversion;
	my $request;
	my $row;
	my $query;

	#Looking for option status
	unless($ENV{'OCS_OPT_UPDATE'}){
		&_send_response({'RESPONSE',['NO_UPDATE']});
		return APACHE_OK;
	}

	# Parse the request
	##########################
	# Parse the XML request
	unless($query = XML::Simple::XMLin( $$data, SuppressEmpty => 1 )){
		&_log(507,'update','Xml stage');
		return APACHE_SERVER_ERROR;
	}

	# OS type
	$platform = $query->{PLATFORM};
	# Version of the agent
	$Aversion = $query->{AGENT};

	# Eventually, the DMI version
	if(defined($query->{DMI})){$Dversion = $query->{DMI}};
	if(defined($query->{IPDISCOVER})){$Iversion = $query->{IPDISCOVER}};
	if(!defined($Aversion) || !($platform=~/^WINDOWS$|^MAC$|^LINUX$/)){
			&_log(508,'update','') if $ENV{'OCS_OPT_LOGLEVEL'};
			return APACHE_BAD_REQUEST;
	}
	
	# What are the available versions in the database
	$request = $dbh->prepare('SELECT * FROM files WHERE OS=?');
	$request->execute($platform);
	
	# If no file available, tell to the client not to update
	unless($request->rows){
		&_send_response({ 'RESPONSE' => ['NO_UPDATE'] });
		$request->finish;
		return APACHE_OK;
	}else{
		# Files are available, does the client have to download and install it ?
		# Get versions number
		while($row=$request->fetchrow_hashref()){
			# Version of the agent in the database
			if($row->{'NAME'}=~/agent/i){
				push @agent, $row->{'VERSION'};
			}
			# Maybe a dmi reader version(on a linux computer)
			if(defined($Dversion)){
				# Version of the dmi in the database
				if($row->{'NAME'}=~/dmi/i){
					push @dmi, $row->{'VERSION'};
				}
			}
			if(defined($Iversion)){
				# Version of ipdiscover in the database
				if($row->{'NAME'}=~/ipdiscover/i){
					push @ipdiscover, $row->{'VERSION'};
				}
			}
		}
		# Determine the upper agent version available	
		if(@agent){
			# Looking for the latest version
			$Acurrent = 0;
			for(@agent){
				if($_>$Acurrent){$Acurrent = $_;}
			}
			# Compare to the client version. If different, we tell you to update
			$agent = ($Aversion==$Acurrent)?0:1;
		}else{
			$agent = 0;
		}
		
		# DMI
		$Dcurrent = 0;
		if(defined($Dversion) and @dmi){
			for(@dmi){
				if($_>$Dcurrent){$Dcurrent = $_;}
			}
			# Compare to the client version. If different, we tell you to update
			$dmi = ($Dversion==$Dcurrent)?0:1;
		}
		# IPDISCOVER
		if(defined($Iversion) and @ipdiscover){
			for(@ipdiscover){
				if($_>$Icurrent){$Icurrent = $_;}
			}
			# Compare to the client version. If different, we tell you to update
			$ip = ($Iversion==$Icurrent)?0:1;
		}
	}
	$request->finish;
	
	# Generate the response
	unless(($agent) or ($dmi) or $ip){
		&_send_response({'RESPONSE',['NO_UPDATE']});
		return APACHE_OK;
	}
	$resp{'RESPONSE'} = ['UPDATE'];
	if( $agent ){ $resp{'AGENT'} = [ $Acurrent ] }
	if( $dmi ){ $resp{'DMI'} = [ $Dcurrent ] }
	if( $ip ){ $resp{'IPDISCOVER'} = [ $Icurrent ] }
	
	# Send it
	&_send_response(\%resp);
	return APACHE_OK;

}
1;
