<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2019
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
 * Les questionnaires : validation
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msFormValidation extends msForm
{

/**
 * Règles de validation
 * @var array
 */
  private $_validationrules;

/**
 * Règles de validation sup
 * @var array
 */
  private $_contextualValidationRules=[];

/**
 * Utiliser les messages d'erreur personalisés
 * @var boolean
 */
  private $_contextualValidationErrorsMsg=true;

/**
 * Définir les règles de validation sup contextuelles
 * @param string $field champ concerné (nom du type)
 * @param array $rules règles
 */
    public function setContextualValidationRule($field, $rules) {
      $rules = (array) $rules;
      if(!isset($field)) {
          throw new Exception('Field is not set');
      }
      if(!empty($rules)) {
        foreach($rules as $rule) {
          $this->_contextualValidationRules[$field][]=$rule;
        }
        return true;
      }
      return false;
    }

/**
 * Définir si on utilise ou non les messages d'erreur de validation personalisés
 * @param boolean $contextualValidationRules true/false
 */
    public function setContextualValidationErrorsMsg($contextualValidationRules) {
      if(!is_bool($contextualValidationRules)) {
          throw new Exception('ContextualValidationRules is not boolean');
      }
      $this->_contextualValidationErrorsMsg=$contextualValidationRules;
    }

/**
 * Obtenir les règles de validation du formulaire
 * @return array Array des règles de validation pour GUMP
 */
    public function getValidation()
    {
        if ($formYaml=$this->getFormFromDb($this->_formID)) {
            return $this->_formValidation($formYaml);
        } else {
            throw new Exception('Validations cannot be done');
        }
    }

/**
 * Traiter le form pour obtenir les règles de validation et valider
 * @param  array $t Le form au format PHP
 * @return bool    True ou false en fonction du résultat de validation
 */
    private function _formValidation($t)
    {
        $r=array();

        //nb of rows
        $rowTotal=count($t['structure']);

        for ($rowNumber=1;$rowNumber<=$rowTotal;$rowNumber++) {
            //row by row
            if (isset($t['structure']['row'.$rowNumber])) {
                $this->_formValidationRow($t['structure']['row'.$rowNumber], $rowNumber, $r);
            }
        }

        //implode final validation rules
        if (!empty($r['validation'])) {
            foreach ($r['validation'] as $k=>$v) {
                $r['validation'][$k]=implode('|', array_unique($v));
            }
        }

        //stock validation rules
        $this->_validationrules=$r;

        //check and validate datas
        $gump=new GUMP('fr');
        $flatPOST = $gump->sanitize($this->_postdatas);

        if (!empty($r['validation'])) {
            $gump->validation_rules($r['validation']);
        }
        if (!empty($r['filter'])) {
            $gump->filter_rules($r['filter']);
        }
        if (!empty($r['correspondances'])) {
            $gump->set_field_names($r['correspondances']);
        }

        $validated_data = $gump->run($flatPOST);

        if ($validated_data === false) {
            $errors=$gump->get_errors_array();
            $_SESSION['form'][$this->_formIN]['validationErrors']=array();
            $_SESSION['form'][$this->_formIN]['validationErrorsMsg']=array();
            foreach ($errors as $k=>$v) {
                $correctName=str_replace(' ', '_', $k);
                if (!in_array($correctName, $_SESSION['form'][$this->_formIN]['validationErrors'])) {
                    $_SESSION['form'][$this->_formIN]['validationErrors'][]=$correctName;

                    if (isset($r['errormsg'][$correctName]) and $this->_contextualValidationErrorsMsg) {
                      $_SESSION['form'][$this->_formIN]['validationErrorsMsg'][]=$r['errormsg'][$correctName];
                    } else {
                      $_SESSION['form'][$this->_formIN]['validationErrorsMsg'][]=$v;
                    }
                }
            }
            $_SESSION['form'][$this->_formIN]['validationErrorsMsg']=array_unique($_SESSION['form'][$this->_formIN]['validationErrorsMsg']);
            $this->savePostValues2Session();
            return false;
        } else {
            return true;
        }
    }

/**
 * Traiter un ligne du form pour en extraire les règles de validation
 * @param  array $rowTab    Le tableau de la ligne
 * @param  int $rowNumber Le numero de ligne
 * @param  array $r         Le tableau général final
 * @return void
 */
    private function _formValidationRow($rowTab, $rowNumber, &$r)
    {
        $col=count($rowTab);
        for ($colNumber=1;$colNumber<=$col;$colNumber++) {
            if (isset($rowTab['col'.$colNumber]['bloc'])) {
                $this->_formValidationBloc($rowTab['col'.$colNumber]['bloc'], $rowNumber, $colNumber, $r);
            }
        }
    }

/**
 * Traiter un bloc du form pour en extraire les règles de validation
 * @param  array $blocs      Array du bloc
 * @param  int $rowNumber Numéro de ligne
 * @param  int $colNumber Numéro de colonne
 * @param  array $r         Le tableau général final
 * @return void
 */
    private function _formValidationBloc($blocs, $rowNumber, $colNumber, &$r)
    {
        if (is_array($blocs)) {
            foreach ($blocs as $k=>$v) {
                $bloc=explode(',', $v);
                if (preg_match('#(template{|label{).*#i', $bloc[0])) {
                    continue;
                }
                if (is_numeric($bloc[0]) or preg_match('#[\w]+#i', $bloc[0])) {
                    if (is_numeric($bloc[0])) {
                        $type=$this->_formExtractType($bloc[0]);
                    } else {
                        $type=$this->_formExtractTypeByName($bloc[0]);
                    }

                    $type['internalName']=$type['name'];
                    if ($this->_typeForNameInForm !='byName') {
                        $type['name']='p_'.$type['name'];
                    }

                    //post name -> type name
                    $r['postname2typename'][$type['name']]=$type['internalName'];

                    //validation rules
                    if (in_array('required', $bloc)) {
                        $r['validation'][$type['name']][]='required';
                    }
                    if (!empty($type['validationRules'])) {
                        $r['validation'][$type['name']][]=$type['validationRules'];
                    }

                    // validation rules : complément si de type select
                    if ($type['formType']=="select") {

                        //forcage des <option>
                        if(isset($this->_optionsForSelect[$type['name']])) {
                            $type['formValues']=$this->_optionsForSelect[$type['name']];
                        }
                        // ou valeur par défaut du type
                        else {
                            $type['formValues']=Spyc::YAMLLoad($type['formValues']);
                        }
                        if (!empty($type['formValues'])) {
                            $r['validation'][$type['name']][]='contains_list,'.implode(';', array_keys($type['formValues']));
                        }
                    }

                    // ajout des règles contextuelles hard-codées de validation
                    if(isset($this->_contextualValidationRules[$type['internalName']])) {
                        foreach($this->_contextualValidationRules[$type['internalName']] as $rule) {
                          $r['validation'][$type['name']][]=$rule;
                        }
                    }

                    //validation filter
                    $r['filter'][$type['name']]='trim';

                    //validation error msg
                    if (!empty($type['validationErrorMsg'])) {
                        $r['errormsg'][$type['name']]=$type['validationErrorMsg'];
                    }

                    //correspondance name=>label
                    $r['correspondances'][$type['name']]=$type['label'];

                    //traitement des ajustements passés dans le formulaire lui même
                    foreach ($bloc as $h) {
                      if (preg_match('#^vr={(.*)}$#i', $h, $match)) {
                          // validation rules (on revient à la syntaxe correcte de gump avec le replace)
                          $r['validation'][$type['name']][]=str_replace(':', ',', $match[1]);
                      }
                    }

                }
            }
        }
    }

}
