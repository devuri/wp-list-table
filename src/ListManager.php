<?php

/*
 * This file is part of the Simple List Manager for WordPress.
 *
 * (c) Uriel Wilson <uriel@urielwilson.com>
 *
 * Please see the LICENSE file that was distributed with this source code
 * for full copyright and license information.
 */
namespace DevUri\ListTable;

/**
 * Table list for Plugin.
 *
 * Based on the WordPress core class WP_List_Table.
 *
 * Note: WP_List_Table class access is marked as private.
 * That means it is not intended for use by plugin and theme
 * developers as it is subject to change without
 * warning in any future WordPress release.
 *
 * Based on https://gist.github.com/paulund/7659452
 *
 * @link https://developer.wordpress.org/reference/classes/wp_list_table/
 */
abstract class ListManager extends \WP_List_Table
{
	public const ITEM_NONCE_FIELD = 'delete_item_nonce_field';
	public const ITEM_NONCE_ACTION = 'delete_item_action';

	/**
	 * Provides results data.
	 *
	 * @var array
	 */
	protected $results;

	/**
	 * create.
	 *
	 * @param array $table_data  array of items.
	 */
	public function __construct(array $table_data)
	{
		parent::__construct(
            [
                'singular' => 'item',
                'plural'   => 'items',
                'ajax'     => false,
            ]
        );

		// array of table data.
		$this->results = $table_data;
	}

	/**
     * Prepare the items for the table to process
     *
     * @return void
     */
    public function prepare_items()
    {
		$columns      = $this->get_columns();
		$hidden       = $this->get_hidden_columns();
		$sortable     = $this->get_sortable_columns();
		$current_page = $this->get_pagenum();
		$per_page     = 10;

        usort( $this->results, array( &$this, 'sort_data' ) );

		$this->set_pagination_args(
			[
				'total_items' => count($this->results),
				'per_page'    => $per_page,
			]
		);

        $data = array_slice($this->results,(($current_page-1)*$per_page),$per_page);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

	/**
     * Gets a list of columns.
     *
     * The format is:
     * - `'internal-name' => 'Title'`
     *
     * @return array
     */
    public function get_columns(): array
    {
        die('function WP_List_Table::get_columns() must be overridden in a subclass.');
    }

    public function get_hidden_columns(): array
    {
        return [];
    }

    public function get_sortable_columns()
    {
        return array('title' => array('title', false));
    }

	/**
     * Generates content for a single row of the table.
     *
     * @since 3.1.0
     *
     * @overrides https://developer.wordpress.org/reference/classes/wp_list_table/
     *
     * @param object|array $item The current item
     */
    public function single_row($item)
    {
        $id = $item['id'] ?? 0;
        echo '<tr class="row-item-'.$id.'">';
        $this->single_row_columns($item);
        echo '</tr>';
    }

	/**
     * Define what data to show on each column of the table
     *
     * @param Array  $item        Data.
     * @param String $column_name Current column name.
     *
     * @return string|void
     */
    public function column_default($item, $column_name)
    {
        $columns = array_keys($this->get_columns());
        foreach ($columns as $key) {
            if ($column_name === $key) {
                if (! isset($item[ $column_name ])) {
                    return;
                }
                return $item[ $column_name ];
            }
        }
    }

    /**
     * Sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'chassis';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }


        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
}
