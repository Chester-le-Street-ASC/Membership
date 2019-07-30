<?php

\Stripe\Stripe::setApiKey(env('STRIPE'));
if (env('STRIPE_APPLE_PAY_DOMAIN')) {
  \Stripe\ApplePayDomain::create([
    'domain_name' => env('STRIPE_APPLE_PAY_DOMAIN')
  ]);
}

global $db;

$expMonth = date("m");
$expYear = date("Y");

$customer = $db->prepare("SELECT CustomerID FROM stripeCustomers WHERE User = ?");
$customer->execute([$_SESSION['UserID']]);
$customerId = $customer->fetchColumn();

$numberOfCards = $db->prepare("SELECT COUNT(*) `count`, stripePayMethods.ID FROM stripePayMethods INNER JOIN stripeCustomers ON stripeCustomers.CustomerID = stripePayMethods.Customer WHERE User = ? AND Reusable = ? AND (ExpYear > ? OR (ExpYear = ? AND ExpMonth >= ?))");
$numberOfCards->execute([$_SESSION['UserID'], 1, $expYear, $expYear, $expMonth]);
$countCards = $numberOfCards->fetch(PDO::FETCH_ASSOC);

$getCards = $db->prepare("SELECT stripePayMethods.ID, `MethodID`, stripePayMethods.Customer, stripePayMethods.Name, stripePayMethods.Last4, stripePayMethods.Brand FROM stripePayMethods INNER JOIN stripeCustomers ON stripeCustomers.CustomerID = stripePayMethods.Customer WHERE User = ? AND Reusable = ? AND (ExpYear > ? OR (ExpYear = ? AND ExpMonth >= ?)) ORDER BY `Name` ASC");
$getCards->execute([$_SESSION['UserID'], 1, $expYear, $expYear, $expMonth]);
$cards = $getCards->fetchAll(PDO::FETCH_ASSOC);

$methodId = $customerID = null;

$selected = null;
if (isset($_SESSION['GalaPaymentMethodID'])) {
  $selected = $_SESSION['GalaPaymentMethodID'];

  foreach ($cards as $card) {
    if ($card['ID'] == $_SESSION['GalaPaymentMethodID']) {
      $methodId = $card['MethodID'];
      $customerID = $card['Customer'];
    }
  }
}

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

$rowArray = [1, null, null, null, null, 2, 1,  null, 2, 1, null, 2, 1, null, 2, 1, null, null, 2];
$rowArrayText = ["Freestyle", null, null, null, null, 2, "Breaststroke",  null, 2, "Butterfly", null, 2, "Freestyle", null, 2, "Individual Medley", null, null, 2];

$getEntry = $db->prepare("SELECT * FROM ((galaEntries INNER JOIN members ON galaEntries.MemberID = members.MemberID) INNER JOIN galas ON galaEntries.GalaID = galas.GalaID) WHERE EntryID = ? AND NOT Charged AND members.UserID = ?");

$hasEntries = false;
foreach ($_SESSION['PaidEntries'] as $entry => $details) {
  $getEntry->execute([$entry, $_SESSION['UserID']]);
  $entry = $getEntry->fetch(PDO::FETCH_ASSOC);
  if ($entry != null) {
    $hasEntries = true;
  }
}
if (!$hasEntries) {
  halt(404);
}

$entryRequestDetails = [];

if (!isset($_SESSION['PaidEntries'])) {
  halt(404);
}

$total = 0;

foreach ($_SESSION['PaidEntries'] as $entry => $details) {
  $total += $details['Amount'];
}

if ($total == 0) {
  header("Location: " . autoUrl("galas/pay-for-entries"));
  return;
}

$intent = null;

if (!isset($_SESSION['GalaPaymentIntent'])) {
  $intent = \Stripe\PaymentIntent::create([
    'amount' => $total,
    'currency' => 'gbp',
    'payment_method_types' => ['card'],
    'confirm' => false,
    'setup_future_usage' => 'off_session',
  ]);
  $_SESSION['GalaPaymentIntent'] = $intent->id;
} else {
  $intent = \Stripe\PaymentIntent::retrieve($_SESSION['GalaPaymentIntent']);
}

if ($intent->status == 'succeeded') {
  header("Location: " . autoUrl("payments/card-transactions"));
  return;
}

