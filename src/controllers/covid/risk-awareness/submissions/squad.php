<?php

$db = app()->db;
$tenant = app()->tenant;
$pagetitle = 'COVID Health Screening';

$squad = null;

$user = app()->user;
if ($user->hasPermission('Admin') || $user->hasPermission('Coach') || $user->hasPermission('Galas')) {
  $showSquadOpts = true;
  $getSquad = $db->prepare("SELECT SquadName, SquadID FROM squads WHERE squads.Tenant = ? AND squads.SquadID = ?;");
  $getSquad->execute([
    $tenant->getId(),
    $id,
  ]);
  $squad = $getSquad->fetch(PDO::FETCH_ASSOC);
} else {
  $getSquad = $db->prepare("SELECT SquadName, SquadID FROM squads INNER JOIN squadReps ON squads.SquadID = squadReps.Squad WHERE squadReps.User = ? AND squadReps.Squad = ?;");
  $getSquad->execute([
    $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'],
    $id,
  ]);
  $squad = $getSquad->fetch(PDO::FETCH_ASSOC);
}

if (!$squad) {
  halt(404);
}

$getMembers = $db->prepare("SELECT MForename, MSurname, MemberID FROM members INNER JOIN squadMembers ON squadMembers.Member = members.MemberID WHERE squadMembers.Squad = ? ORDER BY MForename ASC, MSurname ASC;");
$getMembers->execute([
  $id,
]);
$member = $getMembers->fetch(PDO::FETCH_ASSOC);

$getLatestCompletion = $db->prepare("SELECT `ID`, `DateTime`, `MemberAgreement`, `Guardian`, `Forename`, `Surname` FROM covidRiskAwareness LEFT JOIN users ON users.UserID = covidRiskAwareness.Guardian WHERE Member = ? ORDER BY `DateTime` DESC LIMIT 1");

$pagetitle = htmlspecialchars($squad['SquadName']) . ' - Risk Awareness';

include BASE_PATH . 'views/header.php';

?>

<div class="bg-light mt-n3 py-3 mb-3">
  <div class="container">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid')) ?>">COVID</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid/risk-awareness')) ?>">Risk Awareness</a></li>
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('covid/risk-awareness#squads')) ?>">Squads</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($squad['SquadName']) ?></li>
      </ol>
    </nav>

    <div class="row align-items-center">
      <div class="col-lg-8">
        <h1>
          <?= htmlspecialchars($squad['SquadName']) ?> Risk Awareness Forms
        </h1>
        <p class="lead mb-0">
          Keeping everyone safe
        </p>
      </div>
    </div>
  </div>
</div>

<div class="container">

  <div class="row">

    <div class="col-lg-8">

      <?php if ($member) { ?>

        <ul class="list-group mb-3" id="table-area">
          <?php do {
            $getLatestCompletion->execute([
              $member['MemberID'],
            ]);
            $latest = $getLatestCompletion->fetch(PDO::FETCH_ASSOC);
          ?>
            <li class="list-group-item">
              <div class="row align-items-center">
                <div class="col-sm">
                  <p class="mb-0"><?= htmlspecialchars($member['MForename'] . ' ' . $member['MSurname']) ?></p>
                  <p class="mb-0">
                    <?php if ($latest) {
                      $time = new DateTime($latest['DateTime'], new DateTimeZone('UTC'));
                      $time->setTimezone(new DateTimeZone('Europe/London'));
                    ?>
                      <?php if (bool($latest['MemberAgreement'])) { ?>
                        <span class="text-success"><i class="fa fa-check-circle" aria-hidden="true"></i> Signed at <?= htmlspecialchars($time->format('H:i, j F Y')) ?><?php if ($latest['Guardian']) { ?> with <?= htmlspecialchars($latest['Forename'] . ' ' . $latest['Surname']) ?> as parent/guardian<?php } ?></span>
                      <?php } else { ?>
                        <span class="text-warning"><i class="fa fa-minus-circle" aria-hidden="true"></i> A new declaration form is required</span>
                      <?php } ?>
                    <?php } else { ?>
                      <span class="text-danger"><i class="fa fa-times-circle" aria-hidden="true"></i> No risk awareness declaration submitted</span>
                    <?php } ?>
                  </p>
                  <?php if (!$latest) { ?>
                    <div class="mb-3 d-sm-none"></div>
                  <?php } ?>
                </div>
                <?php if (false && $latest && bool($latest['MemberAgreement'])) { ?>
                  <div class="col-auto">
                    <a class="btn btn-warning" href="<?= htmlspecialchars(autoUrl('covid/risk-awareness/members/' . $member['MemberID'] . '/void-form')) ?>" title="Require that <?= htmlspecialchars($member['MForename']) ?> submits a new declaration form">
                      Void form
                    </a>
                  </div>
                <?php } ?>
              </div>
            </li>
          <?php } while ($member = $getMembers->fetch(PDO::FETCH_ASSOC)); ?>
        </ul>
      <?php } else { ?>
        <div class="alert alert-warning">
          <p class="mb-0">
            <strong>There are no members in <?= htmlspecialchars($squad['SquadName']) ?></strong>
          </p>
        </div>
      <?php } ?>

      <p>
        Information about COVID-19 is available <a href="https://www.nhs.uk/conditions/coronavirus-covid-19/" target="_blank">on the NHS website</a>.
      </p>
    </div>

  </div>
</div>

<div class="modal " id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reviewModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="reviewModalBody">
        ...
      </div>
      <div class="modal-footer" id="reviewModalFooter">
        <div class="d-block w-100">
          <div class="row">
            <div class="col">
              <p class="mb-0">
                <button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
              </p>
            </div>
            <div class="col-auto">
              <p class="mb-0">
                <button type="button" class="btn btn-danger review-confirm-button" id="reject-button" data-action="reject">Reject</button>
                <button type="button" class="btn btn-success review-confirm-button" id="approve-button" data-action="approve">Approve</button>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
