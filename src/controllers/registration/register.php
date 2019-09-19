<?php

global $db;

global $systemInfo;
$privacy = $systemInfo->getSystemOption('PrivacyPolicy');

$Extra = new ParsedownExtra();
$Extra->setSafeMode(true);
$search  = array("\n##### ", "\n#### ", "\n### ", "\n## ", "\n# ");
$replace = array("\n###### ", "\n###### ", "\n##### ", "\n#### ", "\n### ");

$privacyPolicy = null;
if ($privacy != null && $privacy != "") {
  $privacyPolicy = $db->prepare("SELECT Content FROM posts WHERE ID = ?");
  $privacyPolicy->execute([$privacy]);
  $privacyPolicy = str_replace($search, $replace, $privacyPolicy->fetchColumn());
  if ($privacyPolicy[0] == '#') {
    $privacyPolicy = '##' . $privacyPolicy;
  }
}

$mode = "Default";
$fam_keys = null;
/*$fam_keys = [
  "FAM" => null,
  "KEY" => null
];*/

$_SESSION['RegistrationMode'] = $mode;

  $use_white_background = true;
  $pagetitle = "Register";
  $preventLoginRedirect = true;
  include BASE_PATH . "views/header.php";

?>
<div class="pb-3">
  <div class="container">
      <?php if (isset($_SESSION['RegistrationGoVerify'])) {
        echo $_SESSION['RegistrationGoVerify'];
        unset($_SESSION['RegistrationGoVerify']);
      }  else { ?>
      <!--<div class="alert alert-warning">
        <p class="mb-0">
          <strong>
            Registration is currently closed for maintenance
          </strong>
        </p>
        <p>
          We're working to improve this service and will reopen user
          registration as soon as possible.
        </p>

        <p class="mb-0">
          If you've just recieved a letter about getting registered today, don't
          worry. If you don't need to make gala entries there's no rush yet -
          We'll be back by tomorrow. You will need to ensure you register for
          this system and connect your swimmers to your account by 1 September
          2018 ahead of the new season.
        </p>
      </div>-->
      <h1>User Registration</h1>
      <p>We need a few details before we start.</p>
      <hr>
      <?php if (isset($_SESSION['ErrorState'])) {
        echo $_SESSION['ErrorState'];
        unset($_SESSION['ErrorState']);
      } ?>

      <div class="row">
        <div class="col-md-8 col-lg-6">
          <div class="alert alert-warning">
            <p class="mb-0">
              <strong>Stop. Are you a new parent?</strong>
            </p>
            <p>
              We'll be sending you an email in the next few days with instructions on how to get your account set up. Your swimmers will already be connected to your new account when you receive it.
            </p>
            
            <p class="mb-0">
              Until then please bear with us otherwise your registration at <?=htmlspecialchars(env('CLUB_NAME'))?> may be delayed.
            </p>
          </div>
        </div>
      </div>
      
      <form method="post" action="<?php echo autoUrl("register"); ?>" name="register" id="register" class="needs-validation" novalidate>

        <h2>About you</h2>
        <div class="row">
          <div class="col-md-8 col-lg-6">

            <div class="form-row">

              <div class="col">

                <div class="form-group">
                  <label for="forename">First Name</label>
                  <input class="form-control" type="text" name="forename"
                  id="forename" placeholder="First" required
                  value="<?=htmlspecialchars($_SESSION['RegistrationForename'])?>" autocomplete="given-name">
                  <div class="invalid-feedback">
                    You must provide a first name
                  </div>
                </div>

              </div>

              <div class="col">

                <div class="form-group">
                  <label for="surname">Last Name</label>
                  <input class="form-control" type="text" name="surname"
                  id="surname" placeholder="Last" required
                  value="<?=htmlspecialchars($_SESSION['RegistrationSurname'])?>" autocomplete="family-name">
                  <div class="invalid-feedback">
                    You must provide a surname
                  </div>
                </div>

              </div>

            </div>

            <div class="form-group">
              <label for="email">Email Address</label>
              <input class="form-control mb-0 text-lowercase" type="email" name="email" id="email-address" placeholder="yourname@example.com" required value="<?=htmlspecialchars($_SESSION['RegistrationEmail'])?>" autocomplete="email">
              <small id="emailHelp" class="form-text text-muted">
                Your email address will only be used inside <?=htmlspecialchars(env('CLUB_NAME'))?> and SCDS.
              </small>
              <div class="invalid-feedback">
                You must provide a valid email address
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-8 col-lg-6">
            <div class="form-group">
              <label for="mobile">Mobile Number</label>
              <input class="form-control" type="tel" pattern="\+{0,1}[0-9]*" name="mobile" id="mobile" placeholder="01234 567890" required value="<?=htmlspecialchars($_SESSION['RegistrationMobile'])?>" autocomplete="tel">
              <small id="mobileHelp" class="form-text text-muted">If you don't have a mobile, use your landline number.</small>
              <div class="invalid-feedback">
                You must provide a valid UK phone number
              </div>
            </div>
          </div>
        </div>

        <!--<h2>Password</h2>-->
        <div class="row">
          <div class="col-md-8 col-lg-6">
            <div class="form-row">
              <div class="col">
                <div class="form-group">
                  <label for="password1">Password</label>
                  <input class="form-control" type="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" aria-describedby="pwHelp" name="password1" id="password1" placeholder="Password" required autocomplete="new-password">
                  <small id="pwHelp" class="form-text text-muted">Use 8 characters or more, with at least one lowercase letter, at least one uppercase letter and at least one number</small>
                  <div class="invalid-feedback">
                    You must provide password that is at least 8 characters long with at least one lowercase letter, at least one uppercase letter and at least one number
                  </div>
                </div>
              </div>

              <div class="col">
                <div class="form-group">
                  <label for="password2">Confirm Password</label>
                  <input class="form-control" type="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" name="password2" id="password2" aria-describedby="pwConfirmHelp" placeholder="Password" required autocomplete="new-password">
                  <small id="pwConfirmHelp" class="form-text text-muted">Repeat your password</small>
                  <div class="invalid-feedback">
                    You must provide password that is at least 8 characters long with at least one lowercase letter, at least one uppercase letter and at least one number
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <h2>Notification Preferences</h2>

        <?php

        $email = $sms = "";
        if (isset($_SESSION['RegistrationEmailAuth'])) {
          $email = " checked ";
          unset($_SESSION['RegistrationEmailAuth']);
        }
        if (isset($_SESSION['RegistrationSmsAuth'])) {
          $sms = " checked ";
          unset($_SESSION['RegistrationSmsAuth']);
        } ?>

        <div class="row">
          <div class="col-md-8 col-lg-6">
            <div class="form-group">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input"
                name="emailAuthorise" id="emailAuthorise" value="1" <?=$email?>>
                <label class="custom-control-label" for="emailAuthorise">
                  I wish to recieve important email updates about my squads.
                  This includes emails about session cancellations.
                </label>
              </div>
            </div>

            <div class="form-group">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input"
                name="smsAuthorise" id="smsAuthorise" value="1" <?=$sms?>>
                <label class="custom-control-label" for="smsAuthorise">
                  I wish to recieve text message notifications
                </label>
              </div>
            </div>

            <p class="small">
              We will still need to send you notifications relating to your
              account from time to time.
            </p>

          </div>
        </div>

        <div class="cell">
          <p class="mb-0"><strong>Legal Stuff Applies</strong></p>
          <?php if ($privacyPolicy != null) { ?>
          <?=$Extra->text($privacyPolicy)?>
          <?php } else { ?>
          <p>
            In accordance with European Law, <?=htmlspecialchars(env('CLUB_NAME'))?>, Swim England and British Swimming are Data Controllers for the purposes of the General Data Protection Regulation.
          </p>
          <p>
            By proceeding you agree to our <a href="https://www.chesterlestreetasc.co.uk/policies/privacy/" target="_blank">Privacy Policy (this is an example policy)</a> and the use of your data by <?=htmlspecialchars(env('CLUB_NAME'))?>. Please note that you have also agreed to our use of you and your swimmer's data as part of your registration with the club and with British Swimming and Swim England (Formerly known as the ASA).
          </p>
          <p>
            We will be unable to provide this service for technical reasons if you do not consent to the use of this data.
          </p>
          <p class="mb-0">
            Contact a member of your committee if you have any questions or email <a href="mailto:support@chesterlestreetasc.co.uk">support@chesterlestreetasc.co.uk</a>.
          </p>
          <?php } ?>
        </div>
        <?=SCDS\CSRF::write()?>
        <input type="submit" class="btn btn-primary btn-lg" value="Register">
      </form>
      <?php } ?>
    </div>
  </div>
</div>

<script defer src="<?=autoUrl("public/js/NeedsValidation.js")?>"></script>

<?php include BASE_PATH . "views/footer.php";

unset($_SESSION['RegistrationUsername']);
unset($_SESSION['RegistrationForename']);
unset($_SESSION['RegistrationSurname']);
unset($_SESSION['RegistrationEmail']);
unset($_SESSION['RegistrationMobile']);
