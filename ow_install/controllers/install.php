<?php

class INSTALL_CTRL_Install extends INSTALL_ActionController
{
    public function init( $dispatchAttrs = null, $dbReady = null )
    {
        if ( $dbReady && $dispatchAttrs["action"] != "finish" )
        {
            $this->redirect(OW::getRouter()->urlForRoute("finish"));
        }
    }

    public function requirements()
    {
        $this->setPageHeading("Welcome to Oxwall Installation!");
        
        $lines = file(INSTALL_DIR_FILES . 'requirements.txt');
        $ruleLines = array();

        foreach ( $lines as $line )
        {
            $line = trim($line);

            if ( empty($line) || strpos($line, '#') === 0 )
            {
                continue;
            }

            $ruleLines[] = $line;
        }

        $rulesContent = implode('', $ruleLines);
        $rules = explode(';', $rulesContent);
        $rules = array_filter($rules, 'trim');

        $fails = array();
        $current = array();

        foreach ( $rules as $ruleLine )
        {
            $rule = array_filter(explode(' ', $ruleLine), 'trim');

            if ( count($rule) < 2 )
            {
                continue;
            }

            $spacePos = strpos($ruleLine, ' ');
            $config = substr($ruleLine, 0, $spacePos);
            $value = substr($ruleLine, $spacePos + 1);

            switch (true)
            {
                case strpos($config, 'php.') === 0:

                    $fails['php'] = empty($fails['php']) ? null : $fails['php'];

                    $phpOption = substr($config, 4);

                    switch ( $phpOption )
                    {
                        case 'version':
                            $phpVersion = phpversion();
                            if ( version_compare($phpVersion, $value) < 1 )
                            {
                                $fails['php'][$phpOption] =  $value;
                                $current['php'][$phpOption] = $phpVersion;
                            }
                            break;

                        case 'extensions':
                            $requiredExtensions = array_map('trim', explode(',', $value));
                            $loadedExtensions = get_loaded_extensions();
                            $diff = array_values(array_diff($requiredExtensions, $loadedExtensions));
                            if ( !empty($diff) )
                            {
                                $fails['php'][$phpOption] = $diff;
                            }
                            break;
                    }

                break;

                case strpos($config ,'ini.') === 0:
                    $value = ( $value == 'off' || $value == '0' ) ? false : true;
                    $iniConfig = substr($config, 4);
                    $iniValue = (bool) ini_get($iniConfig);

                    if ( intval($iniValue) != intval($value) )
                    {
                        $fails['ini'][$iniConfig] = intval($value);
                        $current['ini'][$iniConfig] = intval($iniValue);
                    }
                    $fails['ini'] = empty($fails['ini']) ? null : $fails['ini'];
                    break;

                case strpos($config ,'gd.') === 0:
                    $gdOption = substr($config, 3);
                    $fails['gd'] = empty($fails['gd']) ? null : $fails['gd'];
                    
                    if ( !function_exists("gd_info") )
                    {
                        break;
                    }
                    
                    $gdInfo = gd_info();

                    switch ($gdOption)
                    {
                        case 'version':
                            preg_match( '/(\d)\.(\d)/', $gdInfo['GD Version'], $match );
                            $gdVersion = $match[1];

                            if ( $gdVersion < $value )
                            {
                                $fails['gd'][$gdOption] = $value;
                                $current['gd'][$gdOption] = $gdVersion;
                            }
                            break;

                        case 'support':

                            if ( empty($gdInfo[$value]) )
                            {
                                $fails['gd'][$gdOption] = $value;
                            }
                            break;
                    }
                    break;
            }
        }

        $this->assign('fails', $fails);
        $this->assign('current', $current);

        $checkRequirements = array_filter($fails);

        if ( empty($checkRequirements) )
        {
            $this->redirect( OW::getRouter()->urlForRoute('site') );
        }
    }

