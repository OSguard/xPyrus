#!/usr/bin/perl -w
#
# convert test data into sql queries
#
# written by Andreas 'ads' Scherbaum <ads@unihelp.de>
#
# v0.1: 2006-06-09
#
# $Id: create_test_data.pl 5865 2008-05-03 09:56:26Z trehn $
#

use strict;
use FileHandle;
use Digest::SHA1 qw(sha1_hex);

sub encrypt_password {
  my $password = shift;
  # NOTE: empty salt here
  my $salt = '';
  return sha1_hex($salt . $password);
}

# validate parameters
if (!defined($ARGV[0])) {
  help();
  exit(1);
}
if ($ARGV[0] ne "-user") {
  help();
  exit(1);
}
if (!-f $ARGV[1]) {
  help();
  exit(1);
}

# read in input file
my $fh = new FileHandle;
open($fh, "<", $ARGV[1]) || die "could not open input file: $!\n";
my @file = <$fh>;
close($fh);

# create variables for storing the query data
$main::user_data = "";
$main::friend_data = "";
$main::role_data = "";
$main::right_data = "";
$main::study_path_data = "";

# parse the file
my @file2 = ();
for (my $num = 0; $num <= $#file; $num++) {
  if ($file[$num] =~ /^#/ or $file[$num] =~ /^\-\-/) {
    next;
  }
  # remove linebreaks
  $file[$num] =~ s/[\r\n]//gs;
  $file[$num] =~ s/\t+/ /gs;
  # check for a valid line
  if ($file[$num] =~ /^([a-zA-Z0-9_\-]+):([\s]+)(.+)$/) {
    push(@file2, $1 . ": " . $3);
    #print "read: $1 : |$3| (" . length($2) . ")\n";
  } elsif ($file[$num] =~ /^([a-zA-Z0-9_\-]+):[\s\t]*$/) {
    # ignore valid lines without value
  } elsif (length($file[$num]) == 0) {
    # but add blank lines since they start a new block
    push(@file2, "");
  } else {
    print STDERR "parse error in line " . ($num + 1) . "\n";
    exit(1);
  }
}
# copy parsed data back
@file = @file2;

# now jump into subroutine to create sql queries from the data
if ($ARGV[0] eq "-user") {
  create_user_data(\@file);
  print "$main::user_data\n";
  print "$main::friend_data\n";
  print "$main::role_data\n";
  print "$main::right_data\n";
  print "$main::study_path_data\n";
}




