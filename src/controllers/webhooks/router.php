<?php

$this->any('/sumpayments', function() {
	global $link;
	require 'sumpayments.php';
});

$this->any('/chargeusers', function() {
	global $link;
	require 'chargeusers.php';
});

$this->any('/retrypayments', function() {
	require 'retry-payments.php';
});

$this->any('/notifysend', function() {
	global $link;
  global $db;
	//echo "Service Suspended";
	require 'SingleEmailHandler.php';
});

$this->any('/newnotifysend', function() {
	require 'notifyhandler.php';
});

$this->any('/updatesquadmembers', function() {
	global $db, $link;
	require 'squadmemberupdate.php';
});

$this->any('/updateregisterweeks', function() {
	global $db, $link;
	require 'newWeek.php';
});

$this->any('/timeupdate', function() {
	global $db, $link;
	require 'getTimesNew.php';
});

/*$this->any('/timeupdatenew', function() {
	global $db, $link;
	require 'getTimesNew.php';
});*/
