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
        // extract search from query param.
        $searchQuery = $request->query('search');

        // verify the search keyword, if it's not exists response error.
        if (!$searchQuery) {
            return response()->json([
                'error' => true,
                'detail' => 'Search keyword is required.',
            ], 400);
        }

        // get cached place from cache.
        $placeCacheKey = 'place_' . $searchQuery;
        $placeCacheResult = Redis::get($placeCacheKey);


        if ($placeCacheResult) {
            // if the place cache does exists, then check for its error key.
            // if it has error key, respone error.

            $firstPlaceCandidate = json_decode($placeCacheResult);

            if (property_exists($firstPlaceCandidate, 'error')) {
                return response()->json($firstPlaceCandidate);
            }
        } else {
            // but if place cache key does not exists, perform an external API request to google map API.
            // perform an external api request using laravel provided http client.
            // https://developers.google.com/maps/documentation/places/web-service/search-find-place
            $placeAPIResponse = Http::get('https://maps.googleapis.com/maps/api/place/findplacefromtext/json', [
                'key' => Config::get('services.google.key'),
                'input' => $searchQuery,
                'inputtype' => 'textquery',
                'fields' => 'formatted_address,name,geometry',
            ]);

            // check for external API response error.
            if (!$placeAPIResponse->ok()) {
                return response()->json([
                    'error' => true,
                    'detail' => 'Place API error.',
                ], 500);
            }

            // if the external API response status is not equal to 'OK', cache the response then pass the response to our frontend.
            // because we don't want to spam problaby useless request to google map API.
            if ($placeAPIResponse->object()->status !== 'OK') {
                $errorAssocResponse = [
                    'error' => true,
                    'detail' => 'Got ' . $placeAPIResponse->object()->status . ' from place API.',
                ];

                Redis::set($placeCacheKey, json_encode($errorAssocResponse), 'EX', '300');

                return response()->json($errorAssocResponse);
            }

            // and if google API response empty result, also cache it.
            if (!count($placeAPIResponse->object()->candidates)) {
                $errorAssocResponse = [
                    'error' => true,
                    'detail' => "There is no such a place named $searchQuery.",
                ];

                Redis::set($placeCacheKey, json_encode($errorAssocResponse), 'EX', '300');

                return response()->json($errorAssocResponse, 404);
            }

            // cache the first place candidate
            $firstPlaceCandidate = $placeAPIResponse->object()->candidates[0];


            Redis::set($placeCacheKey, json_encode($firstPlaceCandidate), 'EX', '3600');
        }

        // extract its location from first place candidate.
        $firstPlaceCandidateLocation = $firstPlaceCandidate->geometry->location;

        // get cached nearby restaurant from cache.
        $nearbyCacheKey = 'nearby_' . $searchQuery;
        $nearbyCacheResult = Redis::get($nearbyCacheKey);

        // if the cache hits, just response result from cache.
        if ($nearbyCacheResult) {
            $nearbyRestaurantList = json_decode($nearbyCacheResult);

            return response()->json($nearbyRestaurantList);
        }

        // if the cache does not exist, we perform an external API request.
        // https://developers.google.com/maps/documentation/places/web-service/search-nearby
        $nearbyRestaurantAPIResponse = Http::get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', [
            'key' => Config::get('services.google.key'),
            'keyword' => $searchQuery,
            'location' => $firstPlaceCandidateLocation->lat . ',' . $firstPlaceCandidateLocation->lng,
            'radius' => '5000',
            'type' => 'restaurant',
        ]);

        // check for external API response error.
        if (!$nearbyRestaurantAPIResponse->ok()) {
            return response()->json([
                'error' => true,
                'detail' => 'Nearby API error.',
            ], 500);
        }

        // cache and return external API response status to our frontend.
        if ($nearbyRestaurantAPIResponse->object()->status !== 'OK') {
            $errorAssocResponse = [
                'error' => true,
                'detail' => 'Got ' . $placeAPIResponse->object()->status . ' from nearby API.',
            ];

            Redis::set($nearbyCacheKey, json_encode($errorAssocResponse), 'EX', '300');

            return response()->json($errorAssocResponse);
        }

        // extract result from external API response.
        $nearbyRestaurantList = $nearbyRestaurantAPIResponse->object()->results;

        // cache empty restaurant list and responses to our frontend.
        if (!count($nearbyRestaurantList)) {
            $errorAssocResponse = [
                'error' => true,
                'detail' => "Cannot find any restaurant nearby $searchQuery.",
            ];

            Redis::set($nearbyCacheKey, json_encode($errorAssocResponse), 'EX', '300');

            return response()->json($errorAssocResponse, 404);
        }

        // put restaurant list into cache then responses to our frontend.
        Redis::set($nearbyCacheKey, json_encode($nearbyRestaurantList), 'EX', '3600');

        return response()->json($nearbyRestaurantList);
    }
}
