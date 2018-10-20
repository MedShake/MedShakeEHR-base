<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * Bertrand Boutillier <b.boutillier@gmail.com>
 * http://www.medshake.net
 *
 * MedShakeEHR is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * MedShakeEHR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MedShakeEHR.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Outils divers
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <http://www.github.com/fr33z00>
 */
class msTools
{


/**
 * Vérifier l'existence d'une arbo et la construire sinon
 * @param  string $dirName arborescence
 * @param  string $rights  droits
 * @return void
 */
  public static function checkAndBuildTargetDir($dirName, $rights=0777)
  {
      if (!is_dir($dirName)) {
          mkdir($dirName,$rights, true);
      }
  }


/**
 * Rediriger via un nom de route
 * @param  string $routeAbrev Nom de la route
 * @param  string $type       type de reirection (code http)
 * @return void
 */
  public static function redirRoute($routeAbrev='', $type='')
  {
      global $p, $routes;

      if (!$routeAbrev or !array_key_exists($routeAbrev, $routes)) {
          $routeAbrev='siteIndex';
      }

      if ($type=='301') {
          header('HTTP/1.1 301 Moved Permanently');
      }
      if ($type=='401') {
          header('HTTP/1.1 401 Unauthorized');
      }
      if ($type=='403') {
          header('HTTP/1.1 403 Forbidden');
      }
      if ($type=='404') {
          header('HTTP/1.1 404 Not Found');
      }
      header('Location: '.$p['config']['protocol'].$p['config']['host'].$p['config']['urlHostSuffixe'].$routes[$routeAbrev][1]);
      die;
  }

/**
 * Rdiriger par url
 * @param  string $url  url
 * @param  string $type type de redirection (code http)
 * @return void
 */
  public static function redirection($url, $type='')
  {
      global $p;

      if ($type=='301') {
          header('HTTP/1.1 301 Moved Permanently');
      }
      if ($type=='401') {
          header('HTTP/1.1 401 Unauthorized');
      }
      if ($type=='403') {
          header('HTTP/1.1 403 Forbidden');
      }
      if ($type=='404') {
          header('HTTP/1.1 404 Not Found');
      }
      header('Location: '.$p['config']['protocol'].$p['config']['host'].$p['config']['urlHostSuffixe'].$url);
      die;
  }


/**
 * Obtenir toutes les clefs d'un tableau multidimensionnel
 * @param  array  $array le tableau
 * @return array        les clefs
 */
  public static function array_keys_multi(array $array)
  {
      $keys = array();

      foreach ($array as $key => $value) {
          $keys[] = $key;

          if (is_array($value)) {
              $keys = array_merge($keys, msTools::array_keys_multi($value));
          }
      }

      return $keys;
  }

/**
 * Valider une date du calendrier
 * @param  string $date   la date
 * @param  string $format son format
 * @return bool         true or false
 */
  public static function validateDate($date, $format = 'd/m/Y H:i:s')
  {
      $d = DateTime::createFromFormat($format, $date);
      return $d && $d->format($format) == $date;
  }

/**
 * Valider une chaîne comme étant une expression régulière
 * @param  string  $string expression
 * @return boolean         TRUE / FALSE
 */
  public static function isRegularExpression($string) {
    return @preg_match($string, '') !== FALSE;
  }


/**
 * "bbcodifier" du html
 * @param  string $t le texte
 * @return string    le texte "bbcodifier"
 */
  public static function bbcodifier($t)
  {
      return str_replace(array('<','>'), array('[',']'), $t);
  }

/**
 * "unbbcodifier" du html
 * @param  string $t le texte
 * @return string    le texte unbbcodifier
 */
  public static function unbbcodifier($t)
  {
      return str_replace(array('[',']'), array('<','>'), $t);
  }

/**
 * Retirer header et footer html
 * @param  string $txt texte d'entrée
 * @return string      texte de sortie
 */
  public static function cutHtmlHeaderAndFooter($txt)
  {
      $txt=explode('<!-- stop head -->', $txt);
      if (isset($txt[1])) {
          $txt=explode('<!-- stop body -->', $txt[1]);
      } else {
          $txt=explode('<!-- stop body -->', $txt[0]);
      }
      $txt=$txt[0];

      return $txt;
  }

/**
 * Obtenir le mimetype d'un fichier
 * @param  string $file le fichier avec son chemin
 * @return array
 */
  public static function getmimetype($file)
  {
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      return $finfo->file($file);
  }

/**
 * Encoder UTF8 un array multidimensionnel
 * @param  array $array le tableau
 * @return array        le tableau encodé UTF8
 */
  public static function utf8_converter($array)
  {
    if(is_array($array)) {
      array_walk_recursive($array, function (&$item, $key) {
          if (!mb_detect_encoding($item, 'utf-8', true)) {
              $item = utf8_encode($item);
          }
      });
    }
    return $array;
  }

/**
 * Tranformer une date d/m/Y en Ymd
 * @param  string $d la date d/m/Y
 * @return string    la date Ymd
 */
  public static function readableDate2Reverse($d)
  {
      return $d{6}.$d{7}.$d{8}.$d{9}.$d{3}.$d{4}.$d{0}.$d{1};
  }

/**
 * Nettoyer le nom d'un fichier
 * @param  string $filename nom du fichier
 * @return string           nom du fichier simplifié
 */
  public static function sanitizeFilename($filename)
  {
      return preg_replace("/[^a-z0-9\.]/", "", strtolower($filename));
  }

/**
 * Nettoyer les noms des fichiers d'un répertoire
 * @param  string $dir nom du répertoire
 * @return void
 */
  public static function sanitizeDirectoryFiles($dir)
  {
      $scanned_directory = array_diff(scandir($dir), array('..', '.'));
      if (count($scanned_directory) > 0) {
          foreach ($scanned_directory as $file) {
              if (is_file($dir.$file)) {
                  rename($dir.$file, $dir.msTools::sanitizeFilename($file));
              }
          }
      }
  }

/**
 * Nettoyer un répertoire recursivement
 * @param  string $dir le chemin du répertoire racine
 * @return void
 */
  public static function rmdir_recursive($dir)
  {
      $dir=rtrim($dir, "/");
      foreach (scandir($dir) as $file) {
          if ('.' === $file || '..' === $file) {
              continue;
          }
          if (is_dir("$dir/$file")) {
              msTools::rmdir_recursive("$dir/$file");
          } else {
              unlink("$dir/$file");
          }
      }
      rmdir($dir);
  }

/**
 * Retirer les accents d'une chaine
 * @param  string $texte chaine
 * @return string        chaine
 */
  public static function stripAccents($texte) {
  	$texte = str_replace(
  		array(
  			'à', 'â', 'ä', 'á', 'ã', 'å',
  			'î', 'ï', 'ì', 'í',
  			'ô', 'ö', 'ò', 'ó', 'õ', 'ø',
  			'ù', 'û', 'ü', 'ú',
  			'é', 'è', 'ê', 'ë',
  			'ç', 'ÿ', 'ñ',
  			'À', 'Â', 'Ä', 'Á', 'Ã', 'Å',
  			'Î', 'Ï', 'Ì', 'Í',
  			'Ô', 'Ö', 'Ò', 'Ó', 'Õ', 'Ø',
  			'Ù', 'Û', 'Ü', 'Ú',
  			'É', 'È', 'Ê', 'Ë',
  			'Ç', 'Ÿ', 'Ñ',
  			'œ', 'Œ'
  		),
  		array(
  			'a', 'a', 'a', 'a', 'a', 'a',
  			'i', 'i', 'i', 'i',
  			'o', 'o', 'o', 'o', 'o', 'o',
  			'u', 'u', 'u', 'u',
  			'e', 'e', 'e', 'e',
  			'c', 'y', 'n',
  			'A', 'A', 'A', 'A', 'A', 'A',
  			'I', 'I', 'I', 'I',
  			'O', 'O', 'O', 'O', 'O', 'O',
  			'U', 'U', 'U', 'U',
  			'E', 'E', 'E', 'E',
  			'C', 'Y', 'N',
  			'oe', 'OE'
  		), $texte);
  	return $texte;
  }


/**
 * Obtenir tous les sous repertoires d'un répertoire, avec récusrsivité
 * @param  string $directory           répertoire racine
 * @param  string $directory_seperator séparateur de répertoire dans le chemin (/)
 * @return array                      array des répertoires et sous répertoires
 */
  public static function getAllSubDirectories( $directory, $directory_seperator )
  {
  	$dirs = array_map( function($item)use($directory_seperator){ return $item . $directory_seperator;}, array_filter( glob( $directory . '*' ), 'is_dir') );

  	foreach( $dirs AS $dir )
  	{
  		$dirs = array_merge( $dirs, msTools::getAllSubDirectories( $dir, $directory_seperator ) );
  	}

  	return $dirs;
  }

/**
 * Convertir un object en array
 * @param  object $objet objet à convertir
 * @return array        array
 */
   public static function objectToArray($objet) {
     return json_decode(json_encode($objet), true);
   }


/**
 * Trier un tableau en natural sorting via un nom de clef de colonne
 * Thanks to Torleif Berger <https://www.geekality.net/2017/02/03/php-natural-sort-array-by-a-given-key/>
 * @param  string $key   colonne sur laquelle trier
 * @param  array  $array array à trier
 * @return array        array trié
 */
   public static function array_natsort_by($key, array &$array)
   {
       return usort($array, function($x, $y) use ($key)
       {
           return strnatcasecmp($x[$key] ?? null, $y[$key] ?? null);
       });
   }

/**
 * Vérifier si une commande système existe
 * @param  string $cmd nom de la commande
 * @return boolean      true / false
 */
   public static function commandExist($cmd) {
      $return = shell_exec(sprintf("which %s", escapeshellarg($cmd)));
      return !empty($return);
   }

}
