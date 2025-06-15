<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\User;

class MyBalanceController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load('balance');

        return Inertia::render('MyBalance', [
            'userName' => $user->name,
            'balance' => optional($user->balance)->current_balance ?? 0,
            'catatan' => $user->balance ? null : 'Belum ada data saldo untuk user ini',
        ]);
    }
}
