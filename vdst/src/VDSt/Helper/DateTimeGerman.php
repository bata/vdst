<?php
namespace VDSt\Helper;

class DateTimeGerman extends \DateTime
{
	public function format($format) 
	{
		$english = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'January', 'February', 'March', 'May', 'June', 'July', 'October', 'December');
		$german = array('Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag', 'Januar', 'Februar', 'März', 'Mai', 'Juni', 'Juli', 'Oktober', 'Dezember');
		
		return str_replace($english, $german, parent::format($format));
	}
}
