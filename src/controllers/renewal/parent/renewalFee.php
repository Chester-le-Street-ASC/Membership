<?php

$db = app()->db;


$user = $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'];
$partial_reg = isPartialRegistration();

$partial_reg_require_topup = false;
if ($partial_reg) {
	$sql = "SELECT COUNT(*) FROM `members` WHERE UserID = ? AND RR = 0 AND ClubPays = 0";
	try {
		$query = $db->prepare($sql);
		$query->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);
	} catch (PDOException $e) {
		halt(500);
	}
	if ($query->fetchColumn() == 1) {
		$partial_reg_require_topup = true;
	}
}

$month = (new DateTime('now', new DateTimeZone('Europe/London')))->format('m');

$discounts = json_decode(app()->tenant->getKey('MembershipDiscounts'), true);
$clubDiscount = $swimEnglandDiscount = 0;
if ($discounts != null && isset($discounts['CLUB'][$month])) {
	$clubDiscount = $discounts['CLUB'][$month];
}
if ($discounts != null && isset($discounts['ASA'][$month])) {
	$swimEnglandDiscount = $discounts['ASA'][$month];
}

$sql = $db->prepare("SELECT COUNT(*) FROM `members` WHERE `members`.`UserID` = ? AND `ClubPays` = '0'");
$sql->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);

$clubFee = $totalFeeDiscounted = $totalFee = 0;

$payingSwimmerCount = $sql->fetchColumn();

