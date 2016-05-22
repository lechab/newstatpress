<?php

/**
 * API: WP Version
 *
 * Return the current version of wordpress as json/html
 *
 * @param typ the type of result (Json/Html)
 * @return the result
 */
function nsp_ApiWpVersion($typ) {
  global $_NEWSTATPRESS;

  $resultJ=array(
    'wpversion' => get_bloginfo('version')
  );

  if ($typ=="JSON") return $resultJ;         // avoid to calculte HTML if not necessary
  
  $resultH="<div>".$resultJ[$var]."</div>";  
  return $resultH;
}
?>
