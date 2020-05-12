<?php

$db = app()->db;

$stylesheet = "";
try {
  $hash = file_get_contents(BASE_PATH . 'cachebuster.json');
  $hash = json_decode($hash);
  $hash = $hash->resourcesHash;
  $stylesheet = autoUrl('public/compiled/css/generic.' . $hash . '.min.css');
} catch (Exception $e) {
  $stylesheet = autoUrl('public/compiled/css/generic.css');
}

$bg = null;

header('Link: <' . autoUrl($stylesheet) . '>; rel=preload; as=style');

$container_class;
if (isset($fluidContainer) && $fluidContainer == true) {
  $container_class = "container-fluid";
} else {
  $container_class = "container";
}?>
<!DOCTYPE html>
<!--

Copyright Chris Heppell & Chester-le-Street ASC 2016 - 2018.
Bootstrap CSS and JavaScript is Copyright Twitter Inc 2011-2018
jQuery v3.1.0 is Copyright jQuery Foundation 2016

Designed by Chris Heppell, www.chrisheppell.uk

Chester-le-Street ASC
Swimming Club based in Chester-le-Street, North East England
https://github.com/Chester-le-Street-ASC/

web@chesterlestreetasc.co.uk

https://corporate.myswimmingclub.co.uk

Chester-le-Street ASC is a non profit unincorporated association.

-->
<html lang="en-gb">

