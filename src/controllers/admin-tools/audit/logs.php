<?php

$tenant = app()->tenant;
$db = app()->db;

$start = 0;

$perPage = 20;

if (isset($_GET['page']) && ((int) $_GET['page']) != 0) {
  $page = (int) $_GET['page'];
  $start = ($page - 1) * $perPage;
} else {
  $page = 1;
}

$sql = $db->prepare("SELECT COUNT(*) FROM auditLogging INNER JOIN users ON auditLogging.User = users.UserID WHERE users.Tenant = ?");
$sql->execute([
  $tenant->getId()
]);
$numLogs  = $sql->fetchColumn();
$numPages = ((int)($numLogs / $perPage)) + 1;

if ($start > $numLogs) {
  halt(404);
}

$sql = $db->prepare("SELECT `Forename`, `Surname`, `UserID`, `Active`, `ID`, `Event`, `Description`, `Time` FROM `auditLogging` INNER JOIN `users` ON auditLogging.User = users.UserID WHERE users.Tenant = :tenant ORDER
BY `Time` DESC LIMIT :offset, :num");
$sql->bindValue(':tenant', $tenant->getId(), PDO::PARAM_INT);
$sql->bindValue(':offset', $start, PDO::PARAM_INT);
$sql->bindValue(':num', $perPage, PDO::PARAM_INT);
$sql->execute();

$row = $sql->fetch(PDO::FETCH_ASSOC);

$fluidContainer = true;
$pagetitle = "Logs - Audit Logs";

include BASE_PATH . 'views/header.php';

?>

<div class="container-fluid">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin")) ?>">Admin</a></li>
      <li class="breadcrumb-item"><a href="<?= htmlspecialchars(autoUrl("admin/audit")) ?>">Audit</a></li>
      <li class="breadcrumb-item active" aria-current="page">Logs</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col">
      <h1>Logs</h1>

      <?php if ($row) { ?>

        <ul class="list-group">
          <?php do {

            $time = new DateTime($row['Time'], new DateTimeZone('UTC'));
            $time->setTimezone(new DateTimeZone('Europe/London'));

          ?>
            <li class="list-group-item">
              <div class="row">
                <div class="col">
                  <strong><?= htmlspecialchars($row['Forename'] . ' ' . $row['Surname']) ?></strong>
                </div>
                <div class="col text-right">
                  <strong><?= htmlspecialchars($time->format('H:i:s d/m/Y')) ?></strong>
                </div>
              </div>
              <p class="mb-0 mono">
                <?= htmlspecialchars($row['Event']) ?>
              </p>
              <p class="mb-0">
                <?= htmlspecialchars($row['Description']) ?>
              </p>
            </li>
          <?php } while ($row = $sql->fetch(PDO::FETCH_ASSOC)); ?>
        </ul>


        <nav aria-label="Page navigation">
          <ul class="pagination">
            <?php if ($numLogs <= $perPage) { ?>
              <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
            <?php } else if ($numLogs <= 20) { ?>
              <?php if ($page == 1) { ?>
                <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page + 1 ?>"><?php echo $page + 1 ?></a></li>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page + 1 ?>">Next</a></li>
              <?php } else { ?>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page - 1 ?>">Previous</a></li>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page - 1 ?>"><?php echo $page - 1 ?></a></li>
                <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
              <?php } ?>
            <?php } else { ?>
              <?php if ($page == 1) { ?>
                <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page + 1 ?>"><?php echo $page + 1 ?></a></li>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page + 2 ?>"><?php echo $page + 2 ?></a></li>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page + 1 ?>">Next</a></li>
              <?php } else { ?>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page - 1 ?>">Previous</a></li>
                <?php if ($page > 2) { ?>
                  <li class="page-item"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page - 2 ?>"><?php echo $page - 2 ?></a></li>
                <?php } ?>
                <li class="page-item"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page - 1 ?>"><?php echo $page - 1 ?></a></li>
                <li class="page-item active"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page ?>"><?php echo $page ?></a></li>
                <?php if ($numLogs > $page * $perPage) { ?>
                  <li class="page-item"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page + 1 ?>"><?php echo $page + 1 ?></a></li>
                  <?php if ($numLogs > $page * $perPage + $perPage) { ?>
                    <li class="page-item"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page + 2 ?>"><?php echo $page + 2 ?></a></li>
                  <?php } ?>
                  <li class="page-item"><a class="page-link" href="<?php echo autoUrl("admin/audit/logs?page="); ?><?php echo $page + 1 ?>">Next</a></li>
                <?php } ?>
              <?php } ?>
            <?php } ?>
          </ul>
        </nav>

      <?php } else { ?>
        <div class="alert alert-info">
          <p class="mb-0">
            <strong>There are no log items to display</strong>
          </p>
        </div>
      <?php } ?>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->useFluidContainer();
$footer->render();
