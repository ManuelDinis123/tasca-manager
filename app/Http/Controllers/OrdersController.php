<?php

namespace App\Http\Controllers;

use App\Models\Items;
use App\Models\Modifiers;
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
        // Get all items
        $items = Items::get();        
        return View("orders")->with("items", $items);
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
        if(isset($data->modifier)){
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
        ];

        session()->push("items", $session_data);

        return response()->json(["title" => "Sucesso"]);
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
