<?php

namespace App\Http\Controllers;

use App\Prod;
use Illuminate\Http\Request;

class ProdsController extends Controller
{
    public function index()
    {
        $prods = Prod::paginate(9);
        return new ProdCollection($prods);
    }

    public function show($id)
    {
        $prod = Prod::find($id);
        if (!$prod) {
            return response()->json(['status' => false, 'status_code' => '401']);
        }
        return new \App\Http\Resources\Order($prod);
    }
}
