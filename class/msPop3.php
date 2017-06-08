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
 * Relève de boite pop3 pour Inbox
 * Class constituée à partir des fonctions de Will Barath (thanks !) sur php.net
 * <http://php.net/manual/fr/intro.imap.php#96415> puis modifiées pour certaines
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */



class msPop3
{

/**
 * Connection à la boite pop3
 * @param  string  $host   host
 * @param  int  $port   port
 * @param  string  $user   user
 * @param  string  $pass   password
 * @param  string  $folder dossier
 * @param  bool $ssl    activation du ssl
 * @return bool          true/false;
 */
    public function pop3_login($host, $port, $user, $pass, $folder="INBOX", $ssl=false)
    {
        $ssl=($ssl==false)?"/novalidate-cert":"";
        return (@imap_open("{"."$host:$port/pop3$ssl"."}$folder", $user, $pass));
    }

/**
 * Informations de base sur le compte pop3
 * @param  resource $connection Connection
 * @return array             data
 */
    public function pop3_stat($connection)
    {
        $check = imap_mailboxmsginfo($connection);
        return ((array)$check);
    }

/**
 * Lister les messages de la boite pop3
 * @param  resource $connection connection
 * @param  int $message    message number
 * @return array             array
 */
    public function pop3_list($connection, $message="")
    {
        if ($message) {
            $range=$message;
        } else {
            $MC = imap_check($connection);
            $range = "1:".$MC->Nmsgs;
        }
        $response = imap_fetch_overview($connection, $range);
        foreach ($response as $msg) {
            $result[$msg->msgno]=(array)$msg;
        }
        return $result;
    }

/**
 * Rapatrier l'en-tête d'un message
 * @param  resource $connection connection
 * @param  int  $message    message number
 * @return array             information message
 */
    public function pop3_retr($connection, $message)
    {
        return(imap_fetchheader($connection, $message, FT_PREFETCHTEXT));
    }

/**
 * Marquer un message à supprimer 
 * @param  resource $connection connection
 * @param  int $message    message number
 * @return void
 */
    public function pop3_dele($connection, $message)
    {
        imap_delete($connection, trim($message));
    }

/**
 * Supprimer tous les messages
 * @param  resource $connection connection
 * @return void
 */
    public function pop3_expunge($connection)
    {
        imap_expunge($connection);
    }

/**
 * Parser le header d'un message
 * @param  string $headers header du message
 * @return array          array
 */
    public function mail_parse_headers($headers)
    {
        $headers=preg_replace('/\r\n\s+/m', '', $headers);
        preg_match_all('/([^: ]+): (.+?(?:\r\n\s(?:.+?))*)?\r\n/m', $headers, $matches);
        foreach ($matches[1] as $key =>$value) {
            $result[$value]=$matches[2][$key];
        }
        return($result);
    }

/**
 * Retourner les composantes d'un message dans un array
 * @param  resource  $connection    connection
 * @param  int  $message       message number
 * @param  bool $parse_headers parser ou pas le header
 * @return array                 Composantes du message
 */
    public function mail_mime_to_array($connection, $message, $parse_headers=false)
    {
        $mail = imap_fetchstructure($connection, $message);


        $rmail = $this->mail_get_parts($connection, $message, $mail, 0);
        if ($parse_headers) {
            $rmail[0]["parsed"]=$this->mail_parse_headers($rmail[0]["data"]);
        }

        if (!isset($mail->parts)) {
            $rmail[1]['charset'] = $rmail[0]['charset'];
            $rmail[1]['format'] = $rmail[0]['format'];
            $rmail[1]['data'] = imap_body($connection, $message);
            if ($mail->encoding == 3) { // 3 = BASE64
            $rmail[1] = base64_decode($rmail[1]);
            } elseif ($mail->encoding == 4) { // 4 = QUOTED-PRINTABLE
            $rmail[1] = quoted_printable_decode($rmail[1]);
            }
        }

        return($rmail);
    }

/**
 * Obtenir les différentes portions d'un message
 * @param  resource $connection connection
 * @param  int $message    message number
 * @param  int $part       portion
 * @param  int $prefix     prefix
 * @return array
 */
    private function mail_get_parts($connection, $message, $part, $prefix)
    {
        $attachments=array();
        $attachments[$prefix]=$this->mail_decode_part($connection, $message, $part, $prefix);
        if (isset($part->parts)) { // multipart
          $prefix = ($prefix == "0")?"":"$prefix.";
            foreach ($part->parts as $number=>$subpart) {
                $attachments=array_merge($attachments, $this->mail_get_parts($connection, $message, $subpart, $prefix.($number+1)));
            }
        }
        return $attachments;
    }

/**
 * Décoder une portion du message
 * @param  resource $connection     connection
 * @param  int $message_number message number
 * @param  int $part           portion
 * @param  int $prefix         prefix
 * @return array
 */
    private function mail_decode_part($connection, $message_number, $part, $prefix)
    {
        $attachment = array();

        if ($part->ifdparameters) {
            foreach ($part->dparameters as $object) {
                $attachment[strtolower($object->attribute)]=$object->value;
                if (strtolower($object->attribute) == 'filename') {
                    $attachment['is_attachment'] = true;
                    $attachment['filename'] = $object->value;
                }
            }
        }

        if ($part->ifparameters) {
            foreach ($part->parameters as $object) {
                $attachment[strtolower($object->attribute)]=$object->value;
                if (strtolower($object->attribute) == 'name') {
                    $attachment['is_attachment'] = true;
                    $attachment['name'] = $object->value;
                }
            }
        }

        $attachment['data'] = imap_fetchbody($connection, $message_number, $prefix);
        if ($part->encoding == 3) { // 3 = BASE64
          $attachment['data'] = base64_decode($attachment['data']);
        } elseif ($part->encoding == 4) { // 4 = QUOTED-PRINTABLE
          $attachment['data'] = quoted_printable_decode($attachment['data']);
        }
        return($attachment);
    }
}
