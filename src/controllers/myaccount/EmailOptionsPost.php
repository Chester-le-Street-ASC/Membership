<?php

use Respect\Validation\Validator as v;
$db = app()->db;
$currentUser = app()->user;

$sql = "SELECT `EmailAddress`, `EmailComms` FROM `users` WHERE `UserID` = ?";
try {
	$query = $db->prepare($sql);
	$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
} catch (Exception $e) {
	halt(404);
}
$row = $query->fetch(PDO::FETCH_ASSOC);

// Normal Emails
$email_comms = false;
$email_comms_update = false;
if ($_POST['EmailComms']) {
	$email_comms = true;
}

if ($email_comms != $row['EmailComms']) {
	$email_comms_update = true;
	$_SESSION['TENANT-' . app()->tenant->getId()]['OptionsUpdate'] = true;
	$emailCommsDb = (int) $email_comms;
  $sql = "UPDATE `users` SET `EmailComms` = ? WHERE `UserID` = ?";
  try {
  	$db->prepare($sql)->execute([$emailCommsDb, $_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
  } catch (Exception $e) {
		// Could not update settings
		$_SESSION['TENANT-' . app()->tenant->getId()]['EmailUpdateError'] = '<p class="mb-0"><strong>We were unable to change your email subscription preferences</strong></p><p class="mb-0">Please try again. If the issue persists, please contact support referencing <span class="mono">Email Preferences Update Error</span></p>';
  }
}

updateSubscription($_POST['SecurityComms'], 'Security');
updateSubscription($_POST['PaymentComms'], 'Payments');
if ($currentUser->hasPermission('Admin')) {
	updateSubscription($_POST['NewMemberComms'], 'NewMember');
}

if ($_POST['EmailAddress'] != $row['EmailAddress']) {
	if (v::email()->validate($_POST['EmailAddress'])) {
		$authCode = hash('sha256', random_bytes(64) . time());

		$user_details = [
			'User'		   => $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
			'OldEmail'   => $row['EmailAddress'],
			'NewEmail'	 => $_POST['EmailAddress']
		];
		$user_details = json_encode($user_details);

	  $sql = 'INSERT INTO `newUsers` (`AuthCode`, `UserJSON`, `Type`) VALUES (?, ?, ?)';
		try {
			$db->prepare($sql)->execute([$authCode, $user_details, 'EmailUpdate']);
		} catch (Exception $e) {
			// Could not add to db
			reportError($e);
			$_SESSION['TENANT-' . app()->tenant->getId()]['EmailUpdateError'] = '<p class="mb-0"><strong>We were unable to add your new email address to our awaiting confirmation list</strong></p><p class="mb-0">Please try again. If the issue persists, please contact support referencing <span class="mono">Email Address Update Error</span></p>';
		}
		$id = $db->lastInsertId();

		$name = getUserName($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']);

		$verifyLink = "email/auth/" . $id . "/" . $authCode;
	  // PHP Email
	  $subject = "Confirm your new email address";
	  $to = $email;
	  $sContent = '<p class="small">Hello ' . $name . '</p>
	  <p>We\'ve received a request to update the email address associated with your account.</p>
	  <p>We need you to verify your email address by following this link - <a
	  href="' . autoUrl($verifyLink) . '" target="_blank">' .
	  autoUrl($verifyLink) . '</a></p>
	  <p>You will need to use your email address, ' . $email . ' to sign in.</p>
	  <p>If you did not make a change to your email address, please ignore this email and consider reseting your password.</p>
	  <p>For help, send an email to <a
	  href="mailto:' . htmlspecialchars(app()->tenant->getKey('CLUB_EMAIL')) . '">' . htmlspecialchars(app()->tenant->getKey('CLUB_EMAIL')) . '</a>/</p>
	  ';
	  notifySend($to, $subject, $sContent, $name, $_POST['EmailAddress'], ["Email" => "support@" . getenv('EMAIL_DOMAIN'), "Name" => app()->tenant->getKey('CLUB_NAME') . " Security"]);
		$_SESSION['TENANT-' . app()->tenant->getId()]['EmailUpdate'] = true;
		$_SESSION['TENANT-' . app()->tenant->getId()]['EmailUpdateNew'] = $_POST['EmailAddress'];
	} else {
		$_SESSION['TENANT-' . app()->tenant->getId()]['EmailUpdate'] = false;
	}
}

header("Location: " . autoUrl("my-account/email"));
