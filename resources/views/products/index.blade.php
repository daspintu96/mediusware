@extends('layouts.app')

@section('content')

<div class="card">
    <form action="{{URL('search')}}" method="post" class="card-header">
        @csrf
        <div class="form-row justify-content-between">
            <div class="col-md-2">
                <input type="text" name="title" placeholder="Product Title" class="form-control">
            </div>
            <div class="col-md-2">
                <select name="variant" id="" class="form-control">
                    @foreach($searchVariants as $sv)
                    <option disabled>----{{$sv->title}}---- </option>
                    @foreach($sv->productVariant as $v)
                    <option value="{{$v->variant}}">{{$v->variant}} </option>
                    @endforeach
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Price Range</span>
                    </div>
                    <input type="text" name="price_from" aria-label="First name" placeholder="From" class="form-control">
                    <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control">
                </div>
            </div>
            <div class="col-md-2">
                <input type="date" name="date" placeholder="Date" class="form-control">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
            </div>
        </div>
    </form>

  
    <div class="card-body">
        <div class="table-response">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Variant</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @php $i = 0 @endphp
                    @foreach($products as $product)
                    @php $i++ @endphp
                    <tr>
                        <td>{{$i}}</td>
                        <td>{{$product->title}} <br> Created at : {{$product->created_at->diffForHumans()}}</td>
                        <td width="200px">{{$product->description}}</td>
                        <td>
                            @foreach($product->ProductVariantPrice as $pvc)
                            <dl class="row mb-0" style="height: 80px; overflow: hidden" id="variant">

                                <dt class="col-sm-3 pb-0">
                                    {{$pvc->pvone->variant}}/ {{$pvc->pvtwo->variant}}/
                                    @if(isset($pvc->pvthree->variant))
                                    {{$pvc->pvthree->variant}}
                                    @endif
                                </dt>
                                <dd class="col-sm-9">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4 pb-0">Price : {{ number_format($pvc->price,2) }}</dt>
                                        <dd class="col-sm-8 pb-0">InStock : {{ number_format($pvc->stock,2) }}</dd>
                                    </dl>
                                </dd>
                            </dl>
                            @endforeach
                            <button onclick="$('#variant').toggleClass('h-auto')" class="btn btn-sm btn-link">Show more</button>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('product.edit', $product->id) }}" class="btn btn-success">Edit</a>
                            </div>
                        </td>
                    </tr>

                    @endforeach



                </tbody>

            </table>
        </div>

    </div>

    <div class="card-footer">
        <div class="row justify-content-between">
            <div class="col-md-6">

                <p>
                    Showing {{($products->currentpage()-1)*$products->perpage()+1}} to {{$products->currentpage()*$products->perpage()}}
                    of {{$products->total()}} entries
                </p>
            </div>
            <div class="col-md-2">
                {{$products->links()}}
            </div>
        </div>
    </div>
 
</div>

@endsection