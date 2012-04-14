#!/usr/bin/perl -w

use strict;
use warnings;

#echo "Target directory for installation: "
#DEV read TARGET
#TARGET=/tmp

#SMARTY_VERSION=`cat htdocs/lib/lib-template/index.php | perl -n -e 'print $1 if /SMARTY_VERSION.*(\d+\.\d+\.\d+)/'`
#OFFSET=`perl -e 'print int(rand(0xffefffff))'`

#wget "http://smarty.php.net/do_download.php?download_file=Smarty-$SMARTY_VERSION.tar.gz"
#if test -z $?
#then
#fi

my $smartyVersion;
my $targetDir;

sub ask {
    my ($q, $default) = @_;
    print "$q: ";
	if (defined $default) {
		print " [$default] ";
	}
    chomp(my $desc = <STDIN>);
	if ($desc eq '' && defined $default) {
		$desc = $default;
	}
    return $desc;
}

sub askYN {
    my ($q) = @_;
    print "$q [y/n] ";
    chomp(my $desc = <STDIN>);
    return (lc $desc) eq 'y';
}

sub execute {
    my ($cmd, $errorMsg) = @_;
    system($cmd) == 0 or die "ERROR: $errorMsg";
}


#FIXME
$targetDir = '/tmp/test';
if (-d $targetDir) {
    if (!(askYN "Target directory already exists. Overwrite?")) {
        exit -1;
    }
} else {
    mkdir $targetDir or die "ERROR: Could not create target directory";
}

open(SMARTY_CONF, 'htdocs/lib/lib-template/index.php') or die "ERROR: Could not open Smarty configuration file";
while (<SMARTY_CONF>) {
    if (/SMARTY_VERSION.*(\d+\.\d+\.\d+)/) {
        $smartyVersion = $1;
        last;
    }
}
close(SMARTY_CONF);

#FIXME
#system("wget 'http://smarty.php.net/do_download.php?download_file=Smarty-$smartyVersion.tar.gz") or die "Could not download Smarty from smarty.php.net";
print "Downloading Smarty\n";
execute "cp /home/linap/downloads/Smarty-$smartyVersion.tar.gz .", "Could not download Smarty from smarty.php.net";

print "Copying files\n";
execute "cp -r htdocs sql $targetDir", "Could not copy files";

print "Extracting Smarty\n";
execute "tar -xzf Smarty-$smartyVersion.tar.gz -C $targetDir/htdocs/lib/lib-template/", "Could not extract Smarty archive";

my $schemaName        = ask "Database schema name (lowercase city name is usually a good idea)";
my $databaseUser      = ask "Database super user", "postgres";
my $databasePassword;
while (1) {
	$databasePassword  = ask "Database password (6 characters minimum)";
	last if ($databasePassword ne '' && length $databasePassword >= 6);
}
