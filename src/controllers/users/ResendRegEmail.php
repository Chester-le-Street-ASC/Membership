<?php

global $db;

// Instantiate variables to go in JSON
$alertContent;
$alertContextualClass;
$status = false;

$getUser = $db->prepare("SELECT Forename, Surname, EmailAddress FROM users WHERE UserID = ? AND RR = 1 AND AccessLevel = 'Parent'");
$getUser->execute([
  $_POST['user']
]);

$info = $getUser->fetch(PDO::FETCH_ASSOC);

if ($info != null) {

  // Try updating the password
  $db->beginTransaction();

  // New PW
  $password = hash('sha256', random_int(0, 999999999999));

  // Update hash in DB
  $update = $db->prepare("UPDATE users SET Password = ? WHERE UserID = ?");
  $update->execute([
    password_hash($password, PASSWORD_BCRYPT),
    $_POST['user']
  ]);

  try {
    $subject = "Complete your registration at " . env('CLUB_NAME');
    $message = "<p>Hello " . htmlspecialchars($info['Forename']) . ", </p>";
    $message .= "<p>We've pre-registered you for a " . htmlspecialchars(env('CLUB_NAME')) . " account. To continue, <a href=\"" . htmlspecialchars(autoUrl("assisted-registration/" . $_POST['user'] . "/" . $password)) . "\">please follow this link</a></p>";
    $message .= "<p>As part of the registration process, we'll ask you to set a password, let us know your communication preferences and fill in important information about you and/or your members.</p>";

    if (!bool(env('IS_CLS'))) {
      $message .= '<p>Please note that your club may not provide all services included in the membership software.</p>';
    }

    notifySend(null, $subject, $message, $info['Forename'] . ' ' . $info['Surname'], $info['EmailAddress']);

    $alertContent = '<p class="mb-0"><strong>Registration email resent successfully</strong></p><p class="mb-0">It will arrive in ' . htmlspecialchars($info['Forename'] . ' ' . $info['Surname'] . '\'s') . ' inbox imminently.</p>';
    $alertContextualClass = 'alert-success';
    $status = true;

    $db->commit();
  } catch (Exception $e) {
    $alertContent = '<p class="mb-0"><strong>Unable to resend registration email</strong></p><p class="mb-0">We\'ve been unable to send a registration emails to ' . htmlspecialchars($info['Forename'] . ' ' . $info['Surname']) . '. Please check their email address before trying again.</p>';
    $alertContextualClass = 'alert-danger';

    $db->rollBack();
  }

} else {

  $alertContent = '<p class="mb-0"><strong>Unable to resend registration email</strong></p><p class="mb-0">We cannot resend a registration email for this user as they are not eligible.</p>';
  $alertContextualClass = 'alert-warning';

}

echo json_encode([
  'alertContent' => $alertContent,
  'alertContextualClass' => $alertContextualClass,
  'status' => $status
]);