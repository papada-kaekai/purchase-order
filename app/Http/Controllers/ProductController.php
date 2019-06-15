<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\UserController as UserController;
use App\Models\Product;
use Validator;

use MongoDB\BSON\ObjectID;


class ProductController extends UserController
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::all();

        return $this->sendResponse($products->toArray(), 'Products retrieved successfully.');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();


        $validator = Validator::make($input, [
            'name' => 'required',
            'price' => 'required',
            'unit' => 'required',
            'category' => 'required',
        ]);


        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $input['code'] = $this->generateProductCode();
        $input['price'] = (float)$input['price'];
        $input['active'] = (bool)$input['active'];
        $input['created_by'] = new ObjectID($this->userData->_id);
        $input['updated_by'] = new ObjectID($this->userData->_id);

        $product = Product::create($input);

        return $this->sendResponse($product->toArray(), 'Product created successfully.');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::find($id);


        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }


        return $this->sendResponse($product->toArray(), 'Product retrieved successfully.');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $input = $request->all();


        // $validator = Validator::make($input, [
        //     'name' => 'required',
        //     'price' => 'required',
        //     'unit' => 'required',
        //     'category' => 'required',
        // ]);

        // if($validator->fails()){
        //     return $this->sendError('Validation Error.', $validator->errors());       
        // }

        $product->name = isset($input['name']) ? $input['name'] : $product->name;
        $product->price = isset($input['price']) ? (float)$input['price'] : $product->price;
        $product->unit = isset($input['unit']) ? $input['unit'] : $product->unit;
        $product->active = isset($input['active']) ? (bool)$input['active'] : $product->active;
        $product->category = isset($input['category']) ? $input['category'] : $product->category;
        $product->image = isset($input['image']) ? $input['image'] : $product->image;
        $product->updated_by = new ObjectID($this->userData->_id);
        $product->save();

        return $this->sendResponse($product->toArray(), 'Product updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->deleted_by = new ObjectID($this->userData->_id);
        $product->save();
        $product->delete();

        return $this->sendResponse($product->toArray(), 'Product deleted successfully.');
    }

    private function generateProductCode()
    {
        $number = 1;
        $lastProduct = Product::withTrashed()->orderBy('_id', 'DESC')->first();
        if($lastProduct) {
            $number = (int)substr($lastProduct->code, -6) + 1;
        }
        return 'PD' . sprintf("%'06d", $number);
    }
}