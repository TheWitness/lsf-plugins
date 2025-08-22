# LSF Work Directory Space Report ELIM

## Description

The ELIM included in this directory reports the total space in the LSF_WORKDIR
the free space and the available space.  This data is used to report on
the free space in LSF as a shared resource that can then be used as a quick
way to report free space in RTM.

## Use Cases

Really, this ELIM should be used for Graphing in RTM, added to the LSF cluster
device template and then have a Threshold added to it to notify LSF
Administrators of work directories that are running out of space.  It's
not an ELIM that should be used for any reservations or any other purpose.

## Installation Instructions

To install this ELIM, you must first add the the numeric resources to LSF as per
the normal process which involves updating your lsf.shared and lsf.cluster files
to include the values.  Make sure you use the 'All' hosts mapping in the
lsf.cluster file.

The numeric resources to add are shown in the example lsf.shared file and
lsf.cluster example files included in this repo.  They include:

workdirTotal = The total size of the volume in GBytes
workdirAvail = The available size of the volume in GBytes
workdirUsed  = The used size of the volume in GBytes

Then, before restarting the cluster, make sure that the elim.workdir binary
has been copied to the $LSF_SERVERDIR and marked executable.

After which, you can restart your cluster using:

	lsadmin reconfig
	badmin mbdrestart

You only have to restart the Master LIM's and of course, the badmin mbdrestart.

## Community Contribution Requirements

Community contributions to this repository must follow the [IBM Developer's Certificate of Origin (DCO)](https://github.com/IBMSpectrumComputing/platform-python-lsf-api/blob/master/IBMDCO.md)
process and only through GitHub Pull Requests:

 1. Contributor proposes new code to community.

 2. Contributor signs off on contributions
    (i.e. attachs the DCO to ensure contributor is either the code
    originator or has rights to publish. The template of the DCO is included in
    this package).

 3. IBM Spectrum LSF development reviews contribution to check for:
    i)  Applicability and relevancy of functional content
    ii) Any obvious issues

 4. If accepted, posts contribution. If rejected, work goes back to contributor
    and is not merged.

## Copyright

I freely offer this software to LSF users everywhere and assign rights to IBM.
