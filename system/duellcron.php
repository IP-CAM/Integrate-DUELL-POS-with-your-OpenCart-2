<?php

// Configuration
if (file_exists('../config.php')) {
  require_once('../config.php');
}



// Startup
require_once( 'startup.php');

$application_config = 'catalog';
// Application Classes
// Registry
$registry = new Registry();

// Config
$config = new Config();
$config->load('default');
$config->load($application_config);

$registry->set('config', $config);




// Event
$event = new Event($registry);
$registry->set('event', $event);

// Event Register
if ($config->has('action_event')) {
  foreach ($config->get('action_event') as $key => $value) {
    $event->register($key, new Action($value));
  }
}

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Request
$registry->set('request', new Request());

// Response
$response = new Response();
$response->addHeader('Content-Type: text/html; charset=utf-8');
$registry->set('response', $response);

// Database
if ($config->get('db_autostart')) {
  $registry->set('db', new DB($config->get('db_type'), $config->get('db_hostname'), $config->get('db_username'), $config->get('db_password'), $config->get('db_database'), $config->get('db_port')));
}

$db = $registry->get('db');

// Store
if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
  $store_query = $db->query("SELECT * FROM " . DB_PREFIX . "store WHERE REPLACE(`ssl`, 'www.', '') = '" . $db->escape('https://' . str_replace('www.', '', $_SERVER['HTTP_HOST']) . rtrim(dirname($_SERVER['PHP_SELF']), '/.\\') . '/') . "'");
} else {
  $store_query = $db->query("SELECT * FROM " . DB_PREFIX . "store WHERE REPLACE(`url`, 'www.', '') = '" . $db->escape('http://' . str_replace('www.', '', $_SERVER['HTTP_HOST']) . rtrim(dirname($_SERVER['PHP_SELF']), '/.\\') . '/') . "'");
}

if ($store_query->num_rows) {
  $config->set('config_store_id', $store_query->row['store_id']);
} else {
  $config->set('config_store_id', 0);
}

// Settings
$query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0' OR store_id = '" . (int) $config->get('config_store_id') . "' ORDER BY store_id ASC");

foreach ($query->rows as $setting) {
  if (!$setting['serialized']) {
    $config->set($setting['key'], $setting['value']);
  } else {
    $config->set($setting['key'], json_decode($setting['value'], true));
  }
}

// Session
$session = new Session();

if ($config->get('session_autostart')) {
  $session->start();
}

$registry->set('session', $session);

// Cache
$registry->set('cache', new Cache($config->get('cache_type'), $config->get('cache_expire')));

// Url
if ($config->get('url_autostart')) {
  $registry->set('url', new Url($config->get('site_base'), $config->get('site_ssl')));
}

// Language
$language = new Language($config->get('language_default'));
$language->load($config->get('language_default'));
$registry->set('language', $language);

// Document
$registry->set('document', new Document());

// Config Autoload
if ($config->has('config_autoload')) {
  foreach ($config->get('config_autoload') as $value) {
    $loader->config($value);
  }
}

// Language Autoload
if ($config->has('language_autoload')) {
  foreach ($config->get('language_autoload') as $value) {
    $loader->language($value);
  }
}

// Library Autoload
if ($config->has('library_autoload')) {
  foreach ($config->get('library_autoload') as $value) {
    $loader->library($value);
  }
}

// Model Autoload
if ($config->has('model_autoload')) {
  foreach ($config->get('model_autoload') as $value) {
    $loader->model($value);
  }
}

// Front Controller
$controller = new Front($registry);

// Pre Actions
if ($config->has('action_pre_action')) {
  foreach ($config->get('action_pre_action') as $value) {
    $controller->addPreAction(new Action($value));
  }
}


require_once( 'library/duell/duell.php');
$obj_duell = new duell\Duell($registry);
$obj_duell->callDuellStockSync('auto');
?>