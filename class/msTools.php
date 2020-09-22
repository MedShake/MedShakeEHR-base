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
 * @contrib Maxime DEMAREST <maxime@indelog>
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
          $routeAbrev='root';
      }

      if ($type=='301') {
          http_response_code(301);
      }
      if ($type=='401') {
          http_response_code(401);
      }
      if ($type=='403') {
          http_response_code(403);
      }
      if ($type=='404') {
          http_response_code(404);
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
          http_response_code(301);
      }
      if ($type=='401') {
          http_response_code(401);
      }
      if ($type=='403') {
          http_response_code(403);
      }
      if ($type=='404') {
          http_response_code(404);
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
 * Obtenir toutes les values d'un tableau multidimensionnel
 * @param  array  $array le tableau
 * @return array        les values
 */
  public static function array_values_multi(array $array)
  {
      $values = array();

      foreach ($array as $key => $value) {


          if (is_array($value)) {
              $values = array_merge($values, msTools::array_values_multi($value));
          } else {
            $values[] = $value;
          }
      }

      return $values;
  }

/**
 * Retirer les éléments d'un array avec une liste de clefs
 * @param  array $tab  array à traiter
 * @param  array  $keys liste des clefs
 */
  public static function arrayRemoveByKey(&$tab, $keys=[] ) {
    foreach($keys as $key){
      unset($tab[$key]);
    }
  }

/**
 * Valider une date du calendrier
 * @param  string $date   la date
 * @param  string $format son format
 * @return bool         true or false
 */
  public static function validateDate($date, $format = 'd/m/Y H:i:s')
  {
      if(!is_string($date)) return false;
      $d = DateTime::createFromFormat($format, $date);
      return $d && $d->format($format) == $date;
  }

/**
 * Formater une date sortie dun champ datetime SQL
 * @param  string $dateSQL      date Y-m-d H:i:s
 * @param  string $outputFormat format de date souhaité
 * @return string               date formatée
 */
  public static function sqlDateToDisplayDate($dateSQL, $outputFormat = 'd/m/Y')
  {
    if(!is_string($dateSQL)) return false;
    $d = DateTime::createFromFormat('Y-m-d H:i:s', $dateSQL);
    return $d->format($outputFormat);
  }

/**
 * Convertisseur générique de date
 * @param  string $dateIn        date
 * @param  string $dateInFormat  format date en entrée
 * @param  string $dateOutFormat format date en sortie
 * @return string                date convertie
 */
  public static function dateConverter($dateIn, $dateInFormat, $dateOutFormat) {
    if(!msTools::validateDate($dateIn, $dateInFormat)) return false;
    $d = DateTime::createFromFormat($dateInFormat, $dateIn);
    return $d->format($dateOutFormat);
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
 * Convertir un ficher texte iso en UTF8 sur lui même ou vers un autre fichier
 * @param  string $source      fichier source
 * @param  string $destination fichier destination
 * @return bool              true/false
 */
  public static function convertPlainTextFileToUtf8($source, $destination='') {
    if(!$destination) $destination=$source;
    $contenu=file_get_contents($source);
    if (!mb_detect_encoding($contenu, 'utf-8', true)) {
      $contenu = utf8_encode($contenu);
      return (bool)file_put_contents($destination, $contenu);
    } elseif ($destination!=$source) {
      return (bool)file_put_contents($destination, $contenu);
    } else {
      return true;
    }
  }

/**
 * Tranformer une date d/m/Y en Ymd
 * @param  string $d la date d/m/Y
 * @return string    la date Ymd
 */
  public static function readableDate2Reverse($d)
  {
      return $d[6].$d[7].$d[8].$d[9].$d[3].$d[4].$d[0].$d[1];
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
 * Obtenir tous les sous repertoires d'un répertoire, avec récursivité
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
 * Trier un tableau en natural sorting via un nom de clef de colonne, en préservant les clefs
 * @param  string $key   colonne sur laquelle trier
 * @param  array  $array tableau à trier
 * @return array        tableau trié
 */
   public static function array_unatsort_by($key, array &$array)
   {
       return uasort($array, function($x, $y) use ($key)
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

/**
 * Obtenir la taille d'un fichier en version lisible simple
 * @param  string  $file     fichier (dont chemin)
 * @param  integer $decimals nombre de décimales
 * @return string            taille lisible
 */
   public static function getFileSize($file, $decimals = 2) {
     $bytes=filesize($file);
     return self::readabledSize($bytes, $decimals);
   }

/**
 * Obtenir une taille en bytes en unitées plus lisibles
 * @param  int  $bytes    taille en bytes
 * @param  integer $decimals décimales pour arrondi
 * @return string            taille lisible
 */
   public static function readabledSize($bytes, $decimals = 2) {
     $sz = ['o', 'Ko', 'Mo', 'Go', 'To', 'Po'];
     $factor = floor((strlen($bytes) - 1) / 3);
     return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
   }

/**
 * Ajouter un slash final au chemin si absent
 * @param string $dir chemin
 */
   public static function setDirectoryLastSlash($dir) {
     if(substr($dir, -1) == '/') return $dir;
     return $dir.'/';
   }

/**
 * Obtenir un tableau ou les clefs sont préfixées
 * @param  array $tab    tableau 2 dimensions
 * @param  string $prefix préfixe
 * @return array         tableau avec clefs préfixées
 */
   public static function getPrefixKeyArray($tab, $prefix) {
     if(empty($tab)) return [];
     foreach($tab as $k=>$v) {
       $prefixTab[$prefix.$k]=$v;
     }
     return $prefixTab;
   }

/**
 * Générer une chaine aléatoire de caractères
 * @param  integer $length longueur de la chaine
 * @param  string  $chars  caractères éligibles
 * @return string          chaine aléatoire
 */
   public static function getRandomStr($length = 8, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789') {
     $count = mb_strlen($chars);
     for ($i = 0, $result = ''; $i < $length; $i++) {
         $index = rand(0, $count - 1);
         $result .= mb_substr($chars, $index, 1);
     }
     return $result;
   }

/**
* Obtenir l'orientation d'un PDF via pdftk en se basant sur ses dimensions
* @return string landscape ou portait ou false si pb
*/
   public static function getPdfOrientation($fileFullPath) {
     if(!is_file($fileFullPath)) return false;
     if(strtolower(pathinfo($fileFullPath,  PATHINFO_EXTENSION)) != 'pdf') return false;

     exec('pdftk '.escapeshellarg($fileFullPath).' dump_data | grep "PageMediaDimensions"' ,$output);
     if(!isset($output[0])) return false;
     $dim = explode(" ", $output[0]);
     if(!isset($dim[2])) return false;
     if($dim[1] > $dim[2]) {
       return 'landscape';
     } elseif($dim[1] <= $dim[2]) {
       return 'portrait';
     } else {
       return false;
     }
   }
/**
 * Générer un code-barres au format svg
 * @param  string  $type       type de code rpps ou adeli
 * @param  string  $code       code à générer
 * @return boolean             true if created, false if exisit
 */
    public static function genBareCodeFile($type, $code) {
        global $p;
        $created = false;

        # checks
        if (! in_array($type, array('rpps', 'adeli'))) {
            throw new Exception('Le type de code doit être \'rpps\' ou \'adeli\'');
        }
        $barcodedir = $p['config']['stockageLocation'].'barecode/';
        self::checkAndBuildTargetDir($barcodedir, $rights=0755);

        if (file_exists($barcodedir.'barecode-'.$type.'-'.$code.'.svg')) {
            return 0;
        } else {
            $generator = new Picqer\Barcode\BarcodeGeneratorSVG();
            if (file_put_contents($barcodedir.'barecode-'.$type.'-'.$code.'.svg', $generator->getBarcode($code, $generator::TYPE_CODE_128))) {
                return true;
            } else {
                throw new Exception('Echec de la création du fichier '.$barcodedir.'barecode-'.$type.'-'.$code.'.svg');
            }
        }
    }

}
