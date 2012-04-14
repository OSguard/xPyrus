#!/usr/bin/perl -w

use strict;
use FileHandle;
use DBI qw(:sql_types);
use DBD::Pg qw(:pg_types);
use Time::HiRes qw(gettimeofday tv_interval);

$|++;

my $new_db_host = "";
my $new_db_user = "";
my $new_db_pass = "";
my $new_db_db = "";
my $new_db_schema = "";

my $old_db_host = "";
my $old_db_user = "";
my $old_db_pass = "";
my $old_db_db = "";

if (!defined($ARGV[0]) or !-f $ARGV[0]) {
  print STDERR "usage:\n";
  print STDERR "$0 configfile [user] [gb] [friends]\n";
  print STDERR "\n";
  print STDERR "configfile is the Makefile.local from sql directory\n";
  exit(1);
}

# get content of configfile
my $fh = new FileHandle;
if (!open($fh, "<", $ARGV[0])) {
  die "could not open file ($ARGV[0]): $!\n";
}
my @file_content = <$fh>;
close($fh);

# extract parameters
my ($param);
foreach $param (@file_content) {
  $param =~ s/[\r\s]*$//gs;

  # new database (postgresql)
  if ($param =~ /^DB_HOST\=(.+)$/) {$new_db_host = $1;}
  if ($param =~ /^DB_NAME\=(.+)$/) {$new_db_db = $1;}
  if ($param =~ /^DB_ADMIN_USER\=(.+)$/) {$new_db_user = $1;}
  if ($param =~ /^DB_ADMIN_PASS\=(.+)$/) {$new_db_pass = $1;}
  if ($param =~ /^DB_SCHEMA\=(.+)$/) {$new_db_schema = $1;}

  # new database (postgresql)
  if ($param =~ /^OLD_DB_HOST\=(.+)$/) {$old_db_host = $1;}
  if ($param =~ /^OLD_DB_NAME\=(.+)$/) {$old_db_db = $1;}
  if ($param =~ /^OLD_DB_ADMIN_USER\=(.+)$/) {$old_db_user = $1;}
  if ($param =~ /^OLD_DB_ADMIN_PASS\=(.+)$/) {$old_db_pass = $1;}
}

# verify that we have parameters
if ($new_db_host eq "" or $old_db_host eq "") {
  die "please set all parameters in $ARGV[0]\n";
}

if ($new_db_schema eq "" or $new_db_schema eq "yourcity") {
  die "you need to set DB_SCHEMA\n";
}
if ($new_db_schema =~ /[A-Z\s]/) {
  die "the db schema name should not content capitalization and/or whitespaces\n";
}

# now open both database connections
my ($new_db, $old_db);
print STDERR "DB connect ... ";
$new_db = DBI->connect("DBI:Pg:dbname=$new_db_db;host=$new_db_host;port=5432",
                       "$new_db_user", "$new_db_pass",
                       {PrintError => 0}
                      );
if (!$new_db) {
  print STDERR "error!\n";
  print STDERR "$DBI::err\n$DBI::errstr\n";
  exit(1);
}
#$new_db->{AutoCommit} = 0;

$old_db = DBI->connect("DBI:mysql:dbname=$old_db_db;host=$old_db_host",
                       "$old_db_user", "$old_db_pass",
                       {PrintError => 0}
                      );
if (!$old_db) {
  print STDERR "error!\n";
  print STDERR "$DBI::err\n$DBI::errstr\n";
  exit(1);
}
shift(@ARGV);
print STDERR "OK\n";


%main::user_id = ();


######################################################################
# prefetch
print STDERR "prefetch some data ... ";
my ($query_pre_new, $st_pre_new, $row_pre_new);
my %pre_data = ();
# uni
$query_pre_new = "SELECT *
                     FROM public.uni
                    WHERE name='Otto-von-Guericke Universität Magdeburg'";
$st_pre_new = $new_db->prepare($query_pre_new);
if (!$st_pre_new->execute) {
  print STDERR "failed!\n";
  print STDERR "$DBI::err\n$DBI::errstr\n";
  exit(1);
}
if ($st_pre_new->rows != 1) {
  print STDERR "failed!\n";
  print STDERR "could not find uni entry!\n";
  exit(1);
}
$row_pre_new = $st_pre_new->fetchrow_hashref;
$pre_data{'uni'} = $row_pre_new->{id};
$st_pre_new->finish;
# student
$query_pre_new = "SELECT *
                     FROM public.person_types
                    WHERE name='Student'";
