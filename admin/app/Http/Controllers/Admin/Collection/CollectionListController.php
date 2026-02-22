<?php

namespace App\Http\Controllers\Admin\Collection;

use Illuminate\Http\Request;
use App\Services\AipHttpClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Collection\CollectionWalletList;

class CollectionListController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.collection.list.index');
    }
    
    //列表
    public function getData(Request $request)
    {
        $model = CollectionWalletList::from('t_collection_wallet_list as a')
                ->where(function($query) use ($request){
                if ($request->wallet_addr != '') {
                    $query->where('a.wallet_addr', 'like' ,"%" . $request->wallet_addr ."%");
                }   
                if ($request->collection_wallet_addr != '') {
                    $query->where('a.collection_wallet_addr',  'like' ,"%" . $request->collection_wallet_addr ."%");
                }
            });

        $count = $model->count();
        $limit = $request->limit ?? 15;
        $offset = $request->page ? ($request->page - 1) * $limit : 0;

        $data = $model->limit($limit)->offset($offset)->select('a.*')->orderBy('a.rid','desc')->get();
        
        return ['code' => '0', 'data' => $data, 'count' => $count];
    }
}
