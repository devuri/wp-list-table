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
abstract class Manager extends \WP_List_Table
{
    public const ITEM_NONCE_FIELD  = 'delete_item_nonce_field';
    public const ITEM_NONCE_ACTION = 'delete_item_action';

    /**
     * Provides results data.
     *
     * @var array
     */
    protected $results;

    /**
     * Itesm per page.
     *
     * @var int
     */
    protected $per_page;

    /**
     * create.
     *
     * @param array $table_data  array of items.
     */
    public function __construct(array $table_data, array $config)
    {
        parent::__construct(
            [
                'singular' => 'item',
                'plural'   => 'items',
                'ajax'     => false,
            ]
        );

        $this->per_page = $config['per_page'] ?? 10;

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

        usort($this->results, [ &$this, 'sort_data' ]);

        $this->set_pagination_args(
            [
                'total_items' => count($this->results),
                'per_page'    => $this->per_page,
            ]
        );

        $data = array_slice($this->results, (($current_page - 1) * $this->per_page), $this->per_page);

        $this->_column_headers = [$columns, $hidden, $sortable];
        $this->items           = $data;
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
        return ['title' => ['title', false]];
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
    public function single_row($item): void
    {
        // @phpstan-ignore-next-line
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
     * Get query.
     *
     * @param  string   $q
     * @return null|string
     */
    protected static function query(array $get, string $q): ?string
    {
        if (! isset($get[$q])) {
            return null;
        }

        $query = sanitize_text_field($get[$q]);

        if (empty($query)) {
            return null;
        }

        return $query;
    }

    /**
     * Sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    protected function sort_data(array $a, array $b)
    {
        $orderby = $this->query($_GET, 'orderby');
        $order   = $this->query($_GET, 'order');

        // If orderby is not set, use this as the sort column
        if (! $orderby) {
            $orderby = 'id';
        }

        // If order is not set, use this as the order
        if (! $order) {
            $order = 'asc';
        }


        $result = strcmp($a[$orderby], $b[$orderby]);

        if ($order === 'asc') {
            return $result;
        }

        return -$result;
    }
}
