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
    protected $_formID;
    /**
     * @var array Les datas envoyées en $_POST quand le questionnaire est rempli
     */
    protected $_postdatas;
    /**
    * @var int Le nom interne du questionnaire
    */
    protected $_formIN;

    /**
     * @var array Les valeur pré calculées à injecter dans le form comme valeurs défaut
     */
    protected $_prevalues;
    /**
     * @var array valeurs des <option> à injecter dans les selects du form en lieu de celles par défaut
     * array('typeName1'=>array('value1'=>'label1', 'value2'=>'label2' ...), ...)
     */
    protected $_optionsForSelect;
    /**
     * @var int Le numéro d'instance du questionnaire
     */
    protected $_instance=0;
    /**
     * @var string Le type de nomage des champs du formulaire (byID / byName)
     */
    protected $_typeForNameInForm='byID';
    /**
     * @var array log des row et col du form pour pouvoir mettre une preValue après coup
     */
    protected $_log;
    /**
     * @var array array PHP du formulaire construit
     */
    protected $_builtForm;
   /**
    * @var array tableau de type à extraire dans la recherche de prevalues
    */
    protected $_typesSupForPrevaluesExtraction=[];

    protected $_cdaData=[];

    protected $_formYamlStructure;

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
        if(!is_string($formName)) {
          throw new Exception('FormName is not string');
        }
        if ($formID=msSQL::sqlUniqueChamp("select id from forms where internalName='".msSQL::cleanVar($formName)."' limit 1")) {
            $this->_formIN=$formName;
            return $this->_formID = $formID;
        } else {
            throw new Exception('Formulaire '.$formName.' non trouvé à partir de son nom');
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
 * @param array $table ($name=>$value) $table array des noms et valeurs à remplir
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
 * Définir les attributs d'un champ de formulaire après sa génération
 * @param array $form    formulaire
 * @param string $name    nom du champ
 * @param array $changes array attr=>value
 */
    public function setFieldAttrAfterwards(&$form, $name, $changes) {
      if(isset($form['structure'][$this->_log[$name][0]][$this->_log[$name][1]]['elements'][$this->_log[$name][2]])) {
        foreach($changes as $k=>$v) {
          $form['structure'][$this->_log[$name][0]][$this->_log[$name][1]]['elements'][$this->_log[$name][2]]['value'][$k]=$v;
        }
        return true;
      } else {
        return false;
      }
    }

/**
 * Retirer des options après génération du form dans un chmap select
 * @param  array $form     formulaire array php (post getForm())
 * @param  string $name     nom du data_type
 * @param  array  $toRemove array des option à retirer (name)
 * @return void
 */
    public function removeOptionInSelectForm(&$form, $name, $toRemove=[]) {
      foreach($toRemove as $optionToRemove) {
        unset($form['structure'][$this->_log[$name][0]][$this->_log[$name][1]]['elements'][$this->_log[$name][2]]['value']['formValues'][$optionToRemove]);
      }
    }

/**
 * Obtenir les options d'un select à partir du form généré en array php
 * @param  array $form formulaire post getForm()
 * @param  string $name nom du data_type
 * @return array       array value => label
 */
    public function getOptionInSelectForm($form, $name) {
      return $form['structure'][$this->_log[$name][0]][$this->_log[$name][1]]['elements'][$this->_log[$name][2]]['value']['formValues'];
    }


/**
 * Retirer un champ du formulaire après sa création
 * @param  array $form formulaire
 * @param  string $name nom du champ à retirer
 * @return boolean true si valeur existe / false sinon
 */
    public function removeFieldFromForm(&$form, $name) {
      if(isset($form['structure'][$this->_log[$name][0]][$this->_log[$name][1]]['elements'][$this->_log[$name][2]])) {
        unset($form['structure'][$this->_log[$name][0]][$this->_log[$name][1]]['elements'][$this->_log[$name][2]]);
        return true;
      } else {
        return false;
      }
    }

/**
 * Retirer des attributs d'un champ de formulaire après sa génération
 * @param array $form    formulaire
 * @param string $name    nom du champ
 * @param array $attr array attr
 */
    public function removeFieldAttrAfterwards(&$form, $name, $attr) {
      if(isset($form['structure'][$this->_log[$name][0]][$this->_log[$name][1]]['elements'][$this->_log[$name][2]])) {
        foreach($attr as $v) {
          unset($form['structure'][$this->_log[$name][0]][$this->_log[$name][1]]['elements'][$this->_log[$name][2]]['value'][$v]);
        }
        return true;
      } else {
        return false;
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
 * Définir des nom de data type dont il faut extraire les prevalues en plus des prevalues du form
 * @param array $v array de name
 */
    public function setTypesSupForPrevaluesExtraction($v) {
      if (is_array($v)) {
          return $this->_typesSupForPrevaluesExtraction = $v;
      } elseif(!empty($v)) {
          throw new Exception('TypesSupForPrevaluesExtraction is not an array');
      }
    }

/**
 * Obtenir les valeurs de remplissage d'un formulaire pour un patient donné
 * @param  int $patientID ID du patient
 * @return array            Array de type typeID => $value
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

        if ($this->_prevalues = msSQL::sql2tabKey("select typeID, value from objets_data where typeID in ('".implode("','", $this->formExtractDistinctTypes())."') and toID='".$patientID."' and outdated='' and deleted='' ".$where, "typeID", "value")) {
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
        if (!is_numeric($id)) throw new Exception('ID is not numeric');
        return msSQL::sqlUniqueChamp("select name from forms_cat where id = '".$id."' ");
    }

/**
 * Obtenir les data types qui implémentent le formulaire
 * @return array dataTypesID
 */
    public function getFormDataTypesImplementation() {
      return msSQL::sql2tabSimple("select id from data_types where groupe='typecs' and formValues='".$this->_formIN."'");
    }

/**
 * Obtenir le formulaire sous forme d'array PHP qui sera décortiqué par une macro Twig
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
 * Obtenir les data brutes d'un formulaire
 * @param  array  $fields champs de la table à retourner
 * @return array         array champ=>value
 */
    public function getFormRawData($fields=['*']) {
      return msSQL::sqlUnique("select ".implode(', ', msSQL::cleanArray($fields))." from forms where id='".$this->_formID."' limit 1");
    }

/**
 * Obtenir un champ unique brut de la table forms via formIN
 * @param  string $formIN internalName
 * @param  string $field  champ
 * @return string         valeur du champ
 */
    public static function getFormUniqueRawField($formIN, $field) {
      return msSQL::sqlUniqueChamp("select ".msSQL::cleanVar($field)." from forms where internalName='".msSQL::cleanVar($formIN)."' limit 1");
    }

/**
 * Obtenir un champ unique brut de la table forms via formID
 * @param  string $formID id
 * @param  string $field  champ
 * @return string         valeur du champ
 */
    public static function getFormUniqueRawFieldByFormID($formID, $field) {
      if (!is_numeric($formID)) throw new Exception('FormID is not numeric');
      return msSQL::sqlUniqueChamp("select ".msSQL::cleanVar($field)." from forms where id='".$formID."' limit 1");
    }

/**
 * Extraire le code javascript accompagnant le formulaire
 * @return string code javascript
 */
    public function getFormJavascript()
    {
      if (!isset($this->_formID)) {
          throw new Exception('formID is not defined');
      }
      $jsFromBdd = $this->getFormRawData(['javascript'])['javascript'];
      $jsFromCda = $this->_getJsFromCdaRules();
      return $jsFromBdd."\n".$jsFromCda;
    }

/**
 * Extraire les options (yaml) du formulaire
 * @return array array
 */
    public function getFormOptions()
    {
      if (!isset($this->_formID)) {
          throw new Exception('formID is not defined');
      }
      if($options = $this->getFormRawData(['options'])['options']) {
        return yaml_parse($options);
      } else {
        return [];
      }

    }

/**
 * Obtenir les types déclarés comme NON exportables dans le formulaire
 * @return array tableau des data_types
 */
    public function getFormDataToNeverExport() {
      if($notExport = $this->getFormOptions()) {
        if(isset($notExport['optionsExport']['neverExportData']) and !empty($notExport['optionsExport']['neverExportData'])) {
          return $notExport['optionsExport']['neverExportData'];
        }
      }
      return [];

    }

/**
 * Obtenir les types déclarés comme à substituer par le peopleExportID anonyme
 * @return array tableau des data_types
 */
    public function getFormDataToSubstituteByPeopleExportId() {
      if($toSubstituteByPeopleExportId = $this->getFormOptions()) {
        if(isset($toSubstituteByPeopleExportId['optionsExport']['substituteByPeopleExportID']) and !empty($toSubstituteByPeopleExportId['optionsExport']['substituteByPeopleExportID'])) {
          return $toSubstituteByPeopleExportId['optionsExport']['substituteByPeopleExportID'];
        }
      }
      return [];
    }

/**
 * Ajouter des champs input hidden à un form généré
 * @param array $f    formulaire u format array php
 * @param array $data array input name=> input value
 */
    public static function addHiddenInput(&$f, $data) {
      if(!empty($data)) {
        foreach($data as $k=>$v) {
          $f['addHidden'][$k]=$v;
        }
      }
      return $f;
    }

/**
 * Ajouter un bouton submit au formulaire
 * @param array $f     Formulaire au format array PHP
 * @param string $class Class à attribuer au bouton submit ajouté
 */
    public function addSubmitToForm(&$f, $class='btn-primary', $label='Valider')
    {
        $f['structure'][][1]=array(
        'size'=>'col-12',
        'elements'=>array(
          '0'=>array(
            'type'=>'form',
            'value'=> array(
              'id' => '0',
              'name' => '0',
              'label' => $label,
              'formType' => 'submit',
              'class' => $class
            ),
          ),
        ),
      );
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
        if($this->_formIN == 'baseLogin') {
          $sql = "select * from forms where id='".$this->_formID."' limit 1";
        } else {
          $sql = "select yamlStructure, formMethod, formAction, cda from forms where id='".$this->_formID."' limit 1";
        }
        if ($formyaml=msSQL::sqlUnique($sql)) {

            if($this->_testNumericBloc($formyaml['yamlStructure'])) {
              $formyaml['yamlStructure']=$this->cleanForm($formyaml['yamlStructure']);
            }

            $this->_formYamlStructure=$formyaml['yamlStructure'];

            $form = Spyc::YAMLLoad($formyaml['yamlStructure']);
            $form['global']['formAction']=$formyaml['formAction'];
            $form['global']['formMethod']=$formyaml['formMethod'];
            if(!empty($formyaml['cda'])) {
              $this->_cdaData=$form['cda'] = Spyc::YAMLLoad($formyaml['cda']);
            } else {
              $this->_cdaData=$form['cda'] = NULL;
            }

            return $form;
        } else {
            throw new Exception('The form could not be retrieved from the database');
        }
    }



/**
 * Construire le formulaire pour le passer ensuite à macro Twig
 * @param  array $t Array PHP du formulaire
 * @return array    array PHP pour maco Twig
 */
    protected function _formBuilder($t)
    {
        global $p;
        $r=array();

        //on passe la config général pour la retrouver dans la macro Twig sous t.config
        $r['config']=$p['config'];

        //form tag
        if (isset($t['global'])) {
            $r['global']=$t['global'];
        }

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
                $this->_formBuilderRow($t['structure']['row'.$rowNumber], $rowNumber, $r);
            }
        }

        //ajouter un champ final avant validation pour coder l'examen pour version CDA si data CDA existent
        if(!empty($t['cda']['actesPossibles'])) $this->_formBuilderAddSelectForServiceEventCode($r,$t['cda']);

        return $r;
    }

/**
 * AJouter un champ pour le codage de l'examen validé par le form
 * @param  array $r   formulaire au format array PHP
 * @param  array $cda data CDA issue du formulaire
 * @return void
 */
    protected function _formBuilderAddSelectForServiceEventCode(&$r,$cda) {

      foreach($cda['actesPossibles'] as $code=>$v) {
        $formValues[$code]=$code.': '.$v['serviceEventCode']['displayName'];
      }

      //valeur par défaut si présente
      $typeID=msData::getTypeIDFromName('codeTechniqueExamen');
      if (isset($this->_prevalues[$typeID])) {
          $preValue=$this->_prevalues[$typeID];
      } elseif (isset($this->_prevalues['codeTechniqueExamen'])) {
          $preValue=$this->_prevalues['codeTechniqueExamen'];
      } else {
          $preValue='noPreValue';
      }


      $newRow = count($r['structure']);
      $r['structure'][$newRow][1]['size']='col-md-12';
      $r['structure'][$newRow][1]['elements'][]=array(
        'type'=>'form',
        'value'=>array(
          'name'=>'p_codeTechniqueExamen',
          'internalName'=>'codeTechniqueExamen',
          'formType'=>'select',
          'formValues'=>$formValues,
          'preValue'=>$preValue,
          'label'=>'Acte correspondant à l\'examen réalisé'
        )
      );
    }

/**
 * Construire le formulaire : analyser une ligne
 * @param  array $rowTab    Array de la ligne
 * @param  int $rowNumber Numéro de ligne
 * @param  array $r         Array final de résultat
 * @return void
 */
    protected function _formBuilderRow($rowTab, $rowNumber, &$r)
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

            //class colonne
            if (isset($rowTab['col'.$colNumber]['class'])) {
                $this->_formBuilderColClass($rowTab['col'.$colNumber]['class'], $rowNumber, $colNumber, $r);
            }

            //bloc
            if (isset($rowTab['col'.$colNumber]['bloc'])) {
                $this->_formBuilderBloc($rowTab['col'.$colNumber]['bloc'], $rowNumber, $colNumber, $r);
            }
        }
    }

