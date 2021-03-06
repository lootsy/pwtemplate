<?php

function renderView($page, $view_name = null, $parameters = array()) {
  global $config;

  if($view_name == null) {
    $view_name = $page->template;
  }

  $path = $config->paths->templates."views/".$view_name.".phtml";

  if(!file_exists($path)) {
    return "<p>File not found: " . $path . "</p>";
  }

  $view = new TemplateFile($path);
  $view->parameters = $parameters;
  return $view->render();
}

function renderPartial($name, $parameter = array()) {
  global $config;
  $path = $config->paths->templates."partials/".$name.".phtml";
  $view = new TemplateFile($path);
  $view->parameters = $parameters;
  return $view->render();
}

// Add ?v=[last modified time] to style sheets
function versioned_stylesheet($relative_url, $add_attributes="") {
   return '<link rel="stylesheet" href="'.versioned_resource($relative_url).'" '.$add_attributes.'>'."\n";
}

// Add ?v=[last modified time] to javascripts
function versioned_javascript($relative_url, $add_attributes="") {
  return '<script src="'.versioned_resource($relative_url).'" '.$add_attributes.'></script>'."\n";
}

// Add ?v=[last modified time] to a file url
function versioned_resource($relative_url) {
   $file = $_SERVER["DOCUMENT_ROOT"].$relative_url;
   $file_version = "";
   if(file_exists($file)) {
     $file_version = "?v=".filemtime($file);
   }
   return $relative_url.$file_version;
}

function getTld($url, $default="com") {
  $urlData = parse_url($url);

  if($urlData['host'] == 'localhost') {
    return $default;
  }

  $hostData = explode('.', $urlData['host']);
  $hostData = array_reverse($hostData);

  return $hostData[0];
}

function setLanguageForDomain($languages, $user, $language_domains, $site_url, $default_domain) {
  $current_tld = getTld($site_url, $default_domain);

  if(array_key_exists($current_tld, $language_domains)) {
    $current_language = $language_domains[$current_tld];
  } else {
    $current_language = 'default';
  }

  foreach($languages as $language)
  {
    if($language->name == $current_language)
    {
      $user->language = $language;
      break;
    }
  }
}

function base64url_encode($data) {
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

function getLinkForPage($selector) {
  if(is_object($selector)) {
    $page = $selector;
  } else {
    $page = wire('pages')->get($selector);
  }

  return sprintf('<a href="%s">%s</a>', $page->httpUrl, $page->title);
}

function imageSizeExists($image, $width, $height = 0) {
  $base_file_name = $image->filename;

  $path_parts = pathinfo($base_file_name);

  if(abs($height) + abs($width) > 0) {
    $size = sprintf('.%dx%d', $width, $height);
  } else {
    $size = '';
  }

  $variation_path = sprintf('%s/%s%s.%s', $path_parts['dirname'], $path_parts['filename'], $size, $path_parts['extension']);

  if(file_exists($variation_path)) {
    return true;
  }

  return false;
}

function check_requirements() {
  global $config;
  $path = $config->paths->templates."/requirements.txt";
  $names = file($path);
  
  $modules = wire('modules');
  
  foreach($names as $id => $name) {
    $name = trim($name);
    
    if(!$name) {
      continue;
    }
    
    if($modules->isInstalled($name) == false) {
      throw new WireException("Module " . $name . " is not installed! Check templates/requirements.txt");
    }
  }
}
