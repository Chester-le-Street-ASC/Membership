<?php

$fluidContainer = true;

global $db;

$getLinked = $db->prepare("SELECT linkedAccounts.ID, main.EmailAddress memail, main.UserID muid, main.AccessLevel mal, linked.EmailAddress lemail, linked.UserID luid, linked.AccessLevel lal FROM ((linkedAccounts INNER JOIN users main ON main.UserID = linkedAccounts.User) INNER JOIN users linked ON linked.UserID = linkedAccounts.LinkedUser) WHERE (linkedAccounts.User = ? OR linkedAccounts.LinkedUser = ?) AND Active = ?");
$getLinked->execute([$_SESSION['UserID'], $_SESSION['UserID'], 1]);
$linked = $getLinked->fetch(PDO::FETCH_ASSOC);

$pagetitle = "Linked Accounts";

include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">
  <div class="row justify-content-between">
    <aside class="col-md-3 d-none d-md-block">
      <?php
        $list = new \CLSASC\BootstrapComponents\ListGroup(file_get_contents(BASE_PATH . 'controllers/myaccount/ProfileEditorLinks.json'));
        echo $list->render('linked-accounts');
      ?>
    </aside>
    <div class="col-md-9">
      <main>
        <h1>Switch Accounts</h1>

        <p class="lead">Sign in to multiple accounts on all of your devices.</p>

        <?php if (isset($_SESSION['LinkedUserSuccess']) && $_SESSION['LinkedUserSuccess']) { ?>
        <div class="alert alert-success">
          We've sent an email to the account you requested to link. Please follow the confirmation link inside.
        </div>
        <?php unset($_SESSION['LinkedUserSuccess']); } ?>

        <?php if (isset($_SESSION['LinkedUserAlreadyExists']) && $_SESSION['LinkedUserAlreadyExists']) { ?>
        <div class="alert alert-warning">
          That account is already linked.
        </div>
        <?php unset($_SESSION['LinkedUserAlreadyExists']); } ?>

        <?php if (isset($_SESSION['LinkedAccountDeleteSuccess']) && $_SESSION['LinkedAccountDeleteSuccess']) { ?>
        <div class="alert alert-success">
          We've deleted the link between those accounts.
        </div>
        <?php unset($_SESSION['LinkedAccountDeleteSuccess']); } ?>

        <?php if (isset($_SESSION['LinkedAccountDeleteError']) && $_SESSION['LinkedAccountDeleteError']) { ?>
        <div class="alert alert-danger">
          We could not delete the link between those accounts.
        </div>
        <?php unset($_SESSION['LinkedAccountDeleteError']); } ?>

        <?php if ($linked == null) { ?>
        <div class="alert alert-warning">
          You have no linked accounts.
        </div>
        <?php } else { ?>
        <ul class="list-group mb-3">
          <?php do { ?>
          <?php if ($linked['muid'] != $_SESSION['UserID']) { ?>
          <li class="list-group-item">
            <div class="row align-items-center">
              <div class="col">
                <p class="text-truncate mb-0">
                  <strong>
                    <?=htmlspecialchars($linked['memail'])?>
                  </strong>
                </p>
                <p class="text-truncate mb-0">
                  <?=htmlspecialchars($linked['mal'])?> Account
                </p>
                <div class="d-lg-none mb-3"></div>
              </div>
              <div class="col-md-5 col-lg-3">
                <div class="form-row">
                  <div class="col-6 col-lg-12">
                    <a class="btn btn-block btn-outline-dark"
                      href="<?=autoUrl("my-account/linked-accounts/" . $linked['muid'] . "/switch")?>">
                      Switch<span class="d-none d-xl-inline"> to this</span>
                    </a>
                    <div class="d-none d-lg-block mb-1"></div>
                  </div>
                  <div class="col-6 col-lg-12">
                    <a class="btn btn-block btn-outline-danger"
                      href="<?=autoUrl("my-account/linked-accounts/" . $linked['ID'] . "/delete")?>"
                      onclick="return confirm('Are you sure you want to delete the link with <?=(htmlspecialchars($linked['memail']))?>?')">
                      Delete<span class="d-none d-xl-inline"> linked account</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </li>
          <?php } ?>
          <?php if ($linked['luid'] != $_SESSION['UserID']) { ?>
          <li class="list-group-item">
            <div class="row align-items-center">
              <div class="col">
                <p class="text-truncate mb-0">
                  <strong>
                    <?=htmlspecialchars($linked['lemail'])?>
                  </strong>
                </p>
                <p class="text-truncate mb-0">
                  <?=htmlspecialchars($linked['lal'])?> Account
                </p>
                <div class="d-lg-none mb-3"></div>
              </div>
              <div class="col-md-5 col-lg-3">
                <div class="form-row">
                  <div class="col-6 col-lg-12">
                    <a class="btn btn-block btn-outline-dark"
                      href="<?=autoUrl("my-account/linked-accounts/" . $linked['luid'] . "/switch")?>">
                      Sign in<span class="d-none d-xl-inline"> to this</span>
                    </a>
                    <div class="d-none d-lg-block mb-1"></div>
                  </div>
                  <div class="col-6 col-lg-12">
                    <a class="btn btn-block btn-outline-danger"
                      href="<?=autoUrl("my-account/linked-accounts/" . $linked['ID'] . "/delete")?>"
                      onclick="return confirm('Are you sure you want to delete the link with <?=(htmlspecialchars($linked['lemail']))?>?')">
                      Delete<span class="d-none d-xl-inline"> linked account</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </li>
          <?php } ?>
          <?php } while ($linked = $getLinked->fetch(PDO::FETCH_ASSOC)); ?>
        </ul>
        <?php } ?>

        <p>
          <a class="btn btn-primary" href="<?=autoUrl("my-account/linked-accounts/new")?>">Add new linked account</a>
        </p>
      </main>
    </div>
  </div>
</div>

<?php

include BASE_PATH . 'views/footer.php';