/**
 * Construire le formulaire : traitement de chaque bloc
 * @param  array $blocs      Array du bloc
 * @param  int $rowNumber Numéro de la ligne
 * @param  int $colNumber Numéro de la colonne
 * @param  array $r         Array final de résultat
 * @return void
 */
    protected function _formBuilderBloc($blocs, $rowNumber, $colNumber, &$r)
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
                } else if (preg_match('#label{([^}]+)}(,class={(.*)})?#i', $v, $match)) {
                    if(!isset($match[3])) $match[3]='';
                    if(empty(trim($match[1]))) $match[1]='&nbsp;';
                    $r['structure'][$rowNumber][$colNumber]['elements'][]=array(
                            'type'=>'label',
                            'value'=>$match[1],
                            'class'=>$match[3],
                        );

                // sinon c'est un bloc standard (ID ou internalName)
                } else {
                    $bloc=explode(',', $v);
                    if (is_numeric($bloc[0])) {
                        $type=$this->_formExtractType($bloc[0]);
                    } else {
                        $type=$this->_formExtractTypeByName($bloc[0]);
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
                    } elseif (isset($this->_prevalues[$type['internalName']])) {
                        $type['preValue']=$this->_prevalues[$type['internalName']];
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
                    $type['classLabel']='';
                    foreach ($bloc as $h) {
                        if (preg_match('/^class=(?!{)(.+)/', $h, $match)) {
                            $type['class'].=' '.$match[1];
                        }
                        if (preg_match('#^class={(.*)}$#i', $h, $match)) {
                            $type['class'].=' '.$match[1];
                        }
                        if (preg_match('#^classLabel={(.*)}$#i', $h, $match)) {
                            $type['classLabel'].=' '.$match[1];
                        }
                        if (preg_match('#^plus={(.*)}#i', $h, $match)) {
                            $type['plus']=$match[1];
                        }
                        if (preg_match('#^plusg={(.*)}#i', $h, $match)) {
                            $type['plusg']=$match[1];
                        }
                        if (preg_match('#^label={(.+)}#i', $h, $match)) {
                            $type['label']=$match[1];
                        }
                        if (preg_match('#^helpTxt={(.+)}#i', $h, $match)) {
                            $type['helpTxt']=$match[1];
                        }
                        if (preg_match('#^tabindex=([0-9]+)#i', $h, $match)) {
                            $type['tabindex']=$match[1];
                        }
                        if (preg_match('#^maxlength=([0-9]+)#i', $h, $match)) {
                            $type['maxlength']=$match[1];
                        }
                    }

                    if(isset($_SESSION['form'][$this->_formIN]['validationErrors'])) {
                      if(in_array($type['name'], $_SESSION['form'][$this->_formIN]['validationErrors'])) {
                        $type['class'].=' is-invalid';
                      }
                    }

                    //traitement spécifique au select
                    if ($type['formType']=="select") {

                        //forcage des <option> du type
                        if(isset($this->_optionsForSelect[$type['internalName']])) {
                          $type['formValues']=$this->_optionsForSelect[$type['internalName']];
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

                    //traitement spécifique aux dates
                    } elseif ($type['formType']=="number" or $type['formType']=="date") {
                        foreach ($bloc as $h) {
                            if (preg_match('#max=([0-9\-]+)#i', $h, $match)) {
                                $type['max']=$match[1];
                            } elseif (preg_match('#min=([0-9\-]+)#i', $h, $match)) {
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
    protected function _traiterListeTypesAutocomplete($stringTypes) {
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
    protected function _formBuilderHeadRow($value, $rowNumber, $colNumber, &$r)
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
    protected function _formBuilderHead($value, $rowNumber, $colNumber, &$r)
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
    protected function _formBuilderColSize($value, $rowNumber, $colNumber, &$r)
    {
      if(is_numeric(trim($value)[0])) {
        $r['structure'][$rowNumber][$colNumber]['size']='col-md-'.$value;
      } else {
        $r['structure'][$rowNumber][$colNumber]['size']=$value;
      }
    }

/**
 * Construire le tableau: définir les class de colonne
 * @param  string $value     class
 * @param  int $rowNumber Numéro de ligne
 * @param  int $colNumber Numéro de colonne
 * @param  array $r         Tableau final de résultat
 * @return void
 */
    protected function _formBuilderColClass($value, $rowNumber, $colNumber, &$r)
    {
      if(is_string($value)) {
        $r['structure'][$rowNumber][$colNumber]['class']=$value;
      }
    }

/**
 * Extraire les infos sur un type de données
 * @param  int $id      ID du type
 * @return array          Infos sur le type
 */
    protected function _formExtractType($id)
    {
        if ($typeData=msSQL::sqlUnique("select id, name, label, validationRules, validationErrorMsg, formType, formValues, placeholder from data_types where id='".msSQL::cleanVar($id)."' limit 1")) {
            return $typeData;
        } else {
            throw new Exception('Le type de donnée '.$id.' n\'a pas pu être extrait de la base de données');
        }
    }

/**
 * Extraire les infos sur un type de données par son name
 * @param  string $name      name du type
 * @return array          Infos sur le type
 */
    protected function _formExtractTypeByName($name)
    {
        if ($typeData=msSQL::sqlUnique("select id, name, label, validationRules, validationErrorMsg, formType, formValues, placeholder from data_types where name='".msSQL::cleanVar($name)."' limit 1")) {
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

            //ajout des types sup
            if(!empty($this->_typesSupForPrevaluesExtraction)) $matchIN[1]=array_merge($matchIN[1],$this->_typesSupForPrevaluesExtraction);

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
 * Obtenir les paramètres d'utilisation d'un data type dans le form
 * @param  string $typeName data type name
 * @return array           array des paramètres
 */
    public function getDataTypeFormParams($typeName) {
      if(!isset($this->_formYamlStructure)) {
        $this->_formYamlStructure=$this->getFormRawData(['yamlStructure'])['yamlStructure'];
      }
      preg_match("# - (".$typeName.".*)\s*\#.*#i", $this->_formYamlStructure, $match);
      if(isset($match[1]) and !empty($match[1])) {
        $params = explode(',', $match[1]);
        unset($params[0]);
        if(!empty($params)) {
          foreach($params as $k=>$v) {
            if(strpos($v,'=')) {
              $pp=explode('=', $v);
              $params[$pp[0]]=trim($pp[1]);
            } else {
              $params[$v]=trim($v);
            }
            unset($params[$k]);
          }
        }
        return $params;
      }
      return [];
    }

/**
 * Obtenir les data type utilisés dans un formulaire qui ne doivent pas être sauvés vides
 * @return array array des types names
 */
    public function getDoNotSaveEmptyDataInForm() {
      if(!isset($this->_formYamlStructure)) {
        $this->_formYamlStructure=$this->getFormRawData(['yamlStructure'])['yamlStructure'];
      }
      preg_match_all("# - (\w+),.*donotsaveempty.*\s*\#.*#i", $this->_formYamlStructure, $match);
      return $match[1];
    }

/**
 * Tester la présence de blocs numériques dans un form
 * @param string $formyaml formulaire au formt yaml
 * @return bool true or false
 */
    protected function _testNumericBloc($formyaml)
    {
        preg_match_all("# - ([0-9]+)#i", $formyaml, $matches);
        if(count($matches[1]) > 0) return true; else return false;
    }

/**
 * Transformer les blocs numériques d'un formulaire en bloc name
 * @param string $formyaml formulaire au formt yaml
 * @return string formulaire nettoyé
 */
  public function cleanForm($formyaml)
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
                  $type=$this->_formExtractType($match[2]);
              } elseif(preg_match("#(\s+)- ([\w]+)(.*)#i", $ligne, $match)) {
                  $type=$this->_formExtractTypeByName($match[2]);
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
                if(isset($el['value']['label'])) $string.=$el['value']['label'].' : ';
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
                if(isset($el['value']['label'])) $string.=$el['value']['label'].' : ';
                $string.='<p>{{ tag.'.$el['value']['internalName']."|nl2br }}</p>\n";
              } else {
                if(isset($el['value']['label'])) $string.=$el['value']['label'].' : ';
                $string.='{{ tag.'.$el['value']['internalName']." }}<br>\n";
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

/**
 * Obtenir le code javascript de sélection automatique de l'acte effectué en fonction des règles CDA en yaml
 * @return string javascript
 */
  protected function _getJsFromCdaRules() {
    if(empty($this->_cdaData)) return;

    $d=$this->_cdaData['clinicalDocument']['documentationOf']['serviceEvent'];
    if(is_string($d['paramConditionServiceEvent'])) {
      $d['paramConditionServiceEvent']=array($d['paramConditionServiceEvent']);
    }

    $d['paramConditionServiceEvent']=msTools::array_values_multi($d['paramConditionServiceEvent']);
    $r='';
    if(is_array($d['paramConditionServiceEvent']) and !empty($d['code'])) {
      $r="$('#nouvelleCs').on('change','#id_".implode("_id, #id_", $d['paramConditionServiceEvent'])."_id', function() {\n";
      foreach($d['paramConditionServiceEvent'] as $champ) {
        $r.="val".$champ." = '".$champ."@' + $('#id_".$champ."_id').val();\n";
      }
      $i=0;
      foreach($d['code'] as $clef=>$v) {
        $vals=explode('|',$clef);
        $tabChamps=[];
        foreach($vals as $val) {
          if(strpos($val, '@')) {
            $tabChamps[]=explode('@',$val)[0];
          }
        }
        $clefc= "val".implode(" + '|' + val",   $tabChamps);

        if($i > 0) $r.=' else ';
        $r.= "if($clefc == '".$clef."') $('#id_codeTechniqueExamen_id option[value=\"".$v."\"]').prop('selected', true);\n";
        $i++;
      }
      $r.="});";
    }
    return $r;

  }
}
