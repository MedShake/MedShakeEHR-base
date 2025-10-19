<?php

/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2020
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
 * Outils pour les images
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib Michaël Val
 */


class msImageTools
{
    public static function rotate90($source, $dest, $direction = 'right')
    {
        if (!is_file($source)) {
            return false;
        }

        $image = imagecreatefromstring(file_get_contents($source));
        if (!$image) {
            return false;
        }

        $angle = ($direction === 'left') ? 90 : -90;
        $rotatedImage = imagerotate($image, $angle, 0);

        $result = imagejpeg($rotatedImage, $dest);
        imagedestroy($image);
        imagedestroy($rotatedImage);

        return $result;
    }

}
