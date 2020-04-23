<?php

global $db;

$query = $db->prepare("SELECT COUNT(*) FROM joinParents WHERE Hash = ? AND Invited = ?");
$query->execute([$hash, true]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$query = $db->prepare("SELECT First, Last, Email, Hash FROM joinParents WHERE Hash = ?");
$query->execute([$hash]);

$parent = $query->fetch(PDO::FETCH_ASSOC);

$_SESSION['AC-Registration']['Hash'] = $parent['Hash'];
$_SESSION['AC-Registration']['Stage'] = 'UserDetails';

header("Location: " . autoUrl("register/ac/user-details"));
