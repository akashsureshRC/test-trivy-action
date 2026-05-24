<?php

namespace App\Services;

class GeolocationService
{
    /**
     * Earth's radius in meters
     */
    const EARTH_RADIUS_METERS = 6371000;

    /**
     * Calculate the distance between two coordinates using the Haversine formula
     * 
     * @param float $lat1 Latitude of point 1
     * @param float $lon1 Longitude of point 1
     * @param float $lat2 Latitude of point 2
     * @param float $lon2 Longitude of point 2
     * @return float Distance in meters
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        // Convert to radians
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        // Haversine formula
        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }

    /**
     * Check if a point is within a given radius of another point
     * 
     * @param float $lat1 Latitude of point to check
     * @param float $lon1 Longitude of point to check
     * @param float $lat2 Latitude of center point
     * @param float $lon2 Longitude of center point
     * @param float $radius Radius in meters
     * @return bool
     */
    public static function isWithinRadius(
        float $lat1, 
        float $lon1, 
        float $lat2, 
        float $lon2, 
        float $radius
    ): bool {
        $distance = self::calculateDistance($lat1, $lon1, $lat2, $lon2);
        return $distance <= $radius;
    }

    /**
     * Check if employee is within branch geofence
     * 
     * @param float $employeeLat Employee's current latitude
     * @param float $employeeLon Employee's current longitude
     * @param \App\Models\Hrm\Branch $branch Branch to check against
     * @return array ['within_geofence' => bool, 'distance' => float]
     */
    public static function checkBranchGeofence(
        float $employeeLat, 
        float $employeeLon, 
        $branch
    ): array {
        if (!$branch->hasGeolocation()) {
            return [
                'within_geofence' => true, // If no geofence configured, allow
                'distance' => 0,
                'radius' => 0,
                'message' => 'Branch has no geolocation configured',
            ];
        }

        $distance = self::calculateDistance(
            $employeeLat,
            $employeeLon,
            (float) $branch->latitude,
            (float) $branch->longitude
        );

        $withinGeofence = $distance <= $branch->attendance_radius;

        return [
            'within_geofence' => $withinGeofence,
            'distance' => round($distance, 2),
            'radius' => $branch->attendance_radius,
            'message' => $withinGeofence 
                ? 'Within geofence' 
                : sprintf(
                    'You are %.0fm away from your work location. Please move within %dm to clock in.',
                    $distance,
                    $branch->attendance_radius
                ),
        ];
    }

    /**
     * Format distance for display
     * 
     * @param float $meters Distance in meters
     * @return string Formatted distance
     */
    public static function formatDistance(float $meters): string
    {
        if ($meters < 1000) {
            return round($meters) . 'm';
        }
        return round($meters / 1000, 2) . 'km';
    }

    /**
     * Validate coordinates
     * 
     * @param float $latitude
     * @param float $longitude
     * @return bool
     */
    public static function validateCoordinates(float $latitude, float $longitude): bool
    {
        return $latitude >= -90 && $latitude <= 90 && 
               $longitude >= -180 && $longitude <= 180;
    }
}
