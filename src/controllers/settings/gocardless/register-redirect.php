<?php

if (!isset($_SESSION['GC-Reg-OAuth'])) {
  halt(404);
}

$tenant = new Tenant::fromId($_SESSION['GC-Reg-OAuth']['tenant']);

if (!$tenant || !isset($_GET['code'])) {
  halt(404);
}

$client = new OAuth2\Client(getenv('GOCARDLESS_CLIENT_ID'),
                            getenv('GOCARDLESS_CLIENT_SECRET'));

$url = 'https://connect.gocardless.com/oauth/access_token';
if (bool(env('IS_DEV'))) {
  $url = 'https://connect-sandbox.gocardless.com/oauth/access_token';
}

// You'll need to use exactly the same redirect URI as in the last step
$response = $client->getAccessToken(
    $url,
    'authorization_code',
    ['code' => $_GET['code'], 'redirect_uri' => autoUrl("services/gc/redirect", false)]
);

$tenant->setGoCardlessAccessToken($response['result']['organisation_id'], $response['result']['access_token']);

$_SESSION['TENANT-' . $tenant->getId()]['GC-Reg-Success'] = true;
unset($_SESSION['GC-Reg-OAuth']['tenant']);

header("location: " . autoUrl($tenant->getCodeId() . "/settings/direct-debit"));