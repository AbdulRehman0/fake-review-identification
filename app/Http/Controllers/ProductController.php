<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reviews = Product::all();
        return response(["data"=>$reviews],200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),[
                "desc"=>"required|string|max:1000|min:5",
                "name"=>"required|string|max:30|min:3",
                "price"=>"required|min:1|max:99999999"
            ]);
            if($validator->fails()){
                return response()->json(["errors"=>$validator->errors(),"msg"=>"Bad Request Error!"],400);
            }
            $review=Product::create([
                "name"=>$request->name,
                "desc"=>$request->desc,
                "price"=>$request->price,
            ]);
            return response()->json(["msg"=>"Review added","data"=>$review],200);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(["msg"=>"Internal server error"],500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response(["data"=>$product],200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
