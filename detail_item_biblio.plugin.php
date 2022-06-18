<?php
/**
 * Plugin Name: Detail Item Biblio
 * Plugin URI: https://github.com/drajathasan/detail_item_biblio
 * Description: Menampilkan rincian sederhana tentang total eksemplar yang tersedia dan dipinjam pada daftar bukusecara langsung
 * Version: 1.0.0
 * Author: Drajat Hasan
 * Author URI: https://t.me/drajathasan
 */

// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// registering menus or hook
$plugin->register("bibliography_init", function(){
    global $dbs, $sysconf;
    if (!isset($_GET['inPopUp'])) {
        include __DIR__ . '/index.biblio.php';
        exit;
    }
});