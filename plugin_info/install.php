<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function InstallComposerDependencies() {
    $pluginId = basename(realpath(__DIR__ . '/..'));
    log::add($pluginId, 'info', 'Install composer dependencies');
    $cmd = 'cd ' . __DIR__ . '/../;export COMPOSER_ALLOW_SUPERUSER=1;export COMPOSER_HOME="/tmp/composer";' . system::getCmdSudo() . 'composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader;' . system::getCmdSudo() . ' chown -R www-data:www-data *';
    shell_exec($cmd);
}

function gotify_post_market_install() {
    log::add('gotify', 'alert', 'post install');
}

function gotify_install() {
    $pluginId = basename(realpath(__DIR__ . '/..'));
    InstallComposerDependencies();

    config::save("api::{$pluginId}::mode", 'disable');
    config::save("api::{$pluginId}::restricted", 1);
}

function gotify_update() {
    $pluginId = basename(realpath(__DIR__ . '/..'));
    InstallComposerDependencies();

    $clientToken = config::byKey('clientToken', $pluginId);
    /** @var gotify */
    foreach (eqLogic::byType($pluginId) as $eqLogic) {
        $appToken = $eqLogic->getConfiguration('token');
        if ($appToken != '') {
            $eqLogic->setConfiguration('appToken', $appToken);
        }
        $eqLogic->setConfiguration('token', null);

        if ($clientToken != '') {
            $eqLogic->setConfiguration('clientToken', $clientToken);
        }
        $eqLogic->save(true);
    }
    config::remove('clientToken', $pluginId);

    config::save("api::{$pluginId}::mode", 'disable');
    config::save("api::{$pluginId}::restricted", 1);
}

function gotify_remove() {
    $pluginId = basename(realpath(__DIR__ . '/..'));
    config::remove('api', $pluginId);
    config::remove("api::{$pluginId}::mode");
    config::remove("api::{$pluginId}::restricted");
}