if ($methodId != null && $customerID != null) {
  $intent = \Stripe\PaymentIntent::update(
    $_SESSION['GalaPaymentIntent'], [
      'payment_method' => $methodId,
      'customer' => $customerID,
    ]
  );
}

if ($customerId != null) {
  $intent = \Stripe\PaymentIntent::update(
    $_SESSION['GalaPaymentIntent'], [
      'customer' => $customerId,
    ]
  );
}

if ($total != $intent->amount) {
  $intent = \Stripe\PaymentIntent::update(
    $_SESSION['GalaPaymentIntent'], [
      'amount' => $total,
    ]
  );
}

if (!isset($_SESSION['GalaPaymentMethodID'])) {
  $_SESSION['AddNewCard'] = true;
}

$countries = getISOAlpha2Countries();

$pagetitle = "Checkout";
include BASE_PATH . "views/header.php";
include BASE_PATH . "controllers/galas/galaMenu.php";
?>

<style>
/**
 * The CSS shown here will not be introduced in the Quickstart guide, but shows
 * how you can use CSS to style your Element's container.
 */
.card-element {
  box-sizing: border-box;

  /* height: 40px; */

  padding: 1rem;

  color: #333;
  font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
  font-size: 1rem;

  border: 1px solid #ced4da;

  background-color: white;

  box-shadow: none;
}
</style>
<?php if (bool(env('IS_CLS'))) { ?>
<style>
.card-element {
  border-radius: 0px;
}
</style>
<?php } ?>


