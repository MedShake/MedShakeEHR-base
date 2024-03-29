<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
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
 * Générer du SQL pour export
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 * SQLPREPOK
 */

class msSqlGenerate
{

	protected $_bdd;
	protected $_actes_fields;
	protected $_actes_values;
	protected $_actes_base_fields;
	protected $_actes_base_values;
	protected $_actes_cat_fields;
	protected $_actes_cat_values;
	protected $_configuration_fields;
	protected $_configuration_values;
	protected $_data_cat_fields_array;
	protected $_data_cat_fields;
	protected $_data_cat_values;
	protected $_data_types_fields_array;
	protected $_data_types_fields;
	protected $_data_types_values;
	protected $_forms_fields_array;
	protected $_forms_fields;
	protected $_forms_values;
	protected $_forms_cat_fields_array;
	protected $_forms_cat_fields;
	protected $_forms_cat_values;
	protected $_form_basic_fields;
	protected $_form_basic_values;
	protected $_people_fields;
	protected $_people_values;
	protected $_prescriptions_cat_fields;
	protected $_prescriptions_cat_values;
	protected $_prescriptions_fields;
	protected $_prescriptions_values;
	protected $_system_fields;
	protected $_system_values;
	protected $_univtags_type_fields;
	protected $_univtags_type_values;
	protected $_tablesSql = [];

	protected $_addUpdateOnDupicate = false;

	public function __construct()
	{
		global $p;
		$this->_bdd = $p['config']['sqlBase'];
	}

	/**
	 * Définir la base de données sur laquelle extraire
	 * @param string $bdd nom de la base de données
	 */
	public function setBdd($bdd)
	{
		return $this->_bdd = $bdd;
	}

	/**
	 * Définir l'ajout de la syntaxe "on duplicate update" pour les insert
	 * @param bool $addUpdateOnDupicate true / false
	 */
	public function setAddUpdateOnDuplicate($addUpdateOnDupicate)
	{
		$this->_addUpdateOnDupicate = $addUpdateOnDupicate;
	}

	/**
	 * Obtenir le SQL complet d'un module
	 * @param  string $name nom du module
	 * @return string       code SQL
	 */
	public function getSqlForModule($name)
	{

		//system
		$system = msSQL::sqlUnique("SELECT * from $this->_bdd.`system` where name= :name", ['name' => $name]);
		unset($system['id']);
		$this->_system_fields = $this->_getSqlFieldsPart($system);
		$this->_system_values[] = $this->_getSqlValuesPart($system);

		//configuration
		if ($configurations = msSQL::sql2tab("SELECT * from $this->_bdd.configuration where module = :name and level='module'", ['name' => $name])) {
			foreach ($configurations as $configuration) {
				unset($configuration['id']);
				if (!isset($this->_configuration_fields)) $this->_configuration_fields = $this->_getSqlFieldsPart($configuration);
				$this->_configuration_values[] = $this->_getSqlValuesPart($configuration);
			}
		}

		//actes
		$this->_prepareSqlForActes($name);

		//formulaires
		if ($listesForms = msSQL::sql2tabSimple("SELECT internalName from $this->_bdd.forms where module = :name", ['name' => $name])) {
			foreach ($listesForms as $formName) {
				$this->_prepareSqlForForm($formName);
			}
		}

		//autres data_type
		if ($name != 'base') {
			$notInGroup = ['admin', 'medical'];
		} else {
			$notInGroup = [];
		}
		$this->_prepareSqlForDataTypes($name, $notInGroup);

		//extension par module
		if (method_exists('msMod' . ucfirst($name) . 'SqlGenerate', '_getSpecifSql')) {
			$this->_getSpecifSql();
		}

		return $this->_composeSql();
	}

	/**
	 * Obtenir le SQL pour un formulaire particulier
	 * @param  string $name internalNamename du formulaire
	 * @return string       code SQL
	 */
	public function getSqlForForm($name)
	{
		$this->_prepareSqlForForm($name);
		return $this->_composeSql();
	}

