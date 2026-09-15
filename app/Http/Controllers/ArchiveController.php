<?php

namespace App\Http\Controllers;

use App\Support\Archive;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ArchiveController extends Controller
{
    /** Elements archives par page. */
    private const PER_PAGE = 12;

    /**
     * Archives du membre : annonces expirees, produits epuises ou hors
     * ligne, trajets passes. Un onglet par type, plus « Tout ».
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Un type inconnu retombe sur « Tout » plutot que sur une page vide
        $type   = array_key_exists((string) $request->input('type'), Archive::TYPES) ? $request->input('type') : null;
        $search = trim((string) $request->input('search'));

        $items = Archive::items($user, $type, $search);
        $page  = LengthAwarePaginator::resolveCurrentPage();

        return view('pages.archives.index', [
            'items'  => new LengthAwarePaginator(
                $items->forPage($page, self::PER_PAGE)->values(),
                $items->count(),
                self::PER_PAGE,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            ),
            'counts' => Archive::counts($user),
            'type'   => $type,
            'search' => $search,
        ]);
    }
}
