<?php

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/posts', function (Request $request) {
    $query = Post::query();

    $query->getModel()->setSearchable([
        'columns' => [
            'posts.title',
            'posts.body',
            'author_name' => 'CONCAT(authors.first_name, " ", authors.last_name)',
        ],
        'joins' => [
            'authors' => ['authors.id', 'posts.author_id'],
        ]
    ]);

    return $query
        ->with('author')
        ->search($request->search)
        ->sortByRelevance(!$request->has('sort_by'))
        ->when($query->getModel()->isColumnValid($request->sort_by), function ($query) use ($request) {
            $query->orderBy(
                DB::raw($query->getModel()->getColumn($request->sort_by)),
                $request->descending ? 'desc' : 'asc'
            );
        })
        ->paginate();
});
