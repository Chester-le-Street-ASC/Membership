<?php

global $db;
$galaDetails = $db->prepare("SELECT GalaName `name`, GalaDate `ends` FROM galas WHERE GalaID = ?");
$galaDetails->execute([$id]);
$gala = $galaDetails->fetch(PDO::FETCH_ASSOC);

if ($gala == null) {
  halt(404);
}

$galaDate = new DateTime($gala['ends'], new DateTimeZone('Europe/London'));
$nowDate = new DateTime('now', new DateTimeZone('Europe/London'));

$getSessions = $db->prepare("SELECT `Name`, `ID` FROM galaSessions WHERE Gala = ? ORDER BY `ID` ASC");
$getSessions->execute([$id]);
$sessions = $getSessions->fetchAll(PDO::FETCH_ASSOC);

$getSwimmers = $db->prepare("SELECT MemberID id, MForename fn, MSurname sn FROM members WHERE UserID = ?");
$getSwimmers->execute([$_SESSION['UserID']]);
$swimmer = $getSwimmers->fetch(PDO::FETCH_ASSOC);

$hasSwimmers = true;
if ($swimmer != null && $nowDate < $galaDate) {
  $checkCount = $db->prepare("SELECT COUNT(*) FROM galaSessionsCanEnter WHERE `Member` = ? AND `Session` = ?");
  $insert = $db->prepare("INSERT INTO galaSessionsCanEnter (`Member`, `Session`, `CanEnter`) VALUES (?, ?, ?)");
  $update = $db->prepare("UPDATE galaSessionsCanEnter SET `CanEnter` = ? WHERE `Member` = ? AND `Session` = ?");

  try {
    do {
      if ($sessions != null) {
        for ($i = 0; $i < sizeof($sessions); $i++) {
          $can = false;
          if (isset($_POST[$swimmer['id'] . '-' . $sessions[$i]['ID']]) && bool($_POST[$swimmer['id'] . '-' . $sessions[$i]['ID']])) {
            $can = true;
          }

          $checkCount->execute([$swimmer['id'], $sessions[$i]['ID']]);
          if ($checkCount->fetchColumn() > 0) {
            // UPDATE
            $update->bindValue(1, $can, PDO::PARAM_BOOL);
            $update->bindValue(2, $swimmer['id'], PDO::PARAM_INT);
            $update->bindValue(3, $sessions[$i]['ID'], PDO::PARAM_INT);
            $update->execute();
          } else {
            // INSERT
            $insert->bindValue(1, $swimmer['id'], PDO::PARAM_INT);
            $insert->bindValue(2, $sessions[$i]['ID'], PDO::PARAM_INT);
            $insert->bindValue(3, $can, PDO::PARAM_BOOL);
            $insert->execute();
          }
        }
      }
    } while ($swimmer = $getSwimmers->fetch(PDO::FETCH_ASSOC));

    $_SESSION['SuccessStatus'] = true;
  } catch (Exception $e) {
    $_SESSION['ErrorStatus'] = true;
  }
}

header("Location: " . currentUrl());