    public function site()
    {
        $this->setPageHeading("Welcome to Oxwall Installation!");
        $this->setPageTitle('Site');
        INSTALL::getStepIndicator()->activate('site');

        $fieldData = array();
        $fieldData['site_url'] = OW_URL_HOME;
        $fieldData['site_path'] = OW_DIR_ROOT;

        $sessionData = INSTALL::getStorage()->getAll();
        $fieldData = array_merge($fieldData, $sessionData);

        $this->assign('data', $fieldData);

        $errors = array();

        if (OW::getRequest()->isPost())
        {
            $data = $_POST;
            $data = array_filter($data, 'trim');

            $success = true;

            if ( empty($data['site_title']) )
            {
                $errors[] = 'site_title';
            }

            if ( empty($data['site_url']) || !trim($data['site_url']) )
            {
                $errors[] = 'site_url';
            }

            if ( empty($data['site_path']) || !is_dir($data['site_path']) )
            {
                $errors[] = 'site_path';
            }

            if ( empty($data['admin_username']) || !UTIL_Validator::isUserNameValid($data['admin_username']) )
            {
                $errors[] = 'admin_username';
            }

            if ( empty($data['admin_password']) || strlen($data['admin_password']) < 3 )
            {
                $errors[] = 'admin_password';
            }

            if ( empty($data['admin_email']) || !UTIL_Validator::isEmailValid($data['admin_email']) )
            {
                $errors[] = 'admin_email';
            }

            $this->processData($data);

            if (empty($errors))
            {
                $this->redirect( OW::getRouter()->urlForRoute('db') );
            }

            foreach ( $errors as $flag )
            {
                INSTALL::getFeedback()->errorFlag($flag);
            }

            $this->redirect();
        }
    }

    public function db()
    {
        $this->setPageTitle('Database');
        INSTALL::getStepIndicator()->activate('db');

        $fieldData = array();
        $fieldData['db_prefix'] = 'ow_';

        $sessionData = INSTALL::getStorage()->getAll();
        $fieldData = array_merge($fieldData, $sessionData);

        $this->assign('data', $fieldData);

        $errors = array();

        if (OW::getRequest()->isPost())
        {
            $data = $_POST;
            $data = array_filter($data, 'trim');

            $success = true;

            if ( empty($data['db_host']) || !preg_match('/^[^:]+?(\:\d+)?$/', $data['db_host']))
            {
                $errors[] = 'db_host';
            }

            if ( empty($data['db_user']) )
            {
                $errors[] = 'db_user';
            }

            if ( empty($data['db_name']) )
            {
                $errors[] = 'db_name';
            }

            if ( empty($data['db_prefix']) )
            {
                $errors[] = 'db_prefix';
            }

            $this->processData($data);

            if (empty($errors))
            {
                $hostInfo = explode(':', $data['db_host']);

                try
                {
                    $dbo = OW_Database::getInstance(array(
                        'host' => $hostInfo[0],
                        'port' => empty($hostInfo[1]) ? null : $hostInfo[1],
                        'username' => $data['db_user'],
                        'password' => empty($data['db_password']) ? '' : $data['db_password'],
                        'dbname' => $data['db_name']
                    ));

                    $existingTables = $dbo->queryForColumnList("SHOW TABLES LIKE '{$data['db_prefix']}base_%'");

                    if ( !empty($existingTables) )
                    {
                        INSTALL::getFeedback()->errorMessage('This database should be empty _especially_ if you try to reinstall Oxwall.');

                        $this->redirect();
                    }
                }
                catch ( InvalidArgumentException $e )
                {
                    INSTALL::getFeedback()->errorMessage('Could not connect to Database<div class="feedback_error">Error: ' . $e->getMessage() . '</div>');

                    $this->redirect();
                }
            }

            if (empty($errors))
            {
                $this->redirect( OW::getRouter()->urlForRoute('install') );
            }

            foreach ( $errors as $flag )
            {
                INSTALL::getFeedback()->errorFlag($flag);
            }

            $this->redirect();
        }
    }

