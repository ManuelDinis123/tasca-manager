<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Categories;
use App\Models\Items;
use App\Models\Modifiers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ItemsController extends Controller
{
    function index()
    {
        return view("admin.items.index")->with("options", $this->get_category_options());
    }

    /**
     * Goes to the edit page
     * 
     * @return View
     */
    function edit_page(Request $params)
    {
        // Get item id
        $id = $params->route("id");

        $item = Items::whereId($id)->get()->first();
        $modifiers = Modifiers::where("item_id", $id)->get();

        return view("admin.items.edit")
            ->with("item", $item)
            ->with("modifiers", $modifiers)
            ->with("categories", $this->get_category_options());
    }

    /**
     * Gets all items raw
     * 
     * @return Response
     */
    function get()
    {
        $items = Items::get();

        return $items;
    }

    /**
     * Gets all items and treats the data to display to the user
     * 
     * @return Response
     */
    function display()
    {
        $items = Items::get();
        foreach ($items as $key => $item) {
            $items[$key]['price'] = $item['price'] . '€';
        }
        return $items;
    }

    /**
     * Saves a new item
     * 
     * @param data
     * @return status
     */
    function save(Request $data)
    {
        if (AppHelper::hasEmpty([$data->name, $data->price, $data->img, $data->category]))
            return response()->json(["title" => "Erro", "message" => "Preencha todos os campos"], 400);

        $toSave = [
            "name" => $data->name,
            "price" => $data->price,
            "category_id" => $data->category,
            "img" => $data->img,
        ];

        // If cost is set then add it to the array of data to save
        if ($data->cost) {
            $toSave["cost"] = $data->cost;
        }

        $saveItem = Items::create($toSave);

        if (!$saveItem) return response()->json(["title" => "Erro", "message" => "Ocorreu um erro!"], 500);

        return response()->json(["title" => "Sucesso", "message" => "Novo item adicionado com sucesso!", "id" => $saveItem->id], 200);
    }

    /**
     * Edits an item
     * 
     * @param data
     * @return status
     */
    function update(Request $data)
    {
        if (AppHelper::hasEmpty([$data->name, $data->price, $data->category]))
            return response()->json(["title" => "Erro", "message" => "Preencha todos os campos"], 400);

        $toSave = [
            "name" => $data->name,
            "price" => $data->price,
            "category_id" => $data->category,
        ];

        // If cost is set then add it to the array of data to save
        if ($data->cost) {
            $toSave["cost"] = $data->cost;
        }
        // If image is not null save the new one
        if ($data->img) {
            $toSave["img"] = $data->img;
        }

        $saveItem = Items::whereId($data->id)->update($toSave);

        if (!$saveItem) return response()->json(["title" => "Erro", "message" => "Ocorreu um erro!"], 500);

        return response()->json(["title" => "Sucesso", "message" => "Item editado com sucesso!"], 200);
    }

    /**
     * Deletes an item
     * 
     * @param id
     * @return status
     */
    function delete(Request $id)
    {
        // Delete modifires if they exist
        $hasMods = Modifiers::where("item_id", $id->id)->count();
        if ($hasMods > 0) {
            Modifiers::where("item_id", $id->id)->delete();
        }

        $deleting = Items::whereId($id->id)->delete();

        if (!$deleting) return response()->json(["title" => "Erro", "message" => "Ocorreu um erro!"], 500);

        return response()->json(["title" => "Sucesso", "message" => "Item removido com sucesso!"], 200);
    }

    /**
     * Get modifiers for a given item
     * 
     * @return mods
     */
    function get_mods(Request $id)
    {
        $mods = Modifiers::where("item_id", $id->id)->get();

        return $mods;
    }

    /**
     * Saves modifier for a given item
     * 
     * @return status
     */
    function save_mod(Request $data)
    {
        if (AppHelper::hasEmpty([$data->name, $data->price, $data->img]))
            return response()->json(["title" => "Erro", "message" => "Preencha todos os campos"], 400);

        $toSave = [
            "name" => $data->name,
            "price" => $data->price,
            "img" => $data->img,
            "item_id" => $data->item_id
        ];

        // If cost is set then add it to the array of data to save
        if ($data->cost) {
            $toSave["cost"] = $data->cost;
        }

        $saveItem = Modifiers::create($toSave);

        if (!$saveItem) return response()->json(["title" => "Erro", "message" => "Ocorreu um erro!"], 500);

        return response()->json(["title" => "Sucesso", "message" => "Novo modificador adicionado com sucesso!"], 200);
    }

    /**
     * Edits an item
     * 
     * @param data
     * @return status
     */
    function updateMods(Request $data)
    {
        if (AppHelper::hasEmpty([$data->name, $data->price]))
            return response()->json(["title" => "Erro", "message" => "Preencha todos os campos"], 400);

        $toSave = [
            "name" => $data->name,
            "price" => $data->price,            
        ];

        // If cost is set then add it to the array of data to save
        if ($data->cost) {
            $toSave["cost"] = $data->cost;
        }
        // If image is not null save the new one
        if ($data->img) {
            $toSave["img"] = $data->img;
        }

        $saveItem = Modifiers::whereId($data->id)->update($toSave);

        if (!$saveItem) return response()->json(["title" => "Erro", "message" => "Ocorreu um erro!"], 500);

        return response()->json(["title" => "Sucesso", "message" => "Modificador editado com sucesso!"], 200);
    }

    /**
     * Deletes a modifier
     * 
     * @return status
     */
    function deleteModifier(Request $id)
    {
        $delete = Modifiers::whereId($id->id)->delete();

        if (!$delete) return response()->json(["title" => "Erro", "message" => "Ocorreu um erro!"], 500);

        return response()->json(["title" => "Sucesso", "message" => "Modificador removido com sucesso!"], 200);
    }

    // Gets the categories and returns an array friendly to a select
    function get_category_options()
    {
        $categories = Categories::get();

        // Create an array to send to the select
        $options = [];
        foreach ($categories as  $category) {
            $options[] = [
                "value" => $category['id'],
                "label" => $category['label'],
            ];
        }

        return $options;
    }
}
