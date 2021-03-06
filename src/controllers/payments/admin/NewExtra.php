<?php

$pagetitle = "Add a New Extra";

include BASE_PATH . "views/header.php";
include BASE_PATH . "views/paymentsMenu.php";

?>

<div class="container">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl('payments'))?>">Payments</a></li>
      <li class="breadcrumb-item"><a href="<?=htmlspecialchars(autoUrl('payments/extrafees'))?>">Extras</a></li>
      <li class="breadcrumb-item active" aria-current="page">New</li>
    </ol>
  </nav>

  <div class="">
    <h1>New Extra</h1>
    <p class="lead">Add a new extra fee such as CrossFit.</p>

    <div class="row">
      <div class="col-lg-8">
        <?php
        if (isset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'])) {
          echo $_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState'];
          unset($_SESSION['TENANT-' . app()->tenant->getId()]['ErrorState']);
        }
        ?>
        <form method="post" class="needs-validation" novalidate>
          <div class="form-group">
            <label for="name">Extra Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Enter name" required>
            <div class="invalid-feedback">
              Provide a name for this monthly extra
            </div>
          </div>

          <div class="form-group">
            <label for="price">Amount</label>
            <div class="input-group mb-3">
              <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon3">&pound;</span>
              </div>
              <input type="number" min="0" step="0.01" class="form-control rounded-right" id="price" name="price" placeholder="0" required>
              <div class="invalid-feedback">
                Enter an amount for this extra
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Monthly payment or refund</label>
            <div class="custom-control custom-radio">
              <input type="radio" id="type-pay" name="pay-credit-type" class="custom-control-input" value="Payment" required>
              <label class="custom-control-label" for="type-pay">Payment</label>
            </div>
            <div class="custom-control custom-radio">
              <input type="radio" id="type-credit" name="pay-credit-type" class="custom-control-input" value="Refund">
              <label class="custom-control-label" for="type-credit">Credit/refund</label>
            </div>
          </div>

          <p class="mb-0">
            <button type="submit" class="btn btn-dark">
              Add extra
            </button>
          </p>
        </form>
      </div>
    </div>
  </div>
</div>

<?php

$footer = new \SCDS\Footer();
$footer->addJs('public/js/NeedsValidation.js');
$footer->render();

?>
