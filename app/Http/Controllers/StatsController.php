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
        $ss = Sessions::whereId($params->route('id'))->get()->first();

        // Financial Stats
        $money_stats = Orders::selectRaw(
            DB::raw('
            ROUND(SUM((order_items.quantity*order_items.price_snapshot))+SUM((order_items_modifiers.quantity*order_items_modifiers.price_snapshot)), 2) as bruto,
            ROUND(SUM((order_items.quantity*order_items.cost_snapshot))+SUM((order_items_modifiers.quantity*order_items_modifiers.cost_snapshot)), 2) as despesas,
            ROUND((SUM((order_items.quantity*order_items.price_snapshot))+SUM((order_items_modifiers.quantity*order_items_modifiers.price_snapshot)))-(SUM((order_items.quantity*order_items.cost_snapshot))+SUM((order_items_modifiers.quantity*order_items_modifiers.cost_snapshot))), 2) as liquido
            ')
        )
            ->join("order_items", "order_items.order_id", "=", "orders.id")
            ->leftJoin("order_items_modifiers", "order_items_modifiers.order_item_id", "=", "order_items.id")
            ->where("orders.session_id", $ss['id'])->get()->first();

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
                       WHERE orders.session_id = ' . $ss['id'] . ' GROUP BY order_items.item_id) AS order_items'),
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
            ->leftJoin("order_items_modifiers", "order_items_modifiers.order_item_id", "=", "order_items.id")
            ->where("orders.session_id", $ss['id'])
            ->groupBy("categories.id")
            ->get();

        // Merge the two sales per category arrays
        foreach($sales_per_category as $key=>$val){
            foreach($sales_per_category_2 as $key2=>$val2){                
                if($val["id"]==$val2['id']){
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
        ->leftJoin("order_items_modifiers", "order_items_modifiers.order_item_id", "=", "order_items.id")
        ->where("orders.session_id", $ss['id'])
        ->groupBy("items.id")
        ->orderBy("total", "DESC")
        ->get();
        
        // Total orders
        $total_orders = Orders::select(
            DB::raw("count(id) as total")
        )
        ->where("orders.session_id", $ss['id'])
        ->get()->first();
        

        return view("statistics.index")
            ->with("ss", $ss)
            ->with("money_stats", $money_stats)
            ->with("category_sales", $sales_per_category)
            ->with("item_sales", $sales_per_item)
            ->with("total", $total_orders->total);
    }
}
