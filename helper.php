<?php
/**
 * @composedBy Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-06-18 17:44:22
 * @modify date 2022-06-18 17:44:34
 * @license GPLv3
 * @desc [description]
 */

function showTitleAuthorsSpecial($obj_db, $array_data)
{
  global $sysconf;
  global $label_cache;
  $_opac_hide = false;
  $_promoted = false;
  $_labels = '';
  $_image = '';

  $img = 'images/default/image.png';
  // biblio author detail
  if ($sysconf['index']['type'] == 'default') {
      $_sql_biblio_q = sprintf('SELECT b.title, a.author_name, opac_hide, promoted, b.labels,b.image FROM biblio AS b
          LEFT JOIN biblio_author AS ba ON b.biblio_id=ba.biblio_id
          LEFT JOIN mst_author AS a ON ba.author_id=a.author_id
          WHERE b.biblio_id=%d', $array_data[0]);
      $_biblio_q = $obj_db->query($_sql_biblio_q);
      $_authors = '';
      while ($_biblio_d = $_biblio_q->fetch_row()) {
          $_title = $_biblio_d[0];
          $_image = $_biblio_d[5];
          $_authors .= $_biblio_d[1].' - ';
          $_opac_hide = (integer)$_biblio_d[2];
          $_promoted = (integer)$_biblio_d[3];
          $_labels = $_biblio_d[4];
      }
      $_authors = substr_replace($_authors, '', -3);
      if($_image!='' AND file_exists('../../../images/docs/'.$_image)){
        $img = 'images/docs/'.urlencode($_image);  
      }
      $_output = '<div class="media">
                    <img class="mr-3 rounded" src="../lib/minigalnano/createthumb.php?filename='.$img.'&width=50&height=65" alt="cover image">
                    <div class="media-body">
                      <div class="title">'.stripslashes($_title).'</div><div class="authors">'.$_authors.'</div>
                    </div>
                  </div>';
  } else {
  	    $_q = $obj_db->query("SELECT opac_hide,promoted FROM biblio WHERE biblio_id=".$array_data[0]);
	    while ($_biblio_d = $_q->fetch_row()) {
	      $_opac_hide = (integer)$_biblio_d[0];
	      $_promoted  = (integer)$_biblio_d[1];
	    }

      if($array_data[3]!='' AND file_exists('../../../images/docs/'.$array_data[3])){
        $img = 'images/docs/'.urlencode($array_data[3]);  
      }
      $summary = ambilData($obj_db, (is_numeric($array_data[6]) ? $array_data[6] : 0), $array_data[0]);
      $_output = '<div class="media">
                    <img class="mr-3 rounded" src="../lib/minigalnano/createthumb.php?filename='.$img.'&width=50&height=65" alt="cover image">
                    <div class="media-body">
                      <div class="title">'.stripslashes($array_data[1]).'</div><div class="authors">'.$array_data[4].'</div>
                      <div id="biblio'.$array_data[0].'" class="flex-column" style="display: none">
                        <strong>Rincian eksemplar</strong>
                        <span>Tidak dipinjam : ' . $summary['tersedia'] . '</span>
                        <span>Sedang dipinjam : ' . $summary['dipinjam'] . '</span>
                      </div>
                    </div>
                  </div>';
      $_labels = $array_data[2];
  }
  // check for opac hide flag
  if ($_opac_hide) {
      $_output .= '<div class="badge badge-dark" title="' . __('Hidden in OPAC') . '">'.__('Hidden in OPAC').'</div>&nbsp;';
  }
  // check for promoted flag
  if ($_promoted) {
      $_output .= '<div class="badge badge-info" title="' . __('Promoted To Homepage') . '">'.__('Promoted To Homepage').'</div>&nbsp;';
  }
  // labels
  // Edit by Eddy Subratha
  if ($_labels) {
      $arr_labels = @unserialize($_labels);
      if ($arr_labels !== false) {
	  foreach ($arr_labels as $label) {
	      if (!isset($label_cache[$label[0]]['name'])) {
	          $_label_q = $obj_db->query('SELECT label_name, label_desc, label_image FROM mst_label AS lb WHERE lb.label_name=\''.$label[0].'\'');
              $_label_d = $_label_q->fetch_row();
	          $label_cache[$_label_d[0]] = array('name' => $_label_d[0], 'desc' => $_label_d[1], 'image' => $_label_d[2]);
	      }
	    //   $_output .= ' <img src="'.SWB.'lib/minigalnano/createthumb.php?filename='.IMG.'/labels/'.urlencode($label_cache[$label[0]]['image']).'&amp;width=16&amp;" title="'.$label_cache[$label[0]]['desc'].'" />';
	      $_output .= '<div class="badge badge-light">'.$label_cache[$label[0]]['desc'].'</div>&nbsp;';
	  }
	}
  }
  return $_output;
}

function ambilData($dbs, $available, $id)
{
    $id = $dbs->escape_string($id);
    $SQL = <<<SQL
        SELECT COUNT(`loan_id`) AS `total` FROM `loan_history` WHERE `biblio_id` = {$id} AND `is_return` = 0 AND `is_lent` = 1
    SQL;

    $q = $dbs->query($SQL);
    $d = $q->fetch_object();

    return $q->num_rows > 0 ? ['tersedia' => ($available - $d->total), 'dipinjam' => $d->total] : ['tersedia' => 0, 'dipinjam' => 0];
}

function bisaDiKlik($dbs, $data)
{
    return is_numeric($data[6]) ? '<span class="bisaDiKlik notAJAX" data-target="'.$data[0].'">' . $data[6] . '</span>' : $data[6];
}