<?php
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if (!class_exists('Areago_Paseos_List_Table')){

	class Areago_Paseos_List_Table extends WP_List_Table{
		
		/** ************************************************************************
		 * REQUIRED. Set up a constructor that references the parent constructor. We
		 * use the parent reference to set some default configs.
		 ***************************************************************************/
		function __construct(){
			global $status, $page;
		
			//Set parent defaults
			parent::__construct( array(
					'singular'  => 'walk',     //singular name of the listed records
					'plural'    => 'walks',    //plural name of the listed records
					'ajax'      => false        //does this table support ajax?
			) );
		
		} //__construct
		
		function prepare_items() {
		
			/**
			 * First, lets decide how many records per page to show
			 */
			$per_page = 5;
		
			$columns = $this->get_columns();
			$hidden = array('id');
			$sortable = $this->get_sortable_columns();
		
		
			$this->_column_headers = array($columns, $hidden, $sortable);
		
		
			/**
			 * Instead of querying a database, we're going to fetch the example data
			 * property we created for use in this plugin. This makes this example
			 * package slightly different than one you might build on your own. In
			 * this example, we'll be using array manipulation to sort and paginate
			 * our data. In a real-world implementation, you will probably want to
			 * use sort and pagination data to build a custom query instead, as you'll
			 * be able to use your precisely-queried data immediately.
			*/
		//	$data = $this->example_data;
			$db_helper = new Areago_DB_Helper();
			$data = $db_helper->get_array_walks();
			
		
			/**
			 * This checks for sorting input and sorts the data in our array accordingly.
			 *
			 * In a real-world situation involving a database, you would probably want
			 * to handle sorting by passing the 'orderby' and 'order' values directly
			 * to a custom query. The returned data will be pre-sorted, and this array
			 * sorting technique would be unnecessary.
			 */
			function usort_reorder($a,$b){
				$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
				$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
				$result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
				return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
			}
			//usort($data, 'usort_reorder');
		
		
			/***********************************************************************
			 * ---------------------------------------------------------------------
			* vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
			*
			* In a real-world situation, this is where you would place your query.
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
		}//prepare_items
		
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
					'id'			=> 'ID',				
					'name'	     	=> 'Title',
					'description'	=> 'Description',
					'recordings'	=> 'Number of recordings'
			);
			return $columns;
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
				case 'id':
				case 'description':
				case 'recordings':
					return $item[$column_name];
				default:
					return print_r($item,true); //Show the whole array for troubleshooting purposes
			}
		}
		
		function column_name($item){
			$actions = array(
					'edit'      => sprintf('<a href="?page=%s&action=%s&book=%s">Edit</a>',$_REQUEST['page'],'edit',$item['id']),
					'delete'    => sprintf('<a href="?page=%s&action=%s&book=%s">Delete</a>',$_REQUEST['page'],'delete',$item['id']),
			);		
			return sprintf('%1$s %2$s', $item['name'], $this->row_actions($actions,true) );
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
					'name'     => array('name',true),     //true means its already sorted
					'recordings'    => array('recordings',false)
			);
			return $sortable_columns;
		}
		
		function no_items() {
			echo "There are no walks.";
		}
		

		
	}//class Areago_List_Table

}