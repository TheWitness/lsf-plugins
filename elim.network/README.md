# UNIX/Linux Network In/Out Bytes per Second ELIM for LSF

## Description

The ELIM included in this directory reports an hosts inbound and outbound bytes 
per second to LSF.  This data can be used visually track host network traffic in 
LSF RTM via it's ELIM Template graphs or through custom developed Device Level 
Graph Templates.

## Use Cases

The Inbound and Outbound Bytes ELIM can be used for the following purposes:

1) Reporting in tools like RTM using ELIM or Classic Graph Templates

2) Sorting of devices to be used for dispatch, or to select hosts with a 
   specific number of bytes per second range.  

For example, you can do the following:

	bsub -R "order[netInMax]" ./a.out

Which would dispatch to the host with the lowest number of inbound bytes per 
second.  Additionally, you could do the following:

	bsub -R "select[netInAvg<1000]" ./a.out

The bsub above would only select hosts that have an average inbound network 
average of less than 1000 bytes per second to dispatch to.

## Installation Instructions

To install this ELIM, you must first add the the numeric resource to LSF as per 
the normal process which involves updating your lsf.shared and lsf.cluster files 
to include the values.  Ensure that you assign this resource to hosts your 
UNIX/Linux hosts.

The numeric resources to add are shown in the example lsf.shared file and 
lsf.cluster example files included in this repo.  They include:

netInMin  = The minumum inbound bytes per second in the reporting period
netInMax  = The maximum inbound bytes per second in the reporting period
netInAvg  = The average inbound bytes per second in the reporting period
netOutMin = The minumum outbound bytes per second in the reporting period
netOutMax = The maximum outbound bytes per second in the reporting period
netOutAvg = The average outbound bytes per second in the reporting period

Then, before restarting the cluster, make sure that the elim.network binary has 
been copied to the $LSF_SERVERDIR for all compute hosts and marked executable.  
Additionally, there are two variables in the elim.css that can be modified 
that affect the granularity of the data.  They are:

sleep_time = 2 seconds, the time between checks of the data
report_time = 60 seconds, the time between reporting min, max, and average data to LSF

If you change either of these settings, make sure that you update the lsf.shared 
file to reflect the new report_time setting.

After which, you can restart your cluster using:

	lsadmin reconfig
	badmin reconfig

Make sure you restart all LIM's and not just the Master LIM.  From each compute 
host, you should then see the binary running in the background.  If not, you 
should debug the binary interactively using simply by running from the command 
line on your hosts.

You can also get the bytes per second of your hosts by running the 
following lsload command:

	lsload -o "netInAvg netInMin netInMax netOutAvg netOutMin netOUtMax" -json

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

(C) Copyright IBM Corporation 2016-2019

U.S. Government Users Restricted Rights - Use, duplication or disclosure 
restricted by GSA ADP Schedule Contract with IBM Corp.

IBM(R), the IBM logo and ibm.com(R) are trademarks of International Business Machines Corp., 
registered in many jurisdictions worldwide. Other product and service names might be trademarks 
of IBM or other companies. A current list of IBM trademarks is available on the Web at 
"Copyright and trademark information" at [IBM Legal](www.ibm.com/legal/copytrade.shtml).