<div class="container">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=autoUrl("galas")?>">Galas</a></li>
      <li class="breadcrumb-item active" aria-current="page">Pay for entries</li>
    </ol>
  </nav>
  
  <div class="row">
    <div class="col-lg-8">
      <h1>Pay for gala entries</h1>
      <p class="lead">You can pay for gala entries by direct debit or by credit or debit card.</p>

      <h2>Selected entries</h2>
      <p>You'll pay for the following gala entries</p>

      <ul class="list-group mb-3">
        <?php foreach ($_SESSION['PaidEntries'] as $entry => $details) {
          $getEntry->execute([$entry, $_SESSION['UserID']]);
          $entry = $getEntry->fetch(PDO::FETCH_ASSOC);
          $notReady = !$entry['EntryProcessed'];
          $entryRequestDetails[] = [
            'label' => htmlspecialchars($entry['MForename'] . ' ' . $entry['MSurname'][0]) . ' for ' . htmlspecialchars($entry['GalaName']),
            'amount' => $details['Amount']
          ];
        ?>
        <li class="list-group-item">
          <h3><?=htmlspecialchars($entry['MForename'] . ' ' . $entry['MSurname'])?> for <?=htmlspecialchars($entry['GalaName'])?></h3>
          <div class="row">
            <div class="col-sm-5 col-md-4 col-lg-6">
              <p class="mb-0">
                <?=htmlspecialchars($entry['MForename'])?> is entered in;
              </p>
              <ul class="list-unstyled">
              <?php $count = 0; ?>
              <?php foreach($swimsArray as $colTitle => $text) { ?>
                <?php if ($entry[$colTitle]) { $count++; ?>
                <li><?=$text?></li>
                <?php } ?>
              <?php } ?>
            </div>
            <div class="col text-right">
              <div class="d-sm-none mb-3"></div>
              <p>
                <?php if ($entry['GalaFeeConstant']) { ?>
                <?=$count?> &times; &pound;<?=htmlspecialchars(number_format($entry['GalaFee'], 2))?>
                <?php } else { ?>
                <strong><?=$count?> swims</strong>
                <?php } ?>
              </p>

              <?php if ($notReady) { ?>
              <p>
                This entry will be locked from editing when you pay.
              </p>
              <?php } ?>

              <p>
                <strong>Fee &pound;<?=htmlspecialchars(number_format($details['Amount']/100 ,2, '.', ''))?></strong>
              </p>
            </div>
          </div>
        </li>
        <?php } ?>
        <li class="list-group-item">
          <div class="row align-items-center">
            <div class="col-6">
              <p class="mb-0">
                <strong>Total to pay</strong>
              </p>
            </div>
            <div class="col text-right">
              <p class="mb-0">
                <strong>&pound;<?=htmlspecialchars(number_format($total/100 ,2, '.', ''))?></strong>
              </p>
            </div>
          </div>
        </li>
      </ul>

      <h2 class="mb-3">Payment Method</h2>
      <p>Choose how to pay</p>
        <div id="payment-request-card">
          <div class="card mb-3">
            <form>
              <div class="card-header" id="device-title">
                Pay quickly and securely
              </div>
              <div class="card-body">
                <div id="alert-placeholder"></div>
                <div id="payment-request-button">
                  <!-- A Stripe Element will be inserted here. -->
                </div>
              </div>
            </form>
          </div>

          <p class="text-center">Or</p>
        </div>

        <?php if (sizeof($cards) > 0) { ?>
        <div class="card mb-3" id="saved-cards">
          <form action="<?=autoUrl("galas/pay-for-entries/switch-method")?>" method="post" id="saved-card-form">
            <div class="card-header" id="device-title">
              Pay with a saved card
            </div>
            <div class="card-body">

              <div class="form-group <?php if ($selected == null) { ?>mb-0<?php } ?>">
                <label for="method">Payment card</label>
                <select class="custom-select" name="method" id="method" onchange="this.form.submit()">
                  <option value="select">Select a payment card</option>
                  <?php foreach ($cards as $card) { ?>
                  <option value="<?=$card['ID']?>" <?php if ($selected == $card['ID']) { $methodId = $card['MethodID']; ?>selected<?php } ?>>
                    <?=$card['Name']?> (<?=htmlspecialchars(getCardBrand($card['Brand']))?> ending <?=htmlspecialchars($card['Last4'])?>)
                  </option>
                  <?php } ?>
                </select>
              </div>

              <noscript>
                <p>
                  <button type="submit" class="btn btn-success">
                    Use selected card
                  </button>
                </p>
              </noscript>

              <?php if ($selected != null) { ?>
              <!-- Used to display form errors. -->
              <div id="saved-card-errors" role="alert"></div>

              <p class="mb-0">
                <button id="saved-card-button" class="btn btn-success btn-block" type="button" data-secret="<?= $intent->client_secret ?>">
                  Pay now
                </button>
              </p>
              <?php } ?>
            </div>
          </form>
        </div>

        <p class="text-center">Or</p>
        <?php } ?>

        <div class="card mb-3">
          <div class="card-header">
            Pay with a new card
          </div>
          <div class="card-body">
            <form id="new-card-form">
              <div class="form-group">
                <label for="new-cardholder-name">Cardholder name</label>
                <input type="text" class="form-control" id="new-cardholder-name" placeholder="C F Frost" required autocomplete="cc-name" aria-describedby="new-cardholder-name-help">
                <small id="new-cardholder-name-help" class="form-text text-muted">The name shown on your card</small>
              </div>

              <div class="form-group">
                <label for="addr-line-1">Address line 1</label>
                <input type="text" class="form-control" id="addr-line-1" placeholder="1 Burns Green" required autocomplete="address-line1">
              </div>

              <div class="form-group">
                <label for="addr-post-code">Post Code</label>
                <input type="text" class="form-control text-uppercase" id="addr-post-code" placeholder="NE99 1AA" required autocomplete="postal-code">
              </div>

              <div class="form-group">
                <label for="addr-post-code">Country</label>
                <select class="custom-select" required id="addr-country" autocomplete="country">
                  <?php foreach ($countries as $code => $name) { ?>
                  <option <?php if ($code == 'GB') { ?>selected<?php } ?> value="<?=htmlspecialchars($code)?>"><?=htmlspecialchars($name)?></option>
                  <?php } ?>
                </select>
              </div>

              <!-- placeholder for Elements -->
              <div class="form-group">
                <label for="card-element">
                  Credit or debit card
                </label>
                <div id="card-element" class="card-element">
                  <!-- A Stripe Element will be inserted here. -->
                </div>
              </div>

              <!--
              <div class="form-group">
                <div class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" id="reuse-card" name="reuse-card" checked>
                  <label class="custom-control-label" for="reuse-card">Save this card for future payments</label>
                </div>
              </div>
              -->

              <p>Your card details will be saved for use with future purchases</p>

              <!-- Used to display form errors. -->
              <div id="new-card-errors" role="alert"></div>

              <p class="mb-0">
                <button id="new-card-button" class="btn btn-success btn-block" type="submit" data-secret="<?= $intent->client_secret ?>">
                  Pay now
                </button>
              </p>
            </form>
          </div>
        </div>
    </div>
  </div>
</div>

<script src="<?=autoUrl("js/gala-checkout.js")?>"></script>


<?php

include BASE_PATH . "views/footer.php"; ?>