<?php
/* Made by Dennis Sarsozo | dpsarso2 for NC State's Network Operations Team in April 2020.
   Part of the Project Management Lite (PML) Program for OIT.
*/
// include your composer dependencies
require_once 'vendor/autoload.php';

/*
if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}
* /

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('dpsarso2-NOC-Billboard-Server');
    $client->setAuthConfig('CLIENT_SECRET that is not uploaded 😉');
    $client->setScopes(array(Google_Service_Calendar::CALENDAR_READONLY, Google_Service_Calendar::CALENDAR_EVENTS_READONLY));
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    $tokenPath = 'token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}

/**
 * Sort Google Calendar meetings by ascending start time, floor, and meeting name.
 */
function cmpMeetings($a, $b)
{
    //Grab meeting's starting time.
    $startA = $a[0]->start->dateTime;
    $startB = $b[0]->start->dateTime;
    $startA = substr($startA, 11, 5);
    $startB = substr($startB, 11, 5);
    
    //Sort by start time.
    if( strcmp($startA, $startB) < 0 ) {
        return -1;
    }
    else if( strcmp($startA, $startB) > 0 ) {
        return 1;
    }
    
    //Sort by floor.
    else if ( strcmp($a[1], $b[1]) < 0 ) {
        return -1;
    }
    else if ( strcmp($a[1], $b[1]) > 0 ) {
        return 1;
    }
    //Sort by meeting name.
    if($a[0]->getSummary() != NULL && $b[0]->getSummary() != NULL) {
        if( strcmp($a[0]->getSummary(), $b[0]->getSummary()) < 0) {
                return -1;
        }
        else if( strcmp($a[0]->getSummary(), $b[0]->getSummary()) < 0) {
                return 1;
        }
    }
    return 0; 
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Calendar($client);

//Get all NOC AFH Room Calendars. Make an empty array to hold their IDs.
$AFH_Calendars = array();

//First get all calendars
$calendars = $service->calendarList->listCalendarList();
//Search for AFH Calendars
foreach ($calendars->getItems() as $calendarListEntry) {
    // Assume all AFH Room calendars are called 'resource'. Not sure if its grabbing "all room calendars."
    if( strpos($calendarListEntry->getID(), "resource") !== false) {
        $AFH_Calendars[] = $calendarListEntry;
	}
}
//Get today's events for each room.
$today = date('c'); //Current date at CURRENT TIME.

//Only look for events happening today from 06:00-21:00
$sixAM_ISO=substr($today,0,11);
$ninePM_ISO=$sixAM_ISO;
//Append to make correct timestamps.
$sixAM_ISO .= "06:00:00Z";
$ninePM_ISO .= "21:00:00Z";
  $optParams = array(
  'timeMin' => $sixAM_ISO,
  'orderBy' => 'startTime',
  'singleEvents' => true,
  'showDeleted' => false,
  'timeMax' => $ninePM_ISO,
  );

//Make an empty array to hold ALL room events.
$All_AFH_Meetings = array();

//Now go into each AFH calendar found and get their meetings events details.
foreach ($AFH_Calendars as $cal) {
    //Get the room ### and floor.
    $resourceName = $cal->getSummary();
    //Cut string to only show floor-room###.
    $resourceName = substr($resourceName, 6, 3);
    //Get meetings from each AFH calendar.
    $meetingsList = $service->events->listEvents($cal->getId(), $optParams);
    $meetings = $meetingsList->getItems();
    
    //In each calendar, go through its meetings.
    foreach ($meetings as $meeting) {
        //Store array so you can read the meeting details and which room it belongs too.
        $meetingsWithName = array($meeting, $resourceName);
        $All_AFH_Meetings[] = $meetingsWithName;
    }
}
//Now sort all meetings by time, then floorRoom###, and meeting name.
usort($All_AFH_Meetings, "cmpMeetings");

//If more than four meetings, will need to auto-scroll site. Use JS slideshow code in index.php to show more meetings.

//If no meetings found, display text.
if (empty($All_AFH_Meetings)) {
    echo "<h3 class=\"align-center text-center p-5\">No meetings today!</h3>";
} else {
    //Track meetings output to HTML. Only 4 events can be shown at a time.
    $eventIndex = 1;
    echo "<div class=\"eventGroup\">"; 
    foreach ($All_AFH_Meetings as $event) {
        //If room calendar permissions aren't given, the title will be null. Put a placeholder if null.
        $meetingTitle = (is_null($event[0]->getSummary()) ? "MEETING NAME" : $event->getSummary());
        //If meeting name is too long, cut it off. TV is 1080p. 22 characters is the sweet spot. 
        if(strlen($meetingTitle) > 22) {
            $meetingTitle = substr($meetingTitle, 0, 19);
            $meetingTitle .= "...";
        }
        //Try not to edit the actual event. put in another variable.
        $start = $event[0]->start->dateTime;
        //Cut it to only display starting 00:00 time format.
        $start = substr($start, 11, 5);
        
        //Build HTML to be inserted.
        //If it has been the 4th event, restart counter and put event on the next "slide"
        if($eventIndex % 5 == 0) {
            $eventIndex = 1;
            echo "</div>"; 
            echo "<div class=\"eventGroup\">"; 
        }
        //Note: Used Bootstrap.
        echo "<div class=\"row p-4 align-items-center\">"; 
        echo "<div id=\"startTime\" class=\"col-2 pl-4\"><h3><time>". $start . "</time></h3></div>";
        echo "<div id=\"meetingTitle\" class=\"col-auto pl-5 \"><h3>". $meetingTitle . "</h3></div>";
        echo "<div id=\"roomName\" class=\"col-3 mr-4\"><h3>Room&nbsp;". $event[1] . "</h3></div>";
        echo "</div>"; 
        $eventIndex++;  
    }
    echo "</div>";
}
