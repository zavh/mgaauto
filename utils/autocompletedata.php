<?php
	$corpobj =  new DbTables($con, 'corporate');
	$corporates = $corpobj->idToField('corporate_id', 'corporate_name');
	$corpjvar = "\"".implode("\",\"",$corporates['corporate_name'])."\"";

	$bankobj = new DbTables($con, 'bank');
	$banks = $bankobj->idToField('bank_id', 'bank_code');
	$bankjvar = "\"".implode("\",\"",$banks['bank_code'])."\"";
?>