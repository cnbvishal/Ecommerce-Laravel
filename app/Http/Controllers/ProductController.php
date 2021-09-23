<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;
use App\Http\Controllers\Session;
use Illuminate\Support\Facades\DB;
class ProductController extends Controller
{
        function index()
        {
          $data= Product ::all();
          return view('product',['products'=>$data]);
        }
        //for detail
        function detail($id)
        {
          $data =Product::find($id);
          return view('detail',['product'=>$data]);
        }
        //for search
        function search(Request $req)
        {
          $data =Product::
          where('name','like','%'.$req->input('query').'%')->get();
          return view('search',['products'=>$data]);
        }

        // functionality for add to cart.
        function addTocart(Request $req)
        {
           if($req->session()->has('user'))
           {
             $cart=new Cart;
             $cart->user_id=$req->session()->get('user')['id'];
             $cart->product_id=$req->product_id;
             $cart->save();
             return redirect('/');
           }   
            else
            {
                return redirect('/login');
            }
        }

        // cart item here
        static function cartitem()
        {
          $userId=Session()->get('user')['id'];
          return Cart::where('user_id',$userId)->count();
        }

        //  cartlist joins here
        function cartList()
        {
          $userId=session()->get('user')['id'];
          $products=DB::table('cart')
          ->join('products','cart.product_id','=','products.id')
          ->where('cart.user_id',$userId)
          ->select('products.*','cart.id as cart_id')
          ->get();

          return view('cartlist',['products'=>$products]);  
        }

          //  remove cart functionality here
        function removeCart($id)
        {
         Cart::destroy($id);
         return redirect('cartlist');
        }

        // order now functionality here
        function orderNow()
        {
          $userId=session()->get('user')['id'];
          $total= $products=DB::table('cart')
          ->join('products','cart.product_id','=','products.id')
          ->where('cart.user_id',$userId)
          ->sum('products.price');

          return view('ordernow',['total'=>$total]);   
        }
        // order place
        function orderPlace(Request $req)
        {
          $userId=session()->get('user')['id'];
         $allcart=Cart::where('user_id',$userId)->get();
         foreach($allcart as $cart)
         {
          $order= new Order;
          $order->product_id=$cart['product_id'];
          $order->user_id=$cart['user_id'];
          $order->status="pending";
          $order->payment_method=$req->payment;
          $order->payment_status="pending";
          $order->address=$req->address;
          $order->save();
          Cart::where('user_id',$userId)->delete(); 
         }
         $req->input(); 
         return redirect('/');
        }
        //my orders list functionality here
        function myOrders()
        {
          $userId=session()->get('user')['id'];
          //joins of orders and product table
          $orders=DB::table('orders')
          ->join('products','orders.product_id','=','products.id')
          ->where('orders.user_id',$userId)
          ->get();

          return view('myorders',['orders'=>$orders]);
        }

}
