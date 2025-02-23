<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2025 The Cacti Group                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDtool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

error_reporting(0);

if (!isset($called_by_script_server)) {
	include(__DIR__ . '/../include/cli_check.php');
	include_once(__DIR__ . '/../lib/functions.php');
	array_shift($_SERVER['argv']);
	print call_user_func_array('ss_grid_host_css', $_SERVER['argv']);
}

function ss_grid_host_css($hostname = '', $clusterid = 0) {
	$rstats = db_fetch_assoc_prepared("SELECT " . SQL_NO_CACHE . " resource_name, totalValue
		FROM grid_hosts_resources
		WHERE resource_name LIKE 'css%'
		AND host = ?
		AND clusterid = ?",
		array($hostname, $clusterid));

	/* initialize variables */
	$cssMin = 0;
	$cssMax = 0;
	$cssAvg = 0;
	$result = 'cssMin:0 cssMax:0 cssAvg:0';

	if (cacti_sizeof($rstats)) {
		foreach($rstats as $stat) {
			if (substr_count($stat['resource_name'], 'cssMin')) {
				$cssMin = round($stat['totalValue'],0);
			} elseif (substr_count($stat['resource_name'], 'cssMax')) {
				$cssMax = round($stat['totalValue'],0);
			} elseif (substr_count($stat['resource_name'], 'cssAvg')) {
				$cssAvg = round($stat['totalValue'],0);
			}
		}

		$result = 'cssMin:' . $cssMin . ' cssMax:' . $cssMax . ' cssAvg:' . $cssAvg;
	}

	return $result;
}
