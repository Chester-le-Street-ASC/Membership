<?php

$db = app()->db;
$tenant = app()->tenant;

$count = 0;
$rows = 0;
$sql = "";

$coachEnters = false;
// Check if coach enters
if (isset($_GET["galaID"])) {
	$getCoachEnters = $db->prepare("SELECT CoachEnters FROM galas WHERE GalaID = ? AND Tenant = ?");
	$getCoachEnters->execute([
		$_GET["galaID"],
		$tenant->getId()
	]);
	$coachEnters = bool($getCoachEnters->fetchColumn());
}

if (!$coachEnters && (isset($_REQUEST["galaID"])) && (isset($_REQUEST["swimmer"]))) {
	// get the galaID parameter from request
	$galaID = $_REQUEST["galaID"];
	$memberID = $_REQUEST["swimmer"];

	// Get swimmer info
	$getSwimmer = $db->prepare("SELECT MemberID id, MForename fn, MSurname sn, DateOfBirth dob, UserID parent FROM members WHERE MemberID = ? AND Tenant = ?");
	$getSwimmer->execute([
		$_GET['swimmer'],
		$tenant->getId()
	]);
	$swimmer = $getSwimmer->fetch(PDO::FETCH_ASSOC);

	if ($swimmer == null || ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' && $swimmer['parent'] != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) {
		halt(404);
	}

	// Get gala info
	$getGala = $db->prepare("SELECT GalaFeeConstant flatfee, GalaFee fee, HyTek, GalaName `name`, GalaVenue venue, RequiresApproval, `Description` FROM galas WHERE GalaID = ? AND Tenant = ?");
	$getGala->execute([
		$_GET["galaID"],
		$tenant->getId()
	]);
	$gala = $getGala->fetch(PDO::FETCH_ASSOC);

	if ($gala == null) {
		halt(404);
	}

	$existing = $db->prepare("SELECT * FROM galaEntries WHERE GalaID = ? AND MemberID = ?");
	$existing->execute([$galaID, $memberID]);

	$row = $existing->fetch(PDO::FETCH_ASSOC);

	if ($row != null) { ?>

		<div id="gala-info" data-enterable=<?= htmlspecialchars(json_encode(false)) ?>></div>

		<div class="alert alert-warning">
			<strong>Oops. You've aleady entered this member into this gala</strong> <br>
			You might want to check that. <?php if ($row['EntryProcessed'] == 0) { ?>We've not processed your entry yet, so you <a class="alert-link" href="<?= htmlspecialchars(autoUrl("galas/entries/" . $row["EntryID"])) ?>">can edit your gala entry</a> if you need to make changes.<?php } else { ?>We've already processed your gala entry - You'll need to contact your gala administrator if you need to make any changes.<?php } ?>
		</div>

		<?php } else {

		$details = $db->prepare("SELECT `HyTek`, `GalaName`, `Description`, `GalaFeeConstant` FROM galas WHERE GalaID = ?");
		$details->execute([$galaID]);
		$row = $details->fetch(PDO::FETCH_ASSOC);

		$galaData = new GalaPrices($db, $_GET["galaID"]);

		// Get squads (if requires approval and any squad has rep, approval will be false)
		$getReps = $db->prepare("SELECT COUNT(*) FROM squadReps INNER JOIN squadMembers ON squadMembers.Squad = squadReps.Squad WHERE squadMembers.Member = ?");
		$getReps->execute([
			$memberID
		]);
		$numReps = $getReps->fetchColumn();

		if ($gala['Description'] || bool($gala['HyTek']) || (bool($gala['RequiresApproval']) && $numReps > 0)) { ?>
			<h2>About this gala</h2>

			<?php

			$markdown = new ParsedownForMembership();
			$markdown->setSafeMode(false);

			?>

			<?= $markdown->text($row['Description']) ?>

			<?php if (bool($gala['HyTek'])) { ?>
				<p>This is a HyTek gala. Once you've selected your swims, you'll need to provide times for each event.</p>
			<?php } ?>

			<?php if (bool($gala['RequiresApproval']) && $numReps > 0) { ?>
				<p>This entry must be approved by a squad rep before the gala team will submit it to the host club.</p>
			<?php } else if (bool($gala['RequiresApproval'])) { ?>
				<p>There is no squad rep assigned to <?= htmlspecialchars($swimmer['fn']) ?>'s squad. This means nobody will be able to review your entry before it goes to the gala team.</p>
		<?php }
		}

		$swimsArray = ['50Free', '100Free', '200Free', '400Free', '800Free', '1500Free', '50Breast', '100Breast', '200Breast', '50Fly', '100Fly', '200Fly', '50Back', '100Back', '200Back', '100IM', '150IM', '200IM', '400IM',];
		$swimsTimeArray = ['50FreeTime', '100FreeTime', '200FreeTime', '400FreeTime', '800FreeTime', '1500FreeTime', '50BreastTime', '100BreastTime', '200BreastTime', '50FlyTime', '100FlyTime', '200FlyTime', '50BackTime', '100BackTime', '200BackTime', '100IMTime', '150IMTime', '200IMTime', '400IMTime',];
		$swimsTextArray = ['50 Free', '100 Free', '200 Free', '400 Free', '800 Free', '1500 Free', '50 Breast', '100 Breast', '200 Breast', '50 Fly', '100 Fly', '200 Fly', '50 Back', '100 Back', '200 Back', '100 IM', '150 IM', '200 IM', '400 IM',];
		$rowArray = [1, null, null, null, null, 2, 1,  null, 2, 1, null, 2, 1, null, 2, 1, null, null, 2];
		$rowArrayText = ["Freestyle", null, null, null, null, 2, "Breaststroke",  null, 2, "Butterfly", null, 2, "Freestyle", null, 2, "Individual Medley", null, null, 2];

		?>

		<h2>Select Swims</h2>
		<p>Your club will have hidden events which will not run at this gala but some events may not be open for entries from some age groups.</p>

		<div id="gala-info" data-enterable=<?= htmlspecialchars(json_encode(true)) ?>></div>

		<div id="gala-checkboxes">
			<div class="row mb-3">
				<?php if ($galaData->getEvent('50Free')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="50Free" name="50Free" data-event-fee="<?= htmlspecialchars($galaData->getEvent('50Free')->getPrice()) ?>">
							<label class="custom-control-label" for="50Free">50 Freestyle <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('50Free')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
				<?php if ($galaData->getEvent('100Free')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="100Free" name="100Free" data-event-fee="<?= htmlspecialchars($galaData->getEvent('100Free')->getPrice()) ?>">
							<label class="custom-control-label" for="100Free">100 Freestyle <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('100Free')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
				<?php if ($galaData->getEvent('200Free')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="200Free" name="200Free" data-event-fee="<?= htmlspecialchars($galaData->getEvent('200Free')->getPrice()) ?>">
							<label class="custom-control-label" for="200Free">200 Freestyle <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('200Free')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
				<?php if ($galaData->getEvent('400Free')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="400Free" name="400Free" data-event-fee="<?= htmlspecialchars($galaData->getEvent('400Free')->getPrice()) ?>">
							<label class="custom-control-label" for="400Free">400 Freestyle <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('400Free')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
				<?php if ($galaData->getEvent('800Free')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="800Free" name="800Free" data-event-fee="<?= htmlspecialchars($galaData->getEvent('800Free')->getPrice()) ?>">
							<label class="custom-control-label" for="800Free">800 Freestyle <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('800Free')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
				<?php if ($galaData->getEvent('1500Free')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="1500Free" name="1500Free" data-event-fee="<?= htmlspecialchars($galaData->getEvent('1500Free')->getPrice()) ?>">
							<label class="custom-control-label" for="1500Free">1500 Freestyle <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('1500Free')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
			</div>
			<div class="row mb-3">
				<?php if ($galaData->getEvent('50Breast')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="50Breast" name="50Breast" data-event-fee="<?= htmlspecialchars($galaData->getEvent('50Breast')->getPrice()) ?>">
							<label class="custom-control-label" for="50Breast">50 Breaststroke <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('50Breast')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
				<?php if ($galaData->getEvent('100Breast')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="100Breast" name="100Breast" data-event-fee="<?= htmlspecialchars($galaData->getEvent('100Breast')->getPrice()) ?>">
							<label class="custom-control-label" for="100Breast">100 Breaststroke <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('100Breast')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
				<?php if ($galaData->getEvent('200Breast')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="200Breast" name="200Breast" data-event-fee="<?= htmlspecialchars($galaData->getEvent('200Breast')->getPrice()) ?>">
							<label class="custom-control-label" for="200Breast">200 Breaststroke <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('200Breast')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
			</div>
			<div class="row mb-3">
				<?php if ($galaData->getEvent('50Fly')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="50Fly" name="50Fly" data-event-fee="<?= htmlspecialchars($galaData->getEvent('50Fly')->getPrice()) ?>">
							<label class="custom-control-label" for="50Fly">50 Butterfly <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('50Fly')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
				<?php if ($galaData->getEvent('100Fly')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="100Fly" name="100Fly" data-event-fee="<?= htmlspecialchars($galaData->getEvent('100Fly')->getPrice()) ?>">
							<label class="custom-control-label" for="100Fly">100 Butterfly <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('100Fly')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
				<?php if ($galaData->getEvent('200Fly')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="200Fly" name="200Fly" data-event-fee="<?= htmlspecialchars($galaData->getEvent('200Fly')->getPrice()) ?>">
							<label class="custom-control-label" for="200Fly">200 Butterfly <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('200Fly')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
			</div>
			<div class="row mb-3">
				<?php if ($galaData->getEvent('50Back')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="50Back" name="50Back" data-event-fee="<?= htmlspecialchars($galaData->getEvent('50Back')->getPrice()) ?>">
							<label class="custom-control-label" for="50Back">50 Backstroke <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('50Back')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
				<?php if ($galaData->getEvent('100Back')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="100Back" name="100Back" data-event-fee="<?= htmlspecialchars($galaData->getEvent('100Back')->getPrice()) ?>">
							<label class="custom-control-label" for="100Back">100 Backstroke <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('100Back')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
				<?php if ($galaData->getEvent('200Back')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="200Back" name="200Back" data-event-fee="<?= htmlspecialchars($galaData->getEvent('200Back')->getPrice()) ?>">
							<label class="custom-control-label" for="200Back">200 Backstroke <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('200Back')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
			</div>
			<div class="row mb-3">
				<?php if ($galaData->getEvent('100IM')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="100IM" name="100IM" data-event-fee="<?= htmlspecialchars($galaData->getEvent('100IM')->getPrice()) ?>">
							<label class="custom-control-label" for="100IM">100 IM <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('100IM')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
				<?php if ($galaData->getEvent('150IM')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="150IM" name="150IM" data-event-fee="<?= htmlspecialchars($galaData->getEvent('150IM')->getPrice()) ?>">
							<label class="custom-control-label" for="150IM">150 IM <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('150IM')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
				<?php if ($galaData->getEvent('200IM')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="200IM" name="200IM" data-event-fee="<?= htmlspecialchars($galaData->getEvent('200IM')->getPrice()) ?>">
							<label class="custom-control-label" for="200IM">200 IM <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('200IM')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
				<?php if ($galaData->getEvent('400IM')->isEnabled()) { ?>
					<div class="col-sm-4 col-md-2">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="1" class="custom-control-input" id="400IM" name="400IM" data-event-fee="<?= htmlspecialchars($galaData->getEvent('400IM')->getPrice()) ?>">
							<label class="custom-control-label" for="400IM">400 IM <span class="d-sm-block"><em>&pound;<?= htmlspecialchars($galaData->getEvent('400IM')->getPriceAsString()) ?></em></span></label>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>

		<p>
			Your entry fee is <strong>&pound;<span id="total-field" data-total="0" data-count="0">0.00</span></strong><span id="entries-field"></span>.
		</p>

	<?php } ?>

<?php } else if ($coachEnters && isset($_GET["galaID"]) && isset($_GET["swimmer"])) {

	/**
	 * This is a gala where the coach enters, so we will show
	 * the select available sessions interface.
	 */

	// Get swimmer info
	$getSwimmer = $db->prepare("SELECT MemberID id, MForename fn, MSurname sn, DateOfBirth dob, UserID parent FROM members WHERE MemberID = ? AND Tenant = ?");
	$getSwimmer->execute([
		$_GET['swimmer'],
		$tenant->getId()
	]);
	$swimmer = $getSwimmer->fetch(PDO::FETCH_ASSOC);

	if ($swimmer == null || ($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] == 'Parent' && $swimmer['parent'] != $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) {
		halt(404);
	}

	// Get gala info
	$getGala = $db->prepare("SELECT GalaFeeConstant flatfee, GalaFee fee, HyTek, `Description`, GalaName `name`, GalaVenue venue FROM galas WHERE GalaID = ? AND Tenant = ?");
	$getGala->execute([
		$_GET["galaID"],
		$tenant->getId()
	]);
	$gala = $getGala->fetch(PDO::FETCH_ASSOC);

	if ($gala == null) {
		halt(404);
	}

	$nowDate = new DateTime('now', new DateTimeZone('Europe/London'));

	$getSessions = $db->prepare("SELECT `Name`, `ID` FROM galaSessions WHERE Gala = ? ORDER BY `ID` ASC");
	$getSessions->execute([$_GET["galaID"]]);
	$sessions = $getSessions->fetchAll(PDO::FETCH_ASSOC);

	$getCanAttend = $db->prepare("SELECT `Session`, `CanEnter` FROM galaSessionsCanEnter ca INNER JOIN galaSessions gs ON ca.Session = gs.ID WHERE gs.Gala = ? AND ca.Member = ?");
	$getCanAttend->execute([
		$_GET["galaID"],
		$_GET['swimmer']
	]);

	$events = GalaEvents::getEvents();
	$galaData = new GalaPrices($db, $_GET["galaID"]);

	// Output
?>

	<?php if ($gala['Description'] || $gala['HyTek']) {

		$markdown = new ParsedownForMembership();
		$markdown->setSafeMode(false); ?>

		<h2>About this gala</h2>

		<?= $markdown->text($gala['Description']) ?>

		<?php if (bool($gala['HyTek'])) { ?>
			<p>This is a HyTek gala. Once your coach has selected your swims, you'll be sent an email asking you to provide times for each event.</p>
		<?php } ?>
	<?php } ?>

	<h2>Events and entry fees</h2>

	<p>We recommend you take a look at the events and prices for this gala before proceeding.</p>

	<p>
		<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#eventPrices" aria-expanded="false" aria-controls="eventPrices">
			Show events and prices <i class="fa fa-chevron-down" aria-hidden="true"></i>
		</button>
	</p>

	<div class="cell collapse" id="eventPrices">
		<p>The events available and their entry fees as follows;</p>

		<dl class="row mb-0">
			<?php foreach ($events as $key => $name) { ?>
				<?php if ($galaData->getEvent($key)->isEnabled()) { ?>
					<dt class="col-sm-3 col-lg-2"><?= $name ?></dt>
					<dd class="col-sm-3 col-lg-2">&pound;<?= htmlspecialchars($galaData->getEvent($key)->getPriceAsString()) ?></dd>
				<?php } ?>
			<?php } ?>
		</dl>

		<p>Make sure you're happy with these events and fees before you enter this gala.</p>
	</div>

	<h2>Select available sessions</h2>
	<p class="lead">Select sessions which <?= htmlspecialchars($swimmer['fn']) ?> will be able to swim at.</p>
	<p>Your coaches will use this information when making suggested entries to this gala.</p>

	<?php if ($sessions == null) { ?>
		<div class="alert alert-danger">
			<p class="mb-0"><strong>You cannot complete this form at this time.</strong></p>
			<p class="mb-0">Please contact your club.</p>
		</div>
		<div id="gala-info" data-enterable=<?= htmlspecialchars(json_encode(false)) ?>></div>
	<?php } else {
		$canAtt = $getCanAttend->fetchAll(PDO::FETCH_KEY_PAIR);
		$checked = [];
		for ($i = 0; $i < sizeof($sessions); $i++) {
			if (isset($canAtt[$sessions[$i]['ID']]) && $canAtt[$sessions[$i]['ID']]) {
				$checked[] = " checked ";
			} else {
				$checked[] = "";
			}
		}

	?>

		<div id="gala-info" data-enterable=<?= htmlspecialchars(json_encode(true)) ?>></div>

		<input type="hidden" name="is-select-sessions" value="1">

		<!--
		<h2><?= htmlspecialchars($swimmer['fn'] . ' ' . $swimmer['sn']) ?></h2>
		<p class="lead"><?= htmlspecialchars($swimmer['fn']) ?> is able to enter;</p>
		-->
		<div class="row">
			<?php for ($i = 0; $i < sizeof($sessions); $i++) { ?>
				<div class="col-sm-6 col-lg-4 col-xl-3">
					<div class="form-group">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" id="<?= $swimmer['id'] ?>-<?= $sessions[$i]['ID'] ?>" name="<?= $swimmer['id'] ?>-<?= $sessions[$i]['ID'] ?>" <?= $checked[$i] ?>>
							<label class="custom-control-label" for="<?= $swimmer['id'] ?>-<?= $sessions[$i]['ID'] ?>">
								<?= htmlspecialchars($sessions[$i]['Name']) ?>
							</label>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	<?php } ?>

	<p>
		You should pay for entries to this gala in the usual way. Your club has not provided guidance as to which payment methods are accepted, which would be displayed in place of this message. This system provides support for payments by card, account balance (paid off by direct debit or any other method supported by your club),
	</p>

<?php

} else {
	halt(404);
}
