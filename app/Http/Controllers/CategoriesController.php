<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoriesController extends Controller
{
    function index()
    {
        return view("admin.categories.index");
    }

    /**
     * Get all categories
     * 
     * @return Array
     */
    function getCategories()
    {
        $categories = Categories::get();        
        return $categories;   
    }

    /**
     * Save a new category
     * 
     * @return Array
     */
    function save(Request $data)
    {
        if(!$data->name) return response()->json(["title" => "Erro", "message" => "Preencha todos os campos"], 400);

        $save = Categories::create([
            "label"=>$data->name,
            "color"=>$data->color,
        ]);

        if (!$save) return response()->json(["title" => "Erro", "message" => "Ocorreu um erro!"], 500);

        return response()->json(["title" => "Sucesso", "message" => "Nova categoria adicionada com sucesso!"], 200);
    }

    /**
     * Removes categories
     * 
     * @return Status
     */
    function remove(Request $id)
    {
        $remove = Categories::whereId($id->id)->delete();

        if (!$remove) return response()->json(["title" => "Erro", "message" => "Ocorreu um erro a remover este item!"], 500);

        return response()->json(["title" => "Sucesso", "message" => "Categoria removida com sucesso!"], 200);
    }

}
