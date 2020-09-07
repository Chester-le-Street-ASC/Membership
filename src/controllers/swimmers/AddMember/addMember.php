<?php

$db = app()->db;
$tenant = app()->tenant;

$today = new DateTime('now', new DateTimeZone('Europe/London'));

$squads = $db->prepare("SELECT SquadName, SquadID FROM squads WHERE Tenant = ? ORDER BY SquadFee DESC, SquadName ASC");
$squads->execute([
	$tenant->getId(),
]);

$pagetitle = "Add a member";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/swimmersMenu.php"; ?>

<div class="bg-light mt-n3 py-3 mb-3">
	<div class="container">

		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl('members')) ?>">Members</a></li>
				<li class="breadcrumb-item active" aria-current="page">New</li>
			</ol>
		</nav>

		<div class="row align-items-center">
			<div class="col">
				<h1>
					Add a member
				</h1>
				<p class="lead mb-0">
					Add a new club member to the system
				</p>
			</div>
		</div>
	</div>
</div>

<div class="container">
	<div class="row">
		<div class="col col-md-8">
			<?php
			if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) { ?>
				<?= $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'] ?>
			<?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
			} ?>
			<form method="post" class="needs-validation" novalidate>
				<div class="form-row">
					<div class="col-sm-4">
						<div class="form-group">
							<label for="forename">Forename</label>
							<input type="text" class="form-control" id="forename" name="forename" placeholder="Enter a forename" required>
							<div class="invalid-feedback">
								Please provide a forename.
							</div>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label for="middlenames">Middle Names</label>
							<input type="text" class="form-control" id="middlenames" name="middlenames" placeholder="Enter a middlename">
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label for="surname">Surname</label>
							<input type="text" class="form-control" id="surname" name="surname" placeholder="Enter a surname" required>
							<div class="invalid-feedback">
								Please provide a surname.
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label for="datebirth">Date of Birth</label>
					<input type="date" class="form-control" id="datebirth" name="datebirth" pattern="[0-9]{4}-[0-1]{1}[0-9]{1}-[0-3]{1}[0-9]{1}" placeholder="YYYY-MM-DD" required max="<?= htmlspecialchars($today->format('Y-m-d')) ?>" value="<?= htmlspecialchars($today->format('Y-m-d')) ?>">
					<div class="invalid-feedback">
						Please provide a valid date of birth, which is not in the future.
					</div>
				</div>
				<div class="form-row">
					<div class="col-sm-6">
						<div class="form-group">
							<label for="asa">Swim England Registration Number</label>
							<input type="test" class="form-control" id="asa" name="asa" aria-describedby="asaHelp" placeholder="Swim England Registration Numer">
							<small id="asaHelp" class="form-text text-muted">If a new member does not yet have a Swim England Number, leave this blank.</small>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="squad">Swim England Membership Category</label>
							<select class="custom-select" placeholder="Select a Category" id="cat" name="cat" required>
								<option value="0">Not a Swim England Member</option>
								<option value="1">Category 1</option>
								<option value="2" selected>Category 2</option>
								<option value="3">Category 3</option>
							</select>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label for="sex">Sex</label>
					<select class="custom-select" id="sex" name="sex" placeholder="Select" required>
						<option value="Male">Male</option>
						<option value="Female">Female</option>
					</select>
				</div>

				<!-- Squads -->
				<?php if ($squad = $squads->fetch(PDO::FETCH_ASSOC)) { ?>
					<p class="mb-2">
						Select <span id="member-name">member's</span> squads.
					</p>

					<div class="row">
						<?php do { ?>
							<div class="col-6 col-md-4 col-lg-3 mb-2">
								<div class="custom-control custom-checkbox">
									<input type="checkbox" class="custom-control-input" id="squad-check-<?= htmlspecialchars($squad['SquadID']) ?>" name="squad-<?= htmlspecialchars($squad['SquadID']) ?>" value="1">
									<label class="custom-control-label" for="squad-check-<?= htmlspecialchars($squad['SquadID']) ?>"><?= htmlspecialchars($squad['SquadName']) ?></label>
								</div>
							</div>
						<?php } while ($squad = $squads->fetch(PDO::FETCH_ASSOC)); ?>
					</div>

					<p class="text-muted mt-n2">
						<small>If this new member is not a member of any squads, you don't need to select any.</small>
					</p>
				<?php } ?>

				<div class="form-group">
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input" id="clubpays" name="clubpays" value="1" aria-describedby="cphelp">
						<label class="custom-control-label" for="clubpays">Club Pays?</label>
					</div>
					<small id="cphelp" class="form-text text-muted">Tick the box if this swimmer will not pay any squad or membership fees, eg if they are at a university.</small>
				</div>

				<div class="form-group">
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input" id="transfer" name="transfer" value="1" aria-describedby="transfer-help">
						<label class="custom-control-label" for="transfer">Transferring from another club?</label>
					</div>
					<small id="transfer-help" class="form-text text-muted">Tick the box if this swimmer is transferring from another swimming club - They will not be charged for Swim England membership fees. If it is almost a new Swim England membership year and this swimmer will not be completing membership renewal then leave the box unticked so they pay Swim England membership fees when registering.</small>
				</div>
				<?= SCDS\CSRF::write() ?>
				<button type="submit" class="btn btn-success">Add Member</button>

			</form>

		</div>
	</div>
</div>

<?php
$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();
