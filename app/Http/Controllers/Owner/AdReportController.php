<?php

namespace App\Http\Controllers\Owner;

use App\Models\Ad;
use App\Models\AdReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdReportController extends Controller
{
    public function store(Request $request, Ad $ad)
    {
        $alreadyReported = AdReport::where('ad_id', $ad->id)->where('user_id', auth()->id())->exists();

        if ($alreadyReported) {
            return response()->json([
                'message' => 'Vous avez déjà signalé cette annonce.'
            ], 400);
        }

        AdReport::create([
            'ad_id' => $ad->id,
            'user_id' => auth()->id(),
            'reason' => $request->reason,
        ]);

        return response()->json([
            'message' => 'Annonce signalée avec succès.'
        ]);
    }
}
