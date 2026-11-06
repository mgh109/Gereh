<?php
namespace App\Controllers;
use App\Models\ProductVariantModel;
use App\Models\ProductModel;
use App\Models\InventoryItemModel;

class CartController extends BaseController {
    public function index(): string {
        $lines=[];$total=0;
        foreach ((array) session()->get('store_cart') as $id=>$qty) {
            $variant=(new ProductVariantModel())->where('is_active',1)->find((int)$id);
            if (!$variant) continue;
            $product=(new ProductModel())->where('status','active')->find($variant['product_id']);
            if (!$product) continue;
            $stock=(new InventoryItemModel())->where('variant_id',$id)->first();
            $available=(int)($stock['quantity']??0);
            if ($available<=0) continue;
            $qty=min(max(1,(int)$qty),$available);
            $price=(int)($variant['price_toman']??$product['price_toman']);
            $lines[]=['variant'=>$variant,'product'=>$product,'quantity'=>$qty,'available'=>$available,'price'=>$price,'subtotal'=>$price*$qty];
            $total+=$price*$qty;
        }
        return view('store/cart',['lines'=>$lines,'total'=>$total]);
    }
    public function add() {
        $rules=['variant_id'=>'required|is_natural_no_zero','quantity'=>'required|is_natural_no_zero|less_than_equal_to[20]'];
        if (!$this->validate($rules)) return redirect()->back()->with('cart_error','انتخاب محصول یا تعداد معتبر نیست.');
        $id=(int)$this->request->getPost('variant_id');$quantity=(int)$this->request->getPost('quantity');
        $variant=(new ProductVariantModel())->where('is_active',1)->find($id);
        if (!$variant || !(new ProductModel())->where('status','active')->find($variant['product_id'])) return redirect()->back()->with('cart_error','این تنوع قابل خرید نیست.');
        $stock=(new InventoryItemModel())->where('variant_id',$id)->first();
        $cart=(array)session()->get('store_cart');$newQty=(int)($cart[$id]??0)+$quantity;
        if ($newQty>(int)($stock['quantity']??0)) return redirect()->back()->with('cart_error','موجودی کافی نیست.');
        $cart[$id]=$newQty;session()->set('store_cart',$cart);
        return redirect()->to(site_url('cart'))->with('cart_success','محصول به سبد خرید اضافه شد.');
    }
    public function update() {
        $id=(int)$this->request->getPost('variant_id');$quantity=(int)$this->request->getPost('quantity');
        $cart=(array)session()->get('store_cart');
        if (!array_key_exists($id,$cart)) return redirect()->to(site_url('cart'));
        if ($quantity<=0) unset($cart[$id]);
        else {
            $stock=(new InventoryItemModel())->where('variant_id',$id)->first();
            $cart[$id]=min($quantity,20,(int)($stock['quantity']??0));
            if ($cart[$id]<=0) unset($cart[$id]);
        }
        session()->set('store_cart',$cart);
        return redirect()->to(site_url('cart'));
    }
}
