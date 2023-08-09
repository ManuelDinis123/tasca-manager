<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Items;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\Sessions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatsController extends Controller
{
    /**
     * Calculate stats and send them to the page
     * 
     */
    function index(Request $params)
    {

        if ($params->route('id') != "all") {
            $ss = Sessions::whereId($params->route('id'))->get()->first();
        }

        // Financial Stats
        $money_stats = Orders::selectRaw(
            DB::raw('
            ROUND(SUM((order_items.quantity*order_items.price_snapshot))+SUM((order_items_modifiers.quantity*order_items_modifiers.price_snapshot)), 2) as bruto,
            ROUND(SUM((order_items.quantity*order_items.cost_snapshot))+SUM((order_items_modifiers.quantity*order_items_modifiers.cost_snapshot)), 2) as despesas,
            ROUND((SUM((order_items.quantity*order_items.price_snapshot))+SUM((order_items_modifiers.quantity*order_items_modifiers.price_snapshot)))-(SUM((order_items.quantity*order_items.cost_snapshot))+SUM((order_items_modifiers.quantity*order_items_modifiers.cost_snapshot))), 2) as liquido
            ')
        )
            ->join("order_items", "order_items.order_id", "=", "orders.id")
            ->leftJoin("order_items_modifiers", "order_items_modifiers.order_item_id", "=", "order_items.id");

        if (isset($ss)) {
            $money_stats->where("orders.session_id", $ss['id']);
        }

        $money_stats = $money_stats->get()->first();

        $sales_per_category_where = '';
        if (isset($ss)) {
            $sales_per_category_where = 'WHERE orders.session_id = ' . $ss['id'];
        }

        // Sales per Category (quantity of sales)
        $sales_per_category = Categories::select(
            'categories.id',
            'categories.label',
            'categories.color',
            DB::raw('IFNULL(SUM(COALESCE(order_items.quantity, 0)), 0) as sales'),
            DB::raw('0 as lucro'),
        )
            ->leftJoin('items', 'items.category_id', '=', 'categories.id')
            ->leftJoin(
                DB::raw('(SELECT order_items.item_id, SUM(order_items.quantity) AS quantity
                       FROM order_items
                       INNER JOIN orders ON orders.id = order_items.order_id
                       ' . $sales_per_category_where . ' GROUP BY order_items.item_id) AS order_items'),
                'items.id',
                '=',
                'order_items.item_id'
            )
            ->groupBy('categories.id')
            ->get();

        // Sales per Category (Money)
        $sales_per_category_2 = Categories::select(
            'categories.id',
            DB::raw('ROUND(( SUM((order_items.quantity*order_items.price_snapshot)) + ifnull(SUM((order_items_modifiers.quantity*order_items_modifiers.price_snapshot) ),0))) -  (SUM((order_items.quantity*order_items.cost_snapshot))+ ifnull(SUM((order_items_modifiers.quantity*order_items_modifiers.cost_snapshot)),0)  ) as lucro')
        )
            ->leftJoin("items", "items.category_id", "=", "categories.id")
            ->leftJoin("order_items", "order_items.item_id", "=", "items.id")
            ->leftJoin("orders", "orders.id", "=", "order_items.order_id")
            ->leftJoin("order_items_modifiers", "order_items_modifiers.order_item_id", "=", "order_items.id");


        if (isset($ss)) {
            $sales_per_category_2->where("orders.session_id", $ss['id']);
        }

        $sales_per_category_2 = $sales_per_category_2->groupBy("categories.id")->get();

        // Merge the two sales per category arrays
        foreach ($sales_per_category as $key => $val) {
            foreach ($sales_per_category_2 as $key2 => $val2) {
                if ($val["id"] == $val2['id']) {
                    $sales_per_category[$key]['lucro'] = $val2['lucro'];
                }
            }
        }

        // Sales per item (quantity)
        $sales_per_item = Items::select(
            "items.id",
            "items.name",
            "categories.color",
            DB::raw("sum(order_items.quantity) as total"),
            DB::raw("ROUND(( SUM((order_items.quantity*order_items.price_snapshot)) + ifnull(SUM((order_items_modifiers.quantity*order_items_modifiers.price_snapshot) ),0))) -  (SUM((order_items.quantity*order_items.cost_snapshot))+ ifnull(SUM((order_items_modifiers.quantity*order_items_modifiers.cost_snapshot)),0)  ) as lucro"),
        )
            ->join("categories", "categories.id", "=", "items.category_id")
            ->leftJoin("order_items", "order_items.item_id", "=", "items.id")
            ->leftJoin("orders", "orders.id", "=", "order_items.order_id")
            ->leftJoin("order_items_modifiers", "order_items_modifiers.order_item_id", "=", "order_items.id");

        if (isset($ss)) {
            $sales_per_item->where("orders.session_id", $ss['id']);
        }

        $sales_per_item = $sales_per_item->groupBy("items.id")
            ->orderBy("total", "DESC")
            ->get();

        // Total orders
        $total_orders = Orders::select(
            DB::raw("count(id) as total")
        );

        if (isset($ss)) {
            $total_orders->where("orders.session_id", $ss['id']);
        }

        $total_orders = $total_orders->get()->first();

        // per session
        $per_session=null;
        if ($params->route('id') == "all") {
            $per_session = Sessions::select(
                "sessions.label",
                DB::raw('COUNT(orders.id) as orders_count'),
                DB::raw('ROUND(SUM((order_items.quantity*order_items.price_snapshot))+SUM((order_items_modifiers.quantity*order_items_modifiers.price_snapshot)), 2) as bruto'),
                DB::raw('ROUND(SUM((order_items.quantity*order_items.cost_snapshot))+SUM((order_items_modifiers.quantity*order_items_modifiers.cost_snapshot)), 2) as despesas'),
                DB::raw('ROUND((SUM((order_items.quantity*order_items.price_snapshot))+SUM((order_items_modifiers.quantity*order_items_modifiers.price_snapshot)))-(SUM((order_items.quantity*order_items.cost_snapshot))+SUM((order_items_modifiers.quantity*order_items_modifiers.cost_snapshot))), 2) as liquido')
            )
                ->join('orders', 'orders.session_id', '=', 'sessions.id')
                ->join('order_items', 'order_items.order_id', '=', 'orders.id')
                ->leftJoin('order_items_modifiers', 'order_items_modifiers.order_item_id', '=', 'order_items.id')
                ->groupBy('sessions.id')
                ->get();
        }                

        return view("statistics.index")
            ->with("ss", (isset($ss) ? $ss : "NO_SESSION"))
            ->with("money_stats", $money_stats)
            ->with("category_sales", $sales_per_category)
            ->with("item_sales", $sales_per_item)
            ->with("total", $total_orders->total)
            ->with("per_session", $per_session);
    }
}