sub create_user_data {
  my $file = shift;
  my @file = @$file;

  my %user = ();
  my @friends = ();
  my @role = ();
  my @right = ();
  my @studyPaths = ();
  my ($have_user, $line);

  $have_user = 0;
  foreach $line (@file) {
    if (length($line) == 0) {
      # do we have user data?
      if ($have_user == 1) {
        # do we have user data?
        if (!defined($user{'username'}) or length($user{'username'}) < 1) {
          # we cannot output a line number since the file is already parsed
          print STDERR "missing the username!\n";
          exit(1);
        }
        if (!defined($user{'password'}) or length($user{'password'}) < 1) {
          # we cannot output a line number since the file is already parsed
          print STDERR "missing the password!\n";
          exit(1);
        }
        # create sql queries

          my $query1 = "INSERT INTO __SCHEMA__.users
            (username" . 
             ((defined($user{'flag_activated'})) ? ",\n             flag_activated" : "") .
             ((defined($user{'flag_active'})) ? ",\n             flag_active" : "") .
             ((defined($user{'flag_invisible'})) ? ",\n             flag_invisible" : "") .
             ((defined($user{'uni'}) and defined($user{'uni_city'})) ? ",\n             uni_id" : "") .
             ((defined($user{'person_type'})) ? ",\n             person_type" : "") .
             ((defined($user{'nationality'})) ? ",\n             nationality_id" : "") .
             ((defined($user{'userpic_file'})) ? ",\n             userpic_file" : "") .
             ((defined($user{'flirt_status'})) ? ",\n             flirt_status" : "") .
             ((defined($user{'gender'})) ? ",\n             gender" : "") .
             ((defined($user{'birthdate'})) ? ",\n             birthdate" : "") .
             ((defined($user{'signature_raw'})) ? ",\n             signature_raw" : "") .
             ((defined($user{'password'})) ? ",\n             password" : "") .
             ((defined($user{'points'})) ? ",\n             points_sum" : "") .
             ((defined($user{'points'})) ? ",\n             points_flow" : "") .
             ((defined($user{'first_login'})) ? ",\n             first_login" : "") .
             ((defined($user{'last_login'})) ? ",\n             last_login" : "") . ")
     VALUES ('" . $user{'username'} . "'" .
             ((defined($user{'flag_activated'})) ? (($user{'flag_activated'} eq "true") ? ",\n             true" : ",\n             false") : "") .
             ((defined($user{'flag_active'})) ? (($user{'flag_active'} eq "true") ? ",\n             true" : ",\n             false") : "") .
             ((defined($user{'flag_invisible'})) ? (($user{'flag_invisible'} eq "true") ? ",\n             true" : ",\n             false") : "") .
             ((defined($user{'uni'}) and defined($user{'uni_city'})) ? (",\n             (SELECT id
                FROM public.uni
               WHERE name='" . simple_quote($user{'uni'}) . "'
                 AND city=(SELECT id
                             FROM cities
                            WHERE name='" . simple_quote($user{'uni_city'}) . "'))") : "") .
             ((defined($user{'person_type'})) ? (",\n             (SELECT id
                FROM public.person_types
               WHERE name='" . simple_quote($user{'person_type'}) . "')") : "") .
             ((defined($user{'nationality'})) ? (",\n             (SELECT id
                FROM public.countries
               WHERE nationality='" . simple_quote($user{'nationality'}) . "')") : "") .
             ((defined($user{'userpic_file'})) ? (",\n             '" . simple_quote($user{'userpic_file'}) . "'") : "") .
             ((defined($user{'flirt_status'})) ? (",\n             '" . simple_quote($user{'flirt_status'}) . "'") : "") .
             ((defined($user{'gender'})) ? (",\n             '" . simple_quote($user{'gender'}) . "'") : "") .
             ((defined($user{'birthdate'})) ? (",\n             '" . simple_quote($user{'birthdate'}) . "'") : "") .
             ((defined($user{'signature_raw'})) ? (",\n             '" . simple_quote($user{'signature_raw'}) . "'") : "") .
             ((defined($user{'password'})) ? (",\n             '" . simple_quote(encrypt_password($user{'password'})) . "'") : "") .
             ((defined($user{'points'})) ? (",\n             '" . simple_quote($user{'points'}) . "'") : "") .
             ((defined($user{'points'})) ? (",\n             '" . simple_quote($user{'points'}) . "'") : "") .
             ((defined($user{'first_login'})) ? (($user{'first_login'} eq "now") ? ",\n             NOW()" : (",\n             '" . $user{'first_login'} . "'")) : "") .
             ((defined($user{'last_login'})) ? (($user{'last_login'} eq "now") ? ",\n             NOW()" : (",\n             '" . $user{'last_login'} . "'")) : "") . ");\n";

        # add query to storage
        $main::user_data .= $query1 . "\n";
        #print "$query1\n\n";

        my @query_keys = ();
        my @query_values = ();
        if (defined($user{'title'})) {
          push(@query_keys, 'title');
          push(@query_values, simple_quote($user{'title'}));
        }
        if (defined($user{'salutation'})) {
          push(@query_keys, 'salutation');
          push(@query_values, simple_quote($user{'salutation'}));
        }
        if (defined($user{'first_name'})) {
          push(@query_keys, 'first_name');
          push(@query_values, simple_quote($user{'first_name'}));
        }
        if (defined($user{'second_name'})) {
          push(@query_keys, 'second_name');
          push(@query_values, simple_quote($user{'second_name'}));
        }
        if (defined($user{'last_name'})) {
          push(@query_keys, 'last_name');
          push(@query_values, simple_quote($user{'last_name'}));
        }
        if (defined($user{'zip_code'})) {
          push(@query_keys, 'zip_code');
          push(@query_values, simple_quote($user{'zip_code'}));
        }
        if (defined($user{'location'})) {
          push(@query_keys, 'location');
          push(@query_values, simple_quote($user{'location'}));
        }
        if (defined($user{'street'})) {
          push(@query_keys, 'street');
          push(@query_values, simple_quote($user{'street'}));
        }
        if (defined($user{'telephone'})) {
          push(@query_keys, 'telephone');
          push(@query_values, simple_quote($user{'telephone'}));
        }
        if (defined($user{'telephone_mobil'})) {
          push(@query_keys, 'telephone_mobil');
          push(@query_values, simple_quote($user{'telephone_mobil'}));
        }
        if (defined($user{'fax'})) {
          push(@query_keys, 'fax');
          push(@query_values, simple_quote($user{'fax'}));
        }
        if (defined($user{'public_email'})) {
          push(@query_keys, 'public_email');
          push(@query_values, simple_quote($user{'public_email'}));
        }
        if (defined($user{'private_email'})) {
          push(@query_keys, 'private_email');
          push(@query_values, simple_quote($user{'private_email'}));
        }
        if (defined($user{'uni_email'})) {
          push(@query_keys, 'uni_email');
          push(@query_values, simple_quote($user{'uni_email'}));
        }
        if ($#query_keys > -1) {
          # have something to add
          my $update_count = 0;
          my $query2 = "UPDATE __SCHEMA__.user_extra_data\n";
          for (my $i = 0; $i <= $#query_keys; $i++) {
            $update_count++;
            if ($update_count == 1) {
              $query2 .= "   SET " . $query_keys[$i] . "='" . $query_values[$i] . "'";
            } else {
              $query2 .= ",\n       " . $query_keys[$i] . "='" . $query_values[$i] . "'";
            }
          }
          $query2 .= "\n WHERE id=(SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='" . simple_quote($user{'username'}) . "');\n";
          # add query to storage
          $main::user_data .= $query2 . "\n";
          #print "$query2\n\n";
        }
        $main::user_data .= "\n";

        if ($#friends > -1) {
          # have friends
          foreach my $friend (@friends) {
            my @friend = split(/\//, $friend);
            my $query3 = "INSERT INTO __SCHEMA__.user_friends
    (user_id, friend_id, friend_type)
  VALUES
    ((SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='" . simple_quote($user{'username'}) . "'),
     (SELECT id::BIGINT FROM __SCHEMA__.users WHERE username='" . simple_quote($friend[0]) . "'),
     (SELECT id::INTEGER FROM public.friend_types WHERE type_name='" . simple_quote($friend[1]) . "'));\n";
            $main::friend_data .= "$query3\n";
            #print "$query3\n";
          }
        }

        if ($#role > -1) {
          # have roles
          foreach my $role (@role) {
            my $query3 = "INSERT INTO __SCHEMA__.user_role_membership
		(user_id, role_id)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='" . simple_quote($user{'username'}) . "'),
            (SELECT id FROM __SCHEMA__.user_roles WHERE name='" . simple_quote($role) . "') );\n";
            $main::role_data .= "$query3\n";
            #print "$query3\n";
          }
        }
        
        if ($#studyPaths > -1) {
          # have studyPaths
          foreach my $sp (@studyPaths) {
            my $query3 = "INSERT INTO __SCHEMA__.study_path_per_student
		(user_id, study_path_id, primary_course)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='" . simple_quote($user{'username'}) . "'),
            (SELECT id FROM __SCHEMA__.study_path WHERE name='" . simple_quote($sp) . "'), true );\n";
            $main::study_path_data .= "$query3\n";
            #print "$query3\n";
          }
        }
      }

      if ($#right > -1) {
        # have rights
        foreach my $right (@right) {
          my $query3 = "";
          if ($right eq "ALL") {
            $query3 = "INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	SELECT (SELECT id FROM __SCHEMA__.users WHERE username='" . simple_quote($user{'username'}) . "'), id, true FROM __SCHEMA__.rights;\n";
          } else {
            $query3 = "INSERT INTO __SCHEMA__.rights_user
		(user_id, right_id, right_granted)
	VALUES ( (SELECT id FROM __SCHEMA__.users WHERE username='" . simple_quote($user{'username'}) . "'),
            (SELECT id FROM __SCHEMA__.rights WHERE name='" . simple_quote($right) . "'),
            true );\n";
          }
          $main::right_data .= "$query3\n";
          #print "$query3\n";
        }
      }

      # reset flag
      $have_user = 0;
      %user = ();
      @friends = ();
      @right = ();
      @role = ();
      @studyPaths = ();
    } else {
      # must be user data
      if ($line =~ /^friend:\s+(.+)\/(.+)$/) {
        push(@friends, ($1 . "/" . $2));
      } elsif ($line =~ /^friend:\s+(.+)$/) {
        print STDERR "illegal friend line!\n";
        exit(1);
      } elsif ($line =~ /^role:\s+(.+)$/) {
        push(@role, $1);
      } elsif ($line =~ /^study_path:\s+(.+)$/) {
        push(@studyPaths, $1);
      } elsif ($line =~ /^right:\s+(.+)$/) {
        push(@right, $1);
      } elsif ($line =~ /^([a-zA-Z0-9_\-]+):\s+(.+)$/) {
        $user{$1} = $2;
        $have_user = 1;
      } else {
        # should not happen here since we already parsed the data earlier
        print STDERR "parse error!\n";
        exit(1);
      }
    }
  }



}



sub simple_quote {
  my $string = shift;

  $string =~ s/\'/\\\'/gs;

  return $string;
}

sub help {
  print STDERR "usage:\n";
  print STDERR "$0 -user <inputfile>\n";
  return;
}
