<?php

use App\Actions\UpdateFakerSeedAction;
use Database\Factories\PersonFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Sleep;
use Inertia\Inertia;
use Pollsockets\Pollsockets;

Route::get('/', function () {
    // Read the pollsockets time before the data, so the client gets every change made after this point.
    $since = Pollsockets::now();

    return Inertia::render('Welcome', [
        'since' => $since,
        'people' => PersonFactory::times(10)->make(),
    ]);
});

Route::get('/people', function (Request $request) {
    $people = PersonFactory::times(10)->make();
    $size = mb_strlen($people->toJson(), '8bit');

    if (! $request->has('noSleep')) {
        Sleep::for(3)->seconds();
    }

    return [
        'people' => $people,
        'size' => $size,
    ];
});

Route::post('/simulate', function (UpdateFakerSeedAction $action) {
    Sleep::for(1)->second();

    $action();

    return response()->noContent();
});
