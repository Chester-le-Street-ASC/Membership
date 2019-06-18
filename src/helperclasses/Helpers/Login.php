<?php

namespace CLSASC\Membership;

class Login {
  private $db;
  private $user;
  private $stayLoggedIn;
  private $noUserWarning;

  function __construct($db) {
    $this->db = $db;
  }

  public function setUser($user) {
    $this->user = $user;
    $checkExists = $this->db->prepare("SELECT COUNT(*) FROM users WHERE UserID = ?");
    $checkExists->execute([$user]);
    if ($checkExists->fetchColumn() == 0) {
      throw new Exception();
    }
  }

  public function stayLoggedIn($option = true) {
    $this->stayLoggedIn = $option;
  }

  public function preventWarningEmail($option = true) {
    $this->noUserWarning = $option;
  }

  public function login() {
    $getUserDetails = $this->db->prepare("SELECT EmailAddress, Forename, Surname, UserID, AccessLevel FROM users WHERE UserID = ?");
    $getUserDetails->execute([$this->user]);
    $details = $getUserDetails->fetch(PDO::FETCH_ASSOC);
    
    $_SESSION['EmailAddress'] = $details['EmailAddress'];
    $_SESSION['Forename'] = $details['Forename'];
    $_SESSION['Surname'] = $details['Surname'];
    $_SESSION['UserID'] = $details['UserID'];
    $_SESSION['AccessLevel'] = $details['AccessLevel'];
    $_SESSION['LoggedIn'] = 1;

    $currentUser = new User($_SESSION['UserID'], $this->db);

    $hash = hash('sha512', time() . $_SESSION['UserID'] . random_bytes(64));

    $geo_string = "Location Information Unavailable";

    try {
      $reader = new Reader(BASE_PATH . 'storage/geoip/GeoLite2-City.mmdb');
      $record = $reader->city(app('request')->ip());
      $city;
      if ($record->city->name != "") {
        $city = $record->city->name . ', ';
      }
      $subdivision;
      if ($record->mostSpecificSubdivision->name != "" && $record->mostSpecificSubdivision->name != $record->city->name) {
        $subdivision = $record->mostSpecificSubdivision->name . ', ';
      }
      $country;
      if ($record->country->name != "") {
        $country = $record->country->name;
      }

      $geo_string = $city . $subdivision . $country;
    } catch (AddressNotFoundException $e) {
      $geo_string = "Unknown Location";
    } catch (InvalidDatabaseException $e) {
      $geo_string = "Location Information Unavailable";
    } catch (Exception $e) {
      $geo_string = "Location Information Unavailable";
    }

    $sql = "INSERT INTO `userLogins` (`UserID`, `IPAddress`, `GeoLocation`, `Browser`, `Platform`, `Mobile`, `Hash`, `HashActive`, `Time`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $mobile = 0;

    $browser_details = new WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);

    $browser = $browser_details->browser->name . ' ' . $browser_details->browser->version->toString();

    if ($browser_details->isType('mobile')) {
      $mobile = 1;
    }

    $remember_me = 0;
    if ($this->stayLoggedIn) {
      $remember_me = 1;
    }

    $date = new DateTime('now', new DateTimeZone('UTC'));
    $dbDate = $date->format('Y-m-d H:i:s');

    $login_details = [
      $_SESSION['UserID'],
      app('request')->ip(),
      $geo_string,
      $browser,
      $browser_details->os->toString(),
      $mobile,
      $hash,
      $remember_me,
      $dbDate
    ];

    try {
      $query = $this->db->prepare($sql);
      $query->execute($login_details);
    } catch (PDOException $e) {
      halt(500);
    }

    $user_info_cookie = json_encode([
      'Forename' => $row['Forename'],
      'Surname' => $row['Surname'],
      'Account' => $_SESSION['UserID'],
      'TopUAL'  => $row['AccessLevel']
    ]);

    if (isset($_SESSION['LoginSec'])) {
      unset($_SESSION['LoginSec']);
    }

    $secure = true;
    if (app('request')->protocol == 'http') {
      $secure = false;
    }
    if (env('IS_CLS') != null && env('IS_CLS')) {
      setcookie(COOKIE_PREFIX . "UserInformation", $user_info_cookie, time()+60*60*24*120 , "/", 'chesterlestreetasc.co.uk', $secure, false);
    }
    setcookie(COOKIE_PREFIX . "AutoLogin", $hash, time()+60*60*24*120, "/", app('request')->hostname('request')->hostname, $secure, false);

    // Test if we've seen a login from here before
    $login_before_data = [
      $_SESSION['UserID'],
      app('request')->ip(),
      ucwords(app('request')->browser()),
      ucwords(app('request')->platform())
    ];

    $login_before = $this->db->prepare("SELECT COUNT(*) FROM `userLogins` WHERE `UserID` = ? AND `IPAddress` = ? AND `Browser` = ? AND `Platform` = ?");
    $login_before->execute($login_before_data);
    $login_before_count = $login_before->fetchColumn();

    if ($login_before_count == 1 && !$this->noUserWarning) {

      $subject = "New Account Login";
      $message = '<p>Somebody just logged into your ' . htmlspecialchars(env('CLUB_NAME')) . ' Account from ' . $browser . ', using a device running ' . $browser_details->os->toString() . ' we believe was located in ' . $geo_string . '*.</p><p>We haven\'t seen a login from this location and device before.</p><p>If this was you then you can ignore this email. If this was not you, please <a href="' . autoUrl("") . '">log in to your account</a> and <a href="' . autoUrl("myaccount/password") . '">change your password</a> as soon as possible.</p><p>Kind Regards, <br>The ' . htmlspecialchars(env('CLUB_NAME')) . ' Team</p><p class="text-muted small">* We\'ve estimated your location from your public IP Address. The location given may not be where you live.</p>';
      $notify = "INSERT INTO notify (`UserID`, `Status`, `Subject`, `Message`,
      `ForceSend`, `EmailType`) VALUES (?, 'Queued', ?, ?, 0, 'Security')";
      try {
        $this->db->prepare($notify)->execute([$_SESSION['UserID'], $subject, $message]);
      } catch (PDOException $e) {
        halt(500);
      }

    }
  }
}
