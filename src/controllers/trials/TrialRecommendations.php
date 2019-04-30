<?php

global $db;

$query = $db->prepare("SELECT COUNT(*) FROM joinSwimmers WHERE ID = ?");
$query->execute([$request]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$query = $db->prepare("SELECT ID, joinSwimmers.First, joinSwimmers.Last, joinParents.First PFirst, joinParents.Last PLast, DoB, ASA, Club, XPDetails, XP, Medical, Questions, TrialStart, TrialEnd, SquadSuggestion, Comments FROM joinSwimmers JOIN joinParents WHERE ID = ? ORDER BY First ASC, Last ASC");
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

$pagetitle = "Trial Request - " . $swimmer['First'] . ' ' . $swimmer['Last'];
$use_white_background = true;

$query = $db->query("SELECT SquadID, SquadName FROM squads ORDER BY SquadFee DESC, SquadName ASC");
$squads = $query->fetchAll(PDO::FETCH_ASSOC);

$value = $_SESSION['RequestTrial-FC'];

if (isset($_SESSION['RequestTrial-AddAnother'])) {
  $value = $_SESSION['RequestTrial-AddAnother'];
}

include BASE_PATH . 'views/header.php';

?>

<div class="container">
  <h1 class="mb-4">Trial Recommendations for <?=$swimmer['First']?> <?=$swimmer['Last']?></h1>
  <div class="row">
    <div class="col-sm-6">
      <p class="lead">
        Hello <?=getUserName($_SESSION['UserID'])?>!
      </p>
      <p>
        From this page you can make a recommendation for a squad for
        <?=$swimmer['First']?> <?=$swimmer['Last']?> and leave comments. You can
        also mark them as being ineligible to join.
      </p>

      <?php if ($_SESSION['TrialRecommendationsUpdated'] === true) { ?>
        <div class="alert alert-success">
          <strong>Successfully updated the recommendations</strong>
        </div>
      <?php } ?>

      <form method="post">
        <div class="form-group">
          <label for="comments">Comments on Swimmer</label>
          <textarea class="form-control" id="comments" name="comments" rows="3"><?=$swimmer['Comments']?></textarea>
        </div>

        <div class="form-group">
          <label for="squad">Recommended Squad</label>
          <select class="custom-select" name="squad" id="squad" required>

            <?php if ($swimmer['SquadSuggestion'] == null) { ?>
            <option value="null" selected>Select a squad</option>
            <?php }

            foreach ($squads as $squad) {
            $selected = "";
            if ($swimmer['SquadSuggestion'] == $squad['SquadID']) {
            $selected = "selected";
             } ?>
            <option value="<?=$squad['SquadID']?>" <?=$selected?>><?=$squad['SquadName']?></option>
            <?php } ?>
          </select>
        </div>

        <p>
          <button type="submit" class="btn btn-success">
            Save details
          </button>

          <a href="<?=autoUrl($url_path . $hash . "cancel/" . $swimmer['ID'])?>" class="btn btn-danger">
            Mark ineligible
          </a>
        </p>

        <p>
            Press <em>Mark ineligible</em> if the swimmer will not be offered a
            place at <?=CLUB_NAME?>.
        </p>
      </form>
    </div>
    <div class="col">

      <?php if ($swimmer['TrialStart'] != null && $swimmer['TrialStart'] != "" &&
      $swimmer['TrialEnd'] != null && $swimmer['TrialEnd'] != "") { ?>
      <p class="mb-0"><strong>Trial Appointment Time</strong></p>
      <p class="mb-2">
        <?=date("H:i, j F Y", strtotime($swimmer['TrialStart']))?> - <?=date("H:i, j F Y", strtotime($swimmer['TrialEnd']))?>
      </p>
      <?php } else { ?>
      <p class="mb-2">
        No trial appointment has been set
      </p>
      <?php } ?>

      <dl class="row">
        <?php if ($swimmer['ASA'] != null && $swimmer['ASA'] != "") { ?>
        <dt class="col-md-4">ASA Number</dt>
        <dd class="col-md-8">
          <a target="_blank" href="https://www.swimmingresults.org/biogs/biogs_details.php?tiref=<?=$swimmer['ASA']?>">
            <?=$swimmer['ASA']?>
          </a>
        </dd>
        <?php } ?>

        <dt class="col-md-4">Date of Birth</dt>
        <dd class="col-md-8">
          <?=date("j F Y", strtotime($swimmer['DoB']))?>
        </dd>

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

        <?php if ($swimmer['Medical'] != null && $swimmer['Medical'] != "") { ?>
        <dt class="col-md-4">Medical Info</dt>
        <dd class="col-md-8">
          <?=$swimmer['Medical']?>
        </dd>
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

<script defer src="<?=autoUrl("js/NeedsValidation.js")?>"></script>

<?php

unset($_SESSION['TrialRecommendationsUpdated']);
include BASE_PATH . 'views/footer.php';