<?php

$db = app()->db;

$obj = null;
if (bool(env('IS_CLS'))) {
  $file = getCachedFile(CACHE_DIR . 'CLS-ASC-News.json', 'https://chesterlestreetasc.co.uk/wp-json/wp/v2/posts?rand_id=' . time(), 10800);
  $obj = json_decode($file);
}

$file = getCachedFile(CACHE_DIR . 'SE-News.json', 'https://www.swimming.org/sport/wp-json/wp/v2/posts?rand_id=' . time(), 10800);
$asa = json_decode($file);

$file = getCachedFile(CACHE_DIR . 'SE-NE.xml', 'https://asaner.org.uk/feed?rand_id=' . time(), 10800);
$asa_ne = null;
try {
  $asa_ne = new SimpleXMLElement($file);
} catch (Exception $e) {
}

try {
	$sql = 'SELECT `MemberID`, `MForename`, `MSurname`, `SquadFee`, `SquadName`, ClubPays FROM `members` INNER JOIN `squads` ON `members`.`SquadID` =
	`squads`.`SquadID` WHERE `members`.`UserID` = ? ORDER BY `MForename` ASC,
	`MSurname` ASC';
	$query = $db->prepare($sql);
	$query->execute([$_SESSION['UserID']]);
} catch (Exception $e) {
	halt(500);
}

$swimmers = $query->fetchAll(PDO::FETCH_ASSOC);

try {
	$sql = 'SELECT `MForename`, `MSurname`, `GalaName`, `FeeToPay`, `EntryID` FROM
	((`galaEntries` INNER JOIN `members` ON `members`.`MemberID` =
	`galaEntries`.`MemberID`) INNER JOIN `galas` ON `galas`.`GalaID` =
	`galaEntries`.`GalaID`) WHERE `members`.`UserID` = ? AND `GalaDate` >=
	CURDATE() ORDER BY `GalaDate` ASC, `MForename` ASC, `MSurname` ASC';
	$query = $db->prepare($sql);
	$query->execute([$_SESSION['UserID']]);
} catch (Exception $e) {
	halt(500);
}

$galas = $query->fetchAll(PDO::FETCH_ASSOC);

$username = htmlspecialchars(explode(" ", getUserName($_SESSION['UserID']))[0]);

$pagetitle = "Home";
include BASE_PATH . "views/header.php";

?>