	/**
	 * Composer le SQL pour data_types data_cat d'un module et groupe particuliers
	 * @param  string $module      name du module
	 * @param  array  $notInGroupe tableau des groupes de data à NE PAS inclure
	 * @return void
	 */
	public function _prepareSqlForDataTypes($module, $notInGroupe = [''])
	{
		$sqlImplode = msSQL::sqlGetTagsForWhereIn($notInGroupe, 'notg');
		$marqueurs = array_merge(['module' => $module], $sqlImplode['execute']);

		if ($typesData = msSQL::sql2tab("SELECT * from $this->_bdd.data_types where module = :module and  groupe not in (" . $sqlImplode['in'] . ")", $marqueurs)) {
			$sqlImplode = msSQL::sqlGetTagsForWhereIn(array_unique(array_column($typesData, 'cat')), 'cat');
			$catData = msSQL::sql2tab("SELECT * from $this->_bdd.data_cat where id in (" . $sqlImplode['in'] . ")", $sqlImplode['execute']);

			// data_cat
			foreach ($catData as $v) {
				unset($v['id']);
				$v['fromID'] = '1';
				$v['creationDate'] = "2019-01-01 00:00:00";
				if (!isset($this->_data_cat_fields)) $this->_data_cat_fields = $this->_getSqlFieldsPart($v);
				if (!isset($this->_data_cat_values[$v['name']])) $this->_data_cat_values[$v['name']] = $this->_getSqlValuesPart($v);
			}

			// data
			foreach ($typesData as $v) {
				unset($v['id']);
				$catID = $v['cat'];
				$v['fromID'] = '1';
				$v['creationDate'] = "2019-01-01 00:00:00";
				if (isset($v['cat'])) $v['cat'] = '@catID';
				if (!isset($this->_data_types_fields)) $this->_data_types_fields = $this->_getSqlFieldsPart($v);
				if (!isset($this->_data_types_values[$catID][$v['name']])) $this->_data_types_values[$catID][$v['name']] = $this->_getSqlValuesPart($v);
			}
		}
	}

