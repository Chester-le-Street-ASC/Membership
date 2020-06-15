<?php

$db = app()->db;

require 'GlobalHead.php';

$clubLogoColour = 'text-white logo-text-shadow';
$navTextColour = 'navbar-dark';

if (app()->tenant->getKey('SYSTEM_COLOUR') && getContrastColor(app()->tenant->getKey('SYSTEM_COLOUR'))) {
  $clubLogoColour = 'text-dark';
  $navTextColour = 'navbar-light';
}

$bg = "bg-white";
if (isset($customBackground) && $customBackground) {
  $bg = $customBackground;
}
?>

<body class="<?= $bg ?> account--body" <?php if (isset($pageHead['body'])) {
                                        foreach ($pageHead['body'] as $item) { ?> <?= $item ?> <?php }
                                                                                                                            } ?>>

  <div class="sr-only sr-only-focusable">
    <a href="#maincontent">Skip to main content</a>
  </div>

  <?php if (bool(env('IS_DEV'))) { ?>
  <div class="bg-warning bg-striped py-1">
    <div class="<?= $container_class ?>">
      <small><strong>DEVELOPMENT PLATFORM</strong> NOT FOR PRODUCTION USE</small>
    </div>
  </div>
  <?php } ?>

  <div class="d-print-none">

    <?php if (app()->tenant->getKey('EMERGENCY_MESSAGE_TYPE') != 'NONE' && app()->tenant->getKey('EMERGENCY_MESSAGE')) {
      $markdown = new ParsedownExtra();
    ?>
      <!-- Yes, this is quick and nasty, but it's an emergency -->
      <style>
        .text-white .emergency-message a {
          color: #fff;
          font-weight: bold;
          text-decoration: underline;
        }

        .text-body .emergency-message a {
          color: #212529;
          font-weight: bold;
          text-decoration: underline;
        }

        .text-white .emergency-message thead {
          color: #212529;
        }

        .emergency-message p:last-child {
          margin-bottom: 0 !important;
          padding-bottom: 0 !important;
        }
      </style>

      <div class="py-3 <?php if (app()->tenant->getKey('EMERGENCY_MESSAGE_TYPE') == 'DANGER') { ?>bg-danger text-white<?php } ?> <?php if (app()->tenant->getKey('EMERGENCY_MESSAGE_TYPE') == 'WARN') { ?>bg-warning text-body<?php } ?> <?php if (app()->tenant->getKey('EMERGENCY_MESSAGE_TYPE') == 'SUCCESS') { ?>bg-success text-white<?php } ?>">
        <div class="<?= $container_class ?> emergency-message">
          <?php try { ?>
            <?= $markdown->text(app()->tenant->getKey('EMERGENCY_MESSAGE')) ?>
          <?php } catch (Exception $e) { ?>
            <p>An emergency message has been set but cannot be rendered.</p>
          <?php } ?>
        </div>
      </div>
    <?php } ?>

    <noscript>
      <div class="bg-warning box-shadow py-3 d-print-none">
        <div class="<?= $container_class ?>">
          <p class="h2">
            <strong>
              JavaScript is disabled or not supported
            </strong>
          </p>
          <p>
            It looks like you've got JavaScript disabled or your browser does
            not support it. JavaScript is essential for our website to function
            properly so we recommend you enable it or upgrade to a browser which
            supports it as soon as possible. <strong><a class="text-dark" href="https://browsehappy.com/" target="_blank">Upgrade your browser
                today <i class="fa fa-external-link" aria-hidden="true"></i></a></strong>.
          </p>
          <p class="mb-0">
            If JavaScript is not supported by your browser, <?= app()->tenant->getKey('CLUB_NAME') ?>
            recommends you <strong><a class="text-dark" href="https://www.firefox.com">install Firefox by
                Mozilla</a></strong>.
          </p>
        </div>
      </div>
    </noscript>

    <?php if ($_SESSION['Browser']['Name'] == "Internet Explorer") { ?>
      <div class="bg-warning py-3 d-print-none">
        <div class="<?= $container_class ?>">
          <p class="h2">
            <strong>
              Internet Explorer is not supported
            </strong>
          </p>
          <p>
            It looks like you're using Internet Explorer which we no longer support so we recommend you upgrade to a new browser which we do support as soon as possible. <strong><a class="text-dark" href="http://browsehappy.com/" target="_blank">Upgrade your browser today <i class="fa fa-external-link" aria-hidden="true"></i></a></strong>.
          </p>
          <p class="mb-0">
            <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> recommends you <strong><a class="text-dark" href="https://www.firefox.com">install Firefox by Mozilla</a></strong>. Firefox has great protections for your privacy with built in features including tracking protection.
          </p>
        </div>
      </div>
    <?php } ?>

    <?php if (bool(env('IS_EVALUATION_COPY'))) { ?>
      <div class="bg-secondary text-white py-2 d-print-none">
        <div class="<?= $container_class ?>">
          <p class="mb-0">
            <strong>
              This is an evaluation copy of this software
            </strong>
          </p>
          <p class="mb-0">
            Your club is testing this system
          </p>
        </div>
      </div>
    <?php } ?>

    <?php if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserSimulation'])) { ?>
      <div class="bg-dark text-white py-2 d-print-none">
        <div class="<?= $container_class ?>">
          <p class="mb-0">
            <strong>
              You are in user simulation mode simulating <?=
                                                            $_SESSION['TENANT-' . app()->tenant->getId()]['UserSimulation']['SimUserName'] ?>
            </strong>
          </p>
          <p class="mb-0">
            <a href="<?= htmlspecialchars(autoUrl("users/simulate/exit")) ?>" class="text-white">
              Exit User Simulation Mode
            </a>
          </p>
        </div>
      </div>
    <?php } ?>

    <?php
    $edit_link = null;
    if ((!isset($people) || !$people) && isset($allow_edit_id)) {
      $edit_link = autoUrl("posts/" . $allow_edit_id . "/edit");
    } else if (isset($people) && isset($page_is_mine) && $people && $page_is_mine) {
      $edit_link = autoUrl("people/me");
    }

    if (isset($allow_edit) && $allow_edit && (($_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Parent" &&
      $_SESSION['TENANT-' . app()->tenant->getId()]['AccessLevel'] != "Coach" && $edit_link != null) || $page_is_mine)) { ?>
      <div class="bg-dark box-shadow py-2 d-print-none">
        <div class="<?= $container_class ?>">
          <p class="mb-0">
            <a href="<?= htmlspecialchars($edit_link) ?>" class="text-white">
              Edit this page
            </a>
          </p>
        </div>
      </div>
    <?php } ?>

    <?php

    $headerLink = autoUrl("");
    if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) && $_SESSION['TENANT-' . app()->tenant->getId()]['UserID'] % 2 == 0) {
      $headerLink = app()->tenant->getKey('CLUB_WEBSITE');
    }

    ?>

    <div class="membership-header">
      <div class="club-name-header <?php if (date("m") == "12") { ?>festive<?php } ?>" style="background: rgba(0, 0, 0, .075)">
        <div class="<?= $container_class ?>">
          <div class="row justify-content-between align-items-center py-3 mb-0 d-none d-md-flex">
            <div class="col-auto">
              <h1 class="mb-0">
                <a href="<?= htmlspecialchars($headerLink) ?>" class="<?= $clubLogoColour ?> text-decoration-none">
                  <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?>
                </a>
              </h1>
            </div>
            <?php if (app()->tenant->getKey('CLUB_WEBSITE')) { ?>
            <div class="col-auto">
              <a href="<?=htmlspecialchars(app()->tenant->getKey('CLUB_WEBSITE'))?>" class="btn btn-dark">Club website</a>
            </div>
            <?php } ?>
          </div>
        </div>
      </div>

      <?php if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['UserID']) || !user_needs_registration($_SESSION['TENANT-' . app()->tenant->getId()]['UserID'])) { ?>
        <div>
          <div class="<?= $container_class ?>">
            <div class="">
              <div class="">
                <nav class="navbar navbar-expand-lg <?= $navTextColour ?>
        d-print-none justify-content-between px-0" role="navigation">

                  <a class="navbar-brand d-lg-none" href="<?= htmlspecialchars(autoUrl("")) ?>">
                    <?= htmlspecialchars(app()->tenant->getKey('CLUB_NAME')) ?> Membership
                  </a>
                  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#chesterNavbar" aria-controls="chesterNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                  </button>

                  <?php include BASE_PATH . 'views/menus/main.php'; ?>
                </nav>
              </div>
            </div>
          </div>
        </div>
      <?php } ?>

    </div>

    <div id="maincontent"></div>

    <!-- END OF HEADERS -->
    <div class="mb-3"></div>

  </div>

  <?php if (!isset($_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) || !$_SESSION['TENANT-' . app()->tenant->getId()]['PWA']) { ?>
    <div class="have-full-height">
    <?php } else { ?>
      <div class="have-full-height">
      <?php } ?>