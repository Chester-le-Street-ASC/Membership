<?php

global $db;

$query = $db->prepare("SELECT COUNT(*) FROM joinSwimmers WHERE ID = ?");
$query->execute([$request]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$query = $db->prepare("SELECT ID, joinSwimmers.First, joinSwimmers.Last, joinParents.First PFirst, joinParents.Last PLast, DoB, ASA, Club, XP, XPDetails, Medical, Questions, TrialStart, TrialEnd FROM joinSwimmers JOIN joinParents WHERE ID = ? ORDER BY First ASC, Last ASC");
$query->execute([$request]);

$swimmer = $query->fetch(PDO::FETCH_ASSOC);

$exp = "None";
if ($swimmer['XP'] == 2) {
  $exp = "Ducklings (pre stages)";
} else if ($swimmer['XP'] == 3) {
  $exp = "School swimming lessons";
} else if ($swimmer['XP'] == 4) {
  $exp = "ASA/Swim England Learn to Swim Stage 1-7";
} else if ($swimmer['XP'] == 5) {
  $exp = "ASA/Swim England Learn to Swim Stage 8-10";
} else if ($swimmer['XP'] == 6) {
  $exp = "Swimming club";
}

$fake_time = false;
if ($swimmer['TrialStart'] == "" || $swimmer['TrialStart'] == null ||
$swimmer['TrialEnd'] == "" || $swimmer['TrialStart'] == null) {
  $swimmer['TrialStart'] = date("Y-m-d") . " 18:00:00";
  $swimmer['TrialEnd'] = date("Y-m-d") . " 18:30:00";
  $fake_time = true;
}

$pagetitle = "Trial Request - " . $swimmer['First'] . ' ' . $swimmer['Last'];
$use_white_background = true;

$value = $_SESSION['RequestTrial-FC'];

if (isset($_SESSION['RequestTrial-AddAnother'])) {
  $value = $_SESSION['RequestTrial-AddAnother'];
}

include BASE_PATH . 'views/header.php';

?>

<div class="container">
  <h1 class="mb-4">Trial Request for <?=$swimmer['First']?> <?=$swimmer['Last']?></h1>
  <div class="row">
    <div class="col-md-6">

      <?php if ($_SESSION['TrialAppointmentUpdated'] === true) { ?>
        <div class="alert alert-success">
          <strong>The appointment time was successfully updated.</strong>
        </div>
      <?php } ?>

      <?php if (!$fake_time && $swimmer['TrialStart'] != null &&
      $swimmer['TrialStart'] != "" && $swimmer['TrialEnd'] != null &&
      $swimmer['TrialEnd'] != "") { ?>
      <p class="mb-0"><strong>Trial Appointment Time</strong></p>
      <p>
        <?=date("H:i, j F Y", strtotime($swimmer['TrialStart']))?> - <?=date("H:i, j F Y", strtotime($swimmer['TrialEnd']))?>
      </p>
      <?php } else { ?>
      <p>
        No trial appointment time has been set
      </p>
      <?php } ?>

      <form method="post">
        <div class="form-group">
          <label for="trial-date">Trial Date</label>
          <input type="date" class="form-control" id="trial-date" name="trial-date" value="<?=date("Y-m-d", strtotime($swimmer['TrialStart']))?>">
        </div>

        <div class="form-row">
          <div class="col">
            <div class="form-group">
              <label for="trial-start">Trial Start Time</label>
              <input type="time" class="form-control" id="trial-start" name="trial-start" value="<?=date("H:i", strtotime($swimmer['TrialStart']))?>">
            </div>
          </div>

          <div class="col">
            <div class="form-group">
              <label for="trial-end">Trial End Time</label>
              <input type="time" class="form-control" id="trial-end" name="trial-end" value="<?=date("H:i", strtotime($swimmer['TrialEnd']))?>">
            </div>
          </div>
        </div>

        <p>
          <button type="submit" class="btn btn-success">
            Save Trial Appointment Details
          </button>

          <a href="<?=autoUrl($url_path . $hash . "cancel/" . $swimmer['ID'])?>" class="btn btn-danger">
            Cancel Trial Request
          </a>
        </p>
      </form>
    </div>
    <div class="col">
      <dl class="row mb-0 pb-0">
        <?php if ($swimmer['ASA'] != null && $swimmer['ASA'] != "") { ?>
        <dt class="col-md-4">ASA Number</dt>
        <dd class="col-md-8">
          <a target="_blank" href="https://www.swimmingresults.org/biogs/biogs_details.php?tiref=<?=$swimmer['ASA']?>">
            <?=$swimmer['ASA']?>
          </a>
        </dd>
        <?php } ?>

        <?php if ($_SESSION['AccessLevel'] == "Admin") { ?>
        <dt class="col-md-4">Date of Birth</dt>
        <dd class="col-md-8">
          <?=date("j F Y", strtotime($swimmer['DoB']))?>
        </dd>
        <?php } ?>

        <?php if ($swimmer['Club'] != null && $swimmer['Club'] != "") { ?>
        <dt class="col-md-4">Current/Previous Club</dt>
        <dd class="col-md-8">
          <?=$swimmer['Club']?>
        </dd>
        <?php } ?>

        <dt class="col-md-4">Experience</dt>
        <dd class="col-md-8">
          <?=$exp?>
        </dd>

        <?php if ($swimmer['XPDetails'] != null && $swimmer['XPDetails'] != "") { ?>
        <dt class="col-md-4">Experience Details</dt>
        <dd class="col-md-8">
          <?=$swimmer['XPDetails']?>
        </dd>
        <?php } ?>

        <?php if ($_SESSION['AccessLevel'] == "Admin") { ?>
        <?php if ($swimmer['Medical'] != null && $swimmer['Medical'] != "") { ?>
        <dt class="col-md-4">Medical Info</dt>
        <dd class="col-md-8">
          <?=$swimmer['Medical']?>
        </dd>
        <?php } ?>
        <?php } ?>

        <?php if ($swimmer['Questions'] != null && $swimmer['Questions'] != "") { ?>
        <dt class="col-md-4">Questions and Comments</dt>
        <dd class="col-md-8">
          <?=$swimmer['Questions']?>
        </dd>
        <?php } ?>
      </dl>
    </div>

  </div>
</div>

<script defer src="<?=autoUrl("js/NeedsVadddation.js")?>"></script>

<?php

unset($_SESSION['TrialAppointmentUpdated']);
include BASE_PATH . 'views/footer.php';