$st_pre_new = $new_db->prepare($query_pre_new);
if (!$st_pre_new->execute) {
  print STDERR "failed!\n";
  print STDERR "$DBI::err\n$DBI::errstr\n";
  exit(1);
}
if ($st_pre_new->rows != 1) {
  print STDERR "failed!\n";
  print STDERR "could not find person type entry!\n";
  exit(1);
}
$row_pre_new = $st_pre_new->fetchrow_hashref;
$pre_data{'person_type'} = $row_pre_new->{id};
$st_pre_new->finish;
print STDERR "OK\n";




######################################################################
# user data
if (defined($ARGV[0]) and $ARGV[0] eq "user") {
  shift(@ARGV);
  print STDERR "getting old user data ... ";
  my ($query_user_old, $st_user_old, $row_user_old, $row_user_count);
  my ($query_user_new, $st_user_new, $row_user_new);
  $query_user_old = "SELECT *
                       FROM unihelp_user
                   ORDER BY LOWER(username)";
  $st_user_old = $old_db->prepare($query_user_old);
  if (!$st_user_old->execute) {
    print STDERR "failed!\n";
    print STDERR "$DBI::err\n$DBI::errstr\n";
    exit(1);
  }
  print STDERR "OK\n";

  print STDERR "write user data ... ";
  $row_user_count = 0;
  $query_user_new = "INSERT INTO $new_db_schema.users
                                 (username,
                                  flag_activated, flag_active, flag_invisible,
                                  uni_id,
                                  person_type,
                                  title, salutation,
                                  first_name,
                                  second_name,
                                  last_name,
                                  zip_code, location, street,
                                  country_id,
                                  nationality_id,
                                  telephone, telephone_mobil, fax,
                                  public_email,
                                  private_email,
                                  uni_email,
                                  homepage, signature_text,
                                  public_key, description,
                                  gender, birthdate,
                                  birthday_public,
                                  password)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                                  ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                                  ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
  $st_user_new = $new_db->prepare($query_user_new);

  $new_db->begin_work;
  while ($row_user_old = $st_user_old->fetchrow_hashref) {
    # output username and count
    $row_user_count++;
    print STDERR sprintf("\033[1Ginsert user data ... (%5d) %s%s", $row_user_count,
                                                                   $row_user_old->{username},
                                                                   " " x (60 - length($row_user_old->{username})));
    if ($row_user_old->{geschlecht} == 1) {
      $pre_data{'gender'} = 'm';
    } elsif ($row_user_old->{geschlecht} == 2) {
      $pre_data{'gender'} = 'f';
    } else {
      $pre_data{'gender'} = '';
    }
    # fix for wrong birthdates
    if ($row_user_old->{gebdatum2} eq "0000-00-00") {
      $pre_data{'birthdate'} = "0001-01-01";
    } elsif ($row_user_old->{gebdatum2} eq "1981-11-31") {
      $pre_data{'birthdate'} = "0001-01-01";
    } elsif ($row_user_old->{gebdatum2} eq "1982-02-31") {
      $pre_data{'birthdate'} = "0001-01-01";
    } elsif ($row_user_old->{gebdatum2} eq "1981-02-31") {
      $pre_data{'birthdate'} = "0001-01-01";
    } elsif ($row_user_old->{gebdatum2} eq "1980-02-30") {
      $pre_data{'birthdate'} = "0001-01-01";
    } else {
      $pre_data{'birthdate'} = $row_user_old->{gebdatum2};
    }
    if ($row_user_old->{email_public} =~ /^.+\@.+$/) {
      $pre_data{'public_email'} = $row_user_old->{email_public};
    } else {
      $pre_data{'public_email'} = "";
    }
    $row_user_old->{uniemail} =~ s/[\r\n]//gs;
    if ($row_user_old->{uniemail} =~ /^.+\@.+$/) {
      $pre_data{'uni_email'} = $row_user_old->{uniemail};
    } else {
      #print STDERR "\nwrong email address for uni:\n|" . $row_user_old->{username} . "|  |" . $row_user_old->{uniemail} . "|\n";
      next;
    }
    $st_user_new->bind_param(1, $row_user_old->{username}, {'pg_type' => PG_VARCHAR});
    $st_user_new->bind_param(2, 'FALSE', {'pg_type' => PG_BOOL});
    $st_user_new->bind_param(3, 'TRUE', {'pg_type' => PG_BOOL});
    $st_user_new->bind_param(4, 'FALSE', {'pg_type' => PG_BOOL});
    $st_user_new->bind_param(5, $pre_data{'uni'}, {'pg_type' => PG_INT4});
    $st_user_new->bind_param(6, $pre_data{'person_type'}, {'pg_type' => PG_INT4});
    $st_user_new->bind_param(7, '', {'pg_type' => PG_VARCHAR});
    $st_user_new->bind_param(8, '', {'pg_type' => PG_VARCHAR});
    $st_user_new->bind_param(9, '', {'pg_type' => PG_VARCHAR});
    $st_user_new->bind_param(10, '', {'pg_type' => PG_VARCHAR});
    $st_user_new->bind_param(11, '', {'pg_type' => PG_VARCHAR});
    $st_user_new->bind_param(12, 0, {'pg_type' => PG_INT4});
    $st_user_new->bind_param(13, '', {'pg_type' => PG_VARCHAR});
    $st_user_new->bind_param(14, '', {'pg_type' => PG_VARCHAR});
    $st_user_new->bind_param(15, 1, {'pg_type' => PG_INT2});
    $st_user_new->bind_param(16, 1, {'pg_type' => PG_INT2});
    $st_user_new->bind_param(17, '', {'pg_type' => PG_VARCHAR});
    $st_user_new->bind_param(18, $row_user_old->{mobile}, {'pg_type' => PG_VARCHAR});
    $st_user_new->bind_param(19, '', {'pg_type' => PG_VARCHAR});
    $st_user_new->bind_param(20, $pre_data{'public_email'}, {'pg_type' => PG_VARCHAR});
    $st_user_new->bind_param(21, '', {'pg_type' => PG_VARCHAR});
    $st_user_new->bind_param(22, $pre_data{'uni_email'}, {'pg_type' => PG_VARCHAR});
    $st_user_new->bind_param(23, '', {'pg_type' => PG_VARCHAR});
    $st_user_new->bind_param(24, $row_user_old->{signature}, {'pg_type' => PG_TEXT});
    $st_user_new->bind_param(25, '', {'pg_type' => PG_TEXT});
    $st_user_new->bind_param(26, $row_user_old->{beschreibung}, {'pg_type' => PG_TEXT});
    $st_user_new->bind_param(27, $pre_data{'gender'}, {'pg_type' => PG_VARCHAR});
    $st_user_new->bind_param(28, $pre_data{'birthdate'}, {'pg_type' => PG_DATE});
    $st_user_new->bind_param(29, (($row_user_old->{gebdatum_public} == 1) ? "TRUE" : "FALSE"), {'pg_type' => PG_BOOL});
    $st_user_new->bind_param(30, $row_user_old->{pass}, {'pg_type' => PG_VARCHAR});
    if (!$st_user_new->execute) {
      print STDERR "failed!\n";
      print STDERR "username: " . $row_user_old->{username} . "\n";
      print STDERR "$DBI::err\n$DBI::errstr\n";
      $new_db->rollback;
      exit(1);
    }
  }
  $new_db->commit;
  print STDERR "\033[1Ginsert user data ... " . " " x 60;
  print STDERR "\033[1Ginsert user data ... " . $st_user_old->rows . " OK\n";
  $st_user_old->finish;
} # end if ($ARGV[0] eq "user")


