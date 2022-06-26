<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        $products = Product::with('ProductVariantPrice')->paginate(3);
        $searchVariants = Variant::select('title', 'id')->with('productVariant')->get();
        //    return $products[0]->ProductVariantPrice[0]->productVariant[0]->variantName;
        //    return $products[0]->ProductVariantPrice;

        return view('products.index', compact('products', 'searchVariants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    public function search(Request $request)
    {

        if (isset($request->title)) {
            $searchResults = Product::where('title', 'LIKE', '%' . $request->title . '%')->with('ProductVariantPrice')->get();
            $mode = 'search';   
        } elseif (isset($request->price_from) && isset($request->price_to)) {
            $searchResults = ProductVariantPrice::whereBetween('price', [1, 100])->with('ProductVariantPrice')->get();
        } elseif (isset($request->date)) {
            $searchResults = Product::where('created_at', 'LIKE', '%' . $request->date . '%')->with('ProductVariantPrice')->paginate(2);
            $mode = 'search';
        } elseif (isset($request->variant)) {
            return $searchResults = ProductVariant::where('variant_id','LIKE','%'.$request->variant)->with('product')->get();
        }
        $searchVariants = Variant::select('title', 'id')->with('productVariant')->get();  
        return view('products.show', compact('searchResults', 'mode', 'searchVariants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {      
       $finalData = [];          
        $pv = [];      
        DB::beginTransaction();
        try {
            $data = Product::create([
                'title' => $request->title,
                'sku' => $request->sku,
                'description' => $request->description
            ]);

            foreach ($request->product_variant as $product_variant) {
                foreach ($product_variant['tags'] as $ta) {
                    array_push($pv, ['variant_id' => $product_variant['option'], 'variant' => $ta, 'product_id' => $data->id]);
                }
            }
            ProductVariant::insert($pv);    

        foreach($request->product_variant_prices as $pvp){
            // return $pvp['title'];
            $i = 0;
            $titleEx = (explode("/",$pvp['title']));
            foreach($titleEx as $d){               
                $i++;
                if(!empty($d)){
                    $id = ProductVariant::where('variant',$d)->where('product_id',$data->id)->first();
                    array_push($finalData, ["product_variant_$i" => $id->id]);
                }
            }
            
           $pvpSave = new ProductVariantPrice();
           if(isset($finalData[0]['product_variant_1'])){
            $pvpSave->product_variant_one =$finalData[0]['product_variant_1'];
           }
           if(isset($finalData[1]['product_variant_2'])){
            $pvpSave->product_variant_two =$finalData[1]['product_variant_2'];
           }
           if(isset($finalData[2]['product_variant_3'])){
            $pvpSave->product_variant_three =$finalData[2]['product_variant_3'];
           }
           $pvpSave->price =$pvp['price'];
           $pvpSave->stock =$pvp['stock'];
           $pvpSave->product_id = $data->id;
           $pvpSave->save(); 
           $finalData = [];       
        }
           
            DB::commit();
            return response()->json([
                'id' => $data->id,

            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['msg' => 'not done'], 401);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