    public function install( $params = array() )
    {
        $success = true;

        $configFile = OW_DIR_INC . 'config.php';

        $dirs = array(
            OW_DIR_PLUGINFILES,
            OW_DIR_USERFILES,
            OW_DIR_STATIC,
            OW_DIR_SMARTY . 'template_c' . DS,
            OW_DIR_LOG
        );

        $errorDirs = array();
        $this->checkWritable($dirs, $errorDirs);

        $doInstall = isset($params["action"]);
        
        if ( OW::getRequest()->isPost() || $doInstall )
        {
            if ( !empty($_POST['isConfigWritable']) )
            {
                @file_put_contents($configFile, $_POST['configContent']);
                
                $this->redirect(OW::getRouter()->urlForRoute("install-action", array(
                    "action" => "install"
                )));
            }

            if ( !empty($errorDirs) )
            {
                //INSTALL::getFeedback()->errorMessage('Some directories are not writable');

                $this->redirect();
            }

            try
            {
                OW::getDbo();
            }
            catch ( InvalidArgumentException $e )
            {
                INSTALL::getFeedback()->errorMessage('Could not connect to Database');

                $this->redirect();
            }

            try
            {
                $this->sqlImport(INSTALL_DIR_FILES . 'install.sql');
            }
            catch ( Exception $e )
            {
                INSTALL::getFeedback()->errorMessage($e->getMessage());

                $this->redirect();
            }

            try
            {
                OW::getConfig()->saveConfig('base', 'site_installed', 0);
            }
            catch ( Exception $e )
            {
                OW::getConfig()->addConfig('base', 'site_installed', 0);
            }

            if ( isset($_POST['continue']) || $doInstall )
            {
                $this->redirect(OW::getRouter()->urlForRoute('plugins'));
            }
        }

        $this->setPageTitle('Installation');
        INSTALL::getStepIndicator()->activate('install');

        $configContent = file_get_contents(INSTALL_DIR_FILES . 'config.txt');
        $data = INSTALL::getStorage()->getAll();

        $hostInfo = explode(':', $data['db_host']);
        $data['db_host'] = $hostInfo[0];
        $data['db_port'] = empty($hostInfo[1]) ? 'null' : '"' . $hostInfo[1] . '"';

        $data['password_salt'] = uniqid();

        $search = array();
        $replace = array();

        foreach ( $data as $name => $value )
        {
            $search[] = '{$' . $name . '}';
            $replace[] = $value;
        }

        $outConfigContent = str_replace($search, $replace, $configContent);
        $this->assign('configContent', $outConfigContent);
        $this->assign('dirs', $errorDirs);

        $this->assign('isConfigWritable', is_writable($configFile));
    }

    private function checkWritable( $dirs, & $notWritableDirs )
    {
        foreach ( $dirs as $dir )
        {
            if ( !is_writable($dir) )
            {
                $notWritableDirs[] = substr($dir, 0, -1);;

                continue;
            }

            $handle = opendir($dir);
            $subDirs = array();
            while ( ($item = readdir($handle)) !== false )
            {
                if ( $item === '.' || $item === '..' )
                {
                    continue;
                }

                $path = $dir . $item;

                if ( is_dir($path) )
                {
                    $subDirs[] = $path . DS;
                }
            }

            $this->checkWritable($subDirs, $notWritableDirs);
        }
    }

    public function plugins()
    {
        $avaliablePlugins = $this->getPluginsForInstall();

        if ( OW::getRequest()->isPost() )
        {
            $plugins = empty($_POST['plugins']) ? array() : $_POST['plugins'];

            $installPlugins = array();

            foreach ( $plugins as $pluginKey )
            {
                if ( !empty($avaliablePlugins[$pluginKey]) )
                {
                    $installPlugins[$pluginKey] = $avaliablePlugins[$pluginKey]['plugin'];
                }
            }

            $this->installComplete($installPlugins);
        }

        INSTALL::getStepIndicator()->activate('plugins');
        $this->setPageTitle('Plugins');

        if ( empty($avaliablePlugins) )
        {
            $this->installComplete();
        }

        $this->assign('plugins', $avaliablePlugins);
    }

