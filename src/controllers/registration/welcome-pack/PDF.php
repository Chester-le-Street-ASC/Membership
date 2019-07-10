<?php

$id = 2;

global $db;

$user = $db->prepare("SELECT Forename, Surname, EmailAddress FROM users WHERE UserID = ?");
$user->execute([$id]);

$swimmers = $db->prepare("SELECT MForename fn, MSurname sn, SquadName squad, SquadFee fee, SquadCoC, ClubPays exempt FROM members INNER JOIN squads ON squads.SquadID = members.SquadID WHERE members.UserID = ? ORDER BY fn ASC");
$swimmers->execute([$id]);
$swimmers = $swimmers->fetchAll(PDO::FETCH_ASSOC);

$email_info = $user->fetch(PDO::FETCH_ASSOC);

$pagetitle = env('CLUB_NAME') . " Welcome Pack";

ob_start();?>

<!DOCTYPE html>
<html>
  <head>
  <meta charset='utf-8'>
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,400i" rel="stylesheet" type="text/css">
  <style>
  .signature-box {
    padding: 5pt;
    margin-bottom: 16pt;
    border: 0.05cm solid #777;
    width: 8cm;
    height: 2cm;
    background: #fff;
  }
  .cell {
    padding: 10pt;
    background: #eee;
    margin: 0 0 16pt 0;
    display: block;
  }
  </style>
  <?php include BASE_PATH . 'helperclasses/PDFStyles/Main.php'; ?>
  <title><?=$pagetitle?></title>
  </head>
  <body>
    <?php include BASE_PATH . 'helperclasses/PDFStyles/Letterhead.php'; ?>

    <p>
      <?=date("d/m/Y")?>
    </p>

    <p>
      <strong><?=htmlspecialchars($email_info['Forename'] . " " . $email_info['Surname'])?></strong><br>
      Registered Parent/Carer
    </p>

    <div class="primary-box mb-3" id="title">
      <h1 class="mb-0">
        Welcome to <?=htmlspecialchars(env('CLUB_NAME'))?>
      </h1>
      <p class="lead">
        Your Welcome Pack
      </p>

      <p class="mb-0">
