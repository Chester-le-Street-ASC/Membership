<?php

// Inserts the standard letterhead on PDF pages
$club = json_decode(CLUB_JSON);
$addr = json_decode(env('CLUB_ADDRESS'));
$logoPath = env('CLUB_LOGO');

?>

<div class="row mb-3">
  <div class="split-50">
    <?php if ($logoPath != null) { ?>
    <img src="<?=BASE_PATH . $logoPath?>" class="logo">
    <?php } else { ?>
      <h1 class="primary"><?=htmlspecialchars(env('CLUB_NAME'))?></h1>
    <?php } ?>
  </div>
  <div class="split-50 text-right">
    <p class="mb-0">
      <strong><?=htmlspecialchars(env('CLUB_NAME'))?></strong><br>
      <?php
      for ($i = 0; $i < sizeof($addr); $i++) { ?>
        <?=htmlspecialchars($addr[$i])?><br>
        <?php if (isset($addr[$i+1]) && $addr[$i+1] == "") {
          break;
        } ?>
      <?php } ?>
    </p>
  </div>
</div>