<div class="front-page mb-n3">
  <div class="container">

    <h1><?=helloGreeting()?> <?=$username?></h1>
		<p class="lead mb-4">Welcome to your account</p>

		<?php if (!isSubscribed($_SESSION['UserID'], 'Notify')) { ?>
    <aside class="row mb-4">
      <div class="col-lg-6">
        <div class="cell bg-primary text-white">
					<h2 class="mb-0">
            Emails from us
          </h2>

          <p>
            <strong>
              You're missing out on email updates from <?=htmlspecialchars(app()->tenant->getKey('CLUB_NAME'))?>
            </strong>
          </p>
          <p>
            Head to <a class="text-white" href="<?=autoUrl("my-account/email")?>">My Account</a>
            to change your email preferences and stay up to date!
          </p>
        </div>
      </div>
    </aside>
		<?php } ?>

    <?php if ($bankHoliday = isBankHoliday()) { ?>
    <aside class="row mb-4">
      <div class="col-lg-6">
        <div class="cell bg-primary text-white">
					<h2 class="mb-0"><?php if ($bankHoliday['bunting']) { ?>It's <?=htmlspecialchars($bankHoliday['title'])?>!<?php if ($bankHoliday['notes']) { ?> <em><?=htmlspecialchars($bankHoliday['notes'])?></em>.<?php } ?><?php } else { ?>Today is <?=htmlspecialchars($bankHoliday['title'])?>.<?php if ($bankHoliday['notes']) { ?> <em><?=htmlspecialchars($bankHoliday['notes'])?></em>.<?php } ?><?php } ?></h2>
					<p class="lead mb-0">There may be session cancellations or alterations today.</p>
        </div>
      </div>
    </aside>
    <?php } ?>

    <?php if (app()->tenant->getKey('GOCARDLESS_ACCESS_TOKEN') && !userHasMandates($_SESSION['UserID'])) { ?>
    <div class="mb-4">
      <h2 class="mb-4">Want to set up a Direct Debit?</h2>
      <div class="news-grid">
        <a href="<?=autoUrl("payments")?>">
          <span class="mb-3">
            <span class="title mb-0">
              Setup a Direct Debit Now
            </span>
          </span>
          <span class="category">
            Payments
          </span>
        </a>
        <a href="https://www.chesterlestreetasc.co.uk/support/directdebit/">
          <span class="mb-3">
            <span class="title mb-0">
              Learn more about Direct Debits
            </span>
          </span>
          <span class="category">
            Payments
          </span>
        </a>
      </div>
    </div>
    <?php } ?>

		<div class="mb-4">
      <h2 class="mb-4">My Members</h2>
      <div class="news-grid">
				<?php
				if (sizeof($swimmers) > 0) {
				foreach ($swimmers as $s) {
					$fee = "Exempt from fees";
					if (!$s['ClubPays']) {
						$fee = "&pound;" . (string) (\Brick\Math\BigDecimal::of((string) $s['SquadFee']))->toScale(2);
					}?>
				<a href="<?=autoUrl("swimmers/" . $s['MemberID'])?>">
					<span class="mb-3">
	          <span class="title mb-0">
							<?=htmlspecialchars($s['MForename'] . ' ' . $s['MSurname'])?>
						</span>
						<span>
							<?=$fee?>
						</span>
					</span>
          <span class="category">
						<?=htmlspecialchars($s['SquadName'])?> Squad
					</span>
        </a>
				<?php }
			} else { ?>
				<p class="mb-0">You do not have any swimmers connected to your account</p>
			<?php } ?>
			</div>
		</div>

    <div class="mb-4">
      <h2 class="mb-4">My Gala Entries</h2>
      <div class="news-grid">
				<?php
				if (sizeof($galas) > 0) {
				foreach ($galas as $g) { ?>
				<a href="<?=autoUrl("galas/entries/" . $g['EntryID'])?>">
					<span class="mb-3">
	          <span class="title mb-0">
							<?=htmlspecialchars($g['MForename'] . ' ' . $g['MSurname'])?>
						</span>
						<span>
							&pound;<?=htmlspecialchars((string) (\Brick\Math\BigDecimal::of((string) $g['FeeToPay']))->toScale(2))?>
						</span>
					</span>
          <span class="category">
						<?=htmlspecialchars($g['GalaName'])?>
					</span>
        </a>
			<?php }
			} else { ?>
				<p class="mb-0">You have no current gala entries</p>
			<?php } ?>
			</div>
		</div>

    <?php if (bool(env('IS_CLS'))) { ?>
    <div class="mb-4">
      <h2 class="mb-4">Club News</h2>
      <div class="news-grid">
        <?php
        $max_posts = 6;
        if (sizeof($obj) < $max_posts) {
          $max_posts = sizeof($obj);
        }
        for ($i = 0; $i < $max_posts; $i++) { ?>
				<a href="<?=htmlspecialchars($obj[$i]->link)?>" target="_blank" title="<?=($obj[$i]->title->rendered)?>">
					<span class="mb-3">
	          <span class="title mb-0">
							<?=($obj[$i]->title->rendered)?>
						</span>
					</span>
          <span class="category">
						News
					</span>
        </a>
        <?php } ?>
			</div>
		</div>
    <?php } ?>

    <?php if ($asa != null && $asa != "") { ?>
    <div class="mb-4">
      <h2 class="mb-4">Swim England News</h2>
      <div class="news-grid">
        <?php
        $max_posts = 6;
        if (sizeof($asa) < $max_posts) {
          $max_posts = sizeof($asa);
        }
        for ($i = 0; $i < $max_posts; $i++) { ?>
  			<a href="<?=htmlspecialchars($asa[$i]->link)?>" target="_blank" title="<?=($asa[$i]->title->rendered)?>">
  				<span class="mb-3">
            <span class="title mb-0">
  						<?=($asa[$i]->title->rendered)?>
  					</span>
  				</span>
          <span class="category">
  					News
  				</span>
        </a>
        <?php } ?>
  		</div>
  	</div>
    <?php } ?>

    <?php if (app()->tenant->getKey('ASA_DISTRICT') == 'E' && $asa_ne != null) { ?>
    <div class="mb-4">
      <h2 class="mb-4">Swim England North East News</h2>
      <div class="news-grid">
        <?php
        $max_posts = 6;
        if (sizeof($asa_ne->channel->item) < $max_posts) {
          $max_posts = sizeof($asa_ne->channel->item);
        }
        for ($i = 0; $i < $max_posts; $i++) { ?>
  			<a href="<?=htmlspecialchars($asa_ne->channel->item[$i]->link)?>" target="_blank" title="<?=htmlspecialchars($asa_ne->channel->item[$i]->title)?> (<?=htmlspecialchars($asa_ne->channel->item[$i]->category)?>)">
  				<span class="mb-3">
            <span class="title mb-0">
  						<?=htmlspecialchars($asa_ne->channel->item[$i]->title)?>
  					</span>
  				</span>
          <span class="category">
  					<?=htmlspecialchars($asa_ne->channel->item[$i]->category)?>
  				</span>
        </a>
        <?php } ?>
  		</div>
  	</div>
    <?php } ?>

  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
