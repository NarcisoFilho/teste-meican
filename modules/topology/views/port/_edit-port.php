<?php
/**
 * @copyright Copyright (c) 2012-2016 RNP
 * @license http://github.com/ufrgs-hyman/meican#license
 */

echo $this->render('_form', array(
    'formId' => 'edit-port-form',
    'networks' => $networks,
	'port' => $port,
	'locations' => $locations,
)); ?>