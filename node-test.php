<?php

require dirname(__FILE__) . '/Node.php';

$ul = new Node('ul', array('class' => 'nested-list'));

$container = new Node('div', array('id' => 'container'), array($ul));
$container->setAttribute('style', 'background-color: blue;');

$ul->addChild(new Node('li'));
$li = new Node('li',
	array(
		'class' => array('c1', 'c2', 'hidden')
	)
);
$li->addClass('toggle toggle-hidden');

$span = new Node('span', array('attr' => 'true'));
$span->addText('Hello!');
$li->addChild($span);
$ul->addChild($li);
$ul->addChild(new Node('li',
	array(
		'class' => 'c1 c2 hidden',
		'id'    => 'hello-li'
	)
));
$ul->addChild(new Node('li'));

echo $container->toHtml(true);