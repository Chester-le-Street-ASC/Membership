/**
 * Common code for CLS Membership Payments
 */

var stripeElementStyle = {
  base: {
    iconColor: '#ced4da',
    lineHeight: '1.5',
    height: '1.5rem',
    color: '#212529',
    fontWeight: 400,
    fontFamily: 'Open Sans, Segoe UI, sans-serif',
    fontSize: '16px',
    fontSmoothing: 'antialiased',
    '::placeholder': {
      color: '#868e96',
    },
  },
  invalid: {
    color: '#212529',
  },
}

function setCardBrandIcon(brand) {
  var content = '<img class="fa fa-fw" src="<?=autoUrl("public/img/stripe/network-svgs/credit-card.svg")?>" aria-hidden="true">';
  if (brand === 'visa') {
    content = '<img class="fa fa-fw" src="<?=autoUrl("public/img/stripe/network-svgs/visa.svg")?>" aria-hidden="true">';
  } else if (brand === 'mastercard') {
    content = '<img class="fa fa-fw" src="<?=autoUrl("public/img/stripe/network-svgs/mastercard.svg")?>" aria-hidden="true">';
  } else if (brand === 'amex') {
    content = '<img class="fa fa-fw" src="<?=autoUrl("public/img/stripe/network-svgs/amex.svg")?>" aria-hidden="true">';
  } else if (brand === 'discover') {
    content = '<img class="fa fa-fw" src="<?=autoUrl("public/img/stripe/network-svgs/discover.svg")?>" aria-hidden="true">';
  } else if (brand === 'diners') {
    content = '<img class="fa fa-fw" src="<?=autoUrl("public/img/stripe/network-svgs/diners.svg")?>" aria-hidden="true">';
  } else if (brand === 'jcb') {
    content = '<img class="fa fa-fw" src="<?=autoUrl("public/img/stripe/network-svgs/jcb.svg")?>" aria-hidden="true">';
  }
  document.getElementById('card-brand-element').innerHTML = content;
}

function disableButtons() {
  document.querySelectorAll('.pm-can-disable').forEach(elem => {
    elem.disabled = true;
  });
}

function enableButtons() {
  document.querySelectorAll('.pm-can-disable').forEach(elem => {
    elem.disabled = false;
  });
}
