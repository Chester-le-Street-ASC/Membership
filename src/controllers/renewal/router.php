<?php

$access = $_SESSION['AccessLevel'];

if ($access == "Parent") {
	// Renewal Home
	$this->get('/', function() {
		global $link;
		include BASE_PATH . 'controllers/renewal/parent/home.php';
	});

	// Renewal Home
	$this->group('/accountreview', function() {
		global $link;

		$this->get('/', function() {
			global $link;
			include BASE_PATH . 'controllers/renewal/parent/accountReview.php';
		});

		$this->post('/', function() {
			global $link;
			include BASE_PATH . 'controllers/renewal/parent/accountReview.php';
		});

		$this->get('/swimmers', function() {
			global $link;
			include BASE_PATH . 'controllers/renewal/parent/swimmerReview.php';
		});

		$this->get('/fees', function() {
			global $link;
			include BASE_PATH . 'controllers/renewal/parent/feeReview.php';
		});
	});

	// Medical Review
	$this->get('/medicalreview/{id}:int', function($id) {
		global $link;
		include BASE_PATH . 'controllers/renewal/parent/medicalReview.php';
	});

	$this->post('/medicalreview/{id}:int', function($id) {
		global $link;
		include BASE_PATH . 'controllers/renewal/parent/accountReview.php';
	});

	// Emergency Contact
	$this->group('/emergencycontact', function() {
		global $link;

		$this->get('/', function() {
			global $link;
			include BASE_PATH . 'controllers/renewal/parent/emergencyContact.php';
		});

		$this->get('/new', function() {
			global $link;
			include BASE_PATH . 'controllers/renewal/parent/emergencyContactNew.php';
		});

		$this->post('/new', function() {
			global $link;
			include BASE_PATH . 'controllers/renewal/parent/accountReview.php';
		});

		$this->get('/edit/{id}:int', function($id) {
			global $link;
			include BASE_PATH . 'controllers/renewal/parent/emergencyContactEdit.php';
		});

		$this->post('/edit/{id}:int', function($id) {
			global $link;
			include BASE_PATH . 'controllers/renewal/parent/accountReview.php';
		});

		$this->get('/delete/{id}:int', function($id) {
			global $link;
			include BASE_PATH . 'controllers/renewal/parent/emergencyContactDelete.php';
		});
	});

	$this->group('/conduct', function() {
		global $link;

		$this->get('/parent', function() {
			global $link;
			include BASE_PATH . 'controllers/renewal/parent/conductForm.php';
		});

		$this->post('/parent', function() {
			global $link;
			include BASE_PATH . 'controllers/renewal/parent/accountReview.php';
		});

		$this->get('/swimmers/{id}:int', function($id) {
			global $link;
			include BASE_PATH . 'controllers/renewal/parent/conductForm.php';
		});

		$this->post('/swimmers/{id}:int', function($id) {
			global $link;
			include BASE_PATH . 'controllers/renewal/parent/accountReview.php';
		});
	});

	$this->get('/administrationform', function() {
		global $link;
		include BASE_PATH . 'controllers/renewal/parent/adminForm.php';
	});

	$this->post('/administrationform', function() {
		global $link;
		include BASE_PATH . 'controllers/renewal/parent/accountReview.php';
	});

	$this->get('/fees', function() {
		global $link;
		include BASE_PATH . 'controllers/renewal/parent/accountReview.php';
	});
}