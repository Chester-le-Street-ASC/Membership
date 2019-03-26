<?php

$use_white_background = true;
$fluidContainer = true;
$squadID = $search = "";
mysqli_real_escape_string($link, parse_str($_SERVER['QUERY_STRING'], $queries));
if (isset($queries['squadID'])) {
  $squadID = mysqli_real_escape_string($link, intval($queries['squadID']));
}
if (isset($queries['search'])) {
  $search = mysqli_real_escape_string($link, $queries['search']);
}

$pagetitle = "Swimmers";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/swimmersMenu.php";

if (isset($_POST['squad'])) {
  $squadID = mysqli_real_escape_string($link, trim(htmlspecialchars($_POST['squad'])));
} ?>
<div class="container-fluid">
  <h1>Swimmer Directory</h1>
  <div class="d-print-none">
    <p class="lead">Currently registered members.</p>
    <?php
  $sql = "SELECT * FROM `squads` ORDER BY `squads`.`SquadFee` DESC;";
  $result = mysqli_query($link, $sql);
  $squadCount = mysqli_num_rows($result);
  ?>
  <div class="form-row">
  <div class="col-md-6 mb-3">
  <label class="sr-only" for="squad">Select a Squad</label>
  <select class="custom-select" placeholder="Select a Squad" id="squad" name="squad">
  <option value="allSquads">Show All Squads</option>;
  <?php for ($i = 0; $i < $squadCount; $i++) {
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $id = $row['SquadID'];
    if ($squadID == $id) {
      ?><option value="<?php echo $row['SquadID']; ?>" selected><?=htmlspecialchars($row['SquadName'])?></option><?php
    }
    else {
      ?><option value="<?php echo $row['SquadID']; ?>"><?=htmlspecialchars($row['SquadName'])?></option><?php
    }
  } ?>
    </select></div>
    <div class="col-md-6 mb-3">
      <label class="sr-only" for="search">Search by Surname</label>
      <input class="form-control" placeholder="Surname" id="search" name="search" value="<?=htmlspecialchars($search)?>">
    </div>

  </div>

  </div>

  <div id="output">
    <div class="ajaxPlaceholder">
      <span class="h1 d-block">
        <i class="fa fa-spin fa-circle-o-notch" aria-hidden="true"></i>
        <br>Loading Content
      </span>If content does not display, please turn on JavaScript
    </div>
  </div>
</div>

<script>
function getResult() {
  var squad = document.getElementById("squad");
  var squadValue = squad.options[squad.selectedIndex].value;
  var search = document.getElementById("search");
  var searchValue = search.value;
  console.log(squadValue);
  console.log(searchValue);
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        console.log("We got here");
        document.getElementById("output").innerHTML = this.responseText;
        console.log(this.responseText);
        window.history.replaceState("string", "Title", "<?php echo autoUrl("swimmers"); ?>?squadID=" + squadValue + "&search=" + searchValue);
      }
    }
    xhttp.open("POST", "<?php echo autoURL("swimmers/ajax/swimmerDirectory"); ?>", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("squadID=" + squadValue + "&search=" + searchValue);
    console.log("Sent");
}
// Call getResult immediately
getResult();

document.getElementById("squad").onchange=getResult;
document.getElementById("search").oninput=getResult;
</script>
<?php
include BASE_PATH . "views/footer.php";
?>
