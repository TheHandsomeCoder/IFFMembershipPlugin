<?php


include_once('IffMembership_LifeCycle.php');
include_once('IffFencersTable.php');
include_once('IffSeasonsTable.php');
include_once('IffLicenceTable.php');

class IffMembership_Plugin extends IffMembership_LifeCycle {

    /**
     * See: http://plugin.michael-simpson.com/?page_id=31
     * @return array of option meta data.
     */
    public function getOptionMetaData() {
        //  http://plugin.michael-simpson.com/?page_id=31
        return array(
            //'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
            'ATextInput' => array(__('Enter in some text', 'my-awesome-plugin')),
            'Donated' => array(__('I have donated to this plugin', 'my-awesome-plugin'), 'false', 'true'),
            'CanSeeSubmitData' => array(__('Can See Submission data', 'my-awesome-plugin'),
                                        'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Anyone')
        );
    }

//    protected function getOptionValueI18nString($optionValue) {
//        $i18nValue = parent::getOptionValueI18nString($optionValue);
//        return $i18nValue;
//    }

    protected function initOptions() {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr > 1)) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }
    }

    public function getPluginDisplayName() {
        return 'IFF Membership';
    }

    protected function getMainPluginFileName() {
        return 'iff-membership.php';
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     * @return void
     */
    protected function installDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
        //            `id` INTEGER NOT NULL");
       
        $this->createUpdateTables();

    }

    protected function createUpdateTables()
    {
         global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        //create fencer status table
        $table_name1 = $this->prefixTableName("fencer_status");        
        $sql =  "CREATE TABLE $table_name1 (
            id INT NOT NULL AUTO_INCREMENT,
            display_name TEXT,
            PRIMARY KEY  (id)
        );";  
        dbDelta( $sql );      
       
        //create license_type table
        $table_name2 = $this->prefixTableName("licence_type");
        $sql = "CREATE TABLE $table_name2 (
            id INT NOT NULL AUTO_INCREMENT,
            display_name TEXT,
            cost DECIMAL,
            is_public TINYINT(1) DEFAULT 0,
            PRIMARY KEY  (id)
        );";
        dbDelta( $sql );

        //create season table
        $table_name3 = $this->prefixTableName("season");
        $sql = "CREATE TABLE $table_name3 (
            id INT NOT NULL AUTO_INCREMENT,
            display_name TEXT,
            start_date DATE,
            end_date DATE,          
            PRIMARY KEY  (id)
         );";
         dbDelta( $sql ); 

        //create season table
        $table_name4 = $this->prefixTableName("gender");
        $sql = "CREATE TABLE $table_name4 (
            id INT NOT NULL AUTO_INCREMENT,
            display_name TEXT,                     
            PRIMARY KEY  (id)
         );";
         dbDelta( $sql ); 
         
        //create fencer table
        $table_name10 = $this->prefixTableName("fencers");
        $sql = "CREATE TABLE $table_name10 (
             id INT NOT NULL AUTO_INCREMENT,
             license_number INT,
             first_name TEXT,
             last_name TEXT,
             gender INT,
             note LONGTEXT,
             nationality TEXT,
             status INT,
             licence_type INT,
             address_line1 TEXT,
             address_line2 TEXT,
             address_line3 TEXT,
             address_line4 TEXT,
             address_country TEXT,
             club TEXT,
             phone_number TEXT,
             email_address TEXT,
             last_season_paid_for INT,             
             PRIMARY KEY  (id)
            );";
         dbDelta( $sql );
        

         /**$sql = "ALTER TABLE $table_name10 ADD CONSTRAINT IF NOT EXISTS fencers_fk1 FOREIGN KEY (status) REFERENCES $table_name1(id);";
         $wpdb->query($sql);
         $sql = "ALTER TABLE $table_name10 ADD CONSTRAINT IF NOT EXISTS fencers_fk2 FOREIGN KEY (licence_type) REFERENCES $table_name2(id);";
         $wpdb->query($sql);
         $sql = "ALTER TABLE $table_name10 ADD CONSTRAINT IF NOT EXISTS fencers_fk3 FOREIGN KEY (last_season_paid_for) REFERENCES $table_name3(id);";
         $wpdb->query($sql);   
         $sql = "ALTER TABLE $table_name10 ADD CONSTRAINT  fencers_fk4 FOREIGN KEY (gender) REFERENCES $table_name4(id);";
         $wpdb->query($sql);  */   

    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
    }


    /**
     * Perform actions when upgrading from version X to version Y
     * See: http://plugin.michael-simpson.com/?page_id=35
     * @return void
     */
    public function upgrade() {
        $this->createUpdateTables();
    }

    public function addActionsAndFilters() {

        // Add options administration page
        // http://plugin.michael-simpson.com/?page_id=47

      
  


        add_action('admin_menu', array(&$this, 'addTopLevelMenu'));
        add_action('admin_menu', array(&$this, 'addSeasonsPage'));
        add_action('admin_menu', array(&$this, 'addLicencePage'));
      
       // Example adding a script & style just for the options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        //        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
        //            wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));
        //            wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        }


        // Add Actions & Filters
        // http://plugin.michael-simpson.com/?page_id=37


        // Adding scripts & styles to all pages
        // Examples:
        //        wp_enqueue_script('jquery');
        //        wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));


        // Register short codes
        // http://plugin.michael-simpson.com/?page_id=39


        // Register AJAX hooks
        // http://plugin.michael-simpson.com/?page_id=41

    }

     function addTopLevelMenu() {
        $this->requireExtraPluginFiles();
        $displayName = $this->getPluginDisplayName();
        add_menu_page($displayName,
                      $displayName,
                      'manage_options',
                     'IFFMembership',
                      array(&$this, 'renderFencersTablePage'));
     }

     
     function addSeasonsPage() {
         $this->requireExtraPluginFiles();
         $displayName = $this->getPluginDisplayName();
         add_submenu_page( 'IFFMembership', 'Seasons', 'Seasons',  'manage_options', 'seasonstable',  array(&$this, 'renderSeasonsTablePage') ); 
       
     }

      function addLicencePage() {
         $this->requireExtraPluginFiles();
         $displayName = $this->getPluginDisplayName();
         add_submenu_page( 'IFFMembership', 'Licence Types', 'Licence Types',  'manage_options', 'licencestable',  array(&$this, 'renderLicencesTablePage') ); 
       
     }

    

    function renderFencersTablePage(){    
    //Create an instance of our package class...
    $testListTable = new FencerTable();
    //Fetch, prepare, sort, and filter our data...
    $testListTable->renderPage();
    }

    
    function renderSeasonsTablePage(){    
    //Create an instance of our package class...
    $testListTable = new SeasonTable();
    //Fetch, prepare, sort, and filter our data...
    $testListTable->renderPage();
    }

     function renderLicencesTablePage(){    
    //Create an instance of our package class...
    $testListTable = new LicenceTable();
    //Fetch, prepare, sort, and filter our data...
    $testListTable->renderPage();
    }
}