######################################################################
# guestbook data
if (defined($ARGV[0]) and $ARGV[0] eq "gb") {
  shift(@ARGV);
  print STDERR "getting old guestbook data ... ";
  my ($query_gb_old, $st_gb_old, $row_gb_old, $row_gb_count_1, $row_gb_count_2);
  my ($query_gb_new, $st_gb_new, $row_gb_new, $st_gb_new_prep);
  $query_gb_old = "SELECT *
                     FROM unihelp_gaestebuch
                    WHERE time > 1000000000
                 ORDER BY id";
  $st_gb_old = $old_db->prepare($query_gb_old);
  if (!$st_gb_old->execute) {
    print STDERR "failed!\n";
    print STDERR "$DBI::err\n$DBI::errstr\n";
    exit(1);
  }
  print STDERR "OK\n";

  print STDERR "write guestbook data ... ";
  $row_gb_count_1 = 0;
  $row_gb_count_2 = 0;
  $query_gb_new = "INSERT INTO $new_db_schema.guestbook
                               (user_id_for,
                                user_id_from,
                                username_from,
                                unihelp_server_from,
                                entry_time,
                                entry,
                                weighting,
                                post_ip)
                         VALUES (?, ?, ?, ?, TIMESTAMPTZ 'EPOCH' + ?::INTEGER * INTERVAL '1 SECOND', ?, ?, ?)";
  $st_gb_new_prep = $new_db->prepare($query_gb_new);
  $new_db->begin_work;
  my ($seconds, $microseconds) = gettimeofday();
  while ($row_gb_old = $st_gb_old->fetchrow_hashref) {
    # output username and count
    $row_gb_count_1++;
    # we need to lookup the user_id
    my $to_id = get_user_id($row_gb_old->{username});
    if ($to_id == 0) {
      next;
    }
    my $from_id = get_user_id($row_gb_old->{autor});
    if ($from_id == 0) {
      $pre_data{'author'} = "unknown";
    } else {
      $pre_data{'author'} = $row_gb_old->{autor};
    }
    $pre_data{'user_id_to'} = $to_id;
    $pre_data{'user_id_from'} = $from_id;
    $st_gb_new_prep->bind_param(1, $pre_data{'user_id_to'}, {'pg_type' => PG_INT8});
    $st_gb_new_prep->bind_param(2, $pre_data{'user_id_from'}, {'pg_type' => PG_INT8});
    $st_gb_new_prep->bind_param(3, $pre_data{'author'}, {'pg_type' => PG_VARCHAR});
    $st_gb_new_prep->bind_param(4, $pre_data{'uni'}, {'pg_type' => PG_INT4});
    #$st_gb_new_prep->bind_param(5, '2005-09-14 18:03:29.739735');
    $st_gb_new_prep->bind_param(5, $row_gb_old->{time}, {'pg_type' => PG_INT4});
    $st_gb_new_prep->bind_param(6, $row_gb_old->{eintrag}, {'pg_type' => PG_TEXT});
    $st_gb_new_prep->bind_param(7, $row_gb_old->{bewertung}, {'pg_type' => PG_INT4});
    $st_gb_new_prep->bind_param(8, $row_gb_old->{postip}, {'pg_type' => PG_VARCHAR});
    if (!$st_gb_new_prep->execute) {
      print STDERR "failed!\n";
      print STDERR "id: " . $row_gb_old->{id} . "\n";
      print STDERR "$DBI::err\n$DBI::errstr\n";
      $new_db->rollback;
      exit(1);
    }
    $row_gb_count_2++;
    # commit every x entries
    if (($row_gb_count_2 / 1000) == int($row_gb_count_2 / 1000)) {
      # calculate remind time
      my $interval = tv_interval([$seconds, $microseconds]);
      my $remind_time = int(($st_gb_old->rows * $interval) / $row_gb_count_1);
      $remind_time = $remind_time - $interval;
      if ($remind_time < 0) {
        $remind_time = 0;
      }
      my $remind_time_h = int($remind_time / 3600);
      $remind_time = $remind_time - $remind_time_h * 3600;
      my $remind_time_m = int($remind_time / 60);
      $remind_time = $remind_time - $remind_time_m * 60;
      print STDERR sprintf("\033[1Ginsert guestbook data ... %10d/%10d (%02d:%02d:%02d)", $row_gb_count_1,
                                                                                          $st_gb_old->rows,
                                                                                          $remind_time_h,
                                                                                          $remind_time_m,
                                                                                          $remind_time);
      $new_db->commit;
      $new_db->begin_work;
    }
  }
  $st_gb_old->finish;
  $new_db->commit;
  print STDERR "\033[1Ginsert guestbook data ... " . " " x 60;
  print STDERR "\033[1Ginsert guestbook data ($row_gb_count_2 of $row_gb_count_1) ... OK\n";
} # end if ($ARGV[0] eq "gb")