    public function finish()
    {
        INSTALL::getStepIndicator()->add('finish', 'Security', true);
    }

    private function installComplete( $installPlugins = null )
    {
        $storage = INSTALL::getStorage();

        $username = $storage->get('admin_username');
        $password = $storage->get('admin_password');
        $email = $storage->get('admin_email');

        $user = BOL_UserService::getInstance()->createUser($username, $password, $email, null, true);

        $realName = ucfirst($username);
        BOL_QuestionService::getInstance()->saveQuestionsData(array( 'realname' => $realName ), $user->id);

        BOL_AuthorizationService::getInstance()->addAdministrator($user->id);
        OW::getUser()->login($user->id);

        OW::getConfig()->saveConfig('base', 'site_name', $storage->get('site_title'));
        OW::getConfig()->saveConfig('base', 'site_tagline', $storage->get('site_tagline'));
        OW::getConfig()->saveConfig('base', 'site_email', $email);

        $notInstalledPlugins = array();

        if ( !empty($installPlugins) )
        {
            OW::getPluginManager()->initPlugins(); // Init installed plugins ( base, admin ), to insure that all of their package pointers are added
            
            foreach ( $installPlugins as $plugin )
            {
                try
                {
                    BOL_PluginService::getInstance()->install($plugin['key'], false);
                    OW::getPluginManager()->readPluginsList();
                    OW::getPluginManager()->initPlugin(OW::getPluginManager()->getPlugin($plugin['key']));
                }
                catch ( LogicException $e )
                {
                    $notInstalledPlugins[] = $plugin['key'];
                }
            }

            if ( !empty($notInstalledPlugins) )
            {
                //Some plugins were not installed
            }
        }

        OW::getConfig()->saveConfig('base', 'site_installed', 1);
        OW::getConfig()->saveConfig('base', 'dev_mode', 1);

        @UTIL_File::removeDir(OW_DIR_ROOT . "ow_install");

        $this->redirect(OW_URL_HOME);
    }



    private function getPluginsForInstall()
    {
        $fileContent = file_get_contents(INSTALL_DIR_FILES . 'plugins.txt');
        $pluginForInstall = explode("\n", $fileContent);

        $plugins = BOL_PluginService::getInstance()->getAvailablePluginsList();

        $resultPluginList = array();

        foreach ( $pluginForInstall as $pluginLine )
        {
            $plInfo = explode(':', $pluginLine);
            $pluginKey = $plInfo[0];

            if ( empty($plugins[$pluginKey]) )
            {
                continue;
            }

            $resultPluginList[$pluginKey] = array(
                'plugin' => $plugins[$pluginKey],
                'auto' =>  ( !empty($plInfo[1]) && trim($plInfo[1]) == 'auto' )
            );
        }

        return $resultPluginList;
    }

     /**
     * Executes an SQL dump file.
     *
     * @param string $sql_file path to file
     */
    private static function sqlImport( $sqlFile )
    {
        if ( !($fd = @fopen($sqlFile, 'rb')) ) {
            throw new LogicException('SQL dump file `'.$sqlFile.'` not found');
        }

        $lineNo = 0;
        $query = '';
        while ( false !== ($line = fgets($fd, 10240)) )
        {
            $lineNo++;

            if ( !strlen(($line = trim($line)))
                || $line{0} == '#' || $line{0} == '-'
                || preg_match('~^/\*\!.+\*/;$~siu', $line) ) {
                continue;
            }

            $query .= $line;

            if ( $line{strlen($line)-1} != ';' ) {
                continue;
            }

            $query = str_replace('%%TBL-PREFIX%%', OW_DB_PREFIX, $query);

            try {
                OW::getDbo()->query($query);
            }
            catch ( Exception $e ) {
                throw new LogicException('<b>ow_includes/config.php</b> file is incorrect. Update it with details provided below.');
            }

            $query = '';
        }

        fclose($fd);

        return true;
    }

    public function processData($data)
    {
        foreach ( $data as $name => $value )
        {
            INSTALL::getStorage()->set($name, $value);
        }
    }
}
