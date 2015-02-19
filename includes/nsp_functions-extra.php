<?php

function nsp_DisplayTabsNavbarForMenuPage($menu_tabs, $current,$ref) {

    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $menu_tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?page=$ref&tab=$tab'>$name</a>";
    }
    echo '</h2>';
}

?>
