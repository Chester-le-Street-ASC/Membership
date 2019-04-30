<?php

global $db;

$query = $db->prepare("SELECT COUNT(*) FROM joinParents WHERE Hash = ?");
$query->execute([$hash]);

if ($query->fetchColumn() != 1) {
  halt(404);
}

$query = $db->prepare("SELECT First, Last, Email, Hash FROM joinParents WHERE Hash = ?");
$query->execute([$hash]);

$parent = $query->fetch(PDO::FETCH_ASSOC);

$query = $db->prepare("SELECT ID, First, Last, SquadSuggestion, SquadName, SquadFee FROM joinSwimmers INNER JOIN squads ON squads.SquadID = joinSwimmers.SquadSuggestion WHERE Parent = ? AND SquadSuggestion IS NOT NULL ORDER BY First ASC, Last ASC");
$query->execute([$hash]);

$swimmers = $query->fetchAll(PDO::FETCH_ASSOC);

$pagetitle = "Join Club - " . $parent['First'] . ' ' . $parent['Last'];
$use_white_background = true;

include BASE_PATH . 'views/header.php';

?>

<div class="container">
  <h1>Invite <?=$parent['First']?> <?=$parent['Last']?></h1>
  <div class="row">
    <div class="col-sm-10 col-md-8">
      <form method="post">
        <p class="lead">
          You are about to invite <?=$parent['First']?> <?=$parent['Last']?> as a
          parent to join the club with their swimmers.
        </p>
        <p>
          <?=$parent['First']?> will receive an email from our system with
          instructions on how to set a password. All swimmers will be connected
          to the account automatically.
        </p>
        <p>
          Swimmers will only be added to our database when <?=$parent['First']?>
          sets up their user account. Swimmers will not appear on our systems
          until this is done.
        </p>

        <h2>Review Swimmers</h2>
        <p>
          Please check that the swimmers listed below are the ones that you
          expect to be joining the club.
        </p>

        <?php foreach ($swimmers as $s) { ?>
          <div class="cell">
            <h3><?=htmlspecialchars($s['First'] . ' ' . $s['Last'])?></h3>
            <p><?=$s['SquadName']?> at &pound;<?=number_format($s['SquadFee'], 2)?></p>
          </div>
        <?php } ?>

        <h2>Review Parent Email Address</h2>
        <p>
          Please check the email address for <?=$parent['First']?> is correct.
          It will be used to set up their account. If a user account already
          exists with the parent's email address, the swimmers will be added to
          that existing account.
        </p>

        <div class="form-group">
          <label for="email-addr">Email address</label>
          <input type="email" class="form-control" id="email-addr" name="email-addr" placeholder="name@example.com" value="<?=$parent['Email']?>">
        </div>

        <p>
          <button class="btn btn-primary" type="submit">
            Invite to club
          </button>
        </p>

      </form>

    </div>
  </div>
</div>

<?php

include BASE_PATH . 'views/footer.php';