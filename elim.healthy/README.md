# UNIX/Linux Host Health Check ELIM for LSF

## Description

The ELIM included in this directory reports the health status of the 
compute host to LSF.  There is a script included called healthcheck.pl
that can be modified to include you various checks and extended
using the simple perl programming language.

This is mainly meant for reference, and begs for the LSF Administrator
to tune the script for their usage.

## Use Cases

The elim.healhty ELIM is mainly used to prevent jobs from dispatching
to hosts with known health issues like hung mount points, automounter
creashing or not responding, file systems out of space, load average
issues, even errors coming into /var/log/(messages|syslog) like
ECC errors, DNS issues, etc.

The output of the ELIM can be used for many purposes including:

1) Reporting in tools like RTM using ELIM Templates

2) Excluding unhealthy execution hosts from job dispatch

For example, you can do the following:

	bsub -R "select[healthy == ok]" ./a.out

To always dispatch to a healthy host.  Putting this at the queue
level is always the best as it will always dictate the dispatch
decision.

Review the comments and arguments in both the elim.healthy
and healthcheck.pl for more information on the usage of the script.

## Installation Instructions

To install this ELIM, you must first add the the numeric resource to LSF as 
per the normal process which involves updating your lsf.shared and lsf.cluster 
files to include the values.  Ensure that you assign this resource to hosts 
your UNIX/Linux hosts.

Then, before restarting the cluster, make sure that the elim.healthy binary has been 
copied to the $LSF_SERVERDIR for all compute hosts and marked executable.  
Additionally, you can control the frequency of health checks by the SLEEP_TIME
variable in elim.healthy script.

After which, you can restart your cluster using:

	lsadmin reconfig
	badmin reconfig

Make sure you restart all LIM's and not just the Master LIM.  From each compute 
host, you should then see the binary running in the background.  If not, you 
should debug the binary interactively using simply by running from the command 
line on your hosts.

You can also get the context switches per second of your hosts by running the 
following lsload command:

	lsload -o "HOST_NAME healthy" -json

## Community Contribution Requirements

Community contributions to this repository must follow the [IBM Developer's Certificate of Origin (DCO)](https://github.com/IBMSpectrumComputing/platform-python-lsf-api/blob/master/IBMDCO.md) process and only through GitHub Pull Requests:

 1. Contributor proposes new code to community.

 2. Contributor signs off on contributions 
    (i.e. attachs the DCO to ensure contributor is either the code 
    originator or has rights to publish. The template of the DCO is included in
    this package).
 
 3. IBM Spectrum LSF development reviews contribution to check for:
    i)  Applicability and relevancy of functional content 
    ii) Any obvious issues

 4. If accepted, posts contribution. If rejected, work goes back to contributor and is not merged.

## Copyright

(C) Copyright IBM Corporation 2016-2025

U.S. Government Users Restricted Rights - Use, duplication or disclosure 
restricted by GSA ADP Schedule Contract with IBM Corp.

IBM(R), the IBM logo and ibm.com(R) are trademarks of International Business Machines Corp., 
registered in many jurisdictions worldwide. Other product and service names might be trademarks 
of IBM or other companies. A current list of IBM trademarks is available on the Web at 
"Copyright and trademark information" at [IBM Legal](www.ibm.com/legal/copytrade.shtml).

