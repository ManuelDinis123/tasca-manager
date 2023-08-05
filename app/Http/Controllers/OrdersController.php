<?php

namespace App\Http\Controllers;

use App\Models\Items;
use App\Models\Sessions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrdersController extends Controller
{
    function index()
    {        
        if(!session()->get("sess")) return redirect("/");

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
            "end"=>date("Y-m-d h:i:s"),
        ]);
        session()->flush();
        return response()->json(["title"=>"Sucesso", "message"=>"Sessão fechada!"]);
    }

    /**
     * Adds an item to the user session and saves it there until the user either confirms or resets
     * 
     */
    function addItem(Request $data)
    {
        $session_data = [
            "id" => $data->id,
            "name" => $data->name,
            "price" => $data->price,
            "modifier" => $data->modifier,
        ];

        if(session()->get("items") != null) {
            session(["items" => array_merge(session()->get("items"), $session_data)]);
        }

        session(["items" => $session_data]);

        return response()->json(["title"=>"Sucesso"]);
    }
}
