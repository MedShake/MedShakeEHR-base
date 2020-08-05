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
 * Système
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */
class msSystem
{

    /**
     * Obtenir le statut système en base
     * @return string normal / maintenance
     */
    public static function getSystemState()
    {
        return msSQL::sqlUniqueChamp("SELECT value FROM `system` WHERE name='state' and groupe = 'system' limit 1");
    }

    /**
     * Obtenir le nombre de pro utilisateurs
     * @return int nombre de pros utilisateurs
     */
    public static function getProUserCount()
    {
        return (int)msSQL::sqlUniqueChamp("SELECT COUNT(*) FROM people WHERE type='pro' AND name!=''");
    }

    /**
     * Permuter l'état du système entre normal et maintenance
     * @return string   état du système ou false si pb
     */
    public static function toggleSystemState()
    {
        global $p;
        if ($p['user']['rank'] != 'admin') return false;

        if ($p['config']['systemState'] == 'normal') {
            $statut = 'maintenance';
        } else {
            $statut = 'normal';
        }
        $data = [
            'name' => 'state',
            'groupe' => 'system',
            'value' => $statut
        ];
        if (msSQL::sqlInsert("system", $data)) {
            return $statut;
        } else {
            return false;
        }
    }

    /**
     * Routes
     * @param array $preDefinedRoutes jeux prédéfinis de routes
     * @return array                  match routeur
     */
    public static function getRoutes($preDefinedRoutes = [])
    {
        global $p, $routes;
        $installedModules = msModules::getInstalledModulesNames();

        if ((!empty($preDefinedRoutes))) {
            $routes = $preDefinedRoutes;
        } else {
            $routes[] = 'base';
            $routes[] = 'login';
            if (isset($p['user']['rank']) and $p['user']['rank'] == 'admin') $routes[] = 'configuration';

            $inclusionsRules = yaml_parse_file($p['homepath'] . 'config/routes/routesInclusionRules.yml');

            /* fichier additionnel pour les modules */
            foreach ($installedModules as $module) {
                $file = $p['homepath'] . 'config/routes/'.$module.'/routesInclusionRules.yml';
                if ($module != 'base' and is_file($file)) {
                    $inclusionsRules = array_merge($inclusionsRules, yaml_parse_file($file));
                }
            }

            if (!empty($inclusionsRules)) {
                foreach ($inclusionsRules as $rule => $f) {
                    if (isset($p['config'][$rule]) and $p['config'][$rule] == 'true') $routes[] = $f;
                }
            }
        }

        $router = new AltoRouter();
        foreach ($routes as $route) {
            $file = $p['homepath'] . 'config/routes/routes-' . $route . '.yml';
            if (is_file($file)) {
                $routes = yaml_parse_file($file);
                $router->addRoutes($routes);
            }

            // ajout de routes complémentaires (via module)
            foreach ($installedModules as $module) {
                $file = $p['homepath'] . 'config/routes/' . $module . '/routes-' . $route . '.yml';
                if ($module != "base" and is_file($file)) {
                    $routes = yaml_parse_file($file);
                    $router->addRoutes($routes);
                }
            }
        }

        $router->setBasePath($p['config']['urlHostSuffixe']);
        return $router->match();
    }
}
