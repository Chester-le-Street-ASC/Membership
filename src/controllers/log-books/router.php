<?php

/**
 * Router for new emergency log-book feature
 */

if (isset($_SESSION['LoggedIn']) && bool($_SESSION['LoggedIn'])) {
  // Logged in access to log books
  if ($_SESSION['AccessLevel'] == 'Parent') {
    // Log books for parents/users
    $this->get('/', function() {
      include 'members.php';
    });

    // Logs by members
    $this->get('/members/{member}:int', function($member) {
      include 'member-logs.php';
    });

    // View log
    $this->get('/logs/{id}:int', function($id) {
      include 'log.php';
    });

    // New log
    $this->get('/members/{member}:int/new', function($member) {
      include 'new-log.php';
    });

    // New log post
    $this->post('/members/{member}:int/new', function($member) {
      include 'new-log-post.php';
    });

    // Edit log
    $this->get('/logs/{id}:int/edit', function($id) {
      include 'edit-log.php';
    });

    // Edit log post
    $this->post('/logs/{id}:int/edit', function($id) {
      include 'edit-log-post.php';
    });

  } else if ($_SESSION['AccessLevel'] != 'Galas') {
    // Access for others

    // Log books welcome
    $this->get(['/squads', '/'], function() {
      include 'squads.php';
    });

    // Logs by members
    $this->get('/squads/{squad}:int', function($squad) {
      include 'squad-members.php';
    });

    // Logs by members
    $this->get('/members/{member}:int', function($member) {
      include 'member-logs.php';
    });

    // View log
    $this->get('/logs/{id}:int', function($id) {
      include 'log.php';
    });

    // New log
    $this->get('/members/{member}:int/new', function($member) {
      include 'new-log.php';
    });

    // New log post
    $this->post('/members/{member}:int/new', function($member) {
      include 'new-log-post.php';
    });

    // Edit log
    $this->get('/logs/{id}:int/edit', function($id) {
      include 'edit-log.php';
    });

    // Edit log post
    $this->post('/logs/{id}:int/edit', function($id) {
      include 'edit-log-post.php';
    });
  }


} else {
  // Public access to log-books

  if (isset($_SESSION['LogBooks-MemberLoggedIn']) && bool($_SESSION['LogBooks-MemberLoggedIn'])) {

    $this->get('/', function() {
      $member = $_SESSION['LogBooks-Member'];
      include 'member-logs.php';
    });

    $this->get('/members/{id}:int', function() {
      http_response_code(302);
      header("location: " . autoUrl("log-books"));
    });

    $this->group('/members/{member}:int/new', function($member) {
      $this->get('/', function($member) {
        if ($member == $_SESSION['LogBooks-Member']) {
          include 'new-log.php';
        } else {
          halt(404);
        }
      });

      $this->post('/', function($member) {
        if ($member == $_SESSION['LogBooks-Member']) {
          include 'new-log-post.php';
        } else {
          halt(404);
        }
      });
    });

    $this->group('/logs/{id}:int', function($id) {
      $this->get('/', function($id) {
        include 'log.php';
      });

      $this->get('/edit', function($id) {
        include 'edit-log.php';
      });

      $this->post('/edit', function($id) {
        include 'edit-log-post.php';
      });
    });

    $this->group('/settings', function() {
      $this->get('/password', function() {
        include 'member-password.php';
      });

      $this->post('/password', function() {
        include 'member-password-post.php';
      });

      $this->get(['/', '/*'], function() {
        http_response_code(303);
        header("location: " . autoUrl("log-books/settings/password"));
      });
    });

    $this->any('/*', function() {
      halt(404);
    });

  } else {
    $this->get('/', function() {
      include 'public/welcome.php';
    });

    $this->group('/login', function() {
      $this->get('/', function() {
        include 'public/login.php';
      });

      $this->post('/', function() {
        include 'public/login-post.php';
      });
    });

    // Send all non-logged in users to login
    $this->any('/*', function() {
      http_response_code(303);
      header("location: " . autoUrl("log-books"));
    });
  }
}