#!/usr/bin/env perl 
# -----------------------------------------------------------------------------
# Name:        LSF Healthcheck ELIM Script
#
# Description: This script is design to check various OS states and if the
#              OS is having a problem, then report that back to LSF so that
#              no jobs will launch on the host.  The way you do that is at the
#              LSF Queue level, add a RES_RES of "select[healthy]" to each
#              Queue.
#
# Usage:       You need to all this scrip from within the elim.healthy
#              script.  If the healthcheck returns all 1's, then the host
#              will be healthy.
#
#              For file system checks, the healthcheck script uses the perl
#              alarm facility to break out of the command and report a hung
#              status to LSF.  So, in the case that the "df" command
#              hangs, the elim will still be able to report a bad health
#              to LSF.
#
# Options:     --all        Run all tests.  Recommended
#              --log=F      Write log messages to this file
#              --timeout=N  Timeout each test after X seconds
# -----------------------------------------------------------------------------
use Getopt::Long; 

sub error {
  my($mess) = @_;
  die "HEALTH Error: $mess\n";
};

GetOptions(\%opt,
    'v!',
    'all!',
    'log:s',
    'timeout=i',
) or error "Bad option use";

$opt{timeout} ||= 15;

$exit     = 0;
$error    = ''; 
$os       = `/usr/bin/uname -s`; chomp $os;
$hostname = `hostname`; chomp $hostname; $hostname =~ s/\..*$//;
$df_flags = '-Pk';
$logdir   = '/tmp/';

%checklist = ( 
    # 1 and more - good status, 0 and less - bad
    perl     => sub { -x "/usr/bin/perl"                                                        },
    vartmp   => sub { ! system( "/bin/touch /var/tmp/hi.touch > /dev/null 2>\&1" )              },
    home     => sub { chdir "$ENV{HOME}"; -d "$ENV{HOME}"                                       },
    pwd      => sub { exists $ENV{PWD}                  && -d $ENV{PWD}                         },
    nobackup => $opt{all} ? \&checkDiskSpace : \&empty,
    tmp      => $opt{all} ? \&checkTmp : \&empty,
    load     => \&checkLoadAverage,
    autofs   => \&checkAutomounter,
);

# -----------------------------------------------------------------------------
# Mainline
# -----------------------------------------------------------------------------

# -----------------------------------------------------------------------------
# if logging, open the various log files
# -----------------------------------------------------------------------------
if ($opt{log}) {
  open(LOG, ">$opt{log}") or error("Cannot open log file $opt{log}");
  open(LOG_EXT, "> $logdir/${hostname}.err") or error("Cannot open log file $logdir/${hostname}.err"); 
}

# -----------------------------------------------------------------------------
# run each check and get the overall status.  Higher the status the more
# issues that the host has present.
# -----------------------------------------------------------------------------
foreach (keys %checklist) {
    my $return = &runCommand($checklist{$_});

    if (defined $return) {
        if ($return > 0) { 
            printf         "%-20s -> OK    (%5d)\n",    $_, $return          if ($opt{v});
        } else {
            printf         "%-20s -> Error (%5d) %s\n", $_, $return, $error  if ($opt{v});
            printf LOG     "%-20s -> Error (%5d) %s\n", $_, $return, $error  if ($opt{log});
            printf LOG_EXT "%-20s (%5d),\n",            $_, $return          if ($opt{log});

            $exit++;
        }
    } else {
        printf         "%-20s -> Error (undef)\n", $_  if ($opt{v});
        printf LOG     "%-20s -> Error (undef)\n", $_  if ($opt{log});
        printf LOG_EXT "%-20s,\n",                 $_  if ($opt{log});

        $exit++;
    }
}

if ($opt{log}) {
    close LOG;
    close LOG_EXT;
}

print " *** Exit HEALTH: $exit ***\n" if ($opt{v}); 
exit ($exit);

# -----------------------------------------------------------------------------
# functions below this line
# -----------------------------------------------------------------------------

sub empty {
  return 1;
};

