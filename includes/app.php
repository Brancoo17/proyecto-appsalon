<?php 

require 'funciones.php';
require 'database.php';
require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');

// Conectarnos a la base de datos
use Model\ActiveRecord;
ActiveRecord::setDB($db);