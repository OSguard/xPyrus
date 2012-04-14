#!/usr/bin/perl -w

use warnings;
use strict;
use FindBin qw($Bin);

my $www_group = getgrnam('www-data');
my $www_user  = getpwnam('www-data');

# automatically fix errors
my $fix_errors = 1;

my %reqs = ( 'conf/local_config.php' => 
                { uid => 0,
                  gid => $www_group,
                  mode => 0640 },
             'conf/mantis_config.php' => 
                { uid => 0,
                  gid => $www_group,
                  mode => 0640 },
             'userfiles/users' => 
                { uid => 0,
                  gid => $www_group,
                  mode => 0775 },
             'userfiles/groups' => 
                { uid => 0,
                  gid => $www_group,
                  mode => 0775 },
             'images/tex' => 
                { uid => 0,
                  gid => $www_group,
                  mode => 0775 },
             'lib/lib-latex/tmp' => 
                { uid => 0,
                  gid => $www_group,
                  mode => 0775 }
            );

sub prepare_file($%) {
    my ($filename, %reqs) = @_;
    
    if (exists($reqs{mode})) {
        (chmod $reqs{mode}, $filename) > 0 or return -1;
    }
    if (exists($reqs{uid}) and exists($reqs{gid})) {
        (chown $reqs{uid}, $reqs{gid}, $filename) > 0 or return -1;
    }
    
    return 0;
}

sub check_file($%) {
    my ($filename, %reqs) = @_;
    my ( $dev, $ino, $mode, $nlink,
         $uid, $gid, $rdev, $size,
         $atime, $mtime, $ctime,
         $blksize, $blocks)          = stat($filename);
    
    my $something_wrong = 0;
    
    if (exists($reqs{uid}) and $uid != $reqs{uid}) {
        print "wrong uid=$uid for $filename (must be $reqs{uid})\n";
        $something_wrong = 1;
    }
    
    if (exists($reqs{gid}) and $gid != $reqs{gid}) {
        print "wrong gid=$gid for $filename (must be $reqs{gid})\n";
        $something_wrong = 1;
    }
    
    $mode &= 0777;
    if (exists($reqs{mode}) and $mode != $reqs{mode}) {
        my $m1 = sprintf "%o", $mode;
        my $m2 = sprintf "%o", $reqs{mode};
        #print "wrong mode=$mode for $filename (must be $reqs{mode})\n";
        print "wrong mode=$m1 for $filename (must be $m2)\n";
        $something_wrong = 1;
    }
    
    if ($something_wrong and $fix_errors) {
        if (prepare_file($filename, %reqs) == 0) {
            print "       fixed.\n"
        } else {
            print "[WARN] failed\n";
        }
    }
}

my $path;
if ($Bin =~ m!(.*)/[^/]+!) {
    $path = $1;
} else {
    die ('could not determine parent directory');
}

chdir $path;

foreach my $filename (keys %reqs) {
    if (-e $filename) {
        #print $reqs{$filename} . "\n";
        check_file($filename, %{$reqs{$filename}} );
    } else {
        print "[WARN] $filename not found\n";
    }
}