######################################################################
# friends
if (defined($ARGV[0]) and $ARGV[0] eq "friends") {
  shift(@ARGV);
  print STDERR "getting old friends data ... ";
  my ($query_friends_old, $st_friends_old, $row_friends_old, $row_friends_count);
  my ($query_friends_new, $st_friends_new, $row_friends_new);
  $query_friends_old = "SELECT *
                          FROM unihelp_friends
                      ORDER BY LOWER(username)";
  $st_friends_old = $old_db->prepare($query_friends_old);
  if (!$st_friends_old->execute) {
    print STDERR "failed!\n";
    print STDERR "$DBI::err\n$DBI::errstr\n";
    exit(1);
  }
  print STDERR "OK\n";

  print STDERR "write friends data ... ";
  $row_friends_count = 0;
  $query_friends_new = "INSERT INTO $new_db_schema.friends
                                    (user_id, friend_id, friend_type)
                             VALUES (?, ?, (SELECT id
                                              FROM public.friend_types
                                             WHERE type_name='Normal'))";
  $st_friends_new = $new_db->prepare($query_friends_new);

  $new_db->begin_work;
  my (@friends_split, %friends_split, $friends_user, $friends_user_id);
  while ($row_friends_old = $st_friends_old->fetchrow_hashref) {
    if (length($row_friends_old->{friends}) < 1) {
      next;
    }
    @friends_split = split(/;/, $row_friends_old->{friends});
    %friends_split = ();
    foreach $friends_user (@friends_split) {
      $friends_split{$friends_user} = 1;
    }
    print STDERR sprintf("\033[1Ginsert friends data ... %s (%d)%s", $row_friends_old->{username},
                                                                      $#friends_split,
                                                                      " " x (60 - length($row_friends_old->{username})));

    # get user id
    my $user_id = get_user_id($row_friends_old->{username});
    if ($user_id == 0) {
      next;
    }

    foreach $friends_user (sort(keys(%friends_split))) {
      $friends_user_id = get_user_id($friends_user);
      if ($friends_user_id == 0) {
        next;
      }

      # insert data
      $st_friends_new->bind_param(1, $user_id, {'pg_type' => PG_INT8});
      $st_friends_new->bind_param(2, $friends_user_id, {'pg_type' => PG_INT8});
      if (!$st_friends_new->execute) {
        print STDERR "failed!\n";
        print STDERR "username: " . $row_friends_old->{username} . "\n";
        print STDERR "$DBI::err\n$DBI::errstr\n";
        $new_db->rollback;
        exit(1);
      }
      $row_friends_count++;
    }
  }

  $new_db->commit;
  print STDERR "\033[1Ginsert friends data ... " . " " x 60;
  print STDERR "\033[1Ginsert friends data ... " . $row_friends_count . " OK\n";
  $st_friends_old->finish;
} # end if ($ARGV[0] eq "friends")









$old_db->disconnect();
$new_db->disconnect();




sub get_user_id {
  my $user = shift;

  my $user_low = lc($user);

  if (defined($main::user_id{$user_low})) {
    # return the user id
    return $main::user_id{$user_low};
  }

  # lookup the user id
  my ($query, $st, $row);
  $query = "SELECT id
              FROM $new_db_schema.users
             WHERE LOWER(username)=" . $new_db->quote($user_low) . "";
  $st = $new_db->prepare($query);
  if (!$st->execute) {
    print STDERR "failed!\n";
    print STDERR "$DBI::err\n$DBI::errstr\n";
    exit(1);
  }
  if ($st->rows > 1) {
    # database error
    print STDERR "more then one userid (" . $user . ")!\n";
    exit;
  }
  if ($st->rows == 1) {
    # user found
    $row = $st->fetchrow_hashref;
    $main::user_id{$user_low} = $row->{id};
    return $row->{id};
  }

  # user not found, set id = 0
  $main::user_id{$user_low} = 0;
  return 0;
}


