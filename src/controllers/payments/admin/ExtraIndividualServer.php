<?php

$db = app()->db;
$tenant = app()->tenant;

if ($_POST['response'] == "getSwimmers") {
  $swimmers = $db->prepare("SELECT * FROM (((`extrasRelations` INNER JOIN `members` ON members.MemberID = extrasRelations.MemberID) INNER JOIN `extras` ON extras.ExtraID = extrasRelations.ExtraID) INNER JOIN `squads` ON members.SquadID = squads.SquadID) WHERE `extrasRelations`.`ExtraID` = ? AND extras.Tenant = ?");
  $swimmers->execute([
    $id,
    $tenant->getId()
  ]);

  $row = $swimmers->fetch(PDO::FETCH_ASSOC);

  ?>

  <div class="">
    <?php if ($row != null) { ?>
    <div class="card">
      <div class="card-header">
        Extra members
      </div>
      <ul class="list-group list-group-flush">
        <?php do { ?>
        <li class="list-group-item">
          <div class="row align-items-center">
            <div class="col-auto">
              <p class="mb-0">
                <strong>
                  <?php echo $row['MForename'] . " " . $row['MSurname']; ?>
                </strong>
              </p>
              <p class="mb-0">
                <?php echo $row['SquadName']; ?>
              </p>
            </div>
            <div class="col text-right">
              <button type="button" id="RelationDrop-<?php echo $row['RelationID']; ?>"
                class="btn btn-link" value="<?php echo $row['RelationID']; ?>">
                Remove
              </button>
            </div>
          </div>
        </li>
        <?php } while ($row = $swimmers->fetch(PDO::FETCH_ASSOC)); ?>
      </ul>
    </div>
    <?php } else { ?>
    <div class="alert alert-info mb-0">
      <strong>There are no swimmers linked to this extra</strong>
    </div>
    <?php } ?>
  </div>
<?php
} else if ($_POST['response'] == "squadSelect") {

  if ($_POST['squadSelect'] == 'Choose...') {
    echo json_encode([
      'state' => false,
      'swimmerSelectContent' => '<option value="null" selected>Please select a squad</option>'
    ]);
  } else {
    $getSwimmers = $db->prepare("SELECT members.MemberID, MForename, MSurname FROM `members` WHERE members.Tenant = ? AND SquadID = ? AND MemberID NOT IN (SELECT MemberID FROM extrasRelations WHERE ExtraID = ?) ORDER BY `MForename` ASC, `MSurname` ASC ");
    $getSwimmers->execute([
      $tenant->getId(),
      $_POST['squadSelect'],
      $id
    ]);
    $state = false;
    $output = '<option value="null" selected>Select a swimmer</option>';
    while ($row = $getSwimmers->fetch(PDO::FETCH_ASSOC)) {
      $output .= '<option value="' . htmlspecialchars($row['MemberID']) . '">' . htmlspecialchars($row['MForename'] . " " . $row['MSurname']) . '</option>';
      $state = true;
    }
    echo json_encode([
      'state' => $state,
      'swimmerSelectContent' => $output
    ]);
  }
} else if ($_POST['response'] == "insert") {

  $responseData = [];

  $swimmer = $_POST['swimmerInsert'];
  if ($swimmer != null && $swimmer != "") {
    try {
      $memberName = $db->prepare("SELECT MForename fn, MSurname sn FROM members WHERE MemberID = ? AND Tenant = ?");
      $memberName->execute([
        $swimmer,
        $tenant->getId()
      ]);
      $name = $memberName->fetch(PDO::FETCH_ASSOC);
      

      if (!$name) {
        throw new Exception('There is no such member');
      }

      // Check not already there
      $getCount = $db->prepare("SELECT COUNT(*) FROM `extrasRelations` WHERE ExtraID = ? AND MemberID = ?");
      $getCount->execute([$id, $swimmer]);
      if ($getCount->fetchColumn() > 0) {
        throw new Exception($name['fn'] . ' ' . $name['sn'] . ' is already assigned to this extra');
      } else {
        $addToExtra = $db->prepare("INSERT INTO `extrasRelations` (`ExtraID`, `MemberID`) VALUES (?, ?)");
        $addToExtra->execute([$id, $swimmer]);

        $memberName = $db->prepare("SELECT MForename fn, MSurname sn FROM members WHERE MemberID = ?");
        $memberName->execute([$swimmer]);

        $responseData = [
          'alertClass' => 'alert-success',
          'alertContent' => '<p class="mb-0"><strong>' . htmlspecialchars($name['fn'] . ' ' . $name['sn']) . ' added to extra</strong></p>',
          'status' => true
        ];
      }
    } catch (Exception $e) {
      $responseData = [
        'alertClass' => 'alert-danger',
        'alertContent' => '<p class="mb-0"><strong>' . htmlspecialchars($e->getMessage()) . '</strong></p>',
        'status' => false
      ];
    }

    echo json_encode($responseData);

  }
} else if ($_POST['response'] == "dropRelation") {
  try {
    $checkMember = $db->prepare("SELECT COUNT(*) FROM extrasRelations INNER JOIN members ON members.MemberID = extrasRelations.MemberID WHERE RelationID = ? AND members.Tenant = ?");
    $checkMember->execute([
      $_POST['relation'],
      $tenant->getId()
    ]);
    $memberExists = $checkMember->fetchColumn() > 0;

    $checkUser = $db->prepare("SELECT COUNT(*) FROM extrasRelations INNER JOIN users ON users.UserID = extrasRelations.UserID WHERE RelationID = ? AND users.Tenant = ?");
    $checkUser->execute([
      $_POST['relation'],
      $tenant->getId()
    ]);
    $userExists = $checkUser->fetchColumn() > 0;

    if (!$memberExists && !$userExists) {
      throw new Exception('There is no such member');
    }

    $delete = $db->prepare("DELETE FROM `extrasRelations` WHERE `RelationID` = ?");
    $delete->execute([$_POST['relation']]);
  } catch (Exception $e) {
    halt(404);
  }
} else {
  halt(404);
}