$clubFees = \SCDS\Membership\ClubMembership::create($db, $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'], $partial_reg);

$clubFee = $clubFees->getFee();

if ($partial_reg) {
	$sql = "SELECT * FROM members WHERE `members`.`UserID` = ? AND `members`.`RR` = 1";
} else {
	$sql = "SELECT * FROM members WHERE `members`.`UserID` = ?";
}
$getMembers = $db->prepare($sql);
$getMembers->execute([$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']]);

$member = $getMembers->fetchAll(PDO::FETCH_ASSOC);
$count = sizeof($member);

if ($clubDiscount > 0 && $renewal == 0) {
	$totalFee += $clubFee;
	$totalFeeDiscounted += $clubFee * (1 - ($clubDiscount / 100));
} else {
	$totalFee += $clubFee;
	$totalFeeDiscounted += $clubFee;
}

$asaFees = [];

$asa1 = app()->tenant->getKey('ASA-County-Fee-L1') + app()->tenant->getKey('ASA-Regional-Fee-L1') + app()->tenant->getKey('ASA-National-Fee-L1');
$asa2 = app()->tenant->getKey('ASA-County-Fee-L2') + app()->tenant->getKey('ASA-Regional-Fee-L2') + app()->tenant->getKey('ASA-National-Fee-L2');
$asa3 = app()->tenant->getKey('ASA-County-Fee-L3') + app()->tenant->getKey('ASA-Regional-Fee-L3') + app()->tenant->getKey('ASA-National-Fee-L3');

for ($i = 0; $i < $count; $i++) {
	if ($member[$i]['ASACategory'] == 1 && !$member[$i]['ClubPays']) {
		$asaFees[$i] = $asa1;
	} else if ($member[$i]['ASACategory'] == 2  && !$member[$i]['ClubPays']) {
		$asaFees[$i] = $asa2;
	} else if ($member[$i]['ASACategory'] == 3  && !$member[$i]['ClubPays']) {
		$asaFees[$i] = $asa3;
	}
	if ($member[$i]['RRTransfer']) {
		$totalFee += $asaFees[$i];
		// $totalFeeDiscounted += 0;
	} else if ($swimEnglandDiscount > 0 && $renewal == 0) {
		$totalFee += $asaFees[$i];
		$totalFeeDiscounted += $asaFees[$i] * (1 - ($swimEnglandDiscount / 100));
	} else {
		$totalFee += $asaFees[$i];
		$totalFeeDiscounted += $asaFees[$i];
	}
}

$clubFeeString = (string) (\Brick\Math\BigDecimal::of((string) $clubFee))->withPointMovedLeft(2)->toScale(2);
$totalFeeString = (string) (\Brick\Math\BigDecimal::of((string) $totalFee))->withPointMovedLeft(2)->toScale(2);

$pagetitle = "Your Renewal Fees";
$title = "Your Membership Renewal Fees";
if ($renewal == 0) {
	$pagetitle = "Your Registration Fees";
	$title = "Your Registration Fees";
}

include BASE_PATH . 'views/header.php';
include BASE_PATH . "views/renewalTitleBar.php";
?>

<div class="container">
	<form method="post">
		<h1>
			<?= $title ?>
		</h1>
		<p class="lead">
			There's just one more step to go. We now need you to confirm your membership <?php if ($renewal == 0) { ?>registration<?php } else { ?>renewal<?php } ?>.
		</p>

		<?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['StripeDDSuccess']) && $_SESSION['TENANT-' . app()->tenant->getId()]['StripeDDSuccess']) { ?>
			<div class="alert alert-success">
          <p class="mb-0">
            <strong>We've set up your new direct debit</strong>
          </p>
          <p class="mb-0">
            It will take a few days for the mandate to be confirmed at your bank.
          </p>
        </div>
		<?php unset($_SESSION['TENANT-' . app()->tenant->getId()]['StripeDDSuccess']); } ?>

		<p>
			These fees include your Swim England membership fees for the sport's governing bodies at National, Regional and County Level.
		</p>
		<?php $nf = "next";
		if ($renewal == 0) {
			$nf = "first";
		}; ?>
		<?php if (app()->tenant->getGoCardlessAccessToken()) { ?>
			<p>
				You will pay these fees as part of your <?= $nf ?> Direct Debit payment to <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>.
			</p>
		<?php } ?>

		<h2>Your Membership Fees</h2>
		<div class="table-responsive-md">
			<table class="table">
				<thead class="">
					<tr class="bg-primary text-light">
						<th>
							Club Membership
						</th>
						<th>
						</th>
					</tr>
				</thead>
				<thead class="thead-light">
					<tr>
						<th>
							Type
						</th>
						<th>
							Fee
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($clubFees->getFeeItems() as $item) { ?>
						<tr>
							<td>
								<?= htmlspecialchars($item['description']) ?>
							</td>
							<td>
								&pound;<?= number_format($item['amount'] / 100, 2, '.', '') ?>
							</td>
						</tr>
					<?php } ?>
					<?php if ($clubDiscount > 0 && $renewal == 0) { ?>
						<tr>
							<td>
								Discretionary discount at <?= htmlspecialchars($clubDiscount) ?>%
							</td>
							<td>
								-&pound;<?= number_format(((int)$clubFee * ($clubDiscount / 100) / 100), 2, '.', '') ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
				<!--</table>
		</div>
		<?php if ($payingSwimmerCount > 1) {
		?>
			<p class="lead"<?= number_format($totalFee - $totalFeeDiscounted, 2, '.', '') ?>>
				You <?php if ($renewal == 0) { ?>will <?php } ?>pay for a family membership, covering all of your swimmers at a reduced cost.
			</p>
			<?php
		} ?>
 		<p>Your club membership fee is &pound;<?= $clubFeeString ?></p>

		<h2>Swim England Membership Fees</h2>
		<div class="table-responsive-md">
			<table class="table">-->
				<thead class="">
					<tr class="bg-primary text-light">
						<th>
							Swim England Membership
						</th>
						<th>
						</th>
					</tr>
				</thead>
				<thead class="thead-light">
					<tr>
						<th>
							Swimmer
						</th>
						<th>
							Fee
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					for ($i = 0; $i < $count; $i++) {
						$asaFeesString;
						if ($member[$i]['ClubPays']) {
							$asaFeesString = "0.00 (Paid by club)";
						} else if (isset($asaFees[$i])) {
							$asaFeesString = (string) (\Brick\Math\BigDecimal::of((string) $asaFees[$i]))->withPointMovedLeft(2)->toScale(2);
						} else {
							$asaFeesString = "0.00 (No fee information)";
						}
					?>
						<tr>
							<td>
								<?= htmlspecialchars($member[$i]['MForename'] . " " . $member[$i]['MSurname']) ?>
							</td>
							<td>
								&pound;<?php echo $asaFeesString; ?>
							</td>
						</tr>
						<?php if ($member[$i]['RRTransfer']) { ?>
							<tr>
								<td>
									<?= htmlspecialchars($member[$i]['MForename'] . " " . $member[$i]['MSurname']) ?> (Swim England Membership Transfer Credit)
								</td>
								<td>
									-&pound;<?= $asaFeesString ?>
								</td>
							</tr>
						<?php } else if ($swimEnglandDiscount > 0 && $renewal == 0) { ?>
							<tr>
								<td>
									Discretionary discount at <?= htmlspecialchars($swimEnglandDiscount) ?>%
								</td>
								<td>
									-&pound;<?= number_format(((int)$asaFees[$i] * ($swimEnglandDiscount / 100)) / 100, 2, '.', '') ?>
								</td>
							</tr>
						<?php } ?>
					<?php } ?>
				</tbody>
				<tbody>
					<tr class="table-active">
						<td>
							Total Membership Fee
						</td>
						<td>
							&pound;<?= $totalFeeString ?>
						</td>
					</tr>
					<?php if (($swimEnglandDiscount > 0 || $clubDiscount > 0)	 && $renewal == 0) { ?>
						<tr class="table-active">
							<td>
								Total discounts
							</td>
							<td>
								-&pound;<?= number_format(((int)$totalFee - $totalFeeDiscounted) / 100, 2, '.', '') ?>
							</td>
						</tr>
						<tr class="table-active">
							<td>
								Total Membership Fee (with discounts)
							</td>
							<td>
								&pound;<?= number_format(((int)$totalFeeDiscounted) / 100, 2, '.', '') ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>

		<?php if (stripeDirectDebit(true)) { ?>
			<p>
				Your total <?php if ($renewal == 0) { ?>registration<?php } else { ?>renewal<?php } ?> fee will be &pound;<?php if (($swimEnglandDiscount > 0 || $clubDiscount > 0) && $renewal == 0) { ?><?= number_format(((int)$totalFeeDiscounted) / 100, 2, '.', '') ?><?php } else { ?><?= $totalFeeString ?><?php } ?>. By continuing to complete your membership <?php if ($renewal == 0) { ?>registration<?php } else { ?>renewal<?php } ?>, you confirm that you will pay this amount as part of your <?= $nf ?> Direct Debit Payment.
			</p>

			<?php if (app()->tenant->getKey('CUSTOM_SCDS_CLUB_CHARGE_DATE') && $renewal != 0) {
				$date = new DateTime(app()->tenant->getKey('CUSTOM_SCDS_CLUB_CHARGE_DATE'), new DateTimeZone('Europe/London'));
				$chargeDate = $date->format("j F Y");
				$date->modify('first day of next month');
				$debitDate = $date->format("F");
			?>
				<p><strong>Your club has overridden the charge date for club membership fees meaning the charge for your club membership fee will be added to your account on <?= htmlspecialchars($chargeDate) ?> and you will pay this charge as part of your <?= htmlspecialchars($debitDate) ?> Direct Debit.</strong></p>
			<?php } ?>

			<?php if (app()->tenant->getKey('CUSTOM_SCDS_ASA_CHARGE_DATE') && $renewal != 0) {
				$date = new DateTime(app()->tenant->getKey('CUSTOM_SCDS_ASA_CHARGE_DATE'), new DateTimeZone('Europe/London'));
				$chargeDate = $date->format("j F Y");
				$date->modify('first day of next month');
				$debitDate = $date->format("F");
			?>
				<p><strong>Your club has overridden the charge date for Swim England membership fees meaning the charge your Swim England membership fee will be added to your account on <?= htmlspecialchars($chargeDate) ?> and you will pay this charge as part of your <?= htmlspecialchars($debitDate) ?> Direct Debit.</strong></p>
			<?php } ?>

			<?php
			// Work out if has mandates
			$getCountNewMandates = $db->prepare("SELECT COUNT(*) FROM stripeMandates INNER JOIN stripeCustomers ON stripeMandates.Customer = stripeCustomers.CustomerID WHERE stripeCustomers.User = ? AND stripeMandates.MandateStatus != 'inactive';");
			$getCountNewMandates->execute([
				$_SESSION['TENANT-' . app()->tenant->getId()]['UserID']
			]);
			$hasStripeMandate = $getCountNewMandates->fetchColumn() > 0;

			if (!$hasStripeMandate) { ?>
				<p>
					We now need you to set up your Direct Debit agreement with <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>. We will redirect you to our payments system where you will setup a Direct Debit.
				</p>
			<?php } else { ?>
				<p>
					You're now ready to complete your <?php if ($renewal == 0) { ?>registration<?php } else { ?>renewal<?php } ?>.
				</p>
			<?php } ?>

			<p>
				<button type="submit" class="btn btn-success btn-lg">
					<?php if (!$hasStripeMandate) { ?>
						Setup Direct Debit
					<?php } else if ($renewal == 0) { ?>
						Complete Registration
					<?php } else { ?>
						Complete Renewal
					<?php } ?>
				</button>
			</p>

		<?php } else if (app()->tenant->getGoCardlessAccessToken()) { ?>
			<p>
				Your total <?php if ($renewal == 0) { ?>registration<?php } else { ?>renewal<?php } ?> fee will be &pound;<?php if (($swimEnglandDiscount > 0 || $clubDiscount > 0) && $renewal == 0) { ?><?= number_format(((int)$totalFeeDiscounted) / 100, 2, '.', '') ?><?php } else { ?><?= $totalFeeString ?><?php } ?>. By continuing to complete your membership <?php if ($renewal == 0) { ?>registration<?php } else { ?>renewal<?php } ?>, you confirm that you will pay this amount as part of your <?= $nf ?> Direct Debit Payment.
			</p>

			<?php if (app()->tenant->getKey('CUSTOM_SCDS_CLUB_CHARGE_DATE') && $renewal != 0) {
				$date = new DateTime(app()->tenant->getKey('CUSTOM_SCDS_CLUB_CHARGE_DATE'), new DateTimeZone('Europe/London'));
				$chargeDate = $date->format("j F Y");
				$date->modify('first day of next month');
				$debitDate = $date->format("F");
			?>
				<p><strong>Your club has overridden the charge date for club membership fees meaning the charge for your club membership fee will be added to your account on <?= htmlspecialchars($chargeDate) ?> and you will pay this charge as part of your <?= htmlspecialchars($debitDate) ?> Direct Debit.</strong></p>
			<?php } ?>

			<?php if (app()->tenant->getKey('CUSTOM_SCDS_ASA_CHARGE_DATE') && $renewal != 0) {
				$date = new DateTime(app()->tenant->getKey('CUSTOM_SCDS_ASA_CHARGE_DATE'), new DateTimeZone('Europe/London'));
				$chargeDate = $date->format("j F Y");
				$date->modify('first day of next month');
				$debitDate = $date->format("F");
			?>
				<p><strong>Your club has overridden the charge date for Swim England membership fees meaning the charge your Swim England membership fee will be added to your account on <?= htmlspecialchars($chargeDate) ?> and you will pay this charge as part of your <?= htmlspecialchars($debitDate) ?> Direct Debit.</strong></p>
			<?php } ?>

			<?php if (!userHasMandates($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) { ?>
				<p>
					We now need you to set up your Direct Debit agreement with <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>. We will redirect you to our payments system where you will setup a Direct Debit.
				</p>
			<?php } else { ?>
				<p>
					You're now ready to complete your <?php if ($renewal == 0) {
																						?>registration<?php } else { ?>renewal<?php } ?>.
				</p>
			<?php } ?>

			<p>
				<button type="submit" class="btn btn-success btn-lg">
					<?php if (!userHasMandates($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) { ?>
						Setup Direct Debit
					<?php } else if ($renewal == 0) { ?>
						Complete Registration
					<?php } else { ?>
						Complete Renewal
					<?php } ?>
				</button>
			</p>
		<?php } else { ?>
			<p>
				You'll need to pay &pound;<?php if (($swimEnglandDiscount > 0 || $clubDiscount > 0) && $renewal == 0) { ?><?= number_format(((int)$totalFeeDiscounted) / 100, 2, '.', '') ?><?php } else { ?><?= $totalFeeString ?><?php } ?> to your club as soon as possible. As <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> does not use Direct Debit payments, they will tell you how they would like you to pay.
			</p>
			<p>
				<button type="submit" class="btn btn-success btn-lg">
					<?php if ($renewal == 0) { ?>
						Complete Registration
					<?php } else { ?>
						Complete Renewal
					<?php } ?>
				</button>
			</p>
		<?php } ?>
	</form>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->render();
