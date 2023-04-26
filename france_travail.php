<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

//require_once(_PS_MODULE_DIR_ . 'france_travail/classes/model/workcountry.php');
require_once('classes/model/workcountry.php');

class France_travail extends Module
{
    static $table_name = 'workcountry';

    public function __construct()
    {
        $this->need_instance = 1;

        $this->name = 'france_travail';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Emanuel Macron';
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('France travail');
        $this->description = $this->l('Une gare, c’est un lieu où on croise les gens qui réussissent et les gens qui ne sont rien');

        $this->confirmUninstall = $this->l('je traverse la rue, je vous en trouve !');
    }

    public function install(): bool
    {
        return parent::install() &&
            $this->registerHook('displayFooterProduct') &&
            $this->installDB();
    }

    public function hookDisplayFooterProduct($params)
    {
        $workcountry = Workcountry::getByIso(strtoupper($this->context->language->iso_code));

        $this->smarty->assign(['name' => $workcountry->name]);
        $this->smarty->assign(['description' => $workcountry->description]);
        return $this->display(__FILE__, 'france_travail.tpl');
    }

    public function uninstall(): bool
    {
        return parent::uninstall() &&
            $this->uninstallDB();
    }

    public function getContent()
    {
        // ici on a un retour de fonction qui n'est pasutilisé
        $this->process();

        if (Tools::isSubmit('add' . $this->name)) {
            return $this->renderForm();
        } else if (Tools::isSubmit('update' . $this->name) && Tools::isSubmit('id_workcountry')) {
            return $this->renderForm(Tools::getValue('id_workcountry'));
        }

        return $this->renderList();
    }

    public function process(): bool
    {
        if (Tools::isSubmit('submit' . $this->name)) {
            return $this->save(Tools::getValue('id_workcountry'));
        } else if (Tools::isSubmit('delete' . $this->name)) {
            return $this->delete();
        }

        return true;
    }

    protected function delete(): bool
    {
        if (Tools::isSubmit('id_workcountry')) {
            $obj = new Workcountry((int)Tools::getValue('id_workcountry'));
            return $obj->delete();
        }
        return false;
    }

    protected function save($id = null): bool
    {
        $obj = new Workcountry((int)$id);

        $obj->name = Tools::getValue('name');
        $obj->description = Tools::getValue('description');
        $obj->iso = Tools::getValue('iso');

        if (!$obj->save())
            return false;

        return true;
    }

    public function renderList()
    {
        $results = Workcountry::getList();

        // champs utilisés dans la liste pour chaque colonne (balise et propriétés html)
        $fields_list = [
            'id_workcountry' => [
                'title' => $this->l('Id'),
                'width' => 60,
                'type' => 'text',
            ],
            'name' => [
                'title' => $this->l('Nom du Pays'),
                'width' => 140,
                'type' => 'text',
            ],
            'iso' => [
                'title' => $this->l('ISO du Pays'),
                'width' => 60,
                'type' => 'text',
            ],
            'description' => [
                'title' => $this->l('Description du Pays'),
                'width' => 140,
                'type' => 'text',
            ],
        ];

        $helper = new HelperList();
        $helper->title = $this->l('Liste des pays');
        $helper->simple_header = true;
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->toolbar_btn['new'] = array(
            'href' => $helper->currentIndex . '&add' . $this->name . '&token=' . $helper->token,
            'desc' => $this->trans('Add new', array(), 'Admin.Actions')
        );

        $helper->identifier = Workcountry::$definition['primary'];
        $helper->table = $this->name;
        $helper->actions = array('edit', 'delete');
        $helper->no_link = true;
        $helper->shopLinkType = null;

        return $helper->generateList($results, $fields_list);
    }


    public function renderForm($id_workcountry = null)
    {
        $fieldsForm[0]['form'] = [
            'tinymce' => true,
            'legend' => [
                'title' => 'Pays',
            ],
            'input' => [
                [
                    'name' => 'name',
                    'label' => 'Nom du Pays',
                    'size' => 140,
                    'type' => 'text',
                    'required' => true
                ],
                [
                    'name' => 'iso',
                    'label' => 'ISO du Pays',
                    'size' => 60,
                    'type' => 'text',
                    'required' => true
                ],
                [
                    'name' => 'description',
                    'label' => 'Description du Pays',
                    'size' => 140,
                    'type' => 'text',
                    'required' => true
                ],
            ],
            'submit' => [
                'title' => 'Sub',
                'class' => 'btn btn-default pull-right'
            ],
            'buttons' => [
                'cancel' => [
                    'title' => $this->l('Bouton ayant une autre fonction (ici quitter le formulaire)'),
                    'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                    'icon' => 'process-icon-cancel'
                ],
            ]
        ];

        $helper = new HelperForm();

        // Module, jeton et index courant
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Langue par défaut
        $currentLang = (int)Context::getContext()->language->id;

        // Langue
        $helper->default_form_language = $currentLang;
        $helper->allow_employee_form_lang = $currentLang;

        // titre du formulaire
        $helper->title = $this->l('Liste des Pays Jamais affichée ???');

        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->name;

        if (!empty($id_workcountry)) {
            $fieldsForm[0]['form']['input'][] = [
                'type' => 'hidden',
                'name' => 'id_workcountry',
            ];
        }

        //S'il s'agit d'un modification, on va aller rechercher les informations dans la base de donnée pour initialiser les champs
        if (!empty($id_workcountry)) {
            $workcountry = new Workcountry((int)$id_workcountry);

            $helper->fields_value['id_workcountry'] = $workcountry->id;
            $helper->fields_value['name'] = $workcountry->name;
            $helper->fields_value['iso'] = $workcountry->iso;
            $helper->fields_value['description'] = $workcountry->description;

        } else {
            $helper->fields_value['name'] = "";
            $helper->fields_value['iso'] = "";
            $helper->fields_value['description'] = "";
        }

        return $helper->generateForm($fieldsForm);
    }

    private function installDB(): bool
    {
        if (!Configuration::updateValue('france_travail_var_conf', 'ON MET UN POGNON DE DINGUE DANS LES MINIMAS SOCIAUX'))
            return false;

        $db = Db::getInstance();

        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . self::$table_name . '` (
            `id_workcountry` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `iso` varchar(2) NOT NULL,
            `name` varchar(255) NOT NULL,
            `description` varchar(255) NOT NULL,
            PRIMARY KEY (`id_workcountry`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;';

        if (!$db->execute($sql))
            return false;

        /*
                $name = 'France';
                $iso = 'FR';
                $description = 'Bah la on fait la gréve';

                if (!$db->insert(self::$table_name, [
                    'iso' => $iso,
                    'name' => $name,
                    'description' => $description,
                ]))
                    return false;
        */

        $france = new Workcountry();
        $france->name = 'France';
        $france->iso = 'FR';
        $france->description = 'Bah la on fait la gréve';

        if (!$france->save())
            return false;

        return true;
    }

    private function uninstallDB(): bool
    {
        return Configuration::deleteByName('france_travail_var_conf') &&
            Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . self::$table_name . '`;');
    }
}