<?php

global $db;

$yes = $no = "";

$getMed;

if ($_SESSION['AccessLevel'] == "Parent") {
  $getMed = $db->prepare("SELECT MForename, MSurname, Conditions, Allergies,
  Medication FROM `members` LEFT JOIN `memberMedical` ON members.MemberID =
  memberMedical.MemberID WHERE members.MemberID = ? AND members.UserID = ?");
  $getMed->execute([$id, $_SESSION['UserID']]);
} else {
  $getMed = $db->prepare("SELECT MForename, MSurname, Conditions, Allergies,
  Medication FROM `members` LEFT JOIN `memberMedical` ON members.MemberID =
  memberMedical.MemberID WHERE members.MemberID = ?");
  $getMed->execute([$id]);
}

$row = $getMed->fetch(PDO::FETCH_ASSOC);

if ($row == null) {
  halt(404);
}

$pagetitle = "Medical Review - " . htmlspecialchars($row['MForename'] . ' ' . $row['MSurname']);

include BASE_PATH . "views/header.php";
?>

<div class="container">
	<form method="post" action="<?=htmlspecialchars(currentUrl())?>" name="med" id="med">
		<h1>Medical Form</h1>
		<?php if (isset($_SESSION['ErrorState'])) {
			echo $_SESSION['ErrorState'];
			unset($_SESSION['ErrorState']);
		} ?>
		<p class="lead">
      Check the details for <?=htmlspecialchars($row['MForename'] . ' ' . $row['MSurname'])?> are correct.
    </p>

    <div class="alert alert-info">
      <p class="mb-0">
        <strong>
          <a href="https://www.markdownguide.org/" target="_blank"
          class="alert-link">Formatting with Markdown</a> is supported in these forms.
        </strong>
      </p>
      <p>
        To start a new line, press return twice.
      </p>
      <p class="mb-0">
        For a bulleted list do the following;
      </p>
<pre><code>
* first item in list
* second item in list
</code></pre>
    </div>

		<div class="mb-2">
			<p class="mb-2">Does <?=htmlspecialchars($row['MForename'])?> have any specific medical conditions
			or disabilities?</p>

			<?php if ($row['Conditions'] != "") {
				$yes = " checked ";
				$no = "";
			} else {
				$yes = "";
				$no = " checked ";
			} ?>

			<div class="custom-control custom-radio">
			  <input type="radio" value="0" <?=$no?> id="medConDisNo" name="medConDis" class="custom-control-input" onclick="toggleState('medConDisDetails', 'medConDis')">
			  <label class="custom-control-label" for="medConDisNo">No</label>
			</div>
			<div class="custom-control custom-radio">
			  <input type="radio" value="1" <?=$yes?> id="medConDisYes" name="medConDis" class="custom-control-input" onclick="toggleState('medConDisDetails', 'medConDis')">
			  <label class="custom-control-label" for="medConDisYes">Yes</label>
			</div>
		</div>

		<div class="form-group">
	    <label for="medConDisDetails">If yes give details</label>
	    <textarea oninput="autoGrow(this)" class="form-control auto-grow" id="medConDisDetails" name="medConDisDetails"
	    rows="8" <?php if($yes==""){?>disabled<?php } ?>><?=htmlspecialchars($row['Conditions'])?></textarea>
	  </div>

		<!-- -->

		<div class="mb-2">
			<p class="mb-2">Does <?=htmlspecialchars($row['MForename'])?> have any allergies?</p>

			<?php if ($row['Allergies'] != "") {
				$yes = " checked ";
				$no = "";
			} else {
				$yes = "";
				$no = " checked ";
			} ?>

			<div class="custom-control custom-radio">
			  <input type="radio" value="0" <?=$no?> id="allergiesNo"
			  name="allergies" class="custom-control-input" onclick="toggleState('allergiesDetails', 'allergies')">
			  <label class="custom-control-label" for="allergiesNo">No</label>
			</div>
			<div class="custom-control custom-radio">
			  <input type="radio" value="1" <?=$yes?> id="allergiesYes"
			  name="allergies" class="custom-control-input" onclick="toggleState('allergiesDetails', 'allergies')">
			  <label class="custom-control-label" for="allergiesYes">Yes</label>
			</div>
		</div>

		<div class="form-group">
	    <label for="allergiesDetails">If yes give details</label>
	    <textarea oninput="autoGrow(this)" class="form-control auto-grow" id="allergiesDetails" name="allergiesDetails"
	    rows="8" <?php if($yes==""){?>disabled<?php } ?>><?=htmlspecialchars($row['Allergies'])?></textarea>
	  </div>

		<!-- -->

		<div class="mb-2">
			<p class="mb-2">Does <?=htmlspecialchars($row['MForename'])?> take any regular medication?</p>

			<?php if ($row['Medication'] != "") {
				$yes = " checked ";
				$no = "";
			} else {
				$yes = "";
				$no = " checked ";
			} ?>

			<div class="custom-control custom-radio">
			  <input type="radio" value="0" <?=$no?> id="medicineNo" name="medicine" class="custom-control-input" onclick="toggleState('medicineDetails', 'medicine')">
			  <label class="custom-control-label" for="medicineNo">No</label>
			</div>
			<div class="custom-control custom-radio">
			  <input type="radio" value="1" <?=$yes?> id="medicineYes" name="medicine" class="custom-control-input" onclick="toggleState('medicineDetails', 'medicine')">
			  <label class="custom-control-label" for="medicineYes">Yes</label>
			</div>
		</div>

		<div class="form-group">
	    <label for="medConDisDetails">If yes give details</label>
	    <textarea oninput="autoGrow(this)" class="form-control auto-grow" id="medicineDetails" name="medicineDetails"
	    rows="8" <?php if($yes==""){?>disabled<?php } ?>><?=htmlspecialchars($row['Medication'])?></textarea>
	  </div>

		<div>
			<p>
				<button type="submit" class="btn btn-success">Save</button>
			</p>
		</div>
	</form>
</div>

<script src="<?=autoUrl("public/js/medical-forms/MedicalForm.js")?>"></script>

<?php include BASE_PATH . "views/footer.php";
