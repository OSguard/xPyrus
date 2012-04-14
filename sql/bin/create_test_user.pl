#!/usr/bin/perl -w
#
# create test user
#
# written by Andreas 'ads' Scherbaum <ads@unihelp.de>
#
# v0.1: 2007-01-27
#
# $Id: create_test_user.pl 4779 2007-06-10 22:39:53Z ads $
#

use strict;
use Digest::MD5 qw(md5 md5_hex md5_base64);
use FileHandle;

#my $target_city = 'Magdeburg';
my $target_city = 'Mauritius';

if (!defined($ARGV[0]) or $ARGV[0] !~ /^\d+$/ or $ARGV[0] < 1) {
  print STDERR "usage:\n";
  print STDERR "$0 number_user\n";
  exit(1);
}

my $number_user = shift(@ARGV);

srand(time());
%main::created_user = ();
read_names_from_file();

my (%data);
for (my $i = 1; $i <= $number_user; $i++) {
  # create username
  my $found = 0;
  my $_name = '';
  do {
    #my $name = md5_hex(rand(32767));
    #$name = "demo_" . substr($name, 0, int(rand(15)) + 3);
    #if (!defined($main::created_user{$name})) {
    #  # name not yet known
    #  print "username:\t\t$name\n";
    #  $main::created_user{$name} = 1;
    #  $found = 1;
    #}
    #$_name = $name;
    %data = generate_user();
    if (!defined($main::created_user{$data{'username'}})) {
      # name not yet known
      $found++;
      $main::created_user{$data{'username'}} = 1;
    }
  } while ($found == 0);
  print "username:\t\t" . $data{'username'} . "\n";
  # flags
  print "flag_activated:\t\ttrue\n";
  print "flag_active:\t\ttrue\n";
  print "flag_invisible:\t\tfalse\n";
  if ($target_city eq 'Mauritius') {
    print "uni_city:\t\tMauritius\n";
    print "uni:\t\t\tUnihelp Universität Mauritius\n";
  } else {
    print "uni_city:\t\tMagdeburg\n";
    # decide which university
    if (yesno() == 1) {
      print "uni:\t\t\tOtto-von-Guericke-Universität Magdeburg\n";
    } else {
      print "uni:\t\t\tHochschule Magdeburg-Stendal (FH)\n";
    }
  }
  # kind of user
  print "person_type:\t\tStudent\n";
  print "title:\t\t\t\n";
  print "salutation:\t\t\n";
  print "first_name:\t\t" . $data{'first_name'} . "\n";
  print "second_name:\t\t\n";
  print "last_name:\t\t" . $data{'last_name'} . "\n";
  print "zip_code:\t\t" . int(rand(99999)) . "\n";
  print "location:\t\tl_" . substr(md5_hex(rand(32767)), 0, int(rand(15)) + 3) . "\n";
  print "street:\t\t\t\n";
  print "country:\t\tDeutschland\n";
  print "nationality:\t\tdeutsch\n";
  print "telephone:\t\t\n";
  print "telephone_mobil:\t\n";
  print "fax:\t\t\t\n";
  print "public_email:\t\t" . substr(md5_hex(rand(32767)), 0, int(rand(15)) + 3) . '@dummy.public.invalid' . "\n";
  print "private_email:\t\t" . substr(md5_hex(rand(32767)), 0, int(rand(15)) + 3) . '@dummy.private.invalid' . "\n";
  print "uni_email:\t\t" . $data{'username'} . '@dummy.uni.invalid' . "\n";
  print "gender:\t\t\t" . $data{'gender'} . "\n";
  print "birthdate:\t\t19" . sprintf("%02d-%02d-%02d", int(rand(86)), int(rand(12)) + 1, int(rand(28)) + 1) . "\n";
  print "password:\t\t" . substr(md5_hex(rand(32767)), 0, int(rand(3)) + 5) . "\n";
  #print "password:\t\t" . substr(md5_hex(rand(32767)), 0, int(rand(15)) + 3) . "\n";
  print "first_login:\t\tnow\n";
  print "last_login:\t\tnow\n";
  print "login_errors:\t\t0\n";
  print "signature_text:\t\t" . $data{'signature'} . "\n";
  print "public_pgp_key:\t\t\n";
  print "description:\t\t\n";
  print "im_icq:\t\t\t\n";
  print "homepage:\t\t\n";
  print "details_visible_for:\tall\n";
  print "points:\t\t\t5432\n";
  print "\n";
}

sub yesno {
  my $rand = int(rand(2));
  #print "rand: $rand\n";
}


# read_names_from_file()
#
# read random names from files
#
# parameter:
#  none
# result:
#  none
sub read_names_from_file {
  # first name
  if (-f "vornamen_w.txt" and -f "vornamen_j.txt" and -f "nachnamen.txt") {
    my $fh1 = new FileHandle;
    open($fh1, "<", "vornamen_w.txt") || die("could not read vornamen_w.txt: $!\n");
    @main::first_name_f = <$fh1>;
    chomp(@main::first_name_f);
    close($fh1);
    my $fh2 = new FileHandle;
    open($fh2, "<", "vornamen_j.txt") || die("could not read vornamen_j.txt: $!\n");
    @main::first_name_m = <$fh2>;
    chomp(@main::first_name_m);
    close($fh2);
    my $fh3 = new FileHandle;
    open($fh3, "<", "nachnamen.txt") || die("could not read nachnamen.txt: $!\n");
    @main::last_name = <$fh3>;
    chomp(@main::last_name);
    close($fh3);
  } else {
    my ($run);
    @main::first_name_f = ();
    @main::first_name_m = ();
    @main::last_name = ();
    for ($run = 1; $run <= 2000; $run++) {
      push(@main::first_name_f, "fn_" . substr(md5_hex(rand(32767)), 0, int(rand(15)) + 3));
      push(@main::first_name_m, "fn_" . substr(md5_hex(rand(32767)), 0, int(rand(15)) + 3));
      push(@main::last_name, "fn_" . substr(md5_hex(rand(32767)), 0, int(rand(15)) + 3));
    }
  }
  @main::signature = ();
  if (-f "signatur.txt") {
    my $fh4 = new FileHandle;
    open($fh4, "<", "signatur.txt") || die("could not read signatur.txt: $!\n");
    @main::signature = <$fh4>;
    chomp(@main::signature);
    close($fh4);
  }
}


# generate_user()
#
# generate an user from random data
#
# parameter:
#  none
# return:
#  hash with userdata
sub generate_user {
  my %return = ();
  $return{'gender'} = (yesno() == 1) ? 'm' : 'f';
  # get first name
  if ($return{'gender'} eq 'm') {
    # male first name
    $return{'first_name'} = $main::first_name_m[int(rand($#main::first_name_m)) - 1];
  } else {
    # female first name
    $return{'first_name'} = $main::first_name_f[int(rand($#main::first_name_f)) - 1];
  }
  $return{'last_name'} = $main::last_name[int(rand($#main::last_name)) - 1];
  $return{'username'} = $return{'first_name'} . '_' . $return{'last_name'};
  $return{'username'} =~ s/ /_/g;
  # get signature
  if ($#main::signature > -1) {
    $return{'signature'} = $main::signature[int(rand($#main::signature)) - 1];
  } else {
    $return{'signature'} = '';
  }
  return %return;
}





