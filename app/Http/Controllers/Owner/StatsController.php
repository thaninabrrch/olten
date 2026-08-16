<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StatsController extends Controller
{ 
    public function adsStats(Request $request)
    {
        $period = $request->get('period', 'week');
        $visitFilter = $request->get('visitFilter', 'all');
        $adFilter = $request->get('annonceFilter', 'all');

        Carbon::setLocale('fr');

        if ($period === 'custom') {
            $start = Carbon::parse($request->get('start'))->startOfDay();
            $end   = Carbon::parse($request->get('end'))->endOfDay();
        } else {
            switch ($period) {
                case 'month':
                    $start = now()->startOfMonth();
                    $end   = now()->endOfMonth();
                    break;

                case 'year':
                    $start = now()->startOfYear();
                    $end   = now()->endOfYear();
                    break;

                default:
                    $start = now()->subDays(6)->startOfDay();
                    $end   = now()->endOfDay();
            }
        }

        $adsQuery = Ad::where('user_id', auth()->id())
            ->whereBetween('created_at', [$start, $end]);

        if ($adFilter === 'active') {
            $adsQuery->where('is_approved', 1);
        } elseif ($adFilter === 'inactive') {
            $adsQuery->where('is_approved', 0);
        }

        $adsPerDay = $adsQuery
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $visitsQuery = AdVisit::whereBetween('created_at', [$start, $end]);

        if ($visitFilter === 'unique') {
            $visitsQuery->selectRaw('DATE(created_at) as date, COUNT(DISTINCT CONCAT(user_id, ip, DATE(created_at))) as count')
                ->groupBy('date');
        } elseif ($visitFilter === 'repeat') {
            $visitsQuery->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date');
        } else {
            $visitsQuery->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date');
        }

        $visitsPerDay = $visitsQuery->pluck('count', 'date');

        $labels = [];
        $adsData = [];
        $viewsData = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $key = $date->format('Y-m-d');

            $labels[] = $date->translatedFormat('d M');
            $adsData[] = $adsPerDay[$key] ?? 0;
            $viewsData[] = $visitsPerDay[$key] ?? 0;
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Nombre d’annonces',
                    'data' => $adsData
                ],
                [
                    'label' => 'Nombre de vues',
                    'data' => $viewsData
                ]
            ]
        ]);
    }
}