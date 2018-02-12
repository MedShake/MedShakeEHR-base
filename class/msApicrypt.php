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
 *
 * Les termes et conditions de la présente licence GPL v3 ne s’appliquent pas aux composants APICRYPT, fournis par la société APICEM SARL, APICEM Développement ou l’association APICEM, qui restent la propriété exclusive desdites entités. Le code source des composants APICRYPT ne saurait être distribué dans le cadre de la licence du logiciel MedShakeEHR.
 * La réutilisation du code source du logiciel MedShakeEHR à quelques fins que ce soit nécessitera pour le responsable de développements de prendre contact avec la société APICEM SARL afin de procéder à l’établissement d’un contrat de partenariat ainsi qu’à des tests de validité de l’intégration des composants APICRYPT.
 * Le logiciel issu de cette réutilisation ne peut en effet prétendre être « compatible APICRYPT » sans avoir effectué ces démarches préalables et la société APICEM SARL ne saurait être tenue responsable d’éventuels problèmes de réceptions, de traitements ou d’envois de messages au travers de ce logiciel. 
 *
 */

/**
 * Chiffrer / déchiffrer à l'aide des binaires Apicrypt
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msApicrypt
{


/**
 * chiffrer le corps du message
 * @param  string $texte texte à crypter
 * @param  string $to    destinataire
 * @return string        texte crypté
 */
 public static function crypterCorps($texte, $to)
 {
     global $p;

     //créer dir specifique utilisateur
     msTools::checkAndBuildTargetDir($p['config']['apicryptCheminFichierNC'].$p['user']['id']);
     msTools::checkAndBuildTargetDir($p['config']['apicryptCheminFichierC'].$p['user']['id']);

     $texte=iconv("UTF-8", "iso-8859-1//TRANSLIT", $texte);

    //donner un nom au fichier
    $filename=md5($texte.$to.time());

    //créer le fichier texte non crypté
    $destinationNC=$p['config']['apicryptCheminFichierNC'].$p['user']['id'].'/'.$filename;
     file_put_contents($destinationNC, $texte);

    //créer la destination pour le fichier support du texte crypté et la commande pour crypter
    $destinationC=$p['config']['apicryptCheminFichierC'].$p['user']['id'].'/'.$filename;
     $apicrypt=$p['config']['apicryptCheminVersBinaires'].'apicrypt -s '.$destinationNC.' -o '.$destinationC.' -u '.$p['config']['apicryptUtilisateur'].' -k '.$p['config']['apicryptCheminVersClefs'].' -d '.$to.' -v';


    //crypter
    exec('sudo '.$apicrypt);

    //récupérer le texte crypté
    $texte=file_get_contents($destinationC);

    //supprimer les fichiers de travail.
    unlink($destinationC);
     unlink($destinationNC);


    //retourner le texte pour inclusion dans le mail
    return $texte;
 }


/**
 * chiffrer une pièce jointe
 * @param  string $file     fichier et son chemin complet
 * @param  string $to       destinataire
 * @param  string $filename nom du fichier de destination
 * @return void
 */
    public static function crypterPJ($file, $to, $filename)
    {
        global $p;

        //créer dir specifique utilisateur
        msTools::checkAndBuildTargetDir($p['config']['apicryptCheminFichierNC'].$p['user']['id']);
        msTools::checkAndBuildTargetDir($p['config']['apicryptCheminFichierC'].$p['user']['id']);

        $destinationC=$p['config']['apicryptCheminFichierC'].$p['user']['id'].'/'.$filename.'.apz';
        $apicrypt=$p['config']['apicryptCheminVersBinaires'].'apicryptpj -s '.escapeshellarg($file).' -o '.escapeshellarg($destinationC).' -u '.$p['config']['apicryptUtilisateur'].' -k '.$p['config']['apicryptCheminVersClefs'].' -d '.escapeshellarg($to).' -v';

        //echo $apicrypt;
        exec('sudo '.$apicrypt);
    }

/**
 * Déchiffrer une pièce jointe
 * @param  string $filec  fichier chiffré
 * @param  string $filenc fichier de destination
 * @return void
 */
    public static function decrypterPJ($filec, $filenc)
    {
        global $p;
        $apicrypt=$p['config']['apicryptCheminVersBinaires'].'apiuncryptpj -s '.escapeshellarg($filec).' -o '.escapeshellarg($filenc).' -k '.$p['config']['apicryptCheminVersClefs'];

        //go $apicrypt;
        exec('sudo '.$apicrypt);
    }

/**
 * Déchiffrer le corps du message (qu'on sauve ici sous forme de txt dans la inbox)
 * @param  string $filec  fichier chiffré (avec son chemin)
 * @param  string $filenc fichier déchiffré (et son chemin)
 * @return void
 */
    public static function decrypterCorps($filec, $filenc)
    {
        global $p;
        $apicrypt=$p['config']['apicryptCheminVersBinaires'].'apiuncrypt -s '.escapeshellarg($filec).' -o '.escapeshellarg($filenc).' -k '.$p['config']['apicryptCheminVersClefs'];

        //go $apicrypt;
        exec('sudo '.$apicrypt);
    }
}
