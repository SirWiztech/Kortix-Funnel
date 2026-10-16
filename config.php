<?php
// ---- SETTINGS: change these ----
const ADMIN_PASSWORD = 'kortix2026';
const BRAND = 'Kortix Digital';
const SETTINGS_FILE = __DIR__ . '/settings.json';
const DATA_FILE = __DIR__ . '/data.json';

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function save_links(array $d){ file_put_contents(DATA_FILE, json_encode(array_values($d), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE), LOCK_EX); }
function load_links(){
  if (!file_exists(DATA_FILE)) {
    $mk = fn($i,$t,$tag,$d,$u,$ic)=>['id'=>$i,'title'=>$t,'tag'=>$tag,'desc'=>$d,'url'=>$u,'icon'=>$ic,'active'=>true,'clicks'=>0];
    save_links([
      $mk('frontend','Frontend Web Development','Frontend','HTML, CSS, JavaScript and modern frameworks. Build interfaces people enjoy using.','https://chat.whatsapp.com/DqODmH902UN4xqmyID5Lm1?s=cl&p=a&mlu=4&ilr=4','</>'),
      $mk('backend','Backend Development','Backend','Servers, databases and APIs. Build the logic that powers real products.','https://chat.whatsapp.com/CA5A0pkg8LXA71R50ij3fS?s=cl&p=a&mlu=4&ilr=4','▤'),
      $mk('uiux','UI/UX Design','UiUx','Research, wireframes and prototypes. Design products that feel obvious to use.','https://chat.whatsapp.com/L6KCoZRWTgHEtK4XENr83W?s=cl&p=a&mlu=4&ilr=4','◩'),
    ]);
  }
  return json_decode(file_get_contents(DATA_FILE), true) ?: [];
}
function valid_url($u){ return filter_var($u, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $u); }

function load_settings(){
  $d=['badge'=>'100% FREE · ONLINE','start_label'=>'Starting soon','start_text'=>'Date to be announced','email'=>'kortixdigital@gmail.com','social'=>'FB / IG: kortixdigital · TikTok: kortix_digital','site'=>'kortixdigital.com','hashtag'=>'#growwithkortix'];
  return (file_exists(SETTINGS_FILE) ? (json_decode(file_get_contents(SETTINGS_FILE),true) ?: []) : []) + $d;
}
function save_settings(array $s){ file_put_contents(SETTINGS_FILE, json_encode($s, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE), LOCK_EX); }
