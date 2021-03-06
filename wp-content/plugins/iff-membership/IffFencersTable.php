<?php

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}




/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 * Our theme for this list table is going to be movies.
 */
class FencerTable extends WP_List_Table {
    
    
   


    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'fencer',     //singular name of the listed records
            'plural'    => 'fencers',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }


    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'name':
            case 'status':
            case 'licence_type':
            case 'club':
            case 'gender':
            case 'last_paid_season':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }


    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_name($item){
        
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&fencer=%s">Edit</a>',$_REQUEST['page'],'edit',$item['id']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&fencer=%s">Delete</a>',$_REQUEST['page'],'delete',$item['id']),
        );
        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(IFF:%2$s)</span>%3$s',
            /*$1%s*/ $item['name'],
            /*$2%s*/ $item['license_number'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }


    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }


    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'name'     => 'Name',
            'status'    => 'Status',
            'licence_type'  => 'Licence',
            'club' => 'Club',
            'gender' => 'Gender',
            'last_paid_season' => 'Last Paid'
        );
        return $columns;
    }


    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'name'     => array('name',false),     //true means it's already sorted
            'status'    => array('status',false),
            'licence_type'  => array('licence_type',false),
            'club'  => array('club',false),
            'gender'  => array('gender',false),
            'last_paid_season'  => array('last_paid_season',false)
        );
        return $sortable_columns;
    }


    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }


    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            $numSelected = $_GET["fencer"];
            wp_die("". count($numSelected)." Items deleted (or they would be if we had items to delete)!");
        }
        
    }


    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 5;
        
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();
        
        
        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example 
         * package slightly different than one you might build on your own. In 
         * this example, we'll be using array manipulation to sort and paginate 
         * our data. In a real-world implementation, you will probably want to 
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
       $whereClause = $this->whereQuery();
       $sql = "select wp_iffmembership_plugin_fencers.id as 'id',
                CONCAT_WS(' ',wp_iffmembership_plugin_fencers.first_name,wp_iffmembership_plugin_fencers.last_name) as 'name',
                wp_iffmembership_plugin_licence_type.display_name as 'licence_type',
                wp_iffmembership_plugin_fencer_status.display_name as 'status' ,
                wp_iffmembership_plugin_season.display_name as 'last_paid_season',
                club,
                license_number,
                wp_iffmembership_plugin_gender.display_name as 'gender'

                from wp_iffmembership_plugin_fencers 

                JOIN wp_iffmembership_plugin_licence_type
                ON wp_iffmembership_plugin_fencers.licence_type = wp_iffmembership_plugin_licence_type.id

                JOIN wp_iffmembership_plugin_fencer_status
                ON wp_iffmembership_plugin_fencers.status = wp_iffmembership_plugin_fencer_status.id

                JOIN wp_iffmembership_plugin_season
                ON wp_iffmembership_plugin_fencers.last_season_paid_for = wp_iffmembership_plugin_season.id
                
                JOIN wp_iffmembership_plugin_gender
                ON wp_iffmembership_plugin_fencers.gender = wp_iffmembership_plugin_gender.id
                " . $whereClause;


       
       
       $data = $wpdb->get_results($sql,ARRAY_A);
    
        
       
                
        
        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         * 
         * In a real-world situation involving a database, you would probably want 
         * to handle sorting by passing the 'orderby' and 'order' values directly 
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'name'; //If no sort, default to name
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
        
        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         * 
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         * 
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/
        
                
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }

    /**
 * Add extra markup in the toolbars before or after the list
 * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
 */
function extra_tablenav( $which ) {

    global $wpdb; 


    $sql = "select id, display_name from wp_iffmembership_plugin_fencer_status order by display_name;";
    $statusList = $wpdb->get_results($sql,ARRAY_A);

    $sql = "select id, display_name from wp_iffmembership_plugin_season order by display_name;";
    $lastPaidList = $wpdb->get_results($sql,ARRAY_A);

    $sql = "select id, display_name from wp_iffmembership_plugin_licence_type order by display_name;";
    $licenceList = $wpdb->get_results($sql,ARRAY_A);

     $sql = "select id, display_name from wp_iffmembership_plugin_gender order by display_name;";
     $genderList = $wpdb->get_results($sql,ARRAY_A);
    
  

    if ( $which == "top" ){
		//The code that goes before the table is here
		$this->statusDropdown($statusList);
        $this->licenceDropdown($licenceList);
        $this->lastPaidDropdown($lastPaidList);
        $this->genderDropdown($genderList);
      
	}
	if ( $which == "bottom" ){
		//The code that goes after the table is there
		echo"Hi, I'm after the table";
	}
}

