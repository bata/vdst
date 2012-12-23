<?php 

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

// doctrine
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'dbname' => 'vdst',
    	'user' => 'root',
    	'password' => 'mysql'
    ),
));

// twig
$twigLoader = new Twig_Loader_Filesystem(__DIR__.'/../src/VDSt/templates');
$twig = new Twig_Environment($twigLoader);

// security
$app->register(new Silex\Provider\SecurityServiceProvider());

$encoder = $app['security.encoder_factory']->getEncoder(new Symfony\Component\Security\Core\User\User('admin', 'vdstpass'));
$password = $encoder->encodePassword('vdstpass', '');

$app['security.firewalls'] = array(
	'admin' => array(
		'pattern' => '^/admin',
		'http' => true,
		'users' => array(
			'admin' => array('ROLE_ADMIN', $password),
		),
	),
);

// home route
$app->get('/start', function() use ($app, $twig) {
	
	$registry = new VDSt\Entity\Registry($app['db']);
	
	// get programm
	$program = VDSt\Entity\Program::fetchBySemester($app['db'], $registry->get('semester_title'));
	
	$formattedEntries = array();
	foreach ($program as $entry) {
		
		// month
		$month = $entry->date->format('F') . ' ' . $entry->date->format('Y');
		if (!isset($formattedEntries[$month])) $formattedEntries[$month] = array();
		
		array_push($formattedEntries[$month], array(
			'day_numeric' => $entry->date->format('j'),
			'day_written' => $entry->date->format('l'),
			'start_time' => $entry->date->format('G'),
			'temporae' => $entry->date->format('i') == '15' ? 'c.t.' : 's.t.',
			'text' => $entry->text,
			'importance' => $entry->importance
		));
		
	}
	
	return $twig->render('semesterprogramm.html.twig', array( 
		'registry' => $registry,
		'program' => $formattedEntries
	));
	
});

// vdst route
$app->get('/vdst', function() use ($app, $twig) {

	$registry = new VDSt\Entity\Registry($app['db']);
	
	return $twig->render('geschichte.html.twig', array(
		'registry' => $registry,
	));

});

// admin route
$app->get('/admin', function() use ($app, $twig) {
	
	$registry = new VDSt\Entity\Registry($app['db']);
	$program = VDSt\Entity\Program::fetchBySemester($app['db'], $registry->get('semester_title'));
	
	return $twig->render('admin/program.html.twig', array(
		'registry' => $registry,
		'program' => $program
	));

});

// save program/einstellungen
$app->post('/admin/program/save', function() use ($app) {
	
	$registry = new VDSt\Entity\Registry($app['db']);
	$registry->set('semester_title', $app['request']->get('semester_title'));
	$registry->set('semester_text', $app['request']->get('semester_text'));
	
	return $app->redirect('/index.php/admin');
	
});

// fetch program entry
$app->post('/admin/program/entry/fetch/{id}', function($id) use ($app, $twig) {
	
	$entry = VDSt\Entity\Program::fetchById($app['db'], $id);
	
	return $twig->render('admin/_program_entry.html.twig', array(
		'entry' => $entry
	));

});

// save program entry
$app->post('/admin/program/entry/save', function() use ($app) {

	$entry = VDSt\Entity\Program::fetchById($app['db'], $app['request']->get('id'));
	
	$date = trim($app['request']->get('date'));
	$time = trim($app['request']->get('time'));
	$dateTime = \DateTime::createFromFormat('d.m.Y H:i', "{$date} {$time}");
	
	$entry->date = $dateTime;
	$entry->text = trim($app['request']->get('text'));
	$entry->importance = $app['request']->get('importance');
	$entry->semester = $app['request']->get('semester');
	
	VDSt\Entity\Program::save($app['db'], $entry);

	return $app->redirect('/index.php/admin');

});

// delete program entry
$app->post('/admin/program/entry/delete/{id}', function($id) use ($app) {
	
	VDSt\Entity\Program::delete($app['db'], $id);

	return new Symfony\Component\HttpFoundation\Response('');
	
});

$app->run();