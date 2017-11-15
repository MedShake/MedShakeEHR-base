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
 * Actions communes aux formulaires médicaux et calculs médicaux
 * nécessaires au module
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 /**
  * Actions communes aux formulaires médicaux
  *
  */


//disable
function disabledForm(classToDisabled) {
  $(classToDisabled + ' input').attr('disabled', 'disabled');
  $(classToDisabled + ' select').attr('disabled', 'disabled');
  $(classToDisabled + ' textarea').attr('disabled', 'disabled');
  $(classToDisabled + ' checkbox').attr('disabled', 'disabled');
}

//enabled
function enabledForm(classToEnabled) {
  $(classToEnabled + ' input').removeAttr('disabled');
  $(classToEnabled + ' select').removeAttr('disabled', 'disabled');
  $(classToEnabled + ' textarea').removeAttr('disabled', 'disabled');
  $(classToEnabled + ' checkbox').removeAttr('disabled', 'disabled');
}

/**
 * Fonctions JS pour les calcules médicaux
 *
 */


// arrondir
function arrondir(nombre) {
  return Math.round(nombre * 1) / 1
}

function arrondir10(nombre) {
  return Math.round(nombre * 10) / 10
}

function arrondir100(nombre) {
  return Math.round(nombre * 100) / 100
}

//percentiles
function pc100(PCm, SA) {
  PC = arrondir100(44.4924 * 1 - (2.7182 * (SA)) * 1 + (0.6673 * Math.pow((SA), 2)) * 1 - (0.0107 * Math.pow((SA), 3)));
  PCds = arrondir100(2.7945 * 1 + (0.345 * (SA)));
  PCzs = arrondir100((PCm - PC) / PCds);
  PC100 = arrondir100((1 / (1 + Math.exp(-1.5976 * (PCzs) - 0.0706 * Math.pow((PCzs), 3)))) * 100);
  return arrondir(PC100);
}

function bip100(BIPm, SA) {
  BIP = arrondir100(31.2452 * 1 - (2.8466 * (SA)) * 1 + (0.2577 * Math.pow((SA), 2)) * 1 - (0.0037 * Math.pow((SA), 3)));
  BIPds = arrondir100(1.5022 * 1 + (0.0636 * (SA)));
  BIPzs = arrondir100((BIPm - BIP) / BIPds);
  BIP100 = arrondir100((1 / (1 + Math.exp(-1.5976 * (BIPzs) - 0.0706 * Math.pow((BIPzs), 3)))) * 100);
  return arrondir(BIP100);
}

function pa100(PAm, SA) {
  PA = arrondir100(42.7794 * 1 - (2.7882 * (SA)) * 1 + (0.5715 * Math.pow((SA), 2)) * 1 - (0.008 * Math.pow((SA), 3)));
  PAds = arrondir100(-2.3658 * 1 + (0.6459 * (SA)));
  PAzs = arrondir100((PAm - PA) / PAds);
  PA100 = arrondir100((1 / (1 + Math.exp(-1.5976 * (PAzs) - 0.0706 * Math.pow((PAzs), 3)))) * 100);
  return arrondir(PA100);
}


function lf100(LFm, SA) {
  LF = arrondir100(-27.085 * 1 + (2.9223 * (SA)) * 1 + (0.0148 * Math.pow((SA), 2)) * 1 - (0.0006 * Math.pow((SA), 3)));
  LFds = arrondir100(1.0809 * 1 + (0.0609 * (SA)));
  LFzs = arrondir100((LFm - LF) / LFds);
  LF100 = arrondir100((1 / (1 + Math.exp(-1.5976 * (LFzs) - 0.0706 * Math.pow((LFzs), 3)))) * 100);
  return arrondir(LF100);
}

function poids100(EPFcalc, SA) {
  EPFatt = arrondir100(Math.pow(2.71828182845904, (0.578 + (0.332 * (SA)) * 1 - (0.00354 * Math.pow((SA), 2)))));
  EPFds = arrondir100(0.127 * (EPFatt));
  EPFzs = arrondir100((EPFcalc - EPFatt) / EPFds);
  EPF100 = arrondir100((1 / (1 + Math.exp(-1.5976 * (EPFzs) - 0.0706 * Math.pow((EPFzs), 3)))) * 100);
  return arrondir(EPF100);
}


// calcul IMC
function imcCalc(poids, taille) {

  taille = taille.replace(",", ".") / 100;
  poids = poids.replace(",", ".");

  if (taille > 0 && poids > 0) {
    imc = Math.round(poids / (taille * taille) * 10) / 10;
    if (imc >= 5 && imc < 90) {
      imc = imc;
    } else {
      imc = '';
    }
    return imc;
  }
}


