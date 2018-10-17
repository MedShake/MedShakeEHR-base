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
 * Les questionnaires
 * - construction à partir du modèle yaml (macro Twig termine le travail ensuite)
 * - obtention des règles de validation à patir du modèle yaml
 * - obtention des valeurs pour un individu particulier
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

class msForm
{


    // Attributs
    /**
     * @var int Le numéro du questionnaire
     */
    private $_formID;
    /**
     * @var array Les datas envoyées en $_POST quand le questionnaire est rempli
     */
    private $_postdatas;
    /**
    * @var int Le nom interne du questionnaire
    */
    private $_formIN;
    /**
     * @var array Les règles de validation d'un questionnaire
     */
    private $_validationrules;
    /**
     * @var array Les valeur pré calculées à injecter dans le form comme valeurs défaut
     */
    private $_prevalues;
    /**
     * @var array valeurs des <option> à injecter dans les selects du form en lieu de celles par défaut
     * array('typeName1'=>array('value1'=>'label1', 'value2'=>'label2' ...), ...)
     */
    private $_optionsForSelect;
    /**
     * @var int Le numéro d'instance du questionnaire
     */
    private $_instance=0;
    /**
     * @var string Le type de nomage des champs du formulaire (byID / byName)
     */
    private $_typeForNameInForm='byID';
    /**
     * @var array log des row et col du form pour pouvoir mettre une preValue après coup
     */
    private $_log;
    /**
     * @var array array PHP du formulaire construit
     */
    private $_builtForm;

/**
 * Définir le numéro du formulaire
 * @param int $formID L'ID du formulaire
 */
    public function setFormID($formID)
    {
        if (is_numeric($formID)) {
            if (!isset($this->_formIN)) {
                if ($formIN=msSQL::sqlUniqueChamp("select internalName from forms where id='".msSQL::cleanVar($formID)."' limit 1")) {
                    $this->_formIN = $formIN;
                } else {
                    throw new Exception('Formulaire non trouvé à partir de son ID');
                }
            }
            return $this->_formID = $formID;
        } else {
            throw new Exception('formID is not numeric');
        }
    }
/**
 * Obtenir le formID
 * @return int formID
 */
    public function getFormID()
    {
        if (is_numeric($this->_formID)) {
            return $this->_formID;
        } else {
            throw new Exception('formID is not numeric');
        }
    }
/**
 * Obtenir le formIN
 * @return int formIN
 */
    public function getFormIN()
    {
        if (isset($this->_formIN)) {
            return $this->_formIN;
        } else {
            throw new Exception('formIN n\'est pas défini');
        }
    }
/**
 * Définir le numéro du formulaire à partir de son nom interne
 * @param int $formName Nom interne du formulaire
 */
    public function setFormIDbyName($formName)
    {
        if ($formID=msSQL::sqlUniqueChamp("select id from forms where internalName='".msSQL::cleanVar($formName)."' limit 1")) {
            $this->_formIN=$formName;
            return $this->_formID = $formID;
        } else {
            throw new Exception('Formulaire non trouvé à partir de son nom');
        }
    }

/**
 * Définir l'instance
 * @param int $instance l'ID de l'instance
 */
    public function setInstance($instance)
    {
        if (is_numeric($instance)) {
            return $this->_instance = $instance;
        } else {
            throw new Exception('Instance is not numeric');
        }
    }

/**
 * Définir typeForNameInForm
 * @param string $typeForNameInForm le type : byID / byName
 */
    public function setTypeForNameInForm($typeForNameInForm)
    {
        return $this->_typeForNameInForm = $typeForNameInForm;
    }

/**
 * Définir les datas envoyées en $_POST
 * @param array $v Datas envoyées en POST
 */
    public function setPostdatas($v)
    {
        if (is_array($v)) {
            return $this->_postdatas = $v;
        } else {
            throw new Exception('Var is not an array');
        }
    }
/**
 * Définir les valeurs de remplissage par défaut du formulaire
 * @param array $v Array des valeurs par défaut
 */
    public function setPrevalues($v)
    {
        if (is_array($v)) {
            return $this->_prevalues = $v;
        } elseif(!empty($v)) {
            throw new Exception('Var is not an array');
        }
    }
/**
 * Définir des valeurs de remplissage du formulaire après sa création
 * @param array $form formulaire
 * @param array($name=>$value) $table array des noms et valeurs à remplir
 */
    public function setPrevaluesAfterwards(&$form, $table)
    {
        foreach ($table as $name=>$value) {
            if (array_key_exists($name, $this->_log)) {
                $form['structure'][$this->_log[$name][0]][$this->_log[$name][1]]['elements'][$this->_log[$name][2]]['value']['preValue']=$value;
            }
        }
    }
/**
 * Remplacer les valeurs de remplissage des selects du form par défaut
 * @param array $v Array des valeurs array('typeName1'=>array('value1'=>'label1', 'value2'=>'label2' ...), ...)
 */
    public function setOptionsForSelect($v)
    {
        if (is_array($v)) {
            return $this->_optionsForSelect = $v;
        } elseif(!empty($v)) {
            throw new Exception('Var is not an array');
        }
    }
/**
 * Obtenir les valeurs de remplissage d'un formulaire pour un patient donné
 * @param  int $patientID ID du patient
 * @return array            Array de type tupeID => $value
 */
    public function getPrevaluesForPatient($patientID)
    {
        if (!is_numeric($patientID)) {
            throw new Exception('PatientID is not numeric');
        }

        if ($this->_instance > 0) {
            $where = " and instance='".$this->_instance."'";
        } else {
            $where=null;
        }

        if ($this->_prevalues = msSQL::sql2tabKey("select typeID, value from objets_data where typeID in ('".implode("','", $this->formExtractDistinctTypes())."') and toID='".$patientID."' and outdated='' ".$where, "typeID", "value")) {
            return $this->_prevalues;
        } else {
            return $this->_prevalues=array();
        }
    }

/**
 * Obtenir le name de la categorie du form à partir partir du cat id
 * @param  int $id de la catégorie
 * @return string     name
 */
    public static function getCatNameFromCatID($id)
    {
        return msSQL::sqlUniqueChamp("select name from forms_cat where id = '".$id."' ");
    }


/**
 * Obetnir le formulaire sous forme d'array PHP qui sera décotiqué par une macro Twig
 * pour obtenir au final une version HTML
 * @return array Array de description du formulaire
 */
    public function getForm()
    {
        if ($formYaml=$this->getFormFromDb($this->_formID)) {
            return $this->_builtForm = $this->_formBuilder($formYaml);
        } else {
            throw new Exception('Form cannot be generated');
        }
    }
/**
 * Ajouter un bouton submit au formulaire
 * @param array $f     Formulaire au format array PHP
 * @param string $class Class à attribuer au bouton submit ajouté
 */
    public function addSubmitToForm(&$f, $class='btn-primary')
    {
        $f['structure'][][1]=array(
        'size'=>'col-12',
        'elements'=>array(
          '0'=>array(
            'type'=>'form',
            'value'=> array(
              'id' => '0',
              'name' => '0',
              'label' => 'Valider',
              'formType' => 'submit',
              'class' => $class
            ),
          ),
        ),
      );
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
 * Enregistrer les valeurs envoyées en POST en session
 * @return void
 */
    public function savePostValues2Session()
    {
        if (!isset($this->_formIN)) {
            throw new Exception('formIN is not defined');
        }
        $_SESSION['form'][$this->_formIN]['formValues']=$this->_postdatas;
    }

/**
 * Obtenir le modèle de formulaire stocké en base
 * le modèle yaml est transformé en array php
 * @return array Le modèle du formulaire en array PHP brut
 */
    public function getFormFromDb()
    {
        if (!isset($this->_formID)) {
            throw new Exception('formID is not defined');
        }
        if ($formyaml=msSQL::sqlUnique("select yamlStructure, dataset, formMethod, formAction from forms where id='".$this->_formID."' limit 1")) {

            if($this->_testNumericBloc($formyaml['yamlStructure'])) {
              $formyaml['yamlStructure']=$this->cleanForm($formyaml['yamlStructure'],$formyaml['dataset']);
            }

            $form = Spyc::YAMLLoad($formyaml['yamlStructure']);
            $form['global']['dataset']=$formyaml['dataset'];
            $form['global']['formAction']=$formyaml['formAction'];
            $form['global']['formMethod']=$formyaml['formMethod'];

            return $form;
        } else {
            throw new Exception('The form could not be retrieved from the database');
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

        //dataset
        $dataset=$t['global']['dataset'];

        for ($rowNumber=1;$rowNumber<=$rowTotal;$rowNumber++) {
            //row by row
            if (isset($t['structure']['row'.$rowNumber])) {
                $this->_formValidationRow($t['structure']['row'.$rowNumber], $rowNumber, $r, $dataset);
            }
        }

        //implode final validation rules
        if (!empty($r['validation'])) {
            foreach ($r['validation'] as $k=>$v) {
                $r['validation'][$k]=implode('|', $v);
            }
        }

        //stock validation rules
        $this->_validationrules=$r;

        //check and validate datas
        $gump=new GUMP();
        $flatPOST = $gump->sanitize($this->_postdatas);

        if (!empty($r['validation'])) {
            $gump->validation_rules($r['validation']);
        }
        if (!empty($r['filter'])) {
            $gump->filter_rules($r['filter']);
        }

        $validated_data = $gump->run($flatPOST);

        if ($validated_data === false) {
            $errors=$gump->get_errors_array();
            $_SESSION['form'][$this->_formIN]['validationErrors']=array();
            $_SESSION['form'][$this->_formIN]['validationErrorsMsg']=array();
            foreach ($errors as $k=>$v) {
                $correctName=str_replace(' ', '_', strtolower($k));
                if (!in_array($correctName, $_SESSION['form'][$this->_formIN]['validationErrors'])) {
                    $_SESSION['form'][$this->_formIN]['validationErrors'][]=$correctName;

                    if (!in_array($r['errormsg'][$correctName], $_SESSION['form'][$this->_formIN]['validationErrorsMsg'])) {
                        $_SESSION['form'][$this->_formIN]['validationErrorsMsg'][]=$r['errormsg'][$correctName];
                    }
                }
                $this->savePostValues2Session();
            }
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
 * @param  string $dataset   Le jeu de data impliqué dans le form
 * @return void
 */
    private function _formValidationRow($rowTab, $rowNumber, &$r, $dataset)
    {
        $col=count($rowTab);
        for ($colNumber=1;$colNumber<=$col;$colNumber++) {
            if (isset($rowTab['col'.$colNumber]['bloc'])) {
                $this->_formValidationBloc($rowTab['col'.$colNumber]['bloc'], $rowNumber, $colNumber, $r, $dataset);
            }
        }
    }

/**
 * Traiter un bloc du form pour en extraire les règles de validation
 * @param  array $blocs      Array du bloc
 * @param  int $rowNumber Numéro de ligne
 * @param  int $colNumber Numéro de colonne
 * @param  array $r         Le tableau général final
 * @param  string $dataset   Le jeu de données impliqué dans le form
 * @return void
 */
    private function _formValidationBloc($blocs, $rowNumber, $colNumber, &$r, $dataset)
    {
        if (is_array($blocs)) {
            foreach ($blocs as $k=>$v) {
                $bloc=explode(',', $v);
                if (preg_match('#(template{|label{).*#i', $bloc[0])) {
                    continue;
                }
                if (is_numeric($bloc[0]) or preg_match('#[\w]+#i', $bloc[0])) {
                    if (is_numeric($bloc[0])) {
                        $type=$this->_formExtractType($bloc[0], $dataset);
                    } else {
                        $type=$this->_formExtractTypeByName($bloc[0], $dataset);
                    }

                    //$type['originalname']=$type['name'];
                    //$type['name']='p_'.$type['id'];

                    $type['internalName']=$type['name'];
                    if ($this->_typeForNameInForm !='byName') {
                        $type['name']='p_'.$type['name'];
                    }

                    //post name -> type name
                    $r['postname2typename'][$type['name']]=$type['internalName'];


                    // si de type select
                    if ($type['formType']=="select") {

                        //validation
                        if (!empty($type['validationRules'])) {
                            $r['validation'][$type['name']][]=$type['validationRules'];
                        }
                        if (in_array('required', $bloc)) {
                            $r['validation'][$type['name']][]='required';
                        }
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
                        //filter
                        $r['filter'][$type['name']]='trim';
                        //error msg
                        if (!empty($type['validationErrorMsg'])) {
                            $r['errormsg'][$type['name']]=$type['validationErrorMsg'];
                        }
                    }

                    // si le reste
                    else {
                        //validation
                        if (!empty($type['validationRules'])) {
                            $r['validation'][$type['name']][]=$type['validationRules'];
                        }
                        if (in_array('required', $bloc)) {
                            $r['validation'][$type['name']][]='required';
                        }
                        //filter
                        $r['filter'][$type['name']]='trim';
                        //error msg
                        if (!empty($type['validationErrorMsg'])) {
                            $r['errormsg'][$type['name']]=$type['validationErrorMsg'];
                        }
                    }
                }
            }
        }
    }

/**
 * Construire le formulaire pour le passer ensuite à macro Twig
 * @param  array $t Array PHP du formulaire
 * @return array    array PHP pour maco Twig
 */
    private function _formBuilder($t)
    {
        global $p;
        $r=array();

        //on passe la config général pour la retrouver dans la macro Twig sous t.config
        $r['config']=$p['config'];

        //form tag
        if (isset($t['global'])) {
            $r['global']=$t['global'];
        }

        //dataset
        $dataset=$t['global']['dataset'];

        //instance
        $r['global']['instance']=$this->_instance;


        // structure
        // on sort si pas de structure (on est dans un formulaire affichage)
        if(!isset($t['structure'])) return;

        $rowTotal=count($t['structure']);

        for ($rowNumber=1;$rowNumber<=$rowTotal;$rowNumber++) {

            //header row
            if (isset($t['structure']['row'.$rowNumber]['head'])) {
                $this->_formBuilderHeadRow($t['structure']['row'.$rowNumber]['head'], $rowNumber, '0', $r);
            }

            //row class
            if (isset($t['structure']['row'.$rowNumber]['class'])) {
                $r['structure'][$rowNumber]['elements']['class']=$t['structure']['row'.$rowNumber]['class'];
            }


            //on passe au colonne
            if (isset($t['structure']['row'.$rowNumber])) {
                $this->_formBuilderRow($t['structure']['row'.$rowNumber], $rowNumber, $r, $dataset);
            }
        }
        return $r;
    }

/**
 * Construire le formualire : analyser une ligne
 * @param  array $rowTab    Array de la ligne
 * @param  int $rowNumber Numéro de ligne
 * @param  array $r         Array final de résultat
 * @param  string $dataset   Jeu de donnéés concerné par le form
 * @return void
 */
    private function _formBuilderRow($rowTab, $rowNumber, &$r, $dataset)
    {
        $col=count($rowTab);

        for ($colNumber=1;$colNumber<=$col;$colNumber++) {

            //header col
            if (isset($rowTab['col'.$colNumber]['head'])) {
                $this->_formBuilderHead($rowTab['col'.$colNumber]['head'], $rowNumber, $colNumber, $r);
            }

            //size colonne
            if (isset($rowTab['col'.$colNumber]['size'])) {
                $this->_formBuilderColSize($rowTab['col'.$colNumber]['size'], $rowNumber, $colNumber, $r);
            }

            //bloc
            if (isset($rowTab['col'.$colNumber]['bloc'])) {
                $this->_formBuilderBloc($rowTab['col'.$colNumber]['bloc'], $rowNumber, $colNumber, $r, $dataset);
            }
        }
    }

/**
 * Construire le formulaire : traitement de chaque bloc
 * @param  array $blocs      Array du bloc
 * @param  int $rowNumber Numéro de la ligne
 * @param  int $colNumber Numéro de la colonne
 * @param  array $r         Array final de résultat
 * @param  string $dataset   Jeu de données concerné par le form
 * @return void
 */
    private function _formBuilderBloc($blocs, $rowNumber, $colNumber, &$r, $dataset)
    {
        if (is_array($blocs)) {
            if(!isset($r['structure'][$rowNumber][$colNumber]['elements'])) $r['structure'][$rowNumber][$colNumber]['elements']=array();
            foreach ($blocs as $k=>$v) {

                //template
                if (preg_match('#template{([\w-]+)}#i', $v, $match)) {
                    $r['structure'][$rowNumber][$colNumber]['elements'][]=array(
                            'type'=>'template',
                            'value'=>$match[1]
                        );
                //label
                } else if (preg_match('#label{(.*)}#i', $v, $match)) {
                    $r['structure'][$rowNumber][$colNumber]['elements'][]=array(
                            'type'=>'label',
                            'value'=>$match[1]
                        );
                // sinon c'est un bloc standard (ID ou internalName)
                } else {
                    $bloc=explode(',', $v);
                    if (is_numeric($bloc[0])) {
                        $type=$this->_formExtractType($bloc[0], $dataset);
                    } else {
                        $type=$this->_formExtractTypeByName($bloc[0], $dataset);
                    }

                    $type['internalName']=$type['name'];
                    if ($this->_typeForNameInForm !='byName') {
                        $type['name']='p_'.$type['name'];
                    }

                    //valeur par défaut si présente
                    if (isset($this->_prevalues[$type['id']])) {
                        $type['preValue']=$this->_prevalues[$type['id']];
                    } elseif (isset($this->_prevalues[$type['name']])) {
                        $type['preValue']=$this->_prevalues[$type['name']];
                    } else {
                        $type['preValue']='noPreValue';
                    }

                    //traitement des flags communs
                    if (in_array('nolabel', $bloc)) {
                        unset($type['label']);
                    }
                      if (in_array('disabled', $bloc)) {
                          $type['disabled']='disabled';
                      }
                      if (in_array('readonly', $bloc)) {
                          $type['readonly']='readonly';
                      }
                      if (in_array('required', $bloc)) {
                          $type['required']='required';
                      }
                      $type['class']='';
                      foreach ($bloc as $h) {
                          if (preg_match('/^class=(.+)/', $h, $match)) {
                              $type['class'].=' '.$match[1];
                          }
                      }
                    //traitement spécifique au select
                    if ($type['formType']=="select") {

                        //forcage des <option> du type
                        if(isset($this->_optionsForSelect[$type['name']])) {
                          $type['formValues']=$this->_optionsForSelect[$type['name']];
                        }
                        // sinon valeur du type
                        else {
                          $type['formValues']=Spyc::YAMLLoad($type['formValues']);
                        }

                    //traitement spécifique au radio
                    } elseif ($type['formType']=="radio") {
                      $type['formValues']=Spyc::YAMLLoad($type['formValues']);

                    //traitement spécifique au textarea
                    } elseif ($type['formType']=="textarea") {
                        foreach ($bloc as $h) {
                            if (preg_match('#rows=([0-9]+)#i', $h, $match)) {
                                $type['rows']=$match[1];
                            }
                        }

                    //traitement spécifique au submit
                    } elseif ($type['formType']=="submit") {
                        if (isset($bloc[1])) {
                            $type['label']=$bloc[1];
                        } else {
                            $type['label']="Go";
                        }
                    //traitement spécifique au number
                    } elseif ($type['formType']=="number") {
                        foreach ($bloc as $h) {
                            if (preg_match('#max=([0-9]+)#i', $h, $match)) {
                                $type['max']=$match[1];
                            } elseif (preg_match('#min=([0-9]+)#i', $h, $match)) {
                                $type['min']=$match[1];
                            } elseif (preg_match('#step=([0-9]+)#i', $h, $match)) {
                                $type['step']=$match[1];
                            }
                        }

                    //traitement spécifique aux autres input
                    } else {
                        if (in_array('autocomplete', $bloc)) {
                            $type['autocompleteclass']=' jqautocomplete';

                            foreach ($bloc as $h) {
                                if (preg_match('#data-acTypeID=(([0-9a-z]+:{0,1})+)#i', $h, $matchOut)) {
                                    $type['dataAcTypeID']='data-acTypeID="'.$this->_traiterListeTypesAutocomplete($matchOut[1]).'"';
                                }
                            }
                            if (!isset($type['dataAcTypeID'])) {
                                $type['dataAcTypeID']='data-acTypeID='.$type['id'];
                            }
                        }

                        foreach ($bloc as $h) {
                            if (preg_match('#plus={(.*)}#i', $h, $match)) {
                                $type['plus']=$match[1];
                            }
                            if (preg_match('#plusg={(.*)}#i', $h, $match)) {
                                $type['plusg']=$match[1];
                            }
                        }

                    }
                    $idx=array_push($r['structure'][$rowNumber][$colNumber]['elements'], array('type'=>'form', 'value'=>$type));
                    $this->_log[$type['internalName']]=[$rowNumber, $colNumber, $idx-1];
                }
            }
        }
    }

/**
 * Traiter les types passés en paramètre pour un autocomplete étendu aux valeurs d'autres types
 * @param  string $stringTypes typeID ou typeName séparés par :
 * @return string              typeID séparés par :
 */
    private function _traiterListeTypesAutocomplete($stringTypes) {
      $finalTab=[];
      if(!empty($stringTypes)) {
        $typesTab=explode(':', $stringTypes);
        foreach($typesTab as $type) {
          if(is_numeric($type)) {
            $finalTab[]=$type;
          } elseif (is_string($type)) {
            if($convert = msData::getTypeIDFromName($type)) $finalTab[]=$convert;
          }
        }
      }
      return implode(':', $finalTab);
    }

/**
 * Construire le formulaire: traitement des en-tête de ligne
 * @param  string $value     Le nom de ligne à afficher
 * @param  int $rowNumber Numéro de ligne
 * @param  int $colNumber Numéro de colonne
 * @param  array $r         Tableau final de résultat
 * @return void
 */
    private function _formBuilderHeadRow($value, $rowNumber, $colNumber, &$r)
    {
        if (empty(trim($value))) {
            $value='&nbsp;';
        }
        $r['structure'][$rowNumber]['elements']=array(
            'type'=>'head',
            'value'=>$value
        );
    }

/**
 * Construire le formulaire: traitement des en-tête de colonne
 * @param  string $value     Le nom de colonne à afficher
 * @param  int $rowNumber Numéro de ligne
 * @param  int $colNumber Numéro de colonne
 * @param  array $r         Tableau final de résultat
 * @return void
 */
    private function _formBuilderHead($value, $rowNumber, $colNumber, &$r)
    {
        if (empty(trim($value))) {
            $value='&nbsp;';
        }
        $r['structure'][$rowNumber][$colNumber]['elements'][]=array(
            'type'=>'head',
            'value'=>$value
        );
    }

/**
 * Construire le tableau: définir la largeur de colonne
 * @param  string $value     largeur exprimée avec un int ou class bootstrap
 * @param  int $rowNumber Numéro de ligne
 * @param  int $colNumber Numéro de colonne
 * @param  array $r         Tableau final de résultat
 * @return void
 */
    private function _formBuilderColSize($value, $rowNumber, $colNumber, &$r)
    {
      if(is_numeric(trim($value){0})) {
        $r['structure'][$rowNumber][$colNumber]['size']='col-md-'.$value;
      } else {
        $r['structure'][$rowNumber][$colNumber]['size']=$value;
      }
    }


/**
 * Extraire les infos sur un type de données
 * @param  int $id      ID du type
 * @param  string $dataset Jeu de données
 * @return array          Infos sur le type
 */
    private function _formExtractType($id, $dataset)
    {
        if ($typeData=msSQL::sqlUnique("select id, name, label, validationRules, validationErrorMsg, formType, formValues, placeholder from ".$dataset." where id='".msSQL::cleanVar($id)."' limit 1")) {
            return $typeData;
        } else {
            throw new Exception('Le type de donnée '.$id.' n\'a pas pu être extrait de la base de données');
        }
    }

  /**
   * Extraire les infos sur un type de données par son name
   * @param  string $name      name du type
   * @param  string $dataset Jeu de données
   * @return array          Infos sur le type
   */
      private function _formExtractTypeByName($name, $dataset)
      {
          if ($typeData=msSQL::sqlUnique("select id, name, label, validationRules, validationErrorMsg, formType, formValues, placeholder from ".$dataset." where name='".msSQL::cleanVar($name)."' limit 1")) {
              return $typeData;
          } else {
              throw new Exception('Le type de donnée n\'a pas pu être extrait de la base de données par son name : '.$name);
          }
      }

/**
 * Extraire tous les typeID présents dans un form
 * Brutal mais ça fonctionne ;-)
 * @return array Array de tous les typeID présents.
 */
    public function formExtractDistinctTypes()
    {
        if ($formyaml=msSQL::sqlUniqueChamp("select yamlStructure from forms where id='".$this->_formID."' limit 1")) {

            $rtypes=[];
            preg_match_all("# - (?!template|label)([\w]+)#i", $formyaml, $matchIN);
            if(count($matchIN[1])>0) {
              $types=new msData();
              if($types=$types->getTypeIDsFromName($matchIN[1])) $rtypes=$types;
            }

            preg_match_all("# - ([0-9]+)#i", $formyaml, $matches);
            if(count($matches[1])>0) {
              $rtypes=array_merge($rtypes, $matches[1]);
            }

            return array_unique($rtypes);
        }
    }

/**
 * Tester la présence de blocs numériques dans un form
 * @param string $formyaml formulaire au formt yaml
 * @return bool true or false
 */
    private function _testNumericBloc($formyaml)
    {
        preg_match_all("# - ([0-9]+)#i", $formyaml, $matches);
        if(count($matches[1]) > 0) return true; else return false;
    }

/**
 * Transformer les blocs numériques d'un formulaire en bloc name
 * @param string $formyaml formulaire au formt yaml
 * @param string $dataset dataset du formulaire
 * @return string formulaire nettoyé
 */
  public function cleanForm($formyaml, $dataset)
  {
      if($form=explode("\n", $formyaml)) {
        foreach ($form as $ligne) {
            $ligneOriginale=$ligne;
            if(preg_match("#(\s+)- (template\{|')(.*)#i", $ligne, $match) ) {
              $ligne=explode('#', rtrim($ligne));
              $ligne[0]=str_pad(rtrim($ligne[0]),50)."\t\t";
              $cleanform[]=implode('# ',$ligne);
            } elseif(preg_match("#(\s+)- (label\{|')(.*)#i", $ligne, $match) ) {
              $ligne=explode('#', rtrim($ligne));
              $ligne[0]=str_pad(rtrim($ligne[0]),50)."\t\t";
              $cleanform[]=implode('# ',$ligne);
            } elseif (preg_match("#(\s+)- ([\w]+)(.*)#i", $ligne, $match)) {
              $ligne=explode('#', rtrim($ligne));
              $ligne=$ligne[0];

              if (preg_match("#(\s+)- ([0-9]+)(.*)#i", $ligne, $match)) {
                  $type=$this->_formExtractType($match[2], $dataset);
              } elseif(preg_match("#(\s+)- ([\w]+)(.*)#i", $ligne, $match)) {
                  $type=$this->_formExtractTypeByName($match[2], $dataset);
              }

              $cleanform[]=str_pad($match[1].'- '.$type['name'].trim($match[3]),50)." \t\t#".str_pad($type['id'],4).' '.str_replace("'", " ", $type['label']);

            } else {
                $cleanform[]=$ligneOriginale;
            }

        }
        return implode("\n", $cleanform);
    } else {
      return $formyaml;
    }
  }

/**
 * Obtenir une version basique du template d'impression du form
 * @return string html/twig
 */
  public function getFlatBasicTemplateCode() {
    if(!isset($this->_builtForm)) throw new Exception('Form is not yet built');
    $string='';
    foreach($this->_builtForm['structure'] as $ligneID=>$ligne) {
      foreach($ligne as $colID=>$element) {
        if(isset($element['type'])) {
          if($element['type'] == 'head') {
            $string.='<h2>'.$element['value']."</h2>\n";
          }
        }
        foreach($element as $typeID=>$type) {
          if(!is_array($type)) continue;
          foreach($type as $ID=>$el) {
            if(isset($el['type']) and $el['type'] == 'form') {
              if($el['value']['formType'] == 'radio' or $el['value']['formType'] == 'select') {
                $string.=$el['value']['label'].' : ';
                $i=0;
                foreach($el['value']['formValues'] as $repId=>$rep) {
                  if($i==0) {
                    $string.='{% if tag.val_'.$el['value']['internalName'].' == "'.$repId.'" %}'.$rep;
                  } else {
                    $string.='{% elseif tag.val_'.$el['value']['internalName'].' == "'.$repId.'" %}'.$rep;
                  }
                  $i++;
                }
                $string.="{% else %}- non renseigné -{% endif %}<br>\n";
              } elseif($el['value']['formType'] == 'textarea' ) {
                $string.=$el['value']['label'].' :<p>{{ tag.'.$el['value']['internalName']."|nl2br }}</p>\n";
              } else {
              if(!isset($el['value']['label'])) $el['value']['label']='';
                $string.=$el['value']['label'].' : {{ tag.'.$el['value']['internalName']." }}<br>\n";
              }
            } elseif($el['type'] == 'head' and !empty(trim(str_replace('&nbsp;','',$el['value'])))) {
              $string.="\n<h3>".$el['value']."</h3>\n";
            }
          }
        }
        }

    }
    return $string;
  }
}