function statusDropdown($statusList)
{
    $_filter =""; 
   
    if(isset($_GET["status"]))
    {
        $_filter = $_GET["status"];
    }
    
  
      echo'<div class="alignleft actions"><select name="status"><option value="-1">Fencer Status</option>';
	    
      foreach($statusList as $value):
          $isSelected = $this->markSelected($value["id"], $_filter);
          echo '<option '.$isSelected.' value="'.$value["id"].'">'.$value["display_name"].'</option>'; //close your tags!!
      endforeach;  
                
      echo'</select></div>';
}

function licenceDropdown($licenceList)
{

    $_filter =""; 
   
    if(isset($_GET["licence"]))
    {
        $_filter = $_GET["licence"];
    }

    echo'<div class="alignleft actions "><select name="licence"><option value="-1" >Licence Type</option>';
	    
      foreach($licenceList as $value):
          $isSelected = $this->markSelected($value["id"], $_filter);
        echo '<option '.$isSelected.' value="'.$value["id"].'">'.$value["display_name"].'</option>'; //close your tags!!
      endforeach;  
                
        echo'</select></div>';
}

function lastPaidDropdown($lastPaidList)
{
     $_filter =""; 
   
    if(isset($_GET["lastpaid"]))
    {
        $_filter = $_GET["lastpaid"];
    }

    echo'<div class="alignleft actions"><select name="lastpaid"><option value="-1">Last Paid</option>';
	    
      foreach($lastPaidList as $value):
        $isSelected = $this->markSelected($value["id"], $_filter);
        echo '<option '.$isSelected.' value="'.$value["id"].'">'.$value["display_name"].'</option>'; //close your tags!!
      endforeach;  
                
        echo'</select></div>';
}

function genderDropdown($genderList)
{
     $_filter =""; 
   
    if(isset($_GET["gender"]))
    {
        $_filter = $_GET["gender"];
    }

    echo'<div class="alignleft actions"><select name="gender"><option value="-1">Gender</option>';
	    
      foreach($genderList as $value):
        $isSelected = $this->markSelected($value["id"], $_filter);
        echo '<option '.$isSelected.' value="'.$value["id"].'">'.$value["display_name"].'</option>'; //close your tags!!
      endforeach;  
                
        echo'</select></div>';
}

function markSelected($id, $selected)
{
    if($id == $selected)
    {
        return 'selected="selected"';
    }
    else
    {
        return '';
    }
}

function whereQuery()
{
  
   $vars;   
   $query = "";
       
   if(isset($_GET["s"]))
   {
        $_filter = $_GET["s"];
        $queries;
     
        if ($_filter)
        {
            $queries[] = "club LIKE '%" .$_filter. "%'";
            $queries[] = "first_name LIKE '%" .$_filter. "%'";
            $queries[] = "last_name LIKE '%" .$_filter. "%'";
        }  
        
         if (!empty($queries)) 
         {
             $vars[] = '(' . implode($queries, ' OR ') . ')';
         }    
       
   }

    if(isset($_GET["lastpaid"]))
    {
        $_filter = $_GET["lastpaid"];
        if( $_filter != -1)
        {
            $vars[] = '(last_season_paid_for = '.$_filter.')';
        }
    }

    if(isset($_GET["licence"]))
    {
        $_filter = $_GET["licence"];
        if( $_filter != -1)
        {
            $vars[] = '(licence_type = '.$_filter.')';
        }
    }

    if(isset($_GET["status"]))
    {
        $_filter = $_GET["status"];
        if( $_filter != -1)
        {
            $vars[] = '(status = '.$_filter.')';
        }
    }


   if (!empty($vars)) 
   {
    $query .= ' WHERE ' . implode(' AND ', $vars);
   }
  
   return $query;


}

/**
 * Generates the table navigation above or bellow the table and removes the
 * _wp_http_referrer and _wpnonce because it generates a error about URL too large
 * 
 * @param string $which 
 * @return void
 */
function display_tablenav( $which ) 
{
    ?>
    <div class="tablenav <?php echo esc_attr( $which ); ?>">

        <div class="alignleft actions">
            <?php $this->bulk_actions(); ?>
        </div>
        <?php
        $this->extra_tablenav( $which );
        $this->pagination( $which );
        ?>
        <br class="clear" />
    </div>
    <?php
}


function renderPage()
{
    $this->prepare_items();
    
    ?>
    <div class="wrap">
        
        <div id="icon-users" class="icon32"><br/></div>
        <h2>Fencers <a href="#" class="add-new-h2">Add New</a></h2>        
              
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="fencers-filter" method="get">
              <?php $this->search_box( "Search", "searchtext" ) ?>
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $this->display() ?>
        </form>
        
    </div>
    <?php
}




}

?>