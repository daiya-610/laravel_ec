<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\shop;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use InterventionImage;
use App\Http\Reauests\UploadImageRequest;
use App\Services\ImageService;

class ShopController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:owner');

        $this->middleware(function ($request, $next) {
            // dd($request->route()->parameter('shop')); //文字列
            // dd(Auth::id()); //数字

            $id = $request->route()->parameter('shop'); //shopのid取得
            if(!is_null($id)){ // null判定
                $shopsOwnerId = Shop::findOrFail($id)->owner->id;
                    $shopId = (int)$shopsOwnerId; //キャスト 文字列→数値に型変換
                    $ownerId = Auth::id();
                    if($shopId !== $ownerId){ // 同じでなかったら
                        abort(404); //404画面表示
                    }
            }
            return $next($request);
        });
    }

    public function index()
    {
        // phpinfo();

        // $ownerId = Auth::id();
        $shops = Shop::where('owner_id', Auth::id())->get();

        return view('owner.shops.index',
        compact('shops'));
    }

    public function edit($id)
    {
        $shop = Shop::findOrFail($id);
        // dd(Shop::findOrFail($id));
        return view('owner.shops.edit', compact('shop'));
    }

    public function update(UploadImageRequest $request, $id)
    {
        $imageFile = $request->image;
        if(!is_null($imageFile) && $imageFile->isValid() ){
            $fileNameStore = ImageService::upload($imageFile, 'shops');
            // // Storage::putFile('public/shops', $imageFile); リサイズなしのバージョン
            // $fileName = uniqid(rand().'_');
            // $extension = $imageFile->extension();
            // $fileNameToStore = $fileName.'.'.$extension;
            // $resizedImage = InterventionImage::make($imageFile)->resize(1920, 1080)->encode();
            // // dd($imageFile, $resizedImage);

            // Storage::put('public/shops/' . $fileNameToStore, $resizedImage);
        }
        return redirect()->route('owner.shops.index');
    }

}
