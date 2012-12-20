<?php
namespace VDSt\Entity;

use VDSt\Helper\DateTimeGerman;

class Program 
{
	public $id;

	public $date;

	public $text;

	public $importance;
	
	public $semester;

	public static function fetchBySemester(\Doctrine\DBAL\Connection $con, $semester) 
	{
		$sql = 'SELECT * FROM program WHERE semester = ?';

		return self::map($con->fetchAll($sql, array($semester)));
	}
	
	public static function fetchById(\Doctrine\DBAL\Connection $con, $id)
	{
		$sql = 'SELECT * FROM program WHERE id = ?';
		$mapping = self::map($con->fetchAll($sql, array($id)));
		
		return array_pop($mapping);
	} 
	
	public static function save(\Doctrine\DBAL\Connection $con, Program $entry)
	{
		$data = array(
			'target_date' => $entry->date->format('Y-m-d H:i:s'),
			'text' => $entry->text,
			'importance' => $entry->importance,
			'semester' => $entry->semester		
		);
		
		if ($entry->id) {
			$con->update('program', $data, array( 'id' => $entry->id ));
		} else {
			$con->insert('program', $data);
		}
	}
	
	public static function delete(\Doctrine\DBAL\Connection $con, $id)
	{
		$con->delete('program', array('id' => $id));
	}

	private static function map($assocData) 
	{
		$resultSet = array();

		foreach ($assocData as $data) {

			$program = new Program();
			$program->id = $data['id'];
			$program->date = new DateTimeGerman($data['target_date']);
			$program->text = $data['text'];
			$program->importance = $data['importance'];
			$program->semester = $data['semester'];
			
			array_push($resultSet, $program);

		}
		
		return $resultSet;
	}
}
