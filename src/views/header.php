<?php

global $currentUser;
$cvp = 'generic';
if (env('IS_CLS') && $currentUser != null && $currentUser->getUserBooleanOption('UsesGenericTheme')) {
  $cvp = 'generic';
} else if (env('IS_CLS')) {
  $cvp = 'chester';
}

include $cvp . '/header.php';
