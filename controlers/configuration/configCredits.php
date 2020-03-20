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
 * Config : remerciements
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$template='configCredits';

$p['page']['thanks']['Twig']=array(
  'description'=>'Template engine for PHP',
  'url'=>'https://twig.sensiolabs.org'
);
$p['page']['thanks'][
  'AltoRouteur']=array(
  'description'=>'Routing class for PHP',
  'url'=>'http://altorouter.com/'
);
$p['page']['thanks'][
  'Spyc']=array(
  'description'=>'A YAML loader/dumper written in PHP',
  'url'=>'http://altorouter.com/'
);
$p['page']['thanks'][
  'GUMP']=array(
  'description'=>'A standalone PHP data validation and filtering class',
  'url'=>'https://github.com/Wixel/GUMP'
);
$p['page']['thanks'][
  'Bootstrap']=array(
  'description'=>'HTML, CSS and JS framework',
  'url'=>'http://getbootstrap.com/'
);
$p['page']['thanks'][
  'Dompdf']=array(
  'description'=>'An HTML to PDF converter',
  'url'=>'https://github.com/dompdf/dompdf'
);
$p['page']['thanks'][
  'PHPMailer']=array(
  'description'=>'An email creation and transfer class for PHP',
  'url'=>'https://github.com/PHPMailer/PHPMailer'
);
$p['page']['thanks'][
  'Bootstrap 3 Datepicker v4']=array(
  'description'=>'Datepicker for Bootstrap',
  'url'=>'http://eonasdan.github.io/bootstrap-datetimepicker/'
);
$p['page']['thanks'][
  'Bootstrap 3 colorpicker v3']=array(
  'description'=>'colorpicker for Bootstrap',
  'url'=>'https://farbelous.github.io/bootstrap-colorpicker/'
);
$p['page']['thanks'][
  'Moment.js']=array(
  'description'=>'Parse, validate, manipulate, and display dates and times in JavaScript',
  'url'=>'http://momentjs.com/'
);
$p['page']['thanks'][
  'Magnific Popup']=array(
  'description'=>'A responsive lightbox & dialog script',
  'url'=>'http://dimsemenov.com/plugins/magnific-popup/'
);
$p['page']['thanks'][
  'TypeWatch']=array(
  'description'=>'A jquery plugin to determine when a user has stopped typing in a text field',
  'url'=>'https://github.com/dennyferra/TypeWatch'
);
$p['page']['thanks'][
  'tinymce']=array(
  'description'=>'WYSIWYG HTML editor',
  'url'=>'https://www.tinymce.com/'
);
$p['page']['thanks'][
  'jQuery']=array(
  'description'=>'JavaScript library',
  'url'=>'http://jquery.com/'
);
$p['page']['thanks'][
  'Uploader']=array(
  'description'=>'JQuery File Uploader',
  'url'=>'https://github.com/danielm/uploader'
);
$p['page']['thanks'][
  'jSignature']=array(
  'description'=>'A jQuery plugin that simplifies creation of a signature capture field in a browser window',
  'url'=>'https://willowsystems.github.io/jSignature/#/about/'
);
$p['page']['thanks'][
  'Orthanc']=array(
  'description'=>'Open-source, lightweight DICOM server',
  'url'=>'http://www.orthanc-server.com/'
);
$p['page']['thanks'][
  'PHP']=array(
  'description'=>'A popular general-purpose scripting language',
  'url'=>'http://php.net/'
);
$p['page']['thanks'][
  'FullCalendar']=array(
  'description'=>'A JavaScript event calendar. Customizable and open source',
  'url'=>'https://fullcalendar.io/'
);
$p['page']['thanks'][
  'jQuery contextMenu']=array(
  'description'=>'jQuery contextMenu plugin & polyfill',
  'url'=>'https://github.com/swisnl/jQuery-contextMenu'
);
$p['page']['thanks'][
  'panzoom']=array(
  'description'=>'A jQuery plugin for panning and zooming elements',
  'url'=>'https://github.com/timmywil/jquery.panzoom/'
);
$p['page']['thanks'][
  'jquery-mousewheel']=array(
  'description'=>'A jQuery plugin that adds cross-browser mouse wheel support',
  'url'=>'https://github.com/jquery/jquery-mousewheel/'
);
$p['page']['thanks'][
  'Mocha']=array(
  'description'=>'Mocha test framework',
  'url'=>'https://visionmedia.github.io/mocha/'
);
$p['page']['thanks'][
  'URLCrypt']=array(
  'description'=>'PHP library to securely encode and decode short pieces of arbitrary binary data in URLs',
  'url'=>'https://github.com/atrapalo/URLcrypt'
);
$p['page']['thanks'][
  'Stupid jQuery Table Sort']=array(
  'description'=>'This is a stupid jQuery table sorting plugin',
  'url'=>'https://github.com/joequery/Stupid-Table-Plugin'
);
$p['page']['thanks'][
  'Spout']=array(
  'description'=>'Spout is a PHP library to read and write spreadsheet files',
  'url'=>'https://github.com/box/spout'
);
$p['page']['thanks'][
  'EvalMath']=array(
  'description'=>'Safely evaluate math expressions',
  'url'=>'https://github.com/dbojdo/eval-math'
);
$p['page']['thanks'][
  'CodeMirror']=array(
  'description'=>'CodeMirror is a versatile text editor implemented in JavaScript for the browser',
  'url'=>'https://codemirror.net/'
);
$p['page']['thanks'][
  'Font Awesome']=array(
  'description'=>'Font Awesome, the web\'s most popular icon set and toolkit',
  'url'=>'https://fontawesome.com/'
);
$p['page']['thanks'][
  'Autosize']=array(
  'description'=>'A small, stand-alone script to automatically adjust textarea height',
  'url'=>'http://www.jacklmoore.com/autosize/'
);

uksort($p['page']['thanks'], 'strcasecmp');
