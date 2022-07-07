<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Null_;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        $products = Product::with('ProductVariantPrice')->orderBy('id', 'desc')->paginate(3);
        $searchVariants = Variant::select('title', 'id')->with('productVariant')->get();



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
        switch ($request) {
            case (isset($request->title)):
                $searchResults = Product::where('title', 'LIKE', '%' . $request->title . '%')
                    ->with('ProductVariantPrice')
                    ->paginate(3);
                break;
            case (isset($request->price_from) && isset($request->price_to)):
                $searchResults = Product::whereIn(
                    'id',
                    ProductVariantPrice::select('product_id')
                        ->whereBetween('price', [$request->price_from, $request->price_to])
                )->paginate(3);
                break;
            case (isset($request->date)):
                $searchResults = Product::where('created_at', 'LIKE', '%' . $request->date . '%')
                    ->with('ProductVariantPrice')
                    ->paginate(3);
                break;
            default:
                $searchResults = Product::whereIn(
                    'id',
                    ProductVariant::select('product_id')
                        ->where('variant', 'LIKE', '%' . $request->variant)
                )->with('ProductVariantPrice')
                    ->paginate(3);
                break;
        }

        $searchVariants = Variant::select('title', 'id')->with('productVariant')->get();
        return view('products.show', compact('searchResults', 'searchVariants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        return $request->all();
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
            foreach ($request->product_variant_prices as $pvp) {
                $titleEx = (explode("/", $pvp['title']));
                $i = 0;
                foreach ($titleEx as $tx) {
                    $i++;
                    if (!empty($tx)) {
                        $cid = ProductVariant::where([
                            'variant' => $tx,
                            'product_id' => $data->id,
                        ])->first();
                        switch ($i) {
                            case ($i == 1):
                                $finalData += ['product_variant_one' => $cid->id];
                                break;
                            case ($i == 2):
                                $finalData += ['product_variant_two' => $cid->id];
                                break;
                            case ($i == 3):
                                $finalData += ['product_variant_three' => $cid->id];
                                break;
                            default:
                                return "somthing want to wrong";
                        }
                    }
                }
                $finalData += ["price" => $pvp["price"], "stock" => $pvp["stock"], "product_id" => $data->id];
                ProductVariantPrice::insert($finalData);
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
        // return $searchVariants = Variant::query()
        //    ->addSelect([
        //        'productVariant_id' => ProductVariant::query()
        //        ->whereColumn('variants.id','product_variants.variant_id')
        //        ->orderBy('id','desc')
        //        ->select('id')
        //        ->limit(1)           
        //    ])->addSelect([
        //     'name' => ProductVariant::query()
        //     ->whereColumn('variants.id','product_variants.variant_id')
        //     ->orderBy('id','desc')
        //     ->select('variant')
        //     ->limit(1)
        // ])
        //    ->get();

        // $single_product = Product::where('id', $product->id)->with('ProductVariantPrice')->first();
         $single_product = Product::where('id', $product->id)
         ->with("ProductVariant","ProductVariantPrice.pvone","ProductVariantPrice.pvtwo","ProductVariantPrice.pvthree")
         ->first();
    
        $variants = Variant::all();
        return view('products.edit', compact('single_product', 'variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        return 'ok';
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
    }
}