<strong>This welcome pack covers these swimmer<?php if (sizeof($swimmers) > 1) { ?>s<?php } ?>;</strong>
      </p>

      <ul class="mb-0 list-unstyled"> 
        <?php foreach ($swimmers as $s) { ?>
        <li><?=htmlspecialchars($s['fn'] . ' ' . $s['sn'])?>, <?=htmlspecialchars($s['squad'])?> Squad</li>
        <?php } ?>
      </ul>
    </div>

    <h2>
      What's in this welcome pack?
    </h2>

    <p>
      In this pack you'll find;
    </p>

    <ul>
      <li>Chairman's welcome</li>
      <li>Information about your squads</li>
      <li>Club Codes of Conduct (for you and your swimmers)</li>
      <li>Information about your fees</li>
      <li>Direct debit payment information</li>
      <li>What are galas?</li>
      <li>How to enter galas</li>
      <li>Welfare information</li>
      <li>More about club policies</li>
    </ul>

    <div class="page-break-after">
      <h2>
        First things first
      </h2>

      <p>
        If you haven't already done so, you'll need to set up your club account. Your account is an easy and secure way of managing your swimmers, gala (competition) entries, payments and more.
      </p>

      <p>
        If you haven't already done so, you'll need to finish setting up your club account. We've sent you an email containing instructions on how to do that. You'll be asked to;
      </p>

      <ul>
        <li>Create a password</li>
        <li>Confirm your email and sms options</li>
      </ul>

      <p class="mb-0">
        We'll then automatically log you in.
      </p>
    </div>

    <div class="page-break"></div>

    <h1>Chairman's Welcome</h1>

    <div class="page-break"></div>

    <h1>Your Squads</h1>

    <div class="page-break"></div>

    <h1>Codes of Conduct</h1>

    <p class="lead">Swimmers are required by Swimming England rules to agree to their squad's code of conduct.</p>

    <p>Please review each code of conduct with your swimmers and ensure you explain the implication if these codes to your swimmers.</p>

    <p>There is a section at the end of this document for you and your swimmers to sign. You only need to sign for swimmers under 18.</p>

    <?php foreach ($swimmers as $s) { ?>
    <h1>Code of Conduct for <?=htmlspecialchars($s['fn'])?></h1>
    <?=getPostContent($s['SquadCoC'])?>
    <?php } ?>

    <div class="page-break"></div>

    <h1>Information about your fees</h1>

    <p class="lead">You will pay squad fees on a monthly basis.</p>

    <?php if (env('IS_CLS') && sizeof($swimmers) > 2) { ?>
    <p>As you have <?=sizeof($swimmers)?> swimmers, you qualify for a reduction on your squad fees.</p>
    <?php } else if (bool(env('IS_CLS'))) { ?>
    <p>If you ever have three or more swimmers while at <?=htmlspecialchars(env('CLUB_NAME'))?>, you'll qualify for a discount on your monthly fees.</p>
    <?php } ?>
    <?php if (bool(env('IS_CLS'))) { ?>
    <p>Reductions are applied as follows;</p>
    <p>If you have 3 swimmers, we'll order your swimmers by monthly fee and give you a reduction of 20% on your lowest cost swimmer.</p>
    <p>If you have 4 or more swimmers, we'll order your swimmers by monthly fee and give you a reduction of 20% on your third lowest cost swimmer and 40% on all further swimmers.</p>
    <?php } ?>

    <h2>Squad Fees</h2>

    <p>Your squad fees are as follows;</p>

    <?php

    $monthlyFee = monthlyFeeCost($db, $id, "int")/100;
    $preDiscountTotal = 0;

    ?>

    <table>
      <thead>
        <tr>
          <td>
            Swimmer
          </td>
          <td>
            Squad
          </td>
          <td>
            Price/month
          </td>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($swimmers as $s) { ?>
        <tr>
          <td>
            <?=htmlspecialchars($s['fn'] . ' ' . $s['sn'])?>
          </td>
          <td>
            <?=htmlspecialchars($s['squad'])?>
          </td>
          <td>
            <?php if ($s['exempt']) { ?>
            Exempt
            <?php } else { ?>
            &pound;<?=number_format($s['fee'], 2)?>
            <?php $preDiscountTotal += $s['fee']; ?>
            <?php } ?>
          </td>
        </tr>
        <?php } ?>
        <?php if (bool(env('IS_CLS'))) { ?>
        <tr>
          <td></td>
          <td>
            <strong>Subtotal</strong>
          </td>
          <td>
            &pound;<?=number_format($preDiscountTotal, 2)?>
          </td>
        </tr>
        <tr>
          <td></td>
          <td>
            <strong>Discounts</strong>
          </td>
          <td>
            &pound;<?=number_format($preDiscountTotal - $monthlyFee , 2)?>
          </td>
        </tr>
        <?php } ?>
        <tr>
          <td></td>
          <td>
            <strong>Total</strong>
          </td>
          <td>
            &pound;<?=number_format($monthlyFee, 2)?>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="page-break"></div>

    <?php if (env('GOCARDLESS_ACCESS_TOKEN') != null) { ?>

    <h1>Direct Debit Payments</h1>

    <p class="lead">Squad fees at <?=htmlspecialchars(env('CLUB_NAME'))?> are paid by Direct Debit.</p>

    <p>You must register for Direct Debit payments by signing into your club account and following the instructions shown.</p>
    <p>
      When your swimmers change squads, your monthly direct debit will be automatically adjusted accordingly. Payments by Direct Debit are covered by the <a href="#payment-dd-guarantee">Direct Debit Guarantee</a>.
    </p>

    <p>
      Full help and support for payments by Direct Debit is available on the Membership System Support Website at <a href="https://www.chesterlestreetasc.co.uk/support/onlinemembership/">https://www.chesterlestreetasc.co.uk/support/onlinemembership/</a>. Help and Support Documentation is provided by Chester-le-Street ASC<?php if (!(bool(env('IS_CLS')))) { ?> to all clubs and users that use this service. If you need somebody to help you, please contact your own club in the first instance<?php } ?>.
    </p>

    <div class="row" id="payment-dd-guarantee">
      <div class="split-75">
        <h2>The Direct Debit Guarantee</h2>
      </div>
      <div class="split-25 text-right">
        <img src="<?=BASE_PATH?>public/img/directdebit/directdebit@3x.png" style="height:1cm;" class="mb-3" alt="Direct Debit Logo">
      </div>
    </div>
    <p>The Direct Debit Guarantee applies to payments made to <?=htmlspecialchars(env('CLUB_NAME'))?></p>

    <ul>
      <li>
        This Guarantee is offered by all banks and building societies that accept instructions to pay Direct Debits
      </li>
      <li>
        If there are any changes to the amount, date or frequency of your Direct Debit <?=htmlspecialchars(env('CLUB_NAME'))?> will notify you three working days in advance of your account being debited or as otherwise agreed. If you request <?=htmlspecialchars(env('CLUB_NAME'))?> to collect a payment, confirmation of the amount and date will be given to you at the time of the request
      </li>
      <li>
        If an error is made in the payment of your Direct Debit, by <?=htmlspecialchars(env('CLUB_NAME'))?> or your bank or building society, you are entitled to a full and immediate refund of the amount paid from your bank or building society
      </li>
        <ul>
          <li>
            If you receive a refund you are not entitled to, you must pay it back when <?=htmlspecialchars(env('CLUB_NAME'))?> asks you to
          </li>
        </ul>
      <li>
        You can cancel a Direct Debit at any time by simply contacting your bank or building society. Written confirmation may be required. Please also notify us.
      </li>
    </ul>

    <p>Payments are handled by <a href="https://gocardless.com/">GoCardless</a> on behalf of <?=htmlspecialchars(env('CLUB_NAME'))?>.</p>

    <div class="page-break"></div>

    <?php } ?>

    <h1>What are galas?</h1>

    <p>Thousands of swimming competitions take place in England for kids and adults every year and most include swimmers with disabilities. Just get involved.</p>

    <p>Swimmers must be at least 9 years old and be a Swim England Category 2 Member for most competitions in England. 8 year old Category 1 swimmers can compete in some special galas and some galas may have stricter minimum or maximum age restrictions.</p>

    <p>For most swimming competitions athletes are split into groups based on their age on 31 December that year. However, there are some competitions for which age groups are based on a swimmer’s age on the day of competition.</p>

    <p>They take place in either a 25m or a 50m pool. The 25m events are called short course and the 50m long course.</p>

    <h2>Key swimming competitions in England</h2>

    <p>Getting into the sport of swimming you will start competing at lower level Licensed Meets.</p>

    <p>As you progress you work your way to the bigger events. Here are some key English competitions. All these events are inclusive of swimmers who hold a current national or international classification for functional, visually impaired or intellectual disability (S1-S14).</p>

    <ol>
      <li>Weeks 2 – 9: English County Championships – the beginning of each calendar year is marked by the staging of the respective English County Championships as well as the Welsh Regional and Scottish District events. There is no requirement for these competitions to be held in a 50m pool so some counties chose to stage their in a short course pool and some in a long course pool. Age Groups: 10/11 Years, 12 Years, 13 Years, 14 Years, 15 Years, 16+ Years</li>
      <li>Weeks 14 – 22: English Regional Championships – the English Regional Championships take place during April or May, as do the Scottish and Welsh National Age Group Championships. Unlike the county events, the eight regional championships are all held in a long course pool. There is also a one year difference in the age groups and the addition of club relays. Age Groups: 11/12 Years, 13 Years, 14 Years, 15 Years, 16 Years, 17+ Years</li>
      <li>Weeks 29 – 33: Swim England National Summer Meet – our National Summer Meet takes place in the week after the British Summer Championships and is a long course event. The event uses the same qualification window and rankings as the British Summer Championships and is for the top ranked English swimmers who did not qualify for the British competition. Age Groups: 12/13 Years, 14 Years, 15 Years, 16/17 Years, 18+ Years</li>
      <li>Week 51: Swim England National Winter Meet – our National Winter Meet brings the calendar year to an end with Great Britain’s top swimmers battling it out in the short course pool.</li>
    </ol>

    <div class="page-break"></div>

    <h1>How to enter galas</h1>

    <div class="page-break"></div>

    <h1>Welfare Information</h1>

    <div class="page-break"></div>

    <h1>More about club policies</h1>

    <?php include BASE_PATH . 'helperclasses/PDFStyles/PageNumbers.php'; ?>
  </body>
</html>

<?php

$html = ob_get_clean();

// reference the Dompdf namespace
use Dompdf\Dompdf;

// instantiate and use the dompdf class
$dompdf = new Dompdf();
// set font dir here
$dompdf->set_option('font_dir', BASE_PATH . 'fonts/');

$dompdf->set_option('defaultFont', 'Open Sans');
$dompdf->set_option('defaultMediaType', 'all');
$dompdf->set_option("isPhpEnabled", true);
$dompdf->set_option('isRemoteEnabled',true);
$dompdf->set_option('isFontSubsettingEnabled', false);
$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

if (!isset($attachment)) {
  // Output the generated PDF to Browser
  header('Content-Description: File Transfer');
  header('Content-Type: application/pdf');
  header('Content-Disposition: inline');
  header('Expires: 0');
  header('Cache-Control: must-revalidate');
  header('Pragma: public');
  $dompdf->stream(str_replace(' ', '', $pagetitle) . ".pdf", ['Attachment' => 0]);
} else if ($attachment) {
  $pdfOutput = $dompdf->output();
}