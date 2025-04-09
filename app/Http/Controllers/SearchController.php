<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query');

        $users = User::query();

        if ($query) {
            $users = $users->where(function($q) use ($query) {
                $q->where('email', 'like', '%' . $query . '%')
                    ->orWhere('phone', 'like', '%' . $query . '%')
                    ->orWhere('name', 'like', '%' . $query . '%');
            });
        }

        $users = $users->get();

        return view('search.index', compact('users'));
    }
}
