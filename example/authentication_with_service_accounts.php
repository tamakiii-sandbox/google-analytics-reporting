<?php

require_once __DIR__ . '/../vendor/autoload.php';

$PATH = realpath(__DIR__ . '/../config/secure/credentials.json');

putenv("GOOGLE_APPLICATION_CREDENTIALS=$PATH");

$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);

$service = new Google_Service_Analytics($client);

// @see https://developers.google.com/analytics/devguides/reporting/core/v3/quickstart/service-php?hl=ja

// Get the user's first view (profile) ID.
// Get the list of accounts for the authorized user.
$accounts = $service->management_accounts->listManagementAccounts();

if (!count($accounts->getItems())) {
  throw new Exception('No accounts found for this user.');
}

$items = $accounts->getItems();
$accountId = $items[0]->getId();

// Get the list of properties for the authorized user.
$properties = $service->management_webproperties->listManagementWebproperties($accountId);

if (!count($properties->getItems())) {
  throw new Exception('No views (profiles) found for this user.');
}

$items = $properties->getItems();
$propertyId = $items[0]->getId();

// Get the list of views (profiles) for the authorized user.
$profiles = $service->management_profiles->listManagementProfiles($accountId, $propertyId);

if (!count($profiles->getItems())) {
    throw new Exception('No properties found for this user.');
}

$items = $profiles->getItems();

// first view (profile) ID.
$profileId = $items[0]->getId();

// Get Result
$results = $service->data_ga->get(
    'ga:' . $profileId,
    '7daysAgo',
    'today',
    'ga:sessions'
);

if (!count($results->getRows())) {
   print "No results found.\n";
} else {
   // Get the profile name.
   $profileName = $results->getProfileInfo()->getProfileName();

   // Get the entry for the first entry in the first row.
   $rows = $results->getRows();
   $sessions = $rows[0][0];

   // Print the results.
   print "First view (profile) found: $profileName\n";
   print "Total sessions: $sessions\n";
}
