<?php

/**
 * API: Version
 *
 * Return the current version of newstatpress as json/html
 *
 * @param typ the type of result (Json/Html)
 * @return the result
 */
function nsp_ApiVersion($typ) {
  global $_NEWSTATPRESS;

  $resultJ=array(
    'version' => $_NEWSTATPRESS['version']
  );

  if ($typ=="JSON") return $resultJ;         // avoid to calculte HTML if not necessary
  
  $resultH="<div>".$resultJ[$var]."</div>";  
  return $resultH;
}
?>