<?php

global $db;

$query = $db->prepare("SELECT COUNT(*) FROM joinParents WHERE Hash = ? AND Invited = ?");
$query->execute([$_SESSION['AC-Registration']['Hash'], true]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$getNumNext = $db->prepare("SELECT COUNT(*) FROM joinSwimmers WHERE Parent = ? AND SquadSuggestion IS NOT NULL AND ID > ?");
$getNext = $db->prepare("SELECT First, Last, ID FROM joinSwimmers WHERE Parent = ? AND SquadSuggestion IS NOT NULL AND ID > ?");

if (!isset($_SESSION['AC-CC-Expected'])) {
  // Ah there's been a problem. Halt and report error
  halt(500);
} else {
  if ($_SESSION['AC-CC-Expected']['Current'] == "Parent") {
    if ($_POST['agreement'] == "1") {
      // Success
      $current = 0;
      $getNumNext->execute([$_SESSION['AC-Registration']['Hash'], $current]);
      if ($getNumNext->fetchColumn() > 0) {
        $getNext->execute([$_SESSION['AC-Registration']['Hash'], $current]);
        $next = $getNext->fetch(PDO::FETCH_ASSOC);
        $_SESSION['AC-CC-Expected']['Current'] = $next['ID'];
        $_SESSION['AC-CC-Expected']['URL'] = str_replace(' ', '', strtolower($next['First'] . '-' . $next['Last']));
        header("Location: " . autoUrl("register/ac/code-of-conduct/" . $_SESSION['AC-CC-Expected']['URL']));
      } else {
        unset(['AC-CC-Expected']);
        $_SESSION['AC-Registration']['Stage'] == 'AutoAccountSetup';
        header("Location: " . autoUrl("register/ac/setup-account"));
      }
    } else {
      header("Location: " . autoUrl("register/ac/code-of-conduct/me"));
    }
  } else {
    if ($_POST['agreement'] == "1") {
      // Success
      $current = $_SESSION['AC-CC-Expected']['Current'];
      $getNumNext->execute([$_SESSION['AC-Registration']['Hash'], $current]);
      if ($getNumNext->fetchColumn() > 0) {
        $getNext->execute([$_SESSION['AC-Registration']['Hash'], $current]);
        $next = $getNext->fetch(PDO::FETCH_ASSOC);
        $_SESSION['AC-CC-Expected']['Current'] = $next['ID'];
        $_SESSION['AC-CC-Expected']['URL'] = strtolower(trim($next['First']) . '-' . trim($next['First']));
        header("Location: " . autoUrl("register/ac/code-of-conduct/" . $_SESSION['AC-CC-Expected']['URL']));
      } else {
        unset(['AC-CC-Expected']);
        $_SESSION['AC-Registration']['Stage'] == 'AutoAccountSetup';
        header("Location: " . autoUrl("register/ac/setup-account"));
      }

    } else {
      header("Location: " . autoUrl("register/ac/code-of-conduct/" . $_SESSION['AC-CC-Expected']['URL']));
    }
  }
}