<?php

global $db;

$disabled = "";

$date = date("Y-m-d");
$insertPayment = $db->prepare("INSERT INTO paymentsPending (`Date`, `Status`, UserID, `Name`, Amount, Currency, PMkey, `Type`, MetadataJSON) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$markAsRefunded = $db->prepare("UPDATE galaEntries SET Refunded = ?, AmountRefunded = ? WHERE EntryID = ?");
$notify = $db->prepare("INSERT INTO notify (UserID, `Status`, `Subject`, `Message`, EmailType) VALUES (?, ?, ?, ?, ?)");

$getGala = $db->prepare("SELECT GalaName `name`, GalaFee fee, GalaVenue venue, GalaFeeConstant fixed FROM galas WHERE GalaID = ?");
$getGala->execute([$id]);
$gala = $getGala->fetch(PDO::FETCH_ASSOC);

if ($gala == null) {
	halt(404);
}

$getEntries = $db->prepare("SELECT 50Free, 100Free, 200Free, 400Free, 800Free, 1500Free, 50Back, 100Back, 200Back, 50Breast, 100Breast, 200Breast, 50Fly, 100Fly, 200Fly, 100IM, 150IM, 200IM, 400IM, MForename, MSurname, EntryID, Charged, FeeToPay, MandateID, userOptions.Value OptOut, EntryProcessed Processed, Refunded, AmountRefunded FROM (((((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) LEFT JOIN users ON members.UserID = users.UserID) LEFT JOIN paymentPreferredMandate ON users.UserID = paymentPreferredMandate.UserID) LEFT JOIN userOptions ON users.UserID = userOptions.User) WHERE galaEntries.GalaID = ? AND (userOptions.Option = 'GalaDirectDebitOptOut' OR userOptions.Option IS NULL	) AND Charged = ? AND EntryProcessed = ? ORDER BY MForename ASC, MSurname ASC");
$getEntries->execute([$id, '1', '1']);

$swimsArray = [
  '50Free' => '50&nbsp;Free',
  '100Free' => '100&nbsp;Free',
  '200Free' => '200&nbsp;Free',
  '400Free' => '400&nbsp;Free',
  '800Free' => '800&nbsp;Free',
  '1500Free' => '1500&nbsp;Free',
  '50Back' => '50&nbsp;Back',
  '100Back' => '100&nbsp;Back',
  '200Back' => '200&nbsp;Back',
  '50Breast' => '50&nbsp;Breast',
  '100Breast' => '100&nbsp;Breast',
  '200Breast' => '200&nbsp;Breast',
  '50Fly' => '50&nbsp;Fly',
  '100Fly' => '100&nbsp;Fly',
  '200Fly' => '200&nbsp;Fly',
  '100IM' => '100&nbsp;IM',
  '150IM' => '150&nbsp;IM',
  '200IM' => '200&nbsp;IM',
  '400IM' => '400&nbsp;IM'
];

while ($entry = $getEntries->fetch(PDO::FETCH_ASSOC)) {
  $hasNoDD = ($entry['MandateID'] == null) || ($entry['OptOut']);
	$count = 0;
	foreach($swimsArray as $colTitle => $text) {

		if ($entry[$colTitle]) {
			//<li>$text</li>
		}
	}

	try {
		$db->beginTransaction();

		$amount = (int) ($_POST[$entry['EntryID'] . '-refund']*100);

		$amountString = number_format($_POST[$entry['EntryID'] . '-refund'], 2);
		$totalString = number_format($_POST[$entry['EntryID'] . '-refund'] + ($entry['AmountRefunded']/2), 2);

		$name = 'REJECTIONS REFUND ' . $entry['MForename'] . ' ' . $entry['MSurname'] . '\'s Gala Entry into ' . $gala['name'] .  ' (Entry #' . $entry['EntryID'] . ')';

		$jsonArray = [
			"Name" => $name
		];
		$json = json_encode($jsonArray);

		$insertPayment->execute([
			$date,
			'Pending',
			$entry['UserID'],
			'Gala Entry (#' . $entry['EntryID'] . ')',
			$amount,
			'GBP',
			null,
			'Voucher',
			$json
		]);

		$markAsRefunded->execute([
			true,
			$amount + $entry['AmountRefunded'],
			$entry['EntryID']
		]);

		$message = '<p>We\'ve issued a refund for ' . htmlspecialchars($entry['MForename']) .  '\'s entry into ' . htmlspecialchars($gala['name']) . '.</p>';

		$message .= '<p>This refund is to the value of <strong>&pound;' . $amountString . '</strong>.</p>';

		if ($amount + $entry['AmountRefunded'] > $entry['AmountRefunded']) {
			$message .= '<p>Please note that this brings the total amount refunded for this gala to &pound;' . $totalString . '</p>';
		}

		if ($entry['MandateID'] != null && !$entry['OptOut']) {
			$message .= '<p>This refund has been applied as a credit to your club account. This means you will either;</p>';
			$message .= '<ul><li>If you have not paid the bill by direct debit for this gala yet, you will automatically charged the correct amount for ' . $gala['name'] . ' on your next bill as reductions will be applied automatically</li><li>If you have already paid the bill by direct debit for this gala, the credit applied to your account will give you a discount on next month\'s bill</li>/ul>';
		} else {
			$message .= '<p>As you don\'t pay your club fees by direct debit or have opted out of paying for galas by direct debit, you\'ll need to collect this refund from the treasurer or gala coordinator.</p>';
		}

		$message .= '<p>Kind Regards<br> The ' . htmlspecialchars(env('CLUB_NAME')) . ' Team</p>';

		$notify->execute([
			$entry['UserID'],
			'Queued',
			'Refund for Rejections: ' . $entry['MForename'] .  '\'s ' . $gala['name'] . ' Entry',
			$message,
			'Galas'
		]);

		$db->commit();
	} catch (Exception $e) {
		// A problem occured
		$db->rollBack();
		$_SESSION['ChargeUsersFailure'] = true;
	}
}

if (!isset($_SESSION['ChargeUsersFailure'])) {
	$_SESSION['ChargeUsersSuccess'] = true;
}
header("Location: " . currentUrl());