sub checkTmp {
    my $dir  = "/tmp";
    my $percent = 90;
    
    my $used = `df $df_flags $dir | grep -v File | awk '{print \$5}'| tr -d '%'`; chomp $used;

    if ( $used =~ /^\d+$/ && $used < $percent ) { 
        return 1;
    } else { 
        my $used = `df $df_flags $dir | grep -v File | awk '{print \$5}' | tr -d '%'`; chomp $used;

        if ( $used =~ /^\d+$/ && $used < $percent ) {
            return 1;
        }

        return -2;
    }
} 

sub checkAutomounter {
    # --------------------------------------------------------
    # check that the automounter is running.
    # --------------------------------------------------------
    my $autofs = `/bin/ps -e | grep -v grep | grep automou | /usr/bin/wc -l`; chomp $autofs;

    if ( $autofs > 0 ) {
        return 1;
    } else { 
        return -1;
    }
}

sub checkDiskSpace { 
    # --------------------------------------------------------
    # The directory to check.  You could pass this to the
    # function as well.
    # --------------------------------------------------------
    my $dir  = "/nobackup";

    # --------------------------------------------------------
    # The amount of free space required on this disk
    # --------------------------------------------------------
    my $need = 1500000;
    
    # --------------------------------------------------------
    # check if the directory exists
    # --------------------------------------------------------
    return -1 unless ( -d $dir || -l $dir );

    # --------------------------------------------------------
    # check permissions on the directory
    # --------------------------------------------------------
    my $link = $dir;

    if (-l $dir) {
        $link = readlink("$dir");
    }

    my $mode = (stat($link))[2];
    my $perm = sprintf ("%04o", $mode & 07777);

    if ($perm ne '0777') {
        `chmod 777 $link`;

        if ( $? > 0 ) {
            return -4;
        }
    }

    # --------------------------------------------------------
    # check if the directory is writable
    # --------------------------------------------------------
    `mkdir ${dir}/${hostname}.$$`;
	$exit  = $?;

    `rmdir ${dir}/${hostname}.$$`;
	$exit += $?;
    
    return -2 unless ($exit == 0);
    
    # --------------------------------------------------------
    # check if the directory has enough free space
    # --------------------------------------------------------
    my $free  = `df $df_flags $dir | grep -v File | awk '{print \$4}'`; chomp $free;
    my $total = `df $df_flags $dir | grep -v File | awk '{print \$2}'`; chomp $total; 

    # --------------------------------------------------------
    # this should not be possible, but for some older OS' it
    # is.
    # --------------------------------------------------------
    if ($free > $total) { 
        return -5;
    }

    # --------------------------------------------------------
    # make sure the free space is enough
    # --------------------------------------------------------
    if ($free =~ /^\d+$/ && $free > $need) {
        return 1;
    } else {
        return -3;
    }
}

sub checkLoadAverage {
    my $la = 0;

    # --------------------------------------------------------
    # get the number of processors to check the load average
    # --------------------------------------------------------
	my $nproc = `nproc --all`;chomp $nproc;

    ($la) = `/usr/bin/uptime` =~ /(\d+\.\d+)/;

    # --------------------------------------------------------
    # a load average below 1.5 is good
    # --------------------------------------------------------
    if ($la < $nproc * 1.5) {
        return 1;
    } else {
        return 0;
    }
}

sub runCommand {
    my ($cmd) = @_;
    my $read;

    # --------------------------------------------------------
    # execute the command or function using an alarm
    # so that we can break out of it if the command hangs.
    # --------------------------------------------------------
    eval {
        local $SIG{ALRM} = sub { die "timeout\n" };
        alarm $opt{timeout};
        $read = &$cmd;
        alarm 0;
    };

    # --------------------------------------------------------
    # check for a timeout, if not return what was read
    # --------------------------------------------------------
    if ($@) {
        $error = $@;

        if ($@ eq "timeout\n") { 
            return -1 
        } else { 
            return -2 
        }
    } else {
        return $read;
    }
}
