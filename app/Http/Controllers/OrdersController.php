<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Items;
use App\Models\Modifiers;
use App\Models\OrderItems;
use App\Models\OrderItemsModifiers;
use App\Models\Orders;
use App\Models\Sessions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class OrdersController extends Controller
{
    function index()
    {
        if (!session()->get("sess")) return redirect("/");

        // Get all categories
        $categories = Categories::get();        
        // Get all items
        $items = Items::get();
        return View("orders")->with("items", $items)->with("categories", $categories);
    }

    /**
     * Filter the show items
     * 
     * @return items
     */
    function filterItems(Request $filters)
    {
        if (!session()->get("sess")) return redirect("/");

        $items = Items::where("name", "like", "%" . $filters->search . "%");

        // If category is specified filter by that also
        if($filters->category != "all") {
            $items->where("category_id", $filters->category);
        }

        $items = $items->get();
        return response()->json($items);
    }

    /**
     * Closes current session
     * 
     */
    function closeSession()
    {
        Sessions::whereId(session()->get("sess.id"))->update([
            "end" => date("Y-m-d h:i:s"),
        ]);
        session()->flush();
        return response()->json(["title" => "Sucesso", "message" => "Sessão fechada!"]);
    }

    /**
     * Adds an item to the user session and saves it there until the user either confirms or resets
     * 
     */
    function addItem(Request $data)
    {
        $item = Items::whereId($data->id)->get()->first();

        $mod_price = 0;
        if ($data->modifier != 0 && $data->modifier != null) {
            $mod = Modifiers::whereId($data->modifier)->get()->first();
            $mod_price = $mod->price;
        }

        $session_data = [
            "id" => $data->id,
            "name" => $item->name,
            "price" => $item->price,
            "quantity" => $data->quantity,
            "modifier" => $data->modifier,
            "modifier_price" => $mod_price,
            "modifier_quantity" => $data->quantityMod,
        ];

        if ($data->isEdit != null) {
            $items = session()->get("items");
            $items[$data->isEdit] = $session_data;
            session()->put("items", $items);
            return response()->json(["title" => "Sucesso", "message" => "Editado com sucesso"]);
        }
        session()->push("items", $session_data);

        return response()->json(["title" => "Sucesso", "message" => "Adicionado com sucesso"]);
    }

    /**
     * Removes an item from session
     * 
     * @return status
     */
    function removeOverviewItem(Request $id)
    {
        $items = session()->get("items", []);
        $idToRemove = $id->id;
        if (isset($items[$idToRemove])) {
            unset($items[$idToRemove]);
        }
        session()->put("items", $items);
        return response()->json(["title" => "Sucesso", "message" => "Item removido"]);
    }

    /**
     * Confirm an order
     * 
     * @return status
     */
    function confirmOrder(Request $data)
    {
        if (count(session()->get("items")) <= 0) return response()->json(["title" => "Erro", "message" => "Não selecionou nenhum item..."], 400);

        $total_price = 0;

        $order = Orders::create([
            "date" => date("Y-m-d h:i:s"),
            "session_id" => session()->get("sess.id"),
        ]);

        if (!$order) return response()->json(["title" => "Erro", "message" => "Ocorreu um erro a guardar o pedido"], 500);

        foreach (session()->get("items") as $item) {
            $it = Items::whereId($item['id'])->get()->first();
            $order_item = OrderItems::create([
                "order_id" => $order->id,
                "item_id" => $item['id'],
                "quantity" => $item['quantity'],
                "price_snapshot" => $item['price'],
                "cost_snapshot" => $it['cost']
            ]);

            if (!$order_item) return response()->json(["title" => "Erro", "message" => "Ocorreu um erro a guardar os items"], 500);

            $total_price += $item['quantity'] * $item['price'];

            // if item has a modifier
            if ($item['modifier'] != null) {
                $mod = Modifiers::whereId($item['modifier'])->get()->first();
                $order_item_modifier = OrderItemsModifiers::create([
                    "order_item_id" => $order_item->id,
                    "modifier_id" => $item['modifier'],
                    "quantity" => $item['modifier_quantity'],
                    "price_snapshot" => $item['modifier_price'],
                    "cost_snapshot" => $mod['cost']
                ]);

                if (!$order_item_modifier) return response()->json(["title" => "Erro", "message" => "Ocorreu um erro a adicionar o modificador"], 500);

                $total_price += $item['modifier_quantity'] * $item['modifier_price'];
            }
        }

        if ($data->client_value != "") {
            $change = $data->client_value - $total_price;
        } else {
            $change = "NO_VALUE";
        }

        session()->remove("items");

        return response()->json(["title" => "Sucesso", "message" => "Pedido guardado com sucesso!", "change" => $change], 200);
    }

    /**
     * Receives an id and gets the info in the session about that item
     * 
     */
    function getItemData(Request $id)
    {
        return response()->json(session()->get("items")[$id->id]);
    }

    /**
     * Resets items added to cart
     * 
     */
    function resetItems()
    {
        session()->remove("items");
    }
}
