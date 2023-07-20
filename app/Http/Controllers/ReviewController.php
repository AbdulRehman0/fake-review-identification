<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reviews = Review::all();
        return response(["data"=>$reviews],200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // DB::table('reviews')->truncate();
        try {
            $validator = Validator::make($request->all(),[
                "review"=>"required|string|max:1000|min:5",
                "product_id"=>"required|exists:products,id",
                "rating"=>"required|digits_between:0,5"
            ]);
            if($validator->fails()){
                return response()->json(["errors"=>$validator->errors(),"msg"=>"Bad Request Error!"],400);
            }
            $user_id = Auth::user()->id;
            $review=Review::create([
                "review"=>$request->review,
                "product_id"=>$request->product_id,
                "rating"=>$request->rating,
                "user_id"=>$user_id,
                "is_fake"=>0
            ]);
            $review = Review::find($review->id);
            return response()->json(["msg"=>"Review added","data"=>$review],200);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(["msg"=>"Internal server error"],500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        return response(["data"=>$review],200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        //
    }
}
