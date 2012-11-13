<?PHP

require_once "defaultincludes.inc";
require_once "mrbs_sql.inc";

//require_once 'ical_func.inc.php';
require_once 'fact0rfunc.inc.php';

### Stage 1 - grab an array of room ids -> room names
	$rooms = rooms_array();

### Stage 2 - First bit of "Welcome" html
?>
<html>
<body>
<h2>Subscribing to bookings from the Resource Booking System from iCal</h2>
<P>
You can now subscribe to various "iCals" of the bookings residing in the Resource Booking System. These iCals are read only (you can only edit them through the web interface) however they may be useful in reminding you of the times you have booked equipment. 
<P>
All of these iCals will allow you to see bookings from two weeks ago to 12 months hence.
<P>
To subscribe to all bookings for a particular resource/room give iCal the relevant url below: 
<?PHP

### Stage 3 - Output links for iCal subscription to various rooms.
function cmp($a, $b)
{
  $cmp = strcmp($a['areaName'], $b['areaName']);
  if ($cmp == 0)
    $cmp = strcmp($a['roomName'], $b['roomName']);
  return $cmp;
}

	usort($rooms, "cmp");

	print '<UL>';
	foreach($rooms as $room) {
		print '<LI><a href="webcal://academic.cleardefinition.com/reservations/mrbs/web/ical2.php?room=' . $room['roomID'] .
			'">' . $room['areaName'] .' - ' . $room['roomName'] .  
			'</a>' . ' or url to add to Google Calendar, etc: <a href="http://academic.cleardefinition.com/reservations/mrbs/web/ical2.php?room='. 
			$room['roomID'] . '">http://academic.cleardefinition.com/reservations/mrbs/web/ical2.php?room='. 
			$room['roomID'] . '</a>' . "\n";
	} 
	print '</UL>';

### Stage 4 - Output static links to iCal subscriptions running on other servers.
?>	 



<?PHP
### Stage 5 - HTML footer.
?>
</body>
</html>