<head>
  <meta charset="utf-8">
  <?php if (isset($pagetitle) && ($pagetitle != "" || $pagetitle != null))  { ?>
    <title><?=$pagetitle?></title>
  <?php } else { ?>
  <title>SCDS Membership</title>
  <?php } ?>
  <meta name="description"
    content="SCDS Membership helps clubs run more efficiently.">
  <meta name="viewport" content="width=device-width, initial-scale=1.0,
    user-scalable=no,maximum-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="apple-mobile-web-app-title" content="SCDS Membership">
  <meta name="format-detection" content="telephone=no">
  <meta name="googlebot" content="noarchive, nosnippet">
  <meta name="X-CLSW-System" content="Membership">
  <meta name="og:type" content="website">
  <meta name="og:locale" content="en_GB">
  <meta name="og:site_name" content="SCDS Membership">
  <link rel="manifest" href="<?=autoUrl("manifest.webmanifest")?>">
  <?php
    // Check if user has opted out of tracking or has DNT headers set before serving Google Analytics
    if (env('GOOGLE_ANALYTICS_ID') && (!$_SESSION['DisableTrackers'] && !(isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] == 1))) {
    ?>
  <meta name="X-SCDS-Membership-Tracking" content="yes">
  <script async>
  (function(i, s, o, g, r, a, m) {
    i['GoogleAnalyticsObject'] = r;
    i[r] = i[r] || function() {
      (i[r].q = i[r].q || []).push(arguments)
    }, i[r].l = 1 * new Date();
    a = s.createElement(o),
      m = s.getElementsByTagName(o)[0];
    a.async = 1;
    a.src = g;
    m.parentNode.insertBefore(a, m)
  })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');
  ga('create', '<?=htmlspecialchars(env('GOOGLE_ANALYTICS_ID'))?>', 'auto');
  <?php if (isset($_SESSION['LoggedIn'])) { ?>
  ga('set', 'userId', '<?=$_SESSION['UserID']?>');
  ga('send', 'event', 'authentication', 'user-id available');
  <?php } else { ?>
  ga('send', 'pageview');
  <?php } ?>
  </script>
  <?php } else { ?>
  <meta name="X-SCDS-Membership-Tracking" content="no">
  <?php } ?>
  <script src="https://js.stripe.com/v3/"></script>
  <link rel="stylesheet preload"
    href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,400i,700|Roboto+Mono|Merriweather:400,600">
  <link rel="stylesheet preload" href="<?=htmlspecialchars($stylesheet)?>">

  <!-- Generic icon -->
  <link rel="icon" href="<?=htmlspecialchars(autoUrl("public/img/corporate/scds.png"))?>">

  <!-- For iPhone 6 Plus with @3× display: -->
  <link rel="apple-touch-icon-precomposed" sizes="180x180"
    href="<?=autoUrl("public/img/corporate/icons/apple-touch-icon-180x180.png")?>">
  <!-- For iPad with @2× display running iOS ≥ 7: -->
  <link rel="apple-touch-icon-precomposed" sizes="152x152"
    href="<?=autoUrl("public/img/corporate/icons/apple-touch-icon-152x152.png")?>">
  <!-- For iPad with @2× display running iOS ≤ 6: -->
  <link rel="apple-touch-icon-precomposed" sizes="144x144"
    href="<?=autoUrl("public/img/corporate/icons/apple-touch-icon-144x144.png")?>">
  <!-- For iPhone with @2× display running iOS ≥ 7: -->
  <link rel="apple-touch-icon-precomposed" sizes="120x120"
    href="<?=autoUrl("public/img/corporate/icons/apple-touch-icon-120x120.png")?>">
  <!-- For iPhone with @2× display running iOS ≤ 6: -->
  <link rel="apple-touch-icon-precomposed" sizes="114x114"
    href="<?=autoUrl("public/img/corporate/icons/apple-touch-icon-114x114.png")?>">
  <!-- For the iPad mini and the first- and second-generation iPad (@1× display) on iOS ≥ 7: -->
  <link rel="apple-touch-icon-precomposed" sizes="76x76"
    href="<?=autoUrl("public/img/corporate/icons/apple-touch-icon-76x76.png")?>">
  <!-- For the iPad mini and the first- and second-generation iPad (@1× display) on iOS ≤ 6: -->
  <link rel="apple-touch-icon-precomposed" sizes="72x72"
    href="<?=autoUrl("public/img/corporate/icons/apple-touch-icon-72x72.png")?>">
  <!-- For non-Retina iPhone, iPod Touch, and Android 2.1+ devices: -->
  <link rel="apple-touch-icon-precomposed"
    href="<?=autoUrl("public/img/corporate/icons/apple-touch-icon.png")?>"><!-- 57×57px -->
  <!-- <link rel="mask-icon" href="https://www.chesterlestreetasc.co.uk/wp-content/themes/chester/img/chesterIcon.svg"
    color="#bd0000"> -->
  <script src="https://www.google.com/recaptcha/api.js"></script>

  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body class="<?=$bg?> account--body" <?php if (isset($pageHead['body'])) { foreach ($pageHead['body'] as $item) { ?> <?=$item?> <?php } } ?>>

  <div class="sr-only sr-only-focusable">
    <a href="#maincontent">Skip to main content</a>
  </div>

  <div class="d-print-none">

    <noscript>
      <div class="bg-warning box-shadow py-3 d-print-none">
        <div class="<?=$container_class?>">
          <p class="h2">
            <strong>
              JavaScript is disabled or not supported
            </strong>
          </p>
          <p>
            It looks like you've got JavaScript disabled or your browser does
            not support it. JavaScript is essential for our website to function
            properly so we recommend you enable it or upgrade to a browser which
            supports it as soon as possible. <strong><a class="text-dark" href="https://browsehappy.com/"
                target="_blank">Upgrade your browser
                today <i class="fa fa-external-link" aria-hidden="true"></i></a></strong>.
          </p>
          <p class="mb-0">
            If JavaScript is not supported by your browser, SCDS recommends you <strong><a class="text-dark" href="https://www.firefox.com">install Firefox by Mozilla</a></strong>.
          </p>
        </div>
      </div>
    </noscript>

    <?php if ($_SESSION['Browser']['Name'] == "Internet Explorer") {?>
    <div class="bg-warning py-3 d-print-none">
      <div class="<?=$container_class?>">
        <p class="h2">
          <strong>
            Internet Explorer is not supported
          </strong>
        </p>
        <p>
          It looks like you're using Internet Explorer which we no longer support so we recommend you upgrade to a new browser which we do support as soon as possible. <strong><a class="text-dark" href="http://browsehappy.com/" target="_blank">Upgrade your browser today <i class="fa fa-external-link" aria-hidden="true"></i></a></strong>.
        </p>
        <p class="mb-0">
          SCDS recommends you <strong><a class="text-dark" href="https://www.firefox.com">install Firefox by Mozilla</a></strong>. Firefox has great protections for your privacy with built in features including tracking protection.
        </p>
      </div>
    </div>
    <?php } ?>

    <div class="container">
      <div class="row align-items-center py-2">
        <div class="col-auto">
          <img src="<?=htmlspecialchars(autoUrl("public/img/corporate/scds.png"))?>" class="img-fluid rounded-top" style="height: 75px;">
        </div>
        <div class="col-auto d-none d-md-flex">
          <h1 class="mb-0">
            <span class="sr-only">SCDS </span>Membership Software
          </h1>
        </div>
      </div>

      <nav class="navbar navbar-expand-md navbar-dark rounded-bottom rounded-right" style="background: #9D005B;">
        <a class="navbar-brand d-md-none" href="#">Navbar</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item">
              <a class="nav-link" href="<?=htmlspecialchars(autoUrl(""))?>">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?=htmlspecialchars(autoUrl("clubs"))?>">Clubs</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="https://www.chesterlestreetasc.co.uk/support/onlinemembership/" target="_blank">Help</a>
            </li>
          </ul>
        </div>
      </nav>
    </div>

    <div id="maincontent"></div>

    <!-- END OF HEADERS -->
    <div class="mb-3"></div>
  
  </div>

  <div class="have-full-height">