<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

class RestaurantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchQuery = $request->query('search');

        if (!$searchQuery) {
            return response()->json([
                'error' => true,
                'detail' => 'Search keyword is required.',
            ], 400);
        }

        $searchQuery = trim($searchQuery);

        $placeCacheKey = 'place_' . $searchQuery;
        $placeCacheResult = Redis::get($placeCacheKey);

        if ($placeCacheResult) {
            $firstPlaceCandidate = json_decode($placeCacheResult);
        } else {
            $placeAPIResponse = Http::get('https://maps.googleapis.com/maps/api/place/findplacefromtext/json', [
                'inputtype' => 'textquery',
                'fields' => 'formatted_address,name,geometry',
                'key' => Config::get('google.api_key'),
                'input' => $searchQuery,
            ]);

            if (!$placeAPIResponse->ok()) {
                return response()->json([
                    'error' => true,
                    'detail' => 'Place API error.',
                ], 500);
            }

            if ($placeAPIResponse->object()->status !== 'OK') {
                return response()->json([
                    'error' => true,
                    'detail' => 'Got ' . $placeAPIResponse->object()->status . ' from place API.',
                ], 200);
            }

            if (!count($placeAPIResponse->object()->candidates)) {
                $errorAssocResponse = [
                    'error' => true,
                    'detail' => "There is no such a place named $searchQuery.",
                ];

                Redis::set($placeCacheKey, json_encode($errorAssocResponse));

                return response()->json($errorAssocResponse, 404);
            }

            $firstPlaceCandidate = array_merge(['cached' => time(), $placeAPIResponse->object()->candidates[0]]);

            Redis::set($placeCacheKey, json_encode($firstPlaceCandidate));
        }

        if (array_key_exists('error', $firstPlaceCandidate)) {
            return response()->json($firstPlaceCandidate);
        }

        return response()->json($firstPlaceCandidate);

        // $firstPlaceCandidateLocation = $firstPlaceCandidate->geometry->location;

        // $nearbyRestaurantAPIResponse = Http::get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', [
        //     'keyword' => $searchQuery,
        //     'location' => $firstPlaceCandidateLocation->lat . ',' . $firstPlaceCandidateLocation->lng,
        //     'radius' => '1500',
        //     'type' => 'restaurant',
        //     'key' => $googleMapAPIKey,
        // ]);

        // if (!$nearbyRestaurantAPIResponse->ok()) {
        //     return response()->json([
        //         'error' => true,
        //         'detail' => 'Nearby API error.',
        //     ], 500);
        // }

        // if ($nearbyRestaurantAPIResponse->object()->status !== 'OK') {
        //     return response()->json([
        //         'error' => true,
        //         'detail' => 'Got ' . $placeAPIResponse->object()->status . ' from nearby API.',
        //     ], 200);
        // }

        // $nearbyRestaurantList = $nearbyRestaurantAPIResponse->object()->results;

        // if (!count($nearbyRestaurantList)) {
        //     return response()->json([
        //         'error' => true,
        //         'detail' => "There is no nearby restaurant near $searchQuery.",
        //     ], 404);
        // }

        // return response()->json($nearbyRestaurantList);
    }

    public function lc(Request $request)
    {
        $kl = [];
        foreach (Redis::keys("*") as $k) {
            array_push($kl, $k);
        }
        return response()->json($kl);
    }

    public function gk(Request $request)
    {

        return response()->json([Config::get('google.keys.first')]);
    }
}