	/**
	 * Composer le SQL pour les actes actes_base et actes_cat
	 * @param  string $name name du module
	 * @return void
	 */
	public function _prepareSqlForActes($name)
	{
		$collecteCcamNgap = [];

		if ($cats = msSQL::sql2tab("SELECT * from $this->_bdd.actes_cat where module = :name", ['name' => $name])) {
			foreach ($cats as $cat) {
				unset($cat['id']);
				$cat['fromID'] = 1;
				$cat['creationDate'] = "2019-01-01 00:00:00";
				if (!isset($this->_actes_cat_fields)) $this->_actes_cat_fields = $this->_getSqlFieldsPart($cat);
				$this->_actes_cat_values[] = $this->_getSqlValuesPart($cat);
			}
		}

		if ($actes = msSQL::sql2tab("SELECT a.* , c.name as catName
			from $this->_bdd.actes as a
			left join $this->_bdd.actes_cat as c on c.id=a.cat
			where c.module = :name and a.toID='0'
			group by a.id", ['name' => $name])) {
			$collecteCcamNgap = [];
			foreach ($actes as $acte) {
				$catName = $acte['catName'];
				unset($acte['id'], $acte['catName']);
				$acte['fromID'] = 1;
				$acte['creationDate'] = "2019-01-01 00:00:00";
				$acte['cat'] = '@catID';
				if (!isset($this->_actes_fields)) $this->_actes_fields = $this->_getSqlFieldsPart($acte);
				$this->_actes_values[$catName][] = $this->_getSqlValuesPart($acte);

				//collecter actes NGAP/CCAM
				$details = msYAML::yamlYamlToArray($acte['details']);
				if (is_array($details)) {
					$collecteCcamNgap = array_merge($collecteCcamNgap, array_keys($details));
				}
			}
		}

		// recherche de méthode informative dans la class du module
		$listFromModule = [];
		$className = 'msMod' . ucfirst($name) . 'SqlGenerate';
		if (class_exists($className, TRUE)) {
			if (method_exists($className, '_getActesModuleSqlExtraction')) {
				$listFromModule = $this->_getActesModuleSqlExtraction();
			}
		}

		// extraction finale des actes NGAP / CCAM nécessaires
		$sqlImplode = msSQL::sqlGetTagsForWhereIn(array_unique(array_merge($collecteCcamNgap, $listFromModule)), 'act');
		if ($actesbase = msSQL::sql2tab("SELECT * from $this->_bdd.actes_base where `code` in (" . $sqlImplode['in'] . ") order by `type`, `code`", $sqlImplode['execute'])) {
			foreach ($actesbase as $actebase) {
				unset($actebase['id']);
				$actebase['fromID'] = 1;
				$actebase['creationDate'] = "2019-01-01 00:00:00";
				if (!isset($this->_actes_base_fields)) $this->_actes_base_fields = $this->_getSqlFieldsPart($actebase);
				$this->_actes_base_values[] = $this->_getSqlValuesPart($actebase);
			}
		}
	}


	/**
	 * Composer le SQL pour un formulaire particulier
	 * @param  string $name internalName du formulaire
	 * @return void
	 */
	protected function _prepareSqlForForm($name)
	{
		$formID = msSQL::sqlUniqueChamp("SELECT id from $this->_bdd.forms where internalName = :name limit 1", ['name' => $name]);

		// form
		$v = msSQL::sqlUnique("SELECT * from $this->_bdd.forms where id = :formID limit 1", ['formID' => $formID]);
		$catForm = $v['cat'];
		unset($v['id']);
		$catID = $v['cat'];
		if (isset($v['cat'])) $v['cat'] = '@catID';
		if (!isset($this->_forms_fields_array)) $this->_forms_fields_array = array_keys($v);
		if (!isset($this->_forms_fields)) $this->_forms_fields = $this->_getSqlFieldsPart($v);
		if (!isset($this->_forms_values[$catID][$v['internalName']])) $this->_forms_values[$catID][$v['internalName']] = $this->_getSqlValuesPart($v);

		// form cat
		$v = msSQL::sqlUnique("SELECT * from $this->_bdd.forms_cat where id = :catForm limit 1", ['catForm' => $catForm]);
		unset($v['id']);
		$v['fromID'] = '1';
		$v['creationDate'] = "2019-01-01 00:00:00";
		if (!isset($this->_forms_cat_fields_array)) $this->_forms_cat_fields_array = array_keys($v);
		if (!isset($this->_forms_cat_fields)) $this->_forms_cat_fields = $this->_getSqlFieldsPart($v);
		if (!isset($this->_forms_cat_values[$v['name']])) $this->_forms_cat_values[$v['name']] = $this->_getSqlValuesPart($v);

		$typesData = [];
		$cats = [];
		//extraire tous les types du form
		$formyaml = msSQL::sqlUniqueChamp("SELECT yamlStructure from $this->_bdd.forms where id = :formID limit 1", ['formID' => $formID]);
		preg_match_all("# - (?!template|label)([\w]+)#i", $formyaml, $matchIN);
		$sqlImplodeCF = msSQL::sqlGetTagsForWhereIn($matchIN[1], 'champ');
		if ($typesDataInForm = msSQL::sql2tab("SELECT * from $this->_bdd.data_types where name in (" . $sqlImplodeCF['in'] . ")", $sqlImplodeCF['execute'])) {
			$typesData = array_merge($typesData, $typesDataInForm);
			$cats = array_merge($cats, array_unique(array_column($typesData, 'cat')));
		}

		// ajout du porteur de cs pour le form
		if ($typesDataCompCs = msSQL::sql2tab("SELECT * from $this->_bdd.data_types where groupe='typecs' and formValues = :name", ['name' => $name])) {
			$typesData = array_merge($typesData, $typesDataCompCs);
			$cats = array_merge($cats, array_unique(array_column($typesData, 'cat')));
		}

		// data_cat
		$sqlImplodeCat = msSQL::sqlGetTagsForWhereIn($cats, 'cat');
		if ($catsData = msSQL::sql2tab("SELECT * from $this->_bdd.data_cat where id in (" . $sqlImplodeCat['in'] . ")", $sqlImplodeCat['execute'])) {
			foreach ($catsData as $v) {
				unset($v['id']);
				$v['fromID'] = '1';
				$v['creationDate'] = "2019-01-01 00:00:00";
				if (!isset($this->_data_cat_fields_array)) $this->_data_cat_fields_array = array_keys($v);
				if (!isset($this->_data_cat_fields)) $this->_data_cat_fields = $this->_getSqlFieldsPart($v);
				if (!isset($this->_data_cat_values[$v['name']])) $this->_data_cat_values[$v['name']] = $this->_getSqlValuesPart($v);
			}
		}

		// data
		if (!empty($typesData)) {
			foreach ($typesData as $v) {
				unset($v['id']);
				$catID = $v['cat'];
				$v['fromID'] = '1';
				$v['creationDate'] = "2019-01-01 00:00:00";
				if (isset($v['cat'])) $v['cat'] = '@catID';
				if (!isset($this->_data_types_fields_array)) $this->_data_types_fields_array = array_keys($v);
				if (!isset($this->_data_types_fields)) $this->_data_types_fields = $this->_getSqlFieldsPart($v);
				if (!isset($this->_data_types_values[$catID][$v['name']])) $this->_data_types_values[$catID][$v['name']] = $this->_getSqlValuesPart($v);
			}
		}
	}

	/**
	 * Composer le SQL
	 * @return string code SQL
	 */
	protected function _composeSql()
	{
		$string = '';

		//tables
		if (!empty($this->_tablesSql)) {
			foreach ($this->_tablesSql as $table => $sql) {
				$string .= "-- création de la table " . $table . "\n";
				$string .= $sql . "\n\n";
			}
		}

		//actes_cat
		if (isset($this->_actes_cat_values)) {
			asort($this->_actes_cat_values);
			$string .= "-- actes_cat\n";
			$string .= "INSERT IGNORE INTO `actes_cat` " . $this->_actes_cat_fields . " VALUES\n";
			$string .= implode(",\n", $this->_actes_cat_values) . ";\n\n";
		}

		//actes_base
		if (isset($this->_actes_base_values)) {
			asort($this->_actes_base_values);
			$string .= "-- actes_base\n";
			$string .= "INSERT IGNORE INTO `actes_base` " . $this->_actes_base_fields . " VALUES\n";
			$string .= implode(",\n", $this->_actes_base_values) . ";\n\n";
		}

		//actes
		if (isset($this->_actes_values)) {
			ksort($this->_actes_values);
			$string .= "-- actes\n";
			foreach ($this->_actes_values as $catName => $values) {
				asort($this->_actes_values[$catName]);
				$tabActes[$catName] = "SET @catID = (SELECT actes_cat.id FROM actes_cat WHERE actes_cat.name='" . $catName . "');\n";
				$tabActes[$catName] .= "INSERT IGNORE INTO `actes` " . $this->_actes_fields . " VALUES\n";
				$tabActes[$catName] .= implode(",\n", $this->_actes_values[$catName]) . ";\n\n";
			}
			ksort($tabActes);
			$string .= implode("\n", $tabActes);
			unset($tabActes);
		}

		//data_cat
		if (isset($this->_data_cat_values)) {
			asort($this->_data_cat_values);
			$string .= "-- data_cat\n";
			$string .= "INSERT IGNORE INTO `data_cat` " . $this->_data_cat_fields . " VALUES\n";
			$string .= implode(",\n", $this->_data_cat_values);
			if ($this->_addUpdateOnDupicate == true) {
				$string .= "\nON DUPLICATE KEY UPDATE ";
				$up = [];
				foreach ($this->_data_cat_fields_array as $v) {
					$up[] = $v . "=values(" . $v . ")";
				}
				$string .= implode(", ", $up) . ";\n\n";
			} else {
				$string .= ";\n\n";
			}
		}

		//data_types
		if (isset($this->_data_types_values)) {
			ksort($this->_data_types_values);
			$string .= "-- data_types\n";
			foreach ($this->_data_types_values as $cat => $values) {
				asort($this->_data_types_values[$cat]);
				$catName = msSQL::sqlUniqueChamp("SELECT name from $this->_bdd.data_cat where id = :cat ", ['cat' => $cat]);
				$tabTypes[$catName] = '';
				if (empty($catName)) {
					$tabTypes[$catName] .= "SET @catID = 0;\n";
				} else {
					$tabTypes[$catName] .= "SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='" . $catName . "');\n";
				}
				$tabTypes[$catName] .= "INSERT IGNORE INTO `data_types` " . $this->_data_types_fields . " VALUES\n";
				$tabTypes[$catName] .= implode(",\n", $this->_data_types_values[$cat]);

				if ($this->_addUpdateOnDupicate == true) {
					$tabTypes[$catName] .= "\nON DUPLICATE KEY UPDATE ";
					$up = [];
					foreach ($this->_data_types_fields_array as $v) {
						if ($v == 'cat') {
							$up[] = "cat=@catID";
						} else {
							$up[] = $v . "=values(" . $v . ")";
						}
					}
					$tabTypes[$catName] .= implode(", ", $up) . ";\n\n";
				} else {
					$tabTypes[$catName] .= ";\n\n";
				}
			}
			ksort($tabTypes);
			$string .= implode("\n", $tabTypes);
			unset($tabTypes);
		}

		//configuration
		if (isset($this->_configuration_values)) {
			asort($this->_configuration_values);
			$string .= "-- configuration\n";
			$string .= "INSERT IGNORE INTO `configuration` " . $this->_configuration_fields . " VALUES\n";
			$string .= implode(",\n", $this->_configuration_values) . ";\n\n";
		}

		//forms cat
		if (isset($this->_forms_cat_values) and !empty($this->_forms_cat_values)) {
			asort($this->_forms_cat_values);
			$string .= "-- forms_cat\n";
			$string .= "INSERT IGNORE INTO `forms_cat` " . $this->_forms_cat_fields . " VALUES\n";
			$string .= implode(",\n", $this->_forms_cat_values);
			if ($this->_addUpdateOnDupicate == true) {
				$string .= "\nON DUPLICATE KEY UPDATE ";
				$up = [];
				foreach ($this->_forms_cat_fields_array as $v) {
					$up[] = $v . "=values(" . $v . ")";
				}
				$string .= implode(", ", $up) . ";\n\n";
			} else {
				$string .= ";\n\n";
			}
		}

		//forms
		if (isset($this->_forms_values)) {
			ksort($this->_forms_values);
			$string .= "-- forms\n";
			foreach ($this->_forms_values as $cat => $values) {
				asort($this->_forms_values[$cat]);
				$catName = msSQL::sqlUniqueChamp("SELECT name from $this->_bdd.forms_cat where id = :cat ", ['cat' => $cat]);
				$tabForms[$catName] = "SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='" . $catName . "');\n";
				$tabForms[$catName] .= "INSERT IGNORE INTO `forms` " . $this->_forms_fields . " VALUES\n";
				$tabForms[$catName] .= implode(",\n", $this->_forms_values[$cat]);

				if ($this->_addUpdateOnDupicate == true) {
					$tabForms[$catName] .= "\nON DUPLICATE KEY UPDATE ";
					$up = [];
					foreach ($this->_forms_fields_array as $v) {
						if ($v == 'cat') {
							$up[] = "cat=@catID";
						} else {
							$up[] = $v . "=values(" . $v . ")";
						}
					}
					$tabForms[$catName] .= implode(", ", $up) . ";\n\n";
				} else {
					$tabForms[$catName] .= ";\n\n";
				}
			}
			ksort($tabForms);
			$string .= implode("\n", $tabForms);
			unset($tabForms);
		}

		//form_basic_types
		if (isset($this->_form_basic_values) and !empty($this->_form_basic_values)) {
			asort($this->_form_basic_values);
			$string .= "-- form_basic_types\n";
			$string .= "INSERT IGNORE INTO `form_basic_types` " . $this->_form_basic_fields . " VALUES\n";
			$string .= implode(",\n", $this->_form_basic_values) . ";\n\n";
		}

		//people
		if (isset($this->_people_values) and !empty($this->_people_values)) {
			asort($this->_people_values);
			$string .= "-- people\n";
			$string .= "INSERT IGNORE INTO `people` " . $this->_people_fields . " VALUES\n";
			$string .= implode(",\n", $this->_people_values) . ";\n\n";
		}

		//prescriptions_cat
		if (isset($this->_prescriptions_cat_values)) {
			asort($this->_prescriptions_cat_values);
			$string .= "-- prescriptions_cat\n";
			$string .= "INSERT IGNORE INTO `prescriptions_cat` " . $this->_prescriptions_cat_fields . " VALUES\n";
			$string .= implode(",\n", $this->_prescriptions_cat_values) . ";\n\n";
		}

		//prescriptions
		if (isset($this->_prescriptions_values)) {
			ksort($this->_prescriptions_values);
			$string .= "-- prescriptions\n";
			foreach ($this->_prescriptions_values as $cat => $values) {
				asort($this->_prescriptions_values[$cat]);
				$catName = msSQL::sqlUniqueChamp("SELECT name from $this->_bdd.prescriptions_cat where id = :cat ", ['cat' => $cat]);
				$tabPres[$catName] = "SET @catID = (SELECT prescriptions_cat.id FROM prescriptions_cat WHERE prescriptions_cat.name='" . $catName . "');\n";
				$tabPres[$catName] .= "INSERT IGNORE INTO `prescriptions` " . $this->_prescriptions_fields . " VALUES\n";
				$tabPres[$catName] .= implode(",\n", $this->_prescriptions_values[$cat]) . ";\n\n";
			}
			ksort($tabPres);
			$string .= implode("\n", $tabPres);
			unset($tabPres);
		}

		//system
		if (isset($this->_system_values) and !empty($this->_system_values)) {
			asort($this->_system_values);
			$string .= "-- system\n";
			$string .= "INSERT IGNORE INTO `system` " . $this->_system_fields . " VALUES\n";
			$string .= implode(",\n", $this->_system_values) . ";\n\n";
		}

		//univtags_type
		if (isset($this->_univtags_type_values) and !empty($this->_univtags_type_values)) {
			asort($this->_univtags_type_values);
			$string .= "-- univtags_type\n";
			$string .= "INSERT IGNORE INTO `univtags_type` " . $this->_univtags_type_fields . " VALUES\n";
			$string .= implode(",\n", $this->_univtags_type_values) . ";\n\n";
		}

		return $string;
	}

	/**
	 * Générer un chainon de la partie values d'un INSERT
	 * @param  array $a tableau col=>value
	 * @return string    chaine ('value', 'value' ...)
	 */
	protected function _getSqlValuesPart($a)
	{

		if (!empty($a)) {
			$p = [];
			foreach ($a as $v) {
				if ($v === '') {
					$p[] = "''";
				} elseif ($v === NULL) {
					$p[] = 'NULL';
				} elseif (is_int($v)) {
					$p[] = $v;
				} elseif ($v == '@catID') {
					$p[] = $v;
				} else {
					$v = addslashes($v);
					$v = str_replace("\n", '\n', $v);
					$v = str_replace("\r", '\r', $v);
					$p[] = "'" . $v . "'";
				}
			}
			return '(' . implode(', ', $p) . ')';
		} else {
			return '';
		}
	}

	/**
	 * Générer la partie initiale d'un INSERT (noms des colonnes)
	 * @param  array $a tableau col=>$value
	 * @return string    chaine (col1, col2 ...)
	 */
	protected function _getSqlFieldsPart($a)
	{
		return "(`" . implode("`, `", array_keys($a)) . "`)";
	}

	/**
	 * Générer le code de création d'une table
	 * @param  string $t nom de la table
	 * @return string    sql de création
	 */
	protected function _getTableStructure($t)
	{
		if (!preg_match("/^[a-zA-Z_][a-zA-Z0-9_]{0,63}$/", $t)) {
			throw new Exception("Nom de table invalide");
		}
		$tab = msSQL::sqlUnique("SHOW CREATE TABLE $this->_bdd." . $t);
		if (isset($tab['Create Table'])) {
			$tab['Create Table'] = preg_replace('#AUTO_INCREMENT=[0-9]+ #i', '', $tab['Create Table']);
			$tab['Create Table'] = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $tab['Create Table']);
			$this->_tablesSql[$tab['Table']] = $tab['Create Table'] . ';';
		} elseif (isset($tab['Create View'])) {
			$tab['Create View'] = preg_replace('#DEFINER=.* SQL SECURITY#', ' SQL SECURITY', $tab['Create View']);
			$this->_tablesSql[$tab['View']] = "DROP VIEW IF EXISTS `" . $t . "`;\n" . $tab['Create View'] . ';';
		}
	}
}
