<?php

$pagetitle = "Squad Rep Home";

global $db;

$today = (new DateTime('now', new DateTimeZone('Europe/London')))->format("y-m-d");
$getGalas = $db->prepare("SELECT GalaName, GalaID, GalaVenue FROM galas WHERE GalaDate >= ? ORDER BY GalaDate ASC");
$getGalas->execute([
  $today
]);
$gala = $getGalas->fetch(PDO::FETCH_ASSOC);

include BASE_PATH . 'views/header.php';

?>

<div class="front-page mb-n3">
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <h1>Welcome to Squad Rep Services</h1>
        <p class="lead">This service allows you to view gala entries and their payment status for your squads.</p>

        <div class="mb-4">
          <h2>
            Upcoming galas
          </h2>
          <?php if ($gala != null) { ?>
            <div class="news-grid">
            <?php do { ?>
              <a href="<?=autoUrl("galas/" . $gala['GalaID'] . "/squad-rep-view")?>">
                <span class="mb-3">
                  <span class="title mb-0">
                    <?=htmlspecialchars($gala['GalaName'])?>
                  </span>
                  <span>
                    <?=htmlspecialchars($gala['GalaVenue'])?>
                  </span>
                </span>
                <span class="category">
                  Galas
                </span>
              </a>
            <?php } while ($gala = $getGalas->fetch(PDO::FETCH_ASSOC)); ?>
            </div>
          <?php } else { ?>
            <div class="alert alert-warning">
              <p class="mb-0">
                <strong>
                  There are no upcoming galas
                </strong>
              </p>
              <p class="mb-0">
                Please check back later
              </p>
            </div>
          <?php } ?>
        </div>

        <div class="mb-4">
          <h2>
            Other services
          </h2>
          <div class="news-grid">
            <a href="<?=autoUrl("notify/newemail")?>">
              <span class="mb-3">
                <span class="title mb-0">
                  Email parents
                </span>
                <span>
                  Email parents of swimmers in your squads
                </span>
              </span>
              <span class="category">
                Notify
              </span>
            </a>
            <a href="<?=autoUrl("squad-reps/list")?>">
              <span class="mb-3">
                <span class="title mb-0">
                  View all squads reps
                </span>
                <span>
                  View a list of all squad reps
                </span>
              </span>
              <span class="category">
                Squad Reps
              </span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php

include BASE_PATH . 'views/footer.php';