<?php

if (!isset($_SESSION['GC-Setup-Status']) || $_SESSION['GC-Setup-Status'] == null) {
  halt(404);
}

$pagetitle = "You've setup a Direct Debit";
if ($_SESSION['GC-Setup-Status'] != 'success') {
  $pagetitle = 'An error has occurred';
}

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

?>

<div class="container">
  <div class="row">
    <div class="col-lg-8">
      <?php if ($_SESSION['GC-Setup-Status'] == 'success' || $_SESSION['GC-Setup-Status'] == 'redirect_flow_already_completed') { ?>

      <h1>You've successfully set up your new direct debit.</h1>
      <p class="lead">GoCardless will appear on your bank statement when
        payments are taken against this Direct Debit.</p>
      <p>GoCardless handles direct debit payments for <?=htmlspecialchars(env('CLUB_NAME'))?>.</p>
      <?php if (isset($renewal_trap) && $renewal_trap) { ?>
      <a href="<?php echo autoUrl("renewal/go"); ?>" class="mb-3 btn btn-success">Continue registration or renewal</a>
      <?php } else { ?>
      <a href="<?php echo autoUrl("payments"); ?>" class="mb-3 btn btn-dark">Go to Payments</a>
      <?php } ?>

      <?php } else if ($_SESSION['GC-Setup-Status'] == 'redirect_flow_incomplete') { ?>

      <h1>Form not completed</h1>
      <p class="lead">You have not completed the required form to set up a direct debit mandate.</p>
      <p><a href="<?=autoUrl($url_path . "/setup/2")?>">Complete form again</a></p>

      <?php } else { ?>

      <h1>An unexpected error has occurred</h1>
      <p class="lead">Please try again.</p>
      <p><a href="<?=autoUrl($url_path . "/setup/2")?>">Complete form again</a></p>

      <?php } ?>
    </div>
  </div>
</div>

<?php

include BASE_PATH . "views/footer.php";

$_SESSION['GC-Setup-Status'] = null;
unset($_SESSION['GC-Setup-Status']);