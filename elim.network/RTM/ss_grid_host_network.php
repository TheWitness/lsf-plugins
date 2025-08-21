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
	print call_user_func_array('ss_grid_host_network', $_SERVER['argv']);
}

function ss_grid_host_network($hostname = '', $clusterid = 0, $summary = 'no') {
	if ($hostname == '' || $hostname == 'localhost' || $summary == 'yes') {
		if ($clusterid == 0) {
			$stats = db_fetch_row("SELECT " . SQL_NO_CACHE . "
				SUM(CASE WHEN resource_name='netInAvg'  THEN totalValue ELSE 0 END) AS netInAvg,
				SUM(CASE WHEN resource_name='netInMax'  THEN totalValue ELSE 0 END) AS netInMax,
				SUM(CASE WHEN resource_name='netOutAvg' THEN totalValue ELSE 0 END) AS netOutAvg,
				SUM(CASE WHEN resource_name='netOutMax' THEN totalValue ELSE 0 END) AS netOutMax
				FROM grid_hosts_resources
				WHERE resource_name IN ('netInAvg', 'netInMax', 'netOutAvg', 'netOutMax')");
		} else {
			$stats = db_fetch_row_prepared("SELECT " . SQL_NO_CACHE . "
				SUM(CASE WHEN resource_name='netInAvg'  THEN totalValue ELSE 0 END) AS netInAvg,
				SUM(CASE WHEN resource_name='netInMax'  THEN totalValue ELSE 0 END) AS netInMax,
				SUM(CASE WHEN resource_name='netOutAvg' THEN totalValue ELSE 0 END) AS netOutAvg,
				SUM(CASE WHEN resource_name='netOutMax' THEN totalValue ELSE 0 END) AS netOutMax
				FROM grid_hosts_resources
				WHERE resource_name IN ('netInAvg', 'netInMax', 'netOutAvg', 'netOutMax')
				AND clusterid = ?",
				array($clusterid));
		}
	} else {
		if ($clusterid == 0) {
			$stats = db_fetch_row_prepared("SELECT " . SQL_NO_CACHE . "
				SUM(CASE WHEN resource_name='netInAvg'  THEN totalValue ELSE 0 END) AS netInAvg,
				SUM(CASE WHEN resource_name='netInMax'  THEN totalValue ELSE 0 END) AS netInMax,
				SUM(CASE WHEN resource_name='netOutAvg' THEN totalValue ELSE 0 END) AS netOutAvg,
				SUM(CASE WHEN resource_name='netOutMax' THEN totalValue ELSE 0 END) AS netOutMax
				FROM grid_hosts_resources
				WHERE resource_name IN ('netInAvg', 'netInMax', 'netOutAvg', 'netOutMax')
				AND host = ?",
				array($hostname));
		} else {
			$stats = db_fetch_row_prepared("SELECT " . SQL_NO_CACHE . "
				SUM(CASE WHEN resource_name='netInAvg'  THEN totalValue ELSE 0 END) AS netInAvg,
				SUM(CASE WHEN resource_name='netInMax'  THEN totalValue ELSE 0 END) AS netInMax,
				SUM(CASE WHEN resource_name='netOutAvg' THEN totalValue ELSE 0 END) AS netOutAvg,
				SUM(CASE WHEN resource_name='netOutMax' THEN totalValue ELSE 0 END) AS netOutMax
				FROM grid_hosts_resources
				WHERE resource_name IN ('netInAvg', 'netInMax', 'netOutAvg', 'netOutMax')
				AND clusterid = ?
				AND host = ?",
				array($clusterid, $hostname));
		}
	}

	if (empty($stats['netInAvg'])) {
		$stats['netInAvg']  = 0;
	}

	if (empty($stats['netInMax'])) {
		$stats['netInMax']  = 0;
	}

	if (empty($stats['netOutAvg'])) {
		$stats['netOutAvg'] = 0;
	}

	if (empty($stats['netOutMax'])) {
		$stats['netOutMax'] = 0;
	}

	$result =
		'netInAvg:'  . ss_grid_host_network_value($stats['netInAvg'])  . ' ' .
		'netInMax:'  . ss_grid_host_network_value($stats['netInMax'])  . ' ' .
		'netOutAvg:' . ss_grid_host_network_value($stats['netOutAvg']) . ' ' .
		'netOutMax:' . ss_grid_host_network_value($stats['netOutMax']);

	return trim($result);
}

function ss_grid_host_network_value($value) {
	if ($value == '') {
    	$value = 0;
	}

	return $value;
}

