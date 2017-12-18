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
     * @var int Le numéro d'instance du questionnaire
     */
    private $_instance=0;
    /**
     * @var array Le tableau de tous les typeID distincts présent dans le form
     */
    private $_typesInForm=[];
    /**
     * @var string Le type de nomage des champs du formulaire (byID / byName)
     */
    private $_typeForNameInForm='byID';


/**
 * Définir le numéro du formulaire
 * @param int $formID L'ID du formulaire
 */
    public function setFormID($formID)
    {
        if (is_numeric($formID)) {
            if (!isset($this->_formIN)) {
                if ($formIN=msSQL::sqlUniqueChamp("select internalName from forms where id='".msSQL::cleanVar($formID)."' limit 1")) {
                    return $this->_formIN = $formIN;
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
 * @param array $v Array des valeur par défaut
 */
    public function setPrevalues($v)
    {
        if (is_array($v)) {
            return $this->_prevalues = $v;
        } else {
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

        if ($this->_prevalues = msSQL::sql2tabKey("select typeID, value from objets_data where typeID in ('".implode("','", $this->_formExtractDistinctTypes())."') and toID='".$patientID."' and outdated='' ".$where, "typeID", "value")) {
            return $this->_prevalues;
        } else {
            return $this->_prevalues=array();
        }
    }
/**
 * Obetnir le formulaire sous forme d'array PHP qui sera décotiqué par une macro Twig
 * pour obtenir au final une version HTML
 * @return array Array de description du formulaire
 */
    public function getForm()
    {
        if ($formYaml=$this->_getFormFromDb($this->_formID)) {
            return $this->_formBuilder($formYaml);
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
        'size'=>12,
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
        if ($formYaml=$this->_getFormFromDb($this->_formID)) {
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
    private function _getFormFromDb()
    {
        if ($formyaml=msSQL::sqlUnique("select yamlStructure, dataset, formMethod, formAction from forms where id='".$this->_formID."' limit 1")) {
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
                if (is_numeric($bloc[0]) and $type=$this->_formExtractType($bloc[0], $dataset)) {
                    $type['originalname']=$type['name'];
                    $type['name']='p_'.$type['id'];

                    //post name -> type name
                    $r['postname2typename'][$type['name']]=$type['originalname'];


                    // si de type select
                    if ($type['formType']=="select") {

                        //validation
                        if (!empty($type['validationRules'])) {
                            $r['validation'][$type['name']][]=$type['validationRules'];
                        }
                        if (in_array('required', $bloc)) {
                            $r['validation'][$type['name']][]='required';
                        }
                        $type['formValues']=Spyc::YAMLLoad($type['formValues']);
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
            foreach ($blocs as $k=>$v) {
                $bloc=explode(',', $v);

                if (!is_numeric($bloc[0])) {
                    $bloc=implode(',', $bloc);

                    //template
                    if ((preg_match('#template{([a-zA-Z]+)}#i', $bloc, $match))) {
                        $r['structure'][$rowNumber][$colNumber]['elements'][]=array(
                                'type'=>'template',
                                'value'=>$match[1]
                            );
                    }
                    //labels
                    else {
                        if (empty(trim($bloc))) {
                            $bloc='&nbsp;';
                        }
                        $r['structure'][$rowNumber][$colNumber]['elements'][]=array(
                                                  'type'=>'label',
                                                  'value'=>$bloc
                                              );
                    }

                } elseif ($type=$this->_formExtractType($bloc[0], $dataset)) {
                    if ($this->_typeForNameInForm=='byName') {
                    } else {
                        $type['internalName']=$type['name'];
                        $type['name']='p_'.$type['id'];
                    }

                    //valeur par défaut si présente
                    if (isset($this->_prevalues[$bloc[0]])) {
                        $type['preValue']=$this->_prevalues[$bloc[0]];
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

                    //traitement spécifique au select
                    if ($type['formType']=="select") {
                        $type['formValues']=Spyc::YAMLLoad($type['formValues']);
                        $r['structure'][$rowNumber][$colNumber]['elements'][]=array('type'=>'form', 'value'=>$type);

                    //traitement spécifique au textarea
                    } elseif ($type['formType']=="textarea") {
                        foreach ($bloc as $h) {
                            if (preg_match('#rows=([0-9]+)#i', $h, $match)) {
                                $type['rows']=$match[1];
                            }
                        }
                        $r['structure'][$rowNumber][$colNumber]['elements'][]=array('type'=>'form', 'value'=>$type);

                    //traitement spécifique au submit
                    } elseif ($type['formType']=="submit") {
                        if (isset($bloc[1])) {
                            $type['label']=$bloc[1];
                        } else {
                            $type['label']="Go";
                        }
                        $r['structure'][$rowNumber][$colNumber]['elements'][]=array('type'=>'form', 'value'=>$type);

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

                        $r['structure'][$rowNumber][$colNumber]['elements'][]=array('type'=>'form', 'value'=>$type);

                    //traitement spécifique aux autres input
                    } else {
                        if (in_array('autocomplete', $bloc)) {
                            $type['autocompleteclass']=' jqautocomplete';

                            foreach ($bloc as $h) {
                                if (preg_match('#data-acTypeID=([0-9]+:{0,1})+#i', $h)) {
                                    $type['dataAcTypeID']=$h;
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

                        $r['structure'][$rowNumber][$colNumber]['elements'][]=array('type'=>'form', 'value'=>$type);
                    }
                }
            }
        }
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
 * @param  int $value     Largeur
 * @param  int $rowNumber Numéro de ligne
 * @param  int $colNumber Numéro de colonne
 * @param  array $r         Tableau final de résultat
 * @return void
 */
    private function _formBuilderColSize($value, $rowNumber, $colNumber, &$r)
    {
        $r['structure'][$rowNumber][$colNumber]['size']=$value;
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
            //stockage des data_types présents dans le form
            if (!in_array($id, $this->_typesInForm)) {
                $this->_typesInForm[]=$id;
            }

            return $typeData;
        } else {
            throw new Exception('Le type de donnée n\'a pas pu être extrait de la base de données');
        }
    }


/**
 * Extraire tous les typeID présents dans un form
 * Brutal mais ça fonctionne ;-)
 * @return array Array de tous les typeID présents.
 */
    private function _formExtractDistinctTypes()
    {
        if ($formyaml=msSQL::sqlUniqueChamp("select yamlStructure from forms where id='".$this->_formID."' limit 1")) {
            preg_match_all("# - ([0-9]+)#i", $formyaml, $matches);
            return $matches[1];
        }
    }
}
