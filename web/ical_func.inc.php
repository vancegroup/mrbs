<?PHP

function ical_fold($prop, $value) {
  # iCal specification says:
  # max line length 75 octets
  # must escape \ as \\
  # ; as \;
  # , as \,
  # a new line as \n

  if ((!($value))||($value=='')) {
    return '';  #Almost every attribute is optional - so lets not output it if the property value given to us is empty. 
  }

  $value = preg_replace("/\\\\/","\\\\\\\\", $value); # (\ ends up as text \\)
  $value = preg_replace("/;/","\\;", $value);
  $value = preg_replace("/,/","\\\,", $value);
  $value = preg_replace("/\n/","\\n",$value); # (a new line ends up as text \n)

  # it "shouldn't" matter if folding occurs across an escaping as unfolding should
  # be the first task of anything reading an ical file.

  $str_to_wrap = $prop . ':' . $value;
  $output = array();

  while(strlen($str_to_wrap) > 75) { 
    $output[] = substr($str_to_wrap,0,75);
    $str_to_wrap = ' ' . substr($str_to_wrap,75); #we wrap by starting the next line with a space.
  }
  $output[]=$str_to_wrap;  #add the last line to the output (it is shorter than 75 chars).

  $return_str = implode("\r\n",$output);  #a CRLF between each line.
  $return_str .= "\r\n";  #and add a CRLF after the last line
  return $return_str;
}

function ical_header($cal_name,$cal_desc) {
  $return = '';
  $return .= 'BEGIN:VCALENDAR' . "\r\n";
  $return .= 'VERSION:2.0' . "\r\n";
  $return .= ical_fold('X-WR-CALNAME', $cal_name);  #Looks like an Apple extension - but a good one! Calendar Name
  $return .= ical_fold('X-WR-CALDESC', $cal_desc);  #Looks like an Apple extension - but a good one! Calendar Name
  $return .= 'PRODID:-//VRAC based on Centenary Institute//Originally a fact0r hack of MRBS v0.1//EN' . "\r\n";
  $return .= 'CALSCALE:GREGORIAN' . "\r\n";
  return $return;
}

function ical_footer() {
  return 'END:VCALENDAR' . "\r\n";
}

function ical_event($start,$end,$last_modified,$summary,$description,$room_name,$id,$booker) {
  $return = 'BEGIN:VEVENT' . "\r\n";
  $return .= 'DTSTART:' . gmdate('Ymd\THis',$start) . "Z\r\n"; #Z indicates GMT - let the user app worry about displaying it nicely in their current time zone.
  $return .= 'DTEND:' . gmdate('Ymd\THis',$end) . "Z\r\n";
  $return .= 'LAST-MODIFIED:' . gmdate('Ymd\THis',$last_modified) . "Z\r\n";
  $return .= ical_fold('SUMMARY',$summary);
  $return .= ical_fold('DESCRIPTION',$description);
  $return .= ical_fold('LOCATION',$room_name);
  $return .= ical_fold('URL',"http://academic.cleardefinition.com/reservations/mrbs/web/view_entry.php?id=" . $id);
  $return .= ical_fold('ATTENDEE;CN="' . $booker . '"' , 'mailto:' . $booker . '@yourdomain.url');  
  $return .= 'UID:' . $id . '-' . gmdate('Ymd-His',$start) . 
	'@rbook.yourdomain.unique.identifier' . "\r\n";
## Still need to get the owner in here somewhere. Can always add it to the top of the description field.
  $return .= 'END:VEVENT' . "\r\n";
  return $return;
}


?>
