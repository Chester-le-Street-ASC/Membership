<?php

// Do not reveal PHP when sending mail
ini_set('mail.add_x_header', 'Off');
ini_set('expose_php', 'Off');

$time_start = microtime(true);

$executionStartTime = microtime();

define('DS', DIRECTORY_SEPARATOR);
define('BASE_PATH', __DIR__ . DS);

$_SERVER['SERVER_PORT'] = 443;

require BASE_PATH .'vendor/autoload.php';
require "helperclasses/ClassLoader.php";

use Symfony\Component\DomCrawler\Crawler;
use GeoIp2\Database\Reader;

$app            = System\App::instance();
$app->request   = System\Request::instance();
$app->route     = System\Route::instance($app->request);

$route          = $app->route;

header("Feature-Policy: fullscreen 'self' https://youtube.com");
header("Referrer-Policy: strict-origin-when-cross-origin");
//header("Content-Security-Policy: default-src https:; object-src data: 'unsafe-eval'; script-src * 'unsafe-inline'; style-src https://www.chesterlestreetasc.co.uk https://account.chesterlestreetasc.co.uk https://fonts.googleapis.com 'unsafe-inline'");
//header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
header('Server: Chester-le-Magic');
header("Content-Security-Policy: block-all-mixed-content");
header('Expect-CT: enforce, max-age=30, report-uri="https://chesterlestreetasc.report-uri.com/r/d/ct/enforce"');

//halt(901);

/*
if (!(sizeof($_SESSION) > 0)) {
  $_SESSION['TARGET_URL'] = app('request')->curl;
}
*/

