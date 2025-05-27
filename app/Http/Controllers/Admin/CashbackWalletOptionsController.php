<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CashbackWalletOptionResource;
use App\Models\CashbackWalletOption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CashbackWalletOptionsController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $cashbackOptions = CashbackWalletOption::all();

        return CashbackWalletOptionResource::collection($cashbackOptions);
    }
}
