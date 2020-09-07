<?php

use SCDS\Footer;

$db = app()->db;
$tenant = app()->tenant;
$user = app()->user;

$getRenewal = $db->prepare("SELECT renewalData.ID, renewalPeriods.ID PID, renewalPeriods.Name, renewalPeriods.Year, renewalData.User, renewalData.Document FROM renewalData LEFT JOIN renewalPeriods ON renewalPeriods.ID = renewalData.Renewal LEFT JOIN users ON users.UserID = renewalData.User WHERE renewalData.ID = ? AND users.Tenant = ?");
$getRenewal->execute([
  $id,
  $tenant->getId(),
]);
$renewal = $getRenewal->fetch(PDO::FETCH_ASSOC);

if (!$renewal) {
  halt(404);
}

if (!$user->hasPermission('Admin') && $renewal['User'] != $user->getId()) {
  halt(404);
}

$ren = Renewal::getUserRenewal($id);

$addr = [];
$getAddress = $db->prepare("SELECT `Value` FROM `userOptions` WHERE `User` = ? AND `Option` = ?");
$getAddress->execute([
  $ren->getUser(),
  'MAIN_ADDRESS',
]);
$json = $getAddress->fetchColumn();
if ($json != null) {
  $addr = json_decode($json);
}

$pagetitle = htmlspecialchars("Member Review - " . $ren->getRenewalName());

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('registration-and-renewal')) ?>">Registration</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('registration-and-renewal/' . $id)) ?>"><?= htmlspecialchars($ren->getRenewalName()) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Member Review</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          Member Review
        </h1>
        <p class="lead mb-0">
          Check all your members are included in this renewal
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container">

  <div class="row">
    <div class="col-lg-8">

      <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']) && $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']) { ?>
        <div class="alert alert-danger">
          <p class="mb-0">
            <strong>An error occurred when we tries to save the changes</strong>
          </p>
        </div>
      <?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
      } ?>

      <form method="post" class="needs-validation" novalidate>

        <p>
          Please check that all members for which you expect to complete registration/renewal are listed below.
        </p>

        <ul class="list-group">
          
        </ul>

        <p>
          If any members are not listed, please contact your club membership secretary before you continue.
        </p>

        <?= \SCDS\CSRF::write() ?>

        <p>
          <button type="submit" class="btn btn-success">Confirm and complete section</button>
        </p>
      </form>

    </div>
  </div>
</div>

<?php

$footer = new Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