function currentUrl() {
  return app('request')->protocol . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

$db = null;
$link = null;

$get_group = '/club/{clubcode}';

if ($custom_domain_mode) {
  $get_group = '/';
}

$config_file = $config;
if (strlen($cookie_prefix) > 0) {
  define('COOKIE_PREFIX', $cookie_prefix);
} else {
  define('COOKIE_PREFIX', 'CLS-Membership-');
}

require $config_file;
require 'default-config-load.php';

// This code is required so cookies work in dev environments
$cookieSecure = 1;
if (app('request')->protocol == 'http') {
  $cookieSecure = 0;
}

session_start([
    //'cookie_lifetime' => 172800,
    'gc_maxlifetime'      => 86400,
    'cookie_httponly'     => 0,
    'gc_probability'      => 1,
    'use_only_cookies'    => 1,
    'cookie_secure'       => $cookieSecure,
    'use_strict_mode'     => 1,
    'sid_length'          => 128,
    'name'                => COOKIE_PREFIX . 'SessionId',
    'cookie_domain'       => $_SERVER['HTTP_HOST']
]);

if ($_SESSION['AccessLevel'] == "Admin") {
  //Show errors
  //===================================
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  //===================================
}

function halt(int $statusCode) {
  if ($statusCode == 200) {
    include "views/200.php";
  }
  else if ($statusCode == 400) {
    include "views/400.php";
  }
  else if ($statusCode == 401) {
    include "views/401.php";
  }
  else if ($statusCode == 403) {
    include "views/403.php";
  }
  else if ($statusCode == 404) {
    include "views/404.php";
  }
  else if ($statusCode == 503) {
    include "views/503.php";
  }
  else if ($statusCode == 0) {
    include "views/000.php";
  }
  else if ($statusCode == 900) {
    // Unavailable for Regulatory Reasons
    include "views/900.php";
  }
  else if ($statusCode == 901) {
    // Unavailable due to GDPR
    include "views/901.php";
  }
  else if ($statusCode == 902) {
    // Unavailable due to no GC API Key
    include "views/902.php";
  }
  else {
    include "views/500.php";
  }
  exit();
}
//$link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
//define("LINK", mysqli_connect($dbhost, $dbuser, $dbpass, $dbname));
//$link = LINK;

$link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
$db = new PDO("mysql:host=" . $dbhost . ";dbname=" . $dbname . "", $dbuser, $dbpass);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* check connection */
if (mysqli_connect_errno()) {
  halt(500);
}

require_once "database.php";

if ($_SERVER['HTTP_HOST'] == 'account.chesterlestreetasc.co.uk' && app('request')->method == "GET") {
  header("Location: " . autoUrl(ltrim(app('request')->path, '/')));
  die();
}

if (empty($_SESSION['LoggedIn']) && isset($_COOKIE[COOKIE_PREFIX . 'AutoLogin']) && $_COOKIE[COOKIE_PREFIX . 'AutoLogin'] != "") {
  $sql = "SELECT `UserID`, `Time` FROM `userLogins` WHERE `Hash` = ? AND `Time` >= ? AND `HashActive` = ?";

  $data = [
    $_COOKIE[COOKIE_PREFIX . 'AutoLogin'],
    date('Y-m-d H:i:s', strtotime("120 days ago")),
    1
  ];

  try {
    $query = $db->prepare($sql);
    $query->execute($data);
  } catch (PDOException $e) {
    //halt(500);
  }

  $row = $query->fetchAll(PDO::FETCH_ASSOC);
  if (sizeof($row) == 1) {
    $user = $row[0]['UserID'];
    $utc = new DateTimeZone("UTC");
    $time = new DateTime($row[0]['Time'], $utc);

    $sql = "SELECT * FROM `users` WHERE `UserID` = ?";

    try {
      $query = $db->prepare($sql);
      $query->execute([$user]);
    } catch (PDOException $e) {
      //halt(500);
    }

    $row = $query->fetchAll(PDO::FETCH_ASSOC);

    $row = $row[0];

    $_SESSION['Username'] = $row['Username'];
    $_SESSION['EmailAddress'] = $row['EmailAddress'];
    $_SESSION['Forename'] = $row['Forename'];
    $_SESSION['Surname'] = $row['Surname'];
    $_SESSION['UserID'] = $user;
    $_SESSION['AccessLevel'] = $row['AccessLevel'];
    $_SESSION['LoggedIn'] = true;

    $hash = hash('sha512', time() . $_SESSION['UserID'] . random_bytes(64));

    $sql = "UPDATE `userLogins` SET `Hash` = ? WHERE `Hash` = ?";
    try {
      $query = $db->prepare($sql);
      $query->execute([$hash, $_COOKIE[COOKIE_PREFIX . 'AutoLogin']]);
    } catch (PDOException $e) {
      halt(500);
    }

    $expiry_time = ($time->format('U'))+60*60*24*120;

    $user_info_cookie = json_encode([
      'Forename' => $row['Forename'],
      'Surname' => $row['Surname'],
      'Account' => $_SESSION['UserID'],
      'TopUAL'  => $row['AccessLevel']
    ]);

    setcookie(COOKIE_PREFIX . "UserInformation", $user_info_cookie, $expiry_time , "/", 'chesterlestreetasc.co.uk', true, false);
    setcookie(COOKIE_PREFIX . "AutoLogin", $hash, $expiry_time , "/", 'chesterlestreetasc.co.uk', true, false);
  }
}

$currentUser = null;
if (isset($_SESSION['UserID'])) {
  $currentUser = new User($_SESSION['UserID'], $db);
}

if ($_SESSION['LoggedIn'] && !isset($_SESSION['DisableTrackers'])) {
  $_SESSION['DisableTrackers'] = filter_var(getUserOption($_SESSION['UserID'], "DisableTrackers"), FILTER_VALIDATE_BOOLEAN);
}

$route->group($get_group, function($clubcode = "CLSE") {
  //$_SESSION['ClubCode'] = strtolower($code);

  $this->get('/auth/cookie/redirect', function() {
    //$target = urldecode($target);
    setcookie(COOKIE_PREFIX . "SeenAccount", true, 0, "/", 'chesterlestreetasc.co.uk', true, false);
    header("Location: https://www.chesterlestreetasc.co.uk");
  });

  // Password Reset via Link
  $this->get('/email/auth/{id}:int/{auth}', function($id, $auth) {
    global $link;
    require('controllers/myaccount/EmailUpdate.php');
  });

  $this->get('/notify/unsubscribe/{userid}/{email}/{list}', function($userid, $email, $list) {
    global $link;
    include 'controllers/notify/UnsubscribeHandlerAsk.php';
  });

  $this->get('/notify/unsubscribe/{userid}/{email}/{list}/do', function($userid, $email, $list) {
    global $link;
    include 'controllers/notify/UnsubscribeHandler.php';
  });

  $this->get('/timeconverter', function() {
    global $link;
    include 'controllers/conversionsystem/testing.php';
  });

  $this->get('/robots.txt', function() {
    header("Content-Type: text/plain");
    echo "User-agent: *\r\nDisallow: /webhooks/\r\nDisallow: /webhooks\r\nDisallow: /css\r\nDisallow: /js\r\nDisallow: /public\r\nDisallow: /files";
  });

  $this->get('/public/*/viewer', function() {
    $filename = $this[0];
    $type = 'public';
    require BASE_PATH . 'controllers/public/Viewer.php';
  });

  $this->get('/public/*', function() {
    $filename = $this[0];
    require BASE_PATH . 'controllers/PublicFileLoader.php';
  });

  $this->post('/timeconverter', function() {
    global $link;
    include 'controllers/conversionsystem/PostTesting.php';
  });

  $this->get('/reportanissue', function() {
    global $link;
    include 'controllers/help/ReportIssueHandler.php';
  });
  $this->post('/reportanissue', function() {
    global $link;
    include 'controllers/help/ReportIssuePost.php';
  });

  $this->group('/ajax', function() {
    global $link;
    include 'controllers/public/router.php';
  });

  $this->group('/about', function() {
    global $link;
    include 'controllers/about/router.php';
  });

  $this->get('/cc/{id}/{hash}/unsubscribe', function($id, $hash) {
    include 'controllers/notify/CCUnsubscribe.php';
  });

  $this->group('/services', function() {
    $this->get('/barcode-generator', function() {
      include 'controllers/barcode-generation-system/gen.php';
    });

    $this->get('/qr-generator', function() {
      include 'controllers/barcode-generation-system/qr.php';
    });

    include 'controllers/services/router.php';
  });

  if ($_SESSION['TWO_FACTOR']) {
    $this->group('/2fa', function() {
      $this->get('/', function() {
        include BASE_PATH . 'views/TwoFactorCodeInput.php';
      });

      $this->post('/', function() {
        include BASE_PATH . 'controllers/2fa/SubmitCode.php';
      });

      $this->get('/exit', function() {
        $_SESSION = [];
        unset($_SESSION);
        header("Location: " . autoUrl("login"));
      });

      $this->get('/resend', function() {
        include BASE_PATH . 'controllers/2fa/ResendCode.php';
      });
    });

    $this->get(['/', '/*'], function() {
      $_SESSION['TWO_FACTOR'] = true;
      header("Location: " . autoUrl("2fa"));
    });
  }

  $this->group('/oauth2', function() {

    $this->any('/authorize', function() {
      include 'controllers/oauth/AuthorizeController.php';
    });

    $this->any('/token', function() {
      include 'controllers/oauth/TokenController.php';
    });

    $this->get('/userinfo', function() {
      include 'controllers/oauth/UserDetails.php';
    });
  });

  if (empty($_SESSION['LoggedIn'])) {
    $this->post(['/'], function() {
      global $link;
    	include 'controllers/login-go.php';
    });

    // Home
    $this->get('/', function() {
      include "views/Welcome.php";
    });

    $this->get('/login', function() {
      if (empty($_SESSION['TARGET_URL'])) {
        $_SESSION['TARGET_URL'] = "";
      }
      include "views/Login.php";
    });

    // Register
    $this->get(['/register', '/register/family', '/register/family/{fam}:int/{acs}:key'], function($fam = null, $acs = null) {
      global $link;
      require('controllers/registration/register.php');
    });

    $this->group(['/register/ac'], function() {
      include 'controllers/registration/join-from-trial/router.php';
    });

    $this->post('/register', function() {
      global $link;
      require('controllers/registration/registration.php');
    });

    // Confirm Email via Link
    $this->get('/register/auth/{id}:int/new-user/{token}', function($id, $token) {
      global $link;
      require('controllers/registration/RegAuth.php');
    });

    // Locked Out Password Reset
    $this->get('/resetpassword', function() {
      global $link;
      require('controllers/forgot-password/request.php');
    });

    $this->post('/resetpassword', function() {
      global $link;
      require('controllers/forgot-password/request-action.php');
    });

    // Password Reset via Link
    $this->get('/resetpassword/auth/{token}', function($token) {
      global $link;
      require('controllers/forgot-password/reset.php');
    });

    $this->post('/resetpassword/auth/{token}', function($token) {
      global $link;
      require('controllers/forgot-password/reset-action.php');
    });

    $this->group('/payments/webhooks', function() {
      global $link;
      require('controllers/payments/webhooks.php');
    });

    $this->group('/webhooks', function() {
      global $link;
      require('controllers/webhooks/router.php');
    });

    $this->get('/notify', function() {
      global $link;
      include 'controllers/notify/Help.php';
    });

    $this->group('/people', function() {
      global $link;
      $people = true;
      include 'controllers/posts/router.php';
    });
/*
    $this->get('/files/*', function() {
      $filename = $this[0];
      if (!isset($_SERVER['PHP_AUTH_USER'])) {
        header('WWW-Authenticate: Basic realm="Use your normal account Email Address and Password"');
        halt(401);
      } else if (verifyUser($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
        require BASE_PATH . 'controllers/FileLoader.php';
      } else {
        header('WWW-Authenticate: Basic realm="Use your normal account Email Address and Password"');
        halt(401);
      }
    });
    */

    // Global Catch All send to login
    $this->any('/*', function() {
      $_SESSION['TARGET_URL'] = app('request')->path;
      header("Location: " . autoUrl("login"));
    });
  } else if (user_needs_registration($_SESSION['UserID'])) {
    $this->group('/renewal', function() {
      global $link;

      include 'controllers/renewal/router.php';
    });

    $this->group('/registration', function() {
      global $link;

      include 'controllers/registration/router.php';
    });

    $this->any(['/', '/*'], function() {
      header("Location: " . autoUrl("registration"));
    });
  } else {
    // Home

    if ($_SESSION['AccessLevel'] == "Parent") {
      $this->get('/', function() {
        global $link;
      	include 'controllers/ParentDashboard.php';
      });
    } else {
      $this->get('/', function() {
        global $link;
      	include 'controllers/NewDashboard.php';
      });
    }

    $this->get('/login', function() {
      header("Location: " . autoUrl(""));
    });

    $this->group('/myaccount', function() {
      global $link;
      include 'controllers/myaccount/router.php';
    });

    $this->group('/swimmers', function() {
      global $link;

      include 'controllers/swimmers/router.php';
    });

    $this->group('/squads', function() {
      global $link;

      include 'controllers/squads/router.php';
    });

    if ($_SESSION['AccessLevel'] != "Parent") {
      $this->group('/trials', function() {
        include 'controllers/trials/router.php';
      });
    }

    $this->group(['/posts', '/pages'], function() {
      global $link;

      include 'controllers/posts/router.php';
    });

    $this->group('/people', function() {
      global $link;
      $people = true;
      include 'controllers/posts/router.php';
    });

    $this->group('/registration', function() {
      global $link;

      include 'controllers/registration/router.php';
    });

    $this->group(['/attendance', '/registers'], function() {
      global $link;

      include 'controllers/attendance/router.php';
    });

    $this->group('/users', function() {
      global $link;

      include 'controllers/users/router.php';
    });

    $this->group('/galas', function() {
      global $link;

      include 'controllers/galas/router.php';
    });

    $this->group('/family', function() {
      global $db;
      require('controllers/family/router.php');
    });

    $this->group('/renewal', function() {
      global $link;

      include 'controllers/renewal/router.php';
    });

    $this->group('/registration', function() {
      global $link;

      include 'controllers/registration/router.php';
    });

    $this->group('/payments', function() {
      global $link;

      include 'controllers/payments/router.php';
    });

    $this->group('/notify', function() {
      global $link;
      include 'controllers/notify/router.php';
    });

    $this->group('/emergencycontacts', function() {
      global $link;

      include 'controllers/emergencycontacts/router.php';
    });

    $this->group('/webhooks', function() {
      global $link;

      $this->group('/payments', function() {
        global $link;

        include 'controllers/payments/webhooks.php';
      });
    });

    $this->group('/qualifications', function() {
      include 'controllers/qualifications/router.php';
    });

    $this->group('/admin', function() {
      include 'controllers/qualifications/AdminRouter.php';
    });

    // Log out
    $this->any(['/logout', '/logout.php'], function() {
      global $link;
      require('controllers/logout.php');
    });

    /*$this->any('test', function() {
      global $link;
      global $db;

      include BASE_PATH . 'views/header.php';

      echo myMonthlyFeeTable($link, 1);

      include BASE_PATH . 'views/footer.php';
    });*/

    if ($_SESSION['AccessLevel'] == "Admin") {
      $this->group('/phpMyAdmin', function() {
        $this->any(['/', '/*'], function() {
          include '/customers/9/d/e/chesterlestreetasc.co.uk/httpd.private/phpMyAdmin/' . $this[0];
        });
      });

      $this->get('/about:php', function() {
        echo phpinfo();
      });

      $this->get('/about:session', function() {
        pre($_SESSION);
      });

      $this->get('/about:server', function() {
        pre($_SERVER);
        pre($_ENV);
      });

      $this->get('/about:cookies', function() {
        pre($_COOKIE);
      });

      $this->get('/about:stopcodes/{code}:int', function($code) {
        halt((int) $code);
      });

      $this->get('/pdf-test', function() {
        include 'controllers/PDFTest.php';
      });

      $this->group('/db', function() {
        // Handle database migrations
        include 'controllers/db/router.php';
      });

      $this->get('/test', function() {
        notifySend("x", "A test", "Hello Christopher", "Chris Heppell", "clheppell1@sheffield.ac.uk", $from = ["Email" => "noreply@galas.uk", "Name" => "GALAS.UK"]);
      });
    }
  }

  $this->get('/files/*/viewer', function() {
    $filename = $this[0];
    $type = 'files';
    require BASE_PATH . 'controllers/public/Viewer.php';
  });

  $this->get('/files/*', function() {
    $filename = $this[0];
    require BASE_PATH . 'controllers/FileLoader.php';
  });

  // Global Catch All 404
  $this->any('/', function() {
    header("Location: " . autoUrl(""));
  });

  // Global Catch All 404
  $this->any('/*', function() {
    global $link;
    include 'views/404.php';
  });

});

$route->end();

// Close SQL Database Connections
mysqli_close($link);
$db = null;
