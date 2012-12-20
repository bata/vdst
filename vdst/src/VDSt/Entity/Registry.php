<?php
namespace VDSt\Entity;

class Registry extends \Symfony\Component\HttpFoundation\ParameterBag
{
	private $con;
	
	public function __construct(\Doctrine\DBAL\Connection $con)
	{
		$this->con = $con;
		
		$bag = array();
		$dbValues = $con->fetchAll('SELECT * FROM registry');
		
		foreach ($dbValues as $pair) {
			$bag[$pair['bag_key']] = $pair['bag_value'];
		}
		
		// init with db values
		parent::__construct($bag);
	}
	
	public function __destruct()
	{
		// truncate
		$this->con->query('TRUNCATE TABLE registry');
		
		// store value in db
		foreach ($this->all() as $key => $value) {
			
			$this->con->insert('registry', array( 
				'bag_key' => $key,
				'bag_value' => $value
			));
			
		}
	}
}
