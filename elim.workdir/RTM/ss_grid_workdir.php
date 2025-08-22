<?php
// $Id: 3ef8bed32b33e029d8b568e4b861e82f5df768c2 $
/*
 +-------------------------------------------------------------------------+
 | Copyright IBM Corp. 2006, 2022                                          |
 |                                                                         |
 | Licensed under the Apache License, Version 2.0 (the "License");         |
 | you may not use this file except in compliance with the License.        |
 | You may obtain a copy of the License at                                 |
 |                                                                         |
 | http://www.apache.org/licenses/LICENSE-2.0                              |
 |                                                                         |
 | Unless required by applicable law or agreed to in writing, software     |
 | distributed under the License is distributed on an "AS IS" BASIS,       |
 | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.|
 | See the License for the specific language governing permissions and     |
 | limitations under the License.                                          |
 +-------------------------------------------------------------------------+
*/

error_reporting(0);

if (!isset($called_by_script_server)) {
	include(dirname(__FILE__) . '/../include/cli_check.php');
	include_once(dirname(__FILE__) . '/../lib/functions.php');
	array_shift($_SERVER['argv']);
	print call_user_func_array('ss_grid_workdir', $_SERVER['argv']);
}

function ss_grid_workdir($clusterid = 0) {
	global $config;

	$data = db_fetch_assoc_prepared('SELECT `resource_name`, `value`
		FROM grid_hosts_resources
		WHERE clusterid = ?
		AND resource_name IN ("workdirAvail", "workdirTotal", "workdirUsed")',
		array($clusterid));

	if (cacti_sizeof($data)) {
		$wdstats = array_rekey($data, 'resource_name', 'value');

		$stats = "workdirTotal:{$wdstats['workdirTotal']} workdirUsed:{$wdstats['workdirUsed']} workdirAvail:{$wdstats['workdirAvail']}";
	} else {
		$stats = "workdirTotal:0 workdirUsed:0 workdirAvail:0";
	}

	return $stats;
}
