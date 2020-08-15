<?php

$db = app()->db;
$tenant = app()->tenant;
$pagetitle = 'COVID Health Screening';

// Show if this user is a squad rep
$getRepCount = $db->prepare("SELECT COUNT(*) FROM squadReps WHERE User = ?");
$getRepCount->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
]);
$showSignOut = $getRepCount->fetchColumn() > 0;

$user = app()->user;
if ($user->hasPermission('Admin') || $user->hasPermission('Coach') || $user->hasPermission('Galas')) {
  $showSignOut = true;
}

$getMembers = $db->prepare("SELECT MForename, MSurname, MemberID FROM members WHERE UserID = ? ORDER BY MForename ASC, MSurname ASC;");
$getMembers->execute([
  $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
]);
$member = $getMembers->fetch(PDO::FETCH_ASSOC);

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid')) ?>">COVID</a></li>
        <li class="breadcrumb-item active" aria-current="page">Screening</li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          COVID-19 Health Screening
        </h1>
        <p class="lead mb-0">
          Making sure you're safe to train
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container">

  <div class="row">

    <div class="col-lg-8">
      <h2>
        About our health screening
      </h2>
      <p class="lead">
        Swim England are recommending that all clubs carry out a periodic screening survey of all members who are training.
      </p>
      <p>
        The screen is to inform you and make you aware of the risks.
      </p>
      <p>
        Your club may refuse access to training if you do not have an up to date health screen.
      </p>

      <?php if ($member) { ?>
        <div class="list-group">
          <?php do { ?>
            <a href="<?= htmlspecialchars(autoUrl('covid/health-screening/members/' . $member['MemberID'] . '/new-survey')) ?>" class="list-group-item list-group-item-action">
              <?= htmlspecialchars($member['MForename'] . ' ' . $member['MSurname']) ?>
            </a>
          <?php } while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)); ?>
        </div>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>You don't have any members on your account</strong>
          </p>
          <p class="mb-0">
            Please add a member to be able to use this service.
          </p>
        </div>
      <?php } ?>
    </div>

  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();