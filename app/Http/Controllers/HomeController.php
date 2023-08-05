<?php

namespace App\Http\Controllers;

use App\Models\Sessions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\FlareClient\View;

class HomeController extends Controller
{
    function index()
    {
        $allSessions = Sessions::get();
        return View("home")->with("sess", $allSessions);
    }

    /**
     * Starts a new session
     * /startsession
     * 
     * @param label
     * @return status
     */
    function start_session(Request $label)
    {
        // Save session to DB
        $sess = Sessions::create([
            "label" => $label->label,
            "start" => date("Y-m-d h:i:s")
        ]);

        if(!$sess) return response()->json(["title" => "Erro", "message" => "Ocorreu um Erro"], 500);

        // Save session to user session
        $session_data = [
            "id" => $sess->id,
            "label" => $label->label
        ];

        session(["sess" => $session_data]);

        return response()->json(["title" => "Sucesso", "message" => "Sessão iniciada com sucesso!"], 200);
    }
}
